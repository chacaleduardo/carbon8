<?
require_once("../inc/php/validaacesso.php");
require_once("../inc/php/permissao.php");
require_once(__DIR__ . "/controllers/calendario_controller.php");
require_once(__DIR__ . "/controllers/eventotipo_controller.php");

// $_GET["exibirtodos"]

$exibirTodos = 'Y';

if ($exibirTodos == 'Y') {
    $clausula = '';
} else {
    $dt1 = new DateTime("-3 month");
    $dt2 = new DateTime("+3 month");
    $date1 = $dt1->format("Y-m-d");
    $date2 = $dt2->format("Y-m-d");
    $clausula = "and et.criadoem between '" . $date1 . "' and '" . $date2 . "'";
}

function carregaEventoTipo($tipo)
{
    global $clausula;

    $eventoTipos =  EventoTipoController::carregarEventoTipoPorTipoEIdPessoa('calendario', $_SESSION["SESSAO"]["IDPESSOA"]);
    $eventoTiposDaPessoa = CalendarioController::buscarEventoTiposPorIdPessoaClausulaEGetIdEmpresa($_SESSION["SESSAO"]["IDPESSOA"], $clausula, getidempresa('e.idempresa', 'evento'));

    if (count($eventoTipos)) {
        if ($tipo === "menu") {
            $aux = array();

            foreach ($eventoTipos as $tipo) {
                $aux[$tipo["tipo"]] = [$tipo["id"], $tipo["tipo"]];
            }

            foreach ($eventoTiposDaPessoa as $tipo) {
                if (!(array_key_exists($tipo["tipo"], $aux))) {
                    $aux[$tipo["tipo"]] = [$tipo["id"], $tipo["tipo"]];
                }
            }
            ksort($aux);

            echo "<ul class='w-100 pl-0 lista-eventos lista-item hidden'>";
            foreach ($aux as $key => $val) {
                echo "<li class='tipoItem pointer list-group-item evento' id='evento-{$val[0]}' style='font-size: 10px; width: 100%; padding: 5px' onclick='altpsq(`evento`,{$val[0]})' data-tipojson='evento'>{$val[1]}</li>";
            }
            echo "</ul>";
        } else {
            if ($tipo === "modal") {
                foreach ($eventoTipos as $tipo) {
                    echo "<button class='category-event-sis'>
                        <span class='category-event-sis-text pointer' id='eventoTipo" . $tipo['id'] . "' 
                            onclick='criaEvento({$tipo['id']})';>" . $tipo['tipo'] . "
                        </span>
                    </button>";
                }
            }
        }
    }
}

$equipamentos = CalendarioController::buscarTagClassPorIdClassGetIdEmpresaECalendario(1, getidempresa('t.idempresa', 'tag'), 'Y');
$salas = CalendarioController::buscarTagClassPorIdClassGetIdEmpresaECalendario(2, getidempresa('t.idempresa', 'tag'), 'Y');
$veiculos = CalendarioController::buscarTagClassPorIdClassGetIdEmpresaECalendario(3, getidempresa('t.idempresa', 'tag'), 'Y');
$prateleiras = CalendarioController::buscarTagClassPorIdClassGetIdEmpresaECalendario(4, getidempresa('t.idempresa', 'tag'), 'Y');
$ops = CalendarioController::buscarFormalizacaoSubTiposPorShare(share::otipo('cb::usr')::formalizacaosubtipoCbUserIdempresa("f.idformalizacaosubtipo"));

function carregaTagTipo1($tags)
{
    // $tags = CalendarioController::buscarTagClassPorIdClassGetIdEmpresaEShare(1, getidempresa('t.idempresa', 'tag'), share::otipo('cb::usr')::compartilharCbUserTag("t.idtag"));

    if (count($tags)) {
        echo "<ul class='lista-item pl-0 w-100 hidden nivel-3'>";
        foreach ($tags as $tag) {
            echo "  <li class='tipoItem pointer list-group-item btequip flex align-items-center justify-between' id='tipotag-{$tag['idtag']}' style='font-size: 10px; width: 100%; padding: 5px' onclick='altpsq(`tipotag`,{$tag['idtag']})' data-tipojson='tipotag'>        
                        <div class='col-md-8 p-0 word-break-all'>{$tag['descricao']}</div><div class='badge badge-primary col-md-4 px-0 font-xs'>{$tag['tag']}</div>
                    </li>";
        }

        echo "</ul>";
    }
}
function carregaTagTipo2($tags)
{
    // $tags = CalendarioController::buscarTagClassPorIdClassGetIdEmpresaEShare(2, getidempresa('t.idempresa', 'tag'), share::otipo('cb::usr')::compartilharCbUserTag("t.idtag"));

    if (count($tags)) {
        echo "<ul class='lista-item pl-0 w-100 hidden nivel-3'>";
        foreach ($tags as $tag) {
            echo "<li class='tipoItem pointer list-group-item btsala d-flex flex-col' id='tagtip-{$tag['idtag']}' style='font-size: 10px; width: 100%; padding: 5px' onclick='altpsq(`tagtip`,{$tag['idtag']})' data-tipojson='tagtip'>
                        <div class='col-xs-12 p-0 word-break-all mb-2'>{$tag['descricao']}</div>
                        <div class='badge badge-primary col-md-4 ml-auto px-0 font-xs'>{$tag['tag']}</div>
                    </li>";
        }
        echo "</ul>";
    }
}
function carregaTagTipo3($tags)
{
    // $tags = CalendarioController::buscarTagClassPorIdClassGetIdEmpresaEShare(3, getidempresa('t.idempresa', 'tag'), share::otipo('cb::usr')::compartilharCbUserTag("t.idtag"));

    if (count($tags)) {
        echo "<ul class='lista-item pl-0 w-100 hidden nivel-3'>";
        foreach ($tags as $tag) {
            echo "<li class='tipoItem pointer list-group-item btveiculo flex align-items-center justify-between' id='tagveiculo-{$tag['idtag']}' style='font-size: 10px; width: 100%; padding: 5px' onclick='altpsq(`tagveiculo`,{$tag['idtag']})' data-tipojson='tagveiculo'>
                        <div class='col-md-8 p-0 word-break-all'>{$tag['descricao']}</div><div class='badge badge-primary col-md-4 px-0 font-xs'>{$tag['tag']}</div>
                    </li>";
        }
        echo "</ul>";
    }
}

function carregaTagTipo4($tags)
{
    // $tags = CalendarioController::buscarTagClassPorIdClassGetIdEmpresaEShare(4, getidempresa('t.idempresa', 'tag'), share::otipo('cb::usr')::compartilharCbUserTag("t.idtag"));

    if (count($tags)) {
        echo "<ul class='lista-item pl-0 w-100 hidden nivel-3'>";
        foreach ($tags as $tag) {
            echo "<li class='tipoItem pointer list-group-item btprateleira  flex align-items-center justify-between' id='tagprateleira-{$tag['idtag']}' style='font-size: 10px; width: 100%; padding: 5px' onclick='altpsq(`tagprateleira`,{$tag['idtag']})' data-tipojson='tagprateleira'>
                        <div class='col-md-8 p-0 word-break-all'>{$tag['descricao']}</div><div class='badge badge-primary col-md-4 px-0 font-xs'>{$tag['tag']}</div>
                    </li>";
        }
        echo "</ul>";
    }
}

function carregaTipoOp($formalizacaoSubTipos)
{
    // $formalizacaoSubTipos = CalendarioController::buscarFormalizacaoSubTiposPorShare(share::otipo('cb::usr')::formalizacaosubtipoCbUserIdempresa("f.idformalizacaosubtipo"));

    if (count($formalizacaoSubTipos)) {
        echo "<ul class='lista-item pl-0 w-100 nivel-3'>";
        foreach ($formalizacaoSubTipos as $subTipo) {
            echo "<li class='tipoItem pointer list-group-item listaop' id='tipoop-" . str_replace('/', '', $subTipo['subtipo']) . "' style='font-size: 10px; width: 100%; padding: 5px' onclick='altpsq(`tipoop`, `" . str_replace('/', '', $subTipo['subtipo']) . "`)' data-tipojson='tipoop'>" . strtoupper($subTipo['descricao']) . "</li>";
        }
        echo "</ul>";
    }
}

?>
<!-- CSS -->
<link href="/form/css/calendario_css.css?_<?= date("dmYhms") ?>" rel="stylesheet" />
<link href="/form/calendar_microsoft/calendar.css?_<?= date("dmYhms") ?>" rel="stylesheet" />
<link href="/form/calendar_microsoft/modals/modals.css?_<?= date("dmYhms") ?>" rel="stylesheet" />

<!-- FullCalendar CSS -->


<? require(__DIR__ . "/calendar_microsoft/calendar.php"); ?>
<?// require(__DIR__ . "/js/calendario_js.php"); ?>