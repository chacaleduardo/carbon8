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
$idpessoab='';
$idempresa='';
$sgarea = false;
//$hidden="hidden";
$dataevento_1 	= $_GET["dataevento_1"];
$dataevento_2 	= $_GET["dataevento_2"];
$idpessoab	= ($_GET["idpessoa"] !="null")?$_GET['idpessoa']:'';
$idempresa	= ($_GET["idempresa"] !="null" && !empty($_GET["idempresa"]))?$_GET['idempresa']:cb::idempresa();
$idsgsetor = $_GET["idsgsetor"];
$idsgdepartamento = $_GET["idsgdepartamento"];
$idusuario= $_SESSION["SESSAO"]["IDPESSOA"];//$perfilpag_idpessoa;
$irregularidades = $_GET['irregularidades'];
$inativos=$_GET['inativos'];

if($inativos!='S'){
    $inativos='N';
}

$sql="select * from "._DBCARBON."._lpmodulo where modulo ='aprovaponto' and idlp in(".getModsUsr("LPS").")";
$res=d::b()->query($sql) or die("erro ao buscar supervisor sql=".$sql);
$_supervisor= mysqli_num_rows($res);
if($_supervisor<1){
   $idpessoab= $idusuario; 
   $readonlyp="disabled='disabled'";
   $hidden="hidden";
}
$sql="select * from pessoaobjeto where idpessoa =".$_SESSION['SESSAO']['IDPESSOA']." and responsavel='Y' and tipoobjeto='sgsetor'";
$res=d::b()->query($sql) or die("erro ao buscar supervisor sql=".$sql);
$_coord= mysqli_num_rows($res);

if($_coord == 0){
    $sql="select * from pessoaobjeto where idpessoa =".$_SESSION['SESSAO']['IDPESSOA']." and tipoobjeto='sgarea'";
    $res=d::b()->query($sql) or die("erro ao buscar supervisor sql=".$sql);
    $_coord= mysqli_num_rows($res);
    $sgarea = true;

}

if($_coord>0){
$idpessoab=($_GET["idpessoa"] !="null")?$_GET['idpessoa']:'';
if($_GET["idsgdepartamento"] !='null'){
    $idpessoab = '';
}
$readonlyp="";
$hidden="hidden";
$readonlydep='disabled=disabled';
}
$sqld="select * from pessoaobjeto where idpessoa =".$_SESSION['SESSAO']['IDPESSOA']." and responsavel='Y' and tipoobjeto='sgdepartamento'";
$resd=d::b()->query($sqld) or die("erro ao buscar supervisor departamento sql=".$sqld);
$_l_depart= mysqli_num_rows($resd);

$s="SELECT * from "._DBCARBON."._lpmodulo where modulo ='visualizaponto' and idlp in(".getModsUsr("LPS").")";
$rs= d::b()->query($s)  or die("Erro buscar se e do RH sql:".$s);
$edoRH=mysqli_num_rows($rs);
if($edoRH>0){
    $idpessoab = ($_GET["idpessoa"] !="null")?$_GET['idpessoa']:'';
    if($_GET["idsgdepartamento"] !="null"){
        $idpessoab = '';
    }
    $readonlyp="";
    $hidden="";
}

if($inativos=='N'){
    $funcativo=" and p.status='ATIVO' ";
    $funcativop=" and pessoas.status='ATIVO' ";
}else{
    $funcativo=" ";
    $funcativop=" ";
}

if ($idsgdepartamento != "null" and ($edoRH>0 or $_coord>0 or $_supervisor>0)) {
        $sqldep = "SELECT group_concat(idpessoa) as idpessoa from (SELECT  concat(group_concat(p.idpessoa),',',pp.idpessoa)as idpessoa from pessoaobjeto po
        join sgdepartamento sd on (sd.idsgdepartamento = po.idobjeto)
        join sgsetor sg on(sg.idsgdepartamento = sd.idsgdepartamento and sg.status = 'ATIVO')
        join pessoaobjeto ppo on(ppo.idobjeto = sg.idsgsetor and ppo.tipoobjeto='sgsetor')
        join pessoa p on (p.idpessoa = ppo.idpessoa ".$$funcativo.")
        join pessoa pp on (pp.idpessoa = po.idpessoa)
        where po.idobjeto in (".$idsgdepartamento.") and po.tipoobjeto='sgdepartamento'
        UNION 
        SELECT  concat(group_concat(p.idpessoa),',',pp.idpessoa)as idpessoa from pessoaobjeto po
        join sgdepartamento sd on (sd.idsgdepartamento = po.idobjeto)
        join sgsetor sg on(sg.idsgdepartamento = sd.idsgdepartamento and sg.status = 'ATIVO')
        join pessoaobjeto ppo on(ppo.idobjeto = sg.idsgsetor and ppo.tipoobjeto='sgsetor')
        join pessoa p on (p.idpessoa = ppo.idpessoa ".$$funcativo.")
        join pessoa pp on (pp.idpessoa = po.idpessoa)
        where ppo.idobjeto in (".$idsgdepartamento.") and po.tipoobjeto='sgdepartamento'
        UNION
        SELECT  group_concat(po.idpessoa)as idpessoa
        from pessoaobjeto po 
        join sgsetor sg on(po.idobjeto = sg.idsgsetor) 
        JOIN sgdepartamento sd ON (sg.idsgdepartamento = sd.idsgdepartamento AND sg.status = 'ATIVO') 
        where po.idobjeto in (sg.idsgsetor) and po.tipoobjeto='sgsetor' AND sg.idsgdepartamento in(".$idsgdepartamento.")) as u where idpessoa ";
    $resdep =  d::b()->query($sqldep);
    $rowdep = mysqli_fetch_assoc($resdep);
    $idpessoab	.= $rowdep['idpessoa'];
}
    if (!empty($dataevento_1) or !empty($dataevento_2)){
        $data1 = validadate($dataevento_1);
        $data2 = validadate($dataevento_2);

        if ($data1 and $data2){
            $strin .= " and (STR_TO_DATE(data,'%Y-%m-%d')  BETWEEN '" . $data1 ."' and '" .$data2 ."')";
        }else{
            die ("Datas n&atilde;o V&aacute;lidas!");
        }
    }
$strjoin='';
    if(!empty($idpessoab) and $idpessoab!='null'){
        $strin .= " and p.idpessoa in (".$idpessoab.") ";
        $strinp .= " and p.idpessoa in (".$idpessoab.") ";
    }elseif(!empty($idsgsetor) and $idsgsetor!='null' and $idsgsetor!='undefined'){
        $strin .=" and s.idpessoa=p.idpessoa and s.idobjeto in(".$idsgsetor.") and s.tipoobjeto = 'sgsetor'";
        $strinp .=" and s.idpessoa=p.idpessoa and s.idobjeto in(".$idsgsetor.") and s.tipoobjeto = 'sgsetor'";
        $strjoin=",pessoaobjeto s";
    }

    if(!empty($_GET['statusevento'])){
        $strin.=" and p.statusevento='".$_GET['statusevento']."' ";
    }
    if(!empty($_GET['entsaida'])){
        $strin.=" and p.entsaida='".$_GET['entsaida']."' ";
    }
    
/*
 * colocar condição para executar select
 */
if($_GET and !empty($strin) and !empty($dataevento_1) and !empty($dataevento_2)){
    
    
    $idusuario= $_SESSION["SESSAO"]["IDPESSOA"];//$perfilpag_idpessoa;

    // $sql="select * from "._DBCARBON."._lpmodulo where modulo ='aprovaponto' and idlp in(".getModsUsr("LPS").")";
    // $res=d::b()->query($sql) or die("erro ao buscar supervisor sql=".$sql);
    // $_supervisor= mysqli_num_rows($res);
    if(empty($idpessoab)){
       $strin.=" and p.idpessoa ='".$idusuario."' "; 
       $strinp.=" and p.idpessoa ='".$idusuario."' "; 
    }

   
    
                $arrayp=array();
            
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
                   //Inserção do Idempresa, pois na hora de mostrar os dados estava trazendo de todas as empresas (Lidiane - 24-04-2020)
                  /* $s1="select f.idpessoa as idpessoa,f.nome 
                        from vw_ponto p,pessoa f ".$strjoin."
                        where f.idpessoa = p.idpessoa
                        and f.status='ATIVO'
                        ".$strin."
						".getidempresa('p.idempresa','pessoa')."
                         order by f.nome"; */

/*
                         $s1 = "select f.idpessoa, f.nome 
                                from pessoa f".$strjoin."
                                where f.status = 'ATIVO'
                          -- and exists (select 1 from ponto p where f.idpessoa = p.idpessoa ".$strin." ) 
                          ".getidempresa('f.idempresa','pessoa')." 
                          order by f.nome";
                          */

                          
                         $s1 = "select p.idpessoa, p.nome 
                         from pessoa p ".$strjoin."
                         where -- p.status = 'ATIVO' and
                          p.idtipopessoa= 1
                         ".$strinp."
                        and p.idempresa in (".$idempresa.")
                        order by p.nome";
                    echo "<!-- ".$s1." -->";	//echo $_sqlresultado;
                   // die($s1);
                    $re1= d::b()->query($s1) or die("erro ao buscar os funcionarios dos pontos  sql=".$s1);
      
                        while($r=mysqli_fetch_assoc($re1)){
                           // $arrayp[$r['idpessoa']]['semana'][$rw['diabusca']]=$r['semana'];  
                            $arrayp[$r['idpessoa']][$r['nome']][$rw['diabusca']][1]['semana']=$rw['semana'];                         
                             
                        }                    

                 }
             } 
             
             //print_r($arrayp);die;
			 //Inserção do Idempresa, pois na hora de mostrar os dados estava trazendo de todas as empresas (Lidiane - 24-04-2020)
            $data2 = $data2.' 23:59:59';
            $s1="select 
                        p.idpessoa,nome,dataponto,idrhevento,idrhtipoevento,hora,semana,statusevento,entsaida,obs
                    from vw_ponto p ".$strjoin."
                    where data between '".$data1."' and '".$data2."'
                     and p.statusevento!='INATIVO'
					and p.idempresa in (".$idempresa.")
                    ".$strin."
                    group by p.idrhevento
                      order by nome,hora";
             // echo $s1;	//echo $_sqlresultado;
             echo "<!-- ".$s1." -->";	//echo $_sqlresultado;
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
                
      
    
}//if($_GET and !empty($clausulad)){
    $s="SELECT * from "._DBCARBON."._lpmodulo where modulo ='visualizaponto' and idlp in(".getModsUsr("LPS").")";
    
    $rs= d::b()->query($s)  or die("Erro buscar se e do RH sql:".$s);
    $edoRH=mysqli_num_rows($rs);

?>

<style>
i.tip:hover {
    cursor: hand;
    position: relative
}
i.tip span {
    display: none
}
i.tip:hover span {
    border: #c0c0c0 1px dotted;
    padding: 5px 20px 5px 5px;
    display: block;
    z-index: 100;
    background: #f0f0f0 no-repeat 100% 5%;
    left: 0px;
    margin: 10px;
    width: 300px;
    position: absolute;
    top: 10px;
    text-decoration: none
}
i.tip2:hover {
    cursor: hand;
    position: relative
}
i.tip2 span {
    display: none
}
i.tip2:hover span {
    border: #c0c0c0 1px dotted;
    padding: 5px 20px 5px 5px;
    display: block;
    z-index: 100;
    background: #f0f0f0 no-repeat 100% 5%;
    left: 0px;
    margin: 10px;
    width: 500px;
    position: absolute;
    top: 10px;
    text-decoration: none
}
</style>
<div class="row">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading" >Filtros para Listagem </div>
        <div class="panel-body" >
	<table>
	    <tr>
            <td class="rotulo">Período</td>
            <td><font class="9graybold">entre</font></td>
            <td><input name="dataevento_1" vpar="" id="dataevento_1" class="calendario" size="10" style="width: 90px;" value="<?=$dataevento_1?>" autocomplete="off"></td>
            <td><font class="9graybold">&nbsp;e&nbsp;</font></td>
            <td><input name="dataevento_2" vpar="" id="dataevento_2"class="calendario" size="10" style="width: 90px;" value="<?=$dataevento_2?>" autocomplete="off"></td>
	    </tr>
        <? if($edoRH>0) {?>
            <tr>
                <td>
                    Colaboradores Inativos:
                </td>
                <td>
                    <select <?=$readonlyp?> <?=$readonlydep?> name="inativos" id="inativos" onchange="pesquisar()">
                        <option value="S" <? if($inativos == 'S'){?> selected <?}?>>Sim</option>
                        <option value="N" <? if($inativos == 'N'){?> selected <?}?>>Não</option>
                    </select>
                </td>
            </tr>	
            <tr>
                <td align="right">Empresa</td>
                <td colspan="10">
                    <select <?=$readonlyp?> name="idempresa" id="pickerempresa" class="selectpicker valoresselect"  data-actions-box="true" multiple="multiple" data-live-search="true">
                    <?$arrempresa= explode(',',$idempresa);  

                    
                        $sqlm="SELECT idempresa, nomefantasia from empresa where filial='N' and status='ATIVO' order by nomefantasia";
                        $resm =  d::b()->query($sqlm)  or die("Erro buscar funcionarios 1 sql:".$sqlm);
                        while ($rowm = mysqli_fetch_assoc($resm)) {
                            if (in_array($rowm['idempresa'],$arrempresa)){
                                    $selected= 'selected';
                            }else{
                                    $selected= '';
                            }

                            echo '<option data-tokens="'.retira_acentos($rowm['nomefantasia']).'" value="'.$rowm['idempresa'].'" '.$selected.' >'.$rowm['nomefantasia'].'</option>'; 
                        }?>
                        </select> 
                    </td>
            </tr>
        <?}?>
	    <tr>
            <td align="right">Colaborador:</td> 
            <td colspan="10">
        
                    <select <?=$readonlyp?> name="idpessoa"  id="picker"  class="selectpicker valoresselect"  data-actions-box="true" multiple="multiple" data-live-search="true">
    <?                    
                        $arrvalor= explode(',',$idpessoab);  
                    $podejustificar='N';
                    if($edoRH>0) {
                        $sqlm="SELECT 
                        idpessoa,concat(nomecurto,if(status='INATIVO',' (INATIVO)','')) as nomecurto
                        from pessoa p
                        where idtipopessoa=1 
                        ".$funcativo."
                        and idempresa in (".$idempresa.")
                        and contrato='CLT' order by nomecurto";
                        $resm =  d::b()->query($sqlm)  or die("Erro buscar funcionarios 1 sql:".$sqlm);
                        $podejustificar='Y';
                    }else{
                        $sqlm="SELECT pessoas.idpessoa, pessoas.nomecurto
                        FROM (
                            SELECT DISTINCT(p.idpessoa), concat(p.nomecurto,if(p.status='INATIVO',' (INATIVO)','')) as nomecurto, p.idtipopessoa, p.status, p.contrato
                            FROM objetovinculo ov
                            JOIN pessoaobjeto podep ON(podep.idobjeto = ov.idobjeto AND podep.tipoobjeto = 'sgdepartamento' AND ov.tipoobjeto = 'sgdepartamento')
                            JOIN pessoaobjeto po ON(po.idobjeto = ov.idobjetovinc AND po.tipoobjeto = 'sgsetor')
                            JOIN pessoa p ON(p.idpessoa = po.idpessoa OR p.idpessoa = podep.idpessoa)
                            WHERE podep.idpessoa = $idusuario
                            AND podep.responsavel = 'Y'
                            UNION
                            SELECT DISTINCT(p.idpessoa), concat(p.nomecurto,if(p.status='INATIVO',' (INATIVO)','')) as nomecurto, p.idtipopessoa, p.status, p.contrato
                            FROM sgsetor s
                            JOIN pessoaobjeto poset on(poset.idobjeto = s.idsgsetor and poset.tipoobjeto = 'sgsetor' AND poset.responsavel = 'Y')
                            JOIN pessoaobjeto po on(po.idobjeto = s.idsgsetor and po.tipoobjeto = 'sgsetor')
                            JOIN pessoa p on(p.idpessoa = po.idpessoa or poset.idpessoa = p.idpessoa)
                            WHERE poset.responsavel = 'Y'
                            AND poset.idpessoa = $idusuario
                            UNION 
                            SELECT DISTINCT(p.idpessoa),concat(p.nomecurto,if(p.status='INATIVO',' (INATIVO)','')) as nomecurto, p.idtipopessoa, p.status, p.contrato
                            FROM pessoa p
                            WHERE p.idpessoa = $idusuario
                        ) pessoas
                        WHERE pessoas.idtipopessoa = 1
                        ".$funcativop."
                        AND pessoas.contrato='CLT'
                        ORDER BY pessoas.nomecurto;";                    
                        $resm =  d::b()->query($sqlm)  or die("Erro buscar funcionarios 2 sql:".$sqlm);
                        if($edoRH>0 or $_coord>0 or $_supervisor>0 or $_l_depart >0){
                            $podejustificar='Y';
                        }else{
                            $podejustificar='N';
                        }
                        
                    }

                    $qtdrep=mysqli_num_rows($resm);
                    
                    if($qtdrep<1){
                        $sqlm="select idpessoa,nomecurto from pessoa where idtipopessoa=1 and  status = 'ATIVO' and idpessoa=".$idusuario." order by nomecurto";
                        $resm =  d::b()->query($sqlm)  or die("Erro buscar funcionarios 3 sql:".$sqlm);
                    }   
                    
                        while ($rowm = mysqli_fetch_assoc($resm)) {
                            if (in_array($rowm['idpessoa'],$arrvalor)){
                                    $selected= 'selected';
                            }else{
                                    $selected= '';
                            }

                            echo '<option data-tokens="'.retira_acentos($rowm['nomecurto']).'" value="'.$rowm['idpessoa'].'" '.$selected.' >'.$rowm['nomecurto'].'</option>'; 
                        }		
            ?>
                    </select> 
            </td>
        </tr>
        <tr hidden>
        <td align="right">Setor:</td>
      <!--  <td colspan="10">           
                            <select <?=$readonlyp?> name="idsgsetor"  id="idsgsetor"  class="selectpicker valoresselect" multiple="multiple" data-live-search="true" >
<?     /*               
                    $arrvalor= explode(',',$idsgsetor);  

                    if($edoRH>0) {
                        $sqlm="select idsgsetor,setor,idsgdepartamento from sgsetor where status='ATIVO' and idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]." order by  setor";
                        $resm =  d::b()->query($sqlm)  or die("Erro buscar setor Drop 1  sql:".$sqlm);
                    }else{
                        $sqlm="SELECT sg.idsgsetor,sg.setor,sg.idsgdepartamento
                        FROM pessoaobjeto d
                        join objetovinculo ov on( ov.tipoobjetovinc = 'sgsetor' and d.idobjeto=ov.idobjeto)
                       join sgsetor sg on(sg.idsgsetor = ov.idobjetovinc and sg.status='ATIVO')
                        WHERE d.idpessoa=".$idusuario."
                        and d.tipoobjeto = 'sgdepartamento'
                        UNION
                        SELECT  sg.idsgsetor,sg.setor,sg.idsgdepartamento
                                FROM pessoaobjeto d
                                join sgsetor sg on(sg.idsgsetor = d.idobjeto and sg.status='ATIVO')
                                WHERE d.idpessoa=".$idusuario."
                            group by sg.idsgsetor order by setor";                    
                        $resm =  d::b()->query($sqlm)  or die("Erro buscar setor Drop 2 sql:".$sqlm);
                        $qtdrep=mysqli_num_rows($resm);
                    }if($qtdrep<1){
                        $sqlm="select idsgsetor,setor,idsgdepartamento from sgsetor where status='ATIVO' and idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]." order by  setor";
                        $resm =  d::b()->query($sqlm)  or die("Erro buscar setor Drop 3 sql:".$sqlm);
                    }
                   
                   
                   
                   
                   
                    while ($rowm = mysqli_fetch_assoc($resm)) {
                        if (in_array($rowm['idsgsetor'],$arrvalor)){
                                $selected= 'selected';
                        }else{
                                $selected= '';
                        }

                        echo '<option data-tokens="'.retira_acentos($rowm['setor']).'" idsgdepartamento="'.$rowm['idsgdepartamento'].'" value="'.$rowm['idsgsetor'].'" '.$selected.' >'.$rowm['setor'].'</option>'; 
                    }		
		*/?>
                </select> 
        </td>-->
	    </tr>	
    <tr>
    <td align="right">Departamento:</td>
    <td colspan="10">
        <?
        if (empty($readonlyp)) {
            $readonlydep = '';
        }
        ?>  
                        <select <?=$readonlyp?> <?=$readonlydep?> name="idsgdepartamento"  id="idsgdepartamento"  class="selectpicker valoresselect"  data-actions-box="true" multiple="multiple" data-live-search="true" >
<?                    
                $arrvalor= explode(',',$idsgdepartamento);  

                if($edoRH>0) {
                    $sqlm1="SELECT idsgdepartamento,departamento from sgdepartamento where status='ATIVO' and idempresa in (".$idempresa.") order by  departamento";
                    $resm1 =  d::b()->query($sqlm1)  or die("Erro buscar departamento Drop 1  sql:".$sqlm1);
                }else{
                    if($sgarea){
                        $sqlm1 = "SELECT  d.idsgdepartamento,d.departamento
                        from sgarea a 
                            JOIN sgdepartamento d on (d.idsgarea = a.idsgarea)
                            join pessoaobjeto po on(po.idpessoa =".$_SESSION['SESSAO']['IDPESSOA']." and po.idobjeto=a.idsgarea and po.tipoobjeto='sgarea')
                        where d.status='ATIVO' and d.idempresa in (".$idempresa.") order by  departamento";
                        $resm1 =  d::b()->query($sqlm1)  or die("Erro buscar departamento Drop 2 sql:".$sqlm1);
                        $qtdrep=mysqli_num_rows($resm1);
                    }else{
                        $sqlm1="SELECT  d.idsgdepartamento,d.departamento
                        from sgdepartamento d
                            join pessoaobjeto po on(po.idpessoa =".$_SESSION['SESSAO']['IDPESSOA']." and po.idobjeto=d.idsgdepartamento and po.tipoobjeto='sgdepartamento')
                        where d.status='ATIVO' and d.idempresa in (".$idempresa.") order by  departamento";                    
                        $resm1 =  d::b()->query($sqlm1)  or die("Erro buscar departamento Drop 2 sql:".$sqlm1);
                        $qtdrep=mysqli_num_rows($resm1);
                    }
                }
               // if($qtdrep<1){
               //     $sqlm1="SELECT departamento,idsgdepartamento from sgdepartamento where status='ATIVO' ".getidempresa('idempresa','sgdepartamento')." order by  departamento";
               //     $resm1 =  d::b()->query($sqlm1)  or die("Erro buscar departamento Drop 3 sql:".$sqlm1);
               // }

                while ($rowm1 = mysqli_fetch_assoc($resm1)) {
                    if (in_array($rowm1['idsgdepartamento'],$arrvalor)){
                            $selected= 'selected';
                    }else{
                            $selected= '';
                    }

                    echo '<option data-tokens="'.retira_acentos($rowm1['departamento']).'" idsgdepartamento="'.$rowm1['idsgdepartamento'].'" value="'.$rowm1['idsgdepartamento'].'" '.$selected.' >'.$rowm1['departamento'].'</option>'; 
                }		
    ?>
            </select> 
    </td>
    </tr>
    <tr>
         <td>
            Irregularidades:
        </td>
        <td>
            <select <?=$readonlyp?> <?=$readonlydep?> name="irregularidades" id="irregularidades">
                <option value=""></option>
                <option value="SIM" <? if($irregularidades == 'SIM'){?> selected <?}?>>Sim</option>
                <option value="NAO" <? if($irregularidades == 'NAO'){?> selected <?}?>>Não</option>
            </select>
        </td>
    </tr>	
    
	</table>	
	<div class="row"> 
	    <div class="col-md-7">
            </div>
	    <div class="col-md-1">
		<button id="cbPesquisar" class="btn btn-default btn-primary" onclick="pesquisar(this)">
		    <span class="fa fa-search"></span>
		</button> 
	    </div>	
		<div class="col-md-1">
			<button title="Banco de Horas Simples" class="btn btn-default btn-primary" onclick="bancohdes()">
				<span class="fa fa-bars"></span>
			</button>
			<!--
			<a title="Banco Horas Simpes" class="fa fa-file  pointer cinza hoverazul" onclick="bancohdes()" target="_blank"></a>
			-->
		</div>
		<div class="col-md-1">
			<button  title="Banco de Horas Detalhado" class="btn btn-default btn-primary" onclick="bancohdet()">
				<span class="fa fa-bars"></span>
			</button>
			<!--
			<a title="Banco Horas Detalhado" class="fa fa-file  cinza hoverazul" onclick="bancohdet()" target="_blank"></a>
			-->
		</div>
        <div class="col-md-1">
			<button  title="Banco de Horas extras" class="btn btn-default btn-primary" onclick="bancohext()">
				<span class="fa fa-bars"></span>
			</button>
		</div>
	</div>
        </div>
    </div>
    </div>
</div>

<?
if($_GET and !empty($arrayp)){
?>
<div class="row">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading">Relat&oacute;rio de pontos</div>
        <div class="panel-body">
        <table style="width: 100%;">
                <tr>
                    <td>
                
<?
    foreach ($arrayp as $idpessoa => $arrayfunc) {
        $totalh=0;
        $totalhn=0;
        $totalp=0;
        $thextra=0;
        $thextradin=0;
        $dinhoraextra=0;
        $tdiastrab=0;
        $entsaida='E';
?>
            <br>
        <table class="table table-striped planilha">
<?
        foreach ($arrayfunc as $nome => $arrdata) {
?>
            <thead>
                <tr >
                    <td   style="text-align:left" colspan="10">
                    <?
                    if($nome!='vazio'){ 
                        ?>
                        <a title="Editar Funcionario" class="pointer" onclick="javascript:janelamodal('./?_modulo=funcionario&_acao=u&idpessoa=<?=$idpessoa?>')">
                            <b><?=$nome?></b>
                        </a>
                       
                        <i title="" class="fa btn-sm fa-info-circle preto pointer hoverazul tip" onclick="">
                                <span>
                           

                        <?
                        //echo('<b>'.$nome.'</b>');
                        $sqlh="select idpessoahorario, 
                        replace(left(horaini,5),':','h') as horaini,
                        replace(left(horafim,5),':','h') as horafim,
                        case when left(horaini,5) > '18:00' then 'NOTURNO' else 'DIURNO' end as horario,
                        case
                            when periodo = 'Mon' then 1
                            when periodo = 'Tue' then 2
                            when periodo = 'Wed' then 3
                            when periodo = 'Thu' then 4
                            when periodo = 'Fri' then 5
                            when periodo = 'Sat' then 6
                            when periodo = 'Sun' then 7
                            else 8 end as ordem,
                        case
                            when periodo = 'Mon' then 'Seg:'
                            when periodo = 'Tue' then 'Ter:'
                            when periodo = 'Wed' then 'Qua:'
                            when periodo = 'Thu' then 'Qui:'
                            when periodo = 'Fri' then 'Sex:'
                            when periodo = 'Sat' then 'Sáb:'
                            when periodo = 'Sun' then 'Dom:'
                        else 'Não Idenf' end as diasemana  
                        from pessoahorario where idpessoa=".$idpessoa." order by ordem,horaini";
                        $resh=d::b()->query($sqlh) or die("Erro ao buscar horarios do funcionário : " . mysqli_error(d::b()) . "<p>SQL:".$sqlh);
                         $virgula="&nbsp- ";
                         $fecha="";
                        while($rowh = mysqli_fetch_assoc($resh)){
                          
                            if($rowh['ordem']!=$ordem){
                                echo($fecha);
                                ?>
                                <ul style="list-style-type: none;">
                                     <li style="color: black;">
                               <? 
                                echo("<b>".$rowh['diasemana']."</b>");
                                $ordem=$rowh['ordem'];
                            }else{
                                echo($virgula);
                            }
                           
                            echo("&nbsp".$rowh["horaini"]." - ".$rowh["horafim"]."");
                            $HORARIO=$rowh['horario'];
                            $fecha="</li></ul>";
                           // $virgula=" ";
                           ?>
                               
                           <?
                        }
                        echo($fecha);
                    }
                    
                    ?>
                            
                        </span>
                        &nbsp;&nbsp;
                        <i title="Horas Extras" class="fa btn-sm fa-wpforms azul pointer hoverazul" onclick="javascript:janelamodal('./?_modulo=rhtipoeventofolha&idrhtipoevento=6&idpessoa=<?=$idpessoa?>')"></i>
                    </td>  
                </tr>
                <tr class="header"> 
                    <td class="header"  align="center" >Dia </td>
                    <td class="header" style="text-align:left">Pontos</td>
                    <td class="header" align="center">Evento</td>                    
                    <td class="header" align="center">Horas</td>
                    <td class="header" align="center"></td>
                    <td class="header" align="center">Horas Ajustadas</td>
                    <td class="header" align="center">Horas Extras</td>
                    <td class="header <?=$hidden?>" align="center">Horas Extras - R$</td>
                    <td class="header <?=$hidden?>" align="center">Horas Noturnas</td>
                    <td class="header  align="center">Dias Trabalhados</td>
                </tr>
            </thead>
            <tbody>
 <?                
            foreach ($arrdata as $data => $arraponto) {

                    $_idempresa=traduzid('pessoa','idpessoa','idempresa',$idpessoa);

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
                    $sf="select obs from feriado where status='ATIVO' and idempresa in (8,".$_idempresa.") and dataferiado ='".$data."'";
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
                   

                    $calendario=$folha->getCalendario($_idempresa); 

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
                  

                    $blink="";
                    $corpotr="";
                    $horamaior=$horas['horaajustada']+0.18;
                    $horamenor=$horas['horaajustada']-0.18;

                    $sj="select idrhjustificativa,justificativa,criadopor,dmahms(criadoem) as criadoem
                    from rhjustificativa where idpessoa=".$idpessoa." and dataevento='".$data."' order by criadoem desc";
                    $rej=d::b()->query($sj);
                    $qtdjustificativa=mysqli_num_rows($rej);
                    if($qtdjustificativa>0){
                        $classico="fa btn-sm fa-info-circle preto pointer hoverazul tip2";
                        $title="";
                    }else{
                        $classico="fa fa-circle vermelho fa-1x hoververmelho blink";
                        $title="Inconsistente";
                    }
                  
                    $stremissao=dma($data);
                    $randomicoid=rand(1111111,9999999);
                    $usuario=strtoupper($_SESSION["SESSAO"]["USUARIO"]);
                    $linhab=0;
                    if($horasexec['hora']<$horamenor or $horasexec['hora']>$horamaior){
                        $blink="<i id=".$randomicoid." style='align-items: center; vertical-align: top;' class='".$classico."' title='".$title."' usuario='".$usuario."' dataevento='".$stremissao."' onclick='gerajustificativa(this,".$idpessoa.",".$randomicoid.",)'><span id='span".$randomicoid."'>";
                        while($roj=mysqli_fetch_assoc($rej)){
                            if($linhab==0){
                                $blink.="<ul style='list-style-type: none;'>
                                <li style='color: black;'> ".ucwords(strtolower($roj['justificativa']))." <br> <b>Por:</b> ".ucwords($roj['criadopor'])." - <b>Em:</b> ".$roj['criadoem']."</li></ul>";
                             
                            }
                              $corpotr.="<tr><td><b>Justificativa:</b></td>
                            <td> ".ucwords(strtolower($roj['justificativa']))." </td> <td><b>Por:</b> ".ucwords($roj['criadopor'])."</td><td> <b>Em:</b> ".$roj['criadoem']."</td></tr>";
                            $linhab++;
                        }
                       
                     
                        $blink.="</span></i>";
                        $inconsistencia = TRUE;
                        if($podejustificar=='N'){
                            $blink="";
                        }
                    } else {
                        $inconsistencia = FALSE;
                    }
                    
                if(($inconsistencia == TRUE && $_GET['irregularidades'] == 'SIM') || (empty($_GET['irregularidades']) || $_GET['irregularidades'] == 'NAO'))
                {
                    ?>  
                    <tr class="res " style="background-color: <?=$corf?>">               
                        <td ><?=dma($data)?> - <?=$arraponto[1]['semana']?> <font color="red"> <?=$wf['obs']?></font>
                        <div id="justificativa<?=$randomicoid?>" class='hide'>
                            <div class="col-md-12">            
                                    <table id="table<?=$randomicoid?>">
                                        <tr> 
                                            <td>Justificativa:</td> 
                                            <td>
                                            <input name="#name_campo" value="#valor_campo" class="size7" type="hidden" >
                                            <input name="#name_data" value="#data_campo" class="size7" type="hidden" >
                                                <select name="#name_justificativa" onchange="alteraoutros(this)"  class="size50">
                                                    <?fillselect("select '', '' union select 'ESQUECIMENTO','Esquecimento' 
                                                    union select 'BATIDA DE HORÁRIO INCORRETO','Batida de horário incorreto' 
                                                    union select 'BATIDA EM DUPLICIDADE','Batida em duplicidade' 
                                                    union select 'HORA EXTRA','Hora Extra' 
                                                    union select 'OUTROS','Outros' ");?>
                                                </select>											
                                            </td> 
                                        </tr>
                                        <?=$corpotr?>
                                    </table>
                            </div>
                        </div>
                        </td>
                        <td class="nowrap">
                            <?                  
                            $horadia=0;
                            $batidas=0;
                            foreach ($arraponto as $idrhevento => $value) {
                                $batidas=$batidas+1;
                                if($idrhevento>1){
                                    $status= traduzid('rhevento', 'idrhevento', 'status', $idrhevento);

                                    if($value['entsaida']=='E'){
                                        // $cor="#c2f5c1";
                                        $cbt="btn-success";
                                        $entsaida='S';
                                    }else{
                                        // $cor="#dfdfe8"; 
                                        $cbt="btn-primary ";
                                        $entsaida='E';
                                    }                        
                                    
                                    $evento= traduzid('rhtipoevento', 'idrhtipoevento', 'evento', $value['idrhtipoevento']);
                                
                                    $cor="";
                                    /*
                                    if($status!="ATIVO"){
                                
                                        //so aprova se for supervisor
                                        if($_supervisor<1){
                                            $_fn="alterast";
                                        }else{
                                            $_fn="alterabt";
                                        }
                                        ?>
                                        <button title="<?=$status?>" entsaida="<?=$value['entsaida']?>"  type="button" class="btn btn-danger btn-xs" onclick="<?=$_fn?>(this,<?=$idrhevento?>)">                           
                                            <?=$value['entsaida']?>
                                        </button>
                                        <?                                
                                    }else{
                                
                                        */
                                        ?>    
                                        <button <?=$readonlyp?> title="<?=$status?>" entsaida="<?=$value['entsaida']?>"  type="button" class="btn <?=$cbt?> btn-xs" onclick="alterast(this,<?=$idrhevento?>)">                           
                                            <?=$value['entsaida']?>
                                        </button>
                                        <?                                
                                        //}
                                
                                        //$value['idrhevento'];
                                        //$value['nome'];
                                        //$value['semana'];
                                        ?>  
                                        : 
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
                            while($roe=mysqli_fetch_assoc($re))
                            {
                                if($roe['eventocurto']){
                                    $title=$roe['evento'];
                                    $tevento=$roe['eventocurto'];
                                }else{
                                    $title=$roe['evento'];
                                    $tevento=$roe['evento'];
                                }
                                
                                if($roe['tipo']=='C'){
                                    ?>
                                    <button <?=$readonlyp?> title="<?=$title?>"   type="button" class="btn btn-info btn-xs" >                           
                                            <?=$tevento?>
                                        &nbsp;&nbsp;&nbsp;
                                        <span >
                                            <i  title="<?=$title?>" class="pointer" onclick="javascript:janelamodal('./?_modulo=rhevento&_acao=u&idrhevento=<?=$roe['idrhevento']?>')"> +  <?=$roe['valor']?></i>
                                        </span>
                                    </button>  
                                    &nbsp;&nbsp;&nbsp;
                                    <?                           
                                }else{
                                    ?>
                                    <button <?=$readonlyp?> title="<?=$title?>"  type="button" class="btn btn-info btn-xs" >                           
                                            <?=$tevento?>
                                        &nbsp;&nbsp;&nbsp;
                                        <span >
                                            <i title="<?=$title?>" class="pointer" onclick="javascript:janelamodal('./?_modulo=rhevento&_acao=u&idrhevento=<?=$roe['idrhevento']?>')"> -  <?=$roe['valor']?></i>
                                        </span>
                                    </button>
                                    &nbsp;&nbsp;&nbsp;                    
                                <?   
                                }
                            }//while($roe=mysqli_fetch_assoc($re)){
                            ?>
                            
                            <?
                            //se for do RH lançar os pontos na tela
                            if($edoRH>0) {
                                for($b=$batidas; $b < 5; ++$b) {
                                        
                                    ?>
                                    <input title="Ponto" id="ponto<?=$idpessoa?>_<?=$b?>" class="size8" name="ponto<?=$idpessoa?>_<?=$b?>" type="time" value="" onblur="novoponto(<?=$idpessoa?>,'<?=dma($data)?>','<?=$entsaida?>',this)">
                                    <?
                                    if($entsaida=='S'){
                                        $entsaida='E';
                                    }else{
                                        $entsaida='S';
                                    }
                                }
                            }
                            ?>
                        </td>
                        <td align="center">
                            <?
                            if ($edoRH>0) {?>
                            <a  class="fa fa-plus-circle fa-x verde btn-lg pointer" onclick="repeteev(<?=$idpessoa?>,'<?=dma($data)?>')" title="Novo Evento"></a>
                        <?}?>      
                        </td>                   
                        <td align="center" class="nowrap"><?=convertHoras($horasexec['hora'])?></td>
                        <td align="center" ><?=$blink?></td>
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
                        <td align="center" class="nowrap <?=$hidden?>"><?if($horas['horaextradinheiro']>0){ echo convertHoras($horas['horaextradinheiro']);?> - <?echo $horas['dinheirohoraextra'];}?></td>
                        <td align="center"><?=convertHoras($horasexec['horanot'])?></td>
                        <td align="center"><?=$horas['diastrab']?></td>
                    </tr>
                    <?   
                }
            }
        }
?>
                <tr>
                    <td colspan="3">Soma:</td>
                    <td align="center"><?=convertHoras($totalh)?></td>
                    <td></td>
                    <td align="center" ><?=convertHoras($totalp)?></td>
                    <td align="center" ><?
                    if($thextra<0){echo "-" ;}
                    echo convertHoras(abs($thextra));
                    //echo ($thextra);
                    ?>
                    </td>
                    <td  align="center" class="nowrap <?=$hidden?>"><?if($thextradin>0){ echo convertHoras($thextradin)?> - <?echo $dinhoraextra;}?></td>
                    <td  align="center"><?=convertHoras($totalhn)?></td>
                    <td     align="center"><?=$tdiastrab?></td>
                </tr>
                </tbody>
          </table>       
<?                
    }
?>                
                    </td>
                </tr>
                <tr>
                    
                    <td></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
        </div>
    </div>
    </div>
</div>
<?
}//if($_GET and $ires >0){
elseif(empty($dataevento_1) or !empty($dataevento_2)) { echo "Nenhuma Informação para listar";}
?>

<div id="repeteev" style="display: none"> 
<div class="interna2" style="background-color:#ccc; margin-top: 6px !important; margin: 3px;">    
   <div class="row">
      <div class="col-md-12">
         <div class="panel panel-default">
             <div class="panel-body"> 
                <table>
                    <tr>
                       
                        <td>                            
                            <input id="rhevento_idpessoa"  type="hidden" value="" >
                            <input id="rhevento_dataevento"  type="hidden" value="" >
                        </td>
                    </tr>
                </table>
                 <div class="modal-body">
				 <div class="row" id="optionsTipos" style="padding: 2px;">
<?
            $sql="select idrhtipoevento,evento from rhtipoevento where status = 'ATIVO' and flgmanual= 'Y' and formato in ('H','HI') order by evento";
            $res=d::b()->query($sql) or die("Erro ao carregar eventos sql=".$sql);
            while($row=mysqli_fetch_assoc($res)){
?>                 
                
                    
                        <span style="background-color: #337ab7; margin-top: 2px;" class="list-group-item btn btn-light col-md-6">
                            <a class="selectTipo pointer" id="eventoTipo13" style="white-space: normal; color: #FFF; font-size: 10px; text-transform: uppercase; text-align: center; width: 100%;"  onclick="criaEventoP(<?=$row['idrhtipoevento']?>)"><?=$row['evento']?>
                            </a>
                        </span>
                 
               
<?
            }
			
?>         
   </div>
       </div>  
            </div>
         </div>
      </div> 
   </div> 
</div>
</div>

<script>
    
    $('.selectpicker').selectpicker({
            selectAllText: '<span class="glyphicon glyphicon-check"></span>',
            deselectAllText: '<span class="glyphicon glyphicon-remove"></span>'
        });

    $("#pickerempresa").on("changed.bs.select", function(e, clickedIndex, isSelected, oldValue) {
        let ids;
        ids = $(e.target).val();
        var inativos  = $("[name=inativos]").val();
        $("#picker").selectpicker('hide');
        $("#idsgdepartamento").selectpicker('refresh');
        $.ajax({
            type: "post",
            url:'ajax/getinfoponto.php',
             data: { ids : ids,inativos : inativos},
            success: function(data){
                try{
                    let arr = JSON.parse(data);

                    $("#picker").html(arr.pessoas);
                    $("#picker").selectpicker('refresh');
                    $("#idsgdepartamento").html(arr.departamentos);
                    $("#idsgdepartamento").selectpicker('refresh');

                }catch(err){

                }
             
            },
            error: function(objxmlreq){
                alert('Erro:<br>'+objxmlreq.status);
            }
            });
        $("#picker").selectpicker('show');
        $("#idsgdepartamento").selectpicker('show');
      
  });
    
 function repeteev(indipessoa,indate){
    var strCabecalho = "</strong>Gerar novo evento</strong>";
    $("#cbModalTitulo").html((strCabecalho));

    var  htmloriginal =$("#repeteev").html();
    var objfrm= $(htmloriginal);

    objfrm.find("#rhevento_idpessoa").attr("name", "rhevento_idpessoa");
    objfrm.find("#rhevento_idpessoa").attr("value",  indipessoa); 
    objfrm.find("#rhevento_dataevento").attr("name", "rhevento_dataevento");
    objfrm.find("#rhevento_dataevento").attr("value",  indate); 
   
    
    $("#cbModalCorpo").html(objfrm.html());
    $('#cbModal').modal('show');   		
} 

function criaEventoP(inidrhtipoevento){

    CB.modal({
            url:"?_modulo=rhevento&_acao=i&_idempresa=<?=cb::idempresa()?>&idpessoa="+$("[name=rhevento_idpessoa]").val()+"&dataevento="+$("[name=rhevento_dataevento]").val()+"&idrhtipoevento="+inidrhtipoevento
            ,header:"Evento RH"
	});
        
 /*   
    var str="_1_i_rhevento_idpessoa="+$("[name=rhevento_idpessoa]").val()+
            "&_1_i_rhevento_dataevento="+$("[name=rhevento_dataevento]").val()+
            "&_1_i_rhevento_idrhtipoevento="+inidrhtipoevento;
    CB.post({
        objetos: str 
        ,parcial:true
        ,refresh: false
        ,posPost: function(resp,status,ajax){
            debugger;
            if(status="success"){
                //$("#cbModalCorpo").html("");
                //$('#cbModal').modal('hide');
                abreEvento(CB.lastInsertId);
            }else{
                alert(resp);
            }
        }
    });
    */
}

function abreEvento(inid){
	CB.modal({
            url:"?_modulo=rhevento&_acao=u&idrhevento="+inid
            ,header:"Evento RH"
	});
}

function alterabt(vthis,inidrhevento){
    
    $.ajax({
        type: "post",
        url:'ajax/alteraponto.php',
        data: { idrhevento : inidrhevento,status : 'A'},

        success: function(data){
            if(data='ok'){
                location.reload();
            }else{
                alert(data);
            }
        },
        error: function(objxmlreq){
            alert('Erro:<br>'+objxmlreq.status);
        }
    });//$.ajax 
}
    
function alterast(vthis,inidrhevento){
    // alert(inidrhevento);
    var entsai = $(vthis).attr('entsaida');
    var ns;
    var bt;
    var rbt;
    var st;
    if(entsai=='E'){
        ns='S';
        st='D';
        bt="btn-primary";
        rbt="btn-success";
    }else{
        ns='E';
        st='L';
        bt="btn-success";
        rbt="btn-primary";
    }
  
    $.ajax({
        type: "post",
        url:'ajax/alteraponto.php',
        data: { idrhevento : inidrhevento,status : ns},

        success: function(data){
            if(data='ok'){
                vthis.innerText=ns;
                $(vthis).attr('entsaida',ns);
                $(vthis).removeClass( rbt ).addClass( bt );
            }else{
                alert(data);
            }
        },
        error: function(objxmlreq){
            alert('Erro:<br>'+objxmlreq.status);
        }
    });//$.ajax 
}    
    
function pesquisar(vthis = null){
    if(vthis != null){
        $(vthis).html('<span class="fa fa-spinner fa-pulse"></span>');
    }
    var dataevento_1 = $("[name=dataevento_1]").val();
    var dataevento_2 = $("[name=dataevento_2]").val();
    var idempresa = ($("[name=idempresa]").val())?'idempresa='+$("[name=idempresa]").val():'';
    var idpessoa = $("[name=idpessoa]").val();
    var idsgsetor = $("[name=idsgsetor]").val();
    var idsgdepartamento = $("[name=idsgdepartamento]").val();
    var irregularidades = $("[name=irregularidades]").val();
    var inativos  = $("[name=inativos]").val();
    var str=idempresa+"&dataevento_1="+dataevento_1+"&dataevento_2="+dataevento_2+"&inativos="+inativos+"&idpessoa="+idpessoa+"&idsgsetor="+idsgsetor+"&idsgdepartamento="+idsgdepartamento+"&irregularidades="+irregularidades;
    CB.go(str);
}

$(document).keypress(function(e) {
  if(e.which == 13) {
    pesquisar();
  }
});

function bancohdes(){
	var dataevento_1 = $("[name=dataevento_1]").val();
    var dataevento_2 = $("[name=dataevento_2]").val();
    var idpessoa = $("[name=idpessoa]").val();
    var idsgsetor = $("[name=idsgsetor]").val();
	
	janelamodal('?_modulo=rhbancohoras&_acao=u&&dataevento_1='+dataevento_1+'&dataevento_2='+dataevento_2+'&idpessoa='+idpessoa+'&idsgsetor='+idsgsetor+'&tiporel=SIMPLES');

}

function bancohdet(){
	var dataevento_1 = $("[name=dataevento_1]").val();
    var dataevento_2 = $("[name=dataevento_2]").val();
    var idpessoa = $("[name=idpessoa]").val();
    var idsgsetor = $("[name=idsgsetor]").val();
	
	janelamodal('?_modulo=rhbancohoras&_acao=u&&dataevento_1='+dataevento_1+'&dataevento_2='+dataevento_2+'&idpessoa='+idpessoa+'&idsgsetor='+idsgsetor+'&tiporel=DETALHADO');

}
function bancohext(){
	var dataevento_1 = $("[name=dataevento_1]").val();
    var dataevento_2 = $("[name=dataevento_2]").val();
    var idpessoa = $("[name=idpessoa]").val();
    var idsgsetor = $("[name=idsgsetor]").val();
    var idsgdepartamento = $("[name=idsgdepartamento]").val();
    
	
	janelamodal('?_modulo=rhbancohoras&_acao=u&&dataevento_1='+dataevento_1+'&dataevento_2='+dataevento_2+'&idpessoa='+idpessoa+'&idsgsetor='+idsgsetor+'&tiporel=EXTRA&idsgdepartamento='+idsgdepartamento);

}


function novoponto(idpessoa,vdata,entsaida,vthis){
    debugger;  
    //$(vthis).prop('disabled', true);
    var text= $(vthis).val();
    if(text.length==5){
        $(vthis).prop('disabled', true);    
        CB.post({
            objetos: "_x_i_rhevento_idrhtipoevento=1&_x_i_rhevento_situacao=A&_x_i_rhevento_idpessoa="+idpessoa+"&_x_i_rhevento_dataevento="+vdata+"&_x_i_rhevento_hora="+$(vthis).val()+"&_x_i_rhevento_entsaida="+entsaida,
            parcial:true,
            refresh:false
        }); 
    }
}

function gerajustificativa(vthis,inidpessoa,idrandomico){
    debugger;
    
    vdataevento=$(vthis).attr('dataevento');
    usuario=$(vthis).attr('usuario');
  
	htmlTrModelo =$("#justificativa"+idrandomico).html();
	
	
		htmlTrModelo = htmlTrModelo.replace("#name_campo", "_1_i_rhjustificativa_idpessoa");
        htmlTrModelo = htmlTrModelo.replace("#valor_campo", inidpessoa);
		htmlTrModelo = htmlTrModelo.replace("#name_justificativa", "_1_i_rhjustificativa_justificativa");
	
        htmlTrModelo = htmlTrModelo.replace("#name_data", "_1_i_rhjustificativa_dataevento");
		htmlTrModelo = htmlTrModelo.replace("#data_campo",  vdataevento);

		var objfrm= $(htmlTrModelo);
		
		
		
		
		strCabecalho = "</strong>Justificativa <button id='cbSalvar' type='button' class='btn btn-success btn-xs' onclick='gravajustificativa("+idrandomico+");'><i class='fa fa-circle'></i>Salvar</button></strong>";

		CB.modal({
				titulo: strCabecalho,
				corpo: 	"<table>"+objfrm.html()+"</table>",
				classe: 'sessenta',
		});


}
function gravajustificativa(inidrandomico){
    debugger;
    var str="_x_i_rhjustificativa_idpessoa="+$("[name=_1_i_rhjustificativa_idpessoa]").val()+
    "&_x_i_rhjustificativa_justificativa="+$("[name=_1_i_rhjustificativa_justificativa]").val()+
              "&_x_i_rhjustificativa_dataevento="+$("[name=_1_i_rhjustificativa_dataevento]").val();
             
            $( "#"+inidrandomico ).removeClass( "blink" );
            $( "#"+inidrandomico ).removeClass( "fa-circle" );
            $( "#"+inidrandomico ). addClass( "fa-info-circle" );
            $( "#"+inidrandomico ).removeClass( "vermelho" );
            $( "#"+inidrandomico ). addClass( "preto" );
            $( "#"+inidrandomico ).removeClass( "hoververmelho" );
            $( "#"+inidrandomico ). addClass( "hoverazul" );
            $( "#"+inidrandomico ). addClass( "tip2" );
            $("#"+inidrandomico).attr('title','');

    vdataevento=$("#"+inidrandomico).attr('dataevento');
    usuario=$("#"+inidrandomico).attr('usuario');
    $("#span"+inidrandomico).html("<ul style='list-style-type: none;'><li style='color: black;'>"+$("[name=_1_i_rhjustificativa_justificativa]").val().toUpperCase()+" <br> <b>Por:</b> "+usuario+" - <b>Em:</b> "+vdataevento+"</li></ul>");
    $("#table"+inidrandomico).append("<tr><td><b>Justificativa:</b></td> <td>"+$("[name=_1_i_rhjustificativa_justificativa]").val().toUpperCase()+" </td> <td> <b>Por:</b> "+usuario+" </td> <td> <b>Em:</b> "+vdataevento+"</td></tr>");
                    

    CB.post({
            objetos: str
            ,parcial:true
            ,refresh:false
            ,posPost: function(resp,status,ajax){
                  if(status="success"){
                                       
                     $("#cbModalCorpo").html("");
                     $('#cbModal').modal('hide');
                      

                   
                  }else{
                     alert(resp);
                  }
            }
         });
}

function alteraoutros(vthis){
		valor=$(vthis).val();
		if(valor=='OUTROS'){ 
            $(vthis).attr('name', '#name_justificativa');
			$(vthis).parent().append('<input style="margin-top:4px;" id="justificaticaText" name="_1_i_rhjustificativa_justificativa" value="" class="size50" type="text" placeholder="Digite aqui a sua justificativa" />');
		}else{
            $(vthis).attr('name', '_1_i_rhjustificativa_justificativa');
			$('#justificaticaText').remove();
		}
	}
//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>