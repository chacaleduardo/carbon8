<?
require_once("../inc/php/validaacesso.php");

if(empty($_GET["idamostra"])){
	die("Amostra não enviada");
}

// $oTra=getObjeto("tra", $_GET["idamostra"],"idamostra");

$oAm=getAmostra($_GET["idamostra"]);
//print_r($oAm);

if ($oAm['statustra'] == 'ABERTO'){
	?>
	<link rel="stylesheet" href="../inc/css/bootstrap/css/bootstrap.min.css" />
	<br>
	<div class="col-md-6">
		<div class="alert alert-warning aviso" role="alert" style="font-size:12px !important;">

		<strong><i class="glyphicon glyphicon-info-sign"></i> Este teste está  <b>em análise</b>.
		<br/>
		<br/>Em caso de dúvida, entre em contato conosco:
		<br/>Email: resultados@laudolab.com.br
		<br/>Telefone: (34) 3222 5700 / Whatsapp: (34) 9 9130-1330
		</div>
	</div>
	<?

}


$idsubtipoamostra=$oAm["idsubtipoamostra"];

$oRes=getResultados($_GET["idamostra"]);

$oAAm=getAgenteAmostra($_GET["idamostra"]);

//Verificação de quais inputs serão mostrados no TRA
$arrConfInputs = getAmostraConfInputs($_GET["unidadepadrao"]);

//Verificação de quais inputs serão mostrados no LDA
$arrConfInputsLDA = getAmostraConfInputs(6);

//Mostrar ou esconder divs conforme configuração
function hide($inCol){
	global $arrConfInputs,$idsubtipoamostra;
	
	if($arrConfInputs["arrcoluna"]["TRA"][$inCol][$idsubtipoamostra]){
		return "";
	}else{
		return "hidden";
	}
}
//Mostrar ou esconder divs conforme configuração
function hidelda($inCol,$idsubtipoamostralda){
	global $arrConfInputsLDA;
	
	if($arrConfInputsLDA["arrcoluna"]["TRA"][$inCol][$idsubtipoamostralda]){
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

?>
<html>
<head>
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

.ordContainer{
	display: flex;
	flex-direction: column;
}
.ord1{order: 1;}
.ord2{order: 2;}
.ord3{order: 3;}
.ord4{order: 4;}
.ord5{order: 5;}
.ord6{order: 6;}
.ord7{order: 7;}
.ord8{order: 8;}
.ord9{order: 9;}
.ord10{order: 10;}
.ord11{order: 11;}
.ord12{order: 12;}
.ord13{order: 13;}
.ord14{order: 14;}
.ord15{order: 15;}
.ord16{order: 16;}
.ord17{order: 17;}
.ord18{order: 18;}
.ord19{order: 19;}
.ord20{order: 20;}
.ord21{order: 21;}
.ord22{order: 22;}
.ord23{order: 23;}
.ord24{order: 24;}
.ord25{order: 25;}
.ord26{order: 26;}
.ord27{order: 27;}
.ord28{order: 28;}
.ord29{order: 29;}
.ord30{order: 30;}
.ord31{order: 31;}
.ord32{order: 32;}
.ord33{order: 33;}
.ord34{order: 34;}
.ord35{order: 35;}
.ord36{order: 36;}
.ord37{order: 37;}
.ord38{order: 38;}
.ord39{order: 39;}
.ord40{order: 40;}
.ord41{order: 41;}
.ord42{order: 42;}
.ord43{order: 43;}
.ord44{order: 44;}
.ord45{order: 45;}
.ord46{order: 46;}
.ord47{order: 47;}
.ord48{order: 48;}
.ord49{order: 49;}
.ord50{order: 50;}
.ord51{order: 51;}
.ord52{order: 52;}
.ord53{order: 53;}
.ord54{order: 54;}
.ord55{order: 55;}
.ord56{order: 56;}
.ord57{order: 57;}
.ord58{order: 58;}
.ord59{order: 59;}
.ord60{order: 60;}
.ord61{order: 61;}
.ord62{order: 62;}
.ord63{order: 63;}
.ord64{order: 64;}
.ord65{order: 65;}
.ord66{order: 66;}
.ord67{order: 67;}
.ord68{order: 68;}
.ord69{order: 69;}
.ord70{order: 70;}
.ord71{order: 71;}
.ord72{order: 72;}
.ord73{order: 73;}
.ord74{order: 74;}
.ord75{order: 75;}
.ord76{order: 76;}
.ord77{order: 77;}
.ord78{order: 78;}
.ord79{order: 79;}
.ord80{order: 80;}
.ord81{order: 81;}
.ord82{order: 82;}
.ord83{order: 83;}
.ord84{order: 84;}
.ord85{order: 85;}
.ord86{order: 86;}
.ord87{order: 87;}
.ord88{order: 88;}
.ord89{order: 89;}
.ord90{order: 90;}
.ord91{order: 91;}
.ord92{order: 92;}
.ord93{order: 93;}
.ord94{order: 94;}
.ord95{order: 95;}
.ord96{order: 96;}
.ord97{order: 97;}
.ord98{order: 98;}
.ord99{order: 99;}
.ord100{order: 100;}


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
.titulodoc{
	height: inherit;
	line-height: inherit;
	display: table-cell;
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
</style>
<?
$titulo="";
if($oAm["statustra"]=="ABERTO" or $oAm["statustra"]=="ENVIADO"){
	$titulo="Termo de Envio de Amostra";
	$sub="TEA";
}elseif($oAm["statustra"]=="DEVOLVIDO" or $oAm["statustra"]=="ASSINADO"){
	$titulo="Termo de Recepção de Amostra";
	$sub="TRA";
}
?>
	<title><?=$titulo?></title>
</head>
<body>
	<pagina class="ordContainer">
		<header class="row margem0.0">
			<div class="logosup col 20"><img src="../inc/img/Logo PB Inata.jpg"></div>
			<div class="titulodoc">
<?=$titulo?>
			</div>
			<div class="col 20"></div>
		</header>
		<div class="row">
			<div class="col 15 rot">N&ordm; <?=$sub?>:</div>
			<div class="col 35"><?=$oAm["idregistro"]?>/<?=$oAm["exercicio"]?></div>
			<div class="col 15 rot">Data Registro:</div>
			<div class="col 35"><?=dmahms($oAm["dataamostrah"], true)?></div>
		</div>
		<div class="row">
			<div class="col grupo 100 quebralinha">
				<div class="titulogrupo">Dados do Cliente</div>
			</div>
		</div>
		<div class="row">
			<div class="col 15 rot">Cliente:</div>
			<div class="col 85 quebralinha"><?=$oAm["razaosocial"]?></div>
		</div>
		<div class="row">
			<div class="col 15 rot">Propriedade/Granja:</div>
			<div class="col 85 quebralinha"><?=$oAm["nome"]?></div>
		</div>
		<div class="row">
			<div class="col 15 rot">Endereço:</div>
			<div class="col 85 quebralinha" title="<?=$oAm["idpessoa"]?>">
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
		<div class="row">
			<div class="col 15 rot">Cnpj:</div>
			<div class="col 35"><?=formatarCPF_CNPJ($oAm["cpfcnpj"])?></div>
			<div class="col 15 rot">Inscr. Estadual:</div>
			<div class="col 35"><?=$oAm["inscrest"]?></div>
		</div>
		<div class="row">
			<div class="<?=hide("responsavelof")?> col 15 rot">Respons. oficial:</div>
			<div class="<?=hide("responsavelof")?> col 35 quebralinha"><?=$oAm["responsavelof"]?></div>
			<div class="<?=hide("responsavelofcrmv")?> col 10 rot">CRMV:</div>
			<div class="<?=hide("responsavelofcrmv")?> col 15"><?=$oAm["responsavelofcrmv"]?></div>
			<div class="<?=hide("responsaveloftel")?> col 10 rot">Tel:</div>
			<div class="<?=hide("responsaveloftel")?> col 15"><?=$oAm["responsaveloftel"]?></div>
		</div>
		<div class="row">
			<div class="<?=hide("numeroanimais")?> col 15 rot">Nº de animais:</div>
			<div class="<?=hide("numeroanimais")?> col 85 quebralinha"><?=$oAm["numeroanimais"]?></div>
		</div>		
		<div class="row">
			<div class="col grupo 100 quebralinha">
				<div class="titulogrupo">Dados da Amostra</div>
			</div>
		</div>
		<div class="row">
			<div class="<?=hide("idespeciefinalidade")?> col 15 rot">Espécie/Finalidade:</div>
			<div class="<?=hide("idespeciefinalidade")?> col 85 quebralinha"><?=$oAm["especietipofinalidade"]?></div>
		</div>
		<div class="row">
			<div class="col 15 rot">Material colhido:</div>
			<div class="col 20 quebralinha"><?=$oAm["subtipoamostra"]?></div>
			<div class="<?=hide("nroamostra")?> col 15 rot">Quantidade:</div>
			<div class="<?=hide("nroamostra")?> col 20 quebralinha"><?=$oAm["nroamostra"]?></div>
			<div class="<?=hide("datacoleta")?> col 15 rot">Data Coleta:</div>
			<div class="<?=hide("datacoleta")?> col 15"><?=dma($oAm["datacoleta"])?></div>
		</div>
		<div class="row">
			<div class="<?=hide("descricao")?> col 15 rot">Descrição:</div>
			<div class="<?=hide("descricao")?> col 85 quebralinha"><?=$oAm["descricao"]?></div>
		</div>
		<div class="row">
			<div class="<?=hide("idnucleo")?> col 15 rot">Núcleo:</div>
			<div class="<?=hide("idnucleo")?> col 35"><?=$oAm["nucleo"]?></div>
			<div class="<?=hide("galpao")?> col 15 rot">Galpão:</div>
			<div class="<?=hide("galpao")?> col 35 quebralinha"><?=$oAm["galpao"]?></div>
			
		</div>
		<div class="row">
			<div class="<?=hide("idade")?> col 15 rot">Idade:</div>
			<div class="<?=hide("idade")?> col 85"><?=$oAm["idade"]." ".$oAm["tipoidade"]?></div>
		</div>
		<div class="row">
			<div class="<?=hide("lote")?> col 15 rot">Lote:</div>
			<div class="<?=hide("lote")?> col 85 quebralinha"><?=$oAm["lote"]?></div>
		</div>		
		<div class="row">
			<div class="<?=hide("linha")?> col 15 rot">Linha:</div>
			<div class="<?=hide("linha")?> col 35 quebralinha"><?=$oAm["linha"]?></div>
			<div class="<?=hide("regoficial")?> col 15 rot">Nº Registro oficial:</div>
			<div class="<?=hide("regoficial")?> col 35 quebralinha"><?=$oAm["regoficial"]?></div>
		</div>
		<div class="row">
			<div class="<?=hide("formaarmazen")?> col 15 rot">Forma de armaz.:</div>
			<div class="<?=hide("formaarmazen")?> col 20"><?=$oAm["formaarmazen"]?></div>
			<div class="<?=hide("meiotransp")?> col 15 rot">Meio de transp.:</div>
			<div class="<?=hide("meiotransp")?> col 50"><?=$oAm["meiotransp"]?></div>
		</div>
		<div class="row">
			<div class="<?=hide("condconservacao")?> col 15 rot">Cond. conservação:</div>
			<div class="<?=hide("condconservacao")?> col 85 quebralinha"><?=nl2br($oAm["condconservacao"])?></div>
		</div>
		<div class="row">
			<div class="<?=hide("sexo")?> col 15 rot">Sexo:</div>
			<div class="<?=hide("sexo")?> col 20"><?=$oAm["sexo"]?></div>
			<div class="<?=hide("clienteterceiro")?> col 15 rot">Cliente 3&ordm;:</div>
			<div class="<?=hide("clienteterceiro")?> col 50"><?=$oAm["clienteterceiro"]?></div>
		</div>
		<div class="row">
			<div class="<?=hide("nucleoorigem")?> col 15 rot">Núcleo origem:</div>
			<div class="<?=hide("nucleoorigem")?> col 20 quebralinha"><?=$oAm["nucleoorigem"]?></div>
			<div class="<?=hide("tipo")?> col 15 rot">Tipo:</div>
			<div class="<?=hide("tipo")?> col 50"><?=$oAm["tipo"]?></div>
		</div>
		<div class="row">
			<div class="<?=hide("especificacao")?> col 15 rot">Especificações:</div>
			<div class="<?=hide("especificacao")?> col 20 quebralinha"><?=$oAm["especificacao"]?></div>
			<div class="<?=hide("partida")?> col 15 rot">Partida:</div>
			<div class="<?=hide("partida")?> col 15 quebralinha"><?=$oAm["partida"]?></div>
			<div class="<?=hide("fornecedor")?> col 15 rot">Fornecedor:</div>
			<div class="<?=hide("fornecedor")?> col 20 quebralinha"><?=$oAm["fornecedor"]?></div>
		</div>
		<div class="row">
			<div class="<?=hide("datafabricacao")?> col 15 rot">Data fabricação:</div>
			<div class="<?=hide("datafabricacao")?> col 20 quebralinha"><?=$oAm["datafabricacao"]?></div>
			<div class="<?=hide("identificacaochip")?> col 15 rot">Chip/Identif.:</div>
			<div class="<?=hide("identificacaochip")?> col 15 quebralinha"><?=$oAm["identificacaochip"]?></div>
			<div class="<?=hide("diluicoes")?> col 15 rot">Diluições:</div>
			<div class="<?=hide("diluicoes")?> col 20 quebralinha"><?=$oAm["diluicoes"]?></div>
		</div>
		<div class="row">
			<div class="<?=hide("nroplacas")?> col 15 rot">Nº Placas:</div>
			<div class="<?=hide("nroplacas")?> col 20 quebralinha"><?=$oAm["nroplacas"]?></div>
			<div class="<?=hide("nrodoses")?> col 15 rot">Nº Doses:</div>
			<div class="<?=hide("nrodoses")?> col 15 quebralinha"><?=$oAm["nrodoses"]?></div>
			<div class="<?=hide("semana")?> col 15 rot">Semana:</div>
			<div class="<?=hide("semana")?> col 20 quebralinha"><?=$oAm["semana"]?></div>
		</div>	
		<div class="row">
			<div class="<?=hide("notafiscal")?> col 15 rot">Nota Fiscal:</div>
			<div class="<?=hide("notafiscal")?> col 20 quebralinha"><?=$oAm["notafiscal"]?></div>
			<div class="<?=hide("vencimento")?> col 15 rot">Vencimento:</div>
			<div class="<?=hide("vencimento")?> col 15 quebralinha"><?=$oAm["vencimento"]?></div>
			<div class="<?=hide("fabricante")?> col 15 rot">Fabricante:</div>
			<div class="<?=hide("fabricante")?> col 20 quebralinha"><?=$oAm["fabricante"]?></div>
		</div>
		<div class="row">
			<div class="<?=hide("sexadores")?> col 15 rot">Sexadores:</div>
			<div class="<?=hide("sexadores")?> col 20 quebralinha"><?=$oAm["sexadores"]?></div>
			<div class="<?=hide("localexp")?> col 15 rot">Local específico:</div>
			<div class="<?=hide("localexp")?> col 50"><?=$oAm["localexp"]?></div>
		</div>
		<div class="row">
			<div class="<?=hide("lacre")?> col 15 rot">Lacre:</div>
			<div class="<?=hide("lacre")?> col 20 quebralinha"><?=$oAm["lacre"]?></div>
			<div class="<?=hide("tc")?> col 15 rot">Termo de coleta:</div>
			<div class="<?=hide("tc")?> col 50"><?=$oAm["tc"]?></div>
		</div>
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
			<div class="<?=hide("mortalidade")?>col 15 quebralinha"><?=$oAm["mortalidade"]?></div>
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
			<div class="<?=hide("responsavel")?> col 15 rot">Respons. coleta:</div>
			<div class="<?=hide("responsavel")?> col 85 quebralinha"><?=$oAm["responsavel"]?></div>
		</div>
		<div class="rows">
			<div class="<?=hide("observacao")?> col 15 rot">Observação:</div>
			<div class="<?=hide("observacao")?> col 85 quebralinha"><?=nl2br($oAm["observacao"])?></div>
		</div>
		<div class="row">
			<div class="col grupo 100 quebralinha">
				<div class="titulogrupo">Exames Solicitados</div>
			</div>
		</div>
	<?
	$i=0;
	while (list($k, $v) = each($oRes)){
	?>
		<div class="row">
			<div class="col grupo 100 quebralinha">
				<?echo("LDA: ".$v["idresultado"]." - ".$v["descr"]);?>
			</div>
		</div>
	<?
		$i++;
	}
	?>
		<!-- <div class="row">
			<div class="col grupo 100 quebralinha">
				<div class="titulogrupo"></div>
			</div>
		</div> -->
		<hr>
		<br>
	<?
	if($oAm["statustra"]=="ASSINADO"){
	$sqlass = "select idpessoa 
		    from carrimbo 
			where tipoobjeto = 'amostra' 
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
	?>
		<div class="row">
			<div class="col 15 rot">Técnico Resp.:</div>
			<div class="col 25"> <?=$nomresp?></div>
			<div class="col 10 rot">CRMV:</div>
			<div class="col "><?=$crmvresp?></div>
			<div class="col 15 rot">Assinatura.:</div>
			<div class="col "><img style="position: relative; top: 13px;" src="../inc/img/sig<?=strtolower(trim($respidpessoa))?>.gif"></div>
		</div>
	<?}else{?>
		<div class="row">
			<div class="<?=hide("responsavelof")?> col 15 rot">Respons. oficial:</div>
			<div class="<?=hide("responsavelof")?> col 35 quebralinha"><?=$oAm["responsavelof"]?></div>
			<div class="<?=hide("responsavelofcrmv")?> col 10 rot">CRMV:</div>
			<div class="<?=hide("responsavelofcrmv")?> col 15"><?=$oAm["responsavelofcrmv"]?></div>
			<div class="<?=hide("responsaveloftel")?> col 10 rot">Tel:</div>
			<div class="<?=hide("responsaveloftel")?> col 15"><?=$oAm["responsaveloftel"]?></div>
		</div>
		<!-- campos pontilhados para preenchimento manual -->
		<div class="row">
			<div class="col 10 rot">Assinatura.:</div>
			<div class="col 35 sublinhado"></div>
			<div class="col 5 rot">Data:</div>
			<div class="col 35 sublinhado"></div>
		</div>
	<?}?>
	</pagina>
	
<!-- LDA-->
<?
if($oAm["statustra"]=="ABERTO" or $oAm["statustra"]=="ENVIADO"){
	die;
}

reset($oRes);
while (list($k, $v) = each($oRes)){
    
  
    $idsubtipoam=$v["idsubtipoamostra"];
?>
	<div class="quebrapagina"></div>
	<header class="row margem0.0">
		<div class="logosup col 20"><img src="../inc/img/Logo PB Inata.jpg"></div>
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
		<div class="col 15 rot">Cnpj:</div>
		<div class="col 35"><?=formatarCPF_CNPJ($oAm["cpfcnpj"])?></div>
		<div class="col 15 rot">Inscr. Estadual:</div>
		<div class="col 35"><?=$oAm["inscrest"]?></div>
	</div>
	<div class="row">
		<div class="<?=hide("responsavelof")?> col 15 rot">Respons. oficial:</div>
		<div class="<?=hide("responsavelof")?> col 35 quebralinha"><?=$oAm["responsavelof"]?></div>
		<div class="<?=hide("responsavelofcrmv")?> col 10 rot">CRMV:</div>
		<div class="<?=hide("responsavelofcrmv")?> col 15"><?=$oAm["responsavelofcrmv"]?></div>
		<div class="<?=hide("responsaveloftel")?> col 10 rot">Tel:</div>
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
		<div class="<?=hidelda("formaarmazen",$idsubtipoam)?> col 15 rot">Forma de Armaz.:</div>
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
		<div class="<?=hide("responsavel")?> col 15 rot">Respons. coleta:</div>
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
        <?if(($v["modelo"]=="DESCRITIVO" and $v["modo"]=="AGRUP") OR ($v["modelo"]=="DROP" and $v["modo"]=="AGRUP")){?>
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
                ?>
        
                 <?
		while($rowind=mysqli_fetch_assoc($resind)){ ?>
	<div class="row">
		<div class="col grupo 100 quebralinha">
		<?
                    echo("Amostra ".$rowind['identificacao']." apresentou resultado ".$rowind['resultado'].". ");
                ?>
                   
		</div>
        </div>
        <?      } ?>          

            
       <?}elseif($v["modelo"]=="UPLOAD"){		
            $strsql = "SELECT * FROM resultadoelisa where idresultado = ". $v["idresultado"] . " and  idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]." and status = 'A' order by idresultadoelisa";
            $result = d::b()->query($strsql) or die("A Consulta  dos resultados elisa falhou : " . mysqli_error() . "<p>SQL: $strsql");

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
?>
    
       <?   
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
</body>

</html>