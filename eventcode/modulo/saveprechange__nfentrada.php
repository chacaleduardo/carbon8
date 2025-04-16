<?
require_once(__DIR__ . "/../../form/controllers/fluxo_controller.php");
require_once(__DIR__ . "/../../form/controllers/compraapp_controller.php");
require_once(__DIR__ . "/../../form/controllers/calculoestoque_controller.php");
require_once("../api/nf/index.php");
//tranferencia entre contas por meio da nfentrada
if (isset($_SESSION["arrpostbuffer"]["transf"]["u"]["nf"]["idnf"])) {
    $idnfinicial = $_SESSION["arrpostbuffer"]["transf"]["u"]["nf"]["idnf"];
    $status = $_SESSION["arrpostbuffer"]["transf"]["u"]["nf"]["status"];
    $dtemissao = $_SESSION["arrpostbuffer"]["dest"]["i"]["nf"]["dtemissao"];

    $empresaorigem = $_SESSION["arrpostbuffer"]["orig"]["i"]["nf"]["idempresa"];
    $empresadestino = $_SESSION["arrpostbuffer"]["dest"]["i"]["nf"]["idempresa"];

    $idpessoaorigem = $_SESSION["arrpostbuffer"]["orig"]["i"]["nf"]["idpessoa"];
    $idpessoadestino = $_SESSION["arrpostbuffer"]["dest"]["i"]["nf"]["idpessoa"];

    $formapagamentoorigem = $_SESSION["arrpostbuffer"]["orig"]["i"]["nf"]["idformapagamento"];
    $formapagamentodestino = $_SESSION["arrpostbuffer"]["dest"]["i"]["nf"]["idformapagamento"];

    $itemorigem = $_SESSION["post"]["orig_nfitem_idprodserv"];
    $itemdestino = $_SESSION["post"]["dest_nfitem_idprodserv"];

    $nforigem = str_replace('+', ' ', $_SESSION["arrpostbuffer"]["orig"]["i"]["nf"]["nforigem"]);
    $nfdestino = str_replace('+', ' ', $_SESSION["arrpostbuffer"]["dest"]["i"]["nf"]["nfdestino"]);

    $valordestino = $valororigin = str_replace('%2C', '.', str_replace('.', '', $_SESSION["arrpostbuffer"]["orig"]["i"]["nf"]["total"]));;

    if (empty($idnfinicial)) {
        die("Não informado retorno remessa para realizar transferência.");
    }
    if (empty($status)) {
        die("Não informado status para realizar transferência.");
    }
    if (empty($dtemissao)) {
        die("Não informado Emissão para realizar transferência.");
    }
    if (empty($empresaorigem)) {
        die("Não informado Empresa destino para realizar transferência.");
    }
    if (empty($empresadestino)) {
        die("Não informado Empresa destino para realizar transferência.");
    }
    if (empty($formapagamentoorigem)) {
        die("Não informado forma de pagamento para realizar transferência.");
    }
    if (empty($formapagamentodestino)) {
        die("Não informado forma de pagamento destino para realizar transferência.");
    }
    if (empty($itemorigem)) {
        die("Não informado item para realizar transferência.");
    }
    if (empty($itemdestino)) {
        die("Não informado item destino para realizar transferência.");
    }
    if (empty($valororigin)) {
        die("Não informado valor para realizar transferência.");
    }
    if (empty($idpessoaorigem)) {
        die("Não informado Fornecedor origem para gerar a Nota.");
    }
    if (empty($idpessoadestino)) {
        die("Não informado Fornecedor  destino para gerar a Nota.");
    }
    if (empty($nforigem)) {
        die("Não informado Nota de Origem  destino para gerar a Nota.");
    }
    if (empty($nfdestino)) {
        die("Não informado Nota de Destino  destino para gerar a Nota.");
    }

    //gerar debito
    $datex = str_replace('%2F', '-', $dtemissao);
    $dtemissao = date('Y-m-d', strtotime($datex));

    $modulo = 'nfentrada';
    $idtipounidade = '19';

    /* $idagencia = traduzid('retornoremessa', 'idretornoremessa', 'idagencia', $idretornoremessa); */
    $rwUnid = PedidoController::buscarUnidadePorIdtipoIdempresa($idtipounidade, $empresaorigem);

    cnf::$idempresa = $empresaorigem;
    $_idempresa = cb::idempresa();

    /* $arrconfCP = cnf::getDadosConfContapagar('TAXA BOLETO'); */
    $arrconfCP['statusnf'] = 'PREVISAO';

    $idfluxostatus = FluxoController::getIdFluxoStatus($modulo, $arrconfCP['statusnf']);

    $insnf = new Insert();
    $insnf->setTable("nf");
    $insnf->status = $arrconfCP['statusnf'];
    $insnf->idunidade = $rwUnid['idunidade'];
    $insnf->idfluxostatus = $idfluxostatus;
    $insnf->idempresa = $empresaorigem;
    $insnf->idformapagamento = $formapagamentoorigem;
    $insnf->tiponf = 'O';
    $insnf->idobjetosolipor = $idnfinicial;
    $insnf->tipoobjetosolipor = 'nfentradatransferencia';
    $insnf->subtotal = $valororigin;
    $insnf->total = $valororigin;
    $insnf->parcelas = 1;
    $insnf->geracontapagar = 'Y';
    $insnf->tipocontapagar = 'D';
    $insnf->parcelas = 1;
    $insnf->diasentrada = 0;
    $insnf->intervalo = 28;
    $insnf->dtemissao = $dtemissao;
    $insnf->idpessoa = $idpessoaorigem;
    $insnf->nnfe = $nforigem;
    $idnf = $insnf->save();

    FluxoController::inserirFluxoStatusHist('nfentrada', $idnf, $idfluxostatus, 'PENDENTE');
    $resum = NfEntradaController::buscarContaItemProdservContaItemDados($itemorigem);

    unset($_SESSION["arrpostbuffer"]);

    $_SESSION['arrpostbuffer']['1']['i']['nfitem']['idnf'] = $idnf;
    $_SESSION['arrpostbuffer']['1']['i']['nfitem']['qtd'] = 1;
    $_SESSION['arrpostbuffer']['1']['i']['nfitem']['idempresa'] = $empresaorigem;
    $_SESSION['arrpostbuffer']['1']['i']['nfitem']['vlritem'] = $valororigin;
    $_SESSION['arrpostbuffer']['1']['i']['nfitem']['total'] = $valororigin;
    $_SESSION['arrpostbuffer']['1']['i']['nfitem']['nfe'] = 'Y';
    $_SESSION['arrpostbuffer']['1']['i']['nfitem']['idprodserv'] = $itemorigem;
    $_SESSION['arrpostbuffer']['1']['i']['nfitem']['idcontaitem'] = $resum['idcontaitem'];
    $_SESSION['arrpostbuffer']['1']['i']['nfitem']['idtipoprodserv'] = $resum['idtipoprodserv'];
    $_SESSION['arrpostbuffer']['1']['i']['nfitem']['tiponf'] = 'C';

    //gerar contas a pagar
    $arrinsnfcp = new Insert();
    $arrinsnfcp->setTable("nfconfpagar");
    $arrinsnfcp->idnf = $idnf;
    $arrinsnfcp->parcela = 1;
    $arrinsnfcp->proporcao = 100;
    $arrinsnfcp->datareceb = $dtemissao;
    $arrinsnfcp->idempresa = $_idempresa;
    $arrinsnfcp->idformapagamento = $formapagamentoorigem;
    $idnfconfpagar = $arrinsnfcp->save();
    cnf::atualizafat($idnf, $formapagamentoorigem, 'ABERTO');

    //gerar credito
    /* $idagencia = traduzid('retornoremessa', 'idretornoremessa', 'idagencia', $idretornoremessa); */
    $rwUnid = PedidoController::buscarUnidadePorIdtipoIdempresa($idtipounidade, $empresadestino);

    cnf::$idempresa = $empresadestino;
    $_idempresa = cb::idempresa();
    /* $arrconfCP = cnf::getDadosConfContapagar('TAXA BOLETO'); */
    $arrconfCP['statusnf'] = 'PREVISAO';

    $idfluxostatus = FluxoController::getIdFluxoStatus($modulo, $arrconfCP['statusnf']);
    $insnf2 = new Insert();
    $insnf2->setTable("nf");
    $insnf2->status = $arrconfCP['statusnf'];
    $insnf2->idunidade = $rwUnid['idunidade'];
    $insnf2->idfluxostatus = $idfluxostatus;
    $insnf2->idempresa = $empresadestino;
    $insnf2->idformapagamento = $formapagamentodestino;
    $insnf2->tiponf = 'O';
    $insnf2->idobjetosolipor = $idnfinicial;
    $insnf2->tipoobjetosolipor = 'nfentradatransferencia';
    $insnf2->subtotal = $valordestino;
    $insnf2->total = $valordestino;
    $insnf2->parcelas = 1;
    $insnf2->geracontapagar = 'Y';
    $insnf2->tipocontapagar = 'C';
    $insnf2->parcelas = 1;
    $insnf2->diasentrada = 0;
    $insnf2->intervalo = 28;
    $insnf2->dtemissao = $dtemissao;
    $insnf2->idpessoa = $idpessoadestino;
    $insnf2->nnfe = $nfdestino;
    $idnf2 = $insnf2->save();

    FluxoController::inserirFluxoStatusHist('nfentrada', $idnf2, $idfluxostatus, 'PENDENTE');
    $resum = NfEntradaController::buscarContaItemProdservContaItemDados($itemorigem);

    $_SESSION['arrpostbuffer']['2']['i']['nfitem']['idnf'] = $idnf2;
    $_SESSION['arrpostbuffer']['2']['i']['nfitem']['qtd'] = 1;
    $_SESSION['arrpostbuffer']['2']['i']['nfitem']['idempresa'] = $empresadestino;
    $_SESSION['arrpostbuffer']['2']['i']['nfitem']['vlritem'] = $valororigin;
    $_SESSION['arrpostbuffer']['2']['i']['nfitem']['total'] = $valororigin;
    $_SESSION['arrpostbuffer']['2']['i']['nfitem']['nfe'] = 'Y';
    $_SESSION['arrpostbuffer']['2']['i']['nfitem']['idprodserv'] = $itemdestino;
    $_SESSION['arrpostbuffer']['2']['i']['nfitem']['idcontaitem'] = $resum['idcontaitem'];
    $_SESSION['arrpostbuffer']['2']['i']['nfitem']['idtipoprodserv'] = $resum['idtipoprodserv'];
    $_SESSION['arrpostbuffer']['2']['i']['nfitem']['tiponf'] = 'C';
    montatabdef();

    $datamenosum = new DateTime($dtemissao);

    // Subtrai 10 dias da data
    $datamenosum->modify('-1 days');

    //gerar conta a receber
    $arrinsnfcp2 = new Insert();
    $arrinsnfcp2->setTable("nfconfpagar");
    $arrinsnfcp2->idnf = $idnf2;
    $arrinsnfcp2->parcela = 1;
    $arrinsnfcp2->proporcao = 100;
    $arrinsnfcp2->datareceb = $datamenosum->format('Y-m-d'); //d-1
    $arrinsnfcp2->idempresa = $empresadestino;
    $arrinsnfcp2->idformapagamento = $formapagamentodestino;
    $idnfconfpagar2 = $arrinsnfcp2->save();
    cnf::atualizafat($idnf2, $formapagamentodestino, 'ABERTO');
}

$iu = $_SESSION['arrpostbuffer']['1']['u']['nf']['idnf'] ? 'u' : 'i';
$_idempresa = isset($_GET["_idempresa"]) ? $_GET["_idempresa"] : $_SESSION["SESSAO"]["IDEMPRESA"];
$idnfitem = $_SESSION['arrpostbuffer']['1'][$iu]['nfitem']['idnfitem'];
$prodservdescr = $_POST['prodservdescr'];
$moedaapp = $_POST['moedaapp'];
$valorapp = $_POST['valorapp'];
$obsapp = $_POST['obsapp'];
$dtemissaoapp = $_POST['dtemissaoapp'];
$idnfitemapp = $_POST['idnfitemapp'];
$qtdapp = $_POST['qtdapp'];

$tiponfBloqueio = ($iu == 'i') ? $_SESSION['arrpostbuffer']['1']['i']['nf']['tiponf'] : $_SESSION['arrpostbuffer']['1']['u']['nf']['tiponf'];
if (empty($tiponfBloqueio) && $_POST['_nf_tiponf_old'] <> $_SESSION['arrpostbuffer']['1']['u']['nf']['tiponf']) {
    die('Favor preencher Tipo NF.');
}

$_idnf = $_SESSION['arrpostbuffer']['1']['u']['nf']['idnf'];
if (!empty($_idnf)) {
    $rotulo = getStatusFluxo('nf', 'idnf', $_idnf);
    $_SESSION['arrpostbuffer']['1']['u']['nf']['status'] = $rotulo['statustipo'];
}

if (!empty($_SESSION['arrpostbuffer']['1']['i']['nf']['tiponf']) && $_SESSION['arrpostbuffer']['1']['i']['nf']['tiponf'] != "V") {
    $tiponf = $_SESSION['arrpostbuffer']['1']['i']['nf']['tiponf'];
    switch ($tiponf) {
        case 'R':
            $idtipounidade = 14;
            break;
        case 'F':
        case 'T':
            $idtipounidade = 21;
            break;
        case 'D':
            $idtipounidade = 22;
            break;
        default:
            $idtipounidade = 19;
    }

    $unidade = NfEntradaController::buscarIdunidadePorTipoUnidade($idtipounidade, $_idempresa);
    $_SESSION['arrpostbuffer']['1']['i']['nf']['idunidade'] = $unidade['idunidade'];
}

$dataEntrada = strtotime(implode('-', array_reverse(explode('/', $_SESSION['arrpostbuffer']['1']['u']['nf']['prazo']))));
$dtEmissaoNfArray = explode(" ", $_SESSION['arrpostbuffer']['1']['u']['nf']['dtemissao']);
$dtEmissaoNf = strtotime(implode('-', array_reverse(explode('/', $dtEmissaoNfArray[0]))));
$tipoNf = $_SESSION['arrpostbuffer']['1']['u']['nf']['tiponf'];

//cotacao complementar
if (!empty($_SESSION['arrpostbuffer']['x']['u']['nf']['idnf']) && $_POST['duplicar'] == 'Y') {
    $_idpessoa_novo = $_POST['idpessoa'];
    $arrNfitem = array();
    foreach ($_POST as $k => $v) {
        //echo($k.' '.$v.'<br>');
        if (preg_match("/_(\d*)#(.*)/", $k, $res)) {
            $arrNfitem[$res[1]][$res[2]] = str_replace("%2F", "/", $v);
        }
    }
    //ordenar array pela previsao
    foreach ($arrNfitem as $key => $row) {
        $previsaoentrega[$key] = $row['previsaoentrega'];
    }
    array_multisort($previsaoentrega, SORT_DESC, $arrNfitem);

    $_idnf = $_SESSION['arrpostbuffer']['x']['u']['nf']['idnf'];
    $_nf = NfController::buscarNfPorIdnf($_idnf);

    if (!empty($_nf['tiponf']) && $_nf['tiponf'] != 'V') {
        switch ($_nf['tiponf']) {
            case 'R':
                $idtipounidade = 14;
                break;
            case 'F':
            case 'T':
                $idtipounidade = 21;
                break;
            case 'D':
                $idtipounidade = 22;
                break;
            default:
                $idtipounidade = 19;
        }
    }

    $key = 999;
    $arrNF = array();
    foreach ($arrNfitem as $k => $_arNfitem) {
        if ($_arNfitem['previsaoentrega'] != $Vprevisaoentrega) {
            $var = $_arNfitem['previsaoentrega'];
            $datex = str_replace('/', '-', $var);
            $dtemissao = date('Y-m-d', strtotime($datex . '- 3 days'));
            $dtprevisaoentrega = date('Y-m-d', strtotime($datex));

            $Vprevisaoentrega = $_arNfitem['previsaoentrega'];
            //LTM - 31-03-2021: Retorna o Idfluxo nf
            $idfluxostatus = FluxoController::getIdFluxoStatus('nfentrada', 'APROVADO');

            $insnf = new Insert();
            $insnf->setTable("nf");
            $insnf->idpessoa = $_nf['idpessoa'];
            $insnf->idempresa = $_idempresa;
            $insnf->idobjetosolipor = $_nf['idobjetosolipor'];
            $insnf->tipoobjetosolipor = $_nf['tipoobjetosolipor'];
            $insnf->idfinalidadeprodserv = $_nf['idfinalidadeprodserv'];
            $insnf->previsaoentrega = $dtprevisaoentrega;
            $insnf->status = 'APROVADO';
            $insnf->idfluxostatus = $idfluxostatus;
            $insnf->tpnf = $_nf['tpnf'];
            $insnf->tiponf = $_nf['tiponf'];
            $insnf->parcelas = $_nf['parcelas'];
            $insnf->idformapagamento = $_nf['idformapagamento'];
            $insnf->diasentrada = $_nf['diasentrada'];
            $insnf->intervalo = $_nf['intervalo'];
            $insnf->geracontapagar = $_nf['geracontapagar'];
            $insnf->tipocontapagar = $_nf['tipocontapagar'];
            $insnf->modfrete = $_nf['modfrete'];
            $insnf->frete = $_nf['frete'];
            $insnf->dtemissao = $dtemissao;
            if (!empty($_nf['tiponf']) && $_nf['tiponf'] != 'V') {
                $unidade = NfEntradaController::buscarIdunidadePorTipoUnidade($idtipounidade, $_idempresa);
                $insnf->idunidade = $unidade['idunidade'];
            }
            $insnf->idnforigem = $_nf['idnf'];
            $newidnf = $insnf->save();

            //LTM - 31-03-2021: Insere o fluxo
            FluxoController::inserirFluxoStatusHist('nfentrada', $newidnf, $idfluxostatus, 'PENDENTE');

            //gera_confparc($_nf['parcelas'], $newidnf);

            array_push($arrNF, $newidnf);
        }

        $_nfitem = NfEntradaController::buscarNfitemPorIdnfitem($_arNfitem['idnfitem'])[0];
        $key = $key + 1;

        if ($_nfitem['valipi'] > 0) {
            $valipi = ($_nfitem['valipi'] / $_nfitem['qtd']) * $_arNfitem['quantidade'];
        } else {
            $valipi = 0;
        }

        if ($_nfitem['vst'] > 0) {
            $valvst = ($_nfitem['vst'] / $_nfitem['qtd']) * $_arNfitem['quantidade'];
        } else {
            $valvst = 0;
        }

        $valtotal = ($_nfitem['total'] / $_nfitem['qtd']) * $_arNfitem['quantidade'];
        if ($valtotal == 0) {
            $valtotal = $_nfitem['vlritem'] * $_nfitem['qtdsol'];
        }

        $insnfi = new Insert();
        $insnfi->setTable("nfitem");
        $insnfi->idempresa = $_idempresa;
        $insnfi->idnf = $newidnf;
        $insnfi->valipi = $valipi;
        $insnfi->aliqipi = $_nfitem['aliqipi'];
        $insnfi->total = $valtotal;
        $insnfi->vst = $valvst;
        $insnfi->tiponf = 'C';
        $insnfi->previsaoentrega = $dtprevisaoentrega;
        $insnfi->qtd = $_arNfitem['quantidade'];
        $insnfi->qtdsol = $_arNfitem['quantidade'];
        $insnfi->idobjetoitem = $_arNfitem['idnfitem'];
        $insnfi->tipoobjetoitem = 'nfitem';

        //Validação realizada para quando o Produto for cadastrado manual e não tem idprodserv. Será cadastrado apenas o nome prodservdescr ao invés do ID
        //Lidiane (26/06/2020) - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=328433
        if ($_nfitem['idprodserv'] == NULL) {
            $insnfi->prodservdescr = $_nfitem['prodservdescr'];
        } else {
            if ($_nfitem['idprodservforn'] > 0) {
                $insnfi->idprodservforn = $_nfitem['idprodservforn'];
            }

            $insnfi->idprodserv = $_nfitem['idprodserv'];
            $insnfi->idtipoprodserv = $_nfitem['idtipoprodserv'];
        }

        $insnfi->un = $_nfitem['un'];
        $insnfi->idcontaitem = $_nfitem['idcontaitem'];
        $insnfi->vlritem = $_nfitem['vlritem'];
        $insnfi->obs = $_nfitem['obs'];
        $insnfi->des = $_nfitem['des'];
        $insnfi->nfe = 'Y';

        $newidnfi = $insnfi->save();
    }

    foreach ($arrNF as $key => $idnotafiscal) {
        $_dadosNf = NfEntradaController::buscarNfPorIdNfENfe($idnotafiscal);
        NfEntradaController::atualizarNfTotalSubtotal($_dadosNf['total'], $_dadosNf['subtotal'], $idnotafiscal);

        $idformapagamento = $_dadosNf['idformapagamento'];
        //Insere novas parcelas
        $valorparcela = $_dadosNf['total'] / $_dadosNf['parcelas'];

        if (empty(trim($_dadosNf['diasentrada']))) {
            $_dadosNf['diasentrada'] = '0';
        }
        $difdias = 0;
        if (!empty($_dadosNf['emissao'])) {
            for ($index = 1; $index <= $_dadosNf['parcelas']; $index++) {
                $strintervalo = 'days';

                if ($index == 1) {
                    $valintervalo = $_dadosNf['diasentrada'];
                    $diareceb = $_dadosNf['diasentrada'] + $difdias;
                    $vencimentocalc = date('Y-m-d ', strtotime("+" . $_dadosNf['diasentrada'] . " $strintervalo", strtotime($_dadosNf['emissao'])));
                    $recebcalc = date('Y-m-d ', strtotime("+$diareceb $strintervalo", strtotime($_dadosNf['emissao'])));
                } else {
                    $valintervalo = $valintervalo + $_dadosNf['intervalo'];
                    $diareceb = $valintervalo + $difdias;
                    $vencimentocalc = date('Y-m-d ', strtotime("+$valintervalo $strintervalo", strtotime($_dadosNf['emissao'])));
                    $recebcalc = date('Y-m-d ', strtotime("+$diareceb $strintervalo", strtotime($_dadosNf['emissao'])));
                }

                //BUSCAR CONFIGURAÇÕES DA FORMA DE PAGAMENTO
                $formapagamento = NfEntradaController::buscarInfFormapagamentoPorId($idformapagamento);
                cnf::$idempresa = $formapagamento['idempresa'];

                gera_nfconfparc_parcela($index, $idnotafiscal, $recebcalc, $idformapagamento);

                if ($formapagamento['agrupado'] == 'Y') //se for agrupado
                {
                    $insContaPagarItem = new Insert();
                    $insContaPagarItem->setTable("contapagaritem");
                    $insContaPagarItem->idempresa = $_idempresa;
                    $insContaPagarItem->status = 'PENDENTE';
                    $insContaPagarItem->idpessoa = $_dadosNf['idpessoa'];
                    if (!empty($_dadosNf['idcontaitem'])) {
                        $insContaPagarItem->idcontaitem = $_dadosNf['idcontaitem'];
                    }
                    $insContaPagarItem->idobjetoorigem = $idnotafiscal;
                    $insContaPagarItem->tipoobjetoorigem = 'nf';
                    $insContaPagarItem->tipo = 'D';
                    $insContaPagarItem->visivel = 'S';
                    $insContaPagarItem->idformapagamento = $idformapagamento;
                    $insContaPagarItem->parcela = $index;
                    $insContaPagarItem->parcelas = $_dadosNf['parcelas'];
                    $insContaPagarItem->datapagto = $recebcalc;
                    $insContaPagarItem->valor = $valorparcela;
                    $idContaPagarItem = $insContaPagarItem->save();
                } else {
                    //LTM - 31-03-2021: Retorna o Idfluxo ContaPagar
                    $idfluxostatus = FluxoController::getIdFluxoStatus('contapagar', 'PENDENTE');

                    $insContaPagar = new Insert();
                    $insContaPagar->setTable("contapagar");
                    $insContaPagar->idempresa = $_idempresa;
                    if (!empty($_dadosNf['idcontaitem'])) {
                        $insContaPagar->idcontaitem = $_dadosNf['idcontaitem'];
                    }
                    $insContaPagar->idagencia = 3;
                    $insContaPagar->idpessoa = $_dadosNf['idpessoa'];
                    $insContaPagar->tipoobjeto = 'nf';
                    $insContaPagar->idobjeto = $idnotafiscal;
                    $insContaPagar->parcela = $index;
                    $insContaPagar->parcelas = $_dadosNf['parcelas'];
                    $insContaPagar->valor = $valorparcela;
                    $insContaPagar->datapagto = $vencimentocalc;
                    $insContaPagar->idformapagamento = $idformapagamento;
                    $insContaPagar->datapagto = $recebcalc;
                    $insContaPagar->status = 'PENDENTE';
                    $insContaPagar->idfluxostatus = $idfluxostatus;
                    $insContaPagar->idformapagamento = $idformapagamento;
                    $insContaPagar->tipo = 'D';
                    $insContaPagar->visivel = 'S';
                    $insContaPagar->intervalo = $_dadosNf['intervalo'];
                    $idcontapagar = $insContaPagar->save();
                }

                //LTM - 31-03-2021: Insere o FluxoHist para ContaPagar
                if (!empty($idfluxostatus)) {
                    FluxoController::inserirFluxoStatusHist('contapagar', $idcontapagar, $idfluxostatus, 'PENDENTE');
                }

                if ($formapagamento['agrupado'] == 'Y') {
                    cnf::agrupaCP();
                }
            }
        }
    } // if ($_nf['geracontapagar']=='Y')
} // FIM NF COMPLEMENTAR

$iu = $_SESSION['arrpostbuffer']['1']['u']['nf']['idnf'] ? 'u' : 'i';

$itensCategoriaNulos = NfEntradaController::buscarItensCategoriaESubCategoriaNula($_SESSION['arrpostbuffer']['1']['u']['nf']['idnf']);
if ($itensCategoriaNulos > 0) {
    echo "Existem itens sem Categoria ou Tipo preenchidos";
}

$arrPosBuffer = $_SESSION['arrpostbuffer'];
foreach ($arrPosBuffer as $grupo => $arrAcao) {
    foreach ($arrAcao as $acao => $arrTabela) {
        foreach ($arrTabela as $tabela => $arrColuna) {
            if ($tabela == 'nfitem' && $acao == 'u' && empty($arrColuna["aliqipi"])) {
                $_SESSION['arrpostbuffer'][$grupo][$acao][$tabela]["aliqipi"] = 0;
            }
        }
    }
}

$nnfe = trim($_SESSION['arrpostbuffer']['1'][$iu]['nf']['nnfe']);
if (!empty($nnfe)) {
    $_SESSION['arrpostbuffer']['1'][$iu]['nf']['nnfe'] = $nnfe;
}

//formatar data ao inserir um nova parcela
$_datapagto = $_SESSION['arrpostbuffer']['x9']['i']['contapagar']['datapagto'];
if (!empty($_datapagto)) {
    $_SESSION['arrpostbuffer']['x9']['i']['contapagar']['datapagto'] = dma($_datapagto);
    $_SESSION['arrpostbuffer']['x9']['i']['contapagar']['datareceb'] = dma($_datapagto);
}

$idpessoa = $_SESSION['arrpostbuffer']['1'][$iu]['nf']['idpessoa'];
$idnf = $_SESSION['arrpostbuffer']['1'][$iu]['nf']['idnf'];
$status = $_SESSION['arrpostbuffer']['1'][$iu]['nf']['status'];
$previsaoentrega = $_SESSION['arrpostbuffer']['1']['u']['nf']['previsaoentrega'];

if (!empty($_SESSION['arrpostbuffer']['1']['u']['nf']['idnf'])) {
    $previsaoEntrega = NfEntradaController::buscarPrevisaoEntregaPorIdNf($idnf)['previsaoentrega'];
    if (!empty($previsaoEntrega) && empty($previsaoentrega)) {
        $_SESSION['arrpostbuffer']['1']['u']['nf']['previsaoentrega'] = $previsaoEntrega;
    }
}

if ($status != "CANCELADO" && !empty($idpessoa) && !empty($nnfe) && NfEntradaController::buscarFornecedorPorNnfe($idpessoa, $nnfe, $idnf)['quant'] >= 1) {
    die("NNFE [" . $nnfe . "] já existente");
} //if($status!="CANCELADO" and !empty($idpessoa) and !empty($nnfe) ){

if ($status == "CONCLUIDO" && !empty($_SESSION['arrpostbuffer']['1']['u']['nf']['idnf']) && $_SESSION['arrpostbuffer']['1']['u']['nf']['tiponf'] == 'C') {

    $idnf = $_SESSION['arrpostbuffer']['1']['u']['nf']['idnf'];

    if ($status == 'CONCLUIDO' && cb::idempresa() == 4 && empty($_SESSION['arrpostbuffer']['1']['u']['nf']['prazo'])) {
        $_SESSION['arrpostbuffer']['1']['u']['nf']['prazo'] = date('d/m/Y');
    }

    //Verifica se o prazo está preenchido, caso não esteja aparece a mensagem (LTM - 05/08/2020)
    if (empty($_SESSION['arrpostbuffer']['1']['u']['nf']['prazo'])  && cb::idempresa() <> 4) {
        die("O campo Data Entrada deve ser preenchido");
    }

    NfEntradaController::atualizarNcmProdServ($idnf);
}

retarraytabdef('nfitem');
retarraytabdef('nfitemxml');
retarraytabdef('lote');

if (!empty($_POST['_nfitemxml_idnfitemxml']) && !empty($_SESSION['arrpostbuffer']['nfitemxml']['i']['nfitem']['idnf'])) {
    $_nfItemXml = NfEntradaController::buscarNfItemXml($idnfitemxml);
    $_SESSION['arrpostbuffer']['nfitemxml']['i']['nfitem']['cfop'] = $_nfItemXml['cfop'];
    $_SESSION['arrpostbuffer']['nfitemxml']['i']['nfitem']['prodservdescr'] = $_nfItemXml['descr'];
    $_SESSION['arrpostbuffer']['nfitemxml']['i']['nfitem']['qtd'] = $_nfItemXml['qtd'];
    $_SESSION['arrpostbuffer']['nfitemxml']['i']['nfitem']['un'] = $_nfItemXml['un'];
    $_SESSION['arrpostbuffer']['nfitemxml']['i']['nfitem']['total'] = $_nfItemXml['valor'];
    $_SESSION['arrpostbuffer']['nfitemxml']['i']['nfitem']['vlritem'] = $_nfItemXml['vlritem'];
    $_SESSION['arrpostbuffer']['nfitemxml']['i']['nfitem']['valipi'] = $_nfItemXml['vipi'];
    $_SESSION['arrpostbuffer']['nfitemxml']['i']['nfitem']['des'] = $_nfItemXml['desconto'];
    $_SESSION['arrpostbuffer']['nfitemxml']['i']['nfitem']['vst'] = $_nfItemXml['vst'];
    $_SESSION['arrpostbuffer']['nfitemxml']['i']['nfitem']['frete'] = $_nfItemXml['frete'];
    $_SESSION['arrpostbuffer']['nfitemxml']['i']['nfitem']['nfe'] = 'Y';
    $_SESSION['arrpostbuffer']['nfitemxml']['i']['nfitem']['tiponf'] = 'C';
    $_SESSION['arrpostbuffer']['nfitemxml']['i']['nfitem']['idnfitemxml'] = $idnfitemxml;
}

/*
 * INSERIR ITENS NA TELA DE PEDIDO CLICANDO EM +
 */
$arrInsProd = array();
foreach ($_POST as $k => $v) {
    if (preg_match("/_(\d*)#(.*)/", $k, $res)) {
        $arrInsProd[$res[1]][$res[2]] = $v;
    }
}

if (!empty($arrInsProd)) {
    $i = 99977;
    // LOOP NOS ITENS DO + DA TELA
    foreach ($arrInsProd as $k => $v) {
        $i = $i + 1;

        $idprodserv = $v['idprodserv'];
        $prodservdescr = $v['prodservdescr'];
        $vlritem = $v['vlritem'];
        $des = $v['des'];
        $aliqipi = $v['aliqipi'];
        $vst = $v['vst'];
        $idnf = $_SESSION["arrpostbuffer"]["1"]["u"]["nf"]["idnf"];

        if (!empty($idprodserv) or !empty($prodservdescr)) {
            if (empty($idnf)) {
                die("[saveprechange_nf]-Não foi possivel identificar o ID do Pedido!!!");
            }
            $total = $v["quantidade"] * tratanumero($v["vlritem"]);
            if (!empty($v['aliqipi'])) {
                $valipi = $total * $aliqipi / 100;
                $total = $total + $valipi;
            }
            // montar o item para insert
            $_SESSION['arrpostbuffer'][$i]['i']['nfitem']['qtd'] = $v["quantidade"];
            if (!empty($v["idprodserv"])) {
                $rowd = NfEntradaController::buscarNFProdservForn($idprodserv, $idnf);
                $_idprodservforn = $rowd['idprodservforn'];
                $unProdserv = traduzid('prodserv', 'idprodserv', 'un', $v["idprodserv"]);
                if (!empty($_idprodservforn)) {
                    $_SESSION['arrpostbuffer'][$i]['i']['nfitem']['idprodservforn'] = $_idprodservforn;
                    $_SESSION['arrpostbuffer'][$i]['i']['nfitem']['un'] = $rowd['unforn'];
                } else if (!empty($unProdserv)) {
                    $_SESSION['arrpostbuffer'][$i]['i']['nfitem']['un'] = $unProdserv;
                }
            } else {
                $_SESSION['arrpostbuffer'][$i]['i']['nfitem']['prodservdescr'] = $v["prodservdescr"];
            }

            $_SESSION['arrpostbuffer'][$i]['i']['nfitem']['vlritem'] = $vlritem;
            $_SESSION['arrpostbuffer'][$i]['i']['nfitem']['des'] = $des;
            $_SESSION['arrpostbuffer'][$i]['i']['nfitem']['aliqipi'] = $aliqipi;
            $_SESSION['arrpostbuffer'][$i]['i']['nfitem']['vst'] = $vst;

            if (!empty($v["idprodserv"])) {
                $idtipoprodserv = traduzid('prodserv', 'idprodserv', 'idtipoprodserv', $v["idprodserv"]);
                $_SESSION['arrpostbuffer'][$i]['i']['nfitem']['idtipoprodserv'] = $idtipoprodserv;
                $_SESSION['arrpostbuffer'][$i]['i']['nfitem']['idprodserv'] = $v["idprodserv"];
            }
            if (!empty($v["idprodserv"])) {
                $ridcontaitem = NfEntradaController::buscarContaItemPorIdprodserv($idprodserv);
                $idcontaitem = $ridcontaitem['idcontaitem'];
                if (!empty($idcontaitem)) {
                    $_SESSION['arrpostbuffer'][$i]['i']['nfitem']['idcontaitem'] = $idcontaitem;
                } else {
                    $_SESSION['arrpostbuffer'][$i]['i']['nfitem']['idcontaitem'] = $v["idcontaitem"];
                }
            } else {
                $_SESSION['arrpostbuffer'][$i]['i']['nfitem']['idcontaitem'] = $v["idcontaitem"];
            }

            $_SESSION['arrpostbuffer'][$i]['i']['nfitem']['idnf'] = $idnf;
            $_SESSION['arrpostbuffer'][$i]['i']['nfitem']['tiponf'] = 'C';
            $_SESSION['arrpostbuffer'][$i]['i']['nfitem']['nfe'] = 'Y';
            $_SESSION['arrpostbuffer'][$i]['i']['nfitem']['pis'] = $v["pis"];
            $_SESSION['arrpostbuffer'][$i]['i']['nfitem']['cofins'] = $v["cofins"];
            $_SESSION['arrpostbuffer'][$i]['i']['nfitem']['valicms'] = $v["valicms"];
            $_SESSION['arrpostbuffer'][$i]['i']['nfitem']['aliqicms'] = $v["aliqicms"];
            $_SESSION['arrpostbuffer'][$i]['i']['nfitem']['vlritem'] = $v["vlritem"];
            $_SESSION['arrpostbuffer'][$i]['i']['nfitem']['total'] = $total;
        }
    } //foreach($arrInsProd as $k=>$v){

    montatabdef();
} //if(!empty($arrInsProd)){

#####MULTIPLICAR NF
if (!empty($_POST['_x_u_nf_idnf']) && !empty($_POST['qtdvezes']) && !empty($_POST['multiplicar']) && !empty($_POST['intervalo']) &&  !empty($_POST['tipointervalo'])) {
    $arrnf = NfEntradaController::buscarNfPorId($_POST['_x_u_nf_idnf']);

    $arrColunasNfItem = NfEntradaController::buscarNfitemPorIdnf($_POST['_x_u_nf_idnf']);
    $colid = "idnfitem";
    foreach ($arrColunasNfItem as $_itens) {
        foreach ($_itens as $nfitemCol => $nfItemValor) {
            $arrnfitem[$_itens[$colid]][$nfitemCol] = $nfItemValor;
        }
    }

    $strinterval = "P" . $_POST['intervalo'] . "" . $_POST['tipointervalo'];

    for ($i = 1; $i <= $_POST['qtdvezes']; $i++) // duplicar a nota quantas vezes precisar
    {
        //INSERIR A NF
        $geracontapagar = 'N';
        $parcelas = 0;
        $insnf = new Insert();
        $insnf->setTable("nf");

        //LTM - 05-04-2021: Retorna o Idfluxo nf
        $idfluxostatus = FluxoController::getIdFluxoStatus('nfentrada', 'PREVISAO');

        foreach ($arrnf as $key => $value) {
            if ($key == 'dtemissao') {
                if (empty($date)) {
                    $date = new DateTime($value);
                }

                $interval = new DateInterval($strinterval);
                $date->add($interval);
                //echo $date->format('Y-m-d') . "\n";die();
                $value = $date->format('Y-m-d h:m:s');
                $dtemissao = $date->format('Y-m-d');
            }

            if ($key == 'geracontapagar') {
                $geracontapagar = $value;
            }
            if ($key == 'parcelas') {
                $parcelas = $value;
            }

            if ($key == 'status') {
                $value = "PREVISAO";
            }
            if ($key == 'idfluxostatus') {
                $value = $idfluxostatus;
            }
            if ($key == 'nnfe' && !empty($value)) {
                $value = $value . "-" . $i;
            }
            if (!empty($value) && $key != 'idnf' && $key != 'alteradoem'  && $key != 'alteradopor' && $key != 'criadoem' && $key != 'criadopor') {
                $insnf->$key = $value;
            }
        }
        $insnf->idobjetosolipor = $_POST['_x_u_nf_idnf'];
        $insnf->tipoobjetosolipor = 'nf';
        $idnf = $insnf->save();

        //LTM - 05-04-2021: Insere o fluxo
        FluxoController::inserirFluxoStatusHist($_GET['_modulo'], $idnf, $idfluxostatus, 'PENDENTE');

        if ($geracontapagar == "Y") {
            // gera_confparc($parcelas, $idnf);
        }

        reset($arrnf);
        gera_contaapagar($idnf, $_POST['_x_u_nf_idnf'], $dtemissao);
        //FIM INSERIR NF 

        foreach ($arrnfitem as $arritem) {
            $insnfItem = new Insert();
            $insnfItem->setTable("nfitem");
            foreach ($arritem as $key => $value) {
                if ($key == 'idnf') {
                    $value = $idnf;
                }

                if (!empty($value) && $key != 'idnfitem') {
                    $insnfItem->$key = $value;
                }
            }
            $idnfitem = $insnfItem->save();
            // echo($idnfitem);
        }
        reset($arrnfitem);

        // ************************************************************************************************ //

    } //for ($i = 1; $i <= $_POST['qtdvezes']; $i++)   
    // die;
}
##FIM MULTIPLICAR NF

function gera_confparc($parc, $idnfparc)
{

    for ($v = 0; $v < $parc; $v++) {
        $insnfconfpagar = new Insert();
        $insnfconfpagar->setTable("nfconfpagar");
        $insnfconfpagar->idnf = $idnfparc;
        $insnfconfpagar->save();
    }
}

function gera_nfconfparc_parcela($parc, $idnfparc, $datareceb, $idformapagamento = null)
{
    $insnfconfpagar = new Insert();
    $insnfconfpagar->setTable("nfconfpagar");
    $insnfconfpagar->idnf = $idnfparc;
    $insnfconfpagar->datareceb = $datareceb;
    $insnfconfpagar->parcela = $parc;
    $insnfconfpagar->idformapagamento = $idformapagamento;
    $insnfconfpagar->save();
}

function gera_contaapagar($idnf, $idnotafiscal, $dtemissao)
{
    global $_idempresa;
    $qtParcelas = NfEntradaController::buscarQuantidadeParcelasPorStatusTipoObjeto($idnf, 'QUITADO', 'nf');
    $qtdlinhasbol = NfEntradaController::buscarQuantidadeBoletosRemessaItem('nf', $idnf);
    if ($qtParcelas == 0 && $qtdlinhasbol == 0) {
        //deleta as parcelas existentes.
        NfEntradaController::apagarParcelasExistentes('nf', $idnf);
        $_itensPorIdNf = NfEntradaController::buscarItensPorIdNf($idnotafiscal, $dtemissao)[0];
        $tiponf = $_itensPorIdNf['tiponf'];


        $formapagamento = NfEntradaController::buscarInfFormapagamentoPorId($_itensPorIdNf["idformapagamento"]);
        cnf::$idempresa = $formapagamento['idempresa'];

        //Insere novas parcelas
        $valorparcela = $_itensPorIdNf['total'] / $_itensPorIdNf['parcelas'];

        if (empty(trim($_itensPorIdNf['diasentrada']))) {
            $_itensPorIdNf['diasentrada'] = '0';
        }
        $difdias = 0;
        for ($index = 1; $index <= $_itensPorIdNf['parcelas']; $index++) {
            $strintervalo = 'DAY';
            $valintervalo = 0;

            if ($index == 1) {
                $valintervalo = $_itensPorIdNf['diasentrada'];
                $diareceb = $_itensPorIdNf['diasentrada'] + $difdias;
                $vencimentocalc = date('Y-m-d ', strtotime("+" . $_itensPorIdNf['diasentrada'] . " $strintervalo", strtotime($_itensPorIdNf['emissao'])));
                $recebcalc = date('Y-m-d ', strtotime("+$diareceb $strintervalo", strtotime($_itensPorIdNf['emissao'])));
            } else {
                $valintervalo = $valintervalo + $_itensPorIdNf['intervalo'];
                $diareceb = $valintervalo + $difdias;
                $vencimentocalc = date('Y-m-d ', strtotime("+$valintervalo $strintervalo", strtotime($_itensPorIdNf['emissao'])));
                $recebcalc = date('Y-m-d ', strtotime("+$diareceb $strintervalo", strtotime($_itensPorIdNf['emissao'])));
            }

            gera_nfconfparc_parcela($index, $idnf, $recebcalc, $_itensPorIdNf["idformapagamento"]);

            if ($formapagamento['agrupado'] == 'Y') {
                $insContaPagarItem = new Insert();
                $insContaPagarItem->setTable("contapagaritem");
                $insContaPagarItem->idempresa = $formapagamento['idempresa'];
                $insContaPagarItem->status = 'ABERTO';
                $insContaPagarItem->idpessoa = $_itensPorIdNf['idpessoa'];
                $insContaPagarItem->idobjetoorigem = $idnf;
                $insContaPagarItem->tipoobjetoorigem = 'nf';
                $insContaPagarItem->tipo = $_itensPorIdNf['tipocontapagar'];
                $insContaPagarItem->visivel = 'S';
                $insContaPagarItem->idformapagamento = $formapagamento['idformapagamento'];
                $insContaPagarItem->parcela = $index;
                $insContaPagarItem->parcelas = $_itensPorIdNf['parcelas'];
                $insContaPagarItem->datapagto = $recebcalc;
                $insContaPagarItem->valor = $valorparcela;
                $insContaPagarItem->save();
                cnf::agrupaCP();
            } else {
                if ($tiponf == "F") {
                    $statuscp = 'INICIO';
                } else {
                    $statuscp = 'PENDENTE';
                }

                //LTM - 31-03-2021: Retorna o Idfluxo ContaPagar
                $idfluxostatus = FluxoController::getIdFluxoStatus('contapagar', $statuscp);

                $insContaPagar = new Insert();
                $insContaPagar->setTable("contapagar");
                $insContaPagar->idempresa = $_idempresa;
                $insContaPagar->idagencia = 3;
                $insContaPagar->idpessoa = $_itensPorIdNf['idpessoa'];
                $insContaPagar->tipoobjeto = 'nf';
                $insContaPagar->idobjeto = $idnf;
                $insContaPagar->parcela = $index;
                $insContaPagar->parcelas = $_itensPorIdNf['parcelas'];
                $insContaPagar->valor = $valorparcela;
                $insContaPagar->datapagto = $vencimentocalc;
                $insContaPagar->datareceb = $recebcalc;
                $insContaPagar->status = $statuscp;
                $insContaPagar->idfluxostatus = $idfluxostatus;
                $insContaPagar->idformapagamento = $_itensPorIdNf["idformapagamento"];
                $insContaPagar->tipo = 'D';
                $insContaPagar->visivel = 'S';
                $insContaPagar->intervalo = $_itensPorIdNf['intervalo'];
                if ($tiponf == "F") {
                    $insContaPagar->tipoespecifico = 'AGRUPAMENTO';
                }
                $idcontapagar = $insContaPagar->save();
                //Insere a parcela
            }

            if ($formapagamento['agrupado'] != 'Y') {
                FluxoController::inserirFluxoStatusHist('contapagar', $idcontapagar, $idfluxostatus, 'PENDENTE');
            }
        }
    }
}

if (!empty($_POST['nf_idnatop']) && !empty($_POST['idnfitemxml'])) {
    $idnatop = $_POST['nf_idnatop'];
    $idcontato = $_POST['nf_idcontato'];
    $_idnf = $_SESSION['arrpostbuffer']['1']['u']['nf']['idnf'];
    $inidnfitemxml = $_POST['idnfitemxml'];
    $idnf = crianfDevolucao($_idnf, $idnatop, $idcontato, $inidnfitemxml);
    echo ('idnf=' . $idnf);
}

function  crianfDevolucao($_idnf, $idnatop, $idcontato, $inidnfitemxml)
{
    $_endereco = NfEntradaController::buscarEnderecoPessoaNf($_idnf);
    $_natOp = NfEntradaController::buscarNatOpECfopPorOrigemEIdNatOp($_endereco['destino'], $idnatop);
    $_cfop = $_natOp['cfop'];

    //LTM - 05-04-2021: Retorna o Idfluxo nf para Pedido (Tipo V)
    $idfluxostatus = FluxoController::getIdFluxoStatus('pedido', 'PEDIDO');

    //INSERIR A NF	
    $arrColunas = NfEntradaController::buscarNfPorId($_idnf);
    $insnf = new Insert();
    $insnf->setTable("nf");
    foreach ($arrColunas as $key => $value) {
        if ($key == 'dtemissao') {
            $value = date("Y-m-d H:i:s");;
        }
        if ($key == 'status') {
            $value = "PEDIDO";
        }
        if ($key == 'idfluxostatus') {
            $value = $idfluxostatus;
        }
        if ($key == 'nnfe') {
            $value = '';
        }
        if ($key == 'idnatop') {
            $value = $idnatop;
        }
        if ($key == 'idendrotulo') {
            $value = $_endereco['idendereco'];
        }
        if ($key == 'tpnf') {
            $value = '1';
        }
        if ($key == 'geracontapagar') {
            $value = 'N';
        }
        if ($key == 'finnfe') {
            $value = '4';
        }
        if ($key == 'idcontato') {
            $value = $idcontato;
        }
        if ($key == 'tiponf') {
            $value = 'V';
        }
        if ($key == 'refnfe') {
            $value = $arrColunas['idnfe'];
        }
        if ($key == 'idpessoafat') {
            $value = $arrColunas['idpessoa'];
        }

        if (!empty($value)  && $key != 'idnfe' && $key != 'procolonfe' && $key != 'xmlret' && $key != 'xml' && $key != 'recibo' && $key != 'envionfe' && $key != 'nnfe' && $key != 'idnf' && $key != 'alteradoem' && $key != 'dtemissao'  && $key != 'idformapagamento' && $key != 'alteradopor' && $key != 'criadoem' && $key != 'criadopor') {
            $insnf->$key = $value;
        }
    }
    $insnf->idobjetosolipor = $_idnf;
    $insnf->tipoobjetosolipor = 'nf';
    $idnf = $insnf->save();

    //LTM - 05-04-2021: Insere o fluxo
    FluxoController::inserirFluxoStatusHist('pedido', $idnf, $idfluxostatus, 'PENDENTE');

    reset($arrnf);
    header('X-CB-PKID: ' . $idnf);
    header('X-CB-PKFLD: idnf');

    $idnfitemxml = explode(",", $inidnfitemxml);
    foreach ($idnfitemxml as  $value) {
        $arrColunasi = NfEntradaController::buscarNfItemXmlNfItem($value, $idnf);
        $insnfItem = new Insert();
        $insnfItem->setTable("nfitem");
        foreach ($arrColunasi as $key => $value) {
            if ($key == 'idnf') {
                $value = $idnf;
            }
            if ($key == 'cfop') {
                $value = $_cfop;
            }

            if (!empty($value) && $key != 'idnfitem') {
                $insnfItem->$key = $value;
            }
        }
        $insnfItem->save();
        reset($arrnfitem);
    }
    return $idnf;
}

//Gerar a configuração das parcelas 
$idnfparc = $_SESSION['arrpostbuffer']['parc']['u']['nf']['idnf'];
$parc = $_SESSION['arrpostbuffer']['parc']['u']['nf']['parcelas'];
if (!empty($idnfparc) && !empty($parc)) {
    NfEntradaController::atualizarProporcaoNfConfPagar($idnfparc);
    $nfconfpagar = NfEntradaController::buscarNfconfpagarOrdenadoPorOrdemDescrescente($idnfparc);
    $qtd = $nfconfpagar['qtdLinhas'];

    if ($qtd > $parc) {
        foreach ($nfconfpagar['dados'] as $_nfconfpagarDados) {
            NfEntradaController::apagarNfConfPagar($_nfconfpagarDados['idnfconfpagar']);
            $qtd = $qtd - 1;
            if ($qtd == $parc) {
                break;
            }
        }
    } elseif ($qtd < $parc) {

        for ($v = $qtd; $v < $parc; $v++) {
            $insnfconfpagar = new Insert();
            $insnfconfpagar->setTable("nfconfpagar");
            $insnfconfpagar->idnf = $idnfparc;
            $idnfconfpagar = $insnfconfpagar->save();
        }
    }
} //if(!empty($idnfparc) && !empty($parc)){

//Gerar a configuração das parcelas 
$idnfati = $_SESSION['arrpostbuffer']['ati']['u']['nf']['idnf'];
$intervalo = $_SESSION['arrpostbuffer']['ati']['u']['nf']['intervalo'];
$diasentrada = $_SESSION['arrpostbuffer']['ati']['u']['nf']['diasentrada'];
if ((!empty($idnfati) && !empty($intervalo)) || (!empty($idnfati) && !empty($diasentrada))) {
    NfEntradaController::atualizarDatarecebNfConfPagar($idnfati);
}

//Gerar a configuração de rateio 
$id_nfrateio = $_SESSION['arrpostbuffer']['x']['i']['nfrateio']['idobjetorateio'];
if (!empty($id_nfrateio)) {
    $qtd = NfEntradaController::buscarNfRateioPorTipoObjetoRateioEIdObjetoRateio('nf', $id_nfrateio);
    $qtd = $qtd + 1;
    $valor = 100 / $qtd;
    if ($qtd > 1) {
        NfEntradaController::atualizarValorNfRateio('nf', $id_nfrateio, $valor);
    }
    $_SESSION['arrpostbuffer']['x']['i']['nfrateio']['valor'] = $valor;
}

$idnfrateio = $_SESSION['arrpostbuffer']['x']['d']['nfrateio']['idnfrateio'];
if (!empty($idnfrateio)) {
    $id_nfrateio = traduzid('nfrateio', 'idnfrateio', 'idobjetorateio', $idnfrateio);
    $qtd = NfEntradaController::buscarNfRateioPorTipoObjetoRateioEIdObjetoRateio('nf', $id_nfrateio);
    $qtd = $qtd - 1;
    $valor = 100 / $qtd;
    if ($qtd > 0) {
        NfEntradaController::atualizarValorNfRateio('nf', $id_nfrateio, $valor);
    }
}

$idnfat = $_SESSION['arrpostbuffer']['atf']['u']['nf']['idnf'];
if (!empty($idnfat)) {
    $idfinalidadeprodserv = $_SESSION['arrpostbuffer']['atf']['u']['nf']['idfinalidadeprodserv'];
    $tipoconsumo = traduzid('finalidadeprodserv', 'idfinalidadeprodserv', 'tipoconsumo', $idfinalidadeprodserv);
    if ($tipoconsumo == 'faticms') {
        $_SESSION['arrpostbuffer']['atf']['u']['nf']['faticms'] = 'Y';
        $_SESSION['arrpostbuffer']['atf']['u']['nf']['consumo'] = 'N';
        $_SESSION['arrpostbuffer']['atf']['u']['nf']['imobilizado'] = 'N';
        $_SESSION['arrpostbuffer']['atf']['u']['nf']['outro'] = 'N';
        $_SESSION['arrpostbuffer']['atf']['u']['nf']['comercio'] = 'N';
    }
    if ($tipoconsumo == 'consumo' or $tipoconsumo == 'comercio') {
        $_SESSION['arrpostbuffer']['atf']['u']['nf']['faticms'] = 'N';
        $_SESSION['arrpostbuffer']['atf']['u']['nf']['consumo'] = 'Y';
        $_SESSION['arrpostbuffer']['atf']['u']['nf']['imobilizado'] = 'N';
        $_SESSION['arrpostbuffer']['atf']['u']['nf']['outro'] = 'N';
        $_SESSION['arrpostbuffer']['atf']['u']['nf']['comercio'] = 'N';
    }
    if ($tipoconsumo == 'imobilizado') {
        $_SESSION['arrpostbuffer']['atf']['u']['nf']['faticms'] = 'N';
        $_SESSION['arrpostbuffer']['atf']['u']['nf']['consumo'] = 'N';
        $_SESSION['arrpostbuffer']['atf']['u']['nf']['imobilizado'] = 'Y';
        $_SESSION['arrpostbuffer']['atf']['u']['nf']['outro'] = 'N';
        $_SESSION['arrpostbuffer']['atf']['u']['nf']['comercio'] = 'N';
    }
    if ($tipoconsumo == 'outro') {
        $_SESSION['arrpostbuffer']['atf']['u']['nf']['faticms'] = 'N';
        $_SESSION['arrpostbuffer']['atf']['u']['nf']['consumo'] = 'N';
        $_SESSION['arrpostbuffer']['atf']['u']['nf']['imobilizado'] = 'N';
        $_SESSION['arrpostbuffer']['atf']['u']['nf']['outro'] = 'Y';
        $_SESSION['arrpostbuffer']['atf']['u']['nf']['comercio'] = 'N';
    }
    if ($tipoconsumo == 'comercio') {
        $_SESSION['arrpostbuffer']['atf']['u']['nf']['faticms'] = 'N';
        $_SESSION['arrpostbuffer']['atf']['u']['nf']['consumo'] = 'N';
        $_SESSION['arrpostbuffer']['atf']['u']['nf']['imobilizado'] = 'N';
        $_SESSION['arrpostbuffer']['atf']['u']['nf']['outro'] = 'N';
        $_SESSION['arrpostbuffer']['atf']['u']['nf']['comercio'] = 'Y';
    }
}

if (isset($_SESSION['arrpostbuffer']['1']['i']['tag'])) {
    $loopInserirTag = count($_SESSION['arrpostbuffer']);
    $it = 1;

    while ($loopInserirTag >= $it) {
        $_SESSION['arrpostbuffer'][$it]['i']['tag']['tag'] = getValorTag();
        $it++;
    }
}

$inidnfitemxml = $_SESSION['arrpostbuffer']['atx']['u']['nfitemxml']['idnfitemxml'];
if (!empty($inidnfitemxml)) {
    if (sizeof($_SESSION['arrpostbuffer']['atx']['u']['nfitemxml']['frete']) != 0) {
        $_SESSION['arrpostbuffer']['atx']['u']['nfitemxml']['fretepor'] = $_SESSION["SESSAO"]["USUARIO"];
        $_SESSION['arrpostbuffer']['atx']['u']['nfitemxml']['freteem'] = date('d/m/Y H:i:s');
    } elseif (!empty($_SESSION['arrpostbuffer']['atx']['u']['nfitemxml']['idprodserv']) != 0) {
        $idprodserv = $_SESSION['arrpostbuffer']['atx']['u']['nfitemxml']['idprodserv'];
        NfEntradaController::atualizarIdNfItemXmlNfItem($idprodserv, $inidnfitemxml);
    } elseif (sizeof($_SESSION['arrpostbuffer']['atx']['u']['nfitemxml']['outro']) == 0) {
        $_SESSION['arrpostbuffer']['atx']['u']['nfitemxml']['descontopor'] = $_SESSION["SESSAO"]["USUARIO"];
        $_SESSION['arrpostbuffer']['atx']['u']['nfitemxml']['descontoem'] = date('d/m/Y H:i:s');
    }
}

$gerandohistorico = $_SESSION['arrpostbuffer']['h1']['i']['modulohistorico']['idobjeto'];
if (!empty($gerandohistorico)) {
    $campo = $_SESSION['arrpostbuffer']['h1']['i']['modulohistorico']['campo'];
    $valor = $_SESSION['arrpostbuffer']['h1']['i']['modulohistorico']['valor'];
    $_SESSION['arrpostbuffer']['1']['u']['nf'][$campo] = $valor;
}

if (
    !empty($_SESSION['arrpostbuffer']['xcte']['i']['nf']['idobjetosolipor'])
    && $_SESSION['arrpostbuffer']['xcte']['i']['nf']['tipoobjetosolipor'] == 'nf'
    && $_SESSION['arrpostbuffer']['xcte']['i']['nf']['tiponf'] == 'T'
) { // gerar CTe vinculado

    $idnfSolPor = $_SESSION['arrpostbuffer']['xcte']['i']['nf']['idobjetosolipor'];

    cnf::$idempresa = $_idempresa;
    $idnforigem = $_SESSION['arrpostbuffer']['xcte']['i']['nf']['idobjetosolipor'];
    $modulo = 'nfcte';
    $idtipounidade = 21;
    $_unidade = NfEntradaController::buscarIdunidadePorTipoUnidade($idtipounidade, $_idempresa);
    $_transportadora = NfEntradaController::buscarNfPessoaPorIdNf($_SESSION['arrpostbuffer']['xcte']['i']['nf']['idobjetosolipor'])[0];

    if ($_transportadora['frete'] < 1) {
        $_transportadora['frete'] = $_transportadora['total'] * 0.02;
    }

    if ($_transportadora['tiponf'] == 'V') {
        $arrconfCP = cnf::getDadosConfContapagar('CTE-ENVIO');
    } else {
        $arrconfCP = cnf::getDadosConfContapagar('CTE-SUPRIMENTOS');
    }

    $idfluxostatus = FluxoController::getIdFluxoStatus($modulo, 'PREVISAO');

    $_SESSION['arrpostbuffer']['xcte']['i']['nf']['status'] = 'PREVISAO';

    $insnf = new Insert();
    $insnf->setTable("nf");
    $insnf->status = 'PREVISAO';
    $insnf->idunidade = $_unidade['idunidade'];
    $insnf->idfluxostatus = $idfluxostatus;
    $insnf->idempresa = $_idempresa;
    $insnf->idformapagamento = $arrconfCP['idformapagamento'];
    $insnf->tiponf = 'T';
    $insnf->idobjetosolipor = $_SESSION['arrpostbuffer']['xcte']['i']['nf']['idobjetosolipor'];
    $insnf->tipoobjetosolipor = 'nf';
    $insnf->previsaoentrega = $_transportadora['previsaoentrega'];
    $insnf->subtotal = $_transportadora['frete'];
    $insnf->total = $_transportadora['frete'];
    $insnf->parcelas = 1;
    $insnf->dtemissao = $_transportadora['dtemissao'];
    if (!empty($_transportadora['idtransportadora'])) {
        $insnf->idpessoa = $_transportadora['idtransportadora'];
    } elseif (!empty($arrconfCP['idpessoa'])) {
        $insnf->idpessoa = $arrconfCP['idpessoa'];
    } else {
        $insnf->idpessoa = $_transportadora['idpessoa'];
    }

    if (empty($_transportadora['idtransportadora']) && !empty($arrconfCP['idpessoa'])) {
        NfEntradaController::atualizarTransportadoraNf($arrconfCP['idpessoa'], $_SESSION['arrpostbuffer']['xcte']['i']['nf']['idobjetosolipor']);
    }

    $idnf = $insnf->save();
    //LTM - 05-04-2021: Insere o fluxo
    FluxoController::inserirFluxoStatusHist('nfentrada', $idnf, $idfluxostatus, 'PENDENTE');

    $insob = new Insert();
    $insob->setTable("objetovinculo");
    $insob->idobjeto = $idnforigem;
    $insob->tipoobjeto = 'nf';
    $insob->idobjetovinc =  $idnf;
    $insob->tipoobjetovinc = 'cte';
    $idob = $insob->save();


    unset($_SESSION["arrpostbuffer"]["xcte"]);
    $_SESSION['arrpostbuffer']['xcte']['i']['nfitem']['idnf'] = $idnf;
    $_SESSION['arrpostbuffer']['xcte']['i']['nfitem']['qtd'] = 1;
    $_SESSION['arrpostbuffer']['xcte']['i']['nfitem']['tipoobjetoitem'] = 'nf';
    $_SESSION['arrpostbuffer']['xcte']['i']['nfitem']['idobjetoitem'] = $idnforigem;
    $_SESSION['arrpostbuffer']['xcte']['i']['nfitem']['idpessoa'] = $_transportadora['idpessoa'];
    $_SESSION['arrpostbuffer']['xcte']['i']['nfitem']['idempresa'] = $_idempresa;
    $_SESSION['arrpostbuffer']['xcte']['i']['nfitem']['vlritem'] = $_transportadora['frete'];
    $_SESSION['arrpostbuffer']['xcte']['i']['nfitem']['total'] = $_transportadora['frete'];
    $_SESSION['arrpostbuffer']['xcte']['i']['nfitem']['obs'] = $_transportadora['idnfe'];
    $_SESSION['arrpostbuffer']['xcte']['i']['nfitem']['nfe'] = 'Y';
    $_SESSION['arrpostbuffer']['xcte']['i']['nfitem']['tiponf'] = 'T';
    $_SESSION['arrpostbuffer']['xcte']['i']['nfitem']['prodservdescr'] = $_transportadora['nome'];
    $_SESSION['arrpostbuffer']['xcte']['i']['nfitem']['idcontaitem'] = $arrconfCP['idcontaitem'];
    $_SESSION['arrpostbuffer']['xcte']['i']['nfitem']['idtipoprodserv'] = $arrconfCP['idtipoprodserv'];

    $arrinsnfcp[1]['idnf'] = $idnf;
    $arrinsnfcp[1]['parcela'] = 1;
    $arrinsnfcp[1]['idformapagamento'] = $arrconfCP['idformapagamento'];
    $arrinsnfcp[1]['proporcao'] = 100;
    $arrinsnfcp[1]['datareceb'] = $_transportadora['dtemissao'];

    $idnfconfpagar = cnf::inseredb($arrinsnfcp, 'nfconfpagar');

    //Atualizar o Valor do Lote
    $_dadosLote = NfEntradaController::buscarLotePorIdNf($idnfSolPor);
    foreach ($_dadosLote as $_loteNfitem) {
        $moedas = ['USD', 'EUR'];
        if (!empty($arrObj['moedaext']) && in_array($arrObj['moedaext'], $moedas)) {
            $impostoImportacao = NfController::buscarValorImpostoTotalItem($_loteNfitem['idprodserv'], 'nf', $idnfSolPor);
            $valorUnitarioImportacao = $_loteNfitem['impostoimportacao'] + $_loteNfitem['valipi'] + $_loteNfitem['pis'] + $_loteNfitem['cofins'];
            $valorTotalImportacaoProduto = NfController::buscarValorImpostoTotalPorTotalItem($idnfSolPor);
            $valorTotal = $valorUnitarioImportacao + $_loteNfitem['valorcomimposto'];
        } else {
            $valorTotal = $_loteNfitem['total'] + $_loteNfitem['valipi'];
        }

        $valorFrete = round((($_transportadora['frete'] * $_loteNfitem['total']) / $_loteNfitem['totalnf']), 4);
        $valconv = ($_loteNfitem['converteest'] == "Y" && !empty($_loteNfitem['valconv'])) ? $_loteNfitem['valconv'] : 1;
        $novoValorLote = round(((($valorTotal + $valorFrete) / $_loteNfitem['qtd']) / $valconv), 4);

        NfEntradaController::atualizarValorLote($novoValorLote, $_loteNfitem['idlote']);
    }

    cnf::atualizafat($idnf, $arrconfCP['idformapagamento']);
} // gerar CTe vinculado

//Atualizar valor de lotes de danfes vinculados ao cte concluido
if ($_SESSION['arrpostbuffer']['1']['u']['nf']['status'] == 'CONCLUIDO' && ($_SESSION['arrpostbuffer']['1']['u']['nf']['tiponf'] == 'T' ||  $_SESSION['arrpostbuffer']['1']['u']['nf']['tiponf'] == 'M')) {

    $idnfcte = $_SESSION['arrpostbuffer']['1']['u']['nf']['idnf'];
    $_dadosNf = NfEntradaController::buscarNfDanfe($idnfcte);


    $idnf =  $_dadosNf['idnf'];
    $frete = $_dadosNf['frete'];
    $frete = number_format(tratanumero($frete), 4, '.', '');

    $_dadosLote = NfEntradaController::buscarLoteNfItemPorIndNf($frete, $idnf);
    $l = 0;
    foreach ($_dadosLote as $_lote) {
        $l++;

        if (!empty($_lote['idlote'])) {
            $nitotal = $_lote['total'];

            if (!empty($_lote['valipi'])) {
                $nitotal = $nitotal + $_lote['valipi'];
            }

            if (!empty($_lote['novovalor'])) {
                $nitotal = $nitotal + $_lote['novovalor'];
            }

            if (!empty($_lote['idprodservforn'])) {
                $idprodservforn = $_lote['idprodservforn'];
                $_prodservForn = NfEntradaController::buscarProdservfornPorId($idprodservforn);

                if ($_prodservForn['converteest'] == "Y") {
                    if (empty($_prodservForn['valconv'])) {
                        $valconv = 1;
                    } else {
                        $valconv = $_prodservForn['valconv'];
                    }

                    $qtdprod = $_lote['qtdprod'];
                    $valor = round((($nitotal / $qtdprod) / $valconv), 4);
                    $_SESSION['arrpostbuffer']['atvlr' . $l]['u']['lote']['idlote'] = $_lote['idlote'];
                    $_SESSION['arrpostbuffer']['atvlr' . $l]['u']['lote']['vlrlote'] = $valor;
                } else {
                    $qtdprod = $_lote['qtdprod'];

                    $valor = round(($nitotal / $qtdprod), 4);
                    $_SESSION['arrpostbuffer']['atvlr' . $l]['u']['lote']['idlote'] = $_lote['idlote'];
                    $_SESSION['arrpostbuffer']['atvlr' . $l]['u']['lote']['vlrlote'] = $valor;
                }
            } else { // if(!empty($idprodservforn)){
                $qtdprod = $_lote['qtdprod'];
                $valor = round(($nitotal / $qtdprod), 4);
                $_SESSION['arrpostbuffer']['atvlr' . $l]['u']['lote']['idlote'] = $_lote['idlote'];
                $_SESSION['arrpostbuffer']['atvlr' . $l]['u']['lote']['vlrlote'] = $valor;
            }
        }
    } // while($row= mysqli_fetch_assoc($res)){

}

//dividir o frete nos itens da compra
if (!empty($_SESSION['arrpostbuffer']['atfrete']['u']['nf']['idnf'])) {
    $idnf = $_SESSION['arrpostbuffer']['atfrete']['u']['nf']['idnf'];
    $frete = $_SESSION['arrpostbuffer']['atfrete']['u']['nf']['frete'];
    $frete = number_format(tratanumero($frete), 4, '.', '');

    $_dadosLote = NfEntradaController::buscarLoteNfItemPorIndNf($frete, $idnf);
    $l = 0;
    foreach ($_dadosLote as $_lote) {
        $l++;
        $_SESSION['arrpostbuffer']['atfrete' . $l]['u']['nfitem']['idnfitem'] = $_lote['idnfitem'];
        $_SESSION['arrpostbuffer']['atfrete' . $l]['u']['nfitem']['frete'] = $_lote['novovalor'];
        if (!empty($_lote['idlote'])) {
            $nitotal = $_lote['total'];

            if (!empty($_lote['valipi'])) {
                $nitotal = $nitotal + $_lote['valipi'];
            }

            if (!empty($_lote['novovalor'])) {
                $nitotal = $nitotal + $_lote['novovalor'];
            }

            if (!empty($_lote['idprodservforn'])) {
                $idprodservforn = $_lote['idprodservforn'];
                $_prodservForn = NfEntradaController::buscarProdservfornPorId($idprodservforn);

                if ($_prodservForn['converteest'] == "Y") {
                    if (empty($_prodservForn['valconv'])) {
                        $valconv = 1;
                    } else {
                        $valconv = $_prodservForn['valconv'];
                    }

                    $qtdprod = $_lote['qtdprod'];
                    $valor = round((($nitotal / $qtdprod) / $valconv), 4);
                    $_SESSION['arrpostbuffer']['atvlr' . $l]['u']['lote']['idlote'] = $_lote['idlote'];
                    $_SESSION['arrpostbuffer']['atvlr' . $l]['u']['lote']['vlrlote'] = $valor;
                } else {
                    $qtdprod = $_lote['qtdprod'];

                    $valor = round(($nitotal / $qtdprod), 4);
                    $_SESSION['arrpostbuffer']['atvlr' . $l]['u']['lote']['idlote'] = $_lote['idlote'];
                    $_SESSION['arrpostbuffer']['atvlr' . $l]['u']['lote']['vlrlote'] = $valor;
                }
            } else { // if(!empty($idprodservforn)){
                $qtdprod = $_lote['qtdprod'];
                $valor = round(($nitotal / $qtdprod), 4);
                $_SESSION['arrpostbuffer']['atvlr' . $l]['u']['lote']['idlote'] = $_lote['idlote'];
                $_SESSION['arrpostbuffer']['atvlr' . $l]['u']['lote']['vlrlote'] = $valor;
            }
        }
    } // while($row= mysqli_fetch_assoc($res)){
}

//atualizar valor do desconto nos itens _atdesc_u_nf_idnf
if (!empty($_SESSION['arrpostbuffer']['atdesc']['u']['nf']['idnf'])) {
    $idnf = $_SESSION['arrpostbuffer']['atdesc']['u']['nf']['idnf'];
    $desconto = $_SESSION['arrpostbuffer']['atdesc']['u']['nf']['desconto'];
    $desconto = number_format(tratanumero($desconto), 4, '.', '');

    unset($_SESSION["arrpostbuffer"]);
    $_dadosLoteDesc = NfEntradaController::buscarLoteNfItemPorIndNf($desconto, $idnf);
    $l = 0;
    retarraytabdef('lote');
    foreach ($_dadosLoteDesc as $lotedesc) {
        $l++;
        $_SESSION['arrpostbuffer']['atdesc' . $l]['u']['nfitem']['idnfitem'] = $lotedesc['idnfitem'];
        $_SESSION['arrpostbuffer']['atdesc' . $l]['u']['nfitem']['des'] = $lotedesc['novovalorcun'];
        if (!empty($lotedesc['idlote'])) {
            $nitotal = $lotedesc['total'];

            if (!empty($lotedesc['valipi'])) {
                $nitotal = $nitotal + $lotedesc['valipi'];
            }

            if (!empty($lotedesc['novovalor'])) {
                $nitotal = $nitotal - $lotedesc['novovalor'];
            }

            if (!empty($lotedesc['idprodservforn'])) {
                $idprodservforn = $lotedesc['idprodservforn'];
                $_prodservForn = NfEntradaController::buscarProdservfornPorId($idprodservforn);

                if ($_prodservForn['converteest'] == "Y") {
                    if (empty($_prodservForn['valconv'])) {
                        $valconv = 1;
                    } else {
                        $valconv = $_prodservForn['valconv'];
                    }

                    $qtdprod = $lotedesc['qtdprod'];
                    $valor = round((($nitotal / $qtdprod) / $valconv), 4);
                    $_SESSION['arrpostbuffer']['atvlr' . $l]['u']['lote']['idlote'] = $lotedesc['idlote'];
                    $_SESSION['arrpostbuffer']['atvlr' . $l]['u']['lote']['vlrlote'] = $valor;
                } else {
                    $qtdprod = $lotedesc['qtdprod'];

                    $valor = round(($nitotal / $qtdprod), 4);
                    $_SESSION['arrpostbuffer']['atvlr' . $l]['u']['lote']['idlote'] = $lotedesc['idlote'];
                    $_SESSION['arrpostbuffer']['atvlr' . $l]['u']['lote']['vlrlote'] = $valor;
                }
            } else { // if(!empty($idprodservforn)){
                $qtdprod = $lotedesc['qtdprod'];
                $valor = round(($nitotal / $qtdprod), 4);
                $_SESSION['arrpostbuffer']['atvlr' . $l]['u']['lote']['idlote'] = $lotedesc['idlote'];
                $_SESSION['arrpostbuffer']['atvlr' . $l]['u']['lote']['vlrlote'] = $valor;
            }
        }
    } // while($row= mysqli_fetch_assoc($res)){
}

/*
 * INSERIR ITENS NA TELA DE PEDIDO CLICANDO EM +
 */
$arrInsProd = array();
foreach ($_POST as $k => $v) {
    if (preg_match("/_(\d*)#(.*)/", $k, $res)) {
        $arrInsProd[$res[1]][$res[2]] = $v;
    }
}

if (!empty($arrInsProd)) {
    $i = 99977;
    // LOOP NOS ITENS DO + DA TELA
    foreach ($arrInsProd as $k => $v) {
        // print_r($v);die();
        $i = $i + 1;
        if (!empty($v['idprodservlote']) && !empty($v['idnfitem']) && !empty($v['qtdprod']) && !empty($v['exercicio'])) {
            $idprodserv = $v['idprodservlote'];
            $idnfitem = $v['idnfitem'];
            $qtd = $v['qtdprod'];
            $exercicio = $v['exercicio'];
            criarlotecompra($idprodserv, $idnfitem, $qtd, $exercicio);
        }
    } //foreach($arrInsProd as $k=>$v){

    montatabdef();
} //if(!empty($arrInsProd)){

$tipoNf = $_SESSION['arrpostbuffer']['1']['u']['nf']['tiponf'];
if (($status == 'PREVISAO' || $status == 'APROVADO' || $status == 'CONCLUIDO') && !empty($_SESSION['arrpostbuffer']['1']['u']['nf']['idformapagamento']) && $tipoNf == 'C') {
    //Criar o lote automaticamente
    if (NfEntradaController::buscarItemValorNulo($_idnf) > 0) {
        die('Existem Valor Un vazio. Favor preencher!');
    }

    gerarLote($_idnf);
} elseif ($status == 'CONCLUIDO' && $_SESSION['arrpostbuffer']['1']['u']['nf']['geracontapagar'] == 'Y' && $tipoNf == 'C') {
    if (empty($_SESSION['arrpostbuffer']['1']['u']['nf']['idformapagamento'])) {
        die('A forma de Pagamento não foi configurada. Favor Configurar!');
    }

    if (NfEntradaController::buscarItemValorNulo($_idnf) > 0) {
        die('Existem Valor Un vazio. Favor preencher!');
    }

    gerarLote($_idnf);
}

function gerarLote($_idnf)
{
    $produtos = CotacaoController::buscarProdutoPorNfItem($_idnf);
    $criaLote = 'N';
    foreach ($produtos as  $_produto) {
        if ($_produto['geraloteautomatico'] == 'Y' && empty($_produto['idlote']) && $_produto['vlritem'] > 0) {
            if ($_produto['un_item'] == $_produto['un_prod']) {
                $criaLote = 'Y';
            } else {
                $parteCnpj = substr($_produto['cpfcnpj'], 0, 10);
                $qtdUnConv = CotacaoController::buscarConversaoFornecedorPorCnpj($parteCnpj, $_produto['idprodserv'], $_produto['un_item']);
                if (count($qtdUnConv) == 0) {
                    $criaLote = 'N';
                } else {
                    $criaLote = 'Y';
                }
            }

            if ($_produto['geraloteautomatico'] == 'Y' && $criaLote == 'Y' && !empty($_produto['modulo']) && $_produto['cobrar'] == 'Y') {
                $exercicio = date("Y");
                criarlotecompra($_produto['idprodserv'], $_produto['idnfitem'], $_produto['qtd'], $exercicio);
            } elseif (empty($_produto['modulo'])) {
                die('O Cadastro da Unidade "Estocado em" (' . $_produto['unidade'] . ') no produto <b>' . $_produto['descr'] . '</b> está incorreta.
                    <br /> Favor cadastrar corretamente.');
            } elseif ($_produto['geraloteautomatico'] == 'Y') {
                die('O produto <b>' . $_produto['descr'] . '</b> tem a unidade (' . $_produto['un_item'] . ') diferente da Prodserv (' . $_produto['un_prod'] . ').
                    <br /> Não tem configuração no Fornecedor.
                    <br /> Favor acrescentar a conversão deste item.');
            }
        }
    }
}

if (!empty($moedaapp) && !empty($valorapp)) {
    $contaItemProdserv = CompraAppController::buscarTipoProdservPorApp($prodservdescr, 'idtipoprodserv');
    if ($iu == 'i') {
        $idfluxostatus = FluxoController::getIdFluxoStatus('nfentrada', 'INICIO');
        $formaPagamento = CompraAppController::buscarFormaPagamentoPorIdPessoa($_SESSION["SESSAO"]["IDPESSOA"], $_idempresa);
        if (!$formaPagamento['idformapagamento']) {
            die("Não tem nenhuma Forma de Pagamento associado a esta empresa.");
        } elseif (!$formaPagamento['idunidade']) {
            die('Não foi configurada a Unidade para a Forma de Pagamento.');
        }

        if ($contaItemProdserv['idpessoa'] == '') {
            die('Não foi configurada a Pessoa no Tipo selecionado.');
        }

        $_SESSION['arrpostbuffer']['1']['i']['nf']['idpessoa'] = $contaItemProdserv['idpessoa'];
        $_SESSION['arrpostbuffer']['1']['i']['nf']['idempresa'] = $_idempresa;
        $_SESSION['arrpostbuffer']['1']['i']['nf']['idobjetosolipor'] = $prodservdescr;
        $_SESSION['arrpostbuffer']['1']['i']['nf']['tipoobjetosolipor'] = 'tipoprodserv';
        $_SESSION['arrpostbuffer']['1']['i']['nf']['status'] = 'INICIO';
        $_SESSION['arrpostbuffer']['1']['i']['nf']['idfluxostatus'] = $idfluxostatus;
        $_SESSION['arrpostbuffer']['1']['i']['nf']['tiponf'] = 'M';
        $_SESSION['arrpostbuffer']['1']['i']['nf']['parcelas'] = 1;
        $_SESSION['arrpostbuffer']['1']['i']['nf']['idformapagamento'] = $formaPagamento['idformapagamento'];
        $_SESSION['arrpostbuffer']['1']['i']['nf']['diasentrada'] = 0;
        $_SESSION['arrpostbuffer']['1']['i']['nf']['intervalo'] = 0;
        $_SESSION['arrpostbuffer']['1']['i']['nf']['geracontapagar'] = 'Y';
        $_SESSION['arrpostbuffer']['1']['i']['nf']['tipocontapagar'] = 'D';
        $_SESSION['arrpostbuffer']['1']['i']['nf']['obs'] = $obsapp;
        $_SESSION['arrpostbuffer']['1']['i']['nf']['dtemissao'] = dma($dtemissaoapp);
        $_SESSION['arrpostbuffer']['1']['i']['nf']['prazo'] = dma($dtemissaoapp);
        $unidade = CompraAppController::buscarIdunidadePorTipoUnidade(19, $_idempresa);
        $_SESSION['arrpostbuffer']['1']['i']['nf']['idunidade'] = $unidade['idunidade'];
        $_SESSION['arrpostbuffer']['1']['i']['nf']['total'] = $valorapp;
        $_SESSION['arrpostbuffer']['1']['i']['nf']['app'] = 'Y';
    } elseif ($iu == 'u') {

        //NF
        $_SESSION['arrpostbuffer']['1']['u']['nf']['idobjetosolipor'] = $prodservdescr;
        $_SESSION['arrpostbuffer']['1']['u']['nf']['obs'] = $obsapp;
        $_SESSION['arrpostbuffer']['1']['u']['nf']['total'] = $valorapp;
        $_SESSION['arrpostbuffer']['1']['u']['nf']['dtemissao'] = dma($dtemissaoapp);

        $_dataemissao = explode(" ", $dtemissaoapp);
        //NfItem
        $nome = empty(strtoupper(traduzid('pessoa', 'idpessoa', 'nomecurto', $_SESSION["SESSAO"]["IDPESSOA"])))
            ? strtoupper(traduzid('pessoa', 'idpessoa', 'nome', $_SESSION["SESSAO"]["IDPESSOA"]))
            : strtoupper(traduzid('pessoa', 'idpessoa', 'nomecurto', $_SESSION["SESSAO"]["IDPESSOA"]));
        $_SESSION['arrpostbuffer']['ni1']['u']['nfitem']['idnfitem'] = $idnfitemapp;
        $_SESSION['arrpostbuffer']['ni1']['u']['nfitem']['moeda'] = $moedaapp;
        $_SESSION['arrpostbuffer']['ni1']['u']['nfitem']['basecalc'] = $valorapp;
        $_SESSION['arrpostbuffer']['ni1']['u']['nfitem']['total'] = $valorapp;
        $_SESSION['arrpostbuffer']['ni1']['u']['nfitem']['idtipoprodserv'] = $contaItemProdserv['idtipoprodserv'];
        $_SESSION['arrpostbuffer']['ni1']['u']['nfitem']['idcontaitem'] = $contaItemProdserv['idcontaitem'];
        $_SESSION['arrpostbuffer']['ni1']['u']['nfitem']['prodservdescr'] = $contaItemProdserv['tipoprodserv'] . " - " . $nome . " - " . $_dataemissao[0];
        $_SESSION['arrpostbuffer']['ni1']['u']['nfitem']['moeda'] = $moedaapp;
        $_SESSION['arrpostbuffer']['ni1']['u']['nfitem']['basecalc'] = $valorapp;
        $_SESSION['arrpostbuffer']['ni1']['u']['nfitem']['total'] = $valorapp;
        if (!empty($qtdapp)) {
            $vlritem = str_replace(",", ".", str_replace(".", "", $valorapp)) / str_replace(",", ".", $qtdapp);
            $_SESSION['arrpostbuffer']['ni1']['u']['nfitem']['vlritem'] = number_format($vlritem, 3);
            $_SESSION['arrpostbuffer']['ni1']['u']['nfitem']['qtd'] = $qtdapp;
        } else {
            $_SESSION['arrpostbuffer']['ni1']['u']['nfitem']['vlritem'] = $valorapp;
        }

        $tag = CompraAppController::buscarUltimoValor($_POST['idtag'], 'tag');
        //if($tag['kmrodados'] >= $_POST['kmatual'] && !empty($tag['kmrodados'])){
        //die("A kilometragem atual é infeior a última enviada. Por favor, verifique o valor correto.");
        //}

        if (!empty($_POST['idtag']) && !empty($_POST['kmatual'])) {
            $arrayInsertNfItemAcao = [
                "idnfitem" => $idnfitemapp,
                "idobjeto" => $_POST['idtag'],
                "tipoobjeto" => 'tag',
                "idempresa" => $_idempresa,
                "categoria" => '',
                "kmrodados" => $_POST['kmatual'],
                "status" => "PENDENTE",
                "usuario" => $_SESSION["SESSAO"]["USUARIO"]
            ];
            CompraAppController::inserirNfItemAcao($arrayInsertNfItemAcao);
        }
    }
} elseif (empty($valorapp) && $_GET['_modulo'] == 'comprasapp') {
    die("O valor deve ser maior que 0 ou não pode ser vazio");
}

if (!empty($_SESSION['arrpostbuffer']['atx']['u']['nfitemxml']['idprodserv'])) {
    $idprodserv = $_SESSION['arrpostbuffer']['atx']['u']['nfitemxml']['idprodserv'];
    $idpessoa = $_POST['fnidpessoa'];
    $cprodforn = $_POST['fncprodforn'];
    $forn = NfEntradaController::buscarIdProdservFornPorIdprodservIdForn($idprodserv, $idpessoa);
    if ($forn['idprodservforn']) {
        $_SESSION['arrpostbuffer']['fn']['u']['prodservforn']['idprodservforn'] = $forn['idprodservforn'];
        $_SESSION['arrpostbuffer']['fn']['u']['prodservforn']['cprodforn'] = $cprodforn;

        retarraytabdef('prodservforn');
    }
}

//gerar historico e atualizar valor
$gerandohistorico = $_SESSION['arrpostbuffer']['h1']['i']['modulohistorico']['idobjeto'];
if (!empty($gerandohistorico)) {
    $campo = $_SESSION['arrpostbuffer']['h1']['i']['modulohistorico']['campo'];
    $valor = $_SESSION['arrpostbuffer']['h1']['i']['modulohistorico']['valor'];
    $_SESSION['arrpostbuffer']['1']['u']['nf'][$campo] = $valor;

    if ($campo == 'prazo') {

        $_vidnf = $_SESSION['arrpostbuffer']['1']['u']['nf']['idnf'];
        unset($_SESSION['arrpostbuffer']['1']);

        $_SESSION['arrpostbuffer']['prazo']['u']['nf']['idnf'] = $_vidnf;
        $_SESSION['arrpostbuffer']['prazo']['u']['nf']['prazo'] = $valor;
    }
}

if (!empty($_SESSION['arrpostbuffer']['ximp']['u']['nf']['idnf']) && $_POST['_tipoobjetosolipor'] == 'nf' && !empty($_POST['_objeto'])) {
    $idfluxostatus = FluxoController::getIdFluxoStatus($modulo, 'INICIO');

    $insnf = new Insert();
    $insnf->setTable("nf");
    $insnf->status = 'INICIO';
    $insnf->idfluxostatus = $idfluxostatus;
    $insnf->idempresa = $_idempresa;
    $insnf->idobjetosolipor = $_POST['_idobjetosolipor'];
    $insnf->tipoobjetosolipor = 'nf';
    $insnf->subtotal = $_POST['_valor'];
    $insnf->total = $_POST['_valor'];
    $insnf->idpessoa = $_POST['_idcliente'];
    $insnf->objeto = $_POST['_objeto'];
    $idnf = $insnf->save();

    $frete = NfEntradaController::buscarFretePorChave($_POST['_objeto']);

    if ($frete) {
        $idProdservFrete = $frete['idprodserv'][$_idempresa];

        FluxoController::inserirFluxoStatusHist('nfentrada', $idnf, $idfluxostatus, 'PENDENTE');
        $_SESSION['arrpostbuffer']['ximpitem']['i']['nfitem']['idnf'] = $idnf;
        $_SESSION['arrpostbuffer']['ximpitem']['i']['nfitem']['idprodserv'] = $idProdservFrete;
        $_SESSION['arrpostbuffer']['ximpitem']['i']['nfitem']['qtd'] = 1;
        $_SESSION['arrpostbuffer']['ximpitem']['i']['nfitem']['idempresa'] = $_idempresa;
        $_SESSION['arrpostbuffer']['ximpitem']['i']['nfitem']['tipoobjetoitem'] = 'nf';
        $_SESSION['arrpostbuffer']['ximpitem']['i']['nfitem']['idobjetoitem'] = $_POST['_idobjetosolipor'];
        $_SESSION['arrpostbuffer']['ximpitem']['i']['nfitem']['idpessoa'] = $_POST['_idcliente'];
        $_SESSION['arrpostbuffer']['ximpitem']['i']['nfitem']['idempresa'] = $_idempresa;
        $_SESSION['arrpostbuffer']['ximpitem']['i']['nfitem']['vlritem'] = $_POST['_valor'];
        $_SESSION['arrpostbuffer']['ximpitem']['i']['nfitem']['total'] = $_POST['_valor'];
        $_SESSION['arrpostbuffer']['ximpitem']['i']['nfitem']['obs'] = $_POST['_objeto'];
        $_SESSION['arrpostbuffer']['ximpitem']['i']['nfitem']['nfe'] = 'Y';
        $_SESSION['arrpostbuffer']['ximpitem']['i']['nfitem']['tiponf'] = 'D';
        $_SESSION['arrpostbuffer']['ximpitem']['i']['nfitem']['prodservdescr'] = $frete['label'];
    }
}

if (!empty($_GET['idnfcp']) && empty($_SESSION['arrpostbuffer']['1']['i']['nf']['idnf']) && empty($_SESSION['arrpostbuffer']['1']['u']['nf']['idnf']) && empty($_GET['idnf'])) {
    $_SESSION['arrpostbuffer']['1']['i']['nf']['tipocontapagar'] = $_POST['_nf_tipocontapagar'];
    $_SESSION['arrpostbuffer']['1']['i']['nf']['idformapagamento'] = $_POST['_nf_idformapagamento'];
}

include_once("saveprechange__rateioitemdest.php");
