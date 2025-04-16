<?

// QUERY
require_once(__DIR__."/../../form/querys/_iquery.php");
require_once(__DIR__."/../../form/querys/m5status_query.php");
require_once(__DIR__."/../../form/querys/devicefirm_query.php");

// CONTROLLERS
require_once(__DIR__."/_controller.php");

class M5StatusController extends Controller
{
    public static $subtiposDisponiveis = ['CONTROLE', 'MONITORAMENTO', 'DIFERENCIAL'];
    public static $tiposDisponiveis = ['t', 'p', 'u', 'd'];

    public static function buscarLeituras($todas = 'Y')
    {
        $condicionalComplementar = "";

        if($todas != 'Y')
        {
            $condicionalComplementar = " and not d.subtipo = '' ";
        }

        $leituras = SQL::ini(M5StatusQuery::buscarLeituras(), [
            'todas' => $condicionalComplementar
        ])::exec();

        if($leituras->error()){
            parent::error(__CLASS__, __FUNCTION__, $leituras->errorMessage());
            return [];
        }

        return $leituras->data;
    }

    public static function buscarDeviceFirmPorModelo($modelo)
    {
        $deviceFirm = SQL::ini(DeviceFirmQuery::buscarDeviceFirmPorModelo(), [
            'modelo' => $modelo
        ])::exec();

        if($deviceFirm->error()){
            parent::error(__CLASS__, __FUNCTION__, $deviceFirm->errorMessage());
            return [];
        }

        return $deviceFirm->data[0];
    }
}

?>