<?php

use App\Mail\PengingatLaporanHarianMail;
use App\Models\Bku;
use App\Models\BkuKontrak;
use App\Models\Kontrak;
use App\Models\LaporanHarian;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    // Simulasi waktu tetap: Selasa, 3 November 2026 08:00 WIB
    // Kemarin adalah Senin, 2 November 2026 (hari kerja)
    Carbon::setTestNow(Carbon::parse('2026-11-03 08:00:00'));
    Cache::flush();
});

afterEach(function () {
    Carbon::setTestNow();
});

function createTestSetup(bool $verified = true, ?string $email = 'operator@example.com'): array
{
    $bku = Bku::create([
        'nama' => 'BKU Musi Jaya',
        'penetapan' => 500,
        'kota' => 'Palembang',
    ]);

    $kontrak = Kontrak::create([
        'nama' => 'Kontrak Blok A',
    ]);

    // Kontrak dibuat 1 Oktober 2026 (sebelum periode pemeriksaan November)
    $bkuKontrak = BkuKontrak::create([
        'bku_id' => $bku->id,
        'kontrak_id' => $kontrak->id,
        'jumlah_sumur' => 5,
    ]);

    DB::table('bku_kontrak')
        ->where('id', $bkuKontrak->id)
        ->update(['created_at' => '2026-10-01 00:00:00']);

    $bkuKontrak->refresh();

    $user = new User([
        'name' => 'Budi Operator',
        'email' => $email ?? 'noemail@example.com',
        'password' => bcrypt('password'),
        'role' => 'operator_bku',
        'bku_id' => $bku->id,
    ]);
    $user->email_verified_at = $verified ? Carbon::parse('2026-10-01 10:00:00') : null;
    $user->save();

    if ($email === '') {
        DB::table('users')->where('id', $user->id)->update(['email' => '']);
        $user->refresh();
    }

    return compact('bku', 'kontrak', 'bkuKontrak', 'user');
}

test('1. BKU sudah memenuhi kewajiban laporan: tidak ada email yang dikirim', function () {
    Mail::fake();
    $setup = createTestSetup();

    // Buat laporan untuk kemarin (Senin, 2 November 2026)
    LaporanHarian::create([
        'bku_kontrak_id' => $setup['bkuKontrak']->id,
        'tanggal' => '2026-11-02',
        'total_produksi' => 150.00,
        'total_lifting' => 140.00,
    ]);

    // Jalankan command hanya untuk 1 hari kerja ke belakang (kemarin)
    Artisan::call('laporan:kirim-pengingat', ['--days' => 1]);

    Mail::assertNothingSent();
});

test('2. Tanggal laporan masih berjalan (hari ini): tidak ada email yang dikirim', function () {
    Mail::fake();
    $setup = createTestSetup();

    // Laporan kemarin sudah diisi
    LaporanHarian::create([
        'bku_kontrak_id' => $setup['bkuKontrak']->id,
        'tanggal' => '2026-11-02',
        'total_produksi' => 100,
        'total_lifting' => 100,
    ]);

    // Sekarang adalah Selasa, 2026-11-03 08:00:00.
    // Laporan untuk 2026-11-03 belum ada karena hari masih berjalan.
    // Tidak boleh ada pengingat untuk hari ini.
    Artisan::call('laporan:kirim-pengingat', ['--days' => 1]);

    Mail::assertNothingSent();
});

test('3. Laporan sudah melewati batas dan belum tersedia: email pengingat dikirim', function () {
    Mail::fake();
    $setup = createTestSetup();

    // Kemarin (Senin, 2026-11-02) belum ada laporan sama sekali
    Artisan::call('laporan:kirim-pengingat', ['--days' => 1]);

    Mail::assertSent(PengingatLaporanHarianMail::class, function ($mail) use ($setup) {
        return $mail->hasTo($setup['user']->email) &&
               $mail->bku->id === $setup['bku']->id &&
               in_array('2026-11-02', $mail->missingDates);
    });
});

test('4. Hari Sabtu dan Minggu tidak dihitung (dilewati dari perhitungan tunggakan)', function () {
    Mail::fake();
    $setup = createTestSetup();

    // Hari ini Selasa 2026-11-03.
    // Rentang 4 hari kalender ke belakang mencakup:
    // - 2026-10-30 (Jumat - hari kerja)
    // - 2026-10-31 (Sabtu - akhir pekan) -> HARUS DILEWATI
    // - 2026-11-01 (Minggu - akhir pekan) -> HARUS DILEWATI
    // - 2026-11-02 (Senin - hari kerja)
    Artisan::call('laporan:kirim-pengingat', ['--days' => 4]);

    Mail::assertSent(PengingatLaporanHarianMail::class, function ($mail) {
        // Sabtu dan Minggu tidak boleh masuk ke missingDates
        $containsWeekend = in_array('2026-10-31', $mail->missingDates) ||
                           in_array('2026-11-01', $mail->missingDates);

        // Hari kerja (Jumat & Senin) harus ada
        $containsWeekdays = in_array('2026-10-30', $mail->missingDates) &&
                            in_array('2026-11-02', $mail->missingDates);

        return ! $containsWeekend && $containsWeekdays;
    });
});

test('5. Email operator belum terverifikasi: email tidak dikirim', function () {
    Mail::fake();
    // Buat akun dengan email_verified_at = null
    createTestSetup(verified: false);

    Artisan::call('laporan:kirim-pengingat', ['--days' => 1]);

    Mail::assertNothingSent();
});

test('6. Email operator kosong: email tidak dikirim dan alasan tercatat di log', function () {
    Mail::fake();
    Log::shouldReceive('warning')->atLeast()->once();
    Log::shouldReceive('info')->zeroOrMoreTimes();

    createTestSetup(verified: true, email: '');

    Artisan::call('laporan:kirim-pengingat', ['--days' => 1]);

    Mail::assertNothingSent();
});

test('7. Laporan backfill yang masuk setelah pengingat: pengingat untuk tanggal tersebut berhenti', function () {
    Mail::fake();
    $setup = createTestSetup();

    // Pemeriksaan hari pertama: email terkirim karena 2026-11-02 (Senin) kosong
    Artisan::call('laporan:kirim-pengingat', ['--days' => 1]);
    Mail::assertSent(PengingatLaporanHarianMail::class, 1);

    // Operator mengisi laporan susulan (backfill) untuk tanggal 2026-11-02
    LaporanHarian::create([
        'bku_kontrak_id' => $setup['bkuKontrak']->id,
        'tanggal' => '2026-11-02',
        'total_produksi' => 200,
        'total_lifting' => 190,
    ]);

    // Reset status cache dan clock maju ke esok hari (Rabu, 2026-11-04)
    Carbon::setTestNow(Carbon::parse('2026-11-04 08:00:00'));
    Cache::flush();
    Mail::fake();

    // Isi juga laporan kemarin (Selasa, 2026-11-03) agar tidak ada tunggakan lain
    LaporanHarian::create([
        'bku_kontrak_id' => $setup['bkuKontrak']->id,
        'tanggal' => '2026-11-03',
        'total_produksi' => 210,
        'total_lifting' => 200,
    ]);

    // Jalankan pemeriksaan kembali untuk 2 hari kerja terakhir (2 Nov & 3 Nov)
    Artisan::call('laporan:kirim-pengingat', ['--days' => 2]);

    // Pengingat untuk 2026-11-02 harus berhenti (tidak dikirim lagi)
    Mail::assertNothingSent();
});

test('8. Pengingat dapat dikirim lagi pada hari berikutnya jika laporan tetap belum ada', function () {
    Mail::fake();
    createTestSetup();

    // Hari 1: 2026-11-03 08:00 WIB (Senin 2 Nov kosong)
    Artisan::call('laporan:kirim-pengingat', ['--days' => 1]);
    Mail::assertSent(PengingatLaporanHarianMail::class, 1);

    // Laporan masih belum dibuat, lalu hari berganti ke 2026-11-04 08:00 WIB
    Carbon::setTestNow(Carbon::parse('2026-11-04 08:00:00'));
    Mail::fake(); // Reset mock

    // Jalankan kembali pengingat
    Artisan::call('laporan:kirim-pengingat', ['--days' => 2]);

    // Email pengingat harus dikirim kembali untuk hari yang baru
    Mail::assertSent(PengingatLaporanHarianMail::class, 1);
});

test('9. Command dijalankan ulang pada hari yang sama tidak mengirim duplikat (idempotensi)', function () {
    Mail::fake();
    createTestSetup();

    // Eksekusi pertama
    Artisan::call('laporan:kirim-pengingat', ['--days' => 1]);
    Mail::assertSent(PengingatLaporanHarianMail::class, 1);

    // Eksekusi kedua pada hari yang sama (misal pukul 09:00 atau scheduler re-run)
    Carbon::setTestNow(Carbon::parse('2026-11-03 09:00:00'));
    Mail::fake(); // Reset mock

    Artisan::call('laporan:kirim-pengingat', ['--days' => 1]);

    // Tidak boleh mengirim duplikat
    Mail::assertNothingSent();
});

test('10. Kegagalan pengiriman tidak ditandai sebagai sukses dan dapat dicoba kembali', function () {
    $setup = createTestSetup();

    // Simulasikan exception saat Mail::send dipanggil
    Mail::shouldReceive('to')
        ->once()
        ->andThrow(new \Exception('SMTP Connection Timed Out'));

    Log::shouldReceive('error')->atLeast()->once();
    Log::shouldReceive('warning')->zeroOrMoreTimes();
    Log::shouldReceive('info')->zeroOrMoreTimes();

    Artisan::call('laporan:kirim-pengingat', ['--days' => 1]);

    // Pastikan key idempotensi TIDAK tersimpan karena gagal kirim
    $today = Carbon::today()->toDateString();
    $key = "pengingat_laporan:bku:{$setup['bku']->id}:sent:{$today}";
    expect(Cache::has($key))->toBeFalse();
});

test('11. Opsi --dry-run menjalankan seluruh logika tanpa mengirim email atau menulis cache', function () {
    Mail::fake();
    $setup = createTestSetup();

    Artisan::call('laporan:kirim-pengingat', ['--dry-run' => true, '--days' => 1]);

    // Tidak ada email yang dikirim
    Mail::assertNothingSent();

    // Cache idempotensi tidak boleh ditulis saat dry run
    $today = Carbon::today()->toDateString();
    $key = "pengingat_laporan:bku:{$setup['bku']->id}:sent:{$today}";
    expect(Cache::has($key))->toBeFalse();
});
