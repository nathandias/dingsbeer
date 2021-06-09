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

