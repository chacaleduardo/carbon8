
<?
// ETIQUETA IMPRESSAO CQ, MODELO: 
//DESCRICAO
//PARTIDA
//FABRICACAO
//VENCIMENTO
//VOLUME

require_once("../inc/php/functions.php");

$idlote= $_GET['idlote'];
 
if(empty($_GET['qtdimp'])){
	$qtdimp = 1;
}else{
	$qtdimp=$_GET['qtdimp'];
}
//comandos do esc pos, declarados como constante, NECESSÁRIOS. 
const ESC = "\x1b";
const GS="\x1d";
const NUL="\x00";
const LF="\x0a";

$unwanted_array = array(    'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
                            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
                            'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
                            'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'0', 'ô'=>'o', 'õ'=>'o',
                            'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', '°'=>' ','ª'=>' ');
//consulta para trazer a impressora
 (empty($_SESSION["SESSAO"]["IDEMPRESA"])) ? $idempresa = 1 : $idempresa = $_SESSION["SESSAO"]["IDEMPRESA"];
 $sqlimp3="select ip from tag 
            where varcarbon='IMPRESSORA_CQ_2'
           and idempresa=".$idempresa."
            and ip is not null 
            and status=	'ATIVO'";
$resimp3=d::b()->query($sqlimp3) or die("Erro ao buscar impressora do producao sementes: ".mysqli_error(d::b())." SQL: ".$sqlimp3);
$qtdimp3=mysqli_num_rows($resimp3);
if($qtdimp3<1){die("Não encontrada impressora do producao sementes em tags var carbon.");}
$rowimp3=mysqli_fetch_assoc($resimp3);
 define("_IP_IMPRESSORA_CQ_2",$rowimp3['ip']);  // DEFINE VARIAVEL COM IP CADASTRADO NA TAG DA IMPRESSORA
 
 
$str = ""; // Variável que receberá conteúdo

//consulta para trazer as informacoes que serao impressas na etiqueta
$sql = "SELECT 
				p.idprodserv,
                l.idlote, pf.volumeformula, pf.un , concat(pf.volumeformula,' ', pf.un) as formula,
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
                join prodservformula pf on (l.idprodservformula = pf.idprodservformula) WHERE l.idlote=".$idlote;
                
        //die($sql);
        $res=d::b()->query($sql) or die("Erro ao buscar informações da partida para impressão: ".mysqli_error(d::b())." SQL: ".$sql);
        $qrow=mysqli_num_rows($res);
        $tpag=ceil($qrow/7);
            if($qrow==0){
                die("Nenhum resultado encontrado para impressão das Etiquetas");
                echo $sql;
            } 
            while($row=mysql_fetch_assoc($res)){
                 $descricao = $row['descr'];
                 $partida = $row['partida'];
                 $fabricacao = $row['fabricacao'];
                 $vencimento = $row['vencimento'];
                 $volume = $row['formula'];    
            }
function intLowHigh(int $input, int $length){
    $maxInput = (256 << ($length * 8) - 1);
    $outp = "";
    for ($i = 0; $i < $length; $i++) {
        $outp .= chr($input % 256);
        $input = (int)($input / 256);
    }
    return $outp;
}

//codificacao para enviar informacoes para impressora 

    $str = "";
    $str .= ESC."@"; // Reset to defaults (Inicializa nova impressão)
    $str .= GS. 'W'. intLowHigh(526,2);
    $str .= GS. 'L'. intLowHigh(64,2);
    $str .= strtr($descricao,$unwanted_array) ."\n"; // sepre colocar \n após um texto e não concatenar ESC no texto
    $str .= "Part.: ".$partida."\n"; // sempre colocar \n após um texto e não concatenar ESC no texto
    $str .= "Fabr.: ".$fabricacao."\n"; // sempre colocar \n após um texto e não concatenar ESC no texto
    $str .= "Venc.: ".$vencimento."\n"; // sempre colocar \n após um texto e não concatenar ESC no texto
    $str .= "Vol.: ".$volume."\n"; 
    $str .= ESC."d".chr(1); // Blank line (pular linha)
    $str .= GS . "V" . chr(65) . chr(3); // Cut (cortar papel)

//quantidade de impressoes que foram enviadas pelo modal (pega qtdimp pela url tambem)
    if($qtdimp > 0){
        for($j = 0; $j < $qtdimp; $j++){
            imprimir($str);
        }
    }
// funcao que faz a impressao, caso for necessario manutencao no codigo, favor comentar a mesma, pois envia para producao 
// mesmo testando em ambiente local. 

function imprimir($strprint){
    try{
        $fp=pfsockopen( _IP_IMPRESSORA_CQ_2 ,9100);
        fputs($fp,$strprint);
        fclose($fp);
 
        echo 'Successfully Printed';
        
    }catch (Exception $e){
        echo 'Caught exception: ',  $e->getMessage(), "\n";
    }
}
?>

