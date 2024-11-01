<?php
  // Simple Table Manager

  defined( 'ABSPATH' ) or die( 'Direct access is not permitted' );

  function tstm_export( $active_table ) {
    global $wpdb;

    ?>
    <h2><?php esc_html_e( 'Simple Table Manager - Export CSV', 'simple-table-manager' ); ?></h2>
    <?php
      
    $info = tstm_get_table_info( $active_table );
    $columns = $info['columns']; // array( 'name' => type )
    $nr_columns = count( $columns );
    $key_name = $info['key_name'];
    $key_is_int = $info['key_is_int'];
    $auto_increment = $info['auto_increment'];
    $hiddens = $info['hiddens'];
    $nr_hiddens = count( $hiddens );
    $nr_showing = $nr_columns - $nr_hiddens;

    $csv_filename = sanitize_text_field( get_option( 'tstm_csv_filename' ) );
    $csv_encoding = sanitize_text_field( get_option( 'tstm_csv_encoding' ) );

    $action = tstm_get_action(); // sanitized and validated

    switch( $action ) {
      case '':
        break;
      case 'save':
        // csv filename
        $csv_filename = isset( $_GET['csv_filename'] ) ? sanitize_text_field( $_GET['csv_filename'] ) : '';
        update_option( 'tstm_csv_filename', $csv_filename );
        // csv_encoding
        $csv_encoding = isset( $_GET['csv_encoding'] ) ? sanitize_text_field( $_GET['csv_encoding'] ) : '';
        update_option( 'tstm_csv_encoding', $csv_encoding );
        // csv_columns
        // setting will be hidden if columns are not filtered
        $csv_columns = isset( $_GET['csv_columns'] ) ? sanitize_text_field( $_GET['csv_columns'] ) : '';
        if( 'all' == $csv_columns || 'some' == $csv_columns ) {
          update_option( 'tstm_csv_columns', $csv_columns );
        }
        $message = __( 'Settings saved', 'simple-table-manager' );
        tstm_message( $message, 'success' );
        break;
      default:
        $message = __( 'Error: Invalid action.', 'simple-table-manager' );
        tstm_message( $message, 'fail' );
    }

    tstm_message( $info['message'], $info['message_type'] );

    // get settings
    $csv_filename = sanitize_text_field( get_option( 'tstm_csv_filename' ) );
    $csv_encoding  = sanitize_text_field( get_option( 'tstm_csv_encoding' ) );
    $csv_columns  = sanitize_text_field( get_option( 'tstm_csv_columns' ) ); // 'all' or 'some'

    ?>
    <form method="get" enctype="multipart/form-data">
    <input type="hidden" name="page" value="simple-table-manager">
    <input type="hidden" name="tab" value="export">
    <input type="hidden" name="action" value="save">

    <table class="wp-list-table widefat settings">
      <thead>
        <tr>
          <th><?php esc_html_e( 'Option', 'simple-table-manager' ); ?></th>
          <th><?php esc_html_e( 'Value', 'simple-table-manager' ); ?></th>
        </tr>
      </thead>
    <tbody>
    <tr>
      <td class="simple-table-manager"><?php esc_html_e( 'CSV file name', 'simple-table-manager' ); ?></td>
      <td><input type="text" name="csv_filename" value="<?php echo esc_html( $csv_filename ); ?>"/></td>
    </tr>
    <tr>
      <td class="simple-table-manager"><?php esc_html_e( 'CSV encoding', 'simple-table-manager' ); ?></td>
      <td><input type="text" name="csv_encoding" value="<?php echo esc_html( $csv_encoding ); ?>"/></td>
    </tr>
    <?php
    if( $nr_showing < $nr_columns ) {
      ?>
      <tr>
        <td class="simple-table-manager"><?php esc_html_e( 'Columns', 'simple-table-manager' ); ?></td>
        <td>
        <?php
        // stm_print_radio_button( $value, $current_value, $name, $text )
        $text = __( 'All columns',' simple-table-manager' );
        tstm_print_radio_button( 'all', $csv_columns, 'csv_columns', $text );
        $text = sprintf( __( 'Selected %1$d columns out of %2$d', 'simple-table-manager'), $nr_showing, $nr_columns );
        tstm_print_radio_button( 'some', $csv_columns, 'csv_columns', $text );
        ?>
        </td>
      </tr>
      <?php
    }
    ?>
    </tbody>
    </table>

    <?php
    $message = __( 'The default CSV encoding is: UTF-8', 'simple-table-manager' );
    tstm_message( $message, 'help' );
    ?>
      
    <div class="tablenav bottom">
    <input type="submit" value="<?php esc_html_e( 'Save', 'simple-table-manager' ); ?>" class="button button-primary" />
    </div>
    </form>

    <div class="tablenav bottom">
    <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="page" value="simple-table-manager">
    <input type="hidden" name="tab" value="export">
    <input type="hidden" name="export" value="true">
    <input type="submit" value="<?php esc_html_e( 'Export', 'simple-table-manager' ); ?>" class="button button-primary" />
    </form>
    </div>
    <?php
  } // end function

  function tstm_do_export() {
    if( isset( $_POST['export'] ) ) {
      $active_table = get_option( 'tstm_active_table' );
      $info = tstm_get_table_info( $active_table);
      $columns = $info['columns'];
      $hiddens = $info['hiddens'];
      $csv_filename = get_option( 'tstm_csv_filename' );
      $csv_encoding = get_option( 'tstm_csv_encoding' );
      $csv_columns = get_option( 'tstm_csv_columns' );

      // output contents
      header( "Content-Type: application/octet-stream" );
      header( "Content-Disposition: attachment; filename=".$csv_filename );
      // column names
      foreach ( $columns as $column_name => $type_code ) {
        if( 'some' == $csv_columns ) {
          if( in_array( $column_name, $hiddens ) ) {
            continue;
          }
        }
        echo( esc_html( $column_name ) . TSTM_DELIMITER );
      }
      echo( TSTM_NEW_LINE );
      // fields
      $rows = tstm_select_all( $active_table ); // array( object column_name->value, column_name->value ... )
      foreach ( $rows as $row ) {
        foreach ( $row as $column_name => $field_value ) {
          if( 'some' == $csv_columns ) {
            if( in_array( $column_name, $hiddens ) ) {
              continue;
            }
          }
          $string = preg_replace( '/"/', '""', $field_value );
          echo( "\"" . mb_convert_encoding( $string, $csv_encoding, 'UTF-8' ) . "\"" . TSTM_DELIMITER );
        }
        echo( TSTM_NEW_LINE );
      }
      exit;
    }
  } // end function
      