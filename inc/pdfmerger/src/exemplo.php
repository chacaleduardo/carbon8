<?
require('Pdf.php');
//require('../setasign/fpdi/src/Fpdi.php');
//require('../setasign/fpdi/src/FpdfTpl.php');

require_once('../setasign/fpdf/fpdf.php');
require_once('../setasign/fpdi/src/autoload.php');

use PDFMerger\Pdf;
$pdf = new Pdf();

$pdf->add('../files/SF.pdf');             // -- merge all pages
$pdf->add('../files/TRA.pdf');       

/*
$pdf->add('files/1.pdf');             // -- merge all pages
$pdf->add('files/2.pdf', [2]);        // -- merge only page 2
$pdf->add('files/3.pdf', [2-5]);      // -- merge page 2 to 5
$pdf->add('files/4.pdf', [1, 3-5]);   // -- merge page 1 and 3 to 5
 */

$pdf->output('merged.pdf');         // -- send pdf to inline browser
$pdf->download('merged.pdf');       // -- force download
$pdf->save('merged.pdf');           // -- save merged pdf to new file
?>