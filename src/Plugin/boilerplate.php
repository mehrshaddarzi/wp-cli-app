<?php

namespace WP_CLI_APP\Plugin;

use WP_CLI_APP\Utility\FileSystem;
use WP_CLI_APP\WorkSpace\Plugin;

class boilerplate extends Plugin {

	/**
	 * Name of your Plugin package.
	 */
	public $name = 'WordPress Plugin Boilerplate';

	/**
	 * Plugin Package Description
	 */
	public $description = 'A standardized, organized, object-oriented foundation for building high-quality WordPress Plugins.';

	/**
	 * Developer Website Url
	 */
	public $author_url = 'http://wppb.io/';

	/**
	 * Plugin Package Url Type.
	 */
	public $type = 'git';

	/**
	 * Link of your git Repository or Zip minor Package.
	 */
	public $link = 'https://github.com/DevinVinson/WordPress-Plugin-Boilerplate.git';

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
	public $rename = array();

	/**
	 * list of Command that run after setup complete.
	 * e.g : app composer install.
	 *
	 */
	public $command = array();

	/**
	 * This Method Run when Plugin Package Copy From Git Or Url to Plugins folder
	 *
	 * @see \WP_CLI_APP\Utility\Plugin [create method]
	 * @param $plugin_folder
	 * @param $plugin_data
	 * @return string | Plugin Name Folder
	 */
	public function after_copy( $plugin_folder, $plugin_data ) {

		//Get Full Path
		$path = FileSystem::path_join( $this->plugins_path, $plugin_folder );

		//Remove Files
		FileSystem::remove_file( FileSystem::path_join( $path, ".gitignore" ) );
		FileSystem::remove_file( FileSystem::path_join( $path, "CHANGELOG.md" ) );
		FileSystem::remove_file( FileSystem::path_join( $path, "README.md" ) );

		//Cut Plugin-name to Plugins dir
		FileSystem::move( FileSystem::path_join( $path, "plugin-name" ), FileSystem::path_join( $this->plugins_path, "plugin-name" ) );
		FileSystem::remove_dir( FileSystem::path_join( $this->plugins_path, "WordPress-Plugin-Boilerplate" ), true );
		FileSystem::rename( FileSystem::path_join( $this->plugins_path, "plugin-name" ), FileSystem::path_join( $this->plugins_path, $plugin_data['plugin_slug'] ) );

		//Return New Plugin Folder Name
		return $plugin_data['plugin_slug'];
	}

}