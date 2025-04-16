<?
require_once("../inc/php/functions.php");
// Visite https://mike42.me/blog/what-is-escpos-and-how-do-i-use-it para exemplos de implementação
// Visite https://reference.epson-biz.com/modules/ref_escpos/index.php?content_id=72 para lista de comandos ESC/POS

/* 
    ASCII constants 

    constantes padrões para uso geral
*/

$idsolmat = $_GET['idsolmat'];

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

$sql = "SELECT  si.idsolmat,
                si.idsolmatitem,
                si.descr,
                IFNULL(fh.criadopor,s.criadopor) as criadopor,
                IFNULL(fh.criadoem,s.criadoem) as criadoem,
                s.tipo,
                p.un,
                si.obs,
                t.descricao,
                u.unidade as destino,
                lc.qtdd,
                lf.qtd as qtdrestante,
                u1.unidade as origem,
                l.idlote,
                concat(l.partida,'/',l.exercicio) as partida,
                p.descr,
                concat(ta.descricao,' ',
                        concat(case d.coluna 
                                when 0 then '0' when 1 then 'A'	when 2 then 'B' when 3 then 'C' when 4 then 'D'	when 5 then 'E'	when 6 then 'F'
                                when 7 then 'G' when 8 then 'H' when 9 then 'I' when 10 then 'J' when 11 then 'K' when 12 then 'L'
                                when 13 then 'M' when 14 then 'N' when 15 then 'O' when 16 then 'P' when 17 then 'Q' when 18 then 'R'
                                when 19 then 'S' when 20 then 'T' when 21 then 'U' when 22 then 'V' when 23 then 'X' when 24 then 'Z'
                                end,' ',d.linha)
                )as campo
        FROM solmatitem si 
            join solmat s on(s.idsolmat = si.idsolmat)
            join unidade u on(s.idunidade = u.idunidade)
            join unidade u1 on(s.unidade = u1.idunidade)
            join lotecons lc on(lc.tipoobjetoconsumoespec = 'solmatitem' and lc.idobjetoconsumoespec = si.idsolmatitem and lc.status != 'INATIVO')
            join lote l on(lc.idlote = l.idlote) 
            join lotefracao lf on lf.idlote = l.idlote and lf.idlotefracao = lc.idlotefracao and l.idempresa = lf.idempresa
            join prodserv p on(p.idprodserv = l.idprodserv) 
            left join tag t on (s.idtag = t.idtag)
            left join lotelocalizacao ll on( ll.idlote = l.idlote and ll.idempresa = lf.idempresa)
            left join tagdim d on(ll.tipoobjeto = 'tagdim' and d.idtagdim = ll.idobjeto)
            left join tag ta on (ta.idtag = d.idtag)
            left join fluxostatushist fh on (fh.idfluxostatus=1337 and fh.modulo = 'solmat' and fh.idmodulo = si.idsolmat and fh.status='ATIVO')
        where
            si.idsolmat =".$idsolmat."
                and qtdd > 0
        group by l.idlote order by ta.descricao asc, d.coluna asc, d.linha asc";
$qr = d::b()->query($sql) or die("Erro ao buscar itens".mysql_error()." sql=".$sql);
//die($sql);
if(mysqli_num_rows($qr) > 0){
    $aux = "";
    while($row = mysqli_fetch_assoc($qr)){
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
   
    $str = ""; // Variável que receberá conteúdo
    
    $str .= GS. 'L'. intLowHigh(0,2);
    $str .= GS .'!'.chr(119);
    $str .= "Requisicao N:".$idsolmat."\n"; // sempre colocar \n após um texto e não concatenar ESC no texto
    $str .= "Realizado por:".$criadopor."\n";
    $str .=  dmahms($criadoem)."\n";
    $str .=  "Origem:".$origem."\n";
    $str .= ESC."d".chr(1); // Blank line (pular linha)
    $str .= "Itens:\n";
    $str .= $aux;
    $str .= GS .'E'.chr(1);
    $str .= GS .'!'.chr(1). "Destino:".strtr( $destino, $unwanted_array )."\n";
    $str .= ESC .'!'.chr(0);
    $str .= GS .'!'.chr(19);
    $str .= GS. 'L'. intLowHigh(0,2);
    $str .= $nomecurto."\n";
    $str .= ESC .'!'.chr(0) ;
    $str .= GS. 'L'. intLowHigh(0,2);
    $str .= "Tipo:\n";
    $str .= GS. 'W'. intLowHigh(1024,2);
    $str .= GS. 'L'. intLowHigh(0,2);
    $str .= GS. 'L'. intLowHigh(64,2);
    $str .= GS .'!'.chr(16).$tipo."\n";
    $str .= ESC .'!'.chr(0) ;
    $str .= "Data de impressão:" .dmahms($now)."\n";
    $str .= ESC."d".chr(1); // Blank line (pular linha)
    $str .= GS . "V" . chr(65) . chr(3); // Cut (cortar papel)
    $str .= ESC."@"; // Reset to defaults (Inicializa nova impressão)
    //die($str);
    imprimir($str);
}else{
    die("Solmat informada não possui itens");
}


function imprimir($strprint){

    try{
        $fp=pfsockopen("192.168.0.32",9100);
        fputs($fp,$strprint);
        fclose($fp);

        echo 'Successfully Printed';
    }catch (Exception $e){
        echo 'Caught exception: ',  $e->getMessage(), "\n";
    }
}
?>