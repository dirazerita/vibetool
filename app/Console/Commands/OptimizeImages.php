<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class OptimizeImages extends Command
{
    protected $signature = 'images:optimize
        {--dry-run : Tampilkan rencana tanpa mengubah file apa pun}
        {--quality=80 : Kualitas encode JPEG/WebP (1-100)}
        {--force : Lewati konfirmasi}';

    protected $description = 'Kompres ulang & resize gambar lama di storage publik (in-place, aman, dengan backup)';

    /**
     * Batas dimensi maksimum per jenis folder.
     */
    private function maxDimsFor(string $path): array
    {
        $p = strtolower($path);

        if (str_contains($p, 'avatars/')) {
            return [400, 400];
        }
        if (str_contains($p, 'gallery/')) {
            return [1000, 1000];
        }
        if (str_starts_with($p, 'landing-pages/')) {
            return [1200, 1200];
        }
        if (str_starts_with($p, 'products/')) {
            return [800, 800];
        }
        if (str_starts_with($p, 'payment_proofs/')) {
            return [1600, 1600];
        }

        return [1600, 1600];
    }

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $quality = max(1, min(100, (int) $this->option('quality')));

        $disk = Storage::disk('public');
        $manager = new ImageManager(new Driver());

        $backupDir = 'image-backups/' . now()->format('Ymd_His');
        $minSavingBytes = 10 * 1024; // minimal hemat 10 KB agar tidak menulis ulang sia-sia

        $allFiles = $disk->allFiles();
        $targets = array_values(array_filter($allFiles, function ($p) {
            $ext = strtolower(pathinfo($p, PATHINFO_EXTENSION));

            return in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true);
        }));

        if (count($targets) === 0) {
            $this->info('Tidak ada gambar yang ditemukan di disk publik.');

            return self::SUCCESS;
        }

        $this->info(($dryRun ? '[DRY-RUN] ' : '') . 'Memindai ' . count($targets) . ' gambar di storage publik...');

        if (! $dryRun && ! $this->option('force')) {
            $this->warn('Command ini akan menimpa file gambar yang bisa dikecilkan. Backup otomatis disimpan ke ' . Storage::disk('local')->path($backupDir));
            if (! $this->confirm('Lanjutkan?', true)) {
                $this->line('Dibatalkan.');

                return self::SUCCESS;
            }
        }

        $processed = 0;
        $skipped = 0;
        $failed = 0;
        $totalBefore = 0;
        $totalAfter = 0;

        $bar = $dryRun ? null : $this->output->createProgressBar(count($targets));
        $bar?->start();

        foreach ($targets as $path) {
            $bar?->advance();

            try {
                $fullPath = $disk->path($path);
                if (! is_file($fullPath)) {
                    $skipped++;
                    continue;
                }

                $sizeBefore = filesize($fullPath);
                [$maxW, $maxH] = $this->maxDimsFor($path);

                $image = $manager->read($fullPath);
                $image->scaleDown($maxW, $maxH);

                $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                $encoded = (string) match ($ext) {
                    'png' => $image->toPng(),
                    'webp' => $image->toWebp($quality),
                    default => $image->toJpeg($quality),
                };

                $sizeAfter = strlen($encoded);

                // Hanya proses bila benar-benar lebih kecil dengan selisih berarti.
                if ($sizeAfter >= $sizeBefore || ($sizeBefore - $sizeAfter) < $minSavingBytes) {
                    $skipped++;
                    continue;
                }

                $totalBefore += $sizeBefore;
                $totalAfter += $sizeAfter;
                $processed++;

                if ($dryRun) {
                    $this->line(sprintf(
                        '  [akan optimasi] %s : %s KB -> %s KB',
                        $path,
                        number_format($sizeBefore / 1024, 1),
                        number_format($sizeAfter / 1024, 1)
                    ));
                    continue;
                }

                // Backup file asli SEBELUM ditimpa.
                Storage::disk('local')->put($backupDir . '/' . $path, file_get_contents($fullPath));

                // Tulis versi terkompres ke path yang sama (path DB tidak berubah).
                file_put_contents($fullPath, $encoded);
            } catch (\Throwable $e) {
                $failed++;
                $this->newLine();
                $this->warn('  Gagal: ' . $path . ' — ' . $e->getMessage());
            }
        }

        $bar?->finish();
        $this->newLine(2);

        $savedKb = ($totalBefore - $totalAfter) / 1024;

        if ($dryRun) {
            $this->info('Ringkasan DRY-RUN (tidak ada file yang diubah):');
        } else {
            $this->info('Selesai. Backup file asli ada di: ' . Storage::disk('local')->path($backupDir));
        }

        $this->table(
            ['Dioptimasi', 'Dilewati', 'Gagal', 'Hemat'],
            [[
                $processed,
                $skipped,
                $failed,
                number_format($savedKb, 1) . ' KB (' . number_format($savedKb / 1024, 2) . ' MB)',
            ]]
        );

        if ($dryRun) {
            $this->line('Jalankan tanpa --dry-run untuk menerapkan.');
        }

        return self::SUCCESS;
    }
}
