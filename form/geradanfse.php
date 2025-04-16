<? //hermesp 23-08-2013 
ini_set("display_errors", "1");
error_reporting(E_ALL);
use Com\Tecnick\Barcode\Barcode;
require_once("../inc/php/functions.php");

$gravaarquivo = $_GET['gravaarquivo'];

(!empty($_GET['_idempresa'])) ? $idempresa = $_GET['_idempresa'] : $idempresa = $_SESSION["SESSAO"]["IDEMPRESA"];

ob_start();


$idnotafiscal = $_GET['idnotafiscal'];
$sql = "SELECT dma(DATE_ADD(n.emissao, INTERVAL 1 month)) as issqn, n.numerorps, convert( lpad(n.nnfe, '8', '0') using latin1) as nnfe, n.nnfe as numeronfe,
			   dmahms(n.emissao) as emissao,dma(n.emissao) as dmaemissao, p.razaosocial, p.cpfcnpj, p.email, x.xml, x.xmlretconsult, x.status, n.status as statusnota,n.informacao,p.dddfixo,p.telfixo,
			   CASE
					WHEN n.idempresa = 1 THEN 'ANALISE LABORATORIAL' 
					ELSE 'SERVIÇOS PRESTADOS'
				END AS Discriminacao,p.InscricaoMunicipalTomador
		FROM pessoa p JOIN notafiscal n ON p.idpessoa = n.idpessoa 
		JOIN nfslote x ON x.numerorps = n.numerorps
		WHERE n.idempresa = x.idempresa
		AND n.idnotafiscal = $idnotafiscal
		ORDER BY x.status DESC LIMIT 1";
$res = d::b()->query($sql) or die("ERRO AO BUSCAR INFORMAÇÕES PARA PRENCHER A DANFe sql=" . mysqli_error(d::b()));
$qtd = mysqli_num_rows($res);
/*if ($qtd != 1) {
	die("1- Erro ao gerar Danfe, favor informar ao administrador do sistema." . $qtd);
}*/
$row = mysqli_fetch_assoc($res);

// buscaR CODIGO DE VERIFICACAO

$xml = ($row['xmlretconsult']);


//Carregar o XML em UTF-8
$doc = DOMDocument::loadXML($xml);

$ConsultarLoteRpsResposta = $doc->getElementsByTagName("ConsultarLoteRpsResposta")->item(0);
$ListaNfse = $ConsultarLoteRpsResposta->getElementsByTagName("ListaNfse")->item(0);

$CompNfse = $ListaNfse->getElementsByTagName("CompNfse")->item(0);
$Nfse = $CompNfse->getElementsByTagName("Nfse")->item(0);
$InfNfse = $Nfse->getElementsByTagName("InfNfse")->item(0);
$CodigoVerificacao =  $InfNfse->getElementsByTagName("CodigoVerificacao")->item(0);

$codver = ($CodigoVerificacao->textContent); //->length;

$codver = substr($codver, 0, 9);


$DeclaracaoPrestacaoServico =  $InfNfse->getElementsByTagName("DeclaracaoPrestacaoServico")->item(0);

$InfDeclaracaoPrestacaoServico =  $DeclaracaoPrestacaoServico->getElementsByTagName("InfDeclaracaoPrestacaoServico")->item(0);

$TomadorServico =  $InfDeclaracaoPrestacaoServico->getElementsByTagName("TomadorServico")->item(0);
$RazaoSocial =  $TomadorServico->getElementsByTagName("RazaoSocial")->item(0);
$vNomecli = ($RazaoSocial->textContent); //pegar o valor da tag 
$vNomecli = ($vNomecli);

$Servico =  $InfDeclaracaoPrestacaoServico->getElementsByTagName("Servico")->item(0);
$IssRetido =  $Servico->getElementsByTagName("IssRetido")->item(0);
$vIssRetido = ($IssRetido->textContent); //pegar o valor da tag 

//TipoRecolhimento
if ($vIssRetido == "1") {	
	$tiporecolhimento = "RETIDO";
} else {
	$tiporecolhimento = "A RECOLHER";
}

$ExigibilidadeISS =  $Servico->getElementsByTagName("ExigibilidadeISS")->item(0);
$vExigibilidadeISS = ($ExigibilidadeISS->textContent); //pegar o valor da tag 

$IdentificacaoTomador =  $TomadorServico->getElementsByTagName("IdentificacaoTomador")->item(0);

$CpfCnpj =  $IdentificacaoTomador->getElementsByTagName("CpfCnpj")->item(0);
$vCNPJcli = ($CpfCnpj->textContent); //pegar o valor da tag 


/*
$emailcli = $RPS->getElementsByTagName("EmailTomador")->item(0);
$vemailcli = ($emailcli->textContent); //pegar o valor da tag 
*/
$vemailcli=$row['email'];

$Endereco =  $TomadorServico->getElementsByTagName("Endereco")->item(0);

$codcidcli =  $Endereco->getElementsByTagName("CodigoMunicipio")->item(0);
$vcodcidcli = ($codcidcli->textContent); //pegar o valor da tag 


//bairro cliente
$Bairrocli = $Endereco->getElementsByTagName("Bairro")->item(0);
$vBairrocli = ($Bairrocli->textContent); //pegar o valor da tag 
$vBairrocli = ($vBairrocli);

//cep cliente
$CEPcli = $Endereco->getElementsByTagName("Cep")->item(0);
$vCEPcli = ($CEPcli->textContent); //pegar o valor da tag 
$vCEPcli = formatarCEP($vCEPcli, true);
//numero cliente
$Numcli = $Endereco->getElementsByTagName("Numero")->item(0);
$vNumcli = ($Numcli->textContent); //pegar o valor da tag 
$vNumcli = ($vNumcli);
//logradouro cliente
$Logradourocli = $Endereco->getElementsByTagName("Endereco")->item(0);
$vLogradourocli = ($Logradourocli->textContent); //pegar o valor da tag 
$vLogradourocli = ($vLogradourocli);


$DescricaoRPS =  $Servico->getElementsByTagName("Discriminacao")->item(0);
//descrição NFE
$vDescricaoRPS = ($DescricaoRPS->textContent); //pegar o valor da tag 
$vDescricaoRPS = (($vDescricaoRPS));


$sqlcid = "select uf,cidade from nfscidadesiaf where cmunfg =" . $vcodcidcli;
$rescid = d::b()->query($sqlcid) or die("Erro 1 ao buscar a cidade do cliente" . $sqlcid);
$qtdcid = mysqli_num_rows($rescid);
if ($qtdcid != 1) {
	die("Erro 2 ao buscar a cidade do cliente.");
}
$rowcid = mysqli_fetch_assoc($rescid); // cidade e uf do cliente

$Valores =  $Servico->getElementsByTagName("Valores")->item(0);


//valor total nf
$ValorTotalServicos = $Valores->getElementsByTagName("ValorServicos")->item(0);
$VL_NF = ($ValorTotalServicos->textContent);

$VISS = ($VL_NF * 2) / 100;


$vValorTotalDeducoes = 0.00;



$ValorPIS = $Valores->getElementsByTagName("ValorPis")->item(0);
$vValorPIS = ($ValorPIS->textContent); //pegar o valor da tag 

$ValorCOFINS = $Valores->getElementsByTagName("ValorCofins")->item(0);
$vValorCOFINS = ($ValorCOFINS->textContent); //pegar o valor da tag 

$ValorINSS = $Valores->getElementsByTagName("ValorInss")->item(0);
$vValorINSS = ($ValorINSS->textContent); //pegar o valor da tag 

$ValorIR = $Valores->getElementsByTagName("ValorIr")->item(0);
$vValorIR = ($ValorIR->textContent); //pegar o valor da tag 

$ValorCSLL = $Valores->getElementsByTagName("ValorCsll")->item(0);
$vValorCSLL = ($ValorCSLL->textContent); //pegar o valor da tag 	

?>
<html>

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link href=../inc/css/mtorep.css media=all rel=stylesheet type=text/css />

	<style>
		@page {
			margin: 0px;
		}

		body {
			margin-left: 50px;
		}

		table.default {
			border-spacing: 0 !important;
		}

		.divborda {
			border: 1px solid black;
			width: 90%;
			font-size: 9px;
			margin-left: -50px;
			position: relative;
			z-index: 1;
		}

		.divbordacabiten {
			border: 1px solid black;			
		}

		table.bordasimples {
			border-collapse: collapse;
			min-width: 100%;
		}

		table.bordasimples tr td {
			border: 1px solid black;
		}

		.tdnegrito {
			font-weight: bold;
		}

		table.item tr td {
			font-weight: bold;
		}

		.divitem {
			display: inline-block;
		}

		.negrito {
			font-weight: bold;
		}

		.uppercase {
			text-transform: uppercase;
		}

		<? if($row['status'] == 'CANCELADO'){ ?>
			.watermark{
				position: fixed;
				top: 10%;
				left: 5%;
				transform: translate(-50%, -50%);
				z-index: 9999;
				pointer-events: none;
				user-select: none;
			}
		<? } ?>
	</style>
</head>

<body style="margin-right:10px;">
	<? if($row['status'] == 'CANCELADO'){ 
		// URL da imagem da marca d'água
		$watermarkImage = base64_encode(file_get_contents('https://sislaudo.laudolab.com.br/inc/img/nfcancelada.png'));
		$base64Image = 'data:image/png;base64,' . $watermarkImage;
		?>
		<div class="watermark">
			<img src="<?=$base64Image?>" alt="Marca d\'água">
		</div>
	<? } ?>
	<div class='divborda'>
		<table style="width:100%">
			<tr>
				<th style='width:60%;'>
					<table>
						<tr>
							<td><img src='../inc/img/logoprefeituraudi.jpg' style='max-width:90px;'></td>
							<td align="center" style="font-size: 12px;font-weight: bold;width:10cm">
								<h2 style="margin: 0 !important;">PREFEITURA DE UBERLÂNDIA</h2>
								<h3 style='margin: 0 !important;font-weight: 300;'>SECRETARIA MUNICIPAL DE FINANÇAS</h3>
								<h3 style="margin: 0 !important;">NOTA FISCAL DE SERVIÇOS ELETRÔNICA-NFSe</h3>
							</td>
						</tr>
					</table>
				</th>
				<th style='border-left: 1px solid black; vertical-align: bottom;font-size: 10px;width: 40%;'>
					<table style="margin: 0; padding: 0; border-spacing: 0;font-weight: 300;">
						<tr>
							<td style='border-bottom: 1px solid black; padding-top: .5rem;'>Número da Nota <strong> <?=$row['nnfe'] ?></strong></td>
						</tr>
						<tr>
							<td style='border-bottom: 1px solid black; padding-top: .5rem;'>Número RPS | Série <strong> <?=$row['numerorps'] ?> | 99</strong> </td>
						</tr>
						<tr>
							<td style='border-bottom: 1px solid black; padding-top: .5rem;'>Data da Emissão <strong> <?=$row['emissao'] ?></strong></td>
						</tr>
						<tr>
							<td style="padding-top: .5rem;">Codigo de Verificação <?=$codver ?></td>
						</tr>
					</table>
				</th>
			</tr>
		</table>
	</div>
	<div class='divborda'>
		<div align='center' style='font-size: 16px; font-weight: bold;'>PRESTADOR DE SERVIÇOS</div>
		<table style="width:100%">
			<tr>
				<?
				$sqlfig = "select * from empresa where idempresa =" . $idempresa;
				$resfig = mysql_query($sqlfig) or die("Erro ao retornar figura para cabeçalho do relatà³rio: " . mysql_error());
				$figrel = mysql_fetch_assoc($resfig);
				?>
				<td rowspan='7' style='width:50px;'><img src='<?=$figrel["logosis"] ?>' style="width:100%;"></td>
			</tr>
			<tr>							
				<td nowrap>
					CNPJ: <strong><?= formatarCPF_CNPJ($figrel["cnpj"], true) ?></strong>
				</td>
				<td>
					INSCRIÇÃO MUNICIPAL: <strong><?=$figrel["InscricaoMunicipalPrestador"] ?></strong>
				</td>
				<td rowspan='6' style='width:50px;' >
					<div style="width:100%;">
						<img src="<?php
							$url = "https://nfse.uberlandia.mg.gov.br/#/verificacaoNfse/".$codver."/".$figrel["cnpj"]."/".$row['nnfe'];
							require_once('/var/www/carbon8/inc/cte/vendor/autoload.php');
							$barcode = new Barcode();
							try {
								$bobj = $barcode->getBarcodeObj(
									'QRCODE,M',
									$url,
									-2,
									-2,
									'black',
									array(-2, -2, -2, -2)
								)->setBackgroundColor('white');
								$qrcode = $bobj->getPngData();
							} catch (\Throwable $th) {
								throw $th;
							}
							
							// prepare a base64 encoded "data url"
							echo 'data://text/plain;base64,' . base64_encode($qrcode);
						?>" alt="">
					</div>
						
				</td>
			</tr>
			<tr>
				<td colspan="2">
					RAZÃO SOCIAL: <strong><?=$figrel["razaosocial"] ?></strong>
				</td>
			</tr>
			<tr>
				<td>
					ENDEREÇO: <strong><?=$figrel["xlgr"] ?> - <?=$figrel["nro"] ?></strong>
				</td>
			</tr>
			<tr>
				<td>Bairro: <strong><?=$figrel["xbairro"] ?></strong></td>
				<td>CEP: <strong><?= formatarCEP($figrel["cep"], true) ?></strong></td>
			</tr>
			<tr>
				<td nowrap> 
					MUNICÍPIO: <strong><?=$figrel["xmun"] ?></strong>
				</td>
				<td>
					UF: <strong><?=$figrel["uf"] ?></strong>
				</td>
			</tr>
			<tr>
				<td nowrap> 
					E-MAIL: <strong><?=$figrel["email"] ?></strong>
				</td>
				<td>
					TELEFONE: <strong>(<?=$figrel["DDDPrestador"] ?>)<?=$figrel["TelefonePrestador"] ?></strong>
				</td>
			</tr>
		</table>
		
	</div>
	<div class='divborda' style='height:10%;'>
		<div align='center' style='font-size: 16px; font-weight: bold;'>TOMADOR DE SERVIÇOS</div>
		<table style="width:100%">
			<tr>
				<td nowrap>CNPJ:<font style='font-weight: bold;'><? print formatarCPF_CNPJ($vCNPJcli, true); ?></font>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?if(!empty($row['InscricaoMunicipalTomador'])){?>INSCRIÇÃO MUNICIPAL:&nbsp;<font style='font-weight: bold;'><?=$row['InscricaoMunicipalTomador']?></font><?}?>
				</td>
			</tr>
			<tr>
				<td nowrap>RAZÃO SOCIAL:<font style='font-weight: bold;'><?=$vNomecli ?></font>
				</td>
			</tr>
		
			<tr>
				<td nowrap>ENDEREÇO:
					<font style='font-weight: bold;'><? echo ($vTipoLogradourocli . " " . $vLogradourocli . ", " . $vNumcli); ?></font>
				</td>
				<td>
					<span>Bairro: </span><strong><?= $vBairrocli ?></strong>
				</td>
			</tr>
			<tr>
				<td nowrap>
					<span>Município / UF: </span>
					<strong class='negrito'><?=$rowcid["cidade"] ?> / <?=$rowcid["uf"] ?></strong>
				</td>
				<td>
					<span>CEP: </span><strong><?= $vCEPcli ?></strong>
				</td>
			</tr>
			<tr>
				<td nowrap>
					E-MAIL:<font style='font-weight: bold;'><?=$vemailcli ?></font>
				</td>
				<td>
					<span>Telefone: </span><strong><?=$row['dddfixo'] ?> <?=$row['telfixo'] ?></strong>
				</td>
			</tr>
		</table>
	</div>
	<div class='divborda' style='height:auto; padding-bottom:10px;'>
		<div align='center' style='font-size: 16px; font-weight: bold;'>DISCRIMINAÇÃO DOS SERVIÇOS</div>
		<div style="padding-left:5px;">
			<br>
			<span style="font-size: 8px;"><?= $row['Discriminacao']; ?> </span>
		</div>
	</div>
	<div class='divborda' style='height:auto; padding-bottom:10px;'>
		<div align='center' style='font-size: 16px; font-weight: bold;'>SUBITEM DOS SERVIÇOS</div>
		<div style="padding-left:5px;">
			<br>
			<span style="font-size: 9px;">
				<strong><?=$figrel["ItemListaServico"] ?></strong> <?=$figrel["atividadescrserv"] ?>
			</span>
		</div>
	</div>
	<div class='divborda'>
		
		<?
		$Itens = $Servico->getElementsByTagName("Itens")->item(0);
		if(!empty($Itens)){
		?>
		<table class="default" style="width: 100%;text-align: right;">
			<thead>
				<tr>
					<th style="border-right: 1px solid;width: 40%;text-align: left;border-bottom: 1px solid;"><strong>Item</strong></th>
					<th style="border-right: 1px solid;border-bottom: 1px solid;" align="right"><strong>Item Tributável</strong></th>
					<th style="border-right: 1px solid;border-bottom: 1px solid;" align="right"><strong>Quantidade</strong></th>
					<th style="border-right: 1px solid;border-bottom: 1px solid;" align="right"><strong>Valor Unitário (R$)</strong></th>
					<th style="border-bottom: 1px solid;" align="right"><strong>Valor Total (R$)</strong></th>
				</tr>
			</thead>
			<tbody>
			<?		
				$qtditems  = $Itens->getElementsByTagName('Item')->length;
				if($qtditems>0){
					for ($nitems = 0; $nitems < $qtditems;) { //loop nos item da nf	

						$Item = $Itens->getElementsByTagName("Item")->item($nitems);			

						$ValorUn = $Item->getElementsByTagName("ValorUnitario")->item(0);
						$VL_ITEM_UN = ($ValorUn->textContent);

						$Quantidade = $Item->getElementsByTagName("Quantidade")->item(0);
						$VQuantidade = ($Quantidade->textContent);

						$DiscriminacaoServico = $Item->getElementsByTagName("Descricao")->item(0);
						$VDiscriminacaoServico = ($DiscriminacaoServico->textContent);
						$VDiscriminacaoServico = ($VDiscriminacaoServico);

					
						$VL_ITEM = ($VL_ITEM_UN * $VQuantidade);

						//mudar a cor da linha
						if ($troca == "S") {
							$fundodiv = "#FFFFFF";
							$troca = "N";
						} else {
							$fundodiv = "#D3D3D3";
							$troca = "S";
						}
						?>
						<tr style="font-size:8px;">
							<td style="border-right: 1px solid;text-align: left;"><strong><?=$VDiscriminacaoServico ?></strong></td>
							<td style="border-right: 1px solid;">SIM</td>
							<td style="border-right: 1px solid;"><?=$VQuantidade ?></td>
							<td style="border-right: 1px solid;"><?=number_format($VL_ITEM_UN, 2, ',', '.'); ?></td>
							<td><?=number_format($VL_ITEM, 2, ',', '.'); ?></td>
						</tr>
					<?
						$nitems++;
					}
				}
			}	
			?>
			</tbody>
		</table>
	</div>
	<div class='divborda' style="padding: .5rem 0;">
		<div align='center' style='font-size: 16px; font-weight: bold;'>VALOR TOTAL DA NOTA = R$ <?=number_format($VL_NF, 2, ',', '.'); ?></div>
	</div>
	<div class='divborda'>
		<div class='divitem' align='center' style='width:4cm; border-right: 1px solid black; padding: .5rem 0;'>
			<span class="uppercase">Serviço Prestado em</span>
			<br>
			<strong class='negrito' style="padding-top: .5rem;">Uberlândia/MG</strong>
		</div> 
		<div class='divitem uppercase' align='center' style='width:3cm; border-right: 1px solid black;padding: .5rem 0;'>
			<span class="uppercase">Exigibilidade</span>
			<br>
			<font class='negrito'>Incidente</font>
		</div>
		<div class='divitem' align='center' style='width:3cm; border-right: 1px solid black; padding: .5rem 0;'>
			<span class="uppercase">Imposto Devido em</span>
			<br>
			<font class='negrito'>Uberlândia/MG</font>
		</div>
		
		<div class='divitem'  align='center' style='width:3cm; border-right: 1px solid black; padding: .5rem 0;'>
			<span class="uppercase">
				Regime Tributário
			</span>
			<br>
			<font class='negrito'>-</font>
		</div>
		<div class='divitem' align='center' style='width:4cm; padding: .5rem 0;'>
			<span class="uppercase">
				Tipo Recolhimento
			</span>
			<br>
			<strong><?=$tiporecolhimento ?></strong>
		</div>
	</div>
	<div class='divborda'>
		<div class='divitem' align='center' style='width:4cm; border-right: 1px solid black; padding: .5rem 0;'>
			<span class="uppercase">
				Base de Cálculo
			</span>
			<br>
			<font class='negrito'>R$ 0,00</font>
		</div>
		<div class='divitem' align='center' style='width:3cm; border-right: 1px solid black; padding: .5rem 0;'>
			<span class="uppercase">
				Deduções
			</span>
			<br>
			<font class='negrito'>R$ <?=number_format($vValorTotalDeducoes, 2, ',', '.'); ?></font>
		</div>
		<div class='divitem' align='center' style='width:4cm; border-right: 1px solid black; padding: .5rem 0;'>
			<span class="uppercase">
				Valor dos Serviços
			</span>
			<br>
			<font class='negrito'>R$ <?=number_format($VL_NF, 2, ',', '.'); ?> </font>
		</div>
		
		<div class='divitem' align='center' style='width:3cm; border-right: 1px solid black; padding: .5rem 0;'>
			<span class="uppercase">
				Aliquota
			</span>
			<br>
			<font class='negrito'>2,00%</font>
		</div>
		<div class='divitem' align='center' style='width:3cm; padding: .5rem 0;'>
			<span class="uppercase">
				Valor ISS
			</span>
			<br>
			<font class='negrito'>R$ <?=number_format($VISS, 2, ',', '.'); ?></font>
		</div>
	</div>
	<div class='divborda' style="padding: .5rem 0;">
		<div align='center' style='font-size: 8px;'>RETENÇÕES FEDERAIS</div>
	</div>
	<div class='divborda'>
		<div class='divitem' align='center' style='width:4cm; border-right: 1px solid black; padding: .5rem 0;'>PIS<br>
			<font class='negrito'>R$ <?=number_format($vValorPIS, 2, ',', '.'); ?></font>
		</div>
		<div class='divitem' align='center' style='width:4cm; border-right: 1px solid black; padding: .5rem 0;'>COFINS<br>
			<font class='negrito'>R$ <?=number_format($vValorCOFINS, 2, ',', '.'); ?></font>
		</div>
		<div class='divitem' align='center' style='width:3cm; border-right: 1px solid black; padding: .5rem 0;'>IR<br>
			<font class='negrito'>R$ <?=number_format($vValorIR, 2, ',', '.'); ?></font>
		</div>		
		<div class='divitem' align='center' style='width:3cm;  border-right: 1px solid black; padding: .5rem 0;'>CSLL<br>
			<font class='negrito'>R$ <?=number_format($vValorCSLL, 2, ',', '.'); ?></font>
		</div>
		<div class='divitem' align='center' style='width:4cm; padding: .5rem 0;'>INSS<br>
			<font class='negrito'>R$ <?=number_format($vValorINSS, 2, ',', '.'); ?></font>
		</div>

	</div>
	<div class='divborda uppercase' style="padding: .5rem 0;">
		<div style='font-size: 10px;'>CNAE: <?=$figrel["atividadeserv"] ?> - <?=$figrel["atividadescrserv"] ?></div>
	</div>
	
	<div class='divborda' style='height:12%;'>
		<div align='center' style='font-size: 16px; font-weight: bold;'>OUTRAS INFORMAÇÕES</div>
		<table style="width:100%">			
			<tr>
				<td style='vertical-align: top;'>Mês de Competência da Nota Fiscal:<?= substr($row['dmaemissao'], -7); ?><br>
					RPS/SERIE:<?=$row['numerorps'] ?>/99 (<?=$row['dmaemissao'] ?>)<br>
					
					Data de Vencimento do ISSQN referente à esta NFSe: 15/<?= substr($row['issqn'], -7); ?><br>
					<?=$row['informacao']?>
					</td>
				<td style='vertical-align: top;'></td>
			</tr>
			<tr>
				<td></td>
			</tr>
		</table>
	</div>
</body>

</html>
<?

$html = ob_get_contents();
//limpar o codigo html
$html = preg_replace('/>\s+</', "><", $html);

//$html = mb_convert_encoding($html,  "ISO-8859-1", 'HTML-ENTITIES');

//$html = mb_convert_encoding($html, "UTF-8", mb_detect_encoding($html, "UTF-8, ISO-8859-1, ISO-8859-15", true))


ob_end_clean();


// Incluímos a biblioteca DOMPDF
require_once("../inc/dompdf/dompdf_config.inc.php");

// Instanciamos a classe
$dompdf = new DOMPDF();

// Passamos o conteúdo que será convertido para PDF
$dompdf->load_html($html);

// Definimos o tamanho do papel e
// sua orientação (retrato ou paisagem)
$dompdf->set_paper('A4', 'portrait');

// O arquivo é convertido
$dompdf->render();

if ($gravaarquivo == 'Y') {
	// Salvo no diretório  do sistema
	$output = $dompdf->output();
	$result = file_put_contents("/var/www/laudo/tmp/nfe/NFSe_" . $row['numeronfe'] . ".pdf", $output);
	if ($result === false) {
		$error = error_get_last();
		echo "Erro: " . $error['message'];
	} else {
		echo ("OK");
	}
} else {
	// Exibido para o usuário
	$dompdf->stream("NFSe_" . $row['nnfe'] . ".pdf");
}

?>