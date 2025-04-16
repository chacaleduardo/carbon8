<?
require_once("../inc/php/validaacesso.php");
require_once("../form/controllers/solfab_controller.php");
$geraarquivo = $_GET['geraarquivo'];

//merge pdf
require('../inc/pdfmerger/src/Pdf.php');
require_once('../inc/pdfmerger/setasign/fpdf/fpdf.php');
require_once('../inc/pdfmerger/setasign/fpdi/src/autoload.php');

use PDFMerger\Pdf;
//fim merge pdf restante no rodapé

ob_start();
if (empty($_GET["idsolfab"])) {
   die("SF não enviada");
}

$row = SolfabController::buscarDadosSolfabRelatorio($_GET["idsolfab"]);

//@todo: realizar a verificação através de Classes
function validaDataExame($ascriadoem, $dataamostra)
{
   if ($ascriadoem < $dataamostra) {
      die("<h1>Erro: Data Final Exame [" . $ascriadoem . "] < Data Início Exame [" . $dataamostra . "]</h1>");
   }
}
?>
<html>

   <head>
      <link href="../inc/css/emissaoresultadopdf.css" rel="stylesheet" type="text/css" />
      <link href="../form/css/solfab_css.css?_<?=date("dmYhms")?>" rel="stylesheet">
      <title>SF</title>
   </head>

   <body>
      <table style="width:700px; margin:auto; ">
         <tr>
            <td style="width: 100%">
               <table style="width: 100%">
                  <tr>
                     <td>
                        <table class="tsep" style="width:100%; margin-top:6px;">
                           <tr>
                              <td style="font-size:13px;">
                                 <table>
                                    <tr>
                                       <td>
                                          <?
                                          // GVT - 17/04/2020 - Altera logo do relatório, utiliza a imagem cadastrada na empresa.
                                          $_timbrado = $_GET["_timbrado"] != '' ? $_GET["_timbrado"] : '';
                                          $timbradoidempresa = $_GET["_timbradoidempresa"] != '' ? " AND idempresa = ".$_GET["_timbradoidempresa"] : getImagemRelatorio('solfab', 'idsolfab', $_GET["idsolfab"]);

                                          if ($_timbrado != 'N') 
                                          {
                                             if ($row["data"] >= '2021-05-18 00:00:01' && ($_SESSION["SESSAO"]["IDEMPRESA"] == 1 || $_SESSION["SESSAO"]["IDEMPRESA"] == 2)) 
                                             {
                                                $timbradoidempresa =  " AND idempresa = 2";
                                             } 
                                             $_figtimbrado = SolfabController::buscarCaminhoImagemTipoHeaderProduto($timbradoidempresa, true);
                                             $_timbradocabecalho = $_figtimbrado["caminho"];

                                             if (!empty($_timbradocabecalho)) { ?>
                                                <div id="_timbradocabecalho"><img src="<?=$_timbradocabecalho ?>" height="18px" width="140px"></div>
                                                <? 
                                             }
                                          }
                                          ?>
                                       <td>SOLICITAÇÃO DE AUTORIZAÇÃO PARA FABRICAÇÃO DE VACINA AUTÓGENA </td>
                                    </tr>
                                 </table>
                              </td>
                           </tr>
                           <tr>
                              <td>
                                 <table class="tsep" style="width:100%;">
                                    <tr>
                                       <td>
                                          <table class="tbgr" style="width:100%; border:1px solid #f7f7f7;">
                                             <tr style="background-color:#f7f7f7; font-size:13x; text-transform:uppercase;	height:20px;">
                                                <td colspan="6" style="font-size:11px;">DADOS DA SOLICITAÇÃO DE FABRICAÇÃO
                                                </td>
                                             </tr>
                                             <tr>
                                                <td style="width:12% !important;" class="tdrot grrot">ID:</td>
                                                <td style="width:38% !important;" class="tdval grval"><?=$row["idsolfab"] ?></td>
                                                <td style="width:12% !important;" class="tdrot grrot">Partida:</td>
                                                <td style="width:38% !important;" class="tdval grval"><?=$row["partida"] ?>/<?=$row["exercicio"] ?></td>
                                                <td style="width:12% !important;" class="tdrot grrot">Data:</td>
                                                <td style="width:38% !important;" class="tdval grval"><?=dma($row["data"]) ?></td>
                                             </tr>
                                             <tr>
                                                <td colspan="6"><?=nl2br(espaco2nbsp($row["descr"])) ?></td>
                                             </tr>
                                          </table>
                                       </td>
                                    </tr>
                                 </table>
                              </td>
                           </tr>
                           <tr style="height:20px;">
                              <td></td>
                           </tr>
                           <tr>
                              <td>
                                 <table class="tsep" style="width:100%;">
                                    <tr>
                                       <td>
                                          <table class="tbgr" style="width:100%; border:1px solid #f7f7f7;">
                                             <tr style="background-color:#f7f7f7; font-size:13px; text-transform:uppercase;	height:20px;">
                                                <td colspan="6" style="font-size:11px;">DADOS DO CLIENTE</td>
                                             </tr>
                                             <tr>
                                                <td class="tdrot grrot">Cliente:</td>
                                                <td class="tdval grval" colspan="5"><?=$row["razaosocial"] ?></td>
                                             </tr>
                                             <tr>
                                                <td class="tdrot grrot">Propriedade:</td>
                                                <td class="tdval grval" colspan="5"><?=$row["nome"] ?></td>
                                             </tr>
                                             <tr>
                                                <td class="tdrot grrot">Endereço:</td>
                                                <td class="tdval grval" colspan="5">
                                                   <?
                                                   if (empty($row["enderecosacado"])) {
                                                   ?>
                                                      <div class="alert alert-warning">
                                                         <span class="notProducao"><i class="fa fa-exclamation-triangle"></i>&nbsp;Favor preencher o endereço da propriedade no cadastro do cliente!</span>
                                                      </div>
                                                   <?
                                                   } else {
                                                      echo ($row["enderecosacado"]);
                                                   }
                                                   ?>
                                                </td>
                                             </tr>
                                             <tr>
                                                <td class="tdrot grrot">Cnpj:</td>
                                                <td class="tdval grval" colspan="2"><?=formatarCPF_CNPJ($row["cpfcnpj"]) ?></td>
                                                <td class="tdrot grrot" style="width: 100px !important;">Inscr. Estadual:</td>
                                                <td class="tdval grval" colspan="2"><?=$row["inscrest"] ?></td>
                                             </tr>
                                          </table>
                                       </td>
                                    </tr>
                                 </table>
                              </td>
                           </tr>
                           <tr style="height:20px;">
                              <td></td>
                           </tr>
                           <tr>
                              <td>
                                 <table class="tsep" style="width:100%;">
                                    <tr>
                                       <td>
                                          <table class="tbgr" style="width:100%; border:1px solid #f7f7f7;">
                                             <tr style="background-color:#f7f7f7; font-size:13px; text-transform:uppercase;	height:20px;">
                                                <td style="font-size:11px;">Dados da Solicitação</td>
                                             </tr>
                                             <tr style=" font-size:10px; ">
                                                <td>Espécie e nº de animais suscetíveis na propriedade:</td>
                                             </tr>
                                             <tr>
                                                <td class="tdval grval"><?=nl2br(espaco2nbsp($row["animsuscep"])) ?></td>
                                             </tr>
                                             <tr style=" font-size:10px; ">
                                                <td>Identificação e endereço das propriedades adjacentes:</td>
                                             </tr>
                                             <tr>
                                                <td class="tdval grval"><?=nl2br(espaco2nbsp($row["propad"])) ?></td>
                                             </tr>
                                             <tr style=" font-size:10px; ">
                                                <td>Espécie e nº de animais susceptíveis nas propriedades adjacentes:</td>
                                             </tr>
                                             <tr>
                                                <td class="tdval grval"><?=nl2br(espaco2nbsp($row["animsuscepad"])) ?></td>
                                             </tr>
                                          </table>
                                       </td>
                                    </tr>
                                 </table>
                              </td>
                           </tr>
                           <tr style="height:20px;">
                              <td></td>
                           </tr>
                           <tr>
                              <td>
                                 <table class="tsep" style="width:100%;">
                                    <tr>
                                       <td>
                                          <table class="tbgr" style="width:100%; border:1px solid #f7f7f7;">
                                             <tr style="background-color:#f7f7f7; font-size:13px; text-transform:uppercase;	height:20px;">
                                                <td style="font-size:11px;">Informações do Produto</td>
                                             </tr>
                                             <tr style=" font-size:10px; ">
                                                <td>Nome Comercial:</td>
                                             </tr>
                                             <tr>
                                                <td class="tdval grval"><?=nl2br($row["descr_prod"]) ?></td>
                                             </tr>
                                             <tr style=" font-size:10px; ">
                                                <td>Nº de doses por partida:</td>
                                             </tr>
                                             <tr>
                                                <td class="tdval grval"><?=nl2br(espaco2nbsp($row["ndosespart"])) ?></td>
                                             </tr>
                                             <tr style=" font-size:10px; ">
                                                <td>Nº de doses por propriedade:</td>
                                             </tr>
                                             <tr>
                                                <td class="tdval grval"><?=nl2br(espaco2nbsp($row["ndoses"])) ?></td>
                                             </tr>
                                          </table>
                                       </td>
                                    </tr>
                                 </table>
                              </td>
                           </tr>
                           <tr style="height:20px;">
                              <td></td>
                           </tr>
                           <tr>
                              <td>
                                 <table class="tsep" style="width:100%;">
                                    <tr>
                                       <td>
                                          <table class="tbgr" style="width:100%; border:1px solid #f7f7f7;">
                                             <tr style="background-color:#f7f7f7; font-size:13px; text-transform:uppercase;	height:20px;">
                                                <td colspan="6" style="font-size:11px;">Semente(s)</td>
                                             </tr>
                                             <tr>
                                                <td class="tdrot grrot" style="width:55% !important">IDENTIFICAÇÃO</td>
                                                <td class="tdrot grrot" style="width:5% !important">PARTIDA</td>
                                                <td class="tdrot grrot" style="width:2% !important">TRA</td>
                                                <td class="tdrot grrot" style="width:2% !important">LDA</td>
                                                <td class="tdrot grrot" style="width:15% !important">TIPO DE AMOSTRA</td>
                                                <td class="tdrot grrot" style="width: 21% !important;">Autorização <span><i style="font-size:9px"> - Nº SEI</i></span></td>
                                             </tr>
                                             <?
                                             $listarLotesSolfab = SolfabController::buscarItensSolfabRelatorioStatusNotIN($row['idsolfab']);
                                             $i = 0;
                                             foreach ($listarLotesSolfab as $linha) 
												         {
                                                ?>
                                                <tr>
                                                   <td class="tdrot grval"><?=$linha["descr"] ?></td>
                                                   <td class="tdrot grval"><?=$linha["partida"] ?>/<?=$linha["exercicio"] ?></td>
                                                   <td class="tdrot grval"><?=$linha["idregistro"] ?>/<?=$linha["exercicioam"] ?></td>
                                                   <td class="tdrot grval"><?=$linha["idresultado"] ?></td>
                                                   <td class="tdrot grval"><?=$linha["orgao"] ?></td>
                                                   <td class="tdrot grval"><?=$linha["ultimasolfab"] ?></td>
                                                </tr>
                                                <?
                                             }
                                             ?>
                                          </table>
                                       </td>
                                    </tr>
                                 </table>
                              </td>
                           </tr>
                           <?
                           if (!empty($row["observacao"])) {
                           ?>
                              <tr style="height:20px;">
                                 <td></td>
                              </tr>
                              <tr>
                                 <td>
                                    <table class="tsep" style="width:100%;">
                                       <tr>
                                          <td>
                                             <table class="tbgr" style="width:100%; border:1px solid #f7f7f7;">
                                                <tr style="background-color:#f7f7f7; font-size:13px; text-transform:uppercase;	height:20px;">
                                                   <td colspan="6" style="font-size:11px;">OBSERVAÇÃO</td>
                                                </tr>
                                                <tr>

                                                   <td class="tdval grval" style="word-break: break-all;"><?=nl2br(espaco2nbsp($row["observacao"])) ?></td>
                                                </tr>
                                             </table>
                                          </td>
                                       </tr>
                                    </table>
                                 </td>
                              </tr>
                           <?
                           } //if(!empty($row["observacao"])){
                           ?>
                        </table>
                     </td>
                  </tr>
               </table>
            </td>
         </tr>
         <tr>
            <td style="height:20px;"></td>
         </tr>
         <tr>
            <td style="width: 100%">
               <table style="width: 100%">
                  <tr>
                     <td>
                        <table class="tsep" style="width:100%; margin-top:6px;">
                           <tr>
                              <td style="font-size:13px;" class="nowrap">

                                 <table>
                                    <? if ($row["data"] >= '2021-05-18 00:00:01') { ?>
                                       <tr>
                                          <td>Técnico Resp.:</td>
                                          <td>José Renato de O. Branco</td>
                                          <td>CRMV:</td>
                                          <td>MG - 19770</td>
                                          <td>Assinatura.:</td>
                                          <td><img style=" height: 30px; " src='../inc/img/sig5655.gif'></td>
                                       </tr>
                                    <? } else { ?>
                                       <tr>
                                          <td>Técnico Resp.:</td>
                                          <td>Marcio Danilo Botrel Coutinho</td>
                                          <td>CRMV:</td>
                                          <td>MG - 1454</td>
                                          <td>Assinatura.:</td>
                                          <td><img style=" height: 30px; " src='../inc/img/sig797.gif'></td>
                                       </tr>
                                    <? } ?>
                                    <tr>
                                       <td style="height:20px;"></td>
                                    </tr>
                                    <tr>
                                       <td>Fiscal Agropec.:</td>
                                       <td>..............................................................................</td>
                                       <td></td>
                                       <td>Parecer:</td>
                                       <td colspan="3">...........................................................</td>
                                    </tr>
                                    <tr>
                                       <td style="height:20px;"></td>
                                    </tr>
                                    <tr>
                                       <td>Assinatura.:</td>
                                       <td>..............................................................................</td>
                                       <td></td>
                                       <td>Data:</td>
                                       <td colspan="3">___/____/_________</td>
                                    </tr>
                                 </table>
                              </td>
                           </tr>
                        </table>
                     </td>
                  </tr>
               </table>
            </td>
         </tr>
      </table>
      <?
      $listrarAmostrasVinculadasSolfab = SolfabController::buscarAmostrasVinculadasSolfab($_GET["idsolfab"]);
      $numtra = count($listrarAmostrasVinculadasSolfab);
      if ($numtra > 0) 
      {
         $li = 0;
         foreach ($listrarAmostrasVinculadasSolfab as $rowt) 
         {
            $listarDataAmostra = SolfabController::buscarDatasAmostra($rowt["idamostra"]);
            $dataamostra = dmahms($listarDataAmostra['dataamostrah']);
            $idregistro =  $listarDataAmostra['idregistro'];
            $exercicio =  $listarDataAmostra['exercicio'];

            //Abre um $row com os dados da coluna jresultado  
            $oAm = SolfabController::buscarAmostraPorEnderecoEFinalidade($rowt["idamostra"]);
            $oRes = SolfabController::buscarResultado($rowt["idamostra"]);

            $oAAm = SolfabController::buscarAgenteAmostras($rowt["idamostra"]);

            $titulo = "";
            if ($oAm["status"] == "ABERTO" || $oAm["status"] == "ENVIADO") {
               $titulo = "Termo de Envio de Amostra";
               $sub = "TEA";
            } elseif ($oAm["status"] == "DEVOLVIDO" || $oAm["status"] == "ASSINADO") {
               $titulo = "Termo de Recepção de Amostra";
               $sub = "TRA";
            }
            ?>

            <div style="page-break-before:always;display:none"></div>
            <table style="width:700px; margin:auto;display:none ">
               <tr>
                  <td style="width: 100%">
                     <table style="width: 100%">
                        <tr>
                           <td>
                              <table class="tsep" style="width:100%; margin-top:6px;">
                                 <tr>
                                    <td style=" font-size:13px;">
                                       <table class="tsep" style="width:100%;">
                                          <tr>
                                             <td>
                                                <?
                                                $_timbrado = $_GET["_timbrado"] != '' ? $_GET["_timbrado"] : '';
                                                $timbradoidempresa = $_GET["_timbradoidempresa"] != '' ? "and idempresa = " . $_GET["_timbradoidempresa"] : getImagemRelatorio('solfab', 'idsolfab', $_GET["idsolfab"]);

                                                if ($_timbrado != 'N') {
                                                   if ($oAm["dataamostrah"] >= '2021-05-18 00:00:01' &&  ($_SESSION["SESSAO"]["IDEMPRESA"] == 1 || $_SESSION["SESSAO"]["IDEMPRESA"] == 2)) {
                                                      $timbradoidempresa = "and idempresa = 2";


                                                      $nomresp = 'José Renato de O. Branco';
                                                      $crmvresp = 'MG N&ordm; 19770';
                                                   }
                                                   $_figtimbrado = SolfabController::buscarCaminhoImagemTipoHeaderProduto($timbradoidempresa, true);
                                                   $_timbradocabecalho = $_figtimbrado["caminho"];
                                                }
                                                ?>

                                                <? if (!empty($_timbradocabecalho)) { ?>
                                                   <div id="_timbradocabecalho"><img src="<?=$_timbradocabecalho ?>" height="18px" width="140px"></div>
                                                <? } ?>
                                             </td>
                                             <td><?=$titulo?></td>
                                          </tr>
                                       </table>
                                    </td>
                                 </tr>
                                 <tr>
                                    <td>
                                       <table class="tsep" style="width:100%;">
                                          <tr>
                                             <td>
                                                <table class="tbgr" style="width:100%; border:1px solid #f7f7f7;">

                                                   <tr style="background-color:#f7f7f7; font-size:13px; text-transform:uppercase; height:20px;">
                                                      <td colspan="4" style="font-size:11px;">DADOS DO TEA/TRA</td>
                                                   </tr>
                                                   <tr>
                                                      <td class="tdrot grrot">N&ordm; <?=$sub ?>:</td>
                                                      <td class="tdval grval"><?=$oAm["idregistro"] ?>/<?=$oAm["exercicio"] ?></td>
                                                      <td class="tdrot grrot">Data Registro:</td>
                                                      <td class="tdval grval"><?=dmahms($oAm["dataamostrah"], true) ?></td>
                                                   </tr>
                                                </table>
                                             </td>
                                          </tr>
                                       </table>
                                    </td>
                                 </tr>
                                 <tr>
                                    <td>
                                       <table class="tsep" style="width:100%;">
                                          <tr style="width:100%;">
                                             <td>
                                                <table class="tbgr" style="width:100%; border:1px solid #f7f7f7;">
                                                   <tr style="background-color:#f7f7f7; font-size:13px; text-transform:uppercase;	height:20px;">
                                                      <td colspan="6" style="font-size:11px;">DADOS DO CLIENTE</td>
                                                   </tr>
                                                   <tr>
                                                      <td class="tdrot grrot" width="12%">Cliente:</td>
                                                      <td class="tdval grval" colspan="5"><?=$oAm["razaosocial"] ?></td>
                                                   </tr>
                                                   <tr>
                                                      <td class="tdrot grrot">Propriedade/Granja:</td>
                                                      <td class="tdval grval" colspan="5"><?=$oAm["nome"] ?></td>
                                                   </tr>
                                                   <tr>
                                                      <td class="tdrot grrot">Endereço:</td>
                                                      <td class="tdval grval" colspan="5"> 
                                                         <?
                                                         if (empty($oAm["enderecosacado"])) 
                                                         {
                                                            ?>
                                                            <div class="alert alert-warning">
                                                               <span class="notProducao"><i class="fa fa-exclamation-triangle"></i>&nbsp;Favor preencher o endereço da propriedade no cadastro do cliente!</span>
                                                            </div>
                                                            <?
                                                         } else {
                                                            echo ($oAm["enderecosacado"]);
                                                         }
                                                         ?>
                                                      </td>
                                                   </tr>
                                                   <tr>
                                                      <td class="tdrot grrot">Cnpj:</td>
                                                      <td class="tdval grval"><?=formatarCPF_CNPJ($oAm["cpfcnpj"]) ?></td>
                                                      <td class="tdrot grrot" style="width: 100px !important;">Inscr. Estadual:</td>
                                                      <td class="tdval grval" colspan="3"><?=$oAm["inscrest"] ?></td>
                                                   </tr>
                                                   <tr>
                                                      <td class="tdrot grrot">Respons. oficial:</td>
                                                      <td class="tdval grval"><?=$oAm["responsavelof"] ?></td>
                                                      <td class="tdrot grrot">CRMV:</td>
                                                      <td class="tdval grval"><?=$oAm["responsavelofcrmv"] ?></td>
                                                      <td class="tdrot grrot">Tel:</td>
                                                      <td class="tdval grval"><?=$oAm["responsaveloftel"] ?></td>
                                                   </tr>
                                                   <tr>
                                                      <td class="tdrot grrot">Nº de animais:</div>
                                                      <td class="tdval grval" colspan="5"><?=$oAm["numeroanimais"] ?></td>
                                                   </tr>

                                                </table>
                                             </td>
                                          </tr>

                                       </table>
                                    </td>
                                 </tr>
                                 <tr>
                                    <td>
                                       <table class="tsep" style="width:100%;">
                                          <tr>
                                             <td>
                                                <table class="tbgr" style="width:100%; border:1px solid #f7f7f7;">
                                                   <tr style="background-color:#f7f7f7; font-size:13px; text-transform:uppercase;	height:20px;">
                                                      <td colspan="4" style="font-size:11px;">DADOS DA AMOSTRA</td>
                                                   </tr>
                                                   <tr>
                                                      <td class="tdrot grrot">Espécie/Finalidade:</td>
                                                      <td class="tdval grval"><?=$oAm["especietipofinalidade"] ?></td>
                                                      <td class="tdrot grrot">Data Coleta:</td>
                                                      <td class="tdval grval"><?=dma($oAm["datacoleta"]) ?></td>
                                                   </tr>
                                                   <tr>
                                                      <td class="tdrot grrot">Material colhido:</td>
                                                      <td class="tdval grval"><?=$oAm["subtipoamostra"] ?></td>
                                                      <td class="tdrot grrot">Quantidade:</td>
                                                      <td class="tdval grval"><?=$oAm["nroamostra"] ?></td>

                                                   </tr>

                                                   <tr>
                                                      <td class="tdrot grrot">Descrição:</td>
                                                      <td class="tdval grval"><?=$oAm["descricao"] ?></td>
                                                      <td class="tdrot grrot">Idade:</td>
                                                      <td class="tdval grval"><?=$oAm["idade"] . " " . $oAm["tipoidade"] ?></td>
                                                   </tr>
                                                   <?
                                                   if ($oAm["nucleo"] || $oAm["lote"]) { ?>
                                                      <tr>
                                                         <td class="tdrot grrot">Núcleo:</td>
                                                         <td class="tdval grval"><?=$oAm["nucleo"] ?></td>
                                                         <td class="tdrot grrot">Lote:</td>
                                                         <td class="tdval grval"><?=$oAm["lote"] ?></td>
                                                      </tr>
                                                   <? }
                                                   if ($oAm["linha"] || $oAm["regoficial"]) {
                                                   ?>
                                                      <tr>
                                                         <td class="tdrot grrot">Linha:</td>
                                                         <td class="tdval grval"><?=$oAm["linha"] ?></td>
                                                         <td class="tdrot grrot">Nº Registro oficial:</td>
                                                         <td class="tdval grval"><?=$oAm["regoficial"] ?></td>
                                                      </tr>
                                                   <? }
                                                   if ($oAm["formaarmazen"] || $oAm["meiotransp"]) {
                                                   ?>
                                                      <tr>
                                                         <td class="tdrot grrot">Forma de armaz.:</td>
                                                         <td class="tdval grval"><?=$oAm["formaarmazen"] ?></td>
                                                         <td class="tdrot grrot">Meio de transp.:</td>
                                                         <td class="tdval grval"><?=$oAm["meiotransp"] ?></td>
                                                      </tr>
                                                   <?
                                                   }
                                                   if ($oAm["condconservacao"]) {
                                                   ?>
                                                      <tr>
                                                         <td class="tdrot grrot">Cond. conservação:</td>
                                                         <td class="tdval grval" colspan="3"><?=nl2br($oAm["condconservacao"]) ?></td>
                                                      </tr>
                                                   <?
                                                   }
                                                   if ($oAm["sexo"] || $oAm["clienteterceiro"]) {
                                                   ?>
                                                      <tr>
                                                         <td class="tdrot grrot">Sexo:</td>
                                                         <td class="tdval grval"><?=$oAm["sexo"] ?></td>
                                                         <td class="tdrot grrot">Cliente 3&ordm;:</td>
                                                         <td class="tdval grval"><?=$oAm["clienteterceiro"] ?></td>
                                                      </tr>
                                                   <?
                                                   }
                                                   if ($oAm["nucleoorigem"] || $oAm["tipo"]) {
                                                   ?>
                                                      <tr>
                                                         <td class="tdrot grrot">Núcleo origem:</td>
                                                         <td class="tdval grval"><?=$oAm["nucleoorigem"] ?></td>
                                                         <td class="tdrot grrot">Tipo:</td>
                                                         <td class="tdval grval"><?=$oAm["tipo"] ?></td>
                                                      </tr>
                                                   <?
                                                   }
                                                   if ($oAm["especificacao"] || $oAm["partida"]) {
                                                   ?>
                                                      <tr>
                                                         <td class="tdrot grrot">Especificações:</td>
                                                         <td class="tdval grval"><?=$oAm["especificacao"] ?></td>
                                                         <td class="tdrot grrot">Partida:</td>
                                                         <td class="tdval grval"><?=$oAm["partida"] ?></td>
                                                      </tr>
                                                   <?
                                                   }
                                                   if ($oAm["datafabricacao"] || $oAm["identificacaochip"]) {
                                                   ?>
                                                      <tr>
                                                         <td class="tdrot grrot">Data fabricação:</td>
                                                         <td class="tdval grval"><?=$oAm["datafabricacao"] ?></td>
                                                         <td class="tdrot grrot">Chip/Identif.:</td>
                                                         <td class="tdval grval"><?=$oAm["identificacaochip"] ?></td>
                                                      </tr>
                                                   <?
                                                   }
                                                   if ($oAm["nroplacas"] || $oAm["nrodoses"]) {
                                                   ?>
                                                      <tr>
                                                         <td class="tdrot grrot">Nº Placas:</td>
                                                         <td class="tdval grval"><?=$oAm["nroplacas"] ?></td>
                                                         <td class="tdrot grrot">Nº Doses:</td>
                                                         <td class="tdval grval"><?=$oAm["nrodoses"] ?></td>

                                                      </tr>
                                                   <?
                                                   }
                                                   if ($oAm["notafiscal"] || $oAm["vencimento"]) {
                                                   ?>
                                                      <tr>
                                                         <td class="tdrot grrot">Nota Fiscal:</td>
                                                         <td class="tdval grval"><?=$oAm["notafiscal"] ?></td>
                                                         <td class="tdrot grrot">Vencimento:</td>
                                                         <td class="tdval grval"><?=$oAm["vencimento"] ?></td>
                                                      </tr>
                                                   <?
                                                   }
                                                   if ($oAm["sexadores"] || $oAm["localexp"]) {
                                                   ?>
                                                      <tr>
                                                         <td class="tdrot grrot">Sexadores:</td>
                                                         <td class="tdval grval"><?=$oAm["sexadores"] ?></td>
                                                         <td class="tdrot grrot">Local específico:</td>
                                                         <td class="tdval grval"><?=$oAm["localexp"] ?></td>
                                                      </tr>
                                                   <?
                                                   }
                                                   if ($oAm["lacre"] || $oAm["tc"]) {
                                                   ?>
                                                      <tr>
                                                         <td class="tdrot grrot">Lacre:</td>
                                                         <td class="tdval grval"><?=$oAm["lacre"] ?></td>
                                                         <td class="tdrot grrot">Termo de coleta:</td>
                                                         <td class="tdval grval"><?=$oAm["tc"] ?></td>
                                                      </tr>
                                                   <?
                                                   }
                                                   if ($oAm["fabricante"] || $oAm["semana"]) {
                                                   ?>
                                                      <tr>
                                                         <td class="tdrot grrot">Fabricante:</td>
                                                         <td class="tdval grval"><?=$oAm["fabricante"] ?></td>
                                                         <td class="tdrot grrot">Semana:</td>
                                                         <td class="tdval grval"><?=$oAm["semana"] ?></td>
                                                      </tr>
                                                   <?
                                                   }
                                                   if ($oAm["diluicoes"] || $oAm["fornecedor"]) {
                                                   ?>
                                                      <tr>
                                                         <td class="tdrot grrot">Diluições:</td>
                                                         <td class="tdval grval"><?=$oAm["diluicoes"] ?></td>
                                                         <td class="tdrot grrot">Fornecedor:</td>
                                                         <td class="tdval grval"><?=$oAm["fornecedor"] ?></td>
                                                      </tr>
                                                   <?
                                                   }
                                                   ?>
                                                </table>
                                             </td>
                                          </tr>
                                       </table>
                                    </td>
                                 </tr>
                                 <tr>
                                    <td>
                                       <table class="tsep" style="width:100%;">
                                          <tr>
                                             <td>
                                                <table class="tbgr" style="width:100%; border:1px solid #f7f7f7;">
                                                   <tr style="background-color:#f7f7f7; font-size:13x; text-transform:uppercase;	height:20px;">
                                                      <td colspan="6" style="font-size:11px;">DADOS EPIDEMIOLÓGICOS</td>
                                                   </tr>

                                                   <tr>
                                                      <td class="tdrot grrot">Início sinais clínicos:</td>
                                                      <td class="tdval grval" colspan="5"><?=$oAm["sinaisclinicosinicio"] ?></td>
                                                   </tr>
                                                   <tr>
                                                      <td class="tdrot grrot">Sinais clínicos:</td>
                                                      <td class="tdval grval" colspan="5"><?=nl2br($oAm["sinaisclinicos"]) ?></td>
                                                   </tr>
                                                   <tr>
                                                      <td class="tdrot grrot">Achados necrópsia:</td>
                                                      <td class="tdval grval" colspan="5"><?=nl2br($oAm["achadosnecropsia"]) ?></td>
                                                   </tr>
                                                   <tr>
                                                      <td class="tdrot grrot">Suspeitas clínicas:</td>
                                                      <td class="tdval grval" colspan="5"><?=nl2br($oAm["suspclinicas"]) ?></td>
                                                   </tr>
                                                   <tr>
                                                      <td class="tdrot grrot">Histórico problema:</td>
                                                      <td class="tdval grval" colspan="5"><?=nl2br($oAm["histproblema"]) ?></td>
                                                   </tr>
                                                   <tr>
                                                      <td class="tdrot grrot">Morbidade<span class="fonte8"> (N&ordm; animais)</span>:</td>
                                                      <td class="tdval grval"><?=$oAm["morbidade"] ?></td>
                                                      <td class="tdrot grrot">Letalidade<span class="fonte8"> (N&ordm; animais)</span>:</td>
                                                      <td class="tdval grval"><?=$oAm["letalidade"] ?></td>
                                                      <td class="tdrot grrot">Mortalidade<span class="fonte8"> (N&ordm; animais)</span>:</td>
                                                      <td class="tdval grval"><?=$oAm["mortalidade"] ?></div>
                                                   </tr>
                                                   <tr>
                                                      <td class="tdrot grrot">Uso medicamentos:</td>
                                                      <td class="tdval grval" colspan="2"><?=$oAm["usomedicamentos"] ?></td>
                                                      <td class="tdrot grrot">Uso de vacinas:</td>
                                                      <td class="tdval grval" colspan="2"><?=$oAm["usovacinas"] ?></td>
                                                   </tr>
                                                   <?
                                                   if ($oAm["localcoleta"] || $oAm["responsavel"]) {
                                                   ?>
                                                      <tr>
                                                         <td class="tdrot grrot">Local coleta:</td>
                                                         <td class="tdval grval" colspan="5"><?=$oAm["localcoleta"] ?></td>
                                                      </tr>
                                                      <tr>
                                                         <td class="tdrot grrot">Respons. Coleta:</td>
                                                         <td class="tdval grval"><?=$oAm["responsavel"] ?></td>
                                                         <td class="tdrot grrot">CRMV:</td>
                                                         <td class="tdval grval"><?=$oAm["responsavelcolcrmv"] ?></td>
                                                         <td class="tdrot grrot">Tel:</td>
                                                         <td class="tdval grval"><?=$oAm["responsavelcolcont"] ?></td>
                                                      </tr> <?
                                                         }
                                                            ?>
                                                   <? //LTM - 23-10-2020: Retirado o comentário a pedido do Igor (379058) 
                                                   ?>
                                                   <tr>
                                                      <td class="tdrot grrot">Observação:</td>
                                                      <td class="tdval grval" colspan="5" style="word-break: break-all;"><?=nl2br(espaco2nbsp($oAm["observacao"])) ?></td>
                                                   </tr>

                                                </table>
                                             </td>
                                          </tr>
                                       </table>
                                    </td>
                                 </tr>
                                 <?
                                 $_listaAmostras = SolfabController::buscarAmostras($rowt['idamostra']);
                                 $qtdexames = count($_listaAmostras);
                                 if ($qtdexames > 29) 
                                 {
                                    ?>
                                    </table>
                                 </td>
                              </tr>
                           </table>
                        </td>
                     </tr>
                  </table>
                  <div style="page-break-before:always;display:none"></div>
                  <table style="width:700px; margin:auto;display:none ">
                     <tr>
                        <td style="width: 100%">
                           <table style="width: 100%">
                              <tr>
                                 <td>
                                    <table class="tsep" style="width:100%; margin-top:6px;">
                                       <tr>
                                          <td style=" font-size:13px;">
                                             <table class="tsep" style="width:100%;">
                                                <tr>
                                                   <td><? if (!empty($_timbradocabecalho)) { ?>
                                                         <div id="_timbradocabecalho"><img src="<?=$_timbradocabecalho ?>" height="18px" width="140px"></div>
                                                      <? } ?>
                                                   </td>
                                                   <td><?=$titulo ?></td>
                                                </tr>
                                             </table>
                                          </td>
                                       </tr>
                              <?
                              } //if($qtdexames>29){
                              ?>

                              <tr>
                                 <td>
                                    <table class="tsep" style="width:100%;">
                                       <tr>
                                          <td>
                                             <table class="tbgr" style="width:100%; border:1px solid #f7f7f7;">
                                                <tr style="background-color:#f7f7f7; font-size:13px; text-transform:uppercase; height:20px;">
                                                   <td colspan="2" style="font-size:11px;">EXAME(S) SOLICITADO(S)</td>
                                                </tr>
                                                <tr style="font-size:13px;">
                                                   <td class="tdtit grrot" style="max-width: 30px;">N&ordm; LDA</td>
                                                   <td class="tdtit grrot">TESTE</td>

                                                </tr>
                                                <?
                                                $l = 0;
                                                foreach ($_listaAmostras as $row) 
                                                {
                                                   $l = $l + 1;
                                                   if ($l == 61) 
                                                   {
                                                      $l = 0;
                                                   ?>
                                                </table>
                                             </td>
                                          </tr>
                                       </table>
                                    </td>
                                 </tr>
                                 </table>
                              </td>
                           </tr>
                        </table>
                     </td>
                  </tr>
               </table>
               <div style="page-break-before:always;"></div>
               <table style="width:700px; margin:auto;display:none; ">
                  <tr>
                     <td style="width: 100%">
                        <table style="width: 100%">
                           <tr>
                              <td>
                                 <table class="tsep" style="width:100%; margin-top:6px;">
                                    <tr>
                                       <td style=" font-size:13px;">
                                          <table class="tsep" style="width:100%;">
                                             <tr>
                                                <td><? if (!empty($_timbradocabecalho)) { ?>
                                                      <div id="_timbradocabecalho"><img src="<?=$_timbradocabecalho ?>" height="18px" width="140px"></div>
                                                   <? } ?>
                                                </td>
                                                <td><?=$titulo ?></td>
                                             </tr>
                                          </table>
                                       </td>
                                    </tr>
                                    <tr>
                                       <td>
                                          <table class="tsep" style="width:100%;">
                                             <tr>
                                                <td>
                                                   <table class="tbgr" style="width:100%; border:1px solid #f7f7f7;">
                                                      <tr style="background-color:#f7f7f7; font-size:13px; text-transform:uppercase; height:20px;">
                                                         <td colspan="2" style="font-size:11px;">EXAME(S) SOLICITADO(S)</td>
                                                      </tr>
                                                      <tr style="font-size:13px;">
                                                         <td class="tdtit grrot" style="max-width: 30px;">N&ordm; LDA</td>
                                                         <td class="tdtit grrot">TESTE</td>

                                                      </tr>
                                                      <?
                                                   } //if($l==61)
                                                   ?>
                                                   <tr>
                                                      <td class="tdval grval" style="max-width: 30px;"><?=$row["idresultado"] ?></td>
                                                      <td class="tdval grval"><?=$row["descr"] ?></td>

                                                   </tr>
                                                   <?
                                                } // while($row=mysqli_fetch_assoc($_listaAmostras)){ 
                                                ?>
                                                </table>
                                             </td>
                                          </tr>
                                       </table>
                                    </td>
                                 </tr>
                              </table>
                           </td>
                        </tr>
                     </table>
                  </td>
               </tr>
               <tr>
                  <td style="height:20px;"></td>
               </tr>
               <tr>
                  <td style="width: 100%">
                     <table style="width: 100%">
                        <tr>
                           <td>
                              <table class="tsep" style="width:100%; margin-top:6px;">
                                 <tr>
                                    <td style="font-size:13px;" class="nowrap">
                                       <table>
                                          <?
                                          if ($oAm["status"] == "ASSINADO") 
                                          {                        
                                             $listarPessoaAssinatura = SolfabController::buscarPessoaAssinaturaStatusAssinado('amostratra', $oAm["idamostra"]);
                                             foreach($listarPessoaAssinatura as $assinatura) {
                                                $nomresp = "";
                                                $crmvresp = "";
                                                $arrayPessoaAssinatura = [782, 797, 5655, 1098];
                                                if(!in_array($assinatura["idpessoa"], $arrayPessoaAssinatura)) {
                                                   $assinatura["idpessoa"] = 797;
                                                }

                                                if ($oAm["dataamostra"] >= '2021-05-18' && ($oAm["idempresa"] == 1 || $oAm["idempresa"] == 2)) {
                                                   if ($rowass["idpessoa"] == 797) {
                                                      $rowass["idpessoa"] = 5655;
                                                   } elseif ($rowass["idpessoa"] == 782) {
                                                      $rowass["idpessoa"] = 1098;
                                                   } else {
                                                      $rowass["idpessoa"] = 5655;
                                                   }
                                                }
                                                $respidpessoa = $rowass["idpessoa"];
                                                //troca dados do responsavel via hardcode
                                                switch ($rowass["idpessoa"]) {
                                                   case 782: //edison
                                                      $nomresp = "Edison Rossi";
                                                      $crmvresp = "MG N&ordm; 1626";
                                                      break;
                                                   case 797: //marcio
                                                      $nomresp = "Marcio Danilo Botrel Coutinho";
                                                      $crmvresp = "MG N&ordm; 1454";
                                                      break;
                                                   case 5655: //marcio
                                                      $nomresp = "José Renato de O. Branco";
                                                      $crmvresp = "MG N&ordm; 19770";
                                                      break;
                                                   case 1098: //marcio
                                                      $nomresp = "Hermes Pedro";
                                                      $crmvresp = "MG N&ordm; 20412";
                                                      break;
                                                   case 97118: //marcio
                                                      $nomresp = "Ana Paula Mori";
                                                      $crmvresp = "MG N&ordm; 20758";
                                                      break;
                                                   default:
                                                      $nomresp = "Marcio Danilo Botrel Coutinho";
                                                      $crmvresp = "MG N&ordm; 1454";
                                                      break;
                                                }
                                             }
                                             ?>
                                             <tr>
                                                <td>Técnico Resp.:</td>
                                                <td><?=$nomresp ?></td>
                                                <td>CRMV:</td>
                                                <td><?=$crmvresp ?></td>
                                                <td>Assinatura.:</td>
                                                <td><img style=" height: 30px;" src="../inc/img/sig<?=strtolower(trim($respidpessoa)) ?>.gif"></td>
                                             </tr>
                                          <? 
                                          } else {
                                             if ($oAm["responsavelof"] || $oAm["responsavelofcrmv"] || $oAm["responsaveloftel"]) 
                                             {
                                                ?>
                                                <tr>
                                                   <td>Respons. oficial:</td>
                                                   <td><?=$oAm["responsavelof"] ?></td>
                                                   <td>CRMV:</td>
                                                   <td><?=$oAm["responsavelofcrmv"] ?></td>
                                                   <td>Tel:</td>
                                                   <td><?=$oAm["responsaveloftel"] ?></td>
                                                </tr>
                                                <?
                                             } //if($oAm["responsavelof"] || $oAm["responsavelofcrmv"] || $oAm["responsaveloftel"]){
                                             ?>
                                             <tr>
                                                <td style="height:20px;"></td>
                                             </tr>
                                             <!-- campos pontilhados para preenchimento manual -->
                                             <tr>
                                                <td>Assinatura.:</td>
                                                <td>..............................................................................</td>
                                                <td></td>
                                                <td>Data:</td>
                                                <td colspan="3">...........................................................</td>
                                             </tr>
                                          <? } ?>

                                       </table>
                                    </td>
                                 </tr>
                              </table>
                           </td>
                        </tr>
                     </table>
                  </td>
               </tr>
            </table>
            <div style="page-break-before:always;"></div>
            <table style="width:700px; margin:auto; ">
               <tr>
                  <td style="width: 100%">
                     <table style="width: 100%">
                        <tr>
                           <td>
                              <table class="tsep cabecalhoimpressao" style="width:100%; margin-top:6px;">
                                 <tr>
                                    <td style=" font-size:13px;">
                                       <table class="tsep" style="width:100%;">
                                          <tr>
                                             <td class="wd-10"><? if (!empty($_timbradocabecalho)) { ?>
                                                   <div id="_timbradocabecalho"><img src="<?=$_timbradocabecalho ?>" height="18px" width="140px"></div>
                                                <? } ?>
                                             </td>
                                             <td class="pd-left">RESUMO <?=$titulo; ?> - LDA</td>
                                          </tr>
                                       </table>
                                    </td>
                                 </tr>
                              </table>
                           </td>
                        </tr>
                     </table>
                  </td>
               </tr>

               <!-- Controle Impressao -->
               <tr>
                  <td style="width: 100%">
                     <table style="width: 100%">
                        <tr>
                           <td>
                              <table class="tsep" style="width:100%; margin-top:6px;">
                                 <tr>
                                    <td>
                                       <table class="tsep" style="width:100%;">
                                          <tr>
                                             <td>
                                                <table class="tbgr" style="width:100%; border:1px solid #f7f7f7;">
                                                   <tr style="background-color:#f7f7f7; font-size:13x; text-transform:uppercase;	height:20px;">
                                                      <td colspan="4" style="font-size:11px;">DADOS DO TEA/TRA</td>
                                                   </tr>
                                                   <tr>
                                                      <td style="width:12% !important;" class="tdrot grrot">TEA/TRA:</td>
                                                      <td style="width:38% !important;" class="tdval grval"><?=$idregistro; ?>/<?=$exercicio; ?></td>

                                                      <td style="width:12% !important;" class="tdrot grrot">Data Registro:</td>
                                                      <td style="width:38% !important;" class="tdval grval"><?=$dataamostra; ?></td>
                                                   </tr>
                                                </table>
                                             </td>
                                          </tr>
                                       </table>
                                    </td>
                                 </tr>
                                 <tr>
                                    <td>
                                       <table class="tsep" style="width:100%;">
                                          <tr>
                                             <td>
                                                <table class="tbgr" style="width:100%; border:1px solid #f7f7f7;">

                                                   <tr style="background-color:#f7f7f7; font-size:13px; text-transform:uppercase;	height:20px;">
                                                      <td colspan="6" style="font-size:11px;">DADOS DO CLIENTE</td>
                                                   </tr>
                                                   <tr>
                                                      <td class="tdrot grrot">Cliente:</td>
                                                      <td class="tdval grval" colspan="5"><?=$oAm["razaosocial"] ?></td>
                                                   </tr>
                                                   <tr>
                                                      <td class="tdrot grrot">Propriedade/Granja:</td>
                                                      <td class="tdval grval" colspan="5"><?=$oAm["nome"] ?></td>
                                                   </tr>
                                                   <tr>
                                                      <td class="tdrot grrot">Endereço:</td>
                                                      <td class="tdval grval" colspan="5"> 
                                                         <?
                                                         if (empty($oAm["enderecosacado"])) 
                                                         {
                                                            ?>
                                                            <div class="alert alert-warning">
                                                               <span class="notProducao"><i class="fa fa-exclamation-triangle"></i>&nbsp;Favor preencher o endereço da propriedade no cadastro do cliente!</span>
                                                            </div>
                                                            <?
                                                         } else {
                                                            echo ($oAm["enderecosacado"]);
                                                         }
                                                         ?>
                                                      </td>
                                                   </tr>
                                                   <tr>
                                                      <td class="tdrot grrot">Cnpj:</td>
                                                      <td class="tdval grval" colspan="2"><?=formatarCPF_CNPJ($oAm["cpfcnpj"]) ?></td>
                                                      <td class="tdrot grrot" style="width: 100px !important;">Inscr. Estadual:</td>
                                                      <td class="tdval grval" colspan="2"><?=$oAm["inscrest"] ?></td>
                                                   </tr>
                                                   <tr>
                                                      <td class="tdrot grrot">Respons. oficial:</td>
                                                      <td class="tdval grval"><?=$oAm["responsavelof"] ?></td>
                                                      <td class="tdrot grrot">CRMV:</td>
                                                      <td class="tdval grval"><?=$oAm["responsavelofcrmv"] ?></td>
                                                      <td class="tdrot grrot">Tel:</td>
                                                      <td class="tdval grval"><?=$oAm["responsaveloftel"] ?></td>
                                                   </tr>
                                                   <tr>
                                                      <td class="tdrot grrot">Nº de animais:</div>
                                                      <td class="tdval grval" colspan="5"><?=$oAm["numeroanimais"] ?></td>
                                                   </tr>
                                                </table>
                                             </td>
                                          </tr>
                                       </table>
                                    </td>
                                 </tr>
                                 <tr>
                                    <td>
                                       <table class="tsep" style="width:100%;">
                                          <tr>
                                             <td>
                                                <table class="tbgr" style="width:100%; border:1px solid #f7f7f7;">
                                                   <tr style="background-color:#f7f7f7; font-size:13px; text-transform:uppercase;	height:20px;">
                                                      <td colspan="4" style="font-size:11px;">DADOS DA AMOSTRA</td>
                                                   </tr>
                                                   <tr>
                                                      <td class="tdrot grrot">Espécie/Finalidade:</td>
                                                      <td class="tdval grval"><?=$oAm["especietipofinalidade"] ?></td>
                                                      <td class="tdrot grrot">Data Coleta:</td>
                                                      <td class="tdval grval"><?=dma($oAm["datacoleta"]) ?></td>
                                                   </tr>
                                                   <tr>
                                                      <td class="tdrot grrot">Material colhido:</td>
                                                      <td class="tdval grval"><?=$oAm["subtipoamostra"] ?></td>
                                                      <td class="tdrot grrot">Quantidade:</td>
                                                      <td class="tdval grval"><?=$oAm["nroamostra"] ?></td>

                                                   </tr>
                                                   <tr>
                                                      <td class="tdrot grrot">Descrição:</td>
                                                      <td class="tdval grval"><?=$oAm["descricao"] ?></td>
                                                      <td class="tdrot grrot">Idade:</td>
                                                      <td class="tdval grval"><?=$oAm["idade"] . " " . $oAm["tipoidade"] ?></td>
                                                   </tr>
                                                   <? if ($oAm["nucleo"] || $oAm["lote"]) { ?>
                                                      <tr>
                                                         <td class="tdrot grrot">Núcleo:</td>
                                                         <td class="tdval grval"><?=$oAm["nucleo"] ?></td>
                                                         <td class="tdrot grrot">Lote:</td>
                                                         <td class="tdval grval"><?=$oAm["lote"] ?></td>
                                                      </tr>
                                                   <? }
                                                   if ($oAm["linha"] || $oAm["regoficial"]) {
                                                   ?>
                                                      <tr>
                                                         <td class="tdrot grrot">Linha:</td>
                                                         <td class="tdval grval"><?=$oAm["linha"] ?></td>
                                                         <td class="tdrot grrot">Nº Registro oficial:</td>
                                                         <td class="tdval grval"><?=$oAm["regoficial"] ?></td>
                                                      </tr>
                                                   <? }
                                                   if ($oAm["formaarmazen"] || $oAm["meiotransp"]) {
                                                   ?>
                                                      <tr>
                                                         <td class="tdrot grrot">Forma de armaz.:</td>
                                                         <td class="tdval grval"><?=$oAm["formaarmazen"] ?></td>
                                                         <td class="tdrot grrot">Meio de transp.:</td>
                                                         <td class="tdval grval"><?=$oAm["meiotransp"] ?></td>
                                                      </tr>
                                                   <?
                                                   }
                                                   if ($oAm["condconservacao"]) {
                                                   ?>
                                                      <tr>
                                                         <td class="tdrot grrot">Cond. conservação:</td>
                                                         <td class="tdval grval" colspan="3"><?=nl2br($oAm["condconservacao"]) ?></td>
                                                      </tr>
                                                   <?
                                                   }
                                                   if ($oAm["sexo"] || $oAm["clienteterceiro"]) {
                                                   ?>
                                                      <tr>
                                                         <td class="tdrot grrot">Sexo:</td>
                                                         <td class="tdval grval"><?=$oAm["sexo"] ?></td>
                                                         <td class="tdrot grrot">Cliente 3&ordm;:</td>
                                                         <td class="tdval grval"><?=$oAm["clienteterceiro"] ?></td>
                                                      </tr>
                                                   <?
                                                   }
                                                   if ($oAm["nucleoorigem"] || $oAm["tipo"]) {
                                                   ?>
                                                      <tr>
                                                         <td class="tdrot grrot">Núcleo origem:</td>
                                                         <td class="tdval grval"><?=$oAm["nucleoorigem"] ?></td>
                                                         <td class="tdrot grrot">Tipo:</td>
                                                         <td class="tdval grval"><?=$oAm["tipo"] ?></td>
                                                      </tr>
                                                   <?
                                                   }
                                                   if ($oAm["especificacao"] || $oAm["partida"]) {
                                                   ?>
                                                      <tr>
                                                         <td class="tdrot grrot">Especificações:</td>
                                                         <td class="tdval grval"><?=$oAm["especificacao"] ?></td>
                                                         <td class="tdrot grrot">Partida:</td>
                                                         <td class="tdval grval"><?=$oAm["partida"] ?></td>
                                                      </tr>
                                                   <?
                                                   }
                                                   if ($oAm["datafabricacao"] || $oAm["identificacaochip"]) {
                                                   ?>
                                                      <tr>
                                                         <td class="tdrot grrot">Data fabricação:</td>
                                                         <td class="tdval grval"><?=$oAm["datafabricacao"] ?></td>
                                                         <td class="tdrot grrot">Chip/Identif.:</td>
                                                         <td class="tdval grval"><?=$oAm["identificacaochip"] ?></td>
                                                      </tr>
                                                   <?
                                                   }
                                                   if ($oAm["nroplacas"] || $oAm["nrodoses"]) {
                                                   ?>
                                                      <tr>
                                                         <td class="tdrot grrot">Nº Placas:</td>
                                                         <td class="tdval grval"><?=$oAm["nroplacas"] ?></td>
                                                         <td class="tdrot grrot">Nº Doses:</td>
                                                         <td class="tdval grval"><?=$oAm["nrodoses"] ?></td>

                                                      </tr>
                                                   <?
                                                   }
                                                   if ($oAm["notafiscal"] || $oAm["vencimento"]) {
                                                   ?>
                                                      <tr>
                                                         <td class="tdrot grrot">Nota Fiscal:</td>
                                                         <td class="tdval grval"><?=$oAm["notafiscal"] ?></td>
                                                         <td class="tdrot grrot">Vencimento:</td>
                                                         <td class="tdval grval"><?=$oAm["vencimento"] ?></td>
                                                      </tr>
                                                   <?
                                                   }
                                                   if ($oAm["sexadores"] || $oAm["localexp"]) {
                                                   ?>
                                                      <tr>
                                                         <td class="tdrot grrot">Sexadores:</td>
                                                         <td class="tdval grval"><?=$oAm["sexadores"] ?></td>
                                                         <td class="tdrot grrot">Local específico:</td>
                                                         <td class="tdval grval"><?=$oAm["localexp"] ?></td>
                                                      </tr>
                                                   <?
                                                   }
                                                   if ($oAm["lacre"] || $oAm["tc"]) {
                                                   ?>
                                                      <tr>
                                                         <td class="tdrot grrot">Lacre:</td>
                                                         <td class="tdval grval"><?=$oAm["lacre"] ?></td>
                                                         <td class="tdrot grrot">Termo de coleta:</td>
                                                         <td class="tdval grval"><?=$oAm["tc"] ?></td>
                                                      </tr>
                                                   <?
                                                   }
                                                   if ($oAm["fabricante"] || $oAm["semana"]) {
                                                   ?>
                                                      <tr>
                                                         <td class="tdrot grrot">Fabricante:</td>
                                                         <td class="tdval grval"><?=$oAm["fabricante"] ?></td>
                                                         <td class="tdrot grrot">Semana:</td>
                                                         <td class="tdval grval"><?=$oAm["semana"] ?></td>
                                                      </tr>
                                                   <?
                                                   }
                                                   if ($oAm["diluicoes"] || $oAm["fornecedor"]) {
                                                   ?>
                                                      <tr>
                                                         <td class="tdrot grrot">Diluições:</td>
                                                         <td class="tdval grval"><?=$oAm["diluicoes"] ?></td>
                                                         <td class="tdrot grrot">Fornecedor:</td>
                                                         <td class="tdval grval"><?=$oAm["fornecedor"] ?></td>
                                                      </tr>
                                                   <?
                                                   }
                                                   ?>
                                                </table>
                                             </td>
                                          </tr>
                                       </table>
                                    </td>
                                 </tr>
                                 <tr>
                                    <td>
                                       <table class="tsep" style="width:100%;">
                                          <tr>
                                             <td>
                                                <table class="tbgr" style="width:100%; border:1px solid #f7f7f7;">
                                                   <tr style="background-color:#f7f7f7; font-size:13px; text-transform:uppercase;	height:20px;">
                                                      <td colspan="6" style="font-size:11px;">DADOS EPIDEMIOLÓGICOS</td>
                                                   </tr>

                                                   <tr>
                                                      <td class="tdrot grrot">Início sinais clínicos:</td>
                                                      <td class="tdval grval" colspan="5"><?=$oAm["sinaisclinicosinicio"] ?></td>
                                                   </tr>
                                                   <tr>
                                                      <td class="tdrot grrot">Sinais clínicos:</td>
                                                      <td class="tdval grval" colspan="5"><?=nl2br($oAm["sinaisclinicos"]) ?></td>
                                                   </tr>
                                                   <tr>
                                                      <td class="tdrot grrot">Achados necrópsia:</td>
                                                      <td class="tdval grval" colspan="5"><?=nl2br($oAm["achadosnecropsia"]) ?></td>
                                                   </tr>
                                                   <tr>
                                                      <td class="tdrot grrot">Suspeitas clínicas:</td>
                                                      <td class="tdval grval" colspan="5"><?=nl2br($oAm["suspclinicas"]) ?></td>
                                                   </tr>
                                                   <tr>
                                                      <td class="tdrot grrot">Histórico problema:</td>
                                                      <td class="tdval grval" colspan="5"><?=nl2br($oAm["histproblema"]) ?></td>
                                                   </tr>
                                                   <tr>
                                                      <td class="tdrot grrot">Morbidade<span class="fonte8"> (N&ordm; animais)</span>:</td>
                                                      <td class="tdval grval"><?=$oAm["morbidade"] ?></td>
                                                      <td class="tdrot grrot">Letalidade<span class="fonte8"> (N&ordm; animais)</span>:</td>
                                                      <td class="tdval grval"><?=$oAm["letalidade"] ?></td>
                                                      <td class="tdrot grrot">Mortalidade<span class="fonte8"> (N&ordm; animais)</span>:</td>
                                                      <td class="tdval grval"><?=$oAm["mortalidade"] ?></div>
                                                   </tr>
                                                   <tr>
                                                      <td class="tdrot grrot">Uso medicamentos:</td>
                                                      <td class="tdval grval" colspan="2"><?=$oAm["usomedicamentos"] ?></td>
                                                      <td class="tdrot grrot">Uso de vacinas:</td>
                                                      <td class="tdval grval" colspan="2"><?=$oAm["usovacinas"] ?></td>
                                                   </tr>
                                                   <?
                                                   if ($oAm["localcoleta"] || $oAm["responsavel"]) {
                                                   ?>
                                                      <tr>
                                                         <td class="tdrot grrot">Local coleta:</td>
                                                         <td class="tdval grval" colspan="5"><?=$oAm["localcoleta"] ?></td>
                                                      </tr>
                                                      <tr>
                                                         <td class="tdrot grrot">Respons. Coleta:</td>
                                                         <td class="tdval grval"><?=$oAm["responsavel"] ?></td>
                                                         <td class="tdrot grrot">CRMV:</td>
                                                         <td class="tdval grval"><?=$oAm["responsavelcolcrmv"] ?></td>
                                                         <td class="tdrot grrot">Tel:</td>
                                                         <td class="tdval grval"><?=$oAm["responsavelcolcont"] ?></td>
                                                      </tr>
                                                   <?
                                                   }
                                                   ?>
                                                   <tr>
                                                      <td class="tdrot grrot">Observação:</td>
                                                      <td class="tdval grval" colspan="5" style="word-break: break-all;"><?=nl2br(espaco2nbsp($oAm["observacao"])) ?></td>
                                                   </tr>
                                                </table>
                                             </td>
                                          </tr>
                                       </table>
                                    </td>
                                 </tr>
                                 <?
                                 $_listaAmostras = SolfabController::buscarAmostras($rowt['idamostra']);
                                 $qtdexames = count($_listaAmostras);
                                 if ($qtdexames > 29) 
                                 {
                                    ?>
                                    </table>
                                 </td>
                              </tr>
                           </table>
                        </td>
                     </tr>
                  </table>

                  <div style="page-break-before:always;"></div>
                  <table style="width:700px; margin:auto; ">
                     <tr>
                        <td style="width: 100%">
                           <table style="width: 100%">
                              <tr>
                                 <td>
                                    <table class="tsep cabecalhoimpressao" style="width:100%; margin-top:6px;">
                                       <tr>
                                          <td style=" font-size:13px;">
                                             <table class="tsep" style="width:100%;">
                                                <tr>
                                                   <td class="wd-10">
                                                      <? if (!empty($_timbradocabecalho)) { ?>
                                                         <div id="_timbradocabecalho"><img src="<?=$_timbradocabecalho ?>" height="18px" width="140px"></div>
                                                      <? } ?>
                                                   </td>
                                                   <td class="pd-left">RESUMO TRA - LDA
                                                   </td>
                                                </tr>
                                             </table>
                                          </td>
                                       </tr>
                                    <?
                                 } //if($qtdexames>29){
                              ?>
                              <tr>
                                 <td>
                                    <table class="tsep" style="width:100%;">
                                       <tr>
                                          <td>
                                             <table class="tbgr" style="width:100%; border:1px solid #f7f7f7;">
                                                <tr style="background-color:#f7f7f7; font-size:13px; text-transform:uppercase; height:20px;">
                                                   <td colspan="7" style="font-size:11px;">RESULTADO DO(S) EXAME(S) </td>
                                                </tr>
                                                <tr style="font-size:13px;">
                                                   <td class="tdtit grrot" style="max-width: 20px;">N&ordm; LDA</td>
                                                   <td class="tdtit grrot">TESTE</td>
                                                   <td class="tdtit grrot">INICIO</td>
                                                   <td class="tdtit grrot">FIM</td>
                                                   <td class="tdtit grrot" style="max-width: 60px;display:none">AMOSTRA</td>
                                                   <td class="tdtit grrot" style="width: 80px;">RESULTADO</td>
                                                   <td class="tdtit grrot" style="max-width: 30px;">SEMENTE</td>
                                                </tr>
                                                <?
                                                $l = 0;
                                                $n = 0;
                                                $pos = 0;
                                                foreach($_listaAmostras as $row) 
                                                {
                                                   $n = $n + 1;
                                                   if ($row["status"] != "ASSINADO") {
                                                      $strp = 'EM ANDAMENTO';
                                                      $td = "tdval";
                                                      $partida = "";
                                                      $dataass = "";
                                                   } elseif ($row["alerta"] == "Y") {
                                                      $strp = 'POSITIVO';
                                                      $td = "tdtit";
                                                      $pos = $pos + 1;
                                                      $rese = SolfabController::buscarLotePorIdObjetoSoliPor($row['idresultado'], 'resultado');
                                                      $partida = "";
                                                      $dataass = $row["dataass"];
                                                      foreach($rese as $rowe) {
                                                         $partida .= $rowe["partida"] . " ";
                                                      }
                                                   } else {
                                                      $strp = 'NEGATIVO';
                                                      $td = "tdval";
                                                      $partida = "";
                                                      $dataass = $row["dataass"];
                                                   }

                                                   $l = $l + 1;
                                                   if ($l == 61) {
                                                      $l = 0;
                                                ?>
                                             </table>
                                          </td>
                                       </tr>
                                    </table>
                                 </td>
                              </tr>
                              </table>
                           </td>
                        </tr>
                     </table>
                  </td>
               </tr>
            </table>
            <div style="page-break-before:always;"></div>
            <table style="width:700px; margin:auto; ">
               <tr>
                  <td style="width: 100%">
                     <table style="width: 100%">
                        <tr>
                           <td>
                              <table class="tsep cabecalhoimpressao" style="width:100%; margin-top:6px;">
                                 <tr>
                                    <td style=" font-size:13px;">
                                       <table class="tsep" style="width:100%;">
                                          <tr>
                                             <td class="wd-10">
                                                <? if (!empty($_timbradocabecalho)) { ?>
                                                   <div id="_timbradocabecalho"><img src="<?=$_timbradocabecalho ?>" height="18px" width="140px"></div>
                                                <? } ?>
                                             </td>
                                             <td class="pd-left">RESUMO TRA - LDA</td>
                                          </tr>
                                       </table>
                                    </td>
                                 </tr>
                                 <tr>
                                    <td>
                                       <table class="tsep" style="width:100%;">
                                          <tr>
                                             <td>
                                                <table class="tbgr" style="width:100%; border:1px solid #f7f7f7;">
                                                   <tr style="background-color:#f7f7f7; font-size:13px; text-transform:uppercase; height:20px;">
                                                      <td colspan="7" style="font-size:11px;">RESULTADO DO(S) EXAME(S) </td>
                                                   </tr>
                                                   <tr style="font-size:13px;">
                                                      <td class="tdtit grrot" style="max-width: 20px;">N&ordm; LDA</td>
                                                      <td class="tdtit grrot">TESTE</td>
                                                      <td class="tdtit grrot">INICIO</td>
                                                      <td class="tdtit grrot">FIM</td>
                                                      <td class="tdtit grrot" style="max-width: 60px;display:none">AMOSTRA</td>
                                                      <td class="tdtit grrot" style="width: 80px;">RESULTADO</td>
                                                      <td class="tdtit grrot" style="max-width: 30px;">SEMENTE</td>
                                                   </tr>
                                                   <?
                                                   } //if($l==61)
                                                   ?>
                                                   <tr>
                                                      <td class="<?=$td ?> grval"><?=$row["idresultado"] ?></td>
                                                      <td class="<?=$td ?> grval"><?=$row["descr"] ?></td>
                                                      <td class="<?=$td ?> grval"><?=$row["dataamostra"] ?></td>
                                                      <td class="<?=$td ?> grval"><?=$dataass ?></td>
                                                      <td class="<?=$td ?> grval" style="display:none"><?=$row["subtipoamostra"] ?></td>
                                                      <td class="<?=$td ?> grval nowrap"><?=$strp ?></td>
                                                      <td class="<?=$td ?> grval"><?=$partida ?></td>
                                                   </tr>
                                                <?
                                                }
                                                ?>
                                                <tr>
                                                   <td colspan="4"><b>TOTAL</b></td>
                                                   <td><b><?=$n ?> TESTE(S)</b></td>
                                                   <td><b><?=$pos ?> POSITIVO(S)</b></td>
                                                   <td> </td>
                                                </tr>
                                             </table>
                                             </td>
                                          </tr>
                                       </table>
                                    </td>
                                 </tr>
                              </table>
                           </td>
                        </tr>
                     </table>
                  </td>
               </tr>
               <tr>
                  <td style="height:20px;"></td>
               </tr>
               <tr>
                  <td style="width: 100%">
                     <table style="width: 100%">
                        <tr>
                           <td>
                              <table class="tsep" style="width:100%; margin-top:6px;">
                                 <tr>
                                    <td style="font-size:13px;" class="nowrap">
                                       <table>

                                          <?
                                          if ($oAm["status"] == "ASSINADO") 
                                          {
                                             $listarPessoaAssinaturaImpressao = SolfabController::buscarPessoaAssinaturaStatusAssinado('amostratra' , $oAm["idamostra"]);                    
                                             foreach($listarPessoaAssinaturaImpressao as $assinaturaImpressao) 
                                             {
                                                $nomresp = "";
                                                $crmvresp = "";
                                                $arrayPessoaAssinaturaImpressao = [782, 797, 5655, 1098];
                                                if(!in_array($assinaturaImpressao["idpessoa"], $arrayPessoaAssinaturaImpressao)) {
                                                   $assinaturaImpressao["idpessoa"] = 797;
                                                }
                                                if ($oAm["dataamostra"] >= '2021-05-18' &&  ($oAm["idempresa"] == 1 || $oAm["idempresa"] == 2)) {
                                                   if ($rowass["idpessoa"] == 797) {
                                                      $rowass["idpessoa"] = 5655;
                                                   } elseif ($rowass["idpessoa"] == 782) {
                                                      $rowass["idpessoa"] = 1098;
                                                   } else {
                                                      $rowass["idpessoa"] = 5655;
                                                   }
                                                }

                                                $respidpessoa = $rowass["idpessoa"];
                                                //troca dados do responsavel via hardcode
                                                switch ($rowass["idpessoa"]) {
                                                   case 782: //edison
                                                      $nomresp = "Edison Rossi";
                                                      $crmvresp = "MG N&ordm; 1626";
                                                      break;
                                                   case 797: //marcio
                                                      $nomresp = "Marcio Danilo Botrel Coutinho";
                                                      $crmvresp = "MG N&ordm; 1454";
                                                      break;
                                                   case 5655: //marcio
                                                      $nomresp = "José Renato de O. Branco";
                                                      $crmvresp = "MG N&ordm; 19770";
                                                      break;
                                                   case 1098: //marcio
                                                      $nomresp = "Hermes Pedro";
                                                      $crmvresp = "MG N&ordm; 20412";
                                                      break;
                                                   case 97118: //marcio
                                                      $nomresp = "Ana Paula Mori";
                                                      $crmvresp = "MG N&ordm; 20758";
                                                      break;
                                                   default:
                                                      $nomresp = "Marcio Danilo Botrel Coutinho";
                                                      $crmvresp = "MG N&ordm; 1454";
                                                      break;
                                                }
                                             }
                                             ?>
                                             <tr>
                                                <td>Técnico Resp.:</td>
                                                <td><?=$nomresp ?></td>
                                                <td>CRMV:</td>
                                                <td><?=$crmvresp ?></td>
                                                <td>Assinatura.:</td>
                                                <td><img style=" height: 30px; " src='../inc/img/sig<?=strtolower(trim($respidpessoa)) ?>.gif'></td>
                                             </tr>

                                          <? } ?>
                                       </table>
                                    </td>
                                 </tr>
                              </table>
                           </td>
                        </tr>
                     </table>
                  </td>
               </tr>
            </table>
            <?
            reset($oRes);
            foreach($oRes as $k => $v) 
            {
               //Alteração para mostrar os LDA's que estão NEGATIVOS
               $listarResultados = SolfabController::buscarLotePorResultado($v["idresultado"]);
               $qtdsem = count($listarResultados);

               if ($qtdsem > 0) 
               {
                  ?>
                  <div style="page-break-before:always;"></div>
                  <table style="width:700px; margin:auto; ">
                     <tr>
                        <td style="width: 100%">
                           <table style="width: 100%">
                              <tr>
                                 <td>
                                    <table class="tsep cabecalhoimpressao" style="width:100%; margin-top:6px;">
                                       <tr>
                                          <td style=" font-size:13px;">
                                             <table class="tsep" style="width:100%;">
                                                <tr>
                                                   <td class="wd-10">
                                                      <? if (!empty($_timbradocabecalho)) { ?>
                                                         <div id="_timbradocabecalho"><img src="<?=$_timbradocabecalho ?>" height="18px" width="140px"></div>
                                                      <? } ?>
                                                   </td>
                                                   <td class="pd-left">LAUDO DIAGNÓSTICO AUTÓGENA</td>
                                                </tr>
                                             </table>
                                          </td>
                                       </tr>
                                       <tr>
                                          <td>
                                             <table class="tsep" style="width:100%;">
                                                <tr>
                                                   <td>
                                                      <table class="tbgr" style="width:100%; border:1px solid #f7f7f7;">
                                                         <tr style="background-color:#f7f7f7; font-size:13px; text-transform:uppercase; height:20px;">
                                                            <td colspan="6" style="font-size:11px;">DADOS DO LDA</td>
                                                         </tr>
                                                         <tr>
                                                            <td class="tdrot grrot">LDA:</td>
                                                            <td class="tdval grval"><?=$v["idresultado"] ?></td>
                                                            <td class="tdrot grrot">Início Exame:</td>
                                                            <td class="tdval grval"><?=dma($v["dataamostra"]) ?></td>
                                                            <td class="tdrot grrot">Final Exame:</td>
                                                            <td class="tdval grval"><?=dma($v["ascriadoem"]) ?></td>
                                                         </tr>
                                                      </table>
                                                      <?
                                                      if (!empty($v["ascriadoem"]) && !empty($oAm["dataamostra"])) {
                                                         validaDataExame($v["ascriadoem"], $oAm["dataamostra"]);
                                                      }
                                                      ?>
                                                   </td>
                                                </tr>
                                             </table>
                                          </td>
                                       </tr>
                                       <tr>
                                          <td>
                                             <table class="tsep" style="width:100%;">
                                                <tr>
                                                   <td>
                                                      <table class="tbgr" style="width:100%; border:1px solid #f7f7f7;">
                                                         <tr style="background-color:#f7f7f7; font-size:13px; text-transform:uppercase;	height:20px;">
                                                            <td colspan="4" style="font-size:11px;">DADOS DA AMOSTRA</td>
                                                         </tr>
                                                         <tr>
                                                            <td class="tdrot grrot">Espécie/Finalidade:</td>
                                                            <td class="tdval grval"><?=$v["especietipofinalidade"] ?></td>
                                                            <td class="tdrot grrot">Data Coleta:</td>
                                                            <td class="tdval grval"><?=dma($v["datacoleta"]) ?></td>
                                                         </tr>
                                                         <tr>
                                                            <td class="tdrot grrot">Material colhido:</td>
                                                            <td class="tdval grval"><?=$v["subtipoamostra"] ?></td>
                                                            <td class="tdrot grrot">Quantidade:</td>
                                                            <td class="tdval grval"><?=$v["nroamostra"] ?></td>

                                                         </tr>
                                                         <? if (!empty($v["descricao"]) || !empty($v["idade"])) { ?>
                                                            <tr>
                                                               <td class="tdrot grrot">Descrição:</td>
                                                               <td class="tdval grval"><?=$v["descricao"] ?></td>
                                                               <td class="tdrot grrot">Idade:</td>
                                                               <td class="tdval grval"><?=$v["idade"] . " " . $v["tipoidade"] ?></td>
                                                            </tr>
                                                         <?
                                                         }

                                                         if ($v["linha"] || $v["regoficial"]) {
                                                            ?>
                                                            <tr>
                                                               <td class="tdrot grrot">Linha:</td>
                                                               <td class="tdval grval"><?=$v["linha"] ?></td>
                                                               <td class="tdrot grrot">Nº Registro oficial:</td>
                                                               <td class="tdval grval"><?=$v["regoficial"] ?></td>
                                                            </tr>
                                                            <? 
                                                         }

                                                         if ($v["formaarmazen"] || $v["meiotransp"]) {
                                                         ?>
                                                            <tr>
                                                               <td class="tdrot grrot">Forma de armaz.:</td>
                                                               <td class="tdval grval"><?=$v["formaarmazen"] ?></td>
                                                               <td class="tdrot grrot">Meio de transp.:</td>
                                                               <td class="tdval grval"><?=$v["meiotransp"] ?></td>
                                                            </tr>
                                                         <?
                                                         }
                                                         if ($v["condconservacao"]) {
                                                         ?>
                                                            <tr>
                                                               <td class="tdrot grrot">Cond. conservação:</td>
                                                               <td class="tdval grval" colspan="3"><?=nl2br($v["condconservacao"]) ?></td>
                                                            </tr>
                                                         <?
                                                         }
                                                         if ($v["observacao"]) {
                                                         ?>
                                                            <tr>
                                                               <td class="tdrot grrot">Observação:</td>
                                                               <td class="tdval grval" colspan="3" style="word-break: break-all;"><?=nl2br(espaco2nbsp($v["observacao"])) ?></td>
                                                            </tr>
                                                         <?
                                                         }
                                                         if ($v["sexo"] || $v["clienteterceiro"]) {
                                                         ?>
                                                            <tr>
                                                               <td class="tdrot grrot">Sexo:</td>
                                                               <td class="tdval grval"><?=$v["sexo"] ?></td>
                                                               <td class="tdrot grrot">Cliente 3&ordm;:</td>
                                                               <td class="tdval grval"><?=$v["clienteterceiro"] ?></td>
                                                            </tr>
                                                         <?
                                                         }
                                                         if ($v["nucleoorigem"] || $v["tipo"]) {
                                                         ?>
                                                            <tr>
                                                               <td class="tdrot grrot">Núcleo origem:</td>
                                                               <td class="tdval grval"><?=$v["nucleoorigem"] ?></td>
                                                               <td class="tdrot grrot">Tipo:</td>
                                                               <td class="tdval grval"><?=$v["tipo"] ?></td>
                                                            </tr>
                                                         <?
                                                         }
                                                         if ($v["especificacao"] || $v["partida"]) {
                                                         ?>
                                                            <tr>
                                                               <td class="tdrot grrot">Especificações:</td>
                                                               <td class="tdval grval"><?=$v["especificacao"] ?></td>
                                                               <td class="tdrot grrot">Partida:</td>
                                                               <td class="tdval grval"><?=$v["partida"] ?></td>
                                                            </tr>
                                                         <?
                                                         }
                                                         if ($v["datafabricacao"] || $v["identificacaochip"]) {
                                                         ?>
                                                            <tr>
                                                               <td class="tdrot grrot">Data fabricação:</td>
                                                               <td class="tdval grval"><?=$v["datafabricacao"] ?></td>
                                                               <td class="tdrot grrot">Chip/Identif.:</td>
                                                               <td class="tdval grval"><?=$v["identificacaochip"] ?></td>
                                                            </tr>
                                                         <?
                                                         }
                                                         if ($v["nroplacas"] || $v["nrodoses"]) {
                                                         ?>
                                                            <tr>
                                                               <td class="tdrot grrot">Nº Placas:</td>
                                                               <td class="tdval grval"><?=$v["nroplacas"] ?></td>
                                                               <td class="tdrot grrot">Nº Doses:</td>
                                                               <td class="tdval grval"><?=$v["nrodoses"] ?></td>

                                                            </tr>
                                                         <?
                                                         }
                                                         if ($v["notafiscal"] || $v["vencimento"]) {
                                                         ?>
                                                            <tr>
                                                               <td class="tdrot grrot">Nota Fiscal:</td>
                                                               <td class="tdval grval"><?=$v["notafiscal"] ?></td>
                                                               <td class="tdrot grrot">Vencimento:</td>
                                                               <td class="tdval grval"><?=$v["vencimento"] ?></td>
                                                            </tr>
                                                         <?
                                                         }
                                                         if ($v["sexadores"] || $v["localexp"]) {
                                                         ?>
                                                            <tr>
                                                               <td class="tdrot grrot">Sexadores:</td>
                                                               <td class="tdval grval"><?=$v["sexadores"] ?></td>
                                                               <td class="tdrot grrot">Local específico:</td>
                                                               <td class="tdval grval"><?=$v["localexp"] ?></td>
                                                            </tr>
                                                         <?
                                                         }
                                                         if ($v["lacre"] || $v["tc"]) {
                                                         ?>
                                                            <tr>
                                                               <td class="tdrot grrot">Lacre:</td>
                                                               <td class="tdval grval"><?=$v["lacre"] ?></td>
                                                               <td class="tdrot grrot">Termo de coleta:</td>
                                                               <td class="tdval grval"><?=$v["tc"] ?></td>
                                                            </tr>
                                                         <?
                                                         }
                                                         if ($v["fabricante"] || $v["semana"]) {
                                                         ?>
                                                            <tr>
                                                               <td class="tdrot grrot">Fabricante:</td>
                                                               <td class="tdval grval"><?=$v["fabricante"] ?></td>
                                                               <td class="tdrot grrot">Semana:</td>
                                                               <td class="tdval grval"><?=$v["semana"] ?></td>
                                                            </tr>
                                                         <?
                                                         }
                                                         if ($v["diluicoes"] || $v["fornecedor"]) {
                                                         ?>
                                                            <tr>
                                                               <td class="tdrot grrot">Diluições:</td>
                                                               <td class="tdval grval"><?=$v["diluicoes"] ?></td>
                                                               <td class="tdrot grrot">Fornecedor:</td>
                                                               <td class="tdval grval"><?=$v["fornecedor"] ?></td>
                                                            </tr>
                                                         <?
                                                         }
                                                         ?>
                                                      </table>
                                                   </td>
                                                </tr>
                                             </table>
                                          </td>
                                       </tr>
                                       <?
                                       if ($v['subtipoamostra'] != '') {
                                          $org = ' (' . $v['subtipoamostra'] . ')';
                                       } else {
                                          $org = '';
                                       }
                                       ?>
                                       <tr>
                                          <td>
                                             <table class="tsep" style="width:100%;">
                                                <tr>
                                                   <td>
                                                      <table class="tbgr" style="width:100%; border:1px solid #f7f7f7;">
                                                         <tr style="background-color:#f7f7f7; font-size:13px; text-transform:uppercase; height:20px;">
                                                            <td colspan="4" style="font-size:11px;">EXAME</td>
                                                         </tr>
                                                         <tr>
                                                            <td class="tdrot grrot">DESCRIÇÃO:</td>
                                                            <td class="tdval grval"><?=$v["descr"] ?></td>
                                                         </tr>
                                                      </table>
                                                   </td>
                                                </tr>
                                             </table>
                                          </td>
                                       </tr>
                                       <tr>
                                          <td>
                                             <table class="tsep" style="width:100%;">
                                                <tr>
                                                   <td>
                                                      <table class="tbgr" style="width:100%; border:1px solid #f7f7f7;">
                                                         <tr style="background-color:#f7f7f7; font-size:13px; text-transform:uppercase; height:20px;">
                                                            <td colspan="4" style="font-size:11px;">RESULTADO DO EXAME</td>
                                                         </tr>

                                                         <?
                                                         unset($linha);
                                                         unset($rc);
                                                         $rc = unserialize(base64_decode($v["jresultado"]));

                                                         //Abre um $row com os dados da coluna jresultado
                                                         $linha["idempresa"]            = $rc["amostra"]["res"]["idempresa"];
                                                         $linha["idunidade"]            = $rc["amostra"]["res"]["idunidade"];
                                                         $linha["idregistro"]         = $rc["amostra"]["res"]["idregistro"];
                                                         $linha["idamostra"]            = $rc["amostra"]["res"]["idamostra"];
                                                         $linha["exercicio"]            = $rc["amostra"]["res"]["exercicio"];
                                                         $linha["idpessoa"]            = $rc["amostra"]["res"]["idpessoa"];
                                                         $linha["idtipoamostra"]         = $rc["amostra"]["res"]["idtipoamostra"];
                                                         $linha["idsubtipoamostra"]      = $rc["amostra"]["res"]["idsubtipoamostra"];
                                                         $linha["datacoleta"]         = dma($rc["amostra"]["res"]["datacoleta"]);
                                                         $linha["nroamostra"]         = $rc["amostra"]["res"]["nroamostra"];
                                                         $linha["rejeitada"]            = $rc["amostra"]["res"]["rejeitada"];
                                                         $linha["origem"]            = $rc["amostra"]["res"]["origem"];
                                                         $linha["lote"]               = $rc["amostra"]["res"]["lote"];
                                                         $linha["idade"]               = $rc["amostra"]["res"]["idade"];
                                                         $linha["tipoidade"]            = $rc["amostra"]["res"]["tipoidade"];
                                                         $linha["observacao"]         = $rc["amostra"]["res"]["observacao"];
                                                         $linha["descricao"]            = $rc["amostra"]["res"]["descricao"];
                                                         $linha["lacre"]               = $rc["amostra"]["res"]["lacre"];
                                                         $linha["galpao"]            = $rc["amostra"]["res"]["galpao"];
                                                         $linha["alojamento"]         = $rc["amostra"]["res"]["alojamento"];
                                                         $linha["numgalpoes"]         = $rc["amostra"]["res"]["numgalpoes"];
                                                         $linha["linha"]               = $rc["amostra"]["res"]["linha"];
                                                         $linha["responsavelof"]         = $rc["amostra"]["res"]["responsavelof"];
                                                         $linha["responsavel"]         = $rc["amostra"]["res"]["responsavel"];
                                                         $linha["tipo"]               = $rc["amostra"]["res"]["tipo"];
                                                         $linha["nroplacas"]            = $rc["amostra"]["res"]["nroplacas"];
                                                         $linha["diluicoes"]            = $rc["amostra"]["res"]["diluicoes"];
                                                         $linha["tipoaves"]            = $rc["amostra"]["res"]["tipoaves"];
                                                         $linha["identificacaochip"]      = $rc["amostra"]["res"]["identificacaochip"];
                                                         $linha["sexo"]               = $rc["amostra"]["res"]["sexo"];
                                                         $linha["datafabricacao"]      = $rc["amostra"]["res"]["datafabricacao"];
                                                         $linha["partida"]            = $rc["amostra"]["res"]["partida"];
                                                         $linha["nrodoses"]            = $rc["amostra"]["res"]["nrodoses"];
                                                         $linha["especificacao"]         = $rc["amostra"]["res"]["especificacao"];
                                                         $linha["fornecedor"]         = $rc["amostra"]["res"]["fornecedor"];
                                                         $linha["localcoleta"]         = $rc["amostra"]["res"]["localcoleta"];
                                                         $linha["localexp"]            = $rc["amostra"]["res"]["localexp"];
                                                         $linha["tc"]               = $rc["amostra"]["res"]["tc"];
                                                         $linha["semana"]            = $rc["amostra"]["res"]["semana"];
                                                         $linha["notafiscal"]         = $rc["amostra"]["res"]["notafiscal"];
                                                         $linha["vencimento"]         = $rc["amostra"]["res"]["vencimento"];
                                                         $linha["fabricante"]         = $rc["amostra"]["res"]["fabricante"];
                                                         $linha["sexadores"]            = $rc["amostra"]["res"]["sexadores"];
                                                         $linha["cpfcnpjprod"]         = $rc["amostra"]["res"]["cpfcnpjprod"];
                                                         $linha["uf"]               = $rc["amostra"]["res"]["uf"];
                                                         $linha["cidade"]            = $rc["amostra"]["res"]["cidade"];
                                                         $linha["pedido"]            = $rc["amostra"]["res"]["pedido"];
                                                         $linha["criadopor"]            = $rc["amostra"]["res"]["criadopor"];
                                                         $linha["criadoem"]            = $rc["amostra"]["res"]["criadoem"];
                                                         $linha["alteradopor"]         = $rc["amostra"]["res"]["alteradopor"];
                                                         $linha["alteradoem"]         = $rc["amostra"]["res"]["alteradoem"];
                                                         $linha["estexterno"]         = $rc["amostra"]["res"]["estexterno"];
                                                         $linha["clienteterceiro"]      = $rc["amostra"]["res"]["clienteterceiro"];
                                                         $linha["nucleoorigem"]         = $rc["amostra"]["res"]["nucleoorigem"];
                                                         $linha["idwfxprocativ"]         = $rc["amostra"]["res"]["idwfxprocativ"];
                                                         $linha["dataamostra"]         = $rc["amostra"]["res"]["dataamostra"];
                                                         $linha["dataamostraformatada"]   = dma($rc["amostra"]["res"]["dataamostra"]);
                                                         $linha["granja"]            = $rc["amostra"]["res"]["granja"];
                                                         $linha["nsvo"]               = $rc["amostra"]["res"]["nsvo"];
                                                         $linha["nucleoamostra"]         = $rc["amostra"]["res"]["nucleoamostra"];
                                                         $linha["idespeciefinalidade"]   = $rc["especiefinalidade"]["res"]["idespeciefinalidade"];
                                                         $linha["especiefinalidade"]      = $rc["especiefinalidade"]["res"]["especiefinalidade"];
                                                         $linha["finalidade"]         = $rc["especiefinalidade"]["res"]["finalidade"];
                                                         $linha["idnucleo"]            = $rc["nucleo"]["res"]["idnucleo"];
                                                         $linha["nucleo"]            = $rc["nucleo"]["res"]["nucleo"];
                                                         $linha["regoficial"]         = $rc["amostra"]["res"]["regoficial"];
                                                         $linha["quantidadeteste"]       = $rc["resultado"]["res"]["quantidade"];
                                                         $linha["nome"]               = $rc["pessoa"]["res"]["nome"];
                                                         $linha["razaosocial"]         = $rc["pessoa"]["res"]["razaosocial"];
                                                         $linha["idservicoensaio"]      = $rc["resultado"]["res"]["idservicoensaio"];
                                                         $linha["gmt"]               = $rc["resultado"]["res"]["gmt"];
                                                         $linha["padrao"]            = $rc["resultado"]["res"]["padrao"];
                                                         $linha["descritivo"]         = $rc["resultado"]["res"]["descritivo"];
                                                         $linha["idresultado"]         = $rc["resultado"]["res"]["idresultado"];
                                                         $linha["idt"]               = $rc["resultado"]["res"]["idt"];
                                                         $linha["idtipoteste"]         = $rc["resultado"]["res"]["idtipoteste"];
                                                         $linha["padrao"]            = $rc["resultado"]["res"]["padrao"];
                                                         $linha["q1"]               = $rc["resultado"]["res"]["q1"];
                                                         $linha["q10"]               = $rc["resultado"]["res"]["q10"];
                                                         $linha["q11"]               = $rc["resultado"]["res"]["q11"];
                                                         $linha["q12"]               = $rc["resultado"]["res"]["q12"];
                                                         $linha["q13"]               = $rc["resultado"]["res"]["q13"];
                                                         $linha["q2"]               = $rc["resultado"]["res"]["q2"];
                                                         $linha["q3"]               = $rc["resultado"]["res"]["q3"];
                                                         $linha["q4"]               = $rc["resultado"]["res"]["q4"];
                                                         $linha["q5"]               = $rc["resultado"]["res"]["q5"];
                                                         $linha["q6"]               = $rc["resultado"]["res"]["q6"];
                                                         $linha["q7"]               = $rc["resultado"]["res"]["q7"];
                                                         $linha["q8"]               = $rc["resultado"]["res"]["q8"];
                                                         $linha["q9"]               = $rc["resultado"]["res"]["q9"];
                                                         $linha["status"]            = $rc["resultado"]["res"]["status"];
                                                         $linha["var"]               = $rc["resultado"]["res"]["var"];
                                                         $linha["idsecretaria"]         = $rc["resultado"]["res"]["idsecretaria"];
                                                         $linha["interfrase"]         = $rc["resultado"]["res"]["interfrase"];
                                                         $linha["versao"]               = $rc["resultado"]["res"]["versao"];
                                                         $linha["alerta"]               = $rc["resultado"]["res"]["alerta"];
                                                         $linha["jsonresultado"]         = $rc["resultado"]["res"]["jsonresultado"];
                                                         $linha["jsonconfig"]            = $rc["resultado"]["res"]["jsonconfig"];
                                                         $linha["subtipoamostra"]      = $rc["subtipoamostra"]["res"]["subtipoamostra"];
                                                         $linha["normativa"]            = $rc["subtipoamostra"]["res"]["normativa"];
                                                         $linha["tipoamostra"]         = $rc["tipoamostra"]["res"]["tipoamostra"];
                                                         $linha["tipoamostraformatado"]   = ($linha["tipoamostra"] == $linha["subtipoamostra"]) ? $linha["tipoamostra"] : $linha["tipoamostra"] . " Subtipo:" . $linha["subtipoamostra"];
                                                         $linha["tipoteste"]            = $rc["prodserv"]["res"]["tipoteste"];
                                                         $linha["sigla"]               = $rc["prodserv"]["res"]["sigla"];
                                                         $linha["tipoespecial"]         = $rc["prodserv"]["res"]["tipoespecial"];
                                                         $linha["geralegenda"]         = $rc["prodserv"]["res"]["geralegenda"];
                                                         $linha["geragraf"]            = $rc["prodserv"]["res"]["geragraf"];
                                                         $linha["geracalc"]            = $rc["prodserv"]["res"]["geracalc"];
                                                         $linha["textopadrao"]         = $rc["prodserv"]["res"]["textopadrao"];
                                                         $linha["textointerpretacao"]   = $rc["prodserv"]["res"]["textointerpretacao"];
                                                         $linha["tipobact"]            = $rc["prodserv"]["res"]["tipobact"];
                                                         $linha["logoinmetro"]         = $rc["prodserv"]["res"]["logoinmetro"];
                                                         $linha["modo"]               = $rc["prodserv"]["res"]["modo"];
                                                         $linha["modelo"]            = $rc["prodserv"]["res"]["modelo"];
                                                         $linha["tipogmt"]            = $rc["prodserv"]["res"]["tipogmt"];
                                                         $linha["comparativodelotes"]   = $rc["prodserv"]["res"]["comparativodelotes"];
                                                         $arrprodservtipoopcao          = $rc["prodservtipoopcao"]["res"];
                                                         $arrprodservtipoopcaoespecie   = $rc["prodservtipoopcaoespecie"]["res"];
                                                         $arrlotecons                = $rc["lotecons"]["res"];
                                                         $rowbio                     = $rc["bioensaio"]["res"];
                                                         $rowend                     = $rc["endereco"]["res"];
                                                         $rtitulos                  = $rc["titulos"]["res"];
                                                         $arrgrafgmt                  = $rc["hist_gmt"]["res"];
                                                         $arrelisa                  = $rc["resultadoelisa"]["res"];
                                                         $arrelisagr1               = $rc["resultadoelisa_graf1"]["res"];
                                                         $arrelisagr2               = $rc["resultadoelisa_graf2"]["res"];
                                                         $arrassinat                  = $rc["resultadoassinatura"]["res"];

                                                         $linha["dataconclusao"]         = $rc["_auditoria"]["res"]["dataconclusao"];

                                                         echo ' <tr style="display:none"><td>' . $linha["idresultado"] . '</td></tr>';
                                                         echo ' <tr style="display:none"><td>' . $linha["modelo"] . '</td></tr>';

                                                         if (($linha["modelo"] == "DESCRITIVO" || $linha["modelo"] == "DROP") && $linha["modo"] == "AGRUP") 
                                                         {
                                                            if ($linha['subtipoamostra'] != '') {
                                                               $org = ' (' . $linha['subtipoamostra'] . ')';
                                                            } else {
                                                               $org = '';
                                                            }
                                                            ?>
                                                            <tr>
                                                               <td class="tdval grval"><?=strip_tags($linha["descritivo"], '<p>'); ?><?=$org; ?></td>
                                                            </tr>
                                                            <?
                                                         } elseif (($linha["modelo"] == "DESCRITIVO" || $linha["modelo"] == "DROP") && $linha["modo"] == "IND") {

                                                            $resind = SolfabController::buscarResultadoIndividualPorIdresultado( $v['idresultado']);
                                                            $y = 0;

                                                            foreach ($resind as $rowind) 
                                                            {
                                                               if ($rowind['subtipoamostra'] != '') {
                                                                  $org = ' (' . $rowind['subtipoamostra'] . ')';
                                                               } else {
                                                                  $org = '';
                                                               }
                                                            ?>
                                                               <tr>
                                                                  <td class="tdval grval">
                                                                     <?
                                                                     echo ("Amostra " . $rowind['identificacao'] . " apresentou resultado " . $rowind['resultado'] . "" . $org . ".");
                                                                     ?>
                                                                  </td>
                                                               </tr>
                                                            <?
                                                            }
                                                         } elseif ($linha["modelo"] == "UPLOAD") {
               
                                                            $i = 0;
                                                            ?>
                                                            <tr>
                                                               <td class="tdrot grrot">&nbsp;</td>
                                                               <td class="tdrot grrot">Wells</td>
                                                               <td class="tdrot grrot">O.D.</td>
                                                               <td class="tdrot grrot">S/P</td>
                                                               <td class="tdrot grrot">S/N</td>
                                                               <td class="tdrot grrot">Titer</td>
                                                               <td class="tdrot grrot">Group</td>
                                                               <td class="tdrot grrot">Result</td>
                                                            </tr>

                                                            <?
                                                            $listarResultadoElisa = SolfabController::buscarResultadosDeArquivoUploadEliza($v["idresultado"]);
                                                            foreach($listarResultadoElisa as $elisa) 
                                                            {
                                                               ?>
                                                               <tr>
                                                                  <td class="tdval grval"><?=$elisa["nome"] ?></td>
                                                                  <td class="tdval grval"><?=$elisa["well"] ?></td>
                                                                  <td class="tdval grval"><?=$elisa['OD'] ?></td>
                                                                  <td class="tdval grval"><?=$elisa['SP'] ?></td>
                                                                  <td class="tdval grval"><?=$elisa['SN'] ?></td>
                                                                  <td class="tdval grval"><?=$elisa['titer'] ?></td>
                                                                  <td class="tdval grval"><?=$elisa['grupo'] ?></td>
                                                                  <td class="tdval grval"><?=$elisa['result'] ?></td>
                                                               </tr>
                                                               <?
                                                               $i++;
                                                            }
                                                         } elseif ($linha['modelo'] == 'DINÂMICO') {
                                                            if ($linha["jsonresultado"] == '') {
                                                               ?>
                                                               <tr>
                                                                  <td class="tdval grval"><?=strip_tags($linha["descritivo"], '<p>') ?></td>
                                                               </tr>
                                                               <?
                                                            } else {
                                                               $phpArray = json_decode($linha["jsonresultado"], true);
                                                               $phpArray[0]->name;
                                                               $z = 0;
                                                               $vindice = '';
                                                               $x = 0;
                                                               foreach ($phpArray as $key1 => $value1) 
                                                               {
                                                                  if ($key1 == 'INDIVIDUAL') 
                                                                  {
                                                                     foreach ($value1 as $k => $w) 
                                                                     {
                                                                        $group = explode('_', $w['name']);
                                                                        $h = $group[2];
                                                                        $group = $group[0];

                                                                        $dinamicoindividual['header'][$h] = $w['titulo'];
                                                                        switch ($w['type']) 
                                                                        {
                                                                           case 'date':
                                                                              $dinamicoindividual[$group][$h] = dma($w['value']);
                                                                           break;
                                                                           case 'checkbox':
                                                                              if ($w['value'] == 1) {
                                                                                 $dinamicoindividual[$group][$h] = 'Sim';
                                                                              } else {
                                                                                 $dinamicoindividual[$group][$h] = 'Não';
                                                                              }

                                                                           break;
                                                                           case 'text':
                                                                              $partida = explode('/', $w['value']);
                                                                              if ($partida[0]) 
                                                                              {
                                                                                 $statuslote = SolfabController::buscarStatusLotePorPartidaEExercicio(2, $partida[0], $partida[1]);
                                                                                 if (count($statuslote) > 0) 
                                                                                 {
                                                                                    if ($statuslote['status'] != 'CANCELADO' && $statuslote['status'] != 'REPROVADO') {
                                                                                       $dinamicoindividual[$group][$h] = $w['value'];
                                                                                    } else {
                                                                                       $dinamicoindividual[$group][$h] = '';
                                                                                    }
                                                                                 } else {
                                                                                    $dinamicoindividual[$group][$h] = $w['value'];
                                                                                 }
                                                                              }
                                                                           break;
                                                                           default:
                                                                              $dinamicoindividual[$group][$h] = $w['value'];
                                                                           break;
                                                                        }
                                                                     }
                                                                     $z++;
                                                                  } else {
                                                                     foreach ($value1 as $k => $w) 
                                                                     {
                                                                        //print_r($v);
                                                                        switch ($w['type']) {
                                                                           case 'date':
                                                                              $dinamicoagrupado[$x]['value'] = dma($w['value']);
                                                                              break;
                                                                           case 'checkbox':
                                                                              if ($w['value'] == 1) {
                                                                                 $dinamicoagrupado[$x]['value'] = 'Sim';
                                                                              } else {
                                                                                 $dinamicoagrupado[$x]['value'] = 'Não';
                                                                              }
                                                                              break;
                                                                           default:
                                                                              $dinamicoagrupado[$x]['value'] = $w['value'];
                                                                              break;
                                                                        }

                                                                        $dinamicoagrupado[$x]['header'] = $w['titulo'];
                                                                        $x++;
                                                                     }
                                                                  }
                                                               }
                                                               //print_r($dinamicoagrupado);
                                                               ?>
                                                               <table style="width:100%; border:1px solid #E1E1E1; height:14px; vertical-align:middle" class="trelisa ">
                                                                  <thead>
                                                                     <tr>
                                                                        <?
                                                                        $z = 0;
                                                                        $cab = [];
                                                                        $tabela = "";

                                                                        foreach ($dinamicoindividual['header'] as $key1 => $value1) 
                                                                        { 
                                                                           ?>
                                                                           <td style="  flex-grow: 1; font-weight:bold;"><?=$value1; ?></td>
                                                                           <?
                                                                           $cab[$z] = $key1;
                                                                           $z++;
                                                                        }
                                                                        unset($dinamicoindividual['header']);
                                                                        ?>
                                                                     </tr>
                                                                  </thead>
                                                                  <tbody>
                                                                     <?
                                                                     $z = 0;
                                                                     foreach ($dinamicoindividual as $key1 => $value1) 
                                                                     { 
                                                                        ?>
                                                                        <tr data-tipo="ind">
                                                                           <? $r = 0;
                                                                           while ($r < count($cab)) 
                                                                           {
                                                                              ?>
                                                                              <td style="  flex-grow: 1;"><?=$value1[$cab[$r]]; ?></td>
                                                                              <?
                                                                              $r++;
                                                                           }
                                                                           ?>
                                                                        </tr>
                                                                        <? 
                                                                     $z++;
                                                                     } 
                                                                     ?>
                                                                  </tbody>
                                                               </table>
                                                               <?
                                                               $z = 0;
                                                               if (count($dinamicoagrupado) > 0) {
                                                                  $tabela .= '<br><table style="width:100%;">';
                                                               }
                                                               foreach ($dinamicoagrupado as $key1 => $value1) {
                                                                  $tabela .= '<tr data-tipo="agrp"><td style="width:74px;white-space:nowrap;"><b>' . $value1['header'] . ':</b></td><td>' . $value1['value'] . '</td></tr>';
                                                               }
                                                               if (count($dinamicoagrupado) > 0) {
                                                                  $tabela .= '</table>';
                                                               }

                                                               echo $tabela;
                                                               unset($dinamicoindividual);
                                                               unset($dinamicoagrupado);
                                                            }
                                                         } //elseif($v["modelo"]=="UPLOAD"){
                                                         ?>
                                                      </table>
                                                   </td>
                                                </tr>
                                             </table>
                                          </td>
                                       </tr>
                                       <?
                                       $mostraIdentificacao = "";
                                       foreach ($listarResultados as $identificaoagente) 
                                       {
                                          if (!empty($identificaoagente["descr"])) 
                                          {
                                             $mostraIdentificacao .= '<tr>                                                           
                                                                        <td class="tdval grval" >' . $identificaoagente["descr"] . ' - ' . $identificaoagente["partida"] . '/' . $identificaoagente["exercicio"] . '</td>
                                                                     </tr>';
                                             $identificao = true;
                                          } else {
                                             $identificao = false;
                                          }
                                       }
                                       if ($identificao == true) 
                                       {
                                          ?>
                                          <tr>
                                             <td>
                                                <table class="tsep" style="width:100%;">
                                                   <tr>
                                                      <td>
                                                         <table class="tbgr" style="width:100%; border:1px solid #f7f7f7;">
                                                            <tr style="background-color:#f7f7f7; font-size:13px; text-transform:uppercase; height:20px;">
                                                               <td style="font-size:11px;">IDENTIFICAÇÃO DO AGENTE</td>
                                                            </tr>
                                                            <?=$mostraIdentificacao ?>
                                                         </table>
                                                      </td>
                                                   </tr>
                                                </table>
                                             </td>
                                          </tr>
                                       <? } ?>

                                       <tr>
                                          <td style="width: 100%">
                                             <table style="width: 100%">
                                                <tr>
                                                   <td>
                                                      <table class="tsep" style="width:100%; margin-top:6px;">
                                                         <tr>
                                                            <td style="font-size:13px;" class="nowrap">
                                                               <table>
                                                                  <?
                                                                  if ($v["statusresult"] == "ASSINADO" && !empty($v["idassinadopor"])) 
                                                                  {
                                                                        $nomresp = "";
                                                                        $crmvresp = "";
                                                                        $arrayPessoaAssinadoPor = [782, 797, 5655, 1098];
                                                                        if(!in_array($v["idassinadopor"], $arrayPessoaAssinadoPor)) {
                                                                           $rowass["idpessoa"] = 797;
                                                                        }

                                                                        if ($oAm["dataamostra"] >= '2021-05-18' &&  ($oAm["idempresa"] == 1 || $oAm["idempresa"] == 2)) {
                                                                           if ($v["idassinadopor"] == 797) {
                                                                              $rowass["idpessoa"] = 5655;
                                                                           } elseif ($v["idassinadopor"] == 782) {
                                                                              $rowass["idpessoa"] = 1098;
                                                                           } else {
                                                                              $rowass["idpessoa"] = 5655;
                                                                           }
                                                                        }

                                                                        $respidpessoa = $rowass["idpessoa"];
                                                                        //troca dados do responsavel via hardcode
                                                                        switch ($rowass["idpessoa"]) {
                                                                           case 782: //edison
                                                                              $nomresp = "Edison Rossi";
                                                                              $crmvresp = "MG N&ordm; 1626";
                                                                              break;
                                                                           case 797: //marcio
                                                                              $nomresp = "Marcio Danilo Botrel Coutinho";
                                                                              $crmvresp = "MG N&ordm; 1454";
                                                                              break;
                                                                           case 5655: //marcio
                                                                              $nomresp = "José Renato de O. Branco";
                                                                              $crmvresp = "MG N&ordm; 19770";
                                                                              break;
                                                                           case 1098: //marcio
                                                                              $nomresp = "Hermes Pedro";
                                                                              $crmvresp = "MG N&ordm; 20412";
                                                                              break;
                                                                           case 97118: //marcio
                                                                              $nomresp = "Ana Paula Mori";
                                                                              $crmvresp = "MG N&ordm; 20758";
                                                                              break;
                                                                           default:
                                                                              $nomresp = "Marcio Danilo Botrel Coutinho";
                                                                              $crmvresp = "MG N&ordm; 1454";
                                                                              break;
                                                                        }                                                            
                                                                        ?>
                                                                        <tr>
                                                                           <td>Técnico Resp.:</td>
                                                                           <td><?=$nomresp ?></td>
                                                                           <td>CRMV:</td>
                                                                           <td><?=$crmvresp ?></td>
                                                                           <td>Assinatura.:</td>
                                                                           <td><img style=" height: 30px; " src='../inc/img/sig<?=strtolower(trim($respidpessoa)) ?>.gif'></td>
                                                                        </tr>                                                                  
                                                                  </table>
                                                               <?
                                                               }
                                                               ?>
                                                            </td>
                                                         </tr>
                                                      </table>
                                                   </td>
                                                </tr>
                                             </table>
                                          </td>
                                       </tr>
                                    </table>
                                 </td>
                              </tr>
                           </table>
                        </td>
                     </tr>
                  </table>
      <?
               } //if($v["alerta"]=="Y"){
            } //while (list($k, $v) = each($oRes)){
         } //while ($rowt=mysqli_fetch_assoc($re)){
      } //if($numtra>0){
      ?>
   </body>
</html>

<?
if ($geraarquivo == 'Y') 
{
   $html = ob_get_contents();
   //limpar o codigo html
   $html = preg_replace('/>\s+</', "><", $html);
   ob_end_clean();

   // Incluímos a biblioteca DOMPDF
   require_once("../inc/dompdf/dompdf_config.inc.php");

   // Instanciamos a classe
   $dompdf = new DOMPDF();

   // Passamos o conteúdo que será convertido para PDF
   $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
   $dompdf->load_html($html);

   // Definimos o tamanho do papel e
   // sua orientação (retrato ou paisagem)
   $dompdf->set_paper('A4', 'portrait');

   // O arquivo é convertido
   $dompdf->render();

   //if($gravaarquivo=='Y'){
   // Salvo no diretório temporário do sistema
   $output = $dompdf->output();
   file_put_contents("/var/www/carbon8/tmp/solfab/soliciacao_fab_" . $_GET["idsolfab"] . ".pdf", $output);
   
   $pdf = new Pdf();

   $pdf->add("/var/www/carbon8/tmp/solfab/soliciacao_fab_" . $_GET["idsolfab"] . ".pdf");             // -- merge all pages      
   $listarArquivos = SolfabController::buscarArquivoSolfabItem($_GET["idsolfab"]);

   foreach($listarArquivos as $arquivo) 
   {
      $pdf->add($arquivo["caminho"]);
   }

   $pdf->output('merged.pdf');         // -- send pdf to inline browser
   $pdf->download('merged.pdf');       // -- force download
   $pdf->save('merged.pdf');           // -- save merged pdf to new file 
}
?>