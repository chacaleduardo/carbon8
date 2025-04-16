<?
/*
 * gerar os itens da folha de pagamento
 * 
 */

//print_r($_SESSION['arrpostbuffer']); die;


$iu = $_SESSION['arrpostbuffer']['1']['u']['folha']['idfolha'] ? 'u' : 'i';

$id_folha =$_SESSION["_pkid"];

$idpessoa=$_SESSION['arrpostbuffer']['x']['i']['folhaitem']['idpessoa'];
$idfolha=$_SESSION['arrpostbuffer']['x']['i']['folhaitem']['idfolha'];
$tipo=$_SESSION['arrpostbuffer']['x']['i']['folhaitem']['tipo'];


//se for insert
if($iu == "i" and !empty($id_folha) and empty($idpessoa)){// gerar os itens da folha e a planilha de refeições	
	
	$idfolhacopia=$_SESSION['arrpostbuffer']['1']['i']['folha']['idfolhacopia'];
	
	
	
	mysql_query("START TRANSACTION") or die("gera agentes: Falha 1 ao abrir transacao: ".mysql_error());
	
		
		if(empty($idfolhacopia)){
			$sql = " INSERT INTO folhaitem(idempresa,idfolha,idpessoa,salario,irrf,insalubridade,inss,vale,emprestimo,unimedmens) 
			(select ".$_SESSION["SESSAO"]["IDEMPRESA"].",".$id_folha.",idpessoa,salario,irrf,insalubridade,(((salario+insalubridade)*aliqinss)/100),salario*0.3,emprestimo,unimedmens
				from pessoa 
				where status='ATIVO' and idtipopessoa = 1 and salario > 0  ".getidempresa('idempresa','pessoa').");";
		}else{
			$sql = "INSERT INTO `folhaitem`
				(`idempresa`,`idfolha`,`idpessoa`,`dia`,`salario`,`insalubridade`,`horaextra`,`inss`,`ferias`,`ferias13`,`trocomes`,`trocomesant`,
				`adicnot`,`irrf`,`irferias`,`vale`,`valetransp`,`differias`,`trferias`,`estouromes`,`insssferias`,`adiantferias`,`adiantdt`,
				`estouromesant`,`contsindical`,`habit`,`obs`,`obsint`,`farmacia`,`unimedmens`,`unimed`,`alimentacao`,`emprestimo`,`dsr`,`dsr_hr_extra`,
				`hr_auxilio_doenca`,`insal_s_sal_min_aux_doenca`,`med_hrs_ext_aux_doenca`,`insal_s_sal_min_13_sal_adto`,`med_hrs_ext_s_ferias`,`hrs_abono_pec_diurna`,
				`med_hrs_ext_abono_pec_diurno`,`insal_s_sal_min_abono_pec`,`13_abono_pec`,`desc_adiant_ferias`,`fgts_s_13_sal`,`fgts`,`outros`,`imprime`,`tipo`)
			(select `idempresa`,".$id_folha.",`idpessoa`,`dia`,`salario`,`insalubridade`,`horaextra`,`inss`,`ferias`,`ferias13`,`trocomes`,`trocomesant`,
				`adicnot`,`irrf`,`irferias`,`vale`,`valetransp`,`differias`,`trferias`,`estouromes`,`insssferias`,`adiantferias`,`adiantdt`,
				`estouromesant`,`contsindical`,`habit`,`obs`,`obsint`,`farmacia`,`unimedmens`,`unimed`,`alimentacao`,`emprestimo`,`dsr`,`dsr_hr_extra`,
				`hr_auxilio_doenca`,`insal_s_sal_min_aux_doenca`,`med_hrs_ext_aux_doenca`,`insal_s_sal_min_13_sal_adto`,`med_hrs_ext_s_ferias`,`hrs_abono_pec_diurna`,
				`med_hrs_ext_abono_pec_diurno`,`insal_s_sal_min_abono_pec`,`13_abono_pec`,`desc_adiant_ferias`,`fgts_s_13_sal`,`fgts`,`outros`,`imprime`,`tipo`
			 from folhaitem where idfolha=".$idfolhacopia.")";
		}
		$res = mysql_query($sql);
				
		//echo($sql);
		if(!$res){
			mysql_query("ROLLBACK;");
			die("1-Falha ao gerar itens da folha: " . mysql_error() . "<p>SQL: ".$sql);
		}

		$sql0="insert into refeicao(idempresa,idfolha,status) values(".$_SESSION["SESSAO"]["IDEMPRESA"].",".$id_folha.",'ATIVA');";
		$res0 = mysql_query($sql0);
		$id_refeicao=mysql_insert_id();
		
		$sql1 = " INSERT INTO refeicaoitem(idempresa,idrefeicao,idpessoa) 
		(select ".$_SESSION["SESSAO"]["IDEMPRESA"].",".$id_refeicao.",idpessoa
			from pessoa 
			where status='ATIVO' and idtipopessoa = 1  ".getidempresa('idempresa','pessoa').")";
		$res1 = mysql_query($sql1);
				
		echo($sql1);
		if(!$res1){
			mysql_query("ROLLBACK;");
			die("1-Falha ao gerar itens da refeicao: " . mysql_error() . "<p>SQL: ".$sql1);
		}else{
		
		mysql_query("COMMIT") or die("erro: Falha ao efetuar COMMIT [eventcode]: ".mysql_error());
		}
		

}elseif(!empty($idpessoa) and !empty($idfolha) and $tipo!='F'){// gerar os dados quando o funcionario for incluido na drop	
	
	
		$sql="update pessoa p,folhaitem i set i.salario = p.salario,i.insalubridade = p.insalubridade,i.emprestimo = p.emprestimo,i.irrf=p.irrf
		where p.idpessoa = i.idpessoa 
		and i.idfolha =".$idfolha."
		and i.idpessoa =".$idpessoa;
		$res=mysql_query($sql) or die('Erro ao altualizar o salário do item sql='.$sql);
		
		$sql0="select idrefeicao from refeicao where status='ATIVA' and idfolha=".$idfolha;
		$res0=mysql_query($sql0) or die('erro ao buscar refeição correspondente da folha'.$sql0);
		$row0=mysql_fetch_assoc($res0);
		
		
		$sql1 = " INSERT INTO refeicaoitem(idempresa,idrefeicao,idpessoa) values
		(".$_SESSION["SESSAO"]["IDEMPRESA"].",".$row0['idrefeicao'].",".$idpessoa.")";
		$res1 = mysql_query($sql1) or die("1-Falha ao gerar itens da refeicao: " . mysql_error() . "<p>SQL: ".$sql1);	

}


