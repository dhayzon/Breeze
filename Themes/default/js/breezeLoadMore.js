/**
 * breezeLoadMore.js
 *
 * Loads the next batch of status.
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
 * Portions created by the Initial Developer are Copyright (c) 2012, 2013
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 *
 */

jQuery(document).ready(function(){

	// Fires up the load more thingy
	if (breeze.currentSettings.load_more){

		var numberOfEvents = 0;

		// Hide the normal pagination.
		jQuery('.pagelinks').hide();

		showMoarButton();
	}

	function showMoarButton()
	{
		// Add a nice button...
		jQuery('<button/>', {
			id: 'loadMoar',
			class: 'clear',
			text: breeze.text.load_more,
			click: function () {

				numberOfEvents++;

				jQuery('<div/>', {
					id: 'tempDIV_'+ numberOfEvents,
					class: 'clear',
					style: ''
				}).appendTo('#breezeAppendTo').hide();

				// Append some more data to breeze.pagination.
				breeze.pagination.numberTimes = numberOfEvents;
				breeze.pagination.comingFrom = breeze.tools.comingFrom

				jQuery.ajax(
				{
					// Send the data and oh boy there's a lot of it!
					url: smf_scripturl + '?action=breezeajax;sa=fetch;js=1;' + breeze.session.v + '=' + breeze.session.id,
					data : breeze.pagination,
					cache: false,
					dataType: 'json',
					success: function(html)
					{
						// The server response as a JSON object
						if(html.type == 'success'){

							// There are no more data to load!
							if (html.data != 'end'){
								jQuery('#tempDIV_'+ numberOfEvents).append(html.data).fadeIn('slow', 'linear', function(){});
							}

							else{
								noty({
									text: breeze.text.load_more_no,
									timeout: 3500,
									type: 'success'
								});
								jQuery('#loadMoar').fadeOut('slow');
								return;
							}
						}

						else if(html.type == 'error'){
							noty({
								text: html.message,
								timeout: 3500, type: html.type,
								type: html.type
							});
						}
					},
					error: function (html){
						noty({
							text: html,
							timeout: 3500,
							type: 'error'
						});
					}
				});
			}
		}).appendTo('#tab-wall');
	}

	// Check if we are near the end of the page
	function GetScrollPercent()
	{
		 var bottom = jQuery(window).height() + jQuery(window).scrollTop();
		 var height = jQuery(document).height();

		 return Math.round(100*bottom/height);

	}
});