<?
//require_once("../inc/php/cmd.php");
// QUERYS
require_once(__DIR__."/../../form/querys/_iquery.php");
require_once(__DIR__."/../../form/querys/fluxostatuspessoa_query.php");

// CONTROLLERS
require_once(__DIR__."/../../form/controllers/evento_controller.php");
require_once(__DIR__."/../../form/controllers/eventoclassificacao_controller.php");


if($_POST['_chamaprechange_'])
{
    include_once("saveprechange__documento.php");
}
	$repetircheckbox=$_POST['repetircheckbox'];

    $iu                 = $_SESSION['arrpostbuffer']['1']['u']['evento']['idevento'] ? 'u' : 'i';

    $idevento           = $_SESSION['arrpostbuffer']['1'][$iu]['evento']['idevento'];
    $prazo              = $_SESSION['arrpostbuffer']['1'][$iu]['evento']['prazo'];
    $inicial            = $_SESSION['arrpostbuffer']['1'][$iu]['evento']['inicio'];
    $inicio             = $_SESSION['arrpostbuffer']['1'][$iu]['evento']['inicio'];
   // $fim                = $_SESSION['arrpostbuffer']['1'][$iu]['evento']['fim'];
    $iniciohms          = $_SESSION['arrpostbuffer']['1'][$iu]['evento']['iniciohms'];
    $jsonConfig         = $_SESSION['arrpostbuffer']['1'][$iu]['evento']['jsonconfig'];
    $fimhms             = $_SESSION['arrpostbuffer']['1'][$iu]['evento']['fimhms'];
    $diainteiro            = $_SESSION['arrpostbuffer']['1'][$iu]['evento']['diainteiro'];
    $duracaohms         = $_SESSION['arrpostbuffer']['1'][$iu]['evento']['duracaohms'];
    $repetirate         = $_SESSION['arrpostbuffer']['1'][$iu]['evento']['repetirate'];
    $ideventotipo       = $_SESSION['arrpostbuffer']['1'][$iu]['evento']['ideventotipo'];
    $evento             = $_SESSION['arrpostbuffer']['1'][$iu]['evento']['evento'];
    $_x_u_idevento      = $_SESSION['arrpostbuffer']['x']['u']['evento']['idevento'];    
    $_x_u_fluxostatuspessoa_idfluxostatuspessoa = $_SESSION['arrpostbuffer']['x']['u']['fluxostatuspessoa']['idfluxostatuspessoa'];
	$_x_i_carrimbo_idpessoa = $_SESSION['arrpostbuffer']['x']['i']['carrimbo']['idpessoa'];
	$_x_u_carrimbo_idcarrimbo = $_SESSION['arrpostbuffer']['x']['u']['carrimbo']['idcarrimbo'];
	$_ajax_u_modulocom_idmodulocom = $_SESSION['arrpostbuffer']['ajax']['u']['modulocom']['idmodulocom'];

    $_x_i_eventoadd_idobjeto = $_SESSION['arrpostbuffer']['x']['i']['eventoadd']['idobjeto'];
    $_x_d_eventoadd_ideventoadd = $_SESSION['arrpostbuffer']['x']['d']['eventoadd']['ideventoadd'];
   
    $_x_i_modulocom_idmodulo= $_SESSION['arrpostbuffer']['x']['i']['modulocom']['idmodulo'];
    $_idsgdoctipo       = $_SESSION['arrpostbuffer']['x']['i']['sgdoc']['idsgdoctipo'];
    $sgdocstatus        = $_SESSION['arrpostbuffer']['x']['i']['sgdoc']['status'];
    $titulo             = $_SESSION['arrpostbuffer']['x']['i']['sgdoc']['titulo'];
    $sgdoctipodocumento = $_SESSION['arrpostbuffer']['x']['i']['sgdoc']['idsgdoctipodocumento'];
    $_idobjeto       = $_SESSION['arrpostbuffer']['x']['i']['eventoobj']['idobjeto'];

    $idfluxostatuspessoa       = $_SESSION['arrpostbuffer']['x']['d']['fluxostatuspessoa']['idfluxostatuspessoa'];

    $_fluxostatuspessoa_idevento      = $_SESSION['arrpostbuffer']['x']['i']['fluxostatuspessoa']['idevento'];
	
	$ideventoobj       = $_SESSION['arrpostbuffer']['x']['d']['eventoobj']['ideventoobj'];

	 
	if (empty(trim($_SESSION['arrpostbuffer']['100']['i']['modulocom']['descricao']))){
		unset($_SESSION['arrpostbuffer']['100']['i']['modulocom']['idmodulocom']);
		unset($_SESSION['arrpostbuffer']['100']['i']['modulocom']['idempresa']);
		unset($_SESSION['arrpostbuffer']['100']['i']['modulocom']['idmodulo']);
        unset($_SESSION['arrpostbuffer']['100']['i']['modulocom']['modulo']);
		unset($_SESSION['arrpostbuffer']['100']['i']['modulocom']['descricao']);
		unset($_SESSION['arrpostbuffer']['100']['i']['modulocom']['idstatus']);
		unset($_SESSION['arrpostbuffer']['100']['i']['modulocom']['status']);
	}else{

        $atualizandoParaNaoVisualizado = SQL::ini(FluxostatuspessoaQuery::atualizarParaNaoVisualizadoPorIdEvento(), [
            'idevento' => $idevento,
            'idpessoa' => $_SESSION["SESSAO"]["IDPESSOA"]
        ])::exec();

	}


	if($repetircheckbox=='on'){
		$_SESSION['arrpostbuffer']['1'][$iu]['evento']['repetirevento']='Y';
	}elseif($iu=='u' and !empty($idevento)){
        $_SESSION['arrpostbuffer']['1'][$iu]['evento']['repetirevento']='N';
    }

    
    if($diainteiro=='on'){
        $_fimhms='23:59'; 
        $_SESSION['arrpostbuffer']['1'][$iu]['evento']['diainteiro']='Y';
        $_SESSION["arrpostbuffer"]["1"][$iu]["evento"]["fimhms"] = $_fimhms;
        $_SESSION['arrpostbuffer']['1'][$iu]['evento']['fim']=$inicio;
        $fim=$inicio;
        $_SESSION["post"]["_1_".$iu."_evento_fimhms"] = $_fimhms;
        $_POST["_1_".$_GET['_acao']."_evento_fimhms"]=$_fimhms;
        $_POST["_1_".$_GET['_acao']."_evento_fim"]=$inicio;
        $fimhms=$_fimhms;

    }elseif(!empty($duracaohms)){

        $arrfim = explode(":",$duracaohms);    
        // Create a new \DateTime instance
        if (strlen($iniciohms) == 5) {
            $date = DateTime::createFromFormat('H:i', $iniciohms);
            $fimDate  = DateTime::createFromFormat('d/m/Y H:i', $inicio.' '.$iniciohms);
        }else{
            $date = DateTime::createFromFormat('H:i:s', $iniciohms);
            $fimDate  = DateTime::createFromFormat('d/m/Y H:i:s', $inicio.' '.$iniciohms);
        }
        
        
            
        // Modify the date
        $date->modify('+'.$arrfim['0'].' hours');
        // Modify the date
        $date->modify('+'.$arrfim['1'].' minutes');
        // Output
        $_fimhms= $date->format('H:i');   
        
        // Modify the date
        $fimDate->modify('+'.$arrfim['0'].' hours');
        // Modify the date
        $fimDate->modify('+'.$arrfim['1'].' minutes');
        // Output
        $_fimdate= $fimDate->format('d/m/Y');    
        
        $_POST["_1_".$_GET['_acao']."_evento_fim"]=$_fimdate;
        $_SESSION['arrpostbuffer']['1'][$iu]['evento']['fim']=$_fimdate;

        //Atribuir o valor para retorno por session['post'] ah pagina anterior.
        $_SESSION["arrpostbuffer"]["1"][$iu]["evento"]["fimhms"] = $_fimhms;
        $_SESSION["post"]["_1_".$iu."_evento_fimhms"] = $_fimhms;
            $_POST["_1_".$_GET['_acao']."_evento_fimhms"]=$_fimhms;

        $fimhms=$_fimhms;

        $_SESSION['arrpostbuffer']['1'][$iu]['evento']['diainteiro']='N';

    }elseif(!empty($iniciohms)){
        $duracaohms='00:15:00';
        $_SESSION["arrpostbuffer"]["1"][$iu]["evento"]["duracaohms"] = $duracaohms;
        $arrfim = explode(":",$duracaohms);    
        // Create a new \DateTime instance
        if (strlen($iniciohms) == 5) {
            $date = DateTime::createFromFormat('H:i', $iniciohms); 
            $fimDate  = DateTime::createFromFormat('d/m/Y H:i', $inicio.' '.$iniciohms);
        }else{
            $date = DateTime::createFromFormat('H:i:s', $iniciohms);   
            $fimDate  = DateTime::createFromFormat('d/m/Y H:i:s', $inicio.' '.$iniciohms);
        }
        
            // Modify the date
        $fimDate->modify('+'.$arrfim['0'].' hours');
        // Modify the date
        $fimDate->modify('+'.$arrfim['1'].' minutes');
        // Output
        $_fimdate= $fimDate->format('d/m/Y');    
        
        $_POST["_1_".$_GET['_acao']."_evento_fim"]=$_fimdate;
        $_SESSION['arrpostbuffer']['1'][$iu]['evento']['fim']=$_fimdate;
        
        // Modify the date
        $date->modify('+'.$arrfim['0'].' hours');
        // Modify the date
        $date->modify('+'.$arrfim['1'].' minutes');
        // Output
        $_fimhms= $date->format('H:i');    

        //Atribuir o valor para retorno por session['post'] ah pagina anterior.
        $_SESSION["arrpostbuffer"]["1"][$iu]["evento"]["fimhms"] = $_fimhms;
        $_SESSION["post"]["_1_".$iu."_evento_fimhms"] = $_fimhms;
            $_POST["_1_".$_GET['_acao']."_evento_fimhms"]=$_fimhms;

        $fimhms=$_fimhms;
        $_SESSION['arrpostbuffer']['1'][$iu]['evento']['diainteiro']='N';
    }

if (!empty($idfluxostatuspessoa))
{
    EventoController::excluirAssinaturaPorIdFluxostatusPessoa($idfluxostatuspessoa);
}

if (strlen($iniciohms) == 5) {
    $iniciohms .= ":12";
}

if (strlen($fimhms) == 5) {
    $fimhms .= ":12";
}

//valida prazo
if (!empty($prazo) and !empty($_SESSION['arrpostbuffer']['1'][$iu]['evento']['ideventotipo']) ) {
    
    $_SESSION['arrpostbuffer']['1'][$iu]['evento']['inicio']=$prazo;
    $_SESSION['arrpostbuffer']['1'][$iu]['evento']['iniciohms']=$_POST["_1_".$iu."_evento_iniciohms"] ? $_POST["_1_".$iu."_evento_iniciohms"] : date('H:i:s');
    $_SESSION['arrpostbuffer']['1'][$iu]['evento']['fim']=$prazo;
    $_SESSION['arrpostbuffer']['1'][$iu]['evento']['fimhms']=$_SESSION['arrpostbuffer']['1'][$iu]['evento']['iniciohms'];
    $_POST["_1_".$iu."_evento_inicio"]=$prazo;
    $_POST["_1_".$iu."_evento_iniciohms"]=$_SESSION['arrpostbuffer']['1'][$iu]['evento']['iniciohms'];
    $_POST["_1_".$iu."_evento_fim"]=$prazo;
    $_POST["_1_".$iu."_evento_fimhms"]=$_SESSION['arrpostbuffer']['1'][$iu]['evento']['iniciohms'];
}

if(!empty($inicio))
{
    $_SESSION['arrpostbuffer']['1'][$iu]['evento']['prazo'] = $inicio;
}

if (empty($_idsgdoctipo) && empty($idevento) &&  empty($idfluxostatuspessoa) && empty($_fluxostatuspessoa_idevento) && empty($_idobjeto) && empty($_x_u_idevento)
    && empty($prazo) && empty($_x_u_fluxostatuspessoa_idfluxostatuspessoa) && empty($_x_i_modulocom_idmodulo) && empty($_x_i_carrimbo_idpessoa) && empty($_ajax_u_modulocom_idmodulocom)) {

    if ($iu === 'u')
    {
        $evento = EventoController::buscarPorChavePrimariaPadrao($idevento);

        if ($ideventotipo !== $evento["ideventotipo"])
        {
            die("Tipo do evento não pode ser alterado");
        }
    }
    
    if (empty($ideventotipo) )
    {
        if ((empty($ideventoobj) and !$_POST['_x_u_eventoobj_ideventoobj']) and empty($_x_u_carrimbo_idcarrimbo) and empty($_x_i_eventoadd_idobjeto) and empty($_x_d_eventoadd_ideventoadd)){
            die("Tipo evento deve ser selecionado");
        }
    }

    //se $repetirate for diferente de nulo, valida se é maior do que a data inicial do evento
    if (!empty($repetirate)) { 
        $dateRepetirate = DateTime::createFromFormat('d/m/Y', $repetirate)->format('Ymd');
        //Inicio > Repetirate
        if (DateTime::createFromFormat('d/m/Y', $inicio)->format('Ymd')>=$dateRepetirate) {
            die("A Data de início do evento não pode ser maior ou igual a data final de Repetição");
        }
    }


} elseif(!empty($_idsgdoctipo)) {
    
    if (empty($sgdocstatus) || empty($titulo) || empty($sgdoctipodocumento)) {
        die ("RNC inválida");
    }

    $_idregistro = geraRegistrosgdoc($_idsgdoctipo);        

    //Enviar o campo para a pagina de submit
    $_SESSION["arrpostbuffer"]["x"]["i"]["sgdoc"]["idregistro"] = $_idregistro;
    
    //Atribuir o valor para retorno por session['post'] ah pagina anterior.
    $_SESSION["post"]["_x_u_sgdoc_idregistro"] = $_idregistro;
}

$idfluxostatus = traduzid("evento", "idevento", "idfluxostatus", $_SESSION['arrpostbuffer']['1']['u']['evento']['idevento']);

// Adiciona pessoa no evento de acordo com a classificacao
$_modulo = $_SESSION['arrpostbuffer']['1']['u']['evento']['classificacao'];
if($_modulo)
{
    $infoclassificacao = EventoClassificacaoController::buscarClassificacaoPorId($_modulo)[0];
    $idpessoa = $infoclassificacao['idpessoa'];

    $fluxostatusPessoa = SQL::ini(FluxostatusPessoaQuery::buscarFluxoStatuspessoaPorIdEventoEIdPessoa(), [
        'idpessoa' => $idpessoa ?? 'null',
        'idevento' => $_SESSION['arrpostbuffer']['1']['u']['evento']['idevento']
    ])::exec();

    $fluxostatusPessoaEvento = SQL::ini(FluxostatusPessoaQuery::buscarFluxoStatuspessoaPorIdEventoEIdPessoa(), [
        'idpessoa' => $_SESSION['SESSAO']['IDPESSOA'],
        'idevento' => $_SESSION['arrpostbuffer']['1']['u']['evento']['idevento']
    ])::exec();

    if(!$fluxostatusPessoa->numRows())
    {
        $_SESSION['arrpostbuffer']['fp']['i']['fluxostatuspessoa']['idpessoa'] = $_SESSION["SESSAO"]["IDPESSOA"];
        $_SESSION['arrpostbuffer']['fp']['i']['fluxostatuspessoa']['idempresa'] = $_SESSION["SESSAO"]["IDEMPRESA"];
        $_SESSION['arrpostbuffer']['fp']['i']['fluxostatuspessoa']['idmodulo'] = $_SESSION['arrpostbuffer']['1']['u']['evento']['idevento'];
        $_SESSION['arrpostbuffer']['fp']['i']['fluxostatuspessoa']['modulo'] = 'evento';
        $_SESSION['arrpostbuffer']['fp']['i']['fluxostatuspessoa']['idobjeto'] = $idpessoa;
        $_SESSION['arrpostbuffer']['fp']['i']['fluxostatuspessoa']['tipoobjeto'] = 'pessoa';
        $_SESSION['arrpostbuffer']['fp']['i']['fluxostatuspessoa']['status'] = $fluxostatusPessoaEvento->data[0]['status'];
        $_SESSION['arrpostbuffer']['fp']['i']['fluxostatuspessoa']['idfluxostatus'] = $idfluxostatus;
        $_SESSION['arrpostbuffer']['fp']['i']['fluxostatuspessoa']['oculto'] = 0;
        $_SESSION['arrpostbuffer']['fp']['i']['fluxostatuspessoa']['inseridomanualmente'] = 'N';
        $_SESSION['arrpostbuffer']['fp']['i']['fluxostatuspessoa']['visualizado'] = 0;
        $_SESSION['arrpostbuffer']['fp']['i']['fluxostatuspessoa']['assinar'] = 'N';
        $_SESSION['arrpostbuffer']['fp']['i']['fluxostatuspessoa']['criadopor'] = $_SESSION["SESSAO"]["USUARIO"];
        $_SESSION['arrpostbuffer']['fp']['i']['fluxostatuspessoa']['criadoem'] = date("d/m/Y H:i:s");
        $_SESSION['arrpostbuffer']['fp']['i']['fluxostatuspessoa']['alteradopor'] = $_SESSION["SESSAO"]["USUARIO"];
        $_SESSION['arrpostbuffer']['fp']['i']['fluxostatuspessoa']['alteradoem'] = date("d/m/Y H:i:s");
    }
}
    $status = FluxoController::buscarStatusPorIdFluxoStatus($idfluxostatus)[0];
    if(($status['rotuloresp'] == 'Projeto' || $status['rotuloresp'] == 'Dados' || $status['rotuloresp'] == 'Corretiva' || $status['rotuloresp'] == 'Configuração' || $status['rotuloresp'] == 'Infra')
        && empty(traduzid("evento","idevento","url",$_SESSION['arrpostbuffer']['1']['u']['evento']['idevento']))){

    $log = EventoController::verificarLogEventoNoJira($_SESSION['arrpostbuffer']['1']['u']['evento']['idevento'],'evento','jira');
    if($log < 1){

        $username = _USERNAMEAPIJIRA;
        $api_token = _TOKENAPIJIRA;

        // Endpoint da API REST do Jira
        $api_url = _ROTAPADRAOJIRA;

        switch ($status['rotuloresp']) {
            case 'Projeto':
                $tipoJira = array('name' => 'História');
                $projeto = 'LEG';
                $rotafinal = _ROTASADICIONAISAPIINTEGRACAOJIRA["create"];
                // Define os campos da nova tarefa
                $data = array(
                    "URL" => $api_url,
                    "payload" => array(
                        "path" => _ROTASADICIONAISJIRA['issue'],
                        "username" => $username,
                        "token" => $api_token,
                        "jwt" => _JWTAPIJIRA,
                        'fields' => array(
                            'project' => array('key' => $projeto),
                            'summary' => "@".$_SESSION['arrpostbuffer']['1']['u']['evento']['idevento']." - ".strtoupper($_SESSION['arrpostbuffer']['1']['u']['evento']['evento']),
                            'description' => $_SESSION['arrpostbuffer']['1']['u']['evento']['descricao']."\n https://sislaudo.laudolab.com.br/?_modulo=eventoti&_acao=u&idevento=".$_SESSION['arrpostbuffer']['1']['u']['evento']['idevento']."",
                            'issuetype' => $tipoJira,
                            'customfield_10044' => array("https://sislaudo.laudolab.com.br/?_modulo=eventoti&_acao=u&idevento=".$_SESSION['arrpostbuffer']['1']['u']['evento']['idevento'].""),
                            'customfield_10045' => array(str_replace(" ","",$infoclassificacao["classificacao"])),
                            'customfield_10046' => array($infoclassificacao['tipo']),
                        ),
                ));
                break;
            case 'Dados':
                $tipoJira = array('name' => 'Bug');
                $projeto = 'LEG';
                $rotafinal = _ROTASADICIONAISAPIINTEGRACAOJIRA["create"];
                // Define os campos da nova tarefa
                $data = array(
                    "URL" => $api_url,
                    "payload" => array(
                        "path" => _ROTASADICIONAISJIRA['issue'],
                        "username" => $username,
                        "token" => $api_token,
                        "jwt" => _JWTAPIJIRA,
                        'fields' => array(
                            'project' => array('key' => $projeto),
                            'summary' => "@".$_SESSION['arrpostbuffer']['1']['u']['evento']['idevento']." - ".strtoupper($_SESSION['arrpostbuffer']['1']['u']['evento']['evento']),
                            'description' => $_SESSION['arrpostbuffer']['1']['u']['evento']['descricao']."\n https://sislaudo.laudolab.com.br/?_modulo=eventoti&_acao=u&idevento=".$_SESSION['arrpostbuffer']['1']['u']['evento']['idevento']."",
                            'issuetype' => $tipoJira,
                            'customfield_10044' => array("https://sislaudo.laudolab.com.br/?_modulo=eventoti&_acao=u&idevento=".$_SESSION['arrpostbuffer']['1']['u']['evento']['idevento'].""),
                            'customfield_10045' => array(str_replace(" ","",$infoclassificacao["classificacao"])),
                            'customfield_10046' => array($infoclassificacao['tipo']),
                        ),
                ));
                break;
            case 'Corretiva':
                $tipoJira = array('name' => 'Bug');
                $projeto = 'LEG';
                $rotafinal = _ROTASADICIONAISAPIINTEGRACAOJIRA["create"];
                // Define os campos da nova tarefa
                $data = array(
                    "URL" => $api_url,
                    "payload" => array(
                        "path" => _ROTASADICIONAISJIRA['issue'],
                        "username" => $username,
                        "token" => $api_token,
                        "jwt" => _JWTAPIJIRA,
                        'fields' => array(
                            'project' => array('key' => $projeto),
                            'summary' => "@".$_SESSION['arrpostbuffer']['1']['u']['evento']['idevento']." - ".strtoupper($_SESSION['arrpostbuffer']['1']['u']['evento']['evento']),
                            'description' => $_SESSION['arrpostbuffer']['1']['u']['evento']['descricao']."\n https://sislaudo.laudolab.com.br/?_modulo=eventoti&_acao=u&idevento=".$_SESSION['arrpostbuffer']['1']['u']['evento']['idevento']."",
                            'issuetype' => $tipoJira,
                            'customfield_10044' => array("https://sislaudo.laudolab.com.br/?_modulo=eventoti&_acao=u&idevento=".$_SESSION['arrpostbuffer']['1']['u']['evento']['idevento'].""),
                            'customfield_10045' => array(str_replace(" ","",$infoclassificacao["classificacao"])),
                            'customfield_10046' => array($infoclassificacao['tipo']),
                        ),
                ));
                break;
            case 'Configuração':
                $tipoJira = array('name' => 'Tarefa');
                $projeto = 'LEG';
                $rotafinal = _ROTASADICIONAISAPIINTEGRACAOJIRA["create"];
                // Define os campos da nova tarefa
                $data = array(
                    "URL" => $api_url,
                    "payload" => array(
                        "path" => _ROTASADICIONAISJIRA['issue'],
                        "username" => $username,
                        "token" => $api_token,
                        "jwt" => _JWTAPIJIRA,
                        'fields' => array(
                            'project' => array('key' => $projeto),
                            'summary' => "@".$_SESSION['arrpostbuffer']['1']['u']['evento']['idevento']." - ".strtoupper($_SESSION['arrpostbuffer']['1']['u']['evento']['evento']),
                            'description' => $_SESSION['arrpostbuffer']['1']['u']['evento']['descricao']."\n https://sislaudo.laudolab.com.br/?_modulo=eventoti&_acao=u&idevento=".$_SESSION['arrpostbuffer']['1']['u']['evento']['idevento']."",
                            'issuetype' => $tipoJira,
                            'customfield_10044' => array("https://sislaudo.laudolab.com.br/?_modulo=eventoti&_acao=u&idevento=".$_SESSION['arrpostbuffer']['1']['u']['evento']['idevento'].""),
                            'customfield_10045' => array(str_replace(" ","",$infoclassificacao["classificacao"])),
                            'customfield_10046' => array($infoclassificacao['tipo']),
                        ),
                ));
                break;
            case 'Infra':
                $tipoJira = array('name' => 'Tarefa');
                $projeto = 'INFRA';
                $rotafinal = _ROTASADICIONAISAPIINTEGRACAOJIRA["createissuekanban"];
                // Define os campos da nova tarefa
                $data = array(
                    "URL" => $api_url,
                    "payload" => array(
                        "path" => _ROTASADICIONAISJIRA['issue'],
                        "username" => $username,
                        "token" => $api_token,
                        "jwt" => _JWTAPIJIRA,
                        'fields' => array(
                            'project' => array('key' => $projeto),
                            'summary' => "@".$_SESSION['arrpostbuffer']['1']['u']['evento']['idevento']." - ".strtoupper($_SESSION['arrpostbuffer']['1']['u']['evento']['evento']),
                            'description' => $_SESSION['arrpostbuffer']['1']['u']['evento']['descricao']."\n https://sislaudo.laudolab.com.br/?_modulo=eventoti&_acao=u&idevento=".$_SESSION['arrpostbuffer']['1']['u']['evento']['idevento']."",
                            'issuetype' => $tipoJira,
                        ),
                ));
                break;
            
            default:
                $tipoJira = array('name' => 'Tarefa');
                $projeto = 'LEG';
                $rotafinal = _ROTASADICIONAISAPIINTEGRACAOJIRA["createissuekanban"];
                // Define os campos da nova tarefa
                $data = array(
                    "URL" => $api_url,
                    "payload" => array(
                        "path" => _ROTASADICIONAISJIRA['issue'],
                        "username" => $username,
                        "token" => $api_token,
                        "jwt" => _JWTAPIJIRA,
                        'fields' => array(
                            'project' => array('key' => $projeto),
                            'summary' => "@".$_SESSION['arrpostbuffer']['1']['u']['evento']['idevento']." - ".strtoupper($_SESSION['arrpostbuffer']['1']['u']['evento']['evento']),
                            'description' => $_SESSION['arrpostbuffer']['1']['u']['evento']['descricao']."\n https://sislaudo.laudolab.com.br/?_modulo=eventoti&_acao=u&idevento=".$_SESSION['arrpostbuffer']['1']['u']['evento']['idevento']."",
                            'issuetype' => $tipoJira,
                        ),
                ));
                break;
        }

        // Codifica os dados da nova tarefa em formato JSON
        $data_string = json_encode($data);

        // // Configura a requisição cURL para criar uma nova tarefa
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, _ROTAAPIINTEGRACAOJIRA.$rotafinal);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string))
        );
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$api_token");
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

        // Executa a requisição cURL para criar uma nova tarefa
        $response = curl_exec($ch);
        curl_close($ch);
        $decoded_response = json_decode($response, true);
        if($decoded_response["key"]){
            EventoController::inserirLog($_SESSION['arrpostbuffer']['1']['u']['evento']['idevento'],'evento','jira');
            $_SESSION['arrpostbuffer']['1']['u']['evento']['url'] = "https://laudolab.atlassian.net/browse/".$decoded_response['key'];
        }
    }
    montatabdef();
}

// Atualiza grupo de participantes do evento
if($_POST['_x_d_fluxostatuspessoa_idfluxostatuspessoa'])
{
    /**
     * Verifica se o usuario possui vinculo com algum
     * grupo inserido do evento e o vincula ao mesmo
     */
    if(EventoController::atualizarVinculoParticipanteEvento($_POST['_x_d_fluxostatuspessoa_idfluxostatuspessoa']))
        unset($_SESSION['arrpostbuffer']['x']);
}

if(!empty($_POST["_9999_d_arquivo_idarquivo"]))
{
    EventoController::deleterArquivoPorIdArquivo($_POST["_9999_d_arquivo_idarquivo"], 'evento');
}


if ($_SESSION['arrpostbuffer']['1'][$iu]['evento']['ideventotipo'] == 28){
    $_SESSION['arrpostbuffer']['1'][$iu]['evento']['prazo'] = "";
    $_POST["_1_".$_GET['_acao']."_evento_prazo"] = "";
    $prazo = "";
}

$prazosolicitante = $_SESSION['arrpostbuffer']['1']['u']['evento']['prazosolicitante'];
$prazoresponsavel = $_SESSION['arrpostbuffer']['1']['u']['evento']['prazoresponsavel'];
$prazoacordado = $_SESSION['arrpostbuffer']['1']['u']['evento']['prazoacordado'];
if(!empty($prazosolicitante) || !empty($prazoresponsavel)){
    if(($prazosolicitante == $prazoresponsavel && empty($prazoresponsavel)) || ($prazosolicitante > $prazoacordado) || ($prazoresponsavel < $prazoacordado)){
        $prazoAcordado = (($prazosolicitante == $prazoresponsavel) ? $prazoresponsavel : ($prazosolicitante > $prazoacordado)) ? $prazosolicitante : $prazoresponsavel;
        $_SESSION['arrpostbuffer']['1']['u']['evento']['prazoacordado'] = $prazoAcordado;
    }
}

retarraytabdef('fluxostatuspessoa');



?>