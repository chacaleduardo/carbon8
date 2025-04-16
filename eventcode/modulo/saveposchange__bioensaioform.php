<?

$i_ativ_idprativ = $_SESSION['arrpostbuffer']['999']['i']['loteativ']['idprativ'];
$i_ativ_idlote=$_SESSION['arrpostbuffer']['999']['i']['loteativ']['idlote'];
$inidloteativ=$_SESSION['arrscriptsql']['999']['loteativ']['insertid'];



if(!empty($i_ativ_idprativ) and !empty($inidloteativ) and !empty($i_ativ_idlote)){
       
    congelaAtividade($i_ativ_idlote,$i_ativ_idprativ,$inidloteativ);//congelar a atividade da formalizacao
    
    $status= traduzid('lote', 'idlote', 'status', $i_ativ_idlote);
     //die($status.'teste');
    if($status=='PROCESSANDO' OR $status=='FORMALIZACAO' ){    
            geraAmostrasRelacionadasAoLote($i_ativ_idlote);
    }
}
/*
function geraamostrapos($i_idanalise){
    
    $sqlu="select idunidade from unidade where idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]." and cq='Y'";
    $resu = d::b()->query($sqlu) or die("geraAmostrasRelacionadasAoLote erro ao buscar unidade de CQ: ".mysqli_error(d::b()));
    $rowu=mysqli_fetch_assoc($resu);

    $idunidade=$rowu['idunidade'];
    
    //$idunidade=2;
     
    $sql="select ba.idprodserv,c.idservicobioterio,c.dia,s.idservicoensaio,e.qtd,e.produto,e.partida,e.idnucleo,e.nascimento,s.data, DATEDIFF(s.data,e.nascimento) AS idade,e.idpessoa,e.idespeciefinalidade,n.nucleo
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
    
    $res=d::b()->query($sql) or die("[saveposchange]- Erro ao buscar informacoes para gerar as amostras e testes: ".mysqli_error(d::b())."<p>SQL: ".$sql);
    while($row=mysqli_fetch_assoc($res)){
        
        if($row["idservicoensaio"]!=$idservicoensaio){
            $idservicoensaio=$row["idservicoensaio"];
            $idamostra=$row['idamostra'];
        }
        
        
        if(empty($idamostra)){
            
            //GERA A AMOSTRA
            $arrReg=geraIdregistro($_SESSION["SESSAO"]["IDEMPRESA"],$idunidade);
            $insamostra = new Insert();
            $insamostra->setTable("amostra");
            $insamostra->idunidade=$idunidade;
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

        $sqlu="update servicoensaio set idamostra =".$idamostra."
                                        where idservicoensaio =".$row["idservicoensaio"];
        d::b()->query($sqlu) or die("[saveposchange]- Erro ao atualizar amostra no servicoensaio sql=".$sqlu);

        }//if(empty($row['idamostra'])){
        
        if(empty($idamostra)){
           echo('não gerado ID amostra'); die;
        }
        
          $sqlinsereitens = "insert into resultado (
                                                        idamostra
                                                        ,idempresa
                                                        ,idtipoteste
                                                        ,idservicoensaio
                                                        ,quantidade
                                                        ,status
                                                        ,criadopor
                                                        ,criadoem
                                                        ,alteradopor
                                                        ,alteradoem
                                                        )
                                           select ".
                                                           $idamostra."
                                                        , ".$_SESSION["SESSAO"]["IDEMPRESA"]."
                                                        ,".$row['idprodserv']."
                                                        ,".$row['idservicoensaio']."
                                                        ,".$row['qtd']."
                                                        ,'AGUARDANDO'
                                                        ,'".$_SESSION["SESSAO"]["USUARIO"]."'
                                                        ,now()
                                                        ,'".$_SESSION["SESSAO"]["USUARIO"]."'
                                                        ,now()";
        
        $res1 = d::b()->query($sqlinsereitens);
        if(!$res1){
             echo("[saveposchange]- Erro inserindo teste: ".mysqli_error(d::b())." sql=".$sqlinsereitens);
        }
        //mysqli_query("COMMIT") or die("Falha ao efetuar COMMIT [sequence update]: ".mysqli_error(d::b()));
	
        
    }//while($row=mysqli_fetch_assoc($res)){
}//function geraamostrapos($i_idanalise){
*/

// alterar cliente do nucleo
$_uidpessoa=$_SESSION['arrpostbuffer']['1']['u']['bioensaio']['idpessoa'];
$_uidnucleo=$_SESSION['arrpostbuffer']['1']['u']['bioensaio']['idnucleo'];
$_uidpessoaant=$_POST['bioensaio_idpessoa'];// Variavel para gerar os testes;
if(!empty($_uidpessoa) and $_uidpessoaant != $_uidpessoa and !empty($_uidnucleo)){
     $su="update nucleo set idpessoa=".$_uidpessoa."
                    where idnucleo=".$_uidnucleo;
    $ru=d::b()->query($su) or die("[saveposchange]-Erro ao atualizar cliente do nucleo: ".mysqli_error(d::b())."<p>SQL: ".$su);
         
}

//print_r($_SESSION['arrpostbuffer']['x']['u']['servicoensaio']); die;
$iu = $_SESSION['arrpostbuffer']['1']['i']['bioensaio']['idpessoa'] ? 'i' : 'u';
/*
//gera os serviços da ficha de reproducao
if($iu=='i' and !empty($_SESSION["_pkid"]) and !empty($_SESSION['arrpostbuffer']['1']['i']['bioensaio']['idpessoa'])){
    //insere no localensaio
    $sqlin3="INSERT INTO localensaio
                            (idempresa,status,idbioensaio,criadopor,criadoem,alteradopor,alteradoem)
                    VALUES
                    (".$_SESSION["SESSAO"]["IDEMPRESA"].",'PENDENTE',".$_SESSION["_pkid"].",'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate(),'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate())";
    //echo($sqlin3); die;
    $resin3=d::b()->query($sqlin3) or die("[saveposchange_bioensaio]-Erro ao inserir no localensaio sql=".$sqlin3);
}
 
 */

//*COPIAR OS SERVICOS DA ANALISE PARA O CONTROLE DA ANALISE
/*
$idanalise = $_SESSION['arrpostbuffer']['setcontrole']['u']['analise']['idanalise'];
$idbioensaioctr = $_SESSION['arrpostbuffer']['setcontrole']['u']['analise']['idbioensaioctr'];
$i_idbioensaioant=$_POST['idbioensaioant'];// Variavel para gerar os testes;
//print_r($_SESSION['arrpostbuffer']);
if(!empty($idanalise)){
    
    if(!empty($idbioensaioctr)){
        $s="select idanalise from analise where  idanalisepai=".$idanalise;
        $r=d::b()->query($s) or die("[saveposchange]-Erro ao buscar analise gerada do controle: ".mysqli_error(d::b())."<p>SQL: ".$s);
        $qtdr= mysqli_num_rows($r);
        //echo($s);
        if($qtdr>0){
            $rw=mysqli_fetch_assoc($r); 
            $su="update analise a,analise aa set a.idbioterioanalise= aa.idbioterioanalise,a.datadzero=aa.datadzero 
                    where  a.idanalise=".$rw['idanalise']." and aa.idanalise=a.idanalisepai";
            $ru=d::b()->query($su) or die("[saveposchange]-Erro ao atualizar analise gerada do bioensaio: ".mysqli_error(d::b())."<p>SQL: ".$su);
            
            $id_analisectr= $rw['idanalise'];
            
            $sqld1="delete r.*  
                from servicoensaio s,resultado r
                where s.idobjeto=".$id_analisectr."
		and s.status = 'PENDENTE'
		and  s.tipoobjeto='analise'
                and r.idservicoensaio = s.idservicoensaio";
            d::b()->query($sqld1) or die("[saveposchange]- Falha ao deletar resultados : ".mysqli_error(d::b())."<p>SQL: ".$sqld1);
             
            $sql="delete from servicoensaio where idobjeto= ".$id_analisectr." and tipoobjeto ='analise' and status!='CONCLUIDO'";
            $res=d::b()->query($sql) or die("3-Falha ao deletar reiniciar servicos servicoensaio: ".mysqli_error(d::b())."<p>SQL: ".$sql);
        }else{        
        
            $sql="INSERT INTO analise
                (idempresa,idbioterioanalise,descr,idobjeto,objeto,datadzero,idanalisepai,status,criadopor,criadoem,alteradopor,alteradoem)
                (select idempresa,idbioterioanalise,descr,".$idbioensaioctr.",'bioensaio',datadzero,".$idanalise.",status,
                    '".$_SESSION["SESSAO"]["USUARIO"]."',now(),'".$_SESSION["SESSAO"]["USUARIO"]."',now() 
                from analise
                where idanalise =".$idanalise." )";
            $res=d::b()->query($sql) or die("[saveposchange]- Falha ao gerar analise controle: ".mysqli_error(d::b())."<p>SQL: ".$sql);
            $id_analisectr= mysqli_insert_id(d::b());
            if(!$res){
                echo "[saveposchange]- Falha ao gerar a analise para o controle do bioensaio [bionsaio] : " . mysql_error() . "<p>SQL: $sql";
                die();
            }
        }
        
        if(empty($id_analisectr)){ die('Nao gerada ou nao encontrada analise do controle');}
        
        $sql="INSERT INTO servicoensaio
                    (idempresa,idobjeto,tipoobjeto,idservicobioterio,servico,
                    dia,data,diazero,obs,status,idservicoensaioctr,criadopor,criadoem,alteradopor,alteradoem)
                    (select s.idempresa,".$id_analisectr.",s.tipoobjeto,s.idservicobioterio,s.servico,
                        s.dia,s.data,s.diazero,s.obs,'PENDENTE',s.idservicoensaio,'".$_SESSION["SESSAO"]["USUARIO"]."',now(),'".$_SESSION["SESSAO"]["USUARIO"]."',now()  
                    from  servicoensaio s,servicobioterio sb  
                        where s.idobjeto= ".$idanalise." 
                        and s.tipoobjeto ='analise'
                        and s.idservicobioterio=sb.idservicobioterio
                        and sb.controle ='Y'
                    )";
        $res=d::b()->query($sql) or die("[saveposchange]- Falha ao inserir servico ensaio ".mysqli_error(d::b())."<p>SQL: ".$sql);
        
        geraamostrapos($id_analisectr);

    }elseif(!empty($i_idbioensaioant) and empty($idbioensaioctr)){
        
        $s="select idanalise from analise where  idanalisepai=".$idanalise;
        $r=d::b()->query($s) or die("[saveposchange]- erro 2 ao buscar possivel analise do controle: ".mysqli_error(d::b())."<p>SQL: ".$s);
        $qtdr= mysqli_num_rows($r);
        //echo($s);
        if($qtdr>0){
            $rw=mysqli_fetch_assoc($r); 
            $su="delete from analise where  idanalise=".$rw['idanalise'];
            $ru=d::b()->query($su) or die("[saveposchange]-  Erro 2 ao excluir analise: ".mysqli_error(d::b())."<p>SQL: ".$su);
           
            $sql="delete from servicoensaio where idobjeto= ".$rw['idanalise']." and tipoobjeto ='analise' and status!='CONCLUIDO'";
            $res=d::b()->query($sql) or die("[saveposchange]-  Falha 2 ao excluir servicoensaio: ".mysqli_error(d::b())."<p>SQL: ".$sql);
        }
        $_SESSION['arrpostbuffer']['setcontrole']['u']['analise']['idbioensaioctr']='null';
        
    }//if(!empty($idbioensaioctr)){    
}//if(!empty($idanalise)){
//*FIM COPIAR OS SERVICOS DA ANALISE PARA O CONTROLE DA ANALISE


//abre variavel com a acao que veio da tela
$i_idanalise = $_SESSION['arrpostbuffer']['x']['u']['analise']['idanalise'];
$idbioterioanalise = $_SESSION['arrpostbuffer']['x']['u']['analise']['idbioterioanalise'];
//se for um update na analise deve atulizar as amostras
if(!empty($i_idanalise) and !empty($idbioterioanalise)){    
   geraamostrapos($i_idanalise);
}
 
 */