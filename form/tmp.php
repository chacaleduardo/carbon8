<?


ini_set("display_errors","1");
error_reporting(E_ALL);
require_once("../inc/php/functions.php");

$sql="select l.idprodserv,a.idpessoa,GROUP_CONCAT(l.idlote SEPARATOR ';') as lotes
from lote l join lotefracao lf on (lf.idlote=l.idlote and lf.status='DISPONIVEL')
join resultado r on(r.idresultado=l.idobjetosolipor)
join amostra a on(a.idamostra=r.idamostra)
where l.tipoobjetosolipor ='resultado'
and l.idempresa = 1
and not exists (select 1 from lotepool p where p.idlote=l.idlote)
and l.status='APROVADO' group by l.idprodserv,a.idpessoa";
$res= d::b()->query($sql) or die("erro 1");

while($row=mysqli_fetch_assoc($res)){
$sqli="INSERT INTO pool
(idempresa,ord,criadopor,criadoem,alteradopor,alteradoem)
VALUES
(1,1,'hermesp',sysdate(),'hermesp',sysdate())";
$resi= d::b()->query($sqli) or die("erro inseririr");
$idpool= mysqli_insert_id(d::b());

	$lotes = explode(";",$row['lotes']);
	foreach($lotes as $idlote) {
		$sql2="INSERT INTO lotepool
		(
		idempresa,
		idlote,
		idpool,
		criadopor,
		criadoem,
		alteradopor,
		alteradoem)
		VALUES
		(1,$idlote,$idpool,'hermesp',sysdate(),'hermesp',sysdate());
		";

		$res2= d::b()->query($sql2) or die("erro inseririr lotepool");
	}

}


echo('ok');
?>