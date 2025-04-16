<?
require_once(__DIR__ . "/_iquery.php");

class ResultadoAssinaturaQuery implements DefaultQuery
{

	public static $table = "resultadoassinatura";
	public static $pk = "idresultado";

	
	public static function buscarPorChavePrimaria()
	{
		return replaceQueryWildCard(self::buscarPorChavePrimariaSQL, ['table' => self::$table,'pk' =>  self::$pk]);
	}


	public static function inserirResultadoassinatura(){
		return "INSERT into resultadoassinatura
				(idempresa,idresultado,idpessoa,criadoem)
				values
				(?idempresa?,?idresultado?,?idpessoa?,now())";
	}

	public static function deletarResultadoAssinaturaPorIdresultado(){
		return "DELETE FROM resultadoassinatura WHERE idresultado = ?idresultado?";
	}

	public static function buscarResultadoAssinaturaPorIdresultado(){
		return "SELECT * FROM resultadoassinatura WHERE idresultado = '?idresultado?'";
	}

	public static function deletarResultadoAssinaturaPorIdresultadoComAmostra(){
		return "DELETE a.* 
				FROM resultadoassinatura a
					JOIN resultado r ON (a.idresultado = r.idresultado) 
					JOIN amostra am ON (r.idamostra = am.idamostra)
				WHERE r.idresultado = ?idresultado?";
	}

	public static function deletarResultadoAssinaturaPorIdamostra(){
		return "DELETE a.* from resultadoassinatura a,resultado r,amostra am
				where a.idresultado = r.idresultado 
				and r.idamostra = am.idamostra
				and am.idunidade = 1
				and am.idamostra= ?idamostra?";
	}

}