<?
require_once(__DIR__."/_iquery.php");

class AmostraCamposQuery implements DefaultQuery{

	public static $table = "amostracampos";
	public static $pk = "idamostracampos";

	public static function buscarPorChavePrimaria(){
		return replaceQueryWildCard(self::buscarPorChavePrimariaSQL, ["table"=> self::$table,"pk"=> self::$pk]) ;
	}

	public static function buscarPorIdunidade(){
		return "SELECT * from amostracampos where idunidade = ?idunidade?";
	}

	public static function buscarPorIdunidadeESubtipoAmostra(){
		return "SELECT * from amostracampos where idunidade = ?idunidade? and idsubtipoamostra = ?idsubtipoamostra? and campo='?inCol?'";
	}

	public static function buscarPorIdEIdempresa(){
		return "SELECT *
				FROM amostracampos t
				where idempresa = ?idempresa?
					and idsubtipoamostra = ?idsubtipoamostra?
				order by idunidade";
	}

}

?>