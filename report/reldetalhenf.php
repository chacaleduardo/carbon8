<?
require_once("../inc/php/functions.php");
$geraarquivo=$_GET['geraarquivo'];
$gravaarquivo=$_GET['gravaarquivo'];
ob_start();
//error_reporting(E_ALL);
################################################## Atribuindo o resultado do metodo GET

//$controle_1      	= $_GET["controle_1"];
//$controle_2      	= $_GET["controle_2"];
$nnfe_1      		= $_GET["nnfe_1"];
$nnfe_2      		= $_GET["nnfe_2"];
$nnfe               = $_GET["nnfe"];

$idpessoa      		= $_GET["idpessoa"];
$idnotafiscal      		= $_GET["idnotafiscal"];
$cliente      		= $_GET["cliente"];
$exercicio      	= $_GET["exercicio"];

$idregistro_1      	= $_GET["idamostra_1"];
$idregistro_2      	= $_GET["idamostra_2"];

$emissao_1    	= $_GET["emissao_1"];
$emissao_2		= $_GET["emissao_2"];
#ADICIONADO CAMPO LOTE PARA CONSULTA DE NOTAS POR LOTE 
$lote			= $_GET["lote"];


if (!empty($nnfe_1) or !empty($nnfe_2)){
	if (is_numeric($nnfe_1) and is_numeric($nnfe_2)){
		$clausula .= " and (nf.nnfe BETWEEN " . $nnfe_1 ." and " . $nnfe_2 .")";
	}else{
		die ("O campo N&ordm; NF informado possui caracteres inv&aacute;lidos: [".$nnfe_1."] e [".$nnfe_2."]");
	}
}

if (!empty($idpessoa)){
	$clausula .= " and nf.idpessoa = " .$idpessoa ." ";
	if(!empty($lote)){
		$clausula .= " idregistro in (select idregistro from amostra where idpessoa = " .$idpessoa ." and lote = '".$lote ."') and ";
	}
}

if(!empty($idnotafiscal)){
	$clausula .= " and nf.idnotafiscal = " .$idnotafiscal ." ";
}

if(!empty($cliente)){	
	$clausula .= " and p.nome like ('%" .$cliente ."%') ";
}

if (!empty($exercicio)){
	$clausula .= " and nf.exercicio = " .$exercicio ." ";
}

if (!empty($nnfe)){
	$clausula .= " and nf.nnfe = '" .$nnfe ."'";
}

if (!empty($idregistro_1) or !empty($idregistro_2)){
	if (is_numeric($idregistro_1) and is_numeric($idregistro_2)){
		$clausula .= " and (a.idregistro BETWEEN " . $idregistro_1 ." and " . $idregistro_2 .")";
	}else{
		die ("Os N&ordm;s de Registro informados s&atilde;o inv&aacute;lidos: [".$idregistro_1."] e [".$idamostra_2."]");
	}
}

if (!empty($emissao_1) or !empty($emissao_2)){
	$dataini = validadate($emissao_1);
	$datafim = validadate($emissao_2);
	if ($dataini and $datafim){
		$clausula .= " and (nf.emissao  BETWEEN '" . $dataini ."' and '" .$datafim ."')";
	}else{
		die ("Datas n&atilde;o V&aacute;lidas!");
	}
}

// $clausula .= " (controle is not null) and ";

if (empty($clausula)){
	die("E necessário informar pelo aumenos um parâmetro para a busca.");
	//$clausula = ' where ' . substr($clausula,1,strlen($clausula) - 5);
}

/*$sql = "SELECT 
	    i.*
	    -- ,(select l.nnfe from "._DBNFE.".nfslote l where l.numerorps = i.numerorps and l.status='SUCESSO') as nnfe
		FROM 
		vwnfamostraitens i " . $clausula . " order by i.nnfe, if(isnull(i.idresultado),1,0),i.idamostra";*/

$sql = "  SELECT nf.idnotafiscal,
				nf.idempresa,
				nf.nnfe,
				p.nome,
				p.dddfixo,
				p.telfixo,
				p.cpfcnpj,
				p.inscrest,
				p.contrato,
				p.centrocusto,
				DMA(p.vigencia) AS vigencia,
				p.razaosocial,
				p.email,
				p.idpessoa,
				nfi.idamostra,
				nfi.idresultado,
				nfi.idnotafiscal,
				nf.numerorps,
				SUM(nfi.quantidade) AS quantidade,
				nfi.descricao,
				ROUND(nfi.valor, 2) AS valorunitario,
				ROUND(SUM((nfi.valor * nfi.quantidade)), 2) AS subtotal,
				nfi.desconto,
				((nfi.valor - ROUND((nfi.valor * (nfi.desconto / 100)), 2)) * SUM(nfi.quantidade)) AS totals,
				ROUND(((nfi.valor - ROUND((nfi.valor * (nfi.desconto / 100)), 2)) * SUM(nfi.quantidade)), 2) AS total,
				ISNULL(MAX(nfi.idresultado)) AS complemento,
				nf.emissao,
				DMA(nf.emissao) AS dmaemissao,
				nf.exercicio,
				a.idregistro,
				a.estexterno,
				DMA(a.dataamostra) AS dataamostra,
				a.lote,
				a.nucleoamostra,
				a.galpao,
				a.granja,
				t.subtipoamostra AS tipoamostra,
				ps.codprodserv,
				r.npedido,
				pf.pedidocp,
				IF((a.idpessoaresponsavel IS NULL OR a.idpessoaresponsavel = ''), a.responsavel, pc.nome) AS solicitante,
				a.idpessoaresponsavel AS idsolicitante
			FROM notafiscal nf JOIN notafiscalitens nfi ON  nf.idnotafiscal = nfi.idnotafiscal
			JOIN pessoa p ON p.idpessoa = nf.idpessoa
	   LEFT JOIN preferencia pf ON (pf.idpreferencia = p.idpreferencia)
	   LEFT JOIN resultado r ON (r.idresultado = nfi.idresultado)
	   LEFT JOIN amostra a ON (a.idamostra = r.idamostra)
	   LEFT JOIN subtipoamostra t ON (t.idsubtipoamostra = a.idsubtipoamostra)
	   LEFT JOIN prodserv ps ON (ps.idprodserv = r.idtipoteste)
	   LEFT JOIN pessoa pc ON pc.idpessoa = a.idpessoaresponsavel
		   WHERE 1 $clausula
		GROUP BY a.idregistro, nfi.idamostra, nfi.idnotafiscal, nfi.descricao, nfi.valor, nfi.desconto
		ORDER BY a.exercicio, a.idregistro, nf.nnfe, p.nome";
//echo "<!-- ".$sql." -->";

$res = d::b()->query($sql) or die("Falha ao pesquisar Nota Fiscal : " . mysqli_error(d::b()) . "<p>SQL: $sql");

################################################## Inicializa as variaveis para o relatorio
$titulo = "Detalhamento Nota Fiscal:";
$grp1 = "nnfe";
$col1 = array(
		"idnotafiscal" => "idnotafiscal",
		"idpessoa" =>"idpessoa",
		"dddfixo" =>"dddfixo",
		"telfixo" =>"telfixo",
		"inscrest" => "inscrest",
		"vigencia" => "vigencia",
		"contrato" => "contrato",
		"centrocusto" => "centrocusto",
		"email" =>"email",
		"cpfcnpj" => "cpfcnpj",
		"razaosocial" => "razaosocial",
		"nome" => "Cliente",
		"idregistro" => "N&ordm; Reg",
		"npedido" => "N&ordm; Pedido",
		"quantidade" => "Qt",
		"descricao" => "Descr",
		"total" => "Total",
		"totals" => "totals",
		"valorunitario" => "Valor Unit&aacute;rio",
		"subtotal" => "Sub Total",
		"desconto" => "Desc.",
		"dmaemissao" => "dmaemissao",
		"dataamostra" => "dataamostra",
		"lote" => "lote",
		"tipoamostra" => "tipoamostra",
		"galpao" => "galpao",
		"granja" => "granja",
		"codprodserv" => "codprodserv",
		"nucleoamostra" => "nucleoamostra",
		"solicitante" => "Solicitante");
$res1 = array();
$i1 = 0;

$qtdres=mysqli_num_rows($res);
if($qtdres<1){
	die("Não há detalhamento para este cliente!!!");
}

################################################## Agrupa resultados da consulta para o grupo indicado na variavel $grp1
while ($row = mysqli_fetch_array($res)){
	$i1++;
	reset($col1);
	foreach ($col1 as $k1 => $v1) {
		$res1[$row[$grp1]][$i1][$k1] = $row[$k1];
	}

	if($row['pedidocp'] == 'Y'){
		$pedido='Y';
	}
}
//mysqli_freeresult($res);
//print_r($res1); die();

################################################## Inicio do Relat'orio
?>
<html>
<head>
<title>Sislaudo - <?=$titulo?></title>
<meta http-equiv="Content-Type" content="text/html;" charset="UTF-8">

<?
$qitens=0;
$i=1;
foreach ($res1 as $k1 => $v1){
$vlrtotal = 0.00;
$idnotafiscal=$res1[$k1][$i]["idnotafiscal"];
?>

</head>
<body style="max-width:1100px;">
<?
	$_timbradoheader = 'HEADERSERVICO';
	if($geraarquivo == 'Y'){
		require_once("../form/timbrado.php");
	}else{
		$timbradoidempresa = $_GET["_timbradoidempresa"] != ''? "and idempresa = ".$_GET["_timbradoidempresa"]:getImagemRelatorio('notafiscal', 'idnotafiscal', $idnotafiscal);
		?>
		<table style="width: 100%;">
			<tr>
				<td>
					<?
						$_sqltimbrado="select * from empresaimagem where 1 ".$timbradoidempresa." and tipoimagem = '".$_timbradoheader."'";
						$_restimbrado = mysql_query($_sqltimbrado) or die("Erro ao retornar figura para cabeçalho do relatório: ".mysql_error());
						$_figtimbrado=mysql_fetch_assoc($_restimbrado);
						$_timbradocabecalho = $_figtimbrado["caminho"];
						if(!empty($_timbradocabecalho)){?>
						<div class="container">
							<div class="row">
									<div id="_timbradocabecalho"><img src="<?=$_timbradocabecalho?>" height="105px"></div>
							</div>
						</div>
						<?}
					?>
				</td>
			<tr>
		</table>
	<?}?>

<hr>
<table  style="font-size: 10px; border-spacing: 0px;">	
<tr>
	<td style="vertical-align: top; width: 800px;">
	<table  style="font-size: 12px;">	
	<tr> 
		<td align="right" class="rotulo">Cliente:</td> 
		<td class="texto" nowrap><font style="font-weight: bold"><?print_r($res1[$k1][$i]["nome"]);?></font></td>
		<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
	<?if(!empty($res1[$k1][$i]["dddfixo"]) and !empty($res1[$k1][$i]["telfixo"])){?>	 
		<td align="right" class="rotulo">Telefone:</td> 
		<td class="texto" nowrap><font style="font-weight: bold"><?print_r($res1[$k1][$i]["dddfixo"]);?>-<?print_r($res1[$k1][$i]["telfixo"]);?></font></td> 
	<?}?>		
	</tr>
	<tr> 
		<?if(!empty($res1[$k1][$i]["email"])){?>
		<td align="right" class="rotulo">Email:</td>
		<td class="texto"><font style="font-weight: bold"><?print_r($res1[$k1][$i]["email"]);?></font></td>
		<?
		}
		?>
		<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
		<td align="right" class="rotulo"></td> 
		<td class="texto" nowrap></td> 
	</tr>
	</table>
	</td>
	<td style="vertical-align: top; width: 100%">
	<div align="right" style="font-size: 12px;">N&#186; Detalhamento:<font style="font-weight: bold;"> <?print_r($res1[$k1][$i]["idnotafiscal"]);?></font></div>
	<?if(!empty($res1[$k1][$i]["dmaemissao"])){?>	
	<div align="right" style="font-size: 12px;">Data: <font style="font-weight: bold;"><?print_r($res1[$k1][$i]["dmaemissao"]);?></font></div>
	<?}?>	
	</td>
</tr>
</table>
<hr>
<table  style="font-size: 12px; border-spacing: 0px;">	
	<tr>
		<td align="left" colspan="5">
		<table style="font-size: 12px; border-spacing: 0px;">
		<?
		if(!empty($res1[$k1][$i]["razaosocial"])){?>
		<tr style="background-color: #FFFFFF"> 
			<td  colspan="3">Raz&atilde;o Social: <font style="font-weight: bold"><?print_r($res1[$k1][$i]["razaosocial"]);?></font></td>				
		</tr>
		<?}
		?>
		<tr style="background-color: #FFFFFF"> 
		<?
		if(!empty($res1[$k1][$i]["cpfcnpj"])){?>
			<td  colspan="3">CNPJ/CPF: <font style="font-weight: bold"><?$cnpj=formatarCPF_CNPJ($res1[$k1][$i]["cpfcnpj"],true); echo($cnpj);?></font></td>	
		<?}
		if(!empty($res1[$k1][$i]["inscrest"])){?>
			<td>IE: <font style="font-weight: bold"><?print_r($res1[$k1][$i]["inscrest"]);?></font></td>
		<?}?>				
		</tr>
		<?				
		if(!empty($res1[$k1][$i]["idpessoa"])){
		$sqle="select c.cidade,e.logradouro,e.endereco,e.numero,e.complemento,e.bairro,e.cep,e.uf
			from nfscidadesiaf c,endereco e
			where c.codcidade = e.codcidade 
			and e.status = 'ATIVO'
			and e.idtipoendereco = 2
			and e.idpessoa =".$res1[$k1][$i]["idpessoa"];	 
			$rese=d::b()->query($sqle) or die("erro ao buscar informações do endereço de entrega sql=".$sqle);
			$rowe=mysqli_fetch_assoc($rese);
			$qtde=mysqli_num_rows($rese);
			if($qtde>0){
		?>
		<tr style="background-color: #FFFFFF"> 
			<td  colspan="3" style="width: 500px;">Endere&ccedil;o:<font style="font-weight: bold"><?=$rowe["logradouro"]?> <?=$rowe["endereco"]?> N&#186;<?=$rowe["numero"]?> <?=$rowe["complemento"]?></font></td>
			
		</tr>
		<tr style="background-color: #FFFFFF"> 
		<?if(!empty($rowe["bairro"])){?>
			<td  colspan="3">Bairro: <font style="font-weight: bold"><?=$rowe["bairro"]?></font></td>
		<?}
		if(!empty($rowe["cep"])){?>
			<td>CEP:<font style="font-weight: bold"> <?	$cep=formatarCEP($rowe["cep"],true); echo($cep);?></font></td>
		<?}?>
		</tr>
		<?if(!empty($rowe['cidade']) or !empty($rowe["uf"])){?>
		<tr style="background-color: #FFFFFF"> 
		<?if(!empty($rowe['cidade'])){?>
			<td  colspan="3">Cidade:<font style="font-weight: bold"><?=$rowe['cidade']?></font></td>
		<?}
		if(!empty($rowe["uf"])){?>
			<td >UF:<font style="font-weight: bold"><?=$rowe["uf"]?></font></td>
		<?}?>
		</tr>
		<?}?>
		
		<?
			}
		}
		if(!empty($res1[$k1][$i]["email"])){?>
		<tr style="background-color: #FFFFFF"> 
			<td  colspan="3">Email: <font style="font-weight: bold"><?print_r($res1[$k1][$i]["email"]);?></font></td>				
		</tr>
		<?}
		?>
		<?if(!empty($res1[$k1][$i]["contrato"]) or !empty($res1[$k1][$i]["contrato"]) or !empty($res1[$k1][$i]["vigencia"])){?>
		<tr style="background-color: #FFFFFF"> 
		<?
		if(!empty($res1[$k1][$i]["contrato"])){?>
			<!--<td nowrap>Contrato: <font style="font-weight: bold"><?print_r($res1[$k1][$i]["contrato"]);?></font></td>	-->
			<tr>
                <td colspan="3">Contrato de Serviço: <font style="font-weight: bold">
		<?
			$sqls ="SELECT c.idcontratopessoa,p.*
				FROM contratopessoa c,contrato p
				where p.status = 'ATIVO'
				and p.tipo = 'S'
				and p.idcontrato = c.idcontrato
				and c.idpessoa =".$res1[$k1][$i]["idpessoa"];							
			
			  	$ress = d::b()->query($sqls) or die("A Consulta dos contratos falhou :".mysqli_error()."<br>Sql:".$sqls); 
				$qtdrows= mysqli_num_rows($ress);
				
				if($qtdrows> 0){
					while($rows = mysqli_fetch_array($ress)){					
			?>					
                                       <?=$rows["numero"]?>
									</td>
<?
					}//while($rows = mysqli_fetch_array($ress)){
				}
?>

                    </tr>
		<?}
		if(!empty($res1[$k1][$i]["centrocusto"])){?>
			<td nowrap>Centro Custo: <font style="font-weight: bold"><?print_r($res1[$k1][$i]["centrocusto"]);?></font></td>			
		<?}	
		if(!empty($res1[$k1][$i]["vigencia"])){?>
			<td>Validade: <font style="font-weight: bold"><?print_r($res1[$k1][$i]["vigencia"]);?></font></td>				
				
		<?}?>
		</tr>		
		<?
		}
		if(!empty($k1)){?>
		<tr style="background-color: #FFFFFF"> 	
			<td  colspan="3">N&ordm; NF: <font style="font-weight: bold"><?=$k1?></font></td>
		</tr>
		<?}
		/*
		if(!empty($res1[$k1][$i]["dmaemissao"])){?>
		
			<td  colspan="3">Emiss&atilde;o: <font style="font-weight: bold"><?print_r($res1[$k1][$i]["dmaemissao"]);?></font></td>
		
		<?}*/?>
		
		</table>
		<p>
		</td>

	<!-- 	<td align="right" colspan="10" style="vertical-align: top;">
		<table>
			<tr>
				<td align="right"><img style="width:150px;" src="../img/logolateral.jpg"></td>
			</tr>
		</table>
		</td>
		 -->
	</tr>	
	</table>
	<hr>
	<table style="font-size: 9px; width:100%; " >
	<tr style="background-color: #B5B5B5; font-size: 11px;"> 
		<td nowrap style="text-align: center; font-weight: bold; wid; padding-left: 5px; padding-right: 5px;"> Data Reg </td>
		<td nowrap style="text-align: center; font-weight: bold; padding-left: 5px; padding-right: 5px;">N&ordm; Reg</td>
                <?if($pedido=='Y'){ ?>
		<td nowrap style="text-align: center; font-weight: bold; padding-left: 5px; padding-right: 5px;">N&ordm; Pedido</td>
                <?}?>
		<?if(!empty($res1[$k1][$i]["estexterno"])){?>
		<td nowrap style="text-align: center; font-weight: bold; padding-left: 5px; padding-right: 5px;">Est. Externo</td>
		<?}?>
		<td nowrap style="text-align: center; font-weight: bold; padding-left: 5px; padding-right: 5px;">Galp&atilde;o/ <br /> Avi&aacute;rio:</td>
		<td nowrap style="text-align: center; font-weight: bold; padding-left: 5px; padding-right: 5px;">Lote</td>
		<td nowrap style="text-align: center; font-weight: bold; padding-left: 5px; padding-right: 5px;">N&uacute;cleo</td>
		<td nowrap style="text-align: center; font-weight: bold; padding-left: 5px; padding-right: 5px;">Granja</td>
		<td nowrap style="text-align: center; font-weight: bold; padding-left: 5px; padding-right: 5px;">Amostra</td>		
		<td nowrap style="text-align: center; font-weight: bold; padding-left: 5px; padding-right: 5px;">Descri&ccedil;&atilde;o - Sigla</td>
		<td nowrap style="text-align: center; font-weight: bold; padding-left: 5px; padding-right: 5px;">Solicitante</td>
		<td nowrap style="text-align: center; font-weight: bold; padding-left: 5px; padding-right: 5px;">Qtd</td>
		<td nowrap style="text-align: center; font-weight: bold; padding-left: 5px; padding-right: 5px;">Valor <br> Unit.</td>
		<td nowrap style="text-align: center; font-weight: bold; padding-left: 5px; padding-right: 5px;">SubTotal</td>
		<td nowrap style="text-align: center; font-weight: bold; padding-left: 5px; padding-right: 5px;">Desc.</td>
		<td nowrap style="text-align: center; font-weight: bold; padding-left: 5px; padding-right: 5px; width: 5%">Total</td>
	</tr>
	<?
	$troca="S";
	$sumtotal=0;
	foreach ($v1 as $k2 => $v2){
		if($troca=="S"){
				$cortr = "#FFFFFF";
				$troca="N";
			}else{
				$cortr = "#E8E8E8";
				$troca="S";
			} 
		$qitens++;
		$vlrtotal = $vlrtotal + $v2["totals"];
		$i=$i+1;
		$sumtotal=$sumtotal+$v2["total"];
		?>
		<tr style="background-color: <?=$cortr?>; font-size: 9px;"> 
			<td style="text-align: center;"><?=$v2["dataamostra"]?></td>
			<td style="text-align: center;"><?=$v2["idregistro"]?></td>
					<?if($pedido=='Y'){?>
			<td style="text-align: center;"><?=$v2["npedido"]?></td>
			<?}?>
			<?if(!empty($v2["estexterno"])){?>
			<td style="text-align: center;"><?=$v2["estexterno"]?></td>
			<?}?>
			<td style="text-align: center; "><?=$v2["galpao"]?></td>
			<td style="text-align: center; "><?=$v2["lote"]?></td>
			<td style="text-align: center; "><?=$v2["nucleoamostra"]?></td>
			<td style="text-align: center; "><?=$v2["granja"]?></td>
			<td style="text-align: center;"><?=$v2["tipoamostra"]?></td>
			<td ><?=$v2["descricao"]?> - <?=$v2["codprodserv"]?></td>
			<td style="text-align: center;"><?=$v2["solicitante"]?></td>
			<td style="text-align: center;"><?=$v2["quantidade"]?></td>
			<td style="text-align: right;"><?=$v2["valorunitario"]?></td>
			<td style="text-align: right;"><?=$v2["subtotal"]?></td>
			<td style="text-align: right;"><?=$v2["desconto"]?>%</td>
			<td style="text-align: right;"><?=$v2["total"]?></td>
		</tr>
		<?
	}
		
	$codepress = md5(date('dmYHis')); //gera um codigo para a impressao
	?>
	<tr>
		<td align="left" colspan="12">Qtd. Itens:<font style="font-weight: bold;"><?=$qitens?></font></td>
		<td align="right" colspan="2">Total:<font style="font-weight: bold;"><?=number_format(tratanumero($vlrtotal), 2, ',', '.'); ?></font></td>
		
	</tr>
	</table>
	<?
	$sql="select
					idempresa,
					idnotafiscal,
					sum(quantidade) AS quantidade,
					descricao,
					round(valor, 2) AS valorunitario,
					round(sum((valor * quantidade)),2) AS subtotal,
					desconto,
					round(((valor - round((valor * (desconto / 100)),2)) * sum(quantidade)),2)  AS total,
					isnull(max(idresultado)) AS complemento
					from
					notafiscalitens
					where idnotafiscal = ".$idnotafiscal."
					group by idnotafiscal,descricao,valor,desconto ORDER BY complemento, descricao";
	//echo $sql;
	$resc = d::b()->query($sql) or die("A Consulta falhou : " . mysqli_error(d::b()) . "<p>SQL: $sql");
	$iitens = mysqli_num_rows($resc);

	?>
						<hr>
						<table style="font-size: 9px;">
						<tr style="background-color: #B5B5B5; font-size: 11px;"> 
							<td colspan="6" style="text-align: center; font-weight: bold;">RESUMO DO DETALHAMENTO N&ordm; <?=$idnotafiscal?> <?if(!empty($k1)){?> - NOTA FISCAL: <?=$k1?><?}?></td>
						</tr>
						<tr style="background-color: #B5B5B5; font-size: 11px;"> 
							<td style="text-align: center; font-weight: bold;">Qtd</td>
							<td style="text-align: center; font-weight: bold;" >Descri&ccedil;&atilde;o</td>
							<td style="text-align: center; font-weight: bold;">Valor Unit.</td>
							<td style="text-align: center; font-weight: bold;">SubTotal</td>
							<td style="text-align: center; font-weight: bold;">Desc</td>
							<td style="text-align: center; font-weight: bold;">Total</td>
						</tr>
						<?
						$troca="S";
						while ($rowc = mysqli_fetch_array($resc)) {
						if($troca=="S"){
							$cortr = "#FFFFFF";
							$troca="N";
						}else{
							$cortr = "#E8E8E8";
							$troca="S";
						}
						?>
						<tr style="background-color: <?=$cortr?>; font-size: 8px;"> 
						
							<td style="text-align: center;"><?=$rowc["quantidade"]?></td>
							<td style="text-align: left; "><?=$rowc["descricao"]?></td>
							<td style="text-align: right;"><?=$rowc["valorunitario"]?></td>
							<td style="text-align: right;"><?=$rowc["subtotal"]?></td>
							<td style="text-align: right;"><?=$rowc["desconto"]?>%</td>
							<td style="text-align: right;"><?=$rowc["total"]?></td>
						</tr>
						<?
						}
						?>	
						<tr>
							<td colspan="4"></td>
							<td align="right" colspan="2">Total:<font style="font-weight: bold;"><?=number_format(tratanumero($vlrtotal), 2, ',', '.'); ?></font></td>
						</tr>	
						<tr>
							<td><p></td>
						</tr>			
						<tr>
							<td colspan="10" align="right"><div class="nimptop" style="">Imp: <?=$codepress?>;  NF.: [<?=$k1?>];</div><!-- Controle Impressao --></td>
						</tr>
					</table>
<? if($geraarquivo != 'Y'){?>
	<p style="page-break-after: always;"></p>
<?
}

$qitens=0;
}
?>
<?
		if($geraarquivo!='Y'){?>
			<table style="width: 100%;">
			<tr>
				<td>
					<?
						$timbradoidempresa = $_GET["_timbradoidempresa"] != ''? "and idempresa = ".$_GET["_timbradoidempresa"]:getImagemRelatorio('notafiscal', 'idnotafiscal', $idnotafiscal);

						$_sqltimbrado2="select * from empresaimagem where 1 ".$timbradoidempresa." and tipoimagem = 'IMAGEMRODAPE'";
						$_restimbrado2 = mysql_query($_sqltimbrado2) or die("Erro ao retornar figura do relatório: ".mysql_error());
						$_figtimbrado2=mysql_fetch_assoc($_restimbrado2);
						
						$_timbradorodape = $_figtimbrado2["caminho"];
						
						if(!empty($_timbradorodape)){?>
							<div id="_timbradorodape"><img src="<?=$_timbradorodape?>" height="80px" width="100%"></div>
						<?}
					?>
				</td>
			<tr>
		</table>
		<?}
	?>
</body>
</html>
<?
	if(empty($nnfe)){
		$nnfe = "Det_".$idnotafiscal;
	}
if($geraarquivo=='Y'){
	
	

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
    $html=preg_match("//u", $html)?utf8_decode($html):$html; //MAF060519: Converter para ISO8859-1. @todo: executar upgrade no dompdf
	
	$dompdf->load_html($html);

	// Definimos o tamanho do papel e
	// sua orientação (retrato ou paisagem)
	$dompdf->set_paper(array(0, 0, 690, 841.89),'landscape');

	// O arquivo é convertido
	$dompdf->render();
  
	if($gravaarquivo=='Y'){
		// Salvo no diretório temporário do sistema
		$output = $dompdf->output();
    	$result = file_put_contents("/var/www/laudo/tmp/nfe/Detalhamento_".$nnfe.".pdf",$output);
    	if ($result === false) {
			$error = error_get_last();
			echo "Erro: " . $error['message'];
		} else {
			echo ("OK");
		}
	}else{
		// e exibido para o usuário
		$dompdf->stream("Detalhe_".$nnfe.".pdf");
	}
}
?>
