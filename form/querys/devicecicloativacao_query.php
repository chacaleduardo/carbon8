<?

class DeviceCicloAtivacaoQuery
{
    public static function inserirDeviceCicloAtivacao()
    {
        return "INSERT INTO devicecicloativacao 
                (
                    iddevicecicloativ, acao, pino, estado, rotulo, idempresa, criadopor, criadoem, alteradopor, alteradoem
                )
                VALUES
                (
                    '?iddevicecicloativ?', '?acao?', '?pino?', '?estado?', '?rotulo?',
                    '?idempresa?', '?criadopor?', ?criadoem?, '?alteradopor?', ?alteradoem?
                )";
    }

    public static function buscarDeviceCicloAtivacaoPorIdDeviceCicloAtiv()
    {
        return "SELECT * FROM devicecicloativacao WHERE iddevicecicloativ = ?iddevicecicloativ?";
    }
}

?>