<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if ($_POST) {
    include_once("../inc/php/cbpost.php");
}

?>

<link rel="stylesheet" href="/form/css/gerenciamentocamerafria_css.css?version=1.0" />


<div class="row">
    <!-- Div que faz mudanças entre as telas pelo id  ? -->
    <div id="corpo" class="col-md-12">
    </div>
</div>
<script src="./inc/js/qr-scanner/qr-scanner.legacy.min.js" type="text/javascript"></script>

<?
include(__DIR__ . "/js/gerenciamentocamerafria_js.php");
?>