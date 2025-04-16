<?
require_once($_SERVER['DOCUMENT_ROOT']."/inc/php/functions.php");
include_once($_SERVER['DOCUMENT_ROOT']."/inc/php/permissao.php");

// CONTROLLERS
require_once(__DIR__."/../form/controllers/menurelatorio_controller.php");

// @TODO: validar token

if(empty($_GET["idrep"])){
    echo '{"erro":"Parâmetro inválido"}';
    die;
}

$idrep = $_GET["idrep"];
echo json_encode(getConfRepMenuRelatorio($idrep), true);

function getConfRepMenuRelatorio ( $idRep ) : array {
    $relatorios = MenuRelatorioController::buscarRelatorioPorIdRepEColunaPrimaria($idRep);

    if(!$relatorios){
        return [
            "erro" => "Erro ao recuperar configurações do relatório: ".mysql_error(d::b())
        ];
    }

	$arrRepConf = array();

    foreach($relatorios as $key => $relatorio){
        $nomeColCan = strtolower(retira_acentos($relatorio["col"]));

        foreach($relatorio as $coluna => $valor)
        {
            if(in_array($coluna, ["json","code"]) && strlen(trim($valor))>0){
                $fp = fopen("php://temp/", 'w');
                fputs($fp, $valor);
                rewind($fp);
                ob_start();
                require "data://text/plain;base64,". base64_encode(stream_get_contents($fp));
                $valor = ob_get_clean();
            }

            if($coluna == 'tabde' && $valor)
            {
                $arrRepConf[$nomeColCan][$coluna]=$relatorio['colde'];
                $arrRepConf[$nomeColCan][$coluna]=$relatorio['tabpara'];
                $arrRepConf[$nomeColCan][$coluna]=$relatorio['colpara'];
            }

            $arrRepConf[$nomeColCan][$coluna]=$valor;
        }
    }

    return $arrRepConf;
}
?>