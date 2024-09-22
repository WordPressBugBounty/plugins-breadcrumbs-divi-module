<?php

class LWP_DiviBreadcrumbsClass extends DiviExtension {

	/**
	 * The gettext domain for the extension's translations.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $gettext_domain = 'lwp-divi-breadcrumbs';

	/**
	 * The extension's WP Plugin name.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $name = 'divi-breadcrumbs';

	/**
	 * The extension's version
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $version = '1.2.2';

	/**
	 * LWP_DiviBreadcrumbs constructor.
	 *
	 * @param string $name
	 * @param array  $args
	 */
	public function __construct( $name = 'divi-breadcrumbs', $args = array() ) {
		$this->plugin_dir     = plugin_dir_path( __FILE__ );
		$this->plugin_dir_url = plugin_dir_url( $this->plugin_dir );

		parent::__construct( $name, $args );
	}
}

new LWP_DiviBreadcrumbsClass;
