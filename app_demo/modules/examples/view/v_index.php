<h1><?=$language['base']['menu']['examples']?></h1>

<p><?=$language['examples']['text1']?></p>
<p><?=$language['examples']['text2']?></p>

<ul>

  <li><a href="<?=$uf_view->cap('examples','sub-views')?>">Sub views</a></li>
  <li><a href="<?=$uf_view->cap('routing-example-using-special-url')?>">Routing (special, follows language)</a></li>
  <li><a href="/routing-example-using-special-url">Routing (special, overrides language)</a></li>
  <li><a href="<?=$uf_view->cap('examples','error')?>">Returning error from an action</a></li>
  <li><a href="<?=$uf_view->cap('examples','before-action')?>">The before_action() method</a></li>
  <li><a href="<?=$uf_view->cap('examples','form-validation')?>">Form validation</a></li>
  <li><a href="<?=$uf_view->cap('examples','todo-list')?>">Todo list</a></li>
  <li><a href="<?=$uf_view->cap('examples','language')?>">Language support / performing translations for module names, actions, parameters and routing and in views</a></li>
  <li><a href="<?=$uf_view->cap('examples','translation')?>">I10n</a></li>
  <li><a href="<?=$uf_view->cap('examples','debug')?>">Debug</a></li>
  <li><a href="<?=$uf_view->cap('examples','no-view')?>">No view</a></li>
  <li><a href="<?=$uf_view->cap('examples','other-view')?>">Other view</a></li>
  <li><a href="<?=$uf_view->cap('examples','javascript')?>">JavaScript</a></li>
  <li><a href="<?=$uf_view->cap('examples','lightbox')?>">Lightbox</a></li>
</ul>

<p>Foo: <?=$foo?></p>
