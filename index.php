<?php
require 'common.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = field('action');
    if ($action === 'logout') {
        $_SESSION = [];
        session_regenerate_id(true);
        setcookie('lastlight_node', '', ['expires'=>time()-3600, 'path'=>'/', 'httponly'=>true, 'samesite'=>'Lax']);
        redirect('index.php');
    }
    $username = field('username');
    // Passwords are not trimmed: spaces can be part of a password.
    $password = isset($_POST['password']) && is_string($_POST['password']) ? $_POST['password'] : '';
    if (!in_array($action, ['login','register'], true) || !preg_match('/\A[A-Za-z0-9_]{3,24}\z/', $username) || strlen($password) < 8 || strlen($password) > 72) {
        $error = 'Use a username of 3 to 24 letters, numbers or underscores and a password of 8 to 72 bytes.';
    } else {
        $key = strtolower($username);
        try {
            $data = storage();
            if ($action === 'register') {
                storage(function ($data) use ($key, $password, $username) {
                    if (isset($data['users'][$key])) { throw new RuntimeException('That username is already registered.'); }
                    $data['users'][$key] = ['name'=>$username, 'hash'=>password_hash($password, PASSWORD_DEFAULT)];
                    return $data;
                });
            } elseif (!isset($data['users'][$key]) || !password_verify($password, $data['users'][$key]['hash'])) {
                throw new RuntimeException('Username or password is incorrect.');
            }
            session_regenerate_id(true);
            $_SESSION = ['username'=>$key, 'csrf'=>bin2hex(random_bytes(24))];
            redirect('game.php');
        } catch (Throwable $exception) { $error = $exception->getMessage(); }
    }
}
page_start('Before the storm');
?>
<p>A boat is trying to get back to shore, but the lighthouse is out. Get inside and fix the light.</p>
<p>Choose a character and make your way up the tower. There are three endings.</p>
<?php if (!empty($_SESSION['username'])): ?>
<p><a href="game.php">Continue to your game</a></p>
<?php else: ?>
<?php if ($error): ?><p role="alert" class="error"><?= e($error) ?></p><?php endif; ?>
<h2>Sign in or register</h2>
<form method="post">
<?php token(); ?>
<label for="username">Username</label>
<input id="username" name="username" minlength="3" maxlength="24" pattern="[A-Za-z0-9_]+" autocomplete="username" required>
<label for="password">Password</label>
<input id="password" type="password" name="password" minlength="8" maxlength="72" autocomplete="current-password" required>
<p><button name="action" value="login">Sign in</button> <button name="action" value="register">Register</button></p>
</form>

<?php endif; page_end(); ?>
