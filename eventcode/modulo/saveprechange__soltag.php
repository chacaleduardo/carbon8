<?
// CONTROLLERS
require_once(__DIR__."/../../form/controllers/tag_controller.php");
require_once(__DIR__."/../../form/controllers/soltag_controller.php");

if(empty($_SESSION['arrpostbuffer']['99']['i']['modulocom']['descricao'])){
    unset($_SESSION['arrpostbuffer']['99']);
}

//se estiver ativando uma tag na solicitação de material, remover tag da sala atual e salvar esse dado na tabela auxiliar [solmatitemobj]

if(isset($_SESSION['arrpostbuffer']['s']['i']['tagsala']['idtag'])){
   
    //pegando a tag
    $idtag =$_SESSION['arrpostbuffer']['s']['i']['tagsala']['idtag'];


    //verificar se ela está em alguma sala
    // $sqlTagSala="select * from tagsala where idtag=".$idtag." order by criadoem desc";
    // $tagSalas = d::b()->query($sqlTagSala) or die("Erro ao buscar salas da tag : ". mysql_error() . "<p>SQL: ".$sqlTagSala);  
    // $qtdTagSala=mysqli_num_rows($tagSalas);
    $tagSala = TagController::buscarTagSalaPorIdTag($idtag);

    if($tagSala)
    {
        // $tagPai=mysqli_fetch_assoc($tagSalas);
        if(is_numeric($idtag))
        {
            // $deleteTagSala="DELETE FROM tagsala WHERE idtag=".$idtag;
            // d::b()->query($deleteTagSala) or die("Erro ao deletar salas da tag : ". mysql_error() . "<p>SQL: ".$deleteTagSala);

            $deletandoTagSalaPorIdTag = TagController::deletarTagSalaPorIdTag($idtag);
        }
        
        //salvar a tagpai antiga na tabela auxiliar para o caso de precisar retornar ela ao estado anterir
        if(isset($_SESSION['arrpostbuffer']['w']['d']['solmatitemobj']['idsolmatitemobj']))
        {
            if($tagSala['idtagpai']!=NULL)
            {
                // $tagpaianterior="select * from solmatitemobj where idsolmatitemobj=".$_SESSION['arrpostbuffer']['w']['d']['solmatitemobj']['idsolmatitemobj'];
                // $tagpaianterior = d::b()->query($tagpaianterior) or die("Erro ao buscar idtagpaianterior : ". mysql_error() . "<p>SQL: ".$tagpaianterior);  
                // $tagpaianterior=mysqli_fetch_assoc($tagpaianterior);
                // $_SESSION['arrpostbuffer']['s']['i']['tagsala']['idtagpai'] = $tagpaianterior['idtagpaianterior'];

                $_SESSION['arrpostbuffer']['s']['i']['tagsala']['idtagpai'] = SolMatController::buscarSolMatItemObjPorChavePrimaria($_SESSION['arrpostbuffer']['w']['d']['solmatitemobj']['idsolmatitemobj'])['idtagpaianterior'];
            }            
        } else
        {
            $_SESSION['arrpostbuffer']['w']['i']['solmatitemobj']['idtagpaianterior']=$tagSala['idtagpai'];
        }
    }
}
if(isset($_SESSION['arrpostbuffer']['s']['i']['tagsala']['idtag']) && isset($_SESSION['arrpostbuffer']['s']['i']['tagsala']['idtagpai']))
    if($_SESSION['arrpostbuffer']['s']['i']['tagsala']['idtagpai']=='undefined'){
        unset($_SESSION['arrpostbuffer']['s']['i']['tagsala']['idtag']);
        unset($_SESSION['arrpostbuffer']['s']['i']['tagsala']['idtagpai']);
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
            $_SESSION['arrpostbuffer'][$i]['i']['solmatitem']['idtag']=$v["idtag"];
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
    // $sql = "select FLOOR(RAND()*1000000000) as idtransacao";
    // $res = d::b()->query($sql);
    // $row=mysqli_fetch_assoc($res);

    $idtransacao = SolTagController::buscarIdTransacao();

    $idunidade=$_SESSION['arrpostbuffer']['x']['i']['lotefracao']['idunidade'];
    $qtdpedida= $_SESSION['arrpostbuffer']['x']['i']['lotefracao']['qtd'];
    $idloteorigem=$_SESSION['arrpostbuffer']['x']['i']['lotefracao']['idlote'];
    $idlotefracaoori=$_SESSION['arrpostbuffer']['x']['i']['lotefracao']['idlotefracaoorigem'];
    $idsolmatitem=$_SESSION['arrpostbuffer']['x']['i']['lotefracao']['idobjetoconsumoespec'];
    //$unfracao=$_SESSION['arrpostbuffer']['x']['i']['lotefracao']['un'];
    
   
    if(!empty($idlotefracaoori))
    {
        $consomeun=traduzid('unidade','idunidade','consomeun',$idunidade);
        $idsgdepartamento=traduzid('unidade','idunidade','idsgdepartamento',$idunidade);
    
        // $s="select * from lotefracao where idlote=".$idloteorigem." and idunidade=".$idunidade;
        // $re = d::b()->query($s) or die("Erro ao buscar se ja existe fracao : ". mysql_error() . "<p>SQL: ".$s);  
        // $qtdr=mysqli_num_rows($re);
        // $rorig=mysqli_fetch_assoc($re);

        $loteFracao = SolTagController::buscarLoteFracaoPorIdLoteEIdUnidade($idloteorigem, $idunidade);

        if($loteFracao)
        {// se ja tiver fracao 
            unset($_SESSION['arrpostbuffer']);
           
            $qtdpedidaori=$qtdpedida;
            $qtdpedidadest=$qtdpedida;
             //ALTERAÇÕES DO PROJETO: CRIAÇÃO DE CHECK IMOBILOZADO NA PRODSERV -> LINK DO EVENTO: sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=467416  -- ALBT 11/06/2021.
            // $s="select u.convestoque,l.valconvori as valconv,l.converteest as convertido,l.vunpadrao,p.consometransf,p.imobilizado,u.idtipounidade
            //     from lotefracao f join lote l on(l.idlote=f.idlote)
            //     join unidade u on(u.idunidade=f.idunidade)
            //     join prodserv p on(p.idprodserv=l.idprodserv)
            // where f.idlotefracao=".$idlotefracaoori;
            // $re = d::b()->query($s) or die("Erro ao buscar informacoes da unidade de origem : ". mysql_error() . "<p>SQL: ".$s);  
            // $rowconv=mysqli_fetch_assoc($re);

            $informacoesDaUnidade = SolTagController::buscarInformacoesDaUnidadeDeOrigemPorIdLoteFracao($idlotefracaoori);
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
         
            // $inslotecons = new Insert();
            // $inslotecons->setTable("lotecons");
            // $inslotecons->idlote=$idloteorigem;
            // $inslotecons->idlotefracao=$idlotefracaoori;
            // $inslotecons->idobjeto=$loteFracao['idlotefracao'];
            // $inslotecons->tipoobjeto='lotefracao';
            // $inslotecons->obs='Lote Fracionado.';
            // $inslotecons->idtransacao=$idtransacao;
            // $inslotecons->idobjetoconsumoespec=$idsolmatitem;
            // $inslotecons->tipoobjetoconsumoespec='solmatitem';
            // $inslotecons->qtdd=$qtdpedidaori;      
            // $inidlotecons=$inslotecons->save();

            $dadosLoteCons = [
                'idempresa' => 'null',
                'idtransacao' => $idtransacao,
                'idlote' => $idloteorigem,
                'tipoobjeto' => 'lotefracao',
                'idobjeto' => $loteFracao['idlotefracao'],
                'tipoobjetoconsumoespec' => 'solmatitem',
                'idobjetoconsumoespec' => $idsolmatitem,
                'idlotefracao' => $idlotefracaoori,
                'qtdd' => $qtdpedidaori,
                'qtdd_exp' => '',
                'qtdc' => 'null',
                'qtdc_exp' => '',
                'qtdsaldo' => 'null',
                'qtdsaldo_exp' => '',
                'qtdsol' => 'null',
                'qtdsol_exp' => '',
                'obs' => 'Lote Fracionado.',
                'status' => '',
                'criadoem' => 'NOW()',
                'criadopor' => $_SESSION['SESSAO']['USUARIO'],
                'alteradoem' => 'NOW()',
                'alteradopor' => $_SESSION['SESSAO']['USUARIO']
            ];

            $inidlotecons = SolTagController::inserirLoteCons($dadosLoteCons);

            //gerar rateio
            if($informacoesDaUnidade['idtipounidade'] == 3)
            {
                if(empty($idunidade)){
                    $idrateioobj=$_idempresa;
                    $objrateio='empresa';
                }else{
                    $idrateioobj=$idunidade;
                    $objrateio='unidade';
                }
    
                // $insrateio = new Insert();
                // $insrateio->setTable("rateio");
                // $insrateio->idobjeto=$idloteorigem;
                // $insrateio->tipoobjeto='lote';
                // $inidrateio=$insrateio->save();
/*
                $dadosRateio = [
                    'idempresa' => '',
                    'idobjeto' => $idloteorigem,
                    'tipoobjeto' => 'lote',
                    'criadopor' => $_SESSION['SESSAO']['USUARIO'],
                    'criadoem' => 'NOW()',
                    'alteradopor' => $_SESSION['SESSAO']['USUARIO'],
                    'alteradoem' => 'NOW()'
                ];

                $inidrateio = SolTagController::inserirRateio($dadosRateio);
*/
                // $insrateioitem = new Insert();
                // $insrateioitem->setTable("rateioitem");
                // $insrateioitem->idrateio=$inidrateio;
                // $insrateioitem->idobjeto=$inidlotecons;
                // $insrateioitem->tipoobjeto='lotecons';
                // $inidrateioitem=$insrateioitem->save();

                $dadosRateioItem = [
                    'idempresa' => 'null',
                    // 'idrateio' => $inidrateio,
                    'idobjeto' => $inidlotecons,
                    'tipoobjeto' => 'lotecons',
                    'criadopor' => $_SESSION['SESSAO']['USUARIO'],
                    'criadoem' => 'NOW()',
                    'alteradopor' => $_SESSION['SESSAO']['USUARIO'],
                    'alteradoem' => 'NOW()'
                ];

                $inidrateioitem = SolTagController::inserirRateioItem($dadosRateioItem);
                
                // $insrateioitemd = new Insert();
                // $insrateioitemd->setTable("rateioitemdest");
                // $insrateioitemd->idrateioitem=$inidrateioitem;
                // $insrateioitemd->valor=100;
                // $insrateioitemd->idobjeto=$idrateioobj;
                // $insrateioitemd->tipoobjeto=$objrateio;          
                // $inidrateioitemdest=$insrateioitemd->save();   

                $dadosRateioItemDest = [
                    'idempresa' => 'null',
                    'idrateioitem' => $inidrateioitem,
                    'idobjeto' => $idrateioobj,
                    'tipoobjeto' => $objrateio,
                    'valor' => 100,
                    'idpessoa' => 'null',
                    'idobjetoinicio' => 'null',
                    'tipoobjetoinicio' => '',
                    'valorinicio' => 'null',
                    'criadopor' => $_SESSION['SESSAO']['USUARIO'],
                    'criadoem' => 'NOW()',
                    'alteradopor' => $_SESSION['SESSAO']['USUARIO'],
                    'alteradoem' => 'NOW()',
                ];

                $inidrateioitemdest = SolTagController::inserirRateioItemDest($dadosRateioItemDest);
            }

            // adiciona um credItio na destino
            $_SESSION['arrpostbuffer']['ufr']['i']['lotecons']['idlote']=$idloteorigem;
            $_SESSION['arrpostbuffer']['ufr']['i']['lotecons']['idlotefracao']=$loteFracao['idlotefracao'];
            $_SESSION['arrpostbuffer']['ufr']['i']['lotecons']['qtdc']=$qtdpedidadest; 
            $_SESSION['arrpostbuffer']['ufr']['i']['lotecons']['idobjeto']=$idlotefracaoori;
            $_SESSION['arrpostbuffer']['ufr']['i']['lotecons']['tipoobjeto']='lotefracao';
            $_SESSION['arrpostbuffer']['ufr']['i']['lotecons']['obs']='crédito via solicitação de materiais';
            $_SESSION['arrpostbuffer']['ufr']['i']['lotecons']['idtransacao']=$idtransacao;
            ///  $_SESSION['arrpostbuffer']['ufr']['i']['lotecons']['idempresa']=$_SESSION["SESSAO"]["IDEMPRESA"];
            //ALTERAÇÕES DO PROJETO: CRIAÇÃO DE CHECK IMOBILOZADO NA PRODSERV -> LINK DO EVENTO: sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=467416  -- ALBT 11/06/2021.
            if(($informacoesDaUnidade["consometransf"]=='Y' or $consomeun=='Y') and $informacoesDaUnidade["imobilizado"] != 'Y'){
                $_SESSION['arrpostbuffer']['ulc2']['i']['lotecons']['idlote']=$idloteorigem;
                $_SESSION['arrpostbuffer']['ulc2']['i']['lotecons']['idlotefracao']=$loteFracao['idlotefracao'];
                $_SESSION['arrpostbuffer']['ulc2']['i']['lotecons']['idobjeto']=$loteFracao['idlotefracao'];
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
            
            // $s="select u.convestoque,l.valconvori as valconv,l.converteest as convertido,l.unlote,l.idprodserv,l.vunpadrao,p.consometransf,p.imobilizado,u.idtipounidade
            //     from lotefracao f join lote l on(l.idlote=f.idlote)
            //     join unidade u on(u.idunidade=f.idunidade)
            //     join prodserv p on(p.idprodserv=l.idprodserv)
            // where f.idlotefracao=".$idlotefracaoori;
            // $re = d::b()->query($s) or die("Erro ao buscar informacoes da unidade de origem : ". mysql_error() . "<p>SQL: ".$s);  
            // $rowconv=mysqli_fetch_assoc($re);

            $informacoesDaUnidade = SolTagController::buscarInformacoesDaUnidadeDeOrigemPorIdLoteFracao($idlotefracaoori);
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
            
            // $inslotefracao = new Insert();
            // $inslotefracao->setTable("lotefracao");
            // $inslotefracao->idunidade=$idunidade;
            // $inslotefracao->qtd=$qtdpedidadest;
            // $inslotefracao->qtdini=$qtdpedidadest;
            // $inslotefracao->idempresa=$_idempresa;
            // $inslotefracao->idlote=$idloteorigem;
            // $inslotefracao->idtransacao=$idtransacao;
            // $inslotefracao->idlotefracaoorigem=$idlotefracaoori;      
            // $_idlotefracao=$inslotefracao->save();

            $dadosLoteFracao = [
                'idempresa' => $_idempresa,
                'idlote' => $idloteorigem,
                'idunidade' => $idunidade,
                'idobjeto' => 'null',
                'tipoobjeto' => '',
                'idlotefracaoorigem' => $idlotefracaoori,
                'qtd' => $qtdpedidadest,
                'qtd_exp' => '',
                'qtdini' => $qtdpedidadest,
                'qtdini_exp' => '',
                'idtransacao' => $idtransacao,
                'status' => '',
                'conferido' => '',
                'criadopor' => $_SESSION['SESSAO']['USUARIO'],
                'criadoem' => 'NOW()',
                'alteradopor' => $_SESSION['SESSAO']['USUARIO'],
                'alteradoem' => 'NOW()',
                'idlotebkp' => 'null'
            ];

            $_idlotefracao = SolTagController::inserirLoteFracao($dadosLoteFracao);

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
            // $inslotecons = new Insert();
            // $inslotecons->setTable("lotecons");
            // $inslotecons->idlote=$idloteorigem;
            // $inslotecons->idlotefracao=$idlotefracaoori;
            // $inslotecons->idobjeto=$_idlotefracao;
            // $inslotecons->tipoobjeto='lotefracao';
            // $inslotecons->obs='Transferência na solicitacão de materiais';
            // $inslotecons->qtdd=$qtdpedidaori; 
            // $inslotecons->idtransacao=$idtransacao;
            // $inslotecons->idobjetoconsumoespec=$idsolmatitem;
            // $inslotecons->tipoobjetoconsumoespec='solmatitem';     
            // $inidlotecons=$inslotecons->save();

            $dadosLoteCons = [
                'idempresa' => 'null',
                'idtransacao' => $idtransacao,
                'idlote' => $idloteorigem,
                'tipoobjeto' => 'lotefracao',
                'idobjeto' => $_idlotefracao,
                'tipoobjetoconsumoespec' => 'solmatitem',
                'idobjetoconsumoespec' => $idsolmatitem,
                'idlotefracao' => $idlotefracaoori,
                'qtdd' => $qtdpedidaori,
                'qtdd_exp' => '',
                'qtdc' => 'null',
                'qtdc_exp' => '',
                'qtdsaldo' => 'null',
                'qtdsaldo_exp' => '',
                'qtdsol' => 'null',
                'qtdsol_exp' => '',
                'obs' => 'Transferência na solicitacão de materiais',
                'status' => '',
                'criadoem' => 'NOW()',
                'criadopor' => $_SESSION['SESSAO']['USUARIO'],
                'alteradoem' => 'NOW()',
                'alteradopor' => $_SESSION['SESSAO']['USUARIO']
            ];

            $inidlotecons = SolTagController::inserirLoteCons($dadosLoteCons);

            //gerar rateio
            if($informacoesDaUnidade['idtipounidade']==3){
                if(empty($idunidade)){
                    $idrateioobj=$_idempresa;
                    $objrateio='empresa';
                }else{
                    $idrateioobj=$idunidade;
                    $objrateio='unidade';
                }               
    

                // $insrateio = new Insert();
                // $insrateio->setTable("rateio");
                // $insrateio->idobjeto=$idloteorigem;
                // $insrateio->tipoobjeto='lote';
                // $inidrateio=$insrateio->save();
/*
                $dadosRateio = [
                    'idempresa' => '',
                    'idobjeto' => $idloteorigem,
                    'tipoobjeto' => 'lote',
                    'criadopor' => $_SESSION['SESSAO']['USUARIO'],
                    'criadoem' => 'NOW()',
                    'alteradopor' => $_SESSION['SESSAO']['USUARIO'],
                    'alteradoem' => 'NOW()'
                ];

                $inidrateio = SolTagController::inserirRateio($dadosRateio);
    */
                // $insrateioitem = new Insert();
                // $insrateioitem->setTable("rateioitem");
                // $insrateioitem->idrateio=$inidrateio;
                // $insrateioitem->idobjeto=$inidlotecons;
                // $insrateioitem->tipoobjeto='lotecons';
                // $inidrateioitem=$insrateioitem->save();

                $dadosRateioItem = [
                    'idempresa' => 'null',
                   // 'idrateio' => $inidrateio,
                    'idobjeto' => $inidlotecons,
                    'tipoobjeto' => 'lotecons',
                    'criadopor' => $_SESSION['SESSAO']['USUARIO'],
                    'criadoem' => 'NOW()',
                    'alteradopor' => $_SESSION['SESSAO']['USUARIO'],
                    'alteradoem' => 'NOW()'
                ];

                $inidrateioitem = SolTagController::inserirRateioItem($dadosRateioItem);
                
                // $insrateioitemd = new Insert();
                // $insrateioitemd->setTable("rateioitemdest");
                // $insrateioitemd->idrateioitem=$inidrateioitem;
                // $insrateioitemd->valor=100;
                // $insrateioitemd->idobjeto=$idrateioobj;
                // $insrateioitemd->tipoobjeto=$objrateio;          
                // $inidrateioitemdest=$insrateioitemd->save();

                $dadosRateioItemDest = [
                    'idempresa' => 'null',
                    'idrateioitem' => $inidrateioitem,
                    'idobjeto' => $idrateioobj,
                    'tipoobjeto' => $objrateio,
                    'valor' => 100,
                    'idpessoa' => 'null',
                    'idobjetoinicio' => 'null',
                    'tipoobjetoinicio' => '',
                    'valorinicio' => 'null',
                    'criadopor' => $_SESSION['SESSAO']['USUARIO'],
                    'criadoem' => 'NOW()',
                    'alteradopor' => $_SESSION['SESSAO']['USUARIO'],
                    'alteradoem' => 'NOW()',
                ];

                $inidrateioitemdest = SolTagController::inserirRateioItemDest($dadosRateioItemDest);
            }
  
            // não adiciona um credItio no destino pois e inserido o valor na quantidade inicial da fracao
      
            
            if(($informacoesDaUnidade["consometransf"]=='Y' or $consomeun=='Y') and $informacoesDaUnidade["imobilizado"] != 'Y'){
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