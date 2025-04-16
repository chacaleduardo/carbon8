<?
header('Cache-Control: no-cache, must-revalidate');
header('Content-Type: application/json');

include_once("functions.php"); 

if($_REQUEST['status'] == 'atualizar'){
    $sql = "select d.iddevice, dv.versao as versaoHist, d.versao as versaoDev, dv.caminho, d.ip_hostname from devicefirm dv
    join device d on (d.modelo=dv.modelo)
    where d.mac_address='".$_REQUEST['mac_address']."'
    order by dv.versao desc";
    $res = d::b()->query($sql);
    $row = mysqli_fetch_assoc($res);    
    
    $atualizar = $row['versaoHist'] != $row['versaoDev']?1:0;
    if($atualizar == 1){
        $caminho['dados']['caminho'] = ''.$row['caminho'].'';
        $ip = $row['ip'];
    
        $versao = $row['versaoHist'];
        $sql = "update device set versao='".$versao."', atualizadoem = NOW() where iddevice = ".$row['iddevice'];
    
        $rs = d::b()->query($sql) or die($msgerro.": ". mysqli_error(d::b()));
        
    }else{
        $caminho['dados']['caminho'] = '';
            
    }
    echo json_encode($caminho);
}

?>
