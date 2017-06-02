<?php

 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Add the RPSL widget.
 *
 * @since 1.0.0
 *
 * @see WP_Widget
 */
class GetMovieData_Posts_Widget extends WP_Widget {
	/**
	 * Sets up a Recent Posts widget instance.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		$widget_ops = array( 'classname' => 'muvipro-posts-module', 'description' => __( 'Module posts for module home.','getmoviedata' ) );
		parent::__construct( 'muvipro-posts', __( 'Module Posts (Movie)','getmoviedata' ), $widget_ops );
	}
	
	/**
	 * Outputs the content for Mailchimp Form.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $args     Display arguments including 'before_title', 'after_title',
	 *                        'before_widget', and 'after_widget'.
	 * @param array $instance Settings for Mailchimp Form.
	 */
    public function widget($args, $instance) {
		
		global $post;
		
		// Title
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
		
		// Link Title
		$link_title 		= ( ! empty( $instance[ 'link_title' ] ) ) ? esc_url( $instance[ 'link_title' ] ) : '';
		
		echo $args['before_widget'];
		if ( $title ) {
			if ( !empty ( $link_title ) ) {
				echo '<div class="row">';
					echo '<div class="col-md-10">';
			}
						echo $args['before_title'] . $title . $args['after_title'];
			if ( !empty ( $link_title ) ) {
					echo '</div>';
					echo '<div class="col-md-2"><div class="module-linktitle"><h4><a href="'.$link_title.'" title="'.__( 'Watch ','getmoviedata' ). $title .'">'.__( 'More','getmoviedata' ).'</a></h4></div></div>';
				echo '</div>';
			}
		}
		// Base Id Widget
		$idmuv_widget_ID = $this->id_base . '-' . $this->number;
		// Category ID
        $idmuv_category_ids 		= ( ! empty( $instance[ 'idmuv_category_ids' ] ) ) ? array_map( 'absint', $instance[ 'idmuv_category_ids' ] ) : array( 0 );
		// Tag ID
        $idmuv_tag_ids 				= ( ! empty( $instance[ 'idmuv_tag_ids' ] ) ) ? array_map( 'absint', $instance[ 'idmuv_tag_ids' ] ) : array( 0 );
		// Excerpt Length
        $idmuv_number_posts 		= ( ! empty( $instance[ 'idmuv_number_posts' ] ) ) ? absint( $instance[ 'idmuv_number_posts' ] ) : absint( 8 );
		// Title Length
        $idmuv_title_length 		= ( ! empty( $instance[ 'idmuv_title_length' ] ) ) ? absint( $instance[ 'idmuv_title_length' ] ) : absint( 40 );
		
		// if 'all categories' was selected ignore other selections of categories
		if ( in_array( 0, $idmuv_category_ids ) ) {
			$idmuv_category_ids = array( 0 );
		}
		
		// if 'all tag' was selected ignore other selections of tag
		if ( in_array( 0, $idmuv_tag_ids ) ) {
			$idmuv_tag_ids = array( 0 );
		}
		
		// filter the arguments for the Recent Posts widget:
		
		// standard params
		$query_args = array(
			'post_type'           => array ( 'post','tv' ),
			'posts_per_page'      => $idmuv_number_posts,
			'no_found_rows'       => true,
			'post_status'         => 'publish',
			// make it fast withour update term cache and cache results
			// https://thomasgriffin.io/optimize-wordpress-queries/
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
		);
		
		$query_args[ 'ignore_sticky_posts' ] = true;
		
		// set order of posts in widget
		$query_args[ 'orderby' ] = 'date';
		$query_args[ 'order' ] = 'DESC';
		
		// add categories param only if 'all categories' was not selected
		if ( ! in_array( 0, $idmuv_category_ids ) ) {
			$query_args[ 'category__in' ] = $idmuv_category_ids;
		}
		
		// add tags param only if 'all tags' was not selected
		if ( ! in_array( 0, $idmuv_tag_ids ) ) {
			$query_args[ 'tag__in' ] = $idmuv_tag_ids;
		}

		// run the query: get the latest posts
		$rp = new WP_Query( apply_filters( 'idmuv_rp_widget_posts_args', $query_args ) );
		
		?>

			<div class="row grid-container gmr-module-posts">
				<?php while ( $rp->have_posts() ) : $rp->the_post(); ?>
					<div class="col-md-125" <?php echo getmoviedata_itemtype_schema( 'Movie' ); ?>>
						<div class="gmr-item-modulepost">
							<?php
								// Add thumnail
								if ( has_post_thumbnail() ) :
										echo '<a href="' . get_permalink() . '" itemprop="url" title="' . the_title_attribute( array( 'before' => __( 'Watch ','getmoviedata' ), 'after' => '', 'echo' => false ) ) . '" rel="bookmark">';
											the_post_thumbnail( 'medium', array( 'itemprop'=>'image' ) );
										echo '</a>';
								else :
									// do_action( 'funct', $size, $link, $classes = '', $echo = true );
									do_action( 'idmuvi_core_get_images', 'medium', true );
								endif; // endif; has_post_thumbnail()
							?>
							
							<header class="entry-header text-center">
								<div class="gmr-button-widget">
									<?php 
										$trailer = get_post_meta( $post->ID, 'IDMUVICORE_Trailer', true );
										// Check if the custom field has a value.
										if ( ! empty( $trailer ) ) {
											echo '<div class="clearfix gmr-popup-button-widget">';
											echo '<a href="https://www.youtube.com/watch?v=' . $trailer . '" class="button gmr-trailer-popup" title="' . the_title_attribute( array( 'before' => __( 'Trailer ','getmoviedata' ), 'after' => '', 'echo' => 0 ) ) . '">' . __( 'Trailer','getmoviedata' ) . '</a>';
											echo '</div>';
										}
									?>
									<div class="clearfix">
										<a href="<?php the_permalink(); ?>" class="button" <?php echo getmoviedata_itemprop_schema( 'url' ); ?> title="<?php the_title_attribute( array( 'before' => __( 'Watch ','getmoviedata' ), 'after' => '' ) ); ?>" rel="bookmark"><?php echo __( 'Watch Movie','getmoviedata' ); ?></a>
									</div>
								</div>
								<h2 class="entry-title" <?php echo getmoviedata_itemprop_schema( 'headline' ); ?>>
									<a href="<?php the_permalink(); ?>" <?php echo getmoviedata_itemprop_schema( 'url' ); ?> title="<?php the_title_attribute( array( 'before' => __( 'Watch ','getmoviedata' ), 'after' => '' ) ); ?>" rel="bookmark">
										<?php 
											if ( $post_title = $this->get_the_trimmed_post_title( $idmuv_title_length ) ) { 
												echo $post_title; 
											} else { 
												the_title(); 
											} 										
										?>
									</a>
								</h2>
							</header><!-- .entry-header -->
							
							<?php
								$rating = get_post_meta( $post->ID, 'IDMUVICORE_tmdbRating', true ); 
								if ( ! empty( $rating ) ) {
									echo '<div class="gmr-rating-item">' . __( 'Rating: ','getmoviedata' ) . $rating . '</div>';
								}
								$duration = get_post_meta( $post->ID, 'IDMUVICORE_Runtime', true ); 
				if ( ! empty( $duration ) ) {
					echo '<div class="gmr-duration-item" property="duration">' . $duration . __( ' min','getmoviedata' ) . '</div>';
				}
				
								if ( is_sticky() ) {
					echo '<div class="kbd-sticky">' . __( 'Sticky', 'getmoviedata' ) . '</div>';
				}
				
				if( !is_wp_error( get_the_term_list( $post->ID, 'muviquality' ) ) ) {
								if ( !empty (get_the_term_list( $post->ID, 'muviquality' )) ) {
									echo '<div class="gmr-quality-item">';
									echo get_the_term_list( $post->ID, 'muviquality', '', ', ', '' );
									echo '</div>';
								}
							}
							
							if ( 'tv' == get_post_type() ) {
								echo '<div class="gmr-posttype-item">';
								echo __( 'TV Show','getmoviedata' );
								echo '</div>';
							}
				
				
				
								
								$release = get_post_meta( $post->ID, 'IDMUVICORE_Released', true );
								// Check if the custom field has a value.
								if ( ! empty( $release ) ) {
									if ( gmr_checkIsAValidDate($release) == true ) {
										$datetime = new DateTime( $release );
										echo '<span class="screen-reader-text"><time itemprop="dateCreated" datetime="'.$datetime->format('c').'">'.$release.'</time></span>';
									}
								}

								if( !is_wp_error( get_the_term_list( $post->ID, 'muvidirector' ) ) ) {
									if ( !empty (get_the_term_list( $post->ID, 'muvidirector' )) ) {
										echo '<span class="screen-reader-text">';
										echo get_the_term_list( $post->ID, 'muvidirector', '<span itemprop="director" itemscope="itemscope" itemtype="http://schema.org/Person"><span itemprop="name">', '</span></span>, <span itemprop="director" itemscope="itemscope" itemtype="http://schema.org/Person"><span itemprop="name">', '</span></span>' );
										echo '</span>';
									}
								}
								
							?>
								
						</div>
					</div>
				<?php endwhile; ?>
			</div>
			
		<?php
		echo $args['after_widget'];
    }
	
	/**
	 * Handles updating settings for the current Mailchimp widget instance.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $new_instance New settings for this instance as input by the user via
	 *                            GetMovieData_Posts_Widget::form().
	 * @param array $old_instance Old settings for this instance.
	 * @return array Updated settings to save.
	 */
    public function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$new_instance = wp_parse_args( (array) $new_instance, 
			array( 
				'title' => '', 
				'link_title' => '', 
				'idmuv_category_ids' => array( 0 ), 
				'idmuv_tag_ids' => array( 0 ), 
				'idmuv_number_posts' => 8,
				'idmuv_title_length' => 40
			) 
		);
		// Title
		$instance['title'] 								= sanitize_text_field( $new_instance['title'] );
		// Link Title
		$instance['link_title']							= esc_url( $new_instance[ 'link_title' ] );
		// Category IDs
        $instance['idmuv_category_ids']           		= array_map( 'absint', $new_instance[ 'idmuv_category_ids' ] );
		// Tag IDs
        $instance['idmuv_tag_ids']           			= array_map( 'absint', $new_instance[ 'idmuv_tag_ids' ] );
		// Number posts
        $instance['idmuv_number_posts']          		= absint( $new_instance[ 'idmuv_number_posts' ] );
		// Title Length
        $instance['idmuv_title_length']          		= absint( $new_instance[ 'idmuv_title_length' ] );
		
		// if 'all categories' was selected ignore other selections of categories
		if ( in_array( 0, $idmuv_category_ids ) ) {
			$idmuv_category_ids = array( 0 );
		}
		
		// if 'all tags' was selected ignore other selections of tags
		if ( in_array( 0, $idmuv_tag_ids ) ) {
			$idmuv_tag_ids = array( 0 );
		}
		
        return $instance;
    }
	
	/**
	 * Outputs the settings form for the Mailchimp widget.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $instance Current settings.
	 */
    public function form( $instance ) {
        $instance = wp_parse_args( (array) $instance, 
			array( 
				'title' => 'Recent Post', 
				'link_title' => '',
				'idmuv_category_ids' => array( 0 ), 
				'idmuv_tag_ids' => array( 0 ), 
				'idmuv_number_posts' => 8,
				'idmuv_title_length' => 40
			) 
		);
		// Title
		$title 							= sanitize_text_field( $instance['title'] );
		// Link Title
		$link_title 					= esc_url( $instance[ 'link_title' ] );
		// Category ID
        $idmuv_category_ids          	= array_map( 'absint', $instance[ 'idmuv_category_ids' ] );
		// Tag ID
        $idmuv_tag_ids          		= array_map( 'absint', $instance[ 'idmuv_tag_ids' ] );
		// Number posts
        $idmuv_number_posts          	= absint( $instance[ 'idmuv_number_posts' ] );
		// Title Length
        $idmuv_title_length          	= absint( $instance[ 'idmuv_title_length' ] );

		// get categories
		$categories = get_categories( array( 'hide_empty' => 0, 'hierarchical' => 1 ) );
		$number_of_cats = count( $categories );
		
		// get size (number of rows to display) of selection box: not more than 5
		$number_of_rows = ( 5 > $number_of_cats ) ? $number_of_cats + 1 : 5;
		
		// if 'all categories' was selected ignore other selections of categories
		if ( in_array( 0, $idmuv_category_ids ) ) {
			$idmuv_category_ids = array( 0 );
		}

		// start selection box
		$selection_category = sprintf(
			'<select name="%s[]" id="%s" class="cat-select widefat" multiple size="%d">',
			$this->get_field_name( 'idmuv_category_ids' ),
			$this->get_field_id( 'idmuv_category_ids' ),
			$number_of_rows
		);
		$selection_category .= "\n";

		// make selection box entries
		$cat_list = array();
		if ( 0 < $number_of_cats ) {

			// make a hierarchical list of categories
			while ( $categories ) {
				// go on with the first element in the categories list:
				// if there is no parent
				if ( '0' == $categories[ 0 ]->parent ) {
					// get and remove it from the categories list
					$current_entry = array_shift( $categories );
					// append the current entry to the new list
					$cat_list[] = array(
						'id'	=> absint( $current_entry->term_id ),
						'name'	=> esc_html( $current_entry->name ),
						'depth'	=> 0
					);
					// go on looping
					continue;
				}
				// if there is a parent:
				// try to find parent in new list and get its array index
				$parent_index = $this->get_parent_index( $cat_list, $categories[ 0 ]->parent );
				// if parent is not yet in the new list: try to find the parent later in the loop
				if ( false === $parent_index ) {
					// get and remove current entry from the categories list
					$current_entry = array_shift( $categories );
					// append it at the end of the categories list
					$categories[] = $current_entry;
					// go on looping
					continue;
				}
				// if there is a parent and parent is in new list:
				// set depth of current item: +1 of parent's depth
				$depth = $cat_list[ $parent_index ][ 'depth' ] + 1;
				// set new index as next to parent index
				$new_index = $parent_index + 1;
				// find the correct index where to insert the current item
				foreach( $cat_list as $entry ) {
					// if there are items with same or higher depth than current item
					if ( $depth <= $entry[ 'depth' ] ) {
						// increase new index
						$new_index = $new_index + 1;
						// go on looping in foreach()
						continue;
					}
					// if the correct index is found:
					// get current entry and remove it from the categories list
					$current_entry = array_shift( $categories );
					// insert current item into the new list at correct index
					$end_array = array_splice( $cat_list, $new_index ); // $cat_list is changed, too
					$cat_list[] = array(
						'id'	=> absint( $current_entry->term_id ),
						'name'	=> esc_html( $current_entry->name ),
						'depth'	=> $depth
					);
					$cat_list = array_merge( $cat_list, $end_array );
					// quit foreach(), go on while-looping
					break;
				} // foreach( cat_list )
			} // while( categories )

			// make HTML of selection box
			$selected = ( in_array( 0, $idmuv_category_ids ) ) ? ' selected="selected"' : '';
			$selection_category .= "\t";
			$selection_category .= '<option value="0"' . $selected . '>' . __( 'All Categories', 'getmoviedata' ) . '</option>';
			$selection_category .= "\n";

			foreach ( $cat_list as $category ) {
				$cat_name = apply_filters( 'getmoviedata_list_cats', $category[ 'name' ], $category );
				$pad = ( 0 < $category[ 'depth' ] ) ? str_repeat('&ndash;&nbsp;', $category[ 'depth' ] ) : '';
				$selection_category .= "\t";
				$selection_category .= '<option value="' . $category[ 'id' ] . '"';
				$selection_category .= ( in_array( $category[ 'id' ], $idmuv_category_ids ) ) ? ' selected="selected"' : '';
				$selection_category .= '>' . $pad . $cat_name . '</option>';
				$selection_category .= "\n";
			}
			
		}

		// close selection box
		$selection_category .= "</select>\n";
		
		// get tags
		$tags = get_tags( array( 'hide_empty' => 0, 'hierarchical' => 1 ) );
		$number_of_tags = count( $tags );
		
		// get size (number of rows to display) of selection box: not more than 5
		$number_of_rows_tags = ( 5 > $number_of_tags ) ? $number_of_tags + 1 : 5;
		
		// if 'all tags' was selected ignore other selections of tags
		if ( in_array( 0, $idmuv_tag_ids ) ) {
			$idmuv_tag_ids = array( 0 );
		}

		// start selection box
		$selection_tag = sprintf(
			'<select name="%s[]" id="%s" class="cat-select widefat" multiple size="%d">',
			$this->get_field_name( 'idmuv_tag_ids' ),
			$this->get_field_id( 'idmuv_tag_ids' ),
			$number_of_rows_tags
		);
		$selection_tag .= "\n";

		// make selection box entries
		$tag_list = array();
		if ( 0 < $number_of_tags ) {

			// make a hierarchical list of categories
			while ( $tags ) {
				// go on with the first element in the categories list:
				// if there is no parent
				if ( '0' == $tags[ 0 ]->parent ) {
					// get and remove it from the categories list
					$current_entry = array_shift( $tags );
					// append the current entry to the new list
					$tag_list[] = array(
						'id'	=> absint( $current_entry->term_id ),
						'name'	=> esc_html( $current_entry->name ),
						'depth'	=> 0
					);
					// go on looping
					continue;
				}
				// if there is a parent:
				// try to find parent in new list and get its array index
				$parent_index = $this->get_parent_index( $tag_list, $tags[ 0 ]->parent );
				// if parent is not yet in the new list: try to find the parent later in the loop
				if ( false === $parent_index ) {
					// get and remove current entry from the categories list
					$current_entry = array_shift( $tags );
					// append it at the end of the categories list
					$tags[] = $current_entry;
					// go on looping
					continue;
				}
				// if there is a parent and parent is in new list:
				// set depth of current item: +1 of parent's depth
				$depth = $tag_list[ $parent_index ][ 'depth' ] + 1;
				// set new index as next to parent index
				$new_index = $parent_index + 1;
				// find the correct index where to insert the current item
				foreach( $tag_list as $entry ) {
					// if there are items with same or higher depth than current item
					if ( $depth <= $entry[ 'depth' ] ) {
						// increase new index
						$new_index = $new_index + 1;
						// go on looping in foreach()
						continue;
					}
					// if the correct index is found:
					// get current entry and remove it from the categories list
					$current_entry = array_shift( $tags );
					// insert current item into the new list at correct index
					$end_array = array_splice( $tag_list, $new_index ); // $cat_list is changed, too
					$cat_list[] = array(
						'id'	=> absint( $current_entry->term_id ),
						'name'	=> esc_html( $current_entry->name ),
						'depth'	=> $depth
					);
					$tag_list = array_merge( $tag_list, $end_array );
					// quit foreach(), go on while-looping
					break;
				} // foreach( cat_list )
			} // while( categories )

			// make HTML of selection box
			$selected = ( in_array( 0, $idmuv_tag_ids ) ) ? ' selected="selected"' : '';
			$selection_tag .= "\t";
			$selection_tag .= '<option value="0"' . $selected . '>' . __( 'All Tags', 'getmoviedata' ) . '</option>';
			$selection_tag .= "\n";

			foreach ( $tag_list as $tag ) {
				$tag_name = apply_filters( 'getmoviedata_list_tags', $tag[ 'name' ], $tag );
				$pad = ( 0 < $tag[ 'depth' ] ) ? str_repeat('&ndash;&nbsp;', $tag[ 'depth' ] ) : '';
				$selection_tag .= "\t";
				$selection_tag .= '<option value="' . $tag[ 'id' ] . '"';
				$selection_tag .= ( in_array( $tag[ 'id' ], $idmuv_tag_ids ) ) ? ' selected="selected"' : '';
				$selection_tag .= '>' . $pad . $tag_name . '</option>';
				$selection_tag .= "\n";
			}
			
		}

		// close selection box
		$selection_tag .= "</select>\n";
		
		?>

		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:','getmoviedata' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('link_title'); ?>"><?php _e( 'Link Title:','getmoviedata' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id('link_title'); ?>" name="<?php echo $this->get_field_name('link_title'); ?>" type="url" value="<?php echo esc_attr($link_title); ?>" />
			<br />
            <small><?php _e( 'Target url for title (example: http://www.domain.com/target), leave blank if you want using title without link.','getmoviedata' ); ?></small>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('idmuv_category_ids'); ?>"><?php _e( 'Selected categories','getmoviedata' ); ?></label> 
			<?php echo $selection_category; ?>
			<br />
            <small><?php _e( 'Click on the categories with pressed CTRL key to select multiple categories. If All Categories was selected then other selections will be ignored.','getmoviedata' ); ?></small>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('idmuv_tag_ids'); ?>"><?php _e( 'Selected tags','getmoviedata' ); ?></label> 
			<?php echo $selection_tag; ?>
			<br />
            <small><?php _e( 'Click on the tags with pressed CTRL key to select multiple tags. If All Tags was selected then other selections will be ignored.','getmoviedata' ); ?></small>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('idmuv_number_posts'); ?>"><?php _e( 'Number post','getmoviedata' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id('idmuv_number_posts'); ?>" name="<?php echo $this->get_field_name('idmuv_number_posts'); ?>" type="number" value="<?php echo esc_attr($idmuv_number_posts); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('idmuv_title_length'); ?>"><?php _e( 'Maximum length of title','getmoviedata' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id('idmuv_title_length'); ?>" name="<?php echo $this->get_field_name('idmuv_title_length'); ?>" type="number" value="<?php echo esc_attr($idmuv_title_length); ?>" />
		</p>
		<?php
    }
	
	/**
	 * Return the array index of a given ID
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function get_parent_index( $arr, $id ) {
		$len = count( $arr );
		if ( 0 == $len ) {
			return false;
		}
		$id = absint( $id );
		for ( $i = 0; $i < $len; $i++ ) {
			if ( $id == $arr[ $i ][ 'id' ] ) {
				return $i;
			}
		}
		return false; 
	}
	
	/**
	 * Returns the shortened post title, must use in a loop.
	 *
	 * @since 1.0.0
	 */
	private function get_the_trimmed_post_title( $len = 40, $more = '&hellip;' ) {
		
		// get current post's post_title
		$post_title = get_the_title();

		// if post_title is longer than desired
		if ( mb_strlen( $post_title ) > $len ) {
			// get post_title in desired length
			$post_title = mb_substr( $post_title, 0, $len );
			// append ellipses
			$post_title .= $more;
		}
		// return text
		return $post_title;
	}
	
}

add_action( 'widgets_init', function() {
    register_widget( 'GetMovieData_Posts_Widget' );
} );
