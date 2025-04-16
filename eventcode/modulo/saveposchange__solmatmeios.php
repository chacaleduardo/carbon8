<?

$lastid = mysql_insert_id();


$arrInsProd=array();
foreach($_POST as $k=>$v) {
	if(preg_match("/duplicar_(\d*)_(.*)/", $k, $res)){
		$arrInsProd[$res[1]][$res[2]]=$v;
	}
}
if(!empty($arrInsProd)){
    
    foreach($arrInsProd as $k =>$v){
        if(!empty($v['idprodserv'])){
            $sql= "INSERT INTO `laudo`.`solmatitem`
            (`idempresa`,
            `idsolmat`,
            `qtdc`,
            `idprodserv`,
            `descr`,
            `criadoem`,
            `criadopor`,
            `alteradoem`,
            `alteradopor`,
            `obs`,
            `un`)
            VALUES
            (".$_SESSION['SESSAO']['IDEMPRESA'].",
            ".$lastid.",
            ".$v['qtdc'].",
            ".$v['idprodserv'].",
            '".$v['descr']."',
            now(),
            '".$_SESSION['SESSAO']['USUARIO']."',
            now(),
            '".$_SESSION['SESSAO']['USUARIO']."',
            '".$v['obs']."','".$v['un']."')";
            d::b()->query($sql);
        }else{
            $sql= "INSERT INTO `laudo`.`solmatitem`
            (`idempresa`,
            `idsolmat`,
            `qtdc`,
            `descr`,
            `criadoem`,
            `criadopor`,
            `alteradoem`,
            `alteradopor`,
            `obs`,
            `un`)
            VALUES
            (".$_SESSION['SESSAO']['IDEMPRESA'].",
            ".$lastid.",
            ".$v['qtdc'].",
            '".$v['descr']."',
            now(),
            '".$_SESSION['SESSAO']['USUARIO']."',
            now(),
            '".$_SESSION['SESSAO']['USUARIO']."',
            '".$v['obs']."','".$v['un']."')";
            d::b()->query($sql);
        }
       
    }
}

?>