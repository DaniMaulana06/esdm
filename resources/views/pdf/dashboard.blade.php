<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Produksi Minyak Bumi</title>

    <style>
        {!! file_get_contents(public_path('css/dashboard.pdf.css')) !!}
    </style>
</head>

<body>

    <img src="{{ public_path('images/logo_sumsel.png') }}" class="watermark">

    @php
        $formatAngka = fn($angka) => number_format($angka ?? 0, 2, ',', '.');

        $tanggalAwal = \Carbon\Carbon::parse($startDate);
        $tanggalAkhir = \Carbon\Carbon::parse($endDate);

        if ($tanggalAwal->format('Y-m') === $tanggalAkhir->format('Y-m')) {
            $periode = $tanggalAwal->translatedFormat('F Y');
        } elseif ($tanggalAwal->format('Y') === $tanggalAkhir->format('Y')) {
            $periode = $tanggalAwal->translatedFormat('F')
                . ' - ' .
                $tanggalAkhir->translatedFormat('F Y');
        } else {
            $periode = $tanggalAwal->translatedFormat('F Y')
                . ' - ' .
                $tanggalAkhir->translatedFormat('F Y');
        }
    @endphp

    <h2 class="report-title">
        REKAPITULASI HARIAN PRODUKSI MINYAK BUMI DARI SUMUR MASYARAKAT
    </h2>

    <div class="report-period">
        Periode {{ $periode }}
    </div>

    @foreach ($rekapPages as $pageIndex => $page)

        <table class="recap-table">

            <thead>
                <tr>
                    <th rowspan="3">NO</th>
                    <th rowspan="3">BKU</th>
                    <th colspan="3">TOTAL SUMUR MINYAK</th>
                    <th colspan="4">TOTAL PRODUKSI</th>
                    <th colspan="3">TOTAL LIFTING (BARREL)</th>
                    <th rowspan="3">TOTAL<br>(BARREL)</th>
                </tr>

                <tr>
                    <th rowspan="2">PENETAPAN</th>
                    <th colspan="2">KONTRAK</th>
                    <th rowspan="2">TANGGAL</th>
                    <th rowspan="2">PERTAMINA</th>
                    <th rowspan="2">MEDCO</th>
                    <th rowspan="2">BARREL</th>
                    <th rowspan="2">TANGGAL</th>
                    <th rowspan="2">PERTAMINA</th>
                    <th rowspan="2">MEDCO</th>
                </tr>

                <tr>
                    <th>PERTAMINA</th>
                    <th>MEDCO</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($page as $segment)

                    @php
                        $bku = $segment['bku'];
                        $segmentDates = $segment['dates'];
                        $rowspan = $segmentDates->count();
                    @endphp

                    @foreach ($segmentDates as $dateIndex => $date)

                        @php
                            $tanggalKey = $date->format('Y-m-d');
                            $harian = $bku['harian'][$tanggalKey] ?? [];

                            $produksiPertamina = $harian['produksi_pertamina'] ?? 0;
                            $produksiMedco = $harian['produksi_medco'] ?? 0;

                            $liftingPertamina = $harian['lifting_pertamina'] ?? 0;
                            $liftingMedco = $harian['lifting_medco'] ?? 0;
                        @endphp

                        <tr>
                            @if ($dateIndex === 0)
                                <td rowspan="{{ $rowspan }}" class="center">
                                    {{ $segment['nomor'] }}
                                </td>

                                <td rowspan="{{ $rowspan }}" class="bku-name">
                                    {{ $bku['nama'] }}
                                </td>

                                <td rowspan="{{ $rowspan }}" class="center">
                                    {{ number_format($bku['penetapan'], 0, ',', '.') }}
                                </td>

                                <td rowspan="{{ $rowspan }}" class="center">
                                    {{ number_format($bku['sumur_pertamina'], 0, ',', '.') }}
                                </td>

                                <td rowspan="{{ $rowspan }}" class="center">
                                    {{ number_format($bku['sumur_medco'], 0, ',', '.') }}
                                </td>
                            @endif

                            <td class="center">
                                {{ $date->translatedFormat('j F') }}
                            </td>

                            <td class="number">
                                {{ $formatAngka($produksiPertamina) }}
                            </td>

                            <td class="number">
                                {{ $formatAngka($produksiMedco) }}
                            </td>

                            <td class="number">
                                {{ $formatAngka($produksiPertamina + $produksiMedco) }}
                            </td>

                            <td class="center">
                                {{ $date->translatedFormat('j F') }}
                            </td>

                            <td class="number">
                                {{ $formatAngka($liftingPertamina) }}
                            </td>

                            <td class="number">
                                {{ $formatAngka($liftingMedco) }}
                            </td>

                            @if ($dateIndex === 0)
                                <td rowspan="{{ $rowspan }}" class="number total-cell">
                                    {{ $formatAngka($bku['total_lifting']) }}
                                </td>
                            @endif
                        </tr>
                    @endforeach
                @endforeach

                @if ($loop->last)
                    <tr class="grand-total">
                        <td colspan="5" class="center">
                            TOTAL KESELURUHAN
                        </td>
                        <td colspan="3" class="number">
                            {{ $formatAngka($totalProduksi) }}
                        </td>
                        <td colspan="4" class="number">
                            {{ $formatAngka($totalLifting) }}
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>

        @if (!$loop->last)
            <div class="page-break"></div>
        @endif

    @endforeach

    <div class="footer">
        Dicetak pada {{ now()->translatedFormat('d F Y H:i') }}
    </div>

</body>

</html>