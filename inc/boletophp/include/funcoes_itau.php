<?php
// +----------------------------------------------------------------------+
// | BoletoPhp - Vers�o Beta                                              |
// +----------------------------------------------------------------------+
// | Este arquivo est� dispon�vel sob a Licen�a GPL dispon�vel pela Web|
// | em http://pt.wikipedia.org/wiki/GNU_General_Public_License           |
// | Voc� deve ter recebido uma c�pia da GNU Public License junto com   |
// | esse pacote; se n�o, escreva para:                                  |
// |                                                                      |
// | Free Software Foundation, Inc.                                       |
// | 59 Temple Place - Suite 330                                          |
// | Boston, MA 02111-1307, USA.                                          |
// +----------------------------------------------------------------------+

// +----------------------------------------------------------------------+
// | Originado do Projeto BBBoletoFree que tiveram colabora��es de Daniel|
// | William Schultz e Leandro Maniezo que por sua vez foi derivado do	  |
// | PHPBoleto de Jo�o Prado Maia e Pablo Martins F. Costa				 |
// | 																	  |
// | Se vc quer colaborar, nos ajude a desenvolver p/ os demais bancos :-)|
// | Acesse o site do Projeto BoletoPhp: www.boletophp.com.br             |
// +----------------------------------------------------------------------+

// +----------------------------------------------------------------------+
// | Equipe Coordena��o Projeto BoletoPhp: <boletophp@boletophp.com.br>  |
// | Desenvolvimento Boleto Ita�: Glauber Portella		                  |
// +----------------------------------------------------------------------+

$codigobanco = "341";
$codigo_banco_com_dv = geraCodigoBanco($codigobanco);
$nummoeda = "9";
//$fator_vencimento = fator_vencimento($dadosboleto["data_vencimento"]);

$datacorte = '22/02/2025';

// Convertendo as datas para o formato YYYY-MM-DD para comparação correta
$data_vencimento = DateTime::createFromFormat('d/m/Y', $dadosboleto["data_vencimento"]);
$data_corte = DateTime::createFromFormat('d/m/Y', $datacorte);

if ($data_vencimento >= $data_corte) {
    $fator_vencimento = fator_vencimento($dadosboleto["data_vencimento"]);
} else {
    $fator_vencimento = fator_vencimento_1997($dadosboleto["data_vencimento"]);
}

//valor tem 10 digitos, sem virgula
$valor = formata_numero($dadosboleto["valor_boleto"], 10, 0, "valor");
//agencia � 4 digitos
$agencia = formata_numero($dadosboleto["agencia"], 4, 0);
//conta � 5 digitos + 1 do dv
$conta = formata_numero($dadosboleto["conta"], 5, 0);
$conta_dv = formata_numero($dadosboleto["conta_dv"], 1, 0);
//carteira 109
$carteira = $dadosboleto["carteira"];
//nosso_numero no maximo 8 digitos
$nnum = formata_numero($dadosboleto["nosso_numero"], 8, 0);

$codigo_barras = $codigobanco . $nummoeda . $fator_vencimento . $valor . $carteira . $nnum . modulo_10($agencia . $conta . $carteira . $nnum) . $agencia . $conta . modulo_10($agencia . $conta) . '000';
// 43 numeros para o calculo do digito verificador
$dv = digitoVerificador_barra($codigo_barras);
// Numero para o codigo de barras com 44 digitos
$linha = substr($codigo_barras, 0, 4) . $dv . substr($codigo_barras, 4, 43);

$nossonumero = $carteira . '/' . $nnum . '-' . modulo_10($agencia . $conta . $carteira . $nnum);
$agencia_codigo = $agencia . " / " . $conta . "-" . modulo_10($agencia . $conta);

$dadosboleto["codigo_barras"] = $linha;
$dadosboleto["linha_digitavel"] = monta_linha_digitavel($linha); // verificar
$dadosboleto["agencia_codigo"] = $agencia_codigo;
$dadosboleto["nosso_numero"] = $nossonumero;
$dadosboleto["codigo_banco_com_dv"] = $codigo_banco_com_dv;

// Algumas foram retiradas do Projeto PhpBoleto e modificadas para atender as particularidades de cada banco

//vamos validar se está ativo a impressão do boleto por api
$sql = "SELECT * FROM configboleto where idempresa = {$row['idempresa']} and codigobanco = $codigobanco and `status` = 'ATIVO';";
$resultativo = d::b()->query($sql) or die("Erro ao buscar informações do cedente" . mysqli_error(d::b()));
$configboleto = mysqli_fetch_assoc($resultativo);

if ($resultativo->num_rows > 0) {

    //vamos validar se já foi registrado o boleto no banco
    $sql = "select * from boletopix where idcontapagar = {$_REQUEST['idcontapagar']};";
    $result = d::b()->query($sql);

    if ($result->num_rows > 0) {
        $dadosbanco = $result->fetch_assoc();

        $dadosboleto["codigo_barras"] = $dadosbanco['codigodebarras'];
        $dadosboleto["linha_digitavel"] = $dadosbanco['linhadigitavel'];
        $dadosboleto["qrcode"] = $dadosbanco['qrcodebase64'];
    } else {
        //chama a função para gerar o boleto com qrcode
        $boletoregistrado = geraBoletoQrcode($dadosboleto, $row, $roe, $configboleto);
        $json_decodificado = json_decode($boletoregistrado);

        //vamos salvar os dados no banco
        $sql = "insert into boletopix 
                        (
                            idempresa, idcontapagar, 
                            linhadigitavel,
                            codigodebarras, 
                            qrcodebase64,
                            status, criadopor, criadoem, alteradopor, alteradoem
                        )
                        values
                        (
                            {$row['idempresa']}, {$_REQUEST['idcontapagar']},
                            {$json_decodificado->data->dado_boleto->dados_individuais_boleto[0]->numero_linha_digitavel}, 
                            {$json_decodificado->data->dado_boleto->dados_individuais_boleto[0]->codigo_barras}, '" . $json_decodificado->data->dados_qrcode->base64 . "', 
                            'PROCESSADO', 'API', NOW(), 'API', NOW()
                        );";

        $res = d::b()->query($sql);

        $dadosboleto["codigo_barras"] = $json_decodificado->data->dado_boleto->dados_individuais_boleto[0]->codigo_barras;
        $dadosboleto["linha_digitavel"] = $json_decodificado->data->dado_boleto->dados_individuais_boleto[0]->numero_linha_digitavel;
        $dadosboleto["qrcode"] = $json_decodificado->data->dados_qrcode->base64;
    }
}

function digitoVerificador_barra($numero)
{
    $resto2 = modulo_11($numero, 9, 1);
    $digito = 11 - $resto2;
    if ($digito == 0 || $digito == 1 || $digito == 10  || $digito == 11) {
        $dv = 1;
    } else {
        $dv = $digito;
    }
    return $dv;
}

function formata_numero($numero, $loop, $insert, $tipo = "geral")
{
    if ($tipo == "geral") {
        $numero = str_replace(",", "", $numero);
        while (strlen($numero) < $loop) {
            $numero = $insert . $numero;
        }
    }
    if ($tipo == "valor") {
        /*
		retira as virgulas
		formata o numero
		preenche com zeros
		*/
        $numero = str_replace(",", "", $numero);
        while (strlen($numero) < $loop) {
            $numero = $insert . $numero;
        }
    }
    if ($tipo == "convenio") {
        while (strlen($numero) < $loop) {
            $numero = $numero . $insert;
        }
    }
    return $numero;
}


function fbarcode($valor)
{

    $fino = 1;
    $largo = 3;
    $altura = 50;

    $barcodes[0] = "00110";
    $barcodes[1] = "10001";
    $barcodes[2] = "01001";
    $barcodes[3] = "11000";
    $barcodes[4] = "00101";
    $barcodes[5] = "10100";
    $barcodes[6] = "01100";
    $barcodes[7] = "00011";
    $barcodes[8] = "10010";
    $barcodes[9] = "01010";
    for ($f1 = 9; $f1 >= 0; $f1--) {
        for ($f2 = 9; $f2 >= 0; $f2--) {
            $f = ($f1 * 10) + $f2;
            $texto = "";
            for ($i = 1; $i < 6; $i++) {
                $texto .=  substr($barcodes[$f1], ($i - 1), 1) . substr($barcodes[$f2], ($i - 1), 1);
            }
            $barcodes[$f] = $texto;
        }
    }
?>
    <img src=imagens/p.png width=<?php echo $fino ?> height=<?php echo $altura ?> border=0><img
        src=imagens/b.png width=<?php echo $fino ?> height=<?php echo $altura ?> border=0><img
        src=imagens/p.png width=<?php echo $fino ?> height=<?php echo $altura ?> border=0><img
        src=imagens/b.png width=<?php echo $fino ?> height=<?php echo $altura ?> border=0><img
        <?php
        $texto = $valor;
        if ((strlen($texto) % 2) <> 0) {
            $texto = "0" . $texto;
        }

        // Draw dos dados
        while (strlen($texto) > 0) {
            $i = round(esquerda($texto, 2));
            $texto = direita($texto, strlen($texto) - 2);
            $f = $barcodes[$i];
            for ($i = 1; $i < 11; $i += 2) {
                if (substr($f, ($i - 1), 1) == "0") {
                    $f1 = $fino;
                } else {
                    $f1 = $largo;
                }
        ?>
        src=imagens/p.png width=<?php echo $f1 ?> height=<?php echo $altura ?> border=0><img
        <?php
                if (substr($f, $i, 1) == "0") {
                    $f2 = $fino;
                } else {
                    $f2 = $largo;
                }
        ?>
        src=imagens/b.png width=<?php echo $f2 ?> height=<?php echo $altura ?> border=0><img
        <?php
            }
        }

        // Draw guarda final
        ?>
        src=imagens/p.png width=<?php echo $largo ?> height=<?php echo $altura ?> border=0><img
        src=imagens/b.png width=<?php echo $fino ?> height=<?php echo $altura ?> border=0><img
        src=imagens/p.png width=<?php echo 1 ?> height=<?php echo $altura ?> border=0>
<?php
} //Fim da func

function esquerda($entra, $comp)
{
    return substr($entra, 0, $comp);
}

function direita($entra, $comp)
{
    return substr($entra, strlen($entra) - $comp, $comp);
}

function fator_vencimento_1997($data)
{
    $data = explode("/", $data);
    $ano = $data[2];
    $mes = $data[1];
    $dia = $data[0];
    return (abs((_dateToDays("1997", "10", "07")) - (_dateToDays($ano, $mes, $dia))));
}
function fator_vencimento($data)
{
    $data = explode("/", $data);
    $ano = $data[2];
    $mes = $data[1];
    $dia = $data[0];
    //return(abs((_dateToDays("2025","02","22")) - (_dateToDays($ano, $mes, $dia))));
    return (abs((_dateToDays("2025", "02", "22")) - (_dateToDays($ano, $mes, $dia))) + 1000);
}

function _dateToDays($year, $month, $day)
{
    $century = substr($year, 0, 2);
    $year = substr($year, 2, 2);
    if ($month > 2) {
        $month -= 3;
    } else {
        $month += 9;
        if ($year) {
            $year--;
        } else {
            $year = 99;
            $century--;
        }
    }
    return (floor((146097 * $century)    /  4) +
        floor((1461 * $year)        /  4) +
        floor((153 * $month +  2) /  5) +
        $day +  1721119);
}

function modulo_10($num)
{
    $numtotal10 = 0;
    $fator = 2;

    // Separacao dos numeros
    for ($i = strlen($num); $i > 0; $i--) {
        // pega cada numero isoladamente
        $numeros[$i] = substr($num, $i - 1, 1);
        // Efetua multiplicacao do numero pelo (falor 10)
        // 2002-07-07 01:33:34 Macete para adequar ao Mod10 do Ita�
        $temp = $numeros[$i] * $fator;
        $temp0 = 0;
        foreach (preg_split('//', $temp, -1, PREG_SPLIT_NO_EMPTY) as $k => $v) {
            $temp0 += $v;
        }
        $parcial10[$i] = $temp0; //$numeros[$i] * $fator;
        // monta sequencia para soma dos digitos no (modulo 10)
        $numtotal10 += $parcial10[$i];
        if ($fator == 2) {
            $fator = 1;
        } else {
            $fator = 2; // intercala fator de multiplicacao (modulo 10)
        }
    }

    // v�rias linhas removidas, vide fun��o original
    // Calculo do modulo 10
    $resto = $numtotal10 % 10;
    $digito = 10 - $resto;
    if ($resto == 0) {
        $digito = 0;
    }

    return $digito;
}

function modulo_11($num, $base = 9, $r = 0)
{
    /**
     *   Autor:
     *           Pablo Costa <pablo@users.sourceforge.net>
     *
     *   Fun��o:
     *    Calculo do Modulo 11 para geracao do digito verificador 
     *    de boletos bancarios conforme documentos obtidos 
     *    da Febraban - www.febraban.org.br 
     *
     *   Entrada:
     *     $num: string num�rica para a qual se deseja calcularo digito verificador;
     *     $base: valor maximo de multiplicacao [2-$base]
     *     $r: quando especificado um devolve somente o resto
     *
     *   Sa�da:
     *     Retorna o Digito verificador.
     *
     *   Observa��es:
     *     - Script desenvolvido sem nenhum reaproveitamento de c�digo pr� existente.
     *     - Assume-se que a verifica��o do formato das vari�veis de entrada � feita antes da execu��o deste script.
     */

    $soma = 0;
    $fator = 2;

    /* Separacao dos numeros */
    for ($i = strlen($num); $i > 0; $i--) {
        // pega cada numero isoladamente
        $numeros[$i] = substr($num, $i - 1, 1);
        // Efetua multiplicacao do numero pelo falor
        $parcial[$i] = $numeros[$i] * $fator;
        // Soma dos digitos
        $soma += $parcial[$i];
        if ($fator == $base) {
            // restaura fator de multiplicacao para 2 
            $fator = 1;
        }
        $fator++;
    }

    /* Calculo do modulo 11 */
    if ($r == 0) {
        $soma *= 10;
        $digito = $soma % 11;
        if ($digito == 10) {
            $digito = 0;
        }
        return $digito;
    } elseif ($r == 1) {
        $resto = $soma % 11;
        return $resto;
    }
}

// Alterada por Glauber Portella para especifica��o do Ita�
function monta_linha_digitavel($codigo)
{
    // campo 1
    $banco    = substr($codigo, 0, 3);
    $moeda    = substr($codigo, 3, 1);
    $ccc      = substr($codigo, 19, 3);
    $ddnnum   = substr($codigo, 22, 2);
    $dv1      = modulo_10($banco . $moeda . $ccc . $ddnnum);
    // campo 2
    $resnnum  = substr($codigo, 24, 6);
    $dac1     = substr($codigo, 30, 1); //modulo_10($agencia.$conta.$carteira.$nnum);
    $dddag    = substr($codigo, 31, 3);
    $dv2      = modulo_10($resnnum . $dac1 . $dddag);
    // campo 3
    $resag    = substr($codigo, 34, 1);
    $contadac = substr($codigo, 35, 6); //substr($codigo,35,5).modulo_10(substr($codigo,35,5));
    $zeros    = substr($codigo, 41, 3);
    $dv3      = modulo_10($resag . $contadac . $zeros);
    // campo 4
    $dv4      = substr($codigo, 4, 1);
    // campo 5
    $fator    = substr($codigo, 5, 4);
    $valor    = substr($codigo, 9, 10);

    $campo1 = substr($banco . $moeda . $ccc . $ddnnum . $dv1, 0, 5) . '.' . substr($banco . $moeda . $ccc . $ddnnum . $dv1, 5, 5);
    $campo2 = substr($resnnum . $dac1 . $dddag . $dv2, 0, 5) . '.' . substr($resnnum . $dac1 . $dddag . $dv2, 5, 6);
    $campo3 = substr($resag . $contadac . $zeros . $dv3, 0, 5) . '.' . substr($resag . $contadac . $zeros . $dv3, 5, 6);
    $campo4 = $dv4;
    $campo5 = $fator . $valor;

    return "$campo1 $campo2 $campo3 $campo4 $campo5";
}

function geraCodigoBanco($numero)
{
    $parte1 = substr($numero, 0, 3);
    $parte2 = modulo_11($parte1);
    return $parte1 . "-" . $parte2;
}


function geraToken($configboleto)
{

    //função para saber o arquivo do certificado esta no diretorio correto
    if (!file_exists("certificados/itau/cert_laudo2.crt") && !file_exists("certificados/itau/cert_laudo.key")) {
        echo "Não localizado arquivos para o processamento do certificado";
        exit;
    }

    $cliente_id = $configboleto['cliente_id'];
    $cliente_secret = $configboleto['cliente_secret'];

    $sslcert = 'certificados/itau/' . $configboleto['cert'];
    $sslkey = 'certificados/itau/' . $configboleto['key'];

    $curl = curl_init();

    // Configurações do cURL
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://sts.itau.com.br/api/oauth/token',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => 'grant_type=client_credentials&client_id=' . $cliente_id . '&client_secret=' . $cliente_secret,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/x-www-form-urlencoded',
            'Cookie: APIMANAGERSTATIC=726672ab-8ca9-42f2-910b-c6c75e29e1ed'
        ),

        // Configurações do certificado e chave privada
        CURLOPT_SSLCERT => $sslcert, // Caminho para o arquivo do certificado
        CURLOPT_SSLKEY => $sslkey, // Caminho para o arquivo da chave privada
        CURLOPT_SSL_VERIFYPEER => true, // Ativar verificação do certificado do servidor
        CURLOPT_SSL_VERIFYHOST => 2, // Verificar o host do certificado
    ));

    // Executar a requisição
    $response = curl_exec($curl);

    // Verificar erros
    if (curl_errno($curl)) {
        throw new Exception("Erro cURL: " . curl_error($curl));
    }

    // Verificar o status code
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    if ($http_code != 200) {
        throw new Exception("Erro na requisição. Status code: " . $http_code);
    }

    // Fechar a sessão cURL
    curl_close($curl);

    // Exibir a resposta em json
    return $response;
}

/**
 * Função para gerar o QRCode do Itaú
 * @param array $dadosboleto
 * @param array $cliente row
 * @param array $empresa roe
 */
function geraBoletoQrcode($dadosboleto, $cliente, $roe, $configboleto)
{

    $token = geraToken($configboleto);

    $correlationID = $configboleto['correlation_id'];
    $sslcert = 'certificados/itau/' . $configboleto['cert'];
    $sslkey = 'certificados/itau/' . $configboleto['key'];

    $agencia = formata_numero($dadosboleto["agencia"], 4, 0);
    $conta = formata_numero($dadosboleto["conta"], 5, 0);
    $conta_dv = formata_numero($dadosboleto["conta_dv"], 1, 0);

    $id_beneficiario = $agencia . str_pad($conta, 7, "0", STR_PAD_LEFT) . $conta_dv;
    $dataemissao = date('Y-m-d');

    //vamos validar se é pessoa juridica ou fisica
    if (strlen($cliente['cpfcnpj']) == 11) {
        $tipo_pessoa = 'F';
        $stringpessoa = 'numero_cadastro_pessoa_fisica';
        $numero_pessoa = $cliente['cpfcnpj'];
    } else {
        $tipo_pessoa = 'J';
        $stringpessoa = 'numero_cadastro_nacional_pessoa_juridica';
        $numero_pessoa = $cliente['cpfcnpj'];
    }

    $razaosocial = $cliente['razaosocial'];
    $logradouro = $cliente['logradouro'] . " " . $cliente['endereco'] . ", " . $cliente['numero'] . ", " . $cliente['complemento'];
    $bairro = $cliente['bairro'];
    $cidade = $cliente['cidade'];
    $uf = $cliente['uf'];
    $cep = $cliente['cep'];

    // Cria um objeto DateTime a partir da string de data
    $dateTime = DateTime::createFromFormat('d/m/Y', $dadosboleto["data_vencimento"]);
    //Formata a data para o formato Y-m-d
    $data_vencimento =  $dateTime->format('Y-m-d');

    //valor to titulo com 15 caracters e sem virgula sem ponto
    $valor_titulo = str_pad(str_replace([',', '.'], ['', ''], $dadosboleto["valor_boleto"]), 15, '0', STR_PAD_LEFT);
    $juros = str_pad(str_replace([',', '.'], ['', ''], $roe['juros']), 17, '0', STR_PAD_LEFT);
    $multa = str_pad(str_replace([',', '.'], ['', ''], $roe['multa']), 12, '0', STR_PAD_LEFT);

    //etapa "simulacao" para os testes e "efetivacao" para produção
    $postfields = '{
            "etapa_processo_boleto": "efetivacao", 
            "beneficiario": {
                "id_beneficiario": "' . $id_beneficiario . '"
            },
            "dado_boleto": {
                "tipo_boleto": "a vista",
                "descricao_instrumento_cobranca": "boleto_pix",
                "codigo_carteira": "109",
                "codigo_especie": "01",
                "data_emissao": "' . $dataemissao . '",
                "pagador": {
                    "pessoa": {
                        "nome_pessoa": "' . $razaosocial . '",
                        "nome_fantasia": "' . $razaosocial . '",
                        "tipo_pessoa": {
                            "codigo_tipo_pessoa": "' . $tipo_pessoa . '",
                            "' . $stringpessoa . '": "' . $numero_pessoa . '"
                        }
                    },
                    "endereco": {
                        "nome_logradouro": "' . $logradouro . '",
                        "nome_bairro": "' . $bairro . '",
                        "nome_cidade": "' . $cidade . '",
                        "sigla_UF": "' . $uf . '",
                        "numero_CEP": "' . $cep . '"
                    }
                },
                "dados_individuais_boleto": [{
                    "numero_nosso_numero": "' . $_REQUEST['idcontapagar'] . '",
                    "data_vencimento": "' . $data_vencimento . '",
                    "valor_titulo": "' . $valor_titulo . '"
                }]
            }
        }';

    //vamos mandar os dados para o banco
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://secure.api.itau/pix_recebimentos_conciliacoes/v2/boletos_pix',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $postfields,
        CURLOPT_HTTPHEADER => array(
            'x-itau-correlationID: ' . $correlationID,
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . json_decode($token)->access_token
        ),
        CURLOPT_SSLCERT => $sslcert, // Caminho para o arquivo do certificado
        CURLOPT_SSLKEY => $sslkey, // Caminho para o arquivo da chave privada
        CURLOPT_SSL_VERIFYPEER => true, // Ativar verificação do certificado do servidor
        CURLOPT_SSL_VERIFYHOST => 2, // Verificar o host do certificado
    ));

    $response = curl_exec($curl);

    // Verificar erros
    if (curl_errno($curl)) {
        throw new Exception("Erro cURL: " . curl_error($curl));
    }

    // Verificar o status code
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    if ($http_code != 200) {
        die("Erro na requisição. Status code: " . $http_code . " - " . json_decode($response)->mensagem . " - " . json_decode($response)->campos['0']->mensagem);
    }

    // Fechar a sessão cURL
    curl_close($curl);

    // Exibir a resposta em json
    return $response;
}
