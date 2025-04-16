<?
#CVG: Este cabecalho deve estar presente em qualquer evento
#15/02/2019
require_once("../../inc/php/functions.php");
if(!($_SESSION["SESSAO"]["LOGADO"])) die();

//Monta o SQL
$sql = "select 
            d.iddevice, d.descricao
        from 
            device d
        where 
            d.status = 'ATIVO' and d.descricao != 'BLOCO A - TI'
        order by descricao asc";

$rsql = mysql_query($sql);

if(!$rsql){
    die("Erro ao listar devices: ".mysql_error());
}

//monta o resultado em formato JSON para autocomplete
//$r[0]=primeira coluna select  /  $r[1]=segunda coluna select
echo "[";
while($r = mysql_fetch_array($rsql)){
    echo $virg.'{"'.retira_acentos($r[0]).'":"'.str_replace("","",$r[1]).'"}';
    $virg=",";
}
echo "]";