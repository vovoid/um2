<?
define('UF_BASE',realpath(dirname(__FILE__).'/../..'));
require_once(UF_BASE.'/core/umvc.php');

echo "\n";
echo "*****************************\n";
echo "*****************************\n";
echo "***  µm2 off line baker *****\n";
echo "*****************************\n";
echo "*****************************\n";
echo "\n";
echo "Are you sure you want to bake?\n";
echo "Y/N:";
$f = fopen( 'php://stdin', 'r' );

$line = fgets( $f );
echo $line;
if ( trim(strtolower($line)) != 'y' )
{
  echo "Quitting...\n";
  die();
}


$_SERVER['SERVER_NAME'] = 'www.foo.bar';

// first init
uf_application::init_config();

echo "Loading baker's plugins:\n";
uf_baker::load_plugins();

echo "\n*****************************\n\n";

// let's bake our FALLBACK dir
echo "Baking FALLBACK:\n";
uf_baker::bake_all();
echo "\n*****************************\n\n";

$hosts_dir = UF_BASE.uf_application::app_dir(FALSE).'/sites/hosts/';

echo 'Baking all hosts in:
   '.$hosts_dir."\n";
echo "\n*****************************\n\n";
$dir_obj = dir($hosts_dir);

while (false !== ($entry = $dir_obj->read())) {
  $s = trim($entry);
  if (
    $s != '.' &&
    $s != '..' &&
    is_dir($hosts_dir.$entry) &&
    $s != 'FALLBACK'
  )
  {
    $_SERVER['SERVER_NAME'] = $s;
    echo 'Baking "'.$_SERVER['SERVER_NAME']."\"\n\n";
    uf_application::init_config();
    uf_baker::bake_all();
    echo "\n*****************************\n\n";
  }
}

