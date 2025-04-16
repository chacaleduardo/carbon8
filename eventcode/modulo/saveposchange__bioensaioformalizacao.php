<?
require_once(__DIR__."/../../form/controllers/fluxo_controller.php");

function geraamostrapos($i_idanalise){
    
	/*
	 * Alteração realizada para setar a unidade correta
	 * sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=313400
	 * Lidiane - 27/04/2020
	 */ 
    $sqlu="select u.idunidade,t.idtipounidade,t.tipounidade, case when t.idtipounidade = 12 then  10 when t.idtipounidade = 17 then 14 else 10 end as destino
    from unidade u join tipounidade t on (u.idtipounidade = t.idtipounidade) where 1 ".getidempresa("u.idempresa","bioensaio");
    $resu = d::b()->query($sqlu) or die("geraAmostrasRelacionadasAoLote erro ao buscar unidade de P&D: ".mysqli_error(d::b()));
    $rowu=mysqli_fetch_assoc($resu);
    $idunidade=$rowu['destino'];
    
    //$idunidade=2;
     
    $sql="SELECT
            ba.idprodserv,
            c.idservicobioterio,
            c.dia,s.idservicoensaio,
            e.idbioensaio,
            e.qtd,
            e.produto,
            e.partida,
            e.idnucleo,
            e.nascimento,
            s.data,
            DATEDIFF(s.data,e.nascimento) AS idade,
            e.idpessoa,
            e.idespeciefinalidade,
            n.nucleo,
            sb.idsubtipoamostra,
            s.idamostra,
            e.estudo,
            e.idregistro
        from analise a
        join servicobioterioconf c on (c.idobjeto=a.idbioterioanalise and c.tipoobjeto='bioterioanalise')
        join bioterioanaliseteste ba on (ba.idservicobioterioconf = c.idservicobioterioconf)
        JOIN servicoensaio s on (s.idservicobioterio=c.idservicobioterio and s.dia=c.dia and s.idobjeto=a.idanalise and s.tipoobjeto = 'analise')
        left JOIN bioensaio e on (e.idbioensaio = a.idobjeto)
        left JOIN nucleo n on (e.idnucleo= n.idnucleo)
        JOIN servicobioterio sb on (s.idservicobioterio = sb.idservicobioterio)
        where a.objeto = 'bioensaio'
        and a.idanalise= ".$i_idanalise."
        order by s.dia, idservicoensaio";
    
    $res=d::b()->query($sql) or die("[saveposchange]- Erro ao buscar informacoes para gerar as amostras e testes: ".mysqli_error(d::b())."<p>SQL: ".$sql);
    $idservicoensaio = "";

    while($row=mysqli_fetch_assoc($res)){
        
        if($row["idservicoensaio"]!=$idservicoensaio){
            $idservicoensaio=$row["idservicoensaio"];
            $idamostra=$row['idamostra'];
        }
        
        
        if(empty($idamostra)){

            //LTM - 13-04-2021: Retorna o Idfluxo Amostra
		    $idfluxostatus = FluxoController::getIdFluxoStatus('amostra', 'ABERTO', $idunidade);	
            $exerciciobioensaio = traduzid('bioensaio','idbioensaio','exercicio',$row['idbioensaio']);

            $arrReg=geraIdregistro(cb::idempresa(),$idunidade,$exerciciobioensaio);

            $idnucleo = $row['idnucleo'];
            $nucleo = $row['nucleo'];
            if(!$idnucleo){
                $nucleo = 'B'.$row['idregistro'] . ' - ' . $row['estudo'];

                $ins = new Insert();
                $ins->setTable("nucleo");
                //$ins->idobjeto=$idlotepd; 
                $ins->idempresa=cb::idempresa(); 
                $ins->idpessoa=$row["idpessoa"]; 
                $ins->idunidade=$idunidade; 
                $ins->idespeciefinalidade=$row["idespeciefinalidade"]; 
                $ins->objeto='lote';
                $ins->nucleo= $nucleo;
                //$ins->lote=$row['lote'];
                $idnucleo=$ins->save();
                
                $_SESSION['arrpostbuffer']['1']['u']['bioensaio']['idnucleo'] = $idnucleo;
                $_SESSION['arrpostbuffer']['1']['u']['bioensaio']['idbioensaio'] = $row['idbioensaio'];
                montatabdef();
            }

            //GERA A AMOSTRA
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
            $insamostra->idnucleo=$idnucleo;
            $insamostra->tipoidade='Dia(s)';
            $insamostra->idade=$row['idade'];
            $insamostra->dataamostra=$row['data'];
            $insamostra->nucleoamostra=$nucleo;
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
            d::b()->query($sqlu) or die("[saveposchange]- Erro ao atualizar amostra no servicoensaio sql=".$sqlu);

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
                                                    ,".$row['idprodserv']."
                                                    ,".$row['idservicoensaio']."
                                                    ,".$row['qtd']."
                                                    ,'ABERTO'
                                                    ,'".$rowFluxo['idfluxostatus']."'
                                                    ,'".$_SESSION["SESSAO"]["USUARIO"]."'
                                                    ,now()
                                                    ,'".$_SESSION["SESSAO"]["USUARIO"]."'
                                                    ,now()";
        
        $res1 = d::b()->query($sqlinsereitens);
        if(!$res1){
             echo("[saveposchange]- Erro inserindo teste: ".mysqli_error(d::b())." sql=".$sqlinsereitens);
        }

        //LTM - 13-04-2021: Insere FluxoHist Resultado
        $idresultado = mysqli_insert_id(d::b());
        FluxoController::inserirFluxoStatusHist($rowFluxo['modulo'], $idresultado, $rowFluxo['idfluxostatus'], 'PENDENTE');

        //mysqli_query("COMMIT") or die("Falha ao efetuar COMMIT [sequence update]: ".mysqli_error(d::b()));
	
        
    }//while($row=mysqli_fetch_assoc($res)){
}//function geraamostrapos($i_idanalise){


//abre variavel com a acao que veio da tela
$i_idanalise = $_SESSION['arrpostbuffer']['x']['u']['analise']['idanalise'];
$idbioterioanalise = $_SESSION['arrpostbuffer']['x']['u']['analise']['idbioterioanalise'];
//se for um update na analise deve atulizar as amostras
if(!empty($i_idanalise) and !empty($idbioterioanalise)){    
   geraamostrapos($i_idanalise);
}

?>