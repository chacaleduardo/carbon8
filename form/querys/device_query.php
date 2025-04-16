<?

class DeviceQuery
{
    public static function atualizarColunaIdTagPeloIdDevice()
    {
        return "UPDATE device SET idtag = ?idtag? WHERE iddevice = ?iddevice?";
    }

    public static function atualizarColunaIdTagPeloIdTag()
    {
        return "UPDATE device SET idtag = ?idtag? WHERE idtag = ?idtagantiga?";
    }

    public static function buscarDevicePorIdTag()
    {
        return "SELECT * FROM device WHERE idtag = ?idtag?";
    }

    public static function buscarInfoDevices()
    {
        return "SELECT
                    t.idtag,
                    d.iddevice,
                    IF(DATE_ADD(dh.registradoem,
                        INTERVAL 60 MINUTE) < NOW(),
                    'danger',
                    'success') AS dataatual,
                    c.iddeviceciclo,
                    t.emuso,
                    dh.valor,
                    dc.alertamax,
                    dsb.tipo
                FROM tag t
                LEFT JOIN device d ON(t.idtag = d.idtag)
                JOIN devicesensor ds ON(ds.iddevice = d.iddevice)
                LEFT JOIN devicesensorhist dh ON(dh.iddevice = d.iddevice)
                JOIN (SELECT 
                    MAX(iddevicesensorhist) AS iddevicesensorhist
                    FROM
                        devicesensorhist dh
                    WHERE
                        tipo in ('u', 'p')
                    GROUP BY iddevice) h ON h.iddevicesensorhist = dh.iddevicesensorhist
                LEFT JOIN devicesensorbloco dsb ON(ds.iddevicesensor = dsb.iddevicesensor)
                LEFT JOIN devicecicloativ dc ON(dc.iddevicecicloativ = dh.iddevicecicloativ)
                LEFT JOIN deviceciclo c on c.iddeviceciclo = dc.iddeviceciclo
                WHERE t.idtag in (?idtag?)
                AND NOT d.subtipo IS null
                GROUP BY t.idtag;";
    }

    public static function buscarTodosDevices()
    {
        return "SELECT iddevice, tag 
                FROM device d 
                JOIN tag t ON(d.idtag = t.idtag)
                ORDER BY tag";
    }

    public static function buscarDevicePorMacAddress()
    {
        return "SELECT ip_hostname AS ip, mac_address 
                FROM device 
                WHERE mac_address = '?mac_address?'";
    }

    public static function buscarDevicesDisponiveisParaVinculoPorIdDeviceCiclo()
    {
        return "SELECT d.iddevice, CONCAT('TAG - ', t.tag, ' - ', t.descricao, ' - ', d.descricao) as descricao
                FROM device d 
                JOIN tag t ON (d.idtag = t.idtag) 
                WHERE d.status = 'ATIVO' 
                AND NOT EXISTS (
                    SELECT 1 
                    FROM deviceobj dj
                    WHERE dj.tipoobjeto = 'deviceciclo' 
                    AND dj.iddevice = d.iddevice 
                    AND dj.objeto = ?iddeviceciclo?
                )";
    }

    public static function buscarTags()
    {
        return "SELECT iddevice, concat('TAG-',tag) AS tag 
                FROM device d
                JOIN tag t ON d.idtag = t.idtag
                ORDER BY tag";
    }

    public static function buscarDeviceTagETagSalaPorClausula()
    {
        return "SELECT 
                    d.iddevice,
                    CONCAT('TAG-', t.tag) AS tag,
                    s.descricao,
                    CONCAT('TAG-', s.tag) AS tagsala
                FROM device d
                JOIN tag t ON t.idtag = d.idtag
                LEFT JOIN tagsala ts ON ts.idtag = t.idtag
                LEFT JOIN tag s ON s.idtag = ts.idtagpai
                WHERE ?clausula?";
    }
}

?>