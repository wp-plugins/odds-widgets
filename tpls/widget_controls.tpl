<!--
This .tpl file is used when editing a widget's options in the WP manager.
It should only contain form *elements*; WordPress will supply the opening and closing <form> tags.
For each key in the OddsWidget::$control_options array, you will have the following placeholders available:
[[your_key.id]] - used inside id attributes, e.g.
id="[[your_key.id]]"
[[your_key.name]] - used inside name attributes, e.g. name="[[your_key.name]]"
[[your_key.value]] - contains the current value of the option
WordPress appends text to the names and id's to allow for multiple instances of the widget, so don't try hard-coding values here.
-->
<div class="odds_widgets_settings_form">
  <label for="[[title.id]]">Title</label>
  <input type="text" class="widefat" id="[[title.id]]" name="[[title.name]]" value="[[title.value]]" />
  <label for="[[sport_name.id]]">Select sport</label>
  [[sport_select]] 
  
  <span class="widget_type_select_holder[[widget_type_select_holder_class]]">
  <label for="[[widget_type.id]]">Select widget type</label>
  [[widget_type_select]] 
  
  </span> 
  <span class="widget_select_holder[[widget_select_holder_class]]">
  <label for="[[widget.id]]">Select widget</label>
  [[widget_select]] 
  
  </span> 
  <span class="widget_style_select_holder">
  
  <label for="[[style_id.id]]">Select widget style</label>
  [[style_select]]
  </span>
  <hr style="border:0px none; border-bottom:1px solid #ccc;" />
  <!--<input type="hidden" class="widefat" id="[[affkey.id]]" name="[[affkey.name]]" value="[[affkey.value]]" />-->
  <!--<input type="hidden" class="widefat" id="[[boxid.id]]" name="[[boxid.name]]" value="[[boxid.value]]" />-->
	
  <input type="button" class="button odds_widget_preview" value="Preview Widget" />
  <span class="preview_button_ajax"></span>
</div>