<?
require_once("../inc/php/validaacesso.php");
if($_POST){
	require_once("../inc/php/cbpost.php");
}

require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
require_once("../api/notifitem/notif.php");

// CONTROLLERS
require_once(__DIR__."/../form/controllers/m5status_controller.php");

session_start();

$leiturasM5 = M5StatusController::buscarLeituras($_GET['todos']);

function notificacarDevices( $arrDevices = [] ){

    $KEY_REDIS_NOTIFICACAO = 'monitoramento:m5';
    $EXP_REDIS_NOTIFICACAO = 1800; // 15 minutos

    // Verifica se existe algum device que precise ser notificado
    if(count($arrDevices) < 1) return false;

    // O comando NX impedi que a chave seja criada duas vezes
    // caso a chave já exista, retorna false
    $redisKey = re::dis()->set($KEY_REDIS_NOTIFICACAO, 'OK',['ex'=>$EXP_REDIS_NOTIFICACAO, 'nx']);

    if(!$redisKey) return false;

    $destinatarios = [
        [
            "idpessoa" => 8150,
            "telefone" => 991463021,
            "usuario" => "leonelaparecido"
        ],
        [ 
            "idpessoa" => 8211,
            "telefone" => 988084431,
            "usuario" => "guilhermealves"
        ],
        [
            "idpessoa" => 7944,
            "telefone" => 992517058,
            "usuario" => "brenocardoso"
        ],
        [
            "idpessoa" => 97219,
            "telefone" => 991970508,
            "usuario" => "udrocorrea"
        ]
    ];

    foreach($arrDevices as $k => $v){

        Notif::ini()->send([
            "canais" => [
                "browser" => [
                    "tipo" => "template", // ou idnotificacaoconfiguracao
                    "template" => [
                        "mod" => $_GET["_modulo"],
                        "modpk" => "iddevice",
                        "idmodpk" => $v['iddevice'],
                        "title" => "Monitoramento Supervisório",
                        "corpo" => $v['corpo'],
                        "localizacao" => "engenharia",
                        "url" => "https://sislaudo.laudolab.com.br/?_modulo=monitoramento".$v['link'],
                    ],
                ],
                "voip" => [
                    "pkid" => $v['iddevice'],
                    "tag" => $v['tag'],
                    "mod" => $_GET["_modulo"]
                ],
            ],
            "destinatarios" => $destinatarios,
        ]);

    }
}

function montarLeiturasHTML($leiturasM5)
{
    // GVT - 11/04/2022 - Array de devices com inconformidade que serão notificados;
    $arrNotificarDevice = array();

    foreach($leiturasM5 as $key => $leitura)
    {
        if($key == 0)
        {
            echo '  <table id="tipom5" style="background: #666666;margin:0px; padding:8px;color:#eee;width:100%">
                        <tr><td>'.$_COOKIE['subtipo'].'</td></tr>
                    </table>
                    <table  id="myTable" class="table table-striped planilha " style="font-size:9px"  > 
                        <thead>
                          <tr>
                            <th style="text-align:left" width="12%">Tag M5</th>
                            <th style="text-align:left" width="5%">Bloco</th>
                            <th style="text-align:left" width="26%">Localização Sala / Equipamento</th>
                            <th style="text-align:center" width="10%">Atividade</th>
                            <th style="text-align:center" width="10%">Ciclo</th>
                            <th style="text-align:right" width="4%">Valor</th>
                            <th style="text-align:right" width="3%">Wifi</th>
                            <th style="text-align:center" width="6%">Última Leitura</th>
                            <th style="text-align:center" width="6%">Uptime</th>
                            <th style="text-align:left" width="3%">Sensor</th>
                            <th style="text-align:left" width="3%">Versão</th>
                            <th style="text-align:center" width="9%">Status</th>
                            <th style="text-align:center" width="1%">Uso</th>
                            <th style="text-align:right" width="1%"></th> 
                            <th style="text-align:center" width="1%"></th>
                            <th style="text-align:center" width="1%"></th>
                            <th style="text-align:center" width="1%"></th>
                            <th style="text-align:left" width="1%"></th>
                          </tr>
                        </thead>';
        }
        //Pegar estado do Device
        $arrm5[$leitura['iddevice']] = re::dis()->hGetAll('_estado:'.$leitura['iddevice'].':device');

        //Pegar dados do sensor
        $arrdsb[$leitura['iddevicesensorbloco']] = re::dis()->hGetAll('_estado:'.$leitura['iddevicesensorbloco'].':devicesensorbloco');
            
        if($arrm5[$leitura['iddevice']]['prssi'] >= 50) {
        
            
            $wifi =  '
                <ul id="signal-strength" title="'.$arrm5[$leitura['iddevice']]['ssid'].'" class="p-0">
                    <li class="very-weak"><div></div></li>
                    <li class="weak"><div></div></li>
                    <li class="strong"><div></div></li>
                    <li class="pretty-strong"><div></div></li>
                </ul>';


        }else if($arrm5[$leitura['iddevice']]['prssi'] >= 40 && $arrm5[$leitura['iddevice']]['prssi'] <= 49 ) {
        
            
            $wifi =  '
                <ul id="signal-strength" title="'.$arrm5[$leitura['iddevice']]['ssid'].'" class="p-0">
                    <li class="very-weak"><div></div></li>
                    <li class="weak"><div></div></li>
                    <li class="strong"><div></div></li>
                </ul>';

        }else if($arrm5[$leitura['iddevice']]['prssi'] >= 30 && $arrm5[$leitura['iddevice']]['prssi'] <= 39 ) {
        
            
            $wifi =  '
                <ul id="signal-strength" title="'.$arrm5[$leitura['iddevice']]['ssid'].'" class="p-0">
                    <li class="very-weak"><div></div></li>
                    <li class="weak"><div></div></li>
                </ul>';


        }else if($arrm5[$leitura['iddevice']]['prssi'] >= 1 && $arrm5[$leitura['iddevice']]['prssi'] <= 29 ) {
        
            
            $wifi =  '
                <ul id="signal-strength" title="'.$arrm5[$leitura['iddevice']]['ssid'].'" class="p-0">
                    <li class="very-weak"><div></div></li>
                </ul>';
        }else {
            $wifi = '-';
        }
        

        if($arrm5[$leitura['iddevice']]['status'] == 'ATIVO'){
            if($leitura['dataatual'] == 'danger'){
                $color = '#DC143C';

                if($leitura['iddeviceciclo'] == 1 || $leitura['iddeviceciclo'] == 15 || $leitura['iddeviceciclo'] == 22 || $leitura['emuso']!="Y"){
                    $color = '#777777';
                }else{
                    switch($leitura['var']){
                        case 't':
                            $notifText = " de Temperatura";
                            break;
                        case 'p':
                        case 'd':
                            $notifText = " de Pressão";
                            break;
                        case 'u':
                            $notifText = " de Umidade";
                            break;
                        default:
                            $notifText = "";
                            break;
                    }
                    $corpoNotificacao = "Equipamento ".$leitura['tag']." está sem leitura".$notifText;
                    $arrNotificarDevice[] = [
                        "iddevice" => $leitura['iddevice'],
                        "tag" => $leitura['tagnotificacao'],
                        "corpo" => $corpoNotificacao,
                        "link" => "&subtipo=".$leitura['subtipo']."&itipo=".$leitura['tipo'],
                    ];
                }
                $text = 'Sem Leitura';
                
                
            }else if ($leitura['valor'] < $leitura['alertamin'] or $leitura['valor'] > $leitura['alertamax'] ){
                if($_SESSION['alerta'.$leitura['iddevice']] == ''){
                    $_SESSION['alerta'.$leitura['iddevice']] = date("Y-m-d H:i:s");
                }
                if((strtotime(date("Y-m-d H:i:s")) - strtotime($_SESSION['alerta'.$leitura['iddevice']])) <= 3600){
                    $color = '#f6c23e';
                }else{
                    $color = '#DC143C';
                }
                if($leitura['emuso']!="Y"){
                    $color = '#777777';
                }
                if ($leitura['var'] == 't'){
                    $text = 'Temperatura';
                }else if ($leitura['var'] == 'p' || $leitura['var'] == 'd'){
                    $text = 'Pressão';
                }else if ($leitura['var'] == 'u'){
                    $text = 'Umidade';
                } 

                if($color == '#DC143C'){
                    $corpoNotificacao = ($leitura['valor'] < $leitura['alertamin']) 
                        ? "Equipamento ".$leitura['tag']." com ".strtoupper($text)." abaixo do esperado"
                        : "Equipamento ".$leitura['tag']." com ".strtoupper($text)." acima do esperado";

                    $arrNotificarDevice[] = [
                        "iddevice" => $leitura['iddevice'],
                        "tag" => $leitura['tagnotificacao'],
                        "corpo" => $corpoNotificacao,
                        "link" => "&subtipo=".$leitura['subtipo']."&itipo=".$leitura['tipo'],
                    ];
                }
            }else{
                $color = '#0f8041';
                $text = 'ok';
                $_SESSION['alerta'.$leitura['iddevice']] = '';
            }
        }elseif($arrm5[$leitura['iddevice']]['status'] == 'DESLIGADO'){
            $color = '#777777';
            $text = 'Desligado';
        }else{
            $color = '#777777';
            $text = 'Inacessível';
        }

        $alerta = ($color == '#DC143C');

        if(($leitura['tipo'] == 'd' && $leitura['subtipo'] == 'DIFERENCIAL') ||( $leitura['subtipo'] == 'MONITORAMENTO'))
        {
            $alerta = ($color == '#777777' && $text == 'Sem Leitura' && (int)str_replace('m', '', explode(' ', $leitura['ultimoregistro'])[2]) > 10);
        }
        
        re::dis()->hSet('_estado:'.$leitura['iddevicesensorbloco'].':devicesensorbloco', 'color', $color);
        re::dis()->hSet('_estado:'.$leitura['iddevicesensorbloco'].':devicesensorbloco', 'text', $text);

        
        if ($leitura['iddeviceciclo'] == 17 || $leitura['iddeviceciclo'] == 18 || $leitura['iddeviceciclo'] == 19 || $leitura['iddeviceciclo'] == 9 || $leitura['iddeviceciclo'] == 28 || $leitura['iddeviceciclo'] == 31 || $leitura['iddeviceciclo'] == 33 || $leitura['iddeviceciclo'] == 42 || $leitura['iddeviceciclo'] == 43 || $leitura['iddeviceciclo'] == 44){
            $colorciclo = '#DC143C';
            $colorcicloback = 'snow';
            
        }else if($leitura['iddeviceciclo'] == 7 || $leitura['iddeviceciclo'] == 8 || $leitura['iddeviceciclo'] == 10 || $leitura['iddeviceciclo'] == 11 || $leitura['iddeviceciclo'] == 13 || $leitura['iddeviceciclo'] == 16 || $leitura['iddeviceciclo'] == 36 || $leitura['iddeviceciclo'] == 40 || $leitura['iddeviceciclo'] == 41 ){
            $colorciclo = '#337ab7';
            $colorcicloback = 'aliceblue';
        }else{
            $colorciclo = '#777777';
            $colorcicloback = 'gainsboro';
            
        }
        
        echo "<!-- Device: ".$leitura['iddevice']." Sessão: ".$_SESSION['id'.$leitura['iddevice']]." | Valor: ".$leitura['valor']."-->";
        if ($_SESSION['id'.$leitura['iddevice']] != ''){
            if($_SESSION['id'.$leitura['iddevice']] < $leitura['valor']){
                $icon = 'fa-long-arrow-up';
                $coloricon = '#DC143C';
                $_SESSION['seta'.$leitura['iddevice']] = $icon;
                $_SESSION['setacor'.$leitura['iddevice']] = $coloricon;
            
            }else if($_SESSION['id'.$leitura['iddevice']] > $leitura['valor']){
                $icon = 'fa-long-arrow-down';
                $coloricon = '#337ab7';
                $_SESSION['seta'.$leitura['iddevice']] = $icon;
                $_SESSION['setacor'.$leitura['iddevice']] = $coloricon;
            }

        }
        if ($_SESSION['id'.$leitura['iddevice']] != $leitura['valor']){
            $_SESSION['id'.$leitura['iddevice']] = $leitura['valor'];
        }

        $idempresadevice = '';
        if (!empty($leitura['idempresadevice'])) {
            $idempresadevice='&_idempresa='.$leitura['idempresadevice'];
        }
        $idempresatag = '';
        if (!empty($leitura['idempresatag'])) {
            $idempresatag='&_idempresa='.$leitura['idempresatag'];
        }
        $idempresasala = '';
        if (!empty($leitura['idempresasala'])) {
            $idempresasala='&_idempresa='.$leitura['idempresasala'];
        }

        echo '<tr title="iddevicesensorbloco: '.$leitura['iddevicesensorbloco'].'\nIp: '.$leitura['ip_hostname'].'" class="'.$leitura['subtipo'].' '.$leitura['tipo'].' '.($alerta ? 'alerta' : '').'">';
        // Tag M5
        echo    '<td style="text-align:left">
                    <a style="font-size:8px;" href="/?_modulo=tag&_acao=u&idtag='.$leitura['idtag'].$idempresatag.'" target="_blank" title="TAG ORIGEM">
                        <span style="background:gainsboro;border-left: 0.75rem solid '.$leitura['corsistema'].' !important;font-size:10px;color: #333;padding: 2px 6px;border-radius: 3px;margin:2px">
                            '.$leitura['tag'].'
                        </span>
                    </a>
                    <br>';

        if ($leitura['taglocada'] <> '')
        {
            echo   '<a style="font-size:8px;" href="/?_modulo=tag&_acao=u&idtag='.$idempresatag.'" target="_blank" title="TAG LOCADA">
                        <span style="background:gainsboro;border-left: 0.75rem solid '.$leitura['corsistemalocada'].' !important;font-size:11px;color: #333;padding: 2px 6px;border-radius: 3px;margin:2px">
                            '.$leitura['taglocada'].'
                        </span>
                    </a>';
        }
        
        echo    '</a>
                </td>';
        // Bloco
        echo    '<td>        
                    <a href="/?_modulo=tag&_acao=u&idtag='.$leitura['idtagsalabloco'].'" target="_blank" >
                        <span style="background:gainsboro;border-left: 0.75rem solid '.$leitura['corsistema'].' !important;font-size:11px;color: #333;padding: 2px 6px;border-radius: 3px;margin:2px">
                            '.explode('|', $leitura['tagsalabloco'])[1].'
                        </span>
                    </a>
                </td>';

        // Localizacao
        echo    '<td style="text-align:left">
                    <a href="/?_modulo=tag&_acao=u&idtag='.$leitura['idtagsala'].$idempresasala.'" target="_blank">
                        <span style="background:gainsboro;border-left: 0.75rem solid '.$leitura['corsistemasala'].' !important;font-size:10px;color: #333;padding: 2px 6px;border-radius: 3px;margin:2px">
                            '.$leitura['tagsala'].'
                        </span>
                    </a>
                    <br>';
        if ($leitura['tagsala2'] <> '')
        {
            echo '<a href="/?_modulo=tag&_acao=u&idtag='.$leitura['idtagsala2'].$idempresasala.'" target="_blank">
                    <span style="background:gainsboro;border-left:0.75rem solid '.$leitura['corsistemasala2'].' !important;font-size:7px;color: #333;padding: 2px 6px;border-radius: 3px;margin:2px">'.$leitura['tagsala2'].'</span>
                </a>';
        }
                
        echo '</td>';

         // Atividade
         echo '<td style="text-align:left">
                    <div style="border-radius:15px;border:1px solid'.$colorciclo.';color:'.$colorciclo.';background-color:'.$colorcicloback.';padding: 2px 6px;font-size:8px;word-break:normal;text-align:center;text-transform:uppercase;">
                        '.$leitura['nomeativ'].'
                    </div>
                </td>';

        // Ciclo
        echo '<td style="text-align:left">
                    <div style="border-radius:15px;border:1px solid #fff;background:#777777;color:#fff;padding: 2px 6px;font-size:8px;word-break:normal;text-align:center;text-transform:uppercase;">
                    <a style="color:#fff;text-decoration:none">'.$leitura['nomeciclo'].'</a>
                    </div>
              </td>';

        // Valor
        echo '<td style="text-align:right">
                        <i class="fa '.$_SESSION['seta'.$leitura['iddevice']].'" style="color:'.$_SESSION['setacor'.$leitura['iddevice']].'" aria-hidden="true"></i>';

        if($leitura['tipo'] == 't')
        {
            $un = 'ºC';
        } else if ($leitura['tipo'] == 'p' && ($leitura['iddevice'] == 93 || $leitura['iddevice'] == 33))
        {
            $un = 'bar';
        } else if ($leitura['tipo'] == 'u')
        {
            $un = 'um';
        } else if ($leitura['tipo'] == 'p' || $leitura['tipo'] == 'd')
        {
            $un = 'Pa';
        }

        echo $arrdsb[$leitura['iddevicesensorbloco']][$leitura['tipo']].$un.'</td>';

        // Wifi
        echo '<td style="text-align:right">'.$wifi.'</td>';

        // Ultima leitura
        echo '<td style="text-align:center">'.$leitura['ultimoregistro'].'</td>';

        // Uptime
        echo '<td style="text-align:center">'.$leitura['uptime'].'</td>';

        // Sensor
        echo '<td style="text-align:left">'.$leitura['nomesensor'].'</td>';

        // Versao
        echo '<td style="text-align:left">'.$leitura['versao'].'</td>';

        // Status
        echo '<td style="text-align:center">
                    <div title="'.dmahms($arrm5[$leitura['iddevice']]['desligadoem']).'" class="hrefs" style="border-radius:15px;width:100%;text-transform:uppercase;background:'.$color.';color:#ffffff;padding: 2px 6px;font-size:8px;word-break:normal;text-align:center;">
                        '.$text.'
                    </div>
                </td>';

        // Uso
        if ($leitura['tipo'] == "d")
        {
            echo '<td style="text-align:left"><a href="/?_modulo=tag&_acao=u&idtag='.$leitura['idtagref'].$idempresatag.'" target="_blank">'.$leitura['tagref'].'</a></td>';
        } else
        {
            if($leitura['emuso'] == "Y")
            {
                echo '<td><i class="fa fa-star fa-1x laranja btn-lg pointer p-0" onclick="altespecial(`N`, '.$leitura['idtagsala'].');" title="Alterar sala para não uso"></i></td>';
            } else
            {
                echo '<td><i class="fa fa-star fa-1x cinzaclaro btn-lg pointer p-0" onclick="altespecial(`Y`, '.$leitura['idtagsala'].');" title="Alterar sala para uso"></i></td>';
            }
        }

        if(!empty($leitura['iddevice']))
        {
            $deviceFirm = M5StatusController::buscarDeviceFirmPorModelo($leitura['modelo']);
            $style = "display: none;";

            if($deviceFirm['versao'] != $leitura['versao'])
            {
                $style = "display: block;";
            }
        }
        
        // Desvio
        echo '<td>';
        
        if ($leitura['desvio'] == 'Y')
        {
            echo '<a href="'.$leitura['link'].'" target="_blank"><i class="fa fa-warning vermelho" ></i></a>';
        }

        $fim = date('d/m/Y');
        $inicio = date("d/m/Y",strtotime("-1 day",strtotime(date("Y-m-d"))));

        $idTag = $leitura['idtaglocada'] ? $leitura['idtaglocada'] : $leitura['idtag'];

        $idBloco = $leitura['idtagsalabloco'] ? "&idbloco={$leitura['idtagsalabloco']}" : '';
        $idSala = $leitura['idtagsala'] ? "&idsala={$leitura['idtagsala']}" : '';
        $idEquipamento = $idTag ? "&idequipamento={$idTag}" : '';

        echo  '</td>';

        // Relatorio agrupado
        echo '<td>
                <a href="/?_modulo=menurelatorio&menupai=294&_menu=N&_menulateral=N&_novajanela=Y&_idrep=176&_idempresa=1&idempresa=1&iddevice='.$leitura['iddevice'].'&tipo='.$leitura['tipo'].'&_fds='.$inicio.'-'.$fim.'" target="_blank">
                    <i class="fa fa-bar-chart snippet" title="Relatório Agrupado"></i>
                </a>
            </td>';
                
        // Mapa equipamento
        echo '<td>';
        if($idBloco)
        {
            echo '  <a href="?_modulo=mapaequipamento&_idempresa=8'.$idBloco.$idSala.$idEquipamento.'" target="_blank" title="Visualizar no Mapa de equipamentos">
                        <i class="fa fa-map pointer azul"></i>
                    </a>';
        }
        echo    '</td>';

        // Atualizar versao
        echo    '<td><i class="fa fa-upload dz-clickable pointer azul acaom52" data-acaom5="atualizar" style="'.$style.'" data-ip="'.$leitura['ip_hostname'].'" title="Clique para atualizar a versão!"></i></td>';

        // Reinicia
        echo    '<td><i class="fa fa-refresh acaom5 pointer" data-acaom5="reiniciar" data-conclusao="M5 Reiniciado" data-ip="'.$leitura['ip_hostname'].'"  title="Clique para reiniciar!"></i></td>';

        // Iddevice
        echo    '<td style="text-align:right"><a href="/?_modulo=device&_acao=u&iddevice='.$leitura['iddevice'].$idempresadevice.'" target="_blank">'.$leitura['iddevice'].'</a></td>
            </tr>';
    }

    echo '</table>';

    notificacarDevices($arrNotificarDevice);
}

/* 53, 55, 44, 54, 43, 42, 58, 57, 12, 47, 48, 13, 51, 49*/

if(!empty($_REQUEST['subtipo']) && in_array($_REQUEST['subtipo'], M5StatusController::$subtiposDisponiveis)) {
    $_COOKIE['subtipo'] = $_REQUEST['subtipo'];
}

if(!empty($_REQUEST['itipo']) && in_array($_REQUEST['itipo'], M5StatusController::$tiposDisponiveis)) {
    $_COOKIE['tipo'] = $_REQUEST['itipo'];
}

?>

<meta http-equiv="refresh" content="60" />
<link rel="stylesheet" href="/inc/css/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="/inc/css/carbon.css">
<link rel="stylesheet" href="/inc/css/fontawesome/font-awesome.min.css">
<style>#signal-strength {
    height: 15px;
    list-style: none;
    overflow: hidden;
    }
    #signal-strength li {
    display: inline-block;
    width: 3px;
    height: 100%;
    margin-right: 0px;
    }
    #signal-strength li.pretty-strong {
    padding-top: 0px;
    }
    #signal-strength li.strong {
    padding-top: 3px;
    }
    #signal-strength li.weak {
    padding-top: 7px;
    }
    #signal-strength li.very-weak {
    padding-top: 11px;
    }
    #signal-strength li div {
    height: 100%;
    background: #337ab7;
    }

    thead th
    {
        padding: 1px 4px !important;
    }

    .alerta
    {
        background-color: #dc143c33 !important;
    }
</style>
<div style="margin-top:20px">
    <h2>Monitoramento Supervisório</h2>
        Selecione um Mapa:
    <select id='mySelector' style="width: 150px">
        <option value="CONTROLE" <?= $_COOKIE['subtipo'] == 'CONTROLE' ? 'selected="selected"' : '' ?>>
            Controle
        </option>
        <option value="MONITORAMENTO" <?= $_COOKIE['subtipo'] == 'MONITORAMENTO' ? 'selected="selected"' : '' ?>>
            Monitoramento
        </option>
        <option value="DIFERENCIAL" <?= $_COOKIE['subtipo'] == 'DIFERENCIAL' ? 'selected="selected"' : '' ?>>
            Diferencial
        </option>
    </select>

    Selecione uma unidade: 
    <select id='mySelector2' style="width: 150px">
        <option value="t" <?= $_COOKIE['tipo'] == 't' ? 'selected="selected"' : '' ?>>
        Temperatura
        </option>
        <option value="p" <?= $_COOKIE['tipo'] == 'p' ? 'selected="selected"' : '' ?>>
        Pressão
        </option>
        <option value="u" <?= $_COOKIE['tipo'] == 'u' ? 'selected="selected"' : '' ?>>
        Umidade
        </option>
        <option value="d" <?= $_COOKIE['tipo'] == 'd' ? 'selected="selected"' : '' ?>>
        Diferencial
        </option>
    </select>
</div>
<br>
<?
montarLeiturasHTML($leiturasM5);

require_once(__DIR__."/../form/js/m5status_js.php");

?>