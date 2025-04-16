<?
//print_r($_POST); die;
$idtipopessoa = $_SESSION['arrpostbuffer']['1']['i']['pessoa']['idtipopessoa'];
$iu = $_SESSION['arrpostbuffer']['1']['i']['pessoa']['idtipopessoa'] ? 'i' : 'u';

//echo($iu); die;

/*
 * Se for um representante criando um cliente 
 * Este cliente deve ser vinculado ao representante
 */

if(($_SESSION["SESSAO"]["IDTIPOPESSOA"]==15 or $_SESSION["SESSAO"]["IDTIPOPESSOA"]==16) and $iu == 'i' and $idtipopessoa == '2'){

    $idRepresentacao ="SELECT 
        pc.idpessoa as 'idrepresentação'
    FROM
        pessoacontato pc
            LEFT JOIN
        pessoa p ON (p.idpessoa = pc.idpessoa)
    WHERE
        pc.idcontato = ".$_SESSION["SESSAO"]["IDPESSOA"]."
            AND p.status = 'ATIVO'
            limit 1";
    $idRep = d::b()->query($idRepresentacao) or die("A busca pelo id da Representação Falhou: " . mysqli_error(d::b()) . "<p>SQL:".$sqlgestor);
    $idr = mysqli_fetch_assoc($idRep);

    $sql="INSERT INTO pessoacontato
		(idempresa
		,idcontato
		,idpessoa
		,criadoem
		,criadopor
		,alteradoem
		,alteradopor)
	    VALUES
		(".$_SESSION["SESSAO"]["IDEMPRESA"]."
		,".$idr['idrepresentação']."
		,".$_SESSION["_pkid"]."
		,sysdate()
		,'".$_SESSION["SESSAO"]["USUARIO"]."'
		,sysdate()
		,'".$_SESSION["SESSAO"]["USUARIO"]."')";
    $res=d::b()->query($sql) or die("Erro ao vincular representante ao cliente: <br> sql=".$sql." <br> ".mysqli_error(d::b()));
    if(!$res){
        d::b()->query("ROLLBACK;");
        die("1-Falha ao vincular representante ao cliente: " .mysqli_error(d::b()). "<p>SQL: ".$sql);
    }
    
    
    //buscar divisão de negocio
    $sql1="select 
            u.idplantel			
            from plantel u 
                join plantelobjeto p 
                            on( u.idplantel = p.idplantel 
                                and p.idobjeto = ".$_SESSION["SESSAO"]["IDPESSOA"]."
                                and p.tipoobjeto = 'pessoa')
            where u.status='ATIVO' limit 1";
    $res1=d::b()->query($sql1) or die("Erro ao buscar plantel do representante: <br> sql=".$sql1." <br> ".mysqli_error(d::b()));
    $qtdres1=mysqli_num_rows($res1);
    if($qtdres1>0){
        $row1=mysqli_fetch_assoc($res1);
        $sqlx="     INSERT INTO plantelobjeto
        (
        idempresa,
        idplantel,
        idobjeto,
        tipoobjeto,
        criadoem,
        criadopor,
        alteradoem,
        alteradopor)
        VALUES
            (".$_SESSION["SESSAO"]["IDEMPRESA"]."
            ,". $row1["idplantel"]."
            ,".$_SESSION["_pkid"]."
            ,'pessoa'
            ,sysdate()
            ,'".$_SESSION["SESSAO"]["USUARIO"]."'
            ,sysdate()
            ,'".$_SESSION["SESSAO"]["USUARIO"]."')";
        
    
        $resx=d::b()->query($sqlx) or die("Erro inserir especie no cliente: <br> sql=".$sqlx." <br> ".mysqli_error(d::b()));
        if(!$resx){
            d::b()->query("ROLLBACK;");
            die("1-Falha ao vincular representante ao cliente: " .mysqli_error(d::b()). "<p>SQL: ".$sql);
        }
    }   

    
}elseif($_SESSION["SESSAO"]["OBRIGATORIOCONTATO"]=="Y" and $_SESSION["SESSAO"]["IDTIPOPESSOA"]==1 and $iu == 'i' and $idtipopessoa == '2'){

    $sql="INSERT INTO pessoacontato
            (idempresa
            ,idcontato
            ,idpessoa
            ,criadoem
            ,criadopor
            ,alteradoem
            ,alteradopor)
            VALUES
            (".$_SESSION["SESSAO"]["IDEMPRESA"]."
            ,".$_SESSION["SESSAO"]["IDPESSOA"]."
            ,".$_SESSION["_pkid"]."
            ,sysdate()
            ,'".$_SESSION["SESSAO"]["USUARIO"]."'
            ,sysdate()
            ,'".$_SESSION["SESSAO"]["USUARIO"]."')";
        $res=d::b()->query($sql) or die("Erro ao vincular representante ao cliente: <br> sql=".$sql." <br> ".mysqli_error(d::b()));
        if(!$res){
            d::b()->query("ROLLBACK;");
            die("1-Falha ao vincular representante ao cliente: " .mysqli_error(d::b()). "<p>SQL: ".$sql);
        }    

    //buscar divisão de negocio
    $sql1="select 
            u.idplantel			
            from plantel u 
                join plantelobjeto p 
                            on( u.idplantel = p.idplantel 
                                and p.idobjeto = ".$_SESSION["SESSAO"]["IDPESSOA"]."
                                and p.tipoobjeto = 'pessoa')
            where u.status='ATIVO' limit 1";
    $res1=d::b()->query($sql1) or die("Erro ao buscar plantel do representante: <br> sql=".$sql1." <br> ".mysqli_error(d::b()));
    $qtdres1=mysqli_num_rows($res1);
    if($qtdres1>0){
        $row1=mysqli_fetch_assoc($res1);
        $sqlx="     INSERT INTO plantelobjeto
        (
        idempresa,
        idplantel,
        idobjeto,
        tipoobjeto,
        criadoem,
        criadopor,
        alteradoem,
        alteradopor)
        VALUES
            (".$_SESSION["SESSAO"]["IDEMPRESA"]."
            ,". $row1["idplantel"]."
            ,".$_SESSION["_pkid"]."
            ,'pessoa'
            ,sysdate()
            ,'".$_SESSION["SESSAO"]["USUARIO"]."'
            ,sysdate()
            ,'".$_SESSION["SESSAO"]["USUARIO"]."')";
        
    
        $resx=d::b()->query($sqlx) or die("Erro inserir especie no cliente: <br> sql=".$sqlx." <br> ".mysqli_error(d::b()));
        if(!$resx){
            d::b()->query("ROLLBACK;");
            die("1-Falha ao vincular representante ao cliente: " .mysqli_error(d::b()). "<p>SQL: ".$sql);
        }
    }   
   
}

//insere como contato quando o like novo pessoa vier da tela de um cliente empresa
if(!empty($_POST['novocontatopessoa']) 
                and $iu == 'i' 
                and $idtipopessoa == '3'
                and $idtipopessoa == '8'
                and $idtipopessoa == '4'
                and $idtipopessoa == '15'
                and $idtipopessoa == '16'){
                
            
    $sql="INSERT INTO pessoacontato
            (idempresa
            ,idcontato
            ,idpessoa
            ,criadoem
            ,criadopor
            ,alteradoem
            ,alteradopor)
            VALUES
            (".$_SESSION["SESSAO"]["IDEMPRESA"]."
           ,".$_SESSION["_pkid"]."
            ,".$_POST['novocontatopessoa']."
            ,sysdate()
            ,'".$_SESSION["SESSAO"]["USUARIO"]."'
            ,sysdate()
            ,'".$_SESSION["SESSAO"]["USUARIO"]."')";
        $res=d::b()->query($sql) or die("Erro ao vincular contato ao cliente: <br> sql=".$sql." <br> ".mysqli_error(d::b()));
        if(!$res){
            d::b()->query("ROLLBACK;");
            die("1-Falha ao vincular representante ao cliente: " .mysqli_error(d::b()). "<p>SQL: ".$sql);
        }   

}//

if($_SESSION['arrpostbuffer']['x']['u']['pessoa']['vendadireta']=='Y' and !empty($_SESSION['arrpostbuffer']['x']['u']['pessoa']['idpessoa'])){
    
    $idpessoa=$_SESSION['arrpostbuffer']['x']['u']['pessoa']['idpessoa'];
    
    $sql="delete c.* 
                from pessoacontato c join pessoa p on p.idpessoa=c.idcontato  and p.idtipopessoa in(1,12)
                where c.idempresa=".$_SESSION["SESSAO"]["IDEMPRESA"]." and c.idpessoa=".$idpessoa;
    $res=d::b()->query($sql) or die("Erro ao retirar responsaveis <br> sql=".$sql." <br> ".mysqli_error(d::b()));
}

/*
if($iu == 'i' and !empty($idtipopessoa)){
    $sql="INSERT INTO immsgconfdest
            (idempresa,idimmsgconf,idobjeto,objeto,status,criadopor,criadoem,alteradopor,alteradoem)
            (select d.idempresa,d.idimmsgconf,".$_SESSION["_pkid"].",'pessoa','ATIVO','".$_SESSION["SESSAO"]["USUARIO"]."',sysdate(),'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate()
            from  sgsetor s, immsgconfdest d
            where s.idtipopessoa=".$idtipopessoa."
            and d.idobjeto = s.idsgsetor
            and d.objeto ='sgsetor')";
    $res=d::b()->query($sql) or die("Erro ao vincular pessoa ao alerta para seu setor: <br> sql=".$sql." <br> ".mysqli_error(d::b()));
    if(!$res){
	d::b()->query("ROLLBACK;");
	die("1-vincular pessoa ao alerta para seu setor: " .mysqli_error(d::b()). "<p>SQL: ".$sql);
    }    
}
*/

//inserir a pessoa para todas as unidades
//marcelocunha NÃO FAZ SENTIDO. COMENTADO.
/*
if($iu=='i' and !empty($_SESSION["_pkid"])){
    
    $sql="INSERT INTO unidadeobjeto
            (idempresa,idunidade,idobjeto,tipoobjeto,criadopor,criadoem)
        (select idempresa,idunidade,".$_SESSION["_pkid"].",'PESSOA','".$_SESSION["SESSAO"]["USUARIO"]."',sysdate() 
            from unidade where status='ATIVO')";
    $res=d::b()->query($sql) or die("Erro atualizar unidades do funcionario: <br>".mysqli_error(d::b())." sql=".$sql);

    
}
 */

if(!empty($_SESSION['arrpostbuffer']['x']['d']['plantelobjeto']['idplantel'])){
    $idpessoa	= $_SESSION['arrpostbuffer']['1']['u']['pessoa']['idpessoa'];
    $sql="UPDATE tipoplantelpessoa SET status = 'INATIVO' WHERE idpessoa = ".$idpessoa." AND idplantel = ".$_SESSION['arrpostbuffer']['x']['d']['plantelobjeto']['idplantel'];
    $res=d::b()->query($sql) or die("Erro ao atualizar as informacoes do Tipo Plantel Pessoa: <br> sql=".$sql." <br> ".mysqli_error(d::b()));
    if(!$res){
	d::b()->query("ROLLBACK;");
	die("1-Falha ao atualizar as informacoes do Tipo Plantel Pessoa: " .mysqli_error(d::b()). "<p>SQL: ".$sql);
    }

}

//Insere o Dependente referente a pessoa
if($iu == 'i' and !empty($_POST["funcionario"]) && $idtipopessoa == 115){
    $sqlDp = "INSERT INTO pessoaobjeto (idempresa, idpessoa, idobjeto, tipoobjeto, criadopor, criadoem, alteradopor, alteradoem)
          VALUES (".$_SESSION["SESSAO"]["IDEMPRESA"].", '".$_POST["funcionario"]."', ".$_SESSION["_pkid"].", 'pessoa', '".$_SESSION["SESSAO"]["USUARIO"]."', sysdate(), '".$_SESSION["SESSAO"]["USUARIO"]."', sysdate() );";
    $resDp = d::b()->query($sqlDp) or die("Erro ao inserir pessoadependente: <br>".mysqli_error(d::b())." sql=".$sqlDp);
}