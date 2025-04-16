<?
require_once("../inc/php/validaacesso.php");

//Alterado para between a pedido de Nessi
$exercicio      = $_GET["exercicio"];
$idunidade		= $_GET["unidade"];
$idregistro_1	= $_GET["registro_1"];
$idregistro_2	= $_GET["registro_2"];
$status	= $_GET["status"];


//if (!empty($idregistro)){
	//$clausula .= " a.idregistro = " .$idregistro ." and ";
//}

if(empty($status)){
	die("E necessario informar o status.");
}elseif($status=='TODOS'){
	$clausula .=" a.status in ('ABERTO','PROCESSANDO') and ";
}else{
	$clausula .=" a.status = '".$status."' and ";
}

if(!empty($idunidade)){
	if(is_numeric($idunidade)){
		$clausula .= " a.idunidade = " . $idunidade ." and ";
	}else{
		die ("A unidade informada possui caracteres inválidos: [".$idunidade."]");
	}
}

if(!empty($exercicio)){
	if(is_numeric($exercicio)){
		$clausula .= " a.exercicio = " . $exercicio ." and ";
	}else{
		die ("O Exercício informado possui caracteres inválidos: [".$exercicio."]");
	}
}

if (!empty($idregistro_1) or !empty($idregistro_2)){
	if (is_numeric($idregistro_1) and is_numeric($idregistro_2)){
		$clausula .= " (a.idregistro BETWEEN " . $idregistro_1 ." and " . $idregistro_2 .")"." and ";
	}else{
		die ("Os Nºs de Registro informados são inválidos: [".$idregistro_1."] e [".$idregistro_2."]");
	}
}

if (!empty($clausula)){
	$clausula = 'where ' . substr($clausula,1,strlen($clausula) - 5);
}
//echo $clausula;die;
$sql = " select * from vwrelsorologico a " . $clausula . " order by idregistro";


$res =  d::b()->query($sql) or die("Falha ao gerar o Relatório Sorologico : " . mysql_error(d::b()) . "<p>SQL: $sql");

?>

<html>
<head>
<title>Sislaudo - <?=$titulo?></title>
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
<body style="max-width:1100px;">
<?
    function cabecalho(){
?>

<table margin="0" padding="0" style="border:0px;">
	<tr style="border:0px;">
	<td style="border:0px;">
		<div class="title12">Relatório Sorológico </div>
	</td>
	</tr>
	<tr style="border:0px;">
	<td style="border:0px;">
		<table border="1">
		  <tr class="header3">
			<td width="0%" nowrap align="center">Exerc.</td>
			<td width="0%" nowrap align="center">NºReg</td>
			<td width="0%" nowrap align="center">Data</td>
			<td width="0%" nowrap align="center">Cliente</td>
			<td width="0%" nowrap align="center">Qtd.</td>
			<td width="0%" nowrap align="center">Sigla</td>
			<td width="0%" nowrap align="center">Núcleo</td>
			<td width="0%" nowrap align="center">Lote</td>
			<td width="0%" nowrap align="center">Idade</td>
			<td width="0%" nowrap align="center" style="width:220px;">Resultado</td>
			<td width="0%" nowrap align="center" style="width:50px;">HI</td>
			<td width="0%" nowrap align="center" style="width:50px;">SAL</td>
			<td width="0%" nowrap align="center" style="width:220px;">Observação</td>
			
		</tr>
<?
    }
    function rodape(){ 
?>
				</table>
	</td>
</tr>

</table>
<div>
    <div align="left">Realizado por:_________________</div>   	
    <div align="right" style="vertical-align: top; width: 100%">Conferido por:_________________</div>			
</div>
<div style="page-break-before: always;">
    <?}?>



<body style="margin:0px;padding:0px;">


		<?
		cabecalho();
		$l=0;
		while ($row = mysqli_fetch_assoc($res)){
			if($row["sigla"]=="SAR MG"){
				$strcor="blue";
			}elseif($row["sigla"]=="SAR MS"){
				$strcor="green";
			}elseif($row["sigla"]=="SAR PUL"){
				$strcor="red";
			}else{
				$strcor="";
			}
		$l=$l+1;
			
		if($l==18){
		    rodape();
		    cabecalho();
		    $l=0;
		}

		?>
		  <tr class="res1" style="height:35px;">
		    <td style="border:1px solid;" nowrap><?=$row["exercicio"]  ?></td>
		    <td nowrap style="text-align: center; cursor:pointer; border:1px solid; color:blue;" onclick="janelareport('../forms/inclusaoresultado.php?acao=u&idamostra=<?=$row["idamostra"]?>&idresultado=<?=$row["idresultado"]?>')"><?=$row["idregistro"] ?></td>
		    <td style="border:1px solid;" nowrap><?=$row["dataamostra"] ?></td>
		    <td style="border:1px solid;" nowrap><?=$row["nome"] ?></td>
		    <td style="border:1px solid;" nowrap><?=$row["quantidade"] ?></td>
		    <td style="border:1px solid;" nowrap><font color="<?=$strcor?>"><?=$row["sigla"] ?></font></td>
		    <td style="border:1px solid;" nowrap><?=$row["nucleo"] ?></td>
		    <td style="border:1px solid;" nowrap><?=$row["lote"] ?></td>
		    <td style="border:1px solid;" nowrap><?=$row["idade"] ?></td>
		    <td style="border:1px solid;"></td>
		    <td style="border:1px solid;"></td>
		    <td style="border:1px solid;width:50px;"></td>
		    <td style="border:1px solid;width:220px;"></td>
		    
		  </tr>

		<?
		} //do while
		rodape()
		?>


</body>
</html>
