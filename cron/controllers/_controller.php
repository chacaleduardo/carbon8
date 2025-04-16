<?
class ControllerCron{
    public static $controllerErrors = [];

    public static function error( $class = "", $function = "", $message = "" ){
        array_push(self::$controllerErrors, $class."::".$function.": ".$message);
    }

    public static function toFillSelect ( $arr = [] ) {
        if( count($arr) < 1 ) return [];

        foreach($arr as $key => $row){
            $keys = array_keys($row);
            $newArray[$row[$keys[0]]] = $row[$keys[1]];
        }

        return $newArray;
    }

    public static function toJson ( $arr = [] ) {
        return json_encode($arr);
    }
}
?>