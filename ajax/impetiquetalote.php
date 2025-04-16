<?
require_once("../inc/php/functions.php");

$idobjeto= $_GET['idobjeto'];
$tipoobjeto=$_GET['tipoobjeto'];
$modulo=$_GET['modulo'];
$qtdimp=$_GET['qtdimp'];

if(empty($idobjeto)){
    die("Erro ao idobjeto não enviado para impressão.");
}

/*
$sqlimp="select ip from tag 
            where varcarbon='IMPRESSORA_LOTES'
            and idempresa=".$_SESSION["SESSAO"]["IDEMPRESA"]."
            and ip is not null 
            and status=	'ATIVO'";
$resimp=d::b()->query($sqlimp) or die("Erro ao buscar impressora do lotes: ".mysqli_error(d::b()));
$qtdimp=mysqli_num_rows($resimp);
if($qtdimp<1){die("Não encontrada impressora dos lotes em tags var carbon.");}
$rowimp=mysqli_fetch_assoc($resimp);
define("_IP_IMPRESSORA_LOTES",$rowimp['ip']);
*/
$sqlimp1="select ip from tag 
            where varcarbon='IMPRESSORA_ALMOXARIFADO'
            ".getidempresa("idempresa","")."
            and ip is not null 
            and status=	'ATIVO'";
$resimp1=d::b()->query($sqlimp1) or die("Erro ao buscar impressora do almoxarifado: ".mysqli_error(d::b()));
$qtdimp1=mysqli_num_rows($resimp1);
//if($qtdimp1<1){die("Não encontrada impressora do almoxarifado em tags var carbon.");}
$rowimp1=mysqli_fetch_assoc($resimp1);
define("_IP_IMPRESSORA_ALMOXARIFADO",$rowimp1['ip']);

$sqlimp1="select ip from tag 
            where varcarbon='IMPRESSORA_INCUBACAO'
            ".getidempresa("idempresa","")."
            and ip is not null 
            and status=	'ATIVO'";
$resimp1=d::b()->query($sqlimp1) or die("Erro ao buscar impressora do almoxarifado: ".mysqli_error(d::b()));
$qtdimp1=mysqli_num_rows($resimp1);
//if($qtdimp1<1){die("Não encontrada impressora do almoxarifado em tags var carbon.");}
$rowimp1=mysqli_fetch_assoc($resimp1);
define("_IP_IMPRESSORA_INCUBACAO",$rowimp1['ip']);


if($tipoobjeto=='tag'){
     $sql="select concat('TAG ',TAG) as descr from tag where idtag=".$idobjeto;
}else{
	if($modulo=='lotealmoxarifado'){
		$sql="select concat(l.spartida,l.npartida,'/',l.exercicio) as descr,p.descr as produto,
			LEFT(p.descr,30) as nomeinicio,LEFT(SUBSTRING(p.descr,31),30) as nomemeio,
            SUBSTRING(p.descr,61) as nomefim,
			dma(l.vencimento) as vencimento
				from lote l ,prodserv p
				where p.idprodserv = l.idprodserv
				and l.idlote=".$idobjeto;
		
	}else{
		$sql="select concat(spartida,npartida,'/',exercicio) as descr from lote where idlote=".$idobjeto;
	}
}
//die($sql);
$resl=d::b()->query($sql) or die("Erro ao buscar informações do lote para impressão: ".mysqli_error(d::b()));

$qrow1=mysqli_num_rows($resl);

$tpag=ceil($qrow1/7);

if($qrow1==0){
	die("Nenhum resultado encontrado para impressão das Etiquetas");
}

	$cabecalho="SIZE 40 mm, 20 mm
SPEED 5
DENSITY 7
DIRECTION 0
REFERENCE 0,0
OFFSET 0 mm
SHIFT 0
CODEPAGE UTF-8
CLS";
               
      
        $l=0;
        $pagina=0;
	while($row=mysql_fetch_assoc($resl)){
	
		if($modulo=='lotealmoxarifado'){
			$altura="60";
			$strprint=$cabecalho;                        

			$strprint.='
TEXT 10,10,"1",0,1,1,"'.retira_acentos($row['nomeinicio']).' "';
			$strprint.='
TEXT 10,30,"1",0,1,1,"'.retira_acentos($row['nomemeio']).' "';	
			$strprint.='
TEXT 10,50,"1",0,1,1,"'.retira_acentos($row['nomefim']).' "';				
                        $strprint.='
TEXT 10,80,"3",0,1,1,"'.retira_acentos($row['descr']).' "';
						 $strprint.='
TEXT 10,120,"2",0,1,1,"V: '.$row['vencimento'].' "';
		                      
		$strprint.="
PRINT 1
		";
		
		if(empty($qtdimp) or $qtdimp<2){
			imprimiralmoxarifado($strprint);
		}else{
			for ($i = 1; $i <= $qtdimp; $i++) {
				imprimiralmoxarifado($strprint);
			}			
		}
		
			
		}else{
				$altura="60";
			$strprint=$cabecalho;                        

			$strprint.='
TEXT 20,20,"3",0,1,1,"'.$row['descr'].' "';
                        $strprint.='
TEXT 20,80,"3",0,1,1,"'.$row['descr'].' "';
		                      
		$strprint.="
PRINT 1
		";
		
			//imprimir($strprint);
			
				if(empty($qtdimp) or $qtdimp<2){
					imprimir($strprint);
				}else{
					for ($i = 1; $i <= $qtdimp; $i++) {
						imprimir($strprint);
					}			
				}
			
			
		}

	}//while($row=mysql_fetch_assoc($res)){
	

function imprimir($strprint){
$data = array('content'=>$strprint,	'Send'=>' Print Test ');	

//print_r($data); //die;

$QueryString= http_build_query($data);
//echo("\n impressao ");
//echo($QueryString); 

// create context
$context = stream_context_create(array(
		'http' => array(
				'method' => 'GET',
				'content' => $QueryString,
		),
));
//Tratar erro quando não encontrar IP
// send request and collect data
$response = file_get_contents("http://"._IP_IMPRESSORA_LOTES."/prt_test.htm?".$QueryString, false, $context);

//
}

function imprimiralmoxarifado($strprint){
$data = array('content'=>$strprint,	'Send'=>' Print Test ');	

//print_r($data); //die;

$QueryString= http_build_query($data);
//echo("\n impressao ");
//echo($QueryString); 

// create context
$context = stream_context_create(array(
		'http' => array(
				'method' => 'GET',
				'content' => $QueryString,
		),
));
//Tratar erro quando não encontrar IP
// send request and collect data

if($_GET["tipo"] == "IMPRESSORA_ALMOXARIFADO"){
	$response = file_get_contents("http://"._IP_IMPRESSORA_ALMOXARIFADO."/prt_test.htm?".$QueryString, false, $context);
}else if ($_GET["tipo"] == "IMPRESSORA_INCUBACAO"){
	$response = file_get_contents("http://"._IP_IMPRESSORA_INCUBACAO."/prt_test.htm?".$QueryString, false, $context);
}


}
?>

