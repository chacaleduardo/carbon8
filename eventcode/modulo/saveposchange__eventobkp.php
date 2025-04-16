<?
require_once("../models/eventoresp.php");

$_acao              = $_GET['_acao'];

/*$pagvalmodulo       =$_GET['_modulo'];
die($pagvalmodulo);
$pagvalcampos       = array(
	"idprodserv" => "pk"
);*/

//$idEvento = $_POST["_1_".$_GET['_acao']."_evento_idevento"];
if ($_POST["_1_u_resultado_idresultado"]){
	$idEvento =  $_POST["_1_u_evento_idevento"];
}else if ($_POST["_x_i_eventoresp_idevento"]){
	$idEvento =  $_POST["_x_i_eventoresp_idevento"];
}else if ($_GET['idevento']){
	$idEvento = $_GET['idevento'];
}else if (!empty($_SESSION["_pkid"])){
	$idEvento = $_SESSION["_pkid"];	
}


$status             = $_POST["_1_".$_GET['_acao']."_evento_status"];  
$idpessoa         	= $_POST["_1_".$_GET['_acao']."_evento_idpessoa"];
$nomeEvento         = $_POST["_1_".$_GET['_acao']."_evento_evento"];
$idmodulo           = $_POST["_1_".$_GET['_acao']."_evento_idmodulo"];
$modulo             = $_POST["_1_".$_GET['_acao']."_evento_modulo"];
$descricao          = $_POST["_1_".$_GET['_acao']."_evento_descricao"]; 
$jsonConfig         = $_POST["_1_".$_GET['_acao']."_evento_jsonconfig"];
$jsonResultado      = $_POST["_1_".$_GET['_acao']."_evento_jsonresultado"];
$ideventotipo      = $_POST["_1_".$_GET['_acao']."_evento_ideventotipo"];

$dataInicio         = $_POST["_1_".$_GET['_acao']."_evento_inicio"];
$horaInicio         = $_POST["_1_".$_GET['_acao']."_evento_iniciohms"];

$dataFim            = $_POST["_1_".$_GET['_acao']."_evento_fim"];
$horaFim            = $_POST["_1_".$_GET['_acao']."_evento_fimhms"];

$fimdesemana        = $_POST["_1_".$_GET['_acao']."_evento_fimsemana"];
$repetirate         = $_POST["_1_".$_GET['_acao']."_evento_repetirate"];
$peridiocidade      = $_POST["_1_".$_GET['_acao']."_evento_periodicidade"];


$ideventoresp      = $_POST['_x_d_eventoresp_ideventoresp'];

$idevento      = $_POST["_x_".$_GET['_acao']."_eventoresp_ideventoresp"];

if (empty($idevento )){
	$idevento  = $_POST['_x_i_eventoresp_idevento'];
}


$jsonConfigDecode = json_decode($jsonConfig, true);

//SABER QUAL TOKEN INICIAL DO EVENTO
if (!empty($idevento)){
	
    $sql = "select 
				getEventoStatusConfig(e.ideventotipo,CONCAT('\"',e.versao,'\"'),null, true,'token') AS tokeninicial
			FROM 
				evento e
			WHERE 
				e.idevento = '".$idevento."'";
	
	
    $res = d::b()->query($sql);

    while ($r = mysqli_fetch_assoc($res)) {
        $tokeninicial = $r['tokeninicial'];
    }
}else if ($ideventotipo){
	
	$sql = "select 
				getEventoStatusConfig(et.ideventotipo,null,null, true,'token') AS tokeninicial
				from
					eventotipo et 
				where ideventotipo = '".$ideventotipo."'
				";
	
	
    $res = d::b()->query($sql);

    while ($r = mysqli_fetch_assoc($res)) {
        $tokeninicial = $r['tokeninicial'];
    }
}



//VERIFICA SE FOI INSERIDO O CRIADOR DO EVENTO NA LISTA DE PARTICIPANTES.. 
//SE NEGATIVO, INSERE O MESMO.
$sql = "SELECT ideventoresp, e.idpessoa FROM evento e left join eventoresp r on e.idevento = r.idevento and tipoobjeto = 'pessoa' and r.idobjeto = e.idpessoa WHERE e.idevento = ".$idEvento.";";

$res = d::b()->query($sql);
$eventoRow = mysqli_fetch_assoc($res);

if (empty($eventoRow['ideventoresp'])){
	d::b()->query("INSERT INTO eventoresp (idevento,  idempresa, idobjeto, tipoobjeto, status, idpessoa) values (".$idEvento.", 1, ".$eventoRow['idpessoa'].",'pessoa', '".$tokeninicial."',".$eventoRow['idpessoa'].");"); 
	$idcriador = $eventoRow['idpessoa'];
}

//INSERE AS PESSOAS DEFINIDAS EM PARTICIPANTES NO EVENTO TIPO APENAS NO INSERT.
if ($_acao == 'i'){
	insereParticipantes($idEvento, $ideventotipo, $tokeninicial, $idcriador);
}


if ($repetirate){

$data =  explode('/',$dataInicio);
$data = $data[2].'-'.$data[1].'-'.$data[0]; 

$datarepetirate = explode('/',$repetirate);
$datarepetirate = $datarepetirate[2].'-'.$datarepetirate[1].'-'.$datarepetirate[0]; 
deletaEvento($idEvento, $data, $datarepetirate);
}

if ($jsonConfigDecode["configprazo"]) {
    $fim = $prazo;
}

//Setando hora default inicio
if (empty($horaInicio)) {
    $horaInicio = '00:00';
}
//Setando hora default fim
if (empty($horaFim)) {
    $horaFim = '00:00';
}

if (strlen($horaInicio) == 5) {
    $dataInicio = DateTime::createFromFormat('d/m/Y H:i', $dataInicio.' '.$horaInicio);
    $dataFim = DateTime::createFromFormat('d/m/Y H:i', $dataFim.' '.$horaFim);
} else {
    $dataInicio = DateTime::createFromFormat('d/m/Y H:i:s', $dataInicio.' '.$horaInicio);
    $dataFim = DateTime::createFromFormat('d/m/Y H:i:s', $dataFim.' '.$horaFim);
}

$sql = "SELECT * , JSON_EXTRACT(JSON_EXTRACT(JSON_EXTRACT(jconfig,concat('$[*].','\"',versao,'\"')), '$[0]'), '$.assinar') as assinar, modulo, idmodulo FROM evento e join eventotipo t on t.ideventotipo = e.ideventotipo WHERE idevento = ".$idEvento;
$res = d::b()->query($sql);
$eventoRow = mysqli_fetch_assoc($res);

 

if ($dataInicio > $dataFim) {
    die("Erro: Data inicial maior que data final:".date_format($dataInicio, 'Y-m-d h:m:s').' > '.$_POST["_1_".$_GET['_acao']."_evento_horaFim"]);
} else {

    if (!empty($repetirate)) {

        $repetirate = DateTime::createFromFormat('d/m/Y', $repetirate);
        //pega a quantidade de dias entre o inicio e o fim do evento
        $intervaldias = $dataInicio->diff($dataFim);
        //pega a quantidade de dias em inteiro para rodar no laço
        $intervaloEvento = $intervaldias->format('%a');
        //roda no for do primeiro dia até o ultimo dia do intervarlo
        $dataInicioSubEvento = clone $dataInicio;
        $dataFinalSubEvento = $dataFim;
		//echo($dataFim);
        if ($peridiocidade == "ANUAL"      || 
            $peridiocidade == "BIANUAL"    || 
            $peridiocidade == "TRIANUAL"   || 
            $peridiocidade == "MENSAL"     || 
            $peridiocidade == "BIMESTRAL"  || 
            $peridiocidade == "TRIMESTRAL" || 
            $peridiocidade == "SEMESTRAL"  || 
            $peridiocidade == "SEMANAL"    || 
            $peridiocidade == "DIARIO") {

            if ($peridiocidade == "DIARIO") {
                $tipoperiodicidade = 'P1D';
                $tipointervalo = 'dia';
            } elseif ($peridiocidade == "SEMANAL") {
                $tipoperiodicidade = 'P7D';
                $tipointervalo = 'dia';
            } elseif ($peridiocidade == "MENSAL") {
                $tipoperiodicidade = 'P1M';
                $tipointervalo = 'mes';
            } elseif ($peridiocidade == "BIMESTRAL") {
                $tipoperiodicidade = 'P2M';
                $tipointervalo = 'mes';
            } elseif ($peridiocidade == "TRIMESTRAL") {
                $tipoperiodicidade = 'P3M';
                $tipointervalo = 'mes';
            } elseif ($peridiocidade == "SEMESTRAL") {
                $tipoperiodicidade = 'P6M';
                $tipointervalo = 'mes';
            } elseif ($peridiocidade == "ANUAL") {
                $tipoperiodicidade = 'P1Y';
                $tipointervalo = 'ano';
            } elseif ($peridiocidade == "BIANUAL") {
                $tipoperiodicidade = 'P2Y';
                $tipointervalo = 'ano';
            } elseif ($peridiocidade == "TRIANUAL") {
                $tipoperiodicidade = 'P3Y';
                $tipointervalo = 'ano';
            }
			
            while($dataInicioSubEvento->format('Ymd') <= $repetirate->format('Ymd')) {

                if ($fimdesemana == 'N' and finalDeSemana($dataInicioSubEvento->format('Y-m-d'))) {

                } else {
					
                    criaEvento($idEvento, $eventoRow, $dataInicioSubEvento, $dataFinalSubEvento,$tokeninicial);
                }
                
                //se a periodicidade for mensal, bimestral, trimestral ou semestral e a dataInicial for 31, subevento irá repetir no ultimo dia de cada mes
                if ($dataInicio->format('d')=='31' && substr($tipoperiodicidade, 2, 1) == 'M') {
                    
                    $diff = 0;
                    $intervalMes = ((int)substr($tipoperiodicidade, 1, 1));

                    for ($i=0; $i < $intervalMes; $i++) {
                        $diff += (int)date("t", mktime(0, 0, 0, ((int)$dataInicioSubEvento->format('m'))+1+$i, 1, $dataInicioSubEvento->format('Y')));                        
                    }

                    $dataInicioSubEvento->modify('+'.$diff.' day');
                    $dataFinalSubEvento->modify('+'.$diff.' day');

                } else if ((int)$dataInicio->format('d') > 28 && 
                            substr($tipoperiodicidade, 2, 1) == 'M') {
                    
                    $intervalMes = ((int)substr($tipoperiodicidade, 1, 1));
                    $mesAtual = (int)$dataInicioSubEvento->format('m');
                    
                    if (($intervalMes+$mesAtual) % 12 == 2) {

                        $diff = 0;

                        for ($i=0; $i < $intervalMes; $i++) {
                            $diff += (int)date("t", mktime(0, 0, 0, ((int)$dataInicioSubEvento->format('m'))+1+$i, 1, $dataInicioSubEvento->format('Y')));                        
                        }

                        $diff += ((int)date("t", mktime(0, 0, 0, ((int)$dataInicioSubEvento->format('m')), 1, $dataInicioSubEvento->format('Y'))))-((int)$dataInicioSubEvento->format('d'));

                        $dataInicioSubEvento->modify('+'.$diff.' day');
                        $dataFinalSubEvento->modify('+'.$diff.' day');

                        $bissexto = ((int)date("L", mktime(0, 0, 0, 1, 1, $dataInicioSubEvento->format('Y'))));
                        $diffFevereiro = ((int)$dataInicio->format('d')) - 28 - $bissexto;
                    
                    } else {

                        $dataInicioSubEvento->add(new DateInterval($tipoperiodicidade));
                        $dataFinalSubEvento->add(new DateInterval($tipoperiodicidade));

                        if ($diffFevereiro > 0) {

                            $dataInicioSubEvento->modify('+'.$diffFevereiro.' day');
                            $dataFinalSubEvento->modify('+'.$diffFevereiro.' day');

                            $diffFevereiro = 0;
                        }                        
                    }                    
                } else if ($dataInicio->format('d/m') == '29/02' && 
                            substr($tipoperiodicidade, 2, 1) == 'Y') {
                                
                    $intervalAno = ((int)substr($tipoperiodicidade, 1, 1));
                    $anoAtual = (int)$dataInicioSubEvento->format('Y');

                    $diff = 0;
                        
                    for ($i = 0; $i < $intervalAno; $i++) {
                        $bissexto = ((int)date("L", mktime(0, 0, 0, 1, 1, ((int)$dataInicioSubEvento->format('Y'))+1+$i)));
                        $diff += (365+$bissexto);
                    }
                    
                    $dataInicioSubEvento->modify('+'.$diff.' day');
                    $dataFinalSubEvento->modify('+'.$diff.' day');   
                                 
                } else {
                    $dataInicioSubEvento->add(new DateInterval($tipoperiodicidade));
                    $dataFinalSubEvento->add(new DateInterval($tipoperiodicidade));
                }
            }
        }
//ECHO ($dataInicioSubEvento.' '.$dataFinalSubEvento);
	
		
    }else if (empty($_POST["_x_i_eventoresp_idevento"]) and empty($_POST["_x_d_eventoresp_ideventoresp"])) {

		$res = d::b()->query("DELETE FROM eventoresp where idevento in (select idevento from evento where ideventopai = ".$idEvento.");");
		$res = d::b()->query("DELETE FROM evento where ideventopai = ".$idEvento.";");
	}
}

$sql = "SELECT * FROM evento WHERE ideventopai = ".$idEvento;
$res = d::b()->query($sql);


while ($r = mysqli_fetch_assoc($res)) {


//@TODO: COLOCAR PARA ATUALIZAR SOMENTE OS PARTICIPANTES DE EVENTOS QUE NÃO ESTEJAM FINALIZADOS
$teste = "select distinct null as ideventoresp, '".$r['idevento']."', null as idpessoa, 1 as idempresa, gp.idpessoa as idobjeto, 'pessoa' as tipoobjeto, '".$tokeninicial."' as status, 0 as oculto, gp.idimgrupo as idobjetoext, 'imgrupo' as tipoobjetoext,'N' as inseridomanualmente,0 as visualizado,e.criadopor,e.criadoem,e.alteradopor, now()
						from evento e
						join eventoresp r on r.idevento = e.idevento and `r`.`tipoobjeto` = 'imgrupo' 
						join imgrupopessoa gp on gp.idimgrupo = r.idobjeto
						left join eventoresp r2 on r2.idevento = r.idevento and r2.tipoobjetoext = 'imgrupo' and r2.idobjetoext = r.idobjeto and r2.idobjeto = gp.idpessoa
						left join pessoa p on p.idpessoa = gp.idpessoa
						where
						r.idevento = '".$idEvento."' and r2.idobjeto is null and not gp.idpessoa = e.idpessoa
						UNION
						select distinct null as ideventoresp, '".$r['idevento']."', null as idpessoa, 1 as idempresa, r.idobjeto as idobjeto, 'pessoa' as tipoobjeto, '".$tokeninicial."' as status, 0 as oculto, null as idobjetoext, null as tipoobjetoext,'Y' as inseridomanualmente,0 as visualizado,e.criadopor,e.criadoem,e.alteradopor, now()
						from evento e
						join eventoresp r on r.idevento = e.idevento and `r`.`tipoobjeto` = 'pessoa' 
						where
						r.idevento = '".$idEvento."');";
						
d::b()->query("replace into eventoresp (
						select distinct null as ideventoresp, '".$r['idevento']."', null as idpessoa, 1 as idempresa, gp.idpessoa as idobjeto, 'pessoa' as tipoobjeto, '".$tokeninicial."' as status, 0 as oculto, gp.idimgrupo as idobjetoext, 'imgrupo' as tipoobjetoext,'N' as inseridomanualmente,0 as visualizado,e.criadopor,e.criadoem,e.alteradopor, now()
						from evento e
						join eventoresp r on r.idevento = e.idevento and `r`.`tipoobjeto` = 'imgrupo' 
						join imgrupopessoa gp on gp.idimgrupo = r.idobjeto
						left join eventoresp r2 on r2.idevento = r.idevento and r2.tipoobjetoext = 'imgrupo' and r2.idobjetoext = r.idobjeto and r2.idobjeto = gp.idpessoa
						left join pessoa p on p.idpessoa = gp.idpessoa
						where
						r.idevento = '".$idEvento."' and r2.idobjeto is null and not gp.idpessoa = e.idpessoa
						UNION
						select distinct null as ideventoresp, '".$r['idevento']."', null as idpessoa, 1 as idempresa, r.idobjeto as idobjeto, 'pessoa' as tipoobjeto, '".$tokeninicial."' as status, 0 as oculto, null as idobjetoext, null as tipoobjetoext,'Y' as inseridomanualmente,0 as visualizado,e.criadopor,e.criadoem,e.alteradopor, now()
						from evento e
						join eventoresp r on r.idevento = e.idevento and `r`.`tipoobjeto` = 'pessoa' 
						where
						r.idevento = '".$idEvento."');");
}

	
if ($ideventoresp){

	
   
}else{
		
if (($jsonConfigDecode["assinar"] && !empty($modulo) && $modulo != '') or $eventoRow['assinar'] == 'true') {
//die($eventoRow['assinar']);   
 $sql = "SELECT r.idobjeto, modulo, idmodulo FROM eventoresp r
				join evento e on e.idevento = r.idevento where r.tipoobjeto = 'pessoa' and r.idevento =".$idEvento;

    $res = d::b()->query($sql);
    
    while ($r = mysqli_fetch_assoc($res)) {
	
        criaAssinatura($r["idobjeto"], $jsonConfig, $r["modulo"], $r["idmodulo"]);
		$teste .= "  ".$r["idobjeto"];
    }
	//die($teste);
}
}


function insereParticipantes($idevento, $ideventotipo, $tokeninicial,$idcriador){
	
		
	 $sqlFuncionarios = "  	SELECT 	
		JSON_EXTRACT(JSON_EXTRACT(JSON_EXTRACT(jconfig,concat('$[*].', REPLACE(REPLACE(JSON_KEYS(JSON_EXTRACT(jconfig,'$[last]')),'[',''),']',''))), '$[0]'), '$.permissoes') as jsonconfig
									FROM 	eventotipo
									WHERE 	ideventotipo = ".$ideventotipo."
									AND idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]."";

			$resFuncionarios = d::b()->query($sqlFuncionarios) or die("Erro carregar participantes: ".mysqli_error(d::b()));			
			$funcionarios = mysqli_fetch_assoc($resFuncionarios);
			$funcionarios["jsonconfig"];
			$func = json_decode($funcionarios["jsonconfig"]);
			 
			$resultsetor = array();
			$resultpessoa = array();
			$i = 0;
			$j = 0;
			foreach ($func as $key => $object) {
				foreach ($object as $k => $v) {
					//  print_r($v); 
					if ($v->tipo == 'imgrupo'){
						$resultsetor[$i++] = $v->value;
						
					}
					if ($v->tipo == 'pessoa'){
						$resultpessoa[$j++] = $v->value;
						
					}
					
					
				}
			} 
			
			if ($resultsetor) {
				$resultsetor = implode(",", $resultsetor);
			} else {
				$resultsetor = "''";
			}
			
			if ($resultpessoa) {
				$resultpessoa = implode(",", $resultpessoa);
			} else {
				$resultpessoa = "''";
			}
			
			$sqlPessoa = "		SELECT 	p.idpessoa,
										p.nomecurto,
										p.idtipopessoa,
										p.status
								FROM 	pessoa p
								WHERE 	p.idempresa 	= ".$_SESSION["SESSAO"]["IDEMPRESA"]."
									AND p.status 		= 'ATIVO'
									AND p.idtipopessoa 	= 1
									AND p.idpessoa 		in (".$resultpessoa.");";

			

		   	$sqlImGrupo = "		SELECT 	i.idimgrupo,
										i.grupo
								FROM 	imgrupo i 
								WHERE 	i.status	= 'ATIVO'
									AND	i.idimgrupo	in (".$resultsetor.")
									AND	i.idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"].";";
			
			$resPessoa = d::b()->query($sqlPessoa) or die("Erro ao carregar configuracao de Pessoa: ".mysqli_error(d::b()));
			$resImGrupo = d::b()->query($sqlImGrupo) or die("Erro ao carregar configuracao do Setor: ".mysqli_error(d::b()));
			
		
			while($r = mysqli_fetch_assoc($resPessoa)) {
				$sql1 = "INSERT INTO eventoresp (idevento,  idempresa, idobjeto, tipoobjeto, status, idpessoa) values (".$idevento.", 1, ".$r["idpessoa"].",  'pessoa', '".$tokeninicial."', '".$idcriador."');";
				d::b()->query($sql1); 

			}

			while($r = mysqli_fetch_assoc($resImGrupo)) {
				$sql2 = "INSERT INTO eventoresp (idevento,  idempresa, idobjeto, tipoobjeto, status, idpessoa) values (".$idevento.", 1, ".$r["idimgrupo"].",'imgrupo', '".$tokeninicial."', '".$idcriador."');";
				d::b()->query($sql2); 
				//die('oi'.$sql2);
			}
			
									
			d::b()->query("replace into eventoresp (
						select distinct null as ideventoresp, '".$idevento."', null as idpessoa, 1 as idempresa, gp.idpessoa as idobjeto, 'pessoa' as tipoobjeto, '".$tokeninicial."' as status, 0 as oculto, gp.idimgrupo as idobjetoext, 'imgrupo' as tipoobjetoext,'N' as inseridomanualmente,0 as visualizado,e.criadopor,e.criadoem,e.alteradopor, now()
						from evento e
						join eventoresp r on r.idevento = e.idevento and `r`.`tipoobjeto` = 'imgrupo' 
						join imgrupopessoa gp on gp.idimgrupo = r.idobjeto
						left join eventoresp r2 on r2.idevento = r.idevento and r2.tipoobjetoext = 'imgrupo' and r2.idobjetoext = r.idobjeto and r2.idobjeto = gp.idpessoa
						left join pessoa p on p.idpessoa = gp.idpessoa
						where
						r.idevento = '".$idevento."' and r2.idobjeto is null and not gp.idpessoa = e.idpessoa
						UNION
						select distinct null as ideventoresp, '".$idevento."', null as idpessoa, 1 as idempresa, r.idobjeto as idobjeto, 'pessoa' as tipoobjeto, '".$tokeninicial."' as status, 0 as oculto, null as idobjetoext, null as tipoobjetoext,'Y' as inseridomanualmente,0 as visualizado,e.criadopor,e.criadoem,e.alteradopor, now()
						from evento e
						join eventoresp r on r.idevento = e.idevento and `r`.`tipoobjeto` = 'pessoa' 
						where
						r.idevento = '".$idevento."');");
						
						

	
}

//Funçao para verificar o dia da semana recebe ANO MES E DIA 2011-07-11 junto
function finalDeSemana($data) {

    $sql = "SELECT DAYOFWEEK('" . $data . "') as dia";
    $res = d::b()->query($sql) or die("A Consulta do dia da semana falhou : " . mysqli_error() . "<p>SQL: $sql");
    $row = mysqli_fetch_assoc($res);

    //se o retorno for diferente de 7 sabado ou 1 domingo mostra no dia
    if ($row["dia"] == 1 or $row["dia"] == 7) {
        return true;
    } else {
        return false;
    }

}

function criaEvento($idEventoPai, $evento, $dataInicioEvento, $dataFimEvento,$tokeninicial) {

//VERIFICA SE JÁ EXISTE UM EVENTO FILHO COM A DATA ESPECIFICADA
 $sql = "SELECT idevento from evento e where ideventopai =  ".$idEventoPai." and inicio = '".$dataInicioEvento->format('Y-m-d')."'";
        

    $res = d::b()->query($sql);
	$criar = true;
        while ($r = mysqli_fetch_assoc($res)) {
			$criar = false;
			$ideventofilho .= ' '.$r['idevento'];
}	
// CASO NEGATIVO, CRIA O EVENTO FILHO PARA A DATA ESPECIFICADA
	if ($criar){
		$sql = "INSERT INTO evento(
					ideventotipo, idempresa,
					idpessoa,
					ideventopai, evento,
					status, jsonconfig,
					descricao, inicio,
					iniciohms, fim,
					fimhms, versao, resultado,
					jsonresultado, criadopor,
					criadoem, alteradopor,
					alteradoem
				) VALUES (".$evento['ideventotipo'].",
					".$evento['idempresa'].",
					".$_SESSION["SESSAO"]["IDPESSOA"].",
					".$idEventoPai.",
					'".$evento['evento']."',
					'".$tokeninicial."',
					'".$evento['jsonconfig']."',
					'".$evento['descricao']."',
					'".$dataInicioEvento->format('Y-m-d')."',
					'".$evento['iniciohms']."',
					'".$dataFimEvento->format('Y-m-d')."',
					'".$evento['fimhms']."',
					'".$evento['versao']."',
					'".$evento['resultado']."',
					'".$evento['jsonresultado']."',
					'".$evento['criadopor']."',
					'".$evento['criadoem']."',
					'".$evento['alteradopor']."',
					'".$evento['alteradoem']."');";

		$res = d::b()->query($sql);
    }else{
// CASO POSITIVO, ATUALIZA OS DADOS DO EVENTO FILHO
// @TODO: AJUSTAR PARA ATUALIZAR SOMENTE OS EVENTOS FILHOS NÃO FINALIZADOS
	$ideventofilho = str_replace(' ',',',trim($ideventofilho));
	$sql = "UPDATE evento set 
					evento 		= '".$evento['evento']."',
					jsonconfig 	= '".$evento['jsonconfig']."',
					
					inicio 		= '".$dataInicioEvento->format('Y-m-d')."',
					iniciohms 	= '".$evento['iniciohms']."',
					fim 		= '".$dataFimEvento->format('Y-m-d')."',
					fimhms 		= '".$evento['fimhms']."'
				WHERE
					idevento 	in (".$ideventofilho.");";

		$res = d::b()->query($sql);
	}



    if (!$res) {
        echo("Erro ao inserir eventos " . mysqli_error(d::b()) . "<p>SQL: $sql");
    }
}

// DELETA TODOS OS EVENTOS FILHOS QUE ESTÃO FORA DO RANGE DO EVENTO PAI (REPETIR ATE)
function deletaEvento($idEventoPai, $datainicioEvento, $dataRepetirAte) { 

$sql = "DELETE FROM evento where ideventopai =  ".$idEventoPai." and (inicio < '".$datainicioEvento."' or fim >  '".$dataRepetirAte."') and getEventoStatusConfig(ideventotipo,CONCAT('\"',versao,'\"'),null, true,'token') = status";
//die($sql);
d::b()->query($sql);

}	

	

function criaAssinatura($idPessoa, $jsonConfig, $modulo, $idmodulo) {

    
 if ( $modulo == 'documento'){
	$sql = "SELECT 
				c.idcarrimbo, if(s.versao = c.versao, null, s.versao) as versao
			FROM
				sgdoc s
			JOIN
				carrimbo c on s.idsgdoc = c.idobjeto and (s.versao = c.versao or c.versao = 0)
			WHERE 
				c.status      in ('PENDENTE', 'ATIVO')
				AND c.idpessoa    = ".$idPessoa."
				AND c.idobjeto    = ".$idmodulo."
				AND c.tipoobjeto  = '".$modulo."'
				LIMIT 1";
	 
 }else{
	 $sql = "SELECT 
				c.idcarrimbo
					
			FROM 
				carrimbo c
			WHERE 
				c.status      in ('PENDENTE', 'ATIVO')
				AND c.idpessoa    = ".$idPessoa."
				AND c.idobjeto    = ".$idmodulo."
				AND c.tipoobjeto  = '".$modulo."'
				limit 1;";

 }
// die($sql);
   // if ($idPessoa != $_SESSION["SESSAO"]["IDPESSOA"]) {


        $res = d::b()->query($sql) or die("Erro ao executar consulta de Assinatura: ".mysqli_error(d::b()));

        if (!(mysqli_num_rows($res))) {
            $intabela               = new Insert();
            $intabela->setTable("carrimbo");
            $intabela->idempresa    = $_SESSION["SESSAO"]["IDEMPRESA"];
            $intabela->idpessoa     = $idPessoa;
            $intabela->idobjeto     = $idmodulo;
            $intabela->tipoobjeto   = $modulo;
            $intabela->status       = "PENDENTE";
			$intabela->versao       = $row['versao'];
            
            $idtabela=$intabela->save();
        }
    //}
}


/*
 * Centralizar a consulta de Módulo
 * Evitar falhas em relação à Módulos Vinculados
 * Complementar com as colunas necessárias diretamente na consulta
 */
function RetornaChaveModulo($inModulo, $inbypass=false) {
	
	if (empty($inModulo)) die("retArrModuloConf: Parâmetro inModulo não informado");

	//Permite reaproveitamento sem verificação de segurança. Ex: Tela de _modulo necessita recuperar informações do módulo mesmo que não estejam devidamente atribuídas em alguma LP
	if ($inbypass !== true) {
		$joinLp = ($_SESSION["SESSAO"]["LOGADO"])?"left join "._DBCARBON."._lpmodulo l on (l.modulo=m.modulo and l.idlp='".$_SESSION["SESSAO"]["IDLP"]."')":"";
		$whereMod = ($_SESSION["SESSAO"]["LOGADO"])?"and m.modulo in (".getModsUsr("SQLWHEREMOD").")":"";
        $ifrestaurar = (getModsUsr("SQLWHEREMOD"))?",IF(1=(select ('restaurar' in  (".getModsUsr("SQLWHEREMOD")."))),'Y','N') as oprestaurar":"";
	}
			
	$smod = "SELECT
				CASE WHEN m.tipo='MODVINC' THEN mv.chavefts ELSE m.chavefts END as chavefts
			FROM 
				"._DBCARBON."._modulo m 
				left join "._DBCARBON."._modulo mv on (mv.modulo=m.modvinculado)
				left join "._DBCARBON."._modulo mpar on (mpar.modulo=m.modulopar)
				".$joinLp."
			WHERE m.modulo = '".$inModulo."'
				".$whereMod;
	
	//die($smod);
	
	$rmod = d::b()->query($smod);
	
	if (!$rmod) die("retArrModuloConf: Erro ao recuperar Módulo ".  mysqli_error(d::b()));

	$rows = mysqli_fetch_assoc($rmod);
	return ($rows['chavefts']);
}
/*
 * Verifica se o funcionário já está no evento, para evitar erro de duplicidade.
 */
function VerificaFuncionarioEvento($idevento, $idpessoa) {
		$smod = "SELECT
				1 as valor
	
			FROM 
				eventoresp r
			WHERE
				idobjeto = '".$idpessoa."' and
				tipoobjeto = 'pessoa' and
				idevento = '".$idevento."'";
	//die($smod);
	
	$rmod = d::b()->query($smod);
	
	if (!$rmod) die("Erro ao verificar funcionario no evento: ".  mysqli_error(d::b()));

	$rows = mysqli_fetch_assoc($rmod);
	return ($rows['valor']);
}
?>