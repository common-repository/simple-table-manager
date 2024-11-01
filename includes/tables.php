<?php
  // Simple Table Manager

  defined( 'ABSPATH' ) or die( 'Direct access is not permitted' );
  
  function tstm_get_table_info( $active_table ) {
    global $wpdb;
    $info = array();
    // check $table
    if( ! $active_table ) {
      $info['valid'] = false;
      // message is escaped later
      $info['message'] = __( 'Active table not set. Please select a table.', 'simple-table-manager' ); // escaped later
      $info['message_type'] = 'error';
    } else {
      $tables = tstm_get_tables();
      if( ! in_array( $active_table, $tables ) ) {
        $info['valid'] = false;
        // message is escaped later
        $info['message']= sprintf( __( 'Error: Table %s does not exist.', 'simple-table-manager' ), $active_table );
        $info['message_type'] = 'error';
      } else {
        // get column information
        $columns = array(); // array( 'name' => type )
        $wpdb->get_row( 'SELECT * FROM `'.$active_table.'`' ); // dummy
        foreach( $wpdb->get_col_info('name') as $col_nr => $name ) {
          $columns[$name] = $wpdb->col_info[$col_nr]->type;
        }
        $info['columns'] = $columns;
        // visible
        $info['hiddens'] = get_option( 'tstm_hiddens_'.$active_table, array() ); // array( 'name' )
        // check for primary key
        $rows = $wpdb->get_results( 'SHOW KEYS FROM `'.$active_table.'` WHERE `Key_name` = "PRIMARY"' );
        // $results = an array of key objects, where Key_name = "PRIMARY"
        $nr_keys = $wpdb->num_rows;
        switch ( $nr_keys ) {
          case 0:
            // no primary key set, this plugin won't work
            $info['valid'] = true;
            // message is escaped later
            $info['message'] = sprintf( __( 'Active table: "%s". Warning: This table does not have a primary key. Editing or deleting records is not possible.', 'simple-table-manager' ), $active_table );
            $info['message_type'] = 'help';
            $info['key_name'] = '';
            $info['key_is_int'] = false;
            $info['auto_increment'] = false;
            break;
          case 1:
            $info['valid'] = true;
            // message is escaped later
            $info['message'] = sprintf( __( 'Active table: "%s"', 'simple-table-manager' ), $active_table );
            $info['message_type'] = 'help';
            $row = $wpdb->get_row( 'SHOW FIELDS FROM `'.$active_table.'` WHERE `Key` = "PRI"' );
            $info['key_name'] = $row->Field;
            // check if key is of type integer
            $info['key_is_int'] = stristr( $row->Type, 'int' );
            // find out if the key field auto_increment
            $query = 'SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME="'.$active_table.'" AND COLUMN_NAME="'.$info['key_name'].'" AND EXTRA like "%auto_increment%"';
            $wpdb->get_results( $query );
            $nr_rows = $wpdb->num_rows;
            if( 1 == $nr_rows ) {
              $info['auto_increment'] = true;
            } else {
              $info['auto_increment'] = false;
            }
            // just right
            break;
          default:
            // more than one primary key = error
            $info['valid'] = false;
            // message is escaped later
            $info['message'] = sprintf( __( 'Active table: "%s". Error: This table has more than one primary key.', 'simple-table-manager' ), $active_table );
            $info['message_type'] = 'error';
            $info['key_name'] = '';
            $info['key_is_int'] = false;
            $info['auto_increment'] = false;
        }
      }
    }
    return $info;
  } // end function

  function tstm_get_tables() {
    global $wpdb;
    $tables = array();
    foreach ( $wpdb->get_results( "SHOW TABLES" ) as $row ) {
      foreach ( $row as $table ) {
        $tables[] = $table;
      }
    }
    return $tables;
  } // end function

  function tstm_select_all( $active_table ) {
    $active_table = sanitize_text_field( $active_table );
    global $wpdb;
    $query = 'SELECT * FROM `'.$active_table.'`';
    return $wpdb->get_results( $query );
  } // end function

  function tstm_select( $active_table, $columns, $keyword, $order_by, $order, $start_row, $end_row ) {
    global $wpdb;
    $where = tstm_get_where_clause( $columns, $keyword );
    $order = tstm_get_order_clause( $order_by, $order );
    $query = 'SELECT * FROM `'.$active_table.'` '.$where.' '.$order.' LIMIT '.$start_row.', '.$end_row;
    return $wpdb->get_results( $query );
  } // end function

  function tstm_count_rows( $active_table, $columns, $keyword ) {
    global $wpdb;
    $where = tstm_get_where_clause( $columns, $keyword );
    $query = 'SELECT COUNT(*) FROM `'.$active_table.'` '.$where;
    return $wpdb->get_var( $query );
  } // end function

  function tstm_get_where_clause( $columns, $keyword ) {
    global $wpdb;
    $where = '';
    if ( '' != $keyword ) {
      $like_statements = array();
      foreach ( $columns as $name => $type ) {
        $search_like = '%'.$wpdb->esc_like( $keyword ).'%';
        $like_statements[] = $wpdb->prepare( ' `'.$name.'` LIKE %s', $search_like );
      }
      $where = ' WHERE ' . implode( ' OR ', $like_statements );
    }
    return $where;
   } // end function

  function tstm_get_order_clause( $order_by, $order ) {
    $query = '';
    if ( '' != $order_by ) {
      $order = esc_sql( $order );
      $order_by = esc_sql( $order_by );
      $query = ' ORDER BY `'.$order_by.'` '.$order;
    }
    return $query;
  } // end function

  function tstm_get_row( $active_table, $key_name, $key_value ) {
    global $wpdb;
    $sql = $wpdb->prepare( 'SELECT * FROM `'.$active_table.'` WHERE `'.$key_name.'` = \'%s\'', $key_value );
    return $wpdb->get_row( $sql );
  } // end function
  