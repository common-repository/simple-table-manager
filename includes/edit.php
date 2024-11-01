<?php
  // Simple Table Manager

  defined( 'ABSPATH' ) or die( 'Direct access is not permitted' );

  function tstm_edit( $active_table ) {
    global $wpdb;
    $allowed_html = tstm_get_allowed_html();

    ?>
    <h2><?php esc_html_e( 'Simple Table Manager - Edit Record', 'simple-table-manager' ); ?></h2>
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
    $unserialize = get_option(' stm_unserialize' );
    
    $action = tstm_get_action(); // sanitized and validated
    $key_value = isset( $_GET['key_value'] ) ? sanitize_text_field( $_GET['key_value'] ) : '';

    switch( $action ) {
      case '':
        break;
      case 'save':
        // collect update values and strip slashes
        $update_vals = array();
        $new_key_value = $key_value;
        foreach ( $columns as $column_name => $type ) {
          $encoded_column_name = urlencode( $column_name );
          if( isset( $_GET[$encoded_column_name] ) ) { // no $encoded name for hidden fields
            $field_value = stripslashes_deep( $_GET[$encoded_column_name] );
            $update_vals[$column_name] = $field_value;
            if( $key_name == $column_name ) {
              $new_key_value = $field_value;
            }
          }
        }
        // update
        $outcome = $wpdb->update( $active_table, $update_vals, array( $key_name => $key_value ) );
        // $outcome = number of rows affected by a query. Could be 0, 1 or false if the operation failed
        if( false === $outcome ) {
          $message = __( 'Record not updated.', 'simple-table-manager' );
          tstm_message( $message, 'fail' );
        } else {            
          $message = __( 'Record updated.', 'simple-table-manager' );
          tstm_message( $message, 'success' );
        }
        // update $key_value
        $key_value = $new_key_value;
        break;
      case 'delete':
        $query = 'DELETE FROM `'.$active_table.'` WHERE `'.$key_name.'`="'.$key_value.'"';
        $outcome = $wpdb->query( $query );
        // $outcome = number of rows deleted. Could be 0, or false if update failed
        if( false === $outcome ) {
          $message = __( 'Record not deleted.', 'simple-table-manager' );
          tstm_message( $message, 'fail' );
        } else {            
          $message = __( 'Record deleted.', 'simple-table-manager' );
          tstm_message( $message, 'success' );
        }
        $key_value = '';
        break;
      default:
        $message = __( 'Invalid action', 'simple-table-manager' );
        tstm_message( $message, 'fail' );
    }

    tstm_message( $info['message'], $info['message_type'] );
    if( ! $key_value ) {
      if( 'delete' != $action ) {
        $message = __( 'Error: Key value missing.', 'simple-table-manager');
        tstm_message( $message, 'fail' );
      }
      tstm_back_button();
      return;
    }
    
    $row = tstm_get_row( $active_table, $key_name, $key_value );
    if( is_null( $row ) ) {
      $message = __( 'Record not found.', 'simple-table-manager' );
      tstm_message( $message, 'fail' );
      tstm_back_button();
      return;
    }  

    $message = ' '.sprintf( __( 'Showing %1$d columns out of %2$d.', 'simple-table-manager'), $nr_showing, $nr_columns );
    tstm_message( $message, 'help' );

    ?>
    <form method="get" enctype="multipart/form-data">
    <input type="hidden" name="page" value="simple-table-manager">
    <input type="hidden" name="tab" value="edit">
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="key_value" value="<?php echo esc_html( $key_value ); ?>">
    <table class="wp-list-table widefat settings">
      <thead>
        <tr>
          <th><?php echo esc_html( 'Name', 'simple-table-manager' ); ?></th>
          <th><?php echo esc_html( 'Type', 'simple-table-manager' ); ?></th>
          <th><?php echo esc_html( 'Value', 'simple-table-manager' ); ?></th>
          <?php
          if( $unserialize ) {
            ?>
            <th></th>
            <?php
          }
        ?>
        </tr>
      </thead>
    <tbody>
    <?php
    foreach ( $row as $column_name => $field_value ) {
      if( in_array( $column_name, $hiddens ) ) {
        continue;
      }
      $type_code = $columns[$column_name];
      $type_name = tstm_get_type_name( $type_code );
      $asterisk = $key_name == $column_name ? '&nbsp;*' : '';
      ?>
      <tr>
        <td><?php echo esc_html( $column_name.$asterisk ); ?></td>
        <td><?php echo esc_html( $type_name ); ?></td>
      <?php
      $field_html = tstm_input( $column_name, $type_code, $field_value );
      $field_html = apply_filters( 'tstm_edit_field_html', $field_html, $active_table, $column_name, $field_value );
      ?>
        <td><?php echo wp_kses( $field_html, $allowed_html ); ?></td>
      <?php
      if( $unserialize ) {
        $valid = false;
        if( 'string' == gettype( $field_value ) ) {
          $classes = array(); // no classes accepted
          $result = @unserialize( $field_value, $classes ); // suppress any E_NOTICE
          ob_start();
          if( false != $result ) {
            $result_type = gettype( $result );
            switch( $result_type ) {
              case 'boolean':
                $result ? esc_html_e( 'True', 'simple-table-manager' ) : esc_html_e( 'False', 'simple-table-manager' );
                $valid = true;
                break;
              case 'integer':
              case 'sring':
                echo esc_html( $result );
                $valid = true;
                break;
              case 'array':
              case 'object':
                ?>
                <pre><?php echo print_r( esc_html( $result ) ); ?></pre>
                <?php
                $valid = true;
                break;
              default:
                // error, nothing to print
            }
          }
          $html = ob_get_clean();
        }
        if( $valid ) {
          ?>
          <td><div class="unserialized"><?php echo $html; ?></div></td>
          <?php
        } else {
          ?>
          <td></td>
          <?php
        }
      }
      ?>
      </tr>
      <?php
    }
    ?>
    </tbody>
    </table>
    <?php
    if( $key_name ) {
      $message = __( '* Indicates the primary key.', 'simple-table-manager' );
      tstm_message( $message, 'help' );
    }

    ?>
    <div class="tablenav bottom">
    <input type="submit" value="<?php esc_html_e( 'Save', 'simple-table-manager' ); ?>" class="button button-primary">&nbsp;
    </div>
    </form>

    <form method="get" enctype="multipart/form-data">
    <input type="hidden" name="page" value="simple-table-manager">
    <input type="hidden" name="tab" value="edit">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="key_value" value="'.$key_value.'">
    <div class="tablenav bottom">
    <input type="submit" value="<?php esc_html_e( 'Delete', 'simple-table-manager' ); ?>" class="button button-primary" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'simple-table-manager' ); ?>')">
    </div>
    </form>
    <?php
  } // end function

  function tstm_back_button() {
    ?>
    <form method="get" enctype="multipart/form-data">
    <input type="hidden" name="page" value="simple-table-manager">
    <input type="hidden" name="tab" value="list">
    <input type="submit" value="<?php esc_html_e( 'Return to list', 'simple-table-manager' ); ?>" class="button button-primary">
    </form>
    <?php
  } // end function 
