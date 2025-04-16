<?
$debugSql=false;
if($debugSql){
	openlog("CBPOST", 0, LOG_LOCAL0);
}

ob_start();

//Caso seja feito acesso direto ao arquivo de post, negar requisicao
if (__FILE__ == $_SERVER['SCRIPT_FILENAME']) {
	header("HTTP/1.0 404 Not Found");
	die();
}

//Inicializa resposta para a requisiÃ§Ã£o ajax/jQuery
cbSetPostHeader("0","alert");

$_clientheaders = getallheaders();

/*
 * Caso o login tenha expirado, armazenar os dados em sessÃ£o ANTES da validacao de acesso
 * Em casos em que o usuÃ¡rio permaneceu com a tela aberta muito tempo, ao expirar a sessao, ocorre o seguinte fluxo:
 * Armazenamento dos dados de POST 
 * 1 - Validacao de acesso normal (erro)
 * 2 - Encaminhamento normal para a tela de Login (com aviso de dados pendentes POSTRECOVER) 
 * 3 - ApÃ³s processamento do login, reencaminhamento para a PÃ¡gina de Submit 
 * 4 - ValidaÃ§Ã£o normal novamente
 * 5 - Troca de variÃ¡veis de POSTRECOVER para $_POST
 * 6 - Limpeza da Variavel de POSTRECOVER 
 */
if((!$_SESSION["SESSAO"]["LOGADO"] or empty($_SESSION["SESSAO"]["USUARIO"])) and !empty($_POST)){
	//recupera o tempo configurado para session
	$_sexp = ini_get("session.gc_maxlifetime") / 60;
	die("Seu tempo de login login expirou: ".$_sexp." minutos");
	unset($_SESSION["POSTRECOVER"]);
	$_SESSION["POSTRECOVER"] = $_POST;
	$_SESSION["POSTRECOVER"]["RETORNOSUBMIT"] = $_SERVER["PHP_SELF"];//armazena o endereco relativo da pagina de submit, para passar para a url de retorno
	$_SESSION["POSTRECOVER"]["HTTP_REFERER"] = $_SERVER["HTTP_REFERER"];//armazena a pagina de origem do POST
}

//print_r($_SESSION);die;

/*
 * maf191103: SEGURANCA: verifica se o modulo esta marcado como 'A'lteração ou 'I'nserção na LP relacionada ao usuário
*/
if(empty($_GET["_modulo"])){
	die("cbpost Erro: Parâmetro GET[_modulo] não informado");
}else{
	
	$rotulo = empty($rowRotulo['rotulomenu']) ? $_GET["_modulo"] : $rowRotulo['rotulomenu'];
	if($_SESSION["SESSAO"]["FULLACCESS"]!="Y" and (getModsUsr("MODULOS")[$_GET["_modulo"]]["permissao"]!='i' and getModsUsr("MODULOS")[$_GET["_modulo"]]["permissao"]!='w')){

		$_sqlRotulo = "SELECT rotulomenu FROM "._DBCARBON."._modulo WHERE modulo = '".$_GET["_modulo"]."'";	
		$resRotulo = mysql_query($_sqlRotulo) or die(__METHOD__." Erro ao recuperar Módulo: ".mysql_error());
		$rowRotulo = mysqli_fetch_assoc($resRotulo);
		$rotulo = empty($rowRotulo['rotulomenu']) ? $_GET["_modulo"] : $rowRotulo['rotulomenu'];

		die("Sem permissão de escrita ao Módulo [ ".$rotulo." ].\nVerificar configurações da LP [".getModsUsr("LPS")."].\nPermissão atual: [".getModsUsr("MODULOS")[$_GET["_modulo"]]["permissao"]."]");
	}
}

if($_SESSION["SESSAO"]["USUARIO"]=="marcelo"){

}

/*
 * Recupera dados de POST armazenados antes do Login
 */
//print_r($_SESSION["POSTRECOVER"]);die("teste1");
if(!empty($_SESSION["POSTRECOVER"]) and empty($_POST)){

	//Troca o conteÃºdo das variaveis
	$_SESSION["post"] = $_SESSION["POSTRECOVER"];
	$_SERVER["HTTP_REFERER"] = $_SESSION["POSTRECOVER"]["HTTP_REFERER"];
	
	unset($_SESSION["POSTRECOVER"]);
	unset($_SESSION["POSTRECOVER"]["HTTP_REFERER"]);

	$_POST = $_SESSION["post"];
	
}else{
	//Atribuicao normal de valores postados
	$_SESSION["post"] = $_POST;
}


$_SESSION["headergetretorno"]=false;

//print_r($_POST);die;

//variavel que armazenara o parametro que sera concatenado em GET para a linha 1 do script
//$_SESSION["vargetpk"] = "";

//maf060820: facilitar o controle das acoes enviadas via post i||u||d
$_acoes=array();

//maf310720: Declaracao das variaveis compartilhadas globalmente
$_arrCQRSCarbon=array(); //Compartilhar todos os comandos que utilizam o carbon com outras plataformas
$_arrRamCache=array();
$arrRamCache=array();
$_rstr="";

function montapostbuffer(){
	global $_acoes;

	//echo "<br>--montapostbuffer()<BR>";

	$arrpilha = $_SESSION["post"];
	$itab  = 0;
	$iaca  = 0;
	$iacatab  = 0; #Conta se foi informada mais de 1 tabela por linha
	$arracoes = array("i", "u", "d", "r"); #Ações permitidas para a pÃ¡gina

	//maf060820: reset no arraypostbuffer na session
	unset($_SESSION["arrpostbuffer"]);
	
	//Alterado junto com o Amorim para que fique ordenado mantendo a associação entre índices e valores, o que não estava ocorrendo no array_multisort (LTM-27-08-2020 - 369403) 
	ksort($arrpilha);
	reset($arrpilha);

	//Constroi arrays a partir do caractere chave do carbon nos input names
	while (list($chave, $vlr) = each($arrpilha)) {

		//echo "Monta postbuffer".memory_get_peak_usage()/1024/1024 . "<br>";
		
		//Se existirem 3 caracteres chave, o input ira compor o buffer
		$r = explodeInputNameCarbon($chave);
		/** $r = array(
		[0] => 1
		[1] => u
		[2] => nometabela
		[3] => nomecampo
		)*/

		if(count($r)==4){

			if(	(!is_numeric($r[1])) &&
				(strlen($r[1]) == 1) &&
				(in_array($r[1],$arracoes))	){ # AÃ§Ã£o para tabela

				//if(!isset($_SESSION["acao"])){	// Recupera o Get da acaO

					//$_SESSION["acao"]=$r[2];

				//}
				if(strlen($r[2]) > 0){ #Se o nome da tabela veio preenchido					

					if(strlen($r[3]) > 0){ #Se o nome do campo veio preenchido

						/*
						 *MAF221110 alteracao: os campos automaticos foram alterados para a funcao validapostbuffer, para serem executados mesmo que nao forem enviados por POST.
						 *Isto permite que, mesmo que eles nao estejam na pagina, o valor automatico seja atribuido
							//$vlr = colauto($r[2],$r[4],$vlr);
						 */

						$_SESSION["post"][$ckey.$r[0].$ckey.$r[1].$ckey.$r[2].$ckey.$r[3]] = trim($vlr);//Devolve o novo valor da coluna automÃ¡tica para o Post, para depois ser devolvido Ã  pÃ¡gina

						//Montagem da Estrutura do BUFFER para o Carbon:
						$arrpostbuffer[$r[0]]
										[$r[1]]
											[$r[2]]
												[$r[3]] = trim($vlr);
												

					}
					$itab ++;
					//maf060820: melhorar a verificacao de acoes sendo executadas na montagem do buffer
					$_acoes[$r[2]][$r[1]]=$itab;
				}
				$iacatab = count($arrpostbuffer[$r[0]][$r[1]]);
				$iaca ++;
				
				if($iacatab > 1){
					
					$tabEnviadas=array();
					foreach ($arrpostbuffer[$r[0]][$r[1]] as $k=>$v) {
						$tabEnviadas[]=$k;
					}
					$tabEnviadas= implode(", ", $tabEnviadas);
					
					die("Mais de 1 tabela foi enviada por POST na Linha [".$r[0]."] do Buffer.\n\nPossibilidade: caso seja uma requisição Ajax, troque o Nº da linha enviada por texto. Ex: _ajax_u_tab_col\n\nTabelas enviadas: " . $tabEnviadas);
					null;
				}
			}
		}else{//211116: ignorar nomeclaturas inválidas
			 null;
		}
		 /* }
		 * elseif(count($r)>1){
			die("O nome do input enviado está incorreto:\n[".($chave)."]\nPara corrigir ajuste os caracteres chave para montagem do buffer.\ncount(r):".count($r)."\nr[1]:".$r[1]);
		}else{
			die("Input name inválido:\n[".($chave)."]\ncount(r):".count($r)."\nr[1]:".$r[1]);
		}*/
	}//while


	//print_r($_POST);
	//print_r($_SESSION["post"]);
	//echo "teste";
	//print_r($arrpostbuffer);die;
	//die();
	//print_r($_GET["acao"]); die();
	if ($iaca == 0){
		die("Nenhuma Ação (name='#acao' value='ins') foi enviada em Submit;");
	}

	if ($itab == 0){
		die("Nenhuma Tabela (name='#tabela[campo]') foi enviada em Submit;");
	}

	//existem tabelas no buffer
	if (($itab >= 1) && ($iaca >= 1)){
		
		$_SESSION["arrpostbuffer"] = $arrpostbuffer;
		//print_r($arrpostbuffer);//die;
		return true;
	}else{
		return false;
	}
}

function montatabdef(){
	global $_acoes;
	//echo "\n--montatabdef()";

	/*
	 * Passar por cada Tabela que veio no buffer, para buscar suas definicoes
	 */
	reset($_SESSION["arrpostbuffer"]);
	//print_r($_SESSION["arrpostbuffer"]);die;
	while (list($row, $rowarr) = each($_SESSION["arrpostbuffer"])) {#Row Number

		//echo "Montatabdef".memory_get_peak_usage()/1024/1024 . "<br>";
		while (list($act, $actarr) = each($_SESSION["arrpostbuffer"][$row])) {#Action

			while (list($tbl, $tblarr) = each($_SESSION["arrpostbuffer"][$row][$act])) {#Table

					$filePart = str_replace(_CARBON_ROOT, "", $_SERVER["SCRIPT_FILENAME"]);

					$strlps=" AND lm.idlp in(".getModsUsr("LPS").") ";

					$sqlobjf = "SELECT 1
                                                            FROM "._DBCARBON."._formobjetos o, "._DBCARBON."._lpmodulo lm
                                                            WHERE o.modulo = '".$_GET["_modulo"]."'
                                                                AND '".$filePart."' like concat('%',o.form,'%')
                                                                AND o.tipoobjeto in ('tabela','tabelacbpost')
                                                                AND o.objeto = '".$tbl."'
                                                                AND lm.modulo = o.modulo
                                                                ".$strlps."
                                                                AND (CASE lm.permissao
                                                                                WHEN 'r' THEN 0
                                                                                WHEN 'i' THEN 1
                                                                                WHEN 'w' THEN 2
                                                                        END) >= (case '".$act."'
                                                                                    WHEN 'i' THEN 1
                                                                                    WHEN 'u' THEN 2
                                                                                    WHEN 'd' THEN 2
                                                                                END) 
                                                    UNION 
                                                    SELECT 1
                                                                FROM "._DBCARBON."._modulo m
                                                                WHERE m.tipo IN ('BTPR') 
                                                                AND m.modulo = '".$_GET["_modulo"]."'                                              

                                                        ";
					//die($sqlobjf);
					$resobjf = mysql_query($sqlobjf) or die("Erro ao pesquisar objetos de formulario:".mysql_error()."\nSQL: ".$sqlobjf);
					
					if(!bypass($tbl) and mysql_num_rows($resobjf)<1){
						$acatmp="";
						switch ($act) {
							case "i":
								$acatmp="Inserção";
								break;
							case "u":
								$acatmp="Escrita/Alteração";
								break;
							case "d":
								$acatmp="Escrita/Deleção";
								break;
							default:
								$acatmp="[".$act."]";
								break;
						}
						
						cbSetPostHeader("0","erro");
						//die($sqlobjf);
						
						die("LP sem permissão de [".$acatmp."] à tabela [".$tbl."].\nVerificar configurações da LP [".getModsUsr("LPS")."]\ne/ou do Módulo [".$_GET["_modulo"]."]\npara o script [".$filePart."].<br><a href='javascript:janelamodal(\"?_modulo=_modulo&_acao=u&idmodulo=".getModReal($_GET["_modulo"])."\")'>Ajustar</a>");
					}
				//}

				/*
				 * Buscar DefiniÃ§Ã£o da Tabela no DB
				 */
				$arrtmp[$tbl] = retarraytabdef($tbl);

				/*
				 * Executa codigos de COLUNAS AUTOMATICAS
				 * Este codigo estÃ¡ sendo executado neste momento porque neste ponto as tabelas que vieram do POST jÃ¡ estao separadas
				 */
				$arrcolauto	= retarrcolauto();

				//print_r($arrtmp); print_r($arrcolauto);die;

				while (list($col, $val) = each($arrcolauto)) {

					//$arrcolauto[$row["col"]][$row["act"]] = $row["code"];
					
					$VALOR_COLUNA_AUTOMATICA = false;//Esta variavel sera preenchida pelo codigo executado no arquivo
					if(array_key_exists($col,$arrtmp[$tbl])){//se a acoluna automatica existir na tabela em questao

						//echo "\ntem".$arrcolauto[$col][$act];
						if(!empty($arrcolauto[$col][$act])){

							//echo "\n".$col ."-".$act."-".$vlr;

							//Executa o codigo armazenado
							$codcolauto = _CARBON_ROOT."eventcode/colauto/col__".$act."__".$col;
							//echo "\nExecutar: ".$codcolauto;
							include($codcolauto);//DEVE ser include, porque o include_once inclui o codigo para execucao somente uma vez

							if($VALOR_COLUNA_AUTOMATICA !== false){//se a coluna foi REALMENTE alterada pelo codigo
								$_SESSION["arrpostbuffer"][$row][$act][$tbl][$col] = $VALOR_COLUNA_AUTOMATICA;
							}

						}
					}
					$VALOR_COLUNA_AUTOMATICA = null;
				}

				//maf040320: Considerar colunas preenchidas automaticamente por variaveis globais pre-definidas
				while (list($col, $conf) = each($arrtmp[$tbl])) {
					if($conf["prompt"]=="var"){
						if($conf["code"]=="idpessoa"){
							$_SESSION["arrpostbuffer"][$row][$act][$tbl][$col]=$_SESSION["SESSAO"]["IDPESSOA"];
						}
					}
				}

			}
		}
	}

	//print_r($_SESSION["arrpostbuffer"]);
	//print_r(cb::$session["arrtabledef"]);die;
	return true;

}
function validapostbuffer(){

	global $_clientheaders, $_acoes;

	//echo "\n--validapostbuffer()";
	/*
	 * maf271211: Inicia transaÃ§Ã£o ANTES de execucao de qualquer codigo de evento PRE ou POS
	 */
	$res = mysql_query("START TRANSACTION");
	if(!$res){
		echo "\n<br>Erro ao iniciar Transação";
		$_SESSION["arrscriptsql"]["erro"]=true;
		return false;
	}

	//inicializa eventcodes
	//sessionArrayEventCode("modulo", getModReal($_GET["_modulo"]));
        sessionArrayEventCode("modulo", $_GET["_modulo"]);
			
	ksort($_SESSION["arrpostbuffer"], SORT_NUMERIC);
	
	/*
	 * Executa SAVEPRE CHANGE
	 * A abertura da variavel interna de arraypostbuffer deve sempre ser feita apos este ponto
	 */
	if($_SESSION["arreventcode"]["modulo"][getModReal($_GET["_modulo"])]["evento_saveprechange"]=="Y" && $_clientheaders["CB-BYPASS"]!=="Y"){

		$arq_saveprechange = _CARBON_ROOT."eventcode/modulo/saveprechange__".getModReal($_GET["_modulo"]).".php";
	
		if(file_exists($arq_saveprechange)) {
			include_once($arq_saveprechange);
		}else{
			die("Erro cbpost: Módulo configurado com evento saveprechange, mas houve falha ao abrir o arquivo:\n\n".$arq_saveprechange."\n\nAltere permissões do arquivo, ou verifique evento existente no DB.");
		}	
	}else{
		null;
		//die("saveprechange nao encontrado");
	}

	$arrpostbuffer	= $_SESSION["arrpostbuffer"];
	$arrtabledef	= cb::$session["arrtabledef"];

	/*
	 * ConstruÃ§Ã£o dos Comandos SQL
	 */
	$arrsql            = array();
	$strsql            = '';
	$strsqlcol         = '';
	$strsqlval         = '';
	$strsqlcolval      = '';
	$strsqlcolvalwhere = '';
	$icol              = 0;

	if(!is_array($arrpostbuffer)){
		die("Falha na atribuição do Array -arrpostbuffer-");
	}
	if(!is_array($arrtabledef)){
		die("Falha na atribuição do Array -arrtabledef-");
	}

	############################## Separar as Tabelas que vieram no buffer
	//todo: verificar se alguma coluna not null nao foi enviada via post
	//print_r($_SESSION["arrpostbuffer"]);
	//print_r($arrtabledef);die;
	reset($arrpostbuffer);
	reset($arrtabledef);
	
	while (list($row, $rowarr) = each($arrpostbuffer)) {#Row Number

		//echo "VAlida postbuffer".memory_get_peak_usage()/1024/1024 . "<br>";

		$arrsql[$row] = array();
		while (list($act, $actarr) = each($arrpostbuffer[$row])) {#Action
			switch ($act) {
				case "i":
					$strsql = "insert into "; break;
				case "u":
					$strsql = "update "; break;
				case "d":
					$strsql = "delete from "; break;
				default:
					die("ParÃ¢metro [act:".$act."] para Tabela [".$tbl."] invÃ¡lido!");
			}
			while (list($tbl, $tblarr) = each($arrpostbuffer[$row][$act])) {#Table

				/*
				 * maf241013: verificar campos não nulos que não foram enviados via post. somente em caso de INSERT, porque isto pode dificultar UPDATES
				 * Isto evita campos '' ou '0' na tabela que na maioria das vezes é um campo FK que o programador esqueceu de enviar
				 */
				if($act=="i"){
					//verifica a diferenca entre os KEYS dos 2 arrays
					$diffnullable = array_diff_key($arrtabledef[$tbl]["#arrnullable"],$tblarr);	
					//print_r($tblarr);print_r($arrtabledef[$tbl]["#arrnullable"]);print_r($diffnullable);die;
					if(count($diffnullable)>=1){
						echo("As colunas NOT NULL da tabela [".$tbl."] abaixo não foram enviados via POST na linha [".$row."] do buffer: ");
						foreach ($diffnullable as $k => $value) {
							echo "\n[".$k."]";
						}
						die;
					}
				}
				
				/*
				 * maf271211: Executa codigos SAVEPRECHANGE da TABELA apÃ³s a execuÃ§Ã£o dos scripts
				 */
				$tab_saveprechange = _CARBON_ROOT."eventcode/tab/".$tbl."__saveprechange";
				if (file_exists($tab_saveprechange)) {
					include_once($tab_saveprechange);
				}

				$strsql = $strsql . $tbl . " ";
				while (list($col, $vlr) = each($arrpostbuffer[$row][$act][$tbl])) {#Coluna


					//print_r($arrpostbuffer);//Em grupos de comandos muito grandes causa ERRO DE MEMORIA

					 #Coluna Existe na Tabela
					if(!array_key_exists($col,$arrtabledef[$tbl])){
						cbSetPostHeader("0","erro");
						if(empty($arrtabledef[$tbl])){
							die("A Tabela [".$tbl."] do arraypostbuffer não retornou sua estrutura na funcao montatabdef().\nPossibilidade 1: execute em [presave] a chamada para retarraytabdef('tabela').\nPossibilidade 2: A tabela não foi salva no Dicionário de Dados<br><a href='javascript:janelamodal(\"?_modulo=_mtotabcol&_acao=u&PK="._DBAPP.".".$tbl."\")'>Ajustar</a>");
						}else{
							die("A Coluna [".$col."] do Buffer [POST] não pertence à Tabela [" .$tbl."] no database ["._DBAPP."]<br><a href='javascript:janelamodal(\"?_modulo=_mtotabcol&_acao=u&PK="._DBAPP.".".$tbl."\")'>Ajustar</a>");							
						}
					}else{

						if ($icol==0){
							$strsqlcol = "(".$col;
						}else{
							$strsqlcol = $strsqlcol . "," . $col;
						}

						/*
						 * Verifica a coluna IDEMPRESA
						 */
						//if($col=="idempresa")die("teste");
						
						if($col=="idempresa" and ($vlr==0 or empty($vlr)) and $tbl!='_empresa'){
							die("Erro: Coluna idempresa com valor vazio!");
						}
						$inc  = "";
						$null = "";
						$pk   = "";
						$type = "";
						$chk  = "";

						$inc  = $arrtabledef[$tbl][$col]["autoinc"];
						$null = $arrtabledef[$tbl][$col]["null"];
						$pk   = $arrtabledef[$tbl][$col]["primkey"];
						$type = $arrtabledef[$tbl][$col]["type"];
						$chk  = $arrtabledef[$tbl][$col]["checkbox"];

						//Token: verificar se está tentando atualizar outro ID diferente do inserido anteriormente: possibilidade de quebra de segurança
						if($_SESSION["SESSAO"]["TOKEN"]==true and $act != "i" and $pk=="Y" and $vlr != $_SESSION['_pkid']){
							echo "pkvlr:".$_SESSION['_pkid']."-Vlr:".$vlr;
							die("Alteração não permitida!");
						}
						
						//print_r($arrtabledef[$tbl][$col]);
						#AUTO INCREMENTO
						switch ($inc) {
							case "Y":
								if(($act == "i") and
								   ($vlr <> 0) and
								   (strlen($vlr) <> 0) ){
								   	//echo "[$vlr]<BR>";
									die("Coluna [".$col."] AutoIncremento não pode conter valor [".$vlr."] para Insert;");
								}elseif($act == "i" and $pk == "Y" and $row == 1){
									//die("tenho que pegar o valor deste campo ".$col." para retornar Ã  tela anterior");
									//Armazenar o campo PK que esta na primeira linha para concatenar e retornar na URL de retorno
									//$_SESSION["vargetpk"] = $col;
								}
								break;
							case "N":
								if($pk == "Y" and stripos($type,"int")!==false){
									//echo "[$vlr]<BR>";
									die("Coluna [".$col."] é Chave Primária (PK), e obrigatoriamente deve ser AutoIncremento na tabela;");

								}elseif($pk=="Y" and $type=="varchar" and $null=="N" and $act == "i"){//campos primarios VARCHAR. pegar o valor para URL de retorno
									//echo("campo chave varchar: ".$col);
									//$_SESSION["vargetpk"] = $col;
								}
								break;
							default:
								die("Coluna [".$col."] com parâmetro [autoinc] da Tabela [".$tbl."] não aceito;");
						}

						//echo "<br><br>col:".$col;
						//echo "<br>act:".$act;
						//echo "<Br>inc:".$inc;
						//echo "<Br>len:".strlen($vlr);
						//echo "<Br>chk:".$chk;
						
						#ALLOW NULL
						switch ($null) {
							case "N":
								if(($act <> "d")and
								   ($inc <> "Y")and
								   (strlen($vlr) == 0)
								   ){
								   	///echo "[$act]<BR>";
								   	mysql_query("ROLLBACK");
									cbSetPostHeader("0","erro");
									if($pk=="Y"){
										die("Coluna PK não foi configurada como Auto Incremento no DB");
									}else{
										die("Coluna [".$col."] Not Null da tabela [".$tbl."] não pode conter valor vazio<br/><a href='javascript:janelamodal(\"?_modulo=_mtotabcol&_acao=u&PK="._DBAPP.".".$tbl."\")'>Ajustar</a>");
									}
								}
								break;
							case "Y":
								break;
							default:
								die("Parâmetro [null] da Tabela [".$tbl."] não aceito;");
						}
						
						//Converter strings "null" para null
						$vlr=($vlr=="null")?"":$vlr;
						
						#TIPO CAMPO
						switch ($type) {

							case 'varchar':

								if((!is_string($vlr)) and
								   (strlen($vlr) <> 0) ){
									die("Coluna [".$col."] [".$type."] da Tabela [".$tbl."] contém caracteres inválidos:".$vlr.";");
								}

								break;

							case 'enum':

								if ((!is_string($vlr)) and
								   (strlen($vlr) <> 0) ) {
									die("Coluna [".$col."] [".$type."] da Tabela [".$tbl."] cont??m caracteres inv??lidos:".$vlr.";");
								}
								
								break;

							case 'json':

								if ((!is_string($vlr)) and
								   (strlen($vlr) <> 0)) {

									die("Coluna [".$col."] [".$type."] da Tabela [".$tbl."] cont??m caracteres inv??lidos:".$vlr.";");
								
								}

								break;

							case 'bigint':
							
								$vlr = tratanumero($vlr);

								if (!is_numeric($vlr) and $vlr!="NULL") {
									die("Coluna [".$col."] [".$type."] da Tabela [".$tbl."] contém caracteres inválidos: [".$vlr."];");
								}

								break;

							case 'char':

								if((!is_string($vlr)) and
								   (strlen($vlr) <> 0) ){
									die("Coluna [".$col."] [".$type."] da Tabela [".$tbl."] contém caracteres inválidos: [".$vlr."];");
								}

								break;

							case 'datetime':

								if(!validadatetime($vlr)and!empty($vlr)){
									die("Coluna datetime [".$col."] [".$type."] da Tabela [".$tbl."] contém caracteres inválidos: [".$vlr."];");
								};

								break;
								
							case 'date':

								if(!validadate($vlr)and!empty($vlr)){
									die("Coluna [".$col."] [".$type."] da Tabela [".$tbl."] contém caracteres inválidos: [".$vlr."];");
								};

								break;

							case 'time':

									if(!validatime($vlr)and!empty($vlr)){
										die("Coluna [".$col."] [".$type."] da Tabela [".$tbl."] contém caracteres inválidos: [".$vlr."];");
									};

									break;

							case 'int':

								$vlr = tratanumero($vlr);

								if( (!is_numeric($vlr)) and
								    (strlen($vlr) <> 0) and $vlr!="NULL"){
									die("Coluna [".$col."] [".$type."] da Tabela [".$tbl."] contém caracteres inválidos: [".$vlr."];");
								}

								break;

							case 'smallint':

								$vlr = tratanumero($vlr);

								if(!is_numeric($vlr) and $vlr!="NULL"){
									die("Coluna [".$col."] [".$type."] da Tabela [".$tbl."] contém caracteres inválidos: [".$vlr."];");
								}

								break;

							case 'timestamp':
								validatimestamppost('pt-br');
								break;

							case 'time':
								break;

							case 'tinyint':

								$vlr = tratanumero($vlr);

								if (!is_numeric($vlr) and $vlr!="NULL"){
									die("Coluna [".$col."] [".$type."] da Tabela [".$tbl."] contém caracteres inválidos;");
								}

								break;

							case 'double':

/* maf140919: este bloco foi alterado incorretamente: nao trata corretamente colunas double */

								if (strpos(strtolower($vlr),"d") or strpos(strtolower($vlr),"e")) {
																    //Efetua registro da representação científica/customizada na coluna associada
								    if (array_key_exists($col."_exp", $arrtabledef[$tbl])) {
								            if (strpos(strtolower($vlr),"e")) {
								                    $_SESSION["arrpostbuffer"][$row][$act][$tbl][$col."_exp"]= str_replace(",",".",$vlr);
								                    $arrvlr=explode("e",$vlr);
								                    //maf140619: anteriormente esta parte estava devolvendo uma string a ser executada no MySQL. Ex: "576000*pow(10,7)" mas isso passava pela valida$
													
													// GVT - 15/04/2020 - Corrigido a função, anteriormente era enviado uma STRING para campos de tabela do tipo DOUBLE
								                    $_SESSION["arrpostbuffer"][$row][$act][$tbl][$col]=tratadouble($arrvlr[0]) * pow(10,tratadouble($arrvlr[1]));
													
													
								                    //$_SESSION["arrpostbuffer"][$row][$act][$tbl][$col]=tratanumero($arrvlr[0]) * pow(10,tratanumero($arrvlr[1]));
								            //maf190819: nao verificar numerico neste ponto} elseif(strpos(strtolower($vlr),"d") && is_numeric(str_replace("d", "", $vlr))){
									     } elseif(strpos(strtolower($vlr),"d")){
								                    $_SESSION["arrpostbuffer"][$row][$act][$tbl][$col."_exp"]=$vlr;//armazena valor original
								                    $arrvlr=explode("d",$vlr);
								                    $vlr=tratadouble($arrvlr[0])*tratadouble($arrvlr[1]);//Multipicacao direta da diluicao
								                    $_SESSION["arrpostbuffer"][$row][$act][$tbl][$col]=$vlr;
								            } elseif(empty($_SESSION["arrpostbuffer"][$row][$act][$tbl][$col."_exp"])){
								                    //Se estiver configurada alguma coluna associada, mas não vier valor, limpar coluna
								                    $_SESSION["arrpostbuffer"][$row][$act][$tbl][$col."_exp"]="";
								            }
								    }else{
								    	$vlr = tratadouble($vlr);
								    }
								}else{
                                                                    //inserido pois apos colocar expoente não mais era possivel retira-lo hermesp 01/06/2020
                                                                    if (array_key_exists($col."_exp", $arrtabledef[$tbl])) {
                                                                        $_SESSION["arrpostbuffer"][$row][$act][$tbl][$col."_exp"]="";
                                                                    }//fim alteracao 01/06/2020
									$vlr = tratadouble($vlr);
								}

								break;

							case 'decimal':

								$vlr = tratanumero($vlr);

								if (!is_numeric($vlr)and(!empty($vlr)) and $vlr!="NULL"){
									//print_r($arrpostbuffer);
									die("Coluna [".$col."] [".$type."] da Tabela [".$tbl."] contém caracteres inválidos: [".$vlr."]");
								}

								break;

							case 'longtext':
								break;

							case 'text':
								break;

							default:
								die("Tipo[".$type."] da Coluna[".$col."] da Tabela [".$tbl."] não previsto(2);");
								break;
						}
						
						$icol++;
					}
				}
				$strsqlcol = $strsqlcol .")";
			}
		}
	}
	null;


	return true;

}

function montascriptsexecucao(){

	global $_acoes, $_arrCQRSCarbon, $_arrRamCache, $arrRamCache, $_rstr;

	//echo "\n--montascriptexecucao()";

	$_SESSION["arrscriptsql"] = array();
	$arrpostbuffer = $_SESSION["arrpostbuffer"];
	$arrtabledef   = cb::$session["arrtabledef"];
	//$arrdb         = $_SESSION["arrdb"];

	//if($_SESSION["SESSAO"]["USUARIO"]=="marcelo"){print_r($_SESSION["arrpostbuffer"]);die;};

	//abre session para armazenar scripts de auditoria
	$_SESSION["arrscriptsqlauditoria"] = array();

	if(!is_array($arrpostbuffer)){
		die("Falha na atribuição do Array arrpostbuffer:[".$arrpostbuffer."]; (arrpostbuffer não é Array);");
	}
	if(!is_array($arrtabledef)){
		die("Falha na atribuição do Array arrtabledef:[".$arrtabledef."]; (arrtabledef não é Array);");
	}

	#ConstruÃ§Ã£o dos Comandos SQL
	$arrscriptsql      = array(); #Lote de Scripts
	$strsql            = ''; #Select, Insert Update
	$strsqlcol         = ''; # (id,nome)
	$strsqlval         = ''; # (12,'marcelo')
	$strsqlcolval      = ''; # (id = 12, nome = 'marcelo')
	$strsqlcolvalwhere = ''; # (id = 12)
	$icol              = 0;
	$ipk               = 0;
	$iwhere			   = 0;
	$tmptbl = "";

	//Armazenar o referer para utilizacao na auditoria
	$audreferer = $_SERVER["HTTP_REFERER"];

	############################## Separar as Tabelas que vieram no buffer
	//print_r($arrtabledef);die();
	//print_r($arrpostbuffer);die;

	reset($arrpostbuffer);
	reset($arrtabledef);

	//maf020120: Criar uma string para agrupar a auditoria, e facilitar a visualizacao dos "salvamentos consecutivos"
	$_rstr=rstr(8);

	$iaud = 0;

	$_modpk = retColPrimKeyTabByMod($_GET["_modulo"]);

	while (list($row, $rowarr) = each($arrpostbuffer)) {#Row Number

		//echo "Monta scripts execucao".memory_get_peak_usage()/1024/1024 . "<br>";

		while (list($act, $actarr) = each($arrpostbuffer[$row])) {#Action
			switch ($act) {
				case "i":
					$strsql = "insert into "; break;
				case "u":
					$strsql = "update "; break;
				case "d":
					$strsql = "delete from "; break;
				default:
					die("ParÃ¢metro [act:".$act."] para Tabela [".$tbl."] não aceito;");
			}

			$pkfld = "";
			$pkvlr = "";

			while (list($tbl, $tblarr) = each($arrpostbuffer[$row][$act])) {#Table
				$strsql = $strsql . nomeTabela($tbl) . " ";

				//armazenar quem eh o campo PK pra realizar a insercao de auditoria
				$pkfld = $arrtabledef[$tbl]["#pkfld"];

				//Caso seja insert, não existe valor definido. A string '#pkvalor' serÃ¡ substituÃ­da pelo insertid().
				if($act=="i"){
					$pkvlr = "#pkvalor";
				}else{
					$pkvlr = $arrpostbuffer[$row][$act][$tbl][$pkfld];
				}

				while (list($col, $vlr) = each($arrpostbuffer[$row][$act][$tbl])) {#Coluna

					/*
					 * Montar Array com scripts de auditoria automatica para registros que estao sendo inseridos, alterados ou deletados
					 * IMPORTANTE: comandos de insert estÃ£o sem o ID inserido. Sendo necessÃ¡rio capturar esse valor depois
					 */
					//echo $arrtabledef[$tbl][$col]["auditar"]."\n";
					if($arrtabledef[$tbl][$col]["auditar"]=="Y" or (
						$act!=="i" and $arrtabledef[$tbl][$col]["primkey"]=="Y" and $arrtabledef[$tbl]["#auditar"]=="Y"
					)){
						$iaud++;

						//verifica se o campo para update ou delete possui valor para poder ser inserido juntamente com cada campo que veio por POST
						if((empty($pkvlr) or $pkvlr==0) and $act != "i"){
							//print_r($arrpostbuffer);
							//maf050720: esta parte foi comentada para possibilitar que o usuario envie campos vazios mesmo que estejam sendo auditados
							//die("Erro Auditoria: O campo [".$col."] da tabela [".$tbl."] estÃ¡ marcado para ser auditado, mas o valor encontrado Ã© [Vazio] ou [0].");
						}else{

							/*Armazena o script de auditoria no Array
							 * maf271211: safe_string_escapeno valor a ser armazenado pois estava deixando passar aspas
							 */
							$_SESSION["arrscriptsqlauditoria"]
										[$row]
											[$tbl]
												//maf160820: coluna grupo incorporada à tabela [$iaud] = "(".$_SESSION["SESSAO"]["IDEMPRESA"].",'".$row."','".$act."','".$tbl."',".$pkvlr.",'".$col."','".safe_string_escape($vlr)."',sysdate(),'".$_SESSION["SESSAO"]["USUARIO"]."','".$_rstr."_".$audreferer."')";
												[$iaud] = "(".$_SESSION["SESSAO"]["IDEMPRESA"].",'".$row."','".$act."','".$tbl."',".$pkvlr.",'".$col."','".safe_string_escape($vlr)."','".$_rstr."',sysdate(),'".$_SESSION["SESSAO"]["USUARIO"]."','".$audreferer."')";
						}
					}

					//maf310720: armazenar em cache as colunas configuradas
					if($arrtabledef[$tbl]["#ramcache"]>=1 && ($arrtabledef[$tbl][$col]["ramcache"]=="Y" or $arrtabledef[$tbl][$col]["primkey"]=="Y")){
						$_arrRamCache[$row][$tbl][$col]=$vlr;
					}

					//maf101220: armazenar em cache as colunas configuradas V2
					if($arrtabledef[$tbl]["#ramcache"]>=1){
						//IDPESSOA criadora
						$arrRamCache[$row]["_idpessoa"]=$_SESSION["SESSAO"]["IDPESSOA"];
						//Criadopor
						$arrRamCache[$row]["_alteradopor"]=$_SESSION["SESSAO"]["USUARIO"];
						//Criadoem
						$arrRamCache[$row]["_alteradoem"]=date("Y-m-d H:i:s");;
						//Modulo
						$arrRamCache[$row]["_mod"]=$_GET["_modulo"];
						$arrRamCache[$row]["_idmod"]=$_SESSION["arreventcode"]["modulo"][getModReal($_GET["_modulo"])]["idmodulo"];
						//Armazena a acao
						$arrRamCache[$row]["_acao"]=$act;
						//Armazena a tabela
						$arrRamCache[$row]["_tab"]=$tbl;
						//Armazena a PK
						if($act!=="i" and $arrtabledef[$tbl][$col]["primkey"]=="Y"){
							$arrRamCache[$row]["_pk"]=$col;
							$arrRamCache[$row]["_pkval"]=$vlr;
						}

						$arrRamCache[$row]["_modpk"] = $_modpk;

						if(!empty($_modpk) && !empty($_GET[$_modpk])){
							$arrRamCache[$row]["_idmodpk"] = $_GET[$_modpk];
						}else if($_GET["_acao"] == 'i'){
							$arrRamCache[$row]["_idmodpk"] = $_SESSION["_pkid"];
						}

						if($arrtabledef[$tbl][$col]["ramcache"]=="Y" and $arrtabledef[$tbl][$col]["primkey"]!=="Y"){
                            $arrRamCache[$row]["_cols"][$col]=evaltipocoldb($tbl, $col, $arrtabledef[$tbl][$col]["type"], $vlr, "");
						}
					}

					//maf310720: compartilhar dados de CQRS com outras plataformas
					if($act!=="i" and $arrtabledef[$tbl][$col]["primkey"]=="Y" and $arrtabledef[$tbl]["#auditar"]=="Y"){
						$_arrCQRSCarbon[$row]["_idobj"]=$vlr;
					}
					if($arrtabledef[$tbl]["#auditar"]=="Y" and $arrtabledef[$tbl][$col]["auditar"]=="Y" and !preg_match('/criadopor|criadoem|alteradopor|alteradoem/', $col)
						or ($act=="d" and $arrtabledef[$tbl][$col]["primkey"]=="Y" and $arrtabledef[$tbl]["#auditar"]=="Y")
					){
						$_arrCQRSCarbon[$row]["_r"][$col]=$vlr;//O valor deve ser escapado no destino
						$_arrCQRSCarbon[$row]["_acao"]=$act;
						$_arrCQRSCarbon[$row]["_tab"]=$tbl;
						$_arrCQRSCarbon[$row]["_a"]["idempresa"]=$_SESSION["SESSAO"]["IDEMPRESA"];
						$_arrCQRSCarbon[$row]["_a"]["grupo"]=$_rstr;
						$_arrCQRSCarbon[$row]["_a"]["cmdem"]=date("Y-m-d H:i:s");
						$_arrCQRSCarbon[$row]["_a"]["cmdpor"]=$_SESSION["SESSAO"]["USUARIO"];
						$_arrCQRSCarbon[$row]["_a"]["tela"]=$audreferer;
						$_arrCQRSCarbon[$row]["_a"]["row"]=$row;
					}

					$tmptbl = $tbl;

					$inc  = $arrtabledef[$tbl][$col]["autoinc"];
					$null = $arrtabledef[$tbl][$col]["null"];
					$pk   = $arrtabledef[$tbl][$col]["primkey"];
					$type = $arrtabledef[$tbl][$col]["type"];

					$tmpcols = "";
					$tmpcols .= "<BR>row : " . $row;
					$tmpcols .= "<BR>act : " . $act;
					$tmpcols .= "<BR>tbl : " . $tbl;
					$tmpcols .= "<BR>col : " . $col;
					$tmpcols .= "<BR>vlr : " . $vlr;
					$tmpcols .= "<BR>inc : " . $inc;
					$tmpcols .= "<BR>null : " . $null;
					$tmpcols .= "<BR>pk  : " . $pk;
					$tmpcols .= "<BR>type:" . $type;
					$tmpcols .= "<BR>&nbsp;";

					//echo $tmpcols;
					//die;
					$aspa = chr(96);//Aspa(acento)` para evitar que nomes de campo invÃ¡lidos causem erro de sintaxe sql
					$colaspa = $aspa.$col.$aspa;

					if((($act=="i")and($inc == "N"))or(($act=="u")and($pk != "Y")and(($vlr == 0) or ($vlr <> "")))){

						#Coloca o primeiro '('
						if ($icol==0){
							$strsqlcol		= "(".$colaspa;
							$strsqlval		= "(";
							$strsqlcolval	= "";
						}else{
							$strsqlcol         = $strsqlcol . ", " . $colaspa;
							$strsqlval      = $strsqlval . ", ";
							$strsqlcolval      = $strsqlcolval . ", ";
						}

						$strsqlval = $strsqlval . evaltipocoldb($tbl, $col, $type, $vlr);
						$strsqlcolval      = $strsqlcolval . $colaspa . " = " .evaltipocoldb($tbl, $col, $type, $vlr);

						$icol++;

					}elseif(($act=="u")and($pk == "Y")and(!empty($vlr))){
						//echo "<br>PK e <> i";
						#Coloca o primeiro '('
						if($ipk == 0){
							$strsqlcolvalwhere = "(";
						}else{
							$strsqlcolvalwhere = $strsqlcolvalwhere . " and ";
						}

						$strsqlcolvalwhere = $strsqlcolvalwhere . $colaspa . " = " .evaltipocoldb($tbl, $col, $type, $vlr);

						$ipk++;
					}elseif(($act=="d")and($pk == "Y")and(!empty($vlr))){
						#Coloca o primeiro '('
						if($iwhere == 0){
							$strsqlcolvalwhere = "(";
						}else{
							$strsqlcolvalwhere = $strsqlcolvalwhere . " and ";
						}

						$strsqlcolvalwhere = $strsqlcolvalwhere . $colaspa . " = " .evaltipocoldb($tbl, $col, $type, $vlr);

						$ipk++;
						$iwhere++;
					}elseif(($act=="u")and($pk == "Y")and(empty($vlr))){
						die("O valor para a coluna [".$col."] PK deve ser informado para Update!");
					}
					//echo "<BR><br>";
				}//while coluna

				//echo "<br><br>" . $ipk; die();
				/*
				if($ipk == 0){
					echo "A tabela [".$tbl."] não possui coluna PK. ". chr(10) .
							" As tabelas enviadas para processamento automatico devem necessariamente conter no minimo 1 coluna com a caracteristica PK";
					//die();
				}*/

				$strsqlcol         = $strsqlcol .")";
				$strsqlval         = $strsqlval . ")";
				$strsqlcolval      = $strsqlcolval . " ";
				$strsqlcolvalwhere = $strsqlcolvalwhere . ")";
			}

			$arrscriptsql[$row][$tmptbl]["rstr"]=$_rstr;

			switch ($act){
				case "i";
					$arrscriptsql[$row][$tmptbl]["script"] = $strsql . " " . $strsqlcol . " values " . $strsqlval;
					$arrscriptsql[$row][$tmptbl]["acao"] = "i";
					break;
				case "u";
					$arrscriptsql[$row][$tmptbl]["script"] = $strsql . " set " . $strsqlcolval . " where " . $strsqlcolvalwhere;
					$arrscriptsql[$row][$tmptbl]["acao"] = "u";
					break;
				case "d";
					$arrscriptsql[$row][$tmptbl]["script"] = $strsql . " where " . $strsqlcolvalwhere;
					$arrscriptsql[$row][$tmptbl]["acao"] = "d";
					break;
				default:
					break;
			}

			// Verifica se alguma clausula where esta incompleta
			if((($act=="u") or ($act=="d")) and ($ipk == 0)){
				echo("\n<br>Nenhum campo PK da tabela [".$tmptbl."] foi enviado por POST.\n<br>Para UPDATE ou DELETE é necessário que os campos PK da tabela a ser salva estejam na pÃ¡gina.\n<br>O comando SQL estÃ¡ incompleto!\n<br><br>");
				//print_r($arrscriptsql);
				die();
			}

			//Reseta variaveis
			$icol              = 0;
			$ipk               = 0;
			$iwhere            = 0;
			$strsqlcol         = "";
			$strsqlval         = "";
			$strsqlcolval      = "";
			$strsqlcolvalwhere = "";
		}
		//print_r($arrscriptsql); //die();
		//print_r($_SESSION["arrscriptsqlauditoria"]);

		$_SESSION["arrscriptsql"] = $arrscriptsql;

	}

	//print_r($arrscriptsql);
	
	return true;
}

function executascripts(){
	global $debugSql, $_acoes, $_arrCQRSCarbon, $_arrRamCache, $arrRamCache, $_rstr;

	$scriptsDebug = "";
	//echo "\n--executascripts()";
	//print_r($_SESSION["arrscriptsql"]);die;
	
	$arrscriptsql = $_SESSION["arrscriptsql"];

	/*
	 * Armazena um codigo para indicar se ocorreu algum erro na execuÃ§Ã£o dos scripts
	 */
	$_SESSION["arrscriptsql"]["erro"]=false;

	/*
	 * verifica se a pk foi recuperada
	 */
	$pkok = false;

	if(!is_array($arrscriptsql)){
		die("Array de scripts invÃ¡lido: " . var_export($arrscriptsql,true));
	}

	unset($_SESSION["_pkid"]);
	unset($_SESSION["_pkfld"]);


	$arrScriptBulkAud=array();

	/*
	 * Executa cada um dos scripts montados pelo POST
	 */
	while(list($irow, $arrtab) = each($arrscriptsql)){

		//echo "executascripts".memory_get_peak_usage()/1024/1024 . "<br>";

		while (list($tab, $script) = each($arrtab)){

if($debugSql)syslog(LOG_DEBUG,$script["script"]);
			$_insertidaud = null;
			
			//echo "<br>\n".$script["script"]; die();

			$result = mysql_query($script["script"]);
			$scriptsDebug .= "\n--\n".$script["script"]."\n";
			/*
			 * Armazena um insertid incondicionalmente para ser utilizado para auditoria
			 */
			//$resiid = mysql_query("SELECT LAST_INSERT_ID() as liid;");
			//$rowiid = mysql_fetch_assoc($resiid);
			$_insertidaud = mysql_insert_id();

			/*
			 * Devolve ao array de scripts o insertid gerado pelo banco de dados, para ser utilizado em save-pos-change
			 */
			if($script["acao"]=="i"){
				$_SESSION["arrscriptsql"][$irow][$tab]["insertid"]=$_insertidaud;
			}

			if (!$result) {
				
				/*
				 * Verifica o raise do mysql: se for erro de proc nao encontrada, buscar na tab de mensagens para mostrar ao usuario
				 */
				$_mysqlerrorno = trim(mysql_errno());
				$_mysqlerror = trim(mysql_error());
				$arrerro = array();
				$deadlock = false;

				switch ($_mysqlerrorno) {
					case "1305"://procedure nao existe. vai retornar 'RAISE' por exemplo em caso de triggers que sao interrompidas propositalmente ao chamar uma determinada trigger que nao existe, simplesmente para mostrar um erro para o usuario
						$arrerro = explode(" ",$_mysqlerror);
						$arrerro = explode(".",$arrerro[1]);
						$_strerro = retparerr($arrerro[1],$_mysqlerror);
					break;

					case "1062":
						$arrerro = explode(" ",$_mysqlerror);
						$_strerro = "Erro: A informação ".$arrerro[2]." não pode ser duplicada para a chave ".$arrerro[2]." na tabela [".$tab."]";
					break;

					case "1449":
						$arrerro = explode(" ",$_mysqlerror);
						$_strerro = "A Trigger/Function/Procedure executada provavelmente foi criada com DEFINER inexistente:\n".$_mysqlerror;
						break;
					case "1213":
						$arrerro = explode(" ",$_mysqlerror);
						$_strerro = "Erro Deadlock: Tente reiniciar a transação:\n".$_mysqlerror;
						$deadlock = true;
						break;
					
					default:
						$_strerro ="Erro na Execução do script [".$irow."]: " . mysql_error()."<BR><b>Script:</b><BR>".$script["script"]."<BR><b>Err Number:</b><BR>".mysql_errno();
					break;
				}

				$_SESSION["arrscriptsql"]["erro"]=true;

				$res = mysql_query("ROLLBACK");

				if($deadlock){
					d::b()->query("INSERT INTO log (idempresa,tipoobjeto,idobjeto,tipolog,log,info,criadoem) VALUES (".$_SESSION["SESSAO"]["IDEMPRESA"].",'pessoa',".$_SESSION["SESSAO"]["IDPESSOA"].",'debugbacktrace','".d::b()->real_escape_string(print_r(debug_backtrace(),true))."','".d::b()->real_escape_string($scriptsDebug)."',now())") or die(mysqli_error(d::b()));
					unset($scriptsDebug);
				}
				if(!$res){
					echo "\n<br>Erro ao efetuar Rollback[2]";
					$_SESSION["arrscriptsql"]["erro"]=true;
				}
				$_SESSION["erros_submit"][]=$_strerro;
				die($_strerro);
				return false;

			}else{

				/*
				 * Recupera informacoes do campo PK da tabela em questao
				 */
				$_fldpk = cb::$session["arrtabledef"][$tab]["#pkfld"];
				$_arrfldpkdef = cb::$session["arrtabledef"][$tab][$_fldpk];

				//CacheV2: Casos de 'pk autoinc'
				if($script["acao"]=="i" and cb::$session["arrtabledef"][$tab]["#ramcache"]>=1){
					$arrRamCache[$irow]["_pk"]=$_fldpk;
					$arrRamCache[$irow]["_pkval"]=$_insertidaud;
				}

				/*
				 * Armazena o id gerado na primeira linha para o retorno na pagina
				 * !is_numeric está sendo utilizado para os casos em que forem enviadas requisições onde a linha é descrita por texto. Ex: [ajax] ao invés de [1]
				 */
				if(($irow==1 or !is_numeric($irow)) and ($script["acao"]!="d")){
					
					//Envia Header de resposta para que _acao nao permaneca como 'i'
					if($script["acao"]=="i"){
						$_SESSION["headergetretorno"] = true;
						$_SESSION["_pkfld"] = $_fldpk;
					}

					//casos de 'pk autoinc' e 'pk varchar'
					if(stripos($_arrfldpkdef["type"],"int")!==false and $_arrfldpkdef["autoinc"]=="Y" and $script["acao"]=="i"){
						$_SESSION["_pkid"] = $_insertidaud;
						if(cb::$session["arrtabledef"][$tab]["#ramcache"]>=1){
							$_arrRamCache[$irow][$tab][$_fldpk]=$_insertidaud;
						}
						if(cb::$session["arrtabledef"][$tab]["#auditar"]){//maf: somente tabelas com auditoria configurada
							//$_arrCQRSCarbon[$irow]["_r"][$_fldpk]=$_insertidaud;
							$_arrCQRSCarbon[$irow]["_idobj"]=$_insertidaud;
						}

						$pkok = true;

					}elseif($_arrfldpkdef["type"]=="varchar" and $_arrfldpkdef["autoinc"]=="N"){
						//maf140211: para casos de campos varchar que sÃ£o chave primaria
						$_SESSION["_pkid"] = $_SESSION["arrpostbuffer"]["1"][$script["acao"]][$tab][$_fldpk];
						//$_SESSION["vargetpk"] .= "=" . $_SESSION["arrpostbuffer"]["1"]["i"][$tab][$_fldpk];
						
						$pkok = true;

					}elseif(stripos($_arrfldpkdef["type"],"int")!==false and $_arrfldpkdef["autoinc"]=="Y" and $script["acao"]=="u"){
						//Verifica o tipo do row para tentar recuperar o ID do primeiro row
						if(!is_numeric($irow)){//_x_u_tab_col
							$_SESSION["_pkid"] = array_values($_SESSION["arrpostbuffer"])[0][$script["acao"]][$tab][$_fldpk];
						}else{//_1_u_tab_col
							$_SESSION["_pkid"] = $_SESSION["arrpostbuffer"]["1"][$script["acao"]][$tab][$_fldpk];
						}
						$pkok = true;
					}

				}else{
					$pkok = true;
				}
				//echo("\npkok[".$script["acao"].$irow.$_arrfldpkdef["autoinc"].$pkok."\n");
				//print_r($_SESSION["arrscriptsqlauditoria"]);
				//print_r($arrscriptsql);
				//echo "count scritps aud:[".count($_SESSION["arrscriptsqlauditoria"])."]";

				/*
				 * Para contemplar os casos em que nao mesma tela existem campos de varias tabelas,
				 * eh necessario checar para cada tabela se existe um script de auditoria relacionado,
				 * por isto esta primeira condicao inicial de count(array[] irow[] itab[])
				 */
				if(!empty($_SESSION["arrscriptsqlauditoria"][$irow]) and count($_SESSION["arrscriptsqlauditoria"][$irow][$tab])>0){

					/*
					 * Monta script INSERT em modo BULK para auditoria.
					 */
					$scriptauditbulk = "INSERT INTO `_auditoria` (idempresa,linha,acao,objeto,idobjeto,coluna,valor,grupo,criadoem,criadopor,tela) VALUES";
					$bulkvirgula = "";

					while(list($irowaud, $sqlaudit) = each($_SESSION["arrscriptsqlauditoria"][$irow][$tab])){//array de scripts

						/*
						 * Em caso de script de Insert, os scripts de auditoria neste ponto ainda não contÃ©m o INSERT_ID do Mysql
						 * Isto Ã© colocado neste ponto para cada script de insert executado substituindo o campo do IDOBJETO da tabela [auditoria]
						 */
						if($script["acao"]=="i"){
							$sqlaudit = str_replace("#pkvalor",$_insertidaud,$sqlaudit);
						}

						$scriptauditbulk .= "\n ".$bulkvirgula.$sqlaudit;
						$bulkvirgula = ",";

					}//while(list($irowaud, $sqlaudit) = each($_SESSION["arrscriptsqlauditoria"][$irow][$tab])){//array de scripts
					
					/*
					 * Armazena script INSERT em modo BULK para auditoria
					 */
					$arrScriptBulkAud[]=$scriptauditbulk;

				}//count($_SESSION["arrscriptsqlauditoria"])

				/*
				 * maf271211: Executa codigos SAVEPOSCHANGE da TABELA apÃ³s a execuÃ§Ã£o dos scripts
				 */
				$tab_saveposchange = _CARBON_ROOT."eventcode/tab/".$tab."__saveposchange";
				if (file_exists($tab_saveposchange)) {
					include_once($tab_saveposchange);
				}

				//echo"<BR>Script [".$irow."] executado";
			}
		}
	}//while(list($irow, $arrtab) = each($arrscriptsql)){
	unset($scriptsDebug);
	unset($_SESSION["arrscriptsqlauditoria"]);

	//se ocorreu algum erro em algum script, esta variavel ira conter true
	if($_SESSION["arrscriptsql"]["erro"]){

		//Efetua Rollback
		$res = mysql_query("ROLLBACK");
		if(!$res){
			echo "\n<br>Erro ao efetuar Rollback[2]";
			$_SESSION["arrscriptsql"]["erro"]=true;
		}
		return false;

	}else{

		//falha ao recuperar pk em caso de script de insert no primeiro row de execucao do buffer
		if($pkok==false){
			echo("Erro ao armazenar PK!\n".mysql_error());

			$_SESSION["arrscriptsql"]["erro"]=true;
			return false;

		}else{

			/*
			 * maf271211: Executa codigos SAVEPOSCHANGE para PAGINAS apÃ³s a execuÃ§Ã£o dos scripts
			 */
			if($_SESSION["arreventcode"]["modulo"][getModReal($_GET["_modulo"])]["evento_saveposchange"]=="Y"){
				
				$arq_saveposchange = _CARBON_ROOT."eventcode/modulo/saveposchange__".getModReal($_GET["_modulo"]).".php";

				if (file_exists($arq_saveposchange)) {
					include_once($arq_saveposchange);
				}else{
					cbSetPostHeader("0","erro");
					die("Erro cbpost: Módulo configurado com evento saveposchange, mas houve falha ao abrir o arquivo:\n\n ".$arq_saveposchange." \n\nAltere permissões do arquivo, ou verifique evento existente no DB.");
				}
			}

			//print_r($_SESSION["arrscriptsql"]);die;
			//O commit pode ser impedido de ser executado, caso ocorra esse no save _pos_change
			mysql_query("COMMIT")or die("Erro ao efetuar Commit!".mysql_error());

			/* /maf310720: armazenar a tabela em cache conforme configuracao
			foreach ($_arrRamCache as $ic => $atab) {
				$pkt=null;
				foreach ($atab as $tab => $tabdados) {
					$pkt = $tabdados[cb::$session["arrtabledef"][$tab]["#pkfld"]];
					$tabdados = array_merge($tabdados, ["#post"=>json_encode($_POST)]);
					re::dis()->hMSet(
						'_cache:'.$tab.':'.$pkt
						, $tabdados);
					/* o hmset vai ser depreciado. O codigo abaixo é funcional
					foreach ($tabdados as $c => $v) {
						re::dis()->hSet(
							'_cache:'.$tab.':'.$pkt
							,$c
							, $v);
					}
					* /
				}
			}*/

			//Grava os dados em uma chave específica do Redis. O redis vai aceitar os dados, e assim liberamos sucesso para o client Http do PHP
			//Isto faz com que os dados sejam disparados, pelo redis, para o canal PUB/SUB, que deve estar ativo pela configuracao
			//https://redis.io/topics/pubsub
			if(count($arrRamCache)>0){
				re::dis()->hMSet(
						'_queue_cache:'.rstr(8)
						, ["#data"=>json_encode($arrRamCache)]);
			}else{
				//@todo: Tratar casos de cache configurado sem nenhum valor neste ponto
			}

			//maf310720: enviar cada um dos scripts de auditoria para as plataformas integradas
			foreach ($_arrCQRSCarbon as $row => $aud) {
				if(empty($aud["_a"]))continue; //Nao enviar para o redis quando nao houver no minimo 1 coluna sendo auditada
				re::dis()->hMSet(
					'_aud:'.$_rstr."_".$row
					,[
						"#j"	=>	json_encode($aud),
						"#post" => 	json_encode($_POST)
					]
				);
			}

			//Após o commit, executar os scripts de auditoria. Isto deve ser colocado fora da TRANSAÇÃO, para permitir ignorar o log binário
			logBinario(false);
			while(list($irowaud, $sqlaudit) = each($arrScriptBulkAud)){//array de scripts
				$raud = d::b()->query($sqlaudit);
				if(!$raud){
					cbSetPostHeader("0","erro");
					die("Erro na Execução do script bulk de Auditoria[".$irow."]: " . mysql_error() ."\n\nScript:\n".$scriptauditbulk."\nErr Number:\n".mysql_errno());
					return false;
				}
			}
			logBinario(true);
			return true;
		}
	}

}
function limpaget($inparget,$inres){

	//echo "\n--limpaget()";

	$getpartes = explode("=",$inparget);

	switch ($getpartes[0]) {
		case "resscripterro":
			$outstr = "";
			break;
		case "resscript":
			$outstr = "";
			break;
		case "acao"://maf140211: nao substituir por u em caso de erro em insert porque o INSERT_ID nao foi gerado
			if($inres!="false"){
				$outstr = str_replace("acao=i","acao=u",$inparget);
			}else{
				$outstr = $inparget;
			}
			break;
		default:
			$outstr = $inparget;
			break;
	}
	//echo "\n[$outstr]\n";
	return $outstr;
}

if(		(montapostbuffer()     )
	and (montatabdef()         )
	and (validapostbuffer()    )
	and (montascriptsexecucao())
	and (executascripts()      )
	){

	cbSetPostHeader("1","html");
	$_acao = "u";
	$_pkid = $_SESSION["_pkid"];
	$_SESSION["insertid"]=$_pkid;
	
	//Envia Header de resposta para que _acao nao permaneca como 'i'
	if($_SESSION["headergetretorno"]===true){
		header('X-CB-PKFLD: '.$_SESSION["_pkfld"]);
		header('X-CB-PKID: '.$_SESSION["_pkid"]);
		unset($_SESSION["headergetretorno"]);
	}
	
	//maf071213: armazenar os variaveis de insert em caso de Token, para evitar updates em registros que não foram inseridos na sessão atual
	if($_SESSION["SESSAO"]["TOKEN"]==true){
		//$_SESSION["SESSAO"]["TOKENPKFLD"]=;
		//$_SESSION["SESSAO"]["TOKENPKFLD"]=;
		//unset($_SESSION["headergetretorno"]);
	}
	
	//maf310513: Caso seja enviado o header de controle, interromper o processamento para nao recarregar a pagina
	if($_SERVER["HTTP_X_CB_REFRESH"]=="N"){
		die;
	}

	if($_GET["_refresh"]=="false"){
		header('X-CB-FORMATO: none');
		header('CB-REFRESH: false');
		die;
	}
	
}else{
	//algum erro ocorreu
	echo("\ncbpost: Erro!\n");
	print_r($_SESSION["erros_submit"]);
	die;
}
//ob_end_flush();
?>