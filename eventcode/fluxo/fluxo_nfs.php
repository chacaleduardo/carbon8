<?

if(!empty($_idobjeto))
{
    $problema = array();
  
    $statuspendente = 'N';
  
	//Proibir locar uma tag já locada
    $sqls = "select c.status,i.idremessa
            from contapagar c left join remessaitem i on (i.idcontapagar=c.idcontapagar)
            where c.idobjeto = ".$_idobjeto."
            and c.tipoobjeto = 'notafiscal'";   
    $ress = d::b()->query($sqls) or die("Erro ao buscar remessa da nota sql = ".$sqls);
    $qtd = mysqli_num_rows($ress);
    while($row=mysqli_fetch_assoc($ress) ){
        if($row['status']=='QUITADO' OR !empty($row['idremessa'])){
            $statuspendente = 'Y';
            $problema[1] = 'FATURAQUITADA';
       }
    }   
                             
}

$status['permissao']['modulo'] = 'nfs';
$status['permissao']['esconderbotao'] = $statuspendente;
$status['permissao']['status'] = 'CANCELADO';
$status['permissao']['problema'] = $problema;

?>
