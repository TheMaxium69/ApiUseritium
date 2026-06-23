<?php

namespace Controllers;

class Oauth extends Controller
{
    public function __construct()
    {
        $this->default = new \Model\Users();
    }

    // -------------------------------------------------------------------------
    // GET/POST /oauth/authorize
    // -------------------------------------------------------------------------

    public function authorize(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePost();
            return;
        }
        $this->showForm();
    }

    private function showForm(string $error = ''): void
    {
        $clientId     = trim($_GET['client_id']      ?? '');
        $redirectUri  = trim($_GET['redirect_uri']   ?? '');
        $state        = $_GET['state']               ?? '';
        $responseType = $_GET['response_type']       ?? 'token';

        $client = $this->findClient($clientId, $redirectUri);
        if (!$client) {
            $this->htmlError('Client OAuth invalide ou redirect_uri non autorisée.');
            return;
        }

        header('Content-Type: text/html; charset=UTF-8');
        header('X-Frame-Options: DENY');
        header("Content-Security-Policy: frame-ancestors 'none'");
        echo $this->loginHtml($client->name, $clientId, $redirectUri, $state, $responseType, $error);
    }

    private function handlePost(): void
    {
        $clientId     = trim($_POST['client_id']        ?? '');
        $redirectUri  = trim($_POST['redirect_uri']     ?? '');
        $state        = $_POST['state']                 ?? '';
        $responseType = $_POST['response_type']         ?? 'token';
        $login        = trim($_POST['login_useritium']  ?? '');
        $mdp          = trim($_POST['mdp_useritium']    ?? '');

        $client = $this->findClient($clientId, $redirectUri);
        if (!$client) {
            $this->htmlError('Client OAuth invalide ou redirect_uri non autorisée.');
            return;
        }

        if ($login === '' || $mdp === '') {
            $this->showFormError($client, $clientId, $redirectUri, $state, $responseType, 'Tous les champs sont requis.');
            return;
        }

        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            $user = $this->default->findByEmail($login);
        } else {
            $user = $this->default->findByUsername($login);
        }

        if (!$user || $this->default->chiffreMdp($mdp) !== $user->password) {
            $this->showFormError($client, $clientId, $redirectUri, $state, $responseType, 'Identifiants incorrects.');
            return;
        }

        if (!empty($client->allowed_roles)) {
            $allowed = array_map('trim', explode(',', $client->allowed_roles));
            if (!in_array($user->role, $allowed, true)) {
                $this->showFormError($client, $clientId, $redirectUri, $state, $responseType, 'Accès non autorisé pour ce compte.');
                return;
            }
        }

        if ($responseType === 'code') {
            $code = (new \Model\OauthCode())->create($clientId, (int) $user->id, $redirectUri);
            $sep  = strpos($redirectUri, '?') !== false ? '&' : '?';
            header('Location: ' . $redirectUri . $sep . 'code=' . urlencode($code) . '&state=' . urlencode($state), true, 302);
        } else {
            // Flux implicite (Odoo Community) — token dans le fragment
            $accessToken = (new \Model\OauthToken())->create((int) $user->id, $clientId);
            $fragment    = 'access_token=' . urlencode($accessToken)
                . '&token_type=Bearer'
                . '&expires_in=3600'
                . '&state=' . urlencode($state);
            header('Location: ' . $redirectUri . '#' . $fragment, true, 302);
        }
        exit;
    }

    private function showFormError(object $client, string $clientId, string $redirectUri, string $state, string $responseType, string $error): void
    {
        header('Content-Type: text/html; charset=UTF-8');
        header('X-Frame-Options: DENY');
        header("Content-Security-Policy: frame-ancestors 'none'");
        echo $this->loginHtml($client->name, $clientId, $redirectUri, $state, $responseType, $error);
    }

    // -------------------------------------------------------------------------
    // POST /oauth/token
    // -------------------------------------------------------------------------

    public function token(): void
    {
        header('Content-Type: application/json');

        $post = $_POST;
        if (empty($post)) {
            parse_str(file_get_contents('php://input'), $post);
        }

        $grantType   = $post['grant_type']   ?? '';
        $clientId    = $post['client_id']    ?? '';
        $code        = $post['code']         ?? '';
        $redirectUri = $post['redirect_uri'] ?? '';

        if ($grantType !== 'authorization_code') {
            $this->json(['error' => 'unsupported_grant_type'], 400);
            return;
        }

        $client = (new \Model\OauthClient())->findById($clientId);
        if (!$client) {
            $this->json(['error' => 'invalid_client'], 401);
            return;
        }

        $codes   = new \Model\OauthCode();
        $codeRow = $codes->findByCode($code);

        if (!$codeRow || $codeRow->client_id !== $clientId || $codeRow->redirect_uri !== $redirectUri) {
            $this->json(['error' => 'invalid_grant'], 400);
            return;
        }

        $codes->consume($code);
        $accessToken = (new \Model\OauthToken())->create((int) $codeRow->user_id, $clientId);

        $this->json([
            'access_token' => $accessToken,
            'token_type'   => 'Bearer',
            'expires_in'   => 3600,
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /oauth/userinfo  (Authorization: Bearer <token>)
    // -------------------------------------------------------------------------

    public function userinfo(): void
    {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Authorization, Content-Type');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }

        // DEBUG TEMPORAIRE — à supprimer après diagnostic
        file_put_contents(__DIR__ . '/../../../debug.log',
            date('Y-m-d H:i:s') . ' | METHOD=' . $_SERVER['REQUEST_METHOD']
            . ' | GET=' . json_encode($_GET)
            . ' | POST=' . json_encode($_POST)
            . ' | AUTH=' . ($_SERVER['HTTP_AUTHORIZATION'] ?? 'none')
            . "\n",
            FILE_APPEND
        );

        // Odoo (flux implicite) envoie le token en query param ou POST body
        $rawToken = $_GET['access_token']
            ?? $_POST['access_token']
            ?? null;

        // Fallback Bearer header (flux code / Postman)
        if (!$rawToken) {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION']
                ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
                ?? (function_exists('getallheaders') ? (getallheaders()['Authorization'] ?? '') : '');
            if (preg_match('/^Bearer\s+(.+)$/i', $authHeader, $m)) {
                $rawToken = trim($m[1]);
            }
        }

        if (!$rawToken) {
            $this->json(['error' => 'invalid_token'], 401);
            return;
        }

        $tokenRow = (new \Model\OauthToken())->findByToken(trim($rawToken));
        if (!$tokenRow) {
            file_put_contents(__DIR__ . '/../../../debug.log', date('Y-m-d H:i:s') . " | RESPONSE=invalid_token (not found in DB)\n", FILE_APPEND);
            $this->json(['error' => 'invalid_token'], 401);
            return;
        }

        $user = $this->default->find((int) $tokenRow->user_id);
        if (!$user) {
            file_put_contents(__DIR__ . '/../../../debug.log', date('Y-m-d H:i:s') . " | RESPONSE=invalid_token (user not found, user_id={$tokenRow->user_id})\n", FILE_APPEND);
            $this->json(['error' => 'invalid_token'], 401);
            return;
        }

        $picture = !empty($user->pp)
            ? 'https://dashboard.useritium.fr/uploads/pp/' . $user->pp
            : 'https://tyrolium.fr/generate-pp/?c=183153&l=' . strtoupper(substr($user->username, 0, 1));

        $response = [
            'sub'     => (string) $user->id,
            'email'   => $user->email,
            'name'    => $user->displayname ?: $user->username,
            'picture' => $picture,
        ];
        file_put_contents(__DIR__ . '/../../../debug.log', date('Y-m-d H:i:s') . ' | RESPONSE=' . json_encode($response) . "\n", FILE_APPEND);
        $this->json($response);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function findClient(string $clientId, string $redirectUri): ?object
    {
        if ($clientId === '' || $redirectUri === '') {
            return null;
        }
        $client = (new \Model\OauthClient())->findById($clientId);
        if (!$client || $client->redirect_uri !== $redirectUri) {
            return null;
        }
        return $client;
    }

    private function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        echo json_encode($data);
    }

    private function htmlError(string $msg): void
    {
        http_response_code(400);
        header('Content-Type: text/html; charset=UTF-8');
        echo '<!DOCTYPE html><html><body style="font-family:sans-serif;text-align:center;padding:60px;background:#0f0f13;color:#f87171;">'
            . '<h2>Erreur OAuth</h2><p>' . htmlspecialchars($msg, ENT_QUOTES) . '</p></body></html>';
    }

    private function loginHtml(string $appName, string $clientId, string $redirectUri, string $state, string $responseType, string $error): string
    {
        $appName      = htmlspecialchars($appName,      ENT_QUOTES);
        $clientId     = htmlspecialchars($clientId,     ENT_QUOTES);
        $responseType = htmlspecialchars($responseType, ENT_QUOTES);
        $redirectUri  = htmlspecialchars($redirectUri,  ENT_QUOTES);
        $state        = htmlspecialchars($state,        ENT_QUOTES);
        $errorHtml    = $error
            ? '<p class="auth-error"><i class="ri-error-warning-line"></i>' . htmlspecialchars($error, ENT_QUOTES) . '</p>'
            : '';

        return <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Connexion Useritium</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Syne:wght@700&family=Inter:wght@400;600&display=swap">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #f3f4f6;
      font-family: 'Inter', sans-serif;
      padding: 1rem;
    }
    .auth-card {
      position: relative;
      width: 100%;
      max-width: 420px;
      background: #ffffff;
      border-radius: 20px;
      padding: 2.25rem 2rem 1.75rem;
      box-shadow: 0 24px 64px rgba(0,0,0,0.18);
      animation: slide-in 0.22s cubic-bezier(0.34,1.56,0.64,1);
    }
    @keyframes slide-in {
      from { opacity: 0; transform: translateY(20px) scale(0.97); }
      to   { opacity: 1; transform: translateY(0)    scale(1);    }
    }
    .auth-header {
      display: flex;
      flex-direction: column;
      align-items: center;
      margin-bottom: 1.5rem;
    }
    .auth-logo-wrap { position: relative; width: 56px; height: 56px; margin-bottom: 0.75rem; }
    .auth-logo-img {
      width: 56px; height: 56px;
      border-radius: 14px;
      object-fit: contain;
    }
    .auth-logo-img:not([style*="display:none"]) + .auth-logo-fallback { display: none; }
    .auth-logo-fallback {
      width: 56px; height: 56px;
      border-radius: 14px;
      background: linear-gradient(135deg, #0000FF, #BF0000);
      display: flex; align-items: center; justify-content: center;
      font-family: 'Syne', sans-serif;
      font-size: 1.5rem; font-weight: 700; color: #fff;
    }
    .auth-title {
      font-family: 'Syne', sans-serif;
      font-size: 1.35rem; font-weight: 700;
      color: #111827; margin: 0 0 0.25rem;
    }
    .auth-subtitle {
      font-size: 0.82rem; color: #9ca3af;
      text-align: center; margin: 0;
    }
    .auth-subtitle strong { color: #374151; font-weight: 600; }
    .auth-form {
      display: flex; flex-direction: column; gap: 1rem;
    }
    .auth-field {
      display: flex; flex-direction: column; gap: 0.4rem;
    }
    .auth-field label {
      font-size: 0.8rem; font-weight: 600; color: #374151;
    }
    .auth-field input {
      width: 100%;
      font-family: 'Inter', sans-serif;
      font-size: 0.9rem; color: #111827;
      padding: 0.7rem 1rem;
      border: 1.5px solid #e5e7eb;
      border-radius: 10px;
      background: #f9fafb;
      outline: none;
      transition: border-color 0.18s, box-shadow 0.18s;
    }
    .auth-field input::placeholder { color: #9ca3af; }
    .auth-field input:focus {
      border-color: #0000FF;
      background: #ffffff;
      box-shadow: 0 0 0 3px rgba(0,0,255,0.08);
    }
    .auth-pass-wrap { position: relative; }
    .auth-pass-wrap input { padding-right: 2.75rem; }
    .auth-pass-toggle {
      position: absolute; right: 0.75rem; top: 50%;
      transform: translateY(-50%);
      border: none; background: none;
      cursor: pointer; color: #9ca3af;
      font-size: 1rem; display: flex; align-items: center;
      padding: 0; transition: color 0.15s; width: auto;
    }
    .auth-pass-toggle:hover { color: #374151; }
    .auth-error {
      display: flex; align-items: center; gap: 0.5rem;
      padding: 0.7rem 0.9rem;
      background: #fef2f2; border: 1px solid #fecaca;
      border-radius: 10px;
      font-size: 0.83rem; color: #dc2626; margin: 0;
    }
    .auth-submit {
      display: flex; align-items: center; justify-content: center;
      width: 100%; padding: 0.85rem;
      background: linear-gradient(135deg, #0000FF 0%, #BF0000 100%);
      color: #fff;
      font-family: 'Inter', sans-serif;
      font-size: 0.95rem; font-weight: 600;
      border: none; border-radius: 100px;
      cursor: pointer;
      transition: opacity 0.18s, transform 0.18s;
      margin-top: 0.25rem;
    }
    .auth-submit:hover { opacity: 0.88; transform: translateY(-1px); }
    .auth-footer {
      margin: 1.25rem 0 0;
      text-align: center;
      font-size: 0.75rem; color: #9ca3af;
    }
    @media (max-width: 480px) {
      .auth-card { padding: 1.75rem 1.25rem 1.5rem; border-radius: 16px; }
    }
  </style>
</head>
<body>
  <div class="auth-card">
    <div class="auth-header">
      <div class="auth-logo-wrap">
        <img src="https://useritium.fr/assets/tyrolium-ui/projects/Useritium.png"
             alt="Useritium" class="auth-logo-img"
             onerror="this.style.display='none'">
        <div class="auth-logo-fallback">U</div>
      </div>
      <h2 class="auth-title">Useritium</h2>
      <p class="auth-subtitle">Connexion requise par <strong>{$appName}</strong></p>
    </div>
    <form class="auth-form" method="POST" action="?controller=Oauth&task=authorize">
      <input type="hidden" name="client_id"      value="{$clientId}">
      <input type="hidden" name="redirect_uri"   value="{$redirectUri}">
      <input type="hidden" name="state"          value="{$state}">
      <input type="hidden" name="response_type"  value="{$responseType}">
      {$errorHtml}
      <div class="auth-field">
        <label>E-mail ou nom d'utilisateur</label>
        <input type="text" name="login_useritium"
               placeholder="vous@exemple.fr ou nom d'utilisateur"
               autocomplete="username" autofocus required>
      </div>
      <div class="auth-field">
        <label>Mot de passe</label>
        <div class="auth-pass-wrap">
          <input type="password" id="mdp" name="mdp_useritium"
                 placeholder="Votre mot de passe"
                 autocomplete="current-password" required>
          <button type="button" class="auth-pass-toggle" onclick="togglePass(this)">
            <i class="ri-eye-line"></i>
          </button>
        </div>
      </div>
      <button type="submit" class="auth-submit">Se connecter</button>
    </form>
    <p class="auth-footer">Gratuit &middot; Hébergé en France &middot; Jamais vendu</p>
  </div>
  <script>
    function togglePass(btn) {
      var input = document.getElementById('mdp');
      var isPass = input.type === 'password';
      input.type = isPass ? 'text' : 'password';
      btn.querySelector('i').className = isPass ? 'ri-eye-off-line' : 'ri-eye-line';
    }
  </script>
</body>
</html>
HTML;
    }
}
