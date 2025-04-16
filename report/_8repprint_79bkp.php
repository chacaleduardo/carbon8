<?
include_once("../inc/php/validaacesso.php");
baseToGet($_GET["_filtros"]);

if($_POST){
   require_once("../inc/php/cbpost.php");
}

?>
<html>
<head>
<title>Relat�rio de Ponto</title>
<script src="../inc/js/jquery/jquery-1.11.2.min.js"></script>
<script src="../inc/js/jquery/jquery-ui.js"></script>
<script src="../inc/js/jquery/jquery.autosize-min.js"></script>
<script src="../inc/css/bootstrap/js/bootstrap.min.js"></script>
<script src="../inc/js/htmlentities/he.js"></script>
<script src="../inc/js/daterangepicker/moment.min.js"></script>
<script src="../inc/js/daterangepicker/daterangepicker.js"></script>
<script src="../inc/js/notifications/smart.js"></script>
<script src="../inc/js/webuipopover/jquery.webui-popover.js"></script>
<script src="../inc/js/accent-fold.js"></script>
<script src="../inc/js/bootstrap-select/bootstrap-select.js"></script>
<script src="../inc/js/bootstrap-select/i18n/defaults-pt_BR.js"></script>
<script src="../inc/js/bootstrap-select/i18n/defaults-pt_BR.js"></script>
<script src="../inc/js/tinymce/tinymce.min.js"></script>
<script src="../inc/js/dropzone/dropzone.js"></script>
<script src="../inc/js/diagrama/Treant.js"></script>
<script src="../inc/js/diagrama/vendor/raphael.js"></script>
<script src="../inc/js/colorpalette/js/bootstrap-colorpalette.js"></script>
<script src="../inc/js/cookie/js.cookie.js"></script>
<script src="../inc/js/ping/ping.js"></script>

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../inc/css/8rep.css" media="all" rel="stylesheet" type="text/css" />
<link rel="shortcut icon" type="image/ico" href="../inc/img/favicon.ico"/>
<link rel="icon" href="../../../favicon.ico">

<link href="../inc/css/bootstrap/css/bootstrap.min.css" media="all" rel="stylesheet" type="text/css" />
<link href="../inc/css/fonts/laudo/laudofonts.css" media="all" rel="stylesheet" type="text/css" />
<link href="../inc/css/fontawesome/font-awesome.min.css" media="all" rel="stylesheet" type="text/css" />
<link href="../inc/js/daterangepicker/daterangepicker.css" media="all" rel="stylesheet" type="text/css" />
<link href="../inc/js/select2/select2.min.css" media="all" rel="stylesheet" type="text/css" />
<link href="../inc/js/notifications/smart.css" media="all" rel="stylesheet" type="text/css" />
<link href="../inc/css/bootstrap-toggle/bootstrap-toggle.css" media="all" rel="stylesheet" type="text/css" />
<link href="../inc/js/webuipopover/jquery.webui-popover.css" media="all" rel="stylesheet" type="text/css" />
<link href="../inc/js/bootstrap-select/bootstrap-select.css" media="all" rel="stylesheet" type="text/css" />
<link href="../inc/js/jquery/jquery-ui.min.css" media="all" rel="stylesheet" type="text/css" />
<link href="../inc/js/diagrama/Treant.css" media="all" rel="stylesheet" type="text/css" />
<link href="../inc/js/colorpalette/css/bootstrap-colorpalette.css" media="all" rel="stylesheet" type="text/css" />
<link href="../inc/fullcalendar/fullcalendar.min.css" media="all" rel="stylesheet" type="text/css" />

<script src="../inc/js/functions.js"></script>
<script src="../inc/js/carbon.js?_p<?=date("dmYhms")?>"></script>
	<!-- CSS Carbon -->
	<link href="../inc/css/carbon.css" rel="stylesheet">
	<link href="../inc/css/sislaudo.css" rel="stylesheet">
	<link href="../inc/js/diagrama/Treant.css" rel="stylesheet">

	<!-- Scripts Carbon -->
	<script src="../inc/nodejs/notificacoes/socket.io.js"></script>
	<script src="../inc/nodejs/notificacoes/notificacoes.php"></script>
<!-- Forcar atualizacao do s scripts -->

<style type="text/css">
   table { page-break-inside:auto; width:100% }
    tr    { page-break-inside:avoid; page-break-after:auto }
    thead { display:table-header-group }
    tfoot { display:table-footer-group }
	@media print
{    
    .no-print, .no-print *
    {
        display: none !important;
    }
	footer {
    position: fixed;
    bottom: 0;
  }
}
footer {
  font-size: 9px;
  color: #f00;
  text-align: center;
}
td table{
    font-size: 12px !important;
  
    
}
tr {
    height: 20px;
}

</style>
</head>
<body>
<?

if (!empty($_GET)){
$_modulo = $_GET["_modulo"];
if ($_GET["relatorio"]){
	$_idrep = $_GET["relatorio"];
}else{
	$_idrep = $_GET["_idrep"];
}

//Recupera a definicao das colunas da view ou table default da pagina
$arrRep=getConfRelatoriosModulo($_modulo,true,$_idrep);

//Facilita a utiliza��o do array
$arrRep=$arrRep[$_idrep];

//print_r($arrRep);
//die();
$_rep = $arrRep["rep"];
$_header = $arrRep["header"];
$_footer = $arrRep["footer"];
$_showfilters = $arrRep["showfilters"];
$_tab = $arrRep["tab"];
$_newgrouppagebreak = $arrRep["newgrouppagebreak"];
$_pbauto = $arrRep["pbauto"];
$_showtotalcounter = $arrRep["showtotalcounter"];
$_compl = $arrRep["compl"];
$_descr = $arrRep["descr"];
$_rodape = $arrRep["rodape"];
$_chavefts = $arrRep["chavefts"];
$_tabfull = $arrRep["tabfull"];


	if(!empty($_GET["_fts"])){
            //Ajusta preferencias do usuario
            userPref("u", $_modulo."._fts", $_GET["_fts"]);



             $arrFk = retPkFullTextSearch($_tabfull, $_GET["_fts"]/*, $_GET["_pagina"],$_arrModConf["limite"]*/);
            //print_r($arrFk);
            //	echo '<br>';
            $countArrFk=$arrFk["foundRows"];
            if($countArrFk>0){

                    $strPkFts = implode(",", $arrFk["arrPk"]);
                    $strPkFts = $aspa . implode(($aspa.",".$aspa), $arrFk["arrPk"]) . $aspa;
                    $str_fts = " and ".$_chavefts . " in (".$strPkFts.")";
            }
        }else{
           $str_fts='';
        }
        
        $idusuario= $_SESSION["SESSAO"]["IDPESSOA"];//$perfilpag_idpessoa;

        $sql="select * from pessoa where supervisor ='Y' and idpessoa =".$idusuario;
        $res=d::b()->query($sql) or die("erro ao buscar supervisor sql=".$sql);
        $_supervisor= mysqli_num_rows($res);
        if($_supervisor<1){
           $strin.=" and p.idpessoa ='".$idusuario."' "; 
        }
        
        if(!empty($_GET['batida'])){
            $strin.=" and p.batida='".$_GET['batida']."' ";
        }
        if(!empty($_GET['entsaida'])){
            $strin.=" and p.entsaida='".$_GET['entsaida']."' ";
        }
        
        $_nomeimpressao = "[".md5(date('dmYHis'))."] gerada em [".date(" d/m/Y H:i:s")."]";



	/*
	$sqlfig="select figrelatorio from empresa where idempresa =".$_SESSION["SESSAO"]["IDEMPRESA"];
	$resfig = d::b()->query($sqlfig) or die("Erro ao recuperar figura para cabe�alho do relat�rio: ".mysql_error());
	$figrel=mysqli_fetch_assoc($resfig);

	//$figurarelatorio = (empty($figrel["figrelatorio"]))?"../inc/img/repheader.png":$figrel["figrelatorio"];
	$figurarelatorio = "../inc/img/repheader.png";
	*/
	
	// GVT - 17/04/2020 - Altera logo do relat�rio, utiliza a imagem cadastrada na empresa.
	$sqlfig="select logosis from empresa where idempresa =".$_SESSION["SESSAO"]["IDEMPRESA"];
	$resfig = d::b()->query($sqlfig) or die("Erro ao recuperar figura para cabe�alho do relat�rio: ".mysql_error());
	$figrel=mysqli_fetch_assoc($resfig);

	//$figurarelatorio = (empty($figrel["figrelatorio"]))?"../inc/img/repheader.png":$figrel["figrelatorio"];
	//$figurarelatorio = "../inc/img/repheader.png";
	$figurarelatorio = $figrel["logosis"];
	
        if ($_REQUEST['_fds']){

            //echo 'aqui';
            $data = explode('-',$_REQUEST['_fds']);

            $data1= implode("-",array_reverse(explode("/",$data[0])));
            $data2= implode("-",array_reverse(explode("/",$data[1])));

            //die($data1."  ".$data2);
            
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
                     
                   $s1="select distinct(f.idpessoa) as idpessoa,f.nome 
                        from vwponto p,pessoa f
                        where f.idpessoa = p.idpessoa
                        and f.status='ATIVO'
                        ".$strin."
                         ".$str_fts." order by f.nome";
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
            $data2 = $data2.' 23:59:59';
            $s1="select 
                        idpessoa,nome,dataponto,idponto,hora,semana,batida,entsaida,obs
                    from vwponto p 
                    where data between '".$data1."' and '".$data2."'
                    ".$strin."
                     ".$str_fts." order by nome,hora";
             echo "<!-- ".$s1." -->";	//echo $_sqlresultado;
             $re1= d::b()->query($s1) or die("erro ao buscar os pontos  sql=".$s1);

            while($r=mysqli_fetch_assoc($re1)){
               // $arrayp[$r['idpessoa']]['semana'][$rw['diabusca']]=$r['semana'];  
                $arrayp[$r['idpessoa']][$r['nome']][$r['dataponto']][$r['idponto']]['idponto']=$r['idponto'];  
                $arrayp[$r['idpessoa']][$r['nome']][$r['dataponto']][$r['idponto']]['semana']=$r['semana'];
                $arrayp[$r['idpessoa']][$r['nome']][$r['dataponto']][$r['idponto']]['hora']=$r['hora'];
                $arrayp[$r['idpessoa']][$r['nome']][$r['dataponto']][$r['idponto']]['batida']=$r['batida'];
                $arrayp[$r['idpessoa']][$r['nome']][$r['dataponto']][$r['idponto']]['entsaida']=$r['entsaida'];  
                $arrayp[$r['idpessoa']][$r['nome']][$r['dataponto']][$r['idponto']]['obs']=$r['obs'];

            }      
                
        }else{
            die('Para este relat�rio � necess�rio informar a data de intervalo');
        }
		
	//print_r($arrayp); die;
	
?>
	<table class="tbrepheader">
	<tr>
		<td rowspan="3" style="width:200;"><img src="<?=$figurarelatorio?>"></td>
		<td class="header"><? //=$_header?></td>
		<td></td>
	</tr>
	<tr>
		<td class="subheader" ><h2><?=($_rep);?></h2>
		</td>
	</tr>
	</table>
	<br>
	<fieldset class="fldsheader">
	  <legend>In�cio da Impress�o <?=$_nomeimpressao?></legend>
	</fieldset>
        <table>
                <tr>
                    <td>
                
<?
    foreach ($arrayp as $idpessoa => $arrayfunc) {
        $totalh=0;
        $thextra=0;
        $horasentrada=0;
?>
            <br>
        <table class="normal">
<?
        foreach ($arrayfunc as $nome => $arrdata) {
?>
            <thead>
                <tr class="titulo">
                    <td class="titulo"  style="text-align:center" colspan="6">
                    <?
                    if($nome!='vazio'){                    
                        echo('<b>'.$nome.'</b>');
                        $sqlh="select idpessoahorario, 
                                left(horaini,5) as horaini,
                                left(horafim,5) as horafim,
                                case when left(horaini,5) > '18:00' then 'NOTURNO' else 'DIURNO' end as horario
                                 from pessoahorario where idpessoa=".$idpessoa." order by horaini";
                        $resh=d::b()->query($sqlh) or die("Erro ao buscar horarios do funcion�rio : " . mysqli_error(d::b()) . "<p>SQL:".$sqlh);
                         $virgula="&nbsp&nbsp&nbsp&nbsp&nbsp- ";
                        while($rowh = mysqli_fetch_assoc($resh)){
                            echo($virgula);
                            echo("&nbsp&nbsp&nbsp&nbsp&nbsp<b>".$rowh["horaini"]."</b> �s <b>".$rowh["horafim"]."</b>");
                            $HORARIO=$rowh['horario'];
                           // $virgula=" ";
                        }
                    }
                    
                    ?>
                    </td>  
                </tr>
                <tr class="header"> 
                    <td class="header"  style="text-align:left" >Dia </td>
                    <td class="header"  style="text-align:center" >Ponto </td>
                    <td class="header"  style="text-align:center" >Aus�ncia</td>
                    <td class="header" style="text-align:left">Pontos</td>
                    <td class="header" style="text-align:left">Horas</td>
                    <td class="header" style="text-align:left">Extra</td>
                </tr>
            </thead>
            <tbody>
 <?          $entsaida='';   
            foreach ($arrdata as $data => $arraponto) {
                    $sm="select WEEKDAY('".$data."') as dsem";
                    $rm=d::b()->query($sm);
                    $wm=mysqli_fetch_assoc($rm);
                    if($wm['dsem']==6 or $wm['dsem']==5){
                        $corf="yellow";
                        $horapd=0;
                    }else{
                         $corf="";
                        if($HORARIO!='NOTURNO'){
                            $horapd=8;
                        }else{
                            $horapd=7;
                        }
                    }
                    $sf="select obs from feriado where dataferiado ='".$data."'";
                    $rf=d::b()->query($sf);
                    $qtf=mysqli_num_rows($rf);
                    $wf=mysqli_fetch_assoc($rf);
                    if($qtf>0){
                        $corfr="#ff0000ad";
                        $horapd=0;
                    }
        ?>  
                <tr class="res" style="background-color: <?=$corf?>">               
                    <td ><?=dma($data)?> - <?=$arraponto[1]['semana']?> <font color="red"> <?=$wf['obs']?></font></td>
                    <td style="text-align:center"><i title="Novo ponto" class="fa fa-plus-circle fa-1x  cinzaclaro hoververde pointer" onclick="javascript:janelamodal('../?_modulo=ponto&_acao=i&idpessoa=<?=$idpessoa?>')"></i></td>
                    <td style="text-align:center"><i title="Novo ponto Ausencia" class="fa fa-plus-circle fa-1x  cinzaclaro hoververde pointer" onclick="javascript:janelamodal('../?_modulo=ponto&_acao=i&ausencia=Y&idpessoa=<?=$idpessoa?>')"></i></td>                
                   
                    <td>
<?                  
                    $horadia=0;
                    foreach ($arraponto as $idponto => $value) {
                        if($idponto>1){
                           $status= traduzid('ponto', 'idponto', 'batida', $idponto);

                            if($value['entsaida']=='E'){
                               // $cor="#c2f5c1";
                                $cbt="btn-success";
                            }else{
                              // $cor="#dfdfe8"; 
                               $cbt="btn-primary ";
                            }
                            $cor="";
                            if($status!="ATIVO"){
                                //so aprova se for supervisor
                                if($_supervisor<1){
                                    $_fn="alterast";
                                }else{
                                    $_fn="alterabt";
                                }
?>
                                <button title="<?=$status?>" entsaida="<?=$value['entsaida']?>"  type="button" class="btn btn-danger btn-xs" onclick="<?=$_fn?>(this,<?=$idponto?>)">                           
                                    <?=$value['entsaida']?>
                                </button>
<?                                
                            }else{
?>    
                                <button title="<?=$status?>" entsaida="<?=$value['entsaida']?>"  type="button" class="btn <?=$cbt?> btn-xs" onclick="alterast(this,<?=$idponto?>)">                           
                                    <?=$value['entsaida']?>
                                </button>
<?                                
                            }
                            //$value['idponto'];
                            //$value['nome'];
                            //$value['semana'];
?>  
                        : 
                            <span >
                                <i title="<?=$value['obs']?>" class="pointer" onclick="javascript:janelamodal('../?_modulo=ponto&_acao=u&idponto=<?=$value['idponto']?>')"> <?=$value['hora']?></i>
                            </span>&nbsp;&nbsp;&nbsp;
<?                        
                            //$value['batida'];
                            //$value['entsaida'];
                                //CALCULAR HORAS
                            if($HORARIO!='NOTURNO'){
                                if(empty($inicio)){
                                    $inicio=$value['hora'];
                                }else{
                                    $arrinicio = explode(":",$inicio);
                                    $arrfim = explode(":",$value['hora']);

                                    $difhora =  $arrfim[0] - $arrinicio[0] ;

                                    $mdifhora= $difhora * 60;

                                    $difmin =  $arrfim[1] - $arrinicio[1] ;

                                    $ress = $mdifhora + $difmin;

                                    $ressfim = $ress / 60;
                                    //echo round($ressfim,2);
                                    $horadia=$horadia+$ressfim;
                                    $totalh= $ressfim + $totalh;
                                    $inicio=''; 
                                    
                                }
                               
                            }else{//trabalha a noite
                                
                                if($value['entsaida']=='E'){
                                    $inicio=$value['hora'];
                                    $fim='24:00';
                                }else{
                                    $inicio='00:00';
                                    $fim=$value['hora'];
                                }   
                            
                                    $arrinicio = explode(":",$inicio);
                                    $arrfim = explode(":",$fim);

                                    $difhora =  $arrfim[0] - $arrinicio[0] ;

                                    $mdifhora= $difhora * 60;

                                    $difmin =  $arrfim[1] - $arrinicio[1] ;

                                    $ress = $mdifhora + $difmin;

                                    $ressfim = $ress / 60;
                                    //echo round($ressfim,2);
                                    $horadia=$horadia+$ressfim;
                                    $totalh= $ressfim + $totalh;
                                    $inicio='';  
                                    
                                    //HORA EXTRA NOTURNO
                                    if($value['entsaida']=='E'){
                                        $horasentrada=$horadia;
                                    } 
                                    $entsaida=$value['entsaida'];
                            }        
                        }
                    }                  
                    ?>

                    </td>
                    <td><?=round($horadia,2);?></td>
                    <td>
                    <?  
                     //HORA EXTRA
                    if($HORARIO!='NOTURNO'){
                        $hrextradia=$horadia-$horapd;
                        $thextra=$thextra+$hrextradia;
                    }else{
                        
                        if($entsaida=='E'){
                            $hrextradia=0;
                        }else{
                            $horaserv=$horadia+$horasentrada;
                            $hrextradia=$horaserv-$horapd;
                            $thextra=$thextra+$hrextradia;
                            $horasentrada=0;
                        }
                    }
                    echo round($hrextradia,2);
                    ?></td>
                    <?
                    
                    $inicio='';
?>

                </tr>
            
<?           
                
            }
        }
?>
                <tr>
                    <td colspan="4">Soma:</td>
                    <td ><?=round($totalh,2)?></td>
                    <td ><?=round($thextra,2)?></td>
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

<?

	
	
	
	
}//if (!empty($_GET)){
?>
 
<?
if(defined("_RODAPEDIR")) $varfooter= _RODAPEDIR;
?>
    <footer>
     
    </footer>
</body>
	<fieldset class="fldsfooter">
	<legend>Fim da Impress�o <?=$_nomeimpressao . " ".$varfooter?></legend>
	</fieldset>
</body>
</html>
<script>

function alterabt(vthis,inidponto){
    
    $.ajax({
        type: "post",
        url:'../ajax/alteraponto.php',
        data: { idponto : inidponto,status : 'A'},

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
    
function alterast(vthis,inidponto){
    // alert(inidponto);
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
        url:'../ajax/alteraponto.php',
        data: { idponto : inidponto,status : st},

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
 
 //o comentario abaixo faz com que este peda�o de script apare�a na aba 'sources' do inspetor do google chrome
//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>;
</script>


<?
if(!empty($_GET["reportexport"])){
	ob_end_clean();//n�o envia nada para o browser antes do termino do processamento
	
	/* Gerar o nome do arquivo para exportar
	 * Substitui qualquer caractere estranho pelo sinal de '_'
	 * Caracteres que NAO SERAO substituidos:
	 *   - qualquer caractere de A a Z (maiusculos)
	 *   - qualquer caracteres de a a z (minusculos)
	 *   - qualquer caractere de 0 a 9
	 *   - e pontos '.'
	 */ 
	$infilename = empty($_header)?$_rep:$_header;
	$infilename = preg_replace("/[^A-Za-z0-9s.]/", "", $infilename);
	//gera o csv
	header("Content-type: text/csv; charset=UTF-8");
	header("Content-Disposition: attachment; filename=".$infilename.".csv");
	header("Pragma: no-cache");
	header("Expires: 0");
	
	echo($conteudoexport);
	
}
?>
