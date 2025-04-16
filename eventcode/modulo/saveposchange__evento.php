<? 
// require_once("../model/evento.php");

require_once(__DIR__."/../../api/notifitem/notif.php");

// QUERYS
require_once(__DIR__."/../../form/querys/_iquery.php");
require_once(__DIR__."/../../form/querys/evento_query.php");
require_once(__DIR__."/../../form/querys/tagreserva_query.php");
require_once(__DIR__."/../../form/querys/eventoobj_query.php");
require_once(__DIR__."/../../form/querys/eventoadd_query.php");
require_once(__DIR__."/../../form/querys/fluxo_query.php");
require_once(__DIR__."/../../form/querys/fluxostatuspessoa_query.php");
require_once(__DIR__."/../../form/querys/log_query.php");

// CONTROLLERS
require_once(__DIR__."/../../form/controllers/evento_controller.php");
require_once(__DIR__."/../../form/controllers/eventotipo_controller.php");
require_once(__DIR__."/../../form/controllers/fluxo_controller.php");

//Chama a Classe Evento
// $eventoclass = new EVENTO();
$idevento = $_GET['idevento'];
$_acao              = $_GET['_acao'];

//Pega os parâmetros via GET do calendario - (LTM - 24/07/2020)
$datacalendario		= $_GET['datacalendario'];
$iniciocalendario	= $_GET['inicio'];
$fimcalendario		= $_GET['fim'];

//$idEvento = $_POST["_1_".$_GET['_acao']."_evento_idevento"];
if ($_POST["_1_u_evento_idevento"]){
	$idEvento =  $_POST["_1_u_evento_idevento"];
}else if ($_POST["_x_i_fluxostatuspessoa_idmodulo"]){
	$idEvento =  $_POST["_x_i_fluxostatuspessoa_idmodulo"];
}else if ($_GET['idevento']){
	$idEvento = $_GET['idevento'];
}else if (!empty($_SESSION["_pkid"])){
	$idEvento = $_SESSION["_pkid"];	  
}

$status             = $_POST["_1_".$_GET['_acao']."_evento_status"];  
$idpessoa         	= $_POST["_1_".$_GET['_acao']."_evento_idpessoa"];
$nomeEvento         = $_POST["_1_".$_GET['_acao']."_evento_evento"];
$idmodulo           = $_POST["_1_".$_GET['_acao']."_evento_idmodulo"];
$modulo             = $_POST["_1_".$_GET['_acao']."_evento_modulo"];
$descricao          = $_POST["_1_".$_GET['_acao']."_evento_descricao"]; 
$jsonConfig         = $_POST["_1_".$_GET['_acao']."_evento_jsonconfig"];
$jsonResultado      = $_POST["_1_".$_GET['_acao']."_evento_jsonresultado"];
$ideventotipo      = $_POST["_1_".$_GET['_acao']."_evento_ideventotipo"];
$_x_d_eventoadd_ideventoadd = $_SESSION['arrpostbuffer']['x']['d']['eventoadd']['ideventoadd'];
$dataInicio         = $_POST["_1_".$_GET['_acao']."_evento_inicio"];
$horaInicio         = $_POST["_1_".$_GET['_acao']."_evento_iniciohms"];
$prazo         = $_POST["_1_".$_GET['_acao']."_evento_prazo"];

$dataFim            = $_POST["_1_".$_GET['_acao']."_evento_fim"];
$horaFim            = $_POST["_1_".$_GET['_acao']."_evento_fimhms"];

$fimdesemana        = $_POST["_1_".$_GET['_acao']."_evento_fimsemana"];
$repetirate         = $_POST["_1_".$_GET['_acao']."_evento_repetirate"];
$repetirevento      = $_POST["repetircheckbox"];
$peridiocidade      = $_POST["_1_".$_GET['_acao']."_evento_periodicidade"];

$eventotiporesp_obj     =$_POST["_x_i_fluxostatuspessoa_tipoobjeto"];
$idbojeto_obj     =$_POST["_x_i_fluxostatuspessoa_idobjeto"];
$inserepessoas = !empty($_POST['_x0_i_fluxostatuspessoa_idmodulo']);
$fluxounico = $_POST['fluxounico'];


$idfluxostatuspessoa      = $_POST['_x_d_fluxostatuspessoa_idfluxostatuspessoa'];

$link		      = $_POST["link"];

$_x_u_fluxostatuspessoa_idstatus = $_SESSION['arrpostbuffer']['x']['u']['fluxostatuspessoa']['idstatus'];
$_x_u_fluxostatuspessoa_idfluxostatuspessoa = $_SESSION['arrpostbuffer']['x']['u']['fluxostatuspessoa']['idfluxostatuspessoa'];

if(
    !empty($_POST["_1_i_evento_ideventotipo"]) AND 
    empty($_POST["_1_i_evento_idmodulo"]) AND
    empty($_POST["_1_i_evento_modulo"]) AND
    !empty($_SESSION["_pkid"])
)
{
    $atualizandoIdModuloDoEvento = SQL::ini(EventoQuery::atualizarIdModuloEModuloPorIdEvento(), [
        'setidmodulo' => "idmodulo = {$_SESSION["_pkid"]},",
        'idevento' => $_SESSION["_pkid"],
        'modulo' => 'evento'
    ])::exec();
} 

//Insere campos específicos de TI na tarefa de projeto
if ($ideventotipo == 40)
{
    EventoController::inserirCamposEspecificosDeTiPorIdEvento($idEvento);
}

//Faz a validação se o link em anexo é da Sislaudo, se for salva no banco evento.
if($link)
{
	$retorno = strrpos($link, $_SERVER['SERVER_NAME']);
	if($retorno){
		$linkExplode = explode("&", $link);$linkExplode = explode("&", $link);
        foreach($linkExplode AS $link)
        {
            if(preg_match('/_modulo/', $link)){
                $modulo = explode("_modulo=", $link);
		        $modulo = $modulo[1];
            } elseif(preg_match('/id/', $link) && !preg_match('/_idempresa/', $link)){
                $id = explode("=", $link);
                $id_modulo = $id[1];
            }
        }

        $search = 'report';
        if (preg_match("/\b{$search}\b/i", $link)) {
	        $modulo = $link;
        }
        
        if(empty($id_modulo)){
            $linkorig = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http"). "://".$_SERVER['SERVER_NAME'];
            $link = str_replace($linkorig."/?_modulo=","",$link);
            $modulo = $link ;
            $id_modulo = "";
        }elseif(!empty($id_modulo)){
            $setidmodulo = "idmodulo='".$id_modulo."',";
        }
        
       
	} else {
		die('Este link não pertence ao sistema');
	}

    $atualizandoIdModuloEModulo = SQL::ini(EventoQuery::atualizarIdModuloEModuloPorIdEvento(), [
        'setidmodulo' => $setidmodulo,
        'modulo' => $modulo,
        'idevento' => $idEvento
    ])::exec();
}

//SABER QUAL TOKEN INICIAL DO EVENTO
if (!empty($idEvento) && $fluxounico != 'Y') {
    $tokeninicial = EventoController::buscarTokenInicialDoEvento($idEvento);
    
} elseif($fluxounico == 'Y') {
    $tokeninicial = $_POST['_x0_i_fluxostatuspessoa_idfluxostatus'];

} elseif($ideventotipo){
    $tokenInicialEventoTipo = SQL::ini(FluxoQuery::buscarTokenInicialPorIdEventoTipo(), [
        'ideventotipo' => $ideventotipo
    ])::exec();

    if($tokenInicialEventoTipo->numRows())
    {
        $tokeninicial = $tokenInicialEventoTipo->data[0];
    }
}

//VERIFICA SE FOI INSERIDO O CRIADOR DO EVENTO NA LISTA DE PARTICIPANTES.. 
//SE NEGATIVO, INSERE O MESMO.

$pessoaInseridaNoEvento = EventoController::verificarSeCriadorFoiInseridoNoEvento($idEvento, $tokeninicial);

//INSERE AS PESSOAS DEFINIDAS EM PARTICIPANTES NO EVENTO TIPO APENAS NO INSERT.
if ($_acao == 'i' || $_POST["alteratipo"] == 1)
{
    FluxoController::insereParticipantes($idEvento, $ideventotipo, $tokeninicial, $pessoaInseridaNoEvento['idpessoa']);

    EventoController::atualizarParticipantesDoEvento($idEvento,$tokeninicial);

	if($_POST["alteratipo"] != 1 ){
		//Insere os campos do EventotipoAdd na tabela EventoAdd - LTM (08/07/2020) - Inclusão Minievento
        $inserindoCampos = SQL::ini(EventoAddQuery::inserirCamposPorIdEventoEIdEventoTipo(), [
            'idevento' => $idEvento,
            'ideventotipo' => $ideventotipo,
            'usuario' => $_SESSION["SESSAO"]["USUARIO"]
        ])::exec();
	}
	
	//Verifica se está setado no eventotipoadd o campo para somar as horas no Prazo. Caso contrário seta o campo como null para obrigar a ser preenchido. (LTM 17/07/2020 - 332052)
    $eventoTipo = EventoTipoController::buscarPorChavePrimaria($ideventotipo);

	if($eventoTipo['horasprazo'] == 'Y')
	{
		$prazo =  date('Y-m-d', strtotime('+'.$eventoTipo['quantidadehorasprazo'].' hours'));

        $atualizandoPrazo = SQL::ini(EventoQuery::atualizarPrazoInicioEFIm(), [
            'idevento' => $idEvento,
            'prazo' => "'$prazo'",
            'inicio' => "'$prazo'",
            'fim' => "'$prazo'"
        ])::exec();
	} else if($datacalendario == true)
    {

        if ($eventoTipo['prazo'] == 'Y'){
            if($eventoTipo["ideventotipo"] != 28){
                $iniciocalendario = implode('-', array_reverse(explode('/', $iniciocalendario)));
                $fimcalendario = implode('-', array_reverse(explode('/', $fimcalendario)));
        
                $atualizandoPrazo = SQL::ini(EventoQuery::atualizarPrazoInicioEFIm(), [
                    'idevento' => $idEvento,
                    'prazo' => "'$iniciocalendario'",
                    'inicio' => "'$iniciocalendario'",
                    'fim' => "'$fimcalendario'"
                ])::exec();
            }
            
        }

	}else if($eventoTipo['prazo'] == 'Y')
    {
        $atualizandoPrazo = SQL::ini(EventoQuery::atualizarPrazoInicioEFIm(), [
            'idevento' => $idEvento,
            'prazo' => 'NULL',
            'inicio' => 'NULL',
            'fim' => 'NULL'
        ])::exec();
	}
	
}else if($inserepessoas)
{
    $pattern = '/^x\d+$/';
    // Use preg_grep to filter array elements based on the pattern
    $resultArray = preg_grep($pattern, array_keys($_SESSION['arrpostbuffer']));

    foreach($resultArray as $k => $v){
        if($_SESSION['arrpostbuffer'][$v]['i']['fluxostatuspessoa']['tipoobjeto'] == 'imgrupo'){
            EventoController::atualizarParticipantesDoEvento($idEvento,$tokeninicial, $_SESSION['arrpostbuffer'][$v]['i']['fluxostatuspessoa']['tipoobjeto'], $_SESSION['arrpostbuffer'][$v]['i']['fluxostatuspessoa']['idobjeto']);
        }elseif($_SESSION['arrpostbuffer'][$v]['i']['fluxostatuspessoa']['tipoobjeto'] == 'pessoa'){
            $pessoasDoEvento = SQL::ini(FluxostatusPessoaQuery::buscarPessoasDeUmEvento(), [
                'idevento' => $idEvento
            ])::exec();
            
            foreach($pessoasDoEvento->data as $pessoa)
            {
                $eventoTipoDescr = $pessoa["eventotipo"];
        
                if($pessoa['assinar'] == 'Y')
                {
                    criaAssinatura($pessoa["idobjeto"], $pessoa["modulo"], $pessoa["idmodulo"]);
                }
            }
        
            if(!empty($_SESSION['arrpostbuffer'][$v]['i']['fluxostatuspessoa']['idobjeto'])){
                $notif = Notif::ini()
                    ->canal("browser")
                    ->conf([
                        "mod" => "evento",
                        "modpk" => "idevento", // 
                        "idmodpk" => $idEvento,
                        "title" => "Você foi adicionado em um evento de ".$eventoTipoDescr,
                        "corpo" => $nomeEvento ?? '',
                        "localizacao" => "dashboardsnippet",
                        "url" => "https://sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=".$idEvento
                    ])
                    ->addDest($_SESSION['arrpostbuffer'][$v]['i']['fluxostatuspessoa']['idobjeto'])
                    ->send();
            }
        }
    }
    
    
}

if($repetirate)
{
	$data =  explode('/', $dataInicio);
	if(empty($data)){
		$data =  explode('/', $prazo); 
	}

	$data = $data[2].'-'.$data[1].'-'.$data[0]; 
	$datarepetirate = explode('/',$repetirate);
	$datarepetirate = $datarepetirate[2].'-'.$datarepetirate[1].'-'.$datarepetirate[0]; 
    EventoController::deletarTodosEventosFilhosForaDoRangeDoEventoPai($idEvento, $data, $datarepetirate);
}

/* 
 * Valida se o campo $repetirate está desabilitado. Caso não esteja e tenha filhos, apagará os filhos. 
 * 24/01/2020 - Lidiane	
*/
$quantidadeDeEventos = SQL::ini(EventoQuery::buscarEventosFilhosPorIdEventoCount(), [
    'idevento' => $idEvento
])::exec();

$quantidadeDeEventos = $quantidadeDeEventos->numRows() ? $quantidadeDeEventos->data[0]['count'] : 0;

if($quantidadeDeEventos && !$repetirevento && $data)
{
	$data =  explode('/',$prazo);
	$data = $data[2].'-'.$data[1].'-'.$data[0]; 

	$datarepetirate = explode('/',$repetirate);
	$datarepetirate = $datarepetirate[2].'-'.$datarepetirate[1].'-'.$datarepetirate[0]; 

    EventoController::deletarTodosEventosFilhosForaDoRangeDoEventoPai($idEvento, $data, $datarepetirate, true);
	
	if($quantidadeDeEventos && !$repetirevento)
    {
        $atualizandoRepetirAte = SQl::ini(EventoQuery::atualizarRepetirAte(), [
            'repetirate' => 'NULL',
            'repetirevento' => 'N',
            'idevento' => $idEvento
        ])::exec();
	}
}

if ($prazo) {
    $fim = $prazo;
}

//Setando hora default inicio
if (empty($horaInicio)) {
    $horaInicio = '00:00';
}
//Setando hora default fim
if (empty($horaFim)) {
    $horaFim = '00:00';
}


if (strlen($horaInicio) == 5) {
    $dataInicio = validadatetime($dataInicio.' '.$horaInicio); 
} else {
     $dataInicio = validadatetime($dataInicio.' '.$horaInicio); 
}

if(strlen($horaFim) == 5){  
    $dataFim = validadatetime($dataFim.' '.$horaFim); 
}else{
    $dataFim = validadatetime($dataFim.' '.$horaFim); 
}

$evento = EventoController::buscarPorChavePrimariaPadrao($idEvento);

if ($dataInicio && $dataFim && $dataInicio > $dataFim) {
    die("Erro: Data inicial maior que data final:".date_format($dataInicio, 'Y-m-d h:m:s').' '.$dataFim);
} else {
    if ($repetirevento && !empty($repetirate)) 
    { 
        $repetirateDT = DateTime::createFromFormat('d/m/Y H:i:s', "$repetirate ".date('H:i:s', strtotime($horaInicio)));
        $intervaldias =date_diff(date_create($dataInicio),date_create($dataFim));
       
        //pega a quantidade de dias em inteiro para rodar no laço
        $intervaloEvento = $intervaldias->format('%a');
        //roda no for do primeiro dia até o ultimo dia do intervarlo
        $dataInicioOuPrazoFormatada = $prazo ? validadatetime($prazo.' '.$horaInicio) : $dataInicio;

        $dataInicioSubEventod = new DateTime($dataInicioOuPrazoFormatada);
        $dataInicioSubEventod=$dataInicioSubEventod->format('Ymd');
        
        $dataInicioSubEvento = new DateTime($dataInicioOuPrazoFormatada);            
       
        
        $dataFinalSubEvento =  new DateTime($dataFim);
      
	
        if ($peridiocidade == "ANUAL"      || 
            $peridiocidade == "BIANUAL"    || 
            $peridiocidade == "TRIANUAL"   || 
            $peridiocidade == "MENSAL"     || 
            $peridiocidade == "BIMESTRAL"  || 
            $peridiocidade == "TRIMESTRAL" || 
            $peridiocidade == "SEMESTRAL"  || 
            $peridiocidade == "SEMANAL"    || 
            $peridiocidade == "DIARIO") 
        {

            if ($peridiocidade == "DIARIO") {
                $tipoperiodicidade = 'P1D';
                $tipointervalo = 'dia';
            } elseif ($peridiocidade == "SEMANAL") {
                $tipoperiodicidade = 'P7D';
                $tipointervalo = 'dia';
            } elseif ($peridiocidade == "MENSAL") {
                $tipoperiodicidade = 'P1M';
                $tipointervalo = 'mes';
            } elseif ($peridiocidade == "BIMESTRAL") {
                $tipoperiodicidade = 'P2M';
                $tipointervalo = 'mes';
            } elseif ($peridiocidade == "TRIMESTRAL") {
                $tipoperiodicidade = 'P3M';
                $tipointervalo = 'mes';
            } elseif ($peridiocidade == "SEMESTRAL") {
                $tipoperiodicidade = 'P6M';
                $tipointervalo = 'mes';
            } elseif ($peridiocidade == "ANUAL") {
                $tipoperiodicidade = 'P1Y';
                $tipointervalo = 'ano';
            } elseif ($peridiocidade == "BIANUAL") {
                $tipoperiodicidade = 'P2Y';
                $tipointervalo = 'ano';
            } elseif ($peridiocidade == "TRIANUAL") {
                $tipoperiodicidade = 'P3Y';
                $tipointervalo = 'ano';
            }

		    $LI=0;

            while($dataInicioSubEvento <= $repetirateDT) 
            {
                $LI=$LI+1;

                $dataInicioD = new DateTime($dataInicioOuPrazoFormatada);
                $dataInicioD=$dataInicioD->format('d');
                
                $dataInicioDM = new DateTime($dataInicioOuPrazoFormatada);
                $dataInicioDM=$dataInicioDM->format('d/m');
   
                
                $dataInicioSubEventoM=$dataInicioSubEvento;
                $dataInicioSubEventoM=$dataInicioSubEventoM->format('m');
                
                $dataInicioSubEventoY=$dataInicioSubEvento;
                $dataInicioSubEventoY=$dataInicioSubEventoY->format('Y');
                    
                $dataInicioSubEventoD=$dataInicioSubEvento;
                $dataInicioSubEventoD=$dataInicioSubEventoD->format('d');
                
                $dataInicioSubEventoING =$dataInicioSubEvento;
                $dataInicioSubEventoING=$dataInicioSubEventoING->format('Y-m-d');
                
                $dataFinalSubEventoING =  $dataFinalSubEvento;
                $dataFinalSubEventoING=$dataFinalSubEventoING->format('Y-m-d');

                // finalDeSemana($dataInicioSubEventoING)
                if ($fimdesemana == 'N' && !EventoController::verificarSeEFimDeSemana($dataInicioSubEventoING)) {
                    $dados = [
                        'idevento' => $idEvento,
                        'evento' => $evento,
                        'datainicio' => $dataInicioSubEventoING,
                        'datafim' => $dataFinalSubEventoING,
                        'tokeninicial' => $tokeninicial
                    ];

                    EventoController::inserirEvento($idEvento, $evento, $dataInicioSubEventoING, $dataFinalSubEventoING,$tokeninicial);
                } elseif($fimdesemana == 'Y') {
                    EventoController::inserirEvento($idEvento, $evento, $dataInicioSubEventoING, $dataFinalSubEventoING,$tokeninicial);
                }

                //se a periodicidade for mensal, bimestral, trimestral ou semestral e a dataInicial for 31, subevento irá repetir no ultimo dia de cada mes
                if ($dataInicioD=='31' && substr($tipoperiodicidade, 2, 1) == 'M') {
                    
                    $diff = 0;
                    $intervalMes = ((int)substr($tipoperiodicidade, 1, 1));

                    for ($i=0; $i < $intervalMes; $i++) {
                        $diff += (int)date("t", mktime(0, 0, 0, ((int)$dataInicioSubEventoM)+1+$i, 1, $dataInicioSubEventoY));                        
                    }

                    $dataInicioSubEvento->modify('+'.$diff.' day');
                    $dataFinalSubEvento->modify('+'.$diff.' day');

                } else if ((int)$dataInicioD > 28 && 
                            substr($tipoperiodicidade, 2, 1) == 'M') {
                    
                    $intervalMes = ((int)substr($tipoperiodicidade, 1, 1));
                    $mesAtual = (int)$dataInicioSubEventoM;
                    
                    if (($intervalMes+$mesAtual) % 12 == 2) {

                        $diff = 0;

                        for ($i=0; $i < $intervalMes; $i++) {
                            $diff += (int)date("t", mktime(0, 0, 0, ((int)$dataInicioSubEventoM)+1+$i, 1, $dataInicioSubEventoY));                        
                        }

                        $diff += ((int)date("t", mktime(0, 0, 0, ((int)$dataInicioSubEventoM), 1, $dataInicioSubEventoY)))-((int)$dataInicioSubEventoD);

                        $dataInicioSubEvento->modify('+'.$diff.' day');
                        $dataFinalSubEvento->modify('+'.$diff.' day');

                        $bissexto = ((int)date("L", mktime(0, 0, 0, 1, 1, $dataInicioSubEventoY)));
                        $diffFevereiro = ((int)$dataInicioD) - 28 - $bissexto;
                    
                    } else {

                        $dataInicioSubEvento->add(new DateInterval($tipoperiodicidade));
                        $dataFinalSubEvento->add(new DateInterval($tipoperiodicidade));

                        if ($diffFevereiro > 0) {

                            $dataInicioSubEvento->modify('+'.$diffFevereiro.' day');
                            $dataFinalSubEvento->modify('+'.$diffFevereiro.' day');

                            $diffFevereiro = 0;
                        }                        
                    }                    
                } else if ($dataInicioDM == '29/02' && 
                            substr($tipoperiodicidade, 2, 1) == 'Y') {
                                
                    $intervalAno = ((int)substr($tipoperiodicidade, 1, 1));
                    $anoAtual = (int)$dataInicioSubEventoY;

                    $diff = 0;
                        
                    for ($i = 0; $i < $intervalAno; $i++) {
                        $bissexto = ((int)date("L", mktime(0, 0, 0, 1, 1, ((int)$dataInicioSubEventoY)+1+$i)));
                        $diff += (365+$bissexto);
                    }
                    
                    $dataInicioSubEvento->modify('+'.$diff.' day');
                    $dataFinalSubEvento->modify('+'.$diff.' day');   
                                 
                } else {
                    $dataInicioSubEvento->add(new DateInterval($tipoperiodicidade));
                    $dataFinalSubEvento->add(new DateInterval($tipoperiodicidade));
                }
            }
        }
    } else 
    {
        $removendoEventosFilhosSemStatus = EventoController::removerEventoFilhosSemStatus($idEvento);
    }
}

if(!empty($_x_d_eventoadd_ideventoadd)){
    EventoController::removerEventoObjVinculadoEvendoAdd($_x_d_eventoadd_ideventoadd);
}

$eventosFilhosSemStatus = SQL::ini(EventoQuery::buscarEventosFilhosSemStatusPorIdEvento(), [
    'idevento' => $idEvento
])::exec();

foreach($eventosFilhosSemStatus->data as $eventoFilho)
{
    $eventosAdicionais = SQL::ini(EventoQuery::buscarEventosAdicionais(), [
        'idevento' => $eventoFilho['idevento']
    ])::exec();

    foreach($eventosAdicionais->data as $evento)
    {
        EventoController::inserirEventoObj($evento, $eventoFilho['idevento']);
    }
    
    $eventosAdicionaisDeEventosFilhos = SQL::ini(EventoQuery::buscarEventosAdicionaisDeEventosFilhos(), [
        'idevento' => $eventoFilho['idevento']
    ])::exec();

    foreach($eventosAdicionaisDeEventosFilhos->data as $evento)
    {
        $deletandoEventoObj = SQL::ini(EventoObjQuery::deletarEventoObjPorIdEventoObj(), [
            'ideventoobj' => $evento['ideventoobj']
        ])::exec();
    }

    //Atualiza os Nomes dos Campos dos Eventos Filhos na tabela eventoadd - LTM (14/07/2020 - Minievento)
    $objetos = SQL::ini(EventoAddQuery::buscarObjetosPorIdEvento(), [
        'idevento' => $idEvento
    ])::exec();

    foreach($objetos->data as $objeto)
    {
        $atualizandoNomeCamposDosEventosFilhos = SQL::ini(EventoAddQuery::atualizarTituloDosCamposPorIdObjetoETipoObjeto(), [
            'titulo' => $objeto['titulo'],
            'usuario' => $_SESSION["SESSAO"]["USUARIO"],
            'idobjeto' => $objeto['idobjeto'],
            'tipoobjeto' => $objeto['objeto']
        ])::exec();
    }
}

if (!$idfluxostatuspessoa)
{
    if ((!empty($modulo) && $modulo != '') and $evento['assinar'] == 'Y')
    {
        $pessoasDoEvento = SQL::ini(FluxostatuspessoaQuery::buscarTodasPessoasDeUmEventoPorIdEvento(), [
            'idevento' => $idEvento
        ])::exec();
        
        foreach($pessoasDoEvento->data as $pessoa)
        {
            criaAssinatura($pessoa["idobjeto"], $pessoa["modulo"], $pessoa["idmodulo"]);
            $teste .= "  ".$pessoa["idobjeto"];
        }
    }
}

//Funçao para verificar o dia da semana recebe ANO MES E DIA 2011-07-11 junto
// DELETA TODOS OS EVENTOS FILHOS QUE ESTÃO FORA DO RANGE DO EVENTO PAI (REPETIR ATE)
	
/*
 * Centralizar a consulta de Módulo
 * Evitar falhas em relação à Módulos Vinculados
 * Complementar com as colunas necessárias diretamente na consulta
 */
/*
 * Verifica se o funcionário já está no evento, para evitar erro de duplicidade.
*/

if($_POST['travasala']=="Y"){
    $inicio=$_POST["_1_".$_acao."_evento_inicio"];  
    $iniciohms=$_POST["_1_".$_acao."_evento_iniciohms"];  
    $duracao=$_POST["_1_".$_acao."_evento_duracaohms"];  
    $fim=$_POST["_1_".$_acao."_evento_fim"];  
    $diainteiro=$_POST["_1_".$_acao."_evento_diainteiro"]; 
    $idtag=$_POST["_1_".$_acao."_evento_idequipamento"]; 
    
    if(!empty($idtag) and !empty($inicio) and !empty($iniciohms)  and !empty($fim) and !empty($idEvento))
    { 
        $inInicio = validadatetime($inicio.' '.$iniciohms); 

        if($diainteiro=='on'){  
            $_fimhms='23:59:00'; 
           
        }elseif(!empty($duracao)){
             $arrfim = explode(":",$duracao);    
            // Create a new \DateTime instance
            if (strlen($iniciohms) == 5) {
                $date = DateTime::createFromFormat('H:i', $iniciohms);   
            }else{
                $date = DateTime::createFromFormat('H:i:s', $iniciohms);   
            }
            // Modify the date
            $date->modify('+'.$arrfim['0'].' hours');
            // Modify the date
            $date->modify('+'.$arrfim['1'].' minutes');
            // Output
            $_fimhms= $date->format('H:i'); 
        }else{
            $_fimhms='23:59:00'; 
        }

        $inFim = validadatetime($fim.' '.$_fimhms); 
 
        $tagReserva = SQL::ini(TagReservaQuery::buscarTagReservaPorIdObjetoTipoObjeto(), [
            'idobjeto' => $idEvento,
            'tipoobjeto' => 'evento'
        ])::exec();

        if(!empty($tagReserva->data[0]['idtagreserva']))
        {
            $atualizandoTagReserva = SQL::ini(TagReservaQuery::atualizarPrazo(), [
                'idtagreserva' => $tagReserva->data[0]['idtagreserva'],
                'inicio' => $inInicio,
                'fim' => $inFim,
                'idtag' => $idtag,
                'usuario' => $_SESSION["SESSAO"]["USUARIO"],
                'alteradoem' => 'now()'
            ])::exec();
        } else 
        {
            $dadosTagReserva = [
                'idtag' => $idtag,
                'idobjeto' => $idEvento,
                'objeto' => 'evento',
                'inicio' => $inInicio,
                'fim' => $inFim,
                'trava' => 'Y',
                'status' => '',
                'criadopor' => $_SESSION["SESSAO"]["USUARIO"],
                'criadoem' => 'now()',
                'alteradopor' => $_SESSION["SESSAO"]["USUARIO"],
                'alteradoem' => 'now()',
            ];

            $inserindoTagReserva = SQL::ini(TagReservaQuery::inserir(), $dadosTagReserva)::exec();
        }
    } else
    {
        $deletandoTagReserva = SQL::ini(TagReservaQuery::deletarTagReservaPorIdEvento(), [
            'idevento' => $idEvento
        ])::exec();
    }
}

if($_acao == 'i')
{
    $dadosEventoCheckList = [
        'idevento' => $idEvento,
        'idempresa' => cb::idempresa()
    ];

    EventoController::inserirEventoChecklist($dadosEventoCheckList);
}

foreach($_POST as $chave => $valor) {
	if(preg_match("/(eventoAdd*)/", $chave, $res)){
		$eventoAdd = explode("_", $chave);
        EventoController::atualizarJsonConfigCampos($eventoAdd[1], $valor , $eventoAdd[2]);
	}
}
$horaconv = $_POST['evento_horasexec'];
$horalanc = $_POST['evento_acomphoras'];

if (!empty($horalanc) || !empty($horaconv)){
    
   $horasexec = EventoController::buscarValorEventoApontamento($idevento);
    EventoController::inserirHorasExec($horasexec , $idEvento);
}

if ($_SESSION['arrpostbuffer']['1']["i"]['evento']['ideventotipo'] == 28){
    $_SESSION['arrpostbuffer']['1'][$iu]['evento']['prazo'] = "";
    $_POST["_1_".$_GET['_acao']."_evento_prazo"] = "";
    $prazo = "";
}
?>