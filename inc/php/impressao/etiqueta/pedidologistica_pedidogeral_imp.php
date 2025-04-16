<?
require_once(__DIR__."/../../../../form/controllers/etiqueta_controller.php");

if(empty($_OBJ["idnf"])){
    die("IDNF não enviado");
}else if(empty($_OBJ["modulo"])){
    die("Modulo não enviado");
}

$modulo = $_OBJ["modulo"];

$idsnf = explode(',',$_OBJ["idnf"]);

foreach ($idsnf as $idnf) {
    if(!empty($idnf)){
        
        $rest = EtiquetaController::buscarInfosEtiquetaPedidoGeral($idnf);
        $qtdinfo=count($rest);

        foreach ($rest as $k =>$rowinfo){
            $qvol = $rowinfo["qvol"];
            $transportadora =  $rowinfo["transportadora"];
            $nnfe = $rowinfo["nnfe"];
            $row1 = EtiquetaController::buscarEnderecosEtiquetaPedidoGeral($rowinfo['idendereco']);
            $q=1;
            while( $q <= $qvol){

                $etiqueta = "^FO20,20 ^FO20,25 ^BQN,2,3 
                ^FDQA,https://sislaudo.laudolab.com.br/?_modulo=[_MODULO_]&_acao=u&idnf=[_LIDNF_]^FS^FX^CF0,26^FB610,4,5^FO160,35^FDTRANSP: [_TRANPORTADORA_]^FS
                ^FB510,4,5^FO160,100^FDN. PEDIDO: [_IDNF_]^FS^FB510,4,5^FO160,140^FDN. NF: [_NNF_]^FS^FX^CF0,22^FO20,180^GB750,430,3^FS^FO30,190^FDDESTINATARIO:^FS^CFR,15
                ^FB710,4,5^FO30,210^FD[_DEST_]^FS^FB710,4,5^CF0,22^FO30,280^FDR. SOCIAL:^FS^CFR,15^FB710,4,5^FO30,300^FD[_RSOCIAL_]^FS^FB710,4,5^CF0,22^FO30,370^FDEND.:^FS^CFR,15
                ^FB710,4,5^FO30,390^FD[_END_]^FS^FB710,4,5^CF0,22^FO30,460^FDBAIRRO:^FS^CFR,10^FO30,480^FD[_BAIRRO_]^FS^CF0,22^FO30,550^FDCEP:^FS^CFR,10^FO30,570^FD[_CEP_]^FS^CF0,22
                ^FO400,550^FDCIDADE:^FS^CFR,10^FO400,570^FD[_CIDADE_]^FS^CF0,24^FO30,630^FDREMETENTE:^FS^FO20,650^FD[_LOGO_]^FS^CF0,23^FO160,650^FD[_EMPRESA_]^FS^FO160,680
                ^FDCNPJ: [_CNPJEMP_]^FS^FB500,4,5^FO160,710^FDEND.: [_ENDEMP_]^FS^FO160,735^FDBAIRRO: [_BAIRROEMP_]^FS^FO160,760^FDCEP.: [_CEPEMP_] - [_CIDADEEMP_] - [_UFEMP_]^FS
                ^CF0,24^FO630,760^FDVOL: [_CONT_] de [_TOTAL_]^FS";

                $_CONTEUDOIMPRESSAO.="^XA";

                if(strpos($etiqueta, "[_MODULO_]") !== false){
                    $etiqueta = str_replace("[_MODULO_]",retira_acentos($modulo),$etiqueta);
                }
                if(strpos($etiqueta, "[_LIDNF_]") !== false){
                    $etiqueta = str_replace("[_LIDNF_]",retira_acentos($idnf),$etiqueta);
                }
                if(strpos($etiqueta, "[_TRANPORTADORA_]") !== false){
                    (!empty($transportadora)) ? $etiqueta = str_replace("[_TRANPORTADORA_]",retira_acentos($transportadora),$etiqueta) : $etiqueta = str_replace("[_TRANPORTADORA_]","",$etiqueta);
                }
                if(strpos($etiqueta, "[_IDNF_]") !== false){
                    (!empty($idnf)) ? $etiqueta = str_replace("[_IDNF_]",retira_acentos($idnf),$etiqueta) : $etiqueta = str_replace("[_IDNF_]","",$etiqueta);
                }
                if(strpos($etiqueta, "[_NNF_]") !== false){
                    (!empty($nnfe)) ? $etiqueta = str_replace("[_NNF_]",retira_acentos($nnfe),$etiqueta) : $etiqueta = str_replace("[_NNF_]","",$etiqueta);
                }
                if(strpos($etiqueta, "[_DEST_]") !== false){
                    $dest = retira_acentos(traduzid("pessoa", "idpessoa", "nome", $rowinfo['idpessoa']));
                    (!empty($dest)) ? $etiqueta = str_replace("[_DEST_]",$dest,$etiqueta) : $etiqueta = str_replace("[_DEST_]","",$etiqueta);
                }
                if(strpos($etiqueta, "[_RSOCIAL_]") !== false){
                    $rsocial = retira_acentos(traduzid("pessoa", "idpessoa", "razaosocial", $rowinfo['idpessoa']) );
                    (!empty($rsocial)) ? $etiqueta = str_replace("[_RSOCIAL_]",$rsocial,$etiqueta) : $etiqueta = str_replace("[_RSOCIAL_]","",$etiqueta);
                }
                if(strpos($etiqueta, "[_END_]") !== false){
                    if(!empty($row1['logradouro'])){
                        $endtotal = $row1['logradouro'].". ";
                    }
                    $endtotal.= $row1['endereco'].", ".$row1['numero'] ;
                    if(!empty($row1['complemento'])){
                        $endtotal.=	" - ".$row1['complemento'];
                    }
                    (!empty($rsocial)) ? $etiqueta = str_replace("[_END_]",retira_acentos($endtotal),$etiqueta) : $etiqueta = str_replace("[_END_]","",$etiqueta);
                }
                if(strpos($etiqueta, "[_RSOCIAL_]") !== false){
                    if (!empty($row1['bairro'])) {
                        $bairro = ($row1['bairro']);
                    }
                    (!empty($rsocial)) ? $etiqueta = str_replace("[_RSOCIAL_]",retira_acentos($bairro),$etiqueta) : $etiqueta = str_replace("[_RSOCIAL_]","",$etiqueta);
                }
                if(strpos($etiqueta, "[_BAIRRO_]") !== false){
                    if (!empty($row1['bairro'])) {
                        $bairro = ($row1['bairro']);
                    }
                    (!empty($bairro)) ? $etiqueta = str_replace("[_BAIRRO_]",retira_acentos($bairro),$etiqueta) : $etiqueta = str_replace("[_BAIRRO_]","",$etiqueta);
                }
                if(strpos($etiqueta, "[_CEP_]") !== false){
                    if (!empty($row1['cep'])) {
                        $CEP = formatarCEP($row1['cep'],true);
                    }
                    (!empty($CEP)) ? $etiqueta = str_replace("[_CEP_]",retira_acentos($CEP),$etiqueta) : $etiqueta = str_replace("[_CEP_]","",$etiqueta);
                }
                if(strpos($etiqueta, "[_CIDADE_]") !== false){
                    if(!empty($row1['cidade'])){
                        $cidadeuf = $row1['cidade'];
                    }
                    if(!empty($row1['uf'])){
                        $cidadeuf.= "-".$row1['uf'];
                    }
                    (!empty($cidadeuf)) ? $etiqueta = str_replace("[_CIDADE_]",retira_acentos($cidadeuf),$etiqueta) : $etiqueta = str_replace("[_CIDADE_]","",$etiqueta);
                }
                if ((strpos($etiqueta, "[_LOGO_]") !== false)) {
                    (!empty($rowinfo['zplimg'])) ? $etiqueta = str_replace("[_LOGO_]",$rowinfo['zplimg'],$etiqueta) : $etiqueta = str_replace("[_LOGO_]","",$etiqueta);
                }
                if ((strpos($etiqueta, "[_EMPRESA_]") !== false)) {
                    if ($rowinfo['idempresa'] == 1 || $rowinfo['idempresa' == 2]) {
                            $etiqueta = str_replace("[_EMPRESA_]",'INATA PRODUTOS BIOLOGICOS',$etiqueta);
                        //$strprint.='^FO160,650^FD^FS';
                    }else{
                        (!empty($rowinfo['nomefantasia'])) ? $etiqueta = str_replace("[_EMPRESA_]",retira_acentos($rowinfo['nomefantasia']),$etiqueta) : $etiqueta = str_replace("[_EMPRESA_]","",$etiqueta);
                    }
                }
                if ((strpos($etiqueta, "[_CNPJEMP_]") !== false)) {
                    if ($rowinfo['idempresa'] == 1 || $rowinfo['idempresa' == 2]) {
                        $etiqueta = str_replace("[_CNPJEMP_]",' 39.978.746/0001-00',$etiqueta);
                    }else{
                        (!empty($rowinfo['cnpj'])) ? $etiqueta = str_replace("[_CNPJEMP_]",formatarCPF_CNPJ($rowinfo['cnpj']),$etiqueta) : $etiqueta = str_replace("[_CNPJEMP_]","",$etiqueta);
                    }
                }
                if ((strpos($etiqueta, "[_ENDEMP_]") !== false)) {
                    if(!empty($rowinfo['xlgr'])){
                        $endemp = retira_acentos($rowinfo['xlgr']);
                    }
                    if(!empty($row1['uf'])){
                        $endemp.= ' - '.retira_acentos($rowinfo['nro']);
                    }
                    (!empty($endemp)) ? $etiqueta = str_replace("[_ENDEMP_]",$endemp,$etiqueta) : $etiqueta = str_replace("[_ENDEMP_]","",$etiqueta);
                }
                if ((strpos($etiqueta, "[_BAIRROEMP_]") !== false)) {
                    if(!empty($rowinfo['xbairro'])){
                        $bairroemp = retira_acentos($rowinfo['xbairro']);
                    }
                    (!empty($bairroemp)) ? $etiqueta = str_replace("[_BAIRROEMP_]",$bairroemp,$etiqueta) : $etiqueta = str_replace("[_BAIRROEMP_]","",$etiqueta);
                }
                if ((strpos($etiqueta, "[_CEPEMP_]") !== false)) {
                    if(!empty($rowinfo['cep'])){
                        $cepemp = retira_acentos($rowinfo['cep']);
                    }
                    (!empty($cepemp)) ? $etiqueta = str_replace("[_CEPEMP_]",$cepemp,$etiqueta) : $etiqueta = str_replace("[_CEPEMP_]","",$etiqueta);
                }
                if ((strpos($etiqueta, "[_CIDADEEMP_]") !== false)) {
                    if(!empty($rowinfo['xmun'])){
                        $cidadeemp = retira_acentos($rowinfo['xmun']);
                    }
                    (!empty($cidadeemp)) ? $etiqueta = str_replace("[_CIDADEEMP_]",$cidadeemp,$etiqueta) : $etiqueta = str_replace("[_CIDADEEMP_]","",$etiqueta);
                }
                if ((strpos($etiqueta, "[_UFEMP_]") !== false)) {
                    if(!empty($rowinfo['uf'])){
                        $ufemp = retira_acentos($rowinfo['uf']);
                    }
                    (!empty($ufemp)) ? $etiqueta = str_replace("[_UFEMP_]",$ufemp,$etiqueta) : $etiqueta = str_replace("[_UFEMP_]","",$etiqueta);
                }
                if ((strpos($etiqueta, "[_CONT_]") !== false)) {
                    $etiqueta = str_replace("[_CONT_]",$q,$etiqueta);
                }
                if ((strpos($etiqueta, "[_TOTAL_]") !== false)) {
                    $etiqueta = str_replace("[_TOTAL_]",$qvol,$etiqueta);
                }

                $_CONTEUDOIMPRESSAO.= $etiqueta;
                $_CONTEUDOIMPRESSAO.="^XZ";
                $q = $q + 1 ;
            }
            
        }
    }
}
?>