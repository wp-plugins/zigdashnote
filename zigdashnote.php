<?php
/*
Plugin Name: ZigDashNote
Plugin URI: http://www.zigpress.com/wordpress/plugins/zigdashnote/
Description: Adds a text widget to the Dashboard for notes and reminders. HTML allowed, HTML restrictions observed, URLs automatically linkified.
Version: 0.2.1
Author: ZigPress
Requires at least: 3.0
Tested up to: 3.1.1
Author URI: http://www.zigpress.com/
License: GPLv2
*/


/*
Copyright (c) 2011 ZigPress

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


/*
ZigPress PHP code uses Whitesmiths indent style: http://en.wikipedia.org/wiki/Indent_style#Whitesmiths_style
*/


# VERSION CHECK


global $wp_version;
if (version_compare($wp_version, "3.0", "<")) 
	{ 
	exit('ZigDashNote requires WordPress 3.0 or newer. Please update your installation.'); 
	}
if (floatval(phpversion()) < 5)
	{
	exit('ZigDashNote requires PHP 5.0 or newer. Please update your server.'); 
	}


# DEFINE PLUGIN


if (!class_exists('ZigDashNote'))
	{
	class ZigDashNote
		{
		public $Options;
		public $Version;


		public function __construct()
			{
			$this->Options = array();
			$this->Version = '0.2';
			add_action('wp_dashboard_setup', array($this, 'WPDashboardSetup'));
			add_filter('plugin_row_meta', array($this, 'FilterPluginRowMeta'), 10, 2 );
			}


		public function Activate()
			{
			$this->Options = $this->GetOrCreateOptions();
			update_option('zigdashnote_options', $this->Options);
			}


		public function Deactivate()
			{
			}


		public function WPDashboardSetup()
			{
			$this->Options = get_option('zigdashnote_options');
			wp_add_dashboard_widget('zigDashNote', $this->Options['title'], array($this, 'Output'), array($this, 'Control'));
			}


		private function GetOrCreateOptions() 
			{
			$defaults = array('title'=>'ZigDashNote', 'text'=>'Your text here', 'credit'=>1);
			if ((!$this->Options = get_option('zigdashnote_options')) || !is_array($this->Options)) 
				{
				$this->Options = array();
				}
			return array_merge($defaults, $this->Options);
			}


		public function Output() 
			{
			echo wpautop($this->Options['text']);
			if ($this->Options['credit'])
				{
				?>
				<div class="description"><small><em><a class="description" target="_blank" href="http://www.zigpress.com/wordpress/plugins/zigdashnote/">ZigDashNote</a> <?php echo $this->Version?> dashboard widget by <a class="description" target="_blank" href="http://www.zigpress.com/">ZigPress</a></em></small></div>
				<?php
				}
			}


		public function Control() 
			{
			if (('post' == strtolower($_SERVER['REQUEST_METHOD'])) && isset($_POST['widget_id']) && ('zigDashNote' == $_POST['widget_id'])) 
				{
				foreach (array('title', 'text', 'credit') as $key) 
					{
					$this->Options[$key] = stripslashes($_POST['zigdashnote_' . $key]);
					}
				$this->Options['credit'] = ($this->Options['credit'] == 1) ? 1 : 0;
				if (!current_user_can('unfiltered_html')) 
					{
					$this->Options['text'] = stripslashes(wp_filter_post_kses($this->Options['text']));
					}
				else
					{
					$this->Options['text'] = make_clickable($this->Options['text']); 
					}
				update_option('zigdashnote_options', $this->Options);
				}
			?>
			<p>Title: <input class="widefat" type="text" id="zigdashnote_title" name="zigdashnote_title" value="<?php echo $this->Options['title'] ?>" /></p>
			<p><textarea class="widefat" rows="12" id="zigdashnote_text" name="zigdashnote_text"><?php echo $this->Options['text'] ?></textarea></p>
			<p>Show credit: <input type="checkbox" id="zigdashnote_credit" name="zigdashnote_credit" value="1" <?php echo ($this->Options['credit'] == 1) ? 'checked="checked"' : ''?> /></p>
			<?php
			}


		public function FilterPluginRowMeta($links, $file) 
			{
			$plugin = plugin_basename(__FILE__);
			if ($file == $plugin) return array_merge($links, array('<a target="_blank" href="http://www.zigpress.com/donations/">Donate</a>'));
			return $links;
			}


		} # end of class


	}
else
	{
	exit('Class ZigDashNote already declared!');
	}


# INSTANTIATE PLUGIN


$objZigDashNote = new ZigDashNote();


# INTEGRATE PLUGIN


if (isset($objZigDashNote))
	{
	register_activation_hook(__FILE__, array(&$objZigDashNote, 'Activate'));
	register_deactivation_hook(__FILE__, array(&$objZigDashNote, 'Deactivate'));
	}


# EOF
