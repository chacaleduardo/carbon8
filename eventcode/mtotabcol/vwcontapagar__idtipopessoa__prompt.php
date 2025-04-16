
<?
require_once("../../inc/php/functions.php");
if($_SESSION["SESSAO"]["IDTIPOPESSOA"]==15 or $_SESSION["SESSAO"]["IDTIPOPESSOA"]==16){
 echo 
'[
{"2":"Empresa"},
{"3":"Contato Cliente"}
]';
    
}else{
    
    $sql=" select idtipopessoa,tipopessoa from tipopessoa where status='ATIVO'   order by tipopessoa";
    $res=mysql_query($sql) or die(mysql_error()." Erro ao buscar unidade sql=".$sql);
    $virg="";
    $json.="[";
    while($row=mysql_fetch_assoc($res)){
            $json.=$virg.'{"'.$row['idtipopessoa'].'":"'.$row['tipopessoa'].'"}';
            $virg=",";
    }
    $json.="]";
    echo($json);


}

?>