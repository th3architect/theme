<?php
/**
 * Custom template tags for AffiliateWP
 *
 * @package WordPress
 * @subpackage Twenty_Fourteen
 * @since AffiliateWP 1.0
 */

/**
 * Limit excerpt length to 20 characters
 */
function affwp_excerpt_length( $length ) {
	return 20;
}
add_filter( 'excerpt_length', 'affwp_excerpt_length' );

/**
 * Make doc category pags show all posts
 */
function affwp_pre_get_posts( $query ) {
	if ( is_admin() || ! $query->is_main_query() )
		return;
 	
 	// Make doc category pags show all posts
	if ( $query->is_tax( 'doc_category' ) ) {

		$query->set( 'posts_per_page', -1 );
			return;
	}
}
add_action( 'pre_get_posts', 'affwp_pre_get_posts' );

/**
 * Get started now button
 * @param  string $text [description]
 * @return [type]       [description]
 */
function affwp_button_get_started( $text = 'Get started now' ) { ?>
	<a class="button large get-started" href="/pricing"><?php echo $text; ?></a>
<?php }

/**		
 * Render the_title
 * @since 1.0
*/
function affwp_the_title() { 
?>
	<?php if ( edd_is_checkout() ) : ?>
		<h1 class="page-title">Nice choice!</h1>
	<?php elseif( ! is_front_page() && is_page() ) : ?>
		<h1 class="page-title"><?php the_title(); ?></h1>
	<?php elseif( is_singular('download') ) : ?>
		<h1 class="download-title"><?php the_title(); ?></h1>
	<?php elseif( is_tax( 'download_category' ) ) : ?>
		<h1 class="page-title">
            <?php printf( __( '%s', 'affwp' ), single_term_title( '', false ) ); ?>
        </h1>
	<?php else : ?>
		<h1 class="entry-title"><?php the_title(); ?></h1>
	<?php endif; ?>

<?php }	

if ( ! function_exists( 'affwp_paging_nav' ) ) :
/**
 * Display navigation to next/previous set of posts when applicable.
 *
 * @since AffiliateWP 1.0
 *
 * @return void
 */
function affwp_paging_nav() {
	// Don't print empty markup if there's only one page.
	if ( $GLOBALS['wp_query']->max_num_pages < 2 ) {
		return;
	}

	$paged        = get_query_var( 'paged' ) ? intval( get_query_var( 'paged' ) ) : 1;
	$pagenum_link = html_entity_decode( get_pagenum_link() );
	$query_args   = array();
	$url_parts    = explode( '?', $pagenum_link );

	if ( isset( $url_parts[1] ) ) {
		wp_parse_str( $url_parts[1], $query_args );
	}

	$pagenum_link = remove_query_arg( array_keys( $query_args ), $pagenum_link );
	$pagenum_link = trailingslashit( $pagenum_link ) . '%_%';

	$format  = $GLOBALS['wp_rewrite']->using_index_permalinks() && ! strpos( $pagenum_link, 'index.php' ) ? 'index.php/' : '';
	$format .= $GLOBALS['wp_rewrite']->using_permalinks() ? user_trailingslashit( 'page/%#%', 'paged' ) : '?paged=%#%';

	// Set up paginated links.
	$links = paginate_links( array(
		'base'     => $pagenum_link,
		'format'   => $format,
		'total'    => $GLOBALS['wp_query']->max_num_pages,
		'current'  => $paged,
		'mid_size' => 1,
		'add_args' => array_map( 'urlencode', $query_args ),
		'prev_text' => __( '&larr; Previous', 'affwp' ),
		'next_text' => __( 'Next &rarr;', 'affwp' ),
	) );

	if ( $links ) :

	?>
	<nav class="navigation paging-navigation" role="navigation">
		<h1 class="screen-reader-text"><?php _e( 'Posts navigation', 'affwp' ); ?></h1>
		<div class="pagination loop-pagination">
			<?php echo $links; ?>
		</div><!-- .pagination -->
	</nav><!-- .navigation -->
	<?php
	endif;
}
endif;

if ( ! function_exists( 'affwp_post_nav' ) ) :
/**
 * Display navigation to next/previous post when applicable.
 *
 * @since AffiliateWP 1.0
 *
 * @return void
 */
function affwp_post_nav() {
	// Don't print empty markup if there's nowhere to navigate.
	$previous = ( is_attachment() ) ? get_post( get_post()->post_parent ) : get_adjacent_post( false, '', true );
	$next     = get_adjacent_post( false, '', false );

	if ( ! $next && ! $previous ) {
		return;
	}

	?>
	<nav class="navigation post-navigation" role="navigation">
		<h1 class="screen-reader-text"><?php _e( 'Post navigation', 'affwp' ); ?></h1>
		<div class="nav-links columns columns-2">
			<?php
			if ( is_attachment() ) :
				previous_post_link( '%link', __( '<span class="meta-nav item">Published In</span>%title', 'affwp' ) );
			else :
				previous_post_link('<div class="item">%link</div>');
				next_post_link('<div class="item">%link</div>');
				// previous_post_link( '%link', __( '<span class="meta-nav item">%title</span>', 'affwp' ) );
				// next_post_link( '%link', __( '<span class="meta-nav item">%title</span>', 'affwp' ) );
			endif;
			?>
		</div><!-- .nav-links -->
	</nav><!-- .navigation -->
	<?php
}
endif;

if ( ! function_exists( 'affwp_posted_on' ) ) :
/**
 * Print HTML with meta information for the current post-date/time and author.
 *
 * @since AffiliateWP 1.0
 *
 * @return void
 */
function affwp_posted_on() {
	if ( is_sticky() && is_home() && ! is_paged() ) {
		echo '<span class="featured-post">' . __( 'Sticky', 'affwp' ) . '</span>';
	}

	// Set up and print post meta information.
	// printf( '<span class="entry-date"><a href="%1$s" rel="bookmark"><time class="entry-date" datetime="%2$s">%3$s</time></a></span> <span class="byline"><span class="author vcard"><a class="url fn n" href="%4$s" rel="author">%5$s</a></span></span>',
	// 	esc_url( get_permalink() ),
	// 	esc_attr( get_the_date( 'c' ) ),
	// 	esc_html( get_the_date() ),
	// 	esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
	// 	get_the_author()
	// );

	// printf( '<span class="entry-date"><time class="entry-date" datetime="%1$s">%2$s</time></span> <span class="byline"><span class="author vcard"><a class="url fn n" href="%3$s" rel="author">%4$s</a></span></span>',
	// 	esc_attr( get_the_date( 'c' ) ),
	// 	esc_html( get_the_date() ),
	// 	esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
	// 	get_the_author()
	// );

	printf( '<div class="entry-date"><time class="entry-date" datetime="%1$s">%2$s</time></div>',
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() )
	);
}
endif;

/**
 * Find out if blog has more than one category.
 *
 * @since AffiliateWP 1.0
 *
 * @return boolean true if blog has more than 1 category
 */
function affwp_categorized_blog() {
	if ( false === ( $all_the_cool_cats = get_transient( 'affwp_category_count' ) ) ) {
		// Create an array of all the categories that are attached to posts
		$all_the_cool_cats = get_categories( array(
			'hide_empty' => 1,
		) );

		// Count the number of categories that are attached to the posts
		$all_the_cool_cats = count( $all_the_cool_cats );

		set_transient( 'affwp_category_count', $all_the_cool_cats );
	}

	if ( 1 !== (int) $all_the_cool_cats ) {
		// This blog has more than 1 category so affwp_categorized_blog should return true
		return true;
	} else {
		// This blog has only 1 category so affwp_categorized_blog should return false
		return false;
	}
}

/**
 * Flush out the transients used in affwp_categorized_blog.
 *
 * @since AffiliateWP 1.0
 *
 * @return void
 */
function affwp_category_transient_flusher() {
	// Like, beat it. Dig?
	delete_transient( 'affwp_category_count' );
}
add_action( 'edit_category', 'affwp_category_transient_flusher' );
add_action( 'save_post',     'affwp_category_transient_flusher' );

/**
 * Display an optional post thumbnail.
 *
 * Wraps the post thumbnail in an anchor element on index
 * views, or a div element when on single views.
 *
 * @since AffiliateWP 1.0
 *
 * @return void
*/
function affwp_post_thumbnail() {
	if ( post_password_required() || ! has_post_thumbnail() ) {
		return;
	}

	if ( is_singular() ) :
	?>

	<div class="post-thumbnail">
	<?php
		if ( ( ! is_active_sidebar( 'sidebar-2' ) || is_page_template( 'page-templates/full-width.php' ) ) ) {
			the_post_thumbnail( 'affwp-full-width' );
		} else {
			the_post_thumbnail();
		}
	?>
	</div>


	<?php else : ?>

	<a title="<?php the_title_attribute(); ?>" class="post-thumbnail" href="<?php the_permalink(); ?>">
	<?php
		if ( ( ! is_active_sidebar( 'sidebar-2' ) || is_page_template( 'page-templates/full-width.php' ) ) ) {
			the_post_thumbnail();
		} else {
			the_post_thumbnail();
		}
	?>
	</a>

	<?php endif; // End is_singular()
}