<?

class DeviceSensorCalibQuery
{
    public static function buscarDeviceSensorCalibPorIdDeviceSensorBloco()
    {
        return "SELECT dp.refsubida, dp.refdescida, dp.sensorsubida, dp.sensordescida, dp.iddevicesensorcalib, db.iddevicesensorbloco
                FROM devicesensorcalib dp
                JOIN devicesensorbloco db ON db.iddevicesensorbloco = dp.iddevicesensorbloco
                WHERE db.iddevicesensorbloco = ?iddevicesensorbloco?";
    }
}

?>