<?
require_once(__DIR__."/../../form/controllers/fluxo_controller.php");
require_once("../api/nf/index.php");
 

//print_r($_POST);die;
$iu = $_SESSION['arrpostbuffer']['1']['u']['nf']['idnf'] ? 'u' : 'i';

if($iu == 'i' && !empty($_SESSION['arrpostbuffer']['1']['i']['nf']['envio'])){
    $_SESSION['arrpostbuffer']['1']['i']['nf']['dtemissao'] = $_SESSION['arrpostbuffer']['1']['i']['nf']['envio'];
}

//gerar historico e atualizar valor
$gerandohistorico = $_SESSION['arrpostbuffer']['h1']['i']['modulohistorico']['idobjeto'];
if (!empty($gerandohistorico)) 
{
    $campo = $_SESSION['arrpostbuffer']['h1']['i']['modulohistorico']['campo'];
    $valor = $_SESSION['arrpostbuffer']['h1']['i']['modulohistorico']['valor'];
    $_SESSION['arrpostbuffer']['1']['u']['nf'][$campo] = $valor;

    if($campo=='envio'){

        $_vidnf=$_SESSION['arrpostbuffer']['1']['u']['nf']['idnf'];
        $_parcelas=$_SESSION['arrpostbuffer']['1']['u']['nf']['parcelas'];

        unset($_SESSION['arrpostbuffer']['1']);

        $envionfe=traduzid("nf","idnf","envionfe",$_vidnf);

        if($envionfe!='CONCLUIDA'){         
            $_SESSION['arrpostbuffer']['parc']['u']['nf']['dtemissao']=$valor.' 00:00:00';
        }        

        $_SESSION['arrpostbuffer']['parc']['u']['nf']['idnf']=$_vidnf;
        $_SESSION['arrpostbuffer']['parc']['u']['nf']['parcelas']=$_parcelas;
        $_SESSION['arrpostbuffer']['parc']['u']['nf']['envio']=$valor;
  
    }  
}


//gerar historico e atualizar valor
$gerandohistoricoEnd = $_SESSION['arrpostbuffer']['hent']['i']['modulohistorico']['idobjeto'];
if (!empty($gerandohistoricoEnd)) 
{
    $campo = $_SESSION['arrpostbuffer']['hent']['i']['modulohistorico']['campo'];
    $valor = $_SESSION['arrpostbuffer']['hent']['i']['modulohistorico']['valor'];
    $_SESSION['arrpostbuffer']['1']['u']['nf'][$campo] = $valor;
}

$_idnf = $_SESSION['arrpostbuffer']['1']['u']['nf']['idnf'];
$natop = $_SESSION['arrpostbuffer']['1']['u']['nf']['natop'];
if(!empty($_idnf) and $_GET['_modulo'] != "pedidosapp"){
    $rotulo = getStatusFluxo('nf', 'idnf', $_idnf);
    $_SESSION['arrpostbuffer']['1']['u']['nf']['status'] = $rotulo['statustipo'];  
}
$status = $_SESSION['arrpostbuffer']['1']['u']['nf']['status'];
$statusant = $_POST["statusant"];
$idnatop = $_SESSION['arrpostbuffer']['1']['u']['nf']['idnatop'];

$idnfalteratransp = $_POST['_alteratransp_u_nf_idnf'];
$idtranspalteratransp = $_POST['_alteratransp_u_nf_idtransportadora'];


//formatar data ao inserir um nova parcela
$_datapagto = $_SESSION['arrpostbuffer']['x9']['i']['contapagar']['datapagto'];
if(!empty($_datapagto)){
    $_SESSION['arrpostbuffer']['x9']['i']['contapagar']['datapagto']=dma($_datapagto);
    $_SESSION['arrpostbuffer']['x9']['i']['contapagar']['datareceb']=dma($_datapagto);
}

$_idempresa = cb::idempresa();

if($_POST['nf_dividir']=="Y"){
  
    $arrpb=$_SESSION["arrpostbuffer"];
    reset($arrpb);
    $idnfori=$_POST['idnfori'];
    $nidnf=geranf($idnfori);
    //Gerar PARTIDA para qualquer linha que realize insert na lote
    while (list($linha, $arrlinha) = each($arrpb)) {
	while (list($acao, $arracao) = each($arrlinha)) {
		if($acao=="u"){
                  /*  
                    $sqlx="select idunidade from unidade where idtipounidade=5 and idempresa=".$_SESSION["SESSAO"]["IDEMPRESA"];
                    $resx = d::b()->query($sqlx) or die("presave: Erro ao buscar unidade de produção: ".mysqli_error(d::b())."\n".$sql);
                    $rowx=mysqli_fetch_assoc($resx);
                    $idunidade=$rowx['idunidade'];
                     */
                     
			while (list($tab, $arrtab) = each($arracao)){
                            //Se for tabela de lote, gerar incondicionalmente a Partida
                            echo($arrtab["qtd"]);
                            if($tab=="nfitem" and $arrtab["qtd"]>0){
                                $idnfitemori=$arrtab["idnfitem"];
                                $row=PedidoController::buscarNfitemPorIdnfitem($idnfitemori);
                                $nwqtd=$row['qtd']-$arrtab["qtd"];
                                
                                gerarnfitem($nidnf,$idnfitemori,$arrtab["qtd"]);
                                //Enviar o campo para a pagina de submit
                               
                                $_SESSION["arrpostbuffer"][$linha][$acao]["nfitem"]["qtd"] = $nwqtd;
                              
                            }else{
                               unset($_SESSION["arrpostbuffer"][$linha]); 
                            }

			}
		}
	}
    } 
    
    //print_r($_SESSION["arrpostbuffer"]);die;
}

//retirar insert de consumo e reserva com quantidade vazia
foreach($_SESSION['arrpostbuffer'] as $k=>$v){    
   if(empty($v['i']['lotereserva']['qtd']) and !empty($v['i']['lotereserva']['idlote'])){
       unset($_SESSION['arrpostbuffer'][$k]);
   }
   if(empty($v['i']['lotecons']['qtdd']) and empty($v['i']['lotecons']['qtdc']) and !empty($v['i']['lotecons']['idlote'])){
       unset($_SESSION['arrpostbuffer'][$k]);
   }    
} 

// so vai para o status expedição se a reserva estiver no almoxarifado
if($status == 'FATURAR'){ //EXPEDICAO
     $res = PedidoController::buscarReservaNfitemPorIdnf($_SESSION['arrpostbuffer']['1']['u']['nf']['idnf']);
    $qtdres=count($res);
    if($qtdres>0){
        foreach($res as $row){          
              $resf = PedidoController::verificarLoteReservadoDisponivel($row['idlote']);
              $qtdf=count($resf);
              if($qtdf==0){
                  die('Deslocar Lote da Partida <a href="?_modulo=loteproducao&_acao=u&idlote='.$row['idlote'].'">'.$row['partida'].'/'.$row['exercicio']."</a> para a Logística.");
              }
           
        }        
    }
}
// Ao retirar uma nota do status contigencia liberar o envio novamente
if($_POST['statusant']=="CONTINGENCIA" and !empty($_SESSION['arrpostbuffer']['1']['u']['nf']['idnf']) AND $_SESSION['arrpostbuffer']['1']['u']['nf']['status']!= "CONTINGENCIA" AND !empty($_SESSION['arrpostbuffer']['1']['u']['nf']['status']) ){
    $_SESSION['arrpostbuffer']['1']['u']['nf']['envionfe'] ="PENDENTE";
}

//if($_POST['statusant']=="CONCLUIDO" and empty($_SESSION['arrpostbuffer']['restaurar']['u']['nf']['idnf']) and empty($idnfalteratransp) and empty($idtranspalteratransp)){
    //Die("O Pedido não poder ser salvo no status=".$_POST['statusant']."<br> Caso queira salva-ló procure um dos gestores para restaurar o mesmo.");
//}

// so vai para o status faturar apos consumir a reserva que esta no almoxarifado
if($status == 'FATURAR'){

    $res = PedidoController::buscarReservaNfitemPorIdnf($_SESSION['arrpostbuffer']['1']['u']['nf']['idnf']);
    $qtdres=count($res);
    if($qtdres>0){
        foreach($res as $row){   
            if(empty($row['idlotefracao'])){
                die("Não encontrado o produto reservado na Logística, Verificar estoque reservado.");
            }else{
                $inslotecons = new Insert();
                $inslotecons->setTable("lotecons");
                $inslotecons->idlote=$row['idlote'];
                $inslotecons->idlotefracao=$row['idlotefracao'];
                $inslotecons->idempresa=$_idempresa;
                if(!empty($row['idobjetoconsumoespec'])){
                    $inslotecons->idobjetoconsumoespec=$row['idobjetoconsumoespec'];
                    $inslotecons->tipoobjetoconsumoespec=$row['tipoobjetoconsumoespec'];
                }                        
                $inslotecons->qtdd=$row['qtd'];
                $inslotecons->idobjeto=$row['idnfitem'];
                $inslotecons->tipoobjeto='nfitem';      
                $_idlotecons=$inslotecons->save();
            }
           
            PedidoController::liberarLotereservaPorId($row['idlotereserva']);          
    
        }        
    }
}





// retirar filial do pedido insere novamente os impostos
$lf_idnf = $_SESSION["arrpostbuffer"]["limpafilial"]["u"]["nf"]["idnf"];
if(!empty($lf_idnf)){
    cnf::impostoItemPedido($lf_idnf);
}

// retirar filial do pedido insere novamente os impostos
$lf_idnf = $_SESSION["arrpostbuffer"]["alt"]["u"]["nf"]["idnf"];
$lf_idpessoafat = $_SESSION["arrpostbuffer"]["alt"]["u"]["nf"]["idnf"];
if(!empty($lf_idnf)){
    cnf::impostoItemPedido($lf_idnf);
}


$ns_idnf = $_SESSION["arrpostbuffer"]["nosso"]["u"]["nf"]["idnf"];
if(!empty($ns_idnf)){
 
      //if(_NFSECHOLOG)//echo "\n".date("H:i:s")." - geranumerorps:gerando nova rps";
    
            ### Tenta incrementar e recuperar o numerorps
            d::b()->query("LOCK TABLES sequence WRITE;");
            d::b()->query("update sequence set chave1 = (chave1 + 1) where sequence = 'nossonumero'");
    
            $sqlns = "SELECT chave1 FROM sequence where sequence = 'nossonumero';";
    
            $resns = d::b()->query($sqlns);
    
            if(!$resns){
                d::b()->query("UNLOCK TABLES;");
                echo "1-Falha Pesquisando Sequence [nossonumero] : " . mysqli_error(d::b()) . "<p>SQL: $sql";
                die();
            }
        
            $rowns = mysqli_fetch_array($resns);
        
            ### Caso nao retorne nenhuma linha ou retorn valor vazio
            if(empty($rowns["chave1"])){
                if(!$rowns){
                    d::b()->query("UNLOCK TABLES;");
                    echo "2-Falha Pesquisando Sequence [nossonumero] : " . mysqli_error(d::b()) . "<p>SQL: $sqlns";
                    die();
                }
            }
        
            d::b()->query("UNLOCK TABLES;");  
            
            $_SESSION["arrpostbuffer"]["nosso"]["u"]["nf"]["controle"]=$rowns["chave1"];     
}




//print_r($_SESSION['arrpostbuffer']); die;


/*
if($_POST['statusant']=="CONCLUIDO" and empty($_SESSION['arrpostbuffer']['restaurar']['u']['nf']['idnf'])){
    Die("O Pedido não poder ser salvo no status=".$_POST['statusant']."<br> Caso queira salva-ló procure um dos gestores para restaurar o mesmo.");
}*/

$_idnfajax = $_SESSION["arrpostbuffer"]["x"]["u"]["nf"]["idnf"];

$_arrtabdef = retarraytabdef('nfitem');
/*
 * INSERIR ITENS NA TELA DE PEDIDO CLICANDO EM +
 */
$arrInsProd=array();
foreach($_POST as $k=>$v) {
	if(preg_match("/_(\d*)#(.*)/", $k, $res)){
		$arrInsProd[$res[1]][$res[2]]=$v;
	}
}
if(count($arrInsProd) > 0 /*or $_POST['alteranatop'] == 'Y'*/){ 

  //echo(count($arrInsProd));
  //die();

    $idnf= $_SESSION["arrpostbuffer"]["1"]["u"]["nf"]["idnf"];
    cnf::gerarItemPedido($arrInsProd,$idnf);
}//if(!empty($arrInsProd))


if(!empty($_POST['pedido_idnatop']) and !empty($_POST['pedido_idnfitem']) ){
    
      $idnatop=$_POST['pedido_idnatop'];
      $idpessoa=$_POST['pedido_idpessoa'];
      $_idnf=$_SESSION['arrpostbuffer']['1']['u']['nf']['idnf'];
      $inidnfitem=$_POST['pedido_idnfitem'];
      $idnf= cnf::criarPedido($_idnf,$idnatop,$idpessoa,$inidnfitem);

      // volta o imposto do pedido @730044
     // cnf::impostoItemPedido($idnf); comentado para deixar impostos zerados @844596

  }elseif(!empty($_POST['pedido_idempresafat']) and !empty($_SESSION['arrpostbuffer']['1']['u']['nf']['idnf']) ){


    $_idnf=$_SESSION['arrpostbuffer']['1']['u']['nf']['idnf'];

    $idnf= cnf::criarEntrada($_idnf);

  }




//Atualizar valor e comissões ao trocar a formula
if(!empty($_SESSION['arrpostbuffer']['x']['u']['nfitem']['idnfitem']) 
and !empty($_SESSION['arrpostbuffer']['x']['u']['nfitem']['idprodservformula'])){

    $Nidnfitem=$_SESSION['arrpostbuffer']['x']['u']['nfitem']['idnfitem'];
   
    $arrNfitem=getObjeto("nfitem",$Nidnfitem);
    $idnf= $arrNfitem['idnf'];

    $arrNf=getObjeto("nf",$idnf);
    $idnatop = $arrNf["idnatop"];

    if(empty($idnatop)){
         die("Obrigatório informar natureza da informação.");
    }
    
    PedidoController::deletarNfitemComissaoPorId($Nidnfitem);
  
        //busca endereco do cliente ou fornecedor tipoendereco = sacado		
        $rowuf=PedidoController::buscarConfPedidoFatPorIdnf($idnf);
        $qtdrowsp= count($rowuf);
        $uf=$rowuf["uf"];
        $vendadireta=$rowuf["vendadireta"];
        if($qtdrowsp == 0){	
            //$aliqicms=18;
        // $uf="MG";
            die("Não foi encontrado a UF do cliente!!!");
        }


    if(!empty($uf)){
	
        $rowaliq=PedidoController::buscarAliqicmsUF($uf);

        //se não tiver IE pega produto com valor de aliquota de 18
        if($rowuf['indiedest'] == 9 and $uf !="MG" and $rowuf['tpnf']==1 and empty($rowuf['natopdev'])){ 
            $rowaliq18=PedidoController::buscarAliqicmsPorId(4);
            $aliqitem=$rowaliq18['idaliqicms'];
        }else{
            $aliqitem=$rowaliq['idaliqicms'];
        }
        
        if($uf !="MG"){            
            $strlocal='FORA';           
        }else{           
            $strlocal='DENTRO';
        }
        
        $rwc=PedidoController::buscarCfopPorNatop($idnatop,$strlocal);;
        $cfop=$rwc['cfop'];
    
    }else{
            die("Não foi possivel identificar o estado UF do cliente!!!");
    }
        
    $i=99977;

    $i=$i+1;

    $idprodserv=$arrNfitem['idprodserv'];
    $idprodservformula=$_SESSION['arrpostbuffer']['x']['u']['nfitem']['idprodservformula'];
      
      if(!empty($idprodserv) and !empty($idnf)){
    
          $qre = PedidoController:: buscarCfopPorIdprodserv($strlocal,$idprodserv,$cfop);
          $qtcfop=count($qre);
          if($qtcfop<1){
             $cfop=''; 
          }

          $row = PedidoController::buscarInfoProdserv($idprodserv);
          $qtdrows1= count($row);
          $ufemp=traduzid("empresa","idempresa","uf",$_idempresa);
  
      
          if($qtdrows1 > 0){	
            
             
              $comissionado=$row['comissionado'];
              
              $valor=$row["vlrvenda"];// seta como valor o valor do produto
              $comissaodf=$row["comissao"];// seta como comissao o valor do produto
              $idobcomissaodf=$row['idprodserv'];
              $obcomissaodf='prodserv';
            

              if($ufemp==$uf /*or $rowuf['indiedest']==1*/){
                    if($row['isentomesmauf']=='N' and  $row['cst']==20){
                        $rowaliq["aliq"]=$rowaliq['aliqicmsint'];
                    }

                    if($row['cst']!=60 and $row['cst']!=41 and $row['cst']!=00 and $row['isentomesmauf']=='Y'){// se for 60 e devolução e deve permanecer o 60
                            $row['cst']=40;
                    }
                    if($row['cst']==00){// ser for 00 cobrar integral
                        $rowaliq["aliq"]=$rowaliq['aliqicmsint'];
                    }
              }
              // nestas origems o icms e 4 % hermesp 31-08-2020
              if($row['origem']==1 or $row['origem']==2 or $row['origem']==3){
                  $rowaliq["aliq"]=4;
              }

              if(!empty($idprodservformula)){
                  
                  $rowf=PedidoController::buscarValorVendaFormula($idprodservformula);
                  $qtdformula=count($rowf);

                  $valor=$rowf["vlrvenda"];//troca pelo valor e comissao da formula
                  $comissaodf=$rowf["comissao"];
                  $idobcomissaodf=$idprodservformula;
                  $obcomissaodf='prodservformula';
              }

             
            if(!empty($idprodservformula)){               

                $rowvlr=PedidoController::buscarValorContatoProdutoFomulado($rowuf['idpessoa'],$row['idprodserv'],$idprodservformula);
                $temcontrato=count($rowvlr); 

            }else{                 
                        
                $rowvlr=PedidoController::buscarDescontoContratoPorProduto($rowuf['idpessoa'],$row['idprodserv']); 
                $temcontrato=count($rowvlr); 
            }
            
           
              //se tiver contrato pega valor do contrato
            if($temcontrato>0){                     
                $valor=$rowvlr['valor'];
            }

             $idtipoprodserv = traduzid('prodserv', 'idprodserv', 'idtipoprodserv', $idprodserv);
             
              // montar o item para insert

             if(empty($valor)){$valor=0.00;}
             //seta o valor do produto
             $_SESSION['arrpostbuffer']['x']['u']['nfitem']['vlrliq']=$valor;
                    
              if($vendadireta=='N' and  $comissionado =='Y'){
                         

                $rescont=PedidoController::buscarComissaoContatoProduto($idprodserv,$rowuf['idpessoa']);
                  $qtdcom=count($rescont);
                  if($qtdcom>0){
                    foreach($rescont as $rcom ){
                        
                          $infitemc = new Insert();
                          $infitemc->setTable("nfitemcomissao");
                          $infitemc->idnfitem=$Nidnfitem; 
                          $infitemc->idpessoa=$rcom['idpessoa']; 
                          $infitemc->pcomissao=$rcom['comissao']; 
                          $infitemc->idempresa=$_idempresa;
                          $infitemc->idobjeto=$rcom['idcontrato']; 
                          $infitemc->tipoobjeto='contrato'; 
                          $idnfitemc=$infitemc->save();
                      }
                  }elseif($comissaodf>0){
                         
                        $resc=PedidoController::buscarResponsavelComissao($rowuf['idpessoa']);

                        foreach($resc as $rowc ){
                          $infitemc = new Insert();
                          $infitemc->setTable("nfitemcomissao");
                          $infitemc->idnfitem=$Nidnfitem; 
                          $infitemc->idpessoa=$rowc['idcontato']; 
                          $infitemc->pcomissao= $comissaodf; 
                          $infitemc->idempresa=$_idempresa;
                          $infitemc->idobjeto= $idobcomissaodf; 
                          $infitemc->tipoobjeto= $obcomissaodf; 
                          $idnfitemc=$infitemc->save();

                      }

                      //comissao do gestor
						$rosp=PedidoController::buscarPlantelPessoa($rowuf['idpessoa']);
						if(!empty($rosp['idplantel'])){

							
							$resg=PedidoController::buscarDivisaoPlantel($idprodserv,$rosp['idplantel']);
                            foreach($resg as $rowg){
								$infitemc = new Insert();
								$infitemc->setTable("notafiscalitenscomissao");
								$infitemc->idnotafiscalitens=$Nidnfitem; 
								$infitemc->idpessoa=$rowg['idpessoa']; 
								$infitemc->idempresa=$_idempresa;
								$infitemc->pcomissao= $rowg['comissaogest']; 
								$infitemc->idobjeto= $rowg['iddivisao']; 
								$infitemc->tipoobjeto= 'divisao'; 
								$idnfitemc=$infitemc->save();

							}
						}

                         

                  }else{// ligação funcionario cliente

                    $resc=PedidoController::buscarResponavelClienteComissaoProd($rowuf['idpessoa']);
                    foreach($resc as $rowc){
                        $infitemc = new Insert();
                        $infitemc->setTable("nfitemcomissao");
                        $infitemc->idnfitem=$Nidnfitem; 
                        $infitemc->idpessoa=$rowc['idcontato']; 
                        $infitemc->idempresa=$_idempresa;
                        $infitemc->pcomissao= $rowc['participacaoprod']; 
                        $infitemc->idobjeto= $rowc['idpessoacontato']; 
                        $infitemc->tipoobjeto= 'pessoacontato'; 
                        $idnfitemc=$infitemc->save();

                    }

                    //comissao do gestor
		              $rosp=PedidoController::buscarPlantelPessoa($rowuf['idpessoa']);
						if(!empty($rosp['idplantel'])){

							
                            $resg=PedidoController::buscarDivisaoPlantel($idprodserv,$rosp['idplantel']);
                            foreach($resg as $rowg){
								$infitemc = new Insert();
								$infitemc->setTable("notafiscalitenscomissao");
								$infitemc->idnotafiscalitens=$Nidnfitem; 
								$infitemc->idpessoa=$rowg['idpessoa']; 
								$infitemc->idempresa=$_idempresa;
								$infitemc->pcomissao= $rowg['comissaogest']; 
								$infitemc->idobjeto= $rowg['iddivisao']; 
								$infitemc->tipoobjeto= 'divisao'; 
								$idnfitemc=$infitemc->save();

							}
						}


                }

              }
               
          }else{
              die("Não encontrada configuração do produto!!!!");
          }
      }
}


if(!empty($_idnfajax) and empty($_idnf)){
    $_idnf=$_idnfajax;
    $iu="u";
}



if($iu=="u" and !empty($_idnf) and $_POST['collapse']=='Y' ){
    $_tipo='collapse in';
    PedidoController::atualizaCollapseNfitem($_tipo,$_idnf);
}elseif($iu=="u" and !empty($_idnf) and $_POST['collapse']=='N' ){
    $_tipo='collapse';
    PedidoController::atualizaCollapseNfitem($_tipo,$_idnf);
}

if($iu=="i" and !empty($_SESSION['arrpostbuffer']['1']['i']['nf']['idpessoa'])){
    $_SESSION['arrpostbuffer']['1']['i']['nf']['idpessoafat']=$_SESSION['arrpostbuffer']['1']['i']['nf']['idpessoa'];
    $_SESSION['arrpostbuffer']['1']['i']['nf']['idenderecofat']=$_SESSION['arrpostbuffer']['1']['i']['nf']['idendereco'];
}

$_idobjetoprodpara = $_SESSION['arrpostbuffer']['1']['i']['lote']['idobjetoprodpara'];
$_tipoobjetoprodpara = $_SESSION['arrpostbuffer']['1']['i']['lote']['tipoobjetoprodpara'];

if(!empty($_tipoobjetoprodpara) and !empty($_idobjetoprodpara) ){
    
    //$rowx=PedidoController::buscarUnidadePorIdtipoIdempresa(5,$_idempresa);
   // $idunidade=$rowx['idunidade'];

    $idunidade = getUnidadePadraoModulo('solfab',$_idempresa);  

    $idunidadeProd = getUnidadePadraoModulo('formalizacao',$_idempresa);  
    
    if(empty($idunidadeProd)){
        die('Unidade não configurada no modulo de produção!!!');
    }

    $_SESSION['arrpostbuffer']['1']['i']['lote']['idunidade']=$idunidadeProd;
    $_SESSION['arrpostbuffer']['1']['i']['lote']['exercicio']= date("Y");
    if($_SESSION['arrpostbuffer']['1']['i']['lote']['idsolfab']=='novo'){
        $_SESSION['arrpostbuffer']['1']['i']['lote']['idsolfab']=null;
        $_POST['_1_u_lote_idsolfab']='novo';
        $_SESSION['arrpostbuffer']['1']['i']['lote']['status']= 'AGUARDANDO';
    }else{
        $_SESSION['arrpostbuffer']['1']['i']['lote']['status']= 'FORMALIZACAO';
    }
    include_once("saveprechange__loteproducao.php");
}



// retirar items não consumidos e esgotados
if(!empty($_idnf) and $_idnf!="undefined"){
     $res=PedidoController::buscarLotesNaoConsumidosPedido($_idnf);
     foreach($res as $row){        
        PedidoController::deletaLoteconsPorId($row['idlotecons']);
    }
}

if((!empty($idnfalteratransp)) and (!empty($idtranspalteratransp))){
    $_rownome=traduzid('pessoa','idpessoa','nome',$idtranspalteratransp);

    $correcao = "Alterada transportadora para ".$_rownome.". ";
    PedidoController:: atualizaInfCorrecaoNF($correcao,$idnfalteratransp);
}



$_idnfitem=$_SESSION['arrpostbuffer']['x']['d']['nfitem']['idnfitem'];

if(!empty($_idnfitem)){
    PedidoController::deletarNfitemComissao($_idnfitem);
}

//Copiar o pedido;
if(!empty($_SESSION['arrpostbuffer']['x']['i']['nf']['idobjetosolipor']) 
    and $_SESSION['arrpostbuffer']['x']['i']['nf']['tipoobjetosolipor']=='nf'
    and $_SESSION['arrpostbuffer']['x']['i']['nf']['tiponf']!='T'
    ){

    $idnfcp= $_SESSION['arrpostbuffer']['x']['i']['nf']['idobjetosolipor'];
   
    $row=PedidoController::buscarNfPorId($idnfcp);

    switch($row['tiponf']) {
        case 'V':
            $modulo = 'pedido';
        break;
        case 'R':
            $modulo = 'comprasrh';
            $idtipounidade = 14;
        break;
        case 'F':
        case 'T':
            $modulo = 'nfcte';
            $idtipounidade = '21,3';
        break;
        case 'D':
            $modulo = 'comprassocio';
            $idtipounidade = 22;
        break;
        default:
            $modulo = 'nfentrada';
            $idtipounidade = 19;
    }

    if($row["tiponf"] != 'V'){
        $rwUnid = PedidoController::buscarUnidadePorIdtipoIdempresa($idtipounidade,$_idempresa);;
    }

    //LTM - 05-04-2021: Retorna o Idfluxo nf para Pedido (Tipo V)
    $idfluxostatus = FluxoController::getIdFluxoStatus($modulo, 'INICIO');
    
    $_SESSION['arrpostbuffer']['x']['i']['nf']['idpessoa']=$row['idpessoa'];
    
    $_SESSION['arrpostbuffer']['x']['i']['nf']['idpessoafat']=$row['idpessoafat'];
    $_SESSION['arrpostbuffer']['x']['i']['nf']['idcontato']=$row['idcontato'];
    $_SESSION['arrpostbuffer']['x']['i']['nf']['idendereco']=$row['idendereco'];
    $_SESSION['arrpostbuffer']['x']['i']['nf']['idenderecofat']=$row['idenderecofat'];
    $_SESSION['arrpostbuffer']['x']['i']['nf']['idtransportadora']=$row['idtransportadora']; 
    $_SESSION['arrpostbuffer']['x']['i']['nf']['data']=dma($row['data']);
    $_SESSION['arrpostbuffer']['x']['i']['nf']['status'] = 'INICIO';
    $_SESSION['arrpostbuffer']['x']['i']['nf']['idfluxostatus'] = $idfluxostatus;
    $_SESSION['arrpostbuffer']['x']['i']['nf']['diasentrada']=$row['diasentrada'];
    $_SESSION['arrpostbuffer']['x']['i']['nf']['validade']=$row['validade'];
    $_SESSION['arrpostbuffer']['x']['i']['nf']['frete']=$row['frete'];
    $_SESSION['arrpostbuffer']['x']['i']['nf']['modfrete']=$row['modfrete'];
    $_SESSION['arrpostbuffer']['x']['i']['nf']['inffrete']=$row['inffrete'];
    $_SESSION['arrpostbuffer']['x']['i']['nf']['geracontapagar']='N';
    $_SESSION['arrpostbuffer']['x']['i']['nf']['comissao']=$row['comissao'];
    $_SESSION['arrpostbuffer']['x']['i']['nf']['formapgto']=$row['formapgto'];
    $_SESSION['arrpostbuffer']['x']['i']['nf']['idformapagamento']=$row['idformapagamento'];
    $_SESSION['arrpostbuffer']['x']['i']['nf']['idagencia']=$row['idagencia'];
    $_SESSION['arrpostbuffer']['x']['i']['nf']['parcelas']=$row['parcelas'];
    $_SESSION['arrpostbuffer']['x']['i']['nf']['tipointervalo']=$row['tipointervalo'];
    $_SESSION['arrpostbuffer']['x']['i']['nf']['intervalo']=$row['intervalo'];
    $_SESSION['arrpostbuffer']['x']['i']['nf']['desconto']=$row['desconto'];
    $_SESSION['arrpostbuffer']['x']['i']['nf']['subtotal']=$row['subtotal'];
    $_SESSION['arrpostbuffer']['x']['i']['nf']['total']=$row['total'];
    $_SESSION['arrpostbuffer']['x']['i']['nf']['tiponf']=$row['tiponf'];
    if($row["tiponf"] != 'V'){
        $_SESSION['arrpostbuffer']['x']['i']['nf']['idunidade']=$rwUnid['idunidade'];
    }
    $_SESSION['arrpostbuffer']['x']['i']['nf']['obs']=$row['obs'];
    $_SESSION['arrpostbuffer']['x']['i']['nf']['infcpl']=$row['infcpl'];
    $_SESSION['arrpostbuffer']['x']['i']['nf']['obspartilha']=$row['obspartilha'];
    $_SESSION['arrpostbuffer']['x']['i']['nf']['obsint']=$row['obsint'];
    $_SESSION['arrpostbuffer']['x']['i']['nf']['obsinterna']=$row['obsinterna'];
    $_SESSION['arrpostbuffer']['x']['i']['nf']['obsenvio']=$row['obsenvio'];
    $_SESSION['arrpostbuffer']['x']['i']['nf']['idnatop']=$row['idnatop'];
    $_SESSION['arrpostbuffer']['x']['i']['nf']['qvol']=$row['qvol'];
    $_SESSION['arrpostbuffer']['x']['i']['nf']['esp']=$row['esp'];
    $_SESSION['arrpostbuffer']['x']['i']['nf']['marca']=$row['marca'];
    $_SESSION['arrpostbuffer']['x']['i']['nf']['nvol']=$row['nvol'];
    $_SESSION['arrpostbuffer']['x']['i']['nf']['pesol']=$row['pesol'];
    $_SESSION['arrpostbuffer']['x']['i']['nf']['pesob']=$row['pesob'];
    $_SESSION['arrpostbuffer']['x']['i']['nf']['peso']=$row['peso'];
    $_SESSION['arrpostbuffer']['x']['i']['nf']['custoenvio']=$row['custoenvio'];
    $_SESSION['arrpostbuffer']['x']['i']['nf']['idendrotulo']=$row['idendrotulo'];

    if(!empty($row["idempresafat"] )){
        $_SESSION['arrpostbuffer']['x']['i']['nf']['idobjetosolipor'] ='';
        $_SESSION['arrpostbuffer']['x']['i']['nf']['tipoobjetosolipor'] ='';
    }

}elseif(!empty($_SESSION['arrpostbuffer']['x']['i']['nf']['idobjetosolipor']) 
        and $_SESSION['arrpostbuffer']['x']['i']['nf']['tipoobjetosolipor']=='nf'
        and $_SESSION['arrpostbuffer']['x']['i']['nf']['tiponf']=='T'
        ){// gerar CTe vinculado
    
    cnf::$idempresa=$_idempresa;
    $modulo = 'nfcte';
    $idtipounidade = '21,3';

    $_idobjetosolipor = $_SESSION['arrpostbuffer']['x']['i']['nf']['idobjetosolipor'];

    $idempresafat = $_SESSION['arrpostbuffer']['x']['i']['nf']['idempresafat'];

    $rwUnid = PedidoController::buscarUnidadePorIdtipoIdempresa($idtipounidade,$_idempresa);

    $row = PedidoController::buscarInfCteNF($_idobjetosolipor);

    if($row['frete'] < 1){
        $row['frete']=$row['total']*0.02;
    }

    if($row['tiponf']=='V'){
        $arrconfCP=cnf::getDadosConfContapagar('CTE-ENVIO');
    }else{
        $arrconfCP=cnf::getDadosConfContapagar('CTE-SUPRIMENTOS');
    } 

    $idfluxostatus = FluxoController::getIdFluxoStatus($modulo, 'PREVISAO');

    $_SESSION['arrpostbuffer']['x']['i']['nf']['status'] = 'PREVISAO';

    $insnf = new Insert();
    $insnf->setTable("nf");	
    
    $insnf->status = 'PREVISAO';  
    $insnf->idunidade = $rwUnid['idunidade'];
    if(!empty($idempresafat)){
        $insnf->idempresafat = $idempresafat;
    }
    $insnf->idfluxostatus = $idfluxostatus;
    $insnf->idempresa = $_idempresa;
    $insnf->idformapagamento = $arrconfCP['idformapagamento'];
    $insnf->tiponf = 'T';
    $insnf->idobjetosolipor = $_idobjetosolipor;
    $insnf->tipoobjetosolipor = 'nf';
    $insnf->subtotal = $row['frete'];
    $insnf->total = $row['frete'];
    $insnf->parcelas = 1;
    $insnf->dtemissao = $row['dtemissao'];
    $insnf->idpessoa = $_SESSION['arrpostbuffer']['x']['i']['nf']['idpessoa'];
    
    //print_r($insnf); die;
    $idnf = $insnf->save();
    //LTM - 05-04-2021: Insere o fluxo
    FluxoController::inserirFluxoStatusHist('nfentrada', $idnf, $idfluxostatus, 'PENDENTE');

    unset($_SESSION["arrpostbuffer"]["x"]);

    $_SESSION['arrpostbuffer']['x']['i']['nfitem']['idnf'] = $idnf;
    $_SESSION['arrpostbuffer']['x']['i']['nfitem']['qtd'] = 1;
    $_SESSION['arrpostbuffer']['x']['i']['nfitem']['tipoobjetoitem'] ='nf';
    $_SESSION['arrpostbuffer']['x']['i']['nfitem']['idobjetoitem'] = $_idobjetosolipor;
    $_SESSION['arrpostbuffer']['x']['i']['nfitem']['idpessoa'] = $row['idpessoa'];
    $_SESSION['arrpostbuffer']['x']['i']['nfitem']['idempresa'] = $_idempresa;
    $_SESSION['arrpostbuffer']['x']['i']['nfitem']['vlritem'] = $row['frete'];
    $_SESSION['arrpostbuffer']['x']['i']['nfitem']['total'] = $row['frete'];
    $_SESSION['arrpostbuffer']['x']['i']['nfitem']['obs'] = $row['idnfe'];
    $_SESSION['arrpostbuffer']['x']['i']['nfitem']['nfe'] = 'Y';
    $_SESSION['arrpostbuffer']['x']['i']['nfitem']['prodservdescr'] = $row['nome'];
    $_SESSION['arrpostbuffer']['x']['i']['nfitem']['idcontaitem'] = $arrconfCP['idcontaitem'];
    $_SESSION['arrpostbuffer']['x']['i']['nfitem']['idtipoprodserv'] = $arrconfCP['idtipoprodserv'];
    $_SESSION['arrpostbuffer']['x']['i']['nfitem']['tiponf'] = 'T';

    $arrinsnfcp[1]['idnf'] = $idnf;	
    $arrinsnfcp[1]['parcela'] = 1;
    $arrinsnfcp[1]['idformapagamento'] = $arrconfCP['idformapagamento'];
    $arrinsnfcp[1]['proporcao'] = 100;
    $arrinsnfcp[1]['datareceb'] = $row['dtemissao'];    

    $idnfconfpagar=cnf::inseredb($arrinsnfcp,'nfconfpagar');

    cnf::atualizafat($idnf,$arrconfCP['idformapagamento']);

    $insob = new Insert();
    $insob->setTable("objetovinculo");
    $insob->idobjeto =$_idobjetosolipor ;
    $insob->tipoobjeto = 'nf';
    $insob->idobjetovinc =  $idnf;
    $insob->tipoobjetovinc = 'cte';
    $idob = $insob->save();


}// gerar CTe vinculado

//Gerar a configuração das parcelas 
$idnfparc=$_SESSION['arrpostbuffer']['parc']['u']['nf']['idnf'];
$parc= $_SESSION['arrpostbuffer']['parc']['u']['nf']['parcelas'];
$pedido=PedidoController::buscarNfPorId($idnfparc);
if(
    !empty($idnfparc) and !empty($parc) and $idnfparc != "undefined"
    and $parc != $pedido['parcelas']
){
  
    PedidoController::atualizarProporcaoNfConfPagar($idnfparc);
       
  
    $res=PedidoController::buscarIdNfConfPagar($idnfparc);
    $qtd=count($res);
    if($qtd > $parc){
        foreach($res as $row){
            
            PedidoController::apagarNfConfPagar($row['idnfconfpagar']);
            $qtd=$qtd-1;
            if($qtd==$parc){
                break; 
            }
        }
    }elseif($qtd < $parc){
        $qtdAtualParcelas = count(PedidoController::buscarConfpagarPorNF($_1_u_nf_idnf));

        for($v = $qtd; $v < $parc; $v++) {
            $insnfconfpagar = new Insert();
            $insnfconfpagar->setTable("nfconfpagar");
            $insnfconfpagar->idnf=$idnfparc;    
            $idnfconfpagar=$insnfconfpagar->save();
        }
         
    }
    
}//if(!empty($idnfparc) and !empty($parc)){

//Gerar a configuração das parcelas 
$idnfati=$_SESSION['arrpostbuffer']['ati']['u']['nf']['idnf'];
$intervalo= $_SESSION['arrpostbuffer']['ati']['u']['nf']['intervalo'];
if(!empty($idnfati) and !empty($intervalo) and is_numeric($idnfati)){
    //PedidoController::atualizarDatarecebNfConfPagar($idnfati);
}

//Gerar a configuração das parcelas 
$idnfati=$_SESSION['arrpostbuffer']['ati']['u']['nf']['idnf'];
$diasentrada= $_SESSION['arrpostbuffer']['ati']['u']['nf']['diasentrada'];
if(!empty($idnfati) and !empty($diasentrada) and is_numeric($idnfati) ){
    
    //PedidoController::atualizarDatarecebNfConfPagar($idnfati);
}

function geranf($_idnf){
   
    $arrnf = PedidoController::buscarNfPorId($_idnf);
 	
    //LTM - 05-04-2021: Retorna o Idfluxo nf
    $idfluxostatus = FluxoController::getIdFluxoStatus('pedido', 'INICIO');

    //INSERIR A NF
    $insnf = new Insert();
    $insnf->setTable("nf");	
    foreach ($arrnf as $key => $value) {	    
       // echo "{$key} => {$value} ";
        if($key=='parcelas'){
            $parcelas= $value;
        }
        if($key=='dtemissao'){		
            $value='';
        }
        if($key=='idobjetosolipor'){		
            $value=$_idnf;
        }
        if($key=='tipoobjetosolipor'){		
            $value='nf';
        }
            
        if($key=='geracontapagar'){
           $geracontapagar= $value;
        }
        if($key=='parcelas'){
          $parcelas= $value;
        }
        if($key=='status'){
            $value="INICIO";
        }

        if($key == 'idfluxostatus'){
            $value = $idfluxostatus;
        }
      
        if(!empty($value) and $key!='idnf' and $key!='nnfe' and $key!='alteradoem'  and $key!='alteradopor' and $key!='criadoem' and $key!='criadopor'
                and $key!='cnf' and $key!='idnfe' and $key!='cdv' and $key!='envionfe'  and $key!='recibo' and $key!='protocolonfe'  and $key!='xml' and $key!='xmlret'){
            $insnf->$key=$value;
        }	    
    }
    
    //print_r($insnf); die;
    $idnf=$insnf->save();

    //LTM - 05-04-2021: Insere o fluxo
    FluxoController::inserirFluxoStatusHist('pedido', $idnf, $idfluxostatus, 'PENDENTE');

    if($geracontapagar=="Y"){
            gera_confparc($parcelas,$idnf);
    }
     
   return $idnf;
	
}//copianf($_idnf){

function gerarnfitem($idnf,$idnfitemori,$qtd){
    
        $arrnfitem=PedidoController::ArrayNfitemPorIdnfitemArray($idnfitemori);
		
        foreach ($arrnfitem as $arritem ) {
            $insnfItem = new Insert();
            $insnfItem->setTable("nfitem");
            foreach ($arritem as $key => $value) {
                if($key=='idnf'){
                    $value=$idnf;
                }	
                if($key=='qtd'){
                    $value=$qtd;
                }
                if(!empty($value) and $key!='idnfitem'  and $key!='manual' and $key!='alteradoem'  and $key!='alteradopor' and $key!='criadoem' and $key!='criadopor'){
                    $insnfItem->$key=$value;
                }	 
            }
            $idnfitem=$insnfItem->save();	    
	   // echo($idnfitem);
        }
        reset($arrnfitem);
	 
        // ************************************************************************************************ //
    
}


function gera_confparc($parc,$idnfparc){
    
     for($v = 0; $v < $parc; $v++) {
            $insnfconfpagar = new Insert();
            $insnfconfpagar->setTable("nfconfpagar");
            $insnfconfpagar->idnf=$idnfparc;    
            $idnfconfpagar=$insnfconfpagar->save();
        }
}

//TRANSFERIR A NOTA FISCAL PARA COMPRA
$idpessoatransf=$_SESSION['arrpostbuffer']['dev']['u']['nf']['idpessoatransf'];
$idnftransf=$_SESSION['arrpostbuffer']['dev']['u']['nf']['idnf'];

if(!empty($idnftransf) and !empty($idpessoatransf)){
    
    $idfinalidadeprodserv=$_SESSION['arrpostbuffer']['dev']['u']['nf']['idfinalidadeprodserv'];
    $idempresatransf=$_SESSION['arrpostbuffer']['dev']['u']['nf']['idempresatransf'];

    //if($idempresatransf==$_idempresa){@880664 - ITENS SEM LOTE - NAO CONSIGO CONCLUIR LANÇAMENTO NOTAS FISCAIS
        $devolucao="Y";
    //}else{
      //  $devolucao="N";
    //}
    
    $arrnf=PedidoController::ArrayNfPorId($idnftransf);
    
    $idfluxostatus = FluxoController::getIdFluxoStatus('nfentrada', 'INICIO');

    $lin=0;
    foreach ($arrnf as $_idnf => $arrvalue) {
        $lin=$lin+1;
      
        PedidoController::atualizaNftransferencia($_idnf,'CONCLUIDO');
        
        $insnf = new Insert();
        $insnf->setTable("nf");	
        foreach ($arrvalue as $key => $value) {	   
            // echo "{$key} => {$value} ";            
            if($key=='status'){
                $value="APROVADO";
            }
            if($key=='tiponf'){
                $value="C";
            }
            if($key=='idunidade'){
               
                $rwUnid = PedidoController::buscarUnidadePorIdtipoIdempresa(19,$arrnf[$_idnf]['idempresa']);         

                $value=$rwUnid['idunidade'];
            }
            if($key=='idfinalidadeprodserv'){
                $value= $idfinalidadeprodserv;
            }
            if($key=='idpessoa'){
                $value= $idpessoatransf;
            }
            if($key=='idfluxostatus'){
                $value=$idfluxostatus ;
            }
            if($key=='status'){
                $value='INICIO';
            }
          /*  if($key=='idempresa'){
                $value= $idempresatransf;
            }*/
	      
            if(!empty($value) and $key!='nnfe' and $key!='idnf' and $key!='alteradoem' and $key!='criadoem' and $key!='idformapagamento'  and $key!='idtransportadora' 
                    and $key!='idpessoafat' and $key!='parcelas' and $key!='dtemissao' and $key!='idendereco' and $key!='idenderecofat' and $key!='idpessoatransf' and $key!='idempresatransf'  and $key!='statustransf'                    
                    and $key!='cnf' and $key!='alteradopor' and $key!='alteradoem' and $key!='criadoem'  and $key!='criadopor' and $key!='idnfe' and $key!='cdv' and $key!='xml' and $key!='xmlret' and $key!='envionfe' and $key!='recibo' and $key!='protocolonfe' ){
                $insnf->$key=$value;
            }
        }
        $insnf->idobjetosolipor=$_idnf;
        $insnf->tipoobjetosolipor='nf';
        //print_r($insnf); die;
        $idnf_novo=$insnf->save();
        reset($arrnf);
        
        
    
        $arrnfitem=PedidoController::buscarNfitemComConsumo($_idnf);

        foreach ($arrnfitem as $arritem  ) {
            $insnfItem = new Insert();
            $insnfItem->setTable("nfitem");
            foreach ($arritem as $key => $value) {		
                if($key=='idnf'){
                    $value=$idnf_novo;
                }
                if($key=='tiponf'){
                    $value="C";
                }
               /* if($key=='idempresa'){
                    $value= $idempresatransf;
                }*/
                
                if($key=='idnfitem'){
                    $idnfitemold=$value;
                }

                if( $devolucao=="Y"){
                    if(!empty($value) and $key!='idlotecons' and $key!='idnfitem' and $key!='alteradopor' and $key!='alteradoem' and $key!='criadoem'  and $key!='criadopor' and $key!='idcontaitem' ){
                        $insnfItem->$key=$value;
                    }
                }else{
                    if(!empty($value) and $key!='idlotecons' and $key!='idnfitem' and $key!='alteradopor' and $key!='alteradoem' and $key!='criadoem'  and $key!='criadopor' and $key!='idprodserv' and $key!='idcontaitem' and $key!='idtipoprodserv'){
                        $insnfItem->$key=$value;
                    }
                }
            
            }
            $idnfitem=$insnfItem->save();
   
            if($devolucao=="Y"){
              
                $arrcons=PedidoController:: buscarConsumoLotecons($idnfitemold,'nfitem');
        
                foreach ($arrcons as $arritemc ) {
                    $inscon = new Insert();
                    $inscon->setTable("lotecons");
                    foreach ($arritemc as $key => $value) {		
                        if($key=='idobjeto'){
                            $value=$idnfitem;
                        }
                    
                        if($key=='qtdd'){
                            $inscon->qtdc=$value;
                        }

                    
                        if(!empty($value) and $key!='idlotecons' and $key!='qtdsol' and $key!='qtdc'  and $key!='qtdd' and $key!='alteradopor' and $key!='alteradoem' and $key!='criadoem'  and $key!='criadopor' and $key!='idcontaitem' ){
                            $inscon->$key=$value;
                        }
                    
                
                    }
                    $_idlotecons=$inscon->save();	    
                }
                unset($arrcons);
            }//if($devolucao=="Y"){

        }
        reset($arrnfitem);          
    }
   // echo($lin." -Pedido(s) transferido(s)");
}

//gerar um lote 
$_idprodserv = $_SESSION['arrpostbuffer']['x']['i']['lote']['idprodserv'];

if(!empty($_idprodserv)){
    
    $idnfitem =$_SESSION['arrpostbuffer']['x']['i']['lote']['idnfitem'];
    $imobilizado = traduzid('prodserv', 'idprodserv', 'imobilizado', $_idprodserv);
    $arrObj=getObjeto("nfitem",  $idnfitem,"idnfitem");
    $idprodservforn =$arrObj['idprodservforn'];
    $nitotal =$arrObj['total'];
    if(!empty($arrObj['valipi'])){
        $nitotal = $nitotal + $arrObj['valipi'];
    }
       

    if($imobilizado != 'Y'){   
        if(!empty($idnfitem)){

            if(empty($idprodservforn) and !empty($arrObj['idprodserv'])){
                $rowx =PedidoController::buscarProdservfornPorIdprodservIdnf($arrObj['idprodserv'],$arrObj['idnf']);
                $idprodservforn =  $rowx['idprodservforn'];
            }

            if(!empty($idprodservforn)){
                $_SESSION['arrpostbuffer']['x']['i']['lote']['idprodservforn']=$idprodservforn;
            
                $rowy=PedidoController::buscarProdservfornPorId($idprodservforn); 
            
                if($rowy['converteest']=="Y"){
                    if(empty($rowy['valconv'])){
                        $valconv=1;
                    }else{
                        $valconv=$rowy['valconv'];
                    }
                   
                    $un = traduzid('prodserv', 'idprodserv', 'un', $_idprodserv);
                   
                    $qtdprod=$_SESSION['arrpostbuffer']['x']['i']['lote']['qtdprod'];
                    //$qtddest= $qtdprod*$rowy['valconv'];
                     //round(ifnull(((ni.total/l.qtdprod)/l.valconvori),0),2)
                    $valor=round((($nitotal/$qtdprod)/$valconv),2);
                    $_SESSION['arrpostbuffer']['x']['i']['lote']['vlrlote']=$valor;
                    $_SESSION['arrpostbuffer']['x']['i']['lote']['qtdprod']=$qtdprod;
                    $_SESSION['arrpostbuffer']['x']['i']['lote']['qtdpedida']=$qtdprod;
                    $_SESSION['arrpostbuffer']['x']['i']['lote']['unlote']=$rowy['unforn'];
                    $_SESSION['arrpostbuffer']['x']['i']['lote']['unpadrao']=$un;
                    $_SESSION['arrpostbuffer']['x']['i']['lote']['valconvori']=$rowy['valconv'];
                    $_SESSION['arrpostbuffer']['x']['i']['lote']['converteest']=$rowy['converteest'];
                    
                }else{    
                    $qtdprod=$_SESSION['arrpostbuffer']['x']['i']['lote']['qtdprod'];
                    $un = traduzid('prodserv', 'idprodserv', 'un', $_idprodserv);
                     $valor=round(($nitotal/$qtdprod),2);
                    $_SESSION['arrpostbuffer']['x']['i']['lote']['vlrlote']=$valor;
                    $_SESSION['arrpostbuffer']['x']['i']['lote']['unlote']=$un;
                    $_SESSION['arrpostbuffer']['x']['i']['lote']['unpadrao']=$un;
                    $_SESSION['arrpostbuffer']['x']['i']['lote']['valconvori']=1;
                    $_SESSION['arrpostbuffer']['x']['i']['lote']['converteest']=$rowy['converteest'];
                }
            }else{// if(!empty($idprodservforn)){
                $valconv=1;
                $qtdprod=$_SESSION['arrpostbuffer']['x']['i']['lote']['qtdprod'];
                $un = traduzid('prodserv', 'idprodserv', 'un', $_idprodserv);
                 $valor=round(($nitotal/$qtdprod),2);
                $_SESSION['arrpostbuffer']['x']['i']['lote']['vlrlote']=$valor;
                $_SESSION['arrpostbuffer']['x']['i']['lote']['unlote']=$un;
                $_SESSION['arrpostbuffer']['x']['i']['lote']['unpadrao']=$un;
                $_SESSION['arrpostbuffer']['x']['i']['lote']['valconvori']=1;
                $_SESSION['arrpostbuffer']['x']['i']['lote']['converteest']='N';
       
            }
        }

        $rowx=PedidoController::buscarUnidadePorIdtipoIdempresa(3,$_idempresa);

        $idunidade=$rowx['idunidade'];// unidade almoxarifado

    $_arrlote = geraLote($_idprodserv);
    $_numlote = $_arrlote[0].$_arrlote[1];

    //Enviar o campo para a pagina de submit
        $_SESSION["arrpostbuffer"]["x"]["i"]["lote"]["partida"] = $_numlote;
        $_SESSION["arrpostbuffer"]["x"]["i"]["lote"]["idpartida"] = $_numlote;
        $_SESSION["arrpostbuffer"]["x"]["i"]["lote"]["spartida"] = $_arrlote[0];
        $_SESSION["arrpostbuffer"]["x"]["i"]["lote"]["npartida"] = $_arrlote[1];
        $_SESSION["arrpostbuffer"]["x"]["i"]["lote"]["idunidade"]=$idunidade;
        //Atribuir o valor para retorno por session['post'] ah pagina anterior.
        $_SESSION["post"]["_x_u_lote_partida"] = $_numlote;

        d::b()->query("COMMIT") or die("prechange: Falha ao efetuar COMMIT [sequence]: ".mysqli_error(d::b()));
    }else{
        //if($imobilizado =='Y'){ 
            //ALTERAÇÕES DO PROJETO: CRIAÇÃO DE CHECK IMOBILOZADO NA PRODSERV -> LINK DO EVENTO: sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=467416  -- ALBT 11/06/2021.
          // buscar lote do produto com status aprovado, se tem fracao e unidade tipoalmoxarifado   

      
        $rowc=PedidoController::buscarUnidadePorIdtipoIdempresa(3,$_idempresa);
        $idunidadeu=$rowc['idunidade'];// unidade almoxarifado 

        $rowl=PedidoController::buscarLotePorIdprodservIdunidade($idunidadeu,$_idprodserv);
        $num_rows= count($rowl);
            if($num_rows == 0){
                // se o numeros de linhas for 0, significa que nao existe lote para o produto em questão, sera criado entao um novo lote
                // adicionei todas as funcoes responsaveis pela criacao do lote novo. 
                if(!empty($idprodservforn)){
                    $_SESSION['arrpostbuffer']['x']['i']['lote']['idprodservforn']=$idprodservforn;
                
                    $rowy=PedidoController::buscarProdservfornPorId($idprodservforn); 
                    
                    if($rowy['converteest']=="Y"){
                        if(empty($rowy['valconv'])){
                            $valconv=1;
                        }else{
                            $valconv=$rowy['valconv'];
                        }
                       
                        $un = traduzid('prodserv', 'idprodserv', 'un', $_idprodserv);
                       
                        $qtdprod=$_SESSION['arrpostbuffer']['x']['i']['lote']['qtdprod'];
                        //$qtddest= $qtdprod*$rowy['valconv'];
                         //round(ifnull(((ni.total/l.qtdprod)/l.valconvori),0),2)
                        $valor=round((($nitotal/$qtdprod)/$valconv),2);
                        $_SESSION['arrpostbuffer']['x']['i']['lote']['vlrlote']=$valor;
                        $_SESSION['arrpostbuffer']['x']['i']['lote']['qtdprod']=$qtdprod;
                        $_SESSION['arrpostbuffer']['x']['i']['lote']['qtdpedida']=$qtdprod;
                        $_SESSION['arrpostbuffer']['x']['i']['lote']['unlote']=$rowy['unforn'];
                        $_SESSION['arrpostbuffer']['x']['i']['lote']['unpadrao']=$un;
                        $_SESSION['arrpostbuffer']['x']['i']['lote']['valconvori']=$rowy['valconv'];
                        $_SESSION['arrpostbuffer']['x']['i']['lote']['converteest']=$rowy['converteest'];
                    }else{    
                        $qtdprod=$_SESSION['arrpostbuffer']['x']['i']['lote']['qtdprod'];
                        $un = traduzid('prodserv', 'idprodserv', 'un', $_idprodserv);
                         $valor=round(($nitotal/$qtdprod),2);
                        $_SESSION['arrpostbuffer']['x']['i']['lote']['vlrlote']=$valor;
                        $_SESSION['arrpostbuffer']['x']['i']['lote']['unlote']=$un;
                        $_SESSION['arrpostbuffer']['x']['i']['lote']['unpadrao']=$un;
                        $_SESSION['arrpostbuffer']['x']['i']['lote']['valconvori']=1;
                        $_SESSION['arrpostbuffer']['x']['i']['lote']['converteest']=$rowy['converteest'];
                    }
                }else{// if(!empty($idprodservforn)){
                    $qtdprod=$_SESSION['arrpostbuffer']['x']['i']['lote']['qtdprod'];
                    $un = traduzid('prodserv', 'idprodserv', 'un', $_idprodserv);
                    $valor=round(($nitotal/$qtdprod),2);
                    $_SESSION['arrpostbuffer']['x']['i']['lote']['vlrlote']=$valor;
                    $_SESSION['arrpostbuffer']['x']['i']['lote']['unlote']=$un;
                    $_SESSION['arrpostbuffer']['x']['i']['lote']['unpadrao']=$un;
                    $_SESSION['arrpostbuffer']['x']['i']['lote']['valconvori']=1;
                    $_SESSION['arrpostbuffer']['x']['i']['lote']['converteest']='N';
                }

                $_arrlotenovo = geraLote($_idprodserv);
                $_numlotenovo = $_arrlotenovo[0].$_arrlotenovo[1];
                $idfluxostatus = FluxoController::getIdFluxoStatus('loteproducao', 'APROVADO', $idunidadeu);
                //Enviar o campo para a pagina de submit
                $_SESSION["arrpostbuffer"]["x"]["i"]["lote"]["partida"] = $_numlotenovo;
                $_SESSION["arrpostbuffer"]["x"]["i"]["lote"]["idpartida"] = $_numlotenovo;
                $_SESSION["arrpostbuffer"]["x"]["i"]["lote"]["spartida"] = $_arrlotenovo[0];
                $_SESSION["arrpostbuffer"]["x"]["i"]["lote"]["npartida"] = $_arrlotenovo[1];
                $_SESSION["arrpostbuffer"]["x"]["i"]["lote"]["idunidade"]=$idunidadeu;
                $_SESSION["arrpostbuffer"]["x"]["i"]["lote"]["idfluxostatus"]=$idfluxostatus;
                $_SESSION["arrpostbuffer"]["x"]["i"]["lote"]["status"]="APROVADO";

                //Atribuir o valor para retorno por session['post'] ah pagina anterior.
                $_SESSION["post"]["_x_u_lote_partida"] = $_numlotenovo;

            }else{
                // se existir lote ja criado busca o idfracao correspondente ao lote cadastrado com a unidade correspondente ao almoxarifado
                $rowlf =PedidoController::buscarLotefracaoPorIdloteIdunidade($rowl['idlote'],$idunidadeu);
                $num_rows_fr= count($rowlf);
                //se o numero retornado da fracao for menor que 1 , cria uma fraçao nova com status disponivel, idlote (que nesse momento ja existe), qtdprod, e idunidade do almoxarifado.
                if($num_rows_fr < 1 ){
                    $qtdprod=$_SESSION['arrpostbuffer']['x']['i']['lote']['qtdprod'];
                    $ifracao = new Insert();
                    $ifracao->setTable("lotefracao");
                    $ifracao->status="DISPONIVEL";
                    $ifracao->idlote=$rowl['idlote'];
                    $ifracao->qtdini=$qtdprod;
                    $ifracao->idunidade=$idunidadeu;
                    $idlotefracao=$ifracao->save();
                //limpar $_SESSION["arrpostbuffer"] -> limpa o array antes de enviar informacoes novas
                //    unset($_SESSION["arrpostbuffer"]["x"]["u"]["idlote"]["idlote"]);
                //    unset($_SESSION["arrpostbuffer"]["x"]["u"]["status"]["status"]);
                    $_SESSION["arrpostbuffer"]["x"]["u"]["lote"]["idlote"]=$rowl['idlote'];
                    $_SESSION["arrpostbuffer"]["x"]["u"]["lote"]["status"]=$rowl['status'];
                }else{ //se existir fracao (com idlote correspondente), ja faz todo processo abaixo de new insert()
                    $idlotefracao=$rowlf['idlotefracao'];
                }
                $qtdprod=$_SESSION['arrpostbuffer']['x']['i']['lote']['qtdprod'];
                $ilotecons=new Insert();
                $ilotecons->setTable("lotecons");
                $ilotecons->idlote= $rowl['idlote'];
                $ilotecons->idempresa=$_idempresa;
                $ilotecons->idlotefracao=$idlotefracao; 
                $ilotecons->idobjeto=$idnfitem;
                $ilotecons->tipoobjeto='nfitem';
                $ilotecons->qtdc=$qtdprod;
                $idlotecons=$ilotecons->save();
                //limpar $_SESSION["arrpostbuffer"] -> limpa o array antes de enviar informacoes novas
                unset($_SESSION["arrpostbuffer"]["x"]["u"]["idlote"]["idlote"]);
                 unset($_SESSION["arrpostbuffer"]["x"]["u"]["status"]["status"]);

                $_SESSION["arrpostbuffer"]["x"]["u"]["lote"]["idlote"]=$rowl['idlote'];
                $_SESSION["arrpostbuffer"]["x"]["u"]["lote"]["status"]=$rowl['status'];
            }
    }
}

if(!empty($_SESSION["arrpostbuffer"]["pr"]["u"]["nfconfpagar"]["idnfconfpagar"]) && !empty($_SESSION["arrpostbuffer"]["pr"]["u"]["nfconfpagar"]["proporcao"]))
{
    $proporcao = $_SESSION["arrpostbuffer"]["pr"]["u"]["nfconfpagar"]["proporcao"];
    $valor_total = $_POST['valor_total'];
    $_SESSION["arrpostbuffer"]["pr"]["u"]["nfconfpagar"]["valorparcela"] = ($valor_total * $proporcao)/100;
}

$nf_emailboleto = $_POST['_nf_emailboleto'];
$qtd_boleto_faturar = $_POST['qtd_boleto_faturar'];
if($status == 'ENVIAR' && $statusant == 'FATURAR' /*&& $nf_emailboleto == 'Y' && $qtd_boleto_faturar > 0*/)
{
    $emailSelected = $_POST['nameemailnfe'];
    $idemailvirtualconf = $_POST["idemailvirtualconf-$emailSelected"];
    $emaildadosnfemat = $_POST["emaildadosnfemat-$emailSelected"];
    $tipoenvioemail = $_POST["tipoenvioemail-$emailSelected"];
    $idempresa_dominio = $_POST["idempresa_dominio-$emailSelected"];    
    $emaildadosnfe = $_SESSION['arrpostbuffer']['1']['u']['nf']['emaildadosnfe'];
    $tipoenvio = ($tipoenvioemail == 'VENDA') ? 'NFP' : 'NFPS';

    $_SESSION['arrpostbuffer']['1']['u']['nf']['tipoenvioemail'] = $tipoenvioemail;
    $_SESSION['arrpostbuffer']['1']['u']['nf']['emaildadosnfe'] = $emaildadosnfe;
    $_SESSION['arrpostbuffer']['1']['u']['nf']['emaildadosnfemat'] = $emaildadosnfemat;
    $_SESSION['arrpostbuffer']['w']['i']['empresaemailobjeto']['idempresa'] = $idempresa_dominio;
    $_SESSION['arrpostbuffer']['w']['i']['empresaemailobjeto']['idemailvirtualconf'] = $idemailvirtualconf;
    $_SESSION['arrpostbuffer']['w']['i']['empresaemailobjeto']['tipoenvio'] = $tipoenvio;
    $_SESSION['arrpostbuffer']['w']['i']['empresaemailobjeto']['tipoobjeto'] = 'nf';
    $_SESSION['arrpostbuffer']['w']['i']['empresaemailobjeto']['idobjeto'] = $_idnf;

    retarraytabdef('empresaemailobjeto');
}

$formaPagamento = NfController::buscarFormaPagamentoPorIdNf($_SESSION['arrpostbuffer']['1']['u']['nf']['idnf']);
if($status == 'ENVIADO' && $statusant != 'ENVIADO' && (($formaPagamento == 'BOLETO' && $nf_emailboleto == 'Y' && $qtd_boleto_faturar > 0) || ($formaPagamento != 'BOLETO')) )
{
    $_SESSION['arrpostbuffer']['1']['u']['nf']['envioemail'] = 'Y';
}

if($_SESSION['arrpostbuffer']['1']['u']['nf']['geracontapagar'] == 'N'){
    $_SESSION['arrpostbuffer']['1']['u']['nf']['idformapagamento'] = '';
}

if(!empty($_SESSION['arrpostbuffer']['filial']['u']['nf']['idnf']) 
and !empty($_SESSION['arrpostbuffer']['filial']['u']['nf']['idempresafat'])){

    $idnf=$_SESSION['arrpostbuffer']['filial']['u']['nf']['idnf'];
    $_idempresa = $_SESSION['arrpostbuffer']['filial']['u']['nf']['idempresafat'];

    $sqlp = "select e.uf,p.indiedest
            from nf f, endereco e,pessoa p
            where e.idendereco = f.idenderecofat and  p.idpessoa = f.idpessoafat and f.idnf = ".$idnf;
    $resp = d::b()->query($sqlp) or die("Erro ao buscar endereco: ".mysqli_error(d::b()));
    $qtdrowsp= mysqli_num_rows($resp);
    $rowuf=mysqli_fetch_assoc($resp);
    $uf=$rowuf["uf"];
    $ufemp=traduzid("empresa","idempresa","uf",$_idempresa);

    if($ufemp=='PR' and  $uf=='PR' ){// se for da filial do parana @838527
        if($rowuf['indiedest']==9){// Cliente não contribuinte      
            //TRIBUTAÇÃO INTEGRAL ICMS ALIQUOTA INTERNA DO PARANA                      
            $sql="UPDATE nfitem 
                    SET 
                        cst = '00',
                        cfop=5101,
                        descdeson = '0.00',
                        basecalc = vlrliq*qtd,
                        vicmsdeson = '0.00',
                        valipi = '0.00',
                        vst = '0.00',
                        aliqbasecal = '100.00',
                        aliqicms = '19.50',
                        aliqipi = '0.00',
                        pis = case when confinscst='01' then pis else 0.00 end,
                        icmsufdest = '0.00',
                        aliqpis=case when confinscst='01' then aliqpis else 0.00 end,
                        aliqcofins=case when confinscst='01' then aliqcofins else 0.00 end,
                        bcpis=case when confinscst='01' then bcpis else 0.00 end,
                        bccofins=case when confinscst='01' then bccofins else 0.00 end,
                        indiedest=9,
                        icmsufremet = '0.00',
                        cofins = case when confinscst='01' then cofins else 0.00 end
                    WHERE
                        idnf = ".$idnf;
        }else{// Cliente  contribuinte e isento bc imposto zerado
            $sql="UPDATE nfitem 
                    SET 
                        cst = '51',
                        cfop=5101,
                        descdeson = '0.00',
                        basecalc = '0.00',
                        vicmsdeson = '0.00',
                        valicms = '0.00',
                        valipi = '0.00',
                        vst = '0.00',
                        aliqbasecal = '0.00',
                        aliqicms = '0.00',
                        aliqipi = '0.00',
                        pis = case when confinscst='01' then pis else 0.00 end,
                        icmsufdest = '0.00',
                        aliqpis=case when confinscst='01' then aliqpis else 0.00 end,
                        aliqcofins=case when confinscst='01' then aliqcofins else 0.00 end,
                        bcpis=case when confinscst='01' then bcpis else 0.00 end,
                        bccofins=case when confinscst='01' then bccofins else 0.00 end,
                        indiedest=0,
                        icmsufremet = '0.00',
                        cofins = case when confinscst='01' then cofins else 0.00 end
                    WHERE
                        idnf = ".$idnf;
        }
    }else{
        $sql="UPDATE nfitem 
        SET 
            cst = '40',
            cfop=5101,
            descdeson = '0.00',
            basecalc = '0.00',
            vicmsdeson = '0.00',
            valicms = '0.00',
            valipi = '0.00',
            vst = '0.00',
            aliqbasecal = '0.00',
            aliqicms = '0.00',
            aliqipi = '0.00',
            pis = case when confinscst='01' then pis else 0.00 end,
            icmsufdest = '0.00',
            aliqpis=case when confinscst='01' then aliqpis else 0.00 end,
            aliqcofins=case when confinscst='01' then aliqcofins else 0.00 end,
            bcpis=case when confinscst='01' then bcpis else 0.00 end,
            bccofins=case when confinscst='01' then bccofins else 0.00 end,
            indiedest=0,
            icmsufremet = '0.00',
            cofins = case when confinscst='01' then cofins else 0.00 end
        WHERE
            idnf = ".$idnf;

    }
    //se cofins e pis for 01 não zerar pis e cofins neim aliq
   
    $res = d::b()->query($sql) or die("erro ao zerar impostos dos itens para faturar pela filial: " . mysqli_error(d::b()) . "<p>SQL: ".$sql);
}

if($_POST['statusant']!="CONCLUIDO" and $_SESSION['arrpostbuffer']['restaurar']['u']['nf']['status']=="CONCLUIDO" and !empty($_SESSION['arrpostbuffer']['restaurar']['u']['nf']['idnf'])){

    $_idnf=$_SESSION['arrpostbuffer']['restaurar']['u']['nf']['idnf'];

    $sqls="select
                n.total,sum(ifnull(i.valor,0)) as valor ,n.geracontapagar
            from nf n 
                left join contapagaritem i on(i.idobjetoorigem = n.idnf and i.tipoobjetoorigem = 'nf' and i.status!='INATIVO')
            where n.idnf=".$_idnf;

    $res = d::b()->query($sqls) or die("erro ao buscar valores do pedido e faturas " . mysqli_error(d::b()) . "<p>SQL: ".$sqls);

    $_row = mysqli_fetch_assoc($res);

    if($_row['total'] != $_row['valor'] and  $_row['geracontapagar'] == 'Y' ){
        die("O valor do pedido (".$_row['total']."), deve ser o mesmo da soma das faturas (".$_row['total']."), antes da Conclusão do pedido. </br> Salve o pedido novamente antes de Concluir.");
    }

}


