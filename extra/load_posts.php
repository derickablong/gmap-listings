<?php 
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$query = new WP_Query(array(
	'cat'				=> 	$atts['category_id'],
	'posts_per_page'	=>	$atts['posts_per_page'],
	'paged'				=>	$paged
));
if( $query->have_posts() ){
	while( $query->have_posts() ){ $query->the_post();
	?>
	<article id="custom_posts_category" <?php post_class()  ?>>
		<header class="entry-header">
			<h1 class="entry-title" itemprop="headline">
				<a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a>
			</h1> 
			<p class="entry-meta">
				<time class="entry-time" itemprop="datePublished" datetime="<?php the_time('Y-m-d m:sa') ?>"><?php the_time('M d, Y') ?></time>
				 By <span class="entry-author" itemprop="author" itemscope="" itemtype="http://schema.org/Person"><a href="<?php the_author_link() ?>" class="entry-author-link" itemprop="url" rel="author"><span class="entry-author-name" itemprop="name"><?php the_author() ?></span></a></span>
				 <?php if( (array_key_exists('comment', $atts) && $atts['comment'] == 'true') || !array_key_exists('comment', $atts) ): ?> <span class="entry-comments-link"><a href="<?php comment_link() ?>">Leave a Comment</a></span><?php endif; ?>
			</p>
		</header>
		<div class="entry-content <?php if( (array_key_exists('thumbnail', $atts) && $atts['thumbnail'] == 'false') || !has_post_thumbnail() ): echo 'no-thumbnail'; endif;?>" itemprop="text">
			<?php if( ((array_key_exists('thumbnail', $atts) && $atts['thumbnail'] == 'true') || array_key_exists('thumbnail', $atts) !== true) && has_post_thumbnail() ): ?>
			<a href="<?php the_permalink() ?>" aria-hidden="true">
				<?php the_post_thumbnail(); ?>
			</a>
			<?php endif; ?>
			<p>
			<?php
			$excerpt = get_the_excerpt();
			$charlength = (array_key_exists('excerpt_lenght', $atts))? $atts['excerpt_lenght'] : 200;

			if ( mb_strlen( $excerpt ) > $charlength ) {
				$subex = mb_substr( $excerpt, 0, $charlength - 5 );
				$exwords = explode( ' ', $subex );
				$excut = - ( mb_strlen( $exwords[ count( $exwords ) - 1 ] ) );
				if ( $excut < 0 ) {
					echo mb_substr( $subex, 0, $excut );
				} else {
					echo $subex;
				}
				echo '...';
			} else {
				echo str_replace(array('[', ']'), '', $excerpt);
			} 
			?>
			<div class="more-link-holder"><a class="more-link button blue" href="<?php the_permalink() ?>">Read More</a></div>
			</p>
		</div>
	</article>
	<?php
	}
}

if($query->max_num_pages>1){?>
    <p class="custom_pagination">
    <a href="<?php echo '?paged=1'?>">&laquo;</a>
    <?php	
    for($i=1;$i<=$query->max_num_pages;$i++){?>
        <a href="<?php echo '?paged=' . $i; ?>" <?php echo (($paged==$i) || ($paged <= 0) && $i == 1)? 'class="active"':'';?>><?php echo $i;?></a>
        <?php
    }
    if($paged!=$query->max_num_pages){?>
        <a href="<?php echo '?paged=' . ($i - 1); ?>">&raquo;</a>
    <?php } ?>
    </p>
<?php }
wp_reset_postdata();