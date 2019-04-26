<?php

namespace WP_CLI_APP\Utility;

class List_Format {

	/*
	 * Get Base Wordpress Path
	 */
	public $base_path;

	/*
	 * Get Default List dir name
	 */
	public $list_dir;

	/*
	 * Get Default List dir path
	 */
	public $list_dir_path;

	/*
	 * Get List Of Template.
	 */
	public $template;

	/**
	 * Construct List class
	 */
	public function __construct() {
		global $WP_CLI_APP;

		//Create List folder if not exist
		if ( ! FileSystem::folder_exist( FileSystem::path_join( getcwd(), $WP_CLI_APP['list_format_dir'] ) ) ) {
			FileSystem::create_dir( $WP_CLI_APP['list_format_dir'], getcwd() );
		}

		//Set Default folder
		$this->base_path     = getcwd();
		$this->list_dir      = $WP_CLI_APP['list_format_dir'];
		$this->list_dir_path = FileSystem::path_join( $this->base_path, $WP_CLI_APP['list_format_dir'] );
		$this->template      = array(
			"json"  => '.json',
			"array" => '.php'
		);
	}

	/**
	 * Check Template
	 *
	 * @param $template
	 * @return bool
	 */
	public function check_template( $template ) {
		if ( ! array_key_exists( $template, $this->template ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Generate List filename
	 *
	 * @param array $list
	 * @param $command
	 * @param $template
	 * @return string
	 */
	public function generate_file( $list, $command, $template ) {

		//Create File
		$content = $this->{'template_' . $template}( $list, $command );

		//Get file Name
		$date_time = ( function_exists( 'current_time' ) ? current_time( "Y-m-d_H-i-s" ) : date( "Y-m-d_H-i-s" ) );
		$file_name = '#' . $command . '_' . $date_time . $this->template[ $template ];

		//Put To file
		$file_path = rtrim( $this->list_dir_path, "/" ) . '/' . $file_name;
		if ( FileSystem::file_put_content( $file_path, $content ) ) {
			return $file_path;
		}

		return false;
	}

	/**
	 * Run Command
	 *
	 * @param array $list
	 * @param $command
	 * @param $template
	 */
	public function run( $list, $command, $template ) {

		//Check Template
		if ( $this->check_template( $template ) === false ) {
			CLI::log( "\n" . CLI::color( "Error:", 'r' ) . "Your Template is not valid. Use This Command:" );
			$items = array(
				array(
					'name'        => 'json',
					'example'     => 'wp app list:.. --s=json',
					'description' => 'Export as Json file',
				),
				array(
					'name'        => 'array',
					'example'     => 'wp app list:.. --s=array',
					'description' => 'Export as PHPArray file',
				)
			);
			CLI::create_table( $items, false, false );
			exit;
		}

		//Save File
		$file = $this->generate_file( $list, $command, $template );

		//Error or Success
		if ( $file === false ) {
			CLI::error( "an error in creating file. please Tray again." );

		} else {
			CLI::br();
			$item = array(
				'File'         => basename( $file ),
				'DownloadLink' => FileSystem::path_join( Wordpress::get_site_url(), FileSystem::path_join( $this->list_dir, basename( $file ) ) ),
				'Size'         => FileSystem::size_format( $file ),
			);
			//Check if Wordpress is installed
			if ( trim( Wordpress::get_site_url() ) == '' ) {
				unset( $item['DownloadLink'] );
				$item['File'] = $this->list_dir . '/' . basename( $file );
			}
			$info[] = $item;
			CLI::create_table( $info, false );
		}
	}

	/**
	 * Template Json
	 *
	 * @param array $list
	 * @param $command
	 * @return string
	 */
	public static function template_json( $list, $command ) {
		return json_encode( $list, JSON_PRETTY_PRINT );
	}

	/**
	 * Template array
	 *
	 * @param array $list
	 * @param $command
	 * @return string
	 */
	public static function template_array( $list, $command ) {
		$t = "<?php\n\n";
		$t .= '$' . str_replace( "-", "_", FileSystem::sanitize_folder_name( $command ) ) . ' = ' . preg_replace( "/[0-9]+ \=\>/i", '', var_export( $list, true ) ) . ';';
		$t .= "\n\n?>";
		return $t;
	}

}