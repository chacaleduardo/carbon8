<?

require_once(__DIR__."/_iquery.php");

class EventoChecklistItemQuery implements DefaultQuery
{
    public static $table = 'eventochecklistitem';
    public static $pk = 'ideventochecklistitem';

    public static function buscarPorChavePrimaria(){
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL,[
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }

    public static function inserirEventoChecklistItemQuery()
    {
        return "INSERT INTO eventochecklistitem (ideventochecklist, idempresa, titulo, checked, criadopor, criadoem, alteradopor, alteradoem)
                values(?ideventochecklist?, ?idempresa?, '?titulo?', '?checked?', '?criadopor?', ?criadoem?, '?alteradopor?', ?alteradoem?)";
    }

    public static function removerEventoChecklistItemPorChavePrimaria()
    {
        return "DELETE FROM eventochecklistitem WHERE ideventochecklistitem = ?ideventochecklistitem?";
    }

    public static function buscarCheckListItemPorIdEventoCheckList()
    {
        return "SELECT *
                FROM eventochecklistitem
                WHERE ideventochecklist = ?ideventochecklist?";
    }

    public static function atualizarTituloEventoChecklistItem()
    {
        return "UPDATE eventochecklistitem
                SET titulo = '?titulo?'
                WHERE ideventochecklistitem = ?ideventochecklistitem?";
    }

    public static function atualizarCheckedEventoChecklistItem()
    {
        return "UPDATE eventochecklistitem
                SET checked = '?checked?'
                WHERE ideventochecklistitem = ?ideventochecklistitem?";
    }
}
?>