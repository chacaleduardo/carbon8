<?
require_once("../inc/php/validaacesso.php");

if($_POST){
	require_once("../inc/php/cbpost.php");
}
/*
if(empty($_GET["idprodserv"])){
$_GET["idprodserv"]=1726;
}*/

if(empty($_GET["idprodserv"])){
	die("Id do Produto não enviado via GET");
}
if(empty($_GET["idobjetoprodpara"])){
	die("Id do Lote pai não enviado via GET");
}

$arrInsumos = getArvoreInsumos($_GET["idprodserv"],true);

$arrObj=array();
$arrObjKey=array();
$collapsed="";
function transformaArvoreParaJsonTreant($a,$pai=null) {
	global $arrObj, $arrObjKey,$collapsed;

	if (!is_array($a)) {
		//echo $a, ' ';
		return;
	}
	
	//$collapsed="collapsed: true,";
	foreach($a as $k=>$v) {
		
		//Variavel contendo insumo customizado, que foi originado via amostra de determinado cliente
		$idObjetoSoliPor=(!empty($_GET["idobjetosolipor"] && $v["idprodserv"]==$_GET["idprodserv"]))?$_GET["idobjetosolipor"]:false;
		
		$strEst = "estoque: ".getEstoque($v["idprodserv"],false,$idObjetoSoliPor).",";
		$idObjetoSoliPor=false;
		$strTSalas = "tiposSala:".getTipoSalas($v["idprproc"]).",";
		$strPai=($v["nodepai"]=="")?"":"parent: p".$v["nodepai"].",";

		$arrObjKey[]="\np".$k;

		$arrObj[]= "\np".$k." = {
	".$strEst."
	".$strTSalas."
	".$strPai."
	".$collapsed."
	HTMLid : '".$k."',
	qtdpadrao: '".$v["qtdpadrao"]."',
	qtdpadrao_exp: '".$v["qtdpadrao_exp"]."',
	idprodserv: '".$v["idprodserv"]."',
	descr: '".$v["descr"]."',
	text: {
		name: '".$v["codprodserv"]."',
		title: '".$v["qtdpadrao"]."',
	}
}";

		transformaArvoreParaJsonTreant($v["insumos"], $k);

	}
	
}

transformaArvoreParaJsonTreant($arrInsumos);

$objTreant=implode(",", $arrObj);
$strTreantKey=implode(",", $arrObjKey);

//Gera uma string json normal (sem ser no padrão treant para poder fazer referàªncia via js
$JSON = new Services_JSON();
$jInsumos=$JSON->encode($arrInsumos);

if(empty($_SERVER["HTTP_REFERER"])){
?>
<link href="../inc/css/bootstrap/css/bootstrap.min.css?_<?=date("dmYh")?>" rel="stylesheet">
<link href="../inc/js/diagrama/Treant.css?_<?=date("dmYh")?>" rel="stylesheet">
<?}else{?>

<link href="inc/css/bootstrap/css/bootstrap.min.css?_<?=date("dmYh")?>" rel="stylesheet">
<link href="inc/js/diagrama/Treant.css?_<?=date("dmYh")?>" rel="stylesheet">
<?}?>

<style>
/* optional Container STYLES */
#tree { 
	height: 100%; 
	width: 100%; 
}
.node { 
	color: gray;
	border: 2px solid #C8C8C8;
	border-radius: 3px;
	height: 80px;
}
.node p{
	font-size: 15px;
	line-height: 16px;
	font-weight: bold;
	padding: 3px;
	margin: 0;
}
.node .node-title{
	font-size: 12px;
}
.Treant .collapse-switch {
	display: block;
    position: absolute;
    cursor: pointer;
    top: 75px;
    right: 0px;
    left: 18px;
    margin: auto;
    height: 14px;
    width: 10px;
    border: none;
}
.Treant .collapsed .collapse-switch{
    background-color: white;
}

.Treant .collapse-switch:after{
    content: '\f139';
    font-family: FontAwesome;
    font-weight: normal;
    font-style: normal;
    text-decoration: none;
    left: 10px;
    margin: -2px;
	color: silver;
}

.Treant .collapsed .collapse-switch:after{
	content: '\f13a';
	color:  silver;
}
.node.semEstoque{
    background-color: #fec3c3;
    border-color: darkred;
    color: darkred;
}
.node.estoqueNaoSelecionado{
    background-color: #ffd280;
    color: #c54a00;
    border: 2px solid #c54a00;
}

</style>

<?
if(empty($_SERVER["HTTP_REFERER"])){
?>
<script src="../inc/js/jquery/jquery-1.11.2.min.js?_<?=date("dmYh")?>"></script>
<script src="../inc/js/diagrama/Treant.js?_<?=date("dmYh")?>"></script>
<script src="../inc/js/diagrama/vendor/raphael.js?_<?=date("dmYh")?>"></script>
<?}else{?>

<script src="inc/js/diagrama/Treant.js?_<?=date("dmYh")?>"></script>
<script src="inc/js/diagrama/vendor/raphael.js?_<?=date("dmYh")?>"></script>
<?}?>

<div id="tree"></div>

<script>
jInsumos=<?=$jInsumos?>;
arrGeraLoteAuto=[];
$("#cbModuloForm").css("position","absolute");

var config = {
        container: "#tree",
        levelSeparation:    50,
        siblingSeparation:  5,
        subTeeSeparation:   5,
        XrootOrientation: "WEST",
        connectors: {
            type: 'step',
            style: {
                "stroke-width": 2,
                "stroke": "#ccc"
            }
        },
        node: {
            /*HTMLclass: 'nodeCustom'*/
            collapsable: true
			,collapsed: true
        },
		callback : {
			//Antes de criar a tag html
			onCreateNode: function(inNode){
				null;
			}
			//Depois de criar a tag html, cria um atributo contendo o estoque, para referencia posterior
			,onAfterPositionNode: function(inNode){
				if(window["p"+inNode.nodeHTMLid]){
					inNode.nodeDOM.data.prodserv=window["p"+inNode.nodeHTMLid];
				}
			}
			//Depois de montar toda a árvore de objetos
			,onTreeLoaded: function(nodePai){
				//$tree=this;
				//customizaNoPai(nodePai,$tree);
				trataNosArvore();
			}
		}
    },

<?=$objTreant?>
;
    chart_config = [
        config,
        <?=$strTreantKey?>
    ];

var arvore = new Treant(chart_config);

function htmlProcesso(inNode){

	strHProc ="";
	strHProc += htmlEst(inNode);
	strHProc += htmlSalas(inNode);
	strHProc += "<button class='btn btn-xs btn-danger' onclick='consumirLote(this)'>Ok</button>";
	strHProc += htmlNovoLote(inNode);
	return strHProc;
}

function htmlNovoLote(inNode){

	if(inNode.children){
		avisoInsumos=false;
		$.each(inNode.children, function(i,ins){
			iEstoqueInsumo=Object.keys(ins.estoque).length;
			console.log("Estoque insumo:"+iEstoqueInsumo);
			if(iEstoqueInsumo==0){
				avisoInsumos=true;
			}
		})
		if(avisoInsumos){
			return "<hr></hr><i class='fa fa-exclamation-circle laranja'></i><label>  Insumos insuficientes</label>";
		}else{
			return "<hr></hr><button class='btn btn-xs btn-success' onclick='gerarNovoLote(\""+inNode.HTMLid+"\")'>Gerar Novo Lote</button>";
		}
	}else{
		return "<hr></hr><button class='btn btn-xs btn-success' onclick='gerarNovoLote(\""+inNode.HTMLid+"\")'>Gerar Novo Lote</button>";
	}
}

function htmlEst(inNode){
	//console.log(inNode);
	var strFracoes="";

	var tabest = "<table class='inlineblocktop'>";
	tabest += "<tr><th>Lote</th><th>Disp.</th><th>Utilizar</th></tr>";
	var strTr="";

	if(inNode["estoque"]==undefined){
		alertAtencao("Informação de estoque não disponà­vel");
		return "";
	}

	$.each(inNode["estoque"], function(i,fracao){
		//strFracoes+="<br>"+JSON.stringify(fracao);
		strTr += `<tr>
<td>${fracao.partida}</td>
<td>${fracao.qtd}</td>
<td><input class="lotecons qtd" idlotecons="" idlote="${fracao.idlote}" type='text' value='' style='width: 3em;'></td>
				</tr>`;
	})

	tabest += strTr;
	tabest += "</table>";
	
	return tabest;
}

function htmlSalas(inNode){
	sTSalas = "<table class='inlineblocktop'>";
	sTSalas += "<tr>";
	
	if(inNode["tiposSala"]==undefined){
		alertAtencao("Informação de Salas não disponà­vel");
		return "";
	}
	
	$.each(inNode["tiposSala"], function(i,tipoSala){
		sTSalas += "<th>"+tipoSala+"</th>";
	})
	sTSalas += "</tr>";
	sTSalas += "</table>";

	return sTSalas;
}

/*
 * Inicializa a visualização dos nà³s conforme a movimentação de estoque
 * Status de Lotes e quantidades são tratadas aqui para colorir as caixas 
 *
 */

function trataNosArvore(){
	vProdPara=getUrlParameter("idobjetoprodpara");
	$.each($(".node"), function(i,obj){

		vobj=obj;
		$vobj=$(vobj);

		var sTitle = "<a href='?_modulo=prodserv&_acao=u&idprodserv="+vobj.data.prodserv.idprodserv+"' target='_blank' title='Abrir Produto'><strong>"+vobj.data.prodserv.descr+"</string></a>";

		//Trata primeira caixa
		if(vobj.data.treenode.parentId===-1 && vProdPara){
			$.each(vobj.data.prodserv.estoque, function(idlote, lote){
				if(lote.tipoobjetoprodpara=="lote" && lote.idobjetoprodpara==vProdPara){
					shtml=`<a href='?_modulo=formalizacao&_acao=u&idlote=${lote.idlote}' target='_blank' title='Abrir Formalização'><strong>${lote.partida}</strong></a>`;
					$vobj
						.addClass("semEstoque")
						.find("p.node-name").html(shtml);
				}
			})
		}else{

			//Verifica a quantidade de estoque encontrada
			classNode="";
			if(Object.keys(vobj.data.prodserv.estoque).length==0){
				classNode="semEstoque";
				//Teste para gerar lotes automaticamente para o terceiro nivel
				if(vobj.data.treenode.parentId==0){
					arrGeraLoteAuto.push(vobj.data.treenode.nodeDOM.id);
				}
			}else{
				classNode="estoqueNaoSelecionado";
			}

			$vobj
				.addClass(classNode)
				.webuiPopover({
					title: sTitle
					,trigger:'hover'
					,placement:'bottom'
					,cache: false
					,content: function(){
						strEst=htmlProcesso(this.data.prodserv);
						return strEst;
					}
				});
		}
		
		//Soma o estoque
		if(vobj.data.prodserv.estoque){
			destoque=0;
			$.each(vobj.data.prodserv.estoque, function(i,o){
				//console.log(o.qtd);
				destoque+=parseFloat(o.qtd);
			})
			$vobj.find("p.node-title").html(destoque);
		}
		
	});

	//Os botàµes de toggle collapse foram posicionados fora das caixas, portanto é necessário impedir que o Hover acione o popup
	$(".Treant .collapse-switch").hover(function(e){
		e.stopPropagation();
		e.preventDefault();
		return false;
	})

}

function consumirLote(inBtOk){
	$.each($(inBtOk).parent().find(":input.lotecons"), function(i,input){
		$input=$(input);
		idlote=$input.attr("idlote");
		idlotecons=$input.attr("idlotecons");
		qtd=$input.val();
		if(!idlotecons && qtd!==""){
			CB.post({
				objetos: "_x_i_lotecons_tipoobjeto=lote&_x_i_lotecons_idobjeto="+idlote+"&_x_i_lotecons_qtd="+qtd
				,refresh:"refresh"
			});
		}

	})
}


function gerarFormalizacao(inIdprodserv,inIdloteprodpara,inDescr){

	strobjetos=`_x_i_lote_tipoobjetoprodpara=lote&_x_i_lote_idobjetoprodpara=${inIdloteprodpara}&_x_i_lote_idprodserv=${inIdprodserv}&_x_i_lote_idpessoa=${$_1_u_lote_idpessoa}&_x_i_lote_status=FORMALIZACAO`;

	if(confirm("Deseja gerar uma NOVA PARTIDA para\n\n"+inDescr+"?\n\n")){

		CB.post({
			objetos: strobjetos
			,refresh: false
			,posPost: function(data, textStatus, jqXHR){
				console.log(jqXHR);
				if(jqXHR.getResponseHeader("X-CB-RESPOSTA")=="1"
						&&jqXHR.getResponseHeader("X-CB-PKID")
						&&jqXHR.getResponseHeader("X-CB-PKFLD")=="idlote"){
					janelamodal("?_modulo=arvoreinsumos&idprodserv="+inIdprodserv+"&idobjetoprodpara="+inIdloteprodpara);
				}
			}
		})
	}
}

if(arrGeraLoteAuto.length>0){
	/*
	if(confirm("Deseja gerar uma NOVA PARTIDA automaticamente?")){
		strobjetos="";
		i=0;
		ecom="";
		$.each(arrGeraLoteAuto, function(i,o){
			vo=o;
			jo=window["p"+o];
			qtdsol=jo.qtdpadrao;
			strobjetos+=`${ecom}_x${i}_i_lote_qtdpedida=${qtdsol}&_x${i}_i_lote_tipoobjetoprodpara=lote&_x${i}_i_lote_idobjetoprodpara=${getUrlParameter("idobjetoprodpara")}&_x${i}_i_lote_idprodserv=${jo.idprodserv}&_x${i}_i_lote_status=FORMALIZACAO`;

			i++;
			ecom="&";
		})
		console.log(strobjetos);
		
		CB.post({
			objetos: strobjetos
		})
	}
	*/
   
}

function verificaNode(key,val) {
    console.log(key + " - "+val);
}

function varreArvoreRec(o,callback) {
    for (var idNode in o) {
        callback.apply(this,[idNode,o[idNode]]);  
        if (o[idNode] !== null && typeof(o[idNode])=="object") {
            varreArvoreRec(o[idNode],callback);
        }
    }
}

function traverse(jObj) {

    if(jObj && typeof jObj === "object" ) {
		
		if(Object.keys(jObj).length==1 && jObj[Object.keys(jObj)[0]].insumos){
			console.log(jObj);
			oinsumos=jObj[Object.keys(jObj)[0]].insumos;
			$.each(oinsumos, function(i,insumo) {
				// k is either an array index or object key
				o={};
				o[i]=insumo;
				traverse({i:insumo});
			});
		}else{
			/*
			$.each(jObj, function(k,v) {
				// k is either an array index or object key
				traverse(v);
			});*/
		}
        
    }
    else {
        // jsonOb is a number or string
    }
}
traverse(jInsumos);
//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>
<?

//print_r($arrInsumos);

require_once '../inc/php/readonly.php';
?>