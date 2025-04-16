<?
require_once("../inc/php/validaacesso.php");
?>
<link rel="stylesheet" href="./inc/js/qr-scanner/qr-scanner.css" />

<div class="row" id="main-content">
    <div class="col-sm-12 col-md-5">
        <div id="scanner"></div>
    </div>
    <div class="col-sm-12 col-md-7" id="volumes"></div>
</div>

<script src="./inc/js/qr-scanner/qr-scanner.legacy.min.js" type="text/javascript"></script>
<script src="./inc/js/qr-scanner/qr-scanner-controller.js" type="text/javascript"></script>
<?
require_once('./js/nfvolume_js.php');
?>