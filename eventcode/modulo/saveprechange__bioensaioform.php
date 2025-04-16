<?
require_once(__DIR__."/../../form/controllers/fluxo_controller.php");
 $_idempresa=cb::idempresa();

//abre variavel com a acao que veio da tela
$idpessoa = $_SESSION['arrpostbuffer']['1']['i']['bioensaio']['idpessoa'];




//se for um insert, GERAR O IDREGISTRO DO ESTUDO
if(!empty($idpessoa)){		

    $idunidade = 2;//produção

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
        $sqlins = "insert into sequence  (`sequence`, `chave1`,`idempresa`,exercicio) values ('bioensaio',0,".$_idempresa.",year(current_date))";
        mysql_query($sqlins) or die("[saveprechange]- Falha ao inserir Sequence [bioensaio] : " . mysql_error() . "<p>SQL: ".$sqlins);
    }

    ### Incrementa e  a sequence
    mysql_query("LOCK TABLES sequence WRITE") or die("seqmeiolote: Falha 1 ao efetuar LOCK [sequence]: ".mysql_error());
    mysql_query("START TRANSACTION") or die("[saveprechange]- sequence: Falha 2 ao abrir transacao: ".mysql_error());

    mysql_query("update sequence set chave1 = (chave1 + 1) where sequence = 'bioensaio'  and  exercicio = year(current_date) ");
    mysql_query("COMMIT") or die("[saveprechange]- sequence: Falha ao efetuar COMMIT [sequence update]: ".mysql_error());

    $sql = "SELECT chave1,exercicio FROM sequence where sequence = 'bioensaio'  and  exercicio = year(current_date)";

    $res = mysql_query($sql);

    if(!$res){
        mysql_query("UNLOCK TABLES;") or die("sequence: Falha 3 ao efetuar UNLOCK [sequence]: ".mysql_error());
        echo "[saveprechange]- Falha Pesquisando Sequence [biensaio] : " . mysql_error() . "<p>SQL: $sql";
        die();
    }

    $row = mysql_fetch_array($res);

    ### Caso nao retorne nenhuma linha ou retorn valor vazio
    if(empty($row["chave1"]) or $row["chave1"]==0){
        if(!$resexercicio){
            mysql_query("UNLOCK TABLES") or die("sequence: Falha 4 ao efetuar UNLOCK [sequence]: ".mysql_error());
            mysql_query("ROLLBACK;") or die("sequence: Falha 5 ao efetuar UNLOCK [sequence]: ".mysql_error());

            echo "[saveprechange]- Falha Pesquisando Sequence [bionsaio] : " . mysql_error() . "<p>SQL: $sql";
            die();
        }
    }else{
        $_SESSION["arrpostbuffer"]["1"]["i"]["bioensaio"]["idregistro"]=$row["chave1"];
        $_SESSION["arrpostbuffer"]["1"]["i"]["bioensaio"]["exercicio"]=$row["exercicio"];
    }	
	
}//if(!empty($idpessoa)){	FIM GERAR REGISTRO


/*
 * GERAR PROTOCOLO DE SERVICO NA LOTE 
 */
$arrpb=$_SESSION["arrpostbuffer"];
reset($arrpb);
//Gerar PARTIDA para qualquer linha que realize insert na lote
while (list($linha, $arrlinha) = each($arrpb)) {
	while (list($acao, $arracao) = each($arrlinha)) {
		if($acao=="i"){
                    /*
                    $sqlx="select idunidade from unidade where producao='Y' and idempresa=".$_SESSION["SESSAO"]["IDEMPRESA"];
                    $resx = d::b()->query($sqlx) or die("presave: Erro ao buscar unidade de produção: ".mysqli_error(d::b())."\n".$sql);
                    $rowx=mysqli_fetch_assoc($resx);
                    $idunidade=$rowx['idunidade'];
                     
                     */
			while (list($tab, $arrtab) = each($arracao)){
                            //Se for tabela de lote, gerar incondicionalmente a Partida
                            if($tab=="lote" and empty($_SESSION["arrpostbuffer"][$linha][$acao]["lote"]["partida"])){
                                $_arrlote = geraLoteServico($arrtab["idprodserv"]);
                                $ssq="select * from prodservformula where status='ATIVO' and idprodserv=".$arrtab["idprodserv"];
                                $rss = d::b()->query($ssq) or die("Prechange: Erro ao buscar formula: ".mysqli_error(d::b())."\n".$ssq);
                                $rows=mysqli_fetch_assoc($rss);
                                
                                $ssqp="select idprproc from prodservprproc where idprodserv=".$arrtab["idprodserv"];
                                $rssp = d::b()->query($ssqp) or die("Prechange: Erro ao buscar processo: ".mysqli_error(d::b())."\n".$ssqp);
                                $rowp=mysqli_fetch_assoc($rssp);
                                
                                $sqlx="select idunidade from unidade where idtipounidade=12 and status='ATIVO' and idempresa=".$_SESSION["SESSAO"]["IDEMPRESA"];
                                $resx = d::b()->query($sqlx) or die("presave: Erro ao buscar unidade de produção: ".mysqli_error(d::b())."\n".$sql);
                                $rowx=mysqli_fetch_assoc($resx);
                                $idunidade=$rowx['idunidade'];
                               
                                //Enviar o campo para a pagina de submit
                                $_SESSION["arrpostbuffer"][$linha][$acao]["lote"]["idprodservformula"] = $rows['idprodservformula'];
                                $_SESSION["arrpostbuffer"][$linha][$acao]["lote"]["idprproc"] = $rowp['idprproc'];
                                $_SESSION["arrpostbuffer"][$linha][$acao]["lote"]["partida"] = $_arrlote[0];
                                $_SESSION["arrpostbuffer"][$linha][$acao]["lote"]["idunidade"] =$idunidade;
                                //$_SESSION["arrpostbuffer"][$linha][$acao]["lote"]["spartida"] = $_arrlote[0];
                                //$_SESSION["arrpostbuffer"][$linha][$acao]["lote"]["npartida"] = $_arrlote[1];
                               
                            }

			}
		}
	}
}

// inserir testes individuais por servico
if(!empty($_POST['i_teste_idloteativ']) and !empty($_POST['i_teste_idtipoteste']) and !empty($_POST['i_teste_qtdteste'])){
    $idunidade=2;
    $_idloteativ=$_POST['i_teste_idloteativ'];
    $_idprodserv=$_POST['i_teste_idtipoteste'];
    $_qtd=$_POST['i_teste_qtdteste'];    
    
    $sql="select 
    la.dia,
    la.idloteativ,
    b.qtd,
    b.produto,
    b.partida,
    b.idnucleo,
    b.nascimento,
	la.execucao as data,
    DATEDIFF(la.execucao, b.nascimento) AS idade,
    b.idpessoa,
    b.idespeciefinalidade,
    n.nucleo,
    a.idsubtipoamostra,
    a.idamostra
            from loteativ la
            left join amostra a on(a.tipoobjetosolipor='loteativ' and a.idobjetosolipor =la.idloteativ)
            left join lote l on(l.idlote=la.idlote)
            left join bioensaio b on( b.idbioensaio= l.idobjetoprodpara and l.tipoobjetoprodpara='bioensaio') 
            left join nucleo n on(b.idnucleo=n.idnucleo)
            where  la.idloteativ=".$_idloteativ;
   
$res=d::b()->query($sql) or die("[saveprechange]- Erro ao buscar informacoes para gerar as amostras e testes: ".mysqli_error(d::b())."<p>SQL: ".$sql);
$row=mysqli_fetch_assoc($res);
        
        
        $idloteativ=$row["idloteativ"];
        $idamostra=$row['idamostra'];

        
        
        if(empty($idamostra)){
            $sqlx="select p.idsubtipoamostra,t.idpessoa
                    from loteativ l join prativ p on(p.idprativ=l.idprativ)
                    join lote t on(t.idlote=l.idlote)
                    where l.idloteativ =".$row["idloteativ"];
            $resx=d::b()->query($sqlx) or die("[saveposchange]-  Falha ao buscar o tipo e a pessoa para gerar a amostra: ".mysqli_error(d::b())."<p>SQL: ".$sqlx);
            $rowx=mysqli_fetch_assoc($resx);
          
            //LTM - 13-04-2021: Retorna o Idfluxo Amostra
            $idfluxostatus = FluxoController::getIdFluxoStatus('amostra', 'ABERTO', $idunidade);

            //GERA A AMOSTRA
            $arrReg=geraIdregistro($_SESSION["SESSAO"]["IDEMPRESA"],$idunidade);
            $insamostra = new Insert();
            $insamostra->setTable("amostra");
            $insamostra->idunidade=$idunidade;
            $insamostra->idempresa=$_idempresa;            
            $insamostra->status = 'ABERTO';
            $insamostra->idfluxostatus = $idfluxostatus;
            $insamostra->idregistro=$arrReg['idregistro'];
            $insamostra->exercicio=$arrReg["exercicio"];
            $insamostra->idespeciefinalidade=$row["idespeciefinalidade"];
            $insamostra->idsubtipoamostra=$rowx['idsubtipoamostra'];
            $insamostra->idpessoa=$rowx['idpessoa'];
            $insamostra->idnucleo=$row['idnucleo'];
            $insamostra->tipoidade='Dia(s)';
            $insamostra->idade=$row['idade'];
            $insamostra->dataamostra=$row['data'];
            $insamostra->nucleoamostra=$row['nucleo'];
            $insamostra->partida=$row['produto'].' '.$row['partida'];
            //$insamostra->lote=$rowam['partida'];
            $insamostra->tipoobjetosolipor="loteativ";
            $insamostra->idobjetosolipor=$row["idloteativ"];
            //$insamostra->estexterno=$rowam['bioensaio'];
            $idamostra=$insamostra->save();

            //LTM - 13-04-2021: Insere FluxoHist Amostra
            $modulo = getModuloPadrao('amostra', $idunidade); 
            FluxoController::inserirFluxoStatusHist($modulo, $idamostra, $idfluxostatus, 'PENDENTE');

        }//if(empty($row['idamostra'])){
        
        if(empty($idamostra)){
           echo('não gerado ID amostra'); die;
        }
        
        //LTM - 13-04-2021: Retorna o Idfluxo Resultado
        $rowFluxo = FluxoController::getDadosResultadoAmostra('amostra', 'idamostra', $idamostra, 'AGUARDANDO', 'resultado', '', '');

        $sqlinsereitens = "insert into resultado (
                                                    idamostra
                                                    ,idempresa
                                                    ,idtipoteste
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
                                             , ".$_idempresa."
                                             ,".$_idprodserv."
                                             ,".$_qtd."
                                             ,'AGUARDANDO'
                                             , '".$rowFluxo['idfluxostatus']."'
                                             ,'".$_SESSION["SESSAO"]["USUARIO"]."'
                                             ,now()
                                             ,'".$_SESSION["SESSAO"]["USUARIO"]."'
                                             ,now()";
        
        $res1 = d::b()->query($sqlinsereitens);
        if(!$res1){
             echo("[saveprechange]- Erro-2 inserindo teste: ".mysqli_error(d::b())." sql=".$sqlinsereitens);
        }

        $idresultado = mysqli_insert_id(d::b());
        FluxoController::inserirFluxoStatusHist($rowFluxo['modulo'], $idresultado, $rowFluxo['idfluxostatus'], 'PENDENTE');
        
        //mysqli_query("COMMIT") or die("Falha ao efetuar COMMIT [sequence update]: ".mysqli_error(d::b()));
}//if(!empty($_POST['i_teste_idanalise']) and !empty($_POST['i_teste_idservico'])and !empty($_POST['i_teste_idtipoteste']) and !empty($_POST['i_teste_qtdteste'])){

$i_ativ_idprativ = $_SESSION['arrpostbuffer']['999']['i']['loteativ']['idprativ'];
$i_ativ_idlote=$_SESSION['arrpostbuffer']['999']['i']['loteativ']['idlote'];

if(!empty($i_ativ_idprativ)){
    
    $ativ= traduzid('prativ', 'idprativ', 'ativ', $i_ativ_idprativ);
    
    $dataprod= traduzid('lote', 'idlote', 'producao', $i_ativ_idlote);
    
    $dia=$_SESSION['arrpostbuffer']['999']['i']['loteativ']['dia'];
   
    if(!empty($dataprod) and !empty($dia)){
        $sql="select DATE_ADD('".$dataprod."', INTERVAL (ifnull(".$dia.",1)-1) DAY) as execucao";
        $resf = d::b()->query($sql) or die("erro ao atualizar datas da atividade: ". mysqli_error(d::b()));
        $rof=mysqli_fetch_assoc($resf);
        $_SESSION['arrpostbuffer']['999']['i']['loteativ']['execucao']=dma($rof['execucao']);
    }
    $sco="select count(*) as ord from loteativ where idlote=".$i_ativ_idlote;
    $rco = d::b()->query($sco);
    $roco=mysqli_fetch_assoc($rco);
    $_SESSION['arrpostbuffer']['999']['i']['loteativ']['ord']=$roco['ord'];
    $_SESSION['arrpostbuffer']['999']['i']['loteativ']['ativ']=$ativ;
  
}
//print_r($_SESSION['arrpostbuffer']['999']);
//die();
?>

