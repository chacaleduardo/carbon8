<?
require_once(__DIR__."/../../../../form/controllers/etiqueta_controller.php");

$row=EtiquetaController::buscarInfosEtiquetaRotulagem15x60( $_OBJ["idlote"] );

if(!empty($row)){
    $arrMeses = [
        "01" => "JAN",
        "02" => "FEV",
        "03" => "MAR",
        "04" => "ABR",
        "05" => "MAI",
        "06" => "JUN",
        "07" => "JUL",
        "08" => "AGO",
        "09" => "SET",
        "10" => "OUT",
        "11" => "NOV",
        "12" => "DEZ",
    ];
    $fabrAno = substr($row["fabricacao"],2,2);
    $vencAno = substr($row["vencimento"],2,2);
    $fabrMes = $arrMeses[substr($row["fabricacao"],5,2)];
    $vencMes = $arrMeses[substr($row["vencimento"],5,2)];


    // $_CONTEUDOIMPRESSAO .= "^XA
    //                             ^MMT
    //                             ^PW177
    //                             ^LL709
    //                             ^LS0
    //                             ^FPH,3^FT49,199^A0B,17,18^FH\^CI28^FDPART.: ".$row["partidaext"]."^FS^CI27
    //                             ^FPH,3^FT70,198^A0B,17,18^FH\^CI28^FDFABR.: ".$fabrMes."/".$fabrAno."^FS^CI27
    //                             ^FPH,3^FT91,197^A0B,17,18^FH\^CI28^FDVENC.: ".$vencMes."/".$vencAno."^FS^CI27
    //                             ^FPH,3^FT112,182^A0B,17,15^FH\^CI28^FDConteúdo: ".$row["volumeformula"]."".$row["un"]."^FS^CI27
    //                             ^PQ1,0,1,Y
    //                         ^XZ";

    $_CONTEUDOIMPRESSAO .= "^XA
                                ^MMT
                                ^PW152
                                ^LL0511
                                ^LS0
                                ^FT113,355^A@R,18,17,TT0003M_^FH\^CI17^F8^FDPART.: ".$row["partidaext"]."^FS^CI0
                                ^FT96,355^A@R,18,17,TT0003M_^FH\^CI17^F8^FDFABR.: ".$fabrMes."/".$fabrAno."^FS^CI0
                                ^FT79,355^A@R,18,17,TT0003M_^FH\^CI17^F8^FDVENC.: ".$vencMes."/".$vencAno."^FS^CI0
                                ^FT55,355^A@R,14,13,TT0003M_^FH\^CI17^F8^FDConteúdo: ".$row["volumeformula"]."".$row["un"]."^FS^CI0
                                ^PQ1,0,1,Y
                            ^XZ";
    // $_CONTEUDOIMPRESSAO .= "^XA
    //                             ^CF0,15
    //                             ^FO370,20
    //                             ^FDPART.: ".$row["partidaext"]."^FS
    //                             ^FO370,40
    //                             ^FDFABR.: ".$fabrMes."/".$fabrAno."^FS
    //                             ^FO370,60
    //                             ^FDVENC.: ".$vencMes."/".$vencAno."^FS
    //                             ^FO375,80
    //                             ^CF0,14
    //                             ^FDConteudo: ".$row["volumeformula"]." ".$row["un"]."^FS
    //                         ^XZ";

}
?>