#!/usr/bin/php
<?php

function error_die($msg): never {
  echo "ERROR: $msg\n";
  exit(1);
}

$v33Format = true;
$input = null;
$output = "env";
if ($argc > 1) {
  for ($i = 1; $i < $argc; $i++) {
    $arg = $argv[$i];
    switch ($arg) {
    case "--legacy":
      $v33Format = false;
      break;
    case "--env":
      $output = "env";
      break;
    case "--json":
      $output = "json";
      break;
    default:
      $input = $arg;
      break;
    }
  }
}

if ($input === null) {
  error_die("Il faut spécifier le fichier en entrée");
}

$contents = @file_get_contents($input);
if ($contents === false) error_die("Erreur à la lecture de $input");

$data = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
if ($v33Format) {
  $version = $data["pegase-version"];
} else {
  $version = $data;
}

[
  "majeure" => $majeure,
  "mineure" => $mineure,
  "patch" => $patch,
  "prerelease" => $prerelease,
] = $version;

switch ($output) {
case "env";
  echo "majeure=".escapeshellarg($majeure)."\n";
  echo "mineure=".escapeshellarg($mineure)."\n";
  echo "patch=".escapeshellarg($patch)."\n";
  echo "prerelease=".escapeshellarg($prerelease)."\n";
  break;
case "json";
  echo json_encode([
    "majeure" => $majeure,
    "mineure" => $mineure,
    "patch" => $patch,
    "prerelease" => $prerelease,
  ]);
  break;
}
