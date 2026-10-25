<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmailOtpVerification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class EmailOtpVerificationController extends Controller
{
    /**
     * Menampilkan halaman verifikasi email.
     */
    public function show(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        if ($user->role !== 'operator_bku') {
            return redirect()->route('dashboard');
        }

        if ($user->email_verified_at !== null) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('auth/VerifyEmailOtp', [
            'status' => session('status'),
        ]);
    }

    /**
     * Mengirim kode OTP ke email aktif yang dimasukkan operator.
     */
    public function send(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->role === 'operator_bku', 403);

        if ($user->email_verified_at !== null) {
            return redirect()->route('dashboard');
        }

        $validated = $request->validate([
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
        ]);

        $otpRecord = EmailOtpVerification::where(
            'user_id',
            $user->id
        )->first();

        // Batasi pengiriman ulang menjadi sekali per 60 detik.
        if (
            $otpRecord?->last_sent_at !== null &&
            $otpRecord->last_sent_at->gt(now()->subSeconds(60))
        ) {
            $remaining = max(
                1,
                60 - now()->diffInSeconds($otpRecord->last_sent_at)
            );

            return back()->withErrors([
                'email' => "Silakan tunggu {$remaining} detik sebelum meminta kode baru.",
            ]);
        }

        $otp = (string) random_int(100000, 999999);

        // Simpan hash OTP, bukan kode aslinya.
        EmailOtpVerification::updateOrCreate(
            ['user_id' => $user->id],
            [
                'pending_email' => $validated['email'],
                'code_hash' => Hash::make($otp),
                'attempts' => 0,
                'expires_at' => now()->addMinutes(10),
                'last_sent_at' => now(),
            ]
        );

        Mail::raw(
            "Halo {$user->name},\n\n"
            . "Kode OTP verifikasi email SUMAS Anda adalah: {$otp}\n\n"
            . "Kode berlaku selama 10 menit. Jangan bagikan kode ini kepada siapa pun.\n\n"
            . "Jika Anda tidak meminta kode ini, abaikan email ini.",
            function ($message) use ($validated) {
                $message
                    ->to($validated['email'])
                    ->subject('Kode Verifikasi Email SUMAS');
            }
        );

        return back()->with(
            'status',
            'Kode OTP telah dikirim ke email Anda.'
        );
    }

    /**
     * Memvalidasi OTP dan mengaktifkan email operator.
     */
    public function verify(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->role === 'operator_bku', 403);

        if ($user->email_verified_at !== null) {
            return redirect()->route('dashboard');
        }

        $validated = $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $otpRecord = EmailOtpVerification::where(
            'user_id',
            $user->id
        )->first();

        if (!$otpRecord) {
            return back()->withErrors([
                'otp' => 'Silakan minta kode OTP terlebih dahulu.',
            ]);
        }

        if ($otpRecord->expires_at->isPast()) {
            $otpRecord->delete();

            return back()->withErrors([
                'otp' => 'Kode OTP sudah kedaluwarsa. Silakan minta kode baru.',
            ]);
        }

        if ($otpRecord->attempts >= 5) {
            $otpRecord->delete();

            return back()->withErrors([
                'otp' => 'Batas percobaan habis. Silakan minta kode OTP baru.',
            ]);
        }

        if (!Hash::check($validated['otp'], $otpRecord->code_hash)) {
            $otpRecord->increment('attempts');

            if ($otpRecord->fresh()->attempts >= 5) {
                $otpRecord->delete();

                return back()->withErrors([
                    'otp' => 'Batas percobaan habis. Silakan minta kode OTP baru.',
                ]);
            }

            return back()->withErrors([
                'otp' => 'Kode OTP salah. Silakan periksa kembali.',
            ]);
        }

        DB::transaction(function () use ($user, $otpRecord) {
            $user->email = $otpRecord->pending_email;
            $user->email_verified_at = now();
            $user->save();

            $otpRecord->delete();
        });

        return redirect()
            ->route('dashboard')
            ->with('success', 'Email berhasil diverifikasi.');
    }
}
