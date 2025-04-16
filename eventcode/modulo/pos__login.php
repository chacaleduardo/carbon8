<?

getClientesContato();

//MAF: Na tabela não existem registros de versao para outras empresas que nao seja o Inata. Portanto a versao deve refletir globalmente
$sv = "select versao,revisao 
		from controleversao 
		-- where idempresa=".$_SESSION["SESSAO"]["IDEMPRESA"]."
		order by concat(versao,revisao) desc
		limit 1";

$resv=d::b()->query($sv) or die("pos_login: Erro ao recuperar versÃ£o");

$rfv = mysqli_fetch_assoc($resv);

$_SESSION["SESSAO"]["VERSAOSISTEMA"]=$rfv["versao"].".".$rfv["revisao"];

require_once "../form/controllers/pessoa_controller.php";
if(PessoaController::verficarPlantelPessoa($_SESSION['SESSAO']['IDPESSOA'], 34)){
	$_SESSION['SESSAO']['PESSOAPLANTEL'] = 34;
}

?>