<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Import\Readers\FrozenJsonSourceReader;
use App\Services\ImportTargetGuard;
use App\Services\Reconciliation\IndependentReconciler;
use Illuminate\Console\Command;
use Throwable;

final class ReconcileImportSource extends Command
{
    protected $signature = 'phase3:reconcile {manifest : Private frozen source manifest path} {--report= : New JSON report filename inside storage/app/private}';

    protected $description = 'Independently compare source, preservation and shared values without publication';

    public function handle(IndependentReconciler $reconciler): int
    {
        try {
            ImportTargetGuard::check();
            $filename = $this->option('report');
            if ($filename !== null && (! preg_match('/\A[a-zA-Z0-9_-]+\.json\z/D', $filename))) {
                $this->error('Use a plain JSON report filename inside private storage.');

                return self::FAILURE;
            }
            $report = $reconciler->reconcile(new FrozenJsonSourceReader((string) $this->argument('manifest')));
            if ($filename !== null) {
                $path = storage_path('app/private/'.$filename);
                $handle = fopen($path, 'xb');
                if ($handle === false) {
                    $this->error('Report already exists or private storage is unavailable.');

                    return self::FAILURE;
                }
                try {
                    $bytes = json_encode($report, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)."\n";
                    if (fwrite($handle, $bytes) !== strlen($bytes)) {
                        throw new \RuntimeException('Incomplete private report write.');
                    }
                } finally {
                    fclose($handle);
                }
            }
            $this->line(json_encode(['mismatch_count' => $report['mismatch_count'], 'source_anomalies' => $report['source_anomalies'],
                'checked_cells' => $report['checked_cells'], 'checked_relationships' => $report['checked_relationships'],
                'core_ready' => $report['core_ready'], 'publishable' => false], JSON_THROW_ON_ERROR));

            return $report['core_ready'] ? self::SUCCESS : self::FAILURE;
        } catch (Throwable) {
            $this->error('Independent reconciliation stopped. Check private evidence; no publication was performed.');

            return self::FAILURE;
        }
    }
}
