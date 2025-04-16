<?
header("Content-Type: text/html;  charset=UTF-8",true);

require_once("../inc/php/validaacesso.php");
//Alterado para between a pedido de Nessi
$cliente		= $_GET["cliente"];
//$nome			= $_GET["nome"];
$especie		= $_GET["especie"];
$idplantel		= $_GET["idplantel"];
$status			= $_GET["status"];
$estado			= $_GET["estado"];
$statuscrm		= $_GET["statuscrm"];
//if (!empty($idamostra)){
	//$clausula .= " a.idamostra = " .$idamostra ." and ";
//}

if(!empty($cliente)){
	$clausula .="and p.nome like '%".$cliente."%' ";
}
if(!empty($status)){
	$clausula .="and p.status = '".$status."' ";
}
if(!empty($estado)){
	$clausula .="and e.uf = '".$estado."' ";
}
if(!empty($statuscrm)){
	$clausula .="and p.statuscrm = '".$statuscrm."' ";
}
if(!empty($idplantel)){
	$planteis = explode(",", $idplantel);
	$iplanteis = sizeof($planteis);
	if($iplanteis > 1){
		$clausula .= " and po.idplantel in (";
		$virg="";
		foreach($planteis as $i => $valor){
			$strin .= $virg . "'".$valor."'";
			$virg=",";
		}
		$clausula .= $strin. ")";
	}else{
		$clausula .= " and po.idplantel in ('".$idplantel."')";
	}
}



if (!empty($clausula)){
	
	//$clausula .=" a.exercicio = EXTRACT(YEAR FROM sysdate()) and ";
	$clausula .= ' and 1';
}
//echo $clausula;die;
$sql = " 
SELECT
	p.idempresa,
	p.idpessoa,
	p.nome,
	p.cpfcnpj,
	tp.tipoplantel,
	tpp.qtd,
	tpp.idtipoplantel,
	e.uf,
	p.statuscrm,
	pl.plantel,
	concat(IFNULL(logradouro,''),' ', IFNULL(endereco,''), ' ',IFNULL(numero,''),if(length(trim(complemento)) > 0, concat(' - ',complemento,' -'),''),if(length(trim(bairro)) > 0, concat(' ',bairro),''),' - ',IFNULL(cidade,''),'/',IFNULL(uf,'')) as endereco
FROM
	pessoa p 
LEFT JOIN 
	tipoplantelpessoa tpp ON tpp.idpessoa = p.idpessoa
LEFT JOIN 
	tipoplantel tp on tp.idtipoplantel = tpp.idtipoplantel 
left JOIN 
	endereco e on e.idpessoa = p.idpessoa AND e.idtipoendereco = 2
LEFT JOIN
    plantelobjeto po ON (po.idobjeto = p.idpessoa
        AND po.tipoobjeto = 'pessoa') 
join 
	plantel pl on (po.idplantel = pl.idplantel)
WHERE
	p.idtipopessoa = 2 AND tpp.status = 'ATIVO' and p.idempresa = '".$_SESSION["SESSAO"]["IDEMPRESA"]."'" . $clausula . " order by idtipoplantel, tipoplantel,   nome 
;";
//echo $sql;die;
$res = d::b()->query($sql) or die("Falha ao gerar o Relatório Clientes/Plantel : " . mysqli_error(d::b()) . "<p>SQL: $sql");





	$_i = 0;
    $_numcolunas = mysql_num_fields($res);
	$_ipagpsqres = mysql_num_rows($res);
	if($_ipagpsqres==1){
		$strs = $_ipagpsqres." Registro encontrado";
	}elseif($_ipagpsqres>1){
		$strs = $_ipagpsqres." Registros encontrados";
	}else{
		$strs = "Nenhum Registro encontrado";
	}

	$_nomeimpressao = "[".md5(date('dmYHis'))."] gerada em [".date(" d/m/Y H:i:s")."]";

	// GVT - 17/04/2020 - Altera logo do relatório, utiliza a imagem cadastrada na empresa.
	$sqlfig="select logosis from empresa where idempresa =".$_SESSION["SESSAO"]["IDEMPRESA"];
	$resfig = d::b()->query($sqlfig) or die("Erro ao recuperar figura para cabeçalho do relatório: ".mysql_error());
	$figrel=mysqli_fetch_assoc($resfig);

	//$figurarelatorio = (empty($figrel["figrelatorio"]))?"../inc/img/repheader.png":$figrel["figrelatorio"];
	//$figurarelatorio = "../inc/img/repheader.png";
	$figurarelatorio = $figrel["logosis"];
?>
	<table class="tbrepheader">
	<tr>
		<td rowspan="3" style="width:200;"><img src="<?=$figurarelatorio?>"></td>
		<td class="header"><?=$_header?></td>
		<td></td>
	</tr>
	<tr>
		<td class="subheader">(<?=$strs?>)</td>
	</tr>
	</table>
	<br>
	
	<table class="normal">
		<tr class='header'>


<html>
<head>
<title>Relatorio Clientes/Plantel </title>
</head>
<style>
.normal {
    border: 1px solid silver;
    border-collapse: collapse;
}
html{
	font-family: Arial, FreeSans, Sans,  Serif, SansSerif;
	font-size: 11px;
	margin:0px;
	padding:0px;
}

body{
	margin:0px;
	padding:0px;
}
.tbrepheader{
	border: 0px;
	width: 100%;
}
.tbrepheader .header{
	font-size: 13px;
	font-weight: bold;
}

.tbrepheader .subheader{
	font-size: 10px;
	color: gray;
}
.tbrepheader .titulo{
	font-size: 18px;
	font-weight: bold;
}
.tbrepheader .res{
	font-size: 18px;
}
a:link		{text-decoration: none; color: blue}
a:visited	{text-decoration: none; color: blue}
a:hover		{text-decoration: none; color: blue}
a.inst:link	{text-decoration: none; color: blue}
a.inst:visited	{text-decoration: none; color: blue}
a.inst:hover	{text-decoration: none; color: blue}

.title{
	font-family: Arial, FreeSans, Sans,  Serif, SansSerif;
	font-size: 16pt;
	font-weight: bold;
	weight: bold;
	color: black;
	text-align: center;
	width: 100%;
}
.title12{
	font-family: Arial, FreeSans, Sans,  Serif, SansSerif;
	font-size: 12pt;
	font-weight: bold;
	weight: bold;
	color: black;
	text-align: center;
	width: 100%;
}
.titlemeio{
	font-family: Arial, FreeSans, Sans,  Serif, SansSerif;
	font-size: 12pt;
	font-weight: bold;
	weight: bold;
	color: black;
	text-align: center;
	width: 100%;
	border-top: 1px solid gray;
	border-bottom: 1px solid gray;
	padding-top:10px;
	padding-bottom:10px;
}
.titleleft{
	font-family: Arial, FreeSans, Sans,  Serif, SansSerif;
	font-size: 16pt;
	font-weight: bold;
	weight: bold;
	color: black;
	text-align: left;
	width: 100%;
}
table{
	border: 1px solid black;
	border-collapse: collapse;
	margin-bottom: 15;
	font-family: Arial, FreeSans, Sans,  Serif, SansSerif;
	font-size: 10pt;
	weight: bold;
	color: black;
}



.normal{
	border-top: 0px solid black;
	border-bottom: 0px solid black;
	border-left: none;
	border-right: none;
	border-collapse: collapse;
	width: 100%;
	margin-bottom: 15;
}
.normal .rot{
	font-size: 10pt;
	color: black;
	text-align: right;
	padding-right: 5px;
}
.normal .rotx{
	padding-right: 35px;
	font-size: 10pt;
	color: black;
	text-align: left;
}
.normal .valres{
	padding-right: 5px;
	font-size: 10pt;
	color: black;
	text-align: left;
}
.normal .rotvalres{
	padding-left: 15px;
	font-size: 8pt;
	color: black;
	text-align: left;
}
.header1{
	font-size: 10pt;
	font-weight: bold;
	weight: bold;
	color: black;
	text-align: left;
	border-bottom: 0px solid black;
	bgcolor: #CCCCCC;
	background-color: #CCCCCC;
}
.header2{
	font-size: 7pt;
	font-weight: bold;
	weight: bold;
	color: gray	;
	text-align: left;
	border: 0px;
}
.header3{
	font-size: 7pt;
	font-weight: bold;
	weight: bold;
	color: black;
	text-align: left;
	border: 0px;
}
.res1{
	font-size: 8pt;
	color: black;
	text-align: left;
	border:none;
}

.res1 td{
	font-size: 8pt;
	color: black;
	text-align: left;
	border:0px;
	padding-left:3px;
	padding-right:3px;
}

.divisorcinza{
	border-top: 1px solid gray;
	height: 0px;
}
.localdatacab{
	font-size: 10pt;
	color: black;
	text-align: right;
}
.cabrot{
	font-size: 10pt;
	color: black;
	text-align: left;
	padding-right: 10px;
}
.cabrotbold{
	font-size: 10pt;
	font-weight: bold;
	weight: bold;
	color: black;
	text-align: right;
	padding-right: 10px;
}
.cabval{
	font-size: 10pt;
	color: black;
	text-align: left;
}
.cabvalbold{
	font-size: 10pt;
	font-weight: bold;
	weight: bold;
	color: black;
	text-align: left;
}
.resdesc{
	font-family: Courier New;
	font-size: 10pt;
	font-weight: bold;
	weight: bold;
	color: black;
	/*margin-left: 40px;*/
}
.divdesc{
	text-align: left;
	padding-left: 35px;
	padding-top: 15px;
	padding-bottom: 25px;
}
.label7silver{
	font-family: Arial, FreeSans, Sans,  Serif, SansSerif;
	font-size: 9pt;
	font-weight: bold;
	color: silver;
}
.label10preto{
	font-family: Arial, FreeSans, Sans,  Serif, SansSerif;
	font-size: 10pt;
	font-weight: bold;
}
.graf{
	border: none;
	font-family: Arial, FreeSans, Sans,  Serif, SansSerif;
	font-size: 9pt;
	display: inline;
}
.graf .padrao{
	bgcolor: #1874cd;
	background-color: #1874cd;
	width: 20px;
	height: 20px;
	border: 3px solid white;
}
.graf .obtido{
	bgcolor: #ffc125;
	background-color: #ffc125;
	width: 20px;
	height: 20px;
	border: 3px solid white;
}

.tabelisa {
	border: 1px solid #D7D7D7;
	border-collapse: collapse;
	padding: 0px;
	margin-left: 5px;
	font-family: Arial, FreeSans, Sans,  Serif, SansSerif;
	font-size: 8pt;
	color: black;

	width: 300px;
}
.tabelisa .hdr td{
	border: 1px solid #D7D7D7;
	bgcolor: #EDEDED;
	background-color: #EDEDED;
	font-family: Arial, FreeSans, Sans,  Serif, SansSerif;
	font-size: 8pt;
	color: black;
	font-weight: bold;
	weight: bold;

}
.tabelisa .trnormal td{
	border: 1px solid #D7D7D7;
	font-family: Arial, FreeSans, Sans,  Serif, SansSerif;
	font-size: 8pt;
	color: black;
}
.tabelisa .trpos td{
	border: 1px solid #D7D7D7;
	bgcolor: #FFC0C0;
	background-color: #FFC0C0;
	font-family: Arial, FreeSans, Sans,  Serif, SansSerif;
	font-size: 8pt;
	color: black;
}

.horas{
	border: 1px solid black;
	border-collapse: collapse;
	margin-bottom: 15;
	font-family: Arial, FreeSans, Sans,  Serif, SansSerif;
	font-size: 9pt;
	weight: bold;
	color: black;
}

.horas tr{
	border: 0px solid black;
}
.horas tr td{
	border-right: 1px dotted gray;
	border-left: 1px dotted gray;
	border-top: 1px solid black;
	border-bottom: 1px solid black;  
}

.horas .cab{
	background-color: #f6f6f6;
	font-weight: bold;
}

ececec

.horas .data{
	width: 90px;
}

.horas .dia{
	width: 33px;
}

.horas .ent{
	width: 45px;
	background-color: #ccffcc;
	background-image: url("../img/icoent.gif");
	background-repeat: no-repeat;
	padding-left: 17px;
}
.horas .sai{
	width: 45px;
	background-color: #ffc9c3;
	background-image: url("../img/icosai.gif");
	background-repeat: no-repeat;
	padding-left: 17px;
}
.horas .subtotal{
	width: 45px;
	background-color: #e6ecf7;
	padding-left: 5px;
}
.horas .rottotal{
	font-size: 9px;
	font-weight: bold;
	color: gray;
	text-align: right;
}
.nimptop{/*numero da impressao superior*/
	border-bottom: 1px solid gray;
	border-bottom-style: dotted;
	font-size: 7pt;
	color: rgb(90, 90, 90);
	text-align: right;
	float: right;
	white-space: nowrap;
}

.normal{
	border: 1px solid silver;
	border-collapse: collapse;	
}

.normal td{
	border: 1px solid silver !important;
	padding: 0px 3px 0px 3px;
}

.normal .header{
	font-size: 10px;
	font-weight: bold;
	color: rgb(75,75,75);
	background-color: rgb(222,222,222);
}
.normal .res{
	font-size: 11px;
}
.normal .res .link{
	background-color:#FFFFFF;
	cursor:pointer;
}
.normal .res .tot{
	background-color:#E8E8E8;
	font-weight: bold;	
	text-align: center;
}
.normal .res .inv{
	border: 0px;
}
.normal .tdcounter{
	border:1px dotted rgb(222,222,222);
	background-color:white;
	color:silver;
	font-size:8px;
}
</style>

<?
function cabecalho(){
?>
<div class="title">Relatorio Clientes/Plantel <?if($tiporel=='BACT2'){echo("2");}?></div>
<br />
<table class="normal"  style="width:686px" border="1">
  <tr class="header" style="height:20px;">
		<td width="45%" nowrap>Cliente</td>
		<td width="15%" nowrap>CNPJ</td>
		<td width="15%" nowrap>Endereço</td>
		<td width="5%" nowrap>UF</td>
		<td width="5%" nowrap>Status CRM</td>
		<td width="15%" >Divisão</td>
		<td width="10%" >Tipo</td>
		<td width="5%" >Qtd</td>
		
		
	</tr>
<?    
}
 function rodape(){ 
?>
	</table>
<br>
<p>
<div>
    <div align="left">Realizado por:_________________</div>   	
    <div align="right" style="vertical-align: top; width: 100%">Conferido por:_________________</div>			
</div>
<div style="page-break-before: always;">
    <?}
    
    
    cabecalho();
    $l=0;
	$totalqtd = 0;
	$totaltipo = 0;
	$i = 0;
	$j = 0;
while ($row = mysqli_fetch_assoc($res)){
	$i++;

	
	if(empty($row['oficial'])){
	 	$oficial = '';
	 }else{
	 	$oficial = 'X';	
	 }
	$l=$l+1;
			
	if($l==50){
	  //  rodape();
	  //  cabecalho();
	  //  $l=0;
	}
	
	 
	if ($row["idtipoplantel"] != $idtipoplantel and $i > 1){
		
		$finalt[$j] = 'TOTAL '.($plantelantigo).' ('.($planteltipoantigo).')';
		$finalv[$j] = $totaltipo;
		$j++;
		echo '
		 <tr class="res1" style="background-color:#f1f1f1; height:20px; font-weight: bold">
    <td style="border:1px solid; text-transform: uppercase;" colspan="7">TOTAL '.($plantelantigo).' ('.($planteltipoantigo).'): <span style="float:right">'.number_format($totaltipo,0,',','.').'</span></td>
  </tr>
		</table><br />
<table class="normal"  style="width:686px" border="1">
  <tr class="header" style="height:20px;">
		<td width="45%" nowrap>Cliente</td>
		<td width="15%" nowrap>CNPJ</td>
		<td width="5%" nowrap>Endereço</td>
		<td width="5%" nowrap>UF</td>
		<td width="5%" nowrap>Status CRM</td>
		<td width="15%" >Plantel</td>
		<td width="10%" >Tipo</td>
		<td width="5%" >Qtd</td>
		
		
	</tr>';
		$totaltipo = 0;
	}
		
?>
  <tr class="res1">
    <td style="border:1px solid;"><?=$row["nome"]  ?></td>
    <td style="border:1px solid; "><?=$row["cpfcnpj"]?></td>
	<td style="border:1px solid; "><?=$row["endereco"]?></td>
	<td style="border:1px solid; "><?=$row["uf"]?></td>
	 <td style="border:1px solid;" ><?=$row["statuscrm"];?></td>
    <td style="border:1px solid;" ><?=$row["plantel"];?></td>
	<td style="border:1px solid;" ><?=$row["tipoplantel"]?></td>
    <td style="border:1px solid;text-align: right" ><?=number_format($row["qtd"],0,',','.');?></td>
    
     
  </tr>

<?
	$totalqtd = $totalqtd + $row["qtd"];
	$totaltipo = $totaltipo + $row["qtd"];
	$idpessoa = $row['idpessoa'];
	$idplantel = $row['idplantel'];
	$idtipoplantel = $row['idtipoplantel'];
	$plantelantigo = $plantel;
	$planteltipoantigo = $row['tipoplantel'];
} 

	$finalt[$j] = 'TOTAL '.($plantelantigo).' ('.($planteltipoantigo).')';
	$finalv[$j] = $totaltipo;
	$j++;
//do while
//rodape()
?>
 <tr class="res1" style="background-color:#f1f1f1; height:20px; font-weight: bold">
    <td style="border:1px solid; text-transform: uppercase;" colspan="7">TOTAL <?=($plantel);?> (<?=$planteltipoantigo;?>) : <span style="float:right"><?=number_format($totaltipo,0,',','.');?></span></td>
  </tr>
<br><br>
<table class="normal"  style="width:686px" border="1"> 
<?
$i = 0; 
while ($i < $j){
	?> 
<tr class="res1" style="background-color:#f1f1f1; height:20px;  ">   
 <td style="font-size:12px !important" colspan="7"><?=$finalt[$i];?> <span style="float:right"><?=number_format($finalv[$i],0,',','.');?></span></td>
</tr>	
	<?
	$i++;
}

?>
<tr class="res1" style="background-color:#f1f1f1; height:40px; font-weight: bold; ">  
 <td style="border:1px solid; font-size:14px !important" colspan="7	">TOTAL: <span style="float:right"><?=number_format($totalqtd,0,',','.');?></span></td>
</tr>
</table>

