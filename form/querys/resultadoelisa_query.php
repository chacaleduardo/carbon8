<?
require_once(__DIR__ . "/_iquery.php");

class ResultadoElisaQuery implements DefaultQuery
{

	public static $table = "resultadoelisa";
	public static $pk = "idresultadoelisa";

	
	public static function buscarPorChavePrimaria()
	{
		return replaceQueryWildCard(self::buscarPorChavePrimariaSQL,[
            'table' => self::$table,
            'pk' => self::$pk
        ]);
	}


	public static function buscarResultadosDeArquivoUploadEliza()
	{
		return "SELECT 
			*
		FROM
			resultadoelisa
		WHERE
			idresultado = ?idresultado? AND status = 'A'
		ORDER BY idresultadoelisa
		";
	}


}
