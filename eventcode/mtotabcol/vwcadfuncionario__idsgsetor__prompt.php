<?
#CVG: Este cabecalho deve estar presente em qualquer evento
#19/02/2019
require_once("../../inc/php/functions.php");
if(!($_SESSION["SESSAO"]["LOGADO"])) die();

//Monta o SQL
$sql = "SELECT
            s.idsgsetor, CONCAT(e.sigla,' - ',s.setor) AS setor
        FROM sgsetor s
        JOIN empresa e ON(e.idempresa = s.idempresa)
        WHERE 1
        ".idempresaFiltros("s")."
        AND s.status = 'ATIVO'
        ORDER BY s.setor;";

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