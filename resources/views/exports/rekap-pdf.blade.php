<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<style>
@page { margin: 28px 30px; }
body { font-family: DejaVu Sans, sans-serif; color: #18313b; font-size: 9px; }
h1 { font-size: 18px; color: #123b52; margin: 0 0 5px; }
p { color: #617982; margin: 0 0 20px; }
table { width: 100%; border-collapse: collapse; table-layout: fixed; }
th { background: #123b52; color: #fff; text-align: left; padding: 8px 6px; }
td { border-bottom: 1px solid #dbe7e8; padding: 7px 6px; vertical-align: top; overflow-wrap: break-word; }
tr:nth-child(even) td { background: #f5f8f7; }
.small { font-size: 8px; }
</style>
</head>
<body>
<h1>SITERA — Rekap Laporan Irigasi</h1>
<p>Laporan terkirim · dicetak {{ now()->format('d/m/Y H:i') }} WIB</p>
<table>
<thead><tr><th style="width:16%">Nomor tiket</th><th style="width:10%">Status</th><th style="width:12%">Kejadian</th><th style="width:14%">Pelapor</th><th style="width:12%">Provinsi</th><th style="width:12%">Kabupaten</th><th style="width:14%">Jenis kerusakan</th><th style="width:10%">Dikirim</th></tr></thead>
<tbody>
@forelse ($reports as $report)
<tr><td>{{ $report->nomor_tiket }}</td><td>{{ $report->status }}</td><td>{{ $report->tanggal_kejadian?->format('d/m/Y') }}</td><td>{{ $report->user?->name }}</td><td>{{ $report->provinsi }}</td><td>{{ $report->kabupaten }}</td><td>{{ $report->jenis_kerusakan }}</td><td>{{ $report->dikirim_pada?->format('d/m/Y') }}</td></tr>
@empty
<tr><td colspan="8">Tidak ada laporan terkirim pada periode ini.</td></tr>
@endforelse
</tbody>
</table>
</body>
</html>
