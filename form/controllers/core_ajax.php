<?php

require_once(__DIR__."/../../inc/php/validaacesso.php");


class Core
{
    public function __construct()
    {
        $this->run();
    }

    public function run()
    {   
        $controller = 'AjaxController';
        $metodo = 'mostrarErroUrlAjax';
        $parametros = array();

        if (isset($_GET['_cmp'])) {
            $url = $_GET['_cmp'];
        }

        //Possui informação após dominio www.site.com/classe/função/parametro
        if (!empty($url)) {

            $url = explode('/', $url);

            $caminho = $url[0].'.php';


            $controller = ucfirst($url[1]); //Pega a classe
            
            if(isset($url[2]) && !empty($url[2])){
                $metodo = $url[2]; //Pega o Método 
            }
            
            if(isset($url[3]) && !empty($url[2])){
                $parametros = array($url[3]); // Pega O parametro
            }

        }

        //verifica se o arquivo existe
        if(!file_exists($caminho)){
            $controller = 'AjaxController';
            $metodo = 'mostrarErroUrlAjax';
            $caminho = 'ajax_controller.php';
            $parametros[0] = 'caminho não encontrado';
        }       
            
        require_once $caminho; 
        
        //verifica se a Classe Existe
        if(!class_exists($controller)){
            $controller = 'AjaxController';
            $metodo = 'mostrarErroUrlAjax';
            $caminho = 'ajax_controller.php';
            $parametros[0] = 'Classe não encontrada';
            $c = new $controller;
        }  else {        
            $c = new $controller;
        }
        
        //Verifica se o Método Existe;
        if(!method_exists($controller, $metodo)){
            $controller = 'AjaxController';
            $metodo = 'mostrarErroUrlAjax';
            $caminho = 'ajax_controller.php';
            $parametros[0] = 'Método não encontrado';
            $c = new $controller;
        }  

        call_user_func_array(array($c,$metodo), $parametros);
    }
}