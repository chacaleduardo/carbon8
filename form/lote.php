<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
require_once("../model/prodserv.php");
require_once("../api/prodserv/index.php");

// CONTROLLERS
require_once(__DIR__ . "/controllers/lote_controller.php");
require_once(__DIR__ . "/controllers/pessoa_controller.php");
require_once(__DIR__ . "/controllers/solmat_controller.php");
require_once(__DIR__ . "/controllers/formalizacao_controller.php");

// COMPARA LP USR COM LPS DO PCP
$lp = getModsUsr("LPS");
$arraylp = explode(",", str_replace("'", "", $lp));

$descricaolp = 'PCP';
$lppcp = _LpController::BuscarLpPorDescricao($descricaolp);
$idlppcp = array_column($lppcp, 'idlp');

$lpcoincidentes = array_intersect($arraylp, $idlppcp);

//Chama a Classe prodserv
$prodservclass = new PRODSERV();

if ($_POST) {
	include_once("../inc/php/cbpost.php");
}
if ($_POST['alertacomprador']) {
	echo (
		"<script>
			alert('" . $_POST['alertacomprador'] . "');
		</script>"
	);
}

$idobjetosolipor = $_GET['idobjetosolipor'];
$tipoobjetosolipor = $_GET['tipoobjetosolipor'];

if ($_GET['_acao'] == 'i') {
} else {
}

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parâmetros GET que devem ser validados para compor o select principal
 *                pk: indica parâmetro chave para o select inicial
 *                vnulo: indica parâmetros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "lote";
$pagvalmodulo = $_GET['_modulo'];
$pagvalcampos = array(
	"idlote" => "pk"
);
$pessoasDisponiveisParaVinculo = PessoaCOntroller::buscarPessoaPorIdTipoPessoaEGetIdEmpresa(2, getidempresa("p.idempresa", ""), true);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */

if (isset($_GET["_idempresa"])) {

	$_idempresa = $_GET["_idempresa"];
} else if (!empty($_GET['idlote'])) {

	$_sqlidempresa = "select idempresa from lote where idlote = " . $_GET['idlote'] . ";";
	$_residempresa = d::b()->query($_sqlidempresa) or die("getProdutosFormalizacao: Erro: " . mysqli_error(d::b()) . "\n" . $sql);

	while ($_rowidempresa = mysqli_fetch_assoc($_residempresa)) {
		$_idempresa = $_rowidempresa['idempresa'];
	}
} else {

	$_idempresa = $_SESSION["SESSAO"]["IDEMPRESA"];
}

$_sql = " and f.idempresa = " . $_idempresa;

$idunidadepadrao = getUnidadePadraoModulo($_GET["_modulo"], $_idempresa);

if (!empty($idunidadepadrao)) {
	$_sql .= " and f.idunidade = " . $idunidadepadrao;
} else {
	echo '<div class="alert alert-warning" role="alert" style="text-transform:uppercase;margin-top:20px" >
			<div class="row">
				<div class="col-md-12"><b>Módulo Padrão não configurado. Entre em contato com o Administrador do Sistema.
				</div>
			</div>
		</div>';
	die();
}


if ($_GET['idunidade']) {

	$sqlmodunidade = "select m.modulo
						from " . _DBCARBON . "._modulo m 
						join unidadeobjeto o on(m.modulo = o.idobjeto and o.tipoobjeto='modulo' and o.idobjeto like ('lote%') and o.idunidade = " . $_GET['idunidade'] . ")
						where m.ready='FILTROS';";
	$res = d::b()->query($sqlmodunidade) or die("getProdutosFormalizacao: Erro: " . mysqli_error(d::b()) . "\n" . $sql);

	while ($r = mysqli_fetch_assoc($res)) {
		//monta 2 estruturas json para finalidades (loops) d
		if (!empty($r['modulo'])) {
			$pagvalmodulo = $r['modulo'];
		}
	}
}

/*
$pagsql = "select l.* from lote l 
        where  l.idlote = #pkid  and exists (select 1 from  lotefracao f where  f.idlote = l.idlote and f.idunidade=".$idunidadepadrao." and f.idempresa=".idempresa().")";
*/
$pagsql = "select l.* from lote l 
where  l.idlote = #pkid  and exists (select 1 from  lotefracao f where  f.idlote = l.idlote " . $_sql . " )";


/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");


if (!empty($_GET['idunidade'])) {
	$_1_u_lote_idunidade = $_GET['idunidade'];
}


if (empty($_1_u_lote_idunidade) and empty($_GET['idloteorigem'])) {
	$_1_u_lote_idunidade = $idunidadepadrao;
}

if (empty($_1_u_lote_idloteorigem) and !empty($_GET['idloteorigem'])) {
	$_1_u_lote_idloteorigem = $_GET['idloteorigem'];
}
if ($_acao == 'i' and !empty($_1_u_lote_idloteorigem)) {
	$loteorigem = "Y";
	$sql = "select *  from lote where idlote = " . $_1_u_lote_idloteorigem;
	$res = d::b()->query($sql) or die("Erro ao buscar lote de origem sql=" . $sql);
	$row = mysqli_fetch_assoc($res);

	$_1_u_lote_idprodserv = $row['idprodserv'];
	$_1_u_lote_idpessoa = $row['idpessoa'];
	$_1_u_lote_idprodservformula = $row['idprodservformula'];
	$_1_u_lote_tipoobjetoprodpara = $row['tipoobjetoprodpara'];
	$_1_u_lote_idobjetoprodpara = $row['idobjetoprodpara'];
	$_1_u_lote_idsolfab = $row['idsolfab'];
	$_1_u_lote_partida = $row['partida'];
	$_1_u_lote_spartida = $row['spartida'];
	$_1_u_lote_npartida = $row['npartida'];
	$_1_u_lote_exercicio = $row['exercicio'];
	$_1_u_lote_partidaext = $row['partidaext'];
	$_1_u_lote_status = $row['status'];
	$_1_u_lote_fabricante = $row['fabricante'];
	$_1_u_lote_fabricacao = dma($row['fabricacao']);
	$_1_u_lote_vencimento = dma($row['vencimento']);
	$_1_u_lote_observacao = $row['observacao'];
	$_1_u_lote_idpartida = $row['idpartida'];
	//$_1_u_lote_idunidadeold=$row['idunidade'];
	$_1_u_lote_qtdpedida = 0;
	$_1_u_lote_qtdpedida_exp = '';
	$_1_u_lote_qtdprod = 0;
	$_1_u_lote_qtdprod_exp = '';
	$_1_u_lote_qtddisp = 0;
	$_1_u_lote_qtddisp_exp = '';
}

$_idtipounidade = traduzid('unidade', 'idunidade', 'idtipounidade', $idunidadepadrao);

$possuiFormalizacao = false;

if($_1_u_lote_idlote) $possuiFormalizacao = count(FormalizacaoController::buscarFormalizacaoPorIdLote($_1_u_lote_idlote)) > 0;


//Instancia classe para formatacao em json
$JSON = new Services_JSON();

if (empty($_1_u_lote_exercicio)) {
	$_1_u_lote_exercicio = date("Y");
}

if (!empty($_GET['idprodserv']) and empty($_1_u_lote_idprodserv)) {
	$_1_u_lote_idprodserv = $_GET['idprodserv'];
}

function parseFloat($value) {
	return floatval(preg_replace('#^([-]*[0-9\.,\' ]+?)((\.|,){1}([0-9-]{1,3}))*$#e', "str_replace(array('.', ',', \"'\", ' '), '', '\\1') . '.\\4'", $value));
}

function getHideComponents()
{
	global $JSON, $_1_u_lote_idprodserv;

	$sql = "SELECT ifnull(group_concat(idlotetipo),0) as idlotetipo FROM lotetipoprodserv WHERE 1 " . getidempresa('idempresa', 'prodserv') . " AND idprodserv = " . $_1_u_lote_idprodserv;
	$res = d::b()->query($sql) or die("getHideComponents: Erro: " . mysqli_error(d::b()) . "\n" . $sql);
	$row = mysqli_fetch_assoc($res);
	$nrow = mysqli_num_rows($res);

	if ($row["idlotetipo"] != 0) {
		$_sql = "SELECT DISTINCT(campo) as campo FROM lotetipocampos WHERE 1 " . getidempresa('idempresa', 'lotetipo') . " AND idlotetipo in (" . $row["idlotetipo"] . ")";
		$_res = d::b()->query($_sql) or die("getHideComponents: Erro: " . mysqli_error(d::b()) . "\n" . $_sql);
		$_nrow = mysqli_num_rows($_res);
		if ($_nrow > 0) {
			$arrret = array();
			$i = 0;
			while ($_row = mysqli_fetch_assoc($_res)) {
				$arrret[$i]["campo"] = $_row["campo"];
				$i++;
			}
			return $JSON->encode($arrret);
		} else {
			return 0;
		}
	} else {
		return 0;
	}
}

function montaAtalhoObjeto()
{
	global $_1_u_lote_idunidade, $_1_u_lote_tipoobjetosolipor, $_1_u_lote_idobjetosolipor;

	//Recupera a linha do resultado
	$arrObj = getObjeto("vwcliente_visualizarresultados", $_1_u_lote_idobjetosolipor, "idresultado");

	//Unidade relacionada
	$idUnidadeRel = $arrObj["idunidade"];

	//Módulo de Resultados associado
	$modResul = getModuloResultadoPadrao($idUnidadeRel);

	//Nome do Módulo
	$arrUnidade = getObjeto("unidade", $idUnidadeRel);

	$title = $arrObj["nome"] . " - " . $arrUnidade["unidade"];

	return "<label>" . $title . "</label>&nbsp;-&nbsp;<a title='" . $title . "' href='?_acao=u&_modulo=" . $modResul . "&idresultado=" . $_1_u_lote_idobjetosolipor . "' target='_blank'>" . $arrObj["tipoteste"] . "</a>";
}

function getProdutos()
{
	global $idunidadepadrao;

	$sql = "select p.idprodserv
                , p.descr
                ,p.codprodserv
            from prodserv p join unidadeobjeto u 
                            on(u.idunidade = " . $idunidadepadrao . " and u.idobjeto = p.idprodserv and u.tipoobjeto = 'prodserv')
            where p.tipo ='PRODUTO'
             " . getidempresa('p.idempresa', 'prodserv') . "
            and p.status='ATIVO'
            order by p.descr";

	$res = d::b()->query($sql) or die("getProdutos: Erro: " . mysqli_error(d::b()) . "\n" . $sql);

	$arrret = array();
	while ($r = mysqli_fetch_assoc($res)) {
		//monta 2 estruturas json para finalidades (loops) diferentes
		$arrret[$r["idprodserv"]]["descr"] = (($r["descr"]));
		$arrret[$r["idprodserv"]]["codprodserv"] = htmlentities(($r["codprodserv"]));
	}
	//	print_r($arrret); die;
	return $arrret;
}


//Recupera os produtos a serem selecionados para uma nova Formalização
$arrProd = getProdutos();

$jProd = $JSON->encode($arrProd);


function listaprateleira()
{
	global $_1_u_lote_idlote, $idunidadepadrao;

	$tipounidade = traduzid('unidade', 'idunidade', 'idtipounidade', $idunidadepadrao);

	//	$clausulaShare= share::otipo('cb::usr')::unidadeOrigem("l.idunidade");

	//documentos
	$sqls = "select c.idlotelocalizacao,l.idtag,p.idtagdim,concat(l.descricao,' ',concat(case p.coluna 
							when 0 then '0' when 1 then 'A'	when 2 then 'B' when 3 then 'C' when 4 then 'D'	when 5 then 'E'	when 6 then 'F'
						    when 7 then 'G' when 8 then 'H' when 9 then 'I' when 10 then 'J' when 11 then 'K' when 12 then 'L'
						    when 13 then 'M' when 14 then 'N' when 15 then 'O' when 16 then 'P' when 17 then 'Q' when 18 then 'R'
						    when 19 then 'S' when 20 then 'T' when 21 then 'U' when 22 then 'V' when 23 then 'X' when 24 then 'Z'
						     end,' ',p.linha) )as campo
                        from lotelocalizacao c,tag l,tagdim p
                        WHERE c.idlote=" . $_1_u_lote_idlote . "
						and exists (select 1 from unidade a where a.idtipounidade = $tipounidade and a.status = 'ATIVO' and l.idunidade = a.idunidade)
                        and c.tipoobjeto ='tagdim'
                        and p.idtagdim= c.idobjeto
                        and p.idtag = l.idtag";
	echo "<!-- " . $sqls . " -->";
	$ress = d::b()->query($sqls) or die("A Consulta das pessoas falhou :" . mysqli_error(d::b()) . "<br>Sql:" . $sqls);
	$qtdrows = mysqli_num_rows($ress);
	if ($qtdrows > 0) {
		//$y=9999999;
		while ($rows = mysqli_fetch_array($ress)) {
			$y = $y + 1;
?>
			<tr>
				<td align="center"><i class="fa fa-print fa-1x cinza pointer hoverazul fleft" title="Imprimir etiqueta" onclick="showModalEtiqueta(<?= $rows['idlotelocalizacao'] ?>)"></i><?= $rows["campo"] ?></td>
				<td>
					<a class="fa fa-bars fa-1x pointer hoverazul" title="Editar Prateleira" onclick="janelamodal('?_modulo=tag&_acao=u&idtag=<?= $rows['idtag'] ?>')"></a>
				</td>
				<td align="center">
					<a class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-sm pointer ui-droppable" onclick="dlotelocalizacao(<?= $rows["idlotelocalizacao"] ?>)" title="Excluir"></a>
				</td>
			</tr>
		<?
		} //while($rows = mysqli_fetch_array($ress)){	
	} //if($qtdrows> 0){
}

function listapessoa()
{
	global $_1_u_lote_idlote;
	$sqls = "select c.idlotelocalizacao,p.idpessoa,p.nomecurto 
                            from lotelocalizacao c,pessoa p
                                where p.idpessoa = c.idobjeto
                                and c.tipoobjeto ='pessoa'
                                and  c.idlote=" . $_1_u_lote_idlote;

	$ress = d::b()->query($sqls) or die("A Consulta dos funcionarios falhou :" . mysqli_error(d::b()) . "<br>Sql:" . $sqls);
	$qtdrows = mysqli_num_rows($ress);
	if ($qtdrows > 0) {
		//$y=9999;
		while ($rows = mysqli_fetch_array($ress)) {
			$y = $y + 1;
		?>
			<tr>
				<td align="center"><i class="fa fa-print fa-1x cinza pointer hoverazul fleft" title="Imprimir etiqueta" onclick="showModalEtiqueta(<?= $rows['idlotelocalizacao'] ?>)"></i><?= $rows["nomecurto"] ?></td>
				<td>
					<a class="fa fa-bars fa-1x pointer hoverazul" title="Editar Funcionario" onclick="janelamodal('?_modulo=funcionario&_acao=u&idpessoa=<?= $rows['idpessoa'] ?>')"></a>
				</td>
				<td align="center">
					<a class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-sm pointer ui-droppable" onclick="dlotelocalizacao(<?= $rows["idlotelocalizacao"] ?>)" title="Excluir"></a>
				</td>
			</tr>
		<?
		} //while($rows = mysqli_fetch_array($ress)){
	} //if($qtdrows> 0){
}

function listasala()
{
	global $_1_u_lote_idlote, $idunidadepadrao;
	$sqls = " select idtag,concat(descricao,'- TAG ',tag) as tag,c.idlotelocalizacao
                        from tag t,lotelocalizacao c
                        where c.tipoobjeto='tagsala'
                        and c.idobjeto = t.idtag
                        and t.idunidade=" . $idunidadepadrao . "
                        and c.idlote=" . $_1_u_lote_idlote . "
                        order by tag";

	$ress = d::b()->query($sqls) or die("A Consulta das salas falhou :" . mysqli_error(d::b()) . "<br>Sql:" . $sqls);
	$qtdrows = mysqli_num_rows($ress);
	if ($qtdrows > 0) {
		// $y=9999;
		while ($rows = mysqli_fetch_array($ress)) {
			$y = $y + 1;
		?>
			<tr>
				<td align="center"><i class="fa fa-print fa-1x cinza pointer hoverazul fleft" title="Imprimir etiqueta" onclick="showModalEtiqueta(<?= $rows['idlotelocalizacao'] ?>)"></i><?= $rows["tag"] ?></td>
				<td>
					<a class="fa fa-bars fa-1x pointer hoverazul" title="Editar TAG" onclick="janelamodal('?_modulo=tag&_acao=u&idtag=<?= $rows['idtag'] ?>')"></a>
				</td>
				<td align="center">
					<a class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-sm pointer ui-droppable" onclick="dlotelocalizacao(<?= $rows["idlotelocalizacao"] ?>)" title="Excluir"></a>
				</td>
			</tr>
		<?
		} //while($rows = mysqli_fetch_array($ress)){
	} //if($qtdrows> 0){
} //function listasala(){ 

function listabotijao()
{
	global $_1_u_lote_idlote, $idunidadepadrao;
	$sqls = " select idtag,concat(descricao,'- TAG ',tag) as tag,c.idlotelocalizacao
                        from tag t,lotelocalizacao c
                        where c.tipoobjeto='tagbotijao'
                        and c.idobjeto = t.idtag
                        and t.idunidade=" . $idunidadepadrao . "
                        and c.idlote=" . $_1_u_lote_idlote . "
                        order by tag";

	$ress = d::b()->query($sqls) or die("A Consulta dos butijoes falhou :" . mysqli_error(d::b()) . "<br>Sql:" . $sqls);
	$qtdrows = mysqli_num_rows($ress);
	if ($qtdrows > 0) {
		// $y=9999;
		while ($rows = mysqli_fetch_array($ress)) {
			$y = $y + 1;
		?>
			<tr>
				<td align="center"><i class="fa fa-print fa-1x cinza pointer hoverazul fleft" title="Imprimir etiqueta" onclick="showModalEtiqueta(<?= $rows['idlotelocalizacao'] ?>)"></i><?= $rows["tag"] ?></td>
				<td>
					<a class="fa fa-bars fa-1x pointer hoverazul" title="Editar TAG" onclick="janelamodal('?_modulo=tag&_acao=u&idtag=<?= $rows['idtag'] ?>')"></a>
				</td>
				<td align="center">
					<a class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-sm pointer ui-droppable" onclick="dlotelocalizacao(<?= $rows["idlotelocalizacao"] ?>)" title="Excluir"></a>
				</td>
			</tr>
<?
		} //while($rows = mysqli_fetch_array($ress)){
	} //if($qtdrows> 0){   
} //function listabotijao(){

if (!empty($_1_u_lote_idlote)) {
	$jHideComponents = getHideComponents();
}
?>
<style>
	.btassina {
		cursor: pointer;
		border: solid 1px #ccc;
		background: rgb(235, 255, 235);
		color: black;
		height: 30px;
		/*background:url(../img/btbg.gif) repeat-x left top;*/
	}

	.btassinafoco {
		cursor: pointer;
		border: solid 1px #ccc;
		color: black;
		background: rgb(0, 255, 0);
		height: 30px;
	}

	.btretira {
		cursor: pointer;
		border: solid 1px #ccc;
		background: rgb(255, 235, 235);
		color: black;
		height: 30px;
		/*background:url(../img/btbg.gif) repeat-x left top;*/
	}

	.btretirafoco {
		cursor: pointer;
		border: solid 1px #ccc;
		color: black;
		background: rgb(255, 0, 0);
		height: 30px;
	}

	ul.c {
		list-style-type: circle;
	}
</style>


<div id='msalerta' class='hide'>
	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-default">
				<table>
					<tr>
						<td class="nowrap">Alerta:
							<? if ($_1_u_lote_flgalerta == 'P') {
								$corEstrela = 'preto';
								$corValor = 'P';
								$star = "fa-star";
							} else if ($_1_u_lote_flgalerta == 'A') {
								$corEstrela = 'azul';
								$corValor = 'A';
								$star = "fa-star";
							} else if ($_1_u_lote_flgalerta == 'R') {
								$corEstrela = 'roxo';
								$corValor = 'R';
								$star = "fa-star";
							} else {
								$corEstrela = '';
								$corValor = 'N';
								$star = "fa-star-o";
							} ?>

						</td>
						<td>
							<i class="fa <?= $star ?> <?= $corEstrela ?> bold fa-1x btn-lg" name="flgalerta" corvalor='<?= $corValor ?>' style="font-size: 1.6rem;" onclick="altalerta(this)"></i>

						</td>
					</tr>
					<tr>
						<td>Obs:</td>
						<td>
							<input name="lote_idlote" id="lote_idlote" type='hidden' value="<?= $_1_u_lote_idlote ?>">
							<textarea name="lote_alerta" id="lote_alerta" style="width: 760px; height: 41px; margin: 0px;"><?= $_1_u_lote_alerta ?></textarea>
						</td>
					</tr>
				</table>
				<p>
					<br>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<table>
					<tr>
					<input size="8" name="_1_<?= $_acao ?>_lote_idunidade" type="hidden" value="<?= $_1_u_lote_idunidade ?>">

						<th align="right" style="text-align:right; width:1%; color:white;" title="Unidade padrao: <?= $idunidadepadrao ?>"><strong>Produto:</strong></th>
						<td  style="width:33.33%;">
							<?
							if ($_acao == 'i' and !empty($idobjetosolipor) and !empty($tipoobjetosolipor)) {
							?>
								<input type="hidden" name="_1_<?= $_acao ?>_lote_idobjetosolipor" value="<?= $idobjetosolipor ?>">
								<input type="hidden" name="_1_<?= $_acao ?>_lote_tipoobjetosolipor" value="<?= $tipoobjetosolipor ?>">



							<?
							} //if($_acao=='i' and !empty($idobjetosolipor) and !empty($tipoobjetosolipor)){ 
							if ($_acao == 'i' and !empty($_1_u_lote_tipoobjetoprodpara) and !empty($_1_u_lote_idobjetoprodpara)) {
							?>

								<input type="hidden" name="_1_<?= $_acao ?>_lote_tipoobjetoprodpara" value="<?= $_1_u_lote_tipoobjetoprodpara ?>">
								<input type="hidden" name="_1_<?= $_acao ?>_lote_idobjetoprodpara" value="<?= $_1_u_lote_idobjetoprodpara ?>">
								<input type="hidden" name="_1_<?= $_acao ?>_lote_idsolfab" value="<?= $_1_u_lote_idsolfab ?>">
								<input type="hidden" name="_1_<?= $_acao ?>_lote_idpessoa" value="<?= $_1_u_lote_idpessoa ?>">

							<?
							}
							if (empty($_1_u_lote_idlote)) { ?>
								<input type="text" name="_1_<?= $_acao ?>_lote_idprodserv" vnulo cbvalue="<?= $_1_u_lote_idprodserv ?>" value="<?= $arrProd[$_1_u_lote_idprodserv]["descr"] ?>" style="width: 40em;">

							<? } else { ?>

								<label class="alert-warning">
									<?= traduzid("prodserv", "idprodserv", "descr", $_1_u_lote_idprodserv) ?>
									<input type="hidden" name="_1_<?= $_acao ?>_lote_idprodserv" vnulo cbvalue="<?= $_1_u_lote_idprodserv ?>" value="<?= $arrProd[$_1_u_lote_idprodserv]["descr"] ?>" style="width: 40em;">
									<a class="fa fa-bars pointer fade" href="?_modulo=prodserv&_acao=u&idprodserv=<?= $_1_u_lote_idprodserv ?>" target="_blank" title="#<?= $_1_u_lote_idprodserv ?>"></a>
								</label>
							<? } ?>

							<? if ($loteorigem == "Y") { ?>
								<input name="_1_<?= $_acao ?>_lote_idloteorigem" type="hidden" value="<?= $_1_u_lote_idloteorigem ?>" readonly='readonly'>
							<? } ?>
							<input id="idlote" name="_1_<?= $_acao ?>_lote_idlote" type="hidden" value="<?= $_1_u_lote_idlote ?>" readonly='readonly'>
							<input name="_1_<?= $_acao ?>_lote_exercicio" type="hidden" value="<?= $_1_u_lote_exercicio ?>" readonly='readonly'>
						</td>

						<?$_fabricado = traduzid('prodserv', 'idprodserv', 'fabricado', $_1_u_lote_idprodserv);
						if ($_1_u_lote_status == 'ABERTO' and $_fabricado == 'Y') { ?>

						
							<th style="width: 1%; text-align:right; color:white" align="right" hide="_formula">Formula:</th>
							<td hide="_formula">

								<select vnulo class="size20" name="_1_<?= $_acao ?>_lote_idprodservformula">
									<option value="" hidden>Selecionar Fórmula</option>
									<? $sqlf = "select idprodservformula,concat(f.rotulo,' ',ifnull(f.dose,' '),' ',p.conteudo,' ',' (',f.volumeformula,' ',f.un,')') as rotulo
								from prodservformula f join prodserv p on(p.idprodserv=f.idprodserv)
								where 1 " . getidempresa('f.idempresa', 'prodserv') . " 
								and f.status ='ATIVO'
								and f.idprodserv = " . $_1_u_lote_idprodserv;

									fillselect($sqlf, $_1_u_lote_idprodservformula); ?>
								</select>
							</td>

							<? } else {
							if ((!empty($_1_u_lote_idprodservformula) and $_idtipounidade != 16) or (!empty($_1_u_lote_idprodservformula) and $_1_u_lote_status == 'APROVADO')) {

									$sqr = "select idprodservformula, f.rotulo, f.dose, p.conteudo, concat('',f.volumeformula, ' - ' ,f.un, '') as volume
								from prodservformula f join prodserv p on(p.idprodserv=f.idprodserv)
								where  f.idprodservformula =" . $_1_u_lote_idprodservformula;
									$resr = d::b()->query($sqr) or die('Erro ao buscar formula do lote sql=' . $sqr);
									$rowr = mysqli_fetch_assoc($resr);
							?>

							<div>
								<th align="right" style="width: 1%; text-align:right; color:white" hide="_formula">Formula:</th>
								<td  hide="_formula">
									<label class="alert-warning size30" > <?= $rowr['rotulo'] ?></label>
									<input size="8" name="_1_<?= $_acao ?>_lote_idprodservformula" type="hidden" value="<?= $_1_u_lote_idprodservformula ?>" readonly='readonly'>
								</td>

								<th align="right" style="width: 1%; text-align:right; color:white" hide="_formula">Doses:</th>
								<td  hide="_dose" style="width:1%;">
								<label class="alert-warning"> <?= $rowr['dose']?></label>
									<input size="8" name="_1_<?= $_acao ?>_lote_idprodservformula" type="hidden" value="<?= $_1_u_lote_idprodservformula ?>" readonly='readonly'>
								</td>
								
								<th align="right" style="width: 1%; text-align:right; color:white" hide="_formula">Volume:</th>
								<td  hide="_dose" style="width:44%;">
								<label class="alert-warning"> <?= $rowr['volume'] ?></label>
									<input size="8" name="_1_<?= $_acao ?>_lote_idprodservformula" type="hidden" value="<?= $_1_u_lote_idprodservformula ?>" readonly='readonly'>
								</td>
							</div>
							<? } elseif ($_idtipounidade == 16 and !empty($_1_u_lote_idprodserv)) { ?>
								<td align="right" hide="_formula">Formula:</td>
								<td colspan='8' hide="_formula">
									<select class="size20" name="_1_<?= $_acao ?>_lote_idprodservformula">
										<option value="" hidden>Selecionar Fórmula</option>
										<? $sqlf = "select idprodservformula,concat(f.rotulo,' ',ifnull(f.dose,' '),' ',p.conteudo,' ',' (',f.volumeformula,' ',f.un,')') as rotulo
										from prodservformula f join prodserv p on(p.idprodserv=f.idprodserv)
										where 1 " . getidempresa('f.idempresa', 'prodserv') . " 
										and f.status ='ATIVO'
										and f.idprodserv = " . $_1_u_lote_idprodserv;
										fillselect($sqlf, $_1_u_lote_idprodservformula); ?>
									</select>
								</td>
						<? } 	
						} ?>
						
						<td class="d-flex pr-3 align-items-center" style="justify-content:right; padding-right: 1rem; gap: 1rem;"  
						<? if ($_GET["_modulo"] == 'sementerepresentante') {
								echo 'class="hide"';
							} ?>>

							<span align="right" style="text-align:right; color:white;" <? if ($_GET["_modulo"] == 'sementerepresentante') {
												echo 'class="hide"';
											} ?>>Status:</span>
							<? if (empty($_1_u_lote_status)) { ?>
								<input name="_1_<?= $_acao ?>_lote_status" type="hidden" value="ABERTO">
								<label class="alert-warning">ABERTO</label>
								<input name="statusant" type="hidden" value="<?= $_1_u_lote_status ?>">
							<?
							} else {
								$rotulo = getStatusFluxo($pagvaltabela, 'idlote', $_1_u_lote_idlote) ?>
								<label class="alert-warning" title="<?= $_1_u_lote_status ?>" id="statusButton"><?= mb_strtoupper($rotulo['rotulo'], 'UTF-8') ?> </label>
								<input name="_1_<?= $_acao ?>_lote_status" type="hidden" value="<?= $_1_u_lote_status ?>">
								<input name="statusant" type="hidden" value="<?= $_1_u_lote_status ?>">
							<? } ?>
						</td>

				
							<i title="Impressão do lote" class="fa fa-print pull-right fa-lg cinza pointer hoverazul" onclick="janelamodal('report/implote.php?_acao=u&idlote=<?= $_1_u_lote_idlote ?>&_modulo=<?= $_GET['_modulo'] ?>')"></i>
						
					</tr>
				</table>
			</div>
		</div>
	</div>
</div>
<? if (empty($_1_u_lote_idprodserv)) { ?>
	<div class="row">
		<div class="col-md-8">
			<div class="panel panel-default">
				<div class="panel-body">
					<table>
						<tr>
							<th class="col-md-1 nowrap" hide="_qtdpedida" align="right">Quantidade Pedida:</th>
							<td class="col-md-3" hide="_qtdpedida"><input name="_1_<?= $_acao ?>_lote_qtdpedida" class="size7" type="text" value="" vnulo></td>
							<th class="col-md-1" hide="_qtdproduzida" align="right">Quantidade:</th>
							<td class="col-md-3" hide="_qtdproduzida" nowrap><input name="_1_<?= $_acao ?>_lote_qtdprod" class="size7" type="text" value="" vnulo></td>
						</tr>
					</table>
				</div>
			</div>
		</div>
	</div>
<?
}
if ($_1_u_lote_idprodserv) {

	$arrProdserv = getObjeto("prodserv", $_1_u_lote_idprodserv);
	$unidade = $arrProdserv["un"];

	?>
	<link rel="stylesheet" href="/form/css/lote_css.css?version=1.0" />
<div class="row">
    <div class="col-md-8">
        <div class="panel panel-default">
            <div class="panel-body">
			<table>
                    <? if (!empty($_1_u_lote_idobjetosolipor) and $_1_u_lote_tipoobjetosolipor == 'resultado') { ?>
                        <tr hide="_solipor">
                            <th style="width: 10%; text-align:right;">Solicitado por:</th>
                            <td colspan="3"><?= montaAtalhoObjeto() ?></td>

							<th class='nowrap' style="width: 10%; text-align:right;">Isolado em:</th>
                            <?
                            $sq = "select a.idregistro,a.exercicio,st.subtipoamostra,a.idamostra,a.idunidade
										from resultado r join amostra a on(a.idamostra = r.idamostra)
										left join subtipoamostra st on(st.idsubtipoamostra=a.idsubtipoamostra)
										where r.idresultado =" . $_1_u_lote_idobjetosolipor;
                            $resq = d::b()->query($sq) or die("A Consulta do tipo amostra  falhou[1] : " . mysqli_error() . "<p>SQL: $sq");
                            $rowq = mysqli_fetch_assoc($resq);
                            $modamostra = getModuloAmostraPadrao($rowq["idunidade"]);
                            ?>
                            <th colspan="3">
                                <a title="Amostra" href="?_acao=u&_modulo=<?= $modamostra ?>&idamostra=<?= $rowq['idamostra'] ?>" target="_blank">
                                    <?= $rowq['idregistro'] ?>/<?= $rowq['exercicio'] ?> - <?= $rowq['subtipoamostra'] ?>
                                </a>
                            </th>

                            <td>
                                <div class="oSolfab">
                                    <a class="fa fa-search azul pointer hoverazul" data-target="webuiPopover0"></a>
                                </div>
                                <div class="webui-popover-content">
                                    <table>
                                        <?
                                        $sqlv = "select  s.idsolfab,l.partida,l.exercicio,p.nome
													from solfabitem i,solfab s,lote l,pessoa p
													where i.idobjeto =" . $_1_u_lote_idlote . "
													and p.idpessoa = s.idpessoa
													and i.tipoobjeto ='lote'
													and l.idlote = s.idlote
													and s.idsolfab = i.idsolfab order by s.idsolfab";
                                        $resv = d::b()->query($sqlv) or die("erro ao buscar solfab: " . mysqli_error(d::b()) . "<p>SQL: " . $sqlv);
                                        //$y=0;	
                                        while ($r = mysqli_fetch_assoc($resv)) {

                                        ?>
                                            <tr>
                                                <td>Solicitação Fab.:</td>
                                                <td>
                                                    <a class="fa azul pointer hoverazul" title="Solicitação de Fabricação" onclick="janelamodal('?_modulo=solfab&_acao=u&idsolfab=<?= $r["idsolfab"] ?>')">
                                                        <?= $r["idsolfab"] ?>-<?= $r["partida"] ?>/<?= $r["exercicio"] ?>
                                                    </a>
                                                </td>
                                                <td><?= $r["nome"] ?></td>
                                            </tr>
                                        <?
                                        }
									
                                        ?>
                                    </table>
                                </div>
                            </td>

							
                        </tr>

                        <tr>
                            <th style="width: 10%; text-align:right;" hide="_tipoamostra">Tipo de Amostra:</th>
                            <td nowrap colspan="3" hide="_tipoamostra">
                                <input name="_1_<?= $_acao ?>_lote_orgao" size=6 type="text" value="<?= $_1_u_lote_orgao ?>">

                            </td>
                            <th  style="width: 10%; text-align:right;" hide="_tipificacao">Tipificação:</th>
                            <td nowrap hide="_tipificacao">
                                <input name="_1_<?= $_acao ?>_lote_tipificacao" size=6 type="text" value="<?= $_1_u_lote_tipificacao ?>">
                            </td>
                        </tr>
                    <? } ?>
                    <? if ($loteorigem == "Y") { ?>
                        <input name="_1_<?= $_acao ?>_lote_idloteorigem" type="hidden" value="<?= $_1_u_lote_idloteorigem ?>" readonly='readonly'>
                    <? } ?>
                    <input id="idlote" name="_1_<?= $_acao ?>_lote_idlote" type="hidden" value="<?= $_1_u_lote_idlote ?>" readonly='readonly'>
                    <input name="_1_<?= $_acao ?>_lote_exercicio" type="hidden" value="<?= $_1_u_lote_exercicio ?>" readonly='readonly'>
					</tr>

					
					<tr>
					<? if (!empty($_1_u_lote_idobjeto) and $_1_u_lote_tipoobjeto == 'resultado') {
							$sqla = "select a.idamostra,p.idpessoa,p.nome 
							from amostra a,pessoa p,resultado r 
							where p.idpessoa=a.idpessoa 
							and a.idamostra=r.idamostra 
							and r.idresultado=" . $_1_u_lote_idobjeto;

							$resa = d::b()->query($sqla) or die('Erro ao buscar cliente da amostra');
							$rowa = mysqli_fetch_assoc($resa); ?>

							<th style="width: 1%; text-align:right;"><strong>Cliente:</strong></th>
							<td    hide="_cliente"><?= $rowa['nome'] ?></td>

						<? } elseif (!empty($_1_u_lote_idpessoa)) {
							$cliente = traduzid('pessoa', 'idpessoa', 'nome', $_1_u_lote_idpessoa);
						?>

							<th align="right" hide="_cliente" style="width: 1%; text-align:right;">Cliente:</th>
							<td hide="_cliente" nowrap>
								<label  class="alert-warning" hide="_cliente" ><?= traduzid('pessoa', 'idpessoa', 'nome', $_1_u_lote_idpessoa) ?></label>
							</td>

						<? } ?>

					</tr>

					<tr>
						
					<?$_comprado = traduzid('prodserv', 'idprodserv', 'comprado', $_1_u_lote_idprodserv);
							$_fabricado = traduzid('prodserv', 'idprodserv', 'fabricado', $_1_u_lote_idprodserv);
							if (array_key_exists("vervalordolote", getModsUsr("MODULOS"))) {
								if ($_fabricado == "Y") {

									$valorlote = cprod::buscavalorlote($_1_u_lote_idlote, 1, 'Y');
									$testes = cprod::buscartestes($_1_u_lote_idlote);
									$rateios = cprod::buscarateios($_1_u_lote_idlote);

									$valorloteun = ($valorlote + $testes['valor'] + $rateios['valor']) / ($_1_u_lote_qtdprod * $_1_u_lote_valconvori);
									$valLoteFormatado = number_format(tratanumero($valorlote), 4, ',', '.');
									$valLoteUnFormatado = number_format(tratanumero($valorloteun), 4, ',', '.');
								?>
									<th hide="_valor" align="right" style="width: 1%; text-align:right;">Valor R$:</th>
									<td  hide="_valor" nowrap>
										<label class="alert-warning"><? echo number_format(tratanumero($valorloteun), 4, ',', '.') . ' - '; ?> <?= $_1_u_lote_unpadrao ?></label>
										<i id="mostraval" class="fa fa-money hoverazul btn-lg pointer" title="" onclick="mostraval()"></i>
									</td>
								<?
								} else { //fabricado
									if (empty($_1_u_lote_vlrlote)) {
										$vlrlote = 0;
									} else {
										$vlrlote = $_1_u_lote_vlrlote;
									}
								?>
										<th hide="_valor" align="right" style="width: 10%; text-align:right;">Valor R$:</th>
										<td  hide="_valor" nowrap>
											<label class="alert-warning">
												<?= number_format(tratanumero($vlrlote), 4, ',', '.')  . ' - '; ?> <?=$_1_u_lote_unpadrao ?>
											</label>
										</td>
									<?
								}
							}
						?>


						<!-- Acrescentar NNFE (id Compra) Tarefa nº 294246 em 08/01/2020 - Lidiane -->
						<? if (!empty($_1_u_lote_idlote)) { ?>
							<?
							$sqlop = "SELECT NF.nnfe, NF.idnf FROM lote L 
								INNER JOIN nfitem NFI ON L.idnfitem = NFI.idnfitem 
								INNER JOIN nf NF ON NFI.idnf = NF.idnf
								WHERE idlote = " . $_1_u_lote_idlote;

							$resop = d::b()->query($sqlop) or die('Erro ao buscar Nota Fiscal.');
							$rowop = mysqli_fetch_assoc($resop);
							if($rowop > 0){

							}
							if (!empty($_1_u_lote_idlote)) {
								if (empty($_1_u_lote_vlrlote)) {
									$vlrlote = 0;
								} else {
									$vlrlote = $_1_u_lote_vlrlote;
								}
							?>
									<th hide="_NNFe." style="width: 10%; text-align:right;">NNFe.:</th>
									<td class="nowrap">
										<label class="alert-warning">
											<a class="hoverazul pointer" onclick="janelamodal('?_modulo=nfentrada&_acao=u&idnf=<?php echo $rowop['idnf']; ?>')" title="NNFe">
												<?php echo $rowop['nnfe']; ?>
											</a>
										</label>
									</td>
								<?
							}}
							$colspan = 'colspan="4"';
								?>


							
						<th hide="_fabricante" style="width: 10%; text-align:right;">Fabricante:</th>
						<td hide="_fabricante" <?= $colspan ?>><input name="_1_<?= $_acao ?>_lote_fabricante" type="text" value="<?= $_1_u_lote_fabricante ?>" vnulo></td>
						
				<? if ($_idtipounidade == 3) { $colspan = '';?>
					
                        <th hide="_conferido" align="right" style="width: 10%; text-align:right;">Conferido:</th>
                        <td>

                            <? if ($rowf['conferido'] == "Y") { ?>
                                <i style="padding-right: 0px;" class="fa fa-check-square-o btn-lg fa-1x btn-lg pointer" onclick="altconferido('N',<?= $rowf['idlotefracao'] ?>);" alt="Alterar para Não"></i>

                            <? } else { ?>
                                <i style="padding-right: 0px;" class="fa fa-square-o btn-lg fa-1x btn-lg pointer" onclick="altconferido('Y',<?= $rowf['idlotefracao'] ?>);" alt="Alterar para Sim"></i>
							
                            <? } ?>    
							</td>     
							<? } ?>
						</td>
					</tr>

                    <tr>
                    <? if ($_acao == 'u') { 
						
							$prod_especial = traduzid("prodserv", "idprodserv", "especial", $_1_u_lote_idprodserv);
							$msalerta = '';
							
							if($prod_especial == 'Y' && (!empty($lpcoincidentes))){
								$msalerta = 'msalerta()';
							}?>
							<th hide="_partidainterna" align="right" style="width: 10%; text-align:right;" onclick="<?= $msalerta?>"> Part. Interna:</th>
                			<td hide="_partidainterna" nowrap>
                                <input size="8" name="_1_<?= $_acao ?>_lote_partida" id="partida" type="hidden" value="<?= $_1_u_lote_partida ?>" readonly='readonly'>
                                <input size="8" name="_1_<?= $_acao ?>_lote_npartida" id="partida" type="hidden" value="<?= $_1_u_lote_npartida ?>" readonly='readonly'>
                                <input size="8" name="_1_<?= $_acao ?>_lote_spartida" id="partida" type="hidden" value="<?= $_1_u_lote_spartida ?>" readonly='readonly'>
                                <input size="8" name="_1_<?= $_acao ?>_lote_idpartida" id="idpartida" type="hidden" value="<?= $_1_u_lote_idpartida ?>" readonly='readonly'>
                                <?
                                if (!empty($_1_u_lote_idlote)) {
                                    $sqlop = "select * from formalizacao where idlote = " . $_1_u_lote_idlote;
                                    $resop = d::b()->query($sqlop) or die('Erro ao buscar se possui OP.');
                                    $rowop = mysqli_fetch_assoc($resop);
                                    if ($rowop > 0) {
                                        $sqlModulo = "SELECT o.idobjeto FROM unidadeobjeto o 
												JOIN " . _DBCARBON . "._modulo m ON m.modulo = o.idobjeto AND m.modulotipo = 'formalizacao' AND m.status = 'ATIVO'                                          
											WHERE o.tipoobjeto='modulo'               
												AND o.idunidade = '$_1_u_lote_idunidade'";
                                        echo ("<!--" . $sl . "-->");
                                        $qrModulo = d::b()->query($sqlModulo);
                                        $rowModulo = mysqli_fetch_assoc($qrModulo);
                                        $lkmodulo = $rowModulo['idobjeto'];
                                ?>
                                        <label class="alert-warning">
                                            <a class="hoverazul pointer" onclick="janelamodal('?_modulo=<?= $lkmodulo ?>&_acao=u&idformalizacao=<?= $rowop['idformalizacao'] ?>&_idempresa=<?= $rowop['idempresa'] ?>')" title="OP">
                                                <?= $_1_u_lote_partida ?>/<?= $_1_u_lote_exercicio ?>
                                            </a>
											
                                        </label>
                                        <?
                                    } else {
                                        $sqlao = "select * from carbonnovo._lpmodulo where modulo ='ao' and idlp in(" . getModsUsr("LPS") . ")";
                                        $resao = d::b()->query($sqlao);
                                        $qtdao = mysqli_num_rows($resao);

                                        if ($qtdao > 0 and $_1_u_lote_tipoobjetosolipor == 'resultado' and !empty($_1_u_lote_idobjetosolipor)) {
                                            if (!empty($_1_u_lote_alerta)) {
                                                $alerta = ' * ' . $_1_u_lote_alerta;
                                            } else {
                                                $alerta = '';
                                            }
                                        ?>
                                            <label class="alert-warning pointer" title="<?= $alerta ?>" onclick="msalerta()"><?= $_1_u_lote_partida ?>/<?= $_1_u_lote_exercicio ?></label>
                                        <?
                                        } else {
                                        ?>
                                            <label class="alert-warning"><?= $_1_u_lote_partida ?>/<?= $_1_u_lote_exercicio ?></label>
                                <?
                                        }
                                    }
                                }
                                ?>
								<a title="Etiqueta Partida" class="fa fa-print fa-lg cinza pointer hoverazul " onclick="showModalEtiqueta()"></a>
                            </td>
						
									
                            <th  hide="_partida" align="right" nowrap style="width: 10%; text-align:right;">Partida:</th>
                            <td hide="_partida" nowrap style="width: 10%;">
                                <? if ($_1_u_lote_idlote) {
                                    $listarHistorioPartida = LoteController::buscarHistoricoDeAlteração($_1_u_lote_idlote, 'lote', 'partidaext');
                                    $qtdhist = count($listarHistorioPartida);
                                    if ($qtdhist > 0) { ?>
                                        <div id="hist_partida" style="display: none">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th scope="col">De</th>
                                                        <th scope="col">Para</th>
                                                        <th scope="col">Justificativa</th>
                                                        <th scope="col">Por</th>
                                                        <th scope="col">Em</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <? foreach ($listarHistorioPartida as $historico) { ?>
                                                        <tr>
                                                            <? if ($historico['campo'] == "idunidadeest" || $historico['campo'] == "idunidadealerta") { ?>
                                                                <td><label class="alert-warning"><?= traduzid("unidade", "idunidade", "unidade", $historico['valor_old']); ?></label></td>
                                                                <td><label class="alert-warning"><?= traduzid("unidade", "idunidade", "unidade", $historico['valor']); ?></label></td>
                                                            <? } else { ?>
                                                                <td><?= $historico['valor_old'] ?></td>
                                                                <td><?= $historico['valor'] ?></td>
                                                            <? } ?>

                                                            <td><?
                                                                echo $historico['justificativa'];
                                                                ?></td>
                                                            <td><?= $historico['nomecurto'] ?></td>
                                                            <td><?= dmahms($historico['criadoem']) ?></td>
                                                        </tr>
                                                    <?
                                                    } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <? } ?>
                                <? } ?>

                                <?
								$_1_u_lote_partidaext = empty($_1_u_lote_partidaext) ? str_pad($_1_u_lote_npartida, 3, 0, STR_PAD_LEFT) . '/' . substr($_1_u_lote_exercicio, 2) : $_1_u_lote_partidaext; ?>
                                
								
								<? if ($_1_u_lote_partidaext){?>
										<input style="width:100px; background-color:#E0E0E0;" hide="_partext" name="_1_<?= $_acao ?>_lote_partidaext" type="text" value="<?= $_1_u_lote_partidaext ?>" vnulo readonly>
									<?}
                                    else{?>
										<input style="width:100px; <?=$stylepartida?>" hide="_partext" name="_1_<?= $_acao ?>_lote_partidaext" type="text" value="<?= $_1_u_lote_partidaext ?>" vnulo>
									<?}?>

								
                                <? if ($_1_u_lote_idlote) { ?>
                                    <i class="fa fa-pencil preto pointer" onclick="alteravalor('partidaext','<?= $_1_u_lote_partidaext ?>','modulohistorico',<?= $_1_u_lote_idlote ?>,'Partida:')"></i>
									<i class="fa btn-sm fa-info-circle preto pointer tip " onclick="modalhist('hist_partida')"></i>
                                <? } ?>
                            </td>
                        <? } ?>
                        <th hide="_datafabricacao" align="right" style="width: 10%; text-align:right;"> Data de Fabricação:</th>
                		<td hide="_datafabricacao" nowrap style="width:1%;">
                            <div>
                                <? if ($_1_u_lote_idlote) {
                                    $listaHistoricoFab = LoteController::buscarHistoricoDeAlteração($_1_u_lote_idlote, 'lote', 'fabricacao');
                                    $qtdhist = count($listaHistoricoFab);
                                    if ($qtdhist > 0) { ?>
                                        <div id="hist_fab" style="display: none">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th scope="col">De</th>
                                                        <th scope="col">Para</th>
                                                        <th scope="col">Justificativa</th>
                                                        <th scope="col">Por</th>
                                                        <th scope="col">Em</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <? foreach ($listaHistoricoFab as $historico) { ?>
                                                        <tr>
                                                            <? if ($historico['campo'] == "idunidadeest" || $historico['campo'] == "idunidadealerta") { ?>
                                                                <td><label class="alert-warning"><?= traduzid("unidade", "idunidade", "unidade", $historico['valor_old']); ?></label></td>
                                                                <td><label class="alert-warning"><?= traduzid("unidade", "idunidade", "unidade", $historico['valor']); ?></label></td>
                                                            <? } else { ?>
                                                                <td><?= $historico['valor_old'] ?></td>
                                                                <td><?= $historico['valor'] ?></td>
                                                            <? } ?>

                                                            <td><?
                                                                echo $historico['justificativa'];
                                                                ?></td>
                                                            <td><?= $historico['nomecurto'] ?></td>
                                                            <td><?= dmahms($historico['criadoem']) ?></td>
                                                        </tr>
                                                    <?
                                                    } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <? } ?>
                                <? } ?>
                                <input autocomplete="off"
                                    <? if ($_1_u_lote_fabricacao) echo "readonly style='background-color:#E0E0E0;'";
                                    else echo ""; ?>
                                    <? if ($arrProdserv['validadeforn'] == 'Y') {
                                        echo 'vnulo';
                                    } ?>
                                    type="text" name="_1_<?= $_acao ?>_lote_fabricacao" title="Data Fabricacao" class="<? if ($_1_u_lote_fabricacao) echo "";
                                                                                                                        else echo "calendario"; ?> size10" value="<?= $_1_u_lote_fabricacao ?>">
                                <? if ($_1_u_lote_idlote) { ?>
                                    <i class="fa fa-pencil preto pointer" onclick="alteravalor('fabricacao','<?= $_1_u_lote_fabricacao ?>','modulohistorico',<?= $_1_u_lote_idlote ?>,'Fabricação:',true)"></i>
									<i class="fa btn-sm fa-info-circle preto pointer tip " onclick="modalhist('hist_fab')"></i>
                                <? } ?>
                            </div>
                        </td>
						<th hide="_vencimento" align="right" style="width: 7%; text-align:right;"> Vencimento:</th>
						<td hide="_vencimento" nowrap >
							<?
							if ($arrProdserv['validade']) {
								if ($_1_u_lote_fabricacao) {
									$fabricacao = explode("/", $_1_u_lote_fabricacao);
									$dia = ($fabricacao[0] == 31) ? 30 : $fabricacao[0];
									$fabricacao = $fabricacao[2] . "-" . $fabricacao[1] . "-" . $dia;
									$_1_u_lote_vencimento = date('d/m/Y', strtotime("+" . $arrProdserv['validade'] . " MONTH", strtotime($fabricacao)));
								} else {
									$_1_u_lote_vencimento = "";
								}
							?>
								<label class="alert-warning vencimento" title="<?= $_1_u_lote_vencimento ?>" id="statusButton">
									<?= $_1_u_lote_vencimento ?>
								</label>
								<input type="hidden" class="size10" name="validade" value="<?= $arrProdserv['validade'] ?>">
								<input id="input-vencimento" class="vencimento size10 <?= $_GET['_modulo'] == 'lotealmoxarifado' ? 'desabilitado' : 'calendario' ?>" <? $arrProdserv['validadeforn'] == 'Y' ? 'vnulo' : '' ?> type="hidden" name="_1_<?= $_acao ?>_lote_vencimento" value="<?= $_1_u_lote_vencimento ?>" <?= $_GET['_modulo'] == 'lotealmoxarifado' ? 'readonly' : '' ?> />
								<?if($_GET['_modulo'] == 'lotealmoxarifado') { ?>
									<i class="fa fa-pencil preto pointer ml-2" onclick="modalAlterarValidade()" title="Alterar data de vencimento"></i>
									<i id="btn-historico-vencimento" class="fa btn-sm fa-info-circle preto pointer tip ml-2" title="Histórico de vencimento"></i>
								<?}?>
							<?
							} else {
							?>
								<input id="input-vencimento" autocomplete="off" <? $arrProdserv['validadeforn'] == 'Y' ? 'vnulo' : ''?> class="size10 <?= $_GET['_modulo'] == 'lotealmoxarifado' ? 'desabilitado' : 'calendario' ?>" title="Data Vencimento" type="text" name="_1_<?= $_acao ?>_lote_vencimento" value="<?= $_1_u_lote_vencimento ?>" <?= $_GET['_modulo'] == 'lotealmoxarifado' ? 'readonly' : '' ?> />
								<?if($_GET['_modulo'] == 'lotealmoxarifado') { ?>
									<i class="fa fa-pencil preto pointer ml-2" onclick="modalAlterarValidade()" title="Alterar data de vencimento"></i>
									<i id="btn-historico-vencimento" class="fa btn-sm fa-info-circle preto pointer tip ml-2" title="Histórico de vencimento"></i>
								<?}?>
							<? } ?>
						</td>
						<? if($_1_u_lote_substatus) { ?>
							<td>
								<div class="d-flex">
									<label class="alert-warning" title="<?= $_1_u_lote_substatus ?>"><?= $_1_u_lote_substatus ?> </label>
									<i id="btn-historico-substatus" class="fa btn-sm fa-info-circle preto pointer tip ml-1" title="Histórico de substatus"></i>
								</div>
							</td>
						<? } ?>
						<td>
							<? if ($_1_u_lote_prioridade == "ALTA" and !empty($_1_u_lote_idobjetosolipor) and $_1_u_lote_tipoobjetosolipor == 'resultado') { ?>
								<span style="COLOR: RED;"> <b>GUARDADO</b></span>
							<? } ?>
						</td>
                        </tr>
				<!-- Em caso de unidade PDI ou P&D -->
				<tr>
				<? //retirado as quantidades a pedido do Daniel rossi 26-01-2021 hermesp
						if (!empty($idunidadepadrao) and !empty($_1_u_lote_idlote)) {
							$idtipounidade = traduzid("unidade", "idunidade", "idtipounidade", $idunidadepadrao);
							if ($idtipounidade == 13 or $idtipounidade == 16) {
								// GSFE - condição para bloquerar e informar sobre o bloqueio do campo.
								if ($_1_u_lote_status != 'ABERTO' and $_1_u_lote_status != 'PROCESSANDO') {
									$readonly1 = 'readonly';
									$titlet = 'Disponivel no status ABERTO ou PROCESSANDO';
								} else {
									$readonly1 = '';
									$titlet = $readonly;
								}
								// GSFE - condição para bloquerar e informar sobre o bloqueio do campo.
								if ($_1_u_lote_status != 'ABERTO') {
									$readonly2 = 'readonly';
									$titlet2 = 'Disponivel somente no status ABERTO';
								} else {
									$readonly1 = '';
									$titlet2 = $readonly;
								}
						?>
								<tr>
									<th hide="_qtdpedida" align="right" style="width: 10%; text-align:right;">Qtd. Pedida:</th>
									<td hide="_qtdpedida" nowrap>
										<input name="_1_<?= $_acao ?>_lote_qtdpedida" class="size7" type="text" title="<?= $titlet2 ?>" <?= $readonly2 ?> value="<?= recuperaExpoente(tratanumero($_1_u_lote_qtdpedida), $_1_u_lote_qtdpedida_exp) ?>" vnulo>
										<label class="alert-warning"><?= $_1_u_lote_unlote ?></label>
									</td>

									<th hide="_qtdproduzida" align="right" style="width: 10%; text-align:right;">Qtd. Produzida: </th>
									<td hide="_qtdproduzida" nowrap style="width: 1%;">
										<input name="_1_<?= $_acao ?>_lote_qtdprod" class="size7" title="<?= $titlet ?>" type="text" <?= $readonly1 ?> value="<?= recuperaExpoente(tratanumero($_1_u_lote_qtdprod), $_1_u_lote_qtdprod_exp) ?>" vnulo>
										<label class="alert-warning"><?= $_1_u_lote_unlote ?></label>
									</td>
							<?
							}
						}						
							$quantidadeinicial = LoteController::BuscarQtdinilote( $_1_u_lote_idlote );
								?>

								<th hide="_qtdinicial" align="right" style="width: 10%; text-align:right;">Qtd. Inicial:</th>
								<td hide="__qtdinicial" nowrap>
									<input style="background-color:#E0E0E0;" class="size7" readonly  value="<?= number_format(tratanumero($quantidadeinicial['qtdini']), 2, ',', '.')?>"></input>
									<label class="alert-warning"><?= $_1_u_lote_unpadrao?></label>
								</td>

								<?

							if(!empty($_1_u_lote_idprodservformula)){

								$volumeformula = LoteController::BuscarVolumeFormula($_1_u_lote_idprodservformula);

								if($_1_u_lote_unpadrao != $volumeformula['un']){
									$quantidadeproduzida = $volumeformula['volumeformula'] * $_1_u_lote_qtdprod;
									$unidadevolprod = $volumeformula['un'];
								}else{
									$quantidadeproduzida = $_1_u_lote_qtdprod; $unidadevolprod = $_1_u_lote_unpadrao;
								}?>

								<th hide="_volumeprod" style="width: 10%; text-align:right;">Volume Produzido:</th>
								<td hide="_volumeprod" nowrap style="width:1%;">
									<input class="size7" readonly style="background-color:#E0E0E0;" value="<?= number_format(tratanumero($quantidadeproduzida), 2, ',', '.')?>"></input>
									<label class="alert-warning"><?= $unidadevolprod?></label>
								</td>
							<?}?>

							<th hide="_unidade" align="right" nowrap style="width: 10%; text-align:right;">Unidade:</th>
                    		<td style="width:1%;">
                        		<? if ($_acao == 'i') { ?>
                            	<input size="8" name="_1_<?= $_acao ?>_lote_idunidade" type="hidden" value="<?= $_1_u_lote_idunidade ?>">
                        		<? } else { ?>
                            		<input size="8" name="_1_<?= $_acao ?>_lote_idunidade" type="hidden" value="<?= $_1_u_lote_idunidade ?>">
                        		<? }
                        		$strunidade = traduzid('unidade', 'idunidade', 'unidade', $idunidadepadrao);
                        		?>

                        <label class="alert-warning">
                            <? echo (traduzid('unidade', 'idunidade', 'unidade', $idunidadepadrao)); ?>
                        </label>
                    </td>


						<?
						$sqlf = "select f.* from lotefracao f  where f.idunidade=" . $idunidadepadrao . " and f.idlote =" . $_1_u_lote_idlote;
						$resf = d::b()->query($sqlf) or die("Erro ao buscar fracoes:" . mysqli_error());
						$numf = mysqli_num_rows($resf);
						echo ("<!-- " . $sqlf . " -->");
						$arrunori = getObjeto('unidade', $idunidadepadrao);

						if ($numf > 0) {
							while ($rowf = mysqli_fetch_assoc($resf)) {
								if ($rowf["qtd"] < 0) {
									$rowf["qtd"] = 0;
								}?>


				</tr>

                <tr>		
                    <th hide="_qtddisp"  style="width: 10%; text-align:right;">Qtd. Disponível:</th>
                    <td style="width:1%;" hide="_qtddisp" nowrap title="<?= number_format(tratanumero($rowf["qtdini"]), 2, ',', '.'); ?>">

                        <?
                        $unestoque = $prodservclass->getUnEstoque($_1_u_lote_idprodserv, $idunidadepadrao, $_1_u_lote_converteest, $_1_u_lote_unpadrao, $_1_u_lote_unlote);
                        if (
                            strpos(strtolower($rowf['qtd_exp']), "d")
                            or strpos(strtolower($rowf['qtd_exp']), "e")
                        ) {
                            $vlst = recuperaExpoente(tratanumero($rowf["qtd"]), $rowf['qtd_exp']);
                            $vlproduzida = recuperaExpoente(tratanumero($_1_u_lote_qtdprod), $_1_u_lote_qtdprod_exp);
                            $stvalor = $vlst . ' - ' . $_1_u_lote_unpadrao;
                            $stqtdproduzida =    $vlproduzida . ' - ' . $_1_u_lote_unpadrao;
                            $nund = explode("d", $vlst);
                            $nune = explode("e", $vlst);
                            if (!empty($nund[1])) {
                                $vlfim = $nund[0];
                                $vlfim1 = "d" . $nund[1];
                            } else {
                                $vlfim = $nune[0];
                                $vlfim1 = "e" . $nune[1];
                            }
                        } else {
                            $qtdfr = $prodservclass->getEstoqueLote($rowf['idlotefracao']);
                            if ($qtdfr < 0) {
                                $qtdfr = 0;
                            }
                            $stvalor = number_format(tratanumero($qtdfr), 2, ',', '.') . ' - ' . $_1_u_lote_unpadrao;
                            $stqtdproduzida = number_format(tratanumero($_1_u_lote_qtdprod * $_1_u_lote_valconvori), 2, ',', '.') . ' - ' . $_1_u_lote_unpadrao;
                            $vlfim = $qtdfr;
                        }
                        ?>

                        <button type="button" <? if ($_1_u_lote_vunpadrao == 'Y' and $arrunori['convestoque'] == 'N' and $_1_u_lote_converteest == 'Y') { ?>style='font-size: 5px;' <? } else { ?>style='border: none; background-color: #faebcc;' <? } ?> class="btn btn-default btn-xs  " <? if ($_1_u_lote_converteest == 'Y' and $arrunori['convestoque'] == 'N') { ?> onclick="alteravunpadrao(<?= $_1_u_lote_idlote ?>,'N')" <? } ?> title="<?= number_format(tratanumero($rowf["qtd"]) , 2, ',', '.') . ' - ' . $_1_u_lote_unpadrao; ?>">
                            <? if (empty($vlst)) { ?>
                                <input type="hidden" name="_loteconsqtdd_" value="<?= number_format(tratanumero($qtdfr), 2, ',', '.') ?>">
                            <? } else { ?>
                                <input type="hidden" name="_loteconsqtdd_" value="<?= $vlst ?>">
                            <? } ?>
                            <?= $stvalor ?>
                        </button>
                        <? if ($_1_u_lote_converteest == 'Y' and $arrunori['convestoque'] == 'N') {
                            $qtdfrR = $prodservclass->getEstoqueLoteReal($rowf['idlotefracao']);

                        ?>
                            <button <? if ($_1_u_lote_vunpadrao == 'N') {
                                    ?>style='font-size: 5px;' <? } else {
                                                            ?>style='border: none; background-color: #faebcc;'
                                <?
                                                            $arrlotefracao = getObjeto('lotefracao', $rowf['idlotefracao']);

                                                            $vlfim = $arrlotefracao["qtd"];
                                                        } ?> type="button" class="btn btn-default btn-xs " onclick="alteravunpadrao(<?= $_1_u_lote_idlote ?>,'Y')" title="<?= number_format(tratanumero($rowf["qtd"]) , 2, ',', '.') . ' - ' . $_1_u_lote_unpadrao; ?>">
                                <?= number_format(tratanumero($rowf["qtd"]) , 2, ',', '.') . ' - ' . $_1_u_lote_unpadrao; ?>
                            </button>
                        <? } ?>

					<td colspan="6" >								
                        <? if (array_key_exists("ajustelote", getModsUsr("MODULOS"))) 
						{ ?>
                            <a class="fa fa-arrow-up btn-lg verde pointer <? if ($_GET["_modulo"] == 'sementerepresentante') {
                                                                                echo 'hide';
                                                                            } ?>" title="Adicionar" onClick="ajustaest('add',<?= $_1_u_lote_idlote ?>,<?= $rowf['idlotefracao'] ?>,<?= $vlfim ?>,<?= "'$vlfim1'" ?>);"></a>
                            <a class="fa fa-arrow-down btn-lg vermelho pointer <? if ($_GET["_modulo"] == 'sementerepresentante') {
                                                                                    echo 'hide';
                                                                                } ?>" title="Retirar" onClick="ajustaest('sub',<?= $_1_u_lote_idlote ?>,<?= $rowf['idlotefracao'] ?>,<?= $vlfim ?>,<?= "'$vlfim1'" ?>);"></a>
                        <? } ?>
                        <a class="fa fa-chain-broken btn-lg  pointer hoverazul <? if ($_GET["_modulo"] == 'sementerepresentante') {
                                                                                    echo 'hide';
                                                                                } ?>" title="Deslocar Lote" onclick="criafr('criarfr',<?= $_1_u_lote_idlote ?>,<?= $rowf['idlotefracao'] ?>,<?= $vlfim ?>, '<?= $vlfim1 ?>', '<?= $_1_u_lote_vencimento ?>');"></a>
                        <? if ($idtipounidade == 13 or $idtipounidade == 16 or $idtipounidade == 5) { ?>
                            <a class="fa fa-sign-out  btn-lg pointer hoverazul <? if ($_GET["_modulo"] == 'sementerepresentante') {
                                                                                    echo 'hide';
                                                                                } ?>" title="Alíquotar Lote" onclick="criafr('aliquotar',<?= $_1_u_lote_idlote ?>,<?= $rowf['idlotefracao'] ?>,<?= $vlfim ?>, '<?= $vlfim1 ?>', '<?= $_1_u_lote_vencimento ?>');"></a>
                            <? }
                        if ($_idtipounidade == 3 and $_1_u_lote_status == 'APROVADO') { //transferencia fiscal
                            $sqlfi = "select * From prodservforn where idprodservori=" . $_1_u_lote_idprodserv . " and status ='ATIVO'";
                            $resfi = d::b()->query($sqlfi) or die("Erro ao buscar se existe fornecedor interno:" . mysqli_error(d::b()));
                            $qtdfiscal = mysqli_num_rows($resfi);

                            if ($qtdfiscal > 0) { ?>
                                <a class="fa fa-mail-forward btn-lg  pointer hoverazul" title="Transferência Fiscal" onclick="tfiscal(<?= $_1_u_lote_idlote ?>,<?= $rowf['idlotefracao'] ?>,<?= $vlfim ?>,'<?= $vlfim1 ?>');"></a>
                        <? }
                        } ?>
                        <?
                        if ($_idtipounidade == 16 or $_idtipounidade == 5) {
                        ?>
                            <a class="fa fa-external-link-square btn-lg pointer hoverazul" title="Migrar Fração" onclick="migrarfr(<?= $_1_u_lote_idlote ?>,<?= $rowf['idlotefracao'] ?>,<?= $vlfim ?>,'<?= $vlfim1 ?>');"></a>
                            &nbsp;&nbsp;&nbsp;&nbsp;
                        <?
                        }
                        ?>
                        <a class="fa fa-search azul btn-lg pointer hoverazul" title="Histórico" onClick="consumo();"></a>
                        &nbsp;&nbsp;&nbsp;&nbsp;

                        <?
                        if (array_key_exists("convercaolote", getModsUsr("MODULOS"))) {

                            $sqlfo = "select * from prodservforn p 
												where idprodserv = " . $_1_u_lote_idprodserv . "
												and status='ATIVO'
												and converteest='Y'";
                            $resfo = d::b()->query($sqlfo) or die("Erro ao buscar se produto tem conversão:" . mysqli_error());
                            $numfo = mysql_num_rows($resfo);
                            if ($numfo > 0) {
                                $converte = 'Y';
                            } else {
                                $converte = 'N';
                            }

                        ?>
                            <a class="fa fa-wrench btn-lg azul pointer hoverazul" title="Editar conversão do lote"'
                                onClick="editarConversaoModal(<?= $_1_u_lote_idlote ?>,<?= tratanumero($_1_u_lote_qtdprod) ?>,'<?= $_1_u_lote_unpadrao ?>','<?= $_1_u_lote_unlote ?>','<?= $_1_u_lote_converteest ?>',<?= tratanumero($_1_u_lote_valconvori) ?>,<?= (empty($_1_u_lote_vlrlote)) ? 0 : tratanumero($_1_u_lote_vlrlote); ?>,'<?= $converte ?>');"></a>
                        <? } ?>
						
						<? if($_GET["_modulo"] == 'lotepesqdess' && (!empty($lpcoincidentes)) && $_1_u_lote_status == 'APROVADO'){?>
							<a class="fa fa-share-square btn-lg azul pointer hoverazul" title="Transferir consumo"' onClick="transferirConsumo('transferirconsumo',<?=$_1_u_lote_idlote?> , <?=$rowf['idlotefracao']?>, <?= $vlfim ?>, '<?= $vlfim1 ?>', '<?= $_1_u_lote_vencimento ?>');"></a>
						<?}?>
					</td>
                </tr>
				<?
							}
						}
						?>
			
                <tr>
                    <th hide="_obs" style="width: 10%; text-align:right;">Observação:</th>
                    <td hide="_obs" colspan="5"><textarea <?= $readonly ?> cols="120" rows="4" name="_1_<?= $_acao ?>_lote_observacao"><?= $_1_u_lote_observacao ?></textarea></td>
                </tr>


                </table>
            </div>
        </div>
    </div>

		<div class="col-md-4 <? if ($_GET["_modulo"] == 'sementerepresentante') {
									echo 'hide';
								} ?>">
			<?
			if ($_1_u_lote_idlote) {
			?>
				<div class="panel panel-default" hide="_local">
					<div class="panel-heading">Local - <?= traduzid('unidade', 'idunidade', 'unidade', $idunidadepadrao) ?></div>
					<div class="panel-body">
						<table>
							<tr>
								<td id="tdfuncionario"><input id="funcionario" class="compacto" type="text" cbvalue placeholder="Selecione"></td>
								<td id="tdtag"><input id="tag" class="compacto" type="text" cbvalue placeholder="Selecione"></td>
								<td id="tdsala"><input id="sala" class="compacto" type="text" cbvalue placeholder="Selecione"></td>
								<td id="tdbotijao"><input id="botijao" class="compacto" type="text" cbvalue placeholder="Selecione"></td>
								<td class="nowrap" style="width: 210px">
									<div class="btn-group nowrap" role="group" aria-label="...">
										<button onclick="showtag()" type="button" class=" btn btn-default fa fa-table hoverlaranja pointer floatright selecionado" title="Selecionar Prateleira" style="margin-right: 8px; border-radius: 4px;">&nbsp;</button>
										<button onclick="showfuncionario()" type="button" class=" btn btn-default fa fa-user fa-1x hoverlaranja pointer floatright " title="Selecionar Funcionário" style="margin-right: 8px; border-radius: 4px;">&nbsp;</button>
										<button onclick="showsala()" type="button" class=" btn btn-default fa fa-bank fa-1x hoverlaranja pointer floatright " title="Selecionar Sala" style="margin-right: 8px; border-radius: 4px;">&nbsp;</button>
										<button onclick="showbotijao()" type="button" class=" btn btn-default fa fa-battery fa-1x hoverlaranja pointer floatright " title="Selecionar Botijão" style="margin-right: 8px; border-radius: 4px;">&nbsp;</button>
									</div>
								</td>
							</tr>
						</table>
						<table class="table table-striped planilha">
							<?
							listaprateleira();
							listapessoa();
							listasala();
							listabotijao();

							?>
						</table>
					</div>
				</div>

				<?/* //Mostrar os Eventos vinculados ao Lote (Lidiane - 06-04-2020)
	$_sql = "select e.idevento, t.eventotipo from evento e join eventotipo t on (e.ideventotipo = t.ideventotipo) where e.modulo = '".$pagvalmodulo."' and e.idmodulo =".$_1_u_lote_idlote;
	$_res = d::b()->query($_sql) or die("Erro ao pesquisar eventos:".mysqli_error());
	$_num = mysql_num_rows($_res);
	if($_num > 0){ ?>
		<div class="panel panel-default" hide="_eventosassoc">
			<div class="panel-heading" data-toggle="collapse" href="#eventosassoc">Eventos Associados:</div>
			<div class="panel panel-default">
				<div class="panel-body">
					<table class='respreto collapse' id='eventosassoc'>
						<tr>
							<td><b>Tipo Evento</b></td>
							<td><b>Id Evento</b></td>
						</tr>
						<?
						while($_r = mysql_fetch_assoc($_res)){?>
							<tr>
								<td><?=$_r["eventotipo"]?></td>
								<td> <a onclick="janelamodal('?_modulo=evento&_acao=u&idevento=<?=$_r["idevento"]?>')"><?=$_r["idevento"]?></a></td>
							</tr>
						<? } ?>
					</table>
				</div>
			</div>
		</div>
	<? } */ ?>

				<!--div>
	<div class="cbupload" hide="_upload" title="Clique ou arraste arquivos para cá" style="width:50%;height:100%;">
		<i class="fa fa-cloud-upload fonte18"></i>
	</div>
    </div-->
			<? } ?>
		</div>
	</div>
	<div class="row">
		<?
		/*
    $sqlf = "select u.unidade,o.idobjeto as modulo,f.* 
        from lotefracao f 
            join unidade u on(u.idunidade = f.idunidade) 
            	 JOIN unidadeobjeto o FORCE INDEX (TIPOOBJETOUNIDADE) ON (o.tipoobjeto = 'modulo' AND f.idunidade = o.idunidade)
                JOIN "._DBCARBON."._modulo m on m.modulo = o.idobjeto and m.modulotipo = 'lote'
            where f.idunidade !=".$idunidadepadrao." and f.idlote =".$_1_u_lote_idlote;
    $resf = d::b()->query($sqlf) or die("Erro ao buscar fracoes:".mysqli_error());
    $cols=8;
   // echo($sqlf);
    $numf = mysqli_num_rows($resf);
	if($numf > 0){
            $cols=4;
            ?>

<div class="col-md-8">
<div class="panel panel-default">
    <div class="panel-heading">Lote(s)</div>
    <div class="panel-body">
        <table  class="table table-striped planilha">
            <tr>
                <th>Qtd</th>
                <th>Unidade</th>               
                <th>Status</th>
            </tr>
<?
        while($rowf=mysqli_fetch_assoc($resf)){
            if(empty($rowf['modulo'])){
                    $link = 'lotealmoxarifado';
            }else{
                    $link = $rowf['modulo'];
            }
?>            
            <tr>
                <td onclick="janelamodal('?_modulo=<?=$link?>&_acao=u&idlote=<?=$rowf['idlote']?>');"  style="cursor: pointer;">
                    <a style="color:blue;">
                        <?=recuperaExpoente(tratanumero($rowf['qtd']),$rowf['qtd_exp']);?>
                    </a>			
                </td>
                <td><?=$rowf['unidade']?></td>
                <td><?=$rowf['status']?></td>
            </tr>
<?
        }
?>            
        </table>
    </div>
</div>
</div>
    <? //reservas do lote
        }
        */
		$sqlr2 = "select n.idnf,l.qtd,n.status,l.idlotereserva
                from lotereserva l 
                        join nfitem i on(i.idnfitem=l.idobjeto and l.tipoobjeto = 'nfitem')
                        join nf n on(n.idnf=i.idnf)
                where l.idlote=" . $_1_u_lote_idlote . " 
                and l.qtd >0
                and l.status='PENDENTE'";
		$resr2 = d::b()->query($sqlr2) or die("Erro ao buscar reservas do lote:" . mysqli_error());
		$numr2 = mysqli_num_rows($resr2);
		if ($numr2 > 0) { ?>

			<div class="col-md-8">
				<div class="panel panel-default" hide="_reservas">
					<div class="panel-heading">Reserva(s)</div>
					<div class="panel-body">
						<table class="table table-striped planilha">
							<tr>
								<th>Pedido</th>
								<th>Qtd</th>
								<th>Status</th>
								<th></th>
							</tr>
							<?
							while ($rowr2 = mysqli_fetch_assoc($resr2)) {
							?>
								<tr>
									<td>
										<a class="pointer" title="Pedido" onclick="janelamodal('?_modulo=pedido&_acao=u&idnf=<?= $rowr2['idnf'] ?>')">
											<?= $rowr2['idnf'] ?>
										</a>
									</td>
									<td><?= $rowr2['qtd'] ?></td>
									<td><?= $rowr2['status'] ?></td>
									<td>
										<i class="fa fa-trash fa-1x cinzaclaro hoververmelho pointer ui-droppable" onclick="inativareserva(<?= $rowr2['idlotereserva'] ?>)" title="Retirar Reserva"></i>
									</td>
								</tr>
							<?
							}
							?>
						</table>
					</div>
				</div>
			</div>

		<?
		}
		?>
	</div>
<?
} //if($_1_u_lote_idlote){
?>

<?
if ($_1_u_lote_idlote) {

	$_inidunidade = $idunidadepadrao;

	if (!empty($_1_u_lote_idprodservformula)) {
		$strff = " and f.idprodservformula=" . $_1_u_lote_idprodservformula . " ";
	} else {
		$strff = " ";
	}

	$sqli = "select l.idlote,fr.idlotefracao,i.qtdi,l.idunidade,p.idprodserv,p.descr,p.un,l.partida,l.exercicio,f.qtdpadraof,fr.qtd as qtddisp,fr.qtd_exp as qtddisp_exp,lc.idobjeto,lc.tipoobjeto,m.modulo,l.fabricacao
			from prodservformula f join prodservformulains i join prodserv p   
			join lote l on(l.idprodserv = p.idprodserv and l.status = 'APROVADO' )
					join lotefracao fr on(l.idlote = fr.idlote and fr.status='DISPONIVEL'  and fr.idunidade=" . $_inidunidade . getidempresa('fr.idempresa', 'lotefracao') . ")
					join unidade u on (u.idunidade = fr.idunidade) 
					join unidadeobjeto o FORCE INDEX (TIPOOBJETOUNIDADE) ON (o.tipoobjeto = 'modulo' AND fr.idunidade = o.idunidade)
					join " . _DBCARBON . "._modulo m on (m.modulo = o.idobjeto and m.modulotipo = 'lote')
					left join lotelocalizacao lc on (l.idlote = lc.idlote)  
			where f.idprodserv =  " . $_1_u_lote_idprodserv . "  
			" . $strff . "
			and i.status='ATIVO'	
			and i.idprodservformula = f.idprodservformula
			and not exists(select 1 from lotecons c where c.idlote =l.idlote and c.status!='INATIVO' and c.idlotefracao=fr.idlotefracao and c.tipoobjeto='lote' and c.idobjeto=" . $_1_u_lote_idlote . ")
			and p.idprodserv = i.idprodserv group by idlote,idlotefracao
			union 
			select  '' as idlote, '' as idlotefracao,i.qtdi, '' as idunidade,p.idprodserv,p.descr,p.un, '' as  partida, '' as exercicio,f.qtdpadraof, '' as  qtddisp, '' as  qtddisp_exp, '' as idobjeto, '' as tipoobjeto, '' as modulo, '' as fabricacao
			from prodservformula f join prodservformulains i join prodserv p   
				
			where f.idprodserv =  " . $_1_u_lote_idprodserv . "  
			 " . $strff . "
			and i.status='ATIVO'	
			and i.idprodservformula = f.idprodservformula
			and p.idprodserv = i.idprodserv 
			and not exists(select 1 from  lote l 	 join lotefracao fr on(l.idlote = fr.idlote and fr.status='DISPONIVEL'  and fr.idunidade=" . $_inidunidade . getidempresa('fr.idempresa', 'lotefracao') . ")
					join unidade u on (u.idunidade = fr.idunidade) 
					join unidadeobjeto o FORCE INDEX (TIPOOBJETOUNIDADE) ON (o.tipoobjeto = 'modulo' AND fr.idunidade = o.idunidade)
					join " . _DBCARBON . "._modulo m on (m.modulo = o.idobjeto and m.modulotipo = 'lote')
					 where (l.idprodserv = p.idprodserv and l.status = 'APROVADO' ) ) order by descr, idlote";

	//echo($sqli);
	$resi = d::b()->query($sqli) or die("A Consulta dos componentes  falhou[1] : " . mysql_error() . "<p>SQL: $sqli");
	//$rowexiste = mysqli_fetch_array($resexiste);
	$qtdrowi = mysqli_num_rows($resi);


?>
	<div class="row">
		<?
		$y = 99;
		if ($qtdrowi > 0 and $_1_u_lote_status == "ABERTO") {
		?>
			<div class="col-md-6">
				<div class="panel panel-default" hide="_complotes">
					<div class="panel-heading">Componentes do lote</div>
					<div class="panel-body">
						<table class="table table-striped planilha">
							<tr>
								<th class="size2">#</th>
								<th class="size3">Produto</th>
								<th class="size1">Partida Interna</th>
								<th class="size1">Fabricação</th>
								<th class="size1">Local</th>
								<th class="size1">Estoque</th>
								<th class="size1">
									Utilizar
									<a class="fa fa-fast-forward fa-1x preto hoververmelho pointer botaoTransferirTodos" title="Inserir todos os itens selecionados" onclick="transferirValores();"></a>
								</th>
								<th class="size1">Utilizando</th>
								<th class="size1">Esgotar</th>
							</tr>
							<?
							$cons = 0;
							while ($rowi = mysqli_fetch_assoc($resi)) {
								$iqtdpadrao = ($_1_u_lote_qtdprod *  $rowi['qtdi']) / $rowi['qtdpadraof'];
								$cons = ($idprodservOld != $rowi["idprodserv"]) ? $cons + 1 : $cons;
								if ($rowi["idprodserv"] != $_idprodserv) {
									$_idprodserv = $rowi["idprodserv"];
									$font = "font-weight: bold;";
									$seta = 'Y';
								} else {
									$font = "font-weight: normal;";
									$seta = 'N';
								}

							?>
								<tr style="<?= $font ?>">
									<!-- Count -->
									<td><?= $cons ?> - </td>
									<!-- Componente -->
									<td><a href="?_modulo=prodserv&_acao=u&idprodserv=<?= $rowi["idprodserv"] ?>" target="_blank"><?= $rowi['descr'] ?></a></td>
									<?
									if (!empty($rowi['idlote'])) {
									?>
										<!-- Lote -->
										<td>
											<a href="?_modulo=<?= $rowi["modulo"] ?>&_acao=u&idlote=<?= $rowi["idlote"] ?>" target="_blank"><?= $rowi['partida'] ?>/<?= $rowi['exercicio'] ?></a>
										</td>
										<!-- Fabricação -->
										<td>
											<?= dma($rowi['fabricacao']) ?>
										</td>
										<!-- Local -->
										<td><? if ($rowp['tipoobjeto'] == "pessoa") {
												echo (traduzid("pessoa", "idpessoa", "nome", $rowp['idobjeto']));
											} elseif ($rowp['tipoobjeto'] == 'tagdim') {
												$sloc = "select p.idtagdim,concat(l.descricao,concat(case p.coluna 
													when 0 then '0' when 1 then 'A'	when 2 then 'B' when 3 then 'C' when 4 then 'D'	when 5 then 'E'	when 6 then 'F'
													when 7 then 'G' when 8 then 'H' when 9 then 'I' when 10 then 'J' when 11 then 'K' when 12 then 'L'
													when 13 then 'M' when 14 then 'N' when 15 then 'O' when 16 then 'P' when 17 then 'Q' when 18 then 'R'
													when 19 then 'S' when 20 then 'T' when 21 then 'U' when 22 then 'V' when 23 then 'X' when 24 then 'Z'
													end,p.linha) )as campo
												from tag l,tagdim p
													where p.idtag = l.idtag 
												and p.idtagdim =" . $rowp['idobjeto'];
												$rel =  d::b()->query($sloc) or die("Erro ao somar a quantidade dos itens:" . mysql_error());

												$rloc = mysqli_fetch_assoc($rel);
												echo ($rloc['campo']);
											} else {
												echo ("Sem localização");
											} ?></td>
										<!-- Estoque -->
										<td style="text-align-last: end;"><?= $rowi['qtddisp'] ?> </td>
										<!-- Utilizar -->
										<td style="text-align-last: end;"><?= round($iqtdpadrao, 2) ?>
											<? if ($seta == 'Y') { ?>
												<i class="fa fa-arrow-right fa-1x hoververmelho transferirvalores" style="margin:3px;" idlote="<?= $rowi['idlote'] ?>" idlotefracao="<?= $rowi['idlotefracao'] ?>" idobjeto="<?= $_1_u_lote_idlote ?>" valor="<?= round($iqtdpadrao, 2) ?>" cssicone="fa fa-arrow-right" title="fa fa-arrow-right" onclick="copiavalor(<?= $rowi['idlote'] ?>, <?= $rowi['idlotefracao'] ?>, <?= $_1_u_lote_idlote ?>, '<?= round($iqtdpadrao, 2) ?>')"></i>
											<? } ?>
										</td>
										<!-- Utilizando -->
										<td>
											<?
											$sco = "select idlotecons,ifnull(qtdd,0) as qtdd,ifnull(qtdd_exp,'') as qtdd_exp  from lotecons where idobjeto = " . $_1_u_lote_idlote . " and status <> 'INATIVO' and tipoobjeto = 'lote' and idlote=" . $rowi["idlote"];
											$rco =  d::b()->query($sco) or die("Erro ao buscar consumo:" . mysql_error());
											$qco = mysqli_num_rows($rco);
											$roco = mysqli_fetch_assoc($rco);
											if ($qco > 0) {
												$strf = "atulizacon(this,'u'," . $rowi["idlote"] . "," . $rowi["idlotefracao"] . "," . $_1_u_lote_idlote . ")";
											} else {
												$strf = "atulizacon(this,'i'," . $rowi["idlote"] . "," . $rowi["idlotefracao"] . "," . $_1_u_lote_idlote . ")";
											}
											if (empty($roco["qtdd"])) {
												$roco["qtdd"] = 0;
											}
											?>
											<input type="text" id="consumo<?= $cons ?>" class="size6" idlotecons="<?= $roco["idlotecons"] ?>" idlotecons="<?= $roco["idlotecons"] ?>" name="lotecons_qtdd" value="<?= recuperaExpoente(tratanumero($roco["qtdd"]), $roco["qtdd_exp"]) ?>" onchange="<?= $strf ?>">
										</td>
										<!-- Esgotar -->
										<td><a title="Esgotar Lote" class="fa fa-minus-circle pointer cinza hoververmelho" onclick="esgotarlote(<?= $rowi['idlotefracao'] ?>)"></a></td>
									<? } else { ?>
										<td colspan="6" style="color: red;"> Não possui lote aprovado.</td>
									<? } ?>
								</tr>
							<?
								$idprodservOld = $rowi["idprodserv"];
							}
							?>
						</table>
					</div>
				</div>
			</div>
		<?
		}

		// MCC - 27/01/2020 - Validar regra de lote unidade com Daniel
		//Hermes -07-08-2020 - o link estava apontando para o modulo de lote errado
		$sqlc = "SELECT li.idlotecons,l.exercicio,l.idlote,li.qtdd,li.qtdd_exp,l.partida,p.descr,p.idprodserv,o.idobjeto,m.modulo,l.fabricacao, li.obs, p.un
			FROM 
			    lotecons li			   
			    join  prodserv p
			    join lote l 
				join lotefracao f on(f.idlote =l.idlote 
				-- and f.idunidade=" . $_inidunidade . "
				)
				join unidade u on (u.idunidade = f.idunidade) 
				join unidadeobjeto o FORCE INDEX (TIPOOBJETOUNIDADE) ON (o.tipoobjeto = 'modulo' AND f.idunidade = o.idunidade)
				join " . _DBCARBON . "._modulo m on m.modulo = o.idobjeto and m.modulotipo = 'lote'
			where p.idprodserv = l.idprodserv
			and li.qtdd >0
			and li.status  NOT IN ('INATIVO','ALIQUOTA')
			and l.idunidade != 14
			and li.idlotefracao = f.idlotefracao
			and li.tipoobjeto='lote'			
			and li.idobjeto  =" . $_1_u_lote_idlote . " 
			order by l.partida";

		$resc = d::b()->query($sqlc) or die("A Consulta dos componentes selecionados falhou[1] : " . mysqli_error() . "<p>SQL: $sqlc");
		//$rowexiste = mysqli_fetch_array($resexiste);
		echo "<!--" . $sqlc . "-->";
		$qtdrow = mysqli_num_rows($resc);
		//$y=99;	

		if ($qtdrow > 0) {
		?>
			<div class="col-md-6">
				<div class="panel panel-default" hide="_compsellotes">
					<div class="panel-heading">Componentes em uso no lote</div>
					<div class="panel-body">
						<table class="table table-striped planilha">
							<tr>
								<th class="size2">#</th>
								<th class="header">Produto</th>
								<th class="header">Partida</th>
								<th class="header">Fabricação</th>
								<th class="header">Qtd</th>
								<th class="header"></th>
								<th class="header"></th>
							</tr>
							<?
							$count = 0;
							while ($rowc = mysqli_fetch_array($resc)) {
								$y = $y + 1;
								$count = ($idprodservOldUso != $rowc["idprodserv"]) ? $count + 1 : $count;
							?>
								<tr class="respreto">
									<td><?= $count ?> - </td>
									<td><a href="?_modulo=prodserv&_acao=u&idprodserv=<?= $rowc["idprodserv"] ?>" target="_blank"><?= $rowc["descr"] ?></a></td>
									<td>
										<a href="?_modulo=<?= $rowc["idobjeto"] ?>&_acao=u&idlote=<?= $rowc["idlote"] ?>" target="_blank"><?= $rowc["partida"] ?> / <?= $rowc['exercicio'] ?></a>
									</td>
									<td>
										<?= dma($rowc['fabricacao']) ?>
									</td>
									<td class="d-flex align-items-center">
										<? if (
												($_1_u_lote_status != 'APROVADO' and $_1_u_lote_status != 'QUARENTENA'  and $_1_u_lote_status != 'LIBERADO'  and $_1_u_lote_status != 'CANCELADO'  and $_1_u_lote_status != 'REPROVADO')
												&& !$possuiFormalizacao
										) { ?>
											<input name="_<?= $y ?>_u_lotecons_idlotecons" size="6" type="hidden" value="<?= $rowc["idlotecons"] ?>">
											<input name="_<?= $y ?>_u_lotecons_qtdd" size="6" type="text" value="<?= recuperaExpoente(tratanumero($rowc["qtdd"]), $rowc["qtdd_exp"]) ?>"> <span title="Unidade padrão" class="ml-2"><?= $rowc['un'] ?></span>
										<? } else {
											echo recuperaExpoente(tratanumero($rowc["qtdd"]), $rowc["qtdd_exp"]); ?> <span title="Unidade padrão" class="ml-2"><?= $rowc['un'] ?></span>
										<? } ?>
									</td>
									<td>
										<? if ($_1_u_lote_status != 'APROVADO' and $_1_u_lote_status != 'QUARENTENA') { ?>
											<i class="fa fa-trash cinzaclaro hoververmelho btn-sm pointer ui-droppable" onclick="excluirlotecons(<?= $rowc['idlotecons'] ?>)" title="Excluir consumo"></i>
										<? } ?>
									</td>
									<td>
										<? if ($rowc['obs']) { ?>
											<span class="fa fa-warning text-warning hoververmelho" data-toggle="tooltip" data-placement="right" title="<?= $rowc['obs'] ?>"></span>
										<? } ?>
									</td>
								</tr>
							<?
								$idprodservOldUso = $rowc["idprodserv"];
							}
							?>
						</table>
					</div>
				</div>
			</div>
		<?
		}

		?>
	</div>
	<?
	//se for uma copia não tem certificado de analise
	//if(empty($_1_u_lote_idloteorigem)){
	//buscar informações para assinatura e tipo do certificado
	$sqlp = "select assinatura,tipocertanalise from prodserv where idprodserv=" . $_1_u_lote_idprodserv;
	$resp = d::b()->query($sqlp) or die("erro ao busca se assina partida sql" . $sqlp);
	$rowp = mysqli_fetch_assoc($resp);

	$sqlx = "select la.idlote 
                        from lote lpt join loteativ la on(la.idlote=lpt.idlote)
                        where lpt.partida='" . $_1_u_lote_partida . "' and lpt.exercicio='" . $_1_u_lote_exercicio . "' limit 1";
	$resx = d::b()->query($sqlx) or die("erro ao busca se assina partida sql" . $sqlx);
	$rowx = mysqli_fetch_assoc($resx);

	if (!empty($rowx['idlote'])) {
		$idlotev = $rowx['idlote'];
	} else {
		$sqlx = "Select a.idlote from lote lpt join  analiselote a  on(a.idlote=lpt.idlote)
                              where  lpt.partida='" . $_1_u_lote_partida . "' and lpt.exercicio='" . $_1_u_lote_exercicio . "' limit 1";
		$resx = d::b()->query($sqlx) or die("erro ao busca se assina partida sql" . $sqlx);
		$rowx = mysqli_fetch_assoc($resx);
		if (!empty($rowx['idlote'])) {
			$idlotev = $rowx['idlote'];
		} else {
			$idlotev = $_1_u_lote_idlote;
		}
	}



	$sqlam = "SELECT lt.idprodserv
						,a.idregistro
						,a.exercicio
						,r.idamostra
						,r.idresultado
						,p.descr AS qst
						,pa.especificacao
						,r.conformidade AS resultado
						,r.status
						,pa.ordem
		  		 	FROM loteativ l JOIN objetovinculo ov ON ov.idobjetovinc = l.idloteativ AND ov.tipoobjetovinc = 'loteativ'
					JOIN resultado r ON r.idresultado = ov.idobjeto AND ov.tipoobjeto = 'resultado'
					JOIN amostra a ON a.idamostra = r.idamostra
					JOIN prodserv p ON p.idprodserv = r.idtipoteste 
					JOIN lote lt ON lt.idlote = l.idlote 
					JOIN analiseqst pa ON pa.idprodserv = lt.idprodserv AND r.idtipoteste = pa.idtipoteste AND pa.status = 'ATIVO'
					WHERE l.idlote = " . $idlotev . "
				ORDER BY pa.ordem";

	$resam = d::b()->query($sqlam) or die("Erro ao buscar informações dos resultados sql=" . $sqlam);
	$qtdan = mysqli_num_rows($resam);

	$sqlb = "select r.idresultado,s.dia,b.idregistro,b.exercicio,concat(n.nucleo,'-',p.descr) as qst,r.conformidade as resultado,r.status,p.idprodserv,pa.especificacao,pa.ordem
			    from 						                      
                            loteativ la,
                            nucleo n,
                            bioensaio b,
                            analise a,
                            servicoensaio s,
                            resultado r 
                            join prodserv p 
                            join lote lt
			    join analiseqst pa on( pa.idprodserv =lt.idprodserv and r.idtipoteste=pa.idtipoteste and pa.status = 'ATIVO')
		    where b.idloteativ=la.idloteativ
                            and n.idnucleo = b.idnucleo
                            and a.objeto ='bioensaio'
                            and a.idobjeto = b.idbioensaio
			    and s.idobjeto = a.idanalise
			    and s.tipoobjeto = 'analise'
			    and r.idservicoensaio = s.idservicoensaio
			    and r.idtipoteste= p.idprodserv
			    and la.idlote=lt.idlote 
			    AND la.idlote = " . $idlotev . "
		    union 
		    select r.idresultado,s.dia,b.idregistro,b.exercicio,concat(b.estudo,'-',p.descr) as qst,r.conformidade as resultado,r.status,p.idprodserv,pa.especificacao,pa.ordem
			    from 
                            
                            loteativ la,
                            bioensaiodes d,
                            bioensaio b2,
                            nucleo n,
                            analise a,
                            bioensaio b,
                            servicoensaio s,
                            resultado r
                            join prodserv p join lote lt
			    join analiseqst pa on( pa.idprodserv =lt.idprodserv and r.idtipoteste=pa.idtipoteste and pa.status = 'ATIVO')
		    where d.idbioensaio=b2.idbioensaio
			    and b2.idloteativ=la.idloteativ
                            and b.idnucleo = n.idnucleo
			    and b.idbioensaio= d.idbioensaioc
                            and a.objeto ='bioensaio'
                            and a.idobjeto = b.idbioensaio
			    and s.idobjeto = a.idanalise
			    and s.tipoobjeto = 'analise'
			    and r.idservicoensaio = s.idservicoensaio
			    and r.idtipoteste= p.idprodserv
			    and la.idlote=lt.idlote 
                            AND la.idlote = " . $idlotev . " order by dia";
	$resb = d::b()->query($sqlb) or die("Erro ao buscar informações dos bioensaio sql=" . $sqlb);
	$qtdb = mysqli_num_rows($resb);
	echo '<!--' . $sqlam . '-->';
	$aceitar = "OK";
	$qtdresp = 0;
	$prod_especial = traduzid("prodserv", "idprodserv", "especial", $_1_u_lote_idprodserv);

	if ($qtdan > 0 or $qtdb > 0) {
		//SE TIVER REGISTRO VINCULADO
		$qtdan = $qtdan + $qtdb;
	}
	?>
	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-default">
				<div class="panel-heading">Tags vinculadas</div>
				<div class="panel-body" style="padding-top: 10px !important;">
					<?
					$tagsVinculadas = LoteController::buscarTagsVinculadasAoLote($_1_u_lote_idlote, $_1_u_lote_idprodserv);
					foreach ($tagsVinculadas['tags'] as $k => $tag) { ?>
						<div class="panel panel-default">
							<div class="panel-heading">
								<?= $k ?>
							</div>
							<div class="panel-body" style="padding-top: 10px !important;">
								<table class="table table-striped planilha">
									<tr>
										<th style="width: 80%;">Tag</th>
										<th style="text-align: center;width: 20%;">
											<button class="btn btn-success btn-xs" onclick="vincularTagAoLote(<?= $_1_u_lote_idlote ?>,this)">Vincular</button>
										</th>
									</tr>
									<?
									foreach ($tag['tags'] as $t => $tagVinculada) {
									?>
										<tr>
											<td style="width: 80%;">
												<?= $tagVinculada['descr'] ?>
											</td>
											<td align="center" style="width: 20%;">
												<?
												$checked = '';
												if (
													(
														in_array($tagVinculada['idtag'], array_map(function ($item) {
															return $item['idTag'];
														}, $tag['tagsVinculadas']))
													) ||
													(
														in_array($tagVinculada['idtag'], array_map(function ($item) {
															return $item['idobjetovinc'];
														}, $tagsVinculadas['tagsVinculadasManualmente']))
													)
												) {
													$checked = 'checked';
													$objetoVinculado = array_filter($tagsVinculadas['tagsVinculadasManualmente'], function ($item) use ($tagVinculada) {
														return $item['idobjetovinc'] == $tagVinculada['idtag'];
													});
												}
												?>
												<input type="checkbox" idobjetovinculo="<?= $objetoVinculado ? $objetoVinculado[0]['idobjetovinculo'] : $tagVinculada['idobjetovinculo'] ?>" idtag="<?= $tagVinculada['idtag'] ?>" <?= $checked ?> title="<?= !$objetoVinculado && $checked ? 'Vínculo configurado na prodserv.' : '' ?>">
											</td>
										</tr>
									<? } ?>
								</table>
							</div>
						</div>
					<? } ?>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-10">
			<div class="panel panel-default" hide="_analiselote">
				<div class="panel-heading">Certificado de Análise
					<i title="Certificado de Análise" class="fa fa-print pull-right fa-lg cinza pointer hoverazul" onclick="janelamodal('form/certanalise.php?_acao=u&idlote=<?= $idlotev ?>')"></i>
				</div>
				<div class="panel-body">
					<?
					$comprado = traduzid('prodserv', 'idprodserv', 'comprado', $_1_u_lote_idprodserv);
					if ($comprado == "Y") { ?>
						<table>
							<tr>
								<td align="right">Fornecedor:</td>
								<td><select <?= $disabled ?> name="_1_<?= $_acao ?>_lote_idfornecedor">
										<option value=""></option>
										<? fillselect("	SELECT p.idpessoa, 
										   p.nome 
									FROM   pessoa p 
										   JOIN prodservforn a 
											 ON a.idpessoa = p.idpessoa 
												AND a.idprodserv = " . $_1_u_lote_idprodserv . " 
									WHERE  p.status = 'ATIVO' 
									 group by p.idpessoa ORDER  BY p.nome", $_1_u_lote_idfornecedor); ?>
									</select>
								</td>
								<td align="right">Fornecedor-produto:</td>
								<td>
									<select <?= $disabled ?> name="_1_<?= $_acao ?>_lote_idprodservforn" class="size35">
										<option value=""></option>
										<? fillselect("	SELECT a.idprodservforn, 
										   concat(p.nome,'-',ifnull(a.codforn,'')) as nomeforn
									FROM   pessoa p 
										   JOIN prodservforn a 
											 ON a.idpessoa = p.idpessoa 
												AND a.idprodserv = " . $_1_u_lote_idprodserv . " 
									WHERE  p.status = 'ATIVO' 
									 and a.status='ATIVO'
									ORDER  BY p.nome", $_1_u_lote_idprodservforn); ?>
									</select>
								</td>
							</tr>
						</table>
					<? } ?>
					<?


					$sqla = "select p.idprodservloteservico,ps.descr,ps.codprodserv,ps.idprodserv,
						a.idregistro
						,a.exercicio
						,r.idamostra
						,r.idresultado
						,r.resultadocertanalise as resultado
						,r.conformidade as conclusao
						,r.status
					from lote l join prodservloteservico p on(p.idprodserv=l.idprodserv  and p.status!='INATIVO') 
					join prodservloteservicoins i on (i.idprodservloteservico=p.idprodservloteservico and i.status='ATIVO')
					join prodserv ps on(ps.idprodserv=i.idprodserv)
					left join  objetovinculo ov ON (ov.idobjetovinc = l.idlote AND ov.tipoobjetovinc = 'lote')
					left join amostra a ON (a.idamostra = ov.idobjeto AND ov.tipoobjeto = 'amostra'  )
					left join resultado r on (a.idamostra = r.idamostra and r.idtipoteste=i.idprodserv)  
					where l.idlote=" . $_1_u_lote_idlote;
					echo "<!--$sqla-->";
					$resa = d::b()->query($sqla) or die("Erro ao selecionar item 3 :" . mysqli_error());
					$qtdan = mysqli_num_rows($resa);
					if ($qtdan > 0) {
					?>

						<table class="table table-striped planilha">
							<tr bgcolor="#F2F2F2">
								<th class="col-md-1">Registro</th>
								<th class="col-md-6">Teste</th>
								<th class="col-md-2">Status</th>
								<th class="col-md-2">Resultado</th>
								<th class="col-md-1">Conclusão</th>
							</tr>

							<? $i = $y;
							while ($rowb = mysqli_fetch_assoc($resa)) {
								$i++;
								if ($rowb['status'] == "ASSINADO") {
									$cortr = "rgba(150, 201, 101,0.5)";
								} elseif ($rowb['status'] == "FECHADO") {
									$cortr = "#B0E2FF";
								} else {
									$cortr = "rgba(208,0,56,0.5)";
								}

								if (empty($rowb['idregistro'])) {
							?>
									<tr>
										<td colspan="5"><i class="fa fa-plus-circle fa-2x  cinzaclaro hoververde pointer" onclick="geraamostra(this,<?= $_1_u_lote_idlote ?>)" title="Gerar testes"></i></td>
									</tr>
								<?
									break;
								} else {
								?>
									<tr style="background-color:<?= $cortr ?>;">
										<td class=" nowrap" align="center">
											<a onclick="janelamodal('?_modulo=resultprod&_acao=u&idresultado=<?= $rowb['idresultado'] ?>')"><?= $rowb['idregistro'] ?> / <?= $rowb['exercicio'] ?></a>
										</td>
										<td class="texto2"><?= $rowb['descr'] ?></td>
										<td><?= $rowb['status'] ?></td>
										<td class="texto2" align="center"><?= $rowb['resultado'] ?></td>
										<td class="texto2" align="center"><?= $rowb['conclusao'] ?></td>
									</tr>
							<?
								}
							}
							?>
						</table>
					<?
					}





					$sqlf = "Select * from analiseqst where  status = 'ATIVO' and idprodservforn is not null and idpessoa is not null and idprodserv = " . $_1_u_lote_idprodserv . " order by ordem asc";
					$resf = d::b()->query($sqlf) or die("Erro ao selecionar item 1 :" . mysqli_error());
					$qtdf = mysqli_num_rows($resf);
					if ($qtdf > 0 and !empty($_1_u_lote_idfornecedor) and !empty($_1_u_lote_idprodservforn)) {
						if (!empty($_1_u_lote_idfornecedor)) {
							$sqla = "Select * from analiseqst where  status = 'ATIVO' and idprodservforn=" . $_1_u_lote_idprodservforn . " and idpessoa=" . $_1_u_lote_idfornecedor . " and idprodserv = " . $_1_u_lote_idprodserv . " order by ordem asc";
							$resa = d::b()->query($sqla) or die("Erro ao selecionar item 2 :" . mysqli_error());
							$qtdan = mysqli_num_rows($resa);
							$qtdresp = 0;
							/*
			$sqldel="delete p.*  from analiselote p,analiseqst a 
					where a.idanaliseqst=p.idanaliseqst and a.idpessoa !=".$_1_u_lote_idfornecedor." and a.idprodserv = ".$_1_u_lote_idprodserv."";
			$resdel= d::b()->query($sqldel) or die("Erro ao retirar questões de outro fornecedor sql=".$sqldel);
*/
						}


						$qtdresp = 0;
					} else {

						$sqla = "Select a.idanaliseqst,a.idempresa,a.idprodserv,a.idpessoa,a.idtipoteste,a.especificacao,a.esperado,a.status,a.ordem,a.qst 
                        from analiseqst a
                            where a.idpessoa is null and a.idprodservforn is null and a.status = 'ATIVO' 
                            and a.idprodserv = " . $_1_u_lote_idprodserv . " order by a.ordem asc";
						echo "<!--$sqla-->";
						$resa = d::b()->query($sqla) or die("Erro ao selecionar item 3 :" . mysqli_error());
						$qtdan = mysqli_num_rows($resa);
						$qtdresp = 0;
					}

					if ($qtdan > 0) {
					?>

						<table class="table table-striped planilha">
							<tr bgcolor="#F2F2F2">
								<th colspan="2" class="col-md-6">Análise</th>
								<th class="col-md-3">Especificações</th>
								<th class="col-md-1">Resultado</th>
								<th class="col-md-2">Conclusão</th>
							</tr>

							<? $i = $y;
							while ($item = mysqli_fetch_array($resa)) {
								$i++;

								/*
					 * Recupera informacoes dos itens avaliados
					 */
								$sqlavalitem = "Select * from analiselote where  idanaliseqst = " . $item["idanaliseqst"] . " and idlote =" . $idlotev;
								//die($sqlavalitem);
								$resavalitem = d::b()->query($sqlavalitem) or die("Erro ao selecionar avalitem :" . mysqli_error() . "- " . $sqlavalitem);
								$ravalitem = mysqli_fetch_assoc($resavalitem);
								$iitens = mysqli_num_rows($resavalitem);

								if ($iitens == 0) {
									$_acaoitem = "i";
								} elseif ($iitens > 0) {
									$_acaoitem = "u";
								} else {
									die("Erro na contagem dos itens");
								}
								//echo $sqlavalitem;

								$cortr = "";
								if ($ravalitem["resultado"] == "CONFORME") {
									$cortr = "rgba(150, 201, 101,0.5)";
									$qtdresp = $qtdresp + 1;
								} elseif ($ravalitem["resultado"] == "NAOCONFORME") {
									$cortr = "rgba(208,0,56,0.5)";
									$qtdresp = $qtdresp + 1;
								} elseif ($ravalitem["resultado"] == "NAOSEAPLICA") {
									$cortr = "rgba(208,0,56,0.5)";
									$qtdresp = $qtdresp + 1;
								} elseif (!empty($ravalitem["resultado"])) {
									$cortr = "rgba(150, 201, 101,0.5)";
									$qtdresp = $qtdresp + 1;
								} else {
									$cortr = "#ffffff";
								}

								$sqlcert = "SELECT r.resultadocertanalise as resultado
								FROM loteativ l JOIN objetovinculo ov ON ov.idobjetovinc = l.idloteativ AND ov.tipoobjetovinc = 'loteativ'		
								JOIN resultado r ON r.idresultado = ov.idobjeto AND ov.tipoobjeto = 'resultado'
								JOIN amostra a ON a.idamostra = r.idamostra   
								JOIN prodserv p ON p.idprodserv = r.idtipoteste	 		
								JOIN lote lt ON lt.idlote = l.idlote			  
								JOIN analiseqst pa ON pa.idprodserv = lt.idprodserv  AND pa.status = 'ATIVO'
						 		JOIN analiseteste te ON te.idanaliseqst = pa.idanaliseqst AND r.idtipoteste = te.idprodserv
							   WHERE l.idlote = " . $idlotev . "											   					
								 AND pa.idanaliseqst = " . $item["idanaliseqst"] . "
								 AND a.idamostra = r.idamostra
							ORDER BY pa.ordem";

								$rescert = d::b()->query($sqlcert);
								$rowcert = mysqli_fetch_assoc($rescert);

								if (empty($ravalitem["resultado"]) && !empty($rowcert['resultado'])) {
									$resultado = $rowcert["resultado"];
								} else {
									$resultado = $ravalitem["resultado"];
								}

								if ($ravalitem["resultado"] != 'NAOSEAPLICA') { // não mostrar quando não se aplica
							?>
									<tr style="background-color:<?= $cortr ?>;">
										<td class="texto2" colspan="2" nowrap="nowrap"><?= $item["qst"] ?></td>
										<td class="texto2"><?= $item["especificacao"] ?></td>
										<td nowrap="nowrap">
											<input type="hidden" name="_a<?= $i ?>_<?= $_acaoitem ?>_analiselote_idanaliselote" value="<?= $ravalitem["idanaliselote"] ?>">
											<input type="hidden" name="_a<?= $i ?>_<?= $_acaoitem ?>_analiselote_idlote" value="<?= $idlotev ?>">
											<input type="hidden" name="_a<?= $i ?>_<?= $_acaoitem ?>_analiselote_idanaliseqst" value="<?= $item["idanaliseqst"] ?>">
											<input type="text" class="size15" name="_a<?= $i ?>_<?= $_acaoitem ?>_analiselote_resultado" value="<?= $resultado ?>">
										</td>
										<td>
											<? if ($rowp['tipocertanalise'] == "DROP") { ?>
												<select class="size10" <?= $disabled ?> name="_a<?= $i ?>_<?= $_acaoitem ?>_analiselote_conclusao">
													<option></option>
													<?
													fillselect("select 'CONFORME','Conforme' union all select 'NAOCONFORME','Não Conforme' union all select 'NAOSEAPLICA','Não Se Aplica'", $ravalitem["conclusao"]);
													?>
												</select>
												<i class="fa fa-arrows-v fa-2x cinzaclaro pointer" data-toggle="collapse" href=".collapse<?= $item["idanaliseqst"] ?>" title="Detalhar"></i>
											<? } else { ?>
												<select class="size10" <?= $disabled ?> name="_a<?= $i ?>_<?= $_acaoitem ?>_analiselote_conclusao">
													<option></option>
													<?
													fillselect("select 'CONFORME','Conforme' union all select 'NAOCONFORME','Não Conforme' union all select 'NAOSEAPLICA','Não Se Aplica'", $ravalitem["conclusao"]);
													?>
												</select>
												<i class="fa fa-arrows-v fa-2x cinzaclaro pointer" data-toggle="collapse" href=".collapse<?= $item["idanaliseqst"] ?>" title="Detalhar"></i>
											<? } ?>
										</td>
									</tr>
									<?
								}
								$sqlam = "SELECT lt.idprodserv
								    ,a.idregistro
									,a.exercicio
									,r.idamostra
									,r.idresultado
									,p.descr as qst
									,pa.especificacao
									,r.resultadocertanalise as resultado
									,r.conformidade as conclusao
									,r.status
									,pa.ordem
								FROM loteativ l JOIN objetovinculo ov ON ov.idobjetovinc = l.idloteativ AND ov.tipoobjetovinc = 'loteativ'		
								JOIN resultado r ON r.idresultado = ov.idobjeto AND ov.tipoobjeto = 'resultado'
								JOIN amostra a ON a.idamostra = r.idamostra   
								JOIN prodserv p ON p.idprodserv = r.idtipoteste	 		
								JOIN lote lt ON lt.idlote = l.idlote			  
								JOIN analiseqst pa ON pa.idprodserv = lt.idprodserv  AND pa.status = 'ATIVO'
						 		JOIN analiseteste te ON te.idanaliseqst = pa.idanaliseqst AND r.idtipoteste = te.idprodserv
							   WHERE l.idlote = " . $idlotev . "											   					
								 AND pa.idanaliseqst = " . $item["idanaliseqst"] . "
								 AND a.idamostra = r.idamostra
							ORDER BY pa.ordem";

								$resam = d::b()->query($sqlam) or die("Erro ao buscar informações dos resultados sql=" . $sqlam);
								$qtdan = mysqli_num_rows($resam);

								$sqlb = "select r.idresultado,s.dia,b.idregistro,b.exercicio,concat(n.nucleo,'-',p.descr) as qst,r.resultadocertanalise as resultado
				,r.conformidade as conclusao,r.status,p.idprodserv,pa.especificacao,pa.ordem
						from 
									loteativ la,
									nucleo n,
									bioensaio b,
									analise a,
									servicoensaio s,									
									resultado r 
									join prodserv p 
									join lote lt
									join analiseqst pa on( pa.idprodserv =lt.idprodserv  and pa.status = 'ATIVO')
									join analiseteste te on(te.idanaliseqst=pa.idanaliseqst and r.idtipoteste=te.idprodserv )							   
					where b.idloteativ=la.idloteativ
									and n.idnucleo = b.idnucleo
									and a.objeto ='bioensaio'
									and a.idobjeto = b.idbioensaio
						and s.idobjeto = a.idanalise
						and s.tipoobjeto = 'analise'
						and r.idservicoensaio = s.idservicoensaio
						and r.idtipoteste= p.idprodserv						
						and pa.idanaliseqst=" . $item["idanaliseqst"] . "
						and la.idlote=lt.idlote 
						AND la.idlote = " . $idlotev . "
					union 
					select r.idresultado,s.dia,b.idregistro,b.exercicio,concat(b.estudo,'-',p.descr) as qst,r.resultadocertanalise as resultado
					,r.conformidade as conclusao,r.status,p.idprodserv,pa.especificacao,pa.ordem
						from 
									
									loteativ la,
									bioensaiodes d,
									bioensaio b2,
									nucleo n,
									analise a,
									bioensaio b,
									servicoensaio s,									
									resultado r
									join prodserv p join lote lt
									join analiseqst pa on( pa.idprodserv =lt.idprodserv  and pa.status = 'ATIVO')
									join analiseteste te on(te.idanaliseqst=pa.idanaliseqst and r.idtipoteste=te.idprodserv )							   
						where d.idbioensaio=b2.idbioensaio
							and b2.idloteativ=la.idloteativ
							and b.idnucleo = n.idnucleo
							and b.idbioensaio= d.idbioensaioc
							and a.objeto ='bioensaio'
							and a.idobjeto = b.idbioensaio
							and s.idobjeto = a.idanalise
							and s.tipoobjeto = 'analise'						
							and pa.idanaliseqst=" . $item["idanaliseqst"] . "
							and r.idservicoensaio = s.idservicoensaio
							and r.idtipoteste= p.idprodserv
							and la.idlote=lt.idlote 
							AND la.idlote = " . $idlotev . " order by dia";
								$resb = d::b()->query($sqlb) or die("Erro ao buscar informações dos bioensaio sql=" . $sqlb);
								$qtdb = mysqli_num_rows($resb);
								if ($qtdan > 0 or $qtdb > 0) {

									while ($rowam = mysqli_fetch_assoc($resam)) {
										if ($rowam['status'] == "ASSINADO") {
											$cortr = "rgba(150, 201, 101,0.5)";
											$qtdresp = $qtdresp + 1;
										} elseif ($rowam['status'] == "FECHADO") {
											$cortr = "#B0E2FF";
											$qtdresp = $qtdresp + 1;
										} else {
											$cortr = "rgba(208,0,56,0.5)";
											if ($rowam['idprodserv'] != 2410 and $rowam['idprodserv'] != 2409 and $prod_especial == 'N') {
												$aceitar = "";
											} else {
												$qtdresp = $qtdresp + 1;
											}
										}
									?>
										<tr class="collapse<?= $item["idanaliseqst"] ?> in">
											<td class=" nowrap" align="center"><a class="pointer" onclick="janelamodal('?_modulo=resultprod&_acao=u&idresultado=<?= $rowam['idresultado'] ?>')"><?= $rowam['idregistro'] ?> / <?= $rowam['exercicio'] ?></a></td>
											<td class="texto2"><?= $rowam['qst'] ?> (<?= $rowam['status'] ?>)</td>
											<td></td>
											<td class="texto2" align="center"><?= $rowam['resultado'] ?></td>
											<td class="texto2" align="center"><?= $rowam['conclusao'] ?></td>
										</tr>
									<?
									}

									//loop resultados do bioensaio
									while ($rowb = mysqli_fetch_assoc($resb)) {
										if ($rowb['status'] == "ASSINADO") {
											$cortr = "rgba(150, 201, 101,0.5)";
											$qtdresp = $qtdresp + 1;
										} elseif ($rowb['status'] == "FECHADO") {
											$cortr = "#B0E2FF";
											$qtdresp = $qtdresp + 1;
										} else {
											$cortr = "rgba(208,0,56,0.5)";
											if ($rowb['idprodserv'] != 2410 and $rowb['idprodserv'] != 2409 and $prod_especial == 'N') {
												$aceitar = "";
											} else {
												$qtdresp = $qtdresp + 1;
											}
										}
									?>
										<tr class="collapse<?= $item["idanaliseqst"] ?> in">
											<td class=" nowrap" align="center"><a onclick="janelamodal('?_modulo=resultprod&_acao=u&idresultado=<?= $rowb['idresultado'] ?>')"><?= $rowb['idregistro'] ?> / <?= $rowb['exercicio'] ?></a></td>
											<td class="texto2"><?= $rowb['qst'] ?><? if ($rowb['dia']) {
																						echo " D" . $rowb['dia'];
																					} else {
																						echo " D0";
																					} ?> (<?= $rowb['status'] ?>)</td>
											<td></td>
											<td class="texto2" align="center"><?= $rowb['resultado'] ?></td>
											<td class="texto2" align="center"><?= $rowb['conclusao'] ?></td>
										</tr>
							<?
									}
								}
							}
							?>
						</table>
					<?
					}

					?>

					<table style="width: 100%;">
						<tr>
							<td><textarea <?= $readonly ?> cols="50" rows="4" name="_1_<?= $_acao ?>_lote_obsanaliseqst"><?= $_1_u_lote_obsanaliseqst ?></textarea></td>
						</tr>
					</table>

					<?
					//if($qtdresp==$qtdan and $aceitar=="OK"){
					?> <table>
						<tr>
							<td>
								<select <?= $disabled ?> name="_1_<?= $_acao ?>_lote_analise" onchange="atualizaanalise(this,'<?= date("d-m-Y") ?>')">
									<option></option>
									<?
									fillselect("select 'ACEITO','Aceito' union all select 'RECUSADO','Recusado'", $_1_u_lote_analise);
									?>
								</select>
							</td>
							<?

							if ($rowp['assinatura'] == 'S') {

								$sqla = "select * from carrimbo 
						where
						 idobjeto = " . $idlotev . " 
						and tipoobjeto in ('lotealmoxarifado','lotelogistica','lotecq','lotemeios','lotediagnostico','lotediagnosticoautogenas','lotesproducaobacterias','lotesproducaofungos','loteproducao','loteretem', 'lote')";
								$resa = d::b()->query($sqla) or die("Erro ao buscar se modulo esta assinado: Erro: " . mysqli_error(d::b()) . "\n" . $sqla);
								$qtda = mysqli_num_rows($resa);
								if ($qtda > 0) {
									$assinado = 'N';
									while ($_rowa = mysqli_fetch_assoc($resa)) {
										if ($_rowa['status'] == 'ASSINADO') {
											$assinado = 'Y';
										}
									}
									$rowa = mysqli_fetch_assoc($resa);
									$idassinadopor	=	$rowa['alteradopor'];
									$statuscarrimbo	=	$rowa['status'];
									$idcarrimbo		=	$rowa['idcarrimbo'];
								}



								//se não foi assinado naum imprime assinatura

								if ($_SESSION["SESSAO"]["ASSINARESULTADO"] == 'Y') {

							?>

									<td style="border: none;">
										&nbsp;&nbsp;&nbsp;
										<? if ((empty($idassinadopor) or $assinado == 'N') and !empty($idlotev)) { ?>
											<button type="button" value="Assinar" id="btassina" class="btn btn-success btn-xs" onClick="assina(<?= $idlotev ?>,'A',<?= $_SESSION["SESSAO"]["IDPESSOA"] ?>,'<?= $statuscarrimbo; ?>','<?= $idcarrimbo; ?>');">
												<i class="fa fa-circle"></i>Assinar
											</button>
										<? } elseif (!empty($idlotev) and (!empty($idassinadopor) or $assinado == 'Y')) { ?>
											<button type="button" value="Retirar" id="btretira" class="btn btn-warning btn-xs" onClick="assina(<?= $idlotev ?>,'R',<?= $_SESSION["SESSAO"]["IDPESSOA"] ?>,'<?= $statuscarrimbo; ?>','<?= $idcarrimbo; ?>');">
												<i class="fa fa-circle"></i>Retirar
											</button>
										<? } ?>
									</td>
								<?
								}
								if ($assinado == 'Y') {
								?>

									<td style="border: none; ">
										<font color="red">Lote já assinado</font>
									</td>
								<?

								} else {
								?>

									<td style="border: none;">
										<font color="red">Lote PENDENTE para assinatura</font>
									</td>
							<?					}
							}

							?>



						</tr>
					</table>

					<?
					//}
					?>
				</div>
			</div>
		</div>
	</div>

	<?
	/**
	 * se estiver marcado que o produto tem EPI vamos colocar o campo para o preenchimento do mesmo
	 * Este campo será obrigatório
	 */

	if ($arrProdserv['epi'] == 'Y') {
	?>
		<div class="row">
			<div class="col-md-12">
				<div class="panel panel-default">
					<div class="panel-heading">Certificado de aprovação (EPI)</div>
					<div class="panel-body">
						<table class="w-100">
							<tr>
								<td lefth>C.A*</td>
							</tr>
							<tr>
								<td hide="_obs" colspan="5">
									<input <?= $_1_u_lote_certificadoepi > 0 ? 'readonly' : '' ?> placeholder=<?= $_1_u_lote_certificadoepi ? $_1_u_lote_certificadoepi : "Insira o valor" ?> value="<?= $_1_u_lote_certificadoepi ?>" type="number" min="0" max="99999" vnulo name="_1_<?= $_acao ?>_lote_certificadoepi">
								</td>
							</tr>
						</table>
					</div>
				</div>
			</div>
		</div>
	<? } ?>


	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-default">
				<div class="panel-heading">Informações do Produto
				</div>
				<table>
					<tr>
						<td colspan="5"><textarea readonly cols="200" rows="7" name="_1_<?= $_acao ?>_lote_infprod"><?= $_1_u_lote_infprod ?></textarea></td>
					</tr>
				</table>
			</div>
		</div>
	</div>
	<?

	if (!empty($_1_u_lote_idlote)) { // trocar p/ cada tela a tabela e o id da tabela
		$_idModuloParaAssinatura = $_1_u_lote_idlote; // trocar p/ cada tela o id da tabela
		require 'viewAssinaturas.php';
	}
	$tabaud = "lote"; //pegar a tabela do criado/alterado em antigo
	require 'viewCriadoAlterado.php';
	?>


	<div id="consumo" style="display: none;">
		<?= $prodservclass->historicolotecons($_1_u_lote_idlote, $idunidadepadrao); ?>
	</div>
	<?

	$sqlf = "select  qtd,qtd_exp from lotefracao f  where f.idunidade=" . $idunidadepadrao . " and f.idlote =" . $_1_u_lote_idlote;
	$resf = d::b()->query($sqlf) or die("Erro ao buscar fracoes 2:" . mysqli_error());
	echo ("<!-- fracao " . $sqlf . " -->");
	$rowff = mysqli_fetch_assoc($resf);
	?>

	<div id="ajustaest" style="display: none;">
		<div class="row">
			<div class="col-md-12">
				<div class="panel panel-default">
					<div class="panel-heading">
						<table>
							<tr>
								<td align="right" id='ajustaestrotulo'></td>
								<td nowrap>
									<input name="" id="ajutaestidlote" type="hidden" size="6" value="">
									<input name="" id="ajutaestidlotefracao" type="hidden" size="6" value="">
									<input name="" id="ajutaestqtd" sQtddisp_exp="<?= $rowff['qtd_exp'] ?>" sQtddisp="<?= $rowff['qtd'] ?>" type="text" size="6" value="0" onkeyup="mostraConsumo(this)" onchange="verificadiluicao(this)">
									<input name="" id="ajutaestqtc" sQtddisp_exp="<?= $rowff['qtd_exp'] ?>" sQtddisp="<?= $rowff['qtd'] ?>" type="text" size="6" value="0" onkeyup="mostraConsumoCred(this)" onchange="verificadiluicao(this)">
									<input name="#name_campo" id="ndroptipo" type="hidden" value="AJUSTE - INVENTARIO" />
								</td>
								<td>
									<label class="alert-warning">
										<? if ($_1_u_lote_vunpadrao == 'Y') {
											echo $_1_u_lote_unpadrao;
										} else {
											echo $_1_u_lote_unlote;
										} ?>
									</label>
								</td>
							</tr>
							<tr>
								<td align="right">Descr.:</td>
								<td colspan="5">
									<!-- textarea name="" id="observ" style="width: 300px; height: 30px;"></textarea -->
									<select id="ndroptipo" class="size10" name="#name_campo">
										<option value=""></option>
										<? fillselect("select 'AJUSTE - ERRO DE BAIXA','AJUSTE - ERRO DE BAIXA' union select 'AJUSTE - INVENTARIO','AJUSTE - INVENTÁRIO' "); ?>
									</select>
								</td>
							</tr>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div id="ajustaestdeb" style="display: none;">
		<div class="row">
			<div class="col-md-12">
				<div class="panel panel-default">
					<div class="panel-heading">
						<table>
							<tr>
								<td align="right" id='ajustaestrotulo'></td>
								<td nowrap>
									<input name="" id="ajutaestidlote" type="hidden" size="6" value="">
									<input name="" id="ajutaestidlotefracao" type="hidden" size="6" value="">
									<input name="" id="ajutaestqtd" sQtddisp_exp="<?= $rowff['qtd_exp'] ?>" sQtddisp="<?= $rowff['qtd'] ?>" type="text" size="6" value="0" onkeyup="mostraConsumo(this)" onchange="verificadiluicao(this)">
									<input name="" id="ajutaestqtc" sQtddisp_exp="<?= $rowff['qtd_exp'] ?>" sQtddisp="<?= $rowff['qtd'] ?>" type="text" size="6" value="0" onkeyup="mostraConsumoCred(this)" onchange="verificadiluicao(this)">
								</td>
								<td>
									<label class="alert-warning">
										<? if ($_1_u_lote_vunpadrao == 'Y') {
											echo $_1_u_lote_unpadrao;
										} else {
											echo $_1_u_lote_unlote;
										} ?>
									</label>
								</td>
							</tr>
							<tr>
								<td align="right">Descr.:</td>
								<td colspan="5">
									<!-- textarea name="" id="observ" style="width: 300px; height: 30px;"></textarea -->
									<select id="ndroptipo" class="size10" name="#name_campo">
										<option value=""></option>
										<? fillselect(LoteController::$statusEstoque); ?>
									</select>
								</td>
							</tr>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div id="criarfr" style="display: none">
		<div class="row">
			<div class="col-md-12">
				<div class="panel panel-default">
					<div class="panel-heading">
						<table>
							<tr>
								<td align="right" id='criafrrotulo'></td>
								<td>
									<input name="" id="criafrqtd" type="text" size="6" value="0" sQtddisp_exp="<?= $rowff['qtd_exp'] ?>" sQtddisp="<?= $rowff['qtd'] ?>" type="text" size="6" value="0" onkeyup="mostraConsumo(this)" onchange="verificadiluicao(this)">
									<input name="" id="criafrun" type="hidden" size="6" value="0">
								</td>
								<td id='mostraunn'>
									<label class="alert-warning">
										<? if ($_1_u_lote_vunpadrao == 'Y') {
											echo $_1_u_lote_unpadrao;
										} else {
											echo $_1_u_lote_unlote;
										} ?>
									</label>
								</td>
								<td nowrap>
									<? $flagao = 'N'; ?>
									<input name="" id="criafridlote" type="hidden" size="6" value="">
									<input name="" id="criafrloteao" type="hidden" size="6" value="<?= $flagao ?>">
									<input name="" id="criafridlotefracao" type="hidden" size="6" value="">
									<select name="" id="ndropunidade" value="" onchange="buscaunidade(this,<?= $_1_u_lote_idlote ?>,<?= $idunidadepadrao ?>)">
										<option value=""></option>
										<? fillselect("select u.idunidade,concat(u.unidade,' - ',e.empresa) as unidade
                                                                from unidade u  
                                                                    join  unidadeobjeto o  
                                                                    on (
                                                                        u.idunidade = o.idunidade 
                                                                        and o.idobjeto = " . $_1_u_lote_idprodserv . " 
                                                                        and o.tipoobjeto = 'prodserv'
                                                                        )
																join empresa e on(e.idempresa=u.idempresa)
                                                                where u.status='ATIVO' and u.requisicao  ='Y'
                                                                -- and u.idempresa=" . $_SESSION["SESSAO"]["IDEMPRESA"] . "
                                                                 and u.idunidade !=" . $idunidadepadrao . "
                                                                order by u.unidade"); ?>
									</select>

								</td>
							</tr>
							<tr>
								<td align="right">Descr:</td>
								<td colspan="3">
									<input name="" id="criafrobs" type="text" class="size20">
								</td>
							</tr>

						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div id="aliquotar" style="display: none">
		<div class="row">
			<div class="col-md-12">
				<div class="panel panel-default">
					<div class="panel-heading">
						<table>
							<tr>
								<td align="right" id='criafrrotulo'></td>
								<td>
									<input name="" id="criafrqtd" type="text" size="6" value="0" sQtddisp_exp="<?= $rowff['qtd_exp'] ?>" sQtddisp="<?= $rowff['qtd'] ?>" type="text" size="6" value="0" onkeyup="mostraConsumo(this)" onchange="verificadiluicao(this)">
									<input name="" id="criafrun" type="hidden" size="6" value="0">
								</td>
								<td id='mostraunn'>
									<label class="alert-warning">
										<? if ($_1_u_lote_vunpadrao == 'Y') {
											echo $_1_u_lote_unpadrao;
										} else {
											echo $_1_u_lote_unlote;
										} ?>
									</label>
								</td>
								<td nowrap>
									<? $flagao = 'Y'; ?>
									<input name="" id="criafridlote" type="hidden" size="6" value="">
									<input name="" id="criafrloteao" type="hidden" size="6" value="<?= $flagao ?>">
									<input name="" id="criafridlotefracao" type="hidden" size="6" value="">
									<select name="" id="ndropunidade" value="" onchange="buscaunidade(this,<?= $_1_u_lote_idlote ?>,<?= $idunidadepadrao ?>)">
										<option value=""></option>
										<? fillselect("select u.idunidade,concat(u.unidade,' - ',e.empresa) as unidade
                                                                from unidade u  
                                                                    join  unidadeobjeto o  
                                                                    on (
                                                                        u.idunidade = o.idunidade 
                                                                        and o.idobjeto = " . $_1_u_lote_idprodserv . " 
                                                                        and o.tipoobjeto = 'prodserv'
                                                                        )
																join empresa e on(e.idempresa=u.idempresa)
                                                                where u.status='ATIVO' and u.requisicao  ='Y'
                                                                -- and u.idempresa=" . $_SESSION["SESSAO"]["IDEMPRESA"] . "
                                                                -- and u.idunidade !=" . $idunidadepadrao . "
                                                                order by u.unidade"); ?>
									</select>

								</td>

								<td>
									<input id="ndropidpessoa" name="" type="text" value="" cbvalue="" />
								</td>

							</tr>

						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div id="transferirconsumo" style="display: none">
		<div class="row">
			<div class="col-md-12">
				<div class="panel panel-default">
					<div class="panel-heading">
						<table>
							<tr>
								<td align="right" id='criafrrotulo'></td>
								<td>
									<input name="" id="criafrqtd" type="text" size="6" value="0" sQtddisp_exp="<?= $rowff['qtd_exp'] ?>" sQtddisp="<?= $rowff['qtd'] ?>" type="text" size="6" value="0" onkeyup="mostraConsumo(this)" onchange="verificadiluicao(this)">
									<input name="" id="criafrun" type="hidden" size="6" value="0">
								</td>
								<td id='mostraunn'>
									<label class="alert-warning">
										<? if ($_1_u_lote_vunpadrao == 'Y') {
											echo $_1_u_lote_unpadrao;
										} else {
											echo $_1_u_lote_unlote;
										} ?>
									</label>
								</td>
								<td nowrap>
								<? $flagao = 'T'; ?>
									<input name="" id="idloteorigem" type="hidden" size="6" value="">
									<input name="" id="transfereconsumo" type="hidden" size="6" value="<?= $flagao ?>">
									<select name="" id="idlotedestino" value="">
										<option value=""></option>
										<? fillselect("SELECT l.idlote, CONCAT('',l.partida,'/',l.exercicio,'') FROM lote l
														JOIN lotefracao lf ON(l.idlote = lf.idlote)
														JOIN formalizacao f ON(f.idlote = l.idlote)
														JOIN prodserv p ON(p.idprodserv = l.idprodserv)
														JOIN tipoprodserv tp ON(tp.idtipoprodserv = p.idtipoprodserv)
															WHERE l.idempresa = 2 
															AND lf.idunidade = 2 
															AND l.flgalerta = 'P'
															AND l.idprodserv = ".$_1_u_lote_idprodserv."
															AND f.status = 'TRIAGEM';");?>
									</select>
								</td>
							</tr>
							<br>
						</table>
					</div>
				</div>
			</div>
			<br>
			<div class="col-md-12">
				<div class="panel panel-default" hide="_compsellotes">
					<div class="panel-heading">Histórico de transferência</div>
					<div class="panel-body">
						<table class="table table-striped planilha">
							<tr>
								<th class="header">Origem</th>
								<th class="header">Destino</th>
								<th class="header">Criadoem</th>
								<th class="header">Criadopor</th>
							</tr>
							<?$vinculopdi = LoteController::BuscaVinculoConsumoPdi($_1_u_lote_idlote);?>
							<?foreach($vinculopdi as $vinculos){?>
								<tr class="respreto">
									<td><?= $vinculos["loteorigem"] ?></td>
									<td>
										<a href="?_modulo=loteproducao&_acao=u&idlote=<?= $vinculos["idlotedestino"]?>" target="_blank"><?= $vinculos["lotedestino"] ?></a>
									</td>
									<td><?= dmahms($vinculos["criadoem"]) ?></td>
									<td><?= $vinculos["criadopor"] ?></td>
								</tr>
							<?}?>
						</table>	
					</div>
				</div>
			</div>
		</div>
	</div>
	<div id="migrarfr" style="display: none">
		<div class="row">
			<div class="col-md-12">
				<div class="panel panel-default">
					<div class="panel-heading">
						<table>
							<tr>
								<td align="right" id='migrarrotulo'></td>
								<td>
									<input name="" id="migrarqtd" type="text" size="6" value="0" sQtddisp_exp="<?= $rowff['qtd_exp'] ?>" sQtddisp="<?= $rowff['qtd'] ?>" type="text" size="6" value="0" onkeyup="mostraConsumo(this)" onchange="verificadiluicao(this)">
									<input name="" id="migrarun" type="hidden" size="6" value="0">
								</td>
								<td id='mgmostraunn'>
									<label class="alert-warning">
										<? echo $_1_u_lote_unpadrao; ?>
									</label>
								</td>
								<td nowrap>
									<input name="" id="migraridlote" type="hidden" size="6" value="">
									<input name="" id="migraridlotefracao" type="hidden" size="6" value="">
									<select name="" id="droplote" value="">
										<option value=""></option>
										<? fillselect("select l.idlote,concat(l.partida,'/',l.exercicio) as partida
									from lote l join lotefracao lf on(lf.idlote=l.idlote  " . getidempresa('lf.idempresa', 'lote') . ")
									where lf.idunidade=" . $idunidadepadrao . "
									and l.idlote!=" . $_1_u_lote_idlote . " 
									AND l.idprodserv = " . $_1_u_lote_idprodserv . " 
									 and l.status ='PROCESSANDO' 
									order by partida"); ?>
									</select>

								</td>
							</tr>

						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div id="bodytfiscal" style="display: none">
		<div class="row">
			<div class="col-md-12">
				<div class="panel panel-default">
					<div class="panel-heading">
						<table>
							<tr>
								<td align="right" id='criafrrotulo'></td>
								<td>
									<input name="" id="criafrqtd" type="text" size="6" value="0" sQtddisp_exp="<?= $rowff['qtd_exp'] ?>" sQtddisp="<?= $rowff['qtd'] ?>" type="text" size="6" value="0" onkeyup="mostraConsumo(this)" onchange="verificadiluicao(this)">
									<input name="" id="criafrun" type="hidden" size="6" value="0">
								</td>
								<td id='mostraunn'>
									<label class="alert-warning">
										<? if ($_1_u_lote_vunpadrao == 'Y') {
											echo $_1_u_lote_unpadrao;
										} else {
											echo $_1_u_lote_unlote;
										} ?>
									</label>
								</td>
								<td nowrap>
									<? if ($idtipounidade == 13 or $idtipounidade == 16) {
										$flagao = 'Y';
									} else {
										$flagao = 'N';
									} ?>
									<input name="" id="criafridlote" type="hidden" size="6" value="">
									<input name="" id="criafrloteao" type="hidden" size="6" value="<?= $flagao ?>">
									<input name="" id="criafridlotefracao" type="hidden" size="6" value="">


								</td>
								<? if ($idtipounidade == 3) { ?>
									<td>
										<select name="" id="ndropidprodservforn" value="">
											<option value=""></option>
											<? fillselect("select f.idprodservforn,pf.nome
												from prodservforn f join prodserv p on(p.idprodserv = f.idprodserv)
												join empresa e on(e.idempresa = p.idempresa)	
												join pessoa pf on(pf.idempresagrupo = e.idempresa)
												where f.idprodservori=" . $_1_u_lote_idprodserv . " 
												and f.status ='ATIVO' order by pf.nome"); ?>
										</select>
									</td>
								<? } ?>
							</tr>

						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
<?
}

?>
<?
$impressoras = "'IMPRESSORA_SEMENTES','IMPRESSORA_ALMOXARIFADO_ZEBRA','IMPRESSORA_ALMOXARIFADO', 'IMPRESSORA_CQ_2','IMPRESSORA_PRODUCAO_2','IMPRESSORA_INCUBACAO'";

$sImp = "SELECT t.tag,t.fabricante,t.modelo,t.varcarbon, concat(e.sigla, '-', t.tag) as siglatag FROM tag t left join empresa e on (e.idempresa = t.idempresa) where t.status = 'ATIVO' and t.varcarbon in (" . $impressoras . ") " . getidempresa('t.idempresa', 'tag') . " ORDER BY t.tag asc";
$reImp = d::b()->query($sImp) or die("A consulta das impressoras falhou!!! : " . mysqli_error() . "<p>SQL: $sImp");
$qImp = mysqli_num_rows($reImp);
if ($qImp > 0) {
	$arrtmp = array();
	$i = 0;
	while ($rImp = mysqli_fetch_assoc($reImp)) {
		$arrtmp[$i]["tag"] = $rImp["tag"];
		$arrtmp[$i]["fabricante"] = $rImp["fabricante"];
		$arrtmp[$i]["modelo"] = $rImp["modelo"];
		$arrtmp[$i]["varcarbon"] = $rImp["varcarbon"];
		$arrtmp[$i]["siglatag"] = $rImp["siglatag"];

		$i++;
	}
	$jArray = $JSON->encode($arrtmp);
} else {
	$jArray = 0;
}

function getJfuncionario()
{
	global $JSON, $_1_u_lote_idunidade;
	$s = "select 
                a.idpessoa
                ,a.nomecurto				
            from pessoa a
            where  a.status ='ATIVO'
            " . getidempresa('idempresa', 'funcionario') . "
                and a.idtipopessoa =1
            order by a.nomecurto asc";

	$rts = d::b()->query($s) or die("getJSetorvinc: " . mysqli_error(d::b()));

	$arrtmp = array();
	$i = 0;
	while ($r = mysqli_fetch_assoc($rts)) {
		$arrtmp[$i]["value"] = $r["idpessoa"];
		$arrtmp[$i]["label"] = $r["nomecurto"];
		$i++;
	}
	return $JSON->encode($arrtmp);
}
function getJtag()
{
	global $JSON, $idunidadepadrao;
	$s = "select p.idtagdim,concat(l.descricao,' ',concat(case p.coluna 
				when 0 then '0' when 1 then 'A'	when 2 then 'B' when 3 then 'C' when 4 then 'D'	when 5 then 'E'	when 6 then 'F'
                when 7 then 'G' when 8 then 'H' when 9 then 'I' when 10 then 'J' when 11 then 'K' when 12 then 'L'
                when 13 then 'M' when 14 then 'N' when 15 then 'O' when 16 then 'P' when 17 then 'Q' when 18 then 'R'
                when 19 then 'S' when 20 then 'T' when 21 then 'U' when 22 then 'V' when 23 then 'X' when 24 then 'Z'
                 end,' ',p.linha) )as campo
                from tag l,tagdim p
           where p.idtag = l.idtag 
           and l.idtagclass=4
           and l.status='ATIVO'
           and l.idunidade=" . $idunidadepadrao . "
             order by campo;";

	$rts = d::b()->query($s) or die("getJSetorvinc: " . mysqli_error(d::b()));

	$arrtmp = array();
	$i = 0;
	while ($r = mysqli_fetch_assoc($rts)) {
		$arrtmp[$i]["value"] = $r["idtagdim"];
		$arrtmp[$i]["label"] = $r["campo"];
		$i++;
	}
	return $JSON->encode($arrtmp);
}

function getJsala()
{
	global $JSON, $idunidadepadrao;
	$s = "select idtag,concat(descricao,'- TAG ',tag) as tag
        from tag where idtagclass=2 and idunidade=" . $idunidadepadrao . " and status='ATIVO' order by descricao;";

	$rts = d::b()->query($s) or die("getJSetorvinc: " . mysqli_error(d::b()));

	$arrtmp = array();
	$i = 0;
	while ($r = mysqli_fetch_assoc($rts)) {
		$arrtmp[$i]["value"] = $r["idtag"];
		$arrtmp[$i]["label"] = $r["tag"];
		$i++;
	}
	return $JSON->encode($arrtmp);
}

function getJbotijao()
{
	global $JSON, $idunidadepadrao;
	$s = "select idtag,concat(descricao,'- TAG ',tag) as tag
            from tag where idtagtipo=10 and idunidade=" . $idunidadepadrao . " and status='ATIVO' order by descricao";

	$rts = d::b()->query($s) or die("getJSetorvinc: " . mysqli_error(d::b()));

	$arrtmp = array();
	$i = 0;
	while ($r = mysqli_fetch_assoc($rts)) {
		$arrtmp[$i]["value"] = $r["idtag"];
		$arrtmp[$i]["label"] = $r["tag"];
		$i++;
	}
	return $JSON->encode($arrtmp);
}

$jFuncionario = "null";
$jPrateleira = "null";
$Jsala = "null";
$Jbotijao = "null";


if (!empty($_1_u_lote_idunidade)) {
	$jFuncionario = getJfuncionario();
	$jPrateleira = getJtag();
	$Jsala = getJsala();
	$Jbotijao = getJbotijao();
}


?>
<script>
	const gestor = !!'<?= strripos(getModsUsr('LPS'), '3870') ?>',
		responsavel = !!'<?= strripos(getModsUsr('LPS'), '2503') ?>';

	var pessoasDisponiveisParaVinculo = <?= json_encode($pessoasDisponiveisParaVinculo) ?>;
	let idLote = '<?= $_1_u_lote_idlote ?>',
		vencimentoLote = '<?= $_1_u_lote_vencimento ?>',
		idProdserv = '<?= $_1_u_lote_idprodserv ?>',
		partida = '<?= $_1_u_lote_partida ?>',
		historicoCampoVencimento = <?= json_encode(LoteController::buscarHistoricoDeAlteração($_1_u_lote_idlote, $_GET['_modulo'], 'vencimento')) ?>,
		historicoCampoSubstatus = <?= json_encode(LoteController::buscarHistoricoDeAlteração($_1_u_lote_idlote, $_GET['_modulo'], 'substatus')) ?>,
		subStatus = '<?= $_1_u_lote_substatus ?>',
		status = '<?= $_1_u_lote_status ?>',
		idfluxostatus = '<?= $_1_u_lote_idfluxostatus ?>';

	$('#btn-historico-vencimento').on('click', function() {
		abrirModalHistoricoVencimento(historicoCampoVencimento);
	});

	$('#btn-historico-substatus').on('click', function() {
		abrirModalHistoricoVencimento(historicoCampoSubstatus, 'Histório de alteração de substatus');
	});

	function showModalEtiqueta(idloc = null) {
		_controleImpressaoModulo({
			modulo: getUrlParameter("_modulo"),
			grupo: 1,
			idempresa: "<?= $_1_u_lote_idempresa ?>" || "1",
			objetos: {
				modulo: getUrlParameter("_modulo"),
				partida: $("input[name=_1_u_lote_partida]").val(),
				exercicio: $("input[name=_1_u_lote_exercicio]").val(),
				idlote: $("[name='_1_u_lote_idlote']").val(),
				vencimento: "<?= $_1_u_lote_vencimento ?>",
				fabricacao: "<?= $_1_u_lote_fabricacao ?>",
				produto: "<?= traduzid("prodserv", "idprodserv", "descr", $_1_u_lote_idprodserv) ?>",
				idloc
			}
		});
	}
	$('.tranalise').click(function() {
		$(this).nextUntil('tr.tranalise').slideToggle(1000);
	});

	$('#tdtag').show();
	$('#tdfuncionario').hide();
	$('#tdsala').hide();
	$('#tdbotijao').hide();

	function showfuncionario() {
		$('#tdfuncionario').show();
		$('#tdtag').hide();
		$('#tdsala').hide();
		$('#tdbotijao').hide();
	}

	function showtag() {
		$('#tdfuncionario').hide();
		$('#tdtag').show();
		$('#tdsala').hide();
		$('#tdbotijao').hide();
	}

	function showsala() {
		$('#tdfuncionario').hide();
		$('#tdtag').hide();
		$('#tdsala').show();
		$('#tdbotijao').hide();
	}

	function showbotijao() {
		$('#tdfuncionario').hide();
		$('#tdtag').hide();
		$('#tdsala').hide();
		$('#tdbotijao').show();
	}

	<? if (!empty($_1_u_lote_idlote)) { ?>
		jHideComponents = <?= $jHideComponents ?>;

		$(document).ready(function() {
			if (jHideComponents != 0) {
				//$("[hide]").hide(); // Voltar quando o cadastro das configurações dos lotetipos estiverem completas
				$.each(jHideComponents, function(i, item) {
					$("[hide=" + item.campo + "]").show();
				});
			} else {
				//$("[hide]").hide(); // Voltar quando o cadastro das configurações dos lotetipos estiverem completas
			}
		});
		var jArray = <?= $jArray ?>;
		console.log(jArray);

		function showModal() {
			$oModal = $(`<div id="modaletiqueta">
						<div class="row">
							<div class="col-md-12" id="imp_content">
								<table style="width:100%;" id="imp_tabela"></table>
								<hr>
								<div class="col-md-12" id="imp_tabela_tipos">
								</div>
								<div class="col-md-12" id="imp_tabela_exemplos">
									<div class="col-md-12 imp_exemplos">
										<div class="col-md-6"></div>
										<div class="col-md-6"></div>
									</div>
								</div>
								<table style="width:100%;">
									<tr>
										<td>
											Qtd. Impressões:
											<input class="size2" id="qtdimp" value="1" title="Quantidade de impressões">
										</td>
										<td style="text-align: end;">
											<button onclick="imprimeEtiqueta(<?= $_1_u_lote_idlote ?>,'<?= $_GET["_modulo"] ?>')" type="button" class="btn btn-primary fa fa-print pointer" title="Imprimir"><span style="margin-left:5px;">Imprimir</span></button>
										</td>
									</tr>
								</table>
							</div>
						</div>
					</div>
				`);
			if (jArray != 0) {
				jArray.forEach((k, m) => {
					$oModal.find("#imp_tabela").append(`<tr><td><input type="radio" name="impEtiqueta" id="impEtiqueta_${k.varcarbon}" value="${k.varcarbon}"></td><td><b>TAG: ${k.siglatag} </b>- ${k.modelo} - ${k.fabricante}</td></tr>`);
					$oModal.find(`#impEtiqueta_${k.varcarbon}`).on('click', function() {
						$("#imp_tipos").remove();
						if (k.varcarbon == 'IMPRESSORA_SEMENTES') {
							$("#imp_tabela_tipos").append(
								$(`<table style="width:100%;" id="imp_tipos">
								<tr>
								<td style="width:1%;"><input type="radio" name="imp_radio" value="tipo_qrcode"></td>
								<td>QR Code - ${k.siglatag} </td>
								
								</tr>
							`)
							)
						} else if (k.varcarbon == 'IMPRESSORA_ALMOXARIFADO_ZEBRA') {
							$("#imp_tabela_tipos").append(
								$(`<table style="width:100%;" id="imp_tipos">
								<tr>
								<td style="width:1%;"><input type="radio" name="imp_radio" value="tipo_qrcode"></td>
								<td>QR Code -  ${k.siglatag} </td>
								
								</tr>
							`)
							)
						} else if (k.varcarbon == 'IMPRESSORA_PRODUCAO_2') {
							$("#imp_tabela_tipos").append(
								$(`<table style="width:100%;" id="imp_tipos">
								<tr>
								<td style="width:1%;"><input type="radio" name="imp_radio" value="tipo_normal"></td>
								<td>Tipo 1 - ${k.siglatag} </td>
								
								</tr>
							`)
							)
						}
						$oModal.find('input[name="imp_radio"]').on('click', function() {
							$(".imp_exemplos").remove();
							switch (this.value) {
								case 'tipo_qrcode':
									$("#imp_tabela_exemplos").append(
										$(`<div class="col-md-12 imp_exemplos">
											<div class="col-md-2"></div>
											<div class="col-md-8" style="border: 1px solid black;padding: 10px;">
												* PARTIDA<br>
												* EXERCÍCIO<br>
												* QR CODE <br>
												<i class="fa fa-qrcode" aria-hidden="true" style="font-size:40px; margin-left:10px;"></i>
											</div>
											<div class="col-md-2"></div>
											</div>`)
									);
									break;
								case 'tipo_normal':
									$("#imp_tabela_exemplos").append(
										$(`<div class="col-md-12 imp_exemplos">
											<div class="col-md-2"></div>
											<div class="col-md-8" style="border: 1px solid black;padding: 10px;">
												* PARTIDA &nbsp;&nbsp&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; * PARTIDA <br>
												* /EXERCÍCIO &nbsp;&nbsp;&nbsp; * /EXERCÍCIO <br>
											</div>
											<div class="col-md-2"></div>
											</div>`)
									);
									break;
								default:
									break;
							}
						});
					});
				});
			} else {
				$oModal.find("#imp_content").append(`<label class="alert-warning">Não há impressora cadastrada para essa empresa!</label>`);
				$oModal.find("table").remove();
				$oModal.find("hr").remove();
			}

			CB.modal({
				titulo: "</strong>Escolha uma Impressora</strong>",
				corpo: [$oModal],
				classe: 'trinta',
			});
		}
		<?
		$sqUn = "select un, descr 
		from unidadevolume where status='A'
		order by un";
		$resUn = d::b()->query($sqUn) or die("Erro ao consultar unidades");
		if (mysqli_num_rows($resUn) > 0) {
			$iUn = 0;
			$aUn = array();
			while ($rUn = mysqli_fetch_assoc($resUn)) {
				$aUn[$iUn]['un'] 	= $rUn['un'];
				$aUn[$iUn]['descr'] = $rUn['descr'];
				$iUn++;
			}
		} else {
			$aUn = [];
		}

		$aUn = json_encode($aUn);
		?>
		jUn = <?= $aUn ?>;

		function editarConversaoModal(...data) {
			let idlote = data[0];
			let qtdprod = data[1].toString().replace(".", ",");
			let unpadrao = data[2];
			let unlote = data[3];
			let converteest = data[4].toString().replace(".", ",");
			let valconvori = data[5].toString().replace(".", ",");
			let vlrlote = data[6].toString().replace(".", ",");
			let converteprod = data[7].toString().replace(".", ",");

			let checked;
			let hide;
			let styleconversao;
			let styleckconversao;
			let stylecorrecao;
			let classdivconversao;
			let classdivcorrecao;
			let marginleft;

			if (converteest == 'Y') {
				checked = 'checked';
				hide = '';
				read = "";
				cor = '#ffffff';
			} else {
				checked = "";
				hide = 'hide';

				read = "readonly='readonly'";
				cor = '#F4F4F4';
			}

			if (converteprod == 'Y') {
				//styleconversao="text-decoration: underline;";
				conversao = '<a class="pointer" style="text-decoration: underline;" title="Clique para editar informações da conversão."  id="checkconversao" checked="Y">CONVERSÃO</a>'
				naotemconversao = "";
				stylecorrecao = "text-decoration: none;";
				classdivconversao = '';
				classdivcorrecao = 'hide';
				marginleft = '116px';

			} else {
				//styleconversao="display: none;";
				conversao = "";
				styleckconversao = "display: none;";
				naotemconversao = '<a class="pointer" style=" color:gray;" title="Conversão não habilitada para este fornecedor no cadastro de produto." onclick="alert(\'Conversão não habilitada para este fornecedor no cadastro de produto\');" id="checknaoconversao">CONVERSÃO</a>';
				stylecorrecao = "text-decoration: underline;";
				classdivconversao = 'hide';
				classdivcorrecao = '';
				marginleft = '116px';
			}

			let $oCabecalho = $(`<div style="text-align-last: justify;margin-left: ${marginleft};margin-right: 141px;">
						<input title="Se marcado faz conversão do estoque (Quantidade Comprada x Quantidade Conversão)" style="${styleckconversao}"  type="checkbox" name="" value="${converteest}" id="convm_converteest" ${checked}>
						${naotemconversao}
						${conversao}					
						<a class='pointer' style="${stylecorrecao}" title="Clique para editar informações da compra" id="checkcorrecao" checked="N" >COMPRA</a>
				</div>`);

			let $oModal = $(`<div class="panel panel-default" style="padding-top: 0px !important;">
		<div class="panel-body" style="padding-top: 0px !important;">
			<div id="val_correcao" class="${classdivcorrecao}">
			<div class="row">
				<div class="col-sm-12">
					<div class="col-sm-5">
						<label for="convm_qtdprod">Quantidade Comprada</label>
						<input type="text"  class="size10" name="" id="convm_qtdprod" value="${qtdprod}">				
					</div>
					<div class="col-sm-7">
						<label for="convm_unlote">Unidade (Comprada)</label><br>
						<select name="" class="size15"  id="convm_unlote"></select>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-12">
					<div class="col-sm-5">
						<label for="convm_valconvori">Quantidade Conversão</label><br>
						<label class="alert-warning">${valconvori}</label>
					</div>
					
					<div class="col-sm-7">
						<label for="convm_unpadrao">Unidade (Padrão)</label><br>
						<label class="alert-warning">${unpadrao}</label>
					</div>
				</div>
			</div>
			</div>
			<div id="val_conversao" class="${classdivconversao}">

			<div class="row">
				<div class="col-sm-12">
					<div class="col-sm-5">
						<label for="convm_qtdprod">Quantidade Comprada</label><br>
						<label class="alert-warning">${qtdprod}</label>			
					</div>
					<div class="col-sm-7">
						<label for="convm_unlote">Unidade (Comprada)</label><br>
						<label class="alert-warning">${unlote}</label>	
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-sm-12">
					<div class="col-sm-5">
						<label for="convm_valconvori">Quantidade Conversão</label>
						<input class="size10"  type="text" ${read} style="background-color:${cor}" name="" id="convm_valconvori" value="${valconvori}">
					</div>
					
					<div class="col-sm-7">
						<label for="convm_unpadrao">Unidade (Padrão)</label><br>
						<select name=""  class="size15"  id="convm_unpadrao"></select>
					</div>
				</div>
			</div>
			</div>
			<hr>
			<div class="row">
				<div id="convm_vlrun" class="col-sm-12 ">
					<div class="col-sm-4">
						<label for="convm_vlrlote">Valor Unitário</label>
						<input type="text"  class="size10" name="" id="convm_vlrlote" value="${vlrlote}" readonly="readonly" style="background-color:#F4F4F4;">
					</div>
				</div>
			</div>
			<br>
			<div class="row">
				<div class="col-sm-12">
					<div class="col-sm-6">
						<!-- button type="button" id="convm_resetar" class="btn btn-warning btn-sm"><i class="fa fa-circle"></i>Resetar</button -->
					</div>
					<div class="col-sm-6 text-right">
						<button type="button" id="convm_salvar" class="btn btn-danger btn-sm"><i class="fa fa-circle"></i>Salvar</button>
					</div>
				</div>
			</div>
		</div>
		</div>
		`);

			let $oUnLote = $oModal.find("#convm_unlote");
			let $oUnPadrao = $oModal.find("#convm_unpadrao");
			let selectedLote = "";
			let selectedPadrao = "";

			for (o of jUn) {
				(o.un == unlote) ? selectedLote = "selected": selectedLote = "";
				(o.un == unpadrao) ? selectedPadrao = "selected": selectedPadrao = "";

				$oUnLote.append(`<option value="${o.un}" ${selectedLote}>${o.descr}</option>`);
				$oUnPadrao.append(`<option value="${o.un}" ${selectedPadrao}>${o.descr}</option>`);
			}


			$oCheckD = $oCabecalho.find("#checkcorrecao");

			$oCheckD.on('click', function() {
				correcao = $('#val_correcao');
				conversao = $('#val_conversao');
				flagconverteest = $("#convm_converteest");

				dc = $(this);

				dc1 = $("#checkconversao");
				/*
					if(dc.is(":checked")){
				*/
				correcao.removeClass('hide');
				conversao.addClass('hide');
				dc.css("text-decoration", "underline");
				dc1.css("text-decoration", "none");
				flagconverteest.attr('disabled', true);
				/*
		}else{
			correcao.addClass('hide');
			conversao.removeClass('hide');	
			dc.val('Y');

		}
		*/
			});


			$oCheckC = $oCabecalho.find("#checkconversao");

			$oCheckC.on('click', function() {
				correcao1 = $('#val_correcao');
				conversao1 = $('#val_conversao');
				flagconverteest = $("#convm_converteest");

				dc1 = $(this);
				dc = $("#checkcorrecao");

				/*
						if(dc1.is(":checked")){
							*/
				conversao1.removeClass('hide');
				correcao1.addClass('hide');
				dc1.css("text-decoration", "underline");
				dc.css("text-decoration", "none");
				flagconverteest.attr('disabled', false);
				/*
			}else{
				conversao1.addClass('hide');
				correcao1.removeClass('hide');
				
				dc1.val('Y');

			}*/
			});
			//debugger
			let $oCheck = $oCabecalho.find("#convm_converteest");

			$oCheck.on('click', function() {
				let v = $('#convm_valconvori');
				//let s = $('#convm_unpadrao'); 

				let c = $(this);

				if (c.is(":checked")) {
					//v.removeClass('hide');
					v.attr('readonly', false);
					v.css('background-color', '#ffffff');

					//s.attr('readonly', false);
					//	s.css('background-color','#ffffff');




					c.val('Y');
				} else {
					//v.addClass('hide');

					v.attr('readonly', true);
					v.css('background-color', '#F4F4F4');

					//	s.attr('readonly', true);
					//	s.css('background-color','#F4F4F4');




					c.val('N');
				}
			});


			let $oSalvar = $oModal.find("#convm_salvar");
			let $oResetar = $oModal.find("#convm_resetar");

			$oSalvar.on('click', () => {
				CB.post({
					objetos: {
						"_cvest_u_lote_idlote": idlote,
						"_cvest_u_lote_qtdprod": $oModal.find("#convm_qtdprod").val().replace(",", "."),
						"_cvest_u_lote_unpadrao": $oModal.find("#convm_unpadrao").val(),
						"_cvest_u_lote_unlote": $oModal.find("#convm_unlote").val(),
						"_cvest_u_lote_converteest": $oCabecalho.find("#convm_converteest").val().replace(",", "."),
						"_cvest_u_lote_valconvori": $oModal.find("#convm_valconvori").val().replace(",", "."),
					},
					parcial: true,
					refresh: false,
					posPost: function() {
						$('#cbModal').modal('hide');
						resetareestoque();
					}
				});
			});

			function resetareestoque() {
				let v = $oModal.find("#convm_qtdprod").val() + 1;
				CB.post({
					objetos: {
						"_reset1_u_lote_idlote": idlote,
						"_reset1_u_lote_qtdprod": v.replace(",", ".")
					},
					parcial: true,
					posPost: function() {
						CB.post({
							objetos: {
								"_reset2_u_lote_idlote": idlote,
								"_reset2_u_lote_qtdprod": $oModal.find("#convm_qtdprod").val().replace(",", ".")
							},
							parcial: true,
							posPost: function() {
								$('#cbModal').modal('hide');
							}
						});
					}
				});
			}


			$oResetar.on('click', () => {
				let v = $oModal.find("#convm_qtdprod").val() + 1;
				CB.post({
					objetos: {
						"_reset1_u_lote_idlote": idlote,
						"_reset1_u_lote_qtdprod": v.replace(",", ".")
					},
					parcial: true,
					posPost: function() {
						CB.post({
							objetos: {
								"_reset2_u_lote_idlote": idlote,
								"_reset2_u_lote_qtdprod": $oModal.find("#convm_qtdprod").val().replace(",", ".")
							},
							parcial: true,
							posPost: function() {
								$('#cbModal').modal('hide');
							}
						});
					}
				});
			});

			CB.modal({
				titulo: $oCabecalho,
				corpo: [$oModal],
				classe: 'trinta'
			});
		}

	<? } ?>

	jFuncionario = <?= $jFuncionario ?>;

	//Autocomplete de Setores vinculados
	$("#funcionario").autocomplete({
		source: jFuncionario,
		delay: 0,
		create: function() {
			$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
				lbItem = item.label;
				return $('<li>')
					.append('<a>' + lbItem + '</a>')
					.appendTo(ul);
			};
		},
		select: function(event, ui) {
			CB.post({
				objetos: {
					"_x_i_lotelocalizacao_idlote": $(":input[name=_1_" + CB.acao + "_lote_idlote]").val(),
					"_x_i_lotelocalizacao_tipoobjeto": 'pessoa',
					"_x_i_lotelocalizacao_idobjeto": ui.item.value
				}
				// ,parcial: true
			});
		}
	});

	jPrateleira = <?= $jPrateleira ?>;

	//Autocomplete 
	$("#tag").autocomplete({
		source: jPrateleira,
		delay: 0,
		create: function() {
			$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
				lbItem = item.label;
				return $('<li>')
					.append('<a>' + lbItem + '</a>')
					.appendTo(ul);
			};
		},
		select: function(event, ui) {
			CB.post({
				objetos: {
					"_x_i_lotelocalizacao_idlote": $(":input[name=_1_" + CB.acao + "_lote_idlote]").val(),
					"_x_i_lotelocalizacao_tipoobjeto": 'tagdim',
					"_x_i_lotelocalizacao_idobjeto": ui.item.value
				}
				// ,parcial: true
			});
		}
	});

	Jbotijao = <?= $Jbotijao ?>;

	//Autocomplete 
	$("#botijao").autocomplete({
		source: Jbotijao,
		delay: 0,
		create: function() {
			$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
				lbItem = item.label;
				return $('<li>')
					.append('<a>' + lbItem + '</a>')
					.appendTo(ul);
			};
		},
		select: function(event, ui) {
			CB.post({
				objetos: {
					"_x_i_lotelocalizacao_idlote": $(":input[name=_1_" + CB.acao + "_lote_idlote]").val(),
					"_x_i_lotelocalizacao_tipoobjeto": 'tagbotijao',
					"_x_i_lotelocalizacao_idobjeto": ui.item.value
				}
				//,parcial: true
			});
		}
	});

	Jsala = <?= $Jsala ?>;

	//Autocomplete 
	$("#sala").autocomplete({
		source: Jsala,
		delay: 0,
		create: function() {
			$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
				lbItem = item.label;
				return $('<li>')
					.append('<a>' + lbItem + '</a>')
					.appendTo(ul);
			};
		},
		select: function(event, ui) {
			CB.post({
				objetos: {
					"_x_i_lotelocalizacao_idlote": $(":input[name=_1_" + CB.acao + "_lote_idlote]").val(),
					"_x_i_lotelocalizacao_tipoobjeto": 'tagsala',
					"_x_i_lotelocalizacao_idobjeto": ui.item.value
				}
				//,parcial: true
			});
		}
	});

	jProd = <?= $jProd ?>;
	jProd = jQuery.map(jProd, function(o, id) {
		return {
			"label": o.descr,
			value: id,
			"codprodserv": o.codprodserv
		}
	});

	$("[name*=_lote_idprodserv]").autocomplete({
		source: jProd,
		delay: 0,
		create: function() {
			$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
				return $('<li>').append("<a>" + item.label + "<span class='cinzaclaro'> " + item.codprodserv + "</span></a>").appendTo(ul);
			};
		},
		select: function(event, ui) {
			$("[name=_1_u_lote_idprodserv]").val("").cbval("");
		}
	});

	function loteretira(inlinha) {

		CB.post({
			objetos: "_x_i_loteretira_idlotefracao=" + $("[name=" + inlinha + "_idlotefracao]").val() + "&_x_i_loteretira_data=" + $("[name=" + inlinha + "_data]").val() + "&_x_i_loteretira_qtd=" + $("[name=" + inlinha + "_qtd]").val() + "&_x_i_loteretira_obs=" + $("[name=" + inlinha + "_obs]").val(),
			refresh: "refresh"
		});
	}

	function loteadiciona(inlinha) {
		CB.post({
			objetos: "_x_i_loteadiciona_idlotefracao=" + $("[name=" + inlinha + "_idlotefracao]").val() + "&_x_i_loteadiciona_data=" + $("[name=" + inlinha + "_data]").val() + "&_x_i_loteadiciona_qtd=" + $("[name=" + inlinha + "_qtd]").val() + "&_x_i_loteadiciona_obs=" + $("[name=" + inlinha + "_obs]").val(),
			refresh: "refresh"
		});
	}

	function novalocalizacao() {

		CB.post({
			objetos: "_x_i_lotelocalizacao_idlote=" + $("[name*=_lote_idlote]").val() + "&_x_i_lotelocalizacao_tipoobjeto=tagdim",
			parcial: "true"
		});
	}

	function excluirlocal(inidlotelocalizacao) {
		CB.post({
			objetos: "_x_d_lotelocalizacao_idlotelocalizacao=" + inidlotelocalizacao,
			parcial: "true"
		});
	}

	function atulizacon(vthis, acao, idlote, idlotefracao, idobjeto) {
		if (acao == 'i') {
			CB.post({
				objetos: "_x_i_lotecons_idlote=" + idlote + "&_x_i_lotecons_idlotefracao=" + idlotefracao + "&_x_i_lotecons_idobjeto=" + idobjeto + "&_x_i_lotecons_tipoobjeto=lote&_x_i_lotecons_qtdd=" + $(vthis).val(),
				parcial: "true"
			});
		} else {
			CB.post({
				objetos: "_x_u_lotecons_idlotecons=" + $(vthis).attr('idlotecons') + "&_x_u_lotecons_qtdd=" + $(vthis).val(),
				parcial: "true"
			});
		}
	}

	// function excluirloteconst(...data){
	// 	if(data.length > 0){
	// 		let obj = {};
	// 		for(let d in data){
	// 			if(d < data.length - 1){
	// 				obj[`_x${d}_u_lotecons_idlotecons`] = data[d];
	// 				obj[`_x${d}_u_lotecons_status`] = "INATIVO";

	// 			}
	// 		}
	// 		/* não deletar fracao so inativar o consumo
	// 		let idlotefracao = data[data.length-1];
	// 		if(idlotefracao != null){
	// 				obj[`_x300_u_lotefracao_idlotefracao`] = idlotefracao;
	// 			}
	// 		*/
	// 		CB.post({
	// 			objetos: obj
	// 			,parcial:true
	// 			,posPost: function(){
	// 				$("#cbModalCorpo").html("");
	// 				$('#cbModal').modal('hide');
	// 			}
	// 		});
	//	}
	//}
	function excluirlotecons(inidlotecons) {
		if ($(`[name$=_lote_status]`).val() == "CANCELADO") {
			alertAtencao("Lote cancelado, transação não pode ser excluída!");
			return false;
		}
		CB.post({
			objetos: "_x_u_lotecons_idlotecons=" + inidlotecons + "&_x_u_lotecons_status=INATIVO",
			parcial: true,
			posPost: function() {
				$("#cbModalCorpo").html("");
				$('#cbModal').modal('hide');
			}
		});
	}


	function alttipolocalizacao(intipo, inidlotelocalizacao) {
		CB.post({
			objetos: "_x_u_lotelocalizacao_idlotelocalizacao=" + inidlotelocalizacao + "&_x_u_lotelocalizacao_tipoobjeto=" + intipo,
			refresh: "refresh"
		});
	}

	function atualizaanalise(vthis, indata) {
		CB.post({
			objetos: "_x_u_lote_idlote=" + $("[name*=_lote_idlote]").val() + "&_x_u_lote_dataanalise=" + indata + "&_x_u_lote_analise=" + $(vthis).val(),
			parcial: "true"
		});
	}

	function esgotarlote(inIdlotefracao) {
		if (confirm("Deseja realmente ESGOTAR está fração do lote?")) {
			CB.post({
				"objetos": "_x_u_lotefracao_idlotefracao=" + inIdlotefracao + "&_x_u_lotefracao_status=ESGOTADO&_x_u_lotefracao_qtd=0&_x_u_lotefracao_qtd_exp=0",
				parcial: true
			});
		}
	}

	function cancelarlote() {
		if (confirm("Deseja realmente CANCELAR o lote?")) {
			var vidlote = $("[name*=_lote_idlote]").val();
			//LTM - 07-04-2021 - Retorna o idFluxoStatus Selecionado
			var idfluxostatus = getIdFluxoStatus('<?= $pagvalmodulo ?>', 'CANCELADO');
			var idFluxoStatusHist = getIdFluxoStatusHist('<?= $pagvalmodulo ?>', vidlote);

			CB.post({
				"objetos": "_x_u_lote_idlote=" + vidlote + "&_x_u_lote_status=CANCELADO&_x_u_lote_idfluxostatus=" + idfluxostatus,
				parcial: true,
				posPost: function() {
					CB.post({
						urlArquivo: 'ajax/_fluxo.php?fluxo=fluxo',
						refresh: false,
						objetos: {
							"_modulo": '<?= $pagvalmodulo ?>',
							"_primary": 'idlote',
							"_idobjeto": vidlote,
							"idfluxo": '',
							"idfluxostatushist": idFluxoStatusHist,
							"idstatusf": idfluxostatus,
							"statustipo": 'CANCELADO',
							"idfluxostatus": idfluxostatus,
							"idfluxostatuspessoa": '',
							"ocultar": '',
							"prioridade": '20',
							"tipobotao": '',
							"acao": "alterarstatus"
						}
					});
				}
			});
		}
	}

	function adicionarlote(inIdlote) {
		CB.post({
			"objetos": "_x_i_lotecons_idlote=" + inIdlote + "&_x_i_lotecons_tipoobjeto=lote&_x_i_lotecons_qtdd=0&&_x_i_lotecons_idobjeto=" + $("[name*=_lote_idlote]").val(),
			parcial: true
		});
	}

	function inativareserva(inIdlote) {
		CB.post({
			"objetos": "_x_u_lotereserva_idlotereserva=" + inIdlote + "&_x_u_lotereserva_status=INATIVO",
			parcial: true
		});

	}

	function corrigepart(inIdlote, inPartida) {
		CB.post({
			"objetos": "_x_u_lote_idlote=" + inIdlote + "&_x_u_lote_partida=" + inPartida,
			parcial: true
		});
	}

	function dlotelocalizacao(idlotelocalizacao) {
		CB.post({
			"objetos": "_x_d_lotelocalizacao_idlotelocalizacao=" + idlotelocalizacao,
			parcial: true
		});
	}

	function buscaunidade(vthis, vidlote, inidunidade) {

		var vidunidade = $(vthis).val();
		$.ajax({
			type: "get",
			url: "ajax/buscaunidade.php",
			data: {
				idlote: vidlote,
				idunidade: vidunidade,
				idunidadeori: inidunidade
			},

			success: function(data) {
				$("#strunidade").html(data);
				$("[name=_9999_i_lotefracao_un]").val(data);

			},

			error: function(objxmlreq) {
				//alert('Erro:<br>'+objxmlreq.status); 
				$("#strunidade").html('Erro:<br>' + objxmlreq.status);

			}
		}) //$.ajax

	}


	function criafr(id, idLote, idlotefracao, vlfim, vlfim1, vencimento = false) {
		var strt = 'Alíquotar';

		if (id == 'criarfr') {
			var strt = 'Deslocar';
		}

		var strCabecalho = `<strong>
								${strt} Lote 
								<button id='cbSalvar' type='button' class='btn btn-danger btn-xs' onclick="gerarfracao(${vlfim},'${vlfim1}');">
									<i class='fa fa-circle'></i>Salvar
								</button>
							</strong>`;

		var JQelement = $(`#${id}`);
		$(`#${id}`).remove();

		// Verificar se lote esta vencido
		if (vencimento && (new Date() >= new Date(vencimento.split('/').reverse().join('/')))) {
			JQelement = $(`
					<div class="col-xs-12">
						<h3>Não é possível deslocar esse lote.</h3>
						<h4>Motivo: <strong class="text-danger">vencido</strong></h4>
					</div>
				`);
		} else {
			JQelement.find("#criafridlote").attr("name", "_9999_i_lotefracao_idlote");
			JQelement.find("#criafridlote").attr("value", idLote);
			JQelement.find("#criafridlotefracao").attr("name", "_9999_i_lotefracao_idlotefracaoorigem");
			JQelement.find("#criafridlotefracao").attr("value", idlotefracao);
			JQelement.find("#criafrloteao").attr("name", "lotefracao_AO");
			JQelement.find("#ndropunidade").attr("name", "_9999_i_lotefracao_idunidade");
			JQelement.find("#ndropidpessoa").attr("name", "_9999_i_lotefracao_idpessoa");

			JQelement.find("#criafrqtd").attr("type", "text");
			JQelement.find("#criafrqtd").attr("name", "_9999_i_lotefracao_qtd");
			JQelement.find("#criafrun").attr("name", "_9999_i_lotefracao_un");
			JQelement.find("#criafrrotulo").html("Qtd. :");
			JQelement.find("#criafrobs").attr("name", "_9999_i_lotefracao_obs");
			JQelement.find("#mostraun").html("<label class='alert-warning' id='strunidade'></label>");
		}

		CB.modal({
			titulo: strCabecalho,
			corpo: JQelement.html()
		});

		if (id == 'aliquotar') {
			$(".modal.in #ndropidpessoa").autocomplete({
				source: pessoasDisponiveisParaVinculo,
				delay: 0,
				create: function() {
					$(this).data('ui-autocomplete')._renderItem = function(ul, item) {

						lbItem = item.label;
						return $('<li>')
							.append('<a>' + lbItem + '</a>')
							.appendTo(ul);
					};
				}
			});
		}
	}

	function transferirConsumo(id, idLote, idlotefracao, vlfim, vlfim1, vencimento = false) {


		var strCabecalho = `<strong>
								Transferir Consumo
								<button id='cbSalvar' type='button' class='btn btn-danger btn-xs' onclick="fracionar(${vlfim},'${vlfim1}');">
									<i class='fa fa-circle'></i>Salvar
								</button>
							</strong>`;

		var JQelement = $(`#${id}`);
		$(`#${id}`).remove();

		// Verificar se lote esta vencido
		if (vencimento && (new Date() >= new Date(vencimento.split('/').reverse().join('/')))) {
			JQelement = $(`
					<div class="col-xs-12">
						<h3>Não é possível deslocar esse lote.</h3>
						<h4>Motivo: <strong class="text-danger">vencido</strong></h4>
					</div>
				`);
		} else {
		
			JQelement.find("#idloteorigem").attr("value", idLote);
			JQelement.find("#idloteorigem").attr("name", "_9999_i_lotefracao_idlote");
			JQelement.find("#transfereconsumo").attr("name", "lotefracao_AO");
		
			JQelement.find("#idlotedestino").attr("name", "_9999_i_lotefracao_idlotedestino");

			JQelement.find("#criafrqtd").attr("type", "text");
			JQelement.find("#criafrqtd").attr("name", "_9999_i_lotefracao_qtd");
			JQelement.find("#criafrun").attr("name", "_9999_i_lotefracao_un");
			JQelement.find("#criafrrotulo").html("Qtd. :");
			JQelement.find("#mostraun").html("<label class='alert-warning' id='strunidade'></label>");

		}

		CB.modal({
			titulo: strCabecalho,
			corpo: JQelement.html()
		});

		if (id == 'aliquotar') {
			$(".modal.in #ndropidpessoa").autocomplete({
				source: pessoasDisponiveisParaVinculo,
				delay: 0,
				create: function() {
					$(this).data('ui-autocomplete')._renderItem = function(ul, item) {

						lbItem = item.label;
						return $('<li>')
							.append('<a>' + lbItem + '</a>')
							.appendTo(ul);
					};
				}
			});
		}
	}

	function gerarfracao(vlfim, vlfim1) {
		var valid = vlfim + vlfim1;
		var resud = valid.split("d", 2);
		var resue = valid.split("e", 2);
		if (resud[1] != null) {
			resultvali = resud[1];
		} else {
			resultvali = resue[1];
		}

		if ($("[name=_9999_i_lotefracao_qtd]").val().indexOf("e") != -1) {
			var ress = $("#cbModal.in [name=_9999_i_lotefracao_qtd]").val().split("e", 2);
		} else {
			var ress = $("#cbModal.in [name=_9999_i_lotefracao_qtd]").val().split("d", 2);
		}

		if (resultvali != ress[1]) {
			alert('Qtd solicitada esta incorreta');
		} else if (ress[0] > vlfim) {
			alert('Qtd solicitada maior que a disponível');
		} else {
			var str = "_x_i_lotefracao_idlote=" + $("[name=_9999_i_lotefracao_idlote]").val() +
				"&_x_i_lotefracao_idunidade=" + $("#cbModal.in [name=_9999_i_lotefracao_idunidade]").val() +
				"&_x_i_lotefracao_un=" + $("[name=_9999_i_lotefracao_un]").val() +
				"&_x_i_lotefracao_idpessoa=" + $("#cbModal.in [name=_9999_i_lotefracao_idpessoa]").attr('cbvalue') +
				"&_x_i_lotefracao_idlotefracaoorigem=" + $("[name=_9999_i_lotefracao_idlotefracaoorigem]").val() +
				"&lotefracao_AO=" + $("[name=lotefracao_AO]").val() +
				"&_x_i_lotefracao_obs=" + $("#cbModal.in [name=_9999_i_lotefracao_obs]").val() +
				"&_x_i_lotefracao_qtd=" + $("#cbModal.in [name=_9999_i_lotefracao_qtd]").val();


			$("[name=_9999_i_lotefracao_idlote]").attr("name", "");
			$("[name=_9999_i_lotefracao_obs]").attr("name", "");
			$("[name=_9999_i_lotefracao_idunidade]").attr("name", "");
			$("[name=_9999_i_lotefracao_idlotefracaoorigem]").attr("name", "");
			$("[name=_9999_i_lotefracao_qtd]").attr("name", "");

			CB.post({
				objetos: str,
				parcial: true,
				posPost: function(resp, status, ajax) {
					if (status = "success") {
						$("#cbModalCorpo").html("");
						$('#cbModal').modal('hide');
					} else {
						alert(resp);
					}
				}
			});
		}
	}

	function migrarfr(inidlote, inidlotefracao, vlfim, vlfim1) {

		var strCabecalho = `</strong>Migrar Lote <button id='cbSalvar' type='button' class='btn btn-danger btn-xs' onclick="migrarfracao(` + vlfim + `,'` + vlfim1 + `');"><i class='fa fa-circle'></i>Salvar</button></strong>`;
		//$("#cbModalTitulo").html((strCabecalho));

		var htmloriginal = $("#migrarfr").html();
		var objfrm = $(htmloriginal);

		objfrm.find("#migraridlote").attr("name", "_9999_i_lotecons_idlote");
		objfrm.find("#migraridlote").attr("value", inidlote);
		objfrm.find("#migraridlotefracao").attr("name", "_9999_i_lotecons_idlotefracao");
		objfrm.find("#migraridlotefracao").attr("value", inidlotefracao);
		objfrm.find("#droplote").attr("name", "_9999_i_lotecons_idobjeto");
		objfrm.find("#migrarqtd").attr("type", "text");
		objfrm.find("#migrarqtd").attr("name", "_9999_i_lotecons_qtdd");
		objfrm.find("#migrarun").attr("name", "lotecons_un");
		objfrm.find("#migrarrotulo").html("Qtd. :");
		objfrm.find("#mgmostraun").html("<label class='alert-warning' id='strunidade'></label>");



		//Chamada para limpar os campos quando aplica o comando ctrl+s, pois estava salvando a mesma informação mais de uma vez. Lidiane(02/06/2020)
		//sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=326318
		//O modo abaixo só abre o Modal, mas não faz todo o processo que precisa. O certo é chamar o CB.Modal
		//$("#cbModalCorpo").html(objfrm.html());
		//$('#cbModal').modal('show');
		CB.modal({
			titulo: strCabecalho,
			corpo: objfrm.html()
		});
	}

	function migrarfracao(vlfim, vlfim1) {
		var valid = vlfim + vlfim1;
		var resud = valid.split("d", 2);
		var resue = valid.split("e", 2);
		if (resud[1] != null) {
			resultvali = resud[1];
		} else {
			resultvali = resue[1];
		}
		var ress = $("[name=_9999_i_lotecons_qtdd]").val().split("d", 2);
		if (resultvali != ress[1]) {
			alert('Qtd solicitada esta incorreta');
		} else if (ress[0] > vlfim) {
			alert('Qtd solicitada maior que a disponível');
		} else {
			var str = "_xm_i_lotecons_idlote=" + $("[name=_9999_i_lotecons_idlote]").val() +
				"&_xm_i_lotecons_idlotefracao=" + $("[name=_9999_i_lotecons_idlotefracao]").val() +
				"&_xm_i_lotecons_idobjeto=" + $("[name=_9999_i_lotecons_idobjeto]").val() +
				"&_xm_i_lotecons_qtdd=" + $("[name=_9999_i_lotecons_qtdd]").val() +
				"&lotecons_un=" + $("[name=lotecons_un]").val();


			$("[name=_9999_i_lotecons_idlote]").attr("name", "");
			$("[name=_9999_i_lotecons_idlotefracao]").attr("name", "");
			$("[name=_9999_i_lotecons_idobjeto]").attr("name", "");
			$("[name=_9999_i_lotecons_qtdd]").attr("name", "");

			CB.post({
				objetos: str,
				parcial: true,
				posPost: function(resp, status, ajax) {
					if (status = "success") {
						$("#cbModalCorpo").html("");
						$('#cbModal').modal('hide');
					} else {
						alert(resp);
					}
				}
			});
		}

	}

	function fracionar(vlfim, vlfim1) {
		var valid = vlfim + vlfim1;
		var resud = valid.split("d", 2);
		var resue = valid.split("e", 2);
		if (resud[1] != null) {
			resultvali = resud[1];
		} else {
			resultvali = resue[1];
		}

		if ($("[name=_9999_i_lotefracao_qtd]").val().indexOf("e") != -1) {
			var ress = $("#cbModal.in [name=_9999_i_lotefracao_qtd]").val().split("e", 2);
		} else {
			var ress = $("#cbModal.in [name=_9999_i_lotefracao_qtd]").val().split("d", 2);
		}

		if (resultvali != ress[1]) {
			alert('Qtd solicitada esta incorreta');
		} else if (ress[0] > vlfim) {
			alert('Qtd solicitada maior que a disponível');
		} else {
			var str = "_x_i_lotefracao_idlote=" + $("[name=_9999_i_lotefracao_idlote]").val() +
					"&_x_i_lotefracao_un=" + $("[name=_9999_i_lotefracao_un]").val() +
				"&_x_i_lotefracao_idlotedestino=" + $("[name=_9999_i_lotefracao_idlotedestino]").val() +
				"&lotefracao_AO=" + $("[name=lotefracao_AO]").val() +				
				"&_x_i_lotefracao_qtd=" + $("#cbModal.in [name=_9999_i_lotefracao_qtd]").val();


			$("[name=_9999_i_lotefracao_idlote]").attr("name", "");
			$("[name=_9999_i_lotefracao_un]").attr("name", "");
			$("[name=_9999_i_lotefracao_idlotedestino]").attr("name", "");
			$("[name=_9999_i_lotefracao_qtd]").attr("name", "");

			CB.post({
				objetos: str,
				parcial: true,
				posPost: function(resp, status, ajax) {
					if (status = "success") {
						$("#cbModalCorpo").html("");
						$('#cbModal').modal('hide');
					} else {
						alert(resp);
					}
				}
			});
		}
	}

	function tfiscal(inidlote, inidlotefracao, vlfim, vlfim1) {


		var strCabecalho = `</strong>Transferência fiscal do lote <button id='cbSalvar' type='button' class='btn btn-danger btn-xs' onclick="gerartfiscal(` + vlfim + `,'` + vlfim1 + `');"><i class='fa fa-circle'></i>Salvar</button></strong>`;
		//$("#cbModalTitulo").html((strCabecalho));

		var htmloriginal = $("#bodytfiscal").html();
		var objfrm = $(htmloriginal);

		objfrm.find("#criafridlote").attr("name", "_9999_i_lote_idlote");
		objfrm.find("#criafridlote").attr("value", inidlote);
		objfrm.find("#criafridlotefracao").attr("name", "_9999_i_lote_idlotefracaoorigem");
		objfrm.find("#criafridlotefracao").attr("value", inidlotefracao);

		objfrm.find("#ndropidprodservforn").attr("name", "_9999_i_lote_idprodservforn");

		objfrm.find("#criafrqtd").attr("type", "text");
		objfrm.find("#criafrqtd").attr("name", "_9999_i_lote_qtdprod");
		objfrm.find("#criafrun").attr("name", "_9999_i_lote_unpadrao");
		objfrm.find("#criafrrotulo").html("Qtd. :");
		objfrm.find("#mostraun").html("<label class='alert-warning' id='strunidade'></label>");



		//Chamada para limpar os campos quando aplica o comando ctrl+s, pois estava salvando a mesma informação mais de uma vez. Lidiane(02/06/2020)
		//sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=326318
		//O modo abaixo só abre o Modal, mas não faz todo o processo que precisa. O certo é chamar o CB.Modal
		//$("#cbModalCorpo").html(objfrm.html());
		//$('#cbModal').modal('show');
		CB.modal({
			titulo: strCabecalho,
			corpo: objfrm.html()
		});

	}


	function gerartfiscal(vlfim, vlfim1) {
		var valid = vlfim + vlfim1;
		var resud = valid.split("d", 2);
		var resue = valid.split("e", 2);
		if (resud[1] != null) {
			resultvali = resud[1];
		} else {
			resultvali = resue[1];
		}
		var ress = $("[name=_9999_i_lote_qtdprod]").val().split("d", 2);
		if (resultvali != ress[1]) {
			alert('Qtd solicitada esta incorreta');
		} else if (ress[0] > vlfim) {
			alert('Qtd solicitada maior que a disponível');
		} else {
			var str = "_tf_i_lote_idloteorigem=" + $("[name=_9999_i_lote_idlote]").val() +
				"&_tf_i_lote_idprodservforn=" + $("[name=_9999_i_lote_idprodservforn]").val() +
				"&idlotefracaoorigem=" + $("[name=_9999_i_lote_idlotefracaoorigem]").val() +
				"&_tf_i_lote_qtdprod=" + $("[name=_9999_i_lote_qtdprod]").val();


			$("[name=_9999_i_lote_idlote]").attr("name", "");
			$("[name=_9999_i_lote_idloteorigem]").attr("name", "");
			$("[name=_9999_i_lote_idprodservforn]").attr("name", "");
			$("[name=_9999_i_lote_qtdprod]").attr("name", "");

			CB.post({
				objetos: str,
				parcial: true,
				posPost: function(resp, status, ajax) {
					if (status = "success") {
						$("#cbModalCorpo").html("");
						$('#cbModal').modal('hide');
					} else {
						alert(resp);
					}
				}
			});
		}
	}


	function ajustaest(vop, inidlote, inidlotefracao, vlfim, vlfim1) {

		var strCabecalho = `</strong>Ajustar estoque <button id='cbSalvar' type='button' class='btn btn-danger btn-xs' onclick="alteraest(` + vlfim + `,'` + vlfim1 + `','` + vop + `');"><i class='fa fa-circle'></i>Salvar</button></strong>`;
		//$("#cbModalTitulo").html((strCabecalho));



		if (vop == "add") {
			var htmloriginal = $("#ajustaest").html();
			var objfrm = $(htmloriginal);
			objfrm.find("#ajutaestidlote").attr("name", "_999_i_lotecons_idlote");
			objfrm.find("#ajutaestidlote").attr("value", inidlote);
			objfrm.find("#ajutaestidlotefracao").attr("name", "_999_i_lotecons_idlotefracao");
			objfrm.find("#ajutaestidlotefracao").attr("value", inidlotefracao);
			objfrm.find("#ajutaestqtc").attr("value", "");
			objfrm.find("#ajutaestqtc").attr("name", "_999_i_lotecons_qtdc");
			objfrm.find("#ajutaestqtd").attr("type", "hidden");
			objfrm.find("#ajutaestqtd").attr("name", "_999_i_lotecons_qtdd");
			objfrm.find("#ajustaestrotulo").html("Qtd. Adicionar:");
		} else {
			var htmloriginal = $("#ajustaestdeb").html();
			var objfrm = $(htmloriginal);
			objfrm.find("#ajutaestidlote").attr("name", "_999_i_lotecons_idlote");
			objfrm.find("#ajutaestidlote").attr("value", inidlote);
			objfrm.find("#ajutaestidlotefracao").attr("name", "_999_i_lotecons_idlotefracao");
			objfrm.find("#ajutaestidlotefracao").attr("value", inidlotefracao);
			objfrm.find("#ajutaestqtd").attr("value", "");
			objfrm.find("#ajutaestqtd").attr("name", "_999_i_lotecons_qtdd");
			objfrm.find("#ajutaestqtc").attr("type", "hidden");
			objfrm.find("#ajutaestqtc").attr("name", "_999_i_lotecons_qtdc");
			objfrm.find("#ajustaestrotulo").html("Qtd. Retirar:");
		}

		objfrm.find("#ndroptipo").attr("name", "_999_i_lotecons_obs");

		//Chamada para limpar os campos quando aplica o comando ctrl+s, pois estava salvando a mesma informação mais de uma vez. Lidiane(02/06/2020)
		//sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=326318
		//O modo abaixo só abre o Modal, mas não faz todo o processo que precisa. O certo é chamar o CB.Modal
		//$("#cbModalCorpo").html(objfrm.html());
		//$('#cbModal').modal('show');
		CB.modal({
			titulo: strCabecalho,
			corpo: objfrm.html()
		});
	}

	function alteraest(vlfim, vlfim1, vop) {
		var valid = vlfim + vlfim1;
		var resud = valid.split("d", 2);
		var resue = valid.split("e", 2);
		if (resud[1] != null) {
			resultvali = resud[1];
			var ress = $("[name=_999_i_lotecons_qtdd]").val().split("d", 2);
		} else {
			resultvali = resue[1];
			var ress = $("[name=_999_i_lotecons_qtdd]").val().split("e", 2);
		}

		if (resultvali != ress[1] && vop != "add") {
			alert('Qtd solicitada esta incorreta');
		} else {
			if (ress[0] > vlfim && vop != "add") {
				alert('Qtd solicitada maior que a disponível');

			} else {
				var str = {
					'_x_i_lotecons_idlote': $("[name=_999_i_lotecons_idlote]").val(),
					'_x_i_lotecons_idlotefracao': $("[name=_999_i_lotecons_idlotefracao]").val(),
					'_x_i_lotecons_obs': $("[name=_999_i_lotecons_obs]").val(),
					'_x_i_lotecons_qtdc': $("[name=_999_i_lotecons_qtdc]").val(),
					'_x_i_lotecons_qtdd': $("[name=_999_i_lotecons_qtdd]").val()
				};

				$("[name=_999_i_lotecons_idlote]").attr("name", "");
				$("[name=_999_i_lotecons_idlotefracao]").attr("name", "");
				$("[name=_999_i_lotecons_obs]").attr("name", "");
				$("[name=_999_i_lotecons_qtdc]").attr("name", "");
				$("[name=_999_i_lotecons_qtdd]").attr("name", "");

				CB.post({
					objetos: str,
					parcial: true,
					posPost: function(resp, status, ajax) {
						if (status = "success") {
							$("#cbModalCorpo").html("");
							$('#cbModal').modal('hide');
						} else {
							alert(resp);
						}
					}
				});
			}
		}

	}

	function consumo() {
		/*
			var strCabecalho = "</strong>Histórico do Lote</strong>";
			$("#cbModalTitulo").html((strCabecalho));

			var  htmloriginal =$("#consumo").html();
			var objfrm= $(htmloriginal);
			
			$("#cbModalCorpo").html(objfrm.html());
			$('#cbModal').modal('show');
		*/
		CB.modal({
			titulo: "</strong>Histórico do Lote</strong>",
			corpo: $("#consumo").html(),
			classe: 'sessenta'
		});

	}



	function venda() {
		var strCabecalho = "</strong>Vendas do Lote</strong>";
		$("#cbModalTitulo").html((strCabecalho));

		var htmloriginal = $("#venda").html();
		var objfrm = $(htmloriginal);

		$("#cbModalCorpo").html(objfrm.html());
		$('#cbModal').modal('show');

	}
	<? if (!empty($_1_u_lote_idlote)) { ?>
		$(".cbupload").dropzone({
			idObjeto: $("[name=_1_u_lote_idlote]").val() || '<?= $_1_u_lote_idlote ?>',
			tipoObjeto: 'lote',
			idPessoaLogada: '<?= $_SESSION["SESSAO"]["IDPESSOA"] ?>'
		});
	<? } ?>

	CB.preLoadUrl = function() {
		//Como o carregamento é via ajax, os popups ficavam aparecendo após o load
		$(".webui-popover").remove();
	}

	$(".oSolfab").webuiPopover({
		trigger: "hover",
		placement: "right",
		delay: {
			show: 300,
			hide: 0
		}
	});

	/*
	 * Duplicar bioensaio [ctrl]+[d]
	 */
	/*
	$(document).keydown(function(event) {
		

		if (!((event.ctrlKey || event.altKey) && event.keyCode == 68)) return true;

		if(!teclaLiberada(event)) return;//Evitar repetição do comando abaixo

		janelamodal('?_modulo='+CB.modulo+'&_acao=i&idloteorigem=<?= $_1_u_lote_idlote ?>');

		return false;
	});
	*/

	function assina(inidlote, inacao, inidassinadopor, statuscarrimbo, idcarrimbo) {
		var acaoa;
		var strval;
		var statusa;
		if ($("[name=_1_u_lote_analise]").val() != "") {

			if (inacao == "A") {
				vPost = inidassinadopor;
				statusa = 'ATIVO';
				if (statuscarrimbo == 'PENDENTE') {
					acaoa = 'u';
					strval = "&_y_" + acaoa + "_carrimbo_idcarrimbo=" + idcarrimbo;
				} else {
					acaoa = 'i';
					strval = '';
				}
			} else {
				vPost = null;
				statusa = 'PENDENTE';
				acaoa = 'u';
				strval = "&_y_" + acaoa + "_carrimbo_idcarrimbo=" + idcarrimbo;
			}
			//console.log("_x_u_lote_idlote="+$("#idlote").val()+"&_x_u_lote_analise="+$("[name=_1_u_lote_analise]").val()+"&_y_"+acaoa+"_carrimbo_idempresa=<? //=$_SESSION["SESSAO"]["IDEMPRESA"];
																																								?>"+strval+"&_y_"+acaoa+"_carrimbo_idpessoa="+inidassinadopor+"&_y_"+acaoa+"_carrimbo_idobjeto="+$("#idlote").val()+"&_y_"+acaoa+"_carrimbo_tipoobjeto=<? //=$_REQUEST[_modulo];
																																																																														?>&_y_"+acaoa+"_carrimbo_status="+statusa+"");
			//return 0; 
			CB.post({
				objetos: "_x_u_lote_idlote=" + inidlote + "&_x_u_lote_analise=" + $("[name=_1_u_lote_analise]").val() + "&_y_" + acaoa + "_carrimbo_idempresa=<?= $_SESSION["SESSAO"]["IDEMPRESA"]; ?>" + strval + "&_y_" + acaoa + "_carrimbo_idpessoa=" + inidassinadopor + "&_y_" + acaoa + "_carrimbo_idobjeto=" + $("#idlote").val() + "&_y_" + acaoa + "_carrimbo_tipoobjeto=<?= $_REQUEST[_modulo]; ?>&_y_" + acaoa + "_carrimbo_status=" + statusa + "",
				parcial: true
			});
		} else {
			alert("Preencher lote como ACEITO/RECUSADO");
		}
	}

	function imprimeEtiqueta(inIdlote, modulo) {
		var imprimir = true;
		var qtdimp = $("#qtdimp").val();
		CB.imprimindo = true;

		var impressora = $("input[name='impEtiqueta']:checked").val() || "";

		if (impressora === "") {
			alert("Selecione uma Impressora para imprimir");
		} else {
			if (!confirm("Deseja realmente enviar para a impressora?")) {
				imprimir = false;
			}
			switch (impressora) {
				case "IMPRESSORA_SEMENTES":
				case "IMPRESSORA_ALMOXARIFADO_ZEBRA":
					var partida = $("input[name=_1_u_lote_partida]").val();
					var exercicio = $("input[name=_1_u_lote_exercicio]").val();
					if (imprimir) {
						$.ajax({
							type: "get",
							url: "ajax/impressora3.php?partida=" + partida + "&exercicio=" + exercicio + "&tipoimpressora=" + impressora + "&qtdimp=" + qtdimp + "&modulo=" + modulo + "&idlote=" + inIdlote,
							success: function(data) {
								console.log(data);
								alertAzul("Enviado para impressão", "", 1000);

							}
						});
					}
					break;
				case "IMPRESSORA_ALMOXARIFADO":
				case "IMPRESSORA_INCUBACAO":
					if (imprimir) {
						$.ajax({
							type: "get",
							url: "ajax/impetiquetalote.php?tipo=" + impressora + "&tipoobjeto=lote&idobjeto=" + inIdlote + "&qtdimp=" + qtdimp + "&modulo=" + modulo,
							success: function(data) {
								console.log(data);
								alertAzul("Enviado para impressão", "", 1000);

							}
						});
					}
					break;
				case "IMPRESSORA_CQ_2":
					if (imprimir) {
						$.ajax({
							type: "get",
							url: "ajax/impetiquetacq.php?tipoobjeto=lote&idlote=" + inIdlote + "&qtdimp=" + qtdimp + "&modulo=" + modulo,
							success: function(data) {
								console.log(data);
								alertAzul("Enviado para impressão", "", 1000);

							}
						});
					}
					break;
				case "IMPRESSORA_PRODUCAO_2":
					var partida = $("input[name=_1_u_lote_partida]").val();
					var exercicio = $("input[name=_1_u_lote_exercicio]").val();
					if (imprimir) {
						$.ajax({
							type: "get",
							url: "ajax/impressora3.php?partida=" + partida + "&exercicio=" + exercicio + "&tipoimpressora=" + impressora + "&qtdimp=" + qtdimp + "&modulo=" + modulo + "&idlote=" + inIdlote,
							success: function(data) {
								console.log(data);
								alertAzul("Enviado para impressão", "", 1000);

							}
						});
					}
					break;
				default:
					alertAtencao("Não foi possível imprimir");
					break;
			}
		}
	}
	<?
	if (!empty($_1_u_lote_idlote)) {
		$sqla = "select * from carrimbo 
			where status='PENDENTE' 
			and idobjeto = " . $_1_u_lote_idlote . " 
			and tipoobjeto in('lote','lotesuinos','loteretem','lotecq','loteaves','lotealmoxarife')
			and idpessoa=" . $_SESSION["SESSAO"]["IDPESSOA"];
		$resa = d::b()->query($sqla) or die("Erro ao buscar se modulo esta assinado: Erro: " . mysqli_error(d::b()) . "\n" . $sqla);
		$qtda = mysqli_num_rows($resa);
		if ($qtda > 0) {
			$rowa = mysqli_fetch_assoc($resa);

	?>
			botaoAssinar(<?= $rowa['idcarrimbo'] ?>);
	<?

		} // if($qtda>0){
	} //if(!empty($_1_u_lote_idlote)){
	?>

	function botaoAssinar(inidcarrimbo) {
		$bteditar = $("#btAssina");
		if ($bteditar.length == 0) {
			CB.novoBotaoUsuario({
				id: "btAssina",
				rotulo: "Assinar",
				class: "verde",
				icone: "fa fa-pencil",
				onclick: function() {
					CB.post({
						objetos: "_x_u_carrimbo_idcarrimbo=" + inidcarrimbo + "&_x_u_carrimbo_status=ATIVO",
						parcial: true,
						posPost: function(data, textStatus, jqXHR) {
							$('#btAssina').hide();
						}
					});
				}
			});
		}
	}

	function preencheun(transf) {

		if (transf == 'Y') {
			if (confirm("Transferir o lote para a Unidade?")) {
				alteraun(transf);
			} else {
				location.reload();
			}
		} else {
			alteraun(transf);
		}


	}

	function alteraun(transf) {
		var vidprodserv = $("[name=_1_" + CB.acao + "_lote_idprodserv]").attr("cbvalue");
		var vidunidade = $("[name=_1_" + CB.acao + "_lote_idunidade]").val();
		$.ajax({
			type: "get",
			url: "ajax/buscaunidade.php",
			data: {
				idprodserv: vidprodserv,
				idunidade: vidunidade
			},

			success: function(data) {
				$("#unidade").html(data);
				if (transf == "Y") {
					CB.post();
				}
			},

			error: function(objxmlreq) {
				alert('Erro:<br>' + objxmlreq.status);

			}
		}) //$.ajax
	}


	function alteravunpadrao(idlote, vunpadrao) {
		CB.post({
			objetos: "_x_u_lote_idlote=" + idlote + "&_x_u_lote_vunpadrao=" + vunpadrao,
			parcial: true

		});

	}

	function altalerta(vthis) {
		let corValor = $('#flgalerta').attr('corvalor');

		if (corValor == 'P') {
			$(vthis).addClass("azul").removeClass('preto');
			$(vthis).attr('corvalor', 'A')
			$(vthis).attr('title', 'A semente irá ter a cor Azul')
		} else if (corValor == "A") {
			$(vthis).addClass("roxo").removeClass('azul');
			$(vthis).attr('corvalor', 'R')
			$(vthis).attr('title', 'A semente irá ter a cor Roxa')
		} else if (corValor == "R") {
			$(vthis).removeClass('roxo fa-star').addClass('fa-star-o');
			$(vthis).attr('corvalor', 'N')
			$(vthis).attr('title', 'A semente não terá cor')
		} else {
			$(vthis).addClass("preto fa-star").removeClass('fa-star-o');
			$(vthis).attr('corvalor', 'P')
			$(vthis).attr('title', 'A semente irá ter a cor Preta')
		}

	}


	function setalerta() {
		let corValor = $('#flgalerta').attr('corvalor')

		var strcbpost = "_999_u_lote_idlote=" + $("[name=_999_u_lote_idlote]").val() + "&_999_u_lote_alerta=" + $("[name=_999_u_lote_alerta]").val() + '&_999_u_lote_flgalerta=' + corValor;

		console.log(strcbpost);
		CB.post({
			objetos: strcbpost,
			parcial: true,
			msgSalvo: "Salvo",
			posPost: function(resp, status, ajax) {
				if (status = "success") {
					$("#cbModalCorpo").html("");
					$('#cbModal').modal('hide');
				} else {
					alert(resp);
				}
			}
		});

	}

	function alteravalor(campo, valor, tabela, inid, texto, datepicker = false) {
		htmlTrModelo = "";
		htmlTrModelo = `<div id="alt${campo}${inid}">
            <table class="table table-hover">
                <tr>
                    <td>${texto}</td>
                    <td>
                        <input name="_h1_i_${tabela}_idobjeto" value="${inid}" type="hidden">
                        <input name="_h1_i_${tabela}_campo" value="${campo}" type="hidden">
                        <input name="_h1_i_${tabela}_tipoobjeto" value="lote" type="hidden">
                        <input name="_h1_i_${tabela}_valor_old" value="${valor}" type="hidden">
                        <input name="_h1_i_${tabela}_valor" value="${valor}" class="size10" type="text">
                    </td>
                </tr>
                <tr>
                    <td>Justificativa:</td>
                    <td>
                        <input id="justificativa" name="_h1_i_${tabela}_justificativa" vnulo class="size50">
                    </td>
                </tr>
            </table>
        </div>`;

		if (campo == 'previsaoentrega') {
			var objfrm = $(htmlTrModelo);
			objfrm.find("[name='_h1_i_modulohistorico_justificativa']").attr("vnulo");
		} else {
			var objfrm = $(htmlTrModelo);
			objfrm.find("#ndroptipo option[value='" + valor + "']").attr("selected", "selected");
			objfrm.find("[name='_h1_i_modulohistorico_justificativa']").attr("vnulo");
		}

		strCabecalho = "</strong>Alterar " + texto + " <button id='cbSalvar' type='button' class='btn btn-success btn-xs' onclick='salvaHist()' style='float: right; margin-top: 14px;'><i class='fa fa-circle'></i>Salvar</button></strong>";

		CB.modal({
			titulo: strCabecalho,
			corpo: "<table>" + objfrm.html() + "</table>",
			classe: 'sessenta',
			aoAbrir: function(vthis) {
				if (datepicker) {
					$("[name='_h1_i_modulohistorico_valor']").daterangepicker({
						"singleDatePicker": true,
						"locale": CB.jDateRangeLocale
					}).on('apply.daterangepicker', function(ev, picker) {
						console.log(picker.startDate.format('YYYY-MM-DD'));
						$(this).html(picker.startDate.format("DD/MM/YYYY") || "");
					});
				}
				$(`[name="_h1_i_${tabela}_valor"]`).val(valor);
			}
		});
	}

	function modalhist(div) {
		var htmloriginal = $(`#${div}`).html();

		CB.modal({
			titulo: "</strong>Histórico de Alteração:</strong>",
			corpo: htmloriginal,
			classe: 'sessenta'
		});
	}

	function salvaHist() {
		if ($(`#justificativa`).val().length >= 5) {
			CB.post();
		} else {
			alert(`Justificativa deve ter pelo menos 5 caracteres`)
		}
	}

	function msalerta() {
		var htmloriginal = $("#msalerta").html();
		var objfrm = $(htmloriginal);

		objfrm.find("#lote_idlote").attr("name", "_999_u_lote_idlote");
		objfrm.find("#lote_alerta").attr("name", "_999_u_lote_alerta");

		objfrm.find("[name=flgalerta]").attr("id", "flgalerta");


		CB.modal({
			titulo: "</strong>Alerta Lote <button type='button' class='btn btn-danger btn-xs' onclick='setalerta();'><i class='fa fa-circle'></i>Salvar</button></strong>",
			corpo: objfrm.html(),
			classe: 'sessenta'
		});
	}
	<? if($_acao=="u" && $_fabricado == "Y"){ ?>
		function mostraval() {
			inid 		= <?= $_1_u_lote_idlote ?>;
			valLote 	= '<?= $valLoteFormatado ?>';
			partida 	= '<?= $_1_u_lote_partida ?>/<?= $_1_u_lote_exercicio ?>';
			valunlote	= '<?= $valLoteUnFormatado ?>';
			unpadrao	= '<?= $_1_u_lote_unpadrao ?>';
			formula		= '<?= $rowr['rotulo'] ?>';
			cliente		= '<?= $cliente ?>';
			unidade		= '<?= $strunidade ?>';
			
			listarateio = `<?= $rateios['lista'] ?>`;
			valorrateio = '<?= $rateios['valor'] ?>';
			
			listatestes	= `<?= $testes['lista'] ?>`;
			valortestes = '<?= $testes['valor'] ?>';

			total = "<?= number_format(tratanumero($valorlote+$testes['valor']+$rateios['valor']), 4, ',', '.') ?>";

			let strCabecalho = "</strong>Custo(s) do Lote&nbsp;&nbsp;</strong>";
			//debugger
			let $oContent = $(`
				<div id="valaorform${inid}">
					<div class="row">
						<div class="col-md-12">
							<div class="panel panel-default" style="margin-top: 0px !important; border-color:#ffffff00 !important; background-color:#f5f5f505 !important; padding-bottom: 20px;">
								<div class="panel-heading" style="border-bottom: 1px solid #cec8c8b3;  font-weight: bold; background-color: #e6e6e6; ">
								<div class='row'>
								<div class="col-md-1">Produto:</div>
								<div class="col-md-5">
									<label class='alert-warning'><?= traduzid("prodserv", "idprodserv", "descr", $_1_u_lote_idprodserv) ?></label>
								</div>
								<div class="col-md-1">Partida:</div>
								<div class="col-md-5">
								<label class='alert-warning'><? echo ($_1_u_lote_partida . '-' . $_1_u_lote_exercicio); ?></label>
								</div>
								</div>
								<div class='row'>
								<div class="col-md-1">Cliente: </div>
								<div class="col-md-5">${cliente}</div>
								<div class="col-md-1">Formula:</div>
								<div class="col-md-5"> ${formula}</div>
								</div>
								<div class='row'>
								<div class="col-md-1">Valor R$:</div>
								<div class="col-md-5"> <label class='alert-warning'>${valunlote} ${unpadrao}</label></div>
								<div class="col-md-1">Unidade:</div>
								<div class="col-md-5">${unidade}</div>
								</div>
								</div>
							</div>

							<h4>Insumos</h4>
							<div class="panel panel-default" style="margin-top: 0px !important; border-color:#ffffff00 !important; background-color:#f5f5f505 !important; padding-bottom: 20px;">
								<div class="panel-body" style="font-size:12px;padding: 0px !important;">
								
									<div class="col-md-12 panel-heading" style="border-bottom: 1px solid #cec8c8b3;  font-weight: bold; background-color: #e6e6e6; <?= $margin ?>" lvl="<?= $lvl ?>">
										<div class="col-md-2" title="Quantidade Utilizada">
											Qtd Utilizada
										</div>
										<div class="col-md-1" title="Partida Utilizada">  
											Partida 
										</div>
										<div class="col-md-7" title="Produto Utilizado">
											Produto 
										</div>
										<div class="col-md-1" title="Valor Unitário  R$">
											<span style="float:right" title="Valor Unitário R$"> 
												Valor Un R$
											</span>
										</div>
										<div class="col-md-1" title="Valor Total R$ " >
											<span style="float:right" title="Valor Total R$ ">
											Valor Total R$
											</span>
										</div>
									</div>
									<?
									$listalote = cprod::listavalorlote($_1_u_lote_idlote, 1);
									?>
									<div class="col-md-12" style="font-weight: bold; background-color: #e6e6e6;">
										<div class="col-md-2" title="Lote Fabricado">
											<?= $stqtdproduzida ?>
										</div>
										<div class="col-md-1 nowrap" title="Lote Produzido">  
											<? echo ($_1_u_lote_partida . '-' . $_1_u_lote_exercicio); ?>
										</div>
										<div class="col-md-7" title="Produto Produzido">
											<?= traduzid("prodserv", "idprodserv", "descr", $_1_u_lote_idprodserv) ?>
										</div>
										<div class="col-md-1" style="text-align:right" title="Custo R$ Unitário do Produto Produzido">
											Total R$:
										</div>
										<div class="col-md-1" title="Custo R$ dos Produtos Utilizados no Lote " >
										<span style="float:right" title="Custo R$ Total dos Produtos Utilizados no Lote : ${valLote}">
											${valLote}
										</span>
										</div>
									</div>
								</div>
							</div>
							
							<h4>Testes aplicados</h4>
							<div class="panel panel-default" style="margin-top: 0px !important; border-color:#ffffff00 !important; background-color:#f5f5f505 !important; padding-bottom: 20px;">
								<div class="panel-body" style="font-size:12px;padding: 0px !important;">
									<div class="col-md-12 panel-heading" style="border-bottom: 1px solid #cec8c8b3;  font-weight: bold; background-color: #e6e6e6; <?= $margin ?>" lvl="<?= $lvl ?>">
										<div class="col-md-1" title="Quantidade Utilizada">
											Qtd
										</div>
										<div class="col-md-1" title="Partida Utilizada">  
											Resultado 
										</div>
										<div class="col-md-1" title="Registro">
											Registro
										</div>
										<div class="col-md-7" title="Produto Utilizado">
											Produto 
										</div>
										<div class="col-md-1" title="Valor Unitário  R$">
											<span style="float:right" title="Valor Unitário R$"> 
												Valor Un R$
											</span>
										</div>
										<div class="col-md-1" title="Valor Total R$ " >
											<span style="float:right" title="Valor Total R$ ">
											Valor Total R$
											</span>
										</div>               
									</div>
									${valortestes?listatestes:'<div class="col-md-12" style="border-bottom: 1px solid #cec8c8b3;text-align:center;color: grey;padding: 8px;"> Nenhum teste aplicado</div>'}
									<div class="col-md-12" style="font-weight: bold; background-color: #e6e6e6;">
										<div class="col-md-2"></div>
										<div class="col-md-1 nowrap"></div>
										<div class="col-md-7"></div>
										<div class="col-md-1 text-right">
											Total R$
										</div>
										<div class="col-md-1">
											<span style="float:right" title="Custo R$ Total dos Testes Utilizados no Lote">
												<?= number_format(tratanumero($testes['valor']), 4, ',', '.'); ?>
											</span>
										</div>
									</div>
								</div>
							</div>
							
							<h4>Rateio</h4>
							<div class="panel panel-default" style="margin-top: 0px !important; border-color:#ffffff00 !important; background-color:#f5f5f505 !important; padding-bottom: 20px;">
								<div class="panel-body" style="font-size:12px;padding: 0px !important;">
									<div class="col-md-12 panel-heading" style="border-bottom: 1px solid #cec8c8b3;  font-weight: bold; background-color: #e6e6e6; <?= $margin ?>" lvl="<?= $lvl ?>">
										<div class="col-md-1" title="">
											Data Inicio
										</div>
										<div class="col-md-1" title="">
											Data Fim
										</div>
										<div class="col-md-2" title="Empresa">
											Empresa
										</div>
										<div class="col-md-7" title="Unidade">
											Unidade 
										</div>

										<div class="col-md-1" title="Valor Total R$ " >
											<span style="float:right" title="Valor Total R$ ">
											Valor R$
											</span>
										</div>
									</div>
									${valorrateio?listarateio:'<div class="col-md-12" style="border-bottom: 1px solid #cec8c8b3;text-align:center;color: grey;padding: 8px;"> Nenhum rateio aplicado</div>'}
									<div class="col-md-12" style="font-weight: bold; background-color: #e6e6e6;">
										<div class="col-md-2"></div>
										<div class="col-md-1"></div>
										<div class="col-md-7"></div>
										<div class="col-md-1 text-right">
											Total R$
										</div>
										<div class="col-md-1">
											<span style="float:right" title="Custo R$ Total dos Testes Utilizados no Lote">
												<?= number_format(tratanumero($rateios['valor']), 4, ',', '.'); ?>
											</span>
										</div>
									</div>
								</div>
							</div>

							<div class="col-md-12" style="font-weight: bold;">
								<div class="col-md-2"></div>
								<div class="col-md-1"></div>
								<div class="col-md-7"></div>
								<div class="col-md-1">
									<h4>Total R$</h4>
								</div>
								<div class="col-md-1">
									<span style="float:right" title="">
										<h4>${total}</h4>
									</span>
								</div> 
							</div>
						</div>
					</div>
				</div>
			`); //v_url = "?_modulo="+modulo+"&_acao=u&tipo=rateio&funcao=COBRAR&stidrateioitemdest="+v_idrateioitemdest+"&_idempresa="+v_idempresa+"&dataini="+dataini+"&datafim="+datafim+"&idrateiocusto="+idrateiocusto;

							
			$oContent.find('span[href^="#collapse-vallote-"]').on('click', function() {
				let vth = $(this);
				let kHref = $(vth.attr('href'));
				
				if (kHref.hasClass('hidden')) {
					kHref.removeClass('hidden');
					vth.children('i').removeClass('fa-angle-right').addClass('fa-angle-down');
					vth.parent().parent().css('font-weight', 'bold');
				} else {
					kHref.addClass('hidden');
					vth.children('i').removeClass('fa-angle-down').addClass('fa-angle-right');
					vth.parent().parent().css('font-weight', 'normal');
				}
			});
			
			CB.modal({
				titulo: strCabecalho,
				corpo: [$oContent],
				classe: 'noventa',
				aoAbrir: function(vthis) {
					let vTotal = calculaValorAcumuladoPorIdlotecons();
					console.log(`Valor Total Acumulado: ${vTotal}`);
				}
			});		
		} //function financeiro(inidnfitem,inlinha){

		if($('#mostraval').length){
			$('#mostraval')[0].title = 'Custo Total: R$ <?= number_format(tratanumero($valorlote+$testes['valor']+$rateios['valor']), 4, ',', '.') ?>';
		}
	<? } ?>
	
	function calculaValorAcumuladoPorIdlotecons(idlotecons = 0, $objPai = null, lvl = 0) {
		let $objFilho = ($objPai === null) ?
			$(`div[lvl='${lvl}']`) :
			$objPai.siblings(`#collapse-vallote-${idlotecons}`).find(`div[lvl='${lvl}']`);

		var vTotal = 0;

		$objFilho.each(function(i, o) {
			let $o = $(o);

			if ($o.children('[vallote]').length > 0) {
				vTotal += parseFloat($o.children('[vallote]').attr('vallote')) || 0;

			} else if ($o.children('[idlotecons-valloteacumulado]').length > 0) {

				let idlotecons = $o.children('[idlotecons-valloteacumulado]').attr('idlotecons-valloteacumulado');
				vTotal += calculaValorAcumuladoPorIdlotecons(idlotecons, $o, lvl + 1);
			}

		});

		if ($objPai != null) {
			let vTotalFormatado = parseFloat(vTotal.toFixed(2))
				.toLocaleString('en')
				.replace('.', '_')
				.replaceAll(',', '.')
				.replace('_', ',');
			if (vTotalFormatado == "0")
				vTotalFormatado += ",00";
			$objPai.children('[idlotecons-valloteacumulado]').html(`
				<span style="float:right" title=" R$: ${vTotalFormatado}">
					${vTotalFormatado}
				</span>
			`);
		}

		return vTotal;
	}

	function altconferido(inval, inidlotefracao) {
		CB.post({
			objetos: "_x_u_lotefracao_idlotefracao=" + inidlotefracao + "&_x_u_lotefracao_conferido=" + inval,
			parcial: true

		});
	}


	function copiavalor(idlote, idlotefracao, idobjeto, valor) {

		CB.post({
			objetos: "_x_i_lotecons_idlote=" + idlote + "&_x_i_lotecons_idlotefracao=" + idlotefracao + "&_x_i_lotecons_idobjeto=" + idobjeto + "&_x_i_lotecons_tipoobjeto=lote&_x_i_lotecons_qtdd=" + valor,
			parcial: "true"
		});
	}

	function transferirValores() {
		if (confirm("Deseja realmente Utilizar todos os lotes?")) {
			let objetoConcat = "";
			let qtdValores = $('.transferirvalores').length;
			$('.transferirvalores').each(function(i, item) {
				var objeto = `_x${i + 1}_i_lotecons_idlote=${$(item).attr('idlote')}` +
					`&_x${i + 1}_i_lotecons_idlotefracao=${$(item).attr('idlotefracao')}` +
					`&_x${i + 1}_i_lotecons_idobjeto=${$(item).attr('idobjeto')}` +
					`&_x${i + 1}_i_lotecons_tipoobjeto=lote` +
					`&_x${i + 1}_i_lotecons_qtdd=${$(item).attr('valor')}`;
				if (i < (qtdValores - 1)) {
					objetoConcat += `${objeto}&`;
				} else {
					objetoConcat += objeto;
				}
			});

			CB.post({
				objetos: objetoConcat,
				parcial: "true"
			});
		}
	}

	//Esconde o botão de transferencia, caso não tenha nenhum lote para utilizar
	if ($('.transferirvalores').length == 0) {
		$('.botaoTransferirTodos').hide();
	}

	function geraamostra(vthis, vidlote) {

		$(vthis).toggleClass('blink');

		$.ajax({
			type: "get",
			url: "ajax/geraamostra.php",
			data: {
				idlote: vidlote
			},
			success: function(data) {
				location.reload();
			},
			error: function(objxmlreq) {
				alert('Erro:<br>' + objxmlreq.status);
			}
		});
	}

	$(function() {
		$('[data-toggle="tooltip"]').tooltip();
	})

	function verificadiluicao(vthis) {
		if (vthis.classList.contains('diluicaoerrada')) {
			// Se possuir, faça algo aqui
			alert("A diluição " + $(vthis).val() + " não é válida!");
			vthis.value = "";
		}
		if (vthis.classList.contains('consumoerrado')) {
			// Se possuir, faça algo aqui
			alert("Não é permitido consumir mais do que o estoque disponível do Lote!");
			vthis.value = "";
		}
		if (vthis.classList.contains('infdiluicao')) {
			// Se possuir, faça algo aqui

			alert("Valor inválido. Inserir diluição.");
			vthis.value = "";
		}
	}



	function mostraConsumo(inOConsumo) {
		inOConsumo.style.backgroundColor = "";
		inOConsumo.classList.remove('diluicaoerrada');
		inOConsumo.classList.remove('consumoerrado');
		inOConsumo.classList.remove('infdiluicao');


		debugger;
		$o = $(inOConsumo);



		//$sQtddisp_exp=$o.attr("sQtddisp_exp");
		somaUtilizacao = 0;

		if ($o.val()) {

			if ($o.attr("sQtddisp_exp") != "" && ($o.val().toLowerCase().indexOf("e") <= 0 && $o.val().toLowerCase().indexOf("d") <= 0)) {
				inOConsumo.classList.add('infdiluicao');
				inOConsumo.style.backgroundColor = "#ffff0075";
				//alertAtencao("Valor inválido. <br> Inserir e ou d.");
				return false;
			} else if ($o.attr("sQtddisp_exp") != "" && ($o.val().toLowerCase().indexOf("e") >= 0 || $o.val().toLowerCase().indexOf("d") >= 0)) {
				sQtddisp_exp = $o.attr("sQtddisp_exp");
				var stringOriginal = $o.val();
				var matches_orig = sQtddisp_exp.match(/^([\d.]+)([a-zA-Z]+)(\d+)$/);
				var matches = stringOriginal.match(/^([\d.]+)([a-zA-Z]+)(\d*)$/);

				// Verificando se a string foi dividida corretamente
				if (matches) {

					// Valor2 é a sequência de letras após os números
					var valor1 = matches_orig[2] + (matches_orig[3] || ""); // Concatenando a letra ("d" ou "e") com os números seguintes
					var valor2 = matches[2] + (matches[3] || ""); // Concatenando a letra ("d" ou "e") com os números seguintes
					if (valor1 != valor2) {
						inOConsumo.style.backgroundColor = "#ff00003b";
						inOConsumo.classList.add('diluicaoerrada');

						//alertAtencao("Valor inválido. <br> Diluição inválida");
						return false;


					}

				} else {
					alertAtencao("Valor inválido. <br> Diluição inválida");
					return false;
				}
			} else if(
				!$o.attr("sQtddisp_exp")
				&& ($o.val().toLowerCase().indexOf("e") !== -1 || $o.val().toLowerCase().indexOf("d") !== -1)
			) {
				inOConsumo.classList.add('infdiluicao');
				inOConsumo.style.backgroundColor = "#ffff0075";
				return alertAtencao("Valor inválido.");
			}

			valor = $o.val().replace(/,/g, '.');
			valor = normalizaQtd(valor);

			somaUtilizacao = valor;
		}

		sQtddisp = normalizaQtd($o.attr("sQtddisp"));

		if (somaUtilizacao > 0) {
			if (somaUtilizacao > sQtddisp) {
				inOConsumo.classList.add('consumoerrado');
				inOConsumo.style.backgroundColor = "#ff00003b";
				//alertAtencao("Valor inválido. <br> O consumo é maior que a quantidade disponível.");
				return false;
			}
		}

	}

	function mostraConsumoCred(inOConsumo) {
		inOConsumo.style.backgroundColor = "";
		inOConsumo.classList.remove('diluicaoerrada');
		inOConsumo.classList.remove('consumoerrado');
		inOConsumo.classList.remove('infdiluicao');


		debugger;
		$o = $(inOConsumo);



		//$sQtddisp_exp=$o.attr("sQtddisp_exp");
		somaUtilizacao = 0;

		if ($o.val()) {

			if ($o.attr("sQtddisp_exp") != "" && ($o.val().toLowerCase().indexOf("e") <= 0 && $o.val().toLowerCase().indexOf("d") <= 0)) {
				inOConsumo.classList.add('infdiluicao');
				inOConsumo.style.backgroundColor = "#ffff0075";
				//alertAtencao("Valor inválido. <br> Inserir e ou d.");
				return false;
			} else if ($o.attr("sQtddisp_exp") != "" && ($o.val().toLowerCase().indexOf("e") >= 0 || $o.val().toLowerCase().indexOf("d") >= 0)) {
				sQtddisp_exp = $o.attr("sQtddisp_exp");
				var stringOriginal = $o.val();
				var matches_orig = sQtddisp_exp.match(/^([\d.]+)([a-zA-Z]+)(\d+)$/);
				var matches = stringOriginal.match(/^([\d.]+)([a-zA-Z]+)(\d*)$/);

				// Verificando se a string foi dividida corretamente
				if (matches) {

					// Valor2 é a sequência de letras após os números
					var valor1 = matches_orig[2] + (matches_orig[3] || ""); // Concatenando a letra ("d" ou "e") com os números seguintes
					var valor2 = matches[2] + (matches[3] || ""); // Concatenando a letra ("d" ou "e") com os números seguintes
					if (valor1 != valor2) {
						inOConsumo.style.backgroundColor = "#ff00003b";
						inOConsumo.classList.add('diluicaoerrada');

						//alertAtencao("Valor inválido. <br> Diluição inválida");
						return false;


					}

				} else {
					alertAtencao("Valor inválido. <br> Diluição inválida");
					return false;
				}
			}

			valor = $o.val().replace(/,/g, '.');
			valor = normalizaQtd(valor);

			somaUtilizacao = valor;
		}



	}


	function normalizaQtd(inValor) {
		var sVlr = "" + inValor;
		var $arrExp;
		var fVlr;
		if (sVlr.toLowerCase().indexOf("d") > -1) {
			$arrExp = sVlr.toLowerCase().split('d');
			fVlr = (parseFloat($arrExp[0]) * parseFloat($arrExp[1])).toFixed(2);
			fVlr = parseFloat(fVlr);
		} else if (sVlr.toLowerCase().indexOf("e") > -1) {
			$arrExp = sVlr.toLowerCase().split('e');
			fVlr = $arrExp[0] * Math.pow(10, $arrExp[1]);
		} else {
			fVlr = parseFloat(sVlr).toFixed(2);
		}

		return parseFloat(fVlr);
	}

	function vincularTagAoLote(idlote, vthis) {
		debugger
		var cbpost = "";
		arraycheck = $(vthis).parent().parent().parent().find(":checkbox").each((i, o) => {
			if ($(o).prop("checked") == true && $(o).attr("idobjetovinculo") == "") {
				cbpost += `_x${i}_i_objetovinculo_idobjeto=${idlote}
							&_x${i}_i_objetovinculo_tipoobjeto=lote
							&_x${i}_i_objetovinculo_idobjetovinc=${$(o).attr("idtag")}
							&_x${i}_i_objetovinculo_tipoobjetovinc=tag&`;
			} else if ($(o).prop("checked") == false && $(o).attr("idobjetovinculo") != "") {
				cbpost += `_xd${i}_d_objetovinculo_idobjetovinculo=${$(o).attr("idobjetovinculo")}&`;
			} else {
				cbpost += "";
			}
		});

		if (cbpost != "") {
			CB.post({
				"objetos": cbpost,
				parcial: true
			});
		}
	}

	/**
	 * O sistema atribuirá automaticamente como substatus 'Revalidar' a lotes com 30 dias ou menos para o vencimento.
	 */
	function revalidarLote() {
		$.ajax({
			url: './../ajax/lote.php',
			method: 'POST',
			dataType: 'json',
			data: {
				action: 'revalidarLote',
				params: idLote
			},
			success: res => {
				if(res.error) {
					alertAtencao(res.error);

					return false;
				}

				alertAzul(res.message);
			},
			error: err => {
				console.log(err);
				alertErro('Ocorreu um erro ao revalidar os lote.');
			}
		})
	}

	function salvarHistoricoVencimento() {
		const justificativa = $('#select-justificativa').val();
		let objetos = {};
		$('.campos-historico input, .campos-historico select').get().forEach((item) => {
			objetos[item.name] = item.value;
		});

		if(justificativa == 'Revalidação da Data') {
			objetos['_1_u_lote_substatus'] = 'REVALIDADO';

			// Obter a data atual
			const dataAtual = new Date();
    
			// Obter a data do campo
			const dataSelecionada = new Date(`${converterParaFormatoAmericano($('#nova-data-validade').val())}T00:00:00`);
			
			// Adicionar 30 dias à data atual
			// const dataLimite = new Date(`${new Date().getFullYear()}-${String(new Date().getMonth() + 1).padStart(2, '0')}-${new Date().getDate()}T00:00:00`);
			const dataLimite = new Date();
			dataLimite.setDate(dataAtual.getDate() + 30);
			
			// Verificar se a data selecionada é superior à data limite
			if (dataSelecionada < dataLimite) return alertAtencao('A data de revalidação deve ser superior a 30 dias da data atual.');

			// Salvando histórico de revalidação
			objetos['_h2_i_modulohistorico_idobjeto'] = idLote;
			objetos['_h2_i_modulohistorico_campo'] = 'substatus';
			objetos['_h2_i_modulohistorico_tipoobjeto'] = CB.modulo;
			objetos['_h2_i_modulohistorico_valor_old'] = subStatus;
			objetos['_h2_i_modulohistorico_valor'] = 'REVALIDADO';
			objetos['_h2_i_modulohistorico_justificativa'] = justificativa;
		}

		if(status == 'VENCIDO') {
			// Atualizar status do lote
			$.ajax({
				url: './../ajax/lote.php',
				method: 'POST',
				dataType: 'json',
				data: {
					action: 'atualizarStatusLote',
					params: [idLote, idfluxostatus, status]
				},
				success: res => {
					if(res.error) {
						alertAtencao(res.error);

						return false;
					}

					CB.post({
						objetos,
						parcial: true
					});
				},
				error: err => {
					console.log(err);
					alertErro('Ocorreu um erro ao salvar o histórico de vencimento.');
				}
			});
		} else {
			CB.post({
				objetos,
				parcial: true
			});
		}
	}

	function modalAlterarValidade() {
		let selectGestor = '',
			selectResponsaveis = '';
		
		let inputJustificativa = `<select name="_h1_i_modulohistorico_justificativa" id="select-justificativa" disabled>
									<option ${gestor ? 'selected' : ''} value="Erro de Data">Erro de Data</option>
									<option ${!gestor && responsavel ? 'selected' : ''} value="Revalidação da Data">Revalidação da Data</option>
								</select>`

		let corpo = `<div id="altvencimento${idLote}">
							<table class="table table-hover campos-historico">
								<tr>
									<td>Vencimento</td>
									<td>
										<input name="_1_u_lote_idlote" value="${idLote}" type="hidden">
										<input name="_1_u_lote_idprodserv" value="${idProdserv}" type="hidden">
										<input name="_1_u_lote_partida" value="${partida}" type="hidden">
										<input name="_h1_i_modulohistorico_idobjeto" value="${idLote}" type="hidden">
										<input name="_h1_i_modulohistorico_campo" value="vencimento" type="hidden">
										<input name="_h1_i_modulohistorico_tipoobjeto" value="${CB.modulo}" type="hidden">
										<input name="_h1_i_modulohistorico_valor_old" value="${vencimentoLote}" type="hidden">
										<input name="_h1_i_modulohistorico_valor" value="${vencimentoLote}" class="size10" type="text" id="nova-data-validade">
									</td>
								</tr>
								<tr>
									<td>Justificativa:</td>
									<td>
										${inputJustificativa}
									</td>
								</tr>
							</table>
						</div>`;
			
		let btnSalvar = `<button class="btn btn-success" onclick="salvarHistoricoVencimento()">Salvar</button>`


		CB.modal({
			titulo: 'Alterar Validade',
			corpo,
			rodape: btnSalvar,
			classe: 'sessenta',
			aoAbrir: function(vthis) {
				$("[name='_h1_i_modulohistorico_valor']").daterangepicker({
					"singleDatePicker": true,
					"locale": CB.jDateRangeLocale
				}).on('apply.daterangepicker', function(ev, picker) {
					console.log(picker.startDate.format('YYYY-MM-DD'));
					$(this).html(picker.startDate.format("DD/MM/YYYY") || "");
				});

				$(`[name="_h1_i_modulohistorico_valor"]`).val(vencimentoLote);
			}
		});
	}

	function abrirModalHistoricoVencimento(dados, titulo = 'Histórico de vencimento') {
		const historicoHTML = dados.map(historico => `<tr>
																			<td>${historico.valor_old}</td>
																			<td>${historico.valor}</td>
																			<td>${historico.justificativa}</td>
																			<td>${historico.nomecurto}</td>
																			<td>${dmahms(historico.criadoem)}</td>
																		</tr>`).join(' ')
		let corpo = `<table class="table table-hover">
						<thead>
							<tr>
								<th scope="col">De</th>
								<th scope="col">Para</th>
								<th scope="col">Justificativa</th>
								<th scope="col">Por</th>
								<th scope="col">Em</th>
							</tr>
						</thead>
						<tbody>
							${historicoHTML.length ? historicoHTML : '<tr><td>Nenhuma alteração realizada.</td></tr>'}
						</tbody>
					</table>`;

		CB.modal({
			titulo,
			corpo
		})
	}

	//o comentario abaixo faz com que este pedaço de script apareça na aba 'sources' do inspetor do google chrome
	//@ sourceURL=<?= $_SERVER["SCRIPT_NAME"] ?>;
</script>
<?
require_once '../inc/php/readonly.php';

?>