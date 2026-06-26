<?php

function debug_mode($mode = DEBUG) {
  if($mode) {
    // Report all PHP errors
    error_reporting(E_ALL);
  }
  else {
    // Turn off all PHP error reporting
    error_reporting(0);
  }
}

?>