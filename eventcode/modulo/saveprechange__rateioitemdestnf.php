<?
require_once(__DIR__."/../../form/controllers/fluxo_controller.php");

$status = $_SESSION['arrpostbuffer']['x']['u']['nf']['status'];
$idnf = $_SESSION['arrpostbuffer']['x']['u']['nf']['idnf'];

if($status=="APROVADO" and !empty($idnf)){

    $rwNf = NfEntradaController::buscarNfPorIdnf($idnf);

    if(empty($rwNf['dtemissaoorig'])){die('Favor informar a data de emissão antes de gerar os itens.');}

    if(empty($rwNf['nnfe'])){die('Favor informar o número da NF.');}

    $idfluxostatus = FluxoController::getIdFluxoStatus('nfentrada', 'APROVADO');
    $_SESSION['arrpostbuffer']['x']['u']['nf']['idfluxostatus']=$idfluxostatus;

    $rowv=EmpresaController::buscarEmpresaPessoaValorNovaNF($idnf);

  

    $idtipounidade = 19;

    
    $qrUnid = "SELECT idunidade FROM unidade WHERE idtipounidade = ".$idtipounidade." AND idempresa = ".$rowv['idempresa'];
    $rsUnid = d::b()->query($qrUnid) or die("[prechangerateioitemdest][1]: Erro ao buscar idunidade. SQL: ".$qrUnid);
    $rwUnid = mysqli_fetch_assoc($rsUnid);

    $confempresa=RateioItemDestController::buscarConfEmpresaCobranca($rowv['idempresacredito'],$rowv['idempresa']);

    $idfluxostatus = FluxoController::getIdFluxoStatus('nfentrada', 'APROVADO');

   if($rwNf['geracontapagar']=='Y'){
      
               
        $insnfdebito = new Insert();
        $insnfdebito->setTable("nf");				
        $insnfdebito->idempresa=$rowv['idempresa'];
        $insnfdebito->idpessoa=$rowv['idpessoaform'];   
        $insnfdebito->idformapagamento=$confempresa['idformapagamentod'];   
        $insnfdebito->idobjetosolipor=$idnf;
        $insnfdebito->tipoobjetosolipor='nf';
        $insnfdebito->total=$rowv['valor'];
        $insnfdebito->subtotal=$rowv['valor'];                
        $insnfdebito->tiponf='S'; 
        $insnfdebito->idunidade=$rwUnid['idunidade'];
        $insnfdebito->status ='APROVADO';
        $insnfdebito->tipocontapagar ='D'; 
        $insnfdebito->nnfe = $rwNf['nnfe'];   
        $insnfdebito->dtemissao = $rwNf['dtemissaoorig'];  
        $insnfdebito->parcelas = $rwNf['parcelas']; 
        $insnfdebito->idfluxostatus = $idfluxostatus;
        $insnfdebito->tipoorc='COBRANCA';
    // $insunid->primtipounidade='Y';                        
        $idnfdebito=$insnfdebito->save();  

        if(empty($idnfdebito)){ die('savapre-rateioitemdestnf - erro ao gerar nota de débito.');}


        $qrx= "SELECT * FROM nfconfpagar WHERE idnf = ".$idnf." order by idnfconfpagar";
        $rescx = d::b()->query($qrx) or die("[prechangerateioitemdestnf][1]: Erro ao buscar confpagar. SQL: ".$qrx);
        while($rowcx = mysqli_fetch_assoc($rescx))
        {
            $insnfconfpagar = new Insert();
            $insnfconfpagar->setTable("nfconfpagar");
            $insnfconfpagar->idnf = $idnfdebito;
            if(!empty($rowcx['datareceb'])){
                $insnfconfpagar->datareceb = $rowcx['datareceb'];      
            }                 
            $insnfconfpagar->save();
        
        }


        $_SESSION['arrpostbuffer']['x']['u']['nf']['idobjetosolipor']=$idnfdebito;
        $_SESSION['arrpostbuffer']['x']['u']['nf']['tipoobjetosolipor']='nf';
    }

    $listarItens=RateioItemDestController::listarRateioitemdestnfPorIdnfAgrupadoUnidade($idnf);

   
    /*
    if($rowv['idempresacredito']==15){//MB MAQUINAS
        $idcontaitem=487;
        $idtipoprodserv=956;  
    }elseif($rowv['idempresacredito']==12){
        $idcontaitem=487;
        $idtipoprodserv=955;  
    }else{
        $idcontaitem=486;
        $idtipoprodserv=953;  
    }
    */
    $idcontaitem=$confempresa['idcontaitem'];
    $idtipoprodserv=$confempresa['idtipoprodserv'];  
    $idcontaitemd=$confempresa['idcontaitemd'];
    $idtipoprodservd=$confempresa['idtipoprodservd'];  

   // print_r( $listarItens); die();
   $total=0;
    foreach ($listarItens as $_itens){
        $total=$total+$_itens['valor'];
                            
        $insitemcred = new Insert();
        $insitemcred->setTable("nfitem");				
        $insitemcred->idempresa=$rowv['idempresacredito'];
        $insitemcred->idnf=$idnf;
        $insitemcred->qtd=1;   
        $insitemcred->qtdsol=1;
        $insitemcred->idcontaitem=$idcontaitem;
        $insitemcred->idtipoprodserv=$idtipoprodserv;                          
        $insitemcred->prodservdescr=$_itens['unidade']; 
        $insitemcred->vlritem=$_itens['valor'];
        $insitemcred->total=$_itens['valor'];
        $insitemcred->nfe = 'Y';                            
        $idnfitemcredito=$insitemcred->save();  

            
        if(empty($idnfitemcredito)){ die('savapre-rateioitemdestnf - erro ao gerar item de credito.');}
        if($rwNf['geracontapagar']=='Y'){
            $insitemdeb = new Insert();
            $insitemdeb->setTable("nfitem");				
            $insitemdeb->idempresa=$rowv['idempresa'];
            $insitemdeb->idnf=$idnfdebito;
            $insitemdeb->qtd=1;  
            $insitemdeb->qtdsol=1; 
            $insitemdeb->idcontaitem=$idcontaitemd;     
            $insitemdeb->idtipoprodserv=$idtipoprodservd;                          
            $insitemdeb->prodservdescr=$_itens['unidade']; 
            $insitemdeb->vlritem=$_itens['valor'];
            $insitemdeb->total=$_itens['valor'];
            $insitemdeb->nfe = 'Y';                      
            $idnfitemdebito=$insitemdeb->save();  
                
            if(empty($idnfitemdebito)){ die('savapre-rateioitemdestnf - erro ao gerar item de débito.');}
        }
    }
    $_SESSION['arrpostbuffer']['x']['u']['nf']['subtotal']=$total;
    $_SESSION['arrpostbuffer']['x']['u']['nf']['total']=$total;


}


//Gerar a configuração das parcelas 
$idnfparc = $_SESSION['arrpostbuffer']['parc']['u']['nf']['idnf'];
$parc = $_SESSION['arrpostbuffer']['parc']['u']['nf']['parcelas'];
if (!empty($idnfparc) && !empty($parc)) 
{
    NfEntradaController::atualizarProporcaoNfConfPagar($idnfparc);
    $nfconfpagar = NfEntradaController::buscarNfconfpagarOrdenadoPorOrdemDescrescente($idnfparc);
    $qtd = $nfconfpagar['qtdLinhas'];

    if ($qtd > $parc) 
    {
        foreach ($nfconfpagar['dados'] as $_nfconfpagarDados) 
        {
            NfEntradaController::apagarNfConfPagar($_nfconfpagarDados['idnfconfpagar']);
            $qtd = $qtd - 1;
            if ($qtd == $parc) {
                break;
            }
        }
    } elseif ($qtd < $parc) {

        for ($v = $qtd; $v < $parc; $v++) 
        {
            $insnfconfpagar = new Insert();
            $insnfconfpagar->setTable("nfconfpagar");
            $insnfconfpagar->idnf = $idnfparc;
            $idnfconfpagar = $insnfconfpagar->save();
        }
    }
} //if(!empty($idnfparc) && !empty($parc)){


?>