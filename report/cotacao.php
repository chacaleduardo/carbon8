<?
include_once("../inc/php/validaacesso.php");
$idobjetojson = $_GET['idobjetojson'];
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
        <link href="../inc/css/carbon.css" media="all" rel="stylesheet" type="text/css" />
        <link href="../inc/bootstrap/css/bootstrap.min.css" media="all" rel="stylesheet" type="text/css" />
        <link href="../inc/css/fontawesome/font-awesome.min.css" media="all" rel="stylesheet" type="text/css" />
        <script src="/inc/js/jquery/jquery-1.11.2.min.js"></script>
        <style type="text/css">
            * {
                text-shadow: none !important;
                filter:none !important;
                -ms-filter:none !important;
                font-family: Helvetica, Arial;
                font-size: 11px;
                -webkit-box-sizing: border-box; 
                -moz-box-sizing: border-box;    
                box-sizing: border-box; 
            }
            html{
                background-color: silver;
            }
            body {
                line-height: 1.4em;
                background-color: white;
            }
            table{width: 100%;}
            .descricaoTabela{width: 10%;}
            .detalheTabela{width: 50%;}
            th{text-align: start;}
            @media print {
                body {-webkit-print-color-adjust: exact;margin: 0cm;}
                html{background-color: transparent;}
	            .quebrapagina{ page-break-before:always; }
	            .rot{color: #777777;}
            }
            .titulodoc{
                height: inherit;
                line-height: inherit;
                font-size: 12px;
                font-weight: bold;
                color: #666;
            }
            @media screen{
                body {
                    margin: auto;
                    margin-top: 0.2cm;
                    margin-bottom: 1cm;
                    padding: 3mm 10mm;
                    width: 29cm;
                }
                .quebrapagina{
                    page-break-before:always;
                    border: 2px solid #c0c0c0;
                    width: 120%;
                    margin: 1.5cm -1.5cm;
                }
                .rot{
                    color: gray;
                }
            }
        </style>
    </head>
    <body>
        <pagina>
            <?
            if (!empty($_GET)){
                $sqlCotacao = "SELECT jobjeto FROM objetojson WHERE idobjetojson =".$idobjetojson;
                $resCotacao = d::b()->query($sqlCotacao) or die("Erro ao recuperar Objeto: ".mysql_error());
                $rowCotacao = mysqli_fetch_assoc($resCotacao);

                //Recupera os dados congelados da Cotação para montar o PDF
                $cotacao = unserialize(base64_decode($rowCotacao["jobjeto"]));

                // Pegado o registro do usuario que aprovou
                $query = "SELECT nomecurto from pessoa where usuario = '{$cotacao['orcamento']['res']['alteradopor']}'";
                $rowPessoa = d::b()->query($query) or die("Erro ao recuperar Objeto: ".mysql_error());
                $aprovador = mysqli_fetch_assoc($rowPessoa);

                ?>
                 <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <table>
                                <tr style="height: 50px;">
                                    <td><span class="titulodoc">Orçamento:</span> <?=$cotacao['orcamento']['res']['idcotacao']?></td>
                                    <?if(!empty($cotacao['orcamento']['res']['tiponf'])){?>
                                        <td><span class="titulodoc">Tipo: </span>
                                            <? 
                                            if($cotacao['orcamento']['res']['tiponf'] == "C"){
                                                echo('<label class="alert-warning">Danfe</label>');
                                            }elseif($cotacao['orcamento']['res']['tiponf'] == "M"){
                                                echo('<label class="alert-warning">Guia/Cupom</label>');
                                            }elseif($cotacao['orcamento']['res']['tiponf'] == "B"){
                                                echo('<label class="alert-warning">Recibo</label>');
                                            }else{
                                                echo('<label class="alert-warning">Serviço</label>');
                                            }
                                            ?>
                                        </td> 
                                    <? } ?>
                                    <td><span class="titulodoc">Título:</span> <?=$cotacao['orcamento']['res']['titulo']?></td>
                                    <td><span class="titulodoc">Prazo:</span> <?=dma($cotacao['orcamento']['res']['prazo'])?></td> 
                                </tr>               
                            </table> 
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">  
                            <table>
                                <tbody>
                                    <tr>
                                        <td align="right" class="descricaoTabela">Cotação:</td>
                                        <td colspan="2" style="width: 5%;"><label class="idbox"><?=$cotacao['cotacao']['res']['idnf']?></label></td>
                                        <td align="right" class="nowrap descricaoTabela">Fornecedor:</td>
                                        <td class="nowrap"><label class="idbox"><?=$cotacao['fornecedor']['res']['nome']?></label></td>
                                        <td align="right" class="descricaoTabela">Finalidade:</td>
                                        <td class="nowrap"><?=$cotacao['finalidade']['res']['finalidadeprodserv']?></td>
                                        <td align="right" class="nowrap descricaoTabela">Emissão NF:</td> 
                                        <td class="nowrap"><?=dma($cotacao['transporte']['res']['dtemissao'])?></td>
                                        <? $status = array("INICIO", "RESPONDIDO", "AUTORIZADO", "ENVIADO", "REPROVADO")?>
                                        <?if(!in_array($cotacao['cotacao']['res']['status'], $status)){?>
                                            <td>NF:</td>
                                            <td class="nowrap"><?=$cotacao['transporte']['res']['idnf']?></td>
                                        <? } ?>
                                        <td align="right" class="descricaoTabela">Status:</td>
                                        <td class="nowrap"><?=$cotacao['cotacao']['res']['status']?></td>
                                    </tr>
                                </tbody>
                            </table>                    
                        </div>            
                        <div class="panel-body" id="cotacao107685">
                            <? if(!empty($cotacao['transporte']['res']['observacaore']))
                            {
                                ?>
                                <table>
                                    <tr>
                                        <td  align="right" style="vertical-align: top;">Observação:</td>					
                                        <td colspan="12">
                                            <font color="red">
                                                <?=str_replace(chr(13),"<br>", $cotacao['transporte']['res']['observacaore'])?>
                                            </font>
                                        </td>
                                    </tr>
                                </table>
                            <? } ?>
                            <table>
                                <tbody>
                                    <tr>
                                        <td align="right" class="descricaoTabela">Nº Orçamento:</td>
                                        <td><?=$cotacao['transporte']['res']['pedidoext']?></td>				
                                        <td align="right" class="descricaoTabela">Vendedor(a):</td>
                                        <td><?=$cotacao['transporte']['res']['aoscuidados']?></td>				
                                        <td align="right" class="descricaoTabela">Telefone:</td>
                                        <td><?=$cotacao['transporte']['res']['telefone']?></td>
                                    </tr>
                                </tbody>
                            </table>
                            <br>                
                            <table class="table table-striped planilha" >
                                <tbody>
                                    <tr>
                                        <th>NF</th>
                                        <!-- <th style="text-align: center;">
                                            <? if($cotacao['cotacao']['res']['marcartodosnfitem'] == 'Y') { ?>
                                                <i style="padding-right: 0px;pointer-events: none;" class="fa fa-check-square-o fa-1x btn-lg pointer"></i>
                                            <? } else { ?>
                                                <i style="padding-right: 0px;pointer-events: none;" class="fa fa-square-o fa-1x btn-lg pointer"></i>
                                            <? } ?>
                                        </th> -->
                                        <th>Qtd Sol.</th>
                                        <th>Un</th>
                                        <th>Descrição</th>
                                        <!--th>Categoria</th-->                        
                                        <th>Valor Un</th>
                                        <th>Desc</th>
                                        <th>IPI R$</th>
                                        <th>Total</th>
                                        <th>Validade</th>
                                        <th>Previsão de Entrega</th>
                                        <th>Obs</th>
                                        <th colspan="4"></th>
                                    </tr>  
                                    <? $itens = $cotacao['nfitem']['res'];
                                   
                                    foreach($itens AS $_itens) 
                                    {
                                        if($_itens['nfe'] == 'Y') {?>          
                                        <tr>
                                            <td></td>
                                            <!-- <td>
                                                <? if($_itens['nfe'] == 'Y') { ?>
                                                    <i style="padding-right: 0px;pointer-events: none;" class="fa fa-check-square-o fa-1x btn-lg pointer"></i>
                                                <? } else { ?>
                                                    <i style="padding-right: 0px;pointer-events: none;" class="fa fa-square-o fa-1x btn-lg pointer"></i>
                                                <? } ?>
                                            </td> -->
                                            <td ><?=$_itens['qtdsol']?></td>
                                            <td><?=$_itens['unidade']?></td>
                                            <td><?=!empty($_itens['codforn'])?$_itens['codforn']:$_itens['descr']?></td> 
                                            <!--td><?//=$_itens['tipoprodserv']?></td-->
                                            <td><?=$_itens['moeda']?> <?=$_itens['vlritem']?></td>
                                            <td><?=$_itens['des']?></td>
                                            <td><?=$_itens['valipi']?></td>
                                            <td align="right"><?=number_format(tratanumero($_itens['total']), 2, ',', '.')?></td>
                                            <td><?=dma($_itens['validade'])?></td>
                                            <td><?=dma($_itens['previsaoentrega'])?></td>
                                            <td><?=$_itens['obs']?></td>
                                        </tr>
                                        <? }
                                        if($_itens['moeda']=="BRL"){
                                            if($_itens['nfe'] == 'Y') {
                                            $totalsemdesc += $_itens['total'] + $_itens['valipi'];
                                            $total = $total + $_itens['total'] + $_itens['valipi'] - $_itens['des'];
                                            $desconto += $_itens['des'];
                                            $moeda= $_itens['moeda'];
                                            }
                                        }else{
                                            if($_itens['nfe'] == 'Y') {
                                            $total = $total + $_itens['totalext'];
                                            $moeda = $_itens['moeda'];
                                            }
                                        }
                                    } 
                                    ?>
                                    <tr>
                                        <td colspan="6"></td>
                                        <td colspan="10"></td>
                                    </tr>
                                    <tr>
                                        <td align="right" colspan="7">Frete: 
                                            <? if($cotacao['transporte']['res']['modfrete'] == 0){
                                                    echo 'CIF';
                                                } elseif($cotacao['transporte']['res']['modfrete'] == 1) {
                                                    echo 'FOB';
                                                }
                                            ?>
                                        </td>
                                        <td align="right">
                                            <? if(empty($cotacao['transporte']['res']['frete'])){$frete = 0.00;} else {$frete = $cotacao['transporte']['res']['frete'];} ?>   
                                            <?=number_format(tratanumero($frete), 2, ',', '.');?>         
                                        </td>
                                        <td colspan="7"></td>
                                    </tr>
                                    <tr>
                                        <td align="right" colspan="7">Total sem Desconto:<b><?=$moeda?> </b></td>
                                        <td align="right"><b><?=number_format(tratanumero($totalsemdesc), 2, ',', '.');?></b></td>
                                        <td colspan="7"></td>
                                    </tr>
                                    <tr>
                                        <td align="right" colspan="7">Desconto:<b><?=$moeda?></b></td>
                                        <td align="right"><b><?=number_format(tratanumero($desconto), 2, ',', '.');?></b></td>
                                        <td colspan="7"></td>
                                    </tr>
                                    <tr>
                                        <td align="right" colspan="7">Total com Desconto:<b><?=$moeda?></b></td>
                                        <td align="right"><b><?=number_format(tratanumero($totalsemdesc - $desconto), 2, ',', '.');?></b></td>
                                        <td colspan="7"></td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="row"> 	 
                                <div class="col-md-6">
                                    <div class="">
                                        <div class=""></div>
                                        <div class="panel-body">   
                                            <table>
                                                <tbody>
                                                    <tr>
                                                        <td>Transportadora: <?=$cotacao['transportadora']['res']['nome']?></td> 
                                                        <td>Pagto: <?=$cotacao['pagamento']['res']['descricao']?></td> 
                                                        <td class="nowrap">1º Venc: <?=$cotacao['transporte']['res']['diasentrada']?> Dias</td>	
                                                        <td>Parcelas: <?=$cotacao['transporte']['res']['parcelas']?></td>
                                                        <? if($cotacao['transporte']['res']['parcelas'] > 1) { ?>
                                                            <td><div class="divtab">Intervalo Parcelas: <?=$cotacao['transporte']['res']['intervalo']?> Dias</div></td>
                                                        <? } ?>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="2">Obs: <?=$cotacao['transporte']['res']['obs']?></td>
                                                        <!--td colspan="3">Obs. Interna: <?//=$cotacao['transporte']['res']['obsinterna']?></td-->
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row"> 	 
                                <div class="col-12">
                                    <div class="panel-body" style="text-transform: uppercase;display: flex; flex-wrap: wrap;">
                                        <div style="width: 50%;">
                                            <span>
                                                Aprovado por: <?=$aprovador['nomecurto']?>                                                     
                                            </span>
                                        </div>
                                        <div style="width: 50%; text-align: right;">
                                            <span>Em: <?= date('d/m/Y H:i:s', strtotime($cotacao['orcamento']['res']['alteradoem']))?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?
            }
            ?>
        </pagina>
    </body>
</html>