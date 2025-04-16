<?
require_once("../inc/php/validaacesso.php");
//Alterado para between a pedido de Nessi
$exercicio      = $_GET["exercicio"];
$idamostra_1	= $_GET["registro_1"];
$idamostra_2	= $_GET["registro_2"];
$idunidade		= $_GET["unidade"];
$tiporel= $_GET["relatorio"]; 
$status=$_GET["status"]; 

//if (!empty($idamostra)){
	//$clausula .= " a.idamostra = " .$idamostra ." and ";
//}

if (!empty($idamostra_1) or !empty($idamostra_2)){
	if (is_numeric($idamostra_1) and is_numeric($idamostra_2)){
		$clausula .= " (a.idregistro BETWEEN " . $idamostra_1 ." and " . $idamostra_2 .")"." and ";
	}else{
		die ("Os Nºs de Registro informados são inválidos: [".$idamostra_1."] e [".$idamostra_2."]");
	}
}

if(!empty($idunidade)){
	if(is_numeric($idunidade)){
		$clausula .= " a.idunidade = " . $idunidade ." and ";
	}else{
		die ("A unidade informada possui caracteres inválidos: [".$idunidade."]");
	}
}

if(!empty($status)){
	$clausula .=" a.status='".$status."' and ";
}
if(!empty($tiporel)){
	if($tiporel=='BACT'){
		$clausula .=" a.tiporelatorio='".$tiporel."' and ";
	}ELSE{
		$clausula .=" a.tiporelatorio IN ('BACT','BACT2') and ";
	}	
}else{
	die("Favor informar o tipo do relatório.");
}

if(!empty($exercicio)){
	if(is_numeric($exercicio)){
		$clausula .= " a.exercicio = " . $exercicio ." and ";
	}else{
		die ("O Exercício informado possui caracteres inválidos: [".$exercicio."]");
	}
}

if (!empty($clausula)){
	
	//$clausula .=" a.exercicio = EXTRACT(YEAR FROM sysdate()) and ";
	$clausula = 'where ' . substr($clausula,1,strlen($clausula) - 5);
}
//echo $clausula;die;
$sql = " select * from vwrelbacteriologico a " . $clausula . " order by idregistro";
//echo $sql;die;
$res = d::b()->query($sql) or die("Falha ao gerar o Relatório Bacteriologico : " . mysqli_error(d::b()) . "<p>SQL: $sql");

?>

<html>
<head>
<title>Relatório Bacteriológico <?if($tiporel=='BACT2'){echo("2");}?> </title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<style>
html {
	margin: 20px;
}

body {
	margin: 20px;
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

table tr{
	border: 1px solid black;
}
table tr td{
	border: 1px solid black;
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

</style>

<?
function cabecalho(){
?>
<div class="title">Relatório Bacteriológico <?if($tiporel=='BACT2'){echo("2");}?></div>
<br />
<table width="75%" border="1">
  <tr class="header3">
		<td width="0%" nowrap>Exerc&iacute;cio</td>
		<td width="0%" nowrap>Nº Reg</td>
		<td colspan="6" nowrap align="center" style="width:40px;">Resultado</td>
		<td width="0%" nowrap>Data</td>
		<td width="0%" nowrap>Cliente</td>
		<td width="0%" nowrap>Material</td>
		<td width="0%" nowrap>Teste</td>		
		
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
while ($row = mysqli_fetch_assoc($res)){
	if(empty($row['oficial'])){
	 	$oficial = '';
	 }else{
	 	$oficial = 'X';	
	 }
	$l=$l+1;
			
	if($l==50){
	    rodape();
	    cabecalho();
	    $l=0;
	}
	
?>
  <tr class="res1">
    <td style="border:1px solid;"><?=$row["exercicio"]  ?></td>
    <td nowrap style="text-align: center; cursor:pointer; border:1px solid; color:blue;" onclick="janelareport('../forms/inclusaoresultado.php?acao=u&idamostra=<?=$row["idamostra"]?>&idresultado=<?=$row["idresultado"]?>')"><?=$row["idregistro"] ?></td>

    <td style="border:1px solid;" align="center">Sus</td> 
    <td style="border:1px solid;" align="center">&nbsp;&nbsp;&nbsp;</td> 
    <td style="border:1px solid;" align="center">Neg</td>
    <td style="border:1px solid;" align="center">&nbsp;&nbsp;&nbsp;</td>
    <td style="border:1px solid;" align="center">Pos</td>
    <td style="border:1px solid;" align="center">&nbsp;&nbsp;&nbsp;</td>
    <td style="border:1px solid; "><?=$row["dataamostra"]?></td>
    <td style="border:1px solid;" nowrap><?=$row["nome"] ?></td>
    <td style="border:1px solid;" nowrap><?=$row["tipoamostra"] ?></td>
    <td style="border:1px solid;" nowrap><?=$row["sigla"] ?></td>    
     
  </tr>

<?
} //do while
rodape()
?>



