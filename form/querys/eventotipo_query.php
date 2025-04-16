<?

require_once(__DIR__."/_iquery.php");

class EventoTipoQuery implements DefaultQuery {
	public static $table = "eventotipo";
	public static $pk = "ideventotipo";

	public static function buscarPorChavePrimaria()
    {
		return replaceQueryWildCard(self::buscarPorChavePrimariaSQL, ['table' => self::$table, 'pk' => self::$pk]);
	}

    public static function buscarEventoTipoPorIdTagTipo () {
        return "SELECT
                et.ideventotipo,
                et.eventotipo
            FROM eventotipo et
                JOIN eventotipoadd eta ON(eta.ideventotipo = et.ideventotipo)
            WHERE eta.tag = 'Y'
                AND eta.status = 'ATIVO'
                AND FIND_IN_SET(?idtagtipo?, eta.tagtipoobj)
            ORDER BY et.eventotipo";
    }

    public static function buscarTokenInicialEUltimaVersaoDoEventoTipoPorIdEventoTipo()
    {
        return "SELECT
                    REPLACE(REPLACE(JSON_KEYS(JSON_EXTRACT(jconfig,'$[last]')),'[',''),']','') AS versao,
                    getEventoStatusConfig(et.ideventotipo,CONCAT('',REPLACE(REPLACE(JSON_KEYS(JSON_EXTRACT(jconfig,'$[last]')),'[',''),']','') ,''),null, true,'token') AS tokeninicial
                FROM eventotipo et
                WHERE et.ideventotipo = ?ideventotipo?";
    }

    public static function atualizarVersaoDeEventosQueEstejamNoStatusInicial()
    {
        return "UPDATE evento 
                SET versao = '?versao?'
                WHERE status = '?tokeninicial?'
                AND ideventotipo = '?ideventotipo?'";
    }

    public static function buscarEventoTipoPorIdPessoaClausulaEGetIdEmpresa()
    {
        return "SELECT et.ideventotipo as id, et.eventotipo as tipo,et.idempresa
                FROM eventotipo et
                JOIN (
                    SELECT e.idevento, e.ideventotipo
                    FROM evento e
                    WHERE EXISTS(
                        SELECT 1
                        FROM eventotipo
                        WHERE e.ideventotipo = ideventotipo
                        AND status = 'ATIVO'
                        AND calendario = 'Y'
                    )
                    GROUP BY e.idevento
                ) qry ON(qry.ideventotipo = et.ideventotipo)
                WHERE EXISTS (
                    SELECT 1
                    FROM fluxostatuspessoa fp
                    WHERE fp.idobjeto = ?idpessoa?
                    AND fp.tipoobjeto = 'pessoa'
                    AND fp.idmodulo = qry.idevento
                    AND fp.modulo = 'evento'
                )
                ?clausula?
                ?getidempresa?
                AND et.status = 'ATIVO'
                AND et.calendario = 'Y'
                GROUP BY et.ideventotipo
                ORDER BY et.eventotipo ASC";
    }

    public static function buscarEventoTipoPorTipoEIdPessoa()
    {
        return "SELECT *
                FROM (
                    SELECT et.ideventotipo AS id, 
                            et.eventotipo AS tipo,
                            et.eventotitle AS eventotitle,
                            et.idempresa AS idempresa
                        FROM eventotipo et 
                        WHERE et.status = 'ATIVO' 
                        AND (et.?tipo? = 'Y' AND NOT exists 
                                                (SELECT 1 FROM fluxo ms JOIN fluxoobjeto r ON ms.idfluxo = r.idfluxo
                                                WHERE ms.idobjeto = et.ideventotipo AND ms.tipoobjeto = 'ideventotipo' and r.tipo = 'CRIADOR')
                            )
                UNION
                    SELECT et.ideventotipo AS id, 
                            et.eventotipo 	AS tipo,
                            et.eventotitle AS eventotitle,
                            et.idempresa AS idempresa
                        FROM eventotipo et 
                        WHERE et.status = 'ATIVO'
                        AND (et.?tipo? = 'Y' AND exists 
                                                (SELECT 1 FROM fluxo ms JOIN fluxoobjeto r ON ms.idfluxo = r.idfluxo
                                                    WHERE ms.idobjeto = et.ideventotipo AND ms.tipoobjeto = 'ideventotipo' and r.tipo = 'CRIADOR'
                                                    AND r.idobjeto='?idpessoa?' AND r.tipoobjeto='pessoa')
                            )
                UNION
                    SELECT et.ideventotipo AS id, 
                            et.eventotipo 	AS tipo,
                            et.eventotitle AS eventotitle,
                            et.idempresa AS idempresa
                        FROM eventotipo et 
                        WHERE et.status = 'ATIVO'
                        AND (et.?tipo? = 'Y' AND exists 
                                                (SELECT 1 FROM fluxo ms JOIN fluxoobjeto r ON ms.idfluxo = r.idfluxo
                                                    JOIN imgrupopessoa igp on igp.idimgrupo = r.idobjeto AND igp.idpessoa = '?idpessoa?'                                                    
                                                    WHERE ms.idobjeto = et.ideventotipo AND ms.tipoobjeto = 'ideventotipo' and r.tipo = 'CRIADOR' 
                                                    AND r.tipoobjeto='imgrupo')     
                            )
                UNION
                SELECT et.ideventotipo AS id, 
                        et.eventotipo 	AS tipo,
                        et.eventotitle AS eventotitle,
                        et.idempresa AS idempresa
                    FROM eventotipo et 
                    WHERE et.status = 'ATIVO' 
                    AND (et.?tipo? = 'Y' AND exists 
                                            (SELECT 1 FROM fluxo ms JOIN fluxoobjeto r ON ms.idfluxo = r.idfluxo
                                                JOIN empresa e on e.idempresa = r.idobjeto 
                                                JOIN pessoa p on p.idempresa=e.idempresa AND p.idpessoa = '?idpessoa?'                                                    
                                                WHERE ms.idobjeto = et.ideventotipo AND ms.tipoobjeto = 'ideventotipo' and r.tipo = 'CRIADOR' 
                                                AND r.tipoobjeto='empresa')     
                        )
            ) a  ORDER BY tipo asc";
    }

    public static function buscarEventoTipoPorIdEventoTipoEIdEmpresa()
    {
        return "SELECT JSON_EXTRACT(JSON_EXTRACT(JSON_EXTRACT(jconfig,concat('$[*].', REPLACE(REPLACE(JSON_KEYS(JSON_EXTRACT(jconfig,'$[last]')),'[',''),']',''))), '$[0]'), '$.permissoes') as jsonconfig
                FROM eventotipo
                WHERE ideventotipo = ?ideventotipo?
                AND idempresa = ?idempresa?";
    }

    public static function buscarEventoTipoPorIdEmpresa()
    {
        return "SELECT *
                FROM eventotipo 
                WHERE status = 'ATIVO'
                AND idempresa = ?idempresa?
                ORDER BY eventotipo";
    }

    public static function buscarEventoTipoPorIdEvento()
    {
        return "SELECT t.eventotipo 
                FROM eventotipo t 
                WHERE EXISTS (
                    SELECT 1 
                    FROM evento e 
                    WHERE t.ideventotipo = e.ideventotipo 
                    AND e.idevento = ?idevento?
                )";
    }

    public static function buscarEventoTipoFluxoPorModuloEPessoa () {
        return "SELECT s.statustipo AS posicao,
                    ordem,
                    r.idfluxostatuspessoa,
                    er.fluxo,
                    er.idstatus,
                    er.novamensagem,
                    er.idfluxostatus
            FROM eventotipo et 
                JOIN fluxo ms ON ms.idobjeto = et.ideventotipo 
                    AND ms.modulo = 'evento' 
                    AND ms.status = 'ATIVO'
                JOIN fluxostatus er ON er.idfluxo = ms.idfluxo 
                JOIN fluxostatuspessoa r ON r.idfluxostatus = er.idfluxostatus
                LEFT JOIN "._DBCARBON."._status s ON s.idstatus = er.idstatus
            WHERE r.idmodulo = ?idevento?
                AND r.modulo = 'evento'
                AND r.tipoobjeto = 'pessoa'
                AND r.idobjeto = ?idpessoa?";
    }

    public static function buscarEventoTipo() {
        return "SELECT ideventotipo,
                        eventotipo
                from eventotipo
                order by eventotipo";
    }

    public static function buscarCamposTituloEventoComCodePorIdEventoTipo()
    {
        return "SELECT et.ideventotipo, etc.code
                from eventotipo et
                join eventotipocampos etc on(etc.ideventotipo = et.ideventotipo)
                where et.ideventotipo in(?ideventotipo?)
                and col = 'evento'
                and etc.code != ''";
    }
}
?>