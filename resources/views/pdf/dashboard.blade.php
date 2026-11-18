<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Dashboard SUMAS</title>

    <style>
        body {
            font-family: sans-serif;
            font-size: 10px;
            color: #292929;
        }

        h1 {
            font-size: 20px;
            text-align: center;
            margin-bottom: 5px;
        }

        .subtitle {
            text-align: center;
            margin-bottom: 20px;
            color: #666;
        }

        h3 {
            margin-top: 22px;
            margin-bottom: 8px;
            font-size: 13px;
        }

        .period {
            text-align: center;
            margin-bottom: 20px;
        }

        .stats {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .stats td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }

        .stats strong {
            display: block;
            font-size: 16px;
            margin-top: 5px;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        table.data th,
        table.data td {
            border: 1px solid #ccc;
            padding: 7px;
            text-align: left;
        }

        table.data th {
            background: #f0d34a;
            font-weight: bold;
        }

        .center {
            text-align: center !important;
        }

        .footer {
            margin-top: 25px;
            text-align: right;
            font-size: 9px;
            color: #777;
        }
    </style>
</head>

<body>

    <h1>LAPORAN DASHBOARD SUMAS</h1>

    <div class="subtitle">
        Sistem Informasi Produksi Minyak Bumi
    </div>

    <div class="period">
        Periode:
        {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }}
        -
        {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}
    </div>

    <h3>Ringkasan Statistik</h3>

    <table class="stats">
        <tr>
            <td>
                Total BKU
                <strong>{{ $stats['total_bku'] }}</strong>
            </td>
            <td>
                Total Kontrak
                <strong>{{ $stats['total_kontrak'] }}</strong>
            </td>
            <td>
                Rata-rata Produksi
                <strong>{{ number_format($stats['average_produksi'], 2, ',', '.') }}</strong>
            </td>
            <td>
                Rata-rata Lifting
                <strong>{{ number_format($stats['average_lifting'], 2, ',', '.') }}</strong>
            </td>
        </tr>
    </table>

    <h3>Produksi dan Lifting Harian</h3>

    <table class="data">
        <thead>
            <tr>
                <th class="center">No.</th>
                <th>Tanggal</th>
                <th>Total Produksi</th>
                <th>Total Lifting</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($productionLiftingChart as $index => $item)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($item['tanggal'])->translatedFormat('d F Y') }}</td>
                    <td>{{ number_format($item['total_produksi'], 2, ',', '.') }}</td>
                    <td>{{ number_format($item['total_lifting'], 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="center">
                        Tidak ada data laporan pada periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h3>Produksi Berdasarkan BKU</h3>

    <table class="data">
        <thead>
            <tr>
                <th class="center">No.</th>
                <th>Nama BKU</th>
                <th>Total Produksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($productionByBku as $index => $item)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>{{ $item['nama'] }}</td>
                    <td>{{ number_format($item['total_produksi'], 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="center">Tidak ada data.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h3>Status Pelaporan Hari Ini</h3>

    <table class="stats">
        <tr>
            <td>
                Total BKU
                <strong>{{ $reportingStatus['total_bku'] }}</strong>
            </td>
            <td>
                Sudah Melapor
                <strong>{{ $reportingStatus['sudah_melapor'] }}</strong>
            </td>
            <td>
                Belum Melapor
                <strong>{{ $reportingStatus['belum_melapor'] }}</strong>
            </td>
            <td>
                Persentase
                <strong>{{ $reportingStatus['percentage'] }}%</strong>
            </td>
        </tr>
    </table>

    <h3>BKU yang Belum Menyelesaikan Pelaporan</h3>

    <table class="data">
        <thead>
            <tr>
                <th class="center">No.</th>
                <th>Nama BKU</th>
                <th>Jumlah Kontrak Belum Melapor</th>
                <th>Kontrak</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($missingReports as $index => $item)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>{{ $item['nama'] }}</td>
                    <td>{{ $item['missing_count'] }}</td>
                    <td>
                        {{ collect($item['contracts'])->pluck('nama')->implode(', ') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="center">
                        Semua BKU telah menyelesaikan pelaporan.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h3>10 Laporan Terbaru</h3>

    <table class="data">
        <thead>
            <tr>
                <th class="center">No.</th>
                <th>Tanggal</th>
                <th>BKU</th>
                <th>Kontrak</th>
                <th>Total Produksi</th>
                <th>Total Lifting</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($latestReports as $index => $item)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($item['tanggal'])->translatedFormat('d F Y') }}</td>
                    <td>{{ $item['bku'] }}</td>
                    <td>{{ $item['kontrak'] }}</td>
                    <td>{{ number_format($item['total_produksi'], 2, ',', '.') }}</td>
                    <td>{{ number_format($item['total_lifting'], 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="center">Tidak ada laporan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada {{ now()->translatedFormat('d F Y H:i') }}
    </div>

</body>

</html>