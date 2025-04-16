<?
include_once("../inc/php/validaacesso.php");

?>
<html>
<head>
<title>Funcion&aacute;rio</title>
</head>


<link href="../inc/css/rep.css" media="all" rel="stylesheet" type="text/css" />

<script language="JavaScript" src="../inc/js/functions.js"></script>
<?
    function getCamposVisiveis($inideventotipoadd){
        $sql = "select distinct(t.col) as col,t.rotulo,t.prompt,t.code,c.datatype,t.ord 
                    from eventotipocampos t 
join "._DBCARBON."._mtotabcol c on (c.col=t.col and c.tab= 'eventoobj')
                where t.ideventotipoadd=".$inideventotipoadd."
                    and t.visivel='Y' 
                    and ord is not null
                    and rotulo is not null order by t.ord,t.rotulo";

        $rts = d::b()->query($sql) or die("getCamposVisiveis: ". mysqli_error(d::b()));

        $arrtmp = array();
        

        while ($r = mysqli_fetch_assoc($rts)) {
            $arrtmp[$r["ord"]]["col"] = $r["col"];
            $arrtmp[$r["ord"]]["rotulo"] = $r["rotulo"];  
            $arrtmp[$r["ord"]]["prompt"] = $r["prompt"];
            $arrtmp[$r["ord"]]["code"] = $r["code"];
            $arrtmp[$r["ord"]]["datatype"] = $r["datatype"];
        }

        return $arrtmp;
    }
    
?>

<body>
<?
$figurarelatorio = "../inc/img/repheader.png";

$id = $_REQUEST['id'];
$tipo = $_REQUEST['tipo'];
$ideventotipo = $_REQUEST['ideventotipo'];
	
$eventotipo= traduzid('eventotipo', 'ideventotipo', 'eventotipo', $ideventotipo);

$sqlp="select * from eventotipoadd where ideventotipo=".$ideventotipo." and status='ATIVO'";
$resp = d::b()->query($sqlp) or die("Falha ao verificar o evento: " . mysqli_error(d::b()) . "<p>SQL: $sqlp");
$qtdadd=mysqli_num_rows($resp);


    

    if($tipo=='pessoa'){
        $sql=" select idpessoa as id,nome as nome from pessoa where idpessoa = '".$id."';";
        $asrot='Nome';
        $strin="e.idpessoaev =".$id ." ";
    }elseif($tipo=='tag'){
        $sql=" select idtag as id,concat(tag,' - ',descricao) as nome from tag where idtag='".$id."';";
        $asrot='TAG';
        $strin="e.idequipamento =".$id ." ";
    }elseif($tipo=='documento'){
        $sql=" select idregistro as id,titulo as nome from sgdoc where idsgdoc='".$id."';";
        $asrot='TAG';
        $strin="e.idsgdoc =".$id ." ";
    }
    //echo "<!-- ".$sql." -->";

    $res = d::b()->query($sql) or die("Falha ao pesquisar a origem do objeto : " . mysqli_error(d::b()) . "<p>SQL: $sql");
    $row = mysqli_fetch_array($res);

?>
<p>&nbsp;</p>	
    <table class="tbrepheader">
    <tr>
      <td class="header" pre-line>Id: <?=$row['id'];?></td>
  
      <td class="header" pre-line><?=$asrot?>: <?=strtoupper($row['nome']);?></td>
    </tr>
    </table>
	
<p>&nbsp;</p>	
<fieldset style="border: none; border-top: 2px solid silver;">
	<legend><?=$eventotipo?></legend>
</fieldset>
<?
if($qtdadd==0){
    $sqle="select idevento,eventotipo,evento,nome,idregistro,documento,ntag,tag,dmainicio,iniciohms,duracaohms,rotulo
                    from vwevento e
                    where ".$strin ." and e.ideventotipo=".$ideventotipo." order by evento";
    $rese = d::b()->query($sqle) or die("A Consulta dos eventos falhou :".mysql_error()."<br>Sql:".$sqle);
        
?>    

<table class='normal'>
<tr class="header">
	<td>ID</td> 
        <td>Evento</td>
        <td>nome</td>
        <td>Documento</td>
        <td>Tag</td>
        <td>Data</td>
        <td>Status</td>       
	
</tr>
<?
        while($rowe=mysqli_fetch_assoc($rese)){
?>
<tr class="res">
	<td pre-line>
             <a class="pointer" title="Editar" href="javascript:janelamodal('../?_modulo=evento&_acao=u&idevento=<?=$rowe['idevento']?>')">
        <?=$rowe['idevento']?>
             </a>
        </td> 	
        <td pre-line><?=$rowe['evento']?></td>
        <td pre-line><?=$rowe['nome']?></td> 
	<td pre-line><?=$rowe['idregistro']?></td>
        <td pre-line><?=$rowe['ntag']?></td>
        <td pre-line><?=$rowe['dmainicio']?> <?=$rowe['iniciohms']?></td>  
	<td pre-line><?=$rowe['rotulo']?></td>


</tr>
<?
        }
?>
</table>
<?
    }else{
       
        while($rowadd=mysqli_fetch_assoc($resp)){
            $arrc= getCamposVisiveis($rowadd['ideventotipoadd']);
			//Select alterado por causa do evento que foi add uma nova tabela eventoadd, onde salva os dados da tabela eventotipoadd. (LTM - 09-07-2020)
            $sqle="SELECT e.idevento,
						  e.evento,
						  e.eventotipo,
						  dma(e.inicio) as dmainicio,
						  e.status as statusev,
						  o.* 
					 FROM vwevento e JOIN eventoobj o ON e.idevento=o.idevento 
					 JOIN eventoadd ea ON o.ideventoadd = ea.ideventoadd 
					WHERE ea.idobjeto = ".$rowadd['ideventotipoadd']." 
					  AND o.idobjeto = ".$id." 
					  AND o.objeto = '".$tipo."'
				 ORDER BY e.inicio";
           // die($sqle);
	
            $reo = d::b()->query($sqle) or die("erro ao buscar os objetos do evento: ". mysqli_error(d::b()));
           ?>

            <table class='normal'>
            <tr class="header">
                <td>ID</td> 
                <td>Data</td>
                   
<?
                foreach ($arrc as $ord => $value) {
?>                        
                    <td><?=strip_tags($value['rotulo'])?></td>
<?
                }
?>       
                    <td>Status</td>
            </tr>
<? 
                reset($arrc);
            while($rowo= mysqli_fetch_assoc($reo)){
                ?>
            <tr class="res">
                <td>
                    <a class="pointer" title="Evento" href="javascript:janelamodal('../?_modulo=evento&_acao=u&idevento=<?=$rowo['idevento']?>')">
                        <?=$rowo['idevento']?>
                    </a>
                </td>
                <td><?=$rowo['dmainicio']?></td>
            <?
                    foreach($arrc as $ord => $value){
           
?>       
                                
                        <td>
                            <?if($value['datatype']=='varchar' and $value['prompt']=='select'){
                              echo $rowo[$value['col']];
                            }elseif($value['datatype']=='longtext'){?>
                               <?=$rowo[$value['col']]?>
                            <?}else{
                                if($value['datatype']=='date'){
                                   
                                     echo(dma($rowo[$value['col']]));
                                }elseif($value['datatype']=='datetime'){
                                    echo(dmahms($rowo[$value['col']]));
                                }else{
                                   echo($rowo[$value['col']]);
                                }
                                ?>
                               
                            <?}?>
                        </td>
                       
<?
                }
                reset($arrc);
                ?>
                        <td><?=$rowo['statusev']?></td>
                        </tr>  
                <?
            }
            ?>
            </table>
                <?
        }
        
    }
?>
<p>&nbsp;</p>	
<hr style="background-color: solid silver;">

</body>
</html>


