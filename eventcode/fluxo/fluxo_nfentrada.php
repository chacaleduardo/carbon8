<?
require_once("../api/nf/index.php");
require_once("../form/controllers/nfentrada_controller.php");

$botaoStatus = '';
$problema = array();

if (!empty($_idobjeto)) { // Não permitir concluir uma nota dos tipos do select sem rateio
    $statuspendente = 'N';
    $problema = array();
    $i = 0;
    $sqls = "select 'RATEIO' as problema
                from nf n 
                join  nfitem i  on(i.idnf=n.idnf and  i.idpessoa is  null  and i.nfe='Y' -- and i.vlritem >0
                 and i.qtd>0   )	
                join contaitem c on(c.idcontaitem= i.idcontaitem and c.somarelatorio = 'Y')	
            where n.tiponf in ('T','S','M','E','R','D','B') 
            and n.tipocontapagar='D'
            and n.geracontapagar = 'Y'
             and not exists(select 1 from  rateioitem ri  
                                join rateioitemdest rd on(rd.idrateioitem = ri.idrateioitem)
                                where( ri.idobjeto = i.idnfitem 
                                and ri.tipoobjeto = 'nfitem'))
            and n.idnf=".$_idobjeto."
            union 
            select  'RATEIO' as problema
                                from nfitem i JOIN nf n ON n.idnf = i.idnf
                                        join contaitem c on(c.idcontaitem= i.idcontaitem and c.somarelatorio = 'Y')                                      
                                where i.nfe='Y' 
                                and i.idprodserv is null
                                and i.idpessoa is null   
                                and i.qtd>0      
                                and n.tipocontapagar = 'D'
                                and n.geracontapagar = 'Y'
                                -- and i.vlritem >0
                                and not exists(select 1 from  rateioitem ri  
                                            join rateioitemdest rd on(rd.idrateioitem = ri.idrateioitem)
                                            where( ri.idobjeto = i.idnfitem 
                                            and ri.tipoobjeto = 'nfitem' ))                    
                                and i.idnf =".$_idobjeto;

    $ress = d::b()->query($sqls) or die("Erro ao buscar rateios da nota sql=" . $sqls);
    $qtd = mysqli_num_rows($ress);
    if ($qtd > 0) {
        $statuspendente = 'Y';
        while ($row = mysqli_fetch_assoc($ress)) {
            $problema[$i] = $row['problema'];
            $i++;
        }
    }
    
    $tiponf = traduzid('nf', 'idnf', 'tiponf', $_idobjeto);
    if($tiponf == 'C' || $tiponf == 'T' || $tiponf == 'O'){
        $_valorNf = NfEntradaController::buscarValorNfitemXmlNfItem($_idobjeto);
        $itensFaturamento = NfEntradaController::buscarValorItensFaturamento($_idobjeto, 'nf', 'gnre', $_idobjeto);
        if(
            ($_valorNf['internacional'] == 'N' && $_valorNf['valorxml'] > 0) && ($_valorNf['valorxml'] != $_valorNf['valor']) || 
            (($_valorNf['internacional'] == 'Y' && $_valorNf['valorxml'] != null) && $_valorNf['valorxml'] != $itensFaturamento['valor'])

            ){
            $i++;
            $statuspendente = 'Y';
            $problema[$i] = 'DIFERENCAENTREITENS';
        }

        $itemfatura = NfEntradaController::buscaNfitemFaturar($_idobjeto);
        if(count($itemfatura)<1){
            $i++;
            $statuspendente = 'Y';
            $problema[$i] = 'ENTRADAPREENCHIDA';
        }
    }

    $sqlPagamento = "SELECT idformapagamento, geracontapagar FROM nf WHERE idnf = $_idobjeto";
    $rePagamento = d::b()->query($sqlPagamento) or die("Erro ao buscar Anexo: <br>" . mysqli_error(d::b()));
    $pagamento = mysqli_fetch_assoc($rePagamento);
    if (empty($pagamento['idformapagamento']) && $pagamento['geracontapagar'] == 'Y') {
        $i++;
        $statuspendente = 'Y';
        $problema[$i] = 'SEMPAGAMENTO';
        $botaoStatus = array('PREVISAO', 'INICIO RECEBIMENTO', 'CONFERIDO', 'DIVERGENCIA', 'APROVADO', 'CORRIGIDO', 'CONCLUIDO');
    }

    if(NfEntradaController::buscarItemValorNulo($_idobjeto) > 0){
        $i++;
        $statuspendente = 'Y';
        $problema[$i] = 'ITENSNULOS';
        $botaoStatus = array('PREVISAO', 'INICIO RECEBIMENTO', 'CONFERIDO', 'DIVERGENCIA', 'APROVADO', 'CORRIGIDO', 'CONCLUIDO');
    }

    $produtos = CotacaoController::buscarProdutoPorNfItem($_idobjeto);
	$bloqueiaLoteAutomatico = false;
    foreach($produtos as  $_produto) {
        if($_produto['geraloteautomatico'] == 'Y' && empty($_produto['modulo'])){
            $bloqueiaLoteAutomatico = true;
            $produtosArray = [$_produto['descr']];
        }
    }

    if($bloqueiaLoteAutomatico){
        $produtos = implode("\n", $produtosArray);
        $i++;
        $statuspendente = 'Y';
        $problema[$i] = 'SEMMODULO';
        $listagem[$i] = $produtos;
        $botaoStatus = array('PREVISAO', 'INICIO RECEBIMENTO', 'CONFERIDO', 'DIVERGENCIA', 'APROVADO', 'CORRIGIDO', 'CONCLUIDO');

    }
}

$sql = "SELECT * FROM nf WHERE idnf = $_idobjeto AND prazo IS NULL";
$res = d::b()->query($sql) or die("Erro ao buscar data entrada vazia: <br>" . mysqli_error(d::b()));
$qtdres = mysqli_num_rows($res);
if ($qtdres > 0) {
    $i++;
    $statuspendente = 'Y';
    $problema[$i] = 'DATAENTRADA';
    $botaoStatus = array('INICIO RECEBIMENTO', 'CONFERIDO', 'DIVERGENCIA', 'CORRIGIDO', 'CONCLUIDO');
}

if (!empty($_idobjeto) and  $statuspendente == 'N') {

    $problema = array();
    $i = 0;
    $tiponf = traduzid('nf', 'idnf', 'tiponf', $_idobjeto);
    if ($tiponf == 'C') {
        $sql = "SELECT (SELECT sum(valor)+sum(ifnull(valipi,0))+sum(ifnull(outro,0))-sum(des)+sum(frete)+sum(vst)
            from nfitemxml where idnf=".$_idobjeto." and status='Y') as valorxml,'DIFERENCA' as problema,
            round((SELECT  sum(i.total)++sum(ifnull(i.valipi,0))+sum(n.frete/ (select count(*) 
            from nfitem ii  where ii.idnf=".$_idobjeto." and ((ii.nfe = 'Y') or (ii.nfe = 'N' and ii.cobrar='N')))) 
            from nfitem i join nf n on(n.idnf=i.idnf) where i.idnf=".$_idobjeto." and ((i.nfe = 'Y')  or (i.nfe = 'N' and i.cobrar='N') 
                or( i.nfe='N' and i.idprodserv is null))),2) as valor";
        $res = d::b()->query($sql) or die("Erro ao buscar valor dos itens da Nota Fiscal: <br>" . mysqli_error(d::b()));

        $row = mysqli_fetch_assoc($res);
        if (($row['valorxml'] > 0) and ($row['valorxml'] != $row['valor'])) {
            $i++;
            $statuspendente = 'Y';
            $problema[$i] = $row['problema'];
        } elseif (empty($row['valorxml'])) {
            $i++;
            $statuspendente = 'Y';
            $problema[$i] = 'ATUALIZARXML';
        }


        //Verifica se todos os campos do XML estão associados
        $sqlXml = "SELECT 1 FROM nfitemxml WHERE idnf = $_idobjeto AND (idprodserv IS NULL OR idprodserv = 0) AND status = 'Y'";
        $resXml = d::b()->query($sqlXml) or die("Erro ao buscar IdProdserv associado ao XML : <br>" . mysqli_error(d::b()));
        $qtdXml = mysqli_num_rows($resXml);
        if ($qtdXml > 0) {
            $i++;
            $statuspendente = 'Y';
            $problema[$i] = 'XMLASSOCIADO';
        } else {
            $sqlXml = "SELECT 
                            *
                        FROM
                            nfitem
                        WHERE
                            cobrar = 'Y' AND idnf = " . $_idobjeto;
            $resXml = d::b()->query($sqlXml) or die("Erro ao buscar se tem item faturado : <br>" . mysqli_error(d::b()));
            $qtdXml = mysqli_num_rows($resXml);

            if ($qtdXml < 1) {
                $i++;
                $statuspendente = 'Y';
                $problema[$i] = 'ITEMCOBRANCA';
            }
        }
      
        $sqlXml = "SELECT 1 FROM nfitemxml WHERE idnf = $_idobjeto AND cfop >'3999' and status='Y'";
        $resXml = d::b()->query($sqlXml) or die("Erro ao buscar se tem item cfop : <br>" . mysqli_error(d::b()));
        $qtdXml = mysqli_num_rows($resXml);

        if ($qtdXml > 0) {
            $i++;
            $statuspendente = 'Y';
            $problema[$i] = 'CFOPNAOCONVERTIDO';
        }

    } else if ($tiponf == 'T') {   

        $sql = "select * from nf where idnf=".$_idobjeto." and (xmlret is null or xmlret ='' )";
        $res = d::b()->query($sql) or die("Erro ao buscar data xml vazio: <br>" . mysqli_error(d::b()));
        $qtdres = mysqli_num_rows($res);
        if ($qtdres > 0) {
            $i++;
            $statuspendente = 'Y';
            $problema[$i] = 'XMLASSOCIADOCTE';
        }
    }

    //Verifica se foi gerada Parcela
    $sqlParcela = "SELECT 1 FROM nfconfpagar WHERE idnf = $_idobjeto";
    $resParcela = d::b()->query($sqlParcela) or die("Erro ao buscar IdProdserv associado ao XML : <br>" . mysqli_error(d::b()));
    $qtdParcela = mysqli_num_rows($resParcela);

    $sql = "SELECT geracontapagar FROM nf WHERE idnf = ".$_idobjeto."";
    $res = d::b()->query($sql) or die("Erro ao buscar geracontapagar: <br>" . mysqli_error(d::b()));
    $rowgcp = mysqli_fetch_assoc($res);

    if ($qtdParcela == 0 && $rowgcp['geracontapagar'] == 'Y') {
        $i++;
        $statuspendente = 'Y';
        $problema[$i] = 'GERARPARCELAS';
    }

    $sqlGrupoES = "SELECT 1 FROM nfitem WHERE idnf = ".$_idobjeto." AND (idcontaitem IS NULL OR idtipoprodserv IS NULL);";
    $resGrupoES = d::b()->query($sqlGrupoES) or die("Erro ao buscar IdProdserv associado ao XML : <br>" . mysqli_error(d::b()));
    $qtdGrupoES = mysqli_num_rows($resGrupoES);
    if ($qtdGrupoES > 0) {
        $i++;
        $statuspendente = 'Y';
        $problema[$i] = 'GRUPOESTIPOITEM';
    }

    $sqlPendencia = "SELECT 1 FROM nfpendencia WHERE idnf = ".$_idobjeto." AND status = 'PENDENTE';";
    $resPendencia = d::b()->query($sqlPendencia) or die("Erro ao buscar IdProdserv associado ao XML : <br>" . mysqli_error(d::b()));
    $qtdPendencia = mysqli_num_rows($resPendencia);
    if ($qtdPendencia > 0) {
        $i++;
        $statuspendente = 'Y';
        $problema[$i] = 'PENDENCIA';
        $botaoStatus = 'CORRIGIDO';
    }

    $sqlConfereQtdLote = "SELECT ni.qtd, l.qtdprod FROM nfitem ni LEFT JOIN lote l ON l.idnfitem = ni.idnfitem
                            JOIN nf n ON n.idnf = ni.idnf
                            JOIN prodserv p ON p.idprodserv = ni.idprodserv AND p.geraloteautomatico = 'Y' 
                            JOIN empresa e ON e.idempresa = ni.idempresa
                           WHERE ni.idnf = '$_idobjeto'
                             AND ni.nfe = 'Y'
                             AND e.filial = 'N'
                             AND NOT EXISTS (SELECT 1 FROM nf npe WHERE npe.idnf = n.idobjetosolipor AND npe.tiponf = 'V')
                        GROUP BY ni.idnfitem
                          HAVING ni.qtd != COALESCE(SUM(l.qtdprod), 0);";
    $resConfereQtdLote = d::b()->query($sqlConfereQtdLote) or die("Erro ao buscar Itens sem Lote : <br>" . mysqli_error(d::b()));
    $qtdConfereQtdLote = mysqli_num_rows($resConfereQtdLote);
    if ($qtdConfereQtdLote > 0) {
        $i++;
        $statuspendente = 'Y';
        $problema[$i] = 'ITENSSEMLOTE';
        $botaoStatus = 'CONCLUIDO';
    }

    $sqls="SELECT n.total, sum(ifnull(i.valor,0)) AS valor, n.geracontapagar
            FROM nf n LEFT JOIN contapagaritem i ON i.idobjetoorigem = n.idnf AND i.tipoobjetoorigem = 'nf' AND i.status != 'INATIVO'
            WHERE n.idnf = $_idobjeto";

    $res = d::b()->query($sqls) or die("erro ao buscar valores do pedido e faturas " . mysqli_error(d::b()) . "<p>SQL: ".$sqls);

    $_row = mysqli_fetch_assoc($res);

    if($_row['total'] != $_row['valor'] && $_row['geracontapagar'] == 'Y'){
    
        $i++;
        $problema[$i] = 'TOTALNFVALORFATCOMPRA';
        $botaoStatus = 'CONCLUIDO';
    }
}

$botaoStatusExec = empty($botaoStatus) ? 'CONCLUIDO' : $botaoStatus;
$status['permissao']['modulo'] = 'nfentrada';
$status['permissao']['esconderbotao'] = $statuspendente;
$status['permissao']['status'] = $botaoStatusExec;
$status['permissao']['problema'] = $problema;
$status['permissao']['listagem'] = $listagem;

?>