<?
$idrhevento_ori=$_POST['_x_u_rhevento_idrhevento'];
$status_ori=$_POST['_x_u_rhevento_status'];
$idrhtipoevento_dest=$_POST['x_idrhtipoevento'];


//transferencia de valores entre eventos


//[_x_u_rhfolha_idrhfolha] => 8 
//[x_idrhtipoevento] => 23 
//[x_stridrhevento] => 328020,328266,328431,328824,329184,341797,344636,344640,344644,344648;
$inidrhevento_ori=$_POST['x_stridrhevento'];
$idrhfolha_ori=$_POST['_x_u_rhfolha_idrhfolha'];
$idrhtipoevento_dest=$_POST['x_idrhtipoevento'];

//print_r($_POST); die();

if( !empty($inidrhevento_ori) and !empty($idrhfolha_ori) and !empty($idrhtipoevento_dest) ){

   //$idrhtipoevento_ori=traduzid('rhevento', 'idrhevento', 'idrhtipoevento', $inidrhevento_ori);
    //echo($idrhtipoevento_ori); die();

    if($idrhtipoevento_dest!=435){// se  for ir para hora extra R$
        $sql2="select t.*          
        from rhtipoevento t
        where t.idrhtipoevento=".$idrhtipoevento_dest;
        $res2= d::b()->query($sql2) or die("saveprechange_rhtipoeventofolha- erro ao buscar evento: ". mysqli_error(d::b()));
        $row2=mysqli_fetch_assoc($res2);

            $sql="select round(
                                CASE t.formato
                                WHEN 'H' THEN ((((p.  salario + p.insalubridade)  / 200)*2)*e.valor) 
                                ELSE e.valor END
                            ,2) AS valor_dinheiro,e.*         
                    from rhevento e,rhtipoevento t,pessoa p
                    where e.idrhevento in(".$inidrhevento_ori.")
                    and e.idpessoa = p.idpessoa
                    and e.idrhtipoevento =t.idrhtipoevento";
            $res= d::b()->query($sql) or die("saveprechange_rhtipoeventofolha- erro ao buscar evento: ". mysqli_error(d::b()));
            $l=99;
            while($row=mysqli_fetch_assoc($res)){
                $l=$l+1;
                // if($row['valorconv']);
                $novo_valor=$row['valor_dinheiro']*$row2['valorconv'];

                //die($novo_valor);

                $sqt="select 
                        col,datatype
                        from "._DBCARBON."._mtotabcol 
                        where tab='rhevento' 
                        and primkey ='N' 
                        and col not in('alteradoem','alteradopor','criadoem','criadopor','idempresa','dataevento','idrhtipoevento','valor','idobjetoori','tipoobjetoori')";
                $ret= d::b()->query($sqt) or die("Erro ao buscar os campos na mtotabcol do rhevento sql=".$sqt);

                $insrhevento = new Insert();
                $insrhevento->setTable("rhevento");
                $insrhevento->tipoobjetoori= 'rhevento';
                $insrhevento->idrhtipoevento= $idrhtipoevento_dest;
                $insrhevento->idobjetoori= $row['idrhevento'];
                $insrhevento->valor= $novo_valor;
                $insrhevento->dataevento= $row['dataevento'];


                while($rowt=mysqli_fetch_assoc($ret)){

                    $valor=$row[$rowt['col']];
                    $campo=$rowt['col'];

                    if(!empty($valor)){

                        $valor= evaltipocoldb("rhevento", $rowt['col'], $rowt['datatype'],$valor);

                        $insrhevento->$campo= str_replace("'", "", $valor);  
                    }                    
                }//while($rowt=mysqli_fetch_assoc($ret)){ 

                //print_r($insrhevento); die;
                $new_idrhevento=$insrhevento->save();
                
                $_SESSION['arrpostbuffer'][$l]['u']['rhevento']['idrhevento']=$row['idrhevento'];
                $_SESSION['arrpostbuffer'][$l]['u']['rhevento']['status']="QUITADO TRANSFERENCIA";
              
                
            }//while($row=mysqli_fetch_assoc($res)){
                montatabdef();
    }else{// se for HE para dias trabalhados R$ 
       
    //se pra hora extra se trabalhássemos 44h semanais seria 07h20. 
    //Como trabalhamos 40h semanais, considerando 6 dias úteis = 40 : 6 = 6,66 -> 6,40
      /*  if($idrhtipoevento_dest==6){// hora extra
            

            $sql="select 
                        (e.valor*8) AS valor_dinheiro,e.*        
                    from rhevento e
                    where e.idrhevento in(".$inidrhevento_ori.")";

           // die($sql);
     
            $res= d::b()->query($sql) or die("saveprechange_rhtipoeventofolha- erro ao buscar evento: ". mysqli_error(d::b()));
            $l=99;
            while($row=mysqli_fetch_assoc($res)){
            $l=$l+1;
             
            $sqt="select 
                    col,datatype
                    from "._DBCARBON."._mtotabcol 
                    where tab='rhevento' 
                    and primkey ='N' 
                    and col not in('alteradoem','alteradopor','criadoem','criadopor','idempresa','dataevento','idrhtipoevento','valor','idobjetoori','tipoobjetoori')";
            $ret= d::b()->query($sqt) or die("Erro ao buscar os campos na mtotabcol do rhevento sql=".$sqt);

            $insrhevento = new Insert();
            $insrhevento->setTable("rhevento");
            $insrhevento->tipoobjetoori= 'rhevento';
            $insrhevento->idrhtipoevento= $idrhtipoevento_dest;
            $insrhevento->idobjetoori= $row['idrhevento'];
            $insrhevento->valor= $row['valor_dinheiro'];
            $insrhevento->dataevento= $row['dataevento'];


            while($rowt=mysqli_fetch_assoc($ret)){

                $valor=$row[$rowt['col']];
                $campo=$rowt['col'];

                if(!empty($valor)){

                    $valor= evaltipocoldb("rhevento", $rowt['col'], $rowt['datatype'],$valor);

                    $insrhevento->$campo= str_replace("'", "", $valor);  
                }                    
            }//while($rowt=mysqli_fetch_assoc($ret)){ 

            //print_r($insrhevento); die;
            $new_idrhevento=$insrhevento->save();

            $_SESSION['arrpostbuffer'][$l]['u']['rhevento']['idrhevento']=$row['idrhevento'];
            $_SESSION['arrpostbuffer'][$l]['u']['rhevento']['status']="QUITADO TRANSFERENCIA";
            

            }//while($row=mysqli_fetch_assoc($res)){
                montatabdef();

        }else{
            */
            // hora extra em dinheiro Como trabalhamos 40h semanais,
            // considerando 6 dias úteis = 40 : 6 = 6,66 -> 6,40

            $sql="select round( ((((p.  salario + p.insalubridade)/30)*2)),2) AS valor_dinheiro,e.*         
                from rhevento e,rhtipoevento t,pessoa p
                where e.idrhevento in(".$inidrhevento_ori.")
                and e.idpessoa = p.idpessoa
                and e.idrhtipoevento =t.idrhtipoevento";
     
            $res= d::b()->query($sql) or die("saveprechange_rhtipoeventofolha- erro ao buscar evento: ". mysqli_error(d::b()));
            $l=99;
            while($row=mysqli_fetch_assoc($res)){
            $l=$l+1;
           
            $sqt="select 
                    col,datatype
                    from "._DBCARBON."._mtotabcol 
                    where tab='rhevento' 
                    and primkey ='N' 
                    and col not in('alteradoem','alteradopor','criadoem','criadopor','idempresa','dataevento','idrhtipoevento','valor','idobjetoori','tipoobjetoori')";
            $ret= d::b()->query($sqt) or die("Erro ao buscar os campos na mtotabcol do rhevento sql=".$sqt);

            $insrhevento = new Insert();
            $insrhevento->setTable("rhevento");
            $insrhevento->tipoobjetoori= 'rhevento';
            $insrhevento->idrhtipoevento= $idrhtipoevento_dest;
            $insrhevento->idobjetoori= $row['idrhevento'];
            $insrhevento->valor= $row['valor_dinheiro'];
            $insrhevento->dataevento= $row['dataevento'];


            while($rowt=mysqli_fetch_assoc($ret)){

                $valor=$row[$rowt['col']];
                $campo=$rowt['col'];

                if(!empty($valor)){

                    $valor= evaltipocoldb("rhevento", $rowt['col'], $rowt['datatype'],$valor);

                    $insrhevento->$campo= str_replace("'", "", $valor);  
                }                    
            }//while($rowt=mysqli_fetch_assoc($ret)){ 

            //print_r($insrhevento); die;
            $new_idrhevento=$insrhevento->save();

            $_SESSION['arrpostbuffer'][$l]['u']['rhevento']['idrhevento']=$row['idrhevento'];
            $_SESSION['arrpostbuffer'][$l]['u']['rhevento']['status']="QUITADO TRANSFERENCIA";


            }
            montatabdef();

       //}
           
    }
 
}
//print_r( $_SESSION['arrpostbuffer']); die;