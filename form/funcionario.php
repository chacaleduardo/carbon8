<?
require_once("../inc/php/validaacesso.php");

if ($_POST) require_once("../inc/php/cbpost.php");

//Parâmetros mandatórios para o carbon
$pagvaltabela = "pessoa";
$pagvalmodulo = $_GET['_modulo'];
$pagvalcampos = array(
	"idpessoa" => "pk"
);

//Select que inicializa as variáveis que preenchem os campos da tela em caso de update
$pagsql = "select * from pessoa where   idpessoa = '#pkid'";
//die($pagsql);
//Validacao do GET e criacao das variáveis 'variáveis' para a página
include_once("../inc/php/controlevariaveisgetpost.php");
$i = 99;

if (!empty($_GET['_idempresa'])) {
	$_stridempresa = '&_idempresa=' . $_GET['_idempresa'];
} else {
	$_stridempresa = "";
}

if($_GET['_acao'] == 'i')
{
	$_1_u_pessoa_status = 'ABERTO';
}


//Lista as pessoas que foram inseridas no Evento
function getListaComentariosEvento($idpessoa)
{
	$sqlc = "SELECT IF(p.nomecurto is null, p.nome, p.nomecurto) AS nomecurto,
						e.* ,
						if(ev.idpessoa = p.idpessoa, 'Y', 'N') as dono
					FROM modulocom e JOIN pessoa p ON(p.usuario=e.criadopor)
					JOIN pessoa ev ON ev.idpessoa = e.idmodulo AND e.modulo = 'funcionario'
					WHERE e.idmodulo=" . $idpessoa . "
					AND e.status = 'ATIVO' 
					ORDER BY e.criadoem desc";
	$resc = d::b()->query($sqlc) or die("[model-evento] - Erro ao buscar getListaComentariosEvento." . $sqlc);
	return $resc;
}

function getJimmsgconf()
{
	global $JSON;

	$s = "select idimmsgconf,titulo from immsgconf c where status='ATIVO' and tipo not in('E','EP','ET')  order by titulo";

	$rts = d::b()->query($s) or die("getJSetorvinc: " . mysql_error(d::b()));

	$arrtmp = array();
	$i = 0;
	while ($r = mysqli_fetch_assoc($rts)) {
		$arrtmp[$i]["value"] = $r["idimmsgconf"];
		$arrtmp[$i]["label"] = $r["titulo"];
		$i++;
	}
	return $JSON->encode($arrtmp);
}

// GVT - 19/05/2020 - Função retorna a lista de emails virtuais associados ao funcionário.
function listaEmailVirtual()
{
	global $_1_u_pessoa_idpessoa;
	$s = "SELECT * 
			FROM emailvirtualconf 
			WHERE 1 " . getidempresa('idempresa', 'emailvirtualconf') . " 
			AND tipoemailvirtual='PESSOA'
			AND idpessoaemail = $_1_u_pessoa_idpessoa
			AND status = 'ATIVO'";

	$rts = d::b()->query($s) or die("listaEmailVirtual: SQL" . $s . " " . mysql_error(d::b()));

	echo "<table class='table-hover'><tbody>";
	while ($r = mysqli_fetch_assoc($rts)) {
		echo "<tr><td>" . $r["email_original"] . "</td>
			<td><i class='fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable' onclick='excluiremailvirtual(" . $r["idemailvirtualconf"] . ")'></i></td></tr>";
	}
	echo "</tbody></table>";
}

if (!empty($_1_u_pessoa_idpessoa)) {

	function getContato()
	{
		global $_1_u_pessoa_idpessoa;

		/*
		$sqlplanteis1="select ifnull(group_concat(idplantel),0) as planteis
			from plantelobjeto
			where tipoobjeto = 'pessoa'
			and idobjeto = ".$_1_u_pessoa_idpessoa;
	*/

		$sqlplanteis1 = "select ifnull(group_concat(u.idplantel),0) as planteis from (
						select p.idplantel
						from pessoaobjeto ps
							,sgarea s
							,lpobjeto o
							,carbonnovo._lp l
							join plantelobjeto p on(  p.idobjeto = l.idlp and p.tipoobjeto = 'lp')
						where ps.idpessoa=" . $_1_u_pessoa_idpessoa . "
						-- and l.idempresa=8		
						and s.idsgarea=ps.idobjeto
						and ps.tipoobjeto = 'sgarea'
						and s.status='ATIVO'
						and o.idobjeto = s.idsgarea
						and o.tipoobjeto = 'sgarea'
						and l.status='ATIVO'
						and l.idlp=o.idlp
						UNION
						select p.idplantel
						from pessoaobjeto ps
							,sgdepartamento s
							,lpobjeto o
							,carbonnovo._lp l
							join plantelobjeto p on(  p.idobjeto = l.idlp and p.tipoobjeto = 'lp')
						where ps.idpessoa=" . $_1_u_pessoa_idpessoa . "
						-- and l.idempresa=8				
						and s.idsgdepartamento=ps.idobjeto
						and ps.tipoobjeto = 'sgdepartamento'
						and s.status='ATIVO'
						and o.idobjeto = s.idsgdepartamento
						and o.tipoobjeto = 'sgdepartamento'
						and l.status='ATIVO'
						and l.idlp=o.idlp
						UNION
						select p.idplantel
						from pessoaobjeto ps
							,sgsetor s
							,lpobjeto o
							,carbonnovo._lp l
							join plantelobjeto p on(  p.idobjeto = l.idlp and p.tipoobjeto = 'lp')
						where ps.idpessoa=" . $_1_u_pessoa_idpessoa . "
						-- and l.idempresa=8					
						and s.idsgsetor=ps.idobjeto
						and ps.tipoobjeto = 'sgsetor'
						and s.status='ATIVO'
						and o.idobjeto = s.idsgsetor
						and o.tipoobjeto = 'sgsetor'
						and l.status='ATIVO'
						and l.idlp=o.idlp
						UNION
						SELECT p.idplantel
						FROM 
							lpobjeto ps,carbonnovo._lp l
							join plantelobjeto p on(  p.idobjeto = l.idlp and p.tipoobjeto = 'lp')
						where l.idlp=ps.idlp
						and l.status='ATIVO'
						-- and l.idempresa=8
						and ps.tipoobjeto ='pessoa' 
						and ps.idobjeto = " . $_1_u_pessoa_idpessoa . "
						UNION
						select p.idplantel 
						from carbonnovo._lpobjeto o
							join carbonnovo._lp l on (o.idlp = l.idlp 
															and o.tipoobjeto = 'empresa' 
															-- and o.idobjeto = 1 
															and l.status = 'ATIVO') 
							join lpobjeto ps on (ps.idlp = l.idlp and ps.tipoobjeto = 'pessoa' and ps.idobjeto = " . $_1_u_pessoa_idpessoa . ")
							join plantelobjeto p on(  p.idobjeto = l.idlp and p.tipoobjeto = 'lp')
						UNION
						select p.idplantel 
						from carbonnovo._lpobjeto o
							join carbonnovo._lp l on (o.idlp = l.idlp 
														and o.tipoobjeto = 'empresa' 
														-- and o.idobjeto = 1 
														and l.status = 'ATIVO') 
							join lpobjeto ps on (ps.idlp = l.idlp and ps.tipoobjeto = 'sgsetor')
							join pessoaobjeto po on (po.tipoobjeto = 'sgsetor' and po.idobjeto = ps.idobjeto and po.idpessoa = " . $_1_u_pessoa_idpessoa . ")
							join plantelobjeto p on(  p.idobjeto = l.idlp and p.tipoobjeto = 'lp')
						UNION
						select p.idplantel 
						from carbonnovo._lpobjeto o
							join carbonnovo._lp l on (o.idlp = l.idlp 
														and o.tipoobjeto = 'empresa' 
														-- and o.idobjeto = 1 
														and l.status = 'ATIVO') 
							join lpobjeto ps on (ps.idlp = l.idlp and ps.tipoobjeto = 'sgdepartamento')
							join pessoaobjeto po on (po.tipoobjeto = 'sgdepartamento' and po.idobjeto = ps.idobjeto and po.idpessoa = " . $_1_u_pessoa_idpessoa . ")
							join plantelobjeto p on(  p.idobjeto = l.idlp and p.tipoobjeto = 'lp')
						UNION 
						select p.idplantel 
						from carbonnovo._lpobjeto o
							join carbonnovo._lp l on (o.idlp = l.idlp 
														and o.tipoobjeto = 'empresa' 
														-- and o.idobjeto = 1 
														and l.status = 'ATIVO') 
							join lpobjeto ps on (ps.idlp = l.idlp and ps.tipoobjeto = 'sgarea')
							join pessoaobjeto po on (po.tipoobjeto = 'sgarea' and po.idobjeto = ps.idobjeto and po.idpessoa = " . $_1_u_pessoa_idpessoa . ")
							join plantelobjeto p on(  p.idobjeto = l.idlp and p.tipoobjeto = 'lp')
							) as u";

		$resplanteis1 = d::b()->query($sqlplanteis1) or die("getClientesx: Erro: " . mysql_error(d::b()) . "\n" . $sqlplanteis1);
		$rplanteis1 = mysqli_fetch_assoc($resplanteis1);

		if ($rplanteis1["planteis"] == 0) {
			$and1 = "";
		} else {
			$and1 = "and po.idplantel in (" . $rplanteis1["planteis"] . ")";
		}

		$sql = "select p.idpessoa,p.nome 
			from pessoa p
			where 1
			--	not exists(select 1 from pessoacontato c,pessoa pp
			-- where c.idpessoa = p.idpessoa and pp.idtipopessoa in (1,12) and c.idcontato =pp.idpessoa)
			and p.idtipopessoa = 2
			and exists (select 1 from plantelobjeto po where po.idobjeto = p.idpessoa and po.tipoobjeto = 'pessoa' " . $and1 . ")
			" . getidempresa('p.idempresa', 'pessoa') . "
			and (p.webmailemail <> '' or p.email <> '')
			and p.status in ('ATIVO','PENDENTE')
			order by p.nome";


		//die($sql);
		$res = d::b()->query($sql) or die("getClientes: Erro: " . mysql_error(d::b()) . "\n" . $sql);

		$arrret = array();
		while ($r = mysqli_fetch_assoc($res)) {
			//monta 2 estruturas json para finalidades (loops) diferentes
			$arrret[$r["idpessoa"]]["nome"] = $r["nome"];
		}
		return $arrret;
	}


	function getContatof()
	{
		global $_1_u_pessoa_idpessoa;



		$sql = "select p.idpessoa,p.nome 
			from pessoa p
			where p.idtipopessoa = 5
				" . getidempresa('p.idempresa', 'pessoa') . "			
			and p.status in ('ATIVO','PENDENTE')
			order by p.nome";


		//die($sql);
		$res = d::b()->query($sql) or die("getContatof: Erro: " . mysql_error(d::b()) . "\n" . $sql);

		$arrret = array();
		while ($r = mysqli_fetch_assoc($res)) {
			//monta 2 estruturas json para finalidades (loops) diferentes
			$arrret[$r["idpessoa"]]["nome"] = $r["nome"];
		}
		return $arrret;
	}

	function getAssinaturasFunc()
	{
		global $JSON, $_1_u_pessoa_webmailemail, $_1_u_pessoa_idpessoa;

		$sa = "SELECT 
					w.email,
    				group_concat(wp.idwebmailassinaturaobjeto) as idwebmailassinaturaobjetos
				FROM
					webmailassinatura w
						JOIN
					webmailassinaturaobjeto wp ON (w.idwebmailassinatura = wp.idwebmailassinatura)
						JOIN
					pessoa p ON (w.email = p.webmailemail)
				WHERE
					w.status = 'ATIVO'
						AND TRIM(w.email) != TRIM('" . $_1_u_pessoa_webmailemail . "')
						AND w.email <> ''
						AND p.idpessoa = wp.idobjeto AND wp.tipoobjeto = 'pessoa'
						AND NOT EXISTS (
							SELECT 1 FROM webmailassinaturaobjeto wp1 WHERE wp1.idobjeto = " . $_1_u_pessoa_idpessoa . " AND wp1.tipoobjeto = 'pessoa' AND wp1.idwebmailassinatura = wp.idwebmailassinatura
						)
						AND wp.tipo = 'PESSOA'
				GROUP BY w.idwebmailassinatura";

		$rsa = d::b()->query($sa) or die("Erro ao consultar assinaturas de e-mail de outros funcionários sql= " . $sa);
		if (mysqli_num_rows($rsa) == 0) {
			$arrtmp = 0;
		} else {
			$arrtmp = array();
			$i = 0;
			while ($ra = mysqli_fetch_assoc($rsa)) {
				$arrtmp[$i]["email"] 						= $ra["email"];
				$arrtmp[$i]["idwebmailassinaturaobjetos"] 	= $ra["idwebmailassinaturaobjetos"];
				$i++;
			}
			$arrtmp = $JSON->encode($arrtmp);
		}

		return $arrtmp;
	}

	$arrCont = getContato();
	//print_r($arrCont); die;
	$jCont = $JSON->encode($arrCont);

	$arrContf = getContatof();
	//print_r($arrCont); die;
	$jContf = $JSON->encode($arrContf);
}
?>
<style>
	.opacity {
		opacity: 0.3;
	}

	.desabilitado {
		background-color: #ece5e5 !important;
	}

	#avatarFoto {
		margin: 5px;
		border-radius: 5%;
		cursor: pointer;
		height: 180px;
		width: auto;
	}

	.panel {
		margin: 0 !important;
	}

	.checkbox-dia {
		display: inline-flex !important;
		justify-content: space-between;
		width: 35px;
		line-height: initial;
		padding: 8px 0px;
		margin: 2px;
	}

	.checkbox-dia>input[type=checkbox] {
		margin: 0;
	}

	#enderecoinfo > tbody
	{
		display: table;
   		width: 100%;
	}
</style>
<div class='row'>
	<div class="col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading ">
				<table>
					<tr>
						<td>Id.</td>
						<td><label class="idbox"><?= $_1_u_pessoa_idpessoa ?></label></td>
						<td>Nome</td>
						<td><input class="size25" name="_1_<?= $_acao ?>_pessoa_nome" type="text" class="" value="<?= $_1_u_pessoa_nome ?>" vnulo></td>
						<td>Nome Curto</td>
						<td>
							<input id="idpessoa" name="_1_<?= $_acao ?>_pessoa_idpessoa" type="hidden" value="<?= $_1_u_pessoa_idpessoa ?>" readonly>
							<input type="hidden" name="_1_<?= $_acao ?>_pessoa_idtipopessoa" value="1">
							<input class="size15" name="_1_<?= $_acao ?>_pessoa_nomecurto" type="text" class="" value="<?= $_1_u_pessoa_nomecurto ?>" vnulo>
							<? //LTM 01-09-20: Seta para os novos cadastros o jsonpreferencias para que pois quando faz alteração (update) em alguns módulos, não estava salvando a preferência do usuário pois o campo está NULL 
							?>
							<? if ($_acao == 'i') { ?>
								<input name="_1_<?= $_acao ?>_pessoa_jsonpreferencias" type="hidden" value="{}" readonly='readonly'>
							<? } ?>
						</td>
						<td title="Número Informado Pela Contabilidade Obrigatório para Transferência de Arquivos">Contrato Emprego</td>
						<td><input class="size7" name="_1_<?= $_acao ?>_pessoa_contratoemp" type="text" class="" value="<?= $_1_u_pessoa_contratoemp ?>"></td>
						<td class="lbr" align="right">Status</td>
						<td>
							<td>
								<? $rotulo = getStatusFluxo($pagvaltabela, 'idpessoa', $_1_u_pessoa_idpessoa)?>                                              
								<label class="alert-warning" title="<?=$_1_u_pessoa_status?>" id="statusButton"><?=mb_strtoupper($rotulo['rotulo'],'UTF-8')?> </label>
								<input name="_1_<?=$_acao?>_pessoa_status" type="hidden" value="<?=$_1_u_pessoa_status?>">
							</td> 
						</td>
						<td class="lbr" align="right"><a class="fa fa-print pointer hoverazul" title="IMPRIMIR" onclick="janelamodal('./report/relfuncionario.php?acao=u&idpessoa=<?= $_1_u_pessoa_idpessoa ?>')">
								<? if (!empty($_1_u_pessoa_idpessoa)) { ?>
						<td>
							<?if (array_key_exists("duplicarusuario", getModsUsr("MODULOS")) == 1){?>
							<button type="button" class="btn btn-danger btn-xs" style="margin-left: 100%;" onclick="modalTransferir(<?=cb::idempresa()?>,<?=$_1_u_pessoa_idpessoa?>)" title="Transferir usuário">
								<i class="fa fa-circle"></i>Transferir usuário
							</button>
							<?}?>
							<!-- <button type="button" class="btn btn-primary btn-xs" style="margin-left: 100%;" onclick="sincronizarbiometria(<?= $_1_u_pessoa_idpessoa ?>)" title="SINCRONIZAR BIOMETRIA">
								<i class="fa fa-circle"></i>Sincronizar Biometria
							</button> -->
						</td>
					<? } ?>
					</tr>
				</table>
			</div>
		</div>
	</div>
</div>
<?
if (!empty($_1_u_pessoa_idpessoa)) {
?>
<div class='row'>
	<div class="col-md-12">
		<div class="panel panel-default">
			<div class="panel-body">
				<div class="row">
					<div class="col-md-4" style="z-index: 1">
						<table>
							<? if ($_1_u_pessoa_contrato == 'PJ' or $_1_u_pessoa_contrato == 'TERC') { ?>
								<!--tr>
			<td>Razão Social</td>
			<td>
				<input name="_1_<?= $_acao ?>_pessoa_razaosocial" type="text" size="28" value="<?= $_1_u_pessoa_razaosocial ?>" class="size25" vnulo>
			</td>
		</tr-->




								<?
								$sql = "select 
            c.idpessoacontato
            ,p.idpessoa      
            ,p.nome
             from pessoa p,pessoacontato c
            where p.idpessoa = c.idpessoa
			and p.idtipopessoa=5			
            and c.idcontato = " . $_1_u_pessoa_idpessoa . " order by nome";
								$res = d::b()->query($sql) or die("A Consulta do fornecedor falhou :" . mysql_error() . "<br>Sql:" . $sql);
								$rownum1 = mysqli_num_rows($res);

								if ($rownum1 > 0) {
									while ($row = mysqli_fetch_assoc($res)) {

								?>
										<tr>
											<td>Razão Social:</td>
											<td nowrap><a title="Cliente" onclick="janelamodal('?_modulo=pessoa&_acao=u&idpessoa=<?= $row["idpessoa"] ?>')"><?= $row["nome"] ?></a>

												<a class="fa fa-trash  cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="excluicontato(<?= $row["idpessoacontato"] ?>)" title="Excluir"></a>
											</td>
										</tr>
									<?
									} //while($row = mysqli_fetch_array($res)){
								} else { //if($rownum1>0){
									?>

									<tr>
										<td>Razão Social:</td>
										<td colspan="3">
											<input type="text" name="pessoacontatof" cbvalue="pessoacontatof" value="" style="width: 30em;">
										</td>
									</tr>
							<?
								}
							}
							?>
							<tr>
								<td>Cargo</td>
								<td class="nowrap">
									<select name="_1_<?= $_acao ?>_pessoa_idsgcargo" class="size25">
										<?
										fillselect("select idsgcargo,TRIM(concat(cargo, ' ',ifnull(nivel,''))) as cargo  from sgcargo where status = 'ATIVO' ".getidempresa('idempresa', 'funcionario')." order by cargo", $_1_u_pessoa_idsgcargo);
										?>
									</select>
									<? if (!empty($_1_u_pessoa_idsgcargo)) { ?>
										<a class="fa fa-bars cinzaclaro hoverazul pointer" onclick="janelamodal('?_modulo=sgcargo&_acao=u&idsgcargo=<?= $_1_u_pessoa_idsgcargo ?>');"></a>
									<? }
									$_idempresa = ($_GET["_idempresa"]) ? "&_idempresa=" . $_GET["_idempresa"] : '';
									?>
									&nbsp;&nbsp;
									&nbsp;
									<a class="fa fa-plus-circle verde pointer hoverazul" title="Novo Cargo" onclick="janelamodal('?_modulo=sgcargo&_acao=i<?= $_idempresa ?>&idpessoa=<?= $_1_u_pessoa_idpessoa ?>')"></a>
								&nbsp;&nbsp;&nbsp;
									<a class="fa fa-search azul pointer hoverazul" title="Histórico" onClick="cargoPessoa();"></a></td>

							</tr>
							<tr>
								<td>Email</td>
								<td><input name="_1_<?= $_acao ?>_pessoa_email" type="text" size="28" value="<?= $_1_u_pessoa_email ?>" vemail="" class="size25"></td>
							</tr>
							<tr>
								<td>Tel. 1</td>
								<td class="nowrap">
									<input name="_1_<?= $_acao ?>_pessoa_dddfixo" type="text" size="1" value="<?= $_1_u_pessoa_dddfixo ?>" vnumero="" maxlength="2" class="size3">
									<input name="_1_<?= $_acao ?>_pessoa_telfixo" type="text" size="8" value="<?= $_1_u_pessoa_telfixo ?>" vnumero="" maxlength="9" class="size10">
								</td>
							</tr>
							<tr>
								<td>Tel. 2</td>
								<td class="nowrap">
									<input name="_1_<?= $_acao ?>_pessoa_dddcom" type="text" size="1" value="<?= $_1_u_pessoa_dddcom ?>" vnumero="" maxlength="2" class="size3">
									<input name="_1_<?= $_acao ?>_pessoa_telcom" type="text" size="8" value="<?= $_1_u_pessoa_telcom ?>" vnumero="" maxlength="9" class="size10">
								</td>
							</tr>
							<tr>
								<td>Tel. Cel.</td>
								<td class="nowrap">
									<input name="_1_<?= $_acao ?>_pessoa_dddcel" type="text" size="1" value="<?= $_1_u_pessoa_dddcel ?>" vnumero="" maxlength="2" class="size3">
									<input name="_1_<?= $_acao ?>_pessoa_telcel" type="text" size="8" value="<?= $_1_u_pessoa_telcel ?>" vnumero="" maxlength="9" class="size10">
								</td>
							</tr>
							<tr>
								<td>Ramal</td>
								<td><input name="_1_<?= $_acao ?>_pessoa_ramalfixo" type="text" size="8" value="<?= $_1_u_pessoa_ramalfixo ?>" maxlength="9" class="size5"></td>
							</tr>
							<tr>
								<td>Motivo Admissão</td>
								<td>
									<select name="_1_<?= $_acao ?>_pessoa_motivoadmissao" class="size25">
										<option value=""></option>
										<? fillselect("SELECT 'Aumento de Quadro', 'Aumento de Quadro' UNION SELECT 'Substituição', 'Substituição'", $_1_u_pessoa_motivoadmissao) ?>
									</select>
								</td>
							</tr>
							<tr>
								<td>Motivo Demissão</td>
								<td>
									<select name="_1_<?= $_acao ?>_pessoa_motivodemissao" class="size25">
										<option value=""></option>
										<? fillselect(
											"SELECT 'Pedido de demissão', 'Pedido de demissão' UNION 
											SELECT 'Justa causa', 'Justa causa' UNION
											SELECT 'Justa causa', 'Justa causa' UNION
											SELECT 'Demitido', 'Demitido' UNION
											SELECT 'Término de contrato', 'Término de contrato' UNION
											SELECT 'Falecimento', 'Falecimento' UNION
											SELECT 'Abandono de emprego', 'Abandono de emprego' UNION
											SELECT 'Acordo trabalhista', 'Acordo trabalhista' UNION
											SELECT 'Aposentadoria', 'Aposentadoria'",
										 $_1_u_pessoa_motivodemissao) ?>
									</select>
								</td>
							</tr>
							<tr>
								<td>Observação</td>
								<td>
									<textarea name="_1_<?= $_acao ?>_pessoa_observacaodemissao" cols="30" rows="5" class="size25"><?= $_1_u_pessoa_observacaodemissao ?></textarea>
								</td>
							</tr>
						</table>
					</div>

					<div class="col-md-6">
						<table>

							<tr>
								<td class="lbr" align="right">Nome Pai</td>
								<td colspan="3"><input name="_1_<?= $_acao ?>_pessoa_nomepai" type="text" size="28" value="<?= $_1_u_pessoa_nomepai ?>"></td>
							</tr>

							<tr>
								<td class="lbr" align="right">Nome Mãe</td>
								<td colspan="3"><input name="_1_<?= $_acao ?>_pessoa_nomemae" type="text" size="28" value="<?= $_1_u_pessoa_nomemae ?>"></td>
							</tr>

							<tr>
								<td class="lbr" align="right">Nascimento</td>
								<td><input name="_1_<?= $_acao ?>_pessoa_nasc" class="calendario" type="text" size="8" value="<?= $_1_u_pessoa_nasc ?>" vdata></td>
								<td align="right" class="lbr">Cidade Nasc.</td>
								<td ><input name="_1_<?= $_acao ?>_pessoa_cidnasc" type="text" size="25" value="<?= $_1_u_pessoa_cidnasc ?>"></td>
							</tr>

							<tr>
								<td nowrap="nowrap" class="lbr">Contratação</td>
								<td class="nowrap">
									<input class="size8" name="_1_<?= $_acao ?>_pessoa_contratacao" class="calendario" type="text" value="<?= $_1_u_pessoa_contratacao ?>" vdata>
									<a title="Formulário de Admissão" class="fa fa-print cinza pointer hoverazul" onclick="janelamodal('form/admissao.php?_acao=u&idpessoa=<?= $_1_u_pessoa_idpessoa ?>')"></a>
								</td>
								<td align="right" class="lbr">Contrato Exp.</td>
								<td  class="nowrap"><input name="_1_<?= $_acao ?>_pessoa_ctrexp" type="number" size="25" value="<?= $_1_u_pessoa_ctrexp ?>"></td>
								<td  colspan="4" class="nowrap"><input name="_1_<?= $_acao ?>_pessoa_ctrexp2" type="number" size="25" value="<?= $_1_u_pessoa_ctrexp2 ?>"> Dias</td>
							</tr>

							<tr>
								<td class="lbr" align="right">Demissão </td>
								<td>
									<?
									if ($_1_u_pessoa_status == "ATIVO") {
										$_1_u_pessoa_demissao = "";
									}
									?>
									<input name="_1_<?= $_acao ?>_pessoa_demissao" class="calendario" type="text" size="8" value="<?= $_1_u_pessoa_demissao ?>" >
								</td>
								<td align="right" nowrap="nowrap" class="lbr">Cipa</td>
								<td><input name="_1_<?= $_acao ?>_pessoa_cipainicio" class="calendario" type="text" size="8" value="<?= $_1_u_pessoa_cipainicio ?>" ></td>
								<td colspan="4" ><input name="_1_<?= $_acao ?>_pessoa_cipafim" class="calendario" type="text" size="8" value="<?= $_1_u_pessoa_cipafim ?>" ></td>
							</tr>

							<tr>
								<td class="lbr" align="right">Alist. Militar</td>
								<td ><input name="_1_<?= $_acao ?>_pessoa_alistmil" type="text" size="25" value="<?= $_1_u_pessoa_alistmil ?>"></td>
								<td class="lbr" align="right">PCD</td>
								<td>
									<select name="_1_<?= $_acao ?>_pessoa_pcd" class="size8" vnulo>
										<option value=""></option>
										<? fillselect("SELECT 'Y', 'Sim' UNION SELECT 'N', 'Não'", $_1_u_pessoa_pcd) ?>
									</select>
							</tr>

							<tr>
								<td class="lbr" align="right">CNH</td>
								<td><input name="_1_<?= $_acao ?>_pessoa_cnh" type="text" size="15" value="<?= $_1_u_pessoa_cnh ?>"></td>
								<td class="lbr" align="right">Emissão</td>
								<td><input name="_1_<?= $_acao ?>_pessoa_emissaocnh" class="calendario" type="text" size="8" value="<?= $_1_u_pessoa_emissaocnh ?>" ></td>
								<td class="lbr" align="right">Vencimento</td>
								<td><input name="_1_<?= $_acao ?>_pessoa_vencimentocnh" class="calendario" type="text" size="8" value="<?= $_1_u_pessoa_vencimentocnh ?>" ></td>
							</tr>

							<tr>
								<td class="lbr" align="right">RG</td>
								<td><input name="_1_<?= $_acao ?>_pessoa_rg" type="text" size="15" value="<?= $_1_u_pessoa_rg ?>"></td>
								<td class="lbr" align="right">Orgão Exp.</td>
								<td><input name="_1_<?= $_acao ?>_pessoa_rgorgexpedidor" type="text" size="10" value="<?= $_1_u_pessoa_rgorgexpedidor ?>" class="size8"></td>
								<td class="lbr" align="right">Emissão</td>
								<td><input name="_1_<?= $_acao ?>_pessoa_emissaorg" class="calendario" type="text" size="18" value="<?= $_1_u_pessoa_emissaorg ?>" ></td>
							</tr>

							<tr> 
								<td class="lebr" align="right">CPF</td>
								<td><input id="validacpfcnjp" name="_1_<?= $_acao ?>_pessoa_cpfcnpj" type="text" size="15" value="<?= $_1_u_pessoa_cpfcnpj ?>" vcpfcnpj></td>
								<td class="lbr" align="right">Título Eleit.</td>
								<td><input name="_1_<?= $_acao ?>_pessoa_titeleitor" type="text" size="25" value="<?= $_1_u_pessoa_titeleitor ?>"></td>
								<td class="lbr" align="right">Zona</td>
								<td><input name="_1_<?= $_acao ?>_pessoa_zona" type="text" size="5" value="<?= $_1_u_pessoa_zona ?>"></td>
							</tr>

							<tr>
								<td class="lbr" align="right">CTPS</td>
								<td><input name="_1_<?= $_acao ?>_pessoa_ctps" type="text" size="15" value="<?= $_1_u_pessoa_ctps ?>"></td>
								<td class="lbr" align="right">Série</td>
								<td><input name="_1_<?= $_acao ?>_pessoa_ctpsserie" type="text" size="5" value="<?= $_1_u_pessoa_ctpsserie ?>"></td>
							</tr>

							<tr>
								<td class="lbr" align="right">PIS</td>
								<td>
									<? if (!empty($_1_u_pessoa_pis)) {
										if (strlen($_1_u_pessoa_pis) >= 12) {
											$pisComOnzeDigitos = substr($_1_u_pessoa_pis, 1);
										} else {
											$pisComOnzeDigitos = $_1_u_pessoa_pis;
										}
									?>
										<input name="_1_<?= $_acao ?>_pessoa_pis" id="n_pis" type="text" size="15" onkeyup="validaPis(this)" value="<?= $pisComOnzeDigitos ?>" maxlength="11"> <br>
									<? } else { ?>
										<input id="n_pis" type="text" size="15" onkeyup="validaPis(this)">
									<? } ?>
									<span id="alertapisinvalido" class="hidden" style="color: red;"><strong>PIS inválido</strong></span>
									<span id="alertapisvalido" class="hidden" style="color: green;"><strong>OK</strong></span>

									<input name="old_pessoa_pis" type="hidden" size="15" value="<?= $_1_u_pessoa_pis ?>" maxlength="11">
								</td>
								<td class="lbr" align="right">Est. Civil</td>
								<td><input name="_1_<?= $_acao ?>_pessoa_estcivil" type="text" size="25" value="<?= $_1_u_pessoa_estcivil ?>"></td>
							</tr>

							<tr>
								<td class="lbr" align="right">
									Gênero
								</td>
								<td>
									<select name="_1_<?= $_acao ?>_pessoa_sexo" class="size11" vnulo>
										<option value=""></option>
										<? fillselect("SELECT 'M', 'Masculino' UNION SELECT 'F', 'Feminino'", $_1_u_pessoa_sexo) ?>
									</select>
								</td>
								<td class="lbr" align="right">
									Tamanho do Uniforme
								</td>
								<td>
									<select name="_1_<?= $_acao ?>_pessoa_tamanhouniforme" class="size15">
										<option value=""></option>
										<? fillselect(["Babylook P"=>"Babylook P",
														"Babylook M"=>"Babylook M",
														"Babylook G"=>"Babylook G",
														"Normal PP"=>"Normal PP",
														"Normal P"=>"Normal P",
														"Normal M"=>"Normal M",
														"Normal G"=>"Normal G",
														"Normal GG"=>"Normal GG",
														"Normal XG"=>"Normal XG",
														"Normal XXG"=>"Normal XXG",
														], $_1_u_pessoa_tamanhouniforme) ?>
									</select>
								</td>
							</tr>

						</table>


					</div>

					<div class="col-md-2">
						<table>
							<tr>
								<td>
									<img src="inc/img/avatarprofile.png" id="avatarFoto" title="Clique para alterar a foto do funcionário" class="dz-clickable">
								</td>
							</tr>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?
}
if (!empty($_1_u_pessoa_idpessoa)) {
?>
		<div class='row'>
			
				<div class="col-md-6">
					<div class="panel panel-default">
						<div class="panel-heading" href="#sistema">Sistema</div>
						<div class="panel-body">
							<div  id="sistema">
								<div class="alert alert-info">
									<? if (!empty($_1_u_pessoa_idpessoa)) { ?>
										<table>
											<tr>
												<td rowspan=99><i class="fa fa-shield fa-2x"></i></td>
												<td class="lbr" align="right">Usuário:</td>
												<td class="fonte14 preto bold">
													<?
													if (empty($_1_u_pessoa_usuario)) { //deixar informar somente se nao tiver sido salvo e a variavel da pagina nao existir
													?>
														<input name="_1_<?= $_acao ?>_pessoa_usuario" id="username" type="text" size="15" vnulo value="" maxlength="30" vregex="^[a-zA-Z0-9_.]+$" autocomplete="off" onchange="gerauser(this)">
														<input name="username_old" id="username_old" type="hidden">
													<?
													} else {
													?>
														<input name="_1_<?= $_acao ?>_pessoa_usuario" type="hidden" value="<?= $_1_u_pessoa_usuario ?>">
														<?= $_1_u_pessoa_usuario ?>
													<?
													}
													?>
												</td>
											</tr>
											<tr>
												<? if (!empty($_1_u_pessoa_usuario) and empty($_1_u_pessoa_email)) { ?>
											</tr>
										</table>
										<table>
											<tr>
												<td colspan=99 class="cinza">
													Atenção: Para geração ou alteração de senha, o usuário deve ter salvo o seu <span class="link" onclick="$('[name=_1_u_pessoa_email]').addClass('highlight').focus();">email pessoal</span></b>
												</td>
											<? } else { ?>
												<td>Senha:</td>
												<td><span class="link bold" onclick="javascript:CB.mostraRecuperaSenha('<?= $_1_u_pessoa_usuario ?>','<?= $_1_u_pessoa_email ?>')">Gerar Senha</span></td>
											<? } ?>
											</tr>
										</table>
									<? } ?>
								</div>
								<table>
									<tr>
										<td class="lbr" align="right">Assina Teste</td>
										<td>
											<select name="_1_<?= $_acao ?>_pessoa_assinateste" vnulo>
												<? fillselect(array('N' => 'NÃO', 'Y' => 'SIM'), $_1_u_pessoa_assinateste); ?>
											</select>
										</td>
									</tr>
									<tr>
										<td class="lbr" align="right">Visualiza Resultado</td>
										<td>
											<select name="_1_<?= $_acao ?>_pessoa_visualizares" vnulo>
												<? fillselect(array('N' => 'NÃO', 'Y' => 'SIM'), $_1_u_pessoa_visualizares); ?>
											</select>
										</td>
									</tr>
									<tr>
										<td class="lbr" align="right">Mostrar No Organograma</td>
										<td>
											<select name="_1_<?= $_acao ?>_pessoa_organograma" vnulo>
												<? fillselect(array('N' => 'NÃO', 'Y' => 'SIM'), $_1_u_pessoa_organograma); ?>
											</select>
										</td>
									</tr>
									<tr>
										<td class="lbr" align="right">Visualizar Organograma</td>
										<td>
											<select name="_1_<?= $_acao ?>_pessoa_visualizarorganograma" vnulo>
												<? fillselect(array('N' => 'NÃO', 'Y' => 'SIM'), $_1_u_pessoa_visualizarorganograma); ?>
											</select>
										</td>
									</tr>
									<tr>
										<td class="lbr" align="right">Comissão Colaborador Inativo</td>
										<td>
											<select name="_1_<?= $_acao ?>_pessoa_comissaoinativo" vnulo>
												<? fillselect(array('N' => 'NÃO', 'Y' => 'SIM'), $_1_u_pessoa_comissaoinativo); ?>
											</select>
										</td>
									</tr>
									<tr>
										<td colspan="2">
											<hr>
										</td>
									</tr>

									<?
									// ---------------------------------------------------------------------------------------------------------------------------------------------------------------- //
									//																																									//
									//																	WEBMAIL																							//
									//																																									//
									// ---------------------------------------------------------------------------------------------------------------------------------------------------------------- //
									//OBS: Caso algum dia volte a ter um email interno é necessário verificar esta parte de criação de webmail que agora será criado automático.
									?>
									<input name="_1_<?= $_acao ?>_pessoa_webmailemail" type="hidden" value="<?=$_1_u_pessoa_webmailusuario?>">
									<input name="_1_<?= $_acao ?>_pessoa_idempresa" type="hidden" value="<?=$_1_u_pessoa_idempresa?>">
									<?
									$habilitarwebmailempresa = traduzid('empresa', 'idempresa', 'habilitarwebmail', cb::idempresa());
									if (!empty($_1_u_pessoa_idpessoa) and $habilitarwebmailempresa == 'Y') { ?>
										<tr>
											<? if ($_1_u_pessoa_webmailpermissao == "N" and !empty($_1_u_pessoa_usuario)) { ?>
												<td class="lbr" align="right">Web Email</td>
												<td>
													<select name="_1_<?= $_acao ?>_pessoa_webmailpermissao" onchange="confemailpermissao();">
														<? fillselect("select 'N','NAO' union select 'Y','SIM'", $_1_u_pessoa_webmailpermissao); ?>
													</select>
												</td>
												<? } else {

												// GVT - 19/05/2020 - Adicionada a funcionalidade do usuário escolher um email principal para o seu WebMail
												//					- com base nos domínios cadastrados na empresa.
												if (empty($_1_u_pessoa_webmailusuario) and empty($_1_u_pessoa_webmailemail) and !empty($_1_u_pessoa_usuario)) { ?>
													<td class="lbr" align="right">Email Principal</td>
													<td>
														<div>
															<?
															$sqldominio = "select max(iddominio),dominio from dominio where 1 " . getidempresa('idempresa', 'dominio') . " and status in ('ATIVO','EXTERNO') group by dominio order by dominio";
															$resdominio = d::b()->query($sqldominio) or die("A Consulta dos dominios falhou :" . mysql_error() . "<br>Sql:" . $sqldominio);
															while ($rowdominio = mysqli_fetch_assoc($resdominio)) {
																$email = $_1_u_pessoa_usuario . "@" . $rowdominio["dominio"];
																$auxuser = explode(".", $rowdominio["dominio"]);
																$user = $_1_u_pessoa_usuario . "_" . $auxuser[0];
																if (filter_var($email, FILTER_VALIDATE_EMAIL)) { ?>
																	<input type="radio" name="_1_<?= $_acao ?>_pessoa_webmailemail" user="<?= $user ?>" id="k_<?= $rowdominio["iddominio"] ?>" value="<?= $email ?>"><label for="k_<?= $rowdominio["iddominio"] ?>"><?= $email ?></label><br>
																<? } else {
																	echo "<div>ERRO: " . $email . "</div>";
																}
																?>
															<? }
															?>
														</div>


													</td>
												<? } else {
												?>
													<td class="lbr" align="right">Email Principal</td>
													<td class="nowrap">
														<font color="red"><?= $_1_u_pessoa_webmailusuario ?> <?= $_1_u_pessoa_webmailemail ?></font>
														<input name="_1_<?= $_acao ?>_pessoa_webmailusuario" type="hidden" <?if(empty($_1_u_pessoa_webmailusuario)){echo("disabled");}?> size="5" value="<?= $_1_u_pessoa_webmailusuario ?>">
														<a title="Gerar Assinatura de Email." class="fa fa-print cinza pointer hoverazul" onclick="janelamodal('form/assinaturaemail.php?_acao=u&idpessoa=<?= $_1_u_pessoa_idpessoa ?>')"></a>
													</td>
											<? }
											} ?>
										</tr>
										<?
										// GVT - 19/05/2020 - Um funcionário pode ter mais de um email associado.
										//					- Ao associar um novo email, é criado um registro na tabela
										//					- emailvirtualconf, setando o campo email original com o valor selecionado
										//					- no input abaixo e o campo email destino com o valor do input do Email Principal ("_1_u_pessoa_webmailemail)
										if (!empty($_1_u_pessoa_webmailusuario) and !empty($_1_u_pessoa_webmailemail) and !empty($_1_u_pessoa_usuario)) { ?>
											<tr>
												<td class="lbr" align="right">Email(s) virtual(is)</td>
												<td><input id="emailvirtual" class="compacto" type="text" cbvalue placeholder="Selecione"></td>
											</tr>
											<tr>
												<td></td>
												<td>
													<?= listaEmailVirtual(); ?>
												</td>
											</tr>
									<? }
									} ?>
								</table>
								<div class="alert alert-info">
									<label>Sistema:</label><br/>
									<table>
									<tr>
<?
	$sacesso=$_1_u_pessoa_acesso=="N"?"checked":"";
?>
										<!--JLAL - 14-08-20 **Acrescimo do campo (Acesso ext. ao sistema) para verificar se o usuário terá acesso ao sistema mesmo que não enteja presente na empresa**-->
										<td>Acesso ao Sislaudo:</td>
										<td>
										<select name="_1_u_pessoa_acesso">
												
												<? fillselect(
													[
														"N"=>"Apenas em horário comercial ( 06:00 - 18:00 )",
														"B"=>"Bloqueio temporário (Férias, afastamento e etc.)",
														"Y"=>"Sem restrições (Acesso total)",
													], $_1_u_pessoa_acesso); ?>
											</select>
										</td>
									</tr>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>			
				<div class="col-md-6">	
					
				<div class="col-sm-12">
					<div class="panel panel-default">
						<div class="panel-heading"  href="#areasetor">
							Conselhos, Áreas, Departamentos e Setores de Atuação
						</div>
						<div class="panel-body " id="areasetor">
							<a class="fa fa-search azul pointer hoverazul mb-3" title="Histórico" onClick="historico();"></a>
							<?
							$sqlarea = "SELECT po.idpessoaobjeto, po.tipoobjeto, po.idobjeto,po.responsavel
										  FROM pessoaobjeto po
										 WHERE po.idpessoa = $_1_u_pessoa_idpessoa
										 AND tipoobjeto NOT IN ('pessoa', 'rhtipoevento')";

							$resarea = d::b()->query($sqlarea) or die("A Consulta das Áreas e setores relacionados falhou: " . mysql_error() . "<p>SQL: $sqlarea");
							$qtda = mysqli_num_rows($resarea);
							if($qtda < 1) {
							?>
								<table>
									<tr>
										<td><input id="sgsetorvinc" class="compacto" type="text" cbvalue placeholder="Selecione"></td>
									</tr>
								</table>
							<? } ?>
							<table class="table table-striped planilha ">
								<tbody class="vinculos">
									<?
									while ($rarea = mysqli_fetch_assoc($resarea)) {
										$i = $i + 1;

										//Valida qual tipo Objeto, buscando o nome e id correspondente
										if ($rarea['tipoobjeto'] == 'sgconselho') {
											$sqlConselho = "SELECT idsgconselho, conselho FROM sgconselho where status = 'ATIVO' and  idsgconselho = " . $rarea['idobjeto'];
											$resConselho = d::b()->query($sqlConselho) or die("A Consulta dos Conselhos falhou: " . mysql_error(d::b()) . "<p>SQL: $sqlConselho");
											$conselhoArray = mysqli_fetch_assoc($resConselho);
											$descricao = $conselhoArray['conselho'];
											$link = 'sgconselho&_acao=u&idsgconselho=' . $conselhoArray['idsgconselho'];
											$objeto = 'CONSELHO';
										}

										if ($rarea['tipoobjeto'] == 'sgarea') {
											$sqlArea2 = "SELECT idsgarea, area FROM sgarea where status = 'ATIVO' and  idsgarea = " . $rarea['idobjeto'];
											$resArea2 = d::b()->query($sqlArea2) or die("A Consulta das Áreas falhou: " . mysql_error() . "<p>SQL: $sqlArea2");
											$rArea2 = mysqli_fetch_assoc($resArea2);
											$descricao = $rArea2['area'];
											$link = 'sgarea&_acao=u&idsgarea=' . $rArea2['idsgarea'];
											$objeto = 'ÁREA';
										}

										if ($rarea['tipoobjeto'] == 'sgdepartamento') {
											$sqlDepartamento2 = "SELECT idsgdepartamento, departamento FROM sgdepartamento where status = 'ATIVO' and  idsgdepartamento = " . $rarea['idobjeto'];
											$resDepartamento2 = d::b()->query($sqlDepartamento2) or die("A Consulta das Departamento falhou: " . mysql_error() . "<p>SQL: $sqlArea2");
											$rDepartamento2 = mysqli_fetch_assoc($resDepartamento2);
											$descricao = $rDepartamento2['departamento'];
											$link = 'sgdepartamento&_acao=u&idsgdepartamento=' . $rDepartamento2['idsgdepartamento'];
											$objeto = 'DEPARTAMENTO';
										}

										if ($rarea['tipoobjeto'] == 'sgsetor') {
											$sqlArea2 = "SELECT idsgsetor, setor FROM sgsetor where status = 'ATIVO' and idsgsetor = " . $rarea['idobjeto'];
											$resArea2 = d::b()->query($sqlArea2) or die("A Consulta das Setor falhou: " . mysql_error() . "<p>SQL: $sqlArea2");
											$rArea2 = mysqli_fetch_assoc($resArea2);
											$descricao = $rArea2['setor'];
											$link = 'sgsetor&_acao=u&idsgsetor=' . $rArea2['idsgsetor'];
											$objeto = 'SETOR';
										}
										if ($descricao != '') {
									?>
											<tr>
												<td class="nowrap">
											<?if($rarea['responsavel']=='Y'){?>
												<i class="fa fa-1x cinza btn-lg pointer"  title="Responsável">Responsável:</i> 
											<?}?>
												<?= $objeto . ' - ' . $descricao ?>
												</td>
												<td>
													<a class="fa fa-bars cinzaclaro hoverazul pointer" onclick="janelamodal('?_modulo=<?= $link ?>');"></a>
												</td>
												<td align="center">
													<i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="inativaobjeto(<?= $rarea['idpessoaobjeto'] ?>,'pessoaobjeto')" title="Excluir!"></i>
												</td>
											</tr>
									<?    }
									} //while($rarea = mysqli_fetch_assoc($resarea)) {
									?>
								</tbody>
							</table>
						</div>
					</div>
					</div>

					<div class="col-sm-12">
					<div class="panel panel-default">
						<div class="panel-heading" data-toggle="collapse" href="#enderecoinfo">Endereço</div>

						<table class="table table-striped planilha collapse" id="enderecoinfo">
							<tr>
								<th>Tipo</th>
								<th>Endere&ccedil;o</th>
								<th>Cidade</th>
								<th>UF</th>
								<th colspan="2">CEP</th>

							</tr>
							<?
							$sql = "SELECT e.idendereco,e.idtipoendereco,concat(e.logradouro,' ',e.endereco,' ',e.numero,' ',e.complemento) as endereco,e.bairro,c.cidade,e.uf,e.cep
									FROM endereco e left join nfscidadesiaf c on (c.codcidade = e.codcidade)
									WHERE 
									e.status = 'ATIVO'
									and e.idpessoa = " . $_1_u_pessoa_idpessoa . " order by e.idtipoendereco";

							$res = d::b()->query($sql) or die("A Consulta falhou : " . mysql_error() . "<p>SQL: $sql");
							while ($row = mysqli_fetch_array($res)) {

								$sqltipo = "SELECT tipoendereco FROM tipoendereco WHERE idtipoendereco = " . $row["idtipoendereco"];

								$alertend = (empty($row["endereco"]) || empty($row["cidade"]) || empty($row["uf"]) || empty($row["cep"])) ? "style='background-color:red;color: yellow'" : "";
								$result = d::b()->query($sqltipo) or die("A Consulta falhou : " . mysql_error() . "<p>SQL: $sqltipo");
								$rowtipo = mysqli_fetch_array($result);
							?>
								<tr>
									<td <?= $alertend ?>><?= $rowtipo["tipoendereco"] ?></td>
									<td <?= $alertend ?>><?= $row["endereco"] ?></td>
									<td <?= $alertend ?>><?= $row["cidade"] ?></td>
									<td <?= $alertend ?>><?= $row["uf"] ?></td>
									<td <?= $alertend ?>><?= $row["cep"] ?></td>
									<td>
										<a class="fa fa-bars pointer hoverazul" title="Endereço" onclick="janelamodal('?_modulo=endereco&_acao=u&idendereco=<?= $row['idendereco'] ?>')"></a>
									</td>
								</tr>
							<?
							}


							?>

							<tr>
								<td>
									<a class="fa fa-plus-circle fa-1x  cinzaclaro hoververde btn-lg pointer" onclick="janelamodal('?_modulo=endereco&_acao=i&idpessoa=<?= $_1_u_pessoa_idpessoa ?><?= $_stridempresa ?>')" title="Cadastro de  Endereço"></a>
								</td>
							</tr>
						</table>

					</div>
				</div>

					
				</div>			
		</div>



<!------------------------------------- Dependentes ------------------------------------------------->
<div class='row'>
	<div class="col-md-6">
		<div class="panel panel-default">
			<div class="panel-heading" class="point" data-toggle="collapse" href="#gpdependente">Dependente(s)</div>
			<div class="panel-body">
				<table class="table table-striped planilha collapse in" id="gpdependente">
					<?
					if (!empty($_1_u_pessoa_idpessoa)) {
						//Seleciona as pessoas do tipo DEPENDENTE
						$sqlDp = "SELECT p.idpessoa, p.nome, po.idpessoaobjeto, po.idobjeto, po.tipo
									FROM pessoa p JOIN pessoaobjeto po ON po.idobjeto = p.idpessoa AND tipoobjeto = 'pessoa'
									WHERE idtipopessoa = 115 AND po.idpessoa = $_1_u_pessoa_idpessoa
								ORDER BY nome;";

						$resDp = d::b()->query($sqlDp) or die("A Consulta de Conta itens falhou :" . mysql_error() . "<br>Sql:" . $sqlDp);
						$qtdDp = mysqli_num_rows($resDp);
						if ($qtdDp > 0) {
					?>
							<tr>
								<th style="width: 35%;">Nome</th>
								<th style="width: 10%;">Grau Parentesco</th>
								<th style="width: 53%;">Tipo Evento</th>
								<th style="width: 2%;"></th>
							</tr>
						<?
						}
						?>
						<tr>
							<td>
								Titular: <?= $_1_u_pessoa_nome ?>
							</td>
							<td>-</td>
							<td>
								<table>
									<tr>
										<?
										$resDpRhTitular = getRhTipoEvento($_1_u_pessoa_idpessoa);
										$idpessoaobjetoarrayTitular = array();
										while ($rowDpRhTitular = mysqli_fetch_assoc($resDpRhTitular)) {
											if (!empty($rowDpRhTitular['idpessoaobjeto'])) {
												$checked = 'checked=checked';
												$onclick = "excluir('pessoaobjeto', '" . $rowDpRhTitular['idpessoaobjeto'] . "')";
												array_push($idpessoaobjetoarrayTitular, $rowDpRhTitular['idpessoaobjeto']);
											} else {
												$checked = '';
												$onclick = "inserirPessoaObjeto('" . $_1_u_pessoa_idpessoa . "', '" . $rowDpRhTitular['idrhtipoevento'] . "', 'rhtipoevento', '" . $rowDpRhTitular['idpessoaobjeto'] . "')";
											}
										?>
											<td style="padding-right: 20px;">
												<label><?= $rowDpRhTitular['evento'] ?></label>
												<input type="checkbox" <?= $checked ?> onclick="<?= $onclick ?>">
											</td>
										<?

										}
										?>
									</tr>
								</table>
							</td>
						</tr>
						<?
						$ir = 0;
						while ($rowDp = mysqli_fetch_assoc($resDp)) {
						?>
							<tr>
								<td >
									<a title="<?= $row["email"] ?>" onclick="janelamodal(`?_modulo=pessoa&_acao=u&idpessoa=<?= $rowDp['idpessoa'] ?>`)"><?= $rowDp["nome"] ?></a>
								</td>
								<td nowrap>
									<select class="size10" name="grauparentesco" onchange="atualizaGrauParentesco(this, '<?= $rowDp['idpessoaobjeto'] ?>')">
										<option value=''></option>
										<? fillselect("SELECT 'AVO-F','Avó' 
												 UNION SELECT 'AVO-M','Avô'
												 UNION SELECT 'CONJUGE','Cônjuge'
												 UNION SELECT 'COMPANHEIRO','Companheiro'
												 UNION SELECT 'ENTIADA','Entiada'
												 UNION SELECT 'FILHA','Filha'
												 UNION SELECT 'FILHO','Filho'
												 UNION SELECT 'IRMA','Irmã'
												 UNION SELECT 'IRMAO','Irmão'
												 UNION SELECT 'MAE','Mãe'
												 UNION SELECT 'NETA','Neta'
												 UNION SELECT 'NETO','Neto'												 
												 UNION SELECT 'PAI','Pai'												 
												 UNION SELECT 'PRIMA','Prima'
												 UNION SELECT 'PRIMO','Primo'
												 UNION SELECT 'TIO','Tio'
												 UNION SELECT 'TIA','Tia'
												 UNION SELECT 'SOBRINHA','Sobrinha'
												 UNION SELECT 'SOBRINHO','Sobrinho'
												 UNION SELECT 'OUTROS','Outros'", $rowDp['tipo']); ?>
									</select>
								</td>
								<td>
									<table>
										<tr>
											<?
											$resDpRh = getRhTipoEvento($rowDp['idpessoa']);
											$idpessoaobjetoarray = array();
											while ($rowDpRh = mysqli_fetch_assoc($resDpRh)) {
												if (!empty($rowDpRh['idpessoaobjeto']) && ($rowDp['idobjeto'] == $rowDpRh['idpessoa'])) {
													$checked = 'checked=checked';
													$onclick = "excluir('pessoaobjeto', '" . $rowDpRh['idpessoaobjeto'] . "')";
													array_push($idpessoaobjetoarray, $rowDpRh['idpessoaobjeto']);
												} else {
													$checked = '';
													$onclick = "inserirPessoaObjeto('" . $rowDp['idpessoa'] . "', '" . $rowDpRh['idrhtipoevento'] . "', 'rhtipoevento', '" . $rowDpRh['idpessoaobjeto'] . "')";
												}
											?>
												<td style="padding-right: 20px;">
													<label><?= $rowDpRh['evento'] ?></label>
													<input type="checkbox" <?= $checked ?> onclick="<?= $onclick ?>">
												</td>
											<?

											}
											?>
										</tr>
									</table>
								</td>
								<td align="center">
									<i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick='removerPessoaObjeto(`pessoaobjeto`, <?= json_encode($idpessoaobjetoarray) ?>, `<?= $rowDp['idpessoaobjeto'] ?>`)' alt="Excluir !"></i>
								</td>
							</tr>
					<?
						}
					}
					?>
				</table>
				<table>
					<tr>
						<td colspan="2">
							Dependente: <input type="text" name="dependente" cbvalue="dependente" value="" style="width: 40em;">
							<i class="fa fa-plus-circle fa-1x  cinzaclaro hoververde btn-lg pointer" id="modaldependente" alt="Inserir novo!"></i>
						</td>
					</tr>
				</table>
			</div>
		</div>
	</div>

<!------------------------------------- Dependentes ------------------------------------------------->


		<div class="col-md-6">
			<div class="panel panel-default">
				<div class="panel-heading" data-toggle="collapse" href="#gpfaturamento">Dados Financeiros</div>
				<div class="panel-body collapse" id="gpfaturamento">
					<table class="table table-striped planilha">
						<tr>
							<th>Banco</th>
							<th>Agência</th>
							<th>Conta</th>
							<th></th>
						</tr>
						<?
						$sqlc = "SELECT * FROM pessoaagencia  WHERE status ='ATIVO' and idpessoa = " . $_1_u_pessoa_idpessoa;
						$resc = d::b()->query($sqlc) or die("Erro ao agencias da pessoa sql=" . $sqlc);

						$qtdcontaitem = mysqli_num_rows($resc);

						$i = 9999;
						while ($rowc = mysqli_fetch_assoc($resc)) {
							$i++;
						?>
							<tr>
								<td>
									<input name="_<?= $i ?>_u_pessoaagencia_idpessoaagencia" type="hidden" value="<?= $rowc["idpessoaagencia"] ?>">
									<input name="_<?= $i ?>_u_pessoaagencia_banco" class="size20" type="text" value="<?= $rowc["banco"] ?>">
								</td>
								<td><input name="_<?= $i ?>_u_pessoaagencia_agencia" class="size10" type="text" value="<?= $rowc["agencia"] ?>"></td>
								<td><input name="_<?= $i ?>_u_pessoaagencia_conta" class="size10" type="text" value="<?= $rowc["conta"] ?>"></td>
								<td><i class="fa fa-trash fa-1x cinzaclaro hoververmelho pointer ui-droppable" style="float:right" title="Excluir " onclick="excluir('pessoaagencia',<?= $rowc['idpessoaagencia'] ?>)"></i></td>
							</tr>

						<?
						}
						?>
						<tr>

							<td >
								<i class="fa fa-plus-circle fa-1x btn-lg  cinzaclaro hoververde pointer" onclick="novo('pessoaagencia')" alt="Inserir novo!"></i>
								Adicionar Informações Bancárias
							</td>
						</tr>
					</table>

					<!-- table class="table table-striped planilha"  > 

                <tr>
                    <th>Categoria</th>
                    <th> Subcategoria</th>
                    <th></th>
                </tr>    
				<?

				//	while($row1 = mysqli_fetch_assoc($res1)){
				//	    $i++;	
				?>
						<tr>
							<td>
							<input name="_<?= $i ?>_u_pessoacontaitem_idpessoacontaitem"type="hidden" value="<?= $row1["idpessoacontaitem"] ?>">
							<select name="_<?= $i ?>_u_pessoacontaitem_idcontaitem"  vnulo>
								<option value=""></option>
								<? //fillselect(getContaItemSelect(),$row1['idcontaitem']);
								?>
							</select>			   
							</td>
							<td>
										<select name="_<?= $i ?>_u_pessoacontaitem_idtipoprodserv"  vnulo>
								<option value=""></option>
								<? //fillselect("select idtipoprodserv,tipoprodserv from tipoprodserv where 1 ".getidempresa('idempresa','tipoprodserv')." and status = 'ATIVO' order by tipoprodserv",$row1['idtipoprodserv']);
								?>
							</select>	
							</td>
							<td align="center">	
							<i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="excluir('pessoacontaitem',<?= $row1['idpessoacontaitem'] ?>)" alt="Excluir !"></i>
							</td>							
						</tr>
						
				<?
				//}
				?>
						<tr>
							
							<td colspan="3">
							<i class="fa fa-plus-circle fa-1x btn-lg  cinzaclaro hoververde pointer" onclick="novo('pessoacontaitem')" alt="Inserir novo!"></i>
							Adicionar Categoria 
							</td>
						</tr>
						</table -->
				</div>
			</div>
		</div>
	</div>


		<div class="row">
			<div class="col-sm-12 col-md-6 px-0">
				<div class="col-sm-12">
					<div class="panel panel-default">
						<div class="panel-heading" data-toggle="collapse" href="#curriculum">Curriculum</div>
						<div class="panel-body">
							<div class="collapse" id="curriculum">
								<table>
									<tr>
										<td>Escolaridade</td>
										<td>
											<select name="_1_<?= $_acao ?>_pessoa_escolaridade" >
												<option value=""></option>
												<?fillselectNoError([
													"FUNDAMENTAL COMPLETO" => "FUNDAMENTAL COMPLETO",
													"FUNDAMENTAL INCOMPLETO" => "FUNDAMENTAL INCOMPLETO",
													"ENSINO MÉDIO COMPLETO" => "ENSINO MÉDIO COMPLETO",
													"ENSINO MÉDIO INCOMPLETO" => "ENSINO MÉDIO INCOMPLETO",
													"GRADUAÇÃO COMPLETO" => "GRADUAÇÃO COMPLETO",
													"GRADUAÇÃO INCOMPLETO" => "GRADUAÇÃO INCOMPLETO",
													"PÓS GRADUAÇÃO" => "PÓS GRADUAÇÃO",
													"MESTRADO" => "MESTRADO",
													"DOUTORADO" => "DOUTORADO",
													"CURSO TÉCNICO COMPLETO" => "CURSO TÉCNICO COMPLETO",
												],$_1_u_pessoa_escolaridade)?>
											</select>
										</td>
									</tr>
									<tr>
										<td>Formação</td>
										<td><textarea name="_1_<?= $_acao ?>_pessoa_formacao" style="width: 100%; height: 100px;"><?= $_1_u_pessoa_formacao ?></textarea></td>
									</tr>
									<tr>
										<td>Exepriência Profissional</td>
										<td><textarea name="_1_<?= $_acao ?>_pessoa_experiencia" style="width: 100%; height: 100px;"><?= $_1_u_pessoa_experiencia ?></textarea></td>
									</tr>
									<tr>
										<td>Qualificações e Atividades</td>
										<td><textarea name="_1_<?= $_acao ?>_pessoa_qualificacao" style="width: 100%; height: 100px;"><?= $_1_u_pessoa_qualificacao ?></textarea></td>
									</tr>
									<tr>
										<td>Informações Adicionais</td>
										<td><textarea name="_1_<?= $_acao ?>_pessoa_obs" style="width: 100%; height: 100px;"><?= $_1_u_pessoa_obs ?></textarea></td>
									</tr>
								</table>
								<?
								if (!empty($_1_u_pessoa_idpessoa)) {
								?>



									<? //listar assinaturas
									/*
						if(!empty($_1_u_pessoa_idpessoa)){
								$sql ="SELECT a.idassinatura,a.assunto,ap.idassinaturapessoa,dmahms(ap.dataassinatura) as dataassinatura,t.idtreinamento,ct.titulo 
										FROM `assinaturapessoa` ap,assinatura a,treinamento t,cadtreinamento ct
										where ct.idcadtreinamento = t.idcadtreinamento
										and t.idtreinamento = a.idobjeto
					                    and a.tipoobjeto = 'treinamento'
					                   	and a.idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]."
					                    and ap.idassinatura = a.idassinatura
					                   	and ap.idpessoa = ".$_1_u_pessoa_idpessoa."
										and ap.dataassinatura is not null";			
									
								$res = d::b()->query($sql) or die("A Consulta dos treinamentos falhou :".mysql_error(d::b())."<br>Sql:".$sql); 
								$qtdrows= mysqli_num_rows($res);
					
							if($qtdrows > 0){
						?>
							
								 <table class="table table-striped planilha" >
								     <tr>			
									<th >Treinamentos Oferecidos</th>
									<th></th>
								
						<?			
								while($row = mysqli_fetch_array($res)){		
								?>			
								    <tr class="respreto">
									   <td nowrap><?=$row["titulo"]?>-<?=$row["dataassinatura"]?></td>	
									   <td>
									   <i class="fa fa-bars fa-1x cinzaclaro hoverazul btn-lg pointer" onclick="janelamodal('?_modulo=treinamento&_acao=u&idtreinamento=<?=$row["idtreinamento"]?>');"></i>
									   </td>
								   </tr>	
					<?
								}
					?>				
								 
					<?
							}else{
								echo"Este ainda não assinou treinamentos";	
							}	
					?>
								</table>
					
					<?		
						}
                                                */
									?>

								<?
								}
								?>
							</div>
						</div>
					</div>
				</div>
				<div class="col-sm-12">
					<div class="panel panel-default">
						<div class="panel-heading" data-toggle="collapse" href="#funcoes">Funções </div>
						<div class="panel-body">
							<div class="collapse" id="funcoes">
								<table>

									<tr>

										<td align="center">
											<? if (!empty($_1_u_pessoa_idpessoa)) {
											?>
												<?= listaSgPessoaFuncao($_1_u_pessoa_idpessoa) ?>
											<? } ?>
										</td>
									</tr>
								</table>
							</div>
						</div>
					</div>
				</div>
				<?
				$sql0 = "select t.idtag,t.tag,t.descricao, e.sigla from tag t join empresa e on (t.idempresa = e.idempresa) where t.status='ATIVO' and t.idpessoa = " . $_1_u_pessoa_idpessoa . "";

				$res10 = d::b()->query($sql0) or die("A Consulta de Conta itens falhou :" . mysql_error() . "<br>Sql:" . $sql0);
				?>
				<div class="col-sm-12">
					<div class="panel panel-default">
						<div class="panel-heading" data-toggle="collapse" href="#tags">Tags Vinculadas</div>
						<div class="panel-body">
							<div class="collapse" id="tags">
								<table class="table table-striped">
									<thead>
										<th>Tag</th>
										<th>Descrição</th>
										<th></th>
									</thead>
									<tbody>
										<? while ($row10 = mysqli_fetch_assoc($res10)) { ?>
											<tr>
												<td><?= $row10['sigla'] ?>-<?= $row10['tag'] ?></td>
												<td><?= $row10['descricao'] ?></td>
												<td>
													<a class="fa fa-bars cinzaclaro hoverazul pointer" onclick="janelamodal('?_modulo=tag&_acao=u&idtag=<?= $row10['idtag'] ?>');"></a>
												</td>
											</tr>
										<? } ?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-sm-12 col-md-6 px-0">
				<?
				/*		
		$sql1 = "select i.idpessoacontaitem,c.contaitem,c.idcontaitem,i.idtipoprodserv
				from pessoacontaitem i left join contaitem c on(i.idcontaitem = c.idcontaitem and c.status ='ATIVO')
						left join tipoprodserv t on(t.idtipoprodserv= i.idtipoprodserv)
				where i.idpessoa = ".$_1_u_pessoa_idpessoa ."
			order by c.contaitem;";

		$res1 = d::b()->query($sql1) or die("A Consulta de Conta itens falhou :".mysql_error()."<br>Sql:".$sql1);	
			$qtd1=mysqli_num_rows($res1);
			*/
				?>
				<?
				if ($_1_u_pessoa_idpessoa) {
					if($_1_u_pessoa_contrato=="CLT"){
?>
						<div class="col-sm-12">
							<div class="panel panel-default">
								<div class="panel-heading" >Contrato de Trabalho</div>
								<div class="panel-body">
									<div >
									<table  class="table table-striped planilha">
										<?
										 $sqlc="select DATEDIFF(now(),c.vigenciafim) as vencido,c.* from contrato c where c.idpessoa=".$_1_u_pessoa_idpessoa." order by c.vigencia desc";
										 $resc = d::b()->query($sqlc) or die("Erro ao buscar Contrato temporário : " . mysql_error(d::b()) . "<p>SQL:" . $sqlc);
										$qtdc =mysqli_num_rows($resc);
										//echo( $sqlc);
										if($qtdc>0){
										?>									
										<tr>
											<th>Contrato</th>
											<th>Início</th>											
											<th>Fim</th>
											<th>status</th>
											<th></th>
										</tr>
										<?
										}
										$temativo='N';
										while($rowc = mysqli_fetch_assoc($resc)){
											$i=$i+1;
											if(($rowc['vencido'] > 0 and !empty($rowc['vigenciafim'])) OR ($rowc['status']=='INATIVO')){
												$color="color:red";
											}else{
												$color="color:black";
											}
											if($rowc['status']!='INATIVO'){
												$temativo='Y';
											}

												?>
												<tr>
													<td>
														<input name="_<?= $i ?>_u_contrato_idcontrato" type="hidden"  value="<?= $rowc['idcontrato'] ?>">
														<input name="_<?= $i ?>_u_contrato_titulo" type="text" class="size30" value="<?=$rowc['titulo'] ?>">
													</td>
													<td><input name="_<?= $i ?>_u_contrato_vigencia" type="text" class="size8 calendario" value="<?=dma($rowc['vigencia'])?>"></td>											
													<td><input name="_<?= $i ?>_u_contrato_vigenciafim" type="text" class="size8 calendario" value="<?=dma($rowc['vigenciafim'])?>"></td>
													<td style="<?=$color?>"><?=$rowc['status'] ?></td>
													<td><i class="fa fa-trash fa-1x cinzaclaro hoververmelho pointer pull-right" onclick="inativa('contrato','idcontrato', <?=$rowc['idcontrato']?>)"  title="Inativar contrato"></i></td>
												</tr>
										<?
											
											
										}
										?>
										<?if($temativo=='N'){?>
										<tr>
											<td colspan="5">
												<i class="fa fa-plus-circle fa-x verde btn-lg pointer" onclick="novoContrato('contrato')" title="Inserir Contrato"></i>
											</td>
										</tr>
										<?}?>

									</table>
									
									</div>
								</div>
							</div>
						</div>
<?
					}//if($_1_u_pessoa_contrato=="PD"){
					if (array_key_exists("rhfolha", getModsUsr("MODULOS")) == 1 ||  $_SESSION["SESSAO"]["IDPESSOA"] == 6494) {

						$sqlf = "select max(f.idrhfolha) as idrhfolha from rhfolhaitem f where idpessoa =" . $_1_u_pessoa_idpessoa;
						$resf = d::b()->query($sqlf) or die("Erro ao buscar ultima folha do funcionário : " . mysql_error(d::b()) . "<p>SQL:" . $sqlf);
						$rowf = mysqli_fetch_assoc($resf);
				?>
						<div class="col-sm-12">
							<div class="panel panel-default">
								<div class="panel-heading" data-toggle="collapse" href="#rh">RH</div>
								<div class="panel-body">
									<div class="collapse" id="rh">
										<table>
											<th align="center" ><a class="fa fa-search azul pointer hoverazul" title="Histórico" onClick="rhPessoa();"></a></th>
											<tr>
												<td class="lbr" align="right">Regime</td>
												<td>
													<select name="_1_<?= $_acao ?>_pessoa_contrato">
														<? fillselect("SELECT 'CLT','CLT' UNION SELECT 'ES','Estagiário' UNION SELECT 'PJ','PJ' UNION SELECT 'TERC','Terceiro' UNION SELECT 'SO','Sócio'", $_1_u_pessoa_contrato); ?>
													</select>
												</td>
												<td>
													<?/*if($_1_u_pessoa_bancohoras=="Y"){?>
		    <i style="padding-right: 0px;" class="fa fa-check-square-o fa-1x btn-lg pointer" onclick="alttipocontato('bancohoras','N');" Title="Este campo controla se gera banco de horas ou dias trabalhados.">&nbsp;&nbsp;Banco de Horas</i>
		    <?}else{?>
		    <i style="padding-right: 0px;" class="fa fa-square-o fa-1x btn-lg pointer" onclick="alttipocontato('bancohoras','Y');" Title="Este campo controla se gera banco de horas ou dias trabalhados.">&nbsp;&nbsp;Banco de Horas</i>
        	    <?}*/ ?>
												</td>
											</tr>
											<tr>
												<td class="lbr" align="right">Salário</td>
												<td>
													<input name="_1_<?= $_acao ?>_pessoa_salario" type="text" size="15" value="<?= $_1_u_pessoa_salario ?>" vdecimal>
													<label style="font-size:9px;">
														<font color="silver">Def: </font><?= traduzid("sgcargo", "idsgcargo", "salario", $_1_u_pessoa_idsgcargo) ?>
													</label>
												</td>
											</tr>
											<tr>
												<td class="lbr" align="right">Insalubridade</td>
												<td><input name="_1_<?= $_acao ?>_pessoa_insalubridade" type="text" size="15" value="<?= $_1_u_pessoa_insalubridade ?>" vdecimal></td>
											</tr>
											<tr>
												<td class="lbr" align="right">Unimed Mens.</td>
												<td><input name="_1_<?= $_acao ?>_pessoa_unimedmens" type="text" size="15" value="<?= $_1_u_pessoa_unimedmens ?>" vdecimal></td>
											</tr>
											<tr>
												<td class="lbr" align="right">Empréstimo</td>
												<td class="nowrap">
													<input name="_1_<?= $_acao ?>_pessoa_emprestimo" type="text" placeholder="R$" value="<?= $_1_u_pessoa_emprestimo ?>" vdecimal class="size6">Parc.
													<input name="_1_<?= $_acao ?>_pessoa_parcela" type="text" size="2" placeholder="Parc." title="Parcela" value="<?= $_1_u_pessoa_parcela ?>" class="size3"> de
													<input name="_1_<?= $_acao ?>_pessoa_parcelas" type="text" size="2" placeholder="Total P." title="Total de Parcelas" value="<?= $_1_u_pessoa_parcelas ?>" class="size3">
												</td>
											</tr>
											<tr>
												<td class="lbr" align="right">Qtd. Dependente</td>
												<td><input name="_1_<?= $_acao ?>_pessoa_qtddependente" type="text" size="15" value="<?= $_1_u_pessoa_qtddependente ?>"></td>
												<td class="lbr" align="right">Vlr. Dependente</td>
												<td><input name="_1_<?= $_acao ?>_pessoa_vlrdependente" type="text" size="5" value="<?= $_1_u_pessoa_vlrdependente ?>"></td>
											</tr>
											<tr>
												<td class="lbr" align="right">Evento Folha</td>
												<td>
													<select name="_1_<?= $_acao ?>_pessoa_idrheventofolha" vnulo>
													<option value=""></option>
														<? fillselect("select idrheventofolha,titulo from rheventofolha where status='ATIVO' and idempresa=".$_1_u_pessoa_idempresa." order by titulo;", $_1_u_pessoa_idrheventofolha); ?>
													</select>
												</td>
												<td><a class="fa fa-bars cinzaclaro hoverazul pointer" onclick="janelamodal('?_modulo=rheventofolha&_acao=u&idrheventofolha=<?=$_1_u_pessoa_idrheventofolha?>');"></a></td>
											</tr>
											<tr>
												<td class="lbr" align="right">Pagamento</td>
												<td>
													<select name="_1_<?= $_acao ?>_pessoa_tipopagamento" vnulo>													
														<? fillselect("SELECT 'CONVENIO','Convênio' UNION SELECT 'TRANSFERENCIA','Transferência' UNION SELECT 'CHEQUE','Cheque';", $_1_u_pessoa_tipopagamento); ?>
													</select>
												</td>
											</tr>
											<tr>
												<td class="lbr" align="right">Observações</td>
												<td ><textarea name="_1_<?= $_acao ?>_pessoa_observacaore" rows="3" cols="70"><?= $_1_u_pessoa_observacaore ?></textarea></td>
											</tr>
											<?
										$sqc="select SUBSTRING(SEC_TO_TIME(sum(TIME_TO_SEC(TIMEDIFF(horafim,horaini)))),1,2) as cargah
											from pessoahorario p 
											where p.idpessoa=" . $_1_u_pessoa_idpessoa . "
											group by p.idpessoa";
										$rec = d::b()->query($sqc) or die("Erro ao buscar carga horaria do funcionário : " . mysql_error(d::b()) . "<p>SQL:" . $sqc);
										$rwc=mysqli_fetch_assoc($rec);
										?>
										<?if(!empty($rwc['cargah'])){?> 
											<tr>
												<td class="lbr" align="right">Carga Horária</td>
												<td >
													<label style="float:left" class="alert-warning">
														<?=$rwc['cargah']?>h Semanais 
													</label>
												</td>
										 <?}?>

										</table>
										<br>
										<div class="panel panel-default">
											<div class="panel-heading" style="height:34px">
												Evento(s) Fixo(s)
											</div>											
											<div class="panel-body">
										<table class="table table-striped planilha">
											
											<?
											$sqlh = "select e.idrheventopessoa,e.valor,t.idrhtipoevento,e.status
                            from rheventopessoa e 
							left join rhtipoevento t on(t.idrhtipoevento=e.idrhtipoevento)
                            where e.idpessoa=" . $_1_u_pessoa_idpessoa . " ";
											$resh = d::b()->query($sqlh) or die("Erro ao buscar eventos fixos do funcionário : " . mysql_error(d::b()) . "<p>SQL:" . $sqlh);
											$qtdh = mysqli_num_rows($resh);
											if ($qtdh > 0) {
											?>
												<tr>
													<th>Evento</th>
													<th>Valor</th>
													<th></th>
												</tr>
												<?
												while ($rowh = mysqli_fetch_assoc($resh)) {
													$i = $i + 1;
												?>
													<tr>
														<td>
															<input name="_<?= $i ?>_u_rheventopessoa_idrheventopessoa" type="hidden" value="<?= $rowh['idrheventopessoa'] ?>">
															<select name="_<?= $i ?>_<?= $_acao ?>_rheventopessoa_idrhtipoevento">
																<option value=""></option>
																<? fillselect("select idrhtipoevento,evento 
                                                from rhtipoevento 
                                                where status = 'ATIVO' 
												" . getidempresa('idempresa', 'rhtipoevento') . "												
                                                and flgfixo='Y'  order by evento ", $rowh['idrhtipoevento']); ?>
															</select>

														</td>
														<td>
															<?
															if($rowh['idrhtipoevento'] == 486){
																$sqldep = "SELECT 
																				p.idpessoa,
																				p.nome,
																				po.idpessoaobjeto,
																				po.idobjeto,
																				po.tipo,
																				p.nasc
																			FROM
																				pessoa p
																					JOIN
																				pessoaobjeto po ON po.idobjeto = p.idpessoa
																					AND tipoobjeto = 'pessoa'
																					JOIN
																				pessoaobjeto po1 ON po1.idpessoa = p.idpessoa
																			WHERE
																				idtipopessoa = 115
																					AND po.idpessoa = $_1_u_pessoa_idpessoa
																					AND po1.idobjeto = 486
																					AND po1.tipoobjeto = 'rhtipoevento' 
																			UNION SELECT 
																				p.idpessoa,
																				p.nome,
																				po.idpessoaobjeto,
																				po.idobjeto,
																				po.tipo,
																				p.nasc
																			FROM
																				pessoa p
																					JOIN
																				pessoaobjeto po ON po.idpessoa = p.idpessoa
																					AND tipoobjeto = 'rhtipoevento'
																			WHERE
																				po.idpessoa = $_1_u_pessoa_idpessoa AND po.idobjeto = 486;";

																$resdep = d::b()->query($sqldep) or die("Erro ao buscar dependentes do funcionário : " . mysql_error(d::b()) . "<p>SQL:" . $sqldep);
																$qtddep = mysqli_num_rows($resdep);
																$valor = 0;
																if($qtddep > 0){
																	$valor = 0;
																	while($rowdep = mysqli_fetch_assoc($resdep)){
																		
																		$dataNascimento = $rowdep["nasc"];
															
																		$dataAtual = new DateTime();
																		$dataNascimento = new DateTime($dataNascimento);
																		$idade = $dataAtual->diff($dataNascimento);
																		$idade = $idade->y;
															
																		$sql = "SELECT 
																				percentual as valor
																			FROM 
																				rhtipoeventobc
																			WHERE 
																				idrhtipoevento = 486 -- //486 = 486 em produção
																				and $idade BETWEEN valinicio AND valfim";
																		$res = d::b()->query($sql);
																		$row = mysqli_fetch_assoc($res);
															
																		if(empty($row)){
																			$sql = "SELECT 
																					valor
																				FROM 
																					rhtipoevento
																				WHERE 
																					idrhtipoevento = 486";
																			$res = d::b()->query($sql);
																			$row = mysqli_fetch_assoc($res);
																		}
																		$valor += $row["valor"];
																	}
																}
																if($valor == 0){
																	$valor = $rowh['valor'];
																}else{
																	$rowh['valor'] = number_format($valor, 2, ',', '.');
																}
															}
															?>

															<input name="_<?= $i ?>_u_rheventopessoa_valor" type="text" class="size5" value="<?= $rowh['valor'] ?>">
														</td>
														<td>
															<? if ($rowh['status'] == 'ATIVO') { ?>
																<i class="fa fa-check-circle-o  fa-1x verde hoververde btn-lg pointer ui-droppable" onclick="retiraevfixo(<?= $rowh['idrheventopessoa'] ?>,'INATIVO')" title="ATIVO"></i>
															<? } else { ?>
																<i class="fa fa-check-circle-o  fa-1x vermelho hoververmelho btn-lg pointer ui-droppable" onclick="retiraevfixo(<?= $rowh['idrheventopessoa'] ?>,'ATIVO')" title="INATIVO"></i>
															<? } ?>
														</td>
													</tr>
											<?
												}
											}
											?>
											<tr>
												<td colspan="3"><i class="fa fa-plus-circle fa-x verde btn-lg pointer" onclick="novo('rheventopessoa')" alt="Inserir novo evento fixo!"></i></td>
											</tr>
										</table>
										</div>
										</div>
										<br>
										<div class="panel panel-default">
											<div class="panel-heading" style="height:34px"   data-toggle="collapse" href='#eventopendente'>
											Evento(s) Pendente(s)
											</div>											
											<div class="panel-body"   class="collapse" id='eventopendente'>
										<table class="table table-striped planilha">
											
											<tr>
												<th>Evento</th>
												<th>Valor</th>
												<th>Formato</th>
											</tr>
											<?

											$sqle = "select t.idrhtipoevento,t.evento,t.tipo,t.formato,case t.formato 
                                                        when 'D' then 'Dinheiro'
                                                        when 'H' then 'Horas'                                           
                                                        end as formatacao ,
                                sum(e.valor) as valor
                        from rhtipoevento t,rhevento e
                         where (t.flgfolha='Y' or t.flgferias='Y' or flgfixo='Y')
                         -- and t.idrhtipoeventosum is not null
                         and e.idrhtipoevento = t.idrhtipoevento
                         and e.status='PENDENTE'
                         and e.idpessoa = " . $_1_u_pessoa_idpessoa . " group by  t.idrhtipoevento,t.evento,t.tipo order by t.evento";
											$rese = d::b()->query($sqle) or die("Erro ao buscar eventos da folha ligados ao funcionário : " . mysql_error(d::b()) . "<p>SQL:" . $sqle);
											while ($rowe = mysqli_fetch_assoc($rese)) {
												if ($rowe['tipo'] == 'D') {
													$cortr = "#ee300573"; //vermelho fraco
												} else {
													$cortr = "#98FB98"; //verde
												}
												if ($rowe['formato'] == 'H') {
													$valor = convertHoras($rowe['valor']);
												} else {
													$valor = $rowe['valor'];
												}
											?>
												<tr style="background-color: <?= $cortr ?>;">
													<td>
														<a class="hoverazul pointer" onclick="janelamodal('/?_modulo=rhtipoeventofolha&idrhtipoevento=<?= $rowe['idrhtipoevento'] ?>&idpessoa=<?= $_1_u_pessoa_idpessoa ?>')" title="Eventos pendente">
															<?= $rowe['evento'] ?>
														</a>
													</td>
													<td><?= $valor ?></td>
													<td><?= $rowe['formatacao'] ?></td>
												</tr>
											<?
											}

											?>
											<tr>
												<td colspan="3"><a class="fa fa-plus-circle fa-x verde btn-lg pointer" onclick="repeteev(<?= $_1_u_pessoa_idpessoa ?>)" title="Novo Evento"></a></td>
											</tr>
										</table>
										</div>
										</div>
												<br>
										<div class="panel panel-default">
											<div class="panel-heading" style="height:34px"  data-toggle="collapse" href='#horariotrab'>
											Horário de trabalho
											</div>											
											<div class="panel-body" class="collapse" id="horariotrab">										
											<table class="table table-striped planilha">
											<tbody>

												<?
												$sqlh = "select p.*,case
															when p.periodo = 'Mon' then 1
															when p.periodo = 'Tue' then 2
															when p.periodo = 'Wed' then 3
															when p.periodo = 'Thu' then 4
															when p.periodo = 'Fri' then 5
															when p.periodo = 'Sat' then 6
															when p.periodo = 'Sun' then 7
															else 8 end as ordem from pessoahorario p
															where idpessoa=" . $_1_u_pessoa_idpessoa . " order by ordem,horaini";
												$resh = d::b()->query($sqlh) or die("Erro ao buscar horarios do funcionário : " . mysql_error(d::b()) . "<p>SQL:" . $sqlh);
												$qtdh = mysqli_num_rows($resh);
												if ($qtdh > 0) {
												?>
													<tr>
														<th>
															<input type="checkbox" value="<?= $rowh['idpessoahorario'] ?>" onclick="checkAllHorarios(this)">
														</th>
														<th>De</th>
														<th>Até</th>
														<th>Dias da semana</th>
														<th></th>
													</tr>
													<?
													while ($rowh = mysqli_fetch_assoc($resh)) {
														$i = $i + 1;
													?>
														<tr id="tr_horario<?= $rowh['idpessoahorario'] ?>">
															<td>
																<input name="pessoahorario" type="checkbox" value="<?= $rowh['idpessoahorario'] ?>">
															</td>
															<td>
																<input name="_<?= $i ?>_u_pessoahorario_idpessoahorario" type="hidden" value="<?= $rowh['idpessoahorario'] ?>">
																<input name="_<?= $i ?>_u_pessoahorario_horaini" type="text" class="size5" value="<?= $rowh['horaini'] ?>">
															</td>

															<td>
																<input name="_<?= $i ?>_u_pessoahorario_horafim" type="text" class="size5" value="<?= $rowh['horafim'] ?>">
															</td>
															<td>
																<select name="_<?= $i ?>_u_pessoahorario_periodo" vnulo>
																	<? fillselect("SELECT 'Mon','Segunda' 
											UNION SELECT 'Tue','Terça' 
											UNION SELECT 'Wed','Quarta' 
											UNION SELECT 'Thu','Quinta' 
											UNION SELECT 'Fri','Sexta' 
											UNION SELECT 'Sat','Sábado' 
											UNION SELECT 'Sun','Domingo'", $rowh['periodo']); ?>
																</select>
															</td>

															<td>
																<i class="fa fa-trash fa-1x cinzaclaro hoververmelho pointer" onclick="excluirHorario(<?= $rowh['idpessoahorario'] ?>)" title="Excluir"></i>
															</td>
														</tr>
												<?
													}
												}
												?>
												<tr id="horariosSelecionados" style="display: none;height:40px">
													<td id="horariosSelecionadosTexto" colspan="5" class="text-center">
														<span style="margin-right: 20px;font-weight: 600;"></span>
														<button type="button" class="btn btn-danger btn-xs" onclick="excluirVariosHorarios()" title="Excluir selecionados">
															<i class="fa fa-trash"></i>Excluir
														</button>
													</td>
												</tr>
												<!--            <tr>
                         <td ><i class="fa fa-plus-circle fa-x verde btn-lg pointer" onclick="novo('pessoahorario')" alt="Inserir novo horário!"></i></td>
		    </tr> -->
												<tr>
													<table class="table table-striped planilha">
														<tbody>
															<tr>
																<th>De</th>
																<th>Até</th>
																<th>Dias da semana</th>
																<th></th>
															</tr>

															<tr>
																<td>
																	<input id="template_idpessoa" type="hidden" value="<?= $_1_u_pessoa_idpessoa ?>">
																	<input id="template_horaini" type="time" class="size7" value="">
																</td>
																<td>
																	<input id="template_horafim" type="time" class="size7" value="">
																</td>
																<td>
																	<div class="form-inline">
																		<div class="form-group">
																			<div class="checkbox-dia checkbox"><input type="checkbox" name="diahorario" value="Mon"><span>seg</span></div>
																			<div class="checkbox-dia checkbox"><input type="checkbox" name="diahorario" value="Tue"><span>ter</span></div>
																			<div class="checkbox-dia checkbox"><input type="checkbox" name="diahorario" value="Wed"><span>qua</span></div>
																			<div class="checkbox-dia checkbox"><input type="checkbox" name="diahorario" value="Thu"><span>qui</span></div>
																			<div class="checkbox-dia checkbox"><input type="checkbox" name="diahorario" value="Fri"><span>sex</span></div>
																			<div class="checkbox-dia checkbox"><input type="checkbox" name="diahorario" value="Sat"><span>sab</span></div>
																			<div class="checkbox-dia checkbox"><input type="checkbox" name="diahorario" value="Sun"><span>dom</span></div>
																		</div>
																	</div>
																</td>
																<td><i class="fa fa-plus-circle fa-2x verde  btn pointer" onclick="novoHorario()" title="Adicionar"></i></td>
															</tr>
														</tbody>
													</table>
												</tr>
											</tbody>
										</table>
										</div>
										</div>
											<br>

										<div class="panel panel-default divHistorico">
											<div class="panel-heading" style="height:34px" data-toggle="collapse" href='#hist'>
												<div class="row">
													<div class="col-md-10">Histórico</div>
													<div class="col-md-2">
													</div>
												</div>
											</div>
											<div class="collapse" id="hist">
												<div class="col-md-10"></div>
												<div class="col-md-2">
													<button id="adicionar" type="button" class="btn btn-success btn-xs fright" title="Adicionar" onclick="addcoment()">
														<i class="fa fa-check"></i>Salvar
													</button>
												</div>
												<div class="panel-body" id="comentario" style="max-height: 100px; min-height: 100px; height: 100px;">
													<input name="_100_i_modulocom_idmodulocom" type="hidden" value="">
													<input name="_100_i_modulocom_idempresa" type="hidden" value="1">
													<input name="_100_i_modulocom_idmodulo" type="hidden" value="<?= $_1_u_pessoa_idpessoa ?>">
													<input name="_100_i_modulocom_modulo" type="hidden" value="funcionario">
													<textarea class="caixa" name="_100_i_modulocom_descricao" id="obs" name="" style="width: 100%; height: 80px; resize: none;"></textarea>
													<input name="_100_i_modulocom_status" type="hidden" value="ATIVO">
												</div>
												<div class="panel-body">
													<table class="table table-striped planilha" style="font-size: 10px; word-break: break-word;" id="tblComentarios">
														<?
														$resc = getListaComentariosEvento($_1_u_pessoa_idpessoa);
														$i = 0;
														while ($rowc = mysqli_fetch_assoc($resc)) { ?>
															<tr>
																<td id="comentario<?= $rowc["idmodulocom"] ?>" comentario='true' style="line-height: 14px; padding: 8px; font-size: 11px;color:#666;">
																	<span><?= dmahms($rowc['criadoem']) ?> - <?= $rowc['nomecurto'] ?>:</span>
																	<? if ($_SESSION["SESSAO"]["USUARIO"] == $rowc['criadopor'] and empty($rowc['idstatus'])) { ?>
																		<input name="_01<?= $i ?>_u_modulocom_idmodulocom" type="hidden" value="<?= $rowc["idmodulocom"] ?>">
																		<textarea onfocus="botoes(this)" style="background: transparent;border: none;resize: none;height:auto;" name="_01<?= $i ?>_u_modulocom_descricao"><?= ($rowc['descricao']) ?></textarea>
																	<? } else { ?>
																		<?= nl2br($rowc['descricao']) ?>
																	<? } ?>
																</td>
															</tr>
														<? $i++;
														} ?>
													</table>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div> <? } ?>
				<? } ?>

			</div>
		</div>
		<!-- <div class="col-md-6">
	    <div class="panel panel-default">   
			<div class="panel-heading"  data-toggle="collapse" href="#certificado">Certificado</div>
			<div class="panel-body" id="certificado">
				<table>
					<tr>
						<td>
							Anexo Certificado:
						</td>
						<td>
							<i class="fa fa-cloud-upload dz-clickable pointer azul" style="display: inline-flex;" id="certanexo" title="Clique para adicionar um certificado"></i>
						</td>
					</tr>
					<tr>
						<td>
							Imagem Assinatura:
						</td>
						<td>
							<i class="fa fa-cloud-upload dz-clickable pointer azul" style="display: inline-flex;" id="imagemassinatura"></i>
						</td>
					</tr>
				</table>
			</div>
		</div>
	</div> -->
	
	<? if ($templantel == 'Y' or $_1_u_pessoa_contagro == 'Y' or $_1_u_pessoa_contaves == 'Y' or $_1_u_pessoa_contsuinos == 'Y' or $_1_u_pessoa_contbovinos == 'Y') { ?>
		<div class="row">
			<div class="col-sm-12">
				<div class="panel panel-default">
					<div class="panel-heading" data-toggle="collapse" href="#clientes">Clientes</div>
					<div class="panel-body">
						<div class="collapse" id="clientes">
							<?
							$sql = "select 
            c.idpessoacontato
            ,p.idpessoa      
            ,p.nome
            ,c.participacaoprod
            ,c.participacaoserv
            from pessoa p,pessoacontato c
            where p.idpessoa = c.idpessoa
			and p.idtipopessoa!=5			
            and c.idcontato = " . $_1_u_pessoa_idpessoa . " and p.status in('ATIVO','PENDENTE') order by nome";
							$res = d::b()->query($sql) or die("A Consulta falhou :" . mysql_error() . "<br>Sql:" . $sql);
							$rownum1 = mysqli_num_rows($res);
							?>
							<?
							if ($rownum1 > 0) {
							?>
								<table class="table table-striped planilha ">
									<tr>
										<th>Empresa</th>
										<th>Participação Serv.%</th>
										<th>Participação Prod.%</th>
										<th></th>
										<th></th>
									</tr>
									<?
									$y = 888;
									while ($row = mysqli_fetch_assoc($res)) {
										$_acao = 'u';
										$y = $y + 1;
									?>
										<tr class="res">

											<td nowrap><?= $row["nome"] ?></td>
											<td>
												<input name="_<?= $y ?>_<?= $_acao ?>_pessoacontato_idpessoacontato" type="hidden" size="5" value="<?= $row['idpessoacontato'] ?>">
												<input name="_<?= $y ?>_<?= $_acao ?>_pessoacontato_participacaoserv" type="text" size="5" value="<?= $row['participacaoserv'] ?>" vdecimal>
											</td>
											<td><input name="_<?= $y ?>_<?= $_acao ?>_pessoacontato_participacaoprod" type="text" size="5" value="<?= $row['participacaoprod'] ?>" vdecimal></td>

											<td><a class="fa fa-bars pointer hoverazul" title="Cliente" onclick="janelamodal('?_modulo=pessoa&_acao=u&idpessoa=<?= $row["idpessoa"] ?>')"></a></td>
											<td>
												<a class="fa fa-trash  cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="excluicontato(<?= $row["idpessoacontato"] ?>)" title="Excluir"></a>
											</td>
										</tr>
									<?
									} //while($row = mysqli_fetch_array($res)){
									?>
								</table>

							<?
							} //if($rownum1>0){
							?>
							<table>
								<tr>
									<td>Nome:</td>
									<td colspan="3">
										<input type="text" name="pessoacontato" cbvalue="pessoacontato" value="" style="width: 40em;">
									</td>
								</tr>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	<? } ?>
	<!--div class="row">
    <div class="col-md-12">
	<div class="panel panel-default">
	<div class="panel-heading">Arquivos Anexos</div>
	<div class="panel-body">
	    <div class="cbupload" title="Clique ou arraste arquivos para cá" style="width:100%;height:100%;">
		    <i class="fa fa-cloud-upload fonte18"></i>
	    </div>
	</div> 
	</div>
    </div>
    </div-->

<?
}
?>

<div id="repeteev" style="display: none">
	<div class="interna2" style="background-color:#ccc; margin-top: 6px !important; margin: 3px;">
		<div class="row">
			<div class="col-md-12">
				<div class="panel panel-default">
					<div class="panel-body">
						<table>
							<tr>
								<td>
									<input id="rhevento_idpessoa" type="hidden" value="">
								</td>
							</tr>
						</table>
						<div class="modal-body">
							<?
							$sql = "select idrhtipoevento,evento from rhtipoevento where (flgmanual = 'Y' or flgfixo='Y') and status='ATIVO' order by evento";
							$res = d::b()->query($sql) or die("Erro ao carregar eventos sql=" . $sql);
							while ($row = mysqli_fetch_assoc($res)) {
							?>

								<div class="row" style="padding: 2px;">
									<span style="background-color: #337ab7; margin-top: 2px;" class="list-group-item btn btn-light">
										<a class="selectTipo pointer" id="eventoTipo13" style="color: #FFF; font-size: 16px; text-align: center; width: 100%; padding: 5px" onclick="criaEventoP(<?= $row['idrhtipoevento'] ?>)"><?= $row['evento'] ?>
										</a>
									</span>
								</div>

							<?
							}
							?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Historico salario -->
<div id="historico" style="display: none">
<table class="table table-hover">
<?
	if($_1_u_pessoa_idpessoa !=''){
		$sql="SELECT p.nomecurto,c.idpessoa, c.contrato, c.salario, c.insalubridade, c.unimedmens, c.parcela, c.parcelas, c.qtddependente, c.vlrdependente, c.tipopagamento, c.observacaore, c.horaini, c.horafim, c.periodo, c.acao
		,c.alteradopor,c.alteradoem 
		FROM laudo.colaboradorhistorico as c
		join pessoa as p on (p.idpessoa=c.idpessoa)
		where c.aba='RH' and c.idpessoa=$_1_u_pessoa_idpessoa";  
			
		$res = d::b()->query($sql) or die("A consulta de histórico falhou!!! : ". mysql_error() . "<p>SQL: $sql");
		$qtdv=mysqli_num_rows($res);
	}

	if($qtdv > 0 && array_key_exists("rhfolha", getModsUsr("MODULOS")) == 1){
		?>      
		<thead>          
			<tr> 
				<th scope="col">Pessoa</th>
				<th scope="col">Alteração</th>		   
				<th scope="col">Regime</th>
				<th scope="col">Salário</th>		   
				<th scope="col">Insalubridade</th>
				<th scope="col">Unimed</th>		   	   
				<th scope="col">Obs</th>		   
				<th scope="col">Hora inicio</th>
				<th scope="col">Hora fim</th>		   
				<th scope="col">Por</th>
				<th scope="col">Em</th>
			</tr> 
		</thead>
  		<tbody>
		<?
		while($row=mysqli_fetch_assoc($res)){
			if($row['acao']=="i"){
				$acao = "Inserção";
			}
			if($row['acao']=="d"){
				$acao = "Remoção";
			}
			if($row['acao']=="u"){
				$acao = "Atualização";
			}
			?>
				<tr> 
					<td ><?=$row['nomecurto']?></td> 
					<td ><?=$acao?></td> 
					<td ><?=$row['contrato']?></td> 
					<td ><?=$row['salario']?></td> 
					<td ><?=$row['insalubridade']?></td> 
					<td ><?=$row['unimedmens']?></td> 
					<td ><?=$row['observacaore']?></td> 
					<td ><?=$row['horaini']?></td> 
					<td ><?=$row['horafim']?></td> 
					<td ><?=$row['alteradopor']?></td> 
					<td ><?=dmahms($row['alteradoem'])?></td> 
				</tr>
			</tbody>
			<?
		}
	}
?>
	 </table>
</div>

<!-- Historico Conselho / Area / Dep / Setor -->
<div id="historicosetor" style="display: none">
<table class="table table-hover">
		<?
		$sql = "SELECT p.nomecurto, qry.*
                FROM (
                    -- CONSELHO
                    SELECT h.idpessoa, h.acao, 'Conselho' as tipoobjeto, c.conselho as descricao, h.alteradopor, h.alteradoem
                    FROM colaboradorhistorico as h
                    JOIN sgconselho c ON(c.idsgconselho = h.objeto AND h.tipoobjeto = 'sgconselho')
                    -- AREA
                    UNION
                    SELECT h.idpessoa, h.acao, 'Área' as tipoobjeto, a.area as descricao, h.alteradopor, h.alteradoem
                    FROM colaboradorhistorico as h
                    JOIN sgarea a ON(a.idsgarea = h.objeto AND h.tipoobjeto = 'sgarea')
                    -- DEP
                    UNION
                    SELECT h.idpessoa, h.acao, 'Departamento' as tipoobjeto, sgdep.departamento as descricao, h.alteradopor, h.alteradoem
                    FROM colaboradorhistorico as h
                    JOIN sgdepartamento sgdep ON(sgdep.idsgdepartamento = h.objeto AND h.tipoobjeto = 'sgdepartamento')
                    UNION
                    -- SETOR
                    SELECT h.idpessoa, h.acao, 'Setor' as tipoobjeto, s.setor as descricao, h.alteradopor, h.alteradoem
                    FROM colaboradorhistorico as h
                    JOIN sgsetor s ON(s.idsgsetor = h.objeto AND h.tipoobjeto = 'sgsetor')
                ) as qry
                JOIN pessoa as p on (p.idpessoa = qry.idpessoa)
                WHERE qry.idpessoa = $_1_u_pessoa_idpessoa;";

		$res = d::b()->query($sql) or die("A consulta de histórico falhou!!! : " . mysql_error(d::b()) . "<p>SQL: $sql");
		$qtdv = mysqli_num_rows($res);
		if ($qtdv > 0) {
		?>
			<thead>
				<tr>
					<th scope="col">Pessoa</th>
					<th scope="col">Tipo</th>
					<th scope="col">Descrição</th>
					<th scope="col">Alteração</th>
					<th scope="col">Por</th>
					<th scope="col">Em</th>
				</tr>
			</thead>
			<tbody>
				<?
				while ($row = mysqli_fetch_assoc($res)) {
					if ($row['acao'] == "i") {
						$acao = "Inserção";
					}
					if ($row['acao'] == "d") {
						$acao = "Remoção";
					}
					if ($row['acao'] == "u") {
						$acao = "Atualização";
					}
				?>
					<tr>
						<td><?= $row['nomecurto'] ?></td>
						<td><?= $row['tipoobjeto'] ?></td>
						<td><?= $row['descricao'] ?></td>
						<td><?= $acao ?></td>
						<td><?= $row['alteradopor'] ?></td>
						<td><?= dmahms($row['alteradoem']) ?></td>
					</tr>
			</tbody>
	<?
				}
			}
	?>
	</table>
</div>

<div id="historicocargo" style="display: none">
	<table class="table table-hover">
		<?
		if ($_1_u_pessoa_idpessoa != "") {
			$sql = "SELECT p.nomecurto,c.acao,sgc.cargo,c.alteradopor,c.alteradoem 
	FROM colaboradorhistorico as c
	join pessoa as p on (p.idpessoa=c.idpessoa)
    join sgcargo as sgc on (sgc.idsgcargo=c.cargo)
	where p.idpessoa=$_1_u_pessoa_idpessoa";

			$res = d::b()->query($sql) or die("A consulta de histórico falhou!!! : " . mysql_error() . "<p>SQL: $sql");
			$qtdv = mysqli_num_rows($res);
		}
		if ($qtdv > 0) {
		?>
			<thead>
				<tr>
					<th scope="col">Pessoa</th>
					<th scope="col">Cargo</th>
					<th scope="col">Alteração</th>
					<th scope="col">Por</th>
					<th scope="col">Em</th>
				</tr>
			</thead>
			<tbody>
				<?
				while ($row = mysqli_fetch_assoc($res)) {
					if ($row['acao'] == "i") {
						$acao = "Inserção";
					}
					if ($row['acao'] == "d") {
						$acao = "Remoção";
					}
					if ($row['acao'] == "u") {
						$acao = "Atualização";
					}
				?>
					<tr>
						<td><?= $row['nomecurto'] ?></td>
						<td><?= $row['cargo'] ?></td>
						<td><?= $acao ?></td>
						<td><?= $row['alteradopor'] ?></td>
						<td><?= dmahms($row['alteradoem']) ?></td>
					</tr>

			<?
				}
			}
			?>
			</tbody>
	</table>
</div>

<?
if (!empty($_1_u_pessoa_idpessoa)) { // trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_pessoa_idpessoa; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
$tabaud = "pessoa"; //pegar a tabela do criado/alterado em antigo
require 'viewCriadoAlterado.php';
?>

<?

function listaSgPessoaFuncao($id)
{
	//  echo $_1_u_pessoa_idpessoa;
	//global $_1_u_sgcargo_idsgcargo;
	//die();
	global $JSON, $_1_u_pessoa_idpessoa, $_1_u_pessoa_idsgcargo;
	$s = "
		select 
			
			
			scf.idsgcargofuncao,
			sf.funcao,
			psf.idpessoasgfuncao,
			psf.status
			
			
		from 
			sgcargofuncao scf
		join 
			sgfuncao sf on sf.idsgfuncao = scf.idsgfuncao
		left join
			pessoasgfuncao psf on (psf.idsgfuncao = scf.idsgfuncao and psf.idpessoa = " . $_1_u_pessoa_idpessoa . ")
		where
			scf.status = 'ATIVO' and
			scf.idsgcargo = '" . $_1_u_pessoa_idsgcargo . "'
		order by
			sf.funcao;";

	$rts = d::b()->query($s) or die("listaSgPessoaFuncao: " . mysql_error(d::b()));

	echo "<table class='table-hover'><tbody>";
	while ($r = mysqli_fetch_assoc($rts)) {
		$title = "Vinculado por: " . $r["criadopor"] . " - " . dmahms($r["criadoem"], true);
		if ($r["status"] == 'ATIVO') {
			$opacity = '';
			$cor = 'verde hoververde';
		} else {
			$opacity = 'opacity';
			$cor = 'vermelho hoververmelho ';
		}
		echo "<tr id=" . $r["idpessoasgfuncao"] . " class='" . $opacity . "'><td>" . $r["funcao"] . "</td><td><i class='fa fa-check-circle-o $cor' title='" . $r["i"] . "' status='" . $r["status"] . "' idpessoasgfuncao='" . $r["idpessoasgfuncao"] . "' onclick='AlteraStatus(this)'></i></td></tr>";
	}
	echo "</tbody></table>";
}
?>
<?
function getJSetorvinc()
{
	global $JSON, $_1_u_pessoa_idpessoa;
	$s = "select * FROM (
		select a.idsgsetor AS id
				,CONCAT('SETOR - ',a.setor) AS name
				,'sgsetor' AS tipo
				from sgsetor a
				where a.status = 'ATIVO'
				" . getidempresa('a.idempresa', 'sgsetor') . "
					and not exists(
						SELECT 1
						FROM pessoaobjeto v
						where 1 " . getidempresa('v.idempresa', 'sgsetor') . "
							and v.idpessoa= " . $_1_u_pessoa_idpessoa . " 						
							and v.idobjeto=a.idsgsetor	
							and v.tipoobjeto = 'sgsetor'								
					)
		union
		select a.idsgarea AS id
				,CONCAT('AREA - ',a.area) AS name
				,'sgarea' AS tipo
				from sgarea a
				where a.status = 'ATIVO'
				" . getidempresa('a.idempresa', 'sgarea') . "
					and not exists(
						SELECT 1
						FROM pessoaobjeto v
						where 1 " . getidempresa('v.idempresa', 'sgsetor') . "
							and v.idpessoa= " . $_1_u_pessoa_idpessoa . " 						
							and v.idobjeto=a.idsgarea	
							and v.tipoobjeto = 'sgarea'								
					)
		union
		select a.idsgdepartamento AS id
				,CONCAT('DEPART. - ',a.departamento) AS name
				,'sgdepartamento' AS tipo
				from sgdepartamento a
				where a.status = 'ATIVO'
				" . getidempresa('a.idempresa', 'sgdepartamento') . "
					and not exists(
						SELECT 1
						FROM pessoaobjeto v
						where 1 " . getidempresa('v.idempresa', 'sgdepartamento') . "
							and v.idpessoa= " . $_1_u_pessoa_idpessoa . " 						
							and v.idobjeto=a.idsgdepartamento	
							and v.tipoobjeto = 'sgdepartamento'								
					)
				) a
				order by tipo, name desc";

	$rts = d::b()->query($s) or die("getJSetorvinc: " . mysql_error(d::b()));

	$arrtmp = array();
	$i = 0;
	while ($r = mysqli_fetch_assoc($rts)) {
		$arrtmp[$i]["value"] = $r["id"];
		$arrtmp[$i]["label"] = $r["name"];
		$arrtmp[$i]["tipo"] = $r["tipo"];
		$i++;
	}

	return $JSON->encode($arrtmp);
}

$jSgsetorvinc = "null";

if (!empty($_1_u_pessoa_idpessoa)) {
	$jSgsetorvinc = getJSetorvinc();
}

$jImmsgconf = "null";
if (!empty($_1_u_pessoa_idpessoa)) {
	$jImmsgconf = getJimmsgconf();
}

function getDependente()
{
	$sqlDependente = "SELECT p.idpessoa, p.nome
						FROM pessoa p
					   WHERE p.idtipopessoa = 115 
					     AND NOT EXISTS (SELECT 1 FROM pessoaobjeto po WHERE po.idobjeto = p.idpessoa AND po.tipoobjeto = 'pessoa')
						 AND p.status <> 'INATIVO'
					ORDER BY p.nome;";
	$resDependente = d::b()->query($sqlDependente) or die("getDependente: Erro: " . mysql_error(d::b()) . "\n" . $sqlDependente);

	$arDependente = array();
	while ($r = mysqli_fetch_assoc($resDependente)) {
		//monta 2 estruturas json para finalidades (loops) diferentes
		$arDependente[$r["idpessoa"]]["nome"] = $r["nome"];
	}
	return $arDependente;
}

$arrDp = getDependente();
$jDependente = $JSON->encode($arrDp);

function getRhTipoEvento($idpessoa)
{
	$sqlDpRh = "SELECT idrhtipoevento, evento, po.idpessoaobjeto, po.idpessoa	
				  FROM rhtipoevento rt LEFT JOIN pessoaobjeto po ON po.idobjeto = rt.idrhtipoevento AND po.tipoobjeto = 'rhtipoevento' AND po.idpessoa = '" . $idpessoa . "' 
				 WHERE rt.flgdependente = 'Y' 
				 ORDER BY evento;";
	$resDpRh = d::b()->query($sqlDpRh) or die("A Consulta de Conta itens falhou :" . mysql_error() . "<br>Sql:" . $sqlDpRh);

	return $resDpRh;
}

?>

<script>
	<?if(!empty($_1_u_pessoa_idpessoa)){?>
        if( $("[name=_1_u_pessoa_idpessoa]").val() ){
            $(".cbupload").dropzone({
                idObjeto: $("[name=_1_u_pessoa_idpessoa]").val()
                ,tipoObjeto: 'idpessoa'
                ,idPessoaLogada: '<?=$_SESSION["SESSAO"]["IDPESSOA"]?>'
                ,caminho: 'upload/'
            });

            <?
                $sqlcert = "SELECT * FROM novocertificadodigital WHERE idobjeto = ".$_1_u_pessoa_idpessoa." AND objeto = 'pessoa'";
                $rescert = d::b()->query($sqlcert) or die("A Consulta do certificado falhou :".mysql_error()."<br>Sql:".$sqlcert);
                $ncert = mysql_num_rows($rescert);
                if($ncert > 0){
                    $arrcerttmp = array();
                    $rcert = mysqli_fetch_assoc($rescert);
                    $arrcerttmp["id"]=$rcert["idnovocertificadodigital"];
                    $arrcerttmp["nome"]=$rcert["nome"];
                    $arrcerttmp["caminho"]=$rcert["caminho"];
                    $arrcerttmp = $JSON->encode($arrcerttmp);
                }else{
                    $arrcerttmp = 0;
                }
            ?>
        }
    <? } ?>

	function ChecaPIS(pis) {

		var ftap = "3298765432";
		var i;
		total = 0;
		resto = 0;
		numPIS = 0;
		strResto = "";

		numPIS = pis;
		if (numPIS == "" || numPIS == null) {
			return false;
		}
		for (i = 0; i <= 9; i++) {
			resultado = (numPIS.slice(i, i + 1)) * (ftap.slice(i, i + 1));
			total = total + resultado;
		}
		resto = (total % 11)
		if (resto != 0) {
			resto = 11 - resto;
		}
		if (resto == 10 || resto == 11) {
			strResto = resto + "";
			resto = strResto.slice(1, 2);
		}
		if (resto != (numPIS.slice(10, 11))) {
			return false;
		}
		return true;
	}

	function validaPis(vthis) {
		pis = vthis.value;
		if (pis.length > 10) {
			if (!ChecaPIS(pis)) {
				$(vthis).attr('name', '');
				$(vthis).css('border', '1px solid red');
				$('#alertapisinvalido').removeClass('hidden');
				$('#alertapisvalido').addClass('hidden');
			} else {
				if (pis.length = 11) {
					$(vthis).attr('name', '_1_' + CB.acao + '_pessoa_pis');
				} else {
					$(vthis).attr('name', '');
				}
				$(vthis).attr('name', '_1_' + CB.acao + '_pessoa_pis');
				$(vthis).css('border', '1px solid green');
				$('#alertapisvalido').removeClass('hidden');
				$('#alertapisinvalido').addClass('hidden');
			}
		} else {
			if (pis.length != 11) {
				$(vthis).attr('name', '');
			}
			$(vthis).css('border', '');
			$('#alertapisinvalido').addClass('hidden');
			$('#alertapisvalido').addClass('hidden');
		}
	}

	function addcoment() {
		let post = '';
		let comercial = '';
		$("#comentario :input").each(function(index, value) {
			if ($("[name='_100_i_modulocom_descricao']").val() != "") {
				post += comercial + $(value).attr("name") + "=" + $(value).val();
				comercial = "&";
			}
		});
		if (post != '') {
			CB.post({
				objetos: post,
				parcial: true
			});
		} else {
			$("[name='_100_i_modulocom_descricao']").focus()
		}

	}

	function editacoment(tdcomentario) {
		post = '';
		comercial = '';
		$(tdcomentario).children().each(function(index, value) {
			if ($(value).attr("name") !== undefined) {
				post += comercial + $(value).attr("name") + "=" + $(value).val();
				comercial = "&";
			}
		});
		CB.post({
			objetos: post,
			parcial: true
		});

	}

	function atualizacomentario(vthis, idcomentario) {
		CB.post({
			objetos: {
				"_100_u_modulocom_idmodulocom": idcomentario,
				"_100_u_modulocom_descricao": $(vthis).val()
			},
			parcial: true
		});
	}
	CB.on('prePost', function(inParam) {
		if(inParam !== undefined){
			if(inParam.objetos){
				if(inParam.objetos.statustipo){
					if(inParam.objetos.statustipo == "CANCELADO"){
						$("#username").removeAttr("vnulo");
					}
				}
			}
		}
		if($("[name$=_status]").val() == "CANCELADO"){
			$("#username").removeAttr("vnulo");
		}
		if ($("[name='_100_i_modulocom_descricao']").val() == "") {
			$("#comentario :input").each(function(index, value) {
				$(value).attr("name", "")
			});
		}
	});

	function botoes(vthis) {

		var td = $(vthis).parent();
		if ($(td).attr('comentario') == 'true' && !$(td).find("#editacomentario").length) {
			$(td).append(`<button 
							onclick="CB.post({objetos:'_ajax_u_modulocom_idmodulocom=${$(vthis).attr('idmodulocom')}&_ajax_u_modulocom_status=INATIVO',parcial:true})" 
							class="btn btn-danger btn-xs fright"
							>
								<i class="fa fa-trash"></i> 
								Excluir
							</button>`);
			$(td).append(`<button id="editacomentario"
							type="button"
							class="btn btn-success btn-xs fright"
							title="Salvar"
							onclick="editacoment(${$(td).attr("id")})"
							>
						<i class="fa fa-check"></i>Salvar
						</button>`);
		}
	}
	// $("textarea").blur(function(e) {
	// 	while ($(this).outerHeight() < this.scrollHeight + parseFloat($(this).css("borderTopWidth")) + parseFloat($(this).css("borderBottomWidth"))) {
	// 		$(this).height($(this).height() + 1);
	// 	};
	// });

	function rhPessoa() {
		CB.modal({
			titulo: "</strong>Histórico de RH</strong>",
			corpo: $("#historico").html(),
			classe: 'sessenta',
		});

	}

	function gerauser(vthis) {

		if (confirm("Deseja gerar o usuario "+$(vthis).val()+" ?")) {
						CB.post({
				objetos: {
					"_1_u_pessoa_idpessoa": $("[name$=_pessoa_idpessoa]").val(),
					"_1_u_pessoa_usuario": $(vthis).val(),
					"_1_u_pessoa_webmailemail": $("[name$=_pessoa_webmailemail]").val(),
					"_1_u_pessoa_idempresa": $("[name$=_pessoa_idempresa]").val(),
				},
				parcial: true
			})
		}
	}

	function cargoPessoa() {
		CB.modal({
			titulo: "</strong>Histórico de Cargo</strong>",
			corpo: $("#historicocargo").html(),
			classe: 'sessenta',
		});

	}

	function historico() {
		CB.modal({
			titulo: "</strong>Histórico</strong>",
			corpo: $("#historicosetor").html(),
			classe: 'sessenta',
		});

	}

	jImmsgconf = <?= $jImmsgconf ?>;

	//Autocomplete de Setores vinculados
	$("#immsgconf").autocomplete({
		source: jImmsgconf,
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
					"_x_i_immsgconfdest_idobjeto": $(":input[name=_1_" + CB.acao + "_pessoa_idpessoa]").val(),
					"_x_i_immsgconfdest_idimmsgconf": ui.item.value,
					"_x_i_immsgconfdest_objeto": 'pessoa'
				},
				parcial: true
			});
		}
	});

	jSgsetorvinc = <?= $jSgsetorvinc ?>;

	//Autocomplete de Setores vinculados
	$("#sgsetorvinc").autocomplete({
		source: jSgsetorvinc,
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
			let objetoPost = {
				"_x_i_pessoaobjeto_idpessoa": $(":input[name=_1_" + CB.acao + "_pessoa_idpessoa]").val(),
				"_x_i_pessoaobjeto_idobjeto": ui.item.value,
				"_x_i_pessoaobjeto_tipoobjeto": ui.item.tipo
			};

			if(ui.item.tipo == 'sgdepartamento' || ui.item.tipo == 'sgarea' || ui.item.tipo == 'sgconselho')
			{
				objetoPost['_x_i_pessoaobjeto_responsavel'] = 'Y';
			}

			CB.post({
				objetos: objetoPost,
				parcial: true
			});
		}
	});

	function showtemplate(intitulo, inid) {
		CB.modal({
			titulo: intitulo,
			corpo: $("#webmailassinatura_" + inid).html(),
			classe: "sessenta"
		});
	}

	function novoobjeto(inobj) {
		CB.post({
			objetos: "_x_i_" + inobj + "_idpessoa=" + $("[name=_1_u_pessoa_idpessoa]").val()
		});

	}

	function novoHorario() {
		let dias = [];
		let idpessoa = $("#template_idpessoa").val();
		let de = $("#template_horaini").val();
		let ate = $("#template_horafim").val();

		$("input:checkbox[name=diahorario]:checked").each(function(key, val) {
			key = key + 1;
			dias['_' + key + '_i_pessoahorario_idpessoa'] = $("#template_idpessoa").val();;
			dias['_' + key + '_i_pessoahorario_periodo'] = $(val).val();
			dias['_' + key + '_i_pessoahorario_horaini'] = $("#template_horaini").val();
			dias['_' + key + '_i_pessoahorario_horafim'] = $("#template_horafim").val();

		});
		console.log($.extend({}, dias))

		CB.post({
			objetos: $.extend({}, dias),
			parcial: true
		});
	}

	function checkChange() {
		i = 0;
		$('[name=pessoahorario]').each((index, el) => {
			if ($(el).is(':checked')) i++;
		});
		if (i > 0) {
			$('#horariosSelecionados').show();
			$('#horariosSelecionadosTexto span').text(i + ' horário(s) selecionado(s)');
		} else {
			$('#horariosSelecionados').hide();
		}
	}
	$('[name=pessoahorario]').on('change', () => {
		checkChange();
	});

	function checkAllHorarios(el) {

		if ($(el).is(':checked')) {
			//$('[name=pessoahorario]').each((index, el)=>{console.log(index, el); $(el).prop('checked', false).attr('checked', 'checked')});
			$('[name=pessoahorario]').prop('checked', true).attr('checked', 'checked');
		} else {
			$('[name=pessoahorario]').prop('checked', false).removeAttr('checked');
		}
		checkChange();
	}

	function excluirVariosHorarios() {
		let dias = [];
		$("input:checkbox[name=pessoahorario]:checked").each(function(key, val) {
			key = key + 1;
			dias['_' + key + '_d_pessoahorario_idpessoahorario'] = $(val).val();
		});
		console.log($.extend({}, dias))

		CB.post({
			objetos: $.extend({}, dias),
			parcial: true
		});

	}

	function excluirHorario(idpessoahorario) {
		$('#tr_horario' + idpessoahorario).fadeOut('ease');
		CB.post({
			objetos: '_ajax_d_pessoahorario_idpessoahorario=' + idpessoahorario,
			refresh: false,
			partial: true
		});
		$('#tr_horario' + idpessoahorario).remove();
	}

	/**
	 * Salva o vinculo que sera removido
	 */

	function inativaobjeto(inid, inobj)
	{
		CB.post({
			objetos: "_x_d_" + inobj + "_id" + inobj + "=" + inid,
			parcial: true
		});
	}

	function retiraevfixo(inid, status) {
		CB.post({
			objetos: "_x_u_rheventopessoa_idrheventopessoa=" + inid + "&_x_u_rheventopessoa_status=" + status,
			parcial: true
		});
	}

	function inseriev(inidev) {
		CB.post({
			objetos: "_x_i_rheventopessoa_idpessoa=" + $("[name=_1_u_pessoa_idpessoa]").val() + "&_x_i_rheventopessoa_idrhevento=" + inidev,
			parcial: true
		});
	}

	if ($("[name=_1_u_pessoa_idpessoa]").val()) {
		$("#avatarFoto").dropzone({
			url: "form/_arquivo.php",
			idObjeto: $("[name=_1_u_pessoa_idpessoa]").val(),
			tipoObjeto: 'pessoa',
			tipoArquivo: 'AVATAR',
			caminho: 'upload/avatar/',
			sending: function(file, xhr, formData) {
				formData.append("idobjeto", this.options.idObjeto);
				formData.append("tipoobjeto", this.options.tipoObjeto);
				formData.append("tipoarquivo", this.options.tipoArquivo);
				formData.append("caminho", this.options.caminho);
			},
			success: function(file, response) {
				this.options.loopArquivos(response);
			},
			init: function() {
				var thisDropzone = this;
				$.ajax({
					url: this.options.url + "?caminho=" + this.options.caminho + "&tipoobjeto=" + this.options.tipoObjeto + "&idobjeto=" + this.options.idObjeto + "&tipoarquivo=" + this.options.tipoArquivo
				}).done(function(data, textStatus, jqXHR) {
					thisDropzone.options.loopArquivos(data);
				})
			},
			loopArquivos: function(data) {
				jResp = jsonStr2Object(data);
				if (jResp.length > 0) {
					nomeArquivo = jResp[jResp.length - 1].nome;
					if (nomeArquivo) {
						$("#avatarFoto").attr("src", "upload/avatar/" + nomeArquivo);
					}
				}
			}
		});
	}

	function novo(inobj) {
		CB.post({
			objetos: "_x_i_" + inobj + "_idpessoa=" + $("[name=_1_u_pessoa_idpessoa]").val(),
			parcial: true
		});

	}

	function novoContrato(inobj){
		CB.post({
			objetos: "_x_i_" + inobj + "_idpessoa=" + $("[name=_1_u_pessoa_idpessoa]").val()+"&_x_i_" + inobj + "_tipo=T&_x_i_" + inobj + "_status=ATIVO" ,
			parcial: true
		});
		
	}

	function apagarcert(inId) {
		CB.post({
			objetos: "_x_d_novocertificadodigital_idnovocertificadodigital=" + inId,
			parcial: true
		});
	}

	function sincronizarbiometria(idfuncionario) {
		$("#idfuncao:hidden").show();
		$.ajax({
			type: "get",
			url: "ajax/sincronizarbiometria.php",
			data: {
				idpessoa: idfuncionario
			},
			success: function() {
				alertAzul("Sincronizado com Sucesso", "", 1000);
			},

			error: function(objxmlreq) {
				alertErro('Erro:<br>' + objxmlreq.status);
			}
		}); //$.ajax
	}

	function modalTransferir(idempresa,idpessoa) {
			
		$oModal = $(`
			<div id="nova_lp">
				<div class="col-md-12">
					<label for="nova_empresa_usuario">Empresa:</label>
					<select id="nova_empresa_usuario">
						<?fillselect("SELECT idempresa,sigla from empresa where status='ATIVO'")?>
					</select>
				</div>
				<div class="col-md-12" style="text-align: right;margin: 10px 0px 10px 0px;">
					<button onclick="enviaAjaxDuplicarUsuario(${idpessoa})" class="btn btn-success btn-sm">
						Transferir
					</button>
				</div>
			</div>
		`);

		
		$oModal.find("#nova_empresa_usuario option").remove("[value='"+idempresa+"']");

		CB.modal({
			titulo: "</strong>Empresa Destino</strong>",
			corpo: [$oModal],
			classe: 'vinte',
		});	
	}

	function enviaAjaxDuplicarUsuario(idpessoa){debugger
		if(confirm(`Essa movimentação cria um novo registro para o colaborador na empresa selecionada, transferindo todas as informações do usuário atual para esse novo registro. Isso inclui a migração de eventos, caixa de e-mail, e demais dados relevantes.
Após a conclusão do processo, é necessário realizar as configurações para o novo registro, tais como definir as Lps, Setor e Cargo apropriados, assim como proceder com a inativação do registro antigo.
Deseja realmente finalizar a ação?`)){
			$.ajax({
				type: "get",
				url: `ajax/duplicarusuario.php?idpessoaant=${idpessoa}&idempresanovo=${$("#nova_empresa_usuario").val()}`,
				success: function(data) {
					alert(`Para completar a transferência favor copiar comando abaixo e anexar em Suporte Tecnologia:\n`+data);
					// alertAzul("Transferido com Sucesso", "", 1000);
					// window.location.reload();
				},

				error: function(objxmlreq) {
					alertErro('Erro:<br>' + objxmlreq.status);
				}
			}); //$.ajax
		}
	}
	function excluir(tab, inid) {
		if (confirm("Deseja retirar este?")) {
			CB.post({
				objetos: "_x_d_" + tab + "_id" + tab + "=" + inid + "&_idpessoa=" + $("[name='_1_u_pessoa_idpessoa']").val(),
				parcial: true
			});
		}

	}

	function inativa(intab,incampo, inval) {
		CB.post({
			objetos: "_x_u_"+intab+"_status=INATIVO&_x_u_"+intab+"_" + incampo + "=" + inval,
			parcial: true
		});

	}

	function AlteraStatus(vthis) {

		//alert($(vthis).attr('idsgareasetor'));
		var id;
		id = $(vthis).attr('idpessoasgfuncao');
		status = $(vthis).attr('status');
		//alert(status);
		var novostatus, cor, novacor;
		if (status == 'ATIVO') {
			novostatus = 'INATIVO';
			cor = 'verde hoververde';
			novacor = 'vermelho hoververmelho';
			opacity = true;

		} else {
			novostatus = 'ATIVO';
			cor = 'vermelho hoververmelho';
			novacor = 'verde hoververde';
			opacity = false;
		}
		//alert("_x_u_sgareasetor_idsgareasetor="+id+"&_x_u_sgareasetor_status="+novostatus);
		CB.post({
			objetos: "_x_u_pessoasgfuncao_idpessoasgfuncao=" + id + "&_x_u_pessoasgfuncao_status=" + novostatus

				,
			refresh: true,
			msgSalvo: "Status Alterado",
			posPost: function() {
				$(vthis).removeClass(cor);
				$(vthis).addClass(novacor);
				$(vthis).attr('status', novostatus);
				$(vthis).attr('title', novostatus);
				if (opacity) {
					//  alert();
					$('#' + id).addClass('opacity');
					//alert(id);
				} else {
					$('#' + id).removeClass('opacity');
				}
				//removeClass("vermelho hoververmelho").addClass("verde hoververde");
			}
		});

	}

	function AlteraStatusImsg(vthis) {

		var idimmsgconfdest = $(vthis).attr('idimmsgconfdest');
		var status = $(vthis).attr('status');
		var cor, novacor;

		if (status == 'ATIVO') {
			cor = 'verde hoververde';
			novacor = 'vermelho hoververmelho';
			CB.post({
				objetos: "_x_u_immsgconfdest_idimmsgconfdest=" + idimmsgconfdest + "&_x_u_immsgconfdest_status=INATIVO",
				parcial: true,
				msgSalvo: "Status Alterado",
				posPost: function() {
					$(vthis).removeClass(cor);
					$(vthis).addClass(novacor);
				}
			});

		} else {

			cor = 'vermelho hoververmelho';
			novacor = 'verde hoververde';
			CB.post({
				objetos: "_x_u_immsgconfdest_idimmsgconfdest=" + idimmsgconfdest + "&_x_u_immsgconfdest_status=ATIVO",
				parcial: true,
				msgSalvo: "Status Alterado",
				posPost: function() {
					$(vthis).removeClass(cor);
					$(vthis).addClass(novacor);
				}
			});
		}
	}

	function alttipocontato(incampo, inval) {
		CB.post({
			objetos: "_x_u_pessoa_idpessoa=" + $("[name=_1_u_pessoa_idpessoa]").val() + "&_x_u_pessoa_" + incampo + "=" + inval,
			parcial: true
		});

	}

	function confemailpermissao() {
		if (confirm("Permitir que o usuário utilize o WebMail?")) {

			CB.post({
				objetos: "_x_u_pessoa_idpessoa=" + $("[name=_1_u_pessoa_idpessoa]").val() + "&_x_u_pessoa_webmailpermissao=Y",
				parcial: true
					//, refresh: false
					,
				msgSalvo: "Webmail habilitado"
			});
		}
	}

	$("[name='_1_u_pessoa_webmailemail']").on('change', function() {
		if (confirm("Criar email para este usuário?")) {

			var webmailusuario = $(this).attr("user");
			var webmailemail = this.value;
			CB.post({
				objetos: "_mail_u_pessoa_idpessoa=" + $("[name=_1_u_pessoa_idpessoa]").val() + "&_mail_u_pessoa_webmailusuario=" + webmailusuario + "&_mail_u_pessoa_webmailemail=" + webmailemail,
				parcial: true
					//, refresh: false
					,
				msgSalvo: "Email criado"
			});
		}
	})


	function repeteev(indipessoa) {
		var strCabecalho = "</strong>Gerar novo evento</strong>";
		$("#cbModalTitulo").html((strCabecalho));

		var htmloriginal = $("#repeteev").html();
		var objfrm = $(htmloriginal);

		objfrm.find("#rhevento_idpessoa").attr("name", "rhevento_idpessoa");
		objfrm.find("#rhevento_idpessoa").attr("value", indipessoa);


		$("#cbModalCorpo").html(objfrm.html());
		$('#cbModal').modal('show');
	}

	function criaEventoP(inidrhtipoevento) {


		var str = "_1_i_rhevento_idpessoa=" + $("[name=rhevento_idpessoa]").val() +
			"&_1_i_rhevento_idrhtipoevento=" + inidrhtipoevento;
		CB.post({
			objetos: str,
			parcial: true,
			refresh: false,
			posPost: function(resp, status, ajax) {
				if (status = "success") {
					//$("#cbModalCorpo").html("");
					//$('#cbModal').modal('hide');
					abreEvento(CB.lastInsertId);
				} else {
					alert(resp);
				}
			}
		});
	}

	function abreEvento(inid) {
		CB.modal({
			url: "?_modulo=rhevento&_acao=u&idrhevento=" + inid,
			header: "Evento RH"
		});
	}

	function excluicontato(inid) {
		if (confirm("Deseja retirar o contato?")) {
			CB.post({
				objetos: "_x_d_pessoacontato_idpessoacontato=" + inid,
				parcial: true
			});
		}
	}


	function excluiremailvirtual(idemailvirtual) {
		if (confirm("Deseja retirar o email virtual?")) {
			CB.post({
				objetos: "_x_d_emailvirtualconf_idemailvirtualconf=" + idemailvirtual,
				parcial: true
			});
		}
	}

	<? if (!empty($jCont)) { ?>
		jCont = <?= $jCont ?>; // autocomplete cliente

		//mapear autocomplete de clientes
		jCont = jQuery.map(jCont, function(o, id) {
			return {
				"label": o.nome,
				value: id + ""
			}
		});


		//autocomplete de clientes
		$("[name=pessoacontato]").autocomplete({
			source: jCont,
			delay: 0,
			select: function(event, ui) {
				insericontato(ui.item.value);
			},
			create: function() {
				$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
					return $('<li>').append("<a>" + item.label + "</a>").appendTo(ul);

				};
			}
		});
	<? }
	if (!empty($jContf)) { ?>
		jContf = <?= $jContf ?>; // autocomplete cliente

		//mapear autocomplete de clientes
		jContf = jQuery.map(jContf, function(o, id) {
			return {
				"label": o.nome,
				value: id + ""
			}
		});

		//autocomplete de clientes
		$("[name=pessoacontatof]").autocomplete({
			source: jContf,
			delay: 0,
			select: function(event, ui) {
				insericontato(ui.item.value);
			},
			create: function() {
				$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
					return $('<li>').append("<a>" + item.label + "</a>").appendTo(ul);

				};
			}
		});

	<? } ?>

	function insericontato(inid) {

		CB.post({
			objetos: "_x_i_pessoacontato_idcontato=" + $("[name=_1_u_pessoa_idpessoa]").val() + "&_x_i_pessoacontato_idpessoa=" + inid,
			parcial: true
		});
	}
	const _acao = '<?= $_GET["_acao"] ?>';
	$(document).ready(function() {
		//Verifica disponibilidade de cpf/cnpf
		$("#validacpfcnjp").blur(function() {
			var cpfcnpj = $("#validacpfcnjp").val();
			if (cpfcnpj != "" && cpfcnpj != "0" && _acao != '' && _acao != 'u') {

				// GVT - 25/06/2020 - Função assíncrona para verificar a disponibilidade do cpf/cnpj
				//                    retorno: 0 = CPF/CNPJ está disponível
				//                    retorno: 1 = CPF/CNPJ já existe no banco de dados
				//                    retorno: -1 = Entrada inválida
				verificacpfcnpj(cpfcnpj).then(retorno => {
					switch (retorno) {
						case "0":
							$("#validacpfcnjp").css("border", "2px solid green");
							break;
						case "1":
							if (!confirm("CPF/CNPJ já cadastrado. Deseja continuar?")) {
								$("#validacpfcnjp").val("");
							} else {
								$("#validacpfcnjp").css("border", "2px solid red");
							}
							break;
						case "-1":
							console.warn("Verifique o valor de retorno da função verificacpfcnpj");
							$("#validacpfcnjp").css("border", "2px solid yellow");
							break;
						default:
							console.warn("Verifique o valor de retorno da função verificacpfcnpj");
							$("#validacpfcnjp").css("border", "2px solid yellow");
							break;
					}
				}).catch(e => {
					console.warn("Verfique a PROMISSE da função verificacpfcnpj " + e);
				});
			} else {
				$("#validacpfcnjp").css("border", "");
			}
		});
	});

	function retiraundneg(inidunidadeobjeto) {
		CB.post({
			objetos: "_x_d_plantelobjeto_idplantelobjeto=" + inidunidadeobjeto
		});
	}

	function inseriundneg(inidund) {
		CB.post({
			objetos: "_x_i_plantelobjeto_idobjeto=" + $("[name=_1_u_pessoa_idpessoa]").val() + "&_x_i_plantelobjeto_idplantel=" + inidund + "&_x_i_plantelobjeto_tipoobjeto=pessoa"
		});
	}
	$(document).ready(function() {
		$('input[type="text"]').each(function() {
			var val = $(this).val().replace(',', '.');
			$(this).val(val);
		});
	}); //# sourceURL=<?= $_SERVER["SCRIPT_NAME"] ?>_rodape

	//Lista Dependentes
	<? if (!empty($jDependente)) { ?>
		jDependente = <?= $jDependente ?>; // autocomplete dependente

		//mapear autocomplete de clientes
		jDependente = jQuery.map(jDependente, function(o, id) {
			return {
				"label": o.nome,
				value: id + ""
			}
		});

		//autocomplete de clientes
		$("[name=dependente]").autocomplete({
			source: jDependente,
			delay: 0,
			select: function(event, ui) {
				inserirPessoaObjeto($("[name=_1_u_pessoa_idpessoa]").val(), ui.item.value, 'pessoa');
			},
			create: function() {
				$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
					return $('<li>').append("<a>" + item.label + "</a>").appendTo(ul);
				};
			}
		});
	<? } ?>

	function inserirPessoaObjeto(idpessoa, inid, tipoobjeto) {
		CB.post({
			objetos: "_x_i_pessoaobjeto_idpessoa=" + idpessoa + "&_x_i_pessoaobjeto_idobjeto=" + inid + "&_x_i_pessoaobjeto_tipoobjeto=" + tipoobjeto + "&_idpessoa=" + $("[name='_1_u_pessoa_idpessoa']").val(),
			parcial: true
		});
	}

	function removerPessoaObjeto(tab, idpessoaobjetoarray, idpessoaobj) {
		var obj = "";
		var i = 1;
		for (let idpessoaobjeto of idpessoaobjetoarray) {
			obj += "_" + i + "_d_pessoaobjeto_idpessoaobjeto=" + idpessoaobjeto;
			obj += "&";
			i++;
		}
		obj += "_" + i + "_d_pessoaobjeto_idpessoaobjeto=" + idpessoaobj + "&_idpessoa=" + $("[name='_1_u_pessoa_idpessoa']").val();

		CB.post({
			objetos: obj,
			parcial: true
		});
	}

	$("#modaldependente").click(function() {
		var idempresa = (getUrlParameter("_idempresa")) ? "&_idempresa=" + getUrlParameter("_idempresa") : '';
		CB.modal({
			url: '?_modulo=pessoa&_acao=i&idfuncionario=<?= $_1_u_pessoa_idpessoa ?>&tipopessoa=dependente' + idempresa,
			header: "Pessoa",
			aoFechar: function(inPar) {
				alertAzul('Aguarde! Atualizando Lista Dependentes.');
				vUrl = CB.urlDestino + window.location.search;
				CB.loadUrl({
					urldestino: vUrl
				});
			}
		});
		$('#example').removeClass('is-visible');
	});

	function atualizaGrauParentesco(vthis, idpessoaobj) {
		debugger
		CB.post({
			objetos: "_agp_u_pessoaobjeto_idpessoaobjeto=" + idpessoaobj + "&_agp_u_pessoaobjeto_tipo=" + vthis.value,
			parcial: true
		});
	}

	function altacesso(inchecked) {
		checked=inchecked?"N":"Y";
		CB.post({
			objetos: "_x_u_pessoa_idpessoa=" + $("[name=_1_u_pessoa_idpessoa]").val() + "&_x_u_pessoa_acesso=" + checked,
			parcial: true
		});

	}

	function altrep(inselect){
		acao=inselect.value==""?"d":"i";
		idrep=inselect.getAttribute("idrep");;
		idempresa=inselect.getAttribute("idempresa");
		idreppessoa=inselect.getAttribute("idreppessoa");
		if(acao=="d" && idreppessoa.length > 0){
			CB.post({
				objetos: "_x_d_reppessoa_idreppessoa=" + idreppessoa,
				parcial: true
			});
		}else{
			CB.post({
				objetos:  "_x_i_reppessoa_idrep=" + idrep + 
							"&_x_i_reppessoa_idempresa=" + idempresa +
							"&_x_i_reppessoa_idpessoa=" + $("[name=_1_u_pessoa_idpessoa]").val()
				,parcial: true
			});
		}
	}

/*
	// Tornar vinculo a um Cons / Area / Dep / Setor obrigatorio
	CB.prePost = function()
	{
		if(!$('.vinculos').children().length)
		{
			alertAtencao('Vínculo com Conselho, Área, Departamento ou Setor obrigatório!');

			return false;
		}
	}
	*/
</script>
<script src="inc/js/dom-to-image/dom-to-image.min.js"></script>