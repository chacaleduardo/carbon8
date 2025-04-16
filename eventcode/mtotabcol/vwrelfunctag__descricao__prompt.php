<?
require_once("../../inc/php/functions.php");
if(!($_SESSION["SESSAO"]["LOGADO"])) die();

$sql = "select 
            t.idtag, t.descricao
        from 
            tag t
        where 
            t.status = 'ATIVO'
        
        order by t.descricao asc";

$rsql = mysql_query($sql);

if(!$rsql){
    die("Erro ao listar devices: ".mysql_error());
}

echo "[";
while($r = mysql_fetch_array($rsql)){
    echo $virg.'{"'.$r[0].'":"'.trim($r[1]).'"}';
    $virg=",";
}
echo "]";