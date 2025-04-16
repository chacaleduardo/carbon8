<?
require_once("../inc/php/functions.php");

$jwt = validaTokenReduzido();

if($jwt["sucesso"] !== true){
    header("HTTP/1.1 401 Unauthorized");
    die;
}


if(!empty($_POST['eventos'])){

    $eventos = implode(",",$_POST['eventos']);
    $sql = "UPDATE
                evento e 
                join fluxostatus fse on fse.idfluxostatus = e.idfluxostatus
                join carbonnovo._status se on se.idstatus = fse.idstatus

                join fluxostatuspessoa fsp on fsp.idmodulo = e.idevento and fsp.modulo = 'evento' 

                join fluxostatus fs on fs.idfluxostatus = fsp.idfluxostatus
                join carbonnovo._status s on s.idstatus = fs.idstatus 

                SET
                fsp.alteradopor = '".$_SESSION['SESSAO']["USUARIO"]."',
                fsp.alteradoem=now(),
                fsp.oculto = 1,
                fsp.visualizado = 0,
                fsp.idfluxostatus = (select idfluxostatus from fluxostatus fn join carbonnovo._status sn on sn.idstatus = fn.idstatus where fn.idfluxo = fse.idfluxo and sn.statustipo = 'OCULTO' limit 1)
                where
                (se.tipobotao = 'FIM' or se.statustipo = 'CANCELADO')
                and idevento in (".$eventos.")
                and fsp.idobjeto = ".$_SESSION['SESSAO']["IDPESSOA"]." and fsp.tipoobjeto = 'pessoa'
                and fsp.oculto = 0;";
            $res = d::b()->query($sql);
            if(!$res){
                die(json_encode(["erro"=>"erro"]));
            }
            $sql1 = "SELECT e.idevento,e.evento from
                    evento e 
                    join fluxostatus fse on fse.idfluxostatus = e.idfluxostatus
                    join carbonnovo._status se on se.idstatus = fse.idstatus
                    join fluxostatuspessoa fsp on fsp.idmodulo = e.idevento and fsp.modulo = 'evento' 
                    join fluxostatus fs on fs.idfluxostatus = fsp.idfluxostatus
                    join carbonnovo._status s on s.idstatus = fs.idstatus 
                    where
                    (se.tipobotao = 'FIM' or se.statustipo = 'CANCELADO')
                and idevento in (".$eventos.") 
                    and fsp.idobjeto = ".$_SESSION['SESSAO']["IDPESSOA"]." and fsp.tipoobjeto = 'pessoa'
                    and fsp.oculto = 1;";
            $res1 = d::b()->query($sql1);
            if(!$res1){
                die(json_encode(["erro"=>"erro"]));
            }
            while($row = mysqli_fetch_assoc($res1)){
                $response[] = $row;
            }
            echo json_encode($response);
}


?>