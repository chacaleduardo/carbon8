<?
require_once(__DIR__."/_iquery.php");

class PessoaQuery implements DefaultQuery
{
    public static $table = 'pessoa';
    public static $pk = 'idpessoa';

    public static function buscarPorChavePrimaria(){
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL,[
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }

    public static function listarPessoaPorIdTipoPessoa()
    {
        return "SELECT p.idpessoa,
                        concat(e.sigla,' - ',p.nomecurto) as nomecurto,
                        concat(e.sigla,' - ',p.nome,' ',ifnull(p.cpfcnpj,'')) as nome,
                        CASE p.idtipopessoa
                        WHEN 1 THEN 'FUNCIONARIO'
                            WHEN 5 THEN 'FORNECEDOR'
                            WHEN 2 THEN 'EMPRESA'	
                            WHEN 7 THEN 'TERCEIRO'
                            WHEN 12 THEN 'REPRESENTAÇÃO'					
                        END as tipo
                  FROM pessoa p JOIN empresa e on(e.idempresa=p.idempresa)
                 WHERE p.idtipopessoa IN ( ?idtipopessoa? )
                 ?status?
                 ?pessoaPorCbUserIdempresa?
              ORDER BY p.nomecurto, p.nome";
    }

    public static function buscarPessoasSetorDepartamentosAreas()
    {
        return "SELECT * FROM (SELECT 
                    0 AS idobjeto,
                    'Colaboradores' AS objeto,
                    'colaboradores' AS tipo,
                    1 as ord
                UNION SELECT 
                    p.idpessoa AS idobjeto,
                    ifnull(p.nomecurto,p.nome) AS objeto,
                    'pessoa' AS tipo,
                    2 as ord
                FROM
                    pessoa p
                WHERE
                    p.status = 'ATIVO'
                        AND p.idtipopessoa = 1
                UNION SELECT 
                    sa.idsgsetor AS idobjeto,
                    sa.setor AS objeto,
                    'sgsetor' AS tipo,
                    3 as ord
                FROM
                    sgsetor sa
                WHERE
                    sa.status = 'ATIVO'
                UNION SELECT 
                    a.idsgdepartamento as idobjeto
                    ,concat('DEPARTAMENTO - ',a.departamento) as local
                    ,'sgdepartamento' as tipo,
                    4 as ord
                FROM
                    sgdepartamento a
                WHERE
                    a.status = 'ATIVO'
                UNION SELECT
                    a.idsgarea as idobjeto
                    ,CONCAT('AREA - ',a.area) as local
                    ,'sgarea' as tipo,
                    5 as ord
                FROM
                    sgarea a
                WHERE
                    a.status = 'ATIVO'
                ) as u order by u.ord asc,u.objeto asc";
    }

    public static function buscarPreferenciaPessoa()
    {
        return "SELECT json_extract(jsonpreferencias,'$.?caminho?') AS jsonpref
                  FROM pessoa
                 WHERE idpessoa = ?idpessoa?";
    }

    public static function buscarPreferenciaCliente(){
        return  "SELECT 
                        pf.prazopagtovenda,
                        pf.parcelavenda,
                        pf.intervalovenda,
                        pf.idformapagamento,
                        pf.obsvenda,
                        pf.observacaonfp,
                        pf.obsinfnfe
                    FROM
                        pessoa p
                            LEFT JOIN
                        preferencia pf ON (pf.idpreferencia = p.idpreferencia)
                            LEFT JOIN
                        formapagamento f ON (pf.idformapagamento = f.idformapagamento ?strempresa?)
                    WHERE
                        p.idpessoa =?idpessoa?";
    }
    
    public static function buscarResultadoAvaliacaoFornecedor()
    {
        return "SELECT resultado, idpessoa
                  FROM vwresultadoavaliacao 
                 WHERE idpessoa IN (?idpessoas?)";
    }

    
    public static function buscarSecretariaResultado(){
        return"SELECT 
                pp.idpessoa, pp.nome
            FROM
                pessoa p,
                pessoa pp
            WHERE
                pp.idpessoa = p.idsecretaria
                    ?idempresa?
                    AND p.idpessoa = ?idsecretaria?";
    }

    public static function buscarPessoasDaEmpresaLogada()
    {
        return "SELECT a.*
                FROM pessoa a
                JOIN objempresa oe ON(oe.idobjeto = a.idpessoa)
                WHERE idtipopessoa in (1,2)
                ?getidempresa?
                AND status = 'ATIVO' 
                UNION
                SELECT a.*
                FROM pessoa a
                WHERE idpessoa = '?idpessoa?' 
                ORDER BY nome, nomecurto";
    }


    public static function buscarHistoricoConselhoAreaDepartamentoSetorPorIdPessoa()
    {
        return "SELECT p.nomecurto, qry.*
                FROM (
                    -- CONSELHO
                    SELECT h.idpessoa, h.acao, 'Conselho' as tipoobjeto, c.conselho as descricao, h.alteradopor, h.alteradoem
                    FROM colaboradorhistorico as h
                    JOIN sgconselho c ON(c.idsgconselho = h.objeto AND h.tipoobjeto = 'sgconselho')
                    -- AREA
                    UNION
                    SELECT h.idpessoa, h.acao, 'Área' as tipoobjeto, a.area as descricao, h.alteradopor, h.alteradoem
                    FROM colaboradorhistorico as h
                    JOIN sgarea a ON(a.idsgarea = h.objeto AND h.tipoobjeto = 'sgarea')
                    -- DEP
                    UNION
                    SELECT h.idpessoa, h.acao, 'Departamento' as tipoobjeto, sgdep.departamento as descricao, h.alteradopor, h.alteradoem
                    FROM colaboradorhistorico as h
                    JOIN sgdepartamento sgdep ON(sgdep.idsgdepartamento = h.objeto AND h.tipoobjeto = 'sgdepartamento')
                    UNION
                    -- SETOR
                    SELECT h.idpessoa, h.acao, 'Setor' as tipoobjeto, s.setor as descricao, h.alteradopor, h.alteradoem
                    FROM colaboradorhistorico as h
                    JOIN sgsetor s ON(s.idsgsetor = h.objeto AND h.tipoobjeto = 'sgsetor')
                ) as qry
                JOIN pessoa as p on (p.idpessoa = qry.idpessoa)
                WHERE qry.idpessoa = ?idpessoa?;";
    }

	public static function buscarPessoasDisponiveisParaVinculoPorIdEmpresa()
	{
		return "SELECT a.idpessoa, CONCAT(a.nome, ' (', t.tipopessoa, ')') AS nome, po.responsavel
				FROM pessoa a
				JOIN tipopessoa t ON t.idtipopessoa = a.idtipopessoa
				LEFT JOIN pessoaobjeto po ON po.idpessoa = a.idpessoa
				WHERE a.status in('ATIVO', 'PENDENTE')
				-- AND a.idempresa = ?idempresa?
				AND a.idtipopessoa IN (1)
				AND NOT EXISTS
				(
					SELECT 
						1
					FROM pessoaobjeto v
					JOIN sgsetor s ON s.idsgsetor = v.idobjeto
					WHERE a.idpessoa = v.idpessoa
					AND v.tipoobjeto IN ('sgsetor')
					AND s.status = 'ATIVO'
                    AND s.idempresa = ?idempresa?
					UNION ALL SELECT 
						1
					FROM pessoaobjeto v
					JOIN sgdepartamento s ON s.idsgdepartamento = v.idobjeto
					WHERE a.idpessoa = v.idpessoa
					AND v.tipoobjeto IN ('sgdepartamento')
					AND s.status = 'ATIVO'
                    AND s.idempresa = ?idempresa?
					UNION ALL
                    SELECT 
						1
					FROM pessoaobjeto v
					JOIN sgarea s ON s.idsgarea = v.idobjeto
					WHERE a.idpessoa = v.idpessoa
					AND v.tipoobjeto IN ('sgarea')
                    AND s.idempresa = ?idempresa?
					AND s.status = 'ATIVO'
                    UNION ALL
					SELECT 1
					FROM pessoaobjeto po
					JOIN sgconselho c ON c.idsgconselho = po.idobjeto AND po.tipoobjeto = 'sgconselho'
					WHERE a.idpessoa = po.idpessoa
                    AND c.idempresa = ?idempresa?
					AND c.status = 'ATIVO'
				)
				GROUP BY a.idpessoa
				ORDER BY t.idtipopessoa , a.nome ASC";
	}

    public static function buscarPessoasDisponiveisParaVinculoPorIdImGrupoEIdEmpresa()
    {
        return "SELECT a.idpessoa, ifnull(a.nomecurto,a.nome) as nomecurto
                FROM pessoa a
                JOIN objempresa oe ON (oe.idobjeto = a.idpessoa)
                WHERE oe.empresa = ?idempresa?
                AND oe.objeto = 'pessoa'
                AND a.status='ATIVO'
                AND a.idtipopessoa in (1,15)
                AND NOT EXISTS(
                    SELECT 1
                    FROM imgrupopessoa v
                    WHERE v.idimgrupo = ?idimgrupo?
                    AND a.idpessoa = v.idpessoa				
                )
                ORDER BY a.nomecurto asc";
    }

    public static function buscarPessoasPorIdEmailVirtualConfEShare()
    {
        return "SELECT p.idpessoa, p.nomecurto AS nome, if(p.webmailemail <> '', p.webmailemail,p.email) as email
                FROM pessoa p
                JOIN objempresa oe ON oe.idobjeto = p.idpessoa 
                WHERE oe.objeto = 'pessoa'
                ?share?
                AND p.idtipopessoa = 1
                AND NOT EXISTS
                (
                    SELECT 1
                    FROM emailvirtualconfpessoa c
                    WHERE c.idpessoa = p.idpessoa 
                    AND c.idemailvirtualconf = ?idemailvirtualconf?)
                AND p.status IN ('ATIVO')
                AND (webmailemail <> '' OR email <> '')
                GROUP BY p.idpessoa
                ORDER BY p.nome";
    }

    public static function buscarPessoasPorIdEmailVirtualConf()
    {
        return "SELECT 
                    e.idemailvirtualconfpessoa,
                    CONCAT(em.sigla, ' - ', p.nomecurto) as nomecurto,
                    IF(p.webmailemail <> '',p.webmailemail,p.email) AS webmailemail,
                    p.idpessoa
                FROM pessoa p
                JOIN emailvirtualconfpessoa e ON(e.idpessoa=p.idpessoa AND e.idemailvirtualconf = ?idemailvirtualconf?)
                JOIN empresa em ON(p.idempresa = em.idempresa)
                WHERE p.idtipopessoa = 1 
                AND p.status='ATIVO'
                AND (p.webmailemail <> '' OR p.email <> '')
                ORDER BY p.nomecurto";
    }

    public static function buscarPessoasDisponiveisParaVinculoPorIdSgcargoEGetIdEmpresa()
    {
        return "SELECT a.idpessoa ,a.nomecurto
                FROM pessoa a
                WHERE a.status='ATIVO'
                    AND a.idtipopessoa = 1
                    AND NOT EXISTS (
                        SELECT 1
                        FROM pessoa v
                        WHERE  v.idsgcargo= ?idsgcargo?
                        AND a.idpessoa = v.idpessoa				
                    )
                ?getidempresa?
                ORDER BY a.nomecurto";
    }

    public static function buscarPessoasPorIdSgcargo()
    {
        return "SELECT p.nome,p.idpessoa,p.idsgcargo 
                FROM pessoa p
                WHERE p.status='ATIVO' 
                AND p.idsgcargo = ?idsgcargo?
                ORDER BY  p.nome";
    }

    public static function buscarFuncoesPorIdSgcargoEGetIdEmpresa()
    {
        return "SELECT idpessoa, idsgfuncao
                FROM pessoa p
                JOIN sgcargofuncao scf ON(scf.idsgcargo = p.idsgcargo AND scf.status = 'ATIVO')
                where 1 
                ?getidempresa?
                AND p.status = 'ATIVO'
                AND p.idsgcargo = ?idsgcargo?
                AND NOT EXISTS (
                    SELECT * FROM pessoasgfuncao psf
                    WHERE psf.idpessoa = p.idpessoa and psf.idsgfuncao = scf.idsgfuncao and psf.status = 'ATIVO'
                )";
    }

    public static function buscarFuncoesRemovidasPorIdSgcargoEGetIdEmpresa()
    {
        return "SELECT p.idpessoa, psf.idsgfuncao, psf.idpessoasgfuncao
                FROM pessoa p
                JOIN pessoasgfuncao psf on psf.idpessoa = p.idpessoa
                WHERE 1
                ?getidempresa?
                AND p.status = 'ATIVO'
                AND p.idsgcargo = ?idsgcargo?
                AND NOT EXISTS (
                    SELECT * FROM sgcargofuncao scf
                    WHERE scf.idsgfuncao = psf.idsgfuncao AND scf.idsgcargo = p.idsgcargo AND scf.status = 'ATIVO'
                );";
    }

    public static function buscarPessoaFuncaoPorGetIdEmpresa()
    {
        return "SELECT p.idpessoa, psf.idsgfuncao, psf.idpessoasgfuncao
                FROM pessoa p
                JOIN pessoasgfuncao psf ON(psf.idpessoa = p.idpessoa)
                WHERE 1
                ?getidempresa?
                AND p.status = 'ATIVO'
                AND (p.idsgcargo = '' OR p.idsgcargo is null)";
    }

    public static function buscarEmailVirtualConfPorGetIdEmpresaIdEmailVirtualConfEEmailDestino()
    {
        return "SELECT 
                        p.idpessoa,
                        p.nomecurto,
                        if(
                            (
                                SELECT 1 
                                FROM emailvirtualconfpessoa e 
                                WHERE 1 
                                ?getidempresa?
                                AND e.idpessoa = p.idpessoa
                                AND e.idemailvirtualconf = ?idemailvirtualconf?
                            ) = 1, 'red', 'blue'
                        )
            as cor 
            FROM pessoa p 
            where (p.webmailemail ='?emaildestino?' or p.email ='?emaildestino?')";
    }

    public static function buscarPessoaPorIdPessoaEGetIdEmpresa()
    {
        return "SELECT p.idpessoa, p.nomecurto, p.idtipopessoa, p.status
                FROM pessoa p
                WHERE 1 
                ?getidempresa?
                AND p.status = 'ATIVO'
                AND p.idtipopessoa 	= 1
                AND p.idpessoa in (?idpessoa?);";
    }

    public static function buscarContatoPessoa()
    {
        return "SELECT 
                    c.idcontato, nome
                FROM
                    pessoa p,
                    pessoacontato c
                WHERE
                    p.status IN ('ATIVO' , 'PENDENTE')
                        AND p.idpessoa = c.idcontato
                        AND c.idpessoa = ?idpessoa?
                ORDER BY nome";
    }

    public static function buscarResponavelCliente()
    {
        return "SELECT 
                    p.idpessoa, p.nome
                FROM
                    pessoacontato c,
                    pessoa p
                WHERE
                    c.idpessoa = ?idpessoa?
                        AND p.idtipopessoa IN (12 , 1)
                        AND p.status = 'ATIVO'
                        AND c.idcontato = p.idpessoa";
    }

    public static function verficaAssinateste()
    {
        return "SELECT assinateste from pessoa where assinateste = 'Y' and idpessoa = ?idpessoa?";
    }

    public static function buscarPessoasPorIdTipoPessoa()
    {
        return "SELECT p.idpessoa, 
                        CONCAT(e.sigla, ' - ',p.nome) AS nome,
                        p.idtipopessoa,
                        p.nomecurto
                FROM pessoa p
                JOIN empresa e ON(e.idempresa = p.idempresa)
                WHERE 1
                AND p.idtipopessoa in (?idtipopessoa?)
                AND p.status IN ('ATIVO', 'PENDENTE')
                ?clausulaIdEmpresa?
                ?orderby?";
    }

    public static function buscarPessoa()
    {
        return "SELECT 
                    p.*
                FROM
                    pessoa p
                WHERE
                    p.idpessoa = ?idpessoa?";
    }

    public static function buscarContatoTelefonePessoa()
    {
        return "SELECT 
                    dddfixo,
                    telfixo,
                    dddcel,
                    telcel
                FROM
                    pessoa 
                WHERE
                    idpessoa = ?idpessoa?";
    }

    public static function listarPessoaVinculadaLote()
    {
        return "SELECT p.idpessoa, p.nome
                  FROM pessoa p
                 WHERE p.idtipopessoa = 2
                   ?idempresa?
                   AND EXISTS(SELECT 1 FROM lote l WHERE l.idpessoa = p.idpessoa)
                   AND p.status = 'ATIVO'
              ORDER BY p.nome";
    }

    public static function buscarPessoasDisponiveisParaVincularNoEvento()
    {
        return "SELECT a.idpessoa ,a.nomecurto    
                FROM pessoa a
                WHERE 1
                ?getidempresa?
                AND a.status ='ATIVO'
                AND a.idtipopessoa = 1
                AND not idpessoa= ?idpessoa?
                AND not exists (
                    SELECT 1
                    FROM fluxostatuspessoa r
                    WHERE r.idmodulo = '?idevento?' AND r.modulo = 'evento'
                    AND r.tipoobjeto ='pessoa'
                    AND a.idpessoa = r.idobjeto)
                ORDER BY a.nomecurto asc";
    }

    public static function buscarGruposDePessoasDisponiveisParaVinculoNoEvento()
    {
        return "SELECT a.idpessoa AS pessoa,
                    CONCAT('<i class=\"fa fa-user\" style=\"color:#ddd;font-size:10px;\"></i> ',
                            (CASE
                                WHEN ss.setor IS NOT NULL THEN CONCAT(e.sigla, ' - ',ss.setor,' - ')
                                WHEN sd.departamento IS NOT NULL THEN CONCAT(e.sigla, ' - ',sd.departamento,' - ')
                                WHEN sa.area IS NOT NULL THEN CONCAT(e.sigla, ' - ',sa.area,' - ')
                                else CONCAT(e.sigla,' - ')
                            END),
                            IF(a.nomecurto IS NULL,
                                CONCAT(a.nome),
                                CONCAT(a.nomecurto))) AS nome,
                    'pessoa' AS 'tipo',
                    IF(a.nomecurto IS NULL,
                        a.nome,
                        a.nomecurto) AS labelnome
                FROM
                    pessoa a
                        LEFT JOIN
                    empresa e ON (e.idempresa = a.idempresa)
                        LEFT JOIN
                    pessoaobjeto po ON (po.idpessoa = a.idpessoa
                        AND po.tipoobjeto IN ('sgsetor' , 'sgdepartamento', 'sgarea'))
                        LEFT JOIN
                    sgsetor ss ON (ss.idsgsetor = po.idobjeto
                        AND po.tipoobjeto = 'sgsetor')
                        LEFT JOIN
                    sgdepartamento sd ON (sd.idsgdepartamento = po.idobjeto
                        AND po.tipoobjeto = 'sgdepartamento')
                        LEFT JOIN
                    sgarea sa ON (sa.idsgarea = po.idobjeto
                        AND po.tipoobjeto = 'sgarea')
                WHERE  a.idtipopessoa in (1,8)
               ?sharepessoa?
                -- AND NOT idpessoa=?idpessoa? 
                AND NOT usuario is null
                AND NOT EXISTS(
                    SELECT 1
                        FROM fluxostatuspessoa r
                        WHERE r.idmodulo= '?idmodulo?' 
                        AND r.modulo = '?modulo?'
                        AND r.tipoobjeto ='pessoa'
                        AND a.idpessoa = r.idobjeto
                )
            UNION
                SELECT idimgrupo AS pessoa, CONCAT('<i class=\"fa fa-users\" style=\"color:lightblue;font-size:10px;\"></i> ',concat(e.sigla,' - ',g.grupo)) AS nome, 'grupo' as 'tipo', grupo as labelnome
                    FROM imgrupo g left join empresa e on (e.idempresa=g.idempresa)
                    WHERE 1
                    ?sharegrupo?
                    AND NOT EXISTS(
                        SELECT 1
                            FROM fluxostatuspessoa r
                            WHERE r.idmodulo= '?idmodulo?' 
                            AND r.modulo = '?modulo?'
                            AND r.tipoobjeto ='imgrupo'
                            AND g.idimgrupo = r.idobjeto)	
            UNION
                SELECT a.idpessoa AS pessoa, CONCAT('<i class=\"fa fa-user\" style=\"color:#ddd;font-size:10px;\"></i> ',IF(a.nomecurto is NULL,concat(e.sigla,' - ',a.nome), concat(e.sigla,' - ',a.nomecurto))) AS nome, 'pessoa' AS 'tipo', IF(a.nomecurto is NULL, a.nome, a.nomecurto) as labelnome
                FROM pessoa a left join empresa e on (e.idempresa=a.idempresa)
                WHERE a.idtipopessoa in (15, 16, 113)
               ?sharepessoa?
                -- AND NOT idpessoa=?idpessoa?
                AND NOT usuario is null
                AND NOT EXISTS(
                    SELECT 1
                        FROM fluxostatuspessoa r
                        WHERE r.idmodulo= '?idmodulo?' 
                        AND r.modulo = '?modulo?'
                        AND r.tipoobjeto ='pessoa'
                        AND a.idpessoa = r.idobjeto)			
            ORDER BY nome ASC";
    }
    
    public static function buscarPessoasAtivasPorIdPessoaEGetIdEmpresa ()
    {
        return "SELECT 
                    p.idpessoa,
                    p.nomecurto,
                    p.idtipopessoa,
                    p.status,
                    p.idempresa
                FROM pessoa p
                WHERE 1
                ?getidempresa?
                AND p.status = 'ATIVO'
                AND p.idtipopessoa IN (1,8)
                AND p.idpessoa in (?idpessoa?)";
    }

    public static function buscarPessoasPorGrupoTipoPessoa()
    {
        return "SELECT 
                    p.idempresa
                    , p.idpessoa
                    , CONCAT(e.sigla, ' - ', tp.tipopessoa) as grupo
                    , '' as descr
                    , p.idtipopessoa  as idobjetoext
                    , 'tipopessoa' as tipoobjetoext
                FROM pessoa p JOIN tipopessoa tp on tp.idtipopessoa=p.idtipopessoa
                JOIN empresa e ON e.idempresa = p.idempresa
                WHERE NOT p.status='INATIVO'
                AND p.idtipopessoa in (1,9)
                AND p.senha > ''";
    }

    public static function buscarTransportadorPorIdpessoa()
    {
        return "SELECT 
                p.idtransportadora, t.nome
            FROM
                pessoa p JOIN pessoa t ON t.idpessoa = p.idtransportadora
            WHERE
                p.idpessoa = ?idpessoa?
                ?idempresa?";
    }

    public static function listarTransportadora()
    {
        return "SELECT 
                        idpessoa,nome 
                FROM
                     pessoa p
                WHERE 
                idtipopessoa = 11 
                 ?transportadoraPorSessionIdempresa? 
                AND status = 'ATIVO'
                ORDER BY nome";
    }

    public static function listarTransportadoraSemShare()
    {
        return "SELECT 
                        idpessoa,nome 
                FROM
                     pessoa p
                WHERE 
                idtipopessoa = 11 
                AND status = 'ATIVO'
                ORDER BY nome";
    }

    public static function buscarPessoaEmailNfePorId()
    {
        return "SELECT 
                    p.emailxmlnfe AS emailxmlnfe,p.emailmat as emailmat
                FROM
                    pessoa p
                WHERE
                    p.idpessoa = ?idpessoa?
                        AND p.status = 'ATIVO'";
    }

    public static function buscarPessoaEmailNfeCc()
    {
        return "SELECT 
                    p.email AS emailxmlnfecc
                FROM
                    pessoacontato c
                        JOIN
                    pessoa p ON (c.idcontato = p.idpessoa)
                WHERE
                    c.idpessoa = ?idpessoa?
                        AND p.status = 'ATIVO'
                        AND c.emailxmlnfe = 'Y'";
    }

    public static function buscarPessoaEmailMaterialNfe()
    {
        return "SELECT 
                    p.email AS emailmaterial
                FROM
                    pessoacontato c
                        JOIN
                    pessoa p ON (c.idcontato = p.idpessoa)
                WHERE
                    c.idpessoa = ?idpessoa?
                        AND p.status = 'ATIVO'
                        AND c.emailmaterial = 'Y'";
    }

    public static function listarClietenPedidoPorIdTipoPessoa()
    {
        return "SELECT p.idpessoa,
                        concat(e.sigla,' - ',p.nomecurto) as nomecurto,
                        concat(e.sigla,' - ',p.nome,' ',ifnull(p.cpfcnpj,'')) as nome,
                        CASE p.idtipopessoa
                        WHEN 1 THEN 'FUNCIONARIO'
                            WHEN 5 THEN 'FORNECEDOR'
                            WHEN 2 THEN 'EMPRESA'	
                            WHEN 7 THEN 'TERCEIRO'
                            WHEN 12 THEN 'REPRESENTAÇÃO'	
                            WHEN 116 THEN 'DISTRIBUIDOR'				
                        END as tipo
                  FROM pessoa p JOIN empresa e on(e.idempresa=p.idempresa)
                 WHERE p.idtipopessoa IN ( ?idtipopessoa? )
                 ?status?
                 and p.idempresa=?idempresa?
                 union
				SELECT p.idpessoa,
                        concat(e.sigla,' - ',p.nomecurto) as nomecurto,
                        concat(e.sigla,' - ',p.nome,' ',ifnull(p.cpfcnpj,'')) as nome,
                        CASE p.idtipopessoa
                        WHEN 1 THEN 'FUNCIONARIO'
                            WHEN 5 THEN 'FORNECEDOR'
                            WHEN 2 THEN 'EMPRESA'	
                            WHEN 7 THEN 'TERCEIRO'
                            WHEN 12 THEN 'REPRESENTAÇÃO'
                            WHEN 116 THEN 'DISTRIBUIDOR'					
                        END as tipo
                  FROM pessoa p JOIN empresa e on(e.idempresa=p.idempresa)
                 WHERE p.idtipopessoa IN (5)
                and p.status = 'ATIVO'
              ORDER BY nomecurto, nome";
    }

    public static function buscarClientePedidoPorIdPessoa()
    {
        return "SELECT p.idpessoa,
                        concat(e.sigla,' - ',p.nome,' ',ifnull(p.cpfcnpj,'')) as nome,
                        CASE p.idtipopessoa
                            WHEN 1 THEN 'FUNCIONARIO'
                            WHEN 5 THEN 'FORNECEDOR'
                            WHEN 2 THEN 'EMPRESA'	
                            WHEN 7 THEN 'TERCEIRO'
                            WHEN 12 THEN 'REPRESENTAÇÃO'
                            WHEN 116 THEN 'DISTRIBUIDOR'					
                        END as tipo
                  FROM pessoa p JOIN empresa e on(e.idempresa=p.idempresa)
                 WHERE p.idpessoa = ?idpessoa?";
    }
    
    public static function buscarClientesAmostra ()
    {
        return "SELECT p.idpessoa
                        , if(p.cpfcnpj !='',concat(p.nome,' - ',p.cpfcnpj),p.nome) as nome
                        ,pf.observacaore
                        ,pf.pedidocp
                        ,p.cpfcnpj
                        ,sec.idpessoa as idsecretaria
                        ,sec.nome as secretaria
                FROM pessoa p
                    LEFT JOIN pessoa sec on (sec.idpessoa = p.idsecretaria)
                    LEFT JOIN preferencia pf on (pf.idpreferencia= p.idpreferencia)
                WHERE p.status in ('ATIVO','PENDENTE')
                    AND p.idtipopessoa = 2			
                    ?getidempresa?
                ORDER BY 2";
    }
    
    public static function buscarSecretariaPessoa ()
    {
        return "SELECT pp.idpessoa,
                        pp.nome
                FROM pessoa p,pessoa pp
                WHERE pp.idpessoa = p.idsecretaria
                    and p.idpessoa =?idpessoa?";
    }
    
    public static function buscarPedidopreferenciaPessoa ()
    {
        return "SELECT pf.pedidocp 
                from pessoa p
                    join preferencia pf on (pf.idpreferencia=p.idpreferencia)
                where p.idpessoa = ?idpessoa?
                and pedidocp='Y'";
    }

    public static function buscarResponavelClienteComissaoProd()
    {
        return "SELECT 
                    c.idcontato, c.participacaoprod, c.idpessoacontato
                FROM
                    pessoa p,
                    pessoacontato c
                WHERE
                    p.status = 'ATIVO'
                        AND p.idtipopessoa IN (12 , 1)
                        AND c.participacaoprod > 0
                        AND p.idpessoa = c.idcontato
                        AND c.idpessoa = ?idpessoa?
                ORDER BY nome";
    }

    public static function buscarClientesPorIdTipoPessoa()
    {
        return "SELECT p.idpessoa,
                       IF(p.cpfcnpj != '', CONCAT(p.nome, ' - ', p.cpfcnpj), p.nome) AS nome,
                       t.tipopessoa AS tipo, 
                       IF(p.cpfcnpj != '' AND p.razaosocial != '', p.razaosocial, '-') AS razaosocial,
                       IF(p.cpfcnpj != '', p.cpfcnpj, '-') AS cpfcnpj,
                       p.regimetrib,
                       p.email
                  FROM pessoa p JOIN tipopessoa t ON t.idtipopessoa = p.idtipopessoa
                 WHERE p.idtipopessoa IN (?idtipopessoa?)
                 ?pessoaPorCbUserIdempresa?
              ORDER BY p.nome";
    }

    public static function buscarTodosClientesETipoFuncionario()
    {
        return "SELECT idpessoa, 
                       nome, 
                       tipo, 
                       razaosocial, 
                       cpfcnpj, 
                       regimetrib,
                       email
                  FROM (SELECT p.idpessoa,
                               IF(p.cpfcnpj != '', CONCAT(p.nome, ' - ', p.cpfcnpj), p.nome) AS nome,
                               t.tipopessoa AS tipo,
                               IF(p.cpfcnpj != '' AND p.razaosocial != '', p.razaosocial, '-') AS razaosocial,
                               IF(p.cpfcnpj != '', p.cpfcnpj, '-') AS cpfcnpj,
                               p.regimetrib,
                               p.email
                          FROM pessoa p JOIN tipopessoa t ON t.idtipopessoa = p.idtipopessoa
                         WHERE p.idtipopessoa IN (2, 5, 6, 7, 9, 11, 12, 116)
                           AND p.status = 'ATIVO'
                           ?pessoaPorCbUserIdempresa?
                      UNION 
                        SELECT p.idpessoa,
                               IF(p.cpfcnpj != '', CONCAT(p.nome, ' - ', p.cpfcnpj), p.nome) AS nome,
                               t.tipopessoa AS tipo,
                               IF(p.cpfcnpj != '' AND p.razaosocial != '', p.razaosocial, '-') AS razaosocial,
                               IF(p.cpfcnpj != '', p.cpfcnpj, '-') AS cpfcnpj,
                               p.regimetrib,
                               p.email
                          FROM pessoa p JOIN tipopessoa t ON t.idtipopessoa = p.idtipopessoa
                         WHERE p.flagobrigatoriocontato = 'Y'
                           AND p.idtipopessoa = 1
                           AND p.status = 'ATIVO') AS u
                ORDER BY nome";
    }

    public static function buscarSePessoaESocio()
    {
        return "SELECT 1 FROM pessoa WHERE flgsocio = 'Y' AND idpessoa = ?idpessoa?";
    }

    public static function listarFuncionarioPessoaPorIdtipoPessoa()
    {
        return "SELECT p.idpessoa, IFNULL(p.nomecurto, p.nome) AS nomecurto, nome
                  FROM pessoa p
                 WHERE p.idtipopessoa = ?idtipopessoa?
                   AND p.status = '?status?'
                   ?share?
             ORDER BY nome";
    }
 
    public static function buscarNomePessoa()
    {
        return "SELECT 
                    nome
                FROM
                    pessoa
                WHERE
                    idpessoa = ?idpessoa?";
    }
 
    public static function buscarClientesParaBioensaio()
    {
        return "SELECT c.idpessoa,
                        c.nome
                FROM pessoa c 
                where status = 'ATIVO'
                        and idtipopessoa = 2 ?getidempresa?
                ORDER BY c.nome";
    }
 
    public static function buscarClientesParaNucleo()
    {
        return "SELECT idpessoa,
                        if(centrocusto !='',concat(nome,' - ',centrocusto),nome) as nome
                FROM pessoa 
                WHERE status IN ('ATIVO', 'PENDENTE')
                   ?getidempresa?
                    and idtipopessoa = 2
                    and nome is not null
                ORDER BY nome";
    }
 
    public static function buscarSecretariaPessoaNucleo()
    {
        return "SELECT p.idsecretaria from pessoa p where p.idpessoa = ?idpessoa?";
    }
 
    public static function buscarUnidadesClientes()
    {
        return "SELECT p.idpessoa,p.nome
                from pessoa p FORCE INDEX(PRIMARY)
                where p.idempresa = ?idempresa?
                and p.status = 'ATIVO'
                ?clausula? and exists (
                SELECT 1 from nucleo n where n.idpessoa = p.idpessoa)
                order by p.nome";
    }

    public static function buscarPessoaPorIdPessoa()
    {
        return "SELECT idpessoa, IFNULL(nomecurto, nome) AS nome
                  FROM pessoa p
                 WHERE idpessoa in(?idpessoa?)
                 order by nome";
    }

    public static function buscarPessoaPorIdUnidadeFuncionario()
    {
        return "SELECT u.idunidade AS id,
                       u.unidade AS nome,
                       'unidade' AS tipo,
                       COUNT(f.idpessoa) AS funidade,
                       (SELECT COUNT(*) FROM pessoa p JOIN vw8FuncionarioUnidade w ON (w.idpessoa = p.idpessoa)
                                        JOIN unidade up ON (up.idunidade = w.idunidade ?rateioexternoup? AND up.status = 'ATIVO')
                                       WHERE p.idtipopessoa = 1 ?idempresaup? AND p.status = 'ATIVO') AS totalf
                  FROM unidade u LEFT JOIN vw8FuncionarioUnidade w ON (w.idunidade = u.idunidade)
             LEFT JOIN pessoa f ON (f.idpessoa = w.idpessoa AND f.status = 'ATIVO' AND f.idtipopessoa = 1)
                 WHERE u.status = 'ATIVO'
                 ?idempresau?
                 ?rateioexterno?
              GROUP BY u.idunidade
              ORDER BY nome;";
    }

    public static function buscarvw8FuncionarioUnidadePorIdTipoPessoa()
    {
        return "SELECT p.nome, 
                       p.idpessoa, 
                       u.idunidade, 
                       u.unidade,
                       ifnull(p.nomecurto,p.nome) as nomecurto
                  FROM pessoa p JOIN vw8FuncionarioUnidade w ON (w.idpessoa = p.idpessoa)
                  JOIN unidade u ON (w.idunidade = u.idunidade)
                 WHERE p.idtipopessoa = 1 
                   ?idempresa?
                   AND p.status = 'ATIVO'
              GROUP BY p.idpessoa, u.idunidade
              ORDER BY p.nome";
    }

    public static function buscarHistoricoProdservPessoaPorIdProdservFormula()
    {
        return "SELECT h.valor,
                       h.valor_old,
                       h.justificativa,
                       h.criadoem,
                       p.nomecurto,
                       h.campo
                  FROM prodservhistorico h JOIN pessoa p ON (p.usuario = h.criadopor)
                 WHERE h.idprodservformula = ?idprodservformula?
                   AND h.campo = '?campo?'
              ORDER BY h.criadoem DESC
                 LIMIT 15";
    }

    public static function buscarHistoricoProdservPessoaPorIdProdserv()
    {
        return "SELECT h.valor,
                       h.valor_old,
                       h.justificativa,
                       h.criadoem,
                       p.nomecurto,
                       h.campo
                  FROM prodservhistorico h JOIN pessoa p ON (p.usuario = h.criadopor)
                 WHERE h.idprodserv = ?idprodserv?
                   AND h.campo = '?campo?'
              ORDER BY h.criadoem DESC
                 LIMIT 15";
    }

    public static function buscarFornecedorPorSessionIdEmpresaEIdTipoPessoa()
    {
        return "SELECT p.idpessoa, IF(p.cpfcnpj != '', CONCAT(p.nome, ' - ', p.cpfcnpj), p.nome) AS nome, em.sigla
                  FROM pessoa p JOIN empresa em ON p.idempresa = em.idempresa
                 WHERE p.idtipopessoa = ?idtipopessoa?
                   AND p.status = 'ATIVO'
                   ?pessoasPorSessionIdempresa?
              ORDER BY p.nome";
    }

    public static function listarPessoaIdempresaGrupoNulo()
    {
        return "SELECT p.idpessoa, p.nome
                  FROM pessoa p JOIN empresa e ON (e.idempresa = p.idempresa)
                 WHERE p.idempresagrupo IS NOT NULL
                   AND p.status = 'ATIVO'
              ORDER BY nome";
    }

    public static function buscarPessoaPorIdTipoPessoaEGetIdEmpresa()
    {
        return "SELECT idpessoa,
                        nome,
                        nomecurto
                FROM pessoa p 
                WHERE p.idtipopessoa = ?idtipopessoa?
                    ?getidempresa?
                    and p.status='ATIVO'
                ORDER BY nome";
    }

    public static function listarPessoaPorIdtipopessoaIdempresa()
    {
        return "SELECT p.idpessoa, p.nome
                  FROM pessoa p
                 WHERE p.idtipopessoa in ( ?idtipopessoa?)
                   ?pessoasPorSessionIdempresa?
                  AND p.status = 'ATIVO'
              ORDER BY p.nome";
    }

    public static function buscarPessoaPorStatusIdTipoPessoaEIdEmpresa()
    {
        return "SELECT p.idpessoa, p.nome, p.centrocusto
                  FROM pessoa p
                 WHERE p.status = '?status?'
                   AND p.idtipopessoa = ?idtipopessoa?
                   ?getidempresa?
              ORDER BY p.nome";
    }
        
    public static function buscarPessoaAtivaPorUsuario()
    {
        return "SELECT * from pessoa where usuario = '?usuario?' and status = 'ATIVO'";
    }

    public static function buscarGroupConcatIdPessoasPorClausula()
    {
        return "SELECT group_concat(idpessoa) as idpessoa from pessoa po where 1 ?clausula?";
    }

    public static function buscarPessoasPorPlantel()
    {
        return "SELECT p.idpessoa, p.nome
                  FROM pessoa p
                 WHERE p.idtipopessoa IN (?idtipopessoa?)
                   AND EXISTS(SELECT 1 FROM plantelobjeto po WHERE po.idobjeto = p.idpessoa AND po.tipoobjeto = 'pessoa' ?condicaoAnd?)
                   AND p.status IN ('ATIVO', 'PENDENTE')
                   ?getidempresa?
              ORDER BY p.nome";
    }

    public static function verificaTipoPessoa()
    {
        return "SELECT 1
                  FROM pessoa p
                 WHERE p.idpessoa = ?idpessoa? and idtipopessoa in (?intipopessoa?)
              ORDER BY p.nome";
    }

    public static function verificaEmpresaPlantelEEmpresaPessoaSaoIguais()
    {
        return "SELECT 1
                  FROM pessoa p
                    JOIN plantelobjeto po on (po.idobjeto = p.idpessoa AND po.tipoobjeto = 'pessoa')
                    JOIN plantel pl on (pl.idplantel = po.idplantel and pl.idempresa = p.idempresa)
                    JOIN unidade u on (pl.idunidade = u.idunidade and u.idempresa = p.idempresa)
                 WHERE p.idpessoa = ?idpessoa?
              ORDER BY p.nome";
    }

    public static function buscarPermissaoVisualizacaoOrganograma()
    {
        return "SELECT visualizarorganograma FROM pessoa WHERE idpessoa = ?idpessoa?";
    }

    public static function buscarColaboradoresPorIdempresaEStatus() {
        return "SELECT idpessoa, nome
                FROM pessoa
                WHERE status = '?status?'
                AND idtipopessoa = 1
                AND idempresa = ?idempresa?";
    }

    public static function verficarPlantelPessoa() {
        return "SELECT count(idplantelobjeto) as verificarplantel
                FROM plantelobjeto
                WHERE idobjeto = ?idpessoa?
                AND idplantel = ?idplantel?
                AND tipoobjeto = 'pessoa'
                ";
    }

    public static function buscarFuncionarPorNome() {
        return "SELECT idpessoa
                  FROM pessoa
                WHERE nome = '?nome?'
                  AND status <> 'INATIVO'
                  AND idempresa = '?idempresa?'";
    }
}
?>