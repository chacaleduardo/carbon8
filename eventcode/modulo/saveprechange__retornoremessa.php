<?
require_once("../form/controllers/fluxo_controller.php");
require_once("../api/nf/index.php");
require_once("../form/controllers/pedido_controller.php");

$idretornoremessa = $_POST['idretornoremessa'];
$idpessoa = $_SESSION["arrpostbuffer"]["1"]["i"]["nf"]["idpessoa"];
$dtemissao = $_SESSION["arrpostbuffer"]["1"]["i"]["nf"]["dtemissao"];
$idformapagamento = $_SESSION["arrpostbuffer"]["1"]["i"]["nf"]["idformapagamento"];

if (!empty($idretornoremessa)) {

    if (empty($idpessoa)) {
        die("Não informado Nome para gerar a Nota.");
    }

    if (empty($idformapagamento)) {
        die("Não informado Pagamento para gerar a Nota.");
    }

    if (empty($dtemissao)) {
        die("Não informado Emissão para gerar a Nota.");
    }

    $datex = str_replace('/', '-', $dtemissao);
    $dtemissao = date('Y-m-d', strtotime($datex));

    cnf::$idempresa = cb::idempresa();
    $_idempresa = cb::idempresa();
    $modulo = 'nfentrada';
    $idtipounidade = '19';

    $idagencia = traduzid('retornoremessa', 'idretornoremessa', 'idagencia', $idretornoremessa);
    $txboleto = traduzid('agencia', 'idagencia', 'txboleto', $idagencia);

    if (empty($txboleto)) {
        die("Informar a taxa de boleto no cadastro da agência.");
    }
    $rwUnid = PedidoController::buscarUnidadePorIdtipoIdempresa($idtipounidade, $_idempresa);

    $arrconfCP = cnf::getDadosConfContapagar('TAXA BOLETO');

    $sql = "select c.idobjeto,c.tipoobjeto,concat('Boleto:',i.seunumero,' - ',i.pagador) as descr,c.idpessoa
            from retornoremessaitem i join contapagar c on(c.idcontapagar =i.idcontapagar)
        where i.idretornoremessa=" . $idretornoremessa;
    $resum = d::b()->query($sql);
    $qtdbol = mysqli_num_rows($resum);

    $total = $txboleto * $qtdbol;

    $idfluxostatus = FluxoController::getIdFluxoStatus($modulo, $arrconfCP['statusnf']);
    $insnf = new Insert();
    $insnf->setTable("nf");
    $insnf->status = $arrconfCP['statusnf'];
    $insnf->idunidade = $rwUnid['idunidade'];
    $insnf->idfluxostatus = $idfluxostatus;
    $insnf->idempresa = $_idempresa;
    $insnf->idformapagamento = $idformapagamento;
    $insnf->tiponf =  $arrconfCP['tiponf'];
    $insnf->idobjetosolipor = $idretornoremessa;
    $insnf->tipoobjetosolipor = 'retornoremessa';
    $insnf->subtotal = $total;
    $insnf->total = $total;
    $insnf->parcelas = 1;
    $insnf->geracontapagar = 'Y';
    $insnf->tipocontapagar = 'D';
    $insnf->parcelas = 1;
    $insnf->diasentrada = 0;
    $insnf->intervalo = 28;
    $insnf->dtemissao = $dtemissao;
    $insnf->idpessoa = $idpessoa;
    //print_r($insnf); die;
    $idnf = $insnf->save();
    //LTM - 05-04-2021: Insere o fluxo
    FluxoController::inserirFluxoStatusHist('nfentrada', $idnf, $idfluxostatus, 'PENDENTE');

    unset($_SESSION["arrpostbuffer"]);
    $l = 1;
    while ($rowsum = mysqli_fetch_assoc($resum)) {
        $_SESSION['arrpostbuffer'][$l]['i']['nfitem']['idnf'] = $idnf;
        $_SESSION['arrpostbuffer'][$l]['i']['nfitem']['qtd'] = 1;
        $_SESSION['arrpostbuffer'][$l]['i']['nfitem']['tipoobjetoitem'] = $rowsum['tipoobjeto'];
        $_SESSION['arrpostbuffer'][$l]['i']['nfitem']['idobjetoitem'] = $rowsum['idobjeto'];
        $_SESSION['arrpostbuffer'][$l]['i']['nfitem']['idpessoa'] = $rowsum['idpessoa'];
        $_SESSION['arrpostbuffer'][$l]['i']['nfitem']['idempresa'] = $_idempresa;
        $_SESSION['arrpostbuffer'][$l]['i']['nfitem']['vlritem'] = $txboleto;
        $_SESSION['arrpostbuffer'][$l]['i']['nfitem']['total'] = $txboleto;
        $_SESSION['arrpostbuffer'][$l]['i']['nfitem']['obs'] = $row['idnfe'];
        $_SESSION['arrpostbuffer'][$l]['i']['nfitem']['nfe'] = 'Y';
        $_SESSION['arrpostbuffer'][$l]['i']['nfitem']['prodservdescr'] = $rowsum['descr'];
        $_SESSION['arrpostbuffer'][$l]['i']['nfitem']['idcontaitem'] = $arrconfCP['idcontaitem'];
        $_SESSION['arrpostbuffer'][$l]['i']['nfitem']['idtipoprodserv'] = $arrconfCP['idtipoprodserv'];
        $_SESSION['arrpostbuffer'][$l]['i']['nfitem']['tiponf'] =  $arrconfCP['tiponf'];
        $l = $l + 1;
    }

    montatabdef();
    $arrinsnfcp = new Insert();
    $arrinsnfcp->setTable("nfconfpagar");
    $arrinsnfcp->idnf = $idnf;
    $arrinsnfcp->parcela = 1;
    $arrinsnfcp->proporcao = 100;
    $arrinsnfcp->datareceb = $dtemissao;
    $arrinsnfcp->idempresa = $_idempresa;
    $arrinsnfcp->idformapagamento = $idformapagamento;
    $idnfconfpagar = $arrinsnfcp->save();
    cnf::atualizafat($idnf, $idformapagamento);
}


if (isset($_SESSION["arrpostbuffer"]["transf"]["u"]["retornoremessa"]["idretornoremessa"])) {
    $idretornoremessa = $_SESSION["arrpostbuffer"]["transf"]["u"]["retornoremessa"]["idretornoremessa"];
    $status = $_SESSION["arrpostbuffer"]["transf"]["u"]["retornoremessa"]["status"];
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
    $nfdestino = str_replace('+',' ',$_SESSION["arrpostbuffer"]["dest"]["i"]["nf"]["nfdestino"]);

    $valordestino = $valororigin = str_replace('%2C', '.', str_replace('.', '', $_SESSION["arrpostbuffer"]["orig"]["i"]["nf"]["total"]));;

    if (empty($idretornoremessa)) {
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
        die("Não informado forma de pagamento origem para realizar transferência.");
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

    $idagencia = traduzid('retornoremessa', 'idretornoremessa', 'idagencia', $idretornoremessa);
    $rwUnid = PedidoController::buscarUnidadePorIdtipoIdempresa($idtipounidade, $empresaorigem);

    cnf::$idempresa = $empresaorigem;
    $_idempresa = cb::idempresa();

    $arrconfCP = cnf::getDadosConfContapagar('TAXA BOLETO');

    $idfluxostatus = FluxoController::getIdFluxoStatus($modulo, 'APROVADO');

    $insnf = new Insert();
    $insnf->setTable("nf");
    $insnf->status = 'APROVADO';
    $insnf->idunidade = $rwUnid['idunidade'];
    $insnf->idfluxostatus = $idfluxostatus;
    $insnf->idempresa = $empresaorigem;
    $insnf->idformapagamento = $formapagamentoorigem;
    $insnf->tiponf = 'O';
    $insnf->idobjetosolipor = $idretornoremessa;
    $insnf->tipoobjetosolipor = 'retornoremessatransferencia';
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

    FluxoController::inserirFluxoStatusHist('nfentrada', $idnf, $idfluxostatus, 'APROVADO');

    $sql = "SELECT p.idtipoprodserv, c.idcontaitem FROM prodserv p 
            JOIN prodservcontaitem pc ON pc.idprodserv = p.idprodserv AND pc.status = 'ATIVO'
            JOIN contaitem c ON c.idcontaitem = pc.idcontaitem 
            WHERE p.idprodserv =" . $itemorigem;

    $res = d::b()->query($sql);
    $resum = mysqli_fetch_assoc($res);

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
    $idagencia = traduzid('retornoremessa', 'idretornoremessa', 'idagencia', $idretornoremessa);
    $rwUnid = PedidoController::buscarUnidadePorIdtipoIdempresa($idtipounidade, $empresadestino);

    cnf::$idempresa = $empresadestino;
    $_idempresa = cb::idempresa();
    $arrconfCP = cnf::getDadosConfContapagar('TAXA BOLETO');

    $idfluxostatus = FluxoController::getIdFluxoStatus($modulo, 'APROVADO');
    $insnf2 = new Insert();
    $insnf2->setTable("nf");
    $insnf2->status = 'APROVADO';
    $insnf2->idunidade = $rwUnid['idunidade'];
    $insnf2->idfluxostatus = $idfluxostatus;
    $insnf2->idempresa = $empresadestino;
    $insnf2->idformapagamento = $formapagamentodestino;
    $insnf2->tiponf = 'O';
    $insnf2->idobjetosolipor = $idretornoremessa;
    $insnf2->tipoobjetosolipor = 'retornoremessatransferencia';
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

    FluxoController::inserirFluxoStatusHist('nfentrada', $idnf2, $idfluxostatus, 'APROVADO');

    $sql = "SELECT p.idtipoprodserv, c.idcontaitem FROM prodserv p 
            JOIN prodservcontaitem pc ON pc.idprodserv = p.idprodserv AND pc.status = 'ATIVO'
            JOIN contaitem c ON c.idcontaitem = pc.idcontaitem 
            WHERE p.idprodserv =" . $itemdestino;

    $res = d::b()->query($sql);
    $resum = mysqli_fetch_assoc($res);

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
