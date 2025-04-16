<?
ob_start();

if (defined('STDIN')){
	require_once("/var/www/carbon8/inc/php/functions.php");
	require_once("/var/www/carbon8/inc/php/laudo.php");
}else{
	require_once("functions.php");
	require_once("laudo.php");
}

$jwt=validaToken();

if($jwt["sucesso"]){

	$lppag_logado=true;

}else{	
	// Exibir todos os dados REQUEST
	
	/*
	 * ALTERAÇÃO PARA ATENDER O SISTEMA DE ENVIO DE EMAILS COM TOKENS PARA AUTENTICAÇÃO E ACESSO AO SISTEMA
	 */
	$_token=$_GET["_token"]?$_GET["_token"]:$_GET["token"];

	//verifica se foi enviado o _token de autenticação
	if(!empty($_token)){		
		//desencripta o _token
		$str_token=des($_token);
		//verifica se deu certo a desencriptação
		if($str_token==false){					
			$lppag_logado=false;
			header("CB-ERROR: Falha #1 ao autenticar _token");
		}else{					

			parse_str($str_token,$arr_token);

			foreach ($arr_token as $chave => $valor) {
				$_GET[$chave]=$valor;
			}

			//Verifica se o campo data esta vazio
			if(empty($arr_token["datalimite"])){						
				$lppag_logado=false;
				header("CB-ERROR: Falha #2 ao autenticar _token");
			}else{								
				// trabalhando a primeira data
				$I= strtotime($arr_token["datalimite"]);				
				// trabalhando a segunda data
				$II= strtotime(date("Y-m-d"));					
				//se a data enviada foi menor que a data atual não pode mais acessar o sistema
				if($II > $I){
					$lppag_logado=false;
					header("CB-ERROR: Acesso expirado!");
				}else{													
					//verifica se o idpessoa esta preenchido							
					if(empty($arr_token["idpessoa"]) and empty($arr_token["idcontato"])){
						$lppag_logado=false;
						header("CB-ERROR: Falha #4 ao autenticar _token");
					}else{
						//pega os dados da secretaria
						$_token_idpessoa=empty($arr_token["idcontato"])?$arr_token["idpessoa"]:$arr_token["idcontato"];
						$sql = "Select * from pessoa where idpessoa=".$_token_idpessoa;							
						$res = mysql_query($sql) or die("Falha ao pesquisar permissoes do usuario : " . mysql_error() . "<p>SQL: $sql");							
						$qtd = mysql_num_rows($res);								
						//verfica se foi encontrada a pessoa
						if (empty($qtd)){
							$lppag_logado=false;
							header("CB-ERROR: Usuario do _token nao encontrado.");
						}else{
							if(empty($arr_token["usuario"])){
								$arr_token["usuario"]="_token_geral";
							}
							/*
							* Preenche a SESSAO com os dados da pessoa
							*/								
							$row = mysql_fetch_assoc($res);
							
							$_SESSION["SESSAO"]["EMAIL"]=$arr_token["email"];
							$_SESSION["SESSAO"]["LOGADO"] = true;
							$_SESSION["SESSAO"]["USUARIO"]= $arr_token["usuario"];
							$_SESSION["SESSAO"]["IDLP"]=$row["idlp"];
							$_SESSION["SESSAO"]["IDPESSOA"] = $_token_idpessoa;
							$_SESSION["SESSAO"]["OBRIGATORIOCONTATO"]=$row["flagobrigatoriocontato"];
							$_SESSION["SESSAO"]["IDTIPOPESSOA"] = $row["idtipopessoa"];
							$_SESSION["SESSAO"]["FULLACCESS"] = 'N';
							$_SESSION["SESSAO"]["IDEMPRESA"] = $row["idempresa"];
							if(!empty($_SESSION["SESSAO"]["IDLP"]) and $_SESSION["SESSAO"]["IDLP"]!==""){
								$_SESSION["SESSAO"]["MIGRACAO"]["LPS"] = "'".$_SESSION["SESSAO"]["IDLP"]."'";
							}else{
								$_SESSION["SESSAO"]["MIGRACAO"]["LPS"] = arrLpSetoresPorIdPessoa($_SESSION["SESSAO"]["IDPESSOA"], true);
							}
							/*
							* Modulos disponiveis para o usuario
							* O array resultante será utilizado no POST para restricao de escrita nas tabelas
							*/
							$arrModulosUsuario = retArrayModulosUsuario();
							$arrModulosPorIdPessoa = arrayModulosPorIdPessoa($_SESSION["SESSAO"]["IDPESSOA"]);

							if(sizeof($arrModulosUsuario)==0){
								die("Nenhum Módulo atribuído à Lista de Permissões [".$row["idlp"]."].\nEntre em contato com o administrador.");
							}

							$_SESSION["SESSAO"]["SQLWHEREMOD"] = $arrModulosUsuario["SQLWHEREMOD"];
							$_SESSION["SESSAO"]["MODULOS"] = $arrModulosUsuario["MODULOS"];
							$_SESSION["SESSAO"]["MIGRACAO"]["SQLWHEREMOD"] = $arrModulosPorIdPessoa["SQLWHEREMOD"];
							$_SESSION["SESSAO"]["MIGRACAO"]["MODULOS"] = $arrModulosPorIdPessoa["MODULOS"];


						}
					}
				}									
			}													
		}
	}
}

if (!empty($_GET["_modulo"])) {
	$pos__validatoken = "../eventcode/modulo/pos__validatoken_".$_GET["_modulo"].".php";
	if (file_exists($pos__validatoken)) {
		include_once($pos__validatoken);
	}
}

//Se nao houver sessao de autenticacao, interrompe
if((!$_SESSION["SESSAO"]["LOGADO"] or empty($_SESSION["SESSAO"]["USUARIO"])) and $_GET["_modulo"]!="_login"){
	header("HTTP/1.1 401 Não Autorizado");
	die();
}else{

	/*
	 * maf201113: SEGURANCA: verificar se o usuario possui acesso ao modulo
	 */
	//Inicializa normalmente
	//Libera o módulo de login
	if( $_GET["_modulo"] == "_login" ){
		$sqlWhereMod = "'_login'";
	}else{
		$sqlWhereMod = getAllModsUsr() ?? "'_login'";
		$_GET["_modulo"] = ($sqlWhereMod == "'_login'") ? "_login" : $_GET["_modulo"];
	}
	
	if(empty($_GET["_modulo"])){
		$_GET["_modulo"] = modInicial();
	}

	$modEnviado = true;
	//Inicializa o modulo selecionado
	$_sqlinitmod = "SELECT * 
		FROM "._DBCARBON."._modulo
		WHERE modulo = '".$_GET["_modulo"]."'
			and modulo in (".$sqlWhereMod.")";
	
	$resmod = mysql_query($_sqlinitmod) or die(__METHOD__." Erro ao recuperar Módulo: ".mysql_error());
	$iMod = mysql_num_rows($resmod); 	
	
	if( $iMod > 1 ){
		erroacesso("img/lock16.png"
			,"Mais de um módulo foi encontrado para a HOME.<br>Módulo: [".$_GET["_modulo"]."]<BR>Entre em contato com o Administrador."
			,true
			,"Erro");
		die;
	}elseif( $iMod < 1 ){
		cbSetPostHeader("0","erro");
		$_sqlRotulo = "SELECT rotulomenu FROM "._DBCARBON."._modulo WHERE modulo = '".$_GET["_modulo"]."'";	
		$resRotulo = mysql_query($_sqlRotulo) or die(__METHOD__." Erro ao recuperar Módulo: ".mysql_error());
		$rowRotulo = mysqli_fetch_assoc($resRotulo);
		$rotulo = empty($rowRotulo['rotulomenu']) ? $_GET["_modulo"] : $rowRotulo['rotulomenu'];
		
		die("Sem permissão para visualizar o Módulo [ ".$rotulo." ]. Entre em contato com o Administrador.");
	}else{
		$rowmod = mysql_fetch_assoc($resmod);
		$_modulo = $rowmod["modulo"];
		$_modulo_tipo = $rowmod["tipo"];
		$_modulo_ready = $rowmod["ready"];;
		$_modulo_urldestino = $rowmod["urldestino"];
		$_modulo_urlprint = $rowmod["urlprint"];
	}

	/*
	 * log do acesso
	 */
	if($_SESSION["SESSAO"]["LOGADO"]){
		pessoaLog('A');
		cb::idempresa();

		//Executar inicializacao controlada por dominio. Isto permite interromper o processamento neste ponto
		//@todo: realizar vberificacao em outra fonte, para reduzir IO no disco
		$arq_init = _CARBON_ROOT."eventcode/dominio/".str_replace(".","_",$_SERVER["HTTP_HOST"])."__init.php";
		if(file_exists($arq_init)) {
			include_once($arq_init);
		}

		//Verifica prompt de validacao de senha
		prompt::get('forca_troca_senha')::executa();

		//Verifica se o funcionario possui configuracao para lancamento de ponto tipo REP-P
		prompt::get('ponto_tipo_p')::executa();
	}
}

$bloqueioScript = '';
$bloqueio = false;

if ($_GET['_acao'] == 'u'){
	/* Adicionar em todos os modulos*/
	/*Implanta o Modulo e sua validação */
	$bloqueio = geraBloqueioTela();

	$bloqueioScript = "<script>
		var eventoBloqueio = JSON.parse('".json_encode($bloqueio)."')
	</script>";

}
if ($lppag_logado){
	$chave = "_sessao:modulos:".$_GET["_modulo"].":".$_SESSION["SESSAO"]["IDPESSOA"];
	$ttl = 600;
	re::dis()->set($chave, '', ['ex' => $ttl]);
	$chave = "_sessao:usuarios:".$_SESSION["SESSAO"]["IDPESSOA"];
	re::dis()->set($chave, '', ['ex' => $ttl]);
}


?>
