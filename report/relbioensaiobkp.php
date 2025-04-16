<?
require_once("../inc/php/validaacesso.php");

if(empty( $_GET['idbioensaio'])){
	die("Bioensaio não enviado");
}

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
<style>
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
	height: 0.5cm;
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
<title>Bioensaio</title>
</head>
<body>
<?
    $res =d::b()->query($sql) or die("Erro ao pesquisar na vwbioensaio :\n".mysqli_error(d::b())."\n".$sql);
    $row=mysqli_fetch_assoc($res);
    
    if(!empty($row["idficharep"])){
      
        $idespeciefinalidade=traduzid("ficharep","idficharep","idespeciefinalidade",$row["idficharep"]);
        $especie=traduzid("especiefinalidade","idespeciefinalidade","especie",$idespeciefinalidade);
    }
     if($especie=="Aves"){
        $fase1='Incubadora';
        $fase2='Pinteiro';
        $fase3='Biobox';
        $taloj='GAIOLA';        
        $titulo='INCUBAÇÃO';
        
    }else{
        $fase1='Reprodução';
        $fase2='Cria';
        $fase3='Biobox';
        $taloj='CAIXA';
        $titulo='REPRODUÇÃO';
    }
       
?>
<pagina class="ordContainer">
     <header class="row margem0.0">
		<div class="logosup col 5"><img src="../inc/img/logoHeaderInata.jpg"></div>
		<div class="titulodoc">REGISTRO OPERACIONAL DO BIOTÉRIO</div>		
    </header>
    <div class="row">
        <div class=" col 15 rot">Empresa:</div>
        <div class=" col 35 quebralinha"><?=$row["nome"]?></div>
        <div class=" col 15 rot">N&ordm; Registro:</div>
        <div class=" col 35"><?=$row["idregistro"]?> / <?=$row["exercicio"]?></div>
    </div>
      <br>
    <div class="row">
        <div class="col grupo 100 quebralinha">
                <div class="titulogrupo">Dados do estudo</div>
        </div>
    </div>
     
    <div class="row">
        <div class=" col 10 rot">Estudo:</div>
        <div class=" col 55 quebralinha"><?=$row["estudo"]?></div>
        <div class=" col 10 rot">Partida:</div>
        <div class=" col 30"><?=$row["partida"]?></div>
    </div>
    
    <div class="row">
        <div class="col 10 rot">Vol. Aplicado:</div>
        <div class="col 20 <?if(empty($row["volume"])){?>sublinhado<?}?>"><?=$row["volume"]?></div>
        <div class="col 10 rot">Nº Doses:</div>
        <div class="col 25 <?if(empty($row["doses"])){?>sublinhado<?}?>"><?=$row["doses"]?></div>
        <div class="col 5 rot">Via:</div>
        <div class="col 30 <?if(empty($row["via"])){?>sublinhado<?}?>"><?=$row["via"]?></div>
    </div>
   
    <div class="row">
        <div class="col 10 rot">Início:</div>
        <div class="col 20"><?=$row["dmainicio"]?></div>
        <div class="col 10 rot">Termino:</div>
        <div class="col 25"><?=$row["fimbioensaio"]?></div>
        <div class="col 10 rot">Cor da Anilha:</div>
        <div class="col 25 <?if(empty($row["coranilha"])){?>sublinhado<?}?>"><?=$row["coranilha"]?></div>

    </div>
     <br>
    <div class="row">
        <div class="col grupo 100 quebralinha">
                <div class="titulogrupo">Dados <?=$especie?></div>
        </div>
    </div>
    <div class="row">
        <div class=" col 15 rot">Procedência:</div>
        <div class=" col 35 quebralinha"><?=$row["fabricante"]?></div>
        <div class=" col 15 rot">Tipo:</div>
        <div class=" col 35"><?=$row["tipo"]?></div>
    </div>
    <div class="row">
        <div class=" col 15 rot">Nº <?=$especie?>:</div>
        <div class=" col 35 quebralinha"><?=$row["qtd"]?></div>
        <div class=" col 15 rot">Nascimento:</div>
        <div class=" col 35"><?=$row["nascimento"]?></div>
    </div>

<?
    $sqll="select idbioensaio,qtd,
                    dma(inicioinc) as iinc ,
                    dma(fiminc) as finc,
                    dma(iniciopint) as ipint,
                    dma(fimpint) as fpint,
                    dma(iniciobio) as ibio,
                    dma(fimbio) as fbio,
                    obs
                    from vwreservabioensaio where idbioensaio = ".$row['idbioensaio'];
    $resl=d::b()->query($sqll) or die("Erro ao buscar locais de ensaio sql=".$sqll);
    $qtdl=mysqli_num_rows($resl);
    if($qtdl>0){
 
        $rowl=mysqli_fetch_assoc($resl);

        $sql0="select concat(l.tipo,' ',right(l.local, 2)) as rot,e.gaiola
                from localensaio e ,local l
                where l.idlocal = e.idlocal
                and e.idbioensaio = ".$rowl['idbioensaio']." 
                and e.idlocal > 3";
        $res0=d::b()->query($sql0) or die("erro ao buscar local biobox sql=".$sql0);
        $row0=mysqli_fetch_assoc($res0);
        $local=$row0['rot'];
?>
    <br>
    <div class="row">
        <div class="col grupo 100 quebralinha">
                <div class="titulogrupo">Locações</div>
        </div>
    </div>
    <div class="row grid">
            <div class="col grupo 33 quebralinha">
                    <div class="titulogrupo"><?=$fase1?></div>
                    <?=$rowl["iinc"]?> - <?=$rowl["finc"]?>
            </div>
            <div class="col grupo 33 quebralinha">
                    <div class="titulogrupo"><?=$fase2?></div>
                   <?=$rowl["ipint"]?> - <?=$rowl["fpint"]?>
            </div>
            <div class="col grupo 34 quebralinha">
                    <div class="titulogrupo"><?=$fase3?></div>
                    <?if(!empty($row0['rot'])){?>(<?=$row0['rot']?><?if(!empty($row0['gaiola'])){?> - <?=$taloj?> <?=$row0['gaiola']?><?}?>) <?}?><?=$rowl["ibio"]?> - <?=$rowl["fbio"]?>
            </div>
    </div>
<?
    }
    $sqls="select	
                s.dia,		
                s.data,
                dma(s.data) as dmadata,
                s.servico,		
                s.obs,
                s.status,
                s.diazero,
                sb.ordem,
                sb.rotulo,
                localservico,
                sb.procedimento,
                s.alteradopor
                from servicoensaio s,servicobioterio sb
                where sb.servico = s.servico
                and s.status !='OFFLINE'
                and s.tipoobjeto = 'bioensaio'
                and s.idobjeto=".$idbioensaio." order by s.data,sb.ordem";
    $ress=d::b()->query($sqls) or die("Erro ao buscar Serviços sql=".$sqls);
    $qtds=mysqli_num_rows($ress);
    if($qtds>0){
?>	
    <br>

    <div class="row">
        <div class="col grupo 100 quebralinha">
                <div class="titulogrupo">Resumo Experimental</div>
        </div>
    </div>
     <div class="row grid">
            <div class="col grupo 33 quebralinha">
                    <div class="titulogrupo">Data</div>
                    <?=$rowl["iinc"]?> 
            </div>
            <div class="col grupo 33 quebralinha">
                    <div class="titulogrupo">Idade(dias)</div>
                  
            </div>
            <div class="col grupo 34 quebralinha">
                    <div class="titulogrupo">Procedimento</div>
                   Incub. / Rep.
            </div>
          <div class="col grupo 34 quebralinha">
                    <div class="titulogrupo">Local</div>
                    SALA DE INC./REP.
            </div>
                   <div class="col grupo 34 quebralinha">
                    <div class="titulogrupo">Observação</div>
                    
            </div>
                   <div class="col grupo 34 quebralinha">
                    <div class="titulogrupo">Assinatura</div>
                    
            </div>
    </div>
  <?		$l=0;
			$temdiaz="N";
			while($rows=mysqli_fetch_assoc($ress)){
		
			if($rows['diazero']=="Y"){
				$temdiaz="Y";
			}
			
				if($rows['servico']=='ALOJAMENTO'){
					$alojamento='Y';
				}
				
				if($alojamento=='Y'){
					if(!empty($row['idlocal'])){
						$sqll="select concat(tipo,' ',right(local, 2)) as rot,l.* 
						from local l where l.idlocal = ".$row['idlocal'];
						$resll=d::b()->query($sqll) or die('Erro ao buscar local sql'.$sqll);
						$rowl=mysqli_fetch_assoc($resll);
						$local=$rowl['rot'];
					}else{
						$local=$rows['localservico'];
					}
				}else{
					$local=$rows['localservico'];
				}
				$sqldia="select 
					(DATEDIFF('".$rows["data"]."',DATE_ADD(fim, INTERVAL 1 DAY))+1) as diasvida
					from ficharep 
					where idficharep = ".$row['idficharep'];
				$resdia=d::b()->query($sqldia) or die("Erro ao buscar dia de vida sql=".$sqldia);
				$rowdia=mysqli_fetch_assoc($resdia);

?>
<div class="row grid">
            <div class="col grupo 33 quebralinha">
                <?=$rows["dmadata"]?>
            </div>
            <div class="col grupo 33 quebralinha">
                <?=$rowdia["diasvida"]?>
            </div>
            <div class="col grupo 34 quebralinha">
                 <?=$rows['procedimento']?><?if($temdiaz=='Y'){?> - D<?=$rows["dia"]?><?}?>
            </div>
            <div class="col grupo 34 quebralinha">
                   <?=$local?>
            </div>
            <div class="col grupo 34 quebralinha">     
                   <?=$rows["obs"]?>                      
            </div>
            <div class="col grupo 34 quebralinha">
                                     
            </div>
</div>
					
<?
			}
?>
    
   
<?
    }
?>  
    
    <br>
    <div class="row">
        <div class="col grupo 100 quebralinha">
                <div class="titulogrupo">Observação</div>
        </div>
    </div>
<?
            //$varobs = str_replace(chr(13),"<br>",$row["observacao"]);
            $varobs = nl2br($row["obs"]);
?>
    
     <div class="row">
        <div class=" col 100 quebralinha"><?=$varobs?></div>        
    </div>
    <br>
    <br>
    </pagina>
   
<?
	$sqlt="select p.codprodserv,b.estudo,b.antigeno,b.partida,p.descr,r.quantidade,p.textoinclusaores,p.textopadrao,r.status,
		    r.idamostra,r.idresultado,s.dia,if(s.dia is null,sb.rotulo,concat(sb.rotulo,' D',s.dia)) as rotulo,
		    a.idregistro,a.exercicio,sta.subtipoamostra
				from bioensaio b, servicoensaio s,resultado r,prodserv p,servicobioterio sb,amostra a,subtipoamostra sta
				where sb.servico = s.servico
				and a.idamostra = r.idamostra
				and a.idsubtipoamostra = sta.idsubtipoamostra
				and p.idprodserv  = r.idtipoteste
				and p.idprodserv in (2075,2259,2248,2247,1941,2411,2999,3000,3001,3122,2005,3193,3641,3642,3992)
				and r.status !='OFFLINE'
				-- and r.idamostra = b.idamostra
				and r.idservicoensaio=s.idservicoensaio
                                and s.tipoobjeto = 'bioensaio'
				and s.idobjeto =b.idbioensaio
				and b.idbioensaio =".$row['idbioensaio'];
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
	     <hr>
	<div class="row"> 
	    <div class="col 10 rot">Produto:</div>
            <div class="col 20 val"><?=$row['formulacao']?></div>   
	    <div class="col 10 rot"></div>
            <div class="col 10 val"></div>
	    <div class="col 10 rot">Exercicio:</div>
	    <div class="col 20 val"><?=$rowt['exercicio']?></div> 
        </div>
	<div class="row"> 
	    <div class="col 10 rot">Amostra:</div>
            <div class="col 20 val"><?=$rowt["subtipoamostra"]?></div>   
	    <div class="col 10 rot"></div>
            <div class="col 10 val"></div>
	    <div class="col 10 rot">Partida:</div>
	    <div class="col 20 val"><?=$row['partida']?></div> 
        </div>
	<div class="row"> 
	    <div class="col 10 rot">Empresa:</div>
            <div class="col 20 val"><?=$row['nome']?></div>   
	    <div class="col 10 rot"></div>
            <div class="col 10 val"></div>
	    <div class="col 10 rot"></div>
	    <div class="col 20 val"></div> 
        </div>
	<div class="row"> 
	    <div class="col 10 rot">Reg. Biotério:</div>
            <div class="col 20 val"><?=$row['idregistro']?></div>   
	    <div class="col 10 rot"></div>
            <div class="col 10 val"></div>
	    <div class="col 10 rot">Reg:</div>
	    <div class="col 20 val"><?=$rowt['idregistro']?></div> 
        </div>
	<div class="row"> 
	    <div class="col 10 rot">Quantidade:</div>
            <div class="col 20 val"><?=$rowt['quantidade']?></div>   
	    <div class="col 10 rot"></div>
            <div class="col 10 val"></div>
	    <div class="col 10 rot">Serviço:</div>
	    <div class="col 20 val"><?=$rowt['rotulo']?></div> 
        </div>
<?
	if(!empty($row["obs"])){
?>	     
      
	<div class="row">
	    <div class="col 10 rot">Obs.:</div>
	    <div class="col 85 quebralinha"><?=$row["obs"]?></div>
	</div>
 <?
	}
 ?>       
         <hr>
         <br>
        <?=$rowt['textoinclusaores']?>
	
	<?=$rowt['textopadrao']?>
        </pagina>
			
<?
			}	
		
		}
?>


</body>
</html>