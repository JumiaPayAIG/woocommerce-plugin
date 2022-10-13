<?php

class WC_JumiaPay_Cleaner
{
  public static function filterNotNull($data) {
    $data = array_map(function($item) {
        return is_array($item) ? WC_JumiaPay_Cleaner::filterNotNull($item) : $item;
    }, $data);
    return array_filter($data, function($item) {
        return $item !== "" && $item !== null && (!is_array($item) || count($item) > 0);
    });
  }
}
