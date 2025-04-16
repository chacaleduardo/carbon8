<?
#CVG: Este cabecalho deve estar presente em qualquer evento
#18/02/2019
require_once("../../inc/php/functions.php");
if(!($_SESSION["SESSAO"]["LOGADO"])) die();

//Monta o SQL
$sql = "select 
            distinct p.nome as nome
        from 
            (bioensaio b left join pessoa p ON (b.idpessoa = p.idpessoa))
        where 
        1 ".getidempresa('b.idempresa','bioensaio')."
        order by
            nome;";

$rsql = mysql_query($sql);

if(!$rsql){
    die("Bioensaio - Erro ao recuperar registros: ".mysql_error());
}

//monta o resultado em formato JSON para autocomplete
//$r[0]=primeira coluna select  /  $r[1]=segunda coluna select
echo "[";
while($r = mysql_fetch_array($rsql)){
    echo $virg.'{"'.retira_acentos($r[0]).'":"'.str_replace("","",$r[0]).'"}';
    $virg=",";
}
echo "]";