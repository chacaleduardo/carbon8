<?
require_once("../inc/php/functions.php");

$idlote= $_GET['idlote']; 
$idunidade= $_GET['idunidade']; 
$idunidadeori=$_GET['idunidadeori'];

if(empty($idunidade) or empty($idlote) or empty($idunidadeori)){
	die("Informções para buscar a unidade insuficientes.");
}
$arrlote= getObjeto('lote', $idlote);
$arrun= getObjeto('unidade', $idunidade);
$arrunori= getObjeto('unidade', $idunidadeori);
$arrprod= getObjeto('prodserv', $arrlote['idprodserv']);

    if($arrlote['converteest']=='Y'){
        if($arrunori['convestoque']=='N'){
            $un=traduzid('unidadevolume','un','descr', $arrlote['unlote']) ;
        }else{
            $un=traduzid('unidadevolume','un','descr', $arrlote['unpadrao']) ;
        }
    }else{
         $un=traduzid('unidadevolume','un','descr', $arrprod['un']) ; 
    }

        
    
   echo($un)     
          
?>


