<?php
$appid = "ab9c012f-5ea4-4a42-8e2c-f00be1575998";
$tenantid = "df6429a8-9c76-493c-b61b-c1f2b87147e2";
$secret = "J8y8Q~s6Rqq4CErtoBHjS33zhYdhQeBo1iPgmbtd";
$login_url = "https://login.microsoftonline.com/$tenantid/oauth2/v2.0/authorize";
$logout_url = "https://login.microsoftonline.com/$tenantid/oauth2/v2.0/logout";
$redirect_uri = 'https://sislaudo.laudolab.com.br/form/calendario.php'; // Redirecionamento após login
$intermediate_redirect_uri = 'https://sislaudo.laudolab.com.br/?_modulo=calendario';
$scopes = 'openid offline_access profile email User.Read User.ReadBasic.All Calendars.Read Place.Read.All Calendars.ReadWrite Contacts.Read Contacts.ReadWrite';