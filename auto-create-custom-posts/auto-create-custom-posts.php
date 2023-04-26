/*
Plugin Name: Auto Create Custom Posts
Plugin URI: https://www.example.com/
Description: A plugin that allows users to automatically publish posts of one custom post type when a post from a separate custom post type is created
Version: 1.0.069 hehehe
Author: Kevin Garofalo
Author URI: https://dibraco.com
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: auto-create-custom-posts
*/

define( 'ACP_ACF_EXISTS', function_exists( 'acf_add_local_field_group' ) );
define( 'ACP_REGISTER_FIELD_GROUP_EXISTS', function_exists( 'register_field_group' ) );

// Check if ACF is installed and active
if ( ! ACP_ACF_EXISTS ) {
    add_action( 'admin_notices', 'dibraco_acf_missing_notice' );
    return;
}

// Check if register_field_group function exists
if ( ! ACP_REGISTER_FIELD_GROUP_EXISTS ) {
    add_action( 'admin_notices', 'dibraco_register_field_group_missing_notice' );
    return;
}

// Display notice for missing ACF plugin
function dibraco_acf_missing_notice() {
    echo '<div class="notice notice-error"><p>The Auto Create Custom Posts plugin requires Advanced Custom Fields (ACF) or ACF Pro to be installed and activated. Please install and activate ACF to use this plugin.</p></div>';
}

// Display notice for missing register_field_group function
function dibraco_register_field_group_missing_notice() {
    echo '<div class="notice notice-error"><p>The Auto Create Custom Posts plugin requires a legacy version of Advanced Custom Fields (ACF) to be installed and activated. Please install and activate a legacy version of ACF to use this plugin.</p></div>';
}

function auto_create_dibraco_posts_options_page() {
    add_options_page(
        'Auto Create Dibraco Posts',
        'Auto Create Dibraco Posts',
        'manage_options',
        'auto-create-dibraco-posts',
        'auto_create_dibraco_posts_render_options_page'
    );
}
add_action('admin_menu', 'auto_create_dibraco_posts_options_page');



function auto_create_dibraco_posts_render_options_page() {
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('auto_create_dibraco_posts_options_group');
            do_settings_sections('auto-create-dibraco-posts');
            submit_button('Save Settings');
            ?>
        </form>
    </div>
    <?php
}

function auto_create_dibraco_posts_register_settings() {
    register_setting(
        'auto_create_dibraco_posts_options_group',
        'auto_create_dibraco_posts_settings',
        'auto_create_dibraco_posts_sanitize_settings'
    );

    add_settings_section(
        'auto_create_dibraco_posts_main_section',
        'Main Settings',
        'auto_create_dibraco_posts_main_section_callback',
        'auto-create-dibraco-posts'
    );

    add_settings_field(
        'auto_create_dibraco_posts_post_types',
        'Post Types',
        'auto_create_dibraco_posts_post_types_callback',
        'auto-create-dibraco-posts',
        'auto_create_dibraco_posts_main_section'
    );
}
add_action('admin_init', 'auto_create_dibraco_posts_register_settings');

function auto_create_dibraco_posts_main_section_callback() {
    echo '<p>Select the post types you want to use:</p>';
}

function auto_create_dibraco_posts_post_types_callback() {
    $args = array(
        'public' => true,
    );
    $post_types = get_post_types($args, 'objects');

    $settings = get_option('auto_create_dibraco_posts_settings');
    $selected_post_types = isset($settings['post_types']) ? $settings['post_types'] : array();

    echo '<select name="auto_create_dibraco_posts_settings[post_types][]" multiple="multiple">';
    foreach ($post_types as $post_type) {
        $selected = in_array($post_type->name, $selected_post_types) ? 'selected="selected"' : '';
        echo '<option value="' . esc_attr($post_type->name) . '"' . $selected . '>' . esc_html($post_type->label) . '</option>';
    }
    echo '</select>';
}

function auto_create_dibraco_posts_admin_menu() {
    add_options_page(
        'Auto Create Dibraco Posts',
        'Auto Create Dibraco Posts',
        'manage_options',
        'auto-create-dibraco-posts',
        'auto_create_dibraco_posts_options_page'
    );
}
add_action('admin_menu', 'auto_create_dibraco_posts_admin_menu');

function auto_create_dibraco_posts_settings_init() {
    register_setting(
        'auto_create_dibraco_posts_settings_group',
        'auto_create_dibraco_posts_settings'
    );

    add_settings_section(
        'auto_create_dibraco_posts_main_section',
        'Main Settings',
        'auto_create_dibraco_posts_main_section_callback',
        'auto-create-dibraco-posts'
    );

    add_settings_field(
        'auto_create_dibraco_posts_post_types',
        'Post Types',
        'auto_create_dibraco_posts_post_types_callback',
        'auto-create-dibraco-posts',
        'auto_create_dibraco_posts_main_section'
    );
}
add_action('admin_init', 'auto_create_dibraco_posts_settings_init');

function auto_create_dibraco_posts_main_section_callback() {
    echo '<p>Select the post types that you want to use for the auto-creation feature:</p>';
}

function auto_create_dibraco_posts_post_types_callback() {
    $options = get_option('auto_create_dibraco_posts_settings');
    $post_types = get_post_types();

    echo '<select name="auto_create_dibraco_posts_settings[post_types][]" multiple>';
    foreach ($post_types as $post_type) {
        if ($post_type != 'attachment') {
            $selected = '';
            if (in_array($post_type, $options['post_types'])) {
                $selected = 'selected';
            }
            echo '<option value="' . $post_type . '" ' . $selected . '>' . $post_type . '</option>';
        }
    }
    echo '</select>';
}

function dibraco_create_cpt_submenu() {
  add_submenu_page(
    'edit.php?post_type=dibraco_main_type',
    'Auto Create Custom Posts Settings',
    'Settings',
    'manage_options',
    'dibraco-create-cpt-settings',
    'dibraco_create_cpt_settings_page'
  );
}
add_action('admin_menu', 'dibraco_create_cpt_submenu');

function dibraco_create_cpt_settings_page() {
  ?>
  <div class="wrap">
    <h1>Auto Create Custom Posts Settings</h1>

    <form method="post" action="">
      <label for="dibraco_existing_taxonomy">Choose an existing taxonomy:</label>
      <select id="dibraco_existing_taxonomy" name="dibraco_existing_taxonomy">
        <option value="">-- Choose an existing taxonomy --</option>
        <?php
          $taxonomies = get_taxonomies();
          foreach ($taxonomies as $taxonomy) {
            echo '<option value="' . $taxonomy . '">' . $taxonomy . '</option>';
          }
        ?>
      </select>

      <p>OR</p>

      <label for="dibraco_new_taxonomy">Create a new taxonomy:</label>
      <input id="dibraco_new_taxonomy" name="dibraco_new_taxonomy" type="text">

      <input type="submit" value="Save Settings">
    </form>
  </div>
  <?php
}

function dibraco_save_cpt_settings() {
  if (isset($_POST['dibraco_existing_taxonomy'])) {
    $taxonomy = $_POST['dibraco_existing_taxonomy'];
  } elseif (isset($_POST['dibraco_new_taxonomy'])) {
    $taxonomy = $_POST['dibraco_new_taxonomy'];
  }

  if (!empty($taxonomy)) {
    update_option('dibraco_cpt_taxonomy', $taxonomy);
  }
}
add_action('admin_post_dibraco_create_cpt_settings', 'dibraco_save_cpt_settings');

function dibraco_create_cpt_taxonomy() {
    $labels = array(
        'name' => _x( 'Creation Page Categories', 'taxonomy general name' ),
        'singular_name' => _x( 'Creation Page Category', 'taxonomy singular name' ),
        'search_items' => __( 'Search Creation Page Categories' ),
        'all_items' => __( 'All Creation Page Categories' ),
        'parent_item' => __( 'Parent Creation Page Category' ),
        'parent_item_colon' => __( 'Parent Creation Page Category:' ),
        'edit_item' => __( 'Edit Creation Page Category' ),
        'update_item' => __( 'Update Creation Page Category' ),
        'add_new_item' => __( 'Add New Creation Page Category' ),
        'new_item_name' => __( 'New Creation Page Category Name' ),
        'menu_name' => __( 'Creation Page Categories' ),
    );
 
    $args = array(
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array( 'slug' => 'creation_page_taxonomy' ),
    );
 
    register_taxonomy( 'creation_page_taxonomy', 'creator_post_type', $args );
}
add_action( 'init', 'dibraco_create_cpt_taxonomy', 0 );


function dibraco_create_acf_fields() {

    // Define the field group
    $field_group = array(
        'key' => 'group_614937c7d294e',
        'title' => 'Creator Post Type Details',
        'fields' => array(
            array(
                'key' => 'field_614937d16f70d',
                'label' => 'Name',
                'name' => 'name',
                'type' => 'text',
            ),
            array(
                'key' => 'field_6149380b6f70f',
                'label' => 'Address',
                'name' => 'address',
                'type' => 'group',
                'sub_fields' => array(
                    array(
                        'key' => 'field_6149383b6f710',
                        'label' => 'Street Address',
                        'name' => 'street_address',
                        'type' => 'text',
                    ),
                    array(
                        'key' => 'field_614938566f711',
                        'label' => 'Street Address 2',
                        'name' => 'street_address_2',
                        'type' => 'text',
                    ),
                    array(
                        'key' => 'field_6149386c6f712',
                        'label' => 'City',
                        'name' => 'city',
                        'type' => 'text',
                    ),
                    array(
                        'key' => 'field_614938896f713',
                        'label' => 'State',
                        'name' => 'state',
                        'type' => 'text',
                    ),
                    array(
                        'key' => 'field_6149389d6f714',
                        'label' => 'Zip Code',
                        'name' => 'zip_code',
                        'type' => 'text',
                    ),
                ),
                'wrapper' => array(
                    'width' => '50%',
                    'class' => '',
                    'id' => '',
                ),
            ),
            array(
                'key' => 'field_614939696f715',
                'label' => 'Phone Number',
                'name' => 'telephone',
                'type' => 'text',
            ),
            array(
                'key' => 'field_614939836f716',
                'label' => 'Email Address',
                'name' => 'email',
                'type' => 'text',
            ),
            array(
                'key' => 'field_6149399c6f717',
                'label' => 'Latitude',
                'name' => 'lat',
                'type' => 'text',
            ),
            array(
                'key' => 'field_614939b16f718',
                'label' => 'Longitude',
                'name' => 'longi',
                'type' => 'text',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'creator_post_type',
                ),
            ),
        ),
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'seamless',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
    );

    // Add the field group
    acf_add_local_field_group( $field_group );
}
add_action( 'acf/init', 'dibraco_create_acf_fields' );


