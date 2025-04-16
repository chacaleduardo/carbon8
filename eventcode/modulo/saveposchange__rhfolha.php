<?
/*
 * gerar os itens da folha de pagamento
 * 
 */
$iu = $_SESSION['arrpostbuffer']['1']['i']['rhfolha']['titulo'] ? 'i' : 'u';
$tipofolha=$_SESSION['arrpostbuffer']['1']['i']['rhfolha']['tipofolha'];

$idempresa = (!empty($_GET["_idempresa"])) ? $_GET["_idempresa"] : $_SESSION["SESSAO"]["IDEMPRESA"];

$id_folha =$_SESSION["_pkid"];

//se for insert
if($iu == "i" and !empty($id_folha) and empty($idpessoa) and $tipofolha=='FOLHA'){// gerar os itens da folha 
	
    $sql = " INSERT INTO rhfolhaitem(idempresa,idrhfolha,idpessoa,regime,criadopor,criadoem,alteradopor,alteradoem) 
    (select ".$idempresa.",".$id_folha.",idpessoa,contrato,
            '".$_SESSION["SESSAO"]["USUARIO"]."',sysdate(),
            '".$_SESSION["SESSAO"]["USUARIO"]."',sysdate()
        from pessoa 
            where status='ATIVO' and idtipopessoa = 1 and contrato !='PD'  and idempresa= ".$idempresa.")";
		
    $res = d::b()->query($sql);
  			
    //echo($sql);
    if(!$res){
     
        die("1-Falha ao gerar itens da folha: " . mysqli_error(d::b()) . "<p>SQL: ".$sql);
    }

    $sql = " INSERT INTO rhfolhaitem(idempresa,idrhfolha,idpessoa,regime,criadopor,criadoem,alteradopor,alteradoem) 
    (select ".$idempresa.",".$id_folha.",p.idpessoa,'CLT',
            '".$_SESSION["SESSAO"]["USUARIO"]."',sysdate(),
            '".$_SESSION["SESSAO"]["USUARIO"]."',sysdate()
            pessoa p 
            where p.contrato = 'PD' 
            and p.status='ATIVO' 
            and p.idtipopessoa = 1
            and exists(select 1 from contrato c where c.idpessoa=p.idpessoa and p.status ='ATIVO')
            and p.idempresa= ".$idempresa.")";
		
    $res = d::b()->query($sql);
    
    //INSERIR EVENTOS FIXOS
    $datafim=$_SESSION['arrpostbuffer']['1']['i']['rhfolha']['datafim'];
    $idfluxostatus = FluxoController::getIdFluxoStatus('rhevento', 'PENDENTE');

    $sql = " INSERT INTO rhevento
                        (idempresa,
                        idrhtipoevento,
                        idrhfolha,
                        idpessoa,
                        idfluxostatus,
                        dataevento,
                        valor,criadopor,criadoem,alteradopor,alteradoem
                        )
            (select 
                    ".$idempresa.",
                    e.idrhtipoevento,
                    ".$id_folha.",
                    p.idpessoa,
                    $idfluxostatus,
                    '".validadate($datafim)."',
                    e.valor,
                    '".$_SESSION["SESSAO"]["USUARIO"]."',sysdate(),
                    '".$_SESSION["SESSAO"]["USUARIO"]."',sysdate()
                from rheventopessoa e, pessoa p 
                where p.status='ATIVO'
                and e.status='ATIVO'
                and e.idrhtipoevento >0
                and p.idempresa=".$idempresa."
                and p.idpessoa = e.idpessoa)";
    
    $res = d::b()->query($sql);
  			
    //echo($sql);
    if(!$res){     
        die("1-Falha ao gerar eventos fixos da folha: " . mysqli_error(d::b()) . "<p>SQL: ".$sql);
    }
}elseif($iu == "i" and !empty($id_folha) and empty($idpessoa) and ($tipofolha=='DECIMO TERCEIRO' or $tipofolha=='DECIMO TERCEIRO 2')){// gerar os itens da folha 
	
    $sql = "INSERT INTO rhfolhaitem(idempresa,diastrab,idrhfolha,idpessoa,regime,criadopor,criadoem,alteradopor,alteradoem) 
    (select ".$idempresa.",12,".$id_folha.",idpessoa,contrato,
            '".$_SESSION["SESSAO"]["USUARIO"]."',sysdate(),
            '".$_SESSION["SESSAO"]["USUARIO"]."',sysdate()
        from pessoa 
            where status='ATIVO' and contrato='CLT'  and idtipopessoa = 1 and idempresa= ".$idempresa.")";
		
    $res = d::b()->query($sql);
  			
    //echo($sql);
    if(!$res){
     
        die("1-Falha ao gerar itens da folha: " . mysqli_error(d::b()) . "<p>SQL: ".$sql);
    }
    
    $sql1="select * from rhtipoevento where flgdecimoterc='Y' and idrhtipoevento=427 and status ='ATIVO'";
    $res1 = d::b()->query($sql1);
    $row1=mysqli_fetch_assoc($res1);
    
    if(empty($row1['idrhtipoevento'])){
        die("E necessario configurar evento decimo terceiro no tipoevento.");
    }
    
    
    //INSERIR EVENTOS FIXOS
    $datafim=$_SESSION['arrpostbuffer']['1']['i']['rhfolha']['datafim'];



/*
    $sql = " INSERT INTO rhevento
                        (idempresa,
                        idrhtipoevento,
                        idrhfolha,
                        idpessoa,
                        dataevento,
                        valor,criadopor,criadoem,alteradopor,alteradoem
                        )
            (select 
                    ".$idempresa.",
                    e.idrhtipoevento,
                    ".$id_folha.",
                    p.idpessoa,
                    '".validadate($datafim)."',
                    e.valor,
                    '".$_SESSION["SESSAO"]["USUARIO"]."',sysdate(),
                    '".$_SESSION["SESSAO"]["USUARIO"]."',sysdate()
                from rheventopessoa e, pessoa p 
                where p.status='ATIVO'
                and e.status='ATIVO'
                and p.contrato='CLT'
                and e.idrhtipoevento in (18,33)
                and p.idempresa=".$idempresa."
                and p.idpessoa = e.idpessoa)";
    
    $res = d::b()->query($sql);
*/

    if($tipofolha=='DECIMO TERCEIRO'){
    
        $qr = "SELECT p.idpessoa, year(p.contratacao) as anocontratacao, month(p.contratacao) as mescontratacao, day(p.contratacao) as diacontratacao, 
                year(now()) as anovigente,ifnull(e1.valor, 0.0) as salario, ifnull(e2.valor, 0.0) as insalubridade
                FROM rheventopessoa e1 
                    JOIN pessoa p ON (p.idpessoa = e1.idpessoa)
                    JOIN rheventopessoa e2 ON (p.idpessoa = e2.idpessoa)
                WHERE p.status = 'ATIVO' and 
                    e1.status = 'ATIVO' and
                    e2.status = 'ATIVO' and
                    p.contrato='CLT' and
                    e1.idrhtipoevento = 18 and 
                    e2.idrhtipoevento = 33 and 
                    p.idempresa = ".$idempresa." and 
                    p.idtipopessoa = 1";
        $rs = d::b()->query($qr);

        while($rw = mysqli_fetch_assoc($rs)){

            // DÉCIMO TERCEIRO = ((( salario + insalubridade ) / 12 ) * meses trabalhados ) / 2

            // Caso o funcionário tenha sido contratado no ano vigente
            if($rw["anovigente"] == $rw["anocontratacao"]){

                // Caso tenha sido contratado depois do dia 15 do mês
                if($rw["diacontratacao"] > 15){
                    
                    // Não contar o mês de contratação
                    $valorDecimoTerceiro = ( ( ( $rw["salario"] + $rw["insalubridade"] ) / 12 ) * ( 12 - $rw["mescontratacao"] ) ) / 2;
                }else{

                    // Contar o mês de contratação
                    $valorDecimoTerceiro = ( ( ( $rw["salario"] + $rw["insalubridade"] ) / 12 ) * ( 13 - $rw["mescontratacao"] ) ) / 2;
                }
                
            }else{
                // Contabilizar 12 meses trabalhados
                $valorDecimoTerceiro = ($rw["salario"] + $rw["insalubridade"])/2;
            }

            $idfluxostatus = FluxoController::getIdFluxoStatus('rhevento', 'PENDENTE');
            $sql = "INSERT INTO rhevento
                    (
                        idempresa,
                        idrhtipoevento,
                        idrhfolha,
                        idpessoa,
                        idfluxostatus,
                        dataevento,
                        valor,
                        criadopor,criadoem,alteradopor,alteradoem
                    )
                    VALUES (
                        ".$idempresa.", 
                        ".$row1['idrhtipoevento'].", 
                        ".$id_folha.", 
                        ".$rw["idpessoa"].", 
                        $idfluxostatus,
                        '".validadate($datafim)."', 
                        ".$valorDecimoTerceiro.", 
                        '".$_SESSION["SESSAO"]["USUARIO"]."',sysdate(), '".$_SESSION["SESSAO"]["USUARIO"]."',sysdate()
                    );";
            $res = d::b()->query($sql);
            if(!$res){     
                die("1-Falha ao gerar eventos fixos da folha: " . mysqli_error(d::b()) . "<p>SQL: ".$sql);
            }
        }
    }else{//if($tipofolha=='DECIMO TERCEIRO'){

        $qr = "SELECT p.idpessoa, year(p.contratacao) as anocontratacao, month(p.contratacao) as mescontratacao, day(p.contratacao) as diacontratacao, 
        year(now()) as anovigente,ifnull(e1.valor, 0.0) as salario, ifnull(e2.valor, 0.0) as insalubridade
        FROM rheventopessoa e1 
            JOIN pessoa p ON (p.idpessoa = e1.idpessoa)
            JOIN rheventopessoa e2 ON (p.idpessoa = e2.idpessoa)
        WHERE p.status = 'ATIVO' and 
            e1.status = 'ATIVO' and
            e2.status = 'ATIVO' and
            p.contrato='CLT' and
            e1.idrhtipoevento = 18 and 
            e2.idrhtipoevento = 33 and 
            p.idempresa = ".$idempresa." and 
            p.idtipopessoa = 1";
            $rs = d::b()->query($qr);

            while($rw = mysqli_fetch_assoc($rs)){

                // DÉCIMO TERCEIRO = ((( salario + insalubridade ) / 12 ) * meses trabalhados ) / 2

                $sqx="select ifnull(valor,0) as valor from rhevento where idpessoa = ".$rw["idpessoa"]." and idrhtipoevento=427  and status !='INATIVO' ORDER BY idrhevento desc limit 1";
                $rsx = d::b()->query($sqx);
               
                $rwx=mysqli_fetch_assoc($rsx);
                if(empty($rwx['valor'])){
                    $rwx['valor']=0;
                }

                // Caso o funcionário tenha sido contratado no ano vigente
                if($rw["anovigente"] == $rw["anocontratacao"]){

                    // Caso tenha sido contratado depois do dia 15 do mês
                    if($rw["diacontratacao"] > 15){
                        
                        // Não contar o mês de contratação
                        $valorDecimoTerceiro = ( ( ( $rw["salario"] + $rw["insalubridade"] ) / 12 ) * ( 12 - $rw["mescontratacao"] ) ) - $rwx['valor'];
                    }else{

                        // Contar o mês de contratação
                        $valorDecimoTerceiro = ( ( ( $rw["salario"] + $rw["insalubridade"] ) / 12 ) * ( 13 - $rw["mescontratacao"] ) ) - $rwx['valor'];
                    }
                    
                }else{
                    // Contabilizar 12 meses trabalhados
                    $valorDecimoTerceiro = ($rw["salario"] + $rw["insalubridade"]) - $rwx['valor'];
                }

                $idfluxostatus = FluxoController::getIdFluxoStatus('rhevento', 'PENDENTE');
                $sql = "INSERT INTO rhevento
                        (
                            idempresa,
                            idrhtipoevento,
                            idrhfolha,
                            idpessoa,
                            idfluxostatus,
                            dataevento,
                            valor,
                            criadopor,criadoem,alteradopor,alteradoem
                        )
                        VALUES (
                            ".$idempresa.", 
                            ".$row1['idrhtipoevento'].", 
                            ".$id_folha.", 
                            ".$rw["idpessoa"].", 
                            $idfluxostatus,
                            '".validadate($datafim)."', 
                            ".$valorDecimoTerceiro.", 
                            '".$_SESSION["SESSAO"]["USUARIO"]."',sysdate(), '".$_SESSION["SESSAO"]["USUARIO"]."',sysdate()
                        );";
                $res = d::b()->query($sql);
                if(!$res){     
                    die("1-Falha ao gerar eventos fixos da folha: " . mysqli_error(d::b()) . "<p>SQL: ".$sql);
                }
            }

        
        

    }//if($tipofolha=='DECIMO TERCEIRO 2'){

    //echo($sql);
    if(!$res){     
        die("1-Falha ao gerar eventos fixos da folha: " . mysqli_error(d::b()) . "<p>SQL: ".$sql);
    }
}//if($iu == "i" and !empty($id_folha) and empty($idpessoa) and $tipofolha=='FOLHA'){
$x_status =$_SESSION['arrpostbuffer']['1']['u']['rhfolha']['status'];
$x_idrhfolha=$_SESSION['arrpostbuffer']['1']['u']['rhfolha']['idrhfolha'];
$x_tipofolha=$_SESSION['arrpostbuffer']['1']['u']['rhfolha']['tipofolha'];
if($x_status=='FECHADA' and !empty($x_idrhfolha) and !empty($x_tipofolha)){
    if($x_tipofolha == 'FOLHA'){
        // $str=" and (flgfolha='Y'  or flgfixo='Y' or valorconv >0 ) ";
        //alterado em 04-12-2019 hermesp
        $str=" and t.flgferias !='Y' and t.flgdecimoterc !='Y' ";
    }elseif($x_tipofolha=='FOLHA FERIAS'){
        $str=" and t.flgferias ='Y'";
    }elseif($x_tipofolha=='DECIMO TERCEIRO'){
         $str=" and t.flgdecimoterc ='Y'";
    }else{
        $str=" and t.flgdecimoterc2 ='Y'";
    }
   
    $sql="update rhfolha f,rhfolhaitem fi,rhevento e,rhtipoevento t
            set e.status = 'QUITADO'
                ,e.idrhfolha = f.idrhfolha
            where f.idrhfolha = ".$x_idrhfolha." 
            and fi.idrhfolha=f.idrhfolha
            and t.idrhtipoevento = e.idrhtipoevento
            ".$str."
            and e.idpessoa = fi.idpessoa
            and e.status='PENDENTE' 
            and e.situacao='A'
           -- and e.idempresa=".$idempresa."
            and dataevento <= f.datafim";
    $res = d::b()->query($sql);
  			
    //echo($sql);
    if(!$res){     
        die("1-Falha ao quitar eventos da folha: " . mysqli_error(d::b()) . "<p>SQL: ".$sql);
    }
    
}


$x_diastrab =$_SESSION['arrpostbuffer']['x']['u']['rhfolhaitem']['diastrab'];
$x_idrhfolhaitem=$_SESSION['arrpostbuffer']['x']['u']['rhfolhaitem']['idrhfolhaitem'];

if(!empty($x_idrhfolhaitem)){

    $idrhfolha=(traduzid("rhfolhaitem","idrhfolhaitem","idrhfolha",$x_idrhfolhaitem));

    $tipofolha=(traduzid("rhfolha","idrhfolha","tipofolha",$x_idrhfolhaitem));
    if($tipofolha=='DECIMO TERCEIRO' or $tipofolha=='DECIMO TERCEIRO 2'){

        $sql="update rhfolhaitem i,rhevento e, rheventopessoa f 
        set e.valor=round((f.valor/12)* i.diastrab,2)
         where idrhfolhaitem=".$x_idrhfolhaitem."
         and e.idrhfolha = i.idrhfolha
         and e.idpessoa = i.idpessoa
         and f.idpessoa = e.idpessoa
        -- and e.idempresa=".$idempresa."
         and f.idrhtipoevento = e.idrhtipoevento ";

    }else{
        $sql="update rhfolhaitem i,rhevento e, rheventopessoa f 
        set e.valor=round((f.valor/30)* i.diastrab,2)
         where idrhfolhaitem=".$x_idrhfolhaitem."
         and e.idrhfolha = i.idrhfolha
         and e.idpessoa = i.idpessoa
         and f.idpessoa = e.idpessoa
        -- and e.idempresa=".$idempresa."
         and f.idrhtipoevento = e.idrhtipoevento ";
    }


   
    $res = d::b()->query($sql);
  			
    //echo($sql);
    if(!$res){     
        die("2-Falha ao atualizar eventos fixos da folha: " . mysqli_error(d::b()) . "<p>SQL: ".$sql);
    }
}
