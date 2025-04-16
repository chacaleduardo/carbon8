<?

$iu = $_SESSION['arrpostbuffer']['1']['i']['pessoa']['idtipopessoa'] ? 'i' : 'u';

$Iidsgsetor = $_SESSION['arrpostbuffer']['x']['i']['pessoaobjeto']['idobjeto'];
$Iidpessoa = $_SESSION['arrpostbuffer']['x']['i']['pessoaobjeto']['idpessoa'];



//inserir a pessoa para todas as unidades
if($iu=='i' and !empty($_SESSION["_pkid"])){
    
    $sql="INSERT INTO unidadeobjeto
            (idempresa,idunidade,idobjeto,tipoobjeto,criadopor,criadoem)
        (select idempresa,idunidade,".$_SESSION["_pkid"].",'PESSOA','".$_SESSION["SESSAO"]["USUARIO"]."',sysdate() 
            from unidade where status='ATIVO')";
    $res=d::b()->query($sql) or die("Erro atualizar unidades do funcionario: <br>".mysqli_error(d::b())." sql=".$sql);
    
    
    $sq2="INSERT INTO objempresa
            (idempresa,empresa,idobjeto,objeto,criadopor,criadoem,alteradopor,alteradoem)
                values
            (".cb::idempresa().",".cb::idempresa().",".$_SESSION["_pkid"].",'PESSOA','".$_SESSION["SESSAO"]["USUARIO"]."',sysdate(),'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate())";
    $res2=d::b()->query($sq2) or die("Erro relacionar funcionario a empresa: <br>".mysqli_error(d::b())." sql=".$sq2);

    $sqlcontrep = "SELECT idrep FROM rep where 1 ".getidempresa('idempresa','pessoa')." and status = 'ATIVO' ";
    $rescontrep=d::b()->query($sqlcontrep) or die("Erro buscar quantidade de reps da empresa: <br>".mysqli_error(d::b())." sql=".$sqlcontrep);
    
    while($rowcontrep = mysqli_fetch_assoc($rescontrep)){
        $sql3="INSERT INTO reppessoa
            (idempresa,idrep,idpessoa,criadopor,criadoem,alteradopor,alteradoem)
            VALUES (".cb::idempresa().",".$rowcontrep["idrep"].",".$_SESSION["_pkid"].",'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate(),'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate())";
        $res3=d::b()->query($sql3) or die("Erro inserir funcionário em reppessoa: <br>".mysqli_error(d::b())." sql=".$sql3);
    }

}

if(!empty($Iidsgsetor) and !empty($Iidpessoa)){
    $sql="INSERT INTO immsgconfdest
            (idempresa,idimmsgconf,idobjeto,objeto,status,criadopor,criadoem,alteradopor,alteradoem)
            (select idempresa,idimmsgconf,".$Iidpessoa.",'pessoa','ATIVO','".$_SESSION["SESSAO"]["USUARIO"]."',sysdate(),'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate()
                from immsgconfdest 
            where idobjeto=".$Iidsgsetor." 
            and objeto='sgsetor')";
    $res=d::b()->query($sql) or die("Erro ao vincular funcionario ao alerta para seu setor: <br> sql=".$sql." <br> ".mysqli_error(d::b()));
    if(!$res){
	d::b()->query("ROLLBACK;");
	die("falha 1-vincular funcionario ao alerta para seu setor: " .mysqli_error(d::b()). "<p>SQL: ".$sql);
    }  
}

$tipoobjeto = $_SESSION['arrpostbuffer']['x']['i']['pessoaobjeto']['tipoobjeto'];
$idobjeto = $_SESSION['arrpostbuffer']['x']['i']['pessoaobjeto']['idobjeto'];

$idpessoa = $_POST["_idpessoa"];

// Tipo Evento UNIODONTO
if(!empty($tipoobjeto) AND $tipoobjeto == 'rhtipoevento' AND !empty($idobjeto) AND ($idobjeto == 21 || $idobjeto == 20) AND !empty($idpessoa)){
    $sql = "SELECT 
                valor
            FROM 
                rhtipoevento
            WHERE 
                idrhtipoevento = ".$idobjeto;
    $res = d::b()->query($sql);
    $row = mysqli_fetch_assoc($res);

    $qr = "SELECT 
                idrheventopessoa, valor
            FROM 
                rheventopessoa
            WHERE 
                idrhtipoevento = ".$idobjeto." AND
                status = 'ATIVO' AND
                idpessoa = ".$idpessoa;
    $rs = d::b()->query($qr);

    if(mysqli_num_rows($rs) > 0){

        $rw = mysqli_fetch_assoc($rs);
        $newVal = $rw["valor"] + $row["valor"];
        d::b()->query("UPDATE rheventopessoa SET valor = ".$newVal." WHERE idrheventopessoa = ".$rw["idrheventopessoa"]);

    }else{
        d::b()->query("INSERT INTO `laudo`.`rheventopessoa` (`idempresa`,`idpessoa`,`idrhtipoevento`,`valor`,`criadopor`,`criadoem`,`alteradopor`,`alteradoem`) 
        VALUES (".$_SESSION["SESSAO"]["IDEMPRESA"].",".$idpessoa.",".$idobjeto.",".$row["valor"].",'sislaudo',now(),'sislaudo',now());");
    }
}
if(!empty($tipoobjeto) AND $tipoobjeto == 'rhtipoevento' AND !empty($idobjeto) AND $idobjeto == 486 AND !empty($idpessoa)){
    if(!empty($idpessoa)){

        $sql = "SELECT 
                idobjeto,idpessoa
            FROM 
                pessoaobjeto
            WHERE 
                idpessoaobjeto = ".$_POST['_x_d_pessoaobjeto_idpessoaobjeto'];
        $res = d::b()->query($sql);
        $row = mysqli_fetch_assoc($res);

        if($row['idobjeto'] == 486 && $row['idpessoa'] != $idpessoa){
            $sqlidade = "SELECT 
                    nasc
                FROM 
                    pessoa
                WHERE 
                    idpessoa = ".$row['idpessoa'];
            $residade = d::b()->query($sqlidade);
            $rowidade = mysqli_fetch_assoc($residade);
            $dataNascimento = $rowidade["nasc"];

            $dataAtual = new DateTime();
            $dataNascimento = new DateTime($dataNascimento);
            $idade = $dataAtual->diff($dataNascimento);
            $idade = $idade->y;

            $sql = "SELECT 
                    percentual as valor
                FROM 
                    rhtipoeventobc
                WHERE 
                    idrhtipoevento = 486 -- //486 = 486 em produção
                    and $idade BETWEEN valinicio AND valfim";
            $res = d::b()->query($sql);
            $row = mysqli_fetch_assoc($res);

            if(empty($row)){
                $sql = "SELECT 
                        valor
                    FROM 
                        rhtipoevento
                    WHERE 
                        idrhtipoevento = 486";
                $res = d::b()->query($sql);
                $row = mysqli_fetch_assoc($res);
            }

            $qr1 = "SELECT 
                    idrheventopessoa, valor
                FROM 
                    rheventopessoa
                WHERE 
                    idrhtipoevento = 486 -- //486 = 486 em produção 
                    AND status = 'ATIVO'
                    AND idpessoa = ".$idpessoa;
            $rs1 = d::b()->query($qr1);
            $rw1 = mysqli_fetch_assoc($rs1);

            $newVal =  $rw1["valor"] - $row["valor"];
            if($newVal <= 0){
                d::b()->query("DELETE FROM rheventopessoa WHERE idrheventopessoa = ".$rw1["idrheventopessoa"]);
            }else{
                d::b()->query("UPDATE rheventopessoa SET valor = ".$newVal." WHERE idrheventopessoa = ".$rw1["idrheventopessoa"]);
            }
        }
    }
}

//------------ Setar a empresa para os tipos funcionário(1) ou Representante(15) na tabela objempresa para que apareça no evento ----------//
//Lidiane (07/05/2020)
if(!empty($_SESSION['arrpostbuffer']['1'][$iu]['pessoa']['idpessoa']))
{
	getInsertUpdateObjempresa($_SESSION['arrpostbuffer']['1'][$iu]['pessoa']['idpessoa']);
}