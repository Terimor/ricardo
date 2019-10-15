<?php


if (!function_exists('mix_cdn')) {

  function mix_cdn($path, $manifestDirectory = '') {
    $mixPath = mix($path, $manifestDirectory);
    $env = \App::environment();

    if ($env !== 'development') {
      $mixPath = \Utils::getCdnUrl() . $mixPath;
    }

    return $mixPath;
  }

}
