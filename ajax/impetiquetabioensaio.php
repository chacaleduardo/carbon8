<?
require_once("../inc/php/functions.php");

$idobjeto= $_GET['idobjeto'];
$tipoobjeto= $_GET['tipoobjeto'];


if( empty($idobjeto) or empty($tipoobjeto) ){
    die("Não foi informado o parâmetros necessários para impressão");
}

if($tipoobjeto=="servicoensaio"){
    $str=" and s.idservicoensaio =".$idobjeto;
}elseif($tipoobjeto=="analise"){
    $str=" and a.idanalise = ".$idobjeto;
}else{
    die("Erro ao identificar o tipo da impressão.");
}

$sqlimp="select ip from tag 
            where varcarbon='IMPRESSORA_CQ'
            ".getidempresa("idempresa","")."
            and ip is not null 
            and status=	'ATIVO'";
$resimp=d::b()->query($sqlimp) or die("Erro ao buscar impressora do diagnostico: ".mysqli_error(d::b()));
$qtdimp=mysqli_num_rows($resimp);
if($qtdimp<1){die("Não encontrada impressora do CA em tags var carbon.");}
$rowimp=mysqli_fetch_assoc($resimp);
define("_IP_IMPRESSORA_CQ",$rowimp['ip']);


    $sql="select b.idregistro,b.exercicio,n.nucleo as estudo,b.partida,s.idservicoensaio,sb.servico,s.dia,dma(s.data) as dmadata
		,tt.descricao as rot,l.descricao as gaiola,s.data
		from nucleo n join bioensaio b on (n.idnucleo =b.idnucleo) join analise a on (a.idobjeto=b.idbioensaio) join servicoensaio s on (s.idobjeto=a.idanalise)
		join servicobioterio sb on (s.idservicobioterio = sb.idservicobioterio)
			join localensaio le on (le.idanalise = a.idanalise) left join tag l on (le.idtag=l.idtag) left join tagsala ts on (ts.idtag = l.idtag) left join tag tt on (tt.idtag = ts.idtagpai)
			where sb.impetiqueta = 'S'
			and a.objeto ='bioensaio'
			and s.tipoobjeto = 'analise' 
         	".$str."
          	order by s.data";

//die($sql);
$resl=d::b()->query($sql) or die("Erro ao recuperar serviços de impressão: ".mysqli_error(d::b()));

$qrow1=mysqli_num_rows($resl);

$tpag=ceil($qrow1/7);

if($qrow1==0){
	die("Nenhum resultado encontrado para impressão das Etiquetas");
}
$pagina=0;
while($rl=  mysqli_fetch_assoc($resl)){

$cabecalho="SIZE 58 mm, 40 mm
SPEED 0
DENSITY 14
DIRECTION 1
REFERENCE 0,0
OFFSET 0 mm
SHIFT 0
CODEPAGE UTF-8
CLS";

                        
        
        $sql="select r.quantidade,p.codprodserv,concat(a.idregistro,'/',a.exercicio) as idregamostra
            from resultado r,prodserv p, amostra a
            where p.idprodserv = r.idtipoteste
	    and a.idamostra = r.idamostra
            and r.idservicoensaio = ".$rl["idservicoensaio"];
	//die($sql);
	$res=mysql_query($sql) or die("Erro ao buscar dados dos testes para impressão  sql=".$sql);
        $l=0;
        $pagina=0;
	while($row=mysql_fetch_assoc($res)){
	
		if($l==0){
			$altura="90";
			$strprint=$cabecalho;
			$strprint.='
TEXT 10,20,"2",0,1,1,"B'.str_pad($rl['idregistro'],6).'/'.str_pad($rl['exercicio'],6).'"';
			$strprint.='
REVERSE 6,12,80,30';
			$strprint.='
TEXT 10,50,"2",0,1,1,"'.$rl['rot'].' '.$rl['gaiola'].'"';
			
			// se tiver nucleo
			if(!empty($rl['estudo'])){
				$altura="120";
				$strprint.='
TEXT 10,80,"2",0,1,1,"'.retira_acentos(trim($rl['estudo'])).'" ';

			}
if(!empty($rl['servico'])){
		
				$strprint.='
TEXT 10,'.$altura.',"",0,1,1,"REG. : '.$row['idregamostra'].'"';
				$altura=$altura+30;
				$strprint.='
TEXT 10,'.$altura.',"",0,1,1,"SERV.: '.$rl['servico'].' ('.$rl['dia'].') - '.$rl['dmadata'].' "';
				$altura=$altura+30;
			}
			$strprint.='
TEXT 10,'.$altura.',"",0,1,1,"QTD. : '.$row['quantidade'].'"';
$altura=$altura+30;
			$strprint.='
TEXT 10,'.$altura.',"",0,1,1,"PROD.: '.retira_acentos($row['codprodserv']).' '.$of.' "';
		}else{
			$altura=$altura+30;
			$strprint.='
TEXT 10,'.$altura.',"",0,1,1,"QTD. : '.$row['quantidade'].'"';
$altura=$altura+30;
			$strprint.='
TEXT 10,'.$altura.',"",0,1,1,"PROD.: '.retira_acentos($row['codprodserv']).' '.$of.' "';
		}
		$l=$l+1;
		if($l==7){
			$pagina=$pagina+1;
			$l=0;
			$strprint.='
TEXT 390,300,"",0,1,1," '.$pagina.'/'.$tpag.' "';
		$strprint.="
PRINT 1
		";
			imprimir($strprint);
		
		}
	}//while($row=mysql_fetch_assoc($res)){
	if($l>0){
		$pagina=$pagina+1;
		$strprint.='
TEXT 390,300,"",0,1,1," '.$pagina.'/'.$tpag.' "';
	$strprint.="
PRINT 1
		";
		imprimir($strprint);
	}


}



function imprimir($strprint){
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
//Tratar erro quando não encontrar IP
// send request and collect data
$response = file_get_contents("http://"._IP_IMPRESSORA_CQ."/prt_test.htm?".$QueryString, false, $context);

}
?>

http://192.168.0.21/prt_test.htm?content=SIZE+59+mm%2C+40+mm%0D%0ASPEED+0%0D%0ADENSITY+14%0D%0ADIRECTION+1%0D%0AREFERENCE+0%2C0%0D%0AOFFSET+0+mm%0D%0ASHIFT+0%0D%0ACODEPAGE+UTF-8%0D%0ACLS%0D%0ATEXT+10%2C20%2C%222%22%2C0%2C1%2C1%2C%22B1599++%2F2021++-+%22%0D%0AREVERSE+6%2C12%2C80%2C30%0D%0ATEXT+10%2C50%2C%222%22%2C0%2C1%2C1%2C%22B1599+Teste+P%26D+-+SCCBB102%22+%0D%0ATEXT+10%2C90%2C%22%22%2C0%2C1%2C1%2C%22+Reg.%3A66863%2F2021+-+ABATE+%2821%29+-+07%2F09%2F2021+%22%0D%0ATEXT+10%2C120%2C%22%22%2C0%2C1%2C1%2C%22+10+-+ACAVALNEC++%22%0D%0ATEXT+390%2C300%2C%22%22%2C0%2C1%2C1%2C%22+1%2F1+%22%0D%0APRINT+1%0D%0A%09%09&Send=+Print+Test+