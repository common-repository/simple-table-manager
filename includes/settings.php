<?php
  // Simple Table Manager

  function tstm_settings( $active_table ) {

    ?>
    <h2><?php esc_html_e( 'Simple Table Manager - Settings', 'simple-table-manager' ); ?></h2>
    <?php
  
    $info = tstm_get_table_info( $active_table );

    $action = tstm_get_action(); // $action is sanitized and validated

    switch( $action ) {
      case '':
        break;
      case 'save':
        // table
        $active_table = isset( $_GET['active_table'] ) ? $_GET['active_table'] : '';
        // sanitize
        $active_table = sanitize_text_field( $active_table );
        update_option( 'tstm_active_table', $active_table );
        // rows per page
        $rows_per_page = isset( $_GET['rows_per_page'] ) ? $_GET['rows_per_page']: '10';
        $rows_per_page = tstm_validate_rows_per_page( $rows_per_page ); // sanitize and validate
        update_option( 'tstm_rows_per_page', $rows_per_page );
        // unserialize
        $unserialize = isset( $_GET['unserialize'] ) ? 1 : 0;
        update_option( 'tstm_unserialize', $unserialize );
        // capability
        $capability = isset( $_GET['capability'] ) ? $_GET['capability'] : '';
        $capability = tstm_validate_capability( $capability ); // sanitize and validate
        update_option( 'tstm_capability', $capability );
        $message = __( 'Settings saved', 'simple-table-manager' );
        tstm_message( $message, 'success' );
        break;
      default:
        $message = __( 'Error: Unknown action', 'simple-table-manager' );
        tstm_message( $message, 'fail' );
    }

    tstm_message( $info['message'], $info['message_type'] );

    // get settings
    $rows_per_page = tstm_validate_rows_per_page( get_option( 'tstm_rows_per_page' ) );
    $tables = tstm_get_tables();
    
    ?>
    <form method="get" enctype="multipart/form-data">
    <input type="hidden" name="page" value="simple-table-manager">
    <input type="hidden" name="tab" value="settings">
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
        <td><?php esc_html_e( 'Table name', 'simple-table-manager' ); ?></td>
        <td><select name="active_table">
        <option value=""><?php esc_html_e( 'Select a table ...', 'simple-table-manager' ); ?></option>
        <?php
        foreach ( $tables as $table ) {
          $selected = ( $table == $active_table ) ? 'selected' : '';
          echo '<option value="'.esc_html( $table ).'" '.esc_html( $selected ).'>'.esc_html( $table ).'</option>'.PHP_EOL;
        }
        ?>
        </select>
        </td>
      </tr>
      <tr>
        <td><?php esc_html_e( 'Max rows per page', 'simple-table-manager' ); ?></td>
        <td><input type="number" name="rows_per_page" value="<?php echo esc_html( $rows_per_page ); ?>"/></td>
      </tr>
      <?php
      $checked = get_option( 'tstm_unserialize' ) ? 'checked' : '' ;
      ?>
      <tr>
        <td style="max-width: 180px;"><?php esc_html_e( 'Edit tab: Show unserialized objects and arrays', 'simple-table-manager' ); ?></td>
        <td><input type="checkbox" name="unserialize" value="1" <?php echo esc_html( $checked ); ?>></td>
      </tr>
      <?php
      // capability
      $capability = tstm_validate_capability( get_option( 'tstm_capability' ) );
      ?>
      <tr>
        <td><?php esc_html_e( 'Capability required to use the plugin', 'simple-table-manager' ); ?></td>
        <td>
        <?php
        // tstm_print_radio_button( $value, $current_value, $name, $text ) {
        tstm_print_radio_button( 'edit_posts', $capability, 'capability', 'Authors and higher capability' );
        tstm_print_radio_button( 'manage_options', $capability, 'capability', 'Administrators only' );    
        ?>
        </td>
      </tr>
    </tbody>
    </table>
    <?php
    $message = __( 'Max rows per page limits: 1 to 1000.', 'simple-table-manager' );
    tstm_message( $message, 'help' );

    ?>
    <div class="tablenav bottom">
    <input type="submit" value="<?php esc_html_e( 'Save', 'simple-table-manager' ); ?>" class="button button-primary" />
    </div>

    </form>
    <?php
  } // end function
      