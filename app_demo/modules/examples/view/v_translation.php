<h1>Internationalization</h1>
<p>
µm2 provides 2 methods of maintaining string content in multiple languages.
You have probably already seen the $language arrays.<br />
<br />
This example demonstrates the more advanced and feature-rich "baked language engine".<br />
<br />
It supports parameters, namespaces, overriding namespaces etc.<br />
<br />
The benefits are many, especially in the views - you can just write:
<pre>
  t('NAME');
</pre>
in the PHP code.
</p>

<h2>Translate</h2>
<?=t('ID_01');?>

<h2>Translate with arguments</h2>
<?=t('ID_02', array('@name' => '<b>filtered</b>', '!code' => '<b>not filtered</b>'));?>

<h2>Translate override namespace</h2>
<?=t('ID_01', NULL, 'override_hello');?>
