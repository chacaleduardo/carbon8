<?
require_once(__DIR__."/../../form/querys/_iquery.php");
require_once(__DIR__."/../../form/querys/immsgconfdest_query.php");
require_once(__DIR__."/../../form/querys/imgrupopessoa_query.php");
require_once(__DIR__ . "/../../form/querys/log_query.php");

$idimgrupopessoa=$_SESSION['arrpostbuffer']['x']['d']['imgrupopessoa']['idimgrupopessoa'];
$iu = $_SESSION['arrpostbuffer']['1']['u']['imgrupo']['idimgrupo'] ? 'u' : 'i';

if(!empty($idimgrupopessoa))
{
	$imGrupoPessoa = SQL::ini(ImGrupoPessoaQuery::buscarGrupoEPessoasPorIdImGrupoPessoa(), [
		'idimgrupopessoa' => $idimgrupopessoa
	])::exec();

	if($imGrupoPessoa->numRows() && $imGrupoPessoa->data[0]['idpessoaemail'])
	{
			$deletandoImGrupoPessoa = SQL::ini(ImGrupoPessoaQuery::deletarImGrupoPessoaPorIdImGrupoPessoa(), [
				'idimgrupopessoa' => $imGrupoPessoa->data[0]['idpessoaemail']
			])::exec();
	}
}


$idimgrupo=$_SESSION['arrpostbuffer']['x']['i']['imgrupopessoa']['idimgrupo'];

if(!empty($idimgrupo))
{
	//inserir pessoas no configuracao de alerta do sistema			
	$idpessoa=$_SESSION['arrpostbuffer']['x']['i']['imgrupopessoa']['idpessoa'];
	if(!empty($idpessoa))
	{
		$imMsgConfDest = SQL::ini(ImMsgConfDestQuery::buscarImMgsgConfDestQueryPorIdImGrupoEIdPessoa(), [
			'idimgrupo' => $idimgrupo,
			'idpessoa' => $idpessoa
		])::exec();

		$i=99;

		foreach($imMsgConfDest->data as $imMsgConf)
		{
			$i++;
			$_SESSION['arrpostbuffer'][$i]['i']['immsgconfdest']['idimmsgconf']=$imMsgConf['idimmsgconf'];
			$_SESSION['arrpostbuffer'][$i]['i']['immsgconfdest']['idobjeto']=$idpessoa;
			$_SESSION['arrpostbuffer'][$i]['i']['immsgconfdest']['objeto']='pessoa';
			$_SESSION['arrpostbuffer'][$i]['i']['immsgconfdest']['inseridomanualmente']='N';
			
		}
	}
}

if ($_SESSION['arrpostbuffer'][1][$iu]['imgrupo']['tipoobjetoext'] == 'manual')
{
	$_SESSION['arrpostbuffer'][1][$iu]['imgrupo']['idobjetoext'] = $_SESSION['arrpostbuffer'][1][$iu]['imgrupo']['idimgrupo'];
}
?>