<?
require_once("../inc/php/functions.php");

$idresultado= $_GET['idresultado'];
$idloteativ=$_GET['idloteativ'];
$idlote=$_GET['idlote'];

if(empty($idresultado) OR empty($idloteativ) OR empty($idlote)){
    die("Erro: Parâmetros Inválidos.");
}


// BUSCA A IMPRESSORA
$sqlimp="SELECT ip from tag 
            where varcarbon='IMPRESSORA_PRODUCAO_SEM'
            and idempresa=".$_SESSION["SESSAO"]["IDEMPRESA"]."
            and ip is not null 
            and status=	'ATIVO'";
$resimp=d::b()->query($sqlimp) or die("Erro ao buscar impressora do produção zebra: ".mysqli_error(d::b()));
$qtdimp=mysqli_num_rows($resimp);
if($qtdimp<1){die("Não encontrada impressora de produção sementes em tags var carbon.");}
$rowimp=mysqli_fetch_assoc($resimp);
define("_IP_IMPRESSORA_SEMENTE_TESTES",$rowimp['ip']);
//echo $sqlimp;die;
//echo _IP_IMPRESSORA_SEM_TESTES;die;


// BUSCA LOTE PARA RECUPERAR PARTIDA
$sqlote = "SELECT partida,exercicio FROM lote where idlote = ".$idlote;
$reslote=d::b()->query($sqlote) or die("Erro ao buscar lote: ".mysqli_error(d::b()));
$qtdlote=mysqli_num_rows($reslote);
//echo $sqlote;die;

if($qtdlote < 1){
    die("Não foi possível encontrar o lote");
}else{
    $rowlote=mysqli_fetch_assoc($reslote);
    $partida = $rowlote["partida"]."/".$rowlote["exercicio"];
}
//echo $partida;die;


// BUSCA SALA DA ATIVIDADE
$sqlsala = "SELECT t.descricao 
    FROM loteobj l 
        join tag t on (l.idobjeto = t.idtag)
        join tagclass tc on (t.idtagclass = tc.idtagclass)
    WHERE 
        l.idloteativ = ".$idloteativ." 
        and l.tipoobjeto = 'tag' 
        and l.idobjeto <> ''
        AND t.status = 'ATIVO'
        and tc.tagclass = 'SALA'
        ".getidempresa('l.idempresa','loteobj')."
    ORDER BY
        l.criadoem DESC LIMIT 1";

$ressala=d::b()->query($sqlsala) or die("Erro ao buscar sala da atividade: ".mysqli_error(d::b()));
$qtdsala=mysqli_num_rows($ressala);
//echo $sqlsala;die;

if($qtdsala < 1){
    $sala = "";
}else{
    $rowsala=mysqli_fetch_assoc($ressala);
    $sala = $rowsala["descricao"];
}
//echo $sala;die;

$sqlamostra = "SELECT a.idregistro 
    FROM resultado r 
    JOIN amostra a 
        ON (r.idamostra = a.idamostra) 
    WHERE idresultado = ".$idresultado;
$resamostra=d::b()->query($sqlamostra) or die("Erro ao buscar registro: ".mysqli_error(d::b()));
$qtdamostra=mysqli_num_rows($resamostra);
//echo $sqlamostra;die;

if($qtdamostra < 1){
    die("Não foi possível encontrar o lote");
}else{
    $rowamostra=mysqli_fetch_assoc($resamostra);
    $registro = $rowamostra["idregistro"];
}
//echo $registro;die;

        $cabecalho="SIZE 60 mm, 30 mm
		SPEED 5
		DENSITY 7
		DIRECTION 0
		REFERENCE 0,0
		OFFSET 0 mm
		SHIFT 0
		CODEPAGE UTF-8
        CLS";   
        $strprint=$cabecalho;
                    $strprint.='
        TEXT 50,20,"2",0,1,1,"PARTIDA:'.retira_acentos($partida).' "';
                    $strprint.='
        TEXT 50,50,"2",0,1,1,"SALA:'.retira_acentos($sala).' "';
                    $strprint.='
        TEXT 50,80,"2",0,1,1,"REG:'.retira_acentos($registro).' "';
                    $strprint.='
        TEXT 50,120,"2",0,1,1,"DATA EXPOSICAO:____/____/____"';
                    $strprint.='
        TEXT 50,170,"2",0,1,1,"HORA EXPOSICAO:_______:______"';
                    $strprint.='
        TEXT 50,220,"2",0,1,1,"OPERADOR:____________________"';
                    $strprint.="
		PRINT 1
                ";
                
        imprimir($strprint);
		imprimir($strprint);

function imprimir($strprint){
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
    //Tratar erro quando não encontrar IP
    // send request and collect data
    //echo "http://"._IP_IMPRESSORA_SEMENTE_TESTES."/prt_test.htm?".$QueryString;die;
    //echo "http://192.168.0.55/prt_test.htm?".$QueryString;die;
    $response = file_get_contents("http://"._IP_IMPRESSORA_SEMENTE_TESTES."/prt_test.htm?".$QueryString, false, $context);
    //$response = file_get_contents("http://192.168.0.55/prt_test.htm?".$QueryString, false, $context);
}
?>