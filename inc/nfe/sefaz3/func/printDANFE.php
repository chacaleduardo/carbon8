<?php
/*	MAF: exemplo de profiler funcional para verificação de falhas de execução: http://queirozf.com/entries/xhprof-php-profiler-full-usage-example
    $XHPROF_ROOT = "/var/www/xhprof";
    include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_lib.php";
    include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_runs.php";
    // start profiling
    xhprof_enable();
*/
include_once("../../../php/functions.php");
	//phpinfo() ;die;
//conectabanco();
$idnotafiscal = $_GET["idnotafiscal"]; //Id da nota fiscal que vem da acao de enviar
// Passe para este script o arquivo da NFe
// Ex. printDANFE.php?nfe=35100258716523000119550000000033453539003003-nfe.xml

require_once('../libs/NFe/DanfeNFePHP.class.php');

$sql="select xmlret,tiponf from nf where idnf=".$idnotafiscal;
$res=d::b()->query($sql) or die("erro ao buscar xml-sql=".$sql);
$row=mysqli_fetch_assoc($res);

//$arq = $_GET['nfe'];
//$arq='./35100406315070000115550010000000180199467603-nfe.xml';
//$arq='./35100258716523000119550000000033453539003003-nfe.xml';
//$arq='./35100459462366000125550010000013490224813007-nfe.xml';
//$arq = './35101158716523000119550010000000011003000000-nfe.xml';

//if ( is_file($arq) ){
   // $docxml = file_get_contents($arq);
   //$docxml= str_replace(array("\r\n","\n"), "", $row["xml"]);
   $docxml =utf8_encode($row["xmlret"]);
	

  // echo($docxml);
   // $danfe = new DanfeNFePHP($docxml, 'P', 'A4','../images/logo.jpg','I','');
   if($row['tiponf'=='C']){
   	$danfe = new DanfeNFePHP($docxml, 'P', 'A4','../images/logo.jpg','I','');
   }else{
    $danfe = new DanfeNFePHP($docxml, 'P', 'A4','../../../../img/logolateral.jpg','I','');
   }
    $id = $danfe->montaDANFE();

	//Para testes de modo String
    //$teste = $danfe->printDANFE('','S');echo $teste;die;

    $teste = $danfe->printDANFE('','I');

	
//}


/*	MAF: exemplo de profiler funcional para verificação de falhas de execução: http://queirozf.com/entries/xhprof-php-profiler-full-usage-example
    //end profiling 
    $xhprof_data = xhprof_disable();
    $xhprof_runs = new XHProfRuns_Default();
    $run_id = $xhprof_runs->save_run($xhprof_data, "foobar");
*/
?>