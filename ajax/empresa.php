<?
require_once(__DIR__."/../inc/php/functions.php");
// CONTROLLRES
require_once(__DIR__."/../form/controllers/empresa_controller.php");

$action = $_GET['action'];

if($action)
{
    $params = $_GET['params'];

    if(!isset($params['typeParam']))
    {
        $params['typeParam'] = false;
    }

    if(is_array($params) && ($params['typeParam'] != 'array'))
    {
        return $action(implode(',', $params));
    }

    return $action($params);
}

function buscarEmpresasPorModulos($modulo)
{
    $linkModal = "?_modulo=$modulo&_acao=i";

    $arrModulosUsuario = getModsUsr()["MODULOS"];
    $arrRetorno = [];

    foreach($arrModulosUsuario as $idempresa => $modulosEmpresa){
        if(array_key_exists($modulo, $modulosEmpresa) AND $modulosEmpresa[$modulo]['permissao'] == 'w'){
            $arrEmpresas[] = $idempresa;
        }
    }

    if(count($arrEmpresas) > 0)
    {
        $empresas = EmpresaController::buscarEmpresasPorStatusEIdEmpresa(implode(",", $arrEmpresas), 'ATIVO');

        foreach($empresas as $empresa)
        {
            array_push($arrRetorno, [
                'idempresa' => $empresa["idempresa"],
                'sigla' => $empresa['sigla'],
                'corsistema' => $empresa['corsistema'],
                'iconemodal' => "." . preg_replace('/(^\.+)/', '', $empresa['iconemodal'])
            ]);
        }
    }

    echo json_encode($arrRetorno);
}

function buscarRazaoSocial($idempresa){
    echo json_encode( EmpresaController::buscarEmpresaPorIdEmpresa($idempresa));
}
?>