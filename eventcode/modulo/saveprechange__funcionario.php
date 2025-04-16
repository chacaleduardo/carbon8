<?
$iu = $_SESSION['arrpostbuffer']['1']['u']['pessoa']['idpessoa'] ? 'u' : 'i';

if(!empty($_SESSION['arrpostbuffer']['1'][$iu]['pessoa']['pis']) and $_POST["old_pessoa_pis"] !== $_SESSION['arrpostbuffer']['1'][$iu]['pessoa']['pis']){
	$_SESSION['arrpostbuffer']['1'][$iu]['pessoa']['pis']=str_pad($_SESSION['arrpostbuffer']['1'][$iu]['pessoa']['pis'], 12, "0", STR_PAD_LEFT);
}

$Didpessoaobjeto = $_SESSION['arrpostbuffer']['x']['d']['pessoaobjeto']['idpessoaobjeto'];

if(!empty($Didpessoaobjeto)){
    $sql="select pd.idimmsgconfdest
        from pessoaobjeto p,immsgconfdest d,immsgconfdest pd
        where p.idpessoaobjeto=".$Didpessoaobjeto."
        and d.idobjeto = p.idobjeto
        and d.objeto ='sgsetor'
        and pd.idobjeto = p.idpessoa
        and pd.objeto='pessoa'
        and d.idimmsgconf = pd.idimmsgconf";
    $res=d::b()->query($sql) or die("[prechange] - Erro ao desvincular funcionario do alerta para seu setor: <br> sql=".$sql." <br> ".mysqli_error(d::b()));
    if(!$res){
	d::b()->query("ROLLBACK;");
	die("[prechange] - falha ao desvincular funcionario do alerta para seu setor: " .mysqli_error(d::b()). "<p>SQL: ".$sql);
    }
    $i=9999;
    while($row=mysqli_fetch_assoc($res)){
        $i++;
        $_SESSION['arrpostbuffer'][$i]['d']['immsgconfdest']['idimmsgconfdest']=$row["idimmsgconfdest"];
    }
  
}

$idpessoa = $_POST["_idpessoa"];

foreach($_POST as $k=>$v) {
	if(preg_match("/d_pessoaobjeto_(.*)/", $k, $r)){

        $idpessoaobj = $v;

        if(!empty($idpessoaobj) AND $idpessoaobj AND !empty($idpessoa)){
            $qr = "SELECT 
                        1
                    FROM 
                        pessoaobjeto
                    WHERE 
                        tipoobjeto = 'rhtipoevento' AND
                        idobjeto = 21 AND
                        idpessoaobjeto = ".$idpessoaobj;
            $rs = d::b()->query($qr);
            if(mysqli_num_rows($rs) > 0){
                $sql = "SELECT 
                        valor
                    FROM 
                        rhtipoevento
                    WHERE 
                        idrhtipoevento = 21";
                $res = d::b()->query($sql);
                $row = mysqli_fetch_assoc($res);

                $qr1 = "SELECT 
                        idrheventopessoa, valor
                    FROM 
                        rheventopessoa
                    WHERE 
                        idrhtipoevento = 21 AND
                        status = 'ATIVO' AND
                        idpessoa = ".$idpessoa;
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

    $idpessoa = $_POST["_x_i_pessoaobjeto_idpessoa"];
    $_idpessoa = $_POST["_idpessoa"];

    if(preg_match("/_x_i_pessoaobjeto_idpessoa/", $k, $r)){

        if(!empty($idpessoa)){
            $sqlidade = "SELECT 
                    nasc
                FROM 
                    pessoa
                WHERE 
                    idpessoa = ".$idpessoa;
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
                    AND idpessoa = ".$_idpessoa;
            $rs1 = d::b()->query($qr1);
            $rw1 = mysqli_fetch_assoc($rs1);

            $newVal =  $rw1["valor"] + $row["valor"];
            if($newVal <= 0){
                d::b()->query("DELETE FROM rheventopessoa WHERE idrheventopessoa = ".$rw1["idrheventopessoa"]);
            }else{
                d::b()->query("UPDATE rheventopessoa SET valor = ".$newVal." WHERE idrheventopessoa = ".$rw1["idrheventopessoa"]);
            }
        }
    }

    $_idpessoa = $_POST["_idpessoa"];

    if(preg_match("/_x_d_pessoaobjeto_/", $k, $r)){

        if(!empty($_idpessoa)){

            $sql = "SELECT 
                    idobjeto,idpessoa
                FROM 
                    pessoaobjeto
                WHERE 
                    idpessoaobjeto = ".$_POST['_x_d_pessoaobjeto_idpessoaobjeto'];
            $res = d::b()->query($sql);
            $row = mysqli_fetch_assoc($res);

            if($row['idobjeto'] == 486 && $row['idpessoa'] != $_idpessoa){
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
                        AND idpessoa = ".$_idpessoa;
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
    
}
 
 
/*
*JLAL - 17-08-20 **Inserção na tb colaboradorhistorico toda vez que ocorrer alteração do tipo DELETE na aba de AREA,DEPARTAMENTO,SETOR**
*/

if ($_POST["_1_u_pessoa_idpessoa"]){
	$contrato = $_POST["_1_".$iu ."_pessoa_contrato"];
    $salario = $_POST["_1_".$iu ."_pessoa_salario"];
    $insalubridade = $_POST["_1_".$iu ."_pessoa_insalubridade"];
    $unimedmens = $_POST["_1_".$iu ."_pessoa_unimedmens"];
    $emprestimo = $_POST["_1_".$iu ."_pessoa_emprestimo"];
    $parcela = $_POST["_1_".$iu ."_pessoa_parcela"];
    $parcelas = $_POST["_1_".$iu ."_pessoa_parcelas"];
    $qtddependente = $_POST["_1_".$iu ."_pessoa_qtddependente"];
    $vlrdependente = $_POST["_1_".$iu ."_pessoa_vlrdependente"];
    $tipopagamento = $_POST["_1_".$iu ."_pessoa_tipopagamento"];
    $observacaore = $_POST["_1_".$iu ."_pessoa_observacaore"];
    $horaini = $_POST["_101_".$iu ."_pessoahorario_horaini"];
    $horafim = $_POST["_101_".$iu ."_pessoahorario_horafim"];
    $periodo = $_POST["_101_".$iu ."_pessoahorario_periodo"];
    $idsgcargo = $_POST["_1_".$iu ."_pessoa_idsgcargo"];
    
	$sql = "SELECT contrato,salario,insalubridade,unimedmens,emprestimo,
    parcela,parcelas,qtddependente,vlrdependente,tipopagamento,observacaore,horaini,horafim,periodo,cargo 
    FROM colaboradorhistorico
    WHERE idpessoa=".$_POST["_1_u_pessoa_idpessoa"]." AND aba='RH' ORDER BY idcolaboradorhistorico DESC limit 1";
	$res =  d::b()->query($sql) or die("Falha ao pesquisar pessoaobjeto: " . mysqli_error() . "<p>SQL: $sql");
    $qtresult = mysqli_num_rows ( $res );
    if($qtresult > 0){
        while ($row = mysqli_fetch_assoc($res)){
            
            if(($row['contrato'] != $contrato)
                || ($row['salario'] != $salario)
                || ($row['insalubridade'] != $insalubridade)
                || ($row['unimedmens'] != $unimedmens)
                || ($row['emprestimo'] != $emprestimo)
                || ($row['parcela'] != $parcela)
                || ($row['parcelas'] != $parcelas)
                || ($row['qtddependente'] != $qtddependente)
                || ($row['vlrdependente'] != $vlrdependente)
                || ($row['tipopagamento'] != $tipopagamento)
                || ($row['observacaore'] != $observacaore)
                || ($row['horaini'] != $horaini)
                || ($row['horafim'] != $horafim)
                || ($row['periodo'] != $periodo)
                || ($row['cargo'] != $idsgcargo)){
                    $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['idpessoa'] 	= $_POST["_1_u_pessoa_idpessoa"];
                    $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['idempresa'] 	= $_SESSION["SESSAO"]["IDEMPRESA"];
                    $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['aba'] 	= 'RH';
                    $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['contrato'] 	=           $contrato;
                    $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['salario'] 	=           $salario;
                    $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['insalubridade'] 	=   $insalubridade;
                    $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['unimedmens'] 	=       $unimedmens;
                    $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['emprestimo'] 	=       $emprestimo;
                    $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['parcela'] 	=           $parcela;
                    $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['parcelas'] 	=           $parcelas;
                    $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['qtddependente'] 	=   $qtddependente;
                    $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['vlrdependente'] 	=   $vlrdependente;
                    $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['tipopagamento'] 	=   $tipopagamento;
                    $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['observacaore'] 	=       $observacaore;
                    $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['horaini'] 	=           $horaini;
                    $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['horafim'] 	=           $horafim;
                    $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['periodo'] 	=           $periodo;
                    $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['horafim'] 	=           $horafim;
                    $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['acao'] 	= 'u';
                    $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['cargo'] 	= $idsgcargo;
                    $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['criadopor'] 	= $_SESSION["SESSAO"]["USUARIO"];
                    $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['criadoem'] 	= date("d/m/Y H:i:s");
                    $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['alteradopor'] 	= $_SESSION["SESSAO"]["USUARIO"];
                    $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['alteradoem'] 	= date("d/m/Y H:i:s");
                }
        }
    }else{
        $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['idpessoa'] 	= $_POST["_1_u_pessoa_idpessoa"];
        $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['idempresa'] 	= $_SESSION["SESSAO"]["IDEMPRESA"];
        $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['aba'] 	= 'RH';
        $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['contrato'] 	=           $contrato;
        $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['salario'] 	=           $salario;
        $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['insalubridade'] 	=   $insalubridade;
        $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['unimedmens'] 	=       $unimedmens;
        $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['emprestimo'] 	=       $emprestimo;
        $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['parcela'] 	=           $parcela;
        $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['parcelas'] 	=           $parcelas;
        $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['qtddependente'] 	=   $qtddependente;
        $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['vlrdependente'] 	=   $vlrdependente;
        $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['tipopagamento'] 	=   $tipopagamento;
        $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['observacaore'] 	=       $observacaore;
        $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['horaini'] 	=           $horaini;
        $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['horafim'] 	=           $horafim;
        $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['periodo'] 	=           $periodo;
        $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['horafim'] 	=           $horafim;
        $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['acao'] 	= 'i';
        $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['cargo'] 	= $idsgcargo;
        $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['criadopor'] 	= $_SESSION["SESSAO"]["USUARIO"];
        $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['criadoem'] 	= date("d/m/Y H:i:s");
        $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['alteradopor'] 	= $_SESSION["SESSAO"]["USUARIO"];
        $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['alteradoem'] 	= date("d/m/Y H:i:s");
    }	
retarraytabdef('colaboradorhistorico');	
montatabdef();	
} 




if($_POST["_x_i_pessoaobjeto_idpessoa"]){
    $idpessoa 	= $_POST["_x_i_pessoaobjeto_idpessoa"];
    $idobjeto 	= $_POST["_x_i_pessoaobjeto_idobjeto"];
    $objeto 	= $_POST["_x_i_pessoaobjeto_tipoobjeto"];
        
        $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['idpessoa'] 	= $idpessoa;
        $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['idempresa'] 	= $_SESSION["SESSAO"]["IDEMPRESA"];
        $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['aba'] 	= 'FUNCIONARIO';
        $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['acao'] 	= 'i';
        $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['objeto'] 	= $idobjeto;
        $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['tipoobjeto'] 	= $objeto ;
        $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['criadopor'] 	= $_SESSION["SESSAO"]["USUARIO"];
        $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['criadoem'] 	= date("d/m/Y H:i:s");
        $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['alteradopor'] 	= $_SESSION["SESSAO"]["USUARIO"];
        $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['alteradoem'] 	= date("d/m/Y H:i:s");
    
        montatabdef();
        
    }else if ($_POST["_x_d_pessoaobjeto_idpessoaobjeto"]){
        
        $sql = "select po.idpessoa, po.idempresa, po.idobjeto,po.tipoobjeto
                         from pessoaobjeto po
                     where idpessoaobjeto =".$_POST["_x_d_pessoaobjeto_idpessoaobjeto"];
        $res =  d::b()->query($sql) or die("Falha ao pesquisar pessoaobjeto: " . mysqli_error() . "<p>SQL: $sql");
    
        while ($row2 = mysqli_fetch_assoc($res)){
            $idpessoa 	= $row2['idpessoa'];
            $idempresa 	= $row2['idempresa'];
            $idobjeto 	= $row2['idobjeto'];
            $objeto 	= $row2['tipoobjeto'];
        }
        
            $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['idpessoa'] 	= $idpessoa;
            $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['idempresa'] 	= $_SESSION["SESSAO"]["IDEMPRESA"];
            $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['aba'] 	= 'FUNCIONARIO';
            $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['acao'] 	= 'd';
            $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['objeto'] 	= $idobjeto;
            $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['tipoobjeto'] 	=  $objeto ;
            $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['criadopor'] 	= $_SESSION["SESSAO"]["USUARIO"];
            $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['criadoem'] 	= date("d/m/Y H:i:s");
            $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['alteradopor'] 	= $_SESSION["SESSAO"]["USUARIO"];
            $_SESSION['arrpostbuffer']['100']['i']['colaboradorhistorico']['alteradoem'] 	= date("d/m/Y H:i:s");
            
    montatabdef();
    }


if(!empty($_SESSION['arrpostbuffer']['mail']['u']['pessoa']['idpessoa'])){
    $_email = $_SESSION['arrpostbuffer']['mail']['u']['pessoa']['webmailemail'];
    $_user = $_SESSION['arrpostbuffer']['mail']['u']['pessoa']['webmailusuario'];

    if(!filter_var($_email, FILTER_VALIDATE_EMAIL)){
        cbSetPostHeader("0","alerta");
        echo "E-mail Inválido";
        die;
    }

    if(empty($_user)){
        cbSetPostHeader("0","alerta");
        echo "Usuário de e-mail não pode ser vazio";
        die;
    }
}

if(empty($_POST['username_old']) && !empty($_SESSION['arrpostbuffer']['1']['u']['pessoa']['usuario'])){
    $_SESSION['arrpostbuffer']['1']['u']['pessoa']['usuario'] = strtolower(tirarAcentos(trim(str_replace(" ", "", $_SESSION['arrpostbuffer']['1']['u']['pessoa']['usuario']))));
}

if(empty($_SESSION['arrpostbuffer']['1']['u']['pessoa']['webmailemail']) && !empty($_SESSION['arrpostbuffer']['1']['u']['pessoa']['usuario']) && !empty($_SESSION['arrpostbuffer']['1']['u']['pessoa']['idempresa'])) {
    $idempresa = $_SESSION['arrpostbuffer']['1']['u']['pessoa']['idempresa'];
    $usuario = $_SESSION['arrpostbuffer']['1']['u']['pessoa']['usuario'];
    $sql = "SELECT dominio FROM dominio WHERE idempresa = $idempresa";
    $res = d::b()->query($sql);
    $row = mysqli_fetch_assoc($res);
    $usuario = $usuario.'@'.$row['dominio'];
    $_SESSION['arrpostbuffer']['1']['u']['pessoa']['webmailemail'] = $usuario;
}
?>
