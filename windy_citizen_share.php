<?php
/*
Plugin Name: Windy Citizen Share
Plugin URI: http://www.windycitizen.com/buttons
Description: Place a "Windy Citizen Share" button at the end of your posts, pages, or anywhere at your choice. 
Version: 1.0.2
Author: bflora
Author URI: http://www.windycitizen.com
*/


$options = array(
	'dtom_add_to_full_post' => array(
		'type'			=> 'select',
		'label' 		=> __('Automatically add "Windy Citizen Share" button to your posts? *', 'windycitizenshare'),
		'options'		=> array ('yes', 'no'),
		'default_value'	=> 'yes',
	), 
	'dtom_add_to_page' 		=> array(
		'type'			=> 'select',
		'label' 		=> __('Automatically add "Windy Citizen Share" button to your pages? *', 'windycitizenshare'),
		'options'		=> array ('yes', 'no'),
		'default_value'	=> 'yes',
	),
	'dtom_bgcolor' 			=> array(
		'type'			=> 'text',
		'label'			=> __('Background color around the button: ', 'windycitizenshare'),
		'default_value'	=> '#ffffff',
		'extra'			=> '<div style="display:none" id="colorPickerMap"></div>',
	),
	'dtom_skin' 			=> array(
		'type'			=> 'select',
		'label' 		=> __('Skin of the button: ', 'windycitizenshare'),
		'options'		=> array ('tall', 'wide', 'text'),
		'default_value'	=> 'default',
	),
);

load_plugin_textdomain('windycitizenshare');

/**
 * @desc	Adds the DiggThis button into the content
 */
function dtom_add_button($content)
{
	if (((is_page() && get_option('dtom_add_to_page') != 'no') || (!is_page() && get_option('dtom_add_to_full_post') != 'no')) && !is_feed())
	{
		return $content . dtom_button();
	}		

	return $content;
}

if (get_option('dtom_add_to_full_post') != 'no' || get_option('dtom_add_to_page') != 'no') 
{
	add_filter('the_content', 'dtom_add_button');
}

function dtom()
{
	echo dtom_button();
}


/**
 * @desc	Creates the DiggThis button
 */
function dtom_button()
{
	global $post;

	$diggthisomine = '<div class="dtom"><script src="http://www.windycitizen.com/sites/all/modules/drigg_external/js/windycitizen.js"  type="text/javascript" charset="utf-8"></script><script type="text/javascript" charset="utf-8">diggthisomine.addEntry({ title: "'.str_replace('"', '\"', strip_tags(get_the_title())).'", url: "'.get_permalink($post->ID).'" });</script>';
	
	$config = array(
		'wc_url'		=> get_permalink($post->ID),
		'wc_title'	=> str_replace("'", "\'", get_the_title()),
	);
	
	$skin = get_option('dtom_skin');
	if ($skin != 'default')
	{
		$config['wc_skin'] = $skin;
	}
	
	
	$config_string = '';
	foreach ($config as $key => $value)
	{
		$config_string .= "'$value',";
	}

	$button = 'show_windycitizen_button( 
	' . $config_string . '
	);
	</script></div>';
	
	return $button;
}

/**
 * @desc	Creates the options form
 */
function dtom_options_form() 
{
	$home = get_settings('siteurl') . '/wp-content/plugins/windy-citizen-share/';
	
	echo(
		'<link rel="stylesheet" href="' . $home . 'css/farbtastic.css" type="text/css" media="screen" />' . 
		'<script type="text/javascript" src="' . $home . 'js/farbtastic.js"></script>' . 
		'<script type="text/javascript" src="' . $home . 'js/onload.js"></script>'
	);
	
	print('
			<div class="wrap">
				<h2>'.__('Windy Citizen Share Options', 'windycitizenshare').'</h2>
				<form id="form_windycitizenshare" name="form_windycitizenshare" action="' . get_bloginfo('wpurl') . '/wp-admin/index.php" method="post">
	');
	
	global $options;
	
	foreach ($options as $key => $config)
	{
		$value = get_option($key);
		if (!$value) $value = $config['default_value'];
		switch ($config['type'])
		{
			case 'text':
				printf('
				<p>
					<label for="%s">%s</label><br />
					<input type="text" name="%s" id="%s" value="%s" />
				</p>
				', $key, $config['label'], $key, $key, $value);
				break;
			case 'select':
				$select_options = '';
				foreach ($config['options'] as $o)
				{
					$select_options .= sprintf('
					<option value="%s"%s>%s</option>
					', $o, $o == $value ? 'selected="selected"' : '', $o);
				}
				printf('
				<p>
					<label for="%s">%s</label><br />
					<select name="%s" id="%s">
					%s
					</select>
				</p>
				', $key, $config['label'], $key, $key, $select_options);
				break;
			default;
				break;
		}
		
		if  (isset($config['extra'])) echo $config['extra'];
	}
	
	print('
						<p>'.__('* You will have to manually add this Windy Citizen Share template tag into your theme if these settings are set to "no":', 'windycitizenshare').'</p>
						<code>&lt;?php if (function_exists(\'dtom\')) dtom(); ?&gt;</code>

					<p class="submit">
						<input type="hidden" name="dtom_action" value="update" />
						<input type="submit" name="submit_button" value="'.__('Save Options', 'windycitizenshare').'" />
					</p>
				</form>
			</div>
	');
}

/**
 * @desc	Adds the Options menu item 
 */
function dtom_menu_items() {
	add_options_page(
		__('Windy Citizen Share', 'windycitizenshare'), 
		__('Windy Citizen Share', 'windycitizenshare'), 
		8,
		basename(__FILE__), 
		'dtom_options_form'
	);
}

add_action('admin_menu', 'dtom_menu_items');

/**
 * @desc	Handles the form POST data to save the options
 */
function dtom_request_handler()
{
	if (!isset($_POST['dtom_action'])) return;
	
	global $options;
	
	foreach ($options as $key => $config) 
	{
		if (isset($_POST[$key])) 
		{
			update_option($key, $_POST[$key]);
		}
	}
	
	header('Location: ' . get_bloginfo('wpurl') . '/wp-admin/options-general.php?page=digg_this_o_mine.php&updated=true');
	die();
}

add_action('init', 'dtom_request_handler', 9999);	
