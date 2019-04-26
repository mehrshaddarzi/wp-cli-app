<?php

namespace WP_CLI_APP\WorkSpace;

use WP_CLI_APP\Utility\Wordpress;
use WP_CLI_APP\Package\Arguments\Dir;

/**
 * Class Plugin
 * @package WP_CLI_APP\WorkSpace
 */
class Plugin {

	/**
	 * Name of your Plugin package.
	 * e.g: WordPress Plugin Starter
	 *
	 */
	public $name = 'WordPress Plugin Starter';

	/**
	 * Plugin Package Description
	 * e.g: a new plugin package from WP-CLI-App
	 *
	 */
	public $description = '-';

	/**
	 * Developer Website Url
	 * e.g: github.com/...
	 *
	 */
	public $author_url = '-';

	/**
	 * Package Version
	 * e.g : 1.0.0
	 *
	 */
	public $version = '1.0.0';

	/**
	 * Plugin Package Url Type.
	 *
	 * Type List :
	 * gti  -> Clone Of Repository.
	 * url  -> Zip file Minor url.
	 *
	 */
	public $type = 'git';

	/**
	 * Link of your git Repository or Zip minor Package.
	 * e.g : https://github.com/DevinVinson/WordPress-Plugin-Boilerplate.git
	 *
	 */
	public $link = '';

	/**
	 * List of All words that you want search in replace in Files.
	 *
	 * Default argument list :
	 * {{plugin_name}}        -> Plugin name
	 * {{plugin_url}}         -> Plugins Url
	 * {{plugin_slug}}        -> Plugin Slug
	 * {{plugin_slug_class}}  -> Plugin slug With UCFirst Word
	 * {{plugin_description}} -> Plugin Description
	 * {{plugin_author_name}} -> Plugin author name
	 * {{plugin_author_url}}  -> Plugin author Url
	 * {{plugin_text_domain}} -> Plugin Text Domain
	 * {{plugin_namespace}}   -> Plugin Namespace
	 *
	 * for example :
	 * plugin_name => {{plugin_name}}
	 *
	 */
	public $replace = array();

	/**
	 * Rename File or folder after Download Package.
	 *
	 * For example :
	 * plugin-starter         => '{{plugin-slug}}',
	 * /admin/asset/script.js => 'script-{{plugin-author}}.js'
	 *
	 */
	public $rename = array();

	/**
	 * list of Command that run after setup complete.
	 * e.g : app composer install.
	 *
	 */
	public $command = array();

	/**
	 * Get Plugins Directory
	 * Default : wp-content/plugins
	 *
	 */
	public $plugins_path;

	/**
	 * Get Plugin Dir path
	 * e.g : wp-content/plugins/plugin-starter/
	 *
	 */
	public $plugin_dir;

	/**
	 * Plugin Base Template constructor.
	 */
	public function __construct() {

		//Get Plugins Path
		$this->plugins_path = Dir::get_plugins_dir();

	}

	/**
	 * This Method Run when Plugin Package Copy From Git Or Url to Plugins folder
	 *
	 * @see \WP_CLI_APP\Utility\Plugin [create method]
	 * @param $plugin_folder
	 * @param $plugin_data
	 * @return string
	 */
	public function after_copy( $plugin_folder, $plugin_data ) {
		//Return New Plugin Folder Name
		return $plugin_folder;
	}

	/**
	 * This Method Run when setup Plugin Package was completed.
	 *
	 * @see \WP_CLI_APP\Utility\Plugin [create method]
	 * @param $plugin_folder
	 * @param $plugin_data
	 */
	public function after_setup( $plugin_folder, $plugin_data ) {
	}


}