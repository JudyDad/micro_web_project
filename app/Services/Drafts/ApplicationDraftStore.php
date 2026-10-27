<?php

declare(strict_types=1);

namespace App\Services\Drafts;

use App\Models\WebAccount;
use App\Services\AccountAccess;
use App\Services\AccountAuditTrail;
use App\Services\Applications\ApplicationService;
use App\Services\People\PeopleService;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

/** Private encrypted form recovery, separate from committed operational records. */
final class ApplicationDraftStore
{
    public const APPLICANT_KINDS = ['loss', 'illness', 'asset', 'aid', 'help', 'homepower'];

    public const KINDS = ['application', 'people', 'agriculture', 'rehab', 'rehab_answers', 'powersource', 'risk', 'material', ...self::APPLICANT_KINDS];

    public const RETENTION_DAYS = 7;

    private const MAX_BYTES = 131072;

    public function __construct(private AccountAccess $access, private AccountAuditTrail $audit, private ApplicationService $applications, private PeopleService $people) {}

    private function authorize(WebAccount $account, int $city, ?int $application = null): void
    {
        abort_unless($this->access->check($account, $city, 'applications.view') && $this->access->check($account, $city, 'applications.manage'), 403);
        if ($application !== null) {
            $row = $this->applications->find($account, $application)['application'];
            abort_unless((int) $row->city_id === $city, 404);
        }
    }

    private function disk(): Filesystem
    {
        abort_unless(config('filesystems.disks.local.driver') === 'local' && config('filesystems.disks.local.serve') === false, 503, 'Private draft storage is unavailable.');

        return Storage::disk('local');
    }

    private function authorizeKind(WebAccount $account, int $city, string $kind): void
    {
        if (in_array($kind, self::APPLICANT_KINDS, true)) {
            abort_unless($this->access->check($account, $city, 'people.view') && $this->access->check($account, $city, 'people.manage'), 403);
        }
    }

    private function directory(WebAccount $account, int $city): string
    {
        return 'application-drafts/'.(int) $account->id.'/'.$city;
    }

    private function path(WebAccount $account, int $city, string $id): string
    {
        abort_unless(preg_match('/\A[a-f0-9]{32}\z/', $id) === 1, 404);

        return $this->directory($account, $city).'/'.$id.'.enc';
    }

    private function lock(WebAccount $account, int $city, callable $work): mixed
    {
        return Cache::store(config('session.block_store', 'file'))->lock('application-drafts:'.$account->id.':'.$city, 30)->block(5, $work);
    }

    private function read(WebAccount $account, int $city, string $id): array
    {
        $path = $this->path($account, $city, $id);
        abort_unless($this->disk()->exists($path) && $this->disk()->size($path) <= self::MAX_BYTES * 3, 404);
        try {
            $draft = json_decode(Crypt::decryptString($this->disk()->get($path)), true, 32, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            abort(404);
        }
        abort_unless(is_array($draft) && ($draft['owner'] ?? null) === (int) $account->id && ($draft['city'] ?? null) === $city
            && ($draft['id'] ?? null) === $id && in_array($draft['kind'] ?? null, self::KINDS, true)
            && is_string($draft['version'] ?? null) && preg_match('/\A[a-f0-9]{64}\z/', $draft['version']) === 1
            && array_key_exists('application_id', $draft) && ($draft['application_id'] === null || is_int($draft['application_id']))
            && is_array($draft['payload'] ?? null) && is_int($draft['updated_at'] ?? null)
            && is_int($draft['expires_at'] ?? null), 404);
        if ($draft['expires_at'] <= now()->timestamp) {
            abort_unless($this->disk()->delete($path), 503);
            abort(404);
        }
        abort_unless($draft['application_id'] === null || (is_string($draft['payload']['version'] ?? null)
            && preg_match('/\A[a-f0-9]{64}\z/', $draft['payload']['version']) === 1), 404);
        $this->authorize($account, $city, $draft['application_id']);
        $this->authorizeKind($account, $city, $draft['kind']);

        return $draft;
    }

    /** Same-directory rename makes the complete encrypted envelope visible atomically. */
    private function write(string $path, string $encrypted): void
    {
        $temporary = $path.'.'.bin2hex(random_bytes(16)).'.tmp';
        try {
            abort_unless($this->disk()->put($temporary, $encrypted, ['visibility' => 'private']), 503);
            abort_unless($this->disk()->move($temporary, $path), 503);
        } finally {
            $this->disk()->delete($temporary);
        }
    }

    /** Lazy retention also releases quota for abandoned or no-longer-decryptable drafts. */
    private function prune(WebAccount $account, int $city): void
    {
        $cutoff = now()->subDays(self::RETENTION_DAYS)->timestamp;
        foreach ($this->disk()->files($this->directory($account, $city)) as $path) {
            if ($this->disk()->lastModified($path) <= $cutoff) {
                abort_unless($this->disk()->delete($path), 503);
            }
        }
    }

    private function payload(string $kind, array $payload): array
    {
        $allowed = ['fields', 'references', 'beneficiary_id', 'person_id', 'role', 'stage', 'detail_id', 'link_id', 'parent_id', 'version'];
        $nested = [];
        if ($kind === 'application') {
            // One-form intake: the applicant and family sections are drafted with the project.
            $allowed = [...$allowed, 'beneficiary_mode', 'person', 'family'];
            $nested = ['person' => ['fields', 'references'], 'family' => ['id', 'number']];
        }
        if (array_diff(array_keys($payload), $allowed) || ! in_array($kind, self::KINDS, true)) {
            throw ValidationException::withMessages(['draft' => __('applications.draft_invalid')]);
        }
        $this->checkSection($payload['fields'] ?? [], array_keys($this->applications->fields($kind)));
        $this->checkSection($payload['references'] ?? [], array_keys($this->applications->relations($kind)));
        foreach ($nested as $section => $keys) {
            $values = $payload[$section] ?? [];
            if (! is_array($values) || array_diff(array_keys($values), $keys)) {
                throw ValidationException::withMessages(['draft' => __('applications.draft_invalid')]);
            }
            if ($section === 'person') {
                $this->checkSection($values['fields'] ?? [], array_keys($this->people->fields('person')));
                $this->checkSection($values['references'] ?? [], array_keys($this->people->relations('person')));
            } else {
                $this->checkSection($values, $keys);
            }
        }
        foreach (array_diff(array_keys($payload), ['fields', 'references', ...array_keys($nested)]) as $key) {
            if ($payload[$key] !== null && (! is_string($payload[$key]) && ! is_int($payload[$key]) || strlen((string) $payload[$key]) > 128)) {
                throw ValidationException::withMessages(['draft' => __('applications.draft_invalid')]);
            }
        }
        $json = json_encode($payload, JSON_THROW_ON_ERROR);
        if (strlen($json) > self::MAX_BYTES) {
            throw ValidationException::withMessages(['draft' => __('applications.draft_too_large')]);
        }

        return $payload;
    }

    /** A drafted section holds only known keys and bounded scalar text or numbers. */
    private function checkSection(mixed $values, array $keys): void
    {
        if (! is_array($values) || array_diff(array_keys($values), $keys)) {
            throw ValidationException::withMessages(['draft' => __('applications.draft_invalid')]);
        }
        foreach ($values as $value) {
            if ($value !== null && (! is_scalar($value) || ! is_string($value) && ! is_int($value) && ! is_float($value))) {
                throw ValidationException::withMessages(['draft' => __('applications.draft_invalid')]);
            }
            if (is_string($value) && (strlen($value) > 40000 || preg_match('//u', $value) !== 1)) {
                throw ValidationException::withMessages(['draft' => __('applications.draft_invalid')]);
            }
        }
    }

    public function save(WebAccount $account, int $city, string $kind, ?int $application, array $payload, ?string $id = null, ?string $version = null): array
    {
        $this->authorize($account, $city, $application);
        $this->authorizeKind($account, $city, $kind);
        abort_unless($application !== null || $kind === 'application', 422);
        $payload = $this->payload($kind, $payload);
        if ($application !== null && (! is_string($payload['version'] ?? null) || preg_match('/\A[a-f0-9]{64}\z/', $payload['version']) !== 1)) {
            throw ValidationException::withMessages(['version' => __('applications.draft_invalid')]);
        }

        return $this->lock($account, $city, function () use ($account, $city, $kind, $application, $payload, $id, $version) {
            $this->authorize($account, $city, $application);
            $this->authorizeKind($account, $city, $kind);
            $this->prune($account, $city);
            if ($id !== null) {
                $existing = $this->read($account, $city, $id);
                abort_unless($existing['kind'] === $kind && $existing['application_id'] === $application, 404);
                abort_unless(is_string($version) && hash_equals($existing['version'], $version), 409, __('applications.draft_conflict'));
                foreach (['detail_id', 'link_id'] as $identity) {
                    abort_unless((string) ($existing['payload'][$identity] ?? '') === (string) ($payload[$identity] ?? ''), 409, __('applications.draft_conflict'));
                }
            } else {
                abort_if(count($this->disk()->files($this->directory($account, $city))) >= 100, 409, __('applications.draft_limit'));
                $id = bin2hex(random_bytes(16));
            }
            $path = $this->path($account, $city, $id);
            $old = $this->disk()->exists($path) ? $this->disk()->get($path) : null;
            $draft = ['id' => $id, 'owner' => (int) $account->id, 'city' => $city, 'kind' => $kind, 'application_id' => $application,
                'version' => bin2hex(random_bytes(32)), 'updated_at' => now()->timestamp,
                'expires_at' => now()->addDays(self::RETENTION_DAYS)->timestamp, 'payload' => $payload];
            try {
                $this->write($path, Crypt::encryptString(json_encode($draft, JSON_THROW_ON_ERROR)));
                $this->audit->record($account->id, 'application_draft.saved', 'succeeded', $city, decision: 'draft:'.$id);
            } catch (Throwable $error) {
                if ($old === null) {
                    $this->disk()->delete($path);
                } else {
                    $this->write($path, $old);
                }
                throw $error;
            }

            return $draft;
        });
    }

    public function load(WebAccount $account, int $city, string $id): array
    {
        $this->authorize($account, $city);

        return $this->lock($account, $city, function () use ($account, $city, $id) {
            $this->authorize($account, $city);
            $draft = $this->read($account, $city, $id);
            $this->audit->record($account->id, 'application_draft.viewed', 'succeeded', $city, decision: 'draft:'.$id);

            return $draft;
        });
    }

    public function listing(WebAccount $account, int $city): array
    {
        $this->authorize($account, $city);

        return $this->lock($account, $city, function () use ($account, $city) {
            $this->authorize($account, $city);
            $this->prune($account, $city);
            $drafts = [];
            foreach (array_slice($this->disk()->files($this->directory($account, $city)), 0, 100) as $path) {
                try {
                    $draft = $this->read($account, $city, pathinfo($path, PATHINFO_FILENAME));
                    unset($draft['payload']);
                    $drafts[] = $draft;
                } catch (HttpException $error) {
                    if ($error->getStatusCode() !== 404) {
                        throw $error;
                    }
                }
            }
            usort($drafts, fn ($a, $b) => $b['updated_at'] <=> $a['updated_at']);

            return $drafts;
        });
    }

    public function discard(WebAccount $account, int $city, string $id, string $version): void
    {
        $this->authorize($account, $city);
        $this->lock($account, $city, function () use ($account, $city, $id, $version) {
            $this->authorize($account, $city);
            $draft = $this->read($account, $city, $id);
            abort_unless(hash_equals($draft['version'], $version), 409, __('applications.draft_conflict'));
            $path = $this->path($account, $city, $id);
            $old = $this->disk()->get($path);
            abort_unless($this->disk()->delete($path), 503);
            try {
                $this->audit->record($account->id, 'application_draft.discarded', 'succeeded', $city, decision: 'draft:'.$id);
            } catch (Throwable $error) {
                $this->write($path, $old);
                throw $error;
            }
        });
    }
}
