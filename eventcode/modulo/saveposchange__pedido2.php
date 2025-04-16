<?
require_once("../api/nf/index.php");


$idnotafiscal = $_POST["_1_u_nf_idnf"];
$qtdparcelas = $_POST["_1_u_nf_parcelas"];
$total = tratanumero($_POST["_1_u_nf_total"]);
$idobjetosolipor = $_POST["_1_i_nf_idobjetosolipor"];
$tipoobjetosolipor = $_POST["_1_i_nf_tipoobjetosolipor"];
//$formapgto = $_POST["_1_u_nf_formapgto"];/////////ATENCAO
$modfrete=$_POST["_1_u_nf_modfrete"];
$tipocontapagar=$_POST["_1_u_nf_tipocontapagar"];
//$tipointervalo = $_POST["_1_u_nf_tipointervalo"];
$tiponf = $_POST["_1_u_nf_tiponf"];
$tipoorc = $_POST["_1_u_nf_tipoorc"];
$status = $_POST["_1_u_nf_status"];
$idformapagamento = $_POST["_1_u_nf_idformapagamento"];
$statusant = $_POST["statusant"];
$idformapagamentoant=$_POST["_nf_idformapagamentoant"];
$emissao = $_POST["_1_u_nf_dtemissao"];
$dtemissao = $_POST["_1_u_nf_dtemissao"];
$geracontapagar = $_POST["_1_u_nf_geracontapagar"];
$idcontaitem = $_POST["_1_u_nf_idcontaitem"];
$comissao =$_POST['_1_u_nf_comissao'];
$idpessoa = $_POST['_1_u_nf_idpessoafat'];
$altitem=$_POST['_1_u_contapagaritem_idcontapagaritem'];
$entrega = $_POST['_1_u_nf_entrega'];
$idnfepedido = $_POST['idnfepedido'];
$idnfecotacao = $_POST['_1_u_nf_idnfe'];

if(empty($idpessoa)){
  $idpessoa = $_POST['_1_u_nf_idpessoa'];  
}

$nnfe=$_POST['_1_u_nf_nnfe'];
$gnre=$_POST['_1_u_nf_gnre'];
$gnreval=$_POST['gnreval'];

//IMPOSTO DE SERVICO
$nf_pis=tratanumero($_POST['_1_u_nf_pis']);
$nf_cofins=tratanumero($_POST['_1_u_nf_cofins']);
$nf_csll=tratanumero($_POST['_1_u_nf_csll']);

$nf_darf=$nf_pis+$nf_cofins+$nf_csll;

$nf_inss=tratanumero($_POST['_1_u_nf_inss']);
$nf_ir=tratanumero($_POST['_1_u_nf_ir']);
$nf_issret=tratanumero($_POST['_1_u_nf_issret']);

//Trecho de codigo para gerar as parcelas INICIO
$iu = $_SESSION['arrpostbuffer']['1']['u']['nf']['idnf'] ? 'u' : 'i';

//Gerar a configuração das parcelas 
if($iu=='i' and !empty($_SESSION['arrpostbuffer']['1']['i']['nf']['idpessoa'])){ 
    $insnfconfpagar[1]['idnf']=$_SESSION["_pkid"];        
    $inidnfconfpagar=cnf::inseredb($insnfconfpagar,'nfconfpagar'); 
}

$_SESSION['arrpostbuffer']['1'][$iu]['nf']['nnfe'] = $nnfe;

if(!empty($idobjetosolipor) and $tipoobjetosolipor=='nf'){
    cnf::copiarnfitem($idobjetosolipor,$_SESSION["_pkid"]);  
}

//relacionado a geracao da solicitacao de fabricacao
 $idsolfab=$_POST['_1_u_lote_idsolfab'];
 if($idsolfab=='novo'){
    
     $insf[1]['idlote']=$_SESSION["_pkid"];    
     $insf[1]['idpessoa']=$_POST['_1_i_lote_idpessoa'];
     $idsolfab=cnf::inseredb($insf,'solfab'); 
     $idsolfab= $idsolfab[0];   
    
    $sqlu="update lote set idsolfab=".$idsolfab." where idlote =".$_SESSION["_pkid"];
    d::b()->query($sqlu) or die("Erro ao atualizar  solicitação de fabricação no lote: <br>".mysqli_error(d::b())." sql=".$sqlu);
 }

 cnf::alteraFrete($modfrete,$iu,$idnotafiscal,$statusant,$tiponf);

 //LTM 16-09-2020 - 373161: Insere no campo Entrega a mesma data de entrega no CTe 
if(!empty($entrega)){
    $entregaFormat = explode('/', $entrega);
    $entregaFormat = $entregaFormat[2].'-'.$entregaFormat[1].'-'.$entregaFormat[0];
    if(!empty($idnfepedido)){
        $sqlEntrega = "UPDATE nf n JOIN nfitem ni ON n.idnf=ni.idnf SET entrega = '$entregaFormat' 
             WHERE ni.obs LIKE ('%".substr($idnfepedido,3)."%')";
    }
    
    if(!empty($idnfecotacao)){
        $sqlEntrega = "UPDATE nf n SET entrega = '$entregaFormat' WHERE n.idnfe LIKE ('%".substr($idnfecotacao,3)."%')";
    }

    d::b()->query($sqlEntrega); 
}

//SE CANCELAR A NOTA DELETA AS PARCELAS
//LTM: 05-10-2020 - 375925: Acrescentado status ABERTO e RESPONDIDO quando restaurar ou voltar o status, pois as parcelas serão criadas somente no status APROVADO
if ((!empty($idnotafiscal)) AND ($status == "CANCELADO" OR $status == "REPROVADO"  OR $status == "ABERTO" OR $status == "RESPONDIDO") ){
    
    cnf::deletaparcela($idnotafiscal);
}
//SE FOR GERA PARCELA NÃO DELETA AS PARCELAS
IF((!empty($idnotafiscal)) AND $geracontapagar=='N'){      
    cnf::deletaparcela($idnotafiscal);
}

if(!empty($idformapagamento)){
    $sqlc="select * from confcontapagar where status='ATIVO' and tipo='COMISSAO' and idformapagamento =".$idformapagamento;
    $rfc=d::b()->query($sqlc) or die("Erro ao buscar se é uma comissao: sql=".$sqlc." mysql".mysqli_error(d::b()));
    $qtdcom=mysqli_num_rows($rfc);
    
}else{
    $qtdcom=0;
}

if (!empty($idnotafiscal) 
        and !empty($idformapagamento)
        and ( $status=="ENVIAR" or $status == "ENVIADO" or $status == "APROVADO" or $status == "PREVISAO"
                or
                    ($status == "CONCLUIDO" and $statusant!="CONCLUIDO" and $statusant!="DIVERGENCIA"  and $statusant!="RECEBIDO")
                or 
                    ($status == "DIVERGENCIA" and $statusant!="CONCLUIDO" and $statusant!="DIVERGENCIA") 
                or 
                    ($status == "RECEBIDO" and $statusant!="CONCLUIDO" and $statusant!="RECEBIDO") 
                or ($idformapagamentoant!=$idformapagamento and $status == "CONCLUIDO")
              )
      
        and $tipoorc!="S" 
        and $geracontapagar=="Y" and $qtdcom < 1 ){
    
    
    //BUSCAR CONFIGURAÇÕES DA FORMA DE PAGAMENTO
    $sf="select idagencia,agruppessoa,agrupado,agrupfpagamento,agrupnota from formapagamento where idformapagamento=".$idformapagamento;
    $rf=d::b()->query($sf) or die("Erro ao buscar configuração da forma de pagamento: sql=".$sf." mysql".mysqli_error(d::b()));
    $formapagamento=mysqli_fetch_assoc($rf);
          
   
    $arrParcelas= cnf::recuperaParcelas($idnotafiscal,'QUITADO','nf');//Contapagar Quitado
    $qtParcelas =$arrParcelas['quant'];
    
    $arrParcelasFechado= cnf::recuperaParcelas($idnotafiscal,'FECHADO','nf');//Contapagar fechado
    $qtParcelasFechadas =$arrParcelasFechado['quant'];
    
    $arrParcelasIV= cnf::recuperaParcelasItensVinc($idnotafiscal,'nf');
    $qtParcelasIV =$arrParcelasIV['quant'];
    
    
    $arrlinhasbol=  cnf::verificaboleto($idnotafiscal);
    $qtdlinhasbol=$arrlinhasbol['quant'];
   //die($qtParcelas);
    $arrParcItens= cnf::getParcelaItens($idnotafiscal);
    $qtParcelasitem = $arrParcItens['quant'];
    
    $arrParcItensFechada= cnf::getParcelaItensfechada($idnotafiscal,$formapagamento['agrupnota']);
    $qtParcelasitemFechada = $arrParcItensFechada['quant'];
        
    
    //echo($arrParcelas['quant']." - ".$arrlinhasbol['quant']." - ".$qtParcelasitem ." - ".$qtParcelasIV);die;
    if ($qtParcelas == 0 and $qtdlinhasbol== 0 and $qtParcelasitem==0 and $qtParcelasIV==0 and $qtParcelasFechadas==0 and $qtParcelasitemFechada==0){
	//deleta as parcelas existentes.
        cnf::deletaParcelasExistentes($idnotafiscal);
        
        cnf::gerarContapagar($idnotafiscal);

        agrupaContapagar(); 
    }

}elseif (!empty($idnotafiscal) and ($status == "CONCLUIDO" or $status == "DIVERGENCIA" or $status=="RECEBIDO" or $status == "PREVISAO") and $geracontapagar=="N"){

	$arrParcItens= cnf::getParcelaItens($idnotafiscal);
	$qtParcelasitem = $arrParcItens['quant'];
	if($qtParcelasitem == 0){
		/*
		 * deleta as parcelas existentes.
		 */
        cnf::deletaParcelasExistentes($idnotafiscal);//maf: confirmar o nome "parcelasComissao"
	}
}


//impostos e comissao
if(!empty($nf_issret) and ($nf_issret>0) and !empty($dtemissao) and !empty($idnotafiscal)){
      
    $prodservdescr='NFe ('.$inpost['_1_u_nf_nnfe'].') - ISS Retido';
    $arrconfCP=cnf::getDadosConfContapagar('ISS');
    $arrnfitem=cnf::montaarrnfitem($_POST,$arrconfCP,$nf_issret,$nf_issret,$prodservdescr);  

    $inidnfitem=cnf::inseredb($arrnfitem,'nfitem'); 
    $inidnfitem=$inidnfitem[0];
    
    $inidnf=cnf::agrupaNfitem($inidnfitem);
    if (!is_numeric($inidnf)) {
        echo($inidnf); die();
    }
    cnf::gerarContapagar($inidnf);

    agrupaContapagar(); 
}

$idnfcp = $_POST["_x_i_nf_idobjetosolipor"];
$tipoobjetosolipor = $_POST["_x_i_nf_tipoobjetosolipor"];

if(!empty($idnfcp) and !empty($_SESSION["_pkid"]) and $tipoobjetosolipor=='nf'){
    
    cnf::copiarnfitem($idnfcp,$_SESSION["_pkid"]);
  
}

if(!empty($altitem)){
     agrupaContapagar(); 
}

?>