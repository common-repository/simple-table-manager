<?php
  // Simple Table Manager

  defined( 'ABSPATH' ) or die( 'Direct access is not permitted' );

  function tstm_add( $active_table ) {
    global $wpdb;

    ?>
    <h2><?php esc_html_e( 'Simple Table Manager - Add Record', 'simple-table-manager' ) ?></h2>
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

    $action = tstm_get_action(); // sanitized and validated
    
    switch( $action ) {
      case '':
        break;
      case 'save':
        // collect insert values and strip slashes
        $insert_vals = array();
        foreach ( $columns as $column_name => $type_code ) {
          $encoded_name = urlencode( $column_name );
          if( isset( $_GET[$encoded_name] ) ) { // no $encoded name for hidden or auto_increment fields
            $insert_vals[$column_name] = stripslashes_deep( $_GET[$encoded_name] );
          }
        }
        if( ! $key_name || $auto_increment ) {
          $outcome = $wpdb->insert( $active_table, $insert_vals );
          // $outcome = number of rows affected by a query. Could be 0, 1 or false if the operation failed
          if( false === $outcome ) {
            $message = __( 'Record not saved.', 'simple-table-manager' );
            tstm_message( $message, 'fail' );
          } else {            
            $message = __( 'Record saved.', 'simple-table-manager' );
            tstm_message( $message, 'success' );
          }
        } else {
          // check if the key exists already
          $query = 'SELECT * FROM `'.$active_table.'` WHERE `'.$key_name.'` = "'.$insert_vals[$key_name].'"';
          $wpdb->get_results( $query );
          $nr_rows = $wpdb->num_rows;
          if ( $nr_rows ) {
            $message = __( 'Key exists. Record not saved', 'simple-table-manager' );
            tstm_message( $message, 'fail' );
          } else {
            $outcome = $wpdb->insert( $active_table, $insert_vals );
            // $outcome = number of rows affected by a query. Could be 0, 1 or false if the operation failed
            if( false === $outcome ) {
              $message = __( 'Record not saved.', 'simple-table-manager' );
              tstm_message( $message, 'fail' );
            } else {            
              $message = __( 'Record saved.', 'simple-table-manager' );
              tstm_message( $message, 'success' );
            }
          }
        }
        break;
      default:
        $message = __( 'Invalid action', 'simple-table-manager' );
        tstm_message( $message, 'fail' );
    }

    tstm_message( $info['message'], $info['message_type'] );
    $message = ' '.sprintf( __( 'Showing %1$d columns out of %2$d.', 'simple-table-manager'), $nr_showing, $nr_columns );
    tstm_message( $message, 'help' );

    ?>
    <form method="get" enctype="multipart/form-data">
    <input type="hidden" name="page" value="simple-table-manager">
    <input type="hidden" name="tab" value="add">
    <input type="hidden" name="action" value="save">
    <table class="wp-list-table widefat settings">
      <thead>
        <th><?php esc_html_e( 'Name', 'simple-table-manager' ); ?></th>
        <th><?php esc_html_e( 'Type', 'simple-table-manager' ); ?></th>
        <th><?php esc_html_e( 'Value', 'simple-table-manager' ); ?></th>
      </thead>
    <tbody>
    <?php
    foreach ( $columns as $column_name => $type_code ) {
      $type_name = tstm_get_type_name( $type_code );
      if ( $column_name == $key_name ) {
        // the key field cannot be hidden
        ?>
        <tr>
        <th><?php echo esc_html( $column_name ).' *'; ?></th>
        <th><?php echo esc_html( $type_name ) ?></th>
        <?php
        if( $auto_increment ) {
          ?>
          <td><?php esc_html_e( 'auto-incrementing field', 'simple-table-manager' ); ?></td>
          <?php
        } else {
          if( $key_is_int ) {    
            $max_id = $wpdb->get_var( 'SELECT MAX( `'.$key_name.'` ) FROM `'.$active_table.'`' );
            $new_id = $max_id + 1;
          } else {
           // key cannot be auto-generated  
           $new_id = '';
          }
          $field_html = tstm_input( $column_name, $type_code, $new_id );
          $field_html = apply_filters( 'tstm_add_field_html', $field_html, $active_table, $column_name, $new_id );
          ?>
          <td><?php esc_htm( $field_html ); ?></td> <!-- not right for html -->
          <?php
        }
        ?>
        </tr>
        <?php
      } else {
        if( in_array( $column_name, $hiddens ) ) {
          continue;
        }
        ?>
        <tr>
          <td><?php echo esc_html( $column_name ); ?></td>
          <td><?php echo esc_html( $type_name ); ?></td>
        <?php
        $field_html = tstm_input( $column_name, $type_code, '', '' );
        $field_html = apply_filters( 'tstm_add_field_html', $field_html, $active_table, $column_name, '' );
        $allowed_html = tstm_get_allowed_html();
        ?>
        <td><?php echo wp_kses( $field_html, $allowed_html ); ?></td>
        </tr>
        <?php
      }
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
    <input type="submit" value="<?php esc_html_e( 'Save', 'simple-table-manager' ); ?>" class="button button-primary">
    </div>
    </form>
    <?php
  } // end function
