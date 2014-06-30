<?php
/*
Plugin Name: ZigDashNote
Plugin URI: http://www.zigpress.com/plugins/zigdashnote/
Description: Adds a text widget to the Dashboard for notes and reminders. HTML allowed, HTML restrictions observed, URLs automatically linkified.
version: 0.3.3
Author: ZigPress
Requires at least: 3.5
Tested up to: 3.9
Author URI: http://www.zigpress.com/
License: GPLv2
*/


/*
Copyright (c) 2011-2012 ZigPress

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation Inc, 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
*/


if (!class_exists('zigdashnote')) {


	class zigdashnote
	{
	
	
		private $options;
		private $version;
	
	
		public function __construct() {
			$this->options = array();
			$this->version = '0.3.3';
			global $wp_version;
			if (version_compare(phpversion(), '5.2.4', '<')) wp_die('ZigDashNote requires PHP 5.2.4 or newer. Please update your server.'); 
			if (version_compare($wp_version, '3.5', '<')) $this->AutoDeactivate('ZigDashNote requires WordPress 3.5 or newer. Please update your installation.'); 
			add_action('wp_dashboard_setup', array($this, 'action_wp_dashboard_setup'));
			add_filter('plugin_row_meta', array($this, 'filter_plugin_row_meta'), 10, 2 );
		}
	
	
		public function activate() {
			$this->options = $this->get_or_create_options();
			update_option('zigdashnote_options', $this->options);
		}
	
	
		public function deactivate() {}
	
	
		public function action_wp_dashboard_setup() {
			$this->options = get_option('zigdashnote_options');
			wp_add_dashboard_widget('ZigDashNote', $this->options['title'], array($this, 'output'), array($this, 'control'));
		}
	
	
		public function filter_plugin_row_meta($links, $file) {
			$plugin = plugin_basename(__FILE__);
			if ($file == $plugin) return array_merge($links, array('<a target="_blank" href="http://www.zigpress.com/donations/">Donate</a>'));
			return $links;
		}
	
	
		private function get_or_create_options() {
			$defaults = array('title'=>'ZigDashNote', 'text'=>'Your text here', 'credit'=>1);
			if ((!$this->options = get_option('zigdashnote_options')) || !is_array($this->options)) $this->options = array();
			return array_merge($defaults, $this->options);
		}
	
	
		public function output() { 
			echo wpautop($this->options['text']);
			if ($this->options['credit']) {
				?>
				<div class="description"><small><em><a class="description" target="_blank" href="http://www.zigpress.com/plugins/zigdashnote/">ZigDashNote</a> <?php echo $this->version?> dashboard widget by <a class="description" target="_blank" href="http://www.zigpress.com/">ZigPress</a></em></small></div>
				<?php
			}
		}
	
	
		public function control() { 
			if (('post' == strtolower($_SERVER['REQUEST_METHOD'])) && isset($_POST['widget_id']) && ('ZigDashNote' == $_POST['widget_id'])) {
				foreach (array('title', 'text', 'credit') as $key) $this->options[$key] = stripslashes($_POST['zigdashnote_' . $key]);
				$this->options['credit'] = ($this->options['credit'] == 1) ? 1 : 0;
				if (!current_user_can('unfiltered_html')) {
					$this->options['text'] = stripslashes(wp_filter_post_kses($this->options['text']));
				} else {
					$this->options['text'] = make_clickable($this->options['text']); 
				}
				update_option('zigdashnote_options', $this->options);
			}
			?>
			<p>Title: <input class="widefat" type="text" id="zigdashnote_title" name="zigdashnote_title" value="<?php echo $this->options['title'] ?>" /></p>
			<p><textarea class="widefat" rows="10" id="zigdashnote_text" name="zigdashnote_text"><?php echo $this->options['text'] ?></textarea></p>
			<p>Show credit: <input type="checkbox" id="zigdashnote_credit" name="zigdashnote_credit" value="1" <?php echo ($this->options['credit'] == 1) ? 'checked="checked"' : ''?> /></p>
			<?php
		}
	
	
	} # END OF CLASS


} else {
	wp_die('Namespace clash! Class zigdashnote already exists.');
}


# INTEGRATE PLUGIN


$zigdashnote = new zigdashnote();
register_activation_hook(__FILE__, array(&$zigdashnote, 'activate'));
register_deactivation_hook(__FILE__, array(&$zigdashnote, 'deactivate'));


# EOF
