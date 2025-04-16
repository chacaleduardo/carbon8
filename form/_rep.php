<?
require_once("../inc/php/validaacesso.php");

if ($_POST) {
	require_once("../inc/php/cbpost.php");
}

//Parà¢metros mandatários para o carbon
$pagvaltabela = "_rep";
$pagvalcampos = array(
	"idrep" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from " . getDbTabela("_rep") . "._rep where idrep = '#pkid'";

/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
require_once("../inc/php/controlevariaveisgetpost.php");

$idRep = isset($_1_u__rep_idrep) ? $_1_u__rep_idrep : 0;

/* *********************************** Tabelas dos databases configurados ****************************** */
function jsonTabelasCarbonApp()
{
	$sql = "select table_schema as db, table_name as tab from information_schema.tables 
			where table_schema='" . _DBAPP . "'
			union all
			select table_schema,table_name as tab from information_schema.tables 
			where table_schema='" . _DBCARBON . "'";

	$res = d::b()->query($sql);

	$arrtmp = array();
	$i = 0;
	while ($r = mysqli_fetch_assoc($res)) {
		$arrtmp[$i]["value"] = $r["tab"];
		$arrtmp[$i]["label"] = $r["tab"];
		$arrtmp[$i]["db"] = $r["db"];
		$i++;
	}

	$json = new Services_JSON();
	return $json->encode($arrtmp);
}

function autocompleteModulos($idrep)
{
	$sqlGetModulos = "SELECT DISTINCT
		m.idmodulo, m.modulo, m.rotulomenu
		FROM
		carbonnovo._modulo m
		WHERE
		NOT EXISTS( SELECT 
				1
			FROM
				carbonnovo._modulorep mr
			WHERE
				(mr.modulo = m.modulo AND mr.idrep = " . $idrep . "))
			AND m.tipo != 'DROP'
			AND m.status = 'ATIVO'
			AND m.modulo != ''";

	$res = d::b()->query($sqlGetModulos);

	$arrMod = array();
	$i = 0;

	while ($r = mysqli_fetch_assoc($res)) {
		$arrMod[$i]["idmodulo"] = $r["idmodulo"];
		$arrMod[$i]["modulolist"] = $r["modulo"] . " (" . $r["rotulomenu"] . ")";
		$arrMod[$i]["modulo"] = $r["modulo"];

		$i++;
	}

	$json = new Services_JSON();
	return $json->encode($arrMod);
}

if ($_1_u__rep_idrep) {
	/*
	 * maf 081110: Caso a tabela informada na pagina de pesquisa seja alterada, é necessário excluir os relacionamentos antigos 
	 * para não sobrar lixo na tabela ou gerar informacoes de colunas inexistentes na tabela
	 */
	$_sqldeltabant = "delete rc
	FROM " . _DBCARBON . "._repcol rc
	where idrep = " . $_1_u__rep_idrep . "
	and not exists(
		select 1 from " . _DBCARBON . "._rep r join " . _DBCARBON . "._mtotabcol t where t.tab=r.tab and t.col=rc.col and r.idrep=" . $_1_u__rep_idrep . "
	)
	and inseridomanualmente='N'";

	//die($_sqldeltabant);
	d::b()->query($_sqldeltabant) or die("Erro ao apagar dados relacionados da tabela anterior: " . mysqli_error(d::b()));

	$sqlfld = "SELECT 
		tc.tab, 
		tc.col,
		tc.rotcurto as rotulo,
		rc.psqkey,
		rc.psqreq,
		tc.datatype, 
		tc.rotcurto, 
		rc.idrepcol,
		rc.idrep,
		rc.visres,
		rc.align,
		rc.grp,
		rc.ordseq,
		rc.ordtype,
		rc.tsum,
		rc.tavg,
		rc.hyperlink,
		rc.entre,
		rc.inseridomanualmente,
		rc.calendario,
		rc.like,
		rc.in,
		rc.inval,
		rc.findinset,
		rc.json,
		(case when isnull(rc.idrepcol) then 'i' else 'u' end) as act,
		rc.ordcol,
		rc.acsum,
		rc.acavg,
		rc.mascara,
		rc.eixograph
	from (" . _DBCARBON . "._rep r 
		join " . _DBCARBON . "._mtotabcol tc) 
		left join " . _DBCARBON . "._repcol rc on (rc.idrep = r.idrep and r.tab = tc.tab and rc.col = tc.col)
	where 
		r.tab = tc.tab
		and r.idrep = " . $_1_u__rep_idrep . "
	UNION ALL -- Colunas inseridas manualmente
	SELECT '',rc.col,'' as rotulo,rc.psqkey,rc.psqreq,'','',rc.idrepcol,rc.idrep,rc.visres,rc.align,rc.grp,rc.ordseq,rc.ordtype,rc.tsum,rc.tavg,rc.hyperlink,rc.entre,rc.inseridomanualmente,rc.calendario,rc.like,rc.in,rc.inval,rc.findinset,rc.json,(case when isnull(rc.idrepcol) then 'i' else 'u' end) as act, rc.ordcol, rc.acsum, rc.acavg, rc.mascara, rc.eixograph
	from " . _DBCARBON . "._repcol rc
	join " . _DBCARBON . "._rep r on r.idrep=rc.idrep and rc.idrep=" . $_1_u__rep_idrep . " and rc.inseridomanualmente='Y' 
	order by visres, ordcol  ";
	//die($sqlfld);
	echo "<!--" . $sqlfld . "-->";
	$resfld = d::b()->query($sqlfld) or die("Erro ao recuperar colunas: " . mysql_error(d::b()));
}
?>
<div class="row">
	<div class="col-md-3">
		<div class="panel panel-default">
			<div style="padding: 3%;" class="panel-heading">

				<input type="hidden" name="_1_<?= $_acao ?>__rep_idrep" value="<?= $_1_u__rep_idrep ?>">
				Id: <label class='alert-warning'><?= $_1_u__rep_idrep ?></label>

				<? if (!empty($_1_u__rep_idrep)) { ?>
					<div class="pull-right">
						Status:
						<select onchange="statusUpd(this, <?= $_1_u__rep_idrep ?>)" style="margin-top: -2px;" name="_1_u__rep_status" vnulo="">
							<? fillselect(array('ATIVO' => 'ATIVO', 'INATIVO' => 'INATIVO'), $_1_u__rep_status); ?>
						</select>
					</div>
				<? } ?>
			</div>

			<div class="panel-body">
				<table>
					<tr>
						<td>Rótulo Relatório:</td>
						<td>
							<input type="text" name="_1_<?= $_acao ?>__rep_rep" class="bold" vnulo="" value="<?= $_1_u__rep_rep ?>">
						</td>
					</tr>
					<tr>
						<td>Title Butoon:</td>
						<td>
							<textarea rows="4" type="text" name="_1_<?= $_acao ?>__rep_titlebutton" style="width: 100%"><?= $_1_u__rep_titlebutton ?></textarea>

						</td>
					</tr>
					<tr>
						<td>Ícone (class):</td>
						<td nowrap>
							<div class="input-group input-group-sm">
								<input type="text" name="_1_<?= $_acao ?>__rep_cssicone" value="<?= $_1_u__rep_cssicone ?>">
								<span class="input-group-addon" title="Ícone">
									<i id="seletoricones" class="<?= ($_1_u__rep_cssicone) ? $_1_u__rep_cssicone : "fa fa-smile-o" ?> fa-2x fade"></i>
								</span>

							</div>
						</td>
					</tr>
					<tr>
						<td>Mostra Filtros:</td>
						<td>
							<select name="_1_<?= $_acao ?>__rep_showfilters">
								<? fillselect(array('Y' => 'S', 'N' => 'N'), $_1_u__rep_showfilters); ?>
							</select>
						</td>
					</tr>
					<tr>
						<td>Url:</td>
						<td>
							<input type="text" name="_1_<?= $_acao ?>__rep_url" vnulo="" value="<?= $_1_u__rep_url ?>">
						</td>
					</tr>
					<? if ($_1_u__rep_idrep) { ?>
						<tr>
							<td>Tipo Relatório</td>
							<td>
								<select name="_1_<?= $_acao ?>__rep_idreptipo">
									<option value=""></option>
									<? fillselect("SELECT 
											idreptipo, reptipo
										FROM
											carbonnovo._reptipo
										WHERE
											status = 'ATIVO'
										ORDER BY reptipo", $_1_u__rep_idreptipo); ?>
								</select>
							</td>
						</tr>
						<tr>
							<td>Mostrar Menu Relatório</td>
							<td>
								<select name="_1_<?= $_acao ?>__rep_mostrarmenurelatorio">
									<? fillselect(['Y' => 'Sim', 'N' => 'Não'], $_1_u__rep_mostrarmenurelatorio); ?>
								</select>
							</td>
						</tr>
						<tr>
							<td>Dashboard:</td>
							<td>
								<? if ($_1_u__rep_dashboard == 'N') { ?>
									<input type="checkbox" onclick="toggle('Y')">
								<? } else { ?>
									<input type="checkbox" onclick="toggle('N')" checked>
								<? } ?>
							</td>
						</tr>
					<? } ?>
				</table>
			</div>
		</div>

		<? if ($_1_u__rep_idrep) { ?>
			<div class="panel panel-default">
					<div class="panel-heading">Observações</div>
					<div class="panel-body">
						<textarea rows="6" type="text" name="_1_<?= $_acao ?>__rep_observacoes" style="width: 100%"><?= $_1_u__rep_observacoes ?></textarea>
					</div>
			</div>
		<?}?>


		<? if ($_1_u__rep_idrep) { ?>
			<div class="panel panel-default">
				<div class="panel-heading">Gráfico do Relatório</div>
				<div class="panel-body">
					<table style="width:100%">
						<tr>
							<td style="width:20%">Tipo de Gráfico:</td>
							<td>
								<select name="_1_<?= $_acao ?>__rep_tipograph">
									<option value=""></option>
									<? fillselect(array(
										'LINHA' => 'LINHA', 
										'PIZZA' => 'PIZZA', 
										'BARRASLATERAIS' => 'BARRAS HORIZONTAIS', 
										'BARRASVERTICAIS' => 'BARRAS VERTICAIS', 
										'BARRASAGRUPADAS' => 'BARRAS AGRUPADAS'
										), $_1_u__rep_tipograph); ?>
								</select>
							</td>
						</tr>
					</table>


					<? if (!empty($_1_u__rep_tipograph)) { ?>
						<hr>
						<table class="planilha grade compacto" id="tablegraph" style="width:100%">
							<tr>
								<th>Col</th>
								<th>Axis/Group</th>
							</tr>
							<?
							$ln = 1000;
							while ($rg = mysql_fetch_array($resfld)) {
								if ($rg["visres"] == "Y") { ?>
									<tr>
										<td>
											<input type="hidden" name="_graph<?= $ln ?>_u__repcol_idrepcol" value="<?= $rg["idrepcol"] ?>">
											<?= $rg["col"] ?>
										</td>
										<td>
											<select name="_graph<?= $ln ?>_u__repcol_eixograph" identificador="<?= $ln ?>" class="selecteixo" onchange="removerEixosIguais(this, '<?= $ln ?>')">
												<option value=""></option>
												<? fillselect(array('X' => 'Axis X', 'Y' => 'Axis Y', 'G' => 'Let Group'), $rg["eixograph"]); ?>
											</select>
										</td>
									</tr>
							<?
									$ln++;
								}
							} ?>
						</table>
					<? } ?>
					<div class="col-xs-12 form-group mt-3">
						<label for="">Valor pós-fixado</label>
						<input name="_1_<?= $_acao ?>__rep_valorposfixado" type="text" class="form-control" value="<?= $_1_u__rep_valorposfixado ?>">
					</div>
				</div>
			</div>



			<div class="panel panel-default">
				<div class="panel-heading">Módulos</div>
				<div class="panel-body">
					<?
					$srep = "select m.*, mr.ord, mr.idmodulorep
						from " . getDbTabela("_modulo") . "._modulo m
						join " . getDbTabela("_modulorep") . "._modulorep mr on mr.modulo=m.modulo and mr.idrep='" . $_GET["idrep"] . "'
						order by m.modulo ";

					$rrep = d::b()->query($srep) or die("Erro ao recuperar relatorios: " . mysqli_error(d::b()));
					$arrColunas = mysqli_fetch_fields($rrep);
					$i = 0;
					while ($robj = mysqli_fetch_assoc($rrep)) {
						$i = $i + 1;
						//para cada coluna resultante do select cria-se um item no array
						foreach ($arrColunas as $col) {
							$arrret[$i][$col->name] = $robj[$col->name];
						}
					}

					?>
					<table id="tbrelatorios" class="table table-striped planilha">
						<?
						foreach ($arrret as $i => $arrrep) {
							$ifi++;

						?>
							<tr class="dragRep" idmodulorep="<?= $arrrep["idmodulorep"] ?>">
								<td title="Ordenar Relatórios">
									<i class="fa fa-arrows cinzaclaro hover move"></i>
								</td>
								<td>
									<a target="_blank" href="?_modulo=_modulo&_acao=u&idmodulo=<?=$arrrep["modulo"]?>"> <?= $arrrep["rotulomenu"] ." (". $arrrep["modulo"].")"?> </a> 
									<?if($arrrep['status']=="INATIVO"){?>
										<i class="fa fa-exclamation-triangle vermelho blink" title="Este módulo está inativo"></i>
									<?}?>
								</td>

								<td>
									<i class="fa fa-times vermelho fade" title="Excluir" onclick="CB.post({objetos:'_del_d__modulorep_idmodulorep=<?= $arrrep["idmodulorep"] ?>',parcial:true})"></i>
								</td>
							</tr>
						<?
						}
						?>

						<?
						?>

					</table>
					<input id="associarRelatorio" type="text" title="Associar Relatório" autocomplete="off">
				</div>
			</div>
		<?if(array_key_exists("repmaster", getModsUsr("MODULOS"))){?>
			<div class="panel panel-default">
				<div class="panel-heading pointer" onclick="MontaModal()">Lps</div>
				<div id="modal_lps" class="panel-body" style="display: none;">
					<table id="tblps" class="table table-striped planilha">
						<?
						$srep = "SELECT e.empresa,
									e.sigla,
									l.idlp,
									l.descricao,
									lm.idlprep,
									l.idempresa                  as idempresa,
									lm.flgunidade,
									lm.flgidpessoa,
									lm.flgcontaitem,
									rc.idrep                     as 'btnrep',
									rc1.idrep                    as 'btnreporg',
									rc2.idrep                    as 'btnrepcti',
									group_concat(lpm.idlpmodulo) as 'alertlpmod'
								FROM carbonnovo._lprep lm
										JOIN
									carbonnovo._lp l ON (lm.idlp = l.idlp)
										JOIN
									empresa e ON (l.idempresa = e.idempresa)
										left join
									carbonnovo._repcol rc on rc.idrep = lm.idrep and rc.col = 'idunidade'
										left join
									carbonnovo._repcol rc1 on rc1.idrep = lm.idrep and rc1.col = 'idpessoa'
										left join
									carbonnovo._repcol rc2 on rc2.idrep = lm.idrep and rc2.col = 'idcontaitem'
										left join
									carbonnovo._modulorep mr on mr.idrep = lm.idrep
										left join
									carbonnovo._lpmodulo lpm on lpm.idlp = l.idlp and mr.modulo = lpm.modulo
								WHERE lm.idrep =  ".$_1_u__rep_idrep."
									AND l.status = 'ATIVO'
									AND e.status = 'ATIVO'
									group by l.idlp
									order by e.idempresa, l.descricao;";
						$rrep = d::b()->query($srep) or die("Erro ao recuperar relatorios: " . mysqli_error(d::b()));
						$arrColunas = mysqli_fetch_fields($rrep);
						$i = 0;
						$empresa = "";
						while($rw = mysqli_fetch_assoc($rrep)){

							if($rw['btnrep']==''){
								$displayBtnUnidade="display:none";
							}else{
								$displayBtnUnidade="display:block";
							}

							if($rw['btnreporg']==''){
								$displayBtnOrganograma="display:none";
							}else{
								$displayBtnOrganograma="display:block";
							}

							if($rw['btnrepcti']==''){
								$displayBtnCTI="display:none";
							}else{
								$displayBtnCTI="display:block";
							}

							if ($rw["flgunidade"] == 'Y') {
			
								$rotPermissaoU="Restrito";
								$classeBtU="btn-primary";//azul
								$corIcoU="azul";
								$icoPermissaoU="fa fa-building";
			
							}else{
									$rotPermissaoU="Sem Restrição";
									$classeBtU="nopermission";//vermelho
									$corIcoU="cinza";
									$icoPermissaoU="fa fa-building";

							}

							if ($rw["flgcontaitem"] == 'Y') {
			
								$rotPermissaoCTI="Restrito";
								$classeBtCTI="btn-primary";//azCTIl
								$corIcoCTI="azul";
								$icoPermissaoCTI="fa fa-credit-card-alt";
			
							}else{

								$rotPermissaoCTI="Sem Restrição";
								$classeBtCTI="nopermission";//vermelho
								$corIcoCTI="cinza";
								$icoPermissaoCTI="fa fa-credit-card-alt";

							}

							if ($rw["flgidpessoa"] == 'Y') {
					
								$rotPermissaoO="Permissão";
								$classeBtO="btn-primary";//azul
								$corIcoO="azul";
								$icoPermissaoO="fa fa-sitemap";
			
							}else{
								$rotPermissaoO="Sem Permissão";
								$classeBtO="nopermission";//vermelho
								$corIcoO="cinza";
								$icoPermissaoO="fa fa-sitemap";
							}


							if ($empresa != $rw['empresa']) {
								$empresa = $rw['empresa']; ?>
								
								<tr style="background-color: #cccccc;">
									<td colspan="3" style="font-weight: bold; text-align:center;">
										<?= $rw['empresa'] ?>
									</td>
								</tr>
							<?}?>
							<tr>
								<td class="hoverazul">
									<a target="_blank" href="?_modulo=_lp&_acao=u&idlp=<?= $rw['idlp'] ?>"><?= $rw['descricao'] ?></a>
								</td>
								<td>
									<?if(array_key_exists("repmaster", getModsUsr("MODULOS"))){?>
										<i class="fa fa-trash vermelho hoverpreto pointer" onclick="CB.post({objetos:`_del_d__lprep_idlprep=<?= $rw['idlprep'] ?>`,parcial:true})" style="margin-left: 6px; margin-right: 0px;"></i>
									<?}?>
								</td>
							</tr>
						<?}?>
					</table>
					<?
					if(array_key_exists("repmaster", getModsUsr("MODULOS"))){?>
						<select id="associaLP" class="selectpicker" title="Associar LP" data-actions-box="true" multiple="multiple" data-live-search="true">
							<?
							$sqlop="SELECT lp.idlp, concat(e.sigla,' - ',lp.descricao) as descr 
							from "._DBCARBON."._lp lp 
							JOIN empresa e on (e.idempresa = lp.idempresa)
							where lp.status='ATIVO'
							and not exists (select 1 from "._DBCARBON."._lprep lr where lr.idlp=lp.idlp and lr.idrep=".$_1_u__rep_idrep.")";
							
							$resop = d::b()->query($sqlop);
							while($rowm = mysqli_fetch_assoc($resop)){
								echo '<option data-tokens="'.retira_acentos($rowm['descr']).'" value="'.$rowm['idlp'].'" >'.$rowm['descr'].'</option>'; 
							}
							?>
						</select>
						<button class="btn btn-success btn-xs" onclick="vinculaLps()"><i class="fa fa-check"></i>Adicionar</button>
					<?}?>
				</div>
			</div>
		<? } ?>
	<? } ?>

	</div>
	<?
	if ($_1_u__rep_idrep) {



		if ($_1_u__rep_showfilters == "Y") {
	?>
			<div class="col-md-9">
				<div class="panel panel-default">
					<div class="panel-heading">
						<table>
							<tr>
								<td>Tabela de dados para Relatório:</td>
								<td>
									<div class="input-group input-group-sm">
										<input type="text" name="_1_<?= $_acao ?>__rep_tab" cbvalue="<?= $_1_u__rep_tab ?>" value="<?= $_1_u__rep_tab ?>">
										<span class="input-group-addon" title="Editar Tabela">
											<a class="fa fa-pencil" href="?_modulo=_mtotabcol&_acao=u&PK=<?= _DBAPP . "." . $_1_u__rep_tab ?>" target="_blank"></a>
										</span>
									</div>
								</td>
							</tr>
						</table>
					</div>
					<div class="panel-body">
						<table>
							<tr>
								<td>Cabeçalho:</td>
								<td>
									<input type="text" name="_1_<?= $_acao ?>__rep_header" value="<?= $_1_u__rep_header ?>">
								</td>
								<td>Rodapé:</td>
								<td>
									<input type="text" name="_1_<?= $_acao ?>__rep_footer" value="<?= $_1_u__rep_footer ?>">
								</td>
								<td>Contador:</td>
								<td>
									<select name="_1_<?= $_acao ?>__rep_showtotalcounter">
										<? fillselect(array('Y' => 'S', 'N' => 'N'), $_1_u__rep_showtotalcounter); ?>
									</select>
								</td>
							</tr>
						</table>
						<hr>
						<label>Colunas do Relatório:</label>
						<table style="width: 100%;" class="planilha grade compacto" <?= $estado ?>>
							<tr>
								<th>Ord</th>
								<th>Col</th>
								<th>Rót Curto</th>
								<th>Psq Key</th>
								<th>Psq Req</th>
								<th>Vis</th>
								<th>Align</th>
								<th>Group</th>
								<th>Sum</th>
								<th>ACSum</th>
								<th>Avg</th>
								<th>ACAvg</th>
								<th>Máscara</th>
								<th>Order(+pos)</th>
								<th>Hyperlink</th>
							</tr>
							<?
							$ln = 2;
							$tabIndex=1;
							//Efetuar reset no ponteiro para novo loop
							mysqli_data_seek($resfld, 0);
							while ($rf = mysql_fetch_array($resfld)) {

								$ln++;

								switch ($rf["act"]) {
									case "i":
										$icon = "<img src='../img/novo16.gif' border='0' alt='Novo Item'>";
										break;
									case "u":
										$icon = "<img src='../img/editar16.gif' border='0' alt='Alterar Item'>";
										break;
									case "d":
										$icon = "<img src='../img/lixo16.gif' border='0' alt='Deletar Item'>";
										break;
									default:
										$icon = "<img src='../img/editar16.gif' border='0' alt='Alterar Item'>";
										break;
								}

								$trcolor = "";
								$trstyle = "";

								if ($rf["visres"] == "Y") $trcolor = "#ccccff";
								if ($rf["psqkey"] == "Y") $trcolor = "#99cc99";
								if ($rf["psqreq"] == "Y") $trcolor = "#ff9933";
								if ($rf["grp"] == "Y") $trcolor = "#fdd236";

								$trstyle = "style='background-color:" . $trcolor . ";'";

								//echo $trstyle;
								if ($rf["inseridomanualmente"] != "Y" or ($rf["inseridomanualmente"] == "Y" and $rf["psqkey"] == 'N')) {
							?>
									<tr style="background-color:<? echo $trcolor; ?>;">
										<td>
											<input tabindex="<?=$tabIndex?>" name="_<?= $ln ?>_<?= $rf["act"] ?>__repcol_ordcol" type="text" value="<?= $rf["ordcol"] ?>" style="font-size:10px;width: 18px;">
											<? if ($rf["inseridomanualmente"] == "Y" and $rf["psqkey"] == 'N') { ?><i class="fa fa-times vermelho fade" title="Excluir" onclick="CB.post({objetos:'_del_d__repcol_idrepcol=<?= $rf["idrepcol"] ?>'})"></i>
											<? } ?>
										</td>
										<td>
											<? if ($rf["inseridomanualmente"] == "Y" and $rf["psqkey"] == 'N') { ?>
												<textarea name="_<?= $ln ?>_<?= $rf["act"] ?>__repcol_col" rows="1" cols="3" style="font-size:12px;"><?= $rf["col"] ?></textarea>

											<? } else { ?>
												<?=$rf["col"]?>
												<input type="hidden" name="_<?= $ln ?>_<?= $rf["act"] ?>__repcol_col" value="<?= $rf["col"] ?>">
											<? } ?>
											<input type="hidden" name="_<?= $ln ?>_<?= $rf["act"] ?>__repcol_idrepcol" value="<?= $rf["idrepcol"] ?>">
											<input type="hidden" name="_<?= $ln ?>_<?= $rf["act"] ?>__repcol_idrep" value="<?= $_1_u__rep_idrep ?>">

										</td>
										<td>
										<?=$rf["rotulo"]?>
										</td>
										<td title="Psq Key">
											<select name="_<?= $ln ?>_<?= $rf["act"] ?>__repcol_psqkey">
												<? fillselect("select 'N','N' union select 'Y','Y'", $rf["psqkey"]); ?>
											</select>
										</td>
										<td title="Psq Req">
											<select name="_<?= $ln ?>_<?= $rf["act"] ?>__repcol_psqreq">
												<? fillselect("select 'N','N' union select 'Y','Y'", $rf["psqreq"]); ?>
											</select>
										</td>
										<td title="Vis">
											<select name="_<?= $ln ?>_<?= $rf["act"] ?>__repcol_visres">
												<? fillselect("select 'N','N' union select 'Y','Y'", $rf["visres"]); ?>
											</select>
										</td>
										<td title="Align">
											<select name="_<?= $ln ?>_<?= $rf["act"] ?>__repcol_align">
												<? fillselect("select 'left','L' union select 'center','C' union select 'right','R'", $rf["align"]); ?>
											</select>
										</td>
										<td title="Group">
											<select name="_<?= $ln ?>_<?= $rf["act"] ?>__repcol_grp">
												<? fillselect("select 'N','N' union select 'Y','Y'", $rf["grp"]); ?>
											</select>
										</td>
										<td title="Sum">
											<select name="_<?= $ln ?>_<?= $rf["act"] ?>__repcol_tsum">
												<? fillselect("select 'N','N' union select 'Y','Y'", $rf["tsum"]); ?>
											</select>
										</td>
										<td title="ACSum">
											<select name="_<?= $ln ?>_<?= $rf["act"] ?>__repcol_acsum">
												<? fillselect("select 'N','N' union select 'Y','Y'", $rf["acsum"]); ?>
											</select>
										</td>
										<td title="Avg">
											<select name="_<?= $ln ?>_<?= $rf["act"] ?>__repcol_tavg">
												<? fillselect("select 'N','N' union select 'Y','Y'", $rf["tavg"]); ?>
											</select>
										</td>
										<td title="ACAvg">
											<select name="_<?= $ln ?>_<?= $rf["act"] ?>__repcol_acavg">
												<? fillselect("select 'N','N' union select 'Y','Y'", $rf["acavg"]); ?>
											</select>
										</td>
										<td title="Máscara">
											<select title="N=Nada  C=CPF/CNPJ  M=Moeda  E=CEP " style="width: auto;" name="_<?= $ln ?>_<?= $rf["act"] ?>__repcol_mascara">
												<? fillselect("select 'N','N' union select 'CPF/CJPJ','C' union select 'MOEDA','M'", $rf["mascara"]); ?>
											</select>
										</td>
										<td title="Order(+pos">
											<select name="_<?= $ln ?>_<?= $rf["act"] ?>__repcol_ordtype" style="width:60%;">
												<? fillselect("select '','' union select 'asc','A' union select 'desc','D'", $rf["ordtype"]); ?>
											</select>
											<input name="_<?= $ln ?>_<?= $rf["act"] ?>__repcol_ordseq" type="text" value="<?= $rf["ordseq"] ?>" style="font-size:12px;width: 18px;">
										</td>
										<td title="Hyperlink">
											<input name="_<?= $ln ?>_<?= $rf["act"] ?>__repcol_hyperlink" type="text" value="<?= $rf["hyperlink"] ?>" style="font-size:12px;padding:0px;margin:0px;">
										</td>

									</tr>
							<?
								}
								$tabIndex++;
							} //while
							?>

						</table>
						<table>
							<tr>
								<td>
									<i class="fa fa-plus-circle verde pointer fa-lg" onclick="$('novocampo').removeClass('hidden').find(':input, select').removeAttr('disabled').find(':input:text, select').removeAttr('disabled')" title="Inserir manualmente"></i>
								</td>
								<td>
									<novocampo class="hidden">
										<input type="hidden" name="_manual_i__repcol_idrep" value="<?= $_1_u__rep_idrep ?>" disabled>
										<input type="hidden" name="_manual_i__repcol_psqkey" value="N" disabled>
										<input type="hidden" name="_manual_i__repcol_inseridomanualmente" value="Y" disabled>
										<input type="text" name="_manual_i__repcol_col" disabled>
									</novocampo>
								</td>
							</tr>
						</table>
						<table width="100%" style="margin-top: 30px">
							<tr>
								<td>Cláusulas:</td>
							</tr>
							<tr>
								<td>
									<textarea rows="6" type="text" name="_1_<?= $_acao ?>__rep_compl" style="width: 100%"><?= $_1_u__rep_compl ?></textarea>
								</td>

							</tr>
						</table>
						<table width="100%" style="margin-top: 30px">
							<tr>
								<td>Rodapé:</td>
							</tr>
							<tr>
								<td>
									<textarea rows="6" type="text" name="_1_<?= $_acao ?>__rep_rodape" style="width: 100%"><?= $_1_u__rep_rodape ?></textarea>
								</td>

							</tr>
						</table>
						<table width="100%" style="margin-top: 30px">
							<tr>
								<td>Legenda:</td>
							</tr>
							<tr>
								<td>
									<textarea rows="6" type="text" name="_1_<?= $_acao ?>__rep_descr" style="width: 100%"><?= $_1_u__rep_descr ?></textarea>
								</td>

							</tr>
						</table>

					</div>
				</div>


				<div class="panel panel-default">
					<div class="panel-heading">Filtros do Relatório</div>
					<div class="panel-body">
						<table class="planilha grade compacto" <?= $estado ?>>
							<tr>
								<th style="width:20%">Col</th>
								<th style="text-align: center; width:10%"><i class="fa fa-caret-right" title="Intervalo de valores (between)"></i><i class="fa fa-caret-left" title="Intervalo de valores (between)"></i></th>
								<th style="text-align: center; width:10%"><i class="fa fa-exclamation-circle" title="Preenchimento obrigatário"></i></th>
								<th style="text-align: center; width:10%"><i class="fa fa-percent" title="Like"></i><i class="fa fa-percent" title="Like"></i></th>
								<th style="text-align: center; width:10%"><i class="fa fa fa-at" title="InVal"></i></th>
								<th style="text-align: center; width:10%"><i class="fa fa fa-sign-in" title="Find_In_Set"></i></th>
								<th style="text-align: center; width:30%"><label title="Autocomplete Json">{&nbsp;}</label></th>
								<th></th>
							</tr>
							<?
							$ln = 1;
							//Efetuar reset no ponteiro para novo loop
							mysqli_data_seek($resfld, 0);
							while ($rf = mysql_fetch_array($resfld)) {
								$ln++;
								if ($rf["psqkey"] == "Y") {
							?>
									<tr>
										<td>
											<? if ($rf["inseridomanualmente"] == "Y") { ?>
												<i class="fa fa-info azulclaro fade" title="Filtro inserido manualmente"></i>
											<? } ?>
											<?= $rf["col"] ?>
											<input type="hidden" name="_psqkey<?= $ln ?>_u__repcol_idrepcol" value="<?= $rf["idrepcol"] ?>">

										</td>
										<td>
											<select name="_psqkey<?= $ln ?>_u__repcol_entre" style="width:80%;">
												<? fillselect(array('N' => 'N', 'Y' => 'Y'), $rf["entre"]); ?>
											</select>
										</td>
										<td>
											<select name="_psqkey<?= $ln ?>_u__repcol_psqreq" style="width:80%;" disabled title="Alterar valor na configuração de colunas do Relatório acima.">
												<? fillselect(array('N' => 'N', 'Y' => 'Y'), $rf["psqreq"]); ?>
											</select>
										</td>
										<td>
											<select name="_psqkey<?= $ln ?>_u__repcol_like" style="width:80%;">
												<? fillselect(array('N' => 'N', 'Y' => 'Y'), $rf["like"]); ?>
											</select>
										</td>
										<td>
											<select name="_psqkey<?= $ln ?>_u__repcol_inval" style="width:80%;">
												<? fillselect(array('N' => 'N', 'Y' => 'Y'), $rf["inval"]); ?>
											</select>
										</td>
										<td>
											<select name="_psqkey<?= $ln ?>_u__repcol_findinset" style="width:80%;">
												<? fillselect(array('N' => 'N', 'Y' => 'Y'), $rf["findinset"]); ?>
											</select>
										</td>
										<td>
											<textarea name="_psqkey<?= $ln ?>_u__repcol_json" rows="1" cols="3"><?= $rf["json"] ?></textarea>
										</td>
										<td>
											<i class="fa fa-times vermelho fade" title="Excluir" onclick="CB.post({objetos:'_del_d__repcol_idrepcol=<?= $rf["idrepcol"] ?>'})"></i>
										</td>
									</tr>
							<?
								}
							} //while
							?>
						</table>
						<hr>
						<table>
							<tr>
								<td>
									<i class="fa fa-plus-circle verde pointer fa-lg" onclick="$('novoobj').removeClass('hidden').find(':input, select').removeAttr('disabled').find(':input:text, select').removeAttr('disabled')" title="Inserir manualmente"></i>
								</td>
								<td>
									<novoobj class="hidden">
										<input type="hidden" name="_manual_i__repcol_idrep" value="<?= $_1_u__rep_idrep ?>" disabled>
										<input type="hidden" name="_manual_i__repcol_psqkey" value="Y" disabled>
										<input type="hidden" name="_manual_i__repcol_inseridomanualmente" value="Y" disabled>
										<input type="text" name="_manual_i__repcol_col" disabled>
									</novoobj>
								</td>
							</tr>
						</table>
					</div>
				</div>
			</div>
</div>
<?
		}
?>

<?
		if (!empty($_1_u__rep_idrep)) { // trocar p/ cada tela a tabela e o id da tabela
			$_idModuloParaAssinatura = $_1_u__rep_idrep; // trocar p/ cada tela o id da tabela
			require 'viewAssinaturas.php';
		}
		$tabaud = "_rep"; //pegar a tabela do criado/alterado em antigo
		require 'viewCriadoAlterado.php';
?>
<?
	}
?>
<script>
	
	const idRep =  <?= $idRep ?>;
	const jsonMods = <?= autocompleteModulos($idRep) ?>;

	// $('.selectpicker').selectpicker('render');
	jsonTabCarbonApp = <?= jsonTabelasCarbonApp() ?>;

	CB.on('posPost',()=>{
		CB.oModal.modal('hide')
	})

	function vinculaLps(){
		let str = {

		};

		if($('#associaLpModal').val() !== null && $('#associaLpModal').val() != ''){
			let lps = $('#associaLpModal').val();

			try {
					lps.forEach(function (e,i){
					obj1 = '_xx'+i+"_i__lprep_idlp";
					obj2 = '_xx'+i+"_i__lprep_idrep";
					str[obj1] = e;
					str[obj2] = $("[name$=_idrep]").val() || getUrlParameter("idrep");
					
				})
			} catch (error) {
				console.error(error);
			}
			CB.post({
				objetos:str
				,parcial:true
			});

		}
	}

	function MontaModal() {
		CB.modal({
			titulo:"Lps",
			corpo:$("#modal_lps").html(),
			classe: "noventa"
		})
		$('#cbModalCorpo #associaLP').attr('id','associaLpModal');
		$('#cbModalCorpo .selectpicker').selectpicker('refresh');
		$("#cbModalCorpo").css("min-height","850px")
	}

	//Autocomplete de Tabelas
	$(":input[name=_1_" + CB.acao + "__rep_tab]").autocomplete({
		source: jsonTabCarbonApp,
		delay: 0,
		create: function() {
			$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
				vitem = "<span class='cinzaclaro'>" + item.db + ".</span>" + item.value;
				return $('<li>')
					.append('<a>' + vitem + '</a>')
					.appendTo(ul);
			};
		}
		/*,
			select: function(event, ui){
				mostraDetalhesCliente();
				preencheDropNucleos(ui.item.value);
			},
			create: function( event, ui ) {
				mostraDetalhesCliente();
			}*/
	});


	//Autocomplete de Modulos

	$("#associarRelatorio").autocomplete({
		source: jsonMods,
		delay: 0,
		create: function() {
			$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
				vitem = item.modulolist;
				return $('<li>')
					.append('<a>' + vitem + '</a>')
					.appendTo(ul);
			};
		},
		select: function(i, e) {
			CB.post({
				objetos: {
					'_m_i__modulorep_modulo': e.item.modulo,
					'_m_i__modulorep_idrep': idRep,
					'_m_i__modulorep_ord': "999"
				},
				parcial: true
			});
		}
	});

	//Monta um seletor de à­cones de acordo com parte do nome dos arquivos CSS informados
	var styleSheetList = document.styleSheets;
	var hIcones = "";
	var separador = "";
	$.each(styleSheetList, function(i, o) {
		var prefClasse;
		//Procura por Css por parte do nome do arquivo
		if (/laudofonts|fontawesome/.test(o.href)) {
			//Adicionar o prefixo padrao para utilizacao da fonte css
			if (/laudofonts/.test(o.href)) {
				prefClasse = "laudoicon";
			} else if (/fontawesome/.test(o.href)) {
				prefClasse = "fa";
			}
			hIcones += separador;
			//Loop em todas as classes css
			$.each(o.rules, function(ir, or) {
				if (or.type == "1") {
					//Extrai a string referente ao seletor Css do icone
					if (/::/.test(or.selectorText)) {
						var strIco = or.selectorText.match(/.*?(?=::|$)/)[0].replace(/^\./, '');
						hIcones += `<i class="${prefClasse + " " + strIco} fa-2x hoververmelho" style="margin:3px;" cssicone="${prefClasse + " " +strIco}" title="${prefClasse + " " +strIco}"" onclick="alteraIcone(this)"></i>`;
					}
				}
			});

			separador = "<hr>";
		}
	})

	$("#seletoricones").webuiPopover({
		title: 'Selecionar à­cone para o Mádulo',
		content: hIcones
	});

	function alteraIcone(inObj) {
		$("[name*=_cssicone]").val($(inObj).attr("cssicone"));

	}

	function toggle(value) {
		CB.post({
			objetos: {
				'_x_u__rep_idrep': $("input[name='_1_u__rep_idrep']").val(),
				'_x_u__rep_dashboard': value
			},
			parcial: true
		});
	}

	function removerEixosIguais(vthis, identificador) {
		if (vthis.value != '' && vthis.value != 'G' && vthis.value != 'Y') {
			$("#tablegraph .selecteixo").each((i, o) => {
				if (o.value == vthis.value && $(o).attr('identificador') != identificador) {
					o.value = '';
				}
			});
		}
	}

	function statusUpd(vthis, id) {
		debugger
		let v = vthis.value
		CB.post({
			objetos: {
				'_x_u__rep_idrep': id,
				'_x_u__rep_status': v
			},
			parcial: true
		});
	}


	function relacionaLpRepUBT(vthis){

		inIdLpRep=$(vthis).attr("idlprep");
		inflgunidade=$(vthis).attr("flgunidade");


		if(inIdLpRep==""){
			alert("Falha: Relatório não associado!");
		return;
		}else{

			if(inflgunidade=="Y"){
				sacao="u";
				Nbtn = "cinza";
				Rbtn = "azul";
				newflgunidade = "N"				

				var strPost = "_ajax_"+sacao+"__lprep_idlprep="+inIdLpRep
							+"&_ajax_"+sacao+"__lprep_flgunidade=N";

			}else{
				sacao = "u";
				Nbtn = "azul";
				Rbtn = "cinza";
				newflgunidade = "Y"
				
				var strPost = "_ajax_"+sacao+"__lprep_idlprep="+inIdLpRep
							+"&_ajax_"+sacao+"__lprep_flgunidade=Y";
			}





			CB.post({
				objetos: strPost,
				parcial:true,
				refresh:false,
				posPost: function(data, textStatus, jqXHR){
					
					if(CB.lastInsertId>0){
						$(vthis).attr("idlprep", CB.lastInsertId);
					}
					
					$(vthis).removeClass(Rbtn).addClass(Nbtn);
					$(vthis).attr('flgidpessoa', newflgunidade);
				}
			});
		}
	}



	function relacionaLpRepIdPessoa(vthis){

		inIdLpRep=$(vthis).attr("idlprep");
		inflgidpessoa=$(vthis).attr("flgidpessoa");


		if(inIdLpRep==""){
		alert("Falha: Relatório não associado!");
		return;
		} else {

			if(inflgidpessoa=="Y"){
				sacao = "u";
				Nbtn = "cinza";
				Rbtn = "azul";
				newinflgidpessoa = "N"

				var strPost = "_ajax_"+sacao+"__lprep_idlprep="+inIdLpRep
							+"&_ajax_"+sacao+"__lprep_flgidpessoa=N";

			}else{
				sacao = "u";
				Nbtn = "azul";
				Rbtn = "cinza";
				newinflgidpessoa = "Y"
				
				var strPost = "_ajax_"+sacao+"__lprep_idlprep="+inIdLpRep
							+"&_ajax_"+sacao+"__lprep_flgidpessoa=Y";
			}


			CB.post({
				objetos: strPost,
				parcial:true,
				refresh:false,
				posPost: function(data, textStatus, jqXHR){
					
					
					if(CB.lastInsertId>0){
						$(vthis).attr("idlprep", CB.lastInsertId);
					}
					
					$(vthis).removeClass(Rbtn).addClass(Nbtn);
					$(vthis).attr('flgidpessoa', newinflgidpessoa);

				}
			});
		}
	}
	function relacionaLpRepContaItem(vthis){

		inIdLpRep=$(vthis).attr("idlprep");
		inflgcontaitem=$(vthis).attr("flgcontaitem");


		if(inIdLpRep==""){
		alert("Falha: Relatório não associado!");
		return;
		} else {

			if(inflgcontaitem=="Y"){
				sacao = "u";
				Nbtn = "cinza";
				Rbtn = "azul";
				newinflgcontaitem = "N"

				var strPost = "_ajax_"+sacao+"__lprep_idlprep="+inIdLpRep
							+"&_ajax_"+sacao+"__lprep_flgcontaitem=N";

			}else{
				sacao = "u";
				Nbtn = "azul";
				Rbtn = "cinza";
				newinflgcontaitem = "Y"
				
				var strPost = "_ajax_"+sacao+"__lprep_idlprep="+inIdLpRep
							+"&_ajax_"+sacao+"__lprep_flgcontaitem=Y";
			}


			CB.post({
				objetos: strPost,
				parcial:true,
				refresh:false,
				posPost: function(data, textStatus, jqXHR){
					
					
					if(CB.lastInsertId>0){
						$(vthis).attr("idlprep", CB.lastInsertId);
					}
					
					$(vthis).removeClass(Rbtn).addClass(Nbtn);
					$(vthis).attr('flgcontaitem', newinflgidpessoa);

				}
			});
		}
	}



	//# sourceURL=<?= $_SERVER["SCRIPT_NAME"] ?>_rodape

</script>
