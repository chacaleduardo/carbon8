<?
require_once(__DIR__ . "/_iquery.php");

class ProdservTipoAlertaQuery implements DefaultQuery
{
	
	public static $table = "prodservtipoalerta";
	public static $pk = "idprodservtipoalerta";
	
	public static function buscarPorChavePrimaria(){
		return replaceQueryWildCard(self::buscarPorChavePrimariaSQL, ['table'=>self::$table,'pk'=>self::$pk]) ;
	}

	
	public static function buscarConfiguracaoAlerta()
	{
		return "SELECT tipoalerta as tipoalerta1, 
					   tipoalerta,
					   idprodservtipoalerta,
					   criadopor,
					   criadoem
		FROM
			prodservtipoalerta
		WHERE
			idprodserv = ?idtipoteste?
		ORDER BY tipoalerta";
	}

	public static function buscarConfiguracaoAgente()
	{
		return "SELECT tipoagente as tipoagente1, 
					   tipoagente,
					   idprodservtipoagente,
					   criadopor,
					   criadoem
		FROM
			prodservtipoagente
		WHERE
			idprodserv = ?idtipoteste?
		ORDER BY tipoagente";
	}

}
