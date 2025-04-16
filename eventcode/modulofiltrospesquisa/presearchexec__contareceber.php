<?

$idusuario= $_SESSION["SESSAO"]["IDPESSOA"];//$perfilpag_idpessoa;

$sql=" select * from pessoa where flgsocio='Y' and idpessoa=".$idusuario;
$res = d::b()->query($sql) or die("Erro ao buscar usuário: " . mysqli_error(d::b()));
$flgdiretor=mysqli_num_rows($res);

//verificar se e usuario com modulo master restaurar ativo
$sqlm=" select if('restaurar' in (".getModsUsr("SQLWHEREMOD")."),'Y','N') as master";
$resm = d::b()->query($sqlm) or die("Falha ao pesquisar SQLWHEREMOD usuario master : " . mysqli_error(d::b()) . "<p>SQL: $sqlm");
$rowm=mysqli_fetch_assoc($resm);

    if($flgdiretor<1){       
        if($rowm['master']!="Y"){
            $_SESSION["SEARCH"]["WHERE"]["visivel "]=" visivel  = 'S'";
        }      
    }

    $_SESSION["SEARCH"]["WHERE"]["tipo "]=" tipo  = 'C'";
?>