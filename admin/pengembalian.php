<?php
include '../config/auth.php';
include '../config/database.php';
include '../includes/table_tools.php';
cekRole('admin');

$q_param = isset($_GET['q']) ? trim($_GET['q']) : '';
$from = isset($_GET['from']) ? trim($_GET['from']) : '';
$to = isset($_GET['to']) ? trim($_GET['to']) : '';

echo '<h2>Data Pengembalian</h2>';

// render search + export buttons
render_table_tools('pengembalian', $q_param, $from, $to);

// build sql with prepared params
list($where_sql, $types, $values) = build_filter_sql_and_params('pengembalian', ['q'=>$q_param,'from'=>$from,'to'=>$to]);

$sql = "SELECT pk.id_kembali, pk.tanggal_dikembalikan, pk.terlambat, pk.denda, p.jumlah, p.tanggal_pinjam, p.tanggal_kembali, u.nama AS nama_peminjam, a.nama_alat
FROM pengembalian pk
JOIN peminjaman p ON pk.id_pinjam = p.id_pinjam
JOIN `user` u ON p.id_user = u.id_user
JOIN alat a ON p.id_alat = a.id_alat
" . ($where_sql ? ' ' . $where_sql : '') . " ORDER BY pk.tanggal_dikembalikan DESC";

$res = false;
if ($stmt = mysqli_prepare($conn, $sql)) {
    if ($types) {
        mysqli_stmt_bind_param($stmt, $types, ...$values);
    }
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
} else {
    $res = mysqli_query($conn, $sql);
}

echo '<table border="1" style="width: 100%; border-collapse: collapse;">';
echo '<tr style="background-color: #f2f2f2;">
    <th style="padding: 10px; text-align: left;">Peminjam</th>
    <th style="padding: 10px; text-align: left;">Alat</th>
    <th style="padding: 10px; text-align: left;">Jumlah</th>
    <th style="padding: 10px; text-align: left;">Tanggal Dikembalikan</th>
    <th style="padding: 10px; text-align: left;">Terlambat</th>
    <th style="padding: 10px; text-align: left;">Denda</th>
</tr>';

if ($res && mysqli_num_rows($res) > 0) {
    while ($r = mysqli_fetch_assoc($res)) {
        $nama_peminjam = $r['nama_peminjam'] ?? 'N/A';
        $nama_alat = $r['nama_alat'] ?? 'N/A';
        $jumlah = $r['jumlah'] ?? '0';
        $tanggal_dikembalikan = $r['tanggal_dikembalikan'] ?? '-';
        $terlambat = isset($r['terlambat']) ? ($r['terlambat'] ? '✓ Ya' : 'Tidak') : '-';
        $denda = isset($r['denda']) ? 'Rp ' . number_format($r['denda'], 0, ',', '.') : 'Tidak Ada';
        $terlambat_color = isset($r['terlambat']) && $r['terlambat'] ? 'color: red; font-weight: bold;' : '';
        echo '<tr>';
        echo '<td style="padding: 10px; border-bottom: 1px solid #ddd;">' . htmlspecialchars($nama_peminjam) . '</td>';
        echo '<td style="padding: 10px; border-bottom: 1px solid #ddd;">' . htmlspecialchars($nama_alat) . '</td>';
        echo '<td style="padding: 10px; border-bottom: 1px solid #ddd;">' . $jumlah . '</td>';
        echo '<td style="padding: 10px; border-bottom: 1px solid #ddd;">' . $tanggal_dikembalikan . '</td>';
        echo '<td style="padding: 10px; border-bottom: 1px solid #ddd; ' . $terlambat_color . '">' . $terlambat . '</td>';
        echo '<td style="padding: 10px; border-bottom: 1px solid #ddd;">' . $denda . '</td>';
        echo '</tr>';
    }
} else {
    echo "<tr><td colspan='6' style='padding: 20px; text-align: center; border-bottom: 1px solid #ddd;'>ℹ️ Belum ada data pengembalian.</td></tr>";
}

echo '</table>';
?>
