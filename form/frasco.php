<?
require_once("../inc/php/validaacesso.php");

if($_POST){
	require_once("../inc/php/cbpost.php");
}

$pagvaltabela = "frasco";
$pagvalcampos = array(
	"idfrasco" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from frasco where idfrasco = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");
?>
<div class="col-md-12">
    <div class="panel panel-default" >
        <div class="panel-heading">
	    	<table>
	    		<tr> 		    
					<td>
		   				<input name="_1_<?=$_acao?>_frasco_idfrasco" type="hidden" value="<?=$_1_u_frasco_idfrasco?>" readonly='readonly'					>
					</td> 	   
					<td>Frasco:</td> 
					<td>
						<input name="_1_<?=$_acao?>_frasco_frasco" type="text" value="<?=$_1_u_frasco_frasco?>">
					</td> 
					<td> Status:</td> 
					<td>
						<select name="_1_<?=$_acao?>_frasco_status">
						<?fillselect("select 'ATIVO','Ativo' union select 'INATIVO','Inativo'",$_1_u_frasco_status);?>		
						</select>
					</td> 					
				</tr>	    
	    </table>
	</div>
	<div class="panel-body"> 
	    <table>
			<tr>
				<td align="right">Altura do rótulo:</td>
				<td><input class="size4" name="_1_<?=$_acao?>_frasco_arotulo" type="text"  value="<?=$_1_u_frasco_arotulo?>" ></td>     
				<td align="right" >Altura das Indicações:</td>
				<td><input class="size4"  name="_1_<?=$_acao?>_frasco_aind" type="text"  value="<?=$_1_u_frasco_aind?>" ></td> 	
				<td align="right">Altura da formula:</td>
				<td><input class="size4"  name="_1_<?=$_acao?>_frasco_aformula" type="text"  value="<?=$_1_u_frasco_aformula?>" ></td>              
				<td align="right">Altura do modo de usar:</td>
				<td><input class="size4"  name="_1_<?=$_acao?>_frasco_amodousar" type="text"  value="<?=$_1_u_frasco_amodusar?>" ></td>  
			</tr>
			<tr>
				<td align="right">Largura das informações:</td>
				<td><input class="size4"  name="_1_<?=$_acao?>_frasco_linf" type="text"  value="<?=$_1_u_frasco_linf?>" ></td>		
				<td align="right">Largura do espaço cepas:</td>
				<td><input class="size4"  name="_1_<?=$_acao?>_frasco_lcepas" type="text"  value="<?=$_1_u_frasco_lcepas?>" ></td>  		
				<td align="right">Largura do espaço vazio:</td>
				<td><input class="size4"  name="_1_<?=$_acao?>_frasco_lesp" type="text"  value="<?=$_1_u_frasco_lesp?>" ></td> 
			</tr>
			<tr>
				<td align="right">Altura acima das Cepas:</td>
				<td><input class="size4"  name="_1_<?=$_acao?>_frasco_accepas" type="text"  value="<?=$_1_u_frasco_accepas?>" ></td>   				
				<td align="right">Altura entre Cepas e Descrição:</td>
				<td><input class="size4"  name="_1_<?=$_acao?>_frasco_acepas" type="text"  value="<?=$_1_u_frasco_acepas?>" ></td>   
				<td align="right">Altura da Descrição:</td>
				<td><input class="size4"  name="_1_<?=$_acao?>_frasco_adescricao" type="text"  value="<?=$_1_u_frasco_adescricao?>" ></td>  				
				<td align="right">Espaço acima da partida:</td>
				<td><input class="size4"  name="_1_<?=$_acao?>_frasco_espsobpart" type="text"  value="<?=$_1_u_frasco_espsobpart?>" ></td>          
				<td align="right">Espaço após informação partida:</td>
				<td><input class="size4"  name="_1_<?=$_acao?>_frasco_esppospart" type="text"  value="<?=$_1_u_frasco_esppospart?>" ></td>
			</tr>  
			<tr>
				<td align="right">Tamanho Fonte Títulos:</td>
				<td><input class="size4"  name="_1_<?=$_acao?>_frasco_ftitulo" type="text"  value="<?=$_1_u_frasco_ftitulo?>" ></td>   				
				<td align="right">Tamanho Fonte Textos:</td>
				<td><input class="size4"  name="_1_<?=$_acao?>_frasco_ftexto" type="text"  value="<?=$_1_u_frasco_ftexto?>" ></td>   
				<td align="right">Tamanho Fonte Cepas:</td>
				<td><input class="size4"  name="_1_<?=$_acao?>_frasco_ftextocepas" type="text"  value="<?=$_1_u_frasco_ftextocepas?>" ></td>  				
				<td align="right">Tamanho Fonte Partida:</td>
				<td><input class="size4"  name="_1_<?=$_acao?>_frasco_fpartida" type="text"  value="<?=$_1_u_frasco_fpartida?>" ></td>          
				<td align="right">Tamanho Fonte Texto Descição:</td>
				<td><input class="size4"  name="_1_<?=$_acao?>_frasco_fdescricao" type="text"  value="<?=$_1_u_frasco_fdescricao?>" ></td>
			</tr> 
			<tr>
				<td align="right">Altura Fundo Imagem:</td>
				<td><input class="size4"  name="_1_<?=$_acao?>_frasco_ftitulo" type="text"  value="<?=$_1_u_frasco_ftitulo?>" ></td>   				
				<td align="right">Largura Fundo Imagem:</td>
				<td><input class="size4"  name="_1_<?=$_acao?>_frasco_ftexto" type="text"  value="<?=$_1_u_frasco_ftexto?>" ></td>  
				<td align="right">Largura Imagem:</td>
				<td><input class="size4"  name="_1_<?=$_acao?>_frasco_whidtimagem" type="text"  value="<?=$_1_u_frasco_whidtimagem?>" ></td>
				<td align="right">Altura Imagem:</td>
				<td><input class="size4"  name="_1_<?=$_acao?>_frasco_heightimagem" type="text"  value="<?=$_1_u_frasco_heightimagem?>" ></td> 
			</tr>
			<tr>
				<td align="right">Imagem:</td>
				<td colspan="5">
					<div class="cbupload" title="Clique ou arraste arquivos para cá" style="width:100%;height:100%;">
						<i class="fa fa-cloud-upload fonte18"></i>
					</div>
				</td>
			</tr> 
	    </table>
	 </div>
    </div>
</div>

<?
if(!empty($_1_u_frasco_idfrasco)){// trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_frasco_idfrasco; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
	$tabaud = "frasco"; //pegar a tabela do criado/alterado em antigo
	$_disableDefaultDropzone = true;
	require 'viewCriadoAlterado.php';
?>

<?
$sqlFrasco = "SELECT * FROM frasco WHERE idfrasco = ".$_1_u_frasco_idfrasco."";
$resFrasco = d::b()->query($sqlFrasco) or die("A Consulta do certificado falhou :".mysql_error()."<br>Sql:".$sqlcert);
$nFrasco = mysql_num_rows($resFrasco);

$arrcerttmp = array();
$rFrasco = mysqli_fetch_assoc($resFrasco);
$arrcerttmp["id"] = $rFrasco["idfrasco"];
$caminhoImagem = $rFrasco["imagemfrasco"];
$nomeImagem = explode("/", $caminhoImagem);
$arrcerttmp["nome"]	= $nomeImagem[3];
$arrcerttmp["caminho"] = $caminhoImagem;

if(!empty($caminhoImagem)){
	$arrcerttmp = $JSON->encode($arrcerttmp);
}else{
	$arrcerttmp = 0;
}
?>

<script>
var jImagem = <?=$arrcerttmp?>;
<? if(!empty($_1_u_frasco_idfrasco)){ ?>
	$(".cbupload").dropzone({
		idObjeto: $("[name=_1_u_frasco_idfrasco]").val()
		,tipoObjeto: 'frasco'
		,tipoArquivo: 'IMAGEMFRASCO'
		,idPessoaLogada: '<?=$_SESSION["SESSAO"]["IDPESSOA"]?>'
		,maxFiles: 1
		,init: function(){
			this.on("sending", function(file, xhr, formData){
				formData.append("idobjeto", this.options.idObjeto);
				formData.append("tipoobjeto", this.options.tipoObjeto);
				formData.append("tipoarquivo", this.options.tipoArquivo);
				formData.append("idPessoaLogada", this.options.idPessoaLogada);
			});

			this.on("error", function(file,response,xhr){
				if(xhr.getResponseHeader('x-cb-formato') == 'erro' && xhr.getResponseHeader('x-cb-resposta') == '0'){
					alertAtencao("Formato do Arquivo Inválido");
				}else{
					alertErro("Ocorreu um erro inesperado");
				}
			});

			this.on("addedfile", function(file) {
				if((jImagem !== 0 && file.id !== undefined) || (jImagem === 0 && file.id === undefined))
				{
					var removeButton = Dropzone.createElement("<i class='fa fa-trash hoververmelho' title='Apagar arquivo'></i>");

					var _this = this;

					removeButton.addEventListener("click", function(e) {
						e.preventDefault();
						e.stopPropagation();
						
						if(confirm("Deseja realmente excluir o arquivo?"))
						{  	
							_this.removeFile(file);
							var imgfrasco = '';
							CB.post({
								objetos:"_x_u_frasco_idfrasco="+file.id+"&_x_u_frasco_imagemfrasco="+imgfrasco
								, parcial:true
							});						
						}
					});

					file.previewElement.appendChild(removeButton);

					file.previewElement.addEventListener("click", function(e) {
						e.preventDefault();
						e.stopPropagation();

						janelamodal(file.caminho);
					});
				}else{
					this.removeFile(file);
					alert("Só é possível ter um arquivo de Imagem por vez.\nÉ necessário excluir o arquivo antigo para poder adicionar um novo.");
				}

			});
			
			if(jImagem !== 0){
				var mockFile = { 
					name: jImagem.nome
					,nome: jImagem.nome
					,caminho: jImagem.caminho
					,id: jImagem.id
				};

				this.emit("addedfile", mockFile).emit("complete", mockFile);
			}
		}
	});
<? } ?>
//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>
