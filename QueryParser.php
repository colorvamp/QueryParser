<?php

/**
 * Utility class for converting a stringified JSON object into a where-clause
 * fragment.
 * by: Solodev - Shawn Moore, Ross Williams, Allen Johnson
 * webpage: http://www.ocoa.com
 */
class QueryParser
{
  /**
   * Primary entry point. Given a JSON formatted string, return a SQL where
   * clause fragment.
   *
   * @param \string $query_str JSON formatted string
   * @return \string Returns a where clause fragment
   */
  public static function parse($query_str) {
    return QueryParser::toFragment(json_decode($query_str));
  }

  private static function toFragment($values, $op='and') {
    $res = '';

    if (is_object($values)) {
      $res = QueryParser::objectToFragment($values);
    } else if (is_array($values)) {
      $res = QueryParser::arrayToFragment($values, $op);
    }

    return $res;
  }

  private static function arrayToFragment($array, $op='and') {
    $len = count($array);
    $res = '';

    foreach ($array as $idx => $val) {
      $res .= QueryParser::toFragment($val);
      if ($idx < $len - 1) {
        $res .= ' ' . $op . ' ';
      }
    }

    return $res;
  }

  private static function objectToFragment($obj, $op='and') {
    $arr = (array) $obj;
    $len = count($arr);
    $res = '';

    if ($len > 0) {
      $res .= '(';

      foreach (array_keys($arr) as $i => $key) {
        $val = $arr[$key];

        if ('$or' === $key) {
          $res .= QueryParser::toFragment($val, 'or');
        } else if ('$and' === $key) {
          $res .= QueryParser::toFragment($val, 'and');
        } else {
          if (is_object($val)) {
            switch (key($val)) {
              case '$elemMatch':
                $res .= QueryParser::toFragment($val, 'and');
                break;
              case '$regex':
                $res .= sprintf("%s REGEXP '%s'", $key, $val->{key($val)});
                break;
              case '$ne':
                $res .= sprintf("%s != '%s'", $key, $val->{key($val)});
                break;
            }
          } else {
          	$GLOBALS[$key] = $val;
            $res .= "$key = '$val'";
          }
        }

        if ($i < $len - 1) { $res .= ' and '; }
      }

      $res .= ')';
    }

    return $res;
  }
}
