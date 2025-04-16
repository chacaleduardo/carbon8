<?
#CVG: Este cabecalho deve estar presente em qualquer evento
#19/02/2019
require_once("../../inc/php/functions.php");
if(!($_SESSION["SESSAO"]["LOGADO"])) die();

//Monta o SQL

//Monta o SQL
$sql = "select idtipoprodserv, tipoprodserv 
		from tipoprodserv t
		where status = 'ATIVO'
		".getidempresa('t.idempresa','tipoprodserv')."
			".$sqlwhere."
		order by tipoprodserv";

$rsql = mysql_query($sql);

if(!$rsql){
	die("mtotabcol/autocomplete: Erro ao recuperar registros: ".mysql_error());
}

//monta o resultado em formato JSON para autocomplete
echo "[";
while($r = mysql_fetch_array($rsql)){
	echo $virg."{\"".$r[0]."\":\"".$r[1]."\"}";
	$virg=",";
}
echo "]";

?>
