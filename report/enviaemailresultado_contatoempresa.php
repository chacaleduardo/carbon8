<?
require_once("/var/www/carbon8/inc/php/validaacesso.php");
//require_once("c:/xampp/htdocs/carbon8/inc/php/validaacesso.php");
require(_CARBON_ROOT."/inc/php/composer/vendor/autoload.php");
//require_once(_CARBON_ROOT."/inc/php/composer/vendor/dompdf/dompdf/src/Autoloader.php");
require_once(_CARBON_ROOT."inc/dompdf/dompdf_config.inc.php");

ob_start();

if(!empty($_SESSION["SESSAO"]["USUARIO"])){
	$_usuario = $_SESSION["SESSAO"]["USUARIO"];
}else{
	$_usuario = 'sislaudo';
}
$simulacao=false;
if(!empty($_SESSION["SESSAO"]["IDPESSOA"])){
	$_idpessoa = $_SESSION["SESSAO"]["IDPESSOA"];
}else{
	$_idpessoa = 1029;
}

$conteudo = json_decode($_GET["content"],true);
unset($_GET["idnucleo"]);

if(!empty($conteudo["remetente"])){
    $remetente = $conteudo["remetente"];
}else{
    $remetente = "oficial@laudolab.com.br";
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
//print_r($conteudo);

$sqlemail="";
$virg="";
foreach ($conteudo["destinatario"] as $name => $value) {
    $sqlemail.=$virg.$value['email'];
    $virg=",";
}

//INSERIR DADOS DA COMUNICAÇÃO
$sqlicom = "insert into comunicacaoext (idempresa,tipo,`from`,`to`,idobjeto,tipoobjeto,status,criadoem,criadopor)
values (".$conteudo["idempresa"].",'EMAILCONTATOEMPRESA','".$remetente."','".$sqlemail."',".$conteudo["idnucleo"].",'nucleo','ENVIANDO',sysdate(),'".$_SESSION["SESSAO"]["USUARIO"]."')";
d::b()->query($sqlicom) or die("Erro ao inserir Log ENVIANDO"); //[".mysql_error()."] ".$sqlicom);
//$newidcomunicacao = mysqli_insert_id(d::b());
$newidcomunicacao = d::b()->insert_id;

//Recupera os resultados relacionados ao núcleo, e associa com a comunicação externa
if(!empty($newidcomunicacao)){

    if($conteudo["tipores"] == 'POS'){
        $sqlalerta=" and r.alerta = 'Y' ";
    }else{
        $sqlalerta=" ";
    }

    $sqlf="SELECT r.idresultado
            from resultado r
                ,amostra a
                ,pessoa p
            where p.idpessoa = a.idpessoa
                and r.status = 'ASSINADO'
                and r.idamostra = a.idamostra
                and r.alteradoem between '".$conteudo["alterado_1"]."' and '".$conteudo["alterado_2"]." 23:59:59'
                ".$sqlalerta."
                and a.idpessoa = ".$conteudo["idpessoa"]."
                and a.idnucleo = ".$conteudo["idnucleo"]."
                and a.exercicio = ".$conteudo["exercicio"]."
                and not exists(
                    select 1 from comunicacaoext c,comunicacaoextitem i
                    where c.tipo = 'EMAILCONTATOEMPRESA'
                    and c.status ='SUCESSO'
                    and c.idcomunicacaoext = i.idcomunicacaoext
                    and i.tipoobjeto = 'resultado'
                    and i.idobjeto = r.idresultado
                )
                and a.idempresa = ".$conteudo["idempresa"];

    $resf=d::b()->query($sqlf) or die("Erro ao buscar resultados enviados sql: ".$sqlf);

    $qtdresf=mysqli_num_rows($resf);
    if($qtdresf<1){
        $sqlu11="UPDATE comunicacaoext 
                    set status = 'ATENCAO',
                    conteudo='Resultado já enviado ou não existem resultados pendentes para envio. Tipo Envio:".$conteudo['tipores'].", Núcleo:".$idnucleo.", Idpessoa:".$idpessoa.",Exercício: ".$exercicio."' 
                    where idcomunicacaoext = ".$newidcomunicacao;
        mysql_query($sqlu11) or die("erro ao inserir Log de ERRO [".mysql_error()."] ".$sqlu11);
        die("Resultado já enviado ou não existem resultados pendentes para envio. Verificar com administrador do sistema.");
    }else{
        $sqlu="";
        $avids=array();
        while($rowf=mysqli_fetch_assoc($resf)){
            $sqlu="INSERT INTO `comunicacaoextitem` (idempresa,idcomunicacaoext,idobjeto,tipoobjeto,criadopor,criadoem)
                    values
                    (".$conteudo["idempresa"].",".$newidcomunicacao.",".$rowf['idresultado'].",'resultado','".$_SESSION["SESSAO"]["USUARIO"]."',now())";
            d::b()->query($sqlu) or die("erro ao vincular comunicação ao resultado erro [".mysql_error()."] ".$sqlu);
            $avids[]=$rowf['idresultado'];
        }
    }
}else{
    die("Falha ao gerar comunicação externa!");
}

$_GET["_vids"]=implode(",",$avids);

//Invoca a emissaoresultado
require_once("emissaoresultado.php");
$html = ob_get_contents();
//limpar o codigo html
$html = preg_replace('/>\s+</', "><", $html);

ob_end_clean();
define("DOMPDF_DEFAULT_MEDIA_TYPE", "print");
define("DOMPDF_ENABLE_HTML5PARSER", true);
define("DOMPDF_ENABLE_FONTSUBSETTING", true);
define("DOMPDF_UNICODE_ENABLED", true);

define("DOMPDF_DPI", 86);
define("DOMPDF_ENABLE_REMOTE", true);
define("DOMPDF_DEFAULT_PAPER_SIZE", "A4");

// Instanciamos a classe
$dompdf = new DOMPDF();
$html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
// Passamos o conteúdo que será convertido para PDF
$dompdf->load_html($html,'UTF-8'); 

// Definimos o tamanho do papel e
// sua orientação (retrato ou paisagem)
$dompdf->set_paper('A4','portrait');

// O arquivo é convertido
$dompdf->render();

$nomearq="resultados_".$newidcomunicacao;
$nomearqcompleto=_CARBON_ROOT."/upload/comunicacaoext/".$nomearq.".pdf";
$link = str_replace(_CARBON_ROOT, "", $nomearqcompleto);

$output = $dompdf->output();
file_put_contents($nomearqcompleto,$output);

require(_CARBON_ROOT."/inc/php/composer/vendor/autoload.php");

if(empty($newidcomunicacao)){
    die("Esta vazio o id comunicacao");
}

$exercicio = $conteudo["exercicio"];
$idpessoa = $conteudo["idpessoa"];
$usuario = $conteudo["usuario"];
$idnucleo = $conteudo["idnucleo"];
$idempresa = $conteudo["idempresa"];

$enviook=0;

if(!empty($exercicio) and !empty($idnucleo) and !empty($newidcomunicacao)){
    $sql = "SELECT p.razaosocial as nome
				from pessoa p
				where p.status in ('ATIVO','PENDENTE')
                and p.idpessoa = ".$idpessoa;
    $sqlres = mysql_query($sql) or die("A Consulta dos dados do cliente falhou : " . mysql_error() . "<p>SQL: $sql");

    $qtdres=mysql_num_rows($sqlres);
    if($qtdres<1){
        die("Não foi possivel localizar o cliente ".$idpessoa);
    }

    $row = mysql_fetch_array($sqlres);

    $sqln="SELECT a.lote,a.nucleoamostra as nucleo,a.lacre,a.tc
				from comunicacaoextitem i,resultado r,amostra a
				where i.idcomunicacaoext = ".$newidcomunicacao."
				and i.tipoobjeto='resultado' 
				and r.idresultado=i.idobjeto
				and a.idamostra = r.idamostra";
    $resn=mysql_query($sqln) or die("Erro ao buscar informações do nucleo");
    $rown=mysql_fetch_assoc($resn);

    if($conteudo["tipores"] == 'POS'){
        $positivo="- Positivo";
    }else{
        $positivo="";
    }

    $envioid = geraIdEnvioEmail();
    foreach ($conteudo["destinatario"] as $name => $value) {
        $strchenc="";
				
        //Monta a data que ira expirar o acesso ao sistema  
        $date = date_create(date("Y-m-d"));
        date_add($date, date_interval_create_from_date_string('728 days'));
        $dataval=date_format($date, 'Y-m-d');
                
        if($value['tipo']=='LINK'){				
            //Monta a string que será encriptada
            $stringchave = ("usuario=".$value["usuario"]."&idpessoa=".$idpessoa.'&idcomunicacaoext='.$newidcomunicacao.'&idcontato='.$value['idcontato'].'&email='.$value['email']."&datalimite=".$dataval);
            // Encripta a string
            $strchenc = trim(enc($stringchave));
        }

        if(empty($strchenc) and $value['tipo']=='LINK'){			
            // insere o log de erro
            $sqlu1="update comunicacaoext set status = 'ERRO',conteudo='A string do token esta vazia. Tipo Envio: EMAIL CONTATO EMPRESA: ".$conteudo["tipores"].", Núcleo:".$idnucleo.", Idpessoa:".$idpessoa.",Exercício: ".$exercicio."' where idcomunicacaoext = ".$newidcomunicacao;
            mysql_query($sqlu1) or die("erro ao inserir Log de ERRO [".mysql_error()."] ".$sqlu1);				
        }else{
            //Monta versao TXT (texto puro)
            if($conteudo["tipores"]=="POS"){
                if($value['tipo']=='LINK'){
                    $linkpornf = retpar('urlemailresultadooficial');
                    $message = retpar('textoemailcontatoempresatodosp');
                }else{
                    $message = retpar('textoemailcontatoempresapdfpos');
                }
            }else{
                if($value['tipo']=='LINK'){
                    $linkpornf = retpar('urlemailresultadooficial');
                    $message = retpar('textoemailcontatoempresatodos');
                }else{
                    $message = retpar('textoemailcontatoempresapdf');
                }		
            }
			
			$sqlcliente="SELECT nome FROM pessoa WHERE status = 'ATIVO' and idpessoa = ".$value["idcontato"];
			$rescliente=d::b()->query($sqlcliente) or die("Erro ao buscar nome do contato empresa. sql=".$sqlcliente);
            $rowcliente=mysqli_fetch_assoc($rescliente);

            $sqlrodapeemail = "SELECT * FROM empresarodapeemail WHERE tipoenvio = 'EMAILCONTATOEMPRESA' AND idempresa =".$idempresa." ORDER BY idempresarodapeemail asc limit 1";
            $resrodapeemail=d::b()->query($sqlrodapeemail) or die("Erro ao buscar informações de email da empresa. sql=".$sqlrodapeemail);
            $rowrodapeemail=mysqli_fetch_assoc($resrodapeemail);
			
			$infcomplementar1 = ( strpos( $rowrodapeemail["assunto"], "_info_" ) !== 0 );
			$infcomplementar2 = ( strpos( $rowrodapeemail["assunto"], "_info1_" ) !== 0 );
			$infcomplementar3 = ( strpos( $rowrodapeemail["assunto"], "_info2_" ) !== 0 );
			$infcomplementar4 = ( strpos( $rowrodapeemail["assunto"], "_info3_" ) !== 0 );
			$infcomplementar5 = ( strpos( $rowrodapeemail["assunto"], "_info4_" ) !== 0 );
			
			if ($infcomplementar1) {
			   $rowrodapeemail["assunto"] = str_replace("_info_", $positivo, $rowrodapeemail["assunto"]);
			}
			if ($infcomplementar2) {
			   $rowrodapeemail["assunto"] = str_replace("_info1_", $row["nome"], $rowrodapeemail["assunto"]);
			}
			if ($infcomplementar3) {
			   $rowrodapeemail["assunto"] = str_replace("_info2_", $rown['nucleo'], $rowrodapeemail["assunto"]);
			}
			if ($infcomplementar4) {
			   $rowrodapeemail["assunto"] = str_replace("_info3_", $rown['lote'], $rowrodapeemail["assunto"]);
			}
			if ($infcomplementar5) {
			   $rowrodapeemail["assunto"] = str_replace("_info4_", $exercicio, $rowrodapeemail["assunto"]);
			}

            // GVT - 30/04/2020 - Alterado o rodapé dos emails. Busca o rodapé cadastrado no módulo empresa. Função se encontra em /inc/php/functions.php
            $rodapeemailhtml = imagemtipoemailempresa("EMAILCONTATOEMPRESA",$idempresa,$remetente);
            // Caso a função imagemtipoemailempresa retorne FALSE
            if(!$rodapeemailhtml){
                $rodapeemailhtml = "";
            }

            	//Monta versao HTML
				$messagehtm = $message;
				$messagehtm = str_replace("_cliente", 		$rowcliente["nome"], $messagehtm);
				$messagehtm = str_replace("nome", 		$row["nome"], $messagehtm);
				$messagehtm = str_replace("exercicio", 		$exercicio, $messagehtm);

				if($value['tipo']=='LINK'){
					$urlhtm = $linkpornf.$strchenc;
					$linkpornf = "Para acessar o resultado, clique <a href='".$urlhtm."'>aqui</a>";
					$messagehtm = str_replace("urlresultado", $linkpornf, $messagehtm);
				}
		
				$messagehtm = str_replace("xnucleo", 	$rown['nucleo'], $messagehtm);
				$messagehtm = str_replace("xlote", 		$rown['lote'], $messagehtm);
				$messagehtm = str_replace("xlacre", 	$rown['lacre'], $messagehtm);
				$messagehtm = str_replace("xtc", 		$rown['tc'], $messagehtm);
						
				//se não tiver nucleo tira a palavra Nucleo:
				if(empty($rown['nucleo'])){
                    $messagehtm = str_replace("Núcleo:","", $messagehtm);
                    $messagehtm = str_replace(" / ","", $messagehtm);
				}
			
				//se não tiver lote tira a palavra Lote:
				if(empty($rown['lote'])){
                    $messagehtm = str_replace("Lote:","", $messagehtm);
                    $messagehtm = str_replace(" / ","", $messagehtm);
				}
						
				//se não tiver lacre tira a palavra Lacre:
				if(empty($rown['lacre'])){
					$messagehtm = str_replace("Lacre:","", $messagehtm);
				}

				//senão tiver tc tira a palavra Termo de Coleta:
				if(empty($rown['tc'])){
					$messagehtm = str_replace("Termo de Coleta:","", $messagehtm);
				}
				
						
				$messagehtm = nl2br($messagehtm);
				$messagehtm = $messagehtm.$rodapeemailhtml;
                $messagehtm = "<html><body style='font-family:Arial, Tahoma;font-size:14px;'>".$messagehtm."</body></html>";
                
                $emailFrom=$remetente;
				$nomeFrom=$rowrodapeemail["nomeremetente"];

                if(empty($emailFrom)){
                    $sql = "insert into log (idobjeto,tipoobjeto,tipolog,log,status,criadoem) 
                    values (".$newidcomunicacao.",'comunicacaoext','EMAILCONTATOEMPRESA','REMETENTE VAZIO','ERRO',sysdate())";
        
                    d::b()->query($sql) or die("erro ao inserir Log de erro [".mysqli_error(d::b())."]");
                    die("O remetente está vazio");
                }

                //DESTINATARIO
                if($_GET["_emailteste_"] == 'Y'){
                    if(!empty($_GET["_destinatario_"])){
                        $emailDest=$_GET["_destinatario_"];
                    }else{
                        $emailDest="gabrieltiburcio@laudolab.com.br";
                    }
                }else{
                    $emailDest=$value['email'];
                }
				
				$emailDestNome=$row['nome'];
				if(empty($rowrodapeemail["comcopia"])){
					$auxCC = $dominio;
				}else{
					$auxCC = $rowrodapeemail["comcopia"];
				}
				$emailDestCCO=$auxCC;
				$emailDestCCONome=$rowrodapeemail["nomecc"];
				//CCO
				//$emailDestCCO="resultados@laudolab.com.br";
				//$emailDestCCONome="Resultados Laudo Laboratório";

				//ASSUNTO
                $assunto=$rowrodapeemail["assunto"];
                
                //CONFIGURACOES E ENVIO
				$mail = new PHPMailer(true); //true habilita exceptions
				$mail->SMTPDebug=2; //maf120619: Recuperar o diálogo com o servidor IMAP: https://github.com/PHPMailer/PHPMailer/wiki/SMTP-Debugging

				// GVT - 08/07/2020 - Adicionado array para armazenar assunto, mensagem e anexos do email.
                //					- O array é armazenado na tabela mailfila para ser utilizado posteriormente
                //					- para mostrar o que foi enviado no email e reenvio do mesmo.
                $aMsg=array();
                $aMsg["assunto"] = $assunto;
                $aMsg["mensagem"] = $messagehtm;
                $aMsg["anexos"][0] = $nomearqcompleto;
                try{
                    $mail->IsSMTP();
                    $mail->SMTPAuth  = false;
                    $mail->SMTPAutoTLS = false; //somente para testes
                    $mail->CharSet = "UTF-8";
                    $mail->Host  = '192.168.0.15';
                    $mail->Port  = '587';
                    //$mail->Username  = "admin_laudolab";
                    //$mail->Password  = "37383738";

                    //Espaço em branco para desativar o header 'PHPMAILER version' http://phpmailer.github.io/PHPMailer/classes/PHPMailer.PHPMailer.PHPMailer.html#property_XMailer
                    $mail->XMailer = " ";

                    //Rastrear headers em caixas de email
                    $mail->addCustomHeader('X-IDOBJETO',$newidcomunicacao);
                    $mail->addCustomHeader('X-TIPOOBJETO','comunicacaoext');

                    $mail->From  = $emailFrom;
                    $mail->FromName  = $nomeFrom; //utf8_decode($nomeFrom);

                    $mail->IsHTML(true);
                    $mail->Subject  = $assunto;
                    $mail->Body  = $messagehtm;
                    //email destino
                    $mail->AddAddress($emailDest,$emailDestNome);

                    $mail->AddCustomHeader("X-MAIL-MOD:".strtoupper(explode(".",basename($_SERVER["SCRIPT_FILENAME"]))[0]));
                    //$mail->AddAddress("gabrieltiburcio@laudolab.com.br","Gabriel");
					//$mail->AddAddress("marcelocunha@laudolab.com.br","Marcelo");

                    //copia para testes
                    //$mail->addBCC('william@laudolab.com.br', 'Willian');
                    
                    /*
                        MAF080919: Esta função é uma callback executado durante o diálogo do protocolo SMTP.
                        Neste caso está sendo recuperado o ID gerado pelo servidor SMTP Postfix. Isto servirá para consultar no postfix remoto, se a mensagem foi realmente enviada
                        Atenção para o escopo incassível de variáveis: foi utilizado o recurso de $GLOBALS
                    */
                    $queueid="";
                    $mail->Debugoutput = function($debugstr, $level) {
                        //echo $debugstr; //mostra TUDO na tela. inclusive o conteudo binario do arquivo anexo
                        $pattern='/(queued\ as\ )(.*)/';//Resposta SMTP de sucesso (https://regex101.com/r/rUqXH5/1)
                        if (preg_match($pattern, $debugstr, $match)){
                            //echo($match[2]);
                            global $queueid;
                            $queueid=trim($match[2]);
                            $GLOBALS["queueid"]=trim($queueid);
                        }
                    };

                    //Adicionar um anexo
                    if($value['tipo']=="PDF"){
                        $mail->AddAttachment($nomearqcompleto);
                    }

                    if($simulacao===false){
                        //Envia grazadeus
                        if(!$mail->Send()){
                            $ret.= " ERRO ao enviar email. (" . print($mail). ") ";
                            $envioerro=1;									
                        } else {						
                            // ------------------- Feito por Gabriel Valentin Tiburcio em 06/01/2020
                            $_sql = "insert into mailfila (idempresa,remetente,destinatario,queueid,status,idobjeto,tipoobjeto,idsubtipoobjeto,subtipoobjeto,idpessoa,idenvio,enviadode,link,conteudoemail,criadoem,criadopor,alteradopor,alteradoem) 
                                    values (".$idempresa.",'".$emailFrom."','".$emailDest."','".$queueid."','EM FILA',".$newidcomunicacao.",'comunicacaoext',".$value["idcontato"].",'contatoempresa',".$idpessoa.",'".$envioid."','".$_SERVER["SCRIPT_NAME"]."','".$link."','".base64_encode(serialize($aMsg))."',sysdate(),'".$_usuario."','".$_usuario."',sysdate())";

                            d::b()->query($_sql) or die("Erro ao gerar Log de mailfila [".mysqli_error(d::b())."] ".$_sql);;
                            // ---------------------------------------------------------------------

                            
                            $sqlstmp = "INSERT INTO `logmail`(`idobjeto`,`tipoobjeto`,`destinatario`,`queueid`,`log`,`criadoem`)
                            VALUES (".$newidcomunicacao.",'comunicacaoext','".$emailDest."','".$GLOBALS["queueid"]."','',now());";
                            d::b()->query($sqlstmp) or die("Erro ao gerar Log de Smtp [".mysqli_error(d::b())."] ".$sqlstmp);

                            //echo "Email enviado com sucesso!";
                            $ret .= " Enviado email com sucesso! [".$emailDest."] ";
                            $retx .= " Enviado email para ".$value['email']." com sucesso! Token(".$strchenc.") ";
                            $enviook=1;	

                        }
                    }else{
                        //echo "\n\n-----------Mail getAllRecipientAddresses";
                        //print_r($mail->getAllRecipientAddresses());
                        //echo "\n\n-----------";
                        
                        $ret = "\n<Br>Simulação para ".$value['email']." executada com sucesso! ";
                        $retx = "\n<br>Simulação de link para ".$value['email']." criado com sucesso! Token(".$strchenc.") ";
                        $enviook=1;
                        echo $ret;
                        echo $retx;				
                    }

                }catch (Exception $e) {
                    echo "Erro do PEAR::MAIL -> {$mail->ErrorInfo}";
                }
                // Apaga conteúdo do array para a próxima iteração do loop
                unset($aMsg);
        }
    }

}else{
    $sqlu1="update comunicacaoext set status = 'ERRO',conteudo='ERRO ao tentar enviar o email. (Campo nucleo, exercicio e secretaria devem ser informados!). Tipo Envio: EMAIL CONTATO EMPRESA: ".$conteudo["tipores"].", Núcleo:".$idnucleo.", Idpessoa:".$idpessoa.",Exercício: ".$exercicio."' where idcomunicacaoext = ".$newidcomunicacao;
    mysql_query($sqlu1) or die("erro ao inserir Log de ERRO [".mysql_error()."] ".$sqlu1);
    echo "ERRO ao tentar enviar o email. (Campo nucleo, exercicio e secretaria devem ser informados!)";	
    die($ret);
}

if($enviook==1){

    if($GLOBALS["queueid"]==""){
        $messagetxt.="\nAtenção: QUEUEID não recuperado!";
    }

    $sqlu1="update comunicacaoext set status = 'SUCESSO',conteudo='Tipo Envio:".$conteudo["tipores"].", Núcleo:".$idnucleo.", Idpessoa:".$idpessoa.",Exercício: ".$exercicio."',queueid='".$GLOBALS["queueid"]."' where idcomunicacaoext = ".$newidcomunicacao;		
    mysql_query($sqlu1) or die("erro ao inserir Log de SUCESSO [".mysql_error()."] ".$sqlu1);
    
}else{
    $sqlu1="update comunicacaoext set status = 'ERRO',conteudo='".$retx."' where idcomunicacaoext = ".$newidcomunicacao;
    mysql_query($sqlu1) or die("erro ao inserir Log de ERRO [".mysql_error()."] ".$sqlu1);			
}

if($_GET["_emailteste_"] == 'Y' and empty($_GET["_ndeletar_"])){
    echo "<br><br>DELETANDO REGISTRO: Comunicaçãoext nº ".$newidcomunicacao."<br><br>";
    $sqldel="UPDATE comunicacaoext SET status = 'EMAILTESTE', conteudo = 'Email teste, favor ignorar' WHERE idcomunicacaoext = ".$newidcomunicacao;
    mysql_query($sqldel) or die("erro ao Atualizar comunicacaoext [".mysql_error()."] ".$sqldel);

    $sqldel1="DELETE FROM comunicacaoextitem WHERE idcomunicacaoext = ".$newidcomunicacao;
    mysql_query($sqldel1) or die("erro ao deletar comunicacaoextitem [".mysql_error()."] ".$sqldel1);

    $sqldel1="DELETE FROM mailfila WHERE tipoobjeto = 'comunicacaoext' and idobjeto = ".$newidcomunicacao;
    mysql_query($sqldel1) or die("erro ao deletar comunicacaoextitem [".mysql_error()."] ".$sqldel1);
}

echo($ret);
?>