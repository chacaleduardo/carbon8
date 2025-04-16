<?
require_once("../inc/php/validaacesso.php");

$idbioensaio = $_GET['idbioensaio'];

if(!empty($idbioensaio)){
    $clausulam .=" and idbioensaio =".$idbioensaio;
}

$sql="select * from vwbioensaio where idempresa =".$_SESSION["SESSAO"]["IDEMPRESA"]." ".$clausulam;

echo "<!--";
echo $sql;
echo "-->";
?>
<html>
<head>
<title>Bioensaio</title>
<style>
.tbgr{/*tabela de agrupamento de informacoes*/
	border: 1px solid rgb(225,225,225);
	border-collapse: collapse;
	width: 100%;
	padding-right: 20px;
}
	.tbgr .grcab{/*cabecalho*/
		background-color: rgb(225,225,225);
		font-size: 8pt;
		color: rgb(76,76,76);
		padding-left: 4px;
		font-weight:bold;
	}
	.tbgr .grcabteste{/*cabecalho*/
		background-color: rgb(225,225,225);
		font-size: 9pt;
		padding-left: 4px;
		font-weight:bold;
	}
	.tbgr .grrot{
		border:1px solid rgb(225,225,225);
		font-size: 8pt;
		color: rgb(102,102,102);
		text-align: right;
		padding-left: 4px;
		padding-right: 4px;
	}
	.tbgr .grvalb{/*valor negrito*/
		border:1px solid rgb(225,225,225);
		font-size: 9pt;
		font-weight: bold;
		padding-left: 4px;
		padding-right: 4px;
	}
	.tbgr .grval{/*valor SEM negrito*/
		border:1px solid rgb(225,225,225);
		font-size: 9pt;
		padding-left: 4px;
		padding-right: 4px;
	}
	.tbgr .lbrot{/*outras informacoe da amostra*/
		font-size: 8pt;
		color: rgb(102,102,102);
		text-align: right;
		padding-left: 4px;
		padding-right: 4px;
	}
	.tbgr .lbval{/*outras informacoe da amostra*/
		font-size: 9pt;
		padding-left: 4px;
		padding-right: 4px;
	}
        
.borderblue div{
border-radius: 10px;
border: 1px solid #B5B5B5;
}

.trblue{
font-size: 14px; background-color:#B5B5B5;
}

.trtitulo font{
color:#828282;
}

table{
font-family: Sans-serif;
font-size:14px;
}


div{
font-family: Sans-serif;
line-height:250%;
font-size:14px;
text-align: justify;
}

b{
font-size:18px;
}

.borderblue td{
min-width: 300px;
}
.borderblue table{
font-size: 14px;
}
.trblue font{
 color:black;
}

.divcirculo{
background:#B5B5B5;
color:black;
width:30px;
height:30px;
line-height:30px;
text-align:center;
font-size:16px;
border-radius:50%;
display: inline-block;
}

.conteudo{
align-items: center;
display: flex;
flex-direction: row;
flex-wrap: wrap;
justify-content: center;		
}

li{
font-family: Sans-serif;
font-size:16px !important;
}

.grrot font{
font-family: Sans-serif;
font-size:16px !important;
}

.grval font{
font-family: Sans-serif;
font-size:16px !important;
}

@media print { 
  * {
    -webkit-transition: none !important;
    transition: none !important;
  }
}
* {
    text-shadow: none !important;
    filter:none !important;
    -ms-filter:none !important;
    font-family: Helvetica, Arial;
    font-size: 10px;
    -webkit-box-sizing: border-box; 
    -moz-box-sizing: border-box;    
    box-sizing: border-box; 
}
html{
    background-color: silver;
}
body {
    line-height: 1.4em;
    background-color: white;
}

@media screen{
    body {
        margin: auto;
        margin-top: 0.2cm;
        margin-bottom: 1cm;
        padding: 3mm 10mm;
        width: 21cm;
    }
    .quebrapagina{
        page-break-before:always;
        border: 2px solid #c0c0c0;
        width: 120%;
        margin: 1.5cm -1.5cm;
    }
    .rot{
        color: gray;
    }
}

@media print{
    html{
        background-color: transparent;
    }
    body {
        margin: 0cm;
    }
    .quebrapagina{
        page-break-before:always;
    }
    .rot{
        color: #777777;
    }
}

.ordContainer{
    display: flex;
    flex-direction: column;
}
.ord1{order: 1;}
.ord2{order: 2;}
.ord3{order: 3;}
.ord4{order: 4;}
.ord5{order: 5;}
.ord6{order: 6;}
.ord7{order: 7;}
.ord8{order: 8;}
.ord9{order: 9;}
.ord10{order: 10;}
.ord11{order: 11;}
.ord12{order: 12;}
.ord13{order: 13;}
.ord14{order: 14;}
.ord15{order: 15;}
.ord16{order: 16;}
.ord17{order: 17;}
.ord18{order: 18;}
.ord19{order: 19;}
.ord20{order: 20;}
.ord21{order: 21;}
.ord22{order: 22;}
.ord23{order: 23;}
.ord24{order: 24;}
.ord25{order: 25;}
.ord26{order: 26;}
.ord27{order: 27;}
.ord28{order: 28;}
.ord29{order: 29;}
.ord30{order: 30;}
.ord31{order: 31;}
.ord32{order: 32;}
.ord33{order: 33;}
.ord34{order: 34;}
.ord35{order: 35;}
.ord36{order: 36;}
.ord37{order: 37;}
.ord38{order: 38;}
.ord39{order: 39;}
.ord40{order: 40;}
.ord41{order: 41;}
.ord42{order: 42;}
.ord43{order: 43;}
.ord44{order: 44;}
.ord45{order: 45;}
.ord46{order: 46;}
.ord47{order: 47;}
.ord48{order: 48;}
.ord49{order: 49;}
.ord50{order: 50;}
.ord51{order: 51;}
.ord52{order: 52;}
.ord53{order: 53;}
.ord54{order: 54;}
.ord55{order: 55;}
.ord56{order: 56;}
.ord57{order: 57;}
.ord58{order: 58;}
.ord59{order: 59;}
.ord60{order: 60;}
.ord61{order: 61;}
.ord62{order: 62;}
.ord63{order: 63;}
.ord64{order: 64;}
.ord65{order: 65;}
.ord66{order: 66;}
.ord67{order: 67;}
.ord68{order: 68;}
.ord69{order: 69;}
.ord70{order: 70;}
.ord71{order: 71;}
.ord72{order: 72;}
.ord73{order: 73;}
.ord74{order: 74;}
.ord75{order: 75;}
.ord76{order: 76;}
.ord77{order: 77;}
.ord78{order: 78;}
.ord79{order: 79;}
.ord80{order: 80;}
.ord81{order: 81;}
.ord82{order: 82;}
.ord83{order: 83;}
.ord84{order: 84;}
.ord85{order: 85;}
.ord86{order: 86;}
.ord87{order: 87;}
.ord88{order: 88;}
.ord89{order: 89;}
.ord90{order: 90;}
.ord91{order: 91;}
.ord92{order: 92;}
.ord93{order: 93;}
.ord94{order: 94;}
.ord95{order: 95;}
.ord96{order: 96;}
.ord97{order: 97;}
.ord98{order: 98;}
.ord99{order: 99;}
.ord100{order: 100;}


[class*='5']{width: 5%;}
[class*='10']{width: 9%;}
[class*='15']{width: 15%;}
[class*='20']{width: 20%;}
[class*='25']{width: 25%;}
[class*='30']{width: 30%;}
[class*='35']{width: 35%;}
[class*='40']{width: 39.99%;}
[class*='45']{width: 45%;}
[class*='50']{width: 50%;}
[class*='55']{width: 55%;}
[class*='60']{width: 60%;}
[class*='65']{width: 65%;}
[class*='70']{width: 70%;}
[class*='75']{width: 75%;}
[class*='80']{width: 80%;}
[class*='85']{width: 85%;}
[class*='90']{width: 90%;}
[class*='95']{width: 95%;}
[class*='100']{width: 100%;}
header{
    background-color: white;
    top: 0;
    height: 1cm;
    line-height: 1cm;
    display: table;
}
hr{
    margin: 0;
}
.logosup{
    height: inherit;
    line-height: inherit;
    display: table-cell;
}
.logosup img{
    height: 1cm;
    vertical-align: middle;
}
.titulodoc{
    height: inherit;
    line-height: inherit;
    display: table-cell;
    text-align: center;
    font-size: 0.5cm;
    font-weight: bold;
}
.row{
    display: table;
    table-layout: fixed;
    width: 99%;
    margin: 0mm 0mm;
}
.linhainferior{
    border-bottom: 1px dashed gray;
}
.col{
    display: table-cell;
    white-space: nowrap;
    padding: 1.5mm 1mm;
}
.row.grid .col{
    border: 1px solid silver;	
}
.row.grid .col:first-child{
    border-top: 1px solid silver;
}
.col.grupo {}
.col.grupo .titulogrupo{
    margin: 0px;
    border-bottom: 1px solid silver;
    color: #777777;
    font-weight: bold;
    Xmargin-bottom: 2mm;
}
.rot{
    overflow: hidden;
    font-size: 12px;
}
.quebralinha{
    white-space: normal;
}
[class*='margem0.0']{
    margin: 0 0;
}
.hidden{
    display: none;
}
.sublinhado{
    border-bottom: 1px dashed gray;
}
.fonte8{
    font-size: 8px;
}
.resultadodescritivo{
    margin: 0 0;
}
.resultadodescritivo p{
    margin: 0 0;	
}
</style>
<script language="javascript">

</script>
</head>

<?
    $sqlfig="select figrelatorio from empresa where idempresa =".$_SESSION["SESSAO"]["IDEMPRESA"];
    $resfig = d::b()->query($sqlfig) or die("Erro ao retornar figura para cabeçalho do relatório: ".mysqli_error());
    $figrel=mysqli_fetch_assoc($resfig);

if($_GET){	
    $res = d::b()->query($sql) or die("Erro ao pesquisar na vwbioensaio: ".mysqli_error());
    $row=mysqli_fetch_assoc($res);
?>
<body style="">

<div style="align-items: center; text-align: center;">


<br><br><br><br><br>
<b><pre ><p align="center" style="font-size:40px !important; ">
Relatório de Estudo Experimental

com Animais

</p></pre></b>
<br><br><br><br><br>
<?
if($row['agrupar']=='Y'){
    $sqlin="select
                concat('B',b.idregistro) as registro,b.*
            from bioensaio b 
            where b.idbioensaio =  ".$row['idbioensaio']." union all ";

}else{
    $sqlin="select
                    concat('B',b.idregistro) as registro,b.*
            from bioensaio b ,bioensaiodes d
            where  b.idbioensaio =  d.idbioensaio
            and d.idbioensaioc =  ".$row['idbioensaio']." union all ";

}

        $sqldes=$sqlin."select
                            concat('B',b.idregistro) as registro,b.*
                        from bioensaiodes d,bioensaio b 
                        where b.idbioensaio = d.idbioensaioc
                        and d.idbioensaio = ".$row['idbioensaio']."
                        union all
                        select
                            concat('B',b.idregistro) as registro,b.*
                        from bioensaiodes d,bioensaio b 
                        where b.idbioensaio = d.idbioensaioc
                        and exists
                        (select 1 from bioensaiodes dd
                        where d.idbioensaio = dd.idbioensaio
                        and dd.idbioensaioc = ".$row['idbioensaio'].")";
			
        //die($sqldes);
        $resdes=d::b()->query($sqldes) or die("Erro ao buscar desenho experimental 1 sql=".$sqldes);
?>		
<b><pre><p align="center" >DOCUMENTO: B<?
	$traco="";
	$virg="";
	while($rowdes=mysqli_fetch_assoc($resdes)){

		if($rowdes['agrupar']=='Y'){//pegar as informações do produto agrupador

		$produto=$rowdes['produto'];	
		$vantigeno= nl2br($rowdes["antigeno"]);
		
		$faselab=$rowdes['faselab'];
		echo($traco.$rowdes['idregistro']);
		}
		
		$registros.=$virg.$rowdes['registro'];
		$idcontrole=$rowdes['idbioensaioctr'];
		$exercicio=$rowdes['exercicio'];
		
		$traco=" - ";
		$inid.=$virg.$rowdes['idbioensaio'];
		$virg=",";
	}?>/<?=$exercicio?></p></pre></b>

<!-- <font>Uberlândia, <?echo date("d-m-Y");?></font> -->
<br><br><br><br><br><br><br><br><br><br><br><br><br>
 <div class="logosup col 65"><img src="../inc/img/Logo PB Inata.jpg"  > </div>
<div class="logosup col 5"><img src="../inc/img/Logo-Laudo-Laboratorio.png"  >  </div>
</div>
<div style="page-break-after: always"></div>
<?
if(empty($inid)){
	die("<font color='red'>E necessário montar o desenho experimental para emissão do relatório</font>");
}

$sqlcap="select * from vwbioensaio where idempresa =".$_SESSION["SESSAO"]["IDEMPRESA"]." and idbioensaio in (".$inid.") ";
$rescap=d::b()->query($sqlcap) or die("Erro ao buscar bioensaios slq=".$sqlcap);
//die($sqlcap);
while($rowcap=mysqli_fetch_assoc($rescap)){
    $sif="select dma(a.datadzero) as inicio,dma(max(s.data)) as fim
            from analise a,bioterioanalise b,servicoensaio s
            where a.idobjeto = ".$rowcap["idbioensaio"]." 
            and a.objeto ='bioensaio'
            and b.idbioterioanalise = a.idbioterioanalise
            and b.cria='N'
            and s.idobjeto=a.idanalise
            and s.tipoobjeto ='analise'";
    $rif=d::b()->query($sif) or die("Erro ao buscar data inicio e fim do bioensaio slq=".$sif);
    $rwif=mysqli_fetch_assoc($rif);
?>
<div class="conteudo">
<div>
<table  CELLSPACING="10" CELLPADDING="0" >
    <tr style="font-size: 14px;">
        <td style="width: 100%; background-color: rgb(225,225,225);" colspan="2" align="left"><b>INFORMAÇÕES DO ESTUDO</b></td>
    </tr>	
    <tr>
        <td>&nbsp;</td>
    </tr>
    <tr>		
        <td class="borderblue">
        <div>
        <table>
            <tr >
                <td style="width: 350px;"  nowrap><font color="#828282">N&ordm; Registro:</font></td>
            </tr>
            <tr> 
                <td><?=$rowcap["idregistro"]?></td>
            </tr>
        </table>
        </div>
        </td>
        <td class="borderblue">
        <div>
        <table>
            <tr >
                <td style="width: 350px;"><font color="#828282">Contratante:</font></td>
            </tr>
            <tr>
                <td><?=$rowcap["nome"]?></td>
            </tr>
        </table>
        </div>
        </td>	
    </tr> 
    <tr>	
        <td class="borderblue">
        <div>
        <table>
            <tr >
                <td style="width: 350px;" nowrap><font color="#828282">Estudo:</font></td>
            </tr>
            <tr>
                <td><?=$rowcap["bioensaio"]?></td>
            </tr>
        </table>
        </div>
        </td>
        <td class="borderblue" >
        <div>
        <table>
            <tr >
                <td style="width: 350px;" ><font color="#828282">Nº Amostras:</font></td>
            </tr>
            <tr>
                <td><?=$rowcap['qtd']?></td>
            </tr>
        </table>
        </div>
        </td> 
    </tr>
    <tr>
        <?if(!empty($rwif["inicio"])){?>
        <td class="borderblue" >
        <div>
        <table>
            <tr >
                <td style="width: 350px;" ><font color="#828282">Data de Início:</font></td>
            </tr>
            <tr>
                <td><?=$rwif["inicio"]?></td>
            </tr>
        </table>
        </div>
        </td>
        <?}else{?><td></td><?}?>
       <?if(!empty($rwif["fim"])){?>
        <td class="borderblue">
        <div>
        <table>
            <tr >
                <td style="width: 350px;" nowrap><font color="#828282">Data de Término:</font></td>
            </tr>
            <tr>
                <td><?=$rwif["fim"]?></td>
            </tr>
        </table>
        </div>
        </td>
        <?}else{?><td></td><?}?>    
    </tr>
    <tr>
        <td></td>
    </tr>
</table>
<table CELLSPACING="10" CELLPADDING="0">
    <tr style="font-size: 14px;">
        <td style="width: 100%; background-color: rgb(225,225,225);" colspan="2" align="left"><b>INFORMAÇÕES DO PRODUTO</b></td>
    </tr>
    <tr>
        <td class="borderblue" >
        <div>
        <table>
            <tr >
                <td style="width: 350px;" ><font color="#828282">Produto:</font></td>
            </tr>
            <tr>
                <td><?=$rowcap["produto"]?> <?=$rowcap["partida"]?></td>
            </tr>
        </table>
        </div>
        </td>
        <td class="borderblue" >
        <div>
        <table>
            <tr >
                <td style="width: 350px;" ><font color="#828282"> Via:</font></td>
            </tr>
            <tr>
                <td><?=$rowcap["via"]?></td>
            </tr>
        </table>
        </div>
        </td>
    </tr>
        <tr>
        <td class="borderblue" >
        <div>
        <table>
            <tr >
                <td style="width: 350px;" ><font color="#828282">Dose:</font></td>
            </tr>
            <tr>
                <td><?=$rowcap["doses"]?></td>
            </tr>
        </table>
        </div>
        </td>
        <td class="borderblue" >
        <div>
        <table>
            <tr >
                <td style="width: 350px;" ><font color="#828282">Volume:</font></td>
            </tr>
            <tr>
                <td><?=$rowcap["volume"]?></td>
            </tr>
        </table>
        </div>
        </td>
    </tr>
</table>
<table  CELLSPACING="10" CELLPADDING="0" >
    <tr style=" font-size: 14px;">
        <td style="width: 100%; background-color: rgb(225,225,225);" colspan="2" align="left"><b>INFORMAÇÕES DOS ANIMAIS INOCULADOS</b></td>
    </tr>	
    <tr>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td class="borderblue" >
        <div>
        <table>
            <tr >
                <td style="width: 350px;" ><font color="#828282">Fornecedor:</font></td>
            </tr>
            <tr>
                <td><?=$rowcap['fabricante']?></td>
            </tr>
        </table>
        </div>
        </td>		
        <td class="borderblue">
        <div>
        <table>
            <tr >
                <td style="width: 350px;"  nowrap><font color="#828282">Partida Interna:</font></td>
            </tr>
            <tr>
                <td><?=$rowcap['partidainterna']?></td>
            </tr>
        </table>
        </div>
        </td>
    </tr>
        <tr>
        <td class="borderblue" >
        <div>
        <table>
            <tr >
                <td style="width: 350px;" ><font color="#828282">Tipo:</font></td>
            </tr>
            <tr>
                <td><?=$rowcap["especie"]?>-<?=$rowcap["finalidade"]?></td>
            </tr>
        </table>
        </div>
        </td>		
        <td class="borderblue">
        <div>
        <table>
            <tr >
                <td style="width: 350px;" nowrap><font color="#828282">Partida Fornecedor:</font></td>
            </tr>
            <tr>
                <td><?=$rowcap["partidaext"]?></td>
            </tr>
        </table>
        </div>
        </td>
    </tr>    
    <tr>
        <td class="borderblue" >
        <div>
        <table>
            <tr >
                <td style="width: 350px;" ><font color="#828282">Nascimento:</font></td>
            </tr>
            <tr>
                <td><?=dma($rowcap['nascimento'])?></td>
            </tr>
        </table>
        </div>
        </td>		
        <td class="borderblue">
        <div>
        <table>
            <tr >
                <td style="width: 350px;" nowrap><font color="#828282">Alojamento:</font></td>
            </tr>
            <tr>
                 <td><?=dma($rowcap['alojamento'])?></td>
            </tr>
        </table>
        </div>
        </td>
    </tr>
    <tr>
        <td></td>
    </tr>	
    <tr>
        <td class="borderblue" >
        <div>
        <table>
            <tr>
                <td  style="width: 350px;"><font color="#828282">Anilha:</td>
            </tr>
            <tr>
                <td><?=$rowcap["coranilha"]?></td>
            </tr>
        </table>
        </div>
        </td>		
        <td class="borderblue">
        <div>
        <table>
            <tr >
                <td style="width: 350px;" nowrap><font color="#828282">Box - Gaiola</font></td>
            </tr>
            <tr>
                <td><?=$rowcap["rot"]?> - <?=$rowcap["gaiola"]?></td>
            </tr>
        </table>
        </div>
        </td>
    </tr>
</table>
</div>
</div>
<?
//##### INFORMAÇÕES DO CONTROLE ########
$sqlcapx="select * from vwbioensaio e join analise a 
        where e.idempresa =".$_SESSION["SESSAO"]["IDEMPRESA"]."
          and a.idobjeto=".$rowcap["idbioensaio"]."
            and a.objeto ='bioensaio'
        and e.idbioensaio =a.idbioensaioctr";
$rescapx=d::b()->query($sqlcapx) or die("Erro ao buscar bioensaios do controle slq=".$sqlcapx);
while($rowcapx=mysqli_fetch_assoc($rescapx)){
?>

<div class="conteudo">
<div>
<table  CELLSPACING="10" CELLPADDING="0" >
    <tr style="font-size: 14px;">
        <td style="width: 100%; background-color: rgb(225,225,225);" colspan="2" align="left"><b>INFORMAÇÕES DO CONTROLE DO ESTUDO</b></td>
    </tr>
    <tr>		
        <td class="borderblue">
        <div>
        <table>
            <tr >
                <td style="width: 350px;"  nowrap><font color="#828282">N&ordm; Registro:</font></td>
            </tr>
            <tr> 
                <td><?=$rowcapx["idregistro"]?></td>
            </tr>
        </table>
        </div>
        </td>
        <td class="borderblue">
        <div>
        <table>
            <tr >
                <td style="width: 350px;"><font color="#828282">Contratante:</font></td>
            </tr>
            <tr>
                <td><?=$rowcapx["nome"]?></td>
            </tr>
        </table>
        </div>
        </td>	
    </tr>
    <tr>	
        <td class="borderblue">
        <div>
        <table>
            <tr >
                <td style="width: 350px;" nowrap><font color="#828282">Estudo:</font></td>
            </tr>
            <tr>
                <td>CONTROLE <?=$rowcap["bioensaio"]?></td>
            </tr>
        </table>
        </div>
        </td>
 
        <td class="borderblue" >
        <div>
        <table>
            <tr >
                <td style="width: 350px;" ><font color="#828282">Nº Amostras:</font></td>
            </tr>
            <tr>
                <td><?=$rowcapx['qtd']?></td>
            </tr>
        </table>
        </div>
        </td>
    </tr>
    <tr>
        <?if(!empty($rwif["inicio"])){?>
        <td class="borderblue" >
        <div>
        <table>
            <tr >
                <td style="width: 350px;" ><font color="#828282">Data de Início:</font></td>
            </tr>
            <tr>
                <td><?=$rwif["inicio"]?></td>
            </tr>
        </table>
        </div>
        </td>
        <?}else{?><td></td><?}?>
       <?if(!empty($rwif["fim"])){?>
        <td class="borderblue">
        <div>
        <table>
            <tr >
                <td style="width: 350px;" nowrap><font color="#828282">Data de Término:</font></td>
            </tr>
            <tr>
                <td><?=$rwif["fim"]?></td>
            </tr>
        </table>
        </div>
        </td>
        <?}else{?><td></td><?}?>    
    </tr>
    <tr>
        <td></td>
    </tr>
</table>
<table  CELLSPACING="10" CELLPADDING="0" >
    <tr style=" font-size: 14px;">
        <td style="width: 100%; background-color: rgb(225,225,225);" colspan="2" align="left"><b>INFORMAÇÕES DOS ANIMAIS TESTEMUNHA</b></td>
    </tr>	
  <tr>
        <td class="borderblue" >
        <div>
        <table>
            <tr >
                <td style="width: 350px;" ><font color="#828282">Fornecedor:</font></td>
            </tr>
            <tr>
                <td><?=$rowcapx['fabricante']?></td>
            </tr>
        </table>
        </div>
        </td>		
        <td class="borderblue">
        <div>
        <table>
            <tr >
                <td style="width: 350px;"  nowrap><font color="#828282">Partida Interna:</font></td>
            </tr>
            <tr>
                <td><?=$rowcapx['partidainterna']?></td>
            </tr>
        </table>
        </div>
        </td>
    </tr>
        <tr>
        <td class="borderblue" >
        <div>
        <table>
            <tr >
                <td style="width: 350px;" ><font color="#828282">Tipo:</font></td>
            </tr>
            <tr>
                <td><?=$rowcapx["especie"]?>-<?=$rowcapx["finalidade"]?></td>
            </tr>
        </table>
        </div>
        </td>		
        <td class="borderblue">
        <div>
        <table>
            <tr >
                <td style="width: 350px;" nowrap><font color="#828282">Partida Fornecedor:</font></td>
            </tr>
            <tr>
                <td><?=$rowcapx["partidaext"]?></td>
            </tr>
        </table>
        </div>
        </td>
    </tr>    
    <tr>
        <td class="borderblue" >
        <div>
        <table>
            <tr >
                <td style="width: 350px;" ><font color="#828282">Nascimento:</font></td>
            </tr>
            <tr>
                <td><?=dma($rowcapx['nascimento'])?></td>
            </tr>
        </table>
        </div>
        </td>		
        <td class="borderblue">
        <div>
        <table>
            <tr >
                <td style="width: 350px;" nowrap><font color="#828282">Alojamento:</font></td>
            </tr>
            <tr>
                 <td><?=dma($rowcapx['alojamento'])?></td>
            </tr>
        </table>
        </div>
        </td>
    </tr>
    <tr>
        <td class="borderblue" >
        <div>
        <table>
            <tr>
                <td  style="width: 350px;"><font color="#828282">Anilha:</td>
            </tr>
            <tr>
                <td><?=$rowcapx["coranilha"]?></td>
            </tr>
        </table>
        </div>
        </td>		
        <td class="borderblue">
        <div>
        <table>
            <tr >
                <td style="width: 350px;" nowrap><font color="#828282">Box - Gaiola</font></td>
            </tr>
            <tr>
                <td><?=$rowcapx["rot"]?> - <?=$rowcapx["gaiola"]?></td>
            </tr>
        </table>
        </div>
        </td>
    </tr>
</table>
</div>
</div>
<div style="page-break-after: always"></div>
<?}?>
<?
}

    //certificado
    $sqld="select d.titulo,a.idsgdoc,a.idbioensaiosgdoc,a.versao,a.revisao,d.conteudo
                from sgdoc d,bioensaiosgdoc a 
                where d.idsgdoc = a.idsgdoc
                and a.idbioensaio= ".$idbioensaio." order by d.titulo";
    $resd=d::b()->query($sqld) or die("Erro ao buscar certificado sql=".$sqld);
    $qtdcert= mysqli_num_rows($resd);
    if($qtdcert>0){
        $rowcert=mysqli_fetch_assoc($resd);
         

?>

		<?=$rowcert["conteudo"]?>

<div style="page-break-after: always"></div>
<?
    }// if($qtdcert>0){
?>
<table style="font-size: 20px;">
    <tr><td><br><b><div class="divcirculo">2.0</div> PROTOCOLO EXPERIMENTAL</b><p></td></tr>	
</table>
<?
 $sqlan="select b.idbioensaio, GROUP_CONCAT(ba.texto SEPARATOR '<br>') as texto
            from bioensaio b 
            left join analise a on(a.objeto='bioensaio' and a.idobjeto = b.idbioensaio)
            join bioterioanalise ba on(ba.idbioterioanalise=a.idbioterioanalise)
            where b.idbioensaio = ".$idbioensaio;


//die($sqli);
    $resan=d::b()->query($sqlan) or die("Erro ao buscar protocolo sql=".$sqlan);
	$rowan=mysqli_fetch_assoc($resan);
	?>
 <table style="font-size: 20px;">
		<tr >
		<td>
			<div class="row">
			<div class="col 100 quebralinha" style="line-height: 150% !important;"><?=$rowan["texto"]?></div>
		</div>
		</td>
		</tr>
</table>	

    <table style="font-size: 20px;">
            <tr ><td><b><div class="divcirculo">2.1</div> PRODUTO/FORMULAÇÃO EXPERIMENTAL</b></td></tr>
    </table>	
            <p>
    <table ><!-- Cabecalho Superior -->
    <tr>
        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
        <td >
        <table class="tbgr"  style="font-size: 14px;" >			
        <!--<tr><td class="grcab" colspan="2" align="center" >PRODUTO/FORMULAÇÃO EXPERIMENTAL</td></tr> -->
        <tr >
            <td class="grrot" style="text-align:center;" >PRODUTO / FORMULAÇÃO</td>
            <td class="grrot" style="text-align:center;">ANTÍGENOS</td>
        </tr>
        <tr>
            <td class="grval"><?=$produto?></td>	

            <td class="grval"><?=$vantigeno?></td>
        </tr>		
        </table>
        </td>
    </tr>
    </table>
    <p>

<?
if($row['agrupar']=='Y'){
	$sqlin="select
			0 as idbioensaiodes,b.idbioensaio,b.qtd,concat('B',b.idregistro) as registro,b.idbioensaio as idbioensaioc,n.nucleo as formulacao,b.volume,b.via,b.doses,b.idbioensaioctr,lc.gaiola,CONCAT(lo.tipo, ' ', RIGHT(lo.local, 2)) AS rot
		from bioensaio b join nucleo n
                    LEFT JOIN localensaio lc ON (b.idbioensaio = lc.idbioensaio)
                    LEFT JOIN local lo ON (lc.idlocal = lo.idlocal)
		where b.idbioensaio =  ".$row['idbioensaio']."  and n.idnucleo = b.idnucleo union all ";

}else{
	$sqlin="select
			0 as idbioensaiodes,b.idbioensaio,b.qtd,concat('B',b.idregistro) as registro,b.idbioensaio as idbioensaioc,n.nucleo as formulacao,b.volume,b.via,b.doses,b.idbioensaioctr,lc.gaiola,CONCAT(lo.tipo, ' ', RIGHT(lo.local, 2)) AS rot
		from bioensaio b join bioensaiodes d join nucleo n
		where b.idbioensaio =  d.idbioensaio
                    LEFT JOIN localensaio lc ON (b.idbioensaio = lc.idbioensaio)
                    LEFT JOIN local lo ON (lc.idlocal = lo.idlocal)
                 and n.idnucleo = b.idnucleo
		and d.idbioensaioc =  ".$row['idbioensaio']." union all ";
}

    $sqldes=$sqlin."select
                                    d.idbioensaiodes,b.idbioensaio,b.qtd,concat('B',b.idregistro) as registro,d.idbioensaioc,n.nucleo as formulacao,b.volume,b.via,b.doses,b.idbioensaioctr,lc.gaiola,CONCAT(lo.tipo, ' ', RIGHT(lo.local, 2)) AS rot
                            from bioensaiodes d join bioensaio b join nucleo n
                                LEFT JOIN localensaio lc ON (b.idbioensaio = lc.idbioensaio)
                                LEFT JOIN local lo ON (lc.idlocal = lo.idlocal)
                            where b.idbioensaio = d.idbioensaioc
                            and n.idnucleo = b.idnucleo
                            and d.idbioensaio = ".$row['idbioensaio']."
                            union all
                            select
                                    d.idbioensaiodes,b.idbioensaio,b.qtd,concat('B',b.idregistro) as registro,d.idbioensaioc,n.nucleo as formulacao,b.volume,b.via,b.doses,b.idbioensaioctr,lc.gaiola,CONCAT(lo.tipo, ' ', RIGHT(lo.local, 2)) AS rot
                            from bioensaiodes d join bioensaio b join nucleo n
                                LEFT JOIN localensaio lc ON (b.idbioensaio = lc.idbioensaio)
                                LEFT JOIN local lo ON (lc.idlocal = lo.idlocal)
                            where b.idbioensaio = d.idbioensaioc
                             and n.idnucleo = b.idnucleo
                            and exists
                            (select 1 from bioensaiodes dd
                            where d.idbioensaio = dd.idbioensaio
                            and dd.idbioensaioc = ".$row['idbioensaio'].")";
    //die($sqldes);
    $resdes=d::b()->query($sqldes) or die("Erro ao buscar desenho experimental 2 sql=".$sqldes);
    
?>		
    <table style="font-size: 20px;">
            <tr><td><b><div class="divcirculo">2.2</div> DESENHO EXPERIMENTAL</b></td></tr>
    </table>
            <p>	
    <table><!-- Cabecalho Superior -->
    <tr>
        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
        <td >
        <table class="tbgr" style="font-size: 14px;">			
        <!--<tr><td class="grcab" colspan="5" align="center" >DESENHO EXPERIMENTAL</td></tr> -->
        <tr>
            <td class="grrot" style="text-align:center;">Grupo</td>
            <td class="grrot" style="text-align:center;">Estudo / Partida</td>
            <td class="grrot" style="text-align:center;">Dose</td>
            <td class="grrot" style="text-align:center;">Administração</td>
            <td class="grrot" style="text-align:center;">Volume</td>
            <td class="grrot" style="text-align:center;">Número de Animais</td>
            <td class="grrot" style="text-align:center;">Local</td>
        </tr>
<?
        $l=0;
        $virg="";
        while($rowdes=mysqli_fetch_assoc($resdes)){
            $l=$l+1;

            if(!empty($rowdes['idbioensaio'])){
                    $strcrts=$strcrts.$virg."".$rowdes['idbioensaio'];
                    $virg=',';
            }
?>			
        <tr>
            <td class="grval"><?=$l?></td>	
            <td class="grval"><?=$rowdes['formulacao']?></td>	
            <td class="grval"><?=$rowdes['doses']?></td>
            <td class="grval"> <?=$rowdes['via']?></td>
            <td class="grval"><?=$rowdes['volume']?></td>	
            <td class="grval"><?=$rowdes['qtd']?></td>	
            <td class="grval"><?=$rowdes["rot"]?> - <?=$rowdes["gaiola"]?></td>
        </tr>	
<?
        }

				
            $sqldes="select n.nucleo,e.* from vwbioensaio e join bioensaio et join analise a join nucleo n
                        where e.idempresa =".$_SESSION["SESSAO"]["IDEMPRESA"]."
                        and et.idnucleo = n.idnucleo
                        and et.idbioensaio = a.idobjeto
                        and a.idobjeto in (".$inid.")
                        and a.objeto ='bioensaio'
                        and e.idbioensaio =a.idbioensaioctr";
   
            $resdes=d::b()->query($sqldes) or die("Erro ao buscar desenho experimental do controle sql=".$sqldes);
            while($rowdes=mysqli_fetch_assoc($resdes)){
                $l=$l+1;
?>
        <tr>
                <td class="grval"><?=$l?></td>	
                <td class="grval">CONTROLE <?=$rowdes['nucleo']?></td>
                <td class="grval"><?=$rowdes['doses']?></td>
                <td class="grval"><?=$rowdes['via']?></td>
                <td class="grval"><?=$rowdes['volume']?></td>	
                <td class="grval"><?=$rowdes['qtd']?></td>
                <td class="grval"><?=$rowdes["rot"]?> - <?=$rowdes["gaiola"]?></td>
        </tr>	
<?				
            }	
?>				
        </table>
        </td>
    </tr>
    </table>
        <p>

    <table style="font-size: 18px;">
            <tr><td><b><div class="divcirculo">2.3</div> RESUMO EXPERIMENTAL</b></td></tr>
    </table>
<?
    $sqli="select n.nucleo,b.idregistro,b.idbioensaio,aa.idanalise,aa.idobjeto,ec.idregistro as idregistroctr,b.recebidopor
            from bioensaio b join nucleo n 
            left join analise a on(a.idbioensaioctr is not null and a.idbioensaioctr!=0 and a.idobjeto = b.idbioensaio) 
            left join analise aa on (aa.idanalisepai = a.idanalise)
            left join bioensaio ec on(a.idbioensaioctr = ec.idbioensaio)
            where b.idbioensaio in (".$inid.")
            and b.idnucleo = n.idnucleo";


//die($sqli);
    $resi=d::b()->query($sqli) or die("Erro ao buscar incubação sql=".$sqli);

    $e=0;
    while($rowi=mysqli_fetch_assoc($resi)){
       
        $sqls="select 
                dma(s.data) as dmadata,
                s.data,
                sb.rotulo,
                DATEDIFF(s.data,e.nascimento) AS idade,
                s.dia,
                concat('B',e.idregistro,'/',e.exercicio) as registro
                ,n.nucleo as bioensaio,
                ba.cria
            from bioensaio e join servicoensaio s join servicobioterio sb join analise a join bioterioanalise ba
                left join analise aa on(aa.idanalise =a.idanalisepai) 
               left join nucleo n on(n.idnucleo=e.idnucleo)
            where s.idobjeto=a.idanalise
            and sb.idservicobioterio = s.idservicobioterio 
            and s.tipoobjeto = 'analise'
            and a.idbioterioanalise = ba.idbioterioanalise
            and a.objeto='bioensaio'
            and a.idobjeto =  e.idbioensaio
            and e.idbioensaio = ".$rowi['idbioensaio']."
            order by s.data,sb.ordem";
        $ress=d::b()->query($sqls) or die("Erro ao buscar Serviços sql=".$sqls);
			
        //echo($sqls); 
        $qtds=mysqli_num_rows($ress);
        if($qtds>0){
?>	
		<table ><!-- Cabecalho Superior -->
		<tr>
			<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
			<td >
			<table class="tbgr"  style="font-size: 14px;">			
 			<tr><td class="grcab" colspan="6" align="center" >
                            <?=$rowi['nucleo']?> 
 			</td></tr> 
			<tr>				
				<td class="grval" style="text-align:left;" >B<?=$rowi['idregistro']?></td>
				<td class="grrot" style="text-align:center;" >Data</td>
				<td class="grrot" style="text-align:center;" >Idade</td>
				<td class="grrot" style="text-align:center;">Procedimento</td>							
				<td class="grrot" style="text-align:center;">Responsável</td>
			</tr>				
			
			
<?		
			while($rows=mysqli_fetch_assoc($ress)){	
?>		
                        <tr>
                            <td class="grval" colspan="2"><?=$rows["dmadata"]?></td>
                            <td class="grval" ><?=$rows["idade"]?></td>
                            <td class="grval"><?=$rows['rotulo']?><?if($rows["cria"]=='N'){?> - D<?=$rows["dia"]?><?}?></td>								
                            <td class="grval"><?=$rowi['recebidopor']?></td>	
			</tr>		
<?
			}
?>
			</table>
			</td>
		</tr>
		</table>
		<p>
<?		
        }
        //se tem controle
        if(!empty($rowi["idanalise"])){
            $sqls="select 
                                dma(s.data) as dmadata,
                                s.data,
                                sb.rotulo,
                                DATEDIFF(s.data,e.nascimento) AS idade,
                                s.dia,
                                concat('B',e.idregistro,'/',e.exercicio) as registro
                                ,n.nucleo as bioensaio,
                                ba.cria
                            from bioensaio e join servicoensaio s join servicobioterio sb join analise a join bioterioanalise ba
                                left join analise aa on(aa.idanalise =a.idanalisepai) 
                               left join nucleo n on(n.idnucleo=e.idnucleo)
                            where s.idobjeto=a.idanalise
                            and sb.idservicobioterio = s.idservicobioterio 
                            and s.tipoobjeto = 'analise'
                            and a.idbioterioanalise = ba.idbioterioanalise
                            and a.objeto='bioensaio'
                            and a.idobjeto =  e.idbioensaio                            
                            and (a.idanalise = ".$rowi["idanalise"]." or ba.cria='Y')
                            and e.idbioensaio = ".$rowi["idobjeto"]."
                            order by s.data,sb.ordem";
            $ress=d::b()->query($sqls) or die("Erro ao buscar Serviços sql=".$sqls);		
            //echo($sqls); 
            $qtds=mysqli_num_rows($ress);
            if($qtds>0){
?>	
		<table ><!-- Cabecalho Superior -->
		<tr>
			<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
			<td >
			<table class="tbgr"  style="font-size: 14px;">			
 			<tr><td class="grcab" colspan="6" align="center" >
                            CONTROLE <?=$rowi['nucleo']?> 
 			</td></tr> 
			<tr>				
				<td class="grval" style="text-align:left;" >B<?=$rowi['idregistroctr']?></td>
				<td class="grrot" style="text-align:center;" >Data</td>
				<td class="grrot" style="text-align:center;" >Idade</td>
				<td class="grrot" style="text-align:center;">Procedimento</td>							
				<td class="grrot" style="text-align:center;">Responsável</td>
			</tr>				
			
			
<?		
			while($rows=mysqli_fetch_assoc($ress)){	
?>		
                        <tr>
                            <td class="grval" colspan="2"><?=$rows["dmadata"]?></td>
                            <td class="grval" ><?=$rows["idade"]?></td>
                            <td class="grval"><?=$rows['rotulo']?><?if($rows["cria"]=='N'){?> - D<?=$rows["dia"]?><?}?></td>								
                            <td class="grval"><?=$rowi['recebidopor']?></td>	
			</tr>		
<?
			}
?>
			</table>
			</td>
		</tr>
		</table>
		<p>
<?		
            }// serviços do controle

        }//if(!empty($rowi["idanalise"])){  
        
    }//while($rowi=mysqli_fetch_assoc($resi)){
	
	/*retirado ate funcionar
?>					
		
		<div style="page-break-after: always"></div>
		<table style="font-size: 12px;">
			<tr><td><b><div class="divcirculo">8.0</div> RESULTADOS</b></td></tr>
		</table>
<?
		$sql="select a.idregistro as idregistroorig,a.exercicio,a.idpessoa
				from servicoensaio s,amostra a 
				where s.idbioensaio  in (".$inid.") 
				and s.idempresa =".$_SESSION["SESSAO"]["IDEMPRESA"]." 
				and s.idamostra =a.idamostra order by a.idregistro";
		$res=d::b()->query($sql) or die("Erro ao buscar registros slq=".$sql);
		
		//die($sql);
		while($row=mysqli_fetch_assoc($res)){

?>		
	<script type="text/javascript">
	carregaresultados(<?=$row['idregistroorig']?>,<?=$row['exercicio']?>,<?=$row['idpessoa']?>);
	</script>				
	<div id="conteudo<?=$row['idregistroorig']?>" style="display:table-layout;   height: 100%; width: 100%;">
	<!-- conteudo aqui aparece a emissaoresultado.php -->
	</div>
	<?
		}
		*/
	?>
		       
<?
die();
	$sqlt="select p.codprodserv,b.bioensaio,b.antigeno,b.partida,p.descr,r.quantidade,p.textoinclusaores,r.status,r.idamostra,r.idresultado,s.dia,if(s.dia is null,sb.rotulo,concat(sb.rotulo,' D',s.dia)) as rotulo,r.status,r.descritivo
				from bioensaio b, servicoensaio s,resultado r,prodserv p,servicobioterio sb
				where sb.servico = s.servico
				and p.idprodserv  = r.idtipoteste
				and p.idprodserv in (2075,2259,2248,2247,1941,2411,2999,3000,3001)
				and r.status !='OFFLINE'
				-- and r.idamostra = b.idamostra
				and r.idservicoensaio=s.idservicoensaio
                                and s.tipoobjeto = 'bioensaio'
				and s.idobjeto =b.idbioensaio
				and b.idbioensaio in (".$inid.")";
	$rest=d::b()->query($sqlt) or die("Erro ao buscar os testes sql=".$sqlt);
	$qtdt=mysqli_num_rows($rest);

?>
<?
		if($qtdt>0){

			while($rowt=mysqli_fetch_assoc($rest)){
?>			
    
         <div class="quebrapagina"></div>
         <pagina>
        <header class="row margem0.0">
		<div class="logosup col 5"><img src="../inc/img/logoHeaderInata.jpg"></div>
		<div class="titulodoc"><?=$rowt['descr']?></div>
		
	</header>
        <div class="row">
            <div class="col 10 rot">Reg.:</div>
            <div class="col 15 val"><?=$row["idregistro"]?></div>
            <div class="col 15 rot">Quantidade</div>
            <div class="col 20 val"><?=$rowt['quantidade']?></div>
            <div class="col 15 rot">Serviço:</div>
            <div class="col 20 val"><?=$rowt['rotulo']?></div>
        </div>
	<div class="row">
	    <div class="col 10 rot">Descrição:</div>
	    <div class="col 85 val quebralinha"><?=$row["obs"]?></div>
	</div>
         
         <br>
         <br>
        <?=$rowt['textoinclusaores']?>
       
        </pagina>
			
<?
			}	
		
		}
?>

		<p>
		<p>
		<p>
<?
}else{//if($_GET){
?>
<fieldset style="border: none; border-top: 2px solid silver;">
<table>
    <tr>
        <td>Favor preencher os campos da pesquisa para a consulta</td>
    </tr>
</table>
</fieldset>			
<?		
}//if($_GET){
?>
</body>
</html>