<?
require_once("../inc/php/validaacesso.php");

//ini_set("display_errors", 1);
//error_reporting(E_ALL);

session_cache_expire(1);
session_cache_limiter("private");
//header('Content-Type: application/json');

if($_SESSION["SESSAO"]["TOKEN"]==true){
	die("<?-- Pesquisa não permitida -->");
}

//$jAcoes=null;
function recuperaAcoes(){
	global $jAcoes, $ARRMOD;
	
	$sqla = "SELECT *
				FROM carbonnovo._moduloacao where modulo = '".$_GET["_modulo"]."';";

	$res = d::b()->query($sqla);	

	$arrres=array();
	while ($row = mysqli_fetch_assoc($res)) {
		//$arrres[]=$row;
		$ARRMOD["acoes"][$row["moduloacaoid"]]=$row;
	}
	
	if(count($arrres)>0){
		$jAcoes = json_encode($arrres);
	}
}

//Recupera Parametros gerais da Pagina
if(!$_SESSION["SESSAO"]["LOGADO"]){
	//Não logado: Inicializa o modulo de LOGIN
	$_arrModConf = retArrModuloConf('_login');

}else{

	//Inicializa o objeto json para o Módulo, sem substituir caso já exista
	userPref("i", $_modulo);
	
	if(!empty($_GET["_userPref"])){
		if($_GET["_userPref"]=="resetFts"){
			userPref("d", $_modulo."._fts");
		}
		die;
	}

	if(!empty($_GET["_modulo"])){
		//Inicializa o modulo enviado via GET
		$_arrModConf = retArrModuloConf($_GET["_modulo"]);

	}else{
		
		$_arrModConf = retArrModuloConf($rowm["moduloinicial"]);
	}
}

/*
 * Considerar módulos relacionados
 */
if($_arrModConf["tipo"]=="MODVINC"){
	$moduloReal=$_arrModConf["modvinculado"];
}else{
	$moduloReal=$_modulo;
}

$custom = modulocustom($_arrModConf["modulo"], $_SESSION['SESSAO']['IDPESSOA']);

/* ***********************************************************************************************
 * Cb5.0: Armazenar os objetos em array, para serem tranformados em Json e devolvidos à página 
 *********************************************************************************************** */
$ARRMOD = array();

$_modulo 					= $_arrModConf["modulo"];
$ARRMOD["modulo"]			= $_arrModConf["modulo"];
$ARRMOD["checkbox"]			= $_arrModConf["checkbox"];
$ARRMOD["modulopar"]		= $_arrModConf["modulopar"];
$ARRMOD["timeout"]			= $_arrModConf["timeout"];
$ARRMOD["rotulomenu"]		= htmlentities($_arrModConf["rotulomenu"]);
$ARRMOD["tipo"]				= $_arrModConf["tipo"];
$ARRMOD["cssicone"] 		= $_arrModConf["cssicone"];
$ARRMOD["csscustom"] 		= $_arrModConf["csscustom"];
$ARRMOD["ready"] 			= $_arrModConf["ready"];
$ARRMOD["titulofiltros"] 	= htmlentities($_arrModConf["titulofiltros"]);
$ARRMOD["btsalvar"]			= $_arrModConf["btsalvar"];
$ARRMOD["cbheader"]			= $_arrModConf["cbheader"];
$ARRMOD["btnovo"]			= $_arrModConf["btnovo"];
$ARRMOD["btimprimir"] 		= $_arrModConf["btimprimir"];
$ARRMOD["btimprimirconf"] 	= $_arrModConf["btimprimirconf"];
$ARRMOD["menufixo"] 		= $_arrModConf["menufixo"];
$ARRMOD["psqfull"]			= $_arrModConf["psqfull"];
$ARRMOD["urldestino"] 		= empty($custom['urldestino']) ? htmlentities($_arrModConf["urldestino"]) : htmlentities($custom['urldestino']);
$ARRMOD["urlprint"] 		= htmlentities($_arrModConf["urlprint"]);
$ARRMOD["novajanela"] 		= $_arrModConf["novajanela"];
$ARRMOD["novajanelamodal"] 	= $_arrModConf["novajanelamodal"];
$ARRMOD["limite"]			= $_arrModConf["limite"];
$ARRMOD["numlinha"]			= $_arrModConf["numlinha"];
$ARRMOD["postonenter"]		= $_arrModConf["postonenter"];
$ARRMOD["largurafixa"]		= $_arrModConf["largurafixa"];
$ARRMOD["escondemenu"]		= $_arrModConf["escondemenu"];
$ARRMOD["ajaxparalelo"]		= $_arrModConf["ajaxparalelo"];
$ARRMOD["ordenavel"]		= $_arrModConf["ordenavel"];
$ARRMOD["statusrest"]		= $_arrModConf["statusrest"];
$ARRMOD["tabrest"]		= $_arrModConf["tabrest"];
$ARRMOD["oprestaurar"]		= $_arrModConf["oprestaurar"];
//Informações do parent, para construir o breadcrumb
$ARRMOD["cssiconepar"] 		= $_arrModConf["cssiconepar"];
$ARRMOD["rotulomenupar"]	= htmlentities($_arrModConf["rotulomenupar"]);
$ARRMOD["rotulomenupar"] 	= empty($ARRMOD["rotulomenupar"])?"":$ARRMOD["rotulomenupar"];


if($_arrModConf["btnovo"] == 'Y'){
	$arrEmpresas = [];
	$arrModulosUsuario = getModsUsr()["MODULOS"];

	foreach($arrModulosUsuario as $idempresa => $modulosEmpresa){
		if(array_key_exists($_modulo, $modulosEmpresa)){

			if(($_GET['idempresa'] ? $_GET['idempresa'] : cb::idempresa()) == $idempresa) {
				$ARRMOD["permissaomodulo"] = $modulosEmpresa[$_modulo]['permissao'];
			}

			if($modulosEmpresa[$_modulo]['permissao'] == 'w')
				if($idempresa) $arrEmpresas[] = $idempresa;
		}
	}

	$ARRMOD['lpsusuario'] = getModsUsr("LPS");

	if(count($arrEmpresas) > 0){
		$qr = 'SELECT idempresa, sigla, corsistema, iconemodal 
			FROM empresa
			WHERE status = "ATIVO" AND idempresa in ('.implode(",", $arrEmpresas).')';

		$rs = d::b()->query($qr);
        while($rw = mysqli_fetch_assoc($rs)){
			$ARRMOD["btnovooptions"][$rw["idempresa"]]['sigla'] = $rw['sigla'];
			$ARRMOD["btnovooptions"][$rw["idempresa"]]['corsistema'] = $rw['corsistema'];
			$ARRMOD["btnovooptions"][$rw["idempresa"]]['iconemodal'] = "." . preg_replace('/(^\.+)/', '', $rw['iconemodal']);
        }
	}
}


//Relatórios associados ao módulo somente para os clientes
$actual_link = $_SERVER["HTTP_HOST"];
$pattern = "/resultados/i";
$mostraImpressora=preg_match($pattern, $actual_link);
if(($mostraImpressora >= 1 && logado()) || $_GET['carregaconfreport']=="Y"){
	$ARRMOD["relatorios"]=getConfRelatoriosModulo($_modulo);
} else {
	$ARRMOD["relatorios"] = array();
}


//Preferências do usuário 2.0
$sqljc = "SELECT json_insert(json_extract(jsonpreferencias,'$.".$_modulo."'), '$.pesquisamenulateral', json_extract(jsonpreferencias,'$.pesquisamenulateral')) as jsonpref 
		from pessoa where usuario='".$_SESSION["SESSAO"]["USUARIO"]."'";
$resc = d::b()->query($sqljc);
$rc = mysqli_fetch_assoc($resc);
$jPref = empty($rc["jsonpref"])?"{}":$rc["jsonpref"];

$ARRMOD["jsonpreferencias"] = json_decode($jPref, true);

if($_arrModConf["ready"]=="FILTROS"){
#################################################### Recupera a definicao dos campos da view ou table default da pagina
	$_sqlfiltros = "SELECT
		  m.tab
		  ,mtc.col
		  ,mtc.perfindice
		  ,mtc.ftskey
		  ,mf.psqkey
		  ,mf.psqreq
		  ,mf.psqreqdefault
		  ,mf.visres
	      ,mf.oculto
	      ,mf.filtrodata
	      ,mf.parget
		  ,mf.entre
		  ,mf.masc
		  ,mtc.ordpos
		  ,mtc.datatype
		  ,mtc.primkey
		  ,mtc.autoinc
		  ,mtc.nullable
		  ,mtc.rotcurto
		  ,mtc.rotlongo
		  ,mtc.rotpsq
		  ,mtc.prompt
		  ,mf.promptativo
		  ,mtc.default
		  ,mtc.code
		  ,mtc.codeeval
		  ,mtc.acsum
		FROM
			"._DBCARBON."._modulo m
			join "._DBCARBON."._mtotabcol mtc on (mtc.tab=m.tab)
			left join "._DBCARBON."._modulofiltros mf on (mf.modulo = m.modulo and mf.col = mtc.col) /* ************ EM CASO DE ERRO SEMPRE CONFERIR A _modulofiltros ******************** */
		WHERE m.modulo = '". $moduloReal ."'
		ORDER BY  case when mtc.ordpos is null then 2 else 1 end, mtc.ordpos asc";
	
	//echo "<!-- ".$_sqlfiltros." -->"; die();
	
	$_resfiltros = mysql_query($_sqlfiltros);
	$_iresfiltros = mysql_num_rows($_resfiltros);
	if(!$_resfiltros){
		die("Falha ao recuperar Filtros de Pesquisa [".$_modulo."]: ".mysql_error()."\nSql:".$_sqlfiltros);
	}
	
	if($_iresfiltros == 0){
		echo "<!-- ";
		echo $_sqlfiltros;
		echo " -->";
	
		die("Conferir Módulo: [".$moduloReal."]<br>Informacao para pesquisa em [".$_arrModConf["tab"]."] nao disponivel.&nbsp;Solu&ccedil;&otilde;es:<br>1 - Provavelmente as informacoes estao inconsistentes em _mtotabcol ou _modulofiltros para [".$_arrModConf["tab"]."];<br>2 - O M&oacute;dulo ou os filtros n&atilde;o foram devidamente configurados.");
	}
	
	$ARRMOD["filtrardata"]="";//Caso não exista nenhuma coluna de data configurada, não mostrar botão de calendário para pesquisa
	/*
	 * Montagem do array de campos de filtro 
	 */
	$parget='';
	while ($_rowpp = mysql_fetch_assoc($_resfiltros)){

	    //print_r($_rowpp);die;

		$_mtotab = $_rowpp["tab"];

		$ARRMOD['colunas'][$_rowpp['col']]['tab']			= htmlentities($_rowpp['tab']);
		$ARRMOD['colunas'][$_rowpp['col']]['col']			= htmlentities($_rowpp['col']);
		$ARRMOD["colunas"][$_rowpp["col"]]["datatype"]		= htmlentities($_rowpp["datatype"]);
	    $ARRMOD['colunas'][$_rowpp['col']]['psqreq']		= htmlentities($_rowpp['psqreq']);
	    $ARRMOD['colunas'][$_rowpp['col']]['psqreqdefault']	= htmlentities($_rowpp['psqreqdefault']);
		$ARRMOD['colunas'][$_rowpp['col']]['entre']			= htmlentities($_rowpp['entre']);
	    $ARRMOD['colunas'][$_rowpp['col']]['oculto']		= htmlentities($_rowpp['oculto']);
	    $ARRMOD['colunas'][$_rowpp['col']]['nullable']		= htmlentities($_rowpp['nullable']);
	    $ARRMOD['colunas'][$_rowpp['col']]['rotcurto']		= htmlentities($_rowpp['rotcurto']);
	    $ARRMOD['colunas'][$_rowpp['col']]['rotpsq']		= htmlentities($_rowpp['rotpsq']);
	    $ARRMOD['colunas'][$_rowpp['col']]['prompt']		= htmlentities($_rowpp['prompt']);
		$ARRMOD['colunas'][$_rowpp['col']]['promptativo']	= htmlentities($_rowpp['promptativo']);
		$ARRMOD['colunas'][$_rowpp['col']]['acsum']			= htmlentities($_rowpp['acsum']);
		$ARRMOD['colunas'][$_rowpp['col']]['masc']			= htmlentities($_rowpp['masc']);

		//Conforme lógica em functions.php.retArrModuloConfFiltros()
		if($_rowpp['filtrodata']=="Y" and ($_rowpp["datatype"]=="date" or $_rowpp["datatype"]=="datetime" or $_rowpp["datatype"]=="timestamp")){
			$ARRMOD["filtrosdata"][$_rowpp['col']]["perfindice"]=$_rowpp['perfindice'];
			$ARRMOD["filtrosdata"][$_rowpp['col']]["rotulo"]=(!empty($_rowpp['rotcurto']))? $_rowpp['rotcurto'] : $_rowpp['col'];
			$ARRMOD["filtrardata"]="Y";
		}

        if($_rowpp['ftskey']=="Y"){
            $ARRMOD['colsfts'][]=$_rowpp['col'];
        }

		if($_rowpp['parget']=="Y"){
		    $ARRMOD["parget"]=$_rowpp['col'];
		}
		if($_rowpp["primkey"]=="Y"){
			$ARRMOD["pk"]=$_rowpp["col"];
		}

	}#while ($_rowpp = mysql_fetch_array($_resfiltros))
	
	$ARRMOD["tabpesquisa"]=$_mtotab;
}

//Caso seja um módulo vinculado e as colunas não retornarem nada provavelmente o modulo vonculado não existe
if($ARRMOD["tipo"]=="MODVINC" && $ARRMOD["ready"]=="" && sizeof($ARRMOD["colunas"])==0){
	//Verifica se o modulo vinculado existe
	if(!empty($_GET["_modulo"])&&$_GET["_modulo"]!='_modulo'){
		$sqlmv = "SELECT mv.modulo as modulovinculado
			FROM 
				carbonnovo._modulo m 
				left join carbonnovo._modulo mv on (mv.modulo=m.modvinculado)
			WHERE m.modulo = '".$_GET["_modulo"]."'";

		$resmv = d::b()->query($sqlmv) or die("Erro ao recuperar módulo vinculado: ".mysqli_error(d::b()));
		$rv = mysqli_fetch_assoc($resmv);
		if(empty($rv["modulovinculado"])){
			die("O módulo vinculado em [".$_GET["_modulo"]."] não existe.<br><a href='javascript:janelamodal(\"?_modulo=_modulo&_acao=u&modulo=".$_GET["_modulo"]."\")'>Ajustar</a>");
		}
	}

}
//Recupera as Ações (botões com ações pré-programadas
recuperaAcoes();

$ARRMOD['scriptBloqueio'] = $bloqueioScript;

//Transforma o array em json
$json_filtros = json_encode($ARRMOD,JSON_PRETTY_PRINT);

if(json_last_error()){
    
    echo("_modulofiltros: Erro ao montar Json: Código [".json_last_error()."] Erro: ".json_last_error_msg());
	if($_SESSION["SESSAO"]["USUARIO"]=="marcelo" OR $_SESSION["SESSAO"]["USUARIO"]=="hermesp"){
		echo "teste";
		print_r($ARRMOD);
	}
    die;
}else{
    echo $json_filtros;
}

//print_r($ARRMOD);

?>
