<?
require_once("../../inc/php/functions.php");

$sql = "SELECT idpessoa, nome
          FROM pessoa
         WHERE idtipopessoa in (1,2,5,6,7,9,11,12) AND status = 'ATIVO'
         and idempresa = ".cb::idempresa()."
      ORDER BY nome";

echo($json);
$res=mysql_query($sql) or die("Lote - Erro ao recuperar idfluxostatus: ".mysql_error());
$virg="";
$json = "";
while($row=mysql_fetch_assoc($res)){
    $json.=$virg.'{"'.$row['idpessoa'].'":"'.$row['nome'].'"}';
    $virg=",";
}
echo("[".$json."]");
?>  
