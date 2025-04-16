<?
require_once("../inc/php/validaacesso.php");
require_once(__DIR__."/controllers/nucleo_controller.php");

if($_POST){
	require_once("../inc/php/cbpost.php");
}

//Parà¢metros mandatà³rios para o carbon
$pagvaltabela = "nucleo";
$pagvalmodulo=$_GET['_modulo'];
$pagvalcampos = array(
	"idnucleo" => "pk"
);
$idunidadepadrao = getUnidadePadraoModulo($_GET['_modulo']);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from nucleo where idnucleo = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
require_once("../inc/php/controlevariaveisgetpost.php"); 

$idnucleotipo=$_GET["idnucleotipo"];

if($idnucleotipo=="F" or $_1_u_nucleo_idnucleotipo =="F" and !empty($_1_u_nucleo_rotulonucleotipo)){
	$strnucleo="PRODUTO";
	$strlote="Descrição:";
	$strsituacao ="select 'ATIVO','Ativo' union select 'INATIVO','Inativo'";
	$_1_u_nucleo_idnucleotipo = "F";

}elseif(!empty($_1_u_nucleo_rotulonucleotipo)){
	$idnucleotipo=="G";
	$strnucleo="PRODUTO";
	$strlote="Lote:";
	$strsituacao ="select 'ATIVO','Vivo' union select 'INATIVO','Abatido'";
	$_1_u_nucleo_idnucleotipo="G";
}

if(!empty($_1_u_nucleo_idnucleo)){

 $tipodias = NucleoController::buscarTipoAves($_1_u_nucleo_idnucleo); 

}     
?>

<div class="row">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading">Cadastro de Núcleo </div>
	<div class="panel-body">	
	<table>
	<tr> 
	    <td align="right" >Id Núcleo:</td> 
	    <td>
		<input name="_1_<?=$_acao?>_nucleo_idnucleo" type="hidden" value="<?=$_1_u_nucleo_idnucleo?>" readonly='readonly'>
		<label class="idbox"><?=$_1_u_nucleo_idnucleo?></label>
	    </td>
	</tr>
	<tr> 
	    <td align="right" >Unidade:</td> 
	    <td>
			<label class='alert-warning'><?=traduzid('unidade','idunidade','unidade',$idunidadepadrao)?></label>
		<input name="_1_<?=$_acao?>_nucleo_idunidade" type="hidden" value="<?=$idunidadepadrao?>">
		</td>
	</tr>
	<tr> 
		<td align="right" >Cliente:</td> 
		<td>
<?
if($_acao == "i" and !empty($_GET["idpessoa"])){
	$_1_u_nucleo_idpessoa = $_GET["idpessoa"];
	$_readonly = "readonly='readonly'";
}
?>
		<select name="_1_<?=$_acao?>_nucleo_idpessoa" <?=$_readonly?> onchange="CB.post()" vnulo>
		<option value=""></option>
			<?fillselect(
				NucleoController::toFillSelect(NucleoController::buscarClientesParaNucleo(getidempresa('idempresa','pessoa', true))),
				$_1_u_nucleo_idpessoa);?>
		</select>

		</td>
	</tr>
<?if(!empty($_1_u_nucleo_idnucleo)){

	if($_1_u_nucleo_idnucleotipo=='F' and empty($_1_u_nucleo_rotulonucleotipo)){
		$strnucleo="PRODUTO";
		$strlote="Descrição:";
		$strsituacao ="select 'ATIVO','Ativo' union select 'INATIVO','Inativo'";
	
	}elseif(empty($_1_u_nucleo_rotulonucleotipo)){
		$strnucleo="NUCLEO";
		$strlote="Lote:";
		$strsituacao ="select 'ATIVO','Vivo' union select 'INATIVO','Abatido'";
	}

	
?>
	<tr> 
	    <td align="right" >
		    <select name="_1_<?=$_acao?>_nucleo_rotulonucleotipo" vnulo >
			    <?fillselect(array("NUCLEO"=>"Núcleo","INTEGRACAO"=>"Integração","PRODUTO"=>"Produto","PROP"=>"Propriedade","FASE"=>"Fase","DESCRICAO"=>"Descrição"),$_1_u_nucleo_rotulonucleotipo); ?>
		    </select>
	    </td> 
	    <td>
		    <input 
			    name="_1_<?=$_acao?>_nucleo_nucleo" 
			    type="text" 
			    value="<?=$_1_u_nucleo_nucleo?>" 
			    vnulo>
	    </td>
	</tr>
	<tr>
		<td align="right" >Lote:</td> 
		<td nowrap>
			<input 
				name="_1_<?=$_acao?>_nucleo_lote" 
				type="text" 
				value="<?=$_1_u_nucleo_lote?>" style="display: inline-block; width: 80%">
	<?
		if($_1_u_nucleo_idnucleotipo!='F' && $tipodias["idade"]){
	?>	
		<font color="Red"><b><?=$tipodias["idade"]?> - <?=$tipodias["tipoidade"]?></b></font>
	<?
		}
	?>
		</td>
	</tr>


	<tr cbtiponucleo> 
		<td align="right" >Granja:</td> 
		<td><input name="_1_<?=$_acao?>_nucleo_granja" type="text" value="<?=$_1_u_nucleo_granja?>" ></td> 
	</tr>
	<tr cbtiponucleo> 
		<td align="right" >Unidade Epidemiológica:</td> 
		<td><input name="_1_<?=$_acao?>_nucleo_unidadeepidemiologica" type="text" value="<?=$_1_u_nucleo_unidadeepidemiologica?>"></td> 
	</tr>
	<tr cbtiponucleo>
		<td align="right" >Alojamento:</td> 
		<td><? if (empty($_1_u_nucleo_alojamento) and empty($_1_u_nucleo_idnucleo)){
				$_1_u_nucleo_alojamento = date("d/m/Y H:i:s");
				}
				?>						
				<input name="_1_<?=$_acao?>_nucleo_alojamento"  id ="fdata" type="text" size ="8" value="<?=$_1_u_nucleo_alojamento?>">
		</td> 
	</tr>
	<tr cbtiponucleo> 
		<td align="right" >Espécie/Finalidade:</td> 
		<td>
			<select name="_1_<?=$_acao?>_nucleo_idespeciefinalidade" vnulo>
				<option></option>
				<?fillselect(
					NucleoController::toFillSelect(NucleoController::buscarEspecieFinalidadePorEmpresa(cb::idempresa()))
					,$_1_u_nucleo_idespeciefinalidade);?>		</select>
		</td> 
	</tr> 

	<tr>	
		<td align="right" >Situação:</td>
		<td>
		    <select name="_1_<?=$_acao?>_nucleo_situacao">
			<?fillselect($strsituacao,$_1_u_nucleo_situacao); ?>
		    </select>
		</td>
	</tr>
	<tr>
	    <td class="nowrap">CNPJ/CPF (Proprietário):</td>
	    <td><input class="size10" name="_1_<?=$_acao?>_nucleo_cpfcnpj" type="text" value="<?=$_1_u_nucleo_cpfcnpj?>" ></td>
	</tr>
	<tr>
	    <td align="right"> UF:</td>
	    <td class="nowrap">
		<select class="size4" name="_1_<?=$_acao?>_nucleo_uf" id="uf" title="uf">
			<option value=""></option>
		    <?fillselect(array('AC'=>'AC','AL'=>'AL','AM'=>'AM','AP'=>'AP','BA'=>'BA','CE'=>'CE','DF'=>'DF','ES'=>'ES','GO'=>'GO','MA'=>'MA','MG'=>'MG','MS'=>'MS','MT'=>'MT','PA'=>'PA','PB'=>'PB','PE'=>'PE','PI'=>'PI','PR'=>'PR','RJ'=>'RJ','RN'=>'RN','RO'=>'RO','RR'=>'RR','RS'=>'RS','SC'=>'SC','SE'=>'SE','SP'=>'SP','TO'=>'TO','EX'=>'EX'),$_1_u_nucleo_uf); ?>
		</select>

	    Cidade:

		<?
		if(empty($_1_u_nucleo_uf)){
	?>	
		    <select class="size20" name="_1_<?=$_acao?>_nucleo_cidade" id="cidade" title="Cidade" >
			<option value=""></option>
		    </select>
	      <?		
		}elseif(!empty($_1_u_nucleo_uf)){
	      ?>       
		    <select class="size20" name="_1_<?=$_acao?>_nucleo_cidade" id="cidade" title="Cidade" >
			<option value=""></option>
			<?fillselect(NucleoController::toFillSelect(NucleoController::buscarCidade($_1_u_nucleo_uf))
			,$_1_u_nucleo_cidade);?>
		    </select>	
	<?
		}
	?>   
	    </td>
	</tr>
	<tr>
	  	
	    <td align="right">Secr. Relacionada:</td>
	    <td>
		    <select name="_1_<?=$_acao?>_nucleo_idsecretaria" size="1" style="font-size:11cpx;">
			    <option value=""></option>
				<?$row0 = NucleoController::buscarSecretariaPessoaNucleo($_1_u_nucleo_idpessoa); 
				if(empty($_1_u_nucleo_idsecretaria) and !empty($row0['idsecretaria'])){
					$_1_u_nucleo_idsecretaria=$row0['idsecretaria'];
				}

	$sqltmp = "select idpessoa, nome 
				from pessoa 
				where idtipopessoa = 10
				order by nome
				and status = 'ATIVO'";

	fillselect($sqltmp,$_1_u_nucleo_idsecretaria);
	?>
			    </select>		
		    </td>
	    </tr>
	    <tr>
		    <td align="right">Nº Registro Ofic.:</td>
		    <td><input name="_1_<?=$_acao?>_nucleo_regoficial" type="text" size="8" value="<?=$_1_u_nucleo_regoficial?>"></td> 
	    </tr>
	    <tr>
		    <td align="right">Nº SVO:</td>
		    <td><input name="_1_<?=$_acao?>_nucleo_nsvo" type="text" size="8" value="<?=$_1_u_nucleo_nsvo?>"></td> 
	    </tr>		    
	<tr>
	    <td align="right"  >Obs. Teste(s):</td>
	    <td  nowrap><textarea name="_1_<?=$_acao?>_nucleo_observacaoteste" rows="5" cols="40"><?=$_1_u_nucleo_observacaoteste?></textarea></td>
	 </tr>
	<tr>
		<td align="center">Monitoramento<br> Interno</td> 
		<td><select name="_1_<?=$_acao?>_nucleo_monitoramento"
			 >

			<?fillselect("SELECT 'N','Nao' UNION SELECT 'Y','Sim'",$_1_u_nucleo_monitoramento); ?>
		</select></td>
	</tr>
	  <td align="right"  >Observação:</td>
	    <td  nowrap><textarea name="_1_<?=$_acao?>_nucleo_observacao" rows="5" cols="40"><?=$_1_u_nucleo_observacao?></textarea></td>

	<?
	}
	?> 
	</table>
	</div>
    </div>
    </div>
</div>



<?
if(!empty($_1_u_nucleo_idnucleo)){
    $resreg = NucleoController::buscarAmostrasPorNucleo($_1_u_nucleo_idnucleo);
    $ireg = count($resreg);
    if($ireg>0){
?>
    <div class="row">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading"><b><?=$ireg?></b> Registros Relacionados</div>
	 <div class="panel-body"> 
<?
	$ex = "";
	foreach($resreg as $k => $rreg ){
	    if($rreg["exercicio"]!=$ex){
		if($ex!=""){
			echo "</ul>";
		}
		$ex = $rreg["exercicio"];
		echo "<ul>".$rreg["exercicio"]."";
	    }

?>
	     <li><?=$rreg["idregistro"]?>
		 <a class="fa fa-bars pointer hoverazul" title="Cadstro de Amostra" onclick="janelamodal('?_modulo=amostraaves&_acao=u&idamostra=<?=$rreg["idamostra"]?>')"></a>
	     </li>
	    <?
	}
?>
	</div>
    </div>
    </div>
</div>
<?
    }
}
?>
<?
if(!empty($_1_u_nucleo_idnucleo)){// trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_nucleo_idnucleo; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
	$tabaud = "nucleo"; //pegar a tabela do criado/alterado em antigo
	require 'viewCriadoAlterado.php';
?>
	

<script>
//atribui funcao change ao radio button
/*
$("[name='_1_<?=$_acao?>_nucleo_idnucleotipo']").change(function() {
	toggleTipoNucleo();
});

//executa na primeira vez a funcao para mostrar/esconder campos
toggleTipoNucleo();
*/

//Mostrar ou esconder/limpar os campos referentes a granja ou fabricante
/*
function toggleTipoNucleo(){

	vNTipo = $("[name='_1_<?=$_acao?>_nucleo_idnucleotipo']:checked").val();

	//captura todos os objetos que possuem a propriedade cbtiponucleo para esconder quando for Fabricante e mostrar quando for granja. Isto permite que a validação de campos seja feita somente quando necessario
	if(vNTipo=="G"){
		var objGranja = $("[cbtiponucleo]");
		$("[cbtiponucleo]").find("*").show();
	}else{
		var objGranja = $("[cbtiponucleo]");
		objGranja.find("*").hide().find("input:text").val("");//retira o valor dos inputs tipo texto
		objGranja.find("option:selected").removeAttr('selected')//retira o valor das drops
	}

}
*/
	
function preenchecidade(){
	
	$("#cidade").html("<option value=''>Procurando....</option>");
	
	$.ajax({
			type: "get",
			url : "ajax/buscacidade.php",
			data: { uf : $("#uf").val() },

			success: function(data){
				$("#cidade").html(data);
			},

			error: function(objxmlreq){
				alert('Erro:<br>'+objxmlreq.status); 

			}
		})//$.ajax

}
$().ready(function() {
	 $("#uf").change(function(){
		preenchecidade();
	}
	);
});

//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape

</script>