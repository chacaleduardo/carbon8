<?

// QUERY
require_once(__DIR__."/../../form/querys/_iquery.php");
require_once(__DIR__."/../../form/querys/devicecicloativ_query.php");
require_once(__DIR__."/../../form/querys/device_query.php");
require_once(__DIR__."/../../form/querys/deviceobj_query.php");
require_once(__DIR__."/../../form/querys/devicecicloativacao_query.php");
require_once(__DIR__."/../../form/querys/log_query.php");

// CONTROLLERS
require_once(__DIR__."/_controller.php");

class DeviceCicloController extends Controller
{
    public static $tipos =[
        'T' => 'Minutos',
        'Q' => 'Vez(es)',
        'I' => 'Indeterminado'
    ];

    public static $var = [
        't' => 'Temperatura',
        'p' => 'Pressão',
        'd' => 'Diferencial'
    ];

    public static $status = [
        'ATIVO' => 'Ativo',
        'INATIVO' => 'Inativo'
    ];

    public static $acoes = [
        '2|1|Ligar Compressor' => 'Ligar Compressor (2)',
        '5|1|Ligar Compressor' => 'Ligar Compressor (5)',
        '17|1|Ligar Compressor' => 'Ligar Compressor (17)',
        '16|1|Ligar Compressor' => 'Ligar Compressor (16)',
        '1|1|Ligar Compressor' => 'Ligar Compressor (1)',
        '3|1|Ligar Compressor' => 'Ligar Compressor (3)',
        '2|0|Desligar Compressor' => 'Desligar Compressor (2)',
        '5|0|Desligar Compressor' => 'Desligar Compressor (5)',
        '17|0|Desligar Compressor' => 'Desligar Compressor (17)',
        '16|0|Desligar Compressor' => 'Desligar Compressor (16)',
        '1|0|Desligar Compressor' => 'Desligar Compressor (1)',
        '3|0|Desligar Compressor' => 'Desligar Compressor (3)',
        '2|1|Ligar Resistencia' => 'Ligar Resistencia (2)',
        '5|1|Ligar Resistencia' => 'Ligar Resistencia (5)',
        '17|1|Ligar Resistencia' => 'Ligar Resistencia (17)',
        '16|1|Ligar Resistencia' => 'Ligar Resistencia (16)',
        '1|1|Ligar Resistencia' => 'Ligar Resistencia (1)',
        '3|1|Ligar Resistencia' => 'Ligar Resistencia (3)',
        '2|0|Desligar Resistencia' => 'Desligar Resistencia (2)',
        '5|0|Desligar Resistencia' => 'Desligar Resistencia (5)',
        '17|0|Desligar Resistencia' => 'Desligar Resistencia (17)',
        '16|0|Desligar Resistencia' => 'Desligar Resistencia (16)',
        '1|0|Desligar Resistencia' => 'Desligar Resistencia (1)',
        '3|0|Desligar Resistencia' => 'Desligar Resistencia (3)',
        '2|1|Ligar Ventiladores' => 'Ligar Ventiladores (2)',
        '5|1|Ligar Ventiladores' => 'Ligar Ventiladores (5)',
        '17|1|Ligar Ventiladores' => 'Ligar Ventiladores (17)',
        '16|1|Ligar Ventiladores' => 'Ligar Ventiladores (16)',
        '1|1|Ligar Ventiladores' => 'Ligar Ventiladores (1)',
        '3|1|Ligar Ventiladores' => 'Ligar Ventiladores (3)',
        '2|0|Desligar Ventiladores' => 'Desligar Ventiladores (2)',
        '5|0|Desligar Ventiladores' => 'Desligar Ventiladores (5)',
        '17|0|Desligar Ventiladores' => 'Desligar Ventiladores (17)',
        '16|0|Desligar Ventiladores' => 'Desligar Ventiladores (16)',
        '1|0|Desligar Ventiladores' => 'Desligar Ventiladores (1)',
        '3|0|Desligar Ventiladores' => 'Desligar Ventiladores (3)',
        '2|1|Ligar Valvula Entra => a' => 'Ligar Valvula Entrada (2)',
        '5|1|Ligar Valvula Entra => a' => 'Ligar Valvula Entrada (5)',
        '17|1|Ligar Valvula Entra => a' => 'Ligar Valvula Entrada (17)',
        '16|1|Ligar Valvula Entra => a' => 'Ligar Valvula Entrada (16)',
        '1|1|Ligar Valvula Entra => a' => 'Ligar Valvula Entrada (1)',
        '3|1|Ligar Valvula Entra => a' => 'Ligar Valvula Entrada (3)',
        '2|0|Desligar Valvula Entra => a' => 'Desligar Valvula Entrada (2)',
        '5|0|Desligar Valvula Entra => a' => 'Desligar Valvula Entrada (5)',
        '17|0|Desligar Valvula Entra => a' => 'Desligar Valvula Entrada (17)',
        '16|0|Desligar Valvula Entra => a' => 'Desligar Valvula Entrada (16)',
        '1|0|Desligar Valvula Entra => a' => 'Desligar Valvula Entrada (1)',
        '3|0|Desligar Valvula Entra => a' => 'Desligar Valvula Entrada (3)',
        '2|1|Ligar Valvula Sai => a' => 'Ligar Valvula Saida (2)',
        '5|1|Ligar Valvula Sai => a' => 'Ligar Valvula Saida (5)',
        '17|1|Ligar Valvula Sai => a' => 'Ligar Valvula Saida (17)',
        '16|1|Ligar Valvula Sai => a' => 'Ligar Valvula Saida (16)',
        '1|1|Ligar Valvula Sai => a' => 'Ligar Valvula Saida (1)',
        '3|1|Ligar Valvula Sai => a' => 'Ligar Valvula Saida (3)',
        '2|0|Desligar Valvula Sai => a' => 'Desligar Valvula Saida (2)',
        '5|0|Desligar Valvula Sai => a' => 'Desligar Valvula Saida (5)',
        '17|0|Desligar Valvula Sai => a' => 'Desligar Valvula Saida (17)',
        '16|0|Desligar Valvula Sai => a' => 'Desligar Valvula Saida (16)',
        '1|0|Desligar Valvula Sai => a' => 'Desligar Valvula Saida (1)',
        '3|0|Desligar Valvula Sai => a' => 'Desligar Valvula Saida (3)',
        '2|1|Ligar Valvula Sai => a Liquido' => 'Ligar Valvula Saida Liquido (2)',
        '5|1|Ligar Valvula Sai => a Liquido' => 'Ligar Valvula Saida Liquido (5)',
        '17|1|Ligar Valvula Sai => a Liquido' => 'Ligar Valvula Saida Liquido (17)',
        '16|1|Ligar Valvula Sai => a Liquido' => 'Ligar Valvula Saida Liquido (16)',
        '1|1|Ligar Valvula Sai => a Liquido' => 'Ligar Valvula Saida Liquido (1)',
        '3|1|Ligar Valvula Sai => a Liquido' => 'Ligar Valvula Saida Liquido (3)',
        '2|0|Desligar Valvula Sai => a Liquido' => 'Desligar Valvula Saida Liquido (2)',
        '5|0|Desligar Valvula Sai => a Liquido' => 'Desligar Valvula Saida Liquido (5)',
        '17|0|Desligar Valvula Sai => a Liquido' => 'Desligar Valvula Saida Liquido (17)',
        '16|0|Desligar Valvula Sai => a Liquido' => 'Desligar Valvula Saida Liquido (16)',
        '1|0|Desligar Valvula Sai => a Liquido' => 'Desligar Valvula Saida Liquido (1)',
        '3|0|Desligar Valvula Sai => a Liquido' => 'Desligar Valvula Saida Liquido (3)',
        '2|1|Ligar Valvula Bom => a' => 'Ligar Valvula Bomba (2)',
        '5|1|Ligar Valvula Bom => a' => 'Ligar Valvula Bomba (5)',
        '17|1|Ligar Valvula Bom => a' => 'Ligar Valvula Bomba (17)',
        '16|1|Ligar Valvula Bom => a' => 'Ligar Valvula Bomba (16)',
        '1|1|Ligar Valvula Bom => a' => 'Ligar Valvula Bomba (1)',
        '3|1|Ligar Valvula Bom => a' => 'Ligar Valvula Bomba (3)',
        '2|0|Desligar Valvula Bom => a' => 'Desligar Valvula Bomba (2)',
        '5|0|Desligar Valvula Bom => a' => 'Desligar Valvula Bomba (5)',
        '17|0|Desligar Valvula Bom => a' => 'Desligar Valvula Bomba (17)',
        '16|0|Desligar Valvula Bom => a' => 'Desligar Valvula Bomba (16)',
        '1|0|Desligar Valvula Bom => a' => 'Desligar Valvula Bomba (1)',
        '3|0|Desligar Valvula Bom => a' => 'Desligar Valvula Bomba (3)',
        '2|1|Ligar Relé' => 'Ligar Relé (2)',
        '5|1|Ligar Relé' => 'Ligar Relé (5)',
        '17|1|Ligar Relé' => 'Ligar Relé (17)',
        '16|1|Ligar Relé' => 'Ligar Relé (16)',
        '1|1|Ligar Relé' => 'Ligar Relé (1)',
        '3|1|Ligar Relé' => 'Ligar Relé (3)',
        '2|0|Desligar Relé' => 'Desligar Relé (2)',
        '5|0|Desligar Relé' => 'Desligar Relé (5)',
        '17|0|Desligar Relé' => 'Desligar Relé (17)',
        '16|0|Desligar Relé' => 'Desligar Relé (16)',
        '1|0|Desligar Relé' => 'Desligar Relé (1)',
        '3|0|Desligar ResReléistencia' => 'Desligar Relé (3)',
    ];

    public static function buscarDeviceCicloAtivPorIdDeviceCiclo($idDeviceCiclo)
    {
        $deviceCicloAtiv = SQL::ini(DeviceCicloAtivQuery::buscarDeviceCicloAtivPorIdDeviceCiclo(), [
            'iddeviceciclo' => $idDeviceCiclo
        ])::exec();

        if($deviceCicloAtiv->error()){
            parent::error(__CLASS__, __FUNCTION__, $deviceCicloAtiv->errorMessage());
            return [];
        }

        return $deviceCicloAtiv->data;
    }

    public static function buscarDeviceCicloAtivacaoPorIdDeviceCicloAtivEAcao($idDeviceCicloAtiv, $acao = 'min')
    {
        $deviceCicloAtivacao = SQL::ini(DeviceCicloAtivQuery::buscarDeviceCicloAtivPorIdDeviceCicloAtivEAcao(), [
            'iddevicecicloativ' => $idDeviceCicloAtiv,
            'acao' => $acao
        ])::exec();

        if($deviceCicloAtivacao->error()){
            parent::error(__CLASS__, __FUNCTION__, $deviceCicloAtivacao->errorMessage());
            return [];
        }

        return $deviceCicloAtivacao->data;
    }

    public static function buscarDevicesDisponiveisParaVinculoPorIdDeviceCiclo($idDeviceCiclo)
    {
        $arrRetorno = [];

        $devicesDisponiveisParaVinculo = SQL::ini(DeviceQuery::buscarDevicesDisponiveisParaVinculoPorIdDeviceCiclo(), [
            'iddeviceciclo' => $idDeviceCiclo
        ])::exec();

        if($devicesDisponiveisParaVinculo->error()){
            parent::error(__CLASS__, __FUNCTION__, $devicesDisponiveisParaVinculo->errorMessage());
            return [];
        }

        foreach($devicesDisponiveisParaVinculo->data as $device)
        {
            $arrRetorno[$device['iddevice']] = $device['descricao'];
        }

        return $arrRetorno;
    }

    public static function buscarDevicesPorIdDeviceCiclo($idDeviceCiclo)
    {
        $devices = SQL::ini(DeviceObjQuery::buscarDevicesPorIdDeviceCiclo(), [
            'iddeviceciclo' => $idDeviceCiclo
        ])::exec();

        if($devices->error()){
            parent::error(__CLASS__, __FUNCTION__, $devices->errorMessage());
            return [];
        }

        return $devices->data;
    }

    public static function inserirDeviceCicloAtivEDeviceCicloAtivacao($idDeviceCiclo, $idDeviceCicloCop)
    {
        $deviceCicloAtiv = self::buscarDeviceCicloAtivPorIdDeviceCiclo($idDeviceCicloCop);

        foreach($deviceCicloAtiv as $cicloAtiv)
        {
            $dadosInserirDeviceCicloAtiv = [
                'iddeviceciclo' => $idDeviceCiclo,
                'nomeativ' => $cicloAtiv["nomeativ"],
                'tipo' => $cicloAtiv["tipo"],
                'qtd' => tratanumero($cicloAtiv["qtd"]),
                'min' => tratanumero($cicloAtiv["min"]),
                'max' => tratanumero($cicloAtiv["max"]),
                'var' => $cicloAtiv["var"],
                'alertamin' => tratanumero($cicloAtiv["alertamin"]),
                'alertamax' => tratanumero($cicloAtiv["alertamax"]),
                'panicomin' => tratanumero($cicloAtiv["panicomin"]),
                'panicomax' => tratanumero($cicloAtiv["panicomax"]),
                'status' => $cicloAtiv["status"],
                'ordem' => tratanumero($cicloAtiv["ordem"]),
                'idempresa' => $cicloAtiv["idempresa"] ?? "'null'",
                'criadopor' => 'sislaudo',
                'criadoem' => 'now()',
                'alteradopor' => 'sislaudo',
                'alteradoem' =>  'now()'
            ];
            
            $inserindoDeviceCicloAtiv = SQL::ini(DeviceCicloAtivQuery::inserirDeviceCicloAtiv(), $dadosInserirDeviceCicloAtiv)::exec();
            if($inserindoDeviceCicloAtiv->error())
            {
                parent::error(__CLASS__, __FUNCTION__, $inserindoDeviceCicloAtiv->errorMessage());
            }
            
            $idDaltimaInsercao = d::b()->insert_id;
    
            $deviceCicloAtivacao = SQL::ini(DeviceCicloAtivacaoQuery::buscarDeviceCicloAtivacaoPorIdDeviceCicloAtiv(), [
                'iddevicecicloativ' => $cicloAtiv["iddevicecicloativ"]
            ])::exec();

            foreach($deviceCicloAtivacao->data as $cicloAtivacao)
            {
                $dadosInserirDeviceCicloAtivacao = [
                    'iddevicecicloativ' => $idDaltimaInsercao,
                    'acao' => $cicloAtivacao["acao"],
                    'pino' => $cicloAtivacao["pino"],
                    'estado' => $cicloAtivacao["estado"],
                    'rotulo' => $cicloAtivacao["rotulo"],
                    'idempresa' => $cicloAtivacao["idempresa"],
                    'criadopor' => 'sislaudo',
                    'criadoem' => 'now()',
                    'alteradopor' => 'sislaudo',
                    'alteradoem' => 'now()'
                ];
        
                $inserindoDeviceCicloAtivacao = SQL::ini(DeviceCicloAtivacaoQuery::inserirDeviceCicloAtivacao(), $dadosInserirDeviceCicloAtivacao)::exec();

                if($inserindoDeviceCicloAtivacao->error())
                {
                    parent::error(__CLASS__, __FUNCTION__, $inserindoDeviceCicloAtivacao->errorMessage());
                }
            }
        }
    }
}

?>