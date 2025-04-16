<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if ($_POST) {
	require_once("../inc/php/cbpost.php");
}

$geraarquivo = $_GET['geraarquivo'];
$gravaarquivo = $_GET['gravaarquivo'];
$_idempresa = $_GET['_idempresa'];
ob_start();
//error_reporting(E_ALL);

//die();

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parâmetros GET que devem ser validados para compor o select principal
 *                pk: indica parâmetro chave para o select inicial
 *                vnulo: indica parâmetros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "orcamento";
$pagvalcampos = array(
	"idorcamento" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from " . _DBAPP . ".orcamento where idorcamento = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

?>
<html>

<head>
	<title>Orçamento</title>
	<style>
		.rotulo {
			font-weight: bold;
			font-size: 10px;
		}

		.texto {
			font-size: 10px;
		}

		.textoitem {
			font-size: 9px;
		}

		.textoitem8 {
			font-size: 8px;
		}

		.bordadiv {
			margin: 0;
			margin: 0 auto;
			height: 99%;
			width: 100%;
			border: 1px solid #000000;
			border-radius: 10px;
		}

		.box {
			display: table-cell;
			text-align: center;
			vertical-align: middle;
			width: 550px;
		}

		.box * {
			vertical-align: middle;
		}
	</style>
</head>

<body style="max-width:630px;">
	<?
	$_timbradoheader = 'HEADERSERVICO';
	if ($geraarquivo == 'Y') {
		require_once("../form/timbrado.php");
	} else {
		$timbradoidempresa = $_GET["_timbradoidempresa"] != '' ? "and idempresa = " . $_GET["_timbradoidempresa"] : getImagemRelatorio('orcamento', 'idorcamento', $_1_u_orcamento_idorcamento)
		?>
		<table style="width: 100%;">
			<tr>
				<td>
					<?
					$_sqltimbrado = "select * from empresaimagem where 1 " . $timbradoidempresa . " and tipoimagem = '" . $_timbradoheader . "'";
					$_restimbrado = mysql_query($_sqltimbrado) or die("Erro ao retornar figura para cabeçalho do relatório: " . mysql_error());
					$_figtimbrado = mysql_fetch_assoc($_restimbrado);
					$_timbradocabecalho = $_figtimbrado["caminho"];
					if (!empty($_timbradocabecalho)) { ?>
						<div id="_timbradocabecalho" style="position: relative;">
							<img src="<?= $_timbradocabecalho ?>" height="80px" width="100%">
							<?
							$sqlLogo = "SELECT 1 FROM orcamentoitem o JOIN prodserv p ON p.idprodserv = o.idtipoteste 
										WHERE logoinmetro = 'Y' AND o.idorcamento = $_1_u_orcamento_idorcamento;";
							$qrLogo = d::b()->query($sqlLogo) or die("Erro ao buscar itens do orçamento:" . mysqli_error(d::b()));
							$qtdrowsLogo = mysqli_num_rows($qrLogo);
							if ($qtdrowsLogo > 0) { ?>
								<img src="../inc/img/selo-inmetron.png" border="0" style="position: absolute; top: 0; right: 0; max-height:115%;">
							<? } ?>
						</div>
					<? }
					?>
				</td>
			<tr>
		</table>
	<? } ?>
	<hr>
	<table style="width: 100%;">
		<tr>
			<td style="vertical-align: top;">
				<table>
					
					<tbody>
					<tr>
						<td align="right" class="rotulo">Cliente:</td>
						<td class="texto" nowrap><?= traduzid("pessoa", "idpessoa", "nome", $_1_u_orcamento_idpessoa) ?></td>
						<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
						<? if (!empty($_1_u_orcamento_telefone)) { ?>
							<td align="right" class="rotulo">Telefone:</td>
							<td class="texto"><?= $_1_u_orcamento_telefone ?></td>
						<? } ?>
					</tr>
					<tr>
						<td align="right" class="rotulo">A/C:</td>
						<td class="texto" nowrap><?= $_1_u_orcamento_resp ?></td>
						<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
						<? if (!empty($_1_u_orcamento_email)) { ?>
							<td align="right" class="rotulo">Email:</td>
							<td class="texto"><?= traduzid("pessoa", "idpessoa", "email", $_1_u_orcamento_idpessoa) ?></td>
						<?
						}
						?>
					</tr>
					</tbody>
				</table>
			</td>
			<td style="vertical-align: top;">
				<div align="right" style="font-size: 12px;">
					<font style="font-weight: bold;">N&#186; Orçamento:</font> <?= $_1_u_orcamento_controle ?>
				</div>
				<? if (!empty($_1_u_orcamento_dataorc)) { ?>
					<div align="right" style="font-size: 12px;">
						<font style="font-weight: bold;">Data:</font> <?= $_1_u_orcamento_dataorc ?>
					</div>
				<? } ?>
			</td>
		</tr>
	</table>

	<?

	if (!empty($_1_u_orcamento_idendereco) and !empty($_1_u_orcamento_idpessoa)) {
		$sqlf = "select c.cidade,e.logradouro,e.endereco,e.numero,e.complemento,e.bairro,e.cep,e.uf
	from nfscidadesiaf c,endereco e
	where c.codcidade = e.codcidade 
	and e.idendereco =" . $_1_u_orcamento_idendereco;
		$resf = mysql_query($sqlf) or die("erro ao buscar informações do endereço sql=" . $sqlf);
		$rowf = mysql_fetch_assoc($resf);
	?>
		<hr>
		<table>

			<tr>
				<td align="right" nowrap class="rotulo">Razão Social:</td>
				<td colspan="5" class="texto" nowrap><?= traduzid("pessoa", "idpessoa", "razaosocial", $_1_u_orcamento_idpessoa) ?></td>
			</tr>
			<tr>
				<td align="right" class="rotulo">CNPJ:</td>
				<td class="texto"><?= traduzid("pessoa", "idpessoa", "cpfcnpj", $_1_u_orcamento_idpessoa) ?></td>
				<td align="right" class="rotulo">I.E:</td>
				<td class="texto"><?= traduzid("pessoa", "idpessoa", "inscrest", $_1_u_orcamento_idpessoa) ?></td>
			</tr>
			<tr>
				<td align="right" class="rotulo" nowrap>Endereço:</td>
				<td class="texto" colspan="5"><?= $rowf["logradouro"] ?> <?= $rowf["endereco"] ?> Nº<?= $rowf["numero"] ?> <?= $rowf["complemento"] ?></td>

			</tr>
			<tr>
				<td align="right" class="rotulo">Bairro:</td>
				<td class="texto"><?= traduzid("endereco", "idendereco", "bairro", $_1_u_orcamento_idendereco) ?></td>
				<td align="right" class="rotulo">CEP:</td>
				<td class="texto"><?= traduzid("endereco", "idendereco", "cep", $_1_u_orcamento_idendereco) ?></td>
			</tr>
			<tr>
				<td align="right" class="rotulo">Cidade:</td>
				<td class="texto"><?= $rowf['cidade'] ?></td>
				<td align="right" class="rotulo">UF:</td>
				<td class="texto"><?= traduzid("endereco", "idendereco", "uf", $_1_u_orcamento_idendereco) ?></td>
			</tr>
			<tr>
				<td align="right" class="rotulo" nowrap>Email NFE:</td>
				<td colspan="5" class="texto" nowrap><?= traduzid("pessoa", "idpessoa", "email", $_1_u_orcamento_idpessoa) ?></td>
			</tr>
		</table>

	<?
	}
	?>
	<br>
	<?
	$sql3 = "select * from orcamentoitem 
		where desconto > 0 
	        and idorcamento = " . $_1_u_orcamento_idorcamento;

	$qr3 = mysql_query($sql3) or die("Erro ao buscar se existe desconto:" . mysql_error());
	$qtdrows3 = mysql_num_rows($qr3);


	$sql = "SELECT i.idorcamentoitem,i.idorcamento,i.idtipoteste,i.qtd,i.valor,i.desconto,i.valtotal,p.idportaria,
			concat(t.tipoteste,' ',ifnull(p.codigo,' ')) as tipoteste,t.sigla,i.valorun,i.obs, t.oficial, t.logoinmetro
			FROM orcamentoitem i,vwtipoteste t left join portaria p on(t.idportaria=p.idportaria)
	        where t.idtipoteste = i.idtipoteste
	        and i.idorcamento = " . $_1_u_orcamento_idorcamento . " order by t.tipoteste";
	$qr = mysql_query($sql) or die("Erro ao buscar itens do orçamento:" . mysql_error());
	$qtdrows = mysql_num_rows($qr);
	if ($qtdrows > 0) {

	?>
		<hr>
		<table>
			<tr style="background-color: #B5B5B5">
				<td align="center" class="rotulo">Descrição do Item</td>
				<? if ($_1_u_orcamento_vervalores == "Y") { ?>
					<td align="center" class="rotulo">Valor Unit. (R$)</td>
				<? } ?>
				<td align="center" class="rotulo">Qtd</td>
				<? if ($_1_u_orcamento_vervalores == "Y") { ?>
					<td align="center" class="rotulo">Valor Total (R$)</td>
					<? if ($qtdrows3 > 0) { ?>
						<td align="center" class="rotulo">Desconto %</td>
					<? } ?>
					<td align="center" class="rotulo">Valor Final (R$)</td>
				<? } ?>
				<td align="center" class="rotulo">Obs.</td>
			</tr>
			<?
			$vtotaliten = 0.00;
			$troca = "S";
			$virg = "";
			while ($row = mysql_fetch_array($qr)) {
				if (!empty($row['idportaria'])) {
					$legportaria = "S";
					$inidportaria = $inidportaria . $virg . $row['idportaria'];
					$virg = ",";
				}
				$vtotaliten = $vtotaliten + $row["valtotal"];
				if ($troca == "S") {
					$cortr = "#FFFFFF";
					$troca = "N";
				} else {
					$cortr = "#E8E8E8";
					$troca = "S";
				}
			?>

				<tr style="background-color: <?= $cortr ?>">
					<td align="left" class="textoitem" <? echo $row["logoinmetro"] == 'Y' ? 'style="font-weight: bold;"' : ''; ?>><?= $row["tipoteste"] ?></td>
					<? if ($_1_u_orcamento_vervalores == "Y") { ?>
						<td align="center" class="textoitem"><?= number_format($row["valorun"], 2, ',', ''); ?></td>
					<? } ?>
					<td align="center" class="textoitem"><?= $row["qtd"] ?></td>
					<? if ($_1_u_orcamento_vervalores == "Y") { ?>
						<td align="center" class="textoitem"><?= number_format($row["valor"], 2, ',', '');  ?></td>
						<? if ($qtdrows3 > 0) { ?>
							<td align="center" class="textoitem"><?= number_format($row["desconto"], 2, ',', ''); ?></td>
						<? } ?>
						<td align="center" class="textoitem"><?= number_format($row["valtotal"], 2, ',', '') ; ?></td>
					<? } ?>
					<td align="center" class="textoitem"><?= $row["obs"]; ?></td>
				</tr>
			<?
			}
			$fvtotaliten = round($vtotaliten, 2);
			if ($_1_u_orcamento_vertotalitem == "Y" and $_1_u_orcamento_vervalores == "Y") {
			?>
				<tr>
					<? if ($qtdrows3 > 0) { ?>
						<td colspan="4"></td>
					<? } else { ?>
						<td colspan="3"></td>
					<? } ?>
					<td align="center" style="background-color: #B5B5B5;" class="rotulo" nowrap>Total Item (R$)</td>
					<td align="center" style="background-color: #B5B5B5;" class="rotulo"><?= number_format($fvtotaliten, 2, ',', ''); ?></td>
				</tr>
			<?
			}
			?>
		</table>

		<?
		if ($legportaria == "S") {
			$sqlport = "select idportaria,portaria,codigo,referencia,tipo from portaria where idempresa = '" . $_idempresa . "' and idportaria in(" . $inidportaria . ")";
			$resport = mysql_query($sqlport) or die("Erro ao buscar portaria sql=" . $sqlport);
			$qtdport = mysql_num_rows($resport);
			if ($qtdport > 0) {
		?>
				<table>
					<?
					while ($rowport = mysql_fetch_assoc($resport)) {
						if ($troca == "S") {
							$cortr = "#FFFFFF";
							$troca = "N";
						} else {
							$cortr = "#E8E8E8";
							$troca = "S";
						}
						?>
						<tr>
							<td class="textoitem">
								<? echo ($rowport['codigo'] . " " . $rowport['tipo'] . " MAPA N&#186;. " . $rowport['portaria'] . ", de " . $rowport['referencia']); ?>
							</td>
						</tr>
						<tr><td>&nbsp;</td></tr>
						<?
					}
					?>
					<tr>
						<td class="textoitem">
							NOTA 1: Texto em negrito, representa ensaios acreditados pela Coordenação Geral de Acreditação do Inmetro para Ensaios ABNT NBR ISO/IEC 17025 sob número 0767. Somente os relatórios de ensaios acreditados, serão emitidos com o símbolo da acreditação.
						</td>
					</tr>
					<tr><td>&nbsp;</td></tr>
					<tr>
						<td class="textoitem">
							NOTA 2: Resultados provenientes de amostras de coletas oficiais, poderão ser colocados em domínio público, caso seja obrigado por lei, normativa ou ordem judicial, conforme a instrução normativa N°57, de 11 Dezembro de 2013 e ISO / IEC 17025.
						</td>
					</tr>
				</table>
		<?
			}
		}
	}

	$sql1 = "SELECT *
			FROM orcamentoadic 
	        where idorcamento =" . $_1_u_orcamento_idorcamento;
	$res1 = mysql_query($sql1) or die("A Consulta  dos materiais solicitados falhou : " . mysql_error() . "<p>SQL: $sql1");
	$qtdrows1 = mysql_num_rows($res1);
	if ($qtdrows1 > 0) {
		?>
		<hr>
		<table>
			<tr style="background-color: #B5B5B5">
				<td align="center" colspan="2" class="rotulo">Adicionais do Orçamento</td>
			</tr>
			<tr style="background-color: #B5B5B5">
				<td align="center" class="rotulo">Descrição</td>
				<? if ($_1_u_orcamento_vervalores == "Y") { ?>
					<td align="center" class="rotulo">Valor</td>
				<? } ?>
			</tr>
			<?
			$vtotaadic = 0.00;
			$troca = "S";
			while ($row1 = mysql_fetch_array($res1)) {
				$vtotaadic = $vtotaadic + $row1["valor"];
				if ($troca == "S") {
					$cortr = "#FFFFFF";
					$troca = "N";
				} else {
					$cortr = "#E8E8E8";
					$troca = "S";
				}
			?>
				<tr style="background-color: <?= $cortr ?>">
					<td align="left" class="textoitem"><?= $row1["descr"] ?></td>
					<? if ($_1_u_orcamento_vervalores == "Y") { ?>
						<td align="center" class="textoitem"><?= number_format($row1["valor"], 2, ',', '')  ?></td>
					<? } ?>
				</tr>
			<?
			}
			$fvtotaladic = round($vtotaadic, 2);
			?>
			<? if ($_1_u_orcamento_vervalores == "Y") { ?>
				<tr>
					<td align="center" style="background-color: #B5B5B5;" class="rotulo">Total Adicionais</td>
					<td align="center" style="background-color: #B5B5B5;" class="rotulo"><?= number_format($fvtotaladic, 2, ',', ''); ?></td>
				</tr>
			<? } ?>
		</table>

		<br></br>
	<?
	}
	?>

	<hr>
	<table>
		<tr>
			<td class="rotulo" align="right">Proposta válida por:</td>
			<td class="texto"><?= $_1_u_orcamento_validade ?> dia(s).</td>
		</tr>

		<? if (!empty($_1_u_orcamento_diasentrada)) { ?>
			<tr>
				<td class="rotulo" align="right">Prazo de pagamento:</td>
				<td class="texto">
					<font style="font-weight: bold;"><?= $_1_u_orcamento_diasentrada ?></font> dia(s).
				</td>
			</tr>

		<? } ?>
		<? if (!empty($_1_u_orcamento_formapgto)) { ?>
			<tr>
				<td class="rotulo" align="right">Forma de pagamento:</td>
				<td class="texto"><?= $_1_u_orcamento_formapgto ?></td>
			</tr>
		<? } ?>
		<?
		$_1_u_orcamento_total = $fvtotaladic + $fvtotaliten;
		if ($_1_u_orcamento_vertotalorc == "Y" and $_1_u_orcamento_vervalores == "Y") {
		?>
			<tr>
				<td class="rotulo" align="right">Total do orçamento(R$):</td>
				<td class="rotulo"><?= number_format($_1_u_orcamento_total, 2, ',', ''); ?></td>
			</tr>
		<?
		}
		?>
	</table>
	<br>
	<? if (!empty($_1_u_orcamento_obs)) { ?>
		<table>
			<tr>
				<td colspan="4" class="rotulo">Obs.:</td>
			</tr>
			<tr>
				<td colspan="4" class="texto">
					<div style="width:450px; height:240px;">
						<?= nl2br(strtoupper($_1_u_orcamento_obs)) ?>
					</div>
				</td>
			</tr>
		</table>
	<? } ?>
	<?
	if ($geraarquivo != 'Y') { ?>
		<table style="width: 100%;">
			<tr>
				<td>
					<?
					$timbradoidempresa = $_GET["_timbradoidempresa"] != '' ? "and idempresa = " . $_GET["_timbradoidempresa"] : getImagemRelatorio('orcamento', 'idorcamento', $_1_u_orcamento_idorcamento);

					$_sqltimbrado2 = "select * from empresaimagem where 1 " . $timbradoidempresa . " and tipoimagem = 'IMAGEMRODAPE'";
					$_restimbrado2 = mysql_query($_sqltimbrado2) or die("Erro ao retornar figura do relatório: " . mysql_error());
					$_figtimbrado2 = mysql_fetch_assoc($_restimbrado2);

					$_timbradorodape = $_figtimbrado2["caminho"];

					if (!empty($_timbradorodape)) { ?>
						<div id="_timbradocabecalho" style="position: relative;">
							<img src="<?= $_timbradocabecalho ?>" height="80px" width="100%">
							<?
							$sqlLogo = "SELECT 1 FROM orcamentoitem o JOIN prodserv p ON p.idprodserv = o.idtipoteste 
										WHERE logoinmetro = 'Y' AND o.idorcamento = $_1_u_orcamento_idorcamento;";
							$qrLogo = d::b()->query($sqlLogo) or die("Erro ao buscar itens do orçamento:" . mysqli_error(d::b()));
							$qtdrowsLogo = mysqli_num_rows($qrLogo);
							if ($qtdrowsLogo > 0) { ?>
								<img src="../inc/img/selo-inmetron.png" border="0" style="position: absolute; top: 0; right: 0; max-height:115%;">
							<? } ?>
						</div>
					<? }
					?>
				</td>
			<tr>
		</table>
	<? }
	?>
</body>

</html>
<?
if ($geraarquivo == 'Y') {

	$html = ob_get_contents();
	//limpar o codigo html
	$html = preg_replace('/>\s+</', "><", $html);

	ob_end_clean();

	//echo($html);die;


	// Incluímos a biblioteca DOMPDF
	require_once("../inc/dompdf/dompdf_config.inc.php");

	// Instanciamos a classe
	$dompdf = new DOMPDF();

	// Passamos o conteúdo que será convertido para PDF
	$html = preg_match("//u", $html) ? utf8_decode($html) : $html; //MAF060519: Converter para ISO8859-1. @todo: executar upgrade no dompdf
	$dompdf->load_html($html);

	// Definimos o tamanho do papel e
	// sua orientação (retrato ou paisagem)
	$dompdf->set_paper('A4', 'portrait');

	// O arquivo é convertido
	$dompdf->render();

	if ($gravaarquivo == 'Y') {
		// Salvo no diretório temporário do sistema
		$output = $dompdf->output();
		file_put_contents("/var/www/carbon8/upload/nfe/Orcamentos_" . $_1_u_orcamento_idorcamento . ".pdf", $output);
    	echo("OK");
	} else {
		// e exibido para o usuário
		$dompdf->stream("Orcamento_" . $_1_u_orcamento_idorcamento . ".pdf");
	}
}

?>