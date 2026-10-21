<?php

declare(strict_types=1);

namespace App\Services\Finance;

use App\Models\WebAccount;
use App\Services\AccountAccess;
use App\Services\AccountAuditTrail;
use App\Services\Operational\OperationalTables;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Purchased items of a project (Purchasing_Form). The desktop screen is unreachable; this is an opt-in editor over the preserved rows.
 * Items are inserted or edited in place; deletion is not offered.
 */
final class PurchasingService
{
    public const CURRENCIES = ['USD', 'SYP'];

    public function __construct(private AccountAccess $access, private AccountAuditTrail $audit, private OperationalTables $tables) {}

    private function authorize(WebAccount $account, int $city, bool $write = false): void
    {
        foreach (['applications.view', 'purchasing.view', ...($write ? ['purchasing.manage'] : [])] as $capability) {
            if (! $this->access->check($account, $city, $capability)) {
                $this->audit->record($account->id, 'access.denied', 'denied');
                abort(403);
            }
        }
    }

    private function project(int $id): object
    {
        $row = $this->tables->query('microproject')->where('p.id', $id)->first();
        abort_if($row === null, 404);

        return $row;
    }

    private function items(object $project): Collection
    {
        $ref = $this->tables->relation('purchasing', 'MicroProject_ID');

        return $this->tables->query('purchasing')->where('p.city_id', $project->city_id)->where('p.source_id', $project->source_id)
            ->where('p.'.$ref['resolved_column'], $project->id)->where('p.'.$ref['state_column'], 'resolved')->orderBy('p.id')->get();
    }

    private function version(Collection $items): string
    {
        return hash('sha256', json_encode($items->map(fn ($row) => collect((array) $row)->except(['city_name_en', 'city_name_ar'])->all())->all(), JSON_THROW_ON_ERROR));
    }

    public function show(WebAccount $account, int $projectId): array
    {
        $project = $this->project($projectId);
        $city = (int) $project->city_id;
        $this->authorize($account, $city);
        $items = $this->items($project);
        $categoryRef = $this->tables->relation('purchasing', 'ItemCategory_ID');
        $this->audit->record($account->id, 'purchasing.view', 'succeeded', $city, subjectType: 'application_entity', subjectId: $projectId);

        return [
            'project' => $project, 'items' => $items->map(function ($row) use ($categoryRef) {
                $row = clone $row;
                $row->category_id = $row->{$categoryRef['state_column']} === 'resolved' ? (int) $row->{$categoryRef['resolved_column']} : null;

                return $row;
            }), 'categories' => $this->tables->catalog('purchasingcategory', $city, (int) $project->source_id),
            'canManage' => $this->access->check($account, $city, 'purchasing.manage'), 'version' => $this->version($items),
        ];
    }

    private function fail(string $field, string $key): never
    {
        throw ValidationException::withMessages([$field => __('finance.errors.'.$key)]);
    }

    /** $rows: id?, name, brand, category_id?, quantity, price, currency, invoice_date?, invoice_number, trader. */
    public function save(WebAccount $account, int $projectId, array $rows, string $version): void
    {
        DB::transaction(function () use ($account, $projectId, $rows, $version) {
            DB::table('op_microproject')->where('id', $projectId)->lockForUpdate()->first();
            $project = $this->project($projectId);
            $city = (int) $project->city_id;
            $source = (int) $project->source_id;
            $this->authorize($account, $city, true);
            $items = $this->items($project);
            abort_unless(hash_equals($this->version($items), $version), 409, 'Purchases changed. Reload before saving.');
            $categoryRef = $this->tables->relation('purchasing', 'ItemCategory_ID');
            $stored = $items->keyBy('id');

            foreach (array_values($rows) as $index => $row) {
                $id = Fields::blank($row['id'] ?? null) ? null : (int) $row['id'];
                $text = fn (string $key) => Fields::text($row[$key] ?? '');
                if ($id === null && Fields::blank($row['name'] ?? null) && Fields::blank($row['price'] ?? null) && Fields::blank($row['quantity'] ?? null)) {
                    continue;
                }
                $item = $id === null ? null : $stored->get($id);
                abort_if($id !== null && $item === null, 422, 'Select purchases of this project.');
                $field = fn (string $key) => 'rows.'.$index.'.'.$key;
                $categoryInput = Fields::blank($row['category_id'] ?? null) ? null : (int) $row['category_id'];
                $price = Money::normalize($text('price'));
                $quantity = trim($text('quantity'));
                $date = trim($text('invoice_date'));
                $unchanged = $item !== null
                    && $text('name') === (string) ($item->c_ItemName ?? '') && $text('brand') === (string) ($item->c_Brand ?? '')
                    && $categoryInput === ($item->{$categoryRef['state_column']} === 'resolved' ? (int) $item->{$categoryRef['resolved_column']} : null)
                    && $quantity === (string) ($item->c_Quantity ?? '') && Money::canonical($price) === Money::canonical($item->c_Price ?? null)
                    && $text('currency') === (string) ($item->c_Currency ?? '') && $date === Fields::day($item->c_InvoiceDate ?? null)
                    && $text('invoice_number') === (string) ($item->c_InvoiceNumber ?? '') && $text('trader') === (string) ($item->c_TraderName ?? '');
                if ($unchanged) {
                    continue;
                }
                (trim($text('name')) !== '' && mb_strlen($text('name')) <= 300) || $this->fail($field('name'), 'item_name');
                mb_strlen($text('brand')) <= 300 || $this->fail($field('brand'), 'item_text');
                mb_strlen($text('trader')) <= 300 || $this->fail($field('trader'), 'item_text');
                mb_strlen($text('invoice_number')) <= 100 || $this->fail($field('invoice_number'), 'item_text');
                (preg_match('/\A\d{1,9}\z/', $quantity) === 1) || $this->fail($field('quantity'), 'item_quantity');
                (Money::isDecimal($price, 2) && strlen(explode('.', $price)[0]) <= 10) || $this->fail($field('price'), 'item_price');
                in_array($text('currency'), self::CURRENCIES, true) || $this->fail($field('currency'), 'item_currency');
                ($date === '' || Fields::isDate($date)) || $this->fail($field('invoice_date'), 'date');
                $category = $categoryInput === null ? null : $this->tables->find('purchasingcategory', $categoryInput, $city, $source);
                ($categoryInput === null || $category !== null) || $this->fail($field('category_id'), 'item_category');
                $payload = [
                    'c_ItemName' => $text('name'), 'c_Brand' => $text('brand'), 'c_Quantity' => (int) $quantity, 'c_Price' => Money::canonical($price) ?? '0',
                    'c_Currency' => $text('currency'), 'c_InvoiceDate' => $date === '' ? null : $date, 'c_InvoiceNumber' => $text('invoice_number'), 'c_TraderName' => $text('trader'),
                ] + $this->tables->link('purchasing', 'ItemCategory_ID', $category === null ? null : (int) $category->id);
                if ($item === null) {
                    $this->tables->insert('purchasing', $city, $source, $payload + $this->tables->link('purchasing', 'MicroProject_ID', $projectId));
                } else {
                    DB::table('op_purchasing')->where('id', $item->id)->update($payload);
                }
            }
            $this->audit->record($account->id, 'purchasing.saved', 'succeeded', $city, subjectType: 'application_entity', subjectId: $projectId);
        });
    }
}
