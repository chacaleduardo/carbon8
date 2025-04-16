<?
require_once(__DIR__."/../../form/controllers/fluxo_controller.php");
require_once(__DIR__."/../../form/controllers/pedido_controller.php");

require_once("../api/nf/index.php");

cnf::$idempresa = cb::idempresa();

//print_r($_POST);
$idnotafiscal = $_POST["_1_u_notafiscal_idnotafiscal"];
$idpessoa = $_POST["_1_u_notafiscal_idpessoa"];
$qtdparcelas = $_POST["_1_u_notafiscal_qtdparcelas"];
$diasentrada = $_POST["_1_u_notafiscal_diasentrada"];
$total = $_POST["_1_u_notafiscal_total"];   
$parcelamento = $_POST["_1_u_notafiscal_parcelamento"];
$emissao = $_POST["_1_u_notafiscal_emissao"];
$formapgto = $_POST["_1_u_notafiscal_formapgto"];
$intervalo = $_POST["_1_u_notafiscal_intervalo"];
//$idagencia = $_POST["_1_u_notafiscal_idagencia"];
$idformapagamento = $_POST["_1_u_notafiscal_idformapagamento"];
$status = $_POST["_1_u_notafiscal_status"];
$nnfe = $_POST["_1_u_notafiscal_nnfe"];
$geracontapagar=$_POST["_1_u_notafiscal_geracontapagar"];
$envionfs=$_POST["envionfs"];
$tipo="C";
$difdias = 0;
//colocado a pedido do fabio
$diasentrada=$diasentrada;
$intervalo=$intervalo-1;

//inserção de uma nova parcela
$_datapagto = $_SESSION['arrpostbuffer']['x9']['i']['contapagaritem']['datapagto'];

if(!empty($_datapagto)){	
	cnf::agrupaCP();	
}

//SE FOR GERA PARCELA NÃO DELETA AS PARCELAS
IF((!empty($idnotafiscal)) AND $geracontapagar=='N'){     
	
	/*
	* verifica se existe alguma parcela item quitada. se existir, nao alterar nada.
	*/
	$sqlveritem = "select count(*) as quant from contapagaritem where  idobjetoorigem= ".$idnotafiscal." and tipoobjetoorigem = 'notafiscal' and status in('QUITADO')";
	
	$resveritem = d::b()->query($sqlveritem) or die($sql);
	$rowqtd=mysqli_fetch_assoc($resveritem );
	$qtParcelasitem = $rowqtd['quant'];

    $arrParcelas=recuperaParcelas($idnotafiscal,'QUITADO','notafiscal');//Contapagar Quitado
    $qtParcelas =$arrParcelas['quant'];
    
    if($qtParcelasitem==0 and $qtParcelas==0 ){
    	deletaParcelasExistentes($idnotafiscal,'notafiscal');
    }  
}

//se cancelar a note deve excluir as guias de imposto
if(!empty($idnotafiscal)){
	$status=traduzid('notafiscal','idnotafiscal','status',$idnotafiscal);
	//die($status);
	if($status=='CANCELADO' or $geracontapagar=='N'){
		
		$sql="select i.* from nfitem i join nf n on(n.idnf=i.idnf)
		join contapagaritem ci on(ci.idobjetoorigem = n.idnf and ci.tipoobjetoorigem ='nf' and ci.status!='QUITADO')
						where i.idobjetoitem =".$idnotafiscal." 
						and i.tipoobjetoitem = 'notafiscal'";

		$resf=d::b()->query($sql) or die("[nfcancel] - Erro 1 ao buscar se ja exite o item do imposto sql=".$sql." mysql".mysqli_error(d::b()));      
		$Vqtdnfitem=mysqli_num_rows($resf);
		if($Vqtdnfitem>0){
			while($row=mysqli_fetch_assoc($resf)){
				$sd="delete from nfitem where idnfitem=".$row['idnfitem'];
				$rd=d::b()->query($sd) or die('[poschange_pedido] - Falha ao excluir nf item imposto sql='.$sd);
				atualizavalornf($row['idnf']);
				atualizafat($row['idnf']);
			}
		}
	}
}



//die($idnotafiscal." ".$status);
//if (!empty($idnotafiscal) and $status!="FATURADO" and empty($nnfe)){
if($geracontapagar=="Y" and $envionfs=='Y'){

/*
 * verifica se existe alguma parcela quitada. se existir, nao alterar nada.
 */
$sqlverifquit = "select count(*) as quant from contapagar where status = 'QUITADO' and tipoobjeto='notafiscal' and idobjeto = ".$idnotafiscal;
//echo $sqlverifquit;
$resverif = d::b()->query($sqlverifquit) or die($sqlverifquit."Erro ao consultar parcelas da nota: <br>".mysqli_error(d::b()));
$rowverif = mysqli_fetch_array($resverif);

$qtlinhas = $rowverif["quant"];

//echo "qt:".$qtlinhas; 

/*
 * verifica se a nota não esta cancelada
 */
$sqlverifnf = "select count(*) as nfcancel from notafiscal where status='CANCELADO' and  idnotafiscal = ".$idnotafiscal;
//echo $sqlverifquit;
$resverifnf = d::b()->query($sqlverifnf) or die($sqlverifquit."Erro ao consultar parcelas da nota: <br>".mysqli_error(d::b()));
$rowverifnf = mysqli_fetch_array($resverifnf);
$qtlinhasnfcancel = $rowverifnf["nfcancel"];



//echo "qt nf cancel:".$qtlinhasnfcancel; 

if ($qtlinhas == 0 and $qtlinhasnfcancel == 0){
    
        $tmpsqldel = "delete from contapagaritem where tipoobjetoorigem =  'notafiscal' and idobjetoorigem = ".$idnotafiscal."  and status in ('ABERTO','INICIO','PENDENTE')";
	d::b()->query($tmpsqldel) or die("Erro ao retirar parcelas da Nota Fiscal: <br>".mysqli_error(d::b()));
	
	/*
	 * deleta as parcelas existentes.
	 */
	$tmpsqldel = "delete from contapagar where tipoobjeto = 'notafiscal' and idobjeto = ".$idnotafiscal;
	d::b()->query($tmpsqldel) or die("Erro ao retirar parcelas da Nota Fiscal: <br>".mysqli_error(d::b()));
    
	$sqlcx="select n.proporcional,c.* 
		from nfsconfpagar c 
		join notafiscal n on(n.idnotafiscal=c.idnotafiscal) 
	where c.idnotafiscal=".$idnotafiscal." and datareceb is not null";
	$rescx=d::b()->query($sqlcx) or die("Falha ao buscar configurações das parcelas sql=".$sqlcx);
	$qtdparcelas= mysqli_num_rows($rescx);

	$sqlct="select n.proporcional,sum(c.proporcao) as proporcao 
	from  nfsconfpagar c 
		join notafiscal n on(n.idnotafiscal=c.idnotafiscal) 
	where c.idnotafiscal=".$idnotafiscal;
	//die($sqlct);
	$resct=d::b()->query($sqlct) or die("Falha ao buscar configurações das parcelas sql=".$sqlct);
	$rowct=mysqli_fetch_assoc($resct);
	if($rowct['proporcao']!=100 and $rowct['proporcional']=='Y'){
		die("A soma das proporções deve ser 100!!! Verificar a proporção das faturas.");
	}

       
	/*
	 * Insere novas parcelas.
	 * 
	 */
	/*
	$total=tratanumero($total);
	$valorparcela = $total/$qtdparcelas;
	*/
	if (empty($diasentrada)or ($diasentrada == ' ') ){
		$diasentrada = '0';		
	}	
	//die($index." ".$qtdparcelas);
        //BUSCAR CONFIGURAÇÕES DA FORMA DE PAGAMENTO
        $sf="select idagencia,agruppessoa,agrupado,agrupfpagamento from formapagamento where idformapagamento=".$idformapagamento;
        $rf=d::b()->query($sf) or die("Erro ao buscar configuração da forma de pagamento: sql=".$sf." mysql".mysqli_error(d::b()));
        $formapagamento=mysqli_fetch_assoc($rf);
		
	//for ($index = 1; $index <= $qtdparcelas; $index++) {
	$index = 0;
	while($rowcx=mysqli_fetch_assoc($rescx)){
		$index++;

		  //Insere novas parcelas
		  if($rowcx['proporcional']=='Y'){
			$valorparcela = $total*($rowcx['proporcao']/100);

		}else{
			$valorparcela = $total/$qtdparcelas;			
		}

				$vencimentocalc = $rowcx['datareceb'];
				$recebcalc = $rowcx['datareceb'];
				
				if($index == 1){
					
					$sqlu = "update notafiscal set vencimento = '".$vencimentocalc."'	 
							where idnotafiscal = ".$idnotafiscal." 
							".getidempresa('idempresa','nfs').";";
					
					$qru = d::b()->query($sqlu) or die($sqlu."Erro ao inserir a data de vencimento da nota fiscal".mysqli_error(d::b()));					
					
				}


                if($formapagamento['agrupado']=='Y'){//se for agrupado
                  
                    $tmpsqlins = "INSERT INTO contapagaritem (idempresa,status,idpessoa,idobjetoorigem,tipoobjetoorigem,tipo,visivel,idformapagamento,parcela,parcelas,datapagto,valor,criadopor,criadoem,alteradopor,alteradoem)
                        VALUES (".cb::idempresa().",'PENDENTE',".$idpessoa.",".$idnotafiscal.",'notafiscal','C','S',".$idformapagamento.",".$index.",".$qtdparcelas.",'".$recebcalc."',".$valorparcela.",'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate(),'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate())";

                    //Insere a parcela
                    $resi=d::b()->query($tmpsqlins) or die("Erro ao inserir parcelas da Nota Fiscal: <br>".mysqli_error(d::b())." sql=".$tmpsqlins);
                             
                    
                }else{
					//LTM - 31-03-2021: Retorna o Idfluxo ContaPagar
					$idfluxostatus = FluxoController::getIdFluxoStatus('contapagar', 'PENDENTE');

                    $tmpsqlins = "INSERT INTO contapagar (idempresa,idformapagamento,idpessoa,tipoobjeto,idobjeto,visivel,parcela,parcelas,valor,datapagto,datareceb,status, idfluxostatus, tipo,intervalo,criadopor,criadoem)
                     VALUES (".cb::idempresa().",".$idformapagamento.",".$idpessoa.",'notafiscal',".$idnotafiscal.",'S',".$index.",".$qtdparcelas.",".$valorparcela.",'".$vencimentocalc."','".$recebcalc."','PENDENTE', $idfluxostatus, '".$tipo."',".$intervalo.",'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate())";
                    //echo "<br>".$tmpsqlins;
                    //echo "<br>".$sqlupdatevcto;
                    //die($tmpsqlins);
                    d::b()->query($tmpsqlins) or die("Erro ao inserir parcelas da Nota Fiscal: <br>".mysqli_error(d::b()));

					//LTM - 31-03-2021: Insere o FluxoHist para ContaPagar
					if(!empty($idfluxostatus))
					{
						$idcontapagar = mysqli_insert_id(d::b());
						FluxoController::inserirFluxoStatusHist('contapagar', $idcontapagar, $idfluxostatus, 'PENDENTE');
					}
                }
		
	}
    
	//agrupaContapagar($fluxo); 

	$comissao=traduzid('notafiscal','idnotafiscal','comissao',$idnotafiscal);
	d::b()->query("COMMIT") or die("Erro");
	cnf::agrupaCP();
	if($comissao=='Y'){//inserir o item da comissao oculto na nota
		geracomissao($idnotafiscal);
		cnf::agrupaCP();
	}

	

	
	//corrigir parcelas em um centavo
	$sqls="select sum(valor) as vvalor,max(parcela) as mparcela from contapagar where idobjeto=".$idnotafiscal." and tipoobjeto='notafiscal' ".getidempresa('idempresa','nfs').";";
	$ress=d::b()->query($sqls) or die("Erro ao somar valor das parcelas sql=".$sqls);
	$rows=mysqli_fetch_assoc($ress);
	if($rows['vvalor']!=$total){
		if($rows['vvalor']>$total){
			$sqlup="update contapagar set valor=valor-0.01
					where idobjeto=".$idnotafiscal."
					and tipoobjeto='notafiscal'
					and parcela = 1
					 ".getidempresa('idempresa','contapagar').";";
		}elseif($rows['vvalor']<$total){
			$sqlup="update contapagar set valor=valor+0.01
					where idobjeto=".$idnotafiscal."
					and tipoobjeto='notafiscal'
					and parcela = ".$rows['mparcela']."
					 ".getidempresa('idempresa','contapagar').";";
		}
		if(!empty($sqlup)){
			d::b()->query($sqlup) or die("erro ao atualizar parcelas");
				
		}
	}

}
		//$tmpsqlins = "INSERT INTO notafiscalparcela(idnotafiscal,parcela,valor,datapagto,status) VALUES (".$idnotafiscal.",".$index.",".$valorparcela.",".$vencimentocalc.",'PENDENTE')";
	//	echo "<br>".$tmpsqlins;

}

//ATUALIZAR VALOR DA FATURA
if(!empty($_SESSION['arrpostbuffer']['atitem']['u']['contapagaritem']['idcontapagaritem']))
{

   $idcontapagaritem = $_SESSION['arrpostbuffer']['atitem']['u']['contapagaritem']['idcontapagaritem'];

    $rowv = PedidoController::buscarFormaPagamentoPorParcela($idcontapagaritem);
    

    if($rowv['agrupado']=='Y' and $rowv['formapagamento'] !='C.CREDITO' and  ( $rowv['formapagamento']!='BOLETO' or  $rowv['agruppessoa'] !='Y')){      
           
    if(!empty($rowv['idcontapagar'])){

        $rowvalor = PedidoController::somarValorParcelasPorFatura($rowv['idcontapagar']);

        PedidoController::AtualizaValorFatura($rowv['idcontapagar'],$rowvalor['nvalor']);   
                   
               
     }
    }        
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
				//FluxoController::inserirFluxoStatusHist('contapagar', $idnfcp, $idfluxostatus, 'PENDENTE');
				$modulo='contapagar';
				$sqlEtapaHistInsert = "INSERT INTO fluxostatushist (idempresa, idfluxostatus, idmodulo, modulo, status, criadopor, criadoem, alteradopor, alteradoem) 
				VALUES (".$row["idempresa"].", '$idfluxostatus', '$idnfcp', '". $modulo."', 'ATIVO', 'cron', now(), 'cron', now())"; 
				$res = d::b()->query($sqlEtapaHistInsert);
			}            

		}//for ($index = 1; $index <= $qtdparcelas; $index++) {
	   
	}
}//function gerarContapagaritem($idnotafiscal){



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
                        and c.idempresa = ".$row["idempresa"]."
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
                        and c.idempresa = ".$row["idempresa"]."
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
                        $idfluxostatus = getId_FluxoStatus('contapagar', 'ABERTO');
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
                        $idfluxostatus =getId_FluxoStatus('contapagar', 'ABERTO');
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
                                            where idcontapagaritem =".$row['idcontapagaritem']."  and idempresa = ".$row["idempresa"]."";
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

function recuperaParcelas($inidobj,$instatus,$intipoobjeto){
        
	/*
	* verifica se existe alguma parcela quitada. se existir, nao alterar nada.
	*/
	$sqlverifquit = "select count(*) as quant from contapagar where status = '".$instatus."'    and tipoobjeto='".$intipoobjeto."' and idobjeto = ".$inidobj;

	$resverif = d::b()->query($sqlverifquit) or die($sqlverifquit."Erro ao consultar parcelas da nota: <br>".mysqli_error(d::b()));
	$rowverif = mysqli_fetch_array($resverif);

	return  $rowverif;
}
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

function deletaParcelasExistentes($idnotafiscal,$tipoobjeto='nf'){
	/*
	* deleta as parcelas existentes.
	*/
		$tmpsqldel = "delete cc.* 
						from contapagar c,contapagaritem cc
						where c.tipoobjeto = '".$tipoobjeto."' 
						and c.idobjeto =".$idnotafiscal."
						and cc.idobjetoorigem = c.idcontapagar
			and cc.tipoobjetoorigem ='contapagar'
			and cc.status in ('ABERTO','PENDENTE')";
			//echo('1:'.$tmpsqldel."<br>");
	d::b()->query($tmpsqldel) or die("Erro ao retirar parcelas de comissão da Nota Fiscal : <br>".mysqli_error(d::b()));


	//if($contapagaritem=="Y"){
	$tmpsqldel = "delete from contapagaritem where tipoobjetoorigem =  '".$tipoobjeto."' and idobjetoorigem = ".$idnotafiscal."  and status in ('ABERTO','PENDENTE')";
	//echo('2:'.$tmpsqldel."<br>");
	d::b()->query($tmpsqldel) or die("Erro ao retirar parcelas da Nota Fiscal: <br>".mysqli_error(d::b()));
	//}
		
	$tmpsqldel = "delete c.* from contapagar c 
		where c.tipoobjeto =  '".$tipoobjeto."'
		and c.status!='QUITADO'
				and not exists(select 1 from contapagaritem i where i.idcontapagar=c.idcontapagar and i.tipoobjetoorigem='contapagar')
		and c.idobjeto = ".$idnotafiscal;
	   // echo('3:'.$tmpsqldel."<br>");
	d::b()->query($tmpsqldel) or die("Erro ao retirar parcelas da Nota Fiscal: <br>".mysqli_error(d::b()));
		
}


function geracomissao($idnotafiscal){  

    $arrNF=getObjeto("notafiscal",$idnotafiscal,"idnotafiscal");

    //BUSCAR CONFIGURAÇÕES DA FORMA DE PAGAMENTO
    $sf="select idagencia,agruppessoa,agrupado,agrupfpagamento,agrupnota 
        from formapagamento 
        where idformapagamento=".$arrNF['idformapagamento'];
    $rf=d::b()->query($sf) or die("Erro ao buscar configuração da forma de pagamento: sql=".$sf." mysql".mysqli_error(d::b()));
    $formapagamento=mysqli_fetch_assoc($rf);

    $sc="select c.idpessoa,round((sum(((i.valor - round((i.valor * (i.desconto / 100)),2)) * (i.quantidade)))-(n.subtotal-n.total))*(c.pcomissao/100),2) as comissao
            from notafiscalitens i 
				join notafiscalitenscomissao c on(c.idnotafiscalitens = i.idnotafiscalitens)
            	join notafiscal n on(n.idnotafiscal=i.idnotafiscal)
			where  i.idnotafiscal=".$idnotafiscal." group by c.idpessoa";
    $rc=d::b()->query($sc) or die("erro ao buscar valores da comissão sql=".$sc);
    while($rwc= mysqli_fetch_assoc($rc)){
       
        if($formapagamento['agrupado']=='Y'){//se for agrupado
            $si="select DATE_ADD(datapagto, INTERVAL 1 DAY) as vdatapagto,nc.proporcao,i.* 
                    from contapagaritem i 
					left join nfsconfpagar nc on (nc.idnotafiscal=i.idobjetoorigem and nc.datareceb=i.datapagto )
                    where i.idobjetoorigem = ".$idnotafiscal." 
                    and i.tipoobjetoorigem='notafiscal' 
                    and i.status !='INATIVO'";        
        
        }else{//if($formapagamento['agrupado']=='Y'){
            $si="select c.datareceb as vdatapagto,c.* 
                    from contapagar c 
                    where c.idobjeto=".$idnotafiscal." 
                    and c.tipoobjeto='notafiscal'  
                    and c.status !='INATIVO'";           
        }//else($formapagamento['agrupado']=='Y'){
			

        $ri=d::b()->query($si) or die("erro ao buscar parcelas da nota sql=".$si);
        $qtdparc=mysqli_num_rows($ri);
        $valor=$rwc['comissao']/$qtdparc;
       
        while($rwi=mysqli_fetch_assoc($ri)){
			if(empty($rwi['proporcao'])){
                $valor=$rwc['comissao']/$qtdparc;
            }else{
                $perc=$rwi['proporcao']/100;
                $valor=$rwc['comissao']*$perc;
            }
			
                comissao($rwi['idcontapagar'],$valor,$rwi['parcela'],$rwi['parcelas'],$rwi['vdatapagto'],$rwc['idpessoa']);  
        }
    }//while($rwc= mysqli_fetch_assoc($rc)){
}//function geracomissao($idnf){

function  comissao($idcontapagar,$valorn,$parcela,$parcelas,$datapagto,$idpessoa){
	

		$_idempresa= traduzid("contapagar","idcontapagar","idempresa",$idcontapagar);

		$arrconfCP=getDadosConfContapagarServ('COMISSAO',$_idempresa);
		$visivel='S';
	
	
		$tmpsqlins = "INSERT INTO contapagaritem (idempresa,status,idpessoa,idobjetoorigem,tipoobjetoorigem,idformapagamento,tipo,parcela,parcelas,datapagto,valor,visivel,criadopor,criadoem,alteradopor,alteradoem)
		VALUES (".cb::idempresa().",'ABERTO',".$idpessoa.",".$idcontapagar.",'contapagar',".$arrconfCP['idformapagamento'].",'D',".$parcela.",".$parcelas.",'".$datapagto."',".$valorn.",'".$visivel."','".$_SESSION["SESSAO"]["USUARIO"]."',sysdate(),'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate())";
		$resi=d::b()->query($tmpsqlins) or die("Erro ao inserir parcelas do representante: sql=".$tmpsqlins." mysql".mysqli_error(d::b()));
	
		//echo "<br>".$tmpsqlins; die;
		if(!$resi){
				d::b()->query("ROLLBACK;");
				die("1-Falha ao gerar parcela do representante: " . mysql_error() . "<p>SQL: ".$tmpsqlins);
		}
	
	
	
}


function getDadosConfContapagarServ($tipo,$_idempresa){
	$sqlrep = "select * from confcontapagar where status='ATIVO' and idempresa = ".$_idempresa." and tipo='".$tipo."'";
	$resrep = d::b()->query($sqlrep) or die("A Consulta de configuração automatica contapagar falhou :".mysqli_error()."<br>Sql:".$sqlrep);
	$qtdresp = mysqli_num_rows($resrep); 
	
	if($qtdresp<1){
		die('Não encontrada a configuração para a parcela automatica '.$tipo);
	}
   
	$rowrep= mysqli_fetch_assoc($resrep);
		 
	return $rowrep;    
}


?>
