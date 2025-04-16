<?
// QUERYS
require_once(__DIR__."/../querys/sgfuncao_query.php");

// CONTROLLERS
require_once(__DIR__."/_controller.php");

class SgfuncaoController extends Controller
{
    public static $status = [
        'ATIVO' => 'Ativo',
        'INATIVO' => 'Inativo'
    ];

    public static function buscarFuncoesDisponiveisParaVinculoPorIdSgcargo($idSgCargo)
    {
        $funcoes = SQL::ini(SgfuncaoQuery::buscarFuncoesDiponiveisParaVinculoPorIdSgcargo(), [
            'idsgcargo' => $idSgCargo
        ])::exec();

        if($funcoes->error()){
            parent::error(__CLASS__, __FUNCTION__, $funcoes->errorMessage());
            return [];
        }

        return $funcoes->data;
    }
}

?>