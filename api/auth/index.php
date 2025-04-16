<?
require_once "../../inc/php/functions.php";

if(!empty($_POST)
    and (empty($_POST["usuario"]) or empty($_POST["senha"]))
    and !verificaSuperUsuario($_POST["usuario"])){

    cbSetPostHeader("0","alert");
    die("Usuário ou Senha não informados corretamente!");

}elseif($_POST["usuario"]){

    //Impede bruteforce na tela de login
    if(alarmeCheck("tentativaLogin")>=_ALARME_QTD_TENTATIVAS_LOGIN){
        alarmeSet('Y','login','Tentativa invasao',$inusr);
        header("HTTP/1.1 403 Seu login está bloqueado.");
        die;
    }

    $_GET["_idempresa"] = $_headers["_idempresa"];
    //Efetua procedimento de Login
    logincarbon($_POST["usuario"],$_POST["senha"]);
}
?>