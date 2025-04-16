<?
if(!empty($_idobjeto))
{
	//Proibir locar uma tag já locada
    $sqls = "SELECT 1
			   FROM solcom s JOIN solcomitem si ON si.idsolcom = s.idsolcom
			  WHERE si.idsolcom = $_idobjeto AND (si.idprodserv is null OR si.idprodserv = 0) AND si.status = 'PENDENTE';";   
    $ress = d::b()->query($sqls) or die("Erro ao buscar rateios da nota sql = ".$sqls);
    $qtd = mysqli_num_rows($ress);
    if($qtd > 0){
        $statuspendente = 'Y';
    }else{
        $statuspendente = 'N';
    } 
}

$status['permissao']['modulo'] = 'solcom';
$status['permissao']['esconderbotao'] = $statuspendente;
$status['permissao']['status'] = 'APROVADO';
?>