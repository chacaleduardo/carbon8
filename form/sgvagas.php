<?
require_once("../inc/php/validaacesso.php");

if($_POST){
	require_once("../inc/php/cbpost.php");
}

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parâmetros GET que devem ser validados para compor o select principal
 *                pk: indica parâmetro chave para o select inicial
 *                vnulo: indica parâmetros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "sgvagas";
$pagvalmodulo=$_GET['_modulo'];
$pagvalcampos = array(
	"idsgvagas" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from "._DBAPP.".sgvagas where idsgvagas = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
require_once("../inc/php/controlevariaveisgetpost.php");


function mostraFuncoes(){
    	global $_1_u_sgvagas_idsgcargo;
	$s = "
		select 
			scf.status,
			scf.idsgcargofuncao,
			sf.funcao
			
		from 
			sgcargofuncao scf
		join 
			sgfuncao sf on sf.idsgfuncao = scf.idsgfuncao
		where
			scf.status = 'ATIVO' and
			scf.idsgcargo = ".$_1_u_sgvagas_idsgcargo."
		order by
			sf.funcao;";

	$rts = d::b()->query($s) or die("mostraFuncoes: ". mysqli_error(d::b()));

	while ($r = mysqli_fetch_assoc($rts)) {
            echo $r["funcao"]."<br>";
        }
}


function mostraObs(){
    	global $_1_u_sgvagas_idsgcargo;
	$s = "select obs from sgcargo
		where idsgcargo = ".$_1_u_sgvagas_idsgcargo."";

	$rts = d::b()->query($s) or die("mostraObs: ". mysqli_error(d::b()));

	while($r = mysqli_fetch_array($rts)) {
            echo $r["obs"];
	} 
	
}

?>
<style>
.diveditor {
    border: 1px solid gray;
    background-color: white;
    color: black;
    font-family: Arial,Verdana,sans-serif;
    font-size: 10pt;
    font-weight: normal;
    width: 800px;
    height: 260px;
    word-wrap: break-word;
    overflow: auto;
    padding: 5px;
}
</style>
<div class="container-fluid">
    <?$tabaud = "sgvagas";?>
    <input name="_1_<?=$_acao?>_sgvagas_idsgvagas" 
				    type="hidden" 
				    value="<?=$_1_u_sgvagas_idsgvagas?>" 
				    readonly='readonly'>
    <div class="panel panel-default">
        <div class="panel-heading">
			<table>
				<tr>
					<td>Título:</td>
					<td><input name="_1_<?=$_acao?>_sgvagas_titulo" type="text" value="<?=$_1_u_sgvagas_titulo?>"></td>
					<td>Nº de Vagas:</td>
					<td><input class="size5" name="_1_<?=$_acao?>_sgvagas_numerovagas" type="number" min="1" max="30" value="<?=$_1_u_sgvagas_numerovagas?>"></td>
					<td>Status:</td>
					<td>
						<select name="_1_<?=$_acao?>_sgvagas_status">
							<?fillselect("select 'ATIVO','ATIVO' union select 'INATIVO','INATIVO'",$_1_u_sgvagas_status);?>
						</select>
					</td>
				</tr>
			</table>
        </div>
        <div class="panel-body">
			<table style="width:100%;">
				<!--tr>
					<!--td>Área:</td>
					<td style="width:40%;">
						<select name="_1_<?=$_acao?>_sgvagas_idsgarea" id="idarea" vnulo>
							<option value=""></option>
							<?//fillselect("select idsgarea, area  from sgarea where status = 'ATIVO' order by area ",$_1_u_sgvagas_idsgarea);?>
						</select>
					</td>
					<td><a class="fa fa-plus-circle verde pointer hoverazul" title="Área" onclick="abrirpopup1('?_modulo=sgarea')"></a></td>
					<td><a class="fa fa-bars pointer hoverazul" title="Área" onclick="abrirpopup2('?_modulo=sgarea','idarea','idsgarea')"></a></td>
					<td>Departamento:</td>
					<td>
						<select name="_1_<?=$_acao?>_sgvagas_idsgdepartamento" id="iddepartamento" vnulo>
							<option value=""></option>
							<?//fillselect("select idsgdepartamento, departamento  from sgdepartamento where status = 'ATIVO' order by departamento ",$_1_u_sgvagas_idsgdepartamento);?>
						</select>
					</td>
					<td><a class="fa fa-plus-circle verde pointer hoverazul" title="Departamento" onclick="abrirpopup1('?_modulo=sgdepartamento')"></a></td>
					<td><a class="fa fa-bars pointer hoverazul" title="Departamento" onclick="abrirpopup2('?_modulo=sgdepartamento','iddepartamento','idsgdepartamento')"></a></td>
				</tr-->
				<tr>
					<td>Área / Departamento / Setor:</td>
					<td style="width:40%;">
						<select name="_1_<?=$_acao?>_sgvagas_objeto" id="idsetor" vnulo>
							<option value=""></option>
							<?fillselect("SELECT DISTINCT (CONCAT(idobjeto, '-', tipoobjeto)) AS idobjeto, 
											CASE WHEN sc.tipoobjeto = 'sgarea' THEN sa.area
												WHEN sc.tipoobjeto = 'sgdepartamento' THEN sd.departamento
												WHEN sc.tipoobjeto = 'sgsetor' THEN ss.setor END AS nome
											 FROM sgcargo sc  
										LEFT JOIN sgarea sa ON sc.idobjeto = sa.idsgarea AND sc.tipoobjeto = 'sgarea'
										LEFT JOIN sgdepartamento sd ON sc.idobjeto = sd.idsgdepartamento AND sc.tipoobjeto = 'sgdepartamento'
										LEFT JOIN sgsetor ss ON sc.idobjeto = ss.idsgsetor AND sc.tipoobjeto = 'sgsetor'	
											WHERE 1 
											  AND sc.idobjeto IS NOT NULL ORDER by nome;",$_1_u_sgvagas_objeto);?>
						</select>
					</td>
					<td><a class="fa fa-plus-circle verde pointer hoverazul" title="Setor" onclick="abrirpopup1('?_modulo=sgsetor')"></a></td>
					<td><a class="fa fa-bars pointer hoverazul" title="Setor" onclick="abrirpopup2('?_modulo=sgsetor','idsetor','idsgsetor')"></a></td>
					<td>Cargo:</td>
					<td>
						<select name="_1_<?=$_acao?>_sgvagas_idsgcargo" id ="idcargo" vnulo>
							<option value=""></option>
							<?fillselect("select idsgcargo, cargo  from sgcargo where status = 'ATIVO' order by cargo ",$_1_u_sgvagas_idsgcargo);?>
						</select>
					</td>
					<td><a class="fa fa-plus-circle verde pointer hoverazul" title="Cargo" onclick="abrirpopup1('?_modulo=sgcargo')"></a></td>
					<td><a class="fa fa-bars pointer hoverazul" title="Cargo" onclick="abrirpopup2('?_modulo=sgcargo','idcargo','idsgcargo')"></a></td>
				</tr>
				<tr>
					<td colspan="4">Formação:</td>
					<td colspan="4">Funções:</td>
				</tr>
			</table>
            <div class="row">
                    <div class="col-sm-6" id="idformacao">
                        <?if(($_acao == 'u') || !empty($_1_u_sgvagas_idsgcargo)){?>
                            <?=mostraObs()?>
                        <?}?>
                    </div>
                    <div class="col-sm-6" id="idfuncao">
                        <?if(!empty($_1_u_sgvagas_idsgcargo)){?>
                            <?=mostraFuncoes()?>
                        <?}?>
                    </div>
            </div>
        </div>
    </div>
   <?
if(!empty($_1_u_sgvagas_idsgvagas)){// trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_sgvagas_idsgvagas; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
	$tabaud = "sgvagas"; //pegar a tabela do criado/alterado em antigo
	require 'viewCriadoAlterado.php';
?>
</div>
        
        

<script>

CB.prePost = function(){
    $.ajax({
            type: "get",
            url : "cron/bim.php",

            success: function(data){
                   // alert('OK');
            },

            error: function(objxmlreq){
                    alert('Erro:<br>'+objxmlreq.status); 

            }
    })//$.ajax

}

function abrirpopup1(vthis){
	CB.modal({
        url: vthis
        ,header:"Evento"
    });
}

function abrirpopup2(vthis, uthis, wthis){
    var aux = "#"+uthis;
    var i = $(aux).val();
    
    var str = vthis + "&_acao=u&" + wthis + "=" + i;
    
    CB.modal({
        url: str
        ,header:"Evento"
    });
}

function AlteraStatus(vthis){
	
	//alert($(vthis).attr('idsgareasetor'));
	var id;
	id = $(vthis).attr('idsgcargofuncao');
	status = $(vthis).attr('status');
	//alert(status);
	var novostatus, cor, novacor;
    if (status == 'ATIVO'){
		novostatus = 'INATIVO';
		cor = 'verde hoververde';
		novacor = 'vermelho hoververmelho';
		
		
    }else{
		novostatus = 'ATIVO';	
		cor = 'vermelho hoververmelho';
		novacor = 'verde hoververde';
    }
	//alert("_x_u_sgareasetor_idsgareasetor="+id+"&_x_u_sgareasetor_status="+novostatus);
    CB.post({
				objetos: "_x_u_sgcargofuncao_idsgcargofuncao="+id+"&_x_u_sgcargofuncao_status="+novostatus
				
				,refresh: true
				,msgSalvo: "Status Alterado"
				,posPost: function(){
					$(vthis).removeClass(cor);
					$(vthis).addClass(novacor);
					$(vthis).attr('status', novostatus);
					$(vthis).attr('title', novostatus);
					$('#'+id).addClass('hide');
					//removeClass("vermelho hoververmelho").addClass("verde hoververde");
				}
			});
    
}

function novoobjeto(inobj){
    CB.post({
	objetos: "_x_i_"+inobj+"_idsgcargo="+$("[name=_1_u_sgcargo_idsgcargo]").val()
    });
    
}


function inativaobjeto(inid,inobj){    		
    CB.post({
	objetos: "_x_u_pessoa_idpessoa="+ inid +"&_x_u_pessoa_idsgcargo="
	
    });    
}

	sSeletor = '#diveditor';
	oDescritivo = $("[name=_1_"+CB.acao+"_sgcargo_obs]");

	//Atribuir MCE somente apà³s método loadUrl
	//CB.posLoadUrl = function(){
		//Inicializa Editor
		if(tinyMCE.editors["diveditor"]){
		    tinyMCE.editors["diveditor"].remove();
		}
                //Inicializa Editor
                tinymce.init({
                        selector: sSeletor
                        ,language: 'pt_BR'
                        ,inline: true /* não usar iframe */
                        ,toolbar: 'formatselect | removeformat | fontsizeselect | forecolor backcolor | bold | alignleft aligncenter alignright alignjustify | subscript superscript | bullist numlist | table | pagebreak'
                        ,menubar: false
                        ,plugins: ['table','textcolor','lists']
                        ,fontsize_formats: "8pt 10pt 12pt 14pt 18pt 24pt 36pt"
                        ,setup: function (editor) {
				editor.on('init', function (e) {
					this.setContent(oDescritivo.val());
				});
			}
                        ,entity_encoding: 'raw'
                });
	 //}

	//Antes de salvar atualiza o textarea
	CB.prePost = function(){
		if(tinyMCE.get('diveditor')){
			//falha nbsp: oDescritivo.val( tinyMCE.get('diveditor').getContent({format : 'raw'}).toUpperCase());
			oDescritivo.val( tinyMCE.get('diveditor').getContent().toUpperCase());
		}
	}
        
//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>
<script>
    //Busca departamentos de uma área selecionada no input
    function preencheDepartamento(){
        $("#iddepartamento").html("<option value=''>Procurando....</option>");
        $("#idsetor").html("<option value=''>Procurando....</option>");
        $("#idcargo").html("<option value=''>Procurando....</option>");
        
        $.ajax({
            type: "get",
            url : "ajax/buscadepartamento.php",
            data: { 
                area : $("#idarea").val()
            },
            success: 
                function(data){
                    $("#iddepartamento").html(data);
            },

            error: function(objxmlreq){
                alert('Erro:<br>'+objxmlreq.status);
            }
        });//$.ajax
    }
        
        function preencheSetor(){
        $("#idsetor").html("<option value=''>Procurando....</option>");
        
        $.ajax({
            type: "get",
            url : "ajax/buscasetor.php",
            data: { 
                departamento : $("#iddepartamento").val()
            },
            success: 
                function(data){
                    $("#idsetor").html(data);
            },

            error: function(objxmlreq){
                alert('Erro:<br>'+objxmlreq.status);
            }
        });//$.ajax
    }
    
    
        function preencheCargo(){
        $("#idcargo").html("<option value=''>Procurando....</option>");
        
        $.ajax({
            type: "get",
            url : "ajax/buscacargo.php",
            data: { 
                setor : $("#idsetor").val()
            },
            success: 
                function(data){
                    $("#idcargo").html(data);
            },

            error: function(objxmlreq){
                alert('Erro:<br>'+objxmlreq.status);
            }
        });//$.ajax
    }
    
    function preencheFormacao(){
        $("#idformacao:hidden").show();
        $.ajax({
            type: "get",
            url : "ajax/buscaformacao.php",
            data: { 
                cargo : $("#idcargo").val()
            },
            success: 
                function(data){
                    $("#idformacao").html(data);
            },

            error: function(objxmlreq){
                alert('Erro:<br>'+objxmlreq.status);
            }
        });//$.ajax
    }
    
    function preencheFuncoes(){
        $("#idfuncao:hidden").show();
        $.ajax({
            type: "get",
            url : "ajax/buscafuncao.php",
            data: { 
                cargo : $("#idcargo").val()
            },
            success: 
                function(data){
                    $("#idfuncao").html(data);
            },

            error: function(objxmlreq){
                alert('Erro:<br>'+objxmlreq.status);
            }
        });//$.ajax
    }
        
                                            
    $().ready(function() {
        $("#idarea").change(function(){
            preencheDepartamento();
        });
        $("#iddepartamento").change(function(){
            preencheSetor(); 
        });
        $("#idsetor").change(function(){
            preencheCargo(); 
        });
        $("#idcargo").change(function(){
            preencheFormacao(); 
            preencheFuncoes(); 
        });
    });

</script>
