<?php
require_once 'includes/config1.php';
require_once 'includes/class-db1.php';
require_once '../vendor/autoload.php';

session_start();

$provider = new League\OAuth2\Client\Provider\Google([
    'clientId'     => OAUTH_CLIENT_ID,
    'clientSecret' => OAUTH_CLIENT_SECRET,
    'redirectUri'  => OAUTH_REDIRECT_URI,
]);

if (!isset($_GET['code'])) {
    // Paso 1: Obtener URL de autorización
    $authUrl = $provider->getAuthorizationUrl([
        'scope' => ['https://mail.google.com/'],
        'access_type' => 'offline',
        'prompt' => 'consent'
    ]);
    
    $_SESSION['oauth2state'] = $provider->getState();
    header('Location: '.$authUrl);
    exit;

} else {
    // Paso 2: Intercambiar código por token
    try {
        $token = $provider->getAccessToken('authorization_code', [
            'code' => $_GET['code']
        ]);
        
        $db = new DB();
        $db->update_oauth_token($token->getRefreshToken());
        $db->close();
        
        echo "<h2>Token configurado correctamente!</h2>";
        echo "<p>Refresh token almacenado en la base de datos.</p>";
        echo "<p>Ahora puedes cerrar esta ventana.</p>";

    } catch (Exception $e) {
        exit('Error: ' . $e->getMessage());
    }
}