<?
// QUERYS
require_once(__DIR__."/../../form/querys/_iquery.php");
require_once(__DIR__."/../../form/querys/sgdepartamento_query.php");
require_once(__DIR__."/../../form/querys/pessoaobjeto_query.php");

// CONTROLLERS
require_once(__DIR__ . "/../../form/controllers/unidade_controller.php");

$iu = $_SESSION['arrpostbuffer']['1']['i']['sgdepartamento']['idsgdepartamento'] ? 'i' : 'u';
/*
*JLAL - 17-08-20 **Inserção na tb colaboradorhistorico toda vez que ocorrer alteração do tipo DELETE na aba de AREA,DEPARTAMENTO,SETOR**
*/
if($_POST["_x_i_pessoaobjeto_idpessoa"]){
	$idpessoa 	= $_POST["_x_i_pessoaobjeto_idpessoa"];
	$idobjeto 	= $_POST["_x_i_pessoaobjeto_idobjeto"];
	$tipoobjeto = $_POST["_x_i_pessoaobjeto_tipoobjeto"];


	if($tipoobjeto=="sgdepartamento"){

		$unidade = SQL::ini(SgDepartamentoQuery::buscarUnidadePorIdSgDepartamento(), [
			'idsgdepartamento' => $idobjeto
		])::exec()->data[0];
		
		$_SESSION['arrpostbuffer']['101']['u']['pessoa']['idpessoa'] 	= $idpessoa;
		$_SESSION['arrpostbuffer']['101']['u']['pessoa']['idunidade'] 	= $unidade['idunidade'];
	}

	
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['idpessoa'] 	= $idpessoa;
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['idempresa'] 	= $_SESSION["SESSAO"]["IDEMPRESA"];
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['aba'] 	= 'AREADEPSETOR';
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['acao'] 	= 'i';
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['objeto'] 	= $idobjeto;
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['tipoobjeto'] 	= 'sgdepartamento';
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['criadopor'] 	= $_SESSION["SESSAO"]["USUARIO"];
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['criadoem'] 	= date("d/m/Y H:i:s");
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['alteradopor'] 	= $_SESSION["SESSAO"]["USUARIO"];
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['alteradoem'] 	= date("d/m/Y H:i:s");

	
}else if ($_POST["_x_d_pessoaobjeto_idpessoaobjeto"]){
	
	$pessoaObjeto = SQL::ini(PessoaobjetoQuery::buscarPorChavePrimaria(), [
		'pkval' => $_POST["_x_d_pessoaobjeto_idpessoaobjeto"]
	])::exec()->data[0];

	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['idpessoa'] 	= $pessoaObjeto['idpessoa'];
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['idempresa'] 	= $_SESSION["SESSAO"]["IDEMPRESA"];
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['aba'] 	= 'AREADEPSETOR';
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['acao'] 	= 'd';
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['objeto'] 	= $pessoaObjeto['idobjeto'];
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['tipoobjeto'] 	= 'sgdepartamento';
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['criadopor'] 	= $_SESSION["SESSAO"]["USUARIO"];
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['criadoem'] 	= date("d/m/Y H:i:s");
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['alteradopor'] 	= $_SESSION["SESSAO"]["USUARIO"];
	$_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['alteradoem'] 	= date("d/m/Y H:i:s");
		
} 

if($_POST['_1_u_sgdepartamento_status'] == 'INATIVO')
{
	UnidadeController::deletarVinculoPorIdObjetoTipoObjeto($_POST['_1_u_sgdepartamento_idsgdepartamento'], 'sgdepartamento');
}

retarraytabdef('pessoa');
retarraytabdef('colaboradorhistorico');
?>