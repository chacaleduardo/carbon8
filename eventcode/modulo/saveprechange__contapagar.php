<?//Copiar o pedido;

require_once(__DIR__."/../../form/controllers/fluxo_controller.php");
 

if(!empty($_SESSION['arrpostbuffer']['x']['i']['contapagar']['idobjeto']) 
        and $_SESSION['arrpostbuffer']['x']['i']['contapagar']['tipoobjeto']=='contapagar'
        and $_POST["_x_copiar"]=="Y"){
   $idcontapagarcp= $_SESSION['arrpostbuffer']['x']['i']['contapagar']['idobjeto'];
   
    //LTM - 20-04-2021: Retorna o Idfluxo ContaPagar
    $idfluxostatus = FluxoController::getIdFluxoStatus('contapagar', 'PENDENTE');

    $sql="select * from contapagar where idcontapagar=".$idcontapagarcp;
    $res=d::b()->query($sql) or die("Erro ao buscar contapagar para copia sql=".$sql);
    $row=mysqli_fetch_assoc($res);

    $_SESSION['arrpostbuffer']['x']['i']['contapagar']['idcontaitem']=$row['idcontaitem'];
    $_SESSION['arrpostbuffer']['x']['i']['contapagar']['idcontadesc']=$row['idcontadesc'];
    $_SESSION['arrpostbuffer']['x']['i']['contapagar']['datapagto']=dma($row['datapagto']);
    $_SESSION['arrpostbuffer']['x']['i']['contapagar']['datareceb']=dma($row['datareceb']);
    $_SESSION['arrpostbuffer']['x']['i']['contapagar']['valor']=$row['valor'];
    $_SESSION['arrpostbuffer']['x']['i']['contapagar']['periodicidade']=$row['periodicidade'];
    $_SESSION['arrpostbuffer']['x']['i']['contapagar']['parcelas']=$row['parcelas'];
    $_SESSION['arrpostbuffer']['x']['i']['contapagar']['parcela']=$row['parcela'];
    $_SESSION['arrpostbuffer']['x']['i']['contapagar']['obs']=$row['obs'];
    $_SESSION['arrpostbuffer']['x']['i']['contapagar']['obscobranca']=$row['obscobranca'];
    $_SESSION['arrpostbuffer']['x']['i']['contapagar']['tipo']=$row['tipo'];
    $_SESSION['arrpostbuffer']['x']['i']['contapagar']['tipoespecifico']=$row['tipoespecifico'];
    $_SESSION['arrpostbuffer']['x']['i']['contapagar']['status']='PENDENTE';
    $_SESSION['arrpostbuffer']['x']['i']['contapagar']['idfluxostatus'] = $idfluxostatus;
    $_SESSION['arrpostbuffer']['x']['i']['contapagar']['idagencia']=$row['idagencia'];
    //$_SESSION['arrpostbuffer']['x']['i']['contapagar']['idobjeto']=$row['idobjeto'];
    //$_SESSION['arrpostbuffer']['x']['i']['contapagar']['tipoobjeto']=$row['tipoobjeto'];
    $_SESSION['arrpostbuffer']['x']['i']['contapagar']['idpessoa']=$row['idpessoa'];
    $_SESSION['arrpostbuffer']['x']['i']['contapagar']['idrepresentante']=$row['idrepresentante'];
    $_SESSION['arrpostbuffer']['x']['i']['contapagar']['tipointervalo']=$row['tipointervalo'];
    $_SESSION['arrpostbuffer']['x']['i']['contapagar']['intervalo']=$row['intervalo'];
    $_SESSION['arrpostbuffer']['x']['i']['contapagar']['formapagto']=$row['formapagto'];
    $_SESSION['arrpostbuffer']['x']['i']['contapagar']['idcartao']=$row['idcartao'];
    $_SESSION['arrpostbuffer']['x']['i']['contapagar']['visivel']=$row['visivel'];

}

//atualizar o valor da fatura anterior ao mudar a parcela de fatura
$_idcontapagaritem=$_SESSION['arrpostbuffer']['x']['u']['contapagaritem']['idcontapagaritem'];
$_idcontapagar=$_SESSION['arrpostbuffer']['x']['u']['contapagaritem']['idcontapagar'];
if(!empty($_idcontapagaritem) and !empty($_idcontapagar)){
    $tipoespecifico=traduzid("contapagar","idcontapagar","tipoespecifico",$_idcontapagar );
   if($tipoespecifico!='NORMAL'){
    $_idcontapagarOld=traduzid("contapagaritem","idcontapagaritem","idcontapagar",$_idcontapagaritem );

        $sql="select sum(valor) AS valor ,f.agrupado,f.formapagamento,f.agruppessoa
        from contapagaritem i join  formapagamento f on( f.idformapagamento=i.idformapagamento)
            where i.idcontapagar = ".$_idcontapagarOld."
            and i.status !='INATIVO'
            and i.idcontapagaritem !=".$_idcontapagaritem;

        $res=d::b()->query($sql) or die("Falha ao buscar somatorio da fatura sql=".$sql);
        $qtdrows=mysqli_num_rows($res);
        $row=mysqli_fetch_assoc($res);
        if($qtdrows>0 and $row['formapagamento']!='C.CREDITO'  and ($row["formapagamento"]!='BOLETO' or  $row['agruppessoa']!='Y') ){
          
            $sqlu="update contapagar set valor='".$row['valor']."' where idcontapagar=".$_idcontapagarOld;
            $resu=d::b()->query($sqlu) or die("Falha ao atualizar a somatória da fatura sql=".$sqlu);
        }      

   }   

}


//gerar um nova conta aberta para evitar que seja duplicada a conta no mesmo mês
//assim quando a parcela ao ser gerada ira encontrar a fatura aberta para entrar
//hermesp 20-11-200
$iu = $_SESSION['arrpostbuffer']['1']['u']['contapagar']['idcontapagar'] ? 'u' : 'i';
$status=$_SESSION['arrpostbuffer']['1']['u']['contapagar']['status'];
if($iu =='u' and $status=='FECHADO' or $status=='PENDENTE'){
    $idcontapagar=$_SESSION['arrpostbuffer']['1']['u']['contapagar']['idcontapagar'];
        
    $sql="select 
                (LAST_DAY(c.datareceb) + INTERVAL ifnull(f.diavenc,1) DAY) as datavencimento,f.previsao,c.* 
            from contapagar c join formapagamento f  on(f.idformapagamento= c.idformapagamento)
            where c.status='ABERTO' 
            and f.agrupado= 'Y' 
            and  f.agrupfpagamento= 'Y' 
            and c.idcontapagar =".$idcontapagar."
            and not exists (select 1 from contapagar c2
                                where c2.idformapagamento = c.idformapagamento and c2.idempresa = c.idempresa
                                and c2.tipoespecifico=c.tipoespecifico
                                and c2.status='ABERTO' 
                                and c2.datareceb >= (LAST_DAY(c.datareceb) + INTERVAL ifnull(f.diavenc,1) DAY)
                            )";
            
    $res=d::b()->query($sql) or die($sql."save_prechange: Falha ao buscar informações da conta".mysqli_error());
    $qtd=mysqli_num_rows($res);
    
    if($qtd>0){
        $row=mysqli_fetch_assoc($res);
        
        //LTM - 20-04-2021: Retorna o Idfluxo ContaPagar
        $idfluxostatus = FluxoController::getIdFluxoStatus('contapagar', 'ABERTO');

        $inscontapagar = new Insert();
        $inscontapagar->setTable("contapagar");				
        $inscontapagar->idagencia=$row['idagencia'];                        
        $inscontapagar->idcontaitem=$row['idcontaitem']; 
        $inscontapagar->status='ABERTO';
        $inscontapagar->idfluxostatus = $idfluxostatus;
        $inscontapagar->parcela=1;                                
        $inscontapagar->parcelas=1;                            
        $inscontapagar->idformapagamento=$row['idformapagamento'];
        $inscontapagar->valor=$row['previsao'];
        $inscontapagar->tipo=$row['tipo'];
        $inscontapagar->visivel=$row['visivel'];
        $inscontapagar->tipoespecifico=$row['tipoespecifico'];
        $inscontapagar->datapagto=$row['datavencimento'];
        $inscontapagar->datareceb=$row['datavencimento'];

        $idcontapagarins=$inscontapagar->save();   
        
        //LTM - 20-04-2021: Retorna o IdfluxoHist
        FluxoController::inserirFluxoStatusHist('contapagar', $idcontapagarins, $idfluxostatus, 'PENDENTE');
    }
    
    
}

if($_SESSION['arrpostbuffer']['1']['u']['contapagar']['status']=='PENDENTE' and ($_POST['contapagar_status_ant']=='ABERTO' or $_POST['contapagar_status_ant']=='FECHADO'))
{
    $idcontapagar=$_SESSION['arrpostbuffer']['1']['u']['contapagar']['idcontapagar'];
    $arrCP=getObjeto("contapagar", $idcontapagar,"idcontapagar");


    $sqlfo="select * from formapagamento
	where idformapagamento =".$arrCP['idformapagamento']." 
		and tipoespecifico in ('REPRESENTACAO','IMPOSTO')";
		$resfo= d::b()->query($sqlfo) or die($sqlfo."Fala ao buscar se forma de pagamento e comissao: <br>".mysqli_error());
		$qtdfo=mysqli_num_rows($resfo);
		if($qtdfo>0){


            if(empty($arrCP['idobjeto'])){


                if($arrCP['tipoespecifico']=='REPRESENTACAO'){
                    $tiponf='R';
                    $idtipounidade = 14;
                   
                }else{
                    $tiponf='S';
                    $idtipounidade = 19;
                    
                }
                
                $qrUnid = "SELECT idunidade FROM unidade WHERE idtipounidade = ".$idtipounidade." AND idempresa = ".$arrCP['idempresa'];
                $rsUnid = d::b()->query($qrUnid) or die("[prechangecontapagar][1]: Erro ao buscar idunidade. SQL: ".$qrUnid);
                $rwUnid = mysqli_fetch_assoc($rsUnid);
               
                $_SESSION['arrpostbuffer']['x']['i']['nf']['idobjetosolipor']=$idcontapagar;
                $_SESSION['arrpostbuffer']['x']['i']['nf']['idformapagamento']=$arrCP['idformapagamento'];
                $_SESSION['arrpostbuffer']['x']['i']['nf']['tipoobjetosolipor']='contapagar';
                $_SESSION['arrpostbuffer']['x']['i']['nf']['idpessoa']=$arrCP['idpessoa'];
                $_SESSION['arrpostbuffer']['x']['i']['nf']['tiponf']=$tiponf;
                $_SESSION['arrpostbuffer']['x']['i']['nf']['idunidade']=$rwUnid['idunidade'];
                $_SESSION['arrpostbuffer']['x']['i']['nf']['subtotal']=$arrCP['valor'];   
                $_SESSION['arrpostbuffer']['x']['i']['nf']['total']=$arrCP['valor'];   
                $_SESSION['arrpostbuffer']['x']['i']['nf']['dtemissao']=dmahms($arrCP['datareceb']);   
                
                
                $sqlf="select * from confcontapagar
                where idformapagamento =".$arrCP['idformapagamento']." 
                    and tipo='COMISSAO' and status='ATIVO'";
                $resf= d::b()->query($sqlf) or die($sqlf."Fala ao buscar se nota automática: <br>".mysqli_error());
                $rowf=mysqli_fetch_assoc($resf);

                if(!empty($rowf['statusnf'])){
                    if( $_SESSION['arrpostbuffer']['x']['i']['nf']['tiponf']=='R'){
                        $idfluxostatus = FluxoController::getIdFluxoStatus('comprasrh', $rowf['statusnf']);
                    }else{
                        $idfluxostatus = FluxoController::getIdFluxoStatus('nfentrada', $rowf['statusnf']);
                    } 
                
                    $_SESSION['arrpostbuffer']['x']['i']['nf']['tipoobjetosolipor'] = 'contapagar';
                    $_SESSION['arrpostbuffer']['x']['i']['nf']['status'] = $rowf['statusnf'];
                    $_SESSION['arrpostbuffer']['x']['i']['nf']['idfluxostatus'] = $idfluxostatus;
                }

            
            }elseif($arrCP['tipoobjeto']=='nf'){        
                $_SESSION['arrpostbuffer']['x']['u']['nf']['idobjetosolipor']=$idcontapagar;
                $_SESSION['arrpostbuffer']['x']['u']['nf']['idnf']=$arrCP['idobjeto'];
                $_SESSION['arrpostbuffer']['x']['u']['nf']['idformapagamento']=$arrCP['idformapagamento'];
                $_SESSION['arrpostbuffer']['x']['u']['nf']['tipoobjetosolipor']='contapagar';
                $_SESSION['arrpostbuffer']['x']['u']['nf']['idpessoa']=$arrCP['idpessoa'];
                //$_SESSION['arrpostbuffer']['x']['u']['nf']['tiponf']='S';
                $_SESSION['arrpostbuffer']['x']['u']['nf']['subtotal']=$arrCP['valor'];
                $_SESSION['arrpostbuffer']['x']['u']['nf']['total']=$arrCP['valor'];
            
            }
            montatabdef();
        }
}

//GERAR A NOTA DA FATURA COM STATUS CONCLUIDO 
if(!empty($_SESSION['arrpostbuffer']['x']['i']['nf']['idobjetosolipor']) and $_SESSION['arrpostbuffer']['x']['i']['nf']['tipoobjetosolipor']=='contapagar' and empty($_SESSION['arrpostbuffer']['x']['i']['nf']['status']))
{
    if( $_SESSION['arrpostbuffer']['x']['i']['nf']['tiponf']=='R'){
        $idfluxostatus = FluxoController::getIdFluxoStatus('comprasrh', 'APROVADO');
    }else{
        $idfluxostatus = FluxoController::getIdFluxoStatus('nfentrada', 'APROVADO');
    } 
   
    $_SESSION['arrpostbuffer']['x']['i']['nf']['tipoobjetosolipor'] = 'contapagar';
    $_SESSION['arrpostbuffer']['x']['i']['nf']['status'] = 'APROVADO';
    $_SESSION['arrpostbuffer']['x']['i']['nf']['idfluxostatus'] = $idfluxostatus;
} 
