<?

class ImMsgConfFiltrosQuery
{
    public static function buscarFiltrosDaSelecao()
    {
        return "SELECT 
                    col,
                    sinal,
                    valor,
                    nowdias,
                    idimmsgconffiltros
                from immsgconffiltros
                where
                    valor!=''
                    and valor!=' '
                    and ((valor='null' and (sinal='is' or sinal='is not')) or valor!='null')
                    and valor is not null
                    and idimmsgconf = ?idimmsgconf?";
    }

    
}

?>