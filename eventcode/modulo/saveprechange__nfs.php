<?

//print_r($_SESSION["arrpostbuffer"]);die;
 //print_r($_POST);
 //die;
//echo(count($_SESSION["arrpostbuffer"]));
$tipo          = substr($_POST["tipo"], 0, 16); 

$_datapagto = $_SESSION['arrpostbuffer']['x9']['i']['contapagaritem']['datapagto'];
if(!empty($_datapagto)){
    $_SESSION['arrpostbuffer']['x9']['i']['contapagaritem']['datapagto']=dma($_datapagto);
    
}

 
//inserir itens na notafical serialize do jquery altera descricao para utf8 o que desconfigura a descricao
//Usar assim
if($tipo== "inotafiscalitens" or $tipo== "dnotafiscalitens"){ //insert ou delete
    $arrpb=$_SESSION["arrpostbuffer"];
    reset($arrpb);
     $l=999;

    while (list($linha, $arrlinha) = each($arrpb)) {
	    while (list($acao, $arracao) = each($arrlinha)) {
		    if($acao=="i"){//INSERT
				while (list($tab, $arrtab) = each($arracao)){
					//Se for tabela de notafiscalitens
					if($tab=="notafiscalitens"){
					//Enviar o campo para a pagina de submit

					//print_r($_SESSION["arrpostbuffer"][$linha][$acao][$tab]["descricao"]);
					$idprodserv=$_SESSION["arrpostbuffer"][$linha][$acao][$tab]["idprodserv"];
					$idnotafiscal=$_SESSION["arrpostbuffer"][$linha][$acao][$tab]["idnotafiscal"];
					
					
					$_sqlx = "select p.vendadireta,n.*
								from notafiscal n 
									join pessoa p on(p.idpessoa=n.idpessoa)
								where n.idnotafiscal=".$idnotafiscal;

					//echo $_sql;	
					$resx = d::b()->query($_sqlx) or die($_sql."Erro ao retornar se e venda direta: ".mysqli_error()." sql:".$_sqlx);
					$rowuf=mysqli_fetch_assoc($resx);
					$vendadireta=$rowuf["vendadireta"];		

					//Apagar da tabela tempnfs a pessoa que foi inserido os itens da NF
					$sqlTemp = "DELETE fROM tempnfs WHERE idpessoa =".$rowuf['idpessoa']; 
					d::b()->query($sqlTemp) or die("Erro ao remover dados da tempnfs".$sqld);

					$_sql = "select ps.*                                    
							from prodserv ps 
						where ps.idprodserv =".$idprodserv;

					//echo $_sql;	
					$res = d::b()->query($_sql) or die($_sql."Erro ao retornar serviço: ".mysqli_error());
					$row=mysqli_fetch_assoc($res);
					$comissionado=$row['comissionado'];

					$valor=$row["vlrvenda"];// seta como valor o valor do produto
					$comissaodf=$row["comissao"];// seta como comissao o valor do produto
					$idobcomissaodf=$row['idprodserv'];
					$obcomissaodf='prodserv';
				  
				
					$_SESSION["arrpostbuffer"][$linha][$acao][$tab]["descricao"]=$row['descr'];

					$fiscalitens = new Insert();
					$fiscalitens->setTable("notafiscalitens");
					$fiscalitens->idnotafiscal=$_SESSION["arrpostbuffer"][$linha][$acao][$tab]["idnotafiscal"]; 
					$fiscalitens->idamostra=$_SESSION["arrpostbuffer"][$linha][$acao][$tab]["idamostra"]; 
					$fiscalitens->idresultado=$_SESSION["arrpostbuffer"][$linha][$acao][$tab]["idresultado"];
					$fiscalitens->idprodserv=$_SESSION["arrpostbuffer"][$linha][$acao][$tab]["idprodserv"]; 
					$fiscalitens->valor=$_SESSION["arrpostbuffer"][$linha][$acao][$tab]["valor"]; 
					$fiscalitens->desconto=$_SESSION["arrpostbuffer"][$linha][$acao][$tab]["desconto"]; 
					$fiscalitens->descricao= $_SESSION["arrpostbuffer"][$linha][$acao][$tab]["descricao"]; 
					$fiscalitens->quantidade=$_SESSION["arrpostbuffer"][$linha][$acao][$tab]["quantidade"]; 
					$Nidnfitem=$fiscalitens->save();
				
					unset($_SESSION["arrpostbuffer"][$linha]);
					
					$_SESSION["arrpostbuffer"][$linha]['u'][$tab]["idnotafiscal"]=$idnotafiscal;
					$_SESSION["arrpostbuffer"][$linha]['u'][$tab]["idnotafiscalitens"]=$Nidnfitem;


					$sqlcont="select c.idcontrato,o.idpessoa,ifnull(o.comissao,0) as comissao
								from contratopessoa p 
								join contrato c on(c.idcontrato=p.idcontrato and c.tipo='S')
								join desconto d on(d.idcontrato=c.idcontrato)
								join contratocomissao o on(o.iddesconto = d.iddesconto)
							where p.idpessoa = ".$rowuf['idpessoa']."
							and d.idtipoteste=".$idprodserv;              

					$rescont=d::b()->query($sqlcont) or die("Falha ao buscar comissoes do contrato sql=".$sqlcont);
					$qtdcom=mysqli_num_rows($rescont);
					if($qtdcom>0){
						
						while($rcom=mysqli_fetch_assoc( $rescont)){
						
							$infitemc = new Insert();
							$infitemc->setTable("notafiscalitenscomissao");
							$infitemc->idnotafiscalitens=$Nidnfitem; 
							$infitemc->idpessoa=$rcom['idpessoa']; 
							$infitemc->pcomissao=$rcom['comissao']; 
							$infitemc->idobjeto=$rcom['idcontrato']; 
							$infitemc->tipoobjeto='contrato'; 
							$idnfitemc=$infitemc->save();
						}
					}elseif($comissaodf>0){

						
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
							$infitemc->setTable("notafiscalitenscomissao");
							$infitemc->idnotafiscalitens=$Nidnfitem; 
							$infitemc->idpessoa=$rowc['idcontato']; 
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
									join divisaoplantel dp on(dp.iddivisao=d.iddivisao and d.idplantel=".$rosp['idplantel'].")
									where  i.idprodserv = ".$idprodserv." 
									and d.status='ATIVO'
									and d.tipo='SERVICO' group by d.idpessoa";
							$resg=d::b()->query($sqg) or die("Falha ao buscar comissao do gestorsql=".$sqg);

							while($rowg=mysqli_fetch_assoc($resg)){
								$infitemc = new Insert();
								$infitemc->setTable("notafiscalitenscomissao");
								$infitemc->idnotafiscalitens=$Nidnfitem; 
								$infitemc->idpessoa=$rowg['idpessoa']; 
								$infitemc->pcomissao= $rowg['comissaogest']; 
								$infitemc->idobjeto= $rowg['iddivisao']; 
								$infitemc->tipoobjeto= 'divisao'; 
								$idnfitemc=$infitemc->save();

							}
						}

					}else{// ligação funcionario cliente
					

						$sqlc="select                        
							c.idcontato,c.participacaoserv,c.idpessoacontato                     
							from pessoa p ,pessoacontato c
							where p.status='ATIVO'
							and p.idtipopessoa in(12,1)
							and c.participacaoserv > 0
							and  p.idpessoa = c.idcontato
							and c.idpessoa = ".$rowuf['idpessoa']." order by nome";
						$resc=d::b()->query($sqlc) or die("Falha ao buscar responsaveis do cliente sql=".$sqlc);

						while($rowc=mysqli_fetch_assoc($resc)){
							$infitemc = new Insert();
							$infitemc->setTable("notafiscalitenscomissao");
							$infitemc->idnotafiscalitens=$Nidnfitem; 
							$infitemc->idpessoa=$rowc['idcontato']; 
							$infitemc->pcomissao= $rowc['participacaoserv']; 
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
									join divisaoplantel dp on(dp.iddivisao=d.iddivisao and d.idplantel=".$rosp['idplantel'].")
									where  i.idprodserv = ".$idprodserv." 
									and d.status='ATIVO'
									and d.tipo='SERVICO' group by d.idpessoa";
							$resg=d::b()->query($sqg) or die("Falha ao buscar comissao do gestorsql=".$sqg);

							while($rowg=mysqli_fetch_assoc($resg)){
								$infitemc = new Insert();
								$infitemc->setTable("notafiscalitenscomissao");
								$infitemc->idnotafiscalitens=$Nidnfitem; 
								$infitemc->idpessoa=$rowg['idpessoa']; 
								$infitemc->pcomissao= $rowg['comissaogest']; 
								$infitemc->idobjeto= $rowg['iddivisao']; 
								$infitemc->tipoobjeto= 'divisao'; 
								$idnfitemc=$infitemc->save();

							}
						}


					}
					}				
				}
		    }elseif($acao=="d"){//DELETE
			while (list($tab, $arrtab) = each($arracao)){
			   
			    $tmpidnotafiscal = $_SESSION["arrpostbuffer"][$linha][$acao][$tab]["idnotafiscal"];
			    $tmpdescricao = str_replace('+', ' ', $_SESSION["arrpostbuffer"][$linha][$acao][$tab]["descricao"]);
			    if(empty($tmpidnotafiscal)or !is_numeric($tmpidnotafiscal)or empty($tmpdescricao)){
				echo("Erro ao retirar itens da nota fiscal. A linha selecionada n&atilde;o enviou informa&ccedil;&otilde;s para apagar os itens:");
				echo("<br>Id NF: [".$tmpidnotafiscal."]");
				die("<br>Descri&ccedil;&atilde;o: [".$tmpdescricao."]");
			    }else{
				$tmpsqldel = "select * from notafiscalitens where idnotafiscal = ".$tmpidnotafiscal." and descricao = '".$tmpdescricao."';";
				//echo($tmpsqldel);
				$resdel=d::b()->query($tmpsqldel) or die("Erro ao retirar itens da Nota Fiscal: <br>".mysql_error());
				$y=1;
				while($rowdel= mysqli_fetch_assoc($resdel)){
				    if($y==1){
					$_SESSION["arrpostbuffer"][$linha][$acao][$tab]["idnotafiscalitens"]=$rowdel['idnotafiscalitens'];
					$y=$y+1;
				    }else{
				       $_SESSION["arrpostbuffer"][$l][$acao][$tab]["idnotafiscalitens"]=$rowdel['idnotafiscalitens']; 
				       $l=$l+1;
				    }					
				}
			    }
			}
		    }
	    }
    }
}elseif($tipo!="idescritivo"){//limpar array de itens checkados na tela
    $arrpb=$_SESSION["arrpostbuffer"];
    reset($arrpb);
    //Gerar PARTIDA para qualquer linha que realize insert na lote
    while (list($linha, $arrlinha) = each($arrpb)) { 
        if($linha!='ps'){
            while (list($acao, $arracao) = each($arrlinha)) {
                if($acao=="i"){
                    while (list($tab, $arrtab) = each($arracao)){
                        //Se for tabela de notafiscalitens deletar o array
                        if($tab=="notafiscalitens"){
                            unset($_SESSION["arrpostbuffer"][$linha]);
                        }
                    }
                }elseif($acao=="d"){
                    while (list($tab, $arrtab) = each($arracao)){			   
                        if($tab=="notafiscalitens"){
                            unset($_SESSION["arrpostbuffer"][$linha]);
                        }
                    }
                }
            }
        }elseif($linha=='ps' and !empty($_SESSION["arrpostbuffer"]['ps']['i']['notafiscalitens']['idprodserv'])){
            $idprodserv=$_SESSION["arrpostbuffer"]['ps']['i']['notafiscalitens']['idprodserv'];
            $descr = traduzid('prodserv', 'idprodserv', 'descr', $idprodserv);
            $vlrvenda = traduzid('prodserv', 'idprodserv', 'vlrvenda', $idprodserv);
            $_SESSION["arrpostbuffer"]['ps']['i']['notafiscalitens']['descricao']=$descr;
            $_SESSION["arrpostbuffer"]['ps']['i']['notafiscalitens']['valor']=$vlrvenda;
            
           
        }
    }
}
//echo($tipo);
///print_r($_SESSION["arrpostbuffer"]);
//die;

retarraytabdef('fluxostatushist');

//Inserir na Hist e Atualizar o status do idfluxostatus - LTM (10/03/2021) 
if($_POST["_1_u_notafiscal_status"] == 'PENDENTE')
{
	$sf="SELECT fs.idfluxostatus, 
				s.statustipo
			FROM fluxo f JOIN fluxostatus fs ON f.idfluxo = fs.idfluxo AND f.modulo = 'nfs' AND f.status = 'ATIVO'
			JOIN "._DBCARBON."._status s ON fs.idstatus = s.idstatus AND s.statustipo in ('ABERTO','PENDENTE')";
	$rf = d::b()->query($sf) or die("Erro ao buscar configuração da forma de pagamento: sql=".$sf." mysql".mysqli_error(d::b()));

	$i = 1;
	while($fluxo = mysqli_fetch_assoc($rf))
	{
		if($fluxo['statustipo'] == 'ABERTO'){ $status = 'ATIVO'; } else { $status = 'PENDENTE'; }
		$_SESSION["arrpostbuffer"]['fh'.$i]['i']['fluxostatushist']['idfluxostatus'] = $fluxo['idfluxostatus'];
		$_SESSION["arrpostbuffer"]['fh'.$i]['i']['fluxostatushist']['idmodulo'] = $_POST['_1_u_notafiscal_idnotafiscal'];
		$_SESSION["arrpostbuffer"]['fh'.$i]['i']['fluxostatushist']['modulo'] = 'nfs';
		$_SESSION["arrpostbuffer"]['fh'.$i]['i']['fluxostatushist']['status'] = $status;
		$_SESSION["arrpostbuffer"]['fh'.$i]['i']['fluxostatushist']['idempresa'] = cb::idempresa();
		$_SESSION["arrpostbuffer"]['fh'.$i]['i']['fluxostatushist']['criadopor'] = $_SESSION["SESSAO"]["USUARIO"];
		$_SESSION["arrpostbuffer"]['fh'.$i]['i']['fluxostatushist']['criadoem'] = dmahms(sysdate());
		$_SESSION["arrpostbuffer"]['fh'.$i]['i']['fluxostatushist']['alteradopor'] = $_SESSION["SESSAO"]["USUARIO"];
		$_SESSION["arrpostbuffer"]['fh'.$i]['i']['fluxostatushist']['alteradoem'] = dmahms(sysdate());
		//Busca a classe para inserir o criadoem para inserir no array
		//$foo = new ColunasAuditoria($_SESSION["arrpostbuffer"]['fh'.$i]['i']['fluxostatushist']);

		if($fluxo['statustipo'] == 'PENDENTE')
		{
			$_SESSION["arrpostbuffer"]['1']['u']['notafiscal']['idfluxostatus'] = $fluxo['idfluxostatus'];
		}
		$i++;
	}	
}

//Gerar a configuração das parcelas 
$idnfparc=$_SESSION['arrpostbuffer']['parc']['u']['notafiscal']['idnotafiscal'];
$parc= $_SESSION['arrpostbuffer']['parc']['u']['notafiscal']['qtdparcelas'];
if(!empty($idnfparc) and !empty($parc) and $idnfparc != "undefined"){
    
    $sql="update nfsconfpagar set proporcao=null where idnotafiscal=".$idnfparc;
    $res=d::b()->query($sql) or die("Falha ao zerar a proporcao de pagamento sql=".$sql);
       
    $sql="select * from nfsconfpagar where idnotafiscal=".$idnfparc." order by idnfsconfpagar desc";
    $res=d::b()->query($sql) or die("Falha ao buscar configuracoes de pagamento sql=".$sql);
    $qtd=mysqli_num_rows($res);
    
    if($qtd > $parc){
        
        while($row=mysqli_fetch_assoc($res)){
            $sqld="delete  from nfsconfpagar where idnfsconfpagar=".$row['idnfsconfpagar'];
            $resd=d::b()->query($sqld) or die("Falha ao retirar configuracao de pagamento excedente sql=".$sqld);
            $qtd=$qtd-1;
            if($qtd==$parc){
                break; 
            }
        }
    }elseif($qtd < $parc){
       
        for($v = $qtd; $v < $parc; $v++) {
            $insnfconfpagar = new Insert();
            $insnfconfpagar->setTable("nfsconfpagar");
            $insnfconfpagar->idnotafiscal=$idnfparc;    
            $idnfconfpagar=$insnfconfpagar->save();
        }
         
    }
    
}//if(!empty($idnfparc) and !empty($parc)){

// gerar configuração das parcelas caso não exista
$idnf=$_SESSION['arrpostbuffer']['1']['u']['notafiscal']['idnotafiscal'];
if(!empty($idnf)){
	$sql="select c.idnfsconfpagar,n.qtdparcelas,n.geracontapagar from notafiscal n left join  nfsconfpagar c on(c.idnotafiscal=n.idnotafiscal)
	where n.idnotafiscal=".$idnf." limit 1";
	$res=d::b()->query($sql) or die("Falha ao buscar se notafiscal tem configuração das parcelas sql=".$sql);
	$row=mysqli_fetch_assoc($res);

	if(empty($row['idnfsconfpagar']) and $row['qtdparcelas'] > 0 and  $row['geracontapagar']=='Y'){

		for($v = 0; $v < $row['qtdparcelas']; $v++) {
            $insnfconfpagar = new Insert();
            $insnfconfpagar->setTable("nfsconfpagar");
            $insnfconfpagar->idnotafiscal=$idnf;    
            $idnfconfpagar=$insnfconfpagar->save();
        }

	}

}