<?php

$post_meta_keys = ['series', 'year', 'abv', 'appearance', 'smell', 'taste', 'mouthfeel', 'overall'];
$custom_taxonomies = ['brewery', 'style', 'format'];


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

function dbb_display_beer_review_custom_fields($post_id) {
	global $custom_taxonomies;
	global $post_meta_keys;

	?>
	<hr>
	<?php
	foreach ($custom_taxonomies as $taxonomy) {
	?>
		<strong><?= ucfirst($taxonomy) ?>: </strong>
		<?= get_taxonomy_terms($post_id, $taxonomy) ?><br/>
	<?php
	}

	foreach ($post_meta_keys as $post_meta_key) {
	?>
		<strong><?= ucwords($post_meta_key) ?>: </strong>
		<?= get_post_meta_or_default($post_id, $post_meta_key, "<em>not specified</em>") ?><br/>
	<?php
	}
	
	$post_date = get_the_date(  'l, F j, Y' );
	echo "<strong>Review date:</strong> $post_date<br/>\n";
}

