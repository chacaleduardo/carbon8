<?
/*
 * So listar funcionarios da objempresa
 */
/*
    $_SESSION["SEARCH"]["WHERE"][]="(
                                        exists (select 1 from objempresa o where o.idobjeto = a.idpessoa and o.objeto='pessoa'  ".getidempresa('o.empresa','funcionario').") 
                                    )";*/
    //print_r( $_SESSION["SEARCH"]["WHERE"]);
  // die();
   
?>
