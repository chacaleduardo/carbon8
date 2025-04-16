<?
require_once(__DIR__."/_controller.php");

// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/prodservformularotulo_query.php");

//Controllers
//require_once(__DIR__."/../controllers/prodservformularotulo_controller.php");

class ProdservFormulaRotuloController extends Controller
{
    public static function buscarPorChavePrimaria($id)
    {
        $prodServFormulaRotulo = SQL::ini(ProdservFormulaRotuloQuery::buscarPorChavePrimaria(), [
            'pkval' => $id
        ])::exec();

        if($prodServFormulaRotulo->error()){
            parent::error(__CLASS__, __FUNCTION__, $prodServFormulaRotulo->errorMessage());
            return [];
        }

        return $prodServFormulaRotulo->data[0];
    }

    public static function buscarFormulaRotulo()
    {
        $rotulos = SQL::ini(ProdservFormulaRotuloQuery::buscarFormulaRotulo())::exec();

        if($rotulos->error()){
            parent::error(__CLASS__, __FUNCTION__, $rotulos->errorMessage());
            return [];
        }

        return $rotulos->data;
    }
}
?>