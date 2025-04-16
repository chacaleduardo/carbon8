<?
require_once("../inc/php/validaacesso.php");
require_once("../inc/php/functions.php");
//pega a data do GET que e timestamp
$timestampstart	= filter_input(INPUT_GET, "start");
$timestampend	= filter_input(INPUT_GET, "end");
$eventotipo		= filter_input(INPUT_GET, "veventotipo");

$date = new DateTime($timestampend);
$teste = $date->modify('-1 month')->format("Y-m-d");
/*$teste = substr($teste, 0, 7);
die($teste);
die($timestampstart->modify('+1 month')->format("Y-m-d"));
$timestampend->format("Y-m-d")->modify('+1 month');
$teste = substr($timestampend,0,7);
echo ($teste);
die();*/
if (empty($eventotipo)) {
	$eventotipo = '';
}

$arrayTipo		= explode(",", $eventotipo);

$selectFrom 	= "SELECT	e.idevento 		as 	idevento, 
							e.evento 		as 	evento, 
							e.inicio 		as 	inicio, 
							e.iniciohms 	as 	iniciohms, 
							e.fim 			as 	fim, 
							e.fimhms 		as 	fimhms,
							e.jsonconfig	as 	jsonconfig,
							if (getEventoStatusConfig(e.ideventotipo,CONCAT('\"',e.versao,'\"'),e.status,if (e.status, true, false),'final') = 'true', '#666', et.cor) as cor
					FROM 	evento 			as 	e
					JOIN	eventotipo 		as 	et
					ON 		e.ideventotipo	=	et.ideventotipo
					JOIN	eventoresp		as 	er
					ON		e.idevento 		= 	er.idevento
					WHERE 	e.status 		!= 	'CANCELADO'
							AND JSON_EXTRACT(JSON_EXTRACT(JSON_EXTRACT(jconfig,concat('$[*].','\"',versao,'\"')), '$[0]'), '$.calendario') = true
							AND DATE_FORMAT('$teste','%Y-%m') between 
								DATE_FORMAT(DATE_SUB(e.inicio, INTERVAL 14 DAY),'%Y-%m') and 
								DATE_FORMAT(DATE_ADD(e.fim, INTERVAL 14 DAY),'%Y-%m')
							AND er.tipoobjeto = 'pessoa'
							AND er.idobjeto 	=	".$_SESSION['SESSAO']['IDPESSOA']."
							AND e.ideventotipo 	in 	(".implode(',',$arrayTipo).")
							";

$sql	= 	$selectFrom." 	AND e.ideventopai 	!= 	''
							AND e.idevento 		= 	er.idevento
				
				UNION ".

			$selectFrom." 	AND e.ideventopai 	is 	null
							AND e.repetirate	is 	null
							AND e.idevento 		= 	er.idevento;";
//die($sql);
$res 	= d::b()->query($sql);	

$i 		= 0;
$cor	= "A9A9A9";
$arr 	= array();

while($row=mysqli_fetch_assoc($res)){

	$arreventos['id']		= $row["idevento"];
	$arreventos['title']	= ($row["evento"]);
	$arreventos['start']	= $row["inicio"]." ".$row["iniciohms"];
	$arreventos['end']		= $row["fim"]." ".$row["fimhms"];
	$arreventos['url']		= "?_modulo=evento&_acao=u&idevento=".$row["idevento"]."&dataclick=".$row["fim"];
	$arreventos['allDay']	= false;
	$arreventos['color']	= $row["cor"];
	$arr[$i]=$arreventos;
	$i=$i+1;
	
}

echo json_encode($arr,JSON_UNESCAPED_UNICODE);