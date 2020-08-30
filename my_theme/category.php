<?php
	if(have_posts())while(have_posts()){the_post();

	echo "
<article>
	<h1>" .
	     get_the_title()
	     ."</h1><div>" /*.
	     get_the_post_thumbnail()*/
	."</div><div>" .
		get_the_content()
	."</div>
	<small>".get_the_date()."</small>
	
</article>";

}

wp_reset_postdata();