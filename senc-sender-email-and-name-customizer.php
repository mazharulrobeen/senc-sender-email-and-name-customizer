<?php
/*
 * Plugin Name: SENC Sender Email and Name Customizer
 * Description: WordPress Email's Default Email Address and Sender Name Customizer
 * Version:     0.1
 * Author:      Robeen
 * Author URI:  https://profiles.wordpress.org/mazharulrobeen/
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: senc-sender-email-and-name-customizer
 * Domain Path: /languages
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) exit;

/**
 * Load plugin textdomain.
 */

function senc_campaign_load_textdomain()
{
	load_plugin_textdomain('senc_campaign', false, basename(dirname(__FILE__)) . '/languages');
}

add_action('init', 'senc_campaign_load_textdomain');

function senc_campaign_sender_register()
{
	// Define sanitization args FIRST as variables
	$name_args = ['type' => 'string', 'sanitize_callback' => 'senc_sanitize_sender_name'];
	$email_args = ['type' => 'string', 'sanitize_callback' => 'senc_sanitize_sender_email'];

	add_settings_section(
		'senc_campaign_wp_default_se_customizer_section',
		__('WordPress Email\'s Default Sender Name and Email Address Customizer: SENC', 'senc-sender-email-and-name-customizer'),
		'senc_campaign_wp_default_se_customizer_text',
		'senc_campaign_wp_default_se_mail_sender' // This is your settings PAGE slug
	);

	// Sender Name Field
	add_settings_field(
		'senc_campaign_wp_default_mail_sender_name_id',
		__('Email Sender Name', 'senc-sender-email-and-name-customizer'),
		'senc_campaign_wp_default_se_customizer_function',
		'senc_campaign_wp_default_se_mail_sender', // Settings PAGE slug
		'senc_campaign_wp_default_se_customizer_section' // Section ID
	);

	register_setting(
		'senc_campaign_wp_default_se_mail_sender', // Should match settings PAGE slug
		'senc_campaign_wp_default_mail_sender_name_id',
		$name_args // Pre-defined args
	);

	// Sender Email Field
	add_settings_field(
		'senc_campaign_wp_default_mail_sender_email_id',
		__('Sender Email Address', 'senc-sender-email-and-name-customizer'),
		'senc_campaign_wp_default_sender_email',
		'senc_campaign_wp_default_se_mail_sender', // Settings PAGE slug
		'senc_campaign_wp_default_se_customizer_section' // Section ID
	);

	register_setting(
		'senc_campaign_wp_default_se_mail_sender', // Settings PAGE slug
		'senc_campaign_wp_default_mail_sender_email_id',
		$email_args // Pre-defined args
	);
}
add_action('admin_init', 'senc_campaign_sender_register');

// Sanitization functions
function senc_sanitize_sender_name($input)
{
	// Sanitize text input
	return sanitize_text_field($input);
}

function senc_sanitize_sender_email($input)
{
	// Sanitize email and ensure it's valid
	$sanitized_email = sanitize_email($input);

	// Additional validation
	if (!is_email($sanitized_email)) {
		add_settings_error(
			'senc_campaign_wp_default_mail_sender_email_id',
			'invalid_email',
			__('The provided email address is not valid.', 'senc-sender-email-and-name-customizer')
		);
		// Return existing value if validation fails
		return get_option('senc_campaign_wp_default_mail_sender_email_id');
	}

	return $sanitized_email;
}



function senc_campaign_wp_default_se_customizer_function()
{

	printf('<input name="senc_campaign_wp_default_mail_sender_name_id" type="text" class="regular-text" value="%s" placeholder="WordPress"/>', esc_html(get_option('senc_campaign_wp_default_mail_sender_name_id')));
}
function senc_campaign_wp_default_sender_email()
{
	printf('<input name="senc_campaign_wp_default_mail_sender_email_id" type="email" class="regular-text" value="%s" placeholder="wordpress@yourdomain.com"/>', esc_html(get_option('senc_campaign_wp_default_mail_sender_email_id')));
}

function senc_campaign_wp_default_se_customizer_text()
{

	printf('%s By default, it uses "WordPress" as the sender\'s name and a non-existent email address (wordpress@yourdomain.com) as the sender email. <br>

	To configure it according to your own preference you will need to enter the name and email address you want to be used for outgoing WordPress emails. Don’t forget to click on the save changes button to store your settings. <br>
	
	That’s all, your WordPress notification emails will now show the name and email address you entered in plugin settings. <hr> %s', '<p>', '</p>');
}

function senc_campaign_admin_menu()
{
	add_menu_page(__('SENC Campaign Options', 'senc-sender-email-and-name-customizer'), __('SENC', 'senc-sender-email-and-name-customizer'), 'manage_options', 'senc_campaign', 'senc_campaign_wp_default_mail_sender_output', 'dashicons-yes-alt');
}
add_action('admin_menu', 'senc_campaign_admin_menu');

function senc_campaign_wp_default_mail_sender_output()
{
?>
	<?php settings_errors(); ?>
	<form action="options.php" method="POST">
		<?php do_settings_sections('senc_campaign_wp_default_se_mail_sender'); ?>
		<?php settings_fields('senc_campaign_wp_default_se_customizer_section'); ?>
		<?php submit_button(); ?>
	</form>
<?php }

// Change the default Sender (wordpress@exampleyourdomain.com) email address
add_filter('wp_mail_from', 'senc_campaign_wp_new_form');
add_filter('wp_mail_from_name', 'senc_campaign_wp_new_form_name');

function senc_campaign_wp_new_form($old)
{
	return get_option('senc_campaign_wp_default_mail_sender_email_id');
}
function senc_campaign_wp_new_form_name($old)
{
	return get_option('senc_campaign_wp_default_mail_sender_name_id');
}
