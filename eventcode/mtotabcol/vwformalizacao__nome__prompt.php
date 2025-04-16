<?
#CVG: Este cabecalho deve estar presente em qualquer evento
#19/02/2019
require_once("../../inc/php/functions.php");
if(!($_SESSION["SESSAO"]["LOGADO"])) die();

//Monta o SQL
$sql = "select 
            distinct p.nome as nome
        from 
            (lote l join pessoa p ON (l.idpessoa = p.idpessoa))
        where 
        1 ".getidempresa('p.idempresa','pessoa')."
        order by
            nome;";

$rsql = mysql_query($sql);

if(!$rsql){
    die("Formalização - Erro ao recuperar registros: ".mysql_error());
}

//monta o resultado em formato JSON para autocomplete
//$r[0]=primeira coluna select  /  $r[1]=segunda coluna select
echo "[";
$virg = "";

while ($r = mysql_fetch_array($rsql)) {
    
    $chave = retira_acentos($r[0]);
    $chave = preg_replace('/[^a-zA-Z0-9_]/', '_', $chave); 

    
    $valor = addslashes(str_replace("'", "", $r[0]));


    echo $virg . json_encode([$chave => $valor]);

    $virg = ",";
}
echo "]";