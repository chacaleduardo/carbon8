<?
// QUERYS
require_once(__DIR__ . "/../../form/querys/_iquery.php");
require_once(__DIR__ . "/../../form/querys/pessoaobjeto_query.php");
require_once(__DIR__ . "/../../form/querys/objetovinculo_query.php");
require_once(__DIR__ . "/../../form/querys/sgarea_query.php");

// CONTROLLERS
require_once(__DIR__ . "/../../form/controllers/unidade_controller.php");

$iu = $_SESSION['arrpostbuffer']['1']['i']['sgconselho']['idsgconselho'] ? 'i' : 'u';
/*
*JLAL - 17-08-20 **Inserção na tb colaboradorhistorico toda vez que ocorrer alteração do tipo DELETE na aba de CONSELHO, AREA,DEPARTAMENTO,SETOR**
*/
if($_POST["_x_i_pessoaobjeto_idpessoa"])
{
    $idpessoa 	= $_POST["_x_i_pessoaobjeto_idpessoa"];
    $idobjeto 	= $_POST["_x_i_pessoaobjeto_idobjeto"];
    $tipoobjeto = $_POST["_x_i_pessoaobjeto_tipoobjeto"];

	
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['idpessoa'] 	= $idpessoa;
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['idempresa'] 	= $_SESSION["SESSAO"]["IDEMPRESA"];
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['aba'] 	= 'CONSAREADEPSETOR';
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['acao'] 	= 'i';
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['objeto'] 	= $idobjeto;
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['tipoobjeto'] 	= 'sgconselho';
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['criadopor'] 	= $_SESSION["SESSAO"]["USUARIO"];
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['criadoem'] 	= date("d/m/Y H:i:s");
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['alteradopor'] 	= $_SESSION["SESSAO"]["USUARIO"];
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['alteradoem'] 	= date("d/m/Y H:i:s");

	
}else if ($_POST["_x_d_pessoaobjeto_idpessoaobjeto"])
{
	
	$pessoaObjeto = SQL::ini(PessoaObjetoQuery::buscarPorChavePrimaria(), [
		'pkval' => $_POST["_x_d_pessoaobjeto_idpessoaobjeto"]
	])::exec();

	if($pessoaObjeto->numRows())
	{
		$idpessoa 	= $pessoaObjeto->data[0]['idpessoa'];
		$idempresa 	= $pessoaObjeto->data[0]['idempresa'];
		$idobjeto 	= $pessoaObjeto->data[0]['idobjeto'];	
	}
	
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['idpessoa'] 	= $idpessoa;
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['idempresa'] 	= $_SESSION["SESSAO"]["IDEMPRESA"];
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['aba'] 	= 'CONSAREADEPSETOR';
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['acao'] 	= 'd';
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['objeto'] 	= $idobjeto;
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['tipoobjeto'] 	= 'sgconselho';
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['criadopor'] 	= $_SESSION["SESSAO"]["USUARIO"];
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['criadoem'] 	= date("d/m/Y H:i:s");
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['alteradopor'] 	= $_SESSION["SESSAO"]["USUARIO"];
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['alteradoem'] 	= date("d/m/Y H:i:s");
		
}

// Remover vinculo com o area
if($_POST['_x_d_objetovinculo_idobjetovinculo'])
{
	$objetovinculo = SQL::ini(ObjetoVinculoQuery::buscarPorChavePrimaria(), [
		'pkval' => $_POST['_x_d_objetovinculo_idobjetovinculo']
	])::exec();
	
	$sgArea = SQL::ini(SgAreaQuery::buscarPorChavePrimaria(),[
		'pkval' => $objetovinculo->data[0]['idobjetovinc'],
		'status' => 'ATIVO'
	])::exec();

	if($sgArea->numRows())
	{
		$_SESSION['arrpostbuffer']['101']['u']['sgarea']['idsgarea'] 	 = $sgArea->data[0]['idsgarea'];
		$_SESSION['arrpostbuffer']['101']['u']['sgarea']['idsgconselho'] = null;
	}
}

if($_POST['_1_u_sgconselho_status'] == 'INATIVO')
{
	UnidadeController::deletarVinculoPorIdObjetoTipoObjeto($_POST['_1_u_sgconselho_idsgconselho'], 'sgconselho');
}

retarraytabdef('colaboradorhistorico');
montatabdef();
?>