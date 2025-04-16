<?

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$ler=true;//Gerar logs de erro
$rid="\n".rand()." - Sislaudo: ";
if($ler)error_log($rid.basename(__FILE__, '.php'));

session_start();
$sessionid = session_id();//PEGA A SESSÃO

ini_set("display_errors","1");
error_reporting(E_ALL);

if (defined('STDIN')){//se estiver sendo executao em linhade comando
	$prefu="stdin_";
	include_once("/var/www/carbon8/inc/php/functions.php");
}else{//se estiver sendo executado via requisicao http
	include_once("../inc/php/functions.php");
}


function getidempresacron($param1, $param2){
	return 'and '.$param1.' = 1 ';
	
}


 
 
 
	$ss = "SELECT d.iddashboard
			,d.dashboard
			,d.dashboard_title
			,d.url
			,d.idunidade
			,d.especial
			,d.titulo
			,d.code
			,d.panel_id
			,d.panel_class_col
			,concat('".$titulo." ', panel_title) as panel_title
			,d.card_id
			,d.card_class_col
			,d.card_url
			,d.card_notification_bg
			,d.card_notification
			,d.card_color
			,d.card_border_color
			,d.card_bg_class
			,d.card_title
			,d.card_title_sub
			,d.card_value as card_value
			,d.card_icon
			,d.card_title_modal
			,d.card_url_modal
			FROM laudo.dashboard d
				JOIN "._DBCARBON."._lpobjeto lo on lo.tipoobjeto='dashboard' 
					and lo.idobjeto=d.iddashboard
					and lo.idlp in(".getModsUsr("LPS").")
					where d.status = 'ATIVO' and d.cron = 'Y'
					order by
			d.dashboard, d.panel_id, card_id";

        $rs = d::b()->query($ss) or die("Erro ao recuperar snippets: ". mysqli_error(d::b()));
	
	$i = 0;

	$sqldash = '';
	while($_row= mysqli_fetch_assoc($rs)){ 
		$sqldash .= 	$unionall.$_row['code'];
		$unionall = ' UNION ALL ';
	
	}
	
	echo '<pre>'.$sqldash.'</pre>';
	
	$resfig = d::b()->query($sqldash) or die("Erro ao recuperar figura para cabeçalho do relatório: ".mysql_error());
	while($_row= mysqli_fetch_assoc($resfig)){ 
		$sqldash .= 	$unionall.$_row['code'];
		$unionall = ' UNION ALL ';
		
		$result[$i]['iddashboard']			= $_row['iddashboard'];
		$result[$i]['dashboard']			= $_row['dashboard'];
		$result[$i]['panel_id']			= $_row['panel_id'];
		$result[$i]['dashboard_title']			= $_row['dashboard_title'];
		$result[$i]['card_id']			= $_row['card_id'];
		$result[$i]['card_class_col']			= $_row['card_class_col'];
		$result[$i]['card_url']			= addslashes($_row['card_url']);
		$result[$i]['card_notification_bg']			= $_row['card_notification_bg'];
		$result[$i]['card_notification']			= $_row['card_notification'];
		$result[$i]['card_border_color']			= $_row['card_border_color'];
		$result[$i]['card_color']			= $_row['card_color'];
		$result[$i]['card_bg_class']			= $_row['card_bg_class'];
		$result[$i]['card_title']			= $_row['card_title'];
		$result[$i]['card_title_sub']			= $_row['card_title_sub'];
		$result[$i]['card_title_modal']			= $_row['card_title_modal'];
		$result[$i]['card_url_modal']			= $_row['card_url_modal'];
		$result[$i]['card_value']			= $_row['card_value'];
		$result[$i]['card_icon']			= $_row['card_icon'];
		$i++;
		
	}
	
	
	$j = 0;
	
	while ($j < $i){

		$nsql = 
		"update dashboard set
			
			dashboard_title = '".$result[$j]['dashboard_title']."',
			panel_id = '".$result[$j]['panel_id']."',
			card_id = '".$result[$j]['card_id']."',
			card_class_col = '".$result[$j]['card_class_col']."',
			card_url = '".$result[$j]['card_url']."',
			card_notification_bg = '".$result[$j]['card_notification_bg']."',
			card_notification = '".$result[$j]['card_notification']."',
			card_border_color = '".$result[$j]['card_border_color']."',
			card_color = '".$result[$j]['card_color']."',
			card_bg_class = '".$result[$j]['card_bg_class']."',
			card_title = '".$result[$j]['card_title']."',
			card_title_sub = '".$result[$j]['card_title_sub']."',
			card_title_modal = '".$result[$j]['card_title_modal']."',
			card_url_modal = '".$result[$j]['card_url_modal']."',
			card_value = '".$result[$j]['card_value']."',
			card_icon = '".$result[$j]['card_icon']."'
			
		where
			iddashboard = '".$result[$j]['iddashboard']."';";
		
		echo $nsql;
		d::b()->query($nsql	);
		$j++;
	}
		
		
		
		
?>