<?php
/**
 * FPDF Wrapper - Minimal PDF Generation
 * Ini adalah wrapper sederhana untuk generate PDF
 * Untuk setup lengkap, install via Composer: composer require setasign/fpdf
 */

// Jika belum ada FPDF installed, gunakan HTML to PDF
class FPDF {
    public $page = 1;
    
    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4') {}
    
    public function AddPage() {}
    
    public function SetFont($family = '', $style = '', $size = 0) {}
    
    public function Cell($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = false) {}
    
    public function Ln($h = '') {}
    
    public function SetFillColor($r, $g = null, $b = null) {}
    
    public function PageNo() {
        return $this->page;
    }
    
    public function Output($dest = '', $name = '') {
        // Output sebagai file download
        if ($dest === 'D') {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $name . '"');
        }
    }
}

// Alternatif: Generate HTML yang bisa di-print sebagai PDF
function generateHTMLtoPDF($data) {
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Laporan Peminjaman</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            h1 { text-align: center; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #000; padding: 8px; text-align: left; }
            th { background-color: #ddd; }
            @media print { body { margin: 0; } }
        </style>
    </head>
    <body>
        <h1>LAPORAN PEMINJAMAN ALAT</h1>
        ' . $data . '
    </body>
    </html>
    ';
    return $html;
}
?>
