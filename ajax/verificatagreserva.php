<?require_once("../inc/php/functions.php");

$idsala= $_GET['idsala']; 
$execucao= $_GET['execucao']; 
$execucaofim= $_GET['execucaofim'];
$duracao= $_GET['duracao']; 
$inidloteativ=$_GET['inidloteativ'];
$trava=$_GET['travasala'];



if(empty($idsala) or  empty($execucao) or empty($execucaofim) or empty($inidloteativ)){
	die("PARAMETROS PARA BUSCA DA RESERVA INSUFICIENTE");
}

	$inInicio  = validadatetime($execucao);
        $inFim = validadatetime($execucaofim);
/*
        $inFim = validadatetime($execucao);
	
 //ECHO($idsala." ".$execucao." ".$duracao);
$horas = explode(":", $duracao);
//echo $horas[0]; // piece1
//echo $horas[1]; // piece2

if($horas[1]>0){
	$sql1="select DATE_ADD('".$inFim."', INTERVAL ".$horas[1]." MINUTE) as datamin";
	//ECHO($sql1);
	$res1 = d::b()->query($sql1) or die("Falha ao somar minutos: " . mysqli_error(d::b()) . "<p>SQL: $sql1");
	$rm=mysqli_fetch_assoc($res1);
	$inFim=$rm['datamin'];
}
//echo("FIM=".$inFim);

if($horas[0]>0){
	$sql1="select DATE_ADD('".$inFim."', INTERVAL ".$horas[0]." HOUR) as datamin";
	$res1 = d::b()->query($sql1) or die("Falha ao somar minutos: " . mysqli_error(d::b()) . "<p>SQL: $sql1");
	$rm=mysqli_fetch_assoc($res1);
	$inFim=$rm['datamin'];
}
 * 
 * Exclusiva= Sozinha
 * Compartilhada= somente junto com simultanea
 * Simultanea=junto com compartilhada e simultanea
 */

if($trava=='E'){
    $intrava="";
}elseif($trava=='C'){
    $intrava=" and trava in ('Y','C') ";
}else{
    $intrava=" and trava = 'Y' ";
}
        
 /*       
IF($trava=='N'){
	$intrava=" and trava = 'Y' ";
}else{
	$intrava="";
}
*/
//echo("INICIO=".$inInicio);

$sql =  "	SELECT 
					true as travado
				FROM 
					tagreserva tr 
				WHERE
					 idtag=".$idsala."
					and objeto = 'loteativ'
					and idobjeto != ".$inidloteativ."
					".$intrava."
				and 
					((
						if (tr.inicio<='".$inInicio."','".$inInicio."',tr.inicio) = '".$inInicio."' and 
						if(tr.fim>='".$inFim."','".$inFim."',tr.fim )= '".$inFim."'
					) 
					or
					(
						(tr.inicio > '".$inInicio."' and tr.inicio < '".$inFim."') 
						or 
						(tr.fim > '".$inInicio."' and tr.fim < '".$inFim."')
					))
					";
					
		$res = d::b()->query($sql) or die("verificaTagReserva: Erro ao verificar tag reserva: " . mysql_error() . "\nSQL: $sql");
	
		if (mysqli_num_rows($res) > 0){
			echo('true');			
		}else{			
			echo('false');
		}
		