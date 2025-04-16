<?

class TagReservaQuery
{
    /**
     * Tag gerada a partir de uma locacao
     */
    public static function buscarTagOriginal()
    {
        return "SELECT * FROM tagreserva WHERE idobjeto = ?idtag? AND objeto = 'tag'";
    }

    public static function atualizarIdObjetoPeloId()
    {
        return "UPDATE tagreserva SET idobjeto = ?idobjeto? WHERE idtagreserva = ?idtagreserva?";
    }

    public static function buscarPeloIdObjeto()
    {
        return "SELECT tr.*, e.idempresa, e.sigla, t.tag, t.descricao
                FROM tagreserva tr
                LEFT JOIN tag t ON(t.idtag = tr.idtag)
                JOIN empresa e ON(t.idempresa = e.idempresa)
                WHERE tr.idobjeto = ?idobjeto? 
                AND tr.objeto = '?tipoobjeto?'";
    }

    public static function buscarPorId()
    {
        return "SELECT * FROM tagreserva WHERE idtagreserva = ?idtagreserva?";
    }

    public static function buscarPeloIdTag()
    {
        return "SELECT tr.*, t.idempresa
                FROM tagreserva tr
                LEFT JOIN tag t ON(t.idtag = tr.idobjeto AND tr.objeto = 'tag')
                WHERE tr.idtag = ?idtag?";
    }

    public static function atualizarStatusPeloId()
    {
        return "UPDATE tagreserva SET status = '?status?' WHERE idtagreserva = ?idtagreserva?";
    }

    public static function inativarPeloIdTag()
    {
        return "UPDATE tagreserva SET status = 'INATIVO' WHERE idtag = ?idtag? AND idobjeto = ?idobjeto? AND objeto = 'tag'";
    }

    public static function atualizarColunaFimAlocacao()
    {
        return "UPDATE tagreserva SET fim = ?fim? WHERE idtagreserva = ?idtagreserva?";
    }

    public static function inserir()
    {
        return "INSERT INTO tagreserva (
                idtag, idobjeto, objeto, inicio, fim, trava, status,
                criadopor, criadoem, alteradopor, alteradoem
            ) VALUES (
                ?idtag?, ?idobjeto?, '?objeto?', '?inicio?', '?fim?', '?trava?', '?status?',
                '?criadopor?', ?criadoem?, '?alteradopor?', ?alteradoem?
            );";
    }

    public static function removerColuna($coluna, $query)
    {
        return str_replace(["$coluna,", "'?$coluna?',"], '', $query);
    }

    public static function buscarArquivosDaTagLocadaPorIdObjeto()
    {
        return "SELECT a.nome, a.criadoem, a.caminho
                FROM arquivo a
                JOIN tagreserva tr ON (a.idobjeto = tr.idtag AND a.tipoobjeto = 'tag')
                WHERE tr.idobjeto = ?idtag? 
                AND tr.objeto = '?objeto?'";
    }

    public static function buscarTagReservaPorIdObjetoTipoObjeto()
    {
        return "SELECT * 
                FROM tagreserva r 
                WHERE r.idobjeto = ?idobjeto?
                and r.objeto = '?tipoobjeto?'";
    }

    public static function atualizarPrazo()
    {
        return "UPDATE tagreserva 
                SET inicio='?inicio?' 
                    ,fim= '?fim?'
                    ,idtag= ?idtag?
                    ,alteradopor= '?usuario?'
                    ,alteradoem= ?alteradoem?
                WHERE idtagreserva = ?idtagreserva?";
    }

    public static function deletarTagReservaPorIdEvento()
    {
        return "DELETE t.*
                FROM tagreserva t 
                WHERE t.idobjeto = ?idevento?
                AND t.objeto = 'evento'";
    }

    public static function buscarDevicesApartirDaLocacaoDaTag()
    {
        return "SELECT tr.idtag as idtagoriginal, tr.idobjeto as idtaglocada, d.iddevice, dsb.iddevicesensorbloco, dsb.tipo
                FROM tagreserva tr
                JOIN device d ON(d.idtag = tr.idtag)
                LEFT JOIN devicesensor ds ON(d.iddevice = ds.iddevice)
                LEFT JOIN devicesensorbloco dsb ON(ds.iddevicesensor = dsb.iddevicesensor)
                WHERE tr.idobjeto IN(?iddastagslocadas?)
                GROUP BY tr.idobjeto;";
    }

    public static function buscarReservasPorIdTag()
    {
        return "SELECT idtagreserva as idevento,
                        'RESERVA' as tipo,
                        tr.idobjeto,
                        tr.objeto,
                        e.diainteiro,
                        CONCAT(UPPER(p.nomecurto), '<br/>' , UPPER(t.descricao), '<br/>') as evento,
                        DATE(tr.inicio) inicio,
                        time(tr.inicio) iniciohms,
                        DATE(tr.fim) fim,
                        time(tr.fim) fimhms,
                        '' as jsonconfig,
                        '#b08431' as cor
                FROM  tagreserva tr 
                JOIN tag t on(t.idtag=tr.idtag and t.idtag in(?idtag?))		
                JOIN evento e on(e.idevento=tr.idobjeto)
                JOIN pessoa p on p.idpessoa = e.idpessoa
                JOIN fluxostatus fs ON(fs.idfluxostatus = e.idfluxostatus)
                JOIN carbonnovo._status cs ON(cs.idstatus = fs.idstatus)
                WHERE tr.objeto = 'evento'
                AND DATE_FORMAT('?data?','%Y-%m') 
                BETWEEN DATE_FORMAT(DATE_SUB(tr.inicio, INTERVAL 14 DAY),'%Y-%m')
                AND DATE_FORMAT(DATE_ADD(tr.fim, INTERVAL 14 DAY),'%Y-%m')
                AND cs.statustipo != 'CANCELADO'
                UNION
                SELECT 
                    idtagreserva as idevento,
                    'RESERVA' as tipo,
                    'N' as diainteiro,
                    tr.idobjeto,
                    tr.objeto,					
                    concat ('Tag-',t.tag,' - ',UPPER(e.ativ)) as evento,
                    DATE(tr.inicio) inicio,
                    time(tr.inicio) iniciohms,
                    DATE(tr.inicio) fim,
                    DATE_ADD(time(tr.inicio), INTERVAL 30 MINUTE) as fimhms,
                    -- time(tr.fim) fimhms,
                    '' as jsonconfig,
                    '#b08431' as cor
                FROM  tagreserva tr 
                JOIN tag t on(t.idtag=tr.idtag and t.idtag in(?idtag?))		
                JOIN loteativ e on(e.idloteativ=tr.idobjeto)
                WHERE tr.objeto = 'loteativ'
                AND DATE_FORMAT('?data?','%Y-%m')
                BETWEEN DATE_FORMAT(DATE_SUB(tr.inicio, INTERVAL 14 DAY),'%Y-%m') 
                AND DATE_FORMAT(DATE_ADD(tr.fim, INTERVAL 14 DAY),'%Y-%m')";
    }

    public static function verificarReserva()
    {
        return "SELECT 
                    true AS travado
                FROM tagreserva tr 
                JOIN evento e ON(e.idevento = tr.idobjeto AND tr.objeto = 'evento')
                JOIN fluxostatus fs ON(fs.idfluxostatus = e.idfluxostatus)
                JOIN carbonnovo._status cs ON(cs.idstatus = fs.idstatus)
                WHERE idtag = ?idtag?
                    ?regraevento?
                    ?regratrava?
                AND cs.statustipo != 'CANCELADO'
                AND 
                (
                    (
                        if (tr.inicio <= '?inicio?', '?inicio?', tr.inicio) = '?inicio?' AND 
                        if(tr.fim >= '?fim?', '?fim?', tr.fim ) = '?fim?'
                    ) 
                    OR
                    (
                        (tr.inicio > '?inicio?' and tr.inicio < '?fim?') 
                        OR
                        (tr.fim > '?inicio?' and tr.fim < '?fim?')
                    )
                )";
    } 
    
    public static function validarPrazoLocacao()
    {
        return "SELECT 1
                FROM tagreserva tr 
                JOIN tag t ON t.idtag = tr.idobjeto
                WHERE tr.idtag = ?idtag?
                AND tr.status = 'ATIVO'
                AND (t.idempresa = ?idempresa? AND '?datainicio?' BETWEEN DATE(tr.inicio) AND DATE(tr.fim))";
    }

    public static function buscarTagReservaPorIdTagStatusEClausulaWhere()
    {
        return "SELECT tr.idobjeto 
                FROM tagreserva tr 
                JOIN tag t ON t.idtag = tr.idobjeto  
                WHERE tr.idtag = '?idtag?' AND tr.status = '?status?' 
                ?clausula?
                LIMIT 1";
    }

    public static function atualizarReservaSalaLoteFormalizacao()
    {
        return "UPDATE tagreserva 
                   SET inicio = '?inicio?',
                       fim = '?fim?',
                       trava = '?trava?',
                       alteradopor = '?alteradopor?',
                       alteradoem = SYSDATE()
                 WHERE idtagreserva = ?idtagreserva?";
    }
}

?>