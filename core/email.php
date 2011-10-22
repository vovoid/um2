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

// Cleans up and verifies an e-mail (with MX pointer)
// returns '' if it fails
// otherwise a valid e-mail address
function sanitize_email($email)
{
  // validate the syntax of the e-mail adress
  $s_email = filter_var($email, FILTER_SANITIZE_EMAIL);
  if(!filter_var($s_email, FILTER_VALIDATE_EMAIL)) {
    return '';
  }
  // perform recursive dns query to validate the domain
  $domain = explode('@',$s_email);
  $domain = $domain[1];
  if (getmxrr($domain, $mxhosts) === true)
  return $s_email;
  return '';
}
