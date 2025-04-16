<?php


ini_set("display_errors","1");
error_reporting(E_ALL ^ E_NOTICE) ;
//error_reporting(E_ERROR);

$directory_to_iterate='/var/www/carbon50/';

$directories = new RecursiveIteratorIterator(
    new ParentIterator(new RecursiveDirectoryIterator($directory_to_iterate)), 
    RecursiveIteratorIterator::SELF_FIRST);

//$filtro_pastas = "/.(php)/i";
//$filtro_pastas = "/.(cur|new|tmp)./i";

$arrEs=array();

foreach ($directories as $directory) {

	//filtra as pastas /cur e /new que contem as strings /list no nome
	//qif(preg_match($filtro_pastas, $directory, $matches)) {
		//echo $directory."\n";
		//loop nos diretorios de /list
		foreach (new DirectoryIterator($directory) as $fileInfo) {
			if($fileInfo->isDot()) continue;
			$nomearq = $directory."/".$fileInfo->getFilename();
			
			//verifica se eh bloqueio ou liberacao
			$blwl = (preg_match("/(list)/i", $directory))?"list":"list";
			//echo $blwl."->".$nomearq."\n";
			echo "\n________________________________________\n";

//require_once('MimeParser.class.php');
//	$e = parse_msg_parse_file ( $nomearq );
//	$print_r($e);

			//Abre o arquivo para leitura do conteudo
			$handle = fopen($nomearq, "r");
			//if ($handle) {
			if ($handle and strpos($nomearq,'.php') !== false) {
				$iFrom=0;
				echo "arquivo: ".$nomearq."\n";
				while (($line = fgets($handle)) !== false) {
					$line=trim($line);
					if(empty($line))break;

					//se a linha contem algum dos headers padrao
					//O FROM não vai ter a clausula [^] de comparação de inicio da string, porque alguns emalis estavanm vindo com quebvra de linha e utf8. isto passa a contemplar tambem a linha do DKIM de [;from:]
					$expReg="//i";

					//Extrai o e da linha
					$expRegExtracaoE= '/[._-\w]+@[._-\w]+[.].[._-\w]*/';

					if((preg_match($expReg, $line))){
						//considerar es com ponto, underline e traço
						preg_match($expRegExtracaoE, $line, $domain);
						
						if(!empty($domain[0])){
							$arrEs[$domain[0]]["arquivo"]=$nomearq;
							$arrEs[$domain[0]]["blwl"]=$blwl;
							echo("E encontrado para ".$blwl.": ".$domain[0]."\n");
							$iFrom++;
						}else{
							//echo "expReg vazia 1: [$expReg]\n";
						}
					}else{
						if(strpos($line, ":") === false){
							preg_match($expRegExtracaoE, $line, $domain);
						
							if(!empty($domain[0])){
								$arrEs[$domain[0]]["arquivo"]=$nomearq;
								$arrEs[$domain[0]]["blwl"]=$blwl;
								echo("E encontrado para ".$blwl.": ".$domain[0]."\n");
								$iFrom++;
							}
						}
					}

					//Tenta recuperar o destinatario
					$expRegDTo="/^Delivered-To:*/i";
					$expRegTo="/^To:*/i";
					if((preg_match($expRegDTo, $line))){
						//recuperar destinatario de forwardings
						preg_match('/[._-\w]+@[._-\w]+[.].[._-\w]*/', $line, $destinatario);
						if(!empty($domain[0]) and !empty($destinatario[0])){
							echo("Destinatario Delivered-To encontrado: ".$destinatario[0]."\n");
							$arrEs[$domain[0]]["destinatario"]=$destinatario[0];
						}
					}elseif((preg_match($expRegTo, $line))){
						//recuperar destinatario unico
						preg_match('/[._-\w]+@[._-\w]+[.].[._-\w]*/', $line, $destinatario);
						if(!empty($domain[0]) and !empty($destinatario[0])){
							echo("Destinatario To encontrado: ".$destinatario[0]."\n");
							$arrEs[$domain[0]]["destinatario"]=$destinatario[0];
						}
					}
					

				}//while

			} else {
				echo "Arquivo inválido [".$nomearq."]";
			}
			fclose($handle);

		}
	//}if pregmatch

}

//print_r($arrEs);

$conn = mysql_connect('localhost', 'root', '');
$db = mysql_select_db('postfix', $conn);



foreach ($arrEs as $e => $det) {
	$e=trim($e);
	if(!empty($e)){

//		echo "------------------------------------\n";
//		echo "Banco de dados:\n";
		
		$sqldel = 'delete from userpref where username = \'$GLOBAL\' and preference in (\'list_from\',\'list_from\') and value = \''.$e.'\'';
		$sqlins = 'insert into postfix.userpref (username, preference, value, descript) values (\'$GLOBAL\',\''.$det["blwl"].'_from\',\''.$e.'\',\''.$det["arquivo"].'\')';

		$res1 = mysql_query($sqldel) or die("Erro1:".mysql_error());
		$res2 = mysql_query($sqlins) or die("Erro2:".mysql_error());
		
		if($res1 and $res2 and $det["blwl"]=="list"){
			//apaga o arquivo
			@unlink($det["arquivo"]);
			echo "arquivo apagado";
		}else{
		
			//Tenta reenviar o e para a caixa de entrada

			//extrai a parte da pasta do usuario do caminho do arquivo
			$folderInbox = str_replace($directory_to_iterate, "", $det["arquivo"]);

			//Separa o nome da pasta do usuario
			$folderInbox = explode("/",$folderInbox);
			//echo $folderInbox[0]."\n";
			//echo "destinatario:".$det["destinatario"];
			//$sqlins = 'insert into postfix.userpref (username, preference, value, descript) values (\'$GLOBAL\',\''.$det["blwl"].'_from\',\''.$e.'\',\''.$det["arquivo"].'\')';

			//$res1 = mysql_query($sqldel) or die("Erro1:".mysql_error());

		}

	}
}

?>
