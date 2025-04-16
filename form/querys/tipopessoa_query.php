<?

require_once(__DIR__."/_iquery.php");

class TipoPessoaQuery implements DefaultQuery
{
    public static $table = 'tipopessoa';
    public static $pk = 'idtipopessoa';

    public static function buscarPorChavePrimaria(){
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL,[
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }

    public static function buscarTodosTipoPessoa()
    {
        return "SELECT idtipopessoa,CONCAT(e.sigla, ' - ', t.tipopessoa) AS tipopessoa
                FROM tipopessoa t 
                JOIN empresa e ON e.idempresa = t.idempresa
                WHERE t.status = 'ATIVO'														
                ORDER BY  tipopessoa;";
    }
}

?>