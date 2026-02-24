<?php
/**
 * download_results_pdf.php
 * Colorful, well-aligned student results PDF.
 * Schema: atmpt_list + student + exm_list
 */

session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login_teacher.php");
    exit;
}
include '../config.php';
error_reporting(0);

if (!isset($_POST['exid'])) die("No exam selected.");

$exid   = intval($_POST['exid']);
$exname = isset($_POST['exname']) ? $_POST['exname'] : 'Exam';

// ── 1. Exam info ──────────────────────────────────────────────────────────────
$exam_res  = mysqli_query($conn, "SELECT * FROM exm_list WHERE exid=$exid LIMIT 1");
$exam_info = mysqli_fetch_assoc($exam_res) ?? [];

// ── 2. Student results ────────────────────────────────────────────────────────
$res_sql = "
    SELECT
        s.fname                    AS student_name,
        a.uname                    AS username,
        a.cnq                      AS score,
        a.nq                       AS total,
        a.ptg                      AS percentage,
        a.subtime                  AS submitted_at,
        CASE
            WHEN a.ptg >= 80 THEN 'A'
            WHEN a.ptg >= 65 THEN 'B'
            WHEN a.ptg >= 50 THEN 'C'
            WHEN a.ptg >= 35 THEN 'D'
            ELSE 'F'
        END AS grade
    FROM atmpt_list a
    LEFT JOIN student s ON s.uname = a.uname
    WHERE a.exid = $exid AND a.status = 1
    ORDER BY a.ptg DESC
";
$res_result = mysqli_query($conn, $res_sql);

// ── 3. Load FPDF ──────────────────────────────────────────────────────────────
require(__DIR__ . '/fpdf/fpdf.php');

// ── 4. PDF Class ──────────────────────────────────────────────────────────────
class ResultsPDF extends FPDF {
    public $examName='', $examDesc='', $examDate='';
    public $examSubject='', $teacherName='', $totalQ=0;

    // ── Helper: filled rounded rectangle (simulated with rect) ────────────────
    function colorRect($x,$y,$w,$h,$r,$g,$b) {
        $this->SetFillColor($r,$g,$b);
        $this->Rect($x,$y,$w,$h,'F');
    }

    function Header() {
        // ── Deep blue banner ──────────────────────────────────────────────────
        $this->colorRect(0, 0, 210, 40, 30, 40, 180);

        // ── Accent bar ────────────────────────────────────────────────────────
        $this->colorRect(0, 40, 210, 4, 65, 192, 253);

        // ── Logo circle placeholder ───────────────────────────────────────────
        $this->SetFillColor(255,255,255);
        $this->Rect(8, 8, 24, 24, 'F');
        $this->SetFont('Arial','B',7);
        $this->SetTextColor(30,40,180);
        $this->SetXY(8,16);
        $this->Cell(24,6,'SCALE',0,1,'C');
        $this->SetXY(8,22);
        $this->Cell(24,6,'GRAD',0,0,'C');

        // ── Title ─────────────────────────────────────────────────────────────
        $this->SetFont('Arial','B',22);
        $this->SetTextColor(255,255,255);
        $this->SetXY(35, 7);
        $this->Cell(130,12,'SCALEGRAD',0,1,'L');

        $this->SetFont('Arial','',10);
        $this->SetTextColor(190,210,255);
        $this->SetXY(35,19);
        $this->Cell(130,7,'Student Results Report',0,1,'L');

        // ── Right: generated date ─────────────────────────────────────────────
        $this->SetFont('Arial','',8);
        $this->SetTextColor(190,210,255);
        $this->SetXY(0,29);
        $this->Cell(202,7,'Generated: '.date('d M Y, h:i A'),0,1,'R');

        // ── Info row (light blue bg) ──────────────────────────────────────────
        $this->colorRect(0, 44, 210, 20, 235, 240, 255);
        $this->SetDrawColor(41,51,204);
        $this->SetLineWidth(0.1);
        $this->Line(0,44,210,44);
        $this->Line(0,64,210,64);

        $this->SetTextColor(41,51,204);
        $this->SetFont('Arial','B',9);
        $this->SetXY(8,47);
        $this->Cell(16,5,'Exam:',0,0,'L');
        $this->SetFont('Arial','',9);
        $this->SetTextColor(30,30,30);
        $this->Cell(60,5,$this->examName,0,0,'L');

        $this->SetTextColor(41,51,204);
        $this->SetFont('Arial','B',9);
        $this->Cell(18,5,'Subject:',0,0,'L');
        $this->SetFont('Arial','',9);
        $this->SetTextColor(30,30,30);
        $this->Cell(0,5,$this->examSubject,0,1,'L');

        $this->SetXY(8,54);
        $this->SetTextColor(41,51,204);
        $this->SetFont('Arial','B',9);
        $this->Cell(16,5,'Date:',0,0,'L');
        $this->SetFont('Arial','',9);
        $this->SetTextColor(30,30,30);
        $this->Cell(60,5,$this->examDate,0,0,'L');

        $this->SetTextColor(41,51,204);
        $this->SetFont('Arial','B',9);
        $this->Cell(18,5,'Teacher:',0,0,'L');
        $this->SetFont('Arial','',9);
        $this->SetTextColor(30,30,30);
        $this->Cell(0,5,$this->teacherName,0,1,'L');

        $this->Ln(4);
    }

    function Footer() {
        $this->SetY(-13);
        $this->colorRect(0,$this->GetY()-1,210,18,30,40,180);
        $this->SetFont('Arial','I',8);
        $this->SetTextColor(190,210,255);
        $this->SetXY(8,$this->GetY());
        $this->Cell(100,10,chr(169).' 2025 FinVedic. All rights reserved.',0,0,'L');
        $this->Cell(94,10,'Page '.$this->PageNo().' of {nb}',0,0,'R');
    }

    function gradeColor($g) {
        switch(strtoupper(trim($g))) {
            case 'A': return [39,174,96];
            case 'B': return [41,128,185];
            case 'C': return [243,156,18];
            case 'D': return [211,84,0];
            default:  return [192,57,43];
        }
    }

    // Draw a smooth percentage bar
    function percentBar($x,$y,$w,$h,$pct) {
        // Track (background)
        $this->SetFillColor(220,224,240);
        $this->Rect($x,$y,$w,$h,'F');

        // Fill
        $fill = max(0, min($w, ($w * floatval($pct)) / 100));
        if ($pct>=65)      $this->SetFillColor(39,174,96);
        elseif ($pct>=50)  $this->SetFillColor(90,190,100);
        elseif ($pct>=35)  $this->SetFillColor(243,156,18);
        else               $this->SetFillColor(192,57,43);
        if ($fill > 0) $this->Rect($x,$y,$fill,$h,'F');

        // Border
        $this->SetDrawColor(180,185,210);
        $this->SetLineWidth(0.2);
        $this->Rect($x,$y,$w,$h,'D');
    }

    // Stat box
    function statBox($x,$y,$w,$h,$label,$value,$r,$g,$b) {
        // Shadow
        $this->SetFillColor(180,185,210);
        $this->Rect($x+1.5,$y+1.5,$w,$h,'F');
        // Box
        $this->colorRect($x,$y,$w,$h,$r,$g,$b);
        // Top highlight strip
        $this->SetFillColor(255,255,255);
        $this->SetAlpha = 0.15; // not supported in FPDF base, skip
        // Label
        $this->SetFont('Arial','B',7);
        $this->SetTextColor(220,230,255);
        $this->SetXY($x,$y+3);
        $this->Cell($w,5,strtoupper($label),0,1,'C');
        // Value
        $this->SetFont('Arial','B',17);
        $this->SetTextColor(255,255,255);
        $this->SetXY($x,$y+9);
        $this->Cell($w,9,$value,0,1,'C');
    }
}

// ── 5. Collect rows & stats ───────────────────────────────────────────────────
$rows=[]; $total_students=0; $sum_pct=0;
$highest=-1; $lowest=101; $pass_count=0;

if ($res_result && mysqli_num_rows($res_result)>0) {
    while($row=mysqli_fetch_assoc($res_result)) {
        $rows[]=$row;
        $pct=floatval($row['percentage']);
        $sum_pct+=$pct;
        if($pct>$highest) $highest=$pct;
        if($pct<$lowest)  $lowest=$pct;
        if($pct>=50) $pass_count++;
        $total_students++;
    }
}
$avg_pct  = $total_students>0 ? round($sum_pct/$total_students,1) : 0;
$pass_pct = $total_students>0 ? round(($pass_count/$total_students)*100,1) : 0;
if($highest<0) $highest=0;
if($lowest>100) $lowest=0;

// ── 6. Init PDF ───────────────────────────────────────────────────────────────
$pdf = new ResultsPDF('P','mm','A4');
$pdf->AliasNbPages();
$pdf->examName    = $exam_info['exname']  ?? $exname;
$pdf->examDesc    = $exam_info['desp']    ?? '';
$pdf->examSubject = $exam_info['subject'] ?? '';
$pdf->examDate    = isset($exam_info['datetime'])
                    ? date('d M Y, h:i A', strtotime($exam_info['datetime']))
                    : date('d M Y');
$pdf->teacherName = $_SESSION['fname']    ?? 'Teacher';
$pdf->totalQ      = $exam_info['nq']      ?? 0;
$pdf->SetAuthor('ScaleGrad');
$pdf->SetTitle('Results - '.$pdf->examName);
$pdf->AddPage();
$pdf->SetAutoPageBreak(true,18);

// ── 7. Stat boxes (4 across) ──────────────────────────────────────────────────
$by = $pdf->GetY() + 2;
$boxes = [
    ['Total Students', $total_students,               [41,  51,204]],
    ['Average Score',  $avg_pct.'%',                  [20, 160,200]],
    ['Highest Score',  $highest.'%',                  [39, 174, 96]],
    ['Pass Rate',      $pass_pct.'%',                 [155, 89,182]],
];
$bx = 8;
foreach($boxes as $b) {
    $pdf->statBox($bx,$by,46,24,$b[0],$b[1],$b[2][0],$b[2][1],$b[2][2]);
    $bx += 50;
}
$pdf->SetY($by + 30);

// ── 8. Description strip ──────────────────────────────────────────────────────
if (!empty($pdf->examDesc)) {
    $pdf->SetFillColor(248,248,255);
    $pdf->SetDrawColor(200,205,240);
    $pdf->SetLineWidth(0.2);
    $pdf->SetX(8);
    $pdf->SetFont('Arial','',9);
    $pdf->SetTextColor(80,80,120);
    $pdf->Cell(194,7,'  Description: '.$pdf->examDesc,1,1,'L',true);
    $pdf->Ln(3);
}

// ── 9. Column definitions (total usable width = 194mm) ───────────────────────
// Cols: #, Name, Username, Score, Total, %, [Progress bar], Grade, Submitted
$cols = [
    ['#',            7, 'C'],
    ['Student Name', 58,'L'],
    ['Score',        16,'C'],
    ['Total Qs',     16,'C'],
    ['%',            14,'C'],
    ['Progress',     36,'C'],
    ['Grade',        13,'C'],
    ['Submitted On', 34,'C'],
];
// verify widths sum
$total_w = 0; foreach($cols as $c) $total_w += $c[1]; // = 194 ✓

// ── Table header ──────────────────────────────────────────────────────────────
$pdf->SetX(8);
$pdf->SetFont('Arial','B',8);
$pdf->SetFillColor(30,40,180);
$pdf->SetTextColor(255,255,255);
$pdf->SetDrawColor(100,110,200);
$pdf->SetLineWidth(0.2);
foreach($cols as $col) {
    $pdf->Cell($col[1],9,$col[0],1,0,$col[2],true);
}
$pdf->Ln();

// ── 10. Rows ──────────────────────────────────────────────────────────────────
if(empty($rows)) {
    $pdf->SetX(8);
    $pdf->SetFillColor(255,249,230);
    $pdf->SetTextColor(180,100,0);
    $pdf->SetFont('Arial','I',9);
    $pdf->Cell($total_w,11,'  No completed attempts found for this exam.',1,1,'L',true);
} else {
    $odd = true;
    foreach($rows as $i=>$row) {
        $pct   = floatval($row['percentage']);
        $grade = $row['grade'] ?? 'F';
        $gc    = $pdf->gradeColor($grade);

        // Alternate row color
        $bg = $odd ? [248,249,255] : [255,255,255];
        $odd = !$odd;

        $pdf->SetFillColor($bg[0],$bg[1],$bg[2]);
        $pdf->SetTextColor(40,40,40);
        $pdf->SetFont('Arial','',8);
        $pdf->SetDrawColor(200,205,230);
        $pdf->SetLineWidth(0.1);
        $pdf->SetX(8);

        $row_y = $pdf->GetY();
        $row_h = 9;

        $name = !empty(trim($row['student_name'])) ? $row['student_name'] : $row['username'];

        // ── Draw all cells except progress & grade (need X position) ─────────
        $pdf->Cell($cols[0][1],$row_h,$i+1,           1,0,'C',true);
        $pdf->Cell($cols[1][1],$row_h,' '.$name,      1,0,'L',true);
        $pdf->Cell($cols[2][1],$row_h,$row['score'],   1,0,'C',true);
        $pdf->Cell($cols[3][1],$row_h,$row['total'],   1,0,'C',true);
        $pdf->Cell($cols[4][1],$row_h,$pct.'%',        1,0,'C',true);

        // ── Progress bar cell ─────────────────────────────────────────────────
        $bar_cell_x = $pdf->GetX();
        $pdf->Cell($cols[5][1],$row_h,'',1,0,'C',true);
        $bar_pad_x = 2; $bar_pad_y = 2.5;
        $bar_w = $cols[5][1] - ($bar_pad_x*2);
        $bar_h = $row_h - ($bar_pad_y*2);
        $pdf->percentBar($bar_cell_x+$bar_pad_x,$row_y+$bar_pad_y,$bar_w,$bar_h,$pct);
        $pdf->SetFont('Arial','B',6);
        $pdf->SetTextColor(255,255,255);
        $pdf->SetXY($bar_cell_x,$row_y+1);
        $pdf->Cell($cols[5][1],$row_h-2,$pct.'%',0,0,'C');

        // ── Grade badge ───────────────────────────────────────────────────────
        $pdf->SetFillColor($gc[0],$gc[1],$gc[2]);
        $pdf->SetTextColor(255,255,255);
        $pdf->SetFont('Arial','B',9);
        $pdf->Cell($cols[6][1],$row_h,strtoupper($grade),1,0,'C',true);

        // ── Submitted date ────────────────────────────────────────────────────
        $pdf->SetFillColor($bg[0],$bg[1],$bg[2]);
        $pdf->SetTextColor(80,80,100);
        $pdf->SetFont('Arial','',7);
        $sub = !empty($row['submitted_at'])
               ? date('d/m/Y H:i', strtotime($row['submitted_at']))
               : '-';
        $pdf->Cell($cols[7][1],$row_h,$sub,1,1,'C',true);
    }
}

// ── Bottom border line ────────────────────────────────────────────────────────
$pdf->SetDrawColor(30,40,180);
$pdf->SetLineWidth(0.4);
$pdf->SetX(8);
$pdf->Line(8,$pdf->GetY(),202,$pdf->GetY());
$pdf->Ln(6);

// ── 11. Grade legend ──────────────────────────────────────────────────────────
$pdf->SetX(8);
$pdf->SetFont('Arial','B',9);
$pdf->SetTextColor(30,40,120);
$pdf->Cell(0,6,'Grade Scale:',0,1,'L');
$pdf->Ln(2);

$legend = [
    ['A', [39,174,96],   '>= 80%',   'Excellent'],
    ['B', [41,128,185],  '65 - 79%', 'Good'],
    ['C', [243,156,18],  '50 - 64%', 'Average'],
    ['D', [211,84,0],    '35 - 49%', 'Below Avg'],
    ['F', [192,57,43],   '< 35%',    'Fail'],
];

// Usable width = 194mm (page 210 - 8 margin left - 8 margin right)
// Each block = badge(10) + range(18) + label(10) + gap(0) = 38mm per item
// 5 items x 38 = 190mm — fits perfectly with small gaps
// 5 blocks must fit in 194mm usable width
// badge=8, range=14, label=16 => block=38mm, 5*38=190mm, gap=(194-190)/4=1mm
$badge_w = 8;
$range_w = 14;
$label_w = 16;
$block_w = $badge_w + $range_w + $label_w;  // 38mm
$gap     = (194 - ($block_w * 5)) / 4;      // 1mm between blocks

$lx = 8;
$ly = $pdf->GetY();
$row_h = 8;

foreach($legend as $l) {
    // ── Colored grade badge ───────────────────────────────────────────────
    $pdf->SetFillColor($l[1][0],$l[1][1],$l[1][2]);
    $pdf->SetTextColor(255,255,255);
    $pdf->SetFont('Arial','B',10);
    $pdf->SetXY($lx, $ly);
    $pdf->Cell($badge_w, $row_h, $l[0], 0, 0, 'C', true);

    // ── Range text ────────────────────────────────────────────────────────
    $pdf->SetFillColor(245,246,255);
    $pdf->SetTextColor(50,50,50);
    $pdf->SetFont('Arial','B',7);
    $pdf->Cell($range_w, $row_h, $l[2], 0, 0, 'C', true);

    // ── Label text ────────────────────────────────────────────────────────
    $pdf->SetFillColor(255,255,255);
    $pdf->SetTextColor(100,100,120);
    $pdf->SetFont('Arial','',7);
    $pdf->Cell($label_w, $row_h, $l[3], 0, 0, 'L', true);

    $lx += $block_w + $gap;
}

// ── 12. Footer note ───────────────────────────────────────────────────────────
$pdf->Ln(14);
$pdf->SetX(8);
$pdf->SetFont('Arial','I',8);
$pdf->SetTextColor(150,150,170);
$pdf->Cell(0,5,'Total Questions in Exam: '.$pdf->totalQ.'   |   Pass mark: 50%   |   Results sorted by score (highest first)',0,1,'L');

// ── 13. Output ────────────────────────────────────────────────────────────────
$filename = 'Results_'.preg_replace('/[^A-Za-z0-9_-]/','_',$pdf->examName).'_'.date('Ymd_His').'.pdf';
$pdf->Output('D',$filename);
exit;
?>