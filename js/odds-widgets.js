// JavaScript Document
jQuery(document).ready(function($) {
	init_odds_widgets_preview_holder(); 

	// sport select change
	$('.odds_widget_sport_select').live('change', function() {
		var d = $(this).parents('.odds_widgets_settings_form');
		var sport_name = $(this).val(); 
		resetWidgetTypeSelect(d);
		
		if(sport_name != 0) {
			$('.widget_type_select_holder', d).html('').addClass('ajax_loading vis');
			var data = {
				action: 'odds_widgets_refresh_widget_type',
				sport_name: sport_name,
				item_id: $(this).attr('id'), 
				item_name: $(this).attr('name')
			};
			 
			$.post(ajaxurl, data, function(response) {
				$('.widget_type_select_holder', d).removeClass('ajax_loading').html(''); 
				if(response == 'FALSE') {
					alert("There was an error processing your request!\nPlease make sure you have selected a sport!");
				}
				else {
					//alert($('.widget_type_select_holder', d).attr('class'));
					$('.widget_type_select_holder', d).html(response); 
				}
			});
		}
	});  
	
	// widget type select change
	$('.odds_widget_type_select').live('change', function() {
		var d = $(this).parents('.odds_widgets_settings_form'); 
		resetWidgetSelect(d);
		
		var widget_type = $('.odds_widget_type_select', d).val(); 
		
		if(widget_type != 0) {
			$('.widget_select_holder', d).html('').addClass('ajax_loading vis');
			var data = {
				action: 'odds_widgets_refresh_widget',
				sport_name: $('.odds_widget_sport_select', d).val(),
				widget_type: widget_type, 
				item_id: $(this).attr('id'), 
				item_name: $(this).attr('name')
			}; 
	
			$.post(ajaxurl, data, function(response) {
				$('.widget_select_holder', d).removeClass('ajax_loading').html('');
				if(response == 'false') {
					alert('Please select a type of widget');
				}
				else {
					$('.widget_select_holder', d).html(response); 
				}
			});
		}
	}); 
	
	$('.odds_widget_style_select').live('change', function() {
		var d = $(this).parents('.odds_widgets_settings_form');																		
		var style_id = $(this).val();
		//updateWidgetDataWithCurrentSelectedStyle(style_id, d);
	});
	
	// validate the widget save
	$('input.widget-control-save').click(function(e) {
		odds_widgets_close_preview(); 
		var f = $(this).parents('form');
		var data = odds_widgets_validate_widget_form(f); 
		if(!data) {
			e.preventDefault(); 
			return false; 
		}
	}); 
	
	// preview odds widget
	$('.odds_widget_preview').live('click', function(e){
		e.preventDefault(); 
		var f = $(this).parents('form');
		var data = odds_widgets_validate_widget_form(f); 
		if(data) {
			$('.preview_button_ajax', f).addClass('ajax_loading');
		 	$.post(ajaxurl, data, function(response) {
				$('.preview_button_ajax', f).removeClass('ajax_loading');
				if(response == 'FALSE') {
					alert('There was an error generating the preview!');
				}
				else { 
					$('.odds_widget_preview_holder_inner').html(response);
					$('.odds_widget_preview_holder_inner').append($('<div class="odds_widgets_preview_close_button_holder"><input type="button" value="Close Widget Preview" class="button odds_widgets_preview_close_button" /></div>')); 
					$('.odds_widget_preview_holder').odds_widgets_center().show();
					
				}
		});
		}
		else {
			return false; 
		}
	})
	
	$('.odds_widgets_preview_close_button').live('click', function(){
		odds_widgets_close_preview(); 
	}); 
	
	function odds_widgets_close_preview() {
		var di = $('.odds_widget_preview_holder_inner'); 
		var d = $('.odds_widget_preview_holder'); 
		di.html('');
		d.hide(); 
	}
	
	function odds_widgets_validate_widget_form(f) {
		var data = new Object(); 
		var errors = ''; 
		var data = {
			action: 'odds_widgets_preview_widget',
			sport_name: $('.odds_widget_sport_select', f).val(),
			widget_type: $('.odds_widget_type_select', f).val(), 
			widget: $('.odds_widget_select', f).val(),
			style_id: $('.odds_widget_style_select', f).val()
		};
		
		if(data.sport_name == 0 || data.sport_name == '') {
			errors += "You must select a sport\n"; 
		}
		if(data.widget_type == 0 || data.widget_type == '') {
			errors += "You must select a widget type\n"; 
		}
		if(data.widget == 0 || data.widget == '') {
			errors += "You must select a widget\n"; 
		}
		if(data.style_id == 0 || data.style_id == '') {
			errors += "You must select a style for the widget\n"; 
		}
		
		if(errors != '') {
			alert(errors); 
			return false; 
		}
		return data; 
	}
	
	function resetWidgetTypeSelect(holder) {
		$('.widget_type_select_holder', holder).html(''); 
		resetWidgetSelect(holder);
	}
	
	function resetWidgetSelect(holder) {
		$('.widget_select_holder', holder).html(''); 
	}
	
	function initWidgetsStyleSamples() {
		var style_selects = $('.odds_widgets_settings_form .odds_widget_style_select'); 
		var d = null; 
		$(style_selects).each(function(i, n) {
			d = $(n).parents('.odds_widgets_settings_form');
			updateWidgetDataWithCurrentSelectedStyle($(n).val(), d); 
		}); 
	}
	
	function updateWidgetDataWithCurrentSelectedStyle(style_id, style_sample_holder) {

	//if(style_id != null && box_styles["style_" + style_id] != null) {
		var style_key = 'style_' + style_id;
		var style = box_styles[style_key];
		var str = '';
		var hex_regexp = /^[a-f0-9]{6}$/gi;

		for(key in style){
			var val = style[key];
			val = val.replace('"', '&quot;');
			//alert(key + ' -- ' + val);
			if(val.match(hex_regexp)) {
				var input_id = '#' + key + '_input';
				var sample_id = input_id.replace('_input', '_sample');
				$(sample_id, style_sample_holder).css('backgroundColor', '#' + val);
				$(input_id).val(val);
				updateBoxItems(input_id, val, style_sample_holder);
			}
			else {
				$('#' + key).val(style[key]);
				updateOddsFormat();
				
				if(key == 'show_box_title') {
					if(val == 0) {
						$('.affbox_title', style_sample_holder).css('display', 'none');
						$('#show_box_title', style_sample_holder).attr('checked', false);
						$('.box_title_details', style_sample_holder).css('display', 'none');
					}
					else {
						$('.affbox_title', style_sample_holder).css('display', 'block');
						$('#show_box_title', style_sample_holder).attr('checked', true);
						$('.box_title_details', style_sample_holder).css('display', 'block');
					}
				}
				if(key == 'box_title_font_family') {
					var fam = $('#' + key + ' :selected').text();
					$('.affbox_title', style_sample_holder).css('fontFamily', fam);
				}
				else if(key == 'box_title_font_size') {
					$('.affbox_title', style_sample_holder).css('fontSize', val);
				}
				
				if(key == 'show_market_title') {
					if(val == 0) {
						$('.affbox_market_name', style_sample_holder).css('display', 'none');
						$('#show_market_title', style_sample_holder).attr('checked', false);
						$('.market_title_details', style_sample_holder).css('display', 'none');
					}
					else {
						$('.affbox_market_name', style_sample_holder).css('display', 'block');
						$('#show_market_title', style_sample_holder).attr('checked', true);
						$('.market_title_details', style_sample_holder).css('display', 'block');
					}
				}
				else if(key == 'market_title_font_family') {
					var fam = $('#' + key + ' :selected').text();
					$('.affbox_market_name', style_sample_holder).css('fontFamily', fam);
				}
				else if(key == 'market_title_font_size') {
					$('.affbox_market_name', style_sample_holder).css('fontSize', val);
				}
				
				else if(key == 'odds_table_font_family') {
					var fam = $('#' + key + ' :selected').text();
					$('.affbox_table tr th, .affbox_table tr td', style_sample_holder).css('fontFamily', fam);
				}
				else if(key == 'odds_table_font_size') {
					$('.affbox_table tr th, .affbox_table tr td', style_sample_holder).css('fontSize', val);
				}
				
				// bookie logo size settings
				if(key == 'bookie_logo_size') {
					switch_box_sizes(val);
				}
				
				// referral link settings
				if(key == 'show_box_referral_link') {
					$('.ab_ref_link a').text(style['box_referral_link_text']);
					if(val == 0) {
						$('.ab_ref_link', style_sample_holder).css('display', 'none');
						$('#show_box_referral_link', style_sample_holder).attr('checked', false);
						$('.box_referral_link_details', style_sample_holder).css('display', 'none');
					}
					else {
						$('.ab_ref_link', style_sample_holder).css('display', 'block');
						$('#show_box_referral_link', style_sample_holder).attr('checked', true);
						$('.box_referral_link_details', style_sample_holder).css('display', 'block');
					}
				}
			}
		}
//}

}
	
function updateBoxItems(input_id, hex, style_sample_holder) {
	// box
	if(input_id == '#box_border_color_input') {
		$('.affbox_inner_div', style_sample_holder).css('borderColor', '#' + hex);
	}
	if(input_id == '#box_background_color_input') {
		$('.affbox_inner_div', style_sample_holder).css('backgroundColor', '#' + hex);
	}
	
	// box title
	if(input_id == '#box_title_color_input') {
		$('.affbox_title', style_sample_holder).css('color', '#' + hex);
	}
	else if(input_id == '#box_title_background_color_input') {
		$('.affbox_title', style_sample_holder).css('backgroundColor', '#' + hex);
	}
	
	// market title
	if(input_id == '#market_title_color_input') {
		$('.affbox_market_name', style_sample_holder).css('color', '#' + hex);
	}
	else if(input_id == '#market_title_background_color_input') {
		$('.affbox_market_name', style_sample_holder).css('backgroundColor', '#' + hex);
	}
	
	// odds table head
	if(input_id == '#odds_table_head_color_input') {
		$('.affbox_odds_table_head th', style_sample_holder).css('color', '#' + hex);
	}
	else if(input_id == '#odds_table_head_background_color_input') {
		$('.affbox_odds_table_head th', style_sample_holder).css('backgroundColor', '#' + hex);
	}
	
	// odd rows
	if(input_id == '#odd_row_color_input') {
		$('.affbox_odd td', style_sample_holder).css('color', '#' + hex);
	}
	else if(input_id == '#odd_row_background_color_input') {
		$('.affbox_odd td', style_sample_holder).css('backgroundColor', '#' + hex);
	}
	
	// even rows
	if(input_id == '#even_row_color_input') {
		$('.affbox_even td', style_sample_holder).css('color', '#' + hex);
	}
	else if(input_id == '#even_row_background_color_input') {
		$('.affbox_even td', style_sample_holder).css('backgroundColor', '#' + hex);
	}
}

function updateOddsFormat(style_sample_holder) {
	var format = $('#odds_table_odds_format', style_sample_holder).val();
	$('.affbox_odds span.selected', style_sample_holder).removeClass('selected');
	if(format == 'f') {
		$('span.fra', style_sample_holder).addClass('selected');
	}
	else if(format == 'a') {
		$('span.ame', style_sample_holder).addClass('selected');
	}
	else {
		$('span.dec', style_sample_holder).addClass('selected');
	}
}

function switch_box_sizes(id) {
	$('#bookie_logo_size_' + id).attr("checked", 'checked');
	$('div.bookie_logo_size_holder').removeClass('bookie_logo_size_holder_active');
	$('#bookie_logo_size_holder_' + id).addClass('bookie_logo_size_holder_active');
	
	var images = $('.affbox_bookie_logo img');
	var val = 80; 
	var w = 80;

	$.each(images, function(i, n) {
		var src = $(n).attr('src');

		if(id == 2) {
			val = 40;
			w = 40;
		}
		else if(id == 3) {
			val = 54;
			w = 54;
		}
		
		var preg = /\/[\d]{1}[04]{1}(|b)\//;
		
		src = src.replace(preg, '/' + val + '/');
		$(images[i]).attr('src', src);
		$('.affbox_bookie_logo').css('width:' + w + 'px;');

	});
}

function init_odds_widgets_preview_holder() {
	$('body').append($('<div class="odds_widget_preview_holder"><div class="odds_widget_preview_holder_inner"></div></div>'));  
}


	
});
jQuery.fn.odds_widgets_center = function() {
	this.css("position","absolute");
	var top = ( jQuery(window).height() - this.height() ) / 2+jQuery(window).scrollTop();
	if(top < 0) {
		top = 0;
	}
	//alert(jQuery(window).height() + ' - ' + jQuery(window).width() + ' - ' + jQuery(window).scrollLeft());
	this.css("top", top + "px");
	this.css("left", ( jQuery(window).width() - this.width() ) / 2+jQuery(window).scrollLeft() + "px");
	return this;
}
