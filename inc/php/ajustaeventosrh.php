<?
//Hermes pedro 20-05-2021
require_once("functions.php");
require_once("folha.php");

$idrhfolha =$_GET['idrhfolha'];

if(empty($idrhfolha)){
    die("Não foi possivel identificar a Folha para execução.");
}

$grupo = rstr(8);

re::dis()->hMSet('cron:ajustaponto',['inicio' => Date('d/m/Y H:i:s')]);

$sqli = "INSERT INTO `laudo`.`log` (`idempresa`, `sessao`, `tipoobjeto`, `idobjeto`, `tipolog`, `log`, `status`, `criadoem`, `data`) 
							VALUES ('1', '".$grupo ."','cron', 'ajustaponto', 'status', 'INICIO', 'SUCESSO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";

d::b()->query($sqli) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqli);

$sqlx="select i.idpessoa,a.contrato,r.tipofolha
                from rhfolha r join
                rhfolhaitem i on(i.idrhfolha=r.idrhfolha) join pessoa a on(a.idpessoa = i.idpessoa)
                where r.idrhfolha=".$idrhfolha;
$resx = d::b()->query($sqlx) or die("Erro1 - ajusta eventos rh buscar funcionarios ".mysqli_error(d::b())."\n".$sqlx);

while($rowx=mysqli_fetch_assoc($resx)){

    if($rowx['contrato']=='CLT' and $rowx['tipofolha']=='FOLHA'){
        $sql="select e.dataevento,e.idpessoa,e.idempresa
                from rhevento e,rhtipoevento t
                where e.status='PENDENTE' 
                and e.idpessoa is not null
                and e.dataevento is not null
            -- and e.idpessoa = 794
            -- and e.dataevento='2020-11-04'
                and t.idrhtipoevento=e.idrhtipoevento
                and t.flgponto='Y'
                and t.status='ATIVO'
                and e.idpessoa = ".$rowx['idpessoa']."
                -- and  e.hora is not null    
                and t.formato in ('HI','HIF','H')
                and t.flhtotais = 'N' and t.flhtotaisajust  = 'N' and t.flhext  = 'N' and t.flhextcalc  = 'N'
                and e.dataevento< DATE_FORMAT(sysdate(),'%Y-%m-%d') 
                and e.alteradoem > DATE_SUB(sysdate(), INTERVAL 3 DAY)
                group by e.dataevento,e.idpessoa";
        $res = d::b()->query($sql) or die("Erro1 - Crom ajusta ponto ".mysqli_error(d::b())."\n".$sql);

        while($row=mysqli_fetch_assoc($res)){
            
            $timestampdt = strtotime($row['dataevento']);
            $dia= date("d", $timestampdt);
            $mes= date("m", $timestampdt);
            $ano= date("Y", $timestampdt);

            $folha = new Folha($dia,$dia,$mes,$ano,$row['idpessoa']);
            $_idempresa=traduzid('pessoa','idpessoa','idempresa',$row['idpessoa']);

            $calendario=$folha->getCalendario($_idempresa); 

            $horasexec=$folha->gethorasExec();

            $horasplan=$folha->getHorasPlan();
            
            $horas=$folha->gethoras();
            

            //echo("Horas realizadas=".$horasexec['hora']);
        // echo("Horas planejadas=".$horasplan['hora']);
        /*  
        echo('Horas='.$horasexec['hora'].'<br>');
        echo('H ajustada='.$horas['horaajustada'].'<br>');
        echo('H Extra='.$horas['horaextra'].'<br>');
        echo('H Extra dinheiro='.$horas['horaextradinheiro'].'<br>');
        echo('H Extra dinheiro dsr='.$horas['dsrhoraextra'].'<br>');
        echo('Diastrab extra='.$horas['diastrab'].'<br>');
        */




            $horasextras = $horasexec['hora']-$horasplan['hora'];
            
            //-- horas totais
            $s1="insert INTO rhevento(dataevento,idpessoa,status,idrhtipoevento,idempresa,criadopor,criadoem,alteradopor,alteradoem)
                (select '".$row['dataevento']."',".$row['idpessoa'].",'PENDENTE',(select idrhtipoevento from rhtipoevento where status='ATIVO' and flhtotais='Y'  ),".$row['idempresa'].",'ajustaeventosrh',now(),'ajustaeventosrh',now()
                where not exists (
                        SELECT 1 FROM rhevento e,rhtipoevento t WHERE e.dataevento='".$row['dataevento']."'  and e.idpessoa=".$row['idpessoa']." and t.idrhtipoevento = e.idrhtipoevento and t.flhtotais='Y' and t.status='ATIVO'
                )    LIMIT 1)";
            $r1 = d::b()->query($s1) or die("Error 2  Crom ajusta ponto".$s1);

            // -- horas planejadas ajustadas
            $s2="insert INTO rhevento(dataevento,idpessoa,status,idrhtipoevento,idempresa,criadopor,criadoem,alteradopor,alteradoem)
                (select '".$row['dataevento']."',".$row['idpessoa'].",'PENDENTE',(select idrhtipoevento from rhtipoevento where status='ATIVO' and flhtotaisajust='Y' ),".$row['idempresa'].",'ajustaeventosrh',now(),'ajustaeventosrh',now()
                where not exists (
                        SELECT 1 FROM rhevento e,rhtipoevento t WHERE e.dataevento='".$row['dataevento']."'  and e.idpessoa=".$row['idpessoa']." and t.idrhtipoevento = e.idrhtipoevento and t.flhtotaisajust='Y' and t.status='ATIVO'
                )  LIMIT 1)";
            $r2 = d::b()->query($s2) or die("Error 3  Crom ajusta ponto".$s2);

            // -- horas extras                        
            $s3="insert INTO rhevento(dataevento,situacao,idpessoa,status,idrhtipoevento,idempresa,criadopor,criadoem,alteradopor,alteradoem)
                (select '".$row['dataevento']."','P',".$row['idpessoa'].",'PENDENTE',(select idrhtipoevento from rhtipoevento where status='ATIVO' and flhext='Y' ),".$row['idempresa'].",'ajustaeventosrh',now(),'ajustaeventosrh',now()
                where not exists (
                        SELECT 1 FROM rhevento e,rhtipoevento t WHERE e.dataevento='".$row['dataevento']."'  and e.idpessoa=".$row['idpessoa']." and t.idrhtipoevento = e.idrhtipoevento and t.flhext='Y' and t.status='ATIVO'
                ) LIMIT 1)";
            $r3 = d::b()->query($s3) or die("Error 4  Crom ajusta ponto".$s3);
            /*
                // -- dias trabalhados                        
                $s3="insert INTO rhevento(dataevento,idpessoa,status,idrhtipoevento,idempresa)
                (select '".$row['dataevento']."',".$row['idpessoa'].",'PENDENTE',(select idrhtipoevento from rhtipoevento where status='ATIVO' and flgdiastrab='Y' ),".$row['idempresa']."
                where not exists (
                        SELECT 1 FROM rhevento e,rhtipoevento t WHERE e.dataevento='".$row['dataevento']."'  and e.idpessoa=".$row['idpessoa']." and t.idrhtipoevento = e.idrhtipoevento and t.flgdiastrab='Y' and t.status='ATIVO'
                ) LIMIT 1)";
                $r3 = d::b()->query($s3) or die("Error dias trab  Crom ajusta ponto".$s3);
*/
            // dinheiro horas extrasflhextcalc
            $s3="insert INTO rhevento(dataevento,idpessoa,status,idrhtipoevento,idempresa,criadopor,criadoem,alteradopor,alteradoem)
            (select '".$row['dataevento']."',".$row['idpessoa'].",'PENDENTE',(select idrhtipoevento from rhtipoevento where status='ATIVO' and flhextcalc='Y' ),".$row['idempresa'].",'ajustaeventosrh',now(),'ajustaeventosrh',now()
            where not exists (
                    SELECT 1 FROM rhevento e,rhtipoevento t WHERE e.dataevento='".$row['dataevento']."'  and e.idpessoa=".$row['idpessoa']." and t.idrhtipoevento = e.idrhtipoevento and t.flhextcalc='Y' and t.status='ATIVO'
            ) LIMIT 1)";
            $r3 = d::b()->query($s3) or die("Error 5  Crom ajusta ponto".$s3);
            
            // dinheiro DSR horas extras
            $s3="insert INTO rhevento(dataevento,situacao,idpessoa,status,idrhtipoevento,idempresa,criadopor,criadoem,alteradopor,alteradoem)
            (select '".$row['dataevento']."','P',".$row['idpessoa'].",'PENDENTE',51,".$row['idempresa'].",'ajustaeventosrh',now(),'ajustaeventosrh',now()
            where not exists (
                    SELECT 1 FROM rhevento e,rhtipoevento t WHERE e.dataevento='".$row['dataevento']."'  and e.idpessoa=".$row['idpessoa']." and t.idrhtipoevento = e.idrhtipoevento and t.idrhtipoevento=51 and t.status='ATIVO'
            )  LIMIT 1)";
            $r3 = d::b()->query($s3) or die("Error 6  Crom ajusta ponto".$s3);


            // adicional noturno
            $s3="insert INTO rhevento(dataevento,idpessoa,status,idrhtipoevento,idempresa,criadopor,criadoem,alteradopor,alteradoem)
            (select '".$row['dataevento']."',".$row['idpessoa'].",'PENDENTE',428,".$row['idempresa'].",'ajustaeventosrh',now(),'ajustaeventosrh',now()
            where not exists (
                    SELECT 1 FROM rhevento e,rhtipoevento t WHERE e.dataevento='".$row['dataevento']."'  and e.idpessoa=".$row['idpessoa']." and t.idrhtipoevento = e.idrhtipoevento and t.idrhtipoevento=428 and t.status='ATIVO'
            )  LIMIT 1)";
            $r3 = d::b()->query($s3) or die("Error 7  Crom ajusta ponto adicional noturno".$s3);
            
            $s1="update rhevento e join rhtipoevento t set e.valor='".$horasexec['hora']."'
                    where e.idpessoa = ".$row['idpessoa']."
                    and t.status='ATIVO' and t.flhtotais='Y' and t.idrhtipoevento =e.idrhtipoevento
                    and e.dataevento='".$row['dataevento']."'";
            $r1 = d::b()->query($s1) or die("Error 5 Crom ajusta ponto".$s1);
            
            $s1="update rhevento e join rhtipoevento t set e.valor='".$horas['horaajustada']."'
                    where e.idpessoa = ".$row['idpessoa']."
                    and t.status='ATIVO' and t.flhtotaisajust='Y' and t.idrhtipoevento =e.idrhtipoevento
                   and e.dataevento='".$row['dataevento']."'";
            $r1 = d::b()->query($s1) or die("Error 6 Crom ajusta ponto".$s1);

       /*       dias trabalhados inativo
         $s1="update rhevento e join rhtipoevento t set e.valor='".$horas['diastrab']."'
                        where e.idpessoa = ".$row['idpessoa']."
                        and t.status='ATIVO' and t.flgdiastrab='Y' and t.idrhtipoevento =e.idrhtipoevento
                        and e.dataevento='".$row['dataevento']."'";
                $r1 = d::b()->query($s1) or die("Error 6 Crom ajusta ponto".$s1);
           */ 
            
            $s1="update rhevento e join rhtipoevento t set e.valor='".$horas['horaextra']."'
                    where e.idpessoa = ".$row['idpessoa']."
                    and t.status='ATIVO' and t.flhext='Y' and t.idrhtipoevento =e.idrhtipoevento
                    and e.dataevento='".$row['dataevento']."'";
            $r1 = d::b()->query($s1) or die("Error 7   Crom ajusta ponto".$s1);

            $s1="update rhevento e join rhtipoevento t set e.valor='".$horas['dinheirohoraextra']."'
                    where e.idpessoa = ".$row['idpessoa']."
                    and t.status='ATIVO' and t.flhextcalc='Y' and t.idrhtipoevento =e.idrhtipoevento                  
                    and e.dataevento='".$row['dataevento']."'";
            $r1 = d::b()->query($s1) or die("Error 8 Crom ajusta ponto".$s1);
        
            $s1="update rhevento e join rhtipoevento t set e.valor='".$horas['dsrhoraextra']."'
                    where e.idpessoa = ".$row['idpessoa']."
                    and t.status='ATIVO' and t.idrhtipoevento=51 and t.idrhtipoevento =e.idrhtipoevento                  
                    and e.dataevento='".$row['dataevento']."'";
            $r1 = d::b()->query($s1) or die("Error 8 Crom ajusta ponto".$s1);

                // adicional noturno
                $s1="update rhevento e join rhtipoevento t set e.valor='".$horasexec['horanot']."'
                where e.idpessoa = ".$row['idpessoa']."
                and t.status='ATIVO' and t.idrhtipoevento=428 and t.idrhtipoevento =e.idrhtipoevento              
                and e.dataevento='".$row['dataevento']."'";
                $r1 = d::b()->query($s1) or die("Error 9 Crom ajusta ponto Adicional noturno".$s1);

            
        }//while($row=mysqli_fetch_assoc($res)){
    }//if($rowx['contrato']=='CLT'){

        // ATULIZAR SOMATORIO DE HORAS PARA MOSTRAR UM UNICO EVENTO
            $sql="update rhtipoevento t join  rhevento e on(e.idrhtipoevento=t.idrhtipoevento and  e.status='PENDENTE')
                set e.valor='0.00'
                where t.flgsomatorio='Y' and t.status='ATIVO'
                and t.idrhtipoeventosum is not null
                and e.idpessoa=".$rowx['idpessoa']."
                and t.status='ATIVO'";
            $res = d::b()->query($sql) or die("Zerar  somatorio do ponto".$sql);

            $sql1="select
                        LAST_DAY(e.dataevento) as dataeventos,t.idrhtipoevento,e.idpessoa,sum(e.valor) as valor,e.idempresa
                    from rhtipoevento t,rhevento e
                    where t.flgsomatorio='Y'
                    and t.status='ATIVO'
                    and t.idrhtipoeventosum is not null
                    and t.idrhtipoeventosum !=23
                    and e.idpessoa is not null
                    and e.valor is not null
                    and e.status='PENDENTE'
                    and e.situacao='A'
                    and e.dataevento<= DATE_FORMAT(sysdate(),'%Y-%m-%d')
                    and e.idrhtipoevento = t.idrhtipoeventosum
                    and t.status='ATIVO'
                    and e.idpessoa=".$rowx['idpessoa']."
                    group by dataeventos,e.idrhtipoevento,e.idpessoa";
            $res1 = d::b()->query($sql1) or die("Error 8 Crom ajusta ponto somatorio de eventos".$sql1);
            while($row=mysqli_fetch_assoc($res1)){

                // -- criar evento para somar as horas                 
                $s3="insert INTO rhevento(dataevento,idpessoa,valor,status,idrhtipoevento,idempresa,criadopor,criadoem,alteradopor,alteradoem)
                        (select '".$row['dataeventos']."',".$row['idpessoa'].",'".$row['valor']."','PENDENTE',".$row['idrhtipoevento'].",".$row['idempresa'].",'ajustaeventosrh',now(),'ajustaeventosrh',now()
                            where not exists (
                                SELECT 1 FROM rhevento e WHERE e.dataevento='".$row['dataeventos']."'  and e.idpessoa=".$row['idpessoa']." and e.idrhtipoevento = ".$row['idrhtipoevento']." and e.status='PENDENTE'
                        ) LIMIT 1)";
                $r3 = d::b()->query($s3) or die("Error 9 insert Crom ajusta ponto".$s3);
                
                $s1="update rhevento e  set e.valor='".$row['valor']."'
                        where e.idpessoa = ".$row['idpessoa']."
                        and e.idrhtipoevento=".$row['idrhtipoevento']."                          
                        and e.dataevento='".$row['dataeventos']."' and e.status='PENDENTE'"
                    ;
                $r1 = d::b()->query($s1) or die("Error 10 Update Crom ajusta ponto".$s1);
            }

            $sql1="select
                         max(LAST_DAY(e.dataevento)) as dataeventos,                     
                        t.idrhtipoevento,e.idpessoa,sum(e.valor) as valor,e.idempresa
                        from rhtipoevento t,rhevento e
                        where t.flgsomatorio='Y'
                        and t.status='ATIVO'
                        and t.idrhtipoeventosum =23
                        and e.idpessoa is not null
                        and e.valor is not null
                        and e.status='PENDENTE'
                        and e.situacao='A'
                        and e.dataevento<= DATE_FORMAT(sysdate(),'%Y-%m-%d')
                        and e.idrhtipoevento = t.idrhtipoeventosum
                        and t.status='ATIVO'
                        and e.idpessoa=".$rowx['idpessoa']."
                        group by e.idrhtipoevento,e.idpessoa";

/*
(select datafim from rhfolhaitem i join rhfolha r on(r.idrhfolha=i.idrhfolha and r.status= 'ABERTA')
                                where i.idpessoa=e.idpessoa order by datafim limit 1) as datafolha
*/

                $res1 = d::b()->query($sql1) or die("Error 8 Crom ajusta ponto somatorio de eventos".$sql1);
                while($row=mysqli_fetch_assoc($res1)){
/*
                        if(empty($row['datafolha'])){
                                $row['datafolha']=$row['dataeventos'];
                        }
*/
                // -- criar evento para somar as horas                 
                $s3="insert INTO rhevento(dataevento,idpessoa,valor,status,idrhtipoevento,idempresa,criadopor,criadoem,alteradopor,alteradoem)
                        (select '".$row['dataeventos']."',".$row['idpessoa'].",'".$row['valor']."','PENDENTE',".$row['idrhtipoevento'].",".$row['idempresa'].",'ajustaeventosrh',now(),'ajustaeventosrh',now()
                                where not exists (
                                SELECT 1 FROM rhevento e WHERE e.dataevento='".$row['dataeventos']."'  and e.idpessoa=".$row['idpessoa']." and e.idrhtipoevento = ".$row['idrhtipoevento']." and e.status='PENDENTE'
                        ) LIMIT 1)";
                $r3 = d::b()->query($s3) or die("Error 9 insert Crom ajusta ponto".$s3);
                
                $s1="update rhevento e  set e.valor='".$row['valor']."'
                        where e.idpessoa = ".$row['idpessoa']."
                        and e.idrhtipoevento=".$row['idrhtipoevento']."                          
                        and e.dataevento='".$row['dataeventos']."' and e.status='PENDENTE'"
                        ;
                $r1 = d::b()->query($s1) or die("Error 10 Update Crom ajusta ponto".$s1);
                }
}//while($rowx=mysqli_fetch_assoc($resx)){

re::dis()->hMSet('cron:ajustaponto',['fim' => Date('d/m/Y H:i:s')]);

$sqli = "INSERT INTO `laudo`.`log` (`idempresa`, `sessao`, `tipoobjeto`, `idobjeto`, `tipolog`, `log`, `status`, `criadoem`, `data`) 
							VALUES ('1', '".$grupo ."','cron', 'ajustaponto', 'status', 'FIM', 'SUCESSO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";

d::b()->query($sqli) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqli);


?>