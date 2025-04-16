<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if($_POST){
	include_once("../inc/php/cbpost.php");
}

$pagvaltabela = "empresa";
$pagvalmodulo=$_GET['_modulo'];
$pagvalcampos = array(
	"idmatriz" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from empresa where idempresa = '#pkid'";

/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

?>

<style>
	#colaborador-label {
		position: relative;
	}
	#colaborador-label:after {
		color: #949494;
		content:"\f002";
		font-family: FontAwesome;
		position: absolute;
		top: 6px;
		left: 10px;
	}
</style>

<div class="row">
    <div class="col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<table>
					<tr> 
						<td align="right">Matriz:</td> 
						<td>
							<label class="idbox"><?=$_1_u_empresa_nomefantasia?></label>
							<input name="matrizconf_idmatriz" type="hidden" value="<?=$_1_u_empresa_idempresa?>" readonly='readonly'>                  
						</td>          
					</tr>
				</table>
			</div>
			<div class="panel-body">
				<div class="col-md-12">
					<table>
						<tr>
							<td>Empresa:</td>
							<td>	
								<select name="matriz_idempresa" onchange="insEmpresa(this)"> 
									<option value=""> Selecione para relacionar</option>
									<?fillselect("SELECT e.idempresa, e.empresa
													FROM empresa e
												   WHERE e.status='ATIVO'
										 AND NOT EXISTS (SELECT 1 FROM matrizconf m
														  WHERE m.idmatriz = '$_1_u_empresa_idempresa' AND m.idempresa = e.idempresa)
												ORDER BY e.empresa");?>
								</select>
							</td>
							<td>
								Colaborador
							</td>
							<td>
								<input name="pessoas<?=$idempresa?>" cbvalue="" value="" type="text" id="pessoas<?=$idempresa?>" class="ui-autocomplete-input size20" autocomplete="off">
								<? $jPessoasMatrizPrincipal = getPessoas(); ?>
							</td>
							<td>
								<label id="colaborador-label">
									<input type="text" style="padding-left: 25px;" placeholder="Buscar um colaborador" id="pessoa_nome"/>
								</label>
							</td>
						</tr>
					</table>
				</div>
				<br /><br /><br />

				<div id="corpo_modal" class="panel panel-default">
					<?
					$sqlMatriz = "SELECT m.idmatrizconf, e.empresa, e.idempresa
									FROM empresa e JOIN matrizconf m ON m.idempresa = e.idempresa
									WHERE m.idmatriz = '$_1_u_empresa_idempresa' AND e.status = 'ATIVO'
								ORDER BY e.empresa";
					$resMatriz = d::b()->query($sqlMatriz) or die("Erro ao buscar as unidades sql=".$sqlMatriz);
					$jPessoasMatriz = "null";
					while($rowMatriz = mysqli_fetch_assoc($resMatriz))
					{
						if(!empty($rowMatriz['idmatrizconf']))
						{	
							$idempresa = $rowMatriz['idempresa'];
							?>
							<div class="panel-heading" data-toggle="collapse" href="#clmatrizempresa<?=$rowMatriz['idmatrizconf']?>">
								<span style="font-size: 10px;  font-weight: bold;  color: gray;">Empresa <?=$rowMatriz['empresa']?> &nbsp;
								<i title="Remover Empresa <?=$rowMatriz['empresa']?>" class="fa fa-trash pointer hoververmelho cinza" onclick="excluir('matrizconf', 'idmatrizconf', <?=$rowMatriz['idmatrizconf']?>)"></i></span>
							</div>
							<br>
							<div class="row collapse" style="margin: 5px 0px;" id="clmatrizempresa<?=$rowMatriz['idmatrizconf']?>">
								<div class="col-md-12">
									<div class="col-md-6">
										Permissão para Colaborador <?=$rowMatriz['empresa']?><br />
										<input name="pessoas<?=$idempresa?>" cbvalue="" value="" type="text" id="pessoas<?=$idempresa?>" class="ui-autocomplete-input size20" autocomplete="off">
									</div>
								</div>
								<div class="col-md-12">
									<?
									$sqlMatrizPessoa = "SELECT mp.idmatrizobj, p.nome, p.idpessoa
														FROM matrizpermissao mp JOIN pessoa p ON p.idpessoa = mp.idpessoa
														WHERE mp.idmatriz = '$_1_u_empresa_idempresa' AND mp.idempresa = '$idempresa'
													ORDER BY p.nome";
									$resMatrizPessoa = d::b()->query($sqlMatrizPessoa) or die("Erro ao buscar as Pessoas Matriz sql=".$sqlMatrizPessoa);
									while($rowMatrizPessoa = mysqli_fetch_assoc($resMatrizPessoa))
									{
										?>
										<div class="col-md-3 pessoa-<?=$rowMatrizPessoa['idpessoa'];?>">
											<i title="Remover Pessoa <?=$rowMatrizPessoa['nome']?>" class="fa fa-trash pointer hoververmelho cinza" onclick="excluir('matrizpermissao', 'idmatrizobj', <?=$rowMatrizPessoa['idmatrizobj']?>)"></i>
											<span style="font-size: 11px;"><?=$rowMatrizPessoa['nome']?></span>
											<a id="cadcontatos" class="fa fa-bars pointer hoverazul" title="Colaborador" onclick="janelamodal('?_modulo=funcionario&_acao=u&idpessoa=<?=$rowMatrizPessoa['idpessoa'];?>')"></a>
											<?
											if(empty(getIdlpMatriz($rowMatrizPessoa['idpessoa'],$idempresa))){ ?>
												<i class="fa fa-warning vermelho" title="Sem permissão LP para a empresa <?=$rowMatriz['empresa']?>"></i>
											<? } ?>
										</div>
										<?
									}
									?>
								</div>
							</div>
							<? 
							$jPessoasMatriz = getPessoas($idempresa);
							?>
							<script>
								jPessoasMatriz<?=$idempresa?> = <?=$jPessoasMatriz?>;
								//Autocomplete de funcionarios vinculados
								$("#pessoas<?=$idempresa?>").autocomplete({
									source: jPessoasMatriz<?=$idempresa?>,delay: 0
									,create: function(){
										$(this).data('ui-autocomplete')._renderItem = function (ul, item) 
										{
											lbItem = item.label;							
											return $('<li>')
												.append('<a>' + lbItem + '</a>')
												.appendTo(ul);
										};
									}
									,select: function(event, ui){
										CB.post({
											objetos: {
												"_x_i_matrizpermissao_idmatriz": $("[name=matrizconf_idmatriz").val(),
												"_x_i_matrizpermissao_idempresa": <?=$idempresa?>,
												"_x_i_matrizpermissao_idpessoa": ui.item.value
											}
											,parcial: true
										});

									}
								});
							</script>
							<?
						}
					}
					?>
				</div>
			</div>
		</div>
	</div>
</div>

<?
function getPessoas($idempresa = null)
{
	global $JSON, $_1_u_empresa_idempresa;
	
	if(!empty($idempresa)){
		$notExists = "AND NOT EXISTS (SELECT 1 FROM matrizpermissao mp WHERE mp.idpessoa = p.idpessoa AND mp.idempresa = '$idempresa' and mp.idmatriz = $_1_u_empresa_idempresa )";
	} else {
		$notExists = "AND NOT EXISTS (SELECT 1 FROM matrizpermissao mp WHERE mp.idpessoa = p.idpessoa and mp.idmatriz = $_1_u_empresa_idempresa)";
	}
	$sqlPessoa = "SELECT p.idpessoa, p.nome
	                FROM pessoa p JOIN objempresa oe ON oe.idobjeto = p.idpessoa
	               WHERE p.status = 'ATIVO'
                     AND oe.empresa = '$_1_u_empresa_idempresa' AND oe.objeto = 'pessoa' 
					 $notExists
                ORDER BY p.nome;";
	$resPessoa = d::b()->query($sqlPessoa) or die("Erro ao consultar Pessoa Matriz: ".$sqlPessoa);

	if(mysqli_num_rows($resPessoa) > 0)
	{
		$i = 0;
		$arr = array();
		while($rowPessoa = mysqli_fetch_assoc($resPessoa))
		{
			$arrtmp[$i]["value"] = $rowPessoa["idpessoa"];
			$arrtmp[$i]["label"]= $rowPessoa["nome"];
			$i++;
		}
		$arr = $JSON->encode($arrtmp);
	}else{
		$arr = 0;
	}

	return $arr;
}
?>
<script>
    function insEmpresa(vthis){
	    if(confirm("Deseja realmente relacionar esta empresa a Matriz?"))
        {		
		    CB.post({
			    objetos: "_x_i_matrizconf_idempresa="+$(vthis).val()+"&_x_i_matrizconf_idmatriz="+$("[name=matrizconf_idmatriz").val(),
				parcial: true
		    });
	    }
    }

    function excluir(tab, idtab, inid){
        if(confirm("Deseja remover este Registro?"))
        {		
            CB.post({
                objetos: "_x_d_"+tab+"_"+idtab+"="+inid
                ,parcial: true
            });
        }    
    }

	jPessoasMatrizPrincipal = <?=$jPessoasMatrizPrincipal?>;
	//Autocomplete de funcionarios vinculados
	$("#pessoas").autocomplete({
		source: jPessoasMatrizPrincipal,
		delay: 0,
		create: function(){
			$(this).data('ui-autocomplete')._renderItem = function (ul, item) 
			{
				lbItem = item.label;							
				return $('<li>')
					.append('<a>' + lbItem + '</a>')
					.appendTo(ul);
			};
		}
		,select: function(event, ui){
			CB.post({
				objetos: {
					"_1_i_matrizconf_idmatriz": $("[name=matrizconf_idmatriz").val(),
					"_1_i_matrizconf_idempresa": <?=$_1_u_empresa_idempresa?>,
					"_1_i_matrizconf_idpessoa": ui.item.value
				}
				,parcial: true
			});
		}
	});

	$("#pessoa_nome").keyup(function(){
		var texto = $(this).val().toUpperCase();
		$("#corpo_modal div").css("display", "block");
		$("#corpo_modal div").find(".col-md-12 .col-md-3").each(function(index, elem){
			if(elem.innerText.toUpperCase().indexOf(texto) == -1){
				$(elem).css("display", "none");
			}				
		});
	});
</script>