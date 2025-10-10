<?php
/** @var array $_PAGE */
if ($_PAGE['extension'] !== 'xsl') {
	redirect(array('params'=>'style', 'ext'=>'xsl'));
}
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<xsl:stylesheet version="1.0"
	xmlns:sm="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:mobile="http://www.google.com/schemas/sitemap-mobile/1.0"
	xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
	xmlns:video="http://www.google.com/schemas/sitemap-video/1.1"
	xmlns:news="http://www.google.com/schemas/sitemap-news/0.9" xmlns:fo="http://www.w3.org/1999/XSL/Format"
	xmlns="http://www.w3.org/1999/xhtml">
	<xsl:output method="html" indent="yes" encoding="UTF-8" />
	<xsl:template match="/">
		<html>
			<head>
				<title>Corahn-Rin Sitemap</title>
				<style type="text/css">
					* {margin: 0; padding: 0;}
					body {
						color: #D7C6B1;
						background: url('<?php echo BASE_URL; ?>/img/wrap_fond.jpg') black center repeat-y;
						font: 12px "Verdana", "Helvetica", sans-serif;
						margin:0;
						padding: 5px;
						text-align:center;
					}
					#container{
						margin:auto;
						width:900px;
						text-align:left;
					}
					a {
						font-size: 11px;
						color: #eee;
						text-decoration: none;
					}
					a:hover {
						color: #27c0db;
					}
					h1 {
						padding:20px;
						color:#ffffff;
						text-align:left;
						font-size:32px;
						margin:0px;
					}
					h3 {
						font-size:12px;
						margin:0px;
						padding:10px;
					}
					h3 a {
						float:right;
						font-weight:normal;
						display:block;
					}
					.table_container {
						width: 100%;
						border: solid 1px #303030;
					}
					table {
						width: 100%;
						margin: 1px;
						border-collapse: collapse;
					}
					th {
						background: url('<?php echo BASE_URL; ?>/img/cat_fond.jpg');
						text-align:center;
						color:#fff;
						padding:4px;
						font-weight:normal;
						font-size:12px;
					}
					tr:hover td {
						background-color: rgba(0,0,0,0.6);
					}
					td {
						background-color: rgba(0,0,0,0.5);
						font-size:12px;
						padding:2px 4px;
						text-align:left;
						border: solid 1px transparent;
					}

					.url2 {
						text-align:right;
					}
					#footer {
						padding:10px;
					}
				</style>
			</head>
			<body>
				<div id="container">
					<h1>Corahn-Rin Sitemap</h1>
					<h3>
						Total :
						<xsl:value-of select="count(sm:urlset/sm:url)" />
					</h3>
					<xsl:apply-templates />
					<div id="footer">
						Created with Pierstoval's Corahn-Rin Generator
					</div>
				</div>
			</body>
		</html>
	</xsl:template>
	<xsl:template match="sm:sitemapindex">
		<table cellpadding="0" cellspacing="0" width="100%">
			<tr>
				<th>#</th>
				<th>URL</th>
				<th>Last Modified</th>
			</tr>
			<xsl:for-each select="sm:sitemap">
				<tr>
					<xsl:variable name="loc">
						<xsl:value-of select="sm:loc" />
					</xsl:variable>
					<xsl:variable name="pno">
						<xsl:value-of select="position()" />
					</xsl:variable>
					<td>
						<xsl:value-of select="$pno" />
					</td>
					<td>
						<a href="{$loc}">
							<xsl:value-of select="sm:loc" />
						</a>
					</td>
					<xsl:apply-templates />
				</tr>
			</xsl:for-each>
		</table>
	</xsl:template>
	<xsl:template match="sm:urlset">
		<div class="table_container">
			<table cellpadding="0" cellspacing="0" width="100%">
				<tr>
					<th></th>
					<th>Url</th>
					<xsl:if test="sm:url/sm:lastmod">
						<th>Last Modified</th>
					</xsl:if>
					<xsl:if test="sm:url/sm:changefreq">
						<th>Change</th>
					</xsl:if>
					<xsl:if test="sm:url/sm:priority">
						<th>Priority</th>
					</xsl:if>
				</tr>
				<xsl:for-each select="sm:url">
					<tr>
						<xsl:variable name="loc">
							<xsl:value-of select="sm:loc" />
						</xsl:variable>
						<xsl:variable name="pno">
							<xsl:value-of select="position()" />
						</xsl:variable>
						<td>
							<xsl:value-of select="$pno" />
						</td>
						<td>
							<a href="{$loc}">
								<xsl:value-of select="sm:loc" />
							</a>
						</td>
						<xsl:apply-templates select="sm:*" />
					</tr>
					<xsl:apply-templates select="image:*" />
					<xsl:apply-templates select="video:*" />
				</xsl:for-each>
			</table>
		</div>
	</xsl:template>
	<xsl:template match="sm:loc|image:loc|image:caption|video:*"></xsl:template>
	<xsl:template match="sm:lastmod|sm:changefreq|sm:priority">
		<td>
			<xsl:apply-templates />
		</td>
	</xsl:template>
</xsl:stylesheet>
