<?require_once("../../inc/php/functions.php");
if(logado()==true){
    $sql="SELECT idtipoacao,tipoacao 
    FROM laudo.tipoacao 
    where vinculo in ('EQUIPAMENTO','SGDOC','PESSOA')
    and status = 'ATIVO' order by tipoacao";
    $res=mysql_query($sql) or die(mysql_error()." Erro ao buscar secretaria sql=".$sql);
    $virg="";
    $json.="[";
    while($row=mysql_fetch_assoc($res)){
            $json.=$virg.'{"'.$row['idtipoacao'].'":"'.$row['tipoacao'].'"}';
            $virg=",";
    }
    $json.="]";

    echo($json);
}
?>