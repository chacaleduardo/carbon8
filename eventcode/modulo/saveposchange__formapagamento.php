<?
require_once(__DIR__."/../../form/controllers/fluxo_controller.php");

$iu = $_SESSION['arrpostbuffer']['1']['u']['formapagamento']['idformapagamento'] ? 'u' : 'i';
$saldocontapagar = tratanumero($_POST["x_contasaldo"]);

$lastinsert = $_SESSION['arrscriptsql']['1']['formapagamento']['insertid'];
$idformapagamento = $_SESSION['arrpostbuffer']['1']['u']['formapagamento']['idformapagamento'];

$idagencia = $_SESSION['arrpostbuffer']['1'][$iu]['formapagamento']['idagencia'];

$criadoem = date('Y-m-d');

if(!empty($idagencia)){
    $sqla="select * from agencia c  where c.idagencia=".$idagencia." limit 1";
    $resa=d::b()->query($sqla);
    $rowa=mysqli_fetch_assoc($resa);
    if(!empty($rowa['criacao'])){
        $criadoem = $rowa['criacao'];
    }
}



if(!empty($saldocontapagar) AND !empty($lastinsert) AND !empty($idagencia) AND $iu == 'i'){

    if(empty($saldocontapagar)){$saldocontapagar='0.00';}

    $sqlf="select * from contapagar c  where c.idagencia=".$idagencia." limit 1";
    $resf=d::b()->query($sqlf);
    $qtdf=mysqli_num_rows($resf);
    if($qtdf<1){

      

        $_idempresa = traduzid('agencia', 'idagencia', 'idempresa', $idagencia);

            //LTM - 20-04-2021: Retorna o Idfluxo ContaPagar
            $idfluxostatus = FluxoController::getIdFluxoStatus('contapagar', 'QUITADO');

            $sql = "INSERT INTO `laudo`.`contapagar` 
                (`idempresa`,`datapagto`,`datareceb`,`valor`,`parcelas`,`parcela`,`tipo`,`tipoespecifico`,`status`, idfluxostatus, 
                `idagencia`,`idformapagamento`,`saldo`,`quitadoem`,`saldook`,quitadoemseg,`criadoem`,`alteradoem`,`criadopor`,`alteradopor`)
                VALUES
                (".$_idempresa.",'".$criadoem."','".$criadoem."',0,1,1,'D','NORMAL','QUITADO', '$idfluxostatus',".$idagencia.",".$lastinsert.",".$saldocontapagar.",now(),'Y',1,now(),now(),'sislaudo','sislaudo')";

            $res=d::b()->query($sql) or die("Erro ao inserir contapagar: <br>".mysqli_error(d::b())." sql=".$sql);
    }
    
}

if(!empty($saldocontapagar) AND $saldocontapagar != 'NULL' AND !empty($idformapagamento) AND !empty($idagencia) AND $iu == 'u'){

    $sqlf="select * from contapagar c  where c.idagencia=".$idagencia." limit 1";
    $resf=d::b()->query($sqlf);
    $qtdf=mysqli_num_rows($resf);
    if($qtdf<1){
        //LTM - 20-04-2021: Retorna o Idfluxo ContaPagar
        $idfluxostatus = FluxoController::getIdFluxoStatus('contapagar', 'QUITADO');

        $_idempresa = traduzid('agencia', 'idagencia', 'idempresa', $idagencia);

        $sql = "INSERT INTO `laudo`.`contapagar` 
            (`idempresa`,`datapagto`,`datareceb`,`valor`,`parcelas`,`parcela`,`tipo`,`tipoespecifico`,`status`,  idfluxostatus, 
            `idagencia`,`idformapagamento`,`saldo`,`quitadoem`,`saldook`,quitadoemseg,`criadoem`,`alteradoem`,`criadopor`,`alteradopor`)
            VALUES
            (".$_idempresa.",'".$criadoem."','".$criadoem."',0,1,1,'D','NORMAL','QUITADO', '$idfluxostatus',".$idagencia.",".$idformapagamento.",".$saldocontapagar.",now(),'Y',1,now(),now(),'sislaudo','sislaudo')";

        $res=d::b()->query($sql) or die("Erro ao inserir contapagar: <br>".mysqli_error(d::b())." sql=".$sql);

        //LTM - 20-04-2021: Retorna o Idfluxo ContaPagar
        $idcontapagar=mysqli_insert_id(d::b());
        FluxoController::inserirFluxoStatusHist('contapagar', $idcontapagar, $idfluxostatus, 'PENDENTE');
    }

}
?>