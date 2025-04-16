<?
require_once("../inc/php/validaacesso.php");

if(empty($_GET['idbioensaio'])){
    die("Bioensaio não enviado");
}
$idbioensaio = $_GET['idbioensaio'];

if(!empty($idbioensaio)){
    $clausulam .=" and idbioensaio =".$idbioensaio;
}

$sql="select * from vwbioensaio where idempresa = ".cb::idempresa()." ".$clausulam;

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


[class$='5']{width: 5%;}
[class$='10']{width: 9%;}
[class$='15']{width: 15%;}
[class$='20']{width: 20%;}
[class$='25']{width: 25%;}
[class$='30']{width: 30%;}
[class$='35']{width: 35%;}
[class$='40']{width: 39.99%;}
[class$='45']{width: 45%;}
[class$='50']{width: 50%;}
[class$='55']{width: 55%;}
[class$='60']{width: 60%;}
[class$='65']{width: 65%;}
[class$='70']{width: 70%;}
[class$='75']{width: 75%;}
[class$='80']{width: 80%;}
[class$='85']{width: 85%;}
[class$='90']{width: 90%;}
[class$='95']{width: 95%;}
[class$='100']{width: 100%;}
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

    $piloto = traduzid('lote','idlote','piloto',$row['idlotepd']) == "Y" ? "PP " :"";

    
    if($row["especie"]=="Aves"){
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
   
   $sqls="select a.idanalise,a.idbioensaioctr,a.idanalisepai,a.datadzero,ba.tipoanalise,c.exercicio,c.idregistro,n.nucleo,max(s.data) as fim, le.idtag,ba.pddose,ba.pdvia,ba.pdvolume
            from analise a join bioterioanalise ba left join bioensaio c on(c.idbioensaio = a.idbioensaioctr) left join nucleo n on( n.idnucleo= c.idnucleo)
            left join servicoensaio s on ( s.idobjeto = a.idanalise and s.tipoobjeto ='analise')
            left join localensaio le on (le.idanalise = a.idanalise)
            where  a.idobjeto  = ".$row['idbioensaio']." and a.objeto = 'bioensaio'
            and ba.idbioterioanalise = a.idbioterioanalise group by a.idanalise";
    $ress=d::b()->query($sqls) or die("Erro ao buscar Serviços sql=".$sqls);
    $qtds=mysqli_num_rows($ress);
    if($qtds>0){
        $z=0;
        while($rows=mysqli_fetch_assoc($ress)){
            $z=$z+1;
            if($z>1){
?>
<div class="quebrapagina"></div>
<?
            }
?>
<pagina class="ordContainer">
     <header class="row margem0.0">
		<div class="logosup col 5">
			<?
			// GVT - 17/04/2020 - Altera logo do relatório, utiliza a imagem cadastrada na empresa.
			$_timbrado = $_GET["_timbrado"] != ''? $_GET["_timbrado"]:'';
			$timbradoidempresa = $_GET["_timbradoidempresa"] != ''? "and idempresa = ".$_GET["_timbradoidempresa"]:getImagemRelatorio('bioensaio', 'idbioensaio', $row['idbioensaio']);
			
			if($_timbrado != 'N'){
		
				$_sqltimbrado="select * from empresaimagem where idempresa = ".cb::idempresa()." and tipoimagem = 'HEADERPRODUTO'";
				$_restimbrado = mysql_query($_sqltimbrado) or die("Erro ao retornar figura para cabeçalho do relatório: ".mysql_error());
				$_figtimbrado=mysql_fetch_assoc($_restimbrado);

				$_sqltimbrado1="select * from empresaimagem where idempresa = ".cb::idempresa()." and tipoimagem = 'IMAGEMMARCADAGUA'";
				$_restimbrado1 = mysql_query($_sqltimbrado1) or die("Erro ao retornar figura do relatório: ".mysql_error());
				$_figtimbrado1=mysql_fetch_assoc($_restimbrado1);

				$_sqltimbrado2="select * from empresaimagem where idempresa = ".cb::idempresa()." and tipoimagem = 'IMAGEMRODAPE'";
				$_restimbrado2 = mysql_query($_sqltimbrado2) or die("Erro ao retornar figura do relatório: ".mysql_error());
				$_figtimbrado2=mysql_fetch_assoc($_restimbrado2);
				
				$_timbradocabecalho = $_figtimbrado["caminho"];
				$_timbradomarcadagua = $_figtimbrado1["caminho"];
				$_timbradorodape = $_figtimbrado2["caminho"];
				
				if(!empty($_timbradocabecalho)){?>
					<div id="_timbradocabecalho"><img src="<?=$_timbradocabecalho?>"></div>
				<?}
			}

            $partida = $partidaPiloto.traduzid('lote','idlote','partidaext',$row['idlotepd']);

                        
		?>
		</div>
		<div class="titulodoc">REGISTRO OPERACIONAL DO BIOTÉRIO</div>		
    </header>
    <div class="row">
        <div class=" col rot 15">Empresa:</div>
        <div class=" col quebralinha 35"><?=$row["nome"]?></div>
        <div class=" col rot 15">N&ordm; Registro:</div>
        <div class=" col 35"><?=$row["idregistro"]?> / <?=$row["exercicio"]?></div>
    </div>
    <div class="row">
        <div class=" col rot 15">Estudo:</div>
        <div class=" col quebralinha 35"><?=$row["bioensaio"]?></div>
        <div class=" col rot 15"></div>
        <div class=" col 35"></div>
    </div>
      <br>
    <div class="row">
        <div class="col grupo  quebralinha 100">
                <div class="titulogrupo">DADOS DOS ANIMAIS</div>
        </div>
    </div>
    <div class="row">
        <div class=" col rot 15 ">Tipo:</div>
        <div class=" col 35"><?=$row["especie"]?>-<?=$row["finalidade"]?></div>
        <div class=" col rot 15 ">Nº <?=$row["especie"]?>:</div>
        <div class=" col quebralinha 20"><?=$row["qtd"]?></div>
        <div class="col 30 rot">Identificação do Grupo:</div>
        <div class="col <?if(empty($row["coranilha"])){?>sublinhado<?}?> 20"><?=$row["coranilha"]?></div>
    </div>
    <div class="row">
        <div class=" col rot 15">Nascimento:</div>
        <div class=" col 35"><?=dma($row["nascimento"])?></div>
        <div class="col rot 15"></div>
        <div class="col 20"></div>  
    </div>    
    <br>
    <div class="row">
        <div class="col grupo quebralinha 100">
                <div class="titulogrupo">DADOS DO PRODUTO</div>
        </div>
    </div>     
    <div class="row">
        <div class=" col rot 20">Produto:</div>
        <div class=" col 35"><?=$row["produto"]?></div>
        <div class=" col rot 15"></div>
        <div class=" col 35"></div>
        <div class=" col rot 15"></div>
        <div class=" col quebralinha 20"></div>
    </div>    
    <div class="row">
        <div class=" col rot 20">Partida Interna:</div>
        <div class=" col 35"><?=$row["partidainternaprod"]?></div>
        <div class=" col rot 30"></div>
        <div class=" col rot 15">Partida:</div>

        <div class=" col 35"><?= $piloto . $partida ?></div>
        <div class=" quebralinha"></div>
    </div>    
    <div class="row">
        <div class="col rot 20">Vol. Aplicado:</div>
        <div class="col <?if(empty($row["volume"])){?>sublinhado<?}?> 35"><?=$row["volume"]?></div>
        <div class="col rot 15">Nº Doses:</div>
        <div class="col <?if(empty($row["doses"])){?>sublinhado<?}?> 35"><?=$row["doses"]?></div>
        <div class="col rot 15">Via:</div>
        <div class="col <?if(empty($row["via"])){?>sublinhado<?}?> 20"><?=$row["via"]?></div>        
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
                    from vw_reservabioensaio where idbioensaio = ".$row['idbioensaio'];
    $resl=d::b()->query($sqll) or die("Erro ao buscar locais de ensaio sql=".$sqll);
    $qtdl=mysqli_num_rows($resl);
    if($qtdl>0){
 
        $rowl=mysqli_fetch_assoc($resl);

        $sql0="select le.idtag
        from localensaio le 
        join analise a on (a.idanalise = le.idanalise) 
        join bioensaio b on (a.idobjeto = b.idbioensaio) 
        join tag t on (le.idtag = t.idtag) 
        where a.idanalise = ".$rows['idanalise'];
        $res0=d::b()->query($sql0) or die("erro ao buscar local biobox sql=".$sql0);
        $row0=mysqli_fetch_assoc($res0);
?>
    <br>
    <div class="row">
        <div class="col grupo 100 quebralinha">
                <div class="titulogrupo">LOCALIZAÇÃO</div>
        </div>
    </div>
    <div class="row">
        <div class=" col rot 15">Local:</div>
        <div class=" col quebralinha 35">
            <?if(!empty($row0['idtag'])){
            $sqlt="select CONCAT(b.descricao,' - ',g.descricao) AS descricao  from tag g join  tagsala ts on (ts.idtag = g.idtag) 
			join tag b on(b.idtag=ts.idtagpai)
			where g.idtag =".$row0['idtag'];
            $rt=d::b()->query($sqlt) or die("erro ao buscar local biobox sql=".$sqlt);
            $rowt=mysqli_fetch_assoc($rt);

            echo $rowt['descricao'];
          }?>
        </div>
        <div class=" col rot 15">Permanêcia:</div>
        <div class=" col 35"><?=$rowl["ibio"]?> - <?=$rowl["fbio"]?></div>
        <div class=" col rot 15"></div>
        <div class=" col 20"></div>
    </div>   
<?
    }  
?>  
    
    <br>
    <div class="row">
        <div class="col grupo quebralinha 100">
            <div class="titulogrupo">PROTOCOLO</div>
        </div>
    </div>
   
    <div class="row">
        <div class=" col rot 15">Título:</div>
        <div class=" col quebralinha 35"><?=$rows["tipoanalise"]?></div>
        <div class=" col rot 15">Início:</div>
        <div class=" col 35"><?=dma($rows["datadzero"])?></div>
        <div class=" col rot 15">Fim:</div>
        <div class=" col 20"><?=dma($rows["fim"])?></div>
    </div>
    <?
    if(!empty($rows["idbioensaioctr"])){
    ?>
    <div class="row">
        <div class=" col rot 15">Reg. Controle:</div>
        <div class=" col quebralinha 35">B<?=$rows["idregistro"]?>/<?=$rows["exercicio"]?></div>        
        <div class=" col rot 15">Est. Controle:</div>
        <div class=" col 70" style="white-space: normal;"><?=$rows["nucleo"]?></div>
        <div class=" col rot 0"></div>
        <div class=" col 0"></div>
    </div>
    <?
    }elseif(!empty($rows["idanalisepai"])){
        $sqc="select e.idregistro,e.exercicio,n.nucleo
            from bioensaio e join analise a left join nucleo n on(n.idnucleo = e.idnucleo)
            where a.idanalise = ".$rows["idanalisepai"]."            
            and a.idobjeto = e.idbioensaio
            and a.objeto ='bioensaio'";
        $rec=d::b()->query($sqc) or die("Erro ao buscar bioensaio controlado sql=".$sqc);
        $roc=mysqli_fetch_assoc($rec);
?>
    <div class="row">
        <div class=" col rot 15">Controla:</div>
        <div class=" col quebralinha 35">B<?=$roc["idregistro"]?>/<?=$roc["exercicio"]?></div>        
        <div class=" col rot 15">Estudo:</div>
        <div class=" col 35"><?=$roc["nucleo"]?></div>
        <div class=" col rot 15"></div>
        <div class=" col 35"></div>
    </div>
    
<?
    }//if(!empty($rows["idanalisepai"])){ 
    ?>
     <div class="row grid">
        <div class="col grupo 33 quebralinha">
                Data                   
        </div>
        <div class="col grupo 33 quebralinha">
                Idade(dias)
        </div>
        <div class="col grupo 34 quebralinha">
            Procedimento
        </div>
        <div class="col grupo 68 quebralinha">
               Observação
        </div>
        <div class="col grupo 34 quebralinha">
                Assinatura
        </div>
    </div>
    <?
    
            $sqld="select sb.rotulo,DATEDIFF(s.data,e.nascimento) AS idade,s.data,dma(s.data) as dmadata,s.dia,s.obs
                from analise a, servicoensaio s,bioensaio e,servicobioterio sb
                where sb.idservicobioterio = s.idservicobioterio
                and  a.idanalise = ".$rows['idanalise']."
                and s.idobjeto = a.idanalise
                and s.tipoobjeto = 'analise'
                and a.idobjeto = e.idbioensaio
                and a.objeto='bioensaio' order by s.data,sb.ordem";
            $resd=d::b()->query($sqld) or die("Erro ao buscar dia de vida sql=".$sqld);
            while($rowd=mysqli_fetch_assoc($resd)){    
    ?>
    
    <div class="row grid">
        <div class="col grupo 33 quebralinha">
            <?=$rowd["dmadata"]?>
        </div>
        <div class="col grupo 33 quebralinha">
            <?=$rowd["idade"]?>
        </div>
        <div class="col grupo 34 quebralinha">
            <?=$rowd['rotulo']?> - D<?=$rowd["dia"]?>
        </div>
        <div class="col grupo 68 quebralinha">     
            <?=$rowd["obs"]?>                      
        </div>
        <div class="col grupo 34 quebralinha">
        </div>
    </div>
<?
           }//while($rowd=mysqli_fetch_assoc($resd)){
?>
    <br>
    <div class="row">
        <div class="col grupo quebralinha 100">
                <div class="titulogrupo">OBSERVAÇÃO</div>
        </div>
    </div>
<?
            //$varobs = str_replace(chr(13),"<br>",$row["observacao"]);
            $varobs = nl2br($row["obs"]);
?>
    
     <div class="row">
        <div class=" col quebralinha 100"><?=$varobs?></div>        
    </div>
 </pagina>  
<?

if($row['idpessoa']==1700){
    $strm="  and p.idprodserv in (2075,7216,2259,2248,2247,1941,2411,2999,3000,3001,3122,2005,3193,3641,3642,3992) ";
}else{
    $strm="";
}
//INICIO RESULTADOS 
            $sqlt="select p.codprodserv
            ,ifnull(n.nucleo,b.estudo) as nucleo
            ,b.antigeno
            ,l1.partida
            ,concat(p1.descrcurta) as produto
            ,p.descr
            ,r.quantidade
            ,p.textoinclusaores
            ,p.textopadrao
            ,r.status
            ,r.idamostra
            ,r.idresultado
            ,s.dia
            ,if(s.dia is null,sb.rotulo,concat(sb.rotulo,' D',s.dia)) as rotulo
            ,a.idregistro
            ,a.exercicio
            ,sta.subtipoamostra
            from bioensaio b
            left join nucleo n on(n.idnucleo=b.idnucleo)
            join servicoensaio s 
            join resultado r on (r.idservicoensaio=s.idservicoensaio)
            left join lote l1 on (l1.idlote = b.idlotepd)
            join prodserv p on (p.idprodserv  = r.idtipoteste)
            left join prodserv p1 on (p1.idprodserv  = l1.idprodserv)
            join servicobioterio sb on(sb.idservicobioterio = s.idservicobioterio)
            join amostra a on (a.idamostra = r.idamostra)
            join subtipoamostra sta on (a.idsubtipoamostra = sta.idsubtipoamostra)
            join analise an on (s.idobjeto =an.idanalise and b.idbioensaio =an.idobjeto and an.objeto ='bioensaio')
            where
            r.status !='OFFLINE'
            --  ".$strm."
            -- and p.textopadrao is not null
            -- and r.idamostra = b.idamostra
            and s.tipoobjeto = 'analise'
            and an.idanalise =".$rows['idanalise']."
            order by s.data,sb.ordem";
            
            //die($sqlt);
            $rest=d::b()->query($sqlt) or die("Erro ao buscar os testes sql=".$sqlt);
            $qtdt=mysqli_num_rows($rest);
            if($qtdt>0){

                while($rowt=mysqli_fetch_assoc($rest)){
?>			
    
         <div class="quebrapagina"></div>
         <pagina>
        <header class="row margem0.0">
		<div class="logosup row"><?if(!empty($_timbradocabecalho)){?>
					<div id="_timbradocabecalho"><img src="<?=$_timbradocabecalho?>"></div>
				<?}?></div>
		<div class="titulodoc row"><?=$rowt['descr']?></div>		
	</header>
	     <hr>
       <div class="row"> 
	    <div class="col rot 10">Estudo:</div>
            <div class="col val 20"><?=$rowt['nucleo']?></div>   
	    <div class="col rot 10"></div>
            <div class="col val 10"></div>
            <div class="col rot 10"></div>
	    <div class="col val 20"></div>	    
        </div>
	<div class="row"> 
	    <div class="col rot 10">Produto:</div>
            <div class="col val 20"><?=$rowt['produto']?></div>   
	        <div class="col rot 10"></div>
            <div class="col val 10"></div>
            <div class="col rot 10">Partida:</div>
	        <div class="col val 20"><?= $piloto . $partida ?></div> 	    
        </div>
	<div class="row"> 
	    <div class="col rot 10">Amostra:</div>
            <div class="col val 20"><?=$rowt["subtipoamostra"]?></div>   
	    <div class="col rot 10"></div>
            <div class="col val 10"></div>
            <div class="col rot 10">Exercicio:</div>
	    <div class="col val 20"><?=$rowt['exercicio']?></div> 
        </div>
	<div class="row"> 
	    <div class="col rot 10">Empresa:</div>
            <div class="col val 20"><?=$row['nome']?></div>   
	    <div class="col rot 10"></div>
            <div class="col val 10"></div>
	    <div class="col rot 10"></div>
	    <div class="col val 20"></div> 
        </div>
	<div class="row"> 
	    <div class="col rot 10">Reg. Biotério:</div>
            <div class="col val 20"><?=$row['idregistro']?></div>   
	    <div class="col rot 10"></div>
            <div class="col val 10"></div>
	    <div class="col rot 10">Reg:</div>
	    <div class="col val 20"><?=$rowt['idregistro']?></div> 
        </div>
	<div class="row"> 
	    <div class="col rot 10">Quantidade:</div>
            <div class="col val 20"><?=$rowt['quantidade']?></div>   
	    <div class="col rot 10"></div>
            <div class="col val 10"></div>
	    <div class="col rot 10">Serviço:</div>
	    <div class="col val 20"><?=$rowt['rotulo']?></div> 
        </div>
<?
                    if(!empty($row["obs"])){
?>	     
      
	<div class="row">
	    <div class="col rot 10">Obs.:</div>
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
                }//while($rowt=mysqli_fetch_assoc($rest)){	
		
            }//if($qtdt>0){

//FIM RESULTADOS
        }
    }//aqui
    
    die();
 ?>   

</body>
</html>