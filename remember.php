<?php
// includes/remember.php
declare(strict_types=1);

function _b64url_encode(string $bin): string {
  return rtrim(strtr(base64_encode($bin), '+/', '-_'), '=');
}

function _b64url_decode(string $s): string {
  $s = strtr($s, '-_', '+/');
  return base64_decode($s . str_repeat('=', (4 - strlen($s) % 4) % 4));
}

function remember_me_set(PDO $pdo, int $userId, int $days = 30): void {
  $selector = bin2hex(random_bytes(12)); 
  $token    = random_bytes(32); 
  $tokenHash = hash('sha256', $token);
  $expires = (new DateTimeImmutable("+$days days"))->format('Y-m-d H:i:s');

  $pdo->prepare("DELETE FROM user_remember_tokens WHERE user_id=?")->execute([$userId]);

  $ins = $pdo->prepare("INSERT INTO user_remember_tokens(user_id, selector, token_hash, expires_at) VALUES(?,?,?,?)");
  $ins->execute([$userId, $selector, $tokenHash, $expires]);

  $cookieValue = $selector . ":" . _b64url_encode($token);
  $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
  
  setcookie('remember_me', $cookieValue, [
    'expires'  => time() + $days * 86400,
    'path'     => '/',
    'httponly' => true,
    'secure'   => $secure,
    'samesite' => 'Lax',
  ]);
}

function remember_me_clear(PDO $pdo): void {
  if (!empty($_COOKIE['remember_me'])) {
    $parts = explode(':', $_COOKIE['remember_me'], 2);
    if (count($parts) === 2) {
      $selector = $parts[0];
      $pdo->prepare("DELETE FROM user_remember_tokens WHERE selector=?")->execute([$selector]);
    }
  }
  setcookie('remember_me', '', [
    'expires'  => time() - 3600,
    'path'     => '/',
    'httponly' => true,
    'secure'   => false,
    'samesite' => 'Lax',
  ]);
}

function remember_me_try_login(PDO $pdo): void {
  if (!empty($_SESSION['user'])) return;
  if (empty($_COOKIE['remember_me'])) return;

  $parts = explode(':', $_COOKIE['remember_me'], 2);
  if (count($parts) !== 2) return;

  [$selector, $tokenB64] = $parts;
  $token = _b64url_decode($tokenB64);
  if ($token === false) return;

  $st = $pdo->prepare("SELECT t.user_id, t.token_hash, t.expires_at, u.id, u.name, u.email, u.role
                       FROM user_remember_tokens t
                       JOIN users u ON u.id = t.user_id
                       WHERE t.selector=? LIMIT 1");
  $st->execute([$selector]);
  $row = $st->fetch(PDO::FETCH_ASSOC);
  if (!$row) return;

  if (strtotime($row['expires_at']) < time()) {
    $pdo->prepare("DELETE FROM user_remember_tokens WHERE selector=?")->execute([$selector]);
    return;
  }

  $calcHash = hash('sha256', $token);
  if (!hash_equals($row['token_hash'], $calcHash)) {
    $pdo->prepare("DELETE FROM user_remember_tokens WHERE selector=?")->execute([$selector]);
    return;
  }

  $_SESSION['user'] = [
    'id'    => (int)$row['id'],
    'name'  => $row['name'],
    'email' => $row['email'],
    'role'  => $row['role'],
  ];
  
  // Gia hạn cookie thêm
  remember_me_set($pdo, (int)$row['id'], 30);
}