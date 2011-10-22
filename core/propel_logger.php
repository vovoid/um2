<?
/**
 * Project: umvc: A Model View Controller framework
 *
 * @author David Brännvall, Jonatan Wallmander, HR North Sweden AB http://hrnorth.se, Copyright (C) 2011.
 * @see The GNU Public License (GPL)
 */
/*
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

class MyLogger implements BasicLogger
{
  public function emergency($m)
  {
    $this->log($m, Propel::LOG_EMERG);
  }
  public function alert($m)
  {
    $this->log($m, Propel::LOG_ALERT);
  }
  public function crit($m)
  {
    $this->log($m, Propel::LOG_CRIT);
  }
  public function err($m)
  {
    $this->log($m, Propel::LOG_ERR);
  }
  public function warning($m)
  {
    $this->log($m, Propel::LOG_WARNING);
  }
  public function notice($m)
  {
    $this->log($m, Propel::LOG_NOTICE);
  }
  public function info($m)
  {
    $this->log($m, Propel::LOG_INFO);
  }
  public function debug($m)
  {
    $this->log($m, Propel::LOG_DEBUG);
  }
  
  public function log($message, $severity = null)
  {
    $color = $this->priorityToColor($severity);
    error_log($message);
    //echo '<p style="color: ' . $color . '">'.$message.'</p>';
  }
  
  private function priorityToColor($priority)
  {
    switch($priority) {
      case Propel::LOG_EMERG:
      case Propel::LOG_ALERT:
      case Propel::LOG_CRIT:
      case Propel::LOG_ERR:
        return 'red';
        break;
      case Propel::LOG_WARNING:
        return 'orange';
        break;
      case Propel::LOG_NOTICE:
        return 'green';
        break;
      case Propel::LOG_INFO:
        return 'blue';
        break;
      case Propel::LOG_DEBUG:
        return 'grey';
        break;
    }
  }
}