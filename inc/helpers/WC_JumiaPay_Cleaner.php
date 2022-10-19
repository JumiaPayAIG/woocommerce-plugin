<?php

class WC_JumiaPay_Cleaner
{
  /**
   * It removes empty values from an array.
   * 
   * @param data The data to be filtered.
   * 
   * @return an array of data that is not null.
   */
  public static function filterNotNull($data) {
    $data = array_map(function($item) {
        return is_array($item) ? WC_JumiaPay_Cleaner::filterNotNull($item) : $item;
    }, $data);
    return array_filter($data, function($item) {
        return $item !== "" && $item !== null && (!is_array($item) || count($item) > 0);
    });
  }
}
