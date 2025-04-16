<?
require_once("functions.php");

// Classe base com propriedades e métodos de membros
class Folha {
    var $dia;
    var $Atedia;
    var $mes;
    var $ano;
    var $idpessoa;
    var $hrpddia;//horas padrao do dia
    var $hrpdseg;
    var $hrpdter;
    var $hrpdqua;
    var $hrpdqui;
    var $hrpdsex;
    var $hrpdsab;
    var $hrpddom;
    var $horario;
    var $bancohoras;
	var $calendario;//armazena todo os dias disponíveis do mês desejado
       

	//Esta function tem o mesmo nome da class, portanto quando você dá "new Folha", ela constrói a classe na memoria Ram. Por isso essa functino aqui é o "constructor". 
	function Folha($inDia,$inAteDia,$inMes,$inAno,$inIdpessoa=null)
	{
            $this->dia=$inDia;
            $this->Atedia=$inAteDia;
            $this->mes=$inMes;//Pra setar as propriedades da classe, ao inves de utilizar $ utiliza-se ->
            $this->ano=$inAno;
            $this->idpessoa=$inIdpessoa;
            
            $this->dtinicio= $inAno.'-'.$inMes.'-'. $inDia; //format date
            
            if(empty($inAteDia)){
                $this->dtfim=$inAno.'-'.$inMes.'-'. $inDia;
            }else{
                $this->dtfim=$inAno.'-'.$inMes.'-'. $inAteDia;
            }
            
           
            $sql="select *
                from pessoa
                where idpessoa=".$this->idpessoa;
            $res=d::b()->query($sql);
            $row=mysqli_fetch_assoc($res);
            $_idempresa=$row['idempresa'];
            
            $this->bancohoras=$row['bancohoras'];

            //segunda
            $sql="select  ifnull(ROUND((SUM( TIME_TO_SEC( TIMEDIFF(horafim,horaini) ) )/3600),2),0) AS hpadrao ,
            case when left(horaini,5) > '18:00' then 'NOTURNO' else 'DIURNO' end as horario
            from pessoahorario 
            where idpessoa=".$this->idpessoa." and periodo='Mon' order by horaini";
            $res=d::b()->query($sql);
            $row=mysqli_fetch_assoc($res);

            $this->hrpdseg=$row['hpadrao'];

            //terca
            $sql="select  ifnull(ROUND((SUM( TIME_TO_SEC( TIMEDIFF(horafim,horaini) ) )/3600),2),0) AS hpadrao ,
            case when left(horaini,5) > '18:00' then 'NOTURNO' else 'DIURNO' end as horario
            from pessoahorario 
            where idpessoa=".$this->idpessoa." and periodo='Tue' order by horaini";
            $res=d::b()->query($sql);
            $row=mysqli_fetch_assoc($res);
            $this->hrpdter=$row['hpadrao'];

            //quarta
            $sql="select  ifnull(ROUND((SUM( TIME_TO_SEC( TIMEDIFF(horafim,horaini) ) )/3600),2),0) AS hpadrao ,
            case when left(horaini,5) > '18:00' then 'NOTURNO' else 'DIURNO' end as horario
            from pessoahorario 
            where idpessoa=".$this->idpessoa." and periodo='Wed' order by horaini";
            $res=d::b()->query($sql);
            $row=mysqli_fetch_assoc($res);

            $this->hrpdqua=$row['hpadrao'];

            //quinta
            $sql="select  ifnull(ROUND((SUM( TIME_TO_SEC( TIMEDIFF(horafim,horaini) ) )/3600),2),0) AS hpadrao ,
            case when left(horaini,5) > '18:00' then 'NOTURNO' else 'DIURNO' end as horario
            from pessoahorario 
            where idpessoa=".$this->idpessoa." and periodo='Thu' order by horaini";
            $res=d::b()->query($sql);
            $row=mysqli_fetch_assoc($res);

            $this->hrpdqui=$row['hpadrao'];

            //sexta
            $sql="select  ifnull(ROUND((SUM( TIME_TO_SEC( TIMEDIFF(horafim,horaini) ) )/3600),2),0) AS hpadrao ,
            case when left(horaini,5) > '18:00' then 'NOTURNO' else 'DIURNO' end as horario
            from pessoahorario 
            where idpessoa=".$this->idpessoa." and periodo='Fri' order by horaini";
            $res=d::b()->query($sql);
            $row=mysqli_fetch_assoc($res);

            $this->hrpdsex=$row['hpadrao'];

            //sabado        
            $sql="select  ifnull(ROUND((SUM( TIME_TO_SEC( TIMEDIFF(horafim,horaini) ) )/3600),2),0) AS hpadrao ,
                    case when left(horaini,5) > '18:00' then 'NOTURNO' else 'DIURNO' end as horario
                from pessoahorario 
                where idpessoa=".$this->idpessoa." and periodo='Sat' order by horaini";
            $res=d::b()->query($sql);
            $row=mysqli_fetch_assoc($res);
            
            $this->hrpdsab=$row['hpadrao'];
            
            $sql="select  ifnull(ROUND((SUM( TIME_TO_SEC( TIMEDIFF(horafim,horaini) ) )/3600),2),0) AS hpadrao ,
                    case when left(horaini,5) > '18:00' then 'NOTURNO' else 'DIURNO' end as horario
                from pessoahorario 
                where idpessoa=".$this->idpessoa." and periodo='Sun' order by horaini";
            $res=d::b()->query($sql);
            $row=mysqli_fetch_assoc($res);
            
            $this->hrpddom=$row['hpadrao'];
   
            //Já inicializa o calendário do mês
            $this->getCalendario($_idempresa);
	}

	function getCalendario($_idempresa){
            if(!empty($this->calendario)){
                return $this->calendario;
            }else{
                //código para montar array com os dias disponiveis
                //usando-se $this->mes como parametro
                $this->calendario = array();
                $type = CAL_GREGORIAN;
                $month = $this->mes; // Month ID, 1 through to 12.
                $year = $this->ano; // Year in 4 digit 2009 format.
                if(empty($this->Atedia)){
                    $this->Atedia = cal_days_in_month($type, $month, $year); // Get the amount of days
                }                   
                if(empty( $this->dia)){
                     $this->dia=1;
                }
                //loop through all days
                for ($i = $this->dia; $i <= $this->Atedia; $i++) {

                    $date = $year.'/'.$month.'/'.$i; //format date
                    $date_ing =$i.'-'.$month.'-'. $year; //format date
                    $get_name = date('l', strtotime($date)); //get week day
                    $day_name = substr($get_name, 0, 3); // Trim day name to 3 chars

                    // verificar se e um feriado cadastrado no sistema
                    $sf="select obs from feriado where status='ATIVO' and idempresa in(8,".$_idempresa.") and dataferiado ='".$date."'";
                    $rf=d::b()->query($sf);
                    $qtf=mysqli_num_rows($rf);

                    //echo($sf);
                    //if not a weekend add day to array
                    /*
                    if($day_name != 'Sun' && $day_name != 'Sat'){
                            $this->calendario["dias"][$i] = $i;
                    }
                    */
                    //if not a weekend add day to array

                    /*if($day_name != 'Sun' && $day_name != 'Sat' && $qtf<1){
                        $this->calendario["dias"][$i]["fds"] = false;
                        $this->calendario["dias"][$i]["data"]=$date_ing;
                    }else*/
                    if($qtf<1){
                        $this->calendario["dias"][$i]["fds"] = $day_name;
                        $this->calendario["dias"][$i]["data"]=$date_ing;
                    }else{
                        $this->calendario["dias"][$i]["fds"] = true;
                        $this->calendario["dias"][$i]["data"]=$date_ing;
                    }
                }
                return $this->calendario;
        }
    }

    function getHorasPlan($inIdpessoa=null){
        if(!empty($this->calendario["horasplan"])){
            return $this->calendario["horasplan"];
        }else{
            //print_r($this->calendario["dias"]);
            // código para montar as horas planejadas conforme a data configurada no usuário X dias encontrados em getCalendario
            foreach ($this->calendario["dias"] as $k => $v) {
                // print_r();
				//echo($v["fds"]);
                //echo($this->hrpddom);die();
              
                if($v["fds"]===false){
                    $this->calendario["horasplan"]['hora']=$this->calendario["horasplan"]['hora']+$this->hrpddia;                                
                }elseif($v["fds"]==='Mon' and !empty($this->hrpdseg)){                   
                    $this->calendario["horasplan"]['hora']=$this->calendario["horasplan"]['hora']+$this->hrpdseg; 
                }elseif($v["fds"]==='Tue' and !empty($this->hrpdter)){                   
                    $this->calendario["horasplan"]['hora']=$this->calendario["horasplan"]['hora']+$this->hrpdter; 
                }elseif($v["fds"]==='Wed' and !empty($this->hrpdqua)){                   
                    $this->calendario["horasplan"]['hora']=$this->calendario["horasplan"]['hora']+$this->hrpdqua; 
                }elseif($v["fds"]==='Thu' and !empty($this->hrpdqui)){                   
                    $this->calendario["horasplan"]['hora']=$this->calendario["horasplan"]['hora']+$this->hrpdqui; 
                }elseif($v["fds"]==='Fri' and !empty($this->hrpdsex)){                   
                    $this->calendario["horasplan"]['hora']=$this->calendario["horasplan"]['hora']+$this->hrpdsex; 
                }elseif($v["fds"]==='Sat' and !empty($this->hrpdsab)){                   
                    $this->calendario["horasplan"]['hora']=$this->calendario["horasplan"]['hora']+$this->hrpdsab; 
                }elseif($v["fds"]==='Sun' and !empty($this->hrpddom)){
                    $this->calendario["horasplan"]['hora']=$this->calendario["horasplan"]['hora']+$this->hrpddom; 
                }
                
            }
            
            return $this->calendario["horasplan"];
            }
    }
        
    function gethorasExec($inIdpessoa=null){
        if(!empty($this->calendario["horasexec"])){
                    return $this->calendario["horasexec"];
        }else{
            $horadianot=0;
            $sql="SELECT e.* FROM rhevento e,rhtipoevento t
                where  t.idrhtipoevento=e.idrhtipoevento
                    and t.flgponto='Y' 
                    and t.formato in ('HI','HIF')
                    and e.status!='INATIVO'
                and e.idpessoa = ".$this->idpessoa." 
                and e.dataevento  between '".$this->dtinicio."' and '".$this->dtfim."' 
                and e.hora is not null order by e.dataevento,e.hora";

            //echo($sql);
            $res=d::b()->query($sql);
             $arrayp=array();
            while($r=mysqli_fetch_assoc($res)){
                $arrayp[$r['idpessoa']][$r['dataevento']][$r['idrhevento']]['idrhevento']=$r['idrhevento'];  
                $arrayp[$r['idpessoa']][$r['dataevento']][$r['idrhevento']]['hora']=$r['hora'];
                $arrayp[$r['idpessoa']][$r['dataevento']][$r['idrhevento']]['status']=$r['status'];
                $arrayp[$r['idpessoa']][$r['dataevento']][$r['idrhevento']]['entsaida']=$r['entsaida'];  
                $arrayp[$r['idpessoa']][$r['dataevento']][$r['idrhevento']]['situacao']=$r['situacao'];
                $arrayp[$r['idpessoa']][$r['dataevento']][$r['idrhevento']]['tipo']=$r['tipo'];
            }//while($row=mysqli_fetch_assoc($res)){

                //$arrayp[$r['idpessoa']][$r['nome']][$r['dataponto']][$r['idrhevento']]['idrhevento']=$r['idrhevento'];  
            foreach ($arrayp as $idpessoa => $arradtev) {

                foreach ($arradtev as $dataevento => $arrdata){

                    foreach ($arrdata as $idrhevento => $value){

                         //CALCULAR HORAS
                            if($value['entsaida']=='E'){
                                $inicio=$value['hora'];
                                $fimcom_e='Y';
                                $horadia_e=0;
                            }else{
                                if(empty($inicio)){
                                    $inicio='00:00';
                                }
                                $arrinicio = explode(":",$inicio);
                                $arrfim = explode(":",$value['hora']);

                                $difhora =  $arrfim[0] - $arrinicio[0] ;

                                $mdifhora= $difhora * 60;

                                $difmin =  $arrfim[1] - $arrinicio[1] ;

                                $ress = $mdifhora + $difmin;

                                $ressfim = $ress / 60;

                                //adicional noturno em horas
                                if($arrfim[0]>=22 or ($arrinicio[0]<5)){

                                    if($arrinicio[0]<5){
                                        if($arrfim[0] < 5){
                                            $fimnot=$value['hora'];
                                        }else{
                                            $fimnot='05:00';
                                        }
                                        
                                        $inoturno=$inicio;
                                    }else{
                                        $fimnot=$value['hora'];
                                        if($arrinicio[0]<22){
                                            $inoturno='22:00';
                                        }else{
                                            $inoturno=$inicio;
                                        }
                                    }
                               
                                    //if($arrfim[0]>=19 or $arrfim[0]<=5){
                                        $arrinot = explode(":",$inoturno);
                                        $arrfimnot = explode(":", $fimnot);
        
                                        $difhoranot =  $arrfimnot[0] - $arrinot[0] ;
        
                                        $mdifhoranot= $difhoranot * 60;
        
                                        $difminnot =  $arrfimnot[1] - $arrinot[1] ;
        
                                        $ressnot = $mdifhoranot + $difminnot;
        
                                        $ressfimnot = $ressnot / 60;
                                    //}
                                    $horadianot=$horadianot+$ressfimnot;
                                }


                                //echo round($ressfim,2);
                                $horadia=$horadia+$ressfim;
                                $totalh= $ressfim + $totalh;                               
                                $inicio='';
                                $fimcom_e='N';
                            }
                            //echo( $horadianot."<br>");
                    }//foreach ($arrdata as $idrhevento => $value){
                    if($fimcom_e=='Y'){
                        $arrinicio = explode(":",$inicio);
                        $arrfim = explode(":",'24:00');

                        $difhora =  $arrfim[0] - $arrinicio[0] ;

                        $mdifhora= $difhora * 60;

                        $difmin =  $arrfim[1] - $arrinicio[1] ;

                        $ress = $mdifhora + $difmin;

                        $ressfim = $ress / 60;

                        //adicional noturno em horas
                        if($arrfim[0]>=22 ){ 
                            $fimnot='24:00';
                            if($arrinicio[0]<22){
                                $inoturno='22:00'; 
                            }else{
                                $inoturno=$inicio; 
                            }   
                                        
                       
                           // if($arrfim[0]>=19 or $arrfim[0]<=5){
                                $arrinot = explode(":",$inoturno);
                                $arrfimnot = explode(":",'24:00');

                                $difhoranot =  $arrfimnot[0] - $arrinot[0] ;

                                $mdifhoranot= $difhoranot * 60;

                                $difminnot =  $arrfimnot[1] - $arrinot[1] ;

                                $ressnot = $mdifhoranot + $difminnot;

                                $ressfimnot = $ressnot / 60;
                           // }
                          // $horadianot=$horadianot+$ressfimnot;

                          // echo( $horadianot."<br>");
                        }

                        //echo round($ressfim,2);
                        $horadia=$horadia+$ressfim;
                        $totalh= $ressfim + $totalh;
                        $horadianot=$horadianot+$ressfimnot;
                        $inicio='';                       
                    }//if($fimcom_e=='Y'){

                    //echo(" soma de hora dia=".round($horadia,2));     
                   //  $this->calendario["horasexec"]['hora']=$this->calendario["horasexec"]['hora']+round($totalh,2); 


                }//foreach ($arradtev as $dataevento => $arrdata){
               // echo('Hora dia'.$horadia);

              // echo( $horadianot."<br>");

            }//foreach ($arrayp as $idpessoa => $arradtev) {
            // evento com bonus de valor em horas
            $sqe="select t.tipo,e.idrhevento,t.evento,e.valor from rhtipoevento t,rhevento e
                    where t.formato='H' and t.flgponto = 'Y'
                    and t.flhtotais = 'N' and t.flhtotaisajust  = 'N' and t.flhext  = 'N' and t.flhextcalc  = 'N'
                    and e.idrhtipoevento=t.idrhtipoevento
                    and e.status!='INATIVO'
                    and e.valor is not null
                    and e.valor > 0                   
                    and e.idpessoa = ".$this->idpessoa." 
                    and e.dataevento  between '".$this->dtinicio."' and '".$this->dtfim."'";
            $re=d::b()->query($sqe);
            while($roe=mysqli_fetch_assoc($re)){
               if($roe['tipo']=='C'){
                  $horadia=$horadia+$roe['valor'];
               }elseif($roe['tipo']=='D'){
                  $horadia=$horadia-$roe['valor'];
               }else{
                   $horadia=$horadia;
               }
            }//while($roe=mysqli_fetch_assoc($re)){
            $this->calendario["horasexec"]['hora']=round($horadia,2);
            $this->calendario["horasexec"]['horanot']=round($horadianot,2);
            return $this->calendario["horasexec"];
        }// if(!empty($this->calendario["horasexec"])){
            
    }//function gethorasExec($inIdpessoa=null){
    
    function gethoras(){
        $horasexec=$this->calendario["horasexec"]['hora'];
        $horasplan=$this->calendario["horasplan"]['hora'];
        $horasplanejadasmenor=($horasplan-0.18);
        $horasplanejadasmaior=($horasplan+0.18);     
        
        
        $sqe="select t.tipo,e.idrhevento,t.evento,e.valor from rhtipoevento t,rhevento e
                    where t.tipo='I' and t.flgponto = 'Y'
                    and t.flhtotais = 'N' and t.flhtotaisajust  = 'N' and t.flhext  = 'N' and t.flhextcalc  = 'N'
                    and e.idrhtipoevento=t.idrhtipoevento
                    and e.status!='INATIVO'
                    and e.idpessoa = ".$this->idpessoa." 
                    and e.dataevento  between '".$this->dtinicio."' and '".$this->dtfim."'";
        $re=d::b()->query($sqe);
        $qtdre= mysqli_num_rows($re);
        //se tiver ponto informativo zera as horas
        if($qtdre>0){
            $this->calendario["horasexec"]['horaajustada']=round(0,2);
            $this->calendario["horasexec"]['horaextra']=round(0,2);
            $this->calendario["horasexec"]['horaextradinheiro']=round(0,2);
            $this->calendario["horasexec"]['diastrab']=round(0,2);
        }else{
            $this->calendario["horasexec"]['diastrab']=round(0,2);
            // maior que 10 minuto de atraso e menor que 10 minutos adiantado e a hora  
            if($horasexec>$horasplanejadasmenor and $horasexec<=$horasplanejadasmaior){           
                $this->calendario["horasexec"]['horaajustada']=round($horasplan,2);
                $this->calendario["horasexec"]['horaextra']=round(0,2);
                $this->calendario["horasexec"]['horaextradinheiro']=round(0,2);
                $this->calendario["horasexec"]['diastrab']=round(0,2);
            }elseif($horasexec<=$horasplanejadasmenor){//HORAS TRABALHADAS MENOS QUE AS HORAS PLANEJADAS

                $this->calendario["horasexec"]['horaajustada']=round($horasplan,2);// COLOCA AS HORAS PLANEJADAS PARA O FUNCIONARIO
               // if($horasexec>0){
                    $hext=($horasexec-$horasplan)-0.01;
              //  }else{
              //      $hext=0.00;
               // }
                $this->calendario["horasexec"]['horaextra']=$hext;
                $this->calendario["horasexec"]['horaextradinheiro']=round(0,2);
                $this->calendario["horasexec"]['diastrab']=round(0,2);

            }elseif($horasexec>=$horasplanejadasmaior ){
                $this->calendario["horasexec"]['horaajustada']=round($horasplan,2);
                if($horasexec ==($horasplan+2)){//horasplan = 8+2 10 horas
                    $this->calendario["horasexec"]['horaextra']=round(2,2);
                    $this->calendario["horasexec"]['horaextradinheiro']=round(0,2);
                    $this->calendario["horasexec"]['diastrab']=round(0,2);
                }elseif($horasexec <($horasplan+2)){//horasplan = 8+2 10 horas
                    $this->calendario["horasexec"]['horaextra']=$horasexec-$horasplan;
                    $this->calendario["horasexec"]['horaextradinheiro']=round(0,2);
                    $this->calendario["horasexec"]['diastrab']=round(0,2);
                }elseif($horasexec > 0  and $horasplan ==0){// sabado e domingo feriado
                   // die($horasexec-10);
                   if($this->bancohoras=='Y'){
                    $this->calendario["horasexec"]['horaextra']=round($horasexec,2);
                    $this->calendario["horasexec"]['horaextradinheiro']=round(0,2);
                    $this->calendario["horasexec"]['diastrab']=round(0,2);
                   }else{
                    $this->calendario["horasexec"]['horaajustada']=round(0,2);
                    $this->calendario["horasexec"]['horaextra']=round(0,2);
                    $this->calendario["horasexec"]['horaextradinheiro']=round(0,2);
                    $this->calendario["horasexec"]['diastrab']=round(1,2);
                   }
                    
                    //aqui calcular em dias se não for banco de horas
                }else{
                   // die($horasexec-10);
                   $this->calendario["horasexec"]['diastrab']=round(0,2);
                    $this->calendario["horasexec"]['horaextra']=round(2,2);
                    $this->calendario["horasexec"]['horaextradinheiro']=round(($horasexec-($horasplan+2)),2);
                }
            }//}elseif($horasexec>=$horasplanejadasmaior ){
        }//if($qtdre>0){
        
        //calcular valor dinheiro da horas extras
        if($this->calendario["horasexec"]['horaextradinheiro']>0){
			
            $sqe="select ((sum(e.valor)/200)*2) as salhora
                            from rheventopessoa e  join rhtipoevento t on(t.idrhtipoevento=e.idrhtipoevento)
                            where e.idpessoa=".$this->idpessoa."
                            and e.idrhtipoevento in (18,33)                           
                            and e.status='ATIVO'";
            $re=d::b()->query($sqe);
            $row=mysqli_fetch_assoc($re);
            $this->calendario["horasexec"]['dinheirohoraextra']=round($row['salhora']*$this->calendario["horasexec"]['horaextradinheiro'],2);
         
                           
            
        }else{
            $this->calendario["horasexec"]['dinheirohoraextra']=round(0,2);            
        }
		
		if($this->calendario["horasexec"]['horaextra']>0){
			$totalhoraextra=$this->calendario["horasexec"]['horaextradinheiro']+$this->calendario["horasexec"]['horaextra'];
			
			$sqe="select ((sum(e.valor)/200)*2) as salhora
                            from rheventopessoa e  join rhtipoevento t on(t.idrhtipoevento=e.idrhtipoevento)
                            where e.idpessoa=".$this->idpessoa."                          
                            and e.idrhtipoevento in (18,33)
                            and e.status='ATIVO'";
            $re=d::b()->query($sqe);
            $row=mysqli_fetch_assoc($re);
			 
			$sqd="select func_diaslivres('".$this->ano."','".$this->mes."') as diaslivres";
            $red=d::b()->query($sqd);
            $rowd=mysqli_fetch_assoc($red);
            
            $squ="select func_diauteis('".$this->ano."','".$this->mes."') as diasuteis";
            $reu=d::b()->query($squ);
            $rowu=mysqli_fetch_assoc($reu);
            
           $dsrhoraextra=round(((($totalhoraextra/$rowu['diasuteis'])*$rowd['diaslivres'])*$row['salhora']),2);
		   if($dsrhoraextra>0){
				$this->calendario["horasexec"]['dsrhoraextra']= $dsrhoraextra;
		   }else{
			    $this->calendario["horasexec"]['dsrhoraextra']=round(0,2);
		   }
		   
            
			
		}else{          
            $this->calendario["horasexec"]['dsrhoraextra']=round(0,2);
        }
       
         return $this->calendario["horasexec"];
    }//function gethoras(){
    
    
}//class Folha {
//Folha($inDia,$inAteDia,$inMes,$inAno,$inIdpessoa=null)
/*
$folha = new Folha('04','04','11','2020',794);

$calendario=$folha->getCalendario(); 

$horasexec=$folha->gethorasExec();

$horasplan=$folha->getHorasPlan();


//print_r($horasplan);

$horas=$folha->gethoras();
*/

/*
echo('executada noturna='.$horasexec['horanot'].'<br>');
echo('Horas='.$horasexec['hora'].'<br>');
echo('H ajustada='.$horas['horaajustada'].'<br>');
echo('H Extra='.$horas['horaextra'].'<br>');
echo('H Extra dinheiro='.$horas['horaextradinheiro'].'<br>');
echo('Diastrab extra='.$horas['diastrab'].'<br>');
*/
//print_r($horas); echo('<br>');
//echo('executada='.$horasexec['hora'].'<br>');
//echo('planejada='.$horasplan['hora']);
  
 
     