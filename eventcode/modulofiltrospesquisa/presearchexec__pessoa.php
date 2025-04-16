<?
/*
 * SE for representante so listar
 * clientes que o representante for contato
 * contatos que sejao de clientes representados pelo representante
 * contatos que nao possuam cliente
 */
if($_SESSION["SESSAO"]["IDTIPOPESSOA"]==15){
	/*
    $_SESSION["SEARCH"]["WHERE"][]="(
				    exists (select 1 from pessoacontato c where c.idpessoa = a.idpessoa and c.idcontato=".$_SESSION["SESSAO"]["IDPESSOA"].") 
				    or exists (select 1 from vwcontatorepresentante c where c.idpessoa = a.idpessoa and c.idrepresentante=".$_SESSION["SESSAO"]["IDPESSOA"].") 
					or ( a.idtipopessoa = 3 and not exists (select 1 from pessoacontato c where c.idcontato = a.idpessoa))
				    )
				    and a.idtipopessoa in (2,3) 
																		and a.status!='INATIVO'";
		*/
    //print_r( $_SESSION["SEARCH"]["WHERE"]);
  // die();
    
}

?>
