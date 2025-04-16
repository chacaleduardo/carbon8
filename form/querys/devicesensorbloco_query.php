<?

class DeviceSensorBlocoQuery
{
    public static function buscarDeviceSensorBlocoPorIdDeviceSensor()
    {
        return "SELECT dt.rotulo, db.status, db.iddevicesensorbloco, db.unidade, db.offset, db.prioridade, db.tipocalibracao
                FROM devicesensorbloco db
                JOIN devicesensor d ON d.iddevicesensor = db.iddevicesensor
                JOIN devicesensortipo dt ON db.tipo = dt.tipo
                WHERE d.iddevicesensor = ?iddevicesensor?
                ORDER BY rotulo asc, status asc";
    }
}

?>