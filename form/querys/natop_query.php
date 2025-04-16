<?
class NatopQuery{

    public static function listarNatopPorEmpresa(){
        return "SELECT n.idnatop,concat (n.natop,' - CFOP [',ifnull(GROUP_CONCAT(c.cfop , ''),''),']') as natop,n.finnfe 
                FROM natop n LEFT JOIN cfop c ON(c.idnatop = n.idnatop)
                WHERE n.status='ATIVO'               
                GROUP BY n.idnatop ORDER BY natop";

    }
    
    public static function listarNatopCfop()
    {
        return "SELECT idcfop, CONCAT(cfop, ' ', IFNULL(n.natop, '')) AS ncfop
                  FROM cfop c  LEFT JOIN natop n ON (c.idnatop = n.idnatop)
                 WHERE c.status = 'ATIVO'
              ORDER BY c.cfop";
    }

    public static function buscarCfopPorNatop()
    {
        return "SELECT 
                    cfop
                FROM
                    cfop
                WHERE
                    status = 'ATIVO'
                        AND idnatop = ?idnatop?
                        AND origem = '?origem?'
                LIMIT 1";
    }

    public static function buscarNatOpECfopPorOrigemEIdNatOp()
    {
        return "SELECT c.cfop
                  FROM natop n JOIN cfop c ON c.idnatop = n.idnatop AND c.origem = '?origem?'
                 WHERE n.status = 'ATIVO' AND n.finnfe = 4
                   AND n.idnatop = ?idnatop?";
    }
}
?>