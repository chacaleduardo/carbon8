<?
require_once(__DIR__."/_iquery.php");


class _ModuloQuery implements DefaultQuery{
    public static $table = _DBCARBON.'_modulo';
    public static $pk = 'idmodulo';

    public static function buscarPorChavePrimaria(){
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL,[
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }

    public static function verificaModVinc(){
        return "SELECT 1 
                FROM "._DBCARBON."._modulofiltros mf 
                WHERE 
                    mf.modulo = '?modulo?'";
    }

    public static function buscarTabModVinc(){
        return "SELECT mv.tab 
                FROM "._DBCARBON."._modulo m 
                    JOIN "._DBCARBON."._modulo mv ON mv.modulo = m.modvinculado 
                WHERE
                    m.modulo = '?modulo?'";
    }

    public static function jsonLpsDisponiveis(){
        return "SELECT concat(e.sigla,' - ',lp.descricao) as sigla,
                        lp.idlp,
                        lp.descricao
                FROM carbonnovo._lp lp
                        JOIN empresa e ON (lp.idempresa = e.idempresa)
                WHERE lp.status = 'ATIVO' 
                        AND e.sigla is not null
                        AND NOT EXISTS (SELECT 1 from "._DBCARBON."._lpmodulo lm WHERE lm.modulo = '?modulo?' AND lm.idlp = lp.idlp)
                ORDER BY e.idempresa";
    }

    public static function jsonLpsDisponiveisPorObjEmpresa(){
        return "SELECT concat(e.sigla,' - ',lp.descricao) as sigla,
                        lp.idlp,
                        lp.descricao
                FROM carbonnovo._lp lp
                        JOIN empresa e ON (lp.idempresa = e.idempresa)
                WHERE lp.status = 'ATIVO' 
                        AND e.sigla is not null
                        AND EXISTS (select 1 from objempresa oe where oe.empresa = lp.idempresa and oe.objeto ='PESSOA' and oe.idobjeto = ?idpessoa?)
                        AND NOT EXISTS (SELECT 1 from "._DBCARBON."._lpmodulo lm WHERE lm.modulo = '?modulo?' AND lm.idlp = lp.idlp)
                ORDER BY e.idempresa";
    }

	public static function buscarModuloTipoLoteViculadoAUnidade(){
        return "SELECT 
            m.modulo
        FROM
            unidadeobjeto o FORCE INDEX (TIPOOBJETOUNIDADE)
                JOIN
            carbonnovo._modulo m ON (m.modulo = o.idobjeto
                AND m.modulotipo = 'lote')
        WHERE
            (o.tipoobjeto = 'modulo'
                AND o.idunidade in (?idUnidadePadrao?))";
    }

	public static function listaTabelasVinculadasAoForm(){
        return "SELECT o.tipoobjeto,
                        o.objeto,
                        o.inseridomanualmente,
                        o.idformobjetos,
                        r.idmodulorelac
                FROM "._DBCARBON."._formobjetos o 
                    LEFT JOIN "._DBCARBON."._modulorelac r ON (r.tabpara IS NULL AND r.modulo=o.modulo AND r.tabde=o.objeto)
                WHERE o.modulo = '?modulo?'
                    AND o.form='?urldestino?'
                    AND o.tipoobjeto IN ('tabela','tabelacbpost')
                ORDER BY o.inseridomanualmente, o.objeto;";
    }
    
	public static function listaAjaxVinculadosAoForm(){
        return "SELECT objeto 
                FROM "._DBCARBON."._formobjetos
                WHERE modulo = '?modulo?'
                AND form='?urldestino?' AND tipoobjeto='ajax'
                ORDER BY objeto;";
    }
    
	public static function insertModuloFiltros(){
        return "INSERT INTO carbonnovo._modulofiltros (modulo, col)
                    SELECT DISTINCT '?modulo?', col FROM carbonnovo._mtotabcol mtc  -- o distinct evita erros provenientes de duplicações na mtotabcol
                    WHERE mtc.tab = '?tab?'
                        AND NOT EXISTS(
                            SELECT 1 FROM carbonnovo._modulofiltros mf2
                            WHERE mf2.modulo = '?modulo?'
                            AND mf2.col = mtc.col)";
    }
    
	public static function montaArrayConfPesquisa(){
        return "SELECT
                    m.idmodulo
                    ,m.tab
                    ,m.chavefts
                    ,mf.idmodulofiltros
                    ,mf.masc
                    ,mtc.col
                    ,mtc.cardinality
                    ,mtc.perfindice
                    ,mtc.prompt
                    ,mtc.datatype
                    ,mf.promptativo
                    ,mf.filtrodata
                    ,mf.visres
                    ,mf.visresapp
                    ,mf.parget
                    ,mf.oculto
                    ,IF(LENGTH(mtc.rotcurto)=0,mtc.col,mtc.rotcurto) AS rotcurto
                    ,mf.entre
                    ,mf.ord
                    ,mf.align
                FROM
                    "._DBCARBON."._modulo m 
                    JOIN "._DBCARBON."._mtotabcol mtc ON (mtc.tab=m.tab)
                    LEFT JOIN "._DBCARBON."._modulofiltros mf ON (mf.modulo = m.modulo AND mf.col = mtc.col)
                WHERE
                    m.modulo = '?moduloReal?'
                    AND EXISTS(
                        SELECT 1 from information_schema.tables it
                        WHERE it.table_name = m.tab
                    )
                ORDER BY mf.visres, mf.ord, IF(LENGTH(mtc.rotcurto)=0,mtc.col,mtc.rotcurto), mtc.ordpos";
    }
    
	public static function jsonTabelasCarbonApp(){
        return "SELECT table_schema AS db,
                        table_name AS tab
                FROM information_schema.tables 
                WHERE table_schema='"._DBAPP."'
                UNION ALL
                SELECT table_schema AS db,
                        table_name AS tab
                FROM information_schema.tables 
                WHERE table_schema='"._DBCARBON."'";
    }
    
	public static function jsonRepDisponiveis(){
        return "SELECT rep,
                        idrep,
                        cssicone
                FROM ?db_rep?._rep r
                WHERE NOT EXISTS(
                    SELECT 1 FROM ?db_modulorep?._modulorep mr WHERE mr.modulo='?modulo?' AND mr.idrep=r.idrep
                )
                ORDER BY rep";
    }
    
	public static function RepsVinculadosAoModulo(){
        return "SELECT r.*,
                        mr.ord,
                        mr.idmodulorep
                FROM ?db_rep?._rep r
                    JOIN ?db_modulorep?._modulorep mr ON (mr.idrep=r.idrep AND mr.modulo='?modulo?')
                where r.status = 'ATIVO'
                ORDER BY mr.ord, r.rep";
    }

	public static function buscarModuloTipo(){
        return "SELECT distinct modulotipo as id,
                        modulotipo
                FROM "._DBCARBON."._modulo
                ORDER BY modulotipo ";
    }

	public static function buscarEmpresasParaVincularAoModulo(){
        return "SELECT  idempresa,
                        empresa
                FROM empresa e
                WHERE status = 'ATIVO'  
                        AND NOT EXISTS (SELECT 1 
                                        FROM objempresa o
                                        WHERE o.empresa = e.idempresa
                                            AND o.objeto = 'modulo'
                                            AND o.idobjeto = ?idmodulo?)
                ORDER BY empresa ";
    }

	public static function buscarUnidadesParaVincularAoModulo(){
        return "SELECT u.idunidade, UPPER(CONCAT(e.empresa,' > ',u.unidade)) AS unidade
                FROM unidade u
                    JOIN empresa e ON(e.idempresa=u.idempresa)
                WHERE u.status = 'ATIVO'
                    AND NOT EXISTS (SELECT 1
                                    FROM unidadeobjeto o 
                                        JOIN unidade uo ON (uo.idunidade = o.idunidade)
                                    WHERE (o.tipoobjeto='modulo' AND o.idobjeto = '?modulo?' AND uo.idempresa = u.idempresa))
                    AND EXISTS (SELECT 1
                                FROM objempresa oe
                                WHERE (oe.empresa = e.idempresa AND oe.objeto='modulo' AND oe.idobjeto = ?idmodulo?)
                    )
                ORDER BY e.empresa, u.unidade";
    }

	public static function buscarEmpresasVinculadasAoModulo(){
        return "SELECT  i.idempresa,
                        i.empresa,
                        o.idobjempresa 
                FROM empresa i
                    JOIN objempresa o ON (i.idempresa = o.empresa AND o.objeto='modulo' AND o.idobjeto = ?idmodulo?)
                WHERE status = 'ATIVO'";
    }

	public static function buscarUnidadesVinculadasAoModulo(){
        return "SELECT UPPER(u.unidade) AS unidade,
                        u.idunidade,
                        UPPER(e.empresa) AS empresa,
                        o.idunidadeobjeto 
                FROM unidade u 
                    JOIN unidadeobjeto o ON (o.idunidade = u.idunidade AND o.tipoobjeto='modulo' AND o.idobjeto = '?idmodulo?')
                    JOIN empresa e ON (e.idempresa = u.idempresa)
                WHERE u.status = 'ATIVO'
                    AND e.idempresa = ?idempresa?
                ORDER BY e.empresa, u.unidade";
    }

	public static function adicionarModVinculados(){
        return "SELECT modulo,
                        concat(rotulomenu,' [',modulo,']')
                FROM "._DBCARBON."._modulo 
                WHERE tipo in ('LINK','LINKHOME','BTINV')
                ORDER BY rotulomenu";
    }

	public static function buscarModpar(){
        return "SELECT modulo,
                        concat(rotulomenu,' [',modulo,']')
                FROM "._DBCARBON."._modulo 
                WHERE ?clausula?";
    }

	public static function buscarModVinculados(){
        return "SELECT modulo,
                        modulopar,
                        rotulomenu,
                        status
                FROM "._DBCARBON."._modulo 
                WHERE modvinculado = '?modulo?'
                ORDER BY status, modulopar, modulo";
    }

	public static function buscarImpressorasVinculadasAoModulo(){
        return "SELECT CONCAT(e.sigla,'-',t.tag,' ',t.descricao) as nome,
                        t.idtag,
                        ov.idobjetovinculo,
                        t.fabricante
                FROM objetovinculo ov 
                JOIN tag t ON (t.idtag = ov.idobjetovinc AND ov.tipoobjetovinc='tag') 
                JOIN empresa e ON (e.idempresa = t.idempresa)
                where ov.tipoobjeto='modulo' AND ov.idobjeto = ?idmodulo?";
    }

	public static function buscarLpsVinculadasAoModulo(){
        return "SELECT gp.idlpgrupo as idlpgrupo,
                        gp.descricao as descgrupo,
                        g.idlpgrupo as idgrupo,
                        g.descricao,
                        e.empresa,
                        e.sigla,
                        l.idlp,
                        lm.idlpmodulo,
                        lm.idlp,
                        lm.modulo,
                        lm.permissao,
                        l.idempresa as idempresa
                FROM carbonnovo._lpmodulo lm
                    JOIN carbonnovo._lp l ON (lm.idlp = l.idlp)
                    JOIN empresa e ON (l.idempresa = e.idempresa)
                    JOIN carbonnovo._lpobjeto o ON o.idlp = l.idlp AND o.tipoobjeto = 'lpgrupo'
                    JOIN carbonnovo._lpgrupo g ON g.idlpgrupo = o.idobjeto
                    JOIN carbonnovo._lpgrupo gp ON gp.idlpgrupo = g.lpgrupopar
                WHERE lm.modulo = '?modulo?' 
                        AND l.status = 'ATIVO' 
                        AND e.status = 'ATIVO' 
                        AND g.status = 'ATIVO'
                        AND gp.status = 'ATIVO'
                ORDER BY gp.descricao, g.descricao, e.idempresa";
    }

	public static function buscarLpsVinculadasAoModuloPorObjEmpresa(){
        return "SELECT gp.idlpgrupo as idlpgrupo,
                        gp.descricao as descgrupo,
                        g.idlpgrupo as idgrupo,
                        g.descricao,
                        e.empresa,
                        e.sigla,
                        l.idlp,
                        lm.idlpmodulo,
                        lm.idlp,
                        lm.modulo,
                        lm.permissao,
                        l.idempresa as idempresa
                FROM carbonnovo._lpmodulo lm
                    JOIN carbonnovo._lp l ON (lm.idlp = l.idlp)
                    JOIN empresa e ON (l.idempresa = e.idempresa)
                    JOIN carbonnovo._lpobjeto o ON o.idlp = l.idlp AND o.tipoobjeto = 'lpgrupo'
                    JOIN carbonnovo._lpgrupo g ON g.idlpgrupo = o.idobjeto
                    JOIN carbonnovo._lpgrupo gp ON gp.idlpgrupo = g.lpgrupopar
                WHERE lm.modulo = '?modulo?' 
                        AND l.status = 'ATIVO' 
                        AND e.status = 'ATIVO' 
                        AND g.status = 'ATIVO'
                        AND gp.status = 'ATIVO'
                        AND EXISTS (select 1 from objempresa oe where oe.empresa = l.idempresa and oe.objeto ='PESSOA' and oe.idobjeto = ?idpessoa?)
                ORDER BY gp.descricao, g.descricao, e.idempresa";
    }
    
    public static function buscarLpsVinculadasAoModuloPorEmpresa(){
        return "SELECT  l.idlp,
                        l.descricao,
                        lm.idlpmodulo,
                        lm.permissao,
                        e.idempresa,
                        e.empresa,
                        e.sigla,
                        lm.modulo
                FROM carbonnovo._lpmodulo lm
                JOIN carbonnovo._lp l ON (lm.idlp = l.idlp AND l.status='ATIVO')
                JOIN empresa e ON (l.idempresa = e.idempresa)
                WHERE lm.modulo = '?modulo?'
                        AND l.status = 'ATIVO'
                        AND e.status = 'ATIVO'
                        AND EXISTS (select 1 from objempresa oe where oe.empresa = l.idempresa and oe.objeto ='PESSOA' and oe.idobjeto = ?idpessoa?)
                ORDER BY e.idempresa, l.descricao";
    }
    
	public static function buscarHostsParaFts(){
        return "SELECT h.hostname,
                        h.descr,
                        f.idftsmodulo,
                        IF(IFNULL(f.modulo,'')>'','checked','') as checked
                FROM "._DBCARBON."._hosts h
                    LEFT JOIN "._DBCARBON."._ftsmodulo f ON (f.hostname=h.hostname AND f.modulo='?modulo?')
                ORDER BY idhosts";
    }
    
	public static function buscarUltimosLogDeFts(){
        return "SELECT idexec,
                        status,
                        log,
                        criadoem
                FROM "._DBCARBON."._ftslogtable
                WHERE tab = '?tab?'
                AND criadoem > date_add(now(), interval -1 day)
                ORDER BY criadoem DESC
                LIMIT 15";
    }

    public static function buscarUnidadesDisponiveisParaShare(){
        return "SELECT u.idunidade, UPPER(CONCAT(u.unidade)) AS unidade
                FROM unidade u
                    JOIN empresa e ON(e.idempresa=u.idempresa)
                WHERE u.status = 'ATIVO'
                    AND EXISTS (SELECT 1
                                FROM objempresa oe
                                WHERE (oe.empresa = e.idempresa AND oe.objeto='modulo' AND oe.idobjeto = ?idmodulo?)
                    )
                ORDER BY e.empresa, u.unidade";
    }

    public static function buscarModuloComChavePrimariaPorModulo()
    {
        return "SELECT chavefts
                FROM carbonnovo._modulo m
                JOIN carbonnovo._mtotabcol mtc ON mtc.tab=m.tab
                WHERE modulo = '?modulo?' AND primkey = 'Y'";
    }

    public static function buscarDashboardsDoModulo()
    {
        return "SELECT iddashcard, sigla, cardtitle , cardtitlesub, tipoobjeto, cron, modulofiltros
                FROM dashcard d
                join empresa e on e.idempresa = d.idempresa
                WHERE modulo='?modulo?' and d.status= 'ATIVO'
                ORDER BY cardtitle asc";
    }

    public static function buscarChaveDoModulo()
    {
        return "SELECT
                    CASE WHEN m.tipo='MODVINC' THEN mv.chavefts ELSE m.chavefts END as chavefts
                FROM "._DBCARBON."._modulo m 
                left join "._DBCARBON."._modulo mv on (mv.modulo=m.modvinculado)
                left join "._DBCARBON."._modulo mpar on (find_in_set(mpar.modulo,m.modulopar))
                ?joinlp?
                WHERE m.modulo = '?modulo?'
                ?where?";
    }

    public static function buscarRestaurarPorIdlp()
    {
        return "SELECT 
                    *
                FROM
                    "._DBCARBON."._lpmodulo
                WHERE
                    modulo = 'restaurar' AND idlp IN (?idlp?)";
    }

    public static function buscarModuloVinculadoPorModulo(){
        return "SELECT 
                m.modvinculado
            FROM
            "   ._DBCARBON."._modulo m
            WHERE
                m.modulo = '?modulo?'
                    AND m.modvinculado <> ''";
    }

    public static function buscarUnidadesPorModuloTipoGetIdEmpresaEIdTipoUnidade()
    {
        return "SELECT m.modulo, u.idunidade 
                FROM carbonnovo._modulo m 
                JOIN unidadeobjeto u ON(m.modulo = u.idobjeto)
                JOIN unidade ui ON(u.idunidade = ui.idunidade)
                WHERE m.modulotipo = '?modulotipo?' 
                ?getidempresa?
                and m.status = 'ATIVO'
                and ui.status = 'ATIVO'
                and u.tipoobjeto = 'modulo'
                and ui.idtipounidade = ?idtipounidade?";
    }

    public static function buscarModulosComUnidadesVinculadasPorGetIdEmpresa()
	{
		return "SELECT if(
					(
						SELECT uo.idobjeto 
						FROM unidadeobjeto uo
						JOIN carbonnovo._modulo m1 ON m1.status = 'ATIVO'
						join unidade u on u.idunidade = uo.idunidade
						AND m1.modulotipo = 'lote'
						AND u.idtipounidade = ?idtipounidade?
						?getidempresa?
						AND m1.modulo = uo.idobjeto
						AND uo.tipoobjeto = 'modulo' limit 1
					) != '' ,
					(
						SELECT uo.idobjeto
						FROM unidadeobjeto uo
						JOIN carbonnovo._modulo m1 ON m1.status = 'ATIVO'
						join unidade u on u.idunidade = uo.idunidade
						AND m1.modulotipo = 'lote'
						AND u.idtipounidade = ?idtipounidade?
						?getidempresa?
						AND m1.modulo = uo.idobjeto
						AND uo.tipoobjeto = 'modulo' limit 1
					), modulo
				) as modulo
				FROM carbonnovo._modulo m
				WHERE m.modulotipo = 'lote'
				AND NOT tipo = 'MODVINC'";
	}

    public static function buscarModuloPorUnidade()
	{
		return "SELECT o.idobjeto
                from unidadeobjeto o 
                    join carbonnovo._modulo m on (m.modulo = o.idobjeto and m.modulotipo = '?modulo?' and m.status = 'ATIVO')                                            
                where (o.tipoobjeto='modulo'                
                    and o.idunidade = ?idunidade?)";
	}

    public static function buscarModuloETabComPK()
	{
		return "SELECT 
                    m.idmodulo,m.modulo,m.rotulomenu,m.tab
                FROM carbonnovo._modulo m,
                        carbonnovo._mtotabcol tc
                where tc.primkey ='Y'
                    and exists (select 1 from carbonnovo._mtotabcol t where t.tab = m.tab and col='alteradoem' )
                    and tc.tab = m.tab
                order by m.modulo";
	}

    public static function buscarModuloETab()
	{
		return "SELECT 
                    m.idmodulo, m.modulo,m.rotulomenu,m.tab
                FROM carbonnovo._modulo m
                order by m.modulo";
	}

    public static function buscarModuloSuperiores()
	{
		return "SELECT modulo as id, modulo as obj from carbonnovo._modulo where tipo = 'DROP' and status = 'ATIVO'
            UNION ALL
                SELECT modulo as id, modulo as obj from carbonnovo._modulo where tipo = 'SNIPPET' and status = 'ATIVO'
            UNION ALL
                SELECT snippet as id, snippet as obj  from carbonnovo._snippet where modulo !='' and status = 'ATIVO'";
	}

    public static function buscarModulosDisponiveisParaVinculoEmUnidades()
    {
        return "SELECT m.idmodulo, e.sigla, m.modulo, m.rotulomenu
                FROM carbonnovo._modulo m
                JOIN objempresa oe ON(oe.idobjeto = m.idmodulo AND oe.objeto = 'modulo')
                JOIN empresa e ON(e.idempresa = oe.idempresa)
                WHERE NOT EXISTS(
                    SELECT 1
                    FROM unidadeobjeto uo
                    WHERE uo.idobjeto = m.modulo 
                    AND uo.tipoobjeto = 'modulo'
                    AND uo.idunidade = ?idunidade?
                )
                AND e.idempresa = ?idempresa?
                GROUP BY m.idmodulo";
    }

    public static function buscarModuloPorUnidadeEIdEmpresa()
    {
        return "SELECT uo.idunidadeobjeto, m.idmodulo, m.modulo, e.sigla, uo.idunidade, m.rotulomenu
                FROM carbonnovo._modulo m
                JOIN unidadeobjeto uo ON(m.modulo = uo.idobjeto AND uo.tipoobjeto = 'modulo')
                JOIN empresa e ON(uo.idempresa = e.idempresa)
                WHERE uo.idunidade = ?idunidade?
                AND uo.idempresa = ?idempresa?
                ORDER BY m.rotulomenu";
    }

    public static function buscarModulosPortipo()
    {
        return "SELECT idmodulo, rotulomenu, modulo
                FROM carbonnovo._modulo
                WHERE status = 'ATIVO'
                AND tipo = '?tipo?'
                ORDER by rotulomenu";
    }

    public static function buscarImpressorasTipoZebraDoModulo()
    {
        return "SELECT t.ip, CONCAT(t.tag,' - ',t.descricao) as descr, t.fabricante, t.ip
                FROM etiquetaobjeto eo
                    JOIN tag t ON (eo.idobjeto = t.idtag AND eo.tipoobjeto = 'tag' and t.linguagem = 'ZPL')
                    JOIN objetovinculo ov on (ov.idobjeto = '?idmodulo?' and ov.tipoobjeto='modulo' and ov.tipoobjetovinc='tag' and ov.idobjetovinc=t.idtag)
                GROUP BY t.idtag;";
    }

    public static function buscarRotuloMenu()
    {
        return "SELECT rotulomenu FROM "._DBCARBON."._modulo WHERE modulo = '?_modulo?'";
    }
    
    public static function buscarModparAtivo(){
        return "SELECT 
                        count(*) as existe,group_concat(rotulomenu) as modulos
                    FROM
                        "._DBCARBON."._modulo
                    WHERE
                        modulopar = '?modulo?' AND status = 'ATIVO'";
    }

    public static function buscarUnidadesTabelas(){
        return "SELECT 1 FROM ?tabela? WHERE idunidade IN(?idunidades?)";
    }
}
?>
