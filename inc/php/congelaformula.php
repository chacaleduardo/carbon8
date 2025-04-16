<?
require_once("validaacesso.php");
$arr = array();
var_dump($_GET);

$arr = explode(',',$_GET['_idprodservformula']);

foreach ($arr as $k){
    $sql='select idprodserv,versao from prodservformula where idprodservformula='.$k;
    $res=d::b()->query($sql) or die("Erro ao buscar informacoes da formula: ".mysqli_error(d::b())."<p>SQL: ".$sql);
    if (mysqli_num_rows($res)>0) {
        $row = mysqli_fetch_assoc($res);
        CongelaFormula($row['idprodserv'],$k,$row['versao'],true);
        $upd='UPDATE prodservformula SET editar="N" where idprodservformula='.$k;
        $res=d::b()->query($upd) or die("Erro ao realizar update: ".mysqli_error(d::b())."<p>SQL: ".$upd);
    }
}
