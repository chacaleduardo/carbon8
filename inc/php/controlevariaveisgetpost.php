<?
	//Se for o caso de chamada direta de formulário, considerar Update pra todos os casos
	if(getModsUsr("MODULOS")[$_GET["_modulo"]]["ready"]=="URL"){
		$_acao = "u";
	}

	//Se nao for resultado de POST:
	if(empty($_acao)){
		$_acao = $_GET["_acao"];
	}

	if (!isset($_acao)){
		die("O parâmetro [_acao] deve sempre vir informado no GET ou não foi setado apos ser processado pelo cbpost.<br>Possibilidade: ajuste as variáveis da função CB.loadUrl() no javascript");
		die;
	}

	if($_acao == 'i'){

		null;
	}elseif(($_acao == 'u')or($_acao == 'd')){
		reset($pagvalcampos);
		while (list($key, $val) = each($pagvalcampos)){//Valida os parametros GET informados para serem validados
			switch ($val) {
				case "pk":
					$_pkid = empty($_GET[$key])?$_pkid:$_GET[$key];
					if(empty($_pkid)){
						mostraerro("Erro1","(1)Par&acirc;metro [".$key."] n&atilde;o informado corretamente!");
						die;
					}
				case "vnulo":
					if(isset($_GET[$key]) and empty($_GET[$key])){
						mostraerro("Erro2","(1)Par&acirc;metro [".$key."] n&atilde;o esta vazio!");
						die;
					}
			}
			$$key = $_GET[$key];
		}

		if(empty($pagvaltabela)){
			mostraerro("Erro3","A tabela padr&atilde;o [pagvaltabela] n&atilde;o foi informada corretamente");
			die;			
		}
		
		//echo $pagsql;
		$pagsql = str_replace("#pkid", $_pkid, $pagsql);
		//echo($pagsql);
		$res = mysql_query($pagsql) or die("A Consulta inicial falhou : " . mysql_error() . "<p>SQL: ".$pagsql."<br>Ajuste o texto da consulta inicial da pagina: ".__FILE__);
		
		$ires = mysql_num_rows($res);
		
		if($ires == 0){
			die("Erro4 O Select inicial n&atilde;o retornou resultados: [".$pagsql."]");
			die;
		}elseif($ires > 1){
			die("Erro5 O Select inicial retornou mais de uma linha de resultado: [".$pagsql."]");
			die;
		}
		
		$row = mysql_fetch_assoc($res);

		/*
		 * Cria automaticamente as variaveis conforme os campos que retornarem do banco
		 */
		$_arrtabdef = retarraytabdef($pagvaltabela);
		
		$_prefix = isset($pagprefixo) ? $pagprefixo : "_1_u_";

		if(empty($_ignoraEmpresaControleVariavelGetPost)){
			$_ignoraEmpresaControleVariavelGetPost = false;
		}

		while(list($campo,$valor)=each($row)){
			if($campo == "idempresa" && $_ignoraEmpresaControleVariavelGetPost === false){
				getEmpresaPessoa();
				if(!empty($_SESSION["SESSAO"]["STRIDEMPRESA"][$valor]) or !empty($_GET["_token"]) or !empty($_GET["token"]) or $_SESSION["SESSAO"]["IDEMPRESA"] == $valor){
					if ($valor==$_GET['_idempresa']) {
						echo "<script>
								var gIdEmpresaModulo = '".$_GET['_idempresa']."';
								gIdEmpresa = ".cb::idempresa().";
							</script>";
					}else{//@487013 - MULTI EMPRESA
						$_GET['_idempresa'] = $valor;
						echo "<script>
								var _location = (getUrlParameter('_idempresa') !== '') ? removerParametroGet('_idempresa') : location.href;
								window.history.pushState(null, window.document.title, _location+'&_idempresa='+".$valor.");
								_location = null;
								var gIdEmpresaModulo = ".$valor."
								gIdEmpresa = ".cb::idempresa().";
							</script>";
					}
				}else{
					die("Erro6 Você não possui permissão para visualizar dados da empresa ".$valor);
					die;
				}
			}

			$fldvar = $_prefix.$pagvaltabela."_".$campo;

			$_fldtype = $_arrtabdef[$campo]["type"];
			
			//Reconstruir a representação da coluna double conforme coluna associada "_exp" (registro do expoente)
			if($_fldtype=="double"){
				if(array_key_exists($campo."_exp", $_arrtabdef) and !empty($row[$campo."_exp"])){
					//$arrExp=explode('e',$row[$campo."_exp"]);
					//$valor=recuperaExpoente($row[$campo],$row[$campo."_exp"]);
					$valor=$valor;
				}
			}
			
			/*
			 * executa pre tratamento para formatacao de informacoes
			 */
			$$fldvar = formatastringvisualizacao($valor,$_fldtype);
		}

	}else{
		echo("\n<!-- Insert -->\n");//die("Insert");
	}

?>
