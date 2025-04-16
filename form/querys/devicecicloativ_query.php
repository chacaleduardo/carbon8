<?

class DeviceCicloAtivQuery
{
    public static function inserirDeviceCicloAtiv()
    {
        return "INSERT INTO devicecicloativ
                (
                    iddeviceciclo, nomeativ, tipo, qtd, min, max, var, alertamin, alertamax, 
                    panicomin, panicomax, status, ordem, idempresa, criadopor, criadoem, alteradopor, alteradoem
                ) 
                VALUES 
                (
                    '?iddeviceciclo?', '?nomeativ?', '?tipo?', ?qtd?, ?min?,
                    ?max?, '?var?', ?alertamin?, ?alertamax?,
                    ?panicomin?, ?panicomax?, '?status?', ?ordem?,
                    ?idempresa?, '?criadopor?', ?criadoem?, '?alteradopor?', ?alteradoem?
                )";
    }

    public static function buscarDeviceCicloAtivPorIdDeviceCiclo()
    {
        return "SELECT *
                FROM devicecicloativ
                WHERE iddeviceciclo = ?iddeviceciclo?
                ORDER BY ordem";
    }

    public static function buscarDeviceCicloAtivPorIdDeviceCicloAtivEAcao()
    {
        return "SELECT  *
                FROM
                    devicecicloativacao
                WHERE iddevicecicloativ IN(?iddevicecicloativ?)
                AND acao = '?acao?'
                ORDER BY criadoem";
    }
}

?>