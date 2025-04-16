<?
require_once("../inc/php/functions.php");

$idservicoensaio= $_GET['idservicoensaio'];
$dia= $_GET['dia'];

if(empty($dia)){
    $dia='0';
}


if(!empty($idservicoensaio)){
    $str="  s.idservicoensaio =".$idservicoensaio;
}else{
    die("Erro ao identificar o bioensaio para impressão.");
}

$sqlimp="select ip from tag 
            where varcarbon='IMPRESSORA_CQ'
            ".getidempresa("idempresa","")."
            and ip is not null 
            and status=	'ATIVO'";
$resimp=d::b()->query($sqlimp) or die("Erro ao buscar impressora do CQ: ".mysqli_error(d::b()));
$qtdimp=mysqli_num_rows($resimp);
if($qtdimp<1){die("Não encontrada impressora do CQ em tags var carbon.");}
$rowimp=mysqli_fetch_assoc($resimp);
define("_IP_IMPRESSORA_CQ",$rowimp['ip']);

$sql=" select b.idregistro,b.exercicio,n.nucleo as estudo,b.produto as formulacao,b.partida,i.identificacao,s.dia,dma(s.data) as dmadata      
     from nucleo n,bioensaio b,analise a,identificador i,servicoensaio s
	where  n.idnucleo =b.idnucleo
         and a.idobjeto=b.idbioensaio
         and a.objeto ='bioensaio'
         and s.idobjeto=a.idanalise
         and s.tipoobjeto = 'analise'
	and ".$str."
	and i.tipoobjeto = 'bioensaio'
	and b.idbioensaio=i.idobjeto  order  by i.identificacao";

//die($sql);
$resl=d::b()->query($sql) or die("Erro ao recuperar serviços de impressão: ".mysqli_error(d::b()));

$qrow1=mysqli_num_rows($resl);

$tpag=ceil($qrow1/7);

if($qrow1==0){
	die("Nenhum resultado encontrado para impressão das Etiquetas");
}

	$cabecalho="SIZE 60 mm, 40 mm
SPEED 0
DENSITY 14
DIRECTION 1
REFERENCE 0,0
OFFSET 0 mm
SHIFT 0
CODEPAGE UTF-8
CLS";
               
      
        $l=0;
        $pagina=0;
	while($row=mysql_fetch_assoc($resl)){
	
			$altura="60";
			$strprint=$cabecalho;
			$strprint.='
TEXT 10,20,"2",0,1,1,"B'.str_pad($row['idregistro'],6).'/'.str_pad($row['exercicio'],6).'- '.$row['formulacao'].' '.$row['partida'].'"';
			$strprint.='
REVERSE 6,12,80,30';

				$strprint.='
TEXT 10,60,"2",0,1,1," COLETA: D'.$row['dia'].' - ID:'.$row['identificacao'].'"';
				$strprint.='
TEXT 10,90,"2",0,1,1," DATA: '.$row['dmadata'].'"';
				
			$strprint.='
TEXT 10,150,"2",0,1,1,"B'.str_pad($row['idregistro'],6).'/'.str_pad($row['exercicio'],6).'- '.$row['formulacao'].' '.$row['partida'].'"';
			$strprint.='
TEXT 10,180,"2",0,1,1," COLETA: D'.$row['dia'].' - ID:'.$row['identificacao'].'"';
			$strprint.='
TEXT 10,210,"2",0,1,1," DATA: '.$row['dmadata'].'"';					

			$pagina=1;
			$tpag=1;
			$strprint.='
TEXT 390,300,"",0,1,1," '.$pagina.'/'.$tpag.' "';
		$strprint.="
PRINT 1
		";
			imprimir($strprint);

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
$response = file_get_contents("http://"._IP_IMPRESSORA_CQ."/prt_test.htm?".$QueryString, false, $context);

}
?>

