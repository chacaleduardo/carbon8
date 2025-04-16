<?
//Criado para Listar o Calculo das Horas Extras dos Funcionários
//Lidiane (12/06/2020) - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=325249
header("Content-Type: text/html;  charset=UTF-8",true);

require_once("../inc/php/validaacesso.php");
require_once("../model/recursoshumanos.php");
require_once("../inc/php/folha.php");
baseToGet($_GET["_filtros"]);

//Alterado para between a pedido de Nessi
$idsgsetor			= $_GET["idsgsetor"];
$idsgarea			= $_GET["idsgarea"];
$idpessoa			= $_GET["idpessoa"];
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
		$strin = " AND (dataevento  BETWEEN '" . $data1 ."' and '" .$data2 ."')";
	}else{
		die ("Datas n&atilde;o V&aacute;lidas!");
	}
}

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
} elseif(!empty($idpessoa)) {
	$strin .=" AND idpessoa in(".$idpessoa.")";
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

if($_GET /*and !empty($strin) */and !empty($data1) and !empty($data2))
{
	$re1 = "SELECT * FROM vwrelabsenteismo WHERE 1 $strin";		
	$res1 = d::b()->query($re1) or die("Erro ao recuperar figura para cabeçalho do relatório: ".mysql_error());

	$arrPessoas = array();
	$i = 0;
	$j = 0;
	$pessoa = 1;
	//Coloca no Array as pessoas que estão no SELECT
	while($r = mysqli_fetch_assoc($res1))
	{   
		if(!empty($r['grupo'])){$grupo = $r['grupo'];} else {$grupo = $i; $i++;}
		if(($gruporet != $r['grupo'] && $r['grupo'] != NULL) || ($r['grupo'] == NULL))
		{
			$arrPessoas[$grupo][$j]['idpessoa'] = $r['idpessoa'];
			$arrPessoas[$grupo][$j]['grupo'] = $r['grupo'];
			$arrPessoas[$grupo][$j]['nome'] = $r['nome'];
			$arrPessoas[$grupo][$j]['cargo'] = $r['cargo'];
			$arrPessoas[$grupo][$j]['setor'] = $r['setor'];
			$arrPessoas[$grupo][$j]['status'] = $r['status'];													
			$arrPessoas[$grupo][$j]['evento'] = $r['evento'];		
			$arrPessoas[$grupo][$j]['dataevento'][] = $r['dataevento'];	
			$arrPessoas[$grupo][$j]['horas'] += $r['valor'];	

			//Contar as pessoas
			if($r['idpessoa'] != $idpessoa)
			{
				$arrPessoas[$grupo][$j]['qtd_pessoa'] = $pessoa++;
			}

			$jData = $j;		
			$j++;
		} elseif($gruporet == $r['grupo']){
			$arrPessoas[$grupo][$jData]['dataevento'][] = $r['dataevento'];	
			$arrPessoas[$grupo][$jData]['horas'] += $r['valor'];
		}

		$idpessoa = $r['idpessoa'];	
		$gruporet = $r['grupo']; 		
	} 
	
	$p = 0;
	$grupo = '';
	foreach($arrPessoas AS $pessoas)
	{
		foreach($pessoas AS $_pessoas)
		{
			$dataafastamento = reset($_pessoas['dataevento']);
			$dataretorno = end($_pessoas['dataevento']);
			if(($grupo != $_pessoas['grupo'] && $_pessoas['grupo'] != NULL) || ($_pessoas['grupo'] == NULL))	
			{
				$listaPessoas[$p]['idpessoa'] = $_pessoas['idpessoa'];
				$listaPessoas[$p]['nome'] = $_pessoas['nome'];
				$listaPessoas[$p]['cargo'] = $_pessoas['cargo'];
				$listaPessoas[$p]['setor'] = $_pessoas['setor'];
				$listaPessoas[$p]['evento'] = $_pessoas['evento'];
				$listaPessoas[$p]['grupo'] = $_pessoas['grupo'];
				$listaPessoas[$p]['dataafastamento'] = $dataafastamento;
				$listaPessoas[$p]['dataretorno'] = $dataretorno;
				$listaPessoas[$p]['horas'] = $_pessoas['horas'];

				if($_pessoas['qtd_pessoa'] > 0){
					$qtd_pessoa = $_pessoas['qtd_pessoa'];
				}
				
				$p++;
			}	
			$grupo = $_pessoas['grupo']; 		
		}	
	}

	if($p==1){
		$strs = $p." Registro encontrado";
	}elseif($p>1){
		$strs = $p." Registros encontrados";
	}else{
		$strs = "Nenhum Registro encontrado";
	}

	$_nomeimpressao = "[".md5(date('dmYHis'))."] gerada em [".date(" d/m/Y H:i:s")."]";

	// GVT - 17/04/2020 - Altera logo do relatório, utiliza a imagem cadastrada na empresa.
	$sqlfig="select logosis from empresa where idempresa =".$_SESSION["SESSAO"]["IDEMPRESA"];
	$resfig = d::b()->query($sqlfig) or die("Erro ao recuperar figura para cabeçalho do relatório: ".mysql_error());
	$figrel=mysqli_fetch_assoc($resfig);
	$figurarelatorio = $figrel["logosis"];
	

	//Pegar a quantidade de dias úteis conforme a data passada
    for ($i=0;;$i++) 
    {
        $sqlm = "SELECT func_diauteis(YEAR(DATE_ADD('$data1', INTERVAL ".$i." MONTH)), MONTH(DATE_ADD('$data1', INTERVAL ".$i." MONTH))) AS diautil,
                        CASE WHEN DATE_ADD('$data1', INTERVAL ".$i." MONTH) > '$data2' THEN 'Y' ELSE 'N' END AS maior;";
        $re = d::b()->query($sqlm) or die("Erro ao buscar Datas  sql=".$sqlm);
		$rw = mysqli_fetch_assoc($re);

		if($rw['maior'] == 'Y') {
			break;
		} else {
			$diasUteisTotal += $rw['diautil'];
		}
	}
	?>
	<link href="../inc/css/8rep.css" media="all" rel="stylesheet" type="text/css" />
	
	<div style="display:none; text-align: left; font-size: 9px;" class="n_linhas"><span  id="nlinha"><?=$strs?></span></div>


	<table class="tbrepheader">
		<tr>
			<td rowspan="3" style="width:50px;"><img style="width:100%;" src="<?=$figurarelatorio?>"></td>
			<head>
				<title>Relatorio Absenteísmo</title>
			</head>
			<td><a class="btbr20 no-print" href="<?=$_SERVER['REQUEST_URI']?>&reportexport=csv" target="_blank">Exportar .csv</a></td>
		</tr>
		<tr>
			<td class="subheader"><h2>Relatorio Absenteísmo</h2>
			(<?=$strs?>)</td>
		</tr>
	</table>
	<br>

	<table class="normal">
		<tr class='header'>
			<html>
				<div style="padding-left: 2%;">Período do Relatório: De <?=$dataevento_1?> Até <?=$dataevento_2?></div>
				<table class="normal"  style="width:90%" border="1">
					<tr class="header" style="height:20px;">
						<td width="5%" nowrap>ID Colaborador</td>
						<td width="15%" nowrap>Colaborador</td>
						<td width="20%" nowrap>Cargo</td>
						<td width="20%" nowrap>Setor</td>
						<td width="5%" nowrap>Data Afastamento</td>
						<td width="5%" nowrap>Data Retorno</td>
						<td width="20%" nowrap>Motivo</td>
						<td width="5%" nowrap>Absenteísmo</td>
					</tr>
					<?    	
					if(!empty($conteudoexport)){
						$conteudoexport = ";";
					}
					$conteudoexport .= 'ID Colaborador;Colaborador;Cargo;Setor;Data Afastamento;Data Retorno;Motivo;Absenteísmo;';
					$conteudoexport .= "\n";//QUEBRA DE LINHA NO CONTEUDO CSV
					foreach($listaPessoas AS $_pessoas)
					{
						?>
							<tr class="res1">
								<td style="border:1px solid; width:5%;"><a target=_blank href="/?_modulo=funcionario&_acao=u&idpessoa=<?=$_pessoas['idpessoa'];?>"><?=$_pessoas['idpessoa']?></a></td>
								<td style="border:1px solid; width:15%;"><?=$_pessoas['nome']?></td>
								<td style="border:1px solid; width:20%;"><?=$_pessoas['cargo']?></td>
								<td style="border:1px solid; width:20%;"><?=$_pessoas['setor']?></td>
								<td style="border:1px solid; width:5%;"><?=$_pessoas['dataafastamento']?></td>
								<td style="border:1px solid; width:5%;"><?=$_pessoas['dataretorno']?></td>
								<td style="border:1px solid; width:20%;"><?=$_pessoas['evento']?></td>
								<? $asbUnit = number_format(round((($_pessoas['horas'] / ($diasUteisTotal * 8)) * 100),2),2);?>
								<td style="border:1px solid; width:5%; text-align: right;"><?=$asbUnit?>%</td>
							</tr>
						<?
						$totalGeral += $_pessoas['horas'];
						
						$conteudoexport .= $_pessoas['idpessoa'].';'.$_pessoas['nome'].';'.$_pessoas['cargo'].';'.$_pessoas['setor'].';'.$_pessoas['dataafastamento'].';'.$_pessoas['dataretorno'].';'.$_pessoas['evento'].';'.$asbUnit.'%;';
						$conteudoexport .= "\n";
					}

					?>
				</table>
				<table class="normal"  style="width:90%" border="1"> 
					<tr class="res1" style="background-color:#f1f1f1; height:40px; font-weight: bold; "> 
					<? $absTotal = number_format(round((($totalGeral / ($diasUteisTotal * $qtd_pessoa * 8)) * 100),2),2);?> 
						<td style="border:1px solid; font-size:14px !important" colspan="7	">TOTAL: <span style="float:right"><?=$absTotal?>%</span></td>
					</tr>
				</table>
				<? $conteudoexport .= 'Total;;;;;;;'.$absTotal.'%;';?>
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

<?
if(!empty($_GET["reportexport"])){
	if($_GET["_debug"]!=="true"){
		ob_end_clean();//não envia nada para o browser antes do termino do processamento
	}
	/* Gerar o nome do arquivo para exportar
	 * Substitui qualquer caractere estranho pelo sinal de '_'
	 * Caracteres que NAO SERAO substituidos:
	 *   - qualquer caractere de A a Z (maiusculos)
	 *   - qualquer caracteres de a a z (minusculos)
	 *   - qualquer caractere de 0 a 9
	 *   - e pontos '.'
	 */ 

	$infilename = preg_replace("/[^A-Za-z0-9s.]/", "", 'relatorioabsenteismo');
	//gera o csv
	//LTM - 05-10-2020 - 375916: Alterado pois não estava imprimindo no excel e no libre estava desconfigurando os caracteres especiais. 
	//Devido a correção dos resultados congelados no banco não há necessidade de usar o iconv
	
	header('Content-Encoding: UTF-8');
    header('Content-Type: text/csv; charset=utf-8' );
	header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	if($_GET["_debug"]!=="true"){
		header("Content-Disposition: attachment; filename=".$infilename.".csv");
	}
	header('Content-Transfer-Encoding: binary');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	echo "\xEF\xBB\xBF";
	
	echo $conteudoexport;
	exit();

	
}
?>