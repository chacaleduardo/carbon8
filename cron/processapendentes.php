<?
//ini_set("display_errors","1");
//error_reporting(E_ALL);
session_start();
$sessionid = session_id();//PEGA A SESSÃO 

if (defined('STDIN')){//se estiver sendo executao em linhade comando

	require_once("/var/www/carbon8/inc/php/functions.php");


}else{//se estiver seno executao via requisicao http
	require_once("../inc/php/functions.php");
	
}

require_once(__DIR__."/../form/controllers/fluxo_controller.php");

$grupo = rstr(8);

re::dis()->hMSet('cron:processapendentes',['inicio' => Date('d/m/Y H:i:s')]);

$sqli = "INSERT INTO `laudo`.`log` (`idempresa`, `sessao`, `tipoobjeto`, `idobjeto`, `tipolog`, `log`, `status`, `criadoem`, `data`) 
							VALUES ('1', '".$grupo ."','cron', 'processapendentes', 'status', 'INICIO', 'SUCESSO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";

d::b()->query($sqli) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqli);

function truncarNumero($numero) {
    return floor($numero * 100) / 100;
}
//funcoes para gerar guias de imposto
function imposto($inpost,$numeronfe,$invalor,$dtemissao,$idnotafiscal,$tipo,$obs){
   // print_r($inpost);
   // echo('entrou no imposto<br>');
	$rot='NFs ('.$numeronfe.') -';
	$_idempresa=$inpost['idempresa'];
    
    $arrconfCP=getDadosConfContapagarNFS($tipo,$_idempresa);
    echo('<br>Buscou configurações do imposto');
    //print_r($arrconfCP); die;
    if(!empty($arrconfCP['idpessoa'])){
        $idpessoa=$arrconfCP['idpessoa'];
    }else{
        $idpessoa=$inpost['idpessoa'];
    } 
    if(!empty($idpessoa)){
        $nomep= traduzid("pessoa","idpessoa","nome",$idpessoa);
    }

    if(!empty($obs)){
        $prodservdescr=$rot.''.$obs;
    }else{
        $prodservdescr=$rot.''.$nomep;
    }

    $sql="select * from nfitem 
            where idobjetoitem = ".$idnotafiscal." 
            and tipoobjetoitem = 'notafiscal' 
            and idconfcontapagar=".$arrconfCP['idconfcontapagar'];
     

    $resf=d::b()->query($sql) or die("[processapendentes] - Erro ao buscar se ja exite o item do imposto sql=".$sql." mysql".mysqli_error(d::b()));      
    $Vqtdnfitem=mysqli_num_rows($resf);
    
    if($Vqtdnfitem<1){
        echo('<br>Não foi criado atualizar.');
        $arrnfitem=montaarrnfitem($inpost,$arrconfCP,$invalor,$invalor,$prodservdescr);  
        echo('<br>Montou NFitem.');
        $ndtemissao = $inpost["emissao"];
        if(empty($arrconfCP['diavenc'])){
            $arrconfCP['diavenc']=1;
        }

        if($arrconfCP['vigente']=='Y'){
            $vencimentocalc = " SELECT ('".$ndtemissao."' + INTERVAL ".$arrconfCP['diavenc']." DAY) as dataitem ";
        }else{
            $vencimentocalc = " SELECT (LAST_DAY('".$ndtemissao."') + INTERVAL ".$arrconfCP['diavenc']." DAY) as dataitem ";
        }
        
        $resvenc=d::b()->query($vencimentocalc) or die("Erro ao buscar vencimento do montaarrnfitem: sql=".$vencimentocalc." mysql".mysqli_error(d::b()));
        $rowvenc=mysqli_fetch_assoc($resvenc);

        $arrnfitem[1]['dataitem']=$rowvenc['dataitem'];

        $inidnfitem=inseredb($arrnfitem,'nfitem'); 
        $inidnfitem=$inidnfitem[0];
        
      	$inidnf=agrupaNfitem($inidnfitem);
          echo('<br>Agrupou NFitem.');
        if (!is_numeric($inidnf)) {
            echo($inidnf); //die();
        }
        atualizavalornf($inidnf);
        echo('<br>Atualizou valor nf.');
        gerarContapagar($inidnf);
        echo('<br>Gerou Fatura.');
        atualizafat($inidnf);
        echo('<br>Atualizou fatura.');
        agrupaCP(); 
        echo('<br>Agrupou fatura.');
    }else{
        echo('<br>Ja foi criado atualizar.');
        $rowf=mysqli_fetch_assoc($resf);

        if(empty($rowf['idnf'])){
            $rowf['idnf']=agrupaNfitem($rowf['idnfitem']);
            echo('<br>Agrupou nfitem.');
        }

        $arrParcelas= recuperaParcelas($rowf['idnf'],'QUITADO','nf');//Contapagar Quitado
        $qtParcelas =$arrParcelas['quant'];
        echo('<br>Recuperou fatura.');

        $arrconfCP=getDadosConfContapagarNFS($tipo,$_idempresa);
        echo('<br>Recuperou Configuração a pagar.');
        $ndtemissao = $inpost["emissao"];;
        if(empty($arrconfCP['diavenc'])){
            $arrconfCP['diavenc']=1;
        }

	  
        if($arrconfCP['vigente']=='Y'){
            $vencimentocalc = " SELECT ('".$ndtemissao."' + INTERVAL ".$arrconfCP['diavenc']." DAY) as dataitem ";
        }else{
            $vencimentocalc = " SELECT (LAST_DAY('".$ndtemissao."') + INTERVAL ".$arrconfCP['diavenc']." DAY) as dataitem ";
        }
        //echo( $vencimentocalc);
        $resvenc=d::b()->query($vencimentocalc) or die("Erro ao buscar vencimento do montaarrnfitem: sql=".$vencimentocalc." mysql".mysqli_error(d::b()));
        $rowvenc=mysqli_fetch_assoc($resvenc);
        echo('<br>buscou vencimento.');
        if($qtParcelas == 0){
            $su="update nfitem set idnf=null, dataitem='".$rowvenc['dataitem']."', prodservdescr='".$prodservdescr."',total='".$invalor."',vlritem='".$invalor."'
            where idnfitem =".$rowf['idnfitem'];
            echo('<br>'.$su);
            $rus=d::b()->query($su) or die("[poschange_pedido] - Erro ao atualizar sql=".$su." mysql".mysqli_error(d::b()));      
           
          
            $inidnf=agrupaNfitem($rowf['idnfitem']);
            echo("<br> Agrupar item");      
            
            if (!is_numeric($inidnf)) {
                echo($inidnf); //die();
            }

            atualizavalornf($rowf['idnf']);
           echo('<br> atualizavalornf <br>');


            atualizafat($rowf['idnf']);
           echo(' <br> atualizafat <br>');

            if($inidnf != $rowf['idnf']){

               atualizavalornf($inidnf);
               echo('<br> atualizavalornf2 <br>');

               // cnf::gerarContapagar($inidnf);

            	atualizafat($inidnf);
                echo('<br> atualizafat2 <br>');
            }
        
            agrupaCP(); 
            echo('<br> agrupaCP fim');

        }
            
    }
}

function geraparcelaimposto($inpost,$invalor,$dtemissao,$idnotafiscal,$tipo,$obs) {  
    echo('<br> função gerar imposto'.$tipo);       
  
    $idnf=$inpost['idnotafiscal'];
    $_idempresa=$inpost['idempresa'];

    
    $arrNF=getObjeto("notafiscal",$inpost['idnotafiscal'],"idnotafiscal");
    echo('<br> informacoes 1'.$tipo);  
 
    $cra = traduzid("empresa", "idempresa", "cra",$arrNF['idempresa']);
    echo('<br> informacoes 2'.$tipo);  
 
        //BUSCAR CONFIGURAÇÕES DA FORMA DE PAGAMENTO
        $formapagamento=getObjeto("formapagamento",$arrNF['idformapagamento'],"idformapagamento");      
        echo('<br> informacoes 3'.$tipo);         
        if($formapagamento['agrupado']=='Y'){//se for agrupado          
                           
           // $ri=buscarParcelaPorNf($idnf,'notafiscal');

            $sqlverifquit= "SELECT i.parcela, DATE_ADD(i.datapagto, INTERVAL 1 DAY) AS vdatapagto, nc.proporcao, i.* 
                            FROM contapagaritem i 
                                LEFT JOIN nfconfpagar nc ON (nc.idnf=i.idobjetoorigem AND nc.datareceb=i.datapagto)
                            WHERE i.idobjetoorigem = ".$idnf."
                                AND i.tipoobjetoorigem = 'notafiscal'
                                AND i.status !='INATIVO'";
       
            $resverif = d::b()->query($sqlverifquit) or die($sqlverifquit."Erro ao consultar buscarParcelaPorNf da nota: <br>".mysqli_error(d::b()));
           // $rowverif = mysqli_fetch_assoc($resverif);
           echo('<br> select 4'.$tipo);   
        
        }else{//if($formapagamento['agrupado']=='Y'){
        
           // $ri=buscarFaturaNf($idnf,'notafiscal');

            $sqlverifquit=  "SELECT parcela, c.datareceb AS vdatapagto, c.* 
                                FROM contapagar c 
                                WHERE c.idobjeto =  ".$idnf."
                                    AND c.tipoobjeto='notafiscal'  
                                    AND c.status !='INATIVO'";
            $resverif = d::b()->query($sqlverifquit) or die($sqlverifquit."Erro ao consultar buscarFaturaNf da nota: <br>".mysqli_error(d::b()));
           
            echo('<br> select 5'.$tipo);   
        }//else($formapagamento['agrupado']=='Y'){
        
        if($cra=='FC'){//FLUXO DE CAIXA    
            $qtdparc=mysqli_num_rows($resverif);

            $valor = $invalor/$qtdparc;
        
           while($rwi = mysqli_fetch_assoc($resverif)){

                if(empty($rwi['proporcao'])){
                    $valor =$invalor/$qtdparc;
                }else{
                    $perc = $rwi['proporcao']/100;
                    $valor = $invalor*$perc;
                } 
                echo('<br> gerar parcela agrupado'.$tipo);           
                parcelaimposto($rwi['idcontapagar'],'contapagar',$valor,$rwi['parcela'],$rwi['parcelas'],$rwi['vdatapagto'],$tipo,'ABERTO',$_idempresa);              
            }
        }else{//competência
           // while($rwi = mysqli_fetch_assoc($resverif)){
            $rwi = mysqli_fetch_assoc($resverif);
            echo('<br> gerar parcela normal'.$tipo);        
                parcelaimposto($rwi['idcontapagar'],'contapagar',$invalor,1,1,$dtemissao,$tipo,'PENDENTE',$_idempresa);
               // break; 
           // }
        } 
        agrupaCP();  
        echo('<br> agrupaCP fim'.$tipo);

}//function geracomissao($idnf){

function  parcelaimposto($idobjeto,$tipoobj,$valorn,$parcela,$parcelas,$datapagto,$tipo,$status,$_idempresa){

    $datapagto = date("Y-m-d", strtotime($datapagto));  

    echo('gerando parcela de imposto='.$tipo."<br>");

    //die($datapagto);

    $arrconfCP=getDadosConfContapagarNFS($tipo,$_idempresa);;
    $visivel='S';

    $incontc = new Insert();
    $incontc->setTable("contapagaritem");
    $incontc->idempresa=$_idempresa;
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



function getDadosConfContapagarNFS($tipo,$_idempresa){
	$sqlrep = "select * from confcontapagar where status='ATIVO' and idempresa = ".$_idempresa." and tipo='".$tipo."'";
	$resrep = d::b()->query($sqlrep) or die("A Consulta de configuração automatica contapagar falhou :".mysqli_error()."<br>Sql:".$sqlrep);
	$qtdresp = mysqli_num_rows($resrep); 
	
	if($qtdresp<1){
		die('Não encontrada a configuração para a parcela automatica '.$tipo);
	}
   
	$rowrep= mysqli_fetch_assoc($resrep);
		 
	return $rowrep;    
}

function montaarrnfitem($inpost,$arrconfCP,$vlritem,$vlrtotal,$prodservdescr){
	$ndtemissao = $inpost["emissao"];

	$vencimentocalc = " SELECT (LAST_DAY('".$ndtemissao."') + INTERVAL 15 DAY) as dataitem ";
	$resvenc=d::b()->query($vencimentocalc) or die("Erro ao buscar vencimento do montaarrnfitem: sql=".$vencimentocalc." mysql".mysqli_error(d::b()));
	$rowvenc=mysqli_fetch_assoc($resvenc);
	
	if(empty($rowvenc['dataitem'])){ die("Erro ao buscar data do item montaarrnfitem. sql=".$vencimentocalc);}                   
echo($vencimentocalc."<br>");
	$arrnfitem=array();
	$arrnfitem[1]['qtd']=1;
	$arrnfitem[1]['vlritem']=$vlritem;
	$arrnfitem[1]['total']=$vlrtotal;
	$arrnfitem[1]['prodservdescr']=$prodservdescr;
	$arrnfitem[1]['idcontaitem']=$arrconfCP['idcontaitem'];
	$arrnfitem[1]['idtipoprodserv']=$arrconfCP['idtipoprodserv'];
	if(!empty($arrconfCP['idpessoa'])){
		$arrnfitem[1]['idpessoa']=$arrconfCP['idpessoa'];
	}else{
		$arrnfitem[1]['idpessoa']=$inpost['idpessoa'];
	}    
	$arrnfitem[1]['idobjetoitem']=$inpost['idnotafiscal'];
	$arrnfitem[1]['idempresa']=$inpost['idempresa'];
	$arrnfitem[1]['tipoobjetoitem']='notafiscal';
	$arrnfitem[1]['statusitem']='PENDENTE';
	$arrnfitem[1]['idconfcontapagar']=$arrconfCP['idconfcontapagar'];
	$arrnfitem[1]['dataitem']=$rowvenc['dataitem'];

	$arrnfitem[1]['criadopor']='cron_processapendentes';
	$arrnfitem[1]['criadoem']=date("Y-m-d H:i:s");
	$arrnfitem[1]['alteradopor']='cron_processapendentes';
	$arrnfitem[1]['alteradoem']=date("Y-m-d H:i:s");
	
    //print_r($arrnfitem);
	return $arrnfitem;
}

 //inserir informação no banco de dados
function inseredb($arrvalor,$tabela){   
	//print_r( $arrnfitem);die;
	$i=0;
	foreach ($arrvalor as $arritem ) {

		$insval = new Insert();
		$insval->setTable($tabela);
		foreach ($arritem as $key => $value) {		
			$insval->$key=$value;                             
		}
		$idvalor[$i]=$insval->save();
		$i++;	
	}
	return $idvalor;       
}


     //grupar nfitem sem vinculo com a nf
function agrupaNfitem($inidnfitem,$tiponf=null){

     
        
        $sql="select i.idnfitem,i.idempresa,i.total,i.idpessoa,i.dataitem,i.prodservdescr,c.agrupnota,c.agruppessoa,c.tiponf,c.idformapagamento,c.statusnf
        from nfitem i join confcontapagar c on(c.idconfcontapagar=i.idconfcontapagar)
        where i.idnf is null 
        and i.idconfcontapagar is not null  
        and i.idnfitem = ".$inidnfitem;
        $res= d::b()->query($sql) or die("[Laudo:] Erro ao buscar itens de nf para agrupamento : ". mysql_error() . "<p>SQL: ".$sql);
    
        while($row=mysqli_fetch_assoc($res)){
            if($row['agruppessoa']=='Y'){// procura um nota para entrar ou cria uma 1 para n
                $sql1="select * from nf 
                        where tiponf = '".$row['tiponf']."' 
                        and dtemissao between concat(date_add(date_add(LAST_DAY('".$row['dataitem']."'),interval 1 DAY),interval -1 MONTH),' 00:00:00') and concat(LAST_DAY('".$row['dataitem']."'),' 00:00:00')
                        and idpessoa = ".$row['idpessoa']." 
                        and idempresa=".$row['idempresa']."
                        and status ='".$row['statusnf']."' order by dtemissao asc limit 1";
                $res1= d::b()->query($sql1) or die("[Laudo:] Erro 2 ao buscar  nf para agrupamento : ". mysql_error() . "<p>SQL: ".$sql1);
                $qtd=mysqli_num_rows($res1);
                if($qtd>0){
                    $row1=mysqli_fetch_assoc($res1);
                    $idnf=$row1['idnf'];
                    $su="update nfconfpagar set datareceb='".$row['dataitem']."',obs='".$row['prodservdescr']."' where idnf=".$idnf;
                    $rru= d::b()->query($su);

                }else{
                   
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
                            $idtipounidade = 21;
                        break;
                        case 'D':
                            $modulo = 'comprassocio';
                            $idtipounidade = 22;
                        break;
                        default:
                            $modulo = 'nfentrada';
                            $idtipounidade = 19;
                    }

                    if($row['tiponf'] != 'V'){
                        $qrUnid = "SELECT idunidade FROM unidade WHERE idtipounidade = ".$idtipounidade." AND idempresa = ".$row['idempresa'];
                        $rsUnid = d::b()->query($qrUnid) or die("[processapendentes][1]: Erro ao buscar idunidade. SQL: ".$qrUnid);
                        $rwUnid = mysqli_fetch_assoc($rsUnid);
                    }

                    //LTM - 05-04-2021: Retorna o Idfluxo nf para Pedido (Tipo V)
                    $idfluxostatus = getId_FluxoStatus($modulo, $row['statusnf']);

                    $arrinsnf[1]['idpessoa']=$row['idpessoa'];		
                    $arrinsnf[1]['total']=$row['total'];
                    $arrinsnf[1]['dtemissao']=$row['dataitem']." 00:00:00";
                    if(empty($tiponf)){
                        $arrinsnf[1]['tiponf']=$row['tiponf'];
                    }else{
                        $arrinsnf[1]['tiponf']=$tiponf;
                    }
                    $arrinsnf[1]['tiponf']=$row['tiponf'];
                    if($row['tiponf'] != 'V'){
                        $arrinsnf[1]['idunidade']=$rwUnid['idunidade'];
                    }
                    $arrinsnf[1]['geracontapagar']='Y';
					$arrinsnf[1]['idempresa']=$row['idempresa'];
                    $arrinsnf[1]['status']=$row['statusnf'];
                    $arrinsnf[1]['idfluxostatus'] = $idfluxostatus;
                    $arrinsnf[1]['parcelas']=1;
                    $arrinsnf[1]['diasentrada']=1;					
                    $arrinsnf[1]['idformapagamento']=$row['idformapagamento'];

					$arrinsnf[1]['criadopor']='cron_processapendentes';
					$arrinsnf[1]['criadoem']=date("Y-m-d H:i:s");
					$arrinsnf[1]['alteradopor']='cron_processapendentes';
					$arrinsnf[1]['alteradoem']=date("Y-m-d H:i:s");
                    $idnf=inseredb($arrinsnf,'nf');
                    $idnf=$idnf[0];

                                    
					$sqlEtapaHistInsert = "INSERT INTO fluxostatushist (idempresa, idfluxostatus, idmodulo, modulo, status, criadopor, criadoem, alteradopor, alteradoem) 
					VALUES (".$row["idempresa"].", '$idfluxostatus', '$idnf', '". $modulo."', 'ATIVO', 'cron', now(), 'cron', now())"; 
					$res = d::b()->query($sqlEtapaHistInsert);


                    $arrinsnfcp[1]['idnf']=$idnf;	
                    $arrinsnfcp[1]['parcela']=1;
                    $arrinsnfcp[1]['idformapagamento']=$row['idformapagamento'];
                    $arrinsnfcp[1]['proporcao']=100;
					$arrinsnfcp[1]['idempresa']=$row['idempresa'];
                    $arrinsnfcp[1]['datareceb']=$row['dataitem'];
                    $arrinsnfcp[1]['obs']=$row['prodservdescr'];

					$arrinsnfcp[1]['criadopor']='cron_processapendentes';
					$arrinsnfcp[1]['criadoem']=date("Y-m-d H:i:s");
					$arrinsnfcp[1]['alteradopor']='cron_processapendentes';
					$arrinsnfcp[1]['alteradoem']=date("Y-m-d H:i:s");
                    
                    $idnfconfpagar=inseredb($arrinsnfcp,'nfconfpagar');
                
                }
    
            }else{//if($row['agruppessoa']=='Y'){// cria uma nota para vinculo 1 para 1
               
                    $sql1="select * from nf n
                            where n.tiponf = '".$row['tiponf']."'                           
                            and n.dtemissao between concat(date_add(date_add(LAST_DAY('".$row['dataitem']."'),interval 1 DAY),interval -1 MONTH),' 00:00:00') and concat(LAST_DAY('".$row['dataitem']."'),' 00:00:00')
                            and  n.idpessoa = ".$row['idpessoa']." 
                            and n.idempresa=".$row['idempresa']."
                            and not exists(select 1 from nfitem i where i.idnf=n.idnf)
                            and  n.status ='".$row['statusnf']."' order by  n.dtemissao asc limit 1";
                    $res1= d::b()->query($sql1) or die("[Laudo:] Erro 2 ao buscar  nf para agrupamento : ". mysql_error() . "<p>SQL: ".$sql1);
                    $qtd=mysqli_num_rows($res1);
                    if($qtd>0){
                        $row1=mysqli_fetch_assoc($res1);
                        $idnf=$row1['idnf'];
                        $su="update nfconfpagar set datareceb='".$row['dataitem']."', obs='".$row['prodservdescr']."' where idnf=".$idnf;
                        $rru= d::b()->query($su);
                    }else{

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
                                $idtipounidade = 21;
                            break;
                            case 'D':
                                $modulo = 'comprassocio';
                                $idtipounidade = 22;
                            break;
                            default:
                                $modulo = 'nfentrada';
                                $idtipounidade = 19;
                        }

                        if($row['tiponf'] != 'V'){
                            $qrUnid = "SELECT idunidade FROM unidade WHERE idtipounidade = ".$idtipounidade." AND idempresa = ".$row['idempresa'];
                            $rsUnid = d::b()->query($qrUnid) or die("[processapendentes][2]: Erro ao buscar idunidade. SQL: ".$qrUnid);
                            $rwUnid = mysqli_fetch_assoc($rsUnid);
                        }

                        $idfluxostatus = getId_FluxoStatus($modulo, $row['statusnf']);

                        $arrinsnf[1]['idpessoa']=$row['idpessoa'];		
                        $arrinsnf[1]['total']=$row['total'];
                        $arrinsnf[1]['dtemissao']=$row['dataitem']." 00:00:00";
                        $arrinsnf[1]['tiponf']=$row['tiponf'];
                        if($row['tiponf'] != 'V'){
                            $arrinsnf[1]['idunidade']=$rwUnid['idunidade'];
                        }
                        $arrinsnf[1]['geracontapagar']='Y';
						$arrinsnf[1]['idempresa']=$row['idempresa'];
                        $arrinsnf[1]['status']=$row['statusnf'];
                        $arrinsnf[1]['idfluxostatus'] = $idfluxostatus;
                        $arrinsnf[1]['parcelas']=1;
                        $arrinsnf[1]['diasentrada']=1;					
                        $arrinsnf[1]['idformapagamento']=$row['idformapagamento'];

						$arrinsnf[1]['criadopor']='cron_processapendentes';
						$arrinsnf[1]['criadoem']=date("Y-m-d H:i:s");
						$arrinsnf[1]['alteradopor']='cron_processapendentes';
						$arrinsnf[1]['alteradoem']=date("Y-m-d H:i:s");

                        $idnf=inseredb($arrinsnf,'nf');
                        $idnf=$idnf[0];

                                         
							$sqlEtapaHistInsert = "INSERT INTO fluxostatushist (idempresa, idfluxostatus, idmodulo, modulo, status, criadopor, criadoem, alteradopor, alteradoem) 
							VALUES (".$row["idempresa"].", '$idfluxostatus', '$idnf', '". $modulo."', 'ATIVO', 'cron', now(), 'cron', now())"; 
							$res = d::b()->query($sqlEtapaHistInsert);


                        $arrinsnfcp[1]['idnf']=$idnf;	
                        $arrinsnfcp[1]['parcela']=1;
                        $arrinsnfcp[1]['idformapagamento']=$row['idformapagamento'];
						$arrinsnfcp[1]['idempresa']=$row['idempresa'];
                        $arrinsnfcp[1]['proporcao']=100;
                        $arrinsnfcp[1]['datareceb']=$row['dataitem'];
                        $arrinsnfcp[1]['obs']=$row['prodservdescr'];
						$arrinsnfcp[1]['criadopor']='cron_processapendentes';
						$arrinsnfcp[1]['criadoem']=date("Y-m-d H:i:s");
						$arrinsnfcp[1]['alteradopor']='cron_processapendentes';
						$arrinsnfcp[1]['alteradoem']=date("Y-m-d H:i:s");

                        
                        $idnfconfpagar=inseredb($arrinsnfcp,'nfconfpagar');
                    }
               

            }//if($row['agruppessoa']=='Y'){
            if(!empty($idnf)){
                $sqlu="update nfitem set idnf=".$idnf.",nfe='Y',tiponf='".$row['tiponf']."' where idnfitem=".$row['idnfitem'];
                $resu= d::b()->query($sqlu) or die("[Laudo:] Erro 3 ao atualizar nf item : ". mysql_error() . "<p>SQL: ".$sqlu);
                //gerar faturamento
                return $idnf;
            }else{
                return 'Erro ao agrupar nfitem';
            }
    
        }//while($row=mysqli_fetch_assoc($sql)){
    }//function agrupaNfitem(){


function getId_FluxoStatus($_modulo, $status, $id = NULL, $tipo = NULL)
		{

		$sqlFluxo = "SELECT idfluxostatus
                        FROM fluxo f JOIN fluxostatus fs ON f.idfluxo = fs.idfluxo AND f.status = 'ATIVO'
                        JOIN "._DBCARBON."._status s ON fs.idstatus = s.idstatus AND s.statustipo = '$status'
                    WHERE f.modulo = '$_modulo'";
		
	
		$resFluxo = d::b()->query($sqlFluxo) or die(mysqli_error(d::b())." Erro ao buscar fluxo getIdFluxoStatus ".$sqlFluxo);
		$rowFluxo = mysqli_fetch_assoc($resFluxo);
	
		return $rowFluxo['idfluxostatus'];
}

function atualizavalornf($idnotafiscal){

	$sql="select ifnull(sum(i.total),0) as total from  nfitem i 
		where i.idnf=".$idnotafiscal;
		$re= d::b()->query($sql)or die("[index:atualizavalornf] Erro 1 ao buscar valor da nf : ". mysql_error() . "<p>SQL: ".$sql);
	$row=mysqli_fetch_assoc($re);

	$sqlu="update nf set total='".$row['total']."' where idnf=".$idnotafiscal;
	$resu= d::b()->query($sqlu) or die("[index:atualizavalornf] Erro 2 ao atualizar nf  : ". mysql_error() . "<p>SQL: ".$sqlu);
	//gerar faturamento
	return $idnf;

}

function gerarContapagar($idnotafiscal){
	

	$sql="select * from nf where idnf=".$idnotafiscal;
	$res= d::b()->query($sql) or die("[Laudo:] Erro gerarContapagaritem ao busca dados da nf  : ". mysql_error() . "<p>SQL: ".$sql);
	$row=mysqli_fetch_assoc($res);

	if($row['geracontapagar']=="Y"){	

		$sf="select idagencia,agruppessoa,agrupado,agrupfpagamento,agrupnota from formapagamento where idformapagamento=".$row['idformapagamento'];
		$rf=d::b()->query($sf) or die("Erro ao buscar configuração da forma de pagamento: sql=".$sf." mysql".mysqli_error(d::b()));
		$formapagamento=mysqli_fetch_assoc($rf);

		$sqlf="select ifnull(sum(frete),0) as sumfrete
		from nfitem
		where idnf =".$idnotafiscal;
		$resf=d::b()->query($sqlf) or die("erro ao verificar iten frete da notafiscal sql=".$sqlf);
		$rowf= mysqli_fetch_assoc($resf);

		$sqlcx="select * from nfconfpagar where idnf=".$idnotafiscal;
		$rescx=d::b()->query($sqlcx) or die("Falha ao buscar configurações das parcelas sql=".$sqlcx);
		$qtdparcelas= mysqli_num_rows($rescx);
		if($row['tiponf']=="V"){
			$visivel="S";
			$tipo="C";
		}elseif($row['tiponf']=="C" or$row['tiponf']=="T" or $row['tiponf']=="S" or $row['tiponf']=="E" or $row['tiponf']=="M" or $row['tiponf'] =="B"){//if($tiponf=="V"){
			$visivel="S";
			$tipo="D";	
		}elseif( $row['tiponf']=="D" or $row['tiponf']=="R"){		
			$visivel="N";	
			$tipo="D";
		}else{		
			$visivel='N';
			$tipo="D";
		}

		$index = 0;
		while($rowcx=mysqli_fetch_assoc($rescx)){
			$index++;		 
		
			//Insere novas parcelas
			$valorparcela = $row['total']*($rowcx['proporcao']/100);

			$valorparcelarep =(($row['total']-$rowf['sumfrete'])/($rowcx['proporcao']/100));

			$vencimentocalc = $rowcx['datareceb'];
			$recebcalc = $rowcx['datareceb'];

			if($formapagamento['tipo']=='COMISSAO'){
				$status='ABERTO';
			}else{
				$status='PENDENTE';
			}

			if($formapagamento['agrupado']=='Y'){//se for agrupado	

				$insnfcp[1]['status']=$status;	
				$insnfcp[1]['idpessoa']=$row['idpessoa'];
				$insnfcp[1]['idempresa']=$row['idempresa'];
				$insnfcp[1]['idobjetoorigem']=$idnotafiscal;
				$insnfcp[1]['tipoobjetoorigem']='nf';
				$insnfcp[1]['tipo']=$tipo;
				$insnfcp[1]['visivel']=$visivel;
				$insnfcp[1]['parcela']=$index;
				$insnfcp[1]['parcelas']=$row['parcelas'];
				$insnfcp[1]['datapagto']=$recebcalc;
				$insnfcp[1]['valor']=$valorparcela;
				$insnfcp[1]['obs']=$rowcx['obs'];
				$insnfcp[1]['idformapagamento']=$row['idformapagamento'];
				$insnfcp[1]['criadopor']='cron_processapendentes';
				$insnfcp[1]['criadoem']=date("Y-m-d H:i:s");
				$insnfcp[1]['alteradopor']='cron_processapendentes';
				$insnfcp[1]['alteradoem']=date("Y-m-d H:i:s");	

				$idnfcp=inseredb($insnfcp,'contapagaritem');
				
			}else{	
				$idfluxostatus = getId_FluxoStatus('contapagar', $status);
				$insnfcp[1]['status']=$status;
				$insnfcp[1]['idfluxostatus'] = $idfluxostatus;
				$insnfcp[1]['idformapagamento']=$row['idformapagamento'];
				$insnfcp[1]['idempresa']=$row['idempresa'];
				$insnfcp[1]['idpessoa']=$row['idpessoa'];
				$insnfcp[1]['idobjeto']=$idnotafiscal;
				$insnfcp[1]['tipoobjeto']='nf';
				$insnfcp[1]['tipo']=$tipo;
				$insnfcp[1]['visivel']=$visivel;
				$insnfcp[1]['parcela']=$index;
				$insnfcp[1]['parcelas']=$row['parcelas'];
				$insnfcp[1]['datapagto']=$vencimentocalc;
				$insnfcp[1]['datareceb']=$recebcalc;
				$insnfcp[1]['valor']=$valorparcela;
				$insnfcp[1]['intervalo']=$row['intervalo'];
				$insnfcp[1]['obs']=$rowcx['obs'];
				$insnfcp[1]['criadopor']='cron_processapendentes';
				$insnfcp[1]['criadoem']=date("Y-m-d H:i:s");
				$insnfcp[1]['alteradopor']='cron_processapendentes';
				$insnfcp[1]['alteradoem']=date("Y-m-d H:i:s");	


				$idnfcp = inseredb($insnfcp,'contapagar');
				$modulo='contapagar';
				$sqlEtapaHistInsert = "INSERT INTO fluxostatushist (idempresa, idfluxostatus, idmodulo, modulo, status, criadopor, criadoem, alteradopor, alteradoem) 
				VALUES (".$row["idempresa"].", '$idfluxostatus', '$idnfcp', '". $modulo."', 'ATIVO', 'cron', now(), 'cron', now())"; 
				$res = d::b()->query($sqlEtapaHistInsert);
			}            

		}//for ($index = 1; $index <= $qtdparcelas; $index++) {
	   
	}
}//function gerarContapagaritem($idnotafiscal){

function atualizafat($idnotafiscal,$idformapagamento=null){

        if(empty($idformapagamento)){
            $idformapagamento=traduzid('nf','idnf','idformapagamento',$idnotafiscal);
            if(empty($idformapagamento)){
                die('[api/nf]-forma de pagamento não encontrada');
            }
        }        

        //BUSCAR CONFIGURAÇÕES DA FORMA DE PAGAMENTO
        $sf="select idagencia,agruppessoa,agrupado,agrupfpagamento,agrupnota from formapagamento where idformapagamento=".$idformapagamento;
        $rf=d::b()->query($sf) or die("[api/nf]-Erro ao buscar configuração da forma de pagamento: sql=".$sf." mysql".mysqli_error(d::b()));
        $formapagamento=mysqli_fetch_assoc($rf);
        //echo($sf."<br>");
    
        $arrParcelas= recuperaParcelas($idnotafiscal,'QUITADO','nf');//Contapagar Quitado
        $qtParcelas =$arrParcelas['quant'];
      //  echo($qtParcelas." parcelas<br>");
        $arrParcelasFechado= recuperaParcelas($idnotafiscal,'FECHADO','nf');//Contapagar fechado
        $qtParcelasFechadas =$arrParcelasFechado['quant'];
       // echo($qtParcelasFechadas." qtParcelasFechadas<br>");
/*impostos da erro se usar
        $arrParcelasPendente= cnf::recuperaParcelas($idnotafiscal,'PENDENTE','nf');//Contapagar fechado
        $qtParcelasPendente =$arrParcelasPendente['quant'];
  */      
        $arrParcelasIV= recuperaParcelasItensVinc($idnotafiscal,'nf');
        $qtParcelasIV =$arrParcelasIV['quant'];
      //  echo($arrParcelasIV." arrParcelasIV<br>");
                
        $arrlinhasbol=  verificaboleto($idnotafiscal);
        $qtdlinhasbol=$arrlinhasbol['quant'];
      //  echo($qtdlinhasbol." qtdlinhasbol<br>");
        //die($qtParcelas);
        $arrParcItens= getParcelaItens($idnotafiscal);
        $qtParcelasitem = $arrParcItens['quant'];
       // echo($qtParcelasitem." qtParcelasitem<br>");
        
        $arrParcItensFechada= getParcelaItensfechada($idnotafiscal,$formapagamento['agrupnota']);
        $qtParcelasitemFechada = $arrParcItensFechada['quant'];   
        //echo($qtParcelasitemFechada." qtParcelasitemFechada<br>");

        $qtdprog=recuperaParcelasProg($idnotafiscal,'nf');
        // echo($qtdprog." qtdprog<br>");
        //echo($arrParcelas['quant']." - ".$arrlinhasbol['quant']." - ".$qtParcelasitem ." - ".$qtParcelasIV);die;
        if ($qtParcelas == 0  and  $qtdprog <1 and $qtdlinhasbol== 0 and $qtParcelasitem==0 and $qtParcelasIV==0 and $qtParcelasFechadas==0 and $qtParcelasitemFechada==0){
        //deleta as parcelas existentes.
           // echo($deleta." deleta:".$idnotafiscal."<br>");
            deletaParcelasExistentes($idnotafiscal);
            //echo(" deletaParcelasExistentes<br>");
            gerarContapagar($idnotafiscal);
           // echo(" gerarContapagar<br>");
            agrupaCP(); 
            //echo(" agrupaCP<br>");
        }
      //  echo('fim atualizafat <br>');
    }

	function recuperaParcelas($inidobj,$instatus,$intipoobjeto){
        
        /*
        * verifica se existe alguma parcela quitada. se existir, nao alterar nada.
        */
        $sqlverifquit = "select count(*) as quant from contapagar where status = '".$instatus."'    and tipoobjeto='".$intipoobjeto."' and idobjeto = ".$inidobj;
    
        $resverif = d::b()->query($sqlverifquit) or die($sqlverifquit."Erro ao consultar parcelas da nota: <br>".mysqli_error(d::b()));
        $rowverif = mysqli_fetch_array($resverif);

        return  $rowverif;
    }

  /*  function buscarParcelaPorNf($idnf,$tipo) {
        $sqlverifquit= "SELECT i.parcela, DATE_ADD(i.datapagto, INTERVAL 1 DAY) AS vdatapagto, nc.proporcao, i.* 
            FROM contapagaritem i 
                LEFT JOIN nfconfpagar nc ON (nc.idnf=i.idobjetoorigem AND nc.datareceb=i.datapagto)
            WHERE i.idobjetoorigem = ".$idnf."
                AND i.tipoobjetoorigem = '".$tipo."' 
                AND i.status !='INATIVO'";
        echo ($sqlverifquit);
        $resverif = d::b()->query($sqlverifquit) or die($sqlverifquit."Erro ao consultar buscarParcelaPorNf da nota: <br>".mysqli_error(d::b()));
        $rowverif = mysqli_fetch_assoc($resverif);

        return  $rowverif;
    }
*/
/*
    function buscarFaturaNf($idnf,$tipo) {
        $sqlverifquit=  "SELECT parcela, c.datareceb AS vdatapagto, c.* 
            FROM contapagar c 
            WHERE c.idobjeto =  ".$idnf."
                AND c.tipoobjeto='".$tipo."'   
                AND c.status !='INATIVO'";
        $resverif = d::b()->query($sqlverifquit) or die($sqlverifquit."Erro ao consultar buscarFaturaNf da nota: <br>".mysqli_error(d::b()));
        $rowverif = mysqli_fetch_assoc($resverif);

        return  $rowverif;
    }
*/
	function recuperaParcelasItensVinc($inidobj,$intipoobjeto){
        /*
        * verifica se existe algum contaitem vinculado a conta
        */
        $sqlverifquit = "select count(*) as quant from contapagar c
            where c.tipoobjeto='".$intipoobjeto."' 
            and c.idobjeto = ".$inidobj." and exists (select 1 from contapagaritem i where i.idcontapagar = c.idcontapagar and i.tipoobjetoorigem='contapagar')";
    
        $resverif = d::b()->query($sqlverifquit) or die($sqlverifquit."Erro ao consultar se existe contaitem vinculada: <br>".mysqli_error(d::b()));
        $rowverif = mysqli_fetch_array($resverif);

        return  $rowverif;
        
    }
	function verificaboleto($inidnf){
        $sqlqtdbol="select count(*) as quant
            from remessaitem i,remessa r,contapagar c
            where i.idremessa = r.idremessa 
            and i.idcontapagar =c.idcontapagar
            and c.tipoobjeto ='nf'
            and c.idobjeto=".$inidnf;
        //echo $sqlverifquit;
        $resqtdbol = d::b()->query($sqlqtdbol) or die($sqlqtdbol."Erro ao consultar boletos da nota: <br>".mysqli_error(d::b()));
        $rowqtdbol = mysqli_fetch_array($resqtdbol);
        
        return  $rowqtdbol;
    }

	function getParcelaItens($idnotafiscal){
        /*
        * verifica se existe alguma parcela item quitada. se existir, nao alterar nada.
        */
        $sqlveritem = "select count(*) as quant from contapagaritem where  idobjetoorigem= ".$idnotafiscal." and tipoobjetoorigem = 'nf' and status in('QUITADO')";
        
        $resveritem = d::b()->query($sqlveritem) or die($sqlveritem."Erro ao consultar parcelas item do cte: <br>".mysqli_error(d::b()));
        $rowverifitem = mysqli_fetch_array($resveritem);
        return  $rowverifitem;
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
        $sqlveritem = "select count(*) as quant
                from contapagaritem i join contapagar c on(c.idcontapagar=i.idcontapagar and c.status in ".$instatus.")
                where  i.idobjetoorigem= ".$idnotafiscal." 
                    and i.tipoobjetoorigem = 'nf' ";
        
        $resveritem = d::b()->query($sqlveritem) or die($sqlveritem."Erro ao consultar parcelas item do cte: <br>".mysqli_error(d::b()));
        $rowverifitem = mysqli_fetch_array($resveritem);
        return  $rowverifitem;
    }

	function recuperaParcelasProg($idnotafiscal,$intipoobjeto){
        /*
        * verifica se existe algum contaitem vinculado a conta
        */
       $sqlverifquit = "select c.* from contapagar c
                   where c.tipoobjeto='".$intipoobjeto."' 
                   and c.progpagamento='S'
                   and c.idobjeto = ".$idnotafiscal;
      
       $resverif = d::b()->query($sqlverifquit) or die($sqlverifquit."Erro ao consultar se existe parcela programada: <br>".mysqli_error(d::b()));
       $qtd = mysqli_num_rows($resverif);
       if($qtd<1){
           $sqlverifquit = "select c.*
                       from contapagar c join contapagaritem i on(i.idcontapagar = c.idcontapagar)
                           where i.tipoobjetoorigem='nf' 
                           and i.idobjetoorigem=".$idnotafiscal." 
                           and c.progpagamento='S'";
   
           $resverif = d::b()->query($sqlverifquit) or die($sqlverifquit."Erro ao consultar se existe parcela vinculada programada : <br>".mysqli_error(d::b()));
           $qtd = mysqli_num_rows($resverif);  
           if($qtd<1){
               $tmpsqldel = "select cc.* 
                   from contapagar c,contapagaritem cc
                   where c.tipoobjeto = 'nf' 
                   and c.idobjeto =".$idnotafiscal."
                   and c.progpagamento = 'S'
                   and cc.idobjetoorigem = c.idcontapagar
                   and cc.tipoobjetoorigem ='contapagar'
                   and cc.status in ('INICIO','ABERTO','PENDENTE')";
               $resverif = d::b()->query($sqlverifquit) or die($sqlverifquit."Erro ao consultar se existe comissao vinculada programada : <br>".mysqli_error(d::b()));
               $qtd = mysqli_num_rows($resverif); 
           }
         
       }
      
       return   $qtd;   
   }

   function deletaParcelasExistentes($idnotafiscal){
		/*
		* deleta as parcelas existentes.
		*/
			$tmpsqldel = "delete cc.* 
							from contapagar c,contapagaritem cc
							where c.tipoobjeto = 'nf' 
							and c.idobjeto =".$idnotafiscal."
							and cc.idobjetoorigem = c.idcontapagar
				and cc.tipoobjetoorigem ='contapagar'
				and cc.status in ('ABERTO','INATIVO')";
                //echo('1:'.$tmpsqldel."<br>");
		d::b()->query($tmpsqldel) or die("Erro ao retirar parcelas de comissão da Nota Fiscal : <br>".mysqli_error(d::b()));


		//if($contapagaritem=="Y"){
		$tmpsqldel = "delete from contapagaritem where tipoobjetoorigem =  'nf' and idobjetoorigem = ".$idnotafiscal."  and status !='QUITADO'";
        //echo('2:'.$tmpsqldel."<br>");
        d::b()->query($tmpsqldel) or die("Erro ao retirar parcelas da Nota Fiscal: <br>".mysqli_error(d::b()));
		//}
			
		$tmpsqldel = "delete c.* from contapagar c 
			where c.tipoobjeto = 'nf' 
			and c.status!='QUITADO'
					and not exists(select 1 from contapagaritem i where i.idcontapagar=c.idcontapagar and i.tipoobjetoorigem='contapagar')
			and c.idobjeto = ".$idnotafiscal;
           // echo('3:'.$tmpsqldel."<br>");
		d::b()->query($tmpsqldel) or die("Erro ao retirar parcelas da Nota Fiscal: <br>".mysqli_error(d::b()));
			
	}

	function agrupaCP(){
              
       
    
        $sql="select i.idcontapagaritem,i.idpessoa,i.idformapagamento,i.idagencia,i.idcontaitem,
                    month(LAST_DAY(i.datapagto) + INTERVAL 1 DAY) as mes,
                    year(LAST_DAY(i.datapagto) + INTERVAL 1 DAY) as ano,
                    (LAST_DAY(i.datapagto) + INTERVAL ifnull(f.diavenc,1) DAY) as datavencimento,
                    DATE_ADD((LAST_DAY(i.datapagto) + INTERVAL ifnull(f.diavenc,1) DAY), INTERVAL 1 MONTH) as datavencimentoseq,
                    (LAST_DAY(i.datapagto) + INTERVAL 1 DAY) as inicio,
                    LAST_DAY(LAST_DAY(i.datapagto) + INTERVAL 1 day) as fim,
                    i.datapagto,
                    f.agruppessoa,
                    f.agrupfpagamento,
                    f.agrupnota,
                    i.idobjetoorigem,               
                    i.tipoobjetoorigem,
                    i.valor,
                    i.parcela,
                    i.parcelas,
                    i.tipo,
                    i.visivel,
                    f.previsao,
                    i.status,
                    i.obs,
					i.idempresa,
                    f.tipoespecifico,
                     p.cpfcnpj
            from contapagaritem i join 
                    formapagamento f on(i.idformapagamento=f.idformapagamento)
                     JOIN pessoa p ON p.idpessoa = i.idpessoa 
                where i.status IN ('ABERTO','PENDENTE','PAGAR')
                    and (idcontapagar is null or  idcontapagar='')
                    and i.idpessoa is not null and i.idpessoa !=''
                    and i.idformapagamento is not null and i.idformapagamento !=''                   
                    and i.idagencia is not null and i.idagencia !=''";
                   // echo($sql."<br>");
        $res= d::b()->query($sql) or die($sql."Erro ao buscar contapagaritem agrupado por pessoa para agrupamento: <br>".mysqli_error());
        
        while($row=mysqli_fetch_assoc($res)){
            //se for comissao o tipo da conta agrupadora e REPRESENTACAO por comportar de forma diferente das demais
            /*
            $sqlfo="select * from confcontapagar where idformapagamento =".$row['idformapagamento']." and tipo='COMISSAO' and status='ATIVO'";
           // echo($sqlfo."<br>");
            $resfo= d::b()->query($sqlfo) or die($sql."Fala ao buscar se forma de pagamento e comissao: <br>".mysqli_error());
            $qtdfo=mysqli_num_rows($resfo);
            if($qtdfo>0){$tipoespecifico='REPRESENTACAO';}else{$tipoespecifico='AGRUPAMENTO';}
            */

            $tipoespecifico=$row['tipoespecifico'];

            if($row['agrupnota']=='Y'){
                $qtd1=0;
            }elseif($row['agruppessoa']=='Y'){
                //alterado a buscar para pegar a primeira em aberto apartir da data de vencimento datapagto 11-09-2020 hermesp
                $sql1="select * from contapagar c 
                 join pessoa p on(p.idpessoa=c.idpessoa and SUBSTRING(p.cpfcnpj , 1,8) = SUBSTRING('".$row['cpfcnpj']."', 1,8) )
                        where -- c.idpessoa = ".$row['idpessoa']." and
                         c.idformapagamento= ".$row['idformapagamento']."
                        and c.idagencia = ".$row['idagencia']."
                        and c.idempresa = ".$row['idempresa']."
                        and c.status='ABERTO'
                        and c.tipoespecifico='".$tipoespecifico."'
                        and c.datareceb >= '".$row['datavencimento']."' 
                        -- and '".$row['fim']."'  
                        order by c.datareceb asc limit 1";
                      //  echo('eo1:'.$sql1."<br>");  
                $res1= d::b()->query($sql1) or die($sql1."Erro ao buscar contapagar para agrupar por pessoa e formapagto: <br>".mysqli_error());
                $qtd1=mysqli_num_rows($res1);
            }else{
                //alterado a buscar para pegar a primeira em aberto apartir da data de vencimento datapagto 11-09-2020 hermesp
                $sql1="select * from contapagar c 
                        where c.idformapagamento= ".$row['idformapagamento']."
                        and c.idagencia = ".$row['idagencia']."                   
                        and c.idempresa = ".$row['idempresa']."
                        and c.status='ABERTO'
                        and c.tipoespecifico='".$tipoespecifico."'
                        and c.datareceb >= '".$row['datapagto']."' 
                       -- and '".$row['fim']."' 
                        order by c.datareceb asc limit 1";  
                       // echo('eo2:'.$sql1."<br>");
                $res1= d::b()->query($sql1) or die($sql1."Erro ao buscar contapagar para agrupar por pessoa e formapagto: <br>".mysqli_error());
                $qtd1=mysqli_num_rows($res1);
      
            }
                
                if($qtd1>0){
                   // echo($sql1."<br>");
                    $row1=mysqli_fetch_assoc($res1);
                    $squ="update contapagaritem set idcontapagar=".$row1['idcontapagar']." where idcontapagaritem=".$row['idcontapagaritem'];
                    $reu= d::b()->query($squ) or die($squ."Erro vincular contapagaritem na contapagar: <br>".mysqli_error());
                }else{
                    /* 
                    * Fatura cartão: ao lançar um item de conta, 
                    * verificar se ha  uma fatura "pendente e/ou quitado"
                    * no mes do lançamento. Caso haja, jogar para o proximo mes.                     * 
                    */
                    if($row['agrupnota']=='Y'){
                        
                        $datavencimento=$row['datapagto'];
                        
                    }else{
                        $datavencimento=$row['datavencimento'];
                    }
                    
                   // echo('new insert <br>');
                    $inscontapagar = new Insert();
                    $inscontapagar->setTable("contapagar");
                    $inscontapagar->idempresa=$row['idempresa'];
                    
                    $inscontapagar->idagencia=$row['idagencia'];
                   // echo('depos new insert <br>');           

                    if($row['agruppessoa']=='Y'){
                        $inscontapagar->idpessoa=$row['idpessoa'];
                        $inscontapagar->status='ABERTO';

                        //LTM - 31-03-2021: Retorna o Idfluxo ContaPagar
                        $idfluxostatus = FluxoController::getIdFluxoStatus('contapagar', 'ABERTO');
                        $inscontapagar->idfluxostatus = $idfluxostatus;

                        $inscontapagar->parcela=1;                                
                        $inscontapagar->parcelas=1;
                        if(!empty($row['idcontaitem'])){
                            $inscontapagar->idcontaitem=$row['idcontaitem'];
                        }
                    }elseif($row['agrupnota']=='Y'){
                        $inscontapagar->idpessoa=$row['idpessoa'];
                        $inscontapagar->tipoobjeto=$row['tipoobjetoorigem'];
                        $inscontapagar->idobjeto=$row['idobjetoorigem'];
                        $inscontapagar->parcela=$row['parcela'];
                        $inscontapagar->parcelas=$row['parcelas'];
                        $inscontapagar->valor=$row['valor'];
                        $inscontapagar->status=$row['status'];
					
		
                       // echo('getId_FluxoStatus <br>'); 
                        //LTM - 31-03-2021: Retorna o Idfluxo ContaPagar
                        $idfluxostatus = getId_FluxoStatus('contapagar', $row['status']);
                        $inscontapagar->idfluxostatus = $idfluxostatus;
                      //  echo('depois getId_FluxoStatus <br>'); 
                        if(!empty($row['idcontaitem'])){
                            $inscontapagar->idcontaitem=$row['idcontaitem'];
                        }
                    }else{
                        $inscontapagar->idcontaitem=46;
                        $inscontapagar->status='ABERTO';

                        //LTM - 31-03-2021: Retorna o Idfluxo ContaPagar
                        $idfluxostatus = getId_FluxoStatus('contapagar', 'ABERTO');
                        $inscontapagar->idfluxostatus = $idfluxostatus;

                        $inscontapagar->parcela=1;                                
                        $inscontapagar->parcelas=1;
                    }
                    $inscontapagar->idformapagamento=$row['idformapagamento'];
                    if(!empty($row['previsao']) and $row['agrupnota']!='Y'){
                        $inscontapagar->valor=$row['previsao'];
                    }
                 
                    $inscontapagar->tipo=$row['tipo'];
                    $inscontapagar->visivel=$row['visivel'];
                    $inscontapagar->obs=$row['obs'];
                    $inscontapagar->tipoespecifico=$tipoespecifico;
                                if($row['agruppessoa']=='Y'){
                    $inscontapagar->idpessoa=$row['idpessoa'];
                                    $inscontapagar->status='ABERTO';
                                    $inscontapagar->parcela=1;                                
                    $inscontapagar->parcelas=1;
                                    if(!empty($row['idcontaitem'])){
                                        $inscontapagar->idcontaitem=$row['idcontaitem'];
                                    }
                                }elseif($row['agrupnota']=='Y'){
                                    $inscontapagar->idpessoa=$row['idpessoa'];
                                    $inscontapagar->tipoobjeto=$row['tipoobjetoorigem'];
                                    $inscontapagar->idobjeto=$row['idobjetoorigem'];
                                    $inscontapagar->parcela=$row['parcela'];
                                    $inscontapagar->parcelas=$row['parcelas'];
                                    $inscontapagar->valor=$row['valor'];
                                    $inscontapagar->status=$row['status'];
                                    if(!empty($row['idcontaitem'])){
                                        $inscontapagar->idcontaitem=$row['idcontaitem'];
                                    }
                                }else{
                                     $inscontapagar->idcontaitem=46;
                                     $inscontapagar->status='ABERTO';
                                     $inscontapagar->parcela=1;                                
                    $inscontapagar->parcelas=1;
                                }
                                    $inscontapagar->idformapagamento=$row['idformapagamento'];
                                if(!empty($row['previsao']) and $row['agrupnota']!='Y'){
                                    $inscontapagar->valor=$row['previsao'];
                                }
                                    
                                    
                                    $inscontapagar->tipo=$row['tipo'];
                                    $inscontapagar->visivel=$row['visivel'];
                                    $inscontapagar->obs=$row['obs'];
                                    $inscontapagar->tipoespecifico=$tipoespecifico;

                    $inscontapagar->datapagto=$datavencimento;
                    $inscontapagar->datareceb=$datavencimento;

					$inscontapagar->criadopor='cron_processapendentes';
					$inscontapagar->criadoem=date("Y-m-d H:i:s");
					$inscontapagar->alteradopor='cron_processapendentes';
					$inscontapagar->alteradoem=date("Y-m-d H:i:s");	
                   // echo('insertcontapagar <br>');
                   // print_r($inscontapagar);
                    $idcontapagar=$inscontapagar->save();  

                                  
                    $sqlu="update contapagaritem set idcontapagar =".$idcontapagar."
                                            where idcontapagaritem =".$row['idcontapagaritem']."  and idempresa = ".$row['idempresa']."";
                   //echo('<br>'. $sqlu);
                    d::b()->query($sqlu) or die("erro ao atualizar contapagaritem com novo contapagar sql=".$sqlu);

                    //LTM - 31-03-2021: Retorna o Idfluxo Hist
                    if(!empty($idfluxostatus))
                    {
                      
						$modulo='contapagar';
						$sqlEtapaHistInsert = "INSERT INTO fluxostatushist (idempresa, idfluxostatus, idmodulo, modulo, status, criadopor, criadoem, alteradopor, alteradoem) 
						VALUES (".$row["idempresa"].", '$idfluxostatus', '$idcontapagar', '". $modulo."', 'ATIVO', 'cron', now(), 'cron', now())"; 
					//echo('<br>'. $sqlEtapaHistInsert);
                    $res = d::b()->query($sqlEtapaHistInsert);
                    }
                    
                }
           // echo('fim 1');
         
        }// while($row=mysqli_fetch_assoc($res)){ 
            //echo('fim 2');
    }
//fim funcoes para gerar guias de imposto

function LOGETAPA($inidnfslote,$idempresa,$inetapa){
	
	$msgerro = str_replace("'","",$_SESSION["errocon"]); 
	
		
	$sqllog = "insert into nfslog (
				idnfslote
                                ,idempresa
				,etapa
				,erro) 
				values (
					".$inidnfslote."
                                        ,'".$idempresa."'
					,'".$inetapa."'
					,'".$msgerro."')";
	$retlog = d::b()->query($sqllog);
	if(!$retlog){
		die("Erro ao inserir LOG: \n<br>".mysqli_error(d::b())."\n<br>".$sqllog);
	}
}

function STATUSLOTE($inidnfslote, $instatus,$innrps){

	echo("entrou STATUSLOTE ".$instatus);
	//verifica se o status esta devidamente preenchido
	if(!$instatus){
		echo("Erro ao alterar status do LOTE: \n<br>O status informado esta VAZIO.");
		return false;
	}

	//armazena o texto existente na variavel xml
	//$xml = mysqli_real_escape_string($_SESSION["xml"]);
        $xml = str_replace("'","",$_SESSION["xml"]) ;
	/*
	 * altera o status do lote
	 */ 
	$sqllote = "update nfslote set status = '".$instatus."', xmlretconsult = '".$xml."',alteradoem = sysdate(),nnfe = '".$_SESSION["numeronfe"]."' where idnfslote = ".$inidnfslote;
	$retlote = d::b()->query($sqllote);
	if(!$retlote){
		//LOG($inidlote,$inetapa,$incoderro,$inerro,$inxml);
		echo("Erro ao alterar LOTE: \n<br>".mysqli_error(d::b())."\n<br>".$sqllote);
		return false;
	}
	echo("alterar status do log ".$instatus."\n");
	//Caso seja sucesso inserir o numero da nfe na tabela de notasfiscais	
	//if($instatus=="SUCESSO"){	
	if (strpos("'".$instatus."'", 'SUCESSO') == true or $instatus=="SUCESSO") {
		echo("alterar status NF ".$instatus."\n");
		
		$sql1 = "select numerorps,idempresa from nfslote
		where idnfslote = ".$inidnfslote;
		
		$res1 = d::b()->query($sql1) or die("A Consulta do numero do numero do RPS falhou : ". mysqli_error(d::b()) . "<p>SQL: $sql1");
		$row1 = mysqli_fetch_assoc($res1);

		

		$sqn="select * from notafiscal n where n.numerorps='".$row1['numerorps']."' and n.idempresa=".$row1['idempresa'];
		echo ($sqn);
		$ren = d::b()->query($sqn);
		$rown=mysqli_fetch_assoc($ren);
		$idnotafiscal=$rown['idnotafiscal'];
	    $id_empresa=$rown['idempresa'];
          
        if(empty($rown["controle"])){
    
            //if(_NFSECHOLOG)//echo "\n".date("H:i:s")." - geranumerorps:gerando nova rps";
    
            ### Tenta incrementar e recuperar o numerorps
            d::b()->query("LOCK TABLES sequence WRITE;");
            d::b()->query("update sequence set chave1 = (chave1 + 1) where sequence = 'nossonumero'");
    
            $sqlns = "SELECT chave1 FROM sequence where sequence = 'nossonumero';";
    
            $resns = d::b()->query($sqlns);
    
            if(!$resns){
                d::b()->query("UNLOCK TABLES;");
                echo "1-Falha Pesquisando Sequence [nossonumero] : " . mysqli_error(d::b()) . "<p>SQL: $sql";
               // die();
            }
        
            $rowns = mysqli_fetch_array($resns);
        
            ### Caso nao retorne nenhuma linha ou retorn valor vazio
            if(empty($rowns["chave1"])){
                if(!$rowns){
                    d::b()->query("UNLOCK TABLES;");
                    echo "2-Falha Pesquisando Sequence [nossonumero] : " . mysqli_error(d::b()) . "<p>SQL: $sqlns";
                   // die();
                }
            }
        
            d::b()->query("UNLOCK TABLES;");        
        
            $sqlnf = "update notafiscal set controle = ".$rowns["chave1"]." where idnotafiscal = ".$idnotafiscal;
            d::b()->query($sqlnf) or die("Erro atribuindo nossonumero:\nSQL:".$sqlnf."\nErro:".mysqli_error(d::b()));
    
        }


        $crc = traduzid("empresa", "idempresa", "crc",$row1['idempresa']);


		//gerar guias de imposto
		if($rown['tiporecolhimento']=='R' and  $rown['subtotal'] > 0.01 and $id_empresa != 1){//ISS

           /* 
           não gerar parcela solicitado pela izabella @720989
           $rown['iss'] = truncarNumero($rown['iss']);
            if ($rown['iss'] > 0) {
                echo "<br>Gerar imposto ISS.";
                imposto($rown, $_SESSION['numeronfe'], $rown['iss'], $rown['emissao'], $rown['idnotafiscal'], 'ISSRECOLHER', $obs=null);        
            }        
		*/
        
            echo("<br> Gerar imposto pis.");
            if($crc=='LR'){//LAUDO
                $rown['pis']=($rown['subtotal']*0.0065)* -1;                
            }else{//INATA
                $rown['pis']=($rown['subtotal']*0.0065);
            }
            
            $rown['pis'] = truncarNumero($rown['pis']);

			//imposto($rown,$_SESSION['numeronfe'],$rown['pis'],$rown['emissao'],$rown['idnotafiscal'],'PIS',$obs=null);
            geraparcelaimposto($rown,$rown['pis'],$rown['emissao'],$rown['idnotafiscal'],'PIS',$obs=null);
	
            echo("<br> Gerar imposto cofins.");

            if($crc=='LR'){//LAUDO
                $rown['cofins']=($rown['subtotal']*0.03)* -1;                
            }else{//INATA
                $rown['cofins']=($rown['subtotal']*0.03);
            }
            $rown['cofins'] = truncarNumero($rown['cofins']);
			//imposto($rown,$_SESSION['numeronfe'],$rown['cofins'],$rown['emissao'],$rown['idnotafiscal'],'COFINS',$obs=null);
            geraparcelaimposto($rown,$rown['cofins'],$rown['emissao'],$rown['idnotafiscal'],'COFINS',$obs=null);
		}elseif($rown['tiporecolhimento']=='A' and  $rown['subtotal'] > 0.01){
            $rown['iss']=($rown['subtotal']*0.02);

            $rown['iss'] = truncarNumero($rown['iss']);
            if ($rown['iss'] > 0) {
                echo "<br>Gerar imposto ISS.";
                imposto($rown, $_SESSION['numeronfe'], $rown['iss'], $rown['emissao'], $rown['idnotafiscal'], 'ISSRECOLHER', $obs=null);        
            }
		       
        }

        if($crc=='LR'){//LAUDO
        
            $rown['pis']=($rown['subtotal']*0.0165); 
        
            $rown['pis'] = truncarNumero($rown['pis']);
            echo("<br> Gerar imposto pis lr.");
            //imposto($rown,$_SESSION['numeronfe'],$rown['pis'],$rown['emissao'],$rown['idnotafiscal'],'PIS',$obs=null);
            geraparcelaimposto($rown,$rown['pis'],$rown['emissao'],$rown['idnotafiscal'],'PIS',$obs=null);

            echo("<br> Gerar imposto cofins lr.");
        
            $rown['cofins']=($rown['subtotal']*0.076);            
            $rown['cofins'] = truncarNumero($rown['cofins']);
            //imposto($rown,$_SESSION['numeronfe'],$rown['cofins'],$rown['emissao'],$rown['idnotafiscal'],'COFINS',$obs=null);
            geraparcelaimposto($rown,$rown['cofins'],$rown['emissao'],$rown['idnotafiscal'],'COFINS',$obs=null);

        }

        echo("<br> Finalizado impostos.");
		$idfluxostatus=828;


		$sqlEtapaHistInsert = "INSERT INTO fluxostatushist (idempresa, idfluxostatus, idmodulo, modulo, status, criadopor, criadoem, alteradopor, alteradoem) 
											VALUES (".$row1["idempresa"].", '$idfluxostatus', '$idnotafiscal', 'nfs', 'ATIVO', 'cron', now(), 'cron', now())"; 
		$res = d::b()->query($sqlEtapaHistInsert);

        echo("<br> inserido no fluxo.");
		$atualiza = "UPDATE fluxostatushist SET status = 'ATIVO' where idmodulo=".$idnotafiscal." and modulo='nfs'";
        d::b()->query($atualiza) or die("[_fluxo:] Erro ao atualizar fluxostatushist: ". mysql_error() . "<p>SQL: ".$atualiza);
        echo("<br> Atualizado o fluxo.");
		// GVT - 19/05/2021 - atualizando enviadetalhenfe, enviadanfnfe, emailboleto = 'G' para que a cron geraarquivosnfs.php crie os arquivos.
		$sqlnf="update notafiscal n
				set n.enviadetalhenfe = 'G', n.enviadanfnfe = 'G', n.emailboleto = 'G', n.nnfe='".$_SESSION['numeronfe']."',n.idfluxostatus='".$idfluxostatus."',n.codver= '".$_SESSION['codver']."',n.status = 'CONCLUIDO'
		 		where n.numerorps='".$row1['numerorps']."' and n.idempresa=".$row1['idempresa'];		
		echo($sqlnf);
        echo("<br> Atualizado NFS.");
		$retnf = d::b()->query($sqlnf);
		
		if(!$retnf){
			//LOG($inidlote,$inetapa,$incoderro,$inerro,$inxml);
			echo("Erro ao alterar NF: \n<br>".mysqli_error(d::b())."\n<br>".$sqlnf);
			return false;
		}else{
			return true;
		}	
	}else{
		echo("  (".$instatus.") ");
	}
	
}

$cpfCnpjRemetente = "23259427000104";//T14 //Cnpj do prestador do serviço
$codcid = 5403;  //codigo da cidade prestador do serviço

//destravar caso a conexão finalize de forma inesperada e a linha permaneça no status CONSULTANDO.@874398
$sqlac="update nfslote n set status='PENDENTE' where n.status = 'CONSULTANDO' and n.criadoem < DATE_SUB(now(), INTERVAL 1 MINUTE)";
$retac = d::b()->query($sqlac);

//Altera o status das notas PENDENTES para CONSULTANDO e reserva para a sessão
$sqlc = "update nfslote set status = 'CONSULTANDO', sessionid = '".$sessionid."',alteradoem = sysdate() where loteprefeitura is not null and status = 'PENDENTE'";
$retc = d::b()->query($sqlc);

if(!$retc){
	//LOG($inidlote,$inetapa,$incoderro,$inerro,$inxml);
	echo("Erro ao alterar LOTE para consulta: \n<br>".mysqli_error(d::b())."\n<br>".$sqllog);
	return false;
}


$sql= "SELECT e.cnpj,e.CodCidade, e.InscricaoMunicipalPrestador, e.senha, SUBSTRING(e.certificado, 10) as certificado, n.* FROM `nfslote` n join empresa e on(e.idempresa = n.idempresa)
		where n.status = 'CONSULTANDO'
		and n.sessionid = '".$sessionid."' order by n.numerorps asc";

$sqlres = d::b()->query($sql) or die("A Consulta dos lotes falhou : " . mysqli_error(d::b()) . "<p>SQL: $sql");

while ($row = mysqli_fetch_array($sqlres)){
    $_SESSION["numeronfe"]=null;
    $_SESSION["codver"]=null;
    $vsituacao=null;
        
    $cpfCnpjRemetente = $row["cnpj"];//T14 //Cnpj do prestador do serviço
    $codcid = $row["CodCidade"];  //codigo da cidade prestador do serviço
    $inscricaoMunicipalPrestador = $row["InscricaoMunicipalPrestador"];
	
	//INSERIR STATUS NA ETAPA COMO  CONSULTA
	LOGETAPA($row["idnfslote"],$row['idempresa'],"CONSULTA");
	
	//$vurl= "http://udigital.uberlandia.mg.gov.br/WsNFe2/LoteRps.jws?wsdl";//produção
	//$vurl= "http://200.201.194.78/WsNFe2/LoteRps.jws?wsdl";//homologação

    $xmlCabecalho = '<?xml version="1.0" encoding="UTF-8"?><cabecalho xmlns="http://www.abrasf.org.br/nfse.xsd" versao="2.04"><versaoDados>2.04</versaoDados></cabecalho>';

	//XML DE CONSULTA 
	$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>"
					. "<ns1:ReqConsultaLote xmlns:ns1=\"http://localhost:8080/WsNFe2/lote\" xmlns:tipos=\"http://localhost:8080/WsNFe2/tp\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://localhost:8080/WsNFe2/lote http://localhost:8080/WsNFe2/xsd/ReqConsultaLote.xsd\">"
					. "<Cabecalho><CodCidade>"
					. $codcid
					. "</CodCidade><CPFCNPJRemetente>"
					. $cpfCnpjRemetente
					. "</CPFCNPJRemetente>"
					. "<Versao>1</Versao><NumeroLote>" ."".$row["loteprefeitura"].""."</NumeroLote> 
					</Cabecalho>
					</ns1:ReqConsultaLote >";
    
    $xmlDados = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>"
        . "<ConsultarLoteRpsEnvio xmlns=\"http://www.abrasf.org.br/nfse.xsd\" xmlns:ns2=\"http://www.w3.org/2000/09/xmldsig#\">"
        . "   <Prestador>"
        . "       <CpfCnpj>"
        . "           <Cnpj>" . $cpfCnpjRemetente ."</Cnpj>"
        . "       </CpfCnpj>"
        . "       <InscricaoMunicipal>" . $inscricaoMunicipalPrestador . "</InscricaoMunicipal>"
        . "   </Prestador>"
        . "   <Protocolo>". $row["protocoloprefeitura"] ."</Protocolo>"
        . "</ConsultarLoteRpsEnvio>";
    
    $urlwsdl = "https://nfsews.uberlandia.mg.gov.br:8003/nfse-ws/soap/nfse.wsdl";
    // URL do ambiente de homologação
    $urlsoap = "https://nfsews.uberlandia.mg.gov.br:8003/nfse-ws/soap/nfse";
						
	ini_set("soap.wsdl_cache_enabled", "0");

    $certificadopfx = "../inc/nfe/sefaz4/certs/".$row["certificado"];
    if (define('STDIN')) {
        $certificadopfx = "/var/www/carbon8/inc/nfe/sefaz4/certs/".$row["certificado"];
    }
    $passpfx = $row['senha'];
    
    $pfx = file_get_contents($certificadopfx);
    if ($pfx === false) {
        throw new Exception("Não foi possível ler o arquivo PFX.");
    }

    $certs = array();
    if (!openssl_pkcs12_read($pfx, $certs,  $passpfx)) {
        throw new Exception("Não foi possível ler o arquivo PFX.");
    }

    $certificado_crt = $certs['cert'];
    $chave_privada_key = $certs['pkey'];
    
    $tempCertFile = tempnam(sys_get_temp_dir(), 'cert');
    file_put_contents($tempCertFile, $certificado_crt . $chave_privada_key);

	//conexão e envio SOAP
    $options = array(
        'trace' => 1,
        'cache_wsdl' => WSDL_CACHE_NONE,
        'exceptions' => 1,
        'use' => SOAP_LITERAL,
        'stream_context' => stream_context_create(array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'local_cert' => $tempCertFile,
                'passphrase' => $passpfx
            )
        ))
    );

	$soapClient = new SoapClient($urlwsdl, $options);
    $soapClient->__setLocation($urlsoap);

    $params = new stdClass();
    $params->nfseCabecMsg = $xmlCabecalho;
    $params->nfseDadosMsg = $xmlDados;

	$res= $soapClient->__soapCall("ConsultarLoteRps",array($params));	

    echo "Resposta: " . var_export($res, true);
	//armazena o XML retornado
	$_SESSION["xml"] = $res->outputXML;
	
	$doc = DOMDocument::loadXML($res->outputXML);
    $xpath = new DOMXPath($doc);
    $xpath->registerNamespace('ns', 'http://www.abrasf.org.br/nfse.xsd');

	if (!$doc){
		$errocon = "O WS não retornou XML válido:\n Lote - >".$row["loteprefeitura"]." MSG - >".$res."\n<BR>";
		$_SESSION["errocon"] = $errocon;
		STATUSLOTE($row["idnfslote"],"PENDENTE",$row["numerorps"]); 
		LOGETAPA($row["idnfslote"],$row['idempresa'],"CONSULTA");	
		echo $errocon;		
	}else{
        //$situacao = $doc->getElementsByTagName("situacao")->item(0);

        //pega o valor da tag Sucesso
        if($xpath->query('//ns:Situacao')->length){//verifica se a tag xlm situação foi enviada corretamente
            $vsituacao = $xpath->query('//ns:Situacao')->item(0)->textContent;//pega o codigo de retorno da situação
        }else{
            echo "Não foi possível obter a tag xml de situação";
        }
        //se a tag Sucesso for true o lote foi processado com sucesso			
        echo "<br><br>If Inicio<br><br>";
        if($vsituacao=="4"){	
            // pega o número da notafiscal gerada
            $_SESSION["numeronfe"] = $xpath->query('//ns:ListaNfse/ns:CompNfse/ns:Nfse/ns:InfNfse/ns:Numero')->item(0)->textContent;
            $_SESSION["codver"] = $xpath->query('//ns:ListaNfse/ns:CompNfse/ns:Nfse/ns:InfNfse/ns:CodigoVerificacao')->item(0)->textContent;

            STATUSLOTE($row["idnfslote"],"SUCESSO",$row["numerorps"]); //("OK PROCESSADO");
            echo "Sucesso Lote ".$row["loteprefeitura"]." Nfe N:".$_SESSION["numeronfe"] ;			
       
        }elseif($vsituacao=="3"){//se for false pode ser que tenha erro ou alerta
            $mensagensErro = '';
            for ($i = 0; $i <= $xpath->query('//ns:Mensagem')->length; $i++) {
                $mensagensErro .= $xpath->query('//ns:Mensagem')->item($i)->textContent;
            }

            if(!$mensagensErro){//se a tag erro for vazia o false da tag Sucesso e referente a alertas 
                $errocon = ("LOTE COM ALERTA ->".$row["loteprefeitura"]."; Mesagem:".$mensagensErro);
                $_SESSION["errocon"] = $errocon;
                STATUSLOTE($row["idnfslote"],"PENDENTE",$row["numerorps"]); 
                LOGETAPA($row["idnfslote"],$row['idempresa'],"CONSULTA");	
                echo $errocon;
                                
            }else{//se a tag erro estiver preenchida o lote esta com erro					
                $errocon = ("LOTE COM ERRO - >".$row["loteprefeitura"]."; Mesagem:".$mensagensErro);
                $_SESSION["errocon"] = $errocon;
                STATUSLOTE($row["idnfslote"],"ERRO",$row["numerorps"]);
                LOGETAPA($row["idnfslote"],$row['idempresa'],"CONSULTA");
                echo $errocon;
            }            
        }elseif($vsituacao=="1"){//Não Recebido

            // ocorre situações onde o servidor de assinaturas BR fica fora do ar
            $xmlContent = $_SESSION["xml"];
            // Verifica se a string contém "brasil.certisign"
            if (strpos($xmlContent, "brasil.certisign") !== false) {
                // "A palavra 'brasil.certisign' foi encontrada na string."
                $errocon = ("Valor da Tag Sucesso imprevisto - >".$row["loteprefeitura"]);
                $_SESSION["errocon"] = $errocon;
                STATUSLOTE($row["idnfslote"],"PENDENTE",$row["numerorps"]);
                LOGETAPA($row["idnfslote"],$row['idempresa'],"CONSULTA");
                echo $errocon;
            } else {
                // "A palavra 'brasil.certisign' NÃO foi encontrada na string.";
                $errocon = ("LOTE COM ERRO - >".$row["loteprefeitura"]."; Mesagem:".$mensagensErro);
                $_SESSION["errocon"] = $errocon;
                STATUSLOTE($row["idnfslote"],"ERRO",$row["numerorps"]);
                LOGETAPA($row["idnfslote"],$row['idempresa'],"CONSULTA");
                echo $errocon;
            }


          
        }else{
            $errocon = ("Valor da Tag Sucesso imprevisto - >".$row["loteprefeitura"]);
            $_SESSION["errocon"] = $errocon;
            STATUSLOTE($row["idnfslote"],"PENDENTE",$row["numerorps"]);
            LOGETAPA($row["idnfslote"],$row['idempresa'],"CONSULTA");
            echo $errocon;
        }
        echo "<br><br>Else Fim<br><br>";
	}
    //echo($res);
}

$sqlf = "update nfslote set status = 'PENDENTE',alteradoem = sysdate() where status = 'CONSULTANDO' and sessionid = '".$sessionid."'";
$retf = d::b()->query($sqlf);

if(!$retf){
	//LOG($inidlote,$inetapa,$incoderro,$inerro,$inxml);
	echo("Erro ao voltar o status do LOTE consulta: \n<br>".mysqli_error(d::b())."\n<br>".$sqllog);
	return false;
}

re::dis()->hMSet('cron:processapendentes',['fim' => Date('d/m/Y H:i:s')]);

$sqli = "INSERT INTO `laudo`.`log` (`idempresa`, `sessao`, `tipoobjeto`, `idobjeto`, `tipolog`, `log`, `status`, `criadoem`, `data`) 
							VALUES ('1', '".$grupo ."','cron', 'processapendentes', 'status', 'FIM', 'SUCESSO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";

d::b()->query($sqli) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqli);


?>
