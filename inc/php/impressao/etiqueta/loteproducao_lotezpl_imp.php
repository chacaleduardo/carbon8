<?

$idunidadepadrao = getUnidadePadraoModulo($_MOD,cb::idempresa());
if (!empty($_OBJ['idloc'])) {
    $str = " and c.idlotelocalizacao=".$_OBJ['idloc'];
}

      $sql="select concat(l.spartida,l.npartida,'/',l.exercicio) as descr,p.descr as produto,
      LEFT(p.descr,40) as nomeinicio,LEFT(SUBSTRING(p.descr,41),40) as nomemeio,
      SUBSTRING(p.descr,81) as nomefim,
      l.criadoem as criadoem,
      dma(l.vencimento) as vencimento,l.idempresa,e.sigla,concat(t.descricao,' ',concat(case tp.coluna 
      when 0 THEN '0' when 1 then 'A'	when 2 then 'B' when 3 then 'C' when 4 then 'D'	when 5 then 'E'	when 6 then 'F'
      when 7 then 'G' when 8 then 'H' when 9 then 'I' when 10 then 'J' when 11 then 'K' when 12 then 'L'
      when 13 then 'M' when 14 then 'N' when 15 then 'O' when 16 then 'P' when 17 then 'Q' when 18 then 'R'
      when 19 then 'S' when 20 then 'T' when 21 then 'U' when 22 then 'V' when 23 then 'X' when 24 then 'Z'
       end,' ',tp.linha) )as campo
          from lote l 
          join prodserv p on (p.idprodserv = l.idprodserv)
          join empresa e on (l.idempresa = e.idempresa)
          left join lotelocalizacao c on (c.idlote=l.idlote and c.tipoobjeto ='tagdim')
          left join tagdim tp on (tp.idtagdim= c.idobjeto)
          left join tag t on (tp.idtag = t.idtag)
          where  l.idlote=".$_OBJ['idlote'].$str."
          and t.idunidade=".$idunidadepadrao;


$res=d::b()->query($sql);



if($res and mysqli_num_rows($res) > 0){
    
	while($row=mysql_fetch_assoc($res)){
            
            $_CONTEUDOIMPRESSAO .= "^XA
            ^CF0,100,60
            ^FO80,20^FH\^FD".$row["sigla"]."^FS
            ^FO150,10^BQN,2,2,M^FDMA,https://sislaudo.laudolab.com.br/?_modulo=".$_MOD."&_acao=u&idlote=".$_OBJ['idlote']."^FS^FX
            ^CF0,25
            ^FO235,20^FH\^FD".retira_acentos($row['descr'])."^FS
            ^FO235,60^FH\^FDV: ".$row['vencimento']."^FS
            ^CF0,19
            ^FO80,105^FB430,5,,^FH\^FD".retira_acentos($row['produto'])."^FS
            ^CF0,17
            ^FO80,200^FH\^FDData Lote: ".retira_acentos(dma($row['criadoem']))."^FS
            ^CF0,17
            ^FO80,215^FB430,2,,^FH\^FDLocal: ".retira_acentos($row['campo'])."^FS
            ^XZ";				
            
		

		                      
		$_CONTEUDOIMPRESSAO.="%_quebrapagina_%";
	}
}
?>