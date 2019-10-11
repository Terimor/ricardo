<?php


if (!function_exists('mix_cdn')) {

  function mix_cdn($path, $manifestDirectory = '') {
    $mixPath = mix($path, $manifestDirectory);

    if (env('ENVIRONMENT') !== 'development') {
      $mixPath = \Utils::getCdnUrl() . $mixPath;
    }

    return $mixPath;
  }

}
