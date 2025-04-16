<?
require_once(__DIR__."/../../inc/php/functions.php");

interface INotifCanal{
    public function canal ( $canal ) : INotifConf;
}

interface INotifConf {
    public function conf ( Array $conf ) : INotifRecipient;
}

interface INotifRecipient {
    public function addDest ( $idpessoa ) : INotifRecipient;
    public function send();
}

class NotifFunction implements INotifCanal, INotifConf, INotifRecipient {
    private $ACCESS_HEADER = "x-access-token";
    private $root = 'http://127.0.0.1:3001/notificacao/';
    private $json = [];
    private $endpoint;

    public $response;

    public function printJson(){
        echo json_encode($this->json);
    }

    private function defineEndPoint ( $endpoint ) {
        $this->endpoint = $endpoint;
    }

    public function canal ( $canal ) : INotifConf {
        $this->defineEndPoint($canal);
        return $this;
    }

    public function conf( Array $conf ) : INotifRecipient {
        if($this->endpoint == 'browser' AND !array_key_exists('idmod', $conf) AND array_key_exists('mod', $conf)){
            $conf['idmod'] = traduzid("_modulo", "modulo", "idmodulo", $conf['mod'], false);
        }

        $this->json["conf"] = $conf;
        return $this;
    }

    public function addDest ( $destinatario ) : INotifRecipient {
        $this->json["destinatarios"][]  = $destinatario;
        return $this;
    }

    public function send() {

        $data = json_encode($this->json);

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header'=> "Content-type: application/json\r\n"
                            .$this->ACCESS_HEADER.": "._JWTNOTIFICACAO."\r\n",
                'content' => $data
            ]
        ]);

        $this->response = file_get_contents($this->root . $this->endpoint, false, $context);
        return $this;
    }
}

class Notif {
    public static function ini() : NotifFunction{
        return new NotifFunction();
    }
}
?>