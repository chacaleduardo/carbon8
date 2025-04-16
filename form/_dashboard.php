<?
require_once("../inc/php/validaacesso.php");
function br2nl( $input ) {
    return preg_replace('/<br\s?\/?>/ius', "\n", str_replace("\n","",str_replace("\r","", htmlspecialchars_decode($input))));
}
//echo '<!--';
//print_r(getModsUsr("MODULOS"));

//echo '-->';
$sql = "update immsg set status = 'L', lidoem = now() where idpessoa=".$_SESSION["SESSAO"]["IDPESSOA"]." and not tipo = 'M';";

$rs = d::b()->query($sql) or die($msgerro.": ". mysqli_error(d::b()));

$msgerro="Erro ao recuperar tarefas";
/* TAREFAS EM ABERTO COM AS TAREFAS FINALIZADAS*/
$ss = "
			SELECT * FROM (
				SELECT m.idimmsg
					,b.idimmsgbody
					,ifnull(b.msg, m.descr) as msg
					,m.tipo
					,if(b.idpessoa=".$_SESSION["SESSAO"]["IDPESSOA"].",'eu',b.idpessoa) as sender
					,'pessoa' as objetocontato
					,m.status
					,m.statustarefa
					,m.criadoem
					,m.datatarefa
					,m.alteradoem
				FROM immsg m force index(idpessoa_idimmsg) 
					 JOIN immsgbody b on (b.idimmsgbody = m.idimmsgbody) 
				WHERE -- m.idpessoa = ".$_SESSION["SESSAO"]["IDPESSOA"]."
					-- a clausula idimgrupo evita que o usuario sender veja as tarefas individuais dos destinatarios no seu dashboard
					 (m.idpessoa = ".$_SESSION["SESSAO"]["IDPESSOA"]." -- or b.idpessoa=".$_SESSION["SESSAO"]["IDPESSOA"]." and m.idimgrupo is null
					 ) 
                    and m.tipo in ('T','A')
					and m.statustarefa = 'A'
				-- GROUP BY b.idimmsgbody
				ORDER BY m.datatarefa, idimmsg DESC
				LIMIT 200
			) a
			
			";
			/*union
			SELECT * FROM (
				SELECT m.idimmsg
					,b.idimmsgbody
					,ifnull(b.msg, m.descr) as msg
					,m.tipo
					,if(b.idpessoa=".$_SESSION["SESSAO"]["IDPESSOA"].",'eu',b.idpessoa) as sender
					,'pessoa' as objetocontato
					,m.status
					,m.statustarefa
					,m.criadoem
					,m.datatarefa
					,m.alteradoem
				FROM immsg m force index(idpessoa_idimmsg) 
					 JOIN immsgbody b on (b.idimmsgbody = m.idimmsgbody) 
				WHERE -- m.idpessoa = ".$_SESSION["SESSAO"]["IDPESSOA"]."
					-- a clausula idimgrupo evita que o usuario sender veja as tarefas individuais dos destinatarios no seu dashboard
					 (m.idpessoa = ".$_SESSION["SESSAO"]["IDPESSOA"]." -- or b.idpessoa=".$_SESSION["SESSAO"]["IDPESSOA"]." and m.idimgrupo is null
					 ) 
                    and m.tipo in ('T','A')
					and m.statustarefa = 'F'
				-- GROUP BY b.idimmsgbody
				ORDER BY m.datatarefa, idimmsg DESC
			)b */
//die($ss);
$rs = d::b()->query($ss) or die($msgerro.": ". mysqli_error(d::b()));

function recuperaDestinos($inIdimmsgbody,$inArray=false){
	
	if(empty($inIdimmsgbody)){
		return false;
	}else{
		$sd = "select mb.idpessoa as sender, m.idimmsg, p.idpessoa, p.nomecurto, m.status, m.statustarefa, m.descr, DATE_FORMAT(m.alteradoem, '%d/%m/%Y %H:%m:%s') as alteracao
			from immsg m force index(idimmsgbody)
			join pessoa p on p.idpessoa=m.idpessoa
			join immsgbody mb on mb.idimmsgbody = m.idimmsgbody
			where m.idimmsgbody=".$inIdimmsgbody." and tipo = 'T'
			order by m.alteradoem desc, p.nome";
		$rd = d::b()->query($sd) or die("recuperaDestinos: ".mysqli_error(d::b()));
		
		$sDestinos="";
		$aDestinos=array();
		while ($r=mysqli_fetch_assoc($rd)){
			
			if($inArray){
				$aDestinos[$r["idpessoa"]]=$r["nome"];
			}else{
				 
				$r["descr"] = str_replace('"', '*', $r["descr"]);
				
				
				
				if ($r["sender"] != $r["idpessoa"]){
					$descr = "<span>[".$r["alteracao"]."] ".$r["nome"]."<br><br>".$r["descr"]."</span><script>$('#text".$inIdimmsgbody."').dblclick(function() {mostraInputComentario(".$r["idimmsg"].", '".($r["descr"])."', ".($inIdimmsgbody).");});</script>";
					if ($r["descr"] != ''){
						$st = 'S';
					}else{
						$st = 'N';
					}
					
				}else{
					$descr = "";
					$st = 'N'; 
					
				}
				
				if (($r["idpessoa"] == $_SESSION["SESSAO"]["IDPESSOA"]) and $r["idpessoa"] != $r["sender"]){
					$edt = "<span class='fa fa-edit' style='font-size: 14px; margin-left: 4px; color: #4cae4c; margin-bottom: 2px;cursor: pointer;'><text style='font-size:7px;font-family:Helvetica Neue,Helvetica,Arial,sans-serif'>RESPONDER</text></span>";
				}else{
					$edt = '';
				}
				
				if (empty($r["alteracao"])){
					$r["alteracao"] = '-';
				}
				
				$sDestinos.="<div class='row padding-0' style='border-bottom:1px solid #ddd;margin:4px'><div class='col-md-12 padding-0'><div class='row padding-0'>
				
				
				<div class='col-md-9 padding-0'><div style='font-size:11px;margin-top:1px;    padding: 0px 20px;' onclick=\"mostraInputComentario(".$r["idimmsg"].", '".($r["descr"])."', ".($inIdimmsgbody).");\">".$r["descr"]."</div></div><div class='col-md-3 padding-0' style='padding:0px 20px !important' onclick=\"mostraInputComentario(".$r["idimmsg"].", '".($r["descr"])."', ".($inIdimmsgbody).");\" ><span class='comentario' status=".$st." id='".$r["idimmsg"]."'></span><span class='contato' title='".$r["nome"]."'  idpessoa='".$r["idpessoa"]."' status='".$r["status"]."' descr='".$r["descr"]."' statustarefa='".$r["statustarefa"]."' ><i></i>".$r["nomecurto"]."".$edt."</span>
				<br><span style='float:right;font-size:9px; color:#999; margin-right:16px;'>".$r["alteracao"]."</span>
				</div></div></div></div>
				
				";
		
				
			}
		}
		
		if($inArray){
			return $aDestinos;
		}else{
			return $sDestinos;
		}
	}
}

?>
&nbsp;
<script>
$('.hasTooltip').hover(function() {
    var offset = $(this).offset();
    $(this).next('span').fadeIn(200).addClass('showTooltip');
    //$(this).next('span').css('left', offset.left + 'px');
}, function() {
    $(this).next('span').fadeOut(200);
});

</script>
<style>
#chatNovaMensagemPopup{
	height: auto !important;
}
.textareacomentario{
	background-color:#ddeaff;
	border: 2px solid #fff;
	font-size: 13px;
	height: 80px;
	white-space: pre-wrap;
}
.hasTooltip + span {
    display:none;
}

.showTooltip {
    background-color: #666;
    color: #fff;
    padding: 10px;
    position: absolute;
    width: 400px;
    -moz-border-radius: 5px;
    -webkit-border-radius: 5px;
    border-radius: 5px;
    z-index: 100;
    display: block;
    right: 130px;
    bottom: 8px;
}

.showTooltip:after {
    content: '';
    position: absolute;
    right: 0;
    bottom: 0%;
    width: 0;
    height: 0;
    border: 29px solid transparent;
    border-left-color: #666;
    border-right: 0;
    border-bottom: 0;
    margin-top: -14.5px;
    margin-right: -12px;
}


.statustarefaF{
	display: none;
}
.statustarefaF .visivel{
	display: table-row;
}

input[filtrarElementos]{
	background-image: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAPCAYAAADkmO9VAAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH4gMXEwoCF7eRUAAAABl0RVh0Q29tbWVudABDcmVhdGVkIHdpdGggR0lNUFeBDhcAAAFcSURBVDjLvZQxSxxBHMV/z7tdLxiutTQfIIUGbgsrRUK+QCC1pBBuuLNUEKMhYoxFIJm7Qrs0VpLGWkKaBEY7v4oew63eP80KQd1kBfHBwPAePP7z3szAA0NlgnPupaR14HlBnUn64r3//i/DWonZB0n7wBTwpFhTwJssyxohhOMyw7E7zBYkrQG5ma0Oh8OJRqPxVNIykAMr3W53vsywfisDaaOIYr3f7+/+JX11zk1I2h6NRu+AH5UmBGYA6vX6/k0hTdO9Yvui8pGvMRgM7CYXY7T/tXyX4SlAkiSLtxqs1ZaKWE7uk+FnM5uT9Mk5N57nuW82m4oxdoAtMzNgq/KE3vsj4COQSNpJ0/QixnhecDVJArJ73cMQwnGr1fol6RkwCVwCvyV9A2aBV1mWEUL4WfmllKHT6bw2swMgATZ7vd77Si2XwXt/aGZvgSszSx/sE2i329M8Bv4ALz9pL/EuKdwAAAAASUVORK5CYII=');
	background-repeat: no-repeat;
	padding-left: 19px;
	background-position: right;
	background-color: rgba(255,255,255,0)!important;
    padding: 4px!important;
    width: 22px!important;
	border-color: transparent!important;
	box-shadow: none!important;
    font-size: 12px!important;
    height: 20px!important;
	cursor: pointer;
	-webkit-transition: width 500ms ease-in-out, padding-left 500ms ease-in-out, padding-right 500ms ease-in-out, border-color ease-in-out .15s, box-shadow ease-in-out .15s!important;
    -moz-transition: width 500ms ease-in-out, padding-left 500ms ease-in-out, padding-right 500ms ease-in-out;
    -o-transition: width 500ms ease-in-out, padding-left 500ms ease-in-out, padding-right 500ms ease-in-out;
    transition: width 500ms ease-in-out, padding-left 500ms ease-in-out, padding-right 500ms ease-in-out, border-color ease-in-out .15s, box-shadow ease-in-out .15s!important;
}
input[filtrarElementos]:focus{
	-webkit-transition: width 1s ease-in-out;
    -moz-transition: width 1s ease-in-out;
    -o-transition: width 1s ease-in-out;
    transition: width 1s ease-in-out;
	
	background-color: white!important;
	width: 100%!important;
	border-color: 'original';
	box-shadow: 'original';
}

.destino{
	max-height: 200px;
	/* overflow-y: scroll; */
	width:120px;
}

span.comentario {
	display: block;
    position: relative;
    float: left;
	width:15%;
	margin-left: 20px;
}
span.comentario i{
	margin:0 3px;
	min-width: 11px;
    display: inline-block;
}
span[status=N].comentario i:before{
    font-family: "FontAwesome";
	content: "\f075";
	color: silver;
	
}
span[status=S].comentario i:before{
    font-family: "FontAwesome";
	content: "\f075";
	color: #4FC3F7;
	
}
.padding-0{
	padding:0px !important;
}
span.contato {
	display: block;
    position: relative;
    float: left;
	font-size:9px;
	margin-top: 3px;
}
span.contato i{
	margin:0 3px;
	min-width: 11px;
    display: inline-block;
	font-size: 12px;
}
span[status=N].contato i:before{
    font-family: "FontAwesome";
	content: "\f070";
	color: silver;
	
}
span[status=L].contato i:before{
    font-family: "FontAwesome";
	content: "\f00c";
	color: #4FC3F7;
	
}
span[statustarefa=F].contato i:before{
    font-family: "FontAwesome";
	content: "\f00c";
	color: #449d44;
	
}

.btn-group button.selecionado1 {
    background-color: orange;
    color: white;
    border: 1px solid #d88e06;
}
#tbTarefas .panel-heading{
    color: inherit;
    font-weight: inherit;
	cursor: pointer;
    font-size: 11px;
    color: #333;
	padding-top: 0px;
	text-transform:uppercase;
}

#tbTarefas .panel-heading[data-toggle][href]:hover {
    text-decoration: none !important;
}

#tbTarefas .collapse {
  display: none;
}

#tbTarefas .collapse.in {
  display: block;
}


#tbTarefas .arrow_show {
    display: inline;
}


div[aria-expanded=true] .glyphicon-chevron-right{
	display: none;
}

div[aria-expanded=false] .glyphicon-chevron-right{
	display: inline-block;
	color:orange;
	margin-right:4px;
}

div[aria-expanded=true] .glyphicon-chevron-down{
	display: inline-block;
	color:orange;
	margin-right:4px;
}

div[aria-expanded=false] .glyphicon-chevron-down{
	display: none;
}

div[aria-expanded="false"] resumo{
  display:initial;
}

div[aria-expanded="true"] resumo{
  display:none;
}


div[aria-expanded="false"] texto{
  display:none;
}

div[aria-expanded="true"] texto{
  display:initial;
}

</style>
	<div class="panel panel-default">
		<div class="panel-heading hidden">Tarefas</div>
		<div class="panel-body">
			<table class="table table-condensed" id="tbTarefas" style="position: relative">
				<thead>
				<tr>
					<td colspan="99">
						<button class="btn btn-xs btn-success" onclick="novaTarefa()">Nova Tarefa</button>
						<input type="text" filtrarElementos="tbTarefas">
						<div class="btn-group" role="group">
						  <button type="button" class="btn btn-default btn-xs fonte08" id="btn-1" onclick="toggleFiltrarMinhasTarefas(true)">Minhas Tarefas</button>
						  <button type="button" class="btn btn-default btn-xs fonte08 selecionado" id="btn-2" onclick="toggleFiltrarMinhasTarefas(false)">Todas</button>
						</div>
						<div class="btn-group" role="group">
						  <button type="button" class="btn btn-default btn-xs fonte08 selecionado hide" id="btn-3" onclick="toggleMostrarTarefasFinalizadas(false)">Pendentes</button>
						  <button type="button" class="btn btn-default btn-xs fonte08 hide" id="btn-4" onclick="toggleMostrarTarefasFinalizadas(true)">Todas</button>
						</div>
					</td>
				</tr>
				<tr>
					<th style="width:16%">Criador</th>
					<th style="width:44%">Tarefa</th>
					<th style="width:8%">Data Tarefa</th>

					<th style="width:16%"></th>
					
				</tr>
				</thead>
<?
while ($r=mysqli_fetch_assoc($rs)){
	$r["statustarefa"]=empty($r["statustarefa"])?"A":$r["statustarefa"];
	$idsender=$r["sender"]=="eu"?$_SESSION["SESSAO"]["IDPESSOA"]:$r["sender"];
	$origem=ucwords(strtolower(getObjeto("pessoa",$idsender)["nomecurto"]))."<br>". dmahms($r["criadoem"],true);
	$icostatus=$r["statustarefa"]=="A"?"fa fa-check-circle fa-2x vermelho hoververde":"fa fa-check-circle-o fa-2x verde hoververmelho fade";
	$titlestatus=$r["statustarefa"]=="A"?"Concluir tarefa":"Reabrir tarefa";

?>				
				<tr id="idimmsgbody_<?=$r["idimmsgbody"]?>" class="statustarefa<?=$r["statustarefa"]?>" sender="<?=$r["sender"]?>" status="<?=$r["status"]?>" statustarefa="<?=$r["statustarefa"]?>" idimmsg="<?=$r["idimmsg"]?>" style="background: #f9f9f9;border: 3px solid whitesmoke;">
				
					<td style="font-size: 11px; color: #333; width:100px" infotarefa="<?=$r["idimmsg"]?>">
						
						<?=$origem?>
					</td>
					<td>
					<div class="panel panel-default" style="border:none;background:#f9f9f9;box-shadow:none;">   
	    <div class="panel-heading" data-toggle="collapse" href="#collapse_<?=$r["idimmsgbody"]?>" style="background-color: #e6e6e6; border-bottom: none !important; line-height: 22px; border: 1px solid #ddd; width:750px" aria-expanded="false">
		<em class="glyphicon glyphicon-chevron-right"></em>
		<em class="glyphicon glyphicon-chevron-down"></em>
		<resumo><?=substr(strip_tags($r["msg"]),0,90);?>...</resumo>
		<? if ('eu' != $r["sender"]){ ?>
		
		
			<texto>
			<div style="position: relative; left: 18px; top: -22px;" id="text<?=$r["idimmsgbody"];?>"><?=$r["msg"];?></div>
			</texto>
			
		<? } ?>
		</div>
	    <div class="panel-body" style="padding:0px;">
		
			<table class="table table-striped planilha collapse"  id="collapse_<?=$r["idimmsgbody"]?>" style="background:#fff; border: 1px solid #ddd;width:750px; border-top: none !important;" >
			<? if ('eu' == $r["sender"]){ ?>
			<tr>
			<td style="background-color: #e6e6e6; border-bottom: none !important; line-height: 22px; border: 1px solid #ddd; width: 750px;border-top: none !important;">
			<texto>
		<table><tr><td style="width:750px; top: -26px; position: relative; left: 18px;"  class="editavel" id="msg_<?=$r["idimmsgbody"]?>" onblur="alteraMsgTarefa(<?=$r["idimmsgbody"]?>)"><?=$r["msg"]?></td></tr></table>
		</texto>
			</td>
			</tr>
			<? }else{ ?>
			<tr><td><div class="" id="input<?=$r["idimmsg"];?>"></div></td></tr>
			<? } ?>
			<tr><td width="750">
			<div class="col-md-9"><small><b>MENSAGEM:</b></small></div>
			<div class="col-md-3"><small><b>DESTINATàRIOS:</b></small></div>
			
			<BR><div  ><?=recuperaDestinos($r["idimmsgbody"])?></div></td></tr>
			</table>
	    </div>
	    </div>
					</td>

					
					
					
					<td id="data_<?=$r["idimmsg"]?>" class="calendario" onchange="alteraDataTarefa(<?=$r["idimmsg"]?>)">
<?
	$dt="";
	if($r["datatarefa"]!="0000-00-00 00:00:00"){
		$dt=dma($r["datatarefa"]);
	}
?>
						<?=$dt?>
					</td>
					
					<td>
						<i style='float:right;  margin-right:4px;' class="fa fa-share-alt fa-2x pointer fade hoverlaranja" title="Compartilhar tarefa" onclick="popupCompartilharTarefa(<?=$r["idimmsgbody"]?>)"></i>
<?
	foreach(getImArquivos($r["idimmsgbody"],"HTML") as $idarq=>$arq){
		$title= substr($arq["nome"],0,80);//Evitar strings de html grandes demais
?>
						<a href="<?=$arq["arq"]?>" target="_blank"><i style='float:right; margin-right:4px;' class="fa fa-paperclip fa-2x azul pointer" title="<?=$title?>"></i></a>
<?
	}
?>
<i style='float:right; margin-right:4px;'  id="statustarefa_<?=$r["idimmsg"]?>" idimmsg="<?=$r["idimmsg"]?>" class="pointer <?=$icostatus?>" statustarefa="<?=$r["statustarefa"]?>" title="<?=$titlestatus?>" onclick="alteraStatusTarefa('<?=$r["idimmsg"]?>')"></i>
<? if ('eu' == $r["sender"]){ ?>
						<i style='float:right; margin-right:4px;font-size: 24px;' class="fa fa-trash vermelho fade pointer" title="Excluir" onclick="chat.apagarMsg(<?=$r["idimmsgbody"]?>);CB.loadUrl({urldestino: 'form/_dashboard.php'});"></i>
					<? } ?>
					</td>
					
				</tr>
<?	
}
?>
			</table>
		</div>
	</div>
<script>
	function NOW() {

    var date = new Date();
    var aaaa = date.getFullYear();
    var gg = date.getDate();
    var mm = (date.getMonth() + 1);

    if (gg < 10)
        gg = "0" + gg;

    if (mm < 10)
        mm = "0" + mm;

    var cur_day = aaaa + "-" + mm + "-" + gg;

    var hours = date.getHours()
    var minutes = date.getMinutes()
    var seconds = date.getSeconds();

    if (hours < 10)
        hours = "0" + hours;

    if (minutes < 10)
        minutes = "0" + minutes;

    if (seconds < 10)
        seconds = "0" + seconds;

    return cur_day + " " + hours + ":" + minutes + ":" + seconds;

}
    var $status = false;
    var $minhastarefas = false;
 
function mostraInputComentario(inIdimmsg, descr, id){
	descr = descr.replace(/<br[^>]*>/g, "\n")
	$('#input'+inIdimmsg).html('<textarea class="textareacomentario" id="textarea_'+inIdimmsg+'" onblur="javascript:alteraComentarioTarefa('+inIdimmsg+')">'+descr+'</textarea>');
	$("#textarea_"+inIdimmsg).focus();
	$('#text'+id).unbind('click');
}	


function alteraComentarioTarefa(inIdimmsg){
	$msg=$("#textarea_"+inIdimmsg);
	var $texto = $msg.val().replace(/\n/g, '<br>');
	$msg.parent().addClass("alterado");
	//alert($texto);
	alteraTarefa(
		{
			"_x_u_immsg_idimmsg":inIdimmsg
			,"_x_u_immsg_descr":$texto 
			,"_x_u_immsg_alteradoem":NOW() //Sempre que houver alguma coluna html os objetos cd cb.post devem ser informados dentro de um closure {}
		}
		,function(){
			$msg.parent().removeClass("alterado");	
			$(CB.oModuloHeader, CB.oModuloHeaderBg).addClass('hidden');CB.loadUrl({urldestino: 'form/_dashboard.php'});
		}
	);
}

function alteraMsgTarefa(inIdimmsgbody){
	$msg=$("#msg_"+inIdimmsgbody);
	$msg.parent().addClass("alterado");
	
	alteraTarefa(
		{
			"_x_u_immsgbody_idimmsgbody":inIdimmsgbody
			,"_x_u_immsgbody_msg":$msg.html() //Sempre que houver alguma coluna html os objetos cd cb.post devem ser informados dentro de um closure {}
		}
		,function(){
			$msg.parent().removeClass("alterado");	
		}
	);
}

function alteraDataTarefa(inIdimmsg){
	$msg=$("#data_"+inIdimmsg);
	
	alteraTarefa(
		{
			"_x_u_immsg_idimmsg":inIdimmsg
			,"_x_u_immsg_datatarefa":$msg.text().trim()
		}
	);
}

function alteraStatusTarefa(inIdimmsg){
	$msg=$("#statustarefa_"+inIdimmsg);
	$msg.parent().addClass("alterado");

	$novoStatus=$msg.attr("statustarefa")==="A"?"F":"A";

	alteraTarefa(
		{
			"_x_u_immsg_idimmsg":inIdimmsg
			,"_x_u_immsg_statustarefa":$novoStatus
		}
		,function(){
			$msg.parent().removeClass("alterado");	
			if($novoStatus==="A"){
				$msg
					.removeClass("verde fade fa-check-circle-o")
					.addClass("vermelho fa-check-circle")
					.attr("title","Concluir tarefa")
					.attr("statustarefa",$novoStatus);
			}else{
				$msg.removeClass("vermelho fa-check-circle")
					.addClass("verde fa-check-circle-o fade")
					.attr("title","Reabrir tarefa")
					.attr("statustarefa",$novoStatus);
				delete self.oBadgeTarefa.data("mensagens").naolidas[$msg.attr("idimmsg")];
				var novaQtd=self.oIBadgeTarefa.attr("ibadge")=="1"?"":parseInt(self.oIBadgeTarefa.attr("ibadge"))-1;
				self.oIBadgeTarefa.attr("ibadge",novaQtd);
				self.oIBadgeTarefa.html(novaQtd);
			}
			//Refresh
			CB.loadUrl({urldestino: 'form/_dashboard.php'});
		}
	);
}

function alteraTarefa(inObjetos,incallBack){
	callback=incallBack;
	CB.post({	
		objetos: inObjetos
		,parcial: true
		,refresh: false
		,beforeSend: function(jqXHR, settings){
			//Forçar permissàµes do mà³dulo _droplet
			settings.url=alteraParametroGet("_modulo","_droplet",settings.url);
			settings.url=settings.url.replace(CB.urlDestino,"form/_droplet.php");
		}
		,posPost: function(){
			if(typeof callback=='function'){
				callback()
			}
		}
	});
}

function novaTarefa(){
	//Cria o corpo da mensagem
	CB.post({
		objetos: {
			"_x_i_immsgbody_idpessoa":gIdpessoa
			,"_x_i_immsgbody_msg":"(Nova tarefa)"
		}
		,parcial: true
		,refresh: false
		,msgSalvo: false
		,beforeSend: function(jqXHR, settings){
			//Forçar permissàµes do mà³dulo _droplet
			settings.url=alteraParametroGet("_modulo","_droplet",settings.url);
			settings.url=settings.url.replace(CB.urlDestino,"form/_droplet.php");
		}
		,posPost: function(data, textStatus, jqXHR){
			vIdimmsgbody=jqXHR.getResponseHeader("x-cb-pkid");
			if(vIdimmsgbody){
				//Cria a mensagem/tarefa para o usuario
				CB.post({
					objetos: {
						"_x_i_immsg_idimmsgbody":vIdimmsgbody
						,"_x_i_immsg_idpessoa":gIdpessoa
						,"_x_i_immsg_tipo":"T"
						,"_x_i_immsg_statustarefa":"A"
					}
					,msgSalvo: "Tarefa criada!"
					,parcial: true
					,refresh: false
					,beforeSend: function(jqXHR, settings){
						//Forçar permissàµes do mà³dulo _droplet
						settings.url=alteraParametroGet("_modulo","_droplet",settings.url);
						settings.url=settings.url.replace(CB.urlDestino,"form/_droplet.php");
					}
					,posPost: function(data, textStatus, jqXHR){
						CB.loadUrl({urldestino: 'form/_dashboard.php'});
					}
				});
			}
		}
	});
}

function popupCompartilharTarefa(inIdImmsgbody){

	$oContatosChat = cloneContatosChat();
	$oNovaMsg = cloneNovaMensagemChat();
	
	sMsg=$("#msg_"+inIdImmsgbody).html();

	$tbNovaMsg = $(`

	<table width="100%" id="chatTbNovaMensagemPopup" idimmsgbody="${inIdImmsgbody}">
	<tr>
		<td><label>Assunto:</label></td>
		<td></td>
		<td></td>
	</tr>
	<tr>
		<td colspan="3">
			<div class="form-control" rows="1" id="chatNovaMensagemPopup" style="height: initial"></div>
		</td>
		<td width="20px">
			<i class="fa fa-send fa-3x cinza fade pointer" aria-hidden="true" title="Compartilhar tarefa" onclick="compartilharTarefa();"></i>
		</td>
	</tr>
	</table>`);
	
	$tbNovaMsg.find("#colmsg").append($oNovaMsg);
	
	//Altera o cabeçalho do modal
	strCabecalho="<label class='fa fa-share-alt'></label>&nbsp;&nbsp;Compartilhar tarefa:";
	$("#cbModalTitulo").html(strCabecalho);

	//Mostra o popup
	$('#cbModal #cbModalCorpo')
			.find("*").remove();
	
	$('#cbModal #cbModalCorpo')
			.append($tbNovaMsg)
			.append("<hr>")
			.append($oContatosChat);
	
	//Editor rte
	tinymce.init({
		selector: "#chatNovaMensagemPopup"
		,language: 'pt_BR'
		,inline: true /* não usar iframe */
		,menubar: false
		//,paste_as_text: true
		,paste_data_images: true
		,plugins: 'paste image imagetools lists'
		//,toolbar: 'removeformat bold bullist numlist'
		,toolbar: false
		,fontsize_formats: "8pt 10pt 12pt 14pt 18pt 24pt 36pt"
		,removeformat: [
			{selector: 'b,strong,em,i,font,u,strike', remove : 'all', split : true, expand : false, block_expand: true, deep : true},
			{selector: 'span', attributes : ['style', 'class'], remove : 'empty', split : true, expand : false, deep : true},
			{selector: '*', attributes : ['style', 'class'], split : false, expand : false, deep : true}
		]
		,setup: function (editor) {
			editor.on('KeyDown', function(event) {
				if (event.keyCode == 13 && !event.shiftKey){
					event.preventDefault();
					event.stopPropagation();
				
					//compartilha a mensagem
					compartilharTarefa();

					return false;
				}
			}).on("init",function(){
				tinymce.get("chatNovaMensagemPopup").setContent(sMsg);
			});
		}

	});
	CB.estilizarCalendarios();
	
	$('#cbModal').addClass('quarenta').modal();
	
	return false;

}


function toggleMostrarTarefasFinalizadas(inMostrar){
	
	if(inMostrar===true){
		//TODAS
		if ($minhastarefas == true){
		    //TODAS MINHAS TAREFAS
			$("tr[sender]").hide(200).filter("[sender=eu]").show(200);
		}else{
		    // TODAS AS TAREFAS
			$("tr[sender]").show(200);
		}
		$status = true;
	}else{
		//PENDENTES
		if ($minhastarefas == true){
			//TODAS MINHAS TAREFAS PENDENTES
			$("tr[sender]").hide(200).filter("[sender=eu]").hide(200).filter(".statustarefaA").show(200);
		}else{
			//TODAS AS TAREFAS PENDENTES
			$("tr[sender]").hide(200).filter(".statustarefaA").show(200);
		}
		
		
		$status = false;
	}
}

function toggleFiltrarMinhasTarefas(inMinhasTarefas){
	//MINHAS TAREFAS
	
	if(inMinhasTarefas){
			if ($status){
				//TODAS MINHAS TAREFAS
				$("tr[sender]").hide(200).filter("[sender=eu]").show(200);
				
			}else{
				//TODAS MINHAS TAREFAS PENDENTES
				$("tr[sender]").hide(200).filter("[sender=eu]").hide(200).filter(".statustarefaA").show(200);
			}
		
		$minhastarefas = true;
	}else{
	
			if ($status){
				 // TODAS AS TAREFAS
				$("tr[sender]").show(200);
			}else{
				//TODAS AS TAREFAS PENDENTES
				$("tr[sender]").hide(200).filter(".statustarefaA").show(200);
			}
		
		$minhastarefas = false;
	}
	
}


//$("td[infotarefa]").webuiPopover({content:'',trigger:'hover'});

$(function() {
	if(typeof daterangepicker == "undefined") return true;
	
	daterangepicker.prototype.updateElement=function(){
		svalue="";
		if(!this.singleDatePicker && this.autoUpdateInput){
			svalue=this.startDate.format(this.locale.format) + this.locale.separator + this.endDate.format(this.locale.format);
		}else if(this.autoUpdateInput){
			svalue=this.startDate.format(this.locale.format);
		}
		
		if (this.element.is('input')){
			this.element.val(svalue);
			this.element.trigger('change');
		}else if(this.element.is("td") || this.element.is("div") || this.element.is("span") || this.element.is("label")){
			console.log("Calendário em modo estendido");
			svalue=this.startDate.format(this.locale.format);
			this.element.html(svalue);
			this.element.trigger('change');
		}
	}
});

//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape


</script>
