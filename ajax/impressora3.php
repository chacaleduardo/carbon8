<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
require_once("../inc/php/laudo.php");
 
if($_POST){
include_once("../inc/php/cbpost.php");
}
// CF0 - tamanho da fonte
// FO - posicionamento horizontal, posicionamento vertical
// FT - posicionamento horizontal, posicionamento vertical em relação a borda esquerda da impressão
// Fazer teste de layout em http://labelary.com/viewer.html

$partida=$_GET["partida"];
$exercicio=$_GET["exercicio"];
$tipoempressora=$_GET["tipoimpressora"];
$qtdd=$_GET["qtdimp"];
$modulo=$_GET["modulo"];
$idlote=$_GET["idlote"];

$sqlimp="select ip from tag 
            where varcarbon='".$tipoempressora."'
            ".getidempresa('idempresa','')."
            and ip is not null 
            and status=	'ATIVO'";
$resimp=d::b()->query($sqlimp) or die("Erro ao buscar impressora do lotes: ".mysqli_error(d::b()));
$qtdimp1=mysqli_num_rows($resimp);
if($qtdimp1<1){die("Não encontrada impressora dos lotes em tags var carbon.");}
$rowimp=mysqli_fetch_assoc($resimp);
define("_IP_IMPRESSORA_SEMENTES",$rowimp['ip']);
//die($sqlimp);

$tamanho = strlen($partida);
if($tamanho > 5){
	if($tamanho > 9){
		$strprint="^XA^CF0,30"; 
		$strprint.='^FO10,20 
					^BQN,2,3
					^FDQA,https://sislaudo.laudolab.com.br/?_modulo='.$_GET['modulo'].'&_acao=u&idlote='.$_GET['idlote'].'^FS
					^FX';
		$strprint.='^FT440,50^FH\^FD'.$partida.'^FS';
		$strprint.='^FT500,80^FH\^FD'.'/'.$exercicio.'^FS';
		$strprint.='^FT170,50^FH\^FD'.$partida.'^FS';
		$strprint.='^FT230,80^FH\^FD'.'/'.$exercicio.'^FS';
		$strprint.="^PQ1,0,1,Y";
		$strprint.="^XZ";
	}else{
		$strprint="^XA^CF0,30"; 
		$strprint.='^FO10,20 
					^BQN,2,3
					^FDQA,https://sislaudo.laudolab.com.br/?_modulo='.$_GET['modulo'].'&_acao=u&idlote='.$_GET['idlote'].'^FS
					^FX';
		$strprint.='^FT470,50^FH\^FD'.$partida.'^FS';
		$strprint.='^FT500,80^FH\^FD'.'/'.$exercicio.'^FS';
		$strprint.='^FT200,50^FH\^FD'.$partida.'^FS';
		$strprint.='^FT230,80^FH\^FD'.'/'.$exercicio.'^FS';
		$strprint.="^PQ1,0,1,Y";
		$strprint.="^XZ";
	}
}else{
	$strprint="^XA^CF0,40"; 
	// $strprint.='^FO10,20 
	// 				^BQN,2,3
	// 				^FDQA,https://sislaudo.laudolab.com.br/?_modulo='.$_GET['modulo'].'&_acao=u&idlote='.$_GET['idlote'].'^FS
	// 				^FX';
	$strprint.='^FT440,70^FH\^FD'.$partida.'/'.$exercicio.'^FS';
	$strprint.='^FT160,70^FH\^FD'.$partida.'/'.$exercicio.'^FS';
	$strprint.="^PQ1,0,1,Y";
	$strprint.="^XZ";
}
if($qtdd > 0){
	for($j = 0; $j < $qtdd; $j++){
		imprimir($strprint);
	}
}
//imprimir($strprint);
 //   echo   $strprint;
 
function imprimir($strprint){
   // 
   // Abra uma conexão telnet com a impressora e empurre todos os dados para ela.
	try{
		$fp=pfsockopen(_IP_IMPRESSORA_SEMENTES,9100);
		fputs($fp,$strprint);
		fclose($fp);
		echo 'Successfully Printed';
	}
	catch (Exception $e){
		echo 'Caught exception: ',  $e->getMessage(), "n";
	}
}


