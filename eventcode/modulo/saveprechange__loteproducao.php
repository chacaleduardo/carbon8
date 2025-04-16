<?
require_once(__DIR__."/../../form/controllers/fluxo_controller.php");
require_once("../api/nf/index.php");
 

//print_r( $_SESSION['arrpostbuffer']); die;
//abre variavel com a acao que veio da tela
$iu = $_SESSION['arrpostbuffer']['1']['u']['lote']['idlote'] ? 'u' : 'i';

$_idprodserv = $_SESSION['arrpostbuffer']['1']['i']['lote']['idprodserv'];

$status = $_SESSION['arrpostbuffer']['1']['u']['lote']['status'];

$idassinadopor = $_SESSION['arrpostbuffer']['x']['u']['lote']['idassinadopor'];

$sql = "select FLOOR(RAND()*1000000000) as idtransacao";
    $res = d::b()->query($sql);
    $row=mysqli_fetch_assoc($res);
    $idtransacao= $row['idtransacao'];
    // iserir transacao para adicao ou retirada
if($_SESSION['arrpostbuffer']['x']['i']['lotecons']['idlote']){
    $_SESSION['arrpostbuffer']['x']['i']['lotecons']['idtransacao'] = $idtransacao;
}
if(!empty($idassinadopor)){
    $_SESSION['arrpostbuffer']['x']['u']['lote']['dataanalise']=date("d-m-Y");
}
if(!empty($_SESSION['arrpostbuffer']['999']['i']['lotecons']['qtdd'])){
    $vlor = $_SESSION['arrpostbuffer']['999']['i']['lotecons']['qtdd'];
}else if(!empty($_SESSION['arrpostbuffer']['9999']['i']['lotefracao']['qtd'])){
    $vlor = $_SESSION['arrpostbuffer']['9999']['i']['lotefracao']['qtd'];
}else{
    $vlor = $_SESSION['arrpostbuffer']['9999']['i']['lotecons']['qtdd'];
}
$dispqdt = $_POST['_loteconsqtdd_'];
    $nund = explode("d", $dispqdt);
    $nune = explode("e", $dispqdt);
    $nundv = explode("d", $vlor);
    $nunev = explode("e", $vlor);
    if(!empty($nund[1])){
        $vlfim= $nund[0];
        $vlfim1 ="d".$nund[1];
    }else{
        $vlfim= $nune[0];
        $vlfim1 ="e".$nune[1];
    }
if(!empty($vlfim1)){
    if($vlor > $vlfim){
        die('Qtd solicitada maior que a disponível');
    }
}else{
    if(!empty($nundv[1])){
        $vlor = $nundv[0];
    }else{
        $vlor = $nunev[0];
    }
    if($vlor > $dispqdt){
        die('Qtd solicitada maior que a disponível');
    }
}


//print_r($_SESSION['arrpostbuffer']); die;

//se for um insert, o tipo de meio tiver sido informado e o lote estiver vazio
if($iu == "i"
	and (!empty($_idprodserv) 
	and empty($_SESSION["arrpostbuffer"]["1"]["i"]["lote"]["partida"]))){
    
        $idunidade =$_SESSION['arrpostbuffer']['1']['i']['lote']['idunidade'];
        $idtipounidade= traduzid('unidade', 'idunidade', 'idtipounidade', $idunidade);
        $un= traduzid('prodserv', 'idprodserv', 'un', $_idprodserv);
        
        /*if($idtipounidade==13){//Tipo unidade P&D sequencia diferente
            $tipo='loteao';
        }elseif($idtipounidade==16){
            $tipo='loteaoe';
        }else{*/
            $tipo='lote';
        //}

	$_arrlote = geraLote($_idprodserv,$tipo);
	$_numlote = $_arrlote[0].$_arrlote[1];

    $modoprod = traduzid("prodserv","idprodserv","modopart",$_idprodserv);
    if($modoprod == 'PP'){
        $_numlote='PP '.$_numlote;            
        $part_piloto = 'Y';            
    }else{
        $part_piloto = 'N';
    }
	//Enviar o campo para a pagina de submit
	$_SESSION["arrpostbuffer"]["1"]["i"]["lote"]["piloto"] = $part_piloto;
    $_SESSION["arrpostbuffer"]["1"]["i"]["lote"]["partida"] = $_numlote;
    $_SESSION["arrpostbuffer"]["1"]["i"]["lote"]["idpartida"] = $_numlote;
	$_SESSION["arrpostbuffer"]["1"]["i"]["lote"]["spartida"] = $_arrlote[0];
	$_SESSION["arrpostbuffer"]["1"]["i"]["lote"]["npartida"] = $_arrlote[1]; 
    $_SESSION["arrpostbuffer"]["1"]["i"]["lote"]["infprod"] = $_arrlote[2];   
        $_SESSION["arrpostbuffer"]["1"]["i"]["lote"]["unpadrao"] = $un; 
        $_SESSION["arrpostbuffer"]["1"]["i"]["lote"]["unlote"] = $un; 
        $_SESSION["arrpostbuffer"]["1"]["i"]["lote"]["converteest"] = 'N'; 
        $_SESSION["arrpostbuffer"]["1"]["i"]["lote"]["valconvori"] = 1;
        

	//Atribuir o valor para retorno por session['post'] ah pagina anterior.
	$_SESSION["post"]["_1_u_lote_partida"] = $_numlote;
	
	//d::b()->query("COMMIT") or die("prechange: Falha ao efetuar COMMIT [sequence]: ".mysql_error());

}elseif(is_array($_SESSION["arrpostbuffer"]["1"]["u"]["lote"]) 
		and empty($_SESSION["arrpostbuffer"]["1"]["u"]["lote"]["partida"])){
	echo("seggeraloe: idprodserv nao informado ou falha na atribuicao da sequence ao inserir o partida!");
	die;
}

//migracao para P&D Escalonado e produção

$idloteorigem=$_SESSION['arrpostbuffer']['xm']['i']['lotecons']['idlote'];
$idlotefracaoorigem=$_SESSION['arrpostbuffer']['xm']['i']['lotecons']['idlotefracao'];
$qtdpedida= $_SESSION['arrpostbuffer']['xm']['i']['lotecons']['qtdd'];
$idlotedest=$_SESSION['arrpostbuffer']['xm']['i']['lotecons']['idobjeto'];



if(!empty($idloteorigem) and !empty($qtdpedida)){
    $qtddigitada=$qtdpedida;

    //print_r($_SESSION['arrpostbuffer']); die;
    unset($_SESSION['arrpostbuffer']);
  
    if (strpos(strtolower($qtdpedida),"d") or strpos(strtolower($qtdpedida),"e")) {
        //Efetua registro da representação científica/customizada na coluna associada

        if (strpos(strtolower($qtdpedida),"e")) {
            $qtdpedida_exp= str_replace(",",".",$qtdpedida);
            $arrvlr=explode("e",$qtdpedida);
            $qtdDdigitado=tratadouble($arrvlr[0]);
            //maf140619: anteriormente esta parte estava devolvendo uma string a ser executada no MySQL. Ex: "576000*pow(10,7)" mas isso passava pela valida$
            $qtdpedida=tratadouble($arrvlr[0])*pow(10,tratadouble($arrvlr[1]));
            $de='e';
            //$_SESSION["arrpostbuffer"][$row][$act][$tbl][$col]=tratanumero($arrvlr[0]) * pow(10,tratanumero($arrvlr[1]));
            //maf190819: nao verificar numerico neste ponto} elseif(strpos(strtolower($vlr),"d") && is_numeric(str_replace("d", "", $vlr))){
        } elseif(strpos(strtolower($qtdpedida),"d")){
            $qtdpedida_exp=$qtdpedida;//armazena valor original
            $arrvlr=explode("d",$qtdpedida);
            $qtdDdigitado=tratadouble($arrvlr[0]);
            $qtdpedida=tratadouble($arrvlr[0])*tratadouble($arrvlr[1]);//Multipicacao direta da diluicao
            $de='d';
        } elseif(empty($_SESSION["arrpostbuffer"][$row][$act][$tbl][$col."_exp"])){
            //Se estiver configurada alguma coluna associada, mas não vier valor, limpar coluna
            $qtdpedida_exp="";
            $qtdDdigitado='0';
            $de='';
        }

    }else{
        $qtdDdigitado='0';
        $de='';
    }

    
    $sql1="select (l.qtdprod-".$qtdpedida.") as qtdprod,
    (l.qtdpedida-".$qtdpedida.") as  qtdpedida,qtdprod_exp
    from lote l 
    where l.idlote =".$idloteorigem;
    $rec1 = d::b()->query($sql1) or die("Erro ao buscar lote origem sql=".$sql1);
    $rowc1=mysqli_fetch_assoc($rec1);

   // $_SESSION['arrpostbuffer']['xm1']['u']['lote']['idlote']=$idloteorigem;
   // $_SESSION['arrpostbuffer']['xm1']['u']['lote']['qtdprod']=  recuperaExpoente(tratanumero($rowc1['qtdprod']), $rowc1['qtdprod_exp']); 
   // $_SESSION['arrpostbuffer']['xm1']['u']['lote']['qtdpedida'] =  recuperaExpoente(tratanumero($rowc1['qtdpedida']),$rowc1['qtdprod_exp']); 


   $_SESSION['arrpostbuffer']['xm1']['i']['lotecons']['idlote']=$idloteorigem;
   $_SESSION['arrpostbuffer']['xm1']['i']['lotecons']['idlotefracao']=$idlotefracaoorigem;
   $_SESSION['arrpostbuffer']['xm1']['i']['lotecons']['idobjeto']=$idlotedest;
   $_SESSION['arrpostbuffer']['xm1']['i']['lotecons']['tipoobjeto']='lote';
   $_SESSION['arrpostbuffer']['xm1']['i']['lotecons']['obs']='Migração';
   $_SESSION['arrpostbuffer']['xm1']['i']['lotecons']['idtransacao']=$idtransacao;
   $_SESSION['arrpostbuffer']['xm1']['i']['lotecons']['qtdd']=$qtddigitada;
   $_SESSION['arrpostbuffer']['xm1']['i']['lotecons']['idempresa']=$_SESSION["SESSAO"]["IDEMPRESA"]; 


    $sql2="select l.qtdprod as qtdest,(l.qtdprod+".$qtdpedida.") as qtdprod,
     (l.qtdpedida+".$qtdpedida.") as qtdpedida,qtdprod_exp,f.idlotefracao
    from  lote l join lotefracao f on(f.idlote=l.idlote and l.idunidade=f.idunidade)
    where l.idlote =".$idlotedest;
    $rec2 = d::b()->query($sql2) or die("Erro ao buscar lote destino sql=".$sql2);
    $rowc2=mysqli_fetch_assoc($rec2);

    //die($sql2);
    
    //qtdprod_exp ='2396d71' ou 0
   
  
        if (strpos(strtolower($rowc2['qtdprod_exp']),"d") or strpos(strtolower($rowc2['qtdprod_exp']),"e")) {
            
            if (strpos(strtolower($rowc2['qtdprod_exp']),"e")) {
                $arrvlr=explode("e",$rowc2['qtdprod_exp']);                
                $qtdDdestino=tratadouble($arrvlr[0]);            
            } elseif(strpos(strtolower($rowc2['qtdprod_exp']),"d")){
                $arrvlr=explode("d",$rowc2['qtdprod_exp']);
                $qtdDdestino=tratadouble($arrvlr[0]);//Multipicacao direta da diluicao
                
            } elseif(empty($rowc2['qtdprod_exp'])){
                $qtdDdestino='0';
                
            }
        }else{
            $newqtd_exp='';
            $qtdDdestino='0';
        }
        //echo($qtdDdigitado); die;

       
        if( $qtdDdestino>0){
            $vqtdDdestino=round(($rowc2['qtdest']+$qtdpedida)/($qtdDdigitado+$qtdDdestino));
            $volume=($qtdDdestino+$qtdDdigitado);
            $newqtd_exp= $volume.$de.$vqtdDdestino;
        }else{
            $newqtd_exp=  $qtddigitada;
        }
        
    

    $_SESSION['arrpostbuffer']['xm2']['u']['lote']['idlote']=$idlotedest;
    $_SESSION['arrpostbuffer']['xm2']['u']['lote']['qtdprod']=   $newqtd_exp; 
    $_SESSION['arrpostbuffer']['xm2']['u']['lote']['qtdpedida'] =   $newqtd_exp; 


    $_SESSION['arrpostbuffer']['ulxi2'.$i]['i']['lotecons']['idlote']=$idlotedest;
    $_SESSION['arrpostbuffer']['ulxi2'.$i]['i']['lotecons']['idlotefracao']=$rowc2['idlotefracao'];
    $_SESSION['arrpostbuffer']['ulxi2'.$i]['i']['lotecons']['idobjeto']=$idloteorigem;
    $_SESSION['arrpostbuffer']['ulxi2'.$i]['i']['lotecons']['tipoobjeto']='lote';
    $_SESSION['arrpostbuffer']['ulxi2'.$i]['i']['lotecons']['obs']='Migração';
    $_SESSION['arrpostbuffer']['ulxi2'.$i]['i']['lotecons']['idtransacao']=$idtransacao;
    $_SESSION['arrpostbuffer']['ulxi2'.$i]['i']['lotecons']['qtdsol']=$qtddigitada;
    $_SESSION['arrpostbuffer']['ulxi2'.$i]['i']['lotecons']['idempresa']=$_SESSION["SESSAO"]["IDEMPRESA"]; 



    
    //buscar para abater no consumo
    $sqc="select l.qtdprod-".$qtdpedida." as nqtdprod,l.qtdprod_exp,l.qtdpedida-".$qtdpedida." as nqtdpedida,l.qtdpedida_exp,round(((".$qtdpedida."*qtdd)/l.qtdprod),2) as qcons,round(c.qtdd-(((".$qtdpedida."*qtdd)/l.qtdprod)),2) as ncons,c.*
            from lotecons c join lote l on(l.idlote = c.idobjeto)
            join lote lc on(lc.idlote=c.idlote)
            join prodserv p on(p.idprodserv=lc.idprodserv and p.especial='N')
            where c.idobjeto=".$idloteorigem." and c.tipoobjeto = 'lote' and c.qtdd>0;";
    $rec = d::b()->query($sqc) or die("Erro ao buscar informacoes da unidade de origem : ". mysql_error() . "<p>SQL: ".$sqc); 
    //echo ($sqc);
    $i=1;
    while($rowc=mysqli_fetch_assoc($rec)){
        $i=$i+1;
        //print_r($rowc); die;

        $_SESSION['arrpostbuffer']['ulu'.$i]['u']['lotecons']['idlotecons']=$rowc['idlotecons'];
        $_SESSION['arrpostbuffer']['ulu'.$i]['u']['lotecons']['qtdd']=recuperaExpoente(tratanumero($rowc['ncons']),$rowc['qtdd_exp']);
        
        $sqlcd="select * from lotecons 
                where idlote=".$rowc['idlote']." 
                and idlotefracao=".$rowc['idlotefracao']." 
                and idobjeto='".$idlotedest."' 
                and qtdd>0
                and tipoobjeto= 'lote' limit 1";
        $recc = d::b()->query($sqlcd) or die("Erro ao buscar se o lote ja tem consumo: ". mysql_error() . "<p>SQL: ".$sqlcd); 
        $qtdcc=mysqli_num_rows($recc);
      // $i=$i+1;
     //  echo($sqlcd);
        if($qtdcc>0){
            $rowcc=mysqli_fetch_assoc($recc);
            //print_r($rowc);
           // die;
            $nvcons=$rowc['qcons']+$rowcc['qtdd'];

            $_SESSION['arrpostbuffer']['uc'.$i]['u']['lotecons']['idlotecons']=$rowcc['idlotecons'];
            $_SESSION['arrpostbuffer']['uc'.$i]['u']['lotecons']['qtdd']=recuperaExpoente(tratanumero($nvcons),$rowc['qtdd_exp']);
            
        }else{
        
            $_SESSION['arrpostbuffer']['uli'.$i]['i']['lotecons']['idlote']=$rowc['idlote'];
            $_SESSION['arrpostbuffer']['uli'.$i]['i']['lotecons']['idlotefracao']=$rowc['idlotefracao'];
            $_SESSION['arrpostbuffer']['uli'.$i]['i']['lotecons']['idobjeto']=$idlotedest;
            $_SESSION['arrpostbuffer']['uli'.$i]['i']['lotecons']['tipoobjeto']='lote';
            $_SESSION['arrpostbuffer']['uli'.$i]['i']['lotecons']['obs']='Lote Fracionado.';
            $_SESSION['arrpostbuffer']['uli'.$i]['i']['lotecons']['idtransacao']=$idtransacao;
            $_SESSION['arrpostbuffer']['uli'.$i]['i']['lotecons']['qtdd']=recuperaExpoente(tratanumero($rowc['qcons']),$rowc['qtdd_exp']);
            $_SESSION['arrpostbuffer']['uli'.$i]['i']['lotecons']['idempresa']=$_SESSION["SESSAO"]["IDEMPRESA"]; 
        }
        
    }  

//print_r($_SESSION['arrpostbuffer']); die;    

}

$gerandohistorico = $_SESSION['arrpostbuffer']['h1']['i']['modulohistorico']['idobjeto'];
if (!empty($gerandohistorico)) 
{
    $campo = $_SESSION['arrpostbuffer']['h1']['i']['modulohistorico']['campo'];
    $valor = $_SESSION['arrpostbuffer']['h1']['i']['modulohistorico']['valor'];
    $_SESSION['arrpostbuffer']['1']['u']['lote'][$campo] = $valor;

}



$idunidade=$_SESSION['arrpostbuffer']['x']['i']['lotefracao']['idunidade'];
$qtdpedida= $_SESSION['arrpostbuffer']['x']['i']['lotefracao']['qtd'];
$idloteorigem=$_SESSION['arrpostbuffer']['x']['i']['lotefracao']['idlote'];
$idlotefracaoori=$_SESSION['arrpostbuffer']['x']['i']['lotefracao']['idlotefracaoorigem'];
//$unfracao=$_SESSION['arrpostbuffer']['x']['i']['lotefracao']['un'];
$lotefracao_AO=$_POST['lotefracao_AO'];
//print_r($_SESSION['arrpostbuffer']); die();
if(!empty($idlotefracaoori) and $lotefracao_AO!='Y'){

    if (strpos(strtolower($qtdpedida),"d") or strpos(strtolower($qtdpedida),"e")) {
        //Efetua registro da representação científica/customizada na coluna associada

        if (strpos(strtolower($qtdpedida),"e")) {
            $qtdpedida_exp= str_replace(",",".",$qtdpedida);
            $arrvlr=explode("e",$qtdpedida);
            //maf140619: anteriormente esta parte estava devolvendo uma string a ser executada no MySQL. Ex: "576000*pow(10,7)" mas isso passava pela valida$
            $qtdpedidaori=tratadouble($arrvlr[0])*pow(10,tratadouble($arrvlr[1]));
            //$_SESSION["arrpostbuffer"][$row][$act][$tbl][$col]=tratanumero($arrvlr[0]) * pow(10,tratanumero($arrvlr[1]));
            //maf190819: nao verificar numerico neste ponto} elseif(strpos(strtolower($vlr),"d") && is_numeric(str_replace("d", "", $vlr))){
        } elseif(strpos(strtolower($qtdpedida),"d")){
            $qtdpedida_exp=$qtdpedida;//armazena valor original
            $arrvlr=explode("d",$qtdpedida);
            $qtdpedidaori=tratadouble($arrvlr[0])*tratadouble($arrvlr[1]);//Multipicacao direta da diluicao

        } 

    }else{
        $qtdpedidaori=$qtdpedida;
        $qtdpedida_exp="";          

        
    }
   
  
    $obs= $_SESSION['arrpostbuffer']['x']['i']['lotefracao']['obs'];

    if(empty($obs)){
        $obs='Transferência';
    }
   
    $consomeun=traduzid('unidade','idunidade','consomeun',$idunidade);
    $_idempresa=traduzid('unidade','idunidade','idempresa',$idunidade);
    //$idsgdepartamento=traduzid('unidade','idunidade','idsgdepartamento',$idunidade);

    $s="select * from lotefracao where idlote=".$idloteorigem." and idunidade=".$idunidade;
    $re = d::b()->query($s) or die("Erro ao buscar se ja existe fracao : ". mysql_error() . "<p>SQL: ".$s);  
    $qtdr=mysqli_num_rows($re);
    $rorig=mysqli_fetch_assoc($re);
    if($qtdr>0){// se ja tiver fracao 
        unset($_SESSION['arrpostbuffer']);
       
       // $qtdpedidaori=$qtdpedida;
        $qtdpedidadest=$qtdpedida;
         //ALTERAÇÕES DO PROJETO: CRIAÇÃO DE CHECK IMOBILOZADO NA PRODSERV -> LINK DO EVENTO: sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=467416  -- ALBT 11/06/2021.
        $s="select u.convestoque,l.valconvori as valconv,l.converteest as convertido,l.vunpadrao,p.consometransf,p.imobilizado,u.idtipounidade
			from lotefracao f join lote l on(l.idlote=f.idlote)
            join unidade u on(u.idunidade=f.idunidade)
            join prodserv p on(p.idprodserv=l.idprodserv)
        where f.idlotefracao=".$idlotefracaoori;
        $re = d::b()->query($s) or die("Erro ao buscar informacoes da unidade de origem : ". mysql_error() . "<p>SQL: ".$s);  
        $rowconv=mysqli_fetch_assoc($re);

        $convestdest=traduzid("unidade","idunidade","convestoque",$idunidade);
        if($rowconv["valconv"]>1 /*and $rowconv['uncptransf']=='Y' */and $rowconv["convestoque"]=='N' and $rowconv['convertido']=='Y'){
                      
            $qtdpedidadest=$qtdpedida; 
            $qtdpedidaori=$qtdpedida;
            $qtdpedida_exp=""; 
               
            if($rowconv['vunpadrao']=='N'){
                $qtdpedidadest= $qtdpedida*$rowconv["valconv"]; 
                $qtdpedidaori= $qtdpedida*$rowconv["valconv"]; 
                //$unfracao=$rowconv['unlote'];
            }
           
        } 
        
         // adiciona um debito na origem
         /*
        $_SESSION['arrpostbuffer']['ulc']['i']['lotecons']['idlote']=$idloteorigem;
        $_SESSION['arrpostbuffer']['ulc']['i']['lotecons']['idlotefracao']=$idlotefracaoori;
        $_SESSION['arrpostbuffer']['ulc']['i']['lotecons']['idobjeto']=$rorig['idlotefracao'];
        $_SESSION['arrpostbuffer']['ulc']['i']['lotecons']['tipoobjeto']='lotefracao';
        $_SESSION['arrpostbuffer']['ulc']['i']['lotecons']['obs']='Lote Fracionado.'; 
        $_SESSION['arrpostbuffer']['ulc']['i']['lotecons']['qtdd']=$qtdpedidaori; 
        $_SESSION['arrpostbuffer']['ulc']['i']['lotecons']['idempresa']=$_SESSION["SESSAO"]["IDEMPRESA"]; 
        */

        
        $inslotecons = new Insert();
        $inslotecons->setTable("lotecons");
        $inslotecons->idlote=$idloteorigem;
        $inslotecons->idlotefracao=$idlotefracaoori;
        $inslotecons->idobjeto=$rorig['idlotefracao'];
        $inslotecons->tipoobjeto='lotefracao';
        $inslotecons->idtransacao=$idtransacao;
        $inslotecons->obs=$obs;
        $inslotecons->qtdd=$qtdpedidaori;
        if(!empty($qtdpedida_exp)){
            $inslotecons->qtdd_exp=$qtdpedida_exp;
        }      
        $inidlotecons=$inslotecons->save();
        //gerar rateio
        /*
        if($rowconv['idtipounidade']==3){
            if(empty($idunidade)){
                $idrateioobj=$_idempresa;
                $objrateio='empresa';
            }else{
                $idrateioobj=$idunidade;
                $objrateio='unidade';
            }

            $insrateio = new Insert();
            $insrateio->setTable("rateio");
            $insrateio->idobjeto=$idloteorigem;
            $insrateio->tipoobjeto='lote';
            $inidrateio=$insrateio->save();

            $insrateioitem = new Insert();
            $insrateioitem->setTable("rateioitem");
            $insrateioitem->idrateio=$inidrateio;
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
        */
        
        
        // adiciona um credItio na destino
        $_SESSION['arrpostbuffer']['ufr']['i']['lotecons']['idlote']=$idloteorigem;
        $_SESSION['arrpostbuffer']['ufr']['i']['lotecons']['idlotefracao']=$rorig['idlotefracao'];
        $_SESSION['arrpostbuffer']['ufr']['i']['lotecons']['qtdc']=$qtdpedidadest; 
        $_SESSION['arrpostbuffer']['ufr']['i']['lotecons']['idobjeto']=$idlotefracaoori;
        $_SESSION['arrpostbuffer']['ufr']['i']['lotecons']['idempresa']=$_idempresa;
        $_SESSION['arrpostbuffer']['ufr']['i']['lotecons']['tipoobjeto']='lotefracao';
        $_SESSION['arrpostbuffer']['ufr']['i']['lotecons']['idtransacao']=$idtransacao;
        $_SESSION['arrpostbuffer']['ufr']['i']['lotecons']['obs']=$obs;
        $_SESSION['arrpostbuffer']['ufr']['i']['lotecons']['idempresa']=$_SESSION["SESSAO"]["IDEMPRESA"];
        //ALTERAÇÕES DO PROJETO: CRIAÇÃO DE CHECK IMOBILOZADO NA PRODSERV -> LINK DO EVENTO: sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=467416  -- ALBT 11/06/2021.
        if(($rowconv["consometransf"]=='Y' or $consomeun=='Y') and $rowconv["imobilizado"] != 'Y'){
            $_SESSION['arrpostbuffer']['ulc2']['i']['lotecons']['idlote']=$idloteorigem;
            $_SESSION['arrpostbuffer']['ulc2']['i']['lotecons']['idlotefracao']=$rorig['idlotefracao'];
            $_SESSION['arrpostbuffer']['ulc2']['i']['lotecons']['idobjeto']=$rorig['idlotefracao'];
            $_SESSION['arrpostbuffer']['ulc2']['i']['lotecons']['idempresa']=$_idempresa;
            $_SESSION['arrpostbuffer']['ulc2']['i']['lotecons']['tipoobjeto']='lotefracao';
            $_SESSION['arrpostbuffer']['ulc2']['i']['lotecons']['idtransacao']=$idtransacao;
            $_SESSION['arrpostbuffer']['ulc2']['i']['lotecons']['obs']='Lote consumido na transferência';
            $_SESSION['arrpostbuffer']['ulc2']['i']['lotecons']['qtdd']=$qtdpedidadest;
        }
        
       
      montatabdef();
    }else{//se não tiver fracao
        //$qtdpedidaori=$qtdpedida;
        $qtdpedidadest=$qtdpedida;
        unset($_SESSION['arrpostbuffer']);                       
        
        $s="select u.convestoque,l.valconvori as valconv,l.converteest as convertido,l.unlote,l.idprodserv,l.vunpadrao,p.consometransf,p.imobilizado,u.idtipounidade
			from lotefracao f join lote l on(l.idlote=f.idlote)
            join unidade u on(u.idunidade=f.idunidade)
            join prodserv p on(p.idprodserv=l.idprodserv)
        where f.idlotefracao=".$idlotefracaoori;
        $re = d::b()->query($s) or die("Erro ao buscar informacoes da unidade de origem : ". mysql_error() . "<p>SQL: ".$s);  
        $rowconv=mysqli_fetch_assoc($re);

        $convestdest=traduzid("unidade","idunidade","convestoque",$idunidade);
        if($rowconv["valconv"]>1 /* and $rowconv['uncptransf']=='Y'*/ and $rowconv["convestoque"]=='N' and $rowconv["convertido"]=='Y'){
           
            $qtdpedidaori=$qtdpedida; 
            $qtdpedidaorif=$qtdpedida;
            $qtdpedida_exp=""; 
           
            if($rowconv['vunpadrao']=='N'){
                $qtdpedidaori=  $qtdpedida*$rowconv["valconv"]; 
                $qtdpedidaorif= $qtdpedida*$rowconv["valconv"];  
                //$unfracao=$rowconv['unlote'];
            }
        }
        
        
        $_idempresa=traduzid('unidade','idunidade','idempresa',$idunidade);
        
        $inslotefracao = new Insert();
        $inslotefracao->setTable("lotefracao");
        $inslotefracao->idunidade=$idunidade;
        $inslotefracao->qtd=$qtdpedidaori;
        if(!empty($qtdpedida_exp)){
            $inslotefracao->qtd_exp=$qtdpedida_exp;
        } 
        $inslotefracao->qtdini=$qtdpedidaori;
        if(!empty($qtdpedida_exp)){
            $inslotefracao->qtdini_exp=$qtdpedida_exp;
        } 
        $inslotefracao->idempresa=$_idempresa;
        $inslotefracao->idtransacao=$idtransacao;
        $inslotefracao->idlote=$idloteorigem;
        $inslotefracao->idlotefracaoorigem=$idlotefracaoori;      
        $_idlotefracao=$inslotefracao->save();

        $_SESSION['arrpostbuffer']['ulc']['u']['lotefracao']['idlotefracaoorigem']=$idlotefracaoori;
        $_SESSION['arrpostbuffer']['ulc']['u']['lotefracao']['idlotefracao']= $_idlotefracao;

        $inslotecons = new Insert();
        $inslotecons->setTable("lotecons");
        $inslotecons->idlote=$idloteorigem;
        $inslotecons->idlotefracao=$idlotefracaoori;
        $inslotecons->idobjeto=$_idlotefracao;
        $inslotecons->tipoobjeto='lotefracao';
        $inslotecons->idtransacao=$idtransacao;
        $inslotecons->obs=$obs;
        $inslotecons->qtdd=$qtdpedidaori;
        if(!empty($qtdpedida_exp)){
            $inslotecons->qtdd_exp=$qtdpedida_exp;
        }       
        $inidlotecons=$inslotecons->save();
        //gerar rateio
        /*
        if($rowconv['idtipounidade']==3){
            if(empty($idunidade)){
                $idrateioobj=$_idempresa;
                $objrateio='empresa';
            }else{
                $idrateioobj=$idunidade;
                $objrateio='unidade';
            }           

            $insrateio = new Insert();
            $insrateio->setTable("rateio");
            $insrateio->idobjeto=$idloteorigem;
            $insrateio->tipoobjeto='lote';
            $inidrateio=$insrateio->save();

            $insrateioitem = new Insert();
            $insrateioitem->setTable("rateioitem");
            $insrateioitem->idrateio=$inidrateio;
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
       */ 
       

        if(($rowconv["consometransf"]=='Y' or $consomeun=='Y') and $rowconv["imobilizado"] != 'Y'){
            $_SESSION['arrpostbuffer']['ulc2']['i']['lotecons']['idlote']=$idloteorigem;
            $_SESSION['arrpostbuffer']['ulc2']['i']['lotecons']['idlotefracao']=$_idlotefracao;
            $_SESSION['arrpostbuffer']['ulc2']['i']['lotecons']['idempresa']=$_idempresa;
            $_SESSION['arrpostbuffer']['ulc2']['i']['lotecons']['tipoobjeto']='lotefracao';
            $_SESSION['arrpostbuffer']['ulc2']['i']['lotecons']['idtransacao']=$idtransacao;
            $_SESSION['arrpostbuffer']['ulc2']['i']['lotecons']['idobjeto']=$_idlotefracao;
            $_SESSION['arrpostbuffer']['ulc2']['i']['lotecons']['obs']='Lote consumido na transferência';
            $_SESSION['arrpostbuffer']['ulc2']['i']['lotecons']['qtdd']=$qtdpedidaorif;
            $_SESSION['arrpostbuffer']['ulc2']['i']['lotecons']['idempresa']=$_SESSION["SESSAO"]["IDEMPRESA"];
        }

         montatabdef();
    }
    
}elseif(!empty($idlotefracaoori) and $lotefracao_AO=='Y'){
    
    $idpessoa=$_SESSION['arrpostbuffer']['x']['i']['lotefracao']['idpessoa'];
    
      
        unset($_SESSION['arrpostbuffer']);           
        
        if (strpos(strtolower($qtdpedida),"d") or strpos(strtolower($qtdpedida),"e")) {
                                                                            //Efetua registro da representação científica/customizada na coluna associada
            
                    if (strpos(strtolower($qtdpedida),"e")) {
                            $qtdpedida_exp= str_replace(",",".",$qtdpedida);
                            $arrvlr=explode("e",$qtdpedida);
                            //maf140619: anteriormente esta parte estava devolvendo uma string a ser executada no MySQL. Ex: "576000*pow(10,7)" mas isso passava pela valida$
                            $qtdpedida=tratadouble($arrvlr[0])*pow(10,tratadouble($arrvlr[1]));
                            //$_SESSION["arrpostbuffer"][$row][$act][$tbl][$col]=tratanumero($arrvlr[0]) * pow(10,tratanumero($arrvlr[1]));
                    //maf190819: nao verificar numerico neste ponto} elseif(strpos(strtolower($vlr),"d") && is_numeric(str_replace("d", "", $vlr))){
                     } elseif(strpos(strtolower($qtdpedida),"d")){
                            $qtdpedida_exp=$qtdpedida;//armazena valor original
                            $arrvlr=explode("d",$qtdpedida);
                            $qtdpedida=tratadouble($arrvlr[0])*tratadouble($arrvlr[1]);//Multipicacao direta da diluicao
                          
                    } elseif(empty($_SESSION["arrpostbuffer"][$row][$act][$tbl][$col."_exp"])){
                            //Se estiver configurada alguma coluna associada, mas não vier valor, limpar coluna
                            $qtdpedida_exp="";
                    }
         
        }else{
           
                $qtdpedida_exp="";          

        }
        //die($qtdpedida.' '.$qtdpedida_exp);

        $_idlote = novolote($idloteorigem,$qtdpedida,$qtdpedida_exp,$idunidade,$idpessoa,$fluxo);

        $idempresa = isset($_GET["_idempresa"]) ? $_GET["_idempresa"] : $_SESSION["SESSAO"]["IDEMPRESA"];
        
        $idempresa = isset($_GET["_idempresa"]) ? $_GET["_idempresa"] : $_SESSION["SESSAO"]["IDEMPRESA"];
        
        //LTM - 14/05/2021: Cria a Formalização
        insertFormalizacao($idunidade, $_idlote, $fluxo, $idempresa, $idloteorigem);

        //buscar para abater no consumo
        $sqc="select l.qtdprod-".$qtdpedida." as nqtdprod,l.qtdprod_exp,l.qtdpedida-".$qtdpedida." as nqtdpedida,l.qtdpedida_exp,round(((".$qtdpedida."*qtdd)/l.qtdprod),2) as qcons,round(c.qtdd-(((".$qtdpedida."*qtdd)/l.qtdprod)),2) as ncons,c.*
                from lotecons c join lote l on(l.idlote = c.idobjeto)
                join lote lc on(lc.idlote=c.idlote)
                join prodserv p on(p.idprodserv=lc.idprodserv and p.especial='N')
                where c.idobjeto=".$idloteorigem." and c.tipoobjeto = 'lote' and c.qtdd>0 and c.status = 'ABERTO';";


   // die($sqc);
        $rec = d::b()->query($sqc) or die("Erro ao buscar informacoes da unidade de origem : ". mysql_error() . "<p>SQL: ".$sqc); 
        $i=1;
        $nresc = mysqli_num_rows($rec);
        if($nresc>0){
            while($rowc=mysqli_fetch_assoc($rec)){
                $i=$i+1;
                $_SESSION['arrpostbuffer']['ulu'.$i]['u']['lotecons']['idlotecons']=$rowc['idlotecons'];
                $_SESSION['arrpostbuffer']['ulu'.$i]['u']['lotecons']['qtdd']=recuperaExpoente(tratanumero($rowc['ncons']),$rowc['qtdd_exp']);
                
                $_SESSION['arrpostbuffer']['ult'.$i]['u']['lote']['idlote']=$idloteorigem;
                $_SESSION['arrpostbuffer']['ult'.$i]['u']['lote']['qtdpedida']=recuperaExpoente(tratanumero($rowc['nqtdprod']),$rowc['qtdprod_exp']) ;
                $_SESSION['arrpostbuffer']['ult'.$i]['u']['lote']['qtdprod']=recuperaExpoente(tratanumero($rowc['nqtdprod']),$rowc['qtdprod_exp']);         
                
                $_SESSION['arrpostbuffer']['uli'.$i]['i']['lotecons']['idlote']=$rowc['idlote'];
                $_SESSION['arrpostbuffer']['uli'.$i]['i']['lotecons']['idlotefracao']=$rowc['idlotefracao'];
                $_SESSION['arrpostbuffer']['uli'.$i]['i']['lotecons']['idobjeto']=$_idlote;
                $_SESSION['arrpostbuffer']['uli'.$i]['i']['lotecons']['tipoobjeto']='lote';
                $_SESSION['arrpostbuffer']['uli'.$i]['i']['lotecons']['idtransacao']=$idtransacao;
                $_SESSION['arrpostbuffer']['uli'.$i]['i']['lotecons']['obs']=$obs;
                $_SESSION['arrpostbuffer']['uli'.$i]['i']['lotecons']['qtdd']=recuperaExpoente(tratanumero($rowc['qcons']),$rowc['qtdd_exp']);
                $_SESSION['arrpostbuffer']['uli'.$i]['i']['lotecons']['idempresa']=$_SESSION["SESSAO"]["IDEMPRESA"]; 

            }
        }else{
            $sqlloteqtdprod = "select qtdprod-".$qtdpedida." as qtdprod,qtdpedida-".$qtdpedida." as qtdpedida,qtdprod_exp  from lote where idlote = ".$idloteorigem;
            $resloteqtdprod = d::b()->query($sqlloteqtdprod) or die("Erro ao buscar informacoes da unidade de origem : ". mysql_error() . "<p>SQL: ".$sqlloteqtdprod);
            $rowloteqtdprod=mysqli_fetch_assoc($resloteqtdprod);
            $_SESSION['arrpostbuffer']['ult']['u']['lote']['idlote']=$idloteorigem;
            $_SESSION['arrpostbuffer']['ult']['u']['lote']['qtdpedida']=recuperaExpoente(tratanumero($rowloteqtdprod['qtdprod']),$rowloteqtdprod['qtdprod_exp']) ;
            $_SESSION['arrpostbuffer']['ult']['u']['lote']['qtdprod']=recuperaExpoente(tratanumero($rowloteqtdprod['qtdprod']),$rowloteqtdprod['qtdprod_exp']);
        }

        $_SESSION['arrpostbuffer']['ulc']['i']['lotecons']['idlote']=$idloteorigem;
        $_SESSION['arrpostbuffer']['ulc']['i']['lotecons']['idlotefracao']=$idlotefracaoori;
        $_SESSION['arrpostbuffer']['ulc']['i']['lotecons']['idobjeto']=$_idlote;
        $_SESSION['arrpostbuffer']['ulc']['i']['lotecons']['tipoobjeto']='lote';
        $_SESSION['arrpostbuffer']['ulc']['i']['lotecons']['idtransacao']=$idtransacao;
        $_SESSION['arrpostbuffer']['ulc']['i']['lotecons']['obs']=$obs;
        $_SESSION['arrpostbuffer']['ulc']['i']['lotecons']['status']='ALIQUOTA';
        $_SESSION['arrpostbuffer']['ulc']['i']['lotecons']['qtdd']=recuperaExpoente(tratanumero($qtdpedida),$qtdpedida_exp); 
        $_SESSION['arrpostbuffer']['ulc']['i']['lotecons']['idempresa']=$_SESSION["SESSAO"]["IDEMPRESA"]; 

        $sqluLote='UPDATE lote SET vlrlote = null where idlote = '.$idloteorigem;
        $resuLote = d::b()->query($sqluLote); 

         montatabdef();
         
    }elseif(!empty($idloteorigem) && $lotefracao_AO == 'T'){// fracionar lotes, copia parte dos consumos para o lote destino
        $_idlote = $_SESSION['arrpostbuffer']['x']['i']['lotefracao']['idlotedestino'];// idlote destino

        unset($_SESSION['arrpostbuffer']);           

        $date = new DateTime();
        $now = $date->format("Y-m-d H:i:s");
        
        if (strpos(strtolower($qtdpedida),"d") or strpos(strtolower($qtdpedida),"e")) {
                                                                            //Efetua registro da representação científica/customizada na coluna associada
            
                    if (strpos(strtolower($qtdpedida),"e")) {
                            $qtdpedida_exp= str_replace(",",".",$qtdpedida);
                            $arrvlr=explode("e",$qtdpedida);
                            //maf140619: anteriormente esta parte estava devolvendo uma string a ser executada no MySQL. Ex: "576000*pow(10,7)" mas isso passava pela valida$
                            $qtdpedida=tratadouble($arrvlr[0])*pow(10,tratadouble($arrvlr[1]));
                            //$_SESSION["arrpostbuffer"][$row][$act][$tbl][$col]=tratanumero($arrvlr[0]) * pow(10,tratanumero($arrvlr[1]));
                    //maf190819: nao verificar numerico neste ponto} elseif(strpos(strtolower($vlr),"d") && is_numeric(str_replace("d", "", $vlr))){
                     } elseif(strpos(strtolower($qtdpedida),"d")){
                            $qtdpedida_exp=$qtdpedida;//armazena valor original
                            $arrvlr=explode("d",$qtdpedida);
                            $qtdpedida=tratadouble($arrvlr[0])*tratadouble($arrvlr[1]);//Multipicacao direta da diluicao
                          
                    } elseif(empty($_SESSION["arrpostbuffer"][$row][$act][$tbl][$col."_exp"])){
                            //Se estiver configurada alguma coluna associada, mas não vier valor, limpar coluna
                            $qtdpedida_exp="";
                    }
        }else{
                $qtdpedida_exp="";          
        }
    
        
 
      //buscar para abater no consumo
        $sqc="select l.qtdprod-".$qtdpedida." as nqtdprod,l.qtdprod_exp,l.qtdpedida-".$qtdpedida." as nqtdpedida,l.qtdpedida_exp,round(((".$qtdpedida."*qtdd)/l.qtdprod),2) as qcons,round(c.qtdd-(((".$qtdpedida."*qtdd)/l.qtdprod)),2) as ncons,c.*
                from lotecons c join lote l on(l.idlote = c.idobjeto)
                join lote lc on(lc.idlote=c.idlote)
                join prodserv p on(p.idprodserv=lc.idprodserv and p.especial='N')
                where c.idobjeto=".$idloteorigem." and c.tipoobjeto = 'lote' and c.qtdd>0 and c.status = 'ABERTO';";


    //die($sqc);
        $rec = d::b()->query($sqc) or die("Erro ao buscar informacoes da unidade de origem : ". mysql_error() . "<p>SQL: ".$sqc); 
        $i=1;
        $nresc = mysqli_num_rows($rec);
        if($nresc>0){
            while($rowc=mysqli_fetch_assoc($rec)){
                $i=$i+1;
                
                $_SESSION['arrpostbuffer']['ulu'.$i]['u']['lotecons']['idlotecons']=$rowc['idlotecons'];
                $_SESSION['arrpostbuffer']['ulu'.$i]['u']['lotecons']['qtdd']=recuperaExpoente(tratanumero($rowc['ncons']),$rowc['qtdd_exp']);
                
                $_SESSION['arrpostbuffer']['ult'.$i]['u']['lote']['idlote']=$idloteorigem;
                $_SESSION['arrpostbuffer']['ult'.$i]['u']['lote']['qtdpedida']=recuperaExpoente(tratanumero($rowc['nqtdprod']),$rowc['qtdprod_exp']) ;
                $_SESSION['arrpostbuffer']['ult'.$i]['u']['lote']['qtdprod']=recuperaExpoente(tratanumero($rowc['nqtdprod']),$rowc['qtdprod_exp']);         
                
                $_SESSION['arrpostbuffer']['uli'.$i]['i']['lotecons']['idlote']=$rowc['idlote'];
                $_SESSION['arrpostbuffer']['uli'.$i]['i']['lotecons']['idlotefracao']=$rowc['idlotefracao'];
                $_SESSION['arrpostbuffer']['uli'.$i]['i']['lotecons']['idobjeto']=$_idlote;
                $_SESSION['arrpostbuffer']['uli'.$i]['i']['lotecons']['tipoobjeto']='lote';
                $_SESSION['arrpostbuffer']['uli'.$i]['i']['lotecons']['idtransacao']=$idtransacao;
                $_SESSION['arrpostbuffer']['uli'.$i]['i']['lotecons']['obs']=$obs;
                $_SESSION['arrpostbuffer']['uli'.$i]['i']['lotecons']['qtdd']=recuperaExpoente(tratanumero($rowc['qcons']),$rowc['qtdd_exp']);
                $_SESSION['arrpostbuffer']['uli'.$i]['i']['lotecons']['idempresa']=$_SESSION["SESSAO"]["IDEMPRESA"]; 
                
            }
        }else{
            $sqlloteqtdprod = "select qtdprod-".$qtdpedida." as qtdprod,qtdpedida-".$qtdpedida." as qtdpedida,qtdprod_exp  from lote where idlote = ".$idloteorigem;
            $resloteqtdprod = d::b()->query($sqlloteqtdprod) or die("Erro ao buscar informacoes da unidade de origem : ". mysql_error() . "<p>SQL: ".$sqlloteqtdprod);
            $rowloteqtdprod=mysqli_fetch_assoc($resloteqtdprod);
            $_SESSION['arrpostbuffer']['ult']['u']['lote']['idlote']=$idloteorigem;
            $_SESSION['arrpostbuffer']['ult']['u']['lote']['qtdpedida']=recuperaExpoente(tratanumero($rowloteqtdprod['qtdprod']),$rowloteqtdprod['qtdprod_exp']) ;
            $_SESSION['arrpostbuffer']['ult']['u']['lote']['qtdprod']=recuperaExpoente(tratanumero($rowloteqtdprod['qtdprod']),$rowloteqtdprod['qtdprod_exp']);
        }
/* não vincular o lote destino ao origem
        $_SESSION['arrpostbuffer']['ulc']['i']['lotecons']['idlote']=$idloteorigem;
        $_SESSION['arrpostbuffer']['ulc']['i']['lotecons']['idlotefracao']=$idlotefracaoori;
        $_SESSION['arrpostbuffer']['ulc']['i']['lotecons']['idobjeto']=$_idlote;
        $_SESSION['arrpostbuffer']['ulc']['i']['lotecons']['tipoobjeto']='lote';
        $_SESSION['arrpostbuffer']['ulc']['i']['lotecons']['idtransacao']=$idtransacao;
        $_SESSION['arrpostbuffer']['ulc']['i']['lotecons']['obs']=$obs;
        $_SESSION['arrpostbuffer']['ulc']['i']['lotecons']['status']='ALIQUOTA';
        $_SESSION['arrpostbuffer']['ulc']['i']['lotecons']['qtdd']=recuperaExpoente(tratanumero($qtdpedida),$qtdpedida_exp); 
        $_SESSION['arrpostbuffer']['ulc']['i']['lotecons']['idempresa']=$_SESSION["SESSAO"]["IDEMPRESA"]; 
        
*/

   //Insert Objetovinculo para histórico de transferência de consumo.
   $insertObjetovinculo = "INSERT INTO objetovinculo (idempresa, idobjeto, tipoobjeto, idobjetovinc, tipoobjetovinc, criadopor, criadoem, alteradopor, alteradoem)
   VALUES(".$_SESSION["SESSAO"]["IDEMPRESA"].", ".$idloteorigem.", 'lotepdiorigem', ".$_idlote.", 'lotepdidestino', '".$_SESSION["SESSAO"]["USUARIO"]."', '".$now."', '".$_SESSION["SESSAO"]["USUARIO"]."', '".$now."');";
    $sqlInsertObjetovinculo = d::b()->query($insertObjetovinculo);

    
        $sqluLote='UPDATE lote SET vlrlote = null where idlote = '.$idloteorigem;
        $resuLote = d::b()->query($sqluLote); 

         montatabdef();
}


//print_r( $_SESSION['arrpostbuffer']); die();


//gerar a transferencia fiscal dos itens
$idprodservforn=$_SESSION['arrpostbuffer']['tf']['i']['lote']['idprodservforn'];
$idpessoa=$_SESSION['arrpostbuffer']['tf']['i']['lote']['idpessoa'];
$idloteorigem=$_SESSION['arrpostbuffer']['tf']['i']['lote']['idloteorigem'];
$qtdprod=$_SESSION['arrpostbuffer']['tf']['i']['lote']['qtdprod'];
$idlotefracaoori=$_POST['idlotefracaoorigem'];
if(!empty($idprodservforn)){

    //print_r($_SESSION['arrpostbuffer']); die;
    $sql="select  
                f.idprodserv,p.idempresa,u.idunidade,d.idpessoa,p.uncom,p.idtipoprodserv
                ,p.descr,eori.idempresa as idempresaori,eori.idpessoaform,g.idpessoa as idpessoapedido
                ,f.converteest,f.valconv  
            from prodservforn f 
            join prodserv p on(p.idprodserv=f.idprodserv)
            join unidade u on(u.idempresa = p.idempresa and u.status='ATIVO'  and u.idtipounidade = 3)
            join prodserv pori on(pori.idprodserv=f.idprodservori)
            join empresa eori on(eori.idempresa = pori.idempresa)
            join pessoa g on(g.idempresagrupo=p.idempresa)  
            join pessoa d on(d.idempresagrupo=eori.idempresa)            
            where f.idprodservforn=".$idprodservforn;
    $res = d::b()->query($sql);
    $row=mysqli_fetch_assoc($res);

    if (strpos(strtolower($qtdprod),"d") or strpos(strtolower($qtdprod),"e")) {
        //Efetua registro da representação científica/customizada na coluna associada

        if (strpos(strtolower($qtdprod),"e")) {
            $qtdprod_exp= str_replace(",",".",$qtdprod);
            $arrvlr=explode("e",$qtdprod);
            //maf140619: anteriormente esta parte estava devolvendo uma string a ser executada no MySQL. Ex: "576000*pow(10,7)" mas isso passava pela valida$
            $qtdprod=tratadouble($arrvlr[0])*pow(10,tratadouble($arrvlr[1]));
            //$_SESSION["arrpostbuffer"][$row][$act][$tbl][$col]=tratanumero($arrvlr[0]) * pow(10,tratanumero($arrvlr[1]));
            //maf190819: nao verificar numerico neste ponto} elseif(strpos(strtolower($vlr),"d") && is_numeric(str_replace("d", "", $vlr))){
        } elseif(strpos(strtolower($qtdprod),"d")){
            $qtdprod_exp=$qtdprod;//armazena valor original
            $arrvlr=explode("d",$qtdprod);
            $qtdprod=tratadouble($arrvlr[0])*tratadouble($arrvlr[1]);//Multipicacao direta da diluicao

        } elseif(empty($_SESSION["arrpostbuffer"][$row][$act][$tbl][$col."_exp"])){
            //Se estiver configurada alguma coluna associada, mas não vier valor, limpar coluna
            $qtdprod_exp="";
        }

    }else{

        $qtdprod_exp="";          

    }
    //die($qtdpedida.' '.$qtdpedida_exp);
 
    $_idlote = novolotefiscal($idloteorigem,$row,$idprodservforn,$qtdprod,$qtdprod_exp,$fluxo);

    if(empty($_idlote)){
        die("Não foi possível gerar o lote na empresa destino");
    }

    //Saída
    gerapedido($row,$idloteorigem,$idprodservforn,$qtdprod,$idlotefracaoori,$idtransacao,$fluxo);
    //Entrada
    $idprodservformula= traduzid('lote', 'idlote','idprodservformula', $idloteorigem);
    

    if(!empty($idprodservformula)){
        $_sqlf="select vlrvenda ,comissao from prodservformula where idprodservformula=".$idprodservformula;
        $resf = d::b()->query($_sqlf) or die($_sqlf."Erro ao buscar formula: ".mysql_error());
        $qtdformula=mysqli_num_rows($resf);
        $rowf=mysqli_fetch_assoc($resf);
        if($rowf["vlrvenda"]>0){
            $row['vlrvenda']=$rowf["vlrvenda"];//troca pelo valor e comissao da formula
        }                     
    }

    geracompra($row,$idloteorigem,$_idlote,$idprodservforn,$qtdprod,$idlotefracaoori,$idtransacao,$fluxo);

    unset($_SESSION['arrpostbuffer']);

    $_SESSION['arrpostbuffer']['tf']['u']['lote']['idlote']=$idloteorigem;
    $_SESSION['arrpostbuffer']['tf']['u']['lote']['idunidadegp']=1;

}

function  geracompra($row,$idloteorigem,$idlote,$idprodservforn,$qtdprod,$idlotefracaoori,$idtransacao,$fluxo){

    $idprodservori= traduzid('lote', 'idlote','idprodserv', $idlote);
    $vlrlote= traduzid('lote', 'idlote','vlrlote', $idloteorigem);
 
    if($row['converteest']=='Y' and $row['valconv']>1){
        $qtdprod = $row['valconv'] * $qtdprod;
        $vlrlote = ($vlrlote / $row['valconv']);
    }
  
    $sqln="select * from nf
                where status='TRANSFERIDO' 
                and idpessoa = ".$row['idpessoa']."
                and tiponf = 'C'
                and idempresa = ".$row['idempresa']."
                and tipoorc ='TFISCAL'";
        $resn=d::b()->query($sqln);
        $qtdn=mysqli_num_rows($resn);
        if($qtdn<1){

            $qrUnid = "SELECT idunidade FROM unidade WHERE idtipounidade = 19 AND idempresa = ".$row['idempresa'];
            $rsUnid = d::b()->query($qrUnid) or die("[prechangeloteproducao][1]: Erro ao buscar idunidade. SQL: ".$qrUnid);
            $rwUnid = mysqli_fetch_assoc($rsUnid);

            //LTM - 05-04-2021: Retorna o Idfluxo nf para PEDIDO (Tipo v)
            $idfluxostatus = FluxoController::getIdFluxoStatus('nfentrada', 'TRANSFERIDO');

            $arrinsnf[1]['idpessoa']=$row['idpessoa'];		
            $arrinsnf[1]['dtemissao']= date("Y-m-d H:i:s");
            $arrinsnf[1]['tiponf']='C';
            $arrinsnf[1]['idunidade']=$rwUnid["idunidade"];
            $arrinsnf[1]['geracontapagar']='Y';
            $arrinsnf[1]['status']='TRANSFERIDO';
            $arrinsnf[1]['idfluxostatus'] = $idfluxostatus;
            $arrinsnf[1]['tipoorc']='TFISCAL';
            $arrinsnf[1]['parcelas']=1;
            $arrinsnf[1]['idempresa']=$row['idempresa']; 
            $arrinsnf[1]['geracontapagar']='N';             
            $arrinsnf[1]['diasentrada']=1;					
           

            $idnf=cnf::inseredb($arrinsnf,'nf');
            $idnf=$idnf[0];
        }else{
            $rown=mysqli_fetch_assoc($resn);
            $idnf= $rown['idnf'];
        }
         
        $stp="SELECT p.idcontaitem,ps.idtipoprodserv
                    FROM prodservcontaitem p 
                    join prodserv ps on(ps.idprodserv = p.idprodserv)
                WHERE p.idprodserv =".$idprodservori;
        $rtp=d::b()->query($stp);
        $rotp=mysqli_fetch_assoc($rtp);

        $valor=$row['vlrvenda'];

        $arrnfitem=array();
        if($rotp['idcontaitem']){
            $arrnfitem[1]['idcontaitem']=$rotp['idcontaitem'];
        }
        if($rotp['idtipoprodserv']){
            $arrnfitem[1]['idtipoprodserv']=$rotp['idtipoprodserv'];
        }
        $arrnfitem[1]['qtd']=$qtdprod;
        $arrnfitem[1]['qtdsol']=$qtdprod;
        $arrnfitem[1]['vlritem']=$vlrlote;
        $arrnfitem[1]['idprodserv']= $idprodservori;
        $arrnfitem[1]['idnf']=$idnf;
        $arrnfitem[1]['nfe']='Y';
        $arrnfitem[1]['tiponf']='C';
        $arrnfitem[1]['idempresa']=$row['idempresa']; 
        $inidnfitem=cnf::inseredb($arrnfitem,'nfitem'); 
        $inidnfitem=$inidnfitem[0];

        cnf::atualizavalornf($idnf);

      $sqlup="update lote set idnfitem = ". $inidnfitem." where idlote = ". $idlote;
      $resup= d::b()->query($sqlup) or die("Erro ao vincular lote gerado a compra sql=".$sqlup);
}

function  gerapedido($row,$idloteorigem,$idprodservforn,$qtdprod,$idlotefracaoori,$idtransacao,$fluxo){

    $idprodservori= traduzid('lote', 'idlote','idprodserv', $idloteorigem);
    $idprodservformula= traduzid('lote', 'idlote','idprodservformula', $idloteorigem);
    $vlrlote= traduzid('lote', 'idlote','vlrlote', $idloteorigem);
  
    $sqle="select * from endereco 
            where idpessoa=".$row['idpessoapedido']." 
            and status='ATIVO' 
            and idtipoendereco = 2";
    $rese=d::b()->query($sqle) or die("Erro ao buscar endereço de faturamento sql=".$sqle);
    $rowe=mysqli_fetch_assoc($rese);

   
    $sqln="select * from nf
                where status='TRANSFERIDO' 
                and idpessoa = ".$row['idpessoapedido']."
                and tiponf = 'V'
                and idempresa = ".$row['idempresaori']."
                and tipoorc ='TFISCAL'";
        $resn=d::b()->query($sqln);
        $qtdn=mysqli_num_rows($resn);
        if($qtdn<1){

            //LTM - 05-04-2021: Retorna o Idfluxo nf para PEDIDO (Tipo v)
            $idfluxostatus = FluxoController::getIdFluxoStatus('pedido', 'TRANSFERIDO');

            $arrinsnf[1]['idpessoa']=$row['idpessoapedido'];		
            $arrinsnf[1]['idpessoafat']=$row['idpessoapedido'];
            $arrinsnf[1]['dtemissao']= date("Y-m-d H:i:s");
            $arrinsnf[1]['tiponf']='V';
            $arrinsnf[1]['geracontapagar']='Y';
            $arrinsnf[1]['idenderecofat']= $rowe['idendereco'];
            $arrinsnf[1]['idendrotulo']= $rowe['idendereco'];    
            $arrinsnf[1]['idnatop']=1;
            $arrinsnf[1]['status']='TRANSFERIDO';
            $arrinsnf[1]['idfluxostatus'] = $idfluxostatus;
            $arrinsnf[1]['tipoorc']='TFISCAL';
            $arrinsnf[1]['parcelas']=1;
            $arrinsnf[1]['geracontapagar']='N';
            $arrinsnf[1]['idempresa']=$row['idempresaori']; 
            $arrinsnf[1]['diasentrada']=1;					
           

            $idnf=cnf::inseredb($arrinsnf,'nf');
            $idnf=$idnf[0];
        }else{
            $rown=mysqli_fetch_assoc($resn);
            $idnf= $rown['idnf'];
        }

        //$valor=$row['uncom']*$qtdprod;


        $inidnfitem=gerapedidoitem($qtdprod, $vlrlote,$idprodservori,$idnf,$row['idempresaori'],$idprodservformula);
/*
        $arrnfitem=array();
        $arrnfitem[1]['qtd']=$qtdprod;
        $arrnfitem[1]['vlritem']=$valor;
        $arrnfitem[1]['idprodserv']= $idprodservori;
        $arrnfitem[1]['idnf']=$idnf;
        $arrnfitem[1]['nfe']='Y';
        $arrnfitem[1]['tiponf']='V';
        $arrnfitem[1]['idempresa']=$row['idempresaori']; 
        $inidnfitem=cnf::inseredb($arrnfitem,'nfitem'); 
        $inidnfitem=$inidnfitem[0];
*/
        cnf::atualizavalornf($idnf);

        //inserir na lotecons
        $inslotecons = new Insert();
        $inslotecons->setTable("lotecons");
        $inslotecons->idlote=$idloteorigem;
        $inslotecons->idlotefracao=$idlotefracaoori;
        $inslotecons->idobjeto=$inidnfitem;
        $inslotecons->idempresa=$row['idempresaori'];
        $inslotecons->tipoobjeto='nfitem';
        $inslotecons->idtransacao=$idtransacao;
        $inslotecons->obs='Transferência Fiscal';
        $inslotecons->qtdd=$qtdprod;      
        $inidlotecons=$inslotecons->save();
}


function gerapedidoitem($qtdprod,$valor=0,$idprodservori,$idnf,$_idempresa,$idprodservformula=null){

       
       $idnatop=1;// venda de produção do estabelecimento

        //busca endereco do cliente ou fornecedor tipoendereco = sacado		
        $sqlp = "select e.uf,p.inscrest,ep.indiedest as indiedestempresa ,p.indiedest,p.idpessoa,f.tpnf,p.vendadireta,f.idvendedor,
             (select 1 from natop nt where  nt.natop like('%DEVOLU%') and nt.idnatop=f.idnatop ) as natopdev,
            (select 1 from natop nt where  nt.natop like('%OUTRA%') and nt.natop like('%SAIDA%')  and nt.idnatop=f.idnatop ) as natoprem
            from nf f,pessoa p, endereco e,empresa ep
        where p.idpessoa = f.idpessoafat
        and ep.idempresa=f.idempresa
        and e.idendereco = f.idenderecofat and f.idnf = ".$idnf;
        $resp = d::b()->query($sqlp) or die("Erro ao buscar endereco: ".mysql_error());
        $qtdrowsp= mysqli_num_rows($resp);
        $rowuf=mysqli_fetch_assoc($resp);
        $uf=$rowuf["uf"];
        $vendadireta=$rowuf["vendadireta"];
        if($qtdrowsp == 0){	
            //$aliqicms=18;
           // $uf="MG";
            die("Não foi encontrado a UF do cliente!!!");
        }
        
            if(!empty($uf)){
        
                $sqlaliq="select i.idaliqicms,i.aliq,ii.aliq as aliqicmsint
                        from aliqicmsuf a,aliqicms i,aliqicms ii
                        where ii.idaliqicms=a.idaliqicmsint
                        and i.idaliqicms = a.idaliqicms
                        and a.uf='".$uf."'";
                $resaliq=d::b()->query($sqlaliq);
                $rowaliq=mysqli_fetch_assoc($resaliq);
    
                //se não tiver IE pega produto com valor de aliquota de 18
                if($rowuf['indiedest'] == 9 and $rowuf['indiedestempresa'] !=2 and $uf !="MG" and $rowuf['tpnf']==1 and empty($rowuf['natopdev'])){ 
                    $sqlaliq18="select i.idaliqicms,i.aliq
                                    from aliqicms i
                                    where i.idaliqicms = 4";
                    $resaliq18=d::b()->query($sqlaliq18);
                    $rowaliq18=mysqli_fetch_assoc($resaliq18);
                    $aliqitem=$rowaliq18['idaliqicms'];
                }else{
                    $aliqitem=$rowaliq['idaliqicms'];
                }
                
                if($uf !="MG"){
                    $sc="SELECT cfop FROM cfop where status='ATIVO' and idnatop = ".$idnatop." and origem ='FORA' limit 1";
                    $strlocal='FORA';
                }else{
                    $sc="SELECT cfop FROM cfop where status='ATIVO' and idnatop = ".$idnatop." and origem ='DENTRO' limit 1";
                    $strlocal='DENTRO';
                }
                $rc=d::b()->query($sc);
                $rwc=mysqli_fetch_assoc($rc);
                $cfop=$rwc['cfop'];
            
        }else{
                die("Não foi possivel identificar o estado UF do cliente!!!");
        }
            
       $i=99977;

           $i=$i+1;
    
            $idprodserv=$idprodservori;
            $idprodservformula=$idprodservformula;
                  
     
                $_sql = "select ps.*                                    
                                from prodserv ps 
                            where ps.idprodserv =".$idprodserv;
    
                //echo $_sql;	
                $res = d::b()->query($_sql) or die($_sql."Erro ao retornar produto: ".mysql_error());
                $qtdrows1= mysqli_num_rows($res);
                $ufemp=traduzid("empresa","idempresa","uf",$_idempresa);
        
      
                  
                    $row = mysqli_fetch_assoc($res);
                    $prodservdescr= $row['descr'];

                
                   /* if(empty($valor) and !empty($row["vlrvenda"])){
                        $valor=$row["vlrvenda"];// seta como valor o valor do produto
                    }  */                 
                                     
                    if($ufemp==$uf /*or $rowuf['indiedest']==1*/){
                        if($row['isentomesmauf']=='N' and  $row['cst']==20){
                            $rowaliq["aliq"]=$rowaliq['aliqicmsint'];
                        }
                        if($row['cst']!=60 and $row['cst']!=41 and $row['cst']!=00  and $row['isentomesmauf']=='Y'){// se for 60 e devolução e deve permanecer o 60
                                $row['cst']=40;
                        }
                        if($row['cst']==00){// ser for 00 cobrar integral
                            $rowaliq["aliq"]=18;
                        }
                            
                    }
                    // nestas origems o icms e 4 % hermesp 31-08-2020
                    if($row['origem']==1 or $row['origem']==2 or $row['origem']==3){
                        $rowaliq["aliq"]=4;
                    }
    /*
                    if(!empty($idprodservformula)){
                        $_sqlf="select vlrvenda ,comissao from prodservformula where idprodservformula=".$idprodservformula;
                        $resf = d::b()->query($_sqlf) or die($_sqlf."Erro ao buscar formula: ".mysql_error());
                        $qtdformula=mysqli_num_rows($resf);
                        $rowf=mysqli_fetch_assoc($resf);
                        if($rowf["vlrvenda"]>0){
                            $valor=$rowf["vlrvenda"];//troca pelo valor e comissao da formula
                        }                     
                    }
            */       
    
                   $idtipoprodserv = traduzid('prodserv', 'idprodserv', 'idtipoprodserv', $idprodserv);
                   
                  
                    // montar o item para insert
    
                   if(empty($valor)){$valor=0.00;}
    
                    $infitem = new Insert();
                    $infitem->setTable("nfitem");
                    $infitem->qtd=$qtdprod;    
                    $infitem->idprodserv=$idprodserv;     
                    if(!empty($idprodservformula)){
                        $infitem->idprodservformula=$idprodservformula;
                    } 
                    $infitem->idtipoprodserv=$idtipoprodserv;
                    $infitem->idnf=$idnf;
                    $infitem->tiponf='V';
                    $infitem->nfe='Y';
                    $infitem->ncm=$row["ncm"];
                    $infitem->idempresa=$_idempresa;
                    $infitem->aliqbasecal=$row["reducaobc"];               
                    $infitem->cest=$row["cest"];
                    $infitem->cprod=$row["codprodserv"];
                    $infitem->prodservdescr= ($row["descrcurta"]) ? $row["descrcurta"] : $row["descr"];                
                    $infitem->un=$row["un"];
                    $infitem->aliqipi=$row["ipi"]; 
                    $infitem->aliqicms=$rowaliq["aliq"]; 
                    $infitem->aliqicmsint=$rowaliq["aliqicmsint"]; 
                    $infitem->aliqpis=$row["pis"]; 
                    $infitem->aliqcofins=$row["cofins"]; 
                    $infitem->iss=$row["iss"]; 
                    $infitem->finalidade=(string)$row["finalidade"];
                    $infitem->modbc=(string)$row["modbc"];
                    $infitem->origem=(string)$row["origem"];
                    $infitem->cst=(string)$row["cst"];
                    $infitem->piscst=(string)$row["piscst"];
                    $infitem->confinscst=(string)$row["confinscst"];
                    $infitem->ipint=(string)$row["ipint"];            
                    $infitem->vlrliq=$valor;            
                                
                     
                    if($rowuf['indiedest'] == 9 and $rowuf['indiedestempresa'] !=2 and $uf !="MG" and $rowuf['tpnf']==1 and  empty($rowuf['natopdev']) ){
                         
                        $infitem->indiedest=$rowuf["indiedest"];   
                        if(!empty($rowuf['natoprem'])){
                            if(!empty($cfop)){						   
                                $infitem->cfop=$cfop;
                            }
                        }else{
                              
                            $infitem->cfop='6107';  
                        }
                    }elseif(!empty($cfop)){
                        $infitem->cfop=$cfop; 
                    }
    
                    $Nidnfitem=$infitem->save();
    
                
    return $Nidnfitem;
       
      // print_r($_SESSION['arrpostbuffer']);
    //die;  

}

function novolotefiscal($idloteorigem,$row,$idprodservforn,$qtd,$qtd_exp = null,$fluxo)
{
   // print_r($row); die('hehe');
    $idprodserv =$row['idprodserv'];
    $idunidade=$row['idunidade'];
    $idempresa=$row['idempresa'];
    //LTM - 14-05-52021: Pegar o idfluxo
    $sqlm = "SELECT m.modulo
              FROM unidadeobjeto uo JOIN "._DBCARBON."._modulo m ON m.modulo = uo.idobjeto AND m.modulotipo = 'lote'
             WHERE uo.tipoobjeto = 'modulo' AND uo.idunidade = '$idunidade'";

    $resm = d::b()->query($sqlm);
    $rowm = mysqli_fetch_assoc($resm);
    $idfluxostatus = FluxoController::getIdFluxoStatus($rowm['modulo'], 'APROVADO');
    
  
    $fabricacao= traduzid('lote', 'idlote','fabricacao', $idloteorigem);
    $vencimento= traduzid('lote', 'idlote','vencimento', $idloteorigem);
    $partida= traduzid('lote', 'idlote','partida', $idloteorigem);
    $exercicio= traduzid('lote', 'idlote','exercicio', $idloteorigem);
    $fabricante= traduzid('lote', 'idlote','fabricante', $idloteorigem);    
    $vlrlote= traduzid('lote', 'idlote','vlrlote', $idloteorigem); 

    if($row['converteest']=='Y' and $row['valconv']>1){
        $qtd = $row['valconv'] * $qtd;
        $vlrlote = ($vlrlote / $row['valconv']);
    }

    $_arrlote = geraLote($idprodserv);
    $_numlote = $_arrlote[0].$_arrlote[1];
   
    $inspart = new Insert();
    $inspart->setTable("lote");   
    $inspart->idprodserv=$idprodserv;
   

    $inspart->idpartida=$_numlote;
    $inspart->partida=$_numlote;
    $inspart->exercicio=date("Y");;
    $inspart->partidaext=$partida.'/'.$exercicio;
    
    $inspart->fabricante=$fabricante;
    $inspart->vlrlote=$vlrlote;
    $inspart->spartida=$_arrlote[0];
    $inspart->npartida=$_arrlote[1];  
    $inspart->idempresa=$idempresa; 
    $inspart->idunidade=$idunidade; 
    $inspart->fabricacao=$fabricacao; 
    $inspart->vencimento=$vencimento; 
    $inspart->qtdprod=$qtd;
    $inspart->qtdprod_exp=$qtd_exp;
    $inspart->qtdpedida=$qtd;
    $inspart->qtdpedida_exp=$qtd_exp;
    $inspart->idfluxostatus = $idfluxostatus; 
    $inspart->status='APROVADO';  
    $idlote=$inspart->save();
    
    //LTM - 14-05-52021: Insere FluxoHist Lote        
    FluxoController::inserirFluxoStatusHist($row['modulo'], $idlote, $idfluxostatus, 'PENDENTE');

    return  $idlote;
}



function novolote($idloteorigem,$qtd,$qtd_exp,$idunidade,$idpessoa = null, $fluxo)
{
    //LTM - 14-05-52021: Pegar o idfluxo
    $sql = "SELECT m.modulo
              FROM unidadeobjeto uo JOIN "._DBCARBON."._modulo m ON m.modulo = uo.idobjeto AND m.modulotipo = 'lote'
             WHERE uo.tipoobjeto = 'modulo' AND uo.idunidade = '$idunidade'";

    $res = d::b()->query($sql);
    $row = mysqli_fetch_assoc($res);
    $idfluxostatus = FluxoController::getIdFluxoStatus($row['modulo'], 'ABERTO');
    
    $idprodserv= traduzid('lote', 'idlote','idprodserv', $idloteorigem);
    $fabricacao= traduzid('lote', 'idlote','fabricacao', $idloteorigem);
    $vencimento= traduzid('lote', 'idlote','vencimento', $idloteorigem);
    $_idempresa=traduzid('unidade', 'idunidade','idempresa',  $idunidade);
    $_arrlote = geraLote($idprodserv);
    $_numlote = $_arrlote[0].$_arrlote[1];
   
    $inspart = new Insert();
    $inspart->setTable("lote");   
    $inspart->idprodserv=$idprodserv;
    if(!empty($idpessoa)){
        $inspart->idpessoa=$idpessoa;
    }

    $inspart->idpartida=$_numlote;
    $inspart->partida=$_numlote;
    $inspart->exercicio=date("Y");;
    $inspart->spartida=$_arrlote[0];
    $inspart->npartida=$_arrlote[1]; 
    $inspart->idempresa= $_idempresa; 
    $inspart->idunidade=$idunidade; 
    $inspart->fabricacao=$fabricacao; 
    $inspart->vencimento=$vencimento; 
    $inspart->qtdprod=$qtd;
    $inspart->qtdprod_exp=$qtd_exp;
    $inspart->qtdpedida=$qtd;
    $inspart->qtdpedida_exp=$qtd_exp;
    $inspart->idfluxostatus = $idfluxostatus; 
    $inspart->status='ABERTO';  
    $idlote=$inspart->save();
    
    //LTM - 14-05-52021: Insere FluxoHist Lote        
    FluxoController::inserirFluxoStatusHist($row['modulo'], $idlote, $idfluxostatus, 'PENDENTE');

    return  $idlote;
}

//ATUALIZAR A QUANTIDADE ADICIONADA OU RETIRADA CONFORME A UNIDADE DE CONSUMO
if( !empty($_SESSION['arrpostbuffer']['x']['i']['lotecons']['idlote']) and !empty($_SESSION['arrpostbuffer']['x']['i']['lotecons']['idlotefracao'])){
	$idlotefracao=$_SESSION['arrpostbuffer']['x']['i']['lotecons']['idlotefracao'];
        
         $s="select u.convestoque,l.valconvori as valconv,l.converteest as convertido,l.vunpadrao
			from lotefracao f join lote l on(l.idlote=f.idlote)
            join unidade u on(u.idunidade=f.idunidade)
            join prodserv p on(p.idprodserv=l.idprodserv)
        where f.idlotefracao=".$idlotefracao;
        $re = d::b()->query($s) or die("Erro ao buscar informacoes da unidade de origem : ". mysql_error() . "<p>SQL: ".$s);  
        $rowconv=mysqli_fetch_assoc($re);
        $qtd=1;
        if($rowconv["valconv"]>1 and $rowconv["convestoque"]=='N' and $rowconv['convertido']=='Y'){                      
                        
            if($rowconv['vunpadrao']=='N'){
                $qtd= $rowconv["valconv"];
            }           
        } 

    $qtdc=$_SESSION['arrpostbuffer']['x']['i']['lotecons']['qtdc'];
    $qtdd=$_SESSION['arrpostbuffer']['x']['i']['lotecons']['qtdd'];
    
    if (strpos(strtolower($qtdc),"d") or strpos(strtolower($qtdc),"e") or 
            strpos(strtolower($qtdd),"d") or strpos(strtolower($qtdd),"e")){
                   
        if(empty($qtdc)){
            $qtd=$qtdc;
            $tipo='CREDITO';
            $_SESSION['arrpostbuffer']['x']['i']['lotecons']['qtdc']=$qtdc;
        }else{
            $qtd=$qtdd;
            $tipo='DEBITO';
            $_SESSION['arrpostbuffer']['x']['i']['lotecons']['qtdd']=$qtdd;
        }

    
    }else{

        $qtdc=tratanumero($_SESSION['arrpostbuffer']['x']['i']['lotecons']['qtdc']);
        $qtdd=tratanumero($_SESSION['arrpostbuffer']['x']['i']['lotecons']['qtdd']);

        if($qtdd>0 and $qtd>0){ 
            $_SESSION['arrpostbuffer']['x']['i']['lotecons']['qtdd']= $qtdd*$qtd;
        }elseif($qtdc >0 and $qtd>0){           
            $_SESSION['arrpostbuffer']['x']['i']['lotecons']['qtdc']= $qtdc*$qtd;
        }
    }              
	
   
}


$e_status=$_SESSION['arrpostbuffer']['x']['u']['lotefracao']['status'];
$e_idlotefracao=$_SESSION['arrpostbuffer']['x']['u']['lotefracao']['idlotefracao'];

if($e_status=='ESGOTADO' and !empty($e_idlotefracao)){
    $sql="select qtd,qtd_exp,idlote from lotefracao where idlotefracao=".$e_idlotefracao;
    $res = d::b()->query($sql) or die("Erro ao buscar informacoes da fracao para esgotar : ". mysql_error() . "<p>SQL: ".$sql);  
    $row=mysqli_fetch_assoc($res);
    if($row['qtd']>0){

        $_SESSION['arrpostbuffer']['es']['i']['lotecons']['idlote']=$row['idlote'];
        $_SESSION['arrpostbuffer']['es']['i']['lotecons']['idlotefracao']=$e_idlotefracao;
        $_SESSION['arrpostbuffer']['es']['i']['lotecons']['obs']='Esgotado';
        $_SESSION['arrpostbuffer']['es']['i']['lotecons']['qtdd']=recuperaExpoente(tratanumero($row['qtd']),$row['qtd_exp']);
        $_SESSION['arrpostbuffer']['es']['i']['lotecons']['idempresa']=$_SESSION["SESSAO"]["IDEMPRESA"];
        montatabdef(); 
    }


}

$L_status=$_SESSION['arrpostbuffer']['1']['u']['lote']['status'];
$e_idlote=$_SESSION['arrpostbuffer']['1']['u']['lote']['idlote'];
// ao cancelar ou esgotar o lote o restante deve ser inserido na lotecons como descartado
//@822807 - REPROVAÇÃO DE LOTES | CQ não esgostar lote reprovado
if(($L_status=='CANCELADO' /*OR $L_status=='REPROVADO'*/) and !empty($e_idlote)){

    
    $sql="select qtd,qtd_exp,idlote,idlotefracao from lotefracao where qtd>0 and  idlote=".$e_idlote;
    $res = d::b()->query($sql) or die("Erro ao buscar informacoes da fracao para esgotar : ". mysql_error() . "<p>SQL: ".$sql);  
    $l=8566;
    while($row=mysqli_fetch_assoc($res)){
        $l++;
        if($row['qtd']>0){

            $_SESSION['arrpostbuffer'][$l]['i']['lotecons']['idlote']=$row['idlote'];
            $_SESSION['arrpostbuffer'][$l]['i']['lotecons']['idlotefracao']=$row['idlotefracao'];
            $_SESSION['arrpostbuffer'][$l]['i']['lotecons']['obs']=$L_status;
            $_SESSION['arrpostbuffer'][$l]['i']['lotecons']['qtdd']=recuperaExpoente(tratanumero($row['qtd']),$row['qtd_exp']);
            montatabdef(); 
        }
    }
}

if(!empty($_SESSION['arrpostbuffer']['cvest']['u']['lote']['idlote'])){
    $cvestId = $_SESSION['arrpostbuffer']['cvest']['u']['lote']['idlote'];
    ($_SESSION['arrpostbuffer']['cvest']['u']['lote']['qtdprod'] == 0) ? $cvestQtd = 1 : $cvestQtd = $_SESSION['arrpostbuffer']['cvest']['u']['lote']['qtdprod'];

    if($_SESSION['arrpostbuffer']['cvest']['u']['lote']['valconvori'] == 0){
        $_SESSION['arrpostbuffer']['cvest']['u']['lote']['valconvori'] = 1;
        $cvestValconv = 1;
    }else{
        $cvestValconv = $_SESSION['arrpostbuffer']['cvest']['u']['lote']['valconvori'];
    }
    
    
    $sqcv ="SELECT round(ifnull((((ni.total+ifnull(ni.valipi,0)+ifnull(ni.frete,0))/ni.qtd)/".$cvestValconv."),0),2) as valoritem
            from lote l
            left join nfitem ni on(ni.idnfitem = l.idnfitem)
            where l.idlote=".$cvestId;
    $rescv = d::b()->query($sqcv) or die("saveprechange__loteprodução: Falha ao calcular volor do item");
    $rcv = mysqli_fetch_assoc($rescv);

    $_SESSION['arrpostbuffer']['cvest']['u']['lote']['vlrlote'] = $rcv["valoritem"];


    $converteest= $_SESSION['arrpostbuffer']['cvest']['u']['lote']['converteest'];
    if($converteest=='N'){
        $_SESSION['arrpostbuffer']['cvest']['u']['lote']['valconvori']=1;
    }
    
}

//Se o lote estiver ligado a uma formalização será retirado o status pois estão deixando as duas telas em aberto e não atualiza a do lote
// quando salvam o lote volta do status para o qual ficou parado.
$sFormalizacao = "SELECT idlote
			        FROM formalizacao
                   WHERE idlote = '".$_SESSION['arrpostbuffer']['1']['u']['lote']['idlote']."'";
$resFormalizacao = d::b()->query($sFormalizacao) or die("Erro ao buscar idlote Formalização: ". mysql_error() . "<p>SQL: ".$sFormalizacao);  
$rowFormalizacao = mysqli_num_rows($resFormalizacao);
if($rowFormalizacao > 0)
{
    unset($_SESSION['arrpostbuffer']['1']['u']['lote']['status']);
}

$novoFabricacao = $_SESSION['arrpostbuffer']['1']['u']['lote']['fabricacao'];
if($novoFabricacao != $_POST['fabricacao_old'] && !empty($novoFabricacao) && !empty($_POST['validade'])){
    $dataFormatada = DateTime::createFromFormat('d/m/Y', $novoFabricacao)->format('Y-m-d');
    $_SESSION['arrpostbuffer']['1']['u']['lote']['vencimento'] = date('d/m/Y', strtotime("+" . $_POST['validade'] . " MONTH", strtotime($dataFormatada)));
}

/*
Quando e feita um deslocamento de uma partidade de uma unidade para para outra
Se for excluido este consumo deve-se retirar o credito na fracao de destino
*/
/*
$_idlotecons =$_SESSION['arrpostbuffer']['x']['d']['lotecons']['idlotecons'];
if(!empty($_idlotecons)){
    $sql="select c.idlote,c.idobjeto,c.qtdd,c.idlotefracao,c.idlotecons,p.consometransf,u.consomeun
            from lotecons c 
                join lote l on(l.idlote=c.idlote )
                join prodserv p on(p.idprodserv=l.idprodserv)
                join lotefracao f on(f.idlotefracao = c.idobjeto)
                join unidade u on(u.idunidade = f.idunidade)
            where c.idlotecons=".$_idlotecons." 
            and c.tipoobjeto='lotefracao' 
            and c.qtdd>0";
    $res = d::b()->query($sql) or die("saveprechange__loteprodução: Falha ao buscar a operacao de debito");       
    $qtd = mysqli_num_rows($res);
   
  
        $row=mysqli_fetch_assoc($res);
    if($qtd>0 and $row['consometransf']=='N' and $row['consomeun']=='N' ){
            

            $sqlc="select * from lotecons 
                    where idlote = ".$row['idlote']."
                    and idlotefracao=".$row['idobjeto']."
                    and qtdc=".$row['qtdd']."
                    and idlotecons > ".$row['idlotecons'];
            $resc = d::b()->query($sqlc) or die("saveprechange__loteprodução: Falha ao buscar a operação de credito");       
            $qtdc = mysqli_num_rows($resc);
           
            if($qtdc>0){
                $rowc=mysqli_fetch_assoc($resc);
                
                $_SESSION['arrpostbuffer']['x1']['d']['lotecons']['idlotecons']=$rowc['idlotecons'];
            }else{// foi o primeiro credito ele so gera uma fração
                $sqlf="select * from lotefracao where idlotefracaoorigem=".$row['idlotefracao']." and qtdini=".$row['qtdd'];
                $resf= d::b()->query($sqlf) or die("saveprechange__loteprodução: Falha ao buscar fracao da operação de credito");       
                $qtdf = mysqli_num_rows($resf);
                //die($sqlf);
                if($qtdf>0){
                    $rowf=mysqli_fetch_assoc($resf);
                    $_SESSION['arrpostbuffer']['x1']['d']['lotefracao']['idlotefracao']=$rowf['idlotefracao'];
                }

        }

    }// if($qtd>0 and $row['consometransf']=='N' and $row['consomeun']=='N' ){
}//if(!empty($_idlotecons)){
*/
?>

