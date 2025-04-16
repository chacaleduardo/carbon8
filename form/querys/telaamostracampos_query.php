<?
require_once(__DIR__."/_iquery.php");

class TelaAmostraCamposQuery implements DefaultQuery{

	public static $table = "telaamostracampos";
	public static $pk = "idtelaamostracampos";

	public static function buscarPorChavePrimaria(){
		return replaceQueryWildCard(self::buscarPorChavePrimariaSQL, ["table"=> self::$table,"pk"=> self::$pk]) ;
	}

	public static function buscarTodosOsCamposDeUmaEmpresa(){
		return "SELECT distinct(campo)as campo1,
                        campo
                FROM telaamostracampos
                where idempresa = ?idempresa?
                ORDER BY campo" ;
	}

}

?>
