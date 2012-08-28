<?
/**
* Project: µm2 model2 framework
*
* @author David Brännvall, Jonatan 'jaw' Wallmander.
*        Copyright 2011 HR North Sweden AB http://hrnorth.se
*        Copyright 2011 Vovoid Media Technologies http://vovoid.com/um2
* @see The GNU Public License (GPL)
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
* or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
* for more details.
*
* You should have received a copy of the GNU General Public License along
* with this program; if not, write to the Free Software Foundation, Inc.,
* 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/

class uf_debug {
  private static $_enabled = TRUE;
  
  public static function set_enabled($enabled)
  {
    uf_debug::$_enabled = $enabled;
  }
  
  public static function process_error_backtrace($errno, $errstr, $errfile, $errline, $errcontext) {
    if(!(error_reporting() & $errno))
      return;

    switch($errno) {
      case E_WARNING      :
      case E_USER_WARNING :
      case E_STRICT       :
      case E_NOTICE       :
      case E_USER_NOTICE  :
        $type = 'warning';
        $fatal = false;
        break;
      default             :
        $type = 'fatal error';
        $fatal = true;
        break;
    }
    $trace = array_reverse(debug_backtrace());
    array_pop($trace);
    if(php_sapi_name() == 'cli') {
      echo 'Backtrace from ' . $type . ' \'' . $errstr . '\' at ' . $errfile . ' ' . $errline . ':' . "\n";
      foreach($trace as $item)
        echo '  ' . (isset($item['file']) ? $item['file'] : '<unknown file>') . ' ' . (isset($item['line']) ? $item['line'] : '<unknown line>') . ' calling ' . $item['function'] . '()' . "\n";
    } else {
      if(uf_debug::$_enabled)
      {
        echo '<p class="error_backtrace">' . "\n";
        echo '  Backtrace from ' . $type . ' \'' . $errstr . '\' at ' . $errfile . ' ' . $errline . ':' . "\n";
        echo '  <ol>' . "\n";
        foreach($trace as $item)
          echo '    <li>' . (isset($item['file']) ? $item['file'] : '<unknown file>') . ' ' . (isset($item['line']) ? $item['line'] : '<unknown line>') . ' calling ' . $item['function'] . '()</li>' . "\n";
        echo '  </ol>' . "\n";
        echo '</p>' . "\n";        
      }
    }
    if(ini_get('log_errors')) {
      $items = array();
      foreach($trace as $item)
        $items[] = (isset($item['file']) ? $item['file'] : '<unknown file>') . ' ' . (isset($item['line']) ? $item['line'] : '<unknown line>') . ' calling ' . $item['function'] . '()';
      $message = 'Backtrace from ' . $type . ' \'' . $errstr . '\' at ' . $errfile . ' ' . $errline . ': ' . join(' | ', $items);
      error_log($message);
    }
    if($fatal)
      exit(1);
  }
  
}

set_error_handler(array('uf_debug', 'process_error_backtrace'));

