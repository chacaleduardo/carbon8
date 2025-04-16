<?
require_once("../../inc/php/functions.php");

$modulo = $_GET['_modulo'];

if(!empty($_GET["ocultar"])){
    $ocultar = "AND fs.ocultar IN ('" . implode("','", explode(',', $_GET["ocultar"])) . "')";
}else{
    $ocultar = "";
}

$sql = "SELECT 
    fs.idfluxostatus, s.rotulo, f.idobjeto, fs.ordem
FROM
    fluxo f
        JOIN
    fluxostatus fs ON fs.idfluxo = f.idfluxo
        JOIN
    carbonnovo._status s ON s.idstatus = fs.idstatus
   WHERE
    f.status = 'ATIVO'
        AND f.modulo = '$modulo'
        AND f.tipoobjeto = 'subtipo'
		".$ocultar."
ORDER BY f.idobjeto , fs.ordem" ;

$res=mysql_query($sql) or die("Status formalisação - Erro ao recuperar status: ".mysql_error());
$virg="";
$json = "";
while($row=mysql_fetch_assoc($res)){
    $json.=$virg.'{"'.$row['idfluxostatus'].'":"'.$row['idobjeto'].' - '.$row['rotulo'].'"}';
    $virg=",";
}
echo("[".$json."]");
?>  