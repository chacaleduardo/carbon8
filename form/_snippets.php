<?
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
$pagvaltabela = "_snippet";
$pagvalcampos = array(
	"idsnippet" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from "._DBCARBON."._snippet where idsnippet = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

function getModuloSnippet(){
	global $JSON;

	$sql = "SELECT * from "._DBCARBON."._modulo m where m.status = 'ATIVO'";
	$res = d::b()->query($sql) or die("Falha ao consultar módulos do sistema");
	if(mysqli_num_rows($res) > 0){
		$arr = array();
		$i = 0;
		while($r = mysqli_fetch_assoc($res)){
			$arr[$i]["modulo"] = $r["modulo"];
			$i++;
		}
		return $JSON->encode($arr);
	}else{
		return 0;
	}
}

if(!empty($_1_u__snippet_idsnippet)){
	$jModulo = getModuloSnippet();
}

?>
<!--script type="text/javascript" src="../inc/js/jscolor/jscolor.js"></script-->
<div class="col-md-12">
    <div class="panel panel-default" >
        <div class="panel-heading">
	    	<table>
	    		<tr> 		    
					<td>
						<input 
							name="_1_<?=$_acao?>__snippet_idsnippet" 
							type="hidden" 			   
							value="<?=$_1_u__snippet_idsnippet?>" 
							readonly='readonly'					>
					</td> 
	   
					<td>Snippet</td> 
					<td>
						<input 
							name="_1_<?=$_acao?>__snippet_snippet" 
							type="text" 
							value="<?=$_1_u__snippet_snippet?>" 
												>
					</td> 
					<td>Status</td> 
					<td>
						<select name="_1_<?=$_acao?>__snippet_status">
						<?fillselect("select 'ATIVO','Ativo' union select 'INATIVO','Inativo'",$_1_u__snippet_status);?>		
						</select>
					</td>
					<?if(!empty($_1_u__snippet_idsnippet)){?>
					<td>
						Módulo
					</td>
					<td>
						<input type="text" id="modulo" value="<?=$_1_u__snippet_modulo?>">
					</td>
					<?}?>
	    		</tr>		    
	    	</table>
		</div>
		<div class="panel-body"> 
	    	<table>	
				<tr>
					<td>Classe ícone:</td>
					<td class="nowrap">
						<input type="text" name="_1_<?=$_acao?>__snippet_cssicone" value="<?=$_1_u__snippet_cssicone?>" maxlength="45">
						<i id="seletoricones" class="<?=($_1_u__snippet_cssicone)?$_1_u__snippet_cssicone:"fa fa-smile-o"?> fa-2x fade"></i>
					</td>
				</tr>
				<tr> 
					<td>URL:</td> 
					<td>
						<textarea 
							name="_1_<?=$_acao?>__snippet_code" 
							type="text" size="4"><?=$_1_u__snippet_code?></textarea>
					</td> 
				</tr>		
				<tr> 
					<td>Notificação:</td> 
					<td>
						<? 
						if($_1_u__snippet_notificacao == 'Y'){ 
							$checked = 	'checked';
							$notificacao = 'N';
						} else {
							$checked = 	'';
							$notificacao = 'Y';
						}
						?>
						<input title="grupo"  type="checkbox" <?=$checked?> name="notificacao" onclick="altNotificacao(<?=$_1_u__snippet_idsnippet?>, '<?=$notificacao?>')">
					
					
					</td>
				</tr>	
				<tr> 
					<td>Tipo</td> 
					<td>
						
						<select name="_1_<?=$_acao?>__snippet_tipo">
							<?fillselect("select 'PHP','Php' union select 'JS','Js' union select 'LINK','Link'",$_1_u__snippet_tipo);?>		
						</select>
					</td>
				</tr>	
	    	</table>
	 	</div>
    </div>
</div>
<div class="col-md-12">
    <?$tabaud = "_snippet";?>
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
<script>

<?if(!empty($_1_u__snippet_idsnippet)){?>
var jModulo = <?=$jModulo?>

if(jModulo != 0){
	$("#modulo").autocomplete({
		source: jModulo
		,delay: 0
		,create: function(){
			$(this).data('ui-autocomplete')._renderItem = function (ul, item) {
				return $('<li>').append("<a>"+item.modulo+"</a>").appendTo(ul);

			};
		}
		,select: function(event, ui){
			CB.post({
				objetos: {
					"_x_u__snippet_idsnippet" : $("[name*=__snippet_idsnippet").val(),
					"_x_u__snippet_modulo": ui.item.modulo
				},
				parcial: true
			});
		}
	});
}

<?}?>

function excluir(tab,inid){
    if(confirm("Deseja retirar este?")){		
        CB.post({
        objetos: "_x_d_"+tab+"_id"+tab+"="+inid
        });
    }   
}

function novo(inobj){
    CB.post({
	objetos: "_x_i_"+inobj+"__snippet="+$("[name=_1_u__snippet_idsnippet]").val()
    });
}

//Monta um seletor de à­cones de acordo com parte do nome dos arquivos CSS informados
var styleSheetList = document.styleSheets;
var hIcones="";
var separador="";
$.each(styleSheetList, function(i,o){
	var prefClasse;
	//Procura por Css por parte do nome do arquivo
	if(o.href && /laudofonts|fontawesome/.test(o.href)){
		//Adicionar o prefixo padrao para utilizacao da fonte css
		if(/laudofonts/.test(o.href)){
			prefClasse = "laudoicon";
		}else if(/fontawesome/.test(o.href)){
			prefClasse = "fa";
		}
		hIcones += separador;
		//Loop em todas as classes css
		oRules=o.rules||o.cssRules;
		$.each(oRules, function(ir,or){
			if(or.type=="1"){
				//Extrai a string referente ao seletor Css do icone
				if(/::/.test(or.selectorText)){
					var strIco = or.selectorText.match(/.*?(?=::|$)/)[0].replace(/^\./, '');
					hIcones += `<i class="${prefClasse + " " + strIco} fa-2x hoververmelho" style="margin:3px;" cssicone="${prefClasse + " " +strIco}" title="${prefClasse + " " +strIco}"" onclick="alteraIcone(this)"></i>`;
                }
            }
		});

		separador = "<hr>";
	}
})

$("#seletoricones").webuiPopover({
	title:'Selecionar à­cone para o Módulo'
	,content: hIcones
});

function alteraIcone(inObj){
	$("[name=_1_u__snippet_cssicone]").val($(inObj).attr("cssicone"));
}

function altNotificacao(idsnippet, notificacao)
{
	CB.post({
        objetos: "_1_u__snippet_idsnippet="+idsnippet+"&_1_u__snippet_notificacao="+notificacao
		,parcial: true	
    }); 
}
//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>
