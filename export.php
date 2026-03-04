<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/vendor/FPDF/fpdf.php';

class ReportExporter {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function exportPeminjaman($format = 'csv', $from = null, $to = null, $search = null) {
        // Build query dengan parameter yang aman
        $sql = "SELECT p.id_pinjam, p.jumlah, p.tanggal_pinjam, p.tanggal_kembali, p.status,
               k.tanggal_dikembalikan, k.total_denda, u.nama AS nama_peminjam, a.nama_alat
        FROM peminjaman p
        JOIN `user` u ON p.id_user = u.id_user
        JOIN alat a ON p.id_alat = a.id_alat
        LEFT JOIN pengembalian k ON p.id_pinjam = k.id_pinjam";
        
        $clauses = [];
        $params = [];
        
        // Tambahkan klausa WHERE jika ada
        if (!empty($search)) {
            $clauses[] = "(u.nama LIKE CONCAT('%', ?, '%') OR a.nama_alat LIKE CONCAT('%', ?, '%'))";
            $params[] = $search;
            $params[] = $search;
        }
        
        if (!empty($from)) {
            $clauses[] = "p.tanggal_pinjam >= ?";
            $params[] = $from;
        }
        
        if (!empty($to)) {
            $clauses[] = "p.tanggal_pinjam <= ?";
            $params[] = $to;
        }
        
        if (count($clauses) > 0) {
            $sql .= ' WHERE ' . implode(' AND ', $clauses);
        }
        
        $sql .= ' ORDER BY p.tanggal_pinjam DESC';
        
        // Execute query dengan prepared statement
        $stmt = mysqli_prepare($this->conn, $sql);
        if ($stmt && count($params) > 0) {
            $types = str_repeat('s', count($params));
            mysqli_stmt_bind_param($stmt, $types, ...$params);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
        } else {
            $result = mysqli_query($this->conn, $sql);
        }
        
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        
        return $this->formatOutput($rows, $format, 'peminjaman');
    }
    
    public function exportPengembalian($format = 'csv', $from = null, $to = null, $search = null) {
        // Build query untuk pengembalian
        $sql = "SELECT pk.id_kembali, pk.tanggal_dikembalikan, pk.terlambat, pk.denda, 
               p.jumlah, u.nama AS nama_peminjam, a.nama_alat
        FROM pengembalian pk
        JOIN peminjaman p ON pk.id_pinjam = p.id_pinjam
        JOIN `user` u ON p.id_user = u.id_user
        JOIN alat a ON p.id_alat = a.id_alat";
        
        $clauses = [];
        $params = [];
        
        if (!empty($search)) {
            $clauses[] = "(u.nama LIKE CONCAT('%', ?, '%') OR a.nama_alat LIKE CONCAT('%', ?, '%'))";
            $params[] = $search;
            $params[] = $search;
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
        
        // Execute query
        $stmt = mysqli_prepare($this->conn, $sql);
        if ($stmt && count($params) > 0) {
            $types = str_repeat('s', count($params));
            mysqli_stmt_bind_param($stmt, $types, ...$params);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
        } else {
            $result = mysqli_query($this->conn, $sql);
        }
        
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        
        return $this->formatOutput($rows, $format, 'pengembalian');
    }
    
    private function formatOutput($rows, $format, $entity) {
        if ($format === 'csv') {
            $this->exportCSV($rows, $entity);
        } elseif ($format === 'pdf') {
            $this->exportPDF($rows, $entity);
        } else {
            return $rows;
        }
    }
    
    private function exportCSV($rows, $entity) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="'.$entity.'-export-'.date('Ymd').'.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Header CSV yang dinamis
        if ($entity === 'peminjaman') {
            fputcsv($output, ['ID Peminjaman', 'Peminjam', 'Alat', 'Jumlah', 'Tgl Pinjam', 'Tgl Kembali', 'Status']);
        } elseif ($entity === 'pengembalian') {
            fputcsv($output, ['ID Kembali', 'Peminjam', 'Alat', 'Jumlah', 'Tanggal Dikembalikan', 'Terlambat', 'Denda']);
        }
        
        // Output data
        foreach ($rows as $row) {
            fputcsv($output, array_values($row));
        }
        
        fclose($output);
        exit;
    }
    
    private function exportPDF($rows, $entity) {
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',12);
        
        // Header
        $pdf->Cell(0,10, 'LAPORAN ' . strtoupper($entity), 0,1,'C');
        $pdf->Ln(4);
        
        // Headers
        if ($entity === 'peminjaman') {
            $headers = ['ID', 'Peminjam', 'Alat', 'Jumlah', 'Tgl Pinjam', 'Tgl Kembali', 'Status'];
        } elseif ($entity === 'pengembalian') {
            $headers = ['ID', 'Peminjam', 'Alat', 'Jumlah', 'Tanggal', 'Terlambat', 'Denda'];
        }
        
        $w = [15,45,50,15,35,15,20];
        foreach ($headers as $i => $h) {
            $pdf->Cell($w[$i],7,$h,1);
        }
        $pdf->Ln();
        
        // Data
        $pdf->SetFont('Arial','',10);
        foreach ($rows as $row) {
            $pdf->Cell($w[0],6,$row['id_pinjam'],1);
            $pdf->Cell($w[1],6,substr($row['nama_peminjam'],0,30),1);
            $pdf->Cell($w[2],6,substr($row['nama_alat'],0,30),1);
            $pdf->Cell($w[3],6,$row['jumlah'],1,0,'C');
            $pdf->Cell($w[4],6,$row['tanggal_pinjam'],1);
            $pdf->Cell($w[5],6,$row['tanggal_kembali'],1);
            $pdf->Cell($w[6],6,ucfirst($row['status']),1);
            $pdf->Ln();
        }
        
        // Footer
        $pdf->Ln(4);
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(0,8,'Dicetak oleh: ' . $_SESSION['nama'], 0);
        $pdf->Cell(0,8,'Tanggal: ' . date('d F Y H:i'), 0);
        
        // Output PDF
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="'.$entity.'-export-'.date('Ymd').'.pdf"');
        $pdf->Output();
    }
}

// Handler untuk export
 $entity = $_GET['entity'] ?? '';
 $format = $_GET['format'] ?? 'csv';
 $from = $_GET['from'] ?? '';
 $to = $_GET['to'] ?? '';
 $search = $_GET['search'] ?? '';

// Validasi entity
 $allowed_entities = ['peminjaman', 'pengembalian'];
if (!in_array($entity, $allowed_entities)) {
    http_response_code(400);
    echo 'Invalid entity';
    exit;
}

// Validasi format
 $allowed_formats = ['csv', 'pdf'];
if (!in_array($format, $allowed_formats)) {
    http_response_code(400);
    echo 'Invalid format';
    exit;
}

// Buat instance exporter
 $exporter = new ReportExporter($conn);

// Export berdasarkan entity
switch ($entity) {
    case 'peminjaman':
        $result = $exporter->exportPeminjaman($format, $from, $to, $search);
        break;
    case 'pengembalian':
        $result = $exporter->exportPengembalian($format, $from, $to, $search);
        break;
    default:
        http_response_code(400);
        echo 'Invalid entity';
        exit;
}
?>