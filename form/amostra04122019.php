<?
require_once("../inc/php/validaacesso.php");

if($_POST){
	require_once("../inc/php/cbpost.php");
}
$idpessoa=$_GET["idpessoa"];
//Parâmetros mandatórios para o carbon
$pagvaltabela = "amostra";
$pagvalcampos = array(
	"idamostra" => "pk"
);

//Recuperar a unidade padrão conforme módulo pré-configurado
$unidadepadrao = getUnidadePadraoModulo($_GET["_modulo"]);

//Recuperar o modulo de resultados associado conforme a unidade
$modResultadosPadrao = getModuloResultadoPadrao($unidadepadrao);


/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from amostra where idunidade=".$unidadepadrao." and  idamostra = '#pkid'";

/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
require_once("../inc/php/controlevariaveisgetpost.php");



function mostraUnidade(){
	global $_acao, $_1_u_amostra_idunidade, $unidadepadrao;

	if(!empty($_GET["idunidade"]) && $_acao=="i") $_1_u_amostra_idunidade = $_GET["idunidade"];

	if(!empty($_1_u_amostra_idunidade)){

		$unidade = traduzid("unidade","idunidade","unidade",$_1_u_amostra_idunidade);
		//Cria cor de back e foreground para a unidade
		$bg = str2Color($unidade);
		$fc = colorContrastYIQ($bg);
?>
	<input name="_1_<?=$_acao?>_amostra_idunidade" type="hidden" value="<?=$_1_u_amostra_idunidade?>">
	<span style="color:<?=$fc?>;background-color:#<?=$bg?>;" class="label label-default fonte10">
<?
		echo strtoupper(substr($unidade,0,2));
?>
	</span>
<?
	}else{

?>
	<select name="_1_<?=$_acao?>_amostra_idunidade" title="Unidade" tabindex="-99" style="background-color: transparent;" onchange="CB.setPrefUsuario('u',CB.modulo+'.unidadepreferencial',this.value)">
				<?fillselect("select idunidade,unidade from unidade
				where status='ATIVO' order by ord",$unidadepadrao);?></select>
<?
	}
}

//Tipos de amostras configuradas para cada Unidade
function jsonUnidadeTipoamostra(){
	$sql = "select u.idunidade, t.idsubtipoamostra
			from unidadeobjeto u
				join subtipoamostra t on (t.idsubtipoamostra=u.idobjeto)
			where u.tipoobjeto='subtipoamostra'
				and t.status = 'ATIVO'";

	$res = d::b()->query($sql);

	$arrtmp=array();
	while ($r = mysqli_fetch_assoc($res)) {
        $arrtmp[$r["idunidade"]][$r["idsubtipoamostra"]]="";
    }

    $json = new Services_JSON();
	return $json->encode($arrtmp);
}

// Configuração de inputs visíveis conforme combinação de tipo e subtipo de amostra
function jsonInputsTipoamostra(){
	global $unidadepadrao, $jsonTelaamostraconf;

	$arrConf = getAmostraConfInputs($unidadepadrao);

	$json = new Services_JSON();
	$jsonTelaamostraconf = json_encode($arrConf["arrcoluna"]);
	return $json->encode($arrConf["arrtipo"]);
}

/*
 * Tipos de amostra disponíveis
 */
function jsonTipoSubtipo(){
	global $unidadepadrao;
	$sql = "select o.idunidade,  s.idsubtipoamostra,s.normativa,s.subtipoamostra  as tiposubtipo
			from 
			 subtipoamostra s 
				join unidadeobjeto o on o.tipoobjeto='subtipoamostra' and o.idunidade = ".$unidadepadrao." and o.idobjeto = s.idsubtipoamostra
			where  s.status='ATIVO'
			order by tiposubtipo";

	$res = d::b()->query($sql) or die("Erro ao recuperar Tipo/Subtipo de Amostra: ".mysqli_error(d::b()));

	$arrtmp=array();
	$i=0;
	while ($r = mysqli_fetch_assoc($res)) {
	    if($r["normativa"]){$strnormativa=$r["normativa"];}else{$strnormativa='';}
        $arrtmp[$r["idunidade"]][$i]["idsubtipoamostra"]=$r["idsubtipoamostra"];
        $arrtmp[$r["idunidade"]][$i]["value"]=$r["idsubtipoamostra"];
        $arrtmp[$r["idunidade"]][$i]["label"]=  $r["tiposubtipo"].$strnormativa;
        $i++;
    }

    $json = new Services_JSON();
	return $json->encode($arrtmp);
}


function jsonServicos(){
	global $jsonServicos,$unidadepadrao;

	$sql = "select idprodserv,concat(codprodserv,' - ',descr) as descr
			from prodserv p join unidadeobjeto u on( u.idunidade = ".$unidadepadrao." and u.idobjeto = p.idprodserv and u.tipoobjeto = 'prodserv')
			where  p.status = 'ATIVO'
				and p.tipo='SERVICO'				
			order by p.descr";

	$res = d::b()->query($sql);

	$arrtmp=array();
	$i=0;
	while ($r = mysqli_fetch_assoc($res)) {
        $arrtmp[$i]["value"]=$r["idprodserv"];
		$arrtmp[$i]["label"]= ($r["descr"]);
		$i++;
    }

	$jsonServicos = json_encode($arrtmp);

}
jsonServicos();

$jsonEspecieFinalidade;
function jsonEspecieFinalidade(){
	global $jsonEspecieFinalidade;
	$sql = "select
				tef.idespeciefinalidade
				, p.plantel as especie
				, tef.tipoespecie
				, tef.finalidade
				, tef.calculoidade
				, tef.flgcalculo
                                ,tef.rotulo
			from especiefinalidade tef left join plantel p on(p.idplantel=tef.idplantel)
			where  tef.status='A'
				order by tef.especie, tef.tipoespecie, tef.finalidade";

	$res = d::b()->query($sql) or die("Erro ao recuperar finalidades:".mysqli_error(d::b()));

	$arrtmp=array();
	while ($r = mysqli_fetch_assoc($res)) {
        	$arrtmp[$r["idespeciefinalidade"]]["especie"]=($r["especie"]);
		$arrtmp[$r["idespeciefinalidade"]]["tipoespecie"]=($r["tipoespecie"]);
		$arrtmp[$r["idespeciefinalidade"]]["finalidade"]= ($r["finalidade"]);
		$arrtmp[$r["idespeciefinalidade"]]["calculoidade"]=($r["calculoidade"]);
		$arrtmp[$r["idespeciefinalidade"]]["flgcalculo"]= ($r["flgcalculo"]);
   		$arrtmp[$r["idespeciefinalidade"]]["rotulo"]= $r["rotulo"];
    }
	$jsonEspecieFinalidade = json_encode($arrtmp);
}
jsonEspecieFinalidade();

/*
$jsonTelaamostraconf;
function jsonTelaamostraconf(){
	global $jsonTelaamostraconf;
	$sql = "SELECT campo, idtipoamostra, idsubtipoamostra, idtelaamostraconf
			FROM telaamostraconf
			WHERE idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"];

	$res = d::b()->query($sql) or die("Erro ao recuperar configuração de inputs da amostra:".mysqli_error(d::b()));

	$arrtmp=array();
	while ($r = mysqli_fetch_assoc($res)) {
        $arrtmp[$r["campo"]][$r["idtipoamostra"]][$r["idsubtipoamostra"]]=$r["idtelaamostraconf"];
    }
	$jsonTelaamostraconf = json_encode($arrtmp);
}
jsonTelaamostraconf();
*/

function jsonClientes(){
	global $_acao, $_1_u_amostra_idpessoa, $jsonClientes,$jsonDetClientes;

	$sqlc = "SELECT p.idpessoa
			, if(p.cpfcnpj !='',concat(p.nome,' - ',p.cpfcnpj),p.nome) as nome
			,pf.observacaore
                        ,pf.pedidocp
			,p.cpfcnpj
			,sec.idpessoa as idsecretaria
			,sec.nome as secretaria
		FROM pessoa p
			left join pessoa sec on (sec.idpessoa = p.idsecretaria)
                        left join preferencia pf on(pf.idpreferencia= p.idpreferencia)
		WHERE p.status = 'ATIVO'
			AND p.idtipopessoa = 2			
		ORDER BY 2";

	$resc = d::b()->query($sqlc) or die("Erro ao recuperar clientes: ".d::b()->error);

	$arrtmp=array();
	$arrtmpdet=array();
	$i=0;
	while($r=  mysqli_fetch_assoc($resc)){
		$arrtmp[$i]["value"]=$r["idpessoa"];
		$arrtmp[$i]["label"]=($r["nome"]);
        $arrtmpdet[$r["idpessoa"]]["observacaore"]=($r["observacaore"]);
        $arrtmpdet[$r["idpessoa"]]["cpfcnpj"]=formatarCPF_CNPJ(($r["cpfcnpj"]));
		$arrtmpdet[$r["idpessoa"]]["secretaria"]=($r["secretaria"]);
		$arrtmpdet[$r["idpessoa"]]["idsecretaria"]=$r["idsecretaria"];
                $arrtmpdet[$r["idpessoa"]]["pedidocp"]=$r["pedidocp"];
		$i++;
	}
	$jsonDetClientes = json_encode($arrtmpdet);
	return json_encode($arrtmp);
}

$arrCores = ["silver", "#cc0000", "#0000cc", "#00cc00", "#990000","#ff6600", "#fcd202", "#b0de09", "#0d8ecf",  "#cd0d74"];
//die($arrCores[0]);

$iAgentes=0;
		
function listaTestes(){
	global $_acao, $_1_u_amostra_idamostra, $_1_u_amostra_idpessoa,$arrCores,$modResultadosPadrao,$iAgentes,$unidadepadrao,$_vids,$_1_u_amostra_idunidade;
	//die($_acao.$_1_u_amostra_idamostra);
	$duplica=$_GET["duplicaramostra"];
	if($_acao=="u" and !empty($_1_u_amostra_idamostra)){
		$sqlt = "
			SELECT
			  r.idresultado,
			  p.idprodserv,
			  p.descr,
			  p.codprodserv,
			  r.quantidade,
                          r.npedido,
			  r.status,
				r.criadopor,
				dmahms(r.criadoem) criadoem,
				r.alteradopor,
				dmahms(r.alteradoem) alteradoem,
				r.idlp,
				r.idsecretaria,
			r.impetiqueta,
			r.ord,
			r.loteetiqueta,
                        r.cobrar,
                        r.cobrancaobrig,
			(
				select count(*) as iagentes
				from lote l
				where l.tipoobjetosolipor='resultado' and l.idobjetosolipor=r.idresultado
            ) iagentes
			FROM
				resultado r 
				left join prodserv p on (r.idtipoteste = p.idprodserv)
			WHERE  r.idamostra = ". $_1_u_amostra_idamostra."
				and r.status !='OFFLINE'
			order by r.ord";

		$i=10;
		$rest = d::b()->query($sqlt)or die("Erro ao recuperar resultados: \n".mysqli_error(d::b())."\n".$sqlt);
		$_vids = '';
		while($r=  mysqli_fetch_assoc($rest)){
			$_vids .= $virgula.$r["idresultado"];
			$virgula = ',';
			$iAgentes+=$r["iagentes"];

			$title = ($r["loteetiqueta"])?"Lote ".$r["loteetiqueta"]:"Alterar Lote de Impressão";
			$classDrag = ($r["status"]=="ABERTO")?"dragExcluir":"";
			$disableteste = ($r["status"]=="ABERTO")?"":"readonly='readonly'";
                        
                        if($r["cobrar"]=='Y'){
                            $cobranca='N';
                            $checkedob="checked";
                        }else{
                            $cobranca='Y';
                            $checkedob="";
                        }
?>
	<tr class="<?=$classDrag?>" idresultado="<?=$r["idresultado"]?>" style="border-left-width:2px;border-left-style:solid;border-left-color:<?switch($r["status"]){case "ABERTO": ?>#c53632;<? break;case "PROCESSANDO": ?>#ffc107;<? break;case "FECHADO": ?>#40708F;<? break;case "ASSINADO": ?>#3c763d;<? break;default: ?>#333;<? break;}?>;">
	<td>
		<i class="fa fa-print pointer cinzaclaro" style="color:<?=$arrCores[$r["loteetiqueta"]]?>;" title="<?=$title?>" id="cbimp<?=$i?>" onclick="alteraLoteEtiqueta(<?=$i?>)"></i>
	</td>
        <td>
		<?if($_1_u_amostra_idunidade==1){?>
            <input type="checkbox" atval="<?=$cobranca?>" <?=$checkedob?> idresultado="<?=$r["idresultado"]?>"  style="border:0px" onclick="flgcobranca(this)" title="Cobrar"> 
		<?}?>			
        </td>
	<td style="white-space: nowrap;">
<?if($duplica=="Y"){?>
		<input type="hidden" name="_<?=$i?>_u_resultado_idresultado" value="<?=$r["idresultado"]?>">
		<input type="hidden" name="_<?=$i?>_u_resultado_loteetiqueta" value="<?=$r["loteetiqueta"]?>">
		<input type="hidden" name="_<?=$i?>_u_resultado_ord" value="<?=$r["ord"]?>">
		<input type="text" name="_<?=$i?>_u_resultado_idtipoteste" class="idprodserv" cbvalue="<?=$r["idprodserv"]?>" value="<?=$r["codprodserv"]?>" vnulo <?=$disableteste?>>
<?}else{?>
		<input type="hidden" name="_<?=$i?>_u_resultado_idresultado" value="<?=$r["idresultado"]?>">
                <input type="hidden" name="_<?=$i?>_u_resultado_loteetiqueta" value="<?=$r["loteetiqueta"]?>">
				<a href="?_modulo=<?=$modResultadosPadrao?>&_acao=u&idresultado=<?=$r["idresultado"]?>" target="_blank" style="color:<?switch($r["status"]){case "ABERTO": ?>#c53632;<? break;case "PROCESSANDO": ?>#ffc107;<? break;case "FECHADO": ?>#40708F;<? break;case "ASSINADO": ?>#3c763d;<? break;default: ?>#333;<? break;}?>"><?=$r["codprodserv"]?></a>
<?}?>
	</td>
	<td style="white-space: nowrap;">
		<input type="text" name="_<?=$i?>_u_resultado_quantidade" value="<?=$r["quantidade"]?>" style="width:30px" placeholder="Quant." vnulo vnumero>
</td>
<?
        if($unidadepadrao==1){// Diagnóstico Autógenas 
?>
	<td>
		<select name="_<?=$i?>_u_resultado_idsecretaria" class="idsecretaria" style="font-size:10px;" placeholder="Secretaria" duplicado>
			<option value=""></option>
<?
		$strs="select pp.idpessoa,pp.nome
		from pessoa p,pessoa pp
		where pp.idpessoa = p.idsecretaria
			and p.idpessoa =".$_1_u_amostra_idpessoa;

		fillselect($strs,$r["idsecretaria"]);
?>
		</select>
	</td>
<?
            if(!empty($_1_u_amostra_idpessoa)){
                $sqlpf="select pf.pedidocp 
                        from pessoa p join
                                preferencia pf on (pf.idpreferencia=p.idpreferencia)
                         where p.idpessoa =".$_1_u_amostra_idpessoa."  
                        and pedidocp='Y'";
                $respf=d::b()->query($sqlpf) or die("Erro ao buscar preferencia de numero de compra sql=".$sqlpf);
                $qtdpf =mysqli_num_rows($respf);
                if($qtdpf>0){
                   
?>
        <td>
            
            <input   name="_<?=$i?>_u_resultado_npedido" title="Obrigatório informar o pedido de compra."   type="text" value="<?=$r["npedido"]?>" class="size6" placeholder="N. Pedido" vnulo>
        </td>
<?
            
                    }//if($qtdpf>0){
            }else{//if(!empty($_1_u_amostra_idpessoa)){
?>
        <td>
            
        </td>
        <?
            }
        }else{// Diagnóstico Autógenas 
            if($r["cobrancaobrig"]=='Y'){
                $cobrancaobrig='N';
                $checkedob="checked";
            }else{
                $cobrancaobrig='Y';
                $checkedob="";
            }
            $sqlcob="SELECT * FROM notafiscalitens nfi WHERE nfi.idresultado =".$r["idresultado"];
            $rescob=d::b()->query($sqlcob) or die("Erro ao buscar cobrança na notafiscalitens sql=".$sqlcob);
            $qtdcob=mysqli_num_rows($rescob);
            if($qtdcob>0){               
                $desabilitaob=" disabled='disabled' ";
            }else{$desabilitaob='';}
        ?>
        <td align="center">
            <input <?=$desabilitaob?> type="checkbox" atval="<?=$cobrancaobrig?>" <?=$checkedob?> idresultado="<?=$r["idresultado"]?>"  style="border:0px" onclick="flgcobrancaobrig(this)" title="Obrigatório cobrança.">            
        </td>
<?
        }
?>

	<td>
<?
    $hidemove="";
    $sqlcob="SELECT * FROM notafiscalitens nfi WHERE nfi.idresultado =".$r["idresultado"];
    $rescob=d::b()->query($sqlcob) or die("Erro ao buscar cobrança na notafiscalitens sql=".$sqlcob);
    $qtdcob=mysqli_num_rows($rescob);
if($r["status"]!=="ABERTO" or $qtdcob>0){
	$hidemove="hidden";
}?>
		<i class="fa fa-arrows cinzaclaro hover move <?=$hidemove?>" title="Excluir teste"></i>
	</td>
</tr>
<?
			$i++;
		}
	}
}

function logImpressao(){
	global $_acao, $_1_u_amostra_idamostra;

	if($_1_u_amostra_idamostra){
		$sqlimp="select
					p.codprodserv
					,e.*
				from resultado r, impetiqueta e, prodserv p
				where e.status='ATIVO'
					and p.idprodserv = r.idtipoteste
					and e.idresultado = r.idresultado
					and r.idamostra =  ".$_1_u_amostra_idamostra."
				order by e.criadoem desc";

		$resimp=d::b()->query($sqlimp) or die("Erro ao recuperar impressão de resultados: ".  mysqli_error(d::b()));

		while($rowimp=mysqli_fetch_assoc($resimp)){
?>
				<tr class="respreto">
					<td><?=$rowimp['codprodserv']?></td>
					<td><?=$rowimp['criadopor']?></td>
					<td><?=dmahms($rowimp['criadoem'],true)?></td>
				</tr>
<?
		}
	}
}

$arrUltimaAmostra=array();
function dadosUltimaAmostra(){
	global $unidadepadrao;
	
	$sqla = "SELECT a.idamostra, a.dataamostra, dma(a.dataamostra) as dataamostrabr, a.idregistro
		FROM amostra a
		WHERE a.status in ('ABERTO','FECHADO')
			AND a.idunidade=".$unidadepadrao."
		ORDER BY a.idamostra desc
		LIMIT 1";

	$resc = d::b()->query($sqla) or die("Erro ao recuperar ultima amostra: ".d::b()->error);

	$i=0;
	$r= mysqli_fetch_assoc($resc);

	$arrA["idamostra"]=$r["idamostra"];
	$arrA["dataamostra"]=$r["dataamostra"];
	$arrA["dataamostrabr"]=$r["dataamostrabr"];

	return $arrA;
}
$arrUltimaAmostra = dadosUltimaAmostra();

//Coloca data atual em nova amostra e verifica se é inferior à  data atual
$dtUltimaAmostra=empty($arrUltimaAmostra["dataamostrabr"])?date("d/m/Y"):$arrUltimaAmostra["dataamostrabr"];
$_1_u_amostra_dataamostra = ($_acao=="i")?$dtUltimaAmostra:$_1_u_amostra_dataamostra;
//Compara as 2 datas
$dtUltima = new DateTime($arrUltimaAmostra["dataamostra"]);
$dtAtual = new DateTime("now");
$interval = date_diff($dtAtual, $dtUltima);
$intervaloDias=$interval->days;

if($intervaloDias>0 and $_acao=="i"){
	$stlAlertaDataAnterior="weight: bold; color: #d9534f;";
	$titleAlertaDataAnterior="Data da última amostra é inferior à  data de hoje.";
}

//Recuperar hora do DB para permitir calcular o tempo gasto para registro de cada amostra
function getDatahoraDb(){
	$sdh = "select dmahms(now()) as dmahms";
	$rdh = d::b()->query($sdh) or die("Erro ao recuperar datetime do db: ".d::b()->error);
	$r= mysqli_fetch_assoc($rdh);
	return $r["dmahms"];
}


//print_r($arrLoteTra);die;

$arrTRAAssociado=getObjeto("amostra", $_1_u_amostra_idamostra, "idamostra");

function desenhaTRA(){
	global $_1_u_amostra_idamostra,$_1_u_amostra_idpessoa, $_1_u_amostra_idregistro, $_1_u_amostra_exercicio, $arrLoteTra, $arrTRAAssociado;

	$arrTmp=$arrLoteTra;
	
		$sqla="select  l.partida,l.exercicio,l.idlote,l.status,o.idobjeto
		    from amostra a,resultado r,prodserv p,lote l
                      left join unidadeobjeto o on(o.tipoobjeto='modulo' and o.idobjeto like ('semente%') and o.idunidade = l.idunidade)
		    where r.idamostra = a.idamostra
		    and p.idprodserv = r.idtipoteste
		    and  l.tipoobjetosolipor='resultado' 
		    and l.idobjetosolipor=r.idresultado	
		    and a.idamostra = ".$arrTRAAssociado['idamostra']." order by l.partida,l.exercicio";
		$resa=d::b()->query($sqla) or die("Erro ao buscar agentes da amostra : " . mysql_error() . "<p>SQL:".$sqla);

?>
						<div class="papel hover" id="formTra">
							<h6 class="cinzaclaro" style="white-space: nowrap">Agentes isolados:</h6>
							<hr>
<?
		while ($rowa=mysqli_fetch_assoc($resa)){
			if($rowa['status']=="APROVADO" or $rowa['status']=="PENDENTE"){
?>	
			<h6><a href="?_modulo=<?=$rowa['idobjeto']?>&_acao=u&idlote=<?=$rowa["idlote"]?>" target="_blank" title="Status: <?=$rowa['status']?>"><?=$rowa['partida']."/".$rowa['exercicio']?></a></h6>
<?
                        }else{
?>
                        <h6><a href="?_modulo=<?=$rowa['idobjeto']?>&_acao=u&idlote=<?=$rowa["idlote"]?>" target="_blank" style="color:#c53632;" title="Status: <?=$rowa['status']?>"><?=$rowa['partida']."/".$rowa['exercicio']?></a></h6>  
<?
                        }
		}
?>							<br/>
						</div>
<?
	
}//function desenhatra

function buscaamostras(){
	global $_1_u_amostra_idamostra,$_1_u_amostra_idpessoa;
	
	$sql="select a.idamostra,a.idregistro,a.exercicio,s.subtipoamostra
		from amostra a left join subtipoamostra s on (a.idsubtipoamostra =s.idsubtipoamostra)
		where a.idamostratra = ".$_1_u_amostra_idamostra." order by a.idregistro,a.exercicio";
	$res= d::b()->query($sql) or die("Erro ao buscar amostras : " . mysql_error() . "<p>SQL:".$sql);
	$qtd= mysqli_num_rows($res);
?>
				
				
						<div class="papel hover inlineblocktop" id="novoTra">
							<h6 class="cinza" style="white-space: nowrap">Amostras</h6>
						<?
						while($row=mysqli_fetch_assoc($res)){
						?>
							<h6 style=" margin:  0px;"><a href="?_modulo=amostraautogenas&_acao=u&idamostra=<?=$row["idamostra"]?>" target="_blank" title="<?=$row["subtipoamostra"]?>"><?=$row['idregistro']."/".$row['exercicio']?>-<?=$row["subtipoamostra"]?></a> <i class="fa fa-trash cinzaclaro hoververmelho btn-lg pointer" onclick="rettraamostra(<?=$row["idamostra"]?>)" title="Retirar"></i></h6>
						<?
                                                    $sqlre="select r.idresultado,r.status,p.codprodserv 
																from resultado r,prodserv p,resultadoamostralad ra
																where r.idresultado=ra.idresultado
																and ra.idamostra = ".$row["idamostra"]."
																and p.idprodserv = r.idtipoteste order by p.codprodserv";
                                                    $resre= d::b()->query($sqlre) or die("Erro ao buscar resultados da amostras autogenas : " . mysql_error() . "<p>SQL:".$sqlre);
                                                    while($rowre=mysqli_fetch_assoc($resre)){
                                                        if($rowre['status']=='ASSINADO'){
                                                            $cor='#39bb3c;';//assinado
                                                        }elseif($rowre['status']=='FECHADO'){
                                                            $cor='#40708F;';//fechado
                                                        }else{
                                                            $cor='#c53632;'; //aberto
                                                        }
                                                ?>
                                                       <h6 style="font-size: 8px; margin:  0px;"><a style="color:<?=$cor?>" href="?_modulo=resultsuinos&_acao=u&idresultado=<?=$rowre["idresultado"]?>" target="_blank" title="resultado"><?=$rowre['codprodserv']?></a></h6>
                                                <?
                                                    }
						}	
						?>
							<br/>&nbsp;
						</div>
<?	

}//function buscaamostras(){

?>
<div class="col-md-8">
<div class="panel panel-default">
	<div class="panel-heading">
		<table id="amostra" style="width: 100%;">
		<tr>
			<td>
			    
			    <strong>
				<?if($unidadepadrao!=9){?>
				Registro:
				<?}else{echo("TEA/TRA");}?>
			    </strong>
			</td>
			<td id="cabRegistro">
		<?		
		if(!empty($_1_u_amostra_idregistro)){
		echo "<label class='alert-warning'>".$_1_u_amostra_idregistro."</label>";
		}else{
		?>
		<input name="_1_<?=$_acao?>_amostra_inicioedicao" type="hidden" value="<?=getDatahoraDb()?>">
		Nova Amostra
		<?
		}
		?>
			</td>
			<td style="width: 30px;">Data:</td>
			<td style="width: 100px;">
				<input name="_1_<?=$_acao?>_amostra_idamostra" type="hidden" value="<?=$_1_u_amostra_idamostra?>">
				<input name="_1_<?=$_acao?>_amostra_exercicio" type="hidden" value="<?=$_1_u_amostra_exercicio?>">
				<input name="_1_<?=$_acao?>_amostra_status" type="hidden" disabled value="<?=$_1_u_amostra_status?>">
				<input name="_1_<?=$_acao?>_amostra_dataamostra" type="text" class="calendario" value="<?=$_1_u_amostra_dataamostra?>" autocomplete="off" vnulo style="<?=$stlAlertaDataAnterior?>" title="<?=$titleAlertaDataAnterior?>">
			</td>
			<td style="width: 30px;">Amostra:</td>
			<td>
				
				<div class="input-group input-group-sm">
					<input name="_1_<?=$_acao?>_amostra_idsubtipoamostra" type="text" cbvalue="<?=$_1_u_amostra_idsubtipoamostra?>" value="<?=traduzid("subtipoamostra","idsubtipoamostra","subtipoamostra",$_1_u_amostra_idsubtipoamostra)?>" vnulo>
					<span class="input-group-addon" title="Editar Campos Visíveis" id="editarCamposVisiveis"><i class="fa fa-eye pointer"></i></span>
				</div>
			</td>
			<td style="width: 30px;">

			</td>
			<td style="width: 100px;display:none;"><?mostraUnidade();?></td>
		</tr>
		<?
		if(!empty($_1_u_amostra_idobjetosolipor) and  $_1_u_amostra_tipoobjetosolipor=='loteativ' ){
		$_sql="select l.idlote,l.partida,l.exercicio,l.piloto
				from loteativ a,lote l
			    where a.idloteativ= ".$_1_u_amostra_idobjetosolipor."
			    and l.idlote = a.idlote ";
		$_res=d::b()->query($_sql) or die("Erro ao buscar partida da produção : " . mysql_error() . "<p>SQL:".$_sql);
		$numpart= mysqli_num_rows($_res);
		    if($numpart>0){
			$_part=mysqli_fetch_assoc($_res);
		?>
		<tr>
		    <td>Produto:</td>
		    <td>
			<a class="pointer hoverazul" title="Formalização" onclick="janelamodal('?_modulo=formalizacao&_acao=u&idlote=<?=$_part['idlote']?>')"><?=$_part['piloto']=='Y'?'PP':''?> <?=$_part['partida']?>/<?=$_part['exercicio']?> </a>
		    </td>
		</tr>
		<?
		    }
		
		}
		?>
		</table>
	</div>
	<div class="panel-body">
<style>
.rowDin div[class*="col-"]{
	padding: 3px 15px;
	white-space: nowrap;
}
.rowDin div[id*=lb_]{
	font-size: 12px;
}
.rowDin input,
.rowDin select{
	height: 22px !important;
}

select.idsecretaria {
    width: 96px;
}
</style>
<div class="row">
	<div class="col-md-1">Cliente:</div>
	<div class="col-md-8">
            <?
            if(!empty($_1_u_amostra_idamostra) and $_acao=="u"){
                $s="select i.* from resultado r,notafiscalitens i
                    where r.idamostra = ".$_1_u_amostra_idamostra."
                    and r.idresultado = i.idresultado";
                $sr = d::b()->query($s); 
                $qr= mysqli_num_rows($sr);
                if($qr>0){
                    $redon="readonly='readonly'";
                    $tredon="Amostra possui teste em cobrança.";
                }else{
                    $redon="";$tredon="";
                }
            }
            ?>
		<input <?=$redon?> title="<?=$tredon?>" name="_1_<?=$_acao?>_amostra_idpessoa" type="text" cbvalue="<?=$_1_u_amostra_idpessoa?>" value="<?=traduzid("pessoa","idpessoa","if(cpfcnpj !='',concat(nome,' - ',cpfcnpj),nome)",$_1_u_amostra_idpessoa)?>" vnulo>
	</div>
	<div class="col-md-3">
		<table>
		<tr>
			<td style="width: 3em;">
				<a href="javascript:toggleOficial();" id="aToggleOficial">
					<input name="_1_<?=$_acao?>_amostra_idsecretaria" type="hidden" value="<?=$_1_u_amostra_idsecretaria?>">
					<i class="fa fa-shield fa-2x" style="position:absolute;"></i>
					&nbsp;
				</a>
			</td>
			<td style="white-space: nowrap;">
				<span id="lbcnpj" style="vertical-align: middle;"></span>
			</td>
		</tr>
		</table>
	</div>
</div>
<div class="row rowDin hidden">
	<div class="col-md-1" id="lb_nroamostra">Qtd.
	</div>
	<div class="col-md-3" id="col_nroamostra" colspan="10"><input placeholder="Não Informado" type="text"
		name="_1_<?=$_acao?>_amostra_nroamostra" id="nroamostra" title="Qtd."
		value="<?=$_1_u_amostra_nroamostra?>" size="10" autocomplete="off" vnulo>
	</div>
	<div class="col-md-1" id="lb_descricao">Descri&ccedil;&atilde;o:</div>
	<div class="col-md-7"  id="col_descricao">
		<input type="text" name="_1_<?=$_acao?>_amostra_descricao" title="Descrição" value="<?=$_1_u_amostra_descricao?>" autocomplete="off">
	</div>
</div>
<hr>
<div class="row rowDin hidden">
	<div class="col-md-2" id="lb_idnucleo">
<?
/*if($_1_u_amostra_idnucleo && $_1_u_amostra_status!="ABERTO"){
	echo ucfirst(strtolower(traduzid("nucleo", "idnucleo", "rotulonucleotipo", $_1_u_amostra_idnucleo,false))).":";
}else{*/
?>
		<select name="_1_<?=$_acao?>_amostra_rotulonucleotipo" id="rotulonucleotipo">
<?fillselect(array("NUCLEO"=>"Núcleo","INTEGRACAO"=>"Integração","PRODUTO"=>"Produto","PROP"=>"Propriedade","FASE"=>"Fase","DESCRICAO"=>"Descrição"),$_1_u_amostra_rotulonucleotipo);?>
		</select>
<?//}?>
	</div>

<?
//if($_1_u_amostra_status=="ABERTO" || $_acao=="i"){
if(1==1){
?>
	<div class="col-md-4" id="col_idnucleo">
		<div class="input-group input-group-sm">
			<input name="_1_<?=$_acao?>_amostra_idnucleo" title="Núcleo" type="text" cbvalue="<?=$_1_u_amostra_idnucleo?>" value="<?=traduzid("nucleo","idnucleo","nucleo",$_1_u_amostra_idnucleo,false)?>" class="ui-autocomplete-input" autocomplete="off">
			<span class="input-group-addon" id="limparnucleo" title="Limpar informações de Núcleo"><i class="fa fa-eraser pointer" onclick="resetNucleo()"></i></span>
			<span class="input-group-addon" id="editarnucleo" title="Editar Núcleo"><i class="fa fa-pencil pointer" onclick="alterarNucleo()"></i></span>
			<input type="hidden" readonly title="Núcleo Amostra" name="_1_<?=$_acao?>_amostra_nucleoamostra" id="nucleoamostra" value="<?=$_1_u_amostra_nucleoamostra?>">
		</div>
	</div>
	<div class="col-md-2" id="lb_idade">Idade:</div>
	<div class="col-md-4" id="col_idade" >
	    <input placeholder="Não Informado" autocomplete="off" type="text"
			name="_1_<?=$_acao?>_amostra_idade" id="idade" title="Idade"
			value="<?=$_1_u_amostra_idade?>"  style="width:45%;display: inline-block;" >
		<select name="_1_<?=$_acao?>_amostra_tipoidade" id="tipoidade" style="width:45%;display: inline-block;" >
			<option></option>
		<?fillselect(array('Dia(s)'=>'Dia(s)','Semana(s)'=>'Sem(s)','Mês(es)'=>'Mês(es)','Ano(s)'=>'Ano(s)','ª Progênie'=>'ª Progênie'),$_1_u_amostra_tipoidade); ?>
		</select>
	</div>
	
<?}else{?>
	<div class="col-md-5" id="col_nucleoamostra" style="display: inline-block;">
		Núcleo: <input autocomplete="off" type="text" readonly title="Núcleo Amostra" name="_1_<?=$_acao?>_amostra_nucleoamostra" id="nucleoamostra" value="<?=$_1_u_amostra_nucleoamostra?>" size="20" style="display: inline-block;" >
	</div>
	<div class="col-md-2" id="lb_idespeciefinalidade">
		Espécie/finalidade:
	</div>
	<div class="col-md-3" id="col_idespeciefinalidade">
		<input type="text" name="_1_<?=$_acao?>_amostra_idespeciefinalidade"  title="Tipo de Espécie/Finalidade" cbvalue  vnulo>
	</div>
<?}?>
</div>
<div class="row rowDin hidden">
	<div class="col-md-2" id="lb_lote">Lote:</div>
	<div class="col-md-4" id="col_lote"><input placeholder="Não Informado" type="text" name="_1_<?=$_acao?>_amostra_lote" title="Lote" id="lote" value="<?=$_1_u_amostra_lote?>" size="25" autocomplete="off" ></div>

	<div class="col-md-2" id="lb_idespeciefinalidade">
		Espécie/finalidade:
	</div>
	<div class="col-md-4" id="col_idespeciefinalidade">
		<input type="text" name="_1_<?=$_acao?>_amostra_idespeciefinalidade" vnuo title="Tipo de Espécie/Finalidade" cbvalue="<?=$_1_u_amostra_idespeciefinalidade?>" value="<?=traduzid("vwespeciefinalidade","idespeciefinalidade","especietipofinalidade",$_1_u_amostra_idespeciefinalidade)?>" vnulo>
	</div>
		
</div>
<div class="row rowDin hidden">
    <div class="col-md-2" id="lb_pedido">N&ordm; Clifor/Pedido:</div>
    <div class="col-md-4" id="col_pedido">
	    <input autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_pedido" id="pedido" title="N&ordm; Clifor/Pedido" value="<?=$_1_u_amostra_pedido?>">
    </div>
    <div class="col-md-2" id="lb_granja">Granja:</div>
    <div class="col-md-4" id="col_granja"><input placeholder="Não Informado" autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_granja" id="granja" title="Granja" value="<?=$_1_u_amostra_granja?>" size="20" ></div>
    <div class="col-md-2" id="lb_estexterno">Reg. Externo:</div>
    <div class="col-md-3" id="col_estexterno"> 
        <input autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_estexterno" id="estexterno" title="Registro Externo" value="<?=$_1_u_amostra_estexterno?>">       
    </div>
</div>

<div class="row rowDin hidden">
 <div class="col-md-2" id="lb_unidadeepidemiologica">Unidade Epidemiológica:</div>
    <div class="col-md-4" id="col_unidadeepidemiologica"><input placeholder="Não Informado" autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_unidadeepidemiologica" id="unidadeepidemiologica" title="Unidade Epidemiológica" value="<?=$_1_u_amostra_unidadeepidemiologica?>" size="20" ></div>
</div>
<div class="row rowDin hidden">
	<div class="col-md-2" id="lb_cpfcnpjprod">CNPJ/CPF (Proprietário):</div>
	<div class="col-md-4" id="col_cpfcnpjprod">
		<input placeholder="Não Informado" autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_cpfcnpjprod" id="cpfcnpjprod" title="CPF/CNPJ" value="<?=$_1_u_amostra_cpfcnpjprod?>" >
	</div>
	<div class="col-md-1" id="lb_cidade">UF:</div>
	<div class="col-md-1" id="col_cidade">
	    <select class="size4" name="_1_<?=$_acao?>_amostra_uf" id="uf" title="uf" >
		<option value=""></option>
	    <?fillselect(array('AC'=>'AC','AL'=>'AL','AM'=>'AM','AP'=>'AP','BA'=>'BA','CE'=>'CE','DF'=>'DF','ES'=>'ES','GO'=>'GO','MA'=>'MA','MG'=>'MG','MS'=>'MS','MT'=>'MT','PA'=>'PA','PB'=>'PB','PE'=>'PE','PI'=>'PI','PR'=>'PR','RJ'=>'RJ','RN'=>'RN','RO'=>'RO','RR'=>'RR','RS'=>'RS','SC'=>'SC','SE'=>'SE','SP'=>'SP','TO'=>'TO','EX'=>'EX'),$_1_u_amostra_uf); ?>
	    </select>
	</div>
	<div class="col-md-1" id="lb_cidade">Cidade:</div>
	<div class="col-md-3" id="col_cidade"> 
	    <input autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_cidade" id="cidade" title="cidade" value="<?=$_1_u_amostra_cidade?>">
	    <!--
	    <select name="_1_<?=$_acao?>_amostra_cidade" id="cidade" title="Cidade" >
		<option value="">Não informado</option>
		<?fillselect("SELECT cidade,cidade 
		FROM nfscidadesiaf order by cidade;"
		,$_1_u_amostra_cidade);?>
	    </select>	
	    -->
	</div>
</div>

<div class="row rowDin hidden">
	<div class="col-md-2" id="lb_numeroanimais">N&ordm; de animais:</div>
	<div class="col-md-4" id="col_numeroanimais">
		<input autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_numeroanimais" title="N&ordm; de Animais" id="numeroanimais" value="<?=$_1_u_amostra_numeroanimais?>">
	</div>
	<div class="col-md-2" id="lb_galpao">Galp&atilde;o/Aviário:</div>
	<div class="col-md-4" id="col_galpao">
	    <input placeholder="Não Informado" autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_galpao" title="Galpão" id="galpao" value="<?=$_1_u_amostra_galpao?>" >
	</div>	
</div>
<div class="row rowDin hidden">
	<div class="col-md-2" id="lb_alojamento">Data do Alojamento:</div>
	<div class="col-md-4" id="col_alojamento">
		<input class="calendario" autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_alojamento" title="Data do Alojamento" id="alojamento" value="<?=$_1_u_amostra_alojamento?>">
	</div>
	<div class="col-md-2" id="lb_numgalpoes">Nº Galp&otilde;es</div>
	<div class="col-md-4" id="col_numgalpoes">
	    <input placeholder="Não Informado" autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_numgalpoes" title="Numero de Galpões" id="numgalpoes" value="<?=$_1_u_amostra_numgalpoes?>" >
	</div>	
</div>
<div class="row rowDin hidden">
	<div class="col-md-2" id="lb_linha">Linha:</div>
	<div class="col-md-4" id="col_linha">
		<input autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_linha" id="linha" title="Linha" value="<?=$_1_u_amostra_linha?>">
	</div>
	<div class="col-md-2" id="lb_regoficial">Nº Reg. Of.:</div>
	<div class="col-md-4" id="col_regoficial">
		<input autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_regoficial" id="regoficial" title="Número do Registro Oficial" value="<?=$_1_u_amostra_regoficial?>">
	</div>
	

</div>

<div class="row rowDin hidden">
	<div class="col-md-2" id="lb_nsvo">Nº SVO:</div>
	<div class="col-md-4" id="col_nsvo">
		<input placeholder="Não Informado" autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_nsvo" id="nsvo" title="Número SVO." value="<?=$_1_u_amostra_nsvo?>" style="font-size: 12px;" >
	</div>
	<div class="col-md-4" id="lb_rejeitada">Amostras Rejeitadas / Descartadas:</div>
	<div class="col-md-2" id="col_rejeitada">
		<input placeholder="0" autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_rejeitada" id="rejeitada" title="Rejeitadas" value="<?=$_1_u_amostra_rejeitada?>" style="font-size: 12px;" >
	</div>
</div>
<hr>
<div class="row rowDin hidden">
		<div class="col-md-2" id="lb_sinaisclinicosinicio">Início sinais clínicos:</div> 
		<div class="col-md-10" id="col_sinaisclinicosinicio"><input name="_1_<?=$_acao?>_amostra_sinaisclinicosinicio" type="text" title="Início Sinais Clínicos" value="<?=$_1_u_amostra_sinaisclinicosinicio?>"></div>
</div>
<div class="row rowDin hidden">
		<div class="col-md-2" id="lb_sinaisclinicos">Sinais Clínicos:</div>
		<div class="col-md-10" id="col_sinaisclinicos"><textarea name="_1_<?=$_acao?>_amostra_sinaisclinicos" cols="61" rows="2" title="Sinais Clínicos"><?=$_1_u_amostra_sinaisclinicos?></textarea></div>
</div>
<div class="row rowDin hidden">
		<div class="col-md-2" id="lb_achadosnecropsia">Achados de Necrópsia:</div>
		<div class="col-md-10" id="col_achadosnecropsia"><textarea name="_1_<?=$_acao?>_amostra_achadosnecropsia" cols="61" rows="3" title="Achados de Necrópsia"><?=$_1_u_amostra_achadosnecropsia?></textarea></div>
</div>
<div class="row rowDin hidden">
		<div class="col-md-2" id="lb_suspclinicas">Suspeitas Clínicas:</div>
		<div class="col-md-10" id="col_suspclinicas"><textarea name="_1_<?=$_acao?>_amostra_suspclinicas" cols="61" rows="2" title="Suspeitas Clínicas"><?=$_1_u_amostra_suspclinicas?></textarea></div>
</div>
<div class="row rowDin hidden">
		<div class="col-md-2" id="lb_histproblema">Histórico do Problema:</div>
		<div class="col-md-10" id="col_histproblema"><textarea name="_1_<?=$_acao?>_amostra_histproblema" cols="61" rows="3" title="Histórico do Problema"><?=$_1_u_amostra_histproblema?></textarea></div>
</div>


<div class="row rowDin hidden">
		<div class="col-md-2" id="lb_morbidade">Morbidade/N&ordm; animais:</div>
		<div class="col-md-2" colspan="5" id="col_morbidade">
			<input autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_morbidade" id="morbidade" title="" value="<?=$_1_u_amostra_morbidade?>">
		</div>
		<div class="col-md-2" id="lb_letalidade">Letalidade/N&ordm; animais:</div>
		<div class="col-md-2" id="col_letalidade">
			<input autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_letalidade" id="letalidade" title="" value="<?=$_1_u_amostra_letalidade?>">
		</div>
		<div class="col-md-2" id="lb_mortalidade">Mortalidade/N&ordm; animais:</div>
		<div class="col-md-2" id="col_mortalidade">
			<input autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_mortalidade" id="mortalidade" title="" value="<?=$_1_u_amostra_mortalidade?>">
		</div>
</div>


<div class="row rowDin hidden">
		<div class="col-md-2" id="lb_usomedicamentos">Uso de Medicamentos:</div>
		<div class="col-md-10" id="col_usomedicamentos"><input name="_1_<?=$_acao?>_amostra_usomedicamentos" type="text" title="Uso de Medicamentos" value="<?=$_1_u_amostra_usomedicamentos?>"></div>
</div>
<div class="row rowDin hidden">
		<div class="col-md-2" id="lb_usovacinas">Uso de Vacinas:</div>
		<div class="col-md-10" id="col_usovacinas"><input name="_1_<?=$_acao?>_amostra_usovacinas" type="text" title="Uso de Vacinas"  value="<?=$_1_u_amostra_usovacinas?>"></div>
</div>
<div class="row rowDin hidden">
	<div class="col-md-1" id="lb_sexo">Sexo:</div>
	<div class="col-md-3" id="col_sexo" colspan="10">
		<select name="_1_<?=$_acao?>_amostra_sexo" id="sexo" title="Sexo">
			<option value=""></option>
		<?fillselect(array('Macho'=>'Macho','Fêmea'=>'Fêmea','Macho/Fêmea'=>'Macho/Fêmea'),$_1_u_amostra_sexo); ?>
		</select>
	</div>

	<div class="col-md-2" id="lb_datacoleta">Data Coleta:</div>
	<div class="col-md-3" id="col_datacoleta">
		<input placeholder="Não Informado" autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_datacoleta" id="datacoleta" title="Data Coleta" class="calendario" value="<?=$_1_u_amostra_datacoleta?>" >
	</div>

	<div class="col-md-1" id="lb_clienteterceiro">Cliente 3&ordm;:</div>
	<div class="col-md-3" id="col_clienteterceiro" colspan="10"><input autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_clienteterceiro" id="clienteterceiro" title="Cliente Terceiro" value="<?=$_1_u_amostra_clienteterceiro?>"></div>

</div>
<div class="row rowDin hidden">
		<div class="col-md-2" id="lb_formaarmazen">Forma de <br>Armazenamento:</div>
		<div class="col-md-4" id="col_formaarmazen">
			<input autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_formaarmazen" id="clienteterceiro" title="Forma de Armazenamento" value="<?=$_1_u_amostra_formaarmazen?>">
		</div>
		<div class="col-md-3" id="lb_meiotransp">Meio de Transp. Amostras:</div>
		<div class="col-md-3" id="col_meiotransp">
			<input autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_meiotransp" id="clienteterceiro" title="Meio de Transporte" value="<?=$_1_u_amostra_meiotransp?>">
		</div>
</div>
<div class="row rowDin hidden">
		<div class="col-md-2" id="lb_condconservacao">Condições de <br>Conservação:</div>
		<div class="col-md-10" id="col_condconservacao"><textarea name="_1_<?=$_acao?>_amostra_condconservacao" title="Condições de conservação" cols="61" rows="3"><?=$_1_u_amostra_condconservacao?></textarea></div>
</div>
<div class="row rowDin hidden">
		<div class="col-md-2" id="lb_nucleoorigem">N&uacute;cleo Origem:</div>
		<div class="col-md-3" id="col_nucleoorigem" colspan="10"><input placeholder="Não Informado" autocomplete="off" type="text"
			name="_1_<?=$_acao?>_amostra_nucleoorigem" id="nucleoorigem" title="Núcleo de Origem"
			value="<?=$_1_u_amostra_nucleoorigem?>" ></div>

		<div class="col-md-1" id="lb_tipo">Tipo:</div>
		<div class="col-md-3" id="col_tipo" colspan="5"><input autocomplete="off" type="text" title="Tipo"
			name="_1_<?=$_acao?>_amostra_tipo" id="tipo"
			value="<?=$_1_u_amostra_tipo?>"></div>
</div>
<div class="row rowDin hidden">
		<div class="col-md-1" id="lb_especificacao">Especifica&ccedil;&otilde;es:</div>
		<div class="col-md-3" id="col_especificacao"><input autocomplete="off" type="text"
			name="_1_<?=$_acao?>_amostra_especificacao" id="especificacao" title="Especificação"
			value="<?=$_1_u_amostra_especificacao?>"></div>

		<div class="col-md-1" id="lb_fornecedor">Fornecedor:</div>
		<div class="col-md-3" id="col_fornecedor"><input autocomplete="off" type="text" title="Fornecedor"
			name="_1_<?=$_acao?>_amostra_fornecedor" id="fornecedor"
			value="<?=$_1_u_amostra_fornecedor?>"></div>

		<div class="col-md-1" id="lb_partida">Partida:</div>
		<div class="col-md-3" id="col_partida"><input autocomplete="off" type="text" title="Partida"
			name="_1_<?=$_acao?>_amostra_partida" id="partida"
			value="<?=$_1_u_amostra_partida?>"></div>

</div>
<div class="row rowDin hidden">
		<div class="col-md-1" id="lb_datafabricacao">Data Fabrica&ccedil;&atilde;o:</div>
		<div class="col-md-3" id="col_datafabricacao" colspan="3"><input autocomplete="off"
			type="text" name="_1_<?=$_acao?>_amostra_datafabricacao"
			id="datafabricacao" title="Data de Fabricação" value="<?=$_1_u_amostra_datafabricacao?>"></div>

		<div class="col-md-1" id="lb_identificacaochip">Chip/Identif.:</div>
		<div class="col-md-3" id="col_identificacaochip"><input autocomplete="off" type="text"
			name="_1_<?=$_acao?>_amostra_identificacaochip" id="identificacaochip" title="Identificação/Chip"
			value="<?=$_1_u_amostra_identificacaochip?>"></div>

		<div class="col-md-1" id="lb_diluicoes">Dilui&ccedil;&otilde;es:</div>
		<div class="col-md-3" colspan="3" id="col_diluicoes"><input autocomplete="off" type="text"
			name="_1_<?=$_acao?>_amostra_diluicoes" id="diluicoes" title="Diluições"
			value="<?=$_1_u_amostra_diluicoes?>"></div>
</div>
<div class="row rowDin hidden">
		<div class="col-md-1" id="lb_nroplacas">N&ordm; Placas:</div>
		<div class="col-md-3" id="col_nroplacas"><input autocomplete="off" type="text"
			name="_1_<?=$_acao?>_amostra_nroplacas" id="nroplacas" title="Número de Placas"
			value="<?=$_1_u_amostra_nroplacas?>"></div>

		<div class="col-md-1" id="lb_nrodoses">N&ordm; Doses</div>
		<div class="col-md-3" id="col_nrodoses" colspan="5"><input autocomplete="off" type="text"
			name="_1_<?=$_acao?>_amostra_nrodoses" id="nrodoses" title="Número de Doses"
			value="<?=$_1_u_amostra_nrodoses?>"></div>

		<div class="col-md-1" id="lb_semana">Semana:</div>
		<div class="col-md-3" id="col_semana" colspan="5"><input autocomplete="off" type="text"
			name="_1_<?=$_acao?>_amostra_semana" id="semana" title="Semana"
			value="<?=$_1_u_amostra_semana?>"></div>
</div>
<div class="row rowDin hidden">
		<div class="col-md-1" id="lb_notafiscal">Nota Fiscal:</div>
		<div class="col-md-3" id="col_notafiscal" colspan="5"><input autocomplete="off"
			type="text" name="_1_<?=$_acao?>_amostra_notafiscal" id="notafiscal" title="Nota Fiscal"
			value="<?=$_1_u_amostra_notafiscal?>"></div>

		<div class="col-md-1" id="lb_vencimento">Vencimento:</div>
		<div class="col-md-3" id="col_vencimento" colspan="5"><input autocomplete="off"
			type="text" name="_1_<?=$_acao?>_amostra_vencimento" id="vencimento" title="Vencimento"
			value="<?=$_1_u_amostra_vencimento?>"></div>

		<div class="col-md-1" id="lb_fabricante">Fabricante</div>
		<div class="col-md-3" id="col_fabricante" colspan="5"><input autocomplete="off"
			type="text" name="_1_<?=$_acao?>_amostra_fabricante" id="fabricante" title="Fabricante"
			value="<?=$_1_u_amostra_fabricante?>"></div>
</div>
<div class="row rowDin hidden">
		<div class="col-md-1" id="lb_sexadores">Sexadores:</div>
		<div class="col-md-3" id="col_sexadores" colspan="5"><input autocomplete="off" type="text"
			name="_1_<?=$_acao?>_amostra_sexadores" id="sexadores" title="Sexadores"
			value="<?=$_1_u_amostra_sexadores?>"></div>

		<div class="col-md-1" id="lb_localexp">Local Espec&iacute;fico:</div>
		<div class="col-md-3" colspan="5" id="col_localexp"><input autocomplete="off" type="text"
			name="_1_<?=$_acao?>_amostra_localexp" id="localexp" title="Local Específico"
			value="<?=$_1_u_amostra_localexp?>"></div>

</div>
<div class="row rowDin hidden">
		<div class="col-md-2" id="lb_localcoleta">Local Coleta:</div>
		<div class="col-md-10" colspan="5" id="col_localcoleta"><input autocomplete="off"
			type="text" name="_1_<?=$_acao?>_amostra_localcoleta" id="localcoleta" title="Local Coleta"
			value="<?=$_1_u_amostra_localcoleta?>"></div>
</div>
<div class="row rowDin hidden">
		<div class="col-md-2" id="lb_responsavel">Respons&aacute;vel Coleta:</div>
		<div class="col-md-10" colspan="5" id="col_responsavel"><input autocomplete="off"
			type="text" name="_1_<?=$_acao?>_amostra_responsavel" id="responsavel" title="Responsável Coleta"
			value="<?=$_1_u_amostra_responsavel?>"></div>
</div>
<div class="row rowDin hidden">
		<div class="col-md-2" id="lb_responsavelof">Respons&aacute;vel Oficial:</div>
		<div class="col-md-4" colspan="5" id="col_responsavelof"><input autocomplete="off"
			type="text" name="_1_<?=$_acao?>_amostra_responsavelof" id="responsavelof" title="Responsável Oficial"
			value="<?=$_1_u_amostra_responsavelof?>">
		</div>
		<div class="col-md-1" id="lb_responsavelofcrmv">Crmv:</div>
		<div class="col-md-2" id="col_responsavelofcrmv"><input autocomplete="off"
			type="text" name="_1_<?=$_acao?>_amostra_responsavelofcrmv" id="responsavelofcrmv" title="CRMV"
			value="<?=$_1_u_amostra_responsavelofcrmv?>"></div>
		<div class="col-md-1" id="lb_responsaveloftel">Tel:</div>
		<div class="col-md-2" id="col_responsaveloftel"><input autocomplete="off"
			type="text" name="_1_<?=$_acao?>_amostra_responsaveloftel" id="responsaveloftel" title="Telefone"
			value="<?=$_1_u_amostra_responsaveloftel?>"></div>
</div>
<div class="row rowDin hidden">
		<div class="col-md-2" id="lb_lacre">Lacre:</div>
		<div class="col-md-4" id="col_lacre"><input placeholder="Não Informado" autocomplete="off" type="text"
			name="_1_<?=$_acao?>_amostra_lacre" id="lacre" title="Lacre"
			value="<?=$_1_u_amostra_lacre?>" ></div>

		<div class="col-md-1" id="lb_tc">TC:</div>
		<div class="col-md-4" id="col_tc"><input placeholder="Não Informado" autocomplete="off" type="text"
			name="_1_<?=$_acao?>_amostra_tc" id="tc" title="T.C."
			value="<?=$_1_u_amostra_tc?>" ></div>

</div>
<div class="row rowDin hidden">
		<div class="col-md-2" id="lb_observacao">Observa&ccedil;&atilde;o:</div>
		<div class="col-md-10" id="col_observacao" colspan="5"><textarea
			name="_1_<?=$_acao?>_amostra_observacao" id="observacao" title="Observação" cols="61"
			rows="3"><?=$_1_u_amostra_observacao?></textarea></div>
</div>
<div class="row rowDin hidden">
		<div class="col-md-2" id="lb_observacaointerna">Observa&ccedil;&atilde;o
		interna:<br>
		(N&atilde;o ser&aacute; impressa)</div>
		<div class="col-md-10" id="col_observacaointerna" colspan="5"><textarea
			name="_1_<?=$_acao?>_amostra_observacaointerna" id="observacaointerna" title="Observação Interna"
			cols="61" rows="3"><?=$_1_u_amostra_observacaointerna?></textarea></div>
</div>

	</div><!-- panel body -->
</div>
</div>
<style>
.list-group-item .list-group-item-text{
	display: none;
}
</style>
<div class="col-md-4">
<?
   

?>  
	<div id="observacaore"></div>
	<i class=""></i>
	<div class="panel panel-default">
		<div class="panel-heading">Testes <label>(<span id="testesquant">0</span> itens)</label>
		    <a title="Imprimir Teste(s) Amostra." class="fa fa-print pull-right fa-lg cinza pointer hoverazul" onclick="janelamodal('report/amostra.php?acao=i&idamostra=<?=$_1_u_amostra_idamostra?>')"></a>
		</div>
		<div class="panel-body">
			<span id="infoSecretaria" class="cinza"></span>
			<table id="tbTestes" class="table table-striped planilha">
			<thead>
			<tr>
				<th></th>
				<?if($_1_u_amostra_idunidade==1){?>
                                <th>R$</th>
				<?}?>
				<th>Teste</th>
				<th>Qtd.</th>
                                <?if($unidadepadrao==1){?>
				<th>Oficial</th>
                                <?}else{?>
                                <th>Cobrar</th>
                                <?}?>
				<th></th>
				<th></th>                                
			</tr>
			</thead>
			<tbody>
				<?listaTestes()?>
			</tbody>
			</table>
			<table class="hidden">
			<tr id="modeloNovoTeste">
				<td>
					<i class="fa fa-print pointer cinzaclaro" style="color:silver;" title="Alterar Lote de Impressão" id="cbimp#irow" onclick="alteraLoteEtiqueta(#irow)"></i>
				</td>
				<td style="width:50%">
					<input type="hidden" name="#nameidresultado">
					<input type="hidden" name="#nameloteetiqueta" value="">
					<input type="hidden" name="#nameord" value="">
					<input type="text" name="#nameidtipoteste" class="idprodserv" cbvalue placeholder="Informe o Teste" vnulo>
				</td>
				<td><input type="text" name="#namequantidade" style="width:30px" placeholder="Qtd." vnulo vnumero></td>
				<td>
					<select name="#nameidsecretaria" style="font-size:10px;" placeholder="Secretaria">
						<option value=""></option>
			<?
					$strs="select pp.idpessoa,pp.nome
					from pessoa p,pessoa pp
					where pp.idpessoa = p.idsecretaria
						and p.idpessoa =".$_1_u_amostra_idpessoa;

					fillselect($strs,$r["idsecretaria"]);
			?>
					</select>
				</td>
                                <td>
 <?
 if(!empty($_1_u_amostra_idpessoa)){
                $sqlpf="select pf.pedidocp 
                        from pessoa p join
                                preferencia pf on (pf.idpreferencia=p.idpreferencia)
                         where p.idpessoa =".$_1_u_amostra_idpessoa."  
                        and pedidocp='Y'";
                $respf=d::b()->query($sqlpf) or die("Erro ao buscar preferencia de numero de compra sql=".$sqlpf);
                $qtdpf =mysqli_num_rows($respf);
                if($qtdpf>0){
                    $typepf='text';
                    $disable='';
                }else{
                    $typepf='hidden';
                    $typepf='hidden';
                    $disable="disabled='disabled'";
                }
 }else{
     $typepf='hidden';
     $disable="disabled='disabled'";
 }
 ?>                                   
                                    <input class="namenpedido size6" placeholder="N. Pedido" type="<?=$typepf?>" name="#namenpedido" value="" <?=$disable?> vnulo>
                                
                                </td>
				<td><i class="fa fa-arrows cinzaclaro hover move"></i></td>

			</tr>
			</table>
			<div>
				<i class="fa fa-plus-circle fa-2x verde btn-lg pointer" onclick="novoTeste()" alt="Inserir novo teste"></i>
<?
if(!empty($_1_u_amostra_idamostra)){
?>
				<i class="fa fa-print fa-2x cinzaclaro btn-lg pointer" onclick="imprimeTestes(<?=$_1_u_amostra_idamostra?>);" alt="Imprimir Etiquetas"></i>
<?
}
?>
				<i class="fa fa-trash fa-2x cinzaclaro hoververmelho btn-lg pointer" id="excluirTeste" alt="Arraste o teste até aqui para excluir"></i>
			</div>
		</div>
	</div>
<?
if($unidadepadrao==9 and !empty($_1_u_amostra_idamostra)){// Diagnóstico Autógenas 
   // print_r($arrTRAAssociado);
?>
	<div class="panel panel-default">
		<div class="panel-heading">
			<table style="width: 100%; min-width:100%;">
				<tr>
					<td >
						<label>TRA:</label>
					</td>
					<td >
						<input type="hidden" name="_2_u_amostra_idamostra" value="<?=$arrTRAAssociado["idamostra"]?>">
						<?IF($arrTRAAssociado["statustra"]!='ASSINADO'){?>
						<select name="_2_u_amostra_statustra">
						<?fillselect(array("ABERTO"=>"Aberto","ENVIADO"=>"Enviado","DEVOLVIDO"=>"Devolvido","ASSINAR"=>"Assinar"),$arrTRAAssociado["statustra"])?>
						</select>
						<?}else{?>
						<input type="hidden" name="_2_u_amostra_statustra" value="<?=$arrTRAAssociado["statustra"];?>">
						<label class="alert-warning"><?=$arrTRAAssociado["statustra"]?></label><?}?>
					</td>
					<td>
						<i title="Imprimir TEA/TRA" class="fa fa-print pull-right fa-lg cinza hoverazul" onclick="janelamodal('report/tra.php?idamostra=<?=$_1_u_amostra_idamostra?>&unidadepadrao=9')"></i>
					</td>

					<td>
						<i title="Resumo Diagnóstico" class="fa fa-file pull-right  cinza hoverazul" onclick="janelamodal('report/traresultados.php?idamostratra=<?=$_1_u_amostra_idamostra?>&unidadepadrao=9')"></i>
					</td>
					<td>
						<i title="Imprimir LDA" class="fa fa-print pull-right fa-lg cinza hoverazul" onclick="janelamodal('report/emissaoresultado.php?_vids=<?=$_vids?>')"></i>
					</td>
				</tr>
				<tr>
					<td>
						email:
					</td>
					<td colspan="4">
						<input style="font-size: 10px; height: 20px; width: 180px; " type="text" name="_2_u_amostra_email" value="<?=$arrTRAAssociado["email"]?>">
					<?
						if($arrTRAAssociado["emailtea"]=="O"){
							$cbotao="verde";
						}elseif($arrTRAAssociado["emailtea"]=="E"){
							$cbotao="vermelho";
						}else{
							$cbotao="cinzaclaro";
						}
					?>
						<i class="fa fa-circle <?=$cbotao?> hoververde btn-lg pointer" onclick="statusenviaemailtea(<?=$arrTRAAssociado["idamostra"]?>)" title="<?=$arrTRAAssociado["logemail"]?>"></i>
					</td>
				</tr>				
			</table>				
			
		</div>

		<div class="panel-body">
			<table style="width: 100%;">
			<tr>

				<td style="width:100%;min-width:100%;" class="inlineblocktop">
<?
			    if(!empty($_1_u_amostra_idamostra)){
					
					$sql="select a.idamostra
						from amostra a 
						where a.idamostratra = ".$_1_u_amostra_idamostra;
					$res= d::b()->query($sql) or die("Erro ao buscar amostras vinculadas ao TRA: " . mysql_error() . "<p>SQL:".$sql);
					$qtd= mysqli_num_rows($res);
					if($qtd>0){
						buscaamostras();
					}
					
					desenhaTRA();
			    }
				
?>

				</td>
			</tr>
			<tr>
				<td style="width:100%;height:100%;">
					<div class="cbupload" title="Clique ou arraste arquivos para cá" style="width:100%;height:100%;">
						<i class="fa fa-cloud-upload fonte18"></i>
					</div>
				</td>
			</tr>
<?
			if(!empty($_1_u_amostra_idamostra)){
?>			
				<tr>
<?
				$sqlarq = "select a.*, dmahms(criadoem) as datacriacao 
							from arquivo a 
							where 
								a.tipoobjeto = 'amostra' 
								and a.idobjeto = ".$_1_u_amostra_idamostra." 
								and tipoarquivo = 'ANEXO' 
							order by idarquivo asc";

				//echo $sqlarq."<br>";
				$res = d::b()->query($sqlarq) or die("Erro ao pesquisar arquivos:".mysqli_error());
				$numarq= mysqli_num_rows($res);

				if($numarq>0  ){
?>

	<td colspan="4">
		<ul class="listaitens">
			<li class="cab">Arquivos Anexos (<?=$numarq?>)</li>
<?		while ($row = mysqli_fetch_array($res)) {?>
			<li><a title="Abrir arquivo" target="_blank"  href="../upload/<?=$row["nome"]?>"><?=$row["nome"]?></a></li>
<?			}
?>
		</ul>
	</td>

<?
	}
?>	

			</tr>
<?
			}//if(!empty($_1_u_amostra_idamostra)){
?>
			</table>
		</div>

	</div>

<?}?>

	<div class="panel panel-default" id="panelEtiquetas">
		<div class="panel-heading">Log de Impressão de Etiquetas</div>
		<table class="table">
		<tr class="header">
			<td>Teste</td>
			<td>Criado por</td>
			<td>Criado em</td>
		</tr>
<?
		logImpressao();
?>
		</table>
	</div>

</div>
<?
if(!empty($_1_u_amostra_idamostra)){
    $sql = "select p.idpessoa
                ,p.nome 
                ,CASE c.status
                    WHEN  'ATIVO' THEN dma(c.alteradoem)
                     WHEN  'ASSINADO' THEN dma(c.alteradoem)
                    ELSE ''
                END as dataassinatura 
                ,CASE  c.status
                    WHEN 'ATIVO' THEN 'ASSINADO'
					WHEN 'ASSINADO' THEN 'ASSINADO'
                    ELSE 'PENDENTE'
                END as status
            from carrimbo c ,pessoa p 
            where c.idpessoa = p.idpessoa
            and c.status IN ('ATIVO','PENDENTE','ASSINADO')
            and c.tipoobjeto in ('amostra','amostraaves','amostraautogenas','amostraprod')
            and c.idobjeto =".$_1_u_amostra_idamostra."  order by nome";

    $res = d::b()->query($sql) or die("A Consulta de assinaturas falhou :".mysqli_error(d::b())."<br>Sql:".$sql); 
    $existe = mysqli_num_rows($res);
    if($existe > 0){
?>
<div class="row">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading">Assinaturas</div>
        <div class="panel-body">
            <table class="planilha grade compacto">
               <tr>
                    <th >Funcionários</th>
                    <th >Data Assinatura</th>
                    <th >Status</th>	
                </tr>			
<?			
        while($row = mysqli_fetch_assoc($res)){			
?>	
                <tr class="res">
                    <td nowrap><?=$row["nome"]?></td>
                    <td nowrap><?=$row["dataassinatura"]?></td>
                    <td nowrap><?=$row["status"]?></td>
                </tr>				
<?							
        }
?>	
            </table>
        </div>
    </div>
    </div>
</div>
<?}}?>

<div class="row ">
<div class="col-md-12 container-fluid">
     <?$tabaud = "amostra";?>
    <div class="panel panel-default">		
        <div class="panel-body">
            <div class="row col-md-12">		
                <div class="col-md-1">Criado Por:</div>     
                <div class="col-md-5"><?=${"_1_u_".$tabaud."_criadopor"}?></div>
                <div class="col-md-1">Criado Em:</div>     
                <div class="col-md-5"><?=${"_1_u_".$tabaud."_criadoem"}?></div>   
            </div>
            <div class="row col-md-12">            
                <div class="col-md-1">Alterado Por:</div>     
                <div class="col-md-5"><?=${"_1_u_".$tabaud."_alteradopor"}?></div>
                <div class="col-md-1">Alterado Em:</div>     
                <div class="col-md-5"><?=${"_1_u_".$tabaud."_alteradoem"}?></div>       
            </div>
        </div>
    </div>
</div>
</div>

<script>

jsonInputsTipoamostra = <?=jsonInputsTipoamostra()?>;
jsonTipoSubtipo = <?=jsonTipoSubtipo()?>;
jsonClientes = <?=jsonClientes()?>;
jsonDetClientes = <?=$jsonDetClientes?>;
jsonServicos = <?=$jsonServicos?>;
jsonEspecieFinalidade = <?=$jsonEspecieFinalidade?>;
jsonTelaamostraconf = <?=$jsonTelaamostraconf?>;

dtUltimaAmostra = '<?=$dtUltimaAmostra?>';
booAtualizaNucleo = false;
unidadePadrao = <?=$unidadepadrao?>;

/*
 * Mostra ou esconde os inputs conforme configuração
 */
function confInputs(inv){

	if($(":input[name=_1_"+CB.acao+"_amostra_idsubtipoamostra]").val()==""){
		//return false;
	}

	vIdSubtipoAmostra = $("[name=_1_"+CB.acao+"_amostra_idsubtipoamostra]").cbval()||$("[name=_1_"+CB.acao+"_amostra_idsubtipoamostra]").attr("value");
	// vIdTipoAmostra = $("[name=_1_"+CB.acao+"_amostra_idtipoamostra]").val()||$("[name=_1_"+CB.acao+"_amostra_idtipoamostra]").attr("value");

	if(!vIdSubtipoAmostra ){
		console.warn("tipoamostra não informado");
		return false;
	}

	//Nenhuma coluna configurada para o Tipo de Amostra selecionado. Isto gera erro ao tentar recuperar configuração
	if(jsonInputsTipoamostra["TELA"]===undefined||jsonInputsTipoamostra["TELA"][vIdSubtipoAmostra]===undefined){
		alertAtencao("Nenhum campo configurado para o tipo/subtipo de amostra selecionado!\nUtilize a opção <i class='fa fa-eye'></i>");
		if(!jsonInputsTipoamostra["TELA"][vIdSubtipoAmostra])return false;
	}

	$(".rowDin [id*=lb_], .rowDin [id*=col_]").hide().each(function(k,v){
		oTd = $(v);
		oTdName = oTd.attr("name");
		oTdId = oTd.attr("id");
		sColuna=(oTdId&&oTdId.indexOf("col_")>=0)?oTdId.replace("col_",""):"";

		if(sColuna!="" && sColuna!=undefined  && vIdSubtipoAmostra!=""){
			//Recuperar o input relacionado
			oInput = oTd.children("[name=_1_"+CB.acao+"_amostra_"+sColuna+"]");

			//Se o input possuir valor, mostrar, mesmo que não esteja nas configurações, pois pode ter sido configurado no passado, e que seja diferente de tipoaves
			if(oInput.val()!=undefined && oInput.val()!="" && oInput.attr("name").indexOf("_tipoaves_")<0){
				$(".rowDin div[id=lb_"+sColuna+"], .rowDin div[id=col_"+sColuna+"]").show();
			}

			//Se a coluna estiver configurada, mostrar
			if($.inArray(sColuna, jsonInputsTipoamostra["TELA"][vIdSubtipoAmostra])>=0){
				$(".rowDin div[id=lb_"+sColuna+"], .rowDin div[id=col_"+sColuna+"]").show();
			}
		}
	});

	$(".rowDin").removeClass("hidden");
	if (inv == 1){
		$("#limparnucleo").addClass("hidden");
		$("#editarnucleo").addClass("hidden");
	}

}

function mostraDetalhesCliente(){
	vIdPessoa = $(":input[name=_1_"+CB.acao+"_amostra_idpessoa]").cbval()||"";

	if(vIdPessoa!=""){
		if(jsonDetClientes[vIdPessoa].observacaore && jsonDetClientes[vIdPessoa].observacaore!=""){
			$("#observacaore").html("<div class='alert alert-warning alert-danger' role='alert'> \
				"+jsonDetClientes[vIdPessoa].observacaore.replace(/\r?\n/g,"<br>")+"</div>").show();
			$("#lbcnpj").html(jsonDetClientes[vIdPessoa].cpfcnpj);

		}else{
			$("#observacaore").hide();
		}
	}
}

function mostracamporesadd(){
    debugger;
    vIdPessoa = $(":input[name=_1_"+CB.acao+"_amostra_idpessoa]").cbval()||"";
    if(vIdPessoa!=""){
        if(jsonDetClientes[vIdPessoa].pedidocp && jsonDetClientes[vIdPessoa].pedidocp==="Y"){
                $(".namenpedido").attr('type', 'text');
                 $(".namenpedido").removeAttr("disabled");

        }else{
                $(".namenpedido").attr('type', 'hidden');
                 $(".namenpedido").attr("disabled","disabled");
        }
    }
}

/*
 * Novo teste [ctrl]+[+]
 */
$(document).keydown(function(event) {

    if (!((event.ctrlKey || event.altKey) && event.keyCode == 80)) return true;

    if(CB.acao=="i"){
		alert("Salve a amostra primeiro");
	}

	if(!teclaLiberada(e)) return;//Evitar repetição do comando abaixo

	idamostra = $("[name=_1_u_amostra_idamostra]").val();
	imprimeTestes(idamostra);

    return false;

});

function novaamostra(vmodulo){
    //Recuperar o formulário em modo de update (sem o readonly) para posteriormente transformar os inputs em uma nova amostra
	CB.loadUrl({
		urldestino: CB.urlDestino+"?_modulo=amostra&_acao=u&_pagereadonly=N&duplicaramostra=Y&idamostra="+$("[name=_1_u_amostra_idamostra]").attr("value")
		,render: function(data){
			//Ao invés de mostrar os objetos no formulário, tratar antes:
			duplicarAmostra(data,vmodulo);
		}
	})
}

/*
 * Duplicar amostra [ctrl]+[d]
 */
$(document).keydown(function(event) {

    if (!((event.ctrlKey || event.altKey) && event.keyCode == 68)) return true;

	if(!teclaLiberada(event)) return;//Evitar repetição do comando abaixo

	//Recuperar o formulário em modo de update (sem o readonly) para posteriormente transformar os inputs em uma nova amostra
	CB.loadUrl({
		urldestino: CB.urlDestino+"?_modulo=amostra&_acao=u&_pagereadonly=N&duplicaramostra=Y&idamostra="+$("[name=_1_u_amostra_idamostra]").attr("value")
		,render: function(data){
			//Ao invés de mostrar os objetos no formulário, tratar antes:
			duplicarAmostra(data,CB.modulo);
		}
	})

    return false;
});

function duplicarAmostra(data,vmodulo){

	//Inicializa um objeto jquery com todo o conteúdo que retornou do servidor
	$data = $(data);

	//Verifica se o formulário está visível, para evitar o trigger em outras condições
    if(CB.acao=="u" && CB.oModuloForm.is(":visible")){

		//Ajusta os parâmetros do carbon para simular um clique no botão de "novo"
		lSearch="_modulo="+vmodulo+"&_acao=i";
		CB.locationSearch=lSearch;
		window.history.pushState(null, window.document.title, "?"+lSearch);

		//Recupera todos os campos de input
		$data.find(":input").each(function(k,obj){
			$obj = $(obj);
			$oNome = $obj.attr("name");
			//console.log("name:"+$oNome);
			//Inputs de Amostra
			if(($oNome) && $oNome.indexOf("_1_u_amostra")==0){
				$obj.attr("name",$oNome.replace("_1_u_amostra","_1_i_amostra"));
				if($oNome.match(/^.*idamostra$/)){//Reset
					$obj.val("");
				}else if($oNome.match(/^.*exercicio$/)){//Reset
					$obj.val("");
				}else if($oNome.match(/^.*dataamostra$/)){//Data: hoje
					$obj.val(dtUltimaAmostra);
				}else if($oNome.match(/^.*idunidade/)){//Data: hoje
				    if(vmodulo=='amostraautogenas'){
					$obj.val(6);
				    }else if(vmodulo=='amostratra'){
					$obj.val(9);
				    }
				}
			//inputs de resultado
			}else if(($oNome) && $oNome.indexOf("_u_resultado")>0){
				//Quebra o nome capturando 2 grupos: numero da linha e nome do campo, excluindo-se o campo idresultado
				if($oNome.match(/^.*idresultado$/)){//Se é o campo idresultado, resetar
					//$obj.attr("name","");
					$obj.val("");
				}
				//Renomeia o input para o padrão que será aproveitado no saveposchange
				grCap = $oNome.match(/_(\d+)_u_resultado_(.+)/);
				$obj.attr("name","_"+grCap[1]+"#"+grCap[2]);

			}
			//Verificar processo de redefinir input names:
			//console.log($obj.attr("name")+"-"+$obj.cbval()+"-"+$obj.val());
		});

		//Permite que os testes sejam deletados
		$data.find("#tbTestes tbody tr").addClass("dragExcluir").removeAttr("idresultado").find("i.move.hidden").removeClass("hidden");
		
		//Altera o Cabeçalho e estilo
		$data.find("#cabRegistro").html("Nova amostra");
		CB.acao="i";
		$("body").addClass("novo");
		CB.oModuloForm.html("").html($data);
		CB.removeBotoesUsuario();
		$("#panelEtiquetas").remove();
	}
}

//Autocomplete de Clientes
$(":input[name=_1_"+CB.acao+"_amostra_idpessoa]").autocomplete({
	source: jsonClientes,
	delay: 0,
	select: function(event, ui){
		mostraDetalhesCliente();
		preencheDropNucleos(ui.item.value);
		verificaTestesOficiais();
               mostracamporesadd();
		//autocompleteResponsavel();
		//autocompleteResponsavelOf();
	},
	create: function( event, ui ) {
		mostraDetalhesCliente();
               mostracamporesadd();
	}
});

//Autocomplete de Tipo e Subtipo de amostra
$(":input[name=_1_"+CB.acao+"_amostra_idsubtipoamostra]").autocomplete({
	source: jsonTipoSubtipo[unidadePadrao],
	delay: 0,
	select: function(event, ui){
		
		confInputs();
		popoverEditarCamposVisiveis();
	}
	,create: function( event, ui ) {
		confInputs();
	}
});


//Autocomplete de Servicos (testes)
function criaAutocompletesTestes(){
	$("#tbTestes .idprodserv").autocomplete({
		source: jsonServicos,
		delay: 0
	});
	//Permite ordenação dos elementos
	$("#tbTestes tbody").sortable({
		update: function(event, objUi){
			ordenaTestes();
		}
	});
}

function verificaTestesOficiais(){
	$.each($("#tbTestes").find(":input[name*=idsecretaria]"), function(i,o){
		$o=$(o);
		if($o.val() && $o.val().length>0){
			alertAtencao("Teste marcado como Oficial<br>Salve a amostra para alterar.");
			$o.val("");
		}
	});
}

amostraOficial=<?=empty($_1_u_amostra_idsecretaria)?"false":"true"?>;
function configuraOficial(){
	$aTOficial = $("#aToggleOficial");
	$idsec = $(":input[name=_1_"+CB.acao+"_amostra_idsecretaria]");
	$idpessoa = $(":input[name=_1_"+CB.acao+"_amostra_idpessoa]");
	$infoSecretaria = $("#infoSecretaria");

	if(amostraOficial){
		$aTOficial.removeClass("hoverlaranja")
				.addClass("laranja")
				.attr("title","Amostra Oficial: "+jsonDetClientes[$idpessoa.cbval()].secretaria);
		$infoSecretaria.html("Secretaria: "+jsonDetClientes[$idpessoa.cbval()].secretaria)
				.show();
	}else{
		$aTOficial.removeClass("laranja")
				.addClass("hoverlaranja")
				.attr("title","Marcar Amostra como Oficial");
		$infoSecretaria.html("")
				.hide();
	}
}

//Configura a amostra como oficial, para marcar testes oficiais ou não
function toggleOficial(){
	$aTOficial = $("#aToggleOficial");
	$idsec = $(":input[name=_1_"+CB.acao+"_amostra_idsecretaria]");
	$idpessoa = $(":input[name=_1_"+CB.acao+"_amostra_idpessoa]");

	//Caso o cliente não possua informação de secretaria ou já estiver marcado, retirar o valor do campo idsecretaria
	if(!jsonDetClientes[$idpessoa.cbval()] || jsonDetClientes[$idpessoa.cbval()].idsecretaria==null || $idsec.val()!=""){
		$idsec.val("");
		amostraOficial=false;
		configuraOficial();
	}else{
		$idsec.val(jsonDetClientes[$idpessoa.cbval()].idsecretaria);
		amostraOficial=true;
		configuraOficial();
	}
	console.log("AmostraOficial: "+$idsec.val());
}


//Autocomplete de Espécie/Finalidade
$(":input[name=_1_"+CB.acao+"_amostra_idespeciefinalidade]").autocomplete({
	source: jQuery.map(jsonEspecieFinalidade, function(item, id) {
				return {"label": item.especie+" - "+item.tipoespecie + "/" + item.finalidade, value:id, "especie":item.especie, "tipoespecie":item.tipoespecie, "finalidade":item.finalidade}
			})
	,create: function(){
		$(this).data('ui-autocomplete')._renderItem = function (ul, item) {
			
			lbItem = "<span class=cinzaclaro>"+item.especie+" - </span>"+item.tipoespecie + " / " + item.finalidade;
			
			return $('<li>')
				.append('<a>' + lbItem + '</a>')
				.appendTo(ul);
		};
	}
        ,select: function(event,ui){
            preencherotulo(ui.item.value);
        }
	,delay: 0
});

function preencherotulo(inidespecie){
//alert(jsonEspecieFinalidade[inidespecie]['rotulo']);
$("#rotulonucleotipo").val(jsonEspecieFinalidade[inidespecie]['rotulo']);

}
/*
//Autocomplete de Responsavel
function autocompleteResponsavel(){
	$(":input[name=_1_"+CB.acao+"_amostra_responsavel]").autocomplete({
		source: "ajax/amostraHistoricoAc.php?idpessoa="+$("[name=_1_"+CB.acao+"_amostra_idpessoa]").cbval()+"&coluna=responsavel"
		,delay: 0
	});
}

function autocompleteResponsavelOf(){
	$(":input[name=_1_"+CB.acao+"_amostra_responsavelof]").autocomplete({
		source: "ajax/amostraHistoricoAc.php?idpessoa="+$("[name=_1_"+CB.acao+"_amostra_idpessoa]").cbval()+"&coluna=responsavelof"
		,delay: 0
	});
}
*/

function ordenaTestes(){
	$.each($("#tbTestes tbody").find("tr"), function(i,otr){
		//Recupera objetos de update e de insert
		$(this).find(":input[name*=resultado_ord],:input[name*=ord]").val(i);
	})
}

$("#excluirTeste").droppable({
	accept: ".dragExcluir"
	,drop: function( event, ui ) {
		//verifica se existe o idresultado em mode de update. caso positivo, alternar para excluir
		$idres = $(ui.draggable).attr("idresultado");
		if(parseInt($idres) && CB.acao!=="i"){
			if(confirm("Deseja realmente excluir o teste selecionado?")){
				ui.draggable.remove();
				CB.post({
					"objetos":"_x_d_resultado_idresultado="+$idres
					//,parcial:true
				});
			}
		}else{
			if($(ui.draggable).find(":input[name*=#idresultado]").length==1){//Modo de inclusão
				ui.draggable.remove();
			}
		}
		setTimeout(function(){ atualizaQuantTestes(); }, 100);//Deve ser colocado timeout para postergar atualização de contagem de testes
	}
});

/*
 * Criar novo objeto html de tipo de teste
 */
function novoTeste(objTeste){
	status="<?=$_1_u_amostra_status?>";
	if(CB.acao=="u" && status && status!=="ABERTO"){
		alertAtencao("Clique em Editar Amostra antes de inserir um novo Teste!");
		return false;
	}

	oTbTestes = $("#tbTestes tbody");
	iNovoTeste = (oTbTestes.find("input.idprodserv").length + 11);
	htmlTrModelo = $("#modeloNovoTeste").html();


	htmlTrModelo = htmlTrModelo.replace("#nameidresultado", "_"+iNovoTeste+"#idresultado");
	htmlTrModelo = htmlTrModelo.replace("#nameloteetiqueta", "_"+iNovoTeste+"#loteetiqueta");
	htmlTrModelo = htmlTrModelo.replace("#nameord", "_"+iNovoTeste+"#ord");
	htmlTrModelo = htmlTrModelo.replace("#nameidtipoteste", "_"+iNovoTeste+"#idtipoteste");
	htmlTrModelo = htmlTrModelo.replace("#namenpedido", "_"+iNovoTeste+"#npedido");
        htmlTrModelo = htmlTrModelo.replace("#namequantidade", "_"+iNovoTeste+"#quantidade");
	htmlTrModelo = htmlTrModelo.replace("#nameidsecretaria", "_"+iNovoTeste+"#idsecretaria");
	htmlTrModelo = htmlTrModelo.replace(/#irow/g, iNovoTeste);

	novoTr = "<tr class='dragExcluir'>"+htmlTrModelo+"</tr>";
	oTbTestes.append(novoTr);
	criaAutocompletesTestes();
	atualizaQuantTestes();
}

/*
 * Cria lotes para envio de etiquetas para a impressora térmica
 */
function alteraLoteEtiqueta(inRow){
	$oLote = $("[name=_"+inRow+"_u_resultado_loteetiqueta]");
	if($oLote.length===0) $oLote = $("[name=_"+inRow+"#loteetiqueta]");

	$iLote = $oLote.val()||0;
	$iLote++;
	if(arrCoresLoteEtiquetas[$iLote]){
		$("#cbimp"+inRow).css("color",arrCoresLoteEtiquetas[$iLote]).attr("title","Lote "+$iLote);
		console.log(arrCoresLoteEtiquetas[$iLote]);
	}else{
		$iLote=0;
		$("#cbimp"+inRow).css("color","").attr("title","Alterar Lote de Impressão");
	}
	$oLote.val($iLote);
	CB.lotesImpressaoAlterado=true;
}

/*
 * Recuperar os núcleos do client
 */
function preencheDropNucleos(){

	vIdPessoa = $(":input[name=_1_"+CB.acao+"_amostra_idpessoa]").cbval();
	//vNucleoSelecionado = $(":input[name=_1_"+CB.acao+"_amostra_idnucleo]").val();
	if(vIdPessoa){

		$.ajax({
			type: "get",
			url : "ajax/nucleosCliente.php?idpessoa="+vIdPessoa+"&idunidade="+unidadePadrao,
			success: function(data){
				//Transforma a string json em objeto
				jsonNuc =jsonStr2Object(data);
				if(jsonNuc){

					$oIdnucleo = $("[name=_1_"+CB.acao+"_amostra_idnucleo]");

					//Nova versão de núcleo: autocomplete
					jsonAc = jQuery.map( jsonNuc, function(n, id) {
						return {"label": n.nucleo, value:id ,"lote":n.lote}
					});

					$oIdnucleo.autocomplete({
						source: jsonAc
						,delay: 0
						,select: function(){
							console.log($(this).cbval());
							preencheInputsNucleoAmostra($(this).cbval());
						}
						,create: function(){
							$(this).data('ui-autocomplete')._renderItem = function (ul, item) {
								vnucleo = "<span class='cinzaclaro'>Núcleo: </span>" + item.label;
								vnucleo = (item.lote)?vnucleo+" <span class=cinzaclaro> - Lote: </span>" + item.lote:vnucleo;
								return $('<li>')
									.append('<a>' + vnucleo + '</a>')
									.appendTo(ul);
							};
						}
						,noMatch: function(objAc){
							console.log("Executei callback");
							CB.post({
								objetos: "_x_i_nucleo_idunidade="+unidadePadrao+"&_x_i_nucleo_situacao=ATIVO&_x_i_nucleo_nucleo="+objAc.term+"&_x_i_nucleo_idpessoa="+$(":input[name=_1_"+CB.acao+"_amostra_idpessoa]").cbval()
								,parcial:true
								,refresh: false
								,msgSalvo: "Núcleo criado"
								,posPost: function(data, textStatus, jqXHR){
									//Atualiza source json
									$oIdnucleo.data('uiAutocomplete').options.source.push({
										label: $oIdnucleo.val()
										,value: CB.lastInsertId
									});
									//Atualiza o objeto DATA associado ao input
									$oIdnucleo.data("nucleos")[CB.lastInsertId]={"nucleo":$oIdnucleo.val()};
									//Mostra a nova opção
									$oIdnucleo.autocomplete( "search", $oIdnucleo.val());
									//Informa visualmente o usuário para que complete as informações do núcleo
									getInputsNucleoAmostra().addClass("aguardandoCbpost");
									//Mostra campos adicionais do Núcleo
									$("#col_tipoaves, #col_finalidade").show()
									//Possibilita atualização do núcleo no prePost
									booAtualizaNucleo=true;
								}
							});
						}
					})
					//Uma propriedade com o json é adicionada ao objeto para tornar possível a consulta posterior
					.data("nucleos",jsonNuc);

					//if($valIdnucleo) $oIdnucleo.val($valIdnucleo);
					//if(vNucleoSelecionado) $oIdnucleo.val(vNucleoSelecionado);//Caso de "duplicar amostra" devolver valor à drop

				}else{
					alertErro("Javascript: preencheDropNucleos(): Erro ao recuperar json de nucleos.\nVerificar Console de erros javascript.");
				}
			}
		});

	}else{
		console.warn("js: preencheDropNucleos: Erro: idIdpessoa não informado;")
	}
}

/*
 * Recuperar a coleção de inputs da amostra relacionados à informções de Núcleo
 */
function getInputsNucleoAmostra(){
	return $("[name=_1_"+CB.acao+"_amostra_nucleoamostra] \
		,[name=_1_"+CB.acao+"_amostra_lote] \
		,[name=_1_"+CB.acao+"_amostra_granja] \
		,[name=_1_"+CB.acao+"_amostra_unidadeepidemiologica] \
		,[name=_1_"+CB.acao+"_amostra_idespeciefinalidade] \
		,[name=_1_"+CB.acao+"_amostra_regoficial] \
		,[name=_1_"+CB.acao+"_amostra_tipoaves] \
		,[name=_1_"+CB.acao+"_amostra_tipoidade] \
		,[name=_1_"+CB.acao+"_amostra_idade]\
		,#finalidade");
}

/*
 * Calcular data de alojamento a partir da quantidade de Dias ou Semanas
 */
function getDataAlojamento(){
	vQuant = $("[name=_1_i_amostra_idade]").val();
	diasAlojamento = ($("[name=_1_i_amostra_tipoidade]").val()=="Semana(s)")?(vQuant*7):vQuant;
	return moment().subtract(diasAlojamento, 'days').format("DD/MM/YYYY");
}

function acEspecialidadeNucleo(){
	$oIdnucleo.autocomplete({
		source: jsonAc
		,delay: 0
		,select: function(){
			console.log($(this).cbval());
			preencheInputsNucleoAmostra($(this).cbval());
		}
		,create: function(){
			$(this).data('ui-autocomplete')._renderItem = function (ul, item) {
				vnucleo = "<span class='cinzaclaro'>Núcleo: </span>" + item.label;
				vnucleo = (item.lote)?vnucleo+" <span class=cinzaclaro> - Lote: </span>" + item.lote:vnucleo;
				return $('<li>')
					.append('<a>' + vnucleo + '</a>')
					.appendTo(ul);
			};
		}
		,noMatch: function(objAc){
			console.log("Executei callback");
			CB.post({
				objetos: "_x_i_nucleo_situacao=ATIVO&_x_i_nucleo_nucleo="+objAc.term+"&_x_i_nucleo_idpessoa="+$(":input[name=_1_"+CB.acao+"_amostra_idpessoa]").cbval()
				,parcial:true
				,refresh: false
				,msgSalvo: "Núcleo criado"
				,posPost: function(data, textStatus, jqXHR){
					//Atualiza source json
					$oIdnucleo.data('uiAutocomplete').options.source.push({
						label: $oIdnucleo.val()
						,value: CB.lastInsertId
					});
					//Atualiza o objeto DATA associado ao input
					$oIdnucleo.data("nucleos")[CB.lastInsertId]={"nucleo":$oIdnucleo.val()};
					//Mostra a nova opção
					$oIdnucleo.autocomplete( "search", $oIdnucleo.val());
					//Informa visualmente o usuário para que complete as informações do núcleo
					getInputsNucleoAmostra().addClass("aguardandoCbpost");
					//Mostra campos adicionais do Núcleo
					$("#col_tipoaves, #col_finalidade").show()
					//Possibilita atualização do núcleo no prePost
					booAtualizaNucleo=true;
				}
			});
		}
	})
	//Uma propriedade com o json é adicionada ao objeto para tornar possível a consulta posterior
	.data("nucleos",jsonNuc);
}

/*
 * Ações antes de salvar amostra
 */
CB.prePost = function(inpar){
	if(booAtualizaNucleo){
		updNuc = "_x_u_nucleo_idnucleo="+$("[name=_1_"+CB.acao+"_amostra_idnucleo]").cbval()+
			"&_x_u_nucleo_lote="+$("[name=_1_"+CB.acao+"_amostra_lote]").val()+
			"&_x_u_nucleo_granja="+$("[name=_1_"+CB.acao+"_amostra_granja]").val()+
			"&_x_u_nucleo_unidadeepidemiologica="+$("[name=_1_"+CB.acao+"_amostra_unidadeepidemiologica]").val()+
			"&_x_u_nucleo_idespeciefinalidade="+$("[name=_1_"+CB.acao+"_amostra_idespeciefinalidade]").cbval()+
			"&_x_u_nucleo_regoficial="+$("[name=_1_"+CB.acao+"_amostra_regoficial]").val()+
			"&_x_u_nucleo_alojamento="+getDataAlojamento()+
			"&_x_u_nucleo_rotulonucleotipo="+$("#rotulonucleotipo").val();

		//Altera os dados do Núcleo
		CB.post({
			objetos: updNuc
			,parcial:true
			,refresh: false
			,msgSalvo: "Núcleo atualizado"
		});
	}
}

function preencheInputsNucleoAmostra(inIdNucleo){
	jNucleo = $("[name=_1_"+CB.acao+"_amostra_idnucleo]").data("nucleos")[inIdNucleo];

	booAltera=false;
	if(
		//A pergunta de confirmação somente ocorrerá quando acao==u
		$("[name=_1_u_amostra_granja]").val()
		|| $("[name=_1_u_amostra_unidadeepidemiologica]").val()
		|| $("[name=_1_u_amostra_nucleoamostra]").val()
		|| $("[name=_1_u_amostra_lote]").val()
		|| $("[name=_1_u_amostra_tipoaves]").val()
		|| $("[name=_1_u_amostra_idade]").val()
		|| $("[name=_1_u_amostra_tipoidade]").val()){
		if(confirm("Os valores do Núcleo na amostra serão alterados.\nDeseja realmente confirmar a alteração\ndessas informações?")){
			booAltera=true;
		}else{
			booAltera=false;
		}
	}else{
		booAltera=true;
	}

	if(booAltera){
		//Caso seja selecionada na drop uma opção inexistente, esta variável será undefined, portanto isto limpará os campos relacionados ao núcleo
		if(jNucleo===undefined){
			$("[name=_1_"+CB.acao+"_amostra_granja] \
				,[name=_1_"+CB.acao+"_amostra_unidadeepidemiologica] \
				,[name=_1_"+CB.acao+"_amostra_nucleoamostra] \
				,[name=_1_"+CB.acao+"_amostra_lote] \
				,[name=_1_"+CB.acao+"_amostra_tipoaves] \
				,[name=_1_"+CB.acao+"_amostra_tipoidade] \
				,[name=_1_"+CB.acao+"_amostra_idade] \
				,[name=_1_"+CB.acao+"_amostra_nsvo] \
				,[name=_1_"+CB.acao+"_amostra_cpfcnpjprod] \
				,[name=_1_"+CB.acao+"_amostra_uf] \
				,[name=_1_"+CB.acao+"_amostra_cidade] \
				,[name=_1_"+CB.acao+"_amostra_regoficial]").val("");
				$("[name=_1_"+CB.acao+"_amostra_idespeciefinalidade]").val("").cbval("");
		}else{
			//Alterar os campos da amostra
			$("[name=_1_"+CB.acao+"_amostra_granja]").val(jNucleo.granja);
			$("[name=_1_"+CB.acao+"_amostra_unidadeepidemiologica]").val(jNucleo.unidadeepidemiologica);
			$("[name=_1_"+CB.acao+"_amostra_nucleoamostra]").val(jNucleo.nucleo);
			$("[name=_1_"+CB.acao+"_amostra_lote]").val(jNucleo.lote);
			$("[name=_1_"+CB.acao+"_amostra_tipoaves]").val(jNucleo.tipoaves);
			$("[name=_1_"+CB.acao+"_amostra_idespeciefinalidade]").cbval(jNucleo.idespeciefinalidade);
			$("[name=_1_"+CB.acao+"_amostra_idespeciefinalidade]").val(jNucleo.especiefinalidade);
			$("[name=_1_"+CB.acao+"_amostra_regoficial]").val(jNucleo.regoficial);
			$("[name=_1_"+CB.acao+"_amostra_nsvo]").val(jNucleo.nsvo);
			$("[name=_1_"+CB.acao+"_amostra_cpfcnpjprod]").val(jNucleo.cpfcnpj);
			$("[name=_1_"+CB.acao+"_amostra_uf]").val(jNucleo.uf);
			//preenchecidade();
			$("[name=_1_"+CB.acao+"_amostra_cidade]").val(jNucleo.cidade);

			if(jNucleo.rotulonucleotipo){//Caso o núcleo tenha sido inserido sem salvar a amostra, ele não terá estas propriedades
				$("#lb_idnucleo").html("<b>"+jNucleo.rotulonucleotipo+":</b>")
			};

			if(jsonEspecieFinalidade[jNucleo.idespeciefinalidade] && jsonEspecieFinalidade[jNucleo.idespeciefinalidade].flgcalculo=='Y'){

				if(jsonEspecieFinalidade[jNucleo.idespeciefinalidade].calculoidade=='D'){
					tipoidade="Dia(s)";
					tipoCalc="days";
				}else if(jsonEspecieFinalidade[jNucleo.idespeciefinalidade].calculoidade=='S'){
					tipoidade="Semana(s)";
					tipoCalc="weeks";
				}else{
					tipoCalc="";
				}


				$("[name=_1_"+CB.acao+"_amostra_tipoidade]").val(tipoidade);

				if(jNucleo.alojamento && tipoCalc!=""){
					//Calcula a idade do nucleo conforme o tipo de ave (utiliza o plugin http://momentjs.com/)
					dataamostra = $("[name=_1_"+CB.acao+"_amostra_dataamostra]").val()||"";
					dataamostra = moment(dataamostra,"DD/MM/YYYY");
					dataaloj = moment(jNucleo.alojamento);
					idadenuc = dataamostra.diff(dataaloj,tipoCalc);
					$("[name=_1_"+CB.acao+"_amostra_idade]").val(idadenuc);
				}else{
					$("[name=_1_"+CB.acao+"_amostra_idade]").val("");
				}
			}
		}
	}
}

cbPostCallback = function(jqXHR,data,objFoco){

	//idamostra = jqXHR.getResponseHeader("X-CB-PKID")||$("[name=_1_"+CB.acao+"_amostra_idamostra]").val();

	idamostra = jqXHR.getResponseHeader("X-CB-PKID")

	if(idamostra){
		if(CB.modulo=="amostraaves" && confirm("====== REGISTRO CONCLUÍDO ======\n\nDeseja imprimir as etiquetas?\n\n===========================")){
			imprimeTestes(idamostra);
			//duplicarAmostra();
		}
	}
}

//Valida se data coleta é inferior à 2 dias da data da amostra, para alertar o usuário
function validaDataColeta(inDataColeta, inDataAmostra){

	oDatacoleta = $("[name=_1_"+CB.acao+"_amostra_datacoleta]");

	if(inDataColeta && inDataAmostra){

		dataamostra = moment(dataamostra,"DD/MM/YYYY");
		datacoleta = moment(datacoleta,"DD/MM/YYYY");

		difColeta = datacoleta.diff(dataamostra,"days");

		if(difColeta<-1 ){
			oDatacoleta.addClass("fundovermelho branco");
		}else{
			oDatacoleta.removeClass("fundovermelho branco");
		}
	}else{
		oDatacoleta.removeClass("fundovermelho branco");
	}
}

//O daterangepicker não dispara o "change" do elemento. Portanto deve ser feita verificação do evento do plugin
$("[name=_1_"+CB.acao+"_amostra_datacoleta]").on('apply.daterangepicker', function (ev, picker) {

	dataamostra = ($("[name=_1_"+CB.acao+"_amostra_dataamostra]").val()||"");
	datacoleta = (picker.startDate.format("DD/MM/YYYY")||"");

	validaDataColeta(dataamostra, datacoleta);

}).on('change', function () {

	dataamostra = ($("[name=_1_"+CB.acao+"_amostra_dataamostra]").val()||"");
	datacoleta = $("[name=_1_"+CB.acao+"_amostra_datacoleta]").val();
	datacoleta = moment(datacoleta,"DD/MM/YYYY");

	validaDataColeta(dataamostra, datacoleta);

});

function imprimeTestes(inIdamostra){
	var imprimir=true;
	CB.imprimindo=true;

	if(CB.lotesImpressaoAlterado){
		if(!confirm("Você alterou os lotes de impressão.\nDeseja realmente enviar para a impressora?")){
			imprimir=false;
		}
	}

	if(imprimir){
		$.ajax({
			type: "get",
			url : "ajax/impetiqueta.php?idamostra="+inIdamostra,
			success: function(data){
				console.log(data);
				alertAzul("Enviado para impressão","",1000);
				CB.lotesImpressaoAlterado=false;
				if($("[name=_1_u_amostra_status]").val() && $("[name=_1_u_amostra_status]").val()=="ABERTO"){
					CB.post({
						objetos: "_x_u_amostra_idamostra="+inIdamostra+"&_x_u_amostra_status=FECHADO"
						,parcial:true
						,callback: function(){
							CB.oBtNovo.removeClass("disabled");
							CB.oBtSalvar.addClass("disabled");

							botaoEditarAmostra();

						}
					});
				}
			}
		});
	}
}

function botaoEditarAmostra(){
	$bteditar = $("#editarAmostra");
	if($bteditar.length==0){
		CB.novoBotaoUsuario({
			id:"editarAmostra"
			,rotulo:"Editar Amostra"
			,icone:"fa fa-pencil"
			,onclick:function(){
				CB.post({
					objetos: "_x_u_amostra_idamostra="+$("[name=_1_u_amostra_idamostra]").attr("value")+"&_x_u_amostra_status=ABERTO"

					,parcial:true
				});
			}
		});
	}
}

function resetNucleo(){
	$("[name=_1_"+CB.acao+"_amostra_idnucleo],[name=_1_"+CB.acao+"_amostra_idespeciefinalidade]").val("").cbval(0);
	$("[name=_1_"+CB.acao+"_amostra_rotulonucleotipo],[name=_1_"+CB.acao+"_amostra_idade],[name=_1_"+CB.acao+"_amostra_tipoidade],[name=_1_"+CB.acao+"_amostra_nucleoamostra],[name=_1_"+CB.acao+"_amostra_lote],[name=_1_"+CB.acao+"_amostra_granja],[name=_1_"+CB.acao+"_amostra_unidadeepidemiologica],[name=_1_"+CB.acao+"_amostra_regoficial],[name=_1_"+CB.acao+"_amostra_nsvo],[name=_1_"+CB.acao+"_amostra_cidade],[name=_1_"+CB.acao+"_amostra_uf],[name=_1_"+CB.acao+"_amostra_cpfcnpj]").val("");
	
}

function alterarNucleo(){
	vIdNucleo = $("[name=_1_"+CB.acao+"_amostra_idnucleo]").cbval();
	if(vIdNucleo){
		janelamodal('?_modulo=nucleo&_acao=u&idnucleo='+vIdNucleo,1000,1100);
	}else{
		alertAtencao("Núcleo não selecionado!","");
	}
}

function novoNucleo(){
	vIdpessoa = $("[name=_1_"+CB.acao+"_amostra_idpessoa]").cbval();
	janelamodal('?_modulo=nucleo&_acao=i&idpessoa='+vIdpessoa,1000,1100);
}

//Atualiza rótulo indicador da quantidade de testes da amostra
function atualizaQuantTestes(){
	oQuant = $("#testesquant");
	iTrsTeste = $("#tbTestes tbody tr").length;
	oQuant.html(iTrsTeste);
}

//Inicializa tela
$("[name=_1_"+CB.acao+"_amostra_idsubtipoamostra]").focus();
criaAutocompletesTestes();
arrCoresLoteEtiquetas = $.extend({}, ["silver","#cc0000", "#0000cc", "#00cc00", "#990000","#ff6600", "#fcd202", "#b0de09", "#0d8ecf",  "#cd0d74"]);
preencheDropNucleos();
atualizaQuantTestes();
configuraOficial();

if("<?=$_1_u_amostra_status?>"=="FECHADO"){
	botaoEditarAmostra();
}

if($("[name=_1_"+CB.acao+"_amostra_idsubtipoamostra]").cbval()!==""){
	popoverEditarCamposVisiveis();
}

function incluirEmLote(){
	novajanela("google");
}

$('input[name="datahorarecebimento"]').daterangepicker({
	timePicker: true,
	timePickerIncrement: 15,
	"singleDatePicker": true,
	"showDropdowns": true,
	"linkedCalendars": false,
	"opens": "left",
	"locale": {format: 'DD/MM/YYYY h:mm'}
});

//Recuperar estado da coluna para impressão do documento de TRA
function getColunaVisivelTra(inInputName){

	//Separa o nome da coluna para update/delete na tabela de configuracoes
	$coluna = inInputName.explodeInputNameCarbon()[4];

	//Verifica se existe no Json da tabela telaamostraconf
	try{
		idconf=jsonTelaamostraconf["TRA"][$coluna][$("[name=_1_"+CB.acao+"_amostra_idsubtipoamostra]").cbval()];
		if(idconf){
			return jsonTelaamostraconf["TRA"][$coluna][$("[name=_1_"+CB.acao+"_amostra_idsubtipoamostra]").cbval()];
		}else{
			return false;
		}
	}catch(e){
		return false;
	}
}

/*
 * Monta uma listagem com os inputs disponíveis na tela, para que o usuário configure a tela como desejar
 */
function listaInputsAmostra(){
	strTable = "<table>";
	//Recupera todos os containers que contém inputs visíveis/invisíveis
	$.each($(".rowDin"), function(i, row){
		strTable += "<tr>";
		inputs = $(row).find(":input[name*=_]");

		//Recupera todos os inputs da amostra
		$.each(inputs, function(i, input){
			$input = $(input);
			rotulo = $input.attr("title") || $input.attr("name");
			
			//Classe para o botão de toggle do formulário
			strclass = $input.is(":visible")?"btn btn-success btn-xs pointer":"btn btn-default btn-xs pointer";
			stricon = $input.is(":visible")?"fa fa-eye":"fa fa-eye-slash";
			strVisivel = $input.is(":visible")?"Y":"N";
			//Classe para o botão de toggle da visibilidade no TRA
			iidtelamostraconf=getColunaVisivelTra($input.attr("name"));
			strclassTra = iidtelamostraconf?"laudoicon docaut fa-lg verde pointer":"laudoicon docaut fa-lg fade cinzaclaro hovercinza pointer";
			strTitleTra = iidtelamostraconf?"Ocultar informação no TRA":"Mostrar informação na impressão de TRA";
			
			//Adiciona ao html
			strTable += "<td>";
			strTable += "<button class='"+strclass+"' onclick='toggleInputsVisiveis(this)' cbvisivel='"+strVisivel+"' cbinputname='"+$input.attr("name")+"'><i class='"+stricon+"'></i> "+rotulo+"</button>"
			strTable += "<i title='"+strTitleTra+"' class='"+strclassTra+"' onclick='toggleInputsVisiveisTra(this)' idtelaamostraconf='"+iidtelamostraconf+"' inputname='"+$input.attr("name")+"' style='margin-left: 6px;vertical-align: middle;'></i>";
			strTable += "</td>";
		})
		strTable += "</tr>";
	})
	strTable += "</table>";
	return strTable;
}

function popoverEditarCamposVisiveis(){
	$('#editarCamposVisiveis').webuiPopover("destroy").webuiPopover({
		title:'Selecionar campos visíveis'
		,content: listaInputsAmostra
	});
}

function toggleInputsVisiveis(inBotao){
	$inbt = $(inBotao);

	//Recupera o input associado ao Botão clicado:
	$input = $("[name="+$inbt.attr("cbinputname")+"]");

	//Separa o nome da coluna para update/delete na tabela de configuracoes
	$coluna = $input.attr("name").explodeInputNameCarbon()[4];

	//Recupera o container mais próximo com o atributo id=col_nomecoluna
	$col_ = $("[name="+$input.attr("name")+"]").parents("[id*=col_]");
	if($col_.length==0) console.error("Erro ao alterar visibilidade: O objeto Input ["+$inbt.attr("cbinputname")+"] deve estar contido em uma tag (Ex:div) com id=col_"+$coluna);

	//Recupera o container mais próximo contendo o label id=lb_nomecoluna
	$lb_ = $("#"+$col_.attr("id").replace("col_","lb_"));
	if($lb_.length==0) console.error("Erro ao alterar visibilidade: O LABEL para o objeto Input ["+$inbt.attr("cbinputname")+"] deve estar contido em uma tag 'parent' com id=lb_"+$coluna);

	//Objetos de label e coluna
	$collb=$().add($col_).add($lb_);

	if($inbt.attr("cbvisivel")=="Y"){
		//Recupera a PK da configuração que será apagada
		idtelaamostraconf = jsonTelaamostraconf["TELA"][$coluna][$("[name=_1_"+CB.acao+"_amostra_idsubtipoamostra]").cbval()];
		if(!idtelaamostraconf){
			alertAtencao("Erro: idtelaamostraconf não recuperado para");
		}else{
			//Remove também a visibilidade no TRA
			iidtelamostraconf=getColunaVisivelTra($input.attr("name"));
			strTra=iidtelamostraconf?"&_y_d_telaamostraconf_idtelaamostraconf="+iidtelamostraconf:"";
			CB.post({
				objetos: "_x_d_telaamostraconf_idtelaamostraconf="+idtelaamostraconf+strTra
				,parcial:true
				,refresh: false
				,msgSalvo: "Coluna oculta"
				,posPost: function(data, textStatus, jqXHR){

					jsonTelaamostraconf["TELA"][$coluna][$("[name=_1_"+CB.acao+"_amostra_idsubtipoamostra]").cbval()] = CB.lastInsertId;

					$collb.hide();
					$inbt.removeClass("btn-success")
							.addClass("btn-default")
							.attr("cbvisivel","N")
							.find("i")
							.removeClass("fa-eye")
							.addClass("fa-eye-slash");
				}
			});
		}
	}else{
		CB.post({
			objetos: "_x_i_telaamostraconf_local=TELA&_x_i_telaamostraconf_idunidade="+$("[name=_1_"+CB.acao+"_amostra_idunidade]").val()+"&_x_i_telaamostraconf_idsubtipoamostra="+$("[name=_1_"+CB.acao+"_amostra_idsubtipoamostra]").cbval()+"&_x_i_telaamostraconf_campo="+$coluna
			,parcial:true
			,refresh: false
			,msgSalvo: "Coluna visível"
			,posPost: function(data, textStatus, jqXHR){

				$collb.show();
				$inbt.removeClass("btn-default").addClass("btn-success").attr("cbvisivel","Y").find("i").removeClass("fa-eye-slash").addClass("fa-eye");
			}
		});
	}
	console.log($input);
	//console.log(explodeInputNameCarbon($input.attr("cbinputname")));
}

function toggleInputsVisiveisTra(inBotao){
	$inbt = $(inBotao);
	$coluna = $inbt.attr("inputname").explodeInputNameCarbon()[4];
	$idtelaamostraconf = $inbt.attr("idtelaamostraconf");
	
	if($idtelaamostraconf=="false"){//comparação textual
		CB.post({
			objetos: "_x_i_telaamostraconf_local=TRA&_x_i_telaamostraconf_idunidade="+$("[name=_1_"+CB.acao+"_amostra_idunidade]").val()+"&_x_i_telaamostraconf_idsubtipoamostra="+$("[name=_1_"+CB.acao+"_amostra_idsubtipoamostra]").cbval()+"&_x_i_telaamostraconf_campo="+$coluna
			,parcial:true
			,refresh: false
			,msgSalvo: "Informação visível no TRA"
			,posPost: function(){
				$inbt.removeClass("fade cinzaclaro").addClass("verde");
			}
		});
	}else{
		CB.post({
				objetos: "_x_d_telaamostraconf_idtelaamostraconf="+$idtelaamostraconf
				,parcial:true
				,refresh: false
				,msgSalvo: "Coluna oculta"
				,posPost: function(){
					$inbt.removeClass("verde").addClass("fade cinzaclaro");
				}
			});
	}
	
}

//<i class="fa fa-times cbFecharForm" title="Fechar" onclick="CB.fecharForm()"></i>

$(".dragtra").draggable();

$("#novoTra").droppable({
	accept: ".dragtra"
	,drop: function( event, ui ) {
		alertAtencao("Crie um novo Termo de Recepção de Amostra para relacionar lotes");
	}
});

vIdTra='<?=$arrTRAAssociado["idtra"]?>';

$("#formTra").droppable({
	accept: ".dragtra"
	,drop: function( event, ui ) {
		CB.post({
			objetos: "_x_i_traitem_idtra="+vIdTra+
					"&_x_i_traitem_idobjeto="+$(ui.draggable).attr("idlote")+
					"&_x_i_traitem_tipoobjeto=lote"
			,parcial:true
			,refresh: "refresh"
		});
	}
});

function novoTra(inIdpessoa){
    CB.post({
	objetos: "_x_i_tra_idpessoa="+inIdpessoa
	,parcial:true
	,posPost: function(data, textStatus, jqXHR){					
	    amostra_tra(CB.lastInsertId);
	}
    })
}



function excluiTraItem(inIdTraItem){
	CB.post({
		objetos: "_x_d_traitem_idtraitem="+inIdTraItem
		,refresh: "refresh"
	})
}

if( $("[name=_1_u_amostra_idamostra]").val() ){
	$(".cbupload").dropzone({
		idObjeto: $("[name=_1_u_amostra_idamostra]").val()
		,tipoObjeto: 'amostra'
	});
}

function statusenviaemailtea(intra){
	CB.post({
		objetos: "_x_u_tra_idtra="+intra+"&_x_u_tra_emailtea=Y"
		,refresh:"refresh"
	});
}

function preenchecidade(){
	
	$("#cidade").html("<option value=''>Procurando....</option>");
	
	$.ajax({
			type: "get",
			url : "ajax/buscacidade.php",
			data: { uf : $("#uf").val() },

			success: function(data){
				$("#cidade").html(data);
			},

			error: function(objxmlreq){
				alert('Erro:<br>'+objxmlreq.status); 

			}
		})//$.ajax

}

function flgcobrancaobrig(vthis){
    var atval=$(vthis).attr('atval');
    var idresultado=$(vthis).attr('idresultado');
    CB.post({
	objetos: "_x_u_resultado_idresultado="+idresultado+"=&_x_u_resultado_cobrancaobrig="+atval	
        ,parcial:true
        ,refresh: false
        ,msgSalvo: "Alterada Cobrança do Teste."
        ,posPost: function(){
               if(atval='Y'){
                   $(vthis).attr('atval','N');
               }else{
                   $(vthis).attr('atval','Y');
               }
        }
    })
    
}

function flgcobranca(vthis){
    var atval=$(vthis).attr('atval');
    var idresultado=$(vthis).attr('idresultado');
    CB.post({
	objetos: "_x_u_resultado_idresultado="+idresultado+"=&_x_u_resultado_cobrar="+atval	
        ,parcial:true
        ,refresh: false
        ,msgSalvo: "Alterada Cobrança do Teste."
        ,posPost: function(){
               if(atval='Y'){
                   $(vthis).attr('atval','N');
               }else{
                   $(vthis).attr('atval','Y');
               }
        }
    })
    
}

/*
$().ready(function() {
	 $("#uf").change(function(){
		preenchecidade();
	}
	);
});
*/
//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>
<?

if($_1_u_amostra_status=="FECHADO" && $_GET["_pagereadonly"]!="N" && getModsUsr("MODULOS")[$_GET['_modulo']]["permissao"] == 'w'){
	$_pagereadonly=false;
	echo "<script>confInputs();</script>";
	
}else if (getModsUsr("MODULOS")[$_GET['_modulo']]["permissao"] == 'r'){
	$_pagereadonly=true;
	echo "<script>confInputs(1);</script>";
}else{

}
require_once '../inc/php/readonly.php';
?>
