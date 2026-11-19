<?php
// Shared session, storage and display helpers.
ini_set('session.use_strict_mode', '1');
session_set_cookie_params(['httponly'=>true, 'samesite'=>'Lax', 'secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(24)); }
// Keep account data outside the public files, in a private directory PHP can write.
$dataDirectory = getenv('LASTLIGHT_DATA_DIR') ?: sys_get_temp_dir() . '/lastlight-' . substr(hash('sha256', __DIR__), 0, 16);
define('DATA_FILE', rtrim($dataDirectory, '/') . '/lastlight-private.json');
function e($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function field($name) { return isset($_POST[$name]) && is_string($_POST[$name]) ? trim($_POST[$name]) : ''; }
function redirect($url) { header('Location: ' . $url, true, 303); exit; }
function protect() { if (empty($_SESSION['username'])) { redirect('index.php'); } }
function csrf_check() {
    if (!hash_equals($_SESSION['csrf'], field('csrf'))) { http_response_code(403); exit('Invalid form token. Reload the page and try again.'); }
}
function token() { echo '<input type="hidden" name="csrf" value="' . e($_SESSION['csrf']) . '">'; }
// A single locked file prevents registration and score updates from overwriting each other.
function storage($update = null) {
    $old = umask(0077);
    if (!is_dir(dirname(DATA_FILE)) && !mkdir(dirname(DATA_FILE), 0700, true) && !is_dir(dirname(DATA_FILE))) {
        umask($old);
        throw new RuntimeException('Cannot create private data directory.');
    }
    $file = fopen(DATA_FILE, 'c+');
    umask($old);
    if (!$file || !flock($file, LOCK_EX)) { throw new RuntimeException('Cannot open private data file. Check its directory permissions.'); }
    try {
        $raw = stream_get_contents($file);
        $data = $raw === '' ? ['users'=>[], 'scores'=>[]] : json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($data) || !isset($data['users'], $data['scores']) || !is_array($data['users']) || !is_array($data['scores'])) { throw new RuntimeException('Invalid private data file.'); }
        if ($update !== null) {
            $data = $update($data);
            $json = json_encode($data, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
            rewind($file);
            if (fwrite($file, $json) !== strlen($json)) { throw new RuntimeException('Could not save data.'); }
            ftruncate($file, strlen($json));
            fflush($file);
        }
        return $data;
    } finally { flock($file, LOCK_UN); fclose($file); }
}
function deficits($choice, $game) {
    $reasons = [];
    foreach ($choice['requires'] ?? [] as $stat=>$needed) {
        if ($game[$stat] < $needed) { $reasons[] = $stat . ' needs ' . ($needed - $game[$stat]) . ' more'; }
    }
    foreach ($choice['items'] ?? [] as $item) {
        if (!in_array($item, $game['inventory'], true)) { $reasons[] = 'requires ' . $item; }
    }
    return $reasons;
}
function choice_notes($choice, $game) {
    $notes = deficits($choice, $game);
    if (isset($choice['health'])) { $notes[] = 'Health ' . sprintf('%+d', $choice['health']); }
    if (isset($choice['score'])) { $notes[] = 'Score ' . sprintf('%+d', $choice['score']); }
    if (isset($choice['get'])) { $notes[] = 'Gain ' . $choice['get']; }
    if (isset($choice['note'])) { $notes[] = $choice['note']; }
    return $notes ?: ['No stat change.'];
}
function page_start($title) {
    header('Content-Type: text/html; charset=UTF-8');
    header("Content-Security-Policy: default-src 'self'; style-src 'self'; form-action 'self'; frame-ancestors 'none'; base-uri 'self'");
    header('X-Content-Type-Options: nosniff');
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>' . e($title) . ' | Last light</title><link rel="stylesheet" href="style.css"></head><body><header><a class="title" href="index.php">Last light</a><nav aria-label="Main"><a href="game.php">Play</a> <a href="leaderboard.php">Leaderboard</a> <a href="team.php">Team</a></nav>';
    if (!empty($_SESSION['username'])) { echo '<form method="post" action="index.php" class="logout">'; token(); echo '<input type="hidden" name="action" value="logout"><span>' . e($_SESSION['username']) . '</span> <button>Log out</button></form>'; }
    echo '</header><main><h1>' . e($title) . '</h1>';
}
function page_end() { echo '</main></body></html>'; }
