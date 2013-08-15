<?php

/**
 * BreezeFunctions.template
 *
 * The purpose of this file is to modularize some of the most used blocks of code, the point is to reduce view code and maximize and re-use as much code as possible
 * @package Breeze mod
 * @version 1.0 Beta 3
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2013 Jessica González
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
 * Jessica González.
 * Portions created by the Initial Developer are Copyright (c) 2012
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 *
 */

function breeze_status($data)
 {
	global $context, $txt, $user_info, $scripturl;

	// New ajax status here DO NOT MODIFY THIS UNLESS YOU KNOW WHAT YOU'RE DOING and even if you do, DON'T MODIFY THIS
	echo '
		<div id="breeze_load_image"></div>
		<ul class="breeze_status" id="breeze_display_status">';

	// Status and comments
	if (!empty($data))
		foreach ($data as $status)
		{
			// Yes, this is ugly, deal with it...
			$post_status = $status['poster_id'] == $user_info['id'] ? true : allowedTo('breeze_postStatus');
			$post_comment = $status['poster_id'] == $user_info['id'] ? true : allowedTo('breeze_postComments');
			$delete_status = $status['poster_id'] == $user_info['id'] ? true : allowedTo('breeze_deleteStatus');
			$delete_comments = $status['poster_id'] == $user_info['id'] ? true : allowedTo('breeze_deleteComments');

			echo '
			<li class="windowbg" id ="status_id_', $status['id'] ,'">
				<span class="topslice"><span></span></span>
					<div class="breeze_user_inner">
						<div class="breeze_user_status_avatar">
							',$context['Breeze']['user_info'][$status['poster_id']]['facebox'],'
						</div>
						<div class="breeze_user_status_comment">
							',$status['body'],'
							<div class="breeze_options">
								<span class="time_elapsed">', $status['time'] ,' </span>';

							// Delete status
							if (!empty($delete_status))
								echo '| <a href="', $scripturl , '?action=breezeajax;sa=delete;bid=', $status['id'] ,';type=status;profile_owner=', $status['owner_id'] ,'" id="', $status['id'] ,'" class="breeze_delete_status">', $txt['Breeze_general_delete'] ,'</a>';

							echo '
							</div>
							<hr />
							<div id="comment_flash_', $status['id'] ,'"></div>';
						echo '
								<ul class="breeze_comments_list" id="comment_loadplace_', $status['id'] ,'">';

							// Print out the comments
							if (!empty($status['comments']))
								foreach($status['comments'] as $comment)
								{
									// Yes, the owner of this comment can delete it if they so desire it
									$delete_single_comment = $comment['poster_id'] == $user_info['id'] ? true : allowedTo('breeze_deleteComments');

									echo '
									<li class="windowbg2" id ="comment_id_', $comment['id'] ,'">
										<div class="breeze_user_comment_avatar">
												',$context['Breeze']['user_info'][$comment['poster_id']]['facebox'],'<br />
										</div>
										<div class="breeze_user_comment_comment">
											',$comment['body'],'
											<div class="breeze_options">
												<span class="time_elapsed">', $comment['time'] ,'</span>';

									// Delete comment
									if ($delete_comments == true || $delete_single_comment == true)
										echo '| <a href="', $scripturl , '?action=breezeajax;sa=delete;bid=', $comment['id'] ,';type=comment;profile_owner=', $status['owner_id'] ,'" id="', $comment['id'] ,'" class="breeze_delete_comment">', $txt['Breeze_general_delete'] ,'</a>';

									echo '
											</div>
										</div>
										<div class="clear"></div>
									</li>';
								}

							// Display the new comments
							echo '<li id="breeze_load_image_comment_', $status['id'] ,'" style="margin:auto; text-align:center;"></li>';

							echo '</ul>';

								// Post a new comment
								if (!empty($permissions['post_comment']))
									echo '
								<div>
									<form action="', $scripturl , '?action=breezeajax;sa=postcomment" method="post" name="formID_', $status['id'] ,'" id="formID_', $status['id'] ,'">
										<textarea id="textboxcontent_', $status['id'] ,'" name="content" cols="40" rows="2" rel="atwhoMention"></textarea>
										<input type="hidden" value="',$status['poster_id'],'" name="status_owner_id', $status['id'] ,'" id="status_owner_id', $status['id'] ,'" />
										<input type="hidden" value="',$context['member']['id'],'" name="profile_owner_id', $status['id'] ,'" id="profile_owner_id', $status['id'] ,'" />
										<input type="hidden" value="', $status['id'] ,'" name="status_id" id="status_id" />
										<input type="hidden" value="',$user_info['id'],'" name="poster_comment_id', $status['id'] ,'" id="poster_comment_id', $status['id'] ,'" /><br />
										<input type="hidden" id="', $context['session_var'], '" name="', $context['session_var'], '" value="', $context['session_id'], '" />
										<input type="submit" value="', $txt['post'] ,'" class="comment_submit" id="', $status['id'] ,'" />
									</form>
								</div>';

						echo '
						</div>
						<div class="clear"></div>
					</div>
				<span class="botslice"><span></span></span>
			</li>';
		}

	// End of list
	echo '
		</ul>';
 }

function breeze_profile_owner()
{
	global $context, $txt, $settings, $scripturl;

	// User info, details
	echo '
	<div class="cat_bar">
		<h3 class="catbg">
			<span id="author">
				', $txt['Breeze_tabs_pinfo'], '
		</h3>
	</div>
	<div class="windowbg BreezeBlock">
		<span class="topslice">
		<span> </span>
		</span>
		<div class="content BreezeInfoBlock">';
	echo '
				<div class="username"><h4>', $context['member']['name'] ,'<br/><span class="position">', (!empty($context['member']['group']) ? $context['member']['group'] : $context['member']['post_group']), '</span></h4></div>
				', $context['member']['avatar']['image'], '
				<ul class="reset">';

	// What about if we allow email only via the forum??
	if ($context['member']['show_email'] === 'yes' || $context['member']['show_email'] === 'no_through_forum' || $context['member']['show_email'] === 'yes_permission_override')
		echo '
					<li><a href="', $scripturl, '?action=emailuser;sa=email;uid=', $context['member']['id'], '" title="', $context['member']['show_email'] == 'yes' || $context['member']['show_email'] == 'yes_permission_override' ? $context['member']['email'] : '', '" rel="nofollow"><img src="', $settings['images_url'], '/email_sm.gif" alt="', $txt['email'], '" /></a></li>';

	// Don't show an icon if they haven't specified a website.
	if ($context['member']['website']['url'] !== '' && !isset($context['disabled_fields']['website']))
		echo '
					<li><a href="', $context['member']['website']['url'], '" title="' . $context['member']['website']['title'] . '" target="_blank" class="new_win">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/www_sm.gif" alt="' . $context['member']['website']['title'] . '" />' : $txt['www']), '</a></li>';

	// Are there any custom profile fields for the summary?
	if (!empty($context['custom_fields']))
	{
		foreach ($context['custom_fields'] as $field)
			if (($field['placement'] == 1 || empty($field['output_html'])) && !empty($field['value']))
				echo '
					<li class="custom_field">', $field['output_html'], '</li>';
	}

	echo '
				', !isset($context['disabled_fields']['icq']) && !empty($context['member']['icq']['link']) ? '<li>' . $context['member']['icq']['link'] . '</li>' : '', '
				', !isset($context['disabled_fields']['msn']) && !empty($context['member']['msn']['link']) ? '<li>' . $context['member']['msn']['link'] . '</li>' : '', '
				', !isset($context['disabled_fields']['aim']) && !empty($context['member']['aim']['link']) ? '<li>' . $context['member']['aim']['link'] . '</li>' : '', '
				', !isset($context['disabled_fields']['yim']) && !empty($context['member']['yim']['link']) ? '<li>' . $context['member']['yim']['link'] . '</li>' : '', '
			</ul>
			<span id="userstatus">', $context['can_send_pm'] ? '<a href="' . $context['member']['online']['href'] . '" title="' . $context['member']['online']['label'] . '" rel="nofollow">' : '', $settings['use_image_buttons'] ? '<img src="' . $context['member']['online']['image_href'] . '" alt="' . $context['member']['online']['text'] . '" align="middle" />' : $context['member']['online']['text'], $context['can_send_pm'] ? '</a>' : '', $settings['use_image_buttons'] ? '<span class="smalltext"> ' . $context['member']['online']['text'] . '</span>' : '';

	// Can they add this member as a buddy?
	if (!empty($context['can_have_buddy']) && !$context['user']['is_owner'])
		echo '
			<br /><a href="', $scripturl, '?action=buddy;u=', $context['id_member'], ';', $context['session_var'], '=', $context['session_id'], '">[', $txt['buddy_' . ($context['member']['is_buddy'] ? 'remove' : 'add')], ']</a>';

	if (!$context['user']['is_owner'] && $context['can_send_pm'])
		echo '
			<br /><a href="', $scripturl, '?action=pm;sa=send;u=', $context['id_member'], '">', $txt['profile_sendpm_short'], '</a><br />';
	echo '
			<a href="', $scripturl, '?action=profile;area=showposts;u=', $context['id_member'], '">', $txt['showPosts'], '</a><br />
			<a href="', $scripturl, '?action=profile;area=statistics;u=', $context['id_member'], '">', $txt['statPanel'], '</a>';

	echo '
			<br /></span>';

	echo'
		</div>
		<span class="botslice">
		<span> </span>
		</span>
	</div>';
}

function breeze_user_info()
{
	global $context;

	if (!empty($context['Breeze']['user_info']))
		foreach ($context['Breeze']['user_info'] as $userData)
			if (!empty($userData['data']))
				echo $userData['data'];
}