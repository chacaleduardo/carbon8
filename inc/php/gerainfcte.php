<?php
ini_set("display_errors", "1");
error_reporting(E_ALL);

include_once("functions.php");

//função para comparar se a primeira data e maior que a segunda
function comparadata($dataI, $dataII)
{
	// trabalhando a primeira data
	$I = strtotime($dataI);

	// trabalhando a segunda data
	$II = strtotime($dataII);

	if ($I == $II) {
		$vretorno = "I";
	} elseif ($I > $II) {
		$vretorno = "S";
	} elseif ($II > $I) {
		$vretorno = "N";
	}
	return ($vretorno);
}
/*
 if($_GET["roda"]!='Y'){
die("processo suspenso");
}
*/
$idnf = $_GET["idnf"];

$sql = "select n.idempresa as id,n.xmlret as xml,n.tiponf,n.faticms,n.consumo,n.imobilizado,n.idnf,e.cnpj
				from nf n join empresa e on(e.idempresa=n.idempresa)
				where n.tiponf = 'T'
			 	and n.envionfe = 'CONCLUIDA'
			 	and n.idnf = " . $idnf;
$res = d::b()->query($sql) or die($sql . " erro ao buscar informações do bloco C001" . mysqli_error(d::b()));

$row = mysqli_fetch_assoc($res);

// passar string para UTF-8
$xml = ($row['xml']);

$xml = str_replace('&', '&amp;', $xml);

//Carregar o XML em UTF-8
$doc = new DOMDocument();


// Configura para não exibir erros diretamente
libxml_use_internal_errors(true);


// Tenta carregar o XML
if (!$doc->loadXML($xml)) {



	// Captura erros de libxml
	$errors = libxml_get_errors();
	libxml_clear_errors();

	echo "Erro ao carregar XML:";
	foreach ($errors as $error) {
		echo "<br>Erro em linha {$error->line}, coluna {$error->column}: {$error->message}";
	}
	exit;
}

//inicia lendo as principais tags do xml da nfe
$cteProc = $doc->getElementsByTagName("cteProc")->item(0); // pegar a primeira ocorrencia da tag <nfeProc>
$CTe = $cteProc->getElementsByTagName("CTe")->item(0);
$protCTe = $cteProc->getElementsByTagName("protCTe")->item(0);

$infProt = $protCTe->getElementsByTagName("infProt")->item(0);
$chCTe = $infProt->getElementsByTagName("chCTe")->item(0);
$vchCTe = ($chCTe->textContent);

$infCte = $CTe->getElementsByTagName("infCte")->item(0);

$dest = $infCte->getElementsByTagName("emit")->item(0);
$enderDest = $dest->getElementsByTagName("enderEmit")->item(0);

$ide = $infCte->getElementsByTagName("ide")->item(0);

$nCT = $ide->getElementsByTagName("nCT")->item(0);
$vnCT = ($nCT->textContent);

$serie = $ide->getElementsByTagName("serie")->item(0);
$vserie = ($serie->textContent);

$cMunIni = $ide->getElementsByTagName("cMunIni")->item(0);
$vcMunIni = ($cMunIni->textContent);

$cMunFim = $ide->getElementsByTagName("cMunFim")->item(0);
$vcMunFim = ($cMunFim->textContent);

$UFIni = $ide->getElementsByTagName("UFIni")->item(0);
$vUFIni = ($UFIni->textContent);

$UFFim = $ide->getElementsByTagName("UFFim")->item(0);
$vUFFim = ($UFFim->textContent);

$MUNIni = $ide->getElementsByTagName("xMunIni")->item(0);
$vMUNIni = ($MUNIni->textContent);

$UFIni = $ide->getElementsByTagName("UFIni")->item(0);
$vUFIni = ($UFIni->textContent);

$xMunIni = $vMUNIni . "-" . $vUFIni;

$MUNFim = $ide->getElementsByTagName("xMunFim")->item(0);
$vMUNFim = ($MUNFim->textContent);

$UFFim = $ide->getElementsByTagName("UFFim")->item(0);
$vUFFim = ($UFFim->textContent);

$xMunFim = $vMUNFim . "-" . $vUFFim;


$toma3 = $ide->getElementsByTagName("toma03")->item(0);
if ($toma3 != null) {
	$toma = $toma3->getElementsByTagName("toma")->item(0);
} else {
	$toma3 = $ide->getElementsByTagName("toma3")->item(0);
	if ($toma3 != null) {
		$toma = $toma3->getElementsByTagName("toma")->item(0);
	}
}
if ($toma != null) {
	$vtoma = ($toma->textContent);
} else {
	$vtoma = 0;
}



$rem = $infCte->getElementsByTagName("rem")->item(0);
$remCNPJ = $rem->getElementsByTagName("CNPJ")->item(0);
$vremCNPJ = ($remCNPJ->textContent);
$remNome = $rem->getElementsByTagName("xNome")->item(0);
$vremNome = ($remNome->textContent);
$enderReme = $rem->getElementsByTagName("enderReme")->item(0);
$xMun = $enderReme->getElementsByTagName("xMun")->item(0);
$vxMun = ($xMun->textContent);
$xUF = $enderReme->getElementsByTagName("UF")->item(0);
$vxUF = ($xUF->textContent);

$remMun = $vxMun . "-" . $vxUF;

//emitente
$emitCNPJ = $dest->getElementsByTagName("CNPJ")->item(0);
$vemitCNPJ = ($emitCNPJ->textContent);

$destinatario = $infCte->getElementsByTagName("dest")->item(0);
$destCNPJ = $destinatario->getElementsByTagName("CNPJ")->item(0);
$vdestCNPJ = ($destCNPJ->textContent);


if ($vtoma == 0) { //0-Remetente;   
	$vtomCNPJ = $vremCNPJ;
} elseif ($vtoma == 1) { //1-Expedidor;     
	$vtomCNPJ = $vemitCNPJ;
} elseif ($vtoma == 3) { //3-Destinatário;     
	$vtomCNPJ = $vdestCNPJ;
}


$destNome = $destinatario->getElementsByTagName("xNome")->item(0);
$vdestNome = ($destNome->textContent);
$enderdest = $destinatario->getElementsByTagName("enderDest")->item(0);
$xMundest = $enderdest->getElementsByTagName("xMun")->item(0);
$vxMundest = ($xMundest->textContent);
$xUFdest = $enderdest->getElementsByTagName("UF")->item(0);
$vxUFdest = ($xUFdest->textContent);
$destMun = $vxMundest . "-" . $vxUFdest;


$vPrest = $infCte->getElementsByTagName("vPrest")->item(0);
$vTPrest = $vPrest->getElementsByTagName("vTPrest")->item(0);
$vvTPrest = ($vTPrest->textContent);

$imp = $infCte->getElementsByTagName("imp")->item(0);
$ICMS = $imp->getElementsByTagName("ICMS")->item(0);

$ICMS40 = $ICMS->getElementsByTagName("ICMS40")->item(0);
$ICMSSN = $ICMS->getElementsByTagName("ICMSSN")->item(0);
$ICMS45 = $ICMS->getElementsByTagName("ICMS45")->item(0);
$ICMS10 = $ICMS->getElementsByTagName("ICMS10")->item(0);
$ICMS20 = $ICMS->getElementsByTagName("ICMS20")->item(0);
$ICMS70 = $ICMS->getElementsByTagName("ICMS70")->item(0);
$ICMS00 = $ICMS->getElementsByTagName("ICMS00")->item(0);
$ICMS60 = $ICMS->getElementsByTagName("ICMS60")->item(0);
$ICMSSN500 = $ICMS->getElementsByTagName("ICMSSN500")->item(0);
$ICMSSN101 = $ICMS->getElementsByTagName("ICMSSN101")->item(0);
$ICMSSN102 = $ICMS->getElementsByTagName("ICMSSN102")->item(0);
$ICMSSN103 = $ICMS->getElementsByTagName("ICMSSN103")->item(0);

$ICMSSN201 = $ICMS->getElementsByTagName("ICMSSN201")->item(0);
$ICMSSN202 = $ICMS->getElementsByTagName("ICMSSN202")->item(0);
$ICMSSN203 = $ICMS->getElementsByTagName("ICMSSN203")->item(0);

$ICMSSN300 = $ICMS->getElementsByTagName("ICMSSN300")->item(0);
$ICMSSN400 = $ICMS->getElementsByTagName("ICMSSN400")->item(0);
$ICMSSN900 = $ICMS->getElementsByTagName("ICMSSN900")->item(0);
$ICMSOutraUF = $ICMS->getElementsByTagName("ICMSOutraUF")->item(0);


if ($ICMSSN103 != null) {
	die("erro CONFIGURAR ICMSSN103" . $row['idnf']);
}

if ($ICMSSN201 != null) {
	die("erro CONFIGURAR ICMSSN201" . $row['idnf']);
}

if ($ICMSSN203 != null) {
	die("erro CONFIGURAR ICMSSN203" . $row['idnf']);
}
if ($ICMSSN300 != null) {
	die("erro CONFIGURAR ICMSSN300" . $row['idnf']);
}
if ($ICMSSN400 != null) {
	die("erro CONFIGURAR ICMSSN400" . $row['idnf']);
}
if ($ICMSSN != null) {
	// echo("HE 40\n");
	//Situação Tributária referente ao ICMS CST_ICMS
	$vCST = $ICMSSN->getElementsByTagName("CST")->item(0);
	$vvCST = ($vCST->textContent);
	$vpICMS = 0;
	$vvBCicms = 0;
	$vRedBC = 0;
	$vvICMS = 0;
} elseif ($ICMS40 != null) {
	// echo("HE 40\n");
	//Situação Tributária referente ao ICMS CST_ICMS
	$vCST = $ICMS40->getElementsByTagName("CST")->item(0);
	$vvCST = ($vCST->textContent);
	$vpICMS = 0;
	$vvBCicms = 0;
	$vRedBC = 0;
	$vvICMS = 0;
} elseif ($ICMS45 != null) {
	// echo("HE 40\n");
	//Situação Tributária referente ao ICMS CST_ICMS
	$vCST = $ICMS45->getElementsByTagName("CST")->item(0);
	$vvCST = ($vCST->textContent);
	$vpICMS = 0;
	$vvBCicms = 0;
	$vRedBC = 0;
	$vvICMS = 0;
} elseif ($ICMS20 != null) {

	//icms do item VL_BC_ICMS
	$vBCicms = $ICMS20->getElementsByTagName("vBC")->item(0);
	$vvBCicms = ($vBCicms->textContent);
	$vvBCicms = number_format($vvBCicms, 2, '.', '');

	$vRedBC = $vvProd - $vvBCicms;

	//Aliguota 	ALIQ_ICMS
	$pICMS = $ICMS20->getElementsByTagName("pICMS")->item(0);
	$vpICMS = ($pICMS->textContent);
	$vpICMS = number_format($vpICMS, 2, '.', '');

	// Vlr icms item VL_ICMS
	$vICMS = $ICMS20->getElementsByTagName("vICMS")->item(0);
	$vvICMS = ($vICMS->textContent);
	$vvICMS = number_format($vvICMS, 2, '.', '');

	//Situação Tributária referente ao ICMS CST_ICMS
	$vCST = $ICMS20->getElementsByTagName("CST")->item(0);
	$vvCST = ($vCST->textContent);
	// echo("HE 20\n");
} elseif ($ICMS70 != null) {

	//icms do item VL_BC_ICMS
	$vBCicms = $ICMS70->getElementsByTagName("vBC")->item(0);
	$vvBCicms = ($vBCicms->textContent);
	$vvBCicms = number_format($vvBCicms, 2, '.', '');

	$vRedBC = $vvProd - $vvBCicms;

	//Aliguota 	ALIQ_ICMS
	$pICMS = $ICMS70->getElementsByTagName("pICMS")->item(0);
	$vpICMS = ($pICMS->textContent);
	$vpICMS = number_format($vpICMS, 2, '.', '');

	// Vlr icms item VL_ICMS
	$vICMS = $ICMS70->getElementsByTagName("vICMS")->item(0);
	$vvICMS = ($vICMS->textContent);
	$vvICMS = number_format($vvICMS, 2, '.', '');

	//Situação Tributária referente ao ICMS CST_ICMS
	$vCST = $ICMS70->getElementsByTagName("CST")->item(0);
	$vvCST = ($vCST->textContent);
	// echo("HE 20\n");
} elseif ($ICMS10 != null) {

	//icms do item VL_BC_ICMS
	$vBCicms = $ICMS10->getElementsByTagName("vBC")->item(0);
	$vvBCicms = ($vBCicms->textContent);
	$vvBCicms = number_format($vvBCicms, 2, '.', '');

	$vRedBC = $vvProd - $vvBCicms;

	//Aliguota 	ALIQ_ICMS
	$pICMS = $ICMS10->getElementsByTagName("pICMS")->item(0);
	$vpICMS = ($pICMS->textContent);
	$vpICMS = number_format($vpICMS, 2, '.', '');

	// Vlr icms item VL_ICMS
	$vICMS = $ICMS10->getElementsByTagName("vICMS")->item(0);
	$vvICMS = ($vICMS->textContent);
	$vvICMS = number_format($vvICMS, 2, '.', '');

	//Situação Tributária referente ao ICMS CST_ICMS
	//$vCST=$ICMS10->getElementsByTagName("CST")->item(0);
	//$vvCST =($vCST->textContent);
	$vvCST = '00';
	// echo("HE 20\n");					    
} elseif ($ICMS00 != null) {
	//icms do item VL_BC_ICMS
	$vBCicms = $ICMS00->getElementsByTagName("vBC")->item(0);
	$vvBCicms = ($vBCicms->textContent);
	$vvBCicms = number_format($vvBCicms, 2, '.', '');

	$vRedBC = 0;

	//Aliguota 	ALIQ_ICMS
	$pICMS = $ICMS00->getElementsByTagName("pICMS")->item(0);
	$vpICMS = ($pICMS->textContent);
	$vpICMS = number_format($vpICMS, 2, '.', '');

	// Vlr icms item VL_ICMS
	$vICMS = $ICMS00->getElementsByTagName("vICMS")->item(0);
	$vvICMS = ($vICMS->textContent);
	$vvICMS = number_format($vvICMS, 2, '.', '');

	//Situação Tributária referente ao ICMS CST_ICMS
	$vCST = $ICMS00->getElementsByTagName("CST")->item(0);
	$vvCST = ($vCST->textContent);
} elseif ($ICMS60 != null) {

	//icms do item VL_BC_ICMS
	$vBCicms = $ICMS60->getElementsByTagName("vBCSTRet")->item(0);
	$vvBCicms = ($vBCicms->textContent);
	$vvBCicms = number_format($vvBCicms, 2, '.', '');
	$vRedBC = $vvProd - $vvBCicms;

	// Vlr icms item VL_ICMS
	$vICMS = $ICMS60->getElementsByTagName("vICMSSTRet")->item(0);
	$vvICMS = ($vICMS->textContent);
	$vvICMS = number_format($vvICMS, 2, '.', '');

	//Situação Tributaria referente ao ICMS CST_ICMS
	$vCST = $ICMS60->getElementsByTagName("CST")->item(0);
	$vvCST = ($vCST->textContent);
	// echo("HE 20<BR>");
	//zerar pos este ST nao tem aliq
	$vpICMS = 0;
} elseif ($ICMSSN500 != null) {

	//icms do item VL_BC_ICMS
	$vBCicms = $ICMSSN500->getElementsByTagName("vBCSTRet")->item(0);
	$vvBCicms = ($vBCicms->textContent);
	$vvBCicms = number_format($vvBCicms, 2, '.', '');

	// Vlr icms item VL_ICMS
	$vICMS = $ICMSSN500->getElementsByTagName("vICMSSTRet")->item(0);
	$vvICMS = ($vICMS->textContent);
	$vvICMS = number_format($vvICMS, 2, '.', '');

	//Situação Tributaria referente ao ICMS CST_ICMS
	$vCST = $ICMSSN500->getElementsByTagName("CSOSN")->item(0);
	$vvCST = ($vCST->textContent);
	// echo("HE 20<BR>");
	$vpICMS = 0;
	$vRedBC == 0;
} elseif ($ICMSSN101 != null) {

	$vCST = $ICMSSN101->getElementsByTagName("CSOSN")->item(0);
	$vvCST = ($vCST->textContent);

	$vpICMS = 0;
	$vvBCicms = 0;
	$vRedBC = 0;
	$vvICMS = 0;
} elseif ($ICMSSN102 != null) {

	$vCST = $ICMSSN102->getElementsByTagName("CSOSN")->item(0);
	$vvCST = ($vCST->textContent);

	$vpICMS = 0;
	$vvBCicms = 0;
	$vRedBC = 0;
	$vvICMS = 0;
} elseif ($ICMSSN202 != null) {
	//icms do item VL_BC_ICMS
	$vBCicms = $ICMSSN202->getElementsByTagName("vBCST")->item(0);
	$vvBCicms = ($vBCicms->textContent);
	$vvBCicms = number_format($vvBCicms, 2, '.', '');

	$vRedBC = $vvProd - $vvBCicms;

	//Aliguota 	ALIQ_ICMS
	$pICMS = $ICMSSN202->getElementsByTagName("pICMSST")->item(0);
	$vpICMS = ($pICMS->textContent);
	$vpICMS = number_format($vpICMS, 2, '.', '');

	// Vlr icms item VL_ICMS
	$vICMS = $ICMSSN202->getElementsByTagName("vICMSST")->item(0);
	$vvICMS = ($vICMS->textContent);
	$vvICMS = number_format($vvICMS, 2, '.', '');

	//Situação Tributária referente ao ICMS CST_ICMS
	$vCST = $ICMS00->getElementsByTagName("CSOSN")->item(0);
	$vvCST = ($vCST->textContent);
} elseif ($ICMSSN900 != null) {

	$vCST = $ICMSSN900->getElementsByTagName("CSOSN")->item(0);
	$vvCST = ($vCST->textContent);

	$vpICMS = 0;
	$vvBCicms = 0;
	$vRedBC = 0;
	$vvICMS = 0;
} elseif ($ICMSOutraUF != null) {


	//icms do item VL_BC_ICMS
	$vBCicms = $ICMSOutraUF->getElementsByTagName("vBCOutraUF")->item(0);
	$vvBCicms = ($vBCicms->textContent);
	$vvBCicms = number_format($vvBCicms, 2, '.', '');

	$vRedBC = $vvProd - $vvBCicms;

	//Aliguota 	ALIQ_ICMS
	$pICMS = $ICMSOutraUF->getElementsByTagName("pICMSOutraUF")->item(0);
	$vpICMS = ($pICMS->textContent);
	$vpICMS = number_format($vpICMS, 2, '.', '');

	// Vlr icms item VL_ICMS
	$vICMS = $ICMSOutraUF->getElementsByTagName("vICMSOutraUF")->item(0);
	$vvICMS = ($vICMS->textContent);
	$vvICMS = number_format($vvICMS, 2, '.', '');

	//Situação Tributária referente ao ICMS CST_ICMS
	$vCST = $ICMSOutraUF->getElementsByTagName("CST")->item(0);
	$vvCST = ($vCST->textContent);
}


//COD_PART
$CNPJ = $dest->getElementsByTagName("CNPJ")->item(0);
$vCNPJ = ($CNPJ->textContent); //pegar o valor da tag <CNPJ>
if (empty($CNPJ) or empty($vCNPJ)) {
	//COD_PART
	$CNPJ = $dest->getElementsByTagName("CPF")->item(0);
	$vCPF = ($CNPJ->textContent); //pegar o valor da tag <CPF>
	$vCNPJ = ($CNPJ->textContent); //pegar o valor da tag <CNPJ>
}

$dEmi = $ide->getElementsByTagName("dhEmi")->item(0);
$vdEmi = ($dEmi->textContent); //pegar o valor da tag <chNFe>
//2013-01-14
$dia = substr($vdEmi, 8, 2); // retorna "de"
$mes = substr($vdEmi, 5, 2); // retorna "de"
$ano = substr($vdEmi, 0, 4); // retorna "de"
$vdEmi = $dia . $mes . $ano;

$dhRecbto = $protCTe->getElementsByTagName("dhRecbto")->item(0);
$vdRecbto = ($dhRecbto->textContent); //pegar o valor da tag <chNFe>
//2013-01-14
$diar = substr($vdRecbto, 8, 2); // retorna "de"
$mesr = substr($vdRecbto, 5, 2); // retorna "de"
$anor = substr($vdRecbto, 0, 4); // retorna "de"
$vdRecbto = $diar . $mesr . $anor;

// colocar a data de entrada no mesmo mês do sped caso ela seja menor que o início do sped
$dataxml = $anor . "-" . $mesr . "-" . $diar;
//função para comparar se a primeira data e maior que a segunda
//$retcomparadata=comparadata($this->dtInicio,$dataxml);

/*if($retcomparadata=='S'){
	$vdRecbto=$this->inicio;
}*/
if ($vvCST < 100) {
	$CST_ICMS = "0" . $vvCST;
} else {
	$CST_ICMS = $vvCST;
}

/*    		if($vvCST==20){
	$CST_ICMS="020";
}elseif($vvCST==70){
	$CST_ICMS="070";
}elseif($vvCST==40){
	$CST_ICMS="040";
}elseif($vvCST==60){
	$CST_ICMS="060";
}elseif($vvCST==51){
	$CST_ICMS="051";
}elseif($vvCST==00){
	$CST_ICMS="000";
}else{
	$CST_ICMS=$vvCST;
}

*/

if ($vUFIni == "MG" and $vUFFim == "MG") {
	$vCFOP = '1352';

	//if($vUFFim=="MG"){
	$VL_RED_BC = number_format('0,00', 2, '.', '');
	$vvBCicms = number_format('0,00', 2, '.', '');
	$vvICMS = number_format('0,00', 2, '.', '');
	$vpICMS = number_format('0,00', 2, '.', '');
	/*}else{
		$VL_RED_BC=$vvTPrest-$vvBC;
		$VL_RED_BC =number_format($VL_RED_BC, 2, '.','');
	}*/
} else {
	$vCFOP = '2352';
	//$VL_RED_BC =number_format($VL_RED_BC, 2, '.','');
	//$vvBCicms =number_format($vvBCicms, 2, '.','');
	//$vvICMS =number_format($vvICMS, 2, '.','');
	//$vpICMS =number_format($vpICMS, 2, '.','');
	$VL_RED_BC = $vvTPrest - $vvBC;
	$VL_RED_BC = number_format($VL_RED_BC, 2, '.', '');
}

if (empty($vvICMS)) { // ser for vazio vai ficar 0.00
	$vvBCicms = number_format('0,00', 2, '.', '');
	$vvICMS = number_format('0,00', 2, '.', '');
	$vpICMS = number_format('0,00', 2, '.', '');
}



$vvTPrest = number_format($vvTPrest, 2, '.', '');


///############################

$infCTeNorm = $infCte->getElementsByTagName("infCTeNorm")->item(0);
$qtditem = 0;
if (!empty($infCTeNorm)) {
	$infDoc = $infCTeNorm->getElementsByTagName("infDoc")->item(0);
	//verifica quantos itens existem no xml da notafiscal
	$qtditem  = $infDoc->getElementsByTagName('infNFe')->length;
} else {
	$infCteComp = $infCte->getElementsByTagName("infCteComp")->item(0);
}

if ($qtditem > 0) {

	$vcustop = $vvTPrest / ($qtditem);

	for ($nitem = 0; $nitem < $qtditem;) { // loop chaves notas do CTe


		$infNFe = $infDoc->getElementsByTagName("infNFe")->item($nitem);
		$chave = $infNFe->getElementsByTagName("chave")->item(0);
		$vnchave = ($chave->textContent);
		$vchave = "NFe Chave " . $vnchave;

		if ($vnchave != NULL) {


			$sqlv = "select * from nf where status!='CANCELADO' and idnfe = '" . $vnchave . "' 
				union 
				select * from nf where status!='CANCELADO' and  idnfe = 'NFe" . $vnchave . "'";


			$_resv = d::b()->query($sqlv) or die("Erro ao atualizar custo de envio da venda" . $sqlv);


			$sqld = "delete from objetovinculo where tipoobjetovinc='cte' and idobjetovinc= " . $idnf;
			$resd = d::b()->query($sqld);

			while ($rowv = mysqli_fetch_assoc($_resv)) {

				$ins = new Insert();
				$ins->setTable("objetovinculo");
				$ins->idobjeto = $rowv['idnf'];
				$ins->tipoobjeto = 'nf';
				$ins->idobjetovinc = $idnf;
				$ins->tipoobjetovinc = 'cte';
				$idob = $ins->save();

				$_sql = "update nf set custoenvio = '" . $vcustop . "' where idnf  = " . $rowv['idnf'] . "";
				$_resd = d::b()->query($_sql) or die("Erro ao atualizar custo de envio da venda" . $_sql);
				$_sql = "update nf set idobjetosolipor = " . $rowv['idnf'] . ",tipoobjetosolipor='nf' where idnf  = " . $idnf . "";
				$_resd = d::b()->query($_sql) or die("Erro ao vincular pedido no  cte" . $_sql);
			}
		}
		$nitem++;
	}
} else if ($infCteComp != null) {
	$chave = $infCteComp->getElementsByTagName("chCTe")->item(0);
	$vchave = ($chave->textContent);
	$vchave = "NFe Chave " . $vchave;
} else {
	$vchave = "(Cte não referencia chave NFE)";
}



$sqlsd100 = "select * from spedd100 where idnf = " . $idnf . " and status = 'CORRIGIDO'";
$resd100 = d::b()->query($sqlsd100) or die($sqlsd100 . " erro ao buscar informações do bloco D100" . mysqli_error());
$qtdd100 = mysqli_num_rows($resd100);
if ($qtdd100 < 1) {
	$sqld = "update spedd100 set status='INATIVO' where idnf=" . $idnf;
	$resd = d::b()->query($sqld);

	$sqlid = "insert into spedd100 
	(idempresa,idnf,vcnpj,vnct,vdemi,vdrecbto,vchcte,vvtprest,vvbcicms,vvicms,
	vlredbc,vcmunini,vcmunfim,criadopor,criadoem,alteradopor,alteradoem)
	values
	(" . $_SESSION["SESSAO"]["IDEMPRESA"] . "," . $idnf . ",'" . $vCNPJ . "','" . $vnCT . "','" . $vdEmi . "','" . $vdRecbto . "','" . $vchCTe . "','" . $vvTPrest . "','" . $vvBCicms . "','" . $vvICMS . "',
	'" . $VL_RED_BC . "','" . $vcMunIni . "','" . $vcMunFim . "','" . $_SESSION["SESSAO"]["USUARIO"] . "',now(),'" . $_SESSION["SESSAO"]["USUARIO"] . "',now())";
	$resind = d::b()->query($sqlid) or die("Erro ao inserir sped D100 sql=" . $sqlid);

	$sqld = "update spedd190 set status='INATIVO' where idnf=" . $idnf;
	$resd = d::b()->query($sqld);

	$sqlid9 = "INSERT INTO spedd190
	(idempresa,idnf,csticms,vcfop,vpicms,vvtprest,vvbcicms,vvicms,vlredbc,
	criadopor,criadoem,alteradopor,alteradoem)
	values
	(" . $_SESSION["SESSAO"]["IDEMPRESA"] . "," . $idnf . ",'" . $CST_ICMS . "','" . $vCFOP . "','" . $vpICMS . "','" . $vvTPrest . "','" . $vvBCicms . "','" . $vvICMS . "','" . $VL_RED_BC . "',
	'" . $_SESSION["SESSAO"]["USUARIO"] . "',now(),'" . $_SESSION["SESSAO"]["USUARIO"] . "',now())";
	$resind9 = d::b()->query($sqlid9) or die("Erro ao inserir sped D190 sql=" . $sqlid9);
}

//$vvTPrest =number_format($vvTPrest, 2, '.','');

$sqld = "delete from nfitem where idnf=" . $idnf;
$resd = d::b()->query($sqld) or die("Erro ao retirar itens ja existentes no CTE sql" . $sqld);

if ($row['cnpj'] == $vdestCNPJ) {
	$nome = $vremNome;
} else {
	$nome = $vdestNome;
}

// insere os items no banco
$sqlin = " INSERT INTO  nfitem
(idempresa,idnf,qtd,tiponf, vlritem, total,prodservdescr,obs,cfop,nfe)
VALUES(" . $_SESSION["SESSAO"]["IDEMPRESA"] . "," . $idnf . ",1,'C','" . $vvTPrest . "','" . $vvTPrest . "','" . $nome . "','" . $vchave . "','" . $vCFOP . "','Y')";

//echo($sqlin); //die;
$resin = d::b()->query($sqlin) or die("Erro ao inserir itens sql=" . $sqlin);

$sql = "update nf 
	set nnfe='" . $vnCT . "',
		serie='" . $vserie . "',
		emitcnpj ='" . $vemitCNPJ . "',
	    remcnpj='" . $vremCNPJ . "' ,
	    remnome= '" . $vremNome . "',
	    destcnpj= '" . $vdestCNPJ . "',
	    destnome= '" . $vdestNome . "',
	    cnpjtomador='" . $vtomCNPJ . "',
	    munini='" . $xMunIni . "',
	    munfim='" . $xMunFim . "',
	    remmun= '" . $remMun . "', 
	    destmun='" . $destMun . "',
		prazo='" . $dEmi->textContent . "'
	 where idnf=" . $idnf;
$res = d::b()->query($sql) or die("erro ao atualizar o numero do NNFe");



$idnfe = traduzid("nf", "idnf", "idnfe", $idnf);

if (!empty($idnfe)) {

	$sql = "select * from nfentradaxml x  where tipo='CTE' and chave ='" . $idnfe . "' and idnf is not null and idnf!=" . $idnf;
	$res =  d::b()->query($sql) or die("Falha ao buscar se XML já foi vinculado : " . mysqli_error() . "<p>SQL:" . $sql);
	$qtdb = mysqli_num_rows($res);
	if ($qtdb > 0) {
		$row = mysqli_fetch_assoc($res);
		die("DUPLICADO - Este CTe já foi cadastro no sistema antes., Idnf:" . $row['idnf']);
	}


	$sql = "select * from nfentradaxml x  where tipo='CTE' and chave ='" . $idnfe . "' and idnf is null";
	$res =  d::b()->query($sql) or die("Falha ao buscar XML ja baixado: " . mysqli_error() . "<p>SQL:" . $sql);
	$qtdb = mysqli_num_rows($res);

	if ($qtdb > 0) {
		$row = mysqli_fetch_assoc($res);

		$sqlU = "update nfentradaxml set idnf='" . $idnf . "'where idnfentradaxml= " . $row['idnfentradaxml'];
		$resU =  d::b()->query($sqlU) or die("Falha ao vincular XML do CTe: " . mysqli_error() . "<p>SQL:" . $sqlU);
	}
}

echo ("Carregado com sucesso!!!");
