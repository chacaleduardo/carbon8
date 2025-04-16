<?
require_once("../inc/php/validaacesso.php");
require_once("./controllers/documento_controller.php");
require_once("./controllers/unidade_controller.php");
if($_POST){
	include_once("../inc/php/cbpost9.php");
}



//Parâmetros mandatários para o carbon
$pagvaltabela = "sgdoc";
$pagvalmodulo=$_GET['_modulo'];
$pagvalcampos = array(
	"idsgdoc" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from sgdoc where idsgdoc = '#pkid'";

/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
require_once("../inc/php/controlevariaveisgetpost.php");
//copia o documento do ctrol + D
if($_acao=='i' and !empty($_GET['idsgdoccp'])){	
	$row = DocumentoController::buscarPorChavePrimaria( $_GET['idsgdoccp'] );
    $_1_u_sgdoc_idsgdoc=$row['idsgdoc'];  
    $_1_u_sgdoc_idsgdoctipo=$row['idsgdoctipo'];  
    $_1_u_sgdoc_idsgdoctipodocumento=$row['idsgdoctipodocumento'];  
    $_1_u_sgdoc_conteudo=$row['conteudo'];  
    $_1_u_sgdoc_titulo=$row['titulo'];  
    $_1_u_sgdoc_scrolleditor=$row['scrolleditor']; 
}
if(!empty($_1_u_sgdoc_idsgdoctipodocumento)){
    $arrtipodoc=getObjeto("sgdoctipodocumento", $_1_u_sgdoc_idsgdoctipodocumento, "idsgdoctipodocumento");
}

function getArrUnidades(){
	global $_1_u_sgdoc_idsgdoc;
	$respag = DocumentoController::buscarArrayDeUnidadesDisponiveisParaOModulo( $_1_u_sgdoc_idsgdoc, 'sgdoc' );

	$arrRet=array();
	$i=0;
	foreach ($respag as $k => $r) {
		$i++;
		$arrRet[$i]["idunidade"]=$r["idunidade"];
		$arrRet[$i]["unidade"]=$r["unidade"];
		$arrRet[$i]["idunidadeobjeto"]=$r["idunidadeobjeto"];
		$arrRet[$i]["criadopor"]=$r["criadopor"];
		$arrRet[$i]["criadoem"]=$r["criadoem"];
	}
	return $arrRet;
	
}

function listaUnidades(){
	$arrUn=getArrUnidades();
	echo "<table><tbody>";
	
	foreach($arrUn as $idunidade=>$r){
		if(empty($r["idunidadeobjeto"]))continue;
		$title="Vinculado por: ".$r["criadopor"]." - ".dmahms($r["criadoem"],true);
        echo "<tr><td>".$r["unidade"]."</td><td><i class='fa fa-trash vermelho fade hoververmelho' title='Desvincular' idunidadeobjeto='".$r["$idunidade"]."' onclick='desvincularUn(".$r["idunidadeobjeto"].")'></i></td></tr>";
    }
	echo "<tr>
			<td class='dropdown'>
				<i class='fa fa-plus verde fade hoververde pointer dropdown-toggle' id='dropun' data-toggle='dropdown'></i>
				<ul class='dropdown-menu' aria-labelledby='dropun'>";

	foreach($arrUn as $idunidade=>$r){
		if(!empty($r["idunidadeobjeto"]))continue;
        echo "<li><a idunidadeobjeto='".$r["idunidadeobjeto"]."' href='javascript:relacionaUn(".$r["idunidade"].")'>".$r["unidade"]."</a></li>";
    }
	echo	"	</ul>
			</td>
		</tr>";
	echo "</tbody></table>";	
	
	
}

function listaRnc(){
	global $_1_u_sgdoc_idrnc,$_1_u_sgdoc_idsgdoc;
	echo "<table class='collapse' id='rnc'><tbody>";
	
	if($_1_u_sgdoc_idrnc){
		$sql = "select idregistro from sgdoc where idsgdoc = ".$_1_u_sgdoc_idrnc." ";
		$res = d::b()->query($sql) or die($sql);
		while($r = mysql_fetch_assoc($res)){
			echo "<tr><td><a title='Editar RNC' target='_blank' href='?_modulo=documento&_acao=u&idsgdoc=".$_1_u_sgdoc_idrnc."'>".$r["idregistro"]."</a></td>";
		}
    }else{
	echo "<tr>
			<td><i id='novoteste' class='fa fa-plus-circle verde btn-lg pointer' onclick='fnovornc(".$_1_u_sgdoc_idsgdoc.")' title='Criar novo RNC'></i>
			Novo RNC
			</td>
		</tr>";
    }
	
	echo "</tbody></table>";
	
}

function getJTipodoc(){
	global $_1_u_sgdoc_idsgdoctipo;
	$rts = DocumentoController::buscarJsonTipoDocumento( $_1_u_sgdoc_idsgdoctipo, $_SESSION['SESSAO']['IDPESSOA'] );
	return $rts;
}

function getJDocvinc(){
	global $_1_u_sgdoc_idsgdoc;
	$rts = DocumentoController::buscarDocsQuePodemSerVinculados( $_1_u_sgdoc_idsgdoc, getidempresa('d.idempresa','documento') );
	return $rts;
}

function listaDocVinculados(){
	global $_1_u_sgdoc_idsgdoc,$readonlyp;

	$rts = DocumentoController::buscarDocsVinculadosSemAvaliacaoTreinamento( $_1_u_sgdoc_idsgdoc, cb::idempresa() );

	echo "<table>";
	foreach ($rts as $k => $r) {
		if (empty($readonlyp)) {
			$onclick = "onclick='desvincularDoc(this)";
		}
		$title="Vinculado por: ".$r["criadopor"]." - ".dmahms($r["criadoem"],true);
        echo "<tr>
				<td>
					<a title='".$title."' target='_blank' href='?_modulo=documento&_acao=u&idsgdoc=".$r["idsgdoc"]."'>".$r["idregistro"]."-".$r["titulo"]."</a>
				</td>
				<td>
					<i class='fa fa-trash vermelho fade hoververmelho' title='Desvincular' idsgdocvinc='".$r["idsgdocvinc"]."' ".$onclick."'></i>
				</td>
			</tr>";
    }
	echo "</table>";
}

function listaDocfuncassinatura(){
	global $JSON,$_1_u_sgdoc_idsgdoc;
	$rescar = [];
	$rts = DocumentoController::buscarDocsVinculados( $_1_u_sgdoc_idsgdoc );
	if (count($rts) > 0) {
		foreach($rts as $k => $r){
			$rescar = DocumentoController::buscarParticipantesDeDocsVinculados( $r['idsgdoc'] );
	}

	
	}
	return ($rescar);
}

function getJImgrupovinc(){
	global $_1_u_sgdoc_idsgdoc;
	$rts = DocumentoController::buscarParticipantesParaVincularAoDoc( $_1_u_sgdoc_idsgdoc );

	return $rts;    
}
function desenharadio($tmpsql_arr,$name,$tmpintselected = '') 
{	


	$booencontrou=false;

	if(is_array($tmpsql_arr)){

		foreach ($tmpsql_arr as $key => $vlr){
			if(empty($tmpintselected)){
				//echo '<option value='" . $key . "'>" . $vlr . "</option>\n';
				echo '<div>
                        <input type="radio" id="'.$key.'" name="'.$name.'" value="'.$vlr.'">
                        <label style="font-weight:initial !important" for="'.$key.'">'.$key.'</label><br>
                      </div>';
			}else{
				if($key==$tmpintselected){
					$booencontrou=true;
					echo '<div>
                                <input type="radio" id="'.$key.'" name="'.$name.'" value="'.$vlr.'" checked>
                                <label style="font-weight:initial !important" for="'.$key.'">'.$key.'</label>
                            </div>';
				}else{
					echo '<div>
                        <input type="radio" id="'.$key.'" name="'.$name.'" value="'.$vlr.'">
                        <label style="font-weight:initial !important" for="'.$key.'">'.$key.'</label>
                      </div>';
				}
			}
		}
	}else{

		//echo($tmpsql_arr);
		$result = d::b()->query($tmpsql_arr);
		if (!$result){
		 echo("ERRO Desenha Radio \n<!-- ".  mysqli_error(d::b()) . " -->\n");
		 return;
		 }
	
		while ($row = mysqli_fetch_array($result,MYSQLI_NUM)){
			if(empty($tmpintselected)){
				//echo "<option value='" . $row[0] . "'>" . $row[1] . "</option>\n";
                echo '
                        <input type="radio" id="'.$row[1].'" name="'.$name.'" value="'.$row[0].'">
                        <label style="font-weight:initial !important" for="'.$row[1].'">'.$row[1].'</label><br>
                      ';
			}else{
				if($row[0]==$tmpintselected){
					$booencontrou=true;
                    echo '
                                <input type="radio" id="'.$row[1].'" name="'.$name.'" value="'.$row[0].'" checked>
                                <label style="font-weight:initial !important" for="'.$row[1].'">'.$row[1].'</label><br>
                            ';
				}else{
					echo '
                        <input type="radio" id="'.$row[1].'" name="'.$name.'" value="'.$row[0].'">
                        <label style="font-weight:initial !important" for="'.$row[1].'">'.$row[1].'</label><br>
                      ';
				}
			}
		}
	}

	//maf150513: Caso o valor do DB nao seja encontrado, colocar aviso para o usuario no final
	if(!empty($tmpintselected) and $booencontrou==false){
		echo "<div value='" . $tmpintselected . "'>* ERRO: VALOR [" . $tmpintselected . "] NÃO EXISTENTE! *</div>\n";
	}

}

//Para mostrar na tela a versao atual ao inves da proxima
$flversao = traduzid('sgdoctipo', 'idsgdoctipo', 'flversao', $_1_u_sgdoc_idsgdoctipo);
$_versaoanterior = $_1_u_sgdoc_versao;
if(empty($_1_u_sgdoc_versao)){
	$_1_u_sgdoc_versao = 0;	
}
if(empty($_1_u_sgdoc_revisao)){
	$_1_u_sgdoc_revisao=0;
	$strrevi= 0;
}
else{
	$strrevi= $_1_u_sgdoc_revisao;
}

if($_1_u_sgdoc_idsgdoctipo){
$jTipoSubtDoc = getJTipodoc();
// $jTipoSubtDoc=$JSON->encode($arrTipoSubtDoc);
}else{
  $jTipoSubtDoc='null';  
}


if($_1_u_sgdoc_status == 'APROVADO' OR $_1_u_sgdoc_status == 'REPROVADO' OR $_1_u_sgdoc_status == 'OBSOLETO'){
			$disabled = "disabled='disabled' ";	
			$readonly = "readonly='readonly' ";
			$visual = 'SIM';	
			if($_1_u_sgdoc_status == 'APROVADO'){
			 $aprovado="Aprovado por";	
			}
}

function getClientesDoc($idtipopessoarel = 5){
	$res = DocumentoController::buscarPessoasParaVincularAoDoc($idtipopessoarel);
    $arrret=array();
    foreach($res as $k => $r){
        //monta 2 estruturas json para finalidades (loops) diferentes
        $arrret[$r["idpessoa"]]["nome"]=$r["nome"];
        $arrret[$r["idpessoa"]]["tipo"]=$r["tipo"];
    }
	return $arrret;
}
//Recupera os produtos a serem selecionados para uma nova Formalização
$arrCli=getClientesDoc($arrtipodoc['idtipopessoarel']);
//print_r($arrCli); die;
$jCli=$JSON->encode($arrCli);


function listaPessoaEvento()
{
	global $_1_u_sgdoc_idsgdoc,$_1_u_sgdoc_versao,$_1_u_sgdoc_idsgdoctipo,$disabledp;
	$rts = DocumentoController::buscarParticipantesVinculadosComSetor( $_1_u_sgdoc_idsgdoc );

	echo "<div id='listapessoaevento'>";
	//echo "<!--$s-->";
	$local = "";

	foreach ($rts as $k => $r) 
	{
		$cassinar='Y';
			//Retorna a Versão do Documento
			
		$versao=$_1_u_sgdoc_versao;
			
		//Retorna a Assinatura
		$rowx = DocumentoController::buscarUltimaAssinatura( $r['idpessoa'], $_1_u_sgdoc_idsgdoc );

		//var_dump($rowx);
		if($rowx['status']=='PENDENTE'){
			$clbt="primary";
			$cassinar='N';
			$title =  "title='Assinatura Pendente na versão ".$rowx['versao']."'";
		}elseif($rowx['status']=='ASSINADO' AND $rowx['versao'] == $versao){
			$clbt="success";

		}elseif($rowx['status']=='ASSINADO' AND $rowx['versao'] < $versao){
			$clbt="warning";
			$title =  "title='Solicitar Assinatura em nova versão do Documento'";
		}else {
			$clbt='warning';
			$title =  "title='Solicitar Assinatura'";
		}
		
		if ($r['editar'] == 'Y') {
			$cheked = 'btn btn-xs btn-success';
		}else {
			$cheked = 'btn btn-xs btn-default';
		}
	
		
		if ($rowx['versao'] && cb::idempresa() != 1) {
			$versaoass="<div class='col-md-1 hideprint' style='line-height: 16px; padding: 8px; font-size: 10px;'> Versão:<b>".$rowx['versao']."</b></div>
				<div class='col-md-1 hideprint' style='line-height: 16px; padding: 8px; font-size: 10px;'>".dma($r['alteradoem'])."</div>";
		}elseif($rowx['versao'] && cb::idempresa() == 1){
			$versaoass="<div class='col-md-1 hideprint' style='line-height: 16px; padding: 8px; font-size: 10px;'> Versão:<b>".$rowx['versao']."</b></div>
			<div class='col-md-1 hideprint' style='line-height: 16px; padding: 8px; font-size: 10px;'></div>";
		}else {
			$versaoass="<div class='col-md-2 hideprint'></div>";
		}
		if($r['idcarrimbo']){
			$idcarrimbo = $r['idcarrimbo'];
		} else {
			$idcarrimbo = 'null';
		}		

		if ($clbt=='success' AND $rowx['versao'] == $versao or !empty($disabledp)) {
			$disableass = 'disabled';
		}else {
			$disableass = '';
		}
		$inbtstatus="<button ".$disableass." onclick=\"criaassinatura(".$r['idpessoa'].",'documento',".$_1_u_sgdoc_idsgdoc.",".$versao.",'".$cassinar."',".$r['idfluxostatuspessoa'].",".$idcarrimbo.")\" type='button' class=' btn btn-xs hideprint btn-".$clbt." hovercinza pointer floatright ' ".$title." style='margin-right: 8px; border-radius: 4px;font-size:9px'><i class='fa fa-check'></i>&nbsp;Assinatura</button>";
	
		$pad = '';
		
		if ( $r['oculto'] == '1'){
			$op = 'opacity:0.5;';
		}else{
			$op ='';
		}
	
		if ($local != $r["local"]){
			if ($local != ''){
				echo "</fieldset>";
				echo '</div>';
			}
			$local = $r["local"];
			$bars = "<a align='RIGHT' class='fa fa-bars pointer hoverazul' onclick=\"janelamodal('?_modulo=".$r['tipolocal']."&_acao=u&id".$r['tipolocal']."=".$r["idobjeto"]."')\"></a>";
			$excluir="<i class=\"fa fa-trash fa-1x cinzaclaro hovercinza btn-lg pointer ui-droppable\" onClick=\"retiragrupo(".$r['idobjeto'].",".$_1_u_sgdoc_idsgdoc.",".$r['idfluxostatuspessoagrupo'].",'".$r['tipolocal']."')\" title='Excluir!'></i>";
			if (empty($local)) {
				$excluir = '';
				$bars = '';
			}
			if ($local != ''){
				echo "<div class='filtrarAssinaturaPorNome ' style='padding:2px 10px; margin-top: 30px; margin: 8px; background: #eee;'><legend class='scheduler-border hideprint' style='font-size:11px' ><b style='text-transform:uppercase;'>".($local)."</b> $excluir $bars</legend>";
				echo "<fieldset style='margin-top: -20px;' class='scheduler-border'>";
			}
		}	

		if (!empty($r["local"])){
			$pad = '';
		}

		if($r['idtipopessoa']==1){
			$mod='funcionario';
		}else{
			$mod='pessoa';
		}
		unset($botao);
		if(empty($r['idobjeto']) ){
			if (!empty($disabledp) or $rowx['versao']) {
				$botao = "<i class='fa fa-ban fa-1x hideprint cinzaclaro ui-droppable' status='".$r["status"]."' idfluxostatuspessoa='".$r["idfluxostatuspessoa"]."' ></i>";
			}else {
				$botao="<i class='fa fa-trash fa-1x cinzaclaro hoververmelho  pointer ui-droppable' status='".$r["status"]."' idfluxostatuspessoa='".$r["idfluxostatuspessoa"]."' onclick='retirapessoa(".$r["idfluxostatuspessoa"].")'></i>";
			}
		}else{
			$botao = "<i class='fa fa-ban fa-1x hideprint hideprint cinzaclaro ui-droppable' status='".$r["status"]."' idfluxostatuspessoa='".$r["idfluxostatuspessoa"]."' ></i>";
		}
		$title="Vinculado por: ".$rowx["criadopor"]." - ".dmahms($rowx["criadoem"],true);
		if ($r["setor"]){
			$cl = "&nbsp<span class='hideprint' style='display: inline; background: rgb(102, 102, 102);font-size: 10px;color: #fff;padding: 0px 6px;border-radius: 3px;'>".$r["setor"]."</span>";
		}else{
			$cl = '';
		}

		if ($r['editar'] == 'Y' and $r['idpessoa'] == $_SESSION['SESSAO']['IDPESSOA']) {
			$inputpermissao = '<input type="hidden" id="_permissaoeditardoctemp_" value="Y">';
		}

		if((empty($rowx['versao']) or $rowx['versao'] < $versao) and ($r['localord']=="4") ){
			continue;
		} else {
			if($r['localord']!="4"){
				echo "<div id='".$r["idfluxostatuspessoa"]."' class='col-md-12 filtrarAssinaturaPorNome' style='".$pad."".$op."''>
						<div class='col-md-6' style='line-height: 16px; padding: 8px; font-size: 10px;'>
							".$r["nomecurto"].$cl."
						</div>
						".$versaoass."
						<div align='right' class='col-md-2'>
							<button ".$disabledp." style='margin-right: 8px; border-radius: 4px;font-size:9px' class='".$cheked." hideprint' value='".$r['editar']."' idfluxo=".$r['idfluxostatuspessoa']." type='button' class='compacto' title='Editar' onclick='editardoc(this)'>
								<i class='fa fa-pencil'></i>&nbsp;Editar
							</button>
						</div>
						<div class='col-md-1'>".$inbtstatus."</div>
						<div class='col-md-1'>".$botao."</div> 
					</div>
					<div id='collapse-".$r['idpessoa']."'>".$inputpermissao."</div>";
			}
		}
	}
	if ($local != ''){
		echo "</fieldset>";
		echo '</div>';
	}

	echo "</div>"; // Fecha div id=listaPessoaEvento
}
function hideshowcols($temp = null){
	if ($temp == "N") {
		return 'hidden';
	}
}


function listaPessoaEventoInseridosManualmente()
{
	global $_1_u_sgdoc_idsgdoc,$_1_u_sgdoc_versao,$_1_u_sgdoc_idsgdoctipo,$disabledp;
	$rts = DocumentoController::buscarParticipantesVinculadosComSetor( $_1_u_sgdoc_idsgdoc );
	

	$rts = DocumentoController::buscarParticipantesVinculadosComSetor( $_1_u_sgdoc_idsgdoc );
	echo "<div id='listapessoaeventoinseridosmanualmente'>";
	echo "<div class='filtrarAssinaturaPorNome' style='padding:2px 10px; margin-top: 30px; margin: 8px; background: #eee;'>";

	echo "<legend class='scheduler-border hideprint' style='font-size:11px;padding: 8px 10px 8px 0px;' ><b style='text-transform:uppercase;'>Inseridos Manualmente</b></legend>";
	echo "<fieldset style='margin-top: -20px;' class='scheduler-border'>";
	//echo "<!--$s-->";
	$local = "";

	foreach ($rts as $k => $r) 
	{
		$cassinar='Y';
			//Retorna a Versão do Documento
			
		$versao=$_1_u_sgdoc_versao;
			
		//Retorna a Assinatura
		$rowx = DocumentoController::buscarUltimaAssinatura( $r['idpessoa'], $_1_u_sgdoc_idsgdoc );
		//var_dump($rowx);
		if($rowx['status']=='PENDENTE'){
			$clbt="primary";
			$cassinar='N';
			$title =  "title='Assinatura Pendente na versão ".$rowx['versao']."'";
		}elseif($rowx['status']=='ASSINADO' AND $rowx['versao'] == $versao){
			$clbt="success";

		}elseif($rowx['status']=='ASSINADO' AND $rowx['versao'] < $versao){
			$clbt="warning";
			$title =  "title='Solicitar Assinatura em nova versão do Documento'";
		}else {
			$clbt='warning';
			$title =  "title='Solicitar Assinatura'";
		}

		if ($r['editar'] == 'Y') {
			$cheked = 'btn btn-xs btn-success';
		}else {
			$cheked = 'btn btn-xs btn-default';
		}
	
		
		if ($rowx['versao'] && cb::idempresa() != 1) {
			$versaoass="<div class='col-md-1 hideprint' style='line-height: 16px; padding: 8px; font-size: 10px;'> Versão:<b>".$rowx['versao']."</b></div>
				<div class='col-md-1 hideprint' style='line-height: 16px; padding: 8px; font-size: 10px;'>".dma($r['alteradoem'])."</div>";
		}elseif($rowx['versao'] && cb::idempresa() == 1){
			$versaoass="<div class='col-md-1 hideprint' style='line-height: 16px; padding: 8px; font-size: 10px;'> Versão:<b>".$rowx['versao']."</b></div>
			<div class='col-md-1 hideprint' style='line-height: 16px; padding: 8px; font-size: 10px;'></div>";
		}else {
			$versaoass="<div class='col-md-2 hideprint'></div>";
		}
		if($r['idcarrimbo']){
			$idcarrimbo = $r['idcarrimbo'];
		} else {
			$idcarrimbo = 'null';
		}		

		if ($clbt=='success' AND $rowx['versao'] == $versao or !empty($disabledp)) {
			$disableass = 'disabled';
		}else {
			$disableass = '';
		}
		$inbtstatus="<button ".$disableass." onclick=\"criaassinatura(".$r['idpessoa'].",'documento',".$_1_u_sgdoc_idsgdoc.",".$versao.",'".$cassinar."',".$r['idfluxostatuspessoa'].",".$idcarrimbo.")\" type='button' class=' btn btn-xs hideprint btn-".$clbt." hovercinza pointer floatright ' ".$title." style='margin-right: 8px; border-radius: 4px;font-size:9px'><i class='fa fa-check'></i>&nbsp;Assinatura</button>";
	
		$pad = '';
		
		if ( $r['oculto'] == '1'){
			$op = 'opacity:0.5;';
		}else{
			$op ='';
		}
	
	
		if (!empty($r["local"])){
			$pad = '';
		}

		if($r['idtipopessoa']==1){
			$mod='funcionario';
		}else{
			$mod='pessoa';
		}
		unset($botao);
		if(empty($r['idobjeto']) ){
			if (!empty($disabledp) or $rowx['versao']) {
				$botao = "<i class='fa fa-ban fa-1x hideprint cinzaclaro ui-droppable' status='".$r["status"]."' idfluxostatuspessoa='".$r["idfluxostatuspessoa"]."' ></i>";
			}else {
				$botao="<i class='fa fa-trash fa-1x cinzaclaro hoververmelho  pointer ui-droppable' status='".$r["status"]."' idfluxostatuspessoa='".$r["idfluxostatuspessoa"]."' onclick='retirapessoa(".$r["idfluxostatuspessoa"].")'></i>";
			}
		}else{
			$botao = "<i class='fa fa-ban fa-1x hideprint cinzaclaro ui-droppable' status='".$r["status"]."' idfluxostatuspessoa='".$r["idfluxostatuspessoa"]."' ></i>";
		}
		$title="Vinculado por: ".$rowx["criadopor"]." - ".dmahms($rowx["criadoem"],true);
		if ($r["setor"]){
			$cl = "&nbsp<span class='hideprint' style='display: inline; background: rgb(102, 102, 102);font-size: 10px;color: #fff;padding: 0px 6px;border-radius: 3px;'>".$r["setor"]."</span>";
		}else{
			$cl = '';
		}

		if ($r['editar'] == 'Y' and $r['idpessoa'] == $_SESSION['SESSAO']['IDPESSOA']) {
			$inputpermissao = '<input type="hidden" id="_permissaoeditardoctemp_" value="Y">';
		}

		if(($r['localord']=="4") and ($rowx['versao'] == $versao) or ($r['localord']=="4" and empty($rowx['versao']))){

			echo "
				<div class='col-md-12'>
					<div class='col-md-6' style='line-height: 16px; padding: 8px; font-size: 10px;'>
						".$r["nomecurto"].$cl."
					</div>
					".$versaoass."
					<div  align='right' class='col-md-2'>
						<button ".$disabledp." style='margin-right: 8px; border-radius: 4px;font-size:9px' class='".$cheked." hideprint' value='".$r['editar']."' idfluxo=".$r['idfluxostatuspessoa']." type='button' class='compacto' title='Editar' onclick='editardoc(this)'>
							<i class='fa fa-pencil'></i>&nbsp;Editar
						</button>
					</div>
					<div class='col-md-1'>".$inbtstatus."</div>
					<div class='col-md-1'>".$botao."</div> 
				</div>
				<div id='collapse-".$r['idpessoa']."'>
					".$inputpermissao."
				</div>";	
		}
		
	}

	echo "</fieldset>";
	echo "</div>"; // Fecha div class="filtrarAssinaturaPorNome"
	echo "</div>"; // Fecha div id=listaPessoaEventoInseridosManualmente
}

function listaPessoaEventoSemSetorESemAssinatura()
{
	global $_1_u_sgdoc_idsgdoc,$_1_u_sgdoc_versao,$_1_u_sgdoc_idsgdoctipo,$disabledp;
	$rts = DocumentoController::buscarParticipantesVinculadosSemSetor( $_1_u_sgdoc_idsgdoc );
	//echo "<!--$s-->";
	$local = "";
	$controleCabecalho = true;
	foreach ($rts as $k => $r) 
	{
		$cassinar='Y';
			//Retorna a Versão do Documento
			
		$versao=$_1_u_sgdoc_versao;
			
		//Retorna a Assinatura
		$rowx = DocumentoController::buscarUltimaAssinatura( $r['idpessoa'], $_1_u_sgdoc_idsgdoc );
		//var_dump($rowx);
		if($rowx['status']=='ASSINADO' AND $rowx['versao'] < $versao){
			$clbt="light";
			$title =  "title='Solicitar Assinatura em nova versão do Documento'";
		}else {
			$clbt='light';
			$title =  "title='Solicitar Assinatura'";
		}
		
		if ($r['editar'] == 'Y') {
			$cheked = 'btn btn-xs btn-success';
		}else {
			$cheked = 'btn btn-xs btn-default';
		}
	
		
		if ($rowx['versao'] && cb::idempresa() != 1) {
			$versaoass="<div class='col-md-1 hideprint' style='line-height: 16px; padding: 8px; font-size: 10px;'> Versão:<b>".$rowx['versao']."</b></div>
				<div class='col-md-1 hideprint' style='line-height: 16px; padding: 8px; font-size: 10px;'>".dma($r['alteradoem'])."</div>";
		}elseif($rowx['versao'] && cb::idempresa() == 1){
			$versaoass="<div class='col-md-1 hideprint' style='line-height: 16px; padding: 8px; font-size: 10px;'> Versão:<b>".$rowx['versao']."</b></div>
			<div class='col-md-1 hideprint' style='line-height: 16px; padding: 8px; font-size: 10px;'></div>";
		}else {
			$versaoass="<div class='col-md-2 hideprint'></div>";
		}
		if($r['idcarrimbo']){
			$idcarrimbo = $r['idcarrimbo'];
		} else {
			$idcarrimbo = 'null';
		}		

		if ($clbt=='success' AND $rowx['versao'] == $versao or !empty($disabledp)) {
			$disableass = 'disabled';
		}else {
			$disableass = '';
		}
		$inbtstatus="<button ".$disableass." onclick=\"criaassinatura(".$r['idpessoa'].",'documento',".$_1_u_sgdoc_idsgdoc.",".$versao.",'".$cassinar."',".$r['idfluxostatuspessoa'].",".$idcarrimbo.")\" type='button' class=' btn btn-xs hideprint hovercinza pointer floatright ' ".$title." style='margin-right: 8px;background: silver; border-radius: 4px;font-size:9px'><i class='fa fa-check'></i>&nbsp;Assinatura</button>";
	
		$pad = '';
		
		if ( $r['oculto'] == '1'){
			$op = 'opacity:0.5;';
		}else{
			$op ='';
		}
	


		if (!empty($r["local"])){
			$pad = '';
		}

		if($r['idtipopessoa']==1){
			$mod='funcionario';
		}else{
			$mod='pessoa';
		}
		unset($botao);
		if(empty($r['idobjeto']) ){
			if (!empty($disabledp) or $rowx['versao']) {
				$botao = "<i class='fa fa-ban fa-1x hideprint cinzaclaro ui-droppable' status='".$r["status"]."' idfluxostatuspessoa='".$r["idfluxostatuspessoa"]."' ></i>";
			}else {
				$botao="<i class='fa fa-trash fa-1x cinzaclaro hoververmelho  pointer ui-droppable' status='".$r["status"]."' idfluxostatuspessoa='".$r["idfluxostatuspessoa"]."' onclick='retirapessoa(".$r["idfluxostatuspessoa"].")'></i>";
			}
		}else{
			$botao = "<i class='fa fa-ban fa-1x hideprint cinzaclaro ui-droppable' status='".$r["status"]."' idfluxostatuspessoa='".$r["idfluxostatuspessoa"]."' ></i>";
		}
		$title="Vinculado por: ".$rowx["criadopor"]." - ".dmahms($rowx["criadoem"],true);
		if ($r["setor"]){
			$cl = "&nbsp<span class='hideprint' style='display: inline; background: rgb(102, 102, 102);font-size: 10px;color: #fff;padding: 0px 6px;border-radius: 3px;'>".$r["setor"]."</span>";
		}else{
			$cl = '';
		}

		if ($r['editar'] == 'Y' and $r['idpessoa'] == $_SESSION['SESSAO']['IDPESSOA']) {
			$inputpermissao = '<input type="hidden" id="_permissaoeditardoctemp_" value="Y">';
		}

		if(((!empty($rowx['versao'])) && ($rowx['versao'] < $versao) && $r['localord']=="4")){

			if($controleCabecalho){
				$controleCabecalho = false;
				echo "<div id='listapessoaeventosemsetoresemassinatura'>";
				echo "<div class='filtrarAssinaturaPorNome' style='padding:2px 10px; margin-top: 30px; margin: 8px; background: #eee;'>";

				echo "<legend class='scheduler-border hideprint' style='font-size:11px;padding: 8px 10px 8px 0px;' ><b style='text-transform:uppercase;'>
						Inseridos Manualmente - Obsoletos</b>
						<i class='fa fa-arrows-v fa-2x cinzaclaro pointer' title='Detalhar' data-toggle='collapse' href='#semsetoresemassinatura' aria-expanded='true' style='float: right;'></i>
					</legend>";

				echo "<fieldset style='margin-top: -20px;' class='scheduler-border'>";
					
				echo '<div class="" id="semsetoresemassinatura">';
			}

			echo "<div id='".$r["idfluxostatuspessoa"]."' class='col-md-12 filtrarAssinaturaPorNome' style='".$pad."".$op."'>
					<div class='col-md-6' style='line-height: 16px; padding: 8px; font-size: 10px;'>
						".$r["nomecurto"].$cl."
					</div>
					".$versaoass."
					<div  align='right' class='col-md-2'>
						<button ".$disabledp." style='margin-right: 8px; border-radius: 4px;font-size:9px' class='".$cheked." hideprint' value='".$r['editar']."' idfluxo=".$r['idfluxostatuspessoa']." type='button' class='compacto' title='Editar' onclick='editardoc(this)'>
							<i class='fa fa-pencil'></i>&nbsp;Editar
						</button>
					</div>
					<div class='col-md-1'>".$inbtstatus."</div>
					<div class='col-md-1'>".$botao."</div> 
				</div>
				<div id='collapse-".$r['idpessoa']."'>".$inputpermissao."</div>";

		} 
	}

	if(!$controleCabecalho){
		echo "</div>"; // Fecha div id=semsetoresemassinatura
		echo "</div>"; // Fecha div class="collapse"
		echo "</fieldset>";
		echo "</div>"; // Fecha div class=filtrarAssinaturaPorNome
		echo "</div>"; // Fecha div id=listapessoaeventosemsetoresemassinatura
	}
}
function listaSetoresDepsAreasSemvinculo()
{
	global $_1_u_sgdoc_idsgdoc,$_1_u_sgdoc_versao,$_1_u_sgdoc_idsgdoctipo,$disabledp;
	$rts = DocumentoController::buscarSetorDepsAreasVaziosNoDoc( $_1_u_sgdoc_idsgdoc );
	//echo "<!--$s-->";
	$local = "";
	$controleCabecalho = true;
	if(count($rts) > 0){
		echo "<div class='filtrarAssinaturaPorNome' style='padding:2px 10px; margin-top: 30px; margin: 8px; background: #eee;'>";

		echo "<legend class='scheduler-border hideprint' style='font-size:11px;padding: 8px 10px 8px 0px;' ><b style='text-transform:uppercase;'>
				Setores/Departamentos Vazios</b>
			</legend>";

		echo "<fieldset style='margin-top: -20px;' class='scheduler-border'>";
	}
	foreach ($rts as $k => $r){

	echo "<div class='filtrarAssinaturaPorNome ' style='margin-top: 30px; margin: 8px; background: #eee;'>
			<legend class='scheduler-border hideprint' style='font-size:11px' >
				<b style='text-transform:uppercase;'>".$r['local']."</b>
				<i class=\"fa fa-trash fa-1x cinzaclaro hovercinza btn-lg pointer ui-droppable\" onClick=\"retiragrupo(".$r['idlocal'].",".$_1_u_sgdoc_idsgdoc.",".$r['idfluxostatuspessoa'].",'".$r['tipoobjeto']."')\" title='Excluir!'></i>
			</legend>";
	echo "</div>";
	}

	if(count($rts) > 0){
		echo "</div>"; // Fecha div class="collapse"
		echo "</fieldset>";
	}
}



		$mostrarOutros=true;
	

?>

<style>
@media print {
		.no-print {display: none !important;}

		.print {
			max-width: 675px;
		}
		#cbSteps{
			display: none;
		}

		.hideprint{
			display: none !important;
		}

		.pagebreakprint{
			page-break-after: always;
		}
		#cbContainer{padding: 0 !important;}

	}

body.somenteleitura #editor1 {
    display: none;
}
body.somenteleitura  #cabHistdoc {
    text-align:center;
    color: black;
    border: 1px solid silver;
    position:fixed;
    /**adjust location**/
    right: 0px;
    bottom: 0px;
    padding: 0 10px 0 10px;
    width: 100%;
    /* Netscape 4, IE 4.x-5.0/Win and other lesser browsers will use this */
    _position: absolute;
}

/* GVT - 04/02/2020 - removido essa propriedade, pois fazia com que o texto quebrasse a div no modo somente leitura */

	#editor1Container{
		min-height: 90vh;
	}
	#editor1{
		height: 90vh;
		width: 100%;
		overflow-y: scroll;
		background-color: white;
	}
	
	.transparente{
		opacity: 0;
		transition: opacity .25s ease-in-out;
		-moz-transition: opacity .25s ease-in-out;
		-webkit-transition: opacity .25s ease-in-out;
	}
	.opaco{
		opacity: 1;
		transition: opacity .25s ease-in-out;
		-moz-transition: opacity .25s ease-in-out;
		-webkit-transition: opacity .25s ease-in-out;
	}
.copiancontrolada{
	border: none;
	position:fixed;
	top: 50%;
	left:80px;
	z-index:-100;
}

div.dz-filename > span{
	word-break: break-word;
}

div.dz-preview > i.fa{
	margin: 10px;
}

.dropdown-menu.open
{
	max-height: 400px !important;
}
</style>
<?
if (!empty($_1_u_sgdoc_idsgdoc)) {
	if(array_key_exists("doc", getModsUsr("MODULOS")) != 1){
		//não tem LP
		$sempermissaoed = false;
		$sempermissaov = false;
		$rps = DocumentoController::verificarPermissaoEdicao( $_SESSION['SESSAO']['IDPESSOA'], $_1_u_sgdoc_idsgdoc );
		if (count($rps) > 0) {
			$permissao = $rps[0];
			if ($permissao['editar'] == 'Y') {
				$sempermissaoed = false;
			}
			if($permissao['editar'] == 'N'){
				$sempermissaoed = true;
			}
			if (empty($permissao['editar'])) {
				$sempermissaoed = true;
			}
		$sempermissaov = false;
		}else{
		$sempermissaov = true;
		$sempermissaoed = true;
		}
	}else {
		$sempermissaov = false;
		$sempermissaoed = false;
	}
	if ($sempermissaoed == true) {
		$readonlyp = 'readonly';
		$disabledp = 'disabled';
	}else {
		$readonlyp = '';
		$disabledp = '';
	}
}else {
	$readonlyp = '';
	$disabledp = '';
	$sempermissaov = false;
	$sempermissaoed = false;
}

?>
<div class="row screen">
	<div class="col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<table id="amostra" style="width: 100%;">
					<tr>
						<td>
							<input name="_1_<?=$_acao?>_sgdoc_idsgdoc" type="hidden" value="<?=$_1_u_sgdoc_idsgdoc?>">
							<input name="_1_<?=$_acao?>_sgdoc_criadopor" type="hidden" value="<?=$_1_u_sgdoc_criadopor?>">
							<input name="_1_<?=$_acao?>_sgdoc_idsgdoccopia" type="hidden" value="<?=$_1_u_sgdoc_idsgdoccopia?>">
							<input name="_1_<?=$_acao?>_sgdoc_versao" type="hidden" value="<?=$_1_u_sgdoc_versao?>">
							<input name="_1_<?=$_acao?>_sgdoc_revisao" type="hidden" value="<?=$_1_u_sgdoc_revisao?>">
						</td>
						<td id="cabRegistro">					
							ID: <?echo "<!--".$disabletiny."-->";?>
							<label class="alert-warning"><?=$_1_u_sgdoc_idregistro?></label>
						</td>
						<?
						if ($flversao == 'Y') {?>
							<td id="cabVersao">
								<?if(!empty($_1_u_sgdoc_idsgdoc)){ ?>
									Versão:
									<?=$_versaoanterior?>.<?=$strrevi?>
								<?}?>
							</td>
						<?}else {?>
							<td></td>
						<?}?>
						<td>
							Título:
						</td>
						<td>
							<textarea name="_1_<?=$_acao?>_sgdoc_titulo" type="text" rows="1" cols="60" vnulo><?=$_1_u_sgdoc_titulo?></textarea>
						</td>
						<td style="text-align:right">Tipo:</td>
						<td>
							<?if(empty($_1_u_sgdoc_idsgdoctipo)){?>
							<select name="_1_<?=$_acao?>_sgdoc_idsgdoctipo" vnulo style="width: 100%;">
								<option></option>
								<?$fsql = DocumentoController::buscarTiposDeDocQueOUsuarioPodeCriar( $_SESSION['SESSAO']['IDPESSOA'] );?>
								<?fillselect($fsql,$_1_u_sgdoc_idsgdoctipo);?>		
							</select>
							<?}else{?>
								<input name="_1_<?=$_acao?>_sgdoc_idsgdoctipo" type="hidden" value="<?=$_1_u_sgdoc_idsgdoctipo?>">
								<label class="alert-warning" style="text-transform:uppercase;"><?=$_1_u_sgdoc_idsgdoctipo?></label>
								<?
								$permissao = DocumentoController::verificarPermissao( $_SESSION["SESSAO"]["IDPESSOA"], $_1_u_sgdoc_idsgdoc );
								?>
								<?
								if (!empty($_1_u_sgdoc_idsgdoc)) {
									if($_1_u_sgdoc_restrito=="Y"){?>
										<?if ($permissao) {
											if (empty($readonlyp)) {
												$altrestrito = 'onclick="altrestrito(\'N\','.$_1_u_sgdoc_idsgdoc.');"';
											}
											?>
											<i <?=$altrestrito?> class="fa fa-star fa-1x laranja btn-lg pointer"  title="Alterar Restrito para Não"></i>
										<?}?>
									<?}else{?>
										<?if ($permissao) {
											if (empty($readonlyp)) {
												$altrestrito = 'onclick="altrestrito(\'Y\','.$_1_u_sgdoc_idsgdoc.');"';
											}
											?>
											<i <?=$altrestrito?> class="fa fa-star fa-1x cinzaclaro btn-lg pointer" onclick="altrestrito('Y',<?=$_1_u_sgdoc_idsgdoc?>);" title="Alterar Restrito para Sim"></i>
										<?}
									}
								}
						}?>
						</td>
						<td  align='right'>Status:</td>
						<td class="nowrap">   
							<input name="statusant" type="hidden" value="<?=$_1_u_sgdoc_status?>">

							<?if($_acao == 'u'){ $_1_u_sgdoc_status = $_1_u_sgdoc_status; } else { $_1_u_sgdoc_status = 'AGUARDANDO';}?>
							<input name="_1_<?=$_acao?>_sgdoc_status" type="hidden" id='status' value="<?=$_1_u_sgdoc_status?>">
							<? $rotulo = getStatusFluxo($pagvaltabela, 'idsgdoc', $_1_u_sgdoc_idsgdoc)?>                                              
							<label class="alert-warning" title="<?=$_1_u_sgdoc_status?>" id="statusButton"><?=mb_strtoupper($rotulo['rotulo'],'UTF-8')?> </label>
						</td>
							<td>
								<i title="Ver Versão" class="fa fa-file pull-right  cinza hoverazul" onclick="janelamodal('?_modulo=documentoimp&_acao=u&idsgdoc=<?=$_1_u_sgdoc_idsgdoc?>')"></i>
							</td>
						<td>
							<i class="fa fa-print fa-2x  fade pointer hoverazul  btn-lg pointer" onclick="window.print();" title="Imprimir"></i>
							<i class="fa fa-print fa-2x  fade pointer hoverazul  btn-lg pointer hidden" onclick="janelamodal('report/sgdoc_imp.php?idsgdoc=<?=$_1_u_sgdoc_idsgdoc?>')" title="Imprimir"></i>
						</td>
						
						

					</tr>
					</table>

					<?if(!empty($_1_u_sgdoc_idsgdoc) OR !empty($_GET["idsgdoccp"])){?>
						<table>                        
						<tr>
							<td>Classificação:</td>
							<td class='nowrap' >
							<?if (empty($_1_u_sgdoc_idsgdoctipodocumento)) {?>
								<input type="text" class="compacto size20" name="_1_<?=$_acao?>_sgdoc_idsgdoctipodocumento" cbvalue="<?=$_1_u_sgdoc_idsgdoctipodocumento?>" value="<?=$arrTipoSubtDoc[$_1_u_sgdoc_idsgdoctipodocumento]["tipodocumento"]?>" style="width:95%;" vnulo>
							<?}else{?>
								<input type="hidden"  name="_1_<?=$_acao?>_sgdoc_idsgdoctipodocumento"  value="<?=$_1_u_sgdoc_idsgdoctipodocumento?>">
								<label class="alert-warning" style="text-transform:uppercase;"><?=(traduzid('sgdoctipodocumento', 'idsgdoctipodocumento', 'tipodocumento', $_1_u_sgdoc_idsgdoctipodocumento))?></label>
								<a href="?_modulo=tipodocumento&_acao=u&idsgdoctipodocumento=<?=$_1_u_sgdoc_idsgdoctipodocumento?>" target="_blank"><i class="fa fa-bars fa-1x cinzaclaro hoverazul btn-sm pointer"></i></a>
								<?
								if ($_1_u_sgdoc_status == 'APROVADO' or $_1_u_sgdoc_status == 'OBSOLETO') {?>
								<?}else{?>
								<?if (empty($readonlyp)) {$apagaclass = 'onclick="apagaclass('.$_1_u_sgdoc_idsgdoc.')"';}?>
								<i <?=$apagaclass?> class="fa fa-trash vermelho fade hoververmelho" title="Trocar classificação"></i>
								<?}?>
							<?}?>							
							
							</td>
							<?if($arrtipodoc["flnota"]=='Y'){?>
								<td  align='right'>Nota:</td>
								<td>
									<input type="text" class="size7"  name="_1_<?=$_acao?>_sgdoc_nota"  value="<?=$_1_u_sgdoc_nota?>" >
								</td>
							<?}

							if($arrtipodoc["flresultado"]=='Y'){?>
								<td  align='right'>Resultado:</td>
								<td>
									<select name="_1_<?=$_acao?>_sgdoc_resultado" >
										<option value=""></option>
										<?fillselect("SELECT 'APROVADO','Aprovado' UNION SELECT 'PARCIALMENTE APROVADO','Parcialmente Aprovado' UNION SELECT 'REPROVADO','Reprovado' ",$_1_u_sgdoc_resultado);?>	
									</select>
								</td>
							<?}?>
							
							<?
							$res = DocumentoController::pegarDataVencimento($_1_u_sgdoc_idsgdoc);
							$datadevencimento = $res['vencimento'];
							?>
								<td  style="text-align: right; width:100%">Vencimento:</td>
								<td><label style="width: 100%;" class="alert-warning"><?=dma($datadevencimento)?></label></td>
						</tr>
						</table>
					<?}?>
				
			</div>

			<!-- Faz Busca para trazer resultados do select do modal para voltar a versão					 -->
			<?
			if(!empty($_1_u_sgdoc_idsgdoc)){
			$resalt = DocumentoController::buscarHistoricoDeVersoes($_1_u_sgdoc_idsgdoc);?>
			<div id="mdvoltarversao" style="display: none;">
			<table style="width: 100%;">
				<tr>
					<th>Selecione a versão que deseja Restaurar</th>
					<th></th>
					<th>Descreva o Motivo da Restauração</th>
				</tr>
				<tr>
					<td style="width: 45%;">
						<select style="width: 100%;" name="" id="voltaversao">
							<option value=""></option>
							<?	foreach($resalt as $k => $rowalt){
									if($rowalt["versao"] == $_versaoanterior){
										continue;
									}
								?>										
								<option value="<?=$rowalt["versao"]?>">Versão: <?=$rowalt["versao"]?>.<?=$rowalt["revisao"]?> -	Data:<?=$rowalt["dmadata"]?></option>
							<?}?>
						</select>
					</td>
					<td style="width: 10%;"></td>
					<td style="width: 45%;">
						<input style="width: 100%;" id="descvoltaversao" type="textbox">
					</td>
				</tr>
			</table>
			<div class="pull-right" style="margin-top:15px; margin-bottom: 15px;">
				<button id="cbSalvar" type="button" class="btn btn-success btn-xs" onclick="voltaversaosave(<?= $_GET['idsgdoc'] ?>, <?= $_versaoanterior ?>, '<?= $_1_u_sgdoc_status ?>',<?= $_1_u_sgdoc_idfluxostatus ?> )" title="Salvar">
					<i class="fa fa-circle"></i>Salvar
				</button>
			</div>
			
		</div>
			<?}?>



			<?if (!empty($_1_u_sgdoc_idsgdoc)) {
				$permissao = DocumentoController::verificarPermissao( $_SESSION["SESSAO"]["IDPESSOA"], $_1_u_sgdoc_idsgdoc );
				if (($_1_u_sgdoc_restrito == 'Y' and !$permissao) or $sempermissaov == true) {
					if ($_1_u_sgdoc_restrito == 'Y') {
						echo"<div align='Center' class='panel-body'>Documento restrito, somente participantes possuem acesso.</div>";
					}else {
						echo"<div align='Center' class='panel-body'>Você não possui acesso a este documento.</div>";
					}
				}else {?>
		
					<?if((!empty($_1_u_sgdoc_idsgdoc)and !empty($_1_u_sgdoc_idsgdoctipodocumento)) OR !empty($_GET["idsgdoccp"])){?>

						<div class="panel-body">
							<div class="col-md-3 screen">
								<?if(!empty($_1_u_sgdoc_idsgdoc)){?>
									<table style="width: 100%">
										<tr>
											<td>Unidade:</td>
											<td>
												<select name="_1_<?=$_acao?>_sgdoc_idunidade" vnulo>
													<option value=""></option>
													<?fillselect(UnidadeController::buscarUnidadesAtivasPorIdEmpresa(cb::idempresa(), true),$_1_u_sgdoc_idunidade);?>		
												</select>
											</td>
										</tr>
										<?if($_1_u_sgdoc_idsgdoctipo=='rnc'){?>
											<tr>
												<td>Grau:</td>
												<td style="width: 100%">
													<select name="_1_<?=$_acao?>_sgdoc_grau" style="font-size: 10px;" >
														<option value=""></option>
														<?fillselect("select 'PEQUENO','Pequeno' union select 'MODERADO','Moderado' union select 'GRAVE','Grave'",$_1_u_sgdoc_grau);?>	
													</select>
												</td>				
											</tr>
											<tr>
												<td>Impacto:</td>
												<td style="width: 100%">
													<select name="_1_<?=$_acao?>_sgdoc_impacto" style="font-size: 10px;" >
														<option value=""></option>
														<?fillselect("select 'BAIXO','Baixo' union select 'MEDIO','Medio' union select 'ALTO','Alto'",$_1_u_sgdoc_impacto);?>	
													</select>
												</td>				
											</tr>	
										<?}?>
										<tr>
											<td class="nowrap">Copia Cont.:</td>
											<td>
												<?if($_1_u_sgdoc_cpctr=="Y"){$cpctr="checked"; $sgdoc_cpctr="N";}else{$cpctr="";$sgdoc_cpctr="Y";}?>
												<input <?=$cpctr?>  type="checkbox" sgdoc_cpctr="<?=$sgdoc_cpctr?>" class="compacto" name="sgdoc_cpctr"  value="<?=$_1_u_sgdoc_cpctr?>" onclick="setcopiactr(this)">
											</td>
										</tr>
										<?if(!empty($_1_u_sgdoc_idsgdoctipodocumento)){
															
											if($arrtipodoc['responsavel']=='Y'){
												if($arrtipodoc['idsgdoctipo']=='auditoria'){
													$strexe="Auditor Lider";
												}else{
													$strexe="Executor";
												}?>

												<tr>
													<td class="nowrap"><?=$strexe?>:</td>
													<td style="width: 100%">							
														<input type="text"  name="_1_<?=$_acao?>_sgdoc_responsavel"  value="<?=$_1_u_sgdoc_responsavel?>" >
													</td>
												</tr>
												<?if($arrtipodoc['idsgdoctipo']=='auditoria' and $arrtipodoc['responsavel']=='Y'){?>
													<tr>
														<td class="nowrap">Auditor Participante:</td>
														<td style="width: 100%">							
															<input type="text"  name="_1_<?=$_acao?>_sgdoc_responsavelsec"  value="<?=$_1_u_sgdoc_responsavelsec?>" >
														</td>
													</tr>
												<?}//if($arrtipodoc['idsgdoctipo']=='auditoria')
																	
											}//if($arrtipodoc['responsavel']=='Y')

											if($arrtipodoc['periodo']=='Y'){?>
												<tr>
													<td>Início / Fim:</td>
													<td style="width: 100%" class="nowrap">							
														<input type="text"  name="_1_<?=$_acao?>_sgdoc_inicio" class="calendario size7"  value="<?=$_1_u_sgdoc_inicio?>" >
														&nbsp;&nbsp;/&nbsp;&nbsp;							
														<input type="text"  name="_1_<?=$_acao?>_sgdoc_fim" class="calendario size7"  value="<?=$_1_u_sgdoc_fim?>" >
													</td>
												</tr>                                        
											<?}
											
											if($arrtipodoc['fldatavencimento']=='Y'){?>
												<tr>
													<td>Vencimento:</td>
													<td style="width: 100%" class="nowrap">													
														<input type="text"  name="_1_<?=$_acao?>_sgdoc_datavencimento" class="calendario"  value="<?=$_1_u_sgdoc_datavencimento?>" >
													</td>
												</tr>                                        
											<?}
											
										}
										// if(($_1_u_sgdoc_idsgdoctipo=='questionario' or $_1_u_sgdoc_idsgdoctipo=='avaliacao') /* and !empty($_1_u_sgdoc_idsgdoccopia) */ )
										if($arrtipodoc["pessoarel"]=='Y' and !empty($arrtipodoc["idtipopessoarel"])){?>
											<tr>
												<td class="nowrap">Avaliado:</td>
												<td>
													<table style="width:100%;">
														<tr>
															<td>
																<?if(empty($_1_u_sgdoc_idpessoa)){?>
																	<input  type="text" class="compacto" name="_1_<?=$_acao?>_sgdoc_idpessoa" cbvalue="<?=$_1_u_sgdoc_idpessoa?>" value="<?=$arrCli[$_1_u_sgdoc_idpessoa]["nome"]?>" style="width: 15em;">
																<?}else{
																	echo(traduzid('pessoa', 'idpessoa', 'nome', $_1_u_sgdoc_idpessoa));?>
																<?}?>
															</td>
															<?if(!empty($_1_u_sgdoc_idpessoa)){?>
																<td>
																	<a href="?_modulo=pessoa&_acao=u&idpessoa=<?=$_1_u_sgdoc_idpessoa?>" target="_blank" class="fa fa-bars hoverazul btn-lg pointer" title="Mostrar avaliado"></a>
																</td>
															<?}?>
														</tr>
													</table>
												</td>
											</tr>
										<?}?>
															
									</table>
									<hr>
									<div class="panel panel-default hideprint">
										<!-- Alteração para não mostrar documentos vinculados na impressão 16/03/2023 @571877 - DOCUMENTO TIPO PROCEDIMENTO COM ERRO AO PUXAR OS DOCS. VINCULADOS -->
										<div class="panel-heading" data-toggle="collapse" href="#docCollapse"><i class="fa fa-file-word-o cinzaclaro"></i> Docum. Vinculados:</div>
										<div class="panel-body">
											<table class='table-hovercollapse.show' id='docCollapse'><tbody>
											<tr>
												<td><input <?=$disabledp?> id="docvinc" class="compacto" type="text" cbvalue placeholder="Selecione"></td>
											</tr>
											<tr>
												<td>
													<?=listaDocVinculados()?>	
												</td>
											</tr>	
							
											</tbody></table>
										</div>
									</div>
									<input type="hidden" name="_readonly_" value="<?=$readonlyp?>">
									<?if($_1_u_sgdoc_status == 'APROVADO' OR $_1_u_sgdoc_status == 'OBSOLETO'){
										$res = DocumentoController::buscarAnexosPorTipoObjetoIdObjeto( 'sgdoc', $_1_u_sgdoc_idsgdoc );
										$numarq= count($res);
										if($numarq>0 ){?>
											<div class="panel panel-default" >
												<div class="panel-heading" data-toggle="collapse" href="#docAnexoCollapse">Arquivos Anexos (<?=$numarq?>)</div>
												<div class="panel-body">
													<table class='table-hovercollapse.show' id='docAnexoCollapse'>
														<tbody>
															<tr>
																<td>
																	<ul class="listaitens">
																		<? foreach ($res as $k =>$row){?>
																			<li>
																				<a title="Abrir arquivo" target="_blank"  href="../upload/<?=$row["nome"]?>" style="word-break: break-all;"><?=$row["nome"]?></a>
																			</li>
																		<?}//while ($row = mysqli_fetch_array($res))?>
																	</ul>
																</td>
															</tr>
														</tbody>
													</table>
												</div>
											</div>
										<?}//if($numarq>0  )
									}else{?>
										<div class="panel panel-default" >
											<div class="panel-heading" data-toggle="collapse" href="#docAnexoCollapse">Docum. Anexos:</div>
											<div class="panel-body">
												<table class='table-hovercollapse.show' id='docAnexoCollapse'>
													<tbody>
														<tr>
															<td>                                
																<div class="cbupload" id="upload" title="Clique ou arraste arquivos para cá" style="width:100%;height:100%;">
																	<i class="fa fa-cloud-upload fonte18"></i>
																</div>                                
															</td>
														</tr>
													</tbody>
												</table>
											</div>
										</div>
									<?}?>
									<?if($_1_u_sgdoc_idsgdoctipodocumento==65){?>
										<div class="panel panel-default">
											<div class="panel-heading" data-toggle="collapse" href="#doCertCollapse">Certificado</div>
											<div class="panel-body" id="doCertCollapse">
												<div class="cbupload" id="uploadcertificado" title="Clique ou arraste arquivos para cá" style="width:100%;height:100%;">
													<i class="fa fa-cloud-upload fonte18"></i>
												</div>
											</div>
										</div>
									<?}?>
								<?}//if($_1_u_sgdoc_idsgdoc)?>
							</div>
									
							<?if($_acao=="i" and !empty($_GET["idsgdoccp"]) and $arrtipodoc["fleditor"]=="Y"){?> 
								<div class="col-md-9 ">
									<table style="background-color: white;">
										<tr>
											<td width="900px">	
												<?=$_1_u_sgdoc_conteudo?>
												<textarea  <?=$disabled?>  name="_1_<?=$_acao?>_sgdoc_conteudo" class="hidden"><?=$_1_u_sgdoc_conteudo?></textarea>
											</td>		
										</tr>
									</table>
								</div>
							<?}else{

								if( $arrtipodoc["flquestionario"]=="Y"){?>

									<div class="col-md-9">
										<div class="panel panel-default" >
											<div class="panel-body"> 
												<table class="table table-striped planilha">
													<tr><!-- QST. DESCRICAO  CLASSIFICACAO  OBSERVACAO  CONCLUSAO-->
														<th>Qst.</th>
														<?
														$resp = DocumentoController::buscarCamposVisiveisPorIdsgdoctipodocumento($_1_u_sgdoc_idsgdoctipodocumento);
														$qtd=count($resp);
														$col = array();
														$rotcurto = array();
														$code = array();
														$datatype = array();
														$editavel = array();
														$prompt = array();
														$idsgdoctipodocumentocampos = array();
														$texto = array();
														if($arrtipodoc["tipotemplate"] == 'vertical'){
															foreach ($resp as $k => $rowp){
																array_push($col, $rowp["col"]);
																array_push($rotcurto, $rowp["rotcurto"]);
																array_push($code, $rowp["code"]);
																array_push($editavel, $rowp["editavel"]);
																array_push($datatype, $rowp["datatype"]);
																array_push($prompt, $rowp["prompt"]);
																array_push($idsgdoctipodocumentocampos, $rowp["idsgdoctipodocumentocampos"]);
																array_push($texto, $rowp["texto"]);
																?>
																<!-- <th><?=$rowp["rotcurto"]?></th> -->
															<?}?>
															<th colspan="3">Formulário</th>
														</tr>
														<?
														$rest2   = DocumentoController::buscarPaginas( $_1_u_sgdoc_idsgdoc );
														$qtdpag2 = count($rest2);
														$vqtdpag2=$qtdpag2+1;
														$li=99;
														if($qtdpag2 > 0)
														{
															foreach($rest2 as $k => $rowp2){
																if (traduzid('sgdoctipodocumento','idsgdoctipodocumento','fltemplate',$_1_u_sgdoc_idsgdoctipodocumento) == "Y") {
																	$rowtemp=DocumentoController::buscarTemplate( $_1_u_sgdoc_idsgdoctipodocumento, $rowp2['pagina']);
																}
																$li++;
																$i = 0;
																?>
																<tr>
																	<td>
																		<input type="hidden"  name="_<?=$li?>_<?=$_acao?>_sgdocpag_idsgdocpag"  value="<?=$rowp2["idsgdocpag"]?>" >
																		<input type="text" class="size3"  name="_<?=$li?>_<?=$_acao?>_sgdocpag_pagina" readonly value="<?=$rowp2["pagina"]?>" >
																	</td>
																	<td colspan="3">
																		<div class="col-md-12">
																			<? 
																			$pergunta = '';
																			foreach($rowtemp as $k => $v){
																				if($v == "P"){
																					$pergunta = $k;
																				}
																			}
																			if(!$pergunta){?>
																				Campo Pergunta não configurado!!
																			<?}else{
																				$needleP = array_search($pergunta,$col)
																				?>
																				<div class="papel transparente tiny mceNonEditable" style="width: 100%; min-height: 150px;" id="_<?=$li?>_<?=$_acao?>_sgdocpag_<?=$pergunta?>"></div>
																				<textarea class="hidden flquestionario" tinydisabled cols="40" rows="6" name="_<?=$li?>_<?=$_acao?>_sgdocpag_<?=$pergunta?>">
																				<?if(empty($rowp2[$pergunta])){
																					$desc = $texto[$needleP];
																				} else {
																					$desc = $rowp2[$pergunta];
																				}
																				
																				echo $desc;
																				?>
																				</textarea>
																			<?}?>
																			
																		</div>
																		<div class="col-md-12">
																			<?
																			$resposta = '';
																			foreach($rowtemp as $k => $v){
																				if($v == "R"){
																					$resposta = $k;
																				}
																			}
																			if(!$resposta){?>
																				Campo de resposta não configurado!
																			<?}else{
																				$needleR = array_search($resposta,$col);
																				?>
																				<h3><?=$rotcurto[$needleR]?></h3>
																				<?
																				if(empty($code[$needleR]) and $datatype[$needleR] == "varchar"){?>
																					<input type="text" class="size10" name="_<?=$li?>_<?=$_acao?>_sgdocpag_<?=$resposta?>" value="<?=$rowp2[$resposta]?>">
																					<?}
																				if(!empty($code[$needleR])){?>
																					<?=desenharadio($code[$needleR],"_".$li."_".$_acao."_sgdocpag_".$resposta."",$rowp2[$resposta])?>
																				<?}
																				if($datatype[$needleR] == "longtext" and empty($code[$needleR])){
																					if($prompt[$needleR] == 'select')
																					{
																						$sqlOpcoes = DocumentoController::buscarPorIdsgdoctipodocumentocampos($idsgdoctipodocumentocampos[$needleR]);
																						desenharadio($sqlOpcoes,"_".$li."_".$_acao."_sgdocpag_".$resposta."",$rowp2[$resposta]);
																					} else {
																						?>
																						<div class="papel transparente tiny mceNonEditable" style="width: 100%; min-height: 150px;" id="_<?=$li?>_<?=$_acao?>_sgdocpag_<?=$resposta?>" editavel='<?=$editavel[$needleR]?>'></div>
																						<textarea class="hidden flquestionario" tinydisabled cols="40" rows="6" name="_<?=$li?>_<?=$_acao?>_sgdocpag_<?=$resposta?>">
																							<?
																							if(empty($rowp2[$resposta])){
																								$desc = $texto[$needleR];
																							} else {
																								$desc = $rowp2[$resposta];
																							}
																							echo $desc;
																							?>
																						</textarea>
																						<?
																					}
																				}
																				?>
																			<?}?>
																		</div>
																		<div style="height: 15px;" class="col-md-12"></div>
																	</td>
																</tr>
															<?}
														}
													}else{
														foreach ($resp as $k => $rowp){
															array_push($col, $rowp["col"]);
															array_push($rotcurto, $rowp["rotcurto"]);
															array_push($code, $rowp["code"]);
															array_push($editavel, $rowp["editavel"]);
															array_push($datatype, $rowp["datatype"]);
															array_push($prompt, $rowp["prompt"]);
															array_push($idsgdoctipodocumentocampos, $rowp["idsgdoctipodocumentocampos"]);
															array_push($texto, $rowp["texto"]);
															?>
															<th><?=$rowp["rotcurto"]?></th>
														<?}?>
													</tr>
													<?
													$rest = DocumentoController::buscarPaginas( $_1_u_sgdoc_idsgdoc );
													$qtdpag=count($rest);
													$vqtdpag=$qtdpag+1;
													$li=99;
													if($qtdpag > 0)
													{
														foreach ($rest as $k => $rowp)
														{
															if (traduzid('sgdoctipodocumento','idsgdoctipodocumento','fltemplate',$_1_u_sgdoc_idsgdoctipodocumento) == "Y") {
																$rowtemp=DocumentoController::buscarTemplate( $_1_u_sgdoc_idsgdoctipodocumento, $rowp['pagina']);
															}
															$li++;
															$i = 0;
															?>
															<tr>
																<td>
																	<input type="hidden"  name="_<?=$li?>_<?=$_acao?>_sgdocpag_idsgdocpag"  value="<?=$rowp["idsgdocpag"]?>" >
																	<input type="text" class="size3" readonly name="_<?=$li?>_<?=$_acao?>_sgdocpag_pagina"  value="<?=$rowp["pagina"]?>" >
																</td>
																<?
																	while($i < $qtd){?>
																		<td>
																			<?
																			if(empty($code[$i]) and $datatype[$i] == "varchar"){?>
																				<?if($rowtemp[$col[$i]] == "N"){?>
																					<input type="text" class="size10" disabled='disabled' value="N/A">
																				<?}?>
																				<input type="text" class="size10 <?=hideshowcols($rowtemp[$col[$i]])?>" name="_<?=$li?>_<?=$_acao?>_sgdocpag_<?=$col[$i]?>" value="<?=$rowp[$col[$i]]?>">
																			<?}
																			if(!empty($code[$i]) and ($datatype[$i] == "varchar" || $datatype[$i] == "longtext")){?>
																				<?if($rowtemp[$col[$i]] == "N"){?>
																					<input type="text" class="size10" disabled='disabled' value="N/A">
																				<?}?>
																				<select class="size5 <?=hideshowcols($rowtemp[$col[$i]])?>" style="width: 100%;/*max-width: 15vw;*/" name="_<?=$li?>_<?=$_acao?>_sgdocpag_<?=$col[$i]?>">
																					<option></option>
																					<?
																						fillselect($code[$i],$rowp[$col[$i]]);
																					?>
																				</select>
																			<?}else if($datatype[$i] == "longtext"){
																				if($prompt[$i] == 'select')
																				{
																					?>
																					<?if($rowtemp[$col[$i]] == "N"){?>
																						<input type="text" class="size10" disabled='disabled' value="N/A">
																					<?}?>
																					<select style="width: 100%;/*max-width: 15vw;*/" class="<?=hideshowcols($rowtemp[$col[$i]])?>" name="_<?=$li?>_<?=$_acao?>_sgdocpag_<?=$col[$i]?>">
																						<option></option>
																						<?
																							$sqlOpcoes = DocumentoController::buscarPorIdsgdoctipodocumentocampos($idsgdoctipodocumentocampos[$i]);
																							fillselect($sqlOpcoes,$rowp[$col[$i]]);
																						?>
																					</select>
																					<?
																				} else {
																					?>
																					<?if($rowtemp[$col[$i]] == "N"){?>
																						<input type="text" class="size10" disabled='disabled' value="N/A">
																					<?}?>
																					<div class="papel transparente tiny mceNonEditable <?=hideshowcols($rowtemp[$col[$i]])?>" style="width: 100%;/*max-width: 15vw;*/" id="_<?=$li?>_<?=$_acao?>_sgdocpag_<?=$col[$i]?>" editavel='<?=$editavel[$i]?>'></div>
																					<textarea class="hidden flquestionario" tinydisabled cols="40" rows="6" name="_<?=$li?>_<?=$_acao?>_sgdocpag_<?=$col[$i]?>">
																						<?
																						if(empty($rowp[$col[$i]])){
																							$desc = $texto[$i];
																						} else {
																							$desc = $rowp[$col[$i]];
																						}
																						echo $desc;
																						?>
																					</textarea>								
																					<?
																				}
																			}
																			?>
																		</td>
																	<?$i++;
																	}?>
																<?if(!($_1_u_sgdoc_status == 'APROVADO')){?>
																	<td><a class="fa fa-trash vermelho fade hoververmelho" title="Excluir" idunidadeobjeto="" onclick="excluirpagina(<?=$rowp["idsgdocpag"]?>)"></a></td>
																<?}?>
															</tr>
														<?}
													}?>
													<?}?>
													<tr>
														<?if (traduzid('sgdoctipodocumento','idsgdoctipodocumento','fltemplate',$_1_u_sgdoc_idsgdoctipodocumento) == "N") {?>
															<td colspan="4">
																<?if ($readonlyp == '') {?>
																	<a class="fa fa-plus-circle fa-2x cinzaclaro hoververde pointer" onclick="novapagina(<?=$vqtdpag?>)" title="Adicionar Questão"></a>
																<?}?>
															</td>
														<?}?>
													</tr>
												</table>
											</div>
										</div>
									</div>
								
								<?}elseif($arrtipodoc["fleditor"]=="Y"){?>
									<div id="editor1Container" class="col-md-9 carregando">
										<?if($_acao=="u" and empty($_1_u_sgdoc_conteudo)){
											if(!empty($_1_u_sgdoc_idsgdoctipodocumento) and empty($_1_u_sgdoc_conteudo) ){// se o conteudo da primeira pagina for vazio verifica se existe template pre cadastrado no sgtipodocumento
												$rows= DocumentoController::buscarPorChavePrimariaIdsgdoctipodocumento( $_1_u_sgdoc_idsgdoctipodocumento );
												$template = $rows["template"];		
												if(!empty($template)){//atribui o template no conteudo caso exista
													$_1_u_sgdoc_conteudo=$template;
												}
											}
										}?>
											<!-- Armazenar a posição vertical do editor -->
										<input type="hidden" name="_1_<?=$_acao?>_sgdoc_scrolleditor" value="<?=$_1_u_sgdoc_scrolleditor?>">
										<div id="editor1" class="papel transparente"></div>
										<textarea  <?=$disabled?>  name="_1_<?=$_acao?>_sgdoc_conteudo" tinydisabled class="hidden"><?=$_1_u_sgdoc_conteudo?></textarea>
										<div tinydisabled style=" padding: 50px; "></div>
										<div tinyactive class="hidden" style="background-color: white;">
											<?=$_1_u_sgdoc_conteudo?>
										</div>
									</div>
									
								<?}
							}?>
									
											
						</div>
					<?if ($readonlyp == '') {
						$disabledp = '';
					}else {
						$disabledp = 'disabled';
					}
					$nmp = DocumentoController::verificarPermissao($_SESSION["SESSAO"]["IDPESSOA"],$_1_u_sgdoc_idsgdoc);
					?>
					
					<div class="col-md-12 hideprint">
						<?
						$flavaliacao = traduzid('sgdoctipo', 'idsgdoctipo', 'flavaliacao', $_1_u_sgdoc_idsgdoctipo);
						if ($flavaliacao == 'Y') {?>
							<div class="panel panel-default col-md-6">
								<div class="panel-heading" data-toggle="collapse" href="#avaliacao">Avaliação</div>
								<div class="panel-bodycollapse.show" id="avaliacao"> 
									<?
									$resav = DocumentoController::buscarAvaliacoesVinculadas($_1_u_sgdoc_idsgdoc);?>
									
									<table class="table table-striped planilha " style='width: 100%;'>
										<?if (empty($disabledp)) {?>
											<tr>
												<td colspan="2" >
													<b>Layout da avaliação:</b> 
												</td>
											</tr>
											<tr>
												<td colspan="2" nowrap>
													<?if($_1_u_sgdoc_tipoavaliacao != ''){$read = 'disabled';}?>
													
													<div class="input-group input-group-sm col-md-12">
														<select <?=$read?>  onchange="tipoclassificacao('avaliacao')" class="" id="nova_avaliacao">
															<option value="">- Selecione o Layout -</option>
															<?fillselect(DocumentoController::buscarSubTiposDeAvaliacoesParaVincular(),$_1_u_sgdoc_tipoavaliacao);?>
														</select>
														<span class="input-group-addon " onclick="limpaclassificacao('avaliacao')"><i class="fa fa-eraser pointer"></i></span>
														<span class="input-group-addon " onclick="editaclassificacao('avaliacao')"><i class="fa fa-pencil pointer"></i></span>
														<span class="input-group-addon " onclick="criaclassificacao('avaliacao')"><i class="fa fa-plus pointer"></i></span>
													</div>
												</td> 
											</tr>
										<?}?>
										<?if (empty($disabledp)) {?>
											<tr>
												<td colspan="2" >
													<b>Nova avaliação:</b> 
												</td>
											</tr>
											<tr class="header">
												<td nowrap>
												<?$qst = traduzid('sgdoctipodocumento','idsgdoctipodocumento','flquestionario',$_1_u_sgdoc_tipoavaliacao);?>
													<input onchange="vinculadoc('avaliacao',<?=$_1_u_sgdoc_idsgdoc?>,'<?=$qst?>')" id="nova_avaliacaot" placeholder="Titúlo da avaliação">
												</td>
												<td nowrap>
													<div class="input-group input-group-sm col-md-6">
														<?if (count($resav) > 0) {?>
															<input type="hidden" value="<?=$_1_u_sgdoc_tipoavaliacao?>" id="nova_avaliacao" >
														<?}?>
													</div>
												</td>
											</tr>
										<?}?>
										<?foreach($resav as $k => $row){?>
											<tr>
												<td colspan="1" nowrap><a target="_blank" href="?_modulo=documento&_acao=u&idsgdoc=<?=$row['idsgdoc']?>"><?=$row["titulo"]?></a></td>
												<?if ($nmp) {?>
													<td colspan="1" nowrap><i  title='Desvincular' onclick="desvincula(<?=$row['idsgdocvinc']?>)" class="fa fa-trash vermelho fade hoververmelho pointer"></i></td> 
												<?}else {?>
													<td colspan="1" nowrap></td>
												<?}?>
											</tr>
										<?}?>
									</table>
								</div>
							</div>
						<?}
						$fltreinamento = traduzid('sgdoctipo', 'idsgdoctipo', 'fltreinamento', $_1_u_sgdoc_idsgdoctipo);
						if ($fltreinamento == 'Y') {?>
							<div class="panel panel-default col-md-6" >
								<div class="panel-heading" data-toggle="collapse" href="#treinamento">Treinamento</div>
								<div class="panel-bodycollapse.show" id="treinamento">
									<?
									$resav = DocumentoController::buscarTreinamentosVinculados($_1_u_sgdoc_idsgdoc);?>
									
									<table class="table table-striped planilha " style='width: 100%;'>
										<?if (empty($disabledp)) {?>
											<tr>
												<td colspan="2" >
													<b>Layout do treinamento:</b> 
												</td>
											</tr>
											<tr>
												<td colspan="2" nowrap>
													<?if($_1_u_sgdoc_tipotreinamento != ''){$readt = 'disabled';}?>

													<div class="input-group input-group-sm col-md-12">
														<select <?=$readt?>  onchange="tipoclassificacao('treinamento')" class="" id="novo_treinamento">
															<option value="">- Selecione o Layout -</option>
															<?fillselect(DocumentoController::buscarSubTiposDeTreinamentosParaVincular(),$_1_u_sgdoc_tipotreinamento);?>
														</select>
														<span class="input-group-addon " onclick="limpaclassificacao('treinamento')"><i class="fa fa-eraser pointer"></i></span>
														<span class="input-group-addon " onclick="editaclassificacao('treinamento')"><i class="fa fa-pencil pointer"></i></span>
														<span class="input-group-addon " onclick="criaclassificacao('treinamento')"><i class="fa fa-plus pointer"></i></span>
													</div>
												</td> 
											</tr>
										<?}?>
										
										<?if (empty($disabledp)) {?>
											<tr>
												<td colspan="2" >
													<b>Novo treinamento:</b>
												</td>
											</tr>
											<tr class="header">
												<td nowrap>
												<?$qst = traduzid('sgdoctipodocumento','idsgdoctipodocumento','flquestionario',$_1_u_sgdoc_tipotreinamento);?>
													<input onchange="vinculadoc('treinamento',<?=$_1_u_sgdoc_idsgdoc?>,'<?=$qst?>')" id="novo_treinamentot" placeholder="Título do Treinamento">
												</td>
												<td nowrap>
													<div class="input-group input-group-sm col-md-6">
														<?if (count($resav) > 0) {?>
															<input type="hidden" value="<?=$_1_u_sgdoc_tipotreinamento?>" id="novo_treinamento" >
														<?}?>
													</div>
												</td>
											</tr>
										<?}
										foreach($resav as $k => $row){?>
											<tr>
												<td colspan="1" nowrap><a target="_blank" href="?_modulo=documento&_acao=u&idsgdoc=<?=$row['idsgdoc']?>"><?=$row["titulo"]?></a></td>
												<?if ($nmp) {?>
													<td colspan="1" nowrap><i  title='Desvincular' onclick="desvincula(<?=$row['idsgdocvinc']?>)" class="fa fa-trash vermelho fade hoververmelho pointer"></i></td> 
												<?}else {?>
													<td colspan="1" nowrap></td>
												<?}?>
											</tr>
										<?}?>
									</table>
								</div>
							</div>
						<?}?>
					</div>
				<?}?>
				<?}?>
		</div>
	</div>
</div>

<?if($arrtipodoc["flobsquestionario"]=='Y'){?>
	<div class="col-md-12 screen" >
		<div class="panel panel-default" >
			<div class="panel-heading" >Observação</div>
			<div class="panel-body" > 
				<table>
					<tr>
						<td width="900px">                               
							<textarea  <?=$disabled?>   class="size100" style="margin: 0px; height: 200px;" name="_1_<?=$_acao?>_sgdoc_observacao" ><?=$_1_u_sgdoc_observacao?></textarea>
						</td>		
					</tr>
				</table>
			</div>   
		</div>
	</div>
<?}?>
	
	<div class="col-md-12 hideprint">
		<div class="panel panel-default col-md-12" >
			<div class="panel-heading" data-toggle="collapse" href="#participantes" >Participantes</div>
			<div class="panel-bodycollapse.show" id="participantes"> 					
				<table style="width: 100%;">
					<tr>
						<td>
							<strong>Adicionar Participantes: </strong>
						</td>
						<td>
							<input <?=$disabledp?> id="funcvinc" class="compacto hidden" type="text" cbvalue placeholder="Selecione">
							<select id="funcvinc2" class="selectpicker" multiple="multiple" data-actions-box="true" data-live-search="true">
								<?
									foreach ((getJImgrupovinc()) as $key => $value) {
										echo '<option data-tokens="'.retira_acentos($value['objeto']).'" value="'.$value['idobjeto'] ." -- " .$value['tipo']." -- ".$value['objeto'].'" >'.$value['objeto'].'</option>';
									}
								?>
							</select>
							<?
							//if ($_1_u_sgdoc_idsgdoctipo == 'treinamento') {?>
								<!-- <input <?=$disabledp?> id="funcvinctreinamento" class="compacto hidden" type="text" cbvalue placeholder="Selecione">
								<select id="funcvinc2" class="selectpicker" multiple="multiple" data-actions-box="true" data-live-search="true">
									<?
										foreach ((listaDocfuncassinatura()) as $key => $value) {
											echo '<option data-tokens="'.retira_acentos($value['nomecurto']).'" value="'.$value['idpessoa'] ." -- pessoa -- ".$value['nomecurto'].'" >'.$value['nomecurto'].'</option>';
										}
									?>
								</select> -->
							<?//}else{?>
								
							<?//}?>
							<button id="btnsalvar" onclick="addColaboradorPicker()" class="btn btn-primary hidden">Adicionar</button>
						</td>
						<td style="text-align: center;">
							<button <?=$disabledp?> onclick="assinatodos(<?=$_1_u_sgdoc_idsgdoc?>)" class="btn btn-xs btn-primary"><i class="fa fa-pencil"></i>Gerar Assinatura para todos</button>
						</td>
						<td style="text-align: right;">
							<strong> Filtrar Assinatura por Nome: </strong>	
						</td>
						<td>
							<input type="text" id="filter" class="compacto">
						</td>
					</tr>
				</table>

				<div class="col-md-12">
					<div class="panel panel-default" style="background:#fff;height: 100%;overflow: auto; padding-top:-30px;" >
						<?= listaPessoaEvento()?>

						<?= listaPessoaEventoInseridosManualmente() ?>

						<?= listaPessoaEventoSemSetorESemAssinatura()?>

						<?= listaSetoresDepsAreasSemvinculo ()?>
					</div>
				</div>
			</div>
			</div>
		</div>
		<div id="descversoes" style="display: none;">
			<table class="table table-striped">
				<tr>
					<th style="width:12%;">V. Origem</th>
					<th style="width:12%;">V. Destino</th>
					<th  style="width:40%;">Observação</th>
					<th style="width:20%;">Restaurado Por</th>
					<th style="width:20%;">Restaurado Em</th>
				</tr>
				<?
				$resdesc = DocumentoController::buscarRestauracoesSgdoc( $_1_u_sgdoc_idsgdoc, $_GET['_modulo'] );
				foreach ($resdesc as $k => $r) {?>
					<tr>
						<td><?=$r['versaoorigem']?></td>
						<td><?=['versao']?></td>
						<td><?=['motivoobs']?></td>
						<td><?=['criadopor']?></td>
						<td><?=dmahms($r['criadoem'])?></td>
					</tr>";
				<?}?>
			</table>	
		</div>

		<?$flversao = traduzid('sgdoctipo', 'idsgdoctipo', 'flversao', $_1_u_sgdoc_idsgdoctipo);
		if ($flversao == 'Y') {?>
			<div class="panel panel-default col-md-12 hideprint" >
				<div>	
					<? if(!empty($descricao)){ ?>					
						<i style="font-size: 18px;" title="Histórico de restaurações" onclick="modalhistorirestauracaoversao('<?=$descricao?>')" class="btn fa fa-info-circle laranja pull-right"></i>
					<? } ?>
				</div>
				<div class="panel-heading" data-toggle="collapse" href="#historicoinfo">
					Histórico do Documento				
				</div>
				<div class="panel-bodycollapse.show" id="historicoinfo"> 
					<table class="table table-striped planilha " style='width: 100%;'> 
						<tr class="header">
							<th>Versão</th>
							<th>Data</th>
							<th>Descrição</th>				
						</tr>
						<?if ($_1_u_sgdoc_status != 'APROVADO' AND $_1_u_sgdoc_status != 'OBSOLETO') {?>
							<tr class="header">
								<td><?=$_versaoanterior+1?>.0</td>
								<td><?=$_1_u_sgdoc_alteradoem?></td>
								<td><TEXTAREA COLS=92 ROWS=6 name="_1_<?=$_acao?>_sgdoc_acompversao" vnulo><?=$_1_u_sgdoc_acompversao?></TEXTAREA></td>
							</tr>
						<?}
						$resalt = DocumentoController::buscarHistoricoDeVersoes($_1_u_sgdoc_idsgdoc);
						foreach($resalt as $k => $rowalt){?>
							<tr class="respreto">
								<td><a class="pointer" onclick="janelamodal('form/sgdocupd.php?_acao=u&idsgdocupd=<?=$rowalt['idsgdocupd']?>')"><?=$rowalt["versao"]?>.<?=$rowalt["revisao"]?></a></td>
								<td align="center" nowrap><?=$rowalt["dmadata"]?></td> 
								<td><?=$rowalt["acompversao"]?></td>
							</tr>
						<?}?>
					</table>
				</div>
			</div>
		<?}?>
	</div>
<?}

if($_1_u_sgdoc_idsgdoctipo=='avaliacao' and !empty($_1_u_sgdoc_idpessoa)){
    
	$rownf=DocumentoController::buscarPendenciasPessoa($_1_u_sgdoc_idpessoa);
	if($rownf['divergencia']>0){?>
       	<div class="col-md-12 hideprint">
			<div class="panel panel-default" >
		    	<div class="panel-heading" data-toggle="collapse" href="#pendenciasinfo">Última(s) compra(s)</div>
				<div class="panel-body" > 
					<table class="table table-striped planilhacollapse.show" id="pendenciasinfo">
						<tr class="header">
							<td nowrap><a href="" onclick="janelamodal('report/impqualidadenf.php?_acao=u&idpessoa=<?=$_1_u_sgdoc_idpessoa?>')"><font color="Blue" >Sem Pendência</font></a></td>
							<td><?=$rownf['normal']?></td>
						</tr>
						<tr class="header">
							<td nowrap><a href=""  onclick="janelamodal('report/impqualidadenf.php?_acao=u&situacao=pendresolv&idpessoa=<?=$_1_u_sgdoc_idpessoa?>')"><font color="Blue" >Pendência(s) Resolvida(s)</font></a></td>
							<td><?=$rownf['pendresolv']?></td>
						</tr>
						<tr class="header">
							<td nowrap><a href="" onclick="janelamodal('report/impqualidadenf.php?_acao=u&situacao=pendnresolv&idpessoa=<?=$_1_u_sgdoc_idpessoa?>')"><font color="Blue" >Pendência(s) Não Resolvida(s)</font></a></td>
							<td><?=$rownf['pendnresolv']?></td>
						</tr>
					</table>
                </div>
            </div>
        </div>
	<?}
}


if($_1_u_sgdoc_idsgdoc){

	$tdoc=getObjeto("sgdoctipo",$_1_u_sgdoc_idsgdoctipo)["rotulo"];
	$stdoc=getObjeto("sgdoctipodocumento",$_1_u_sgdoc_idsgdoctipodocumento)["tipodocumento"];
	$dts=empty($stdoc)? $tdoc : $tdoc . " / " .$stdoc;
	?>
	<div class="print">
		<img class="copiancontrolada" id="imagencopia" border="0" src="../inc/img/copiancontrolada.gif"/>  
	</div>
	<table class="tbImpressao print">
		<thead>
			<tr>
				<td colspan="10">
					<table class="titulo">
						<tr>
							<td>
								<?
								$figrel=traduzid("empresa","idempresa","logosis",cb::idempresa());
								$figrel = str_replace("../", "", $figrel);
								?>
								<img class="logoesquerda" src="<?=$figrel?>">
							</td>

							<td style="text-align: center;white-space: inherit;">
								<div><?=$dts?></div>
								<div><?=$_1_u_sgdoc_titulo?></div>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<th colspan="999">
					<table>
						<tr><td>Cód.:</td><td style="width: 100%"><?=$_1_u_sgdoc_idregistro?></td></tr>
						<tr><td>Rev.:</td><td><?=$_1_u_sgdoc_versao.".".$_1_u_sgdoc_revisao?></td></tr>
						<tr>
							<td>Status:</td>
							<td><?= getObjeto("sgdocstatus", $_1_u_sgdoc_status)["des"]?></td>
						</tr>
						<?if(!empty($_1_u_sgdoc_responsavel)){
							if($_1_u_sgdoc_idsgdoctipo=='auditoria'){
								$strexe="Auditor Lider";
							}else{
								$strexe="Executor";
							}?>
							<tr>
								<td class="nowrap"><?=$strexe?>:</td>
								<td><?=$_1_u_sgdoc_responsavel?></td>
							</tr>
							<tr>
								<td class="nowrap">Início/Fim</td>
								<td><?=dma($_1_u_sgdoc_inicio)?> - <?=dma($_1_u_sgdoc_fim)?></td>
							</tr>
						<?}

						if(!empty($_1_u_sgdoc_responsavelsec)){?>
							<tr>
								<td class="nowrap">Auditor Participante:</td>
								<td><?=$_1_u_sgdoc_responsavelsec?></td>
							</tr>			
						<?}

						if(!empty($_1_u_sgdoc_grau)){?>
							<tr>
								<td class="nowrap">Grau:</td>
								<td><?=$_1_u_sgdoc_grau?></td>
							</tr>			
						<?}

						if(!empty($_1_u_sgdoc_impacto)){?>
							<tr>
								<td class="nowrap">Impacto:</td>
								<td><?=$_1_u_sgdoc_impacto?></td>
							</tr>	
						<?}

						if(!empty($_1_u_sgdoc_nota)){?>
							<tr>
								<td class="nowrap">Nota:</td>
								<td><?=$_1_u_sgdoc_nota?></td>
							</tr>			
						<?}

						if(!empty($_1_u_sgdoc_resultado)){?>
							<tr>
								<td class="nowrap">Resultado:</td>
								<td>				
									<?=$_1_u_sgdoc_resultado?>	
								</td>
							</tr>			
						<?}?>
					</table>
				</th>
			</tr>
		</thead>
		<tbody>
			<?
			if( $arrtipodoc["flquestionario"]=="Y"){?>
				<tr>
					<th>Qst.</th>
					<?
					$resp = DocumentoController::buscarCamposVisiveisPorIdsgdoctipodocumento($_1_u_sgdoc_idsgdoctipodocumento);
					$qtd=count($resp);
					$col = array();
					$rotcurto = array();
					$code = array();
					$datatype = array();
					foreach ($resp as $k => $rowp){
						array_push($col, $rowp["col"]);
						array_push($rotcurto, $rowp["rotcurto"]);
						array_push($code, $rowp["code"]);
						array_push($datatype, $rowp["datatype"]);
						?>
						<th><?=$rowp["rotcurto"]?></th>
					<?}?>
				</tr>  
				<?
				$rest = DocumentoController::buscarPaginas( $_1_u_sgdoc_idsgdoc );
				$qtdpag=count($rest);
				$vqtdpag=$qtdpag+1;
				$li=99;
				if($qtdpag > 0){
					foreach($rest as $k => $rowp){
						$li++;
						$i = 0;
						?>
						<tr>
							<td>
								<?=$rowp["pagina"]?>
							</td>
							<?
							while($i < $qtd){?>
								<td>
									<?=$rowp[$col[$i]]?>                                                            
								</td>
								<?
								$i++;
							}
							?>
							<?if(!($_1_u_sgdoc_status == 'APROVADO')){?>
								<td><a class="fa fa-trash vermelho fade hoververmelho" title="Excluir" idunidadeobjeto="" onclick="excluirpagina(<?=$rowp["idsgdocpag"]?>)"></a></td>
							<?}?>
						</tr>
					<?}
				} 
			
			}else{?>
				<tr>
					<td colspan="999"><?=$_1_u_sgdoc_conteudo?></td>
				</tr>
			<?}?>        
		</tbody>
	</table>

	<? if($_acao == "u"){	
		if($_pkid != null or $_pkid == ""){
			
			?>
			<div class="row screen">

			<?
			$rese = DocumentoController::buscarEventosVinculadosAoDoc($_GET['_modulo'],$_pkid);
			if($qtde=count($rese) > 0){
				$colmd = 6;
				?>
			
				<div class="col-md-6">
					<div class="panel panel-default">
						<div class="panel-heading"  data-toggle="collapse" href="#gpEventos">Evento(s)</div>
						<div class="panel-body collapse" id="gpEventos" style="padding-top: 8px !important;">
							<table  class="table table-striped planilha"> 
								<tr>
									<td>ID</td>
									<td>Evento</td>
									<td>Tipo</td>
									<td>Prazo</td>
									<td>Status</td>
								</tr>
								<?foreach($rese as $k => $rowe){?>
									<tr>
										<td>
											<a class="background-color: #FFEFD1; pointer hoverazul" title="Evento" onclick="janelamodal('?_modulo=evento&_acao=u&idevento=<?=$rowe["idevento"]?>')"><?=$rowe["idevento"]?></a>
										</td>
										<td><?=$rowe["evento"]?></td>
										<td><?=$rowe["eventotipo"]?></td>
										<td><?=dma($rowe["prazo"])?></td>
										<td><?=$rowe["status"]?></td>
									</tr>
								<?}?>
							</table>
						</div>            
					</div>   
				</div>

			<?}
		}
	}?>


	</div>
	
    <?
    
		$row= DocumentoController::buscarUltimaVersaoAprovada($_1_u_sgdoc_idsgdoc,$_1_u_sgdoc_versao);
		if(!empty($row)){
			$aprovador = $row['nome'];
			$alteradoem = $row['alteradoem'];
		}

		$elaborador=traduzid("pessoa","usuario","nome",$_1_u_sgdoc_criadopor,false);
		?>

		<TABLE class="tbImpressao pagebreakprint <?= ($_1_u_sgdoc_idempresa == 1 && ($_1_u_sgdoc_idsgdoctipo == "treinamento" OR $_1_u_sgdoc_idsgdoctipo == "avaliacao")) ? 'no-print': '' ?>">
			<thead>
			<tr>		
				<TD nowrap>Elaborador:<br><?=$elaborador?><br><?=$_1_u_sgdoc_criadoem?></TD>
				<TD style="width:100%;"></TD>
				<TD nowrap>Aprovador:<br><?=$aprovador?><br><?=$alteradoem?></TD>			
			</tr>
			</thead>
		</TABLE>

		<style>
		.listaitens{
			border: none;
			margin: 5px;
			padding: 0px;
		}
		.listaitens{
			font-size: 11px;
			list-style: none outside none;
		}
		.listaitens .cab{/* cabecalho para liste de itens*/
			color: gray;
			font-size:9px;
			list-style: none outside none;
		}

		</style>

		<table class="print">
			<tr>
				<?
				$resv = DocumentoController::buscarDocsVinculados($_1_u_sgdoc_idsgdoc);
				$qtdrows1= count($resv);

				if($qtdrows1>0 ){?>
					<td colspan="4">
						<ul class="listaitens">
							<li class="cab">Documentos vinculados:</li>
							<?foreach($resv as $k => $rdvinc){?>
								<li><a target="_blank" href="sgdocprint.php?acao=u&idsgdoc=<?=$rdvinc["idsgdoc"]?>"><?=$rdvinc["idsgdoc"]?> - <?=$rdvinc["titulo"]?></a></li>

							<?}?>
						</ul>
					</td>
				<?}?>
			</tr>
			<tr>
				<?
				$res = DocumentoController::buscarAnexosPorTipoObjetoIdObjeto( 'sgdoc', $_1_u_sgdoc_idsgdoc );
				$numarq= count($res);

				if($numarq>0  ){?>

					<td colspan="4">
						<ul class="listaitens">
							<li class="cab">Arquivos Anexos (<?=$numarq?>)</li>
							<?foreach ($res as $k => $row) {?>
								<li><a title="Abrir arquivo" target="_blank"  href="../upload/<?=$row["nome"]?>"><?=$row["nome"]?></a></li>
							<?}?>
						</ul>
					</td>
				<?}?>	
			</tr>	
		</TABLE>

<?}
$montainput = DocumentoController::buscarUltimaAssinaturaPendente($_1_u_sgdoc_idsgdoc,$_SESSION["SESSAO"]["IDPESSOA"]);
if (!empty($montainput)) {?>
	<input type="hidden" id="carimbopendente<?=$_SESSION["SESSAO"]["IDPESSOA"]?>" idcarrimbo="<?=$montainput["idcarrimbo"]?>">
<?}
if($sempermissaoed){?>
	<input type="hidden" disabled id="nao_edita_doc" value="Y">
<?}

if(array_key_exists("doc", getModsUsr("MODULOS")) == 1 && $_1_u_sgdoc_idsgdoc){?>
	<input type="hidden" disabled id="docmaster" value="Y">
<?}
require_once(__DIR__.'/js/sgdoc_js.php');
?>