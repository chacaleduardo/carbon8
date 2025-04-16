<?
if(array_key_exists("STRCONTATOCLIENTE", $_SESSION["SESSAO"]) and array_key_exists("idpessoa", $arrFiltros)){
    $_sqlresultado .= " and idpessoa in( ".$_SESSION["SESSAO"]["STRCONTATOCLIENTE"].",".$_SESSION["SESSAO"]["IDPESSOA"].") ";
}
?>