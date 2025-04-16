<?
require_once("../inc/php/validaacesso.php");

$idbioensaio = $_GET['idbioensaio'];

if(!empty($idbioensaio)){
    $clausulam .=" and idbioensaio =".$idbioensaio;
}

$sql="select * from vwbioensaio where 1 ".getidempresa("idempresa",$_GET["_modulo"])." ".$clausulam;

echo "<!--";
echo $sql;
echo "-->";
function listaresultado($idbio){
        $sqla="select * from analise where objeto='bioensaio' and idobjeto=".$idbio;
        $resa = d::b()->query($sqla) or die("Erro ao buscar descricao do lote produto=".$sqla);
        while($infoana = mysqli_fetch_assoc($resa)){?>
                <div style="border-color: black;">
                    <table cellspacing='0' border="1" style="border-top: 0;border-bottom: 0;border-left: 0;border-right: 0;width: 100%;border-color: black;">
                    <tr>
                        <?
                        $protocolo=traduzid('bioterioanalise','idbioterioanalise','tipoanalise',$infoana['idbioterioanalise']); 
                        ?>
                        <th colspan="2"  style="border-right: 0;">Protocolo:<?=$protocolo?></th>
                        <th colspan="2"  style="border-left: 0;">QTD:<?=$infoana['qtd']?></th>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;border-top: 0;border-right: 0;width: 100%;border-color: black;">Serviço:</td>
                        <td style="font-weight: bold;border-top: 0;border-right: 0;width: 100%;border-color: black;">Data:</td>
                        <td style="font-weight: bold;border-top: 0;border-right: 0;width: 100%;border-color: black;">OBS.:</td>
                        <td style="font-weight: bold;border-top: 0;width: 100%;border-color: black;">Responsável:</td>
                    </tr>
                    <?
                    $sq="select s.idservicoensaio,
                            a.idanalise,
                            s.dia,dma(s.data) as dmadata,s.diazero,s.status,sb.rotulo,
                            DATEDIFF(s.data,e.nascimento) AS idade,
                            DATEDIFF(s.data,a.datadzero) as diabioensaio,
                            s.idamostra,ee.idbioensaio,
                            sb.idservicobioterio,
                            ba.cria
                            from bioensaio e join servicoensaio s join servicobioterio sb join analise a join bioterioanalise ba
                            left join analise aa on(aa.idanalise =a.idanalisepai) 
                            left join bioensaio ee on(ee.idbioensaio=aa.idobjeto and aa.objeto ='bioensaio')
                            left join nucleo n on(n.idnucleo=ee.idnucleo)
                            where s.idobjeto=a.idanalise
                            and sb.idservicobioterio = s.idservicobioterio 
                            and s.tipoobjeto = 'analise'
                            and a.idbioterioanalise = ba.idbioterioanalise
                            and a.objeto='bioensaio'
                            and a.idobjeto =  e.idbioensaio
                            and a.idanalise =".$infoana["idanalise"]."
                            order by s.data,sb.rotulo desc";
                        echo "<!-- " . $sq . "  -->";
                        $ress=d::b()->query($sq) or die("Erro ao buscar serviço da analise sql=".$sq);
                        
                        if (mysqli_num_rows($ress) > 0) {
                            while($rows=mysqli_fetch_assoc($ress)){?>
                                <tr>
                                    <td style="border-top: 0;border-right: 0;width: 100%;border-color: black;"><?=$rows['rotulo']?></td>
                                    <td style="border-top: 0;border-right: 0;width: 100%;border-color: black;"><?=$rows['dmadata']?></td>
                                    <td style="border-top: 0;border-right: 0;width: 100%;border-color: black;">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    </td>
                                    <td style="border-top: 0;width: 100%;border-color: black;"></td>
                                </tr>     
                            <?}?>
                        <?}?>
                    </table>
                    <br>
                </div>

            <?}
}
function listateste($inidservicoensaio){  

    $sqlt = "select s.idservicoensaio
                ,s.dia
                ,p.sigla
                ,p.tipoteste
                ,r.quantidade
                ,r.status
                ,r.idamostra
                ,r.idresultado
                ,r.idservicoensaio
                ,r.ord
                ,r.conformidade
                ,r.idtipoteste
                ,s.dia
                ,if(s.dia is null,sb.rotulo,concat(sb.rotulo,' D',s.dia)) as rotulo,
                left(dma(s.data),5) as dataserv
            from resultado r,vwtipoteste p,servicobioterio sb,servicoensaio s
           
            where sb.idservicobioterio = s.idservicobioterio
            and p.idtipoteste  = r.idtipoteste 
            and r.status !='OFFLINE'
            and r.idservicoensaio=s.idservicoensaio
            and s.idservicoensaio=".$inidservicoensaio." order by s.data";

    //$i=9999;
    $rest = d::b()->query($sqlt)or die("Erro ao recuperar resultados: \n".mysqli_error(d::b())."\n".$sqlt);
    $qtdres=mysqli_num_rows($rest);	
    if($qtdres>0){

        while($r=mysqli_fetch_assoc($rest)){
            /*if($r['status']=='FECHADO'){
                $cor="#B0E2FF";
            }elseif($r['status']=='ASSINADO'){
                $cor="#00ff004d";                
            }else{
                $cor="#c4c5b47a";     
            }*/
            if ($r['status']=='ASSINADO') {?>
            <table>
                <tr  style="width: 100%;" idresultado="<?=$r["idresultado"]?>">
                    <td align="Center" style="width: 100%;">
                            <?=$r["sigla"]?>-<?=$r["rotulo"]?> (<?=$r["conformidade"]?>)
                    </td>
                </tr>
            </table>
    
<?}
			//$i++;
        }//while($r=  mysqli_fetch_assoc($rest)){
    }//if($qtdres>0){
?>                

<?
}//function listateste(){
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
.borderbluedta div{
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
width: 240px;
max-width: 700px;
}
.borderbluedta td{
/*min-width: 100px;*/
max-width: 700px;
}
.borderblue table{
font-size: 14px;
}
.borderbluedta table{
font-size: 14px;
}
.trblue font{
 color:black;
}

.divcirculo{
background:#e0e0de;
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
.servico{      
        border: none;
        /* min-width:170px;
        min-height: 40px; */
         *background-color :#FF7F50;  
        float:left;  
        border-radius: 10px;    
        margin:2px;  
        display:flex;
        justify-content: center;
       /* align-items: center;*/
        vertical-align: top;
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
<style type="text/css">
    table { page-break-inside:auto }
    tr    { page-break-inside:avoid; page-break-after:auto }
    thead { display:table-header-group }
    tfoot { display:table-footer-group }
    body {
    -webkit-print-color-adjust: exact;
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
Resumo de Estudo Experimental

com Animais

</p></pre></b>
<br><br>
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
<br><br><br><br><br><br><br><br><br><br><br><br>
 <div class="logosup col 65"><img src="../inc/img/Logo PB Inata.jpg"  > </div>
<div class="logosup col 5"><img src="../inc/img/Logo-Laudo-Laboratorio.png"  >  </div>
</div>
<div style="page-break-after: always"></div>
<?
if(empty($inid)){
	die("<font color='red'>E necessário montar o desenho experimental para emissão do relatório</font>");
}


$sqlcap="select * from vwbioensaio where 1 ".getidempresa("idempresa",$_GET["_modulo"])." and idbioensaio in (".$inid.") ";
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
    $sql = "select * from analise where idbioensaioctr =".$rowcap['idbioensaio']." group by idobjeto";
    $resl = d::b()->query($sql) or die("Erro ao buscar numero de bioensaios slq=".$sql);
    $qtdl = mysqli_num_rows($resl);
?>

<table CELLSPACING="10" CELLPADDING="0">
    <tr style="font-size: 14px;">
        <td style="width: 100%; background-color: rgb(225,225,225);" colspan="2" align="left"><b><div class="divcirculo">1.0</div>&nbsp;&nbsp;INFORMAÇÕES DO PRODUTO</b></td>
    </tr>
    <tr>
        <td class="borderblue" >
        <div>
        <table>
            <tr >
                <td style="width: 350px;" ><font color="#828282">Produto:</font></td>
            </tr>
            <tr>
                <td><?=$rowcap["produto"]?></td>
            </tr>
        </table>
        </div>
        </td>
        <td class="borderblue" >
        <div>
        <table>
            <tr >
                <td style="width: 350px;" ><font color="#828282"> Lote:</font></td>
            </tr>
            <tr>
                <td><?=traduzid('lote','idlote','partidaext',$rowcap['idlotepd'])?></td>
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
                <td style="width: 350px;" ><font color="#828282">Vencimento:</font></td>
            </tr>
            <tr>
                <td><?=dma($rowcap["vencimento"])?></td>
            </tr>
        </table>
        </div>
        </td>
        <td class="borderblue" >
        <div>
        <table>
            <tr >
                <td style="width: 350px;" ><font color="#828282">Partida Interna:</font></td>
            </tr>
            <tr>
                <td><?=$rowcap["partidainternaprod"]?></td>
            </tr>
        </table>
        </div>
        </td>
    </tr>
</table>
<table  CELLSPACING="10" CELLPADDING="0" style="width: 100%;">
    <tr style="font-size: 14px;">
        <td style="width: 100%; background-color: rgb(225,225,225);" colspan="2" align="left"><b><div class="divcirculo">2.0</div>&nbsp;&nbsp;INFORMAÇÕES DO ESTUDO</b></td>
    </tr>	
	<td colspan="2" class="borderblue" >
        <div style="width: 700px;">
        <table>
            <tr >
                <td><font color="#828282">Contratante:</font></td>
            </tr>
            <tr>
                <td><?=$rowcap["nome"]?></td>
            </tr>
        </table>
        </div>
        </td>
        <tr>
        <td colspan="1" class="borderblue">
            <div>
                <table style="width: 100%;align-content: center;background-color: #c0c0c0;border-top-left-radius: 10px;border-top-right-radius: 10px;" >
                    <tr>
                        <th>Estudo: <?=$rowcap['idregistro']?></th>
                    </tr>
                </table>
                <table>
                    <tr>
                        <td><font color="#828282">Data de Início: </font><?=$rwif["inicio"]?></td>
                        <td><font color="#828282">Data de Término:  </font><?=$rwif["fim"]?></td>
                    </tr>
                    <tr>
                        <td><font color="#828282">Nº de Animais: </font><?=$rowcap['qtd']?></td>
                        <td><font color="#828282">Nº Lote: </font><?=$rowcap['idlote']?></td>
                    </tr>
                </table>
            </div>
        </td>
        <?if ($qtdl > 0) {
            $sql = "select * from analise where idbioensaioctr =".$rowcap['idbioensaio']." group by idobjeto";
            $i = 0;
            $resl = d::b()->query($sql) or die("Erro ao buscar numero de bioensaios slq=".$sql);
            while( $rwl = mysqli_fetch_assoc($resl)){
                $sqlb = "select * from bioensaio where idbioensaio =".$rwl['idobjeto'];
                $resb = d::b()->query($sqlb) or die("Erro ao buscar numero de bioensaios slq*=".$sqlb);
                while( $rwb = mysqli_fetch_assoc($resb)){
                $sif1="select dma(a.datadzero) as inicio,dma(max(s.data)) as fim
                    from analise a,bioterioanalise b,servicoensaio s
                    where a.idobjeto = ".$rwb["idbioensaio"]." 
                    and a.objeto ='bioensaio'
                    and b.idbioterioanalise = a.idbioterioanalise
                    and b.cria='N'
                    and s.idobjeto=a.idanalise
                    and s.tipoobjeto ='analise'";
                $rif1=d::b()->query($sif1) or die("Erro ao buscar data inicio e fim do bioensaio slq=".$sif1);
                $rwif1=mysqli_fetch_assoc($rif1);
                $i++;
                ?>
                
                <?if ($i == 2) {?>
                <tr>
                <?$i = 0;
                }?>
                    <td colspan="1" class="borderblue">
                        <div style="width: 344px;">
                            <table style="width: 100%;align-content: center;background-color: #c0c0c0;border-top-left-radius: 10px;border-top-right-radius: 10px;" >
                                <tr>
                                    <th>Estudo: B<?=$rwb['idregistro']?></th>
                                </tr>
                            </table>
                            <table>
                                <tr>
                                    <td><font color="#828282">Data de Início: </font><?=$rwif1["inicio"]?></td>
                                    <td><font color="#828282">Data de Término:  </font><?=$rwif1["fim"]?></td>
                                </tr>
                                <tr>
                                    <td><font color="#828282">Nº de Animais: </font><?=$rwb['qtd']?></td>
                                    <td><font color="#828282">Nº Lote: </font><?=$rwb['idlote']?></td>
                                </tr>
                            </table>
                        </div>
                    </td>
                    <?
                if ($i == 1) {?>
                    </tr>
                    <? }

                    }
                }?>
            <?}else{?>
            </tr>
            <?}?>
                        </table>
                    </div>
                </td>	
            </tr>
            
</table>
<table  CELLSPACING="10" CELLPADDING="0"  style="width: 100%;" >
    <tr style="font-size: 14px;">
        <td style="width: 100%; background-color: rgb(225,225,225);" colspan="2" align="left"><b><div class="divcirculo">3.0</div>&nbsp;&nbsp;PROTOCOLO EXPERIMENTAL</b></td>
    </tr>
    <tr>
        <td colspan="1" class="borderblue" style="vertical-align: top;border-color: solid black;border-bottom-left-radius: 11px;border-bottom-right-radius: 11px;">
            <div >
                <table cellspacing="0"  style="border-color: solid black;width: 100%;align-content: center;background-color: #c0c0c0;border-top-left-radius: 10px;border-top-right-radius: 10px;" >
                    <tr>
                        <th align="Center"><?=$rowcap['idregistro']." - ".$rowcap['coranilha']?></th>
                    </tr>
                </table>
                <table border="1" cellspacing="0" style="border-color: solid black;border-bottom-left-radius: 11px;border-bottom-right-radius: 11px;">
                    <tr align="center">
                        <td>Data:</td>
                        <td>Serviço:</td>
                        <td>Quantidade:</td>
                        <td>Dia D:</td>
                    </tr> 
                        <?
                        $sqla="select
                        s.idservicoensaio,
                        a.idanalise,
                        a.idbioterioanalise,
                        a.qtd,
                        s.dia,dma(s.data) as dmadata,
                        s.diazero,
                        s.status,sb.rotulo,
                        DATEDIFF(s.data,e.nascimento) AS idade,
                        DATEDIFF(s.data,a.datadzero) as diabioensaio,
                        s.idamostra,ee.idbioensaio,
                        sb.idservicobioterio,
                        ba.cria 
                        from bioensaio e
                        join analise a on(a.objeto='bioensaio' and a.idobjeto =  e.idbioensaio)
                        join servicoensaio s on( s.tipoobjeto = 'analise' and s.idobjeto=a.idanalise )
                        join servicobioterio sb on(sb.idservicobioterio = s.idservicobioterio )
                        join bioterioanalise ba on(a.idbioterioanalise = ba.idbioterioanalise)
                        left join analise aa on(aa.idanalise =a.idanalisepai) 
                        left join bioensaio ee on(ee.idbioensaio=aa.idobjeto and aa.objeto ='bioensaio')
                        left join nucleo n on(n.idnucleo=ee.idnucleo)
                    where
                    e.idbioensaio =".$rowcap['idbioensaio']."
                    order by s.data,aa.idanalise,sb.rotulo desc";
                        $resa = d::b()->query($sqla) or die("Erro ao buscar descricao do lote produto=".$sqla);
                        while($serv = mysqli_fetch_assoc($resa)){
                    ?>
                    <tr style="border-color: solid black;border-bottom-left-radius: 11px;border-bottom-right-radius: 11px;" align="center">
                    <? $protocolo=traduzid('bioterioanalise','idbioterioanalise','tipoanalise',$serv['idbioterioanalise']);?>
                    <td style="border-bottom: 1 black;"><?=dma($serv['dmadata'])?></td>
                    <td style="border-bottom: 1 black;min-height: 21px;"><?=$serv['rotulo']?></td>
                    <td style="border-bottom: 1 black;"><?=$serv['qtd']?></td>
                    <td style="border-bottom: 1 black;"><?=$serv['diabioensaio']?></td>
                    </tr>
                    <?
                }?>
                </table>
            </div>
        </td>
        <?if ($qtdl > 0) {
            $sql = "select * from analise where idbioensaioctr =".$rowcap['idbioensaio']." group by idobjeto";
            $i = 0;
            $resl = d::b()->query($sql) or die("Erro ao buscar numero de bioensaios slq=".$sql);
            while( $rwl = mysqli_fetch_assoc($resl)){
                $sqlb = "select * from bioensaio where idbioensaio =".$rwl['idobjeto'];
                $resb = d::b()->query($sqlb) or die("Erro ao buscar numero de bioensaios slq*=".$sqlb);
                while( $rwb = mysqli_fetch_assoc($resb)){
                $i++;
                ?>
                
    <?if ($i == 2) {?>
    <tr>
    <?$i = 0;
    }?>
                
    <td colspan="1" class="borderblue" style="vertical-align: top;">
        <div  style="width: 100%;">
            <table style="width: 100%;align-content: center;background-color: #c0c0c0;border-top-left-radius: 10px;border-top-right-radius: 10px;" >
                <tr>
                    <th align="Center">B<?=$rwb['idregistro']." - ".$rwb['coranilha']?></th>
                </tr>
            </table>
            <table border="1" cellspacing="0" style="border-color: solid black;border-bottom-left-radius: 11px;border-bottom-right-radius: 11px;">
            <tr align="center">
                        <td>Data:</td>
                        <td>Serviço:</td>
                        <td>Quantidade:</td>
                        <td>Dia D:</td>
                    </tr> 
                        <?
                        $sqla="select
                        s.idservicoensaio,
                        a.idanalise,
                        a.idbioterioanalise,
                        a.qtd,
                        s.dia,dma(s.data) as dmadata,
                        s.diazero,
                        s.status,sb.rotulo,
                        DATEDIFF(s.data,e.nascimento) AS idade,
                        DATEDIFF(s.data,a.datadzero) as diabioensaio,
                        s.idamostra,ee.idbioensaio,
                        sb.idservicobioterio,
                        ba.cria 
                        from bioensaio e
                        join analise a on(a.objeto='bioensaio' and a.idobjeto =  e.idbioensaio)
                        join servicoensaio s on( s.tipoobjeto = 'analise' and s.idobjeto=a.idanalise )
                        join servicobioterio sb on(sb.idservicobioterio = s.idservicobioterio )
                        join bioterioanalise ba on(a.idbioterioanalise = ba.idbioterioanalise)
                        left join analise aa on(aa.idanalise =a.idanalisepai) 
                        left join bioensaio ee on(ee.idbioensaio=aa.idobjeto and aa.objeto ='bioensaio')
                        left join nucleo n on(n.idnucleo=ee.idnucleo)
                    where
                    ee.idbioensaio =".$rwb['idbioensaio']."
                    order by s.data,sb.rotulo desc";
                        $resa = d::b()->query($sqla) or die("Erro ao buscar descricao do lote produto=".$sqla);
                        while($serv = mysqli_fetch_assoc($resa)){
                    ?>
                    <tr style="border-color: solid black;border-bottom-left-radius: 11px;border-bottom-right-radius: 11px;" align="center">
                    <? $protocolo=traduzid('bioterioanalise','idbioterioanalise','tipoanalise',$serv['idbioterioanalise']);?>
                    <td style="border-bottom: 1 black;"><?=dma($serv['dmadata'])?></td>
                    <td style="border-bottom: 1 black;min-height: 21px;"><?=$serv['rotulo']?></td>
                    <td style="border-bottom: 1 black;"><?=$serv['qtd']?></td>
                    <td style="border-bottom: 1 black;"><?=$serv['diabioensaio']?></td>
                    </tr>
                    <?
                }?>
                </table>
        </div>
    </td>

        <?
    if ($i == 1) {?>
        </tr>
        <? }

        }
    }?>
        <?}else{?>
        </tr>
        <?}
    ?>
</table>
<div class="quebrapagina"></div>
<table  CELLSPACING="10" CELLPADDING="0"  style="width: 100%;" >
    <tr style="font-size: 14px;">
        <td style="width: 100%; background-color: rgb(225,225,225);" colspan="2" align="left"><b><div class="divcirculo">4.0</div>&nbsp;&nbsp;RESUMO</b></td>
    </tr>
    <tr  style="max-width: 698px !important;">
    <?if ($qtdl > 0) {?>
        <td colspan="1" class="borderblue" style="vertical-align: top;">
    <?}else{?>
        <td colspan="2" class="borderblue" style="vertical-align: top;">
    <?}?>
            <div>
                <table style="width: 100%;align-content: center;background-color: #c0c0c0;border-top-left-radius: 10px;border-top-right-radius: 10px;" >
                    <tr>
                        <th align="Center"><?=$rowcap['idregistro']." - ".$rowcap['coranilha']?></th>
                    </tr>
                </table>
                <table border="0" style="width:100%;border-top: 0;border-bottom: 0;border-left: 0;border-right: 0;">   
                        <?
                        $sqla="select * from analise where objeto='bioensaio' and idobjeto=".$rowcap['idbioensaio'];
                        $resa = d::b()->query($sqla) or die("Erro ao buscar descricao do lote produto=".$sqla);
                        while($serv = mysqli_fetch_assoc($resa)){
                            $sqlam = "select a.idanalise
                                        ,a.idbioterioanalise
                                        ,am.idregistro
                                        ,if(s.dia is null,sb.rotulo,concat(sb.rotulo,' D',s.dia)) as rotulo
                                        ,r.conformidade
                                        ,r.resultadocertanalise
                                        ,r.quantidade
                                        ,r.status
                                        ,r.idresultado
                                        ,r.idservicoensaio
                                        ,r.idtipoteste
                                        ,left(dma(s.data),5) as dataserv
                                            from resultado r
                                            join vwtipoteste p on (p.idtipoteste  = r.idtipoteste)
                                            join servicoensaio s on (r.idservicoensaio=s.idservicoensaio)
                                            join servicobioterio sb on (sb.idservicobioterio = s.idservicobioterio)
                                            join analise a on (s.idobjeto = a.idanalise)
                                            left join amostra am on (am.idamostra = r.idamostra)
                                        where 
                                        r.status !='OFFLINE'
                                        and a.idanalise = ".$serv['idanalise']."
                                        order by dataserv desc, idregistro asc";
            
                    $resper=d::b()->query($sqlam) or die("Erro ao buscar dias de permanàªncia"."<br>".$sqlam);
                    $protocolo=traduzid('bioterioanalise','idbioterioanalise','tipoanalise',$serv['idbioterioanalise']);
                    ?>
                    
                    <?
                    $qtdr = mysqli_num_rows($resper);
                    if ($qtdr > 0) {?>
                    <tr>
                        <td align="center" colspan="2">
                            <table border="1" cellspacing="0" style="width: 100%;border-color: solid black;">
                                <tr >
                                    <td colspan="4">
                                        <table style="width: 100%;">
                                            <tr>
                                                <th align="left" nowrap style="border-right: 0;">PROTOCOLO: <?=$protocolo?></th>
                                                <th align="right" style="border-left: 0;">ID: <?=$serv['idanalise']?></th>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr align="Center">
                                    <td> <font style="font-weight: bold;">Data:</font></td>
                                    <td> <font style="font-weight: bold;">ID Registro:</font></td>
                                    <td> <font style="font-weight: bold;">Teste:</font></td>
                                    <td> <font style="font-weight: bold;">Resultado:</font></td>
                                </tr>
                        <?
                        while($rowper=mysqli_fetch_assoc($resper)){?>
                                <tr align="Center">
                                    <td><?=$rowper['dataserv']?></td>
                                    <td><?=$rowper['idregistro']?></td>
                                    <td><?=$rowper['rotulo']?></td>
                                    <td><?if(!$rowper['resultadocertanalise']){echo "Não disponível";}else{ echo $rowper['resultadocertanalise'];}?></td>
                                </tr>
                            <?}
                        ?></table>
                        </td>
                    </tr>
                        <?}?>
                    <?}?>
                </table>
            </div>
        </td>
        <?if ($qtdl > 0) {
            $sql = "select * from analise where idbioensaioctr =".$rowcap['idbioensaio']." group by idobjeto";
            $i = 0;
            $resl = d::b()->query($sql) or die("Erro ao buscar numero de bioensaios slq=".$sql);
            while( $rwl = mysqli_fetch_assoc($resl)){
                $sqlb = "select * from bioensaio where idbioensaio =".$rwl['idobjeto'];
                $resb = d::b()->query($sqlb) or die("Erro ao buscar numero de bioensaios slq*=".$sqlb);
                while( $rwb = mysqli_fetch_assoc($resb)){
                $i++;
                ?>
                
    <?if ($i == 2) {?>
    <tr>
    <?$i = 0;
    }?>
                
    <td colspan="1" class="borderblue" style="vertical-align: top;width: 50%;">
        <div  style="width: 100%;">
            <table style="width: 100%;align-content: center;background-color: #c0c0c0;border-top-left-radius: 10px;border-top-right-radius: 10px;" >
                <tr>
                    <th align="Center">B<?=$rwb['idregistro']." - ".$rwb['coranilha']?></th>
                </tr>
            </table>
            <table border="0" style="width:100%;border-top: 0;border-bottom: 0;border-left: 0;border-right: 0;">   
                        <?
                        $sqla="select * from analise where objeto='bioensaio' and idobjeto=".$rwb['idbioensaio'];
                        $resa = d::b()->query($sqla) or die("Erro ao buscar descricao do lote produto=".$sqla);
                        while($serv = mysqli_fetch_assoc($resa)){
                            $sqlam = "select a.idanalise
                                        ,a.idbioterioanalise
                                        ,am.idregistro
                                        ,if(s.dia is null,sb.rotulo,concat(sb.rotulo,' D',s.dia)) as rotulo
                                        ,r.conformidade
                                        ,r.resultadocertanalise
                                        ,r.quantidade
                                        ,r.status
                                        ,r.idresultado
                                        ,r.idservicoensaio
                                        ,r.idtipoteste
                                        ,left(dma(s.data),5) as dataserv
                                            from resultado r
                                            join vwtipoteste p on (p.idtipoteste  = r.idtipoteste)
                                            join servicoensaio s on (r.idservicoensaio=s.idservicoensaio)
                                            join servicobioterio sb on (sb.idservicobioterio = s.idservicobioterio)
                                            join analise a on (s.idobjeto = a.idanalise)
                                            left join amostra am on (am.idamostra = r.idamostra)
                                        where 
                                        r.status !='OFFLINE'
                                        and a.idanalise = ".$serv['idanalise']."
                                        order by dataserv desc, idregistro asc";
            
                    $resper=d::b()->query($sqlam) or die("Erro ao buscar dias de permanàªncia"."<br>".$sqlam);
                    $protocolo=traduzid('bioterioanalise','idbioterioanalise','tipoanalise',$serv['idbioterioanalise']);
                    ?>
                    
                    <?
                    $qtdr = mysqli_num_rows($resper);
                    if ($qtdr > 0) {
                        ?>
                    <tr>
                        <td align="center" colspan="2">
                            <table border="1" cellspacing="0" style="width: 100%;border-color: solid black;">
                                <tr >
                                    <td colspan="4">
                                        <table style="width: 100%;">
                                            <tr>
                                                <th align="left" nowrap style="border-right: 0;">PROTOCOLO: <?=$protocolo?></th>
                                                <th align="right" style="border-left: 0;">ID: <?=$serv['idanalise']?></th>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr align="Center">
                                    <td> <font style="font-weight: bold;">Data:</font></td>
                                    <td> <font style="font-weight: bold;">ID Registro:</font></td>
                                    <td> <font style="font-weight: bold;">Teste:</font></td>
                                    <td> <font style="font-weight: bold;">Resultado:</font></td>
                                </tr>
                        <?
                        while($rowper=mysqli_fetch_assoc($resper)){?>
                                <tr align="Center">
                                    <td> <font style="font-weight: bold;"><?=$rowper['dataserv']?></td>
                                    <td> <font style="font-weight: bold;"><?=$rowper['idregistro']?></td>
                                    <td> <font style="font-weight: bold;"><?=$rowper['rotulo']?></td>
                                    <td> <font style="font-weight: bold;"><?if(!$rowper['resultadocertanalise']){echo "Não disponível";}else{ echo $rowper['resultadocertanalise'];}?></td>
                                </tr>
                            <?}
                        ?></table>
                        </td>
                    </tr>
                        <?}?>
                    <?}?>
                </table>
        </div>
    </td>

        <?
    if ($i == 1) {?>
        </tr>
        <? }

        }
    }?>
        <?}else{?>
        </tr>
        <?}
    ?>
</table>
<div class="quebrapagina"></div>
<table  CELLSPACING="10" CELLPADDING="0" style="width: 100%;">
    <tr style="font-size: 14px;">
        <td style="width: 100%; background-color: rgb(225,225,225);" colspan="2" align="left"><b><div class="divcirculo">5.0</div>&nbsp;&nbsp;INFORMAÇÕES COMPLEMENTARES</b></td>
    </tr>
    <tr>
        <td>
            <?
            $sqlan="SELECT
            ba.tipoanalise
            ,ba.texto AS texto
            FROM
            analise a
                JOIN
            bioensaio b ON (a.objeto = 'bioensaio'
                AND a.idobjeto = b.idbioensaio)
                JOIN
            bioterioanalise ba ON (ba.idbioterioanalise = a.idbioterioanalise)
            WHERE
            b.idbioensaio = ".$rowcap['idbioensaio']."
            GROUP BY ba.idbioterioanalise";


            //die($sqli);
            $resan=d::b()->query($sqlan) or die("Erro ao buscar protocolo sql=".$sqlan);
            $i = 1;
            while($rowan=mysqli_fetch_assoc($resan)){?>
                <table style="font-size: 20px;">
                    <tr><td><br><b><?=$rowan['tipoanalise']?></b><p></td></tr>	
                </table>
                <table style="font-size: 20px;">
                    <tr>
                        <td>
                            <div class="row">
                                <div class="col 100 quebralinha" style="line-height: 150% !important;"><?=nl2br($rowan["texto"])?></div>
                            </div>
                        </td>
                    </tr>
                </table>
            <?
            $i++;
            }?>
        </td>
    </tr>
</table>
<?if(!empty($row['consideracoes'])){?>
<table  CELLSPACING="10" CELLPADDING="0" style="width: 100%;">
    <tr style="font-size: 14px;">
        <td style="width: 100%; background-color: rgb(225,225,225);" colspan="2" align="left">
            <b>
                <div class="divcirculo">
                    6.0
                </div>
                &nbsp;&nbsp;CONSIDERAÇÕES GERAIS
            </b>
        </td>
    </tr>
    <tr style="font-size: 20px;">
        <td style="line-height: 150% !important;">
            <div class="borderblue">
                <?=nl2br($row['consideracoes'])?>
            </div> 
        </td>
    </tr>
</table>
<?}?>
<?if(!empty($row['conclusao'])){?>
<table  CELLSPACING="10" CELLPADDING="0" style="width: 100%;">
    <tr style="font-size: 14px;">
        <td style="width: 100%; background-color: rgb(225,225,225);" colspan="2" align="left">
            <b>
                <div class="divcirculo">7.0</div>
                &nbsp;&nbsp;CONCLUSÃO DO TESTE
            </b>
        </td>
    </tr>
    <tr style="font-size: 20px;">
        <td style="line-height: 150% !important;">
            <div class="borderblue">
                <?=nl2br($row['conclusao'])?>
            </div> 
        </td>
    </tr>
</table>
<?}?>
<div class="quebrapagina"></div>
<?
    }//while($rowcap=mysqli_fetch_assoc($rescap))
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