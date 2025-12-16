<?php
// includes/helpers.php
declare(strict_types=1);

function e(string $s): string {
  return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function money(float $n): string {
  return number_format($n, 0, ',', '.') . " ₫";
}