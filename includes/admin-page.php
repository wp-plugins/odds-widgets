<div class="wrap">
  <?php screen_icon(); ?>
  <h2>Odds Widgets Settings</h2>
  <?php print $msg; ?>
  <form action="" method="post" id="content_rotation_admin_options_form">
    <h3>Subscribe</h3>
    <p>Subscribe to our mailing list and keep up with the plugin updates</p>
    <?php 
		$subscribe_email = esc_attr( get_option( 'odds_widgets_subscribe_email' ) ); 
		if ( !$subscribe_email ) {
			$subscribe_email = esc_attr( get_option( 'admin_email' ) ); 
		}
		
		if ( esc_attr( get_option( 'odds_widgets_subscribe' ) ) ) {
			$checked = 'checked="checked"'; 
		}
		else {
			$checked = ''; 
		}
		?>
    <table class="form-table">
      <tbody>
        <tr>
          <th><label for="odds_widgets_subscribe_email">Email</label></th>
          <td><input type="text" size="40" id="odds_widgets_subscribe_email" name="odds_widgets_subscribe_email" value="<?php print $subscribe_email; ?>" /></td>
        </tr>
        <tr>
          <th>Subscribe</th>
          <td><input type="checkbox" id="odds_widgets_subscribe" name="odds_widgets_subscribe" value="1" <?php print $checked ?> /> <label for="odds_widgets_subscribe">Subscribe to our mailing list</label></td>
        </tr>
      </tbody>
    </table>

    <h3>Show link to Odds Widgets plugin page</h3>
    <p>This will add a link to the plugin page under each widget</p>
    <?php 
		if ( esc_attr( get_option( 'odds_widgets_link_to_us' ) ) ) {
			$checked = 'checked="checked"'; 
		}
		else {
			$checked = ''; 
		}
		?>
    <p>
      <input type="checkbox" id="odds_widgets_link_to_us" name="odds_widgets_link_to_us" value="1" <?php print $checked ?> />
      <label for="odds_widgets_link_to_us">Show link to Odds Widgets plugin page</label>
    </p>
    <?php global $wp_version; ?>
    <?php if ( version_compare( $wp_version, '3.0', '<' ) ) { ?>
    <hr />
    <h3>Replace WP default jQuery with jQuery 1.4.2</h3>
    <p>WordPress versions prior to 3.0 ship with jQuery 1.3+ by default and this version of jQuery has some known issues with &quot;live()&quot; method in IE6-IE8.<br />
      If you are using IE6-IE8 for widgets administration and Odss Widgets drop-downs don't work, it might be due to the above mentioned.<br />
      You might be able to solve this by enabling the option bellow, which makes the Odds Widgets plugin replace the default jQuery library with jQuery 1.4.2.<br />
      Please beware this might have unexpected results in your wordpress admin and/or front (though it shouldn't), so use at your own risk!</p>
    <?php 
		if ( esc_attr( get_option( 'odds_widgets_replace_jquery' ) ) ) {
			$checked = 'checked="checked"'; 
		}
		else {
			$checked = ''; 
		}
		?>
    <p>
      <input type="checkbox" id="odds_widgets_replace_jquery" name="odds_widgets_replace_jquery" value="1" <?php print $checked ?> />
      <label for="odds_widgets_replace_jquery">Replace WP default jQuery with jQuery 1.4.2</label>
    </p>
    <?php } ?>
    <p class="submit">
      <input type="submit" name="submit"value="Update" />
    </p>
    <?php wp_nonce_field('odds_widgets_admin_options_update','odds_widgets_admin_nonce'); ?>
  </form>
</div>
