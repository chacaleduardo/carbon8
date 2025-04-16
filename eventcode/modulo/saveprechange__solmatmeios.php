<?
if(empty($_SESSION['arrpostbuffer']['99']['i']['modulocom']['descricao'])){
    unset($_SESSION['arrpostbuffer']['99']);
}

//se estiver ativando uma tag na solicitação de material, remover tag da sala atual e salvar esse dado na tabela auxiliar [solmatitemobj]
if(isset($_SESSION['arrpostbuffer']['s']['i']['tagsala']['idtag'])){
    
    //pegando a tag
    $idtag =$_SESSION['arrpostbuffer']['s']['i']['tagsala']['idtag'];
    //verificar se ela está em alguma sala
    $sqlTagSala="select * from tagsala where idtag=".$idtag."order by criadoem desc";
    $tagSalas = d::b()->query($sqlTagSalas) or die("Erro ao buscar salas da tag : ". mysql_error() . "<p>SQL: ".$sqlTagSala);  
    $qtdTagSala=mysqli_num_rows($re);
    if($qtdTagSala>0){
        //salvar a tagpai antiga na tabela auxiliar para o caso de precisar retornar ela ao estado anterir
        $tagPai=mysqli_fetch_assoc($re);
        $_SESSION['arrpostbuffer']['w']['i']['solmatitemobj']['idtagpaianterior'] = $tagPai['idtagpai'];
        $deleteTagSala="DELETE FROM tagsala WHERE idtag=".$idtag;
        d::b()->query($deleteTagSala) or die("Erro ao deletar salas da tag : ". mysql_error() . "<p>SQL: ".$deleteTagSala);  
    }
    if($_SESSION['arrpostbuffer']['s']['i']['tagsala']['idtagpai']==''){
        $_SESSION['arrpostbuffer']['s']['i']['tagsala']['idtagpai']==NULL;
    }
}

$_arrtabdef = retarraytabdef('solmatitem');
/*
 * INSERIR ITENS NA TELA DE SOLICITAÇÂO DE MATERIAS
 */
function debug($inDebug){
    $sm = "call debug('solmat_php', '#".$inDebug."')";
        //echo $sm.'<br>';
    d::b()->query($sm);
}

if(!empty($_POST["_qtdmaximo_"]) and !empty($_POST["_qtdcmaximo_"])){
    $qtdcmaximo = floatval($_POST["_qtdcmaximo_"]);
    $qtdmaximo =  floatval($_POST["_qtdmaximo_"]);
    $qtd =  floatval($_SESSION["arrpostbuffer"]["x"]["i"]["lotefracao"]["qtd"]);
    if($qtd > $qtdmaximo ){
        cbSetPostHeader("0","erro");
        die("O valor informado é maior que o valor disponível no lote.");
    }
    if( $qtd <= 0){
        cbSetPostHeader("0","erro");
        die("O valor informado é inválido.");
    }

}

$arrsolmatitem=array();
foreach($_POST as $k=>$v) {
	if(preg_match("/_x(\d)_u_solmatitem_(.*)/", $k,$res)){
        $arrsolmatitem[$res[1]][$res[2]]=$v;
	}
}

if(!empty($arrsolmatitem)){
   // LOOP NAS QTDC DA TELA
   foreach($arrsolmatitem as $k=>$v){
      // print_r($v);die();

        $qtdc=$v['qtdc'];
        if(empty($qtdc)){die("preencha a quantidade(Qtd)");}  
   }

}

$arrInsProd=array();
foreach($_POST as $k=>$v) {
	if(preg_match("/_(\d*)#(.*)/", $k, $res)){
		$arrInsProd[$res[1]][$res[2]]=$v;
	}
}
//var_dump($arrInsProd);
//die;

if(!empty($arrInsProd)){

   
   $i=99977;
   // LOOP NOS ITENS DO + DA TELA
   foreach($arrInsProd as $k=>$v){
      // print_r($v);die();
       $i=$i+1;

        $idprodserv=$v['idprodserv'];
	    $prodservdescr=$v['prodservdescr'];
        if(empty($_SESSION["arrpostbuffer"]["1"]["u"]["solmat"]["idsolmat"])){
            $idsolmat = $_SESSION["_pkid"];
        } else {
            $idsolmat = $_SESSION["arrpostbuffer"]["1"]["u"]["solmat"]["idsolmat"];
        }

        if(!empty($idprodserv) OR !empty($prodservdescr) ){


            if(empty($idsolmat)){die("[saveprechange_solmat]-Não foi possivel identificar o ID da solicitacao!!!");}   

                // montar o item para insert
                $_SESSION['arrpostbuffer'][$i]['i']['solmatitem']['qtdc']=$v["quantidade"];
                $_SESSION['arrpostbuffer'][$i]['i']['solmatitem']['obs']=$v["obs"];
                $_SESSION['arrpostbuffer'][$i]['i']['solmatitem']['idempresa']=$_SESSION["SESSAO"]["IDEMPRESA"];
                if(!empty($v["idprodserv"])){
                    $_SESSION['arrpostbuffer'][$i]['i']['solmatitem']['idprodserv']=$v["idprodserv"];
                  
                }else{
                    $_SESSION['arrpostbuffer'][$i]['i']['solmatitem']['descr']=$v["prodservdescr"];
                }
                $_SESSION['arrpostbuffer'][$i]['i']['solmatitem']['idsolmat']=$idsolmat;
    
        }
   } //foreach($arrInsProd as $k=>$v){
  
}//if(!empty($arrInsProd)){
// tira a session dos comentarios
    if(empty( $_SESSION['arrpostbuffer']['xa']['i']['solmaticoment']['comentario']) ){
        unset($_SESSION['arrpostbuffer']['xa']['i']['solmaticoment']['comentario']);
    }
    $sql = "select FLOOR(RAND()*1000000000) as idtransacao";
    $res = d::b()->query($sql);
    $row=mysqli_fetch_assoc($res);
    $idtransacao= $row['idtransacao'];
    $idunidade=$_SESSION['arrpostbuffer']['x']['i']['lotefracao']['idunidade'];
    $qtdpedida= $_SESSION['arrpostbuffer']['x']['i']['lotefracao']['qtd'];
    $idloteorigem=$_SESSION['arrpostbuffer']['x']['i']['lotefracao']['idlote'];
    $idlotefracaoori=$_SESSION['arrpostbuffer']['x']['i']['lotefracao']['idlotefracaoorigem'];
    $idsolmatitem=$_SESSION['arrpostbuffer']['x']['i']['lotefracao']['idobjeto'];
    //$unfracao=$_SESSION['arrpostbuffer']['x']['i']['lotefracao']['un'];
    
   
    if(!empty($idlotefracaoori))
    {  
        $consomeun=traduzid('unidade','idunidade','consomeun',$idunidade);
    
        $s="select * from lotefracao where idlote=".$idloteorigem." and idunidade=".$idunidade;
        $re = d::b()->query($s) or die("Erro ao buscar se ja existe fracao : ". mysql_error() . "<p>SQL: ".$s);  
        $qtdr=mysqli_num_rows($re);
        $rorig=mysqli_fetch_assoc($re);
        if($qtdr>0){// se ja tiver fracao 
            unset($_SESSION['arrpostbuffer']);
           
            $qtdpedidaori=$qtdpedida;
            $qtdpedidadest=$qtdpedida;
             //ALTERAÇÕES DO PROJETO: CRIAÇÃO DE CHECK IMOBILOZADO NA PRODSERV -> LINK DO EVENTO: sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=467416  -- ALBT 11/06/2021.
            $s="select u.convestoque,l.valconvori as valconv,l.converteest as convertido,l.vunpadrao,p.consometransf,p.imobilizado,u.idtipounidade
                from lotefracao f join lote l on(l.idlote=f.idlote)
                join unidade u on(u.idunidade=f.idunidade)
                join prodserv p on(p.idprodserv=l.idprodserv)
            where f.idlotefracao=".$idlotefracaoori;
            $re = d::b()->query($s) or die("Erro ao buscar informacoes da unidade de origem : ". mysql_error() . "<p>SQL: ".$s);  
            $rowconv=mysqli_fetch_assoc($re);
    /*
            $convestdest=traduzid("unidade","idunidade","convestoque",$idunidade);
            if($rowconv["valconv"]>1 and $rowconv["convestoque"]=='N' and $rowconv['convertido']=='Y'){
                          
                $qtdpedidadest=$qtdpedida; 
                $qtdpedidaori=$qtdpedida;
                   
                if($rowconv['vunpadrao']=='N'){
                    $qtdpedidadest= $qtdpedida*$rowconv["valconv"]; 
                    $qtdpedidaori= $qtdpedida*$rowconv["valconv"]; 
                    
                }
               
            } 
            */
            
             // adiciona um debito na origem
             /*
            $_SESSION['arrpostbuffer']['ulc']['i']['lotecons']['idlote']=$idloteorigem;
            $_SESSION['arrpostbuffer']['ulc']['i']['lotecons']['idlotefracao']=$idlotefracaoori;
            $_SESSION['arrpostbuffer']['ulc']['i']['lotecons']['idobjeto']=$rorig['idlotefracao'];
            $_SESSION['arrpostbuffer']['ulc']['i']['lotecons']['tipoobjeto']='lotefracao';
            $_SESSION['arrpostbuffer']['ulc']['i']['lotecons']['tipoobjeto']='lotefracao';
            $_SESSION['arrpostbuffer']['ulc']['i']['lotecons']['idobjetoconsumoespec']=$idsolmatitem;
            $_SESSION['arrpostbuffer']['ulc']['i']['lotecons']['tipoobjetoconsumoespec']='solmatitem';
            $_SESSION['arrpostbuffer']['ulc']['i']['lotecons']['obs']='Lote Fracionado.'; 
            $_SESSION['arrpostbuffer']['ulc']['i']['lotecons']['qtdd']=$qtdpedidaori; 
            */
         
            $inslotecons = new Insert();
            $inslotecons->setTable("lotecons");
            $inslotecons->idlote=$idloteorigem;
            $inslotecons->idlotefracao=$idlotefracaoori;
            $inslotecons->idobjeto=$rorig['idlotefracao'];
            $inslotecons->tipoobjeto='lotefracao';
            $inslotecons->obs='Lote Fracionado.';
            $inslotecons->idtransacao=$idtransacao;
            $inslotecons->idobjetoconsumoespec=$idsolmatitem;
            $inslotecons->tipoobjetoconsumoespec='solmatitem';
            $inslotecons->qtdd=$qtdpedidaori;      
            $inidlotecons=$inslotecons->save();
            //gerar rateio
            if($rowconv['idtipounidade']==3){
                if(empty($idunidade)){
                    $idrateioobj=$_idempresa;
                    $objrateio='empresa';
                }else{
                    $idrateioobj=$idunidade;
                    $objrateio='unidade';
                }
    /*
                $insrateio = new Insert();
                $insrateio->setTable("rateio");
                $insrateio->idobjeto=$idloteorigem;
                $insrateio->tipoobjeto='lote';
                $inidrateio=$insrateio->save();
    */
                $insrateioitem = new Insert();
                $insrateioitem->setTable("rateioitem");
                //$insrateioitem->idrateio=$inidrateio;
                $insrateioitem->idobjeto=$inidlotecons;
                $insrateioitem->tipoobjeto='lotecons';
                $inidrateioitem=$insrateioitem->save();
                
                $insrateioitemd = new Insert();
                $insrateioitemd->setTable("rateioitemdest");
                $insrateioitemd->idrateioitem=$inidrateioitem;
                $insrateioitemd->valor=100;
                $insrateioitemd->idobjeto=$idrateioobj;
                $insrateioitemd->tipoobjeto=$objrateio;          
                $inidrateioitemdest=$insrateioitemd->save();   
            }

            // adiciona um credItio na destino
            $_SESSION['arrpostbuffer']['ufr']['i']['lotecons']['idlote']=$idloteorigem;
            $_SESSION['arrpostbuffer']['ufr']['i']['lotecons']['idlotefracao']=$rorig['idlotefracao'];
            $_SESSION['arrpostbuffer']['ufr']['i']['lotecons']['qtdc']=$qtdpedidadest; 
            $_SESSION['arrpostbuffer']['ufr']['i']['lotecons']['idobjeto']=$idlotefracaoori;
            $_SESSION['arrpostbuffer']['ufr']['i']['lotecons']['tipoobjeto']='lotefracao';
            $_SESSION['arrpostbuffer']['ufr']['i']['lotecons']['obs']='crédito via solicitação de materiais';
            $_SESSION['arrpostbuffer']['ufr']['i']['lotecons']['idtransacao']=$idtransacao;
            ///  $_SESSION['arrpostbuffer']['ufr']['i']['lotecons']['idempresa']=$_SESSION["SESSAO"]["IDEMPRESA"];
            //ALTERAÇÕES DO PROJETO: CRIAÇÃO DE CHECK IMOBILOZADO NA PRODSERV -> LINK DO EVENTO: sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=467416  -- ALBT 11/06/2021.
            if(($rowconv["consometransf"]=='Y' or $consomeun=='Y') and $rowconv["imobilizado"] != 'Y'){
                $_SESSION['arrpostbuffer']['ulc2']['i']['lotecons']['idlote']=$idloteorigem;
                $_SESSION['arrpostbuffer']['ulc2']['i']['lotecons']['idlotefracao']=$rorig['idlotefracao'];
                $_SESSION['arrpostbuffer']['ulc2']['i']['lotecons']['idobjeto']=$rorig['idlotefracao'];
                $_SESSION['arrpostbuffer']['ulc2']['i']['lotecons']['tipoobjeto']='lotefracao';
                $_SESSION['arrpostbuffer']['ulc2']['i']['lotecons']['idtransacao']=$idtransacao;
                //$_SESSION['arrpostbuffer']['ulc2']['i']['lotecons']['idobjetoconsumoespec']=$idsolmatitem;
                //$_SESSION['arrpostbuffer']['ulc2']['i']['lotecons']['tipoobjetoconsumoespec']='solmatitem';
                $_SESSION['arrpostbuffer']['ulc2']['i']['lotecons']['obs']='Lote consumido na transferência da solicitacão de materiais.';
                $_SESSION['arrpostbuffer']['ulc2']['i']['lotecons']['qtdd']=$qtdpedidadest;
        //        $_SESSION['arrpostbuffer']['ulc2']['i']['lotecons']['idempresa']=$_SESSION["SESSAO"]["IDEMPRESA"];
            }
            
            debug(1);
          montatabdef();
          debug(2);
        }else{//se não tiver fracao
            $qtdpedidaori=$qtdpedida;
            $qtdpedidadest=$qtdpedida;
            unset($_SESSION['arrpostbuffer']);                       
            
            $s="select u.convestoque,l.valconvori as valconv,l.converteest as convertido,l.unlote,l.idprodserv,l.vunpadrao,p.consometransf,p.imobilizado,u.idtipounidade
                from lotefracao f join lote l on(l.idlote=f.idlote)
                join unidade u on(u.idunidade=f.idunidade)
                join prodserv p on(p.idprodserv=l.idprodserv)
            where f.idlotefracao=".$idlotefracaoori;
            $re = d::b()->query($s) or die("Erro ao buscar informacoes da unidade de origem : ". mysql_error() . "<p>SQL: ".$s);  
            $rowconv=mysqli_fetch_assoc($re);
    /*
            $convestdest=traduzid("unidade","idunidade","convestoque",$idunidade);
            if($rowconv["valconv"]>1  and $rowconv["convestoque"]=='N' and $rowconv["convertido"]=='Y'){
               
                $qtdpedidadest=$qtdpedida; 
                $qtdpedidaori=$qtdpedida;
               
                if($rowconv['vunpadrao']=='N'){
                    $qtdpedidadest=  $qtdpedida*$rowconv["valconv"]; 
                    $qtdpedidaori= $qtdpedida*$rowconv["valconv"];  
                    
                }
            }
            */
            debug(3);
            $_idempresa=traduzid('unidade','idunidade','idempresa',$idunidade);
            
            $inslotefracao = new Insert();
            $inslotefracao->setTable("lotefracao");
            $inslotefracao->idunidade=$idunidade;
            $inslotefracao->qtd=$qtdpedidadest;
            $inslotefracao->qtdini=$qtdpedidadest;
            $inslotefracao->idempresa=$_idempresa;
            $inslotefracao->idlote=$idloteorigem;
            $inslotefracao->idtransacao=$idtransacao;
            $inslotefracao->idlotefracaoorigem=$idlotefracaoori;      
            $_idlotefracao=$inslotefracao->save();
            debug(4);
            $_SESSION['arrpostbuffer']['ulc']['u']['lotefracao']['idlotefracaoorigem']=$idlotefracaoori;
            $_SESSION['arrpostbuffer']['ulc']['u']['lotefracao']['idlotefracao']= $_idlotefracao;
         /*   
            $_SESSION['arrpostbuffer']['ulc']['i']['lotecons']['idlote']=$idloteorigem;
            $_SESSION['arrpostbuffer']['ulc']['i']['lotecons']['idlotefracao']=$idlotefracaoori;
            $_SESSION['arrpostbuffer']['ulc']['i']['lotecons']['idobjeto']=$_idlotefracao;
            $_SESSION['arrpostbuffer']['ulc']['i']['lotecons']['tipoobjeto']='lotefracao';
            $_SESSION['arrpostbuffer']['ulc']['i']['lotecons']['obs']='Lote Fracionado solicitacão materiais';
            $_SESSION['arrpostbuffer']['ulc']['i']['lotecons']['idobjetoconsumoespec']=$idsolmatitem;
            $_SESSION['arrpostbuffer']['ulc']['i']['lotecons']['tipoobjetoconsumoespec']='solmatitem';
            $_SESSION['arrpostbuffer']['ulc']['i']['lotecons']['qtdd']=$qtdpedidaori;
      */
            $inslotecons = new Insert();
            $inslotecons->setTable("lotecons");
            $inslotecons->idlote=$idloteorigem;
            $inslotecons->idlotefracao=$idlotefracaoori;
            $inslotecons->idobjeto=$_idlotefracao;
            $inslotecons->tipoobjeto='lotefracao';
            $inslotecons->obs='Transferência na solicitacão de materiais';
            $inslotecons->qtdd=$qtdpedidaori; 
            $inslotecons->idtransacao=$idtransacao;
            $inslotecons->idobjetoconsumoespec=$idsolmatitem;
            $inslotecons->tipoobjetoconsumoespec='solmatitem';     
            $inidlotecons=$inslotecons->save();
            //gerar rateio
            if($rowconv['idtipounidade']==3){
                if(empty($idunidade)){
                    $idrateioobj=$_idempresa;
                    $objrateio='empresa';
                }else{
                    $idrateioobj=$idunidade;
                    $objrateio='unidade';
                }               
    
/*
                $insrateio = new Insert();
                $insrateio->setTable("rateio");
                $insrateio->idobjeto=$idloteorigem;
                $insrateio->tipoobjeto='lote';
                $inidrateio=$insrateio->save();
  */  
                $insrateioitem = new Insert();
                $insrateioitem->setTable("rateioitem");
                //$insrateioitem->idrateio=$inidrateio;
                $insrateioitem->idobjeto=$inidlotecons;
                $insrateioitem->tipoobjeto='lotecons';
                $inidrateioitem=$insrateioitem->save();
                
                $insrateioitemd = new Insert();
                $insrateioitemd->setTable("rateioitemdest");
                $insrateioitemd->idrateioitem=$inidrateioitem;
                $insrateioitemd->valor=100;
                $insrateioitemd->idobjeto=$idrateioobj;
                $insrateioitemd->tipoobjeto=$objrateio;          
                $inidrateioitemdest=$insrateioitemd->save();


            }
  
            // não adiciona um credItio no destino pois e inserido o valor na quantidade inicial da fracao
      
            
            if(($rowconv["consometransf"]=='Y' or $consomeun=='Y') and $rowconv["imobilizado"] != 'Y'){
                $_SESSION['arrpostbuffer']['ulc2']['i']['lotecons']['idlote']=$idloteorigem;
                $_SESSION['arrpostbuffer']['ulc2']['i']['lotecons']['idlotefracao']=$_idlotefracao;
                $_SESSION['arrpostbuffer']['ulc2']['i']['lotecons']['tipoobjeto']='lotefracao';
                $_SESSION['arrpostbuffer']['ulc2']['i']['lotecons']['idobjeto']=$_idlotefracao;
                $_SESSION['arrpostbuffer']['ulc2']['i']['lotecons']['obs']='Lote consumido na transferência da solicitacão de materiais';
               // $_SESSION['arrpostbuffer']['ulc2']['i']['lotecons']['idobjetoconsumoespec']=$idsolmatitem;
              //  $_SESSION['arrpostbuffer']['ulc2']['i']['lotecons']['tipoobjetoconsumoespec']='solmatitem';
                $_SESSION['arrpostbuffer']['ulc2']['i']['lotecons']['qtdd']=$qtdpedidaori;
  //              $_SESSION['arrpostbuffer']['ulc2']['i']['lotecons'][' idempresa']=$_SESSION["SESSAO"]["IDEMPRESA"];
            }
            debug(5);
             montatabdef();
             debug(6);
        }
        
    }

?>