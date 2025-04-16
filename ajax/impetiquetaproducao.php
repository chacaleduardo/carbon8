<?require_once("../inc/php/validaacesso.php");

$idlote= $_GET['idlote'];
$intipo=$_GET['intipo'];


if(empty($_GET['qtd'])){
	$qtd = 1;
}else{
	$qtd=$_GET['qtd'];
}

// Fazer teste de layout em http://labelary.com/viewer.html

if(empty($idlote) OR empty($intipo)){
    die("Erro ao idobjeto não enviado para impressão.");
}

/*
 * ETIQUETA 1 
 * PRODUTO
 * PARTIDA E VENCIMENTO
 * CLIENTE 
 * SEMENTES
 * TEXTO DA FORMALIZACAO
 * 
 * ################
 * ETIQUETA 2
 * SEMENTE
 * CLIENTE
 * PARTIDA DO PAI
 */

$sqlimp="select ip from tag 
            where varcarbon='IMPRESSORA_PRODUCAO'
            and ip is not null 
            and status=	'ATIVO'";
$resimp=d::b()->query($sqlimp) or die("Erro ao buscar impressora do produção zebra: ".mysqli_error(d::b()));
$qtdimp=mysqli_num_rows($resimp);
//if($qtdimp<1){die("Não encontrada impressora de produção zebra em tags var carbon IMPRESSORA_PRODUCAO.");}
$rowimp=mysqli_fetch_assoc($resimp);
define("_IP_IMPRESSORA_PRODUCAO",$rowimp['ip']);

$sqlimp="select ip from tag 
            where varcarbon='IMPRESSORA_PRODUCAO_2'
            and ip is not null 
            and status=	'ATIVO'";
$resimp=d::b()->query($sqlimp) or die("Erro ao buscar impressora do produção zebra: ".mysqli_error(d::b()));
$qtdimp=mysqli_num_rows($resimp);
//if($qtdimp<1){die("Não encontrada impressora de produção zebra em tags var carbon IMPRESSORA_PRODUCAO_2.");}
$rowimp=mysqli_fetch_assoc($resimp);
define("_IP_IMPRESSORA_PRODUCAO_2",$rowimp['ip']);

$sqlimp1="select ip from tag 
            where varcarbon='IMPRESSORA_PRODUCAO_SEM'
            and ip is not null 
            and status=	'ATIVO'";
$resimp1=d::b()->query($sqlimp1) or die("Erro ao buscar impressora do producao sementes: ".mysqli_error(d::b()));
$qtdimp1=mysqli_num_rows($resimp1);
//if($qtdimp1<1){die("Não encontrada impressora do producao sementes em tags var carbon IMPRESSORA_PRODUCAO_SEM.");}
$rowimp1=mysqli_fetch_assoc($resimp1);
define("_IP_IMPRESSORA_PRODUCAO_SEM",$rowimp1['ip']);

$sqlimp2="select ip from tag 
            where varcarbon='IMPRESSORA_PRODUCAO_SEM2'
            and ip is not null 
            and status=	'ATIVO'";
$resimp2=d::b()->query($sqlimp2) or die("Erro ao buscar impressora do producao sementes: ".mysqli_error(d::b()));
$qtdimp2=mysqli_num_rows($resimp2);
//if($qtdimp2<1){die("Não encontrada impressora do producao sementes em tags var carbon IMPRESSORA_PRODUCAO_SEM2.");}
$rowimp2=mysqli_fetch_assoc($resimp2);
define("_IP_IMPRESSORA_PRODUCAO_SEM2",$rowimp2['ip']);

$sqlimp2="select ip from tag 
            where varcarbon='IMPRESSORA_PRODUCAO_SEM3'
            and ip is not null 
            and status=	'ATIVO'";
$resimp2=d::b()->query($sqlimp2) or die("Erro ao buscar impressora do producao sementes: ".mysqli_error(d::b()));
$qtdimp2=mysqli_num_rows($resimp2);
//if($qtdimp2<1){die("Não encontrada impressora do producao sementes em tags var carbon IMPRESSORA_PRODUCAO_SEM3.");}
$rowimp2=mysqli_fetch_assoc($resimp2);
define("_IP_IMPRESSORA_PRODUCAO_SEM3",$rowimp2['ip']);

$sqlimp3="select ip from tag 
            where varcarbon='IMPRESSORA_MEIOS'
            and ip is not null 
            and status=	'ATIVO'";
$resimp3=d::b()->query($sqlimp3) or die("Erro ao buscar impressora do produção zebra: ".mysqli_error(d::b()));
$qtdimp3=mysqli_num_rows($resimp3);
//if($qtdimp3<1){die("Não encontrada impressora de produção zebra em tags var carbon IMPRESSORA_MEIOS.");}
$rowimp3=mysqli_fetch_assoc($resimp3);
define("_IP_IMPRESSORA_MEIOS",$rowimp3['ip']);

$sqlimp4="select ip from tag 
            where varcarbon='IMPRESSORA_INCUBACAO'
            and ip is not null 
            and status=	'ATIVO'";
$resimp4=d::b()->query($sqlimp4) or die("Erro ao buscar impressora do produção zebra: ".mysqli_error(d::b()));
$qtdimp4=mysqli_num_rows($resimp4);
//if($qtdimp4<1){die("Não encontrada impressora de produção zebra em tags var carbon IMPRESSORA_INCUBACAO.");}
$rowimp4=mysqli_fetch_assoc($resimp4);
define("_IP_IMPRESSORA_INCUBACAO",$rowimp4['ip']);

//echo _IP_IMPRESSORA_PRODUCAO_SEM;
//echo "<br>";
//echo _IP_IMPRESSORA_PRODUCAO;die;
//die($sqlimp1);

if($intipo=='lote' or $intipo=='tipo2' or $intipo=='tipo5'or $intipo=='tipo5b' or $intipo=='tipo7' or $intipo=='tipo10'or $intipo=='tipo15'){

	if($intipo=='lote' or $intipo=='tipo10'){
		$sql="select concat('PART: ',l.partida,'/',l.exercicio) as partida,pd.descr,p.idpessoa,
            LEFT(p.nome,52) as nomeinicio,SUBSTRING(p.nome,53) as nomefim,
            case pd.venda 
            when 'x' then  upper(concat('FAB: ',LEFT(DATE_FORMAT(l.fabricacao, '%M'),3),'/',right(DATE_FORMAT(l.fabricacao, '%Y'),2)))
            else concat('FAB: ',dma(l.fabricacao))
            END as fabricacao,
			case pd.venda 
            when 'x' then upper(concat('VENC: ',LEFT(DATE_FORMAT(l.vencimento, '%M'),3),'/',right(DATE_FORMAT(l.vencimento, '%Y'),2))) 
            else concat('VENC: ',dma(l.vencimento))
            END as vencimento,      
			concat(sf.idsolfab,'-',sl.partida,'/',sl.exercicio) as solfab,l.qtdprod,l.qtdprod_exp,pd.venda
            from lote l 
			left join pessoa p on(p.idpessoa=l.idpessoa) 
			left join prodserv pd on(l.idprodserv= pd.idprodserv)
			left join solfab sf on(sf.idsolfab = l.idsolfab)
			left join lote sl on(sf.idlote =sl.idlote)
            where l.idlote=".$idlote;
    
		//buscar as sementes
		$sql1="	select  concat(l.partida,'/',l.exercicio) as semente
					from lotecons c 
					join lote l on(c.idlote=l.idlote and l.tipoobjetosolipor='resultado' ) 
					join prodserv p on (p.idprodserv=l.idprodserv and p.especial ='Y')
					where  qtdd >0
					and c.tipoobjeto='lote'
					and c.idobjeto =".$idlote;
		
		// observações da formalizacao
		$sql2="select descr,qtd from loteobj o 
			where o.idlote=".$idlote." 
			and o.qtd >0";
			
	}else if($intipo=='tipo2' or $intipo=='tipo7'){
	 	$sql="select l.idempresa,concat('LOTE: ',l.partida,'/',l.exercicio) as partida,pd.descr,p.idpessoa,
            LEFT(p.nome,52) as nomeinicio,SUBSTRING(p.nome,53) as nomefim,
            case pd.venda 
            when 'x' then  upper(concat('FABRICACAO: ',LEFT(DATE_FORMAT(l.fabricacao, '%M'),3),'/',right(DATE_FORMAT(l.fabricacao, '%Y'),2)))
            else concat('FABRICACAO: ',dma(l.fabricacao))
            END as fabricacao,
			case pd.venda 
            when 'x' then upper(concat('VENCIMENTO: ',LEFT(DATE_FORMAT(l.vencimento, '%M'),3),'/',right(DATE_FORMAT(l.vencimento, '%Y'),2))) 
            else concat('VENCIMENTO: ',dma(l.vencimento))
            END as vencimento,      
			concat(sf.idsolfab,'-',sl.partida,'/',sl.exercicio) as solfab,l.qtdprod,l.qtdprod_exp,pd.venda
            from lote l 
			left join pessoa p on(p.idpessoa=l.idpessoa) 
			left join prodserv pd on(l.idprodserv= pd.idprodserv)
			left join solfab sf on(sf.idsolfab = l.idsolfab)
			left join lote sl on(sf.idlote =sl.idlote)
			where l.idlote=".$idlote;
			
		$sql3="SELECT concat(p.volumeformula,' ',p.un) as formula 
		from lote l join prodservformula p on (l.idprodservformula = p.idprodservformula) where l.idlote =".$idlote;
	}else if($intipo=='tipo5'|| $intipo=='tipo5b'){
		$sql="select concat('PARTIDA: ',l.partida,'/',l.exercicio) as partida,p.idpessoa,
			LEFT(pd.descr,50) as descrinicio,SUBSTRING(pd.descr,51,50) as descrfim,
			LEFT(p.nome,50) as nomeinicio,SUBSTRING(p.nome,51,50) as nomefim,
            case pd.venda 
            when 'x' then  upper(concat('FABRICACAO: ',LEFT(DATE_FORMAT(l.fabricacao, '%M'),3),'/',right(DATE_FORMAT(l.fabricacao, '%Y'),2)))
            else concat('FABRICACAO: ',dma(l.fabricacao))
            END as fabricacao,
			case pd.venda 
            when 'x' then upper(concat('VENCIMENTO: ',LEFT(DATE_FORMAT(l.vencimento, '%M'),3),'/',right(DATE_FORMAT(l.vencimento, '%Y'),2))) 
            else concat('VENCIMENTO: ',dma(l.vencimento))
            END as vencimento,      
			concat(sf.idsolfab,'-',sl.partida,'/',sl.exercicio) as solfab,l.qtdprod,l.qtdprod_exp,pd.venda, pd.un
            from lote l 
			left join pessoa p on(p.idpessoa=l.idpessoa) 
			left join prodserv pd on(l.idprodserv= pd.idprodserv)
			left join solfab sf on(sf.idsolfab = l.idsolfab)
			left join lote sl on(sf.idlote =sl.idlote)
			where l.idlote=".$idlote;
			
	} else if($intipo=='tipo15'){

		$sql="select concat(l.spartida,l.npartida,'/',l.exercicio) as descr,p.descr as produto,
		LEFT(p.descr,30) as nomeinicio,LEFT(SUBSTRING(p.descr,31),30) as nomemeio,
		SUBSTRING(p.descr,61) as nomefim,
		dma(l.vencimento) as vencimento
		from lote l ,prodserv p
		where p.idprodserv = l.idprodserv
		and l.idlote=".$idlote;
		
		$resl=d::b()->query($sql) or die("Erro ao buscar informações do lote para impressão: ".mysqli_error(d::b()));

		$qrow1=mysqli_num_rows($resl);
		
		$tpag=ceil($qrow1/7);
		
		if($qrow1==0){
			die("Nenhum resultado encontrado para impressão das Etiquetas");
		}
			
	}
    
}else{
    if($intipo=='lotecons' or $intipo=='tipo11'){
		$sql="select  s.partida as semente,s.exercicio as  exerciciosem,ps.descr as descrsemente,l.partida,l.exercicio,LEFT(p.nome,52) as nome,SUBSTRING(p.nome,53) as nomefim
            from lotecons c,lote s,prodserv ps,lote l,resultado r,amostra a,pessoa p
             where c.idlotecons =  ".$idlote."
             and s.idlote = c.idlote
             and ps.idprodserv=s.idprodserv
             and c.idobjeto = l.idlote
             and c.tipoobjeto = 'lote'
             and s.idobjetosolipor = r.idresultado
             and s.tipoobjetosolipor = 'resultado'
             and r.idamostra = a.idamostra
             and p.idpessoa = a.idpessoa";
	//echo('Etiqueta 2'); die;
	
	}else if($intipo=='tipo3'){ //tipo3
		$sql="SELECT 
			p.idprodserv,
			if(p.descrcurta <> '',p.descrcurta,p.descr) as descr,
			LEFT(if(p.descrcurta <> '',p.descrcurta,p.descr),22) as descrinicio,
			SUBSTRING(if(p.descrcurta <> '',p.descrcurta,p.descr),23) as descrfim,
			CONCAT(l.partida, '/', l.exercicio) AS partida,
			DMA(l.fabricacao) AS fabricacao,
			DMA(l.vencimento) AS vencimento
		FROM
			lote l
				JOIN
			prodserv p ON (l.idprodserv = p.idprodserv)
		WHERE
			l.idlote = ".$idlote;

		$sql3="SELECT concat(p.volumeformula,' ',p.un) as formula 
		from lote l join prodservformula p on (l.idprodservformula = p.idprodservformula) where l.idlote =".$idlote;
	}else if($intipo=='tipo8' or $intipo=='tipo9'){ // tipo8 ou tipo9
		$sql = "SELECT 
				p.nome, CONCAT(l.partida, '/', l.exercicio) AS partida
			FROM
				lote l
					JOIN
				pessoa p ON (l.idpessoa = p.idpessoa)
			WHERE
				l.idlote = ".$idlote;
	} elseif($intipo=='tipo12' or $intipo=='tipo13' or $intipo=='tipo14'){
		$sql = "SELECT distinct(CONCAT('REG: ', r.idamostra)) AS registro,
					   CONCAT('PART: ', l.partida, '/', RIGHT(l.exercicio, 2)) AS partida,
					   p.idpessoa,
					   LEFT(p.nome, 52) AS nomeinicio,
					   SUBSTRING(p.nome, 53) AS nomefim
				  FROM lote l lEFT JOIN pessoa p ON (p.idpessoa = l.idpessoa)
				  JOIN loteativ la ON la.idlote = l.idlote
				  JOIN objetovinculo ov ON ov.idobjetovinc = la.idloteativ AND ov.tipoobjetovinc = 'loteativ'
				  JOIN resultado r ON r.idresultado = ov.idobjeto AND ov.tipoobjeto = 'resultado'
				 WHERE l.idlote = ".$idlote;
	}
}
//die($sql);
$res=d::b()->query($sql) or die("Erro ao buscar informações da partida para impressão: ".mysqli_error(d::b()));

$qrow=mysqli_num_rows($res);

$tpag=ceil($qrow/7);

if($qrow==0){
	die("Nenhum resultado encontrado para impressão das Etiquetas");
}

	
if($intipo=='lote' or $intipo=='tipo2' or $intipo=='tipo5'or $intipo=='tipo5b' or $intipo=='tipo7' or $intipo=='tipo10'or $intipo=='tipo15'){

	if($intipo=='lote' or $intipo=='tipo10'){
		$row=mysqli_fetch_assoc($res);


		//codigo para criar uma linha na etiqueta ^FO50,20^GB700,1,3^FS
		$cabecalho="^XA^CF0,24"; 
		$rodape="^XZ";

		$strprint=$cabecalho;

		if(empty($row["idpessoa"])){
			$row['nomeinicio']="  INATA PRODUTOS - BIOLOGICOS ";

		}
		$valprod=recuperaExpoente(tratanumero($row['qtdprod']),$row['qtdprod_exp']);
		$strprint.='^FO90,20^FD'.retira_acentos($row['nomeinicio']).'^FS';	
		$strprint.='^FO90,60^FD'.retira_acentos($row['nomefim']).'^FS';	
		$strprint.='^FO90,100^FD'.$row['partida'].' '.$row['fabricacao'].' '.$row['vencimento'].'^FS';
		$strprint.='^FO90,140^FD'.retira_acentos($row['descr']).'^FS';
		$strprint.='^FO90,180^FDQUANTIDADE PRODUZIDA: '.$valprod.' SF.: '.$row['solfab'].'^FS';

		$resl=d::b()->query($sql1) or die("Erro ao buscar informações das sementes para impressão: ".mysqli_error(d::b()));
		$qrow1=mysqli_num_rows($resl);
		if($qrow1>0){
			$strprint.='^FO90,250^FD';	
			$sem=0;
			$tamanho=250;
			while($row1=mysqli_fetch_assoc($resl)){
				$sem=$sem+1;
				if($sem==6){
					$sem=0;
					$tamanho=$tamanho+40;
					$strprint.='^FS^FO90,'.$tamanho.'^FD';
				}
				$strprint.=''.retira_acentos($row1['semente']).' ';	
			}
			$strprint.='^FS';	
		}

		$strprint.=$rodape;	
		$j = 0;
		while($j < $qtd){
			imprimir($strprint,$intipo);
			$j++;
		}

	}else if($intipo=='tipo2' or $intipo=='tipo7'){
		$row=mysqli_fetch_assoc($res);

		//codigo para criar uma linha na etiqueta ^FO50,20^GB700,1,3^FS
		$cabecalho="^XA^CF0,24"; 
		$rodape="^XZ";

		$strprint=$cabecalho;

		$strprint.='^FO30,20^FDQTD:^FS';
		$strprint.='^FO400,0^FD^GB1,70,3^FS';
		$strprint.='^FO10,70^FD^GB800,1,2^FS';
		if($row["idempresa"] == 1){
			//$strprint.='^FO10,50^FD^IL '._CARBON_ROOT.'inc/img/logolateral.jpg'.'^FS';
			$strprint.='^FO450,0^FD^GFA,1944,1944,27,,:M01,M03,M07,:M0F,L01F1,L01F38,L03F7C,L07FFE,L0IFE,L0JF,K01JF8,K03JFC,K07JFC,K07JFE,K0LF,J01LFM07EK07F800FC00F807FEL0FFC,J03FDJF8L07EK07F800FC00F807IF8I03IF,J03F9JF8L07EK0FF800FC00F807IFEI0JF8,J07FBJF8L07EK0FFC00FC00F807JF001JFC,J0NFL07EK0FFC00FC00F807JF803FE1FE,I01JFE7FF8K07EJ01FFC00FC00F807C03FC03F807F,I01JFC3FF8K07EJ01F7E00FC00F807C01FE07F003F,I03JF81FFCK07EJ03F3E00FC00F807C00FE07E003F8,I07JF02FFEK07EJ03F3E00FC00F807C007E0FE003F8,I07IFE00IFK07EJ07E3F00FC00F807C007F0FE001F8,I0JFE007FFK07EJ07E1F00FC00F807C007F0FE001F8,001JFE003FF8J07EJ07C1F80FC00F807C003F0FE001FC,003JF8001FFCJ07EJ0FC1F80FC00F807C003F0FC001FC,003JFI03FFCJ07EJ0FC0F80FC00F807C003F0FE001FC,007JFJ0FFEJ07EJ0F80FC0FC00F807C007F0FE001F8,00FEFFEE0A17FEJ07EI01F80FC0FC00F807C007F0FE001F8,01FCFFCJ03FEJ07EI01JFC0FC01F807C007E0FE001F8,01F8JF83IFEJ07EI01JFE0FC01F807C00FE07E003F8,03F9JF83JFJ07EI03JFE07E01F807C01FE07F003F,03F1JF83JFJ07EI03KF07E03F807C03FC03F807F,07E1JF83JFJ07IFC7F003F07F87F007JF803FC1FE,07E3FDFF83JFJ07IFC7E001F83JF007JF001JFC,07PFDFFC007IFC7E001F81IFE007IFEI0JF8,0TFC007IFCFC001F80IFC007IF8I03IF,0TFC007IFCFCI0FC03FFI07FEL0FFC,0TFE,1PF7IFE,1OFEJFEV08,1KFDNFE004I0CN01003J0EM02,3UF004I0CL01O0EM02,3UF004070D83048C3C30D30C00B319061821C,7UF004008JC60310CCEI301919918I206,7UF804018C4C44031084C3230I1911063206,7UF804088C4C44331084C3230118913063I2,7UF804188C4C44331084C3230308E11023262,7UFC060D8EC684131848C3120208E11936236,0UFC,,::::::::::::::::^FS';
			
		}

		$strprint.='^FO30,80^FDPRODUTO:'.retira_acentos($row['descr']).'^FS';	
		$strprint.='^FO10,120^FD^GB800,1,2^FS';

		$strprint.='^FO30,130^FD'.$row['partida'].'^FS';
		$strprint.='^FO10,170^FD^GB800,1,2^FS';

		$strprint.='^FO30,180^FD'.$row['fabricacao'].'^FS';
		$strprint.='^FO10,220^FD^GB800,1,2^FS';

		$strprint.='^FO30,230^FD'.$row['vencimento'].'^FS';
		$strprint.='^FO10,270^FD^GB800,1,2^FS';

		$res3=d::b()->query($sql3) or die("Erro ao buscar informações da fórmula para impressão: ".mysqli_error(d::b()));
		$qrow3=mysqli_num_rows($res3);
		if($qrow3>0){
			$row3=mysqli_fetch_assoc($res3);
			$strprint.='^FO30,280^FDVOLUME: '.strtoupper($row3['formula']).'^FS';
		}
		
		$strprint.=$rodape;	
		$j = 0;
		while($j < $qtd){
			imprimir($strprint,$intipo);
			$j++;
		}
	}else if ($intipo=='tipo5b'){

		$row=mysqli_fetch_assoc($res);

		//codigo para criar uma linha na etiqueta ^FO50,20^GB700,1,3^FS
		$cabecalho="^XA^CF0,24"; 
		$rodape="^XZ";

		$strprint=$cabecalho;

		if(empty($row["idpessoa"])){
			$row['nomeinicio']="INATA PRODUTOS - BIOLOGICOS";
		}

		$valprod=recuperaExpoente(tratanumero($row['qtdprod']),$row['qtdprod_exp']);
		
		if(!empty($row['nomeinicio'])){
			$nInicio=retira_acentos($row['nomeinicio']);			
		}

		if(!empty($row['descrinicio'])){
			$descInicio=retira_acentos($row['descrinicio']);			
		}
		if(!empty($row['descrfim'])){
			$descFim=retira_acentos($row['descrfim']);
		}
		if(!empty($row['partida'])){
			$partida=$row['partida'].'^FS';
		}
		if(!empty($row['fabricacao'])){
			$fab=$row['fabricacao'];
			$venc=$row['vencimento'];
		}
		if(!empty($valprod)){
			$und=$row['un'];
		}
		$margemStr=90;
		$nSquare=20;
		$sc=1;
		$prinSquare = "";
		$squareMarginTopLine1 = 255;
		$squareMarginTopLine2 = 284;
		$squareMarginLeft = 100;
		$squareWide = 30;
		while($nSquare >= $sc){
			$prinSquare .= "^FX^FO".$squareMarginLeft.",".$squareMarginTopLine1."^GB32,32,3^FS";
			$prinSquare .= "^FX^FO".$squareMarginLeft.",".$squareMarginTopLine2."^GB32,32,3^FS";
			$squareMarginLeft = $squareMarginLeft+$squareWide;
			$sc++;
		}

		$strprint= "
		^XA^CF0,20
				
		^FO".$margemStr.",25^FD".$nInicio."^FS		
		^FB620,20^FO".$margemStr.",55^FD".$descInicio." ".$descFim."^FS		
		^FO".$margemStr.",130^FD".$partida."^FS
		^FO".$margemStr.",155^FD".$fab." - ".$venc."^FS
		^FO".$margemStr.",180^FDQUANTIDADE PRODUZIDA:  ".$valprod." ".$und."^FS

		^FO225,225^FDQUANTIDADE DE DESCONGELAMENTO:^FS
		".$prinSquare."

		^XZ";	

		$j = 0;
		while($j < $qtd){
			imprimir($strprint,$intipo);
			$j++;
		}

	}else if ($intipo=='tipo15'){

		$cabecalho="SIZE 40 mm, 20 mm
		SPEED 5
		DENSITY 7
		DIRECTION 0
		REFERENCE 0,0
		OFFSET 0 mm
		SHIFT 0
		CODEPAGE UTF-8
		CLS";
					   
			  
				$l=0;
				$pagina=0;
			while($row=mysql_fetch_assoc($resl)){
							
					$altura="60";
					$strprint=$cabecalho;                        
		
					$strprint.='
		TEXT 10,10,"1",0,1,1,"'.retira_acentos($row['nomeinicio']).' "';
					$strprint.='
		TEXT 10,30,"1",0,1,1,"'.retira_acentos($row['nomemeio']).' "';	
					$strprint.='
		TEXT 10,50,"1",0,1,1,"'.retira_acentos($row['nomefim']).' "';				
								$strprint.='
		TEXT 10,80,"3",0,1,1,"'.retira_acentos($row['descr']).' "';
								 $strprint.='
		TEXT 10,120,"2",0,1,1,"V: '.$row['vencimento'].' "';
									  
				$strprint.="
		PRINT 1
				";
		}

		$j = 0;
		while($j < $qtd){
			imprimirsem($strprint,$intipo);
			$j++;
		}

	}else {
		$row=mysqli_fetch_assoc($res);

		//codigo para criar uma linha na etiqueta ^FO50,20^GB700,1,3^FS
		$cabecalho="^XA^CF0,24"; 
		$rodape="^XZ";

		$strprint=$cabecalho;

		if(empty($row["idpessoa"])){
			$row['nomeinicio']="INATA PRODUTOS - BIOLOGICOS";
		}

		$valprod=recuperaExpoente(tratanumero($row['qtdprod']),$row['qtdprod_exp']);
		$pos = 20;
		if(!empty($row['nomeinicio'])){
			$strprint.='^FO90,'.$pos.'^FD'.retira_acentos($row['nomeinicio']).'^FS';
			$pos = $pos + 40;
		}
		if(!empty($row['nomefim'])){
			$strprint.='^FO90,'.$pos.'^FD'.retira_acentos($row['nomefim']).'^FS';
			$pos = $pos + 40;
		}
		if(!empty($row['descrinicio'])){
			$strprint.='^FO90,'.$pos.'^FD'.retira_acentos($row['descrinicio']).'^FS';
			$pos = $pos + 40;
		}
		if(!empty($row['descrfim'])){
			$strprint.='^FO90,'.$pos.'^FD'.retira_acentos($row['descrfim']).'^FS';
			$pos = $pos + 40;
		}
		if(!empty($row['partida'])){
			$strprint.='^FO90,'.$pos.'^FD'.$row['partida'].'^FS';
			$pos = $pos + 40;
		}
		if(!empty($row['fabricacao'])){
			$strprint.='^FO90,'.$pos.'^FD'.$row['fabricacao'].' '.$row['vencimento'].'^FS';
			$pos = $pos + 40;
		}
		if(!empty($valprod)){
			$strprint.='^FO90,'.$pos.'^FDQUANTIDADE PRODUZIDA: '.$valprod.' '.$row['un'].'^FS';
			$pos = $pos + 40;
		}
	
		$strprint.=$rodape;	
		$j = 0;
		while($j < $qtd){
			imprimir($strprint,$intipo);
			$j++;
		}
	}
	
}else{
	if($intipo=='lotecons' or $intipo=='tipo11'){
		$cabecalho="SIZE 40 mm, 20 mm
		SPEED 5
		DENSITY 7
		DIRECTION 0
		REFERENCE 0,0
		OFFSET 0 mm
		SHIFT 0
		CODEPAGE UTF-8
		CLS";   
			  
				$l=0;
				$pagina=0;
			while($row=mysql_fetch_assoc($res)){
					   
					$altura="60";
					$strprint=$cabecalho;
								
		
					$strprint.='
		TEXT 10,10,"2",0,1,1,"'.retira_acentos($row['nome']).' "';
					$strprint.='
		TEXT 10,40,"2",0,1,1,"'.retira_acentos($row['nomefim']).' "';
								$strprint.='
		TEXT 10,80,"2",0,1,1,"C.:'.$row['partida'].'/'.$row['exercicio'].' "';
								$strprint.='
		TEXT 10,120,"2",0,1,1,"S.:'.$row['semente'].'/'.$row['exerciciosem'].' "';			
				$strprint.="
		PRINT 1
				";
				$j = 0;
				while($j < $qtd){
					imprimirsem($strprint,$intipo);
					$j++;
				}
		
			}//while($row=mysql_fetch_assoc($res)){

	}else if($intipo=='tipo3'){ // tipo3
		$cabecalho="SIZE 40 mm, 20 mm
		SPEED 5
		DENSITY 7
		DIRECTION 0
		REFERENCE 0,0
		OFFSET 0 mm
		SHIFT 0
		CODEPAGE UTF-8
		CLS";   
			  
				$l=0;
				$pagina=0;
			while($row=mysql_fetch_assoc($res)){
					   
					$altura="60";
					$strprint=$cabecalho;
				$tamanho = strlen($row['descr']);
				if($tamanho > 22){
					$strprint.='
			TEXT 10,10,"2",0,1,1,"'.retira_acentos($row['descrinicio']).' "';
					$strprint.='
			TEXT 10,40,"2",0,1,1,"'.retira_acentos($row['descrfim']).' "';
						$strprint.='
			TEXT 10,60,"2",0,1,1,"Part.:'.retira_acentos($row['partida']).' "';
									$strprint.='
			TEXT 10,80,"2",0,1,1,"Fabr.:'.$row['fabricacao'].'"';
									$strprint.='
			TEXT 10,100,"2",0,1,1,"Venc.:'.$row['vencimento'].' "';

			$res3=d::b()->query($sql3) or die("Erro ao buscar informações da fórmula para impressão: ".mysqli_error(d::b()));
			$qrow3=mysqli_num_rows($res3);
			if($qrow3>0){
				$row3=mysqli_fetch_assoc($res3);
									$strprint.='
			TEXT 10,120,"2",0,1,1,"Vol.:'.$row3['formula'].' "';
			}
			
					$strprint.="
			PRINT 1
					";
				}else{
					$strprint.='
			TEXT 10,10,"2",0,1,1,"'.retira_acentos($row['descr']).' "';
						$strprint.='
			TEXT 10,40,"2",0,1,1,"Part.:'.retira_acentos($row['partida']).' "';
									$strprint.='
			TEXT 10,60,"2",0,1,1,"Fabr.:'.$row['fabricacao'].'"';
									$strprint.='
			TEXT 10,80,"2",0,1,1,"Venc.:'.$row['vencimento'].' "';
			$res3=d::b()->query($sql3) or die("Erro ao buscar informações da fórmula para impressão: ".mysqli_error(d::b()));
			$qrow3=mysqli_num_rows($res3);
			if($qrow3>0){
				$row3=mysqli_fetch_assoc($res3);
									$strprint.='
			TEXT 10,100,"2",0,1,1,"Vol.:'.$row3['formula'].' "';
			}			
					$strprint.="
			PRINT 1
					";
				}
				$j = 0;
				while($j < $qtd){
					imprimirsem($strprint,$intipo);
					$j++;
				}
		
			}//while($row=mysql_fetch_assoc($res)){

	}else if($intipo=='tipo8' or $intipo=='tipo9'){ // tipo8 ou tipo9
		$cabecalho="SIZE 40 mm, 20 mm
		SPEED 5
		DENSITY 7
		DIRECTION 0
		REFERENCE 0,0
		OFFSET 0 mm
		SHIFT 0
		CODEPAGE UTF-8
		CLS";
		$row=mysql_fetch_assoc($res);
		$strprint=$cabecalho;
		$altura = 10;
		if(strlen($row['nome']) > 22){
			$strprint.='
				TEXT 10,'.$altura.',"2",0,1,1,"'.retira_acentos(substr($row['nome'],0,22)).'"';
			$altura += 20;
			$strprint.='
				TEXT 10,'.$altura.',"2",0,1,1,"'.retira_acentos(substr($row['nome'],22)).'"';
		}else{
			$strprint.='
				TEXT 10,'.$altura.',"2",0,1,1,"'.retira_acentos($row['nome']).'"';
		}
		$altura += 40;
		$strprint.='
			TEXT 10,'.$altura.',"2",0,1,1,"PART:'.retira_acentos($row['partida']).'"';
		$altura += 40;
		$strprint.='
			TEXT 10,'.$altura.',"2",0,1,1,"TESTE GRAM E"';
		$altura += 20;
		$strprint.='
			TEXT 10,'.$altura.',"2",0,1,1,"INATIVACAO"';
		$strprint.="
		PRINT 1
				";
		$j = 0;
		while($j < $qtd){
			imprimirsem($strprint,$intipo);
			$j++;
		}
	} elseif($intipo == 'tipo12' or $intipo == 'tipo13' or $intipo == 'tipo14'){
		$cabecalho="SIZE 40 mm, 20 mm
					SPEED 5
					DENSITY 7
					DIRECTION 0
					REFERENCE 0,0
					OFFSET 0 mm
					SHIFT 0
					CODEPAGE UTF-8
					CLS";
		$row = mysql_fetch_assoc($res);
		$strprint = $cabecalho;
		$altura = 10;
		if($intipo == 'tipo12')
		{
			if(strlen($row['nomeinicio']) > 22){
				$strprint.='
					TEXT 8,'.$altura.',"2",0,1,1,"'.retira_acentos(substr($row['nomeinicio'],0,22)).'"';
				$altura += 20;
				$strprint.='
					TEXT 8,'.$altura.',"2",0,1,1,"'.retira_acentos(substr($row['nomeinicio'],22)).'"';
			}else{
				$strprint.='
					TEXT 8,'.$altura.',"2",0,1,1,"'.retira_acentos($row['nomeinicio']).'"';
			}
			$altura += 30;
		}
		$strprint.='
			TEXT 8,'.$altura.',"2",0,1,1,"'.retira_acentos($row['partida']).'"';
		$altura += 30;

		if($intipo == 'tipo12')
		{	
			$dataSomada2 = strtotime('+6 month'); // Exemplo de saída: 1570116272
			$vencimento = date('M/Y', $dataSomada2);
			$strprint.='
				TEXT 8,'.$altura.',"2",0,1,1,"'.'FAB:'.traduzMes(date('M/Y')).'"';
			$altura += 30;
			$strprint.='
				TEXT 8,'.$altura.',"2",0,1,1,"'.'VENC:'.traduzMes($vencimento).'"';
		}

		if($intipo == 'tipo13' or $intipo == 'tipo14')
		{
			$strprint.='
			TEXT 8,'.$altura.',"2",0,1,1,"'.$row['registro'].'"';
		}

		if($intipo == 'tipo13')
		{	
			$altura += 90;
			$strprint.='
			TEXT 8,'.$altura.',"2",0,1,1,"INOCULO"';
			$altura += 20;
		}
		
		$strprint.="
		PRINT 1
				";
		$j = 0;
		while($j < $qtd)
		{
			imprimirsem($strprint,$intipo);
			$j++;
		}
	}
	
}//if($intipo=='lote'){
	

function imprimir($strprint,$intipo){
	//echo $strprint;
	//die($strprint);
	if($_GET['testimp']=='y'){
		echo $strprint;
		die($strprint);
	}
	if($intipo == 'tipo7'){
		$ip_impressora = _IP_IMPRESSORA_MEIOS;
		 
	}else if($intipo == 'tipo10'){
		$ip_impressora = _IP_IMPRESSORA_PRODUCAO_2;
	}else{
	 	$ip_impressora = _IP_IMPRESSORA_PRODUCAO;
	}
   // Open a telnet connection to the printer, then push all the data into it.
		try
		{
			$fp=pfsockopen($ip_impressora,9100);
			fputs($fp,$strprint);
			fclose($fp);

			echo 'Successfully Printed';
		}
		catch (Exception $e) 
		{
			echo 'Caught exception: ',  $e->getMessage(), "\n";
		}
}

function imprimirsem($strprint,$intipo){
	if($_GET['testimp']=='y'){
		echo $strprint;
		die($strprint);
	}

	//die($strprint);
    $data = array('content'=>$strprint,	'Send'=>' Print Test ');	

    //print_r($data); //die;

    $QueryString= http_build_query($data);
    //echo("\n impressao ");
    //echo($QueryString); 

    // create context
    $context = stream_context_create(array(
                    'http' => array(
                                    'method' => 'GET',
                                    'content' => $QueryString,
                    ),
	));
	
	if($intipo == 'tipo6'){
		//echo 'tipo6\n';
		//echo $strprint;
		$response = file_get_contents("http://"._IP_IMPRESSORA_PRODUCAO_SEM2."/prt_test.htm?".$QueryString, false, $context);
	}elseif($intipo == 'tipo9' or $intipo == 'tipo11' or $intipo == 'tipo12' or $intipo == 'tipo13' or $intipo == 'tipo14'){
		//echo 'tipo6\n';
		//echo $strprint;
		$response = file_get_contents("http://"._IP_IMPRESSORA_PRODUCAO_SEM3."/prt_test.htm?".$QueryString, false, $context);
	}elseif($intipo == 'tipo15'){

		$response = file_get_contents("http://"._IP_IMPRESSORA_INCUBACAO."/prt_test.htm?".$QueryString, false, $context);

	}else{
		//echo $intipo.'\n';
		//echo $strprint;
		$response = file_get_contents("http://"._IP_IMPRESSORA_PRODUCAO_SEM."/prt_test.htm?".$QueryString, false, $context);
	}
    //Tratar erro quando não encontrar IP
    // send request and collect data
}

function traduzMes($mesano)
{
	$mesanoex = explode('/',$mesano);
	switch($mesanoex[0])
	{
		case "Jan": $month = "JAN"; break;
		case "Feb": $month = "FEV"; break;
		case "Mar": $month = "MAR"; break;
		case "Apr": $month = "ABR"; break;
		case "May": $month = "MAI"; break;
		case "Jun": $month = "JUN"; break;
		case "Jul": $month = "JUL"; break;
		case "Aug": $month = "AGO"; break;
		case "Sep": $month = "SET"; break;
		case "Oct": $month = "OUT"; break;
		case "Nov": $month = "NOV"; break;
		case "Dec": $month = "DEZ"; break;
	}
	return $month.'/'.$mesanoex[1];
}
?>
