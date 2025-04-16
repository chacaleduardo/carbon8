<?
require_once(__DIR__."/../../form/controllers/evento_controller.php");
require_once(__DIR__ . "/../../form/querys/_iquery.php");
require_once(__DIR__ . "/../../form/querys/eventoobj_query.php");


$lastid = $_SESSION["_pkid"];
$idEvento = $_GET['idevento'];
$idEmpresa = $_GET['_idempresa'] ?? cb::idempresa();
$idEventoAdd = $_GET['idEventoAdd'];

if($idEvento && $idEventoAdd) {
    $arrEventoObj = [
        'idevento' => $idEvento,
        'ideventoadd' => $idEventoAdd,
        'idobjeto' => $lastid,
        'objeto' => 'solmat',
        'idempresa' => $idEmpresa,
        'minimo' => '',
        'maximo' => '',
        'atual' => '',
        'resultado' => '',
        'obs' => '',
        'conclusao' => '',
        'status' => '',
        'criadopor' => $_SESSION["SESSAO"]["USUARIO"],
        'criadoem' => 'now()',
        'datainicio' => 'null',
        'alteradopor' => $_SESSION["SESSAO"]["USUARIO"],
        'alteradoem' => 'now()',
        'datafim' => 'null',
        'horainicio' => 'null',
        'horafim' => 'null',
        'ord' => 'null'
    ];

    $eventoObjInsert = SQL::ini(EventoObjQuery::inserirPorSolmat(), $arrEventoObj)::exec();
    
}

$arrInsProd = array();
foreach($_POST as $k => $v) {
	if(preg_match("/duplicar_(\d*)_(.*)/", $k, $res)){
		$arrInsProd[$res[1]][$res[2]] = $v;
	}
}
if(!empty($arrInsProd))
{    
    foreach($arrInsProd as $k =>$v)
    {
        $idProdserv = empty($v['idprodserv']) ? 'NULL' : $v['idprodserv'];
        $arrayInsertSolmatItem = [
            "idempresa" => cb::idempresa(),
            "idsolmat" => $lastid,
            "qtdc" => $v['qtdc'],
            "idprodserv" => $idProdserv,
            "descr" => $v['descr'],
            "obs" => $v['obs'],
            "un" => $v['un'],
            "usuario" => $_SESSION["SESSAO"]["USUARIO"]
        ];
        SolmatController::inserirSolmatItem($arrayInsertSolmatItem);          
    }
}

?>