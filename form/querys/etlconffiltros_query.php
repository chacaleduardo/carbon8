<?
class EtlConfFiltrosQuery
{
    public static function inserirFiltrosAdicionadosNaTabela()
    {
        return "INSERT INTO etlconffiltros
                (idempresa,idetlconf,col,criadopor,criadoem,alteradopor,alteradoem)
                (
                    select
                    ?idempresa?,?idetlconf?, col,'?usuario?',NOW(),?usuario?,NOW()
                    from "._DBCARBON."._mtotabcol tc
                    where tc.tab='?tab?' and tc.rotcurto is not null and tc.rotcurto!=' '
                    and not exists (select 1 from etlconffiltros mf where mf.idetlconf = ?idetlconf? and  tc.col = mf.col)
                )";
    }

    public static function deletarFiltrosRemovidosDaTabela()
    {
        return "DELETE f.* 
                FROM etlconffiltros f 
                WHERE NOT EXISTS (
                    select 1
                    from "._DBCARBON."._mtotabcol tc 
                    where tc.tab='?tab?'
                    and tc.col=f.col 
                    and tc.rotcurto is not null 
                    and tc.rotcurto!=' ' 
                )
                and f.idetlconf = ?idetlconf?";
    }

    public static function desabilitarTSumPorIdEtlConf()
    {
        return "update etlconffiltros set tsum='N' where idetlconf = ?idetlconf?";
    }

    public static function desabilitarSeparadorPorIdEtlConf()
    {
        return "update etlconffiltros set separador='N' where idetlconf = ?idetlconf?";
    }
}

?>