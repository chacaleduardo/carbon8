<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<!-- saved from url=(0046)http://supervisorio.laudolab.com.br/views.shtm -->
<html>
   <head>
      <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
      <title>
         Supervisório INATA
      </title>
      <!-- Meta -->
      <meta http-equiv="Content-Style-Type" content="text/css">
      <meta name="Copyright" content="©2006-2011 Serotonin Software Technologies Inc.">
      <meta name="DESCRIPTION" content="Mango Serotonin Software">
      <meta name="KEYWORDS" content="Mango Serotonin Software">

      <link href="./supervisorio/common.css" type="text/css" rel="stylesheet">


   </head>
   <body>
      <table width="100%" cellspacing="0" cellpadding="0" border="0" id="mainHeader">
         <tbody>
            <tr>
               <td><img src="./supervisorio/logoheader.png" alt="Logo"></td>
               <td align="center" width="99%">
                  <a href="http://supervisorio.laudolab.com.br/events.shtm">
                  <span id="__header__alarmLevelDiv" style="display: none; opacity: 0.999999;">
                  <img id="__header__alarmLevelImg" src="./supervisorio/spacer.gif" alt="" border="0" title="">
                  <span id="__header__alarmLevelText"></span>
                  </span>
                  </a>
               </td>
               <td align="right" valign="bottom" class="smallTitle" style="padding:5px; white-space: nowrap;">Supervisório INATA</td>
            </tr>
         </tbody>
      </table>
   
      <div style="padding:5px;">
        
        
         <table class="borderDiv">
            <tbody>
               <tr>
                  <td class="smallTitle">Representações Gráficas </td>
                  <td width="50"></td>
                  <td align="right">
                     <select onchange="window.location=&#39;?viewId=&#39;+ this.value;">
                        <option value="1" selected="selected">Temperatura / Produção</option>
                        <option value="2">Pressão / Produção</option>
                        <option value="5">Pressão / Biotério</option>
                        <option value="6">Temperatura / Biotério</option>
                        <option value="7">Temperatura / Almoxarifado</option>
                        <option value="8">Bloco H - Temperatura</option>
                        <option value="9">Bloco H - Pressão</option>
                     </select>
                   
                  </td>
               </tr>
            </tbody>
         </table>
         <div id="viewContent">
            <img id="viewBackground" src="./supervisorio/15.JPG" alt="">
            <!-- vc 5 -->
            <div id="c5" style="position:absolute;left:905px;top:242px;">
               <div id="c5Content">
                  <img src="./supervisorio/light_red_off.gif" width="28" height="28" alt="">
                  <div style="position:absolute;left:5px;top:25px;">
                     <span class="simpleRenderer">37.1</span>
                  </div>
               </div>
               <div id="c5Controls" class="controlsDiv">
                  <table cellpadding="0" cellspacing="1">
                     <tbody>
                        <tr onmouseover="showMenu(&#39;c5Info&#39;, 16, 0);">
                           <td>
                              <img src="./supervisorio/information.png" border="0">
                              <div id="c5Info" >
                                 <img src="./supervisorio/icon_comp.png" alt="Detalhes do data point" title="Detalhes do data point" class="ptr"  style="display:inline" border="0">
                                 <b>Temperatura Quarto Incubaçao 06-0</b><br>
                                 &nbsp;&nbsp;&nbsp;Valor: 
                                 <span class="infoData">37.1</span><br>
                                 &nbsp;&nbsp;&nbsp;Tempo: <span class="infoData">Sep 08 15:26</span><br>
                              </div>
                           </td>
                        </tr>
                     </tbody>
                  </table>
               </div>
               <div style="position:absolute;left:-16px;top:0px;z-index:1;">
                  <div id="c5Warning" style="" >
                     
                     <div id="c5Messages" class="controlContent">
                        <table width="200" cellspacing="0" cellpadding="0">
                           <tbody>
                              <tr>
                                 <td><img src="./supervisorio/warn.png" alt="Valor do ponto pode não ser confiável" title="Valor do ponto pode não ser confiável" border="0"></td>
                                 <td style="white-space:nowrap;" colspan="3">
                                    Valor do ponto pode não ser confiável
                                    <img src="./supervisorio/arrow_refresh.png" alt="Atualizar" title="Atualizar" class="ptr" style="display:inline" border="0">
                                 </td>
                              </tr>
                           </tbody>
                        </table>
                     </div>
                  </div>
                  <div id="c5Changing" style="display:none;"><img src="./supervisorio/icon_edit.png" alt="Definindo valor..." title="Definindo valor..." border="0"></div>
               </div>
            </div>
            <!-- vc 6 -->
            <div id="c6" style="position:absolute;left:905px;top:200px;">
               <div id="c6Content">
                  <img src="./supervisorio/light_green.gif" width="28" height="28" alt="">
                  <div style="position:absolute;left:5px;top:25px;">
                     <span class="simpleRenderer">(n/a)</span>
                  </div>
               </div>
               <div id="c6Controls" class="controlsDiv">
                  <table cellpadding="0" cellspacing="1">
                     <tbody>
                        <tr >
                           <td>
                              <img src="./supervisorio/information.png" border="0">
                              <div id="c6Info" >
                                 <img src="./supervisorio/icon_comp.png" alt="Detalhes do data point" title="Detalhes do data point" class="ptr" style="display:inline" border="0">
                                 <b>Umidade Sala de Incubaçao 101-12</b><br>
                                 &nbsp;&nbsp;&nbsp;Valor: 
                                 <span class="infoData">(n/a)</span><br>
                              </div>
                           </td>
                        </tr>
                     </tbody>
                  </table>
               </div>
               <div style="position:absolute;left:-16px;top:0px;z-index:1;">
                  <div id="c6Warning" style="" >
                     
                     <div id="c6Messages"  class="controlContent">
                        <table width="200" cellspacing="0" cellpadding="0">
                           <tbody>
                              <tr>
                                 <td valign="top"></td>
                                 <td colspan="3">O data point ou seu data source deve(m) estar desabilitado(s).</td>
                              </tr>
                           </tbody>
                        </table>
                     </div>
                  </div>
                  <div id="c6Changing" style="display:none;"><img src="./supervisorio/icon_edit.png" alt="Definindo valor..." title="Definindo valor..." border="0"></div>
               </div>
            </div>
            <!-- vc 7 -->
            <div id="c7" style="position:absolute;left:623px;top:237px;">
               <div id="c7Content">
                  <img src="./supervisorio/light_red.gif" width="28" height="28" alt="">
                  <div style="position:absolute;left:5px;top:25px;">
                     <span class="simpleRenderer">37.1</span>
                  </div>
               </div>
               <div id="c7Controls" class="controlsDiv">
                  <table cellpadding="0" cellspacing="1">
                     <tbody>
                        <tr >
                           <td>
                              <img src="./supervisorio/information.png" border="0">
                              <div id="c7Info">
                                 <img src="./supervisorio/icon_comp.png" alt="Detalhes do data point" title="Detalhes do data point" class="ptr" style="display:inline" border="0">
                                 <b>Temperatura Quarto Incubaçao 01-0</b><br>
                                 &nbsp;&nbsp;&nbsp;Valor: 
                                 <span class="infoData">37.1</span><br>
                                 &nbsp;&nbsp;&nbsp;Tempo: <span class="infoData">Sep 08 15:26</span><br>
                              </div>
                           </td>
                        </tr>
                        <tr id="c7ChangeMin" >
                           <td>
                              <img src="./supervisorio/icon_edit.png" alt="">
                              <div id="c7Change" >
                                 Definir valor para escrita:<br>
                                 <input id="txtChange7" type="text" value="37.1" >
                                 <a id="txtSet7" class="ptr" >Definir</a>
                              </div>
                           </td>
                        </tr>
                     </tbody>
                  </table>
               </div>
               <div style="position:absolute;left:-16px;top:0px;z-index:1;">
                  <div id="c7Warning" style="" >
                     
                     <div id="c7Messages" >
                        <table width="200" cellspacing="0" cellpadding="0">
                           <tbody>
                              <tr>
                                 <td><img src="./supervisorio/warn.png" alt="Valor do ponto pode não ser confiável" title="Valor do ponto pode não ser confiável" border="0"></td>
                                 <td style="white-space:nowrap;" colspan="3">
                                    
                                 </td>
                              </tr>
                           </tbody>
                        </table>
                     </div>
                  </div>
                  <div id="c7Changing" style="display:none;"><img src="./supervisorio/icon_edit.png" alt="Definindo valor..." title="Definindo valor..." border="0"></div>
               </div>
            </div>
            <!-- vc 24 -->
            <div id="c24" style="position:absolute;left:1049px;top:60px;">
               <div id="c24Content">
                  <img src="./supervisorio/light_green_off.gif" width="28" height="28" alt="">
                  <div style="position:absolute;left:5px;top:25px;">
                     <span class="simpleRenderer">4.3</span>
                  </div>
               </div>
               <div id="c24Controls" class="controlsDiv">
                  <table cellpadding="0" cellspacing="1">
                     <tbody>
                        <tr >
                           <td>
                              <img src="./supervisorio/information.png" border="0">
                              <div id="c24Info" >
                                 <img src="./supervisorio/icon_comp.png" alt="Detalhes do data point" title="Detalhes do data point" class="ptr"  style="display:inline" border="0">
                                 <b>Temp - Almoxarifado Câmara Fria 1-E</b><br>
                                 &nbsp;&nbsp;&nbsp;Valor: 
                                 <span class="infoData">4.3</span><br>
                                 &nbsp;&nbsp;&nbsp;Tempo: <span class="infoData">09:12:04</span><br>
                              </div>
                           </td>
                        </tr>
                        <tr id="c24ChangeMin" >
                           <td>
                              <img src="./supervisorio/icon_edit.png" alt="">
                              <div id="c24Change" >
                                 Definir valor para escrita:<br>
                                 <input id="txtChange24" type="text" value="4.3" >
                                 <a id="txtSet24" class="ptr" >Definir</a>
                              </div>
                           </td>
                        </tr>
                     </tbody>
                  </table>
               </div>
               <div style="position:absolute;left:-16px;top:0px;z-index:1;">
                  <div id="c24Warning" style="display:none;" >
                     
                     <div id="c24Messages"  class="controlContent"></div>
                  </div>
                  <div id="c24Changing" style="display:none;"><img src="./supervisorio/icon_edit.png" alt="Definindo valor..." title="Definindo valor..." border="0"></div>
               </div>
            </div>
            <!-- vc 25 -->
            <div id="c25" style="position:absolute;left:696px;top:408px;">
               <div id="c25Content">
                  <img src="./supervisorio/light_green.gif" width="28" height="28" alt="">
                  <div style="position:absolute;left:5px;top:25px;">
                     <span class="simpleRenderer">3.1</span>
                  </div>
               </div>
               <div id="c25Controls" class="controlsDiv">
                  <table cellpadding="0" cellspacing="1">
                     <tbody>
                        <tr >
                           <td>
                              <img src="./supervisorio/information.png" border="0">
                              <div id="c25Info" >
                                 <img src="./supervisorio/icon_comp.png" alt="Detalhes do data point" title="Detalhes do data point" class="ptr" style="display:inline" border="0">
                                 <b>Temperatura Camara Fria 4</b><br>
                                 &nbsp;&nbsp;&nbsp;Valor: 
                                 <span class="infoData">3.1</span><br>
                                 &nbsp;&nbsp;&nbsp;Tempo: <span class="infoData">09:12:04</span><br>
                              </div>
                           </td>
                        </tr>
                        <tr id="c25ChangeMin" >
                           <td>
                              <img src="./supervisorio/icon_edit.png" alt="">
                              <div id="c25Change" >
                                 Definir valor para escrita:<br>
                                 <input id="txtChange25" type="text" value="3.1">
                                 <a id="txtSet25" class="ptr">Definir</a>
                              </div>
                           </td>
                        </tr>
                     </tbody>
                  </table>
               </div>
               <div style="position:absolute;left:-16px;top:0px;z-index:1;">
                  <div id="c25Warning" style="display:none;">
                     
                     <div id="c25Messages" ></div>
                  </div>
                  <div id="c25Changing" style="display:none;"><img src="./supervisorio/icon_edit.png" alt="Definindo valor..." title="Definindo valor..." border="0"></div>
               </div>
            </div>
            <!-- vc 26 -->
            <div id="c26" style="position:absolute;left:635px;top:407px;">
               <div id="c26Content">
                  <img src="./supervisorio/light_green.gif" width="28" height="28" alt="">
                  <div style="position:absolute;left:5px;top:25px;">
                     <span class="simpleRenderer">-20.0</span>
                  </div>
               </div>
               <div id="c26Controls" class="controlsDiv">
                  <table cellpadding="0" cellspacing="1">
                     <tbody>
                        <tr>
                           <td>
                              <img src="./supervisorio/information.png" border="0">
                              <div id="c26Info" >
                                 <img src="./supervisorio/icon_comp.png" alt="Detalhes do data point" title="Detalhes do data point" class="ptr"  style="display:inline" border="0">
                                 <b>Temperatura Freezer 05</b><br>
                                 &nbsp;&nbsp;&nbsp;Valor: 
                                 <span class="infoData">-20.0</span><br>
                                 &nbsp;&nbsp;&nbsp;Tempo: <span class="infoData">09:12:04</span><br>
                              </div>
                           </td>
                        </tr>
                        <tr id="c26ChangeMin" >
                           <td>
                              <img src="./supervisorio/icon_edit.png" alt="">
                              <div id="c26Change" >
                                 Definir valor para escrita:<br>
                                 <input id="txtChange26" type="text" value="-20.0">
                                 <a id="txtSet26" class="ptr" >Definir</a>
                              </div>
                           </td>
                        </tr>
                     </tbody>
                  </table>
               </div>
               <div style="position:absolute;left:-16px;top:0px;z-index:1;">
                  <div id="c26Warning" style="display:none;" >
                     
                     <div id="c26Messages"  class="controlContent"></div>
                  </div>
                  <div id="c26Changing" style="display:none;"><img src="./supervisorio/icon_edit.png" alt="Definindo valor..." title="Definindo valor..." border="0"></div>
               </div>
            </div>
            <!-- vc 27 -->
            <div id="c27" style="position:absolute;left:225px;top:139px;">
               <div id="c27Content">
                  <img src="./supervisorio/light_green.gif" width="28" height="28" alt="">
                  <div style="position:absolute;left:5px;top:25px;">
                     <span class="simpleRenderer">(n/a)</span>
                  </div>
               </div>
               <div id="c27Controls" class="controlsDiv">
                  <table cellpadding="0" cellspacing="1">
                     <tbody>
                        <tr >
                           <td>
                              <img src="./supervisorio/information.png" border="0">
                              <div id="c27Info" >
                                 <img src="./supervisorio/icon_comp.png" alt="Detalhes do data point" title="Detalhes do data point" class="ptr"  style="display:inline" border="0">
                                 <b>Temperatura Quarto Incubaçao 03-0</b><br>
                                 &nbsp;&nbsp;&nbsp;Valor: 
                                 <span class="infoData">(n/a)</span><br>
                              </div>
                           </td>
                        </tr>
                     </tbody>
                  </table>
               </div>
               <div style="position:absolute;left:-16px;top:0px;z-index:1;">
                  <div id="c27Warning" style="" onmouseover="showMenu(&#39;c27Messages&#39;, 16, 0);" >
                     
                     <div id="c27Messages" >
                        <table width="200" cellspacing="0" cellpadding="0">
                           <tbody>
                              <tr>
                                 <td><img src="./supervisorio/warn.png" alt="Valor do ponto pode não ser confiável" title="Valor do ponto pode não ser confiável" border="0"></td>
                                 <td style="white-space:nowrap;" colspan="3">
                                    
                                    
                                 </td>
                              </tr>
                           </tbody>
                        </table>
                     </div>
                  </div>
                  <div id="c27Changing" style="display:none;"><img src="./supervisorio/icon_edit.png" alt="Definindo valor..." title="Definindo valor..." border="0"></div>
               </div>
            </div>
            <!-- vc 29 -->
            <div id="c29" style="position:absolute;left:782px;top:181px;">
               <div id="c29Content">
                  <img src="./supervisorio/light_red_off.gif" width="28" height="28" alt="">
                  <div style="position:absolute;left:5px;top:25px;">
                     <span class="simpleRenderer">30.2</span>
                  </div>
               </div>
               <div id="c29Controls" class="controlsDiv">
                  <table cellpadding="0" cellspacing="1">
                     <tbody>
                        <tr >
                           <td>
                              <img src="./supervisorio/information.png" border="0">
                              <div id="c29Info" >
                                 <img src="./supervisorio/icon_comp.png" alt="Detalhes do data point" title="Detalhes do data point" class="ptr" style="display:inline" border="0">
                                 <b>Temperatura Sala Manipulação 101-12</b><br>
                                 &nbsp;&nbsp;&nbsp;Valor: 
                                 <span class="infoData">30.2</span><br>
                                 &nbsp;&nbsp;&nbsp;Tempo: <span class="infoData">Sep 08 15:26</span><br>
                              </div>
                           </td>
                        </tr>
                        <tr id="c29ChangeMin" >
                           <td>
                              <img src="./supervisorio/icon_edit.png" alt="">
                              <div id="c29Change" >
                                 Definir valor para escrita:<br>
                                 <input id="txtChange29" type="text" value="30.2">
                                 <a id="txtSet29" class="ptr" >Definir</a>
                              </div>
                           </td>
                        </tr>
                     </tbody>
                  </table>
               </div>
               <div style="position:absolute;left:-16px;top:0px;z-index:1;">
                  <div id="c29Warning">
                     
                     <div id="c29Messages"  class="controlContent">
                        <table width="200" cellspacing="0" cellpadding="0">
                           <tbody>
                              <tr>
                                 <td><img src="./supervisorio/warn.png" alt="Valor do ponto pode não ser confiável" title="Valor do ponto pode não ser confiável" border="0"></td>
                                 <td style="white-space:nowrap;" colspan="3">
                                    Valor do ponto pode não ser confiável
                                    <img src="./supervisorio/arrow_refresh.png" alt="Atualizar" title="Atualizar" class="ptr" style="display:inline" border="0">
                                 </td>
                              </tr>
                           </tbody>
                        </table>
                     </div>
                  </div>
                  <div id="c29Changing" style="display:none;"><img src="./supervisorio/icon_edit.png" alt="Definindo valor..." title="Definindo valor..." border="0"></div>
               </div>
            </div>
            <!-- vc 30 -->
            <div id="c30" style="position:absolute;left:567px;top:166px;">
               <div id="c30Content">
                  <img src="./supervisorio/light_green_off.gif" width="28" height="28" alt="">
                  <div style="position:absolute;left:5px;top:25px;">
                     <span class="simpleRenderer">24.7</span>
                  </div>
               </div>
               <div id="c30Controls" class="controlsDiv">
                  <table cellpadding="0" cellspacing="1">
                     <tbody>
                        <tr >
                           <td>
                              <img src="./supervisorio/information.png" border="0">
                              <div id="c30Info">
                                 <img src="./supervisorio/icon_comp.png" alt="Detalhes do data point" title="Detalhes do data point" class="ptr"  style="display:inline" border="0">
                                 <b>Temperatura Sala de Manipulaçao 102-7</b><br>
                                 &nbsp;&nbsp;&nbsp;Valor: 
                                 <span class="infoData">24.7</span><br>
                                 &nbsp;&nbsp;&nbsp;Tempo: <span class="infoData">Sep 08 15:26</span><br>
                              </div>
                           </td>
                        </tr>
                        <tr id="c30ChangeMin" >
                           <td>
                              <img src="./supervisorio/icon_edit.png" alt="">
                              <div id="c30Change">
                                 Definir valor para escrita:<br>
                                 <input id="txtChange30" type="text" value="24.7" >
                                 <a id="txtSet30" class="ptr" >Definir</a>
                              </div>
                           </td>
                        </tr>
                     </tbody>
                  </table>
               </div>
               <div style="position:absolute;left:-16px;top:0px;z-index:1;">
                  <div id="c30Warning" style="" >
                     
                     <div id="c30Messages" >
                        <table width="200" cellspacing="0" cellpadding="0">
                           <tbody>
                              <tr>
                                 <td><img src="./supervisorio/warn.png" alt="Valor do ponto pode não ser confiável" title="Valor do ponto pode não ser confiável" border="0"></td>
                                 <td style="white-space:nowrap;" colspan="3">
                                    
                                 </td>
                              </tr>
                           </tbody>
                        </table>
                     </div>
                  </div>
                  <div id="c30Changing" style="display:none;"><img src="./supervisorio/icon_edit.png" alt="Definindo valor..." title="Definindo valor..." border="0"></div>
               </div>
            </div>
            <!-- vc 31 -->
            <div id="c31" style="position: absolute; left: 284px; top: 163px; z-index: 0;">
               <div id="c31Content">
                  <img src="./supervisorio/light_green.gif" width="28" height="28" alt="">
                  <div style="position:absolute;left:5px;top:25px;">
                     <span class="simpleRenderer">19.9</span>
                  </div>
               </div>
               <div id="c31Controls" class="controlsDiv" style="visibility: hidden;">
                  <table cellpadding="0" cellspacing="1">
                     <tbody>
                        <tr >
                           <td>
                              <img src="./supervisorio/information.png" border="0">
                              <div id="c31Info" >
                                 <img src="./supervisorio/icon_comp.png" alt="Detalhes do data point" title="Detalhes do data point" class="ptr"  style="display:inline" border="0">
                                 <b>Temperatura Quarto Incubaçao 02-0</b><br>
                                 &nbsp;&nbsp;&nbsp;Valor: 
                                 <span class="infoData">19.9</span><br>
                                 &nbsp;&nbsp;&nbsp;Tempo: <span class="infoData">Jun 19 16:12</span><br>
                              </div>
                           </td>
                        </tr>
                        <tr id="c31ChangeMin"  >
                           <td>
                              <img src="./supervisorio/icon_edit.png" alt="">
                              <div id="c31Change" >
                                 Definir valor para escrita:<br>
                                 <input id="txtChange31" type="text" value="19.9" >
                                 <a id="txtSet31" class="ptr" >Definir</a>
                              </div>
                           </td>
                        </tr>
                     </tbody>
                  </table>
               </div>
               <div style="position:absolute;left:-16px;top:0px;z-index:1;">
                  <div id="c31Warning">
                     
                     <div id="c31Messages"  class="controlContent">
                        <table width="200" cellspacing="0" cellpadding="0">
                           <tbody>
                              <tr>
                                 <td><img src="./supervisorio/warn.png" alt="Valor do ponto pode não ser confiável" title="Valor do ponto pode não ser confiável" border="0"></td>
                                 <td style="white-space:nowrap;" colspan="3">
                                    
                                 </td>
                              </tr>
                           </tbody>
                        </table>
                     </div>
                  </div>
                  <div id="c31Changing" style="display:none;"><img src="./supervisorio/icon_edit.png" alt="Definindo valor..." title="Definindo valor..." border="0"></div>
               </div>
            </div>
            <!-- vc 32 -->
            <div id="c32" style="position:absolute;left:132px;top:216px;">
               <div id="c32Content">
                  <img src="./supervisorio/light_red.gif" width="28" height="28" alt="">
                  <div style="position:absolute;left:5px;top:25px;">
                     <span class="simpleRenderer">26.6</span>
                  </div>
               </div>
               <div id="c32Controls" class="controlsDiv">
                  <table cellpadding="0" cellspacing="1">
                     <tbody>
                        <tr>
                           <td>
                              <img src="./supervisorio/information.png" border="0">
                              <div id="c32Info">
                                 <img src="./supervisorio/icon_comp.png" alt="Detalhes do data point" title="Detalhes do data point" class="ptr" style="display:inline" border="0">
                                 <b>Temperatura Sala de Manipulaçao 104-9</b><br>
                                 &nbsp;&nbsp;&nbsp;Valor: 
                                 <span class="infoData">26.6</span><br>
                                 &nbsp;&nbsp;&nbsp;Tempo: <span class="infoData">Sep 08 15:26</span><br>
                              </div>
                           </td>
                        </tr>
                        <tr id="c32ChangeMin" >
                           <td>
                              <img src="./supervisorio/icon_edit.png" alt="">
                              <div id="c32Change" >
                                 Definir valor para escrita:<br>
                                 <input id="txtChange32" type="text" value="26.6" >
                                 <a id="txtSet32" class="ptr" >Definir</a>
                              </div>
                           </td>
                        </tr>
                     </tbody>
                  </table>
               </div>
               <div style="position:absolute;left:-16px;top:0px;z-index:1;">
                  <div id="c32Warning" style="" >
                     
                     <div id="c32Messages" class="controlContent">
                        <table width="200" cellspacing="0" cellpadding="0">
                           <tbody>
                              <tr>
                                 <td><img src="./supervisorio/warn.png" alt="Valor do ponto pode não ser confiável" title="Valor do ponto pode não ser confiável" border="0"></td>
                                 <td style="white-space:nowrap;" colspan="3">
                                    Valor do ponto pode não ser confiável
                                    <img src="./supervisorio/arrow_refresh.png" alt="Atualizar" title="Atualizar" class="ptr" style="display:inline" border="0">
                                 </td>
                              </tr>
                           </tbody>
                        </table>
                     </div>
                  </div>
                  <div id="c32Changing" style="display:none;"><img src="./supervisorio/icon_edit.png" alt="Definindo valor..." title="Definindo valor..." border="0"></div>
               </div>
            </div>
            <!-- vc 33 -->
            <div id="c33" style="position:absolute;left:271px;top:422px;">
               <div id="c33Content">
                  <img src="./supervisorio/light_red.gif" width="28" height="28" alt="">
                  <div style="position:absolute;left:5px;top:25px;">
                     <span class="simpleRenderer">26.4</span>
                  </div>
               </div>
               <div id="c33Controls" class="controlsDiv">
                  <table cellpadding="0" cellspacing="1">
                     <tbody>
                        <tr onmouseover="showMenu(&#39;c33Info&#39;, 16, 0);">
                           <td>
                              <img src="./supervisorio/information.png" border="0">
                              <div id="c33Info" >
                                 <img src="./supervisorio/icon_comp.png" alt="Detalhes do data point" title="Detalhes do data point" class="ptr"  style="display:inline" border="0">
                                 <b>Temperatura Sala de Material Esteril 216-5</b><br>
                                 &nbsp;&nbsp;&nbsp;Valor: 
                                 <span class="infoData">26.4</span><br>
                                 &nbsp;&nbsp;&nbsp;Tempo: <span class="infoData">Sep 08 15:26</span><br>
                              </div>
                           </td>
                        </tr>
                        <tr id="c33ChangeMin" >
                           <td>
                              <img src="./supervisorio/icon_edit.png" alt="">
                              <div id="c33Change" >
                                 Definir valor para escrita:<br>
                                 <input id="txtChange33" type="text" value="26.4">
                                 <a id="txtSet33" class="ptr" >Definir</a>
                              </div>
                           </td>
                        </tr>
                     </tbody>
                  </table>
               </div>
               <div style="position:absolute;left:-16px;top:0px;z-index:1;">
                  <div id="c33Warning" style="" >
                     
                     <div id="c33Messages" >
                        <table width="200" cellspacing="0" cellpadding="0">
                           <tbody>
                              <tr>
                                 <td><img src="./supervisorio/warn.png" alt="Valor do ponto pode não ser confiável" title="Valor do ponto pode não ser confiável" border="0"></td>
                                 <td style="white-space:nowrap;" colspan="3">
                                    
                                 </td>
                              </tr>
                           </tbody>
                        </table>
                     </div>
                  </div>
                  <div id="c33Changing" style="display:none;"><img src="./supervisorio/icon_edit.png" alt="Definindo valor..." title="Definindo valor..." border="0"></div>
               </div>
            </div>
            <!-- vc 34 -->
            <div id="c34" style="position:absolute;left:739px;top:603px;">
               <div id="c34Content">
                  <img src="./supervisorio/light_green.gif" width="28" height="28" alt="">
                  <div style="position:absolute;left:5px;top:25px;">
                     <span class="simpleRenderer">19.6</span>
                  </div>
               </div>
               <div id="c34Controls" class="controlsDiv">
                  <table cellpadding="0" cellspacing="1">
                     <tbody>
                        <tr onmouseover="showMenu(&#39;c34Info&#39;, 16, 0);" >
                           <td>
                              <img src="./supervisorio/information.png" border="0">
                              <div id="c34Info" >
                                 <img src="./supervisorio/icon_comp.png" alt="Detalhes do data point" title="Detalhes do data point" class="ptr"  style="display:inline" border="0">
                                 <b>Temperatura Sala Envase Asseptico 202-6</b><br>
                                 &nbsp;&nbsp;&nbsp;Valor: 
                                 <span class="infoData">19.6</span><br>
                                 &nbsp;&nbsp;&nbsp;Tempo: <span class="infoData">Sep 08 15:26</span><br>
                              </div>
                           </td>
                        </tr>
                        <tr id="c34ChangeMin" >
                           <td>
                              <img src="./supervisorio/icon_edit.png" alt="">
                              <div id="c34Change" >
                                 Definir valor para escrita:<br>
                                 <input id="txtChange34" type="text" value="19.6" >
                                 <a id="txtSet34" class="ptr" >Definir</a>
                              </div>
                           </td>
                        </tr>
                     </tbody>
                  </table>
               </div>
               <div style="position:absolute;left:-16px;top:0px;z-index:1;">
                  <div id="c34Warning" style="" >
                     
                     <div id="c34Messages" class="controlContent">
                        <table width="200" cellspacing="0" cellpadding="0">
                           <tbody>
                              <tr>
                                 <td><img src="./supervisorio/warn.png" alt="Valor do ponto pode não ser confiável" title="Valor do ponto pode não ser confiável" border="0"></td>
                                 <td style="white-space:nowrap;" colspan="3">
                                    
                                 </td>
                              </tr>
                           </tbody>
                        </table>
                     </div>
                  </div>
                  <div id="c34Changing" style="display:none;"><img src="./supervisorio/icon_edit.png" alt="Definindo valor..." title="Definindo valor..." border="0"></div>
               </div>
            </div>
            <!-- vc 35 -->
            <div id="c35" style="position:absolute;left:460px;top:589px;">
               <div id="c35Content">
                  <img src="./supervisorio/light_green.gif" width="28" height="28" alt="">
                  <div style="position:absolute;left:5px;top:25px;">
                     <span class="simpleRenderer">20.1</span>
                  </div>
               </div>
               <div id="c35Controls" class="controlsDiv">
                  <table cellpadding="0" cellspacing="1">
                     <tbody>
                        <tr >
                           <td>
                              <img src="./supervisorio/information.png" border="0">
                              <div id="c35Info">
                                 <img src="./supervisorio/icon_comp.png" alt="Detalhes do data point" title="Detalhes do data point" class="ptr"  style="display:inline" border="0">
                                 <b>Temperatura Sala Formulaçao de Vacinas 203-6</b><br>
                                 &nbsp;&nbsp;&nbsp;Valor: 
                                 <span class="infoData">20.1</span><br>
                                 &nbsp;&nbsp;&nbsp;Tempo: <span class="infoData">Sep 08 15:26</span><br>
                              </div>
                           </td>
                        </tr>
                        <tr id="c35ChangeMin" >
                           <td>
                              <img src="./supervisorio/icon_edit.png" alt="">
                              <div id="c35Change" >
                                 Definir valor para escrita:<br>
                                 <input id="txtChange35" type="text" value="20.1" >
                                 <a id="txtSet35" class="ptr" >Definir</a>
                              </div>
                           </td>
                        </tr>
                     </tbody>
                  </table>
               </div>
               <div style="position:absolute;left:-16px;top:0px;z-index:1;">
                  <div id="c35Warning" style="" >
                     
                     <div id="c35Messages"  class="controlContent">
                        <table width="200" cellspacing="0" cellpadding="0">
                           <tbody>
                              <tr>
                                 <td><img src="./supervisorio/warn.png" alt="Valor do ponto pode não ser confiável" title="Valor do ponto pode não ser confiável" border="0"></td>
                                 <td style="white-space:nowrap;" colspan="3">
                                    Valor do ponto pode não ser confiável
                                    <img src="./supervisorio/arrow_refresh.png" alt="Atualizar" title="Atualizar" class="ptr"style="display:inline" border="0">
                                 </td>
                              </tr>
                           </tbody>
                        </table>
                     </div>
                  </div>
                  <div id="c35Changing" style="display:none;"><img src="./supervisorio/icon_edit.png" alt="Definindo valor..." title="Definindo valor..." border="0"></div>
               </div>
            </div>
            <!-- vc 36 -->
            <div id="c36" style="position:absolute;left:247px;top:603px;">
               <div id="c36Content">
                  <img src="./supervisorio/light_green.gif" width="28" height="28" alt="">
                  <div style="position:absolute;left:5px;top:25px;">
                     <span class="simpleRenderer">20.0</span>
                  </div>
               </div>
               <div id="c36Controls" class="controlsDiv">
                  <table cellpadding="0" cellspacing="1">
                     <tbody>
                        <tr>
                           <td>
                              <img src="./supervisorio/information.png" border="0">
                              <div id="c36Info" >
                                 <img src="./supervisorio/icon_comp.png" alt="Detalhes do data point" title="Detalhes do data point" class="ptr"  style="display:inline" border="0">
                                 <b>Temperatura Sala Formulaçao Reagentes Biologicos 204-6</b><br>
                                 &nbsp;&nbsp;&nbsp;Valor: 
                                 <span class="infoData">20.0</span><br>
                                 &nbsp;&nbsp;&nbsp;Tempo: <span class="infoData">Sep 08 15:26</span><br>
                              </div>
                           </td>
                        </tr>
                        <tr id="c36ChangeMin" >
                           <td>
                              <img src="./supervisorio/icon_edit.png" alt="">
                              <div id="c36Change">
                                 Definir valor para escrita:<br>
                                 <input id="txtChange36" type="text" value="20.0" >
                                 <a id="txtSet36" class="ptr" >Definir</a>
                              </div>
                           </td>
                        </tr>
                     </tbody>
                  </table>
               </div>
               <div style="position:absolute;left:-16px;top:0px;z-index:1;">
                  <div id="c36Warning" style="" >
                     
                     <div id="c36Messages" class="controlContent">
                        <table width="200" cellspacing="0" cellpadding="0">
                           <tbody>
                              <tr>
                                 <td><img src="./supervisorio/warn.png" alt="Valor do ponto pode não ser confiável" title="Valor do ponto pode não ser confiável" border="0"></td>
                                 <td style="white-space:nowrap;" colspan="3">
                                    Valor do ponto pode não ser confiável
                                    <img src="./supervisorio/arrow_refresh.png" alt="Atualizar" title="Atualizar" class="ptr" style="display:inline" border="0">
                                 </td>
                              </tr>
                           </tbody>
                        </table>
                     </div>
                  </div>
                  <div id="c36Changing" style="display:none;"><img src="./supervisorio/icon_edit.png" alt="Definindo valor..." title="Definindo valor..." border="0"></div>
               </div>
            </div>
            <!-- vc 37 -->
            <div id="c37" style="position:absolute;left:489px;top:409px;">
               <div id="c37Content">
                  <img src="./supervisorio/light_green.gif" width="28" height="28" alt="">
                  <div style="position:absolute;left:5px;top:25px;">
                     <span class="simpleRenderer">(n/a)</span>
                  </div>
               </div>
               <div id="c37Controls" class="controlsDiv">
                  <table cellpadding="0" cellspacing="1">
                     <tbody>
                        <tr>
                           <td>
                              <img src="./supervisorio/information.png" border="0">
                              <div id="c37Info" >
                                 <img src="./supervisorio/icon_comp.png" alt="Detalhes do data point" title="Detalhes do data point" class="ptr"  style="display:inline" border="0">
                                 <b>Temperatura Sala Preparo de Meios 215-5</b><br>
                                 &nbsp;&nbsp;&nbsp;Valor: 
                                 <span class="infoData">(n/a)</span><br>
                              </div>
                           </td>
                        </tr>
                        <tr id="c37ChangeMin" >
                           <td>
                              <img src="./supervisorio/icon_edit.png" alt="">
                              <div id="c37Change" >
                                 Definir valor para escrita:<br>
                                 <input id="txtChange37" type="text" value="" >
                                 <a id="txtSet37" class="ptr">Definir</a>
                              </div>
                           </td>
                        </tr>
                     </tbody>
                  </table>
               </div>
               <div style="position:absolute;left:-16px;top:0px;z-index:1;">
                  <div id="c37Warning" style="" >
                     
                     <div id="c37Messages" >
                        <table width="200" cellspacing="0" cellpadding="0">
                           <tbody>
                              <tr>
                                 <td><img src="./supervisorio/warn.png" alt="Valor do ponto pode não ser confiável" title="Valor do ponto pode não ser confiável" border="0"></td>
                                 <td style="white-space:nowrap;" colspan="3">
                                    
                                 </td>
                              </tr>
                           </tbody>
                        </table>
                     </div>
                  </div>
                  <div id="c37Changing" style="display:none;"><img src="./supervisorio/icon_edit.png" alt="Definindo valor..." title="Definindo valor..." border="0"></div>
               </div>
            </div>
            <!-- vc 38 -->
            <div style="position:absolute;left:744px;top:139px;">
               <div class="rotsala10">101-12</div>
               <div class="descsala10">Sala de Manipulação</div>
            </div>
            <!-- vc 39 -->
            <div style="position:absolute;left:525px;top:132px;">
               <div class="rotsala10">102-7</div>
               <div class="descsala10">Sala de Manipulação</div>
            </div>
            <!-- vc 40 -->
            <div style="position:absolute;left:356px;top:181px;">
               <div class="rotsala10">103-8</div>
               <div class="descsala10">Sala de Manipulação</div>
            </div>
            <!-- vc 41 -->
            <div style="position:absolute;left:92px;top:179px;">
               <div class="rotsala10">104-9</div>
               <div class="descsala10">Sala de Manipulação</div>
            </div>
            <!-- vc 42 -->
            <div style="position:absolute;left:224px;top:180px;">
               <div class="rotsala8">03-0</div>
               <div class="descsala8">Quarto<br>Incubação</div>
            </div>
            <!-- vc 43 -->
            <div style="position:absolute;left:791px;top:121px;">
               <div class="rotsala8">-</div>
            </div>
            <!-- vc 44 -->
            <div style="position:absolute;left:578px;top:116px;">
               <div class="rotsala8">-</div>
            </div>
            <!-- vc 45 -->
            <div style="position:absolute;left:411px;top:167px;">
               <div class="rotsala8">-</div>
            </div>
            <!-- vc 46 -->
            <div style="position:absolute;left:148px;top:166px;">
               <div class="rotsala8">-</div>
            </div>
            <!-- vc 47 -->
            <div style="position:absolute;left:811px;top:357px;">
               <div class="rotsala8">++</div>
            </div>
            <!-- vc 48 -->
            <div style="position:absolute;left:287px;top:364px;">
               <div class="rotsala8">++</div>
            </div>
            <!-- vc 49 -->
            <div style="position:absolute;left:497px;top:357px;">
               <div class="rotsala8">+</div>
            </div>
            <!-- vc 50 -->
            <div style="position:absolute;left:750px;top:541px;">
               <div class="rotsala8">+++</div>
            </div>
            <!-- vc 51 -->
            <div style="position:absolute;left:251px;top:540px;">
               <div class="rotsala8">+++</div>
            </div>
            <!-- vc 52 -->
            <div style="position:absolute;left:460px;top:534px;">
               <div class="rotsala8">+++</div>
            </div>
            <!-- vc 53 -->
            <div style="position:absolute;left:755px;top:374px;">
               <div class="rotsala10">214-13</div>
               <div class="descsala10">Controle de Sementes</div>
            </div>
            <!-- vc 54 -->
            <div style="position:absolute;left:451px;top:376px;">
               <div class="rotsala10">215-5</div>
               <div class="descsala10">Preparo de Meios</div>
            </div>
            <!-- vc 55 -->
            <div style="position:absolute;left:231px;top:382px;">
               <div class="rotsala10">216-5</div>
               <div class="descsala10">Sala de Material Estéril</div>
            </div>
            <!-- vc 56 -->
            <div style="position:absolute;left:712px;top:560px;">
               <div class="rotsala10">202-6</div>
               <div class="descsala10">Envase Asséptico</div>
            </div>
            <!-- vc 57 -->
            <div style="position:absolute;left:419px;top:550px;">
               <div class="rotsala10">203-6</div>
               <div class="descsala10">Formulação Vacinas</div>
            </div>
            <!-- vc 58 -->
            <div style="position:absolute;left:200px;top:557px;">
               <div class="rotsala10">204-6</div>
               <div class="descsala10">Formulação<br>Reagentes Biológicos</div>
            </div>
            <!-- vc 59 -->
            <div style="position:absolute;left:699px;top:367px;">
               <div class="rotsala8">04-0</div>
               <div class="descsala8">Câmara<br>Fria</div>
            </div>
            <!-- vc 60 -->
            <div style="position:absolute;left:638px;top:367px;">
               <div class="rotsala8">05-0</div>
               <div class="descsala8">Freezer</div>
            </div>
            <!-- vc 61 -->
            <div style="position:absolute;left:1028px;top:25px;">
               <div class="rotsala10">01</div>
               <div class="descsala10">Câmara Fria</div>
            </div>
            <!-- vc 62 -->
            <div id="c62" style="position:absolute;left:400px;top:217px;">
               <div id="c62Content">
                  <img src="./supervisorio/light_green_off.gif" width="28" height="28" alt="">
                  <div style="position:absolute;left:5px;top:25px;">
                     <span class="simpleRenderer">23.3</span>
                  </div>
               </div>
               <div id="c62Controls" class="controlsDiv">
                  <table cellpadding="0" cellspacing="1">
                     <tbody>
                        <tr onmouseover="showMenu(&#39;c62Info&#39;, 16, 0);" >
                           <td>
                              <img src="./supervisorio/information.png" border="0">
                              <div id="c62Info" >
                                 <img src="./supervisorio/icon_comp.png" alt="Detalhes do data point" title="Detalhes do data point" class="ptr"  style="display:inline" border="0">
                                 <b>Temperatura Sala de Manipulaçao 103-8</b><br>
                                 &nbsp;&nbsp;&nbsp;Valor: 
                                 <span class="infoData">23.3</span><br>
                                 &nbsp;&nbsp;&nbsp;Tempo: <span class="infoData">Sep 08 15:26</span><br>
                              </div>
                           </td>
                        </tr>
                        <tr id="c62ChangeMin" >
                           <td>
                              <img src="./supervisorio/icon_edit.png" alt="">
                              <div id="c62Change" >
                                 Definir valor para escrita:<br>
                                 <input id="txtChange62" type="text" value="23.3" >
                                 <a id="txtSet62" class="ptr" >Definir</a>
                              </div>
                           </td>
                        </tr>
                     </tbody>
                  </table>
               </div>
               <div style="position:absolute;left:-16px;top:0px;z-index:1;">
                  <div id="c62Warning" style="" >
                     
                     <div id="c62Messages"  class="controlContent">
                        <table width="200" cellspacing="0" cellpadding="0">
                           <tbody>
                              <tr>
                                 <td><img src="./supervisorio/warn.png" alt="Valor do ponto pode não ser confiável" title="Valor do ponto pode não ser confiável" border="0"></td>
                                 <td style="white-space:nowrap;" colspan="3">
                                    
                                 </td>
                              </tr>
                           </tbody>
                        </table>
                     </div>
                  </div>
                  <div id="c62Changing" style="display:none;"><img src="./supervisorio/icon_edit.png" alt="Definindo valor..." title="Definindo valor..." border="0"></div>
               </div>
            </div>
         </div>
      </div>
      <table width="100%" cellspacing="0" cellpadding="0" border="0">
         <tbody>
            <tr>
               <td colspan="2">&nbsp;</td>
            </tr>
            <tr>
               <td colspan="2" class="footer" align="center">©2004-2013 NASH Solutions Ltda., Todos os direitos reservados</td>
            </tr>
         </tbody>
      </table>
      <div id="sm2-container" class="movieContainer" style="position: fixed; width: 8px; height: 8px; bottom: 0px; left: 0px; z-index: -1;">
         <embed name="sm2movie" id="sm2movie" src="./soundmanager2.swf" width="100%" height="100%" quality="high" allowscriptaccess="always" bgcolor="#ffffff" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" wmode="transparent">
      </div>
      <div id="soundmanager-debug" style="display: none;"></div>
   </body>
</html>