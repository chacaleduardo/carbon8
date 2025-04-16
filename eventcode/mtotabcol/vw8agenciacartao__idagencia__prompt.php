<?
#MAF: Este cabecalho deve estar presente em qualquer evento
require_once("../../inc/php/functions.php");
if(!($_SESSION["SESSAO"]["LOGADO"])) die();

//Monta o SQL
$sql = "select idagencia, agencia
		from
			agencia
		where 
			1 ".getidempresa('idempresa','agencia')."
			and status = 'ATIVO' 
		order by agencia";

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