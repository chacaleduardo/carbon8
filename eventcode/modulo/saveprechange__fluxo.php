<?
$_ajax_d_fluxostatus_idfluxostatus = $_SESSION['arrpostbuffer']['x']['d']['fluxostatus']['idfluxostatus'];
if (!empty($_ajax_d_fluxostatus_idfluxostatus)) {
	$sql = "SELECT 1 FROM fluxostatus WHERE fluxo IN ('$_ajax_d_fluxostatus_idfluxostatus')";
	$res = d::b()->query($sql) or die("Erro ao buscar informções da configuração do status: " . mysqli_error(d::b()));
	$qtd = mysqli_num_rows($res);
	if ($qtd > 0) {
		die('É necessário retirar o status do fluxo antes de excluir o mesmo.');
	}

	$sql = "SELECT 1 FROM fluxoobjeto WHERE inidstatus like '%$_ajax_d_fluxostatus_idfluxostatus%'";
	$res = d::b()->query($sql) or die("Erro ao buscar informções da configuração do status: " . mysqli_error(d::b()));
	$qtd = mysqli_num_rows($res);
	if ($qtd > 0) {
		die('É necessário retirar os participantes deste fluxo antes de excluir o mesmo.');
	}
}
?>