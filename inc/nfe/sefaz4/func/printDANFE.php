<?php
/*	MAF: exemplo de profiler funcional para verificaÃ§Ã£o de falhas de execuÃ§Ã£o: http://queirozf.com/entries/xhprof-php-profiler-full-usage-example
    $XHPROF_ROOT = "/var/www/xhprof";
    include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_lib.php";
    include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_runs.php";
    // start profiling
    xhprof_enable();
*/
include_once("../../../php/functions.php");

//mcc 21092018
//remove os caracteres especiais do xml
function string2Slug($str){
	
	//echo $str; 
	

	if (strpos($str, 'Nfe') !== false) {
		//echo 'true'; 
	}
	$str=str_replace("&", "", $str);


	//die('a'); 
	

/*	
	$str = str_replace("ÃƒÆ’Ã‚Å½","I",$str);
	$str = str_replace("ÃƒÆ’Ã‚Â","I",$str);
	$str = str_replace("ÃƒÆ’Ã‚Å’","I",$str);
	$str = str_replace("ÃƒÆ’Ã‚â€¹","E",$str);
	$str = str_replace("ÃƒÆ’Ã‚Å ","E",$str);
	$str = str_replace("ÃƒÆ’Ã‚â€°","E",$str);
	$str = str_replace("ÃƒÆ’Ã‚Ë†","E",$str);
	$str = str_replace("ÃƒÆ’Ã‚â€¡","C",$str);
	$str = str_replace("ÃƒÆ’Ã‚â€ ","A",$str);
	$str = str_replace("ÃƒÆ’Ã‚â€¦","A",$str);
	$str = str_replace("ÃƒÆ’Ã‚â€ž","A",$str);
	$str = str_replace("ÃƒÆ’Ã‚Æ’","A",$str);
	$str = str_replace("ÃƒÆ’Ã‚â€š","A",$str);
	$str = str_replace("ÃƒÆ’Ã‚Â","A",$str);
	$str = str_replace("ÃƒÆ’Ã‚â‚¬","A",$str);
    $str = str_replace("ÃƒÆ’Ã‚Â»","U",$str);
	$str = str_replace("ÃƒÆ’Ã‚Âº","U",$str);
	$str = str_replace("ÃƒÆ’Ã‚Â¹","U",$str);
	$str = str_replace("ÃƒÆ’Ã‚Â¸","O",$str);
	$str = str_replace("ÃƒÆ’Ã‚Â¶","O",$str);
	$str = str_replace("ÃƒÆ’Ã‚Âµ","O",$str);
	$str = str_replace("ÃƒÆ’Ã‚Â´","O",$str);
	$str = str_replace("ÃƒÆ’Ã‚Â³","O",$str);
	$str = str_replace("ÃƒÆ’Ã‚Â²","O",$str);
	$str = str_replace("ÃƒÆ’Ã‚Â°","O",$str);
	$str = str_replace("ÃƒÆ’Ã‚Â¯","I",$str);
	$str = str_replace("ÃƒÆ’Ã‚Â®","I",$str);
	$str = str_replace("ÃƒÆ’Ã‚Â­","I",$str);
	$str = str_replace("ÃƒÆ’Ã‚Â¬","I",$str);
	$str = str_replace("ÃƒÆ’Ã‚Â«","E",$str);
	$str = str_replace("ÃƒÆ’Ã‚Âª","E",$str);
	$str = str_replace("ÃƒÆ’Ã‚Â©","E",$str);
	$str = str_replace("ÃƒÆ’Ã‚Â¨","E",$str);
	$str = str_replace("ÃƒÆ’Ã‚Â§","C",$str);
	$str = str_replace("ÃƒÆ’Ã‚ ","A",$str);
	$str = str_replace("ÃƒÆ’Ã‚Â¥","A",$str);
	$str = str_replace("ÃƒÆ’Ã‚Â¤","A",$str);
	$str = str_replace("ÃƒÆ’Ã‚Â£","A",$str);
	$str = str_replace("ÃƒÆ’Ã‚Â¢","A",$str);
	$str = str_replace("ÃƒÆ’Ã‚Â¡","A",$str);
	$str = str_replace("ÃƒÆ’Ã‚Å“","U",$str);
	$str = str_replace("ÃƒÆ’Ã‚â€º","U",$str);
	$str = str_replace("ÃƒÆ’Ã‚Å¡","U",$str);
	$str = str_replace("ÃƒÆ’Ã‚â„¢","U",$str);
	$str = str_replace("ÃƒÆ’Ã‚Ëœ","O",$str);
	$str = str_replace("ÃƒÆ’Ã‚â€“","O",$str);
	$str = str_replace("ÃƒÆ’Ã‚â€¢","O",$str);
	$str = str_replace("ÃƒÆ’Ã‚â€","O",$str);
	$str = str_replace("ÃƒÆ’Ã‚â€œ","O",$str);
	$str = str_replace("ÃƒÆ’Ã‚","A",$str);
*/
	//$str = str_replace("ÃƒÆ’Ã‚Â","I",$str);
//	die();
  


    return $str;

}

/*	MAF: exemplo de profiler funcional para verificação de falhas de execução: http://queirozf.com/entries/xhprof-php-profiler-full-usage-example
    $XHPROF_ROOT = "/var/www/xhprof";
    include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_lib.php";
    include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_runs.php";
    // start profiling
    xhprof_enable();
*/
include_once("../../../functions.php");
	//phpinfo() ;die;
//conectabanco();
$idnotafiscal = $_GET["idnotafiscal"]; //Id da nota fiscal que vem da acao de enviar
$idnfentradaxml= $_GET["idnfentradaxml"];
// Passe para este script o arquivo da NFe
// Ex. printDANFE.php?nfe=35100258716523000119550000000033453539003003-nfe.xml

require_once('../libs/NFe/DanfeNFePHP.class.php');

if(!empty($idnfentradaxml)){
	$sql="select idempresa,xml as xmlret from nfentradaxml x where   x.idnfentradaxml=".$idnfentradaxml;
}else{
	$sql="select idempresa,xmlret,tiponf from nf where idnf=".$idnotafiscal;
}



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
   $docxml =string2Slug($row["xmlret"]);
//$docxml =$row["xmlret"];
  // echo($docxml);
   // $danfe = new DanfeNFePHP($docxml, 'P', 'A4','../images/logo.jpg','I','');
   
    $sqlimagemdanfe="select caminho from empresaimagem where idempresa = ".$row["idempresa"]." and tipoimagem = 'IMAGEMEMPRESADANFE'";
	$resimagemdanfe=d::b()->query($sqlimagemdanfe) or die("Erro ao buscar figura da danfe da empresa sql=".$sqlimagemdanfe);
	$rowimagemdanfe= mysqli_fetch_assoc($resimagemdanfe);
	if(!empty($rowimagemdanfe["caminho"])){
		$rowimagemdanfe["caminho"] = str_replace("../", "", $rowimagemdanfe["caminho"]);
		$logo = _CARBON_ROOT.$rowimagemdanfe["caminho"];
	}else{
		$logo = '';
	}

	
    $danfe = new DanfeNFePHP($docxml, 'P', 'A4',$logo,'I','');
	
    $id = $danfe->montaDANFE();

	//Para testes de modo String
    //$teste = $danfe->printDANFE('','S');echo $teste;die;

    $teste = $danfe->printDANFE('','I');

	
//}


/*	MAF: exemplo de profiler funcional para verificaÃ§Ã£o de falhas de execuÃ§Ã£o: http://queirozf.com/entries/xhprof-php-profiler-full-usage-example
    //end profiling 
    $xhprof_data = xhprof_disable();
    $xhprof_runs = new XHProfRuns_Default();
    $run_id = $xhprof_runs->save_run($xhprof_data, "foobar");
*/
?>


/*	MAF: exemplo de profiler funcional para verificação de falhas de execução: http://queirozf.com/entries/xhprof-php-profiler-full-usage-example
    //end profiling 
    $xhprof_data = xhprof_disable();
    $xhprof_runs = new XHProfRuns_Default();
    $run_id = $xhprof_runs->save_run($xhprof_data, "foobar");
*/
?>
