<?php
/**
 * @package WordPress
 * @subpackage Yoko
 */

get_header(); ?>

<?php
// a helper function to display post meta values, or a default string if the post meta is empty

function get_post_meta_or_default($id, $key, $default = "-") {
	return ($value = get_post_meta($id, $key, true)) ? $value : $default;
}

function get_taxonomy_terms($post_id, $taxonomy, $default = "-") {
	$terms = get_the_terms($post_id, $taxonomy);
	if ($terms) {
		foreach ($terms as $term) {
			$out[] = '<a class="' .$term->slug .'" href="' .get_term_link( $term->slug, $taxonomy) .'">' .$term->name .'</a>';
		}
		return join( ', ', $out );
	} else {
		return $default;
	}

}

?>

<div id="wrap">
<div id="main">

	<div id="content">

	<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

			<?php get_template_part( 'content', 'single' ); ?>

			<!-- here are two ways to display the beer review custom fields -->

			<hr/>

			<!-- method 1: loop over the custom fields -->
			<?php
				$post_meta_keys = ['year', 'abv', 'a', 's', 't', 'm', 'o'];
				$custom_taxonomies = ['brewery', 'style', 'format'];

				foreach ($custom_taxonomies as $taxonomy) {
				?>
					<strong><?= ucfirst($taxonomy) ?>: </strong>
					<?= get_taxonomy_terms($post->ID, $taxonomy) ?><br/>
				<?php
				}

				foreach ($post_meta_keys as $post_meta_key) {
				?>
					<strong><?= ucwords($post_meta_key) ?>: </strong>
					<?= get_post_meta_or_default($post->ID, $post_meta_key, "<em>not specified</em>") ?><br/>
				<?php
				}
			?>

			

			<hr/>

			<!-- method 1: display fields individually -->
			<strong>Brewery:</strong> <?php echo get_taxonomy_terms($post->ID, 'brewery'); ?>
			<strong>Style:</strong> <?php echo get_taxonomy_terms($post->ID, 'style'); ?>
			<strong>Format:</strong> <?php echo get_taxonomy_terms($post-ID, 'format'); ?>

			<br/>

			<strong>Series:</strong> <?php echo get_post_meta_or_default($post->ID, 'series'); ?>
			<strong>Year:</strong> <?php echo get_post_meta_or_default($post->ID, 'year'); ?>
			<strong>ABV:</strong> <?php echo get_post_meta_or_default($post->ID, 'abv'); ?>
			<strong>A:</strong> <?php echo get_post_meta_or_default($post->ID, 'a'); ?>
			<strong>S:</strong> <?php echo get_post_meta_or_default($post->ID, 's'); ?>
			<strong>T:</strong> <?php echo get_post_meta_or_default($post->ID, 't'); ?>
			<strong>M:</strong> <?php echo get_post_meta_or_default($post->ID, 'm'); ?>
			<strong>O:</strong> <?php echo get_post_meta_or_default($post->ID, 'o'); ?>

		
				
			<?php //comments_template( '', true ); ?>

			<?php endwhile; // end of the loop. ?>

	
			<nav id="nav-below">
				<div class="nav-previous"><?php next_post_link( '%link', __( '&larr; Previous Post', 'yoko' ) ); ?></div>
				<div class="nav-next"><?php previous_post_link( '%link', __( 'Next Post  &rarr;', 'yoko' ) ); ?></div>
			</nav><!-- end #nav-below -->
				
	</div><!-- end content -->
	
<?php get_sidebar(); ?>
<?php get_footer(); ?>