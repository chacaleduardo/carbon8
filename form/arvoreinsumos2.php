<?
require_once("../inc/php/validaacesso.php");

if($_POST){
	require_once("../inc/php/cbpost.php");
}
/*
if(empty($_GET["idprodserv"])){
$_GET["idprodserv"]=1726;
}*/

if(empty($_GET["idlote"])){
	die("Id do Lote não enviado via GET");
}

/*if(empty($_GET["idobjetoprodpara"])){
	die("Id do Lote pai não enviado via GET");
}*/

$aLote=getObjeto("lote",$_GET["idlote"],"idlote");

$aProdserv=getObjeto("prodservarvore", $aLote["idprodserv"],"idprodserv");

$jLote=$JSON->encode($aLote);

$jInsumos = $aProdserv["jarvore"];

//$aLotes=getLotesProduzidosPara($_GET["idlote"]);
$aLotes=getLotesProduto($aLote["idprodserv"]);
$jLotes=$JSON->encode($aLotes);

$getIdproservformulains=($_GET["idprodservformulains"])?"#".$_GET["idprodservformulains"]:"#0";
?>

<link href="inc/js/diagrama/Treant.css?_<?=date("dmYh")?>" rel="stylesheet">
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

.node.formulaNaoSelecionada{
    background-color: #f8f8f8;
    color: silver;
    border: 2px dashed silver;
    cursor: default;
}
</style>
<div id="tree"></div>

<script src="inc/js/diagrama/Treant.js"></script>
<script src="inc/js/formalizacao.js"></script>

<script>
	
$("#cbModuloForm").css("position","absolute");

jLote=<?=$jLote?>;

jInsumos=<?=$jInsumos?>;

jArvoreLotes=<?=$jLotes?>;

jTreant=null;

idProdservFormulains="<?=$getIdproservformulains?>";

listaFlatInsumos={};

//Inicializa estrutura Flat para correspondàªncia com jInsumos através da coluna idprodservformulains
aFlatLotes={};
aFlatLotes["lotesSemIdprodservformulains"]=[];
aFlatLotes["#0"]={};
aFlatLotes["#0"][jLote.idlote]=jLote;//Monta o item superior (produto final) como #0

/*
 * A árvore pode ser desenhada a partir de nà­veis inferiores
 * Por isso é necessário filtrar (procurando recursivamente) a partir do prodservinsumo desejado
 */
var primeiroNode={};
function encontrarPrimeiroNode(j){
	var oRet=null;
	$.each(j, function(i, o){
		//console.log(i);
		
		if(i==idProdservFormulains){
			//Atribui à  variável global, para montagem da árvore
			primeiroNode[i]=o;
			return false;
		}else{
			encontrarPrimeiroNode(o.insumos||{});
		}
	});

	return oRet;
}
/*
 * Construir estrutura da àrvore de Insumos SOMENTE
 * Não possui informação dos Lotes. Serve somente para desenha na tela a estrutura cadastrada
 * Recursivamente transforma o padrão de json->encode do PHP para o formato da biblioteca Treant
 * Caso deseje-se controlar a construção da árvore para evitar nà­veis desnecessários, deve ser feito neste ponto
 * Maiores controles de aparàªncia e HTML devem ser feitos apà³s serem desenhados na tela
 * Ex: aqui verifica-se se o pai do insumo possui a fà³rmula selecionada, caso negativo, não desenha-se os filhos
 */
function preparaNodesArvoreInsumos(j){
	self=this;
	
	var objs=[];

	$.each(j, function(i, o) {

		//Opçàµes default do Treant mais customizaçàµes
		var oTexto = {
			name: o.codprodserv
			,idlote: o.idlote
			,idArvoreInsumos: i
			,idArvorePai: o.idprodservformulainspai
			,insumo: o
			,lotes: vLotes
			,classesAdicionais: ""
		}

		var idProdServFormula=evalJson(`aFlatLotes["${i}"][primeiro(aFlatLotes["${i}"])].idprodservformula`);
	
		//Recupera os Lotes do node/insumo em questão
		var vLotes=evalJson(`aFlatLotes["${i}"]`);

		//Varrer os lotes em busca de quantidades disponà­veis para produção
		var somaEstoqueDisponivel = getSomaEstoqueDisponivel(vLotes||{});

		//Visto que cada lote possui um idprodservformula, deve-se recuperar a fà³rmula configurada *** no pai *** para verificação se está tudo correto:
		arrFormulasLotes=getFormulasLotes(aFlatLotes["#"+o.idprodservformulainspai]||{});

		vClassesAdicionais="";
		arrChildren=null;
		desenhaNode=false;
		
		//Se houver formula selecionada corretamente no Node pai
		if(arrFormulasLotes && arrFormulasLotes.length==1){
			//Casos seja o primeiro elemento, ou a fà³rmula deste node seja igual à  que foi selecionada no node parent
			if(o.idprodservformula==arrFormulasLotes[0]){
				arrChildren=preparaNodesArvoreInsumos(o.insumos||{});
				oTexto.classesAdicionais="";
				desenhaNode=true;	
			}
		}else{
			arrChildren={};
			oTexto.classesAdicionais="formulaNaoSelecionada";
			desenhaNode=true;
		}

		//Monta o elemento juntamente com os insumos
		el = {
			text: oTexto
			,children: arrChildren
		};

		/*
		 * Caso seja um node válido (Ex: Fà³rmula igual à  selecionada no pai), adiciona à  coleção que será desenhada na tela
		 * Caso contrário simplesmente descarta-se
		 */
		if(desenhaNode){
			objs.push(el);
		}

		//Armazena em modo flat para facilitar extração de propriedades. Caso não seja informado supàµe-se que seja o primeiro nà­vel
		listaFlatInsumos["#"+(o.idprodservformulains||"0")]=o;
	});
	return objs;
}

function getSomaEstoqueDisponivel(inLotes){
	sumLotes=0;
	$.each(inLotes, function(i,l){
		sumLotes+=parseInt(l.qtddisp);
	})
	return sumLotes;
}

function arvoreLotesWalk(j){

	$.each(j, function(i, o) {
		if(tamanho(o.lotesproduzidospara)){
			arvoreLotesWalk(o.lotesproduzidospara);
		}
		//Armazena em modo flat para facilitar extração de propriedades
		if(o.idprodservformulains){
			aFlatLotes["#"+o.idprodservformulains]=aFlatLotes["#"+o.idprodservformulains]||{};
			aFlatLotes["#"+o.idprodservformulains][o.idlote]=o;
		}else{
			aFlatLotes.lotesSemIdprodservformulains.push(o);
		}
	});

}

arvoreLotesWalk(jArvoreLotes);

//encontrarPrimeiroNode(jInsumos);
//jTreant = preparaNodesArvoreInsumos(primeiroNode);

jTreant = preparaNodesArvoreInsumos(jInsumos);

/*
 * Utilização da biblioteca 
 */
simple_chart_config = {
	chart:{
		container:			"#tree",
		levelSeparation:    50,
		siblingSeparation:  5,
		subTeeSeparation:   15,
		connectors: {
			type: 'step',
			style: {
				"stroke-width": 2,
				"stroke": "#ccc"
			}
		}	
		,node: {
			/*HTMLclass: 'nodeCustom'*/
			collapsable: true
			,collapsed: true
		}
		,callback : {
			//Antes de criar a tag html
			onCreateNode: function(inNode){
				
				//console.log(inNode);
			}
			//Depois de criar a tag html, cria um atributo contendo o estoque, para referencia posterior
			,onAfterPositionNode: function(inNode){

			}
			//Depois de montar toda a árvore de objetos
			,onTreeLoaded: function(nodePai){
				customizaNodes();
			}
		}
	}
	,nodeStructure: jTreant[0]
};

var arvore = new Treant(simple_chart_config);

/*
 * Esta função NàO é recursiva. Ela irá percorrer de forma linear as tags html que representam cada node
 * Em cada NODE irá ser verificada a ação do mouseover, os elementos que irão aparecer no popup, cores (css) de caixas, etc
 * Atenção: Existem alguns métodos disponà­veis nesse objeto (self.[nomeMétodo])
 */
function customizaNodes(){
	self=this;

	/*
	 * Este método vai tratar genericamente cada nà³ da árvore, para desenhar o conteúdo conforme as condiçàµes existentes
	 */
	self.getTextoPopup=function(inNode){
		//Verificar se existe 1 (e somente 1) fà³rmula para os lotes gerados. Caso exista mais de uma f?mula selecionada, tratar
		arrFormulasLotes=getFormulasLotes(inNode.data.treenode.text.lotes||{});

		//Se existir algum lote criado, mas a fà³rmula não tiver sido selecionada
		if(tamanho(inNode.data.treenode.text.lotes)>0 && (arrFormulasLotes.length==0 || arrFormulasLotes.length>1)){
			console.log("Nenhuma (ou mais de 1) fà³rmula selecionada: "+arrFormulasLotes.length);
			strSeletor = seletorFormulaPopover(inNode,"alterarFormula");
			return strSeletor;
		}else{
			
			//Caso não existam lotes criados, mostrar botão para criar novo lote
			if(tamanho(inNode.data.treenode.text.lotes)==0 && inNode.data.treenode.text.idArvoreInsumos!=="#0"){
				
				//Produto fabricado: tratar quando não existirem lotes disponà­veis
				if(inNode.data.treenode.text.insumo.fabricado!=="N"){
					strPop = htmlNovoLote(inNode);
				//Verifica produto comprado
				}else{
					strPop = "<div class='alert alert-warning' role='alert'><i class='fa fa-info-circle'></i> Produto não-fabricado sem lotes disponà­veis!</div>";
				}
				return strPop;

			//Caso existam, listar
			}else{
				//Listagem de estoque
				strPop = htmlEst(inNode);

				//Não mostrar botão de novo lote
				if(inNode.data.treenode.id!==0){
					strPop += "<br>"+htmlNovoLote(inNode);
				}
				return strPop;
			}
		}
	}

	$.each($(".node"), function(i,obj){

		vobj=obj;
		$vobj=$(vobj);
		//Atribui o Id do node para ser recuperável posteriormente
		$vobj.attr("idnode", vobj.data.treenode.id);

		var sTitle = "<a href='?_modulo=prodserv&_acao=u&idprodserv="+vobj.data.treenode.text.insumo.idprodserv+"' target='_blank' title='Abrir Produto'><strong>"+vobj.data.treenode.text.insumo.descr+"</string></a>";

		//Adiciona imediatamente classes adicionais
		$vobj.addClass(vobj.data.treenode.text.classesAdicionais);

		//Trata primeira caixa (nà­vel 0)
		if(vobj.data.treenode.parentId===-1){
			var oLote=evalJson(`vobj.data.treenode.text.lotes[primeiro(vobj.data.treenode.text.lotes)]`);

			$vobj
				.webuiPopover({
					title: sTitle
					,trigger:'hover'
					,placement:'bottom'
					,cache: false
					,content: function(){
						return self.getTextoPopup(this);
					}
				});
		}else{
			$vobj
				.webuiPopover({
					title: sTitle
					,trigger:'hover'
					,placement:'bottom'
					,cache: false
					,content: function(){
						return self.getTextoPopup(this);
					}
				});
				
		}
	});

	//Os botàµes de toggle collapse foram posicionados fora das caixas, portanto é necessário impedir que o Hover acione o popup
	$(".Treant .collapse-switch").hover(function(e){
		e.stopPropagation();
		e.preventDefault();
		return false;
	})
}

function seletorFormulaPopover(inNode){
	vObj=listaFlatInsumos[inNode.data.treenode.text.idArvoreInsumos];
	strFormulas="<div class='seletorFormula' idnode='"+inNode.data.treenode.id+"'><span class='cinzaclaro'>Selecione a Fà³rmula para insumos:</span>";
	strFormulas+=htmlSeletorFormula(vObj.insumos,"alteraFormula");
	strFormulas+="</div>"
	return strFormulas;
	//console.log(inNode);
}

function alteraFormula(inObj){
	
	$obj=$(inObj);
	idFormulaSelecionada=$obj.val();
	
	//Recupera o id do Node relacionado
	idNode=$obj.closest("div[idnode]").attr("idnode");
	
	/*
	 * Recupera os lotes existentes para o node
	 * Todos os lotes existentes devem ser alterados
	 */
	vLotes = $(".node[idnode="+idNode+"]")[0].data.treenode.text.lotes;
	sPost="";
	ecom="";
	$.each(vLotes, function(io,o){
		//Altera o lote
		sPost+=`${ecom}_loteformula_u_lote_idlote=${io}&_loteformula_u_lote_idprodservformula=${idFormulaSelecionada}`;
		ecom="&";
	});
	
	CB.post({
		objetos: sPost
		,parcial:true
		,posPost: function(){
			$(".webui-popover").remove();
			$("#cbModal").modal("hide");
		}
	})
}

function htmlEst(inNode){
	//console.log(inNode);
	var strFracoes="";

	var tabest = "<table class='inlineblocktop'>";
	tabest += "<tr><th>Lote</th><th>Disp.</th><th>Utilizar</th></tr>";
	var strTr="";
	
	nodeSuperior=inNode;
	
	
	$.each(inNode.data.treenode.text.lotes||{}, function(i,lote){
		vLote=lote;
		$.each(lote.consumosdolote, function(ic,c){
			//O node supoerior pode possuir mais de 1 lote a ser produzido. Deve-se verificar então se o idobjeto é equivalente a qualquer 1 dos lotes superiores
			if(c.tipoobjeto=='lote' && evalJson(`nodeSuperior.data.treenode.parent().text.lotes[${c.idobjeto}]`)){
				strTr += `<tr idlote="${vLote.idlote}">
					<td title="#${vLote.idlote}"><a>${vLote.partida}</a></td>
					<td>${vLote.qtdpedida}</td>
					<td>
						<div type="text" value="${c.qtd}" idlotecons="${c.idlotecons}" idlote="${vLote.idlote}" class="lotecons qtd" style='width: 3em;' onchange="atualizaLotecons(this)">${c.qtd}</div>
					</td>
				</tr>`;
			}
		});
	});

	tabest += strTr;
	tabest += "</table>";
	
	return tabest;
}

function htmlNovoLote(inNode){
	return `<a class='fa fa-plus-circle pointer fade hoververde fa-2x' onclick='gerarFormalizacao("0",${inNode.data.treenode.text.insumo.idprodserv},${jLote.idlote},"${inNode.data.treenode.text.insumo.descr}",${inNode.data.treenode.text.insumo.idprodservformulains},${jLote.idpessoa})' title="Gerar Novo Lote"></a>`;
}

function gerarFormalizacao(inQtdprod,inIdprodserv,inIdloteprodpara,inDescr,inIdprodservformulains,inIdpessoa){

	strobjetos=`_x_i_lote_qtdpedida=${inQtdprod}&_x_i_lote_tipoobjetoprodpara=lote&_x_i_lote_idobjetoprodpara=${inIdloteprodpara}&_x_i_lote_idprodserv=${inIdprodserv}&_x_i_lote_idprodservformulains=${inIdprodservformulains}&_x_i_lote_idpessoa=${inIdpessoa}&_x_i_lote_status=FORMALIZACAO`;

	//if(confirm("Deseja gerar uma NOVA PARTIDA para\n\n"+inDescr+"?\n\n")){

		CB.post({
			objetos: strobjetos
			,parcial: true
			,posPost: function(){
				$(".webui-popover").remove();
			}
		})
	//}
}

function atualizaLotecons(inObj){
	$o=$(inObj);
	CB.post({
		objetos: `_x_u_lotecons_idlotecons=${$o.attr("idlotecons")}&_x_u_lotecons_qtd=${$o.val()}`
		,parcial: true
	})
	console.log(inObj);
}
//o comentario abaixo faz com que este pedaço de script apareça na aba 'sources' do inspetor do google chrome
//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>;
</script>

<?
//print_r($arrInsumos);
require_once '../inc/php/readonly.php';
?>