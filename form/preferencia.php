<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if ($_POST) {
	include_once("../inc/php/cbpost.php");
}
/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "preferencia";
$pagvalcampos = array(
	"idpreferencia" => "pk"
);
$_idempresa = $_GET['_idempresa'];
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from preferencia where idpreferencia = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

function getClientespref()
{

	$sql = "select * 
	    from pessoa 
	    where idtipopessoa = 2 
	    and status = 'ATIVO'
	    " . getidempresa('idempresa', 'pessoa') . " 
	    and idpreferencia is null order by nome";
	//die($_SESSION["IDPESSOA"]);
	$res = d::b()->query($sql) or die("getClientes: Erro: " . mysqli_error(d::b()) . "\n" . $sql);

	$arrret = array();
	while ($r = mysqli_fetch_assoc($res)) {
		//monta 2 estruturas json para finalidades (loops) diferentes
		$arrret[$r["idpessoa"]]["nome"] = $r["nome"];
	}
	return $arrret;
}

//Recupera os clientes as serem selecionados
$arrCli = getClientespref();
//print_r($arrCli); die;
$jCli = $JSON->encode($arrCli);



?>
<div class="row ">
	<div class="col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<table>
					<tr>
						<td> Título:</td>
						<td>
							<input size="70" name="_1_<?= $_acao ?>_preferencia_titulo" type="text" value="<?= $_1_u_preferencia_titulo ?>">
							<input size="70" name="_1_<?= $_acao ?>_preferencia_idpreferencia" type="hidden" value="<?= $_1_u_preferencia_idpreferencia ?>">
						</td>
						<td> Status:</td>
						<td>
							<select name="_1_<?= $_acao ?>_preferencia_status">
								<? fillselect("SELECT 'ATIVO','ATIVO' UNION SELECT 'INATIVO','INATIVO'", $_1_u_preferencia_status) ?>
							</select>
						</td>
					</tr>
				</table>
			</div>
		</div>
	</div>
</div>
<div class="row ">
	<div class="col-md-6">
		<div class="panel panel-default">
			<div class="panel-heading">Cobrança</div>
			<div class="panel-body">

				<table>

					<tr>
						<td align="right" nowrap>Forma de Venda:</td>
						<td title="Informação obrigatória no lançamento do teste." colspan="5">
							<select title="Informação obrigatória no lançamento do teste." name="_1_<?= $_acao ?>_preferencia_formavenda" vnulo>
								<option value=""></option>
								<? fillselect("SELECT 'direta', 'Direta'
									UNION
									SELECT 'distribuicao', 'Distribuição'
									UNION
									SELECT 'loja', 'Loja'
									UNION
									SELECT 'marketplace', ' Marketplace'
									UNION
									SELECT 'representacao', 'Respresentação'
									UNION
									SELECT 'revenda', 'Revenda'
									UNION
									SELECT 'site', 'Site'", $_1_u_preferencia_formavenda); ?>
							</select>
						</td>
					</tr>

					<tr>
						<td align="right" nowrap>Pedido de Compra:</td>
						<td title="Informação obrigatória no lançamento do teste." colspan="5">
							<select title="Informação obrigatória no lançamento do teste." name="_1_<?= $_acao ?>_preferencia_pedidocp" vnulo>
								<option value=""></option>
								<? fillselect("select 'N','Não' union select 'Y','Sim'", $_1_u_preferencia_pedidocp); ?>
							</select>
						</td>
					</tr>
					<tr>
						<td align="right" nowrap>Declaração S. Nacional:</td>
						<td colspan="5">
							<select name="_1_<?= $_acao ?>_preferencia_decsimplesn" vnulo>
								<option value=""></option>
								<? fillselect("select 'Y','Sim'
							     union select 'N','Não'", $_1_u_preferencia_decsimplesn); ?>
							</select>
						</td>
					</tr>
					<tr>
						<td align="right">Pagamento:</td>
						<td>
							<select name="_1_<?= $_acao ?>_preferencia_idformapagamento">
								<? fillselect("SELECT f.idformapagamento, CONCAT(e.sigla,' - ', f.descricao)
								FROM formapagamento f JOIN empresa e ON e.idempresa = f.idempresa
							   WHERE f.status = 'ATIVO' AND f.credito = 'Y' AND f.idempresa = " . CB::idempresa() . "
							ORDER BY f.descricao", $_1_u_preferencia_idformapagamento); ?>
							</select>
						</td>
					</tr>
					<!--
		<tr>
		    <td align="right">Agência:</td>
		    <td>
		    <select name="_1_<?= $_acao ?>_preferencia_idagencia"  >
			    <? fillselect("select idagencia,agencia from agencia where status = 'ATIVO'   order by ord", $_1_u_preferencia_idagencia); ?>
			     </select>	
		    </td>
		</tr>
		<tr>	 
		    <td align="right">Forma Pgto:</td> 
		    <td><select  name="_1_<?= $_acao ?>_preferencia_formapagtovenda">
		    <? fillselect("select 'BOLETO','Boleto' union select 'DEPOSITO','Depósito'", $_1_u_preferencia_formapagtovenda); ?>		</select>
		    </td>
		</tr> 
                -->
					<tr>
						<td align="right">Prazo Pgto:</td>
						<td><input name="_1_<?= $_acao ?>_preferencia_prazopagtovenda" type="text" size="5" value="<?= $_1_u_preferencia_prazopagtovenda ?>" vdecimal> Dias</td>
					</tr>
					<tr>
						<td align="right">Parcela(s):</td>
						<td>
							<select name="_1_<?= $_acao ?>_preferencia_parcelavenda">
								<? fillselect("select 1,'1x' union select 2,'2x' union select 3,'3x' union select 4,'4x' union select 5,'5x' union
								    select 6,'6x' union select 7,'7x' union select 8,'8x' union select 9,'9x' union select 10,'10x' union
								    select 11,'11x' union select 12,'12x' union select 13,'13x' union select 14,'14x' union select 15,'15x' union
								    select 16,'16x' union select 17,'17x' union select 18,'18x' union select 19,'19x' union select 20,'20x' union
								    select 21,'21x' union select 22,'22x' union select 23,'23x' union select 24,'24x' union select 25,'25x' union
								    select 26,'26x' union select 27,'27x' union select 28,'28x' union select 29,'29x' union select 30,'30x' union
								    select 31,'31x' union select 32,'32x' union select 33,'33x' union select 34,'34x' union select 35,'35x' union
								    select 36,'36x' union select 37,'37x' union select 38,'38x' union select 39,'39x' union select 40,'40x' union
								    select 41,'41x' union select 42,'42x' union select 43,'43x' union select 44,'44x' union select 45,'45x' union
								    select 46,'46x' union select 47,'47x' union select 48,'48x' union select 49,'49x' union select 50,'50x' union
								    select 51,'51x' union select 52,'52x' union select 53,'53x' union select 54,'54x' union select 55,'55x' union
								    select 56,'56x' union select 57,'57x' union select 58,'58x' union select 59,'59x' union select 60,'60x'", $_1_u_preferencia_parcelavenda); ?>
							</select>
						</td>
					</tr>
					<tr>
						<td align="right">Intervalo:</td>
						<td><input name="_1_<?= $_acao ?>_preferencia_intervalovenda" type="text" style=width:10px" value="<?= $_1_u_preferencia_intervalovenda ?>" vdecimal> Dias</td>
					</tr>
					<tr>
						<td align="right">Mostrar Testes Mesmo CNPJ:</td>
						<td>
							<select name="_1_<?= $_acao ?>_preferencia_mesmocnpj">
								<? fillselect("select 'N','Não' union select 'Y','Sim'", $_1_u_preferencia_mesmocnpj); ?>
							</select>
						</td>
					</tr>
					<tr>
						<td align="right">Observação Pgto:</td>
						<td><textarea name="_1_<?= $_acao ?>_preferencia_obsvenda" rows="4" cols="50"><?= $_1_u_preferencia_obsvenda ?></textarea></td>
					</tr>
				</table>
			</div>
		</div>
	</div>

	<div class="col-md-6">
		<div class="panel panel-default">
			<div class="panel-heading">Requisitos do Cliente</div>
			<div class="panel-body">

				<table>
					<tr>
						<td nowrap align="right">OBS - (NF Serviço)</td>
						<td><textarea style="width: 410px; height: 46px;" name="_1_<?= $_acao ?>_preferencia_observacaonf"><?= $_1_u_preferencia_observacaonf ?></textarea></td>
					</tr>
					<tr>
						<td nowrap align="right">OBS - (NF Produto)</td>
						<td><textarea class="caixa" style="width: 410px; height: 46px; " name="_1_<?= $_acao ?>_preferencia_observacaonfp"><?= $_1_u_preferencia_observacaonfp ?></textarea></td>
					</tr>
					<!--
		    <tr>
		      <td nowrap align="right">OBS - (Pedido)</td>
		      <td><textarea class="caixa" style="width: 410px; height: 46px;"  name="_1_<?= $_acao ?>_preferencia_obspedido"><?= $_1_u_preferencia_obspedido ?></textarea></td>	     
		    </tr>
                    -->
					<tr>
						<td nowrap align="right">OBS - (Inf - NFe)</td>
						<td><textarea class="caixa" style="width: 410px; height: 46px; " name="_1_<?= $_acao ?>_preferencia_obsinfnfe"><?= $_1_u_preferencia_obsinfnfe ?></textarea></td>
					</tr>
					<tr>
						<td nowrap align="right">OBS - (Registro)</td>
						<td><textarea class="caixa" style="width: 410px; height: 46px;" name="_1_<?= $_acao ?>_preferencia_observacaore"><?= $_1_u_preferencia_observacaore ?></textarea></td>
					</tr>
					<tr>
						<td nowrap align="right">OBS - (Logística)</td>
						<td><textarea class="caixa" style="width: 410px; height: 46px;" name="_1_<?= $_acao ?>_preferencia_obslogistica"><?= $_1_u_preferencia_obslogistica ?></textarea></td>
					</tr>
					<!--
		    <tr>
		      <td nowrap align="right">OBS - (Rótulo)</td>
		      <td><textarea class="caixa" style="width: 410px; height: 46px;"  name="_1_<?= $_acao ?>_preferencia_obs" ><?= $_1_u_preferencia_obs ?></textarea></td>
		    </tr>
                    -->
				</table>

			</div>
		</div>
	</div>

</div>






<div class="row ">
	<div class="col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">Viagem</div>
			<div class="panel-body">
				<table>
					<tr>
						<td nowrap align="right">OBS - Viagem</td>
						<td><textarea style="width: 897px; height: 46px;" name="_1_<?= $_acao ?>_preferencia_obs1"><?= $_1_u_preferencia_obs1 ?></textarea></td>
					</tr>
					<tr>
						<td nowrap align="right">OBS - Material Viagem</td>
						<td><textarea style="width: 897px; height: 46px;" name="_1_<?= $_acao ?>_preferencia_obs2"><?= $_1_u_preferencia_obs2 ?></textarea></td>
					</tr>
				</table>
			</div>
		</div>
	</div>
</div>

<div class="row ">
	<div class="col-md-12">
		<?
		if (!empty($_1_u_preferencia_idpreferencia)) {

			$sql = "select 		
			nome,idpessoa			
			from pessoa p
			where p.status='ATIVO'
			and  p.idpreferencia =" . $_1_u_preferencia_idpreferencia . " order by nome";

			$res = d::b()->query($sql) or die("A Consulta falhou :" . mysqli_error() . "<br>Sql:" . $sql);
			//die($sql);
			$rownum1 = mysqli_num_rows($res);
			//if($rownum1>0){
		?>

			<div class="row ">
				<div class="col-md-12">
					<div class="panel panel-default">
						<div class="panel-heading">Clientes Vinculados</div>
						<div class="panel-body">
							<table class="table table-striped planilha">
								<tr>
									<th>Nome</th>
									<th></th>
									<th></th>
								</tr>
								<?
								while ($row = mysqli_fetch_assoc($res)) {
								?>
									<tr class="res">
										<td nowrap><?= $row["nome"] ?></td>
										<td><a class="fa fa-bars pointer hoverazul" title="Empresa" onclick="janelamodal('?_modulo=pessoa&_acao=u&idpessoa=<?= $row["idpessoa"] ?>')"></a></td>
										<td><a class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="retirarpreferencia(<?= $row["idpessoa"] ?>)" Title="Retirar preferàªncia"></a></td>
									</tr>
								<?
								} //while($row = mysqli_fetch_array($res)){
								?>
								<tr>
									<td>
										<input type="text" name="preferencia_idpessoa" cbvalue="<?= $preferencia_idpessoa ?>" value="" style="width: 40em;" placeholder="Selecione para adicionar um cliente">
									</td>
									<td></td>
									<td></td>
								</tr>
							</table>
						</div>
					</div>
				</div>
			</div>
		<? } ?>
	</div>
</div>
<?
if (!empty($_1_u_preferencia_idpreferencia)) { // trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_preferencia_idpreferencia; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
$tabaud = "preferencia"; //pegar a tabela do criado/alterado em antigo
require 'viewCriadoAlterado.php';
?>
<script>
	autosize(document.querySelectorAll('textarea'));

	jCli = <?= $jCli ?>; // autocomplete cliente
	//mapear autocomplete de clientes
	jCli = jQuery.map(jCli, function(o, id) {
		return {
			"label": o.nome,
			value: id + "",
			"tipo": o.tipo
		}
	});

	//autocomplete de clientes
	$("[name*=preferencia_idpessoa]").autocomplete({
		source: jCli,
		delay: 0,
		select: function() {
			console.log($(this).cbval());
			inserirpreferencia($(this).cbval());
		},
		create: function() {
			$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
				return $('<li>').append("<a>" + item.label + "</a>").appendTo(ul);
			};
		}
	});
	// FIM autocomplete cliente



	function inserirpreferencia(inid) {
		CB.post({
			objetos: "_1_u_pessoa_idpreferencia=" + $("[name=_1_u_preferencia_idpreferencia]").val() + "&_1_u_pessoa_idpessoa=" + inid,
			parcial: true
		});
	}

	function retirarpreferencia(inid) {

		CB.post({
			objetos: "_1_u_pessoa_idpessoa=" + inid + "&_1_u_pessoa_idpreferencia=null",
			parcial: true
		});

	}




	//o comentario abaixo faz com que este pedaço de script apareça na aba 'sources' do inspetor do google chrome
	//# sourceURL=<?= $_SERVER["SCRIPT_NAME"] ?>_rodape
</script>