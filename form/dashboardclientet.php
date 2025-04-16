<?
include_once("../inc/php/validaacesso.php");

$_SESSION['arraynucleo']=array();
//die();
?>

<!-- Biblioteca do grafico -->   
<script src="inc/js/amcharts/amcharts.js"></script>
<script src="inc/js/amcharts/serial.js"></script>


<style>
.btn-default{
	height: 30px;
}
#cbModuloForm{
	min-width: 430px;
}
.divtab {
	display:none;
}
#chartdiv{
	position: fixed;
	left: 0px;
}
a[href*=amcharts]{
	display: none !important;
}
</style>

<style>
.divnucleo{
	border: 1px solid gray;
	background-color: rgb(225,225,225);
	padding: 5px;
	margin:5px;
	margin-bottom: 15px;
	font-weight: bold;
	color: gray;
	display:inline-table;
	
}
.divnucleovis,
.divnucleores{
	border-radius: 3px;
    display: inline-flex;
    font-weight: bold;
    margin: 5px 10px 20px;
    padding: 5px;
    position: relative;
    width: 30%;
}
.divnucleovis{
    background-color: #EFEFEF;
    border: 0px solid #e8e8e8;
    color: #8e8e8e;
}
.divnucleores{
    background-color: #ffffc2;/*amarelo*/
    border: 0px solid #F0F0F0;
    color: dimgray;
}
.divnucleovis:hover,
.divnucleores:hover{
    box-shadow: 0 2px 2px rgba(0,0,0,0.1);
    color: dimgray;
}

.divnucleovis > span,
.divnucleores > span{
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
	display: inline-block;
	min-width: 20px;
	width: auto;
}

.legenda{
	height: 15px;
	width: 15px;
	padding: 0px;
	margin: 0px;
}

.contadorNaovisualizados,
.contadorAlerta{
	border-radius: 2px;
	position: relative !important;
	top: -15px;
	padding: 0px 4px;
	margin-right: 4px;
    float: right !important;
	overflow: auto;
	text-overflow: initial !important;
	white-space: nowrap;
    box-shadow: 0 1px 5px rgba(90, 90, 90, 0.5);
}

.contadorNaovisualizados{
    background-color: #FFFF64;
}
.contadorNaovisualizados .iContadorNaovisualizados{
	font-size: 12px;
	font-weight: bold;
	padding: 1px;
}

.contadorAlerta{
    background-color: red;
}
.contadorAlerta .iContadorAlerta{
	font-size: 12px;
	font-weight: bold;
	padding: 1px;
	color: white;
}
.lb12{
	color: #B0B0B0;
	font-weight: bold;
}
.lbcatprodutos{
	display: block;
	color: #B0B0B0;
	margin-left: 6px;
}

.lbcatgranja{
	display: block;
	color: silver;
	margin: 3px 6px;
	font-size: 13px;
}
.lbcatgranja > i{
	font-size: 10px;
}
.contCliente{
	margin-top: 15px;
	background-color:transparent;
	display: inline-block;
	width: 48%;
	/**
	 * @todo:o min-width aqui está tentando corrigir uma falha existente quando o cliente possui somente 1 cliente e 1 núcleo;
	 */
	min-width: 430px; 
	vertical-align: top;
	margin-left: 1%;
}
.contCliente .contClienteHeader{
	background-color:#EFEFEF;
	font-weight: bold;
	height: 33px;
	line-height: 33px;
	-webkit-border-radius: 3px;
	-moz-border-radius: 3px;
	border-radius: 3px;
	transition: background-color 0.5s ease;
}
.contCliente:hover .contClienteHeader{
	background: gray;

}
.contCliente .contClienteHeader label{
	margin: 0px 8px;
	color: #646464;
	transition: color 0.5s ease;
}
.contCliente:hover .contClienteHeader label{
	color: white;
}
.frmdash{
	border: 1px solid #fdfdfd;
	background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAQAAAAECAYAAACp8Z5+AAAAHElEQVQIW2P89evXfzY2NkYGKIAzMARgKjFUAABkZQgFTkznEAAAAABJRU5ErkJggg==);
	background-color: white;
}
.togglespan{
	color: #d0d0d0;
	font-weight: bold;
	font-size: 10px;
	cursor:pointer;
	position: absolute;
	top: 5px;
	right: 10px
}
.togglespan:hover{
	color: #A2A2A2;
	text-decoration: underline;
}

.contClienteAcoes{
	float: right;
	white-space: nowrap;
}
.contClienteAcoes i{
	padding: 0px 8px;
	font-size: 20px;
	opacity: 0;
	color: white;
	cursor: pointer;
}
.contClienteAcoes i:hover{
	opacity: 1 !important;
}
.contCliente:hover .contClienteAcoes i{
	opacity: 0.5;
	vertical-align: middle;
}
.contClienteAcoes .contClienteSearchInput{
	display: none !important;
	font-size: 14px !important;
	font-weight: normal !important;
	padding: 0px 8px !important;
	height: auto !important;
	margin: 0px 4px !important;
    width: 0px;
    transition: width 0.5s ease;
}
.contClienteAcoes.ativo .contClienteSearchInput{
	display: inline-block !important;
	width: 150px;
}
.contClienteAcoes.ativo .contClienteSearchButton{
	color: white;
	opacity: 1 !important;
}
</style>
<?
$idcliente = $_GET['idcliente'];
$Aidcliente = explode(',', $idcliente);
?>
<div class="frmdash">
<div class="row">
	<div class="col-md-10 col-md-offset-1" >
		<div class="panel panel-default" style="margin-top:40px;">
			<div class="panel-heading">
			Filtros
			</div>
			
			<div class="panel-body">
				<div style="margin-bottom: 6px;" class="row">
				<div class="col-md-6">
				<label>Cliente:</label><br>
				<select id="idcliente" class="selectpicker" multiple="multiple" data-live-search="true">
<? 
if($_SESSION["SESSAO"]["IDTIPOPESSOA"] == 4){
    die("Secretarias devem usar o mesmo modulo disponibilidado para os clientes.");
}

if($_SESSION["SESSAO"]["IDTIPOPESSOA"] != 1){
    $strin=" and p.idpessoa in (".$_SESSION["SESSAO"]["STRCONTATOCLIENTE"].")";
}

$sql = "select distinct p.idpessoa, p.nome from pessoa p
	where p.status = 'ATIVO'
		and p.idtipopessoa = 2 
                ".$strin."
	order by p.nome";
	$res = mysql_query($sql);
	    while ($row = mysql_fetch_assoc($res)) {
			if (in_array($row['idpessoa'],$Aidcliente)){
				$selected= 'selected';
			}else{
				$selected= '';
			}
			echo '<option data-tokens="'.retira_acentos($row['nome']).'" value="'.$row['idpessoa'].'" '.$selected.' >'.$row['nome'].'</option>'; 
			
		}
		
		?>
	
</select>

			
				</div>
					<div class="col-md-6">
				<label>Resultado entre:</label><br>
				<div class="input-group">
				<input name="datainicial" id="datainicial" class="calendario" col="fabricacao" value="<?=$_GET['datainicial'];?>">
				<label class="input-group-addon">&nbsp;e&nbsp;</label>
				<input name="datafinal" id="datafinal" class="calendario" col="fabricacao" value="<?=$_GET['datafinal'];?>"></div>
				<!--<span id="cbDaterange3" cbdata="" class="input-group-addon pointer cinzaclaro hoverpreto">
					<i class="fa fa-calendar"></i>
					<span id="cbDaterangeTexto"></span>
				</span>-->
			
				</div>
				<div class="col-md-12">
				<button class="btn btn-primary pull-right" onclick="enviarFiltro();">
								&nbsp;Extrair
							</button>
				</div>
				</div>
				<div>
					<div id="chartdiv" style="height: 400px; min-width:99%; margin:8px; margin-top: 20px;"></div>
				</div>
			</div>
		</div>
	</div>
</div>	
	<script>
	function enviarFiltro(){

	var cliente = $("#idcliente" ).val();
	var datainicial = $( "#datainicial" ).val();
	var datafinal = $("#datafinal" ).val();
	
	
	if (cliente){
		var strcliente = '&idcliente='+cliente;
	}else{
		var strcliente = '';
	}
	if (datainicial){
		var strdatainicial = '&datainicial='+datainicial;
	}else{
		var strdatainicial = '';
	}	
	if (datafinal){
		var strdatafinal = '&datafinal='+datafinal;
	}else{
		var strdatafinal = '';
	}	
	
	
	
	$(location).attr('href', '/?'+window.location.search.replace("?", "").split("&")[0]+'&f=1'+strcliente+strdatainicial+strdatafinal);
}	
	</script>
<?

/*
 * Clientes ATIVOS configurados para o contato (usuário) logado
 */
 
 if (isset($_GET['f'])){
	 
	 if (isset($_GET['idcliente'])){
		 $cond .= " and p.idpessoa in (".$_GET['idcliente'].") ";
	 }
	 if (isset($_GET['datainicial'])){
		$vdatainicial = $_GET['datainicial'];
		$vdatainicial = str_replace('/', '-', $vdatainicial);
		$vdatainicial = date('Y-m-d', strtotime($vdatainicial));
		$cond2 .= " and a.dataamostra >= '".$vdatainicial."' ";
	 }
	 if (isset($_GET['datafinal'])){
		$vdatafinal = $_GET['datafinal'];
		$vdatafinal = str_replace('/', '-', $vdatafinal);
		$vdatafinal = date('Y-m-d', strtotime($vdatafinal));
		$cond2 .= " and a.dataamostra <= '".$vdatafinal."' ";
	 }
 $sqlcli = "select p.idpessoa, p.nome from pessoa p
	where status = 'ATIVO'
		and p.idtipopessoa = 2
		".$cond."
            ".$strin."
	order by p.nome";



//die($sqlcli);
if($inspecionarConsultas){
	echo "<!-- sqlcli: ".$sqlcli." -->";
}

//Consulta os clientes do contato
$rescli = mysql_query($sqlcli) or die("[f:" . __FILE__ . "][l:" . __LINE__ . "]: Erro ao recuperar lista de Clientes.<!-- " . mysql_error() . " -->");

if (mysql_num_rows($rescli) > 0) {
    
    while ($rcli = mysql_fetch_assoc($rescli)) {
        $quebracatprod    = true; //somente 1 vez para cada cliente realizar uma quebra no tipo de nucleo=[P]roduto
        $quebraoutros     = true; //somente 1 vez para cada grupo de idnucleo zerados, pois ele TEM de vir ordenados por ultimo na consulta de nucleos
        $rotsomenteoutros = true; //quando o IDPESSOA (cliente) nao possuir nenhuma amostra com nucleo informado, o rotulo deve ser diferente. Ex: "Resultados" ao inves de "Outros Resultados". A pedido de Daniel 26/02/13
        $quebracatgranja  = true;
?>


<div class="contCliente" cbnome="<?=$rcli["nome"]?>" cbidpessoa="<?=$rcli["idpessoa"]?>" onmouseleave="togglePesquisaNucleos('contClienteAcoes_<?=$rcli["idpessoa"]?>',false)">
	<div class="contClienteHeader">
		<label><?=$rcli["nome"]?></label>
		<div class="contClienteAcoes" id="contClienteAcoes_<?=$rcli["idpessoa"]?>">
			<i class="fa fa-eye" title="Marcar todos como 'Visualizados'" onclick="resetNotificacoesPorCliente('<?=$rcli["idpessoa"]?>')"></i>
			<i class="fa fa-search contClienteSearchButton" title="Filtrar os núcleos desta Unidade" onmouseenter="togglePesquisaNucleos('contClienteAcoes_<?=$rcli["idpessoa"]?>',true)"></i>
			<input type="text" class="contClienteSearchInput" placeholder="Filtrar Núcleo/Lote" onkeyup="filtrarNucleos(this)">
		</div>
	</div>
<?
        
        $class = "";


		
        $sqln = "select * 
				from(
					select n.idpessoa, n.idnucleo, if(ifnull(n.lote,'')='',n.nucleo,concat(n.lote,' - ',n.nucleo)) as nucleo, n.idnucleotipo, n.lote, n.criadoem
					from nucleo n
					where 
						n.idpessoa = " . $rcli["idpessoa"] . "
						and n.situacao = 'ATIVO'
					union
					select " . $rcli["idpessoa"] . ",0, 'Outros Resultados', 'G','', ''
				) n2
				WHERE 1 
				order by
					if(n2.idnucleo=0,'',ifnull(n2.idnucleotipo,'G')) desc -- [G]ranjas primeiro, [F]abricantes depois e [idnucleo=0] Outros depois
					,if(n2.idnucleo=0,'ZZZZZ','') -- Mostrar amostras sem nucleos ou lotes no final da listagem
					,n2.nucleo";
        
        if($inspecionarConsultas){
        	echo ("<!-- sqln: " . $sqln . " -->");
        }

        $resn = mysql_query($sqln) or die("Falha ao selecionar nucleos do cliente: " . mysql_error() . "<p>SQL: " . $sqln);
        
        while ($rown = mysql_fetch_array($resn)) {
               
	       	//maf200216: As inconsistencias de dashboard X idpessoa da amostra serao tratadas em outro lugar. Para facilitar o rastreamento de falhas, neste ponto teremos somente a tabela de dashboard
                $sqld = "select 
						count(alerta) as sumalerta,
						count(alerta) as sumalertanovo
					from
						resultado r
					join
						amostra a on (a.idamostra = r.idamostra)
					join
						nucleo n on (n.idnucleo = a.idnucleo)
					where
						alerta = 'Y' and
						n.idnucleo = " . $rown["idnucleo"] . "
						and n.idpessoa = " . $rown["idpessoa"] 
					
					. $cond2;

            if($inspecionarConsultas){
            	echo "<!-- sqld: ".$sqld." -->";
            }
            
            $resd = mysql_query($sqld) or die("Falha ao verificar dashboard do Usuário: " . mysql_error() . "<p>SQL: " . $sqld);
            
            $rowd = mysql_fetch_assoc($resd);
            
            
            //Prepara parametro que sera utilizado para restringir resultados nao visualizados na tela de resultados da pesquisa
			if($rowd["sumnvisualizado"] > 0 or $rowd["sumalertanovo"]>0){
				$vnaovis = "N";
				$rotvis="Não Visualizados";
			}else{
				$vnaovis = "Y";
				$rotvis="Visualizados";
			}
            
            
            //Monta Json para passar via GET (javascript). Esses parà¢metros serão passados via GET ao mà³dulo _modulofiltrospesquisa, que processará os campos conforme a clausula where
            //maf: o IDPESSOA (cliente) sera comparado com o campo IDCLIENTE na tabela dashboardnucleopessoa
            
			$arrAf = array(
						array(
							"col" => "idpessoa",
							"id" => $rown["idpessoa"],
							"valor" => rawurlencode($rcli["nome"])
						),
				
						array(
							"col" => "idnucleo",
							"id" => $rown["idnucleo"],
							"valor" => rawurlencode($rown["nucleo"])
						)
					);
			//Inclui opção de visualizados. Caso não se deseje "não visualizados", retornará qualquer status
			if($vnaovis=="N"){
				array_push($arrAf, 
					array(
						"col" => "statusvisualizacao",
						"id" => $vnaovis,
						"valor" => rawurlencode($rotvis)
					)
				);
			}
			
			//Transforma o array em json
            $_strjson = urlencode(
				json_encode(
					$arrAf
				)
			);
            

            /*
             * maf: mostra um pequeno rotulo logo acima dos nucleos conforme o tipo de granja, que vira ordenado na consulta
             */
            if ($rown["idnucleotipo"] == "G" and $quebracatgranja == true) {
                $rotsomenteoutros = false;
?>
<span class="lbcatgranja">
<i class="fa fa-tags"></i>
Lotes/Núcleos Vivos:
</span>
<?
                $quebracatgranja = false;
            }
            
            if ($rown["idnucleotipo"] == "F" and $quebracatprod == true) {
                $rotsomenteoutros = false;
?>
<span class="lbcatprodutos">
<ul class="tri">
	<li>Produtos:</li>
</ul>
</span>
<?
                $quebracatprod = false;
            }
            
            if ($rown["idnucleo"] == 0 and $quebraoutros == true) {
				$quebracatprod = false;
?>
<br/>
<?
                $quebraoutros = false;
            }



            // se possuir teste sem ter sido visualizado muda a cor
            if($rowd["sumnvisualizado"] > 0){
                
                $class = "divnucleores";
                //$outra = "javascript:apagacontador(this);janelamodal('?_modulo=dashboardclientefiltro&_autofiltro=" . $_strjson . "',screen.availHeight,screen.availWidth); apagacontador(this);";
                $outra = "javascript:popNucleo(this,'N')";
                $outra = '"' . $outra . '"';

			}elseif($rowd["sumalerta"] > 0 and $rowd["sumalertanovo"] > 0){

                $class = "divnucleores";
                //$outra = "javascript:apagacontador(this);janelamodal('?_modulo=dashboardclientefiltro&_autofiltro=" . $_strjson . "',screen.availHeight,screen.availWidth); apagacontador(this);";
                $outra = "javascript:popNucleo(this,'N')";
                $outra = '"' . $outra . '"';
				
            }else{
                
                $class = "divnucleovis";
                //$outra = "javascript:janelamodal('?_modulo=dashboardclientefiltro&_autofiltro=" . $_strjson . "',screen.availHeight,screen.availWidth);apagacontador(" . $rown["idnucleo"] . "); apagacontador(this);";
                $outra = "javascript:popNucleo(this,'Y')";
                $outra = '"' . $outra . '"';
                
            }

			/*
			 * Escreve o conteudo do nucleo
			 */
            //$rotnucltmp = $rotsomenteoutros . $rown["nucleo"];//maf180917: Isto estava escrevendo o numeral "1" na tela
	    $rotnucltmp = $rown["nucleo"];
            
            echo "<div cbidnucleo='" . $rown["idnucleo"] . "' cbnucleo='".$rown["nucleo"]."' class='" . $class . "' title='".$rotnucltmp."' style='cursor: pointer'; onclick=" . $outra . ">";
            echo "<span>";
            echo $rotnucltmp;
            echo "</span>";

            if ($rowd["sumnvisualizado"] > 0) {
?>
			<span class="contadorNaovisualizados" title="Resultados Não Visualizados">
				<span class="iContadorNaovisualizados"><?= $rowd["sumnvisualizado"] ?></span>
			</span>
<?
            } //while nucleos
            
            if ($rowd["sumalerta"] > 0) {
				if($rowd["sumalertanovo"]>0){
					$vcx = "contadorAlerta";
				}else{
					$vcx = "contadorAlerta";
				}
?>
			<span class="contadorAlerta" title="Resultados Com Alerta">
				<span class="iContadorAlerta"><?= $rowd["sumalerta"] ?></span>
			</span>
<?
            }
            
            echo "</div>";
            
        } //while nucleo
?>
</div>
<?
    } //while cliente
}
 }
?>


<fieldset style="background-color:transparent; margin-left: 2%; margin-right: 15px; margin-top: 15px; border:none; border-top: 1px dotted silver;">
	<legend style="color:#818181">Legenda:</legend>
	<div>
		<div class="divnucleores" style="height: 28px; width: 30px;">
		<span class="contadorNaovisualizados" style="top: -12px;right: -2px;">
			<span class="iContadorNaovisualizados" style="font-size:10px;">1</span>
		</span>
		</div>
		<span style="color:#818181;">Resultados Não Visualizados</span>
	</div>
	<div>
		<div class="divnucleores" style="height: 28px; width: 30px;">
		<span class="contadorAlerta" style="top:-12px;right: -2px;">
			<span class="iContadorAlerta" style="font-size:10px;">1</span>
		</span>
		</div>
		<span style="color:#818181;">Resultados com Alerta não visualizados</span>
	</div>
</fieldset>
<style>

</style>
<script>
function printNucleo(inUrl){

	this.goUrlImpressao = function(){
		var vUrlPrint = "report/emissaoresultado.php?"
		
		ids="";
		virg="";
		$.each($("#cbModalCorpo #restbl tbody tr"), function(k,v){
			vid = $(v).attr("goparam").split("=")[1];
			if(vid.length>=1){
				ids += virg + $(v).attr("goparam").split("=")[1];
				virg=",";
			}
		})
		janelamodal(vUrlPrint+"_vids="+ids);
	}

	var iRes = $("#resultadosEncontrados").attr("cbnumrows");
	
	if(parseInt(iRes)>50){
		if(confirm("Deseja realmente imprimir "+iRes+" resultados?")){
			goUrlImpressao();
		}
	}else{
		goUrlImpressao();
	}
}
function popNucleo(inONucleo,inVis){
	
	var oNucleo = $(inONucleo);
	var idNucleo = oNucleo.attr("cbidnucleo");
	var nucleo = oNucleo.attr("cbnucleo");
	
	var oCliente = oNucleo.closest(".contCliente"); 
	var nome = oCliente.attr("cbnome");
	var idPessoa = oCliente.attr("cbidpessoa");

	if(idPessoa=="" || idPessoa==undefined){
		idPessoa=idPessoa||"";
		console.error("js: popNucleo: idPessoaVazio!");
	}

	vGetAutofiltro = "_modulo=dashboardclientefiltro&_autofiltro=[{\"col\":\"idpessoa\",\"id\":\""+idPessoa+"\",\"valor\":\""+nome+"\"},{\"col\":\"idnucleo\",\"id\":\""+idNucleo+"\",\"valor\":\""+nucleo+"\"}]&_ordcol=idamostra&_orddir=desc";

	vGet = "_modulo=dashboardclientefiltro&_pagina=0&_ordcol=idamostratra&_orddir=desc&idpessoa="+idPessoa+"&idnucleo="+idNucleo;

	var strCabecalho = "</strong>"+nucleo+"</strong>";

	//Altera o cabeçalho da janela modal
	$("#cbModalTitulo")
				.html(strCabecalho)
				.append("<i class='fa fa-eye' title='Marcar como visualizados' onclick=\"resetNotificacoesPorNucleo(\'"+idNucleo+"\')\"></i>")
				.append("<i class='fa fa-print' id='btPrintNucleo' title='Impressão' onclick=\"printNucleo()\"></i>")
				.append("<label id='resultadosEncontrados'></label>");

	//Realiza a chamada da pagina de pesquisa manualmente
	$.ajax({
		context: this,
		type: 'get',
		cache: false,
		url: 'form/_modulofiltrospesquisa.php',
		data: vGet,
		dataType: "json",
		beforeSend: function(){
			alertAguarde();
		},
		success: function(data, status, jqXHR){

			//Json contem resultados encontrados?
			if(!$.isEmptyObject(data)){
				//Nos casos onde existia um número muito grande linhas, o browser estava apresentando lentidão. Caso o número de linhas seja > configuracao do Mà³dulo, direcionar para tela de search
				if(parseInt(data.numrows)>parseInt(CB.jsonModulo.limite)||data.numrows>200){
					alertAtencao("Mais de "+CB.jsonModulo.limite+" resultados foram encontrados!\n<a href='?" + vGetAutofiltro+"' target='_blank' style='color:#00009a;'><i class='fa fa-filter'></i> Clique aqui para filtrar os resultados encontrados.</a>");
					janelamodal("?" + vGetAutofiltro);
				}else{
					$("#cbModal").addClass("noventa").modal();
					var tblRes = CB.montaTableResultados(data, function(obj, event){

						oTr = $(obj);
						oTr.css("backgroundColor","transparent");
						
						janelamodal("report/emissaoresultado.php?" + oTr.attr("goParam"));
					});
					$("#cbModal #cbModalCorpo").html(tblRes);

					if(data.numrows){
						$("#resultadosEncontrados").html("("+data.numrows+" resultados encontrados)").attr("cbnumrows",data.numrows);
					}
				}
			}else{
				if(inVis=="N"){
					//Um objeto json vazio retornou
					alert("Nenhum resultado encontrado.\nProvavelmente o Núcleo passou por alteraçàµes posteriores à  notificação.\n\nInforme o erro pelo email resultados@laudolab.com.br.");
				}else{
					alert("Nenhum resultado encontrado.");
				}
			}
		},
		complete: function(){
			CB.aguarde(false);
			if(CB.limparResultados==true){
				CB.resetDadosPesquisa();
			}
		}
	});
	
}

/*
 * Callback do Modal: ao fechar: efetuar refresh no dashboard
 */
$('#cbModal').one('hide.bs.modal', function(){
	CB.inicializaModulo();
});

/*
 * Limpar notificaçàµes para todos os núcleos do cliente informado
 */
function resetNotificacoesPorCliente(inIdpessoa){
	$.ajax({
		type: "get",
		url : "ajax/dashboardclientefiltro_resetnotificacoes.php",
		data: "idpessoa="+inIdpessoa

	}).done(function(data, textStatus, jqXHR){
		if(jqXHR.getResponseHeader("X-CB-RESPOSTA")=="1"){
			alertAzul(data);
			//Refresh no dashboard
			CB.inicializaModulo();
		}else{
			alert(data);
		}
	});
}

/*
 * Limpar notificaçàµes do núcleo informado
 */
function resetNotificacoesPorNucleo(inIdnucleo){
	$.ajax({
		type: "get",
		url : "ajax/dashboardclientefiltro_resetnotificacoes.php",
		data: "idnucleo="+inIdnucleo

	}).done(function(data, textStatus, jqXHR){
		if(jqXHR.getResponseHeader("X-CB-RESPOSTA")=="1"){
			alertAzul(data);
			//Refresh no dashboard
			CB.inicializaModulo();
		}else{
			alert(data);
		}
	});
}


function togglePesquisaNucleos(inObjAcoes, inMostrar){
	if(inMostrar===true){
		//Mostra o input e coloca o foco nele
		$("#"+inObjAcoes).addClass("ativo").find(".contClienteSearchInput").focus();
	}else{
		$("#"+inObjAcoes).removeClass("ativo");
	}
}

function filtrarNucleos(inObjSearch){

	oSearch = $(inObjSearch);
	oNucleos= oSearch 
					.closest(".contCliente") //Encontra o pai
					.find("[cbnucleo]"); //Separa os nucleos

	strSearch = oSearch.val();
	console.log(strSearch);
	
	if(strSearch==""){
		oNucleos.fadeIn(200);
	}else{

		oNucleos
			.filter(function() {
				//Transforma para minúsculas e transforma os acentos em vogais desacentuadas
				strSearch = accent_fold(strSearch.toLowerCase());
				var re = new RegExp(strSearch,"i");

				//Transforma para minúsculas e transforma os acentos em vogais desacentuadas
				var strNucleo = $(this).attr('cbnucleo');
				strNucleo = accent_fold(strNucleo.toLowerCase());

				//Testa as 2 string
				var bMatch = re.test(strNucleo);
	    		return !bMatch;
			})
			.fadeOut(200);
	}
}

//Montar legenda para o usuário
CB.montaLegenda({"#FFFF64": "Existem resultados não-visualizados", "#FF0000": "Existem resultados marcados com Alerta"});
CB.oPanelLegenda.css( "zIndex", 901);

 $('#idcliente').selectpicker('render');
/*$('#cbDaterange3').daterangepicker({locale: {
	
      format: 'DD/MM/YYYY',
		separator: ' - ',
		applyLabel: 'Ok',
		cancelLabel: 'Limpar',
		fromLabel: 'De',
		toLabel: 'Até',
		customRangeLabel: 'Outro intervalo',
		daysOfWeek: ['Do','Se','Te','Qu','Qi','Se','Sa'],
		monthNames: ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'],
		firstDay: 1,
	  opens: 'left'
}
	, ranges: {
		           'Hoje': [moment(), moment()],
		           'Ontem': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
		           '7 dias': [moment().subtract(6, 'days'), moment()],
		           'àltimos 30 dias': [moment().subtract(29, 'days'), moment()],
				   'Prà³ximos 7 dias': [moment(), moment().add(6, 'days')],
		           'Prà³ximos 30 dias': [moment(), moment().add(29, 'days')],
		           'Este màªs': [moment().startOf('month'), moment().endOf('month')],
		           'Màªs passado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
				   'àltimos 365 dias': [moment().subtract(365, 'days'), moment()]
		        }});
//o comentario abaixo faz com que este pedaço de script apareça na aba 'sources' do inspetor do google chrome
//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?> */
</script>



</div>