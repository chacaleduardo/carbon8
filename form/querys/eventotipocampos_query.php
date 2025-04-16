<?

class EventoTipoCamposQuery
{
    public static function inserirNovosCamposPorIdEventoTipo()
    {
        return "INSERT INTO eventotipocampos
                    (col ,rotulo, idempresa, code, ideventotipo, tabela, criadopor, criadoem, alteradopor, alteradoem)
                    (
                        SELECT DISTINCT(mtc.col), mtc.rotpsq, ?idempresa?, mtc.code, ?ideventotipo? , 'eventotipo', '?usuario?' , sysdate(), '?usuario?', sysdate()
                        FROM "._DBCARBON."._mtotabcol mtc
                        WHERE mtc.tab = 'evento'
                        AND mtc.col in(
                            'idempresa', 'motivo', 'evento','descricao','idequipamento','idsgdoc','complemento','idpessoaev',
                            'idpesssoacli','idsgsetor', 'idsgdepartamento', 'idsgarea', 'prazo','inicio','dados1','dados2',
                            'texto1','texto2','data1','data2','datahr1','datahr2','nomecompleto','datainicio','datafim',
                            'horainicio','horafim','textocurto1','textocurto2','textocurto3','textocurto4','textocurto5',
                            'textocurto6','textocurto7','textocurto8','textocurto9','textocurto10','textocurto11','textocurto12','textocurto13','textocurto14','textocurto15','classificacao','checklist','url'
                        )
                        AND NOT EXISTS (
                            SELECT 1 
                            FROM eventotipocampos c
                            WHERE c.ideventotipo=?ideventotipo?
                            AND c.col = mtc.col
                        )
                    )";
    }

    public static function inserirNovoCampoEventoTipoAddPorIdEventoTipoAdd()
    {
        return "INSERT INTO eventotipocampos
                    (col, idempresa, ideventotipoadd, ord, criadopor, criadoem, alteradopor, alteradoem)
                    (
                        SELECT  DISTINCT(mtc.col),
                                ?idempresa?,
                                ?ideventotipoadd?,
                                mtc.ordpos,
                                '?usuario?',
                                sysdate(),
                                '?usuario?',
                                sysdate()
                FROM "._DBCARBON."._mtotabcol mtc 
                WHERE mtc.tab= 'eventoobj'
                AND mtc.col NOT IN('alteradoem','alteradopor','criadopor','criadoem','idempresa','idevento','ideventoobj','idobjeto','objeto','ideventotipoadd')
                AND NOT EXISTS (
                    SELECT 1 
                    FROM eventotipocampos c 
                    WHERE c.ideventotipoadd = ?ideventotipoadd?
                    AND c.col=mtc.col
                )
            )";
    }

    public static function buscarEventoTipoCamposPorIdEventoTipo()
    {
        return "SELECT c.ideventotipocampos,
                        c.code,
                        c.codedeletado,
                        c.codevinculo,
                        c.col,
                        c.rotulo,
                        mtc.datatype,
                        c.visivel,
                        c.ord,
                        c.obrigatorio,
                        mtc.code AS colunacode,
                        c.larguracoluna,
                        c.ideventotipocamposvinculo,
                        (SELECT 'Y' FROM eventotipoadd eva WHERE FIND_IN_SET(c.ideventotipocampos, eva.tipocamposobj) AND eva.status = 'ATIVO') as 'eventotipoadd'
                    FROM eventotipocampos c JOIN carbonnovo._mtotabcol mtc ON (mtc.col = c.col AND mtc.tab = 'evento')
                    WHERE ideventotipo = ?ideventotipo?
                      AND c.col NOT IN ('prazo' , 'inicio')
                      AND tabela = 'eventotipo'
                    ORDER BY ord, eventotipoadd, rotulo, c.col";
    }


    public static function buscarCampoPorIdEventoTipoCampo()
    {
        return "SELECT c.ideventotipocampos,
                        c.code,
                        c.codedeletado,
                        c.codevinculo,
                        c.col,
                        c.rotulo,
                        mtc.datatype,
                        c.visivel,
                        c.ord,
                        c.obrigatorio,
                        mtc.code AS colunacode,
                        c.larguracoluna,
                        c.ideventotipocamposvinculo,
                        (SELECT 'Y' FROM eventotipoadd eva WHERE FIND_IN_SET(c.ideventotipocampos, eva.tipocamposobj) AND eva.status = 'ATIVO') as 'eventotipoadd'
                    FROM eventotipocampos c 
                    JOIN carbonnovo._mtotabcol mtc ON (mtc.col = c.col AND mtc.tab = 'evento')
                    WHERE ideventotipo = ?ideventotipo?
                      AND c.col = '?campo?'
                      AND tabela = 'eventotipo'
                    ORDER BY ord, eventotipoadd, rotulo, c.col";
    }

    public static function buscarEventoTipoCamposPorIdEventoTipoOpcao()
    {
        return "SELECT c.ideventotipocampos, c.rotulo
                FROM eventotipocampos c
                JOIN carbonnovo._mtotabcol mtc ON(mtc.col=c.col AND mtc.tab='evento')
                WHERE ideventotipo = '?ideventotipo?'
                AND c.col NOT in('prazo','inicio')
                AND tabela='eventotipo'
                AND c.col NOT IN ('idsgsetor', 'idsgdoc', 'idsgdepartamento', 'idpessoaev', 'idequipamento', 'idsgdoc', 'url')
                AND mtc.code = ''
                ORDER BY rotulo, c.col;";
    }

    public static function buscarEventoTipoCamposPorIdEventoTipoAdd()
    {
        return "SELECT distinct(mtc.col) AS col,mtc.datatype,mtc.rotpsq,mf.ideventotipocampos,mf.rotulo,mf.ord,mf.visivel,mf.prompt,mf.code
                FROM "._DBCARBON."._mtotabcol mtc 
                JOIN eventotipocampos mf on ( mf.col = mtc.col and mf.ideventotipoadd = ?ideventotipoadd?)
                WHERE mtc.tab= 'eventoobj'
                AND mtc.col not in('alteradoem','alteradopor','criadopor','criadoem','idempresa','idevento','ideventoobj','idobjeto','objeto','ideventotipoadd')
                ORDER by mf.ord,col";
    }

    public static function buscarEventoTipoCamposVisiveisPorIdEventoTipo()
    {
        return "SELECT max(ord)+1 AS nord
                FROM eventotipocampos 
                WHERE ideventotipo = ?ideventotipo?
                AND visivel='Y'";
    }

    public static function buscarCamposPrazoEDataPorIdEventoTipo()
    {
        return "SELECT
                    c.ideventotipocampos,
                    c.col, c.rotulo AS rotulo,
                    c.visivel,
                    c.ord,
                    c2.obrigatorio,
                    c2.ideventotipocampos AS ideventotipocampos2,
                    c2.col AS col2,
                    c2.rotulo AS rotulo2,
                    c2.visivel AS visivel,
                    c2.ord AS ord2,
                    c.larguracoluna,
                    (SELECT 'Y' FROM eventotipoadd eva WHERE FIND_IN_SET(c.ideventotipocampos, eva.tipocamposobj) AND eva.status = 'ATIVO') as 'eventotipoadd'
                FROM eventotipocampos c
                LEFT JOIN eventotipocampos c2 ON(c2.ideventotipo = ?ideventotipo? AND c2.col in('inicio'))
                WHERE c.ideventotipo = ?ideventotipo?
                AND c.col in('prazo')";
    }

    public static function buscarCamposVisiveisPorIdEventoTipo()
    {
        return "SELECT distinct(t.col) as col, 
                       t.codevinculo, 
                       t.rotulo,
                       t.prompt,
                       t.code,
                       t.codedeletado,
                       c.code as tablecode,
                       c.datatype,
                       t.ord,
                       c.dropsql,
                       t.obrigatorio, 
                       t.larguracoluna, 
                       t.ideventotipocamposvinculo,
                       t.ideventotipocampos
                FROM eventotipocampos t
                JOIN "._DBCARBON."._mtotabcol c ON (c.col=t.col and c.tab='evento')
                WHERE t.ideventotipo = ?ideventotipo?
                AND (t.visivel='Y' or t.col in('inicio','prazo'))
                AND t.ord is not null
                AND c.rotpsq is not null 
                ORDER BY t.ord,t.rotulo";
    }

    public static function buscarCamposVisiveisEventoTipoAdd()
    {
        return "SELECT distinct(t.col) as col, 
                       t.codevinculo, 
                       t.rotulo,
                       t.prompt,
                       t.code,
                       c.code as tablecode,
                       c.datatype,
                       t.ord,
                       c.dropsql,
                       t.obrigatorio, 
                       t.larguracoluna, 
                       t.ideventotipocamposvinculo,
                       t.ideventotipocampos
                FROM eventotipocampos t JOIN "._DBCARBON."._mtotabcol c ON (c.col=t.col and c.tab='evento')
                JOIN eventotipoadd ead ON FIND_IN_SET(t.ideventotipocampos, ead.tipocamposobj)
                WHERE t.ideventotipo = ?ideventotipo?
                ORDER BY t.ord, t.rotulo";
    }

    public static function buscarCamposVisiveisPorIdEventoTipoIn()
    {
        return "SELECT distinct(t.col) as col,t.rotulo,t.prompt,t.code,c.code as tablecode,c.datatype,t.ord,c.dropsql,t.obrigatorio, t.larguracoluna
                FROM eventotipocampos t
                JOIN "._DBCARBON."._mtotabcol c ON (c.col=t.col and c.tab='evento')
                WHERE t.ideventotipo = ?ideventotipo?
                AND t.visivel='Y'
                AND t.ord is not null
                AND c.rotpsq is not null 
                AND t.col in(?colunas?)
                ORDER BY t.ord,t.rotulo";
    }

    public static function buscarCamposVisiveisPorIdEventoTipoAdd()
    {
        return "SELECT distinct(t.col) as col,t.rotulo,t.prompt,t.code,c.datatype,t.ord 
                FROM eventotipocampos t 
                JOIN "._DBCARBON."._mtotabcol c ON (c.col=t.col and c.tab= 'eventoobj')
                WHERE t.ideventotipoadd='?ideventotipoadd?'
                AND t.visivel='Y' 
                AND ord is not null
                AND rotulo is not null 
                ORDER BY t.ord,t.rotulo";
    }

    public static function verificarSeEInicioOuPrazo()
    {
        return "SELECT distinct(t.col) as col
                FROM eventotipocampos t 
                JOIN "._DBCARBON."._mtotabcol c ON c.col=t.col and c.tab='evento'
                WHERE t.ideventotipo = ?ideventotipo?
                AND t.col in('inicio','prazo')
                AND t.ord is not null
                AND c.rotpsq is not null";
    }

    public static function buscarCodeIdEventoTipoCampos()
    {
        return "SELECT code, codevinculo, col
                FROM eventotipocampos 
                WHERE ideventotipocampos = '?ideventotipocampos?'";
    }

    public static function buscarColIdEventoTipoCampos()
    {
        return "SELECT col
                FROM eventotipocampos 
                WHERE ideventotipocamposvinculo = '?ideventotipocampos?'
                AND ideventotipo = '?ideventotipo?'";
    }
}

?>