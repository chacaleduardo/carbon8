<?
//echo('entrou \n');


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("../inc/php/validaacesso.php");
require_once("../inc/php/functions.php");
require_once("../api/rhfolha/index.php");
require_once("../api/nf/index.php");
require_once("../model/evento.php");
require_once(__DIR__."/../form/controllers/fluxo_controller.php");


cnf::$idempresa = isset($_GET["_idempresa"])?$_GET["_idempresa"]:$_SESSION["SESSAO"]["IDEMPRESA"];
crh::$idempresa = isset($_GET["_idempresa"])?$_GET["_idempresa"]:$_SESSION["SESSAO"]["IDEMPRESA"];

$_idempresa = isset($_GET["_idempresa"])?$_GET["_idempresa"]:$_SESSION["SESSAO"]["IDEMPRESA"];

$tipo=$_GET['tipo'];
$idrhfolha=$_GET['idrhfolha'];
$idpessoa=$_GET['idpessoa'];
$idnf=$_GET['idnf'];
$call=$_GET['call'];
$inicio=$_GET['inicio'];
//echo('teste \n');
if(empty($tipo) or empty($idrhfolha)){
 die('Parâmetros insuficientes para gerar a nota.');
}

$arrrhf=getObjeto("rhfolha",$idrhfolha);

$sql="select * from rhfolhaitem i join pessoa ps on(ps.idpessoa=i.idpessoa and ps.contrato!='TERC') where i.idrhfolha=".$idrhfolha." and not exists (select 1  from vw8PessoaUnidadeRateio p  where p.idpessoa=i.idpessoa)";

$res=d::b()->query($sql) or die("Falha ao verificar se todos os colaboradores tem unidade.".$sql);
$qtdfunc=mysqli_num_rows($res);

if($qtdfunc>0){

    echo('Não é possível gerar a NF pois há funcionário sem unidade, favor corrigir o cadastro.');  

}else{
    if($call=='atualizar'){
        if(($tipo=="FERIAS"  or  $tipo=="13SALARIO") and  $arrrhf['tipofolha']=='FOLHA FERIAS' ){
            geraferias($idrhfolha,$inicio,$arrrhf,$tipo,$idpessoa);
        }elseif($tipo=="IRRF" and $arrrhf['tipofolha']=='FOLHA FERIAS' ){
            atualizarclt($idrhfolha,$arrrhf,$tipo,$idpessoa,$idnf,$arrrhf['tipofolha']);
        }else{
            atualizarclt($idrhfolha,$arrrhf,$tipo,$idpessoa,$idnf,$arrrhf['tipofolha']);
        }
        
    }else{
        if(($tipo=="FERIAS"  or $tipo=="13SALARIO" )and  $arrrhf['tipofolha']=='FOLHA FERIAS' ){
            geraferias($idrhfolha,$inicio,$arrrhf,$tipo,$idpessoa);
        }elseif($tipo=="IRRF" and $arrrhf['tipofolha']=='FOLHA FERIAS' ){
            geraclt($idrhfolha,$arrrhf,$tipo,$idpessoa,$arrrhf['tipofolha']);
        }else{
            geraclt($idrhfolha,$arrrhf,$tipo,$idpessoa,$arrrhf['tipofolha']);
        }
    }
    echo('ok');    

}


function atualizarclt($idrhfolha,$arrrhf,$tipo,$idpessoa=null,$idnf,$tipofolha=null){

    $tipoorc=$tipo;

    if($tipo!='PJ'){
        crh::dnfitemrhfolha($idnf);
    }

    if($tipo=='SALARIO' or $tipo=='PJ' or $tipo=='13SALARIO' or $tipo=='FERIAS'){
        crh::dtodosnfitemrhfolha($idnf);
    }

    $arrconfCP=cnf::getDadosConfContapagar($tipo);

    if($tipo=='VALE'){
        $s="select  DATE_ADD(LAST_DAY(DATE_SUB(LAST_DAY('".$arrrhf['datafim']."'), interval 1 month)), interval 15 day) as dtemissao";
        $red=d::b()->query($s);
        $rdt=mysqli_fetch_assoc($red);
    }else{
        $s="select DATE_ADD(LAST_DAY('".$arrrhf['datafim']."'),INTERVAL 6 DAY) as dtemissao";
        $red=d::b()->query($s);
        $rdt=mysqli_fetch_assoc($red);
    }

    if($tipo=='PJ'){        
        $sf=" select 
        p.idpessoa             
         from pessoa p,pessoacontato c
        where p.idpessoa = c.idpessoa
        and p.status='ATIVO'
        and p.idtipopessoa=5			
        and c.idcontato =".$idpessoa;
        $ref=d::b()->query($sf);
        $qtdf=mysqli_num_rows($ref);
        if($qtdf>0){
            $rof=mysqli_fetch_assoc($ref);
            $arrconfCP['idpessoa']=$rof['idpessoa'];
        }else{
            $arrconfCP['idpessoa']=$idpessoa;
        }        
    }
    
    if($tipo=='PJ'){
        $sql="select f.*,p.contrato,p.nomecurto
        from rhfolhaitem f 
        join pessoa p on(p.idpessoa=f.idpessoa)
        where idrhfolha=".$idrhfolha." and p.idpessoa=".$idpessoa;

    }elseif($tipo=='FGTSMA'){
        $sql="select f.*,p.contrato,p.nomecurto
        from rhfolhaitem f 
        join pessoa p on(p.idpessoa=f.idpessoa)
        join sgcargo c on(p.idsgcargo=c.idsgcargo and c.tipo = 'JOVEM APRENDIZ')
        where idrhfolha=".$idrhfolha." and p.contrato='CLT'";
    }elseif($tipo=='FGTS'){
        $sql="select f.*,p.contrato,p.nomecurto
        from rhfolhaitem f 
        join pessoa p on(p.idpessoa=f.idpessoa)
        join sgcargo c on(p.idsgcargo=c.idsgcargo and (c.tipo != 'JOVEM APRENDIZ' or c.tipo is null))
        where idrhfolha=".$idrhfolha." and p.contrato='CLT'";
    }elseif($tipo=='IRRF'){
        if($tipofolha=='FOLHA FERIAS'){
            $strand=" and f.idpessoa=".$idpessoa." " ;
        }
        $sql="select f.*,p.contrato,p.nomecurto
        from rhfolhaitem f 
        join pessoa p on(p.idpessoa=f.idpessoa)
        join sgcargo c on(p.idsgcargo=c.idsgcargo and (c.tipo != 'JOVEM APRENDIZ' or c.tipo is null))
        where idrhfolha=".$idrhfolha." ".$strand." and p.contrato='CLT'";
    }else{
        $sql="select f.*,p.contrato,p.nomecurto
        from rhfolhaitem f 
        join pessoa p on(p.idpessoa=f.idpessoa)
        where idrhfolha=".$idrhfolha." and p.contrato='CLT'";
    }
   
    $res=d::b()->query($sql) or die("Erro ao buscar colaboradores.".$sql);
    while($r=mysqli_fetch_assoc($res)){
        if($tipo=='SALARIO' or $tipo=='PJ' or $tipo=='13SALARIO' or $tipo=='FERIAS'){
           //$valor=crh::valorpagamento($r['idpessoa'], $arrrhf['datafim'],$arrrhf['tipofolha']);
            
            if($tipo=='PJ'){
                $arraevento=crh::eventospagamentoPJ($r['idpessoa'], $arrrhf['datafim'],$arrrhf['tipofolha']);
            }else{
                $arraevento=crh::eventospagamento($r['idpessoa'], $arrrhf['datafim'],$arrrhf['tipofolha']);
                if($tipo=='SALARIO'){
                    $arrContaitemTipoprod=crh::buscarRheventoFolhaIdcontatipoIdprodserv($r['idpessoa'],18);
                }elseif($tipo=='13SALARIO'){
                    $arrContaitemTipoprod=crh::buscarRheventoFolhaIdcontatipoIdprodserv($r['idpessoa'],427);
                }elseif($tipo=='FERIAS'){
                    $arrContaitemTipoprod=crh::buscarRheventoFolhaIdcontatipoIdprodserv($r['idpessoa'],36);
                }
            }
           
          

           while($rowevento=mysqli_fetch_assoc($arraevento)){
              
               $tvalor=$rowevento['soma']- $rowevento['reduz'];                       

               $arrnfitem=array();
               $arrnfitem[1]['qtd']=1;
               $arrnfitem[1]['vlritem']=$rowevento['soma'];
               $arrnfitem[1]['des']=$rowevento['reduz'];
               $arrnfitem[1]['total']=$tvalor;                
               $arrnfitem[1]['prodservdescr']=$rowevento['evento'];
                
                if($tipo=='PJ'){
                    if(!empty($rowevento['idcontaitem']) and !empty($rowevento['idtipoprodserv'])){
                        $arrnfitem[1]['idcontaitem']=$rowevento['idcontaitem'];
                        $arrnfitem[1]['idtipoprodserv']=$rowevento['idtipoprodserv'];
                    }
                }else{
                    if(!empty($arrContaitemTipoprod['idcontaitem']) and !empty( $arrContaitemTipoprod['idtipoprodserv'])){
                        $arrnfitem[1]['idcontaitem']=$arrContaitemTipoprod['idcontaitem'];
                        $arrnfitem[1]['idtipoprodserv']=$arrContaitemTipoprod['idtipoprodserv'];
                    }   
                }   
               $arrnfitem[1]['idpessoa']=$r['idpessoa'];
               $arrnfitem[1]['idobjetoitem']=$idrhfolha;
               $arrnfitem[1]['tipoobjetoitem']='rhfolha';
               $arrnfitem[1]['statusitem']='PENDENTE';
               $arrnfitem[1]['idconfcontapagar']=$arrconfCP['idconfcontapagar'];
               $arrnfitem[1]['dataitem']=$rdt['dtemissao'];
               $arrnfitem[1]['idnf']=$idnf;
               $arrnfitem[1]['nfe']='Y';
               $arrnfitem[1]['tiponf']='R';
               $arrnfitem[1]['idempresa']=cnf::$idempresa; 
               $inidnfitem=cnf::inseredb($arrnfitem,'nfitem'); 
               $inidnfitem=$inidnfitem[0];

               //echo('<br>'.$inidnfitem);
           }
           
        }else{
            if($tipo=='VALE'){
                $valor=crh::valorevento($r['idpessoa'], $arrrhf['datafim'],22,$arrrhf['tipofolha']);
                $arrContaitemTipoprod=crh::buscarRheventoFolhaIdcontatipoIdprodserv($r['idpessoa'],22);
            }elseif($tipo=='INSS'){
                if($tipofolha=='DECIMO TERCEIRO 2'){
                    $valor=crh::valorevento($r['idpessoa'], $arrrhf['datafim'],448,$arrrhf['tipofolha']);
                    $arrContaitemTipoprod=crh::buscarRheventoFolhaIdcontatipoIdprodserv($r['idpessoa'],448);
                }else{
                    $valor=crh::valorevento($r['idpessoa'], $arrrhf['datafim'],47,$arrrhf['tipofolha']);
                    $arrContaitemTipoprod=crh::buscarRheventoFolhaIdcontatipoIdprodserv($r['idpessoa'],47);
                }
            }elseif($tipo=='FGTS'  or $tipo=='FGTSMA' ){
                if($tipofolha=='DECIMO TERCEIRO 2'){
                    $valor=crh::valorevento($r['idpessoa'], $arrrhf['datafim'],449,$arrrhf['tipofolha']);
                    $arrContaitemTipoprod=crh::buscarRheventoFolhaIdcontatipoIdprodserv($r['idpessoa'],449);
                }else{
                    $valor=crh::valorevento($r['idpessoa'], $arrrhf['datafim'],430,$arrrhf['tipofolha']);
                    $arrContaitemTipoprod=crh::buscarRheventoFolhaIdcontatipoIdprodserv($r['idpessoa'],430);
                }
            }elseif($tipo=='IRRF' and $tipofolha=='FOLHA FERIAS'){
                $valor=crh::valorevento($r['idpessoa'], $arrrhf['datafim'],29,$arrrhf['tipofolha']);
                $arrContaitemTipoprod=crh::buscarRheventoFolhaIdcontatipoIdprodserv($r['idpessoa'],29);
            }elseif($tipo=='IRRF' ){
                if($tipofolha=='DECIMO TERCEIRO 2'){
                    $valor=crh::valorevento($r['idpessoa'], $arrrhf['datafim'],447,$arrrhf['tipofolha']);
                    $arrContaitemTipoprod=crh::buscarRheventoFolhaIdcontatipoIdprodserv($r['idpessoa'],447);
                }else{
                    $valor=crh::valorevento($r['idpessoa'], $arrrhf['datafim'],48,$arrrhf['tipofolha']);
                    $arrContaitemTipoprod=crh::buscarRheventoFolhaIdcontatipoIdprodserv($r['idpessoa'],48);
                }
            }elseif($tipo=='CONSIGNADO'){
                $valor=crh::valorevento($r['idpessoa'], $arrrhf['datafim'],19,$arrrhf['tipofolha']);
                $arrContaitemTipoprod=crh::buscarRheventoFolhaIdcontatipoIdprodserv($r['idpessoa'],19);
            }

            $arvalnfitem=cnf::buscanfitem($idnf,$r['idpessoa']);

            if($valor>0 and  $arvalnfitem==0){      
                $arrnfitem=array();
                $arrnfitem[1]['qtd']=1;
                $arrnfitem[1]['vlritem']=$valor;
                $arrnfitem[1]['total']=$valor;
                $arrnfitem[1]['prodservdescr']=$r['nomecurto'];
                if(!empty( $arrContaitemTipoprod['idcontaitem']) and !empty( $arrContaitemTipoprod['idtipoprodserv'])){
                    $arrnfitem[1]['idcontaitem']=$arrContaitemTipoprod['idcontaitem'];
                    $arrnfitem[1]['idtipoprodserv']=$arrContaitemTipoprod['idtipoprodserv'];
                }                
                $arrnfitem[1]['idpessoa']=$r['idpessoa'];
                $arrnfitem[1]['idobjetoitem']=$idrhfolha;
                $arrnfitem[1]['tipoobjetoitem']='rhfolha';
                $arrnfitem[1]['statusitem']='PENDENTE';
                $arrnfitem[1]['idconfcontapagar']=$arrconfCP['idconfcontapagar'];
                $arrnfitem[1]['dataitem']=$rdt['dtemissao'];
                $arrnfitem[1]['idnf']=$idnf;
                $arrnfitem[1]['tiponf']='R';
                $arrnfitem[1]['nfe']='Y';
                $arrnfitem[1]['idempresa']=cnf::$idempresa; 
                $inidnfitem=cnf::inseredb($arrnfitem,'nfitem'); 
                $inidnfitem=$inidnfitem[0];
            }elseif($arvalnfitem['total']!=$valor and $arvalnfitem!=0){
                cnf::atualizanfitem($arvalnfitem['idnfitem'],$valor,$arrContaitemTipoprod['idcontaitem'],$arrContaitemTipoprod['idtipoprodserv']);
            } 
        } 
    }

    cnf::atualizavalornf($idnf);

    cnf::atualizafat($idnf);

    cnf:: geraRateio($idnf);
}

function geraclt($idrhfolha,$arrrhf,$tipo,$idpessoa=null,$tipofolha=null){

    $arrconfCP=cnf::getDadosConfContapagar($tipo);

    if($tipo=='VALE'){
        $s="select  DATE_ADD(LAST_DAY(DATE_SUB(LAST_DAY('".$arrrhf['datafim']."'), interval 1 month)), interval 15 day) as dtemissao";
        $red=d::b()->query($s);
        $rdt=mysqli_fetch_assoc($red);
    }else{
        $s="select DATE_ADD(LAST_DAY('".$arrrhf['datafim']."'),INTERVAL 6 DAY) as dtemissao";
        $red=d::b()->query($s);
        $rdt=mysqli_fetch_assoc($red);
    }

   if($tipo=='PJ'){
        
        $sf=" select 
        p.idpessoa             
         from pessoa p,pessoacontato c
        where p.idpessoa = c.idpessoa
        and p.status='ATIVO'
        and p.idtipopessoa=5			
        and c.idcontato =".$idpessoa;
        $ref=d::b()->query($sf);
        $qtdf=mysqli_num_rows($ref);
        if($qtdf>0){
            $rof=mysqli_fetch_assoc($ref);
            $arrconfCP['idpessoa']=$rof['idpessoa'];
        }else{
            $arrconfCP['idpessoa']=$idpessoa;
        }
    }

    $qrUnid = "SELECT idunidade FROM unidade WHERE idtipounidade = 14 AND idempresa = ".cnf::$idempresa;
    $rsUnid = d::b()->query($qrUnid) or die("[geranotarh][1]: Erro ao buscar idunidade. SQL: ".$qrUnid);
    $rwUnid = mysqli_fetch_assoc($rsUnid);

    //LTM - 05-04-2021: Retorna o Idfluxo nf para comprasrh (Tipo R)
    $idfluxostatus = FluxoController::getIdFluxoStatus('comprasrh', 'PREVISAO');
    $arrinsnf=array();
    $arrinsnf[1]['idpessoa']=$arrconfCP['idpessoa'];		
    $arrinsnf[1]['dtemissao']=$arrrhf['datafim']." 00:00:00";
    $arrinsnf[1]['tiponf']='R';
    $arrinsnf[1]['idunidade']=$rwUnid['idunidade'];
    $arrinsnf[1]['geracontapagar']='Y';
    $arrinsnf[1]['status']='PREVISAO';
    $arrinsnf[1]['idfluxostatus'] = $idfluxostatus;
    $arrinsnf[1]['tipoorc']=$tipo;
    $arrinsnf[1]['parcelas']=1;
    $arrinsnf[1]['idobjetosolipor']=$idrhfolha;
    $arrinsnf[1]['tipoobjetosolipor']='rhfolha';
    $arrinsnf[1]['diasentrada']=1;	
    $arrinsnf[1]['idempresa']=cnf::$idempresa; 				
    $arrinsnf[1]['idformapagamento']=$arrconfCP['idformapagamento'];

    $idnf=cnf::inseredb($arrinsnf,'nf');
    $idnf=$idnf[0];

    //LTM - 05-04-2021: Insere o fluxo
    FluxoController::inserirFluxoStatusHist('comprasrh', $idnf, $idfluxostatus, 'PENDENTE');

/*
    $sqlFluxo = "SELECT fs.idfluxostatus, f.idfluxo,
                (SELECT idfluxostatushist FROM fluxostatushist fh WHERE fh.idmodulo = n.idnf AND fh.modulo = f.modulo 
            ORDER BY idfluxostatushist DESC LIMIT 1) AS idfluxostatushist
            FROM nf n
            JOIN fluxo f ON f.modulo = 'comprasrh' AND f.status = 'ATIVO'
            JOIN fluxostatus fs ON f.idfluxo = fs.idfluxo
            JOIN "._DBCARBON."._status s ON fs.idstatus = s.idstatus AND s.statustipo = 'INICIO'
            WHERE n.idnf = ".$idnf;

            $resFluxo = d::b()->query($sqlFluxo) or die(mysqli_error(d::b())." erro ao buscar o numero do recibo ".$sql);
            $rowFluxo = mysqli_fetch_assoc($resFluxo);
            */
            //Atualiza o Fluxo
            FluxoController::verificarInicio('comprasrh', 'idnf', $idnf);
    
    if($tipo=='PJ'){
        $sql="select f.*,p.contrato,p.nomecurto
        from rhfolhaitem f 
        join pessoa p on(p.idpessoa=f.idpessoa)
        where idrhfolha=".$idrhfolha." and p.idpessoa=".$idpessoa;

    }elseif($tipo=='FGTSMA'){
        $sql="select f.*,p.contrato,p.nomecurto
        from rhfolhaitem f 
        join pessoa p on(p.idpessoa=f.idpessoa)
        join sgcargo c on(p.idsgcargo=c.idsgcargo and c.tipo = 'JOVEM APRENDIZ')
        where idrhfolha=".$idrhfolha." and p.contrato='CLT'";
    }elseif($tipo=='FGTS'){
        $sql="select f.*,p.contrato,p.nomecurto
        from rhfolhaitem f 
        join pessoa p on(p.idpessoa=f.idpessoa)
        join sgcargo c on(p.idsgcargo=c.idsgcargo and  (c.tipo != 'JOVEM APRENDIZ' or c.tipo is null))
        where idrhfolha=".$idrhfolha." and p.contrato='CLT'";
    }elseif($tipo=='IRRF'){
        if($tipofolha=='FOLHA FERIAS'){
            $strand=" and f.idpessoa=".$idpessoa." " ;
        }
        $sql="select f.*,p.contrato,p.nomecurto
        from rhfolhaitem f 
        join pessoa p on(p.idpessoa=f.idpessoa)
        join sgcargo c on(p.idsgcargo=c.idsgcargo and  (c.tipo != 'JOVEM APRENDIZ' or c.tipo is null))
        where idrhfolha=".$idrhfolha." ".$strand." and p.contrato='CLT'";
    }else{
        $sql="select f.*,p.contrato,p.nomecurto
        from rhfolhaitem f 
        join pessoa p on(p.idpessoa=f.idpessoa)
        where idrhfolha=".$idrhfolha." and p.contrato='CLT'";
    }
   //echo( $sql);
    $res=d::b()->query($sql) or die("Erro ao buscar colaboradores.".$sql);
    while($r=mysqli_fetch_assoc($res)){
        if($tipo=='SALARIO' or $tipo=='PJ'  or $tipo=='13SALARIO' or $tipo=='FERIAS'){
           //$valor=crh::valorpagamento($r['idpessoa'], $arrrhf['datafim'],$arrrhf['tipofolha']);
           
            if($tipo=='PJ'){
                $arraevento=crh::eventospagamentoPJ($r['idpessoa'], $arrrhf['datafim'],$arrrhf['tipofolha']);
            }else{
                $arraevento=crh::eventospagamento($r['idpessoa'], $arrrhf['datafim'],$arrrhf['tipofolha']);
                if($tipo=='SALARIO'){
                    $arrContaitemTipoprod=crh::buscarRheventoFolhaIdcontatipoIdprodserv($r['idpessoa'],18);
                }elseif($tipo=='13SALARIO'){
                    $arrContaitemTipoprod=crh::buscarRheventoFolhaIdcontatipoIdprodserv($r['idpessoa'],427);
                }elseif($tipo=='FERIAS'){
                    $arrContaitemTipoprod=crh::buscarRheventoFolhaIdcontatipoIdprodserv($r['idpessoa'],36);
                }
                
            }

            

            while($rowevento=mysqli_fetch_assoc($arraevento)){
               
                $tvalor=$rowevento['soma']- $rowevento['reduz'];                       

                $arrnfitem=array();
                $arrnfitem[1]['qtd']=1;
                $arrnfitem[1]['vlritem']=$rowevento['soma'];
                $arrnfitem[1]['des']=$rowevento['reduz'];
                $arrnfitem[1]['total']=$tvalor;                
                $arrnfitem[1]['prodservdescr']=$rowevento['evento'];
                if($tipo=='PJ'){
                    if(!empty($rowevento['idcontaitem']) and !empty($rowevento['idtipoprodserv'])){
                        $arrnfitem[1]['idcontaitem']=$rowevento['idcontaitem'];
                        $arrnfitem[1]['idtipoprodserv']=$rowevento['idtipoprodserv'];
                    }
                }else{
                    if(!empty($arrContaitemTipoprod['idcontaitem']) and !empty( $arrContaitemTipoprod['idtipoprodserv'])){
                        $arrnfitem[1]['idcontaitem']=$arrContaitemTipoprod['idcontaitem'];
                        $arrnfitem[1]['idtipoprodserv']=$arrContaitemTipoprod['idtipoprodserv'];
                    }   
                }   
                $arrnfitem[1]['idpessoa']=$r['idpessoa'];
                $arrnfitem[1]['idobjetoitem']=$idrhfolha;
                $arrnfitem[1]['tipoobjetoitem']='rhfolha';
                $arrnfitem[1]['statusitem']='PENDENTE';
                $arrnfitem[1]['idconfcontapagar']=$arrconfCP['idconfcontapagar'];
                $arrnfitem[1]['dataitem']=$rdt['dtemissao'];
                $arrnfitem[1]['idnf']=$idnf;
                $arrnfitem[1]['nfe']='Y';
                $arrnfitem[1]['tiponf']='R';
                $arrnfitem[1]['idempresa']=cnf::$idempresa; 
                $inidnfitem=cnf::inseredb($arrnfitem,'nfitem'); 
                $inidnfitem=$inidnfitem[0];

               // echo('<br>'.$inidnfitem);
            }


        }else{
        
            if($tipo=='VALE'){
                $valor=crh::valorevento($r['idpessoa'], $arrrhf['datafim'],22,$arrrhf['tipofolha']);
                $arrContaitemTipoprod=crh::buscarRheventoFolhaIdcontatipoIdprodserv($r['idpessoa'],19);
            }elseif($tipo=='INSS'){
                if($tipofolha=='DECIMO TERCEIRO 2'){
                    $valor=crh::valorevento($r['idpessoa'], $arrrhf['datafim'],448,$arrrhf['tipofolha']);
                    $arrContaitemTipoprod=crh::buscarRheventoFolhaIdcontatipoIdprodserv($r['idpessoa'],448);
                }else{
                    $valor=crh::valorevento($r['idpessoa'], $arrrhf['datafim'],47,$arrrhf['tipofolha']);
                    $arrContaitemTipoprod=crh::buscarRheventoFolhaIdcontatipoIdprodserv($r['idpessoa'],47);
                }
            }elseif($tipo=='FGTS' or $tipo=='FGTSMA'){
                if($tipofolha=='DECIMO TERCEIRO 2'){
                    $valor=crh::valorevento($r['idpessoa'], $arrrhf['datafim'],449,$arrrhf['tipofolha']);
                    $arrContaitemTipoprod=crh::buscarRheventoFolhaIdcontatipoIdprodserv($r['idpessoa'],449);
                }else{
                    $valor=crh::valorevento($r['idpessoa'], $arrrhf['datafim'],430,$arrrhf['tipofolha']);
                    $arrContaitemTipoprod=crh::buscarRheventoFolhaIdcontatipoIdprodserv($r['idpessoa'],430);
                }
            
            }elseif($tipo=='IRRF' and $tipofolha=='FOLHA FERIAS'){
                $valor=crh::valorevento($r['idpessoa'], $arrrhf['datafim'],29,$arrrhf['tipofolha']);
                $arrContaitemTipoprod=crh::buscarRheventoFolhaIdcontatipoIdprodserv($r['idpessoa'],29);
            }elseif($tipo=='IRRF'){
                if($tipofolha=='DECIMO TERCEIRO 2'){
                    $valor=crh::valorevento($r['idpessoa'], $arrrhf['datafim'],447,$arrrhf['tipofolha']);
                    $arrContaitemTipoprod=crh::buscarRheventoFolhaIdcontatipoIdprodserv($r['idpessoa'],447);
                }else{
                    $valor=crh::valorevento($r['idpessoa'], $arrrhf['datafim'],48,$arrrhf['tipofolha']);
                    $arrContaitemTipoprod=crh::buscarRheventoFolhaIdcontatipoIdprodserv($r['idpessoa'],48);
                }
            }elseif($tipo=='CONSIGNADO'){
                $valor=crh::valorevento($r['idpessoa'], $arrrhf['datafim'],19,$arrrhf['tipofolha']);
                $arrContaitemTipoprod=crh::buscarRheventoFolhaIdcontatipoIdprodserv($r['idpessoa'],19);
            }
        if($valor>0){      
                $arrnfitem=array();
                $arrnfitem[1]['qtd']=1;
                $arrnfitem[1]['vlritem']=$valor;
                $arrnfitem[1]['total']=$valor;
                $arrnfitem[1]['prodservdescr']=$r['nomecurto'];
                if(!empty($arrContaitemTipoprod['idcontaitem']) and !empty( $arrContaitemTipoprod['idtipoprodserv'])){
                    $arrnfitem[1]['idcontaitem']=$arrContaitemTipoprod['idcontaitem'];
                    $arrnfitem[1]['idtipoprodserv']=$arrContaitemTipoprod['idtipoprodserv'];
                }    
                $arrnfitem[1]['idpessoa']=$r['idpessoa'];
                $arrnfitem[1]['idobjetoitem']=$idrhfolha;
                $arrnfitem[1]['tipoobjetoitem']='rhfolha';
                $arrnfitem[1]['statusitem']='PENDENTE';
                $arrnfitem[1]['idconfcontapagar']=$arrconfCP['idconfcontapagar'];
                $arrnfitem[1]['dataitem']=$rdt['dtemissao'];
                $arrnfitem[1]['idnf']=$idnf;
                $arrnfitem[1]['nfe']='Y';
                $arrnfitem[1]['tiponf']='R';
                $arrnfitem[1]['idempresa']=cnf::$idempresa; 
                $inidnfitem=cnf::inseredb($arrnfitem,'nfitem'); 
                $inidnfitem=$inidnfitem[0];
            }  
        }
    }

    $arrinsnfcp=array();
    $arrinsnfcp[1]['idnf']=$idnf;	
    $arrinsnfcp[1]['parcela']=1;
    $arrinsnfcp[1]['idformapagamento']=$arrconfCP['idformapagamento'];
    $arrinsnfcp[1]['proporcao']=100;
    $arrinsnfcp[1]['datareceb']=$rdt['dtemissao'];    

    $idnfconfpagar=cnf::inseredb($arrinsnfcp,'nfconfpagar');

    cnf::atualizavalornf($idnf);

    cnf::gerarContapagar($idnf);

    cnf::agrupaCP(); 

   // cnf:: geraRateio($idnf);
}//function geraclt($idrhfolha,$arrrhf,$tipo,$idpessoa=null){

function geraferias($idrhfolha,$inicio,$arrrhf,$tipo,$idpessoa=null){
//echo('entrou ferias');

    $arrconfCP=cnf::getDadosConfContapagar($tipo);

    $sx="select 
                inicio,idrhfolha,idpessoa
            from rhfolhaitem 
            where idrhfolha=".$idrhfolha."
            and inicio  ='".$inicio."' 
            group by inicio";
    $redx=d::b()->query($sx);
    while($rdtx=mysqli_fetch_assoc($redx)){        

       // $s="select DATE_SUB('".$rdtx['inicio']."',INTERVAL 2 DAY) as dtemissao";
        $s="select '".$rdtx['inicio']."' as dtemissao";
        $red=d::b()->query($s);
        $rdt=mysqli_fetch_assoc($red);

        $sqln="select * from nf
                where status='PREVISAO' 
                and idpessoa = ".$arrconfCP['idpessoa']."
                and dtemissao = '".$rdt['dtemissao']." 00:00:00'
                and tipoorc = '".$tipo."'
                and idobjetosolipor=".$idrhfolha."
                and tipoobjetosolipor ='rhfolha'";
        $resn=d::b()->query($sqln);
        $qtdn=mysqli_num_rows($resn);
        if($qtdn<1){

        $qrUnid = "SELECT idunidade FROM unidade WHERE idtipounidade = 14 AND idempresa = ".cnf::$idempresa;
        $rsUnid = d::b()->query($qrUnid) or die("[geranotarh][2]: Erro ao buscar idunidade. SQL: ".$qrUnid);
        $rwUnid = mysqli_fetch_assoc($rsUnid);

        //LTM - 05-04-2021: Retorna o Idfluxo nf para comprasrh (Tipo R)
        $idfluxostatus = FluxoController::getIdFluxoStatus('comprasrh', 'PREVISAO');

        $arrinsnf[1]['idpessoa']=$arrconfCP['idpessoa'];		
        $arrinsnf[1]['dtemissao']=$arrrhf['datafim']." 00:00:00";
        $arrinsnf[1]['tiponf']='R';
        $arrinsnf[1]['idunidade']=$rwUnid['idunidade'];
        $arrinsnf[1]['geracontapagar']='Y';
        $arrinsnf[1]['status']='PREVISAO';
        $arrinsnf[1]['idfluxostatus'] = $idfluxostatus;
        $arrinsnf[1]['tipoorc']=$tipo;
        $arrinsnf[1]['parcelas']=1;
        $arrinsnf[1]['idobjetosolipor']=$idrhfolha;
        $arrinsnf[1]['tipoobjetosolipor']='rhfolha';
        $arrinsnf[1]['idempresa']=cnf::$idempresa; 
        $arrinsnf[1]['diasentrada']=1;					
        $arrinsnf[1]['idformapagamento']=$arrconfCP['idformapagamento'];

        $idnf=cnf::inseredb($arrinsnf,'nf');
        $idnf=$idnf[0];

        //LTM - 05-04-2021: Insere o fluxo
        FluxoController::inserirFluxoStatusHist('comprasrh', $idnf, $idfluxostatus, 'PENDENTE');

        FluxoController::verificarInicio('comprasrh', 'idnf', $idnf);

        $arrinsnfcp[1]['idnf']=$idnf;	
        $arrinsnfcp[1]['parcela']=1;
        $arrinsnfcp[1]['idformapagamento']=$arrconfCP['idformapagamento'];
        $arrinsnfcp[1]['proporcao']=100;
        $arrinsnfcp[1]['datareceb']=$rdt['dtemissao'];    

        $idnfconfpagar=cnf::inseredb($arrinsnfcp,'nfconfpagar');
        }else{
            $rown=mysqli_fetch_assoc($resn);
            $idnf= $rown['idnf'];

        }
   

        crh::dnfitemrhfolha($idnf);


        $sql="select f.*,p.contrato,p.nomecurto
        from rhfolhaitem f 
        join pessoa p on(p.idpessoa=f.idpessoa)
        where idrhfolha=".$idrhfolha." and f.inicio='".$rdtx['inicio']."' and p.contrato='CLT'";
        
        //echo( $sql);
        $res=d::b()->query($sql) or die("Erro ao buscar colaboradores.".$sql);
        while($r=mysqli_fetch_assoc($res)){
            if($tipo=='13SALARIO'){//adiantamento 13
                $valor=crh::valorevento($r['idpessoa'], $arrrhf['datafim'],473,$arrrhf['tipofolha']);
                $valor2=crh::valorevento($r['idpessoa'], $arrrhf['datafim'],39,$arrrhf['tipofolha']);
                $arrContaitemTipoprod=crh::buscarRheventoFolhaIdcontatipoIdprodserv($r['idpessoa'],473);
                if($valor2>0){
                    $valor= $valor+ $valor2;
                }
            }else{
                $arrContaitemTipoprod=crh::buscarRheventoFolhaIdcontatipoIdprodserv($r['idpessoa'],42);
                $valor=crh::valorpagamento($r['idpessoa'], $arrrhf['datafim'],$arrrhf['tipofolha']);
            }
            $arvalnfitem=cnf::buscanfitem($idnf,$r['idpessoa']);

            if($valor>0 and  $arvalnfitem==0){      
                $arrnfitem=array();
                $arrnfitem[1]['qtd']=1;
                $arrnfitem[1]['vlritem']=$valor;
                $arrnfitem[1]['total']=$valor;
                $arrnfitem[1]['prodservdescr']=$r['nomecurto'];
                if(!empty( $arrContaitemTipoprod['idcontaitem']) and !empty( $arrContaitemTipoprod['idtipoprodserv'])){
                    $arrnfitem[1]['idcontaitem']=$arrContaitemTipoprod['idcontaitem'];
                    $arrnfitem[1]['idtipoprodserv']=$arrContaitemTipoprod['idtipoprodserv'];
                }    
                $arrnfitem[1]['idpessoa']=$r['idpessoa'];
                $arrnfitem[1]['idobjetoitem']=$idrhfolha;
                $arrnfitem[1]['tipoobjetoitem']='rhfolha';
                $arrnfitem[1]['statusitem']='PENDENTE';
                $arrnfitem[1]['idconfcontapagar']=$arrconfCP['idconfcontapagar'];
                $arrnfitem[1]['dataitem']=$rdt['dtemissao'];
                $arrnfitem[1]['idnf']=$idnf;
                $arrnfitem[1]['nfe']='Y';
                $arrnfitem[1]['tiponf']='R';
                $arrnfitem[1]['idempresa']=cnf::$idempresa; 
                $inidnfitem=cnf::inseredb($arrnfitem,'nfitem'); 
                $inidnfitem=$inidnfitem[0];
            }elseif($arvalnfitem['total']!=$valor and $arvalnfitem!=0){
                cnf::atualizanfitem($arvalnfitem['idnfitem'],$valor,$arrContaitemTipoprod['idcontaitem'],$arrContaitemTipoprod['idtipoprodserv']);
            }  
        }

        cnf::atualizavalornf($idnf);

        cnf::atualizafat($idnf);
        cnf:: geraRateio($idnf);
      
    }//while($rdtx=mysqli_fetch_assoc($redx)){ grupo de datas
}//function geraferias($idrhfolha,$arrrhf,$tipo,$idpessoa=null){
?>