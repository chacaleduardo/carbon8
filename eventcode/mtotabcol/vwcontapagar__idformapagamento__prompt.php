<?
require_once("../../inc/php/functions.php");
require_once("../../inc/php/laudo.php");

cb::idempresa();
$sql = "SELECT f.idformapagamento, f.descricao
          FROM formapagamento f
         WHERE f.status = 'ATIVO'
         ".share::otipo('cb::usr')::formapagamentofiltro("f.idformapagamento")."
      ORDER BY f.formapagamento";
      
$res=mysql_query($sql) or die("Lote - Erro ao recuperar idfluxostatus: ".mysql_error());
$virg="";
$json = "";
while($row=mysql_fetch_assoc($res)){
    $json.=$virg.'{"'.$row['idformapagamento'].'":"'.$row['descricao'].'"}';
    $virg=",";
}
echo("[".$json."]");
?>