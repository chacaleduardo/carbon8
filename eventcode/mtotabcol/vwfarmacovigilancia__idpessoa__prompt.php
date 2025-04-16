<?
include_once("../../inc/php/functions.php");
if(!($_SESSION["SESSAO"]["LOGADO"])) die();

//Monta o SQL
$sql = "SELECT p.idpessoa, p.nome as nome
          FROM lote l JOIN pessoa p ON l.idpessoa = p.idpessoa
         WHERE 1 ".getidempresa('p.idempresa','pessoa')."
        GROUP BY p.idpessoa
        ORDER BY nome;";

$rsql = mysql_query($sql);

if(!$rsql){
    die("Pessoa: ".mysql_error());
}
echo "[";
$virg="";
while($row4 = mysql_fetch_array($rsql)){
    echo $virg.'{"'.$row4[0].'":"'.str_replace("","",$row4[1]).'"}';
    $virg=",";
}
echo "]";