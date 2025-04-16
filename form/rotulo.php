<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if($_POST){
	include_once("../inc/php/cbpost.php");
}
/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "rotulo";
$pagvalcampos = array(
	"idrotulo" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from rotulo where idrotulo = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php")
?>
<div class="row ">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading ">		
            <table>
            <tr> 
                <td>ID:</td> 
                <td>
                    <input name="_1_<?=$_acao?>_rotulo_idrotulo" type="hidden" id="idrotulo2"	value="<?=$_1_u_rotulo_idrotulo?>"	readonly='readonly'>
                    <label class="alert-warning"><?=$_1_u_rotulo_idrotulo?></label>		
                </td> 	

                <td>Rótulo:</td>
                <td ><input type="text" name="_1_<?=$_acao?>_rotulo_rotulo" vnulo size="40" value="<?=$_1_u_rotulo_rotulo?>"  onchange="this.form.submit()"></td>

                <td>Impressão:</td> 
                <td>	
                    <select name="_1_<?=$_acao?>_rotulo_status" >
                                    <?fillselect("select 'PENDENTE','Pendente' union select 'IMPRESSO','Impresso'",$_1_u_rotulo_status);?>		
                    </select>	
                </td>              
                <td align="right">
                    <td><i title="Imprimir" class="fa fa-print pull-right fa-lg cinza hoverazul" onclick="janelamodal('report/rotulo.php?idrotulo=<?=$_1_u_rotulo_idrotulo?>')"></i></td>

            </tr>
            </table>
        </div>
        <?if(!empty($_1_u_rotulo_idrotulo)){?>
        <div class="panel-body"> 
            <div class="row">
                <div class="col-md-6">
                <div class="panel panel-default">  
                <div class="panel-heading">Novo Rótulo</div>
                <div class="panel-body"> 
                   <table class="hidden" id="modeloNovoRotulo">
			<tr >
                            <td>Cliente:</td>
                            <td>
				<select id="cliente1" class="cliente1" name="" >
					<option></option>
					<?fillselect("select idpessoa,nome from pessoa where idtipopessoa in(1,2,5,7,12) and status ='ATIVO' order by nome");?>
				</select>
                            </td>
                        </tr>
                        <tr>
                            <td>Contato:</td>
                            <td>
				<select id="contato1" name="" >
					<option value=""></option>			
				</select>
                            </td>
                        </tr>
                        <tr>
                            <td>Endereço:</td>
                            <td>
				<select  id="endereco1" name="">
					<option value=""></option>
				</select>
                            </td>
                        </tr>
                        <tr>
                            <td>Obs.:
				<input id="idrotulo" type="hidden" value="<?=$_1_u_rotulo_idrotulo?>">
                            </td>
                            <td>
				<textarea name="" id="obs"  rows="6" cols="40"></textarea>
				
                            </td>
                        </tr>
			</table>
                    	<div>
				<i id="novorotulo" class="fa fa-plus-circle fa-2x verde btn-lg pointer" onclick="novoRotulo()" title="Inserir novo Rótulo"></i>
                        </div>


                </div>  
                </div>
                </div>
                <div class="col-md-6">
                <div class="panel panel-default">  
                <div class="panel-heading">Adicionar Rótulo por Pedido</div>
                    <table>
                        <tr>
                            <td>Pedido:</td>
                            <td>
                                <select  name="idpedidocp" id="idpedidocp" onchange="CB.post();">
                                <option value=""></option>
                                        <?fillselect("select idpedido, concat(dma(prazo),'-',pt.nome,'-',p.nome) as nome
                                                                        from pedido pd,pessoa p,pessoa pt
                                                                        where pd.status = 'PENDENTE'
                                                                        and pd.idpessoa = p.idpessoa
                                                                        and pd.idendereco is not null
                                                                        and pd.idcontato is not null
                                                                        and not exists (select 1 from rotuloresultado r where r.idpedido = pd.idpedido and r.idrotulo =".$_1_u_rotulo_idrotulo.")
                                                                        and pd.idtransportadora = pt.idpessoa order by prazo");?>		
                                </select>
                            </td>
                        </tr>
                    </table>                
                <div class="panel-body"> 
                </div>
                </div>
                </div>    
            </div>
        </div>  
        <?}?>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-10">
<?
if(!empty($_1_u_rotulo_idrotulo)){
$sql="select * from rotuloresultado where idendereco is not null  and  idrotulo =".$_1_u_rotulo_idrotulo;
 $res=d::b()->query($sql) or die("Erro ao buscar os rotulos sql:".$sql);
 $l=0;
 $i=3;
 	while($row=mysqli_fetch_assoc($res)){
 		$l=$l+1;
                $i=$i+1;
 		$sql1="select e.logradouro,e.cep,e.endereco,e.numero,e.complemento,e.bairro,c.cidade,c.uf,e.obsentrega
 					from endereco e left join nfscidadesiaf c on (c.codcidade = e.codcidade )
				where  e.idendereco=".$row['idendereco'];
 		$res1=d::b()->query($sql1) or die("Erro ao busca endereco sql:".$sql1);
 		$qtd1=mysqli_num_rows($res1);
 		$row1=mysqli_fetch_assoc($res1);
?>

                <div class="col-md-6">
                <div class="panel panel-default">                 
                <div class="panel-body">
                    <div class="divdest">Destinatário  
                        <i class="fa fa-files-o cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="duplicar(this,<?=$row['idrotulo']?>)"  obs=="<?=$row['obs']?>" idpedido="<?=$row['idpedido']?>" idendereco="<?=$row['idendereco']?>" idrotulo="<?=$row['idrotulo']?>" idcontato="<?=$row['idcontato']?>" idpessoa="<?=$row['idpessoa']?>"></i>
                    </div>                   
                    <div class="divcli">
                    <a href="javascript:janelamodal('cadcliente.php?acao=u&idpessoa=<?=$row['idpessoa']?>')"><font color="Blue" style="font-weight: bold;"><?=traduzid("pessoa","idpessoa","nome",$row['idpessoa'])?></font></a></div>

                    <div class="divcli">
                    <?=traduzid("pessoa","idpessoa","razaosocial",$row['idpessoa'])?></div>
                    <div class="divcli">CPF/CNPJ: 
                    <?	$cnpj=traduzid("pessoa","idpessoa","cpfcnpj",$row['idpessoa']);
                            $cnpj=formatarCPF_CNPJ($cnpj,true);
                            echo($cnpj);
                            ?>
                    </div>
                    <div class="divend">End: <?if(!empty($row1['logradouro'])){echo($row1['logradouro'].". ");} echo($row1['endereco'].", ".$row1['numero']); if(!empty($row1['complemento'])){echo(" - ".$row1['complemento']);}?></div>

                    <?if(!empty($row1['bairro'])){?>
                    <div class="divbairro">Bairro: <?echo($row1['bairro'])?></div>
                    <?}?>
                    <div class="divcep"><?if(!empty($row1['cep'])){?>CEP: <?echo($row1['cep']."        ");} if(!empty($row1['cidade'])){echo($row1['cidade']);}if(!empty($row1['uf'])){echo("-".$row1['uf']);}?></div>
	<?
	if(!empty($row['idcontato'])){
?>	
                    <div class="divac">A/C. <?=traduzid("pessoa","idpessoa","nome",$row['idcontato'])?></div>
<?
	}
        /*
	$sql2="select * from pessoa where idpessoa = ".$row['idpessoa'];
	$res2=d::b()->query($sql2) or die("Erro ao buscar observação sql=".$sql2);
	$row2=mysqli_fetch_assoc($res2);
        */
?>	
                    <div class="divac"><?=nl2br($row1['obsentrega'])?></div>
                    <div class="divac">
                            <input name="_<?=$i?>_u_rotuloresultado_idrotuloresultado" type="hidden" value="<?=$row['idrotuloresultado']?>">
                            <textarea  name="_<?=$i?>_u_rotuloresultado_obs"    rows="6" cols="40" style=font-size:medium;   ><?=nl2br($row['obs'])?></textarea>
                    </div>
		<?if(!empty($row['idpedido'])){?>
                    <div class="divac">Pedido:
                    <a href="javascript:janelamodal('pedido.php?acao=u&idpedido=<?=$row['idpedido']?>')"><font color="Blue" style="font-weight: bold;"><?=$row['idpedido']?></font></a></div>
	<?}?>
                    <div>
                        <i class="fa fa-trash fa-2x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="excluir(<?=$row['idrotuloresultado']?>)" alt="Excluir"></i>
                    </div>
                </div>
                </div> 
               </div>
<?
	}//while($row=mysqli_fetch_assoc($res)){
}//if(!empty($_1_u_rotulo_idrotulo)){
?>

    </div>
</div>
<?
if(!empty($_1_u_rotulo_idrotulo)){// trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_rotulo_idrotulo; // trocar p/ cada tela o id da tabela
	require '../form/viewAssinaturas.php';
}
	$tabaud = "rotulo"; //pegar a tabela do criado/alterado em antigo
	require '../form/viewCriadoAlterado.php';
?>

<script>
function excluir(inid){	
    if(confirm("Deseja retirar o rotulo?")){		
        CB.post({
        objetos: "_x_d_rotuloresultado_idrotuloresultado="+inid
        });
    }
}   

function duplicar(vthis,vidrotulo){
    var idpessoa = $(vthis).attr("idpessoa");
    var obs = $(vthis).attr("obs");
    var idpedido = $(vthis).attr("idpedido");
    var idendereco = $(vthis).attr("idendereco");
    var idcontato = $(vthis).attr("idcontato");

    
    CB.post({
        objetos: "_x_i_rotuloresultado_idrotulo="+vidrotulo+"&_x_i_rotuloresultado_idpessoa="+idpessoa+"&_x_i_rotuloresultado_idpedido="+idpedido+"&_x_i_rotuloresultado_idendereco="+idendereco+"&_x_i_rotuloresultado_idcontato="+idcontato+"&_x_i_rotuloresultado_obs="+obs
    });
}
    
    
function novoRotulo(){

    $("#cliente1").attr("name","_999_i_rotuloresultado_idpessoa");
    $("#contato1").attr("name","_999_i_rotuloresultado_idcontato");
    $("#endereco1").attr("name","_999_i_rotuloresultado_idendereco");
    $("#idrotulo").attr("name","_999_i_rotuloresultado_idrotulo");
    $("#obs").attr("name","_999_i_rotuloresultado_obs");
  
    $('#modeloNovoRotulo').removeClass( "hidden" );;
   
}

$(document).ready(function() {

	$("#cliente1").change(function(){
			preenchecontato();
			preencheendereco();
	});	

});


function preenchecontato(){	
	$("#contato1").html("<option value=''>Procurando....</option>");
	//alert($("#idpessoa").val());	
	$.ajax({
			type: "get",
			url : "ajax/buscacontato.php",
			data: { idpessoa : $("#cliente1").val() },
			success: function(data){
				$("#contato1").html(data);
			},
			error: function(objxmlreq){
				alert('Erro:<br>'+objxmlreq.status); 
			}
		})//$.ajax
}

function preencheendereco(){	
	$("#endereco1").html("<option value=''>Procurando....</option>");
	//alert($("#idpessoa").val());	
	$.ajax({
			type: "get",
			url : "ajax/buscaendereco.php",
			data: { idpessoa : $("#cliente1").val() },
			success: function(data){
				$("#endereco1").html(data);
			},
			error: function(objxmlreq){
				alert('Erro:<br>'+objxmlreq.status); 
			}
		})//$.ajax
}

//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>