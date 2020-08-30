<?php

$cat1 = get_category_by_slug("news_finance");
$cat2 = get_category_by_slug("news_entertainment");
//$d1 = parse_rss_fin("https://finance.yahoo.com/rss/");
?>

<ul>
	<a href="<?=get_category_link( $cat1->cat_ID)?>">Финансы </a>
</ul>
<ul>
	<a href="<?=get_category_link( $cat2->cat_ID)?>">Развлечения </a>
</ul>

