<?php
error_reporting(E_ALL);
require_once './vendor/autoload.php';
$sender = new \Disc\Zabbix\Sender('ping.nash.mobi', 10051);

$sender->addData('supervisorio.laudolab.com.br', 'iot.umidade', $_GET["umid"]);
$sender->addData('supervisorio.laudolab.com.br', 'iot.temp', $_GET["temp"]);
$sender->addData('supervisorio.laudolab.com.br', 'iot.pressao', $_GET["pressao"]);
$sender->send();

//var_export($sender);
