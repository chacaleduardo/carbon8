<?
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$ler=true;//Gerar logs de erro
$rid="\n".rand()." - Sislaudo: ";
if($ler)error_log($rid.basename(__FILE__, '.php'));

session_start();


ini_set("display_errors","1");
error_reporting(E_ALL);

if (defined('STDIN')){//se estiver sendo executao em linhade comando
	$prefu="stdin_";
	include_once("/var/www/carbon8/inc/php/functions.php");
}else{//se estiver sendo executado via requisicao http
	include_once("../inc/php/functions.php");
}
include_once(__DIR__."/controllers/removealertasislaudo_controller.php");

$grupo = rstr(8);

re::dis()->hMSet('cron:removealertasislaudo',['inicio' => Date('d/m/Y H:i:s')]);

RemoveAlertaSislaudoController::inserirLog(1,$grupo,'cron','removealertasislaudo','status','INICIO','SUCESSO','','now()',"DATE_FORMAT(NOW(), '%Y-%m-%d')");

//busca  as configurações para envio da mensagem
echo '<pre>';
echo $sql="SELECT 
				ifnull(if(trim(ic.tabela) = '', null, ic.tabela), m.tab) as tab,
				if (m.modulo = 'tarefaacumulada', 'pessoa',m.modulo) as modulo,
				m.rotulomenu,tc.col,ic.idimmsgconf,ic.titulo,ic.tipo,ic.ideventotipo,ic.code,ic.mensagem,ic.titulocurto,ic.apartirde,ic.multiplo,
				DATE_FORMAT(CASE
				WHEN SUBSTR(multiplo, CHAR_LENGTH(CAST(multiplo AS UNSIGNED))+2) ='Minute' THEN DATE_ADD(now(), INTERVAL CAST(multiplo AS UNSIGNED) MINUTE)
				WHEN SUBSTR(multiplo, CHAR_LENGTH(CAST(multiplo AS UNSIGNED))+2) ='Hour' THEN DATE_ADD(now(), INTERVAL CAST(multiplo AS UNSIGNED) HOUR)
				WHEN SUBSTR(multiplo, CHAR_LENGTH(CAST(multiplo AS UNSIGNED))+2) ='Year' THEN DATE_ADD(now(), INTERVAL CAST(multiplo AS UNSIGNED) YEAR)
				WHEN SUBSTR(multiplo, CHAR_LENGTH(CAST(multiplo AS UNSIGNED))+2) ='Month' THEN DATE_ADD(now(), INTERVAL CAST(multiplo AS UNSIGNED) MONTH)
				WHEN SUBSTR(multiplo, CHAR_LENGTH(CAST(multiplo AS UNSIGNED))+2) ='Day' THEN DATE_ADD(now(), INTERVAL CAST(multiplo AS UNSIGNED) DAY)
				WHEN SUBSTR(multiplo, CHAR_LENGTH(CAST(multiplo AS UNSIGNED))+2) ='Week' THEN DATE_ADD(now(), INTERVAL CAST(multiplo AS UNSIGNED) WEEK) 
				END , '%Y-%m-%d') as prazo,
				ms.assinar as assinar,
				ets.idfluxostatus as idfluxostatus
			from 
			immsgconf ic
			JOIN "._DBCARBON."._modulo m ON m.modulo = ic.modulo
			JOIN "._DBCARBON."._mtotabcol tc on (tc.tab = m.tab or ic.tabela = tc.tab) and  tc.primkey ='Y'
			JOIN fluxo ms ON ms.idobjeto = ic.ideventotipo AND ms.tipoobjeto = 'ideventotipo'
			LEFT JOIN fluxostatus ets on ets.idfluxo = ms.idfluxo 
			JOIN "._DBCARBON."._status s on ets.idstatus = s.idstatus and s.statustipo IN ('FIM', 'CANCELADO', 'CONCLUIDO')

			where              
				ic.tipo not in('E','ET','EP')
			--	and ic.idimmsgconf = 1
				and ic.status='ATIVO'
				and exists (select 1 from immsgconffiltros f where f.valor!=' ' and f.valor is not null and f.idimmsgconf = ic.idimmsgconf)
			group by ic.idimmsgconf	
			order by ic.idimmsgconf	";
	echo '<br />';	
	// die('<pre>'.$sql.'</pre>');	 
 $res=RemoveAlertaSislaudoController::buscarConfiguracoesDaRemocaoDeMensagem();

if($res->error()){
	$strerr="A Consulta na immsgconf falhou : " . $res->errorMessage() . "<p>SQL: $sql";
	if($ler)error_log($rid.$strerr);
}else{
	if($ler)error_log($rid."Consulta immsgconf ok: ".$res->numRows()." registros");
}
 
foreach($res->data as $k => $row){
	unset($arrfinal);
	unset($arrencontrado);
	unset($arr);
	unset($ev);
	//busca os filtros para seleção
	// GVT - 13/02/2020 - Alterado o select para trazer valores nulos quando estão acompanhados do sinal 'is' ou 'is not'
	$resf=RemoveAlertaSislaudoController::buscarCamposDaConfiguracoesDoEnvioDeMensagem($row["idimmsgconf"]);

	$qtdf=count($resf);
	$and=" ";
	if($qtdf>0){
		$clausula="";
		foreach($resf->data as $k1 => $rowf){
			// GVT - 13/02/2020 - Alterado a ci=ondição para permitir valores nulos quando estão acompanhados do sinal 'is' ou 'is not'
			if(($rowf["valor"]!='null' and $rowf["valor"]!=' ' and $rowf["valor"]!='') or ($rowf["valor"]=='null' and ($rowf["sinal"]=='is' or $rowf["sinal"]=='!='))){
				if($rowf["valor"]=='now'){
					if(!empty($rowf["nowdias"])){
 						$rowf["nowdias"];
						$date=date("Y-m-d");
						$valor=date('Y-m-d', strtotime($date. ' - '.$rowf["nowdias"].' day'));
// '1';
					}else{
						$valor=date("Y-m-d"); 
// '2';
					}
				}else if($rowf["valor"]=='mais'){
					$date=date("Y-m-d");
					$valor=date('Y-m-d', strtotime($date. ' + '.$rowf["nowdias"].' day'));
// '3';
				}else if($rowf["valor"]=='menos'){
					$date=date("Y-m-d");
					$valor=date('Y-m-d', strtotime($date. ' - '.$rowf["nowdias"].' day'));
// '3';
				}else{
					$valor=$rowf["valor"];
// '3';
				}
				// $valor;

				if($rowf['sinal']=='in'){
					$strvalor = str_replace(",","','",$valor);
					$clausula.= $and." a.".$rowf["col"]." in ('".$strvalor."')";
				}elseif($rowf['sinal']=='like'){
					$clausula.= $and." a.".$rowf["col"]." like ('%".$valor."%')";
				}elseif($rowf['sinal']=='is'){
					$clausula.= $and." a.".$rowf["col"]." ".$rowf['sinal']." ".$valor."";
				}elseif($rowf['sinal']=='sql'){
					$clausula.= $and." a.".$rowf["col"]." ".$valor."";									   
				}else{
					$clausula.= $and." a.".$rowf["col"]." ".$rowf['sinal']." '".$valor."'";
				}
				$and=" and ";
			}else{
				if($ler)error_log($rid.'rowf[valor] não previsto');
			}
		}//while

		// busca na tabela configurada os ids
		echo $sqlx= "SELECT distinct 
						e.idevento as idevento,
						e.idmodulo,
						(select idfluxostatus from fluxo ms 
						   JOIN fluxostatus ets1 on ets1.idfluxo = ms.idfluxo 
						   JOIN carbonnovo._status s1 on ets1.idstatus = s1.idstatus  and s1.statustipo IN ('FIM', 'CANCELADO', 'CONCLUIDO')
                           where ms.idobjeto = e.ideventotipo AND ms.tipoobjeto = 'ideventotipo'  order by ordem limit 1) as idfluxostatus
					FROM immsgconflog l JOIN evento e on e.idevento =  l.idimmsgbody
					JOIN fluxostatuspessoa r on r.idmodulo = e.idevento AND r.modulo = 'evento'
					JOIN fluxo ms ON ms.idobjeto = e.ideventotipo AND ms.tipoobjeto = 'ideventotipo' 
					left JOIN fluxostatus ets on ets.idfluxo = ms.idfluxo and ets.idfluxostatus = r.idfluxostatus
					JOIN carbonnovo._status s on ets.idstatus = s.idstatus
					WHERE l.idimmsgconf = ".$row['idimmsgconf']." and
					e.idpessoa = 1029
					and (s.statustipo is null or s.statustipo = '')";
					
echo '<br />';
		//echo('<pre>'.$sqlx.'</pre>');
		$resx=RemoveAlertaSislaudoController::buscarEventosCriadosPelaConfiguracao($row['idimmsgconf']);

		if($resx->error()){
			$strerr="A Consulta na tabela de origem dos dados falhou : " . $resx->errorMessage() . "<p>SQL: $sqlx";
			if($ler)error_log($rid.$strerr);
		}else{
			if($ler)error_log($rid."immsgconflog ok: ".$resx->numRows()." registros");
		}

		$virgula = '';
		$ids = '';
		$i = 0;
		foreach($resx->data as $k2 => $rowx){ 
		
			
			if (!in_array($rowx['idmodulo'], $arr)) {
				$arr[] = $rowx['idmodulo'];
				$ids .= $virgula.$rowx['idmodulo'];
				$virgula = ',';
			}
			$ev[$rowx['idmodulo']][$i] = $rowx['idevento'];
			$st[$rowx['idmodulo']][$rowx['idevento']] = $rowx['idfluxostatus'];
			$i++;
			
		}
		
		if (!empty($ids)){
			
			$resz =RemoveAlertaSislaudoController::filtrarIdsDaBusca($row['col'],$row["tab"],$ids,$clausula);
			echo $resz->sql();
			foreach($resz->data as $k3 => $rowz){ 
				$arrencontrado[] = $rowz['idmodulo'];
			}		
			
			
		}
		//echo('<pre>'.$sqlz.'</pre>');
		if (!empty($arrencontrado)){
		//	echo '1';
			$arrfinal = array_diff($arr,$arrencontrado);
		
		}else{
		//	echo '2';
			$arrfinal = $arr;
		}
		
	
		foreach ($arrfinal as $value) {
			foreach ($ev[$value] as $v) {

				$resx=RemoveAlertaSislaudoController::atualizaEventosParaConcluido('CONCLUIDO',$st[$value][$v],'immsgconf',$v);		
				echo $resx->sql().'<br>';
				$resx=RemoveAlertaSislaudoController::atualizarParaNaoVisualizadoPorIdEvento($st[$value][$v],'immsgconf','evento',$v);
				echo $resx->sql().'<br>';
				
			}
		}		
	
	}else{
		if($ler)error_log($rid."immsgconffiltros: 0");
	}
}
        
re::dis()->hMSet('cron:removealertasislaudo',['fim' => Date('d/m/Y H:i:s')]);

RemoveAlertaSislaudoController::inserirLog(1,$grupo,'cron','removealertasislaudo','status','FIM','SUCESSO','','now()',"DATE_FORMAT(NOW(), '%Y-%m-%d')");



?>
<script>//location.reload();</script>