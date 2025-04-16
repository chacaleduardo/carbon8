<!DOCTYPE html>
<!--
 * OpenBoleto - Geração de boletos bancários em PHP
 *
 * LICENSE: The MIT License (MIT)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this
 * software and associated documentation files (the "Software"), to deal in the Software
 * without restriction, including without limitation the rights to use, copy, modify,
 * merge, publish, distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies
 * or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A
 * PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
-->
<html lang="pt-BR">
<head>
    <meta charset="">
    <title><?php echo $cedente; ?></title>
    <style type="text/css">
/**
 * OpenBoleto - Geração de boletos bancários em PHP
 *
 * LICENSE: The MIT License (MIT)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this
 * software and associated documentation files (the "Software"), to deal in the Software
 * without restriction, including without limitation the rights to use, copy, modify,
 * merge, publish, distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies
 * or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A
 * PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

@media print {
    .noprint {
        display:none;
    }
}

body{
    background-color: #ffffff;
    margin-right: 100px;
}

.table-boleto {
    font: 9px Arial;
    width: 100%;
}

.table-boleto td {
    border-left: 1px solid #000;
    border-top: 1px solid #000;
}

.table-boleto td:last-child {
    border-right: 1px solid #000;
}

.table-boleto .titulo {
    color: #000033;
}

.linha-pontilhada {
    color: #000033;
    font: 9px Arial;
    width: 100%;
    border-bottom: 1px dashed #000;
    text-align: right;
    margin-bottom: 10px;
}

.table-boleto .conteudo {
    font: bold 10px Arial;
    height: 13px;
}

.table-boleto .sacador {
    display: inline;
    margin-left: 5px;
}

.table-boleto td {
    padding: 1px 4px;
}

.table-boleto .noleftborder {
    border-left: none !important;
}

.table-boleto .notopborder {
    border-top: none !important;
}

.table-boleto .norightborder {
    border-right: none !important;
}

.table-boleto .noborder {
    border: none !important;
}

.table-boleto .bottomborder {
    border-bottom: 1px solid #000 !important;
}

.table-boleto .rtl {
    text-align: right;
}

.table-boleto .logobanco {
    display: inline-block;
    max-width: 150px;
}

.table-boleto .logocontainer {
    width: 257px;
    display: inline-block;
}

.table-boleto .logobanco img {
    margin-bottom: -5px;
}

.table-boleto .codbanco {
    font: bold 20px Arial;
    padding: 1px 5px;
    display: inline;
    border-left: 2px solid #000;
    border-right: 2px solid #000;
    width: 51px;
    margin-left: 25px;
}

.table-boleto .linha-digitavel {
    font: bold 14px Arial;
    display: inline-block;
    width: 406px;
    text-align: right;
}

.table-boleto .nopadding {
    padding: 0px !important;
}

.table-boleto .caixa-gray-bg {
    font-weight: bold;
    background: #ccc;
}

.info {
    font: 11px Arial;
}

.info-empresa {
    font: 11px Arial;
}

.header {
    font: bold 13px Arial;
    display: block;
    margin: 4px;
}

.barcode {
    height: 50px;
}

.barcode div {
    display: inline-block;
    height: 100%;
}

.barcode .black {
    border-color: #000;
    border-left-style: solid;
    width: 0px;
}

.barcode .white {
    background: #fff;
}

.barcode .thin.black {
    border-left-width: 1px;
}

.barcode .large.black {
    border-left-width: 3px;
}

.barcode .thin.white {
    width: 1px;
}

.barcode .large.white {
    width: 3px;
}
    </style>
</head>
<body>

    <div style="width: 666px">
        <div class="noprint info">
            <h2>Instruções de Impressão</h2>
            <ul>
                <li>Imprima em impressora jato de tinta (ink jet) ou laser em qualidade normal ou alta (Não use modo econômico).</li>
                <li>Utilize folha A4 (210 x 297 mm) ou Carta (216 x 279 mm) e margens mínimas á esquerda e á  direita do formulário.</li>
                <li>Corte na linha indicada. Não rasure, risque, fure ou dobre a região onde se encontra o código de barras.</li>
                <li>Caso não apareça o código de barras no final, pressione F5 para atualizar esta tela.</li>
                <li>Caso tenha problemas ao imprimir, copie a sequencia numérica abaixo e pague no caixa eletrônico ou no internet banking:</li>
            </ul>
            <span class="header">Linha Digitável: <?php echo $dadosboleto["linha_digitavel"]; ?></span>
            <?php if ($dadosboleto["valor_boleto"]) : ?><span class="header">Valor: R$ <?php echo $dadosboleto["valor_boleto"]; ?></span><?php endif ?>
            <?php if ($pagamento_minimo) : ?><span class="header">Pagamento mínimo: R$ <?php echo $pagamento_minimo; ?></span><?php endif ?>
            <br>
            <div class="linha-pontilhada" style="margin-bottom: 20px;">Recibo do Pagador</div>
        </div>

        <div class="info-empresa">
            <?php 
	    if ($logotipo) : ?>
            <div style="display: inline-block;">
                <img alt="logotipo" src="<?php echo $logotipo; ?>" />
            </div>
            <?php endif ?>
            <div style="display: inline-block; vertical-align: super;">
                <div><strong><?php echo $dadosboleto["cedente"]; ?></strong></div>
                <div><?php echo $dadosboleto["cpf_cnpj"]; ?></div>
                <div><?php echo $dadosboleto["endereco"];  ?></div>
                <div><?php echo $dadosboleto["cidade_uf"]; ?></div>
            </div>
        </div>
        <br>

        <table class="table-boleto" cellpadding="0" cellspacing="0" border="0">
            <tbody>
            <tr style="width:100%">
                <td valign="bottom" colspan="8" class="noborder nopadding">
                    <div class="logocontainer">
                        <div class="logobanco">
                            <img src="<?php $logo_banco="imagens/logosicredi.jpg"; echo $logo_banco; ?>" alt="logotipo do banco">
                        </div>
                        <div class="codbanco"><?php echo $codigo_banco_com_dv ?></div>
                    </div>
                    <div class="linha-digitavel"><?php echo $dadosboleto["linha_digitavel"] ?></div>
                </td>
            </tr>
            <tr>
                <td colspan="2" width="150">
                    <div class="titulo">Beneficiário</div>
                    <div class="conteudo"><?php echo $dadosboleto["cedente"] ?></div>
                </td>
                <td width="100">
                    <div class="titulo">CPF/CNPJ</div>
                    <div class="conteudo"><?php echo $dadosboleto["cpf_cnpj"] ?></div>
                </td>
                <td width="120">
                    <div class="titulo">Agência/Código do Beneficiário</div>
                  <div class="conteudo rtl"><?=$dadosboleto["agencia_codigo"]?></div>
                </td>
                <td width="100">
                    <div class="titulo">Vencimento</div>
                    <div class="conteudo rtl"><?php echo $dadosboleto["data_vencimento"] ?></div>
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <div class="titulo">Pagador</div>
                    <div class="conteudo"><?php echo $dadosboleto["sacado"] ?></div>
                </td>
                <td>
                    <div class="titulo">Nº documento</div>
                    <div class="conteudo rtl"><?php echo $dadosboleto["numero_documento"] ?></div>
                </td>
                <td>
                    <div class="titulo">Nosso número</div>
                    <div class="conteudo rtl"><?php echo $dadosboleto["nosso_numero"] ?></div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="titulo">Espécie</div>
                    <div class="conteudo"><?php echo $dadosboleto["especie"] ?></div>
                </td>
                <td>
                    <div class="titulo">Quantidade</div>
                    <div class="conteudo rtl"><?php echo  $dadosboleto["quantidade"] ?></div>
                </td>
                <td>
                    <div class="titulo">Valor</div>
                    <div class="conteudo rtl"><?php echo $dadosboleto["valor_unitario"] ?></div>
                </td>
                <td>
                    <div class="titulo">(-) Descontos / Abatimentos</div>
                    <div class="conteudo rtl"><?php echo $desconto_abatimento ?></div>
                </td>
                <td>
                    <div class="titulo">(=) Valor Documento</div>
                    <div class="conteudo rtl"><?php echo $dadosboleto["valor_boleto"] ?></div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="conteudo"></div>
                    <div class="titulo">Demonstrativo</div>
                </td>
                <td>
                    <div class="titulo">(-) Outras deduções</div>
                    <div class="conteudo"><?php echo $outras_deducoes ?></div>
                </td>
                <td>
                    <div class="titulo">(+) Outros acréscimos</div>
                    <div class="conteudo rtl"><?php echo $outros_acrescimos ?></div>
                </td>
                <td>
                    <div class="titulo">(=) Valor cobrado</div>
                    <div class="conteudo rtl"><?php echo $valor_cobrado ?></div>
                </td>
            </tr>
            <tr>
                <td colspan="4"><div style="margin-top: 10px" class="conteudo"><?php echo $demonstrativo[0] ?></div></td>
                <td class="noleftborder"><div class="titulo">Autenticação mecânica</div></td>
            </tr>
            <tr>
                <td colspan="5" class="notopborder"><div class="conteudo"><?php echo $dadosboleto["demonstrativo1"] ?></div></td>
            </tr>
            <tr>
                <td colspan="5" class="notopborder"><div class="conteudo"><?php echo $dadosboleto["demonstrativo2"]?></div></td>
            </tr>
            <tr>
                <td colspan="5" class="notopborder"><div class="conteudo"><?php echo $dadosboleto["demonstrativo3"] ?></div></td>
            </tr>
            <tr>
                <td colspan="5" class="notopborder"><div class="conteudo"><?php echo $dadosboleto["demonstrativo4"] ?></div></td>
            </tr>
            <tr>
                <td colspan="5" class="notopborder bottomborder"><div style="margin-bottom: 10px;" class="conteudo"><?php echo $dadosboleto["demonstrativo5"]  ?></div></td>
            </tr>
            </tbody>
        </table>
        <br>
        <div class="linha-pontilhada">Corte na linha pontilhada</div>
        <br>

       <!--
 * OpenBoleto - Geração de boletos bancários em PHP
 *
 * LICENSE: The MIT License (MIT)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this
 * software and associated documentation files (the "Software"), to deal in the Software
 * without restriction, including without limitation the rights to use, copy, modify,
 * merge, publish, distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies
 * or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A
 * PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
-->

<table class="table-boleto" cellpadding="0" cellspacing="0" border="0">
    <tbody>
    <tr>
        <td valign="bottom" colspan="8" class="noborder nopadding">
            <div class="logocontainer">
                <div class="logobanco">
                    <img src="<?php echo $logo_banco; ?>" alt="logotipo do banco">
                </div>
                <div class="codbanco"><?php echo $codigo_banco_com_dv ?></div>
            </div>
            <div class="linha-digitavel"><?php echo $dadosboleto["linha_digitavel"] ?></div>
        </td>
    </tr>
    <tr>
        <td colspan="7">
            <div class="titulo">Local de pagamento</div>
            <div class="conteudo"><?php echo $dadosboleto["local_pagamento"] ?></div>
        </td>
        <td width="20%">
            <div class="titulo">Vencimento</div>
            <div class="conteudo rtl"><?php echo $dadosboleto["data_vencimento"] ?></div>
        </td>
    </tr>
    <tr>
        <td colspan="7">
            <div class="titulo">Beneficiário</div>
            <div class="conteudo"><?php echo $dadosboleto["cedente"] ?></div>
        </td>
        <td>
            <div class="titulo">Agência/Código Beneficiário</div>
            <div class="conteudo rtl"><?php echo $dadosboleto["agencia_codigo"] ?></div>
        </td>
    </tr>
    <tr>
        <td width="15%" colspan="2">
            <div class="titulo">Data do documento</div>
            <div class="conteudo"><?php echo $dadosboleto["data_documento"] ?></div>
        </td>
        <td width="12%" colspan="2">
            <div class="titulo">Nº documento</div>
            <div class="conteudo"><?php echo $dadosboleto["numero_documento"] ?></div>
        </td>
        <td width="10%">
            <div class="titulo">Espécie doc.</div>
            <div class="conteudo"><?php echo $dadosboleto["especie_doc"] ?></div>
        </td>
        <td>
            <div class="titulo">Aceite</div>
            <div class="conteudo"><?php echo $dadosboleto["aceite"] ?></div>
        </td>
        <td width="15%">
            <div class="titulo">Data processamento</div>
            <div class="conteudo"><?php echo $dadosboleto["data_processamento"] ?></div>
        </td>
        <td>
            <div class="titulo">Nosso número</div>
            <div class="conteudo rtl"><?php echo $dadosboleto["nosso_numero"] ?></div>
        </td>
    </tr>
    <tr>
        <?php if (!$esconde_uso_banco) : ?>
            <td<?php if (!$mostra_cip) : ?> colspan="2"<?php endif ?>>
                <div class="titulo">Uso do banco</div>
                <div class="conteudo"><?php echo $uso_banco ?></div>
            </td>
        <?php endif; ?>

        <?php if ($mostra_cip) : ?>
            <!-- Campo exclusivo do Bradesco -->
            <td width="20">
                <div class="titulo">CIP</div>
                <div class="conteudo"><?php echo $cip ?></div>
            </td>
        <?php endif ?>

        <td<?php if ($esconde_uso_banco) : ?> colspan="3"<?php endif; ?>>
            <div class="titulo">Carteira</div>
            <div class="conteudo"><?php echo $dadosboleto["carteira"] ?></div>
        </td>
        <td width="3%">
            <div class="titulo">Espécie</div>
            <div class="conteudo"><?php echo $dadosboleto["especie"] ?></div>
        </td>
        <td colspan="2">
            <div class="titulo">Quantidade</div>
            <div class="conteudo"><?php echo $dadosboleto["quantidade"] ?></div>
        </td>
        <td width="11%">
            <div class="titulo">Valor</div>
            <div class="conteudo"><?php echo $dadosboleto["valor_unitario"] ?></div>
        </td>
        <td>
            <div class="titulo">(=) Valor do Documento</div>
            <div class="conteudo rtl"><?php echo $dadosboleto["valor_boleto"] ?></div>
        </td>
    </tr>
    <tr>
        <td colspan="7">
            <div class="titulo">Instruções de responsabilidade do BENEFICIÁRIO.</div>
        </td>
        <td>
            <div class="titulo">(-) Descontos / Abatimentos</div>
            <div class="conteudo rtl"><?php echo $desconto_abatimento ?></div>
        </td>
    </tr>
    <tr>
        <td colspan="7" class="notopborder">
            <div class="conteudo"><?php echo $dadosboleto["instrucoes1"] ?></div>
            <div class="conteudo"><?php echo $dadosboleto["instrucoes2"]; ?></div>
        </td>
        <td>
            <div class="titulo">(-) Outras deduções</div>
            <div class="conteudo rtl"><?php echo $outras_deducoes ?></div>
        </td>
    </tr>
    <tr>
        <td colspan="7" class="notopborder">
            <div class="conteudo"><?php echo $dadosboleto["instrucoes3"] ?></div>
            <div class="conteudo"><?php echo $dadosboleto["instrucoes4"] ?></div>
        </td>
        <td>
            <div class="titulo">(+) Mora / Multa</div>
            <div class="conteudo rtl"><?php echo $mora_multa ?></div>
        </td>
    </tr>
    <tr>
        <td colspan="7" class="notopborder">
            <div class="conteudo"><?php echo $dadosboleto["instrucoes5"] ?></div>
            <div class="conteudo"><?php echo $dadosboleto["instrucoes6"] ?></div>
        </td>
        <td>
            <div class="titulo">(+) Outros acréscimos</div>
            <div class="conteudo rtl"><?php echo $outros_acrescimos ?></div>
        </td>
    </tr>
    <tr>
        <td colspan="7" class="notopborder">
            <div class="conteudo"><?php echo $dadosboleto["instrucoes7"]; ?></div>
            <div class="conteudo"><?php echo $dadosboleto["instrucoes8"]; ?></div>
        </td>
        <td>
            <div class="titulo">(=) Valor cobrado</div>
            <div class="conteudo rtl"><?php echo $valor_cobrado ?></div>
        </td>
    </tr>
    <tr>
        <td colspan="7">
            <div class="titulo">Pagador:</div>
            <div class="conteudo"><?php echo $dadosboleto["sacado"] ?></div>
            <div class="conteudo"><?php echo $dadosboleto["endereco1"]  ?></div>
            <div class="conteudo"><?php echo $dadosboleto["endereco2"]  ?></div>

        </td>
        <td class="noleftborder">
            <div class="titulo" style="margin-top: 50px">Cód. Baixa</div>
        </td>
    </tr>

    <tr>
        <td colspan="6" class="noleftborder">
            <div class="titulo">Sacador/Avalista <div class="conteudo sacador"><?php echo $sacador_avalista; ?></div></div>
        </td>
        <td colspan="2" class="norightborder noleftborder">
            <div class="conteudo noborder rtl">Autenticação mecânica - Ficha de Compensação</div>
        </td>
    </tr>

    <tr>
        <td colspan="8" class="noborder">
            <?php echo fbarcode($dadosboleto["codigo_barras"]); ?>
        </td>
    </tr>

    </tbody>
</table>

    </div>
</body>
</html>
