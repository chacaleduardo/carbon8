<?
require_once(__DIR__."/_iquery.php");

class EmpresaImagemQuery implements DefaultQuery{

	public static $table = "empresa";
	public static $pk = "idempresa";

	public static function buscarPorChavePrimaria(){
		return replaceQueryWildCard(self::buscarPorChavePrimariaSQL,[
            'table' => self::$table,
            'pk' => self::$pk
        ]);
	}


    public static function buscarCaminhoImagemTipoHeaderProduto (){
        return "SELECT caminho
                FROM empresaimagem
                WHERE tipoimagem = 'HEADERPRODUTO'
                ?idempresa?";
    }

    public static function buscarCaminhoImagemPorTipo (){
        return "SELECT 
                    caminho
                FROM
                    empresaimagem
                WHERE
                    idempresa = ?idempresa?
                    AND tipoimagem = '?tipo?'";
    }

}
