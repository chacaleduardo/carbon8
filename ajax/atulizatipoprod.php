<?
require_once("../inc/php/functions.php");
if($_GET['empresa']){
    $idempresa = $_GET['empresa'];
$sql = 'SELECT idtipoprodserv,tipoprodserv from tipoprodserv where status="ATIVO" and idempresa='.$idempresa;
$res =  d::b()->query($sql) or die("Falha ao pesquisar consumos: " . mysqli_error() . "<p>SQL: $sql");
$arrtemp = array();
$i = 0;
while ($row = mysqli_fetch_assoc($res)) {
    $arrtemp[$i]['idtipoprodserv'] = $row['idtipoprodserv'];
    $arrtemp[$i]['tipoprodserv'] = $row['tipoprodserv'];
    $i++;
}
echo json_encode($arrtemp);
}