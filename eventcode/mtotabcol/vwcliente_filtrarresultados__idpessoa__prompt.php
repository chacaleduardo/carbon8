<?
#MAF: Este cabecalho deve estar presente em qualquer evento
require_once("../../inc/php/functions.php");
if(!($_SESSION["SESSAO"]["LOGADO"])) die();

if($_SESSION["SESSAO"]["IDTIPOPESSOA"]==3 or $_SESSION["SESSAO"]["IDTIPOPESSOA"]==15 or $_SESSION["SESSAO"]["IDTIPOPESSOA"]==16){
	$sqlwhere .= " and idpessoa in(".$_SESSION["SESSAO"]["STRCONTATOCLIENTE"].") ";
	
}elseif($_SESSION["SESSAO"]["IDTIPOPESSOA"]==4){

	$sqlwhere .= " and idpessoa in(".$_SESSION["SESSAO"]["STRCONTATOCLIENTE"].")";

}elseif($_SESSION["SESSAO"]["IDTIPOPESSOA"]==1){

	$sqlwhere .= "";//Recupera todos os clientes

}else{
	die('[{"0":"Tipo de UsuÃ¡rio nÃ£o previsto: ['.$_SESSION["SESSAO"]["IDTIPOPESSOA"].']"}]');
}

//Monta o SQL
$sql = "select idpessoa, nome 
		from pessoa 
		where 
			1 ".getidempresa('idempresa','pessoa')."
			and status = 'ATIVO' 
			and idtipopessoa = 2 
			".$sqlwhere."
		order by nome";

$rsql = mysql_query($sql);

if(!$rsql){
	header("HTTP/1.0 520 Erro inesperado");
	echo 'Erro ao recuperar registros: '.mysql_error();

	if($_SESSION["SESSAO"]["SUPERUSUARIO"]){
		echo "\n".$sql;
	}
	die;
}

//monta o resultado em formato JSON para autocomplete
echo "[";
while($r = mysql_fetch_array($rsql)){
	echo $virg."{\"".$r[0]."\":\"".$r[1]."\"}";
	$virg=",";
}
echo "]";

?>
