<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/fields.php';
require_once __DIR__ . '/includes/fpdf.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM submissions WHERE id = ?');
$stmt->execute([$id]);
$row = $stmt->fetch();
if (!$row) {
    header('Location: dashboard.php');
    exit;
}
$payload = json_decode($row['payload'], true) ?: [];

class CrfPdf extends FPDF
{
    public function Header()
    {
        $logo = __DIR__ . '/assets/img/dflogo-black.png';
        if (is_file($logo)) {
            $this->Image($logo, 12, 8, 42);
        }
        $this->SetFont('Helvetica', 'B', 12);
        $this->SetTextColor(13, 43, 184);
        $this->Cell(0, 6, 'VG Digital Falcon', 0, 1, 'R');
        $this->SetFont('Helvetica', '', 9);
        $this->SetTextColor(90, 90, 90);
        $this->Cell(0, 5, 'Client Requirement Form  |  FR Holding Inc.', 0, 1, 'R');
        $this->Ln(8);
        $this->SetDrawColor(255, 154, 26);
        $this->SetLineWidth(0.6);
        $this->Line(12, 24, 198, 24);
    }

    public function Footer()
    {
        $this->SetY(-12);
        $this->SetFont('Helvetica', '', 8);
        $this->SetTextColor(120, 120, 120);
        $this->Cell(0, 8, 'Page ' . $this->PageNo() . '  |  contact@vgdigitalfalcon.com  |  +91 86957 76922', 0, 0, 'C');
    }
}

function pdf_text($s): string
{
    $s = html_entity_decode((string)$s, ENT_QUOTES, 'UTF-8');
    if (function_exists('iconv')) {
        $converted = @iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $s);
        if ($converted !== false) {
            return $converted;
        }
    }
    return utf8_decode($s);
}

$pdf = new CrfPdf('P', 'mm', 'A4');
$pdf->SetTitle('FR Holding Requirement Form #' . $id);
$pdf->SetMargins(12, 28, 12);
$pdf->AddPage();
$pdf->SetFont('Helvetica', 'B', 14);
$pdf->SetTextColor(7, 20, 61);
$pdf->Cell(0, 8, pdf_text('Submission #' . $id), 0, 1);
$pdf->SetFont('Helvetica', '', 10);
$pdf->SetTextColor(80, 80, 80);
$pdf->MultiCell(0, 5, pdf_text($row['created_at'] . '  |  ' . $row['contact_name'] . '  |  ' . $row['contact_email']));
$pdf->Ln(3);

foreach (crf_labels() as $key => $label) {
    $val = field($payload, $key);
    $pdf->SetFillColor(244, 246, 255);
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetTextColor(91, 107, 147);
    $pdf->MultiCell(0, 5, pdf_text($label), 0, 'L', true);
    $pdf->SetFont('Helvetica', '', 9);
    $pdf->SetTextColor(18, 32, 74);
    $pdf->MultiCell(0, 5, pdf_text($val));
    $pdf->Ln(1);
}

$pdf->Output('D', 'VGDF-FR-Holding-Requirement-' . $id . '.pdf');
exit;
