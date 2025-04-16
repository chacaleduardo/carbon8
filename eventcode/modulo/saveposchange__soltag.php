<?
require_once(__DIR__."/../../form/controllers/solmat_controller.php");

$lastid = $_SESSION["_pkid"];


$arrInsProd=array();
foreach($_POST as $k=>$v) {
	if(preg_match("/duplicar_(\d*)_(.*)/", $k, $res)){
		$arrInsProd[$res[1]][$res[2]]=$v;
	}
}
if(!empty($arrInsProd)){
    
    foreach($arrInsProd as $k =>$v){
        if(!empty($v['idprodserv'])){
            $arrayInsertSolmatItem = [
                "idempresa" => cb::idempresa(),
                "idsolmat" => $lastid,
                "qtdc" => $v['qtdc'],
                "idprodserv" => $v['idprodserv'],
                "descr" => $v['descr'],
                "obs" => $v['obs'],
                "un" => $v['un'],
                "usuario" => $_SESSION["SESSAO"]["USUARIO"]
            ];
            $inserindoSolMatItem = SolMatController::inserirSolmatItem($arrayInsertSolmatItem);
        } else
        {
            $arrayInsertSolmatItem = [
                "idempresa" => cb::idempresa(),
                "idsolmat" => $lastid,
                "qtdc" => $v['qtdc'],
                "idprodserv" => 'NULL',
                "descr" => $v['descr'],
                "obs" => $v['obs'],
                "un" => $v['un'],
                "usuario" => $_SESSION["SESSAO"]["USUARIO"]
            ];
            $inserindoSolMatItem = SolMatController::inserirSolmatItem($arrayInsertSolmatItem);
        }
       
    }
}

?>