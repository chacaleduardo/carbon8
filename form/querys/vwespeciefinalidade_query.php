<?
require_once(__DIR__."/_iquery.php");

class VwEspecieFinalidadeQuery {

    
    public static function buscarEspecieFinalidadePorEmpresa(){
        return "SELECT idespeciefinalidade,
                        especietipofinalidade
                from vwespeciefinalidade ef
                where ef.idempresa =?idempresa?
                order by especietipofinalidade";
    }
    

    
}?>