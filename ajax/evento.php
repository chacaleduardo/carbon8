<?

require_once("../inc/php/functions.php");

$jwt = validaTokenReduzido();

if ($jwt["sucesso"] !== true) {
    echo JSON_ENCODE([
        'error' => "Erro: Não autorizado."
    ]);
    die;
}

// QUERYS
require_once(__DIR__ . "/../form/querys/_iquery.php");
require_once(__DIR__ . "/../form/querys/log_query.php");
require_once(__DIR__ . "/../form/querys/carimbo_query.php");
require_once(__DIR__ . "/../form/querys/evento_query.php");
require_once(__DIR__ . "/../form/querys/_status_query.php");

// CONTROLLERS
require_once(__DIR__ . "/../form/controllers/evento_controller.php");
require_once(__DIR__ . "/../form/controllers/sgdocumento_controller.php");
require_once(__DIR__ . "/../form/controllers/pessoa_controller.php");
require_once(__DIR__ . "/../form/controllers/tag_controller.php");

//Chama a Classe Evento
$opcao              = filter_input(INPUT_GET, "vopcao");
$offset             = filter_input(INPUT_GET, "voffset");
$order              = filter_input(INPUT_GET, "vorder");
$filter             = filter_input(INPUT_GET, "vfilter");
$titulo             = filter_input(INPUT_GET, "vtitulo");
$idevento           = filter_input(INPUT_GET, "videvento");
$idtagtipo          = filter_input(INPUT_GET, "vidtagtipo");
$idsgdoctipo        = filter_input(INPUT_GET, "vidsgdoctipo");
$idtipopessoa       = filter_input(INPUT_GET, "vidtipopessoa");
$ideventotipo       = filter_input(INPUT_GET, "videventotipo");
$versao               = filter_input(INPUT_GET, "vversao");
$idsgdocdocumento   = filter_input(INPUT_GET, "vidsgdocdocumento");
$ordenacao           = filter_input(INPUT_GET, "vordenacao");
$_filtro            = filter_input(INPUT_GET, "vfilterEventoTipo");
$idobjeto           = filter_input(INPUT_GET, "idobjeto");
$idcarimbo           = filter_input(INPUT_GET, "idcarrimbo");
$idpessoa           = filter_input(INPUT_GET, "idpessoa");
$minievento           = filter_input(INPUT_GET, "minievento");
$ideventoadd          = filter_input(INPUT_GET, "ideventoadd");
$tipoobjeto              = filter_input(INPUT_GET, "vtipoobjeto");
$objeto                  = filter_input(INPUT_GET, "vobjeto");
$objetos                  = explode(",", filter_input(INPUT_GET, "objetos"));
$repetir              = filter_input(INPUT_GET, "vrepetir");

$arrayTagTipos      = explode(" ", $idtagtipo);
$arraySgdocTipos    = explode(",", $idsgdoctipo);
$arrayPessoaTipos   = explode(" ", $idtipopessoa);

/*
* Centralizar a consulta de Módulo
* Evitar falhas em relação à Módulos Vinculados
* Complementar com as colunas necessárias diretamente na consulta
*/

if (empty($opcao)) {
    die("Opção: Variável POST não enviada corretamente!");
} else {

    if ($opcao == "tags") {
        // PENDENTE
        // $sql = "SELECT idtag, LPAD(tag, 4, '0') as tag, descricao, obs 
        //         FROM tag 
        //         WHERE idtagtipo in(".implode(',',$arrayTagTipos).") 
        //         order by trim(descricao)";
        // $res = d::b()->query($sql) or die("Erro ao carregar tags: ".mysql_error(d::b()));
        // $result = array();
        // PENDENTE

        $tags = TagController::buscarTagsFormatadasPorIdTagTipo(implode(',', $arrayTagTipos));

        // $i = 0;

        // while($r = mysql_fetch_assoc($res)) {
        // foreach($tags as $tag)
        // {
        //     $result[$i]["idtag"]            = $tag["idtag"];
        //     $result[$i]["tag"]              = $tag["tag"];
        //     $result[$i]["descricao"]        = ($tag["descricao"]); 
        //     $result[$i]["obs"]              = $tag["obs"];
        //     $i++;
        // }

        echo json_encode($tags, JSON_UNESCAPED_UNICODE);
    } elseif ($opcao == "documentos") {

        //$sql = "SELECT idsgdoc, idsgdoctipo, titulo, status FROM sgdoc WHERE idsgdoctipo in('".implode("','",$arraySgdocTipos)."')";
        $arrayFormatado = implode("','", $arraySgdocTipos);
        // $sql = "SELECT idsgdoc, idsgdoctipo, titulo FROM sgdoc WHERE idsgdoctipo in('$arrayFormatado')";
        // $res = d::b()->query($sql) or die("Erro ao carregar documentos: ".mysql_error(d::b()));

        // $result = array();
        // $i = 0;

        // while($r = mysql_fetch_assoc($res)) {
        //     $result[$i]["idsgdoc"]          = $r["idsgdoc"];
        //     $result[$i]["idsgdoctipo"]      = $r["idsgdoctipo"];
        //     $result[$i]["titulo"]           = $r["titulo"];
        //     $result[$i]["status"]           = $r["status"];
        //     $i++;
        // }

        $documentos = SgdocumentoController::buscarSgdocPorIdSgdocTipo($arrayFormatado);

        echo json_encode($documentos, JSON_UNESCAPED_UNICODE);
    } elseif ($opcao == "pessoas") {

        // $sql = "SELECT idpessoa, nome, nomecurto, idtipopessoa FROM pessoa WHERE idtipopessoa in(".implode(',',$arrayPessoaTipos).")";
        // $res = d::b()->query($sql) or die("Erro ao carregar pessoas opção: ".mysql_error(d::b()));
        // $result = array();

        // $i = 0;

        // while($r = mysql_fetch_assoc($res)) {
        //     $result[$i]["idpessoa"]         = $r["idpessoa"];
        //     $result[$i]["nome"]             = $r["nome"];
        //     $result[$i]["nomecurto"]        = $r["nomecurto"];
        //     $result[$i]["idtipopessoa"]     = $r["idtipopessoa"];
        //     $i++;
        // }

        $pessoas = PessoaController::buscarPessoasPorIdTipoPessoa(implode(',', $arrayPessoaTipos));

        echo json_encode($pessoas, JSON_UNESCAPED_UNICODE);
    } elseif ($opcao == "historico") {
        $evento = EventoController::buscarPorChavePrimaria($idevento);

        if (empty($evento["jsonhistorico"])) {
            echo json_encode(array());
        } else {
            echo ($evento["jsonhistorico"]);
            return;
        }
    } elseif ($opcao == "rnc") {
        if (empty($titulo) || empty($idsgdocdocumento) || empty($idevento)) {
            /*echo($titulo);
            echo($idsgdocdocumento);
            die($idevento);*/
            die("RNC: Variável POST não enviada corretamente!");
        } else {
            //die(gettype($idsgdocdocumento));
            // $sql = "INSERT INTO sgdoc 
            //                         (   , 
            //                             ,
            //                             , 
            //                             , 
            //                             , 
            //                             , 
            //                             , 
            //                             , 
            //                             , 
            //                             alteradoem)
            //             VALUES        
            //                         (   "..",
            //                             "..",
            //                             '', 
            //                             '',
            //                             '',
            //                             "..",
            //                             '".."',
            //                             '',
            //                             '',
            //                             '".."    ');";

            $dadosSgDoc = [
                'idempresa' => $_SESSION['SESSAO']['IDEMPRESA'],
                'idregistro' => geraRegistrosgdoc('rnc'),
                'idsgdoctipo' => 'rnc',
                'idsgdoctipodocumento' => $idsgdocdocumento,
                'idunidade' => 'null',
                'idpessoa' => 'null',
                'titulo' => $titulo,
                'idsgtipodoc' => 'null',
                'cpctr' => 'null',
                'idequipamento' => 'null',
                'idsgtipodocsub' => 'null',
                'versao' => 'null',
                'revisao' => 'null',
                'copia' => 'null',
                'status' => 'AGUARDANDO',
                'idfluxostatus' => 'null',
                'tipoacesso' => 'null',
                'conteudo' => 'null',
                'acompversao' => 'null',
                'regalteracao' => 'null',
                'idsgdoccopia' => 'null',
                'responsavel' => 'null',
                'responsavelsec' => 'null',
                'inicio' => 'null',
                'fim' => 'null',
                'idrnc' => 'null',
                'grau' => 'null',
                'impacto' => 'null',
                'nota' => 'null',
                'resultado' => 'null',
                'observacao' => 'null',
                'datavencimento' => 'null',
                'restrito' => 'null',
                'tipotreinamento' => 'null',
                'tipoavaliacao' => 'null',
                'criadopor' => $_SESSION['SESSAO']['USUARIO'],
                'criadoem' => 'now()',
                'alteradopor' => $_SESSION['SESSAO']['USUARIO'],
                'alteradoem' => date('Y-m-d H:i:s'),
                'conteudoold' => 'null',
                'scrolleditor' => 'null',
                'iddocumentoorigem' => 'null'
            ];

            $idNovoSgDoc = SgdocumentoController::inserir($dadosSgDoc);

            // $res = d::b()->query($sql) or die("Erro ao inserir rnc: ".mysql_error(d::b()));
            // $row = mysql_fetch_assoc($res);

            // $sqlLastId = "SELECT LAST_INSERT_ID();";                                        
            // $res = d::b()->query($sqlLastId) or die("Erro ao inserir rnca: ".mysql_error(d::b()));
            // $row = mysql_fetch_assoc($res);

            // $lastInsertId = $row["LAST_INSERT_ID()"];

            // $sql = "UPDATE evento SET idsgdoc = $lastInsertId WHERE idevento = $idevento;";
            // $res = d::b()->query($sql) or die("Erro ao inserir idsgdoc em evento: ".mysql_error(d::b()));
            // $row = mysql_fetch_assoc($res);
            if ($idNovoSgDoc) {
                $inserindoIdSgdocEmEvento = SQL::ini(EventoQuery::inserirIdSgdocEmEvento(), [
                    'idsgdoc' => $idNovoSgDoc,
                    'idevento' => $idevento
                ])::exec();

                $jsonResult = '{"titulo": "' . $titulo . '", "lastinsert" : "' . $idNovoSgDoc . '"}';
                echo ($jsonResult);
            }
        }
    }

    if ($opcao == "eventos") {

        $tarefasFilter = '';
        if (!empty($idevento) && $minievento != 'Y') {
            $tarefasFilter .= 'AND e.idevento = ' . $idevento;
            $offset = 0;
        } else if (!empty($filter)) {
            if ($filter == 'minhas') {
                $tarefasFilter .= "AND e.idpessoa = " . $_SESSION["SESSAO"]["IDPESSOA"];
            }

            //Para aparecer no Minievento os Eventos relacionados
            if ($minievento == 'Y') {
                $filtroMiniEvento = ' LEFT JOIN eventoobj eo ON e.idevento = eo.idobjeto';
                $ocultosFilter = 'AND eo.idevento AND objeto = "evento" 
								  AND eo.ideventoadd = ' . $ideventoadd;
            } else {
                if ($filter == 'ocultos') {
                    $ocultosFilter = 'AND er.oculto = 1';
                } else {
                    $ocultosFilter = 'AND er.oculto = 0';
                }
            }
        }
        $limit = 50;
        $asyncLoad = '';

        if (!empty($offset)) {
            //$limit = (int)$limit + (int)$offset;
            $asyncLoad = "LIMIT " . $offset . ", " . $limit;
        } else {
            $asyncLoad = "LIMIT 50";
        }

        if ($ordenacao) {
            switch ($ordenacao) {
                case 'status':
                    $ord = ' es.rotulo asc, e.prazo asc';
                    break;
                case 'statusd':
                    $ord = 'es.rotulo desc, e.prazo asc';
                    break;
                case 'tipo':
                    $ord = 'e.ideventotipo, er.visualizado asc, e.prazo asc';
                    break;
                case 'tipod':
                    $ord = 'e.ideventotipo desc, er.visualizado asc, e.prazo asc';
                    break;
                case 'evento':
                    $ord = 'e.evento asc';
                    break;
                case 'eventod':
                    $ord = 'e.evento desc';
                    break;
                case 'criadopor':
                    $ord = 'e.criadopor asc';
                    break;
                case 'criadopord':
                    $ord = 'e.criadopor desc';
                    break;
                case 'idevento':
                    $ord = 'e.idevento asc';
                    break;
                case 'ideventod':
                    $ord = 'e.idevento desc';
                    break;
                case 'data':
                    $ord = ' e.fim, e.fimhms, e.prazo asc';
                    break;
                case 'datad':
                    $ord = ' e.fim desc, e.fimhms desc, e.prazo desc';
                    break;
            }
        } else {
            if ($filter == 'ocultos') {
                $ord = "er.alteradoem desc";
            } else {
                $ord = "er.visualizado asc, if(et.prazo = 'N', e.fim, e.prazo) asc, e.fimhms asc ";
            }
        }

        if ($_filtro and $_filtro != 'undefined' and $_filtro != 'null' and $minievento != 'Y') {
            $qtde = count(explode(',', $_filtro));
            $filterEventoTipo = ' AND et.ideventotipo IN (' . $_filtro . ')';
        }

        // $sqlcount ="SELECT count(e.idevento) AS quantidade
        //             FROM evento e
        // 			join eventotipo et on et.ideventotipo = e.ideventotipo
        // 			".$filtroMiniEvento."
        //             WHERE et.dashboard = 'Y'
        //             AND e.idevento IN (
        //                 SELECT er.idmodulo 
        //                   FROM fluxostatuspessoa er
        //                  WHERE er.idobjeto = ".$_SESSION["SESSAO"]["IDPESSOA"]."
        //                    AND er.tipoobjeto = 'pessoa'
        //                    ".$tarefasFilter."
        //                    AND er.idmodulo = e.idevento 
        //                    AND er.modulo = 'evento'
        // 				   AND IF (e.ideventopai, e.inicio <= date_format(now(), '%Y-%m-%d'), 1) = 1 
        // 				   AND repetirate is null 
        //                 ".$ocultosFilter."
        //             )";  
        // $count = d::b()->query($sqlcount) or die($msgerro.": ". mysql_error(d::b()));

        // $totalResultados = (int)mysql_fetch_assoc($count)["quantidade"];

        $totalResultados = SQL::ini(EventoQuery::buscarQuantEventos(), [
            'idpessoa' => $_SESSION["SESSAO"]["IDPESSOA"],
            'filtrominievento' => $filtroMiniEvento,
            'filtrodetarefas' => $tarefasFilter,
            'filtrodeocultos' => $ocultosFilter
        ])::exec()->data[0]['quantidade'];

        //Acrescentado o campo dataslaprazo para validar se o prazo é maior que a data de hoje para sla no JavaScript. (LTM - 20-07-2020 - 332052)
        $listaDeEventos = EventoController::buscarListaDeEventos($ocultosFilter, $filterEventoTipo, $ord, $filtroMiniEvento, $tarefasFilter, $asyncLoad);
        // $res = $eventoclass->getListaEvento($ocultosFilter, $filterEventoTipo, $ord, $filtroMiniEvento, $tarefasFilter, $asyncLoad);    
        $i = 0;
        $result = array();

        // while($r = mysql_fetch_assoc($res)) {
        foreach ($listaDeEventos as $evento) {

            //Carrega se é inicio ou prazo
            // $sqlInPr = "SELECT distinct(t.col) as col
            //               FROM eventotipocampos t 
            //               JOIN "._DBCARBON."._mtotabcol c ON c.col=t.col and c.tab='evento'
            //              WHERE t.ideventotipo = ".$r['ideventotipo']." 
            //                AND  t.col in('inicio','prazo')
            //                AND t.ord is not null
            //                AND c.rotpsq is not null";		
            // $resInPr = d::b()->query($sqlInPr);
            // $rInPr = mysql_fetch_assoc($resInPr);
            // $col = $rInPr['col'];

            $colunaInicioOuPrazo = SQL::ini(EventoTipoCamposQuery::verificarSeEInicioOuPrazo(), [
                'ideventotipo' => $evento['ideventotipo']
            ])::exec()->data[0]['col'];

            //CARREGAR OS BOTÕES E DEPOIS ENVIÁ-LOS PARA DENTRO DO MENU LATERAL RÁPIDO.
            // $resb = $eventoclass->getBotoes($evento["idevento"]);
            $botoes = EventoController::buscarBotoes($evento["idevento"]);

            $sep = '';
            $fluxo = '';

            // while($rowb=mysql_fetch_assoc($resb)){ 
            foreach ($botoes as $botao) {
                if (($botao['botaocriador'] == 'Y' and $botao['criadopor'] == $_SESSION["SESSAO"]["USUARIO"]) or ($botao['botaoparticipante'] == 'Y' and $botao['criadopor'] != $_SESSION["SESSAO"]["USUARIO"]) or (($botao['botaocriador'] != 'Y') and ($botao['botaoparticipante'] != 'Y'))) {
                    $idfluxostatuspessoa = $botao['idfluxostatuspessoa'];
                    $fluxo .= $sep . $botao['botao'] . "*" . $botao['cor'] . "*" . $botao['ideventostatusf'] . "*" . $botao['idfluxostatuspessoa'] . "*" . $botao['ocultar'] . "*" . $botao['cortexto'];

                    $sep = '|';
                }
            }

            $arrstatuses = json_decode($evento["statuses"]);
            foreach ($arrstatuses as $key => $object) {
                $corstatus[$object->token] = $object->color;
            }

            if (traduzid("evento", "idevento", "modulo", $evento["idevento"])) {
                // $chaveModulo = RetornaChaveModuloAjax(traduzid("evento","idevento","modulo",$evento["idevento"]));
                $chaveModulo = EventoController::buscarChaveDoModulo(traduzid("evento", "idevento", "modulo", $evento["idevento"]));
            } else {
                $chaveModulo = '';
            }

            $dataTarefa = "";

            if ($evento["prazo"] != "0000-00-00 00:00:00") {
                if ($evento["configprazo"] == "N") {
                    $dataTarefa = substr(dmahms($evento["inicio"] . ' ' . $evento["iniciohms"]), 0, -3) . '<br>' . substr(dmahms($evento["fim"] . ' ' . $evento["fimhms"]), 0, -3);

                    $current = strtotime(date("Y-m-d"));
                    $date    = strtotime($evento["inicio"]);

                    $datediff = $date - $current;
                    $difference = floor($datediff / (60 * 60 * 24));
                    if ($difference == 0) {
                        $dataTarefa = 'HOJE ' . substr($evento["iniciohms"], 0, -3);
                        $coricone = '{"box-shadow":"#0f8041","background":"#0f8041","color":"#fff"}';
                    } else if ($difference > 1) {
                        $dataTarefa = substr(dmahms($evento["inicio"] . ' ' . $evento["iniciohms"]), 0, -3);
                        $coricone = '{"box-shadow":"#999999","background":"gainsboro","color":"#999"}';
                    } else if ($difference > 0) {
                        $dataTarefa = 'AMANHÃ ' . substr($evento["iniciohms"], 0, -3);
                        $coricone = '{"box-shadow":"#999999","background":"gainsboro","color":"#999"}';
                    } else if ($difference < -1) {
                        $dataTarefa = substr(dmahms($evento["inicio"] . ' ' . $evento["iniciohms"]), 0, -3);
                        $coricone = '{"box-shadow":"#999999","background":"#999999","color":"#666"}';
                    } else {
                        $dataTarefa = 'ONTEM ' . substr($evento["iniciohms"], 0, -3);
                        $coricone = '{"box-shadow":"#999999","background":"#999999","color":"#666"}';
                    }
                } else {
                    $dataTarefa = dma($evento["prazo"]);
                }
            }

            if ($evento['prazorestante'] == '0d 00h 00m 00s ') {
                $evento['prazorestante'] = '<i>venc.</i>';
            } else {
                $evento['prazorestante'] = explode(" ", $evento['prazorestante']);
                if ($evento['prazorestante'][0] != '0d') {
                    if (strpos($evento['prazorestante'][0], '-') !== false) {
                        $evento['prazorestante'] = '<i>venc.</i>';
                    } else {
                        $evento['prazorestante'] = $evento['prazorestante'][0];
                    }
                } else if ($evento['prazorestante'][1] != '00h') {
                    if (strpos($evento['prazorestante'][1], '-') !== false) {
                        $evento['prazorestante'] = '<i>venc.</i>';
                    } else {
                        $evento['prazorestante'] = $evento['prazorestante'][1];
                    }
                } else if ($evento['prazorestante'][2] != '00m') {
                    if (strpos($evento['prazorestante'][2], '-') !== false) {
                        $evento['prazorestante'] = '<i>venc.</i>';
                    } else {
                        $evento['prazorestante'] = $evento['prazorestante'][2];
                    }
                } else if ($evento['prazorestante'][3] != '00s') {
                    if (strpos($evento['prazorestante'][3], '-') !== false) {
                        $evento['prazorestante'] = '<i>venc.</i>';
                    } else {
                        $evento['prazorestante'] = $evento['prazorestante'][3];
                    }
                }
            }

            $result[$i]["coricone"]         = $coricone;
            $result[$i]["modulo"]           = $evento["modulo"];
            $result[$i]["idmodulo"]         = $evento["idmodulo"];
            $result[$i]["fim"]              = $evento["fim"];
            $result[$i]["prazo"]            = $dataTarefa;
            $result[$i]["prazo2"]           = $evento["prazo"];
            $result[$i]["configprazo"]      = $evento["configprazo"];
            $result[$i]["fimhms"]           = $evento["fimhms"];
            $result[$i]["iniciodata"]       = $evento["iniciodata"];
            $result[$i]["visualizado"]      = $evento["visualizado"];
            $result[$i]["status"]           = $evento["status"];
            $result[$i]["modulo"]           = $evento["modulo"];
            $result[$i]["idmodulo"]         = $evento["idmodulo"];
            $result[$i]["chavemodulo"]      = $chaveModulo;
            $result[$i]["idevento"]         = $evento["idevento"];
            $result[$i]["idpessoa"]         = $evento["idpessoa"];
            $result[$i]["criadoem"]         = $evento["criadoem"];
            $result[$i]["iniciohms"]        = $evento["iniciohms"];
            $result[$i]["eventotipo"]       = $evento["eventotipo"];
            $result[$i]["cor"]               = $evento["cor"];
            $result[$i]["totalResultados"]  = $totalResultados;
            $result[$i]["evento"]           = $evento["evento"];
            if ($evento["anonimo"] == 'Y') {
                $evento["nomecurto"] = '<i><b>ANÔNIMO</b></i>';
            } else {
                $evento["nomecurto"] = $evento["nomecurto"];
            }

            $result[$i]["nomecurto"]        = $evento["nomecurto"];
            $result[$i]["respcor"]             = $corstatus[$evento["status"]];
            $result[$i]["corstatus"]        = $evento["corstatus"];
            $result[$i]["cortextostatus"]   = $evento["cortextostatus"];
            $result[$i]["rotulo"]            = $evento["rotulo"];
            $result[$i]["id"]                = $evento["idevento"];
            $result[$i]["cor"]                 = $evento["cor"];
            $result[$i]["sla"]                 = $evento["sla"];
            $result[$i]["corstatusresp"]    = $evento["corstatusresp"];
            $result[$i]["rotuloresp"]         = $evento["rotuloresp"];
            $result[$i]["descricao"]         = str_replace('"', '*', htmlentities($evento["descricao"]));
            $result[$i]["fluxo"]             = $fluxo;
            $result[$i]["criadoempor"]         = $evento["criadoempor"];
            $result[$i]["alteradoempor"]    = $evento["alteradoempor"];
            $result[$i]["slaprazo"]            = $evento["slaprazo"];
            $result[$i]["dataslaprazo"]        = $evento["dataslaprazo"];
            $result[$i]["posicao"]            = $evento["posicao"];
            $result[$i]["prazorestante"]    = $evento["prazorestante"];
            $result[$i]["mostradata"]        = $evento["mostradata"];
            $result[$i]["mostraprazo"]        = $evento["mostraprazo"];
            $result[$i]["travasala"]        = $evento["travasala"];
            $result[$i]["diainteiro"]        = $evento["diainteiro"];
            $result[$i]["duracaohms"]        = $evento["duracaohms"];
            $result[$i]["idequipamento"]    = $evento["idequipamento"];
            $result[$i]["inicio"]            = $evento["inicio"];
            $result[$i]["iniciohms"]        = $evento["iniciohms"];
            $result[$i]["oculto"]            = $evento["oculto"];
            $result[$i]["posicaofim"]        = $evento["posicaofim"];
            $result[$i]["col"]                = $colunaInicioOuPrazo;

            if ($minievento == 'Y') {
                $result[$i]["minievento"]   = 'Y';
            }

            $i++;
        }
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    if ($opcao == "getevento") {

        // $sql = "SELECT p.nomecurto, 
        //     e.idevento, 
        //     e.idpessoa,
        //     e.evento,
        //     e.status, 
        //     e.inicio,
        //     e.iniciohms,
        //     e.fim,
        //     e.fimhms,
        //     e.criadoem,
        //     e.prazo,		
        //     e.modulo,
        //     e.idmodulo,
        //     et.eventotipo
        // FROM evento e, pessoa p, eventotipo et
        // WHERE e.idevento = ".$idevento."
        // AND p.idpessoa = e.idpessoa
        // AND e.ideventotipo = et.ideventotipo
        // AND EXISTS (
        //     SELECT 1
        //         FROM fluxostatuspessoa er
        //         WHERE er.idobjeto = ".$_SESSION["SESSAO"]["IDPESSOA"]."
        //         AND er.tipoobjeto = 'pessoa'
        //         AND er.idmodulo = e.idevento
        //         AND modulo = 'evento'
        //         AND er.oculto != 1
        //     ) ORDER BY e.prazo asc";

        // $res = d::b()->query($sql) or die("Erro ao carregar evento: ".mysql_error(d::b()));
        $i = 0;
        $result = array();

        $eventos = SQL::ini(EventoQuery::buscarEventosQueNaoEstejamOcultosPorIdEventoEIdPessoa(), [
            'idevento' => $idevento,
            'idpessoa' => $_SESSION["SESSAO"]["IDPESSOA"]
        ])::exec();

        // while($r = mysql_fetch_assoc($res)) {
        foreach ($eventos->data as $evento) {
            $arrstatuses = json_decode($evento["statuses"]);
            foreach ($arrstatuses as $key => $object) {
                $corstatus[$object->token] = $object->color;
            }

            if (traduzid("evento", "idevento", "modulo", $evento["idevento"])) {

                $chaveModulo = RetornaChaveModuloEvento(traduzid("evento", "idevento", "modulo", $evento["idevento"]));
            } else {
                $chaveModulo = '';
            }

            if ($evento["prazo"] != "0000-00-00 00:00:00") {
                //echo $evento["configprazo"];
                if ($evento["configprazo"] == "false") {
                    $dataTarefa = dmahms($evento["inicio"] . ' ' . $evento["iniciohms"]);
                } else {
                    $dataTarefa = dma($evento["prazo"]);
                }
            }

            $result[$i]["nomecurto"]        = $evento["nomecurto"];
            $result[$i]["idevento"]         = $evento["idevento"];
            $result[$i]["idpessoa"]         = $evento["idpessoa"];
            $result[$i]["inicio"]           = $evento["inicio"];
            $result[$i]["iniciohms"]        = $evento["iniciohms"];
            $result[$i]["fim"]              = $evento["fim"];
            $result[$i]["fimhms"]           = $evento["fimhms"];
            $result[$i]["descricao"]        = $evento["descricao"];
            $result[$i]["evento"]           = $evento["evento"];
            $result[$i]["status"]           = $evento["status"];
            $result[$i]["modulo"]           = $evento["modulo"];
            $result[$i]["idmodulo"]         = $evento["idmodulo"];
            $result[$i]["chavemodulo"]      = $chaveModulo;
            $result[$i]["prazo"]            = $dataTarefa;
            $result[$i]["configprazo"]      = $evento["configprazo"];
            $result[$i]["cor"]                = $corstatus[$evento["status"]];
            $result[$i]["criadoem"]         = $evento["criadoem"];
            $result[$i]["totalResultados"]  = $totalResultados;

            $i++;
        }

        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    if ($opcao == "atualizaparticipantes") {
        // $sql = "SELECT fs.idfluxostatus,
        //                e.idempresa		
        //           FROM evento e 
        //           JOIN fluxo f ON  f.idobjeto = e.ideventotipo AND f.tipoobjeto = 'ideventotipo' AND f.status = 'ATIVO'
        //           JOIN fluxostatus fs ON f.idfluxo = fs.idfluxo
        //           JOIN  "._DBCARBON."._status s ON s.idstatus = fs.idstatus
        //          WHERE e.idevento = ".$idevento." 
        //            AND s.statustipo = 'INICIO'";	

        // $res = d::b()->query($sql) or die("Erro ao buscar idfluxostatus: ".mysql_error(d::b()));

        // $r = mysql_fetch_assoc($res);
        // $tokeninicial = $r['idfluxostatus'];   

        $tokeninicial = SQL::ini(EventoQuery::buscarStatusInicialDoEvento(), [
            'idevento' => $idevento
        ])::exec()->data[0]['idfluxostatus'];

        $idfluxostatus = traduzid("evento", "idevento", "idfluxostatus", $idevento);
        //Insere o FluxostatusHist da pessoa adicionada para histórico de restaurar
        if ($objetos) {
            foreach ($objetos as $k => $obj) {
                $obj = explode("-", $obj);
                if ($obj[1] == 'pessoa') {
                    // $sqlFluxoStatusPessoa = "SELECT idfluxostatuspessoa 
                    //                            FROM fluxostatuspessoa 
                    //                           WHERE idmodulo = '$idevento' AND modulo = 'evento'
                    //                             AND idobjeto = '$idobjeto' AND tipoobjeto = 'pessoa'";
                    // $resFluxoStatusPessoa = d::b()->query($sqlFluxoStatusPessoa) or die("[evento-ajax]-Erro ao buscar fluxostatuspessoa: ".mysql_error(d::b()));
                    // $rowFluxoStatusPessoa = mysql_fetch_assoc($resFluxoStatusPessoa);

                    $pessoa = EventoController::buscarFluxoStatuspessoaPorIdEventoEIdPessoa($idevento, $obj[0]);

                    // $eventoclass->insereInicio($pessoa['idfluxostatuspessoa'], $idfluxostatus, $idevento);
                    EventoController::inserirEmFluxostatusHist($pessoa['idfluxostatuspessoa'], $idfluxostatus, $idevento, cb::idempresa());
                }

                // replaceEvento($idevento, $tokeninicial);
                EventoController::inserirPessoasDoGrupoNoEvento($idevento, $tokeninicial, false);

                //ATUALIZAR PARTICIPANTE NOS FILHOS
                // $sql = "SELECT idevento from evento e where status is null and  ideventopai =  ".$idevento;
                // $res = d::b()->query($sql);
                $eventosFilhosSemStatus = EventoController::buscarEventosFilhosSemStatusPorIdEvento($idevento);

                // while ($r = mysql_fetch_assoc($res)) 
                foreach ($eventosFilhosSemStatus as $evento) {
                    $ideventofilho = $evento['idevento'];

                    // $sqlo="SELECT pr.*
                    //           FROM evento f JOIN  fluxostatuspessoa pr ON f.ideventopai = pr.idmodulo AND pr.modulo = 'evento'
                    //           WHERE f.idevento = '".$ideventofilho."'
                    //  AND NOT EXISTS(SELECT 1 FROM fluxostatuspessoa fr WHERE fr.idmodulo = f.idevento AND pr.modulo = 'evento' AND fr.idobjeto = pr.idobjeto AND fr.tipoobjeto = pr.tipoobjeto);";

                    // $reso = d::b()->query($sqlo) or die("[ajax]-Erro ao atualizar responsaveis dos eventos filhos: ".mysql_error(d::b()));
                    $pessoasDoEventoPai = SQL::ini(EventoQuery::buscarPessoasDoEventoPaiQueNaoEstejamNoEventoAtual(), [
                        'idevento' => $ideventofilho
                    ])::exec();

                    // $arrColunas = mysqli_fetch_fields($reso);
                    // while($robj = mysql_fetch_assoc($reso)) 
                    foreach ($pessoasDoEventoPai->data as $pessoa) {
                        // $ideventoobjpessoa = inserefluxostatuspessoa($robj, $arrColunas, $ideventofilho);   
                        EventoController::inserirEmFluxostatusPessoa($pessoa['idpessoa'], $pessoa['idempresa'], $idEvento, $pessoa, 'pessoa', 'null', $idFluxostatus);
                        // $eventoclass->insereInicio($ideventoobjpessoa, $tokeninicial, $idevento);
                        EventoController::inserirEmFluxostatusHist($pessoa['idfluxostatuspessoa'], $tokeninicial, $idevento, cb::idempresa());
                    }

                    // $sqd="SELECT fr.idfluxostatuspessoa
                    //         FROM evento f JOIN fluxostatuspessoa fr ON fr.idmodulo = f.idevento AND fr.modulo = 'evento'
                    //        WHERE f.idevento = '".$ideventofilho."' AND NOT EXISTS(SELECT 1 FROM fluxostatuspessoa pr 
                    //                                  WHERE pr.idmodulo = f.ideventopai 
                    //                                    AND pr.modulo = 'evento' 
                    //                                    AND pr.idobjeto = fr.idobjeto 
                    //                                    AND fr.tipoobjeto = pr.tipoobjeto);";
                    // $resd = d::b()->query($sqd) or die("[ajax]-Erro ao pesquisar responsaveis dos eventos filhos a excluir: ".mysql_error(d::b()));

                    // $sqd="SELECT fr.idfluxostatuspessoa
                    //         FROM evento f JOIN fluxostatuspessoa fr ON fr.idmodulo = f.idevento AND fr.modulo = 'evento'
                    //         WHERE f.idevento = '".$ideventofilho."' AND NOT EXISTS(SELECT 1 FROM fluxostatuspessoa pr WHERE pr.idmodulo = f.ideventopai AND fr.modulo = 'evento' AND pr.idobjeto = fr.idobjeto AND fr.tipoobjeto = pr.tipoobjeto)";
                    // $resd = d::b()->query($sqd) or die("[ajax]-Erro ao pesquisar responsaveis dos eventos filhos a excluir: ".mysql_error(d::b()));
                    // while($rowd=mysql_fetch_assoc($resd)){
                    //     d::b()->query("delete  from fluxostatuspessoa where idfluxostatuspessoa = ".$rowd['idfluxostatuspessoa']) or die("[ajax]-Erro ao excluir responsaveis dos eventos filhos a excluir: ".mysql_error(d::b()));
                    // }
                    $deletandoEventos = EventoController::buscarEDeletarResponsaveisPorEventosFilhos($ideventofilho);
                }
            }
        } else {

            if ($tipoobjeto == 'pessoa') {
                // $sqlFluxoStatusPessoa = "SELECT idfluxostatuspessoa 
                //                            FROM fluxostatuspessoa 
                //                           WHERE idmodulo = '$idevento' AND modulo = 'evento'
                //                             AND idobjeto = '$idobjeto' AND tipoobjeto = 'pessoa'";
                // $resFluxoStatusPessoa = d::b()->query($sqlFluxoStatusPessoa) or die("[evento-ajax]-Erro ao buscar fluxostatuspessoa: ".mysql_error(d::b()));
                // $rowFluxoStatusPessoa = mysql_fetch_assoc($resFluxoStatusPessoa);

                $pessoa = EventoController::buscarFluxoStatuspessoaPorIdEventoEIdPessoa($idevento, $idobjeto);

                // $eventoclass->insereInicio($pessoa['idfluxostatuspessoa'], $idfluxostatus, $idevento);
                EventoController::inserirEmFluxostatusHist($pessoa['idfluxostatuspessoa'], $idfluxostatus, $idevento, cb::idempresa());
            }

            // replaceEvento($idevento, $tokeninicial);
            EventoController::inserirPessoasDoGrupoNoEvento($idevento, $tokeninicial, false);

            //ATUALIZAR PARTICIPANTE NOS FILHOS
            // $sql = "SELECT idevento from evento e where status is null and  ideventopai =  ".$idevento;
            // $res = d::b()->query($sql);
            $eventosFilhosSemStatus = EventoController::buscarEventosFilhosSemStatusPorIdEvento($idevento);

            // while ($r = mysql_fetch_assoc($res)) 
            foreach ($eventosFilhosSemStatus as $evento) {
                $ideventofilho = $evento['idevento'];

                // $sqlo="SELECT pr.*
                //           FROM evento f JOIN  fluxostatuspessoa pr ON f.ideventopai = pr.idmodulo AND pr.modulo = 'evento'
                //           WHERE f.idevento = '".$ideventofilho."'
                //  AND NOT EXISTS(SELECT 1 FROM fluxostatuspessoa fr WHERE fr.idmodulo = f.idevento AND pr.modulo = 'evento' AND fr.idobjeto = pr.idobjeto AND fr.tipoobjeto = pr.tipoobjeto);";

                // $reso = d::b()->query($sqlo) or die("[ajax]-Erro ao atualizar responsaveis dos eventos filhos: ".mysql_error(d::b()));
                $pessoasDoEventoPai = SQL::ini(EventoQuery::buscarPessoasDoEventoPaiQueNaoEstejamNoEventoAtual(), [
                    'idevento' => $ideventofilho
                ])::exec();

                // $arrColunas = mysqli_fetch_fields($reso);
                // while($robj = mysql_fetch_assoc($reso)) 
                foreach ($pessoasDoEventoPai->data as $pessoa) {
                    // $ideventoobjpessoa = inserefluxostatuspessoa($robj, $arrColunas, $ideventofilho);   
                    EventoController::inserirEmFluxostatusPessoa($pessoa['idpessoa'], $pessoa['idempresa'], $idEvento, $pessoa, 'pessoa', 'null', $idFluxostatus);
                    // $eventoclass->insereInicio($ideventoobjpessoa, $tokeninicial, $idevento);
                    EventoController::inserirEmFluxostatusHist($pessoa['idfluxostatuspessoa'], $tokeninicial, $idevento, cb::idempresa());
                }

                // $sqd="SELECT fr.idfluxostatuspessoa
                //         FROM evento f JOIN fluxostatuspessoa fr ON fr.idmodulo = f.idevento AND fr.modulo = 'evento'
                //        WHERE f.idevento = '".$ideventofilho."' AND NOT EXISTS(SELECT 1 FROM fluxostatuspessoa pr 
                //                                  WHERE pr.idmodulo = f.ideventopai 
                //                                    AND pr.modulo = 'evento' 
                //                                    AND pr.idobjeto = fr.idobjeto 
                //                                    AND fr.tipoobjeto = pr.tipoobjeto);";
                // $resd = d::b()->query($sqd) or die("[ajax]-Erro ao pesquisar responsaveis dos eventos filhos a excluir: ".mysql_error(d::b()));

                // $sqd="SELECT fr.idfluxostatuspessoa
                //         FROM evento f JOIN fluxostatuspessoa fr ON fr.idmodulo = f.idevento AND fr.modulo = 'evento'
                //         WHERE f.idevento = '".$ideventofilho."' AND NOT EXISTS(SELECT 1 FROM fluxostatuspessoa pr WHERE pr.idmodulo = f.ideventopai AND fr.modulo = 'evento' AND pr.idobjeto = fr.idobjeto AND fr.tipoobjeto = pr.tipoobjeto)";
                // $resd = d::b()->query($sqd) or die("[ajax]-Erro ao pesquisar responsaveis dos eventos filhos a excluir: ".mysql_error(d::b()));
                // while($rowd=mysql_fetch_assoc($resd)){
                //     d::b()->query("delete  from fluxostatuspessoa where idfluxostatuspessoa = ".$rowd['idfluxostatuspessoa']) or die("[ajax]-Erro ao excluir responsaveis dos eventos filhos a excluir: ".mysql_error(d::b()));
                // }
                $deletandoEventos = EventoController::buscarEDeletarResponsaveisPorEventosFilhos($ideventofilho);
            }
        }
    } //atualizaparticipantes

    //Caso cancela a Assinatura, será removido da tabela carrimbo e o botãod e Assinar some. (30-01-2020 - Lidiane)
    if ($opcao == "retiraassinatura") {
        // d::b()->query("DELETE FROM carrimbo WHERE idobjeto= ".$idobjeto." AND idpessoa = ".$idpessoa." AND idcarrimbo = ".$idcarrimbo ." AND status = 'PENDENTE'" ) or die("[ajax]-Erro ao excluir Objeto Carrimbo: ".mysql_error(d::b()));
        $deletandoObjetoCarimbo = SQL::ini(CarimboQuery::deletarObjetoCarimbo(), [
            'idobjeto' => $idobjeto,
            'idpessoa' => $idpessoa,
            'idcarimbo' => $idcarimbo
        ])::exec();
    }

    //Lista os eventos anteriores guardados na tabela fluxostatushist (Lidiane - 09-03-2020)
    if ($opcao == "retornaStatus") {

        $statusDoHistoricoDeEventosDaPessoa = SQL::ini(_StatusQuery::buscarStatusDoHistoricoDeEventosDaPessoa(), [
            'idfluxostatuspessoa' => $idobjeto,
            'idpessoa' => $idpessoa,
            'idevento' => $idevento
        ])::exec();

        $i = 0;
        // while ($r = mysql_fetch_assoc($res)) 
        foreach ($statusDoHistoricoDeEventosDaPessoa->data as $statusEvento) {
            $dados['botao'][$i]["idfluxostatus"]     = $statusEvento["idfluxostatus"];
            $dados['botao'][$i]["idfluxostatuspessoa"]  = $statusEvento["idfluxostatuspessoa"];
            $dados['botao'][$i]["oculto"]             = $statusEvento["ocultar"];
            $dados['botao'][$i]["botao"]             = $statusEvento["botao"];
            $dados['botao'][$i]["cor"]             = $statusEvento["cor"];
            $dados['botao'][$i]["cortexto"]        = $statusEvento["cortexto"];
            $dados['botao'][$i]["nomecurto"]       = $statusEvento["nomecurto"];
            $dados['botao'][$i]["criadoem"]        = dmahms($statusEvento["criadoem"]);

            if ($statusEvento["oculto"] == 0) {
                $ocultarHist = 'N';
            } else {
                $ocultarHist = 'Y';
            }
            $dados['historico'][$i]["ideventostatus"]     = $statusEvento["idfluxostatus"];
            $dados['historico'][$i]["idfluxostatuspessoa"]  = $statusEvento["idfluxostatuspessoa"];
            $dados['historico'][$i]["oculto"]             = $ocultarHist;
            $dados['historico'][$i]["botao"]             = $statusEvento["botao"];
            $dados['historico'][$i]["cor"]             = $statusEvento["cor"];
            $dados['historico'][$i]["cortexto"]        = $statusEvento["cortexto"];
            $dados['historico'][$i]["nomecurto"]       = $statusEvento["nomecurto"];
            $dados['historico'][$i]["criadoem"]        = dmahms($statusEvento["criadoem"]);

            $i++;
        }

        echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    }
    
    if ($opcao == "retornaStatusFluxoUnico") {

        $statusDoHistoricoDeEventosDaPessoa = SQL::ini(_StatusQuery::buscarStatusDoHistoricoDeEventosDaPessoa(), [
            'idfluxostatuspessoa' => $idobjeto,
            'idpessoa' => $idpessoa,
            'idevento' => $idevento
        ])::exec();
        
        echo json_encode($statusDoHistoricoDeEventosDaPessoa->data ? $statusDoHistoricoDeEventosDaPessoa->data[0] : []);
    }

    //Adiciona os Novos Blocos no eventoadd e caso tenha filhos, será adicionados neles também. (LTM - 10/07/2020)
    if ($opcao == "adicionarNovoBloco") {
        //Insere o novo bloco Minievento do Pai para depois inserir nos filhos.
        //quando inserir um novo bloco Minievento e o evento repetir
        // $ideventoadd = $eventoclass->getListaEvento($_SESSION["SESSAO"]["IDEMPRESA"], NULL, $idevento, $titulo, $tipoobjeto, $objeto, $order);
        $eventoAdd = EventoController::buscarListaDeEventos($_SESSION["SESSAO"]["IDEMPRESA"], NULL, $idevento, $titulo, $tipoobjeto, $objeto, $order);

        // $sql = "SELECT e.idevento 
        // 			   FROM evento e JOIN eventoadd ea ON e.ideventopai = ea.idevento 
        // 			  WHERE ideventopai = ".$idevento." GROUP BY e.idevento;";

        // $res = d::b()->query($sql) or die("Erro ao carregar evento: ".mysql_error(d::b()));

        $eventos = SQL::ini(EventoQuery::buscarEventoFilhoComEventoAddPorIdEventoPai(), [
            'ideventopai' => $idevento
        ])::exec();

        $i = 0;

        // while ($r = mysql_fetch_assoc($res)) 
        foreach ($eventos->data as $evento) {
            // $rowb = $eventoclass->getBotoes($r['idevento']);
            $botoes = EventoController::buscarBotoes($evento["idevento"]);
        }
    }

    //Insere o Valor Mínimo e Máximo, caso tenha valor mínimo e máximo
    if ($opcao == 'atualizarMinimoMaximoTag') {
        // echo $sql = "SELECT padraotempmin, padraotempmax FROM tag WHERE idtag = '".$idobjeto."';";
        // $res = d::b()->query($sql);
        // while ($r = mysql_fetch_assoc($res)) 
        // {
        //     echo $sql = "UPDATE eventoobj 
        //             SET minimo = '".$r['padraotempmin']."', 
        //                 maximo = '".$r['padraotempmax']."'
        //             WHERE idevento = '".$idevento."'
        //             AND ideventoadd = '".$ideventoadd."'
        //             AND idobjeto = '".$idobjeto."'
        //             AND objeto = 'tag';";
        //     $res = d::b()->query($sql);
        // }

        TagController::atualizarValorMinimoMaximoTag($idevento, $ideventoadd, $idobjeto, $evento['padraotempmin'], $evento['padraotempmax']);
    }

    // Carregar solmat's vinculadas
    if ($opcao == 'buscarSolmatsVinculadas') {
        $idEvento = $_GET['idevento'];
        $idEventoAdd = $_GET['ideventoadd'];
        $solmats = [];

        if ($idEvento && $idEventoAdd)
            $solmats = EventoController::buscarSolmatVinculada($idEvento, $idEventoAdd);

        echo json_encode($solmats);
    }
}
