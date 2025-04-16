<?
//print_r($_POST); die;
$idrhevento=$_POST["_1_u_rhevento_idrhevento"];

$_parcelas=$_POST["_1_u_rhevento_parcelas"];
$tipointervalo=$_POST["rhevento_tipointervalo"];
$intervalo=$_POST["rhevento_intervalo"];
$flgfimdesemana=$_POST["rhevento_flgfimdesemana"];
$dataevento=$_POST["_1_u_rhevento_dataevento"];

$hora=$_POST["_1_u_rhevento_hora"];
$horaf=$_POST["_1_u_rhevento_horaf"];
$dataeventof=$_POST["_1_u_rhevento_dataeventof"];

$flgfimdesemana=$_POST["rhevento_flgfimdesemana"];

//multiplicar o evento do tipo moeda
if(!empty($idrhevento) 
        and !empty($_parcelas) 
            and !empty($tipointervalo) 
                and !empty($intervalo) 
                    and !empty($dataevento)){
    //die($flgfimdesemana);
 
  
    if($_parcelas > 1){

        $vintervalo = 0;
        for($i = 2; $i <= $_parcelas; $i++){
            $vintervalo = $vintervalo +  $intervalo;			
            if($tipointervalo=="M"){
                    $strintervalo= 'MONTH';
            }elseif($tipointervalo=="Y"){
                    $strintervalo= 'YEAR';
            }else{
                    $strintervalo= 'DAY';
            }
            //  calcula a data
            $sqc= "select DATE(DATE_ADD(STR_TO_DATE('".$dataevento."', '%d/%m/%Y'), INTERVAL ".$vintervalo." ".$strintervalo.")) as dataeventocal,
                    WEEKDAY(DATE(DATE_ADD(STR_TO_DATE('".$dataevento."', '%d/%m/%Y'), INTERVAL ".$vintervalo." ".$strintervalo."))) as diasemana";
            $resc= d::b()->query($sqc) or die("Erro ao calcular nova da para o evento sql=".$sqc);
            $rowc=mysqli_fetch_assoc($resc);
            
            if(
                (($rowc['diasemana']==5 or $rowc['diasemana']==6) and !empty($flgfimdesemana)) 
                    or 
                (($rowc['diasemana']!=5 and $rowc['diasemana']!=6)) 
                ){
               

                $sqt="select 
                        col,datatype
                        from "._DBCARBON."._mtotabcol 
                        where tab='rhevento' 
                        and primkey ='N' 
                        and col not in('alteradoem','alteradopor','criadoem','criadopor','idempresa','dataevento','parcela','parcelas','idobjetoori','tipoobjetoori')";
                $ret= d::b()->query($sqt) or die("Erro ao buscar os campos na mtotabcol do rhevento sql=".$sqt);

                $insrhevento = new Insert();
                $insrhevento->setTable("rhevento");
                $insrhevento->dataevento= $rowc['dataeventocal']; 
                $insrhevento->parcelas= $_parcelas;
                $insrhevento->parcela= $i;
                $insrhevento->idobjetoori= $idrhevento;
                $insrhevento->tipoobjetoori= 'rhevento';
                while($rowt=mysqli_fetch_assoc($ret)){

                    $valor=$_SESSION['arrpostbuffer']['1']['u']['rhevento'][$rowt['col']];
                    $campo=$rowt['col'];

                    if(!empty($valor)){

                        $valor= evaltipocoldb("rhevento", $rowt['col'], $rowt['datatype'],$valor);

                        $insrhevento->$campo= str_replace("'", "", $valor);  
                    }                    
                }//while($rowt=mysqli_fetch_assoc($ret)){   

              // print_r($insrhevento); die;
                $newidrhevento=$insrhevento->save();
            }
        }//for

    }// if($_parcelas > 1){
    
}//fim multiplica evento



$hora=$_POST["_1_u_rhevento_hora"];
$horaf=$_POST["_1_u_rhevento_horaf"];
$dataevento=$_POST["_1_u_rhevento_dataevento"];
$dataeventof=$_POST["_1_u_rhevento_dataeventof"];
//cria os eventos conforme a inicio e fim
if(!empty($idrhevento)  
    and !empty($hora)
        and !empty($horaf)
            and !empty($dataevento)
                and !empty($dataeventof)){
    
    $data_inicial = new DateTime( implode( '-', array_reverse( explode( '/', $dataevento ) ) ) );
    $data_final   = new DateTime( implode( '-', array_reverse( explode( '/', $dataeventof ) ) ) );
    $_parcela==0;
    while( $data_inicial <= $data_final ) {
       $_parcela=$_parcela+1;
        $get_name = date('l', strtotime($data_inicial->format( 'Y/m/d' ))); //get week day
        $day_name = substr($get_name, 0, 3); // Trim day name to 3 chars
        
        // verificar se e um feriado cadastrado no sistema
        $sf="select obs from feriado where dataferiado ='".$data_inicial->format( 'Y-m-d' )."'";
        $rf=d::b()->query($sf);
        $qtf=mysqli_num_rows($rf);
        if($day_name != 'Sun' && $day_name != 'Sat' && $qtf<1){
          
            
            
            $sqt="select 
                    col,datatype
                    from "._DBCARBON."._mtotabcol 
                    where tab='rhevento' 
                    and primkey ='N' 
                    and col not in('alteradoem','alteradopor','criadoem','criadopor','idempresa','entsaida','dataevento','hora','dataeventof','horaf','parcela','parcelas','idobjetoori','tipoobjetoori')";
            $ret= d::b()->query($sqt) or die("Erro ao buscar os campos na mtotabcol do rhevento sql=".$sqt);
            
            //INICIO
            if($_parcela>1){
                 echo $data_inicial->format( 'd/m/Y' ) . '<br />' . PHP_EOL;
                $insrhevento = new Insert();
                $insrhevento->setTable("rhevento");
                $insrhevento->dataevento= $data_inicial->format( 'Y-m-d' ); 
                $insrhevento->parcela= $_parcela;
                $insrhevento->hora= $hora;
                $insrhevento->idobjetoori= $idrhevento;
                $insrhevento->entsaida= 'E';
                $insrhevento->tipoobjetoori= 'rhevento';
                while($rowt=mysqli_fetch_assoc($ret)){

                    $valor=$_SESSION['arrpostbuffer']['1']['u']['rhevento'][$rowt['col']];
                    $campo=$rowt['col'];

                    if(!empty($valor)){

                        $valor= evaltipocoldb("rhevento", $rowt['col'], $rowt['datatype'],$valor);

                        $insrhevento->$campo= str_replace("'", "", $valor);  
                    }                    
                }//while($rowt=mysqli_fetch_assoc($ret)){ 
                 $new_idrhevento=$insrhevento->save();
            }
            //FIM
            $_parcela=$_parcela+1;
            //echo $data_inicial->format( 'd/m/Y' ) . '<br />' . PHP_EOL;
            $insrhevento2 = new Insert();
            $insrhevento2->setTable("rhevento");
            $insrhevento2->dataevento= $data_inicial->format( 'Y-m-d' ); 
            $insrhevento2->parcela= $_parcela;
            $insrhevento2->hora= $horaf;
            $insrhevento2->idobjetoori= $idrhevento;
            $insrhevento2->entsaida= 'S';
            $insrhevento2->tipoobjetoori= 'rhevento';
            $ret= d::b()->query($sqt) or die("Erro ao buscar os campos na mtotabcol do rhevento fim sql=".$sqt);
            while($rowt=mysqli_fetch_assoc($ret)){

                $valor=$_SESSION['arrpostbuffer']['1']['u']['rhevento'][$rowt['col']];
                $campo=$rowt['col'];

                if(!empty($valor)){

                    $valor= evaltipocoldb("rhevento", $rowt['col'], $rowt['datatype'],$valor);

                    $insrhevento2->$campo= str_replace("'", "", $valor);  
                }                    
            }//while($rowt=mysqli_fetch_assoc($ret)){  
              $new_idrhevento1=$insrhevento2->save();
             // die('rh0'.$new_idrhevento1);
              
        }//if($day_name != 'Sun' && $day_name != 'Sat' && $qtf<1){
        /*else{
            ECHO('não e dia util..'. '<br />');
        }*/
        $data_inicial->add( DateInterval::createFromDateString( '1 days' ) );
    }

    
/*    
    $dataev=validadate($dataevento);
    $dataevf=validadate($dataeventof);
    
    $timestampdt = strtotime($row['dataevento']);
    $dia= date("d", $timestampdt);
    $mes= date("m", $timestampdt);
    $ano= date("Y", $timestampdt);

    $folha = new Folha($dia,$dia,$mes,$ano,$row['idpessoa']);

    $calendario=$folha->getCalendario(); 

    $horasexec=$folha->gethorasExec();

    $horasplan=$folha->getHorasPlan();
*/    
}
?>