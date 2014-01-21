<?php

/**
 * BreezeAdmin
 *
 * The purpose of this file is, a procedural set of functions that handles the admin pages for Breeze
 * @package Breeze mod
 * @version 1.0
 * @author Jessica Gonz�lez <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2014 Jessica Gonz�lez
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

/*
 * Version: MPL 1.1
 *
 * The contents of this file are subject to the Mozilla Public License Version
 * 1.1 (the "License"); you may not use this file except in compliance with
 * the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 *
 * The Original Code is http://missallsunday.com code.
 *
 * The Initial Developer of the Original Code is
 * Jessica Gonz�lez.
 * Portions created by the Initial Developer are Copyright (c) 2012
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 *
 */

if (!defined('SMF'))
	die('No direct access...');

function Breeze_Admin_Index()
{
		global $txt, $scripturl, $context, $sourcedir, $settings;

		require_once($sourcedir . '/ManageSettings.php');
		loadLanguage('BreezeAdmin');
		$context['page_title'] = $txt['Breeze_page_panel'];

		$subActions = array(
			'general' => 'Breeze_Admin_Main',
			'settings' => 'Breeze_Admin_Settings',
			'permissions' => 'Breeze_Admin_Permissions',
			'style' => 'Breeze_Admin_Style',
			'donate' => 'Breeze_Admin_Donate',
		);

		loadGeneralSettingParameters($subActions, 'general');

		$context[$context['admin_menu_name']]['tab_data'] = array(
			'tabs' => array(
				'general' => array(),
				'settings' => array(),
				'permissions' => array(),
				'style' => array(),
				'donate' => array(),
			),
		);

		// Admin bits
		$context['html_headers'] .= '
<script type="text/javascript">!window.jQuery && document.write(unescape(\'%3Cscript src="http://code.jquery.com/jquery-1.9.1.min.js"%3E%3C/script%3E\'))</script>
<script src="'. $settings['default_theme_url'] .'/js/jquery.zrssfeed.js" type="text/javascript"></script>
<script type="text/javascript">
var breeze_feed_error_message = '. JavaScriptEscape($txt['Breeze_feed_error_message']) .';

$(document).ready(function (){
	$(\'#breezelive\').rssfeed(\''. Breeze::$supportSite .'\',
	{
		limit: 5,
		header: false,
		date: true,
		linktarget: \'_blank\',
		errormsg: breeze_feed_error_message
   });
});
 </script>';

		// Call the sub-action
		$subActions[$_REQUEST['sa']]();
}

function Breeze_Admin_Main()
{
	global $scripturl, $context, $breezeController;

	loadtemplate('BreezeAdmin');

	if (empty($breezeController))
		$breezeController = new BreezeController();

	$tools = $breezeController->get('tools');

	// Get the version
	$context['Breeze']['version'] = Breeze::$version;

	// The support site RSS feed
	$context['Breeze']['support'] = Breeze::$supportSite;

	// Set all the page stuff
	$context['page_title'] = $tools->text('_main');
	$context['sub_template'] = 'admin_home';
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $tools->text('_admin_panel'),
		'description' => $tools->text('admin_welcome'),
	);

	// Get the credits
	$context['Breeze']['credits'] = Breeze::credits();
}

function Breeze_Admin_Settings()
{
	global $scripturl, $context, $sourcedir, $breezeController;

	loadtemplate('Admin');

	if (empty($breezeController))
		$breezeController = new BreezeController();

	$tools = $breezeController->get('tools');

	// Load stuff
	$data = Breeze::data('request');
	$context['sub_template'] = 'show_settings';
	$context['page_title'] = $tools->text('_main');
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => Breeze::$name .' - '. $tools->text('settings'),
		'description' => $tools->text('settings_desc'),
	);

	require_once($sourcedir . '/ManageServer.php');

	$config_vars = array(
		array('title', Breeze::$txtpattern .'_settings'),
		array('check', Breeze::$txtpattern .'_enable', 'subtext' => $tools->text('enable_sub')),
		array('check', Breeze::$txtpattern .'_force_enable', 'subtext' => $tools->text('force_enable_sub')),
		array('check', Breeze::$txtpattern .'_enable_limit', 'subtext' => $tools->text('admin_enable_limit_sub')),
		array('text', Breeze::$txtpattern .'allowedActions', 'size' => 56, 'subtext' => $tools->text('allowedActions_sub')),
		array('int', Breeze::$txtpattern .'admin_mention_limit', 'size' => 3, 'subtext' => $tools->text('admin_mention_limit_sub')),
	);

	$context['post_url'] = $scripturl . '?action=admin;area=breezeadmin;sa=settings;save';

	// Saving?
	if ($data->validate('save') == true)
	{
		checkSession();
		saveDBSettings($config_vars);
		redirectexit('action=admin;area=breezeadmin;sa=settings');
	}

	prepareDBSettingContext($config_vars);
}

function Breeze_Admin_Permissions()
{
	global $scripturl, $context, $sourcedir, $breezeController, $txt;

	loadtemplate('Admin');

	if (empty($breezeController))
		$breezeController = new BreezeController();

	$tools = $breezeController->get('tools');

	// Load stuff
	$data = Breeze::data('request');
	$context['sub_template'] = 'show_settings';
	$context['page_title'] = $tools->text('_main');
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => Breeze::$name .' - '. $tools->text('_permissions'),
		'description' => $tools->text('_permissions_desc'),
	);

	require_once($sourcedir . '/ManageServer.php');

	$config_vars = array(
		array('title', Breeze::$txtpattern .'_permissions'),
	);

	foreach (Breeze::$permissions as $p)
		$config_vars[] = array('permissions', 'breeze_'. $p, 0, $txt['permissionname_breeze_'. $p]);

	$context['post_url'] = $scripturl . '?action=admin;area=breezeadmin;sa=permissions;save';

	// Saving?
	if ($data->validate('save') == true)
	{
		checkSession();
		saveDBSettings($config_vars);
		redirectexit('action=admin;area=breezeadmin;sa=permissions');
	}

	prepareDBSettingContext($config_vars);
}

function Breeze_Admin_Style()
{
	global $scripturl, $context, $sourcedir, $breezeController, $txt;

	loadtemplate('Admin');

	// Load stuff
	$tools = $breezeController->get('tools');
	$data = Breeze::data('request');
	$context['sub_template'] = 'show_settings';
	$context['page_title'] = $tools->text('_sub_style');
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => Breeze::$name .' - '. $tools->text('_sub_style'),
		'description' => $tools->text('_sub_style_desc'),
	);

	require_once($sourcedir . '/ManageServer.php');

	$config_vars = array(
		array('title', Breeze::$txtpattern .'_sub_style'),
		array('int', Breeze::$txtpattern .'admin_posts_for_mention', 'size' => 3, 'subtext' => $tools->text('admin_posts_for_mention_sub')),
	);

	$context['post_url'] = $scripturl . '?action=admin;area=breezeadmin;sa=style;save';

	// Saving?
	if ($data->validate('save') == true)
	{
		checkSession();
		saveDBSettings($config_vars);
		redirectexit('action=admin;area=breezeadmin;sa=style');
	}

	prepareDBSettingContext($config_vars);
}

// Pay no attention to the girl behind the curtain
function Breeze_Admin_Donate()
{
	global $context, $scripturl, $breezeController;

	loadtemplate('BreezeAdmin');

	// Headers
	$tools = $breezeController->get('tools');

	// Page stuff
	$context['page_title'] = Breeze::$name .' - '. $tools->text('_donate');
	$context['sub_template'] = 'admin_donate';
	$context['Breeze']['donate'] = $tools->text('donate');
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $context['page_title'],
		'description' => $tools->text('_donate_desc'),
	);
}
