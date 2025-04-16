<?
require_once(__DIR__."/_iquery.php");

class EmpresaQuery implements DefaultQuery
{
    public static $table = 'empresa';
    public static $pk = 'idempresa';

    public static function buscarPorChavePrimaria()
    {
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL, [
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }  

    public static function  buscarEmpresasCompartilhadas()
    {
        return "SELECT GROUP_CONCAT(ovalue) AS idempresas 
                FROM share 
                WHERE sharemetodo = 'compartilharCbUserTAg' 
                AND FIND_IN_SET(?idtag?, REPLACE(JSON_EXTRACT(jclauswhere, '$.idtag'), '\"', '')) > 0;";
    }

    public static function buscarEmpresasVinculadasAUmaAreaDepartamentoOuSetor()
    {
        return "SELECT e.idempresa, CONCAT(e.sigla, ' - ', e.razaosocial) as empresa
                FROM empresa e
                WHERE status = 'ATIVO'
                AND EXISTS(SELECT 1 FROM ?tabela? t where t.idempresa = e.idempresa);";
    }

    public static function buscarEmpresaPorIdEmpresa()
    {
        return "SELECT e.*, p.nomecurto as ceo 
                FROM empresa e 
                LEFT JOIN pessoa p ON p.idpessoa = e.idceo
                WHERE e.idempresa in(?idempresa?)";
    }

    public static function buscarEmpresasPorIdEmpresaEStatus()
    {
        return SELF::buscarEmpresaPorIdEmpresa()." AND e.status = '?status?'";
        
    }

    public static function buscarEmpresasAtivas()
    {
        return "SELECT * from empresa where status = 'ATIVO'   order by empresa asc";
    }


    public static function listarEmpresasAtivas()
    {
        return "SELECT * from empresa where status = 'ATIVO' and filial='N'  order by empresa asc";
    }

    public static function listarEmpresasAtivasSemConfCobranca()
    {
        return "SELECT idempresa,empresa
                from empresa e
                where e.status = 'ATIVO'
                    and e.filial='N'
                    and not exists (select 1 from empresacobranca ec where ec.idempresad = e.idempresa and ec.idempresa  = ?idempresa?)
                    -- and e.idempresa != ?idempresa? 
                order by e.empresa asc";
    }

    public static function listarEmpresasAtivasComcobranca()
    {
        return "SELECT 
                    e.idempresa, e.empresa,n.idnf,sum(r.valor) as valor,e.idpessoaform
                    ,(select count(*) from empresacobranca c where c.idempresa = ?idempresa?
                                                            and c.idempresad = e.idempresa
                                                            and c.idtipoprodserv is not null 
                                                            and c.idtipoprodservd  is not null
                                                            and c.idcontaitem is not null
                                                            and c.idcontaitemd is not null) as conf
                FROM
                    empresa e
                        LEFT JOIN
                    nf n ON (n.idpessoa = e.idpessoaform
                        AND n.status = 'ABERTO'
                        and n.idempresa=?idempresa?
                        AND n.tipoorc='COBRANCA'
                        AND n.tipocontapagar='C'
                    )
                    left join 
                            rateioitemdestnf r
                        on (  r.idnf = n.idnf)
                WHERE
                    e.status = 'ATIVO'
                    and e.filial='N'						
                GROUP BY e.idempresa  
                ORDER BY e.empresa";
    }

    public static function buscarEmpresaPessoaValorNovaNF(){
        return "SELECT 
                    e.idempresa,sum(r.valor) as valor,e2.idpessoaform,e2.idempresa as idempresacredito
                FROM
                    empresa e
                JOIN  nf n ON (n.idpessoa = e.idpessoaform )                            
                JOIN  rateioitemdestnf r ON (  r.idnf = n.idnf)
                JOIN empresa e2 on(n.idempresa=e2.idempresa)
                WHERE  n.idnf=?idnf? and e.status='ATIVO'";
    }


    public static function buscarEmpresaFilial()
    {
        return "SELECT 
                    e.idempresa, e.nomefantasia
                FROM
                    pessoaobjeto o
                        JOIN
                    empresa e ON (e.idempresa = o.idobjeto
                        AND e.status = 'ATIVO'
                        AND e.filial = 'Y')
                WHERE
                    o.idpessoa = ?idpessoa?
                        AND o.tipoobjeto = 'empresa'
                ORDER BY e.nomefantasia";

    }

    public static function buscarFilial()
    {
        return "SELECT 
                    e.idempresa, e.nomefantasia
                FROM
                    matrizconf m
                        JOIN
                    empresa e ON (e.idempresa = m.idempresa
                        AND e.status = 'ATIVO'
                        AND e.filial = 'Y')
                WHERE
                    m.idmatriz = ?idempresa?
                ORDER BY e.nomefantasia;";

    }

    public static function buscarEmpresasQueNaoEstejamVinculadasEmUmaTagLocada()
    {
        return "SELECT e.idempresa, e.sigla, e.nomefantasia
                FROM empresa e
                WHERE e.idempresa NOT IN (
                    SELECT t.idempresa 
                    FROM tagreserva tr 
                    JOIN tag t ON tr.idobjeto = t.idtag 
                    WHERE tr.idtag = ?idtag?
                    AND tr.status = 'ATIVO'
                )
                AND e.status = 'ATIVO'
                AND e.idempresa <> ?idempresa?";
    }

    public static function buscarEmpresaQueNaoExisteNaObjetoEmpresa()
    {
        return "SELECT u.idempresa, u.empresa
                  FROM empresa u
                 WHERE u.status = 'ATIVO'
                 and u.idempresa= ?idempresa?
                   AND NOT EXISTS(SELECT 1 FROM objempresa o WHERE o.objeto = '?objeto?' AND o.idobjeto = ?idobjeto? AND o.empresa = u.idempresa)
              ORDER BY u.empresa";
    }

    public static function listarEmpresaVinculadaObjetoEmpresa()
    {
        return "SELECT p.idobjempresa, u.empresa, u.idempresa
                  FROM empresa u   JOIN objempresa p ON (u.idempresa = p.empresa AND p.idobjeto = ?idobjeto? and p.objeto = '?objeto?')
                 WHERE u.status = 'ATIVO'
              ORDER BY u.empresa";
    }

    public static function buscarCorSistemaPorIdEmpresa()
    {
        return "SELECT corsistema from empresa where idempresa=?idempresa?";
    }

    public static function buscarIdEmpresasVinculadasPorIdObjetoEObjeto()
    {
        return "SELECT ifnull(group_concat(e.idempresa),0) as idempresa
                FROM empresa e 
                JOIN objempresa o ON o.empresa = e.idempresa
                WHERE e.status = 'ATIVO' 
                AND o.idobjeto = '?idobjeto?'
                AND o.objeto = '?objeto?'";
    }
    
    public static function buscarEmpresaMatriz()
    {
        return "SELECT group_concat(idempresa order by idempresa) as idempresa from(
                SELECT e.idempresa 
                FROM empresa e 
                JOIN matrizconf c on c.idmatriz = e.idempresa
                WHERE habilitarmatriz = 'Y'
                AND c.idempresa = '?idempresa?'
                UNION ALL
                select '?idempresa?') a";
    }
    

    public static function buscarEmpresaPorIdEmpresaEClausulaModuloEUsuario()
    {
        return "SELECT e.idempresa, e.nomefantasia, e.sigla 
                FROM ( 
                    SELECT e.idempresa, e.nomefantasia, e.sigla
                    FROM empresa e
                    WHERE status = 'ATIVO'
                    and idempresa = ?idempresa?
                    AND e.sigla <> ''
                    UNION
                    SELECT e.idempresa, e.nomefantasia, e.sigla
                    from empresa e
                    where e.idempresa in  (select c.idempresa from matrizconf c where c.idmatriz = ?idempresa?)
                    ?clausulaempresamodulo?
                    ?clausulaempresausuario?
                ) as e";
    }
        
    public static function buscarEmpresasQueAPessoaAcessa()
    {
        return "SELECT 
				distinct idempresa, empresa
			FROM
				empresa e
			WHERE
				status = 'ATIVO'
					-- CONDIÇÃO PARA FILTRAR EMPRESAS DO USUARIO
					AND EXISTS
					(
						select 1 from objempresa oe where oe.objeto = 'pessoa' 
						-- ENTRADA
						and oe.idobjeto = ?idpessoa?
						and oe.empresa = e.idempresa
					)				
			ORDER BY e.empresa";
    }

    public static function buscarEmpresasVinculadasAPessoa()
    {
        return "
            select
                CONCAT(COALESCE(p.razaosocial,p.nome),' - ', p.cpfcnpj) as razao_cpfcnpj,
                p.idpessoa,
                p.idtipopessoa,
                p.nome,
                p.razaosocial
            from pessoacontato c, pessoa p
            where c.idpessoa = p.idpessoa
            and c.idcontato = '?idpessoa?'
            order by p.nome";
    }

    public static function buscarEmpresaPorRazaoSocial()
    {
        return "SELECT idempresa
                  FROM empresa
                 WHERE razaosocial LIKE '?razaosocial?%'";
    }
}

?>