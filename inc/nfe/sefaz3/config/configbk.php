<?php
/**
 * Parâmetros de configuração do sistema
 * Última alteração em 15-08-2017 12:50:28 
 **/

//###############################
//########## GERAL ##############
//###############################
// tipo de ambiente esta informação deve ser editada pelo sistema
// 1-Produção 2-Homologação
// esta variável será utilizada para direcionar os arquivos e
// estabelecer o contato com o SEFAZ
$ambiente=;
//esta variável contêm o nome do arquivo com todas as url dos webservices do sefaz
//incluindo a versao dos mesmos, pois alguns estados não estão utilizando as
//mesmas versões
$arquivoURLxml="";
$arquivoURLxmlCTe="";
//Diretório onde serão mantidos os arquivos com as NFe em xml
//a partir deste diretório serão montados todos os subdiretórios do sistema
//de manipulação e armazenamento das NFe e CTe
$arquivosDir="";
$arquivosDirCTe="";
//URL base da API, passa a ser necessária em virtude do uso dos arquivos wsdl
//para acesso ao ambiente nacional
$baseurl="";
//Versão em uso dos shemas utilizados para validação dos xmls
$schemes="";
$schemesCTe="";

//###############################
//###### EMPRESA EMITENTE #######
//###############################
//Nome da Empresa
$empresa="";
//Sigla da UF
$UF="";
//Código da UF
$cUF="";
//Número do CNPJ
$cnpj="";

//###############################
//#### CERITIFICADO DIGITAL #####
//###############################
//Nome do certificado que deve ser colocado na pasta certs da API
$certName="";
//Senha da chave privada
$keyPass="";
//Senha de decriptaçao da chave, normalmente não é necessaria
$passPhrase="";

//###############################
//############ DANFE ############
//###############################
//Configuração do DANFE
$danfeFormato=""; //P-Retrato L-Paisagem 
$danfePapel=""; //Tipo de papel utilizado 
$danfeCanhoto=; //se verdadeiro imprime o canhoto na DANFE 
$danfeLogo=""; //passa o caminho para o LOGO da empresa 
$danfeLogoPos=""; //define a posição do logo na Danfe L-esquerda, C-dentro e R-direta 
$danfeFonte=""; //define a fonte do Danfe limitada as fontes compiladas no FPDF (Times) 
$danfePrinter=""; //define a impressora para impressão da Danfe 

//###############################
//############ DACTE ############
//###############################
//Configuração do DACTE
$dacteFormato=""; //P-Retrato L-Paisagem 
$dactePapel=""; //Tipo de papel utilizado 
$dacteCanhoto=; //se verdadeiro imprime o canhoto na DANFE 
$dacteLogo=""; //passa o caminho para o LOGO da empresa 
$dacteLogoPos=""; //define a posição do logo na Danfe L-esquerda, C-dentro e R-direta 
$dacteFonte=""; //define a fonte do Danfe limitada as fontes compiladas no FPDF (Times) 
$dactePrinter=""; //define a impressora para impressão da Dacte 

//###############################
//############ EMAIL ############
//###############################
//Configuração do email
$mailAuth=""; //ativa ou desativa a obrigatoriedade de autenticação no envio de email, na maioria das vezes ativar 
$mailFROM=""; //identificação do emitente 
$mailHOST=""; //endereço do servidor SMTP 
$mailUSER=""; //username para autenticação, usando quando mailAuth é 1
$mailPASS=""; //senha de autenticação do serviço de email
$mailPROTOCOL=""; //protocolo de email utilizado (classe alternate)
$mailPORT=""; //porta utilizada pelo smtp (classe alternate)
$mailFROMmail=""; //para alteração da identificação do remetente, pode causar problemas com filtros de spam 
$mailFROMname=""; //para indicar o nome do remetente 
$mailREPLYTOmail=""; //para indicar o email de resposta
$mailREPLYTOname=""; //para indicar email de cópia
$mailIMAPhost=""; //url para o servidor IMAP
$mailIMAPport=""; //porta do servidor IMAP
$mailIMAPsecurity=""; //esquema de segurança do servidor IMAP
$mailIMAPnocerts=""; //desabilita verificação de certificados do Servidor IMAP
$mailIMAPbox=""; //caixa postal de entrada do servidor IMAP
$mailLayoutFile=""; //layout da mensagem do email

//###############################
//############ PROXY ############
//###############################
//Configuração de Proxy
$proxyIP=""; //ip do servidor proxy, se existir 
$proxyPORT=""; //numero da porta usada pelo proxy 
$proxyUSER=""; //nome do usuário, se o proxy exigir autenticação
$proxyPASS=""; //senha de autenticação do proxy 

?>