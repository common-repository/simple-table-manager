<?php
  // Simple Table Manager

  defined( 'ABSPATH' ) or die( 'Direct access is not permitted' );

  function tstm_columns( $active_table ) {

    ?>
    <h2><?php esc_html_e( 'Simple Table Manager - Select Visible Columns', 'simple-table-manager' ); ?></h2>
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
      case '';
        break;
      case 'save':
        $visibles = isset( $_GET['visibles'] ) ? $_GET['visibles'] : array(); // array of visible column names
        foreach( $visibles as $key => $visible ) {
          $visibles[$key] = sanitize_text_field( $visible );
        }
        $hiddens = array();
        foreach( $columns as $name => $type) {
          if( ! in_array( $name, $visibles ) ) {
            $hiddens[] = $name;
          }
        }
        update_option( 'tstm_hiddens_'.$active_table, $hiddens );
        $message = __( 'Visible columns list saved.', 'simple-table-manager' );
        tstm_message( $message, 'success' );
        break;
      default:
        $message = __( 'Error: Unknown action', 'simple-table-manager' );
        tstm_message( $message, 'fail' );
    }

    tstm_message( $info['message'], $info['message_type'] );

    $hiddens = get_option( 'tstm_hiddens_'.$active_table, array() );

    ?>
    <form method="get" enctype="multipart/form-data">
    <input type="hidden" name="page" value="simple-table-manager">
    <input type="hidden" name="tab" value="columns">
    <input type="hidden" name="action" value="save">
    <table class="wp-list-table widefat settings">
      <thead>
        <th><?php esc_html_e( 'Name', 'simple-table-manager' ); ?></th>
        <th><?php esc_html_e( 'Type', 'simple-table-manager' ); ?></th>
        <th><?php esc_html_e( 'Visible', 'simple-table-manager' ); ?></th>
      </thead>
    <tbody>
    <?php
    foreach( $columns as $column_name => $type_code ) {
      $type_name = tstm_get_type_name( $type_code );
      ?>
      <tr>
      <?php
      if( $column_name == $key_name ) {
        ?>
        <td><?php echo esc_html( $column_name );?> *</td>
        <th><?php echo esc_html( $type_name ); ?></th>
        <td><input type="hidden" name="visibles[]" value="<?php echo esc_html( $column_name ); ?>"></td>
        <?php
      } else {
        ?>
        <td><?php echo esc_html( $column_name ); ?></td>
        <th><?php echo esc_html( $type_name ); ?></th>
        <td>
        <?php
        // stm_print_checkbox( $value, $checked, $name, $text ) {
        $checked = ! in_array( $column_name, $hiddens );
        tstm_print_checkbox( $column_name, $checked, 'visibles[]', '' );
        ?>
        </td>
        <?php
      }
      ?>
      </tr>
      <?php
    }
    ?>
    </tbody>
    </table>

    <?php
    $message = __( 'Columns which are not visible won\'t show on the other tabs.', 'simple-table-manager' );
    tstm_message( $message, 'help' );
      
    if( $key_name ) {
      $message = __( '* Indicates the primary key. The primary key must be visible.', 'simple-table-manager' );
      tstm_message( $message, 'help' );
    }

    ?>
    <div class="tablenav bottom">
    <input type="submit" value="<?php esc_html_e( 'Save', 'simple-table-manager' ); ?>" class="button button-primary" />
    </div>

    </form>
    <?php
  } // end function
