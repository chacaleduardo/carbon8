<?

class LogQuery
{
    public static function inserirLog()
    {
        /* MAF:300823: Log desativado pois estava gerando lixo na tabela. Criar primeiro um modelo de ATIVAR/DESATIVAR log para usuarios especificos, e após isso pode-se fazer uso de função de LOG
        return "INSERT INTO `log` (idempresa, sessao, tipoobjeto, idobjeto, tipolog, log, status, info, criadoem, data)
                VALUES(?idempresa?, '?sessao?', '?tipoobjeto?', '?idobjeto?', '?tipolog?', '?log?', '?status?', '?info?', '?criadoem?', '?data?')";
        */
        return "";
    }

    public static function buscarlog()
    { return "SELECT 
                *
                FROM
                    log
                WHERE
                    idobjeto = ?idobjeto? 
                    AND tipoobjeto = '?tipoobjeto?'
                        AND tipolog = '?tipolog?'
                ORDER BY criadoem";
    }
}

?>
