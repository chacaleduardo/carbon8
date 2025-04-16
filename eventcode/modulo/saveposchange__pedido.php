<? 
require_once(__DIR__."/../../form/controllers/fluxo_controller.php");
require_once("../api/nf/index.php");
require_once(__DIR__."/../../form/controllers/pedido_controller.php");
cnf::$idempresa = isset($_GET["_idempresa"])?$_GET["_idempresa"]:$_SESSION["SESSAO"]["IDEMPRESA"];

$idnotafiscal = $_POST["_1_u_nf_idnf"];
$idunidade = $_POST["_1_u_nf_idunidade"];
$qtdparcelas = $_POST["_1_u_nf_parcelas"];
$total = tratanumero($_POST["_1_u_nf_total"]);
$idobjetosolipor = $_POST["_1_i_nf_idobjetosolipor"];
$tipoobjetosolipor = $_POST["_1_i_nf_tipoobjetosolipor"];
$modfrete = $_POST["_1_u_nf_modfrete"];
$tipocontapagar = $_POST["_1_u_nf_tipocontapagar"];
$tiponf = $_POST["_1_u_nf_tiponf"];
$tipoorc = $_POST["_1_u_nf_tipoorc"];
$status = $_POST["_1_u_nf_status"];
$idformapagamento = $_POST["_1_u_nf_idformapagamento"];
$statusant = $_POST["statusant"];
$idformapagamentoant = $_POST["_nf_idformapagamentoant"];
$emissao = $_POST["_1_u_nf_dtemissao"];
$dtemissao = $_POST["_1_u_nf_dtemissao"];
$geracontapagar = $_POST["_1_u_nf_geracontapagar"];
$idcontaitem = $_POST["_1_u_nf_idcontaitem"];
$comissao  = $_POST['_1_u_nf_comissao'];
$idpessoa = $_POST['_1_u_nf_idpessoafat'];
$altitem = $_POST['_1_u_contapagaritem_idcontapagaritem'];
$entrega = $_POST['_1_u_nf_entrega'];
$idnfepedido = $_POST['idnfepedido'];
$idnfecotacao = $_POST['_1_u_nf_idnfe'];
$idnatop = $_POST['_1_u_nf_idnatop'];
//$_alterar_valor_parcela = $_POST['_alterar_valor_parcela']; se colocar isso não altera a fatura de aberto para fechado ao aprovar a compra




$_idempresa = isset($_GET["_idempresa"])?$_GET["_idempresa"]:$_SESSION["SESSAO"]["IDEMPRESA"];

if(empty($idpessoa)){
  $idpessoa = $_POST['_1_u_nf_idpessoa'];  
}

$temgnre = $_POST['nf_gnre'];
$nnfe = $_POST['_1_u_nf_nnfe'];
$gnre = $_POST['_1_u_nf_gnre'];
$gnreval = $_POST['gnreval'];
$idcontapagar_remessa = $_POST['idcontapagar_remessa'];

//IMPOSTO DE SERVICO
$nf_pis=tratanumero($_POST['_1_u_nf_pis']);
$nf_cofins=tratanumero($_POST['_1_u_nf_cofins']);
$nf_csll=tratanumero($_POST['_1_u_nf_csll']);

$nf_darf = $nf_pis+$nf_cofins+$nf_csll;
//ser for do RH vai e iver pis ou cofins ou csll vai gear CSRF
if($tiponf =='R'){
    $nf_csll =$nf_darf;
}

$nf_inss=tratanumero($_POST['_1_u_nf_inss']); 
$nf_ir=tratanumero($_POST['_1_u_nf_ir']);
$nf_issret=tratanumero($_POST['_1_u_nf_issret']);

$geraparcela="N";

//Verifica se tem algum item marcado no pedido  quando o status for SOLICITADO
if(($status == "SOLICITADO" || $status == "PEDIDO" || $status == "PRODUCAO" || $status == "EXPEDICAO" || $status == "FATURAR") && $statusant != $status) {    
	$row = PedidoController::buscarNfitemDanfe($idnotafiscal);
    if($row['contador'] == 0){
        die("Selecionar ao menos um item.");
    }
}



//se for um pedido com natop devolução vai mudar a categoria e subcategoria,  danfe com finalidade de devolução tambem muda
if(!empty($idnotafiscal) and $tiponf =='V' and ($status=="CONCLUIDO" or $status == "DEVOLVIDO")){// se a empresa emitiu a nota de devolução
    // busca se a natureza da operação é devolução
	$resdev = PedidoController::buscarCategoriaDevolucao($idnotafiscal);

    $qtddev=count($resdev);
    // echo("qtd ".$Vqtdnfitem);
    if($qtddev>0){
        foreach($resdev as $row){  
            if(!empty($row['idcontaitem']) and !empty($row['idtipoprodserv'])){
                PedidoController::atualizarCategoriaSubcategoriaNfItem($idnotafiscal,$row['idcontaitem'],$row['idtipoprodserv']);
            } 
        }
        PedidoController::AtualizaParcelaPendentePorIdobjeto($idnotafiscal,'nf', 'DEVOLVIDO');
    }
}elseif(!empty($idnotafiscal) and $tiponf =='C' and ($status=="CONCLUIDO" or $status == "DEVOLVIDO")){// se o cliente emitiu a nota de devolução
    // busca se a natureza da operação é devolução
	$resdev = PedidoController::buscarCategoriaDevolucaoEntrada($idnotafiscal);
   
    $qtddev=count($resdev);
    // echo("qtd ".$Vqtdnfitem);
   
    if($qtddev>0){

        foreach($resdev as $row){  
            if(!empty($row['idcontaitem']) and !empty($row['idtipoprodserv'])){
               
                PedidoController::atualizarCategoriaSubcategoriaNfItem($idnotafiscal,$row['idcontaitem'],$row['idtipoprodserv']);
            } 
        }
        PedidoController::AtualizaParcelaPendentePorIdobjeto($idnotafiscal,'nf', 'DEVOLVIDO');

       
    }
}




//Verificar se já foi criada nfentrada
$nfconfpagar = NfEntradaController::buscarNfconfpagarOrdenadoPorOrdemDescrescente($_SESSION["_pkid"]);

//Trecho de codigo para gerar as parcelas INICIO
$iu = $_SESSION['arrpostbuffer']['1']['u']['nf']['idnf'] ? 'u' : 'i';
//Gerar a configuração das parcelas 
if(!empty($_SESSION['arrpostbuffer']['1'][$iu]['nf']['idnf']) && $nfconfpagar['qtdLinhas'] == 0){
    $idnf = $_SESSION["_pkid"];    
    $insnfconfpagar = new Insert();
    $insnfconfpagar->setTable("nfconfpagar");
    $insnfconfpagar->idnf = $idnf;    
    $idnfconfpagar = $insnfconfpagar->save();    
}

//Trecho de codigo para gerar as parcelas INICIO
$iup = $_SESSION['arrpostbuffer']['x']['i']['nf']['idpessoa'] ? 'i' : 'u';
//Gerar a configuração das parcelas 
if($iup=='i' and !empty($_SESSION['arrpostbuffer']['x']['i']['nf']['idpessoa'])){
    $idnf = $_SESSION["_pkid"];    
  
    $parc= $_SESSION['arrpostbuffer']['x']['i']['nf']['parcelas'];
    if($parc<1){$parc=1;}

    for($v = 0; $v < $parc; $v++) {
        $insnfconfpagar = new Insert();
        $insnfconfpagar->setTable("nfconfpagar");
        $insnfconfpagar->idnf = $idnf;   
        $idnfconfpagar = $insnfconfpagar->save();
    }

}

$_SESSION['arrpostbuffer']['1'][$iu]['nf']['nnfe'] = $nnfe;

if(!empty($idobjetosolipor) and $tipoobjetosolipor=='nf'){
  copiarnfitem($idobjetosolipor,$_SESSION["_pkid"]);

  $_GET["idnf"] = $_SESSION["_pkid"];
  $_GET["_acao"]='u';


}

// alterar cliente de faturamento calcula novamente os impostos
$lf_idnf = $_SESSION["arrpostbuffer"]["alt"]["u"]["nf"]["idnf"];
$lf_idpessoafat = $_SESSION["arrpostbuffer"]["alt"]["u"]["nf"]["idnf"];
if(!empty($lf_idnf)){
    cnf::impostoItemPedido($lf_idnf);
}



//relacionado a geracao da solicitacao de fabricacao
 $idsolfab = $_POST['_1_u_lote_idsolfab'];
 if($idsolfab=='novo'){

    $v_idempresa = traduzid('lote', 'idlote', 'idempresa', $_SESSION["_pkid"]);
    $vidunidade = getUnidadePadraoModulo('solfab',$v_idempresa);  
    $idfluxostatusSolfab = FluxoController::getIdFluxoStatus('solfab', 'ABERTO');

    //die($v_idempresa.'- '.$vidunidade);
    $idpessoa = $_POST['_1_i_lote_idpessoa'];
    $insolfab = new Insert();
    $insolfab->setTable("solfab");
    $insolfab->idempresa=$_POST['_1_i_lote_idempresa']; 
    $insolfab->idlote = $_SESSION["_pkid"]; 
    $insolfab->idpessoa = $idpessoa; 
    $insolfab->idunidade = $vidunidade; 
    $insolfab->idfluxostatus = $idfluxostatusSolfab;     
    $idsolfab = $insolfab->save();
        
    PedidoController::atualizaLoteSolfab($idsolfab,$_SESSION["_pkid"]);
    FluxoController::inserirFluxoStatusHist('solfab', $idsolfab, $idfluxostatusSolfab,'PENDENTE');
 }

//Verifica o tipo de NF
if($tiponf=="V"){

    $resnd=PedidoController::buscarFinalidadeNatop($idnotafiscal);

    if($resnd['finnfe']==4){
        $tipo="D";
    }else{
        $tipo="C";
    }

	//Ajusta tipo, geraparcela, difdias, diasentrada, intervalo
   
    $geraparcela="S";    
  //  $difdias = 0;// no caso de credito so e atualizado na conta 2 dias apos o pagamento *atualizado para 0 o dias foram para a trigger na contapagar 21012015
    $diasentrada = $_POST["_1_u_nf_diasentrada"]-1;
    $intervalo = $_POST["_1_u_nf_intervalo"];
    $visivel='S';
   // $contapagaritem="N";

}elseif($tiponf=="C" or $tiponf=="T" or $tiponf=="S" or $tiponf=="E" or $tiponf=="M" or $tiponf=="F" or $tiponf=="B"  or $tiponf=="O"){//if($tiponf=="V"){

    //Ajusta tipo, geraparcela, difdias, diasentrada, intervalo
    if($tipocontapagar){
	$tipo = $tipocontapagar;
    }else{
	$tipo="D";	
    }
    $geraparcela="S";
   // $difdias = 0;//debito e no mesmo dia
    $diasentrada = $_POST["_1_u_nf_diasentrada"];
    $intervalo = $_POST["_1_u_nf_intervalo"];
    $visivel="S";

}elseif( $tiponf=="D" or $tiponf=="R"){
    //Ajusta tipo, geraparcela, difdias, diasentrada, intervalo
    if($tipocontapagar){
	$tipo = $tipocontapagar;
    }else{
	$tipo="D";	
    }
    
    $geraparcela="S";
   // $difdias = 0;//debito e no mesmo dia
    $diasentrada = $_POST["_1_u_nf_diasentrada"];
    $intervalo = $_POST["_1_u_nf_intervalo"];
    $visivel="N";

}else{
	$diasentrada = $_POST["_1_u_nf_diasentrada"];
	$intervalo = $_POST["_1_u_nf_intervalo"];
	$visivel='N';
}

if($tiponf=="V"){
    alteraFrete($modfrete,$iu,$idnotafiscal,$statusant,$tiponf);
}

//LTM 16-09-2020 - 373161: Insere no campo Entrega a mesma data de entrega no CTe 
if(!empty($entrega)){
    $entregaFormat = explode('/', $entrega);
    $entregaFormat = $entregaFormat[2].'-'.$entregaFormat[1].'-'.$entregaFormat[0];
    if(!empty($idnfepedido)){

        PedidoController:: atualizaDataEntregaCte($entregaFormat,substr($idnfepedido,3));
    }
    
    if(!empty($idnfecotacao)){       

        PedidoController:: atualizaDataEntregaCtePorIdnf($entregaFormat,substr($idnfecotacao,3));

    }   
}

//SE CANCELAR A NOTA DELETA AS PARCELAS
//LTM: 05-10-2020 - 375925: Acrescentado status ABERTO e RESPONDIDO quando restaurar ou voltar o status, pois as parcelas serão criadas somente no status APROVADO
//PHOL: 26-02-2024 - 687119: Acrescentado status CORRIGIDO
if ((!empty($idnotafiscal)) && (($status == "CORRIGIDO" || $status == "CANCELADO" ||  $status == "DEVOLVIDO" || $status =="DENEGADA" || $status == "REPROVADO" || $status == "SOLICITADO" || $status == "ABERTO" || $status == "RESPONDIDO") || ($geracontapagar=='N') || ($status == "INICIO" && empty($valorapp)))){
    
    $arrParcItens=getParcelaItens($idnotafiscal);
    $qtParcelasitem = $arrParcItens['quant'];
    $arrParcelas=recuperaParcelas($idnotafiscal,'QUITADO','nf');//Contapagar Quitado
    $qtParcelas  = $arrParcelas['quant'];
    $arrlinhasbol = verificaboleto($idnotafiscal);
    $qtdlinhasbol = $arrlinhasbol['quant'];
    
    if($qtParcelasitem==0 and $qtParcelas==0 and  $qtdlinhasbol==0){
//cancelamento somente nota emitida nfcancel
        if( (/*$status == "CANCELADO" || */ $status == "DEVOLVIDO") && ($tiponf=="V") ){
                         

            PedidoController::AtualizaParcelaPendentePorIdobjeto($idnotafiscal,'nf', $status);

            /*
            * deleta as parcelas existentes.
            */
            //comissão
            PedidoController::deletaParcelaComissaoPendentePorIdobjeto($idnotafiscal,'nf');

           
                     
            // GVT - 23/07/2021 - @471938 remover assinatura de contas a pagar que serão apagadas
            $resCarrimbo = PedidoController::buscarFaturaSemComissaoPorIdobjeto($idnotafiscal,'nf');

            if(count($resCarrimbo) > 0){
                foreach($resCarrimbo as $rCarrimbo ){   
                    //deletar carimbo pendente    
                    PedidoController::deletarPorIdObjetoTipoObjetoEIdPessoa($rCarrimbo["id"],'contapagar',798);
                }
            }
/* cancelamento somente nota emitida nfcancel
            if(  $status == "CANCELADO"  ){

                PedidoController::deletaParcelaImpostoPendentePorIdobjeto($idnotafiscal,'nf');
                
                $resdev = PedidoController::buscarCategoriaCancelado($idnotafiscal);

                $qtddev=count($resdev);
                // echo("qtd ".$Vqtdnfitem);
                if($qtddev>0){
                    foreach($resdev as $row){  
                        PedidoController::atualizarCategoriaSubcategoriaNfItem($idnotafiscal,$row['idcontaitem'],$row['idtipoprodserv']);
                    
                    }
                }
            }
  */          
            /*else{
                $resdev = PedidoController::buscarCategoriaDevolucao($idnotafiscal);

                $qtddev=count($resdev);
                // echo("qtd ".$Vqtdnfitem);
                if($qtddev>0){
                    foreach($resdev as $row){  
                        PedidoController::atualizarCategoriaSubcategoriaNfItem($idnotafiscal,$row['idcontaitem'],$row['idtipoprodserv']);
                    
                    }
                }

            }*/

        }else{
            deletaParcelasExistentes($idnotafiscal);
        }
    	

        if($status == "CANCELADO" OR $status =="DENEGADA" OR $status == "REPROVADO" or  $geracontapagar=='N'){
            $resf=PedidoController::buscarNfitemPorIdobjetoTipoobjeto($idnotafiscal,'nf');
            $Vqtdnfitem=count($resf);
           // echo("qtd ".$Vqtdnfitem);
            if($Vqtdnfitem>0){
                foreach($resf as $row){                    
                    PedidoController:: deletarNfitemPorId($row['idnfitem']);
                    cnf::atualizavalornf($row['idnf']);
                    cnf::atualizafat($row['idnf']);                    
                }        
            }

            
            if($status == "CANCELADO" OR $status =="DENEGADA"){//se cancelar o pedido deleta os consumos
                
                PedidoController::deletaLoteconsPorIdNF($idnotafiscal);
                PedidoController::deletaLotereservaPorIdNF($idnotafiscal);

            } 
        }
    }
}

//SE FOR GERA PARCELA NÃO DELETA AS PARCELAS
IF((!empty($idnotafiscal)) AND $geracontapagar=='N'){      
    $arrParcItens=getParcelaItens($idnotafiscal);
    $qtParcelasitem = $arrParcItens['quant'];

    $arrParcelas=recuperaParcelas($idnotafiscal,'QUITADO','nf');//Contapagar Quitado
    $qtParcelas  = $arrParcelas['quant'];
    
    if($qtParcelasitem==0 and $qtParcelas==0 ){
    	deletaParcelasExistentes($idnotafiscal);
    }  
}

if(!empty($idformapagamento)){
    //buscar pois se for comissao não atualiza fatura
    $rfc=PedidoController:: buscarConfpagarComissao($idformapagamento);
    $qtdcom=count($rfc);
    
}else{
    $qtdcom=0;
}

/*
if($idnotafiscal==41065){
    echo('[Atualiz antes]');
    echo("gerapacela =".$geraparcela." idnotafiscal=".$idnotafiscal." Status=".$status." tipoorc=".$tipoorc." geracontapagar=".$geracontapagar." tiponf = ".$tiponf." qtdcom".$qtdcom." idformapagamento".$idformapagamento." _alterar_valor_parcela".$_alterar_valor_parcela); 
    //die();
}
*/
$atualizaimposto='N';

if (!empty($idnotafiscal) 
        and !empty($idformapagamento)
        and ( $status=="ENVIAR" || $status == "ENVIADO" || $status == "APROVADO" || $status == "PREVISAO" || $status == "PEDIDO" || $status == "PRODUCAO" 
              || $status == "EXPEDICAO" ||  $status == "FATURAR" || $status=="CONFERIDO" || $status == "CORRIGIDO"
                || ($status == "CONCLUIDO" && $statusant!="CONCLUIDO" && $statusant!="DIVERGENCIA" && $statusant!="RECEBIDO")
                || ($status == "DIVERGENCIA" && $statusant!="CONCLUIDO" && $statusant!="DIVERGENCIA") 
                || ($status == "RECEBIDO" && $statusant!="CONCLUIDO" && $statusant!="RECEBIDO") 
                || ($idformapagamentoant != $idformapagamento && $status == "CONCLUIDO")
              )
        && $geraparcela == "S" 
        && $tipoorc != "S" 
        && $geracontapagar == "Y" and $qtdcom < 1
        //&& $_alterar_valor_parcela != 'N' se colocar isso não altera a fatura de aberto para fechado ao aprovar a compra
        ){// 38 comissao não entrar  

        //$atualizaimposto='Y';
       
/*
            if($idnotafiscal==41065){
                echo('[Atualiza]');
            }
    */

    //BUSCAR CONFIGURAÇÕES DA FORMA DE PAGAMENTO
    $formapagamento = PedidoController::buscarInfFormapagamentoPorId($idformapagamento);
    cnf::$idempresa = $formapagamento['idempresa'];    
   
    $arrParcelas = recuperaParcelas($idnotafiscal,'QUITADO','nf');//Contapagar Quitado
    $qtParcelas  = $arrParcelas['quant'];

    $arrParcelasDEV = recuperaParcelas($idnotafiscal,'DEVOLVIDO','nf');//Contapagar Quitado
    $qtParcelasDev  = $arrParcelasDEV['quant'];
    
    $arrParcelasFechado = recuperaParcelas($idnotafiscal,'FECHADO','nf');//Contapagar fechado
    $qtParcelasFechadas  = $arrParcelasFechado['quant'];

    $qtParcelasProg = recuperaParcelasProg($idnotafiscal,'nf');//Contapagar programada  
    
    $arrParcelasIV = recuperaParcelasItensVinc($idnotafiscal,'nf');
    $qtParcelasIV  = $arrParcelasIV['quant'];    
    
    $arrlinhasbol = verificaboleto($idnotafiscal);
    $qtdlinhasbol = $arrlinhasbol['quant'];

    $arrParcItens = getParcelaItens($idnotafiscal);
    $qtParcelasitem = $arrParcItens['quant'];
    
    $arrParcItensFechada = getParcelaItensfechada($idnotafiscal,$formapagamento['agrupnota']);
    $qtParcelasitemFechada = $arrParcItensFechada['quant'];  

    
    if ($qtParcelas == 0 && $qtParcelasProg < 1 && $qtdlinhasbol == 0 && $qtParcelasitem == 0 && $qtParcelasIV == 0 && $qtParcelasFechadas == 0 && $qtParcelasitemFechada == 0 && $qtParcelasDevx == 0){
	   
        $atualizaimposto='Y';
        
        //deleta as parcelas existentes.
    	deletaParcelasExistentes($idnotafiscal);
      
        //@499890 - ERRO AO GERAR PARCELA / COMISSÃO
	    if($status == "INICIO" || $status == "CORRIGIDO" || $status == "SOLICITADO" || $status == "PRODUCAO" || $status == "EXPEDICAO" || $status == "FATURAR" || $status == "PREVISAO" || $status == "PEDIDO"){
            $statuscp='ABERTO';
        }else{
            $statuscp='PENDENTE';
        }

        if(empty(trim($diasentrada))){
            $diasentrada = '0';		
        }

        $nfDataRecb = CotacaoController::buscarIdNfConfPagar($idnotafiscal);
        $nfConfPagarQtd = count($nfDataRecb);
        if($nfConfPagarQtd == 0 || ($nfConfPagarQtd ==1 && ($nfDataRecb[0]['datareceb'] == '0000-00-00' || empty($nfDataRecb[0]['datareceb']))))
        {
            CotacaoController::apagarNfConfPagarPorIdnf($idnotafiscal);
            //gerar as configurações
            geranfconfpagar($idnotafiscal);
    
            $nfDataRecb = CotacaoController::buscarIdNfConfPagar($idnotafiscal);
            $nfConfPagarQtd = count($nfDataRecb);
            if($nfConfPagarQtd == 0 || ($nfConfPagarQtd == 1 && ($nfDataRecb[0]['datareceb'] == '0000-00-00' || empty($nfDataRecb[0]['datareceb']))))
            {
    
            echo 'Favor Gerar as Parcelas. Não foram criadas corretamente.';
            die();
            }
        }

        $rescx = PedidoController::buscarNfconfpagarPorIdnf($idnotafiscal);
        $qtdparcelas = count($rescx);
        
        if(empty($qtdparcelas)){ $qtdparcelas=1;}


      
        $row = PedidoController::somarFretePorIdnf($idnotafiscal);
        
        $rowct = PedidoController::somarProporcaoNfconfpagarPorIdnf($idnotafiscal);
        if($rowct['proporcao'] != 100 && $rowct['proporcional'] == 'Y'){
            die("A soma das proporções deve ser 100!!! Verificar a proporção das faturas.");
        }
        
        $rototal = PedidoController::buscarNfPorId($idnotafiscal);
        if(!empty($rototal['total'])){
            $total = $rototal['total'];
        }

        $index = 0;
        foreach($rescx as $rowcx)
        {
            $index++;   
            /*         
            if($idnotafiscal==164204){
                print_r($rescx);
                echo('[loop]');
            }
            */   
                            
            //Insere novas parcelas
            if($rowcx['proporcional'] == 'Y'){
                $valorparcela = $total * ($rowcx['proporcao'] / 100);
                $valorparcelarep =(($total - $row['sumfrete']) / ($rowcx['proporcao'] / 100));
            }else{
                $valorparcela = $total/$qtdparcelas;                
                $valorparcelarep = (($total-$row['sumfrete']) / $qtdparcelas);
            }

            $vencimentocalc = $rowcx['datareceb'];
            $recebcalc = $rowcx['datareceb'];

            if($formapagamento['agrupado'] == 'Y'){//se for agrupado
                //GERA PARCELA DE COMISSÃO
                
                if($comissao == 'Y' and $temcomissao == 'Y' and $tipo=="C" ){//Se tiver comissao

                    //LTM - 31-03-2021: Retorna o Idfluxo ContaPagar
                    $idfluxostatus = FluxoController::getIdFluxoStatus('contapagar', 'PENDENTE');                       
                    //Cria a fatura
                    $incont = new Insert();
                    $incont->setTable("contapagar");
                    $incont->idempresa = $formapagamento['idempresa']; 
                    $incont->idformapagamento = $idformapagamento; 
                    $incont->tipoespecifico = 'AGRUPAMENTO'; 
                    $incont->idpessoa = $idpessoa; 
                    $incont->tipoobjeto='nf'; 
                    $incont->idobjeto = $idnotafiscal; 
                    $incont->parcela = $index; 
                    $incont->parcelas = $qtdparcelas; 
                    $incont->valor = $valorparcela; 
                    $incont->datapagto = $vencimentocalc; 
                    $incont->datareceb = $recebcalc; 
                    $incont->status = 'PENDENTE'; 
                    $incont->idfluxostatus = $idfluxostatus; 
                    $incont->tipo = $tipo; 
                    $incont->visivel = $visivel; 
                    $incont->intervalo = $intervalo; 
                    $incont->obs = $rowcx['obs']; 
                    $idcontapagar = $incont->save();                    
                    
                    if(empty($idcontapagar)){                              
                        die("1-Falha ao gerar fatura: " . mysql_error() . "<p>SQL: ".$tmpsqlins);
                    }else{
                        $idcontapagar=mysqli_insert_id(d::b());
                        FluxoController::inserirFluxoStatusHist('contapagar', $idcontapagar, $idfluxostatus, 'PENDENTE', cnf::$idempresa);
                    }

                    $inconti = new Insert();
                    $inconti->setTable("contapagaritem");
                    $inconti->idempresa = $formapagamento['idempresa'];
                    $inconti->status = 'PENDENTE';
                    $inconti->idpessoa = $idpessoa;
                    $inconti->idobjetoorigem = $idnotafiscal;
                    $inconti->tipoobjetoorigem='nf';
                    $inconti->idcontapagar = $idcontapagar;
                    $inconti->tipo = $tipo;
                    $inconti->visivel = $visivel;
                    $inconti->idformapagamento = $idformapagamento;
                    $inconti->parcela = $index;
                    $inconti->parcelas = $qtdparcelas;
                    $inconti->datapagto = $recebcalc;
                    $inconti->valor = $valorparcela;
                    $inconti->obs = $rowcx['obs'];
                    $idcontapagaritem = $inconti->save();    

                }else{//Nao tem comissao
                    $inconti = new Insert();
                    $inconti->setTable("contapagaritem");
                    $inconti->idempresa = $formapagamento['idempresa'];
                    $inconti->status = $statuscp;
                    $inconti->idpessoa = $idpessoa;
                    $inconti->idobjetoorigem = $idnotafiscal;
                    $inconti->tipoobjetoorigem = 'nf';                           
                    $inconti->tipo = $tipo;
                    $inconti->visivel = $visivel;
                    $inconti->idformapagamento = $idformapagamento;
                    $inconti->parcela = $index;
                    $inconti->parcelas = $qtdparcelas;
                    $inconti->datapagto = $recebcalc;
                    $inconti->valor = $valorparcela;
                    $inconti->obs = $rowcx['obs'];
                    $idcontapagaritem = $inconti->save();    
                    
                }
            }else{

                die("Forma de Pagamento ID ".$idformapagamento." não está configurada como agrupada.");

                //LTM - 31-03-2021: Retorna o Idfluxo ContaPagar
                $idfluxostatus = FluxoController::getIdFluxoStatus('contapagar', $statuscp);

                $incont = new Insert();
                $incont->setTable("contapagar");
                $incont->idempresa = $formapagamento['idempresa']; 
                $incont->idformapagamento = $idformapagamento; 
                if($tiponf == "F"){
                    $incont->tipoespecifico = 'AGRUPAMENTO'; 
                }
                $incont->idpessoa = $idpessoa; 
                $incont->tipoobjeto = 'nf'; 
                $incont->idobjeto = $idnotafiscal; 
                $incont->parcela = $index; 
                $incont->parcelas = $qtdparcelas; 
                $incont->valor = $valorparcela; 
                $incont->datapagto = $vencimentocalc; 
                $incont->datareceb = $recebcalc; 
                $incont->status = $statuscp; 
                $incont->idfluxostatus = $idfluxostatus; 
                $incont->tipo = $tipo; 
                $incont->visivel = $visivel; 
                $incont->intervalo = $intervalo; 
                $incont->obs = $rowcx['obs']; 
                $idcontapagar = $incont->save();

                //Insere a parcela
                if(empty($idcontapagar)){                           
                    die("1-Falha ao gerar fatura sem parcela: " . mysql_error() . "<p>SQL: ".$tmpsqlins);
                }else{
                    $idcontapagar=mysqli_insert_id(d::b());

                    // GVT - 23/07/2021 - @471938 lançamentos com diferença de 28 dias gera assinatura p/ o Fábio
                    //
                    // Não há a necessidade de verificar se já existe assinatura pendente, pois aqui sempre são criadas novas contas a pagar
                    $d1 = strtotime(date("Y-m-d"));
                    $d2 = strtotime($recebcalc);
                    $diff = ($d2 - $d1)/60/60/24;
                    if($diff < 28){
                        $insCarrimbo = new Insert();
                        $insCarrimbo->setTable("carrimbo");
                        $insCarrimbo->idempresa = cnf::$idempresa; 
                        $insCarrimbo->idpessoa = 798;                                
                        $insCarrimbo->idobjeto = $idcontapagar; 
                        $insCarrimbo->tipoobjeto='contapagar'; 
                        $insCarrimbo->idobjetoext = $idfluxostatus; 
                        $insCarrimbo->tipoobjetoext = 'idfluxostatus'; 
                        $insCarrimbo->status = 'PENDENTE'; 
                        $idcarrimbo = $insCarrimbo->save();

                    }

                    FluxoController::inserirFluxoStatusHist('contapagar', $idcontapagar, $idfluxostatus, 'PENDENTE',cnf::$idempresa);
                }
            }
        }//for ($index = 1; $index <= $qtdparcelas; $index++) {

       
        cnf::agrupaCP(); 
        d::b()->query("COMMIT") or die("Erro");
        if($comissao=='Y' and $tipo=="C" ){//inserir o item da comissao oculto na nota
            geracomissao($idnotafiscal);
            cnf::agrupaCP();
        }

        //corrigir parcelas em um centavo
        corrigirParcelas($idnotafiscal,$total,$formapagamento['agrupado']);
	} else {//if ($qtParcelas == 0 and $qtlinhasbol== 0 and $qtParcelasitem==0){       
 
        cnf::agrupaCP();  
    }         
        
//se não for para gerar parcela deletar as que estiverem pendentes
}elseif (!empty($idnotafiscal) and ($status == "CONCLUIDO" or $status == "DIVERGENCIA" or $status=="RECEBIDO" or $status == "PREVISAO") and $geracontapagar=="N"){

	$arrParcItens=getParcelaItens($idnotafiscal);
	$qtParcelasitem = $arrParcItens['quant'];

    $arrlinhasbol = verificaboleto($idnotafiscal);
    $qtdlinhasbol = $arrlinhasbol['quant'];
    
	if($qtParcelasitem == 0 and $qtdlinhasbol==0){
		/*
		 * deleta as parcelas existentes.
		 */
		deletaParcelasExistentes($idnotafiscal);//maf: confirmar o nome "parcelasComissao"
	}
}

/*
if($idnotafiscal==41065){
   die('FIM');
}
*/

if($comissao=='N' and !empty($idnotafiscal)){
	
	//Verifica se existe alguma parcela quitada. se existir, nao alterar nada.
	$arrComissoesQuit=getComissoesPendentes($idnotafiscal);
	$qtParcelas = $arrComissoesQuit['quant'];

    if ($qtParcelas >0){
    	deletaComissoesPendentes($idnotafiscal);//VErifica se pode apenas apagar
    }
}

//impostos 
//ATUALIZACAO

if(!empty($idnotafiscal) and $status != "CANCELADO" AND $status !="DENEGADA" and $status != "REPROVADO" and $geracontapagar=='Y'){

    $resf=PedidoController::buscarNfitemPorIdobjetoTipoobjeto($idnotafiscal,'nf');
    $Vqtdnfitem=count($resf);
   // echo("qtd ".$Vqtdnfitem);
    if($Vqtdnfitem>0){
        foreach($resf as$row){

            $r=PedidoController::buscarConfcontapagarInpostoServico($row['idconfcontapagar']);
          
            if($r['tipo']=='CSRF' and empty($nf_darf)){
                PedidoController:: deletarNfitemPorId($row['idnfitem']);
                cnf::atualizavalornf($row['idnf']);
                cnf::atualizafat($row['idnf']);
            }/*elseif($r['tipo']=='PIS' and empty($nf_pis)){
                PedidoController:: deletarNfitemPorId($row['idnfitem']);
                cnf::atualizavalornf($row['idnf']);
                cnf::atualizafat($row['idnf']);
            }elseif($r['tipo']=='COFINS' and empty($nf_cofins)){
                PedidoController:: deletarNfitemPorId($row['idnfitem']);
                cnf::atualizavalornf($row['idnf']);
                cnf::atualizafat($row['idnf']);
            }*/elseif($r['tipo']=='IRRF' and empty($nf_ir)){
                PedidoController:: deletarNfitemPorId($row['idnfitem']);
                cnf::atualizavalornf($row['idnf']);
                cnf::atualizafat($row['idnf']);
            }elseif($r['tipo']=='INSS' and empty($nf_inss)){
                PedidoController:: deletarNfitemPorId($row['idnfitem']);
                cnf::atualizavalornf($row['idnf']);
                cnf::atualizafat($row['idnf']);
            }elseif($r['tipo']=='ISS' and empty($nf_issret)){
                PedidoController:: deletarNfitemPorId($row['idnfitem']);
                cnf::atualizavalornf($row['idnf']);
                cnf::atualizafat($row['idnf']);
            }
        }        
    }    
}



//impostos CRIAÇÃO
if(!empty($idnotafiscal) and $status != "CANCELADO" and $status !="DENEGADA" and $status != "REPROVADO"){

    if(!empty($gnreval) and !empty($gnre) and !empty($dtemissao) and !empty($idnotafiscal)){
        impostopedido($_POST,$gnreval,$dtemissao,$idnotafiscal,'GNRE',$gnre);
    }
    if(!empty($nf_issret) and ($nf_issret>0) and !empty($dtemissao) and !empty($idnotafiscal)){
       // geraparcelaimposto($_POST,$nf_issret,$dtemissao,$idnotafiscal,'ISS',$obs=null);
       impostopedido($_POST,$nf_issret,$dtemissao,$idnotafiscal,'ISS',$obs=null);
    }
    if(!empty($nf_inss) and ($nf_inss>0) and !empty($dtemissao) and !empty($idnotafiscal)){
       // geraparcelaimposto($_POST,$nf_inss,$dtemissao,$idnotafiscal,'INSS',$obs=null);
       impostopedido($_POST,$nf_inss,$dtemissao,$idnotafiscal,'INSS',$obs=null);
    }
    if(!empty($nf_ir) and ($nf_ir>0) and !empty($dtemissao) and !empty($idnotafiscal)){
       // geraparcelaimposto($_POST,$nf_ir,$dtemissao,$idnotafiscal,'IRRF',$obs=null);
       impostopedido($_POST,$nf_ir,$dtemissao,$idnotafiscal,'IRRF',$obs=null);
    }
    if(!empty($nf_darf) and ($nf_darf>0) and !empty($dtemissao) and !empty($idnotafiscal)){ 
       
        if($nf_csll>0 and !empty($nf_csll)){// se for $nf_csll > 0 soma $nf_pis+$nf_cofins+$nf_csll e gera somente uma parcela senão gera separado os dois
            //geraparcelaimposto($_POST,$nf_darf,$dtemissao,$idnotafiscal,'CSRF',$obs=null);  
            impostopedido($_POST,$nf_darf,$dtemissao,$idnotafiscal,'CSRF',$obs=null);  
        }elseif ( $atualizaimposto=='Y' or $geracontapagar=='N'){   

            
            if($nf_pis>0){    
                geraparcelaimposto($_POST,$nf_pis,$dtemissao,$idnotafiscal,'PIS',$obs=null,$geracontapagar);   
            }
            if($nf_cofins>0){
                geraparcelaimposto($_POST,$nf_cofins,$dtemissao,$idnotafiscal,'COFINS',$obs=null,$geracontapagar);
            }
            
        }
    }
}
//Cria a Formalização para linkar com o novo Lote Criado para ela
if(!empty($_SESSION['arrpostbuffer']['1']['i']['lote']['idobjetoprodpara']) && !empty($_SESSION['arrpostbuffer']['1']['i']['lote']['idprodservformula']))
{
    insertFormalizacao($_SESSION['arrpostbuffer']['1']['i']['lote']['idunidade'], $_SESSION["_pkid"], $fluxo, $_idempresa);
}

//ATUALIZAR VALOR DA FATURA
if(!empty($_SESSION['arrpostbuffer']['atitem']['u']['contapagaritem']['idcontapagaritem']))
{

   $idcontapagaritem = $_SESSION['arrpostbuffer']['atitem']['u']['contapagaritem']['idcontapagaritem'];

    $rowv = PedidoController::buscarFormaPagamentoPorParcela($idcontapagaritem);
    

    if($rowv['agrupado']=='Y' and $rowv['formapagamento'] !='C.CREDITO' and  ( $rowv['formapagamento']!='BOLETO' or  $rowv['agruppessoa'] !='Y')){      
           
    if(!empty($rowv['idcontapagar'])){

        $rowvalor = PedidoController::somarValorParcelasPorFatura($rowv['idcontapagar']);

        PedidoController::AtualizaValorFatura($rowv['idcontapagar'],$rowvalor['nvalor']);   
                   
               
     }
    }        
}

if(!empty($idcontapagar_remessa))
{
    if($_POST['tipo'] == 'enviarTodos')
    {
        $arryaIdcontapagar = explode(",", $idcontapagar_remessa);
        foreach($arryaIdcontapagar as $idcontapagar_remessa)
        {
            $return = inserirRemessa($idformapagamento, $idcontapagar_remessa);
        }
    } else {
        $return = inserirRemessa($idformapagamento, $idcontapagar_remessa);
    }
}

if($_POST['atualizaComissao'] == 'Y'){
    cnf::agrupaCP();
}

//recalcula os impostos se mudar o idnatop
if($_POST['alteranatop'] == 'Y'){
    $lf_idnf= $_SESSION["arrpostbuffer"]["1"]["u"]["nf"]["idnf"];
    if(!empty($lf_idnf)){
        cnf::impostoItemPedido($lf_idnf);
    }
}

$idcontapagarAlt = $_SESSION["arrpostbuffer"]["altcp"]["u"]["contapagar"]["idcontapagar"];
$idcontapagarItemAlt = $_SESSION["arrpostbuffer"]["altcpi"]["u"]["contapagaritem"]["idcontapagaritem"];
$statusContaPagar = $_POST["statuscontapagar"];
$formapagamentoAtual = $_POST["idformapagamento"];
$formapagamentoAnterior = $_POST["idformapagamentoant"];
if(!empty($formapagamentoAtual) && !empty($formapagamentoAnterior) && !empty($idcontapagarAlt) && $statusContaPagar <> "QUITADO") {
    $fpAnterior = PedidoController::buscarConfiguracoesFormaPagamento($formapagamentoAnterior);
    $fpAtual = PedidoController::buscarConfiguracoesFormaPagamento($formapagamentoAtual);

    if($fpAtual['agrupnota'] == 'Y' && $fpAnterior['agrupfpagamento'] == 'N'){
        PedidoController::atualizarContaPagarFormaPagamentoPorIdContaPagar($fpAtual['idagencia'], $formapagamentoAtual, $idcontapagarAlt);
        PedidoController::atualizarFormaPagamentoContaPagarItem($formapagamentoAtual, $idcontapagarItemAlt);
    } elseif($fpAnterior['agrupfpagamento'] == 'Y'){
        PedidoController::atualizarFormaPagamentoAgrupadoContaPagarItem($formapagamentoAtual, $idcontapagarItemAlt);
        cnf::agrupaCP();
    }
}

function inserirRemessa($idformapagamento, $idcontapagar_remessa)
{
    $remessa = PedidoController::buscarIdRemessa($idformapagamento, $idcontapagar_remessa);
    if(!empty($remessa['dados']) && $remessa['qtdLinhas'] == 1)
    {
        $arrayInserirRemessaItem = [
            "idempresa" => cnf::$idempresa,
            "idremessa" => $remessa['dados']['idremessa'],
            "idcontapagar" => $idcontapagar_remessa,        
            "usuario" => $_SESSION["SESSAO"]["USUARIO"],
            "criacao" => 'now()'
        ];
        $idfluxostatus = FluxoController::getIdFluxoStatus('contapagar', 'PENDENTE');
        $return = PedidoController::inserirRemessaItem($arrayInserirRemessaItem);
        PedidoController::AtualizarStatusContaPagar($idcontapagar_remessa, $idfluxostatus);
        FluxoController::inserirFluxoStatusHist('contapagar', $idcontapagar_remessa, $idfluxostatus, 'PENDENTE');
        return $return;
    } elseif($remessa['qtdLinhas'] > 1){
        die('Favor verificar, pois existem mais de uma remessa para esta Forma de Pagamento');
    }
}

function impostopedido($inpost,$invalor,$dtemissao,$idnotafiscal,$tipo,$obs){

    $arrnatop=PedidoController::buscarTipoNatPorIdnf($idnotafiscal);

    if($arrnatop['natoptipo']!='devolucao'){//somente venda gera imposto devolucao nao
  
        //die('imposto00');
    
        if(!empty($inpost['_1_u_nf_nnfe'])){
            $rot='NFe ('.$inpost['_1_u_nf_nnfe'].') -';
        }else{
            $rot='Compra ID ('.$idnotafiscal.') -';
        }
        
        $arrconfCP=cnf::getDadosConfContapagar($tipo);
        //print_r($arrconfCP); die;
        if(!empty($arrconfCP['idpessoa'])){
            $idpessoa = $arrconfCP['idpessoa'];
        }else{
            $idpessoa = $inpost['_1_u_nf_idpessoa'];
        } 
        if(!empty($idpessoa)){
            $nomep= traduzid("pessoa","idpessoa","nome",$idpessoa);
        }

        if(!empty($obs)){
            $prodservdescr = $rot.''.$obs;
        }else{
            $prodservdescr = $rot.''.$nomep;
        }
        
        $rowf=PedidoController::buscarNfitemPorIdobjetoTipoobjetoIdconfcontapagar($idnotafiscal,'nf',$arrconfCP['idconfcontapagar']);   
        $Vqtdnfitem=count($rowf);
        
        if($Vqtdnfitem<1){
            $arrnfitem=cnf::montaarrnfitem($_POST,$arrconfCP,$invalor,$invalor,$prodservdescr);  

            $ndtemissao = validadate($_POST["_1_u_nf_dtemissao"]);
            if(empty($arrconfCP['diavenc'])){
                $arrconfCP['diavenc']=1;
            }

            if($arrconfCP['vigente']=='Y'){
                $rowvenc=PedidoController::buscarDataVencimentoNoMes($ndtemissao,$arrconfCP['diavenc']);
            }else{
                $rowvenc=PedidoController::buscarDataVencimentoNoMesSequinte($ndtemissao,$arrconfCP['diavenc']);
            }
            
            $arrnfitem[1]['dataitem'] = $rowvenc['dataitem'];

            $inidnfitem=cnf::inseredb($arrnfitem,'nfitem'); 
            $inidnfitem = $inidnfitem[0];
            
            if($inpost['_1_u_nf_tiponf']=='R'){
                $inidnf=cnf::agrupaNfitem($inidnfitem,'R');
            }else{
                $inidnf=cnf::agrupaNfitem($inidnfitem);
            }

            if (!is_numeric($inidnf)) {
                echo($inidnf); die();
            }
            cnf::atualizavalornf($inidnf);

            cnf::gerarContapagar($inidnf);

            cnf::atualizafat($inidnf);
        
            cnf::agrupaCP(); 
        }else{      

            $arrParcelas= cnf::recuperaParcelas($rowf['idnf'],'QUITADO','nf');//Contapagar Quitado
            $qtParcelas = $arrParcelas['quant'];

            $arrconfCP=cnf::getDadosConfContapagar($tipo);

            $ndtemissao = validadate($_POST["_1_u_nf_dtemissao"]);
            if(empty($arrconfCP['diavenc'])){
                $arrconfCP['diavenc']=1;
            }

            if($tipo=='GNRE'){
                
                $rowvenc=PedidoController::buscarDataVencimentoNoMes($ndtemissao,$arrconfCP['diavenc']);
            }else{
                
                $rowvenc=PedidoController::buscarDataVencimentoNoMesSequinte($ndtemissao,$arrconfCP['diavenc']);
            }
        
            if($qtParcelas == 0){
            
                PedidoController::atualizarNfitemImposto($rowvenc['dataitem'],$prodservdescr,$invalor,$rowf['idnfitem']);   
            
                if($inpost['_1_u_nf_tiponf']=='R'){
                    $inidnf=cnf::agrupaNfitem($rowf['idnfitem'],'R');
                }else{
                    $inidnf=cnf::agrupaNfitem($rowf['idnfitem']);
                }
                
                if (!is_numeric($inidnf)) {
                    echo($inidnf); die();
                }

                cnf::atualizavalornf($rowf['idnf']);

                cnf::atualizafat($rowf['idnf']);

                if($inidnf != $rowf['idnf']){

                    cnf::atualizavalornf($inidnf);

                // cnf::gerarContapagar($inidnf);

                    cnf::atualizafat($inidnf);
                }
            
                cnf::agrupaCP(); 

            }
                
        }
    }////somente venda gera imposto devolucao nao
}

function alterafrete($modfrete,$iu,$idnotafiscal,$statusant,$tiponf){ 
     
    if($modfrete==9 and $iu == "u" and !empty($idnotafiscal) and $statusant != "CONCLUIDO"){      
        $frete='0.00';
        PedidoController:: atualizarNfitemValorFrete($frete,$idnotafiscal);	
    }

    if($iu == "u" and !empty($idnotafiscal) and $statusant != "CONCLUIDO" and $tiponf!="C"){	
        $row= PedidoController::somarFretePorIdnf($idnotafiscal);
        PedidoController:: atualizarNfValorFrete($row['sumfrete'],$idnotafiscal);		  
    }

}

function recuperaParcelas($inidobj,$instatus,$intipoobjeto){
    /*
     * verifica se existe alguma parcela quitada. se existir, nao alterar nada.
     */
    $rowverif=PedidoController::buscarQuantidadeParcelasPorStatusTipoObjeto($instatus,$intipoobjeto,$inidobj);

    return  $rowverif;
}

function recuperaParcelasItensVinc($inidobj,$intipoobjeto){
    /*
     * verifica se existe algum contaitem vinculado a conta
     */
     $rowverif =PedidoController::buscarSeExisteItemFaturaPorObj($intipoobjeto,$inidobj);

    return  $rowverif;    
}

function verificaboleto($inidnf){
   
    $rowqtdbol = PedidoController::verificarSeExisteBoletoPorIdnf($inidnf);
    
    return  $rowqtdbol;
}

function getParcelaItens($idnotafiscal){
     /*
     * verifica se existe alguma parcela item quitada. se existir, nao alterar nada.
     */
    $rowverifitem = PedidoController::verificarSeExisteParcelaQuitada($idnotafiscal);
    return  $rowverifitem;
}

function recuperaParcelasProg($idnotafiscal,$intipoobjeto){
     /*
     * verifica se existe algum contaitem vinculado a conta
     */
    $resverif = PedidoController::buscarContapagarProgramadaPorIdobjeto($idnotafiscal,$intipoobjeto);
    $qtd = count($resverif);
    if($qtd<1){
        $resverif =  PedidoController::buscarContapagaritemProgramadaPorIdobjeto($idnotafiscal,$intipoobjeto);
        $qtd = count($resverif);  
        if($qtd<1){
               
            $resverif =  PedidoController::buscarContapagaritemComissaoProgramadaPorIdobjeto($idnotafiscal,$intipoobjeto);
            $qtd = count($resverif); 
        }      
    } 
    return   $qtd;
}

function getParcelaItensfechada($idnotafiscal,$agrupnota){
     /*
     * verifica se existe alguma parcela item quitada. se existir, nao alterar nada.
     */
    if($agrupnota=='Y'){
        $instatus="('FECHADO')";
    }else{
        $instatus="('FECHADO','PENDENTE')";
    }
 
    $rowverifitem =PedidoController::verificarSeExisteParcelaInStatusPorIdobjeto($idnotafiscal,$instatus,'nf'); 
    return  $rowverifitem;
}

function deletaParcelasExistentes($idnotafiscal){
    	/*
	 * deleta as parcelas existentes.
	 */
    //comissão
 	PedidoController::deletaParcelaComissaoPendentePorIdobjeto($idnotafiscal,'nf');

    PedidoController::deletaParcelaImpostoPendentePorIdobjeto($idnotafiscal,'nf');
    // contapagaritem
    PedidoController::deletaParcelaPendentePorIdobjeto($idnotafiscal,'nf');

    
    // GVT - 23/07/2021 - @471938 remover assinatura de contas a pagar que serão apagadas
    $resCarrimbo = PedidoController::buscarFaturaSemComissaoPorIdobjeto($idnotafiscal,'nf');

    if(count($resCarrimbo) > 0){
        foreach($resCarrimbo as $rCarrimbo ){   
            //deletar carimbo pendente    
            PedidoController::deletarPorIdObjetoTipoObjetoEIdPessoa($rCarrimbo["id"],'contapagar',798);
        }
    }

    PedidoController::deletaFaturaSemComissaoPorIdobjeto($idnotafiscal,'nf');   
}

function corrigirParcelas($idnotafiscal,$total,$contapagaritem){
    
    //corrigir parcelas em um centavo
	$rows=PedidoController::somarValorFaturaPorIdobjeto($idnotafiscal,'nf');
	
	if($rows['vvalor'] != $total && ($contapagaritem != "Y"))
    {    
	    if($rows['vvalor']>$total){
            PedidoController::ajustarFaturaMenosUmCentavo($idnotafiscal,'nf',$rows["idempresa"]);
        }elseif($rows['vvalor']<$total){
            PedidoController:: ajustarFaturaMaisUmCentavo($idnotafiscal,'nf',$$rows['mparcela'],$rows["idempresa"]);
        }           
	} 
}

function deletaComissoesPendentes($idnotafiscal){
    PedidoController::deletaParcelaComissaoPendentePorIdobjeto($idnotafiscal,'nf');
}

function getComissoesPendentes($idnotafiscal){
       /*
    * verifica se existe alguma parcela quitada. se existir, nao alterar nada.
    */
   $rowverif = PedidoController::buscarSeExisteComissaoPendetePorIdobjeto($idnotafiscal,'nf');
   return $rowverif;
}

function copiarnfitem($idnfitemori,$idnf){
    
    $arrnfitem=PedidoController::ArrayNfitemVendaPorIdnfArray($idnfitemori);
    
    foreach ($arrnfitem as $arritem ) {
        $insnfItem = new Insert();
        $insnfItem->setTable("nfitem");
        foreach ($arritem as $key => $value) {
            if($key=='idnf'){
                $value = $idnf;
            }	
           
            if((!empty($value) || ($value == 0 && $value != NULL)) and $key!='idnfitem'  and $key!='manual' and $key!='alteradoem'  and $key!='alteradopor' and $key!='criadoem' and $key!='criadopor'){
                $insnfItem->$key = $value;
            }	 
        }
        $idnfitem = $insnfItem->save();	    
   // echo($idnfitem);
    }
    reset($arrnfitem);
 
}

$idnfcp = $_POST["_x_i_nf_idobjetosolipor"];
$tipoobjetosolipor = $_POST["_x_i_nf_tipoobjetosolipor"];

if(!empty($idnfcp) and !empty($_SESSION["_pkid"]) and $tipoobjetosolipor=='nf'){
    
    copiarnfitem($idnfcp,$_SESSION["_pkid"]);
  
}

if(!empty($altitem)){
     //agrupaContapagar($fluxo); 
     cnf::agrupaCP(); 
}


function geracomissao($idnf){  

    $arrNF=getObjeto("nf",$idnf,"idnf");

    //BUSCAR CONFIGURAÇÕES DA FORMA DE PAGAMENTO
    $formapagamento=PedidoController::buscarInfFormapagamentoPorId($arrNF['idformapagamento']);
   
    $rc=PedidoController::buscarComissaoPorIdpessaoIdnf($idnf);
    foreach($rc as $rwc){
       
        if($formapagamento['agrupado']=='Y'){//se for agrupado          
                                
            $ri=PedidoController::buscarParcelaPorNf($idnf,'nf');
        
        }else{//if($formapagamento['agrupado']=='Y'){
           
            $ri=PedidoController::buscarFaturaNf($idnf,'nf');
                       
        }//else($formapagamento['agrupado']=='Y'){
        
        $qtdparc=count($ri);

        $valor = $rwc['comissao']/$qtdparc;
       
        foreach($ri as $rwi){

            if(empty($rwi['proporcao'])){
                $valor = $rwc['comissao']/$qtdparc;
            }else{
                $perc = $rwi['proporcao']/100;
                $valor = $rwc['comissao']*$perc;
            }            
            comissao($rwi['idcontapagar'],$valor,$rwi['parcela'],$rwi['parcelas'],$rwi['vdatapagto'],$rwc['idpessoa']);              
        }
    }//while($rwc= mysqli_fetch_assoc($rc)){
}//function geracomissao($idnf){

function  comissao($idcontapagar,$valorn,$parcela,$parcelas,$datapagto,$idpessoa){
    cnf::$idempresa;
    $arrconfCP=cnf::getDadosConfContapagar('COMISSAO');
    $visivel='S';

    $incontc = new Insert();
    $incontc->setTable("contapagaritem");
    $incontc->idempresa=cnf::$idempresa;
    $incontc->status='ABERTO';
    $incontc->idpessoa = $idpessoa;
    $incontc->idobjetoorigem = $idcontapagar;
    $incontc->tipoobjetoorigem='contapagar';    
    $incontc->tipo='D';
    $incontc->visivel = $visivel;
    $incontc->idformapagamento = $arrconfCP['idformapagamento'];
    $incontc->parcela = $parcela;
    $incontc->parcelas = $parcelas;
    $incontc->datapagto = $datapagto;
    $incontc->valor = $valorn;
    $idcontapagaritem = $incontc->save();  

    if(empty($idcontapagaritem)){
            d::b()->query("ROLLBACK;");
            die("1-Falha ao gerar parcela do representante");
    }
}



function geraparcelaimposto($inpost,$invalor,$dtemissao,$idnotafiscal,$tipo,$obs,$geracontapagar) {  


   
  
    $idnf=$inpost['_1_u_nf_idnf'];

    
    $arrNF=getObjeto("nf",$inpost['_1_u_nf_idnf'],"idnf");

    $cra = traduzid("empresa", "idempresa", "cra",$arrNF['idempresa']);

    if($geracontapagar=='N'){
        parcelaimposto($idnf,'nf',$invalor,1,1,$dtemissao,$tipo,'PENDENTE');
    }else{

        //BUSCAR CONFIGURAÇÕES DA FORMA DE PAGAMENTO
        $formapagamento=PedidoController::buscarInfFormapagamentoPorId($arrNF['idformapagamento']);      
              
        if($formapagamento['agrupado']=='Y'){//se for agrupado          
                                
            $ri=PedidoController::buscarParcelaPorNf($idnf,'nf');
        
        }else{//if($formapagamento['agrupado']=='Y'){
        
            $ri=PedidoController::buscarFaturaNf($idnf,'nf');
                    
        }//else($formapagamento['agrupado']=='Y'){
        
        if($cra=='FC'){//FLUXO DE CAIXA    
            $qtdparc=count($ri);

            $valor = $invalor/$qtdparc;
        
            foreach($ri as $rwi){

                if(empty($rwi['proporcao'])){
                    $valor =$invalor/$qtdparc;
                }else{
                    $perc = $rwi['proporcao']/100;
                    $valor = $invalor*$perc;
                }            
                parcelaimposto($rwi['idcontapagar'],'contapagar',$valor,$rwi['parcela'],$rwi['parcelas'],$rwi['vdatapagto'],$tipo,'ABERTO');              
            }
        }else{//competência
            foreach($ri as $rwi){
                
                if(empty($dtemissao)){$dtemissao=$arrNF['dtemissao'];}

                parcelaimposto($rwi['idcontapagar'],'contapagar',$invalor,1,1,$dtemissao,$tipo,'PENDENTE');
                break; 
            }
        } 
    }
        cnf::agrupaCP();  

}//function geracomissao($idnf){

function  parcelaimposto($idobjeto,$tipoobj,$valorn,$parcela,$parcelas,$datapagto,$tipo,$status){


   
    $datapagto = date("Y-m-d", strtotime($datapagto));  

    //die($datapagto);

    cnf::$idempresa;
    $arrconfCP=cnf::getDadosConfContapagar($tipo);
    $visivel='S';

    $incontc = new Insert();
    $incontc->setTable("contapagaritem");
    $incontc->idempresa=cnf::$idempresa;
    $incontc->status=$status;
    $incontc->idpessoa =  $arrconfCP['idpessoa'];
    $incontc->idobjetoorigem = $idobjeto;
    $incontc->tipoobjetoorigem=$tipoobj;    
    $incontc->tipo='D';
    $incontc->visivel = $visivel;
    $incontc->idformapagamento = $arrconfCP['idformapagamento'];
    $incontc->parcela = $parcela;
    $incontc->parcelas = $parcelas;
    $incontc->datapagto = $datapagto;
    $incontc->valor = $valorn;
    $idcontapagaritem = $incontc->save();  

    if(empty($idcontapagaritem)){
            d::b()->query("ROLLBACK;");
            die("1-Falha ao gerar parcela de imposto");
    }
}

//Gerar a configuração das parcelas 
function geranfconfpagar($idnfparc)
{
    $usuario = $_SESSION["SESSAO"]["USUARIO"];
    $arrNF=getObjeto("nf",$idnfparc,"idnf");

    $parc = $arrNF['parcelas'];
    $dtemissao = $arrNF['dtemissao'];
    $diasentrada = $arrNF['diasentrada'];
    $intervalo = $arrNF['intervalo'];
    $_idempresa = $arrNF['idempresa'];
    $valintervalo=0;
   
  
    $difdias = 0;
    $strintervalo = 'days';
    $dtemissaoAm =$dtemissao;

        for($index = 1; $index <= $parc; $index++) 
        {
            if ($index == 1) {
                $valintervalo = $diasentrada;
                $diareceb = $diasentrada + $difdias;                
                $recebcalc = date('Y-m-d', strtotime("+$diareceb $strintervalo", strtotime($dtemissaoAm)));
                $eFeriado = 1;

                WHILE ($eFeriado >= 1) {
                    
                    $rowdia =  NFController::verificaFeriadoFds(date('Y-m-d', $recebcalc));
                                                        
                    IF($rowdia['eFeriado'] == 1) {
                        $timestemp = strtotime(date('Y-m-d', strtotime($recebcalc)) . "+1 days");
                        $recebcalc = date('Y-m-d', $timestemp);
                        $eFeriado = 1;
                    }else{
                        $eFeriado = 0;
                    }                      
                }
                
            } else {
                $valintervalo = $valintervalo + $intervalo;
                $diareceb = $valintervalo + $difdias;                                       
                $recebcalc = date('Y-m-d', strtotime("+$diareceb $strintervalo", strtotime($dtemissaoAm)));

                $eFeriado = 1;

                WHILE ($eFeriado >= 1) {
                    
                    $rowdia =  NFController::verificaFeriadoFds(date('Y-m-d', $recebcalc));
                                                        
                    IF($rowdia['eFeriado'] == 1) {
                        $timestemp = strtotime(date('Y-m-d', strtotime($recebcalc)) . "+1 days");
                        $recebcalc = date('Y-m-d', $timestemp);
                        $eFeriado = 1;
                    }else{
                        $eFeriado = 0;
                    }                      
                }
            }

            CotacaoController::inserirIdNfContaPagarDataReceb($idnfparc, $_idempresa, $usuario, $recebcalc, $index);
        }
    
}

?>