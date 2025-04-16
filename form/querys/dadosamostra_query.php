<?
require_once(__DIR__."/_iquery.php");

class DadosAmostraQuery implements DefaultQuery{

	public static $table = "dadosamostra";
	public static $pk = "iddadosamostra";

	public static function buscarPorChavePrimaria(){
		return replaceQueryWildCard(self::buscarPorChavePrimariaSQL,[
            'table' => self::$table,
            'pk' => self::$pk
        ]);
	}

	public static function buscarPorIdamostraEObjeto(){
		return "SELECT * FROM dadosamostra where idamostra = ?idamostra? and objeto = '?objeto?'";
	}

	public static function inserir(){
		return "INSERT INTO `laudo`.`dadosamostra`
					(`idempresa`, `idamostra`, `objeto`, `valorobjeto`, `criadoem`, `criadopor`, `alteradoem`, `alteradopor`)
				VALUES
					('?idempresa?', ?idamostra?, '?objeto?', '?valorobjeto?', now(), '?usuario?', now(), '?usuario?')";
	}

}

?>