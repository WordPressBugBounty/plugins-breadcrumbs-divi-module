<?php
/*
Plugin Name: Breadcrumbs Divi Module
Plugin URI:  http://www.learnhowwp.com/divi-breadcrumbs-module
Description: The plugin adds a new module, the Breadcrumbs module in the Divi Builder
Version:     2.0.0
Author:      learnhowwp.com
Author URI:  http://www.learnhowwp.com
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: lwp-divi-breadcrumbs
Domain Path: /languages

Divi Breadcrumbs is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Divi Breadcrumbs is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Divi Breadcrumbs. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/


/**
 * Plugin path and URL constants.
 *
 * @since 2.0.0
 */
if ( ! defined( 'LWP_BREADCRUMBS_PATH' ) ) {
	define( 'LWP_BREADCRUMBS_PATH', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'LWP_BREADCRUMBS_URL' ) ) {
	define( 'LWP_BREADCRUMBS_URL', plugin_dir_url( __FILE__ ) );
}

/**
 * Load Divi 5 module support when Divi 5 files are present.
 *
 * Must run at plugin load time (no hook) so the action callback registered
 * inside server/index.php is in place before Divi fires
 * 'divi_module_library_modules_dependency_tree' at init priority 0.
 *
 * et_builder_d5_enabled() is defined by the Divi theme (functions.php), which
 * loads AFTER plugins_loaded — so we cannot check it here. Instead we guard by
 * checking whether the D5 DependencyInterface file exists on disk, which is a
 * reliable proxy for "Divi 5 is installed". The actual D5-enabled check is
 * deferred to inside the dependency-tree action callback.
 *
 * Uses get_template_directory() (parent theme when a child is active) instead
 * of ABSPATH . 'wp-content/...' so custom WP_CONTENT_DIR installs resolve correctly.
 *
 * @since 2.0.0
 */
$lwp_breadcrumbs_divi_d5_dependency = trailingslashit( get_template_directory() ) . 'includes/builder-5/server/Framework/DependencyManagement/Interfaces/DependencyInterface.php';

if ( file_exists( $lwp_breadcrumbs_divi_d5_dependency ) ) {
	require_once LWP_BREADCRUMBS_PATH . 'divi-5/server/index.php';
}

/**
 * Enqueue Divi 5 Visual Builder assets.
 *
 * The divi_visual_builder_assets_before_enqueue_scripts hook only fires
 * inside the VB, so the et_builder_d5_enabled() guard here is a safety net.
 *
 * @since 2.0.0
 */
function lwp_breadcrumbs_d5_enqueue_vb_assets() {
	if ( ! function_exists( 'et_builder_d5_enabled' ) || ! et_builder_d5_enabled() ) {
		return;
	}

	if ( ! class_exists( \ET\Builder\VisualBuilder\Assets\PackageBuildManager::class ) ) {
		return;
	}

	\ET\Builder\VisualBuilder\Assets\PackageBuildManager::register_package_build(
		array(
			'name'    => 'lwp-breadcrumbs-d5-vb',
			'version' => '2.0.0',
			'script'  => array(
				'src'                => LWP_BREADCRUMBS_URL . 'divi-5/visual-builder/build/breadcrumbs-divi.js',
				'deps'               => array(
					'react',
					'divi-module-library',
					'wp-hooks',
				),
				'enqueue_top_window' => false,
				'enqueue_app_window' => true,
			),
		)
	);
}
add_action( 'divi_visual_builder_assets_before_enqueue_scripts', 'lwp_breadcrumbs_d5_enqueue_vb_assets' );

/**
 * Collect post + Theme Builder layout content for detecting the Divi 5 breadcrumbs block.
 *
 * Mirrors the content sources Dynamic Assets uses (main post + active TB templates).
 *
 * @since 2.0.0
 * @return string Combined block editor content blobs.
 */
function lwp_breadcrumbs_get_combined_page_builder_content() {
	$parts = array();

	$post_id = get_queried_object_id();
	if ( $post_id > 0 ) {
		$post = get_post( $post_id );
        if ( $post instanceof \WP_Post && is_string( $post->post_content ) && '' !== $post->post_content ) {
			$parts[] = $post->post_content;
		}
	}

	if ( class_exists( '\ET\Builder\FrontEnd\Assets\DynamicAssetsUtils' ) ) {
		$tb_ids = \ET\Builder\FrontEnd\Assets\DynamicAssetsUtils::get_theme_builder_template_ids();
		foreach ( $tb_ids as $tb_id ) {
			$tb_post = get_post( (int) $tb_id );
            if ( $tb_post instanceof \WP_Post && is_string( $tb_post->post_content ) && '' !== $tb_post->post_content ) {
				$parts[] = $tb_post->post_content;
			}
		}
	}

	return implode( "\n", $parts );
}

/**
 * Whether a content string references the Divi 5 Breadcrumbs block.
 *
 * @since 2.0.0
 * @param string $blob Post / layout content.
 * @return bool
 */
function lwp_breadcrumbs_content_has_d5_block( $blob ) {
	if ( ! is_string( $blob ) || '' === $blob ) {
		return false;
	}

	return false !== strpos( $blob, 'lwp/breadcrumbs' )
		|| false !== strpos( $blob, 'lwp\\/breadcrumbs' );
}

/**
 * Whether rendered Divi content on this request includes the `lwp/breadcrumbs` block.
 *
 * @since 2.0.0
 * @return bool
 */
function lwp_breadcrumbs_page_uses_d5_module() {
    static $has_module = null;

    if ( null !== $has_module ) {
        return $has_module;
    }

	if ( ! function_exists( 'et_builder_d5_enabled' ) || ! et_builder_d5_enabled() ) {
        $has_module = false;
        return $has_module;
	}

	$blob = lwp_breadcrumbs_get_combined_page_builder_content();

    $has_module = lwp_breadcrumbs_content_has_d5_block( $blob );

    return $has_module;
}

/**
 * Return filesystem or URL prefix for Divi dynamic icon CSS (Divi 5).
 *
 * @since 2.0.0
 * @param bool $url Whether to return a URL (true) or filesystem path (false).
 * @return string
 */
function lwp_breadcrumbs_d5_get_dynamic_assets_path( $url = false ) {
	if ( class_exists( '\ET\Builder\FrontEnd\Assets\DynamicAssetsUtils' ) ) {
		return \ET\Builder\FrontEnd\Assets\DynamicAssetsUtils::get_dynamic_assets_path( (bool) $url );
	}

	return '';
}

/**
 * Ensure Divi icon font CSS is included in Dynamic Assets when the Breadcrumbs module is present.
 *
 * Per Divi docs: https://dev.elegantthemes.com/docs/tutorials/module/advanced/custom-dynamic-assets/modifying-dynamic-assets/
 * use `divi_frontend_assets_dynamic_assets_global_assets_list` and
 * `divi_frontend_assets_dynamic_assets_late_global_assets_list` with the same callback.
 *
 * Important: these filters run only while Divi is **generating** merged dynamic CSS (cache miss
 * or stale cache). On a cache hit, generation is skipped and filters do not run — see
 * `lwp_breadcrumbs_d5_enqueue_frontend_assets()` for a reliable frontend enqueue.
 *
 * @since 2.0.0
 * @param array $global_asset_list Current global assets list (early or late).
 * @param array $assets_args       Arguments; may include `assets_prefix`.
 * @param mixed $instance          DynamicAssets / ListBuilder instance (unused).
 * @return array
 */
function lwp_breadcrumbs_d5_merge_icon_dynamic_assets( $global_asset_list, $assets_args, $instance ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
	if ( ! is_array( $global_asset_list ) || ! is_array( $assets_args ) ) {
		return $global_asset_list;
	}

	if ( ! lwp_breadcrumbs_page_uses_d5_module() ) {
		return $global_asset_list;
	}

	$prefix = isset( $assets_args['assets_prefix'] ) ? (string) $assets_args['assets_prefix'] : '';
	if ( '' === $prefix ) {
		$prefix = lwp_breadcrumbs_d5_get_dynamic_assets_path( false );
	}
	if ( '' === $prefix ) {
		return $global_asset_list;
	}

	// Full ETmodules + Font Awesome packs (same asset keys & paths as Divi core).
	unset( $global_asset_list['et_icons_base'], $global_asset_list['et_icons_social'] );
	$global_asset_list['et_icons_all'] = array(
		'css' => $prefix . '/css/icons_all.css',
	);
	$global_asset_list['et_icons_fa']  = array(
		'css' => $prefix . '/css/icons_fa_all.css',
	);

	return $global_asset_list;
}
add_filter( 'divi_frontend_assets_dynamic_assets_global_assets_list', 'lwp_breadcrumbs_d5_merge_icon_dynamic_assets', 10, 3 );
add_filter( 'divi_frontend_assets_dynamic_assets_late_global_assets_list', 'lwp_breadcrumbs_d5_merge_icon_dynamic_assets', 10, 3 );

/**
 * Enqueue Divi icon fonts and plugin frontend CSS when the D5 Breadcrumbs block is used.
 *
 * Guarded by `lwp_breadcrumbs_page_uses_d5_module()` so nothing runs on unrelated pages.
 *
 * When Divi’s dynamic asset cache is valid, it skips regeneration — so filter hooks on the global
 * asset list never run and icon CSS may be missing from the merged file. Enqueuing the same CSS
 * files Divi uses guarantees icons render. Duplicate @font-face rules are harmless if both run.
 *
 * @since 2.0.0
 * @return void
 */
function lwp_breadcrumbs_d5_enqueue_frontend_assets() {
	if ( is_admin() ) {
		return;
	}
	if ( ! function_exists( 'et_builder_d5_enabled' ) || ! et_builder_d5_enabled() ) {
		return;
	}
	if ( ! lwp_breadcrumbs_page_uses_d5_module() ) {
		return;
	}

	$base_url = lwp_breadcrumbs_d5_get_dynamic_assets_path( true );
	if ( '' === $base_url ) {
		return;
	}

	$et_ver = defined( 'ET_BUILDER_PRODUCT_VERSION' ) ? ET_BUILDER_PRODUCT_VERSION : '2.0.0';

	wp_enqueue_style(
		'lwp-breadcrumbs-d5-icons-all',
		$base_url . '/css/icons_all.css',
		array(),
		$et_ver
	);
	wp_enqueue_style(
		'lwp-breadcrumbs-d5-icons-fa',
		$base_url . '/css/icons_fa_all.css',
		array(),
		$et_ver
	);

	$module_css_path = LWP_BREADCRUMBS_PATH . 'divi-5/assets/breadcrumbs-frontend.css';
	$module_css_ver  = file_exists( $module_css_path ) ? (string) filemtime( $module_css_path ) : '2.0.0';

	wp_enqueue_style(
		'lwp-breadcrumbs-d5-module',
		LWP_BREADCRUMBS_URL . 'divi-5/assets/breadcrumbs-frontend.css',
		array(),
		$module_css_ver
	);
}
add_action( 'wp_enqueue_scripts', 'lwp_breadcrumbs_d5_enqueue_frontend_assets', 20 );

if ( ! function_exists( 'lwp_initialize_extension' ) ):
/**
 * Creates the extension's main class instance.
 *
 * @since 1.0.0
 */
function lwp_initialize_extension() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/DiviBreadcrumbs.php';
}
add_action( 'divi_extensions_init', 'lwp_initialize_extension' );
endif;


if ( ! function_exists( 'lwp_get_breadcrumbs' ) ):

add_action( 'wp_ajax_lwp_get_breadcrumbs', 'lwp_get_breadcrumbs' );

function lwp_get_breadcrumbs(){

	$before_text ='';
	$home_text ='';
    $separator_icon='&#x39;';
    $post_id=0;

	if(isset($_POST['before_text']))
		$before_text=sanitize_text_field($_POST['before_text']);

	if(isset($_POST['home_text']))
		$home_text=sanitize_text_field($_POST['home_text']);

	if(isset($_POST['separator_icon']))
        $separator_icon=esc_html($_POST['separator_icon']);


    if(isset($_POST['post_id'])  && is_int(intval($_POST['post_id'])))
	    $post_id = $_POST['post_id'];

    $result = [
		'title' => get_the_title( $post_id ),	//Title of the Post
		'html'=> lwp_get_hansel_and_gretel_breadcrumbs($home_text,$before_text,$separator_icon) 	//Breadcrumbs for Post
    ];
    echo json_encode( $result );
    wp_die();
}
endif;

if ( ! function_exists( 'lwp_get_hansel_and_gretel_breadcrumbs' ) ):

//Function that generates the HTML from breadcrumbs
function lwp_get_hansel_and_gretel_breadcrumbs( $_home_text='Home', $_before_text='', $_delimiter='&#x39;', $use_custom_home_link = 'off', $home_link = '' ) {
    // Set variables for later use
    $here_text        = __( $_before_text, 'lwp-divi-breadcrumbs' );
    $home_link        = $use_custom_home_link === 'on' ? $home_link : home_url('/');
    $home_text        = __( $_home_text, 'lwp-divi-breadcrumbs' );
    $link_before      = '<span property="itemListElement" typeof="ListItem">';
    $link_after       = '</span>';
    $link_attr        = ' property="item" typeof="WebPage"';
    $link             = $link_before . '<a' . $link_attr . ' href="%1$s"><span property="name">%2$s<span></a><meta property="position" content="positionhere">' . $link_after;
    $delimiter        = $_delimiter;              // Delimiter between crumbs
    $before           = '<span class="current">'; // Tag before the current crumb
    $after            = '</span>';                // Tag after the current crumb
    $page_addon       = '';                       // Adds the page number if the query is paged
    $breadcrumb_trail = '';
    $category_links   = '';
    $position         =2;

	$delimiter = ' <span class="separator et-pb-icon">'.$delimiter.'</span> ';

    /**
     * Set our own $wp_the_query variable. Do not use the global variable version due to
     * reliability
     */
    $wp_the_query   = $GLOBALS['wp_the_query'];
    $queried_object = $wp_the_query->get_queried_object();

    // Handle single post requests which includes single pages, posts and attatchments
    if ( is_singular() )
    {
        /**
         * Set our own $post variable. Do not use the global variable version due to
         * reliability. We will set $post_object variable to $GLOBALS['wp_the_query']
         */
        $post_object = sanitize_post( $queried_object );

        // Set variables
        $title          = apply_filters( 'the_title', $post_object->post_title );
        $parent         = $post_object->post_parent;
        $post_type      = $post_object->post_type;
        $post_id        = $post_object->ID;
        $post_link      = $before . $title . $after;
        $parent_string  = '';
        $post_type_link = '';

        if ( 'post' === $post_type )
        {
            // Get the post categories
            $categories = get_the_category( $post_id );
            if ( $categories ) {
                // Lets grab the first category
                $category  = $categories[0];

                $category_names = get_category_parents( $category);
                $category_names_array = explode('/',$category_names);

                $category_links = get_category_parents( $category, true, $delimiter );
                $category_links = str_replace( '<a',   $link_before . '<a' . $link_attr, $category_links );
                $category_links = str_replace( '</a>', '</a>' . $link_after, $category_links );
                foreach ($category_names_array as $category_loop_name) {
                    if($category_loop_name=='')
                        continue;
                    $category_links = str_replace( $category_loop_name.'</a>', '<span property="name">' .$category_loop_name.'</span></a>',$category_links );   //</a> included in str_replace to avoid replacing the word if it is part of another category
                    $category_links = str_replace( '<span property="name">' .$category_loop_name.'</span></a>','<span property="name">' .$category_loop_name.'</span></a><meta property="position" content="'.$position++.'">' ,$category_links );
                }
            }
        }

        if ( !in_array( $post_type, ['post', 'page', 'attachment'] ) )
        {
            $post_type_object = get_post_type_object( $post_type );
            $archive_link     = esc_url( get_post_type_archive_link( $post_type ) );

            $post_type_link   = sprintf( $link, $archive_link, $post_type_object->labels->singular_name );
            $post_type_link = str_replace( 'positionhere', $position++, $post_type_link );
        }

        // Get post parents if $parent !== 0
        if ( 0 !== $parent )
        {
            $parent_links = [];
            while ( $parent ) {
                $post_parent = get_post( $parent );

                $temp_link = sprintf( $link, esc_url( get_permalink( $post_parent->ID ) ), get_the_title( $post_parent->ID ) );
                $temp_link = str_replace( 'positionhere', $position++, $temp_link );

                $parent_links[] = $temp_link;

                $parent = $post_parent->post_parent;
            }

            $parent_links = array_reverse( $parent_links );

            $parent_string = implode( $delimiter, $parent_links );
        }

        // Lets build the breadcrumb trail
        if ( $parent_string ) {
            $breadcrumb_trail = $parent_string . $delimiter . $post_link;
        } else {
            $breadcrumb_trail = $post_link;
        }

        if ( $post_type_link )
            $breadcrumb_trail = $post_type_link . $delimiter . $breadcrumb_trail;

        if ( $category_links )
            $breadcrumb_trail = $category_links . $breadcrumb_trail;
    }

    // Handle archives which includes category-, tag-, taxonomy-, date-, custom post type archives and author archives
    if( is_archive() )
    {
        if (    is_category()
             || is_tag()
             || is_tax()
        ) {
            // Set the variables for this section
            $term_object        = get_term( $queried_object );
            $taxonomy           = $term_object->taxonomy;
            $term_id            = $term_object->term_id;
            $term_name          = $term_object->name;
            $term_parent        = $term_object->parent;
            $taxonomy_object    = get_taxonomy( $taxonomy );
            //Categories: Tags: is set there
            $current_term_link  = $before . $taxonomy_object->labels->singular_name . ': ' . $term_name . $after;
            $parent_term_string = '';

            if ( 0 !== $term_parent )
            {
                // Get all the current term ancestors
                $parent_term_links = [];
                while ( $term_parent ) {
                    $term = get_term( $term_parent, $taxonomy );

                    $temp_link = sprintf( $link, esc_url( get_term_link( $term ) ), $term->name );
                    $temp_link = str_replace( 'positionhere', $position++, $temp_link );

                    $parent_term_links[] = $temp_link;

                    $term_parent = $term->parent;
                }

                $parent_term_links  = array_reverse( $parent_term_links );
                $parent_term_string = implode( $delimiter, $parent_term_links );
            }

            if ( $parent_term_string ) {
                $breadcrumb_trail = $parent_term_string . $delimiter . $current_term_link;
            } else {
                $breadcrumb_trail = $current_term_link;
            }

        } elseif ( is_author() ) {

            $breadcrumb_trail = __( 'Author archive for ') .  $before . $queried_object->data->display_name . $after;

        } elseif ( is_date() ) {
            // Set default variables
            $year     = $wp_the_query->query_vars['year'];
            $monthnum = $wp_the_query->query_vars['monthnum'];
            $day      = $wp_the_query->query_vars['day'];

            // Get the month name if $monthnum has a value
            if ( $monthnum ) {
                $date_time  = DateTime::createFromFormat( '!m', $monthnum );
                $month_name = $date_time->format( 'F' );
            }

            if ( is_year() ) {

                $breadcrumb_trail = $before . $year . $after;

            } elseif( is_month() ) {

                $year_link        = sprintf( $link, esc_url( get_year_link( $year ) ), $year );
                $year_link = str_replace( 'positionhere', $position++, $year_link );

                $breadcrumb_trail = $year_link . $delimiter . $before . $month_name . $after;

            } elseif( is_day() ) {

                $year_link        = sprintf( $link, esc_url( get_year_link( $year ) ),             $year       );
                $year_link = str_replace( 'positionhere', $position++, $year_link );

                $month_link       = sprintf( $link, esc_url( get_month_link( $year, $monthnum ) ), $month_name );
                $month_link = str_replace( 'positionhere', $position++, $month_link );

                $breadcrumb_trail = $year_link . $delimiter . $month_link . $delimiter . $before . $day . $after;
            }

        } elseif ( is_post_type_archive() ) {

            $post_type        = $wp_the_query->query_vars['post_type'];
            $post_type_object = get_post_type_object( $post_type );

            $breadcrumb_trail = $before . $post_type_object->labels->singular_name . $after;

        }
    }

    // Handle the search page
    if ( is_search() ) {
        $breadcrumb_trail = __( 'Search query for: ' ) . $before . get_search_query() . $after;
    }

    // Handle 404's
    if ( is_404() ) {
        $breadcrumb_trail = $before . __( 'Error 404' ) . $after;
    }

    // Handle paged pages
    if ( is_paged() ) {
        $current_page = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : get_query_var( 'page' );
        // If $current_page is false or an empty string, parse the URL manually
        if ( empty ( $current_page) ) {
            $current_url = home_url( add_query_arg(array()) );
            $parsed_url = parse_url( $current_url );
            parse_str( $parsed_url['query'], $query_params );

            if ( isset( $query_params['paged'] ) ) {
                $current_page = $query_params['paged'];
            } elseif ( isset( $query_params['page'] ) ) {
                $current_page = $query_params['page'];
            }

            // If 'paged' or 'page' is not found in the query parameters, try to get it from the URL path
            if ( empty( $current_page ) && isset( $parsed_url['path'] ) ) {
                $matches = array();
                preg_match( '/\/page\/([0-9]+)/', $parsed_url['path'], $matches );
                if ( isset( $matches[1] ) ) {
                    $current_page = $matches[1];
                }
            }            
        }

        if ( $current_page ) {
            $page_addon = $before . sprintf( __( ' ( Page %s )' ), number_format_i18n( $current_page ) ) . $after;
        }
    }

    $breadcrumb_output_link  = '';
    //$breadcrumb_output_link .= '<div class="breadcrumb">'; //removing the default wrapper for breadcrumbs
    $breadcrumb_output_link .= '';
    if (    is_home()
         || is_front_page()
    ) {
        // Do not show breadcrumbs on page one of home and frontpage
        if ( is_paged() ) {
            $breadcrumb_output_link .= '<span class="before">'.$here_text.'</span> ';
            $breadcrumb_output_link .= '<span vocab="https://schema.org/" typeof="BreadcrumbList">';
            $breadcrumb_output_link .= '<span property="itemListElement" typeof="ListItem"><a property="item" typeof="WebPage" href="' . $home_link . '" class="home"><span property="name">' . $home_text . '</span><meta property="position" content="1"></a><meta property="position" content="1"></span>';
            $breadcrumb_output_link .= $page_addon;
            $breadcrumb_output_link .= '</span>';
        }
    } else {
        $breadcrumb_output_link .= '<span class="before">'.$here_text.'</span> ';
        $breadcrumb_output_link .= '<span vocab="https://schema.org/" typeof="BreadcrumbList">';
        $breadcrumb_output_link .= '<span property="itemListElement" typeof="ListItem"><a property="item" typeof="WebPage" href="' . $home_link . '" class="home"><span property="name">' . $home_text . '</span></a><meta property="position" content="1"></span>';
        $breadcrumb_output_link .= $delimiter;
        $breadcrumb_output_link .= $breadcrumb_trail;
        $breadcrumb_output_link .= $page_addon;
        $breadcrumb_output_link .= '</span>';
    }
    //$breadcrumb_output_link .= '</div><!-- .breadcrumbs -->';

    return $breadcrumb_output_link;
}

endif;


if ( ! function_exists( 'lwp_divi_breadcrumbs_dependencies' ) ):

//et_builder_options();
function lwp_divi_breadcrumbs_dependencies() {
    if( ! function_exists('et_builder_options'))
      echo '<div class="notice notice-warning"><p>' . __( 'The Divi Breadcrums Module needs the Divi Theme or the Divi Plugin to function', 'lwp-divi-breadcrumbs' ) . '</p></div>';
  }

add_action( 'admin_notices', 'lwp_divi_breadcrumbs_dependencies' );

endif;

if ( ! function_exists( 'lwp_breadcrumbs_add_action_links' ) ):

add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'lwp_breadcrumbs_add_action_links' );

function lwp_breadcrumbs_add_action_links ( $actions ) {
    $mylinks = array(
        '<a href="https://wordpress.org/support/plugin/breadcrumbs-divi-module/reviews/?filter=5#new-post" target="_blank">'.esc_html__( 'Rate Plugin', 'lwp-divi-breadcrumbs' ).'</a>',
        '<a href="https://www.learnhowwp.com/divi-plugins/" target="_blank">'.esc_html__( 'More Divi Plugins', 'lwp-divi-breadcrumbs' ).'</a>',
    );
    $actions = array_merge( $actions, $mylinks );
    return $actions;
}

endif;

if ( ! function_exists( 'lwp_breadcrumbs_add_icons' ) ):
    add_filter( 'et_global_assets_list', 'lwp_breadcrumbs_add_icons', 10 );
    function lwp_breadcrumbs_add_icons( $assets ) {
        if ( isset( $assets['et_icons_all'] ) && isset( $assets['et_icons_fa'] ) ) {
            return $assets;
        }
        $assets_prefix = et_get_dynamic_assets_path();
        $assets['et_icons_all'] = array(
            'css' => "{$assets_prefix}/css/icons_all.css",
        );
        $assets['et_icons_fa'] = array(
            'css' => "{$assets_prefix}/css/icons_fa_all.css",
        );
        return $assets;
    }
    endif;