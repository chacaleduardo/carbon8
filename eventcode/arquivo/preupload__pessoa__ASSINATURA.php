<?

$sd1 = "SELECT *
FROM arquivo 
WHERE tipoobjeto='pessoa'
	AND tipoarquivo='ASSINATURA'
	AND idobjeto = ".$_idobjeto."
ORDER BY
	idarquivo ASC";

$qd1 = d::b()->query($sd1) or die(getNomeArquivo(__FILE__)." #1: ".mysqli_error(d::b()));
$nqd = mysql_num_rows($qd1);
if($nqd > 0){
	$r1= mysqli_fetch_assoc($qd1);
	unlink($r1["caminho"]);
	$sd1="DELETE FROM arquivo WHERE idarquivo=".$r1["idarquivo"];
	d::b()->query($sd1) or die(getNomeArquivo(__FILE__)." #2: ".mysqli_error(d::b()));
}
?>