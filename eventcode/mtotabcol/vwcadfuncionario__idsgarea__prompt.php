<?
#CVG: Este cabecalho deve estar presente em qualquer evento
#19/02/2019
require_once("../../inc/php/functions.php");
if(!($_SESSION["SESSAO"]["LOGADO"])) die();

//Monta o SQL
$sql = "SELECT 
            a.idsgarea, CONCAT(e.sigla, ' - ', a.area) as area
        FROM sgarea a
        JOIN empresa e ON(e.idempresa = a.idempresa)
        WHERE 1 ".idempresaFiltros("a")."
        AND a.status = 'ATIVO'
        ORDER BY a.area;";

$rsql = mysql_query($sql);

if(!$rsql){
	die("Prateleira - Erro ao recuperar registros: ".mysql_error());
}

//monta o resultado em formato JSON para autocomplete
//$r[0]=primeira coluna select  /  $r[1]=segunda coluna select
echo "[";
while($r = mysql_fetch_array($rsql)){
    echo $virg.'{"'.$r[0].'":"'.str_replace("","",$r[1]).'"}';
    $virg=",";
}
echo "]";