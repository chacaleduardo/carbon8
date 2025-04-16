<?php
require_once 'config_auth_microsoft.php';
session_start();
$_SESSION['state'] = session_id();

function redirect($url)
{
    header("Location: $url");
    exit();
}

function isAccessTokenExpired()
{
    return !isset($_SESSION['token_expires']) || $_SESSION['token_expires'] <= time();
}

function refreshAccessToken($tenantid, $appid, $secret, $refresh_token)
{
    $token_url = "https://login.microsoftonline.com/$tenantid/oauth2/v2.0/token";
    $token_params = [
        'client_id' => $appid,
        'client_secret' => $secret,
        'grant_type' => 'refresh_token',
        'refresh_token' => $refresh_token
    ];
    $response = fetchFromApi($token_url, [], $token_params);
    error_log("Refresh token response: " . json_encode($response));
    if (isset($response['access_token'])) {
        $_SESSION['access_token'] = $response['access_token'];
        $_SESSION['token_expires'] = time() + $response['expires_in'];
        $_SESSION['refresh_token'] = $response['refresh_token'];
        error_log("New refresh token stored: " . $_SESSION['refresh_token']);
        return $response['access_token'];
    } else {
        error_log("Error refreshing access token: " . json_encode($response));
        unset($_SESSION['token_expires']);
        unset($_SESSION['refresh_token']);
        unset($_SESSION['access_token']);
        return null;
    }
}

function getAccessToken()
{
    global $tenantid, $appid, $secret;

    if(!isset($_SESSION['refresh_token'])){
        $token = userPref('r', 'refresh_token');
        $token = json_decode($token, true);
        if(isset($token['refresh_token'])){
            $_SESSION['refresh_token'] = $token['refresh_token'];
        }
    }
    if (isAccessTokenExpired() && isset($_SESSION['refresh_token'])) {
        return refreshAccessToken($tenantid, $appid, $secret, $_SESSION['refresh_token']);
    }
    return $_SESSION['access_token'];
}

function fetchFromApi($url, $headers = [], $postFields = null, $isJson = false)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    if (!is_null($postFields)) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $isJson ? json_encode($postFields) : http_build_query($postFields));
    }
    if ($isJson) {
        $headers[] = 'Content-Type: application/json';
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

function fetchCalendarEvents()
{
    $access_token = getAccessToken();
    $url = 'https://graph.microsoft.com/v1.0/me/events/?$top=2000';
    $headers = [
        "Authorization: Bearer $access_token",
        'Content-Type: application/json',
        'Prefer: outlook.timezone="America/Sao_Paulo"'
    ];
    $events = fetchFromApi($url, $headers);

    foreach ($events['value'] as &$event) {
        $event['organizer_email'] = $event['organizer']['emailAddress']['address'] ?? '';
        $event['attendees_emails'] = ['responded' => [], 'no_response' => []];
        if (isset($event['attendees'])) {
            foreach ($event['attendees'] as $attendee) {
                if ($attendee['status']['response'] === 'none') {
                    $event['attendees_emails']['no_response'][] = $attendee['emailAddress']['address'];
                } else {
                    $event['attendees_emails']['responded'][] = $attendee['emailAddress']['address'];
                }
            }
        }
        // Adiciona todas as propriedades relevantes do evento
        $event['originalStartTimeZone'] = $event['originalStartTimeZone'] ?? '';
        $event['originalEndTimeZone'] = $event['originalEndTimeZone'] ?? '';
        $event['responseStatus'] = $event['responseStatus']['response'] ?? '';
        $event['iCalUId'] = $event['iCalUId'] ?? '';
        $event['reminderMinutesBeforeStart'] = $event['reminderMinutesBeforeStart'] ?? 0;
        $event['isReminderOn'] = $event['isReminderOn'] ?? false;
        $event['categories'] = $event['categories'] ?? [];
        $event['transactionId'] = $event['transactionId'] ?? '';
        $event['isAllDay'] = $event['isAllDay'] ?? false;
        $event['isCancelled'] = $event['isCancelled'] ?? false;
        $event['isOrganizer'] = $event['isOrganizer'] ?? false;
        $event['responseRequested'] = $event['responseRequested'] ?? false;
        $event['seriesMasterId'] = $event['seriesMasterId'] ?? null;
        $event['showAs'] = $event['showAs'] ?? 'free';
        $event['type'] = $event['type'] ?? 'singleInstance';
        $event['webLink'] = $event['webLink'] ?? '';
        $event['onlineMeetingUrl'] = $event['onlineMeetingUrl'] ?? '';
        $event['isOnlineMeeting'] = $event['isOnlineMeeting'] ?? false;
        $event['onlineMeetingProvider'] = $event['onlineMeetingProvider'] ?? '';
        $event['allowNewTimeProposals'] = $event['allowNewTimeProposals'] ?? false;
        $event['isDraft'] = $event['isDraft'] ?? false;
        $event['hideAttendees'] = $event['hideAttendees'] ?? false;
        $event['recurrence'] = $event['recurrence'] ?? null;
        $event['location'] = $event['location'] ?? ['displayName' => '', 'locationType' => '', 'uniqueId' => '', 'uniqueIdType' => ''];
        $event['locations'] = $event['locations'] ?? [];
        $event['importance'] = $event['importance'] ?? 'normal';
        $event['sensitivity'] = $event['sensitivity'] ?? 'normal';

        // Adiciona a propriedade onlineMeeting
        if (isset($event['onlineMeeting'])) {
            $event['onlineMeeting']['joinUrl'] = $event['onlineMeeting']['joinUrl'] ?? '';
        } else {
            $event['onlineMeeting'] = ['joinUrl' => ''];
        }

        // Log do evento processado
        error_log("Evento processado: " . print_r($event, true));
    }

    return $events;
}

function handleLogin($appid, $login_url, $redirect_uri, $scopes)
{
    $params = [
        'client_id' => $appid,
        'redirect_uri' => $redirect_uri,
        'response_type' => 'code',
        'response_mode' => 'query',
        'scope' => $scopes . ' offline_access',
        'state' => session_id()
    ];
    $loginUrl = $login_url . '?' . http_build_query($params);
    echo "<script>
        var loginWindow = window.open('$loginUrl', 'Login', 'width=600,height=700');
        var loginInterval = setInterval(function() {
            if (loginWindow.closed) {
                clearInterval(loginInterval);
                window.location.reload();
            }
        }, 1000);
    </script>";
}



function handleToken($tenantid, $appid, $secret, $redirect_uri, $code)
{
    $token_url = "https://login.microsoftonline.com/$tenantid/oauth2/v2.0/token";
    $token_params = [
        'client_id' => $appid,
        'client_secret' => $secret,
        'grant_type' => 'authorization_code',
        'code' => $code,
        'redirect_uri' => $redirect_uri
    ];
    return fetchFromApi($token_url, [], $token_params);
}

function fetchUserProfile()
{
    $access_token = getAccessToken();
    if ($access_token === null) {
        return null;
    }
    $url = "https://graph.microsoft.com/v1.0/me/";
    $headers = ["Authorization: Bearer $access_token"];
    return fetchFromApi($url, $headers);
}

function handleLogout($appid, $logout_url, $intermediate_redirect_uri)
{
    unset($_SESSION['access_token']);
    unset($_SESSION['refresh_token']);
    userPref('d', 'access_token');
    $params = [
        'client_id' => $appid,
        'post_logout_redirect_uri' => $intermediate_redirect_uri,
    ];
    redirect($logout_url . '?' . http_build_query($params));
}

function handleAction($action)
{
    global $appid, $login_url, $logout_url, $redirect_uri, $intermediate_redirect_uri, $scopes;

    switch ($action) {
        case 'login':
            handleLogin($appid, $login_url, $redirect_uri, $scopes);
            break;
        case 'logout':
            handleLogout($appid, $logout_url, $intermediate_redirect_uri);
            break;
    }
}

function handleAuthorizationCode($code)
{
    global $tenantid, $appid, $secret, $redirect_uri, $intermediate_redirect_uri;

    $response = handleToken($tenantid, $appid, $secret, $redirect_uri, $code);
    if (isset($response['access_token'])) {
        $_SESSION['access_token'] = $response['access_token'];
        $_SESSION['refresh_token'] = $response['refresh_token'];
        $_SESSION['token_expires'] = time() + $response['expires_in'];
        $userProfile = fetchUserProfile($_SESSION['access_token']);
        if (isset($userProfile['error'])) {
            echo "Error fetching user profile: " . $userProfile['error']['message'];
            exit();
        } else {
            userPref("u", "access_token", $response['access_token']);
            userPref("u", "refresh_token", $response['refresh_token']);
            $_SESSION['user_name'] = $userProfile['displayName'];
            $_SESSION['user_id'] = $userProfile['id'];
            echo "<script>
                window.opener.location.reload();
                window.close();
            </script>";
        }
    } else {
        echo "Error fetching access token: " . json_encode($response);
        exit();
    }
}

// Handle action from URL parameter
if (isset($_GET['action'])) {
    handleAction($_GET['action']);
}

echo '<script>
function handleLogin() {
    const url = "' . $login_url . '?client_id=' . $appid . '&redirect_uri=' . urlencode($redirect_uri) . '&response_type=code&scope=' . urlencode($scopes) . ' offline_access&response_mode=query&state=' . $_SESSION['state'] . '";
    var loginWindow = window.open(url, "Login", "width=600,height=700,top=100,left=100");
    var loginInterval = setInterval(function() {
        if (loginWindow.closed) {
            clearInterval(loginInterval);
            window.location.reload();
        }
    }, 1000);
}
</script>';
