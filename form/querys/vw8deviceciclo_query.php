<?
class Vw8DeviceCicloQuery
{
    public static function buscarHistoricoDevicePorIdDeviceEData()
    {
        return "SELECT * FROM (
                    SELECT 
                        CONCAT('<a href=\"/report/_8repprint_devicecicloativ.php?_modulo=device&_idrep=155&iddevice=', `h`.`iddevice`, '&grupo=', `h`.`grupo`, '\" target=\"_blank\">', `c`.`nomeciclo`, '</a>') AS `nomeciclo`,
                        DMAHMS(MIN(`h`.`registradoem`)) AS `mindate`,
                        DMAHMS(MAX(`h`.`registradoem`)) AS `maxdate`,
                        TIMEDIFF(MAX(`h`.`registradoem`), MIN(`h`.`registradoem`)) AS `dif`,
                        ROUND(MIN(`h`.`valor`), 1) AS `minvalor`,
                        ROUND(MAX(`h`.`valor`), 1) AS `maxvalor`,
                        ROUND(AVG(`h`.`valor`), 1) AS `media`,
                        `h`.`iddevice` AS `iddevice`,
                        `h`.`registradoem` AS `registradoem`
                    FROM devicesensorhist h FORCE INDEX(grupo)
                    JOIN devicecicloativ ca ON ca.iddevicecicloativ = h.iddevicecicloativ
                    JOIN deviceciclo c ON c.iddeviceciclo = ca.iddeviceciclo
                    WHERE h.iddevice = ?iddevice? AND h.tipo IN ('t' , 'p') ?data?
                    GROUP BY grupo,  h.iddevice
                    ORDER BY registradoem DESC 
                ) AS vw8deviceciclo";
    }
}

?>