<?php
/**
 * Divi 5 server-side registration and rendering for the Breadcrumbs module.
 *
 * @package breadcrumbs-divi-module
 * @since   2.0.0
 */

namespace LwpBreadcrumbsD5;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dependency_interface_path = trailingslashit( get_theme_root( 'Divi' ) ) . 'Divi/includes/builder-5/server/Framework/DependencyManagement/Interfaces/DependencyInterface.php';

require_once $dependency_interface_path;

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;
use ET\Builder\Packages\Module\Options\Text\TextClassnames;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;

/**
 * Handles PHP-side registration and rendering of the Breadcrumbs Divi 5 module.
 *
 * @since 2.0.0
 */
class Lwp_Breadcrumbs_D5_Module implements DependencyInterface {

	/**
	 * Boot the module by hooking into WordPress init.
	 *
	 * Called automatically by Divi's dependency tree.
	 *
	 * @since 2.0.0
	 */
	public function load() {
		add_action( 'init', array( self::class, 'register_module' ) );
	}

	/**
	 * Register the module with Divi's ModuleRegistration system.
	 *
	 * Points to the directory containing module.json.
	 *
	 * @since 2.0.0
	 */
	public static function register_module() {
		$module_json_folder_path = dirname( __DIR__, 1 ) . '/visual-builder/src/modules/Breadcrumbs';

		ModuleRegistration::register_module(
			$module_json_folder_path,
			array(
				'render_callback' => array( self::class, 'render_callback' ),
			)
		);
	}


	/**
	 * Generate module classnames.
	 *
	 * @since 2.0.0
	 * @param array $args Arguments provided by Divi.
	 */
	public static function module_classnames( $args ) {
		$classnames_instance = $args['classnamesInstance'];
		$attrs               = $args['attrs'];

		$classnames_instance->add(
			TextClassnames::text_options_classnames(
				array(
					'text' => $attrs['module']['advanced']['text'] ?? array(),
				),
				array( 'orientation' => false )
			),
			true
		);

		$classnames_instance->add(
			ElementClassnames::classnames(
				array(
					'attrs' => $attrs['module']['decoration'] ?? array(),
				)
			)
		);
	}

	/**
	 * Enqueue module styles for the frontend.
	 *
	 * @since 2.0.0
	 * @param array $args Arguments provided by Divi.
	 */
	public static function module_styles( $args ) {
		$attrs       = $args['attrs'] ?? array();
		$elements    = $args['elements'];
		$settings    = $args['settings'] ?? array();
		$order_class = $args['orderClass'];

		Style::add(
			array(
				'id'            => $args['id'],
				'name'          => $args['name'],
				'orderIndex'    => $args['orderIndex'],
				'storeInstance' => $args['storeInstance'],
				'styles'        => array(
					$elements->style(
						array(
							'attrName'   => 'module',
							'styleProps' => array(
								'disabledOn'     => array(
									'disabledModuleVisibility' => $settings['disabledModuleVisibility'] ?? null,
								),
								'advancedStyles' => array(
									// Text toggle (orientation, shadow, light/dark layout) — mirrors BlurbModule.
									array(
										'componentName' => 'divi/text',
										'props'         => array(
											'selector' => "{$order_class} .lwp-breadcrumbs",
											'attr'     => $attrs['module']['advanced']['text'] ?? array(),
										),
									),
									// Link color.
									array(
										'componentName' => 'divi/common',
										'props'         => array(
											'selector' => "{$order_class} .lwp-breadcrumbs a",
											'attr'     => $attrs['linkColor']['innerContent'] ?? array(),
											'property' => 'color',
										),
									),
									// Separator color.
									array(
										'componentName' => 'divi/common',
										'props'         => array(
											'selector' => "{$order_class} .lwp-breadcrumbs .separator",
											'attr'     => $attrs['separatorColor']['innerContent'] ?? array(),
											'property' => 'color',
										),
									),
									// Current page text color.
									array(
										'componentName' => 'divi/common',
										'props'         => array(
											'selector' => "{$order_class} .lwp-breadcrumbs .current",
											'attr'     => $attrs['currentTextColor']['innerContent'] ?? array(),
											'property' => 'color',
										),
									),
									// Separator icon: font-family + font-weight.
									array(
										'componentName' => 'divi/common',
										'props'         => array(
											'selector' => "{$order_class} .lwp-breadcrumbs .separator",
											'attr'     => $attrs['separatorIcon']['innerContent'] ?? array(),
											'declarationFunction' => array( self::class, 'icon_font_declaration' ),
										),
									),
									// Before-icon: font-family + font-weight.
									array(
										'componentName' => 'divi/common',
										'props'         => array(
											'selector' => "{$order_class} .lwp-breadcrumbs .before-icon",
											'attr'     => $attrs['beforeIcon']['innerContent'] ?? array(),
											'declarationFunction' => array( self::class, 'icon_font_declaration' ),
										),
									),
								),
							),
						)
					),
					// Body Text toggle (font family, size, weight, etc.) — separate element style pass.
					$elements->style(
						array(
							'attrName' => 'body',
						)
					),
				),
			)
		);
	}

	/**
	 * Build font-family and font-weight CSS declaration for an icon span.
	 *
	 * Used as a declarationFunction in module_styles advancedStyles entries.
	 * Mirrors the approach used by BlurbModule::icon_style_declaration().
	 *
	 * @since 2.0.0
	 * @param array $params { attrValue: desktop value of the icon innerContent attr }.
	 * @return string CSS declaration string, e.g. "font-family: ETmodules !important; font-weight: 400;"
	 */
	public static function icon_font_declaration( array $params ): string {
		$icon = $params['attrValue'] ?? array();

		if ( empty( $icon ) ) {
			return '';
		}

		$style = new StyleDeclarations(
			array(
				'returnType' => 'string',
				'important'  => array( 'font-family' => true ),
			)
		);

		$font_family = ( isset( $icon['type'] ) && 'fa' === $icon['type'] ) ? 'FontAwesome' : 'ETmodules';
		$style->add( 'font-family', $font_family );

		if ( ! empty( $icon['weight'] ) ) {
			$style->add( 'font-weight', $icon['weight'] );
		}

		return $style->value();
	}

	/**
	 * Output module script data for the frontend.
	 *
	 * @since 2.0.0
	 * @param array $args Arguments provided by Divi.
	 */
	public static function module_script_data( $args ) {
		$args['elements']->script_data(
			array(
				'attrName' => 'module',
			)
		);
	}

	/**
	 * Render the module HTML for the frontend.
	 *
	 * Reads all custom attributes, calls lwp_get_hansel_and_gretel_breadcrumbs(),
	 * and returns the full Module::render() output.
	 *
	 * @since 2.0.0
	 * @param array  $attrs    Module attribute values.
	 * @param string $content  Inner block content (unused).
	 * @param object $block    Block object with parsed_block metadata.
	 * @param object $elements Divi elements helper.
	 * @return string  Rendered HTML.
	 */
	public static function render_callback( $attrs, $content, $block, $elements ) {

		// Breadcrumb content settings.
		$home_text            = sanitize_text_field( $attrs['homeText']['innerContent']['desktop']['value'] ?? 'Home' );
		$before_text          = sanitize_text_field( $attrs['beforeText']['innerContent']['desktop']['value'] ?? '' );
		$use_custom_home_link = $attrs['useCustomHomeLink']['innerContent']['desktop']['value'] ?? 'off';
		$home_link            = esc_url_raw( $attrs['homeLink']['innerContent']['desktop']['value'] ?? '' );

		// Icon settings.
		$use_before_icon  = $attrs['useBeforeIcon']['innerContent']['desktop']['value'] ?? 'off';
		$sep_icon_data    = $attrs['separatorIcon']['innerContent']['desktop']['value'] ?? array();
		$before_icon_data = $attrs['beforeIcon']['innerContent']['desktop']['value'] ?? array();

		// Decode separator icon via Divi's Utils (same approach as BlurbModule).
		// Fall back to ETmodules breadcrumb chevron (digit 9, U+0039 — same as D4) if no icon is set.
		$sep_char = Utils::process_font_icon( $sep_icon_data );
		if ( ! is_string( $sep_char ) || '' === $sep_char ) {
			$sep_char = html_entity_decode( '&#x39;', ENT_HTML5, 'UTF-8' );
		}

		// Build before-icon HTML if the toggle is on and an icon is chosen.
		$before_icon_html = '';
		if ( 'on' === $use_before_icon ) {
			$before_char = Utils::process_font_icon( $before_icon_data );
			if ( is_string( $before_char ) && '' !== $before_char ) {
				$icon_classes = 'before-icon et-pb-icon';
				if ( isset( $before_icon_data['type'] ) && 'fa' === $before_icon_data['type'] ) {
					$icon_classes .= ' et-pb-fa-icon';
				}
				$before_icon_html = HTMLUtility::render(
					array(
						'tag'               => 'span',
						'attributes'        => array( 'class' => $icon_classes ),
						'children'          => $before_char,
						'childrenSanitizer' => 'esc_html',
					)
				);
			}
		}

		// Generate the breadcrumb trail using the existing D4-compatible helper.
		$breadcrumbs_html = lwp_get_hansel_and_gretel_breadcrumbs(
			$home_text,
			$before_text,
			$sep_char,
			$use_custom_home_link,
			$home_link
		);

		if ( empty( $breadcrumbs_html ) ) {
			return '';
		}

		// Wrap in .lwp-breadcrumbs (before-icon goes before the trail).
		$breadcrumbs_wrapper = HTMLUtility::render(
			array(
				'tag'               => 'div',
				'attributes'        => array( 'class' => 'lwp-breadcrumbs' ),
				'children'          => $before_icon_html . $breadcrumbs_html,
				'childrenSanitizer' => 'et_core_esc_previously',
			)
		);

		$module_inner = HTMLUtility::render(
			array(
				'tag'               => 'div',
				'attributes'        => array( 'class' => 'et_pb_module_inner' ),
				'children'          => $breadcrumbs_wrapper,
				'childrenSanitizer' => 'et_core_esc_previously',
			)
		);

		$module_elements = $elements->style_components(
			array(
				'attrName' => 'module',
			)
		);

		return Module::render(
			array(
				'orderIndex'          => $block->parsed_block['orderIndex'],
				'storeInstance'       => $block->parsed_block['storeInstance'],
				'attrs'               => $attrs,
				'elements'            => $elements,
				'id'                  => $block->parsed_block['id'],
				'moduleClassName'     => 'lwp-breadcrumbs',
				'name'                => $block->block_type->name,
				'classnamesFunction'  => array( self::class, 'module_classnames' ),
				'moduleCategory'      => $block->block_type->category,
				'stylesComponent'     => array( self::class, 'module_styles' ),
				'scriptDataComponent' => array( self::class, 'module_script_data' ),
				'children'            => $module_elements . $module_inner,
			)
		);
	}
}

// Hook the module class into Divi's dependency tree.
// By the time this action fires (init priority 0), the Divi theme is fully
// loaded and et_builder_d5_enabled() is defined — safe to call here.
add_action(
	'divi_module_library_modules_dependency_tree',
	function ( $dependency_tree ) {
		if ( ! function_exists( 'et_builder_d5_enabled' ) || ! et_builder_d5_enabled() ) {
			return;
		}
		$dependency_tree->add_dependency( new Lwp_Breadcrumbs_D5_Module() );
	}
);
