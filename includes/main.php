<?php
  // Simple Table Manager

  defined( 'ABSPATH' ) or die( 'Direct access is not permitted' );

  function tstm_main() {
    global $wpdb;
    if ( ! current_user_can( get_option( 'tstm_capability' ) ) )  {
      wp_die( __( 'You do not have permission to access this page.', 'simple-table-manager' ) );
    }

    // tabs
    $tabs = array(
      'settings' => esc_html__( 'Settings', 'simple-table-manager' ),
      'columns'  => esc_html__( 'Columns', 'simple-table-manager' ),
      'list'     => esc_html__( 'List', 'simple-table-manager' ),
      'edit'     => esc_html__( 'Edit', 'simple-table-manager' ),
      'add'      => esc_html__( 'Add', 'simple-table-manager' ),
      'export'   => esc_html__( 'Export CSV', 'simple-table-manager' ),
    );

    // active tab
    $tstm_active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : '';
    $tstm_active_tab = sanitize_text_field( $tstm_active_tab );
    if( ! array_key_exists( $tstm_active_tab, $tabs ) ) {
      $tstm_active_tab = 'settings';
    }

    // get active_table
    $active_table = get_option( 'tstm_active_table' );
    $active_table = sanitize_text_field( $active_table );

    $info = tstm_get_table_info( $active_table );
    if( ! $info['valid'] ) {
      $active_table = '';
      $tstm_active_tab = 'settings';
    }

    $key_value = isset( $_GET['key_value'] ) ? $_GET['key_value'] : '';
    $key_value = sanitize_text_field( $key_value );

    ?>
    <div class="wrap simple-table-manager">
    <nav class="nav-tab-wrapper">
    <?php
    foreach ( $tabs as $slug => $tab_name ) {
      if ( $slug == $tstm_active_tab ) {
        $tab_class = 'nav-tab nav-tab-active';
      } else {
        if( ! $info['valid'] || ( 'edit' == $slug && '' == $key_value ) ) {
          $tab_class = 'nav-tab disabled';
        } else {
          $tab_class = 'nav-tab ';
        }
      } // end if
      ?>
      <a href="?page=simple-table-manager&tab=<?php echo esc_html( $slug ); ?>" class="<?php echo esc_html( $tab_class ); ?>">
      <?php
        echo esc_html( $tab_name );
      ?>
      </a>
      <?php
    } // end foreach
    ?>
    </nav>
    <?php
        
    switch( $tstm_active_tab ) {
      case 'add':
        tstm_add( $active_table );
        break;
      case 'columns':
        tstm_columns( $active_table );
        break;
      case'edit':
        tstm_edit( $active_table );
        break;
      case 'export':
        tstm_export( $active_table );
        break;
      case 'list':
        tstm_list( $active_table );
        break;
      case 'settings':
        tstm_settings( $active_table );
        break;
      default:
        $message = __( 'Error: Invalid tab.', 'simple-table-manager' );
        tstm_message( $message, 'error' );
    }
    ?>
    </div>
    <?php
  } // end function
  