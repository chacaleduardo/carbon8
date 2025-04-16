<?
// QUERYS
require_once(__DIR__."/../../form/querys/_iquery.php");
require_once(__DIR__."/../../form/querys/device_query.php");
require_once(__DIR__."/../../form/querys/devicesensorbloco_query.php");
require_once(__DIR__."/../../form/querys/devicesensortipo_query.php");
require_once(__DIR__."/../../form/querys/devicesensorcalib_query.php");

// CONTROLLERS
require_once(__DIR__."/_controller.php");

class DeviceSensorController extends Controller
{
    public static $nomeSensores = [
        '' =>'',
        'ENV' =>'ENV',
        'ENV3' => 'ENV,3',
        'NTC' => 'NTC',
        'TPJ' => 'TPJ',
        'TXPC' => 'TXPC'
    ];

    public static $status = [
        'ATIVO' => 'Ativo',
        'INATIVO' => 'Inativo',
        'PENDENTE' => 'Pendente'
    ];

    public static $unidades = [
        'PA' => 'Pa',
        'BAR' => 'Bar',
        '%' => '%',
        'ºC' => 'ºC'
    ];

    public static $tiposCalibracao = [
        '1' => 'Offset',
        '2' => 'Multipontos'
    ];

    public static $prioridades = [
        '1' => 'MASTER',
        '2' => 'SECUNDARIO'
    ];

    public static function buscarTags()
    {
        $arrRetorno = [];
        $tags = SQL::ini(DeviceQuery::buscarTags())::exec();

        if($tags->error()){
            parent::error(__CLASS__, __FUNCTION__, $tags->errorMessage());
            return [];
        }

        foreach($tags->data as $tag)
        {
            $arrRetorno[$tag['iddevice']] = $tag['tag'];
        }

        return $arrRetorno;
    }

    public static function buscarDeviceSensoresBlocoPorIdDeviceSensor($idDeviceSensor)
    {
        $arrRetorno = [];
        $deviceSensoresBloco = SQL::ini(DeviceSensorBlocoQuery::buscarDeviceSensorBlocoPorIdDeviceSensor(), [
            'iddevicesensor' => $idDeviceSensor
        ])::exec();

        if($deviceSensoresBloco->error()){
            parent::error(__CLASS__, __FUNCTION__, $deviceSensoresBloco->errorMessage());
            return [];
        }

        return $deviceSensoresBloco->data;
    }

    public static function buscarDeviceSensoresCalibPorIdDeviceSensorBloco($idDeviceSensorBloco)
    {
        $deviceSensoresCalib = SQL::ini(DeviceSensorCalibQuery::buscarDeviceSensorCalibPorIdDeviceSensorBloco(), [
            'iddevicesensorbloco' => $idDeviceSensorBloco
        ])::exec();

        if($deviceSensoresCalib->error()){
            parent::error(__CLASS__, __FUNCTION__, $deviceSensoresCalib->errorMessage());
            return [];
        }

        return $deviceSensoresCalib->data;
    }

    public static function buscarTodosDeviceSensorTipos()
    {
        $arrRetorno = [];
        $sensorTipos = SQL::ini(DeviceSensorTipoQuery::buscarTodosDeviceSensorTipos())::exec();

        if($sensorTipos->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $sensorTipos->errorMessage());
            return [];
        }

        foreach($sensorTipos->data as $tipo)
        {
            $arrRetorno[$tipo['tipo']] = $tipo['rotulo'];
        }

        return $arrRetorno;
    }
}

?>