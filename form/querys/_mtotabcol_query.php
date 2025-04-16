<?
require_once(__DIR__."/_iquery.php");


class _MtotabcolQuery implements DefaultQuery{
    public static $table = _DBCARBON.'_mtotabcol';
    public static $pk = 'idmtotabcol';

    public static function buscarPorChavePrimaria(){
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL,[
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }
    public static function contarColunasFtsKey(){
        return "SELECT 1 
                FROM "._DBCARBON."._mtotabcol mtc
                WHERE mtc.tab='?tab?'
                    and mtc.ftskey='Y'";
    }

    public static function contarColunasInexistentesNoDB(){
        return "SELECT 1
        FROM "._DBCARBON."._mtotabcol mtc 
        WHERE mtc.tab = '?tab?'
                and not exists (
                        select 1 from information_schema.columns c
                        where c.table_schema = '"._DBAPP."'
                                and c.table_name = mtc.tab
                                and c.column_name = mtc.col
                )";
    }

    public static function buscarChavePrimariaPorTabela()
    {
        return "SELECT col from carbonnovo._mtotabcol m where m.tab = '?tabela?' and primkey = 'Y'";
    }

    public static function buscarPorTabECol()
    {
        return "select * from "._DBCARBON."._mtotabcol where tab ='?tab?' and col='?col?'";
    }

    public static function buscarFiltrosPorIdEtlConfEClausula()
    {
        return "SELECT mf.*,tc.datatype,tc.col as colf,if(length(tc.rotcurto) > 0, tc.rotcurto, tc.col) as rotcurto,tc.dropsql
                from "._DBCARBON."._mtotabcol tc
                left join etlconffiltros mf on( mf.idetlconf = ?idetlconf? and tc.col = mf.col)                         
                where ?clausula? order by  colf";
    }

    public static function buscarTabelas()
    {
        return "select distinct tab from " . _DBCARBON . "._mtotabcol";
    }

    public static function buscarFiltrosPorIdDashCardEClausula()
    {
        return "SELECT 
                    tc.col,if(length(tc.rotcurto) > 0, tc.rotcurto, tc.col) as rot
                from " . _DBCARBON . "._mtotabcol tc 
                LEFT join  " . _DBCARBON . "._modulo f on(tc.tab=f.tab)
                join dashcardfiltros mf on(mf.iddashcard = ?iddashcard? and tc.col = mf.col)                         
                where 1
                ?clausula?
                ORDER BY if(length(tc.rotcurto) > 0, tc.rotcurto, tc.col)";
    }

    public static function buscarMtoTabColPorTabela()
    {
        return "select * from " . _DBCARBON . "._mtotabcol where tab ='?tabela?'";
    }

    public static function buscarFiltrosPorIdDashCardEClausulaDaTabela()
    {
        return "select distinct mf.*,tc.datatype,tc.col as colf,if(length(tc.rotcurto) > 0, tc.rotcurto, tc.col) as rotcurto,tc.dropsql
                from " . _DBCARBON . "._mtotabcol tc
                left join dashcardfiltros mf on( mf.iddashcard = ?iddashcard? and tc.col = mf.col )
                where 1
                ?clausula? 
                order by  colf";
    }
}