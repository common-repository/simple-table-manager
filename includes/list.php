<?php
  // Simple Table Manager

  defined( 'ABSPATH' ) or die( 'Direct access is not permitted' );

  function tstm_list( $active_table ) {
    $allowed_html = tstm_get_allowed_html();

    ?>
    <h2><?php esc_Html_e( 'Simple Table Manager - List Records', 'simple-table-manager' ); ?></h2>
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
    $keyword = isset( $_GET['keyword'] ) ? sanitize_text_field( $_GET['keyword'] ) : '';
    $order = isset( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ) : '';
    $order_by = isset( $_GET['order_by'] ) ? sanitize_text_field( $_GET['order_by'] ) : '';
    $start_row = absint( isset( $_GET['start_row'] ) ? sanitize_text_field( $_GET['start_row'] ) : 0 );
    $rows_per_page = tstm_validate_rows_per_page( get_option( 'stm_rows_per_page' ) ); 

    if( 'search' == $action ) {
      $keyword = stripslashes_deep( $keyword );
    }

    // manage record quantity
    $total = tstm_count_rows( $active_table, $columns, $keyword ); // count all data rows
    $next_start_row = $start_row + $rows_per_page;
    if ( $total < $next_start_row ) {
      $next_start_row = $total;
    }
    $last_start_row = $rows_per_page * ( floor( ( $total - 1 ) / $rows_per_page ) );

    // get rows to display
    $rows = tstm_select( $active_table, $columns, $keyword, $order_by, $order, $start_row, $rows_per_page );

    if ( $keyword ) {
      if( '1' == $total ) {
        $message = sprintf( __( 'Found %1$s result for search term: "%2$s"', 'simple-table-manager' ), $total, $keyword );
      } else {
        $message = sprintf( __( 'Found %1$s results for search term: "%2$s"', 'simple-table-manager' ), $total, $keyword );
      }
      tstm_message( $message, 'success' );
    }

    tstm_message( $info['message'], $info['message_type'] );
    $message = ' '.sprintf( __( 'Showing %1$d columns out of %2$d.', 'simple-table-manager'), $nr_showing, $nr_columns );
    tstm_message( $message, 'help' );

    // search box
    ?>
    <form method="get" enctype="multipart/form-data" class="search">
    <input type="hidden" name="page" value="simple-table-manager">
    <input type="hidden" name="tab" value="list">
    <input type="hidden" name="action" value="search">
    <input type="search" name="keyword" placeholder="<?php esc_html_e( 'Search', 'simple-table-manager' ); ?>&hellip;" value="" />
    <input type="submit" value="Go" class="button button-secondary">
    </form>
    <?php

    if ( ! count( $rows ) ) {
      $message = __( 'No rows found.', 'simple-table-manager' );
      tstm_message( $message, 'help' );
      return;
    }

    ?>
    <table class="wp-list-table widefat list">
    <thead>
      <tr>
      <?php
      if( $key_name ) {
        // empty column for edit link
        ?>
        <th></th>
        <?php
      }
      // column names
      $condition = array( 'search' => $keyword );
      foreach ( $columns as $column_name => $type_code ) {
        if( in_array( $column_name, $hiddens ) ) {
          continue;
        }
        $condition['order_by'] = $column_name;
        if ( $column_name == $order_by and 'ASC' == $order ) {
          ?>
          <th scope="col" class="manage-column sortable asc">
          <?php
          $condition['order'] = 'DESC';
        } else {
          ?>
          <th scope="col" class="manage-column sortable desc">
          <?php
          $condition['order'] = 'ASC';
        }
        $url = '?page=simple-table-manager&tab=list&#038'.http_build_query( $condition );
        ?>
        <a href="<?php echo esc_url( $url ); ?>">
        <?php
        $asterisk = $column_name == $key_name ? '&nbsp;*' : '';
        ?>
        <span><?php echo esc_html( $column_name.$asterisk ); ?></span><span class="sorting-indicator"></span></a></th>
        <?php
      }
      ?>
      </tr>
    </thead>
    <tbody>
    <?php
    // rows    
    foreach ( $rows as $row ) { // $row = object( $column_name => $field_value ... )
      ?>
      <tr>
        <?php
        $key_value = isset( $row->$key_name ) ? $row->$key_name : '';
        if( $key_name ) {
          // edit link in first column
          $url = '?page=simple-table-manager&tab=edit&key_value='.$key_value;
          ?>
          <td><a href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'Edit', 'simple-table-manager' ); ?></a></td>
          <?php
        }
        // values
        foreach ( $row as $column_name => $field_value ) {
          if( in_array( $column_name, $hiddens ) ) {
            continue;
          }
          $field_value = apply_filters( 'tstm_list_field', $field_value, $active_table, $column_name, $key_value );
          ?>
          <td><?php echo wp_kses( $field_value, $allowed_html ); ?></td>
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
    
    if( $key_name ) {
      $message = __( '* Indicates the primary key.', 'simple-table-manager' );
      tstm_message( $message, 'help' );
    }

    $message = sprintf( __( 'Total %s records.', 'simple-table-manager' ), number_format( $total ) );
    tstm_message( $message, 'help' );

    // table footer
    ?>
    <div class="tablenav bottom">
    <div class="tablenav-pages">
    <span class="pagination-links">
    <?php
      // navigation
      if( ! isset( $order_by ) ) {
        $order_by = '';
        $order = '';
      }
      $condition = array( 'search' => $keyword, 'order_by' => $order_by, 'order' => $order );
      $query = http_build_query( $condition );
      if ( 0 < $start_row) {
        $url = '?page=simple-table-manager&#038tab=list&#038start_row=0&038'.$query;
        ?>
        <a href="<?php echo esc_html( $url ); ?>" title="first page" class="first-page disabled button button-secondary">&laquo;</a>
        <?php
        $url = '?page=simple-table-manager&#038tab=list&#038start_row='.( $start_row - $rows_per_page ).'&038'.$query;
        ?>
        <a href="<?php echo esc_url( $url ); ?>" title="previous page" class="first-page disabled button button-secondary">&lsaquo;</a>
        <?php
      } else {
        ?>
        <a title="first page" class="first-page disabled button button-secondary">&laquo;</a>
        <a title="previous page" class="prev-page disabled button button-secondary">&lsaquo;</a>
        <?php
      }
      ?>
      <span class='paging-input'><?php echo number_format($start_row + 1); ?> - <span class='total-pages'><?php echo number_format($next_start_row); ?> </span></span>
      <?php
      if ( $next_start_row < $total ) {
        $url = '?page=simple-table-manager&tab=list&start_row='.$next_start_row.'&'.$query;
        ?>
        <a href="<?php echo esc_url( $url ); ?>" title="next page" class="next-page button button-secondary">&rsaquo;</a>
        <?php
        $url = '?page=simple-table-manager&tab=list&start_row='.$last_start_row.'&'.$query;
        ?>
        <a href="<?php echo esc_url( $url ); ?>" title="last page" class="last-page button button-secondary">&raquo;</a>
        <?php
      } else {
        ?>
        <a title="next page" class="next-page disabled button button-secondary">&rsaquo;</a>
        <a title="last page" class="last-page disabled button button-secondary">&raquo;</a>
        <?php
      }
    ?>
    </span>
    </div>
    <?php
  } // end function
      