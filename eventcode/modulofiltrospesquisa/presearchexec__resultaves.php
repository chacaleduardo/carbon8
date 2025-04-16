<?
//Cada unidade deve ter seu módulo correspondente configurado
//die($_GET["_modulo"]);
$idunidade = getUnidadePadraoMatriz($_GET["_modulo"],cb::idempresa(), cb::habilitarMatriz());

if(empty($idunidade)){
	die("Presearch resultaves: Erro ao recuperar ID da Unidade.\nCada unidade deve ter seu módulo correspondente configurado");
}

//Filtra pela Unidade correspondente ao módulo
$_SESSION["SEARCH"]["WHERE"][] = " idunidade in (".$idunidade.")";

    $_SESSION["SEARCH"]["WHERE"][]=" exists(select 1 
                                            from amostra m
                                             where m.idamostra=a.idamostra
											 and not m.status in ('PROVISORIO', 'CANCELADO')
											)";
	//$_SESSION["SEARCH"]["WHERE"]["idpessoa"] = " idpessoa in(".$_SESSION["SESSAO"]["STRCONTATOCLIENTE"].")

?>