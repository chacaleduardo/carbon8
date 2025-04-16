<?
interface iQuery{
    public static function exec() : Result;
}

interface DefaultQuery{
    public static function buscarPorChavePrimaria();

    public const buscarPorChavePrimariaSQL = "SELECT * FROM ?table? WHERE ?pk? = ?pkval?";
    
}

class SQL implements iQuery{
    private static $queryStr;
    private static $moduloAtual = null;

    public static function ini( $query = "", $params = [] ) : iQuery{
        self::$queryStr = (count($params) > 0) 
            ? replaceQueryWildCard($query, $params) 
            : $query;

        self::$moduloAtual = (!empty($_GET['_modulo'])) 
            ? $_GET['_modulo']
            : $_SERVER["SCRIPT_FILENAME"];

        return new static();
    }

    public static function mount($query = "", $params = [])
    {
        return (count($params) > 0) 
            ? replaceQueryWildCard($query, $params) 
            : $query;
    }

    private static function reset(){
        self::$queryStr = null;
    }

    private static function error( $message = '', $sql = '' ){
        // insert log
        return Result::withError($message, $sql);
    }

    private static function inserirLogErros ( $moduloAtual, $error, $sql ) {
        $e = (!empty($_SESSION["SESSAO"]["IDEMPRESA"])) ? $_SESSION["SESSAO"]["IDEMPRESA"] : 1;

        d::b()->query("INSERT INTO log (idempresa, tipoobjeto, tipolog, log, info, status) 
                VALUES (".$e.", '".d::b()->real_escape_string($moduloAtual)."', 'controller', '".d::b()->real_escape_string($error)."', '".d::b()->real_escape_string($sql)."', 'ERRO')");
    }

    public static function exec() : Result{
        $finalQuery = self::$queryStr;
        $moduloAtual = self::$moduloAtual;

        self::reset();

        if(empty($finalQuery)){
            return self::error("A consulta está vazia.", $finalQuery);
        }
    
        $results = d::b()->query($finalQuery);
    
        if(!$results){
            $errorMessage = mysqli_error(d::b());
            // insert log
            if(!empty($moduloAtual))
                self::inserirLogErros($moduloAtual, $errorMessage, $finalQuery);

            return self::error($errorMessage, $finalQuery);
        }else{
            $count = 0;
            $arrResult = array();
            $arrColunas = mysqli_fetch_fields($results);
            $lastInsId = mysqli_insert_id(d::b());
    
            while($row = mysqli_fetch_assoc($results)){
                foreach ($arrColunas as $col) {
                    $arrResult[$count][$col->name]=$row[$col->name];
                }
                $count++;
            }

            $numRows = mysqli_num_rows($results) ?? 0;
            return Result::success($arrResult, $numRows, $finalQuery, $lastInsId);
        }
    }

    public static function setModuloAtual ( $moduloAtual ) {
        self::$moduloAtual = $moduloAtual;
    }
}

class Result {
    private $error = false;
    private $errorMessage;
    private $sql;
    private $numRows = 0;
    private $lastInsId;

    public $data = [];

    function __construct(){}

    public static function withError ( string $message, string $sql ) {
        $instance = new self();
        $instance->setError(true);
        $instance->setErrorMessage($message);
        $instance->setSql($sql);
        return $instance;
    }

    public static function success ( array $data = [], int $numRows = 0, string $sql, int $lastId) {
        $instance = new self();
        $instance->data = $data;
        $instance->setNumRows($numRows);
        $instance->setSql( $sql );
        $instance->setLastInsertId( $lastId );
        return $instance;
    }

    public function error(){
        return $this->error;
    }

    public function errorMessage(){
        return $this->errorMessage;
    }

    public function sql(){
        return $this->sql;
    }

    public function numRows(){
        return $this->numRows;
    }
    
    public function lastInsertId(){
        return $this->lastInsId;
    }

    private function setError( bool $e ){
        $this->error = $e;
    }

    private function setErrorMessage( string $message ){
        $this->errorMessage = $message;
    }

    private function setSql( string $sql ){
        $this->sql = $sql;
    }

    private function setNumRows( int $numRows ){
        $this->numRows = $numRows;
    }

    private function setLastInsertId( $lastInsId ){
        $this->lastInsId = $lastInsId;
    }
}

function replaceQueryWildCard( $query = "",  $params = array() ) : string {

    foreach ( $params as $pattern => $replaceVal ) {
        $query = preg_replace("/(\?".$pattern."\?)/", $replaceVal, $query);
    }

	return $query;
}
?>
