<?php
error_reporting(E_ALL);
require_once './vendor/autoload.php';
$sender = new \Disc\Zabbix\Sender('ping.nash.mobi', 10051);

$sender->addData('supervisorio.laudolab.com.br', 'iot.umidade', 61.8);
$sender->addData('supervisorio.laudolab.com.br', 'iot.temp', 26.6);
$sender->send();

var_export($sender);
