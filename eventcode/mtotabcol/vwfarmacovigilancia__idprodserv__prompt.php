<?
include_once("../../inc/php/functions.php");
include_once("../../form/controllers/farmacovigilancia_controller.php");
if(!($_SESSION["SESSAO"]["LOGADO"])) die();

//Monta o SQL
$arrayProdserv = FarmacoVigilanciaController::buscarProdutos();


$virg = "";
foreach($arrayProdserv AS $_prodserv){
    if(!empty($_prodserv['descr'])){
        $dados .= $virg.'{"'.$_prodserv['idprodserv'].'":"'.trim(addslashes(str_replace("'", "", str_replace("''", "", $_prodserv['descr'])))).'"}';
        $virg = ",";
    }    
}
echo "[";
echo $dados;
echo "]";