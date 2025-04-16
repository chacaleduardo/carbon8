<?
require_once("../inc/php/validaacesso.php");
require_once("../form/controllers/solfab_controller.php");

if (empty($_GET["idsolfab"])) {
	die("SF não enviada");
}

$row = SolfabController::buscarDadosSolfabRelatorio($_GET["idsolfab"]);
?>
<html>
	<head>
		<title>SF</title>
		<link href="../form/css/solfab_css.css?_<?=date("dmYhms")?>" rel="stylesheet">
	</head>

	<body>
		<header class="row margem0.0">
			<div class="logosup col 15"><img src="../inc/img/impcab.png"></div>
			<div class="titulodoc">SOLICITAÇÃO DE AUTORIZAÇÃO PARA FABRICAÇÃO DE VACINA AUTÓGENA</div>
			<div class="col 15"></div>
		</header>
		<div class="row">
			<div class="col 10 rot">ID:</div>
			<div class="col 15"><?=$row["idsolfab"] ?></div>
			<div class="col 15 rot">Partida:</div>
			<div class="col 20"><?=$row["partida"] ?>/<?=$row["exercicio"] ?></div>
			<div class="col 15 rot">Data:</div>
			<div class="col 20"><?= dma($row["data"]) ?></div>
		</div>
		<div class="row">
			<div class="col 100 quebralinha"><?= nl2br(espaco2nbsp($row["descr"])) ?></div>
		</div>
		<br>
		<div class="row">
			<div class="col grupo 100 quebralinha">
				<div class="titulogrupo">Dados do cliente</div>
			</div>
		</div>
		<div class="row">
			<div class="col 15 rot">Cliente:</div>
			<div class="col 85"><?=$row["razaosocial"] ?></div>
		</div>
		<div class="row">
			<div class="col 15 rot">Propriedade:</div>
			<div class="col 85"><?=$row["nome"] ?></div>
		</div>
		<div class="row">
			<div class="col 15 rot">Endereço:</div>
			<div class="col 85 quebralinha">
				<?
				if (empty($row["enderecosacado"])) {
				?>
					<div class="alert alert-warning">
						<span class="notProducao"><i class="fa fa-exclamation-triangle"></i>&nbsp;Favor preencher o endereço da propriedade no cadastro do cliente!</span>
					</div>
				<?
				} else {
					echo ($row["enderecosacado"]);
				}
				?>

			</div>
		</div>
		<div class="row linhainferior">
			<div class="col 15 rot">Cnpj:</div>
			<div class="col 35"><?= formatarCPF_CNPJ($row["cpfcnpj"]) ?></div>
			<div class="col 15 rot">Inscr. Estadual:</div>
			<div class="col 35"><?=$row["inscrest"] ?></div>
		</div>
		<br>
		<div class="row">
			<div class="col grupo 100 quebralinha">
				<div class="titulogrupo">Dados da Solicitação</div>
			</div>
		</div>
		<div class="row ">
			<div class="col 100 rot quebralinha">Espécie e nº de animais suscetíveis na propriedade:</div>
		</div>
		<div class="row ">
			<div class="col 100 quebralinha"><?= nl2br(espaco2nbsp($row["animsuscep"])) ?></div>
		</div>

		<div class="row ">
			<div class="col 100 rot quebralinha">Identificação e endereço das propriedades adjacentes:</div>
		</div>
		<div class="row ">
			<div class="col 100 quebralinha"><?= nl2br(espaco2nbsp($row["propad"])) ?></div>
		</div>
		<div class="row ">
			<div class="col 100 rot quebralinha">Espécie e nº de animais susceptíveis nas propriedades adjacentes:</div>
		</div>
		<div class="row ">
			<div class="col 100 quebralinha"><?= nl2br(espaco2nbsp($row["animsuscepad"])) ?></div>
		</div>
		<br>
		<div class="row">
			<div class="col grupo 100 quebralinha">
				<div class="titulogrupo">Informações do Produto</div>
			</div>
		</div>
		<div class="row ">
			<div class="col 100 rot quebralinha">Nome Comercial:</div>
		</div>
		<div class="row ">
			<div class="col 100 quebralinha"><?= nl2br($row["descr_prod"]) ?></div>
		</div>
		<div class="row ">
			<div class="col 100 rot quebralinha">Nº de doses por partida:</div>
		</div>
		<div class="row ">
			<div class="col 100 quebralinha"><?= nl2br(espaco2nbsp($row["ndosespart"])) ?></div>
		</div>
		<div class="row ">
			<div class="col 100 rot quebralinha">Nº de doses por propriedade:</div>
		</div>
		<div class="row ">
			<div class="col 100 quebralinha"><?= nl2br(espaco2nbsp($row["ndoses"])) ?></div>
		</div>
		<br>
		<?
		$listarItensSolfab = SolfabController::buscarItensSolfabRelatorio($row['idsolfab']);
		$i = 0;
		foreach($listarItensSolfab as $itensSolfab) 
		{
			?>
			<div class="row">
				<div class="col grupo 50 quebralinha">
					<? if ($i == 0) { ?><div class="titulogrupo">Identificação da Semente</div><? } ?>
					<?=$itensSolfab["descr"] ?>
				</div>
				<div class="col grupo 15 quebralinha">
					<? if ($i == 0) { ?><div class="titulogrupo">Partida</div><? } ?>
					<?=$itensSolfab["partida"] ?>/<?=$itensSolfab["exercicio"] ?>
				</div>
				<div class="col grupo 10 quebralinha">
					<? if ($i == 0) { ?><div class="titulogrupo">TRA</div><? } ?>
					<?=$itensSolfab["idregistro"] ?>/<?=$itensSolfab["exercicioam"] ?>
				</div>
				<div class="col grupo 10 quebralinha">
					<? if ($i == 0) { ?><div class="titulogrupo">LDA</div><? } ?>
					<?=$itensSolfab["idresultado"] ?>
				</div>
				<div class="col grupo 25 quebralinha" style="text-align:right">
					<? if ($i == 0) { ?><div class="titulogrupo nowrap">Aut. Anterior <span><i style="font-size:9px">(ID - Nº SEI)</i></span></div><? } ?>
					<?=$itensSolfab["ultimasolfab"] ?>
				</div>
			</div>
			<?
			$i++;
		}
		?>
		<br>
		<?
		if (!empty($row["observacao"]))
		{
			?>
			<div class="row ">
				<div class="col 100 rot quebralinha">Observação:</div>
			</div>
			<div class="row ">
				<div class="col 100 quebralinha"><?= nl2br(espaco2nbsp($row["observacao"])) ?></div>
			</div>
			<br>
			<?
		} //if(!empty($row["observacao"])){
		?>
		<div class="row">
			<div class="col 15 rot">Técnico Resp.:</div>
			<div class="col 25">Marcio Danilo Botrel Coutinho</div>
			<div class="col 10 rot">CRMV:</div>
			<div class="col 15">MG - 1454</div>
			<div class="col 15 rot">Assinatura.:</div>
			<div class="col "><img style="position: relative; top: 13px;" src='../inc/img/sig797.gif'></div>
		</div>
		<div class="row">
			<div class="col 15 rot">Fiscal Agropec.:</div>
			<div class="col 35 sublinhado"></div>
			<div class="col 15 rot">Parecer:</div>
			<div class="col 35 sublinhado"></div>
		</div>
		<div class="row">
			<div class="col 15 rot">Assinatura.:</div>
			<div class="col 35 sublinhado"></div>
			<div class="col 15 rot">Data:</div>
			<div class="col 35 sublinhado"></div>
		</div>
	</body>
</html>