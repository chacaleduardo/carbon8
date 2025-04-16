<?
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$ler = true; //Gerar logs de erro
$rid = "\n".rand()." - Sislaudo: ";
if ($ler) error_log($rid.basename(__FILE__, '.php'));

session_start();
$sessionid = session_id(); //PEGA A SESSÃO

ini_set("display_errors", "1");
error_reporting(E_ALL);

if (defined('STDIN')) { //se estiver sendo executao em linhade comando
	$prefu = "stdin_";
	include_once("/var/www/carbon8/inc/php/functions.php");
	include_once("/var/www/carbon8/model/modelDashboard.php");
} else { //se estiver sendo executado via requisicao http
	include_once("../inc/php/functions.php");
	include_once("../model/modelDashboard.php");
}

$_inspecionar_sql = ($_GET["_inspecionar_sql"] == "Y") ? true : false;

echo "Início: ".date("d/m/Y H:i:s", time()).'<br>';
$sessionid = session_id(); //PEGA A SESSÃO  

$grupo = rstr(8);

//Controle de lock: evita que processos rodem em paralelo; Colocar a instrucao del no fim; Tempo em segundos
$rkey = 'cron:_lock:'.$_SERVER["SCRIPT_FILENAME"];
$iniLock = re::dis()->get($rkey);

if ($_GET['_id']) {
	$_id = " where iddashcard = ".$_GET['_id']." ";
} else {
	$_id = "";
	if ($iniLock === false) {
		re::dis()->set($rkey, date('d/m/y h:i'), 3600);
	} else {
		echo 'Adiado. Rodando desde '.$iniLock;
		d::b()->query("INSERT INTO log (idempresa, sessao, tipoobjeto, idobjeto, tipolog, status, log, criadoem, data) 
							VALUES ('1', '','cron', 'testedash', 'status', 'ADIADO', 'LOCKED. Rodando desde ".$iniLock."', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))") or die("Erro #1 ao inserir log: ".mysqli_error(d::b())."<br>".$sqll);
		die;
	}
}

re::dis()->hMSet('cron:testedash', ['inicio' => Date('d/m/Y H:i:s')]);

$sqli = "INSERT INTO log (idempresa, sessao, tipoobjeto, idobjeto, tipolog, log, status, criadoem, data) 
			VALUES ('1', '".$grupo."', 'cron', 'testedash".$_id."', 'status', 'INICIO', 'SUCESSO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";

d::b()->query($sqli) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqli);
d::b()->query("set transaction isolation level read committed;");

//INATIVA DASHBOARD CUJO OS DASHCARDS FORAM INATIVADOS
inativarDashboarcomDashcardsInativos($_inspecionar_sql);

//Buscar quais Dash foram configurados para diminuir a quantidade de configuração
$sqlBuscarDashLp = "SELECT GROUP_CONCAT(DISTINCT idobjeto) AS iddashcard FROM "._DBCARBON."._lpobjeto lp WHERE tipoobjeto = 'dashboard';";
if ($_inspecionar_sql) {
	echo ('dados para insert tipoobjeto manual <pre>'.$sqlBuscarDashLp.'</pre>');
}
$resDashLp = d::b()->query($sqlBuscarDashLp) or die("Atualização config Dash LP.: ".mysqli_error(d::b())."<p>SQL: $sqlBuscarDashLp");
$linhaDashLp = mysqli_fetch_assoc($resDashLp);

//Consulta Principal para dar insert no Dashboard
$res = buscaDashcardsAtivosParaInsertUpdate($_inspecionar_sql, $_id, $ler, $rid, $linhaDashLp['iddashcard']);
$i = 0;

$sqlQtd = "INSERT INTO log (idempresa, sessao, tipoobjeto, idobjeto, tipolog, log, status, criadoem, data) 
			VALUES ('1', '".$grupo."', 'cron', 'testedash".$_id."', 'qtd', '".mysqli_num_rows($res)."', 'SUCESSO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";

d::b()->query($sqlQtd) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqlQtd);

//loop da consulta principal
while ($row = mysqli_fetch_assoc($res)) {

	if (!empty($row['tab'])) {
		//INDICADORES DE FLUXO/ETAPA				
		if ($row['tipoobjeto'] == 'fluxostatus' || $row['tipoobjeto'] == 'etapa') {

			$verificaTimeIndicador = verificaSeConsultaDoIndicadorEstaNaHoraDeRodar($row);
			if ($verificaTimeIndicador) {
				preparaIndicadoresFluxoEtapa($_inspecionar_sql, $row);
			}
		} else {

			//INDICADORES MANUAIS				
			$clausula = "";
			$group = "";

			if ($idDash != $row['iddashcard']) {
				//PEGA CLAUSULAS DA TABELA DO DASH
				$clausula .= getTableDashFilters($row['iddashcard']);
			}

			//PEGA CLAUSULAS DO INDICADOR DO DASH
			$arrcl = getClausulaCodeDash($row['code'], "Y");
			$clausula .= $arrcl['clausula'];
			$group = $arrcl['group'];
			$idDash =  $row['iddashcard'];
			$verificaTimeIndicador = verificaSeConsultaDoIndicadorEstaNaHoraDeRodar($row);

			if ($verificaTimeIndicador) {
				insereAtaulizaIndicadoresManuais($row, $_inspecionar_sql, $clausula, $group);
			}
		}
	}
}

if ($i > 0) {
	insereAtaulizaIndicadoresFluxoEtapa($tipoobjeto, $cardurl, $tab, $cardcolor, $col, $_inspecionar_sql, $colprazod);
}

//ATUALIZAÇÃO DAS CORES
$sql = "update  dashcard c 
		join dashboard b on b.iddashcard = c.iddashcard
		set  b.card_border_color = if (card_value> 0,'danger','secondary'),
		b.card_color = if (card_value> 0,'danger','secondary'),
		b.card_border_color = if (card_value> 0,'danger','secondary')
		where cardbordercolor = \"if (count(1) > 0,'danger','secondary')\";";
d::b()->query($sql) or die("Atualização Cores".mysqli_error(d::b())."<p>SQL: $sql");
if ($_inspecionar_sql) {
	echo ('<pre>'.$sql.'</pre>');
}
$sql = "update  dashcard c 
		join dashboard b on b.iddashcard = c.iddashcard
		set  b.card_border_color = if (card_value> 0,'primary','secondary'),
		b.card_color = if (card_value> 0,'primary','secondary'),
		b.card_border_color = if (card_value> 0,'primary','secondary')
		where cardbordercolor = \"if (count(1) > 0,'primary','secondary')\";";
d::b()->query($sql) or die("Atualização Cores".mysqli_error(d::b())."<p>SQL: $sql");
if ($_inspecionar_sql) {
	echo ('<pre>'.$sql.'</pre>');
}
$sql = "update  dashcard c 
		join dashboard b on b.iddashcard = c.iddashcard
		set  b.card_border_color = if (card_value> 0,'success','secondary'),
		b.card_color = if (card_value> 0,'success','secondary'),
		b.card_border_color = if (card_value> 0,'success','secondary')
		where cardbordercolor = \"if (count(1) > 0,'success','secondary')\";";
d::b()->query($sql) or die("Atualização Cores".mysqli_error(d::b())."<p>SQL: $sql");
if ($_inspecionar_sql) {
	echo ('<pre>'.$sql.'</pre>');
}
$sql = "update  dashcard c 
		join dashboard b on b.iddashcard = c.iddashcard
		set  b.card_border_color = if (card_value> 0,'warning','secondary'),
		b.card_color = if (card_value> 0,'warning','secondary'),
		b.card_border_color = if (card_value> 0,'warning','secondary')
		where cardbordercolor = \"if (count(1) > 0,'warning','secondary')\";";
d::b()->query($sql) or die("Atualização Cores".mysqli_error(d::b())."<p>SQL: $sql");
if ($_inspecionar_sql) {
	echo ('<pre>'.$sql.'</pre>');
}
$sql = "update  dashcard c 
		join dashboard b on b.iddashcard = c.iddashcard
		set  b.card_border_color = 'primary',
		b.card_color = 'primary'
		where cardbordercolor = \"concat('primary')\";";
d::b()->query($sql) or die("Atualização Cores".mysqli_error(d::b())."<p>SQL: $sql");
if ($_inspecionar_sql) {
	echo ('<pre>'.$sql.'</pre>');
}
$sql = "update  dashcard c 
		join dashboard b on b.iddashcard = c.iddashcard
		set  b.card_border_color = 'success',
		b.card_color = 'success'
		where cardbordercolor = \"concat('success')\";";
d::b()->query($sql) or die("Atualização Cores".mysqli_error(d::b())."<p>SQL: $sql");
if ($_inspecionar_sql) {
	echo ('<pre>'.$sql.'</pre>');
}
$sql = "update  dashcard c 
		join dashboard b on b.iddashcard = c.iddashcard
		set  b.card_border_color = 'danger',
		b.card_color = 'danger'
		where cardbordercolor = \"concat('danger')\";";
d::b()->query($sql) or die("Atualização Cores".mysqli_error(d::b())."<p>SQL: $sql");
if ($_inspecionar_sql) {
	echo ('<pre>'.$sql.'</pre>');
}

$sql = "update  dashcard c 
		join dashboard b on b.iddashcard = c.iddashcard
		set  b.card_border_color = 'warning',
		b.card_color = 'warning'
		where cardbordercolor = \"concat('warning')\";";
d::b()->query($sql) or die("Atualização Cores".mysqli_error(d::b())."<p>SQL: $sql");
if ($_inspecionar_sql) {
	echo ('<pre>'.$sql.'</pre>');
}

$sql = "update  dashcard c 
		join dashboard b on b.iddashcard = c.iddashcard
		set  b.card_border_color = 'secondary',
		b.card_color = 'secondary'
		where cardbordercolor = \"concat('secondary')\";";
d::b()->query($sql) or die("Atualização Cores".mysqli_error(d::b())."<p>SQL: $sql");
if ($_inspecionar_sql) {
	echo ('<pre>'.$sql.'</pre>');
}

//Ao fim: remover lock
re::dis()->del($rkey);

echo "Fim: ".date("d/m/Y H:i:s", time()).'<br>';

re::dis()->hMSet('cron:testedash', ['fim' => Date('d/m/Y H:i:s')]);

$sqli = "INSERT INTO laudo.log (idempresa, sessao, tipoobjeto, idobjeto, tipolog, log, status, criadoem, data) 
							VALUES ('1', '".$grupo."','cron', 'testedash".$_id."', 'status', 'FIM', 'SUCESSO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";

d::b()->query($sqli) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqli);
