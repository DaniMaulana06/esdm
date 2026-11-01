<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengingat Laporan Produksi Harian - SUMAS</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f3f4f6; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1f2937; line-height: 1.6;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f3f4f6; padding: 30px 15px;">
        <tr>
            <td align="center">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background-color: #0f172a; padding: 24px 30px; text-align: left; border-bottom: 4px solid #f59e0b;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 20px; font-weight: 700; letter-spacing: 0.5px;">
                                SUMAS
                            </h1>
                            <p style="margin: 4px 0 0; color: #94a3b8; font-size: 13px;">
                                Aplikasi Pelaporan Produksi & Lifting Harian Sumur Minyak Rakyat
                            </p>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 30px;">
                            <p style="margin: 0 0 16px; font-size: 15px;">
                                Yth. <strong>{{ $user->name }}</strong> (Operator <strong>{{ $bku->nama }}</strong>),
                            </p>

                            <p style="margin: 0 0 20px; font-size: 14px; color: #374151;">
                                Melalui pemberitahuan otomatis ini, kami menginformasikan bahwa sistem mencatat <strong>laporan produksi dan lifting harian</strong> untuk BKU Anda belum disampaikan pada tanggal-tanggal berikut:
                            </p>

                            <!-- Daftar Tanggal -->
                            <div style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 14px 18px; border-radius: 4px; margin-bottom: 24px;">
                                <p style="margin: 0 0 8px; font-weight: 600; font-size: 13px; color: #92400e; text-transform: uppercase; letter-spacing: 0.5px;">
                                    Daftar Tanggal Belum Dilaporkan:
                                </p>
                                <ul style="margin: 0; padding-left: 20px; color: #78350f; font-size: 14px; line-height: 1.8;">
                                    @foreach($missingDates as $date)
                                        <li>
                                            <strong>{{ \Carbon\Carbon::parse($date)->locale('id')->translatedFormat('l, d F Y') }}</strong>
                                            <span style="color: #a16207; font-size: 12px;">({{ $date }})</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>

                            <p style="margin: 0 0 20px; font-size: 14px; color: #4b5563;">
                                Sesuai dengan ketentuan operasional, laporan wajib disampaikan setiap hari kerja (Senin s.d. Jumat). Mohon segera masuk ke sistem dan melengkapi pelaporan harian atau memanfaatkan fitur pelaporan susulan (<em>backfill</em>).
                            </p>


                            <!-- CTA Button -->
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="margin: 28px 0 24px;">
                                <tr>
                                    <td align="center" style="border-radius: 6px; background-color: #2563eb;">
                                        <a href="{{ url('/login') }}" target="_blank" style="font-size: 14px; font-weight: 600; color: #ffffff; text-decoration: none; padding: 12px 24px; display: inline-block; border-radius: 6px;">
                                            Masuk ke Aplikasi SUMAS &rarr;
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 0 0 10px; font-size: 13px; color: #6b7280;">
                                URL Aplikasi: <a href="{{ config('app.url') }}" style="color: #2563eb; text-decoration: underline;">{{ config('app.url') }}</a>
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8fafc; padding: 20px 30px; border-top: 1px solid #e2e8f0; font-size: 12px; color: #64748b; line-height: 1.5;">
                            <p style="margin: 0 0 6px; font-weight: 600; color: #475569;">
                                Dinas Energi dan Sumber Daya Mineral (ESDM) Provinsi Sumatera Selatan
                            </p>
                            <p style="margin: 0;">
                                Email ini dikirimkan secara otomatis oleh sistem scheduler SUMAS. Pengingat ini akan dikirimkan setiap hari selama laporan pada tanggal di atas belum tersimpan di sistem.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
