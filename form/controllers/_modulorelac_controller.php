<?
// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/_modulorelac_query.php");

// CONTROLLERS
require_once(__DIR__."/_controller.php");
require_once(__DIR__."/_mtotabcol_controller.php");

class _ModuloRelacController extends Controller
{
    public static function buscarRegistroPorTabDeColDeTabParaColPara($tabDe, $colDe, $tabPara, $colPara, $pkval)
    {
        $chavePrimariaTabOrigem = _MtoTabColController::buscarChavePrimariaPorTabela($tabDe);

        $registros = SQL::ini(_ModuloRelacQuery::buscarRegistroPorTabDeColDeTabParaColPara(), [
            'tabDe' => $tabDe, 
            'colDe' => $colDe, 
            'tabPara' => $tabPara,
            'colPara' => $colPara,
            'pk' => $chavePrimariaTabOrigem,
            'pkval' => implode(',', $pkval)
        ])::exec();

        if ($registros->error()) {
            parent::error(__CLASS__, __FUNCTION__, $registros->errorMessage());
            return [];
        }

        return $registros->data;
    }
}

?>