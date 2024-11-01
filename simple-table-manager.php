<?php
  /*
  Plugin Name: Simple Table Manager
  Description: Enables editing table records and exporting them to CSV files through a minimal database interface from your dashboard.
  Version:     1.6.0
  Author:      Ryo Inoue & lorro
  Author URI:  https://www.topcode.co.uk/developments/simple-table-manager/
  License:     GPLv3
  License URI: https://www.gnu.org/licenses/gpl-3.0.html
  Text Domain: simple-table-manager
  Domain Path: /languages/
  */
  
  defined( 'ABSPATH' ) or die( 'Direct access is not permitted' );
  
  $stm_version = '1.6.0';
  
  define( 'TSTM_PATH', plugin_dir_path( __FILE__ ) );
  // eg: TSTM_PATH = '/home/.../public_html/my-domain/wp-content/plugins/simple-table-manager/';
  
  define( 'TSTM_URL', plugin_dir_url( __FILE__ ) );
  // eg: STM_URL = 'http://www.my-domain.co.uk/wp-content/plugins/simple-table-manager/';
    
  define( 'TSTM_DELIMITER', ',' );
  define( 'TSTM_NEW_LINE', "\r\n" );
  
  // set default options
  // add_option() will not change the option if it exists
  add_option( 'tstm_table', '' );
  add_option( 'tstm_rows_per_page', 20 );
  add_option( 'tstm_unserialize', 1 );
  add_option( 'tstm_csv_filename', 'export.csv' );
  add_option( 'tstm_csv_encoding', 'UTF-8' );
  add_option( 'tstm_csv_columns', 'all' ); // 'all' or 'some'
  add_option( 'tstm_capability', 'edit_posts' ); 
      
  // translations
  add_action( 'plugins_loaded', 'tstm_custom_load_textdomain' );
  function tstm_custom_load_textdomain() {
    // load_plugin_textdomain( $domain, $abs_rel_path__DEPRECATED, $plugin_rel_path );
    load_plugin_textdomain( 'simple-table-manager', false,  TSTM_PATH. 'languages' );
  }
  
  // register style
  add_action( 'wp_loaded', 'tstm_register_assets' );
  function tstm_register_assets() {
    global $tstm_version;
    wp_register_style( 'tstm_admin_css', TSTM_URL.'css/admin.css', array(), $tstm_version );
  }
  
  // enqueue styles
  add_action( 'admin_enqueue_scripts', 'tstm_admin_enqueue_assets' );
  function tstm_admin_enqueue_assets() {
    wp_enqueue_style( 'tstm_admin_css' );
  }

  require_once( 'includes/add.php' );
  require_once( 'includes/columns.php' );
  require_once( 'includes/edit.php' );
  require_once( 'includes/export-csv.php' );
  require_once( 'includes/functions.php' );
  require_once( 'includes/list.php' );
  require_once( 'includes/main.php' );
  require_once( 'includes/settings.php' );
  require_once( 'includes/tables.php' );
     
  // add custom items to the dashboard
  add_action( 'admin_menu', 'tstm_add_menu_item' );
  function tstm_add_menu_item() {
    global $tstm_page;
    // add_menu_page( page_title, menu_title, capability, menu_slug, callable_function, icon_url, position )
    $tstm_page = add_menu_page(
      esc_html__( 'Simple Table Manager', 'simple-table-manager' ),
      esc_html__( 'Simple Table Manager', 'simple-table-manager' ),
      get_option( 'tstm_capability' ),
      'simple-table-manager',
      'tstm_main',
      '',
      76 );
    add_action( "load-$tstm_page", 'tstm_do_export' );
  } // end function
  