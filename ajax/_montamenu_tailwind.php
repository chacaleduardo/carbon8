<?
require_once("../inc/php/functions.php");

class MontaMenu
{
	private $menu = [
		'superior' => [],
		'lateral' => [],
		'modalSnippetAcao' => []
	];

	public function __construct()
	{
	}

	function modalAlterarEmpresaMenuSuperior()
	{
		if (!empty($_SESSION["SESSAO"]["IDPESSOA"])) {
			$_sqlempresa = "SELECT idempresa,corsistema,iconelateral,iconemodal 
			FROM empresa 
			WHERE status='ATIVO'
				AND idempresa in (
					SELECT empresa 
					FROM objempresa 
					WHERE idobjeto=" . $_SESSION["SESSAO"]["IDPESSOA"] . " 
					AND objeto='pessoa'
				)";
		} else {
			$_sqlempresa = "SELECT idempresa,corsistema,iconelateral,iconemodal FROM empresa WHERE status='ATIVO'";
		}

		$_resempresa = d::b()->query($_sqlempresa) or die("Erro ao buscar Empresas: Erro: " . mysqli_error(d::b()) . "\n" . $_sqlempresa);
		if(mysqli_num_rows($_resempresa)==0){
			$_sqlempresa = "SELECT idempresa,corsistema,iconelateral,iconemodal FROM empresa WHERE status='ATIVO' and idempresa=".cb::idempresa();
			$_resempresa = d::b()->query($_sqlempresa);
		}
		$arrEmpresas = [];
		$ii = 0;

		while ($_rowempresa = mysqli_fetch_assoc($_resempresa)) {
			if ($_rowempresa["idempresa"] == cb::idempresa() and $_SESSION["SESSAO"]["SUPERUSUARIO"] != true) {
				$modalMenu['corSistema'] = $_rowempresa["corsistema"];
				$modalMenu['_url'] = "." . preg_replace('/(^\.+)/', '', $_rowempresa["iconelateral"]);
			}

			if ($_rowempresa["idempresa"] != cb::idempresa()) {
				$arrEmpresas[$ii]["idempresa"] = $_rowempresa["idempresa"];
				$arrEmpresas[$ii]["iconemodal"] = "." . preg_replace('/(^\.+)/', '', $_rowempresa["iconemodal"]);
				$ii++;
			}
		}

		$modalMenu['arrEmpresas'] = $arrEmpresas;
		$modalMenu['idempresa'] = cb::idempresa();

		return $modalMenu;
	}

	function montarSnippets()
	{
		/* $urllogout = "?_acao=logout";
		$idempresa = cb::idempresa();
		$linkIdempresa = "&_idempresa=" . $idempresa; */

		$snippets['modsEmpresa'] = getModsUsr("MODULOS");
		$snippets['bg'] = str2Color($_SESSION["SESSAO"]["NOME"]);
		$snippets['fc'] = colorContrastYIQ($snippets['bg']);
		$snippets['snippets'] = getSnippets();

		$snippetPrincipal = $snippets['snippets']['padrao'];

		if (($_SESSION['SESSAO']['IDTIPOPESSOA'] == 1) && count($snippets['snippets']['snippetprincipal'])){
			$snippetPrincipal = $snippets['snippets']['snippetprincipal'];
		}

		foreach ($snippetPrincipal as $key => $s) {
			$onclick = "";
			if ($s["tipo"] == "PHP") {
				if (strlen(trim($s["msgconfirm"])) > 0) {
					$onclick = "if(confirm('" . $s["msgconfirm"] . "'))CB.snippet('{$s['idsnippet']}');";
				} else {
					$onclick = "CB.snippet('{$s['idsnippet']}');";
				}
			} elseif ($s["tipo"] == "LINK") {
				$onclick = "$(CB.oModuloHeader, CB.oModuloHeaderBg).addClass('hidden');CB.loadUrl({urldestino: '" . $s["code"] . "'});";
			} elseif ($s["tipo"] == "JS" or $s["tipo"] == "MOD") {
				$fname = "_" . md5(uniqid());
				$onclick = $fname . "()";

				$script = "<script>
								function $fname() {
									{$s['code']}
								}
								//# sourceURL=snippet_{$s['idsnippet']}
							</script>";
			}

			if (strpos($s["cssicone"], "/upload/") === 0) {
				// $iconeSnippet = "<img style='width:16px;align-self: center;' class='snippet' src='.".$s["cssicone"]."'/>";
				$iconeSnippet = file_get_contents("{$_SERVER['REQUEST_SCHEME']}://{$_SERVER['HTTP_HOST']}{$s["cssicone"]}");
			} else {
				$iconeSnippet = "<i class='{$s["cssicone"]}'></i>";
			}

			$snippets[] = [
				'id'=>"cbSnippet{$s['idsnippet']}",
				'class'=>'snippet',
				'href'=>"javascript:{$onclick}",
				'title'=>$s["snippet"],
				'attr' => [
					"cbmodulo"=>$s["modulo"],
					"modulo"=>$s["modulo"]
				],
				'style'=>"",
				'icone'=>$iconeSnippet,
				'texto'=> $s["snippet"],
				'script'=>$script
			];
		}

		// Snippet Acao
		if (count($snippets['snippetacao'])) {

			$snippets[] = [
				'id'=>"",
				'class'=>'snippet',
				'href'=>"#",
				'title'=>"Módulos de ação",
				'attr' => [
					"cbmodulo"=>"snippetacao",
					"modulo"=>""
				],
				'style'=>"",
				'icone'=>"<i class='fa fa-plus'></i>",
				'texto'=> ""
			];
		}


		foreach ($snippets['snippetsecundario'] as $key => $snippet) {
			$onclick = "";
			if ($s["tipo"] == "PHP") {
				if (strlen(trim($snippet["msgconfirm"])) > 0) {
					$onclick = "if(confirm('" . $snippet["msgconfirm"] . "'))CB.snippet('{$snippet['idsnippet']}');";
				} else {
					$onclick = "CB.snippet('{$snippet['idsnippet']}');";
				}
			} elseif ($snippet["tipo"] == "LINK") {
				$onclick = "$(CB.oModuloHeader, CB.oModuloHeaderBg).addClass('hidden');CB.loadUrl({urldestino: '" . $snippet["code"] . "'});";
			} elseif ($snippet["tipo"] == "JS" or $snippet["tipo"] == "MOD") {
				$fname = "_" . md5(uniqid());
				$onclick = $fname . "()";

				$script = "<script>
							function $fname() {
								{$snippet['code']}
							}
							//# sourceURL=snippet_{$snippet['idsnippet']}
						</script>";
			}

			$iconeSnippet = "<i class='{$snippet["cssicone"]}'></i>";

			if (strpos($snippet["cssicone"], "/upload/") === 0) {
				$iconeSnippet = "<img style='width:16px;align-self: center;' class='snippet' src='." . $snippet["cssicone"] . "'/>";
			}

			$snippets[] = [
				'id'=>"cbSnippet{$snippet['idsnippet']}",
				'class'=>'snippet',
				'href'=>"javascript:{$onclick}",
				'title'=>"{$snippet["snippet"]}",
				'attr' => [
					"cbmodulo"=>"{$snippet["modulo"]}",
					"modulo"=>"{$snippet["modulo"]}"
				],
				'style'=>"",
				'icone'=>$iconeSnippet,
				'texto'=> $snippet["snippet"],
				'script'=>$script
			];
		}
		
		$snippets['dropdownUser1']=[
			'id'=>'dropdownUser1',
			'class'=>'dropdown-toggle',
			'title'=>"{$_SESSION["SESSAO"]["NOME"]} LP: {$_SESSION['SESSAO']['IDLP']}",
			'attr' => [
				'data-toggle'=>'dropdown', 
				'aria-expanded'=>'false',
				'role'=>'button',
				'cbidpessoa'=>"{$_SESSION["SESSAO"]["IDPESSOA"]}",
			],
			'style'=>"color:{$fc};background-color:#{$bg};",
			'icone'=>'caret',
			'texto'=> mb_substr($_SESSION["SESSAO"]["NOME"], 0, 2)
		];

		$snippets['dropdownUser1']['menu'][] = [
			'title'	=> '',
			'target'=> '',
			'href'	=> 'javascript:janelamodal(\"report/relfuncionario.php\");',
			'class'	=> 'dropdown-item',
			'icone'	=> 'fa fa-info-circle vermelho',
			'texto'	=> '&nbsp;Minhas Informações'
		];
		if ($_SESSION["SESSAO"]["IDTIPOPESSOA"] == 1) {
			$snippets['dropdownUser1']['menu'][] = [
				'title'	=> '',
				'target'=> '',
				'href'	=> 'javascript:janelamodal(\"report/relfuncionario.php\");',
				'class'	=> 'dropdown-item',
				'icone'	=> 'fa fa-info-circle vermelho',
				'texto'	=> '&nbsp;Minhas Informações'				
			];
			$snippets['dropdownUser1']['menu'][] = [
				'title'	=> '',
				'target'=> '',
				'href'	=> '?_modulo=eventoponto$linkIdempresa',
				'class'	=> 'dropdown-item',
				'icone'	=> 'fa fa-clock-o vermelho',
				'texto'	=> '&nbsp;Ponto'				
			];
			$snippets['dropdownUser1']['menu'][] = [
				'id'	=> 'ramais',
				'title'	=> '',
				'target'=> '',
				'href'	=> '#',
				'class'	=> 'dropdown-item',
				'icone'	=> 'fa fa-phone-square vermelho',
				'texto'	=> '&nbsp;Ramais',
				'script' => `
						<script>
							$('#ramais').click(function(){
								CB.modal({
									url:'?_modulo=ramalcolaboradores',
									header:'Ramais',
									menu: false
								});
							});
						</script>`
			];
		}

		if ($_SESSION["SESSAO"]["IDTIPOPESSOA"] == 15 or $_SESSION["SESSAO"]["IDTIPOPESSOA"] == 1) {
			
			$sql = "SELECT p.idpessoa 
					from pessoacontato c
					join pessoa p on(p.idpessoa=c.idpessoa and p.idtipopessoa in (5,12)) 
					where c.idcontato = " . $_SESSION["SESSAO"]["IDPESSOA"];

			$res = d::b()->query($sql);
			$qtd = mysqli_num_rows($res);

			$row = mysqli_fetch_assoc($res);

			if (!empty($row['idpessoa']) and $qtd > 0) {
				$snippets['dropdownUser1']['menu'][] = [
					'title'	=> '',
					'target'=> '',
					'href'	=> '',
					'class'	=> 'divider',
					'icone'	=> '',
					'texto'	=> ''				
				];
				$snippets['dropdownUser1']['menu'][] = [
					'title'	=> 'Webmail',
					'target'=> '_blank',
					'href'	=> '?_modulo=comprasrhrestrito$linkIdempresa',
					'class'	=> 'dropdown-item',
					'icone'	=> 'fa fa-user-plus vermelho',
					'texto'	=> '&nbsp;RH Restrito'				
				];
			}
		}

		if ($_SESSION["SESSAO"]["IDTIPOPESSOA"] == 1 or $_SESSION["SESSAO"]["IDTIPOPESSOA"] == 15 or $_SESSION["SESSAO"]["IDTIPOPESSOA"] == 16)
		{
			/* Webmail */
			$snippets['dropdownUser1']['menu'][] = [
				'title'	=>'Webmail',
				'target' => '_blank',
				'href'	=>'form/webmail.php',
				'class'	=>'dropdown-item',
				'icone'	=> 'fa fa-envelope vermelho',
				'texto'	=> '&nbsp;Webmail'				
			];
		}

		if ($_SESSION["SESSAO"]["IDTIPOPESSOA"] == 1 and array_key_exists("organograma", $modsEmpresa))
		{
			/* Organograma */
			$snippets['dropdownUser1']['menu'][] = [
				'title'	=>'Organograma',
				'target' => '_blank',
				'href'	=>'report/organograma.php?_idempresa='.$idempresa,
				'class'	=>'dropdown-item',
				'icone'	=> 'fa fa-sitemap vermelho',
				'texto'	=> '&nbsp;Organograma'				
			];
		}

		if ($_SESSION["SESSAO"]["SUPERUSUARIO"] != true)
		{
			/* Alterar senha */
			$snippets['dropdownUser1']['menu'][] = [
				'title'	=>'',
				'href'	=>'?_modulo=alterasenha',
				'class'	=>'dropdown-item',
				'icone'	=> 'fa fa-key vermelho',
				'texto'	=> '&nbsp;Alterar Senha'				
			];
		}

		if (verificaSuperUsuario())
		{
			/* Super usuário */
			$snippets['dropdownUser1']['menu'][] = [
				'title'	=>'Super usuário: utilizar como outro usuário',
				'href'	=>'javascript:alterarUsuario()',
				'class'	=>'dropdown-item',
				'icone'	=> 'fa fa-support vermelho',
				'texto'	=> '&nbsp;Alternar usuário'				
			];
		}
		/* Logout */
		$snippets['dropdownUser1']['menu'][] = [
			'title'		=>	'',
			'onclick'	=>	"javascript:localStorage.removeItem('jwt');Cookies.remove('jwt');Cookies.remove('PHPSESSID');window.location.href='{$urllogout}';",
			'href'		=>	"#",
			'class'		=>	'dropdown-item',
			'icone'		=>	'fa fa-power-off vermelho',
			'texto'		=>	'&nbsp;Logout'			
		];
		/* Sobre o Sistema */
		$snippets['dropdownUser1']['menu'][] = [
			'title'	=>'Sobre o Sistema',
			'href'	=>"javascript:sobreOSistema();",
			'class'	=>'dropdown-item',
			'icone'	=> 'fa fa-question-circle-o azulclaro',
			'texto'	=> '&nbsp;Sobre o sistema'			
		];

		return $snippets;
	}

	/*
	* Menu superior da aplicação
	*/
	function montaModulosMenuLateral()
	{
		global $_headers;

		$mostrarMenu = "";
		$menuLateralHTML = "";
		$idempresa = cb::idempresa();

		$linkIdempresa = "&_idempresa=".$idempresa;

		//maf: 181020: não montar dentro de webviews no app
		if( $_headers["cb-canal"] == "webview" ){
			return false;
		} else if($_headers["cb-canal"] == "app"){
			$mostrarMenu = '&_menu=N';
		}

		//Cria cor de back e foreground para o avatar do usuário
		$bg = str2Color($_SESSION["SESSAO"]["NOME"]);
		$fc = colorContrastYIQ($bg);
		
		$urllogout = "?_acao=logout";

		$modsEmpresa = getModsUsr("MODULOS");
		$sqlWhereMod = getModsUsr("SQLWHEREMOD");

		$qr = "SELECT 
				m.modulo
			FROM
				objempresa o
					JOIN
				"._DBCARBON."._modulo m ON (o.idobjeto = m.idmodulo)
			WHERE
				o.objeto = 'modulo' AND o.empresa = ".cb::idempresa()."
					AND m.modulo IN (".$sqlWhereMod.")";
		$rs = d::b()->query($qr);
		$arrMod = array();
		while($rw = mysqli_fetch_assoc($rs)){
			$arrMod[] = $rw["modulo"];
		}
		foreach($modsEmpresa as $i => $item){
			if(!in_array($i, $arrMod))
				unset($modsEmpresa[$i]);
			
			if(in_array($i, $arrMod) && $i == 'contatomenurapido' && $_SESSION["SESSAO"]["IDPESSOA"] == 112378)
				unset($modsEmpresa[$i]);
		}

		$arrMenu = formataArrayModulos($modsEmpresa);

		foreach($arrMenu as $modulo => $menu){

			$iSubMenu = count($menu["sub"]);

			$classDrop = "nav-item px-3 rounded";
			$link="?_modulo=".$modulo.$linkIdempresa.$mostrarMenu;

			if($iSubMenu>0)
			{
				$link="#menu-lateral-$modulo";
			}

			if(empty($menu["cssicone"])){
				$icone = "<span class='visible-sm visible-xs'>".strtoupper(substr($menu["rotulomenu"],0,2))."</span>";
			}else if (strpos($menu["cssicone"], "/upload/") === 0) {
				$icone = "<img style='width:16px;margin-right:6px;' src='.".$menu["cssicone"]."'/>";
			}else{
				$icone = "<i class='".$menu["cssicone"]."'></i>";
			}					
		
			$dataCollapse = $iSubMenu ? 'data-toggle="collapse"' : '';

			$menuLateralHTML .= "<div cbmodulo='$modulo' class='$classDrop' onclick='abrirMenu()'>
				<a href='$link' class='nav-link d-flex align-items-center px-0 rounded white-space-nowrap' $dataCollapse>
					<div class='menu-icon'>$icone</div>
					<span class='cbMenuSuperiorTitle ml-4'>{$menu['rotulomenu']}</span>";

			if($iSubMenu>0){
				$menuLateralHTML .= "<i class='dropdown-icon transition fa fa-chevron-down'></i>";
			}

			$menuLateralHTML .= "</a>";

			if($iSubMenu>0)
			{
				$menuLateralHTML .= "<ul id='menu-lateral-$modulo' class='collapse pt-3 pl-0 w-100' role='menu'>";

				foreach ($menu['sub'] as $submenu => $item) 
				{
					$bordaInferior = $item['divisor'] == 'Y' ? 'border-t-1 border-gray-50' : '';
					$menuLateralHTML .= "<a href='?_modulo={$item['modulo']}{$linkIdempresa}{$mostrarMenu}' class='nav-link px-0 white-space-nowrap'>
						<li cbmodulo='$submenu' class='rounded $bordaInferior'>
							<div class='w-100'>{$item['rotulomenu']}</div>
						</li>		
					</a>";
				}

				$menuLateralHTML .= "</ul>";
			}
			
			$menuLateralHTML .= "</div>";
		}

		$menuLateralHTML .= "
		<script>
			$('#ramais').click(function(){
				CB.modal({
					url:'?_modulo=ramalcolaboradores',
					header:'Ramais',
					menu: false
				});
			});
		</script>";

		if($_SESSION["SESSAO"]["PERMISSAOCHAT"]=="Y" and $_SESSION["SESSAO"]["SUPERUSUARIO"] != true)
		{
			$menuLateralHTML .=	"<div id='cbNotificacoes' class='dropdown pull-right snippet' onclick='chat.abrirContainerChat();chat.montarContatos();chat.recuperarAvatar();chat.maximizar();'>
				<a id='cbBadge' href='#' class='fa fa-comment hide' data-toggle='dropdown' role='button' aria-expanded='false'>
					<span id='cbIBadge' class='badge fundovermelho' style='display: none;'></span>
				</a>
				<ul id='cbListaNotificacoes' class='Xdropdown-menu' role='Xmenu' style='padding: 0px;border: 0px;display: none;'>
					<li id='cbListaNotificacoesHeader' style='padding: 4px 10px;'>
						<table style='width: 100%;'>
							<tbody><tr><td style='color: silver;font-weight: bold;'>Notificações:</td>
								<td></td><td><span class='azul pointer' onclick='chat.marcarLida('*')'>Marcar todas como lidas</span></td>
								<td><a href='#'><i class='fa fa-cog azul pointer'></i></a></td>
							</tr>
						</tbody></table>
					</li>
					<li id='cbListaNotificacoesFooter' style='padding: 4px 10px;'>
						<table style='width: 100%;'>
						<tbody>
						<tr>
							<td></td>
							<td >
								<a href='javascript:chat.abrirContainerChat();chat.maximizar();chat.montarContatos();chat.recuperarAvatar();' class='pointer floatright'>Abrir janela de Chat</a>
							</td>
						</tr>
						</tbody>
						</table>
					</li>
				</ul>
			</div>";
		}

		return $menuLateralHTML;
	}

	function montaSnippetsAcao()
	{
		global $_headers;
	
		$idempresa = cb::idempresa();
		$lps = getModsUsr('LPS');
		$snippetAcaoHTML = "";
	
		if($_headers["cb-canal"] == "app"){
			$mostrarMenu = 'N';
		} else {
			$mostrarMenu = 'Y';
		}
	
		$snippets = _SnippetController::buscarSnippetsPorLpIdEmpresaEModulos($lps, $idempresa, getModsUsr('SQLWHEREMOD'), '"snippetacao"');
	
		if(!$snippets) return $snippetAcaoHTML;
	
		$onclick = (cb::habilitarMatriz() == 'Y' ? 'montaModalEmpresa(this)' : 'abrirEmNovaGuia(this)');
	
		foreach($snippets as $snippet)
		{
			$icone = strpos($snippet['cssicone'], '/') === false ? "<i class='{$snippet['cssicone']} mb-3'></i>" : "<img src='{$snippet['cssicone']}' class='mb-3' />";
	
			$snippetAcaoHTML .= "<div class='bloco-snippet-action pointer p-2' onclick='".($snippet['modulo'] == 'evento' ? 'novaTarefa()' : $onclick)."' data-modulo='{$snippet['modulo']}' data-menu='{$mostrarMenu}'>
									<div class='text-center'>
										$icone
										<span>{$snippet['snippet']}</span>
									</div>
								</div>";
		}
	
		return $snippetAcaoHTML;
	}

	function montarMenuSuperiorAntigo()
	{
		global $_headers;

		$idempresa = cb::idempresa();
		$menuSuperiorHTML = '';

		$linkIdempresa = "&_idempresa=".$idempresa;

		//maf: 181020: não montar dentro de webviews no app
		if( $_headers["cb-canal"]  == "webview" ){
			return false;
		}

		//Cria cor de back e foreground para o avatar do usuário
		$bg = str2Color($_SESSION["SESSAO"]["NOME"]);
		$fc = colorContrastYIQ($bg);
		
		$urllogout = "?_acao=logout";

		$modsEmpresa = getModsUsr("MODULOS");
		$sqlWhereMod = getModsUsr("SQLWHEREMOD");

		$qr = "SELECT 
				m.modulo
			FROM
				objempresa o
					JOIN
				"._DBCARBON."._modulo m ON (o.idobjeto = m.idmodulo)
			WHERE
				o.objeto = 'modulo' AND o.empresa = ".cb::idempresa()."
					AND m.modulo IN (".$sqlWhereMod.")";
		$rs = d::b()->query($qr);
		$arrMod = array();
		while($rw = mysqli_fetch_assoc($rs)){
			$arrMod[] = $rw["modulo"];
		}
		foreach($modsEmpresa as $i => $item){
			if(!in_array($i, $arrMod))
				unset($modsEmpresa[$i]);
		}

		$arrMenu = formataArrayModulos($modsEmpresa);

		foreach($arrMenu as $modulo => $menu){

			$iSubMenu = count($menu["sub"]);

			if($iSubMenu>0){
				$classDrop = "dropdown";
				$link="javascript:void(0)";
			}else{
				$classDrop = "";
				$link="?_modulo=".$modulo.$linkIdempresa;
			}

			if(empty($menu["cssicone"])){
				$icone = "<span class='visible-sm visible-xs'>".strtoupper(substr($menu["rotulomenu"],0,2))."</span>";
			}else if (strpos($menu["cssicone"], "/upload/") === 0) {
				$icone = file_get_contents("{$_SERVER['REQUEST_SCHEME']}://{$_SERVER['HTTP_HOST']}{$menu["cssicone"]}");
			}else{
				$icone = "<i class='".$menu["cssicone"]."'></i>";
			}					
			
			$menuSuperiorHTML .= "<li cbmodulo='$modulo' class='$classDrop'>
			<a href='$link'>
				$icone
				<span class='cbMenuSuperiorTitle'>
					{$menu['rotulomenu']}
				</span>
			</a>";

			if($iSubMenu>0)
			{
				$menuSuperiorHTML .= "<ul class='dropdown-menu' style='max-height: 550px;overflow-y: scroll;' role='menu'>";	

				foreach ($menu['sub'] as $submenu => $item) 
				{
					$bordaInferior = $item['divisor'] == 'Y' ? 'border-b-1 border-gray-50' : '';

					$menuSuperiorHTML .=	"<li cbmodulo='$submenu' class='$bordaInferior'>
											<a href='?_modulo={$item['modulo']}$linkIdempresa'>{$item['rotulomenu']}</a>
										</li>";
				}

				$menuSuperiorHTML .= "</ul>";
			}

		}

		$snippets['dropdownUser1']=[
			'id'=>'dropdownUser1',
			'class'=>'dropdown-toggle',
			'title'=>"{$_SESSION["SESSAO"]["NOME"]} LP: {$_SESSION['SESSAO']['IDLP']}",
			'attr' => [
				'data-toggle'=>'dropdown', 
				'aria-expanded'=>'false',
				'role'=>'button',
				'cbidpessoa'=>"{$_SESSION["SESSAO"]["IDPESSOA"]}",
			],
			'style'=>"color:{$fc};",
			'icone'=>'caret',
			'texto'=> explode(' ',$_SESSION["SESSAO"]["NOME"])[0]
		];

		if ($_SESSION['SESSAO']['IDTIPOPESSOA'] == 1)
		{
				$menuSuperiorHTML .=	"
				<li><a href='javascript:janelamodal(\"report/relfuncionario.php\");'><i class='fa fa-info-circle vermelho'></i>&nbsp;Minhas Informações</a></li>
				";
				$snippets['dropdownUser1']['menu'][] = [
					'title'	=> '',
					'target'=> '',
					'href'	=> '',
					'class'	=> 'divider',
					'icone'	=> '',
					'texto'	=> ''				
				];

				$snippets['dropdownUser1']['menu'][] = [
					'title'	=> '',
					'target'=> '',
					'href'	=> '?_modulo=eventoponto$linkIdempresa',
					'class'	=> 'dropdown-item',
					'icone'	=> 'fa fa-clock-o vermelho',
					'texto'	=> '&nbsp;Ponto'				
				];
				$snippets['dropdownUser1']['menu'][] = [
					'title'	=> '',
					'target'=> '',
					'href'	=> '',
					'class'	=> 'divider',
					'icone'	=> '',
					'texto'	=> ''				
				];
		
				$snippets['dropdownUser1']['menu'][] = [
					'id'	=> 'ramais',
					'title'	=> '',
					'target'=> '',
					'href'	=> '#',
					'class'	=> 'dropdown-item',
					'icone'	=> 'fa fa-phone-square vermelho',
					'texto'	=> '&nbsp;Ramais',
					'script' => `
						<script>
							$('#ramais').click(function(){
								CB.modal({
									url:'?_modulo=ramalcolaboradores',
									header:'Ramais',
									menu: false
								});
							});
						</script>`
				];
		
				
			}

		if($_SESSION['SESSAO']['IDTIPOPESSOA'] == 15 or $_SESSION['SESSAO']['IDTIPOPESSOA'] == 1)
		{
			$sql='SELECT p.idpessoa 
					from pessoacontato c
						join pessoa p on(p.idpessoa=c.idpessoa and p.idtipopessoa in (5,12)) 
				where c.idcontato = '.$_SESSION['SESSAO']['IDPESSOA'];
				
			$res=d::b()->query($sql);
			$qtd=mysqli_num_rows($res);
										
			$row=mysqli_fetch_assoc($res);
				
			if(!empty($row['idpessoa']) and $qtd > 0)
			{
				$menuSuperiorHTML .= "
				<li class='divider'></li>
				<li><a href='?_modulo=comprasrhrestrito$linkIdempresa'><i class='fa fa-user-plus vermelho'></i>&nbsp;RH Restrito</a></li>";
			}						
		}

		if($_SESSION['SESSAO']['IDTIPOPESSOA'] == 1 or $_SESSION['SESSAO']['IDTIPOPESSOA'] == 15 or $_SESSION['SESSAO']['IDTIPOPESSOA'] == 16)
		{
			$menuSuperiorHTML .= "
				<li class='divider'></li>
				<li title='Webmail'>
					<a target='_blank' href='form/webmail.php'>
						<i class='fa fa-envelope vermelho'></i>&nbsp;Webmail</a>
					</a>	
				</li>				 
				<li class='divider'></li>";
		} 

		if ($_SESSION['SESSAO']['IDTIPOPESSOA'] == 1 and array_key_exists('organograma', $modsEmpresa))
		{
			$menuSuperiorHTML .= 
			"<li title='Organograma'>
				<a target='_blank' href='report/organograma.php?_idempresa=$idempresa'>
					<i class='fa fa-sitemap vermelho'></i>&nbsp;Organograma</a>
				</a>
			</li>
			<li class='divider'></li>";
		}

		if($_SESSION['SESSAO']['SUPERUSUARIO'] != true)
		{
			$menuSuperiorHTML .= "<li><a href='?_modulo=alterasenha'><i class='fa fa-key vermelho'></i>&nbsp;Alterar Senha</a></li>";
		}

		if(verificaSuperUsuario())
		{
			$snippets['dropdownUser1']['menu'][] = [
					'title'	=> '',
					'target'=> '',
					'href'	=> '',
					'class'	=> 'divider',
					'icone'	=> '',
					'texto'	=> ''				
				];
				
			$snippets['dropdownUser1']['menu'][] = [
				'title'	=>'Super usuário: utilizar como outro usuário',
				'href'	=>'javascript:alterarUsuario()',
				'class'	=>'dropdown-item',
				'icone'	=> 'fa fa-support vermelho',
				'texto'	=> '&nbsp;Alternar usuário'				
			];
		}
		
		/* Logout */
		$snippets['dropdownUser1']['menu'][] = [
			'title'	=>'',
			'href'	=>"#",
			'onclick'=>"javascript:localStorage.removeItem('jwt');Cookies.remove('jwt');Cookies.remove('PHPSESSID');window.location.href='{$urllogout}';",
			'class'	=>'dropdown-item',
			'icone'	=> 'fa fa-power-off vermelho',
			'texto'	=> '&nbsp;Logout'
		];
		/* Sobre o Sistema */
		$snippets['dropdownUser1']['menu'][] = [
			'title'	=>'Sobre o Sistema',
			'href'	=>"javascript:sobreOSistema();",
			'class'	=>'dropdown-item',
			'icone'	=> 'fa fa-question-circle-o azulclaro',
			'texto'	=> '&nbsp;Sobre o sistema'			
		];

		/* $menu['script'] .= "
		<script>
			$('#ramais').click(function(){
				CB.modal({
					url:'?_modulo=ramalcolaboradores',
					header:'Ramais',
					menu: false
				});
			});
		</script>"; */

		if($_SESSION["SESSAO"]["PERMISSAOCHAT"]=="Y" and $_SESSION["SESSAO"]["SUPERUSUARIO"] != true)
		{
			$menuSuperiorHTML .=	"<div id='cbNotificacoes' class='dropdown pull-right snippet' onclick='chat.abrirContainerChat();chat.montarContatos();chat.recuperarAvatar();chat.maximizar();'>
				<a id='cbBadge' href='#' class='fa fa-comment hide' data-toggle='dropdown' role='button' aria-expanded='false'>
					<span id='cbIBadge' class='badge fundovermelho' style='display: none;'></span>
				</a>
				<ul id='cbListaNotificacoes' class='Xdropdown-menu' role='Xmenu' style='padding: 0px;border: 0px;display: none;'>
					<li id='cbListaNotificacoesHeader' style='padding: 4px 10px;'>
						<table style='width: 100%;'>
							<tbody><tr><td style='color: silver;font-weight: bold;'>Notificações:</td>
								<td></td><td><span class='azul pointer' onclick='chat.marcarLida('*')'>Marcar todas como lidas</span></td>
								<td><a href='#'><i class='fa fa-cog azul pointer'></i></a></td>
							</tr>
						</tbody></table>
					</li>
					<li id='cbListaNotificacoesFooter' style='padding: 4px 10px;'>
						<table style='width: 100%;'>
						<tbody>
						<tr>
							<td></td>
							<td >
								<a href='javascript:chat.abrirContainerChat();chat.maximizar();chat.montarContatos();chat.recuperarAvatar();' class='pointer floatright'>Abrir janela de Chat</a>
							</td>
						</tr>
						</tbody>
						</table>
					</li>
				</ul>
			</div>";
		}

		foreach (getSnippets()['padrao'] as $moduloPai => $s) 
		{
			$onclick="";
			if($s["tipo"]=="PHP"){
				if(strlen(trim($s["msgconfirm"]))>0){
					$onclick="if(confirm('".$s["msgconfirm"]."'))CB.snippet('{$s['idsnippet']}');";
				}else{
					$onclick="CB.snippet('{$s['idsnippet']}');";
				}
			}elseif($s["tipo"]=="LINK"){
				$onclick="$(CB.oModuloHeader, CB.oModuloHeaderBg).addClass('hidden');CB.loadUrl({urldestino: '".$s["code"]."'});";
			}elseif($s["tipo"]=="JS" or $s["tipo"]=="MOD"){
				$fname="_".md5(uniqid());
				$onclick=$fname."()";

				$menuSuperiorHTML .= 
				"<script>
					function $fname() {
						{$s['code']}
					}
					//# sourceURL=snippet_{$s['idsnippet']}
				</script>";
			}
			
			if (strpos($s["cssicone"], "/upload/") === 0) {
				$iconeSnippet = "<a onclick='javascript:{$onclick}'>";
				$iconeSnippet .= file_get_contents("{$_SERVER['REQUEST_SCHEME']}://{$_SERVER['HTTP_HOST']}{$s["cssicone"]}");
				$iconeSnippet .= "</a>";

			}else{
				$iconeSnippet = "<a href='javascript:".$onclick."' class='".$s["cssicone"]." snippet'></a>";
			}

			$menuSuperiorHTML .= "
			<li class='dropdown pull-right snippet' id='cbSnippet{$s['idsnippet']}' title='{$s['snippet']}' cbmodulo='{$s['modulo']}'>
				$iconeSnippet
				<span onclick='javascript:{$onclick}' id='cbIBadgeSnippet{$s['idsnippet']}' modulo='{$s['modulo']}' class='badge fundovermelho' style='' ibadge=''></span>
			</li>";
		}

		return $snippets;
	}

	public function run()
	{
		$jwt = validaTokenReduzido();

		if ($jwt["sucesso"] !== true) {
			header("HTTP/1.1 401 Unauthorized");
			echo json_encode($jwt);
			die;
		}
		
		$this->menu['superior'] = [];
		$this->menu['superior']['alterar_empresa'] = $this->modalAlterarEmpresaMenuSuperior();
		
		if ($_SESSION['SESSAO']['IDTIPOPESSOA'] == 1) {
			try {
				$this->menu['superior']['snippets'] = $this->montarSnippets();
				/*$menu['lateral'] = $this->montaModulosMenuLateral();*/
			} catch (Exception $e) {
				echo json_encode([
					'error' => $e->getMessage()
				]);
			}
		} else {
			if (logado()) {
				$this->menu['superior']['snippets'] = $this->montarMenuSuperiorAntigo();
			}
			//<i class='fa fa-chevron-down' title='Mostrar menu' id='cbMostrarMenu' onmouseover='$('body').removeClass('minimizado');'></i>";
		}

		echo json_encode($this->menu);
	}
}

$menu = new MontaMenu();
$menu->run();
