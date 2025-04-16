<?
require_once("../inc/php/validaacesso.php");

if(empty($_GET["idamostra"])){
	die("Amostra não enviada");
}

//Caso tenha o GET provisório terá o título Envio senão Recebimento e não aparece as LDA's no relatório. - Lidiane (17/06/2020)
$provisorio = $_GET["provisorio"];

// $oTra=getObjeto("tra", $_GET["idamostra"],"idamostra");

$oAm=getAmostra($_GET["idamostra"]);
//print_r($oAm);

if ($oAm['status'] == 'ABERTO' and $oAm['status'] != 'PROVISORIO'){
	?>
	<link rel="stylesheet" href="../inc/css/bootstrap/css/bootstrap.min.css" />
	<br>
	<? //Comentado a pedido do Rui porque não deve aparecer em lugar nenhum ?>
	<!--div class="row">
		<div class="col-md-12">
			<div class="alert alert-warning aviso" role="alert" style="font-size:12px !important;">

			<strong><i class="glyphicon glyphicon-info-sign"></i> Este teste está  <b>em análise</b>.
			<br/>
			<br/>Em caso de dúvida, entre em contato conosco:
			<br/>Email: resultados@laudolab.com.br
			<br/>Telefone: (34) 3222 5700 / Whatsapp: (34) 9 9130-1330
			</div>
		</div>
	</div-->
	<?
}

$idsubtipoamostra=$oAm["idsubtipoamostra"];

$oRes=getResultados($_GET["idamostra"]);

$oAAm=getAgenteAmostra($_GET["idamostra"]);

//Verificação de quais inputs serão mostrados no TRA
//$arrConfInputs = getAmostraConfInputs($_GET["unidadepadrao"]);
$idunidade = $_GET["unidadepadrao"];
//Verificação de quais inputs serão mostrados no LDA
//$arrConfInputsLDA = getAmostraConfInputs(6);

//Mostrar ou esconder divs conforme configuração
function hide($inCol){
	global $idsubtipoamostra,$idunidade;
	$sql = 'select * from amostracampos where idunidade = '.$idunidade.' and idsubtipoamostra = '.$idsubtipoamostra.' and campo="'.$inCol.'" and visualizatra = "Y"';
	$res = d::b()->query($sql) or die("Erro ao recuperar Tipo/Subtipo de Amostra: ".mysqli_error(d::b()));
	$nres = mysqli_num_rows($res);
	if($nres>0){
		$row = mysqli_fetch_assoc($res);
		$arrConf['colunas'][$row["idunidade"]][$row["idsubtipoamostra"]][$row["campo"]]=$row["visualizatra"];
	}
	if($arrConf["colunas"][$idunidade][$idsubtipoamostra][$inCol] == 'Y'){
		return "";
	}else{
		return "hidden";
	}
}
//Mostrar ou esconder divs conforme configuração
function hidelda($inCol,$idsubtipoamostralda){
	$sql = 'select * from amostracampos where idunidade = 6 and campo="'.$inCol.'"';
	$res = d::b()->query($sql) or die("Erro ao recuperar Tipo/Subtipo de Amostra: ".mysqli_error(d::b()));
	$nres = mysqli_num_rows($res);
	if($nres>0){
		$row = mysqli_fetch_assoc($res);
		$arrConf['colunas'][$row["idunidade"]][$row["idsubtipoamostra"]][$row["campo"]]=$row["campo"];
	}
	if($arrConf["colunas"]["6"][$idsubtipoamostralda][$inCol]){
		return "";
	}else{
		return "hidden";
	}
}

function getAgenteAmostra($inidamostra){
	$sqla="select r.idresultado,l.partida,l.exercicio,l.idlote,l.status,p.descr
			from lote l,resultado r,prodserv p,amostra a
			where l.tipoobjetosolipor='resultado' 
			and l.idobjetosolipor=r.idresultado
			and p.idprodserv = l.idprodserv
			and r.idamostra=a.idamostra
			and a.idamostratra = ".$inidamostra." order by r.ord";
	$resa=d::b()->query($sqla) or die("Erro ao buscar agentes da amostra : " . mysql_error() . "<p>SQL:".$sqla);
	$qtdresa=mysqli_num_rows($resa);
	//alteracao para buscar substituir forma antiga de relacionar amostras e tra hermesp 03-05-2019
	if($qtdresa<1){
		$sqla="select r.idresultado,l.partida,l.exercicio,l.idlote,l.status,p.descr
			from lote l,resultado r,prodserv p,amostra a
			where l.tipoobjetosolipor='resultado' 
			and l.idobjetosolipor=r.idresultado
			and p.idprodserv = l.idprodserv
			and r.idamostra=a.idamostra
			and a.idamostra= ".$inidamostra." order by r.ord";
		$resa=d::b()->query($sqla) or die("Erro 2 ao buscar agentes da amostra  2: " . mysql_error() . "<p>SQL:".$sqla);
	}

	$arrColunas = mysqli_fetch_fields($resa);
	$i=0;
	$arrret=array();
	while($r = mysqli_fetch_assoc($resa)){
		$i=$i+1;
		//para cada coluna resultante do select cria-se um item no array
		foreach ($arrColunas as $col) {
			//$arrret[$i][$col->name]=$robj[$col->name];
			$arrret[$r["idresultado"]][$r["idlote"]][$col->name]=$r[$col->name];//alterado para agrupar pelo resultado, para facilitar os loops
		}
	}
	return $arrret;
}

//@todo: realizar a verificação através de Classes
function validaDataExame($ascriadoem,$dataamostra){
	if($ascriadoem<$dataamostra){
		die("<h1>Erro: Data Final Exame [".$ascriadoem."] < Data Início Exame [".$dataamostra."]</h1>");
	}
}

ob_start();
?>
<!DOCTYPE html>
<html>
	<head>		
		<?
		$titulo="";
		if($provisorio == 'Y'){
			$titulo="Termo de Envio de Amostra";
			$sub="TEA";
		} elseif($oAm["status"]=="ABERTO" or $oAm["status"]=="ENVIADO"){		
			$titulo="Termo de Recebimento de Amostra";
			$sub="TRA";			
		}elseif($oAm["status"]=="DEVOLVIDO" or $oAm["status"]=="ASSINADO"){
			$titulo="Termo de Recepção de Amostra";
			$sub="TRA";
		}
		?>
		<title><?=$titulo?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<style>
			@media print { 
			* {
				-webkit-transition: none !important;
				transition: none !important;
			}
			}
			* {
				text-shadow: none !important;
				filter:none !important;
				-ms-filter:none !important;
				font-family: Helvetica, Arial;
				font-size: 10px;
				-webkit-box-sizing: border-box; 
				-moz-box-sizing: border-box;    
				box-sizing: border-box; 
			}
			html{
				background-color: silver;
				margin: 0px;
			}
			body {
				line-height: 1.4em;
				background-color: white;
			}

			@media screen{
				body {
					margin: auto;
					margin-top: 0.2cm;
					margin-bottom: 1cm;
					padding: 3mm 10mm;
					width: 21cm;
				}
				.quebrapagina{
					page-break-before:always;
					border: 2px solid #c0c0c0;
					width: 120%;
					margin: 1.5cm -1.5cm;
				}
				.rot{
					color: gray;
				}
			}

			@media print{
				html{
					background-color: transparent;
				}
				body {
					margin: 0cm;
				}
				.quebrapagina{
					page-break-before:always;
				}
				.rot{
					color: #777777;
				}
			}

			[class*='5']{width: 5%;}
			[class*='10']{width: 9%;}
			[class*='15']{width: 15%;}
			[class*='20']{width: 20%;}
			[class*='25']{width: 25%;}
			[class*='30']{width: 30%;}
			[class*='35']{width: 35%;}
			[class*='40']{width: 39.99%;}
			[class*='45']{width: 45%;}
			[class*='50']{width: 50%;}
			[class*='55']{width: 55%;}
			[class*='60']{width: 60%;}
			[class*='65']{width: 65%;}
			[class*='70']{width: 70%;}
			[class*='75']{width: 75%;}
			[class*='80']{width: 80%;}
			[class*='85']{width: 85%;}
			[class*='90']{width: 90%;}
			[class*='95']{width: 95%;}
			[class*='100']{width: 100%;}
			header{
				background-color: white;
				top: 0;
				height: 1cm;
				line-height: 1cm;
				display: table;
			}
			hr{
				margin: 0;
			}
			.logosup{
				height: inherit;
				line-height: inherit;
				display: table-cell;
			}
			.logosup img{
				height: 0.5cm;
				vertical-align: middle;
			}
			.logosuptd{
				width: 199px;
				height: 24px;
			}
			.titulodoc{
				height: inherit;
				line-height: inherit;
				display: table-cell;
				text-align: center;
				font-size: 0.5cm;
				font-weight: bold;
			}
			.titulodoctd{
				text-align: center;
				font-size: 0.5cm;
				font-weight: bold;
			}
			.row{
				display: table;
				table-layout: fixed;
				width: 99%;
				margin: 0mm 0mm;
			}
			.linhainferior{
				border-bottom: 1px dashed gray;
			}
			.col{
				display: table-cell;
				white-space: nowrap;
				padding: 1.5mm 1mm;
			}
			.row.grid .col{
				border: 1px solid silver;
				
			}
			.row.grid .col:first-child{
				border-top: 1px solid silver;
			}
			.titulogrupotd{
				margin: 0px;
				border-bottom: 1px solid silver;
				color: #777777;
				font-weight: bold;
				Xmargin-bottom: 2mm;
			}
			.col.grupo {}
			.col.grupo .titulogrupo{
				margin: 0px;
				border-bottom: 1px solid silver;
				color: #777777;
				font-weight: bold;
				Xmargin-bottom: 2mm;
			}
			.rot{
				overflow: hidden;
				font-size: 9px;
			}
			.rottd{
				overflow: hidden;
				font-size: 9px;
				display: table-cell;
				white-space: nowrap;
				padding: 1.5mm 1mm;
				color: gray;
				vertical-align: top;
			}
			.padd{
				padding: 1.5mm 1mm;
				vertical-align: top;
			}
			.quebralinha{
				white-space: normal;
			}
			[class*='margem0.0']{
				margin: 0 0;
			}
			.hidden{
				display: none;
			}
			.sublinhado{
				border-bottom: 1px dashed gray;
			}
			.fonte8{
				font-size: 8px;
			}
			.resultadodescritivo{
				margin: 0 0;        
			}
			.resultadodescritivo p{
				margin: 0 0;	
			}
			p{
				font-size: 9px;
			}
			span{
				font-size: 9px !important;
			}
			.alinhaassinatura {
				text-align: center;
			}

			.lb6{
				font-size: 9px;
				line-height: 8px;
				color: gray;
			}

			.lbresp {
				font-size: 9px;
				line-height: 8px;
				font-weight: bold;
				color: gray;
			}
		</style>
	</head>
	<body style="max-width:1100px;">
		<table  style="width: 90%; margin: 0px;">
			<tr>
				<td colspan="6">
					<table style="width: 100%;">
						<tr>
							<td style="width: 20%;">			
								<?
								// GVT - 17/04/2020 - Altera logo do relatório, utiliza a imagem cadastrada na empresa.
								$_timbrado = $_GET["_timbrado"] != ''? $_GET["_timbrado"]:'';
								$timbradoidempresa = $_GET["_timbradoidempresa"] != ''? "and idempresa = ".$_GET["_timbradoidempresa"]:getImagemRelatorio('amostra', 'idamostra', $_GET["idamostra"]);
								
								if($_timbrado != 'N'){

									if ($oAm["dataamostrah"] >= '2021-05-18 00:00:01' and  ( $_SESSION["SESSAO"]["IDEMPRESA"] == 1 || $_SESSION["SESSAO"]["IDEMPRESA"] == 2)){
										$timbradoidempresa = "and idempresa = 2";
									 }
							
									$_sqltimbrado="select * from empresaimagem where 1 ".$timbradoidempresa." and tipoimagem = 'HEADERPRODUTO'";
									$_restimbrado = mysql_query($_sqltimbrado) or die("Erro ao retornar figura para cabeçalho do relatório: ".mysql_error());
									$_figtimbrado=mysql_fetch_assoc($_restimbrado);

									$_sqltimbrado1="select * from empresaimagem where 1 ".$timbradoidempresa." and tipoimagem = 'IMAGEMMARCADAGUA'";
									$_restimbrado1 = mysql_query($_sqltimbrado1) or die("Erro ao retornar figura do relatório: ".mysql_error());
									$_figtimbrado1=mysql_fetch_assoc($_restimbrado1);

									$_sqltimbrado2="select * from empresaimagem where 1 ".$timbradoidempresa." and tipoimagem = 'IMAGEMRODAPE'";
									$_restimbrado2 = mysql_query($_sqltimbrado2) or die("Erro ao retornar figura do relatório: ".mysql_error());
									$_figtimbrado2=mysql_fetch_assoc($_restimbrado2);
									
									$_timbradocabecalho = $_figtimbrado["caminho"];
									$_timbradomarcadagua = $_figtimbrado1["caminho"];
									$_timbradorodape = $_figtimbrado2["caminho"];
									
									if(!empty($_timbradocabecalho)){?>
										<img src="<?=$_timbradocabecalho?>" class="logosuptd">
									<?}
								}					
								?>								
							</td>
							<td class="titulodoctd" style="width: 80%;" height="50px" colspan="3">
								<?=$titulo?>
							</td>
						</tr>
					</table>
				</td>
				
			</tr>
			<? if($provisorio != 'Y' && $oAm["idregistroprovisorio"] != NULL){ ?>
				<tr>
					<td>N&ordm; <?=$sub?>:</td>
					<td><?=$oAm["idregistro"]?>/<?=$oAm["exercicio"]?></td>
					<td>Data<!-- Registro-->:</td>
					<td><?=dmahms($oAm["dataamostrah"], true)?></td>
				</tr>
			<? } ?>
			<tr>
				<td colspan="6" style="padding: 10px 0 6px 0px;"><div class="titulogrupotd">Dados do Cliente</div></td>
			</tr>
			<tr>
				<td class="rottd">Cliente:</td>
				<td colspan="5" class="padd"><?=$oAm["razaosocial"]?></td>
			</tr>
			<tr>
				<td class="rottd">Propriedade/Granja:</td>
				<td colspan="5" class="padd"><?=$oAm["nome"]?></td>
			</tr>		
			<tr>
				<td class="rottd">Endereço:</td>
				<td colspan="5" class="padd">
					<?
					if(empty($oAm["enderecosacado"])){
						?>
			    		<div class="alert alert-warning">
							<span class="notProducao"><i class="fa fa-exclamation-triangle"></i>&nbsp;Favor preencher o endereço da propriedade no cadastro do cliente!</span>
			    		</div>		    
						<?
					}else{
			   			echo($oAm["enderecosacado"]);
					}
					?>
				</td>
			</tr>
			<tr>
				<td class="rottd">CNPJ:</td>
				<td class="col padd"><?=formatarCPF_CNPJ($oAm["cpfcnpj"])?></td>
				<td class="rottd">I.E.</td>
				<td class="col padd" colspan="4"><?=$oAm["inscrest"]?></td>
			</tr>
			<tr>
			<? $string = $oAm['idpessoaresponsavelof']?'idpessoaresponsavelof':'responsavelof';?>
				<td class="<?=hide($string)?> rottd">Responsável<br>Oficial:</td>
				<td class="<?=hide($string)?> col padd"><?=$oAm["responsavelof"]?></td>
			<? $string = $oAm['idpessoaresponsavelcrmvof']?'idpessoaresponsavelcrmvof':'responsavelofcrmv';?>
				<td class="<?=hide($string)?> rottd">CRMV:</td>
				<td class="<?=hide($string)?> col padd"><?=$oAm["responsavelofcrmv"]?></td>
				<td class="<?=hide("responsaveloftel")?> rottd">Contato:</td>
				<td class="<?=hide("responsaveloftel")?> col padd"><?=$oAm["responsaveloftel"]?></td>							
			</tr>
			<tr>
				<td class="<?=hide("numeroanimais")?> rottd">Total de Animais<br>da Propriedade:</td>
				<td class="<?=hide("numeroanimais")?> col padd" colspan="5"><?=$oAm["numeroanimais"]?></td>
			</tr>
			<tr>
				<td colspan="6" style="padding: 10px 0 6px 0px;"><div class="titulogrupotd">Dados da Amostra</div></td>
			</tr>
			<tr>
				<td class="<?=hide("idespeciefinalidade")?> rottd">Espécie/Finalidade:</td>
				<td class="<?=hide("idespeciefinalidade")?> padd" colspan="5"><?=$oAm["especietipofinalidade"]?></td>
			</tr>
			<tr>
				<td class="rottd">Material colhido:</td>
				<td class="padd"><?=$oAm["subtipoamostra"]?></td>
				<td class="<?=hide("nroamostra")?> rottd">Quantidade:</td>
				<td class="<?=hide("nroamostra")?> padd"><?=$oAm["nroamostra"]?></td>
				<td class="<?=hide("datacoleta")?> rottd">Data Coleta:</td>
				<td class="<?=hide("datacoleta")?> padd"><?=dma($oAm["datacoleta"])?></td>
			</tr>
			<tr>
				<td class="<?=hide("descricao")?> rottd">Descrição:</td>
				<td class="<?=hide("descricao")?> padd" colspan="5"><?=$oAm["descricao"]?></td>
			</tr>
			<tr>
				<td class="<?=hide("idnucleo")?> rottd">Núcleo:</td>
				<td class="<?=hide("idnucleo")?> padd"><?=$oAm["nucleoamostra"]?></td>
				<td class="<?=hide("galpao")?> rottd">Galpão:</td>
				<td class="<?=hide("galpao")?> padd"><?=$oAm["galpao"]?></td>
			</tr>
			<tr>
				<td class="<?=hide("idade")?> rottd">Idade:</td>
				<td class="<?=hide("idade")?> padd" colspan="5"><?=$oAm["idade"]." ".$oAm["tipoidade"]?></td>
			</tr>
			<tr>
				<td class="<?=hide("lote")?> rottd">Lote:</td>
				<td class="<?=hide("lote")?> padd" colspan="5"><?=$oAm["lote"]?></td>
			</tr>
			<tr>
				<td class="<?=hide("linha")?> rottd">Linha:</td>
				<td class="<?=hide("linha")?> padd"><?=$oAm["linha"]?></td>
				<td class="<?=hide("regoficial")?> rottd">Nº Registro oficial:</td>
				<td class="<?=hide("regoficial")?> padd"><?=$oAm["regoficial"]?></td>
			</tr>
			<tr>
				<td class="<?=hide("formaarmazen")?> rottd">Forma de<br>Armazenamento.:</td>
				<td colspan="2" class="<?=hide("formaarmazen")?> padd"><?=$oAm["formaarmazen"]?></td>
				<td class="<?=hide("meiotransp")?> rottd">Meio de transp.:</td>
				<td colspan="2" class="<?=hide("meiotransp")?> padd"><?=$oAm["meiotransp"]?></td>
			</tr>
			<tr>
				<td class="<?=hide("condconservacao")?> rottd">Condições de<br>Conservação:</td>
				<td class="<?=hide("condconservacao")?> padd" colspan="5"><?=$oAm["condconservacao"]?></td>
			</tr>
			<tr>
				<td class="<?=hide("sexo")?> rottd">Sexo:</td>
				<td class="<?=hide("sexo")?> padd"><?=$oAm["sexo"]?></td>
				<td class="<?=hide("clienteterceiro")?> rottd">Cliente 3&ordm;:</td>
				<td class="<?=hide("clienteterceiro")?> padd"><?=$oAm["clienteterceiro"]?></td>
			</tr>
			<tr>
				<td class="<?=hide("nucleoorigem")?> rottd">Núcleo origem:</td>
				<td class="<?=hide("nucleoorigem")?> padd"><?=$oAm["nucleoorigem"]?></td>
				<td class="<?=hide("tipo")?> rottd">Tipo:</td>
				<td class="<?=hide("tipo")?> padd"><?=$oAm["tipo"]?></td>
			</tr>
			<tr>
				<td class="<?=hide("especificacao")?> rottd">Especificações:</td>
				<td class="<?=hide("especificacao")?> padd"><?=$oAm["especificacao"]?></td>
				<td class="<?=hide("partida")?> rottd">Partida:</td>
				<td class="<?=hide("partida")?> padd"><?=$oAm["partida"]?></td>
				<td class="<?=hide("fornecedor")?> rottd">Fornecedor:</td>
				<td class="<?=hide("fornecedor")?> padd"><?=$oAm["fornecedor"]?></td>							
			</tr>
			<tr>
				<td class="<?=hide("datafabricacao")?> rottd">Data fabricação:</td>
				<td class="<?=hide("datafabricacao")?> padd"><?=dma($oAm["datafabricacao"])?></td>
				<td class="<?=hide("identificacaochip")?> rottd">Chip/Identif.:</td>
				<td class="<?=hide("identificacaochip")?> padd"><?=$oAm["identificacaochip"]?></td>
				<td class="<?=hide("diluicoes")?> rottd">Diluições:</td>
				<td class="<?=hide("diluicoes")?> padd"><?=$oAm["diluicoes"]?></td>
			</tr>
			<tr>
				<td class="<?=hide("nroplacas")?> rottd">Nº Placas:</td>
				<td class="<?=hide("nroplacas")?> padd"><?=$oAm["nroplacas"]?></td>
				<td class="<?=hide("nrodoses")?> rottd">Nº Doses:</td>
				<td class="<?=hide("nrodoses")?> padd"><?=$oAm["nrodoses"]?></td>
				<td class="<?=hide("semana")?> rottd">Semana:</td>
				<td class="<?=hide("semana")?> padd"><?=$oAm["semana"]?></td>
			</tr>	
			<tr>
				<td class="<?=hide("notafiscal")?> rottd">Nota Fiscal:</td>
				<td class="<?=hide("notafiscal")?> padd"><?=$oAm["notafiscal"]?></td>
				<td class="<?=hide("vencimento")?> rottd">Vencimento:</td>
				<td class="<?=hide("vencimento")?> padd"><?=$oAm["vencimento"]?></td>
				<td class="<?=hide("fabricante")?> rottd">Fabricante:</td>
				<td class="<?=hide("fabricante")?> padd"><?=$oAm["fabricante"]?></td>
			</tr>
			<tr>
				<td class="<?=hide("sexadores")?> rottd">Sexadores:</td>
				<td class="<?=hide("sexadores")?> padd"><?=$oAm["sexadores"]?></td>
				<td class="<?=hide("localexp")?> rottd">Local específico:</td>
				<td class="<?=hide("localexp")?> padd"><?=$oAm["localexp"]?></td>
			</tr>
			<tr>
				<td class="<?=hide("lacre")?> rottd">Lacre:</td>
				<td class="<?=hide("lacre")?> padd"><?=$oAm["lacre"]?></td>
				<td class="<?=hide("tc")?> rottd">Termo de coleta:</td>
				<td class="<?=hide("tc")?> padd"><?=$oAm["tc"]?></td>
			</tr>
			<tr>
				<td colspan="6" style="padding: 10px 0 6px 0px;"><div class="titulogrupotd">Dados Epidemiológicos</div></td>
			</tr>
			<tr>
				<td class="<?=hide("sinaisclinicosinicio")?> rottd">Início sinais clínicos:</td>
				<td class="<?=hide("sinaisclinicosinicio")?> padd"  colspan="5"><?=$oAm["sinaisclinicosinicio"]?></td>
			</tr>
			<tr>
				<td class="<?=hide("sinaisclinicos")?> rottd">Sinais clínicos:</td>
				<td class="<?=hide("sinaisclinicos")?> padd" colspan="5"><?=nl2br($oAm["sinaisclinicos"])?></td>
			</tr>
			<tr>
				<td class="<?=hide("achadosnecropsia")?> rottd">Achados necrópsia:</td>
				<td class="<?=hide("achadosnecropsia")?> padd" colspan="5"><?=nl2br($oAm["achadosnecropsia"])?></td>
			</tr>
			<tr>
				<td class="<?=hide("suspclinicas")?> rottd">Suspeitas clínicas:</td>
				<td class="<?=hide("suspclinicas")?> padd" colspan="5"><?=nl2br($oAm["suspclinicas"])?></td>
			</tr>
			<tr>
				<td class="<?=hide("histproblema")?> rottd">Histórico problema:</td>
				<td class="<?=hide("histproblema")?> padd" colspan="5"><?=nl2br($oAm["histproblema"])?></td>
			</tr>
			<tr>
				<td class="<?=hide("morbidade")?> rottd">Morbidade<span class="fonte8"> (N&ordm; animais)</span>:</td>
				<td class="<?=hide("morbidade")?> padd"><?=$oAm["morbidade"]?></td>
				<td class="<?=hide("letalidade")?> rottd">Letalidade<span class="fonte8"> (N&ordm; animais)</span>:</td>
				<td class="<?=hide("letalidade")?> padd"><?=$oAm["letalidade"]?></td>
				<td class="<?=hide("mortalidade")?> rottd">Mortalidade<span class="fonte8"> (N&ordm; animais)</span>:</td>
				<td class="<?=hide("mortalidade")?>quebralinha padd"><?=$oAm["mortalidade"]?></td>
			</tr>
			<tr>
				<td class="<?=hide("usomedicamentos")?> rottd">Uso medicamentos:</td>
				<td class="<?=hide("usomedicamentos")?> padd"><?=$oAm["usomedicamentos"]?></td>
				<td class="<?=hide("usovacinas")?> rottd">Uso de vacinas:</td>
				<td class="<?=hide("usovacinas")?> padd" colspan="3"><?=$oAm["usovacinas"]?></td>
			</tr>
			<tr>
				<td class="<?=hide("localcoleta")?> rottd">Local coleta:</td>
				<td class="<?=hide("localcoleta")?> padd" colspan="5"><?=$oAm["localcoleta"]?></td>
			</tr>
			<tr>
			<? $string = $oAm['idpessoaresponsavel']?'idpessoaresponsavel':'responsavel';?>
				<td class="<?=hide($string)?> rottd">Responsável<br>Coleta:</td>
				<td class="<?=hide($string)?> padd"><?=$oAm["responsavel"]?></td>
			<? $string2 = $oAm['idpessoaresponsavelcrmv']?'idpessoaresponsavelcrmv':'responsavelcolcrmv';?>
				<td class="<?=hide($string2)?> rottd">CRMV:</td>
				<td class="<?=hide($string2)?> padd"><?=$oAm["responsavelcolcrmv"]?></td>
				<td class="<?=hide("responsavelcolcont")?> rottd">Contato:</td>
				<td class="<?=hide("responsavelcolcont")?> padd"><?=$oAm["responsavelcolcont"]?></td>
			</tr>
			<tr>
				<td class="<?=hide("observacao")?> rottd">Observação:</td>
				<td class="<?=hide("observacao")?> padd" colspan="5"><?=nl2br($oAm["observacao"])?></td>
			</tr>
			<? if($provisorio != 'Y') { ?>
				<tr>
					<td colspan="6" style="padding: 10px 0 6px 0px;"><div class="titulogrupotd">Exames Solicitados</div></td>
				</tr>
			<?
			}
			if($oAm['status'] != 'PROVISORIO' && $provisorio != 'Y'){
				$i=0;
				while (list($k, $v) = each($oRes)){?>
				
					<tr>
						<td colspan="6" >
							<?echo("LDA: ".$v["idresultado"]." - ".$v["descr"]);?>
						</td>
					</tr>
					<?
					$i++;
            	}
        	}else{
				?>
				<?if($oAm["sorologico"]){?>
					<tr>
						<td class="rottd">Sorológico:</td>
						<td colspan="5" class="padd"><?=nl2br($oAm["sorologico"])?></td>
					</tr>
				<?}
				
				if($oAm["isolamento"]){?>
					<tr>
						<td class="rottd">Isolamento:</td>
						<td colspan="5" class="padd"><?=nl2br($oAm["isolamento"])?></td>
					</tr>
				<?}
				
				if($oAm["histopatologico"]){?>
					<tr>
						<td class="rottd">Histopatológico:</td>
						<td colspan="5" class="padd"><?=nl2br($oAm["histopatologico"])?></td>
					</tr>
				<?}

				
				if($oAm["parasitologico"]){?>
					<tr>
						<td class="rottd">Parasitológico:</td>
						<td colspan="5" class="padd"><?=nl2br($oAm["parasitologico"])?></td>
						</tr>
				<?}

				
				if($oAm["pcr"]){?>
					<tr>
						<td class="rottd">PCR:</td>
						<td colspan="5" class="padd"><?=nl2br($oAm["pcr"])?></td>
					</tr>
				<?}
        	}
			?>
			<!-- <tr>
				<td>
					<td class="titulogrupo"></tr>
				</tr>
			</tr> -->
			<br>
			<?
			if($oAm["status"]=="ASSINADO" && $provisorio != 'Y'){
				$sqlass = "select idpessoa 
						from carrimbo 
						where tipoobjeto = 'amostratra' 
						and idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]."
						and idobjeto = ".$oAm["idamostra"]." 
						and status='ASSINADO'";

				$resass = d::b()->query($sqlass) or die("Erro ao recuperar carrimbo: ".mysqli_error(d::b()));
				$qtdrowss= mysqli_num_rows($resass);

				while($rowass = mysqli_fetch_array($resass)){
					$nomresp="";
					$crmvresp="";
						if($rowass["idpessoa"]!=797 or $rowass["idpessoa"]!=782){
							$rowass["idpessoa"]=797;                        
						}
					$respidpessoa=$rowass["idpessoa"];
					//troca dados do responsavel via hardcode
					switch ($rowass["idpessoa"]) {
						case 782://edison
							$nomresp = "Edison Rossi";
							$crmvresp = "MG N&ordm; 1626";
						;
						break;
						case 797://marcio
							$nomresp = "Marcio Danilo Botrel Coutinho";
							$crmvresp = "MG N&ordm; 1454";
						;							
						default:
						null;
						break;
					}
				}
				if ($oAm["dataamostrah"] >= '2021-05-18 00:00:01' and  ( $_SESSION["SESSAO"]["IDEMPRESA"] == 1 || $_SESSION["SESSAO"]["IDEMPRESA"] == 2) ){
					/*if ($rowass["idpessoa"] == 97118){
						$nomresp = "Ana Paula Mori";
						$crmvresp = "MG N&ordm; 20758";
						$respidpessoa=97118;
					}else{*/
						$nomresp = "José Renato de O. Branco";
						$crmvresp = "MG N&ordm; 19770";
						$respidpessoa=5655;
				/*	}*/
						
					
			   }


				?>
				<tr>
					<td class="rottd">Técnico Resp.:</td>
					<td> <?=$nomresp?></td>
					<td class="rottd">CRMV:</td>
					<td><?=$crmvresp?></td>
					<td class="rottd">Assinatura.:</td>
					<td><img style="position: relative; top: 13px;" src="../inc/img/sig<?=strtolower(trim($respidpessoa))?>.gif"></td>
				</tr>
			<?}else{?>
				<tr>
				<? $string = $oAm['idpessoaresponsavelof']?'idpessoaresponsavelof':'responsavelof';?>
					<td class="<?=hide($string)?> rottd">Responsável<br>Oficial:</td>
					<td class="<?=hide($string)?> padd"><?=$oAm["responsavelof"]?></td>
				<? $string = $oAm['idpessoaresponsavelcrmvof']?'idpessoaresponsavelcrmvof':'responsavelofcrmv';?>
					<td class="<?=hide($string)?> rottd">CRMV:</td>
					<td class="<?=hide($string)?> padd"><?=$oAm["responsavelofcrmv"]?></td>
					<td class="<?=hide("responsaveloftel")?> rottd">Contato:</td>
					<td class="<?=hide("responsaveloftel")?> padd"><?=$oAm["responsaveloftel"]?></td>
				</tr>
				<tr>
					<td colspan="6"><div class="titulogrupotd" style="padding: 10px 0 6px 0px;"></div></td>
				</tr>
				<tr>	
					<!-- campos pontilhados para preenchimento manual -->
					<?
					//LTM - 05-10-2020 - Busca a assinatura e o caminho da assinatura do Veterinário salvo no carrimbo.
						//ALBT - 05-05-2021 - Só deve mostrar a assinatura caso: nao seja provisorio e os campos tipoobjetoext e idobjetoext nao sejam nulos - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=462426
						if($provisorio == 'Y'){
							$consultaprov = " AND tipoobjetoext = 'idfluxostatus' and idobjetoext = 882";	
						}else{
							$consultaprov="";
						}
					

						  $sqlCarrimbo = "SELECT c.assinatura, c.alteradoem, a.caminho, p.nome, c.idobjetoext, c.tipoobjetoext
									FROM carrimbo c JOIN pessoa p ON (c.idpessoa = p.idpessoa) LEFT JOIN arquivo a ON c.idpessoa = a.idobjeto AND a.tipoobjeto = 'pessoa' AND tipoarquivo = 'ASSINATURA'
									WHERE c.tipoobjeto = 'amostratra'
									".getidempresa('c.idempresa','carrimbo')."
									AND c.idobjeto = ".$oAm["idamostra"]." 
									AND c.status='ASSINADO' $consultaprov";

					$resCarrimbo = d::b()->query($sqlCarrimbo) or die("Erro ao recuperar carrimbo: ".mysqli_error(d::b()));
					$qtdCarrimbo = mysqli_num_rows($resCarrimbo);
					$rowCarrimbo = mysqli_fetch_array($resCarrimbo);
					if($qtdCarrimbo > 0){
							echo "<td colspan='6'>";
							echo "<div class='alinhaassinatura'>";
							if(!empty($rowCarrimbo['caminho'])){
								echo "<img src='".trim($rowCarrimbo["caminho"]) . "' height='48px'> \n";
							}
							echo "<p><label class='lbresp' style='font-weight:bold'>" . mb_convert_case($rowCarrimbo["nome"], MB_CASE_TITLE, 'UTF-8') . "</label></p>";
							//echo "<p><label class='lb6'>Respons&aacute;vel T&eacute;cnico: CRMV - ".($oAm["responsavelcolcrmv"])."</label> </p>";
							echo "<p> </p>";
							echo "<p><label class='lb6'>Chave Assinatura Digital: <a href='https://auditoria.inata.com.br/assinatura/?assinatura=".trim($rowCarrimbo["assinatura"])."' target='_blank' style='color:gray'>".trim($rowCarrimbo["assinatura"])."</a></label> | <label class='lb6'>Data Assinatura:" . dma($rowCarrimbo["alteradoem"]) . "</label> </p>";
						//	echo "<p><label class='lb6'><img width='6px;' src='../inc/img/secure15.png'> Autorização Certificado Digital SERPRO</label> \n";
						    echo "<p><label class='lb6'><img width='6px;' src='../inc/img/secure15.png'> A autenticidade desta assinatura pode ser conferida no site https://auditoria.inata.com.br/assinatura/ informando a chave de acesso, ou clicando no link acima.</label> \n";
							echo "</div>";
							echo "</td>";
						} else {
						?>
						<td class="rot">Assinatura.:</td>
						<td></td>
						<td class="rot">Data:</td>
						<td></td>
					<?}?>
				</tr>
			<?}?>
		</table>
		<!-- LDA-->
		<? 
		if($oAm["status"]=="ABERTO" or $oAm["status"]=="ENVIADO"){
			
			//LTM - 06-10-2020: Gera o arquivo assinado quando for TEA
			if($_GET["gravaarquivo"] == 'Y') 
			{
				//Alteração realizada para imprim
					if(!empty($_timbradorodape)){?>
					<br>
						<div id="_timbradorodape"><img src="<?=$_timbradorodape?>" height="80px" width="100%"></div>
					<?}?>
				</body>

				</html>
				<?
				gravaarquivo();
			}

			die;
		}
		
		if($provisorio != 'Y')
		{
			reset($oRes);
			while (list($k, $v) = each($oRes)){ 
	
				$idsubtipoam=$v["idsubtipoamostra"];
				?>
				<div class="quebrapagina"></div>
				<header class="row margem0.0">
					<div class="logosup col 20"><img src="<?=$figurarelatorio?>"></div>
					<div class="titulodoc">Laudo Diagnóstico Autógena </div>
					<div class="col 20"></div>
				</header>
				<div class="row">
					<!-- <div class="col 20 rot">N&ordm; Reg./SISLAUDO:</div>
					<div class="col 30"><?=$v["idregistro"]?>/<?=$v["exercicio"]?></div>
					-->
					<div class="col 20 rot">LDA:</div>
					<div class="col 30"><?=$v["idresultado"]?></div>
					<div class="col 20 rot"></div>
					<div class="col 30"></div>		
				</div>
				<div class="row">
					<div class="col 20 rot">Data Início Exame:</div>
					<div class="col 30"><?=dma($v["dataamostra"])?></div>
					<div class="col 20 rot">Data Final Exame:</div>
					<div class="col 30"><?=dma($v["ascriadoem"])?></div>
				</div>
				<?
				if(!empty($v["ascriadoem"]) and !empty($oAm["dataamostra"])){
					validaDataExame($v["ascriadoem"],$oAm["dataamostra"]);
				}
				?>
				<div class="row">
					<div class="col grupo 100 quebralinha">
						<div class="titulogrupo">Dados do Cliente</div>
					</div>
				</div>
				<div class="row">
					<div class="col 15 rot">Cliente:</div>
					<div class="col 85"><?=$oAm["razaosocial"]?></div>
				</div>
				<div class="row">
					<div class="col 15 rot">Propriedade/Granja:</div>
					<div class="col 85 quebralinha"><?=$oAm["nome"]?></div>
				</div>
				<div class="row">
					<div class="col 15 rot">Endereço:</div>
					<div class="col 85 quebralinha">
						<?
						if(empty($oAm["enderecosacado"])){
							?>
							<div class="alert alert-warning">
							<span class="notProducao"><i class="fa fa-exclamation-triangle"></i>&nbsp;Favor preencher o endereço da propriedade no cadastro do cliente!</span>
							</div>		    
							<?
						}else{
							echo($oAm["enderecosacado"]);
						}
						?>
					</div>
				</div>
				<div class="row ">
					<div class="col 15 rot">CNPJ:</div>
					<div class="col 35"><?=formatarCPF_CNPJ($oAm["cpfcnpj"])?></div>
					<div class="col 15 rot">I.E.:</div>
					<div class="col 35"><?=$oAm["inscrest"]?></div>
				</div>
				<div class="row">
				<? $string = $oAm['idpessoaresponsavelof']?'idpessoaresponsavelof':'responsavelof';?>
					<div class="<?=hide($string)?> col 15 rot">Responsável<br>Oficial:</div>
					<div class="<?=hide($string)?> col 35 quebralinha"><?=$oAm["responsavelof"]?></div>
				<? $string = $oAm['idpessoaresponsavelcrmvof']?'idpessoaresponsavelcrmvof':'responsavelofcrmv';?>					
					<div class="<?=hide($string)?> col 10 rot">CRMV:</div>
					<div class="<?=hide($string)?> col 15"><?=$oAm["responsavelofcrmv"]?></div>
					<div class="<?=hide("responsaveloftel")?> col 10 rot">Contato:</div>
					<div class="<?=hide("responsaveloftel")?> col 15"><?=$oAm["responsaveloftel"]?></div>
				</div>
				<br>
				<div class="row">
					<div class="col grupo 100 quebralinha">
						<div class="titulogrupo">Dados da Amostra</div>
					</div>
				</div>
				<div class="row">
					<div class="<?=hidelda("idespeciefinalidade",$idsubtipoam)?> col 15 rot">Espécie/Finalidade:</div>
					<div class="<?=hidelda("idespeciefinalidade",$idsubtipoam)?> col 85 quebralinha"><?=$v["especietipofinalidade"]?></div>
				</div>
				<div class="row">
					<div class="col 15 rot">Material Colhido:</div>
					<div class="col 35"><?=$v["subtipoamostra"]?></div>
					<div class="<?=hidelda("nroamostra",$idsubtipoam)?> col 15 rot">Quantidade:</div>
					<div class="<?=hidelda("nroamostra",$idsubtipoam)?> col 35 quebralinha"><?=$v["nroamostra"]?></div>
				</div>
				<div class="row">
					<div class="<?=hidelda("descricao",$idsubtipoam)?> col 15 rot">Descrição:</div>
					<div class="<?=hidelda("descricao",$idsubtipoam)?> col 85 quebralinha"><?=$v["descricao"]?></div>
				</div>
				<div class="row">
					<div class="<?=hidelda("idnucleo",$idsubtipoam)?> col 15 rot">Núcleo:</div>
					<div class="<?=hidelda("idnucleo",$idsubtipoam)?> col 85"><?=$v["nucleo"]?></div>
				</div>
				<div class="row">
					<div class="<?=hidelda("idade",$idsubtipoam)?> col 15 rot">Idade:</div>
					<div class="<?=hidelda("idade",$idsubtipoam)?> col 85"><?=$v["idade"]." ".$v["tipoidade"]?></div>
				</div>
				<div class="row">
					<div class="<?=hidelda("linha",$idsubtipoam)?> col 15 rot">Linha:</div>
					<div class="<?=hidelda("linha",$idsubtipoam)?> col 35 quebralinha"><?=$v["linha"]?></div>
					<div class="<?=hidelda("regoficial",$idsubtipoam)?> col 15 rot">Nº Registro Oficial:</div>
					<div class="<?=hidelda("regoficial",$idsubtipoam)?> col 35 quebralinha"><?=$v["regoficial"]?></div>
				</div>
					<div class="row">
					<div class="<?=hidelda("sexo",$idsubtipoam)?> col 15 rot">Sexo:</div>
					<div class="<?=hidelda("sexo",$idsubtipoam)?> col 20"><?=$v["sexo"]?></div>
					<div class="<?=hidelda("clienteterceiro",$idsubtipoam)?> col 15 rot">Cliente 3&ordm;:</div>
					<div class="<?=hidelda("clienteterceiro",$idsubtipoam)?> col 50"><?=$v["clienteterceiro"]?></div>
				</div>
				<div class="row">
					<div class="<?=hidelda("formaarmazen",$idsubtipoam)?> col 15 rot">Forma de<br>Armazenamento:</div>
					<div class="<?= hidelda("formaarmazen",$idsubtipoam)?> col 20"><?=$v["formaarmazen"]?></div>
					<div class="<?=hidelda("meiotransp",$idsubtipoam)?> col 15 rot">Meio de Transp.:</div>
					<div class="<?=hidelda("meiotransp",$idsubtipoam)?> col 50"><?=$v["meiotransp"]?></div>
				</div>
				<div class="row">
					<div class="<?=hidelda("condconservacao",$idsubtipoam)?> col 15 rot quebralinha">Cond.de Conservação:</div>
					<div class="<?=hidelda("condconservacao",$idsubtipoam)?> col 85 quebralinha"><?=nl2br($v["condconservacao"])?></div>
				</div>
				<div class="row">
					<div class="<?=hidelda("nucleoorigem",$idsubtipoam)?> col 15 rot">Núcleo Origem:</div>
					<div class="<?=hidelda("nucleoorigem",$idsubtipoam)?> col 20 quebralinha"><?=$v["nucleoorigem"]?></div>
					<div class="<?=hidelda("tipo",$idsubtipoam)?> col 15 rot">Tipo:</div>
					<div class="<?=hidelda("tipo",$idsubtipoam)?> col 50"><?=$v["tipo"]?></div>
				</div>
				<div class="row">
					<div class="<?=hidelda("nucleoorigem",$idsubtipoam)?> col 15 rot">Núcleo Origem:</div>
					<div class="<?=hidelda("nucleoorigem",$idsubtipoam)?> col 20 quebralinha"><?=$v["nucleoorigem"]?></div>
					<div class="<?=hidelda("tipo",$idsubtipoam)?> col 15 rot">Tipo:</div>
					<div class="<?=hidelda("tipo",$idsubtipoam)?> col 50"><?=$v["tipo"]?></div>
				</div>
				<div class="row">
					<div class="<?=hidelda("especificacao",$idsubtipoam)?> col 15 rot">Especificações:</div>
					<div class="<?=hidelda("especificacao",$idsubtipoam)?> col 20 quebralinha"><?=$v["especificacao"]?></div>
					<div class="<?=hidelda("partida",$idsubtipoam)?> col 15 rot">Partida:</div>
					<div class="<?=hidelda("partida",$idsubtipoam)?> col 15 quebralinha"><?=$v["partida"]?></div>
					<div class="<?=hidelda("fornecedor",$idsubtipoam)?> col 15 rot">Fornecedor:</div>
					<div class="<?=hidelda("fornecedor",$idsubtipoam)?> col 20 quebralinha"><?=$v["fornecedor"]?></div>
				</div>
				<div class="row">
					<div class="<?=hidelda("datafabricacao",$idsubtipoam)?> col 15 rot">Data Fabricação:</div>
					<div class="<?=hidelda("datafabricacao",$idsubtipoam)?> col 20 quebralinha"><?=$v["datafabricacao"]?></div>
					<div class="<?=hidelda("identificacaochip",$idsubtipoam)?> col 15 rot">Chip/Identif.:</div>
					<div class="<?=hidelda("identificacaochip",$idsubtipoam)?> col 15 quebralinha"><?=$v["identificacaochip"]?></div>
					<div class="<?=hidelda("diluicoes",$idsubtipoam)?> col 15 rot">Diluições:</div>
					<div class="<?=hidelda("diluicoes",$idsubtipoam)?> col 20 quebralinha"><?=$v["diluicoes"]?></div>
				</div>
				<div class="row">
					<div class="<?=hidelda("nroplacas",$idsubtipoam)?> col 15 rot">Nº Placas:</div>
					<div class="<?=hidelda("nroplacas",$idsubtipoam)?> col 20 quebralinha"><?=$v["nroplacas"]?></div>
					<div class="<?=hidelda("nrodoses",$idsubtipoam)?> col 15 rot">Nº Doses:</div>
					<div class="<?=hidelda("nrodoses",$idsubtipoam)?> col 15 quebralinha"><?=$v["nrodoses"]?></div>
					<div class="<?=hidelda("semana",$idsubtipoam)?> col 15 rot">Semana:</div>
					<div class="<?=hidelda("semana",$idsubtipoam)?> col 20 quebralinha"><?=$v["semana"]?></div>
				</div>	
				<div class="row">
					<div class="<?=hidelda("notafiscal",$idsubtipoam)?> col 15 rot">Nota Fiscal:</div>
					<div class="<?=hidelda("notafiscal",$idsubtipoam)?> col 20 quebralinha"><?=$v["notafiscal"]?></div>
					<div class="<?=hidelda("vencimento",$idsubtipoam)?> col 15 rot">Vencimento:</div>
					<div class="<?=hidelda("vencimento",$idsubtipoam)?> col 15 quebralinha"><?=$v["vencimento"]?></div>
					<div class="<?=hidelda("fabricante",$idsubtipoam)?> col 15 rot">Fabricante:</div>
					<div class="<?=hidelda("fabricante",$idsubtipoam)?> col 20 quebralinha"><?=$v["fabricante"]?></div>
				</div>
				<div class="row ">
					<div class="<?=hidelda("sexadores",$idsubtipoam)?> col 15 rot">Sexadores:</div>
					<div class="<?=hidelda("sexadores",$idsubtipoam)?> col 20 quebralinha"><?=$v["sexadores"]?></div>
					<div class="<?=hidelda("localexp",$idsubtipoam)?> col 15 rot">Local Específico:</div>
					<div class="<?=hidelda("localexp",$idsubtipoam)?> col 50"><?=$v["localexp"]?></div>
				</div>
				<div class="row">
					<div class="<?=hidelda("lacre",$idsubtipoam)?> col 15 rot">Lacre:</div>
					<div class="<?=hidelda("lacre",$idsubtipoam)?> col 20 quebralinha"><?=$v["lacre"]?></div>
					<div class="<?=hidelda("tc",$idsubtipoam)?> col 15 rot">Termo de Coleta:</div>
					<div class="<?=hidelda("tc",$idsubtipoam)?> col 50"><?=$v["tc"]?></div>
				</div>
				<div class="row">
					<div class="<?=hidelda("localcoleta",$idsubtipoam)?> col 15 rot">Local coleta:</div>
					<div class="<?=hidelda("localcoleta",$idsubtipoam)?> col 85 quebralinha"><?=$v["localcoleta"]?></div>
				</div>
				<div class="row">
					<div class="<?=hidelda("responsavel",$idsubtipoam)?> col 15 rot">Responsável Coleta:</div>
					<div class="<?=hidelda("responsavel",$idsubtipoam)?> col 85 quebralinha"><?=$v["responsavel"]?></div>
				</div>	
				<div class="rows">
						<div class="<?=hidelda("observacao",$idsubtipoam)?> col 15 rot">Observação:</div>
						<div class="<?=hidelda("observacao",$idsubtipoam)?> col 85 quebralinha"><?=nl2br($v["observacao"])?></div>
				</div>
				<br>
				<div class="row">
					<div class="col grupo 100 quebralinha">
						<div class="titulogrupo">Dados Epidemiológicos</div>
					</div>
				</div>
				<div class="row">
					<div class="<?=hide("sinaisclinicosinicio")?> col 15 rot">Início sinais clínicos:</div>
					<div class="<?=hide("sinaisclinicosinicio")?> col 85 quebralinha"><?=$oAm["sinaisclinicosinicio"]?></div>
				</div>
				<div class="row">
					<div class="<?=hide("sinaisclinicos")?> col 15 rot">Sinais clínicos:</div>
					<div class="<?=hide("sinaisclinicos")?> col 85 quebralinha"><?=nl2br($oAm["sinaisclinicos"])?></div>
				</div>
				<div class="row">
					<div class="<?=hide("achadosnecropsia")?> col 15 rot">Achados necrópsia:</div>
					<div class="<?=hide("achadosnecropsia")?> col 85 quebralinha"><?=nl2br($oAm["achadosnecropsia"])?></div>
				</div>
				<div class="row">
					<div class="<?=hide("suspclinicas")?> col 15 rot">Suspeitas clínicas:</div>
					<div class="<?=hide("suspclinicas")?> col 85 quebralinha"><?=nl2br($oAm["suspclinicas"])?></div>
				</div>
				<div class="row">
					<div class="<?=hide("histproblema")?> col 15 rot">Histórico problema:</div>
					<div class="<?=hide("histproblema")?> col 85 quebralinha"><?=nl2br($oAm["histproblema"])?></div>
				</div>
				<div class="row">
					<div class="<?=hide("morbidade")?>col 15 rot">Morbidade<span class="fonte8"> (N&ordm; animais)</span>:</div>
					<div class="<?=hide("morbidade")?>col 20"><?=$oAm["morbidade"]?></div>
					<div class="<?=hide("letalidade")?>col 15 rot">Letalidade<span class="fonte8"> (N&ordm; animais)</span>:</div>
					<div class="<?=hide("letalidade")?>col 20"><?=$oAm["letalidade"]?></div>
					<div class="<?=hide("mortalidade")?>col 15 rot">Mortalidade<span class="fonte8"> (N&ordm; animais)</span>:</div>
					<div class="<?=hide("mortalidade")?>col 15"><?=$oAm["mortalidade"]?></div>
				</div>
				<div class="row">
					<div class="<?=hide("usomedicamentos")?> col 15 rot">Uso medicamentos:</div>
					<div class="<?=hide("usomedicamentos")?> col 35 quebralinha"><?=$oAm["usomedicamentos"]?></div>
					<div class="<?=hide("usovacinas")?> col 15 rot">Uso de vacinas:</div>
					<div class="<?=hide("usovacinas")?> col 35 quebralinha"><?=$oAm["usovacinas"]?></div>
				</div>
				<div class="row">
					<div class="<?=hide("localcoleta")?> col 15 rot">Local coleta:</div>
					<div class="<?=hide("localcoleta")?> col 85 quebralinha"><?=$oAm["localcoleta"]?></div>
				</div>
				<div class="row">
					<div class="<?=hide("responsavel")?> col 15 rot">Responsável<br>Coleta:</div>
					<div class="<?=hide("responsavel")?> col 85 quebralinha"><?=$oAm["responsavel"]?></div>
				</div>
				<div class="rows">
					<div class="<?=hide("observacao")?> col 15 rot">Observação:</div>
					<div class="<?=hide("observacao")?> col 85 quebralinha"><?=nl2br($oAm["observacao"])?></div>
				</div>
				<div class="row ">
					<div class="col grupo 100 quebralinha">
						<div class="titulogrupo">Exame Solicitado</div>			
					</div>
				</div>
				<div class="row">
					<div class="col grupo 100 quebralinha">
					<?=$v["descr"]?>
					</div>
				</div>
				<br>	
				<div class="row">
					<div class="col grupo 100 quebralinha">
						<div class="titulogrupo">Resultado do Exame Solicitado</div>
					</div>
				</div>
				<?
				$rc= unserialize(base64_decode($v["jresultado"]));  		 
				$v["modo"]				=$rc["prodserv"]["res"]["modo"];
				$v["modelo"]			=$rc["prodserv"]["res"]["modelo"];
				$v["jsonresultado"]   	=$rc["resultado"]["res"]["jsonresultado"];
				$v["jsonconfig"]      	=$rc["resultado"]["res"]["jsonconfig"];
				$v["descritivo"]      	=$rc["resultado"]["res"]["descritivo"];
				
				
				if(($v["modelo"]=="DESCRITIVO" and $v["modo"]=="AGRUP") OR ($v["modelo"]=="DROP" and $v["modo"]=="AGRUP")){?>
					<div class="row">
						<div class="col grupo 100 quebralinha resultadodescritivo">		
							<?
							echo $v["descritivo"];
							//echo strip_tags($v["descritivo"], '<strong>');
							?>
					
						</div>
					</div>
				<?}elseif(($v["modelo"]=="DESCRITIVO" or $v["modelo"]=="DROP") and $v["modo"]=="IND"){
					$sqlind="select r.*, cast( r.identificacao AS UNSIGNED ) as videntificacao 
								from resultadoindividual r
								where r.idresultado = ".$v['idresultado']." order by videntificacao";
				
					$resind=d::b()->query($sqlind) or die("Erro ao buscar as pesagens para listagem sql".$sqlind);
					$y=0;

					while($rowind=mysqli_fetch_assoc($resind)){ ?>
						<div class="row">
							<div class="col grupo 100 quebralinha">
								<?
								echo("Amostra ".$rowind['identificacao']." apresentou resultado ".$rowind['resultado'].". ");
								?>                   
							</div>
						</div>
					<? } ?>                  
				<?}elseif($v["modelo"]=="UPLOAD"){		
					$strsql = "SELECT * FROM resultadoelisa where idresultado = ". $v["idresultado"] . " and  idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]." and status = 'A' order by idresultadoelisa";
					$result = d::b()->query($strsql) or die("A Consulta  dos resultados elisa falhou : " . mysql_error() . "<p>SQL: $strsql");

					$i=0;
					while ($row = mysqli_fetch_assoc($result)){
						?>
						<div class="row">
							<div class="col grupo 15 quebralinha">
								<?if($i==0){?><div class="titulogrupo">&nbsp;</div><?}?>
								<?=$row["nome"]?>
							</div>
							<div class="col grupo 15 quebralinha">
								<?if($i==0){?><div class="titulogrupo">Wells</div><?}?>
								<?=$row["well"]?>
							</div>
							<div class="col grupo 15 quebralinha">
								<?if($i==0){?><div class="titulogrupo">O.D.</div><?}?>
								<?=$row['OD']?>
							</div>
							<div class="col grupo 10 quebralinha">
								<?if($i==0){?><div class="titulogrupo">S/P</div><?}?>
								<?=$row['SP']?>
							</div>
							<div class="col grupo 20 quebralinha">
								<?if($i==0){?><div class="titulogrupo nowrap">S/N</div><?}?>
								<?=$row['SN']?>
							</div>
							<div class="col grupo 20 quebralinha">
								<?if($i==0){?><div class="titulogrupo nowrap">Titer</div><?}?>
								<?=$row['titer']?>
							</div>
							<div class="col grupo 20 quebralinha">
								<?if($i==0){?><div class="titulogrupo nowrap">Group</div><?}?>
								<?=$row['grupo']?>
							</div>
							<div class="col grupo 20 quebralinha">
								<?if($i==0){?><div class="titulogrupo nowrap">Result</div><?}?>
								<?=$row['result']?>
							</div>
						</div>
						<?
						$i++; 
					}

				}elseif($v["modelo"]=="DINÂMICO"){		
					//echo $row["jsonresultado"];
					$phpArray = json_decode($v["jsonresultado"], true);
					$phpArray[0]->name;
					$z = 0;
					$vindice = '';
					$x = 0;
					foreach ($phpArray as $key1 => $value1) {		
						//if($json_data[$key1]["Age"] < 20){
						//echo $key1;
						if ($key1 == 'INDIVIDUAL'){
							//	print_r($value1);
							foreach ($value1 as $k => $v1){
								$group = explode('_',$v1['name']);
								$h = $group[2];
								$group = $group[0];
								
								$dinamicoindividual['header'][$h] = $v1['titulo'];
								//echo $v1['type'].'<Br>';
							
							
								switch($v1['type']){
									case 'date':
										$dinamicoindividual[$group][$h] = dma($v1['value']);
										break;
									case 'checkbox':
										if 	($v1['value'] == 1){
											$dinamicoindividual[$group][$h] = 'Sim';
										}else{
											$dinamicoindividual[$group][$h] = 'Não';
										}
								
										break;
									default:								
										$dinamicoindividual[$group][$h] = $v1['value'];
										break;
								}			
							}
							$z++;
						}else{
							foreach ($value1 as $k => $v1){
								switch($v1['type']){
									case 'date':
											$dinamicoagrupado[$x]['value'] = dma($v1['value']);
										break;
									case 'checkbox':
										if 	($v1['value'] == 1){
											$dinamicoagrupado[$x]['value'] = 'Sim';
										}else{
											$dinamicoagrupado[$x]['value'] = 'Não';
										}								
										break;
									default:
										$dinamicoagrupado[$x]['value'] = $v1['value'];
										break;
								}
							
								$dinamicoagrupado[$x]['header'] = $v1['titulo'];		
								$x++;
							}			
						}
			
					}
					//print_r($dinamicoagrupado);

					?>
					
					<table style="width:100%; background-color:#f7f7f7; height:14px; vertical-align:middle" class="trelisa ">
						<thead>
							<tr>
								<? 
								$z = 0;
								$cab = [];	

								foreach ($dinamicoindividual['header'] as $key1 => $value1) { ?>
									<td style="  flex-grow: 1; font-weight:bold;"><?=$value1;?></td>
									<? 
									$cab[$z] = $key1;
									$z++;
								}
								unset($dinamicoindividual['header']);
								?>
							</tr>
						</thead>
						<tbody>
							<?
							$z = 0;
							foreach ($dinamicoindividual as $key1 => $value1) { ?>
								<tr>	
									<? $r = 0;
										while ($r < count($cab)){
											?>
											<td style="  flex-grow: 1;"><?=$value1[$cab[$r]];?></td>
											<?								
											$r++;
										}
									?>					
								</tr>
								<? $z++;
							} ?>
						</tbody>
					</table>
					<?
					$z = 0;
					if (count($dinamicoagrupado) > 0){
						$tabela .= '<br><table style="width:100%;">';
					}
					foreach ($dinamicoagrupado as $key1 => $value1) { 
						$tabela .= '<tr><td style="width:74px;white-space:nowrap;"><b>'.$value1['header'].':</b></td><td>'.$value1['value'].'</td></tr>';

					}
					if (count($dinamicoagrupado) > 0){
						$tabela .= '</table>';
					} 
					
					echo $tabela;
																
						unset($dinamicoindividual);
						unset($dinamicoagrupado);
				}//elseif($v["modelo"]=="UPLOAD"){
				?>
				<?
				if(sizeof($oAAm[$v["idresultado"]])>0){
					?>
					<br>
					<div class="row ">
						<div class="col grupo 100 quebralinha">
							<div class="titulogrupo">Identificação do Agente</div>			
						</div>
					</div>
					<div class="row">
						<div class="col grupo 100 quebralinha">
							<?
							reset($oAAm);
							$virg="";
							while(list($idlote, $lote) = each($oAAm[$v["idresultado"]])){
								?>					
								<?echo($virg.$lote['descr']." - ".$lote['partida']."/".$lote['exercicio']);?>

								<?
								$virg=", ";
							}
							?>
						</div>
					</div>
				<?
				}
				?>
							<HR>
				<?

				if($v["statusresult"]=="ASSINADO" and !empty($v["idassinadopor"])){
					$sqlass = "SELECT idpessoa
						FROM pessoa
						WHERE idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]."					
						and idpessoa = " . $v["idassinadopor"];

					$resass = d::b()->query($sqlass) or die("Erro ao recuperar assinaturas: ".mysqli_error(d::b()));
					$qtdrowss= mysqli_num_rows($resass);

					while($rowass = mysqli_fetch_array($resass)){
						$nomresp="";
						$crmvresp="";
						$respidpessoa=$rowass["idpessoa"];
						//troca dados do responsavel via hardcode
						switch ($rowass["idpessoa"]) {
							case 782://edison
								$nomresp = "Edison Rossi";
								$crmvresp = "MG N&ordm; 1626";
							;
							break;
							case 797://marcio
								$nomresp = "Marcio Danilo Botrel Coutinho";
								$crmvresp = "MG N&ordm; 1454";
							;							
							default:
							null;
							break;
						}
					}
					
					if ($oAm["dataamostrah"] >= '2021-05-18 00:00:01' and  ( $_SESSION["SESSAO"]["IDEMPRESA"] == 1 || $_SESSION["SESSAO"]["IDEMPRESA"] == 2)){
						/*if ($rowass["idpessoa"] == 97118){
						$nomresp = "Ana Paula Mori";
						$crmvresp = "MG N&ordm; 20758";
						$respidpessoa=97118;
					}else{*/
						$nomresp = "José Renato de O. Branco";
						$crmvresp = "MG N&ordm; 19770";
						$respidpessoa=5655;
				/*	}*/
					}
					?>
					<div class="row">
						<div class="col 15 rot">Técnico Resp.:</div>
						<div class="col 25"><?=$nomresp?></div>
						<div class="col 10 rot">CRMV:</div>
						<div class="col "><?=$crmvresp?></div>
						<div class="col 15 rot">Assinatura.:</div>
						<div class="col "><img style="position: relative; top: 13px;" src="../inc/img/sig<?=strtolower(trim($respidpessoa))?>.gif"></div>
					</div>
				<?}else{?>
					<div class="row">
						<div class="col 15 rot">Veterinário Resp.:</div>
						<div class="col 25 sublinhado"></div>
						<div class="col 10 rot">CRMV:</div>
						<div class="col sublinhado"></div>
						<div class="col 15 rot">Assinatura.:</div>
						<div class="col sublinhado"></div>
					</div>
				<?}?>	
			<?
			}
			?>
			<?
			if(!empty($_timbradorodape)){?>
				<br>
				<div id="_timbradorodape"><img src="<?=$_timbradorodape?>" height="80px" width="100%"></div>
			<?}?>
		<?}?>	
	</body>
</html>

<? 
function gravaarquivo(){
	$html = ob_get_contents();
	//limpar o codigo html
	$html = preg_replace('/>\s+</', "><", $html);

	ob_end_clean();

	// Incluímos a biblioteca DOMPDF
	require_once("../inc/dompdf/dompdf_config.inc.php");

	// Instanciamos a classe
	$dompdf = new DOMPDF();

	$html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
	$dompdf->load_html($html);

	// Definimos o tamanho do papel e
	// sua orientação (retrato ou paisagem)
	$dompdf->set_paper('A4','portrait');

	// O arquivo é convertido
	$dompdf->render();

	// Salvo no diretório temporário do sistema
	$output = $dompdf->output();

	file_put_contents("../upload/amostratraprovisorio/amostra".$_GET["idamostra"].".pdf", $output);

	echo("../upload/amostratraprovisorio/amostra".$_GET["idamostra"].".pdf");
}

?>
