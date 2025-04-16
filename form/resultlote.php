<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
require_once(__DIR__."/controllers/resultlote_controller.php");

if($_POST){
    include_once("../inc/php/cbpost.php");
}

$exercicio = $_POST["exercicio"];
$idini = $_POST["idini"];
$idfim = $_POST["idfim"];
$idtipoteste = $_POST["idtipoteste"];
$status = $_POST["status"];
$descritivo = $_POST["descritivo"];
$idusuario = "LOTE_".$_SESSION["SESSAO"]["USUARIO"];//$perfilpag_idusuario

echo "<!-- "; print_r($_POST); echo " -->";

if(!empty($exercicio)
	and(!empty($idini))
	and(!empty($idfim))
	and(!empty($idtipoteste))
	and(!empty($descritivo))
	and(!empty($_SESSION["SESSAO"]["USUARIO"]))){

	/*
	 * maf 050110: recupera o menor e maior idamostra (pk) para passar para a procedure
	 */
	echo "<!-- min:".$idini."<br>".$idfim." -->";
	$rowFluxo = ResultLoteController::buscarTipoBotao('resultaves',$status);
	$idfluxostatus = $rowFluxo['idfluxostatus'];
	$tipobotao = $rowFluxo['tipobotao'];

	/*
	 * maf: chama o procedmento de exportacao
	 */
	$row = ResultLoteController::executaProcessaLote($idini,$idfim,$idtipoteste,$status,$idfluxostatus,$tipobotao,$descritivo,$_SESSION["SESSAO"]["USUARIO"]);
	
	echo("<div class='alert alert-warning' role='alert'><i class='fa fa-hand-o-right bold'></i>&nbsp;Resultado: <b>".$row["resultado"]."</b><br><br></div>");
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
    width: 695px;
    height: 98%;
    word-wrap: break-word;
    overflow: auto;
    padding: 5px;
}

.itemestoque{
	width:auto;
	display: inline-block;
	text-align: right;
}
</style>	


<div class="row">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading" >Inclusão em Lote - Dados a serem processados</div>
        <div class="panel-body" >   
 <table>

<tr>
	<td align="right">Exerc&iacute;cio:</td>
	<td>
		<input name="exercicio" id="exercicio" type="text" style="width: 150px;" vnulo value="<?=date("Y")?>">
	</td>
</tr>
<tr>
	<td>Intervalo de Nºs de Registro</td>
	<td class="nowrap">
		<!-- <font class="9graybold">entre:</font> -->
			<input name="idini" id="idini" style="width: 150px;" type="text" onchange="buscatestes()" vnulo>
			<font class="9graybold">e</font>
		<input name="idfim" id="idfim" style="width: 150px;" type="text"  onchange="buscatestes()" vnulo>
	</td>
</tr>
<tr>
	<td align="right">Tipo de Teste:</td>
	<td>
		<select name="idtipoteste" style="width: 250px;" id="idtipoteste" onchange="buscatestes()" data-live-search="true" vnulo>
                    <option value=""></option>
<?
fillselect("select idtipoteste, sigla from vwtipoteste t where status = 'ATIVO'  ".getidempresa('idempresa','prodserv')." order by sigla");
?>
		</select>
	</td>
</tr>
<tr id="trstatus" class="hidden" >
	<td align="right">Mudar Status:</td>
	<td>
		<select name="status" style="width: 250px;" id="status" vnulo>
			<option value="FECHADO">FECHADO</option>
			<? //Somente quem tem permissão poderá voltar para o Status Aberto ?>
			<? if(getModsUsr("MODULOS")["restaurar"]["permissao"]=='w'){ ?>
				<option value="ABERTO">ABERTO</option>
			<? } ?>
			<option value="PROCESSANDO">PROCESSANDO</option>
		</select>
	</td>
</tr>

<tr id="trresultado" class="hidden">   
    <td align="right">Resultado:</td>
    <td>
	    <label id="lbaviso" class="idbox" style="display: none;"></label>
	    <div id="diveditor" 
		    class="diveditor"
		    onkeypress="pageStateChanged=true;"
		    style="width: 800px;height: 200px;"><?=$descritivo?></div>

	    <textarea style="display: none;" name="descritivo"><?=$descritivo?></textarea>
    </td>
</tr>
</table>
	    <div class="row"> 
		<div class="col-md-8"></div>
		<div class="col-md-2">
		   <button id="cbPesquisarx" class="btn btn-danger btn-xs hidden" onclick="salvar()">
		       <i class="fa fa-circle">Salvar</i>
		   </button> 
		</div>   
            </div>
	</div>
    </div>
    </div>
</div>
<table>
<tr>    
    <td rowspan="4" style="vertical-align: top;" id="quantidadetestes"></td>
</tr>
</table>
<script>
	sSeletor = '#diveditor';
	oDescritivo = $("[name=descritivo]");

	//Resetar qualquer objeto com tinymce para não causar conflito
	tinymce.EditorManager.editors = [];

	//Atribuir MCE somente apà³s método loadUrl
	//CB.posLoadUrl = function(){
		//Inicializa Editor
		
		tinymce.init({
			selector: sSeletor	
			,language: 'pt_BR'
			,inline: true /* não usar iframe */
			,toolbar: 'bold | subscript superscript | bullist numlist | table'
			,menubar: false
			,plugins: ['table']
			,setup: function (editor) {
				editor.on('init', function (e) {
					this.setContent(oDescritivo.val());
				});
			}
			,entity_encoding: 'raw'
		});

	 //}

	 $('#idtipoteste').selectpicker();

	//Antes de salvar atualiza o textarea
	CB.prePost = function(){
		$(".fases:not(:checked)").each( (i,o) => {
			$($(o).attr('fase')).find("[name*='_i_lotecons']").each( (k,v) => {
				let linha = $(v).attr('name').split('_')[1];
				let atributo = $(v).attr('name').split('_')[4];
				$(v).attr('name',linha+atributo);
			});
		})


		if(tinyMCE.get('diveditor')){
			//falha nbsp: oDescritivo.val( tinyMCE.get('diveditor').getContent({format : 'raw'}).toUpperCase());
			oDescritivo.val( tinyMCE.get('diveditor').getContent().toUpperCase());
		}
	}

function salvar () {
	var objpost = {};

	$(".fases:checked").each( (i,o) => {
		$($(o).attr('fase')).find("[name*='_i_lotecons']").each( (k,v) => {
			objpost[$(v).attr('name')] = v.value
		});
	});

	$(".vincular-tag:checked").each( (i,o) => {
		objpost[`_tag${i}_i_objetovinculo_idobjetovinc`] = $(o).attr("idtag");
	});

	if(Object.keys(objpost).length > 0){

		objpost["exercicio"] 	= $("[name=exercicio]").val();
		objpost["idini"] 		= $("[name=idini]").val();
		objpost["idfim"] 		= $("[name=idfim]").val();
		objpost["idtipoteste"] 	= $("[name=idtipoteste]").val();
		objpost["status"] 		= $("[name=status]").val();
		objpost["descritivo"] 	= $('#diveditor').html();

		CB.post({
			objetos: objpost,
			parcial: true
		})
	}
	
}
	
 function pesquisar(){
     
    var exercicio = $("[name=exercicio]").val();
    var idini = $("[name=idini]").val();
    var idfim = $("[name=idfim]").val();
    var idtipoteste = $("[name=idtipoteste]").val();
    var status = $("[name=status]").val();
    var descritivo = $('#diveditor').html();
    var str="exercicio="+exercicio+"&idini="+idini+"&idfim="+idfim+"&idtipoteste="+idtipoteste+"&status="+status+"&descritivo="+descritivo;
  
        CB.go(str);
}

function buscatestes(){
    
    $.ajax({
        type: "get",
        url : "ajax/buscaqtdteste.php",
        data: { idprodserv : $('#idtipoteste').val(),idregi: $('#idini').val(),idregf:$('#idfim').val(),exercicio:$('#exercicio').val()},

        success: function(data){
                $("#quantidadetestes").html(data);
                $("#trstatus").removeClass("hidden");
                $("#trresultado").removeClass("hidden");
                $("#cbPesquisarx").removeClass("hidden");

        },

        error: function(objxmlreq){
                alert('Erro:<br>'+objxmlreq.status); 

        }
    })//$.ajax

}

function esgotarlote(inIdlotefracao){
    if(confirm("Deseja realmente esgotar o lote?")){
	CB.post({
	    "objetos":"_x_u_lotefracao_idlotefracao="+inIdlotefracao+"&_x_u_lotefracao_status=ESGOTADO&_x_u_lotefracao_qtd=0&&_x_u_lotefracao_qtd_exp=0"
	    ,parcial:true
       });
    }   
}


function atualizainput(vthis, inlinha){

	if(vthis.value == ""){
		$('[name="_'+inlinha+'_i_lotecons_idlote"]').attr('name', inlinha+"idlote");
		$('[name="_'+inlinha+'_i_lotecons_idlotefracao"]').attr('name', inlinha+"idlotefracao");
		$('[name="_'+inlinha+'_i_lotecons_idprodserv"]').attr('name', inlinha+"idprodserv");
		$('[name="_'+inlinha+'_i_lotecons_qtdteste"]').attr('name', inlinha+"qtdteste");
		$('[name="_'+inlinha+'_i_lotecons_qtdd"]').attr('name', inlinha+"qtdd");
	}else{
		$("[name="+inlinha+"idlote]").attr('name', '_'+inlinha+'_i_lotecons_idlote');
		$("[name="+inlinha+"idlotefracao]").attr('name', '_'+inlinha+'_i_lotecons_idlotefracao');
		$("[name="+inlinha+"idprodserv]").attr('name', '_'+inlinha+'_i_lotecons_idprodserv');
		$("[name="+inlinha+"qtdteste]").attr('name', '_'+inlinha+'_i_lotecons_qtdteste');
		$("[name="+inlinha+"qtdd]").attr('name', '_'+inlinha+'_i_lotecons_qtdd');
	}
    
}
function normalizaQtd(inValor){
	var sVlr=""+inValor;
	var $arrExp;
	var fVlr;
	if(sVlr.toLowerCase().indexOf("d")>-1){
		$arrExp=sVlr.toLowerCase().split('d');
		fVlr = (parseFloat($arrExp[0]) * parseFloat($arrExp[1])).toFixed(2);
		fVlr = parseFloat(fVlr);
	}else if(sVlr.toLowerCase().indexOf("e")>-1){
		$arrExp=sVlr.toLowerCase().split('e');
		fVlr = $arrExp[0]*Math.pow(10,$arrExp[1]);
	}else{
		fVlr=parseFloat(sVlr).toFixed(2);
	}
	
	return parseFloat(fVlr);
}

function mostraConsumo(inOConsumo){
  $oc = $(inOConsumo);
	
	$tbInsumo=$oc.closest("table");	
	//$oajustecalc=$tbInsumo.find("[class=ajuste_calc]");
	
	
	$trInsumo=$oc.closest("tr.trInsumo");
	$sQtdpadrao=$trInsumo.find(".sQtdpadrao");
	$sUtilizando=$trInsumo.find(".sUtilizando");
	$sRestante=$trInsumo.find(".sRestante");
    somaUtilizacao=0;
	$oConsumos=$trInsumo.find("[name*=_qtdd]");
        
        
	$.each($oConsumos, function(isc,osc){
		var $o=$(osc);
		if($o.val()){

			if($o.attr("cbqtddispexp")!="" && ($o.val().toLowerCase().indexOf("e")<=0 && $o.val().toLowerCase().indexOf("d")<=0)){
				alertAtencao("Valor inválido. <br> Inserir e ou d.");
				return false;
			}

			valor=$o.val().replace(/,/g, '.');
			valor=normalizaQtd(valor);

			somaUtilizacao+=valor;
		}
	})
        
    qtdPadrao=normalizaQtd($sQtdpadrao.html());

	//somaUtilizacao=recuperaExpoente(somaUtilizacao,qtdPadrao);
	
	if(somaUtilizacao>=qtdPadrao){
		sclass="fundoverde";
	}else{
		sclass="fundolaranja";
	}
	
	if(somaUtilizacao>0){
		//Formata o badge de 'utilizando'
		$sUtilizando
			.html(somaUtilizacao)
			.removeClass("fundoverde")
			.removeClass("fundolaranja")
			.addClass(sclass)
			.attr("title",(somaUtilizacao/qtdPadrao)*100+"%");
	}else{//zero ou vazio
		//Formata o badge de 'utilizando'
		$sUtilizando
			.html(somaUtilizacao)
			.removeClass("fundoverde")
			.removeClass("fundolaranja")
			.attr("title",(somaUtilizacao/qtdPadrao)*100+"%");	    
	}
	
	$sRestante
		//.html(((qtdPadrao-somaUtilizacao)/$vajustecalc))
		.html(qtdPadrao-somaUtilizacao)
		.removeClass("fundoverde")
		.removeClass("fundolaranja")
		.addClass(sclass);
	
}

//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>