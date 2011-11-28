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

class uf_session
{
  static public function has($name, $namespace = 'global')
  {
    return isset($_SESSION[$namespace][$name]);
  }
  
  static public function get($name, $default_value = '', $namespace = 'global')
  {
    return isset($_SESSION[$namespace][$name]) ? $_SESSION[$namespace][$name] : $default_value;
  }

  static public function set($name, $value = NULL, $namespace = 'global')
  {
    if($value === NULL)
    {
      unset($_SESSION[$namespace][$name]);
    }
    else
    {
      $_SESSION[$namespace][$name] = $value;
    }
    return $value;
  }

  static public function has_namespace($namespace)
  {
    return isset($_SESSION[$namespace]);
  }

  static public function clear_namespace($namespace)
  {
    unset($_SESSION[$namespace]);
  }

  // Value for both saving, getting and checking existence of values in the session.
  // Try to only use strings.
  // How to use:
  //   uf_session::value('foo');       // return value or NULL if not set
  //   uf_session::value('foo',NULL);  // remove value from session (if found)
  //   uf_session::value('foo','bar'); // set value of foo to 'bar'
  //
  // NOTE(!):
  //   Since 0xFFFFFFFF is used as a key, use '4294967295' or (string)0xFFFFFFFF
  //   when setting this particular value into the session.
  static public function value($name, $value = 0xFFFFFFFF, $namespace = 'global')
  {
    if ($value === 0xFFFFFFFF)
    {
      if (isset($_SESSION[$namespace][$name]))
      {
        return $_SESSION[$namespace][$name];
      } else
      {
        return NULL;
      }
    } else
    if ($value === NULL)
    {
      //
      if (isset($_SESSION[$namespace][$name]))
      {
        unset($_SESSION[$namespace][$name]);
      }
    } else
    {
      $_SESSION[$namespace][$name] = $value;
    }
  }
}

if (uf_application::get_config('automatic_session_start',1))
session_start();

/* EOF */