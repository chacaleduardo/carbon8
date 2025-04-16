<?

// QUERYS
require_once(__DIR__ . "/../../form/querys/_iquery.php");
require_once(__DIR__ . "/../../form/querys/pessoaobjeto_query.php");
require_once(__DIR__ . "/../../form/querys/objetovinculo_query.php");

// CONTROLLERS
require_once(__DIR__ . "/../../form/controllers/unidade_controller.php");

/*
*JLAL - 17-08-20 **Inserção na tb colaboradorhistorico toda vez que ocorrer alteração do tipo DELETE na aba de AREA,DEPARTAMENTO,SETOR**
*/

if($_POST["_x_i_pessoaobjeto_idpessoa"]){
	$idpessoa 	= $_POST["_x_i_pessoaobjeto_idpessoa"];
	$idobjeto 	= $_POST["_x_i_pessoaobjeto_idobjeto"];
	
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['idpessoa'] 	= $idpessoa;
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['idempresa'] 	= $_SESSION["SESSAO"]["IDEMPRESA"];
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['aba'] 	= 'AREADEPSETOR';
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['acao'] 	= 'i';
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['objeto'] 	= $idobjeto;
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['tipoobjeto'] 	= 'sgarea';
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['criadopor'] 	= $_SESSION["SESSAO"]["USUARIO"];
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['criadoem'] 	= date("d/m/Y H:i:s");
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['alteradopor'] 	= $_SESSION["SESSAO"]["USUARIO"];
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['alteradoem'] 	= date("d/m/Y H:i:s");

	
}else if ($_POST["_x_d_pessoaobjeto_idpessoaobjeto"]){
	
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
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['aba'] 	= 'AREADEPSETOR';
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['acao'] 	= 'd';
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['objeto'] 	= $idobjeto;
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['tipoobjeto'] 	= 'sgarea';
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['criadopor'] 	= $_SESSION["SESSAO"]["USUARIO"];
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['criadoem'] 	= date("d/m/Y H:i:s");
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['alteradopor'] 	= $_SESSION["SESSAO"]["USUARIO"];
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['alteradoem'] 	= date("d/m/Y H:i:s");
		
}

if(isset($_POST["_1_u_sgarea_idsgconselho"]) && !$_POST["_1_u_sgarea_idsgconselho"])
{
	$objetoVinculo = SQL::ini(ObjetoVinculoQuery::buscarPorTipoObjetOIdObjetoVincTipoObjetoVinc(), [
		'tipoobjeto' => 'sgconselho',
		'idobjetovinc' => $_POST["_1_u_sgarea_idsgarea"],
		'tipoobjetovinc'=> 'sgarea'
	])::exec();
	
	$_SESSION['arrpostbuffer']['101']['d']['objetovinculo']['idobjetovinculo'] = $objetoVinculo->data[0]['idobjetovinculo'];
}

if($_POST['_1_u_sgarea_status'] == 'INATIVO')
{
	UnidadeController::deletarVinculoPorIdObjetoTipoObjeto($_POST['_1_u_sgarea_idsgarea'], 'sgarea');
}

retarraytabdef('colaboradorhistorico'); 
?>
