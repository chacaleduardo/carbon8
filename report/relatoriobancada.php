<?

require_once("../inc/php/validaacesso.php");
//Alterado para between a pedido de Nessi
$exercicio      = $_GET["exercicio"];
$idamostra_1	= $_GET["registro_1"];
$idamostra_2	= $_GET["registro_2"];
$idunidade		= $_GET["unidade"];
$idtiporelatorio= $_GET["relatorio"]; 
$idtipoteste =$_GET["teste"];
$status=$_GET["status"]; 

if(!empty($idtipoteste)){
	$clausula .=" r.idtipoteste=".$idtipoteste." and ";
}

if (!empty($idamostra_1) or !empty($idamostra_2)){
	if (is_numeric($idamostra_1) and is_numeric($idamostra_2)){
		$clausula .= " (r.idregistro BETWEEN " . $idamostra_1 ." and " . $idamostra_2 .")"." and ";
	}else{
		die ("Os Nºs de Registro informados são inválidos: [".$idamostra_1."] e [".$idamostra_2."]");
	}
}

if(!empty($idunidade)){
	if(is_numeric($idunidade)){
		$clausula .= " r.idunidade = " . $idunidade ." and ";
	}else{
		die ("A unidade informada possui caracteres inválidos: [".$idunidade."]");
	}
}

if(!empty($status)){
	$clausula .=" r.status='".$status."' and ";
}
if(!empty($idtiporelatorio)){
    $sqlx="select * from tiporelatorio where idtiporelatorio=".$idtiporelatorio;
    $resx = d::b()->query($sqlx) or die("Falha ao buscar o tipo do relatorio : " . mysqli_error(d::b()) . "<p>SQL:".$sqlx);
    $rowx= mysqli_fetch_assoc($resx);
    $titulorelatorio=$rowx['tiporelatorio'];
    if($idtiporelatorio!=6){//Relatorio geral de testes
	$clausula .=" r.idtiporelatorio=".$idtiporelatorio." and ";
    }
    $nl=$rowx['impressao'];// RETRATO OU PAISAGEM
    $rodapehtml=$rowx['rodape'];
  
  
     
}else{
    die("Favor informar o tipo do relatório.");
}

if(!empty($exercicio)){
	if(is_numeric($exercicio)){
		$clausula .= " r.exercicio = " . $exercicio ." and ";
	}else{
		die ("O Exercício informado possui caracteres inválidos: [".$exercicio."]");
	}
}

if (!empty($clausula)){
	
	//$clausula .=" r.exercicio = EXTRACT(YEAR FROM sysdate()) and ";
	$sql = $rowx['code'].' where ' . substr($clausula,1,strlen($clausula) - 5)." order by r.exercicio,r.idregistro";
}
//echo $sql;die;
echo "<!--";
echo $sql;
echo "-->";
//echo $sql;die;
$res = d::b()->query($sql) or die("Falha ao gerar o Relatório de bancada : " . mysqli_error(d::b()) . "<p>SQL: $sql");
$arrColunas = mysqli_fetch_fields($res);
$arrret=array();
$i=0;
while($r = mysqli_fetch_assoc($res)){
   $i=$i+1;
    //para cada coluna resultante do select cria-se um item no array
    foreach ($arrColunas as $col) {	 
	$arrval[$i][$col->name]=$r[$col->name];
	$arrcol[$col->name]=$r[$col->name];
    }
}

?>

<html>
<head>
<title>Relatório - <?=$rowx['tiporelatorio']?></title>
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
function cabecalho($titulorelatorio,$arrcol){
?>   

<table width="100%" border="1">

  <tr class="header3" >
      <?
      //print_r($arrColunas); die;
while (list($a, $b) = each($arrcol)){
    if($a=="Resultado" or $a=="Observação"){
	$w="250px";
    }elseif($a=="Tipo de Salmonela"){
	$w="150px";
    }elseif($a=="Semente"){
	$w="100px";
    }else{
	$w="40px";
    }
?>
      <td align="center" width="<?=$w?>" nowrap><?=$a?></td>
<?
}
reset($arrcol);//reseta o array de eventos 

 ?>
    </tr>
<?
}//function cabecalho(){
 function rodape(){ 
?>
</table>
<br>
<p>
<table style='border:none !important'>
    <tr style='border:none !important'>
    <td style='border:none !important;width: 1000px' align='left'>Realizado por:_________________<br><p><br>Conferido por:_________________</td>   	
    <td align='' style='vertical-align: top; width: 250px'>
	<ul>Legenda</ul>
	<li style='color: blue;'>Negativo:(N)</li>
	<li style='color: blue;'>Ausente: (A)</li>
        <li style='color: red;'>Suspeito:(S)</li>
        <li style='color: red;'>Positivo: (P)</li>
    </td>
    </tr>
    <tr>
        <td></td>
    </tr>
</table>

    <?}//function rodape(){ 

cabecalho($titulorelatorio,$arrcol);
$l=0;

while (list($a, $b) = each($arrval)){
    $l=$l+1;
			
    if($l==$nl){
	echo($rodapehtml);
	cabecalho($titulorelatorio,$arrcol);
	$l=0;
    }
 ?>   
  <tr class="res1" style="height:<?=$rowx['linha']?>;"> 
<?
    while (list($n, $v) = each($b)){
?>

    <td style="border:1px solid;"><?=$v?></td>   
    <?
  }
?>
  </tr>

<?  
   
} //do while
echo($rodapehtml);
?>
   

</table>