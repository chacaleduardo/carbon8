<?

//maf: Upgrade em 19/08/20
//@todo: Verificar ações permitidas: 'r' ??
//@todo: Controlar headers antes do save
//@todo: !!!!!!!!!!!!!!!!!!!! Controlar as PKs geradas por insert, e atribuir ao pacote de scripts relativo
//@todo: Controlar o comportamento quando algum commit for executado no SAVEPOSCHANGE
//@todo: Reset de variaveis usando reflection
//@todo: Invalidar tratamento para casos de colunas varchar que são chave primária: todas as tabelas devem ter PK/Int/Autonumber
//@todo: Token: verificar se está tentando atualizar outro ID diferente do inserido anteriormente: possibilidade de quebra de segurança
//@todo: validaPostBuffer: enviar coluna com valor 'null', e verificar as variações condicionais para NULL e ''

class cmd{
	//Strings
	public $erro="";
	private $grupoCmd="";
	private $referer="";					//Armazenar o referer para utilizacao na auditoria

	private $pkid="";
	private $pkfld="";
	public $errorCode = "";

	//Bool
	public $autoRollback=true;
	public $debugSql=false;
	public $setPostHeaders=true;
	public $disablePrePosChange = false;

	//Arrays
	public $_POST;					//Armazena os dados enviados via post
	public $_GET;					//Armazena os dados enviados via get
	public $BUFFER=array();			//Armazena os dados do post estruturados no padrão do Carbon
	public $acoes=array();			//Controla quais ações estão sendo executadas durante o processamento
	public $arrAcoesPerm = array();	//Ações permitidas
	public $arrCQRSCarbon=array();	//Compartilhar todos os comandos que utilizam o carbon com outras plataformas
	public $arrRamCache=array();		//maf060820: facilitar o controle das acoes enviadas via post i||u||d
	public $eventCodes=array();
	public $tabDef=array();
	private $scriptsSql=array();
	private $scriptsSqlAuditoria=array();

	//Métodos
	public function err($msg,$ln="",$fn=__METHOD__){
		$this->erro=$msg;
		if($this->autoRollback)d::b()->cmd("ROLLBACK");
		header("CB-ERRO-LINE: ".$ln);
		header("CB-ERRO-FUNCTION: ".$fn);
		header("CB-ERRO-LOCAL: ".str_replace("__","->",basename($this->getIncludedFile(),".php")));
		if($this->setPostHeaders){
			cbSetPostHeader("0","erro");
		}
		return false;
	}

	public function reset(){
		//$oClass = new ReflectionClass(__CLASS__);
		//print_r($oClass->getConstants());
		$this->erro="";
		$this->grupoCmd="";
		$this->referer="";
		$this->pkid="";
		$this->pkfld="";
		$this->debugSql=false;
		// GVT - 10/09/2021 - Por se tratar de uma variável pública
		// fica a vontade do programador ativar ou desativar a opção
		//$this->setPostHeaders=true;
		$this->_POST;
		$this->_GET;
		$this->BUFFER=array();
		$this->acoes=array();
		$this->arrAcoesPerm = array();
		$this->arrCQRSCarbon=array();
		$this->arrRamCache=array();
		$this->eventCodes=array();
		$this->tabDef=array();
		$this->scriptsSql=array();
		$this->scriptsSqlAuditoria=array();
	}

	public function insertid(){
		return $this->pkid;
	}

	public function getPkid(){
		return $this->pkid;
	}
	
	public function getPkfld(){
		return $this->pkfld;
	}

	private function getIncludedFile(){
		$file = false;
		$backtrace =  debug_backtrace();
		$include_functions = array('include', 'include_once', 'require', 'require_once');
		for ($index = 0; $index < count($backtrace); $index++)
		{
			$function = $backtrace[$index]['function'];
			if (in_array($function, $include_functions))
			{
					$file = $backtrace[$index - 1]['file'];
					break;
			}
		}
		return $file;
	}

	public function save($inpost=array()){
		if(!is_array($inpost) or count($inpost)<1){
			cbSetPostHeader("0","erro");
			die("Save: Nenhuma informação foi enviada.");
			//throw new Exception("Array inválido: ".__FUNCTION__);
			//return $this->err("Array inválido",__FUNCTION__);
		}

		$this->reset();

		$this->_POST=$inpost;
		$this->_GET=$_GET;
		$this->arrAcoesPerm = array("i", "u", "d", "r");
		$this->referer=$_SERVER["HTTP_REFERER"];

		if(!$this->montaPostBuffer())return false;
		if(!$this->montaTabDef())return false;
		if(!$this->validaPostBuffer())return false;
		if(!$this->montaScriptsExecucao())return false;
		if(!$this->executaScripts())return false;
		return true;
	}

	//@todo: Criar descricao
	public function montaPostBuffer(){

		$itab=0;
		$iaca=0;
		$iacatab=0; //verifica se foi informada mais de 1 tabela por "linha"

		array_multisort($this->_POST);
		reset($this->_POST);

		//Constroi arrays a partir do caractere chave do carbon nos input names
		foreach ($this->_POST as $chave => $vlr) {
			
			//Se existirem 3 caracteres chave, o input ira compor o buffer
			$r = explodeInputNameCarbon($chave);
	
			if(count($r)==4){
				if((!is_numeric($r[1])) && (strlen($r[1]) == 1) && (in_array($r[1],$this->arrAcoesPerm))	){//Ação para tabela
					if(strlen($r[2]) > 0){ //Se o nome da tabela veio preenchido					
						if(strlen($r[3]) > 0){//Se o nome do campo veio preenchido
							//Montagem da Estrutura do BUFFER para o Carbon:
							$this->BUFFER[$r[0]][$r[1]][$r[2]][$r[3]] = $vlr;
						}
						$itab ++;
						//maf060820: melhorar a verificacao de acoes sendo executadas na montagem do buffer
						$this->acoes[$r[2]][$r[1]]=$itab;
					}
					$iacatab = count($this->BUFFER[$r[0]][$r[1]]);
					$iaca ++;

					if($iacatab > 1){
						$tabEnviadas=array();
						foreach ($this->BUFFER[$r[0]][$r[1]] as $k=>$v) {
							$tabEnviadas[]=$k;
						}
						$tabEnviadas= implode(", ", $tabEnviadas);
						return $this->err("Mais de 1 tabela foi enviada por POST na Linha [".$r[0]."] do Buffer.\n\nPossibilidade: caso seja uma requisição Ajax, troque o Nº da linha enviada por texto. Ex: _ajax_u_tab_col\n\nTabelas enviadas: ".$tabEnviadas);
					}
				}
			}else{//211116: ignorar nomeclaturas inválidas
				 null;
			}
		}//foreach
	
		if ($iaca == 0){
			return $this->err("Nenhuma ação foi enviada");
		}
	
		if ($itab == 0){
			return $this->err("Nenhuma tabela foi enviada");
		}

		//Existem tabelas no buffer, corretamente configuradas com ações permitidas
		if (($itab >= 1) && ($iaca >= 1)){
			return true;
		}else{
			return $this->err("A estrutura de dados enviada está mal-formada");
		}
	}
	//@todo: Criar descricao
	public function montaTabDef(){

		reset($this->BUFFER);
		foreach ($this->BUFFER as $row => $rowarr) {//Identificador da linha
			foreach ($this->BUFFER[$row] as $act => $actarr) {//Ação
				foreach ($this->BUFFER[$row][$act] as $tbl => $tblarr) {//Tabela

					//maf191103: SEGURANCA: verificar se a tabela está relacionada nas permissões de objetos de banco de dados do Módulo
					$filePart = str_replace(_CARBON_ROOT, "", $_SERVER["SCRIPT_FILENAME"]);

					//$strlps=" AND lm.idlp = '".$_SESSION["SESSAO"]["IDLP"]."' ";

					$strlps=" AND lm.idlp in(".getModsUsr("LPS").") ";

					$sqlobjf = "SELECT 1
									FROM "._DBCARBON."._formobjetos o, "._DBCARBON."._lpmodulo lm
									WHERE o.modulo = '".$this->_GET["_modulo"]."'
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
									AND m.modulo = '".$this->_GET["_modulo"]."'";

					$resobjf = d::b()->sel($sqlobjf);
					if(!$resobjf)return $this->err("Erro ao pesquisar objetos de formulario:".mysql_error()."\nSQL: ".$sqlobjf);

					//Maf: Verificar a necessidade do bypass neste ponto. Pode representar falha de seguranca
					//if(mysql_num_rows($resobjf)<1){
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
						
						return $this->err("LP sem permissão de [".$acatmp."] à tabela [".$tbl."].\nVerificar configurações da LP [".getModsUsr("LPS")."]\ne/ou do Módulo [".$this->_GET["_modulo"]."]\npara o script [".$filePart."].<br><a href='javascript:janelamodal(\"?_modulo=_modulo&_acao=u&modulo=".getModReal($this->_GET["_modulo"])."\")'>Ajustar</a>");
					}

					//Buscar Definição da Tabela no dicionário de dados
					$this->tabDef[$tbl] = retarraytabdef($tbl);

					//Executa códigos de COLUNAS AUTOMÁTICAS
					$arrcolauto	= retarrcolauto();
					foreach ($arrcolauto as $col => $val){
						$VALOR_COLUNA_AUTOMATICA = false;//Esta variável será preenchida pelo código executado no arquivo
						if(array_key_exists($col,$this->tabDef[$tbl])){//se a coluna automática existir na tabela em questão
							if(!empty($arrcolauto[$col][$act])){
								//Executa o código armazenado
								$codcolauto = _CARBON_ROOT."eventcode/colauto/col__".$act."__".$col;
								include($codcolauto);//DEVE ser include, porque o include_once inclui o codigo para execucao somente uma vez

								if($VALOR_COLUNA_AUTOMATICA !== false){//se a coluna foi REALMENTE alterada pelo codigo
									$this->BUFFER[$row][$act][$tbl][$col] = $VALOR_COLUNA_AUTOMATICA;
								}
							}
						}
						$VALOR_COLUNA_AUTOMATICA = null;
					}

					//maf040320: Considerar colunas preenchidas automaticamente por variáveis globais pré-definidas
					foreach ($dic[$tbl] as $col => $conf) {
						if($conf["prompt"]=="var"){
							if($conf["code"]=="idpessoa"){
								$this->BUFFER[$row][$act][$tbl][$col]=$_SESSION["SESSAO"]["IDPESSOA"];
							}
						}
					}
				}//foreach ($this->BUFFER[$row][$act] as $tbl => $tblarr) {//Tabela
			}//foreach acao
		}//foreach linha
		return true;
	}
	//@todo: Criar descricao
	public function validaPostBuffer(){

		//maf271211: Inicia transação ANTES de execucao de qualquer codigo de evento PRE ou POS
		$res=d::b()->startTransaction();
		if(!$res)return $this->err("Falha ao iniciar Transação");

		//Inicializa eventcodes
		$this->eventcodes = sessionArrayEventCode("modulo", $this->_GET["_modulo"]);

		$_clientheaders = getallheaders();

		//---------------------------------------------------------------------------------------
		//Executa SAVEPRE CHANGE: A abertura da variavel interna de arraypostbuffer deve sempre ser feita apos este ponto
		if($this->eventcodes["modulo"][getModReal($this->_GET["_modulo"])]["evento_saveprechange"]=="Y" && $_clientheaders["CB-BYPASS"]!=="Y" && !$this->disablePrePosChange){
			$arq_saveprechange = _CARBON_ROOT."eventcode/modulo/saveprechange__".getModReal($this->_GET["_modulo"]).".php";
			if(file_exists($arq_saveprechange)) {
				include_once($arq_saveprechange);
			}else{
				return $this->err("Erro cbpost: Módulo configurado com evento saveprechange, mas houve falha ao abrir o arquivo:\n\n".$arq_saveprechange."\n\nAltere permissões do arquivo, ou verifique evento existente no DB.");
			}	
		}else{
			null;
			//return $this->err("saveprechange nao encontrado");
		}

		if(!is_array($this->BUFFER))return $this->err("Falha na atribuição do Array [buffer]");
		if(!is_array($this->tabDef))return $this->err("Falha na atribuição do Array [tabdef]");

		reset($this->BUFFER);
		foreach ($this->BUFFER as $row => $rowarr) {//Identificador da linha
			//$arrsql[$row] = array();
			foreach ($this->BUFFER[$row] as $act => $actarr) {//Ação
				if(!in_array($act,$this->arrAcoesPerm)){
						return $this->err("Parâmetro [act:".$act."] para Tabela [".$tbl."] inválido!");
				}
				foreach ($this->BUFFER[$row][$act] as $tbl => $tblarr) {//Tabela
					/* maf241013: verificar campos não nulos que não foram enviados via post. somente em caso de INSERT, porque isto pode dificultar UPDATES
					 *  Isto evita campos '' ou '0' na tabela que na maioria das vezes é um campo FK que o programador esqueceu de enviar
					 */
					if($act=="i"){
						//Verifica a diferençaa entre os KEYS dos 2 arrays
						$diffnullable = array_diff_key($this->tabDef[$tbl]["#arrnullable"],$tblarr);	
						if(count($diffnullable)>=1){
							echo("As colunas NOT NULL da tabela [".$tbl."] não foram enviados via POST na linha [".$row."] do buffer:");
							foreach ($diffnullable as $k => $value) {
								echo "\n[".$k."]";
							}
							die;
						}
					}

					//maf271211: Executa codigos SAVEPRECHANGE da TABELA após a execução dos scripts
					$tab_saveprechange = _CARBON_ROOT."eventcode/tab/".$tbl."__saveprechange";
					if (file_exists($tab_saveprechange)) {
						include_once($tab_saveprechange);
					}

					//$strsql = $strsql . $tbl . " ";
					foreach ($this->BUFFER[$row][$act][$tbl] as $col => $vlr) {
						//Verifica se a coluna Existe na Tabela
						if(!array_key_exists($col,$this->tabDef[$tbl])){
							if(empty($this->tabDef[$tbl])){
								//Possibilidade 1: executar em [presave] a chamada para retarraytabdef('tabela')
								//Possibilidade 2: A tabela não foi salva no Dicionário de Dados
								return $this->err("A Tabela [".$tbl."] do buffer não retornou sua estrutura.\n<a href='javascript:janelamodal(\"?_modulo=_mtotabcol&_acao=u&PK="._DBAPP.".".$tbl."\")'>Ajustar</a>");
							}else{
								return $this->err("A Coluna [".$col."] do Buffer [POST] não pertence à Tabela [" .$tbl."] no database ["._DBAPP."]<a href='javascript:janelamodal(\"?_modulo=_mtotabcol&_acao=u&PK="._DBAPP.".".$tbl."\")'>Ajustar</a>",__LINE__,__METHOD__);
							}
						}else{

							//Verifica a coluna IDEMPRESA
							if($col=="idempresa" and ($vlr==0 or empty($vlr)) and $tbl!=='_empresa'){
								return $this->err("Erro: Coluna idempresa com valor vazio!");
							}
							$inc  = "";
							$null = "";
							$pk   = "";
							$type = "";
							$chk  = "";

							$inc  = $this->tabDef[$tbl][$col]["autoinc"];
							$null = $this->tabDef[$tbl][$col]["null"];
							$pk   = $this->tabDef[$tbl][$col]["primkey"];
							$type = $this->tabDef[$tbl][$col]["type"];
							$chk  = $this->tabDef[$tbl][$col]["checkbox"];

							//Token: verificar se está tentando atualizar outro ID diferente do inserido anteriormente: possibilidade de quebra de segurança
							if($_SESSION["SESSAO"]["TOKEN"]==true and $act != "i" and $pk=="Y" and $vlr != $_SESSION['_pkid']){
								echo "pkvlr:".$_SESSION['_pkid']."-Vlr:".$vlr;
								return $this->err("Alteração não permitida!");
							}

							//---------------------------------- AUTO INCREMENTO
							switch ($inc){
								case "Y":
									if(($act=="i") and ($vlr <> 0) and (strlen($vlr) <> 0) ){
										return $this->err("Coluna [".$col."] AutoIncremento não pode conter valor [".$vlr."] para Insert");
									}
									break;
								case "N":
									if($pk=="Y" and stripos($type,"int")!==false){
										return $this->err("Coluna [".$col."] é Chave Primária (PK), e obrigatoriamente deve ser configurada para AutoIncremento");
									}
									break;
								default:
									return $this->err("Coluna [".$tbl."].[".$col."] possui parâmetro [autoinc] inválido");
							}

							//---------------------------------- ALLOW NULL
							switch ($null) {
								case "N":
									if(($act <> "d")and ($inc <> "Y")and (strlen($vlr) == 0)){
										if($pk=="Y"){
											return $this->err("Coluna PK não foi configurada como Auto Incremento no DB");
										}else{
											return $this->err("Coluna [".$col."] Not Null da tabela [".$tbl."] não pode conter valor vazio<br/><a href='javascript:janelamodal(\"?_modulo=_mtotabcol&_acao=u&PK="._DBAPP.".".$tbl."\")'>Ajustar</a>");
										}
									}
									break;
								case "Y":
									break;
								default:
									return $this->err("Coluna [".$tbl."].[".$col."] possui parâmetro [null] inválido");
							}

							//Converter strings "null" para null
							$vlr=($vlr=="null")?"":$vlr;

							//---------------------------------- TIPO CAMPO
							switch ($type) {
								case 'varchar':
									if((!is_string($vlr)) and (strlen($vlr) <> 0) ){
										return $this->err("Coluna [".$col."] [".$type."] da Tabela [".$tbl."] contém caracteres inválidos:".$vlr.";");
									}
									break;
									
								case 'enum':
									if ((!is_string($vlr)) and	(strlen($vlr) <> 0) ) {
										return $this->err("Coluna [".$col."] [".$type."] da Tabela [".$tbl."] contém caracteres inválidos:".$vlr.";");
									}
									break;

								case 'json':
									if ((!is_string($vlr)) and	(strlen($vlr) <> 0)) {
										return $this->err("Coluna [".$col."] [".$type."] da Tabela [".$tbl."] contém caracteres inválidos:".$vlr.";");
									}
									break;

								case 'bigint':
									$vlr = tratanumero($vlr);
									if (!is_numeric($vlr) and $vlr!="NULL") {
										return $this->err("Coluna [".$col."] [".$type."] da Tabela [".$tbl."] contém caracteres inválidos: [".$vlr."];");
									}
									break;

								case 'char':
									if((!is_string($vlr)) and
										(strlen($vlr) <> 0) ){
										return $this->err("Coluna [".$col."] [".$type."] da Tabela [".$tbl."] contém caracteres inválidos: [".$vlr."];");
									}
									break;

								case 'datetime':
									if(!validadatetime($vlr)and!empty($vlr)){
										return $this->err("Coluna datetime [".$col."] [".$type."] da Tabela [".$tbl."] contém caracteres inválidos: [".$vlr."];");
									};
									break;

								case 'date':
									if(!validadate($vlr)and!empty($vlr)){
										return $this->err("Coluna [".$col."] [".$type."] da Tabela [".$tbl."] contém caracteres inválidos: [".$vlr."];");
									};
									break;

								case 'time':
										if(!validatime($vlr)and!empty($vlr)){
											return $this->err("Coluna [".$col."] [".$type."] da Tabela [".$tbl."] contém caracteres inválidos: [".$vlr."];");
										};
										break;

								case 'int':
									$vlr = tratanumero($vlr);
									if( (!is_numeric($vlr)) and
										(strlen($vlr) <> 0) and $vlr!="NULL"){
										return $this->err("Coluna [".$col."] [".$type."] da Tabela [".$tbl."] contém caracteres inválidos: [".$vlr."];");
									}
									break;
									
								case 'smallint':
									$vlr = tratanumero($vlr);
									if(!is_numeric($vlr) and $vlr!="NULL"){
										return $this->err("Coluna [".$col."] [".$type."] da Tabela [".$tbl."] contém caracteres inválidos: [".$vlr."];");
									}
									break;

								case 'timestamp':
									if(!validadatetime($vlr) and !empty($vlr)){
										return $this->err("Coluna timestamp [".$col."] [".$type."] da Tabela [".$tbl."] contém caracteres inválidos: [".$vlr."];");
									};
									break;

								case 'time':
									break;

								case 'tinyint':
									$vlr = tratanumero($vlr);
									if (!is_numeric($vlr) and $vlr!="NULL"){
										return $this->err("Coluna [".$col."] [".$type."] da Tabela [".$tbl."] contém caracteres inválidos;");
									}
									break;

								case 'double':
									if(strpos(strtolower($vlr),"d") or strpos(strtolower($vlr),"e")){
										//Efetua registro da representação científica/customizada na coluna associada
										if(array_key_exists($col."_exp", $this->tabDef[$tbl])){
											if (strpos(strtolower($vlr),"e")){
												$this->BUFFER[$row][$act][$tbl][$col."_exp"]= str_replace(",",".",$vlr);
												$arrvlr=explode("e",$vlr);
												//maf140619: anteriormente esta parte estava devolvendo uma string a ser executada no MySQL. Ex: "576000*pow(10,7)" mas isso passava pela validacao
												// GVT - 15/04/2020 - Corrigido a função, anteriormente era enviado uma STRING para campos de tabela do tipo DOUBLE
												$this->BUFFER[$row][$act][$tbl][$col]=tratadouble($arrvlr[0]) * pow(10,tratadouble($arrvlr[1]));
											}elseif(strpos(strtolower($vlr),"d")){
												$this->BUFFER[$row][$act][$tbl][$col."_exp"]=$vlr;//Armazena valor original
												$arrvlr=explode("d",$vlr);
												$vlr=tratadouble($arrvlr[0])*tratadouble($arrvlr[1]);//Multiplicacao direta do valor
												$this->BUFFER[$row][$act][$tbl][$col]=$vlr;
											} elseif(empty($this->BUFFER[$row][$act][$tbl][$col."_exp"])){
												//Se estiver configurada alguma coluna associada, mas não vier valor, limpar coluna
												$this->BUFFER[$row][$act][$tbl][$col."_exp"]="";
											}
										}else{
											$vlr = tratadouble($vlr);
										}
									}else{
										//inserido pois apos colocar expoente não mais era possivel retira-lo hermesp 01/06/2020
										if (array_key_exists($col."_exp", $this->tabDef[$tbl])) {
											$this->BUFFER[$row][$act][$tbl][$col."_exp"]="";
										}//fim alteracao 01/06/2020
										$vlr = tratadouble($vlr);
									}
									break;

								case 'decimal':
									$vlr = tratanumero($vlr);
									if (!is_numeric($vlr)and(!empty($vlr)) and $vlr!="NULL"){
										return $this->err("Coluna [".$col."] [".$type."] da Tabela [".$tbl."] contém caracteres inválidos: [".$vlr."]");
									}
									break;

								case 'longtext':
									break;

								case 'text':
									break;

								default:
									return $this->err("Tipo[".$type."] da Coluna[".$col."] da Tabela [".$tbl."] não previsto #2");
									break;
							}
						}
					}
				}
			}
		}
		return true;
	}
	//@todo: Criar descricao
	function montaScriptsExecucao(){

		$this->scriptsSql = array();//Força limpeza para evitar códigos em eventcodes
		$this->scriptsSqlAuditoria = array();//Força limpeza para evitar códigos em eventcodes

		if(!is_array($this->BUFFER))return $this->err("Buffer tipo string inválido: [".$this->BUFFER."]");
		if(!is_array($this->tabDef))return $this->err("Dicionário tipo string inválido:[".$this->tabDef."]");

		//--------------------------------------- Construção dos Comandos SQL
		$strsql				= ''; #Select, Insert Update
		$strsqlcol			= ''; # (id,nome)
		$strsqlval			= ''; # (12,'marcelo')
		$strsqlcolval		= ''; # (id = 12, nome = 'marcelo')
		$strsqlcolvalwhere= ''; # (id = 12)
		$icol					= 0;
		$ipk					= 0;
		$iwhere				= 0;
		$tmptbl				= "";

		reset($this->BUFFER);

		$this->grupoCmd=rstr(8);//maf020120: Criar uma string para agrupar a auditoria, e facilitar a visualizacao dos "salvamentos consecutivos"

		$iaud = 0;

		$_modpk = retColPrimKeyTabByMod($_GET["_modulo"]);

		foreach ($this->BUFFER as $row => $rowarr) {//Linhas
			foreach ($this->BUFFER[$row] as $act => $actarr) {//Acoes
				switch ($act) {
					case "i":
						$strsql = "insert into "; break;
					case "u":
						$strsql = "update "; break;
					case "d":
						$strsql = "delete from "; break;
					default:
						return $this->err("Parâmetros [act:".$act."] para Tabela não aceito;");
				}

				$pkfld = "";
				$pkvlr = "";

				foreach ($this->BUFFER[$row][$act] as $tbl => $tblarr) {//Tabelas
					$strsql = $strsql . nomeTabela($tbl) . " ";

					//Armazenar o nome da coluna PK pra realizar a insercao de auditoria
					$pkfld = $this->tabDef[$tbl]["#pkfld"];

					//Caso seja insert, não existe valor definido. A string '#pkvalor' será substituÃída pelo insertid().
					if($act=="i"){
						$pkvlr = "#pkvalor";
					}else{
						$pkvlr = $this->BUFFER[$row][$act][$tbl][$pkfld];
					}

					foreach ($this->BUFFER[$row][$act][$tbl] as $col=>$vlr) {//Coluna
						/*
						* Montar Array com scripts de auditoria automatica para registros que estao sendo inseridos, alterados ou deletados
						* IMPORTANTE: comandos de insert estão sem o ID inserido. Sendo necessário capturar esse valor posteriormente
						*/
						if($this->tabDef[$tbl][$col]["auditar"]=="Y" or (
							$act!=="i" 
								and $this->tabDef[$tbl][$col]["primkey"]=="Y" 
								and $this->tabDef[$tbl]["#auditar"]=="Y"
						)){
							$iaud++;

							//verifica se o campo para update ou delete possui valor para poder ser inserido juntamente com cada campo que veio por POST
							if((empty($pkvlr) or $pkvlr==0) and $act != "i"){
								//maf050720: esta parte foi comentada para possibilitar que o usuario envie campos vazios mesmo que estejam sendo auditados
								//return $this->err("Erro Auditoria: O campo [".$col."] da tabela [".$tbl."] está marcado para ser auditado, mas o valor encontrado é [Vazio] ou [0].");
							}else{
								/*Armazena o script de auditoria no Array
								 * maf271211: safe_string_escape no valor a ser armazenado e tratar aspas
								 */
								$this->scriptsSqlAuditoria
											[$row]
											[$tbl]
											[$iaud] = "(".$_SESSION["SESSAO"]["IDEMPRESA"].",'".$row."','".$act."','".$tbl."',".$pkvlr.",'".$col."','".safe_string_escape($vlr)."','".$this->grupoCmd."',sysdate(),'".$_SESSION["SESSAO"]["USUARIO"]."','".$this->referer."')";
							}
						}

						//maf101220: armazenar em cache as colunas configuradas V2
						if($this->tabDef[$tbl]["#ramcache"]>=1){
							//IDPESSOA criadora
							$this->arrRamCache[$row]["_idpessoa"]=$_SESSION["SESSAO"]["IDPESSOA"];
							//Criadopor
							$this->arrRamCache[$row]["_alteradopor"]=$_SESSION["SESSAO"]["USUARIO"];
							//Criadoem
							$this->arrRamCache[$row]["_alteradoem"]=date("Y-m-d H:i:s");
							//Modulo
							$this->arrRamCache[$row]["_mod"]=$_GET["_modulo"];
							$this->arrRamCache[$row]["_idmod"]=$this->eventcodes["modulo"][getModReal($_GET["_modulo"])]["idmodulo"];
							//Armazena a acao
							$this->arrRamCache[$row]["_acao"]=$act;
							//Armazena a tabela
							$this->arrRamCache[$row]["_tab"]=$tbl;
							//Armazena a PK
							if($act!=="i" and $this->tabDef[$tbl][$col]["primkey"]=="Y"){
								$this->arrRamCache[$row]["_pk"]=$col;
								$this->arrRamCache[$row]["_pkval"]=$vlr;
							}

							$this->arrRamCache[$row]["_modpk"] = $_modpk;

							if(!empty($_modpk) && !empty($_GET[$_modpk])){
								$this->arrRamCache[$row]["_idmodpk"] = $_GET[$_modpk];
							}else if($_GET["_acao"] == 'i'){
								$this->arrRamCache[$row]["_idmodpk"] = $_SESSION['_pkid'];
							}

							if($this->tabDef[$tbl][$col]["ramcache"]=="Y" and $this->tabDef[$tbl][$col]["primkey"]!=="Y"){
								$this->arrRamCache[$row]["_cols"][$col]=evaltipocoldb($tbl, $col, $this->tabDef[$tbl][$col]["type"], $vlr, "");
							}
						}

						//maf310720: compartilhar dados de CQRS com outras plataformas
						if($act!=="i" and $this->tabDef[$tbl][$col]["primkey"]=="Y" and $this->tabDef[$tbl]["#auditar"]=="Y"){
							$this->arrCQRSCarbon[$row]["_idobj"]=$vlr;
						}					
						if($this->tabDef[$tbl]["#auditar"]=="Y" 
								and $this->tabDef[$tbl][$col]["auditar"]=="Y" 
								and !preg_match('/criadopor|criadoem|alteradopor|alteradoem/', $col)
							or ($act=="d" 
									and $this->tabDef[$tbl][$col]["primkey"]=="Y" 
									and $this->tabDef[$tbl]["#auditar"]=="Y")
						){
							$this->arrCQRSCarbon[$row]["_r"][$col]=$vlr;//O valor deve ser escapado no destino
							$this->arrCQRSCarbon[$row]["_acao"]=$act;
							$this->arrCQRSCarbon[$row]["_tab"]=$tbl;
							$this->arrCQRSCarbon[$row]["_a"]["idempresa"]=$_SESSION["SESSAO"]["IDEMPRESA"];
							$this->arrCQRSCarbon[$row]["_a"]["grupo"]=$this->grupoCmd;
							$this->arrCQRSCarbon[$row]["_a"]["cmdem"]=date("Y-m-d H:i:s");
							$this->arrCQRSCarbon[$row]["_a"]["cmdpor"]=$_SESSION["SESSAO"]["USUARIO"];
							$this->arrCQRSCarbon[$row]["_a"]["tela"]=$this->referer;
							$this->arrCQRSCarbon[$row]["_a"]["row"]=$row;
						}

						$tmptbl = $tbl;

						$inc  = $this->tabDef[$tbl][$col]["autoinc"];
						$null = $this->tabDef[$tbl][$col]["null"];
						$pk   = $this->tabDef[$tbl][$col]["primkey"];
						$type = $this->tabDef[$tbl][$col]["type"];

						$colaspa = chr(96).$col.chr(96);//Backticks (`) para evitar que nomes de colunas causem erro de sintaxe sql

						if((($act=="i")and($inc == "N"))or(($act=="u")and($pk != "Y")and(($vlr == 0) or ($vlr <> "")))){
							//Primeiro parentese '('
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
							//Primeiro parentese '('
							if($ipk == 0){
								$strsqlcolvalwhere = "(";
							}else{
								$strsqlcolvalwhere = $strsqlcolvalwhere . " and ";
							}
							$strsqlcolvalwhere = $strsqlcolvalwhere . $colaspa . " = " .evaltipocoldb($tbl, $col, $type, $vlr);

							$ipk++;
						}elseif(($act=="d")and($pk == "Y")and(!empty($vlr))){
							//Primeiro '('
							if($iwhere == 0){
								$strsqlcolvalwhere = "(";
							}else{
								$strsqlcolvalwhere = $strsqlcolvalwhere . " and ";
							}
							$strsqlcolvalwhere = $strsqlcolvalwhere . $colaspa . " = " .evaltipocoldb($tbl, $col, $type, $vlr);

							$ipk++;
							$iwhere++;
						}elseif(($act=="u")and($pk == "Y")and(empty($vlr))){
							return $this->err("O valor para a coluna [".$col."] PK deve ser informado para Update!",__LINE__,__METHOD__);
						}
					}//while coluna

					$strsqlcol         = $strsqlcol .")";
					$strsqlval         = $strsqlval . ")";
					$strsqlcolval      = $strsqlcolval . " ";
					$strsqlcolvalwhere = $strsqlcolvalwhere . ")";
				}

				$this->scriptsSql[$row][$tmptbl]["rstr"]=$this->grupoCmd;

				switch ($act){
					case "i";
						$this->scriptsSql[$row][$tmptbl]["script"] = $strsql . " " . $strsqlcol . " values " . $strsqlval;
						$this->scriptsSql[$row][$tmptbl]["acao"] = "i";
						break;
					case "u";
						$this->scriptsSql[$row][$tmptbl]["script"] = $strsql . " set " . $strsqlcolval . " where " . $strsqlcolvalwhere;
						$this->scriptsSql[$row][$tmptbl]["acao"] = "u";
						break;
					case "d";
						$this->scriptsSql[$row][$tmptbl]["script"] = $strsql . " where " . $strsqlcolvalwhere;
						$this->scriptsSql[$row][$tmptbl]["acao"] = "d";
						break;
					default:
						break;
				}

				// Verifica se alguma clausula where esta incompleta
				if((($act=="u") or ($act=="d")) and ($ipk == 0)){
					$this->err("<br>Nenhuma coluna PK da tabela [".$tmptbl."] foi enviado por POST.\n<br>Para UPDATE ou DELETE é necessário que os valores da PK da tabela a ser salva estejam na página.\n<br>O comando SQL está incompleto!\n<br><br>");
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
		}
		return true;
	}
	//descricao
	function executaScripts(){

		//verifica se a pk foi recuperada
		$pkok = false;

		if(!is_array($this->scriptsSql)) return $this->err("Pacote de Scripts inválido: " . var_export($this->scriptsSql,true));

		unset($this->pkid);
		unset($this->pkfld);
		$arrScriptBulkAud=array();

		//Executa os scripts gerados
		foreach ($this->scriptsSql as $irow=>$arrtab) {
			foreach ($arrtab as $tab=>$script) {
				//debug
				if($this->debugSql)syslog(LOG_DEBUG,$script["script"]);

				$_insertidaud = null;
				
				if($script["acao"]=="i"){
					$result = d::b()->ins($script["script"]);
				}elseif($script["acao"]=="u"){
					$result = d::b()->upd($script["script"]);
				}elseif($script["acao"]=="d"){
					$result = d::b()->del($script["script"]);
				}else{
					return $this->err("Ação não prevista!",__LINE__,__METHOD__);
				}

				//Armazena um insertid incondicionalmente para ser utilizado para auditoria
				$_insertidaud = mysql_insert_id();

				//Devolve ao array de scripts o insertid gerado pelo banco de dados, para ser utilizado em save-pos-change
				if($script["acao"]=="i"){
					$this->scriptsSql[$irow][$tab]["insertid"]=$_insertidaud;
				}

				if (!$result) {
					
					//Verifica o raise do mysql, e buscar na tab de mensagens para mostrar ao usuario
					$_mysqlerrorno = trim(mysql_errno());
					$_mysqlerror = trim(mysql_error());
					$this->errorCode = $_mysqlerrorno;
					$arrerro = array();
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
						default:
							$_strerro ="Erro na Execução do script [".$irow."]: " . mysql_error()."\nScript:\n\n".$script["script"]."\n\nErr Number: ".mysql_errno();
						break;
					}
					return $this->err($_strerro);

				}else{

					//Recupera informacoes do campo PK da tabela em questao
					$_fldpk = $this->tabDef[$tab]["#pkfld"];
					$_arrfldpkdef = $this->tabDef[$tab][$_fldpk];

					//CacheV2: Casos de 'pk autoinc'
					if($script["acao"]=="i" and $this->tabDef[$tab]["#ramcache"]>=1){
						$this->arrRamCache[$irow]["_pk"]=$_fldpk;
						$this->arrRamCache[$irow]["_pkval"]=$_insertidaud;
					}

					/* Armazena o id gerado na primeira linha para o retorno na pagina
					 * !is_numeric está sendo utilizado para os casos em que forem enviadas requisições onde a linha é descrita por texto. Ex: [ajax] ao invés de [1] */
					if(($irow==1 or !is_numeric($irow)) and ($script["acao"]!="d")){

						if($script["acao"]=="i"){
							$this->pkfld = $_fldpk;
						}

						//casos de 'pk autoinc' e 'pk varchar'
						if(stripos($_arrfldpkdef["type"],"int")!==false and $_arrfldpkdef["autoinc"]=="Y" and $script["acao"]=="i"){
							$this->pkid = $_insertidaud;
							if($this->tabDef[$tab]["#ramcache"]>=1){
								$this->arrRamCache[$irow][$tab][$_fldpk]=$_insertidaud;
							}
							if($this->tabDef[$tab]["#auditar"]){//maf: somente tabelas com auditoria configurada
								//$this->arrCQRSCarbon[$irow]["_r"][$_fldpk]=$_insertidaud;
								$this->arrCQRSCarbon[$irow]["_idobj"]=$_insertidaud;
							}
							$pkok = true;

						/*}elseif($_arrfldpkdef["type"]=="varchar" and $_arrfldpkdef["autoinc"]=="N"){
							//maf140211: para casos de colunas varchar que são chave primaria
							$this->pkid = $this->BUFFER["1"][$script["acao"]][$tab][$_fldpk];
							$pkok = true;
						*/
						}elseif(stripos($_arrfldpkdef["type"],"int")!==false and $_arrfldpkdef["autoinc"]=="Y" and $script["acao"]=="u"){
							//Verifica o tipo do row para tentar recuperar o ID do primeiro row
							if(!is_numeric($irow)){//_x_u_tab_col
								$this->pkid = array_values($this->BUFFER)[0][$script["acao"]][$tab][$_fldpk];
							}else{//_1_u_tab_col
								$this->pkid = $this->BUFFER["1"][$script["acao"]][$tab][$_fldpk];
							}
							$pkok = true;
						}

					}else{
						$pkok = true;
					}

					/*
					* Para contemplar os casos em que nao mesma tela existem campos de varias tabelas,
					* eh necessario checar para cada tabela se existe um script de auditoria relacionado,
					* por isto esta primeira condicao inicial de count(array[] irow[] itab[])
					*/
					if(!empty($this->scriptsSqlAuditoria[$irow]) and count($this->scriptsSqlAuditoria[$irow][$tab])>0){
						//Monta script INSERT em modo BULK para auditoria.
						$scriptauditbulk = "INSERT INTO `_auditoria` (idempresa,linha,acao,objeto,idobjeto,coluna,valor,grupo,criadoem,criadopor,tela) VALUES";
						$bulkvirgula = "";
						foreach ($this->scriptsSqlAuditoria[$irow][$tab] as $irowaud=>$sqlaudit) {//array de scripts
							/* Em caso de script de Insert, os scripts de auditoria neste ponto ainda não contém o INSERT_ID do Mysql
							 * Isto é colocado neste ponto para cada script de insert executado substituindo o campo do IDOBJETO da tabela [auditoria] */
							if($script["acao"]=="i"){
								$sqlaudit = str_replace("#pkvalor",$_insertidaud,$sqlaudit);
							}
							$scriptauditbulk .= "\n ".$bulkvirgula.$sqlaudit;
							$bulkvirgula = ",";
						}
						//Armazena script INSERT em modo BULK para auditoria
						$arrScriptBulkAud[]=$scriptauditbulk;
					}

					//maf271211: Executa codigos SAVEPOSCHANGE da TABELA após a execução dos scripts
					$tab_saveposchange = _CARBON_ROOT."eventcode/tab/".$tab."__saveposchange";
					if (file_exists($tab_saveposchange)) {
						include_once($tab_saveposchange);
					}
				}
			}
		}//while(list($irow, $arrtab) = each($this->scriptsSql)){

		unset($this->scriptsSqlAuditoria);

		//Falha ao recuperar pk em caso de script de insert no primeiro row de execucao do buffer
		if($pkok==false){
			return $this->err("Erro ao armazenar PK!\n".mysql_error());

		}else{
			//maf271211: Executa codigos SAVEPOSCHANGE para MÓDULOS após a execução dos scripts
			if($_SESSION["arreventcode"]["modulo"][getModReal($this->_GET["_modulo"])]["evento_saveposchange"]=="Y" && !$this->disablePrePosChange){
				$arq_saveposchange = _CARBON_ROOT."eventcode/modulo/saveposchange__".getModReal($this->_GET["_modulo"]).".php";
				if (file_exists($arq_saveposchange)) {
					include_once($arq_saveposchange);
				}else{
					return $this->err("Erro cbpost: Módulo configurado com evento saveposchange, mas houve falha ao abrir o arquivo:\n\n ".$arq_saveposchange." \n\nAltere permissões do arquivo, ou verifique evento existente no DB.");
				}
			}

			//Verifica se o commit devera ser executado. Caso se esteja dentro de um evento, haverá uma pilha de chamadas ao método CB->save
			$inest=0;
			foreach(debug_backtrace() as $i=>$b){
				if($b["class"]=="cmd" && $b["function"]=="save"){
					$inest++;
				}
			}
			if($inest==1){//Atingiu, após includes sucessivos, a 1ª instância do save (superior)
				$rcomm=d::b()->endCommit();
				if(!$rcomm) return $this->err("Erro ao efetuar Commit!".mysql_error(),__LINE__,__METHOD__);
			}
			
			//Grava os dados em uma chave específica do Redis. O redis vai aceitar os dados, e assim liberamos sucesso para o client Http do PHP
			//Isto faz com que os dados sejam disparados, pelo redis, para o canal PUB/SUB, que deve estar ativo pela configuracao
			//https://redis.io/topics/pubsub
			if(count($this->arrRamCache)>0){
				re::dis()->hMSet(
						'_queue_cache:'.rstr(8)
						, ["#data"=>json_encode($this->arrRamCache)]);
			}else{
				//@todo: Tratar casos de cache configurado sem nenhum valor neste ponto
			}


			//maf310720: enviar cada um dos scripts de auditoria para as plataformas integradas
			foreach ($this->arrCQRSCarbon as $row => $aud) {
				if(empty($aud["_a"]))continue;//Nao enviar para o redis quando nao houver no minimo 1 coluna sendo auditada
				re::dis()->hMSet(
					'_aud:'.$this->grupoCmd."_".$row
					,[
						"#j"	=>	json_encode($aud),
						"#post" => 	json_encode($_POST)
					]
				);
			}

			//Após o commit, executar os scripts de auditoria. Isto deve ser colocado fora da TRANSAÇÃO, para permitir ignorar o log binário
			logBinario(false);
			foreach ($arrScriptBulkAud as $irowaud=>$sqlaudit) {//array de scripts
				$raud = d::b()->ins($sqlaudit);
				if(!$raud){
					return $this->err("Erro na Execução do script bulk de Auditoria[".$irow."]: " . mysql_error() ."\n\nScript:\n".$scriptauditbulk."\nErr Number:\n".mysql_errno());
					return false;
				}
			}
			logBinario(true);
			return true;
		}
	}
}
?>