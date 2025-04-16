<?
require_once("../inc/php/functions.php");

$jwt = validaTokenReduzido();

if($jwt["sucesso"] !== true){
    header("HTTP/1.1 401 Unauthorized");
    die;
}

$idnf	= $_GET["idnf"];

if(empty($idnf)){
    die("ID do CTe não informado.");
}

$idnfe =traduzid("nf","idnf","idnfe",$idnf);

if(empty($idnfe)){
    die("Chave do CTe está vazia.");
}

$sql="select * from nfentradaxml x  where tipo='CTE' and chave ='".$idnfe."' and idnf!=".$idnf;
$res =  d::b()->query($sql) or die("Falha ao buscar se XML já foi vinculado : " . mysqli_error() . "<p>SQL:". $sql);  
$qtdb = mysqli_num_rows($res);
if($qtdb > 0){
$row=mysqli_fetch_assoc($res);
    die("Chave ja foi informada em outro CTe, Idnf:".$row['idnf']);
}


$sql="select * from nfentradaxml x  where tipo='CTE' and chave ='".$idnfe."' and (idnf is null or idnf=".$idnf.")";
$res =  d::b()->query($sql) or die("Falha ao buscar XML ja baixado: " . mysqli_error() . "<p>SQL:". $sql);  
$qtdb = mysqli_num_rows($res);

if($qtdb < 1){
    die("Não encontrado XML já baixado do sistema");
}
$row=mysqli_fetch_assoc($res);

$sqlU="update nfentradaxml set idnf='".$idnf."'where idnfentradaxml= ".$row['idnfentradaxml'];
$resU =  d::b()->query($sqlU) or die("Falha ao vincular XML do CTe: " . mysqli_error() . "<p>SQL:". $sqlU);  

$sqlU="update nf set xmlret='".$row['xml']."',envionfe='CONCLUIDA' where idnf= ".$idnf;
$resU =  d::b()->query($sqlU) or die("Falha ao atualizar XML do CTe: " . mysqli_error() . "<p>SQL:". $sqlU);  


echo("XML CTe vinculado com SUCESSO!!!");


?>