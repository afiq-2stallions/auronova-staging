<?php
/**
 * UAEL Advanced Search.
 *
 * @package UAEL
 */

namespace UltimateElementor\Modules\AdvancedSearch;

use UltimateElementor\Base\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Module.
 */
class Module extends Module_Base {

	/**
	 * Whether to show featured images in search results.
	 *
	 * @since 1.43.0
	 * @var bool
	 */
	private $show_image = true;

	/**
	 * Module should load or not.
	 *
	 * @since 1.43.0
	 * @access public
	 *
	 * @return bool true|false.
	 */
	public static function is_enable() {
		return true;
	}

	/**
	 * Get Module Name.
	 *
	 * @since 1.43.0
	 * @access public
	 *
	 * @return string Module name.
	 */
	public function get_name() {
		return 'uael-advanced-search';
	}

	/**
	 * Get Widgets.
	 *
	 * @since 1.43.0
	 * @access public
	 *
	 * @return array Widgets.
	 */
	public function get_widgets() {
		return array(
			'Advanced_Search',
		);
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct();

		// Add AJAX handlers for search functionality.
		add_action( 'wp_ajax_uael_advanced_search', array( $this, 'handle_search_request' ) );
		add_action( 'wp_ajax_nopriv_uael_advanced_search', array( $this, 'handle_search_request' ) );

		// Add AJAX handlers for load more functionality.
		add_action( 'wp_ajax_uael_advanced_search_load_more', array( $this, 'handle_load_more_request' ) );
		add_action( 'wp_ajax_nopriv_uael_advanced_search_load_more', array( $this, 'handle_load_more_request' ) );

		// Localize script for frontend.
		add_action( 'wp_enqueue_scripts', array( $this, 'localize_scripts' ) );

		// Restrict search to title and excerpt only (not full content).
		add_filter( 'posts_search', array( $this, 'search_by_title_only' ), 10, 2 );
	}

	/**
	 * Localize scripts for frontend.
	 *
	 * @since 1.43.0
	 */
	public function localize_scripts() {
		if ( wp_script_is( 'uael-advanced-search', 'enqueued' ) ) {
			wp_localize_script(
				'uael-advanced-search',
				'uael_advanced_search_script',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce'    => wp_create_nonce( 'uael_advanced_search_nonce' ),
				)
			);
		}
	}

	/**
	 * Modify search query to search only in post title and excerpt.
	 * This prevents false positives from content matches.
	 *
	 * @param string    $search Search SQL for WHERE clause.
	 * @param \WP_Query $query  The WP_Query instance.
	 * @return string Modified search SQL.
	 */
	public function search_by_title_only( $search, $query ) {
		global $wpdb;

		// Only modify queries that have the 's' parameter (search term).
		$search_term = $query->get( 's' );
		if ( empty( $search_term ) ) {
			return $search;
		}

		// Only modify our advanced search queries by checking for our custom flag.
		if ( ! $query->get( 'uael_advanced_search' ) ) {
			return $search;
		}

		// Build search query for title and excerpt only.
		$search_term_like = '%' . $wpdb->esc_like( $search_term ) . '%';

		$search = $wpdb->prepare(
			" AND (({$wpdb->posts}.post_title LIKE %s) OR ({$wpdb->posts}.post_excerpt LIKE %s)) ",
			$search_term_like,
			$search_term_like
		);

		return $search;
	}

	/**
	 * Verify nonce and sanitize request parameters.
	 *
	 * @since 1.43.0
	 * @return array|false Array with sanitized parameters or false if nonce verification fails.
	 */
	private function verify_and_sanitize_request() {
		// Verify nonce for security.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'uael_advanced_search_nonce' ) ) {
			return false;
		}

		$search_term = isset( $_POST['search_term'] ) ? sanitize_text_field( wp_unslash( $_POST['search_term'] ) ) : '';

		// Handle post_types parameter - check both post_types and post_types_json.
		$post_types = array( 'post' ); // Default.

		if ( isset( $_POST['post_types_json'] ) ) {
			$decoded_types = json_decode( stripslashes( sanitize_text_field( wp_unslash( $_POST['post_types_json'] ) ) ), true );
			if ( is_array( $decoded_types ) ) {
				$post_types = array_map( 'sanitize_text_field', $decoded_types );
			}
		} elseif ( isset( $_POST['post_types'] ) ) {
			if ( is_array( $_POST['post_types'] ) ) {
				$post_types = array_map( 'sanitize_text_field', wp_unslash( $_POST['post_types'] ) );
			} else {
				$decoded_types = json_decode( stripslashes( sanitize_text_field( wp_unslash( $_POST['post_types'] ) ) ), true );
				if ( is_array( $decoded_types ) ) {
					$post_types = array_map( 'sanitize_text_field', $decoded_types );
				}
			}
		}

		$category_id    = isset( $_POST['category_id'] ) ? intval( $_POST['category_id'] ) : 0;
		$posts_per_page = isset( $_POST['posts_per_page'] ) ? intval( $_POST['posts_per_page'] ) : 5;
		$offset         = isset( $_POST['offset'] ) ? intval( $_POST['offset'] ) : 0;

		// Enhanced search parameters.
		$additional_params = array();

		// Check for encoded enhanced_params first (from load more).
		if ( isset( $_POST['enhanced_params'] ) ) {
			$decoded_params = json_decode( stripslashes( sanitize_text_field( wp_unslash( $_POST['enhanced_params'] ) ) ), true );
			if ( is_array( $decoded_params ) ) {
				$additional_params = $decoded_params;
			}
		}

		// Taxonomy search.
		if ( isset( $_POST['search_taxonomies'] ) ) {
			$additional_params['search_taxonomies'] = ( 'yes' === $_POST['search_taxonomies'] ) ? true : sanitize_text_field( wp_unslash( $_POST['search_taxonomies'] ) );
			if ( ! isset( $additional_params['taxonomy_names'] ) ) {
				$additional_params['taxonomy_names'] = array();
			}
			if ( isset( $_POST['taxonomy_names'] ) ) {
				if ( is_array( $_POST['taxonomy_names'] ) ) {
					$additional_params['taxonomy_names'] = array_map( 'sanitize_text_field', wp_unslash( $_POST['taxonomy_names'] ) );
				} else {
					$decoded_names = json_decode( stripslashes( sanitize_text_field( wp_unslash( $_POST['taxonomy_names'] ) ) ), true );
					if ( is_array( $decoded_names ) ) {
						$additional_params['taxonomy_names'] = array_map( 'sanitize_text_field', $decoded_names );
					}
				}
			}
		}

		// Author filter.
		if ( isset( $_POST['author_ids'] ) && ! empty( $_POST['author_ids'] ) ) {
			if ( is_array( $_POST['author_ids'] ) ) {
				$additional_params['author_ids'] = array_map( 'intval', wp_unslash( $_POST['author_ids'] ) );
			} else {
				$decoded_ids = json_decode( stripslashes( sanitize_text_field( wp_unslash( $_POST['author_ids'] ) ) ), true );
				if ( is_array( $decoded_ids ) ) {
					$additional_params['author_ids'] = array_map( 'intval', $decoded_ids );
				}
			}
		}

		// Date range filter.
		if ( isset( $_POST['date_from'] ) ) {
			$additional_params['date_from'] = sanitize_text_field( wp_unslash( $_POST['date_from'] ) );
		}
		if ( isset( $_POST['date_to'] ) ) {
			$additional_params['date_to'] = sanitize_text_field( wp_unslash( $_POST['date_to'] ) );
		}

		// Post inclusion/exclusion.
		if ( isset( $_POST['exclude_posts'] ) && ! empty( $_POST['exclude_posts'] ) ) {
			if ( is_array( $_POST['exclude_posts'] ) ) {
				$additional_params['exclude_posts'] = array_map( 'intval', wp_unslash( $_POST['exclude_posts'] ) );
			} else {
				$decoded_posts = json_decode( stripslashes( sanitize_text_field( wp_unslash( $_POST['exclude_posts'] ) ) ), true );
				if ( is_array( $decoded_posts ) ) {
					$additional_params['exclude_posts'] = array_map( 'intval', $decoded_posts );
				}
			}
		}
		if ( isset( $_POST['include_posts'] ) && ! empty( $_POST['include_posts'] ) ) {
			if ( is_array( $_POST['include_posts'] ) ) {
				$additional_params['include_posts'] = array_map( 'intval', wp_unslash( $_POST['include_posts'] ) );
			} else {
				$decoded_posts = json_decode( stripslashes( sanitize_text_field( wp_unslash( $_POST['include_posts'] ) ) ), true );
				if ( is_array( $decoded_posts ) ) {
					$additional_params['include_posts'] = array_map( 'intval', $decoded_posts );
				}
			}
		}

		// Ordering.
		if ( isset( $_POST['orderby'] ) ) {
			$additional_params['orderby'] = sanitize_text_field( wp_unslash( $_POST['orderby'] ) );
		}
		if ( isset( $_POST['order'] ) ) {
			$additional_params['order'] = sanitize_text_field( wp_unslash( $_POST['order'] ) );
		}

		$show_image = isset( $_POST['show_image'] ) ? sanitize_text_field( wp_unslash( $_POST['show_image'] ) ) : 'yes';

		return array(
			'search_term'       => $search_term,
			'post_types'        => $post_types,
			'category_id'       => $category_id,
			'posts_per_page'    => $posts_per_page,
			'offset'            => $offset,
			'additional_params' => $additional_params,
			'show_image'        => $show_image,
		);
	}

	/**
	 * Handle search AJAX request with enhanced parameters.
	 *
	 * @since 1.43.0
	 */
	public function handle_search_request() {
		$params = $this->verify_and_sanitize_request();

		if ( false === $params ) {
			wp_send_json_error( array( 'message' => 'Security check failed' ) );
			return;
		}

		$this->show_image = ( 'yes' === $params['show_image'] );

		try {
			// Simple fallback for empty searches.
			if ( empty( $params['search_term'] ) && 0 === $params['category_id'] && empty( $params['additional_params']['search_taxonomies'] ) ) {
				wp_send_json_success(
					array(
						'posts'       => array(),
						'total_count' => 0,
						'has_more'    => false,
					)
				);
				return;
			}

			$results = $this->perform_search(
				$params['search_term'],
				$params['post_types'],
				$params['category_id'],
				$params['posts_per_page'],
				$params['offset'],
				$params['additional_params']
			);
			wp_send_json_success( $results );
		} catch ( Exception $e ) {
			wp_send_json_error( array( 'message' => 'Search failed: ' . $e->getMessage() ) );
		} catch ( Error $e ) {
			wp_send_json_error( array( 'message' => 'Search failed: ' . $e->getMessage() ) );
		}
	}

	/**
	 * Handle load more AJAX request with enhanced parameters.
	 *
	 * @since 1.43.0
	 */
	public function handle_load_more_request() {
		$params = $this->verify_and_sanitize_request();

		if ( false === $params ) {
			wp_die( esc_html__( 'Security check failed', 'uael' ) );
		}

		$this->show_image = ( 'yes' === $params['show_image'] );

		$results = $this->perform_search(
			$params['search_term'],
			$params['post_types'],
			$params['category_id'],
			$params['posts_per_page'],
			$params['offset'],
			$params['additional_params']
		);

		wp_send_json_success( $results );
	}

	/**
	 * Perform the actual search with enhanced functionality.
	 *
	 * @param string $search_term Search term.
	 * @param array  $post_types Post types to search.
	 * @param int    $category_id Category ID to filter by.
	 * @param int    $posts_per_page Number of posts per page.
	 * @param int    $offset Offset for pagination.
	 * @param array  $additional_params Additional search parameters.
	 * @return array Search results.
	 */
	private function perform_search( $search_term, $post_types, $category_id = 0, $posts_per_page = 5, $offset = 0, $additional_params = array() ) {
		// Filter out post types that should never be searched.
		$excluded_types = array(
			'elementor_library',      // Elementor templates/library items.
			'wpforms',                // WP Forms.
			'e-floating-buttons',     // Elementor floating buttons/elements.
			'elementor-hf',           // Header Footer Elementor post type.
			'hfe_header',             // Header Footer Elementor header.
			'hfe_footer',             // Header Footer Elementor footer.
			'hfe_section',            // Header Footer Elementor section.
		);

		// Remove excluded post types from the search.
		$post_types = array_diff( $post_types, $excluded_types );

		// If all post types were excluded, default to 'post'.
		if ( empty( $post_types ) ) {
			$post_types = array( 'post' );
		}

		// Set appropriate post status based on post types.
		$post_status = array( 'publish' );
		if ( in_array( 'attachment', $post_types ) ) {
			$post_status[] = 'inherit'; // Attachments use 'inherit' status.
		}

		// Check if we need to do OR search (title OR taxonomy).
		$has_taxonomy_search = ! empty( $additional_params['search_taxonomies'] ) &&
			! empty( $additional_params['taxonomy_names'] ) &&
			! empty( $search_term );

		if ( $has_taxonomy_search ) {
			// Perform OR search: title search OR taxonomy search.
			return $this->perform_or_search(
				$search_term,
				$post_types,
				$post_status,
				$category_id,
				$posts_per_page,
				$offset,
				$additional_params
			);
		}

		// Standard search (no OR logic needed).
		$args = array(
			'post_type'            => $post_types,
			'posts_per_page'       => $posts_per_page,
			'offset'               => $offset,
			'post_status'          => $post_status,
			'uael_advanced_search' => true, // Flag for our search filter.
		);

		// Only add 's' parameter if we have a search term.
		if ( ! empty( $search_term ) ) {
			$args['s'] = $search_term;
		}

		// Add category filter if specified.
		if ( $category_id > 0 ) {
			$args['cat'] = $category_id;
		}

		// Enhanced search capabilities.
		$args = $this->apply_enhanced_search_filters( $args, $additional_params );

		$query   = new \WP_Query( $args );
		$results = $this->process_query_results( $query, $search_term );

		// Get total count from the existing query.
		$total_count = $query->found_posts;

		// Render HTML for posts.
		$posts_html = $this->render_posts_html( $results );

		return array(
			'posts'       => $results,
			'posts_html'  => $posts_html,
			'total_count' => $total_count,
			'has_more'    => ( $offset + $posts_per_page ) < $total_count,
			'search_term' => $search_term,
			'filters'     => $this->get_active_filters( $category_id, $additional_params ),
		);
	}

	/**
	 * Perform OR search: title search OR taxonomy search.
	 *
	 * @param string $search_term Search term.
	 * @param array  $post_types Post types to search.
	 * @param array  $post_status Post status.
	 * @param int    $category_id Category ID to filter by.
	 * @param int    $posts_per_page Number of posts per page.
	 * @param int    $offset Offset for pagination.
	 * @param array  $additional_params Additional search parameters.
	 * @return array Search results.
	 */
	private function perform_or_search( $search_term, $post_types, $post_status, $category_id, $posts_per_page, $offset, $additional_params ) {
		// Base args for ID queries.
		$base_args = array(
			'post_type'            => $post_types,
			'posts_per_page'       => -1, // Get all for merging.
			'post_status'          => $post_status,
			'fields'               => 'ids', // Only get IDs for efficiency.
			'uael_advanced_search' => true, // Flag for our search filter.
		);

		if ( $category_id > 0 ) {
			$base_args['cat'] = $category_id;
		}

		// Query 1: Title/content search.
		$title_args      = $base_args;
		$title_args['s'] = $search_term;
		$title_args      = $this->apply_non_taxonomy_filters( $title_args, $additional_params );
		$title_query     = new \WP_Query( $title_args );
		$title_ids       = $title_query->posts;

		// Query 2: Taxonomy search.
		$tax_ids   = array();
		$tax_query = $this->build_taxonomy_query( $search_term, $additional_params['taxonomy_names'] );

		// Only run taxonomy query if we have valid taxonomy terms to search.
		if ( ! empty( $tax_query ) ) {
			$tax_args              = $base_args;
			$tax_args['tax_query'] = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			$tax_args              = $this->apply_non_taxonomy_filters( $tax_args, $additional_params );

			$tax_query_obj = new \WP_Query( $tax_args );
			$tax_ids       = $tax_query_obj->posts;
		}

		// Merge and deduplicate post IDs (OR logic).
		$all_post_ids = array_unique( array_merge( $title_ids, $tax_ids ) );

		if ( empty( $all_post_ids ) ) {
			return array(
				'posts'       => array(),
				'posts_html'  => '',
				'total_count' => 0,
				'has_more'    => false,
				'search_term' => $search_term,
				'filters'     => $this->get_active_filters( $category_id, $additional_params ),
			);
		}

		// Query 3: Get actual posts with pagination.
		$final_args = array(
			'post_type'      => $post_types,
			'post_status'    => $post_status,
			'post__in'       => $all_post_ids,
			'orderby'        => 'post__in', // Maintain relevance order.
			'posts_per_page' => $posts_per_page,
			'offset'         => $offset,
		);

		// Apply ordering if specified.
		if ( ! empty( $additional_params['orderby'] ) ) {
			$final_args['orderby'] = sanitize_text_field( $additional_params['orderby'] );
			$final_args['order']   = ! empty( $additional_params['order'] ) ? sanitize_text_field( $additional_params['order'] ) : 'DESC';
		}

		$final_query = new \WP_Query( $final_args );
		$results     = $this->process_query_results( $final_query, $search_term );

		// Total count is the merged IDs count.
		$total_count = count( $all_post_ids );

		// Render HTML for posts.
		$posts_html = $this->render_posts_html( $results );

		return array(
			'posts'       => $results,
			'posts_html'  => $posts_html,
			'total_count' => $total_count,
			'has_more'    => ( $offset + $posts_per_page ) < $total_count,
			'search_term' => $search_term,
			'filters'     => $this->get_active_filters( $category_id, $additional_params ),
		);
	}

	/**
	 * Process WP_Query results and format as array.
	 *
	 * @param \WP_Query $query WordPress query object.
	 * @param string    $search_term Search term for highlighting.
	 * @return array Array of formatted post data.
	 */
	private function process_query_results( $query, $search_term ) {
		$results = array();

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$post_id   = get_the_ID();
				$post_type = get_post_type();

				// Handle special post types differently.
				$url            = get_permalink();
				$featured_image = get_the_post_thumbnail_url( $post_id, 'medium' );
				$excerpt        = $this->get_enhanced_excerpt( $post_id, $search_term );

				if ( 'attachment' === $post_type ) {
					// For attachments, use the attachment URL and the attachment itself as featured image.
					$url            = wp_get_attachment_url( $post_id );
					$featured_image = wp_get_attachment_image_url( $post_id, 'medium' );
					if ( ! $featured_image ) {
						$featured_image = wp_get_attachment_url( $post_id ); // Fallback to full size.
					}

					// For attachments, create a more informative excerpt.
					$excerpt = $this->get_attachment_excerpt( $post_id, $search_term );
				}

				$results[] = array(
					'id'              => $post_id,
					'title'           => get_the_title(),
					'url'             => $url,
					'excerpt'         => $excerpt,
					'featured_image'  => $featured_image,
					'post_type'       => $post_type,
					'post_type_label' => $this->get_post_type_label( $post_type ),
					'date'            => get_the_date(),
					'author'          => get_the_author(),
					'author_url'      => get_author_posts_url( get_the_author_meta( 'ID' ) ),
					'comment_count'   => get_comments_number(),
					'categories'      => $this->get_post_categories( $post_id ),
					'tags'            => $this->get_post_tags( $post_id ),
					'meta_data'       => $this->get_relevant_meta_data( $post_id ),
				);
			}
			wp_reset_postdata();
		}

		return $results;
	}

	/**
	 * Render posts as HTML.
	 *
	 * @param array $posts Array of post data.
	 * @return string Rendered HTML.
	 */
	private function render_posts_html( $posts ) {
		if ( empty( $posts ) ) {
			return '';
		}

		$html = '';
		foreach ( $posts as $post ) {
			$html .= $this->render_single_post_html( $post );
		}

		return $html;
	}

	/**
	 * Render single post item as HTML.
	 *
	 * @param array $post Post data.
	 * @return string Rendered HTML for single post.
	 */
	private function render_single_post_html( $post ) {
		$image_html = '';
		if ( $this->show_image && ! empty( $post['featured_image'] ) ) {
			$image_html = sprintf(
				'<div class="uael-search-result-image"><img src="%s" alt="%s" loading="lazy"></div>',
				esc_url( $post['featured_image'] ),
				esc_attr( $post['title'] )
			);
		}

		$excerpt_html = '';
		if ( ! empty( $post['excerpt'] ) ) {
			$excerpt_html = sprintf(
				'<p class="uael-search-result-excerpt">%s</p>',
				wp_kses_post( $post['excerpt'] )
			);
		}

		$html = sprintf(
			'<div class="uael-search-result-item" data-post-id="%d">
				%s
				<div class="uael-search-result-content">
					<h4 class="uael-search-result-title">
						<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>
					</h4>
					%s
					<div class="uael-search-result-meta">
						<span class="uael-search-result-type">%s</span>
						<span class="uael-search-result-date">%s</span>
					</div>
				</div>
			</div>',
			absint( $post['id'] ),
			$image_html,
			esc_url( $post['url'] ),
			esc_html( $post['title'] ),
			$excerpt_html,
			esc_html( $post['post_type_label'] ),
			esc_html( $post['date'] )
		);

		return $html;
	}

	/**
	 * Build taxonomy query for searching in taxonomies.
	 *
	 * @param string $search_term Search term.
	 * @param array  $taxonomy_names Taxonomy names to search.
	 * @return array Tax query array.
	 */
	private function build_taxonomy_query( $search_term, $taxonomy_names ) {
		// Don't search taxonomies for very short search terms (< 4 characters).
		// This prevents "ele" from matching "Elementor" category and returning unrelated posts.
		if ( strlen( $search_term ) < 4 ) {
			return array();
		}

		$tax_query = array(
			'relation' => 'OR',
		);

		foreach ( $taxonomy_names as $taxonomy ) {
			$taxonomy = sanitize_text_field( $taxonomy );

			// Check if taxonomy exists first.
			if ( ! taxonomy_exists( $taxonomy ) ) {
				continue;
			}

			// Get terms that match the search term.
			$matching_terms = get_terms(
				array(
					'taxonomy'   => $taxonomy,
					'search'     => $search_term,
					'hide_empty' => false,
				)
			);

			// Check for WP_Error.
			if ( is_wp_error( $matching_terms ) ) {
				$matching_terms = array();
			}

			// If no terms found with built-in search, try manual prefix matching.
			if ( empty( $matching_terms ) && ! empty( $search_term ) ) {
				$all_terms = get_terms(
					array(
						'taxonomy'   => $taxonomy,
						'hide_empty' => false,
					)
				);

				// Check for WP_Error.
				if ( is_wp_error( $all_terms ) ) {
					continue; // Skip this taxonomy if error.
				}

				$search_lower = strtolower( $search_term );

				foreach ( $all_terms as $term ) {
					$term_name_lower = strtolower( $term->name );
					$term_slug_lower = strtolower( $term->slug );

					// Match if term name/slug STARTS with the search term.
					// This prevents "ele" from matching "Elementor" (substring match).
					// But allows "Elem" to match "Elementor" (prefix match).
					if ( strpos( $term_name_lower, $search_lower ) === 0 ||
						strpos( $term_slug_lower, $search_lower ) === 0 ) {
						$matching_terms[] = $term;
					}
				}
			}

			if ( ! empty( $matching_terms ) ) {
				$term_ids = array();
				foreach ( $matching_terms as $term ) {
					$term_ids[] = $term->term_id;
				}

				// Remove duplicates.
				$term_ids = array_unique( $term_ids );

				if ( ! empty( $term_ids ) ) {
					$tax_query[] = array(
						'taxonomy' => $taxonomy,
						'field'    => 'term_id',
						'terms'    => $term_ids,
						'operator' => 'IN',
					);
				}
			}
		}

		// Only return tax_query if we have actual queries.
		if ( count( $tax_query ) > 1 ) {
			return $tax_query;
		}

		return array();
	}

	/**
	 * Apply non-taxonomy filters to query args.
	 *
	 * @param array $args WP_Query arguments.
	 * @param array $params Additional search parameters.
	 * @return array Enhanced query arguments.
	 */
	private function apply_non_taxonomy_filters( $args, $params ) {
		// Author filter.
		if ( ! empty( $params['author_ids'] ) ) {
			$args['author__in'] = array_map( 'intval', $params['author_ids'] );
		}

		// Date range filter.
		if ( ! empty( $params['date_from'] ) || ! empty( $params['date_to'] ) ) {
			$date_query = array();

			if ( ! empty( $params['date_from'] ) ) {
				$date_query['after'] = sanitize_text_field( $params['date_from'] );
			}

			if ( ! empty( $params['date_to'] ) ) {
				$date_query['before'] = sanitize_text_field( $params['date_to'] );
			}

			if ( ! empty( $date_query ) ) {
				$args['date_query'] = array( $date_query );
			}
		}

		// Exclude specific posts.
		if ( ! empty( $params['exclude_posts'] ) ) {
			$args['post__not_in'] = array_map( 'intval', $params['exclude_posts'] );
		}

		// Include specific posts only.
		if ( ! empty( $params['include_posts'] ) ) {
			$args['post__in'] = array_map( 'intval', $params['include_posts'] );
		}

		return $args;
	}

	/**
	 * Apply enhanced search filters to query args.
	 * This is used for non-OR searches (when taxonomy search is disabled).
	 *
	 * @param array $args WP_Query arguments.
	 * @param array $params Additional search parameters.
	 * @return array Enhanced query arguments.
	 */
	private function apply_enhanced_search_filters( $args, $params ) {
		// Apply non-taxonomy filters.
		$args = $this->apply_non_taxonomy_filters( $args, $params );

		// Order by relevance or other criteria.
		if ( ! empty( $params['orderby'] ) ) {
			$args['orderby'] = sanitize_text_field( $params['orderby'] );
			$args['order']   = ! empty( $params['order'] ) ? sanitize_text_field( $params['order'] ) : 'DESC';
		}

		return $args;
	}

	/**
	 * Get enhanced excerpt with search term highlighting.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $search_term Search term to highlight.
	 * @return string Enhanced excerpt.
	 */
	private function get_enhanced_excerpt( $post_id, $search_term ) {
		$excerpt = get_the_excerpt();
		
		if ( empty( $excerpt ) ) {
			$content = get_post_field( 'post_content', $post_id );
			$excerpt = wp_trim_words( strip_shortcodes( $content ), 20 );
		}
		
		// Highlight search terms in excerpt.
		if ( ! empty( $search_term ) && strlen( $search_term ) > 2 ) {
			$excerpt = preg_replace(
				'/(' . preg_quote( $search_term, '/' ) . ')/i',
				'<mark>$1</mark>',
				$excerpt
			);
		}
		
		return $excerpt;
	}

	/**
	 * Get attachment-specific excerpt with metadata.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $search_term Search term to highlight.
	 * @return string Attachment excerpt.
	 */
	private function get_attachment_excerpt( $post_id, $search_term ) {
		$attachment    = get_post( $post_id );
		$excerpt_parts = array();
		
		// Include description if available.
		if ( ! empty( $attachment->post_content ) ) {
			$description     = wp_trim_words( strip_shortcodes( $attachment->post_content ), 15 );
			$excerpt_parts[] = $description;
		}
		
		// Include caption if available.
		if ( ! empty( $attachment->post_excerpt ) ) {
			$excerpt_parts[] = 'Caption: ' . wp_trim_words( $attachment->post_excerpt, 10 );
		}
		
		// Include alt text for images.
		$alt_text = get_post_meta( $post_id, '_wp_attachment_image_alt', true );
		if ( ! empty( $alt_text ) ) {
			$excerpt_parts[] = 'Alt: ' . wp_trim_words( $alt_text, 8 );
		}
		
		// Include file type and size.
		$file_type = get_post_mime_type( $post_id );
		$file_size = filesize( get_attached_file( $post_id ) );
		if ( $file_size ) {
			$file_size_formatted = size_format( $file_size );
			$excerpt_parts[]     = $file_type . ' (' . $file_size_formatted . ')';
		}
		
		$excerpt = implode( ' • ', $excerpt_parts );
		
		// If no metadata, create a basic excerpt.
		if ( empty( $excerpt ) ) {
			$excerpt = 'Media file';
		}
		
		// Highlight search terms in excerpt.
		if ( ! empty( $search_term ) && strlen( $search_term ) > 2 ) {
			$excerpt = preg_replace(
				'/(' . preg_quote( $search_term, '/' ) . ')/i',
				'<mark>$1</mark>',
				$excerpt
			);
		}
		
		return $excerpt;
	}


	/**
	 * Get enhanced post type label.
	 *
	 * @param string $post_type Post type.
	 * @return string Post type label.
	 */
	private function get_post_type_label( $post_type ) {
		$post_type_object = get_post_type_object( $post_type );
		return $post_type_object ? $post_type_object->labels->singular_name : ucfirst( $post_type );
	}

	/**
	 * Get post categories.
	 *
	 * @param int $post_id Post ID.
	 * @return array Categories.
	 */
	private function get_post_categories( $post_id ) {
		$categories = get_the_category( $post_id );
		$cat_list   = array();
		
		foreach ( $categories as $category ) {
			$cat_list[] = array(
				'id'   => $category->term_id,
				'name' => $category->name,
				'url'  => get_category_link( $category->term_id ),
			);
		}
		
		return $cat_list;
	}

	/**
	 * Get post tags.
	 *
	 * @param int $post_id Post ID.
	 * @return array Tags.
	 */
	private function get_post_tags( $post_id ) {
		$tags     = get_the_tags( $post_id );
		$tag_list = array();
		
		if ( $tags ) {
			foreach ( $tags as $tag ) {
				$tag_list[] = array(
					'id'   => $tag->term_id,
					'name' => $tag->name,
					'url'  => get_tag_link( $tag->term_id ),
				);
			}
		}
		
		return $tag_list;
	}

	/**
	 * Get relevant meta data for the post.
	 *
	 * @param int $post_id Post ID.
	 * @return array Meta data.
	 */
	private function get_relevant_meta_data( $post_id ) {
		$meta_data = array();
		$post_type = get_post_type( $post_id );
		
		// WooCommerce product meta.
		if ( 'product' === $post_type && function_exists( 'wc_get_product' ) ) {
			$product = wc_get_product( $post_id );
			if ( $product ) {
				$meta_data['price']        = $product->get_price_html();
				$meta_data['stock_status'] = $product->get_stock_status();
				$meta_data['rating']       = $product->get_average_rating();
				$meta_data['review_count'] = $product->get_review_count();
			}
		}
		
		// Custom fields that might be relevant.
		$relevant_meta_keys = apply_filters(
			'uael_search_relevant_meta_keys',
			array(
				'_price',
				'_regular_price',
				'_sale_price',
				'description',
				'short_description',
			),
			$post_id
		);
		
		foreach ( $relevant_meta_keys as $meta_key ) {
			$meta_value = get_post_meta( $post_id, $meta_key, true );
			if ( ! empty( $meta_value ) ) {
				$meta_data[ str_replace( '_', '', $meta_key ) ] = $meta_value;
			}
		}
		
		return $meta_data;
	}

	/**
	 * Get active search filters for reference.
	 *
	 * @param int   $category_id Category ID.
	 * @param array $params Additional parameters.
	 * @return array Active filters.
	 */
	private function get_active_filters( $category_id, $params ) {
		$filters = array();
		
		if ( $category_id > 0 ) {
			$category            = get_category( $category_id );
			$filters['category'] = $category ? $category->name : '';
		}
		
		if ( ! empty( $params['author_ids'] ) ) {
			$filters['authors'] = $params['author_ids'];
		}
		
		if ( ! empty( $params['date_from'] ) || ! empty( $params['date_to'] ) ) {
			$filters['date_range'] = array(
				'from' => $params['date_from'] ?? '',
				'to'   => $params['date_to'] ?? '',
			);
		}
		
		return $filters;
	}
}
