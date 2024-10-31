<?php
/**
 * @package GooglePhotosPicker
 * @version 1.0
 */
/*
Plugin Name: Google Photos Picker
Plugin URI: http://wordpress.org/plugins/photos-picker/
Description: This is a plugin that enables you choose Google Photos and inserts them in img tags in text mode post editor.  This plugin requires you create a project in Google Developers Console to have your own API credentials.
Author: Wongoo Lee
Version: 1.0
*/

function google_photos_picker_ready()
{
  $browserKey = get_option( "google_photos_picker_setting_browser_key", "" );
  $clientId = get_option( "google_photos_picker_setting_client_id", "" );
  return $browserKey != "" && $clientId != "";
}

function google_photos_picker_button_script() 
{
  if(wp_script_is("quicktags") && google_photos_picker_ready())
  {
    ?>
    <script type="text/javascript">
      (function() {
        QTags.addButton("google_photos_picker", "google photos picker", cb);

        function cb() {
          openGooglePhotosPicker(picker_cb);    
        }

        function picker_cb(imgTag, data) {
          if (data[google.picker.Response.ACTION] != google.picker.Action.PICKED) {
            return;
          }
          var docs = data[google.picker.Response.DOCUMENTS];
          var content = '\n\n';
          docs.map(function(doc) {
            var url = doc[google.picker.Document.THUMBNAILS][0]['url'].replace('/s32-c', '/s2048');
            var img = '<img src="' + url + '" alt="' + doc['name']  + '" />';
            if (imgTag == '1') {
              img = '<a href="' + url + '">' + img + '</a>';
            }
            if (imgTag == '2') {
              img = '![' + doc['name'] + '](' + url  + ')';
            }
            content += img + '\n\n';
          });

          QTags.insertContent(content);
        }
      }());
    </script>
    <?php
  }
}
add_action("admin_print_footer_scripts", "google_photos_picker_button_script");

function google_photos_picker_enqueue($hook)
{
  if (("post.php" != $hook && "post-new.php" != $hook) || !google_photos_picker_ready()) {
    return;
  }

  $data = array(
    "browserKey" => get_option("google_photos_picker_setting_browser_key", ""),
    "clientId" => get_option("google_photos_picker_setting_client_id", ""),
    "imgTag" => get_option("google_photos_picker_setting_img_tag_type", "")
  );

  wp_enqueue_script("google_photos_picker_script", plugin_dir_url( __FILE__ ) . "googlephotospicker.js");
  wp_localize_script("google_photos_picker_script", "googlePhotosPickerVars", $data);
}
add_action("admin_enqueue_scripts", "google_photos_picker_enqueue");








/*
 * Add all your sections, fields and settings during admin_init
 */
 
function google_photos_picker_settings_api_init() {
  add_settings_section(
    "google_photos_picker_setting_section",  // $id
    "Google Photos Picker plugin settings",  // $title
    "google_photos_picker_setting_section_callback_function",  // $callback
    "media"  // $page
  );
 
  add_settings_field(
    "google_photos_picker_setting_browser_key",  // $id
    "The Browser API key obtained from the Google Developers Console",  // $title
    "google_photos_picker_setting_browser_key_callback_function",  // $callback
    "media",  // $page
    "google_photos_picker_setting_section"  // $section
  );
  register_setting(
    "media",  // $option_group
    "google_photos_picker_setting_browser_key"  // $option_name
  );

  add_settings_field(
    "google_photos_picker_setting_client_id",  // $id
    "The Client ID obtained from the Google Developers Console",  // $title
    "google_photos_picker_setting_client_id_callback_function",  // $callback
    "media",  // $page
    "google_photos_picker_setting_section"  // $section
  );
  register_setting(
    "media",  // $option_group
    "google_photos_picker_setting_client_id"  // $option_name
  );

  add_settings_field(
    "google_photos_picker_setting_img_tag_type",  // $id
    "Embedded img tag type",  // $title
    "google_photos_picker_setting_img_tag_type_callback_function",  // $callback
    "media",  // $page
    "google_photos_picker_setting_section"  // $section
  );
  register_setting(
    "media",  // $option_group
    "google_photos_picker_setting_img_tag_type"  // $option_name
  );
}
 
add_action("admin_init", "google_photos_picker_settings_api_init");
 
 
function google_photos_picker_setting_section_callback_function() {
  echo "<p>Go to Google Developers Console and create a browser key and OAuth 2.0 client id, and set them here.</p>";
}
 
function google_photos_picker_setting_browser_key_callback_function() {
  $setting = esc_attr( get_option( "google_photos_picker_setting_browser_key" ) );
  echo "<input type='text' name='google_photos_picker_setting_browser_key' value='$setting' />";
}

function google_photos_picker_setting_client_id_callback_function() {
  $setting = esc_attr( get_option( "google_photos_picker_setting_client_id" ) );
  echo "<input type='text' name='google_photos_picker_setting_client_id' value='$setting' />";
}

function google_photos_picker_setting_img_tag_type_callback_function() {
  ?>
    <input type="radio" name="google_photos_picker_setting_img_tag_type" value="0" <?php checked(0, get_option('google_photos_picker_setting_img_tag_type'), true); ?>>Just img tag - &lt;img src="img_url" alt="file_name"&gt;
    <br>
    <input type="radio" name="google_photos_picker_setting_img_tag_type" value="1" <?php checked(1, get_option('google_photos_picker_setting_img_tag_type'), true); ?>>Img tag in anchor tag - &lt;a href="img_url"&gt;&lt;img src="img_url" alt="file_name"&gt;&lt/a&gt;
    <br>
    <input type="radio" name="google_photos_picker_setting_img_tag_type" value="2" <?php checked(2, get_option('google_photos_picker_setting_img_tag_type'), true); ?>>Markdown - ![file_name](img_url)
  <?php
}

?>
