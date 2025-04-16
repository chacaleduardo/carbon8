<?
#CVG: Este cabecalho deve estar presente em qualquer evento
#19/02/2019
require_once("../../inc/php/functions.php");
if(!($_SESSION["SESSAO"]["LOGADO"])) die();

//Monta o SQL
$sql = "SELECT s.idsgcargo, s.cargo,s.nivel, e.sigla
            FROM sgcargo s
			join empresa e on e.idempresa = s.idempresa
            WHERE s.status = 'ATIVO' order by cargo asc";


$rssqlcargo = mysql_query($sql);

if(!$rssqlcargo){
	echo '[]';
} else {
	$virg = '';
	$json = "[";
	while($rscargo = mysql_fetch_array($rssqlcargo))
	{
		$json.=$virg.'{"'.$rscargo['idsgcargo'].'":"'.$rscargo['sigla'].' - '.$rscargo['cargo'].'  '.$rscargo['nivel'].'"}';
		$virg = ",";
	}
	$json .= "]";
	echo($json);
}
    ?>