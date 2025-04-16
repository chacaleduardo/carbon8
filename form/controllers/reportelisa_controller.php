<?
require_once(__DIR__."/_controller.php");

require_once(__DIR__."../../../inc/php/jpgraph/grafelisa.php");
require_once(__DIR__."../../../inc/php/jpgraph/grafelisagmt.php");
DEFINE("TTF_DIR","../../../inc/fonts/");


class reportElisaController extends Controller{


    public static function montarReportElisa($idresultado, $idnucleo, $idpessoa, $idtipoteste, $tipoidade, $idespeciefinalidade, $mostraass, $ocultar, $textointerpretacao, $textopadrao)
    {

        $rowJsonResultados = TraResultadosController::$arrJsonResultado;

        //Invoca variáveis do escopo superior
        global $irestotal;

        //Quantidade de linhas do Elisa por pagina
        $qtlinhaselisa = 35;
        $quebratab = 0;
        $paginaquebra = 1;
        $iresultv = count($rowJsonResultados['resultadoelisa']);


        if ($iresultv > 0) {
            $arrelisav = array();
            $in = 0;
            foreach ($rowJsonResultados['resultadoelisa'] as $i => $rowv) {

                //se for resultado da tabela de dados, armazenar em um array com um nivel a mais
                $in++;
                if ($rowv["local"] == "C") {

                    //Se o numero de linhas alcancar o limite, aumenta o grupo e reseta o numero de linhas atual
                    if ($quebratab == $qtlinhaselisa) {

                        $paginaquebra++;
                        $quebratab = 0;
                    }
                    //Somente incrementa o numero de linhas atual
                    if ($quebratab < $qtlinhaselisa) {
                        $quebratab++;
                    }

                    $arrelisav[$rowv["local"]][$paginaquebra][$in] = $rowv;
                } else {
                    $arrelisav[$rowv["local"]][$in] = $rowv;
                }
            }
        } else {
            echo ("\nTeste de Elisa sem dados: [" . $idresultado . "]\n");
        }


        $irestotal = count($arrelisav["C"]);


        foreach ($arrelisav["C"] as $key => $tabelisa) {
            self::reportElisaCorpo($idresultado, $idnucleo, $idpessoa, $idtipoteste, $tipoidade, $idespeciefinalidade, $mostraass, $tabelisa, $arrelisav["R"], $textopadrao, $ocultar, $textointerpretacao);
        }
    }


    
    private static function reportElisaCorpo($idresultado, $idnucleo, $idpessoa, $idtipoteste, $tipoidade, $idespeciefinalidade, $mostraass, $arrtabelisa, $arrtabresumo, $intextopadrao = false, $ocultar, $textointerpretacao)
    {
        $rowJsonResultados = TraResultadosController::$arrJsonResultado;

        global $templatecsv;
        global $ins_nomepartida, $ins_fabricante, $ins_partidaext, $ins_fabricacao, $ins_vencimento; 
        global $csvgmt;
        // verifica se trata-se de uma amostra de DIAS. Caso positivo, nao mostrar segundo grafico. A pedido de Andre 271009.
        $booldia = strpos(strtoupper($tipoidade) , "DIA");
        if ($booldia === false) { //Atenção para utilização do '===': $boolsem retorna como encontrado na posição 0 a string procurada. 0=False, portanto, com === se força a tratar somente booleanos
            $booldia = false;
        }
        else {
            $booldia = true;
        }
        if (empty($idresultado) or empty($idpessoa) or empty($idtipoteste)) {
            echo "--> Parâmetros para gr&aacute;fico Elisa est&atilde;o incompletos (Page Source). <br />A amostra n&atilde;o possui informa&ccedil;&atilde;o de [Cliente] ou [Teste]";
            echo "<!-- ";
            print_r(func_get_args());
            echo " -->";
        }
        else {
            // ######################################################Dados para a tabela
            $iresult = count($rowJsonResultados['resultadoelisa']);
            if ($iresult > 0) {
                $tabelisa = array();
                foreach($rowJsonResultados['resultadoelisa'] as $i => $row) {
                    $tabelisa[$row["local"]][$row["nome"]] = $row;
                }
                $tabelisa["C"] = $arrtabelisa;
                $tabelisa["R"] = $arrtabresumo;
                $arrgraf1 = array();
                $linha = array();
                if ( $idtipoteste == 3512) {
                    foreach($rowJsonResultados['resultadoelisa'] as $i => $row) {
                        if (is_numeric($row['nome']) and $row['SP'] != '') {
                            $number = str_replace(',', '.', $row['SP']);
                            $arredondado = floor($number * 100) / 100;
                            if ($arredondado >= 0.00 and $arredondado <= 0.09) {
                                $linha[0]++;
                            }
                            if ($arredondado > 0.09 and $arredondado <= 0.19) {
                                $linha[1]++;
                            }
                            if ($arredondado > 0.19 and $arredondado <= 0.29) {
                                $linha[2]++;
                            }
                            if ($arredondado > 0.29 and $arredondado <= 0.39) {
                                $linha[3]++;
                            }
                            if ($arredondado > 0.39 and $arredondado <= 0.49) {
                                $linha[4]++;
                            }
                            if ($arredondado > 0.49 and $arredondado <= 0.59) {
                                $linha[5]++;
                            }
                            if ($arredondado > 0.59 and $arredondado <= 0.69) {
                                $linha[6]++;
                            }
                            if ($arredondado > 0.69 and $arredondado <= 0.79) {
                                $linha[7]++;
                            }
                            if ($arredondado > 0.79 and $arredondado <= 0.89) {
                                $linha[8]++;
                            }
                            if ($arredondado > 0.89 and $arredondado <= 0.99) {
                                $linha[9]++;
                            }
                            if ($arredondado > 0.99 and $arredondado <= 1.09) {
                                $linha[10]++;
                            }
                            if ($arredondado > 1.09 and $arredondado <= 1.19) {
                                $linha[11]++;
                            }
                            if ($arredondado > 1.19 and $arredondado <= 1.29) {
                                $linha[12]++;
                            }
                            if ($arredondado > 1.29 and $arredondado <= 1.39) {
                                $linha[13]++;
                            }
                            if ($arredondado > 1.39 and $arredondado <= 1.49) {
                                $linha[14]++;
                            }
                            if ($arredondado > 1.49 and $arredondado <= 1.59) {
                                $linha[15]++;
                            }
                            if ($arredondado > 1.59 and $arredondado <= 1000) {
                                $linha[16]++;
                            }
                        }
                    }
                    for ($c = 0; $c <= 16; $c++) {
                        $arrgraf1[$c] = $linha[$c];
                    }
                }
                elseif ($idtipoteste == 1556 or $idtipoteste == 670 or $idtipoteste == 590 or $idtipoteste == 6248 or $idtipoteste == 11741  or $idtipoteste == 4160 ) {
                    foreach($rowJsonResultados['resultadoelisa'] as $i => $row) {
                        if ($row['result'] == 'Pos!') {
                            $linha[0]++;
                        }
                        elseif ($row['result'] == 'Neg') {
                            $linha[1]++;
                        }
                    }
                    for ($c = 0; $c <= 1; $c++) {
                        $arrgraf1[$c] = $linha[$c];
                    }
                }
                elseif ($idtipoteste == 3484 ) {
                    foreach($rowJsonResultados['resultadoelisa'] as $i => $row) {
                        if ($row['result'] == 'Pos!') {
                            $linha[0]++;
                        }
                        elseif ($row['result'] == 'Neg') {
                            $linha[1]++;
                        }
                        elseif ($row['result'] == 'Sus*') {
                            $linha[2]++;
                        }
                    }
                    for ($c = 0; $c <= 2; $c++) {
                        $arrgraf1[$c] = $linha[$c];
                    }
                }
                else {
                    foreach($rowJsonResultados['resultadoelisa_graf1'] as $i => $row) {
                        if ($row["grupo"] == '0') {
                            $arrgraf1[(int)$row["grupo"]] = $row["quant"];
                        }
                        else {
                            $arrgraf1[$row["grupo"]] = $row["quant"];
                        }
                    }
                }
                // #######################################################Dados para o segundo gráfico
                if (!empty($idnucleo) and !empty($idpessoa) and !empty($idtipoteste)) {
                    $arrgraf2 = array();
                    foreach($rowJsonResultados['resultadoelisa_graf2'] as $i => $row) {
                        $arrgraf2[$row["idade"]] = $row["gmt"] + 1;
                    }
                }
                $templateelisa = '	<tr>
                                        <td style="vertical-align: top; width:64%">
                                            <fieldset class="fset" style="border:none;">
                                                <div class="resdesc" style="text-align:center;">
                                                    <div style="width:100%; background-color:#f7f7f7; height:14px; vertical-align:middle" class="trelisa ' . $corback . '">
                                                        <div class="relisa">&nbsp;</div>
                                                        <div class="relisa">Well</div>
                                                        <div class="relisa">O.D.</div>
                                                        <div class="relisa">I.E.</div>
                                                        <div class="relisa">S/P</div>
                                                        <div class="relisa">S/N</div>
                                                        <div class="relisa">Titer</div>
                                                        <div class="relisa">Group</div>
                                                        <div class="relisa">Result</div>
                                                    </div>
    ';
                foreach ($tabelisa["C"] as $key =>  $vlr) {
                    if (strtoupper($vlr["result"]) == "POS!") {
                        $corback = "trpos";
                    }
                    else {
                        $corback = "trnormal";
                    }
                    // maf110314: A pedido de Andre, SPs com zero devem ser mostrados
                    $vc1 = (!empty($vlr['SP']) or $vlr['SP'] == 0) ? 1 : 0;
                    $vc2 = (!empty($vlr['SN'])) ? 1 : 0;
                    $vc3 = (!empty($vlr['titer'])) ? 1 : 0;
                    $vc4 = (!empty($vlr['grupo'])) ? 1 : 0;
                    $vc5 = (!empty($vlr['result'])) ? 1 : 0;
                    $vcr = $vc1 + $vc2 + $vc3 + $vc4 + $vc5; //quantidade de colunas preenchidas. Isto evita mostrar lixo de RTF
                    if (($vcr >= 2 and $vlr['nome'] != "Well" and $vlr['well'] != "O.D.") or (strtoupper($vlr['nome']) == "NEG" or strtoupper($vlr['nome']) == "POS")) { //Nao mostrar lixo
                        $templateelisa.= '
                                                    <div style="width:100%;" class="trelisa ' . $corback . '"> 
                                                        <div class="relisa">' . $vlr['nome'] . '</div>  
                                                        <div class="relisa">' . $vlr['well'] . '</div>
                                                        <div class="relisa">' . $vlr['OD'] . '</div>
                                                        <div class="relisa">' . $vlr['IE'] . '</div>
                                                        <div class="relisa">';
                        if ($vlr['local'] == 'C' and (empty($vlr['SP']) and $vlr['SP'] != 0)) {
                            $templateelisa.= '-';
                            $_sp = '-';
                        }
                        else {
                            $templateelisa.= $vlr['SP'];
                            $_sp = $vlr['SP'];
                        }
                        $templateelisa.= '				</div><div class="relisa">';
                        if ($vlr['local'] == 'C' and empty($vlr['SN'])) {
                            $templateelisa.= '-';
                            $_SN = '-';
                        }
                        else {
                            $templateelisa.= $vlr['SN'];
                            $_SN = $vlr['SN'];
                        }
                        $templateelisa.= '				</div><div class="relisa">';
                        if ($vlr['local'] == 'C' and empty($vlr['titer'])) {
                            $templateelisa.= '-';
                            $_titer = '-';
                        }
                        else {
                            $templateelisa.= $vlr['titer'];
                            $_titer = $vlr['titer'];
                        }
                        $templateelisa.= '				</div><div class="relisa">';
                        if ($vlr['local'] == 'C' and !strlen($vlr['grupo'])) {
                            $templateelisa.= '-';
                            $_grupo = '-';
                        }
                        else {
                            $templateelisa.= $vlr['grupo'];
                            $_grupo = $vlr['grupo'];
                        }
                        $templateelisa.= '				</div><div class="relisa">';
                        if ($vlr['local'] == 'C' and empty($vlr['result'])) {
                            $templateelisa.= '-';
                            $_result = '-';
                        }
                        else {
                            $templateelisa.= $vlr['result'];
                            $_result = $vlr['result'];
                        }
                        $templateelisa.= '				</div>
                                                    </div>';
                        $templatecsv.= '"Nome: ' . $vlr['nome'] . ' " "Well: ' . $vlr['well'] . '" "O.D.: ' . $vlr['OD'] . ' " "S/P: ' . $_sp . ' " "S/N: ' . $_sn . '"  "TITER: ' . $_titer . '"\n "GROUP: ' . $_grupo . '" "RESULT: ' . $_result . '", ';
                    }
                }
                $templateelisa.= '
                                <br />
                                <table style="width:100%; padding:0px;margin:auto;" class="tabelisa" >
                                    <tr class="hdr">
                                        <td colspan="3" class="tdrot grrot"style="text-align:center !important" >Resumo</td>
                                    </tr>
                                    <tr class="hdr">
                                        <td></td>
                                        <td align="center" class="tdrot grrot" style="text-align:center !important;">';

                if ($idtipoteste == 81) {
                    $templateelisa.= "S/N";
                }
                else {
                    $templateelisa.= "S/P";
                }
                $templateelisa.= "</tr>";
                
                foreach ($tabelisa["R"]as $key => $vlr) {

                    if (strtoupper($vlr["result"]) == "POS!") {
                        $corback = "trpos";
                    }
                    else {
                        $corback = "trnormal";
                    }
                    if ($vlr['nome'] == 'GMN'){
                        $csvgmt = $vlr['titer'];
                    }

                    $templateelisa.= '
                                    <tr class="' . $corback . '">
                                        <td align="center" class="tdval grval">' . ($vlr['nome']) . '</td>
                                        <td align="center" class="tdval grval">' . ($vlr['SP']) . '</td>
                                        <td align="center" class="tdval grval">' . ($vlr['titer']) . '</td>
                                    </tr>';

                    $templatecsv.=	'"Nome: ' . $vlr['nome'] . ' " "S/P: ' . $vlr['SP'] . '" "TITER: ' . $vlr['titer'];				
                }

                $templateelisa.= '
                                        </table>
                                    </div>
                                </fieldset>';

                if (!empty($textointerpretacao) and $textointerpretacao != " ") {
                    $templateelisa.= '
                                            <br />
                                             <fieldset class="fset">
                                                <legend><font class="ftitulo" style="text-transform:uppercase;">&nbsp;Interpretação *&nbsp;</font></legend>
                                                <div class="resdesc">
                                                    <div id="fraseedicao" class="divfrase" style="width:100%">' . $textointerpretacao . '
                                                        <input id="idfrasedit" type="hidden" value="' . $textointerpretacao . '">
                                                    </div>';
                    if (!empty($row["idade"]) and !empty($row["tipoidade"])) {
                        $templateelisa.= '
                                                    <table class="tablegenda"style="width:100%;text-transform:none">
                                                        <tr>
                                                            <td>* Para inserção da interpretação não foram considerados registros posteriores a ' . ($row["idade"]) . ' ' . ($row["tipoidade"]) . '</td>
                                                        </tr>
                                                    </table>';
                    }
                    $templateelisa.= '
                                                </div>
                                            </fieldset>';
                }
                elseif ($mostraass == false) {
                    $templateelisa.= '
     
                                            <br /> 											 
                                            <fieldset class="fset">
                                                <legend><font class="ftitulo" style="text-transform:uppercase;">&nbsp;Interpretação *&nbsp;</font></legend>
                                                <div class="resdesc">
                                                    <div id="fraseedicao" class="divfrase" style="width:100%"><textarea  rows="5" cols="40" id="idfrasedit" tabindex="1">' . ($textointerpretacao) . '</textarea></div>
                                                    <table class="tablegenda" style="width:100%;text-transform:none">
                                                        <tr>
                                                            <td>* Para inserção da interpretação não foram considerados registros posteriores a ' . ($row["idade"]) . ' ' . ($row["tipoidade"]) . '</td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </fieldset>';
                }

                if ($ocultar != 0) {
                    $templateelisa.= '  	<br />
                                            <fieldset class="fset" style="text-align:left">
                                                <legend><font class="ftitulo" style="text-transform:uppercase;">&nbsp;Considerações *&nbsp;</font></legend>';
                                                
                                                        
                                                        $x =1;
                                                        
                                                        if (count($ins_partidaext) > 0){
                                                            $templateelisa .= '<table><tr><td>';
                                                        
                                                        
                                                        while ($x <= count($ins_partidaext)){
                                                            if($x % 2 == 0){
                                                                 $bor = 'border-right:1px dashed #eee;';
                                                            } else {
                                                                 $bor = ''; 
                                                            }
                                                            
                                                            if ($x > 1){
                                                                $bot = 'border-top:1px dashed #eee;';
                                                            }else{
                                                                $bot = '';
                                                            }
                                                            
                                                            
                                                            
                                                            $templateelisa .= '<ul style="padding-left:4px !important; '.$bot.' '.$bor.'  padding:4px;  float:left;list-style: none; width:47%; min-width:30px;vertical-align:top; margin-bottom:0px; padding-left:0px; float:left; margin-top:0px;font-size:6px !important;">';
                                                            $templateelisa .= '<li>PARTIDA DE '.$ins_nomepartida[$x].'</li>';
                                                            $templateelisa .= '<li>FABRICANTE: '.$ins_fabricante[$x].'</li>';
                                                            $templateelisa .= '<li>PARTIDA: '.$ins_partidaext[$x].'</li>';
                                                            $templateelisa .= '<li>FABRICAÇÃO: '.$ins_fabricacao[$x].'</li>';
                                                            $templateelisa .= '<li>VENCIMENTO: '.$ins_vencimento[$x].'</li>';
                                                            $templateelisa .= '</ul>';
                                                            
                                                            $x++;
                                                            
                                                            }
                                                        
                                                            
                                                            
                                                            if($x % 2 != 0){
                                                                 if($x % 2 == 0){
                                                                 $bor = 'border-right:1px dashed #eee;';
                                                            } else {
                                                                 $bor = ''; 
                                                            }
                                                            
                                                            if ($x > 1){
                                                                $bot = 'border-top:1px dashed #eee;';
                                                            }else{
                                                                $bot = '';
                                                            }
                                                            
                                                            
                                                            
                                                            $templateelisa .= '<ul style="padding-left:4px !important; '.$bot.' '.$bor.'  padding:4px;  float:left;list-style: none; width:47%; min-width:30px;vertical-align:top; margin-bottom:0px; padding-left:0px; float:left; margin-top:0px;font-size:6px !important;">';
                                                            $templateelisa .= '<li></li>';
                                                            
                                                            $templateelisa .= '</ul>';
                                                            }
                                                            $templateelisa .= '</td></tr></table>';
                                                        } 
                                                        
                                                        
                                                        
                                                        
                                                $templateelisa .='
                                                <div class="resdesc">' . preg_replace('/<(\w+) [^>]+>/', '<$1>', $intextopadrao). '</div>
                                            </fieldset>';
                }
                $templateelisa.= '	</td>';

                $arrayTipoTesteElistaResult = array(1556, 670, 590, 6248, 11741, 4160, 8710);

                if ($idtipoteste == 3512) {
                    $urlimg = geragrafelisaSP($arrgraf1);
                }
                elseif ($idtipoteste == 636 or $idtipoteste == 1455) {
                    $urlimg = geragrafelisa4($arrgraf1);
                }
                elseif (in_array($idtipoteste, $arrayTipoTesteElistaResult)) {
                    $urlimg = geragrafelisaRESULT($arrgraf1);
                }
                elseif ($idtipoteste == 3484) {
                    $urlimg = geragrafelisaRESULTSUS($arrgraf1);
                }
                else {
                    $urlimg = geragrafelisa($arrgraf1);
                }
                $urlimg2 = geragrafelisagmt($arrgraf2);
                if (!empty($urlimg) or !empty($urlimg2)) {
                    $templateelisa.= '
                                    <td style="width:36%;  vertical-align:top">';
                    if (!empty($urlimg)) {
                        $templateelisa.= '
                                        <fieldset class="fset">
                                            <legend><font class="ftitulo" style="text-transform:uppercase;">&nbsp;Gráfico *&nbsp;</font></legend> 
                                            <div class="resdesc" style="text-align:center;">
                                                <img src="' . $urlimg . '" style="padding-bottom:5px; height: 120px;"  >
                                            </div>
                                        </fieldset>	';
                    }
                    if ($comparativodelotes == 'Y') {
                        $templateelisa.= '
                                        <br />
                                        <fieldset class="fset">
                                            <legend><font class="ftitulo" style="text-transform:uppercase;">&nbsp;Histórico *&nbsp;</font></legend> 
                                            <div class="resdesc" style="text-align:center;">';
                        if (!empty($urlimg2)) {
                            $templateelisa.= '
                                                <img src="' . $urlimg2 . '" style="padding-bottom:5px;height: 120px;">';
                        }
                        $templateelisa.= '
                                            </div>
                                        </fieldset>	';
                    }
                    $templateelisa.= '
                                        </td>';
                }
                $templateelisa.= '
                                    </tr>';
            }
        }
        if (empty($_REQUEST['csv'])) {
            echo $templateelisa;
        }
    }
}
