<?
header("Content-Type: text/html;  charset=UTF-8",true);

require_once("../inc/php/validaacesso.php");
//Alterado para between a pedido de Nessi
$idempresa      = $_GET["idempresa"];
$exercicio		= $_GET["exercicio"];
$idregistro_1	= $_GET["nregistro_1"];
$idregistro_2	= $_GET["nregistro_2"];
$idtipoamostra	= $_GET["tipoamostra"];
$idtipoteste	= $_GET["tipoteste"];
$cliente		= $_GET["cliente"];
$flgoficial		= $_GET["flgoficial"];
$tipoaves		= $_GET["tipoaves"];
$status			= $_GET["status"];
$dataamostra_1 	= $_GET["dataamostra_1"];
$dataamostra_2 	= $_GET["dataamostra_2"];
$idpessoa 		= $_GET["idpessoa"];

//print_r($_GET);

if(!empty($exercicio)){
	if(is_numeric($exercicio)){
		$clausula .= " and  a.exercicio = " . $exercicio;
	}else{
		die ("O Exercício informado possui caracteres inválidos: [".$exercicio."]");
	}
}
if(!empty($idpessoa)){
	$clausula .= " and  a.idpessoa = " . $idpessoa;
}

if(!empty($cliente)){
	$clausula .= " and  p.nome like ('%".$cliente."%') ";
}

if (!empty($idregistro_1) or !empty($idregistro_2)){
	if (is_numeric($idregistro_1) and is_numeric($idregistro_2)){
		$clausula .= " and a.idregistro BETWEEN " . $idregistro_1 ." and " . $idregistro_2 ;
	}else{
		die ("Os Nºs de Registro informados são inválidos: [".$idregistro_1."] e [".$idregistro_2."]");
	}
}
if (!empty($idtipoamostra)){
	$clausula .= " and a.idtipoamostra = " . $idtipoamostra;
}
if (!empty($idtipoteste)){
	$clausula .= " and r.idtipoteste = " . $idtipoteste;
}
if (!empty($status)){
	$clausula .= " and r.status = '" . $status ."'";
}
if($flgoficial=="Y"){
	$clausula .= " and (r.idsecretaria is not null and r.idsecretaria != '')";
}elseif ($flgoficial=="N"){
	$clausula .= " and (r.idsecretaria is null or r.idsecretaria = '')";
}
if (!empty($tipoaves)){
	$clausula .= " and a.tipoaves = '" . $tipoaves ."'";
}
if (!empty($dataamostra_1) and !empty($dataamostra_2)){
	$dataini = validadate($dataamostra_1);
	$datafim = validadate($dataamostra_2);

	if ($dataini and $datafim){
		$clausula .= " and dataamostra  BETWEEN '" . $dataini ."' and '" .$datafim ."'";
	}else{
		die ("Datas n&atilde;o V&aacute;lidas!");
	}
}



//echo $clausula;die;
d::b()->query("SET sql_mode = 'NO_UNSIGNED_SUBTRACTION';");
$sql = " SELECT	p.tipoespecial,
			 	p.tipoteste,
				sum(p.nroamostra) as nroamostra
              	,(sum(p.quantidade) - sum(positivo)) as negatividade
              ,sum(negativo) AS negatividadeeli
              ,sum(positivo) AS positividade
              ,sum(aberto)   AS aberto
              ,sum(fechado)  AS fechado
              ,sum(assinado) AS assinado
              ,round(((sum(positivo) / (sum(positivo) + (sum(p.quantidade) -sum(positivo)))) * 100),2) as percent
              ,round(((sum(positivo) / (sum(positivo) + sum(negativo))) * 100),2) as percenteli

         FROM (SELECT 
         		
				t.tipoteste,
				r.quantidade,
				a.nroamostra,
				(CASE tipoespecial

					WHEN 'ELISA' THEN
						r.quantidade - (select count(*) from resultadoelisa e where e.idresultado = r.idresultado and `result` like '%Pos%' and status = 'A' and local = 'C')
					ELSE
						(CASE
						WHEN r.quantidade != 0

							THEN (r.quantidade - r.positividade)
							ELSE 0
					     END)
				END) as negativo

				,(CASE tipoespecial
					WHEN 'ELISA' THEN
						(select count(*) from resultadoelisa e where e.idresultado = r.idresultado and `result` like '%Pos%' and status = 'A' and local = 'C')
					ELSE
						ifnull(r.positividade,0)
				END) as positivo  
 

                      
                    ,(case r.status
                       when 'ABERTO'
                       then r.quantidade
                       else 0
                      end) as aberto
                      
                    ,(case r.status
                       when 'FECHADO'  
                       then r.quantidade
                       else 0
                      end) as fechado
                      
                    ,(case r.status
                       when 'ASSINADO'
                       then r.quantidade
                       else 0
                      end) as assinado
                     ,t.tipoespecial
                FROM resultado r,
                     vwtipoteste t,
                     amostra a,
					pessoa p
               WHERE p.idpessoa = a.idpessoa
				and t.idtipoteste = r.idtipoteste
				and r.quantidade > 0
 				and r.idamostra    = a.idamostra
                and a.idunidade = 1
                and a.idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]." 
                     " .$clausula. "
) as p
GROUP BY p.tipoteste order by p.tipoteste ";
//echo $sql;die;
$res = d::b()->query($sql) or die("Falha ao gerar o RelatÃ³rio Clientes/Plantel : " . mysqli_error(d::b()) . "<p>SQL: $sql");





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

	$sqlfig="select figrelatorio from empresa where idempresa =".$_SESSION["SESSAO"]["IDEMPRESA"];
	$resfig = d::b()->query($sqlfig) or die("Erro ao recuperar figura para cabeÃ§alho do relatÃ³rio: ".mysql_error());
	$figrel=mysqli_fetch_assoc($resfig);

	//$figurarelatorio = (empty($figrel["figrelatorio"]))?"../inc/img/repheader.png":$figrel["figrelatorio"];
	$figurarelatorio = "../inc/img/repheader.png";
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
	font-size: 11px;
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
}

.normal{
	border: 1px solid silver;
	border-collapse: collapse;	
}

.normal td{
	border: 1px solid silver !important;
	padding: 3px 3px 3px 3px;
}

.normal .header{
	font-size: 9px;
	font-weight: bold;
	color: rgb(75,75,75);
	background-color: rgb(222,222,222);
	text-transform: uppercase;
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
<div class="title">Relatorio Quantitativo de Testes <?if($tiporel=='BACT2'){echo("2");}?></div>
<br />
<table class="normal"  style="width:686px; margin: auto" border="1">
  <tr class="header" style="height:20px;">
		<td width="60%" >Descricao</td>
		<td width="5%" >Negativos</td>
		<td width="5%" >Positivos</td>
		<td width="5%" >Percentual de Positivos</td>
		<td width="5%" >Abertos</td>
		<td width="5%" >Fechados</td>
		<td width="5%" >Assinados</td>
		<td width="5%" >Total de Amostras</td>
		<td width="5%" >Total de Testes</td>
				
		
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


$total = $row["aberto"] + $row["fechado"] + $row["assinado"];
$totalf=$totalf+$total;
$neg=$neg+$row['negatividade'];
$pos=$pos+$row['positividade'];
$aberto=$aberto+$row['aberto'];
$fechado=$fechado+$row['fechado'];
$ass=$ass+$row['assinado'];
$nroamostra=$nroamostra+$row["nroamostra"];

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
	

		
?>
  <tr class="res1">
    <td style="border:1px solid;">        <?=$row["tipoteste"] ?></td>
    <td style="border:1px solid; text-align: right; text-align: right;"  >
    <?if($row["tipoespecial"]!='ELISA'){
    	echo($row["negatividade"]); 
    }else{
    	echo("------");
    }
    ?></td>
    <td style="border:1px solid; text-align: right;"  >
    <?if($row["tipoespecial"]!='ELISA'){
    		echo($row["positividade"]);
    	}else{
    		echo("------");
    	}
     ?></td>
    
    <td style="border:1px solid; text-align: right;"  align="center">
	    <?if($row["tipoespecial"]!='ELISA'){
	    	echo($row["percent"]."%");
	    }else{
    		echo("------");
    	}
	    ?> 
    </td>
    <td style="border:1px solid; text-align: right;"  ><?=$row["aberto"] ?></td>
    <td style="border:1px solid; text-align: right;"  ><?=$row["fechado"] ?></td>
    <td style="border:1px solid; text-align: right;"  ><?=$row["assinado"] ?></td>
    <td style="border:1px solid; text-align: right;"  ><?=$row["nroamostra"] ?></td>
    <td style="border:1px solid; text-align: right;"  ><?=$total?></td>
    
     
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
$percent=round((($pos / ($pos + ($totalf - $pos))) * 100),2);
?>
	<tr class="header3" style="background-color:#f1f1f1; height:40px; font-weight: bold; font-size:12px; color:#333">
		<td width="0%" style="text-align: left; text-transform: uppercase;">Total</td>
		<td width="0%" style="text-align: right; text-transform: uppercase;"><?=$neg?></td>
		<td width="0%" style="text-align: right; text-transform: uppercase;"><?=$pos?></td>
		<td width="0%" style="text-align: right; text-transform: uppercase;"><?=$percent."%"?></td>
		<td width="0%" style="text-align: right;text-transform: uppercase;"><?=$aberto?></td>
		<td width="0%" style="text-align: right; text-transform: uppercase;"><?=$fechado?></td>
		<td width="0%" style="text-align: right; text-transform: uppercase;"><?=$ass?></td>
		<td width="0%" style="text-align: right; text-transform: uppercase;" ><?=$nroamostra?></td>		
		<td width="0%" style="text-align: right; text-transform: uppercase;"><?=$totalf?></td>
	</tr>
	</table>

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
</table>

