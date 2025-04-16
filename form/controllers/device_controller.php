<?
// QUERYS
require_once(__DIR__."/../../form/querys/_iquery.php");
require_once(__DIR__."/../../form/querys/device_query.php");
require_once(__DIR__."/../../form/querys/deviceciclo_query.php");
require_once(__DIR__."/../../form/querys/devicefirm_query.php");
require_once(__DIR__."/../../form/querys/devicesensor_query.php");

// CONTROLLERS
require_once(__DIR__."/_controller.php");
require_once(__DIR__."/tag_controller.php");

class DeviceController extends Controller
{
    public static $status = [
        'ATIVO' => 'Ativo',
        'INATIVO' => 'Inativo',
        'PENDENTE' => 'Pendente'
    ];

    public static $tipo = [
        'M5' => 'M5'
    ];

    public static $subTipo = [
        '' => '',
        'CONTROLE' => 'Controle',
        'MONITORAMENTO' => 'Monitoramento',
        'DIFERENCIAL' => 'Diferencial'
    ];

    public static $ambiente = [
        'PROD' => 'PRODUCAO',
        'DEV' => 'DESENVOLVIMENTO'
    ];

    public static $modelo = [
        'CICLO' => 'Ciclo',
        'AUTOCLAVE' => 'Autoclave',
        'AUTOCLAVE VACUO' => 'Autoclave Vacuo',
        'MONITORAMENTO DE SALA' => 'Monitoramento de sala',
        'ESTUFA' => 'Estufa',
        'CHILLER' => 'Chiller',
        'CÂMARA FRIA' => 'Câmara fria',
        'ESTUFA ENV' => 'Estufa ENV',
        'ESTUFA CAL' => 'Estufa CAL'
    ];

    public static function buscarTodosDevices()
    {
        $arrRetorno = [];
        $query = "SELECT '' AS iddevice ,'' AS tag UNION ";
        $query .= DeviceQuery::buscarTodosDevices();

        $devices = SQL::ini($query)::exec();

        if($devices->error()){
            parent::error(__CLASS__, __FUNCTION__, $devices->errorMessage());
            return [];
        }

        foreach($devices->data as $device)
        {
            $arrRetorno[$device['iddevice']] = $device['tag'];
        }

        return $arrRetorno;
    }

    public static function buscarDeviceSensor()
    {
        $arrRetorno = [];
        $sensores = SQL::ini(DeviceSensorQuery::buscarDeviceSensor(), [
            'status' => 'ATIVO'
        ])::exec();

        if($sensores->error()){
            parent::error(__CLASS__, __FUNCTION__, $sensores->errorMessage());
            return $arrRetorno;
        }

        foreach($sensores->data as $sensor)
        {
            $arrRetorno[$sensor['iddevicesensor']] = $sensor['descricao'];
        }

        return $arrRetorno;
    }

    public static function buscarDeviceSensorPorIdDevice($idDevice)
    {
        $sensores = SQL::ini(DeviceSensorQuery::buscarDeviceSensorPorIdDevice(), [
            'iddevice' => $idDevice
        ])::exec();

        if($sensores->error()){
            parent::error(__CLASS__, __FUNCTION__, $sensores->errorMessage());
            return [];
        }

        return $sensores->data;
    }

    public static function buscarCiclosPorIdDevice($idDevice)
    {
        $arrRetorno = [];

        $ciclos = SQL::ini(DeviceCicloQuery::buscarDeviceCicloSemVinculoComIdDevice(), [
            'iddevice' => $idDevice
        ])::exec();

        if($ciclos->error()){
            parent::error(__CLASS__, __FUNCTION__, $ciclos->errorMessage());
            return $arrRetorno;
        }

        foreach($ciclos->data as $ciclo)
        {
            $arrRetorno[$ciclo['iddeviceciclo']] = $ciclo['nomeciclo'];
        }

        return $arrRetorno;
    }

    public static function buscarCiclosDeDeviceObjetoPorIdDevice($idDevice)
    {
        $ciclos = SQL::ini(DeviceCicloQuery::buscarCiclosDeDeviceObjetoPorIdDevice(), [
            'iddevice' => $idDevice
        ])::exec();

        if($ciclos->error()){
            parent::error(__CLASS__, __FUNCTION__, $ciclos->errorMessage());
            return [];
        }

        return $ciclos->data;
    }

    public static function buscarDeviceFirmPorModelo($modelo)
    {
        $deviceFirm = SQL::ini(DeviceFirmQuery::buscarDeviceFirmPorModelo(),[
            'modelo' => $modelo
        ])::exec();

        if($deviceFirm->error()){
            parent::error(__CLASS__, __FUNCTION__, $deviceFirm->errorMessage());
            return [];
        }

        return $deviceFirm->data;
    }

    public static function buscarDevicePorMacAddress($macAddress)
    {
        $device = SQL::ini(DeviceQuery::buscarDevicePorMacAddress(),[
            'mac_address' => $macAddress
        ])::exec();

        if($device->error()){
            parent::error(__CLASS__, __FUNCTION__, $device->errorMessage());
            return [];
        }

        return $device->data;
    }
}

?>