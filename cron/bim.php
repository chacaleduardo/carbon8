<?
ini_set("display_errors","1");
error_reporting(E_ALL);

include_once(__DIR__."/../inc/php/functions.php");

// QUERYS
require_once(__DIR__."/../form/querys/_iquery.php");
require_once(__DIR__."/../form/querys/imgrupopessoa_query.php");
require_once(__DIR__."/../form/querys/impessoa_query.php");
require_once(__DIR__."/../form/querys/sgconselho_query.php");
require_once(__DIR__."/../form/querys/sgarea_query.php");
require_once(__DIR__."/../form/querys/sgdepartamento_query.php");
require_once(__DIR__."/../form/querys/sgsetor_query.php");
require_once(__DIR__."/../form/querys/_lp_query.php");
require_once(__DIR__."/../form/querys/imgrupo_query.php");
require_once(__DIR__."/../form/querys/pessoa_query.php");
require_once(__DIR__."/../form/querys/imcontato_query.php");
require_once(__DIR__."/../form/querys/fluxostatuspessoa_query.php");
require_once(__DIR__."/../form/querys/imregra_query.php");
require_once(__DIR__."/../form/querys/immsgconfdest_query.php");
require_once(__DIR__."/../form/querys/carimbo_query.php");
require_once(__DIR__."/../form/querys/pessoaobjeto_query.php");

// CONTROLLERS
require_once(__DIR__."/../form/controllers/log_controller.php");
require_once(__DIR__."/../form/controllers/emailvirtualconf_controller.php");
require_once(__DIR__."/../form/controllers/imgrupo_controller.php");
require_once(__DIR__."/../form/controllers/evento_controller.php");

$_inspecionar_sql = ($_GET["_inspecionar_sql"] == "Y") ? true : false;
$chaveCron='cron:_lock:'.$_SERVER["SCRIPT_FILENAME"];
$cronLock=re::dis()->get($chaveCron);
$grupo = rstr(8);
$estiloFonteErro = 'color: #e11414;';

if($cronLock === false)
{
	re::dis()->set($chaveCron,date('d/m/y h:i'),3600);
} else
{
	echo 'Adiado. Rodando desde '.$cronLock."</br>";
	$dadosLog = [
		'idempresa' => '1',
		'sessao' => $grupo,
		'tipoobjeto' => 'cron',
		'idobjeto' => 'bim',
		'tipolog' => 'status',
		'log' => "LOCKED. Rodando desde {$cronLock}",
		'status' => 'ADIADO',
		'info' => '',
		'criadoem' => "NOW()",
		'data' => "NOW()"
	];
	
	$inserirLog = LogController::inserir($dadosLog);

    if($_inspecionar_sql)
	{
		echo $inserirLog->sql()."</br>";

		if($inserirLog->error())
		{
			echo "<p style='$estiloFonteErro'>";
			echo $inserirLog->errorMessage();
			echo "</p>";
		}
	}
	die;
}

echo "Início: ".date("d/m/Y H:i:s", time()).'<br>'; 

re::dis()->hMSet('bim',['inicio' => Date('d/m/Y H:i:s')]);

$dadosLog = [
	'idempresa' => $_SESSION["SESSAO"]["IDEMPRESA"],
	'sessao' => $grupo,
	'tipoobjeto' => 'cron',
	'idobjeto' => 'bim',
	'tipolog' => 'status',
	'log' => 'INICIO',
	'status' => 'SUCESSO',
	'info' => '',
	'criadoem' => "NOW()",
	'data' => "NOW()"
];

$inserirLog = LogController::inserir($dadosLog);

if($_inspecionar_sql)
{
	echo $inserirLog->sql()."</br>";
	if($inserirLog->error())
	{
		echo "<p style='$estiloFonteErro'>";
		echo $inserirLog->errorMessage();
		echo "</p>";
	}
}

pessoaLog('ATBIM');
//Inativa os usuários temporariamente. Os idpessoa que não forem atualizados serão excluídos no final

// ================= REMOVER SE NAO FOR NECESSARIO ====================
$inativandoPessoas = SQL::ini(ImPessoaQuery::inativarPessoasQueNaoForamInseridasManualmente())::exec();
if($_inspecionar_sql)
{
	echo $inativandoPessoas->sql()."</br>";
	if($inativandoPessoas->error())
	{
		echo "<p style='$estiloFonteErro'>";
		echo $inativandoPessoas->errorMessage();
		echo "</p>";
	}
}
// ================= FIM REMOVER SE NAO FOR NECESSARIO ====================

// Atualizando nome do grupo
$dadosGrupo = [
	[
		'tabela' => 'sgarea',
		'chaveprimaria' => 'idsgarea',
		'banco' => 'laudo',
		'colunadescricao' => 'area'
	],
	[
		'tabela' => 'sgdepartamento',
		'chaveprimaria' => 'idsgdepartamento',
		'banco' => 'laudo',
		'colunadescricao' => 'departamento'
	],
	[
		'tabela' => 'sgsetor',
		'chaveprimaria' => 'idsgsetor',
		'banco' => 'laudo',
		'colunadescricao' => 'setor'
	],
	[
		'tabela' => '_lp',
		'chaveprimaria' => 'idlp',
		'banco' => 'carbonnovo',
		'colunadescricao' => 'descricao'
	]
];

foreach($dadosGrupo as $grupo)
{
	$atualizandoNomeDosGrupos = SQL::ini(ImGrupoQuery::atualizarNomeDosGruposDeAcordoComOVinculo(), [
		'tabela' => $grupo['tabela'],
		'chaveprimaria' => $grupo['chaveprimaria'],
		'banco' => $grupo['banco'],
		'colunadescricao' => $grupo['colunadescricao']
	])::exec();
	if($_inspecionar_sql)
	{
		echo $atualizandoNomeDosGrupos->sql()."</br>";
		if($atualizandoNomeDosGrupos->error())
		{
			echo "<p style='$estiloFonteErro'>";
			echo $atualizandoNomeDosGrupos->errorMessage();
			echo "</p>";
		}
	}

	$ativandoGrupos = SQL::ini(ImGrupoQuery::ativarGruposCujoVinculoEstejaAtivoEDefinidoParaGerarGrupo(), [
		'tabela' => $grupo['tabela'],
		'chaveprimaria' => $grupo['chaveprimaria'],
		'banco' => $grupo['banco']
	])::exec();
	if($_inspecionar_sql)
	{
		echo $ativandoGrupos->sql()."</br>";
		if($ativandoGrupos->error())
		{
			echo "<p style='$estiloFonteErro'>";
			echo $ativandoGrupos->errorMessage();
			echo "</p>";
		}
	}

	$inativandoGrupos = SQL::ini(ImGrupoQuery::inativarGruposCujoVinculoEstejaInativoEDefinidoParaNaoGerarGrupo(), [
		'tabela' => $grupo['tabela'],
		'chaveprimaria' => $grupo['chaveprimaria'],
		'banco' => $grupo['banco']
	])::exec();
	if($_inspecionar_sql)
	{
		echo $inativandoGrupos->sql()."</br>";
		if($inativandoGrupos->error())
		{
			echo "<p style='$estiloFonteErro'>";
			echo $inativandoGrupos->errorMessage();
			echo "</p>";
		}
	}
}
// FIM Atualizando nome do grupo

$organograma = [
	'sgconselho' => SQL::ini(SgConselhoQuery::buscarPessoasVinculadasEPessoasDoGrupoVinculado())::exec(),
	'sgarea' => SQL::ini(SgAreaQuery::buscarPessoasVinculadasEPessoasDoGrupoVinculado())::exec(),
	'sgdepartamento' => SQL::ini(SgDepartamentoQuery::buscarPessoasVinculadasEPessoasDoGrupoVinculado())::exec(),
	'sgsetor' => SQL::ini(SgSetorQuery::buscarPessoasVinculadasEPessoasDoGrupoVinculado())::exec(),
	'_lp' => SQL::ini(_LpQuery::buscarPessoasVinculadasEPessoasDoGrupoVinculado())::exec(),
	'manual' => SQL::ini(ImGrupoQuery::buscarPessoasVinculadasManualmente())::exec(),
	'tipopessoa' => SQL::ini(PessoaQuery::buscarPessoasPorGrupoTipoPessoa())::exec(),
];

$aGrupos = array();
$grupoDeUsuarios = array();

echo '<pre>';

//A consulta de cada grupo externo deve trazer junto o usuário e os grupos ao qual ele pertence, e serão separados aqui em arrays de grupos e usuarios
foreach ($organograma as $tipoobjeto => $item)
{
	if($_inspecionar_sql)
	{
		echo $item->sql()."</br>";
		if($item->error())
		{
			echo "<p style='$estiloFonteErro'>";
			echo $item->errorMessage();
			echo "</p>";
		}
	}

	echo '<br/>';
	echo '<br/>';
	echo '<br/>';

	foreach($item->data as $objeto)
	{
		//Monta os grupos
		//Concatenado o ID empresa para separar os gupos por empresa. (Lidiane -26-03-2020)
		$grupos[$tipoobjeto][$objeto["idempresa"]][$objeto["idobjetoext"]]["tipoobjetoext"]=$objeto["tipoobjetoext"];
		$grupos[$tipoobjeto][$objeto["idempresa"]][$objeto["idobjetoext"]]["descr"]=mysqli_escape_string(d::b(),$objeto["descr"]);
		$grupos[$tipoobjeto][$objeto["idempresa"]][$objeto["idobjetoext"]]["grupo"]=mysqli_escape_string(d::b(),$objeto["grupo"]);
		$grupos[$tipoobjeto][$objeto["idempresa"]][$objeto["idobjetoext"]]["idempresa"]=$objeto["idempresa"];
		
		//Separa os usuários dentro dos grupos
		if($objeto["idpessoa"])
		{
			if($tipoobjeto == 'manual')
			{
				if($objeto['grupolideranca'] == 'Y')
				{
					if($objeto['responsavel'] == 'Y')
						$grupoDeUsuarios[$tipoobjeto][$objeto["idempresa"]][$objeto["idobjetoext"]][$objeto["idpessoa"]]["idempresa"]=$objeto["idempresa"];
				} else 
					$grupoDeUsuarios[$tipoobjeto][$objeto["idempresa"]][$objeto["idobjetoext"]][$objeto["idpessoa"]]["idempresa"]=$objeto["idempresa"];
			}
			else 
				$grupoDeUsuarios[$tipoobjeto][$objeto["idempresa"]][$objeto["idobjetoext"]][$objeto["idpessoa"]]["idempresa"]=$objeto["idempresa"];
		}
	}
}

if($_inspecionar_sql) 
{
	var_dump($grupos);
	var_dump($grupoDeUsuarios);
}

foreach ($grupos as $objetoDeOrigem => $grupo)
{
	//Inativa os grupos do Objeto Externo temporariamente. Os que não forem atualizados serão excluídos no final
	$inativandoGruposDoOrganograma = SQL::ini(ImGrupoQuery::inativarGruposDoOrganogramaPorTipoObjeto(), [
		'tipoobjeto' => $objetoDeOrigem
	])::exec();
	if($_inspecionar_sql)
	{
		echo $inativandoGruposDoOrganograma->sql()."</br>";
		if($inativandoGruposDoOrganograma->error())
		{
			echo "<p style='$estiloFonteErro'>";
			echo $inativandoGruposDoOrganograma->errorMessage();
			echo "</p>";
		}
	}

	echo '<br/>';	
	echo '<br/>';
	echo '<br/>';
	//Para cada "grupo" do obj externo, verifica se já existe. Caso negativo: insere. Update para ATIVO caso exista. Caso não exista: permanecerá com status INATIVAR.
	foreach ($grupo as $empresas)
	{
		foreach ($empresas as $idObjetoOrigem => $grupoEmpresa)
		{
			$inserindoGrupoCasoNaoExista = SQL::ini(ImGrupoQuery::inserirGrupoCasoNaoExista(), [
				'idempresa' => $grupoEmpresa["idempresa"],
				'idobjeto' => $idObjetoOrigem,
				'tipoobjeto' => $grupoEmpresa["tipoobjetoext"],
				'grupo' => $grupoEmpresa["grupo"],
				'descricao' => $grupoEmpresa["descr"]
			])::exec();
			if($_inspecionar_sql)
			{
				echo $inserindoGrupoCasoNaoExista->sql()."</br>";
				if($inserindoGrupoCasoNaoExista->error())
				{
					echo "<p style='$estiloFonteErro'>";
					echo $inserindoGrupoCasoNaoExista->errorMessage();
					echo "</p>";
				}
			}

			echo '<br/>';
			echo '<br/>';
			echo '<br/>';
			echo $objetoDeOrigem ." - ". $grupoEmpresa['tipoobjetoext']." - ". $grupoEmpresa["grupo"];
			echo '<br/>';
			echo '<br/>';
			echo '<br/>';

			if($grupoEmpresa['tipoobjetoext'] == 'manual')
			{
				$ativandoGruposManuais = SQL::ini(ImGrupoQuery::ativarGruposManuais(), [
					'idempresa' => $grupoEmpresa["idempresa"],
					'idimgrupo' => $idObjetoOrigem
				])::exec();
				if($_inspecionar_sql)
				{
					echo $ativandoGruposOrganograma->sql()."</br>";
					if($ativandoGruposOrganograma->error())
					{
						echo "<p style='$estiloFonteErro'>";
						echo $ativandoGruposOrganograma->errorMessage();
						echo "</p>";
					}
				}
			} else {
				$ativandoGruposOrganograma = SQL::ini(ImGrupoQuery::ativarGruposDoOrganogramaPorIdObjetoETipoObjeto(), [
					'idempresa' => $grupoEmpresa["idempresa"],
					'idobjeto' => $idObjetoOrigem,
					'tipoobjeto' => $grupoEmpresa["tipoobjetoext"]
				])::exec();
				if($_inspecionar_sql)
				{
					echo $ativandoGruposOrganograma->sql()."</br>";
					if($ativandoGruposOrganograma->error())
					{
						echo "<p style='$estiloFonteErro'>";
						echo $ativandoGruposOrganograma->errorMessage();
						echo "</p>";
					}
				}
			}

			echo '<br/>';
			echo '<br/>';
			echo '<br/>';
		}
	}
}
			
//Exclui os usuarios dos grupos com status INATIVAR e ATIVAR
$inativandoPessoasDosGrupos = SQL::ini(ImGrupoPessoaQuery::excluirPessoasComStatusAtivarOuInativarDosGrupos())::exec();
if($_inspecionar_sql)
{
	echo $inativandoPessoasDosGrupos->sql()."</br>";
	if($inativandoPessoasDosGrupos->error())
	{
		echo "<p style='$estiloFonteErro'>";
		echo $inativandoPessoasDosGrupos->errorMessage();
		echo "</p>";
	}
}

//Inclui os usuário snos respectivos grupos
foreach ($grupoDeUsuarios as $objetoDeOrigem => $grupo)
{//objetoexterno
	foreach ($grupo as $empresa)
	{//idpbjetoexterno
		foreach ($empresa as $idObjetoOrigem => $pessoas)
		{ 
			foreach ($pessoas as $idpessoa => $pessoa)
			{
				if($objetoDeOrigem == 'manual')
				{
					$inserindoOuAtualizandoPessoasNoGrupoManual = SQL::ini(ImGrupoPessoaQuery::inserirOuAtualizarPessoasNoGrupoManual(), [
						'idempresa' => $pessoa["idempresa"],
						'idimgrupo' => $idObjetoOrigem,
						'idpessoa' => $idpessoa
					])::exec();
					if($_inspecionar_sql)
					{
						echo $inserindoOuAtualizandoPessoasNoGrupoManual->sql()."</br>";
						if($inserindoOuAtualizandoPessoasNoGrupoManual->error())
						{
							echo "<p style='$estiloFonteErro'>";
							echo $inserindoOuAtualizandoPessoasNoGrupoManual->errorMessage();
							echo "</p>";
						}
					}
				} else {
					$inserindoOuAtualizandoPessoasNoGrupo = SQL::ini(ImGrupoPessoaQuery::inserirOuAtualizarPessoasNoGrupo(), [
						'idempresa' => $pessoa["idempresa"],
						'idobjetoext' => $idObjetoOrigem,
						'tipoobjetoext' => $objetoDeOrigem,
						'idpessoa' => $idpessoa
					])::exec();
					if($_inspecionar_sql)
					{
						echo $inserindoOuAtualizandoPessoasNoGrupo->sql()."</br>";
						if($inserindoOuAtualizandoPessoasNoGrupo->error())
						{
							echo "<p style='$estiloFonteErro'>";
							echo $inserindoOuAtualizandoPessoasNoGrupo->errorMessage();
							echo "</p>";
						}
					}
				}
				
				echo '<br/>';
				echo '<br/>';
				echo '<br/>';
			}
		}
	}
}

// ==========  REMOVER SE NAO FOR NECESSARIO  ================
//Reativa os usuários que vieram nas consultas
foreach ($grupoDeUsuarios as $objetoDeOrigem => $grupo)
{//objetoexterno
	foreach ($grupo as $empresa)
	{//idpbjetoexterno
		foreach ($empresa as $idObjetoOrigem => $pessoas)
		{
			foreach ($pessoas as $idpessoa => $pessoa)
			{
				$ativandoPessoasDoGrupo = SQL::ini(ImPessoaQuery::ativarPessoasQueNaoForamInseridasManualmente(), [
					'idpessoa' => $idpessoa
				])::exec();
				if($_inspecionar_sql)
				{
					echo $ativandoPessoasDoGrupo->sql()."</br>";
					if($ativandoPessoasDoGrupo->error())
					{
						echo "<p style='$estiloFonteErro'>";
						echo $ativandoPessoasDoGrupo->errorMessage();
						echo "</p>";
					}
				}
				
				echo '<br/>';
				echo '<br/>';
				echo '<br/>';
			}
		}
	}
}
// ==========  FIM REMOVER SE NAO FOR NECESSARIO  ================
	
//Exclui grupos e pessoas que não vieram nas consultas
// ==========  REMOVER SE NAO FOR NECESSARIO  ================
$deletandoPessoasComStatusInativar = SQL::ini(ImPessoaQuery::deletarPessoasComStatusInativar())::exec();
if($_inspecionar_sql)
{
	echo $deletandoPessoasComStatusInativar->sql()."</br>";
	if($deletandoPessoasComStatusInativar->error())
	{
		echo "<p style='$estiloFonteErro'>";
		echo $deletandoPessoasComStatusInativar->errorMessage();
		echo "</p>";
	}
}
// ==========  FIM REMOVER SE NAO FOR NECESSARIO  ================

$deletandoGruposComStatusInativar = SQL::ini(ImGrupoQuery::deletarGruposComStatusInativar())::exec();
if($_inspecionar_sql)
{
	echo $deletandoGruposComStatusInativar->sql()."</br>";
	if($deletandoGruposComStatusInativar->error())
	{
		echo "<p style='$estiloFonteErro'>";
		echo $deletandoGruposComStatusInativar->errorMessage();
		echo "</p>";
	}
}

//Ativa grupos e pessoas novas/alteradas
// ==========  REMOVER SE NAO FOR NECESSARIO  ================
$ativandoPessoasComStatusAtivar = SQL::ini(ImPessoaQuery::ativarPessoasComStatusAtivar())::exec();
if($_inspecionar_sql)
{
	echo $ativandoPessoasComStatusAtivar->sql()."</br>";
	if($ativandoPessoasComStatusAtivar->error())
	{
		echo "<p style='$estiloFonteErro'>";
		echo $ativandoPessoasComStatusAtivar->errorMessage();
		echo "</p>";
	}
}
// ==========  FIM REMOVER SE NAO FOR NECESSARIO  ================

$ativandoPessoasComStatusAtivar = SQL::ini(ImGrupoQuery::ativarPessoasComStatusAtivar())::exec();
if($_inspecionar_sql)
{
	echo $ativandoPessoasComStatusAtivar->sql()."</br>";
	if($ativandoPessoasComStatusAtivar->error())
	{
		echo "<p style='$estiloFonteErro'>";
		echo $ativandoPessoasComStatusAtivar->errorMessage();
		echo "</p>";
	}
}

//Final das contas
// ==========  REMOVER SE NAO FOR NECESSARIO  ================
// ADICIONA OS NOVOS GRUPOS (CADASTRADOS ACIMA ATRAVÉS DA SGSETOR) NA TABELA DE REGRAS DO CHAT. 39 É O GRUPO DE FUNCIONÁRIOS
// Alterado para aparecer as pessoas do grupo de cada empresa (Lidiane - 12-05-2020 - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=315016)
// ========== FIM REMOVER SE NAO FOR NECESSARIO  ================

$adicionandoNovosGruposDoSetor = SQL::ini(ImRegraQuery::adicionarNovosGruposPorTipoObjeto(), [
	'tipoobjeto' => 'sgsetor',
	'isnull' => "AND ISNULL(NULLIF(idtipopessoa, ''))"
])::exec();
if($_inspecionar_sql)
{
	echo $adicionandoNovosGruposDoSetor->sql()."</br>";
	if($adicionandoNovosGruposDoSetor->error())
	{
		echo "<p style='$estiloFonteErro'>";
		echo $adicionandoNovosGruposDoSetor->errorMessage();
		echo "</p>";
	}
}
echo '<br/>';
echo '<br/>';
echo '<br/>';
// ==========  REMOVER SE NAO FOR NECESSARIO  ================
// ADICIONA OS NOVOS GRUPOS (CADASTRADOS ACIMA ATRAVÉS DA SGAREA) NA TABELA DE REGRAS DO CHAT. 39 É O GRUPO DE FUNCIONÁRIOS
//Alterado para aparecer as pessoas do grupo de cada empresa (Lidiane - 12-05-2020 - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=315016)
$adicionandoNovosGruposDaArea = SQL::ini(ImRegraQuery::adicionarNovosGruposPorTipoObjeto(), [
	'tipoobjeto' => 'sgarea',
	'isnull' => ""
])::exec();
if($_inspecionar_sql)
{
	echo $adicionandoNovosGruposDaArea->sql()."</br>";
	if($adicionandoNovosGruposDaArea->error())
	{
		echo "<p style='$estiloFonteErro'>";
		echo $adicionandoNovosGruposDaArea->errorMessage();
		echo "</p>";
	}
}

echo '<br/>';
echo '<br/>';
echo '<br/>';
// ADICIONA OS NOVOS GRUPOS (CADASTRADOS ACIMA ATRAVÉS DA SGDEPARTAMENTO) NA TABELA DE REGRAS DO CHAT. 39 É O GRUPO DE FUNCIONÁRIOS
//Alterado para aparecer as pessoas do grupo de cada empresa (Lidiane - 12-05-2020 - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=315016)
$adicionandoNovosGruposDoDepartamento = SQL::ini(ImRegraQuery::adicionarNovosGruposPorTipoObjeto(), [
	'tipoobjeto' => 'sgdepartamento',
	'isnull' => ""
])::exec();

if($_inspecionar_sql)
{
	echo $adicionandoNovosGruposDoDepartamento->sql()."</br>";
	if($adicionandoNovosGruposDoDepartamento->error())
	{
		echo "<p style='$estiloFonteErro'>";
		echo $adicionandoNovosGruposDoDepartamento->errorMessage();
		echo "</p>";
	}
}

$adicionandoNovosGruposDaLp = SQL::ini(ImRegraQuery::adicionarNovosGruposDaLp())::exec();
if($_inspecionar_sql)
{
	echo $adicionandoNovosGruposDaLp->sql()."</br>";
	if($adicionandoNovosGruposDaLp->error())
	{
		echo "<p style='$estiloFonteErro'>";
		echo $adicionandoNovosGruposDaLp->errorMessage();
		echo "</p>";
	}
}
$adicionandoNovosGruposManuais = SQL::ini(ImRegraQuery::adicionarNovosGruposManuais())::exec();
if($_inspecionar_sql)
{
	echo $adicionandoNovosGruposManuais->sql()."</br>";
	if($adicionandoNovosGruposManuais->error())
	{
		echo "<p style='$estiloFonteErro'>";
		echo $adicionandoNovosGruposManuais->errorMessage();
		echo "</p>";
	}
}

// ==========  FIM REMOVER SE NAO FOR NECESSARIO  ================
$deletandoPessoasInativasDeGrupos = SQL::ini(ImGrupoPessoaQuery::deletarPessoasInativasDeGrupos())::exec();
if($_inspecionar_sql)
{
	echo $deletandoPessoasInativasDeGrupos->sql()."</br>";
	if($deletandoPessoasInativasDeGrupos->error())
	{
		echo "<p style='$estiloFonteErro'>";
		echo $deletandoPessoasInativasDeGrupos->errorMessage();
		echo "</p>";
	}
}
	
//DELETA TODAS AS PESSOAS DO "EVENTO" QUE ESTÃO INATIVAS
$deletandoPessoasInativasDeEventos = SQL::ini(FluxostatuspessoaQuery::deletarPessoasInativasDeEventos())::exec();
if($_inspecionar_sql)
{
	echo $deletandoPessoasInativasDeEventos->sql()."</br>";
	if($deletandoPessoasInativasDeEventos->error())
	{
		echo "<p style='$estiloFonteErro'>";
		echo $deletandoPessoasInativasDeEventos->errorMessage();
		echo "</p>";
	}
}
	
//DELETA TODAS AS PESSOAS DO "ALERTA" QUE ESTÃO INATIVAS
$deletandoPessoasInativasDoAlerta = SQL::ini(ImMsgConfDestQuery::deletarPessoasInativasDoAlerta())::exec();
if($_inspecionar_sql)
{
	echo $deletandoPessoasInativasDoAlerta->sql()."</br>";
	if($deletandoPessoasInativasDoAlerta->error())
	{
		echo "<p style='$estiloFonteErro'>";
		echo $deletandoPessoasInativasDoAlerta->errorMessage();
		echo "</p>";
	}
}
	
//DELETA TODAS AS PESSOAS DO "GRUPO" QUE NÃO FAZEM MAIS PARTE DO SETOR E DO SETOR VINCULADO.
$deletandoPessoasDeGruposQueNaoFacamMaisParteDoSetorVinculado = SQL::ini(ImGrupoPessoaQuery::deletarPessoasDoGrupoQueNaoFacamParteDoSetorVinculados())::exec();
if($_inspecionar_sql)
{
	echo $deletandoPessoasDeGruposQueNaoFacamMaisParteDoSetorVinculado->sql()."</br>";
	if($deletandoPessoasDeGruposQueNaoFacamMaisParteDoSetorVinculado->error())
	{
		echo "<p style='$estiloFonteErro'>";
		echo $deletandoPessoasDeGruposQueNaoFacamMaisParteDoSetorVinculado->errorMessage();
		echo "</p>";
	}
}

echo '<br/>';
echo '<br/>';
echo '<br/>';
//DELETA TODAS AS PESSOAS DO "GRUPO" QUE NÃO FAZEM MAIS PARTE DA AREA E DA AREA VINCULADA. (Lidiane - 17-03-2020)
$deletandoPessoasDoGrupoQueNaoFacamMaisParteDaAreaVinculada = SQL::ini(ImGrupoPessoaQuery::deletarPessoasDoGrupoQueNaoFacamParteDaAreaVinculada())::exec();
if($_inspecionar_sql)
{
	echo $deletandoPessoasDoGrupoQueNaoFacamMaisParteDaAreaVinculada->sql()."</br>";
	if($deletandoPessoasDoGrupoQueNaoFacamMaisParteDaAreaVinculada->error())
	{
		echo "<p style='$estiloFonteErro'>";
		echo $deletandoPessoasDoGrupoQueNaoFacamMaisParteDaAreaVinculada->errorMessage();
		echo "</p>";
	}
}

echo '<br/>';
echo '<br/>';
echo '<br/>';
//DELETA TODAS AS PESSOAS DO "GRUPO" QUE NÃO FAZEM MAIS PARTE DEPARTAMENTO E DEPARTAMENTO VINCULADO. (Lidiane - 17-03-2020)
$deletandoPessoasDoGrupoQueNaoFacamMaisParteDoDepartamentoVinculado = SQL::ini(ImGrupoPessoaQuery::deletarPessoasDoGrupoQueNaoFacamMaisParteDoDepartamentoVinculado())::exec();
if($_inspecionar_sql)
{
	echo $deletandoPessoasDoGrupoQueNaoFacamMaisParteDoDepartamentoVinculado->sql()."</br>";
	if($deletandoPessoasDoGrupoQueNaoFacamMaisParteDoDepartamentoVinculado->error())
	{
		echo "<p style='$estiloFonteErro'>";
		echo $deletandoPessoasDoGrupoQueNaoFacamMaisParteDoDepartamentoVinculado->errorMessage();
		echo "</p>";
	}
}

echo '<br/>';
echo '<br/>';
echo '<br/>';
// DELETA TODAS AS PESSOAS DO GRUPO QUE NAO FAZEM MAIS PARTE DO CONSELHO
$deletandoPessoasQueNaoFacamParteDoConselhoVinculado = SQL::ini(ImGrupoPessoaQuery::deletarPessoasDoGrupoQueNaoFacamParteDoConselhoVinculado())::exec();
if($_inspecionar_sql)
{
	echo $deletandoPessoasQueNaoFacamParteDoConselhoVinculado->sql()."</br>";
	if($deletandoPessoasQueNaoFacamParteDoConselhoVinculado->error())
	{
		echo "<p style='$estiloFonteErro'>";
		echo $deletandoPessoasQueNaoFacamParteDoConselhoVinculado->errorMessage();
		echo "</p>";
	}
}

echo '<br/>';
echo '<br/>';
echo '<br/>';


//DELETA TODAS AS PESSOAS DO "GRUPO" QUE NÃO FAZEM MAIS PARTE DA LP
$deletandoPessoasDoGrupoQueNaoFacamParteDaLpVinculada = SQL::ini(ImGrupoPessoaQuery::deletarPessoasDoGrupoQueNaoFacamParteDaLpVinculada())::exec();
if($_inspecionar_sql)
{
	echo $deletandoPessoasDoGrupoQueNaoFacamParteDaLpVinculada->sql()."</br>";
	if($deletandoPessoasDoGrupoQueNaoFacamParteDaLpVinculada->error())
	{
		echo "<p style='$estiloFonteErro'>";
		echo $deletandoPessoasDoGrupoQueNaoFacamParteDaLpVinculada->errorMessage();
		echo "</p>";
	}
}
echo '<br/>';
echo '<br/>';
echo '<br/>';
//DELETA TODAS AS PESSOAS DO "GRUPO" CUJO SETORES NÃO FAZEM MAIS PARTE DA LP
//Alterado para não apagar as pessoas que podem estar no area ou departamento - Lidiane (12/06/2020). 
//Por exemplo, o Paulo estava na area Comercial Bovino, o mesmo inserido no grupo, apagava ele nesta parte e seu nome ficava fora e aceitava somente sgsetor.
$deletandoPessoasDoGrupoCujoSetorNaoFacaMaisParteDaLp = SQL::ini(ImGrupoPessoaQuery::deletarPessoasDoGrupoCujoSetorNaoFacaMaisParteDaLp())::exec();
if($_inspecionar_sql)
{
	echo $deletandoPessoasDoGrupoCujoSetorNaoFacaMaisParteDaLp->sql()."</br>";
	if($deletandoPessoasDoGrupoCujoSetorNaoFacaMaisParteDaLp->error())
	{
		echo "<p style='$estiloFonteErro'>";
		echo $deletandoPessoasDoGrupoCujoSetorNaoFacaMaisParteDaLp->errorMessage();
		echo "</p>";
	}
}

echo '<br/>';
echo '<br/>';
echo '<br/>';
//DELETA TODAS AS PESSOAS DO "GRUPO" MANUAL QUE NÃO FAZEM MAIS PARTE DO SETOR E DO SETOR VINCULADO. 			
$deletandoPessoasDoGrupoManualQueNaoFacamParteDoSetorVinculado = SQL::ini(ImGrupoPessoaQuery::deletarPessoasDoGrupoManualQueNaoFacamParteDoSetorVinculado())::exec();
if($_inspecionar_sql)
{
	echo $deletandoPessoasDoGrupoManualQueNaoFacamParteDoSetorVinculado->sql()."</br>";
	if($deletandoPessoasDoGrupoManualQueNaoFacamParteDoSetorVinculado->error())
	{
		echo "<p style='$estiloFonteErro'>";
		echo $deletandoPessoasDoGrupoManualQueNaoFacamParteDoSetorVinculado->errorMessage();
		echo "</p>";
	}
}

echo '<br/>';
echo '<br/>';
echo '<br/>';
		
//Acrescetado no union os dois ultimos para buscar as pessoas que podem acessar a outra empresa. (LTM - 03-08-2020 - 363640)
$atualizandoStatusDeRegistrosQueNaoForamInseridosManualmente = SQL::ini(ImContatoQuery::atualizarStatusDeRegistrosQueNaoForamInseridosManualmente(), [
	'status' => 'I'
])::exec();
if($_inspecionar_sql)
{
	echo $atualizandoStatusDeRegistrosQueNaoForamInseridosManualmente->sql()."</br>";
	if($atualizandoStatusDeRegistrosQueNaoForamInseridosManualmente->error())
	{
		echo "<p style='$estiloFonteErro'>";
		echo $atualizandoStatusDeRegistrosQueNaoForamInseridosManualmente->errorMessage();
		echo "</p>";
	}
}

$atualizandoContatoDePessoasQuePossamEstarEmOutraEmpresa = SQL::ini(ImContatoQuery::atualizarContatoDePessoasQuePossamEstarEmOutraEmpresa())::exec();
if($_inspecionar_sql)
{
	echo $atualizandoContatoDePessoasQuePossamEstarEmOutraEmpresa->sql()."</br>";
	if($atualizandoContatoDePessoasQuePossamEstarEmOutraEmpresa->error())
	{
		echo "<p style='$estiloFonteErro'>";
		echo $atualizandoContatoDePessoasQuePossamEstarEmOutraEmpresa->errorMessage();
		echo "</p>";
	}
}

echo '<br/>';
echo '<br/>';
echo '<br/>';
//GRUPO: Todas as pessoas do Grupo de origem podem ter como contato destino Todos os grupos de destino
$inserindoOuAtualizandoPessoasDoGrupoVinculado = SQL::ini(ImContatoQuery::inserirOuAtualizarPessoasDoGrupoVinculado())::exec();
if($_inspecionar_sql)
{
	echo $inserindoOuAtualizandoPessoasDoGrupoVinculado->sql()."</br>";
	if($inserindoOuAtualizandoPessoasDoGrupoVinculado->error())
	{
		echo "<p style='$estiloFonteErro'>";
		echo $inserindoOuAtualizandoPessoasDoGrupoVinculado->errorMessage();
		echo "</p>";
	}
}

echo '<br/>';
echo '<br/>';
echo '<br/>';
//DELETA TODAS AS PESSOAS QUE NÃO PERTENCEM MAIS AOS GRUPOS DENTRO DOS EVENTOS
//LTM - 09-10-2020: Alterado para deletar apenas quando estiver no status FIM
$deletandoPessoasQueNaoFacamParteDoGrupoDentroDosEventos = SQL::ini(FluxostatuspessoaQuery::deletarPessoasQueNaoFacamParteDoGrupoDentroDosEventos())::exec();
if($_inspecionar_sql)
{
	echo $deletandoPessoasQueNaoFacamParteDoGrupoDentroDosEventos->sql()."</br>";
	if($deletandoPessoasQueNaoFacamParteDoGrupoDentroDosEventos->error())
	{
		echo "<p style='$estiloFonteErro'>";
		echo $deletandoPessoasQueNaoFacamParteDoGrupoDentroDosEventos->errorMessage();
		echo "</p>";
	}
}

//INSERE TODAS AS PESSOAS QUE FORAM ADICIONADAS RECENTEMENTE DENTRO DOS EVENTOS COM STATUS DIFERENTE DE FIM	

$inserindoOutAtualizandoPessoasDoEventoDeAcordoComOGrupo = SQL::ini(FluxostatuspessoaQuery::inserirOutAtualizarPessoasDoEventoDeAcordoComOGrupo())::exec();
if($_inspecionar_sql)
{
	echo $inserindoOutAtualizandoPessoasDoEventoDeAcordoComOGrupo->sql()."</br>";
	if($inserindoOutAtualizandoPessoasDoEventoDeAcordoComOGrupo->error())
	{
		echo "<p style='$estiloFonteErro'>";
		echo $inserindoOutAtualizandoPessoasDoEventoDeAcordoComOGrupo->errorMessage();
		echo "</p>";
	}
}
	
//DELETA TODAS AS PESSOAS QUE NÃO PERTENCEM MAIS AOS GRUPOS DENTRO DAS CONFIGURAÇÕES DE ALERTA
ImGrupoController::deletarPessoasQueNaoFacamParteDoGrupoDeConfiguracaoDeAlerta();
echo '<br/>';
echo '<br/>';
echo '<br/>';

//INSERE TODAS AS PESSOAS QUE FORAM ADICIONADAS RECENTEMENTE DENTRO DAS CONFIGURAÇÕES DE ALERTA
//Alterado para aparecer as pessoas do grupo de cada empresa (Lidiane - 12-05-2020 - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=315016) - Alterado o 1 para p.idempresa
$inserindoPessoasAdicionadasNaConfiguracaoDeAlerta = SQL::ini(ImMsgConfDestQuery::inserirPessoasAdicionadasNaConfiguracaoDeAlerta())::exec();
if($_inspecionar_sql)
{
	echo $inserindoPessoasAdicionadasNaConfiguracaoDeAlerta->sql()."</br>";
	if($inserindoPessoasAdicionadasNaConfiguracaoDeAlerta->error())
	{
		echo "<p style='$estiloFonteErro'>";
		echo $inserindoPessoasAdicionadasNaConfiguracaoDeAlerta->errorMessage();
		echo "</p>";
	}
}	

echo '<br/>';
echo '<br/>';
echo '<br/>';

// GVT - 24/08/2020 - Deleta as assinaturas pendentes de colaboradores INATIVOS
$deletandoAssinaturasPendentesDeColaboradoresInativos = SQL::ini(CarimboQuery::deletarAssinaturasPendentesDeColaboradoresInativos())::exec();
if($_inspecionar_sql)
{
	echo $deletandoAssinaturasPendentesDeColaboradoresInativos->sql()."</br>";
	if($deletandoAssinaturasPendentesDeColaboradoresInativos->error())
	{
		echo "<p style='$estiloFonteErro'>";
		echo $deletandoAssinaturasPendentesDeColaboradoresInativos->errorMessage();
		echo "</p>";
	}
}	

$deletandoVinculoDePessoasDeletadas = SQL::ini(PessoaObjetoQuery::deletarVinculoDePessoasDeletadas())::exec();
if($_inspecionar_sql)
{
	echo $deletandoVinculoDePessoasDeletadas->sql()."</br>";
	if($deletandoVinculoDePessoasDeletadas->error())
	{
		echo "<p style='$estiloFonteErro'>";
		echo $deletandoVinculoDePessoasDeletadas->errorMessage();
		echo "</p>";
	}
}
$atualizandoEmails = EmailVirtualConfController::atualizarEmailsDeDestinoPorIdEmailVirtualConf();

$deletandoPessoasRemovidasDoSetorQUeNaoPossuemAssinaturaNoDocumento = SQL::ini(FluxostatuspessoaQuery::deletarPessoasRemovidasQueNaoPossuemAssinaturaNoDocumentoPorTipoObjetoExt(), ['tipoobjetoext' => 'sgsetor'])::exec();
$deletandoPessoasRemovidasDoSetorQUeNaoPossuemAssinaturaNoDocumento = SQL::ini(FluxostatuspessoaQuery::deletarPessoasRemovidasQueNaoPossuemAssinaturaNoDocumentoPorTipoObjetoExt(), ['tipoobjetoext' => 'sgdepartamento'])::exec();
$deletandoPessoasRemovidasDoSetorQUeNaoPossuemAssinaturaNoDocumento = SQL::ini(FluxostatuspessoaQuery::deletarPessoasRemovidasQueNaoPossuemAssinaturaNoDocumentoPorTipoObjetoExt(), ['tipoobjetoext' => 'sgarea'])::exec();
// $deletandoPessoasRemovidasDoSetorQUeNaoPossuemAssinaturaNoDocumento = SQL::ini(FluxostatuspessoaQuery::deletarPessoasRemovidasQueNaoPossuemAssinaturaNoDocumentoPorTipoObjetoExt(), ['tipoobjetoext' => 'sgconselho'])::exec();

if($_inspecionar_sql)
{
	echo $deletandoPessoasRemovidasDoSetorDepartamentoEAreaQueNaoPossuemAssinaturaDoDocumento->sql()."</br>";
	if($deletandoPessoasRemovidasDoSetorDepartamentoEAreaQueNaoPossuemAssinaturaDoDocumento->error())
	{
		echo "<p style='$estiloFonteErro'>";
		echo $deletandoPessoasRemovidasDoSetorDepartamentoEAreaQueNaoPossuemAssinaturaDoDocumento->errorMessage();
		echo "</p>";
	}
}

echo '<br/>';
echo '<br/>';
echo '<br/>';

$atualizandoVinculoOrganogramaDePessoasNoDocumento = SQL::ini(FluxostatuspessoaQuery::atualizarVinculoOrganogramaDePessoasNoDocumento())::exec();
if($_inspecionar_sql)
{
	echo $atualizandoVinculoOrganogramaDePessoasNoDocumento->sql()."</br>";
	if($atualizandoVinculoOrganogramaDePessoasNoDocumento->error())
	{
		echo "<p style='$estiloFonteErro'>";
		echo $atualizandoVinculoOrganogramaDePessoasNoDocumento->errorMessage();
		echo "</p>";
	}
}

echo '<br/>';
echo '<br/>';
echo '<br/>';

// Inserindo pessoas que nao estao vinculadas ao doc porem o seu setor esta
$inserindoPessoasQueNaoEstaoVinculadasMasSeuSetorEstaAoDoc = SQL::ini(FluxostatuspessoaQuery::inserirPessoasQueNaoEstaoVinculadasMasSeuSetorEstaAoDoc())::exec();
if($_inspecionar_sql)
{
	echo $inserindoPessoasQueNaoEstaoVinculadasMasSeuSetorEstaAoDoc->sql()."</br>";
	if($inserindoPessoasQueNaoEstaoVinculadasMasSeuSetorEstaAoDoc->error())
	{
		echo "<p style='$estiloFonteErro'>";
		echo $inserindoPessoasQueNaoEstaoVinculadasMasSeuSetorEstaAoDoc->errorMessage();
		echo "</p>";
	}
}

echo '<br/>';
echo '<br/>';
echo '<br/>';

// Inserido coordenadores e pessoas de setores de um departamento ao doc( caso n estejam )
$inserindoPessoasECoordenadoresDeSetoresQueNaoEstejamNoDocumento = SQL::ini(FluxostatuspessoaQuery::inserirPessoasECoordenadoresDeSetoresQueNaoEstejamNoDocumento())::exec();
if($_inspecionar_sql)
{
	echo $inserindoPessoasECoordenadoresDeSetoresQueNaoEstejamNoDocumento->sql()."</br>";
	if($inserindoPessoasECoordenadoresDeSetoresQueNaoEstejamNoDocumento->error())
	{
		echo "<p style='$estiloFonteErro'>";
		echo $inserindoPessoasECoordenadoresDeSetoresQueNaoEstejamNoDocumento->errorMessage();
		echo "</p>";
	}
}

echo '<br/>';
echo '<br/>';
echo '<br/>';

// Inserindo coorenadores de uma area ao doc vinculaco ( caso n estejam )
$inserindoCoordenadoresDaAreaVinculadaAoDocumentoCasoNaoEstejam = SQL::ini(FluxostatuspessoaQuery::inserirCoordenadoresDaAreaVinculadaAoDocumentoCasoNaoEstejam())::exec();
if($_inspecionar_sql)
{
	echo $inserindoCoordenadoresDaAreaVinculadaAoDocumentoCasoNaoEstejam->sql()."</br>";
	if($inserindoCoordenadoresDaAreaVinculadaAoDocumentoCasoNaoEstejam->error())
	{
		echo "<p style='$estiloFonteErro'>";
		echo $inserindoCoordenadoresDaAreaVinculadaAoDocumentoCasoNaoEstejam->errorMessage();
		echo "</p>";
	}
}

echo '<br/>';
echo '<br/>';
echo '<br/>';

// VINCULAR PESSOAS QUE ESTEJAM NO DOC E TENHA SEU SETOR E QUE ESTEJAM SEM VINCULO
$vinculandoPessoasQueEstejamNoDocumentoTenhaSeuSetorEEstejamSemVinculoNoDocumento = SQL::ini(FluxostatuspessoaQuery::vincularPessoasQueEstejamNoDocumentoTenhaSeuSetorEEstejamSemVinculoNoDocumento())::exec();
if($_inspecionar_sql)
{
	echo $vinculandoPessoasQueEstejamNoDocumentoTenhaSeuSetorEEstejamSemVinculoNoDocumento->sql()."</br>";
	if($vinculandoPessoasQueEstejamNoDocumentoTenhaSeuSetorEEstejamSemVinculoNoDocumento->error())
	{
		echo "<p style='$estiloFonteErro'>";
		echo $vinculandoPessoasQueEstejamNoDocumentoTenhaSeuSetorEEstejamSemVinculoNoDocumento->errorMessage();
		echo "</p>";
	}
}

echo '<br/>';
echo '<br/>';
echo '<br/>';

// Atualizar pessoas que foram retiradas do setor / departamento / area e que possuem assinatura do doc
$atualizandoPessoasRetiradasDoSetorDepartamentoOuAreaQuePossuemAssinaturaDoDocumento = SQL::ini(FluxostatuspessoaQuery::atualizarPessoasRetiradasDoSetorDepartamentoOuAreaQuePossuemAssinaturaDoDocumento())::exec();
if($_inspecionar_sql)
{
	echo $atualizandoPessoasRetiradasDoSetorDepartamentoOuAreaQuePossuemAssinaturaDoDocumento->sql()."</br>";
	if($atualizandoPessoasRetiradasDoSetorDepartamentoOuAreaQuePossuemAssinaturaDoDocumento->error())
	{
		echo "<p style='$estiloFonteErro'>";
		echo $atualizandoPessoasRetiradasDoSetorDepartamentoOuAreaQuePossuemAssinaturaDoDocumento->errorMessage();
		echo "</p>";
	}
}

echo '<br/>';
echo '<br/>';
echo '<br/>';

// Oculta o evento para as pessoas q estao vinculadas ao status q possui a coluna ocultar = Y
EventoController::ocultarEventos();

$grupo = rstr(8);

re::dis()->hMSet('bim',['fim' => Date('d/m/Y H:i:s')]);

$dadosLog = [
	'idempresa' => $_SESSION["SESSAO"]["IDEMPRESA"],
	'sessao' => $grupo,
	'tipoobjeto' => 'cron',
	'idobjeto' => 'bim',
	'tipolog' => 'status',
	'log' => 'FIM',
	'status' => 'SUCESSO',
	'info' => '',
	'criadoem' => "NOW()",
	'data' => "NOW()"
];

$inserirLog = LogController::inserir($dadosLog);

echo '<br/>';
echo '<br/>';
echo '<br/>';

echo "Fim: ".date("d/m/Y H:i:s", time()).'<br>';

re::dis()->del($chaveCron);

?>