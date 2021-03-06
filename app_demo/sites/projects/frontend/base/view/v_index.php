<?='<?'?>xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
  "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="sv">
<head>
  <title><?=@$title?></title>	
  <meta name="description" content="<?=@$meta_description?>" />
  <meta name="keywords" content="<?=@$meta_keywords?>" />
  <link rel="alternate" type="application/rss+xml" title="" href="/rss" />
  <link rel="stylesheet" type="text/css" media="screen" href="/css/main.css.php" />
  <script src="/js/main.js.php" type="text/javascript" charset="utf-8"></script>
  <script type="text/javascript" charset="utf-8">
    <?=$uf_response->javascript()?>
  </script>
</head>
<body>
  <div id="center">
    <div id="header"><a href="/"><img src="<?=$uf_dir_web_lib?>/images/umvc.gif" alt="UMVC php web framework" /></a></div>
    <ul id="menu">
      <li<? if(@$mainmenu === 'start') echo ' class="selected"'; ?>><a href="<?=$uf_view->cap('')?>"><?=$language['base']['menu']['start']?></a></li>
      <li<? if(@$mainmenu === 'examples') echo ' class="selected"'; ?>><a href="<?=$uf_view->cap('examples')?>"><?=$language['base']['menu']['examples']?></a></li>
      <li<? if(@$mainmenu === 'about') echo ' class="selected"'; ?>><a href="<?=$uf_view->cap('about')?>"><?=$language['base']['menu']['about']?></a></li>
      <li<? if(@$mainmenu === 'contact') echo ' class="selected"'; ?>><a href="<?=$uf_view->cap('contact')?>"><?=$language['base']['menu']['contact']?></a></li>
      <li<? if(@$mainmenu === 'language') echo ' class="selected"'; ?>><a href="<?=$uf_view->cap('language')?>"><?=$language['base']['menu']['language']?></a></li>
    </ul>
    <div id="content">
      <?=$content;?>
    </div>
    <div id="footer">Yoyo!</div>
  </div>
</body>
</html>