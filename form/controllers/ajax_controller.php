<?php
require_once 'core_ajax.php';

$c = new Core;

class AjaxController {
    public static function mostrarErroUrlAjax($erro){
        echo 'Erro - Verifique a URL - '.$erro;
    }
}