<?
require_once("../inc/php/validaacesso.php");
date_default_timezone_set('America/Sao_Paulo');
if($_POST){
	require_once("../inc/php/cbpost.php");
}

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "devicefirm";
$pagvalcampos = array(
	"iddevicefirm" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from devicefirm where iddevicefirm = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

?>
<script type="text/javascript" src="../inc/js/jscolor/jscolor.js"></script>
<div class="col-md-12">
    <div class="panel panel-default" >
        <div class="panel-heading">
	    <table>
	    <tr> 		    
		<td>
		    <input 
			    name="_1_<?=$_acao?>_devicefirm_iddevicefirm" 
			    type="hidden" 			   
			    value="<?=$_1_u_devicefirm_iddevicefirm?>" 
			    readonly='readonly'					>
		</td> 
	   
		<td>Modelo</td> 
		<td>
        <select name="_1_<?=$_acao?>_devicefirm_modelo" >
                                            <option value=""></option>
                     <?fillselect("select 'CICLO','Ciclo' union select 'AUTOCLAVE','Autoclave'
					 union select 'AUTOCLAVE VACUO','Autoclave Vacuo'
                     union select 'MONITORAMENTO DE SALA','Monitoramento de sala' union select 'ESTUFA','Estufa'
                     union select 'CHILLER','Chiller' union select 'CÂMARA FRIA','Câmara fria' 
					 union select 'ESTUFA ENV','Estufa ENV' union select 'ESTUFA CAL','Estufa CAL'",$_1_u_devicefirm_modelo);?>			
					</select>
		   
		</td> 

		<td>Descrição</td> 
		<td>
        <input 
			    name="_1_<?=$_acao?>_devicefirm_descricao" 
			    type="text" 
			    value="<?=$_1_u_devicefirm_descricao?>" 
									>
		</td>
	    <td>Status</td> 
		<td>
		    <select name="_1_<?=$_acao?>_devicefirm_status">
			<?fillselect("select 'ATIVO','Ativo' union select 'INATIVO','Inativo'",$_1_u_devicefirm_status);?>		
		    </select>
		</td> 
		 <td>Versão</td> 
		<td>
        <input 
			    name="_1_<?=$_acao?>_devicefirm_versao" 
			    type="text" 
			    value="<?=$_1_u_devicefirm_versao?>" 
									>
		</td>
	    </tr>	    
	    </table>
	</div>
	 <div class="panel-body"> 
	    <table>
		
		<tr> 
		<td id="arq" style="display:none;">Arquivo</td> 
		<td>
        <i class="fa fa-cloud-upload dz-clickable pointer azul" style="display: none;" id="certanexo" title="Clique para adicionar um certificado"></i>
		</td>
		</tr>		
		<tr> 
			
	    </table>
	 </div>
    </div>
</div>


    <div class="col-md-12">
     <?$tabaud = "devicefirm";?>
    <div class="panel panel-default">		
        <div class="panel-body">
            <div class="row col-md-12">		
                <div class="col-md-1 nowrap">Criado Por:</div>     
                <div class="col-md-5"><?=${"_1_u_".$tabaud."_criadopor"}?></div>
                <div class="col-md-1 nowrap">Criado Em:</div>     
                <div class="col-md-5"><?=${"_1_u_".$tabaud."_criadoem"}?></div>   
            </div>
            <div class="row col-md-12">            
                <div class="col-md-1 nowrap">Alterado Por:</div>     
                <div class="col-md-5"><?=${"_1_u_".$tabaud."_alteradopor"}?></div>
                <div class="col-md-1 nowrap">Alterado Em:</div>     
                <div class="col-md-5"><?=${"_1_u_".$tabaud."_alteradoem"}?></div>       
            </div>
        </div>
    </div>
    </div>


<script >
function excluir(tab,inid){
    if(confirm("Deseja retirar este?")){		
        CB.post({
        objetos: "_x_d_"+tab+"_id"+tab+"="+inid
        });
    }
    
}

function novo(inobj){
    CB.post({
	objetos: "_x_i_"+inobj+"_iddevicefirm="+$("[name=_1_u_devicefirm_iddevicefirm]").val()
    });
    
}

<?if(!empty($_1_u_devicefirm_iddevicefirm)){?>
	if( $("[name=_1_u_devicefirm_iddevicefirm]").val() ){
		$("#certanexo").show();
		$("#arq").show();
		<?
			$sqlcert = "SELECT * FROM devicefirm WHERE iddevicefirm = ".$_1_u_devicefirm_iddevicefirm."";
			$rescert = d::b()->query($sqlcert) or die("A Consulta do certificado falhou :".mysql_error()."<br>Sql:".$sqlcert);
			$ncert = mysql_num_rows($rescert);
			if($ncert > 0){
				$arrcerttmp = array();
				$rcert = mysqli_fetch_assoc($rescert);
				$arrcerttmp["id"]=$rcert["iddevicefirm"];
				$arrcerttmp["caminho"]=$rcert["caminho"];
				$arrcerttmp = $JSON->encode($arrcerttmp);
			}else{
				$arrcerttmp = 0;
			}
		?>
		var jCert = <?=$arrcerttmp?>;

		$("#certanexo").dropzone({
			idObjeto: $("[name=_1_u_devicefirm_iddevicefirm]").val()
			,tipoObjeto: 'devicefirm'
			,tipoArquivo: 'ARQUIVO'
			,maxFiles: 1
			,init: function(){
				this.on("sending", function(file, xhr, formData){
					formData.append("idobjeto", this.options.idObjeto);
					formData.append("tipoobjeto", this.options.tipoObjeto);
					formData.append("tipoarquivo", this.options.tipoArquivo);
				});

				this.on("error", function(file,response,xhr){
					if(xhr.getResponseHeader('x-cb-formato') == 'erro' && xhr.getResponseHeader('x-cb-resposta') == '0'){
						alertAtencao("Formato do Arquivo Inválido");
					}else{
						alertErro("Ocorreu um erro inesperado");
					}
				});

			

				if(jCert !== 0){
                    var caminho = jCert.caminho;
                    
					var mockFile = {
                        name: caminho
						,nome: caminho 
						,caminho: jCert.caminho
						,id: jCert.id
					};

					this.emit("addedfile", mockFile).emit("complete", mockFile);
				}
			}
		});
	}
<?}?>

//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>
