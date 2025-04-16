<?
class RemessaQuery{ 
    
    public static function buscarRemessaPorIdcontapagar(){
        return "SELECT 
                    i.idremessaitem,
                    i.idremessa,
                    i.idcontapagar,
                    i.status AS remessa,
                    r.dataenvio,
                    r.status,
                    a.boleto
                FROM
                    remessaitem i,
                    remessa r,
                    agencia a
                WHERE
                    i.idremessa = r.idremessa
                        AND a.idagencia = r.idagencia
                        AND r.status IN ('GERADO' , 'ENVIADO', 'CONCLUIDO')
                        AND i.status IN ('C' , 'P')
                        AND i.idcontapagar IN (?idcontapagar?)";

    }

    public static function buscarIdRemessa()
    {
        return "SELECT idremessa
                  FROM remessa r JOIN formapagamento fp ON r.idagencia = fp.idagencia
                 WHERE r.status = 'GERADO' AND geraremessa = 'Y'
                   AND fp.idformapagamento = ?idformapagamento?
                   AND NOT EXISTS(SELECT 1 
                                    FROM remessaitem ri 
                                   WHERE ri.idcontapagar = ?idcontapagar? 
                                     AND ri.status NOT IN ('A', 'E')
                                     AND ri.idremessa = r.idremessa);";
    }

    public static function buscarIdRemessaParaFormaPagamento()
    {
        return "SELECT idremessa
                  FROM remessa r JOIN formapagamento fp ON r.idagencia = fp.idagencia
                 WHERE r.status = 'GERADO' AND geraremessa = 'Y'
                   AND fp.idformapagamento = ?idformapagamento?;";
    }

    public static function inserirRemessaItem()
    {
        return "INSERT INTO remessaitem (idempresa, 
                                         idremessa, 
                                         idcontapagar, 
                                         criadopor, 
                                         criadoem, 
                                         alteradopor, 
                                         alteradoem) 
                                 VALUES (?idempresa?, 
                                         ?idremessa?, 
                                         ?idcontapagar?, 
                                         '?usuario?', 
                                         ?criacao?, 
                                         '?usuario?', 
                                         ?criacao?);";
    }

    public static function buscarRemssaEnvioPorIdnf(){
        return "SELECT 
                    i.*
                FROM
                    contapagar c
                        JOIN
                    remessaitem i ON (i.idcontapagar = c.idcontapagar
                        AND i.tipo = 1)
                WHERE
                    c.idobjeto = ?idnf?
                        AND c.status != 'RENEGOCIADO'
                        AND c.tipoobjeto = 'nf'";
    }
}