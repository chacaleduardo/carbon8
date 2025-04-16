<?
require_once(__DIR__ . "/_iquery.php");

class ProdutoFarmacovigilanciaQuery implements DefaultQuery{
    public static $table = 'produtofarmacovigilancia';
    public static $pk = 'idprodutofarmacovigilancia';

    public static function buscarPorChavePrimaria()
    {
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL, [
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }  

    public static function buscarProdutosPorIdFarmacovigilancia()
    {
       return "SELECT * FROM produtofarmacovigilancia WHERE idfarmacovigilancia = ?idfarmacovigilancia?";
    }

}