<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
require_once(__DIR__ . "/controllers/ao_controller.php");

if ($_POST) {
	include_once("../inc/php/cbpost.php");
}

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parâmetros GET que devem ser validados para compor o select principal
 *                pk: indica parâmetro chave para o select inicial
 *                vnulo: indica parâmetros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "lote";
$pagvalcampos = array(
	"idlote" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "SELECT * from lote where idlote = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

?>
<html>
<head>
	<style>
		.rotulo {
			font-weight: bold;
			font-size: 12px;
		}

		.texto {
			font-size: 12px;
		}

		.textoitem {
			font-size: 10px;
		}

		.tabass {
			border: 0px;
			font-size: 11;
			padding: 0px;
			margin: 0px;
			display: inline;
		}

		.tabass td {
			padding: 0px;
			margin: 0px;
			padding-right: 8px;
		}

		.tabass .lb6 {
			/* label inferior à  imagem da assinatura */
			padding: 0px;
			margin: 0px;
			font-size: 6pt;
		}

		.tabass .lbresp {
			/* label inferior à  imagem da assinatura */
			padding: 0px;
			margin: 0px;
			font-size: 9px;
		}

		html {
			font-family: Arial, FreeSans, Sans, Serif, SansSerif;
			font-size: 11px;
			margin: 0px;
			padding: 0px;
		}

		body {
			margin: 0px;
			padding: 0px;
		}

		.tbrepheader {
			border: 0px;
			width: 100%;
		}

		.tbrepheader .header {
			font-size: 13px;
			font-weight: bold;
		}

		.tbrepheader .subheader {
			font-size: 10px;
			color: gray;
		}

		.tbrepheader .titulo {
			font-size: 18px;
			font-weight: bold;
		}

		.tbrepheader .res {
			font-size: 18px;
		}

		.normal {
			border: 1px solid silver;
			border-collapse: collapse;
		}

		.normal td {
			border: 1px solid silver;
			padding: 0px 3px 0px 3px;
		}

		.normal .header {
			font-size: 10px;
			font-weight: bold;
			color: rgb(75, 75, 75);
			background-color: rgb(222, 222, 222);
		}

		.normal .res {
			font-size: 11px;
		}

		.normal .res .link {
			background-color: #FFFFFF;
			cursor: pointer;
		}

		.normal .res .tot {
			background-color: #E8E8E8;
			font-weight: bold;
			text-align: center;
		}

		.normal .res .inv {
			border: 0px;
		}

		.normal .tdcounter {
			border: 1px dotted rgb(222, 222, 222);
			background-color: white;
			color: silver;
			font-size: 8px;
		}

		.newreppage {
			page-break-before: always;
		}

		.fldsheader {
			border: none;
			border-top: 2px solid silver;
			height: 0px;
			margin: 0px;
			padding: 0px;
			padding-bottom: 5px;
			padding-left: 5px;
		}

		.fldsheader legend {
			font-size: 8px;
			color: gray;
			background-color: white;
		}

		.fldsfooter {
			border: none;
			border-top: 2px solid silver;
			height: 0px;
			margin: 0px;
			padding: 0px;
			margin-top: 5px;
			padding-left: 5px;
		}

		.fldsfooter legend {
			font-size: 8px;
			color: gray;
			background-color: white;
		}

		a.btbr20 {
			display: none;
		}

		/* Botao branco fonte 8 */
		a.btbr20:link {
			position: fixed;

			right: 15px;

			font-weight: bold;
			font-size: 20px;
			color: silver;

			border: 1px solid #d7d7d7;
			cursor: pointer;

			padding-left: 5px;
			padding-right: 5px;
			padding-bottom: 1px;
			margin-left: 5px;

			background: #cccccc;
			/* para browsers sem suporte a CSS 3 */

			/* Gradiente */
			filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ececec', endColorstr='#dcdcdc');
			/* IE */
			background: -webkit-gradient(linear, left top, right top, from(#ececec), to(#dcdcdc));
			/* webkit */
			background: -moz-linear-gradient(top, #ececec, #dcdcdc);
			/* FF */

			/* Arredondamento */
			-moz-border-radius: 8px;
			-webkit-border-radius: 8px;
			border-radius: 8px 8px 8px 8px;

			text-decoration: none;
		}

		a.btbr20:hover {
			font-weight: bold;
			font-size: 20px;
			color: silver;

			border: 1px solid #d7d7d7;
			cursor: pointer;

			padding-left: 5px;
			padding-right: 5px;
			padding-bottom: 1px;
			margin-left: 5px;

			background: #eaeaf4;
			/* para browsers sem suporte a CSS 3 */

			/* Gradiente */
			filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ff0000', endColorstr='#c02900');
			/* IE */
			background: -webkit-gradient(linear, left top, right top, from(#ff0000), to(#c02900));
			/* webkit */
			background: -moz-linear-gradient(top, #ffffff, #e1e1e1);
			/* FF */

			/* Arredondamento */
			-moz-border-radius: 8px;
			-webkit-border-radius: 8px;
			border-radius: 8px 8px 8px 8px;
			text-decoration: none;
		}

		a.btbr20:visited {
			border: 1px solid silver;
			color: white;
			text-decoration: none;
		}
	</style>
	<title>Lotes P&D</title>
</head>

<body>
	<pagina class="ordContainer">
		<?


		$sql = "select c.idlotecons,c.qtdd,c.qtdd_exp, fr.qtd as qtddisp,fr.qtd_exp as qtddisp_exp,p.conteudo,
                                l.idlote,l.partida,l.exercicio,l.idprodserv,p.descr,l.status,l.observacao,((ifnull(c.qtdd,0)*ifnull(f.dose,0))/ifnull(p.qtdpadrao,0)) as qtdoses,fr.idlotefracao
                            from lotecons c 
                                join lote l 
                                join lotefracao fr
                                join prodserv p  
                                join unidade u 
                                left join prodservformula f on(  f.idprodservformula = l.idprodservformula)
                            where c.idobjeto =" . $_1_u_lote_idlote . " 
                            and c.tipoobjeto ='lote'
                            
                            and fr.idlotefracao =c.idlotefracao
                            and l.idlote=fr.idlote
                            and fr.idunidade = u.idunidade
                            " . getidempresa('l.idempresa', 'lotepesqdes') . "
                            and u.idtipounidade=13
                            and p.idprodserv =l.idprodserv";

		$res = d::b()->query($sql) or die("Erro ao buscar lotes sql=" . $sql);
		$qtd = mysqli_num_rows($res);
		if ($qtd > 0) {
		?>
			<div class="row">
				<div class="col-md-12">
					<div class="panel panel-default">
						<div class="panel-heading">


							<table class="tbrepheader">
								<tr>
									<td></td>
									<td class="header" colspan="2" nowrap><?= $_1_u_lote_partida ?>/<?= $_1_u_lote_exercicio ?> - Lote(s) Utilizado(s)</td>
								</tr>
							</table>
							<HR>

						</div>
						<div class="panel-body">
							<table class='normal'>
								<tr class="header">
									<th>Qtd</th>
									<th>Lote</th>
									<th>Descrição</th>
									<th>Valor</th>

									<th>Status</th>
									<th>Observação</th>

								</tr>
								<?
								while ($row = mysqli_fetch_assoc($res)) {


								?>
									<tr class="res">
										<td align="center">
											<?= recuperaExpoente($row['qtdd'], $row['qtdd_exp']) ?>
										</td>
										<td>

											<?= $row['partida'] ?>/<?= $row['exercicio'] ?>

										</td>
										<td>
											<?= $row['descr'] ?>
										</td>
										<td class="nowrap">
											<?= $row['qtdoses'] ?> <?= $row['conteudo'] ?>
										</td>

										<td>
											<?= $row['status'] ?>
										</td>
										<td><?= nl2br($row['observacao']) ?></td>

									</tr>
								<?
								}
								?>
							</table>
							<br>
							<hr>
							<table>
								<tr>
									<td></td>
									<td></td>
									<td>Volume Cons.:</td>
									<td><b>
											<? $arrvcon = AoController::buscarCalculoVolumeConsumo($_1_u_lote_idlote); ?>
											<label class="alert-warning" title="<?= $arrvcon['strcalc'] ?>"><?= $arrvcon['volumeconsf'] ?></label>
										</b>
									</td>
									<td>Volume Cons. Final:</td>
									<td><b>
											<? $arrvcon1 = AoController::buscarCalculoVolumeConsumo($_1_u_lote_idlote, 'ped'); ?>
											<label class="alert-warning" title="<?= $arrvcon1['strcalc'] ?>"><?= $arrvcon1['volumeconsf'] ?></label>
										</b>
									</td>
									<td>
										<i title="Impressão" class="fa fa-print pull-right fa-lg cinza pointer hoverazul" onclick="janelamodal('report/ao.php?_acao=u&idlote=<?= $_1_u_lote_idlote ?>')"></i>
									</td>
								</tr>
							</table>
						</div>
					</div>
				</div>
			</div>
		<?
		} // if($qtd>0)
		?>
	</pagina>
</body>

</html>