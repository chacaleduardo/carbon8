<?
require_once(__DIR__."/../../form/controllers/fluxo_controller.php");

require_once("../api/nf/index.php");

cnf::$idempresa = isset($_GET["_idempresa"])?$_GET["_idempresa"]:$_SESSION["SESSAO"]["IDEMPRESA"];

//Trecho de codigo para gerar as parcelas INICIO
$iu = $_SESSION['arrpostbuffer']['1']['u']['contapagar']['idcontapagar'] ? 'u' : 'i';

if($iu == "i" and (!empty($_POST["_1_i_contapagar_parcelas"]))){

	$_parcelas = $_POST["_1_i_contapagar_parcelas"];
	//$geracontapagar = $_POST["_1_u_nf_geracontapagar"];
	$idcontadesc = $_POST["_1_i_contapagar_idcontadesc"];
	$tipoespecifico= $_POST["_1_i_contapagar_tipoespecifico"];
	$idpessoa = $_POST["_1_i_contapagar_idpessoa"];
	$idcontaitem = $_POST["_1_i_contapagar_idcontaitem"];
	$datapagto = $_POST["_1_i_contapagar_datapagto"];
	$datareceb = $_POST["_1_i_contapagar_datareceb"];
	$tipointervalo = $_POST["_1_i_contapagar_tipointervalo"];
	$intervalo = $_POST["_1_i_contapagar_intervalo"];
	$valor =$_POST["_1_i_contapagar_valor"];
	$obs = $_POST["_1_i_contapagar_obs"];
	$tipo = $_POST["_1_i_contapagar_tipo"];
	$status = $_POST["_1_i_contapagar_status"];
	$idformapagamento = $_POST["_1_i_contapagar_idformapagamento"];
	$formapagto = $_POST["_1_i_contapagar_formapagto"];
	$visivel = $_POST["_1_i_contapagar_visivel"];
	$criadopor = $_POST["_1_i_contapagar_criadopor"];
	$progpagamento = $_POST["_1_i_contapagar_progpagamento"];
	
	

	//echo("Parcelas:".$_parcelas);
	
	//die($_parcelas);
	
	//if($_parcelas > 1 and $geracontapagar!="N"){
	if($_parcelas > 1){
		d::b()->query("START TRANSACTION") or die("presavecontapagar: Falha 2 ao abrir transacao: ".mysqli_error(d::b()));
		
		$vintervalo = 0;
		for($i = 2; $i <= $_parcelas; $i++){
			$vintervalo = $vintervalo +  $intervalo;			
			if($tipointervalo=="M"){
				$strintervalo= 'MONTH';
			}elseif($tipointervalo=="Y"){
				$strintervalo= 'YEAR';
			}else{
				$strintervalo= 'DAY';
			}
			// contasapagar terá so a data de recebimento
			$vencimentocalc = "DATE(DATE_ADD(STR_TO_DATE('".$datareceb."', '%d/%m/%Y'), INTERVAL ".$vintervalo." ".$strintervalo."))";
			
			if(!empty($datareceb)){
				$recebimentocalc = "DATE(DATE_ADD(STR_TO_DATE('".$datareceb."', '%d/%m/%Y'), INTERVAL ".$vintervalo." ".$strintervalo."))";
			}else{
				$recebimentocalc = $vencimentocalc;
			}
			if($status=='ABERTO'){
				$status='ABERTO';
			}else{
				$status='PENDENTE';
			}
			// quando digitar valor com 2,50 trocar para 2.50
			$valor = str_replace(",", ".", $valor);

			//LTM - 31-03-2021: Retorna o Idfluxo ContaPagar
			$idfluxostatus = FluxoController::getIdFluxoStatus('contapagar', $status);

			if(!empty($idcontaitem)){
			    $sqlins = "insert into contapagar  (idempresa,idpessoa,tipoespecifico,idcontaitem,idformapagamento,datapagto,datareceb,valor,intervalo,obs,tipo,status,idfluxostatus,parcelas,parcela,formapagto,progpagamento,visivel,criadopor,criadoem) 
					    values (".cnf::$idempresa.",".$idpessoa.",'".$tipoespecifico."',".$idcontaitem.",".$idformapagamento.",".$vencimentocalc.",".$recebimentocalc.",".$valor.",'".$intervalo."','".$obs."','".$tipo."','".$status."', $idfluxostatus, ".$_parcelas.",".$i.",'".$formapagto."','".$progpagamento."','".$visivel."','".$_SESSION["SESSAO"]["USUARIO"]."',sysdate())";
			}else{
			    $sqlins = "insert into contapagar  (idempresa,idpessoa,tipoespecifico,idformapagamento,datapagto,datareceb,valor,intervalo,obs,tipo,status,idfluxostatus,parcelas,parcela,formapagto,progpagamento,visivel,criadopor,criadoem) 
					    values (".cnf::$idempresa.",".$idpessoa.",'".$tipoespecifico."',".$idformapagamento.",".$vencimentocalc.",".$recebimentocalc.",".$valor.",'".$intervalo."','".$obs."','".$tipo."','".$status."', $idfluxostatus, ".$_parcelas.",".$i.",'".$formapagto."','".$progpagamento."','".$visivel."','".$_SESSION["SESSAO"]["USUARIO"]."',sysdate())";
			  
			}
			$ins = d::b()->query($sqlins) or die("Erro ao inserir parcelas na contapagar: <br>".mysqli_error(d::b())." sql=".$sqlins);
			
			//echo("<Br>".$sqlins);
			
			if(!$ins){
				d::b()->query("ROLLBACK;") or die("presavecontapagar:2-Falha ao inserir na contapagar : ". mysqli_error(d::b()) . "<p>SQL: ".$sqlins);
			}	

			//LTM - 31-03-2021: Insere o FluxoHist para ContaPagar
			if(!empty($idfluxostatus))
			{
				$idcontapagar = mysqli_insert_id(d::b());
				FluxoController::inserirFluxoStatusHist('contapagar', $idcontapagar, $idfluxostatus, 'PENDENTE');
			}

			d::b()->query("COMMIT") or die("possavecontapagar: Falha ao efetuar COMMIT [contapagar]: ".mysqli_error(d::b()));
		}
		cnf::agrupaCP();      
		//die();
	}
	
}elseif($iu == "u" and !empty($_SESSION['arrpostbuffer']['1']['u']['contapagar']['idcontapagar']) and $_SESSION['arrpostbuffer']['1']['u']['contapagar']['tipoobjeto']=='nf' and $_SESSION['arrpostbuffer']['1']['u']['contapagar']['status']=='INATIVO'){
   
    $idcontapagar=$_SESSION['arrpostbuffer']['1']['u']['contapagar']['idcontapagar'];
    
	//LTM - 31-03-2021: Retorna o Idfluxo ContaPagar
	$idfluxostatus = FluxoController::getIdFluxoStatus('contapagar', 'INATIVO');

	$sqlp2 = "UPDATE contapagar set idfluxostatus =".$idfluxostatus." WHERE idcontapagar = ".$idcontapagar;
	d::b()->query($sqlp2);

	FluxoController::inserirFluxoStatusHist('contapagar', $idcontapagar, $idfluxostatus, 'INATIVO');

}

//agrupaContapagar($fluxo);


// gerar o item da nota criada
$idobjetosolipor=$_SESSION['arrpostbuffer']['x']['i']['nf']['idobjetosolipor'];
$tipoobjetosolipor=$_SESSION['arrpostbuffer']['x']['i']['nf']['tipoobjetosolipor'];


if(!empty($idobjetosolipor) and $tipoobjetosolipor=='contapagar'){

	$idformapagamento=$_SESSION['arrpostbuffer']['x']['i']['nf']['idformapagamento'];
    
    $total=str_replace(",",".",$_SESSION['arrpostbuffer']['x']['i']['nf']['total']);

	if( $_SESSION['arrpostbuffer']['x']['i']['nf']['tiponf']=='R'){        
		$idfluxostatus = FluxoController::getIdFluxoStatus('comprasrh', 'APROVADO');
		FluxoController::inserirFluxoStatusHist('comprasrh', $_SESSION["_pkid"], $idfluxostatus, 'APROVADO');
    }else{
       	$idfluxostatus = FluxoController::getIdFluxoStatus('nfentrada', 'APROVADO');
		FluxoController::inserirFluxoStatusHist('nfentrada', $_SESSION["_pkid"], $idfluxostatus, 'APROVADO');
    } 
   

	gerarnfcon($_SESSION["_pkid"],$idobjetosolipor,$tipoobjetosolipor,$idformapagamento,$total);
	
	 
     $squ="update contapagar set tipoobjeto='nf',idobjeto=".$_SESSION["_pkid"]." where idcontapagar=".$idobjetosolipor;
     d::b()->query($squ) or die($sql."Erro ao atualizar contapagar: <br>".mysqli_error(d::b()));

	  //gera rateio quando tem pessoa no nfitem
	  $sqln="select * from nfitem where nfe='Y'  and vlritem >0 and idpessoa is not null and idnf =".$_SESSION["_pkid"];
	  $resn=d::b()->query($sqln) or die("Erro ao buscar  os itens da nf: <br>".mysqli_error(d::b()));
	  $qtdpes=mysqli_num_rows($resn);
  
	  if($qtdpes>0){//so vai gerar para itens com pessoas vinculadas
		  cnf::geraRateio($_SESSION["_pkid"]);
	  }

}else{
	$idobjetosolipor=$_SESSION['arrpostbuffer']['x']['u']['nf']['idobjetosolipor'];
	$tipoobjetosolipor=$_SESSION['arrpostbuffer']['x']['u']['nf']['tipoobjetosolipor'];
	$idformapagamento=$_SESSION['arrpostbuffer']['x']['u']['nf']['idformapagamento'];
	$total=str_replace(",",".",$_SESSION['arrpostbuffer']['x']['u']['nf']['total']);

	$idnf=$_SESSION['arrpostbuffer']['x']['u']['nf']['idnf'];
	if(!empty($idnf)){

		$sqld="delete from nfitem where idnf=".$idnf;
		d::b()->query($sqld) or die($sqld."Erro ao retirar itens: <br>".mysqli_error(d::b()));

		gerarnfcon($idnf,$idobjetosolipor,$tipoobjetosolipor,$idformapagamento,$total);
	}


}

// inativar um item do contapagar ira atualizar seu valor
$_idcontapagaritem=$_SESSION['arrpostbuffer']['x']['u']['contapagaritem']['idcontapagaritem'];
$_status=$_SESSION['arrpostbuffer']['x']['u']['contapagaritem']['status'];
if(!empty($_status) and !empty($_idcontapagaritem)){
	$sql="select f.agrupnota,f.agrupado,c.idcontapagar,f.formapagamento,f.agruppessoa 
				from contapagaritem i 
				join contapagar c on(c.idcontapagar = i.idcontapagar)
				join formapagamento f on(f.idformapagamento = c.idformapagamento)
				where i.idcontapagaritem =".$_idcontapagaritem;
	$res= d::b()->query($sql) or die($sql."Falha ao buscar informacoes da forma de pagamento: <br>".mysqli_error(d::b()));
	$row=mysqli_fetch_assoc($res);
	if( $row['agrupado']=='Y' and $row['formapagamento']!='C.CREDITO' and ($row["formapagamento"]!='BOLETO' or  $row['agruppessoa']!='Y')){       
	
	
		$sql1="select sum(valor) as nvalor from contapagaritem where status!='INATIVO' and idcontapagar =".$row['idcontapagar'];             
		$res1= d::b()->query($sql1) or die($sql1."Falha ao buscar somar parcelas do iten: <br>".mysqli_error(d::b()));
		$row1=mysqli_fetch_assoc($res1);

		$sql2="update contapagar set valor ='".$row1['nvalor']."' 
					where status!='QUITADO' 
					and idcontapagar = ".$row['idcontapagar']; 
		$res2= d::b()->query($sql2) or die($sql2."Falha ao atualizar o valor da fatura  <br>".mysqli_error(d::b()));            
	}
}//if(!empty($_status) and !empty($_idcontapagaritem)){


//atualizar o valor da fatura anterior ao mudar a parcela de fatura
$_idcontapagaritem=$_SESSION['arrpostbuffer']['x']['u']['contapagaritem']['idcontapagaritem'];
$_idcontapagar=$_SESSION['arrpostbuffer']['x']['u']['contapagaritem']['idcontapagar'];
if(!empty($_idcontapagaritem) and !empty($_idcontapagar) and empty($_status)){
    $tipoespecifico=traduzid("contapagar","idcontapagar","tipoespecifico",$_idcontapagar );
   if($tipoespecifico!='NORMAL'){
    
        $sql="select sum(i.valor) AS valor ,f.agrupado,f.formapagamento,f.agruppessoa
			from contapagaritem i  join  formapagamento f on( f.idformapagamento=i.idformapagamento)
            where i.idcontapagar = ".$_idcontapagar."
            and i.status !='INATIVO'";

        $res=d::b()->query($sql) or die("Falha ao buscar somatorio da fatura atual sql=".$sql);
        $qtdrows=mysqli_num_rows($res);
		$row=mysqli_fetch_assoc($res);
        if($qtdrows>0 and $row['formapagamento']!='C.CREDITO' and (($row["formapagamento"]!='BOLETO' or  $row['agruppessoa']!='Y') or ($row['agrupnota']=='Y')) ){
            
            $sqlu="update contapagar set valor='".$row['valor']."' where idcontapagar=".$_idcontapagar;
            $resu=d::b()->query($sqlu) or die("Falha ao atualizar a somatória da fatura atual sql=".$sqlu);
        }      

   }   

}



$_xidcontapagar=$_SESSION['arrpostbuffer']['x9']['i']['contapagaritem']['idcontapagar'];
if(!empty($_xidcontapagar) ){
    $tipoespecifico=traduzid("contapagar","idcontapagar","tipoespecifico",$_xidcontapagar );
   if($tipoespecifico!='NORMAL'){
    
        $sql="select sum(i.valor) AS valor ,f.agrupado,f.formapagamento,f.agruppessoa
			from contapagaritem i  join  formapagamento f on( f.idformapagamento=i.idformapagamento)
            where i.idcontapagar = ".$_xidcontapagar."
            and i.status !='INATIVO'";

        $res=d::b()->query($sql) or die("Falha ao buscar somatorio da fatura atual sql=".$sql);
        $qtdrows=mysqli_num_rows($res);
		$row=mysqli_fetch_assoc($res);
        if($qtdrows>0 and $row['formapagamento']!='C.CREDITO' and (($row["formapagamento"]!='BOLETO' or  $row['agruppessoa']!='Y') or ($row['agrupnota']=='Y') ) ){
            
            $sqlu="update contapagar set valor='".$row['valor']."' where idcontapagar=".$_xidcontapagar;
            $resu=d::b()->query($sqlu) or die("Falha ao atualizar a somatória da fatura atual sql=".$sqlu);
        }      

   }   

}


function gerarnfcon($idnf,$idobjetosolipor,$tipoobjetosolipor,$idformapagamento,$total){

	$arrNF=getObjeto("nf", $idnf,"idnf");

	$sqlfo="select * from confcontapagar
	where idformapagamento =".$idformapagamento." 
		and status='ATIVO'";
		$resfo= d::b()->query($sqlfo) or die($sqlfo."Fala ao buscar se forma de pagamento e comissao: <br>".mysqli_error(d::b()));
		$qtdfo=mysqli_num_rows($resfo);
		if($qtdfo>0){
			$arrconfCP=mysqli_fetch_assoc($resfo);



		//$arrconfCP=cnf::getDadosConfContapagar('COMISSAO');



		$sql="select i.valor,i.parcela,i.idpessoa,n.nnfe,replace(p.nome,'\'','') as nome,n.idnf as  idobjetoitem,'nf' as tipoobjetoitem
				from contapagaritem i join contapagar cp on(cp.idcontapagar=i.idobjetoorigem and i.tipoobjetoorigem ='contapagar')
				join nf n on(n.idnf=cp.idobjeto and cp.tipoobjeto='nf')
				join pessoa p on(p.idpessoa=n.idpessoa)
				where i.idcontapagar =".$idobjetosolipor." and i.status!='INATIVO'
				union
				select i.valor,i.parcela,i.idpessoa,n.nnfe, replace(p.nome,'\'','') as nome,n.idnotafiscal as  idobjetoitem,'notafiscal' as tipoobjetoitem
				from contapagaritem i  join contapagar cp on(cp.idcontapagar=i.idobjetoorigem and i.tipoobjetoorigem ='contapagar')
				join notafiscal n on(n.idnotafiscal=cp.idobjeto and cp.tipoobjeto='notafiscal')
				join pessoa p on(p.idpessoa=n.idpessoa)
				where i.idcontapagar =".$idobjetosolipor." and i.status!='INATIVO'
				UNION 
				select i.valor,i.parcela,i.idpessoa,n.nnfe,replace(p.nome,'\'','') as nome,n.idnf as  idobjetoitem,'nf' as tipoobjetoitem
				from contapagaritem i 
				join nf n on(n.idnf=i.idobjetoorigem and i.tipoobjetoorigem ='nf')	
				join pessoa p on(p.idpessoa=n.idpessoa)
				where i.idcontapagar =".$idobjetosolipor." and i.status!='INATIVO'
				union
				select i.valor,i.parcela,i.idpessoa,n.nnfe, replace(p.nome,'\'','') as nome,n.idnotafiscal as  idobjetoitem,'notafiscal' as tipoobjetoitem
				from contapagaritem i  
				join notafiscal n on(n.idnotafiscal=i.idobjetoorigem and i.tipoobjetoorigem ='notafiscal')		
				join pessoa p on(p.idpessoa=n.idpessoa)
				where i.idcontapagar =".$idobjetosolipor." and i.status!='INATIVO'";


		$res= d::b()->query($sql) or die($sql."Fala ao buscar parcelas existentes: <br>".mysqli_error(d::b()));
		$qtd=mysqli_num_rows($res);
		if($qtd>0){
		while($row=mysqli_fetch_assoc($res)){

			
			$arrnfitem=array();
			$arrnfitem[1]['qtd']=1;
			$arrnfitem[1]['vlritem']=$row['valor'];
			$arrnfitem[1]['total']=$row['valor'];
			$arrnfitem[1]['prodservdescr']='NFe '.$row['nnfe']." ".$row['nome'];
			$arrnfitem[1]['idcontaitem']=$arrconfCP['idcontaitem'];
			$arrnfitem[1]['idtipoprodserv']=$arrconfCP['idtipoprodserv'];				
			$arrnfitem[1]['idnf']=$idnf;
			$arrnfitem[1]['un']='UN';	
			$arrnfitem[1]['nfe']='Y';	
			$arrnfitem[1]['tiponf']='S';	
			$arrnfitem[1]['idobjetoitem']=$row['idobjetoitem'];
			$arrnfitem[1]['tipoobjetoitem']=$row['tipoobjetoitem'];		
			$arrnfitem[1]['idconfcontapagar']=$arrconfCP['idconfcontapagar'];
			$arrnfitem[1]['parcela']=$row['parcela'];
			$arrnfitem[1]['idpessoa']=$row['idpessoa'];


			$inidnfitem=cnf::inseredb($arrnfitem,'nfitem'); 
			$inidnfitem=$inidnfitem[0];

			
		}
		}else{
		$sql="select * from contapagar where idcontapagar=".$idobjetosolipor." and status!='INATIVO'";
		$res= d::b()->query($sql) or die($sql."Fala ao buscar parcelas existentes: <br>".mysqli_error(d::b()));
		$row=mysqli_fetch_assoc($res);


			$arrnfitem=array();
			$arrnfitem[1]['qtd']=1;
			$arrnfitem[1]['vlritem']=$row['valor'];
			$arrnfitem[1]['total']=$row['valor'];
			$arrnfitem[1]['prodservdescr']='ASSESSORIA OU CONSULTORIA DE QUALQUER NATUREZA';
			$arrnfitem[1]['idcontaitem']=$arrconfCP['idcontaitem'];
			$arrnfitem[1]['idtipoprodserv']=$arrconfCP['idtipoprodserv'];				
			$arrnfitem[1]['idnf']=$idnf;	
			$arrnfitem[1]['un']='UN';
			$arrnfitem[1]['nfe']='Y';
			$arrnfitem[1]['tiponf']='S';
			$arrnfitem[1]['idconfcontapagar']=$arrconfCP['idconfcontapagar'];
			$arrnfitem[1]['parcela']=$row['parcela'];
			$arrnfitem[1]['idpessoa']=$row['idpessoa'];

			$inidnfitem=cnf::inseredb($arrnfitem,'nfitem'); 
			$inidnfitem=$inidnfitem[0];

		}

		$insnfconfpagar = new Insert();
        $insnfconfpagar->setTable("nfconfpagar");
        $insnfconfpagar->idnf =$idnf;
        $insnfconfpagar->datareceb = $arrNF['dtemissao'];
        $insnfconfpagar->parcela = 1;
        $insnfconfpagar->idformapagamento = $arrNF['idformapagamento'];
        $insnfconfpagar->save();


		}else{


		$arrnfitem=array();
		$arrnfitem[1]['qtd']=1;
		$arrnfitem[1]['vlritem']=$total;
		$arrnfitem[1]['total']=$total;
		$arrnfitem[1]['prodservdescr']='ASSESSORIA OU CONSULTORIA DE QUALQUER NATUREZA';
		$arrnfitem[1]['idnf']=$idnf;
		$arrnfitem[1]['tiponf']='S';	
		$arrnfitem[1]['un']='UN';
		$arrnfitem[1]['nfe']='Y';

		$inidnfitem=cnf::inseredb($arrnfitem,'nfitem'); 
		$inidnfitem=$inidnfitem[0];

		}

		

		cnf:: geraRateio($idnf);
}

if(!empty($_SESSION['arrpostbuffer']['1']['u']['contapagar']['idcontapagar'])
	and !empty($_SESSION['arrpostbuffer']['1']['u']['contapagar']['datareceb'])
	and $_SESSION['arrpostbuffer']['1']['u']['contapagar']['tipo'] == 'D'){

	
	$id = $_SESSION['arrpostbuffer']['1']['u']['contapagar']['idcontapagar'];

	// GVT - 23/07/2021 - @471938 lançamentos com diferença de 28 dias gera assinatura p/ o Fábio
	//
	// Aqui há a necessidade de verificar a existência de uma 
	// assinatura pendente p/ não gerar novas assinaturas ou 
	// remover a assinatura que já existe

	$sqlCarrimbo = "SELECT idcarrimbo as id FROM carrimbo WHERE idobjeto = ".$id." AND tipoobjeto = 'contapagar' AND idpessoa = 798 AND status = 'PENDENTE'";
	$resCarrimbo = d::b()->query($sqlCarrimbo) or die("[saveposchange]: Erro ao consultar assinatura pendente conta a pagar");

	$datapagto = date('Y-m-d', strtotime(str_replace('/', '-', $_SESSION['arrpostbuffer']['1']['u']['contapagar']['datareceb'])));
	
	$d1 = strtotime(date("Y-m-d"));
	$d2 = strtotime($datapagto);
	$diff = ($d2 - $d1)/60/60/24;
	if($diff < 28 and mysqli_num_rows($resCarrimbo) == 0){
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
		(".$_SESSION["SESSAO"]["IDEMPRESA"].",
		798,
		".$id.",
		'contapagar',
		'PENDENTE',
		'sislaudo',
		now(),
		'sislaudo',
		now());
		";
		d::b()->query($insCarrimbo) or die("[saveposchange]: Erro ao inserir assinatura contapagar. sql: ".$insCarrimbo);
	}else if($diff >= 28 and mysqli_num_rows($resCarrimbo) > 0){
		while($rCarrimbo = mysqli_fetch_assoc($resCarrimbo)){
			d::b()->query("
				UPDATE carrimbo SET status = 'CANCELADO', alteradopor = 'sislaudo', alteradoem = now() WHERE idcarrimbo = ".$rCarrimbo["id"]."
			") or die("[saveposchange]: Erro ao atualizar assinatura conta a pagar");
		}
	}
}
?>