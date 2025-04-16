<?
header('Cache-Control: no-cache, must-revalidate');
header('Content-Type: application/json');

include_once("functions.php"); 

$sql = "select d.ip_hostname from device d 
join device do on do.iddeviceref = d.iddevice 
where do.mac_address ='".$_REQUEST['mac_address']."'
 ";  
 
$res = d::b()->query($sql);

while ($_row = mysql_fetch_assoc($res)){
	$leitura['dados']['ip_hostname'] = strval($_row['ip_hostname']);
}

$sql = "select iddevice, ip_hostname, mac_address, if(calib_press = '', 0,calib_press) as calib_press, calib_umid, calib_temp from device where mac_address = '".$_REQUEST['mac_address']."'";

$res = d::b()->query($sql);

while ($_row = mysql_fetch_assoc($res)){
    $leitura['dados']['iddevice'] = strval($_row['iddevice']);
	$leitura['dados']['calibpress'] = strval($_row['calib_press']);
	$leitura['dados']['calibumid'] = strval($_row['calib_umid']);
	$leitura['dados']['calibtemp'] = strval($_row['calib_temp']);	
}


   $sqlu="select nomeciclo, descricao as nomedevice, numpurgas as numeropurgas, minpurga, maxpurga, minciclo, minciclo2, maxciclo, maxciclo2, tempociclo, tempociclo2, dc.modelo as tipociclo 
		from 
		deviceciclo dc 
		join deviceobj do on do.objeto = dc.iddeviceciclo and do.tipoobjeto = 'deviceciclo'
		join device d on d.iddevice = do.iddevice
		where
		d.mac_address = '".$_REQUEST['mac_address']."' order by nomeciclo";
// send the result now
$rese = d::b()->query($sqlu);
$i = 0;
while ($_row = mysql_fetch_assoc($rese)){
 	$leitura['ciclo'.$i]['nomeciclo'] = strval($_row['nomeciclo']);
	$leitura['ciclo'.$i]['numeropurgas'] = strval($_row['numeropurgas']);
	$leitura['ciclo'.$i]['minpurga'] = strval($_row['minpurga']);
	$leitura['ciclo'.$i]['maxpurga'] = strval($_row['maxpurga']);
	$leitura['ciclo'.$i]['minciclo'] = strval($_row['minciclo']);
	$leitura['ciclo'.$i]['minciclo2'] = strval($_row['minciclo2']);
	$leitura['ciclo'.$i]['maxciclo'] = strval($_row['maxciclo']);
	$leitura['ciclo'.$i]['maxciclo2'] = strval($_row['maxciclo2']);
	$leitura['ciclo'.$i]['tempociclo'] = strval($_row['tempociclo']);
	$leitura['ciclo'.$i]['tempociclo2'] = strval($_row['tempociclo2']);
	$leitura['ciclo'.$i]['tipociclo'] = strval($_row['tipociclo']);
	$i++;
	
	
}
echo json_encode($leitura, JSON_UNESCAPED_UNICODE);

?>
