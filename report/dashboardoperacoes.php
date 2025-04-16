<?
require_once("../inc/php/validaacesso.php");

// QUERYS
require_once(__DIR__ . "/../form/querys/_iquery.php");
require_once(__DIR__ . "/../form/querys/_lp_query.php");

// CONTROLLERS
require_once(__DIR__ . "/../form/controllers/dashboardsnippet_controller.php");
require_once(__DIR__ . "/../form/controllers/pessoa_controller.php");
require_once(__DIR__ . "/../form/controllers/_lp_controller.php");
require_once(__DIR__ . "/../form/controllers/empresa_controller.php");
require_once(__DIR__ . "/../form/controllers/evento_controller.php");

$_idempresa = cb::idempresa();
$idLps = getModsUsr("LPS");

$dashboardFixo = DashboardSnippetController::buscarDashCardPorTipoObjetoEStatus('fixo');

$queryDashFixo = '';
foreach ($dashboardFixo as $item) {
    $queryDashFixo .= DashboardSnippetController::queryBuscarDashFixo($item['iddashcard']);
}

echo '<!-- ' . $queryDashFixo . '-->';

$dashboardCardPanelGrupo = DashboardSnippetController::buscarDashboardCardPanelGrupo($queryDashFixo);
$contadorR = count($dashboardCardPanelGrupo);
$i = 0;
$jsLps = implode(',', array_map(function ($item) {
    return $item['idlp'];
}, $lps));
$userPref = json_decode(PessoaController::buscarPreferenciaPessoa('dashboardsnippet', $_SESSION["SESSAO"]["IDPESSOA"]), true);
$corSistema = EmpresaController::buscarCorSistemaPorIdEmpresa();
$eventosAlerta = DashboardSnippetController::buscarEventosAlerta($_SESSION["SESSAO"]["IDPESSOA"]);

$jsonPref = '{}';

if (!empty($userPref)) {
    $jsonPref = json_encode($userPref);
}

$eventosDestaque = EventoController::buscarEventosDestaque($_SESSION['SESSAO']['IDPESSOA']);
$classAtivo = '';
?>
<!-- Css -->
<link rel="stylesheet" href="./../form/css/dashboardsnippet_css.css?_<?= date('dmYhms') ?>" type="text/Css" />
<div class="row m-0">
    <!-- * MONTA PAINEL DASH -->
    <div id="_col_md_control_" class="col-xs-12" id="dashboards" style="margin-top: 8px;">
        <div id="dashgrupo-conf-<?= $_SESSION['SESSAO']['IDPESSOA'] ?>">
            <?
            $grupo = false;
            $painel = false;
            /*
                * MONTA INDICADORES FIXOS NO CÓDIGO
                */
            foreach ($dashboardCardPanelGrupo as $v) {
                // Verifica se o mudou o grupo de uma linha para a outra
                // Caso sim, desenha um cabeçalho novo.
                // Desenha o Grupo
                if ($grupo != $v['iddashgrupo']) {

                    // Caso seja o primeiro grupo, não fechar as DIVs do grupo
                    if ($grupo != false) {
                        $painel = false;
            ?>
                                </div>
                            </div>
                        </div>
                    <? }

                    $grupo = $v['iddashgrupo'];

                    // $classAtivo = (!empty($userPref)
                    //     and !empty($userPref['grupo'][$v['iddashgrupo'] . "_" . $_SESSION['SESSAO']['IDPESSOA']])
                    //     and $userPref['grupo'][$v['iddashgrupo'] . "_" . $_SESSION['SESSAO']['IDPESSOA']] == 'Y') ? '' : 'display:none;';
                    $classAtivo = '';
?>

                    <div id="div-conf-<?= $grupo ?>-<?= $_SESSION["SESSAO"]["IDPESSOA"] ?>" class="panel panel-primary" style="border-left-color:<?= $v['corsistema'] ?>; border-left-width:3px; margin-bottom:30px;<?= $classAtivo ?>">
                        <div class="panel-body" style="background:#F3F3F3; padding:30px; border-radius:4px;">
                            <h3 class="text-on-pannel text-primary" style="background: <?= $v['corsistema'] ?>;color:#F3F3F3 !important;">
                                <strong class="text-uppercase"><?= $v['grupo_rotulo'] ?></strong>
                            </h3>
                <? }

                // Desenha cada Painel do grupo
                if ($painel != $v['panel_id']) {

                    // Caso seja o primeiro painel, não fechar as DIVs do grupo
                    if ($painel != false) { ?>
                            </div>
                        </div>
                    <? }

                    $painel = $v['panel_id'];
                ?>
<div class="col-md-12 painel idpainel-<?= $painel ?>">
    <i class="fa fa-refresh pointer btnRefresh" onclick="recarregarIndicadoresFixos(this, <?= $painel ?>, '<?= $v['iddashcardreal'] ?>')"></i>
    <h3 class="text-on-pannel text-primary">
        <strong class="text-uppercase"><?= $v['panel_title'] ?></strong>
    </h3>
    <? if ($v['panel_title'] == 'EVENTOS') { ?>
        <div id="dashNovoEvento" style="position: absolute;top: -12px;left: 130px;">
            <a href="javascript:window.location.href ='?_modulo=alerta'" class="fa fa-globe" style="float: left;border: 1px solid #ddd; padding: 4px; border-radius: 8px; background: #eee; margin-left: 4px;" title="Eventos"></a>
            <button class="btn btn-xs btn-primary" onclick="novaTarefa()" style="float: left; position: relative; margin-left: 10px;">
                Novo Evento
            </button>
        </div>
    <? } ?>
    <div class="col-md-12 painel-cards">
    <? }
                // Desenha cada Card do grupo
    ?>
    <div class="col-md-1 mb-4 pointer">
        <? if ($v['card_atraso_value'] > 0) { ?>
            <span class="bg-danger badge badgedash pointer" fixo onclick="popLink(this)" url="<?= $v['card_atraso_url'] ?>" urlmodal="<?= $v['card_url_modal'] ?>" iddashcard='<?= $v['iddashcard'] ?>' titulo="<?= $v['card_title'] ?>" subtitulo="<?= $v['card_title_sub'] ?>" cor="<?= $v['card_border_color'] ?>" modulo="<?= $v['modulo'] ?>" urljs=""><?= $v['card_atraso_value'] ?></span>
        <? } ?>
        <div class="card border-left-<?= $v['card_border_color'] ?> shadow h-100 py-2 bg-<?= $v['card_bg_class'] ?>" fixo style="border-radius:8px;" onclick="popLink(this)" url="<?= $v['card_url'] ?>" urlmodal="<?= $v['card_url_modal'] ?>" iddashcardreal="<?= $v['iddashcardreal'] ?>" iddashcard='<?= $v['iddashcard'] ?>' titulo="<?= $v['card_title'] ?>" subtitulo="<?= $v['card_title_sub'] ?>" cor="<?= $v['card_border_color'] ?>" modulo="<?= $v['modulo'] ?>" tipoobjeto="<?= $v['tipoobjeto'] ?>" idfluxostatus="<?= $v['idfluxostatus'] ?>" urljs="">
            <div class="card-body">
                <div class="no-gutters align-items-center">
                    <div class="col-md-12">
                        <div class="text-xs font-bold mb-1" style="color:#888;text-align:left;padding:0px 0px"><?= $v['card_title'] ?></div>
                    </div>
                </div>
                <div class="row m-0">
                    <div class="col-md-12">
                        <div class="h7 mb-0 font-weight_bold text_gray-800 titulo-<?= $v['card_color'] ?>" style="text-align:center;font-weight:bolder;">
                            <span class='card_value'><?= $v['card_value'] ?></span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div style="text-align:left;font-weight:bolder;"><span id='card_title_sub' class="bg-<?= $v['card_border_color'] ?>" card_titlesub><?= $v['card_title_sub'] ?></span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<? }
            /*
                * Desenha Grupo e painel se todos os cards values estiverem fixos
                */
            if ($contadorR < 1) { ?>
    <div id="div-conf-27-<?= $_SESSION["SESSAO"]["IDPESSOA"] ?>" class="panel panel-primary" style="border-left-color:<?= $corSistema ?>; border-left-width:3px; margin-bottom:30px;">
        <div class="panel-body" style="background:#F3F3F3; padding:30px; border-radius:4px;">
            <h3 class="text-on-pannel text-primary" style="background: <?= $corSistema ?>;color:#F3F3F3 !important;">
                <strong class="text-uppercase">MEU GERENCIAMENTO</strong>
            </h3>
            <div class="col-md-12 painel idpainel-27" style="height: 90px;">
                <!-- <i class="fa fa-refresh pointer btnRefresh" onclick="recarregarIndicadoresFixos(this, <?= $painel ?>, <?= $v['iddashcardreal'] ?>)"></i> -->
                <h3 class="text-on-pannel text-primary">
                    <strong class="text-uppercase">EVENTOS</strong>
                </h3>
                <div id="dashNovoEvento" style="position: absolute;top: -12px;left: 130px;">
                    <a href="javascript:window.location.href ='?_modulo=alerta'" class="fa fa-globe" style="float: left;border: 1px solid #ddd; padding: 4px; border-radius: 8px; background: #eee; margin-left: 4px;" title="Eventos"></a>
                    <button class="btn btn-xs btn-primary" onclick="novaTarefa()" style="float: left; position: relative; margin-left: 10px;">
                        Novo Evento
                    </button>
                </div>
            </div>
        </div>
    </div>
<? } ?>
<? if ($contadorR > 0) { ?>

    </div>
</div>
</div>
</div>
</div>
<? } ?>
</div>
</div>
<?
require_once(__DIR__ . "/../form/js/dashboardoperacoes_js.php");

$figuraCabecalhoRelatorioHTML = "";
$eventosDestaqueHTML = "";

if ($eventosDestaque) {
    $eventosDestaqueHTML = implode(" ", array_map(function ($item) {
        $eventosDestaqueCor = hexParaRgb($item['cor']);
        $eventosDestaqueStyle = "text-transform:uppercase;cursor:pointer;background-color: rgba({$eventosDestaqueCor[0]}, {$eventosDestaqueCor[1]}, {$eventosDestaqueCor[2]}, .2);border: 1px solid rgba({$eventosDestaqueCor[0]}, {$eventosDestaqueCor[1]}, {$eventosDestaqueCor[2]});";

        return '<div class="alert mb-0" role="alert" style="' . $eventosDestaqueStyle . '" onclick="javascript: janelamodal(&apos;../?_modulo=evento&_acao=u&idevento=' . $item['idevento'] . '&apos;)">
                    <div class="row">
                        <div class="col-md-1">' . date('d/m/Y', strtotime($item['criadoem'])) . '</div>
                        <div class="col-md-11"><b>' . $item['eventotipo'] . '</b>: ' . addslashes($item['evento']) . '</div>
                    </div>
                </div>';
    }, $eventosDestaque));
}

if ($eventosDestaqueHTML) {
?>
    <script>
        $('#_col_md_control_').prepend(`<?= $eventosDestaqueHTML; ?>`);
    </script>
<?
}
?>