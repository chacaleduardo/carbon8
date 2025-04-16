<?php
require_once("../../../../inc/php/validaacesso.php");

require_once("../../../../api/nf/index.php");
require_once("../../../../form/controllers/pedido_controller.php");


ini_set("display_errors","1");

error_reporting(E_ALL);


use NFePHP\NFe\Tools;
use NFePHP\Common\Certificate;
use NFePHP\NFe\Common\Standardize;
use NFePHP\NFe\Complements;
use NFePHP\NFe\Common\FakePretty;
/*
$zip="H4sIAAAAAAAEAJ05aZeizM5/hTP3o6ebRdzmYTy3WEVZlEUbv9yDiIiy2IKg/vo3Ba3Ts7zP1meGSlKpJJWkUkG4bBfOz3lAXNMkK7592Zfl6StJ1nX9esrPpZ/s4iLwk9c4271uziRwfyGq8Fz4+bcv7CtFfRlzhhz+g9VjDhC85AcphLr99gWoXZrp0vSw3x9Q3V6XoiiaZns9GDA46vX6oz5ALDvq9rHqeBuOucCVx12aI/HIBYY8fnAACTAu80vzNF6G2dYnxDArzzkhFaW/zTmyneLSfDvu9TgSj1wRnuNwDPJagMtARqsa+LG87V5K4zFDMd0Xmn6hBg49+NodfaWoF6oLT45sGbjyhFdyZDOCqWJYlBj/gLhAv2SyAqYPKIbCxrY4rFPTU7sQA4CDuGLcx4QG4gJxOQY9eIBZlG5abgxwuxiMbOz/gMDfWznO/KTR/YAxdX4Oiw9iA2KampXhOQ23WP5nlDtBkuBtAf0BchBCnDtj9pV+hTBy5IMAPhLyrPzZSexXmv3upIaDu04v4Aw73Pl3Qs22cXHKs7gKE6IIUwLMqmLIEmIbElZY5ucMgtauwH6EzYVpDLBgzKfjH7OGIxsidzXyNBwLkuFYJiFpEgyGKpiEKBEuL1kaMkQVEZJqSZqmgvCGnbvKPhj391c17BykWHiWGouuWnQeoyWBZNOwTWIuGQjYMJHLzvmYYUcMpBNA3FU4JWPNnOJ5DHJX3o/PMIPmyJIEVcQTH6QmZ37MGODHz+9WvegKLGhm4EBgBJ8LQZqPu0OWogbUEFwDGBfMfUgmmuoBoYW5azPwZ7+IExDSEnd5Fo67bJfpMr0hBY5tCBz5abeqNAaTRlSPGsJJHcDWgMIJljPugjIYgLth3DaZ30SGHQ5Yekjj80zjhPgcLv2FV01HEiaEjhauaiCbkAgJoDnSISamTWhO45c2Wo0l7aFqXGyZIsFbL91+77PPbdL47PKZTrz06R7BayYEVuv+7H7LtZD2z1z/J34f0EPqL/1uIVvVfvU7w/QG2EOf/d7uFp9Q6VlXngiOB66ATH9IUbg04miEqR8nYz/1oQ5u/PMmL/z/pps4L8Ng/xrkKRRoHCXMBKezEeNfyjdd+4gX3aVYih2OcLwGg0e8yAfPNiyJTC3D9NsX+ktTLrZ4q3igu8Nus9eWJiFjbEs6oTgqBKRBwQF4TrLnSHElRyIcydJNQjDh/BE0q+vE3JIcE7umkWEI+rg7ogddyDmOxBgnyOZ83KMpyL0G5C5Cno51hyMbgHvHT7o3fIXbBNY0KFe5GR77r7hotH9QxFoiVzW6RiNYAnlUfbfeOcebn3bQkLhLMzQ6W8J7M3zS2pJBQwP8oredhkA6+SOkGAL3tBW5GbnGzR+UJ9yUZRji9JQXJeRrBSsbecxo9NqDlH0SOFXQ7fZJUWMuP8cRFtWMnGA74+Y4wogvRl7AZ7gFuAoeT4dgwqkRRQ9f2y20KFe11MHodQDlosUgCT/0kS0+V9uHeSnPrVp29KH2VzWYmXooaVZWDxIODSaQT1lkgwumrBrP8W9p+VjyVPQQUX2aAN4HmfwsmnxSnxGAUwSxK/MS37d4000sf1H7W281AxzmPHto/U7gKlmYP8gYxDJx2FoK3+7uO6FBge87pUU+iHCx/kDH+O+Tv5LPYRk+mRsEFIXRUxOAXAVWBg9KA8Nu1Oc2VIzOv+PzD4IYVnnyidriv8b5/wlGhcOQP6gtwlXQeT33gLuwPzkU5DNC5EfIyrOfFW172O6Vbg7CY9/YuvclthkONh657ANrRpDdPB9SgnwDabLzQUEmw/PZU2IEzMen72lrg/3Wk1r8/p0NI3AvYAHbC+jIRHhCfQapGOK20PUGH10YA10YpGRDAckw/RTT8JKNBLI18+RHTUmf47F5YqEtWuHn98zAJPLBSjYLocNH2zhoAHyn6uL/el+JviTJAwrJvNwV0IBhh8MhIwlUl+/3WbE3ZIlX4vUP3Xn7A5ECgTRVVgkDmYQiWYgQLElUHZPAMfpDEIWvBG9CV4YIBDcDWpt/CKb4KmjqV4LqDkbsgAAAukDihYDHUjJE6CDgipYs2zT+IOhX2cK3zNy0mjsGESJu7Wwo58hBlmriyttY3gDPvVhhcXLC4ONCpNgRNRiMmn5zOHw2MLit9eH4zxWdsFXbkXTcveBOETeKj9nHdbxN4ywuIEfKuMr/e4rSn6/i791Xn2put48u4LM5ZPtKNebsOIJXmsv5dy9kdfc1P0ckA+aS1IgEhm0RR//50q4KodXfgVmCn0H/De9s8R1MyjM9LPf5lkBJBNdDuU9/J9KxsFSahG71BcS+BDSbvWAK9B69LwT5ya6/I+5nC+E18aXY+3QjyQp34RlSOCRcS/325T///J3Rwedxl5/T4hP8zywKM3hByU/h9qV4bKwx7m+K+2t/kZ+NFOMIurF/47mn11oRSz+5hOOzwVeS0n+TReV4PGXrzuG4OgoKklj1G0d+5uTIp7cB/pwlz3i2jIeld5aUatI9ncjj+fZWJHScb9S1M7gwOrNyy9jWhoVgU7Z1U1LeWthzdn1KlJOjVQuD8W+TmtXuy6Rm5fVOdk/Bytypwm6dJP3FIKx6t3eVfJtd+3SNCtIVcoZ92wgLNjYzk1f9y9V09HipW/PJVdzKq7dOVl+W2S2QyrVmME457GWaEPemxn3bL7qzw001JnvW2JnS0QojnjrbWzRJHc1T+1dr2UkoPZpBKN6Med+NaZ2JNXT3jkI0nOqK4RfX42A0MZz3az5k+NKz8soTTBrdWGtAzReJtEo3dt/TzHRRZIx5Jzue5U3r3ShLeh0lCGNxo8353YKUkNY3+zuERGdzC4r7SBBksZxNN6f3RVqJ0bdvrdM/OZqbhbc2Am89aiT6pd9CQngu4x0cW7iYdBX+REHgozhCtcqjSFWdWFuKHdmptLoWF950lq/VfRUYaCFp/ALVW0fSdHRUEO1K/F4XFol6le7I4iNjyaPcEY6J6Aq0EKTy3V/VV+OAqHaucLSlkXjdabJV5Ju/kqK1IkVuujysVwnl2byyXlkJrCtUxapUaXrz3ozTRkClKk+VxUFa63zQ6EVXfbFw9ciherrt9uRmPuYZKM+hXFM3/Q4cono1RZU1HN7HNKjrP9Bg7/zsN3sR7mja2hs56EhPdEuvpYUnLheLiVSflovjEgq1bFpSwn+am8HcdEGjcpFN9wGTFLrj1QZq5jTpRjvrlXFbv1n7IE32sL892Jx4zPK0BR9Y6fK4fpvuNwJ/3Nz4h+8izeZt66he5QNyHz4Ul81eE801TFfKo4UjXad3lDzmJ0fZBtmycxytFjYvWsupa7vr6YKSxYU7jMDuib20eEeUHJ2Xmr0LV33mTqZJwCwvHpPsN6L0pvPuY87UD2rPuAc9XZRqHfxogH/nIuq3OnVHpwzZSSzbufGyIy1d9zgybdeYq5IFsVkKYIO+cHuS7UqRA/fpwslZ4+B1dVGnIVYgU7oakIfwTvtzzvGQc2IUSXOE5xe5ADCPzIuxDOhN7z4/SHEq7OKdPJgJUocq1/tBUCW7Hrl2hGHETAY0/yZM7hVrn9fCwGAmtbCfqnNB6ZJJWiwNedrJZuldnc7P86GnQMinkuJdL6fVhOqHJYu6itKpd2Sn2NRKeUpG0Flnx+VKjMv3WrOhfFyHOt2T+qtVfs/E/uA46M2X/Fs9nPeW3UxghnwmdIcZi66lGyzPhyDI5GAyWl+Xm62oC9lSJqHehJK1mqyqM1OvA/5tSM0cNCyj0NldD5NzLJBJYm6NRTBU0ne2UIT3a1UdxVXpK73F9tIXEK3sR84Z+ts7q85kR4OckPkwfMsqNo5SX76G/ZNW60wkaX4iKrej/75fz07zUdG5iO/y/F3SZoWAagkh3xRQVddRpLG6qhz4KDrzkSTzi4BHkFDJAZL0zgef6DXK1wt/YlGBmFcakxwCgY4/cviyzfizb/cYb3U9rRUjCTLrtE6Tg7eyqiCVIN9GpZZObxojH7TV6LJRkguchdSLR3vvRleb9HrapMvbOp0yht2rDUat9dpTZ7UHueFOdKQoU2ZPbSeor91Gldc16u3BvWyhjvgr6wCyKH+1PnmMfNx0p3stNaqN3YuDQ3vOJ5YuoQNCOj/E+b5V64Wn8z6Szfv0PrvcZsZqX0226Fx2FpvhvNwN7id9ErS8yKp1ha03yFM8qYaSIXV1Ra099N0+oK2WytO+k9dFpfdRI7RsXfvM8a9t/cUXarWe8OCvn3yWebSW8cc1xOgSQe2xqB2vKjeIY6XLq8jt89b+OMq3E6s242Hlr4xaWz3q7L+KUeF11dJb6eWG6cEeelC/1zH2P9T5Qpf9aDnj7X3C/16nqP6rvW8U4/bbvafGbSMis4nrYsij3RCKhS7wIaoni8YfCc97tfw5PrWqCEKhoIUr87Uuga86bV5IUrTx6mgDjUO6Bt3yBdfrIB0hGC/bybRar64JHjfgq82td9gw1MXLZpGJPLnJh9qOtHpR6ge31g9SD+7CO9Q92nB0qKEeZTq49v35/xzykl/xOlroQs57kixtp45E827M8468lt0bLzr01LZccDJ/fPCKOY8k8RAxxiGo9bt7b+Q5EpXDHfrBM8kRK4m/6jT46Pi+P8bKqKbgzBcyQlATIgmtzsNg1t2f1keGLtSLut16pGVu2W5lHXbmsTuRb4w5UnJzsCvLzjI7bIxqtl/SQn7FNqyUiU8GKbupvLN86IyMyjSis0PVHRWCc+rMLNJ7M8mOlK0El9doQexJLp3znXOh3+zcTRJ0U/RdxFiXS7JHzlEQrrdONVO7Sba+JEdyPXqDPOyQnSqfTHS7nnhbmzmsRXJhMpde7HbK5bmqyEHpH6SuVuerXmCW825BikM0yGXSLs1aEm6C0O0cdT0frskBfaGrcBTKxezUT2ZzYSDwnYkVd96M6Tui9PVtoJ6udEGGy3sQL7yNbXeUVazeK38w6sxT6bTuqLbIVOZly6R6Wu3vuncNydlm8vY+Nxdm8Y5cb5lCazN0+mhqJ0kpjIzTVOn05Pvmrl/T2dG0RlJ0P/n9pWDCUaTP4kYczpC8NneMZ/fPbrYKhOVxdpNQP1kNyM5uKRTC6KTtN1o9mR5U75jpN2c0n/uuYGTWzVHN2k+V6z63KGNUr3tLdX5Zv997xobqxZvtbHQ6HJZ7ka2E0JP6p7Ae7q/3mVTMD2vduVPvo/QwJYtdsRHSq+aJljPdorTWzLJjLCnVvri8vs4XN42n3NzRIZJOnWtDrWN2zAXv3tnN2/vbpXunbl5+7hl7dZ0fFIUdJZTL0ifTosPQWBbG4b0IKoHWw87kskqYLOwzHopu6oCtUBJfR5fTOxU4rNtRTofL/W1VTIRTTocD9bhxoSH+udttKW0nTD674+99M8DNm/HpnJf/7EPlz585YWoOQpovlarYx2+dFMXQI2owHLJffv0QB8vRKYmDsb0UkPE/9pWGf80nspbMBXts2T95e+XIdg233VthsCnznz6wsdQPXyE/eLgM2z3+yWKObMncNo7g5eIvXgs/mLjALvFPV1h+C3JXPcc/XYzRpYQX07u/zYmcuBQ5sfUJQ34J8TeRlqP5maLVSX6EA6CP78/j/wM4ZdFQiR4AAA==";
$zipdata = base64_decode($zip);
$zipdata = gzdecode($zipdata);
*/


require_once "../vendor/autoload.php";


$qr = "SELECT 
			 idempresa,idnfe		
	from nf 
	where idnf = ".$_GET["idnotafiscal"];
$rs = d::b()->query($qr);
$row = mysqli_fetch_assoc($rs);

$_idempresa=$row["idempresa"];


$idnotafiscal = $_GET["idnotafiscal"]; //Id da nota fiscal que vem da acao de enviar

$_sql = "SELECT *,str_to_date(nfatualizacao,'%d/%m/%Y %h:%i:%s') as nfatt FROM empresa WHERE idempresa = ".$_idempresa;
$_res=d::b()->query($_sql) or die(mysqli_error(d::b())." erro ao buscar o empresa na tabela empresa ".$_sql);
$_row=mysqli_fetch_assoc($_res);

    $config = [
    "atualizacao" => $_row["nfatt"],
    "tpAmb" => 1, // Se deixar o tpAmb como 2 você emitirá a nota em ambiente de homologação(teste) e as notas fiscais aqui não tem valor fiscal
    "razaosocial" => $_row["nfrazaosocial"],
    "siglaUF" => $_row["nfsiglaUF"],
    "cnpj" => $_row["nfcnpj"],
    "schemes" => $_row["nfschemes"], 
    "versao" => $_row["nfversao"],
    "tokenIBPT" => $_row["nftokenIBPT"]
    ];

    $configJson = json_encode($config);

    $certificadoDigital = file_get_contents($_row["certificado"]);

    // $tools = new Tools($configJson, Certificate::readPfx($certificadoDigital, '010787'));


if(empty($row['idnfe']) ){
	die("Não foi encontrado o protocolo da notafiscal");
}

//$nfe = new ToolsNFePHP;
//$chNFe = "31130323259427000104550010000005021110385551";
//$nProt = "135110002251645";
$chNFe=$row['idnfe'];

$tpAmb = '1';
$modSOAP = '2';
$descompactar=true;

try {
	
		$tools = new NFePHP\NFe\Tools($configJson, NFePHP\Common\Certificate::readPfx($certificadoDigital, $_row["senha"]));
		// $certificate = Certificate::readPfx($content, 'senha');
		//$tools = new Tools($configJson, $certificate);
		$tools->model('55');

		$chave = $chNFe;
	
     
        $retorno = $tools->sefazDownload($chave);
   

      
    if (!$retorno){
        header('Content-type: text/html; charset=UTF-8');
        echo "Houve erro !! $tools->errMsg";
      
    } else {
             
        $dom = new DOMDocument('1.0', 'utf-8'); //cria objeto DOM
        $dom->formatOutput = false;
        $dom->preserveWhiteSpace = false;
        $dom->loadXML($retorno, LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);
        $retDistDFeInt = $dom->getElementsByTagName("retDistDFeInt")->item(0);
        $cStat = ! empty($dom->getElementsByTagName('cStat')->item(0)->nodeValue) ?
         $dom->getElementsByTagName('cStat')->item(0)->nodeValue : '';
        $xMotivo = ! empty($dom->getElementsByTagName('xMotivo')->item(0)->nodeValue) ?
        $dom->getElementsByTagName('xMotivo')->item(0)->nodeValue : '';
        if ($cStat == '') {
            //houve erro
            $msg = "cStat está em branco, houve erro na comunicação Soap "
                    . "verifique a mensagem de erro e o debug!!";
            throw new nfephpException($msg);
        }
        $bStat = true;
        $dhResp = ! empty($dom->getElementsByTagName('dhResp')->item(0)->nodeValue) ?
         $dom->getElementsByTagName('dhResp')->item(0)->nodeValue : '';
        $ultNSU = ! empty($dom->getElementsByTagName('ultNSU')->item(0)->nodeValue) ?
        $dom->getElementsByTagName('ultNSU')->item(0)->nodeValue : '';
        $maxNSU = ! empty($dom->getElementsByTagName('maxNSU')->item(0)->nodeValue) ?
        $dom->getElementsByTagName('maxNSU')->item(0)->nodeValue : '';
 
        if ($cStat != '138') {
            return $retorno;
        }
        //se cStat == 138 então existem docs
        $docs = $dom->getElementsByTagName('docZip');
        foreach ($docs as $doc) {
            $nsu = (int) $doc->getAttribute('NSU');
            $schema = (string) $doc->getAttribute('schema');
            //o conteudo desse dado é um zip em base64
            //para deszipar deve primeiro descomverter de base64
            //e depois aplicar a descompactação
            $zip = (string) $doc->nodeValue;

            $zipdata = base64_decode($zip);
            $xml = gzdecode($zipdata);               
            
        }

        $xml = str_replace("'", "",$xml);

        $sql="update nf 
        set envionfe='CONCLUIDA', xmlret = '".$xml."'
        where idnf = ".$idnotafiscal;
        $retx = mysql_query($sql) or die("Erro ao atualizar nf sql:".$sql);
        
    //  header('Content-type: text/xml; charset=UTF-8');
        echo("XML baixado com sucesso.");
        //print_r($xml);
        //echo '<BR><BR><BR><BR><BR>';
        //print_r($resp);
    }
	    

} catch (\Exception $e) {
    echo $e->getMessage();
}
?>