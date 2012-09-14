#!/usr/bin/env php
<?
define('UF_BASE',realpath(dirname(__FILE__).'/../..'));
require_once(UF_BASE.'/core/umvc.php');


$command = '';
$sites_to_bake = array();
$baking_sites = array('fallback');

echo "\n";
echo "*****************************\n";
echo "*****************************\n";
echo "***  µm2 off line baker *****\n";
echo "*****************************\n";
echo "*****************************\n";
echo "\n";

$_SERVER['SERVER_NAME'] = 'www.foo.bar';

// first init
uf_application::init_config();

echo "Loading baker's plugins:\n";
uf_baker::load_plugins();

echo "\n*****************************\n\n";
echo "Scanning for hosts that can be baked...";
// finding all the available hosts
$hosts_dir = UF_BASE.uf_application::app_dir(FALSE).'/sites/hosts/';
$dir_obj = dir($hosts_dir);
while (false !== ($entry = $dir_obj->read()))
{
  $s = trim($entry);
  if (
    $s != '.' &&
    $s != '..' &&
    is_dir($hosts_dir.$entry) &&
    $s != 'FALLBACK'
  )
  {
    array_push( $baking_sites , $s);
  }
}

echo " Done, found ".count($baking_sites)." sites.\n\n*****************************\n\n";


if ($_SERVER['argc'] > 1)
{
  // take our command from argv[1]
  $command = $_SERVER['argv'][1];
}
else
{
  echo "------------------------------------------------------------------------------\n";
  echo "-- MAIN MENU -----------------------------------------------------------------\n";
  echo "------------------------------------------------------------------------------\n";
  $i = 1;
  foreach ( $baking_sites as $bs)
  {
    echo $i.'.    '.$bs."\n";
    $i++;
  }
  echo "------------------------------------------------------------------------------\n";
  echo "  - you can select multiple sites like so: >1,2,4,8\n";
  echo "  - you can call this script directly: \"./bake.php 1,2,3\"\n";
  echo "  - * bake all\n";
  echo "  - q quit\n";
  echo "------------------------------------------------------------------------------\n";
  echo ">";
  $f = fopen( 'php://stdin', 'r' );

  $command = fgets( $f );
}

// parse the command
switch ( trim( strtolower( $command ) ) )
{
  case '':
  case 'q':
    {
      die();
    }
  break;
  
  case '*':
    {
      for ($i = 0; $i < count($baking_sites); $i++)
      {
        array_push($sites_to_bake, $i+1 );
      }
    }
  break;
  
  default:
    {
      $sites_to_bake = explode(',', trim($command) );
    }
  break;
}

foreach ($sites_to_bake as $s)
{
  if (! isset($baking_sites[$s-1]) ) continue;
  if ($baking_sites[$s-1] == 'fallback')
  {
    $_SERVER['SERVER_NAME'] = 'www.um2um2um2um2.com';
    echo "Baking FALLBACK\n\n";
  }
  else
  {
    $_SERVER['SERVER_NAME'] = $baking_sites[$s-1];
    echo 'Baking "'.$_SERVER['SERVER_NAME']."\"\n\n";
  }
  
  uf_application::init_config();
  uf_baker::bake_all();
  echo "\n*****************************\n\n";
}

