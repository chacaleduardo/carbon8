<?
if (
    $_SESSION["SESSAO"]["IDPESSOA"] != 8211 ||      //guilhermealves
    $_SESSION["SESSAO"]["IDPESSOA"] != 1098 ||      //hermesp
    $_SESSION["SESSAO"]["IDPESSOA"] != 98070 ||     //lidianemelo
    $_SESSION["SESSAO"]["IDPESSOA"] != 6494 ||      //marcelocunha
    $_SESSION["SESSAO"]["IDPESSOA"] != 107524 ||    //pedrolima
    $_SESSION["SESSAO"]["IDPESSOA"] != 111565 ){   //ademilsonsantos

}else{

    $_SESSION["SEARCH"]["WHERE"][] = " terceirizado = 'N'";
}
?>