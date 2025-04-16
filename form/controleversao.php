<?
require_once("../inc/php/validaacesso.php");

if($_POST){
	require_once("../inc/php/cbpost.php");
}

//Parà¢metros mandatà³rios para o carbon
$pagvaltabela = "controleversao";
$pagvalcampos = array(
	"idcontroleversao" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from controleversao where idcontroleversao = '#pkid'";

/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
require_once("../inc/php/controlevariaveisgetpost.php");
?>
<style>
.containerversao input{
	display: inline!important;
	background-color: transparent!important;
	border: none!important;
	width: 20px!important;
}
</style>
<div class="col-md-8">
	<div class="panel panel-default">
		<div class="panel-heading">
			<input type="hidden" name="_1_<?=$_acao?>_controleversao_idcontroleversao" value="<?=$_1_u_controleversao_idcontroleversao?>">
			Versão: 
			<label class='alert-warning containerversao'>
				<input type="text" name="_1_<?=$_acao?>_controleversao_versao" value="<?=$_1_u_controleversao_versao?>">
				.
				<input type="text" name="_1_<?=$_acao?>_controleversao_revisao" value="<?=$_1_u_controleversao_revisao?>">
				
			</label>
		</div>
		<div class="panel-body">
			<table>
				<tr>
					<td><label>Título:</label></td>
					<td><input type="text" name="_1_<?=$_acao?>_controleversao_titulo" value="<?=$_1_u_controleversao_titulo?>" size="80"></td>
					<td><label>Tipo:</label></td>
					<td>
						<select name="_1_<?=$_acao?>_controleversao_tipoalteracao">
							<option></option>
<?
							fillselect(array("NM"=>"Novo Módulo","AM"=>"Alteração de Módulo","NF"=>"Novo Framework"),$_1_u_controleversao_tipoalteracao)
?>
						</select>
					</td>
				</tr>
				<tr>
					<td colspan="99">
						<label>Descrição:</label>
						<div id="editor1Container" class="col-md-12 carregando">
	
	<div id="editor1" class="papel transparente"></div>
	<textarea name="_1_<?=$_acao?>_controleversao_descricao" class="hidden"><?=$_1_u_controleversao_descricao?></textarea>
    </div>
					</td>
				</tr>
			</table>
		</div>
	</div>
</div>
<script >
$editor1=$("#editor1");
//Resetar o objeto tinymce para não ficar desabilitado no refresh/reload
if(tinyMCE.editors["editor1"])tinyMCE.editors["editor1"].remove();

//Inicializa Editor
tinymce.init({
	selector: "#editor1"
	,language: 'pt_BR'
	,inline: true /* não usar iframe */
	,toolbar: 'formatselect | removeformat | fontsizeselect | bold | subscript superscript | bullist numlist | table'
	,menubar: false
	,plugins: ['table']
	,fontsize_formats: "8pt 10pt 12pt 14pt 18pt 24pt 36pt"
	,setup: function (editor) {
		editor.on('init', function (e) {
			//Recupera o conteudo do DB
			this.setContent($(":input[name=_1_"+CB.acao+"_controleversao_descricao]").val());
			setTimeout(function(){
				$editor1.removeClass("tranparente").addClass("opaco");
				//$editor1.scrollTop($(":input[name=_1_"+CB.acao+"_controleversao_scrolleditor]").val());
			}, 1000);

		});
	}

});

//Controla o evento scroll para que ele não seja executado imediatamente. Isto evita alteraçàµes oriundas da renderização dos elementos na tela
var scrollWait,
  scrollFinished = () => console.log('finished');
  window.onscroll = () => {
    clearTimeout(scrollWait);
    scrollWait = setTimeout(scrollFinished,500);
  }

//Armazena o scroll vertical do editor wysiwyg
$editor1.on("scroll", function(){

	//$(":input[name=_1_"+CB.acao+"_controleversao_scrolleditor]").val($editor1.scrollTop());	
	console.log($editor1.scrollTop());

});

//Antes de salvar atualiza o textarea
CB.prePost = function(){
	var $editor=tinyMCE.get('editor1');
	if($editor){
		//falha nbsp: oDescritivo.val( tinyMCE.get('diveditor').getContent({format : 'raw'}).toUpperCase());
		$(":input[name=_1_"+CB.acao+"_controleversao_descricao]").val($editor.getContent());
	}
}   

    

//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape	
</script>
<style>
#editor1{
	padding:40px
}
</style>
<?
require_once '../inc/php/readonly.php';
?>