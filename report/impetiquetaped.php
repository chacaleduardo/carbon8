<?
require_once("../inc/php/functions.php");



$idnf= $_GET['idnf'];

if(empty($idnf)){
	die('ID no pedido não informado');
}

$sql="select n.*,
		t.nome as transporte,
		n.pedidoext,
		n.idnf as idpedido,
		ps.dddfixo,ps.telfixo,
		ps.nome,
		LENGTH(ps.nome) as nometamanho,		
		SUBSTRING(ps.nome, 1, 35) as nomeinicio,
		SUBSTRING(ps.nome, 36, 78) as nomefim
		from pessoa ps,nf n 
		left join pessoa t on( n.idtransportadora = t.idpessoa)
		where ps.idpessoa=n.idpessoa and n.idnf=".$idnf;
$res=d::b()->query($sql) or die('Erro ao buscar dados do pedido sql='.$sql);
$qtdrow=mysqli_num_rows($res);
$row=mysqli_fetch_assoc($res);
//ob_start();
	if($row['impendereco']=='Y'){
		$mm=$mm+65;
	}

	if($row['impitem']=='Y'){
		
		$mm=$mm+95;
		
		$sqli = "SELECT p.descr,p.codprodserv,p.idprodserv,p.un,p.local,i.qtd,i.idnfitem,p.tipo,p.material,
			LENGTH(p.descr) as nometamanho,
		SUBSTRING(p.descr, 1, 30) as descrinicio,
		SUBSTRING(p.descr, 31, 60) as descrfim
			FROM nfitem i,prodserv p
	        where p.idprodserv = i.idprodserv     
	        and i.idnf =".$row['idnf']." order by p.descr";

		$qri = d::b()->query($sqli) or die("Erro ao buscar itens da nota:".mysqli_error()." sql=".$sqli);
		$qtdrowsi= mysqli_num_rows($qri);
		$nval=$qtdrowsi*5;
		$mm=$mm+$nval;
		
	}
	if($row['implocal']=='Y'){
		$mm=$mm+30;
		
		$sqli = "SELECT p.descr,p.codprodserv,p.idprodserv,p.un,p.local,i.qtd,i.idnfitem,p.tipo,p.material,
			LENGTH(p.descr) as nometamanho,
		SUBSTRING(p.descr, 1, 30) as descrinicio,
		SUBSTRING(p.descr, 31, 60) as descrfim
			FROM nfitem i,prodserv p
	        where p.idprodserv = i.idprodserv
	        and i.idnf =".$row['idnf']." order by p.descr";
		$qri = d::b()->query($sqli) or die("Erro ao buscar itens da nota:".mysqli_error()." sql=".$sqli);
		$qtdrowsi= mysqli_num_rows($qri);
		$nval=$qtdrowsi*9;
		$mm=$mm+$nval;
	}

?>
<html>
<head>
<title>Itens do Pedido</title>

<link href="../inc/css/mtorep.css" media="all" rel="stylesheet" type="text/css" />
<style>
.rotulo{
font-weight: bold;
font-size: 10px;
}
.texto{
font-size: 12px;
}
.textoitem{
font-size: 10px;
}
pre{
    margin: 0px
}

ul{
    padding-left: 00px;

}
</style>
<?
   $sqlfig="select * from empresa where 1 ".getidempresa('idempresa','empresa');
        $resfig = mysql_query($sqlfig) or die("Erro ao retornar figura para cabeçalho do relatório: ".mysql_error());
        $figrel=mysql_fetch_assoc($resfig);
        
?>
</head>			
<body>

    <h1>
	DESTINATÁRIO
    </h1>
    <ul>  
<?
if(!empty($row['idendereco']) and $row['impendereco']=='Y'){
?>
            <pre>Nome: <?=$row['nome']?></pre>	
        
	<?if(!empty($row['aoscuidados'])){?>
            <pre>AC: <?=$row['aoscuidados']?></pre>
		
	<?}elseif($row['idcontato']){
		
		$sqlcont="select p.nome
			from pessoa p
			where p.idpessoa =".$row['idcontato'];
		$rescont=d::b()->query($sqlcont) or die("Erro ao buscar informações do contato do pedido sql=".$sqlcont);
		$rowcont=mysqli_fetch_assoc($rescont);
                
		if(!empty($rowcont['nome'])){
                    ?>
            <pre>AC: <?=$rowcont['nome']?></pre>
            <?
		}
	}


	//ENDERECO
	$sqlf="select c.cidade,e.logradouro,e.endereco,e.numero,e.complemento,e.bairro,e.cep,e.uf,e.obsentrega
	from nfscidadesiaf c,endereco e
	where c.codcidade = e.codcidade
	and e.idendereco =".$row['idendereco'];
	$resf=d::b()->query($sqlf) or die("erro ao buscar informações do endereço sql=".$sqlf);
	$rowf=mysqli_fetch_assoc($resf);
        $cep=formatarCEP($rowf["cep"],true);
?>
	
        <pre>Endereço: <?=$rowf["logradouro"]?> <?=$rowf['endereco']?> <?=$rowf["numero"]?> <?=$rowf["complemento"]?> <?=$rowf["bairro"]?> CEP:<?=$cep?></pre>
       
	
	
	<pre>Cidade:<?=$rowf['cidade']?> UF:<?=$rowf["uf"]?></pre>
	<?
	if(!empty($rowf["dddfixo"]) and !empty($rowf["telfixo"])){
	?>
        <pre>Telefone: <?=$rowf["dddfixo"]?>-<?=$rowf["telfixo"]?></pre>
        <?
	}
	if(!empty($rowf['obsentregaa'])){
	?>
            <pre>OBS:<?=$rowf['obsentrega']?></pre>
       <?
	}
        
     
	?>
</ul>
    <h1>REMETENTE</h1>
    <ul>
    <pre><?=$figrel["razaosocial"]?></pre>
    <pre>CPF/CNPJ: <?=formatarCPF_CNPJ($figrel["cnpj"],true)?></pre>
    <pre>I.E: <?=$figrel["inscestadual"]?></pre>
    <pre><?=$figrel["xlgr"]?> - <?=$figrel["nro"]?> Bairro: <?=$figrel["xbairro"]?></pre>
    <pre>CEP:  <?=formatarCEP($figrel["cep"],true)?> ><?=$figrel["xmun"]?>-<?=$figrel["uf"]?>  Tel: (<?=$figrel["DDDPrestador"]?>) <?=$figrel["TelefonePrestador"]?></pre>
</ul>
<!-- ul>
    <pre>LAUDO LABORATORIO AVICOLA UBERLANDIA LTDA</pre>
    <pre>CNPJ: 23.259.427/0001-04</pre>
    <pre>I.E: 702.387.177.0001</pre>
    <pre>Endereço:Rod. BR 365, KM 615 - S/N, Bairro Alvorada</pre>
    <pre>CEP:  38.407-180 Uberlandia-MG  Tel: (34) 3222-5700</pre>
</ul -->
<?
}
?>
    <ul>
<?
if($row['impitem']=='Y'){
?>	
 <pre>N CONTROLE:</td> <td><?=$row['idpedido']?></pre>
 <pre>N PEDIDO (CLIENTE):</td> <td><?=$row['pedidoext']?></pre>
 <pre>CLIENTE: </td> <td><?=$row['nome']?></pre>
<?
	
	if(!empty($row['aoscuidados'])){
?>	
 <pre>AC: <?=$row['aoscuidados']?></pre>
 <?
	}elseif($row['idcontato']){
		
		$sqlcont="select p.nome
			from pessoa p
			where p.idpessoa =".$row['idcontato'];
		$rescont=d::b()->query($sqlcont) or die("Erro ao buscar informações do contato do pedido sql=".$sqlcont);
		$rowcont=mysqli_fetch_assoc($rescont);
		if(!empty($rowcont['nome'])){
?>
    <pre>AC: <?=$rowcont['nome']?></pre>
    <?
		}
	}	
	if(!empty($row['transporte'])){
?>            

    <pre>TRANSPORTE:<?=$row['transporte']?></pre>
    <?
	}
?>
    </ul>
    <h1>ITENS SOLICITADOS</h1>
    <table class='normal'>
    <?
$sqli = "SELECT p.descr,p.codprodserv,p.idprodserv,p.un,p.local,i.qtd,i.idnfitem,p.tipo,p.material,
			LENGTH(p.descr) as nometamanho,
		UPPER(SUBSTRING(p.descr, 1, 49)) as descrinicio,
		UPPER(SUBSTRING(p.descr, 50, 99)) as descrfim
			FROM nfitem i,prodserv p
	        where p.idprodserv = i.idprodserv
	        and i.idnf =".$row['idnf']." order by p.descr";
	$qri = d::b()->query($sqli) or die("Erro ao buscar itens da nota:".mysqli_error()." sql=".$sqli);
	$qtdrowsi= mysqli_num_rows($qri);
	if($qtdrowsi>0){
	
		$i=0;
		while ($rowi = mysqli_fetch_array($qri)){
			$i=$i+1;
?>
				<tr class="res" ><td style="width: 8px;"><?=number_format($rowi["qtd"], 0, '', '.')?></td><td><?=$rowi['descr']?></td></tr>
<?		
		}
?>		
                <tr class="res" style="width: 8px;" ><td><?=$i?></td><td>Iten(s)</td></tr>
<?                 
	}
        ?>
	</table>

     <br>
        <h1>EMITENTE</h1>
            <ul>
                <pre><?=$figrel["razaosocial"]?></pre>
                <pre>CPF/CNPJ: <?=formatarCPF_CNPJ($figrel["cnpj"],true)?></pre>
                <pre>I.E: <?=$figrel["inscestadual"]?></pre>
                <pre><?=$figrel["xlgr"]?> - <?=$figrel["nro"]?> Bairro: <?=$figrel["xbairro"]?></pre>
                <pre>CEP:  <?=formatarCEP($figrel["cep"],true)?> ><?=$figrel["xmun"]?>-<?=$figrel["uf"]?>  Tel: (<?=$figrel["DDDPrestador"]?>) <?=$figrel["TelefonePrestador"]?></pre>
            </ul>
	<!--ul>
      	<pre>LAUDO LABORATORIO AVICOLA UBERLANDIA LTDA</pre>
	<pre>CNPJ: 23.259.427/0001-04 - I.E: 702.387.177.0001</pre>
	<pre>ROD. BR 365, KM 615 - S/N, BAIRRO ALVORADA</pre>
	<pre>UBERLANDIA-MG - CEP: 38.407-180 - TEL: (34) 3222-5700</pre>
        </ul-->
<?
}

if($row['implocal']=='Y'){
    
	//Alterada o campo ,pl.qtdd as qtddsol, acrescentei um d pois estava trazendo o qtdsol duas vezes e o ultimo trazia dados nulos.
	//Lidiane - 08/05/2020 - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=316551
	// Acrescentado and l.status <> 'CANCELADO' para não imprimir lotes cancelados.
	$sqlp = "SELECT 
			UPPER(p.descr) as descr,
			LENGTH(p.descr) as nometamanho,		
			UPPER(SUBSTRING(p.descr, 1, 49)) as descrinicio,
			UPPER(SUBSTRING(p.descr, 50, 99)) as descrfim,			
			p.codprodserv,p.idprodserv,p.un,p.local,l.partida,l.partidaext,
			concat(l.partida,'/',l.exercicio) as partida,dma(l.fabricacao) as dataf,dma(l.vencimento) as datav,l.qtddisp,
			l.status,pl.qtdd as qtddsol,i.idnfitem,l.idlote,
			-- lc.tipoobjeto as tipoobjetolc,
			-- lc.idobjeto as idobjetolc,
			pl.*                                 
			FROM nfitem i,
                        prodserv p,
                        lotecons pl,
                        lote l 
			-- left join lotelocalizacao lc ON (lc.idlote = l.idlote and lc.idobjeto is not null)           
	        where l.idlote = pl.idlote
	        and p.idprodserv = i.idprodserv
		and pl.qtdd > 0
		and l.status <> 'CANCELADO'
		and pl.tipoobjeto = 'nfitem'
	        and pl.idobjeto =i.idnfitem
		and i.idnf = ".$row['idnf']." order by p.descr";
	
	$qrp = d::b()->query($sqlp) or die("Erro ao buscar itens do pedido:".mysqli_error());
        ?>
    <h1>ITENS SOLICITADOS</h1>
    <table class="normal">
       
<?	
	$i=0;
	while ($rowp = mysqli_fetch_array($qrp)){
		$i = $i+1;
?>
         <tr class="res" ><td><?$rowp['descr']?></td></tr>
<?		
                
                            $sqlloc="select * from lotelocalizacao lc where lc.idlote = ".$rowp['idlote']." and lc.idobjeto is not null";
                            $resloc = d::b()->query($sqlloc);
                            $qtdlocal=0;
                            while($rowloc=mysqli_fetch_assoc($resloc)){

                                if($rowloc['tipoobjeto']=="pessoa" and !empty($rowloc['idobjeto'])){
                                    $qtdlocal=1;
                                    $sqle="select nomecurto from pessoa where idpessoa=".$rowloc['idobjetolc'];
                                    $rese=d::b()->query($sqle) or die("erro ao buscar pessoa sql=".$sqle);
                                    $rowe=mysqli_fetch_assoc($rese);
?>
         <tr class="res" ><td><?=$rowe['nomecurto']?></td></tr>
<?
                                 }elseif($rowloc['tipoobjeto']=='tagdim' and !empty($rowloc['idobjeto'])){

                                        $sloc="select p.idtagdim,concat(l.descricao,' ',concat(case p.coluna 
                                                when 0 then '0' when 1 then 'A'	when 2 then 'B' when 3 then 'C' when 4 then 'D'	when 5 then 'E'	when 6 then 'F'
                                                when 7 then 'G' when 8 then 'H' when 9 then 'I' when 10 then 'J' when 11 then 'K' when 12 then 'L'
                                                when 13 then 'M' when 14 then 'N' when 15 then 'O' when 16 then 'P' when 17 then 'Q' when 18 then 'R'
                                                when 19 then 'S' when 20 then 'T' when 21 then 'U' when 22 then 'V' when 23 then 'X' when 24 then 'Z'
                                                 end,' ',p.linha) )as campo
                                            from tag l,tagdim p,unidade u
                                            WHERE p.idtagdim= ".$rowloc['idobjeto']."
                                                and l.idunidade=u.idunidade
                                                and u.status='ATIVO'
                                                and u.idtipounidade in (3,8,21)
                                                and p.idtag = l.idtag";

                                        $rel = d::b()->query($sloc) or die("Erro ao buscar localização dos lotes:".mysqli_error(d::b())."sql=".$sloc);
                                        $qtdloc= mysqli_num_rows($rel);
                                        if($qtdloc>0){
                                            $qtdlocal=1;
                                            $rloc= mysqli_fetch_assoc($rel);
                                            $local=$rloc['campo'];
?>
         <tr class="res" ><td><?=$local?></td></tr>
<?                                            
                                        }

                                }elseif(($rowloc['tipoobjeto']=='tagbotijao' or $rowloc['tipoobjeto']=='tagsala') and !empty($rowloc['idobjeto'])){

                                        $sloc="select idtag,concat(descricao,'- TAG ',tag) as campo
                                                        from tag t,unidade u
                                                        where t.idtag= ".$rowloc['idobjeto']."  
                                                            and t.idunidade=u.idunidade
                                                            and u.status='ATIVO'
                                                           and u.idtipounidade in (3,8,21)";

                                        $rel = d::b()->query($sloc) or die("Erro ao buscar localização botijao dos lotes:".mysqli_error(d::b())."sql=".$sloc);
                                        $qtdloc= mysqli_num_rows($rel);
                                        if($qtdloc>0){
                                            $qtdlocal=1;
                                            $rloc= mysqli_fetch_assoc($rel);
                                            $local=$rloc['campo'];
?>
         <tr class="res" ><td><?=$local?></td></tr>
<?         
                                        }

                                }
                            }  
                            if( $qtdlocal==0){
                                $local="Localização não informada.";
                                ?>
         <tr class="res" ><td><?=$local?></td></tr>
                                <?
                            }
?>
         <tr class="res" ><td><?=number_format($rowp["qtddsol"], 0, '', '.')?> - <?=$rowp['partida']?><?if(!empty($rowp['partidaext'])){?> - <?=$rowp['partidaext']?> <?}?> </td></tr>

			<tr class="res" ><td>--------------------------------------------------------</td></tr>
<?		
	}
?>	
	
                        <tr class="res" ><td>Qtd. Item:<?=$i?></td></tr>
<?
}
?>    
    
    </table>
	<br>
	<?if($_SESSION["SESSAO"]["IDEMPRESA"]==1){?>
    <ul>
        <pre>Para mais informações, dívidas e sugestões, entre em contato conosco atraves do: TEL.: (34) 3222-5700, (34) 9 9942-2028</pre>
        <pre>PRODUTO INATA: vendas@inata.com.br</pre>
        <pre>MATERIAL DE COLETA: material@laudolab.com.br</pre>
	<pre>*** Devido a questões de tempo de preparo de materiais e de logística, sugerimos que mantenham controle de estoque de produtos e/ou materiais de coleta,
	 solicitando-os com antecedencia. Obrigado! ***</pre>
    </ul>
 <?	
	}
	$sqlpr="select dma(p.prazo) as prazo,pr.nomecurto 
			from nf p left join pessoa pr on(pr.idpessoa = p.respenvio)
			where p.idnf =".$row['idnf'];
	$respr=d::b()->query($sqlpr) or die("Erro ao buscar informações do preparo do pedido sql=".$sqlpr);
	$rowpr=mysqli_fetch_assoc($respr);
?>	
        
        <!-- ul>PREPARADO POR: <?=$rowpr['nomecurto'].'  '.$rowpr['prazo']?></ul -->
	<p>	


</body>
</html>
<?

//if($_GET['gerapdf']=='Y'){
/*
	$html = ob_get_contents();
	//limpar o codigo html
	
	$html = preg_replace('/>\s+</', "><", $html);
	ob_end_clean();
	
	//Inclusão da biblioteca DOMPDF
	require_once "../inc/php/composer/vendor/dompdf/dompdf/src/Autoloader.php";
	Dompdf\Autoloader::register();
	use Dompdf\Dompdf;

	// Instanciamos a classe
	$dompdf = new Dompdf();
	 
	// Passamos o conteúdo que será convertido para PDF
	$dompdf->loadHtml($html);
	 
	// Definimos o tamanho do papel e
	// sua orientação (retrato ou paisagem)
	$dompdf->setPaper('A4', 'portrait');
	 
	// O arquivo é convertido
	$dompdf->render();
	
	
        $dompdf->stream("pedido_".$idnf.".pdf");
*/	
//}
?>