<?php

namespace csv-utils;

$args = new \cli\Arguments();

$args->addOption(array('input-file', 'f'), array(
  'default' => STDIN,
  'description' => 'File to act on. Alternate to pipe to STDIN',
));

$args->parse();
$input = getOption('input-file', $args);

/**
 * Utility to get user option or it's default.
 *
 * @param string $opt option name
 * @param array $args cli\Arguments object
 * @return mixed
 */
function getOption($opt, $args) {
  $user = $args->getArguments();
  if ($args[$opt]) {
    $val = $user[$opt];
  } else {
    $arg = $args->getOption($opt);
    $val = ( key_exists('default', $arg) ) ? $arg['default'] : null;
  }
  return $val;
}

/**
 * Wrap php exec function with some helpers.
 * Returns output of command.
 *
 * @param type $call system call
 * @param string $chDir execute command in this dir
 * @return string
 */
function shell($call, $chDir=NULL) {
  if ($chDir) {
    $cacheDir = getcwd();
    chdir(CIVICRM_ROOT);
  }
  exec($call, $output, $return_var);

  if ($chDir) {
    chdir($cacheDir);
  }

  return join($output);
}

