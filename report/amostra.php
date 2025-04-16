<?
require_once("../inc/php/validaacesso.php");
require_once("../form/controllers/amostra_controller.php");


if (empty($_GET['idamostra'])) {
	die("IdAmostra não enviado");
}

$idamostra = $_GET['idamostra'];
$dadosAmostra = AmostraController::buscarDadosCabecalhoReportAmostra($idamostra);
$dadosAmostra === false ? die('Falha na Consulta da Function buscarDadosCabecalhoReportAmostra()') : '';
$sqlConsultaDadosAmostra = $dadosAmostra->sql();
$numRowsdadosAmostra = $dadosAmostra->numRows();
$dadosCabecalho = $dadosAmostra->data;
$_timbradocabecalho = AmostraController::buscarCaminhoImagemTipoHeaderProduto($idamostra);

echo "<!-- $sqlConsultaDadosAmostra -->";

?>

<html>

<head>
	<title>Resultados</title>
	<link href="../form/css/amostra_css.css" rel="stylesheet">
	<script src="../inc/js/jquery/jquery-1.11.2.min.js"></script>
</head>

<body>
	<?
	if ($numRowsdadosAmostra > 0) {
		$i = 0;

		foreach ($dadosCabecalho as $key => $dadoAmostra) {

			if (empty($dadoAmostra['spartida'])) {
				$lote = $dadoAmostra['lote'];
			} else {
				$piloto = $dadoAmostra['piloto'] == 'Y' ? 'PP' : '';
				$lote =   $piloto." ".$dadoAmostra['spartida'];
			}


			if ($i == 1) { ?>
				<div class="quebrapagina"></div>
			<?
			}
			$i = 1;
			?>

			<pagina>
				<header class="row margem0.0">
					<div class="logosup col 5">
						<div id="_timbradocabecalho"><img src="<?= $_timbradocabecalho ?>" alt="_timbradocabecalho" height="80px" width="100%"></div>
					</div>

					<div class="titulodoc"><?= $dadoAmostra['descr'] ?></div>
				</header>
				<hr>

				<div class="row">
					<div class="col 10 rot">Produto:</div>
					<div class=" val colw700"><?= $dadoAmostra["descricao"] == "" ? $dadoAmostra["descr"] : $dadoAmostra["descricao"] ?></div>
					<div class="col 10 rot"></div>
					<div class="col 10 val"></div>
					<div class="col 10 rot"></div>
					<div class="col 20 val"></div>
				</div>

				<div class="row">
					<div class="col 10 rot">Amostra:</div>
					<div class="col 20 val"><?= $dadoAmostra["subtipoamostra"] ?></div>
					<div class="col 10 rot"></div>
					<div class="col 10 val"></div>
					<div class="col 10 rot"></div>
					<div class="col 20 val"></div>
				</div>

				<?if(!empty($lote)){?>
					<div class="row">
						<div class="col 10 rot">Cód:</div>
						<div class="col 20 val"><?= $lote ?></div>
						<div class="col 10 rot"></div>
						<div class="col 10 val"></div>

						<? if (!empty($dadoAmostra['partidaext'])) { ?>
							<div class="col 10 rot">Partida:</div>
							<div class="col 20 val"> <?= $dadoAmostra['piloto'] == 'Y' ? 'PP' : '' ?> <?= $dadoAmostra['partidaext'] ?> </div>
						<? } else { ?>
							<div class="col 10 rot">Partida:</div>
							<div class="col 20 val"> <?= $dadoAmostra['partidaamostra']?> </div>
						<? } ?>
					</div>
				<?}?>

				<div class="row">
					<div class="col 10 rot">Empresa:</div>
					<div class="col 20 val"><?= $dadoAmostra["nome"] ?></div>
					<div class="col 10 rot"></div>
					<div class="col 10 val"></div>
					<div class="col 10 rot">Reg.:</div>
					<div class="col 20 val"><?=(($dadoAmostra["idunidade"] == 1298) ? $dadoAmostra["idregistro"].'PET' : $dadoAmostra["idregistro"]) ?>/<?= $dadoAmostra['exercicio'] ?></div>
				</div>

				<?if (!empty($dadoAmostra["observacao"])) {?>
					<div class="row">
						<div class="col 10 rot">Obervação:</div>
						<div class="col 80 quebralinha val"><?= nl2br($dadoAmostra["observacao"]) ?></div>
					</div>
				<?}?>

				<hr>
				<br>
				<div class="row">
					<table style="width:100% ;">
						<tr>
							<td class="grval"><?= $dadoAmostra['textoinclusaores'] ?></td>
						</tr>
						<tr>
							<td class="grval"><?= $dadoAmostra['textopadrao'] ?></td>
						</tr>
					</table>
				</div>
			</pagina>
	<?
		}
	}
	?>

</body>

</html>
<?require_once("../form/js/amostrarep_js.php");?>
