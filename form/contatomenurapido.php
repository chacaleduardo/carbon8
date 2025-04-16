<?php
require_once "../inc/php/validaacesso.php";
require_once "./controllers/pessoa_controller.php";
require_once "./controllers/contatomenurapido_controller.php";

$nomeCliente = " " . $_SESSION["SESSAO"]["NOME"];

$portifolioArquivo = [];
if ($_SESSION["SESSAO"]["IDTIPOPESSOA"] == 3)
{

	// Verifica se a pessoa tem resultados de autogenas
	$numeroAmostrasPorUnidade = ContatoMenuRapidoController::buscarNumeroResultadosPorUnidadeCliente($_SESSION['SESSAO']['IDPESSOA']);
	
	$filtrarresultados = ($numeroAmostrasPorUnidade['diagnostico'] > 0) ? 'Y' : 'N';
	$filtrarresultadostra = ($numeroAmostrasPorUnidade['autogena'] > 0) ? 'Y' : 'N';

	
	// Verifica arquivo de portifolio
	$pessoa = PessoaController::buscarPessoaPorId($_SESSION['SESSAO']['IDPESSOA']);
	if($pessoa && $pessoa['status'] == 'ATIVO')
	{
		require_once "./controllers/conciliacaofinanceira_controller.php";
		$portifolioArquivo = ConciliacaoFinanceiraController::buscarArquivoPorTipoObjetoEIdObjeto($_SESSION['SESSAO']['IDPESSOA'], 'portifolio');
	}
}
else
{
	$filtrarresultados = 'Y';
	$filtrarresultadostra = 'Y';
}

//verifica se o cliente é do plantel de pet
if(PessoaController::verficarPlantelPessoa($_SESSION['SESSAO']['IDPESSOA'], 34)){
	$_SESSION['SESSAO']['PESSOAPLANTEL'] = 34;
}

// Definição de qual dos menus de clientes será exibido
// A condição abaixo deve ser temporária e de uso exclusivo para avaliação da nova tela de cliente em produção
// (array_key_exists("novatelapet", getModsUsr("MODULOS")) && $_GET['telapet']=='Y')

if ( $_SERVER['SERVER_NAME'] == RESULTADOSURL && cb::idempresa() == 1 ) {
    if ($_SESSION['SESSAO']['PESSOAPLANTEL'] == 34) {
		//echo 'novatela pet';
        menuLaudoTailwind($portifolioArquivo);
    } else {
        menuLaudoBootstrap();
    }
} elseif ($_SERVER['SERVER_NAME'] == 'resultados.inata.com.br' || cb::idempresa() == 2) {
    menuInataBootstrap();
}

//novo menu de resultados em tailwind
function menuLaudoTailwind($portifolioArquivo){
	$portifolioArquivo = $portifolioArquivo ? $portifolioArquivo['caminho'] : '#';
	echo '<div class="w-full lg:w-10/12 xl:w-11/12 2xl:w-10/12 4xl:w-5/12 flex flex-col m-auto gap-1 justify-center items-center mt-10">
			<span class="w-10/12 lg:w-auto font-bold text-center md:text-center card-panel">
				Recebemos materiais de nossos clientes de diversas formas: correios, transportadoras, ônibus, e outros.
			</span>
			<div class="contato-menu-rapido w-full flex flex-wrap p-4 md:justify-between xl:justify-between flex-col md:flex-row">
				<a href="?_modulo=enviopedidoamostra" class="contato-menu-item w-full md:w-6/12 xl:w-3/12 px-3 text-white cursor-pointer mb-5 xl:mb-0 h-[15rem] md:h-auto">
					<div class="h-full p-6 flex flex-col gap-4 items-center justify-end" style="background-image: url(\'/inc/img/contatomenurapido/novopedido.svg\')" alt="">
						<span class="text-white font-bold text-xl">Envio de amostra</span>
					</div>
				</a>
				<a href="?_modulo=materialcoleta" class="contato-menu-item w-full md:w-6/12 xl:w-3/12 px-3 text-white cursor-pointer mb-5 xl:mb-0 h-[15rem] md:h-auto">
					<div class="h-full p-6 flex flex-col gap-4 items-center justify-end" style="background-image: url(\'/inc/img/contatomenurapido/pedidos.svg\')" alt="">
						<span class="text-white font-bold text-xl">Solicitação de materiais</span>
					</div>
				</a>

				<a href="?_modulo=portifolio" class="contato-menu-item w-full md:w-6/12 xl:w-3/12 px-3 text-white cursor-pointer mb-5 xl:mb-0 h-[15rem] md:h-auto">
					<div class="h-full p-6 flex flex-col gap-4 items-center justify-end" style="background-image: url(\'/inc/img/contatomenurapido/portifolio.svg\')" alt="">
						<span class="text-white font-bold text-xl">Portifólio</span>
					</div>
				</a>
				<a href="?_modulo=resultadocliente" class="contato-menu-item w-full md:w-6/12 xl:w-3/12 px-3 text-white cursor-pointer mb-5 xl:mb-0 h-[15rem] md:h-auto">
					<div class="h-full p-6 flex flex-col gap-4 items-center justify-end" style="background-image: url(\'/inc/img/contatomenurapido/resultados.svg\')" alt="">
						<span class="text-white font-bold text-xl">Resultados</span>
					</div>
				</a>
			</div>
		</div>
		';
}

//menu antigo de resultados em bootstrap

function menuLaudoBootstrap()
{
	echo '
			<div id="containerbemvindo">
				<div class="colBemvindo1">
					<img id="mascote" src="inc/img/laudolabavicola.png">
				</div>
				<div class="colBemvindo2">
					<a id="bt7" cbrelacionado="preview1" class="btn btn-lg btn-block" href="?_modulo=dashboardcliente">
						<span>Resultados por Lote/N&uacute;cleo<i class="fa fa-chevron-right"></i>
					</a>
					<a id="bt6" cbrelacionado="preview2" class="btn btn-lg btn-block" href="?_modulo=cliente_filtrarresultados">
						<span>Filtrar Resultados</span><i class="fa fa-chevron-right"></i>
					</a>
					<a id="bt8" cbrelacionado="preview3" class="btn btn-lg btn-block" href="?_modulo=complote">
						<span>Comparativo de lotes</span><i class="fa fa-chevron-right"></i>
					</a>
				</div>
				<img src="inc/img/previewDashboard.png" id="preview1">
				<img src="inc/img/previewPesquisa.png" id="preview2">
				<img src="inc/img/previewComparativo.png" id="preview3">
			</div>';
		styleAntigo();
		
}

// menu de resultados do inata em bootstrap
function menuInataBootstrap()
{
	echo '<div id="containerbemvindo">
			<div class="colBemvindo1">
				<img id="mascote" src="inc/img/animais-vetor.png" alt="Mascote do Inata">
			</div>
			<div class="colBemvindo2">
				<a id="bt5" cbrelacionado="preview2" class="btn btn-success btn-lg btn-block" href="?_modulo=cliente_filtrarresultadostra&_idempresa='.cb::idempresa().'">
					<span>Filtrar Resultados - Autógenas</span><i class="fa fa-chevron-right"></i>
				</a>
			</div>
			<img src="inc/img/previewPesquisa.png" id="preview2">
		</div>';
	styleAntigo();
	
}

function styleAntigo() {
	echo '
	<style>
		#cbModuloForm{
			background-color: transparent;
			border: none;
			-webkit-box-shadow: none;
			box-shadow: none;
		}
		#mascote{
			position: fixed;
			left: 10px;
			bottom: 10px;
			height: 25%;
		}
		#containerbemvindo{
		width: 100%;
		position: fixed;
		left: 0px;
		display: flex;
		height: 95%;
		}
		.colBemvindo1{
			width: 33%;
		}
		.colBemvindo2{
			width: 33%;
		}
		.colBemvindo2 #bt1,
		.colBemvindo2 #bt2,
		.colBemvindo2 #bt3,
		.colBemvindo2 #bt4,
		.colBemvindo2 #bt5,
		.colBemvindo2 #bt6,
		.colBemvindo2 #bt7,
		.colBemvindo2 #bt8{
			position: fixed;
			height: 70px;
			padding-top: 21px;
			-webkit-box-shadow: 0 5px 10px rgba(0, 0, 0, 0.2);
			box-shadow: 0 5px 10px rgba(0, 0, 0, 0.2);
		}



		.colBemvindo2 #bt1{
			bottom: 80%;
			width: 30%;
		}
		.colBemvindo2 #bt2{
			bottom: 60%;
			width: 30%;
		}
		.colBemvindo2 #bt3{
			bottom: 40%;
			width: 30%;
		}
		.colBemvindo2 #bt4{
			bottom: 20%;
			width: 30%;
		}

		.colBemvindo2 #bt5{
			bottom: 40%;
			width: 30%;
		}

		.colBemvindo2 #bt6{
			bottom: 40%;
			width: 30%;
			background-color: gray;
			color: white;
		}

		.colBemvindo2 #bt7{
			bottom: 60%;
			width: 30%;
			background-color: gray;
			color: white;
		}

		.colBemvindo2 #bt8{
			bottom: 20%;
			width: 30%;
			background-color: gray;
			color: white;
		}

		.colBemvindo2 a i{
			margin-top: 4px;
			right: 8px;
			position: absolute;
		}
		.colBemvindo3{
			width: 33%;
		}

		#preview1{
			height: 400px;
			width: 400px;
			top: 15%;
			position: relative;
			display: none;
		}
		#preview2{
			height: 300px;

			top: 20%;
			position: relative;
			display: none;
		}
		#preview3{
			height: 400px;
			width: 400px;
			top: 25%;
			position: relative;
			display: none;
		}

		</style>
	';
	require_once './js/contatomenurapido_js.php';
}
