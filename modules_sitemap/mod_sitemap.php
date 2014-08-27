<?php
if ($_PAGE['extension'] !== 'xml') {
	redirect(array('ext' => 'xml'));
}
//Titre
echo '<?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet type="text/xsl" href="'.mkurl(array('params'=>'style', 'ext'=>'xsl')).'"?>';
?>

<urlset
	xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xsi:schemaLocation="
		http://www.sitemaps.org/schemas/sitemap/0.9
		http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
<!-- created with Corahn-Rin's sitemap module -->

<url>
	<loc><?php echo BASE_URL.P_LANG; ?></loc>
	<lastmod><?php echo date('c', filemtime(ROOT.DS.'versions.xml')); ?></lastmod>
	<changefreq>weekly</changefreq>
	<priority>1.0000</priority>
</url>

<?php
$step = $db->row('SELECT %gen_mod FROM %%steps WHERE %gen_step = 1');
$lastmod = filemtime(ROOT.DS.'modules_'.$_PAGE['list'][62]['page_getmod'].DS.'mod_'.$step['gen_mod'].'.php');
?>
<url>
	<loc><?php echo mkurl(array('val'=>62, 'params'=>$step['gen_mod'])); ?></loc>
	<lastmod><?php echo date('c', $lastmod); ?></lastmod>
	<changefreq>weekly</changefreq>
	<priority>0.7500</priority>
</url>

<?php

clearstatcache();
// Pages du site
foreach ($_PAGE['list'] as $page_id => $page) {
	if (($page['page_show_in_menu'] === 1 && $page['page_show_in_debug'] === 0 && $page['page_require_login'] === 0)
		|| $page_id == 48
		|| $page_id == 56) {
		$lastmod = filemtime(ROOT.DS.'modules'.DS.'mod_'.$page['page_getmod'].'.php');
		$lastmod = date('c', $lastmod);
		?>

<url>
	<loc><?php echo mkurl(array('val'=>$page['page_id'])); ?></loc>
	<lastmod><?php echo $lastmod; ?></lastmod>
	<changefreq>monthly</changefreq>
	<priority>1.0000</priority>
</url>

	<?php
	}
}

// Personnages
$chars = $db->req('SELECT %char_id, %char_name, %char_date_creation, %char_date_update, %char_name FROM %%characters');
foreach ($chars as $k => $char) {
	$lastmod = $char['char_date_update'] ? $char['char_date_update'] : $char['char_date_creation'];
	$lastmod = date('c', $lastmod);
	?>

<url>
	<loc><?php echo mkurl(array('val'=>47, 'params'=>$char['char_id'])); ?></loc>
	<lastmod><?php echo $lastmod; ?></lastmod>
	<changefreq>weekly</changefreq>
	<priority>0.7500</priority>
</url>

<url>
	<loc><?php echo mkurl(array('val'=>49, 'params'=>array($char['char_id'], 'zip'=>1, $char['char_name']), 'ext'=>'zip')); ?></loc>
	<lastmod><?php echo $lastmod; ?></lastmod>
	<changefreq>weekly</changefreq>
	<priority>0.5000</priority>
</url>

<url>
	<loc><?php echo mkurl(array('val'=>49, 'params'=>array($char['char_id'], 'pdf'=>1, $char['char_name']), 'ext'=>'pdf')); ?></loc>
	<lastmod><?php echo $lastmod; ?></lastmod>
	<changefreq>weekly</changefreq>
	<priority>0.5000</priority>
</url>
<url>
	<loc><?php echo mkurl(array('val'=>49, 'params'=>array($char['char_id'], 'pdf'=>1,'print'=>1, $char['char_name']), 'ext'=>'pdf')); ?></loc>
	<lastmod><?php echo $lastmod; ?></lastmod>
	<changefreq>weekly</changefreq>
	<priority>0.5000</priority>
</url>

<url>
	<loc><?php echo mkurl(array('val'=>49, 'params'=>array($char['char_id'], 'page'=>1, $char['char_name']), 'ext'=>'jpg')); ?></loc>
	<lastmod><?php echo $lastmod; ?></lastmod>
	<changefreq>weekly</changefreq>
	<priority>0.5000</priority>
</url>
<url>
	<loc><?php echo mkurl(array('val'=>49, 'params'=>array($char['char_id'], 'page'=>2, $char['char_name']), 'ext'=>'jpg')); ?></loc>
	<lastmod><?php echo $lastmod; ?></lastmod>
	<changefreq>weekly</changefreq>
	<priority>0.5000</priority>
</url>
<url>
	<loc><?php echo mkurl(array('val'=>49, 'params'=>array($char['char_id'], 'page'=>2, $char['char_name']), 'ext'=>'jpg')); ?></loc>
	<lastmod><?php echo $lastmod; ?></lastmod>
	<changefreq>weekly</changefreq>
	<priority>0.5000</priority>
</url>

<url>
	<loc><?php echo mkurl(array('val'=>49, 'params'=>array($char['char_id'], 'page'=>1, 'print'=>1, $char['char_name']), 'ext'=>'jpg')); ?></loc>
	<lastmod><?php echo $lastmod; ?></lastmod>
	<changefreq>weekly</changefreq>
	<priority>0.5000</priority>
</url>
<url>
	<loc><?php echo mkurl(array('val'=>49, 'params'=>array($char['char_id'], 'page'=>2, 'print'=>1, $char['char_name']), 'ext'=>'jpg')); ?></loc>
	<lastmod><?php echo $lastmod; ?></lastmod>
	<changefreq>weekly</changefreq>
	<priority>0.5000</priority>
</url>
<url>
	<loc><?php echo mkurl(array('val'=>49, 'params'=>array($char['char_id'], 'page'=>2, 'print'=>1, $char['char_name']), 'ext'=>'jpg')); ?></loc>
	<lastmod><?php echo $lastmod; ?></lastmod>
	<changefreq>weekly</changefreq>
	<priority>0.5000</priority>
</url>

	<?php
}

/*
<url>
	<loc><?php echo mkurl(array('val'=>45, 'params'=>'fr')); ?></loc>
	<lastmod><?php echo $lastmod; ?></lastmod>
	<changefreq>weekly</changefreq>
	<priority>0.1000</priority>
</url>
<url>
	<loc><?php echo mkurl(array('val'=>45, 'params'=>'en')); ?></loc>
	<lastmod><?php echo $lastmod; ?></lastmod>
	<changefreq>weekly</changefreq>
	<priority>0.1000</priority>
</url>
*/
?>

</urlset>