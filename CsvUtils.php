<?php

namespace csv-utils;

class CsvUtils {

  var $fieldDefinition = array();
  var $parsed = array();

  var $preProcessFunction = 'preProcess';
  var $postProcessFunction = 'postProcess;

  /**
   * e.g. - myCallback($line, $index) {...}; should return parsed result
   **/ 
  var $itemFunction = '$this->_item';

  /**
   * $dontMask Optional. Remove items from character_mask argument to trim() 
   * e.g. array("\n", "\t"); Note double-quotes.
   **/
  var $dontMask = array();

  public function __construct($options = array() {

  }

  function _item($row) {
    return $this->itemAsJson($row);
  }

  function itemAsJson($row) {
    if (!$row) return;
    return json_encode($row);
  }

  function process($file) {

    if (function_exists($this->preProcessFunction)) $this->preProcessFunction();

    $this->validateFileResource($file);

    $dontMask = array_fill_keys($this->$dontMask, '');
    $charmask = strtr(" \t\n\r\0\x0B", $dontMask);

    $n=0;
    while (($line = fgets($file)) !== FALSE) {
      $this->getItemFunction()(trim($line, $charmask), $n);
      $n++;
    }

    if (function_exists($this->postProcessFunction)) $this->postProcessFunction();

    $this->closeFileResource($file); 
  }

  function getItemFunction($function_name) {
    if (function_exists($function_name)) {
      return $function_name;
    } elseif ( $function_name == "$this->_item" ) {
      return $function_name;
    } else {
      throw new Exception("function name, \"$function_name\" does not exist;");
    }
  }

  function validateFileResource(&$file) {
    if ( !is_resource($file)) {
      //try to open it
      $file = fopen($file, 'r');
    } else if (get_resource_type($file) !== "stream") {
      throw new Exception("Resources is not a file.");
    }
  }

  function closeFileResource(&$file) {
    if (!is_resource($file)) return;

    $meta = stream_get_meta_data($file);
    if (!$meta['uri'] != 'php://stdin') {
      fclose($file);
    }
  }

  /**
   * Loop Callback utility to create an array from a csv file.
   * Associative array if $index and $fieldDefinition are provided.
   *
   * @param string $line
   * @param int $index
   * @param array $fieldDefinition keys
   * @return array
   */
  function parseCsv($line, $index=NULL, &$fieldDefinition=NULL) {
    $values = str_getcsv($line, ',', '"');
    if ($index === 0) {
      $fieldDefinition = $values;
    } else if ($fieldDefinition) {
      if (count($fieldDefinition) == count($values)) {
        return array_combine($fieldDefinition, $values);
      } else {
        cli\err("field definition did not match:\n". var_export($values, TRUE));
      }
    } else {
      return $values;
    }
  }
}
