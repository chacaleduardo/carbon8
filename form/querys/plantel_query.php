<?
class PlantelQuery{
    public static function buscarPlantelPorEmpresaeProdserv(){
        return "SELECT idplantel, plantel 
            FROM plantel 
            WHERE status = 'ATIVO' ?sqlidempresa?
                AND prodserv = 'Y' 
            ORDER BY plantel";
    }

    public static function buscarPlantelPorIdobjetoTipoobjeto(){
        return "SELECT
                    u.idplantel
                    ,u.plantel
                    ,p.idplantelobjeto
                    ,e.sigla
                    from plantel u
                    left join plantelobjeto p on( u.idplantel = p.idplantel and p.idobjeto = ?idobjeto? and p.tipoobjeto = '?tipoobjeto?')
                    left join empresa e on (e.idempresa = u.idempresa)
                where u.status='ATIVO'
                ?getidempresa?
                order by u.plantel";
    }

    public static function listarPlantelPorIdobjetoTipoobjetoProdservAtiva()
    {
        return "SELECT u.idplantel,
                       CONCAT(e.sigla, ' - ', u.plantel) AS plantel,
                       p.idplantelobjeto
                  FROM plantel u LEFT JOIN plantelobjeto p ON (u.idplantel = p.idplantel AND p.idobjeto = ?idobjeto?  AND p.tipoobjeto = '?tipoobjeto?')
                  JOIN empresa e ON e.idempresa = u.idempresa
                 WHERE u.status = 'ATIVO' AND u.prodserv = 'Y'
                 ?getidempresa?
              ORDER BY IF(u.plantel LIKE 'outr%', CONCAT('z', u.plantel), u.plantel)";
    }

    public static function buscarPlantelPessoa()
    {

        return "SELECT 
                    idplantel
                FROM
                    plantelobjeto
                WHERE
                    idobjeto = ?idpessoa?
                        AND tipoobjeto = 'pessoa'
                LIMIT 1";
        
    }

    public static function buscarDivisaoPlantel()
    {
        return "SELECT 
                    i.comissaogest, d.idpessoa, d.iddivisao
                FROM
                    divisao d
                        JOIN
                    divisaoitem i ON (i.iddivisao = d.iddivisao)
                        JOIN
                    divisaoplantel dp ON (dp.iddivisao = d.iddivisao
                        AND d.idplantel = ?idplantel? )
                WHERE
                    i.idprodserv = ?idprodserv?
                        AND d.status = 'ATIVO'
                        AND d.tipo = 'PRODUTO'
                GROUP BY d.idpessoa";
    }
    
    public static function buscarPlantelPorIdObjetoETipoObjeto()
    {
        return "SELECT p.idplantel, p.plantel
                  FROM plantel p JOIN plantelobjeto po ON (p.idplantel = po.idplantel)
                 WHERE status = 'ATIVO'
                   AND po.idobjeto = ?idobjeto?
                   AND po.tipoobjeto = '?tipoobjeto?'
              ORDER BY p.plantel";
    }

    public static function buscarPlantelPorIdUnidadeEIdEmpresa()
    {
        return "SELECT p.idplantel, p.plantel, p.idunidade, p.idempresa, e.sigla
                FROM plantel p
                JOIN empresa e ON(e.idempresa = p.idempresa)
                WHERE p.idunidade = ?idunidade?
                AND p.idempresa = ?idempresa?
                ORDER BY p.plantel";
    }

    public static function buscarPlanteisDisponiveisParaVinculoEmUnidades()
    {
        return "SELECT p.idplantel, p.plantel, e.sigla
                FROM plantel p
                JOIN empresa e ON(e.idempresa = p.idempresa)
                WHERE p.status = 'ATIVO'
                AND p.idempresa = ?idempresa?
                AND (p.idunidade IS NULL OR p.idunidade = '')
                ORDER BY p.plantel";
    }

    public static function buscarPlanteisProdServ() {
        return "SELECT *
                FROM plantel
                WHERE prodserv = 'Y'
                AND status = 'ATIVO'
                AND idempresa = ?idempresa?";
    }
}   
?>