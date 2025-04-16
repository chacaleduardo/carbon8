<?
class _RepColQuery
{
    public static function buscarRepColPorIdRepTabEColuna()
    {
        return "SELECT rc.*, tc.rotcurto as rotulo
                FROM "._DBCARBON."._repcol rc
                JOIN "._DBCARBON."._mtotabcol tc ON(tc.col = rc.col and tc.tab = '?tab?')
                WHERE rc.col = '?coluna?'
                AND rc.idrep = ?idrep?
                ORDER BY rc.col";
    }
}

?>