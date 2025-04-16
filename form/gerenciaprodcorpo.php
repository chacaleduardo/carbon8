<?
require_once("../inc/php/validaacesso.php");

if ($_POST) {
	include_once("../inc/php/cbpost.php");
}

$urini = $_SERVER['QUERY_STRING'];
//UNIQUE_ID para controlar o select na pagina carrimbocorpo.php
$uniqueid = $_SERVER['UNIQUE_ID'];
?>
<link href="../form/css/gerenciaprod_css.css?_<?= date("dmYhms") ?>" rel="stylesheet">

<body onload="listaresultado('ini')">
	<div id="gol" class="gol"><img src="../inc/img/gol.png" class="imggo" id="imggol" onclick="listaresultado('ant')"></div>
	<div id="gor" class="gor"><img src="../inc/img/gor.png" class="imggo" id="imggor" onclick="listaresultado('prox')"></div>
	<div class="divsup">
		<div id="conteudo" style="display: table-cell; height: 100%; width: 100%; padding-left: 30px;">
			<!-- conteudo aqui aparece a assinaresultado corpo -->
		</div>
	</div>
</body>

</html>
<?
$retdel = delgraf();
require_once('../form/js/gerenciaprodcorpo_js.php');
?>