<?
// Controllers
require_once(__DIR__."/_controller.php");

// Querys
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/frasco_query.php");

class FrascoController extends Controller 
{
    public static $status = [
        'ATIVO' => 'ATIVO',
        'INATIVO' => 'INATIVO'
    ];

    public static function buscarFrascos($toFillSelect = false)
    {
        $frascos = SQL::ini(FrascoQuery::buscarFrascos())::exec();

        if($frascos->error()){
            parent::error(__CLASS__, __FUNCTION__, $frascos->errorMessage());
            return [];
        }

        if($toFillSelect) 
        {
            $arrRetorno = [];
            foreach($frascos->data as $frasco) $arrRetorno[$frasco['idfrasco']] = $frasco['frasco'];

            return $arrRetorno;
        }

        return $frascos->data;
    }
}

?>