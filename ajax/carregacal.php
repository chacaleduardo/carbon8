<?
require_once("../inc/php/validaacesso.php");
require_once("../inc/php/functions.php");

$jwt = validaTokenReduzido();

if($jwt["sucesso"] !== true){
    echo JSON_ENCODE([
        'error' => "Erro: Não autorizado."
    ]);
    die;
}

// CONTROLLERS
require_once(__DIR__."/../form/controllers/evento_controller.php");
require_once(__DIR__."/../form/controllers/formalizacao_controller.php");
require_once(__DIR__."/../form/controllers/tag_controller.php");

//pega a data do GET que e timestamp
$dataInicio	= filter_input(INPUT_GET, "start");
$dataFim	= filter_input(INPUT_GET, "end");
$eventotipo		= filter_input(INPUT_GET, "veventotipo");

$data = new DateTime($dataFim);
$dataFormatada = $data->modify('-1 month')->format("Y-m-d");

if (empty($eventotipo)) {
	$eventotipo = '';
}

$arrayTipo		= explode(",", $eventotipo);

foreach($arrayTipo as $key => $item)
{
	$arrayTipo[$key] = str_replace(['eventos-', 'evento-', 'tag-', 'tagtip-', 'tipoop-', 'op-'], '', $item);
}

$busca = [];

//reservas da formalizacao
	
if (in_array("'bttag'", $arrayTipo)) {

	// retira bttag do array
	$chave = array_search("'bttag'", $arrayTipo);
	if($chave!==false){
		unset($arrayTipo[$chave]);
	}
	
	$chave = array_search("'btequip'", $arrayTipo);
	if($chave!==false){
		unset($arrayTipo[$chave]);
	}
	
	$chave = array_search("'btsala'", $arrayTipo);
	if($chave!==false){
		unset($arrayTipo[$chave]);
	}
	
	$chave = array_search("'btprateleira'", $arrayTipo);
	if($chave!==false){
		unset($arrayTipo[$chave]);
	}
	//verifica se ainda tem tagtipo no array
	$result = count($arrayTipo);

	if($result>0)
	{	
		$idEventoTipo = implode(',',$arrayTipo);
		
		if($idEventoTipo)
		{
			$busca = TagController::buscarReservasPorIdTag($idEventoTipo, $dataFormatada);
		}
	}

}

if(in_array("'btop'", $arrayTipo))
{
	$chave = array_search("'btop'", $arrayTipo);

	if($chave!==false){
		unset($arrayTipo[$chave]);
	}
	
	$idEventoTipo = implode('\',\'',$arrayTipo);

	if($idEventoTipo)
	{
		$busca += FormalizacaoController::buscarFormalizacoesPorSubTipo($idEventoTipo, $dataFormatada);
	}
} 

if(in_array("'btevento'", $arrayTipo))
{
	$chave = array_search("'btevento'", $arrayTipo);
	if($chave!==false){
		unset($arrayTipo[$chave]);
	}

	$idEventoTipo = implode(',',$arrayTipo);

	if($idEventoTipo)
	{
		$busca += EventoController::buscarEventosPorIdEventoTipoIdPessoaEData($idEventoTipo, $_SESSION['SESSAO']['IDPESSOA'], $dataInicio, $dataFim);
	}
}

$camposEventoTipoComTituloComCode = [];

if($idEventoTipo)
{
	$camposEventoTipoComTituloComCode = EventoController::buscarCamposTituloEventoComCodePorIdEventoTipo($idEventoTipo);
}

$arr 	= array();

if($busca){
	$i 		= 0;
	$cor	= "A9A9A9";

	foreach($busca as $item){

		if($item['tipo'] == 'EVENTO')
		{
			$arreventos['id']		= $item["idevento"];
			$arreventos['title']	= ($camposEventoTipoComTituloComCode[$item['ideventotipo']] ? ($item['idevento'].'<br/>'.(EventoController::transformarSelectEmArray($camposEventoTipoComTituloComCode[$item['ideventotipo']]['code'])[$item['eventooriginal']]).'<br/>') : $item['evento']);
			if(empty($item["inicio"])){
				$arreventos['start']	= $item["datafim"] ." 18:00:00";
			}else{
				$arreventos['start']	= $item["inicio"]." ".$item["iniciohms"];
			}
			$arreventos['end']		= $item["fim"]." ".$item["fimhms"];
			$arreventos['url']		= "?_modulo=evento&_acao=u&idevento=".$item["idevento"]."&dataclick=".$item["fim"];
			$arreventos['allDay']	= $item["diainteiro"] == 'Y' ? true : false;
			$arreventos['color']	= $item["cor"];	
		}elseif($item['tipo'] == 'FORMALIZACAO'){
			$arreventos['id']		= $item["idevento"];
			$arreventos['title']	= $item["evento"];
			$arreventos['start']	= $item["inicio"]." ".$item["iniciohms"];
			$arreventos['end']		= $item["fim"]." ".$item["fimhms"];
			$arreventos['url']		= "?_modulo=formalizacao&_acao=u&idformalizacao=".$item["idevento"]."&dataclick=".$item["fim"];
			$arreventos['allDay']	= false;
			$arreventos['color']	= $item["cor"];	
		}else{
			if($item['objeto']=='loteativ'){
				
				$idlote =traduzid('loteativ', 'idloteativ', 'idlote', $item['idobjeto']);
				if($idlote){			
					$arreventos['url']		= "?_modulo=formalizacao&_acao=u&idformalizacao=".$idlote;				
				}else{
					$arreventos['url']		= "?_modulo=evento&_acao=u&idevento=".$item["idevento"]."&dataclick=".$item["fim"];				
				}
			}else{
				$arreventos['url']		= "?_modulo=evento&_acao=u&idevento=".$item["idobjeto"]."&dataclick=".$item["fim"];	
			}
			
			$arreventos['id']		= $item["idobjeto"];	
			$arreventos['title']	= $item["evento"] ?? '';
			$arreventos['start']	= $item["inicio"]." ".$item["iniciohms"];
			$arreventos['end']		= $item["fim"]." ".$item["fimhms"];		
			$arreventos['allDay']	= false;
			$arreventos['color']	= $item["cor"];	
		}
		
		$arr[$i]=$arreventos;
		$i=$i+1;
		
	}
}
echo json_encode($arr,JSON_UNESCAPED_UNICODE);
