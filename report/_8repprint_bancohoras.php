<?
//Criado para Listar o Calculo das Horas Extras dos Funcionários
//Lidiane (12/06/2020) - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=325249
header("Content-Type: text/html;  charset=UTF-8",true);

require_once("../inc/php/validaacesso.php");
require_once("../model/recursoshumanos.php");
baseToGet($_GET["_filtros"]);

//Alterado para between a pedido de Nessi
$idsgsetor			= $_GET["idsgsetor"];
$idsgarea			= $_GET["idsgarea"];
$idsgdepartamento	= $_GET["idsgdepartamento"];
$statusevento		= $_GET["status"];
$status				= $_GET["situacao"];
$nome				= $_GET["_fts"];	

if ($_REQUEST['_fds'])
{
	$data = explode('-',$_REQUEST['_fds']);
	$dataevento_1 = $data[0];
	$dataevento_2 = $data[1];
	$data1 = validadate($dataevento_1);
    $data2 = validadate($dataevento_2);
	if ($data1 and $data2){
		$strin .= " AND (dataponto  BETWEEN '" . $data1 ."' and '" .$data2 ."')";
		$dataPonto = " AND (dataevento  BETWEEN '" . $data1 ."' and '" .$data2 ."')";
	}else{
		die ("Datas n&atilde;o V&aacute;lidas!");
	}
}

$rh = new RH();

if(!empty($idsgsetor) && empty($idsgarea) && empty($idsgdepartamento)){
	$strin .=" AND idobjeto in(".$idsgsetor.") AND tipoobjeto = 'sgsetor'";
} elseif(empty($idsgsetor) && !empty($idsgarea) && empty($idsgdepartamento)){
	$strin .=" AND idobjeto in(".$idsgarea.") AND tipoobjeto = 'sgarea'";
} elseif(empty($idsgsetor) && empty($idsgarea) && !empty($idsgdepartamento)){
	$strin .=" AND idobjeto in(".$idsgdepartamento.") AND tipoobjeto = 'sgdepartamento'";
} elseif(!empty($idsgsetor) && !empty($idsgarea) && empty($idsgdepartamento)) {
	$strin .=" AND (idobjeto in(".$idsgsetor.") AND tipoobjeto = 'sgsetor') OR (idobjeto in(".$idsgarea.") AND tipoobjeto = 'sgarea')";
} elseif(!empty($idsgsetor) && empty($idsgarea) && !empty($idsgdepartamento)) {
	$strin .=" AND (idobjeto in(".$idsgsetor.") AND tipoobjeto = 'sgsetor') OR (idobjeto in(".$idsgdepartamento.") AND tipoobjeto = 'sgdepartamento')";
} elseif(empty($idsgsetor) && !empty($idsgarea) && !empty($idsgdepartamento)) {
	$strin .=" AND (idobjeto in(".$idsgarea.") AND tipoobjeto = 'sgarea') OR (idobjeto in(".$idsgdepartamento.") AND tipoobjeto = 'sgdepartamento')";
} elseif(!empty($idsgsetor) && !empty($idsgarea) && !empty($idsgdepartamento)) {
	$strin .=" AND (idobjeto in(".$idsgarea.") AND tipoobjeto = 'sgarea') OR (idobjeto in(".$idsgsetor.") AND tipoobjeto = 'sgsetor') OR (idobjeto in(".$idsgdepartamento.") AND tipoobjeto = 'sgdepartamento')";
}

if($nome){
	$strin.=" AND nome like '%".$nome."%' ";
}
if(!empty($status)){
	$strin.=" AND status='".$rh->getStatus($status)."' ";
}
if(!empty($statusevento)){
	$strin.=" AND statusevento='".$statusevento."' ";
}

if($_GET and !empty($strin) and !empty($data1) and !empty($data2)){
	
	//Retorna a Quantidade de Pessoas (model/recursoshumanos.php)
	$groupBy = "GROUP BY pe.idpessoa";
	$res = $rh->getpessoasPonto($strjoin, $strin, $groupBy);		
	
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
	$sqlfig="select caminho from empresaimagem where tipoimagem = 'HEADERSERVICO' ".getidempresa('idempresa','empresa');
	$resfig = d::b()->query($sqlfig) or die("Erro ao recuperar figura para cabeçalho do relatório: ".mysql_error());
	$figrel=mysqli_fetch_assoc($resfig);
	$figurarelatorio = $figrel["caminho"];
		
	?>
		<div style="display:none; text-align: left; font-size: 9px;" class="n_linhas"><span  id="nlinha"><?=$strs?></span></div>

	<table class="tbrepheader">
		<tr>
			<td><img src="<?=$figurarelatorio?>" width="50%"></td>
			<td></td>
		</tr>
	</table>
	<br>

	<table class="normal">
		<tr class='header'>
			<html>
				<head>
					<title>Relatorio Banco de Horas</title>
				</head>
				<div class="title">Relatorio Banco de Horas</div>
				<br />
				<div style="padding-left: 2%;">Período do Relatório: De <?=$dataevento_1?> Até <?=$dataevento_2?></div>
				<div class="subheader">(<?=$strs?>)</div>
				<table class="normal"  style="width:90%" border="1">
					<tr class="header" style="height:20px;">
						<td width="5%" nowrap>ID Colaborador</td>
						<td width="40%" nowrap>Colaborador</td>
						<td width="40%" nowrap>Setor</td>
						<td width="15%" nowrap>Horas</td>
					</tr>
					<?    
					//(model/recursoshumanos.php)
					$re1 = $rh->getPessoasPonto($strjoin, $strin);		

					//Coloca no Array as pessoas que estão no SELECT
					while($r = mysqli_fetch_assoc($re1))
					{      
						$setor = $rh->getPessoaSetor($r['idpessoa']); 
						//Seta o valor do $idrhtipoevento = 6 na função. Retorna o valor da Hora Extra de cada Funcionário
						$horasExtras = $rh->getHorasExtras(6, $r['idpessoa'], $dataPonto);
						$totalHorasExtras = $totalHorasExtras + $horasExtras['valor'];
						?>
						<tr class="res1">
							<td style="border:1px solid; width:20%;"><a target=_blank href="/?_modulo=funcionario&_acao=u&idpessoa=<?=$r['idpessoa'];?>"><?=$r['idpessoa']?></a></td>
							<td style="border:1px solid; width:40%;"><?=$r['nome']?></td>
							<td style="border:1px solid; width:40%;"><?=$setor['setor']?></td>
							<td style="border:1px solid; width:20%; text-align: right;" ><?if($horasExtras['valor']<0){echo "-" ;}?><?=convertHoras(abs($horasExtras['valor']))?></td>
						</tr>
						<?
					} 
					?>
				</table>
				<table class="normal"  style="width:90%" border="1"> 
					<tr class="res1" style="background-color:#f1f1f1; height:40px; font-weight: bold; ">  
						<td style="border:1px solid; font-size:14px !important" colspan="7	">TOTAL: <span style="float:right"><?if($totalHorasExtras<0){echo "-" ;}?><?=convertHoras(abs($totalHorasExtras));?></span></td>
					</tr>
				</table>
			</html>
		</tr>
	</table>
<? 
} else {
	echo 'Selecione uma Intervalo de Tempo para emitir o Relatório';
}
?>
<style>
.normal {
    border: 1px solid silver;
    border-collapse: collapse;
	margin-left: 2%;
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

.subheader{
	font-size: 10px;
	color: gray;
	padding-left: 2%;
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
</style>