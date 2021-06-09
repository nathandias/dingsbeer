<?php
/**
 * @package WordPress
 * @subpackage Yoko
 */

get_header(); ?>

<div id="wrap">
<div id="main">

	<div id="content">

	<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

			<?php get_template_part( 'content', 'single' ); ?>

			<!-- here are two ways to display the beer review custom fields -->

			<hr/>

			<!-- method 1: loop over the custom fields -->
			<?php
				// $post_meta_keys and $custom_taxonomies for the beer reviews
				// defined in the dbb_plugin: theme_support.php
				
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

			<!-- method 2: display fields individually -->
			<strong>Brewery:</strong> <?php echo get_taxonomy_terms($post->ID, 'brewery'); ?>
			<strong>Style:</strong> <?php echo get_taxonomy_terms($post->ID, 'style'); ?>
			<strong>Format:</strong> <?php echo get_taxonomy_terms($post->ID, 'format'); ?>

			<br/>

			<strong>Series:</strong> <?php echo get_post_meta_or_default($post->ID, 'series'); ?>
			<strong>Year:</strong> <?php echo get_post_meta_or_default($post->ID, 'year'); ?>
			<strong>ABV:</strong> <?php echo get_post_meta_or_default($post->ID, 'abv'); ?>

			<br/>

			<strong>Appearance:</strong> <?php echo get_post_meta_or_default($post->ID, 'appearance'); ?>
			<strong>Smell:</strong> <?php echo get_post_meta_or_default($post->ID, 'smell'); ?>
			<strong>Taste:</strong> <?php echo get_post_meta_or_default($post->ID, 'taste'); ?>
			<strong>Mouthfeel:</strong> <?php echo get_post_meta_or_default($post->ID, 'mouthfeel'); ?>
			<strong>Overall:</strong> <?php echo get_post_meta_or_default($post->ID, 'overall'); ?>

		
				
			<?php //comments_template( '', true ); ?>

			<?php endwhile; // end of the loop. ?>

	
			<nav id="nav-below">
				<div class="nav-previous"><?php next_post_link( '%link', __( '&larr; Previous Post', 'yoko' ) ); ?></div>
				<div class="nav-next"><?php previous_post_link( '%link', __( 'Next Post  &rarr;', 'yoko' ) ); ?></div>
			</nav><!-- end #nav-below -->
				
	</div><!-- end content -->
	
<?php get_sidebar(); ?>
<?php get_footer(); ?>