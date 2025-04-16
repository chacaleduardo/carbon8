<?
require_once(__DIR__."/../../../../form/controllers/etiqueta_controller.php");


// Visite https://mike42.me/blog/what-is-escpos-and-how-do-i-use-it para exemplos de implementação
// Visite https://reference.epson-biz.com/modules/ref_escpos/index.php?content_id=72 para lista de comandos ESC/POS

/* 
    ASCII constants 

    constantes padrões para uso geral
*/

$idsolmat = $_OBJ['idsolmat'];

if(empty($idsolmat)){
    die("Erro: idobjeto não enviado para impressão.");
}
const ESC = "\x1b";
const GS="\x1d";
const NUL="\x00";
const LF="\x0a";

$unwanted_array = array(    'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
                            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
                            'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
                            'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
                            'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', '°'=>' ','ª'=>' ');

$qr=EtiquetaController::buscarInfosSolmatCupomFiscal( $idsolmat, 1552 ,'solmatmeios' );
//die($sql);
if(count($qr) > 0){
    $aux = "";
    foreach($qr as $k => $row){
        $criadoem = $row["criadoem"];
        $criadopor = $row["criadopor"];
        $origem = $row["origem"];
        $destino = $row["destino"];
        $tipo = $row["tipo"];
        $nomecurto = $row['descricao'];
        $now = sysdate();

        $aux .= $row["qtdd"]." ".$row["un"]." - ".$row["partida"]." - ".strtr( $row["descr"], $unwanted_array )."\n" . $row['campo']."\nQtd. Restante ".$row["qtdrestante"]." ".$row["un"]."\n".$row['obs']."\n"."\n";
        
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
    function setTextSize(int $widthMultiplier, int $heightMultiplier){
        $c = (2 << 3) * ($widthMultiplier - 1) + ($heightMultiplier - 1);
        return $c;
    }
   
    $_CONTEUDOIMPRESSAO = ""; // Variável que receberá conteúdo
    $_CONTEUDOIMPRESSAO .= "Requisicao N:".$idsolmat."\n"; // sempre colocar \n após um texto e não concatenar ESC no texto
    $_CONTEUDOIMPRESSAO .= "Realizado por:".$criadopor."\n";
    $_CONTEUDOIMPRESSAO .=  dmahms($criadoem)."\n";
    $_CONTEUDOIMPRESSAO .=  "Origem:".$origem."\n";
    $_CONTEUDOIMPRESSAO .= "Itens:\n";
    $_CONTEUDOIMPRESSAO .= $aux;
    $_CONTEUDOIMPRESSAO .= "Destino:".strtr( $destino, $unwanted_array )."\n";
    $_CONTEUDOIMPRESSAO .= $nomecurto."\n";
    $_CONTEUDOIMPRESSAO .= "Tipo:  ".$tipo."\n";
    $_CONTEUDOIMPRESSAO .= "Data de impressao:" .dmahms($now)." \n";
    $_CONTEUDOIMPRESSAO .= GS . "V" . chr(65) . chr(3); // Cut (cortar papel);
}
?>