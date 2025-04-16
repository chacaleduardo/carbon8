<? //inc/php

ini_set("display_errors", "1");

error_reporting(E_COMPILE_ERROR | E_RECOVERABLE_ERROR | E_ERROR | E_CORE_ERROR);

/*
 *Parametros de seguranca de dados
 */
define("_DBSERVER", "192.168.11.217:3306"); //endereco ip/name do Database Server **** o para forçar a porta utilize IP: 127.0.0.1/192.x.x.x  https://secure.php.net/manual/pt_BR/mysqli.construct.php#112328 ****
define("_DBAPP", "laudo"); //Database que ira guardar os dados da aplicacao
define("_DBCARBON", "carbonnovo"); //Database que ira guardar os dados da aplicacao
define("_DBUSER", "eduardooliveira"); //Usuario para Login
define("_DBPASS", "2N9nXiWTQ457"); //Senha para acesso ao DB
define("_PEPPER", 'l4aud0.');
define("_JWTNOTIFICACAO", 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJzaXNsYXVkbyIsImV4cCI6MTY4NTUzMTIzOSwicmFtYWxmaXhvIjoiMTk2IiwiaWR0aXBvcGVzc29hIjoiMSIsImlkcGVzc29hIjoiMTA3NTI0IiwidXN1YXJpbyI6InBlZHJvbGltYSIsIm5vbWUiOiJQRURSTyBIRU5SSVFVRSBPTElWRUlSQSBMSU1BIiwiand0X3VuaXEiOiJ0cnNHVm9DMkRkIiwiaWRlbXByZXNhIjoiOCIsImlkYXJxdWl2b2F2YXRhciI6bnVsbCwibGlua2F2YXRhciI6InBlZHJvaF9jZWZjMS5saW1hX2NlZmMxLmpwZyIsInBlcm1pc3Nhb2NoYXQiOiJZIiwiZW5kcG9pbnRfbW9kdWxvc19kaXNwb25pdmVpcyI6IlwvYXBpXC9tb2RcLyIsImVuZHBvaW50X3ByZWZlcmVuY2lhcyI6IlwvYXBpXC9wcmVmXC8ifQ.XOsodP7bmRz6COVtnArd88lqiN6GXPboks1EzeP68Rg');

//variavaveis para integração com api em GOlang
define("_JWTAPIJIRA", 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJhcGxpY2FjYW8iOiIiLCJleHAiOjE4NDAzMTk5OTh9.rUM8WsAq9MDz7NNoA6gt076zRkB3yKHkeBqjG5d0xuQ');
define("_TOKENAPIJIRA", 'ATATT3xFfGF0DIt50ZN4KRlRVWpb8eA1kPDYrBqWDP2F0bKdCPfc6owPFkfOdA6vLUMdETruLQkK68jdqsfBVENkEN-mYwNhLhOFbIxdQFcewczo9mTlN41EwgD2eizA6tSFQnPjMbVHbktVRW4wYPipP9wipag9GvkSF0BoTkUMSe4JFj4gA08=2A8C6492');
define("_USERNAMEAPIJIRA", 'pedrolima@laudolab.com.br');
define("_ROTAPADRAOJIRA", 'https://laudolab.atlassian.net/rest/api/2/');
define("_ROTASADICIONAISJIRA", array('issue' => 'issue/'));
define("_ROTAAPIINTEGRACAOJIRA", "192.168.0.1:3002/");
define("_ROTASADICIONAISAPIINTEGRACAOJIRA", array('create' => 'createissue', 'get' => 'getissue'));

define("_ALARME_MINUTOS_BLOQUEIO_LOGIN", "1440"); //Intervalo para verificacao de tentavias de login
define("_ALARME_QTD_TENTATIVAS_LOGIN", 500); //Intervalo para verificacao de tentavias de login

/*
 * Parâmetros para JWTs
 */
define("_JWTKEY", "TuTeTornasEternamenteResponsavelPorAquiloQueCativas");

$CPRIK = <<<EOD
-----BEGIN RSA PRIVATE KEY-----
MIICXAIBAAKBgQCT5O1BM1iVQDDKB9sEqJa913MHqne8PBg2Q63ehZQIgRlM3zfR
gjibtzoRcfJ3H2nqD6uBUuU/fIsaDGrNZ1l9KkyXgkd+9zy+NagWq6wUfxeM/N1y
Dv/BeEFkRBjQppKtBHLbBEbTuQOtBNgxXluz2Dhs+LSiNtqJHfYHEVQmKwIDAQAB
AoGAVlJMWL4LejHZSFKFd5afRXc3YMYS1P+Ocj3WggcdfEk95yxyfAqx19F+Ryhn
CTiArWkwBW/I9uFOn4mX3QPxqsHPIpJsrzyAiWZ5V5yHOpp1Eocsxk/c1jAmey39
leHGMHb/BqhdYrhFOi5zgZB8MqGisYiAPZVmwPEnRCy8SsECQQDpmQj5utNFBGOc
l3JIYvf2p7HxncgdJWsVNkUuTUlTzIffS2n69LRyPX0BIYTllZqeiOZQ0dj7p6Mb
9o0/eojTAkEAohPRjf0eo0+Tz8p9vxZO4DDOWnce+mzIEmGlYuMWQHaEQB1dcekH
p4VOzU5TJA/viEzAN2MbUsfiO996HTsWSQJARJ0w57maqOEbKTnK1bxMPWUQfXns
97Kv+3EPbQRCj5y6JDqQjKgoAI5TE2v3D0CcRAjOLdsVswWQgXwwDP8/BQJBAI6s
eYzPZBgI5ipFqzn6TkbGT/CM6gUym1CrCmapVp46diLmdqreorFSBVNvfnrBWG+Y
eKCJKrVNZZalHB79M5kCQAgoFczQCHcGugb4Q87Och3jJLzKGpb437/aytUb6I0Q
S3XSuxBJcVY8Pwow7g8HMOLwHcbLWPM9/2MgMbStRz8=
-----END RSA PRIVATE KEY-----
EOD;


// Extract public key from private key
// https://github.com/firebase/php-jwt/issues/116
// $__tr = openssl_get_privatekey($CPRIK);
// $__dt = openssl_pkey_get_details($__tr);
// $CPUBK = $__dt['key'];

// define(_CPRIK, $CPRIK);
// define(_CPUBK, $CPUBK);

/*
 * Chat
 */
define("_CHAT_MAX_HISTORICO", "15");

/*
 * Predefinições especificas para o Laudo Laboratório
 */
define("_DBNFE", "laudo"); //Database que guarda os dados de nota fiscal
define("_URLNFSWS", "http://udigital.uberlandia.mg.gov.br/WsNFe2/LoteRps.jws?wsdl"); //URL para envio de lotes RPS producao
define("_NFSECHOLOG", true); //efetua echo durante o processamento da RPS
define("_NFSECHOLOGXML", false); //efetua echo do XML criado durante o processamento da RPS
//define("_IP_IMPRESSORA_TERMICA1","localhost:81");
//define("_IP_IMPRESSORA_TERMICA1","192.168.0.16");
//define("_IP_IMPRESSORA_CQ","192.168.0.22");
//define("_IP_IMPRESSORA_LOTES","192.168.0.219");
//define("_IP_IMPRESSORA_PRODUCAO","192.168.0.44");
//define("_IP_IMPRESSORA_ALMOXARIFADO","192.168.0.39");
//define("_IP_IMPRESSORA_PRODUCAO_SEM","192.168.0.24");
/*
 *Comportamento dinamico de funcionamento
 */
define("_SITEOUT", false); //Define se o site vai ficar fora do ar para manutencao
define("_SITEOUTMSG", '<strong>Previs&atilde;o para retorno/Return date: 28/06/2020 20:00</strong>'); //Texto de previsao de retorno
define("_TABDEFSESSION", false); //Guardar as definicoes de tabela em session ou buscar novamente do DB em cada requisicao. Caso esteja em FALSE, qualquer alteracao em mtotabcol obriga o usuario a ter de fechar o browser e abrir de novo para usufruir das alteracoes

/*
 *Parametros referentes a diretorios e pastas. Obs: Colocar a / final
 */
define("_CARBON_ROOT", "/var/www/carbon8/");
define("_PARPASTATMPGRAF", _CARBON_ROOT . "tmp/graph"); //Define a pasta onde os graficos do jpgraph serao gerados, para poderem ser apagados posteriormente

/*
 * Outros parametros
 */
define("_intervalolimpezagraficostemp", 6000);

/*
 * Gravar dados de tabelas em DBs distintos
 */
$arrCustomTabDb = array(
	"_modulorelac" => "carbonnovo",
	"_carbonadm" => "carbonnovo",
	"_empresa" => "carbonnovo",
	"_droplet" => "carbonnovo",
	"_formobjetos" => "carbonnovo",
	"_fts" => "carbonnovo",
	"_ftslogtable" => "carbonnovo",
	"_ftsmodulo" => "carbonnovo",
	"_lpgrupo" => "carbonnovo",
	"_lp" => "carbonnovo",
	"_status" => "carbonnovo",
	"_lpobjeto" => "carbonnovo",
	"_lpmodulo" => "carbonnovo",
	"_modulo" => "carbonnovo",
	"_modulorep" => "carbonnovo",
	"_modulofiltros" => "carbonnovo",
	"_modulofiltroshl" => "carbonnovo",
	"_mtotabcol" => "carbonnovo",
	"_paraplweb" => "carbonnovo",
	"_parerr" => "carbonnovo",
	"_rep" => "carbonnovo",
	"_lprep" => "carbonnovo",
	"_repcol" => "carbonnovo",
	"_snippet" => "carbonnovo",
	"_superusuario" => "carbonnovo",
	"_token" => "carbonnovo",
	"_tokenmodulo" => "carbonnovo",
	"_vwpsqdicionariodados" => "carbonnovo",
);
define("_CUSTOMTABDB", serialize($arrCustomTabDb));

/*
 * Tabelas que terao bypass no sistema, e funcionarao indepentente das configuracoes do modulo
 */
$arrTablesBypass = array(
	"eventotimer" => ""
);
define("_BYPASSMOD", serialize($arrTablesBypass));

$tmpUser = shell_exec("whoami");
define("_WHOAMI", $tmpUser);

/*
 *Parametros HTML
 */
define("_TITLE", ":: Carbon ::");
define("_RODAPEESQ", "");
define("_RODAPEDIR", "Desenvolvido por NASH Solu&ccedil;&otilde;es Ltda&nbsp;&reg;");
