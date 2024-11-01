<?php
  // Simple Table Manager

  defined( 'ABSPATH' ) or die( 'Direct access is not permitted' );

  if ( ! function_exists( 'write_log' ) ) {
    function write_log ( $log )  {
      if ( is_array( $log ) || is_object( $log ) ) {
        error_log( print_r( $log, true ) );
      } else {
        error_log( $log );
      }
    } // end function
  }

  function tstm_get_action() {
    $action = isset( $_GET['action'] ) ? $_GET['action'] : '';
    $action = sanitize_text_field( $action );
    $allowed_actions = array( '', 'delete', 'save', 'search' );
    if( ! in_array( $action, $allowed_actions ) ) {
      $action = '';
    }
    return $action;
  } // end function

  function tstm_message( $message, $type ) {
    // incoming message is already translated but not escaped
    if( ! $message ) {
      return;
    }
    switch( $type ) {
      case 'success':
        ?>
        <div class="updated">
        <p class="tstm_help"><?php echo esc_html( $message ); ?></p>
        </div>
        <?php
        break;
      case 'fail':
        ?>
        <div class="updated">
        <p class="tstm_error"><?php echo esc_html( $message ); ?></p>
        </div>
        <?php
        break;
      case 'help':
        ?>
        <p class="tstm_help"><?php echo esc_html( $message ); ?></p>
        <?php
        break;
      case 'error':
        ?>
        <p class="tstm_error"><?php echo esc_html( $message ); ?></p>
        <?php
        break;
      default:
        ?>
        <p class="tstm_error"><?php esc_html_e( 'Invalid message type.', 'simple-table-manager' ); ?></p>
        <?php
    }
  } // end function

  // print a radio button
  // if the value is the current_value, the button is selected
  function tstm_print_radio_button( $value, $current_value, $name, $text ) {
    ?>
    <p>
    <label>
    <?php
    if ( $value == $current_value ) {
      ?>
      <input type="radio" name="<?php echo esc_html( $name ); ?>" value="<?php echo esc_html( $value ); ?>" checked="checked" class="radio" />
      <?php
    } else {
      ?>
      <input type="radio" name="<?php echo esc_html( $name ); ?>" value="<?php echo esc_html( $value ); ?>" class="radio" />
      <?php
    }
    echo esc_html( $text );
    ?>
    </label>
    </p>
    <?php
  } // end function

  // tstm_print_checkbox( $column_name, $checked, 'visibles[]', '' );

  // print a checkbox
  // if the value is the current_value, the checkbox is checked
  function tstm_print_checkbox( $value, $checked, $name, $text ) {
    if ( $checked ) {
      ?>
      <input type="checkbox" name="<?php echo esc_html( $name ); ?>" value="<?php echo esc_html( $value ); ?>" checked="checked" />
      <?php
    } else {
      ?>
      <input type="checkbox" name="<?php echo esc_html( $name ); ?>" value="<?php echo esc_html( $value ); ?>" />
      <?php
    }
    echo esc_html( $text ).PHP_EOL;
  } // end function

  function tstm_input( $column_name, $type_code, $value ) {
    // $readonly = '' or 'readonly'
    $encoded_name = urlencode( $column_name );
    switch ( $type_code ) {
      // numeric
      case 'int':
      case 'real':
      case '3':
      case '8':
        return '<input type="number" name="'.$encoded_name.'" value="'.$value.'">';
      // date
      case 'date':
      case '10':
        return '<input type="date" name="'.$encoded_name.'" value="'.$value.'">';
      case 'time':
      case '11':
        return '<input type="time" name="'.$encoded_name.'" step="1" value="'.$value.'">';
      case 'datetime':
      case 'timestamp':
      case '7':
      case '12':
        return '<input type="text" name="'.$encoded_name.'" value="'.$value.'">';
      // long text
      case 'blob':
      case '252':
        return '<textarea name="'.$encoded_name.'">'.$value.'</textarea>';
      default:
        // default: text
        return '<input type="text" name="'.$encoded_name.'" value="'.htmlspecialchars( $value, ENT_QUOTES ).'">';
    }
  } // end function
      
  function tstm_get_allowed_html() {
    $allowed_html = array(
      'input' => array(
        'type' => array(),
        'name' => array(),
        'step' => array(),
        'value' => array(),
        'class' => array()
      ),
      'textarea' => array(
        'name' => array(),
        'class' => array()
      )
    );
    return $allowed_html;
  } // end function
      
  function tstm_get_type_name( $type_code ) {
    switch( $type_code ) {
      case 0:
        $type_name = __( 'decimal', 'simple-table-manager' );
        break;
      case 1:
        $type_name = __( 'tinyint / tiny', 'simple-table-manager' );
        break;
      case 2:
        $type_name = __( 'smallint / short', 'simple-table-manager' );
        break;
      case 3:
        $type_name = __( 'int / long', 'simple-table-manager' );
        break;
      case 4:
        $type_name = __( 'float', 'simple-table-manager' );
        break;
      case 5:
        $type_name = __( 'double', 'simple-table-manager' );
        break;
      case 6:
        $type_name = __( 'null', 'simple-table-manager' );
        break;
      case 7:
        $type_name = __( 'timestamp', 'simple-table-manager' );
        break;
      case 8:
        $type_name = __( 'bigint', 'simple-table-manager' );
        break;
      case 9:
        $type_name = __( 'mediumint / int24', 'simple-table-manager' );
        break;
      case 10:
        $type_name = __( 'date', 'simple-table-manager' );
        break;
      case 11:
        $type_name = __( 'time', 'simple-table-manager' );
        break;
       case 12:
        $type_name = __( 'datetime', 'simple-table-manager' );
        break;
      case 13:
        $type_name = __( 'year', 'simple-table-manager' );
        break;
      case 14:
        $type_name = __( 'newdate', 'simple-table-manager' );
        break;
      case 16:
        $type_name = __( 'bit', 'simple-table-manager' );
        break;
      case 246:
        $type_name = __( 'decimal', 'simple-table-manager' );
        break;
      case 247:
        $type_name = __( 'enum', 'simple-table-manager' );
        break;
      case 248:
        $type_name = __( 'set', 'simple-table-manager' );
        break;
      case 249:
        $type_name = __( 'tiny_blob', 'simple-table-manager' );
        break;
      case 250:
        $type_name = __( 'medium_blob', 'simple-table-manager' );
        break;
      case 251:
        $type_name = __( 'long_blob', 'simple-table-manager' );
        break;
      case 252:
        $type_name = __( 'blob', 'simple-table-manager' );
        break;
     case 253:
        $type_name = __( 'varchar', 'simple-table-manager' );
        break;
      case 254:
        $type_name = __( 'char / string / boolean', 'simple-table-manager' );
        break;
      case 255;
        $type_name = __( 'geometry', 'simple-table-manager' );
        break;
      default:
        $type_name = $type;
    }
    return $type_name;
  } // end function
      
  function tstm_validate_rows_per_page( $rows_per_page ) {
    $rows_per_page = absint( $rows_per_page );
    if( $rows_per_page < 1 || $rows_per_page > 1000 ) {
      $rows_per_page = 10;
    }
    return $rows_per_page;
  } // end function
        
  function tstm_validate_capability( $capability ) {
    $capability = sanitize_text_field( $capability );
    if( ! in_array( $capability, array( 'edit_posts', 'manage_options' ) ) ) {
      $capability = 'edit_posts';
    }
    return $capability;
  } // end function