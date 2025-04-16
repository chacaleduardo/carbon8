<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if($_POST){
	require_once("../inc/php/cbpost.php");
}

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "arquivoponto";
$pagvalcampos = array(
	"idarquivoponto" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from arquivoponto where idarquivoponto = '#pkid'";
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
		    <input 
			    name="_1_<?=$_acao?>_arquivoponto_idarquivoponto" 
			    type="hidden" 			   
			    value="<?=$_1_u_arquivoponto_idarquivoponto?>" 
			    readonly='readonly'					>
		</td> 
	   
		<td>Data</td> 
		<td>
		    <input 
			    name="_1_<?=$_acao?>_arquivoponto_entrada" 
			    type="text" 
                            class="calendario"
			    value="<?=$_1_u_arquivoponto_entrada?>" 
									>
		</td> 
	   
		
	    </tr>	    
	    </table>
	</div>
	 <div class="panel-body"> 
              <?if($_1_u_arquivoponto_idarquivoponto){?>
            <div class="row">
               <div class="col-md-2 nowrap">Ponto:</div>
               <div class="col-md-4">
                    <select  name="_1_<?=$_acao?>_arquivoponto_idtag"  onchange="settag(this,<?=$_1_u_arquivoponto_idarquivoponto?>)" >						
                        <?fillselect("select idtag,concat(tag,' - ',descricao) as descr from tag where idtagtipo=207 and status='ATIVO'
                            order by descr",$_1_u_arquivoponto_idtag);?>		
                    </select>
               </div>
            </div>
              <?}?>
	 </div>
    </div>
</div>

<?
if(!empty($_1_u_acao_idarquivoponto)){// trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_acao_idarquivoponto; // trocar p/ cada tela o id da tabela
	require '../form/viewAssinaturas.php';
}
	$tabaud = "arquivoponto"; //pegar a tabela do criado/alterado em antigo
    $idRefDropzone = "arquivoponto";
	require '../form/viewCriadoAlterado.php';
?>

    <!--div class="col-md-12">
     <?$tabaud = "arquivoponto";?>
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
    </div-->


<script >
    <?if($_1_u_arquivoponto_idarquivoponto and $_1_u_arquivoponto_idtag){?>
  var idarquivoponto = Number(<?=$_1_u_arquivoponto_idarquivoponto?>);   
    
$("#arquivoponto").dropzone({
    idObjeto: idarquivoponto
    ,tipoObjeto: 'arquivoponto'
    ,tipoArquivo: 'PONTO'
    ,idtag: $("[name=_1_u_arquivoponto_idtag]").val()
    ,sending: function(file, xhr, formData){
        //Ajusta parametros antes de enviar via post
        formData.append("idtag", this.options.idtag);
    }
});
    <?}?>
function settag(vthis, inid) {
   CB.post({
	   objetos: `_x_u_arquivoponto_idarquivoponto=`+inid+`&_x_u_arquivoponto_idtag=`+$(vthis).val(),
	   parcial: true	
   });
}

//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>