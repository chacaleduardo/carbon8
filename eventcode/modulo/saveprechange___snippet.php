<?
$idsnippet = $_POST["_1_u__snippet_idsnippet"];
$notificacao = $_POST["_1_u__snippet_notificacao"];
if(!empty($idsnippet) and $notificacao == 'Y')
{   
    //Valida se tem algum campo setado com Y para cada empresa. Se tiver um campo já não deixa inserir mais. (LTM - 14-08-2020 - 367247)
    $sql = "SELECT idsnippet FROM "._DBCARBON."._snippet WHERE notificacao = 'Y' ".getidempresa('idempresa','_snippet')." and not idsnippet = ".$idsnippet;
    $res = d::b()->query($sql) or die("[saveposchange]-Erro ao buscar _snippet: ".mysqli_error(d::b())."<p>SQL: ".$s); ;
    $ret = mysqli_fetch_assoc($res);
    if(!empty($ret['idsnippet'])){
        die("Já existe notificação setada para esta empresa. É possível apenas uma e que seja no Dashboard.");
    }
}
?>