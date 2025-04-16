<?

class DeviceFirmQuery
{
    public static function buscarDeviceFirmPorModelo()
    {
        return "SELECT *
                FROM devicefirm 
                WHERE modelo='?modelo?'
                ORDER BY versao DESC";
    }
}

?>