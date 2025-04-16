<?

require_once(__DIR__."/_iquery.php");

class EventoTipoAddQuery implements defaultQuery
{
    public static $table = 'eventotipoadd';
    public static $pk = 'ideventotipoadd';

    public static function buscarPorChavePrimaria(){
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL,[
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }

    public static function buscarEventoTipoAddPorIdEventoTipo()
    {
        return "SELECT * FROM eventotipoadd WHERE status = 'ATIVO' AND ideventotipo = ?ideventotipo?";
    }

    public static function buscarEventoTipoBlocoPorIdEventoTipo()
    {
        return "SELECT 
                    ideventotipoadd, titulo,
                    CASE
                        WHEN tag = 'Y' THEN 'tag'
                        WHEN sgdoc = 'Y' THEN 'sgdoc'
                        WHEN pessoa = 'Y' THEN 'pessoa'
                        WHEN prodserv = 'Y' THEN 'prodserv'
                        WHEN minievento = 'Y' THEN 'minievento'
                        WHEN tipocampos = 'Y' THEN 'tipocampos'
                        WHEN criasolmat = 'Y' THEN 'criasolmat'
                        ELSE ''
                    END as tipoobjeto
                FROM eventotipoadd 
                WHERE status = 'ATIVO' and ideventotipo = ?ideventotipo?";
    }
}

?>