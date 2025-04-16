<?

if(!empty($_idobjeto))
{
	//Proibir locar uma tag já locada
    $sqls = "SELECT t.idtag, t.tag, t.descricao, e.sigla, t.status, tr.inicio, tr.fim
				   FROM tagreserva tr JOIN tag t ON t.idtag = tr.idtag JOIN empresa e ON e.idempresa = t.idempresa
				  WHERE idobjeto = '$_idobjeto' AND objeto = 'tag' AND tr.status = 'ATIVO';";   
    $ress = d::b()->query($sqls) or die("Erro ao buscar rateios da nota sql = ".$sqls);
    $qtd = mysqli_num_rows($ress);
    if($qtd > 0){
        $statuspendente = 'Y';
    }else{
        $statuspendente = 'N';
    } 
                             
}

$status['permissao']['modulo'] = 'tag';
$status['permissao']['esconderbotao'] = $statuspendente;
$status['permissao']['status'] = 'LOCADO';
?>
