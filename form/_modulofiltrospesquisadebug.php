<?
require_once("../inc/php/validaacesso.php");

session_cache_expire(1);
session_cache_limiter("private");

//Impede pesquisa de usuários autenticados via Token
if($_SESSION["SESSAO"]["TOKEN"]===true){
	erroacesso($tipo="img/lock16.png","Pesquisa não permitida. Entre em contato com o Administrador.",true,"Erro de acesso");
}

//Requisiçàµes jquery/ajax por default enviam este parà¢metro para evitar cache. Isto impede que seja processado pelo carbon
unset($_GET["_"]);

//Inspecionar SQL
$_inspecionar_sql = ($_GET["_inspecionar_sql"]=="Y")?true:false;;

//Inspecionar (mostrar) todas as Colunas da Consulta independentemente se foram marcadas como visres=N
$_inspecionar_colunas = false;

$_modulo = $_GET["_modulo"];


#################################################### Recupera Parametros gerais do Modulo
$_sqlmodulo = "SELECT * 
				FROM carbonnovo._modulo m 
				where m.modulo = '". $_modulo ."'";

//echo $_sqlmodulo; die();
$_resmodulo = mysql_query($_sqlmodulo) or die("Erro consultando parametros do Mà³dulo: ".mysql_error());
$_imodulo = mysql_num_rows($_resmodulo); //Verifica se o Modulo existe
$_rowmodulo = mysql_fetch_array($_resmodulo);

if($_imodulo != 1){//Informa se a pagina de pesquisa foi encontrada
	die("O Mà³dulo [".$_modulo."] n&atilde;o foi encontrada (imodulo=[".$_imodulo."]).");
}

$_rotulomenu = $_rowmodulo["rotulomenu"];
$_urldestino = $_rowmodulo["urldestino"];
//$_pargetdefault = $_rowmodulo["pargetdefault"];
$_tab = $_rowmodulo["tab"];
$_chavefts = $_rowmodulo["chavefts"];
$_novajanela = $_rowmodulo["novajanela"];
$_novajanelamodal = $_rowmodulo["novajanelamodal"];
$_psqfull = $_rowmodulo["psqfull"];
$_limite = (empty($_rowmodulo["limite"]))?100:$_rowmodulo["limite"];
$_orderby = $_rowmodulo["orderby"];

#################################################### Recupera a definicao dos campos da view ou table default do modulo para resultados da pesquisa
$_sqlobjpesq = 
	"SELECT
		m.tab
		,mf.col
	  ,mf.psqkey
	  ,mf.psqreq
	  ,mf.psqreqdefault
	  ,mf.visres
	,mf.parget
	  ,tc.ordpos
	  ,tc.datatype
	  ,tc.primkey
	  ,tc.autoinc
	  ,tc.nullable
	  ,tc.rotcurto
	  ,tc.rotlongo
	  ,tc.rotpsq
	  ,tc.where
	  ,tc.dropsql
	  ,tc.default
	  ,tc.code
	  ,tc.codeeval
	FROM
	  _modulo m,
	  _modulofiltros mf,
	  _mtotabcol tc
	WHERE m.modulo = '". $_modulo ."'
		and mf.modulo = m.modulo
		and tc.tab = m.tab
		and tc.col = mf.col
	 ORDER BY tc.ordpos";

//echo $_sqlobjpesq; die();
$_resfiltrospsq = mysql_query($_sqlobjpesq);
$_iresfiltrospsq = mysql_num_rows($_resfiltrospsq);
if(!$_resfiltrospsq){
	die("Falha ao recuperar filtros do Mà³dulo [".$_modulo."]: ".mysql_error());
}

if($_iresfiltrospsq == 0){
	echo "<!-- ". $_sqlobjpesq. " -->";
	die("Informacao para pesquisa em [".$_rowmodulo["sqlresultado"]."] nao disponivel.&nbsp;Solu&ccedil;&otilde;es:<br>1 - Provavelmente as informacoes estao inconsistentes em [mtotabcol] para [".$_rowmodulo["sqlresultado"]."];<br>2 - A p&aacute;gina de pesquisa est&aacute; desconfigurada. E os itens da tabela de consulta devem ser ajustados;");
}


if(empty($_modulo)){
	die("Mà³dulo n&atilde;o informado!");
}

if($_iresfiltrospsq==0){
        die("Nenhuma informa&ccedil;&atilde;o de dicion&aacute;rio de dados [mtotabcol] ou indica&ccedil;&atilde;o de campos vis&iacute;veis [modulo] para a tabela [".$_rowmodulo["sqlresultado"] ."]");
}

if(empty($_chavefts)){
	die("Coluna Chave para Full Text Search não configurada no Mà³dulo.");
}

$_arrpagpsq = array();
$_arrcoldata = array();

while ($_rowpp = mysql_fetch_assoc($_resfiltrospsq)){

	if(empty($_mtotab)){
		$_mtotab = $_rowpp["tab"];
		if(empty($_mtotab)){
			die("Variável _mtotab vazia!");
		}
	}
	$_arrpagpsq[$_rowpp["tab"]][$_rowpp["col"]]["datatype"] = $_rowpp["datatype"];
	$_arrpagpsq[$_rowpp["tab"]][$_rowpp["col"]]["visres"] = $_rowpp["visres"];
	$_arrpagpsq[$_rowpp["tab"]][$_rowpp["col"]]["rotcurto"] = $_rowpp["rotcurto"];
	$_arrpagpsq[$_rowpp["tab"]][$_rowpp["col"]]["rotpsq"] = $_rowpp["rotpsq"];
	$_arrpagpsq[$_rowpp["tab"]][$_rowpp["col"]]["where"] = $_rowpp["where"];
	$_arrpagpsq[$_rowpp["tab"]][$_rowpp["col"]]["dropsql"] = $_rowpp["dropsql"];
	$_arrpagpsq[$_rowpp["tab"]][$_rowpp["col"]]["default"] = $_rowpp["default"];
	$_arrpagpsq[$_rowpp["tab"]][$_rowpp["col"]]["code"] = $_rowpp["code"];
	$_arrpagpsq[$_rowpp["tab"]][$_rowpp["col"]]["psqreq"] = $_rowpp["psqreq"];
	$_arrpagpsq[$_rowpp["tab"]][$_rowpp["col"]]["psqreqdefault"] = $_rowpp["psqreqdefault"];

	/*
	 * MAF251010: Montar um array com campos (psqreq=Y and psqreqdefault!='')
	 * Isto permite que uma view ou tabela seja reaproveitada, filtrando-se obrigatoriamente pelo filtro informado na tela de configuracao da pesquisa
	 */
	if($_rowpp["psqkey"]=='Y' 
		and ($_rowpp["psqreq"]=='Y' and !empty($_rowpp["psqreqdefault"]))){
		
		$_arrpagpsq[$_rowpp["tab"]]["_psqreqdefault"][$_rowpp["col"]] = $_rowpp["psqreqdefault"];
	}
	
	//maf300513: Monta array com os campos que servem para montagem dos parametros GET a serem passados para a url de destino
	if($_rowpp["parget"]=='Y'){
		$_arrpagpsqparget[] = $_rowpp["col"];
	}
	//Array com as colunas de date/datetime/timestamp para _fds
	if(!empty($_GET["_fds"]) and ($_rowpp["datatype"]=="date" or $_rowpp["datatype"]=="datetime" or $_rowpp["datatype"]=="timestamp")){
		$_arrcoldata[]=$_rowpp["col"];
	}
}#while ($_rowpp = mysql_fetch_array($_resfiltrospsq))

//print_r($_arrpagpsqparget);die;
if(empty($_arrpagpsqparget)){
	die("Nenhuma coluna foi configurada para ser parà¢metro GET. Ajustar os campos no Mà³dulo");
}

if (!empty($_GET)){

	/*
	 *maf071212: armazenar as partes da consulta em um array para facilitar concatenaçàµes de novas cláusulas
	 */
	{
		unset($_SESSION["SEARCH"]);
		$_SESSION["SEARCH"]["SELECT"][] = "*";//Inicializa o SELECT
		$_SESSION["SEARCH"]["FROM"][] = $_tab." a ";//Inicializa clausula FROM default da tabela para pesquisa do mà³dulo
		$_SESSION["SEARCH"]["WHERE"] = array();//As clausulas WHERE serao preenchidas conforme parametros GET, [_autofiltro] ou [_fts]
		if(trim($_orderby)!="") $_SESSION["SEARCH"]["ORDERBY"][] = $_orderby;//Inicializa *ORDER BY*
	}

	//maf160211: Multi Empresas sempre concatenar o IDEMPRESA
	//maf160311: Excluir pagina de search para mtotabcol
	$arrbypassempresa = retbypassidempresa();
	//print_r($arrbypassempresa);die;
	//die($_modulo);
	if(in_array($_tab,$arrbypassempresa)){
		null;//Nao adiciona o campo IDEMPRESA nas clausulas where
	}else{

		if(empty($_SESSION["SESSAO"]["IDEMPRESA"]) and $_SESSION["SESSAO"]["FULLACCESS"]!="Y"){
			//print_r($_SESSION["SESSAO"]);
			die("_modfiltrospesquisa[l:".__LINE__."]: idempresa vazio.");
		}else{
			$_SESSION["SEARCH"]["WHERE"]["idempresa"] = "idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"];
		}
	}

	//print_r($_GET); //die();
	//print_r($_arrpagpsq[$_mtotab]["_psqreqdefault"]);
	//print_r($_GET); die();

	/*
	 * MAF251010: combina array de campos que vieram por post com os campos (psqreq=Y and psqdefault!='') para efetuar clausulas padrao na consulta a ser montada
	 */
	if(!empty($_arrpagpsq[$_mtotab]["_psqreqdefault"])){
		//echo "<!-- Valores default: \n";print_r($_arrpagpsq[$_mtotab]["_psqreqdefault"]);echo" -->";
		$_GET = $_GET + $_arrpagpsq[$_mtotab]["_psqreqdefault"];
	}

	reset($_GET);

	/* ************************
	 * Filtros Rápidos:
	 * 
	 * Conforme configuração do mà³dulo, algumas colunas poderão ser selecionadas na tela
	 * Ex: Status ou idtipo[pessoa|amostra|nf|etc...]
	 * O que o usuário selecionar na tela será enviado para para compor a consulta normalmente (não fará parte do FullTextSearch ou FulldateSearch)
	 */
	//$_filtrosrapidos = array();
	unset($_SESSION["SESSAO"]["PREFERENCIAS"][$_modulo]["_filtrosrapidos"]);
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
				$_SESSION["SESSAO"]["PREFERENCIAS"][$_modulo]["_filtrosrapidos"][$_col] = $_val;
			}else{
				//unset($_SESSION["SESSAO"]["PREFERENCIAS"][$_modulo]["_filtrosrapidos"][$_col]);
			}

		}
	}

	/* ************************
	 * Full Text search
	 * 
	 * Realizar pesquisa em bancos de dados internos ou externos de FULL TEXT SEARCH, e permitir pesquisas booleanas
	 * Ela retorna os IDs da tabela (informada no mà³dulo) para serem utilizados em cláusula 'in'
	 * 
	 */
	$strPkFts;
	$arrFk=array();
	$countArrFk=null;
	if(!empty($_GET["_fts"])){
		//Ajusta preferencias do usuario
		$_SESSION["SESSAO"]["PREFERENCIAS"][$_modulo]["_fts"] = $_GET["_fts"];

		$arrFk = retPkFullTextSearch($_tab, $_GET["_fts"]/*, $_GET["_pagina"],$_limite*/);

		$countArrFk=$arrFk["foundRows"];
		if($countArrFk>0){
			$strPkFts = implode(",", $arrFk["arrPk"]);
			$_SESSION["SEARCH"]["WHERE"][] = $_chavefts . " in (".$strPkFts.")";
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
			cbSetPostHeader("0","alert");
			die("Nenhuma coluna de data foi configurada para pesquisa neste Mà³dulo. \nNão informe nenhuma data no calendário.");
		}else{
			//ajusta preferencias do usuario
			$_SESSION["SESSAO"]["PREFERENCIAS"][$_modulo]["_fds"] = $_GET["_fds"];
			
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
		unset($_SESSION["SESSAO"]["PREFERENCIAS"][$_modulo]["_fds"]);
	}

	/* ***********************
	 * Pesquisa parametrizada por colunas
	 * Realizar a pesquisa obedecendo as colunas e valores enviados via get
	 * 
	 */
    $iget=0;//Verificar se parametros get validos foram enviados
	while (list($_key, $_val) = each($_GET)) {
		$_between = false;

		//Não processar como "colunas para Where" parametros do carbon, que sao iniciados por "_"
		if($_val!="" and $_key!="btnsubmitform" and substr($_key,0,1)!="_" and substr($_key,-2)!="_2"){
			//print_r($_arrpagpsq); echo "<br>1</br>";//die();

			if (substr($_key,-2)=="_1"){
				$_key = substr($_key,0,-2); //Transforma do nome do campo para capturar informacoes de tipo
				$_keyval1 = $_GET[$_key."_1"];
				$_keyval2 = $_GET[$_key."_2"];
				$_between = true;
			}

			$_where = $_arrpagpsq[$_mtotab][$_key]["where"];
			$_datatype = $_arrpagpsq[$_mtotab][$_key]["datatype"];

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
			
			switch ($_where):
				case "=": //Igual a
					$_SESSION["SEARCH"]["WHERE"][$_key] = $_key . " = " . evaltipocoldb($_mtotab, $_key, $_datatype, $_val);
			        $iget++;
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
					$_SESSION["SEARCH"]["WHERE"][$_key] = "(" . $_key . " between " . evaltipocoldb($_mtotab, $_key, $_datatype, $_keyval1) . " and " . evaltipocoldb($_mtotab, $_keyval2, $_datatype, $_keyval2) . ")";
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
				        $_SESSION["SEARCH"]["WHERE"][$_key] = $_key . " = " . evaltipocoldb($_mtotab, $_key, $_datatype, $_val);
				        $iget++;
				    }elseif($iMultEsc>1){
				        $strin = $_key . " in (";
				        $virg="";
				        foreach($arrMultEsc as $i => $valor){
				            $strin .= $virg . evaltipocoldb($_mtotab, $_key, $_datatype, $valor);
				            $virg=",";
				        }
				        $strin .= ")";
				        $_SESSION["SEARCH"]["WHERE"][] = $strin;
				        $iget++;
				    }
					break;
				default:
					echo ("Informação de Operador default para clausula [WHERE][".$_mtotab."][".$_key."] n&atilde;o foi encontrado nas defini&ccedil;&otilde;es da p&aacute;gina de pesquisa". $_modulo);
					echo ("\nPossibilidade 1: Configurar operador 'where': [" . $_where . "]");
					echo ("\nPossibilidade 2: Parà¢metro GET informado deve ser colocado como exceção para não ser tratado como coluna: [" . $_key . "=".$_val."]");
					//echo "<!-- \n";print_r($_arrpagpsq);echo" \n-->";
					die();
			endswitch;

		}
		
		//Processar configuracoes opcionais que foram enviadas pela configuracao da funcao de CB.pesquisar({}) no javascript
		if($_val!="" and $_key!="btnsubmitform" and substr($_key,0,1)=="_"){
			if($_key=="_ordcol" && !empty($_GET["_orddir"]) && ($_GET["_orddir"]=="asc" or $_GET["_orddir"]=="desc")){
				//Ajusta preferencias do usuario
				$_SESSION["SESSAO"]["PREFERENCIAS"][$_modulo]["orderby"]["_ordcol"] = $_GET["_ordcol"];
				$_SESSION["SESSAO"]["PREFERENCIAS"][$_modulo]["orderby"]["_orddir"] = $_GET["_orddir"];
				
				unset($_SESSION["SEARCH"]["ORDERBY"]);
				
				//Está sendo utilizado um recurso que quebra à­ndices, para colocar valores vazios por último na ordenação
				//$_SESSION["SEARCH"]["ORDERBY"][] = $_val." ".$_GET["_orddir"];
				$_SESSION["SEARCH"]["ORDERBY"][] = "if(ifnull(".$_val.",'') = '',1,0),".$_val." ".$_GET["_orddir"];
			}
		}
		
	}//while (list($_key, $_val) = each($_GET)) {

//die("psqfull:".$_psqfull."iget:".$iget);
	//print_r($_SESSION["SEARCH"]);die;
	
	if(empty($_psqfull) and $iget==0){
	    cbSetPostHeader("0","erro");
		die("Mà³dulo não configurado corretamente: Informe configuracao para pesquisa Full.");
		
	}elseif($_psqfull=="N" and $iget==0 and $arrFk["foundRows"]==0 and empty($_GET["_fts"]) and empty($_GET["_fds"])){
	    cbSetPostHeader("0","alert");
		die("Informe um parà¢metro para a pesquisa!");
		
	//}elseif($_psqfull=="N" and $arrFk["foundRows"]==0 and (!empty($_GET["_fts"]) or !empty($_GET["_fds"]))){
	}elseif($_psqfull=="N" and $arrFk["foundRows"]==0 and (!empty($_GET["_fts"]))){
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
		$clauLimit = $_pageOffset.",".$_limite;
		$_SESSION["SEARCH"]["LIMIT"][]=$clauLimit;
	}
	/***************************************************************************************************************
	 * Executa PRE SEARCH EXEC
	 ***************************************************************************************************************/
	$arq_presearchexec = "../eventcode/modulofiltrospesquisa/presearchexec__".$_modulo.".php";

	if (file_exists($arq_presearchexec)) {
		include_once($arq_presearchexec);
	}

	/*
	 * Monta o SQL de acordo com os arrays
	 */
	//print_r($_SESSION["SEARCH"]);
	$_SEARCHSQL = montaSearchFiltrosPesquisa($_SESSION["SEARCH"], "SQL_CALC_FOUND_ROWS");

	
	//die($_SEARCHSQL);
	//Envia o SQL no header para fins de debug rápido
	if($_inspecionar_sql){
		die($_SEARCHSQL);
	}
	
	/*********************************************************************************************
	 * Executa a consulta
	 ********************************************************************************************/
	$_resultados = mysql_query($_SEARCHSQL);
	if (!$_resultados) {
	    die('Falha na execucao da Consulta para os Resultados: ' . mysql_error() . "\n" . $_tab."\n".$_SEARCHSQL);
	}

	//Recupera a quantidade total de registros desconsiderando a cláusula LIMIT ***Não está sendo utilizado: considera-se a quantidade de registros encontrados pela FTS 
	$foundRows = myFoundRows();
	
	$_arrtab = retarraytabdef($_mtotab);

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
	
	$ARRRESULTADOS["rotulo"] = htmlentities($_rotulomenu);
	$ARRRESULTADOS["numrows"] = $_imodulores;
	$ARRRESULTADOS["novajanela"] = $_novajanela;
	$ARRRESULTADOS["numpaginas"] = ceil($_imodulores / $_limite);

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
	    if($_arrpagpsq[$_mtotab][$_metacmp->name]["visres"] == 'Y' or $_inspecionar_colunas==true){
	        $ARRRESULTADOS["cols"][$_metacmp->name] = htmlentities($_arrtab[$_metacmp->name]["rotcurto"]);
			$icols++;
	    }
	    $_i++;

	}

	if($icols==0){
		echo "Nenhuma coluna marcada como visà­vel na configuração do Mà³dulo.";
	}
    $ARRRESULTADOS["numcols"] = $icols;

    $_arrhlcond = retsearchrescond($_modulo, false);
	$_ilinha = 0;
	//print_r($_arrhlcond);
		
    #########################################################################################################################################
    # ROWS
    #########################################################################################################################################
    while ($_row = mysql_fetch_array($_resultados)){
	
    	print_r($_row);

		$_ilinha++;

		//Inicia a montagem dos parametros GET
    	$_i = 0;

    	//echo $_numcampos; die();
    	while ($_i < $_numcampos) {
    		
    		//print_r($_arrpagpsqparget); //die();
    		$_nomecol = $_arridxcol[$_i];
    	    if(in_array($_nomecol,$_arrpagpsqparget)){
				//Chamada de pagina por GET
                $ARRRESULTADOS["rows"][$_ilinha]["parget"][$_nomecol] = htmlentities($_row[$_i]);
	    	}
	    	$_i++;
    	}

    	$_i = 0;

        //print_r($_row);
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
        
            foreach ($_arrhlcond[$_modulo] as $_fldcond => $_cond) {
        		if ($_boocond==false){
        			//echo ("<br>cond:[".$_cond['cond']."-".$_cond["valor1"]."]<br>");
        			switch($_cond["cond"]){
        				
        				case "=":
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
    		if($_arrpagpsq[$_mtotab][$_nomecol]["visres"] == 'Y' or $_inspecionar_colunas==true){

    		    $iNav++;
    		    
    			//Formata o valor do campo do DB para ser visualizado corretamente
    			$valuetd = formatastringvisualizacao($_row[$_i], $_arrpagpsq[$_mtotab][$_nomecol]["datatype"]);

    			$valuetd = htmlentities($valuetd, ENT_QUOTES, 'ISO-8859-1');

    			/*
    			 * Bug: Algumas colunas no mysql (collate: latin1) aceitaram caracteres UTF-8
    			 * Isto obriga a que, apà³s a conversão em HTML Entities, efetuar decode em qualquer remanescente UTF-8 
    			 * @todo: Transformar o DB e aplicação em UTF8 *asap*
    			 * https://www.blueboxcloud.com/insight/blog-article/getting-out-of-mysql-character-set-hell
    			 */
    			if(($_arrpagpsq[$_mtotab][$_nomecol]["datatype"]=="varchar"
    				or $_arrpagpsq[$_mtotab][$_nomecol]["datatype"]=="char" 
    				or $_arrpagpsq[$_mtotab][$_nomecol]["datatype"]=="longtext") 
    				and !empty($valuetd)){
    				//Converte caracteres remanescentes
    				$valuetd = $valuetd;
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
	$arq_possearchexec = "../eventcode/modulofiltrospesquisa/possearchexec__".$_modulo.".php";	
	if (file_exists($arq_possearchexec)) {
		include_once($arq_possearchexec);
	}


    if(!empty($_arrhlcond[$_modulo])){
    
    	foreach ($_arrhlcond[$_modulo] as $_fldcond => $_cond) {
    	    $ARRRESULTADOS["legenda"][$_cond["cor"]] = acentos2ent($_cond["legenda"]);
    
    	}
    }

}

//print_r($_SESSION["SESSAO"]["PREFERENCIAS"]);die;

//Armazena as preferencias do usuario
armazenaUserPref();

//Em caso de erro de JSON, verificar os dados existentes nesta linha:
//print_r($ARRRESULTADOS["rows"]);die;

//Transforma o array em json
$json_table = json_encode($ARRRESULTADOS);

if(json_last_error()){
    echo("_modulofiltrospesquisa: Erro ao montar Json: Cà³digo [".json_last_error()."] Erro: ".json_last_error_msg());
    //Em caso de erro de JSON, verificar os dados existentes nesta linha:
	//print_r($ARRRESULTADOS["rows"]);
    die;
}else{
    echo $json_table;
}

?>