<?
class DashCardFiltrosQuery
{
    public static function buscarTabelaFiltrosDashboard()
    {
        return "SELECT 
                    col, sinal, valor, nowdias, iddashcardfiltros
                FROM dashcardfiltros
                WHERE TRIM(valor) != ''
                AND ((valor = 'null'
                AND (sinal = 'is' OR sinal = 'is not'))
                OR valor != 'null')
                AND valor IS NOT NULL
                AND iddashcard = ?iddashcard?";
    }

    public static function deletarFiltrosPorIdDashCardETabela()
    {
        return "delete f.* 
                from dashcardfiltros f 
                where not exists ( 
                    select 1  from " . _DBCARBON . "._mtotabcol tc 
                    where  tc.tab='?tabela?' and tc.col=f.col and tc.rotcurto is not null and tc.rotcurto!=' ' 
                )
                and f.iddashcard = ?iddashcard?";
    }
    
    public static function inserirObjetosVinculados()
    {
        return "INSERT INTO dashcardfiltros
                (idempresa,iddashcard,col,criadopor,criadoem,alteradopor,alteradoem)
                (
                    select 
                        ?idempresa?,
                        ?iddashcard?,
                        col,
                        '?usuario?',
                        NOW(),
                        '?usuario?',
                        NOW()
                    from " . _DBCARBON . "._mtotabcol tc
                    WHERE tc.tab='?tabela?' 
                    AND tc.rotcurto is not null 
                    AND tc.rotcurto!=' ' 
                    AND not exists (
                        select 1 from dashcardfiltros mf where mf.iddashcard = ?iddashcard? and  tc.col = mf.col
                    )
                )";
    }
}

?>