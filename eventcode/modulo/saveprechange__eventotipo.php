<?
if( $_GET['_acao']=='u' && strlen($_POST['_1_u_eventotipo_eventotitle']) > 255 ){
        echo "Forneça uma explicaçao com no máximo 255 caracteres";
    die;
}

if( $_GET['_acao']=='i' && strlen($_POST['_1_i_eventotipo_eventotitle']) > 255 ){  
        echo "Forneça uma explicaçao com no máximo 255 caracteres";
    die;
}