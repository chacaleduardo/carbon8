<?
require_once __DIR__."/../../inc/php/functions.php";

class NotificacaoController{
    public static $jwt;
    private static $MAX_REGISTROS_REQUISISAO = 10;

    private static function verificaJwt () {
        return (!empty(self::$jwt));
    }

    private static function getIconLocalizacao ($localizacao) {
        $qr = "SELECT 
                    cssicone as icon
                FROM
                    "._DBCARBON."._modulo
                WHERE
                    modulo = '".$localizacao."' 
                UNION SELECT 
                    cssicone as icon
                FROM
                    "._DBCARBON."._snippet
                WHERE
                    modulo = '".$localizacao."'";
        $rs = d::b()->query($qr);
        $rw = mysqli_fetch_assoc($rs);

        return $rw["icon"];
    }

    private static function retArrNotificacoes ( $qr ) {

        $rs = d::b()->query($qr);

        $arrNotif = array();
        $i = 0;

        if(!$rs)
            return $arrNotif;

        while($rw = mysqli_fetch_assoc($rs)){
            $arrNotif[$i]["idnotificacao"]  = $rw["idnotificacao"];
            $arrNotif[$i]["criadoem"]       = formatadatadbweb($rw["criadoem"]);
            $arrNotif[$i]["status"]         = $rw["status"];

            $jsonNotif = json_decode($rw["jsonnotificacao"], true);

            $arrNotif[$i]["mod"]            = $jsonNotif["mod"]             ?? '';
            $arrNotif[$i]["modpk"]          = $jsonNotif["modpk"]           ?? '';
            $arrNotif[$i]["idmod"]          = $jsonNotif["idmod"]           ?? '';
            $arrNotif[$i]["idmodpk"]        = $jsonNotif["idmodpk"]         ?? '';
            $arrNotif[$i]["title"]          = $jsonNotif["title"]           ?? '';
            $arrNotif[$i]["corpo"]          = $jsonNotif["corpo"]           ?? '';
            $arrNotif[$i]["localizacao"]    = $jsonNotif["localizacao"]     ?? '';
            $arrNotif[$i]["url"]            = $jsonNotif["url"]             ?? '';

            $arrNotif[$i]["icon"] = $jsonNotif["localizacao"] == 'notificacoes' 
                ? 'fa-bell' 
                : self::getIconLocalizacao($jsonNotif["localizacao"]);

            $arrNotif[$i]["restricaoPk"]    = 0;
            $arrNotif[$i]["restricaoMod"]   = 0;
            $arrNotif[$i]["restricaoIdNot"] = 0;

            //@TODO: complementar a consulta quando o módulo de configuração de notificações estiver concluído
            $sql = "SELECT idnotificacaorestricao as idrestricao, tipoobjeto
                FROM notificacaorestricao 
                WHERE 
                    idpessoa = ".self::$jwt->idpessoa."
                    AND (
                        (tiporestricao = 'modulo' AND tipoobjeto = '".$jsonNotif["mod"]."' AND idobjeto = ".$jsonNotif["idmod"].")
                        OR (tiporestricao = 'pk' AND tipoobjeto = '".$jsonNotif["modpk"]."' AND idobjeto = ".$jsonNotif["idmodpk"].")
                        -- OR (tipoobjeto = 'notificacao' AND idobjeto = idnotificacao)
                    )";
            $res = d::b()->query($sql);

            while($row = mysqli_fetch_assoc($res)){

                if($row['tipoobjeto'] == 'pk'){

                    $arrNotif[$i]["restricaoPk"] = $row['idrestricao'];

                } else if ($row['tipoobjeto'] == 'modulo') {

                    $arrNotif[$i]["restricaoMod"] = $row['idrestricao'];

                } else if ($row['tipoobjeto'] == 'notificacao') {

                    $arrNotif[$i]["restricaoIdNot"] = $row['idrestricao'];

                }
            }
            $i++;
        }

        return $arrNotif;
    }

    public static function ultimasNotificacoes ( $offset, $filtros = "" ) {
        
        if(!self::verificaJwt())
            return array("message" => "Erro", "info" => "Jwt não encontrado");

        $localizacao = "";
        $status = "AND status IN ('N', 'L')";

        $filtros = json_decode($filtros, true);

        if(!empty($filtros["modulos"])){
            $aspas = "'";
            $virg = "";
            $modulos = "";

            foreach($filtros["modulos"] as $modulo){
                $modulos .= $virg . $aspas . $modulo . $aspas;
                $virg = ",";
            }

            $localizacao = "AND (localizacao IN (".$modulos.") OR modulo IN (".$modulos."))";
        }

        if(!empty($filtros["status"])){
            $aspas = "'";
            $virg = "";
            $s = "";

            foreach($filtros["status"] as $st){
                $s .= $virg . $aspas . $st . $aspas;
                $virg = ",";
            }

            $status = "AND status IN (".$s.")";
        }

        $offset = (empty($offset) AND $offset != 0) ? 0 : $offset;

        $qr = "SELECT * 
                FROM notificacao 
                WHERE idpessoa = " . self::$jwt->idpessoa ." "
                    . $status . " "
                    . $localizacao . 
                " ORDER BY idnotificacao DESC 
                LIMIT ".$offset.",".self::$MAX_REGISTROS_REQUISISAO;
        
        return self::retArrNotificacoes($qr);
    }

    public static function alterarStatusNotificacaoPorId( $idNotificacao, $status ) {

        $qr = "UPDATE notificacao SET status = '".$status."' WHERE idnotificacao = ".$idNotificacao;
        $rs = d::b()->query($qr);

        if(!$rs)
            return array("message" => "Erro", "info" => $qr);

        return array("message" => "OK");
    }

    public static function adicionarRestricaoNotificacaoUsuario ( $tipoRestricao, $idObjeto, $tipoObjeto ) {

        if(!self::verificaJwt())
            return array("message" => "Erro", "info" => "Jwt não encontrado");

        $qr = "INSERT INTO notificacaorestricao (idpessoa, tiporestricao, idobjeto, tipoobjeto, criadoem) 
                VALUES (".self::$jwt->idpessoa.", '".$tipoRestricao."', ".$idObjeto.",'".$tipoObjeto."',now())
                ON DUPLICATE KEY UPDATE criadoem = now()";
        $rs = d::b()->query($qr);

        if(!$rs)
            return array("message" => "Erro", "info" => $qr);

        return array("message" => "OK", "INSERT_ID" => mysqli_insert_id(d::b()));
    }

    public static function removerRestricaoNotificacao ( $idNotificacaoRestricao ) {

        $qr = "DELETE FROM notificacaorestricao WHERE idnotificacaorestricao = ".$idNotificacaoRestricao;
        $rs = d::b()->query($qr);

        if(!$rs)
            return array("message" => "Erro", "info" => $qr);

        return array("message" => "OK");
    }


}
?>