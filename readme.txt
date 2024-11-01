=== Simple Table Manager ===
Contributors: ryo0inoue, lorro
Tags: database, table, mysql, crud, export
Requires at least: 5.6
Tested up to: 6.4
Requires PHP: 7.3
Stable tag: 1.6.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Enables editing table records and exporting them to CSV files through a minimal database interface from your dashboard.

== Description ==

Simple Table Manager enables editing table records and exporting them to CSV files through a minimal database interface from your dashboard.

* Simply CRUD table contents on your wp-admin screen
* Search and sort table records
* No knowledge of MySQL or PHP required
* Export table records to a CSV file
* Does not allow users to change the structure of the table

Unlike 'full featured' database management plugins, it does not allow users to alter the structure of the table but requires no knowledge on MySQL or PHP.

Similar to CakePHP's scaffold feature, Simple Table Manager is an auxiliary tool suited for the initial development phase of a website. It is also ideal when you want to ask someone else with no database expertise to keep track of table records on your website. This was the motivation for developing this plugin.

== Installation ==

1. Via the Dashboard > Plugins > Add plugin menu page.
2. Or upload the entire 'simple-table-manager' folder to the '/wp-content/plugins/' directory.
3. Activate the plugin through the 'Plugins' menu in WordPress.
4. On the plugin's 'Settings' page, select the table you want to manage.

Filter call examples for developer use:

// change a value

`add_filter( 'tstm_list_field', 'custom_list_field_value', 10, 4 );
function custom_list_field_value( $field_value, $current_table, $column_name, $key_value ) {
  if( 'wppb_options' == $current_table ) {
    if( 'option_value' == $column_name ) {
      if( 7 == $key_value ) {
        if( $field_value ) {
          $field_value = "true";
        } else {
          $field_value = "false";
        }
      }
    }
  }
  return $field_value;
}`
 
// change the default text input for radios  

`add_filter( 'tstm_add_field_html', 'custom_field_html', 10, 4 );
add_filter( 'tstm_edit_field_html', 'custom_field_html', 10, 4 );
function custom_field_html( $field_html, $current_table, $column_name, $field_value, $key_value = true  ) {
  if( 'wppb_options' == $current_table ) {
    if( 'option_value' == $column_name ) {
      $column_name = urlencode( $column_name );
      if( $field_value ) {
        $field_html = '<input type="radio" name="'.$column_name.'" value="1" checked> Yes<br>';
        $field_html .= '<input type="radio" name="'.$column_name.'" value="0"> No';
      } else {
        $field_html = '<input type="radio" name="'.$column_name.'" value="1"> Yes<br>';
        $field_html .= '<input type="radio" name="'.$column_name.'" value="0" checked> No';      
      }
    }
  }
  return $field_html;
}`

// change the default text input for a checkbox

`add_filter( 'tstm_add_field_html', 'custom_field_html', 10, 5 );
add_filter( 'tstm_edit_field_html', 'custom_field_html', 10, 5 );
function custom_field_html( $field_html, $current_table, $column_name, $field_value, $key_value = true ) {
  if( 'wppb_options' == $current_table ) {
    if( 'option_value' == $column_name ) {
      if( 7 == $key_value ) {
        $checked = $field_value ? 'checked' : '';
        $field_html = '<input type="hidden" name="'.$column_name.'" value="0">'.PHP_EOL;
        $field_html .= '<input type="checkbox" name="'.$column_name.'" value="1" '.$checked.'>'.PHP_EOL;
      }
    }
  }
  return $field_html;
}`

== Frequently asked questions ==

= How can I add a new table or field by using the plugin? =

You can't. Use 'full featured' plugins or phpMyAdmin if you need full access to the database.

== Screenshots ==

1. List records
2. Edit record
3. Add record
4. Settings

== Changelog ==
= 1.6.0 (2023-11-26) =
* Enhanced security and other changes to comply with wordpress.org plugin guidelines.
* Checked to work with WordPress 6.4

= 1.5.6 (2023-07-23) =
* WHERE clause construction refactored
* Checked to work with WordPress 6.2

= Version 1.5.5 (2022-01-20) =
* Function "stm_load_textdomain" renamed to remove conflict with course-editor plugin.

= Version 1.5.4 (2021-10-24) =
* New feature: Option in the settings tab to restrict plugin access to administrators (Defaults to "Authors" and better capabilities)

= Version 1.5.3 (2021-10-19) =
* Tested with WP 5.8
* Translation file updated

= Version 1.5.2 (2021-03-15) =
* Added a setting to pretty print any unserialized arrays on the Edit tab
* Added a filter example ts show how to setup a checkbox input with the plugin
* Tested with WP v5.7 & PHP v8.0.2

= Version 1.5.1 (2021-03-04) =
* Version 1.5.0 upload was corrupted. Re-uploaded.

= Version 1.5.0 (2021-03-04) =
* Changes by: lorro
* New feature - User can select a sub-set of fields to show. Enables wide tables to fit the screen.
* New feature - Filters for List table field display and for Edit and Add field html. See Installation section for use.
* Redesigned interface
* Tested with WordPress 5.6.2
* Updated readme.txt

= Version 1.4 (2020-09-07) =
* Changes by: lorro
* Fixed errors if column names have spaces.
* Date fields have a date picker in the Edit and Add screens.
* Time fields have a time picker in the Edit and Add screens.
* Works if the primary column is not the first column
* Edit and Add screens show data types.
* Minor styling improvements.
* Tested with WordPress 5.5.1
* Updated readme.txt

= Version 1.3 (2019-08-20) =
* Changes by: lorro
* Fix error on activation if database prefix is not "wp_"
* Fix CSV Export.
* Fix $order not found error.
* Make all strings translation-ready.
* Cosmetic changes

= Version 1.2 (2016-01-15) =
* Author: Ryo Inoue
* Enabled handling of non-integer primary keys.

= Version 1.1 (2015-05-02) =
* Author: Ryo Inoue
* Added feature to auto-adjust input text fields according to data type.
* Enabled insert and retrieval of data containing special chars.
* Rearranged files for a loosely MVC structure.
* Fixed a few minor bugs.

= Version 1.0 (2015-03-03) =
* Author: Ryo Inoue
* First release

== Upgrade Notice ==

= 1.6.0 (2023-11-26) =
* Enhanced security to comply with wordpress.org plugin guidelines.
* Checked to work with WordPress 6.4