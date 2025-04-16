<?
require_once(__DIR__.'/_iquery.php');

class EmpresaRodapeEmailQuery{

    public static function buscarRodapePorTipoEnvio(){
        return "SELECT * FROM empresarodapeemail WHERE tipoenvio = '?tipoenvio?' AND idempresa = ?idempresa? ORDER BY idempresarodapeemail asc limit 1";
    }

}