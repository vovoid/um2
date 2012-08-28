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


define('UF_BASE',realpath(dirname(__FILE__).'/../..'));
require_once(UF_BASE.'/core/umvc.php');
uf_application::init();

header('Content-Type: text/javascript');
if (!uf_application::get_config('dev'))
{
  $expires = 60*60*24*14;
  header("Pragma: public");
  header("Cache-Control: public, max-age=".$expires);
  header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT');
}

$static_js_file = uf_baker::get_baked_static_dir().'/js/baked.js';
$dynamic_js_file = uf_baker::get_baked_cache_dir().'/js/baked.js.php';

echo @file_get_contents($static_js_file)."\n";
include_once($dynamic_js_file);

?>
