<head>
 <meta http-equiv="X-UA-Compatible" content="IE=EDGE"/>
 <meta http-equiv="Content-Type" content="text/html;"/>
 <meta name="Generator" content="Xara HTML filter v.8.1.1.474"/>
 <meta name="XAR Files" content="supervisorio/blocod/xr_files.txt"/>
 <title>blocod</title>
 <meta name="viewport" content="width=device-width, initial-scale=1" />
 <link rel="stylesheet" type="text/css" href="supervisorio/blocod/xr_fonts.css"/>
 <script type="text/javascript"><!--
 if(navigator.userAgent.indexOf('MSIE')!=-1 || navigator.userAgent.indexOf('Trident')!=-1){ document.write('<link rel="stylesheet" type="text/css" href="supervisorio/blocod/xr_fontsie.css"/>');}
 --></script>
 <script language="JavaScript" type="text/javascript">document.documentElement.className="xr_bgh0";</script>
 <link rel="stylesheet" type="text/css" href="supervisorio/blocod/xr_main.css"/>
 <link rel="stylesheet" type="text/css" href="supervisorio/blocod/xr_text.css"/>
 <link rel="stylesheet" type="text/css" href="supervisorio/blocod/custom_styles.css"/>
 <script type="text/javascript" src="supervisorio/blocod/roe.js"></script>
 <script type="text/javascript" src="supervisorio/blocod/replaceMobileFonts.js"></script>
<script src="../inc/js/jquery/jquery-1.11.2.min.js"></script>
 <link rel="stylesheet" type="text/css" href="supervisorio/blocod/ani.css"/>
</head>
<div style="color: #333;font-size: 18px; float: right;"><?=date("d/m/Y H:i:s");?><br>
 <span id="referencia"></span>

</div>
<div id="xr_td" class="xr_td">
<div class="xr_ap xr_xri_" style="width: 100%;">
 <img class="xr_rn_ xr_ap" src="supervisorio/blocod/blocod.jpg" title="" style="left: 0px; top: 66px; width: 100%;"/>
 <a href="" target="_blank">
 <span class="xr_ar 345" style="left: 460px; top: 165px; width: 60px; height: 19px; background-color: #F8BB00; border: 1px solid #333333;"></span>
 <div class="Normal_text" style="position: absolute; left:474px; top:180px; width:24px; height:10px;color:#333333;">
  <span class="xr_tl Normal_text" style="top: -11.77px;color:#333333;font-size:12px;" id="345"></span>  
 </div>
 </a>
 <a href="" target="_blank">
 
 <span class="xr_ar 349" style="left: 728px; top: 165px; width: 60px; height: 19px; background-color: #F8BB00; border: 1px solid #333333;"></span>
 <div class="Normal_text" style="position: absolute; left: 742px; top: 180px; width:24px; height:10px;color:#333333;">
  <span class="xr_tl Normal_text" style="top: -11.77px;color:#333333;font-size:12px;" id="349"></span> 
 </div>
 </a>
 <a href="" target="_blank">
 <span class="xr_ar 351" style="left: 863px; top: 165px; width: 60px; height: 19px; background-color: #F8BB00; border: 1px solid #333333;"></span>
 <div class="Normal_text" style="position: absolute; left: 877px; top: 180px; width:24px; height:10px;color:#333333;">
  <span class="xr_tl Normal_text" style="top: -11.77px;color:#333333;font-size:12px;" id="351"></span>
 </div>
 </a>
 <a href="" target="_blank">
 <span class="xr_ar 353" style="left: 997px; top: 165px; width: 60px; height: 19px; background-color: #F8BB00; border: 1px solid #333333;"></span>
 <div class="Normal_text" style="position: absolute; left: 1011px; top: 180px; width:24px; height:10px;color:#333333;">
  <span class="xr_tl Normal_text" style="top: -11.77px;color:#333333;font-size:12px;" id="353"></span> 
 </div>
 </a>
 <a href="" target="_blank">
 <span class="xr_ar 355" style="left: 1131px; top: 165px; width: 60px; height: 19px; background-color: #F8BB00; border: 1px solid #333333;"></span>
 <div class="Normal_text" style="position: absolute; left: 1148px; top: 180px; width:24px; height:10px;color:#333333;">
  <span class="xr_tl Normal_text" style="top: -11.77px;color:#333333;font-size:12px;" id="353"></span>
 </div>
 </a>
 
 <div id="xr_xo0" class="xr_ap" style="left:0; top:0; width:1000px; height:100px; visibility:hidden; z-index:3;">
 <a href="" onclick="return(false);" onmousedown="xr_ppir(this);">
 <span id="referencia"></span>
 </div>
 <div id="xr_xd0"></div>
</div>
</div>

<?

include_once("../inc/php/functions.php");

$getsala = $_GET['sala'];

$sql = "select 
			d.iddevice, s.idtag,
		IF(s.idtag = '".$getsala."','selecionada','') as salaselecionada
		FROM
			tag s
		join tagsala ts on ts.idtagpai = s.idtag
		join device d on d.idtag = ts.idtag
		WHERE
			s.idtag in (345,349,351,353,355)
		and d.status = 'ATIVO'
		and s.status = 'ATIVO'";

$res = d::b()->query($sql);

while ($linha = mysql_fetch_assoc($res))
{
	if ($_GET['elemento'] == 'pressao')
	{
		$sql = "Select 
				dhr.registradoem, if (dhr.registradoem < (NOW() - INTERVAL 5 MINUTE), 'INATIVO', 'ATIVO') as statusleitura,
				ROUND(IF(dhr.pressao > 0 AND dhrr.pressao > 0,
							(dhr.pressao + d.calib_press) - (dhrr.pressao + dr.calib_press),
							0),
						0) AS pressao
				FROM
				laudo.devicehistref dhr
					JOIN
				laudo.device d ON d.iddevice = dhr.iddevice
					JOIN
				laudo.devicehistref dhrr ON dhrr.iddevice = dhr.iddeviceref
					AND dhr.registradoem = dhrr.registradoem
					JOIN
				laudo.device dr ON dr.iddevice = dhrr.iddevice
				WHERE
				d.iddevice = ".$linha['iddevice']."
				ORDER BY dhr.registradoem DESC
				LIMIT 1";

	}else if ($_GET['elemento'] == 'temperatura')
	{
		$sql = "Select
				ROUND(temperatura - calib_temp,0) AS temperatura, dh.registradoem, if (registradoem < (NOW() - INTERVAL 5 MINUTE), 'INATIVO', 'ATIVO') as statusleitura
				FROM
					laudo.devicehist dh
						JOIN
					laudo.device d ON d.mac_address = dh.mac_address
				WHERE
					d.iddevice = ".$linha['iddevice']."
				ORDER BY dh.registradoem DESC
				LIMIT 1";
	}else if ($_GET['elemento'] == 'umidade')
	{ 
		$sql = "Select
				ROUND(umidade - calib_umid,0) AS umidade, dh.registradoem, if (registradoem < (NOW() - INTERVAL 5 MINUTE), 'INATIVO', 'ATIVO') as statusleitura
				FROM
					laudo.devicehist dh
						JOIN
					laudo.device d ON d.mac_address = dh.mac_address
				WHERE
					d.iddevice = ".$linha['iddevice']."
				ORDER BY dh.registradoem DESC
				LIMIT 1";
	}

	$res2 = d::b()->query($sql);

	while ($_row = mysql_fetch_assoc($res2))
	{ 
		$idtagpai = $linha['idtag']; 
		$statusleitura = $_row['statusleitura'];
		$registradoem = $_row['registradoem'];
		$iddevice = $linha['iddevice'];
		$bgcolor='#F8BB00';

		if ($_GET['elemento'] == 'temperatura'){
			$temperatura = $_row['temperatura'];
			$dado = $temperatura.' °C';
		}else if ($_GET['elemento'] == 'pressao'){
			$pressao = $_row['pressao'];
			$dado = $pressao.' Pa';  
		}elseif ($_GET['elemento'] == 'umidade'){ 
			$umidade = $_row['umidade']; 
			$dado = $umidade.' UR';
		}
		
		if ($statusleitura == 'INATIVO'){
			$pressao = '------'; 
			$temperatura = '------'; 
			$umidade = '------';
			$bgcolor='#e74a3b';
		}
		

		if ($linha['salaselecionada'] == "selecionada"){
			$bgcolor='#07fca2';
		}
		
		?>
		<script>
		$('#<?=$idtagpai;?>').html('<?=$dado;?>');
		$('#referencia').html('<small>Referência: tag-<?='1793';?> <?=$referencia;?></small>');
		$('#<?=$idtagpai;?>').prop('title', '<?=$pressao;?> - <?=$registradoem;?> - <?=$_row['iddevice'];?>');
		$('.<?=$idtagpai;?>').css('background', '<?=$bgcolor;?>');
		$('.<?=$idtagpai;?>').parent().closest("a").attr("href", 'https://sislaudo.laudolab.com.br/?_modulo=device&_acao=u&iddevice='+<?=$iddevice;?>);
		</script>
		<?
	}
}
?>
<script>
$('#<?=$idtagpai;?>').html('<?=$dado;?>');

</script>