<?
require_once("../inc/php/validaacesso.php");

session_cache_expire(1);
session_cache_limiter("private");

//Impede pesquisa de usuários autenticados via Token
if($_SESSION["SESSAO"]["TOKEN"]===true){
	erroacesso($tipo="img/lock16.png","Pesquisa não permitida. Entre em contato com o Administrador.",true,"Erro de acesso");
}

//Requisições jquery/ajax por default enviam este parà¢metro para evitar cache. Isto impede que seja processado pelo carbon
unset($_GET["_"]);

//Inspecionar SQL
//print_r($_SESSION["SESSAO"]);
$_inspecionar_sql = ($_GET["_inspecionar_sql"]=="Y")?true:false;

//Inspecionar (mostrar) todas as Colunas da Consulta independentemente se foram marcadas como visres=N
$_mostrartodascolunas = ($_GET["_mostrartodascolunas"]=="Y")?true:false;

$_modulo = $_GET["_modulo"];

#################################################### Recupera Parametros gerais do Modulo
$_arrModConf = retArrModuloConf($_modulo);
//print_r($_arrModConf);die;

$_arrModConf["limite"] = (empty($_arrModConf["limite"]))?100:$_arrModConf["limite"];

if(empty($_arrModConf["chavefts"])){
	die("Coluna Chave para Full Text Search não configurada no Módulo.<br><a href='javascript:janelamodal(\"?_modulo=_modulo&_acao=u&modulo=".$_modulo."\")'>Ajustar</a>");
}

#################################################### Recupera a definicao dos campos da view ou table default do modulo para resultados da pesquisa

$arrFiltros = retArrModuloConfFiltros($_modulo); 
//print_r($arrFiltros);die;

$_arrpagpsq = $arrFiltros["tabela"];
$_arrcoldata = $arrFiltros["coldata"];
		
if(sizeof($arrFiltros["parget"])==0){
	die("Nenhuma coluna foi configurada para ser parâmetro GET. Ajustar os campos no Módulo");
}

if (!empty($_GET)){

	/*
	 *maf071212: armazenar as partes da consulta em um array para facilitar concatenações de novas cláusulas
	 */
	{
		unset($_SESSION["SEARCH"]);
		$_SESSION["SEARCH"]["SELECT"][] = "*";//Inicializa o SELECT
		$_SESSION["SEARCH"]["FROM"][] = nomeTabela($_arrModConf["tab"])." a ";//Inicializa clausula FROM default da tabela para pesquisa do módulo
		$_SESSION["SEARCH"]["WHERE"] = array();//As clausulas WHERE serao preenchidas conforme parametros GET, [_autofiltro] ou [_fts]
		if(trim($_arrModConf["orderby"])!="") $_SESSION["SEARCH"]["ORDERBY"][] = $_arrModConf["orderby"];//Inicializa *ORDER BY*
	}

	//maf160211: Multi Empresas sempre concatenar o IDEMPRESA
	//maf160311: Excluir pagina de search para mtotabcol
	$arrbypassempresa = retbypassidempresa();
	//print_r($arrbypassempresa);die;
	//die($_modulo);
	if(in_array($_arrModConf["tab"],$arrbypassempresa)){
		null;//Nao adiciona o campo IDEMPRESA nas clausulas where
	}else{

		if(empty($_SESSION["SESSAO"]["IDEMPRESA"]) and $_SESSION["SESSAO"]["FULLACCESS"]!="Y"){
			//print_r($_SESSION["SESSAO"]);
			die("_modfiltrospesquisa[l:".__LINE__."]: idempresa vazio.");
		}else{
		//	$_SESSION["SEARCH"]["WHERE"]["idempresa"] = " 1 ".getidempresa('idempresa',$_arrModConf["tab"]);
			$_SESSION["SEARCH"]["WHERE"]["idempresa"] = " 1 ".getidempresa('idempresa',$_modulo);
		}
	}
        
        
     
        //se o usuario tiver contato e na consulta constar idpessoa so se conseque
        //vizualizar dados de contatos relacionados
        if(array_key_exists("STRCONTATOCLIENTE", $_SESSION["SESSAO"]) and array_key_exists("idpessoa", $_arrpagpsq[$_arrModConf["tab"]])){
            $_SESSION["SEARCH"]["WHERE"]["idpessoa"] = " idpessoa in( ".$_SESSION["SESSAO"]["STRCONTATOCLIENTE"].",".$_SESSION["SESSAO"]["IDPESSOA"].") ";
        }

	//print_r($_GET); //die();
	//print_r($_arrpagpsq[$_arrModConf["tab"]]["_psqreqdefault"]);
	//print_r($_GET); die();

	reset($_GET);

	/* ************************
	 * Filtros Rápidos:
	 * 
	 * Conforme configuração do módulo, algumas colunas poderão ser selecionadas na tela
	 * Ex: Status ou idtipo[pessoa|amostra|nf|etc...]
	 * O que o usuário selecionar na tela será enviado para para compor a consulta normalmente (não fará parte do FullTextSearch ou FulldateSearch)
	 */
	//$_filtrosrapidos = array();
	//Preparar para armazenar as preferàªncias. @todo: melhorar para executar somente 1 comando
	userPref("d", $_modulo."._filtrosrapidos");
	userPref("u", $_modulo."._filtrosrapidos", null);
	if(!empty($_GET["_filtrosrapidos"])){
		$_arrtmp = json_decode($_GET["_filtrosrapidos"],true);

		//Loop nas chaves para pesquisa
		while (list($_col, $_val) = each($_arrtmp)){
			//armazena para uso posterior
			//$_filtrosrapidos[$_col]=$valor;

			//verifica se participará do where
			if(!empty($_val)){
				//Os parametros devem ser colocados na variavel GET, para serem tratados de uma vez abaixo (varchar,int etc..)
				$_GET[$_col]=$_val;
				//Armazena as preferàªncias
				userPref("u", $_modulo."._filtrosrapidos.".$_col, $_val);
			}
		}
	}
	
	if(!empty($_GET["_registrosentre"])){
		$_arrtmp = json_decode($_GET["_registrosentre"],true);

		//Loop nas chaves para pesquisa
		while (list($_col, $_val) = each($_arrtmp)){
			//armazena para uso posterior
			//$_filtrosrapidos[$_col]=$valor;

			//verifica se participará do where
			if(!empty($_val)){
				//Os parametros devem ser colocados na variavel GET, para serem tratados de uma vez abaixo (varchar,int etc..)
				$_GET[$_col]=$_val;
				//Armazena as preferàªncias
				userPref("u", $_modulo."._registrosentre.".$_col, $_val);
			}
		}
	}

	/* ************************
	 * Full Text search
	 * 
	 * Realizar pesquisa em bancos de dados internos ou externos de FULL TEXT SEARCH, e permitir pesquisas booleanas
	 * Ela retorna os IDs da tabela (informada no módulo) para serem utilizados em cláusula 'in'
	 * 
	 */
	$strPkFts;
	$arrFk=array();
	$countArrFk=null;
	
	//Verifica se a PK é algum tipo de char, para colocar aspas nos elementos da cláusula in
	$aspa = (strpos($_arrpagpsq[$_arrModConf["tab"]][$_arrModConf["chavefts"]]["datatype"],"char"))?"'":"";
	
	if(!empty($_GET["_fts"])){
		//Ajusta preferencias do usuario
		userPref("u", $_modulo."._fts", $_GET["_fts"]);
		
		$arrFk = retPkFullTextSearch($_arrModConf["tab"], $_GET["_fts"]/*, $_GET["_pagina"],$_arrModConf["limite"]*/);

		$countArrFk=$arrFk["foundRows"];
		if($countArrFk>0){
			//$strPkFts = implode(",", $arrFk["arrPk"]);
			$strPkFts = $aspa . implode(($aspa.",".$aspa), $arrFk["arrPk"]) . $aspa;
			$_SESSION["SEARCH"]["WHERE"][] = $_arrModConf["chavefts"] . " in (".$strPkFts.")";
		}
	}

	/* ************************
	 * Date search
	 * 
	 * Realizar pesquisa em bancos de dados internos em colunas de tipo date/datetime
	 */
	$_strwherefds;
	if(!empty($_GET["_fds"])){
		if(sizeof($_arrcoldata)==0){
			//cbSetPostHeader("0","alert");
			//die("Nenhuma coluna de data foi configurada para pesquisa neste Módulo. \nNão informe nenhuma data no calendário.");
		}else{
			//ajusta preferencias do usuario
			userPref("u", $_modulo."._fds", $_GET["_fds"]);
			
			$arrdatas = explode("-", $_GET["_fds"]);
			$arrdatas[0] =  validadate($arrdatas[0])." 00:00";
			$arrdatas[1] =  validadate($arrdatas[1])." 23:59:59";
	
			//Loop nas colunas de tipo date/datetime
			while(list($i,$col) = each($_arrcoldata)){
				$_strwherefds .= $stror.$col." between '".$arrdatas[0]."' and '".$arrdatas[1]."'";
				$stror = "\n or ";
			}
			$_SESSION["SEARCH"]["WHERE"][] = "(".$_strwherefds.")";
		}
	}else{
		userPref("d", $_modulo."._fds");
	}

	/* ***********************
	 * Pesquisa parametrizada por colunas
	 * Realizar a pesquisa obedecendo as colunas e valores enviados via get
	 * 
	 */
    $iget=0;//Verificar se parametros get validos foram enviados
	while (list($_key, $_val) = each($_GET)) {
		
			
				
		$_between = false;
		$_aval=false;
		$_ischar=false;
			
		

		//Não processar como "colunas para Where" parametros do carbon, que sao iniciados por "_"
		if($_val!="" and $_key!="btnsubmitform" and substr($_key,0,1)!="_" and substr($_key,-2)!="_2"){
			//print_r($_arrpagpsq); echo "<br>1</br>";//die();

			if (substr($_key,-2)=="_1"){
				$_key = substr($_key,0,-2); //Transforma do nome do campo para capturar informacoes de tipo
				$_keyval1 = $_GET[$_key."_1"];
				$_keyval2 = $_GET[$_key."_2"];
				$faixa = $_keyval2 - $_keyval1;
				if ( $faixa >= 1000 or empty($_keyval1) or empty($_keyval2) ){
				
				 cbSetPostHeader("0","alert");
				die("Intervalo de pesquisa acima do limite permitido (1.000 registros).");
					
				}
				$_between = true;
				$_prompt = 'between';
			}elseif(substr($_val,0,1)=="[" and substr($_val,-1,1)=="]"){

				//Remove os brackets
				$_aval=explode(",",str_replace("[","",str_replace("]","",$_val)));

				//Verifica se todos os parametros sao numericos
				foreach($_aval as $k=>$v){
					//Remove caracteres invalidos
					$v=str_replace("\"","",str_replace("'","",$v));
					$_aval[$k]=d::b()->escape_string($v);
					$_ischar=!is_numeric($v)?true:false;
				}
				//Se houver alguma string nao-numerica, deve-se colocar aspas em todos os elementos
				if($_ischar){
			        	foreach($_aval as $k=>$v){
                                        	//Força aspas
						$_aval[$k]="'".$v."'";
                                	}
				}

				$_prompt = "in";

			}else{

				$_prompt = $_arrpagpsq[$_arrModConf["tab"]][$_key]["prompt"];
				$_datatype = $_arrpagpsq[$_arrModConf["tab"]][$_key]["datatype"];
			}
			
			//Testa e informa uma clausula adicional de HORARIO caso este não exista, no caso de campos datetime. Isto evita que o between ignore registros com hora maior que 00:00:00 
			if(($_datatype == "datetime") and $_between){
                $_keyval2 = str_replace("T"," ",$_keyval2);//maf031213: considerar campos html datetime
                $tmparrdtime = explode(" ",$_keyval2);
                
    			if(count($tmparrdtime)>2){
        			echo "\nErro validadatetime: Mais de um espaco foi informado na data enviada [".$indata."]";
        			return false;
        		}elseif(count($tmparrdtime)<2){//maf031213: caso a hora nao seja enviada, adicionar a string de hora para o between
        			$_keyval2 = $_keyval2." 23:59:59";
        		}
			}

			switch ($_prompt):
				case "=": //Igual a
					$_SESSION["SEARCH"]["WHERE"][$_key] = $_key . " = " . evaltipocoldb($_arrModConf["tab"], $_key, $_datatype, $_val);
			        	$iget++;
					break;
				case "in":
					//Trata-se de parametro enviado como array. Ex: [123,456,789,ATIVO,INATO]
					if($_aval){
                                        	$_SESSION["SEARCH"]["WHERE"][$_key] = $_key . " in (" . implode($_aval,",").")";
					}
					break;
				case "?%": //Comeca com
					$_SESSION["SEARCH"]["WHERE"][$_key] = $_key . " like '" . $_val . "%'";
					$iget++;
					break;
				case "%?%": //Contem
					$_SESSION["SEARCH"]["WHERE"][$_key] = $_key . " like '%" . $_val . "%'";
					$iget++;
					break;
				case "between": //Entre
					$_SESSION["SEARCH"]["WHERE"][$_key] = "(" . $_key . " between " . evaltipocoldb($_arrModConf["tab"], $_key, 'varchar', $_keyval1) . " and " . evaltipocoldb($_arrModConf["tab"], $_keyval2, 'varchar', $_keyval2) . ")";
					$iget++;
					break;
				case "json": //Multipla escolha
				    /*
				     * Os valores serao enviados separados por virgula, conforme o plugin select2 jquery
				     * Neste ponto serao encaixados em clausula [=] ou [in]
				     */
				    $arrMultEsc = explode(",", $_val);
				    $iMultEsc = sizeof($arrMultEsc);
				    
				    //echo($iMultEsc."\n");print_r($arrMultEsc);//die;
				    if($iMultEsc==1){
				        //Foi selecionada somente 1 opcao
				        $_SESSION["SEARCH"]["WHERE"][$_key] = $_key . " = " . evaltipocoldb($_arrModConf["tab"], $_key, $_datatype, $_val);
				        $iget++;
				    }elseif($iMultEsc>1){
				        $strin = $_key . " in (";
				        $virg="";
				        foreach($arrMultEsc as $i => $valor){
				            $strin .= $virg . evaltipocoldb($_arrModConf["tab"], $_key, $_datatype, $valor);
				            $virg=",";
				        }
				        $strin .= ")";
				        $_SESSION["SEARCH"]["WHERE"][] = $strin;
				        $iget++;
				    }
					break;
				case "jsonpicker": //Multipla escolha
				    /*
				     * Os valores serao enviados separados por virgula, conforme o plugin select2 jquery
				     * Neste ponto serao encaixados em clausula [=] ou [in]
				     */
				    $arrMultEsc = explode(",", $_val);
					//var_dump($arrMultEsc);
					//die();

					$strin = " CONCAT(',', `".$_key."`, ',') REGEXP ',(";
					$virg="";
					foreach($arrMultEsc as $i => $valor){
						$strin .= $virg.$valor;
						$virg="|";
					}
					$strin .= "),'";
					//die($strin);
					$_SESSION["SEARCH"]["WHERE"][] = $strin;
					$iget++;
					break;
				default:
					echo ("Informação de Operador default para clausula [WHERE][".$_arrModConf["tab"]."][".$_key."] n&atilde;o foi encontrado nas defini&ccedil;&otilde;es do Módulo [". $_modulo."]");
					echo ("\nPossibilidade 1: Configurar operador 'prompt': [" . $_prompt . "]");
					echo ("\nPossibilidade 2: Parâmetro GET informado deve ser colocado como exceção para não ser tratado como coluna: [" . $_key . "=".$_val."]");
					//echo "<!-- \n";print_r($_arrpagpsq);echo" \n-->";
					die();
			endswitch;

		}
		
		//Processar configuracoes opcionais que foram enviadas pela configuracao da funcao de CB.pesquisar({}) no javascript
		if($_val!="" and $_key!="btnsubmitform" and substr($_key,0,1)=="_"){
			if($_key=="_ordcol" && !empty($_GET["_orddir"]) && ($_GET["_orddir"]=="asc" or $_GET["_orddir"]=="desc")){
				//Ajusta preferencias do usuario
				userPref("u", $_modulo.".orderby", null);
				userPref("u", $_modulo.".orderby._ordcol", $_GET["_ordcol"]);
				userPref("u", $_modulo.".orderby._orddir", $_GET["_orddir"]);
				
				unset($_SESSION["SEARCH"]["ORDERBY"]);
				
				//Está sendo utilizado um recurso que quebra à­ndices, para colocar valores vazios por último na ordenação
				//$_SESSION["SEARCH"]["ORDERBY"][] = $_val." ".$_GET["_orddir"];
				$_SESSION["SEARCH"]["ORDERBY"][] = "if(ifnull(".$_val.",'') = '',1,0),".$_val." ".$_GET["_orddir"];
			}
		}
		
	}//while (list($_key, $_val) = each($_GET)) {

//die("psqfull:".$_arrModConf["psqfull"]."iget:".$iget);
	//print_r($_SESSION["SEARCH"]);die;
	
	if(empty($_arrModConf["psqfull"]) and $iget==0){
	    cbSetPostHeader("0","erro");
		die("Módulo não configurado corretamente: Informe configuração para pesquisa Full.");
		
	}elseif($_arrModConf["psqfull"]=="N" and $iget==0 and $arrFk["foundRows"]==0 and empty($_GET["_fts"]) and empty($_GET["_fds"])){
	    cbSetPostHeader("0","alert");
		die("Informe um parâmetro para a pesquisa!");
		
	}elseif($_arrModConf["psqfull"]=="Y" and $iget==0 and $arrFk["foundRows"]==0 and !empty($_GET["_fts"]) and empty($_GET["_fds"])){
		cbSetPostHeader("0","alert");
		die("Nenhum registro encontrado!");
	}elseif($_arrModConf["psqfull"]=="N" and $arrFk["foundRows"]==0 and (!empty($_GET["_fts"]))){
		if($_inspecionar_sql){
			echo("SQL INCOMPLETO: ".$_SEARCHSQL);
		}
		die("{}");
	}

	/*
	 * Paginação
	 * Em casos onde a paginação não é informada, recuperar somente a primeira página para evitar consultas sem limit
	 */
	$_GET["_pagina"] = (empty($_GET["_pagina"]) and $_GET["_pagina"]!=="0") ? 1 : $_GET["_pagina"];
	
	if($_GET["_pagina"]!="0"){
		$_pageOffset=intval($_GET["_pagina"])-1;
		$_pageOffset=$_pageOffset."00";
		$_pageOffset=intval($_pageOffset);
		$clauLimit = $_pageOffset.",".$_arrModConf["limite"];
		$_SESSION["SEARCH"]["LIMIT"][]=$clauLimit;
	}
	/***************************************************************************************************************
	 * Executa PRE SEARCH EXEC
	 ***************************************************************************************************************/
	$arq_presearchexec = "../eventcode/modulofiltrospesquisa/presearchexec__".getModReal($_modulo).".php";

	if (file_exists($arq_presearchexec)) {


		include_once($arq_presearchexec);

	}


	/*
	 * Monta o SQL de acordo com os arrays
	 * @todo: Melhorar a performance, fazendo union com a FTS. Ex:
				SELECT a.*
			   FROM carbonnovo._fts f join vwcliente_visualizarresultados a on (
				   a.idresultado = f.fk
				   and (dataamostra between '2015-06-16 00:00' and '2016-06-16 23:59:59')
			   )
			   WHERE f.tab = 'vwcliente_visualizarresultados' 
			   and MATCH(f.conteudo) against ('+2016' IN BOOLEAN MODE)
			   limit 0,100
	 */
	//print_r($_SESSION["SEARCH"]);
	$_SEARCHSQL = montaSearchFiltrosPesquisa($_SESSION["SEARCH"], "SQL_CALC_FOUND_ROWS");
	
	
	//die($_SEARCHSQL);
	//Envia o SQL no header para fins de debug rápido
	if($_inspecionar_sql){
		die($_SEARCHSQL);
	}

	//MAF271119: Isola a transacao para 'dirty mode'
	d::b()->query("SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;");

	/*********************************************************************************************
	 * Executa a consulta
	 ********************************************************************************************/
	$_resultados = mysql_query($_SEARCHSQL);
	if (!$_resultados) {
		$_sqlerro = mysql_error();
		$_sqlerrno = mysql_errno();
		if($_sqlerrno==1054 and strpos($_sqlerro,"order")){
	    	die('Falha na execucao da Consulta. Coluna de ordenação inexistente: '. $_sqlerrno ." - " .$_sqlerro . "\n" . $_arrModConf["tab"]."\n".$_SEARCHSQL);
		}else{
	    	die('Falha na execucao da Consulta para os Resultados: '. $_sqlerrno ." - " .$_sqlerro . "\n" . $_arrModConf["tab"]."\n".$_SEARCHSQL);
	    }
	}

	//Recupera a quantidade total de registros desconsiderando a cláusula LIMIT ***Não está sendo utilizado: considera-se a quantidade de registros encontrados pela FTS 
	$foundRows = myFoundRows();
	
	$_arrtab = retarraytabdef($_arrModConf["tab"]);

    $_i = 0;
    $_numcampos = mysql_num_fields($_resultados);
	$_imodulores = $foundRows;

	/*
	 * Se o numero de linhas, for igual a 1, informa a pagina de pesquisa para nao setar o foco inicial nos campos de busca, mas sim no unico de resultado
	 */
	$_focares = "";
	$_strs = "";
	if($_imodulores==0){
		$_nenhumresultado = true;
	}elseif($_imodulores==1){
		$_focares = " id='objfocoinicial' ";
	}else{
		$_strs = "s";
	}

	/*
	 * Monta o array de valores para ser transformado em json e enviado ao cliente
	 */
	$ARRRESULTADOS = array();
	
	$ARRRESULTADOS["rotulo"] = htmlentities($_arrModConf["rotulomenu"]);
	$ARRRESULTADOS["numrows"] = $_imodulores;
	$ARRRESULTADOS["novajanela"] = $_arrModConf["novajanela"];
	$ARRRESULTADOS["numpaginas"] = ceil($_imodulores / $_arrModConf["limite"]);

	if($_nenhumresultado){
		die("{}");
	}

    ######################################################################################################################################### 
	# HEADER
	#########################################################################################################################################

	$icols=0;
	while ($_i < $_numcampos) {
	    $_metacmp = mysql_fetch_field($_resultados, $_i);

	    if (!$_metacmp) {
	        die("Nenhuma informacao de design retornou do SQL de Resultados");
	    }
	    //Escrever na tela os parametros de cada campo
		/*
	    echo "
		blob:         $_metacmp->blob
		max_length:   $_metacmp->max_length
		multiple_key: $_metacmp->multiple_key
		name:         $_metacmp->name
		not_null:     $_metacmp->not_null
		numeric:      $_metacmp->numeric
		primary_key:  $_metacmp->primary_key
		table:        $_metacmp->table
		type:         $_metacmp->type
		default:      $_metacmp->def
		unique_key:   $_metacmp->unique_key
		unsigned:     $_metacmp->unsigned
		zerofill:     $_metacmp->zerofill
		";*/

	    $_arridxcol[$_i] = $_metacmp->name;
	    $rottmp="";
	    if($_arrpagpsq[$_arrModConf["tab"]][$_metacmp->name]["visres"] == 'Y' or $_mostrartodascolunas==true){
	    	$rottmp=(empty($_arrtab[$_metacmp->name]["rotcurto"]))?$_metacmp->name:$_arrtab[$_metacmp->name]["rotcurto"];
			$ARRRESULTADOS["cols"][$_metacmp->name] = htmlentities($rottmp);
			$icols++;
	    }
	    $_i++;
	}

	if($icols==0){
		echo "Nenhuma coluna marcada como visà­vel na configuração do Módulo.";
	}
    $ARRRESULTADOS["numcols"] = $icols;

    $_arrhlcond = retsearchrescond($_modulo, false);
	$_ilinha = 0;
	//print_r($_arrhlcond);
		
    #########################################################################################################################################
    # ROWS
    #########################################################################################################################################
    while ($_row = mysql_fetch_array($_resultados)){
	
		$_ilinha++;

		//Inicia a montagem dos parametros GET
    	$_i = 0;

    	//echo $_numcampos; die();
    	while ($_i < $_numcampos) {

    		$_nomecol = $_arridxcol[$_i];
    	    if(in_array($_nomecol,$arrFiltros["parget"])){
				//Chamada de pagina por GET
                $ARRRESULTADOS["rows"][$_ilinha]["parget"][$_nomecol] = htmlentities($_row[$_i]);
	    	}
	    	$_i++;
    	}

    	$_i = 0;

if($_SESSION["SESSAO"]["USUARIO"]=='marcelo' && _inspecionar_sql){
       // print_r($_row);
}
        //print_r($_arrhlcond[$_modulo][$_metacmp->name]);
        //echo "[". $_metacmp->name. "]";

        /*
         * Cores condicionais para colorir a linha de resultado
    	 * O loop irá passar por todas as linhas encontradas
    	 * Logo, valores MENORES no campo ord da tabela highlight sempre terão prioridade
         */

    	$_tmphlcolor = "";
    	$_strhlcolor = "";

	   $_boocond = false;
	   //print_r($_arrhlcond[$_modulo]);

        if(!empty($_arrhlcond[$_modulo])){

			if($_SESSION["SESSAO"]["USUARIO"]=='marcelo' && _inspecionar_sql){
			        //print_r($_arrhlcond[$_modulo]);
			}

            foreach ($_arrhlcond[$_modulo] as $_fldcond => $_cond) {
        		if ($_boocond==false){

					if($_SESSION["SESSAO"]["USUARIO"]=='marcelo' && _inspecionar_sql){
							//print_r($_row);print_r($_cond);
					}

        			//echo ("<br>cond:[".$_cond["col"].$_cond['cond'].$_cond["valor1"]."]<br>");
					$msgErroHl = "Erro: Coluna [".$_cond["col"]."] configurada para highlight, mas não existe na definição de [".$_arrModConf["tab"]."]: ".$_cond["col"].": ".$_row[$_cond["col"]];
        			switch($_cond["cond"]){
        				
        				case "=":
							//if(!isset($_row[$_cond["col"]]))die($msgErroHl);//maf: retirado em 251017, pois caso o valor fosse vazio estava disparando o erro
        					if ($_row[$_cond["col"]] == $_cond["valor1"]){
        						$_tmphlcolor = $_cond["cor"];
        						$_boocond = true;
        					}
        					break;
        				case "!=":
        					if ($_row[$_cond["col"]] != $_cond["valor1"]){
        						$_tmphlcolor = $_cond["cor"];
        						$_boocond = true;
        					}
        					break;
        				case ">":
        					if ($_row[$_cond["col"]] > $_cond["valor1"]){
        						$_tmphlcolor = $_cond["cor"];
        						$_boocond = true;
        					}
        					break;
        				case ">=":
        					if ($_row[$_cond["col"]] >= $_cond["valor1"]){
        						$_tmphlcolor = $_cond["cor"];
        						$_boocond = true;
        					}
        					break;
        				case "<":
        					if ($_row[$_cond["col"]] < $_cond["valor1"]){
        						$_tmphlcolor = $_cond["cor"];
        						$_boocond = true;
        					}
        					break;
        				case "<=":
        					if ($_row[$_cond["col"]] <= $_cond["valor1"]){
        						$_tmphlcolor = $_cond["cor"];
        						$_boocond = true;
        					}
        					break;
        				case "between":
        					if (($_row[$_cond["col"]] >= $_cond["valor1"]) and ($_row[$_cond["col"]] <= $_cond["valor2"])){
        						$_tmphlcolor = $_cond["cor"];
        						$_boocond = true;
        					}
        					break;
        				default:
        					break;
        			}
        		}//if ($_boocond==false){
            }//foreach ($_arrhlcond[$_modulo] as $_fldcond => $_cond) {
        }//if(!empty($_arrhlcond[$_modulo])){

        //cor do background	
    	if($_tmphlcolor){
            $ARRRESULTADOS["rows"][$_ilinha]["bgcolor"] = $_tmphlcolor;
	    }

	    //Armazenar uma string simples com um resumo dos 2 primeiros campos para navegacao por javascript
	    $trNavegacao="";
	    $iNav=0;
	    
	    //Colunas da linha
    	while ($_i < $_numcampos) {
    		$_nomecol = $_arridxcol[$_i];
    		$valuetd = "";
            //Se o campo estiver marcado como visà­vel
    		if($_arrpagpsq[$_arrModConf["tab"]][$_nomecol]["visres"] == 'Y' or $_mostrartodascolunas==true){

    		    $iNav++;
    		    
    			//Formata o valor do campo do DB para ser visualizado corretamente
    			$valuetd = formatastringvisualizacao($_row[$_i], $_arrpagpsq[$_arrModConf["tab"]][$_nomecol]["datatype"]);

    			$valuetd = htmlentities($valuetd);

    			/*
    			 * Bug: Algumas colunas no mysql (collate: latin1) aceitaram caracteres UTF-8
    			 * Isto obriga a que, após a conversão em HTML Entities, efetuar decode em qualquer remanescente UTF-8 
    			 * @todo: Transformar o DB e aplicação em UTF8 *asap*
    			 * https://www.blueboxcloud.com/insight/blog-article/getting-out-of-mysql-character-set-hell
    			 */
    			if(($_arrpagpsq[$_arrModConf["tab"]][$_nomecol]["datatype"]=="varchar"
    				or $_arrpagpsq[$_arrModConf["tab"]][$_nomecol]["datatype"]=="char" 
    				or $_arrpagpsq[$_arrModConf["tab"]][$_nomecol]["datatype"]=="longtext") 
    				and !empty($valuetd)){
    				//Converte caracteres remanescentes
    				//$valuetd = $valuetd;
    			}

    			if(!empty($valuetd) and ($iNav==1 or $iNav==2)){
    			    $trNavegacao = (empty($trNavegacao))?$valuetd:$trNavegacao."|".$valuetd;
    			}
				$ARRRESULTADOS["rows"][$_ilinha]["cols"][] = $valuetd;
	    	}
	    	$_i++;
    	}
    	
    	$ARRRESULTADOS["rows"][$_ilinha]["nav"] = $trNavegacao;

    }//while ($_row = mysql_fetch_array($_resultados)){
	    
	/***************************************************************************************************************
	 * Executa POS SEARCH EXEC
	 ***************************************************************************************************************/
	$arq_possearchexec = "../eventcode/modulofiltrospesquisa/possearchexec__".getModReal($_modulo).".php";	
	if (file_exists($arq_possearchexec)) {
		include_once($arq_possearchexec);
	}


    if(!empty($_arrhlcond[$_modulo])){
    
    	foreach ($_arrhlcond[$_modulo] as $_fldcond => $_cond) {
    	    $ARRRESULTADOS["legenda"][$_cond["cor"]] = acentos2ent($_cond["legenda"]);
    
    	}
    }

}

//Em caso de erro de JSON, verificar os dados existentes nesta linha:
//print_r($ARRRESULTADOS["rows"]);die;

//Transforma o array em json
$json_table = json_encode($ARRRESULTADOS);

if(json_last_error()){
    echo("_modulofiltrospesquisa: Erro ao montar Json: Código [".json_last_error()."] Erro: ".json_last_error_msg());
    //Em caso de erro de JSON, verificar os dados existentes nesta linha:
	//print_r($ARRRESULTADOS["rows"]);
    die;
}else{
    echo $json_table;
}

?>
