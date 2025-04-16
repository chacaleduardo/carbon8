<?
//objetos: "_x_u_analise_idanalise=" +idanalise + "&_x_u_analise_datadzero=" + $("[name=datadzero" + linha + "]").val() + "&_x_u_analise_idbioterioanalise=" + idbioterioanalise+ "&idloteativ="+idloteativ



if( !empty($_POST['analise_idobjeto'])   and  !empty($_POST['analise_objeto']) ){
 //'analise_idobjeto"]=" + idbioensaio + "&analise_objeto=bioensai

    $insnf = new Insert();
    $insnf->setTable("analise");
    $insnf->objeto = $_POST['analise_objeto'];
    $insnf->idobjeto = $_POST['analise_idobjeto'];
    $insnf->idempresa= cb::idempresa();
    //print_r($insnf); die;
    $idanalise = $insnf->save();
    $_SESSION['arrpostbuffer']['x']['u']['analise']['idanalise']= $idanalise;



    //$idanalise = $_SESSION['arrpostbuffer']['x']['u']['analise']['idanalise'];

    $datadzero = $_SESSION['arrpostbuffer']['x']['u']['analise']['datadzero'];
    $idbioterioanalise = $_SESSION['arrpostbuffer']['x']['u']['analise']['idbioterioanalise'];
    $idloteativ = $_POST['idloteativ'];


    if(!empty($idanalise) and !empty($datadzero) and !empty($idbioterioanalise) and !empty($idloteativ)){
        $idempresa= cb::idempresa();
        $idlote= traduzid('loteativ', 'idloteativ', 'idlote', $idloteativ);
        $partida= traduzid('lote', 'idlote', 'partida', $idlote);
        $exercicio= traduzid('lote', 'idlote', 'exercicio', $idlote);
        $idpessoaform= traduzid('empresa', 'idempresa', 'idpessoaform',$idempresa);
    

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
                    set e.doses=b.pddose,e.volume=b.pdvolume,e.via=b.pdvia,e.idloteativ=".$idloteativ.",e.idlotepd=".$idlote.",e.estudo= concat(b.tipoanalise,' - ','".$partida."','/','".$exercicio."'),e.idpessoa=".$idpessoaform."
                    where a.idanalise=".$idanalise."
                    and e.idbioensaio = a.idobjeto
                    and a.objeto ='bioensaio'
                    and b.cria='N'
                    and b.idbioterioanalise =".$idbioterioanalise; 
            $rs=d::b()->query($s) or die("[saveprechange]- Erro ao atualizar padroes da analise na [saveprechange__bioensaio]: ".mysqli_error(d::b())."<p>SQL: ".$s);

          //die($s);
        }

    }

}



?>