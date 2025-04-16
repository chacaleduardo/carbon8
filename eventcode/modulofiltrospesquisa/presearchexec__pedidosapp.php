<?/*
 * SE for representante so listar
 * clientes que o representante for contato
 * contatos que sejao de clientes representados pelo representante
 * contatos que nao possuam cliente
 */
 /*
if($_SESSION["SESSAO"]["IDTIPOPESSOA"]==15){
	/*
    $_SESSION["SEARCH"]["WHERE"][]="(
				    exists (select 1 from pessoacontato c where c.idpessoa = a.idpessoa and c.idcontato=".$_SESSION["SESSAO"]["IDPESSOA"].")  					
				    )and a.comissao='Y'
				   ";
		*/		
/*
	$_SESSION["SEARCH"]["WHERE"][]=" a.comissao='Y' ";
    
}elseif($_SESSION["SESSAO"]["IDTIPOPESSOA"]==1 
            and $_SESSION["SESSAO"]["OBRIGATORIOCONTATO"]=="Y"
                and $_GET['_modulo']=='pedidorepresentante'){
    
	/*
    $sqllp="select * from divisao where status='ATIVO' and idpessoa= ".$_SESSION["SESSAO"]["IDPESSOA"]."";
    $reslp= d::b()->query($sqllp);
    $qtddiv= mysqli_num_rows($reslp);
    if($qtddiv<1){
        $_SESSION["SEARCH"]["WHERE"][]="(
				    exists (select 1 from pessoacontato c where c.idpessoa = a.idpessoa and c.idcontato=".$_SESSION["SESSAO"]["IDPESSOA"].")  					
				    )and a.comissao='Y'
                            ";
    }
	
	
	$_SESSION["SEARCH"]["WHERE"][]=" a.comissao='Y' ";
    
}
*/	

$_SESSION["SEARCH"]["WHERE"][]=" a.status in ('ENVIADO','CONCLUIDO') ";
