<?

class DeviceCicloQuery
{
    public static function buscarDeviceCicloPorIdDeviceCiclo()
    {
        return "SELECT * FROM deviceciclo WHERE iddeviceciclo = ?iddeviceciclo?";
    }

    public static function buscarDeviceCicloSemVinculoComIdDevice()
    {
        return "SELECT iddeviceciclo, nomeciclo
                FROM deviceciclo d
                WHERE  d.status = 'ATIVO' 						
                AND NOT EXISTS (
                    SELECT 1 
                    FROM deviceobj do
                    WHERE do.objeto= d.iddeviceciclo 
                    AND do.tipoobjeto = 'deviceciclo'
                    AND do.iddevice = ?iddevice?
                )
                ORDER BY nomeciclo";
    }

    public static function buscarCiclosDeDeviceObjetoPorIdDevice()
    {
        return "SELECT nomeciclo, iddeviceobj , iddeviceciclo 
                FROM deviceciclo dc 
                JOIN deviceobj do ON(do.objeto = dc.iddeviceciclo AND do.tipoobjeto = 'deviceciclo')
                WHERE do.iddevice = ?iddevice?
                ORDER BY nomeciclo";
    }
}

?>