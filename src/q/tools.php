<?php
namespace app\q;

class tools {
  static function isa_ts(string $string, ?array &$ms=null): bool {
    return preg_match('/\d{8}/', $string, $ms);
  }

  static function ts2date(string $timestamp, ?string &$ymd=null): string {
    if (self::isa_ts($timestamp, $ms)) {
      $ymd = $ms[0];
      $y = substr($ymd, 0, 4);
      $m = substr($ymd, 4, 2);
      $d = substr($ymd, 6, 2);
      return "$d/$m/$y";
    }
    return $timestamp;
  }

  static function get_profile_var(string $var, string $profile, ?array $defaults=null): ?string {
    $pvar = "{$profile}_{$var}";
    $avar = "__ALL__$var";
    if (($value = getenv($pvar)) === false) {
      if (($value = getenv($avar)) === false) {
        $value = getenv($var);
      }
    }
    if ($value === false) $value = $defaults[$var] ?? null;
    return $value;
  }

  static function get_profile_vars(array $vars, string $profile, ?array $defaults=null): array {
    $pvalues = [];
    foreach ($vars as $key => $var) {
      $pvalues[$key] = self::get_profile_var($var, $profile, $defaults);
    }
    return $pvalues;
  }
}
