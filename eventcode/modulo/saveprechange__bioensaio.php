<?
require_once(__DIR__."/../../form/controllers/fluxo_controller.php");
 

$i_idanalise=$_POST['i_analise_idanalise'];// Variavel para gerar os testes;

//abre variavel com a acao que veio da tela
$idpessoa = $_SESSION['arrpostbuffer']['1']['i']['bioensaio']['idpessoa'];

$i_serv_idobj = $_SESSION['arrpostbuffer']['999']['i']['servicoensaio']['idobjeto'];
$u_serv_idobj = $_SESSION['arrpostbuffer']['999']['u']['servicoensaio']['idobjeto'];

//se for um insert, GERAR O IDREGISTRO DO ESTUDO
if(!empty($idpessoa)){		


    $idunidade= $_SESSION['arrpostbuffer']['1']['i']['bioensaio']['idunidade'];


    if(empty($idunidade)){
        die("[saveprechange]- Não foi possivel identificar a Unidade para gerar o Registro!!!");
    }

    ### Inicializa a sequence para bioensaio
    $sqlini = "SELECT count(*) as quant FROM sequence where sequence = 'bioensaio' and  exercicio = year(current_date) ";
    $resini = mysql_query($sqlini);
    if(!$resini){
        echo "[saveprechange]- Falha ao inicializar Sequence [bioensaio] : " . mysql_error() . "<p>SQL: $sqlini";
        die();
    }
    $rowini = mysql_fetch_array($resini);
    ### Caso nao exista a sequence inicializada 
    if($rowini["quant"]==0){
        $sqlins = "insert into sequence  (`sequence`, `chave1`,`idempresa`,exercicio) values ('bioensaio',0,".cb::idempresa().",year(current_date))";
        mysql_query($sqlins) or die("[saveprechange]- Falha ao inserir Sequence [bioensaio] : " . mysql_error() . "<p>SQL: ".$sqlins);
    }

    ### Incrementa e  a sequence
    //mysql_query("LOCK TABLES sequence WRITE") or die("seqmeiolote: Falha 1 ao efetuar LOCK [sequence]: ".mysql_error());
    //mysql_query("START TRANSACTION") or die("[saveprechange]- sequence: Falha 2 ao abrir transacao: ".mysql_error());

    mysql_query("update sequence set chave1 = (chave1 + 1) where sequence = 'bioensaio'  and  exercicio = year(current_date) ");
    //mysql_query("COMMIT") or die("[saveprechange]- sequence: Falha ao efetuar COMMIT [sequence update]: ".mysql_error());

    $sql = "SELECT chave1,exercicio FROM sequence where sequence = 'bioensaio'  and  exercicio = year(current_date)";

    $res = mysql_query($sql);

    if(!$res){
        //mysql_query("UNLOCK TABLES;") or die("sequence: Falha 3 ao efetuar UNLOCK [sequence]: ".mysql_error());
        echo "[saveprechange]- Falha Pesquisando Sequence [biensaio] : " . mysql_error() . "<p>SQL: $sql";
        die();
    }

    $row = mysql_fetch_array($res);

    ### Caso nao retorne nenhuma linha ou retorn valor vazio
    if(empty($row["chave1"]) or $row["chave1"]==0){
        if(!$resexercicio){
            //mysql_query("UNLOCK TABLES") or die("sequence: Falha 4 ao efetuar UNLOCK [sequence]: ".mysql_error());
            //mysql_query("ROLLBACK;") or die("sequence: Falha 5 ao efetuar UNLOCK [sequence]: ".mysql_error());

            echo "[saveprechange]- Falha Pesquisando Sequence [bionsaio] : " . mysql_error() . "<p>SQL: $sql";
            die();
        }
    }else{
        $_SESSION["arrpostbuffer"]["1"]["i"]["bioensaio"]["idregistro"]=$row["chave1"];
        $_SESSION["arrpostbuffer"]["1"]["i"]["bioensaio"]["exercicio"]=$row["exercicio"];
    }	
	
}//if(!empty($idpessoa)){	FIM GERAR REGISTRO


//abre variavel com a acao que veio da tela
$idanalise = $_SESSION['arrpostbuffer']['x']['u']['analise']['idanalise'];
$idbioterioanalise = $_SESSION['arrpostbuffer']['x']['u']['analise']['idbioterioanalise'];

//se for um update na analise deve atulizar os servicos
if(!empty($idanalise) and !empty($idbioterioanalise)){
    $dtinicio= implode("-",array_reverse(explode("/",$_SESSION['arrpostbuffer']['x']['u']['analise']['datadzero'])));
   // $sql="select * from servicobioterioconf where tipoobjeto = 'bioterioanalise' and idobjeto=".$idbioterioanalise." and idservicobioterio is not null";
    $sqld1="delete r.*  
            from servicoensaio s,resultado r
            where s.idobjeto=".$idanalise."
		and s.status = 'PENDENTE'
		and  s.tipoobjeto='analise'
                and r.idservicoensaio = s.idservicoensaio";
    d::b()->query($sqld1) or die("[saveprechange]- Falha ao deletar resultados na [saveprechange__bioensaio] : ".mysqli_error(d::b())."<p>SQL: ".$sqld1);
    
    $sqld="delete s.*  from servicoensaio s
                    where s.idobjeto=".$idanalise." 
                    and s.status = 'PENDENTE'
                    and  s.tipoobjeto='analise'";
    d::b()->query($sqld) or die("[saveprechange]- Falha ao ATUALIZAR bioensaio na [saveprechange__bioensaio] : ".mysqli_error(d::b())."<p>SQL: ".$sqld);
            
    $sqlins="INSERT INTO servicoensaio (idempresa,idobjeto,tipoobjeto,idservicobioterio,dia,diazero,data,status,criadopor,criadoem,alteradopor,alteradoem)
           (select ".cb::idempresa().",".$idanalise.",'analise',c.idservicobioterio,c.dia,c.diazero,DATE_ADD('".$dtinicio."', INTERVAL c.dia DAY) as datafim,'PENDENTE'
           ,'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate(),'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate()
           from servicobioterioconf c
           where c.idobjeto = ".$idbioterioanalise."
           and c.tipoobjeto='bioterioanalise')";
    $res1=d::b()->query($sqlins) or die("[saveprechange]- Erro ao gerar sevicos da analise na [saveprechange__bioensaio]: ".mysqli_error(d::b())."<p>SQL: ".$sqlins);
   
    $s="update analise a,bioensaio e,bioterioanalise b
            set e.doses=b.pddose,e.volume=b.pdvolume,e.via=b.pdvia
            where a.idanalise=".$idanalise."
            and e.idbioensaio = a.idobjeto
            and a.objeto ='bioensaio'
            and b.cria='N'
            and b.idbioterioanalise =".$idbioterioanalise; 
    $rs=d::b()->query($s) or die("[saveprechange]- Erro ao atualizar padroes da analise na [saveprechange__bioensaio]: ".mysqli_error(d::b())."<p>SQL: ".$s);
  
}//if(!empty($idanalise) and !empty($idbioterioanalise)){

//  O BOTAO GERAR TESTE
// inserir testes por analise
if(!empty($i_idanalise)){
    
    $sqlu="select u.idunidade,t.idtipounidade,t.tipounidade, case when t.idtipounidade = 12 then  10 when t.idtipounidade = 17 then 14 else 10 end as destino
    from unidade u join tipounidade t on (u.idtipounidade = t.idtipounidade) where 1 ".getidempresa("u.idempresa",$_GET['_modulo']);
    $resu = d::b()->query($sqlu) or die("geraAmostrasRelacionadasAoLote erro ao buscar unidade de P&D: ".mysqli_error(d::b()));
    $rowu=mysqli_fetch_assoc($resu);
    
    $idunidade=$rowu['destino'];
     
    $sql="select e.idbioensaio,ba.idprodserv,c.idservicobioterio,c.dia,s.idservicoensaio,e.qtd,e.produto,e.partida,e.idnucleo,e.nascimento,s.data, DATEDIFF(s.data,e.nascimento) AS idade,e.idpessoa,e.idespeciefinalidade,n.nucleo
		,sb.idsubtipoamostra,s.idamostra
            from servicobioterioconf c,bioterioanaliseteste ba,analise a,servicoensaio s,bioensaio e,nucleo n,servicobioterio sb
		where c.idobjeto=a.idbioterioanalise
		and c.tipoobjeto='bioterioanalise'
		and ba.idservicobioterioconf = c.idservicobioterioconf
                and s.idservicobioterio = sb.idservicobioterio
		and s.idservicobioterio=c.idservicobioterio
		and s.dia=c.dia
		and s.idobjeto=a.idanalise
		and s.tipoobjeto = 'analise'
		and e.idnucleo= n.idnucleo
		and e.idbioensaio = a.idobjeto
		and a.objeto = 'bioensaio'
		and a.idanalise= ".$i_idanalise." order by s.dia, idservicoensaio";
        //print_r($sql);
        //die();
    
    $res=d::b()->query($sql) or die("Erro ao buscar informacoes para gerar as amostras e testes: ".mysqli_error(d::b())."<p>SQL: ".$sql);
    while($row=mysqli_fetch_assoc($res)){
        
        if($row["idservicoensaio"]!=$idservicoensaio){
            $idservicoensaio=$row["idservicoensaio"];
            $idamostra=$row['idamostra'];
        }
        
        
        if(empty($idamostra)){
            
            //LTM - 13-04-2021: Retorna o Idfluxo Amostra
		    $idfluxostatus = FluxoController::getIdFluxoStatus('amostra', 'ABERTO', $idunidade);	

            $exerciciobioensaio = traduzid('bioensaio','idbioensaio','exercicio',$row['idbioensaio']);
            //GERA A AMOSTRA
            $arrReg=geraIdregistro(cb::idempresa(),$idunidade,$exerciciobioensaio);
            $insamostra = new Insert();
            $insamostra->setTable("amostra");
            $insamostra->idunidade=$idunidade;
            $insamostra->idempresa=cb::idempresa();
            $insamostra->status = 'ABERTO';
		    $insamostra->idfluxostatus = $idfluxostatus;
            $insamostra->idregistro=$arrReg['idregistro'];
            $insamostra->exercicio=$arrReg["exercicio"];
            $insamostra->idespeciefinalidade=$row["idespeciefinalidade"];
            $insamostra->idsubtipoamostra=$row['idsubtipoamostra'];
            $insamostra->idpessoa=$row['idpessoa'];
            $insamostra->idnucleo=$row['idnucleo'];
            $insamostra->tipoidade='Dia(s)';
            $insamostra->idade=$row['idade'];
            $insamostra->dataamostra=$row['data'];
            $insamostra->nucleoamostra=$row['nucleo'];
            $insamostra->partida=$row['produto'].' '.$row['partida'];
            //$insamostra->lote=$rowam['partida'];
            $insamostra->tipoobjetosolipor="servicoensaio";
            $insamostra->idobjetosolipor=$row["idservicoensaio"];
            //$insamostra->estexterno=$rowam['bioensaio'];
            $idamostra=$insamostra->save();

            //LTM - 13-04-2021: Insere FluxoHist Amostra
            FluxoController::inserirFluxoStatusHist($fluxostatus['modulo'], $idamostra, $idfluxostatus, 'PENDENTE');

            $sqlu="update servicoensaio set idamostra =".$idamostra."
                                        where idservicoensaio =".$row["idservicoensaio"];
            d::b()->query($sqlu) or die("erro ao atualizar amostra no servicoensaio sql=".$sqlu);

        }//if(empty($row['idamostra'])){
        
        if(empty($idamostra)){
           echo('não gerado ID amostra'); die;
        }
        
        //LTM (12-04-2021): Retorna o Idfluxo nf e Inserção do Fluxo no Hist
        $rowFluxo = FluxoController::getDadosResultadoAmostra('amostra', 'idamostra', $idamostra, 'ABERTO', 'resultado', '', '');	
        $sqlinsereitens = "insert into resultado (
                                                    idamostra
                                                    ,idempresa
                                                    ,idtipoteste
                                                    ,idservicoensaio
                                                    ,quantidade
                                                    ,status
                                                    ,idfluxostatus
                                                    ,criadopor
                                                    ,criadoem
                                                    ,alteradopor
                                                    ,alteradoem
                                                    )
                                        select ".
                                                        $idamostra."
                                                    , ".cb::idempresa()."
                                                    ,".$row['idprodserv']."
                                                    ,".$row['idservicoensaio']."
                                                    ,".$row['qtd']."
                                                    ,'ABERTO'
                                                    , '".$rowFluxo['idfluxostatus']."'
                                                    ,'".$_SESSION["SESSAO"]["USUARIO"]."'
                                                    ,now()
                                                    ,'".$_SESSION["SESSAO"]["USUARIO"]."'
                                                    ,now()";
        
        $res1 = d::b()->query($sqlinsereitens);
        if(!$res1){
             echo("Erro inserindo teste: ".mysqli_error(d::b())." sql=".$sqlinsereitens);
        }

        $idresultado = mysqli_insert_id(d::b());
        FluxoController::inserirFluxoStatusHist($rowFluxo['modulo'], $idresultado, $rowFluxo['idfluxostatus'], 'PENDENTE');

        //mysqli_query("COMMIT") or die("Falha ao efetuar COMMIT [sequence update]: ".mysqli_error(d::b()));
	
        
    }//while($row=mysqli_fetch_assoc($res)){
}


// inserir testes individuais por servico
if(!empty($_POST['i_teste_idanalise']) and !empty($_POST['i_teste_idservico'])and !empty($_POST['i_teste_idtipoteste']) and !empty($_POST['i_teste_qtdteste'])){
    
    $sqlu="select u.idunidade,t.idtipounidade,t.tipounidade, case when t.idtipounidade = 12 then  10 when t.idtipounidade = 17 then 14 else 10 end as destino
    from unidade u join tipounidade t on (u.idtipounidade = t.idtipounidade) where 1 ".getidempresa("u.idempresa",$_GET['_modulo']);
    $resu = d::b()->query($sqlu) or die("geraAmostrasRelacionadasAoLote erro ao buscar unidade de P&D: ".mysqli_error(d::b()));
    $rowu=mysqli_fetch_assoc($resu);
    $idunidade=$rowu['destino'];
    
    $_idservicoensaio=$_POST['i_teste_idservico'];
    $_idprodserv=$_POST['i_teste_idtipoteste'];
    $_qtd=$_POST['i_teste_qtdteste'];     
    
    $sql="SELECT     
    s.idservicobioterio,
    e.idbioensaio,
    s.dia,
    s.idservicoensaio,
    e.qtd,
    e.produto,
    e.partida,
    e.idnucleo,
    e.nascimento,
    s.data,
    DATEDIFF(s.data, e.nascimento) AS idade,
    e.idpessoa,
    e.idespeciefinalidade,
    n.nucleo,
    sb.idsubtipoamostra,
    s.idamostra
FROM
    analise a
    JOIN servicoensaio s ON (s.idobjeto = a.idanalise AND s.tipoobjeto = 'analise' AND s.idservicoensaio =".$_idservicoensaio.")
    JOIN servicobioterio sb ON (s.idservicobioterio = sb.idservicobioterio)
    JOIN bioensaio e ON (e.idbioensaio = a.idobjeto)
    LEFT JOIN nucleo n ON (e.idnucleo = n.idnucleo)
WHERE a.objeto = 'bioensaio'";
        //print_r($sql);
        //die();
   
    $res=d::b()->query($sql) or die("[saveprechange]- Erro ao buscar informacoes para gerar as amostras e testes: ".mysqli_error(d::b())."<p>SQL: ".$sql);
    $row=mysqli_fetch_assoc($res);
        
        
    $idservicoensaio=$row["idservicoensaio"];
    $idamostra=$row['idamostra'];

        
        
    if(empty($idamostra)){
        
        //LTM - 13-04-2021: Retorna o Idfluxo Amostra
        $idfluxostatus = FluxoController::getIdFluxoStatus('amostra', 'ABERTO', $idunidade);
        $exerciciobioensaio = traduzid('bioensaio','idbioensaio','exercicio',$row['idbioensaio']);


        //GERA A AMOSTRA
        $arrReg=geraIdregistro(cb::idempresa(), $idunidade,$exerciciobioensaio);
        $insamostra = new Insert();
        $insamostra->setTable("amostra");
        $insamostra->idunidade=$idunidade;
        $insamostra->idempresa=cb::idempresa();
        $insamostra->status = 'ABERTO';
        $insamostra->idfluxostatus = $idfluxostatus;
        $insamostra->idregistro=$arrReg['idregistro'];
        $insamostra->exercicio=$arrReg["exercicio"];
        $insamostra->idespeciefinalidade=$row["idespeciefinalidade"];
        $insamostra->idsubtipoamostra=$row['idsubtipoamostra'];
        $insamostra->idpessoa=$row['idpessoa'];
        $insamostra->idnucleo=$row['idnucleo'];
        $insamostra->tipoidade='Dia(s)';
        $insamostra->idade=$row['idade'];
        $insamostra->dataamostra=$row['data'];
        $insamostra->nucleoamostra=$row['nucleo'];
        $insamostra->partida=$row['produto'].' '.$row['partida'];
        //$insamostra->lote=$rowam['partida'];
        $insamostra->tipoobjetosolipor="servicoensaio";
        $insamostra->idobjetosolipor=$row["idservicoensaio"];
        //$insamostra->estexterno=$rowam['bioensaio'];
        $idamostra=$insamostra->save();
        
        //LTM - 13-04-2021: Insere FluxoHist Amostra
        $modulo = getModuloPadrao('amostra', $idunidade);
        FluxoController::inserirFluxoStatusHist($modulo, $idamostra, $idfluxostatus, 'PENDENTE');

        $sqlu="update servicoensaio set idamostra =".$idamostra."
                                    where idservicoensaio =".$row["idservicoensaio"];
        d::b()->query($sqlu) or die("[saveprechange]- Erro-2 ao atualizar amostra no servicoensaio sql=".$sqlu);

    }//if(empty($row['idamostra'])){
    
    if(empty($idamostra)){
        echo('não gerado ID amostra'); die;
    }
    
    //LTM - 13-04-2021: Retorna o Idfluxo Resultado
    $rowFluxo = FluxoController::getDadosResultadoAmostra('amostra', 'idamostra', $idamostra, 'ABERTO', 'resultado', '', '');

    $sqlinsereitens = "insert into resultado (
                                            idamostra
                                            ,idempresa
                                            ,idtipoteste
                                            ,idservicoensaio
                                            ,quantidade
                                            ,status
                                            ,idfluxostatus
                                            ,criadopor
                                            ,criadoem
                                            ,alteradopor
                                            ,alteradoem
                                            )
                        select ".
                                        $idamostra."
                                        , ".cb::idempresa()."
                                        ,".$_idprodserv."
                                        ,".$row['idservicoensaio']."
                                        ,".$_qtd."
                                        ,'ABERTO'
                                        , '".$rowFluxo['idfluxostatus']."'
                                        ,'".$_SESSION["SESSAO"]["USUARIO"]."'
                                        ,now()
                                        ,'".$_SESSION["SESSAO"]["USUARIO"]."'
                                        ,now()";
    
    $res1 = d::b()->query($sqlinsereitens);
    if(!$res1){
            echo("[saveprechange]- Erro-2 inserindo teste: ".mysqli_error(d::b())." sql=".$sqlinsereitens);
    }

    //LTM - 13-04-2021: Insere FluxoHist Resultado
    $idresultado = mysqli_insert_id(d::b());
    FluxoController::inserirFluxoStatusHist($rowFluxo['modulo'], $idresultado, $rowFluxo['idfluxostatus'], 'PENDENTE');
    
    //mysqli_query("COMMIT") or die("Falha ao efetuar COMMIT [sequence update]: ".mysqli_error(d::b()));
}//if(!empty($_POST['i_teste_idanalise']) and !empty($_POST['i_teste_idservico'])and !empty($_POST['i_teste_idtipoteste']) and !empty($_POST['i_teste_qtdteste'])){

//inserir ou alterar servico
if(!empty($i_serv_idobj) or !empty($u_serv_idobj)){
    if(!empty($i_serv_idobj)){//insert
       $dia= $_SESSION['arrpostbuffer']['999']['i']['servicoensaio']['dia'];
       
        $sql="select  dma(DATE_ADD(datadzero, INTERVAL ".$dia." DAY)) as datafim
            from analise 
        where idanalise=".$i_serv_idobj;
        $res = d::b()->query($sql)  or die("[saveprechange]- Erro ao buscar data fim da analise: ".mysqli_error(d::b())."<p>SQL: ".$sql);
        $row= mysqli_fetch_assoc($res);
        $_SESSION['arrpostbuffer']['999']['i']['servicoensaio']['data']=$row["datafim"];
        
    }else{//update
        $dia= $_SESSION['arrpostbuffer']['999']['u']['servicoensaio']['dia'];
         $sql="select  dma(DATE_ADD(datadzero, INTERVAL ".$dia." DAY)) as datafim
            from analise 
        where idanalise=".$u_serv_idobj;
        $res = d::b()->query($sql)  or die("[saveprechange]- Erro para alterar a data fim da analise: ".mysqli_error(d::b())."<p>SQL: ".$sql);
        $row= mysqli_fetch_assoc($res);
        $_SESSION['arrpostbuffer']['999']['u']['servicoensaio']['data']=$row["datafim"];
    }
}//if(!empty($i_serv_idobj) or !empty($u_serv_idobj)){
    $_x_i_analise_idobjeto = $_SESSION['arrpostbuffer']['x']['i']['analise']['idobjeto'];
    $_x_i_analise_objeto = $_SESSION['arrpostbuffer']['x']['i']['analise']['objeto'];
 
    if(!empty($_x_i_analise_idobjeto) and !empty($_x_i_analise_objeto)){
        $ins = new Insert();
        $ins->setTable("analise");
        $ins->idobjeto=$_x_i_analise_idobjeto; 
        $ins->objeto=$_x_i_analise_objeto;
        $idanalise=$ins->save();

        unset($_SESSION['arrpostbuffer']);

        $_SESSION['arrpostbuffer']['x1']['i']['localensaio']['idanalise'] = $idanalise;
        montatabdef();
    }


    $_x_i_lotecons_idlotefracao = $_SESSION['arrpostbuffer']['x']['i']['lotecons']['idlotefracao'];
    $_x_i_lotecons_qtdd = $_SESSION['arrpostbuffer']['x']['i']['lotecons']['qtdd'];
    $_x_i_lotecons_idobjeto = $_SESSION['arrpostbuffer']['x']['i']['lotecons']['idobjeto'];
    
    if(!empty($_x_i_lotecons_idlotefracao) and !empty($_x_i_lotecons_idobjeto)){
        $sql = "Select idlote from lotefracao where idlotefracao = ".$_x_i_lotecons_idlotefracao;
        $res = d::b()->query($sql)  or die("[saveprechange]- Erro ao buscar lote: ".mysqli_error(d::b())."<p>SQL: ".$sql);

        $row = mysqli_fetch_assoc($res);


        $_SESSION['arrpostbuffer']['x']['i']['lotecons']['idlote'] = $row['idlote'];
        $_SESSION['arrpostbuffer']['x']['i']['lotecons']['tipoobjeto'] = 'bioensaio';
        $_SESSION['arrpostbuffer']['xx']['u']['bioensaio']['idbioensaio'] = $_x_i_lotecons_idobjeto;
        $_SESSION['arrpostbuffer']['xx']['u']['bioensaio']['idlote'] = $row['idlote'];
        montatabdef();
    }

    $_x_u_bioensaio_idbioensaio = $_SESSION['arrpostbuffer']['x']['u']['bioensaio']['idbioensaio'];
    $_x_u_bioensaio_idlote = $_SESSION['arrpostbuffer']['x']['u']['bioensaio']['idlote'];
 
    if(!empty($_x_u_bioensaio_idlote) and !empty($_x_u_bioensaio_idbioensaio)){
        $sql = "update bioensaio set idlote = NULL where idbioensaio = ". $_x_u_bioensaio_idbioensaio;
        $res = d::b()->query($sql)  or die("[saveprechange]- Erro apagar lote no bioensaio: ".mysqli_error(d::b())."<p>SQL: ".$sql);
        $sql = "select * from lotecons  where idlote =". $_x_u_bioensaio_idlote." and tipoobjeto = 'bioensaio' and idobjeto=".$_x_u_bioensaio_idbioensaio;
        $res = d::b()->query($sql)  or die("[saveprechange]- Erro apagar lote no bioensaio: ".mysqli_error(d::b())."<p>SQL: ".$sql);
        //print_r($sql);
        //die();

        $row = mysqli_fetch_assoc($res);
        if (!empty($row['idlote'])) {
            unset($_SESSION['arrpostbuffer']);
            $_SESSION['arrpostbuffer']['x']['d']['lotecons']['idlotecons'] = $row['idlotecons'];
        }

    }

   
    $estudo=$_SESSION['arrpostbuffer']['1']['u']['bioensaio']['estudo'];

    if(!empty($estudo)){
        $sqlu="select u.idunidade,t.idtipounidade,t.tipounidade, case when t.idtipounidade = 12 then  10 when t.idtipounidade = 17 then 14 else 10 end as destino
        from unidade u join tipounidade t on (u.idtipounidade = t.idtipounidade) where 1 ".getidempresa("u.idempresa",$_GET['_modulo']);
        $resu = d::b()->query($sqlu) or die("geraAmostrasRelacionadasAoLote erro ao buscar unidade de P&D: ".mysqli_error(d::b()));
        $rowu=mysqli_fetch_assoc($resu);

        $idunidade=$rowu['destino'];
        $_x_u_bioensaio_idbioensaio = $_SESSION['arrpostbuffer']['1']['u']['bioensaio']['idbioensaio'];

        $sqlb="select * from bioensaio where idbioensaio=".$_x_u_bioensaio_idbioensaio;
        $resb = d::b()->query($sqlb)  or die("[saveprechange]- Erro ao buscar estudo do bioensaio: ".mysqli_error(d::b())."<p>SQL: ".$sqlb);
        $rob = mysqli_fetch_assoc($resb);
        $_x_i_nucleo_idobjeto= $rob['idnucleo'];
        if(!empty($_SESSION['arrpostbuffer']['1']['u']['bioensaio']['idlotepd'])){
            $idlotepd = $_SESSION['arrpostbuffer']['1']['u']['bioensaio']['idlotepd'];
        }elseif (!empty($rob['idlotepd'])) {
            $idlotepd = $rob['idlotepd'];
        }elseif(empty($rob['idlotepd']) and empty($_SESSION['arrpostbuffer']['1']['u']['bioensaio']['idlotepd'])) {
            $idlotepd = NULL;
        }

       // var_dump($_SESSION['arrpostbuffer']['1']['u']['bioensaio']['nascimento']);
        if (empty( $_SESSION['arrpostbuffer']['1']['u']['bioensaio']['idnucleo'])) {
            if (!empty($idlotepd)) {
                $sql= "select l.idlote, l.partida as lote,concat(l.partida,'/',l.exercicio,' - ',ifnull(p.descrcurta,p.descr)) as descr
                from lote l
                join prodserv p on(p.idprodserv=l.idprodserv)
                where l.idlote = ".$idlotepd."
                ".getidempresa('p.idempresa','prodserv')." order by descr";

                $res = d::b()->query($sql)  or die("[saveprechange]- Erro ao buscar lote do bioensaio na prodserv: ".mysqli_error(d::b())."<p>SQL: ".$sql);
                $row = mysqli_fetch_assoc($res);
                $ins = new Insert();
                $ins->setTable("nucleo");
                $ins->idobjeto=$idlotepd; 
                $ins->idempresa=cb::idempresa(); 
                $ins->idpessoa=$_POST['bioensaio_idpessoa']; 
                $ins->idunidade=$idunidade; 
                $ins->idespeciefinalidade=$_SESSION['arrpostbuffer']['1']['u']['bioensaio']['idespeciefinalidade']; 
                $ins->objeto='lote';
                $ins->nucleo='B'.$_SESSION['arrpostbuffer']['1']['u']['bioensaio']['idregistro'].' '.$estudo.' - '.$row['lote'];
                $ins->lote=$row['lote'];
                $idnucleo=$ins->save();

                
                $_SESSION['arrpostbuffer']['1']['u']['bioensaio']['idnucleo'] = $idnucleo;
                $_SESSION['arrpostbuffer']['1']['u']['bioensaio']['idbioensaio'] = $_x_u_bioensaio_idbioensaio;
                montatabdef();
            }else{
                $ins = new Insert();
                $ins->setTable("nucleo");
                //$ins->idobjeto=$idlotepd; 
                $ins->idempresa=cb::idempresa(); 
                $ins->idpessoa=$_POST['bioensaio_idpessoa']; 
                $ins->idunidade=$idunidade; 
                $ins->idespeciefinalidade=$_SESSION['arrpostbuffer']['1']['u']['bioensaio']['idespeciefinalidade']; 
                $ins->objeto='lote';
                $ins->nucleo='B'.$_SESSION['arrpostbuffer']['1']['u']['bioensaio']['idregistro'].' '.$estudo;
                //$ins->lote=$row['lote'];
                $idnucleo=$ins->save();
                
                $_SESSION['arrpostbuffer']['1']['u']['bioensaio']['idnucleo'] = $idnucleo;
                $_SESSION['arrpostbuffer']['1']['u']['bioensaio']['idbioensaio'] = $_x_u_bioensaio_idbioensaio;
                montatabdef();
            }
        }else{
            if (!empty($idlotepd)) {
                $sql= "select l.idlote, l.partida as lote,concat(l.partida,'/',l.exercicio,' - ',ifnull(p.descrcurta,p.descr)) as descr
                from lote l 
                join prodserv p on(p.idprodserv=l.idprodserv)
                where l.idlote = ".$idlotepd."
                ".getidempresa('p.idempresa','prodserv')." order by descr";
                
                $res = d::b()->query($sql)  or die("[saveprechange]- Erro ao buscar lote do bioensaio na prodserv: ".mysqli_error(d::b())."<p>SQL: ".$sql);
                $rowpd = mysqli_fetch_assoc($res);
                $nucleo = "B".$_SESSION['arrpostbuffer']['1']['u']['bioensaio']['idregistro']." ".$estudo." - ".$rowpd['lote'];
                $upn="UPDATE nucleo SET 
                idobjeto=".$idlotepd.", 
                nucleo='".$nucleo."',
                idpessoa=".$_POST['bioensaio_idpessoa'].",
                idespeciefinalidade=".$_SESSION['arrpostbuffer']['1']['u']['bioensaio']['idespeciefinalidade'].",
                lote = '".$rowpd['lote']."',
                alteradopor = '".$_SESSION['SESSAO']['USUARIO']."',
                alteradoem = sysdate()
                WHERE idnucleo=".$_x_i_nucleo_idobjeto;
                $res = d::b()->query($upn)  or die("[saveprechange]- Erro ao fazer update do nucleo: ".mysqli_error(d::b())."<p>SQL: ".$upn);

            }else {

                $nucleo = "B".$_SESSION['arrpostbuffer']['1']['u']['bioensaio']['idregistro']." ".$estudo;
                $upn="UPDATE nucleo SET 
                nucleo='".$nucleo."',
                idpessoa=".$_POST['bioensaio_idpessoa'].",
                idespeciefinalidade=".$_SESSION['arrpostbuffer']['1']['u']['bioensaio']['idespeciefinalidade'].",
                alteradopor = '".$_SESSION['SESSAO']['USUARIO']."',
                alteradoem = NOW()
                WHERE idnucleo=".$_x_i_nucleo_idobjeto;
                $res = d::b()->query($upn)  or die("[saveprechange]- Erro ao fazer update do nucleo: ".mysqli_error(d::b())."<p>SQL: ".$upn);
            }
           
            
            
        }
    }
    ?>

