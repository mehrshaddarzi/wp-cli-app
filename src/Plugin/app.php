<?php

namespace WP_CLI_APP\Plugin;
use WP_CLI_APP\Utility\FileSystem;
use WP_CLI_APP\WorkSpace\Plugin;

class app extends Plugin {

	/**
	 * Name of your Plugin package.
	 */
	public $name = 'WordPress Plugin Starter';

	/**
	 * Plugin Package Description
	 */
	public $description = 'A Wordpress Plugin Starter';

	/**
	 * Developer Website Url
	 */
	public $author_url = 'http://wp-cli-app.com/';

	/**
	 * Plugin Package Url Type.
	 */
	public $type = 'git';

	/**
	 * Link of your git Repository or Zip minor Package.
	 */
	public $link = 'https://github.com/wp-cli-application/plugin-starter.git';

	/**
	 * List of All words that you want search in replace in Files.
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
	public $rename = array(
		'plugin-slug.mustache'                       => '{{plugin_slug}}.php',
		'includes/plugin-slug-class_Loader.mustache' => 'includes/{{plugin_slug_class}}_Loader.php',
	);

	/**
	 * list of Command that run after setup complete.
	 * e.g : app composer install.
	 *
	 */
	public $command = array(
		'composer dump-autoload'
	);

	/**
	 * This Method Run when Plugin Package Copy From Git Or Url to Plugins folder
	 *
	 * @see \WP_CLI_APP\Utility\Plugin [create method]
	 * @param $plugin_folder
	 * @param $plugin_data
	 * @return string
	 */
	public function after_copy( $plugin_folder, $plugin_data ) {
		//Get Full Path
		$path = FileSystem::path_join( $this->plugins_path, $plugin_folder );

		//Remove ReadMe File
		FileSystem::remove_file( FileSystem::path_join( $path, "README.md" ) );

		//Rename Plugin Folder
		FileSystem::rename( FileSystem::path_join( $this->plugins_path, "plugin-starter" ), FileSystem::path_join( $this->plugins_path, $plugin_data['plugin_slug'] ) );

		//Return New Plugin Folder Name
		return $plugin_data['plugin_slug'];
	}

}