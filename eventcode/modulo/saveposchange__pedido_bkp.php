<? //print_r($_POST); die;
$idnotafiscal = $_POST["_1_u_nf_idnf"];
$qtdparcelas = $_POST["_1_u_nf_parcelas"];
$total = tratanumero($_POST["_1_u_nf_total"]);
$subtotal= tratanumero($_POST["_1_u_nf_subtotal"]);
$frete=tratanumero($_POST["_1_u_nf_frete"]);

$idobjetosolipor = $_POST["_1_i_nf_idobjetosolipor"];
$tipoobjetosolipor = $_POST["_1_i_nf_tipoobjetosolipor"];

$parcelamento = $_POST["parcelamento"];
$formapgto = $_POST["_1_u_nf_formapgto"];
$modfrete=$_POST["_1_u_nf_modfrete"];
$tipocontapagar=$_POST["_1_u_nf_tipocontapagar"];
$tipointervalo = $_POST["_1_u_nf_tipointervalo"];
$tiponf = $_POST["_1_u_nf_tiponf"];
$tipoorc = $_POST["_1_u_nf_tipoorc"];
$status = $_POST["_1_u_nf_status"];
//$idagencia = $_POST["_1_u_nf_idagencia"];
$idformapagamento = $_POST["_1_u_nf_idformapagamento"];
$rastreador = $_POST["_1_u_nf_rastreador"];
$antrastreador = $_POST["rastreador"];
$prazo = $_POST["_1_u_nf_prazo"];
$prazoant = $_POST["prazo"];
$statusant = $_POST["statusant"];
$emissao = $_POST["_1_u_nf_dtemissao"];
$dtemissao = $_POST["_1_u_nf_dtemissao"];
$dtemissaoant = $_POST["dtemissao"];
$idtransportadora=$_POST["_1_u_nf_idtransportadora"];
$idtransportadoraant = $_POST["idtransportadora"];
$respenvio=$_POST["_1_u_nf_respenvio"];
$respenvioant = $_POST["respenvio"];
$geracontapagar = $_POST["_1_u_nf_geracontapagar"];
$obsenvio = $_POST["_1_u_nf_obsenvio"];
$idcontaitem = $_POST["_1_u_nf_idcontaitem"];
$comissao =$_POST['_1_u_nf_comissao'];

$idpessoa = $_POST['_1_u_nf_idpessoafat'];
if(empty($idpessoa)){
  $idpessoa = $_POST['_1_u_nf_idpessoa'];  
}

$nnfe=$_POST['_1_u_nf_nnfe'];
$gnre=$_POST['_1_u_nf_gnre'];
$gnreval=$_POST['gnreval'];

//IMPOSTO DE SERVICO
$nf_pis=tratanumero($_POST['_1_u_nf_pis']);
$nf_cofins=tratanumero($_POST['_1_u_nf_cofins']);
$nf_csll=tratanumero($_POST['_1_u_nf_csll']);

$nf_darf=$nf_pis+$nf_cofins+$nf_csll;

$nf_inss=tratanumero($_POST['_1_u_nf_inss']);
$nf_ir=tratanumero($_POST['_1_u_nf_ir']);
$nf_issret=tratanumero($_POST['_1_u_nf_issret']);

$geraparcela="N";

 


//Trecho de codigo para gerar as parcelas INICIO
$iu = $_SESSION['arrpostbuffer']['1']['u']['nf']['idnf'] ? 'u' : 'i';

if(!empty($idobjetosolipor) and $tipoobjetosolipor=='nf'){
  copiarnfitem($idobjetosolipor,$_SESSION["_pkid"]);  
}


//relacionado a geracao da solicitacao de fabricacao
 $idsolfab=$_POST['_1_u_lote_idsolfab'];
 if($idsolfab=='novo'){
     $idpessoa=$_POST['_1_i_lote_idpessoa'];
    $sqli="INSERT INTO solfab
        (idempresa,idlote,idpessoa,criadopor,criadoem,alteradopor,alteradoem)
        VALUES
        (".$_SESSION["SESSAO"]["IDEMPRESA"].",".$_SESSION["_pkid"].",".$idpessoa.",'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate(),'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate())";     
 
    $resi=d::b()->query($sqli) or die("Erro ao gerar solicitação de fabricação: <br>".mysqli_error(d::b())." sql=".$sqli);
    $idsolfab=mysqli_insert_id(d::b());
    
    $sqlu="update lote set idsolfab=".$idsolfab." where idlote =".$_SESSION["_pkid"];
    d::b()->query($sqlu) or die("Erro ao atualizar  solicitação de fabricação no lote: <br>".mysqli_error(d::b())." sql=".$sqlu);
 }

//Verifica o tipo de NF
if($tiponf=="V"){

	//Ajusta tipo, geraparcela, difdias, diasentrada, intervalo
    $tipo="C";
    $geraparcela="S";    
    $difdias = 0;// no caso de credito so e atualizado na conta 2 dias apos o pagamento *atualizado para 0 o dias foram para a trigger na contapagar 21012015
    $diasentrada=$_POST["_1_u_nf_diasentrada"]-1;
    $intervalo=$_POST["_1_u_nf_intervalo"];
    $visivel='N';
    $contapagaritem="N";

}elseif($tiponf=="C" or $tiponf=="T" or $tiponf=="S" or $tiponf=="E" or $tiponf=="R" or $tiponf=="M" or $tiponf=="F"){//if($tiponf=="V"){

    //Ajusta tipo, geraparcela, difdias, diasentrada, intervalo
    if($tipocontapagar){
	$tipo=$tipocontapagar;
    }else{
	$tipo="D";	
    }
    $geraparcela="S";
    $difdias = 0;//debito e no mesmo dia
    $diasentrada=$_POST["_1_u_nf_diasentrada"];
    $intervalo = $_POST["_1_u_nf_intervalo"];
    $visivel="S";
    
    if($formapgto=='C.CREDITO' or  $formapgto=="BOL AGRUPADO"){
	$contapagaritem = "Y";
    }	

}else{
	$diasentrada=$_POST["_1_u_nf_diasentrada"];
	$intervalo = $_POST["_1_u_nf_intervalo"];
	$visivel='N';
}


alteraFrete($modfrete,$iu,$idnotafiscal,$statusant,$tiponf);



//SE CANCELAR A NOTA DELETA AS PARCELAS
if ((!empty($idnotafiscal)) AND $status == "CANCELADO" ){
    	deletaParcelasExistentes($idnotafiscal);
}
//SE FOR GERA PARCELA NÃO DELETA AS PARCELAS
IF((!empty($idnotafiscal)) AND $geracontapagar=='N'){      
	deletaParcelasExistentes($idnotafiscal);   
}

//echo("gerapacela =".$geraparcela." idnotafiscal=".$idnotafiscal." Status=".$status." tipoorc=".$tipoorc." geracontapagar=".$geracontapagar." tiponf = ".$tiponf); die;
if (!empty($idnotafiscal) and ( $status=="ENVIAR" or $status == "ENVIADO" or $status == "APROVADO" or ($status == "CONCLUIDO" and $statusant!="CONCLUIDO" and $statusant!="RECEBIDO") or ($status == "RECEBIDO" and $statusant!="CONCLUIDO" and $statusant!="RECEBIDO") ) and $geraparcela=="S" and $tipoorc!="S" and $geracontapagar=="Y"){
   
    $arrParcelas=recuperaParcelas($idnotafiscal,'QUITADO','nf');
    $qtParcelas =$arrParcelas['quant'];
    
    $arrParcelasIV=recuperaParcelasItensVinc($idnotafiscal,'nf');
    $qtParcelasIV =$arrParcelasIV['quant'];
    
    
    $arrlinhasbol= verificaboleto($idnotafiscal);
    $qtdlinhasbol=$arrlinhasbol['quant'];
   //die($qtParcelas);
    
    // contaitem de transporte ou compra a credito
    if($contapagaritem=="Y"){
	//verifica se existe alguma parcela item quitada. se existir, nao alterar nada.
	$arrParcItens=getParcelaItens($idnotafiscal);
	$qtParcelasitem = $arrParcItens['quant'];
	deletacontapagar($idnotafiscal,'nf');
    }else{
    	$qtParcelasitem=0;
    }
// echo($arrParcelas['quant']." - ".$arrlinhasbol['quant']." - ".$qtParcelasitem);die;
    if ($qtParcelas == 0 and $qtdlinhasbol== 0 and $qtParcelasitem==0 and $qtParcelasIV==0){
	//deleta as parcelas existentes.
    	deletaParcelasExistentes($idnotafiscal);
      
	//Insere novas parcelas
	$valorparcela = $total/$qtdparcelas;
	//$valorparcelarep =(($total-$frete)/$qtdparcelas);
        $sql="select ifnull(sum(frete),0) as sumfrete
	from nfitem
	where idnf =".$idnotafiscal;
	$res=d::b()->query($sql) or die("erro ao verificar itens da notafiscal sql=".$sql);
	$row= mysqli_fetch_assoc($res);
       
        
        $valorparcelarep =(($total-$row['sumfrete'])/$qtdparcelas);

	if(empty(trim($diasentrada))){
		$diasentrada = '0';		
	}

        if($comissao=='Y'){
            $arrComisssao = getDadosComissao($idpessoa);
            $arrComisssao['participacaoprod']=$arrComisssao['participacaoprod']/100;	    
        }


		//die($qtdparcelas);	
		$emissao = validadate($emissao);
		for ($index = 1; $index <= $qtdparcelas; $index++) {
			if($tipointervalo=="M"){
				$strintervalo= 'MONTH';
			}elseif($tipointervalo=="Y"){
				$strintervalo= 'YEAR';
			}else{
				$strintervalo= 'DAY';
			}
			if($index == 1){
				$valintervalo = $diasentrada;
				$diareceb = $diasentrada + $difdias;
				$vencimentocalc = "DATE(DATE_ADD('".$emissao."', INTERVAL ".$diasentrada." ".$strintervalo."))";
				$recebcalc = "DATE(DATE_ADD('".$emissao."', INTERVAL ".$diareceb." ".$strintervalo."))";
				
			}else{
				$valintervalo = $valintervalo + $intervalo;
				$diareceb = $valintervalo + $difdias;
				$vencimentocalc = "DATE(DATE_ADD('".$emissao."', INTERVAL ".$valintervalo." ".$strintervalo."))";
				$recebcalc = "DATE(DATE_ADD('".$emissao."', INTERVAL ".$diareceb." ".$strintervalo."))";
			}

			if($contapagaritem=="Y"){
				$tmpsqlins = "INSERT INTO contapagaritem (idempresa,status,idpessoa,idcontaitem,idobjetoorigem,tipoobjetoorigem,idformapagamento,parcela,parcelas,datapagto,valor,criadopor,criadoem,alteradopor,alteradoem)
				VALUES (".$_SESSION["SESSAO"]["IDEMPRESA"].",'ABERTO',".$idpessoa.",".$idcontaitem.",".$idnotafiscal.",'nf',".$idformapagamento.",".$index.",".$qtdparcelas.",".$recebcalc.",".$valorparcela.",'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate(),'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate())";
			}else{
			    if($tiponf=="F"){
				$tipoespecifico=",tipoespecifico";
				$vtipoespecifico=",'AGRUPAMENTO'";
				$statuscp='ABERTO';
			    }else{
				$tipoespecifico="";
				$vtipoespecifico="";
				$statuscp='PENDENTE';
			    }
			    
				if(!empty($idcontaitem)){
					$tmpsqlins = "INSERT INTO contapagar (idempresa".$tipoespecifico.",idcontaitem,idformapagamento,idpessoa,tipoobjeto,idobjeto,parcela,parcelas,valor,datapagto,datareceb,status,tipo,visivel,intervalo,criadopor,criadoem)
					VALUES (".$_SESSION["SESSAO"]["IDEMPRESA"]."".$vtipoespecifico.",".$idcontaitem.",".$idformapagamento.",".$idpessoa.",'nf',".$idnotafiscal.",".$index.",".$qtdparcelas.",".$valorparcela.",".$vencimentocalc.",".$recebcalc.",'".$statuscp."','".$tipo."','".$visivel."',".$intervalo.",'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate())";
				}else{
					$tmpsqlins = "INSERT INTO contapagar (idempresa".$tipoespecifico.",idformapagamento,idpessoa,tipoobjeto,idobjeto,parcela,parcelas,valor,datapagto,datareceb,status,tipo,visivel,intervalo,criadopor,criadoem)
					VALUES (".$_SESSION["SESSAO"]["IDEMPRESA"]."".$vtipoespecifico.",".$idformapagamento.",".$idpessoa.",'nf',".$idnotafiscal.",".$index.",".$qtdparcelas.",".$valorparcela.",".$vencimentocalc.",".$recebcalc.",'".$statuscp."','".$tipo."','".$visivel."',".$intervalo.",'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate())";
				}  
			}

			//Insere a parcela
			$resi=d::b()->query($tmpsqlins) or die("Erro ao inserir parcelas da Nota Fiscal: <br>".mysqli_error(d::b())." sql=".$tmpsqlins);
			if(!$resi){
				d::b()->query("ROLLBACK;");
				die("1-Falha ao gerar parcela: " . mysql_error() . "<p>SQL: ".$tmpsqlins);
			}else{
				$idcontapagar=mysqli_insert_id(d::b());
			}

			//GERA PARCELA DE COMISSÃO
			if($comissao=='Y' and !empty($arrComisssao['idpessoa']) and !empty($idcontapagar)){

				$valorn=$arrComisssao['participacaoprod']*$valorparcelarep;
				$datapg="(LAST_DAY(".$recebcalc.") + INTERVAL 1 DAY) ";

				$tmpsqlins = "INSERT INTO contapagaritem (idempresa,status,idpessoa,idobjetoorigem,tipoobjetoorigem,idformapagamento,parcela,parcelas,datapagto,valor,criadopor,criadoem,alteradopor,alteradoem)
				VALUES (".$_SESSION["SESSAO"]["IDEMPRESA"].",'ABERTO',".$arrComisssao['idpessoa'].",".$idcontapagar.",'contapagar',38,".$index.",".$qtdparcelas.",".$datapg.",".$valorn.",'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate(),'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate())";
				$resi=d::b()->query($tmpsqlins) or die("Erro ao inserir parcelas do representante: sql=".$tmpsqlins." mysql".mysqli_error(d::b()));

				//echo "<br>".$tmpsqlins; die;
				if(!$resi){
					d::b()->query("ROLLBACK;");
					die("1-Falha ao gerar parcela do representante: " . mysql_error() . "<p>SQL: ".$tmpsqlins);
				}
			}
		}//for ($index = 1; $index <= $qtdparcelas; $index++) {

		d::b()->query("COMMIT") or die("Erro");

		//corrigir parcelas em um centavo
		corrigirParcelas($idnotafiscal,$total,$contapagaritem);
	}//if ($qtParcelas == 0 and $qtlinhasbol== 0 and $qtParcelasitem==0){

//se não for para gerar parcela deletar as que estiverem pendentes
}elseif (!empty($idnotafiscal) and ($status == "CONCLUIDO" or $status == "RECEBIDO") and $geracontapagar=="N"){

	$arrParcItens=getParcelaItens($idnotafiscal);
	$qtParcelasitem = $arrParcItens['quant'];
	if($qtParcelasitem == 0){
		/*
		 * deleta as parcelas existentes.
		 */
		deletaParcelasExistentes($idnotafiscal);//maf: confirmar o nome "parcelasComissao"
	}
}

if($comissao=='N' and !empty($idnotafiscal)){
	
	//Verifica se existe alguma parcela quitada. se existir, nao alterar nada.
	$arrComissoesQuit=getComissoesPendentes($idnotafiscal);
	$qtParcelas = $arrComissoesQuit['quant'];

    if ($qtParcelas >0){
    	deletaComissoesPendentes();//VErifica se pode apenas apagar
    }
}

//gera parcela de GNRE
if(!empty($gnreval) and !empty($gnre) and !empty($dtemissao) and !empty($idnotafiscal)){
	
	$ndtemissao = validadate($dtemissao);     
	$vencimentocalc = "DATE(DATE_ADD('".$ndtemissao."', INTERVAL 1 DAY))";	 

	//verifica se existe alguma parcela quitada. se existir, nao alterar nada.
	$arrParcelas=recuperaParcelas($idnotafiscal,'QUITADO','gnre');//ATENCAO: preparar para considerar TIPOOBJETO
	$qtParcelas =$arrParcelas['quant'];

	 deletacontapagar($idnotafiscal,'gnre');

	if ($qtParcelas == 0){

		$tmpsqlins = "INSERT INTO contapagar (idempresa,idpessoa,idcontaitem,idformapagamento,tipoobjeto,idobjeto,obs,parcela,parcelas,valor,datapagto,datareceb,status,formapagto,tipo,intervalo,visivel,criadopor,criadoem)
		VALUES (".$_SESSION["SESSAO"]["IDEMPRESA"].",6022,10,1,'gnre',".$idnotafiscal.",'NFe (".$nnfe.")-".$gnre."',1,1,".$gnreval.",".$vencimentocalc.",".$vencimentocalc.",'PENDENTE','BOLETO','D',30,'S','".$_SESSION["SESSAO"]["USUARIO"]."',sysdate())";

		$resi=d::b()->query($tmpsqlins) or die("Erro ao inserir parcelas do GNRE: sql=".$tmpsqlins." mysql".mysqli_error());
		// echo "<br>".$tmpsqlins;
		if(!$resi){
			d::b()->query("ROLLBACK;");
			die("1-Falha ao gerar parcela do GNRE: " . mysql_error() . "<p>SQL: ".$tmpsqlins);
		}
	}
}

//gera parcela DArf
if(!empty($nf_darf) and ($nf_darf>0) and !empty($dtemissao) and !empty($idnotafiscal)){    
   
    $ndtemissao = validadate($dtemissao);     
    $vencimentocalc = "(LAST_DAY('".$ndtemissao."') + INTERVAL 20 DAY) ";	    

    //verifica se existe alguma parcela quitada. se existir, nao alterar nada.
    $arrParcelas=recuperaParcelas($idnotafiscal,'QUITADO','nf_darf');//ATENCAO: preparar para considerar TIPOOBJETO
    $qtParcelas =$arrParcelas['quant'];

    deletacontapagar($idnotafiscal,'nf_darf');

    if ($qtParcelas == 0){
        
        
        if($nf_csll>0 and !empty($nf_csll)){// se for $nf_csll > 0 soma $nf_pis+$nf_cofins+$nf_csll e gera somente uma parcela senão gera separado os dois

	    $tmpsqlins = "INSERT INTO contapagar (idempresa,idpessoa,idcontaitem,idformapagamento,tipoobjeto,idobjeto,obs,parcela,parcelas,valor,datapagto,datareceb,status,formapagto,tipo,intervalo,visivel,criadopor,criadoem)
	    VALUES (".$_SESSION["SESSAO"]["IDEMPRESA"].",".$idpessoa.",".$idcontaitem.",1,'nf_darf',".$idnotafiscal.",'NFe (".$nnfe.") - CSRF ',1,1,".$nf_darf.",".$vencimentocalc.",".$vencimentocalc.",'PENDENTE','BOLETO','D',30,'S','".$_SESSION["SESSAO"]["USUARIO"]."',sysdate())";

	    $resi=d::b()->query($tmpsqlins) or die("Erro ao inserir parcelas do nf_darf: sql=".$tmpsqlins." mysql".mysqli_error(d::b()));
	    // echo "<br>".$tmpsqlins;
	    if(!$resi){
		    d::b()->query("ROLLBACK;");
		    die("1-Falha ao gerar parcela do nf_darf: " . mysqli_error(d::b()) . "<p>SQL: ".$tmpsqlins);
	    }
        }else{
 
            //PARCELA 1 PIS
            $tmpsqlins = "INSERT INTO contapagar (idempresa,idpessoa,idcontaitem,idformapagamento,tipoobjeto,idobjeto,obs,parcela,parcelas,valor,datapagto,datareceb,status,formapagto,tipo,intervalo,visivel,criadopor,criadoem)
	    VALUES (".$_SESSION["SESSAO"]["IDEMPRESA"].",".$idpessoa.",".$idcontaitem.",1,'nf_darf',".$idnotafiscal.",'NFe (".$nnfe.") - PIS ',1,1,".$nf_pis.",".$vencimentocalc.",".$vencimentocalc.",'PENDENTE','BOLETO','D',30,'S','".$_SESSION["SESSAO"]["USUARIO"]."',sysdate())";

	    $resi=d::b()->query($tmpsqlins) or die("Erro ao inserir parcelas do nf_darf_PIS: sql=".$tmpsqlins." mysql".mysqli_error(d::b()));
	    // echo "<br>".$tmpsqlins;
	    if(!$resi){
		    d::b()->query("ROLLBACK;");
		    die("1-Falha ao gerar parcela do nf_darf: " . mysqli_error(d::b()) . "<p>SQL: ".$tmpsqlins);
	    }
            //PARCELA 2 COFINS
            $tmpsqlins = "INSERT INTO contapagar (idempresa,idpessoa,idcontaitem,idformapagamento,tipoobjeto,idobjeto,obs,parcela,parcelas,valor,datapagto,datareceb,status,formapagto,tipo,intervalo,visivel,criadopor,criadoem)
	    VALUES (".$_SESSION["SESSAO"]["IDEMPRESA"].",".$idpessoa.",".$idcontaitem.",1,'nf_darf',".$idnotafiscal.",'NFe (".$nnfe.") - COFINS ',1,1,".$nf_cofins.",".$vencimentocalc.",".$vencimentocalc.",'PENDENTE','BOLETO','D',30,'S','".$_SESSION["SESSAO"]["USUARIO"]."',sysdate())";

	    $resi=d::b()->query($tmpsqlins) or die("Erro ao inserir parcelas do nf_darf_COFINS: sql=".$tmpsqlins." mysql".mysqli_error(d::b()));
	    // echo "<br>".$tmpsqlins;
	    if(!$resi){
		    d::b()->query("ROLLBACK;");
		    die("1-Falha ao gerar parcela do nf_darf: " . mysqli_error(d::b()) . "<p>SQL: ".$tmpsqlins);
	    }
        }
    }
}

//gera parcela IR
if(!empty($nf_ir) and ($nf_ir>0) and !empty($dtemissao) and !empty($idnotafiscal)){
    
    $ndtemissao = validadate($dtemissao);
    $vencimentocalc = "(LAST_DAY('".$ndtemissao."') + INTERVAL 20 DAY) ";	    
   
	//verifica se existe alguma parcela quitada. se existir, nao alterar nada.
	$arrParcelas=recuperaParcelas($idnotafiscal,'QUITADO','nf_ir');//ATENCAO: preparar para considerar TIPOOBJETO
	$qtParcelas =$arrParcelas['quant'];

	deletacontapagar($idnotafiscal,'nf_ir');

	if ($qtParcelas==0){

		$tmpsqlins = "INSERT INTO contapagar (idempresa,idpessoa,idcontaitem,idformapagamento,tipoobjeto,idobjeto,obs,parcela,parcelas,valor,datapagto,datareceb,status,formapagto,tipo,intervalo,visivel,criadopor,criadoem)
			VALUES (".$_SESSION["SESSAO"]["IDEMPRESA"].",".$idpessoa.",".$idcontaitem.",1,'nf_ir',".$idnotafiscal.",'NFe (".$nnfe.") - IRRF ',1,1,".$nf_ir.",".$vencimentocalc.",".$vencimentocalc.",'PENDENTE','BOLETO','D',30,'S','".$_SESSION["SESSAO"]["USUARIO"]."',sysdate())";

		$resi=d::b()->query($tmpsqlins) or die("Erro ao inserir parcelas do nf_ir: sql=".$tmpsqlins." mysql".mysqli_error(d::b()));
		// echo "<br>".$tmpsqlins;
		if(!$resi){
			d::b()->query("ROLLBACK;");
			die("1-Falha ao gerar parcela do nf_ir: " . mysqli_error(d::b()) . "<p>SQL: ".$tmpsqlins);
		}
   }
}

//gera parcela INSS
if(!empty($nf_inss) and ($nf_inss>0) and !empty($dtemissao) and !empty($idnotafiscal)){
    
    $ndtemissao = validadate($dtemissao);
    $vencimentocalc = "(LAST_DAY('".$ndtemissao."') + INTERVAL 20 DAY) ";	    
    
    //verifica se existe alguma parcela quitada. se existir, nao alterar nada.
    $arrParcelas=recuperaParcelas($idnotafiscal,'QUITADO','nf_inss');//ATENCAO: preparar para considerar TIPOOBJETO
    $qtParcelas =$arrParcelas['quant'];

    deletacontapagar($idnotafiscal,'nf_inss');
	
   if ($qtParcelas == 0){
    
	

	$tmpsqlins = "INSERT INTO contapagar (idempresa,idpessoa,idcontaitem,idformapagamento,tipoobjeto,idobjeto,obs,parcela,parcelas,valor,datapagto,datareceb,status,formapagto,tipo,intervalo,criadopor,criadoem)
		    VALUES (".$_SESSION["SESSAO"]["IDEMPRESA"].",".$idpessoa.",".$idcontaitem.",1,'nf_inss',".$idnotafiscal.",'NFe (".$nnfe.") - INSS ',1,1,".$nf_inss.",".$vencimentocalc.",".$vencimentocalc.",'PENDENTE','BOLETO','D',30,'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate())";

	$resi=d::b()->query($tmpsqlins) or die("Erro ao inserir parcelas do nf_inss: sql=".$tmpsqlins." mysql".mysqli_error(d::b()));
	// echo "<br>".$tmpsqlins;
	 if(!$resi){
	     d::b()->query("ROLLBACK;");
	     die("1-Falha ao gerar parcela do nf_inss: " . mysqli_error(d::b()) . "<p>SQL: ".$tmpsqlins);
	 }
   }
}

//gera parcela ISS retido

if(!empty($nf_issret) and ($nf_issret>0) and !empty($dtemissao) and !empty($idnotafiscal)){
    
    $ndtemissao = validadate($dtemissao);
    $vencimentocalc = "(LAST_DAY('".$ndtemissao."') + INTERVAL 15 DAY) ";	    
    
    //verifica se existe alguma parcela quitada. se existir, nao alterar nada.
    $arrParcelas=recuperaParcelas($idnotafiscal,'QUITADO','nf_issret');//ATENCAO: preparar para considerar TIPOOBJETO
    $qtParcelas =$arrParcelas['quant'];

    deletacontapagar($idnotafiscal,'nf_issret');
    

   if ($qtParcelas == 0){
    	
	    $tmpsqlins = "INSERT INTO contapagar (idempresa,idpessoa,idcontaitem,idformapagamento,tipoobjeto,idobjeto,obs,parcela,parcelas,valor,datapagto,datareceb,status,formapagto,tipo,intervalo,criadopor,criadoem)
		    VALUES (".$_SESSION["SESSAO"]["IDEMPRESA"].",".$idpessoa.",".$idcontaitem.",1,'nf_issret',".$idnotafiscal.",'NFe (".$nnfe.") - ISS Retido ',1,1,".$nf_issret.",".$vencimentocalc.",".$vencimentocalc.",'PENDENTE','BOLETO','D',30,'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate())";

	$resi=d::b()->query($tmpsqlins) or die("Erro ao inserir parcelas do nf_issret: sql=".$tmpsqlins." mysql".mysqli_error(d::b()));
	// echo "<br>".$tmpsqlins;
	 if(!$resi){
	     d::b()->query("ROLLBACK;");
	     die("1-Falha ao gerar parcela do nf_issret: " . mysqli_error(d::b()) . "<p>SQL: ".$tmpsqlins);
	 }
   }
}


##################################
function alterafrete($modfrete,$iu,$idnotafiscal,$statusant,$tiponf){ 
     
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
	    and c.idobjeto = ".$inidobj." and exists (select 1 from contapagaritem i where i.idcontapagar = c.idcontapagar)";
   
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
    $sqlveritem = "select count(*) as quant from contapagaritem where  idobjetoorigem= ".$idnotafiscal." and tipoobjetoorigem = 'nf' and status in('PENDENTE','PAGAR','QUITADO')";
    
    $resveritem = d::b()->query($sqlveritem) or die($sqlverifquit."Erro ao consultar parcelas item do cte: <br>".mysqli_error(d::b()));
    $rowverifitem = mysqli_fetch_array($resveritem);
    return  $rowverifitem;
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
			and cc.status='ABERTO'";
	d::b()->query($tmpsqldel) or die("Erro ao retirar parcelas de comissão da Nota Fiscal : <br>".mysqli_error(d::b()));
	
	/*
	 * deleta as parcelas existentes.
	 */
        $tmpsqldel = "delete cc.* 
                        from contapagar c,contapagaritem cc
                        where c.tipoobjeto = 'nf' 
                        and c.idobjeto =".$idnotafiscal."
                        and cc.idcontapagar = c.idcontapagar
			and cc.status='ABERTO'";
	d::b()->query($tmpsqldel) or die("Erro ao retirar parcelas de comissão da Nota Fiscal : <br>".mysqli_error(d::b()));
    
	
	//if($contapagaritem=="Y"){
	$tmpsqldel = "delete from contapagaritem where tipoobjetoorigem =  'nf' and idobjetoorigem = ".$idnotafiscal."  and status='ABERTO'";
	d::b()->query($tmpsqldel) or die("Erro ao retirar parcelas da Nota Fiscal: <br>".mysqli_error(d::b()));
	//}
        
	$tmpsqldel = "delete c.* from contapagar c 
		where c.tipoobjeto = 'nf' 
		and c.status!='QUITADO' 
		and c.idobjeto = ".$idnotafiscal;
	d::b()->query($tmpsqldel) or die("Erro ao retirar parcelas da Nota Fiscal: <br>".mysqli_error(d::b()));
        
}

function getDadosComissao($idpessoa){
    $sqlrep = "select 
			p.idpessoa
			,c.participacaoserv
			,c.participacaoprod
			from pessoa p
			,pessoacontato c
			where p.status='ATIVO'
			and p.idtipopessoa = 12
			and  p.idpessoa = c.idcontato
			 ".getidempresa('p.idempresa','pessoa')."
			and c.idpessoa = ".$idpessoa." order by nome";
            $resrep = d::b()->query($sqlrep) or die("A Consulta do representante falhou :".mysqli_error()."<br>Sql:".$sqlrep);	
            $rowrep= mysqli_fetch_assoc($resrep);
         
	    return $rowrep;    
}

function corrigirParcelas($idnotafiscal,$total,$contapagaritem){
    
    //corrigir parcelas em um centavo
	
	$sqls="select sum(valor) as vvalor,max(parcela) as mparcela from contapagar where idobjeto=".$idnotafiscal." and tipoobjeto='nf' ".getidempresa('idempresa','contapagar').";";
	$ress=d::b()->query($sqls) or die("Erro ao somar valor das parcelas sql=".$sqls);
	$rows=mysqli_fetch_assoc($ress);
	
	if($rows['vvalor']!=$total and ($contapagaritem!="Y")){
	    
	    if($rows['vvalor']>$total){
                $sqlup="update contapagar set valor=valor-0.01
                                where idobjeto=".$idnotafiscal."
                                and tipoobjeto='nf'
                                and parcela = 1
				and status!='QUITADO'
                                 ".getidempresa('idempresa','contapagar').";";
            }elseif($rows['vvalor']<$total){
                $sqlup="update contapagar set valor=valor+0.01
                                where idobjeto=".$idnotafiscal."
                                and tipoobjeto='nf'
				and status!='QUITADO'
                                and parcela = ".$rows['mparcela']."
                                 ".getidempresa('idempresa','contapagar').";";
            }
            if(!empty($sqlup)){
                d::b()->query($sqlup) or die("erro ao atualizar parcelas sql=".$sqlup);
            }
	} 
}

function getComisssoesPendentes(){
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
   $resverif = d::b()->query($sqlverifquit) or die($sqlverifquit."Erro ao consultar parcelas do representante para delecao: <br>".mysqli_error());
   $rowverif = mysqli_fetch_array($resverif);
   return $rowverif;
}

function deletaComissoesPendentes(){
    $tmpsqldel = "delete cc.* 
			    from contapagar c,contapagaritem cc
			    where c.tipoobjeto = 'nf' 
			    and c.idobjeto =".$idnotafiscal."
			    and cc.idobjetoorigem = c.idcontapagar
			    and cc.tipoobjetoorigem ='contapagar'
			    and cc.status !='QUITADO'";
    d::b()->query($tmpsqldel) or die("Erro ao retirar parcelas de comissão da Nota Fiscal : <br>".mysqli_error(d::b()));
}

function deletacontapagar($idobjeto,$tipoobjeto){
    //echo "qt:".$qtParcelas; 
    $tmpsqldel = "delete from contapagar where tipoobjeto = '".$tipoobjeto."' and status!='QUITADO' and idobjeto = ".$idobjeto;
    d::b()->query($tmpsqldel) or die("Erro ao retirar parcelas do nf_issret: <br>".mysqli_error(d::b()));
}

function getComissoesPendentes($idnotafiscal){
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
   $resverif = d::b()->query($sqlverifquit) or die($sqlverifquit."Erro ao consultar parcelas do representante para delecao: <br>".mysqli_error());
   $rowverif = mysqli_fetch_array($resverif);
   return $rowverif;
}


function copiarnfitem($idnforigem,$idnfnovo){
    $sql="INSERT INTO nfitem
	    (idempresa,
	    idnf,
	    idprodserv,
	    idprodservformula,
	    qtd,
	    vlritem,
	    vlritemacord,
	    frete,
	    total,
	    tiponf,
	    cst,
	    cfop,
	    ncm,
	    des,
	    basecalc,
	    vicmsdeson,
	    valicms,
	    valipi,
	    aliqbasecal,
	    aliqicms,
	    aliqicmsint,
	    aliqipi,
	    pis,
	    cofins,
	    obs,
	    nitemped,
	    xped,
	    nfe,
	    cert,
	    icmsufdest,
	    icmsufremet,
	    indiedest,
	    manual,
	    validade,
	    previsaoent,
	    cnpjtomador,
	    remcnpj,
	    remnome,
	    destcnpj,
	    destnome,
	    ord,
	    collapse,
	    criadopor,
	    criadoem,
	    alteradopor,
	    alteradoem)
	    (select 
	    idempresa,
	    ".$idnfnovo.",
	    idprodserv,
	    idprodservformula,
	    qtd,
	    vlritem,
	    vlritemacord,
	    frete,
	    total,
	    tiponf,
	    cst,
	    cfop,
	    ncm,
	    des,
	    basecalc,
	    vicmsdeson,
	    valicms,
	    valipi,
	    aliqbasecal,
	    aliqicms,
	    aliqicmsint,
	    aliqipi,
	    pis,
	    cofins,
	    obs,
	    nitemped,
	    xped,
	    nfe,
	    cert,
	    icmsufdest,
	    icmsufremet,
	    indiedest,
	    manual,
	    validade,
	    previsaoent,
	    cnpjtomador,
	    remcnpj,
	    remnome,
	    destcnpj,
	    destnome,
	    ord,
	    collapse,
	    '".$_SESSION["SESSAO"]["USUARIO"]."',
	    sysdate(),
	    '".$_SESSION["SESSAO"]["USUARIO"]."',
	     sysdate()
	    from nfitem where idnf = ".$idnforigem."
	    )";
	    d::b()->query($sql) or die($sql."Erro ao copiar itens da nota fiscal: <br>".mysqli_error());
}

$idnfcp = $_POST["_x_i_nf_idobjetosolipor"];
$tipoobjetosolipor = $_POST["_x_i_nf_tipoobjetosolipor"];

if(!empty($idnfcp) and !empty($_SESSION["_pkid"]) and $tipoobjetosolipor=='nf'){
    
    $sql="INSERT INTO laudo.nfitem
            (
            idempresa,idnf,idprodserv,idprodservformula,qtd,vlritem,vlrliq,vlritemacord,frete,total,tiponf,
            cst,cfop,ncm,des,basecalc,vicmsdeson,valicms,valipi,aliqbasecal,aliqicms,aliqicmsint,aliqipi,piscst,pis,
            confinscst,cofins,obs,nitemped,xped,nfe,cert,icmsufdest,icmsufremet,indiedest,
            collapse,criadopor,criadoem,alteradopor,alteradoem)
            (
            select 
            idempresa,".$_SESSION["_pkid"].",idprodserv,idprodservformula,qtd,vlritem,vlrliq,vlritemacord,frete,total,tiponf,
            cst,cfop,ncm,des,basecalc,vicmsdeson,valicms,valipi,aliqbasecal,aliqicms,aliqicmsint,aliqipi,piscst,pis,
            confinscst,cofins,obs,nitemped,xped,nfe,cert,icmsufdest,icmsufremet,indiedest,
            collapse,'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate(),'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate()
            from nfitem where idnf=".$idnfcp."
            )";
     d::b()->query($sql) or die($sql."Erro ao copiar ctrol D itens da nota fiscal: <br>".mysqli_error());
}

?>
