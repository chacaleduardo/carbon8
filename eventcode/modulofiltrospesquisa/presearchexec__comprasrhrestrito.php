<?

if($_SESSION["SESSAO"]["IDTIPOPESSOA"]==15){

    $sql="select p.idpessoa 
            from pessoacontato c
                join pessoa p on(p.idpessoa=c.idpessoa and p.idtipopessoa = 12) 
        where c.idcontato = ".$_SESSION["SESSAO"]["IDPESSOA"];

    $res=d::b()->query($sql);
    $qtd=mysqli_num_rows($res);
    if($qtd<1){ die("Contato representação sem empresa represetante vinculada");}

    $row=mysqli_fetch_assoc($res);

    if(empty($row['idpessoa'])){ die("Contato representação sem empresa represetante vinculada");}

    $_SESSION["SEARCH"]["WHERE"][]=" idpessoa = ".$row['idpessoa'];
}else{

     $sql="select p.idpessoa 
                from pessoacontato c
                    join pessoa p on(p.idpessoa=c.idpessoa and p.idtipopessoa = 5) 
            where c.idcontato =  ".$_SESSION["SESSAO"]["IDPESSOA"];

    $res=d::b()->query($sql);
        
    $row=mysqli_fetch_assoc($res);

    if(!empty($row['idpessoa'])){ 
        $_SESSION["SEARCH"]["WHERE"][]=" idpessoa = ".$row['idpessoa'];
    }else{
        $_SESSION["SEARCH"]["WHERE"][]=" idpessoa = ".$_SESSION["SESSAO"]["IDPESSOA"];
    }

}

?>