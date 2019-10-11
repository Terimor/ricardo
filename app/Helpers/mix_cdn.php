<?php


if (!function_exists('mix_cdn')) {

  function mix_cdn($path, $manifestDirectory = '') {
    $mixPath = mix($path, $manifestDirectory);

    $cdnUrl = env('ENVIRONMENT') === 'production'
      ? \Utils::IMAGE_HOST_PRODUCTION
      : \Utils::IMAGE_HOST_STAGING;
/*
    if (env('ENVIRONMENT') !== 'development') {
      $mixPath = 'https://' . $cdnUrl . $mixPath;
    }
*/
    return $mixPath;
  }

}
