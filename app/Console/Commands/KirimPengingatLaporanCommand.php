<?php

namespace App\Console\Commands;

use App\Services\PengingatLaporanService;
use Illuminate\Console\Command;

class KirimPengingatLaporanCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laporan:kirim-pengingat
                            {--dry-run : Jalankan simulasi pemeriksaan tanpa mengirim email atau menulis cache}
                            {--days=30 : Rentang hari kalender ke belakang yang diperiksa (default: 30 hari)}
                            {--bku= : Filter pengujian untuk ID BKU tertentu}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim email pengingat otomatis kepada operator BKU jika laporan harian belum disampaikan';

    /**
     * Execute the console command.
     */
    public function handle(PengingatLaporanService $service): int
    {
        $isDryRun = (bool) $this->option('dry-run');
        $days = (int) $this->option('days') ?: 30;
        $bkuId = $this->option('bku') ? (int) $this->option('bku') : null;

        $this->info('===========================================================');
        $this->info('  SUMAS - Pemeriksaan & Pengingat Laporan Harian Operator  ');
        $this->info('===========================================================');
        $this->line("Mode      : " . ($isDryRun ? '<comment>DRY-RUN (Simulasi - Tanpa Kirim Email)</comment>' : '<info>AKTIF (Pengiriman Nyata)</info>'));
        $this->line("Rentang   : {$days} hari kalender ke belakang (s.d. kemarin)");
        if ($bkuId) {
            $this->line("Target BKU: ID #{$bkuId}");
        }
        $this->line("Waktu     : " . now()->toDateTimeString() . " (" . config('app.timezone') . ")");
        $this->newLine();

        $stats = $service->prosesPengingat($isDryRun, $days, $bkuId);

        if (empty($stats['details'])) {
            $this->info('✓ Seluruh BKU yang diperiksa telah melengkapi seluruh kewajiban laporan harian.');
            $this->line("Total BKU diperiksa: {$stats['total_bku_examined']}");
            return Command::SUCCESS;
        }

        $tableRows = [];
        foreach ($stats['details'] as $detail) {
            $missingList = implode(', ', array_slice($detail['missing_dates'], 0, 3));
            if (count($detail['missing_dates']) > 3) {
                $missingList .= ' (+' . (count($detail['missing_dates']) - 3) . ' lainnya)';
            }

            $statusText = match ($detail['status']) {
                'sent' => '<info>TERKIRIM</info>',
                'dry_run_ready' => '<comment>SIAP KIRIM (SIMULASI)</comment>',
                'skipped_already_sent_today' => '<comment>DILEWATI (SUDAH DIKIRIM HARI INI)</comment>',
                'skipped_no_verified_operator' => '<fg=red>DILEWATI (OPERATOR BELUM TERVERIFIKASI)</fg=red>',
                'failed' => '<error>GAGAL: ' . ($detail['error'] ?? 'Terjadi kesalahan') . '</error>',
                default => $detail['status'],
            };

            $recipients = isset($detail['recipients'])
                ? implode(', ', $detail['recipients'])
                : ($detail['reason'] ?? '-');

            $tableRows[] = [
                $detail['bku_id'],
                $detail['bku_nama'],
                count($detail['missing_dates']) . " hari\n[{$missingList}]",
                $recipients,
                $statusText,
            ];
        }

        $this->table(
            ['ID BKU', 'Nama BKU', 'Laporan Tertunggak', 'Penerima / Catatan', 'Status'],
            $tableRows
        );

        $this->newLine();
        $this->info('--- Ringkasan Eksekusi ---');
        $this->line("Total BKU Diperiksa           : {$stats['total_bku_examined']}");
        $this->line("Total BKU Tertunggak          : {$stats['total_missing_bku']}");
        if ($isDryRun) {
            $this->line("Kandidat Siap Kirim (Dry-Run) : " . count(array_filter($stats['details'], fn($d) => $d['status'] === 'dry_run_ready')));
        } else {
            $this->line("Total Email Berhasil Dikirim  : {$stats['total_emails_sent']}");
            $this->line("Total Gagal Dikirim           : {$stats['total_failed']}");
        }
        $this->line("Dilewati (Sudah Kirim Hari Ini): {$stats['total_skipped_idempotent']}");
        $this->line("Dilewati (Belum Ada Operator) : {$stats['total_skipped_no_operator']}");

        return $stats['total_failed'] > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
