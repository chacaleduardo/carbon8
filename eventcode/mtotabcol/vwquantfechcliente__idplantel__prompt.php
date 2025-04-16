<?include_once("../../inc/php/functions.php");

if(cb::idempresa()==8 || cb::idempresa()==9){
	$empresa="";
} else {
	$empresa="and e.idempresa=".cb::idempresa();
}

$sql="select p.idplantel,p.plantel,e.sigla from plantel p
join empresa e on (e.idempresa=p.idempresa)
where p.status='ATIVO'
AND EXISTS
	(
		select 1 from objempresa oe where oe.objeto = 'pessoa' 
		and oe.idobjeto = ".$_SESSION["SESSAO"]["IDPESSOA"]."
		and oe.empresa = e.idempresa
		".$empresa."
	)
	AND EXISTS
	(
		select 1 from  "._DBCARBON."._modulo m 
			JOIN
		objempresa oe on oe.objeto = 'modulo' and oe.idobjeto = m.idmodulo         
        where m.modulo = '".$_GET['_modulo']."'
	)
order by e.idempresa, p.plantel asc";


$res=mysql_query($sql) or die(mysql_error()." Erro ao buscar plantel sql=".$sql);
$virg="";
$json="[";
while($row=mysql_fetch_assoc($res)){
	$json.=$virg.'{"'.$row['idplantel'].'":"'.$row['sigla'].' - '.$row['plantel'].'"}';
	$virg=",";
}
$json.="]";
echo($json);
