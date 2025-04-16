<?
require_once(__DIR__."/../../inc/php/functions.php");
require_once(__DIR__."/_controller.php");

require_once(__DIR__."/../querys/cbenef_query.php");

class CbenefConttrooler extends Controller {
    public static function buscarItensPorIdCbenef($idCbenef) {
        $itens = SQL::ini(CbenefQuery::buscarItensPorIdCbenef(),['idcbenef' => $idCbenef])::exec();

        if($itens->error()){
            parent::error(__CLASS__, __FUNCTION__, $itens->errorMessage());
            return [];
        }
        
        return $itens->data;
    }
}