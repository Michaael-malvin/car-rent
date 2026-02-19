<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/vendor/FPDF/fpdf.php';

$entity = $_GET['entity'] ?? '';
$format = $_GET['format'] ?? 'csv';
$q = $_GET['q'] ?? '';
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';

if ($entity !== 'pengembalian' && $entity !== 'peminjaman') {
    http_response_code(400);
    echo 'Invalid entity';
    exit;
}

// build query - for pengembalian we join peminjaman/user/alat
$sql = "SELECT pk.id_kembali, pk.tanggal_dikembalikan, pk.terlambat, pk.denda, p.jumlah, u.nama AS nama_peminjam, a.nama_alat
FROM pengembalian pk
JOIN peminjaman p ON pk.id_pinjam = p.id_pinjam
JOIN `user` u ON p.id_user = u.id_user
JOIN alat a ON p.id_alat = a.id_alat
";

$clauses = [];
$params = [];
if (!empty($q)) {
    $clauses[] = "(u.nama LIKE CONCAT('%', ?, '%') OR a.nama_alat LIKE CONCAT('%', ?, '%'))";
    $params[] = $q;
    $params[] = $q;
}
if (!empty($from)) {
    $clauses[] = "pk.tanggal_dikembalikan >= ?";
    $params[] = $from;
}
if (!empty($to)) {
    $clauses[] = "pk.tanggal_dikembalikan <= ?";
    $params[] = $to;
}

if (count($clauses) > 0) {
    $sql .= ' WHERE ' . implode(' AND ', $clauses);
}
 $sql .= ' ORDER BY pk.tanggal_dikembalikan DESC';

$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
    if (count($params) > 0) {
        // build types
        $types = str_repeat('s', count($params));
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
} else {
    $res = mysqli_query($conn, $sql);
}

$rows = [];
while ($r = mysqli_fetch_assoc($res)) {
    $rows[] = $r;
}

if ($format === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename='.$entity.'-export-'.date('Ymd').'.csv');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID Kembali','Peminjam','Alat','Jumlah','Tanggal Dikembalikan','Terlambat','Denda']);
    foreach ($rows as $r) {
        fputcsv($out, [
            $r['id_kembali'],
            $r['nama_peminjam'],
            $r['nama_alat'],
            $r['jumlah'],
            $r['tanggal_dikembalikan'],
            ($r['terlambat'] ? 'Ya' : 'Tidak'),
            $r['denda']
        ]);
    }
    fclose($out);
    exit;
}

// PDF export using FPDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,10, strtoupper('Laporan '. $entity), 0,1,'C');
$pdf->Ln(4);
$pdf->SetFont('Arial','B',10);
$w = [15,45,50,15,35,15,20];
$headers = ['ID','Peminjam','Alat','Jumlah','Tanggal','Terlambat','Denda'];
foreach ($headers as $i => $h) { $pdf->Cell($w[$i],7,$h,1,0,'C'); }
$pdf->Ln();
$pdf->SetFont('Arial','',10);
foreach ($rows as $r) {
    $pdf->Cell($w[0],6,$r['id_kembali'],1);
    $pdf->Cell($w[1],6,substr($r['nama_peminjam'],0,30),1);
    $pdf->Cell($w[2],6,substr($r['nama_alat'],0,30),1);
    $pdf->Cell($w[3],6,$r['jumlah'],1,0,'C');
    $pdf->Cell($w[4],6,$r['tanggal_dikembalikan'],1);
    $pdf->Cell($w[5],6,($r['terlambat'] ? 'Ya' : 'Tidak'),1,0,'C');
    $pdf->Cell($w[6],6,$r['denda'],1,0,'R');
    $pdf->Ln();
}

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="'.$entity.'-export-'.date('Ymd').'.pdf"');
$pdf->Output();
exit;
