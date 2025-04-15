<?php

if (!function_exists('formatirajCijenu')) {
  /**
   * Formatira integer za prikaz cijene
   *
   * @param float $cijena
   * @param int $decimale
   * @param string $decimalniSeparator
   * @param string $separatorHiljada
   * 
   * @return string
   */
  function formatirajCijenu($cijena, $decimale = 2, $decimalniSeparator = ',', $separatorHiljada = '.'): string
  {
    return number_format(round($cijena / 100, $decimale), $decimale, $decimalniSeparator, $separatorHiljada);
  }
}