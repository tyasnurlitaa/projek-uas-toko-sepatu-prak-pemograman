<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$result = $conn->query('SELECT * FROM sepatu ORDER BY created_at DESC');
$items = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

$transaksiResult = $conn->query('SELECT p.nama_pembeli, s.nama AS nama_sepatu, p.jumlah, p.total_harga, p.created_at FROM pembelian p JOIN sepatu s ON s.id = p.sepatu_id ORDER BY p.created_at DESC');
$transactions = $transaksiResult ? $transaksiResult->fetch_all(MYSQLI_ASSOC) : [];

$pengeluaranResult = $conn->query('SELECT * FROM pengeluaran ORDER BY created_at DESC');
$expenses = $pengeluaranResult ? $pengeluaranResult->fetch_all(MYSQLI_ASSOC) : [];

$stokKeluarResult = $conn->query('SELECT sk.jumlah, sk.keterangan, sk.created_at, s.nama AS nama_sepatu FROM stok_keluar sk JOIN sepatu s ON s.id = sk.sepatu_id ORDER BY sk.created_at DESC');
$stokKeluar = $stokKeluarResult ? $stokKeluarResult->fetch_all(MYSQLI_ASSOC) : [];

$stokMasuk = $transactions;

$html = '<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Toko Sepatu</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #4A3525; background: #FAF6F0; padding: 20px; }
        .header { background: linear-gradient(135deg, #C08A7C 0%, #7F5539 100%); color: white; padding: 20px; border-radius: 16px; margin-bottom: 20px; }
        h1 { margin: 0 0 8px 0; font-size: 22px; }
        .sub { margin: 0; font-size: 12px; opacity: 0.95; }
        .summary { background: #FFF9F2; border: 1px solid #E8D9C8; border-radius: 12px; padding: 10px 14px; margin-bottom: 16px; }
        h3 { color: #7F5539; margin: 16px 0 8px 0; font-size: 15px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 18px; font-size: 11px; }
        th { background: #7F5539; color: white; padding: 8px; text-align: left; }
        td { border: 1px solid #E8D9C8; padding: 8px; text-align: left; }
        tr:nth-child(even) { background: #FCF7F1; }
        .badge { background: #C08A7C; color: white; padding: 3px 7px; border-radius: 10px; font-size: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Toko Sepatu</h1>
        <p class="sub">Laporan resmi inventory, penjualan, dan pengeluaran toko</p>
    </div>
    <div class="summary">Tanggal: ' . date('d-m-Y') . ' &nbsp;|&nbsp; Total Produk: ' . count($items) . ' &nbsp;|&nbsp; Total Pengeluaran: Rp ' . number_format(array_sum(array_map(fn($item) => (int)$item['nominal'], $expenses)), 0, ',', '.') . '</div>
    <h3>Data Stok Sepatu</h3>
    <table>
        <thead>
            <tr>
                <th>Nama</th>
                <th>Merek</th>
                <th>Ukuran</th>
                <th>Harga</th>
                <th>Stok</th>
            </tr>
        </thead>
        <tbody>';

foreach ($items as $item) {
    $html .= '<tr>';
    $html .= '<td>' . htmlspecialchars($item['nama']) . '</td>';
    $html .= '<td>' . htmlspecialchars($item['merek']) . '</td>';
    $html .= '<td>' . htmlspecialchars($item['ukuran']) . '</td>';
    $html .= '<td>Rp ' . number_format($item['harga'], 0, ',', '.') . '</td>';
    $html .= '<td>' . (int)$item['stok'] . '</td>';
    $html .= '</tr>';
}

$html .= '</tbody></table><h3>Stok Masuk</h3><table><thead><tr><th>Pembeli</th><th>Sepatu</th><th>Jumlah</th><th>Total</th><th>Waktu</th></tr></thead><tbody>';

foreach ($stokMasuk as $trx) {
    $html .= '<tr>';
    $html .= '<td>' . htmlspecialchars($trx['nama_pembeli']) . '</td>';
    $html .= '<td>' . htmlspecialchars($trx['nama_sepatu']) . '</td>';
    $html .= '<td>' . (int)$trx['jumlah'] . '</td>';
    $html .= '<td>Rp ' . number_format((int)$trx['total_harga'], 0, ',', '.') . '</td>';
    $html .= '<td>' . htmlspecialchars($trx['created_at']) . '</td>';
    $html .= '</tr>';
}

$html .= '</tbody></table><h3>Catatan Pengeluaran</h3><table><thead><tr><th>Kategori</th><th>Keterangan</th><th>Nominal</th><th>Waktu</th></tr></thead><tbody>';

foreach ($expenses as $expense) {
    $html .= '<tr>';
    $html .= '<td>' . htmlspecialchars($expense['kategori']) . '</td>';
    $html .= '<td>' . htmlspecialchars($expense['keterangan']) . '</td>';
    $html .= '<td>Rp ' . number_format((int)$expense['nominal'], 0, ',', '.') . '</td>';
    $html .= '<td>' . htmlspecialchars($expense['created_at']) . '</td>';
    $html .= '</tr>';
}

$html .= '</tbody></table><h3>Stok Keluar</h3><table><thead><tr><th>Sepatu</th><th>Jumlah</th><th>Keterangan</th><th>Waktu</th></tr></thead><tbody>';

foreach ($stokKeluar as $item) {
    $html .= '<tr>';
    $html .= '<td>' . htmlspecialchars($item['nama_sepatu']) . '</td>';
    $html .= '<td>' . (int)$item['jumlah'] . '</td>';
    $html .= '<td>' . htmlspecialchars($item['keterangan']) . '</td>';
    $html .= '<td>' . htmlspecialchars($item['created_at']) . '</td>';
    $html .= '</tr>';
}

$html .= '</tbody></table></body></html>';

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream('laporan-toko-sepatu.pdf', ['Attachment' => false]);
