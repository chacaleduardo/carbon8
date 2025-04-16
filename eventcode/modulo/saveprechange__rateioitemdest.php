<?

require_once(__DIR__."/../../form/controllers/rateioitemdest_controller.php");
require_once(__DIR__."/../../form/controllers/fluxo_controller.php");

//Gerar a configuração de rateio 
$id_rateioitem=$_SESSION['arrpostbuffer']['x']['i']['rateioitemdest']['idrateioitem'];

//print_r($_SESSION['arrpostbuffer']); die();
if(!empty($id_rateioitem) ){    
   
    $sql="select * from rateioitemdest where idrateioitem=".$id_rateioitem;
    $res=d::b()->query($sql) or die("Falha ao verificar rateio sql=".$sql);
    $qtd= mysqli_num_rows($res);
    $qtd=$qtd+1;
    $valor=100/$qtd;
    if($qtd>1){
        $sql="update rateioitemdest set valor='".$valor."' where idrateioitem=".$id_rateioitem;
        $res=d::b()->query($sql) or die("Falha ao atualizar rateio sql=".$sql);
    }
    $_SESSION['arrpostbuffer']['x']['i']['rateioitemdest']['valor']=$valor;
}

$idrateioitemdest=$_SESSION['arrpostbuffer']['x']['d']['rateioitemdest']['idrateioitemdest'];

if(!empty($idrateioitemdest) ){   
    $idrateioitem= traduzid('rateioitemdest', 'idrateioitemdest', 'idrateioitem', $idrateioitemdest);
    $sql="select * from rateioitemdest where idrateioitem=".$idrateioitem;
    $res=d::b()->query($sql) or die("Falha ao verificar rateio sql=".$sql);
    $qtd= mysqli_num_rows($res);
   
    $qtd=$qtd-1;
    
    $valor=100/$qtd;
   
    if($qtd>0){
        $sqlx="update rateioitemdest set valor='".$valor."' where idrateioitem=".$idrateioitem; //die($sqlx);
        $resx=d::b()->query($sqlx) or die("Falha ao atualizar rateio sql=".$sqlx);
    }
}

$telarateiotodos=$_POST['telarateiotodos'];
$restaurartodos=$_POST['restaurartodos'];
$restaurartodosdest=$_POST['restaurartodosdest'];
$porempresa=$_POST['porempresa'];

if($telarateiotodos=="Y" and $porempresa=='N'){//RATEIO INTERNO
  
    // PREPARA PARA INSERIR
    $arrInsrateio=array();
    foreach($_POST as $k=>$v) {
        if(preg_match("/_(\d*)#(.*)/", $k, $res)){
            $arrInsrateio[$res[1]][$res[2]]=$v;
        }
    }
       
    $arrpb=$_SESSION["arrpostbuffer"];
    unset($_SESSION["arrpostbuffer"]);
    reset($arrpb); 
    // print_r($arrpb); die();
    $linhanova = 0;      
    foreach($arrpb as $linha => $arrlinha) {
        foreach($arrlinha as $acao => $arracao) {
            if($acao == "u"){
                foreach($arracao as $tab => $arrtab) {    
                    if($tab == 'rateioitemdest') {
                        $sql = "select l.idprodserv,d.idrateioitemdestorigem,d.* from rateioitemdest d left join rateioitem i on(i.idrateioitem=d.idrateioitem)
                                left join nfitem n on(n.idnfitem=i.idobjeto and i.tipoobjeto='nfitem')
                                left join lote l on(l.idnfitem=n.idnfitem)
                                where d.idrateioitemdest=".$arrtab['idrateioitemdest'];   
                        $res = d::b()->query($sql) or die("Falha ao buscar rateiodest sql=".$sql); 
                        $row = mysqli_fetch_assoc($res);  
                        $l = 0;
                        foreach($arrInsrateio as $k => $v){
                            if($row['valor'] == null or $row['valor'] == 0){$row['valor'] = 100;}
                            $linhanova = $linhanova + 1;
                            $destino = explode(',', $v['idunidade']); 
                            $nvalor = ($row['valor'] * ($v['valor'] / 100));
                            if($l == 0){

                                if(!empty($row['idprodserv']) and empty($row['idrateioitemdestorigem'])){
                                    
                                    $_SESSION["arrpostbuffer"]['u'.$linha][$acao]["rateioitemdest"]["idrateioitemdest"] =$arrtab['idrateioitemdest'];
                                    $_SESSION["arrpostbuffer"]['u'.$linha][$acao]["rateioitemdest"]["status"] ='EDITADO';
                                    

                                    $_SESSION["arrpostbuffer"][$linhanova]['i']["rateioitemdest"]["idobjeto"] =  $destino[0];
                                    $_SESSION["arrpostbuffer"][$linhanova]['i']["rateioitemdest"]["tipoobjeto"] = $destino[1];
                                    $_SESSION["arrpostbuffer"][$linhanova]['i']["rateioitemdest"]["idrateioitemdestorigem"] = $arrtab['idrateioitemdest'];
                                    if(!empty($v['idpessoa'])){
                                        $_SESSION["arrpostbuffer"][$linhanova]['i']["rateioitemdest"]["idpessoa"] =$v['idpessoa'];
                                    }

                                    $_SESSION["arrpostbuffer"][$linhanova]['i']["rateioitemdest"]["valor"] =$nvalor;
                                    $_SESSION["arrpostbuffer"][$linhanova]['i']["rateioitemdest"]["idrateioitem"] =$row['idrateioitem'];
                                    

                                }else{                                
                                    $_SESSION["arrpostbuffer"][$linha][$acao]["rateioitemdest"]["idrateioitemdest"] =$arrtab['idrateioitemdest'];
                                    $_SESSION["arrpostbuffer"][$linha][$acao]["rateioitemdest"]["idobjeto"] =  $destino[0];
                                    $_SESSION["arrpostbuffer"][$linha][$acao]["rateioitemdest"]["tipoobjeto"] = $destino[1];
                                    if(!empty($v['idpessoa'])){
                                        $_SESSION["arrpostbuffer"][$linha][$acao]["rateioitemdest"]["idpessoa"] =$v['idpessoa'];
                                    }
                                    $_SESSION["arrpostbuffer"][$linha][$acao]["rateioitemdest"]["valor"] =$nvalor;
                                    $_SESSION["arrpostbuffer"][$linha][$acao]["rateioitemdest"]["idrateioitem"] =$row['idrateioitem'];                                 
                                }
                            }else{
                                $_SESSION["arrpostbuffer"][$linhanova."x"]['i']["rateioitemdest"]["idobjeto"] =  $destino[0];
                                $_SESSION["arrpostbuffer"][$linhanova."x"]['i']["rateioitemdest"]["tipoobjeto"] = $destino[1];
                                if(!empty($v['idpessoa'])){
                                    $_SESSION["arrpostbuffer"][$linhanova."x"]['i']["rateioitemdest"]["idpessoa"]=$v['idpessoa'];
                                }
                                $_SESSION["arrpostbuffer"][$linhanova."x"]['i']["rateioitemdest"]["valor"] =$nvalor;
                                $_SESSION["arrpostbuffer"][$linhanova."x"]['i']["rateioitemdest"]["idrateioitem"] =$row['idrateioitem'];
                                if(!empty($row['idprodserv']) and empty($row['idrateioitemdestorigem'])){
                                    $_SESSION["arrpostbuffer"][$linhanova."x"]['i']["rateioitemdest"]["idrateioitemdestorigem"] = $arrtab['idrateioitemdest'];
                                }
                                
                            }
                            $l=$l+1;
                        }//foreach($arrInsrateio as $k=>$v){
                        reset($arrInsrateio); 
                    }
                }
            }else{
                foreach($arracao as $tab => $arrtab){
                    if($tab == 'rateioitemdest') { 
                        $sql="select n.idempresa,i.idnf,i.idnfitem,ifnull(p.descr,i.prodservdescr) as descr,i.total as rateio,ri.idrateioitem,ifnull(rd.valor,100) as valorateio,
                                    CASE
                                        WHEN n.dtemissao is not null THEN n.dtemissao 
                                        ELSE now()
                                    END as emissao
                                from nfitem i       
                                join nf n on(n.idnf=i.idnf)                                          
                                left join prodserv p on(p.idprodserv=i.idprodserv)
                                left join rateioitem ri on(ri.idobjeto = i.idnfitem and ri.tipoobjeto = 'nfitem' )
                                left join rateioitemdest rd on(rd.idrateioitem = ri.idrateioitem)
                            where i.idnfitem=".$arrtab['idobjeto'];
                        
                        $res=d::b()->query($sql) or die("Falha ao buscar informações do rateio sql=".$sql); 
                        $row=mysqli_fetch_assoc($res); 
                        /* 
                        if(empty($row['idrateio'])){
                            $insrateio = new Insert();
                            $insrateio->setTable("rateio");
                            $insrateio->idobjeto=$row['idnf'];
                            $insrateio->tipoobjeto='nf';
                            $_idrateio=$insrateio->save();
                        }else{
                            $_idrateio=$row['idrateio'];
                        }
*/
                        if(empty($row['idrateioitem'])){
                            $_idrateio =retidrateio($row['emissao'],$row['idempresa']);
                            $insrateioitem = new Insert();
                            $insrateioitem->setTable("rateioitem");
                            $insrateioitem->idrateio=$_idrateio;
                            $insrateioitem->idobjeto=$row['idnfitem'];
                            $insrateioitem->tipoobjeto='nfitem';
                            $_idrateioitem=$insrateioitem->save();
                        }else{
                            $_idrateioitem=$row['idrateioitem'];
                        }

                        foreach($arrInsrateio as $k=>$v){
                            $linhanova=$linhanova+1;
                            $destino=explode(',',$v['idunidade']); 
                            $nvalor=(100*($v['valor']/100));
                            
                            $_SESSION["arrpostbuffer"][$linhanova."x"]['i']["rateioitemdest"]["idobjeto"] =  $destino[0];
                            $_SESSION["arrpostbuffer"][$linhanova."x"]['i']["rateioitemdest"]["tipoobjeto"] = $destino[1];
                            if(!empty($v['idpessoa'])){
                                $_SESSION["arrpostbuffer"][$linhanova."x"]['i']["rateioitemdest"]["idpessoa"] = $v['idpessoa'];  
                            }
                            $_SESSION["arrpostbuffer"][$linhanova."x"]['i']["rateioitemdest"]["valor"] =$nvalor;
                            $_SESSION["arrpostbuffer"][$linhanova."x"]['i']["rateioitemdest"]["idrateioitem"] = $_idrateioitem;                          


                            $_SESSION["arrpostbuffer"][$linhanova."xx"]['i']["rateioitemdestori"]["idobjeto"] =  $destino[0];
                            $_SESSION["arrpostbuffer"][$linhanova."xx"]['i']["rateioitemdestori"]["tipoobjeto"] = $destino[1];
                            if(!empty($v['idpessoa'])){
                                $_SESSION["arrpostbuffer"][$linhanova."xx"]['i']["rateioitemdestori"]["idpessoa"] = $v['idpessoa'];  
                            }
                            $_SESSION["arrpostbuffer"][$linhanova."xx"]['i']["rateioitemdestori"]["valor"] =$nvalor;
                            $_SESSION["arrpostbuffer"][$linhanova."xx"]['i']["rateioitemdestori"]["idrateioitem"] = $_idrateioitem;

                            
                            $l=$l+1;
                        }//foreach($arrInsrateio as $k=>$v){
                        reset($arrInsrateio); 
                    }                
                }
            }
        }
    } 
    montatabdef();

}elseif($telarateiotodos == "Y" && $porempresa == 'Y'){//RATEIO EXTERNO
     
    // PREPARA PARA INSERIR
    $arrInsrateio=array();
    foreach($_POST as $k=>$v) {
        if(preg_match("/_(\d*)#(.*)/", $k, $res)){
            $arrInsrateio[$res[1]][$res[2]]=$v;
        }
    }

    $arrpb=$_SESSION["arrpostbuffer"];
    unset($_SESSION["arrpostbuffer"]);
    reset($arrpb); 
      
    $linhanova = 0;      
    foreach($arrpb as $linha => $arrlinha){
        foreach($arrlinha as $acao => $arracao){
            if($acao == "u"){
                foreach($arracao as $tab => $arrtab){
                    if($tab == 'rateioitemdest') {
                        //buscar o tipo da unidade de origem
                        $sql="select u.idtipounidade,upper(t.tipounidade) as tipounidade,r.idobjeto as idnfitem,d.* 
                                from rateioitemdest d 
                                    join unidade u on(u.idunidade=d.idobjeto and d.tipoobjeto = 'unidade')
                                    join tipounidade t on(u.idtipounidade=t.idtipounidade)
                                    join rateioitem r on(r.idrateioitem=d.idrateioitem)
                                where d.idrateioitemdest=".$arrtab['idrateioitemdest'];   
                        $res = d::b()->query($sql) or die("Falha ao buscar rateiodest Porempresa sql=".$sql); 
                        $row = mysqli_fetch_assoc($res);  
                        $rowv = RateioItemDestController::buscarvalorRateioitemdest($row['idnfitem'],$row['idrateioitemdest']);
                        if(!empty($rowv['idempresa'])){
                            $l = 0;
                            foreach($arrInsrateio as $k => $v){
                                if($row['valor'] == null or $row['valor'] == 0){$row['valor']=100;}
                                $linhanova=$linhanova+1;
                                $destino=explode(',',$v['idempresa']); 

                                $empresaC=RateioItemDestController::buscarEmpresaPorIdEmpresa($destino[0]);
                                if(empty($empresaC['idpessoaform'])){
                                    die("Configurar no cadastro de empresa a pessoa empresa relacionada na empresa para gerar a nota de Crédito");
                                }
                                $existenf=NfController::buscarNfPorIdpessoaIdempresaStatus($empresaC['idpessoaform'],$rowv['idempresa'],'ABERTO');                        
                                
                                if(!empty($existenf)){
                                $idnf= $existenf['idnf'];
                                }else{
                                                            
                                    $tiponf='S';
                                    $idtipounidade = 19;                        
                                    
                                    $qrUnid = "SELECT idunidade FROM unidade WHERE idtipounidade = ".$idtipounidade." and status='ATIVO' AND idempresa = ".$rowv['idempresa'];
                                    $rsUnid = d::b()->query($qrUnid) or die("[prechangerateioitemdest][1]: Erro ao buscar idunidade. SQL: ".$qrUnid);
                                    $rwUnid = mysqli_fetch_assoc($rsUnid);

                                    $idfluxostatus = FluxoController::getIdFluxoStatus('nfentrada', 'INICIO');
                                    $confempresa=RateioItemDestController::buscarConfEmpresaCobranca($rowv['idempresa'],$destino[0]);
                                            
                                    $insunid = new Insert();
                                    $insunid->setTable("nf");				
                                    $insunid->idempresa=$rowv['idempresa'];
                                    $insunid->idpessoa=$empresaC['idpessoaform'];                      
                                    $insunid->tiponf=$tiponf; 
                                    $insunid->idunidade=$rwUnid['idunidade'];
                                    $insunid->status ='ABERTO';
                                    $insunid->idfluxostatus = $idfluxostatus;
                                    $insunid->tipocontapagar ='C';
                                    $insunid->tipoorc='COBRANCA';
                                    $insunid->idformapagamento= $confempresa['idformapagamento'];                       
                                    $idnf=$insunid->save();                                
                                    
                                    $insnfconfpagar = new Insert();
                                    $insnfconfpagar->setTable("nfconfpagar");
                                    $insnfconfpagar->idnf = $idnf;
                                    $idnfconfpagar = $insnfconfpagar->save();     
                                }

                                $valor=($rowv['rateio']*($v['valor']/100));
                            
                                $_SESSION["arrpostbuffer"][$linhanova."x"]['i']["rateioitemdestnf"]["idobjeto"] =  $destino[0];
                                $_SESSION["arrpostbuffer"][$linhanova."x"]['i']["rateioitemdestnf"]["tipoobjeto"] ='empresa';
                                $_SESSION["arrpostbuffer"][$linhanova."x"]['i']["rateioitemdestnf"]["idnf"] =$idnf;
                                $_SESSION["arrpostbuffer"][$linhanova."x"]['i']["rateioitemdestnf"]["valor"] =$valor;
                                $_SESSION["arrpostbuffer"][$linhanova."x"]['i']["rateioitemdestnf"]["rateio"] =$v['valor'];
                                $_SESSION["arrpostbuffer"][$linhanova."x"]['i']["rateioitemdestnf"]["idrateioitemdest"] =$row['idrateioitemdest'];
                            
                                $_SESSION["arrpostbuffer"][$linhanova."y"]['u']["rateioitemdest"]["idrateioitemdest"] =$row['idrateioitemdest'];
                                $_SESSION["arrpostbuffer"][$linhanova."y"]['u']["rateioitemdest"]["status"] ='COBRADO';

                                $l=$l+1;
                            }//foreach($arrInsrateio as $k=>$v){
                        }
                        reset($arrInsrateio); 
                    }
                }
            }else{
                die("Rateio externo e somente para rateios ja existentes");
                
            }
        }
    } 
    montatabdef();

}elseif($restaurartodos=='Y'){//FIM RATEIO EXTERNO       
    $arrpb=$_SESSION["arrpostbuffer"];
    unset($_SESSION["arrpostbuffer"]);
    reset($arrpb); 

    $linhanova = 0;      
    foreach($arrpb as $linha => $arrlinha){
        foreach($arrlinha as $acao => $arracao){
            if($acao == "u"){
                foreach($arracao as $tab => $arrtab){
                    if($tab == 'rateioitemdest') {
                        
                        $sql="select * from rateioitemdest where idrateioitem=".$arrtab['idrateioitem'];   
                        $res=d::b()->query($sql) or die("Falha ao buscar rateioitemdest sql=".$sql); 
                        
                        while($row=mysqli_fetch_assoc($res)){      
                                                        
                            $_SESSION["arrpostbuffer"][$row['idrateioitemdest']]["d"]["rateioitemdest"]["idrateioitemdest"] =$row['idrateioitemdest'];  
                        }  
                    }
                }
            }
        }
    } 
    montatabdef();

}elseif($restaurartodosdest=='Y'){       
    $arrpb=$_SESSION["arrpostbuffer"];
    unset($_SESSION["arrpostbuffer"]);
    reset($arrpb); 
    $arrid = array();
    $linhanova = 0;   
    foreach($arrpb as $linha => $arrlinha){ 
        foreach($arrlinha as $acao => $arracao){ 
            if($acao == "u"){
                foreach($arracao as $tab => $arrtab){     
                    if($tab == 'rateioitemdest' && !empty($arrtab['idrateioitem'])) {
                    
                        $sql="select * from rateioitemdest where idrateioitem=".$arrtab['idrateioitem']." and  idrateioitemdest !=".$arrtab['idrateioitemdest'];   
                        $res=d::b()->query($sql) or die("Falha ao buscar rateioitemdest sql=".$sql); 
                        if(!array_key_exists($arrtab['idrateioitemdest'],$arrid)){    
                            $_SESSION["arrpostbuffer"][$arrtab['idrateioitemdest']]["u"]["rateioitemdest"]["idrateioitemdest"] =$arrtab['idrateioitemdest']; 
                            $_SESSION["arrpostbuffer"][$arrtab['idrateioitemdest']]["u"]["rateioitemdest"]["valor"] =null; 
                            $_SESSION["arrpostbuffer"][$arrtab['idrateioitemdest']]["u"]["rateioitemdest"]["idobjeto"] =null; 
                            $_SESSION["arrpostbuffer"][$arrtab['idrateioitemdest']]["u"]["rateioitemdest"]["tipoobjeto"] =null;     
                            $arrid[$arrtab['idrateioitemdest']]=$arrtab['idrateioitemdest'];
    
                        }
    
                        while($row=mysqli_fetch_assoc($res)){ 
                            if(!array_key_exists($row['idrateioitemdest'],$arrid)){                                                         
                                $_SESSION["arrpostbuffer"][$row['idrateioitemdest']]["d"]["rateioitemdest"]["idrateioitemdest"] =$row['idrateioitemdest']; 
                            }
                            $arrid[$row['idrateioitemdest']]=$row['idrateioitemdest'];     
                            
                        }  
                    }
                }
            }
        }
    } 
    montatabdef();
}

?>