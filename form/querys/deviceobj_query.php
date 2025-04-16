<?

class DeviceObjQuery
{
    public static function buscarDevicesPorIdDeviceCiclo()
    {
        return "SELECT dj.iddeviceobj, d.iddevice, t.idtag AS idtagdevice, t.tag AS tagdevice, t2.idtag, t2.tag AS tagsala, t2.descricao AS sala
                FROM deviceobj dj
                JOIN device d ON (dj.iddevice = d.iddevice)
                JOIN tag t ON (d.idtag = t.idtag)
                LEFT JOIN tagsala s ON (s.idtag = t.idtag)
                LEFT JOIN tag t2 ON (s.idtagpai = t2.idtag)
                WHERE dj.tipoobjeto = 'deviceciclo'
                AND dj.objeto = ?iddeviceciclo?";
    }
}

?>