<?
require_once("../inc/php/validaacesso.php");

if($_POST){
	require_once("../inc/php/cbpost.php");
}
/*
 * $pagvaltabela: tabela principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "rotaorigem";
$pagvalcampos = array(
	"idrotaorigem" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from rotaorigem where idrotaorigem = ".$_GET["idrotaorigem"];
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");
?>
<style>

</style>
<div class="row">
    <div class="col-md-12">
    <div class="panel panel-default" >
	<div class="panel-heading">	
			<table>
			<tr> 
				<td></td> 
				<td>
					<input name="_1_<?=$_acao?>_rotaorigem_idrotaorigem" type="hidden"	value="<?=$_1_u_rotaorigem_idrotaorigem?>"	readonly='readonly'	>
				</td> 

				<td>UF Origem:</td> 
				<td>
					<select name="_1_<?=$_acao?>_rotaorigem_uf" id="iduf" vnulo>
					<option value=""></option>
						<?fillselect("select 'AC','AC' union
							select 'AL','AL' union
							select 'AM','AM' union
							select 'AP','AP' union
							select 'BA','BA' union
							select 'CE','CE' union
							select 'DF','DF' union
							select 'ES','ES' union
							select 'GO','GO' union
							select 'MA','MA' union
							select 'MG','MG' union
							select 'MS','MS' union
							select 'MT','MT' union
							select 'PA','PA' union
							select 'PB','PB' union
							select 'PE','PE' union
							select 'PI','PI' union
							select 'PR','PR' union
							select 'RJ','RJ' union
							select 'RN','RN' union
							select 'RO','RO' union
							select 'RR','RR' union
							select 'RS','RS' union
							select 'SC','SC' union
							select 'SE','SE' union
							select 'SP','SP' union
							select 'TO','TO' union
							select 'XX','XX'",$_1_u_rotaorigem_uf);?></select>
				</td> 
		
				<td>Origem:</td> 
				<td>
				
				<?
			if(empty($_1_u_rotaorigem_codcidade)){
			?>	
			       		<select name="_1_<?=$_acao?>_rotaorigem_codcidade" id="idcidade" vnulo>
							<option value=""></option>
						</select>
			<?		
			}elseif(!empty($_1_u_rotaorigem_codcidade)){
			?>       
			       	<select name="_1_<?=$_acao?>_rotaorigem_codcidade" id="idcidade" vnulo>
						<?fillselect("SELECT codcidade,cidade 
						FROM nfscidadesiaf 
						where uf ='".$_1_u_rotaorigem_uf."';"
						,$_1_u_rotaorigem_codcidade);?>
					 </select>	
			<?
			}
			?>
				
				</td> 
			</tr>
			</table>
	
	
	</div>
    </div>
    </div>
</div>
<?if(!empty($_1_u_rotaorigem_idrotaorigem)){?>
<div class="row">
    <div class="col-md-12">
    <div class="panel panel-default" >
	<div class="panel-heading">				
	    <table class="normal">
					<tr class="respreto"  >
					    <td>Novo Destino</td>
					    <td>		<select name="rotapara_uf" id="idufpara" >
								<option value=""></option>
								<?fillselect("select 'AC','AC' union
									select 'AL','AL' union
									select 'AM','AM' union
									select 'AP','AP' union
									select 'BA','BA' union
									select 'CE','CE' union
									select 'DF','DF' union
									select 'ES','ES' union
									select 'GO','GO' union
									select 'MA','MA' union
									select 'MG','MG' union
									select 'MS','MS' union
									select 'MT','MT' union
									select 'PA','PA' union
									select 'PB','PB' union
									select 'PE','PE' union
									select 'PI','PI' union
									select 'PR','PR' union
									select 'RJ','RJ' union
									select 'RN','RN' union
									select 'RO','RO' union
									select 'RR','RR' union
									select 'RS','RS' union
									select 'SC','SC' union
									select 'SE','SE' union
									select 'SP','SP' union
									select 'TO','TO' union
									select 'XX','XX'");?>
								</select>
						</td>
						<td>
								<select  class="size15" name="rotapara_codcidade" id="idcidadepara" >
								<option value="" ></option>
								</select>
						</td>
						<td>
						    <a class="fa fa-plus-circle pointer fade hoververde fa-1x" title="Nova Rota" onclick="inserir()"></a>
						</td>	
					</tr>
					<tr>
						<td colspan="50">&nbsp;</td>
					</tr>
					</table>	
	</div>
	<div class="panel-body">
	

		
			
		

	    <table style="width: 100%" class="table table-striped planilha">
					<tr >
						<th>Cidade</th>
						<th colspan="2">Via</th>
						<th title="Dias Úteis">Prazo Entrega</th>
						<th>Obs.</th>
						<th>#</th>
					</tr>	
			<?
				$sql1 = "SELECT r.idrotapara,r.idpessoa,r.uf,c.cidade,p.nome,r.obs, r.prazoentrega
						FROM nfscidadesiaf c,rotapara r left join pessoa p on( p.idpessoa=r.idpessoa)
				        where r.idrotaorigem =".$_1_u_rotaorigem_idrotaorigem."
						and c.codcidade = r.codcidade order by c.cidade";
				$res1 = d::b()->query($sql1) or die("A Consulta  dos materiais solicitados falhou :".mysqli_error(d::b())."\n<br>SQL: ".$sql1);
				
				$qtdrows1= mysqli_num_rows($res1);
				$i=3;
					while ($row1 = mysqli_fetch_assoc($res1)) {
						 $vtotaadic= $vtotaadic + $row1["valor"];
						 $i=$i+1;
			?>
					<tr   >
					  	<td   align="center"><?=$row1["cidade"]?> - <?=$row1["uf"]?></td>
					 	<td  class="nowrap">
							<input name="_<?=$i?>_u_rotapara_idrotapara" type="hidden" value="<?=$row1["idrotapara"]?>">
							<select name="_<?=$i?>_u_rotapara_idpessoa" >
									<option value=""></option>
								<?fillselect("select idpessoa,nome 
								from pessoa 
								where idtipopessoa = 11
								and status = 'ATIVO'
								order by nome",$row1['idpessoa']);?>		
							</select>
							
						</td>
						<td>
						    <?if(!empty($row1['idpessoa'])){?>
							<a class="fa fa-bars pointer hoverazul" title="Editar Transportadora" onclick="janelamodal('?_modulo=pessoa&_acao=u&idpessoa=<?=$row1['idpessoa']?>')"></a>
						    <?}?>
						</td>
						<td style="width: 120px;">						
						    <input style="text-align:center;" name="_<?=$i?>_u_rotapara_prazoentrega" value="<?=$row1["prazoentrega"]?>" title="Dias Úteis">													
						</td>
					 	<td>						
						    <textarea   name="_<?=$i?>_u_rotapara_obs"  onblur="CB.post();" ><?=$row1["obs"]?></textarea>															
						</td>	
						<td>
						    <a class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="excluir(<?=$row1["idrotapara"]?>)" alt="Excluir Rota"></a>
						</td>	 	
					 </tr>
			<?		
					}	
			?>
					
				</table>
			
					
		<?}?>
	</div>
    </div>
    </div>
</div>
<?
if(!empty($_1_u_rotaorigem_idrotaorigem)){// trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_rotaorigem_idrotaorigem; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
	$tabaud = "rotaorigem"; //pegar a tabela do criado/alterado em antigo
	require 'viewCriadoAlterado.php';
?>

<script >

function preenchecidade(){
	
    $("#idcidade").html("<option value=''>Procurando....</option>");
	
    $.ajax({
	type: "get",
	url : "ajax/buscacodcidade.php",
	data: { uf : $("#iduf").val() },
	success: function(data){
		$("#idcidade").html(data);
	},
	error: function(objxmlreq){
		alert('Erro:<br>'+objxmlreq.status); 
	}
    })//$.ajax
}

function preenchecidadepara(){
	
    $("#idcidadepara").html("<option value=''>Procurando....</option>");	
    $.ajax({
	type: "get",
	url : "ajax/buscacodcidade.php",
	data: { uf : $("#idufpara").val() },
	success: function(data){
		$("#idcidadepara").html(data);
	},
	error: function(objxmlreq){
		alert('Erro:<br>'+objxmlreq.status);
	}
    })//$.ajax
}

$().ready(function() {
    $("#iduf").change(function(){
	    preenchecidade();
    }
    );
    $("#idufpara").change(function(){
	    preenchecidadepara();
    }
    );	
});

function inserir(){
    CB.post({
	objetos: "_1_i_rotapara_idrotaorigem="+$("[name=_1_u_rotaorigem_idrotaorigem]").val()+"&_1_i_rotapara_uf="+$("[name=rotapara_uf]").val()+"&_1_i_rotapara_codcidade="+$("[name=rotapara_codcidade]").val()
	,parcial:true
    });
} 
function excluir(idrotapara){
    CB.post({
	objetos: "_1_d_rotapara_idrotapara="+idrotapara
	,parcial:true
    });
} 
autosize(document.querySelectorAll('textarea'));

//MAF: Implementar funcao para buscar CEP em base de dados correios ou local
/*function validacoescep(){
	validacep(document.getElementById('cep').value);
	document.getElementById('numero').focus();
}*/
    
//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>