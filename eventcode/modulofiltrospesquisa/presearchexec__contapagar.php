<?

$idusuario= $_SESSION["SESSAO"]["IDPESSOA"];//$perfilpag_idpessoa;

$sql=" select * from pessoa where flgsocio='Y' and idpessoa=".$idusuario;
$res = d::b()->query($sql) or die("Erro ao buscar usuário: " . mysqli_error(d::b()));
$flgdiretor=mysqli_num_rows($res);
if($flgdiretor<1){
	$_SESSION["SEARCH"]["WHERE"]["visivel"]=" visivel  = 'S'";
}

if($_GET['_modulo'] == 'contapagarlogistica'){
	$_SESSION["SEARCH"]["WHERE"][] = " visivel='S' AND ifnull(idpessoa,'') =  ifnull(idpessoap,'') AND tipo = 'D' AND tipoespecifico = 'AGRUPAMENTO' AND idtipopessoa IN (11,5) AND ifnull(idcontaitem,'') <> 13";		 
} elseif($_GET['_modulo'] == 'contapagaritem'){
	$_SESSION["SEARCH"]["WHERE"][] = " tipoespecifico IN ('REPRESENTACAO' , 'AGRUPAMENTO', 'IMPOSTO') AND idformapagamento = idformapagamentofp AND ifnull(idpessoa,'') =  ifnull(idpessoap,'') AND ifnull(idcontaitem,'') <> 13";
}elseif($_GET['_modulo'] == 'contapagarreceber'){
	if($flgdiretor<1){      
		//verificar se e usuario com modulo master restaurar ativo
		$sqlm=" select if('restaurar' in (".getModsUsr("SQLWHEREMOD")."),'Y','N') as master";
		$resm = d::b()->query($sqlm) or die("Falha ao pesquisar SQLWHEREMOD usuario master : " . mysqli_error(d::b()) . "<p>SQL: $sqlm");
		$rowm=mysqli_fetch_assoc($resm);
 
        
        $_SESSION["SEARCH"]["WHERE"]["tiponf "]=" tiponf  != 'D'";

        if(!array_key_exists("quitarrh", getModsUsr("MODULOS"))){
           $_SESSION["SEARCH"]["WHERE"]["tiponf "]=" tiponf  != 'R'";
        }

        if($rowm['master']!="Y"){           
            $_SESSION["SEARCH"]["WHERE"]["visivel "]=" visivel  = 'S'";
		}
	}
       
    $_SESSION["SEARCH"]["WHERE"]["tipo "]=" tipo  = 'D'";     
}
?>