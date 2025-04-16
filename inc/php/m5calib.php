<?
header('Cache-Control: no-cache, must-revalidate');
header('Content-Type: application/json');

include_once("functions.php"); 

$sql = "SELECT d.pino, dt.tipo, dp.refsubida, dp.refdescida, dp.sensorsubida, dp.sensordescida, d.nomesensor, db.iddevicesensorbloco
        FROM devicesensorbloco db
        JOIN devicesensor d ON d.iddevicesensor = db.iddevicesensor
        JOIN devicesensortipo dt ON db.tipo = dt.tipo
        JOIN devicesensorcalib dp ON db.iddevicesensorbloco = dp. iddevicesensorbloco
        WHERE d.iddevice = '".$_REQUEST['iddevice']."' and db.status = 'ATIVO'
        ORDER BY d.nomesensor, dt.tipo, db.iddevicesensorbloco
 ";  

$res = d::b()->query($sql);

$i = 0;
$j = 0;
$k = 0;

/*while ($_row = mysql_fetch_assoc($res))
{   
    if($blocoanterior != $_row['iddevicesensorbloco'] || $nomeanterior != $_row['nomesensor']){
        $i = 0;
    }
	$pontos[$_row['nomesensor']][$_row['rotulo']]['refsubida'][$i] = strval(round($_row['refsubida'],2));
	$pontos[$_row['nomesensor']][$_row['rotulo']]['refdescida'][$i] = strval(round($_row['refdescida'],2));
    $pontos[$_row['nomesensor']][$_row['rotulo']]['sensorsubida'][$i] = strval(round($_row['sensorsubida'],2));
    $pontos[$_row['nomesensor']][$_row['rotulo']]['sensordescida'][$i] = strval(round($_row['sensordescida'],2));
    $blocoanterior = $_row['iddevicesensorbloco'];
    $nomeanterior = $_row['nomesensor'];
    $i++;
}

$pontos['qtdpontos'] = $i;*/

while ($_row = mysql_fetch_assoc($res))
{
    if ($sensoranterior != $_row['nomesensor']){
        $i++;
        $k = 0;
    }

    $pontos[$i]['sensor'] = $_row['nomesensor'];
    $pontos[$i]['pino'] = $_row['pino'];

    if($blocoanterior != $_row['iddevicesensorbloco']){
        $j = 0;
    }

    if($tipoanterior != $_row['tipo']){
        $k++;
    }

    $pontos[$i]['calib'][$k]['tipo'] = $_row['tipo'];

    $pontos[$i]['calib'][$k]['refsubida'][$j] = strval(round($_row['refsubida'],2));
    $pontos[$i]['calib'][$k]['refdescida'][$j] = strval(round($_row['refdescida'],2));
    $pontos[$i]['calib'][$k]['sensorsubida'][$j] = strval(round($_row['sensorsubida'],2));
    $pontos[$i]['calib'][$k]['sensordescida'][$j] = strval(round($_row['sensordescida'],2));

    $blocoanterior = $_row['iddevicesensorbloco'];
    $sensoranterior = $_row['nomesensor'];
    $tipoanterior = $_row['tipo'];

    $j++;
}

echo json_encode($pontos, JSON_UNESCAPED_UNICODE);

?>
