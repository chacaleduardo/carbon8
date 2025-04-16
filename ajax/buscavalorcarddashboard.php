<?
require_once("../inc/php/functions.php");

// QUERYS
require_once(__DIR__ . "/../form/querys/_iquery.php");
require_once(__DIR__ . "/../form/querys/vw8eventopessoa_query.php");
require_once(__DIR__ . "/../form/querys/vw8assinaturaspendentes_query.php");
require_once(__DIR__ . "/../form/querys/_mtotabcol_query.php");
require_once(__DIR__ . "/../form/querys/dashcard_query.php");
require_once(__DIR__ . "/../form/querys/dashpanel_query.php");
require_once(__DIR__ . "/../form/querys/dashboard_query.php");
require_once(__DIR__ . "/../form/querys/empresa_query.php");

// CONTROLLERS
require_once(__DIR__ . "/../form/controllers/dashboardsnippet_controller.php");

$_inspecionar_sql = ($_GET["_inspecionar_sql"] == "Y") ? true : false;
$idDashCard = $_REQUEST["iddashcard"];

if (empty($idDashCard)) {
    die;
}

function atualizaIndicadorFixo($idashcard)
{
    global $_inspecionar_sql;

    // Pega a tabela do dashcard
    $dashcard = DashboardSnippetController::buscarDashCardPorIdDashCard($idashcard)[0];

    $tab = $dashcard['tab'];
    $code = $dashcard['code'];
    $cardurlmodal = $dashcard['cardurlmodal'];
    $modulo = $dashcard['modulo'];

    $and = " and ";
    $clausula = "";
    $group = "";

    //PEGA CLAUSULAS DA TABELA DO DASH
    $clausula .= DashboardSnippetController::buscarTabelaFiltrosDashboard($idashcard, "e.");

    //PEGA CLAUSULAS DO INDICADOR DO DASH
    $arrcl = DashboardSnippetController::buscarClausulaCodigoDashboard($code, "N", "e.");
    $clausula .= $arrcl['clausula'];
    $group = $arrcl['group'];

    // Pega a primary key da tabela do card
    $colunaChavePrimaria = SQL::ini(_MtotabcolQuery::buscarChavePrimariaPorTabela(), [
        'tabela' => $tab
    ])::exec();

    $col = $colunaChavePrimaria->data[0]['col'];
    $infoDashCard = SQL::ini(DashCardQuery::buscarInformacaoDashCardPorTabelaEIdDashCard(), [
        'tabela' => $tab,
        'modulo' => $modulo,
        'coluna' => $col,
        'iddashcard' => $idashcard,
        'clausula' => $clausula
    ])::exec();

    if ($_inspecionar_sql) echo $infoDashCard->sql();

    return $infoDashCard->data;
}

if ($_REQUEST["fixo"] == 'Y') {

    $res_alerta = null;

    if ($_REQUEST["idDashCardReal"] == "N") {
        $eventosCard = atualizaIndicadorFixo($_REQUEST["iddashcard"]);
    } else {

        if ($_REQUEST["idDashCardReal"] == 2174) {
            if($_REQUEST["iddashcard"] == 28){
                $cardQuery = SQL::mount(Vw8EventoPessoaQuery::buscarSuporteTecnologiaCardFixo(), [
                    'idempresa' => cb::idempresa(),
                    'idpessoa' => $_SESSION["SESSAO"]["IDPESSOA"],
                    'iddashcard' => $_REQUEST["iddashcard"]
                ]);
            }else{
                $cardQuery = SQL::mount(Vw8EventoPessoaQuery::buscarEventosCardFixo(), [
                    'idempresa' => cb::idempresa(),
                    'idpessoa' => $_SESSION["SESSAO"]["IDPESSOA"],
                    'iddashcard' => $_REQUEST["iddashcard"]
                ]);
            }

            $res_alerta = SQL::ini(Vw8EventoPessoaQuery::buscarEventoCardPorIdDashCard(), [
                'idpessoa' => $_SESSION["SESSAO"]["IDPESSOA"],
                'iddashcard' => $_REQUEST["iddashcard"]
            ])::exec();

            if ($_inspecionar_sql) {
                echo ('<pre>' . $res_alerta->sql() . '</pre>');
            }
        } else if ($_REQUEST["idDashCardReal"] == 2252) {
            $cardQuery = SQL::mount(Vw8AssinaturasPendentes::buscarCardsAssinaturaPendente(), [
                'idempresa' => cb::idempresa(),
                'idpessoa' => $_SESSION["SESSAO"]["IDPESSOA"]
            ]);
        } elseif ($_REQUEST["idDashCardReal"] == 3991) {
            $cardQuery = SQL::mount(DashCardQuery::buscarDashboardSolicitacoes(), [
                'idpessoa' => $_SESSION["SESSAO"]["IDPESSOA"],
                'idempresa' => cb::idempresa()
            ]);
        }

        $eventosCard = SQL::ini(DashCardQuery::buscarCardComModuloVinculadoPorIdEmpresa(), [
            'querydinamicacard' => $cardQuery,
            'idempresa' => cb::idempresa()
        ])::exec();

        if ($_inspecionar_sql) {
            echo ('<pre>' . $eventosCard->sql() . '</pre>');
        }

        $eventosCard = $eventosCard->data;
    }
    $arrResposta = [
        'qtd'           => $eventosCard[0]['card_value'],
        'qtdAtraso'     => $eventosCard[0]['card_atraso_value'],
        'link'          => $eventosCard[0]['card_url'],
        'linkAtraso'    => $eventosCard[0]['card_atraso_url'],
        'modulo'        => $eventosCard[0]['modulo'],
        'cor'           => $eventosCard[0]['card_color'],
        'corBorda'      => $eventosCard[0]['card_border_color'],
        'titulo'        => $eventosCard[0]['card_title_modal'],
        'urlmodal'      => $eventosCard[0]['card_url_modal'],
        'urljs'         => '',
    ];

    if ($res_alerta->data) {
        $arrResposta['qtdCinza'] = $res_alerta->data[0]['value'];
        $arrResposta['linkCinza'] = $res_alerta->data[0]['url'];
    }

    if (empty($arrResposta['qtd'])) {
        $arrResposta['qtd'] = 0;
    } else {

        if ($v_numsize === true) {
            //$arrResposta['qtd'] = numsize($arrResposta['qtd'],1);
        }
    }

    if (empty($arrResposta['qtdAtraso'])) {
        $arrResposta['qtdAtraso'] = 0;
    }
    echo json_encode($arrResposta);
} else {

    function extractLinkAndIds($url, $virgula)
    {
        $matches = array();
        preg_match("/\[(.*?)\]/", $url, $matches);

        $arrRet = array();

        if (!empty($matches[1]))
            $arrRet['ids'] = $virgula . $matches[1];
        else
            $arrRet['ids'] = "";

        if (!empty($matches[0]))
            $arrRet['lnk'] = str_replace($matches[0], "", $url);
        else
            $arrRet['lnk'] = "";

        return $arrRet;
    }

    $habilitarMatriz = (!empty($_GET["habilitarMatriz"])) ? $_GET["habilitarMatriz"] : cb::habilitarMatriz();

    if ($habilitarMatriz == 'Y') {
        $idEmpresasVinculadas = SQL::ini(EmpresaQuery::buscarIdEmpresasVinculadasPorIdObjetoEObjeto(), [
            'idobjeto' => $_SESSION["SESSAO"]["IDPESSOA"],
            'objeto' => 'pessoa'
        ])::exec();

        if ($_inspecionar_sql) {
            echo ('<pre>' . $idEmpresasVinculadas->sql() . '</pre>');
        }

        $wIdempresa = ($idEmpresasVinculadas->data[0]['idempresa'] == 0) ? cb::idempresa() : $idEmpresasVinculadas->data[0]['idempresa'];
    } else {
        $idEmpresasVinculadas = SQL::ini(EmpresaQuery::buscarEmpresaMatriz(), [
            'idempresa' => cb::idempresa()
        ])::exec();

        if ($_inspecionar_sql) {
            echo ('<pre>' . $idEmpresasVinculadas->sql() . '</pre>');
        }

        $wIdempresa = ($idEmpresasVinculadas->data[0]['idempresa'] == 0) ? cb::idempresa() : $idEmpresasVinculadas->data[0]['idempresa'];
    }

    $dashcard = SQL::ini(DashCardQuery::buscarPorChavePrimaria(), [
        'pkval' => $idDashCard
    ])::exec();

    if ($_inspecionar_sql) echo ('<pre>' . $dashcard->sql() . '</pre>');

    if ($dashcard->data[0]['cron'] == 'N') {
        $lps = $_REQUEST["lps"];

        $dashPanel = SQL::ini(DashPanelQuery::buscarGruposDashPanelPorIdLpIdDashCardEIdEmpresa(), [
            'idempresa' => $wIdempresa,
            'lps' => $lps,
            'iddashcard' => $idDashCard
        ])::exec();

        if ($_inspecionar_sql) {
            echo ('<pre>' . $dashPanel->sql() . '</pre>');
        }

        $unionall = '';
        $clausula = '';
        $queryDinamicaCard = '';
        $mask = null;

        foreach ($dashPanel->data as $row) {
            if (!empty($row['tab'])) {
                $calc = !empty($row['tipocalculo']) ? $row['tipocalculo'] . "(" . $row['colcalc'] . ")" : 'count(1)';

                //PEGA CLAUSULAS DA TABELA DO DASH
                $clausula .= DashboardSnippetController::buscarTabelaFiltrosDashboard($idDashCard, "c.");

                //PEGA CLAUSULAS DO INDICADOR DO DASH
                $arrcl = DashboardSnippetController::buscarClausulaCodigoDashboard($row['code'], "N", "c.");
                $clausula .= $arrcl['clausula'];
                $group = $arrcl['group'];

                $queryDinamicaCard .=  $unionall . " SELECT 
                                    " . $calc . " as card_value,
                                    '0' as card_atraso_value,
                                    " . $row['cardurl'] . " as card_url,
                                    '" . addslashes($row['cardurlmodal']) . "' as card_url_modal,
                                    '" . addslashes($row['modulo']) . "' as modulo,
                                    " . $row['cardatrasourl'] . " as card_atraso_url,
                                    " . $row['cardcolor'] . " as card_color,
                                    " . $row['cardbordercolor'] . " as card_border_color,
                                    '" . addslashes($row['cardtitlemodal']) . "' as card_title_modal,
                                    '" . addslashes($row['cardtitle']) . "' as cardtitle,
                                    '" . addslashes($row['statuscard']) . "' as statuscard,
                                    " . ($row['grupo_ordem'] * 1) . " as grupo_ordem,
                                    '" . addslashes($row['iddashgrupo']) . "' as iddashgrupo,
                                    '" . addslashes($row['idempresa']) . "' as idempresa,
                                    " . ($row['panel_ordem'] * 1) . " as panel_ordem,
                                    " . ($row['card_ordem'] * 1) . " as card_ordem,
                                    (if(dc.tipoobjeto = 'fluxostatus', dc.objeto, '')) as idfluxostatus
                                    from " . $row['tab'] . " c
                                    join dashcard dc on(dc.iddashcard = $idDashCard)
                                    where 1
                                    " . $clausula . "
                                    and c.idempresa in (" . $wIdempresa . ")
                                    " . $group . "";

                $unionall = ' union all ';
            }
            if (!$mask) {
                $mask = $row['mask'];
            }
        }

        $cards = SQL::ini(DashCardQuery::buscarCardComModuloVinculadoPorIdEmpresa(), [
            'idempresa' => cb::idempresa(),
            'querydinamicacard' => $queryDinamicaCard
        ])::exec();

        if ($_inspecionar_sql) {
            echo ('<pre>' . $cards->sql() . '</pre>');
        }

        $arrResposta = array(
            'qtd'           => $cards->data[0]['card_value'],
            'qtdAtraso'     => $cards->data[0]['card_atraso_value'],
            'link'          => $cards->data[0]['card_url'],
            'linkAtraso'    => $cards->data[0]['card_atraso_url'],
            'modulo'        => $cards->data[0]['modulo'],
            'cor'           => $cards->data[0]['card_color'],
            'titulo'        => $cards->data[0]['card_title_modal'],
            'cardTitulo'    => $cards->data[0]['cardtitle'],
            'urlmodal'      => $cards->data[0]['card_url_modal'],
            'urljs'         => '',
            'statuscard'    => $cards->data[0]['statuscard'],
            'corBorda'      => $cards->data[0]['card_border_color'],
            'masc'      => $mask,
            'idfluxostatus' => $cards->data[0]['idfluxostatus']
        );

        if (empty($arrResposta['qtd'])) $arrResposta['qtd'] = 0;
        echo json_encode($arrResposta);
    } else {
        $dashboard = SQL::ini(DashboardQuery::buscarDashboardPorIdDashCardEIdEmpresa(), [
            'idempresa' => $wIdempresa,
            'iddashcard' => $idDashCard
        ])::exec();

        //buscar os não lidos
        $res_alerta = SQL::ini(Vw8EventoPessoaQuery::buscarEventoCardPorIdDashCard(), [
            'idpessoa' => $_SESSION["SESSAO"]["IDPESSOA"],
            'iddashcard' => $_REQUEST["iddashcard"]
        ])::exec();

        if ($_inspecionar_sql) {
            echo ('<pre>' . $res_alerta->sql() . '</pre>');
        }

        if ($_inspecionar_sql) {
            echo ('<pre>' . $dashboard->sql() . '</pre>');
        }

        $arrResposta = array(
            'qtd'           => 0,
            'qtdAtraso'     => 0,
            'link'          => '',
            'linkAtraso'    => '',
            'modulo'        => '',
            'cor'           => '',
            'corBorda'      => '',
            'titulo'        => '',
            'cardTitulo'    => '',
            'statuscard'    => '',
            'cardSubTitulo' => '',
            'urlmodal'      => '',
            'urljs'         => '',
            'modulofiltros' => '',
            'masc'          => '',
            'idfluxostatus' => ''
        );

        if ($res_alerta->data) {
            $arrResposta['qtdCinza'] = $res_alerta->data[0]['value'];
            $arrResposta['linkCinza'] = $res_alerta->data[0]['url'];
        }
        /*Para testar usei assim
        $arrResposta['qtdCinza'] = 100;
        $arrResposta['linkCinza'] = '_modulo=eventoti&idevento=[586107,586108,586109,586110,586111,586119,586120,586121,586122,586123,586127,586128]';
        */

        $strLink = '';
        $linkIds = '';

        $strLinkAtraso = '';
        $linkIdsAtraso = '';

        $virgula = '';
        $virgulaAtraso = '';

        $changeLink = true;
        $changeLinkAtraso = true;

        $dashboardArr = $dashboard->data;

        if ($dashboardArr) {
            foreach ($dashboardArr as $rw) {
                $orderBy = '';

                if ($rw['cardordenacao'])
                    $orderBy = "&_ordcol={$rw['cardordenacao']}&_orddir={$rw['cardsentido']}&";


                $arrResposta['qtd']         += $rw['card_value'];
                $arrResposta['qtdAtraso']   += $rw['card_atraso_value'];

                if ($arrResposta['modulo'] == '' and !empty($rw['modulo']))
                    $arrResposta['modulo'] = $rw['modulo'];
                if ($arrResposta['cor'] == '' and !empty($rw['card_color']) and $rw['card_color'] != 'secondary')
                    $arrResposta['cor'] = $rw['card_color'];
                if ($arrResposta['titulo'] == '' and !empty($rw['card_title_modal']))
                    $arrResposta['titulo'] = $rw['card_title_modal'];
                if ($arrResposta['cardTitulo'] == '' and !empty($rw['card_title']))
                    $arrResposta['cardTitulo'] = $rw['card_title'];
                if ($arrResposta['cardSubTitulo'] == '' and !empty($rw['card_title_sub']))
                    $arrResposta['cardSubTitulo'] = $rw['card_title_sub'];
                if ($arrResposta['urlmodal'] == '' and !empty($rw['card_url_modal']))
                    $arrResposta['urlmodal'] = $rw['card_url_modal'];
                if ($arrResposta['statuscard'] == '' and !empty($rw['statuscard']))
                    $arrResposta['statuscard'] = $rw['statuscard'];
                if ($arrResposta['corBorda'] == '' and !empty($rw['card_border_color']) and $rw['card_border_color'] != 'secondary')
                    $arrResposta['corBorda'] = $rw['card_border_color'];
                if ($arrResposta['modulofiltros'] == '' and !empty($rw['modulofiltros']))
                    $arrResposta['modulofiltros'] = $rw['modulofiltros'];
                if ($arrResposta['masc'] == '' and !empty($rw['masc']))
                    $arrResposta['masc'] = $rw['masc'];
                if ($arrResposta['idfluxostatus'] == '' and !empty($rw['idfluxostatus']))
                    $arrResposta['idfluxostatus'] = $rw['idfluxostatus'];

                $pos1 = strpos($rw['card_url'], "[");
                $pos2 = strpos($rw['card_url'], "]");
                if (($pos1 === false or $pos2 === false) and $strLink == '') {
                    $arrResposta['link'] = $rw['card_url'];
                    if ($rw['card_url'])
                        $changeLink = false;
                } elseif (!empty($rw['card_url'])) {
                    $s = extractLinkAndIds($rw['card_url'], $virgula);

                    if (!empty($s['ids'])) {
                        $linkIds .= $s['ids'];
                        $virgula = ',';
                    }

                    if (!empty($s['lnk']) and $strLink == '')
                        $strLink = "$orderBy{$s['lnk']}";
                }

                $pos1 = strpos($rw['card_atraso_url'], "[");
                $pos2 = strpos($rw['card_atraso_url'], "]");
                if (($pos1 === false or $pos2 === false) and $strLinkAtraso == '') {
                    $arrResposta['linkAtraso'] = $rw['card_atraso_url'];
                    if ($rw['card_atraso_url'])
                        $changeLinkAtraso = false;
                } elseif (!empty($rw['card_atraso_url'])) {
                    $s = extractLinkAndIds($rw['card_atraso_url'], $virgulaAtraso);

                    if (!empty($s['ids'])) {
                        $linkIdsAtraso .= $s['ids'];
                        $virgulaAtraso = ',';
                    }

                    if (!empty($s['lnk']) and $strLinkAtraso == '')
                        $strLinkAtraso = "$orderBy{$s['lnk']}";
                }

                if ($rw['card_url_tipo'] == 'JS') {
                    $arrResposta['urljs'] = $rw['card_url_js'];
                }
            }

            if (empty($arrResposta['qtd'])) {
                $arrResposta['qtd'] = 0;
            } else {

                if ($v_numsize === true) {
                    $arrResposta['qtd'] = numsize($arrResposta['qtd'], 1);
                } 
            }

            if ($arrResposta['corBorda'] == '')
                $arrResposta['corBorda'] = 'secondary';

            if ($changeLink)
                $arrResposta['link'] = $strLink . "[" . $linkIds . "]";

            if ($changeLinkAtraso)
                $arrResposta['linkAtraso'] = $strLinkAtraso . "[" . $linkIdsAtraso . "]";

            if ($arrResposta['modulofiltros'] == 'Y') {
                //alteração criada para soluciona a corretiva
                //@513572 - DASHBOARD: NÚMERO INCORRETO
                $variaveisFiltradasAtrasadas = array('idsFiltrados' => [], 'qtd' => 0);
                if ($changeLinkAtraso) {
                    if (!empty($linkIdsAtraso)) {
                        //pegando a primary key do modulo incluso no link 
                        $primary = retColPrimKeyTabByMod($arrResposta['modulo']);
                        //filtrando valores usando o _modulofiltrospesquisa
                        $variaveisFiltradasAtrasadas =  filtro_modulofiltrospesquisa($linkIdsAtraso, $arrResposta['modulo'], $primary);
                        // alterando qtds e linkIds no prazo
                        $arrResposta['linkAtraso'] = $strLinkAtraso . "[" . implode(",", $variaveisFiltradasAtrasadas['idsFiltrados']) . "]";
                        $arrResposta['qtdAtraso'] =  $variaveisFiltradasAtrasadas['qtd']; //corrigindo quantidade
                    }
                }

                $variaveisFiltradas = array('idsFiltrados' => [], 'qtd' => 0);
                if ($changeLink) {
                    if (!empty($linkIds)) {
                        //pegando a primary key do modulo incluso no link                    
                        $primary = retColPrimKeyTabByMod($arrResposta['modulo']);
                        //filtrando valores usando o _modulofiltrospesquisa
                        $variaveisFiltradas =  filtro_modulofiltrospesquisa($linkIds,  $arrResposta['modulo'], $primary);
                        // alterando qtds e linkIds no prazo
                        $arrResposta['qtd'] = $variaveisFiltradas['qtd'];
                        $arrResposta['info'] = $variaveisFiltradas['info'];
                        $arrResposta['link'] = $strLink . "[" . implode(",", $variaveisFiltradas['idsFiltrados']) . "]";
                    }
                }
            }
        }
        echo json_encode($arrResposta);
    }
}
// função de filtro via _modulofiltropesquisa para dar solução a corretiva
// @513572 - DASHBOARD: NÚMERO INCORRETO
function filtro_modulofiltrospesquisa($linkIds, $modulo, $primary)
{
    //inicia o buffer
    //remove gets do buscavalorcarddashboard e insere os gets do _modulofiltrospesquisa
    unset($_GET['iddashcard'], $_GET['fixo'], $_GET['lps'], $_GET['habilitarMatriz']);
    $_GET['_modulo'] = $modulo;
    $_GET[$primary] = "[" . $linkIds . "]";
    $_GET['_pagina'] = "0";
    //echo var_export($_GET);
    //incluir o arquivo inves de dar um request
    ob_start();
    require(_CARBON_ROOT . "form/_modulofiltrospesquisa.php");
    //pegar o buffer de saida em json
    $buffer_modulofiltrospesquisa = json_decode(ob_get_contents());
    //limpar o buffer
    ob_end_clean();
    if ($_GET['_inspecionar_sql'] == "JSON") {
        echo var_dump(get_object_vars($buffer_modulofiltrospesquisa));
        die();
    }
    //retornar o array
    return array('idsFiltrados' => $buffer_modulofiltrospesquisa->ids, 'qtd' => intval($buffer_modulofiltrospesquisa->numrows), "info" => $_GET);
}

function numsize($size, $round = 2)
{
    $unit = ['', 'K', 'M', 'B', 'T'];
    return round($size / pow(1000, ($i = floor(log($size, 1000)))), $round) . $unit[$i];
}