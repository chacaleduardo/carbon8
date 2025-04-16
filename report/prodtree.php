<?
require_once("../inc/php/functions.php");
require_once("../inc/php/laudo.php");
require_once("../inc/php/query.php");

//$_GET["idprodserv"]=291;
if($_GET["idprodserv"]){
 
	//q::$echoSql=true;
	q::$mostrarTodasColunas=true;

	//Consulta os dados
	$fins = q::formulaprocesso()::t("vwpdformulainsumo")::idprodserv($_GET["idprodserv"])::statusinsumo('ATIVO')::exec();

	$arrform=[];
	$arrprod0=[];
	//Realiza um agrupamento, pois a mesma formula vem repetidas vezes em todas as linhas
	while($r=mysql_fetch_assoc($fins)) {
		//print_r($r);

		//Dados do pai
		$arrprod0[$_GET["idprodserv"]]["title"]=$r["produto"];
		$arrprod0[$_GET["idprodserv"]]["tipo"]="produto";
		$arrprod0[$_GET["idprodserv"]]["expanded"]="true";
		$arrprod0[$_GET["idprodserv"]]["children"]=[];

		//Dados de filhos (formulas)
		$arrform[$r["idprodservformula"]]["title"]=empty($r["formula"])?"Formula ".$r["idprodservformula"]:$r["formula"];
		$arrform[$r["idprodservformula"]]["tipo"]="formula";
		$arrform[$r["idprodservformula"]]["expanded"]="true";
		$arrform[$r["idprodservformula"]]["children"][]=[
			"title"=> $r["qtdinsumo"]."".$r["uninsumo"]." <b>".$r["insumo"]."</b>"
			, "folder"=> true
			,"lazy"=> true
			, "tipo"=>"produto"
			, "idprodserv"=>$r["idprodservinsumo"]
		];
	}

	//Estrutura os filhos: Loop novamente no array para formatar para o fancytree
	$arrj=[];
	foreach ($arrform as $f=>$ai) {
		$arrj[]=$ai;
	}

	if($_GET["json"]!=="Y"){
		//Encaixa dentro do pai
		$arrprod0[$_GET["idprodserv"]]["children"]=$arrj;
		unset($arrj);
		$arrj[]=$arrprod0[$_GET["idprodserv"]];
	}

	if($_GET["json"]=="Y"){
		echo(json_encode($arrj, JSON_PRETTY_PRINT));
		die();
	}else{
		$jprod=json_encode($arrj, JSON_PRETTY_PRINT);
	}
}

/*
$jprod0="";
$arrProd=[];
//Transforma para formato Fancytree
foreach ($aProdutosFormulacao as $id=>$p) {
	$arrProd[]=[
		"title"=> $p["descr"]
		//, "expanded"=> true //Se estiver expadindo, o lazyload nao ocorre
		, "folder"=> true
		//, "children"=> []
		,"lazy"=> true
		, "joriginal"=>$p
		, "tipo"=>"produto"
		, "idprodserv"=>$id
	];
}
$jprod=json_encode($arrProd, JSON_PRETTY_PRINT);
//print_r($arrProd);
*/

?>
<html>
<head>
<script src="../inc/js/jquery/jquery-1.11.2.min.js"></script>
<link href="../inc/js/fancytree/skin-lion/ui.fancytree.css" rel="stylesheet">
<script src="../inc/js/fancytree/modules/jquery.fancytree-all-deps.min.js"></script>
<script src="../inc/js/functions.js"></script>
<!-- Xscript src="../inc/js/carbon.js"></Xscript -->

<link rel="stylesheet" href="../inc/css/bootstrap/css/bootstrap.min.css" />
<script src="../inc/css/bootstrap/js/bootstrap.min.js"></script>

<style>
.fancytree-has-children.fancytree-ico-cf span.fancytree-icon-formula, /* fechado */
.fancytree-has-children.fancytree-ico-ef span.fancytree-icon-formula, /* aberto */
.fancytree-ico-e span.fancytree-icon /* normalmente expandido */
 {
  background-position: -32px -160px;
}
.insumosimples{
	background-position: -32px -160px;
}
</style>
<!-- Initialize the tree when page is loaded -->
<script type="text/javascript">

	$(function(){  // on page load
	// Create the tree inside the <div id="tree"> element.
		$("#tree").fancytree({
			clickFolderMode: 2,
			//extensions: ["edit", "filter"],
			source: <?=$jprod?>
			,
			//Esta funcao controla as classes de todos os icones. caso nao haja return, o icone padrao é usado
			icon: function(event, data) {
				var node = data.node;
				// Create custom icons
				if(node.data.tipo === "formula" ) {
					return "fancytree-icon fancytree-icon-formula";
				}
			},
			lazyLoad: function(event, data){
				return data.result={url: "?json=Y&idprodserv="+data.node.data.idprodserv};
			},
			postProcess: function(event, data) {
				if(data.node.children==null){
					//$(data.node.span).find(".fancytree-icon").addClass("insumosimples")
				}
				//debugger;
			}
		});
		// Note: Loading and initialization may be asynchronous, so the nodes may not be accessible yet.
	});

function loadprod(){
	var tree = $.ui.fancytree.getTree("#tree");

	// Expand all tree nodes
	tree.visit(function(node){
				node.setExpanded(true);
	});
}
</script>
</head>
<body>

<div class="progress" id="xhrprogress" style="display: block;z-index: 1001;position: fixed;height: 3px;"></div>

</div>

  <!-- Define the targel element for the tree -->
<div class="panel panel-default">
<button onclick="loadprod()">Consultar insumos!</button>
<label id="lbconsefet">Consultas efetuadas: <label id="consefet">0</label></label>
</div>
  <div id="tree"></div>

</body>
</html>
<script>
$(document).ajaxStart(function(){
	$("#lbconsefet").show();
	$("#consefet").html(parseInt($("#consefet").html())+1);
})
</script>
