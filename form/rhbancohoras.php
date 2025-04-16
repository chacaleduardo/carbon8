<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
require_once("../inc/php/folha.php");
if($_POST){
    require_once("../inc/php/cbpost.php");
}
//ini_set("display_errors","1");
//error_reporting(E_ALL);
################################################## Atribuindo o resultado do metodo GET

$dataevento_1 	= $_GET["dataevento_1"];
$dataevento_2 	= $_GET["dataevento_2"];

$tiporel = $_GET["tiporel"]; 
$idsgsetor = $_GET["idsgsetor"];
$idsgdepartamento = $_GET["idsgdepartamento"];

if($_GET["_inspecionar_sql"] == 'Y'){
    $inspecionarsql = true;
}else{
    $inspecionarsql = false;
}

if(isset($idsgdepartamento) ){
    $nn = $idsgdepartamento;
    $sqldep = "SELECT group_concat(s.idsgsetor) as iddeps
    FROM sgsetor s INNER JOIN objetovinculo ov ON s.idsgsetor=ov.idobjetovinc AND ov.tipoobjetovinc = 'sgsetor'
    WHERE ov.idobjeto in($nn)";
    $resdep=d::b()->query($sqldep);
    $rwdep=mysqli_fetch_assoc($resdep);
    $ids= $rwdep['iddeps'];
    if(empty($ids)){
        $ids = $idsgsetor;
    }
    $sqlbo = "SELECT group_concat(g.idpessoa) as idpessoa from (
                    SELECT u.idpessoa from( 
                        SELECT p.idpessoa
                        from sgdepartamento d 
                        join pessoaobjeto p on(d.idsgdepartamento = p.idobjeto and p.tipoobjeto = 'sgdepartamento') 
                        where d.idsgdepartamento in($nn) and d.status = 'ATIVO' 
                        union 
                        SELECT p.idpessoa from sgsetor s 
                        join pessoaobjeto p on(s.idsgsetor = p.idobjeto and p.tipoobjeto = 'sgdepartamento') 
                        where s.idsgdepartamento in($nn) and s.status = 'ATIVO' 
                        union 
                        SELECT p.idpessoa from sgsetor s 
                        join pessoaobjeto p on(s.idsgsetor = p.idobjeto and p.tipoobjeto = 'sgsetor') 
                        where s.idsgsetor in($ids) and s.status = 'ATIVO' ) 
                    as u group by u.idpessoa) 
              as g";
    $resbo=d::b()->query($sqlbo);
    $rwbo=mysqli_fetch_assoc($resbo);
    
    if($inspecionarsql){
        echo "<!-- Consulta1: ".$sqlbo." -->";
    }

}else{
    $nn = "''";
}
if(!empty($rwbo['idpessoa'])){
    $idpessoab	=  $rwbo['idpessoa'];
}else{
    $idpessoab	= $_GET["idpessoa"];
}




$dia = date("d")-1;
$mes = date("Y-m");
if($dia <= 9){
    $z = "-0";
}else{
    $z = "-";
}
$diaant = $mes.$z.$dia;

$idusuario= $_SESSION["SESSAO"]["IDPESSOA"];//$perfilpag_idpessoa;
$sql="select * from "._DBCARBON."._lpmodulo where modulo ='aprovaponto' and idlp in(".getModsUsr("LPS").")";
$res=d::b()->query($sql) or die("erro ao buscar supervisor sql=".$sql);
$_supervisor= mysqli_num_rows($res);
if($_supervisor<1){
   $idpessoab= $idusuario; 
   $readonlyp="disabled='disabled'";
   $hidden="hidden";
}

if($inspecionarsql){
    echo "<!-- Consulta2: ".$sql." -->";
}

    if (!empty($dataevento_1) or !empty($dataevento_2)){
        $data1 = validadate($dataevento_1);
        $data2 = validadate($dataevento_2);
        $datafim = validadate($dataevento_1);
/*
        if ($data1 and $data2){
            $strin .= " and (dataponto  BETWEEN '" . $data1 ."' and '" .$data2 ."')";
        }else{
            die ("Datas n&atilde;o V&aacute;lidas!");
        }
 
 */
    }
        //if (!empty($vencimento_1) or !empty($vencimento_2)){
$strjoin='';
    if(!empty($idpessoab) and $idpessoab!='null'){
        $strin .= " and p.idpessoa in (".$idpessoab.") ";
    }elseif(!empty($idsgsetor) and $idsgsetor!='null'){
        $strin .=" and s.idpessoa=p.idpessoa and s.idobjeto in(".$idsgsetor.") and s.tipoobjeto = 'sgsetor'";
        $strjoin=",pessoaobjeto s";
    }

    
/*
 * colocar condição para executar select
 */
if($_GET and !empty($strin) ){
    
    
    $idusuario= $_SESSION["SESSAO"]["IDPESSOA"];//$perfilpag_idpessoa;

    $sql="select * from "._DBCARBON."._lpmodulo where modulo ='aprovaponto' and idlp in(".getModsUsr("LPS").")";
    $res=d::b()->query($sql) or die("erro ao buscar supervisor sql=".$sql);
    $_supervisor= mysqli_num_rows($res);
    if($_supervisor<1){
       $strin.=" and p.idpessoa ='".$idusuario."' "; 
    }
    if($inspecionarsql){
        echo "<!-- Consulta3: ".$sql." -->";
    }
   
    
    $arrayp=array();
    if($tiporel=="DETALHADO"){     
        for ($i=0;;$i++) {
        
            $s="SELECT DATE_ADD('".$data1."', INTERVAL ".$i." DAY) as diabusca,
                DATE_FORMAT( DATE_ADD('".$data1."', INTERVAL ".$i." DAY),'%W') as semana,
                    case  when DATE_ADD('".$data1."', INTERVAL ".$i." DAY) > '".$data2."' then 'Y' 
                    else 'N' end  as maior";
            $re= d::b()->query($s) or die("erro ao buscar os pontos pendentes sql=".$s);
            $rw=mysqli_fetch_assoc($re);
            
                if ($rw['maior'] =='Y') {
                    break;
                }else{
                    
                $s1="select p.idpessoa as idpessoa,p.nome,p.contratacao 
                    from pessoa p ".$strjoin."
                    where 1 -- p.status='ATIVO'
                    ".$strin."
                        order by p.nomecurto";
                echo "<!-- ".$s1." -->";	//echo $_sqlresultado;
                // die($s1);
                $re1= d::b()->query($s1) or die("erro ao buscar os funcionarios dos pontos  sql=".$s1);
    
                    while($r=mysqli_fetch_assoc($re1)){
                        // $arrayp[$r['idpessoa']]['semana'][$rw['diabusca']]=$r['semana']; 
                        if(strtotime($rw['diabusca']) > strtotime($r['contratacao'])){
                            $arrayp[$r['idpessoa']][$r['nome']][$rw['diabusca']][1]['semana']=$rw['semana'];                         
                        } 
                       
                            
                    }    
                    
                    

                }
            } 
            
            //print_r($arrayp);die;
        $data2 = $data2.' 23:59:59';
    /*
        $s1="select e.idrhevento,e.idpessoa,p.nomecurto as nome,e.idrhtipoevento,e.dataevento as dataponto,t.evento,e.valor,e.status
            from rhevento e,rhtipoevento t,pessoa p ".$strjoin."
            where e.idrhtipoevento = t.idrhtipoevento
            and e.status!='INATIVO'
            and e.valor !=0
                and p.idpessoa=e.idpessoa
            and (t.flhtotais='Y' or t.flhtotaisajust='Y' or t.flhext='Y' or  t.flhextcalc='Y'or e.idrhtipoevento = 23 or e.idrhtipoevento=432 )
                ".$strin."
            and e.dataevento between '".$data1."' and '".$data2."'";
        
            echo "<!-- ".$s1." -->";	//echo $_sqlresultado;
            // echo $s1;	//echo $_sqlresultado;
            $re1= d::b()->query($s1) or die("erro ao buscar os pontos  sql=".$s1);

        while($r=mysqli_fetch_assoc($re1)){
            // $arrayp[$r['idpessoa']]['semana'][$rw['diabusca']]=$r['semana'];  
            $arrayp[$r['idpessoa']][$r['nome']][$r['dataponto']][$r['idrhevento']]['idrhevento']=$r['idrhevento'];  
            $arrayp[$r['idpessoa']][$r['nome']][$r['dataponto']][$r['idrhevento']]['evento']=$r['evento'];  
            $arrayp[$r['idpessoa']][$r['nome']][$r['dataponto']][$r['idrhevento']]['valor']=$r['valor'];
            $arrayp[$r['idpessoa']][$r['nome']][$r['dataponto']][$r['idrhevento']]['status']=$r['status'];
            $arrayp[$r['idpessoa']][$r['nome']][$r['dataponto']][$r['idrhevento']]['idrhtipoevento']=$r['idrhtipoevento'];
        }   
        
        */ 
            //print_r($arrayp);die;
            //Inserção do Idempresa, pois na hora de mostrar os dados estava trazendo de todas as empresas (Lidiane - 24-04-2020)
            $data2 = $data2.' 23:59:59';
            $s1="select 
                        p.idpessoa,nome,dataponto,idrhevento,idrhtipoevento,hora,semana,statusevento,entsaida,obs
                    from vw_ponto p ".$strjoin."
                    where data between '".$data1."' and '".$data2."'
                    and p.statusevento!='INATIVO'                   
                       ".$strin."
                    order by nome,hora";
            echo "<!-- ".$s1." -->";	//echo $_sqlresultado;
            // echo $s1;	//echo $_sqlresultado;
            $re1= d::b()->query($s1) or die("erro ao buscar os pontos  sql=".$s1);

            while($r=mysqli_fetch_assoc($re1)){
            // $arrayp[$r['idpessoa']]['semana'][$rw['diabusca']]=$r['semana'];  
                $arrayp[$r['idpessoa']][$r['nome']][$r['dataponto']][$r['idrhevento']]['idrhevento']=$r['idrhevento'];  
                $arrayp[$r['idpessoa']][$r['nome']][$r['dataponto']][$r['idrhevento']]['semana']=$r['semana'];
                $arrayp[$r['idpessoa']][$r['nome']][$r['dataponto']][$r['idrhevento']]['hora']=$r['hora'];
                $arrayp[$r['idpessoa']][$r['nome']][$r['dataponto']][$r['idrhevento']]['statusevento']=$r['statusevento'];
                $arrayp[$r['idpessoa']][$r['nome']][$r['dataponto']][$r['idrhevento']]['entsaida']=$r['entsaida'];  
                $arrayp[$r['idpessoa']][$r['nome']][$r['dataponto']][$r['idrhevento']]['obs']=$r['obs'];
                $arrayp[$r['idpessoa']][$r['nome']][$r['dataponto']][$r['idrhevento']]['idrhtipoevento']=$r['idrhtipoevento'];

            } 


            
    }elseif($tiporel=="SIMPLES"){
        $data2 = $data2.' 23:59:59';
        $s1="select e.idrhevento,e.idpessoa,p.nomecurto as nome,e.idrhtipoevento,e.dataevento as dataponto,t.evento,sum(e.valor)as valor,e.status
        from rhtipoevento t 
        join  pessoa p 
        join rhevento e on(e.idrhtipoevento = t.idrhtipoevento 
            and p.idpessoa=e.idpessoa
            and e.status LIKE 'QUITADO%'
            and e.dataevento between '".$data1."' and '".$data2."'
            )
        ".$strjoin."
        where 				
        (t.flhtotais='Y' or t.flhtotaisajust='Y' or t.flhext='Y' or  t.flhextcalc='Y'or e.idrhtipoevento = 23 )
        ".$strin."
        group by idpessoa,idrhtipoevento";

        echo "<!-- ".$s1." -->";	//echo $_sqlresultado;
         // echo $s1;	//echo $_sqlresultado;
         $re1= d::b()->query($s1) or die("erro ao buscar os eventos simples sql=".$s1);

        while($r=mysqli_fetch_assoc($re1)){
           // $arrayp[$r['idpessoa']]['semana'][$rw['diabusca']]=$r['semana'];  
            $arrayp[$r['idpessoa']][$r['nome']]['QUITADO'][$r['idrhtipoevento']]['evento']=$r['evento'];  
            $arrayp[$r['idpessoa']][$r['nome']]['QUITADO'][$r['idrhtipoevento']]['valor']=$r['valor'];  					
        }  
        
        $s1="select e.idrhevento,e.idpessoa,p.nomecurto as nome,e.idrhtipoevento,e.dataevento as dataponto,t.evento,sum(e.valor)as valor,e.status
        from rhtipoevento t 
        join  pessoa p 
        join rhevento e on(e.idrhtipoevento = t.idrhtipoevento 
            and p.idpessoa=e.idpessoa
            and e.status LIKE 'PENDENTE'
            and e.dataevento between '".$data1."' and '".$data2."'
            )
        ".$strjoin."
        where 				
         (t.flhtotais='Y' or t.flhtotaisajust='Y' or t.flhext='Y' or  t.flhextcalc='Y'or e.idrhtipoevento = 23 )
        ".$strin."
        group by idpessoa,idrhtipoevento";

        echo "<!-- ".$s1." -->";	//echo $_sqlresultado;
         // echo $s1;	//echo $_sqlresultado;
        $re1= d::b()->query($s1) or die("erro ao buscar os eventos simples sql=".$s1);

        while($r=mysqli_fetch_assoc($re1)){
           // $arrayp[$r['idpessoa']]['semana'][$rw['diabusca']]=$r['semana'];  
            $arrayp[$r['idpessoa']][$r['nome']]['PENDENTE'][$r['idrhtipoevento']]['evento']=$r['evento'];  
            $arrayp[$r['idpessoa']][$r['nome']]['PENDENTE'][$r['idrhtipoevento']]['valor']=$r['valor'];  					
        }   
    }else if($tiporel=="EXTRA"){

        // GVT - 25/06/2021 - Consulta p/ buscar banco de horas

        $hoje = strtotime(date("Y-m-d"));
        $dia = date("d", $hoje);
        if($dia == 01){
            $ontem = date("Y-m-d", time() - 60 * 60 * 24);
            $dia1 = $ontem;
            $diaant = $ontem;
        }else{
            $dia1 = date("Y-m-01");
        }
        $s1 = "SELECT f.idpessoa, f.nome 
        from pessoa f 
        where 1 -- f.status = 'ATIVO' 
        and 
            exists (
                select 1 from ponto p where f.idpessoa = p.idpessoa ".$strin." ) "
            .getidempresa('f.idempresa','pessoa')."
        order by f.nome";
        for ($i=0;;$i++) {

            $s="SELECT DATE_ADD('".$dia1."', INTERVAL ".$i." DAY) as diabusca,
                DATE_FORMAT( DATE_ADD('".$dia1."', INTERVAL ".$i." DAY),'%W') as semana,
                case  when DATE_ADD('".$dia1."', INTERVAL ".$i." DAY) > '".$diaant."' then 'Y' 
                else 'N' end  as maior";
            $re= d::b()->query($s) or die("erro ao buscar os pontos pendentes sql=".$s);
            $rw=mysqli_fetch_assoc($re);
            if ($rw['maior'] =='Y') {
                break;
            }else{
                
                $re1= d::b()->query($s1) or die("erro ao buscar os funcionarios dos pontos  sql=".$s1);
                
                while($r=mysqli_fetch_assoc($re1)){
                    $arrayp[$r['idpessoa']][$r['nome']]['PENDENTE']['dataponto'][$rw['diabusca']][1]['semana']=$rw['semana'];                         
                }
            }
        }
        $mesant = date('Y-m-d', strtotime('last day of last month'));
        
        $data2 = $data2.' 23:59:59';
        //mostra apenas as horas extras do meu mes
        if(empty($data1)){
            $datacon = "contratacao";
            
        }else{
            $datacon = "'".$data1."'";
            
        }
        $mesnow = date("Y-m%");
            $and = "and e.dataevento NOT LIKE '".$mesnow."'";
        $s1 = "SELECT e.idpessoa,p.nome,e.idrhevento,t.evento,e.dataevento,e.hora,sum(e.valor) as valor,e.parcelas,e.parcela,e.status,e.situacao,t.formato,t.tipo,e.idobjetoori,e.idrhtipoevento
                from rhevento e left join rhtipoevento t on(t.idrhtipoevento=e.idrhtipoevento)
                join pessoa p on(p.idpessoa =e.idpessoa)
                where e.idrhtipoevento = 6 and
                e.idpessoa in (".$idpessoab.")            
                and e.situacao='P'
                and e.status='PENDENTE'
                ".$and." group by e.idpessoa
                order by e.dataevento,e.hora; -- pendente";
        if($inspecionarsql){
            echo "<!-- Consulta4: ".$s1." -->";
        }
        $re1= d::b()->query($s1) or die("erro ao buscar os pontos  sql=".$s1);
        
        while($r=mysqli_fetch_assoc($re1)){
            $arrayp[$r['idpessoa']][$r['nome']]['PENDENTE']['datapontopendente'][$r['dataevento']]['idrhevento']=$r['idrhevento'];  
            $arrayp[$r['idpessoa']][$r['nome']]['PENDENTE']['datapontopendente'][$r['dataevento']]['valor'] = $r['valor'];
            $arrayp[$r['idpessoa']][$r['nome']]['PENDENTE']['datapontopendente'][$r['dataevento']]['idrhtipoevento']=$r['idrhtipoevento'];
        } 
        $sqlp = "SELECT min(contratacao) as contratacao from pessoa where idpessoa in(".$idpessoab.") and status ='ATIVO' order by idpessoa asc";
        $rep =  d::b()->query($sqlp);
        $rp=mysqli_fetch_assoc($rep);
        if(empty($data1) or (strtotime($data1) < strtotime($rp['contratacao']))){
            $data1 = $rp['contratacao'];
        }
        $s1 = "SELECT f.idpessoa, f.nome 
                FROM pessoa f 
                where 1 -- f.status = 'ATIVO' 
                 and 
                    exists (
                        select 1 from ponto p where f.idpessoa = p.idpessoa ".$strin." ) "
                    .getidempresa('f.idempresa','pessoa')."
                order by f.nome";
        for ($i=0;;$i++) {
    
            $s="SELECT DATE_ADD('".$data1."', INTERVAL ".$i." DAY) as diabusca,
                DATE_FORMAT( DATE_ADD('".$data1."', INTERVAL ".$i." DAY),'%W') as semana,
                case  when DATE_ADD('".$data1."', INTERVAL ".$i." DAY) > '".$mesant."' then 'Y' 
                else 'N' end  as maior";
            $re= d::b()->query($s) or die("erro ao buscar os pontos pendentes sql=".$s);
            $rw=mysqli_fetch_assoc($re);
        
            if ($rw['maior'] =='Y') {
                break;
            }else{

                $re1= d::b()->query($s1) or die("erro ao buscar os funcionarios dos pontos  sql=".$s1);

                while($r=mysqli_fetch_assoc($re1)){
                    $arrayp[$r['idpessoa']][$r['nome']]['PENDENTE']['datapontopendente'][$rw['diabusca']]['semana']=$rw['semana'];                         
                }
            }
        }
        $data2 = $data2.' 23:59:59';
        //mostra apenas as horas extras do meu mes
        $s2 = "SELECT p.idpessoa,nome,dataponto,idrhevento,idrhtipoevento,hora,semana,statusevento,entsaida,obs
                from vw_ponto p ".$strjoin."
                where data between '".$dia1."' and '".$diaant." 23:59:59'
                and p.statusevento !='INATIVO'
                AND (STR_TO_DATE(data, '%Y-%m-%d') BETWEEN '".$dia1."' AND '".$diaant."')
                ".getidempresa('p.idempresa','pessoa')."
                ".$strin."
                order by nome,dataponto; -- dataponto";
        if($inspecionarsql){
            echo "<!-- Consulta4: ".$s2." -->";
        }
        $re2= d::b()->query($s2) or die("erro ao buscar os pontos  sql=".$s2);

        while($r=mysqli_fetch_assoc($re2)){
            $arrayp[$r['idpessoa']][$r['nome']]['PENDENTE']['dataponto'][$r['dataponto']]['idrhevento']=$r['idrhevento'];  
            $arrayp[$r['idpessoa']][$r['nome']]['PENDENTE']['dataponto'][$r['dataponto']]['semana']=$r['semana'];
            $arrayp[$r['idpessoa']][$r['nome']]['PENDENTE']['dataponto'][$r['dataponto']]['hora']=$r['hora'];
            $arrayp[$r['idpessoa']][$r['nome']]['PENDENTE']['dataponto'][$r['dataponto']]['statusevento']=$r['statusevento'];
            $arrayp[$r['idpessoa']][$r['nome']]['PENDENTE']['dataponto'][$r['dataponto']]['entsaida']=$r['entsaida'];  
            $arrayp[$r['idpessoa']][$r['nome']]['PENDENTE']['dataponto'][$r['dataponto']]['obs']=$r['obs'];
            $arrayp[$r['idpessoa']][$r['nome']]['PENDENTE']['dataponto'][$r['dataponto']]['idrhtipoevento']=$r['idrhtipoevento'];
        }

    }  
}

if($_GET and !empty($arrayp)){
	
?>
<html>
<head>
<title>Banco de Horas</title>

<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<style>
html{
	font-family: Arial, FreeSans, Sans,  Serif, SansSerif;
	font-size: 11px;
	margin:0px;
	padding:0px;
}

body{
	margin:0px;
	padding:0px;
}
.tbrepheader{
	border: 0px;
	width: 100%;
}
.tbrepheader .header{
	font-size: 13px;
	font-weight: bold;
}

.tbrepheader .subheader{
	font-size: 10px;
	color: gray;
}
.tbrepheader .titulo{
	font-size: 18px;
	font-weight: bold;
}
.tbrepheader .res{
	font-size: 18px;
}
.normal{
	border: 1px solid silver;
	border-collapse: collapse;	
}

.normal td{
	border: 1px solid silver;
	padding: 0px 3px 0px 3px;
}

.normal .header{
	font-size: 10px;
	font-weight: bold;
	color: rgb(75,75,75);
	background-color: rgb(222,222,222);
}
.normal .res{
	font-size: 11px;
}
.normal .res .link{
	background-color:#FFFFFF;
	cursor:pointer;
}
.normal .res .tot{
	background-color:#E8E8E8;
	font-weight: bold;	
	text-align: center;
}
.normal .res .inv{
	border: 0px;
}
.normal .tdcounter{
	border:1px dotted rgb(222,222,222);
	background-color:white;
	color:silver;
	font-size:8px;
}
.newreppage{
	page-break-before: always;
}
.fldsheader{
	border:none;
	border-top: 2px solid silver;
	height: 0px;
	margin: 0px;
	padding: 0px;
	padding-bottom: 5px;
	padding-left:5px;
}
.fldsheader legend{
	font-size: 8px;
	color: gray;
	background-color: white;
}
.fldsfooter{
	border:none;
	border-top: 2px solid silver;
	height: 0px;
	margin: 0px;
	padding: 0px;
	margin-top: 5px;
	padding-left:5px;
}
.fldsfooter legend{
	font-size: 8px;
	color: gray;
	background-color: white;
}
a.btbr20{
	display: none;
}

/* Botao branco fonte 8 */
a.btbr20:link{
	position: fixed;

	right: 15px;

    font-weight: bold;
    font-size:20px;
    color: silver;
    
	border: 1px solid #d7d7d7;
    cursor: pointer;

    padding-left: 5px;
    padding-right: 5px;
    padding-bottom: 1px;
    margin-left: 5px;
      
	background: #cccccc; /* para browsers sem suporte a CSS 3 */

	/* Gradiente */
	filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ececec', endColorstr='#dcdcdc'); /* IE */
	background: -webkit-gradient(linear, left top, right top, from(#ececec), to(#dcdcdc)); /* webkit */
	background: -moz-linear-gradient(top,  #ececec, #dcdcdc); /* FF */

    /* Arredondamento */
	-moz-border-radius: 8px;
 	-webkit-border-radius: 8px;
 	border-radius: 8px 8px 8px 8px;
 	
 	text-decoration: none;
}
a.btbr20:hover
{
    font-weight: bold;
    font-size:20px;
    color: silver;
    
	border: 1px solid #d7d7d7;
    cursor: pointer;

    padding-left: 5px;
    padding-right: 5px;
    padding-bottom: 1px;
    margin-left: 5px;

	background: #eaeaf4; /* para browsers sem suporte a CSS 3 */

	/* Gradiente */
	filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ff0000', endColorstr='#c02900'); /* IE */
	background: -webkit-gradient(linear, left top, right top, from(#ff0000), to(#c02900)); /* webkit */
	background: -moz-linear-gradient(top, #ffffff, #e1e1e1); /* FF */

    /* Arredondamento */
	-moz-border-radius: 8px;
 	-webkit-border-radius: 8px;
 	border-radius: 8px 8px 8px 8px;
 	text-decoration: none;
} 
a.btbr20:visited {
	border: 1px solid silver;
	color:white;
	text-decoration: none;
}
a.btbr20{
	display: block;
}


</style>
</head>
<body>
                
                  <?
        $l=0;
        if($tiporel=="DETALHADO"){
		foreach ($arrayp as $idpessoa => $arrayfunc) {
            $totalh=0;
            $totalhn=0;
            $totalp=0;
            $thextra=0;
            $thextradin=0;
            $dinhoraextra=0;
            $tdiastrab=0;
?>
           
<?
			foreach ($arrayfunc as $nome => $arrdata) {
                $l=$l+1;
                if($l>1){
 ?>
                <div style="page-break-before: always;"></div>
 <?

                }
?>
             <br>
 <?
 
                $sqlo="select p.idpessoa,p.idempresa,p.nome,p.cpfcnpj,e.razaosocial,e.cnpj,
                e.xlgr,e.nro,e.xbairro,e.xmun,e.uf,e.cep,p.pis,
                dma(p.contratacao) as contratacao,c.cargo,s.setor
                from pessoa p 
                join empresa e on(e.idempresa=p.idempresa)
                left join sgcargo c on(c.idsgcargo=p.idsgcargo)
                left join pessoaobjeto po on(po.idpessoa=p.idpessoa 
                and po.tipoobjeto='sgsetor')
                left join sgsetor s on(s.idsgsetor=po.idobjeto)
                where p.idpessoa=".$idpessoa;
                $reso = d::b()->query($sqlo) or die("Erro 1 - Ao buscar informacoes do funcionario ".mysqli_error(d::b())."\n".$sqlo);
                $rowo=mysqli_fetch_assoc($reso);
 ?>            
             
            <table class="normal"  align="center">
            <thead>
            <tr class="res" >
                <td style="text-align:center" colspan="7" ><div> Período  de <b><?=$dataevento_1?></b> até <b><?=$dataevento_2?></b> </div><p></td>
            </tr>
            <tr class="res"  >    
                <td style="text-align:left" colspan="7" >
                Colaborador: <b><?=$rowo['nome']?></b>  Pis: <b><?=$rowo['pis']?></b>  Data Admissão: <b><?=$rowo['contratacao']?></b></br>
                Cargo/função: <b><?=$rowo['cargo']?></b>  Departamento/Setor: <b><?=$rowo['setor']?></b></br><p>
                </td>
            </tr>
            <tr class="res" >
                    <td  style="text-align:left" colspan="7">
                    Empregador: <b><?=$rowo['razaosocial']?></b>  CNPJ: <b><?=formatarCPF_CNPJ($rowo['cnpj'],true)?></b></br>
                    Endereço: <b><?=$rowo['xlgr']?> <?=$rowo['nro']?></b>  Bairro: <b><?=$rowo['xbairro']?></b> Cidade: <b><?=$rowo['xmun']?>-<?=$rowo['uf']?></b> CEP: <b><?=$rowo['cep']?> </b></br><p>
                     
                    </td> 
                   
                </tr>
                <tr class="header"> 
                    <td class="header"  align="center" >Dia </td>
                    <td class="header" style="text-align:left">Pontos</td>                                  
                    <td class="header" align="center">Horas</td>                    
                    <td class="header" align="center">Horas Ajust.</td>
                    <td class="header" align="center">Horas Extras</td>
                    <td class="header <?=$hidden?>" align="center">Horas Not.</td>
                    <td class="header"  align="center">Dias Trab.</td>

                    
                </tr>
            </thead>
            <tbody>
 <?                
                $corpotrjustificativa="";
				foreach ($arrdata as $data => $arraponto) {
                    $sm="select WEEKDAY('".$data."') as dsem";
                    $rm=d::b()->query($sm);
                    $wm=mysqli_fetch_assoc($rm);
                    if($wm['dsem']==6 or $wm['dsem']==5){
                        $corf="yellow";
                        $horapd=0;
                    }else{
                         $corf="";
                         $horapd=8;
                    }
                    $sf="select obs from feriado where status='ATIVO' and idempresa in(8,".$rowo['idempresa'].") and dataferiado ='".$data."'";
                    $rf=d::b()->query($sf);
                    $qtf=mysqli_num_rows($rf);
                    $wf=mysqli_fetch_assoc($rf);
                    if($qtf>0){
                        $corfr="#ff0000ad";
                        $horapd=0;
                    }

                    $timestampdt = strtotime($data);
                    $dia= date("d", $timestampdt);
                    $mes= date("m", $timestampdt);
                    $ano= date("Y", $timestampdt);

                    $folha = new Folha($dia,$dia,$mes,$ano,$idpessoa);

                    $calendario=$folha->getCalendario($rowo['idempresa']); 

                    $horasexec=$folha->gethorasExec();

                    $horasplan=$folha->getHorasPlan();

                    //echo("Horas realizadas=".$horasexec['hora']);
                   // echo("Horas planejadas=".$horasplan['hora']);

                    $horas=$folha->gethoras();
/*
                    echo('Horas='.$horasexec['hora'].'<br>');
                    echo('H ajustada='.$horas['horaajustada'].'<br>');
                    echo('H Extra='.$horas['horaextra'].'<br>');
                    echo('H Extra dinheiro='.$horas['horaextradinheiro'].'<br>');
*/
                    $sqlhe="select * from rhevento e
                            where e.idrhtipoevento = 6 
                            and e.idpessoa = ".$idpessoa." 
                            and e.dataevento = '".$data."'
                            and e.status = 'QUITADO TRANSFERENCIA'
                            and  exists (select 1 from rhevento d 
                                            where d.idobjetoori = e.idrhevento 
                                            and d.tipoobjetoori='rhevento' 
                                            and d.idrhtipoevento = 435 
                                            and d.status!='INATIVO')";
                    $rhe=d::b()->query($sqlhe);
                    $qtdhe=mysqli_num_rows($rhe);
                    if( $qtdhe>0){
                        $horas['horaextra']=0;
                        $horas['diastrab']=1;
                    }


                    $totalh=$totalh+$horasexec['hora'];
                    $totalhn=$totalhn+$horasexec['horanot'];
                    $tdiastrab=$tdiastrab+$horas['diastrab'];
                    $totalp=$totalp+$horas['horaajustada'];
                  

                  
                    $horamaior=$horas['horaajustada']+0.18;
                    $horamenor=$horas['horaajustada']-0.18;

                 
                    
               
        ?>  
                <tr class="res " style="background-color: <?=$corf?>">               
                    <td ><?=dma($data)?> - <?=substr($arraponto[1]['semana'],0,3)?> <font color="red"> <?=$wf['obs']?></font></td>
                    <td class="nowrap">
<?                  
                    $horadia=0;
                    foreach ($arraponto as $idrhevento => $value) {
                        if($idrhevento>1){
                           $status= traduzid('rhevento', 'idrhevento', 'status', $idrhevento);

                            if($value['entsaida']=='E'){
                               // $cor="#c2f5c1";
                                $cbt="btn-success";
                            }else{
                              // $cor="#dfdfe8"; 
                               $cbt="btn-primary ";
                            }
                      
                                
                            $evento= traduzid('rhtipoevento', 'idrhtipoevento', 'evento', $value['idrhtipoevento']);
                            
                            $cor="";

?>
                            <span >
                                <i  title="<?=$evento?>" class="pointer" onclick="javascript:janelamodal('./?_modulo=rhevento&_acao=u&idrhevento=<?=$value['idrhevento']?>')"> <?=$value['hora']?></i>
                                <?if($value['idrhtipoevento']!=1){?>
                                <font color="red"><b>*</b></font>
                                <?}?>
                            </span>&nbsp;&nbsp;&nbsp;
<?                        
                           
                        }
                    }//foreach ($arraponto as $idrhevento => $value) {
                    
                    //bonus de horas em valor
                    $sqe="select t.tipo,e.idrhevento,t.evento,t.eventocurto,e.valor from rhtipoevento t,rhevento e
                                where t.formato='H' and t.flgponto = 'Y'
                                and t.flhtotais = 'N' and t.flhtotaisajust  = 'N' and t.flhext  = 'N' and t.flhextcalc  = 'N'
                                and e.idrhtipoevento=t.idrhtipoevento
                                and e.status!='INATIVO'
                                and e.valor is not null
                                and e.idpessoa = ".$idpessoa." 
                                and e.dataevento = '".$data."'";
                    $re=d::b()->query($sqe);
                    while($roe=mysqli_fetch_assoc($re)){
                         if($roe['eventocurto']){
                             $title=$roe['evento'];
                             $tevento=$roe['eventocurto'];
                         }else{
                             $title=$roe['evento'];
                             $tevento=$roe['evento'];
                         }
                         
                        if($roe['tipo']=='C'){                            
 ?>                       
                                <?=$tevento?>
                                &nbsp;&nbsp;&nbsp;                              
                                    +  <?=$roe['valor']?>
                                &nbsp;&nbsp;&nbsp;
<?                           
                        }else{
 ?>                            
                                <?=$tevento?>
                                &nbsp;&nbsp;&nbsp;                               
                                 -  <?=$roe['valor']?>                             
                            &nbsp;&nbsp;&nbsp;                    
                            
<?   
                        }
                    }//while($roe=mysqli_fetch_assoc($re)){
                  
                 
                    ?>
                        
                    </td>
                                    
                    <td align="center" class="nowrap"><?=convertHoras($horasexec['hora'])?></td>
                    <td align="center"><?=convertHoras($horas['horaajustada'])?></td>
                    <td align="center">
                    <? 
                   
                   // echo('('.$horadia."-".$horapd.') ');
                    $thextra=$thextra+$horas['horaextra'];
                    if($horas['horaextra']<0){echo "-" ;}
                    echo convertHoras(abs($horas['horaextra']));
                    //echo ($horas['horaextra']);
                    $thextradin=$thextradin+$horas['horaextradinheiro'];
                    $dinhoraextra=$dinhoraextra+$horas['dinheirohoraextra'];
                    ?>
                    </td>                    
                    <td align="center"><?=convertHoras($horasexec['horanot'])?></td>
                    <td align="center"><?=$horas['diastrab']?></td>
                    <?
                     $sj="select dma(dataevento) as dataevento,idrhjustificativa,justificativa,criadopor,dmahms(criadoem) as criadoem
                     from rhjustificativa where idpessoa=".$idpessoa." and dataevento='".$data."' order by criadoem desc limit 1";
                     $rej=d::b()->query($sj);
                     while($rowj=mysqli_fetch_assoc($rej)){
                       
                        $corpotrjustificativa.="<tr>
                        <td>".$rowj['dataevento']."</td>
                        <td> ".ucwords(strtolower($rowj['justificativa']))." </td> 
                        <td> ".ucwords($rowj['criadopor'])."</td>
                        <td> ".$rowj['criadoem']."</td></tr>";
                    
                     }
                    ?>
                </tr>
            
<?           
                
            }


            /*
            $totalhn=0;
            $totalp=0;
            $thextra=0;
            $thextradin=0;
            $dinhoraextra=0;
            $tdiastrab=0;
            */
                ?>
                <tr class="res">
                    <td colspan="2"><b>Total do Período:</b></td>
                    <td align="center">
                    <b>
					<?
                    if($totalh<0){echo "-" ;}
                    echo convertHoras(abs($totalh));
                    //echo ($thextra);
                    ?>
                    </b>
					</td>
                    <td align="center" >
                    <b>
					<?
                    if($totalp<0){echo "-" ;}
                    echo convertHoras(abs($totalp));
                    //echo ($thextra);
                    ?>
                    </b>
					</td>
					<td align="center" > 
                        <a title="Horas Extras" class="pointer" onclick="javascript:janelamodal('./?_modulo=rhtipoeventofolha&idrhtipoevento=6&idpessoa=<?=$idpessoa?>')">
                        <strong> 
                        <?
                            if($thextra<0){echo "-" ;}
                            echo convertHoras(abs($thextra));
                            //echo ($thextra);
                        ?>        
                        </strong>
                        </a>
					</td>
					<td align="center" > 
                    <b>
					<?
                    if($totalhn<0){echo "-" ;}
                    echo convertHoras(abs($totalhn));
                    //echo ($thextra);
                    ?>
                    </b>
					</td>
					<td align="center" ><b><?=$tdiastrab?></b></td>
                    <!-- td align="center" ><?=$totalh23?></td -->
                    <!--td class="nowrap <?=$hidden?>"><?if($thextradin>0){ echo convertHoras($thextradin)?> - <?echo $dinhoraextra;}?></td -->
                </tr>
                <?
                /*
                $sqlx="select sum(e.valor) valor,t.evento 
                        from rhevento e join rhtipoevento t on(t.idrhtipoevento=e.idrhtipoevento)
                        where  e.idrhtipoevento in(6) 
                        and e.status='PENDENTE' 
                        and e.dataevento < '".$datafim."'
                        and e.idpessoa =".$idpessoa." 
                        and e.valor!=0";
                $resx= d::b()->query($sqlx) or die("Erro ao buscar total hora extra sql=".$sqlx);
                $rowx=mysqli_fetch_assoc($resx);
                $sqlx1=" select  sum(e.valor) valor,t.evento 
                        from rhevento e join rhtipoevento t on(t.idrhtipoevento=e.idrhtipoevento)
                        where  e.idrhtipoevento in(432) 
                        and e.status='PENDENTE' 
                        and e.dataevento < '".$datafim."'
                        and e.idpessoa =".$idpessoa." 
                        and e.valor!=0";
                $resx1= d::b()->query($sqlx1) or die("Erro ao buscar total dia trabalhado sql=".$sqlx1);
                $rowx1=mysqli_fetch_assoc($resx1);
                if(empty($rowx1['valor'])){$rowx1['valor']=0;}
                */
                ?>
                <!-- tr class="res">
                    <td colspan="4"><b>Total Banco de Horas:</b></td>
                   
                    <td align="center" ><b><?=$rowx['valor']?></b></td>
                    <td></td>
                    <td align="center" ><b><?=$rowx1['valor']?></b></td>
                </tr>
                <tr class="res">
                    <td colspan="4"><b>Total Acumulado:</b></td>                    
                    <td  align="center" ><b><?=$rowx['valor']+$thextra?></b></td>
                    <td></td>
                    <td align="center" ><b><?=$rowx1['valor']+$tdiastrab?></b></td>
                </tr -->
                </tbody>
          </table> 
          <?
          if(!empty($corpotrjustificativa)){
        ?>
           <br>
            <table class="normal" style="text-align: center;" align="center">
                <tr style="color: rgb(75,75,75);">                                
                    <th colspan="4" style=" text-align: center">Justificativas</th>
                </tr>
                <tr>
                    <td class="tbl" style="color: rgb(75,75,75); text-align: center; background-color: rgb(222,222,222);">Dia</td>
                    <td class="tbl" style="color: rgb(75,75,75); text-align: center; background-color: rgb(222,222,222);">Justificativa</td>
                    
                    <td class="tbl" style="color: rgb(75,75,75); text-align: center; background-color: rgb(222,222,222);">Por</td>
                    <td class="tbl" style="color: rgb(75,75,75); text-align: center; background-color: rgb(222,222,222);">Em</td>                                
                </tr>
                <?=$corpotrjustificativa?>
          </table>
        <?
          }
          $corpotrjustificativa='';
          ?>       
       <?
  /*
select  sum(e.valor) valor,t.evento, MONTH(e.dataevento) as mes 
from rhevento e join rhtipoevento t on(t.idrhtipoevento=e.idrhtipoevento)
where  e.idrhtipoevento in(6) 
and e.status='PENDENTE' 
and e.dataevento > DATE_SUB('".$data2."', INTERVAL 6 month)
and e.dataevento <='".$data2."'
and e.idpessoa=".$idpessoa."                        
and e.valor!=0 group by mes;
                 


select -- e.*
sum(e.valor) valor,t.evento , MONTH(e.dataevento) as mes 
from rhevento e join rhtipoevento t on(t.idrhtipoevento=e.idrhtipoevento)
where  e.idrhtipoevento in(6) 
and e.status='QUITADO TRANSFERENCIA' 
and e.dataevento > DATE_SUB('".$data2."', INTERVAL 6 month)
and e.dataevento <='".$data2."'
and e.idpessoa=".$idpessoa."
and e.valor!=0 group by mes;


$scp = "select  sum(e.valor) valor,t.evento, MONTH(e.dataevento) as mes 
from rhevento e join rhtipoevento t on(t.idrhtipoevento=e.idrhtipoevento)
where  e.idrhtipoevento in(6) 
and e.status='PENDENTE' 
and e.dataevento > DATE_SUB('".$data2."', INTERVAL 6 month)
and e.dataevento <='".$data2."'
and e.idpessoa=".$idpessoa."                        
and e.valor!=0 group by mes";
$cp = d::b()->query($scp) or die("erro ao buscar horas pendentes sql=" . $scp);
$hecp = mysqli_fetch_assoc($cp);


            $contratacao = "SELECT contratacao from pessoa where idpessoa = $idpessoa";
            $ct = d::b()->query($contratacao) or die("erro ao buscar os pontos pendentes sql=" . $s);
            $rct = mysqli_fetch_assoc($ct);
           //$data1 
            //$data2
           
            
            $timestamp2 = strtotime($data1); 
            //$mesfim= ltrim(date('m', $timestamp2), '0');
                       
            $mesfim= date('m', $timestamp2);
           

            $histData1 = date("Y-m-d", mktime(0, 0, 0, $mesfim - 5, 1, date('Y', $timestamp2)));
            $histData2 = date("Y-m-d", mktime(23, 59, 59, $mesfim + 1 , date('d') - date('j'), date('Y', $timestamp2)));
            
            if(strtotime($rct['contratacao']) > strtotime($histData1)){
               $histData1 = $rct['contratacao'];
            }
            $arrHist =array();
            for ($i = 0;; $i++) {
                $s = "SELECT DATE_ADD('" . $histData1 . "', INTERVAL " . $i . " DAY) as diabusca,
        DATE_FORMAT( DATE_ADD('" . $histData1 . "', INTERVAL " . $i . " DAY),'%W') as semana,
            case  when DATE_ADD('" . $histData1 . "', INTERVAL " . $i . " DAY) > '" . $histData2 . "' then 'Y' 
            else 'N' end  as maior";
                $re = d::b()->query($s) or die("erro ao buscar os pontos pendentes sql=" . $s);
                $rw = mysqli_fetch_assoc($re);

                if ($rw['maior'] == 'Y') {
                    break;
                } else {

                    $s1 = "select p.idpessoa as idpessoa,p.nome 
            from pessoa p " . $strjoin . "
            where 1 -- p.status='ATIVO'
            and p.idpessoa = ".$idpessoa."
            " . $strin . "
                order by p.nomecurto";
                    echo "<!-- " . $s1 . " -->";
                    $re1 = d::b()->query($s1) or die("erro ao buscar os funcionarios dos pontos  sql=" . $s1);

                    while ($r = mysqli_fetch_assoc($re1)) {
                        $arrHist[$r['idpessoa']][$r['nome']][$rw['diabusca']][1]['semana'] = $rw['semana'];
                    }
                }
            }

            */
//INICIO EXTRA


?>
 <br>
<table class="table  planilha">
<?
  
    ?>
    <thead>
                    
        <tr class="header" style="background-color: #eee;">                                    
            <td class="header" align="center">HORAS PENDENTES</td>
            <td class="header" align="center">HORAS EXTRAS</td>
            <td class="header" align="center">TOTAL</td>

        </tr>
    </thead>
    <tbody>

        <?

            //$mesnow = date("Y-m%");
            $and = "and e.dataevento <'".$data1."'";
            $s1 = "SELECT e.idpessoa,p.nome,e.idrhevento,t.evento,e.dataevento,e.hora,sum(e.valor) as valor,e.parcelas,e.parcela,e.status,e.situacao,t.formato,t.tipo,e.idobjetoori,e.idrhtipoevento
                    from rhevento e left join rhtipoevento t on(t.idrhtipoevento=e.idrhtipoevento)
                    join pessoa p on(p.idpessoa =e.idpessoa)
                    where e.idrhtipoevento = 6 and
                    e.idpessoa in (".$idpessoa.")            
                    and e.situacao='P'
                    and e.status='PENDENTE'
                    ".$and." group by e.idpessoa
                    order by e.dataevento,e.hora; -- pendente";
                        if($inspecionarsql){
                            echo "<!-- Consulta4: ".$s1." -->";
                        }
            $re1= d::b()->query($s1) or die("erro ao buscar os pontos  sql=".$s1);

            $re=mysqli_fetch_assoc($re1);
            $totall=0
    
         
        ?>
        
            <tr class="res"> 
                <td align="center">					
                <?      
                    $valor=$re['valor']; 
                    if($re['valor']<0){echo "-" ;}
                    echo convertHoras(abs($re['valor']));
                ?>
                </td>	
                
                <td align="center">
                    <?
                    if($thextra<0){echo "-" ;}                            
                    echo convertHoras(abs($thextra));
                    reset($arrtipoev);
                    $totall=$valor + $thextra?>
                </td>
                <td align="center">
                    <?if($totall<0){echo "-" ;}
                    echo convertHoras(abs($totall))?>
                </td>	
            </tr>
            </tbody>
        </table>
        <br>
        <table style="font-size: 11px; margin-top: 50px;" align="center">
        <tr><td>Assinatura:</td><td>___________________________________________</td></tr>
        </table>
        <?
    
  







//FIM EXTRA

/*

            foreach ($arrHist as $idpessoa => $arrf) {
                $totalh = 0;
                $totalhn = 0;
                $totalp = 0;
                $thextra = 0;
                $thextradin = 0;
                $dinhoraextra = 0;
                $tdiastrab = 0;

                foreach ($arrf as $nome => $arrdata) {
                    foreach ($arrdata as $data => $arraponto) {
                        $timestampdt = strtotime($data);
                        $d = date("d", $timestampdt);
                        $m = date("m", $timestampdt);
                        $a = date("Y", $timestampdt);
*/
/*
                        $hExtra = new Folha($d, $d, $m, $a, $idpessoa);

                        $horasexec = $hExtra->gethorasExec();
                        $horasplan = $hExtra->getHorasPlan();
                        $hrs =  $hExtra->gethoras();


                        $arrHrExtraMes[$m] += $hrs['horaextra'];
*/
/*
                        $arrmes[] = $m . "/" . $a;
                    }
                }
            }
*/

//echo('hermes');print_r($arrmes); echo('/n');
                            
                        ?>
                        <br>
                        <?
                        /*
                         retirado a pedido do RH Inicio
                        ?>
                        <!--table class="normal" style="text-align: center;" align="center">
                            <tr style="color: rgb(75,75,75);">                                
                                <th colspan="4" style=" text-align: center">Histórico de Horas Extras </th>
                            </tr>
                            <?
                            $scpx = " select  ifnull(sum(e.valor),0) as valor,t.evento,SUBSTRING(dma( DATE_SUB('".$data1."', INTERVAL 6 month)),4,7)  as periodo 
                                    from rhevento e join rhtipoevento t on(t.idrhtipoevento=e.idrhtipoevento)
                                    where  e.idrhtipoevento in(6) 
                                    and e.status='PENDENTE' 
                                    and e.dataevento < DATE_SUB('".$data1."', INTERVAL 5 month)
                                    and e.idpessoa=".$idpessoa."                       
                                    and e.valor!=0";
                            $cpx = d::b()->query($scpx) or die("erro ao buscar horas extras pendentes anteriores sql=" . $scpx);
                     
                            $hecpx = mysqli_fetch_assoc($cpx);
                           
                            $sinal="";
                            if ($hecpx['valor'] < 0) {                              
                                $sinal = "-";
                            }
                            if($hecpx['valor'] !=0){
                                $alertcor='color:red;';
                            }else{
                                $alertcor='';
                            }

                            ?>
                            
                          
                            <tr>
                              
                                <td class="tbl" style="color: rgb(75,75,75); text-align: center; background-color: rgb(222,222,222);">Mês</td>
                                <td class="tbl" style="color: rgb(75,75,75); text-align: center; background-color: rgb(222,222,222);">Realizadas</td>
                                <td class="tbl" style="color: rgb(75,75,75); text-align: center; background-color: rgb(222,222,222);">Compensadas</td>
                                <td class="tbl" style="color: rgb(75,75,75); text-align: center; background-color: rgb(222,222,222);">Acumuladas</td>                                
                            </tr>
                            <tr> 
                                <td class="tbl">Anterior</td>    
                                <td class="tbl"> -- </td>     
                                <td class="tbl"> -- </td>                           
                                <td style=" text-align: center; <?=$alertcor?>">
                                <?
                                    echo("<!--".$scpx."-->");
                                    ?>
                                <?=$sinal?><?=convertHoras(abs($hecpx['valor']))?>
                                </td>
                            </tr>

                        <?                       
                        
                        $armes = array_unique($arrmes);
                        $vhecp=$hecpx['valor'];
                        $vhec=0;
                        $vhece=0;
                            foreach ($armes as $key => $value) {
                                $ms = explode("/", $value);
                     

                                 $mesQuit=$ms['1']."-".$ms['0'];
                                
                                 $scp = "select  sum(e.valor) valor,t.evento, MONTH(e.dataevento) as mes 
                                 from rhevento e join rhtipoevento t on(t.idrhtipoevento=e.idrhtipoevento)
                                 where  e.idrhtipoevento in(6) 
                                 and e.status='PENDENTE' 
                                 and e.dataevento LIKE '".$mesQuit."%'
                                 and e.idpessoa=".$idpessoa."                        
                                 and e.valor!=0 group by mes";

                                 //echo($scp);
                                 $cp = d::b()->query($scp) or die("erro ao buscar horas pendentes sql=" . $scp);
                                 $hecp = mysqli_fetch_assoc($cp);

                                 $sc = "select  sum(e.valor) valor,t.evento, MONTH(e.dataevento) as mes 
                                 from rhevento e join rhtipoevento t on(t.idrhtipoevento=e.idrhtipoevento)
                                 where  e.idrhtipoevento in(6) 
                                 and e.status in ('QUITADO TRANSFERENCIA','QUITADO') 
                                 and e.dataevento LIKE '".$mesQuit."%'
                                 and e.idpessoa=".$idpessoa."                        
                                 and e.valor!=0 group by mes";
                                 $cq = d::b()->query($sc) or die("erro ao buscar horas quitadas sql=" . $sc);
                                 $hec = mysqli_fetch_assoc($cq);

                                 $sce = "select  sum(e.valor) valor,t.evento, MONTH(e.dataevento) as mes 
                                 from rhevento e join rhtipoevento t on(t.idrhtipoevento=e.idrhtipoevento)
                                 where  e.idrhtipoevento in(6) 
                                 and e.status!='INATIVO' 
                                 and e.dataevento LIKE '".$mesQuit."%'
                                 and e.idpessoa=".$idpessoa."                        
                                 and e.valor!=0 group by mes";
                                 $cqe = d::b()->query($sce) or die("erro ao buscar horas extras sql=" . $sce);
                                 $hece = mysqli_fetch_assoc($cqe);


                                    if(empty($hece['valor'])){$hece['valor']=0;}
                                    if(empty($hecp['valor'])){$hecp['valor']=0;}
                                    if(empty($hec['valor'])){$hec['valor']=0;}
                                    $vhecp=$vhecp+$hecp['valor'];
                                    $vhec= $vhec+$hec['valor'];
                                    $vhece=$vhece+$hece['valor'];


                                    $snce="";
                                    if ($hece['valor'] < 0) {
                                        $vvhece = $hece['valor'] * -1;
                                        $snce = "-";
                                    }else{
                                        $vvhece = $hece['valor'];
                                    }

                                    $sncp="";
                                    if ($hecp['valor'] < 0) {
                                        $vvhecp = $hecp['valor'] * -1;
                                        $sncp = "-";
                                    }else{
                                        $vvhecp = $hecp['valor'];
                                    }

                                    $snc="";
                                    if ($hec['valor'] < 0) {
                                        $vvhec = $hec['valor'] * -1;
                                        $snc = "-";
                                    }else{
                                        $vvhec = $hec['valor'];
                                    }
                                ?>
                                <tr>
                                    <td  class="tbl"><?= $value ?></td>
                                    <td  class="tbl"> <?= $snce.convertHoras($vvhece) ?> </td>                                    
                                    <td class="tbl" ><?= $snc.convertHoras($vvhec) ?></td>
                                    <td  class="tbl"> <?= $sncp.convertHoras($vvhecp) ?> </td>
                                </tr>
                            
                            <? } 
                            
                            $sne="";
                            if ($vhece < 0) {
                                $vhece = $vhece * -1;
                                $sne = "-";
                            }

                            $sn="";
                            if ($vhec < 0) {
                                $vhec = $vhec * -1;
                                $sn = "-";
                            }
                            
                            $snp="";
                            if ($vhecp < 0) {
                                $vhecp = $vhecp * -1;
                                $snp = "-";
                            }

                            

                            ?>
                                <tr>
                                   
                                    <td  class="tbl"style="text-align: right;"><strong>Total</strong></td>
                                    <td  class="tbl"><strong> <?= $sne.convertHoras($vhece) ?> </strong></td>                                   
                                    <td  class="tbl"><strong> <?= $sn.convertHoras($vhec) ?> </strong></td>
                                    <td  class="tbl">
                                        <a title="Horas Extras" class="pointer" onclick="javascript:janelamodal('./?_modulo=rhtipoeventofolha&idrhtipoevento=6&idpessoa=<?=$idpessoa?>')">
                                        <strong> <?= $snp.convertHoras($vhecp) ?> </strong>
                                        </a>
                                    </td>
                                </tr>
                        </table>

                                  
        <br>
        <table style="font-size: 11px; margin-top: 50px;" align="center">
        <tr><td>Assinatura:</td><td>___________________________________________</td></tr>
        </table -->      
                <?
                retirado a pedido do RH fim 
                */ 
			}
             
		}
    }elseif($tiporel=="SIMPLES"){//simples

		foreach ($arrayp as $idpessoa => $arrayfunc) {
			$totalh4=0;
			$totalh5=0;
			$totalh6=0;
			$totalh7=0;
			$totalh23=0;
?>
         <br>
        <table class="table table-striped planilha">
<?
			foreach ($arrayfunc as $nome => $arrstatus) {
?>
            <thead>
                <tr class="titulo">
                    <td class="titulo"  style="text-align:center" colspan="7">
                    <?
                    if($nome!='vazio'){ 
                        ?>
                        <a title="Editar Funcionario" class="pointer" onclick="javascript:janelamodal('./?_modulo=funcionario&_acao=u&idpessoa=<?=$idpessoa?>')">
                            <b><?=$nome?></b>
                        </a>
                   <?
                    }                    
                    ?>
                    </td>  
                </tr>
                <tr class="header"> 
                    <td class="header"  align="center" >Status</td>                                   
                    <td class="header" align="center">HORAS DIARIAS</td>
                    <td class="header" align="center">HORAS AJUSTADAS</td>
                    <td class="header" align="center">HORAS EXTRAS</td>
                    <td class="header " align="center">HORAS EXTRAS EM DINHEIRO</td>
					<td class="header " align="center">HORAS EXTRAS PARA R$</td>
                </tr>
            </thead>
            <tbody>
        
<?
				foreach ($arrstatus as $status => $arrtipoev) {
?>
				<tr class="res" style="background-color: <?=$corf?>"> 
					<td  align="center"><?=$status?></td>
					<td align="center">
<?				
					foreach ($arrtipoev as $idrhtipoev => $value) {
						if($idrhtipoev==4){
							echo($value['valor']);
						}						
					}
					reset($arrtipoev);
?>
					</td>	
					<td align="center">					
<?						
					foreach ($arrtipoev as $idrhtipoev => $value) {
						if($idrhtipoev==5){
							echo($value['valor']);
						}
					}
					reset($arrtipoev);	
?>
					</td>	
					<td align="center">					
<?							
					foreach ($arrtipoev as $idrhtipoev => $value) {
						if($idrhtipoev==6){
							  if($value['valor']<0){echo "-" ;}
								echo convertHoras(abs($value['valor']));
								//echo ($thextra);					
						}	    		
					}//foreach ($arrtipoev as $idrhtipoev => $value) {
					reset($arrtipoev);
?>
					</td>	
					<td align="center">					
<?							
					foreach ($arrtipoev as $idrhtipoev => $value) {
						if($idrhtipoev==7){
							echo($value['valor']);			
						}						
					}//foreach ($arrtipoev as $idrhtipoev => $value) {
					reset($arrtipoev);
?>
					</td>	
					<td align="center">					
<?					
					foreach ($arrtipoev as $idrhtipoev => $value) {
						if($idrhtipoev==23){
							echo ($value['valor']);
						}							
					}//foreach ($arrtipoev as $idrhtipoev => $value) {		
					reset($arrtipoev);
?>
					</td>
				</tr>
<?					
				}//foreach ($arrstatus as $status => $arrtipoev) {
			}//foreach ($arrayfunc as $nome => $arrdata) {
		}//foreach ($arrayp as $idpessoa => $arrayfunc) {
    }else if($tiporel=="EXTRA"){
        $i = 0; 
        $to = 0;
        foreach ($arrayp as $idpessoa => $arrayfunc) {
            $thextra=0;
            $thextrapen=0;
            $_idempresa=traduzid('pessoa','idpessoa','idempresa',$idpessoa);
?>
         <br>
        <table class="table  planilha">
<?
        foreach ($arrayfunc as $nome => $arrstatus) {
            ?>
            <thead>
                <tr class="titulo" >
                    <td class="titulo"  style="text-align:center" colspan="7">
                    <?if($nome!='vazio'){
                        $sqld = "SELECT sg.setor from pessoaobjeto po join sgsetor sg on (po.idobjeto = sg.idsgsetor) where po.idpessoa =".$idpessoa;
                        $ress= d::b()->query($sqld);
                        $rsc=mysqli_fetch_assoc($ress);?>
                        <a title="Editar Funcionario" class="pointer" onclick="javascript:janelamodal('./?_modulo=funcionario&_acao=u&idpessoa=<?=$idpessoa?>')">
                            <b><?=$nome?> - <?=$rsc['setor']?></b>
                        </a>
                   <?}?>
                    </td>
                </tr>              
                <tr class="header" style="background-color: #eee;">                                    
                    <td class="header" align="center">HORAS PENDENTES</td>
                    <td class="header" align="center">HORAS EXTRAS <?=date("m/y")?></td>
                    <td class="header" align="center">TOTAL</td>

                </tr>
            </thead>
            <tbody>

                <?foreach ($arrstatus as $status => $arrtipoev) {?>
                
                    <tr class="res" id="li_<?=$i?>" style="background-color: <?=$corf?>"> 
                        <td align="center">					
                        <?foreach ($arrtipoev as $dataevento => $value) {  
                            
                                if($dataevento=="datapontopendente"){
                                    $i++;
                                    foreach($value as $data => $arraponto){
                                        $valor = $arraponto['valor'];
                                        break;
                                    }
                                    $valorfinal=$valorfinal+$valor; 
                                    if($valor<0){echo "-" ;}
                                    echo convertHoras(abs($valor));
                                    
                                }	 
                            }
                            reset($arrtipoev);
                            ?>
                        </td>	
                        
                        <td align="center">
                            <?foreach ($arrtipoev as $dataponto => $value) {
                                if($dataponto==="dataponto"){
                                    $i++;
                                    foreach($value as $data => $arraponto){
                                        $timestampdt = strtotime($data);
                                        $dia= date("d", $timestampdt);
                                        $mes= date("m", $timestampdt);
                                        $ano= date("Y", $timestampdt);
                                        $folha = new Folha($dia,$dia,$mes,$ano,$idpessoa);
                                        $calendario=$folha->getCalendario($_idempresa); 
                                        $horasexec=$folha->gethorasExec();
                                        $horasplan=$folha->getHorasPlan();
                                        $horas=$folha->gethoras();
                                        
                                        $sqlhe="select * from rhevento e
                                        where e.idrhtipoevento = 6 
                                        and e.idpessoa = ".$idpessoa." 
                                        and e.dataevento = '".$data."'
                                        and e.status = 'QUITADO TRANSFERENCIA'
                                        and  exists (select 1 from rhevento d 
                                                        where d.idobjetoori = e.idrhevento 
                                                        and d.tipoobjetoori='rhevento' 
                                                        and d.idrhtipoevento = 435 
                                                        and d.status!='INATIVO')";
                                        $rhe=d::b()->query($sqlhe);
                                        $qtdhe=mysqli_num_rows($rhe);
                                        if( $qtdhe>0){
                                            $horas['horaextra']=0;
                                            $horas['diastrab']=1;
                                        }

                                        
                                        $thextra=$thextra+$horas['horaextra'];
                                    }
                                    if($thextra<0){echo "-" ;}
                                    
                                    echo convertHoras(abs($thextra));
                                }	 
                            }
                            reset($arrtipoev);
                            $totall=$valor + $thextra?>
                        </td>
                        <td align="center">
                            <?if($totall<0){echo "-" ;}
                            echo convertHoras(abs($totall))?>
                        </td>	
                    </tr>
                    <? $totalfim=$totalfim + $totall?>
                <?}
        }
    }
        
}?>
                    
            </tbody>
        </table>
        <!-- table class="table  planilha">
            <tbody>
                <thead>
                    <tr>
                        <td align="center">⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀
                        </td>
                        <td align="center">⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀
                        </td>
                        <td align="center" style="background-color: #eee;">
                            TOTAL
                        </td>
                    </tr>
                </thead>
                    <tr class="header">
                        <td>⠀⠀</td>
                        <td>⠀⠀</td>
                        <td  align="center">
                            <?if($totalfim<0){echo "-" ;}
                            echo convertHoras(abs($totalfim))?>
                        </td>
                    </tr>
            </tbody>
        </table -->
      </html>
<?}?>

<script>
    
$('.selectpicker').selectpicker('render');
    
    
function pesquisar(){
    var dataevento_1 = $("[name=dataevento_1]").val();
    var dataevento_2 = $("[name=dataevento_2]").val();
    var idpessoa = $("[name=idpessoa]").val();
    var idsgsetor = $("[name=idsgsetor]").val();
    var str="dataevento_1="+dataevento_1+"&dataevento_2="+dataevento_2+"&idpessoa="+idpessoa+"&idsgsetor="+idsgsetor;
    CB.go(str);
}

$(document).keypress(function(e) {
  if(e.which == 13) {
    pesquisar();
  }
});

//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>