<?
require_once(__DIR__."/../../form/controllers/fluxo_controller.php");

class cnf{
   
    public static $idempresa;

    //inserir informação no banco de dados
    static public function inseredb($arrvalor,$tabela){   
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
    static public function agrupaNfitem($inidnfitem,$tiponf=null){
        
        $sql="select i.idempresa,i.idnfitem,i.total,i.idpessoa,i.dataitem,i.prodservdescr,c.agrupnota,c.agruppessoa,c.tiponf,c.idformapagamento,c.statusnf
        from nfitem i join confcontapagar c on(c.idconfcontapagar=i.idconfcontapagar)
        where i.idnf is null 
        and i.idconfcontapagar is not null  
        and i.idnfitem = ".$inidnfitem;
        $res= d::b()->query($sql) or die("[Laudo:] Erro ao buscar itens de nf para agrupamento : ". mysql_error() . "<p>SQL: ".$sql);
    
        while($row=mysqli_fetch_assoc($res)){
            if($row['agruppessoa']=='Y'){// procura um nota para entrar ou cria uma 1 para n
                $sql1="select * from nf n
                        where n.tiponf = '".$row['tiponf']."' 
                        and n.dtemissao between concat(date_add(date_add(LAST_DAY('".$row['dataitem']."'),interval 1 DAY),interval -1 MONTH),' 00:00:00') and concat(LAST_DAY('".$row['dataitem']."'),' 00:00:00')
                        and n.idpessoa = ".$row['idpessoa']." 
                        and n.idempresa=".$row['idempresa']."
                        and not exists (select 1 from contapagar c where c.idobjeto= n.idnf and c.tipoobjeto = 'nf' and c.status='QUITADO')
                        and n.status ='".$row['statusnf']."' order by n.dtemissao desc limit 1";
                $res1= d::b()->query($sql1) or die("[Laudo:] Erro 2 ao buscar  nf para agrupamento : ". mysql_error() . "<p>SQL: ".$sql1);
                $qtd=mysqli_num_rows($res1);
                if($qtd>0){
                    $row1=mysqli_fetch_assoc($res1);
                    $idnf=$row1['idnf'];
                  //  $su="update nfconfpagar set datareceb='".$row['dataitem']."',obs='".$row['prodservdescr']."' where idnf=".$idnf;
                   // $rru= d::b()->query($su);

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
                        $rsUnid = d::b()->query($qrUnid) or die("[api/nf][1]: Erro ao buscar idunidade. SQL: ".$qrUnid);
                        $rwUnid = mysqli_fetch_assoc($rsUnid);
                    }
                    

                    //LTM - 05-04-2021: Retorna o Idfluxo nf para Pedido (Tipo V)
                    $idfluxostatus = FluxoController::getIdFluxoStatus($modulo, $row['statusnf']);

                    $arrinsnf[1]['idpessoa']=$row['idpessoa'];		
                    $arrinsnf[1]['total']=$row['total'];
                    $arrinsnf[1]['subtotal']=$row['total'];
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
                    $arrinsnf[1]['status']=$row['statusnf'];
                    $arrinsnf[1]['idfluxostatus'] = $idfluxostatus;
                    $arrinsnf[1]['idempresa'] =$row['idempresa'];
                    $arrinsnf[1]['parcelas']=1;
                    $arrinsnf[1]['diasentrada']=1;					
                    $arrinsnf[1]['idformapagamento']=$row['idformapagamento'];

                    $idnf=cnf::inseredb($arrinsnf,'nf');
                    $idnf=$idnf[0];

                    FluxoController::inserirFluxoStatusHist($modulo, $idnf, $idfluxostatus, $row['statusnf']);
                    //LTM - 05-04-2021: Retorna o Idfluxo nf
                  //  $idfluxostatus = FluxoController::getIdFluxoStatus($modulo, 'INICIO');

                    $arrinsnfcp[1]['idnf']=$idnf;	
                    $arrinsnfcp[1]['parcela']=1;
                    $arrinsnfcp[1]['idformapagamento']=$row['idformapagamento'];
                    $arrinsnfcp[1]['idempresa'] =$row['idempresa'];
                    $arrinsnfcp[1]['proporcao']=100;
                    $arrinsnfcp[1]['datareceb']=$row['dataitem'];
                    $arrinsnfcp[1]['obs']=$row['prodservdescr'];
                    
                    $idnfconfpagar=cnf::inseredb($arrinsnfcp,'nfconfpagar');
                
                }
    
            }else{//if($row['agruppessoa']=='Y'){// cria uma nota para vinculo 1 para 1
               
                    $sql1="select * from nf n
                            where n.tiponf = '".$row['tiponf']."' 
                            and  n.dtemissao >='".$row['dataitem']." 00:00:00' 
                            and  n.idpessoa = ".$row['idpessoa']." 
                            and n.idempresa=".$row['idempresa']."
                            and not exists(select 1 from nfitem i where i.idnf=n.idnf)
                            and not exists (select 1 from contapagar c where c.idobjeto= n.idnf and c.tipoobjeto = 'nf' and c.status='QUITADO')
                            and  n.status ='".$row['statusnf']."' order by  n.dtemissao asc limit 1";
                    $res1= d::b()->query($sql1) or die("[Laudo:] Erro 2 ao buscar  nf para agrupamento : ". mysql_error() . "<p>SQL: ".$sql1);
                    $qtd=mysqli_num_rows($res1);
                    if($qtd>0){
                        $row1=mysqli_fetch_assoc($res1);
                        $idnf=$row1['idnf'];
                      //  $su="update nfconfpagar set datareceb='".$row['dataitem']."', obs='".$row['prodservdescr']."' where idnf=".$idnf;
                        // $rru= d::b()->query($su);
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
                            $rsUnid = d::b()->query($qrUnid) or die("[api/nf][2]: Erro ao buscar idunidade. SQL: ".$qrUnid);
                            $rwUnid = mysqli_fetch_assoc($rsUnid);
                        }

                        $idfluxostatus = FluxoController::getIdFluxoStatus($modulo, $row['statusnf']);

                        $arrinsnf[1]['idpessoa']=$row['idpessoa'];		
                        $arrinsnf[1]['total']=$row['total'];
                        $arrinsnf[1]['subtotal']=$row['total'];
                        $arrinsnf[1]['dtemissao']=$row['dataitem']." 00:00:00";
                        $arrinsnf[1]['tiponf']=$row['tiponf'];
                        if($row['tiponf'] != 'V'){
                            $arrinsnf[1]['idunidade']=$rwUnid['idunidade'];
                        }
                        $arrinsnf[1]['geracontapagar']='Y';
                        $arrinsnf[1]['status']=$row['statusnf'];
                        $arrinsnf[1]['idfluxostatus'] = $idfluxostatus;
                        $arrinsnf[1]['parcelas']=1;
                        $arrinsnf[1]['diasentrada']=1;					
                        $arrinsnf[1]['idformapagamento']=$row['idformapagamento'];
                        $arrinsnf[1]['idempresa'] =$row['idempresa'];

                        $idnf=cnf::inseredb($arrinsnf,'nf');
                        $idnf=$idnf[0];

                        FluxoController::inserirFluxoStatusHist($modulo, $idnf, $idfluxostatus,$row['statusnf']);

                        $arrinsnfcp[1]['idnf']=$idnf;	
                        $arrinsnfcp[1]['parcela']=1;
                        $arrinsnfcp[1]['idformapagamento']=$row['idformapagamento'];
                        $arrinsnfcp[1]['proporcao']=100;
                        $arrinsnfcp[1]['datareceb']=$row['dataitem'];
                        $arrinsnfcp[1]['obs']=$row['prodservdescr'];
                        $arrinsnfcp[1]['idempresa'] =$row['idempresa'];
                        
                        $idnfconfpagar=cnf::inseredb($arrinsnfcp,'nfconfpagar');
                    }
               

            }//if($row['agruppessoa']=='Y'){
            if(!empty($idnf)){
                $sqlu="update nfitem set idnf=".$idnf.",nfe='Y',tiponf='".$row['tiponf']."' where idnfitem=".$row['idnfitem'];
                $resu= d::b()->query($sqlu) or die("[Laudo:] Erro 3 ao atualizar nf item : ". mysql_error() . "<p>SQL: ".$sqlu);
                //gerar faturamento
                if($row['statusnf']=='CONCLUIDO' AND !empty($idnf)){
                    cnf::geraRateio($idnf);
                }

                return $idnf;
            }else{
                return 'Erro ao agrupar nfitem';
            }
    
        }//while($row=mysqli_fetch_assoc($sql)){
    }//function agrupaNfitem(){
        static public function atualizavalornf($idnotafiscal){

            $sql="select ifnull(sum(i.total),0) as total from  nfitem i 
                where i.idnf=".$idnotafiscal;
                $re= d::b()->query($sql)or die("[index:atualizavalornf] Erro 1 ao buscar valor da nf : ". mysql_error() . "<p>SQL: ".$sql);
            $row=mysqli_fetch_assoc($re);

            $sqlu="update nf set subtotal='".$row['total']."',total='".$row['total']."' where idnf=".$idnotafiscal;
            $resu= d::b()->query($sqlu) or die("[index:atualizavalornf] Erro 2 ao atualizar nf  : ". mysql_error() . "<p>SQL: ".$sqlu);
            //gerar faturamento
            return $idnf;

        }

    
    //gerar faturamento contapagar ou contapagaritem
    static public function gerarContapagar($idnotafiscal, $status = null){

        $sql="select * from nf where idnf=".$idnotafiscal;
        $res= d::b()->query($sql) or die("[Laudo:] Erro gerarContapagaritem ao busca dados da nf  : ". mysql_error() . "<p>SQL: ".$sql);
        $row=mysqli_fetch_assoc($res);

        if($row['geracontapagar']=="Y"){	

            $sf="select idagencia,agruppessoa,agrupado,agrupfpagamento,agrupnota,tipoespecifico from formapagamento where idformapagamento=".$row['idformapagamento'];
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
            
            if ($row['tipocontapagar'] == "D") {
                $tipo = "D";
            } else {
                $tipo = "C";
            }

            if ($row['tiponf'] == "V") {
                $visivel = "S";
            } elseif ($row['tiponf'] == "C" or $row['tiponf'] == "T" or $row['tiponf'] == "S" or $row['tiponf'] == "E" or $row['tiponf'] == "M" or $row['tiponf'] == "B") {
                $visivel = "S";
            } elseif ($row['tiponf'] == "D" or $row['tiponf'] == "R") {
                $visivel = "N";
            } else {
                $visivel = 'N';
            }

            $index = 0;
            while($rowcx=mysqli_fetch_assoc($rescx)){
                $index++;		 
            
                //Insere novas parcelas
                $valorparcela = $row['total']*($rowcx['proporcao']/100);

                $valorparcelarep =(($row['total']-$rowf['sumfrete'])/($rowcx['proporcao']/100));

                $vencimentocalc = $rowcx['datareceb'];
                $recebcalc = $rowcx['datareceb'];

                if($formapagamento['tipoespecifico']=='REPRESENTACAO' or $status == 'ABERTO') {
                    $status='ABERTO';
                }else{
                    $status='PENDENTE';
                }

                if($formapagamento['agrupado']=='Y'){//se for agrupado	

                    $insnfcp[1]['status']=$status;	
                    $insnfcp[1]['idpessoa']=$row['idpessoa'];
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
                    $insnfcp[1]['idempresa'] =self::$idempresa;
                        

                    $idnfcp=cnf::inseredb($insnfcp,'contapagaritem');
                    
                }else{	
                    $idfluxostatus = FluxoController::getIdFluxoStatus('contapagar', $status);
                    $insnfcp[1]['status']=$status;
                    $insnfcp[1]['idfluxostatus'] = $idfluxostatus;
                    $insnfcp[1]['idformapagamento']=$row['idformapagamento'];
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
                    $insnfcp[1]['idempresa'] =self::$idempresa;

                    $idnfcp = cnf::inseredb($insnfcp,'contapagar');
                    FluxoController::inserirFluxoStatusHist('contapagar', $idnfcp, $idfluxostatus, 'PENDENTE');
                }            

            }//for ($index = 1; $index <= $qtdparcelas; $index++) {
           
        }
    }//function gerarContapagaritem($idnotafiscal){

    // busca os dados para as parcelas automáticas
    static public function getDadosConfContapagar($tipo){
        $sqlrep = "select * from confcontapagar where status='ATIVO' and idempresa = ".self::$idempresa." and tipo='".$tipo."'";
        $resrep = d::b()->query($sqlrep) or die("A Consulta de configuração automatica contapagar falhou :".mysqli_error(d::b())."<br>Sql:".$sqlrep);
        $qtdresp = mysqli_num_rows($resrep); 
        
        if($qtdresp<1){
            $surl='?_modulo=confcontapagar&_autofiltro={"col":"idempresa","id":"'.self::$idempresa.'","valor":"'.self::$idempresa.'"}';
            die('Não encontrada a configuração para a parcela automatica '.$tipo.'.<br><a target=_blank href=\''.$surl.'\'>Configurar</a>');
        }       
        $rowrep= mysql_fetch_assoc($resrep);
             
        return $rowrep;    
    }

    static public function buscanfitem($idnf,$idpessoa){
        $sqlrep="select * from nfitem i where i.idnf=".$idnf."  and i.idpessoa =".$idpessoa;
        $resrep = d::b()->query($sqlrep) or die("[api-nf]- A Consulta na nfitem falhou :".mysqli_error(d::b())."<br>Sql:".$sqlrep);
        $qtdresp = mysqli_num_rows($resrep); 
        
        if($qtdresp<1){
            return 0;
        }else{
            $rowrep= mysql_fetch_assoc($resrep);
             
            return $rowrep;  
        }
    }

    static public function atualizanfitem($idnfitem,$valor,$idcontaitem=null,$idtipoprodserv=null){

        if(!empty($idcontaitem) and !empty($idtipoprodserv)){
            $squ="update nfitem set total='".$valor."',vlritem='".$valor."',idcontaitem=".$idcontaitem.",idtipoprodserv=".$idtipoprodserv." where idnfitem=".$idnfitem;
        }else{
            $squ="update nfitem set total='".$valor."',vlritem='".$valor."' where idnfitem=".$idnfitem;
        }
        
        $reu= d::b()->query($squ) or die($squ."[api/nf]-Erro atualizar nfitem : <br>".mysqli_error(d::b()));

    }

    // monta um array para inserção na nfitem
    static public function montaarrnfitem($inpost,$arrconfCP,$vlritem,$vlrtotal,$prodservdescr){
        $ndtemissao = validadate($inpost["_1_u_nf_dtemissao"]);

        $vencimentocalc = " SELECT (LAST_DAY('".$ndtemissao."') + INTERVAL 15 DAY) as dataitem ";
        $resvenc=d::b()->query($vencimentocalc) or die("Erro ao buscar vencimento do montaarrnfitem: sql=".$vencimentocalc." mysql".mysqli_error(d::b()));
        $rowvenc=mysqli_fetch_assoc($resvenc);
        
        if(empty($rowvenc['dataitem'])){ die("Erro ao buscar data do item montaarrnfitem.");}                   
    
        $arrnfitem=array();
        $arrnfitem[1]['qtd']=1;
        $arrnfitem[1]['vlritem']=$vlritem;
        $arrnfitem[1]['total']=$vlrtotal;
        $arrnfitem[1]['tiponf']='C';
        $arrnfitem[1]['prodservdescr']=$prodservdescr;
        $arrnfitem[1]['idcontaitem']=$arrconfCP['idcontaitem'];
        $arrnfitem[1]['idtipoprodserv']=$arrconfCP['idtipoprodserv'];
        if(!empty($arrconfCP['idpessoa'])){
            $arrnfitem[1]['idpessoa']=$arrconfCP['idpessoa'];
        }else{
            $arrnfitem[1]['idpessoa']=$inpost['_1_u_nf_idpessoa'];
        }    
        $arrnfitem[1]['idobjetoitem']=$inpost['_1_u_nf_idnf'];
        $arrnfitem[1]['tipoobjetoitem']='nf';
        $arrnfitem[1]['statusitem']='PENDENTE';
        $arrnfitem[1]['idconfcontapagar']=$arrconfCP['idconfcontapagar'];
        $arrnfitem[1]['dataitem']=$rowvenc['dataitem'];
        $arrnfitem[1]['idempresa'] =self::$idempresa;

        return $arrnfitem;
    }

 

    
###############

    static public function alterafrete($modfrete,$iu,$idnotafiscal,$statusant,$tiponf){ 
        
        if($modfrete==9 and $iu == "u" and !empty($idnotafiscal) and $statusant != "CONCLUIDO"){
        d::b()->query("update nfitem set frete=0 where idnf =".$idnotafiscal);		
        }

        if($iu == "u" and !empty($idnotafiscal) and $statusant != "CONCLUIDO" and $tiponf!="C"){
        $sql="select ifnull(sum(frete),0) as sumfrete
        from nfitem
        where idnf =".$idnotafiscal;
        $res=d::b()->query($sql) or die("erro ao verificar itens da notafiscal sql=".$sql);
        $row= mysqli_fetch_assoc($res);

        d::b()->query("update nf set frete = ".$row['sumfrete']." where idnf =".$idnotafiscal);    
        }

    }

    static public function recuperaParcelas($inidobj,$instatus,$intipoobjeto){
        
        /*
        * verifica se existe alguma parcela quitada. se existir, nao alterar nada.
        */
        $sqlverifquit = "select count(*) as quant from contapagar where status = '".$instatus."'    and tipoobjeto='".$intipoobjeto."' and idobjeto = ".$inidobj;
    
        $resverif = d::b()->query($sqlverifquit) or die($sqlverifquit."Erro ao consultar parcelas da nota: <br>".mysqli_error(d::b()));
        $rowverif = mysqli_fetch_array($resverif);

        return  $rowverif;
    }

    static public function recuperaParcelasProg($idnotafiscal,$intipoobjeto){
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

    static public function recuperaParcelasItensVinc($inidobj,$intipoobjeto){
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

    static public function verificaboleto($inidnf){
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

    static public function getParcelaItens($idnotafiscal){
        /*
        * verifica se existe alguma parcela item quitada. se existir, nao alterar nada.
        */
        $sqlveritem = "select count(*) as quant from contapagaritem where  idobjetoorigem= ".$idnotafiscal." and tipoobjetoorigem = 'nf' and status in('QUITADO')";
        
        $resveritem = d::b()->query($sqlveritem) or die($sqlveritem."Erro ao consultar parcelas item do cte: <br>".mysqli_error(d::b()));
        $rowverifitem = mysqli_fetch_array($resveritem);
        return  $rowverifitem;
    }

    static public function getParcelaItensfechada($idnotafiscal,$agrupnota){
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

    static public function deletaParcelasExistentes($idnotafiscal){
            /*
        * deleta as parcelas existentes.
        */
            $tmpsqldel = "delete cc.* 
                            from contapagar c,contapagaritem cc
                            where c.tipoobjeto = 'nf' 
                            and c.idobjeto =".$idnotafiscal."
                            and cc.idobjetoorigem = c.idcontapagar
                and cc.tipoobjetoorigem ='contapagar'
                and cc.status in ('ABERTO','PENDENTE')";
        d::b()->query($tmpsqldel) or die("Erro ao retirar parcelas de comissão da Nota Fiscal : <br>".mysqli_error(d::b()));
        
        
        //if($contapagaritem=="Y"){
        $tmpsqldel = "delete from contapagaritem where tipoobjetoorigem =  'nf' and idobjetoorigem = ".$idnotafiscal."  and status in ('ABERTO','PENDENTE')";
        d::b()->query($tmpsqldel) or die("Erro ao retirar parcelas da Nota Fiscal: <br>".mysqli_error(d::b()));
        //}
            
        $tmpsqldel = "delete c.* from contapagar c 
            where c.tipoobjeto = 'nf' 
            and c.status!='QUITADO'
                    and not exists(select 1 from contapagaritem i where i.idcontapagar=c.idcontapagar and i.tipoobjetoorigem='contapagar')
            and c.idobjeto = ".$idnotafiscal;
        d::b()->query($tmpsqldel) or die("Erro ao retirar parcelas da Nota Fiscal: <br>".mysqli_error(d::b()));
            
    }

    static public function getDadosComissao($idpessoa){
        $sqlrep = "select 
                p.idpessoa
                ,c.participacaoserv
                ,c.participacaoprod
                from pessoa p
                ,pessoacontato c
                where p.status='ATIVO'
                and p.idtipopessoa in (12,1)
                and c.participacaoprod>0
                and  p.idpessoa = c.idcontato
                and p.idempresa = ".self::$idempresa."
                and c.idpessoa = ".$idpessoa." order by nome";
                $resrep = d::b()->query($sqlrep) or die("A Consulta do representante falhou :".mysqli_error(d::b())."<br>Sql:".$sqlrep);	
                $rowrep= mysqli_fetch_assoc($resrep);
            
            return $rowrep;    
    }



    static public function corrigirParcelas($idnotafiscal,$total,$contapagaritem){
        
        //corrigir parcelas em um centavo
        
        $sqls="select sum(valor) as vvalor,max(parcela) as mparcela from contapagar where idobjeto=".$idnotafiscal." and tipoobjeto='nf' and idempresa=".self::$idempresa;
        $ress=d::b()->query($sqls) or die("Erro ao somar valor das parcelas sql=".$sqls);
        $rows=mysqli_fetch_assoc($ress);
        
        if($rows['vvalor']!=$total and ($contapagaritem!="Y")){
            
            if($rows['vvalor']>$total){
                    $sqlup="update contapagar set valor=valor-0.01
                                    where idobjeto=".$idnotafiscal."
                                    and tipoobjeto='nf'
                                    and parcela = 1
                    and status!='QUITADO'
                                    and idempresa=".self::$idempresa;
                }elseif($rows['vvalor']<$total){
                    $sqlup="update contapagar set valor=valor+0.01
                                    where idobjeto=".$idnotafiscal."
                                    and tipoobjeto='nf'
                    and status!='QUITADO'
                                    and parcela = ".$rows['mparcela']."
                                    and idempresa=".self::$idempresa;
                }
                if(!empty($sqlup)){
                    d::b()->query($sqlup) or die("erro ao atualizar parcelas sql=".$sqlup);
                }
        } 
    }

    static public function getComisssoesPendentes(){
        global $idnotafiscal;
            /*
        * verifica se existe alguma parcela quitada. se existir, nao alterar nada.
        */
        $sqlverifquit = "select count(*) as quant  from contapagar c,contapagaritem cc
                    where c.tipoobjeto = 'nf' 
                    and c.idobjeto =".$idnotafiscal."
                    and cc.idobjetoorigem = c.idcontapagar
                    and cc.tipoobjetoorigem ='contapagar'
                    and cc.status !='QUITADO' ";
        //echo $sqlverifquit;
        $resverif = d::b()->query($sqlverifquit) or die($sqlverifquit."Erro ao consultar parcelas do representante para delecao: <br>".mysqli_error(d::b()));
        $rowverif = mysqli_fetch_array($resverif);
        return $rowverif;
    }

    static public function deletaComissoesPendentes(){
        $tmpsqldel = "delete cc.* 
                    from contapagar c,contapagaritem cc
                    where c.tipoobjeto = 'nf' 
                    and c.idobjeto =".$idnotafiscal."
                    and cc.idobjetoorigem = c.idcontapagar
                    and cc.tipoobjetoorigem ='contapagar'
                    and cc.status !='QUITADO'";
        d::b()->query($tmpsqldel) or die("Erro ao retirar parcelas de comissão da Nota Fiscal : <br>".mysqli_error(d::b()));
    }

    static public function deletacontapagar($idobjeto,$tipoobjeto){
        //echo "qt:".$qtParcelas; 
        $tmpsqldel = "delete c.* from contapagar c where c.tipoobjeto = '".$tipoobjeto."' 
                        and not exists(select 1 from contapagaritem i where i.idcontapagar=c.idcontapagar and i.tipoobjetoorigem='contapagar') 
                        and c.status!='QUITADO'
                        and c.idobjeto = ".$idobjeto;
        d::b()->query($tmpsqldel) or die("Erro ao retirar parcelas do nf_issret: <br>".mysqli_error(d::b()));
        
        $tmpsqldel = "delete from contapagaritem where tipoobjetoorigem =  '".$tipoobjeto."' and idobjetoorigem = ".$idobjeto."  and status in ('ABERTO','PENDENTE')";
        d::b()->query($tmpsqldel) or die("Erro ao retirar parcelas da Nota Fiscal: <br>".mysqli_error(d::b()));
    }

    static public function deletarimpnfitem($idnf,$descr){
        //echo "qt:".$qtParcelas; 
        //$tmpsqldel = "delete from nfitem where idnf = ".$idnf." and  idtipoprodserv = ".$idtipoprodserv;
        $tmpsqldel = "delete from nfitem where idnf = ".$idnf." and  nfe ='C' and prodservdescr like('%".$descr."%')";
        d::b()->query($tmpsqldel) or die("Erro ao retirar imposto nfitem: <br>".mysqli_error(d::b()));
    }

    static public function getComissoesPendentes($idnotafiscal){
        /*
        * verifica se existe alguma parcela quitada. se existir, nao alterar nada.
        */
        $sqlverifquit = "select count(*) as quant  from contapagar c,contapagaritem cc
                    where c.tipoobjeto = 'nf' 
                    and c.idobjeto =".$idnotafiscal."
                    and cc.idobjetoorigem = c.idcontapagar
                    and cc.tipoobjetoorigem ='contapagar'
                    and cc.status !='QUITADO' ";
        //echo $sqlverifquit;
        $resverif = d::b()->query($sqlverifquit) or die($sqlverifquit."Erro ao consultar parcelas do representante para delecao: <br>".mysqli_error(d::b()));
        $rowverif = mysqli_fetch_array($resverif);
        return $rowverif;
    }


        
    static public function copiarnfitem($idnforigem,$idnf){
        $sqli="select * from nfitem where  idnf = ".$idnforigem;
        $resi = d::b()->query($sqli) or die("saveposchange__pedido: Falha ao recuperar nfitem:\n".mysqli_error(d::b())."\n".$sqli);
        $arrColunasi = mysqli_fetch_fields($resi);
        $colid="idnfitem";
        while($ri = mysqli_fetch_assoc($resi)){
            foreach ($arrColunasi as $coli) {
                $arrnfitem[$ri[$colid]][$coli->name]=$ri[$coli->name];
            }
        }
        //print_r( $arrnfitem);die;
        foreach ($arrnfitem as $arritem ) {
        $insnfItem = new Insert();
        $insnfItem->setTable("nfitem");
        foreach ($arritem as $key => $value) {		
            if($key=='idnf'){
                $value=$idnf;
            }	
            if(!empty($value) and $key!='idnfitem' and $key!='alteradoem'  and $key!='alteradopor' and $key!='criadoem' and $key!='criadopor'){
                $insnfItem->$key=$value;
            }	
            if($key=='idnfitem'){
                $idnfitemold=$value;
            }               
        }
        $idnfitem=$insnfItem->save();	
        
        //copiar tambem comissões
        $sqli2="select * from nfitemcomissao 
        where idnfitem=".$idnfitemold;
        $resi2 = d::b()->query($sqli2) or die("saveposchange__pedido: Falha ao recuperar comissao:\n".mysqli_error(d::b())."\n".$sqli2);
    
        while($ri2 = mysqli_fetch_assoc($resi2)){
            $insnfitemcom = new Insert();
            $insnfitemcom->setTable("nfitemcomissao");               
            $insnfitemcom->idnfitem=$idnfitem;
            $insnfitemcom->idpessoa=$ri2['idpessoa'];
            $insnfitemcom->idobjeto=$ri2['idobjeto'];
            $insnfitemcom->tipoobjeto=$ri2['tipoobjeto'];
            $insnfitemcom->pcomissao=$ri2['pcomissao'];
            $insnfitemcom->idempresa =self::$idempresa;
            $idnfitemcom=$insnfitemcom->save();
        }

    // echo($idnfitem);
        }
        reset($arrnfitem);
    
        // ************************************************************************************************ //

    }

    static public function deletaparcela($idnotafiscal){

        $arrParcItens=cnf::getParcelaItens($idnotafiscal);
        $qtParcelasitem = $arrParcItens['quant'];
    
        $arrParcelas=cnf::recuperaParcelas($idnotafiscal,'QUITADO','nf');//Contapagar Quitado
        $qtParcelas =$arrParcelas['quant'];

        $qtdprog=cnf::recuperaParcelasProg($idnotafiscal,'nf');
        
        if($qtParcelasitem==0 and $qtParcelas==0 and $qtdprog<1){
            cnf::deletaParcelasExistentes($idnotafiscal);
        }
    }

    static public function atualizafat($idnotafiscal,$idformapagamento=null, $status = null){

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
            
    
        $arrParcelas= cnf::recuperaParcelas($idnotafiscal,'QUITADO','nf');//Contapagar Quitado
        $qtParcelas =$arrParcelas['quant'];
        
        $arrParcelasFechado= cnf::recuperaParcelas($idnotafiscal,'FECHADO','nf');//Contapagar fechado
        $qtParcelasFechadas =$arrParcelasFechado['quant'];
/*impostos da erro se usar
        $arrParcelasPendente= cnf::recuperaParcelas($idnotafiscal,'PENDENTE','nf');//Contapagar fechado
        $qtParcelasPendente =$arrParcelasPendente['quant'];
  */      
        $arrParcelasIV= cnf::recuperaParcelasItensVinc($idnotafiscal,'nf');
        $qtParcelasIV =$arrParcelasIV['quant'];
                
        $arrlinhasbol=  cnf::verificaboleto($idnotafiscal);
        $qtdlinhasbol=$arrlinhasbol['quant'];
        //die($qtParcelas);
        $arrParcItens= cnf::getParcelaItens($idnotafiscal);
        $qtParcelasitem = $arrParcItens['quant'];
        
        $arrParcItensFechada= cnf::getParcelaItensfechada($idnotafiscal,$formapagamento['agrupnota']);
        $qtParcelasitemFechada = $arrParcItensFechada['quant'];   
        
        $qtdprog=cnf::recuperaParcelasProg($idnotafiscal,'nf');
        
        //echo($arrParcelas['quant']." - ".$arrlinhasbol['quant']." - ".$qtParcelasitem ." - ".$qtParcelasIV);die;
        if ($qtParcelas == 0  and  $qtdprog <1 and $qtdlinhasbol== 0 and $qtParcelasitem==0 and $qtParcelasIV==0 and $qtParcelasFechadas==0 and $qtParcelasitemFechada==0){
        //deleta as parcelas existentes.
            cnf::deletaParcelasExistentes($idnotafiscal);
            
            cnf::gerarContapagar($idnotafiscal, $status);

            cnf::agrupaCP(); 
        }
    }

    static public function agrupaCP(){
    
        $sql="select i.idcontapagaritem,i.idpessoa,i.idformapagamento,i.idagencia,i.idcontaitem,
                    month(LAST_DAY(i.datapagto) + INTERVAL 1 DAY) as mes,
                    year(LAST_DAY(i.datapagto) + INTERVAL 1 DAY) as ano,
                    CASE
                        WHEN f.agruppessoa='Y' and i.datapagto < CURRENT_DATE  THEN    (LAST_DAY(DATE_FORMAT(CURDATE() ,'%Y-%m-01')) + INTERVAL ifnull(f.diavenc,1) DAY)				
                        ELSE (LAST_DAY(i.datapagto) + INTERVAL ifnull(f.diavenc,1) DAY)
					END  as datavencimento,
                    DATE_ADD((LAST_DAY(i.datapagto) + INTERVAL ifnull(f.diavenc,1) DAY), INTERVAL 1 MONTH) as datavencimentoseq,
                    CASE
                        WHEN f.agruppessoa='Y' and i.datapagto < CURRENT_DATE  THEN   (LAST_DAY(DATE_FORMAT(CURDATE() ,'%Y-%m-01')) + INTERVAL 1 DAY) 		
                        ELSE  (LAST_DAY(i.datapagto) + INTERVAL 1 DAY) 
					END as inicio,
					CASE
                        WHEN f.agruppessoa='Y' and i.datapagto < CURRENT_DATE  THEN   LAST_DAY(LAST_DAY(DATE_FORMAT(CURDATE() ,'%Y-%m-01')) + INTERVAL 1 day)  		
                        ELSE LAST_DAY(LAST_DAY(i.datapagto) + INTERVAL 1 day)
					END  as fim,    
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
                    ifnull(fp.previsao,f.previsao) as previsao,
                    i.status,
                    i.obs,
                    i.criadopor,
                    f.formapagamento,
                    f.tipoespecifico,
                    p.cpfcnpj
            from contapagaritem i 
                    join formapagamento f on(i.idformapagamento=f.idformapagamento)
                    left join formapagamentopessoa fp on(fp.idformapagamento=f.idformapagamento and f.agruppessoa='Y' and fp.idpessoa = i.idpessoa)
                    JOIN pessoa p ON p.idpessoa = i.idpessoa 
                where i.status IN ('ABERTO','PENDENTE','PAGAR','CANCELADO','DEVOLVIDO')
                    and (idcontapagar is null or  idcontapagar='')
                    and (i.ajuste!='Y' or i.ajuste is null)
                    and i.idpessoa is not null and i.idpessoa !=''
                    and i.idformapagamento is not null and i.idformapagamento !=''
                    and i.idempresa = ".self::$idempresa."
                   -- and i.datapagto != '0000-00-00'
                    and i.idagencia is not null and i.idagencia !='' group by i.idcontapagaritem";
        $res= d::b()->query($sql) or die($sql."Erro ao buscar contapagaritem agrupado por pessoa para agrupamento: <br>".mysqli_error(d::b()));
        
        while($row=mysqli_fetch_assoc($res)){
            //se for comissao o tipo da conta agrupadora e REPRESENTACAO por comportar de forma diferente das demais
            /*
            $sqlfo="select * from confcontapagar where idformapagamento =".$row['idformapagamento']." and tipo='COMISSAO' and status='ATIVO'";
            $resfo= d::b()->query($sqlfo) or die($sql."Fala ao buscar se forma de pagamento e comissao: <br>".mysqli_error(d::b()));
            $qtdfo=mysqli_num_rows($resfo);
            if($qtdfo>0){$tipoespecifico='REPRESENTACAO';}else{$tipoespecifico='AGRUPAMENTO';}
            */

            $tipoespecifico=$row['tipoespecifico'];

            
            if($row['agrupnota']=='Y'){
                $qtd1=0;
            }elseif($row['agruppessoa']=='Y'){
                //alterado a buscar para pegar a primeira em aberto apartir da data de vencimento datapagto 11-09-2020 hermesp
                $sql1="select c.* from contapagar c 
                        join pessoa p on(p.idpessoa=c.idpessoa and SUBSTRING(p.cpfcnpj , 1,8) = SUBSTRING('".$row['cpfcnpj']."', 1,8) )
                        where -- c.idpessoa = ".$row['idpessoa']." and
                         c.idformapagamento= ".$row['idformapagamento']."
                        and c.idagencia = ".$row['idagencia']."
                        and c.idempresa = ".self::$idempresa."
                        and c.status='ABERTO'
                        and c.tipo='".$row['tipo']."'
                        and c.tipoespecifico='".$tipoespecifico."'
                        and c.datareceb between '".$row['inicio']."' and '".$row['fim']."'  
                        order by c.datareceb asc limit 1";  
                $res1= d::b()->query($sql1) or die($sql1."Erro ao buscar contapagar para agrupar por pessoa e formapagto: <br>".mysqli_error(d::b()));
                $qtd1=mysqli_num_rows($res1);
            }elseif($row['formapagamento']=='C.CREDITO' and $row['agrupfpagamento']=='Y'){
                
                  //alterado a buscar para pegar a primeira em aberto apartir da data de vencimento datapagto 11-09-2020 hermesp
                    $sql1="select * from contapagar c 
                        where c.idformapagamento= ".$row['idformapagamento']."
                        and c.idagencia = ".$row['idagencia']."                   
                        and c.idempresa = ".self::$idempresa."
                        and c.status='ABERTO'
                        and c.tipo='".$row['tipo']."'
                        and c.tipoespecifico='".$tipoespecifico."'
                        and c.datareceb >= '".$row['inicio']."' 
                        and c.datareceb <=  '".$row['fim']."' 
                        order by c.datareceb asc limit 1";  
                    $res1= d::b()->query($sql1) or die($sql1."Erro ao buscar contapagar para agrupar por pessoa e formapagto: <br>".mysqli_error(d::b()));
                    $qtd1=mysqli_num_rows($res1);

            }else{
                //alterado a buscar para pegar a primeira em aberto apartir da data de vencimento datapagto 11-09-2020 hermesp
                $sql1="select * from contapagar c 
                        where c.idformapagamento= ".$row['idformapagamento']."
                        and c.idagencia = ".$row['idagencia']."                   
                        and c.idempresa = ".self::$idempresa."
                        and c.status='ABERTO'
                        and c.tipo='".$row['tipo']."'
                        and c.tipoespecifico='".$tipoespecifico."'
                        and c.datareceb >= '".$row['datapagto']."' 
                       -- and '".$row['fim']."' 
                        order by c.datareceb asc limit 1";  
                $res1= d::b()->query($sql1) or die($sql1."Erro ao buscar contapagar para agrupar por pessoa e formapagto: <br>".mysqli_error(d::b()));
                $qtd1=mysqli_num_rows($res1);
      
            }
                
                if($qtd1>0){
                    $row1=mysqli_fetch_assoc($res1);
                    $squ="update contapagaritem set idcontapagar=".$row1['idcontapagar']." where idcontapagaritem=".$row['idcontapagaritem'];
                    $reu= d::b()->query($squ) or die($squ."Erro vincular contapagaritem na contapagar: <br>".mysqli_error(d::b()));

                    d::b()->query("COMMIT") or die("Erro");

                   if(($row["formapagamento"]!='C.CREDITO') AND ($row["formapagamento"]!='BOLETO' or  $row['agruppessoa']!='Y')){ //Não atualiza valores de cartao de credito                  

                        $sqlc="select sum(i.valor) AS valor 
                        from 
                            contapagaritem i 
                                where i.idcontapagar = ".$row1['idcontapagar']."
                                and i.status !='INATIVO'";
                        $rescc=d::b()->query($sqlc) or die("Falha ao buscaro o valor do contapagar sql=".$sqlc);
                        $rowcc=mysqli_fetch_assoc($rescc);

                        $sqlu2="update contapagar i set i.valor='".$rowcc['valor']."',alteradopor='sislaudo',alteradoem=now()
                                where i.idcontapagar = ".$row1['idcontapagar'];
                        d::b()->query($sqlu2) or die("Falha ao atualizar o valor do contapagar sql=".$sqlu);
                   }// if($row["formapagamento"]!='C.CREDITO'){

                }else{
                    /* 
                    * Fatura cartão: ao lançar um item de conta, 
                    * verificar se ha  uma fatura "pendente e/ou quitado"
                    * no mes do lançamento. Caso haja, jogar para o proximo mes.                     * 
                    */
                    if($row['agrupnota']=='Y'){
                        
                        $datavencimento=$row['datapagto'];
                        
                    }/*elseif($row['agruppessoa']=='N'){                 
                       
                        $sql1="select * from contapagar c 
                           where c.idformapagamento= ".$row['idformapagamento']."
                           and c.idagencia = ".$row['idagencia']."
                            and c.idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]."
                           and c.status in ('PENDENTE','QUITADO')
                           and c.tipoespecifico='AGRUPAMENTO'
                           and c.datareceb between '".$row['inicio']."' and '".$row['fim']."' order by c.datareceb asc limit 1";  
                        $res1= d::b()->query($sql1) or die($sql1."Erro ao buscar contapagar PENDENTE ou QUITADO: <br>".mysqli_error(d::b()));
                        $qtd1=mysqli_num_rows($res1);
                        if($qtd1>0){
                            $datavencimento= $row['datavencimentoseq'];//data do mês sequinte
                        }else{
                            $datavencimento= $row['datavencimento'];
                        }
                    }*/else{
                        $datavencimento=$row['datavencimento'];
                    }
                    
                    
                    $inscontapagar = new Insert();
                    $inscontapagar->setTable("contapagar");
                    $inscontapagar->idagencia=$row['idagencia'];
                    $inscontapagar->criadopor=$row['criadopor'];                   
                    $inscontapagar->alteradopor=$row['criadopor'];
                    $inscontapagar->idempresa=self::$idempresa;             

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

                        //LTM - 31-03-2021: Retorna o Idfluxo ContaPagar
                        $idfluxostatus = FluxoController::getIdFluxoStatus('contapagar', $row['status']);
                        $inscontapagar->idfluxostatus = $idfluxostatus;

                        if(!empty($row['idcontaitem'])){
                            $inscontapagar->idcontaitem=$row['idcontaitem'];
                        }
                    }else{                        
                        $inscontapagar->idcontaitem=46;
                        $inscontapagar->status='ABERTO';

                        //LTM - 31-03-2021: Retorna o Idfluxo ContaPagar
                        $idfluxostatus = FluxoController::getIdFluxoStatus('contapagar', 'ABERTO');
                        $inscontapagar->idfluxostatus = $idfluxostatus;

                        $inscontapagar->parcela=1;                                
                        $inscontapagar->parcelas=1;
                    }
                    
                        
                    if($tipoespecifico=='IMPOSTO'){
                        $inscontapagar->idpessoa=$row['idpessoa'];
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
                    
                    $idcontapagar=$inscontapagar->save();  

                    // GVT - 23/07/2021 - @471938 lançamentos com diferença de 28 dias gera assinatura p/ o Fábio
                    //
                    // Não há a necessidade de verificar se já existe assinatura pendente, pois aqui sempre são criadas novas contas a pagar
                    $d1 = strtotime(date("Y-m-d"));
                    $d2 = strtotime($datavencimento);
                    $diff = ($d2 - $d1)/60/60/24;
                    if($diff < 28 AND $row['agrupnota'] == 'Y' AND $row['tipo'] == 'D'){
                        $insCarrimbo = "INSERT INTO `laudo`.`carrimbo`
                        (`idempresa`,
                        `idpessoa`,
                        `idobjeto`,
                        `tipoobjeto`,
                        `status`,
                        `criadopor`,
                        `criadoem`,
                        `alteradopor`,
                        `alteradoem`)
                        VALUES
                        (".self::$idempresa.",
                        798,
                        ".$idcontapagar.",
                        'contapagar',
                        'PENDENTE',
                        'sislaudo',
                        now(),
                        'sislaudo',
                        now());
                        ";
                        d::b()->query($insCarrimbo) or die("Erro ao inserir assinatura contapagar. sql: ".$insCarrimbo);
                    }
                                  
                    $sqlu="update contapagaritem set idcontapagar =".$idcontapagar."
                                            where idcontapagaritem =".$row['idcontapagaritem']."  and idempresa = ".self::$idempresa."";
                    d::b()->query($sqlu) or die("Falha ao atualizar contapagaritem com novo contapagar sql=".$sqlu);
                    d::b()->query("COMMIT") or die("Erro");
                    if(($row["formapagamento"]!='C.CREDITO') AND ( ($row["formapagamento"]!='BOLETO' or  $row['agruppessoa']!='Y') or ($row['agrupnota']=='Y') ) ){ //Não atualiza valores de cartao de credito   
                      
                        $sqlc="select sum(i.valor) AS valor 
                                from 
                                    contapagaritem i 
                                        where i.idcontapagar = ".$idcontapagar."
                                        and i.status !='INATIVO'";
                        $rescc=d::b()->query($sqlc) or die("Falha ao buscaro o valor do contapagar sql=".$sqlc);
                        $rowcc=mysqli_fetch_assoc($rescc);

                        $sqlu2="update contapagar i set i.valor='".$rowcc['valor']."'
                                where i.idcontapagar = ".$idcontapagar;
                        d::b()->query($sqlu2) or die("Falha ao atualizar o valor do contapagar sql=".$sqlu);
                    }// if($row["formapagamento"]!='C.CREDITO'){

                    //LTM - 31-03-2021: Retorna o Idfluxo Hist
                    if(!empty($idfluxostatus))
                    {
                        FluxoController::inserirFluxoStatusHist('contapagar', $idcontapagar, $idfluxostatus, 'PENDENTE');
                    }
                    
                }
            
         
        }// while($row=mysqli_fetch_assoc($res)){ 
            cnf::ajustaComissoes();
            cnf::gerarRateioTodos();
    }
    
    // ajusta valor das comissões conforme as parcelas das mesmas
    static public function ajustaComissoes(){

        $sqln=" select  concat('update contapagar set valor=',u.svalor,' where idcontapagar=',u.idcontapagar,';') as sqlup
                from (
                
                select c.idcontapagar,c.valor,sum(i.valor) as svalor
                from contapagar c left join contapagaritem i on(i.idcontapagar =c.idcontapagar and i.status!='INATIVO'
                and exists (select 1 from contapagar p where p.idcontapagar = i.idobjetoorigem and i.tipoobjetoorigem='contapagar' and  p.status!='INATIVO')
                )
                where c.tipoespecifico = 'REPRESENTACAO'  
                and c.status ='ABERTO' group by  c.idcontapagar
                ) as u where u.svalor!=valor";
        $resn=d::b()->query($sqln) or die("Erro ao buscar comissões a ajustar <br>".mysqli_error(d::b()));
        $qtdCom=mysqli_num_rows($resn);
        if($qtdCom>0){
            while($row=mysqli_fetch_assoc($resn)){
                $r=d::b()->query($row['sqlup']) or die("Erro ao atualizar comissão: <br>".$row['sqlup']);
            }
        }


    }

    static public function geraRateio($idnf){
        $_idempresa=traduzid("nf","idnf","idempresa",$idnf);

        //gera rateio quando tem pessoa no nfitem
        $sqln="select * from nfitem i where i.nfe='Y'  
                            -- and i.vlritem >0 
                            and i.idpessoa is not null and i.idnf =".$idnf."
                            and not exists (select 1 from rateioitem r where r.idobjeto = i.idnfitem and r.tipoobjeto  = 'nfitem')";
        $resn=d::b()->query($sqln) or die("Erro ao buscar  os itens da nf: <br>".mysqli_error(d::b()));
        $qtdpes=mysqli_num_rows($resn);

        if($qtdpes>0){//so vai gerar para itens com pessoas vinculadas

            /*
            $sql="select * from rateio where idobjeto = ". $idnf." and tipoobjeto='nf'";
            $res=d::b()->query($sql) or die("Erro ao buscar rateio : <br>".mysqli_error(d::b()));
            $qtd=mysqli_num_rows($res);
            if($qtd<1){
                $insrateio = new Insert();
                $insrateio->setTable("rateio");
                $insrateio->idobjeto=$idnf;
                $insrateio->tipoobjeto='nf';
                $_idrateio=$insrateio->save();
            }else{
                $row=mysqli_fetch_assoc($res);
                $_idrateio=$row['idrateio'];
            }
                */
/*
            $sqli="select d.idrateioitemdest 
            from rateioitem r 
                join rateioitemdest d on(d.idrateioitem=r.idrateioitem)
                join  nfitem i on( i.nfe='Y'  
                                    -- and i.vlritem >0 
                                    and i.idpessoa is not null and r.idobjeto = i.idnfitem and r.tipoobjeto ='nfitem') 
            where r.idrateio = ".$_idrateio;
            $resi=d::b()->query($sqli) or die("Erro ao buscar rateio do item: <br>".mysqli_error(d::b()));
            $i=0;
            while($rowi=mysqli_fetch_assoc($resi)){
                $i=$i+1;
                $sqld="delete from rateioitemdest where idrateioitemdest=".$rowi['idrateioitemdest'];
                d::b()->query($sqld) or die("Erro ao excluir rateioitemdest do item: <br>".mysqli_error(d::b()));
            }

            $sqli="select d.idrateioitemdestori 
            from rateioitem r 
                join rateioitemdestori d on(d.idrateioitem=r.idrateioitem)
                join  nfitem i on( i.nfe='Y'  
                                -- and i.vlritem >0 
                                and i.idpessoa is not null and r.idobjeto = i.idnfitem and r.tipoobjeto ='nfitem') 
            where r.idrateio = ".$_idrateio;
            $resi=d::b()->query($sqli) or die("Erro ao buscar rateio do item: <br>".mysqli_error(d::b()));
            $i=0;
            while($rowi=mysqli_fetch_assoc($resi)){
                $i=$i+1;
                $sqld="delete from rateioitemdestori where idrateioitemdestori=".$rowi['idrateioitemdestori'];
                d::b()->query($sqld) or die("Erro ao excluir rateioitemdest do item: <br>".mysqli_error(d::b()));
            }

*/

/*
            $sqli="select r.idrateioitem 
                    from rateioitem r 
                        join  nfitem i on( i.nfe='Y'  
                        -- and i.vlritem >0 
                        and i.idpessoa is not null and r.idobjeto = i.idnfitem and r.tipoobjeto ='nfitem') 
                    where r.idrateio = ".$_idrateio;
            $resi=d::b()->query($sqli) or die("Erro ao buscar rateio do item: <br>".mysqli_error(d::b()));
            
            while($rowi=mysqli_fetch_assoc($resi)){
                $i=$i+1;
                $sqld="delete from rateioitem where idrateioitem=".$rowi['idrateioitem'];
                d::b()->query($sqld) or die("Erro ao excluir rateioitem do item: <br>".mysqli_error(d::b()));
            }
*/
            $sqln="select   
                        CASE
                            WHEN n.dtemissao is not null THEN n.dtemissao 
                            ELSE now()
                        END as nfemissao,
                        n.idempresa as nfidempresa,
                        i.* 
            from nfitem i join nf n on(n.idnf=i.idnf)
             where i.nfe='Y' and i.idpessoa is not null                       
                        and i.idnf =".$idnf."   and not exists (select 1 from rateioitem r where r.idobjeto = i.idnfitem and r.tipoobjeto  = 'nfitem')";
            $resn=d::b()->query($sqln) or die("Erro ao buscar  os itens da nf: <br>".mysqli_error(d::b()));
            
            while($rown=mysqli_fetch_assoc($resn)){

                $sqli="select r.idrateioitem 
                from rateioitem r                         
                where r.tipoobjeto='nfitem'
                and r.idobjeto = ".$rown['idnfitem'];
                $resi=d::b()->query($sqli) or die("Erro ao buscar rateio do item: <br>".mysqli_error(d::b()));                        
                $rowi=mysqli_fetch_assoc($resi);
                if(!empty($rowi['idrateioitem'])){
                    $_idrateioitem=$rowi['idrateioitem'];
                }else{
                    $_idrateio =retidrateio($rown['nfemissao'],$rown['nfidempresa']);
                    $insrateioitem = new Insert();
                    $insrateioitem->setTable("rateioitem");
                    $insrateioitem->idrateio=$_idrateio;
                    $insrateioitem->idobjeto=$rown['idnfitem'];
                    $insrateioitem->tipoobjeto='nfitem';
                    $_idrateioitem=$insrateioitem->save();
                }

                $qtdr=0;
             
                if($rown['tipoobjetoitem']=='notafiscal' and !empty($rown['idobjetoitem'])){
                 

                    $sql="select p.idunidade as id ,'unidade' as obj 
                                from notafiscal n 
                                   join plantelobjeto po on(po.idobjeto=n.idpessoa and po.tipoobjeto = 'pessoa')
                                   join plantel p on(p.idplantel=po.idplantel)
                                   where n.idnotafiscal=".$rown['idobjetoitem']." limit 1";

                    $res=d::b()->query($sql) or die("Erro ao buscar departamento dos funcionarios dos itens: <br>".mysqli_error(d::b()));
                    $qtdr=mysqli_num_rows($res);
                    
                    if($qtdr<1){ 
                        $sql="select idunidade as id ,'unidade' as obj
                                from unidade 
                                    where idtipounidade=28 
                                    and idempresa=".$_idempresa."
                                    and status ='ATIVO' limit 1" ;
                        $res=d::b()->query($sql) or die("Erro ao buscar departamento dos funcionarios dos itens: <br>".mysqli_error(d::b()));
                        $qtdr=mysqli_num_rows($res);
                                             
                    }
                   
                }elseif($rown['tipoobjetoitem']=='nf' and !empty($rown['idobjetoitem'])){
                    //rateio de imposto buscar pela nota da venda

                    $_tiponf=traduzid("nf","idnf","tiponf",$rown['idobjetoitem']);

                    if($_tiponf=='V'){
                        $sql="select p.idunidade as id ,'unidade' as obj 
                                from nf n 
                                join plantelobjeto po on(po.idobjeto=n.idpessoa and po.tipoobjeto = 'pessoa')
                                join plantel p on(p.idplantel=po.idplantel)
                                join unidade u on(u.idunidade=p.idunidade and u.status ='ATIVO')   
                                where n.idnf=".$rown['idobjetoitem']." limit 1";
                                    $res=d::b()->query($sql) or die("Erro ao buscar departamento dos funcionarios dos itens: <br>".mysqli_error(d::b()));
                                    $qtdr=mysqli_num_rows($res);
                    }else{

                        $sql="SELECT 
                                    rd.idobjeto as id ,'unidade' as obj ,                     
                                    ROUND((sum(((i.total + IFNULL(i.valipi, 0) + IFNULL(n.frete, 0)) * (IFNULL(rd.valor, 100) / 100)))) / ( n.total) *100, 2) as rateio
                                FROM nfitem i JOIN nf n ON (n.idnf = i.idnf)
                                        JOIN rateioitem ri ON (ri.idobjeto = i.idnfitem AND ri.tipoobjeto = 'nfitem')
                                        JOIN rateioitemdest rd ON (rd.idrateioitem = ri.idrateioitem)
                                        JOIN contaitem c ON (c.idcontaitem = i.idcontaitem AND c.somarelatorio = 'Y')
                                    WHERE 
                                    i.qtd > 0
                                        AND i.nfe = 'Y'
                                    and i.idnf = ".$rown['idobjetoitem']." GROUP BY rd.idobjeto";
                            $res=d::b()->query($sql) or die("Erro ao buscar departamento dos funcionarios dos itens: <br>".mysqli_error(d::b()));
                            $qtdr=mysqli_num_rows($res);
                        
                    }
                   
                    if($qtdr<1){ 
                        $sql="select idunidade as id ,'unidade' as obj
                                from unidade 
                                    where idtipounidade=28 
                                    and idempresa=".$_idempresa."
                                    and status ='ATIVO' limit 1" ;
                        $res=d::b()->query($sql) or die("Erro ao buscar departamento dos funcionarios dos itens: <br>".mysqli_error(d::b()));
                        $qtdr=mysqli_num_rows($res);
                                             
                    }
                }/*elseif($rown['tipoobjetoitem']=='nf' and !empty($rown['idobjetoitem'])){


                    $sql="select pl.idunidade as id ,'unidade' as obj
                                from nfitem n join nf o on(o.idnf=n.idobjetoitem)
                                join plantelobjeto p on(p.idobjeto=o.idpessoa  and p.tipoobjeto='pessoa')
                                    join plantel pl on(pl.idplantel = p.idplantel)  
                                    join unidade u on(u.idunidade=pl.idunidade and u.status ='ATIVO')                               
                                where n.idnfitem =".$rown['idnfitem']."
                                    and n.tipoobjetoitem='nf' limit 1";
                    $res=d::b()->query($sql) or die("Erro ao buscar departamento dos funcionarios dos itens: <br>".mysqli_error(d::b()));
                    $qtdr=mysqli_num_rows($res);
                    if($qtdr<1){ 
                        $sql="select idunidade as id ,'unidade' as obj
                                from unidade 
                                    where idtipounidade=28 
                                    and idempresa=".$_idempresa."
                                    and status ='ATIVO' limit 1" ;
                        $res=d::b()->query($sql) or die("Erro ao buscar departamento dos funcionarios dos itens: <br>".mysqli_error(d::b()));
                        $qtdr=mysqli_num_rows($res);
                                             
                    }
                }*/else{
                    $sql="select p.idunidade as id ,'unidade' as obj
                    from nfitem i 
                        join vw8PessoaUnidadeRateio p on(i.idpessoa=p.idpessoa)  
                        join unidade u on(u.idunidade=p.idunidade and u.status ='ATIVO')                
                    where i.idnfitem=".$rown['idnfitem']." and p.idunidade is not null group by p.idunidade ";
                    $res=d::b()->query($sql) or die("Erro ao buscar departamento dos funcionarios dos itens: <br>".mysqli_error(d::b()));
                    $qtdr=mysqli_num_rows($res);
                    if($qtdr<1){ 
                        
                        
                        $sql="select pl.idunidade as id ,'unidade' as obj
                            from   plantelobjeto p
                                join plantel pl on(pl.idplantel = p.idplantel) 
                                join unidade u on(u.idunidade=pl.idunidade and u.status ='ATIVO')
                                where pl.idunidade is not null and (p.idobjeto =".$rown['idpessoa']."  and p.tipoobjeto='pessoa') limit 1";
                            $res=d::b()->query($sql) or die("Erro ao buscar unidade dos funcionarios dos itens: <br>".mysqli_error(d::b()));
                            $qtdr=mysqli_num_rows($res);
                            if($qtdr<1){ 
                                
                                $sql="select idunidade as id,'unidade' as obj from unidade where idtipounidade =28 and status ='ATIVO' AND idempresa = ".$_idempresa."";
                                $res=d::b()->query($sql);
                                $qtdr=mysqli_num_rows($res);
                                
                               // die("É necessário configurar departamento  dos funcionarios  para empresa");
                            
                            }
                    }

                }

                if($qtdr<1){ 
                    $sql="select idunidade as id,'unidade' as obj from unidade where idtipounidade =28 and status ='ATIVO' AND idempresa = ".$_idempresa."";
                    $res=d::b()->query($sql);
                    $qtdr=mysqli_num_rows($res);
                    if($qtdr<1){
                        die("Não encontrada unidade para rateio");
                    }
                }
            
                while($row=mysqli_fetch_assoc($res)){  
                     if(empty($row['rateio'])){
                        $rateio=  100 / $qtdr;  
                     }else{
                        $rateio=$row['rateio'];
                     }     
                    
                    $i=$i+1; 

                    if($rateio>100){
                        $rateio=100;
                    }      
                
                    $insrateioitemd = new Insert();
                    $insrateioitemd->setTable("rateioitemdest");
                    $insrateioitemd->idrateioitem=$_idrateioitem;                   
                    $insrateioitemd->idobjeto=$row['id'];
                    $insrateioitemd->tipoobjeto=$row['obj'];                   
                    $insrateioitemd->valor= $rateio;
                    $insrateioitemd->criadopor='sislaudo';
                    $insrateioitemd->criadoem= sysdate();
                    $insrateioitemd->alteradopor='sislaudo';
                    $insrateioitemd->alteradoem= sysdate();
                    $_idrateioitemd=$insrateioitemd->save();

                    $insrateioitemdo = new Insert();
                    $insrateioitemdo->setTable("rateioitemdestori");
                    $insrateioitemdo->idrateioitem=$_idrateioitem;                   
                    $insrateioitemdo->idobjeto=$row['id'];
                    $insrateioitemdo->tipoobjeto=$row['obj'];                   
                    $insrateioitemdo->valor= $rateio;
                    $_idrateioitemdo=$insrateioitemdo->save();
                
                    
                }
                
            }
           
        }

    }

    static public function geraRateioDanfe($idnf){




            
/*
            $sqli="select d.idrateioitemdest 
            from rateioitem r 
                join rateioitemdest d on(d.idrateioitem=r.idrateioitem)
             where r.idrateio = ".$_idrateio;
            $resi=d::b()->query($sqli) or die("Erro ao buscar rateio do item: <br>".mysqli_error(d::b()));
          
            while($rowi=mysqli_fetch_assoc($resi)){
                $i=$i+1;
                $sqld="delete from rateioitemdest where idrateioitemdest=".$rowi['idrateioitemdest'];
                d::b()->query($sqld) or die("Erro ao excluir rateioitemdest do item: <br>".mysqli_error(d::b()));
            }



            $sqli="select r.idrateioitem 
                    from rateioitem r                         
                    where r.idrateio = ".$_idrateio;
            $resi=d::b()->query($sqli) or die("Erro ao buscar rateio do item: <br>".mysqli_error(d::b()));
            
            while($rowi=mysqli_fetch_assoc($resi)){
                $i=$i+1;
                $sqld="delete from rateioitem where idrateioitem=".$rowi['idrateioitem'];
                d::b()->query($sqld) or die("Erro ao excluir rateioitem do item: <br>".mysqli_error(d::b()));
            }
*/
            $sql="select i.idprodserv,p.tempoconsrateio,p.idunidadeest,n.idempresa,u.idunidade,i.qtd as qtdsol,i.idnfitem,
                CASE
                    WHEN n.dtemissao is not null THEN n.dtemissao 
                    ELSE now()
                END as emissao
                from nfitem i join contaitem c on(c.idcontaitem= i.idcontaitem and c.somarelatorio = 'Y')
                join prodserv p on(p.idprodserv=i.idprodserv and p.tipo='PRODUTO')
                join nf n on(n.idnf=i.idnf)
                left join  unidade u on( u.idtipounidade=3 and u.status='ATIVO' and u.idempresa=n.idempresa)
                where i.nfe='Y' and i.qtd> 0 
                -- and i.vlritem >0 
                and  i.idnf=".$idnf."
                and not exists (select 1 from rateioitem r join rateioitemdest d on(d.idrateioitem = r.idrateioitem)
                                    where r.idobjeto = i.idnfitem and r.tipoobjeto  = 'nfitem')
                 group by i.idnfitem";

            $res=d::b()->query($sql) or die("Falha ao buscar informações do produto para o geraRateioDanfe:  " . $sql);

            $qtdres=mysqli_num_rows($res);

            /*
            if($qtdres>0){
                $sqlr="select * from rateio where idobjeto = ". $idnf." and tipoobjeto='nf'";
                $resr=d::b()->query($sqlr) or die("Erro ao buscar rateio : <br>".mysqli_error(d::b()));
                $qtdra=mysqli_num_rows($resr);
                if($qtdra<1){
                    $insrateio = new Insert();
                    $insrateio->setTable("rateio");
                    $insrateio->idobjeto=$idnf;
                    $insrateio->tipoobjeto='nf';
                    $_idrateio=$insrateio->save();
                }else{
                    $rowr=mysqli_fetch_assoc($resr);
                    $_idrateio=$rowr['idrateio'];
                }
                $i=0;
            }
             */

            
            while($row=mysqli_fetch_assoc($res)){

                $_idrateioitem=0;

                $idnfitem=$row['idnfitem'];             


                $idprodserv=$row['idprodserv'];
                $idunidadeest=$row['idunidadeest'];

                $qtdcom=0;
                $qtdsolcom=0;
               
                // inventar algo para quando não tiver compra ainda
                if(empty($idunidadeest)){$idunidadeest=$row['idunidade']; }
/*
                $sqlsol=cnf::buscaSqlsolcom($idnfitem);
                // $sqlsol="select 	1.00 as qtdcom,2.0000 as qtd,2.00 as qtdsol,13 as idunidade,13088 as idprodserv,50 as percentual";
                $ressol =d::b()->query($sqlsol) or die("Falha ao buscar informações do produto para o geraRateioDanfe:  " . $sqlsol);

                $qtdsolcom =mysqli_num_rows($ressol);
             
                if($qtdsolcom>0){// tem solicitação de compra
                    $sqli="select r.idrateioitem 
                    from rateioitem r                         
                    where r.tipoobjeto='nfitem'
                    and r.idobjeto = ".$row['idnfitem'];
                    $resi=d::b()->query($sqli) or die("Erro ao buscar rateio do item: <br>".mysqli_error(d::b()));                        
                    $rowi=mysqli_fetch_assoc($resi);
                    if(!empty($rowi['idrateioitem'])){
                        $_idrateioitem=$rowi['idrateioitem'];
                    }else{
                        $insrateioitem = new Insert();
                        $insrateioitem->setTable("rateioitem");
                        $insrateioitem->idrateio=$_idrateio;
                        $insrateioitem->idobjeto=$row['idnfitem'];
                        $insrateioitem->tipoobjeto='nfitem';
                        $_idrateioitem=$insrateioitem->save();
                    }

                   
                    $percentualacumulado=0;
                    while($rowsol=mysqli_fetch_assoc($ressol)){
                        $qtdcom=$qtdcom+$rowsol['qtdcom'];  
                        
                        if($rowsol['percentual']>100){
                            $rowsol['percentual']=100;
                        }

                        if(($percentualacumulado + $rowsol['percentual']) > 100){
                            $rowsol['percentual']=100 - $percentualacumulado;
                        }


                        $percentualacumulado = $percentualacumulado + $rowsol['percentual'];   

                        if($percentualacumulado <= 100){
                            $insrateioitemd = new Insert();
                            $insrateioitemd->setTable("rateioitemdest");
                            $insrateioitemd->idrateioitem=$_idrateioitem;                          
                            $insrateioitemd->idobjeto=$rowsol['idunidade'];
                            $insrateioitemd->tipoobjeto='unidade';                          
                            $insrateioitemd->valor=$rowsol['percentual'];
                            $insrateioitemd->criadopor='sislaudo';
                            $insrateioitemd->criadoem= sysdate();
                            $insrateioitemd->alteradopor='sislaudo';
                            $insrateioitemd->alteradoem= sysdate();
                            $_idrateioitemd=$insrateioitemd->save();
    
                            
                            $insrateioitemdo = new Insert();
                            $insrateioitemdo->setTable("rateioitemdestori");
                            $insrateioitemdo->idrateioitem=$_idrateioitem;                          
                            $insrateioitemdo->idobjeto=$rowsol['idunidade'];
                            $insrateioitemdo->tipoobjeto='unidade';                          
                            $insrateioitemdo->valor=$rowsol['percentual'];
                            $_idrateioitemdo=$insrateioitemdo->save();
                        }
                       

                    }

                }
*/
               // echo($sqlsol);

                if($qtdcom<$row['qtdsol']){
                    // ratear para unidade do estoque
                   

                    if($idunidadeest>0){
                            
                        if($_idrateioitem==0){
                            $sqli="select r.idrateioitem 
                            from rateioitem r                         
                            where r.tipoobjeto='nfitem'
                            and r.idobjeto = ".$row['idnfitem'];
                            $resi=d::b()->query($sqli) or die("Erro ao buscar rateio do item: <br>".mysqli_error(d::b()));                        
                            $rowi=mysqli_fetch_assoc($resi);
                            if(!empty($rowi['idrateioitem'])){
                                $_idrateioitem=$rowi['idrateioitem'];
                            }else{
                                $_idrateio =retidrateio($row['emissao'],$row['idempresa']);
                                $insrateioitem = new Insert();
                                $insrateioitem->setTable("rateioitem");
                                $insrateioitem->idrateio=$_idrateio;
                                $insrateioitem->idobjeto=$row['idnfitem'];
                                $insrateioitem->tipoobjeto='nfitem';
                                $_idrateioitem=$insrateioitem->save();
                            }
                           
                        }                    
                        
                        if($qtdsolcom>0 and $qtdcom>0){
                            $perc=100-(($qtdcom/$row['qtdsol'])*100);                               
                        }else{
                            $perc=100;
                        }
                        if($perc>100){
                            $perc=100;
                        }                        

                        $insrateioitemd = new Insert();
                        $insrateioitemd->setTable("rateioitemdest");
                        $insrateioitemd->idrateioitem=$_idrateioitem;                          
                        $insrateioitemd->idobjeto=$idunidadeest;
                        $insrateioitemd->tipoobjeto='unidade';                          
                        $insrateioitemd->valor=$perc;
                        $insrateioitemd->situacao='ALMOXARIFADO';
                        $insrateioitemd->criadopor='sislaudo';
                        $insrateioitemd->criadoem= sysdate();
                        $insrateioitemd->alteradopor='sislaudo';
                        $insrateioitemd->alteradoem= sysdate();
                        $_idrateioitemd=$insrateioitemd->save();
                            
                        $insrateioitemdo = new Insert();
                        $insrateioitemdo->setTable("rateioitemdestori");
                        $insrateioitemdo->idrateioitem=$_idrateioitem;                          
                        $insrateioitemdo->idobjeto=$idunidadeest;
                        $insrateioitemdo->tipoobjeto='unidade';                          
                        $insrateioitemdo->valor=$perc;
                        $_idrateioitemdo=$insrateioitemdo->save();    

                    }


/* codigo desativado rateia conforme o consumo 16/06/2023 hermesp
                        
                        $consumodiasloterateio=$row['tempoconsrateio'];

                        $sqlmeio= cnf::buscarSqlRateio($idprodserv,$idunidadeest,$consumodiasloterateio);

                       // echo($sqlmeio);
            
                        $_reslotemeio = d::b()->query($sqlmeio) or die("Erro ao buscar lotes: " . $sqlmeio);
                        $numrowmeio = mysqli_num_rows($_reslotemeio);

                        if ($numrowmeio > 0) { 
                            
                        

                            //$totalqtdd = 0;
                            $totalqtddrateio=0;
                            $consumounidade=array();
                            while ($rowmi = mysqli_fetch_assoc($_reslotemeio)) {

                                if($rowmi['qtdd']>0){
                                    $totalqtddrateio=$totalqtddrateio+$rowmi['qtdd'];
                                    $consumounidade[$rowmi['idunidade']]=$consumounidade[$rowmi['idunidade']]+$rowmi['qtdd'];
                                    
                                }else{
                                    $totalqtddrateio=$totalqtddrateio-$rowmi['qtdc'];
                                    $consumounidade[$rowmi['idunidade']]=$consumounidade[$rowmi['idunidade']]-$rowmi['qtdc'];
                                    
                                }
                                        
                            }//while ($rowmi = mysqli_fetch_assoc($_reslotemeio)) {
                            
                        }else{//if ($numrowmeio > 0) {
                            $sqlmeio="select u.idunidade 
                                            from unidade u join nf n on(n.idempresa=u.idempresa and n.idnf = ". $idnf.")
                                        where u.idtipounidade =28 and u.status ='ATIVO' ";
                            $_reslotemeio = d::b()->query($sqlmeio) or die("Erro ao buscar lotes: " . $sqlmeio);
                            $numrowmeio = mysqli_num_rows($_reslotemeio);
    
                            if ($numrowmeio > 0) { 
                                
                                    $qtddd=$row['qtdsol']-$qtdcom;
                                    $totalqtddrateio=0;
                                    $consumounidade=array();
                                    while ($rowmi = mysqli_fetch_assoc($_reslotemeio)) {
                                     
                                            $totalqtddrateio=$totalqtddrateio+$qtddd;
                                            $consumounidade[$rowmi['idunidade']]=$consumounidade[$rowmi['idunidade']]+$qtddd;                                            
                                        
                                                
                                    }//while ($rowmi = mysqli_fetch_assoc($_reslotemeio)) {
                                
                            }
                        }

                        
                        //die;

                        if(count($consumounidade)>0){
                            
                            if($_idrateioitem==0){
                                $sqli="select r.idrateioitem 
                                from rateioitem r                         
                                where r.tipoobjeto='nfitem'
                                and r.idobjeto = ".$row['idnfitem'];
                                $resi=d::b()->query($sqli) or die("Erro ao buscar rateio do item: <br>".mysqli_error(d::b()));                        
                                $rowi=mysqli_fetch_assoc($resi);
                                if(!empty($rowi['idrateioitem'])){
                                    $_idrateioitem=$rowi['idrateioitem'];
                                }else{
                                    $insrateioitem = new Insert();
                                    $insrateioitem->setTable("rateioitem");
                                    $insrateioitem->idrateio=$_idrateio;
                                    $insrateioitem->idobjeto=$row['idnfitem'];
                                    $insrateioitem->tipoobjeto='nfitem';
                                    $_idrateioitem=$insrateioitem->save();
                                }
                               
                            }
                            
                            $ins='';
                            foreach ($consumounidade as $idunidade => $valor) {
                                if($qtdsolcom>0){
                                    $percentualsobrasolcom=100-(($qtdcom/$row['qtdsol'])*100);
                                    $percrateio=($valor/$totalqtddrateio);
                                    $perc=($percentualsobrasolcom*$percrateio);
                                }else{
                                    $perc=($valor/($totalqtddrateio))*100;
                                }
                                if($perc>100){
                                    $perc=100;
                                }

                                $insrateioitemd = new Insert();
                                $insrateioitemd->setTable("rateioitemdest");
                                $insrateioitemd->idrateioitem=$_idrateioitem;                          
                                $insrateioitemd->idobjeto=$idunidade;
                                $insrateioitemd->tipoobjeto='unidade';                          
                                $insrateioitemd->valor=$perc;
                                $insrateioitemd->criadopor='sislaudo';
                                $insrateioitemd->criadoem= sysdate();
                                $insrateioitemd->alteradopor='sislaudo';
                                $insrateioitemd->alteradoem= sysdate();
                                $_idrateioitemd=$insrateioitemd->save();

                                 
                                $insrateioitemdo = new Insert();
                                $insrateioitemdo->setTable("rateioitemdestori");
                                $insrateioitemdo->idrateioitem=$_idrateioitem;                          
                                $insrateioitemdo->idobjeto=$idunidade;
                                $insrateioitemdo->tipoobjeto='unidade';                          
                                $insrateioitemdo->valor=$perc;
                                $_idrateioitemdo=$insrateioitemdo->save();
                            
                            }

                                        
                        }
                        
                */
                }///

            }   
            
            

    }//geraRateioDanfe(

        static public function  buscarSqlRateio($idprodserv,$idunidadeest,$consumodiaslote=30){

            $sqlmeio="select 
            u.idlotecons,u.idlote,u.partida,u.exercicio,u.qtdd,u.qtdc,u.idunidade,u.unidade,u.criadoem,u.criadopor,u.unpadrao,u.idsolcomitem
                from (
                -- fabricar e transferir
                select 
                sm.idlotecons,lp.idlote,l.partida,l.exercicio, round(((cm.qtdd*sm.qtdd)/lp.qtdprod),2)  as qtdd, '' as qtdc,ufc.idunidade,ufc.unidade,sm.criadoem,sm.criadopor,sm.obs,si.idsolmat,l.unpadrao,sc.idsolcomitem
                    from lotefracao lf 
                        join lote l on (lf.idlote = l.idlote)
                        join lotecons c on (lf.idlote = c.idlote and c.qtdd>0 and c.idlotefracao=lf.idlotefracao and c.status='ABERTO')	
                        join lotefracao lm on(lm.idlotefracao = c.idobjeto)
                        join unidade um on(um.idunidade=lm.idunidade and um.cd = 'Y')
                        join lotecons cm on(cm.idlotefracao=lm.idlotefracao and cm.qtdd>0 and cm.tipoobjeto='lote'  and cm.status='ABERTO')
                        join lote lp on(lp.idlote=cm.idobjeto)
                        join lotecons sm on(sm.idlote = cm.idobjeto and sm.qtdd>0 and sm.tipoobjeto ='lotefracao'  and sm.status='ABERTO' )
                        join lotefracao lfc on(lfc.idlotefracao = sm.idobjeto)																			
                        join unidade ufc on(ufc.idunidade = lfc.idunidade  and ufc.cd ='N')
                        left join solmatitem si on (si.idsolmatitem=c.idobjetoconsumoespec and c.tipoobjetoconsumoespec = 'solmatitem')
                        left join solcomitem sc on(si.idsolmatitem=sc.idsolmatitem)	
                    where l.idprodserv =   ".$idprodserv."
                        and l.status not in ('CANCELADO','CANCELADA')
                        and lf.idunidade = ".$idunidadeest."		
                        and c.tipoobjeto='lotefracao'
                        and sm.criadoem > DATE_SUB(now(), INTERVAL " .  $consumodiaslote . " DAY)
                        group by cm.idlotecons,sm.idlotecons
                union
                    -- fabricar e vender
                select 
                sm.idlotecons,lp.idlote,l.partida,l.exercicio,round(((cm.qtdd*sm.qtdd)/lp.qtdprod),2) as qtdd,'' as qtdc,ufc.idunidade,ufc.unidade,sm.criadoem,sm.criadopor,'Pedido venda','' as idsolmat,l.unpadrao,sc.idsolcomitem
                
                    from lotefracao lf 
                        join lote l on (lf.idlote = l.idlote)
                        join lotecons c on (lf.idlote = c.idlote and c.qtdd>0 and c.idlotefracao=lf.idlotefracao and c.status='ABERTO')	
                        join lotefracao lm on(lm.idlotefracao = c.idobjeto)
                        join unidade um on(um.idunidade=lm.idunidade and um.cd = 'Y')
                        join lotecons cm on(cm.idlotefracao=lm.idlotefracao and cm.qtdd>0 and cm.tipoobjeto='lote'  and cm.status='ABERTO')
                        join lote lp on(lp.idlote=cm.idobjeto)
                        join lotecons sm on(sm.idlote = cm.idobjeto and sm.qtdd>0 and sm.tipoobjeto ='nfitem'  and sm.status='ABERTO' )
                        join nfitem ni on(ni.idnfitem = sm.idobjeto)
                        join nf n on(n.idnf = ni.idnf)
                        join unidade ufc on( ufc.idtipounidade=21 and n.idempresa=ufc.idempresa and ufc.status='ATIVO')	
                        left join solmatitem si on (si.idsolmatitem=c.idobjetoconsumoespec and c.tipoobjetoconsumoespec = 'solmatitem')
                        left join solcomitem sc on(si.idsolmatitem=sc.idsolmatitem)					
                        where l.idprodserv =    ".$idprodserv."
                        and l.status not in ('CANCELADO','CANCELADA')
                        and lf.idunidade = ".$idunidadeest."
                        and c.tipoobjeto='lotefracao'
                        and sm.criadoem > DATE_SUB(now(), INTERVAL " .  $consumodiaslote . " DAY)
                        group by cm.idlotecons,sm.idlotecons
                union 
                -- receber e transferiu como estava
                select 
                cm.idlotecons,l.idlote,l.partida,l.exercicio,cm.qtdd,'' as qtdc,um.idunidade,um.unidade,cm.criadoem,cm.criadopor,cm.obs,si.idsolmat,l.unpadrao,sc.idsolcomitem
                
                            from lotefracao lf 
                        join lote l on (lf.idlote = l.idlote)
                        join lotecons c on (lf.idlote = c.idlote and c.qtdd>0 and c.idlotefracao=lf.idlotefracao and c.status='ABERTO')	
                        join lotefracao lm on(lm.idlotefracao = c.idobjeto)
                        join unidade um on(um.idunidade=lm.idunidade and um.cd = 'Y')
                        join lotecons cm on(cm.idlotefracao=lm.idlotefracao and cm.qtdd>0 and cm.tipoobjeto='lotefracao'  and cm.status='ABERTO')
                        join lotefracao lfc on(lfc.idlotefracao = cm.idobjeto)																			
                        join unidade ufc on(ufc.idunidade = lfc.idunidade  and ufc.cd = 'N')
                        left join solmatitem si on (si.idsolmatitem=c.idobjetoconsumoespec and c.tipoobjetoconsumoespec = 'solmatitem')
                        left join solcomitem sc on(si.idsolmatitem=sc.idsolmatitem)	    
                        where l.idprodserv =   ".$idprodserv."
                        and l.status not in ('CANCELADO','CANCELADA')
                        and lf.idunidade = ".$idunidadeest."
                        and c.tipoobjeto='lotefracao'
                        and cm.criadoem > DATE_SUB(now(), INTERVAL " .  $consumodiaslote . " DAY)
                        group by cm.idlotecons
                union
                -- receber e jogar fora
                select 
                cm.idlotecons,l.idlote,l.partida,l.exercicio,cm.qtdd,'' as qtdc,um.idunidade,um.unidade,cm.criadoem,cm.criadopor,cm.obs,'' as idsolmat,l.unpadrao,sc.idsolcomitem
                            from lotefracao lf 
                        join lote l on (lf.idlote = l.idlote)
                        join lotecons c on (lf.idlote = c.idlote and c.qtdd>0 and c.idlotefracao=lf.idlotefracao and c.status='ABERTO')	
                        join lotefracao lm on(lm.idlotefracao = c.idobjeto)
                        join unidade um on(um.idunidade=lm.idunidade and um.cd = 'Y')
                        join lotecons cm on(cm.idlotefracao=lm.idlotefracao and cm.qtdd>0 and cm.idobjeto is null and cm.status='ABERTO') 
                        left join solmatitem si on (si.idsolmatitem=c.idobjetoconsumoespec and c.tipoobjetoconsumoespec = 'solmatitem')
                        left join solcomitem sc on(si.idsolmatitem=sc.idsolmatitem)	                                                       
                        where l.idprodserv =    ".$idprodserv."
                        and l.status not in ('CANCELADO','CANCELADA')
                        and lf.idunidade = ".$idunidadeest."
                        and c.tipoobjeto='lotefracao'
                        and cm.criadoem > DATE_SUB(now(), INTERVAL " .  $consumodiaslote . " DAY)
                        group by cm.idlotecons
                union 
                -- fabricar e jogar fora                                                    
                select 
                sm.idlotecons,lp.idlote,l.partida,l.exercicio,round(((cm.qtdd*sm.qtdd)/lp.qtdprod),2) as qtdd,'' as qtdc,um.idunidade,um.unidade,sm.criadoem,sm.criadopor,sm.obs,'' as idsolmat,l.unpadrao,sc.idsolcomitem
                        from lotefracao lf 
                        join lote l on (lf.idlote = l.idlote)
                        join lotecons c on (lf.idlote = c.idlote and c.qtdd>0 and c.idlotefracao=lf.idlotefracao and c.status='ABERTO')	
                        join lotefracao lm on(lm.idlotefracao = c.idobjeto)
                        join unidade um on(um.idunidade=lm.idunidade and um.cd = 'Y')
                        join lotecons cm on(cm.idlotefracao=lm.idlotefracao and cm.qtdd>0 and cm.tipoobjeto='lote'  and cm.status='ABERTO')
                        join lote lp on(lp.idlote=cm.idobjeto)
                        join lotecons sm on(sm.idlote = cm.idobjeto and sm.qtdd>0 and sm.idobjeto is null  and sm.status='ABERTO' )
                        left join solmatitem si on (si.idsolmatitem=c.idobjetoconsumoespec and c.tipoobjetoconsumoespec = 'solmatitem')
                        left join solcomitem sc on(si.idsolmatitem=sc.idsolmatitem)	
                        where l.idprodserv =   ".$idprodserv." 
                        and l.status not in ('CANCELADO','CANCELADA')
                        and lf.idunidade = ".$idunidadeest."
                        and c.tipoobjeto='lotefracao'
                        and sm.criadoem > DATE_SUB(now(), INTERVAL " .  $consumodiaslote . " DAY)
                        group by cm.idlotecons,sm.idlotecons
                union
                -- fabricado tranferiu devolvendo
                select
                    sm.idlotecons,lp.idlote,l.partida,l.exercicio,'' as qtdd,round(((cm.qtdd*sm.qtdc)/lp.qtdprod),2) as qtdc,ufc.idunidade,ufc.unidade,sm.criadoem,sm.criadopor,sm.obs, '' as idsolmat,l.unpadrao,null as idsolcomitem
                        from lotefracao lf 
                        join lote l on (lf.idlote = l.idlote)
                        join lotecons c on (lf.idlote = c.idlote and c.qtdd>0 and c.idlotefracao=lf.idlotefracao and c.status='ABERTO')	
                        join lotefracao lm on(lm.idlotefracao = c.idobjeto)
                        join unidade um on(um.idunidade=lm.idunidade and um.cd = 'Y')
                        join lotecons cm on(cm.idlotefracao=lm.idlotefracao and cm.qtdd>0 and cm.tipoobjeto='lote'  and cm.status='ABERTO')
                        join lote lp on(lp.idlote=cm.idobjeto)
                        join lotecons sm on(sm.idlote = cm.idobjeto and sm.qtdc>0 and sm.tipoobjeto ='lotefracao'  and sm.status='ABERTO' )
                        join lotefracao lfc on(lfc.idlotefracao = sm.idobjeto)																			
                        join unidade ufc on(ufc.idunidade = lfc.idunidade  and ufc.cd ='N')				
                        where l.idprodserv =    ".$idprodserv." 
                        and l.status not in ('CANCELADO','CANCELADA')
                        and lf.idunidade = ".$idunidadeest."
                        and c.tipoobjeto='lotefracao'
                        and sm.criadoem > DATE_SUB(now(), INTERVAL " .  $consumodiaslote . " DAY)
                        group by cm.idlotecons,sm.idlotecons
                union  
                -- devolvido da mesma forma que recebeu   
                select 
                cm.idlotecons,l.idlote,l.partida,l.exercicio,cm.qtdd,cm.qtdc,um.idunidade,um.unidade,cm.criadoem,cm.criadopor,cm.obs,si.idsolmat,l.unpadrao,sc.idsolcomitem
                        from lotefracao lf 
                        join lote l on (lf.idlote = l.idlote)
                        join lotecons c on (lf.idlote = c.idlote and c.qtdd>0 and c.idlotefracao=lf.idlotefracao and c.status='ABERTO')	
                        join lotefracao lm on(lm.idlotefracao = c.idobjeto)
                        join unidade um on(um.idunidade=lm.idunidade and um.cd = 'Y')
                        join lotecons cm on(cm.idlotefracao=lm.idlotefracao and cm.qtdc>0 and cm.tipoobjeto='lotefracao'  and cm.status='ABERTO')
                        join lotefracao lfc on(lfc.idlotefracao = cm.idobjeto)																			
                        join unidade ufc on(ufc.idunidade = lfc.idunidade  and ufc.cd = 'N')
                        left join solmatitem si on (si.idsolmatitem=c.idobjetoconsumoespec and c.tipoobjetoconsumoespec = 'solmatitem')
                        left join solcomitem sc on(si.idsolmatitem=sc.idsolmatitem)	
                        where l.idprodserv =    ".$idprodserv."
                        and l.status not in ('CANCELADO','CANCELADA')
                        and lf.idunidade = ".$idunidadeest."
                        and c.tipoobjeto='lotefracao'
                        and cm.criadoem > DATE_SUB(now(), INTERVAL " .  $consumodiaslote . " DAY)
                        group by cm.idlotecons	
                    union   
                    -- daqui para baixo consumos para CD = N
            select c.idlotecons,l.idlote,l.partida,l.exercicio,c.qtdd,c.qtdc,u.idunidade,u.unidade,c.criadoem,c.criadopor,c.obs,si.idsolmat,l.unpadrao,sc.idsolcomitem
                from lotefracao lf 
                        join lote l on (lf.idlote = l.idlote)
                        join lotecons c on (lf.idlote = c.idlote and (c.qtdd>0 or c.qtdc>0) and c.idlotefracao=lf.idlotefracao and c.tipoobjeto='lote' and c.status='ABERTO')
                        join lote lp on(lp.idlote = c.idobjeto)
                        join unidade u on(u.idunidade=lp.idunidade and u.cd='N')	
                        left join solmatitem si on (si.idsolmatitem=c.idobjetoconsumoespec and c.tipoobjetoconsumoespec = 'solmatitem')
                        left join solcomitem sc on(si.idsolmatitem=sc.idsolmatitem)		
                where l.idprodserv = ".$idprodserv."
                and l.status not in ('CANCELADO','CANCELADA')
                and lf.idunidade = ".$idunidadeest."
                and c.criadoem > DATE_SUB(now(), INTERVAL " .  $consumodiaslote . " DAY)
                union
            select c.idlotecons,l.idlote,l.partida,l.exercicio,c.qtdd,c.qtdc,u.idunidade,u.unidade,c.criadoem,c.criadopor,c.obs,si.idsolmat,l.unpadrao,sc.idsolcomitem
                from lotefracao lf 
                        join lote l on (lf.idlote = l.idlote)
                        join lotecons c on (lf.idlote = c.idlote and (c.qtdd>0 or c.qtdc>0) and c.idlotefracao=lf.idlotefracao and c.tipoobjeto='lotefracao' and c.status='ABERTO')
                        join lotefracao lp on(lp.idlotefracao = c.idobjeto)
                        join unidade u on(u.idunidade=lp.idunidade  and u.cd='N')
                        left join solmatitem si on (si.idsolmatitem=c.idobjetoconsumoespec and c.tipoobjetoconsumoespec = 'solmatitem')
                        left join solcomitem sc on(si.idsolmatitem=sc.idsolmatitem)	
                    where l.idprodserv = ".$idprodserv."
                        and l.status not in ('CANCELADO','CANCELADA')
                        and lf.idunidade = ".$idunidadeest."
                        and c.criadoem > DATE_SUB(now(), INTERVAL " .  $consumodiaslote . " DAY)
            union 
            select c.idlotecons,l.idlote,l.partida,l.exercicio,c.qtdd,c.qtdc,u.idunidade,u.unidade,c.criadoem,c.criadopor,c.obs,si.idsolmat,l.unpadrao,sc.idsolcomitem
                from lotefracao lf 
                        join lote l on (lf.idlote = l.idlote)
                        join lotecons c on (lf.idlote = c.idlote and (c.qtdd>0 or c.qtdc>0) and c.idlotefracao=lf.idlotefracao and c.tipoobjeto='nfitem' and c.status='ABERTO')
                        join nfitem i  on(i.idnfitem = c.idobjeto)
                        join nf n on( n.idnf = i.idnf and n.status  !='CANCELADO' )
                        join unidade u on( u.idtipounidade=21 and n.idempresa=u.idempresa and u.status='ATIVO')	
                        left join solmatitem si on (si.idsolmatitem=c.idobjetoconsumoespec and c.tipoobjetoconsumoespec = 'solmatitem')	
                        left join solcomitem sc on(si.idsolmatitem=sc.idsolmatitem)	
                    where l.idprodserv = ".$idprodserv."
                        and l.status not in ('CANCELADO','CANCELADA')
                        and lf.idunidade = ".$idunidadeest."
                        and c.criadoem > DATE_SUB(now(), INTERVAL " .  $consumodiaslote . " DAY)
            union
            select c.idlotecons,l.idlote,l.partida,l.exercicio,c.qtdd,c.qtdc,u.idunidade,u.unidade,c.criadoem,c.criadopor,c.obs,si.idsolmat,l.unpadrao,sc.idsolcomitem
                from lotefracao lf 
                        join lote l on (lf.idlote = l.idlote)
                        join lotecons c on (lf.idlote = c.idlote and (c.qtdd>0 or c.qtdc>0) and c.idlotefracao=lf.idlotefracao and c.tipoobjeto='resultado' and c.status='ABERTO')
                        join resultado i  on(i.idresultado = c.idobjeto)
                        join amostra n on( n.idamostra = i.idamostra  )
                        join unidade u on(  n.idunidade=u.idunidade and u.cd='N' )			
                        left join solmatitem si on (si.idsolmatitem=c.idobjetoconsumoespec and c.tipoobjetoconsumoespec = 'solmatitem')	
                        left join solcomitem sc on(si.idsolmatitem=sc.idsolmatitem)	
                    where l.idprodserv = ".$idprodserv."
                        and l.status not in ('CANCELADO','CANCELADA')
                        and lf.idunidade = ".$idunidadeest."
                        and c.criadoem > DATE_SUB(now(), INTERVAL " .  $consumodiaslote . " DAY)
            union        
            select c.idlotecons,l.idlote,l.partida,l.exercicio,c.qtdd,c.qtdc,u.idunidade,u.unidade,c.criadoem,c.criadopor,c.obs,si.idsolmat,l.unpadrao,sc.idsolcomitem
                from lotefracao lf 
                        join lote l on (lf.idlote = l.idlote)
                        join lotecons c on (lf.idlote = c.idlote and (c.qtdd>0 or c.qtdc>0) and c.idlotefracao=lf.idlotefracao and c.tipoobjeto is null and c.idobjeto is null and c.status='ABERTO')
                        join unidade u on(  lf.idunidade=u.idunidade and u.cd='N' )	
                        left join solmatitem si on (si.idsolmatitem=c.idobjetoconsumoespec and c.tipoobjetoconsumoespec = 'solmatitem')
                        left join solcomitem sc on(si.idsolmatitem=sc.idsolmatitem)	
                    where l.idprodserv = ".$idprodserv."
                        and l.status not in ('CANCELADO','CANCELADA')
                        and lf.idunidade = ".$idunidadeest."
                        and c.criadoem > DATE_SUB(now(), INTERVAL " .  $consumodiaslote . " DAY)
                ) as u where u.idsolcomitem is null order by u.partida,u.criadoem,u.unidade";

        
        return $sqlmeio;
    }   

    static public function gerarRateioTodos(){
        $sql="select 
                    u.idnf,u.vlritem,u.tiponf
                    from (
                        SELECT n.idnf,i.vlritem,n.tiponf
                                    FROM nf n 
                                    join nfitem i on(i.idnf=n.idnf and i.nfe='Y'and i.qtd> 0 
                                    -- and i.vlritem >0 
                                    and i.idprodserv is not null) 
                                    join tipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv )
                                    join contaitem c on(c.idcontaitem=i.idcontaitem  )
                                    join contapagar cp on(cp.idobjeto = n.idnf and cp.tipoobjeto ='nf')
                                    join agencia a on(a.idagencia=cp.idagencia )
                                    join prodserv ps on(ps.idprodserv=i.idprodserv and ps.tipo='PRODUTO')
                                    where cp.tipoespecifico!= 'AGRUPAMENTO'                        
                                    -- and cp.status in ('ABERTO','PENDENTE')
                                    and cp.datareceb >'2021-12-31'
                                    and cp.status !='INATIVO'
                                    and cp.tipo = 'D'
                                    and cp.valor>0
                                    and n.tiponf not in('S','R','O','V')
                                    and not exists(                           
                                        select 1 from  rateioitem r force index(idobj_tipoobj) 
                                            where(r.tipoobjeto='nfitem' and r.idobjeto =i.idnfitem )
                                    )
                            union all                                        
                            SELECT  n.idnf,i.vlritem,n.tiponf
                                FROM contapagar cp
                                join contapagaritem ci on(cp.idcontapagar =ci.idcontapagar 
                                                            and ci.tipoobjetoorigem ='nf')
                                join nf n on(ci.idobjetoorigem =n.idnf  ) 
                                join nfitem i on(i.idnf=n.idnf  and i.nfe='Y'and i.qtd> 0 
                                -- and i.vlritem >0  
                                and i.idprodserv is not null)               
                                join tipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv )
                                join contaitem c on(c.idcontaitem=i.idcontaitem  ) 
                                join agencia a on(a.idagencia=cp.idagencia )
                                join prodserv ps on(ps.idprodserv=i.idprodserv and ps.tipo='PRODUTO')
                                where cp.tipoespecifico = 'AGRUPAMENTO' 
                                -- and cp.status in ('ABERTO','PENDENTE')
                                and cp.datareceb >'2021-12-31'
                                and ci.status!='INATIVO'
                                and cp.valor>0
                                and cp.tipo = 'D'
                                and n.tiponf not in('S','R','O','V')
                                and not exists(                           
                                    select 1 from  rateioitem r force index(idobj_tipoobj) 
                                        where(r.tipoobjeto='nfitem' and r.idobjeto =i.idnfitem )
                                )
                        union all
                            SELECT n.idnf,i.vlritem,n.tiponf
                                    FROM nf n 
                                    join nfitem i on(i.idnf=n.idnf  and i.nfe='Y'and i.qtd> 0
                                     -- and i.vlritem >0 
                                     and i.idpessoa is not null) 
                                    join tipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv )
                                    join contaitem c on(c.idcontaitem=i.idcontaitem  )
                                    join contapagar cp on(cp.idobjeto = n.idnf and cp.tipoobjeto ='nf')
                                    join agencia a on(a.idagencia=cp.idagencia )
                                    left join prodserv ps on(ps.idprodserv=i.idprodserv)
                                    where cp.tipoespecifico!= 'AGRUPAMENTO'                         
                                    -- and cp.status in ('ABERTO','PENDENTE')
                                    and cp.datareceb >'2021-12-31'
                                    and cp.tipo = 'D'
                                    and cp.valor>0                                                                          
                                    and n.tiponf in('S','R')
                                    and not exists(                           
                                        select 1 from  rateioitem r force index(idobj_tipoobj) 
                                            where(r.tipoobjeto='nfitem' and r.idobjeto =i.idnfitem )
                                    )
                            union all                                        
                            SELECT  n.idnf,i.vlritem,n.tiponf
                                FROM contapagar cp
                                join contapagaritem ci on(cp.idcontapagar =ci.idcontapagar 
                                                            and ci.tipoobjetoorigem ='nf')
                                join nf n on(ci.idobjetoorigem =n.idnf  ) 
                                join nfitem i on(i.idnf=n.idnf   and i.nfe='Y'and i.qtd> 0
                                -- and i.vlritem >0 
                                 and i.idpessoa is not null)               
                                join tipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv )
                                join contaitem c on(c.idcontaitem=i.idcontaitem  )  
                                join agencia a on(a.idagencia=cp.idagencia )
                                left join prodserv ps on(ps.idprodserv=i.idprodserv)
                                where cp.tipoespecifico = 'AGRUPAMENTO'                               
                                -- and cp.status in ('ABERTO','PENDENTE')
                                and cp.datareceb >'2021-12-31'
                                and cp.valor>0
                                and ci.status!='INATIVO'
                                and cp.tipo = 'D'                               
                                and n.tiponf in('S','R','M','T')
                                and not exists(                           
                                    select 1 from  rateioitem r force index(idobj_tipoobj) 
                                        where(r.tipoobjeto='nfitem' and r.idobjeto =i.idnfitem )
                                )
                ) as u   
        where 1 -- u.vlritem>0 
        group by u.idnf";


        $_res = d::b()->query($sql) or die("Erro ao buscar rateios pedentes " . $sql);
        while($row= mysqli_fetch_assoc( $_res)){

           // if($row['tiponf']=='C' ){

                //gera rateio quando tem pessoa no nfitem
                $sqln="select i.idnfitem from nfitem i join contaitem c on(c.idcontaitem= i.idcontaitem and c.somarelatorio = 'Y')
                where  i.nfe='Y' and i.qtd> 0
                -- and i.vlritem >0 
                 and i.idprodserv is not null  and idpessoa is null and  i.idnf = ".$row['idnf']."
                and not exists (select 1 from rateioitem r join rateioitemdest d on(d.idrateioitem = r.idrateioitem)
                 where r.idobjeto = i.idnfitem and r.tipoobjeto  = 'nfitem')";
                $resn=d::b()->query($sqln) or die("Erro ao buscar  os itens da nf: <br>".mysqli_error(d::b()));
                $qtdpes=mysqli_num_rows($resn);
            
                if($qtdpes>0){//so vai gerar para itens com pessoas vinculadas
                    cnf::geraRateioDanfe($row['idnf']);
                }
            
            
            // }else{
            
                //gera rateio quando tem pessoa no nfitem
                $sqln="select * from nfitem i where i.nfe='Y'  
                            -- and i.vlritem >0 
                            and i.idpessoa is not null and i.idnf =".$row['idnf']."
                            and not exists (select 1 from rateioitem r where r.idobjeto = i.idnfitem and r.tipoobjeto  = 'nfitem')";
                $resn=d::b()->query($sqln) or die("Erro ao buscar  os itens da nf: <br>".mysqli_error(d::b()));
                $qtdpes=mysqli_num_rows($resn);
            
                if($qtdpes>0){//so vai gerar para itens com pessoas vinculadas
                    cnf::geraRateio($row['idnf']);
                }
            
             //}


        }

     cnf::corrigeRateio();

    }

    static public function buscaSqlsolcom($idnfitem){

        $sqlsol="select sum(si.qtdc) as qtdcom ,i.qtd,i.qtdsol,s.idunidade,si.idprodserv,round((sum(si.qtdc)/i.qtdsol)*100,2) as percentual
        from nfitem i join nf n on(n.idnf=i.idnf)
        join cotacao c on(c.idcotacao = n.idobjetosolipor  and n.tipoobjetosolipor='cotacao')
        join solcomitem si on(si.idcotacao = c.idcotacao and si.idprodserv = i.idprodserv)
        join solcom s on(s.idsolcom=si.idsolcom)
        where i.idnfitem =".$idnfitem." group by s.idunidade,si.idprodserv";
        return $sqlsol;
    }

    static public function gerarItemPedido($arrInsProd,$idnf){

       

        $arrNf=getObjeto("nf",$idnf);
        $idnatop = $arrNf["idnatop"];

      
    
        if(!empty($arrNf['idempresafat'])){
            $_idempresa=$arrNf['idempresafat'];
        }else{
            $_idempresa=$arrNf['idempresa'];
        }
    
        $ufemp=traduzid("empresa","idempresa","uf",$_idempresa);
        
        if(empty($idnatop)){
             die("Obrigatório informar natureza da informação.");
        }
        //busca endereco do cliente ou fornecedor tipoendereco = sacado		
        $sqlp = "select e.uf,p.inscrest,ep.indiedest as indiedestempresa ,p.indiedest,p.idpessoa,f.tpnf,p.vendadireta,f.idvendedor,
             (select 1 from natop nt where  nt.natop like('%DEVOLU%') and nt.idnatop=f.idnatop ) as natopdev,
            (select 1 from natop nt where  nt.natop like('%OUTRA%') and nt.natop like('%SAIDA%')  and nt.idnatop=f.idnatop ) as natoprem
            from nf f,pessoa p, endereco e,empresa ep
        where p.idpessoa = f.idpessoafat
        and ep.idempresa=f.idempresa
        and e.idendereco = f.idenderecofat and f.idnf = ".$idnf;
        $resp = d::b()->query($sqlp) or die("Erro ao buscar endereco: ".mysqli_error(d::b()));
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
                /*
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
                */
                
                if($uf != $ufemp){
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
       // LOOP NOS ITENS DO + DA TELA
       foreach($arrInsProd as $k=>$v){
          // print_r($v);die();
           $i=$i+1;
    
            $idprodserv=$v['idprodserv'];
            $idprodservformula=$v['idprodservformula'];
            $prodservdescr=$v['prodservdescr'];
                   
            if(!empty($idprodserv) and !empty($idnf)){
   
                $_sql = "select ps.*                                    
                                from prodserv ps 
                            where ps.idprodserv =".$idprodserv;
    
                //echo $_sql;	
                $res = d::b()->query($_sql) or die($_sql."Erro ao retornar produto: ".mysqli_error(d::b()));
                $qtdrows1= mysqli_num_rows($res);
          
        
            
                if($qtdrows1 > 0){	
                  
                    $row = mysqli_fetch_assoc($res);
                    $comissionado=$row['comissionado'];
                    
                    $valor=$row["vlrvenda"];// seta como valor o valor do produto
                    $comissaodf=$row["comissao"];// seta como comissao o valor do produto
                    $idobcomissaodf=$row['idprodserv'];
                    $obcomissaodf='prodserv';
                  
                    if($ufemp=='PR' and  $uf=='PR' ){// se for da filial do parana @838527
                        if($rowuf['indiedest']==9){// Cliente não contribuinte                            
                            $row['cst']=00;
                        }else{// Cliente  contribuinte e isento
                            $row['cst']=51;
                        }
                    }elseif($ufemp==$uf /*or $rowuf['indiedest']==1*/){

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
                        $_sqlf="select vlrvenda ,comissao from prodservformula where idprodservformula=".$idprodservformula;
                        $resf = d::b()->query($_sqlf) or die($_sqlf."Erro ao buscar formula: ".mysqli_error(d::b()));
                        $qtdformula=mysqli_num_rows($resf);
                        $rowf=mysqli_fetch_assoc($resf);
                      
    
                        $valor=$rowf["vlrvenda"];//troca pelo valor e comissao da formula
                        $comissaodf=$rowf["comissao"];
                        $idobcomissaodf=$idprodservformula;
                        $obcomissaodf='prodservformula';
                     
                    }
                    
                  
                    if(!empty($idprodservformula)){
                        $sqlvlr="select cf.valor from contratopessoa p 
                                join contrato c on(c.idcontrato=p.idcontrato and c.tipo='P')
                                join desconto d on(d.idcontrato=c.idcontrato)
                                join contratoprodservformula cf on(cf.iddesconto = d.iddesconto and  cf.valor > 0 )
                                where p.idpessoa = ".$rowuf['idpessoa']."
                                and d.idtipoteste=".$row['idprodserv']."
                                and cf.idprodservformula =".$idprodservformula;
    
                    }else{
                        $sqlvlr=" select d.valor from contratopessoa p join contrato c on(c.idcontrato=p.idcontrato and c.tipo='P')
                        join desconto d on(d.idcontrato=c.idcontrato)
                        where p.idpessoa = ".$rowuf['idpessoa']."
                        and d.idtipoteste=".$row['idprodserv'];                
                      
    
                    }
    
                                
                    $resvlr=d::b()->query($sqlvlr) or die("Erro ao buscar valor de contrato do produto sql=".$sqlvlr);
                    $temcontrato=mysqli_num_rows($resvlr);
                    //se tiver contrato pega valor do contrato
                    if($temcontrato>0){
                            $rowvlr=mysqli_fetch_assoc($resvlr);
                            $valor=$rowvlr['valor'];
                    }
    
                   $idtipoprodserv = traduzid('prodserv', 'idprodserv', 'idtipoprodserv', $idprodserv);

                   $idcontaitem = traduzid('prodservcontaitem', 'idprodserv', 'idcontaitem', $idprodserv);                   
                  
                    // montar o item para insert
    
                   if(empty($valor)){$valor=0.00;}
    
                    $infitem = new Insert();
                    $infitem->setTable("nfitem");
                    $infitem->qtd=$v["quantidade"];    
                    $infitem->idprodserv=$idprodserv;     
                    if(!empty($idprodservformula)){
                        $infitem->idprodservformula=$idprodservformula;
                    } 

                    $infitem->idtipoprodserv = $idtipoprodserv;
                    $infitem->idcontaitem = $idcontaitem;
                    $infitem->idnf=$idnf;
                    $infitem->tiponf='V';
                    $infitem->ncm=$row["ncm"];
                    $infitem->idempresa=cb::idempresa();
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
                    $infitem->nfe=$row["venda"];          
                                
                     
                    if($rowuf['indiedest'] == 9 and $rowuf['indiedestempresa'] !=2 and $uf !=$ufemp and $rowuf['tpnf']==1 and  empty($rowuf['natopdev']) ){
                         
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
    
                    //unset($_SESSION['arrpostbuffer']);                
                   // $_SESSION['arrpostbuffer']['1']['u']['nf']['idnf']=$idnf;
    
                    if($vendadireta=='N' and  $comissionado =='Y'){
                        $sqlcont="select c.idcontrato,o.idpessoa,o.comissao
                                    from contratopessoa p 
                                    join contrato c on(c.idcontrato=p.idcontrato and c.tipo='P')
                                    join desconto d on(d.idcontrato=c.idcontrato)
                                    join contratocomissao o on(o.iddesconto = d.iddesconto)
                                where p.idpessoa = ".$rowuf['idpessoa']."
                                and d.idtipoteste=".$idprodserv;
                        $rescont=d::b()->query($sqlcont) or die("Falha ao buscar comissoes do contrato sql=".$sqlcont);
                        $qtdcom=mysqli_num_rows($rescont);
                        if($qtdcom>0){
                            while($rcom=mysqli_fetch_assoc( $rescont)){
                                $infitemc = new Insert();
                                $infitemc->setTable("nfitemcomissao");
                                $infitemc->idnfitem=$Nidnfitem; 
                                $infitemc->idpessoa=$rcom['idpessoa']; 
                                $infitemc->idempresa=$_idempresa; 
                                $infitemc->pcomissao=$rcom['comissao']; 
                                $infitemc->idobjeto=$rcom['idcontrato']; 
                                $infitemc->tipoobjeto='contrato'; 
                                $idnfitemc=$infitemc->save();
                            }
                        }elseif($comissaodf>0){//comissão no produto
                                $sqlc="select                        
                                        c.idcontato                       
                                        from pessoa p ,pessoacontato c
                                        where p.status='ATIVO'
                                        and p.idtipopessoa in(12,1)
                                        and  p.idpessoa = c.idcontato
                                        and c.idpessoa = ".$rowuf['idpessoa']." order by nome";
                                $resc=d::b()->query($sqlc) or die("Falha ao buscar responsaveis do cliente sql=".$sqlc);
    
                            while($rowc=mysqli_fetch_assoc($resc)){
                                $infitemc = new Insert();
                                $infitemc->setTable("nfitemcomissao");
                                $infitemc->idnfitem=$Nidnfitem; 
                                $infitemc->idpessoa=$rowc['idcontato']; 
                                $infitemc->idempresa=$_idempresa; 
                                $infitemc->pcomissao= $comissaodf; 
                                $infitemc->idobjeto= $idobcomissaodf; 
                                $infitemc->tipoobjeto= $obcomissaodf; 
                                $idnfitemc=$infitemc->save();
    
                            }
    
                            //comissao do gestor
                            $slp="select idplantel from plantelobjeto where idobjeto=".$rowuf['idpessoa']." and tipoobjeto='pessoa'  limit 1;";
                            $rsp=d::b()->query($slp) or die("Falha ao buscar divisao do cliente sql=".$slp);
                            $rosp=mysqli_fetch_assoc($rsp);	
                            if(!empty($rosp['idplantel'])){
    
                                $sqg="select i.comissaogest,d.idpessoa,d.iddivisao
                                        from divisao d join   divisaoitem i on(i.iddivisao=d.iddivisao)
                                        join divisaoplantel dp on(dp.iddivisao=d.iddivisao and dp.idplantel=".$rosp['idplantel'].")
                                        where  i.idprodserv = ".$idprodserv." 
                                        and d.status='ATIVO'
                                        and d.tipo='PRODUTO' group by d.idpessoa";
                                $resg=d::b()->query($sqg) or die("Falha ao buscar comissao do gestorsql=".$sqg);
    
                                while($rowg=mysqli_fetch_assoc($resg)){
                                    $infitemc = new Insert();
                                    $infitemc->setTable("nfitemcomissao");
                                    $infitemc->idnfitem=$Nidnfitem; 
                                    $infitemc->idpessoa=$rowg['idpessoa']; 
                                    $infitemc->idempresa=$_idempresa;
                                    $infitemc->pcomissao= $rowg['comissaogest']; 
                                    $infitemc->idobjeto= $rowg['iddivisao']; 
                                    $infitemc->tipoobjeto= 'divisao'; 
                                    $idnfitemc=$infitemc->save();
    
                                }
                            }
    
                               
    
                        }else{// ligação funcionario cliente
    
                            $sqlc="select                        
                                c.idcontato,c.participacaoprod,c.idpessoacontato                     
                                from pessoa p ,pessoacontato c
                                where p.status='ATIVO'
                                and p.idtipopessoa in(12,1)
                                and c.participacaoprod > 0
                                and  p.idpessoa = c.idcontato
                                and c.idpessoa = ".$rowuf['idpessoa']." order by nome";
                            $resc=d::b()->query($sqlc) or die("Falha ao buscar responsaveis do cliente sql=".$sqlc);
    
                            while($rowc=mysqli_fetch_assoc($resc)){
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
                            $slp="select idplantel from plantelobjeto where idobjeto=".$rowuf['idpessoa']." and tipoobjeto='pessoa'  limit 1;";
                            $rsp=d::b()->query($slp) or die("Falha ao buscar divisao do cliente sql=".$slp);
                            $rosp=mysqli_fetch_assoc($rsp);	
                            if(!empty($rosp['idplantel'])){
    
                                $sqg="select i.comissaogest,d.idpessoa,d.iddivisao
                                        from divisao d join   divisaoitem i on(i.iddivisao=d.iddivisao)
                                        join divisaoplantel dp on(dp.iddivisao=d.iddivisao and dp.idplantel=".$rosp['idplantel'].")
                                        where  i.idprodserv = ".$idprodserv." 
                                        and d.status='ATIVO'
                                        and d.tipo='PRODUTO' group by d.idpessoa";
                                $resg=d::b()->query($sqg) or die("Falha ao buscar comissao do gestorsql=".$sqg);
    
                                while($rowg=mysqli_fetch_assoc($resg)){
                                    $infitemc = new Insert();
                                    $infitemc->setTable("nfitemcomissao");
                                    $infitemc->idnfitem=$Nidnfitem; 
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
            }elseif(!empty($prodservdescr) and !empty($idnf)){//if(!empty($idprodserv) and !empty($idnf)){


                $infitem = new Insert();
                $infitem->setTable("nfitem");
               
                $infitem->qtd=$v["quantidade"];
                $infitem->prodservdescr= $prodservdescr; 
                $infitem->idnf=$idnf;
                $infitem->idempresa=$_idempresa;
                $infitem->tiponf='V';
                $infitem->aliqicms=$rowaliq["aliq"]; 
                $infitem->aliqicmsint=$rowaliq["aliqicmsint"]; 
               
                $infitem->vlrliq='0.00';
                if($rowuf['indiedest'] == 9 and $uf !=$ufemp and $rowuf['tpnf']==1 and  empty($rowuf['natopdev']) ){
                    $infitem->indiedest=$rowuf["indiedest"];   
                    if(!empty($rowuf['natoprem'])){
                        $infitem->cfop=$cfop;
                    }else{
                        $infitem->cfop='6107';   
                    }
                }else{
                    $infitem->cfop=$cfop;
                }
                $Nidnfitem=$infitem->save();




            }
          
       } //foreach($arrInsProd as $k=>$v)
       
            
    }



    static public function gerarItemPedidoTransferencia($arrInsProd,$idnf){

       
             
                 
       $i=99977;
       // LOOP NOS ITENS DO + DA TELA
       foreach($arrInsProd as $vidnfitem=>$v){
          //print_r($vidnfitem);die();
           $i=$i+1;
                    
                                
                    // montar o item para insert
                    $valor=$v['vlritem']/2;        
                    
                   if(empty($valor)){$valor=0.00;}
    
                    $infitem = new Insert();
                    $infitem->setTable("nfitem");
                    $infitem->qtd=$v["qtd"];    
                    if(!empty($v['idprodserv'])){
                    $infitem->idprodserv=$v['idprodserv'];     
                    }
                    if(!empty($v['idprodservformula'])){
                        $infitem->idprodservformula=$v['idprodservformula'];
                    } 
                    $infitem->idtipoprodserv=$v["idtipoprodserv"];
                    $infitem->idcontaitem=$v["idcontaitem"];
                    $infitem->idnf=$idnf;
                    $infitem->tiponf='V';
                    $infitem->ncm=$v["ncm"];
                    $infitem->idempresa=cb::idempresa();
                    $infitem->aliqbasecal=$v["reducaobc"];               
                    $infitem->cest=$v["cest"];
                    if(!empty($v['codprodserv'])){
                    $infitem->cprod=$v["codprodserv"];
                    }
                    if(!empty($v['prodservdescr'])){
                    $infitem->prodservdescr= $v["prodservdescr"];  
                    }              
                    $infitem->un=$v["un"];
                    $infitem->aliqipi=$v["ipi"]; 
                    $infitem->aliqicms=$v["aliq"]; 
                    $infitem->aliqicmsint=$v["aliqicmsint"]; 
                    $infitem->aliqpis=$v["pis"]; 
                    $infitem->cprod=(string)$v["cprod"];
                    $infitem->descdeson=(string)$v["descdeson"];                    
                    $infitem->aliqcofins=$v["cofins"]; 
                    $infitem->iss=$v["iss"]; 
                    $infitem->finalidade=(string)$v["finalidade"];
                    $infitem->modbc=(string)$v["modbc"];
                    $infitem->origem=(string)$v["origem"];
                    $infitem->cst=(string)'41';
                    $infitem->piscst=(string)'08';
                    $infitem->confinscst=(string)'08';
                    $infitem->ipint=(string)$v["ipint"];            
                    $infitem->vlritem= (string) number_format((float)$valor, 4, '.', ''); 
                    $infitem->vlrliq= (string) number_format((float)$valor, 4, '.', '');                      
                    $infitem->nfe=$v["nfe"];          
                    $infitem->cfop='6151';  
                    
                    $Nidnfitem=$infitem->save();

                    if(!empty($vidnfitem) and !empty($Nidnfitem)){
                        $sqlr="update lotereserva set idobjeto=".$Nidnfitem." ,tipoobjetoconsumoespec = 'nfitem' ,idobjetoconsumoespec=".$vidnfitem." where idobjeto = ".$vidnfitem." and tipoobjeto = 'nfitem' and status ='PENDENTE'";
                        $reserva=d::b()->query($sqlr) or die("Falha ao migrar reserva sql=".$sqlr);
    
                        $sqlcon="update lotecons set idobjeto=".$Nidnfitem.",tipoobjetoconsumoespec = 'nfitem' ,idobjetoconsumoespec=".$vidnfitem." where idobjeto =  ".$vidnfitem." and tipoobjeto = 'nfitem' and status ='ABERTO' and qtdd > 0";
                        $lconsumo=d::b()->query($sqlcon) or die("Falha ao migrar consumo sql=".$sqlcon);

                        $sqlcon="update nf set idobjetosolipor=".$idnf." ,tipoobjetosolipor='nf' where idnf =  ".$v['idnf']." ";
                        $lconsumo=d::b()->query($sqlcon) or die("Falha ao vincular nf origem item sql=".$sqlcon);
                        
                    }
                    //unset($_SESSION['arrpostbuffer']);                
                   // $_SESSION['arrpostbuffer']['1']['u']['nf']['idnf']=$idnf;    
                     
           
          
       } //foreach($arrInsProd as $k=>$v)
       
            
    }

    static public function  criarEntrada($_idnf)
    {    
               
        $sql="select e.idpessoaform,n.* from nf n join empresa e on(e.idempresa=n.idempresa) where n.idnf=".$_idnf;    
        $res = d::b()->query($sql) or die("saveprechange__nfentrada: Falha ao recuperar nf transferencia:\n".mysqli_error(d::b())."\n".$sql);
        $arrColunas = mysqli_fetch_fields($res);
        while($r = mysqli_fetch_assoc($res)){
            $idpessoa=$r['idpessoaform'];
            $idempresa=$r['idempresafat'];
            foreach ($arrColunas as $col) {
                $arrnf[$col->name]=$r[$col->name];
            }
        }

        //LTM - 05-04-2021: Retorna o Idfluxo nf para Pedido (Tipo V)
        $idfluxostatus = FluxoController::getIdFluxoStatus('nfentrada', 'APROVADO');
            
        //INSERIR A NF	
        $insnf = new Insert();
        $insnf->setTable("nf");	
        foreach ($arrnf as $key => $value) 
        {	    
        // echo "{$key} => {$value} ";
            if($key=='dtemissao'){
                $value=date("Y-m-d H:i:s");;		
            }
            if($key=='status'){
                $value="APROVADO";
            }
            if($key == 'idfluxostatus'){
                $value = $idfluxostatus;
            }
                     
            
            if($key=='tpnf'){
                $value='1';
            }
            
            if($key=='geracontapagar'){
                $value='N';
            }

            if($key=='idformapagamento'){
                $value='';
            }
            if($key=='finnfe'){
                $value='1';
            }
            if($key=='idempresa'){
                $value=$idempresa;
            }
            
            
            if($key=='tiponf'){
                $value='C';
            }
            if($key=='refnfe'){
                $value= $arrnf['idnfe'];
            }
            if($key=='idpessoa'){
                $value= $idpessoa;
            }

            if(!empty($value) and $key!='idcontato' and $key!='idnfe'  and $key!='idpessoaform'  and $key!='idnf' and $key!='alteradoem' and $key!='dtemissao'  and $key!='idformapagamento' and $key!='alteradopor' and $key!='criadoem' and $key!='criadopor'){
                $insnf->$key=$value;
            }	    
        }
        $insnf->idobjetosolipor=$_idnf;
        $insnf->tipoobjetosolipor='nf';
        //print_r($insnf); die;
        $idnf=$insnf->save();

        //LTM - 05-04-2021: Insere o fluxo
        FluxoController::inserirFluxoStatusHist('nfentrada', $idnf, $idfluxostatus, 'APROVADO');

        reset($arrnf); 
        header('X-CB-PKID: '.$idnf);
        header('X-CB-PKFLD: idnf');
        
                
            $sqli="select i.*
                from nfitem i 
                where i.idnf=".$_idnf;
            $resi = d::b()->query($sqli) or die("saveprechange__nfentrada: Falha ao recuperar nfitem para nfentrada:\n".mysqli_error(d::b())."\n".$sqli);
            $arrColunasi = mysqli_fetch_fields($resi);
            $colid="idnfitem";
            while($ri = mysqli_fetch_assoc($resi)){
                foreach ($arrColunasi as $coli) {
                    $arrnfitem[$ri[$colid]][$coli->name]=$ri[$coli->name];
                }
            }
            
    

        cnf::gerarItemEntrada($arrnfitem,$idnf);
    // die();
            return $idnf;
        
    }

    static public function gerarItemEntrada($arrInsProd,$idnf){

       

        $arrNf=getObjeto("nf",$idnf);
     
    
            

            
       $i=99977;
       // LOOP NOS ITENS DO + DA TELA
       foreach($arrInsProd as $vidnfitem=>$v){
          //print_r($vidnfitem);die();
           $i=$i+1;
    
            $idprodserv=$v['idprodserv'];
            $idprodservformula=$v['idprodservformula'];
            $prodservdescr=$v['prodservdescr'];
                   
            if(!empty($idprodserv) and !empty($idnf)){
   
                $_sql = "select ps.*                                    
                                from prodserv ps 
                            where ps.idprodserv =".$idprodserv;
    
                //echo $_sql;	
                $res = d::b()->query($_sql) or die($_sql."Erro ao retornar produto: ".mysqli_error(d::b()));
                $qtdrows1= mysqli_num_rows($res);
          
        
            
                if($qtdrows1 > 0){	
                  
                    $row = mysqli_fetch_assoc($res);
                                        
                  
    
                //    $idtipoprodserv = traduzid('prodserv', 'idprodserv', 'idtipoprodserv', $idprodserv);
                   
                  
                    // montar o item para insert
    
                   if(empty($valor)){$valor=0.00;}
    
                    $infitem = new Insert();
                    $infitem->setTable("nfitem");
                    $infitem->qtd=$v["qtd"];    
                    $infitem->idprodserv=$idprodserv;     
                    if(!empty($idprodservformula)){
                        $infitem->idprodservformula=$idprodservformula;
                    } 
                    $infitem->idtipoprodserv=$v['idtipoprodserv'];
                    $infitem->idcontaitem=$v['idcontaitem'];
                    $infitem->idnf=$idnf;
                    $infitem->tiponf='C';
                    $infitem->ncm=$v["ncm"];
                    $infitem->idempresa=$arrNf['idempresafat'];
                    $infitem->aliqbasecal=$v["aliqbasecal"];               
                    $infitem->cest=$v["cest"];
                    $infitem->cprod=$v["codprodserv"];
                    $infitem->prodservdescr= $v["prodservdescr"];          
                    $infitem->un=$v["un"];
                    $infitem->aliqipi=$v["aliqipi"]; 
                    $infitem->aliqicms=$v["aliqicms"]; 
                    $infitem->aliqicmsint=$v["aliqicmsint"]; 
                    $infitem->aliqpis=$v["aliqpis"]; 
                    $infitem->aliqcofins=$v["aliqcofins"]; 
                    $infitem->iss=$v["iss"]; 
                    $infitem->finalidade=(string)$v["finalidade"];
                    $infitem->modbc=(string)$v["modbc"];
                    $infitem->origem=(string)$v["origem"];
                    $infitem->cst=(string)$v["cst"];
                    $infitem->piscst=(string)$v["piscst"];
                    $infitem->confinscst=(string)$v["confinscst"];
                    $infitem->ipint=(string)$v["ipint"];            
                    $infitem->vlrliq=$v["vlrliq"];  
                    $infitem->vlritem=$v['vlritem'];
                    $infitem->total=$v['total'];
                    $infitem->nfe=$row["nfe"];  
                       
                    }
                    $Nidnfitem=$infitem->save();

                    if(!empty($Nidnfitem)){

                        $sqlun="select idunidade 
                                        from unidade 
                                        where idempresa=".$arrNf['idempresafat']."
                                        and idtipounidade = 21 
                                        and status='ATIVO'";
                        $resun = d::b()->query($sqlun) or die("Erro ao buscar unidade : ". mysql_error() . "<p>SQL: ".$sqlun);  
                        $rowun=mysqli_fetch_assoc($resun);

                        $sqllote="select * from lotecons c where c.idobjeto=".$vidnfitem." and c.tipoobjeto='nfitem' and qtdd>0 and status='ABERTO'";
                        $reslote = d::b()->query($sqllote) or die("Erro ao buscar os consumos : ". mysql_error() . "<p>SQL: ".$sqllote);  

                        while($rowl=mysqli_fetch_assoc($reslote)){

                            $s="select * from lotefracao where idlote=".$rowl['idlote']." and idunidade=".$rowun['idunidade'];
                            $re = d::b()->query($s) or die("Erro ao buscar se ja existe fracao : ". mysql_error() . "<p>SQL: ".$s);  
                            $qtdr=mysqli_num_rows($re);
                            
                            if($qtdr>0){// se ja tiver fracao 
                                $rorig=mysqli_fetch_assoc($re);                          

                                $inslotecons = new Insert();
                                $inslotecons->setTable("lotecons");
                                $inslotecons->idlote=$rowl['idlote'];
                                $inslotecons->idlotefracao=$rorig['idlotefracao'];
                                $inslotecons->idempresa=$arrNf['idempresafat'];
                                $inslotecons->idobjeto=$Nidnfitem;
                                $inslotecons->tipoobjeto='nfitem';
                                $inslotecons->obs="Nota de Transferencia";
                                $inslotecons->qtdc=$rowl['qtdd'];                               
                                $inidlotecons=$inslotecons->save();

                                if(!empty($rowl['idobjetoconsumoespec'])){//insere
                                    $inslotecons = new Insert();
                                    $inslotecons->setTable("lotecons");
                                    $inslotecons->idlote=$rowl['idlote'];
                                    $inslotecons->idlotefracao=$rorig['idlotefracao'];
                                    $inslotecons->idempresa=$rowl['idempresa'];
                                    $inslotecons->idobjeto=$rowl['idobjetoconsumoespec'];
                                    $inslotecons->tipoobjeto='nfitem';
                                    $inslotecons->obs="Nota de Transferencia";
                                    $inslotecons->qtdd=$rowl['qtdd'];                               
                                    $inidlotecons=$inslotecons->save();

                                }
                                

                            }else{

                                $inslotefracao = new Insert();
                                $inslotefracao->setTable("lotefracao");
                                $inslotefracao->idunidade=$rowun['idunidade'];
                                $inslotefracao->qtd=0;
                                $inslotefracao->qtdini=0;
                                $inslotefracao->idempresa=$arrNf['idempresafat'];
                                $inslotefracao->idlote=$rowl['idlote'];
                                $inslotefracao->idlotefracaoorigem=$rowl['idlotefracao'];      
                                $_idlotefracao=$inslotefracao->save();


                                $inslotecons = new Insert();
                                $inslotecons->setTable("lotecons");
                                $inslotecons->idlote=$rowl['idlote'];
                                $inslotecons->idlotefracao=$_idlotefracao;
                                $inslotecons->idempresa=$arrNf['idempresafat'];
                                $inslotecons->idobjeto=$Nidnfitem;
                                $inslotecons->tipoobjeto='nfitem';
                                $inslotecons->obs="Nota de Transferencia";
                                $inslotecons->qtdc=$rowl['qtdd'];                               
                                $inidlotecons=$inslotecons->save();


                                if(!empty($rowl['idobjetoconsumoespec'])){//insere
                                    $inslotecons = new Insert();
                                    $inslotecons->setTable("lotecons");
                                    $inslotecons->idlote=$rowl['idlote'];
                                    $inslotecons->idlotefracao=$_idlotefracao;
                                    $inslotecons->idempresa=$rowl['idempresa'];
                                    $inslotecons->idobjeto=$rowl['idobjetoconsumoespec'];
                                    $inslotecons->tipoobjeto='nfitem';
                                    $inslotecons->obs="Nota de Transferencia";
                                    $inslotecons->qtdd=$rowl['qtdd'];                               
                                    $inidlotecons=$inslotecons->save();

                                }
                                


                            }

                        }
                                
                              
                        
                    }
                    //unset($_SESSION['arrpostbuffer']);                
                   // $_SESSION['arrpostbuffer']['1']['u']['nf']['idnf']=$idnf;
    
                }elseif(!empty($prodservdescr) and !empty($idnf)){//if(!empty($idprodserv) and !empty($idnf)){


                $infitem = new Insert();
                $infitem->setTable("nfitem");
               
                $infitem->qtd=$v["qtd"];
                $infitem->prodservdescr= $prodservdescr; 
                $infitem->idnf=$idnf;
                $infitem->idempresa=$arrNf['idempresafat'];
                $infitem->tiponf='C';
                $infitem->aliqicms=$v["aliqicms"]; 
                $infitem->aliqicmsint=$v["aliqicmsint"];                
                $infitem->vlritem=$v['vlritem'];
                $infitem->total=$v['total'];
              
                $Nidnfitem=$infitem->save();




            }
          
       } //foreach($arrInsProd as $k=>$v)
       
            
    }




    static public function  criarPedido($_idnf,$idnatop,$idpessoa,$inidnfitem)
    {    
        $sqle="select e.idendereco,concat(e.endereco,'-',e.uf) as endereco,
                            CASE
                                                    WHEN e.uf ='MG' THEN 'DENTRO'					
                                                    ELSE 'FORA'
                                            END as destino
                            from endereco e join tipoendereco t  on(t.idtipoendereco = e.idtipoendereco and t.faturamento='Y')
                            where e.idtipoendereco = 2 
                            and e.idpessoa=".$idpessoa."
                            and e.status='ATIVO' ;";
        $rese=d::b()->query($sqle) or die("nf/index.php: Erro ao buscar informacoes do endereço sql=".$sqle); 
        $rowe=mysqli_fetch_assoc($rese);
        
        $sqln="SELECT c.cfop
                FROM natop n  join cfop c on(c.idnatop = n.idnatop and c.origem='".$rowe['destino']."'  )
                    where n.status='ATIVO'
                    and n.finnfe=1
                    and n.idnatop=".$idnatop;
        $resn = d::b()->query($sqln) or die("saveprechange__nfentrada: Falha ao buscar cfop na devolução:\n".mysqli_error(d::b())."\n".$sqln);
        $rown=mysqli_fetch_assoc($resn);
        $_cfop=$rown['cfop'];
        
        $sql="select * from nf where idnf=".$_idnf;    
        $res = d::b()->query($sql) or die("saveprechange__nfentrada: Falha ao recuperar nf na devolução:\n".mysqli_error(d::b())."\n".$sql);
        $arrColunas = mysqli_fetch_fields($res);
        while($r = mysqli_fetch_assoc($res)){
            foreach ($arrColunas as $col) {
                $arrnf[$col->name]=$r[$col->name];
            }
        }

        //LTM - 05-04-2021: Retorna o Idfluxo nf para Pedido (Tipo V)
        $idfluxostatus = FluxoController::getIdFluxoStatus('pedido', 'FATURAR');
            
        //INSERIR A NF	
        $insnf = new Insert();
        $insnf->setTable("nf");	
        foreach ($arrnf as $key => $value) 
        {	    
        // echo "{$key} => {$value} ";
            if($key=='dtemissao'){
                $value=date("Y-m-d H:i:s");;		
            }
            if($key=='status'){
                $value="FATURAR";
            }
            if($key == 'idfluxostatus'){
                $value = $idfluxostatus;
            }
            if($key=='nnfe'){
                $value='';
            }
            if($key=='idnatop'){
                $value=$idnatop;
            }
            
            if($key=='idenderecofat'){
                $value=$rowe['idendereco'];
            }

            if($key=='idendrotulo'){
                $value=$rowe['idendereco'];
            }
            if($key=='tpnf'){
                $value='1';
            }
            
            if($key=='geracontapagar'){
                $value='N';
            }
            if($key=='finnfe'){
                $value='1';
            }
            
            if($key=='tiponf'){
                $value='V';
            }
            if($key=='refnfe'){
                $value= $arrnf['idnfe'];
            }
            if($key=='idpessoafat'){
                $value= $idpessoa;
            }
            if($key=='emaildadosnfe'){
                //PHOL - 27-06-2023: Alterar campo emaildadosnfe da nf para os emails da pessoa do faturamento
                $sql="SELECT 
                            p.emailxmlnfe AS emailxmlnfe,p.emailmat as emailmat
                        FROM
                            pessoa p
                        WHERE
                            p.idpessoa = ".$idpessoa."
                                AND p.status = 'ATIVO'";    
                $res = d::b()->query($sql) or die("saveprechange__nfentrada: Falha ao emails da nf na devolução:\n".mysqli_error(d::b())."\n".$sql);
                $virg = '';
                $value = '';
                while($r = mysqli_fetch_assoc($res)){
                    if (!empty($r['emailxmlnfe'])) {
                        $value .= $virg . $r['emailxmlnfe'];
                        $virg = ",";
                    }
                }
            }
            if($key=='idpessoa'){
                $value= $idpessoa;
            }

            if(!empty($value) and $key!='idcontato' and $key!='idnfe' and $key!='procolonfe' and $key!='xmlret' and $key!='xml' and $key!='recibo' and $key!='envionfe' and $key!='nnfe' and $key!='idnf' and $key!='alteradoem' and $key!='dtemissao'  and $key!='idformapagamento' and $key!='alteradopor' and $key!='criadoem' and $key!='criadopor'){
                $insnf->$key=$value;
            }	    
        }
        $insnf->idobjetosolipor=$_idnf;
        $insnf->tipoobjetosolipor='nf';
        //print_r($insnf); die;
        $idnf=$insnf->save();

        //LTM - 05-04-2021: Insere o fluxo
        FluxoController::inserirFluxoStatusHist('pedido', $idnf, $idfluxostatus, 'FATURAR');

        reset($arrnf); 
        header('X-CB-PKID: '.$idnf);
        header('X-CB-PKFLD: idnf');
        
        
        $idnfitem=explode(",", $inidnfitem);
        foreach ($idnfitem as  $value) {
        
            $sqli="select *
                from nfitem i 
                where i.idnfitem=".$value;
            $resi = d::b()->query($sqli) or die("saveprechange__nfentrada: Falha ao recuperar nfitem para devolução:\n".mysqli_error(d::b())."\n".$sqli);
            $arrColunasi = mysqli_fetch_fields($resi);
            $colid="idnfitem";
            while($ri = mysqli_fetch_assoc($resi)){
                foreach ($arrColunasi as $coli) {
                    $arrnfitem[$ri[$colid]][$coli->name]=$ri[$coli->name];
                }
            }
            
       /*
            Array (
                [11] => Array ( [idnfitem] => [ord] => [quantidade] => 1 [idprodservformula] => [prodservdescr] => [idprodserv] => 3521 ) 
                [12] => Array ( [idnfitem] => [ord] => [quantidade] => 20 [idprodservformula] => [prodservdescr] => [idprodserv] => 5753 )
             )
        */
        
        }

       

        cnf::gerarItemPedidoTransferencia($arrnfitem,$idnf);
    // die();
            return $idnf;
        
    }

    static public function corrigeRateio(){
        $sqli="select u.idnf from (
                                    select i.idnf,d.idrateioitemdest,d.idrateioitem,d.idobjeto,d.tipoobjeto,sum(valor)
                                    from nfitem i join rateioitem ri on(ri.idobjeto = i.idnfitem)
                                    join rateioitemdest d on(d.idrateioitem = ri.idrateioitem)
                                    where d.criadoem > DATE_SUB(now(), INTERVAL 60 DAY)  
                                    and d.status='PENDENTE'
                                 group by d.idrateioitem,d.idobjeto,d.tipoobjeto HAVING COUNT(*)>1) as  u group by u.idnf";

        $resi = d::b()->query($sqli) or die("Erro ao buscar rateios incorretos \n".mysqli_error(d::b())."\n".$sqli);
        while($rowi=mysqli_fetch_assoc($resi)){

            $sql="select d.idrateioitemdest,d.idrateioitem,d.idobjeto,sum(valor) as valor
                        from nfitem i join rateioitem ri on(ri.idobjeto = i.idnfitem)
                        join rateioitemdest d on(d.idrateioitem = ri.idrateioitem)
                        where i.idnf = ".$rowi['idnf']." 
                        and d.status='PENDENTE'
                        group by d.idrateioitem,d.idobjeto";
            $res = d::b()->query($sql) or die("Erro ao buscar rateios incorretos por nota \n".mysqli_error(d::b())."\n".$sql);

            $arridrateioitemdest= array();  

            while($row=mysqli_fetch_assoc($res)){

                if($row['valor']>100){
                    $row['valor']=100;
                }

                $sqlu="update rateioitemdest set valor=".$row['valor']." where idrateioitemdest=".$row['idrateioitemdest'];
                $resu = d::b()->query($sqlu) or die("Erro ao atualizar rateios incorretos \n".mysqli_error(d::b())."\n".$$sqlu);

                array_push($arridrateioitemdest,$row['idrateioitemdest']);          

            }

            if(count($arridrateioitemdest)>0){

                $stidrateioitemdest = implode(",", $arridrateioitemdest);

                if(!empty($stidrateioitemdest)){
                    $sql="delete d.*
                    from nfitem i join rateioitem ri on(ri.idobjeto = i.idnfitem)
                    join rateioitemdest d on(d.idrateioitem = ri.idrateioitem)
                    where i.idnf = ".$rowi['idnf']."
                    and d.status='PENDENTE'
                    and d.idrateioitemdest not in (".$stidrateioitemdest .")";
                    $res = d::b()->query($sql) or die("Erro ao buscar rateios incorretos por nota \n".mysqli_error(d::b())."\n".$sql);
                }//if(!empty($stidrateioitemdest)){
             

            }//if(count($arridrateioitemdest)>0){


        }// while($rowi=mysqli_fetch_assoc($resi)){
    }//corrigeRateio(){





//ao limpar filial ele insere novamente os impostos
        static public function impostoItemPedido($idnf){

       

            $arrNf=getObjeto("nf",$idnf);
            $idnatop = $arrNf["idnatop"];
    
            $_idempresa=$arrNf['idempresa'];
           
        
            $ufemp=traduzid("empresa","idempresa","uf",$_idempresa);
            
            if(empty($idnatop)){
                 die("Obrigatório informar natureza da informação.");
            }
            //busca endereco do cliente ou fornecedor tipoendereco = sacado		
            $sqlp = "select e.uf,p.inscrest,ep.indiedest as indiedestempresa ,p.indiedest,p.idpessoa,f.tpnf,p.vendadireta,f.idvendedor,
                 (select 1 from natop nt where  nt.natop like('%DEVOLU%') and nt.idnatop=f.idnatop ) as natopdev,
                (select 1 from natop nt where  nt.natop like('%OUTRA%') and nt.natop like('%SAIDA%')  and nt.idnatop=f.idnatop ) as natoprem
                from nf f,pessoa p, endereco e,empresa ep
            where p.idpessoa = f.idpessoafat
            and ep.idempresa=f.idempresa
            and e.idendereco = f.idenderecofat and f.idnf = ".$idnf;
            $resp = d::b()->query($sqlp) or die("Erro ao buscar endereco: ".mysqli_error(d::b()));
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
        
                                     
                    if($uf != $ufemp){
                        $sc="SELECT cfop FROM cfop where status='ATIVO' and idnatop = ".$idnatop." and origem ='FORA' limit 1";
                       
                    }else{
                        $sc="SELECT cfop FROM cfop where status='ATIVO' and idnatop = ".$idnatop." and origem ='DENTRO' limit 1";
                    
                    }
                    $rc=d::b()->query($sc);
                    $rwc=mysqli_fetch_assoc($rc);
                    $cfop=$rwc['cfop'];
                
            }else{
                    die("Não foi possivel identificar o estado UF do cliente!!!");
            }
                
           $i=99977;
           // LOOP NOS ITENS DO + DA TELA

            $sqlitem="select * from nfitem where nfe='Y' and idnf=".$idnf;
            $resitem=d::b()->query($sqlitem);
           

            while($v=mysqli_fetch_assoc($resitem)){
              // print_r($v);die();
               $i=$i+1;
        
                $idprodserv=$v['idprodserv'];             
                $prodservdescr=$v['prodservdescr'];
                       
                if(!empty($idprodserv) and !empty($idnf)){
       
                    $_sql = "select ps.*                                    
                                    from prodserv ps 
                                where ps.idprodserv =".$idprodserv;
        
                    //echo $_sql;	
                    $res = d::b()->query($_sql) or die($_sql."Erro ao retornar produto: ".mysqli_error(d::b()));
                    $qtdrows1= mysqli_num_rows($res);
              
            
                
                    if($qtdrows1 > 0){	
                      
                        $row = mysqli_fetch_assoc($res);
                                             
        
                        if($ufemp=='PR' and  $uf=='PR' ){// se for da filial do parana @838527
                            if($rowuf['indiedest']==9){// Cliente não contribuinte                            
                                $row['cst']=00;
                            }else{// Cliente  contribuinte e isento
                                $row['cst']=51;
                            }
                        }elseif($ufemp==$uf /*or $rowuf['indiedest']==1*/){
                                if($row['isentomesmauf']=='N' and  $row['cst']==20){
                                    $rowaliq["aliq"]=$rowaliq['aliqicmsint'];
                                }
                                if($row['cst']!=60 and $row['cst']!=41 and $row['cst']!=00  and $row['isentomesmauf']=='Y'){// se for 60 e devolução e deve permanecer o 60
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
                                
                      
                                    
                        $indiedest=0;
                        if($rowuf['indiedest'] == 9 and $rowuf['indiedestempresa'] !=2 and $uf !=$ufemp and $rowuf['tpnf']==1 and  empty($rowuf['natopdev']) ){                             
                            $indiedest=$rowuf["indiedest"];   
                            if(!empty($rowuf['natoprem'])){
                                if(!empty($cfop)){						   
                                    $cfop=$cfop;
                                }
                            }else{
                                  
                                $cfop='6107';  
                            }
                        }elseif(!empty($cfop)){
                            $cfop=$cfop; 
                        }        
                                              
                    $sqliu="UPDATE nfitem 
                    SET 
                        cst = '".$row["cst"]."',
                        frete='0.00',
                        cfop='".$cfop."',                      
                        aliqbasecal = '".$row["reducaobc"]."',
                        aliqicms = '".$rowaliq["aliq"]."',
                        aliqipi = '".$row["ipi"]."',
                        aliqicmsint='".$rowaliq["aliqicmsint"]."',
                        aliqpis='".$row["pis"]."',
                        aliqcofins='".$row["cofins"]."',
                        iss='".$row["iss"]."',                       
                        cst='".$row["cst"]."',
                        piscst='".$row["piscst"]."',
                        confinscst='".$row["confinscst"]."',
                        indiedest='".$indiedest."',
                        ipint='".$row["ipint"]."'   
                    WHERE
                        idnfitem = ".$v['idnfitem'];

                    $res = d::b()->query($sqliu) or die("erro ao voltar impostos dos itens para faturar sem filial: " . mysqli_error(d::b()) . "<p>SQL: ".$sqliu);
                                    
                           
                    }else{
                        die("Não encontrada configuração do produto!!!!");
                    }
                }elseif(!empty($prodservdescr) and !empty($idnf)){//if(!empty($idprodserv) and !empty($idnf)){                   
                
                  
                    $indiedest=0;   
                    if($rowuf['indiedest'] == 9 and $uf !=$ufemp and $rowuf['tpnf']==1 and  empty($rowuf['natopdev']) ){
                        $indiedest=$rowuf["indiedest"];   
                        if(!empty($rowuf['natoprem'])){
                            $cfop=$cfop;
                        }else{
                            $cfop='6107';   
                        }
                    }else{
                        $cfop=$cfop;
                    }
                                 
                    $sqliu="UPDATE nfitem 
                    SET                       
                        cfop='".$cfop."',   
                        aliqicms = '".$rowaliq["aliq"]."',                     
                        aliqicmsint='".$rowaliq["aliqicmsint"]."',                     
                        indiedest='".$indiedest."' 
                    WHERE
                        idnfitem = ".$v['idnfitem'];

                    $res = d::b()->query($sqliu) or die("erro ao voltar impostos dos itens descritivos para faturar sem filial: " . mysqli_error(d::b()) . "<p>SQL: ".$sqliu);
    
    
                }
              
           } //foreach($arrInsProd as $k=>$v)
           
                
        }




}//fim cnf




?>