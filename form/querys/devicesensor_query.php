<?

class DeviceSensorQuery
{
    public static function buscarDeviceSensor()
    {
        return "SELECT iddevicesensor, concat(nomesensor,' (',ifnull(localizacao,''),')') AS descricao
                FROM devicesensor ds
                WHERE ds.status = '?status?'
                AND ds.iddevice = ''";
    }

    public static function buscarDeviceSensorPorIdDevice()
    {
        return "SELECT ds.nomesensor, ds.iddevicesensor 
                FROM devicesensor ds 
                WHERE ds.iddevice = ?iddevice? 
                ORDER BY nomesensor";
    }
}

?>