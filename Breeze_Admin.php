<?php

/**
 * @package breeze mod
 * @version 1.0
 * @author Suki <missallsunday@simplemachines.org>
 * @copyright Copyright (c) 2011 Suki
 * @license http://creativecommons.org/licenses/by-nc-sa/3.0/ CC BY-NC-SA 3.0
 */

if (!defined('SMF'))
	die('Hacking attempt...');

	/* We can't call a static method from a string... let's do this the old way instead... */
	function Breeze_Admin_Main()
	{
		global $txt, $scripturl, $context;

		loadLanguage('Breeze');
		loadtemplate('Breeze');
		LoadBreezeMethod(array('Breeze_Subs', 'Breeze_Logs'));

		/* Set all the page stuff */
		$context['page_title'] = $txt['breeze_admin_settings_main'];
		$context['sub_template'] = 'admin_home';
		$context['linktree'][] = array(
			'url' => $scripturl . '?action=admin;area=breezeindex',
			'name' => $txt['breeze_admin_settings_main']
		);

		/* Headers */
		Breeze_Subs::Headers(true);

		/* Tell them if their server is up to the challange*/
		$context['breeze']['versions'] = Breeze_Subs::Check_Versions();

	}

	function Breeze_Admin_Settings()
	{
		global $scripturl, $txt, $context, $sourcedir;

		loadLanguage('Breeze');
		$context['sub_template'] = 'show_settings';

		require_once($sourcedir . '/ManageServer.php');

		$config_vars = array(
				array('check', 'breeze_admin_settings_eneble', 'subtext' => $txt['breeze_admin_settings_eneble_sub']),
				array('select', 'breeze_admin_settings_menuposition', array('home' => $txt['home'], 'help' => $txt['help'], 'profile' => $txt['profile']), 'subtext' => $txt['breeze_admin_settings_menuposition_sub']),
				array('check', 'breeze_admin_settings_enablegeneralwall', 'subtext' => $txt['breeze_admin_settings_enablegeneralwall_sub']),
		);

		$context['post_url'] = $scripturl . '?action=admin;area=breezesettings;save';

		// Saving?
		if (isset($_GET['save']))
		{
			checkSession();
			saveDBSettings($config_vars);
			redirectexit('action=admin;area=breezesettings');
		}

		prepareDBSettingContext($config_vars);
	}

	/* Pay no attention to the girl behind the curtain */
	function Breeze_Admin_Donate()
	{
		global $txt, $context;

		loadLanguage('BreezeAdmin');
		loadtemplate('BreezeAdmin');

		/* Page stuff */
		$context['page_title'] = $txt['breeze_admin_settings_donate'];
		$context['sub_template'] = 'admin_donate';
		$context['linktree'][] = array(
			'url' => $scripturl . '?action=admin;area=breezedonate',
			'name' => $txt['breeze_admin_settings_donate']
		);
	}

?>
