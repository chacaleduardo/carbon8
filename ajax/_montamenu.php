<?
require_once("../inc/php/functions.php");

$jwt = validaTokenReduzido();
$string = "cb-idpessoa: ".$_SESSION['SESSAO']['IDPESSOA'];

if($jwt["sucesso"] !== true){
	header("HTTP/1.1 401 Unauthorized");
	echo json_encode($jwt);	
	die;
}

$debug = $_GET['debug'];

$menu = [
	'superior' => '',
	'lateral' => '',
	'modalSnippetAcao' => ''
];

if(in_array($_SESSION['SESSAO']['IDTIPOPESSOA'], [1])) {
	try 
	{
		$menu['superior'] = '<div class="navbar-collapse p-0 border-0" id="navbarSupportedContent">
		<ul class="navbar-nav m-0 flex-row p-0">';
		
		if(logado())
		{
			if($debug) echo 'LOGADO: MONTANDO MENU SUPERIOR <br>';
			$menu['superior'] .= modalAlterarEmpresaMenuSuperior();
			if($debug) echo 'LOGADO: MONTANDO MENU SNIPPETS <br>';
			$menu['superior'] .= montarSnippets($debug);
			if($debug) echo error_get_last()['message'];
		}

		$menu['superior'] .= '		</ul>
			</div>';

		$menu['lateral'] = "<div class='d-flex flex-column pt-2 text-white min-vh-100'>
			<div id='btn-menu' class='nav-item open-menu px-3'>
				<a href='/' class='nav-link d-flex align-items-center px-0 white-space-nowrap'>
					<i class='fa fa-chevron-right'></i>
					<span class='ml-4'>Meu modulos</span>
				</a>
			</div>
			<div class='nav-item open-menu px-3 mb-3'>
				<div class='d-flex align-items-center px-0 white-space-nowrap py-2'>
					<i class='fa fa-search'></i>
					<div class='relative w-100 d-flex align-items-center'>
						<i id='btn-input-search-clear' class='fa fa-close pointer opacity-0 text-gray-60'></i>
						<input id='input-search' placeholder='Pesquisar módulo' class='ml-4' />
					</div>
				</div>
			</div>
			<div class='d-flex flex-column nav nav-pills mb-sm-auto mb-0' id='menu'>
				".montaModulosMenuLateral()."
			</div>
		</div>";

		$menu['modalSnippetAcao'] = montaSnippetsAcao();
	} catch (Exception $e)
	{
		echo json_encode([
			'error' => $e->getMessage()
		]);
	}
} else {
	$menu['superior'] = '<div class="" style="display:flex;max-width:100vw;">
								<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
									<ul class="nav navbar-nav">';

	$menu['superior'] .= modalAlterarEmpresaMenuSuperior();

	if(logado()){
		$menu['superior'] .= montarMenuSuperiorAntigo();
	}

	$menu['superior'] .= "			</ul>
								</div>
							</div>
							<i class='fa fa-chevron-down' title='Mostrar menu' id='cbMostrarMenu' onmouseover='$('body').removeClass('minimizado');'></i>";
}

echo json_encode($menu);
