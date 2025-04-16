<?
require_once("../validaacesso.php");

class ControleImpressaoModulo{
    private static $post;

    public static function exec($__command, $POST){
        self::$post = $POST;

        switch($__command){
            case 'confEtiqueta':
                $response = self::confEtiqueta();
                break;
            case 'imprimirEtiqueta':
                $response = self::imprimirEtiqueta();
                break;
            default:
                $response = self::errorMessage();
                break;
        }

        return json_encode($response);
    }

    protected static function confEtiqueta(){
        
        if(!empty(self::$post["modulo"]) AND !empty(self::$post["grupo"]) AND !empty(self::$post["idempresa"])){
            $mod        = self::$post["modulo"];
            $idmod        = traduzid('carbonnovo._modulo','modulo','idmodulo',$mod);
            $grp        = self::$post["grupo"];
            $idempresa  = self::$post["idempresa"];

            $qr = "SELECT e.idetiqueta, e.rotuloetiqueta, e.nomeetiqueta, e.tipo as linguagem, a.caminho
                    FROM "._DBCARBON."._modulo m
                        JOIN etiquetaobjeto eo ON (m.idmodulo = eo.idobjeto AND eo.tipoobjeto = 'modulo')
                        JOIN etiqueta e ON (eo.idetiqueta = e.idetiqueta)
                        LEFT JOIN arquivo a ON (a.tipoobjeto = 'etiqueta' AND a.idobjeto = e.idetiqueta)
                    WHERE m.modulo = '".$mod."'
                        AND e.status = 'ATIVO'
                        AND eo.grupo = ".$grp."
                    ORDER BY e.rotuloetiqueta";
            $rs = d::b()->query($qr);
            if(!$rs){
                return self::errorMessage("Erro ao consultar etiquetas do módulo");
            }

            if(mysqli_num_rows($rs) > 0){
                $i = 0;
                $arrConf = array();

                while($rw = mysqli_fetch_assoc($rs)){
                    $arrConf[$i]["idetiqueta"]      = $rw["idetiqueta"];
                    $arrConf[$i]["rotuloetiqueta"]  = $rw["rotuloetiqueta"];
                    $arrConf[$i]["linguagem"]       = $rw["linguagem"];
                    $arrConf[$i]["nomeetiqueta"]    = $rw["nomeetiqueta"];
                    $arrConf[$i]["imagem"]    = $rw["caminho"];

                    $qr2 = "SELECT t.tag, t.descricao, t.fabricante, t.ip
                            FROM etiquetaobjeto eo
                                JOIN tag t ON (eo.idobjeto = t.idtag AND eo.tipoobjeto = 'tag')
                                JOIN objetovinculo ov on (ov.idobjeto = ".$idmod." and ov.tipoobjeto='modulo' and ov.tipoobjetovinc='tag' and ov.idobjetovinc=t.idtag)
                            WHERE eo.idetiqueta = ".$rw["idetiqueta"];
                    $rs2 = d::b()->query($qr2);
                    if(!$rs2){
                        return self::errorMessage("Erro ao consultar impressoras para a Etiqueta ID: ".$rw["idetiqueta"]);
                    }

                    if(mysqli_num_rows($rs2) > 0){
                        $j = 0;
                        while($rw2 = mysqli_fetch_assoc($rs2)){
                            $arrConf[$i]["impressoras"][$j]["tag"]          = $rw2["tag"];
                            $arrConf[$i]["impressoras"][$j]["descricao"]    = $rw2["descricao"];
                            $arrConf[$i]["impressoras"][$j]["fabricante"]   = $rw2["fabricante"];
                            $arrConf[$i]["impressoras"][$j]["ip"]           = $rw2["ip"];
                            $j++;
                        }
                    }else{
                        $arrConf[$i]["impressoras"] = [];
                    }
                    $i++;
                }
                $arrConf[$i - 1]['contimp'] = $j;
                return $arrConf;

            }else{
                return self::errorMessage("Nenhuma etiqueta configurada para MÓDULO: ".$mod.", GRUPO: ".$grp.", IDEMPRESA: ".$idempresa);
            }

        }else{
            return self::errorMessage("Parâmetros 'módulo' ou 'grupo' ou 'idempresa' não foram enviados");
        }
    }

    protected static function imprimirEtiqueta(){
        if(!empty(self::$post["modulo"]) 
            AND !empty(self::$post["ip"]) 
            AND !empty(self::$post["linguagem"]) 
            AND !empty(self::$post["nomeetiqueta"]))
            {

            $_MOD            = self::$post["modulo"];
            $_NOMEETIQUETA   = self::$post["nomeetiqueta"];
            $_LINGUAGEM      = self::$post["linguagem"];
            $_IP             = self::$post["ip"];

            $arqConteudoImpressao = _CARBON_ROOT."inc/php/impressao/etiqueta/".$_MOD."_".$_NOMEETIQUETA."_imp.php";

            if(file_exists($arqConteudoImpressao)) {
                $_CONTEUDOIMPRESSAO = "";
                $_OBJ = self::$post["objetos"];

                include_once($arqConteudoImpressao);

                if(!empty($_CONTEUDOIMPRESSAO)){
                    self::insertLog(
                        self::$post["idetiqueta"], 
                        'etiqueta', 
                        'MÓDULO: '.$_MOD.', IDPESSOA: '.$_SESSION["SESSAO"]["IDPESSOA"], 
                        'IP: '.$_IP.', ETIQUETA: '.$_NOMEETIQUETA
                    );

                    return self::imprimir($_LINGUAGEM, $_CONTEUDOIMPRESSAO, $_IP, self::$post["quantidade"]);

                }else{
                    return self::errorMessage("Conteúdo da impressão está em branco");
                }
            }else{
                return self::errorMessage("Arquivo 'impressao/etiqueta/".$_MOD."_".$_NOMEETIQUETA."_imp.php' não encontrado");
            }
        }else{
            return self::errorMessage("Parâmetros 'módulo' ou 'grupo' ou 'idempresa' não foram enviados");
        }
    }

    protected static function imprimir($linguagem, $conteudo, $ip, $qtdImpressoes){

        if(in_array($linguagem, ['ZPL', 'ESCPOS', 'TSPL'])){
            $i = 0;
            while($i < $qtdImpressoes){
                $retorno = null;
                
                switch($linguagem){
                    case 'ZPL':
                        $retorno = self::imprimirZPL($conteudo, $ip);
                        break;
                    case 'ESCPOS':
                        $retorno = self::imprimirESCPOS($conteudo, $ip);
                        break;
                    case 'TSPL':
                        $retorno = self::imprimirTSPL($conteudo, $ip);
                        break;
                    default:
                        break;
                }

                if($retorno != true){
                    return self::errorMessage($retorno["erro"]);
                }

                $i++;
            }
            return array("sucesso" => "Etiqueta enviada para a impressora");
        }else{
            return self::errorMessage("Linguagem da etiqueta não encontrada");
        }
    }

    protected static function imprimirZPL($content, $ip){
        try
		{
            $fp = pfsockopen($ip, 9100);
            fputs($fp, $content);
            fclose($fp);

            return true;
		}
		catch (Exception $e) {
            return array("erro" => $e->getMessage());
		}
    }

    protected static function imprimirESCPOS($content, $ip){
        try
		{
            $fp = pfsockopen($ip, 9100);
            fputs($fp, $content);
            fclose($fp);

            return true;
		}
		catch (Exception $e) {
            return array("erro" => $e->getMessage());
		}
    }

    protected static function imprimirTSPL($content, $ip){

        try{
            $ar = explode("%_quebrapagina_%",$content);
            foreach ($ar as $key => $value) {
                if (!empty($value)) {
                        $value = preg_replace('%_quebrapagina_%','',$value);
                        $data = array('content'=>$value,	'Send'=>' Print Test ');	
            
                        $QueryString= http_build_query($data);
            
                        $context = stream_context_create(array(
                            'http' => array(
                                'method' => 'GET',
                                'content' => $QueryString,
                            ),
                        ));
            
                        file_get_contents("http://".$ip."/prt_test.htm?".$QueryString, false, $context);
                }
            }

            return true;
        }catch(Exception $e){
            return array("erro" => $e->getMessage());
        }
    }

    protected static function insertLog($idobjeto, $tipoobjeto, $log = "", $info = "", $status = "SUCESSO"){
        $qr = "INSERT INTO `laudo`.`log` (`idempresa`, `tipoobjeto`, `idobjeto`, `tipolog`, `log`, `info`, `status`, `criadoem`, `data`)
        VALUES (".self::$post["idempresa"].", '".$tipoobjeto."', '".$idobjeto."', 'IMPRESSAO', '".$log."', '".$info."', '".$status."', now(), now())";
        d::b()->query($qr);
    }

    protected static function errorMessage($msg = "Erro"){
        return array("erro" => $msg);
    }
}

if(!empty($_GET["__command"])){
    echo ControleImpressaoModulo::exec($_GET["__command"], $_POST);
}else{
    echo '{"erro":"Nenhum comando enviado"}';
}
?>