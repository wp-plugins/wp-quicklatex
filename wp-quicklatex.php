<?php
/*
		Plugin Name: WP QuickLaTeX
		Plugin URI: http://www.holoborodko.com/pavel/?page_id=1422
		Description: Insert math in the posts and comments using LaTeX. High rendering quality with anti-aliasing, correct vertical alignment of the formulas, inline and display math modes, precise font properties tuning, custom LaTeX document preamble, meaningful error messages, caching. No LaTeX installation required. Just wrap LaTeX code with [latex]..[/latex] tags. Plugin will do the rest. Check it in action on my <a href="http://www.holoborodko.com/pavel/">blog</a>, e.g. on the <a href="http://www.holoborodko.com/pavel/?page_id=239">Central Differences</a> page. Based on free LaTeX rendering web service <a href="http://quicklatex.com/">QuickLaTeX.com</a>.
		Version: 3.0.0
		Author: Pavel Holoborodko
		Author URI: http://www.holoborodko.com/pavel/
		Copyright: Pavel Holoborodko
		License: GPL2
*/


/*
	Wordpress plugin for the QuickLaTeX.com rendering service.
	
	Project homepage: http://www.holoborodko.com/pavel/?page_id=1422
	Contact e-mail:   pavel@holoborodko.com

 	Copyright 2008-2010 Pavel Holoborodko
	All rights reserved.

	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions
	are met:
	
	1. Redistributions of source code must retain the above copyright
	notice, this list of conditions and the following disclaimer.
	
	2. Redistributions in binary form must reproduce the above copyright
	notice, this list of conditions and the following disclaimer in the
	documentation and/or other materials provided with the distribution.
	
	3. Redistributions of any form whatsoever must retain the following
	acknowledgment:
	"
         This product includes software developed by Pavel Holoborodko
         Web: http://www.holoborodko.com/pavel/
         e-mail: pavel@holoborodko.com
	
	"

	4. This software cannot be, by any means, used for any commercial 
	purpose without the prior permission of the copyright holder. 
	
	5. This software is for individual usage only. It cannot be used as a part
	of blog hosting services for multiple users like WordPress MU or any other 
	"software as a service" systems without the prior permission of the copyright holder. 
	
	Any of the above conditions can be waived if you get permission from 
	the copyright holder. 

	THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS ``AS IS'' AND
	ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
	IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
	ARE DISCLAIMED.  IN NO EVENT SHALL THE AUTHOR OR CONTRIBUTORS BE LIABLE
	FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
	DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS
	OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
	HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
	LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
	OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF
	SUCH DAMAGE.
 
*/

// Prevent direct call to this php file
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

// Define some constants http://codex.wordpress.org/Determining_Plugin_and_Content_Directories
if ( ! defined( 'WP_CONTENT_DIR' ) )
      define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );

if ( ! defined( 'WP_PLUGIN_DIR' ) )
      define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );

if ( ! defined( 'WP_QUICKLATEX_CACHE_DIR' ) )
      define( 'WP_QUICKLATEX_CACHE_DIR', WP_CONTENT_DIR.'/ql-cache' );

if ( ! defined( 'WP_QUICKLATEX_CACHE_URL' ) )
      define( 'WP_QUICKLATEX_CACHE_URL', content_url(). '/ql-cache' );

if ( is_admin() ) {

	if (function_exists('register_uninstall_hook'))
    	register_uninstall_hook(__FILE__, 'uninstall_quicklatex');

	// Check do we have options in DB. Wrte defaults if not.
	if(!get_option('quicklatex',false))									
	{
		// Create array of default settings (you can use the filter to modify these)
		$quicklatex_defaultsettings = array(
			'font_size'      	=> 17, 			    // 17px
			'font_color' 	 	=> '000000',		// black text
			'bg_type'        	=> 0, 				// Transparent
			'bg_color'       	=> 'ffffff',		// white background if opaque
			'latex_mode'     	=> 0,  				// math mode 
			'preamble'       	=> "", 				//\newcommand{\hypotenuse}{a^2+b^2}
			'use_cache'      	=> 1,  				// use cache
			'show_errors'		=> 0,  				// do not show errors
			'add_footer_link'	=> 0
		);
	
		update_option('quicklatex',$quicklatex_defaultsettings);		
	}
	
	add_action('admin_init', 'quicklatex_init');	
	
	// QuickLaTeX Administration Menu
	add_action('admin_menu', 'quicklatex_menu');
	
	function quicklatex_menu() 
	{
	
		if (function_exists('add_menu_page')) 
		{
			$page = add_menu_page(
						  'QuickLaTeX', 									//$page_title
						  'QuickLaTeX', 									//$menu_title
						  'manage_options',							    	//$capability, http://codex.wordpress.org/Roles_and_Capabilities
						  '',
						  //'wp-quicklatex/wp-quicklatex-admin.php',       	//$menu_slug - > php file which handles admin page
						  'quicklatex_options_do_page',						//$function, generates admin page - using now
						  plugins_url('wp-quicklatex/images/quicklatex_menu_icon.png')   //$icon_url
						  );

			// Using registered $page handle to hook script load
			// http://codex.wordpress.org/Function_Reference/wp_enqueue_script			
        	add_action('admin_print_scripts-'. $page, 'quicklatex_admin_scripts');
		}
	
		if (function_exists('add_submenu_page')) 
		{
			//add_submenu_page('wp-quicklatex/wp-quicklatex-admin.php','Options', 'Options', 'manage_options', 'wp-quicklatex/wp-quicklatex-admin.php');
			//add_submenu_page('wp-quicklatex/wp-quicklatex-admin.php','Uninstall', 'Uninstall',  'manage_options', 'wp-quicklatex/wp-quicklatex-uninstall.php');
						  
		}
	}

 	function quicklatex_admin_scripts()
    {
		// Load jQuery on admin page
        wp_enqueue_script('jquery');
    }
    

	// Delete DB entry on uninstall 
	function uninstall_quicklatex()
	{
		delete_option("quicklatex");	
	}
	
	// Register the plugin's setting
	function quicklatex_init() 
	{
		//http://planetozh.com/blog/2009/05/handling-plugins-options-in-wordpress-28-with-register_setting/
		register_setting( 'quicklatex_options', 'quicklatex', 'quicklatex_validate_options');
	}
	
	// Validate user input
	// http://ottodestruct.com/blog/2009/wordpress-settings-api-tutorial/
	function quicklatex_validate_options($input)
	{
		$options = get_option('quicklatex');
		
		$newinput = $input;
		$newinput['font_color'] = quicklatex_sanitize_color(trim($input['font_color']));
		if($newinput['bg_type']==1)
		{
			// if opaque - sanitize color
			$newinput['bg_color'] = quicklatex_sanitize_color(trim($input['bg_color']));			
		}else{
			// just copy color from DB otherwise
			// since disabled elements do not submitted in the forms
			$newinput['bg_color'] = $options['bg_color'];
		}
		
		$newinput['preamble'] = trim($input['preamble']);
							
		return $newinput;
	}
		
	function quicklatex_options_do_page()
	{
	?>
		<?php $options = get_option('quicklatex'); ?>		
		
<script type="text/javascript">
// <![CDATA[
	jQuery(document).ready(function($) {
	 
	 is_visible = false;
	 $(".qltoggle").hide();
	 
	 $("h4#advanced")
	 	.click(function(event)
		{
			is_visible = !is_visible;
			
	 		if(is_visible)
			{
				$(".qltoggle").show();
				$("img#advanced").attr('src', "<?php echo plugins_url('wp-quicklatex/images/minus.gif'); ?>" );
			}else{
				$(".qltoggle").hide();			
				$("img#advanced").attr('src', "<?php echo plugins_url('wp-quicklatex/images/plus.gif'); ?>" );				
			}
	 	})
		.hover(
			function(){
				$(this).css('color', '#0000FF');},
			function(){
				$(this).css('color', '#333333');}
		)
		.css('cursor','pointer');
		
		// Use transparent background color by default
		$("#txtOpaque").attr('disabled',"<?php if($options['bg_type']==0) echo 'disabled';  else echo ''; ?>");
			
		$("#radTransparent").click(function(event)
		{
			$("#txtOpaque").attr('disabled','disabled');
		});

		$("#radOpaque").click(function(event)
		{
			$("#txtOpaque").removeAttr('disabled');
		});
		
	 });
// ]]>
</script>	

		
		<?php 
			if($options['use_cache']==1)
			{
				if(false==is_quicklatex_cache_writable(WP_QUICKLATEX_CACHE_DIR))
				{
					echo '<div id="message" class="updated"><p>Cannot create/access directory to cache formula images: <strong>'.WP_QUICKLATEX_CACHE_DIR.'</strong>.<br />'.'Please create it by yourself and make sure it is writable (by <code>chmode 777</code> or through File Manager in cPanel).'.'<br />'.'You can turn caching off but it is not wise since <strong>caching greatly improves performance of your site</strong>.'.'</p></div>';
				}
			}
		?>	
		<div style="width:700px;">
		<div class="wrap">
		<!-- <h2>QuickLaTeX Settings</h2> -->
			<p style="text-align:center;">
			<img <?php echo 'src='.plugins_url('wp-quicklatex/images/quicklatex_logo.gif'); ?> >
			</p>
		    
			<h3>Usage</h3>
			<p style="margin:10px;margin-left:20px;"> 
			Place your LaTeX code between the shortcodes <code>[latex]your LaTeX code here[/latex]</code>. <br />
			<a href="http://quicklatex.com/">QuickLaTeX</a> will do the rest. Enjoy!
			</p>
			
			<h3>Settings</h3>	
			These settings will be used for every equation on the blog as defaults.
			You can override them for particular formula by shortcode parameters (check <a href="http://www.holoborodko.com/pavel/?page_id=1422">examples</a>). 
			<form method="post" action="options.php">
				<?php settings_fields('quicklatex_options'); ?>
			
				<table cellspacing=15>
				<tr>
				<td style="width:120px;">Font size</td>
				<td>
					<select style="width:100px;" name="quicklatex[font_size]">
					<?php 
						for($i=5;$i<100;$i++)
						{
							if ($i==$options['font_size']){
								echo '<option value="'.$i.'"'.'selected="selected">'.$i.'px</option>'; 																		}else{
								echo '<option value="'.$i.'">'.$i.'px</option>'; 															
							}
						} 
					?>
					</select>
				</td>	
				</tr>
				<tr>
				<td>Font color</td>
				<td>
					<input type="text" name="quicklatex[font_color]" style="width:100px;" value="<?php echo $options['font_color']; ?>"/>
				</td>	
				<td>
				<p style="font-size:80%;">
					A six digit hexadecimal number like <code>000000</code>(black) or <code>ffffff</code>(white).
					<br/>Please see <a href="http://w3schools.com/css/css_colors.asp">CSS Colors</a> for examples.
				</p>
				</td>
				</tr>
				<tr>
				<td>Background color:<br />
					<div style="margin-left:20px;">
					<input type="radio" id="radTransparent" name="quicklatex[bg_type]" value="0" <?php if($options['bg_type']==0) echo 'checked="checked"'; ?> />Transparent<br />
					</div>
				</td>
				<td></td>	
				</tr>
			
				<tr>
				<td>
					<div style="margin-left:20px;">
				    <input type="radio" id="radOpaque" name="quicklatex[bg_type]" value="1" <?php if($options['bg_type']==1) echo 'checked="checked"'; ?>/>Opaque		
					</div>
				</td>
				<td><input type="text" id="txtOpaque" name="quicklatex[bg_color]" style="width:100px;" value="<?php echo $options['bg_color']; ?>"/></td>
				<td>
				<p style="font-size:80%;">
					A six digit hexadecimal number like <code>000000</code>(black) or <code>ffffff</code>(white).
					<br/>Please see <a href="http://w3schools.com/css/css_colors.asp">CSS Colors</a> for examples.
				</p>
				</td>
				</tr>	
				</table>
				
				<h4 id="advanced"><img id="advanced" <?php echo 'src='.plugins_url('wp-quicklatex/images/plus.gif'); ?>> Advanced Options</h4>	
				<div id="advanced" style="display:block;" class="qltoggle">
				<!-- Please use these settings with care. They are for testing purposes only. -->
				<table cellspacing=15>
				<tr>
				<td style="width:105px;">LaTeX mode</td>
				<td>
					<select style="width:100px;" name="quicklatex[latex_mode]">
					<?php
						if($options['latex_mode']==0) 
						{
							echo '<option value="0" selected="selected">Math</option>';							
							echo '<option value="1">Text</option>';														
						}else{
							echo '<option value="0">Math</option>';							
							echo '<option value="1" selected="selected">Text</option>';														
						}
					?>
					</select>
				</td>
				</tr>
				</table>
				<div style="margin-left:15px;">
				Document Preamble (here you can define new commands and include additional packages):<br />
			    <textarea  rows="5" cols="70" name="quicklatex[preamble]"><?php echo $options['preamble']; ?></textarea>	
				</div>
				</div>
			
				<h4>Miscellaneous</h4>		
				<div style="margin:10px;margin-left:20px;">
				
				<input type="checkbox" name="quicklatex[use_cache]" value="1" <?php if($options['use_cache']==1)echo 'checked="checked"'; ?>/> Cache formula images locally. 
				<br />
				<input type="checkbox" name="quicklatex[show_errors]" value="1" <?php if($options['show_errors']==1)echo 'checked="checked"'; ?>/> Show LaTeX errors. 
				
				<!-- <input type="checkbox" name="quicklatex[add_footer_link]" value="1" <?php if($options['add_footer_link']==true) echo 'checked="checked"'; ?> /> Add 'Powered by <a href="http://QuickLaTeX.com" >QuickLaTeX.com</a>' link to the footer<br />	
				 -->
				 
				</div>
			
				<input class='button-primary' type='submit' name='Save' value='<?php _e('Save Changes'); ?>' id='submitbutton' />	
			</form>
		
			<h3>Information</h3>
			Please visit <a href="http://www.holoborodko.com/pavel/?page_id=1422">plugin's home page</a> for detailed explanation of usage. Also you can leave your feedback there or suggest new features to be included in future versions.
			<br></br>			
			<br></br>						
			If you like QuickLaTeX, please support its development by doing the following:			
			<br />
				<table cellspacing=10>
				
				<tr>
				<td>&#9632;</td>
				<td>Write post about QuickLaTeX.</td>
				</tr>

				<tr>
				<td>&#9632;</td>
				<td>Place <a href="http://www.quicklatex.com/">Powered by QuickLaTeX.com</a> link somewhere on your blog.</td>
				</tr>

				<tr>
				<td>&#9632;</td>
				<td>Spread the word among your colleagues and friends.</td>
				</tr>
				
				</table>

				
		</div>
		</div>
		
	<?php	
	}

}else{

	if( !class_exists( 'WP_Http' ) )
         include_once( ABSPATH . WPINC. '/class-http.php' );
	
	// Load default options
	// Could be included in init function of class
	$ql_options   = get_option('quicklatex');
	$ql_size      = $ql_options['font_size'];
	$ql_color     = $ql_options['font_color'];
	$ql_bg_type   = $ql_options['bg_type'];
	$ql_bg_color  = $ql_options['bg_color'];
	$ql_mode      = $ql_options['latex_mode'];
	$ql_preamble  = $ql_options['preamble'];	
	$ql_use_cache = $ql_options['use_cache'];	
	$ql_show_errors = $ql_options['show_errors'];		
	$ql_link      = $ql_options['add_footer_link'];	

	
	// Compile formula with parameters
	// called by do_quicklatex_tag()
	function quicklatex_kernel( $atts, $formula_text) 
	{
		//Get access to global variables
		global $ql_size, $ql_color, $ql_bg_type, $ql_bg_color, $ql_mode, $ql_preamble, $ql_use_cache, $ql_show_errors, $ql_link;

	   // Setup defaults if user didn't supply parameters by shortcode_atts
	   // Extract parameter values as local variables by extract
	   extract( shortcode_atts( array(
	      'size' => $ql_size,
	      'color' => $ql_color,
	      'background' => false,
		  'mode' => $ql_mode,
		  'preamble' => $ql_preamble,
		  'center' => 'false',
		  'example' => 'false',
		  'errors'  => $ql_show_errors 
	      ), $atts));
				
		// Our main variables
		$image_url   = false;
		$image_align = false;
		$status 	 = -100; // indicates global error
		$error_msg   = "Cannot generate formula";
		$out_str     = "Cannot generate formula";

		// Quick check of the parameters
		$formula_text = trim($formula_text);
		$preamble     = trim($preamble);
		$color 		  = quicklatex_sanitize_color($color);

		// Check size for valid ranges
		if($size<5)  $size = 5;
		if($size>99) $size = 99;

		// Check mode 
		if($mode!=0 && $mode!=1) $mode = 0;			  

		// Check background
		if($background==false)
		{
			if($ql_bg_type==1) $background = $ql_bg_color;																				
			else $background ='transparent';
		}else{
			if($background!='transparent')		
				$background = quicklatex_sanitize_color($background);									
		}
	
		if(null!=$formula_text)
		  {
		  	 if($center=='false')
			 {
					 // Analyze formula for displaymath mode environments
					 // and do centering automatically
					 $displayedenv = array(
									'$$',
									'\\[',
									'\\begin{gather',
									'\\begin{multiline',
									'\\begin{align',
									'\\begin{flalign',
									'\\begin{eqnarray',
									'\\begin{alignat',
									'\\begin{displaymath'
									);
									
					  $formula_text_escaped = addslashes($formula_text);
					  foreach ($displayedenv as $value)
					       if (strpos($formula_text_escaped, $value) != false) 
						   {
						   		$center = 'true';
						   		break;	
						   }
					  
					  unset($value);	
			  }
	  			
		  	  // Build hash based on local and global settings.
			  // So it will change if any setting is changed.
			  $formula_hash = md5($formula_text.$size.$color.$background.$mode.$preamble.$errors);		  
			  
			  $info_file  = 'quicklatex.com-'.$formula_hash.'_l2.txt';
			  $image_file = 'quicklatex.com-'.$formula_hash.'_l2.gif';		

			  $cache_url  = WP_QUICKLATEX_CACHE_URL;
			  $cache_path = WP_QUICKLATEX_CACHE_DIR;
			  
			  $info_full_path  = $cache_path.'/'.$info_file;	
		      $image_full_path = $cache_path.'/'.$image_file;	

			  // Should we use cache?	
			  if($ql_use_cache==1)
			  {
				  	// Check if we have formula in the cache
					if(file_exists($info_full_path) && file_exists($image_full_path))
					{
						// Check if it readable
						if(is_readable($info_full_path) && is_readable($image_full_path))					
						{
								
								//echo "<br/>QL: Get image from cache.";																			
						  		
								// Get formula from the cache
								// Read info file
								$handle = fopen($info_full_path, "r");
								$image_url = rtrim(fgets($handle),"\n");
								$image_align = rtrim(fgets($handle),"\n");				
								fclose($handle);
				
								$image_url = WP_QUICKLATEX_CACHE_URL.'/'.$image_file;			
								$status = 0;
						}
					}
			  }
			  
			  // Check if we still do not have image_url, reasons:
			  // - we pushed to not use cache
			  // - or formula is not in the cache
			  if(!$image_url)
			  {
			  		//echo "<br/>QL: New image generation!";
					
					// Cannot access cache or formula is not in the cache.
					// Create new query to the QuickLaTeX.com to generate formula
					// Start heavy stuff

					// 1.
					// Remove any HTML tags added by WordPress in the Visual Editing mode
					$formula_text = strip_tags($formula_text);
					
					// 2.
					// Latex doesn't understand some fancy symbols 
					// inserted by WordPress as HTML numeric entities
					// Make sure they are not included in the formula text.
					// Add lines as needed using HTML symbol translation references:
					// http://www.htmlcodetutorial.com/characterentities_famsupp_69.html
					// http://www.ascii.cl/htmlcodes.htm
					// http://leftlogic.com/lounge/articles/entity-lookup/
					$formula_text = str_replace(
							array( '&quot;', '&#8220;', '&#8221;',  '&#8125;', '&#8127;', '&#8217;', '&#8216;', '&#8211;', "\n", "\r", "\xa0" ),
							array( 		'"',      '``',      "''",        "'",       "'",       "'",	   "'",       "-",  ' ',  ' ', 	  ' ' ),
							$formula_text
					);
					
					// 3.
					// Decode HTML entities (numeric or literal) to characters, e.g. &amp; to &.
					$formula_text = quicklatex_unhtmlentities($formula_text);
					
					// 4.
					// Make URL
					$url = 'http://www.quicklatex.com/latex2.f';
					//$url = 'http://localhost/ql/latex2.f';

					$url = $url.'?formula='.rawurlencode($formula_text);
					$url = $url.'?hf='.$size.'px';										
					$url = $url.'?fc='.$color;					
					$url = $url.'?mode='.$mode;					
					$url = $url.'?remhost='.get_option('siteurl');					
										
					if($preamble!='') $url = $url.'?preamble='.rawurlencode($preamble);										
					if($background!='transparent')	$url = $url.'?bc='.$background;																
					if($errors==1)	$url = $url.'?errors=1';																					
					
					//echo '<br />'.$url.'<br />';
					
					// Send request to compile LaTeX code on the server
					$server = new WP_Http;
				
					$server_resp = $server->request($url);		
					
					//echo $server_resp['body'];					
					
					if(!is_wp_error($server_resp)) // Check for error codes $server_resp['response']['code']
					{
			  			//echo "<br/>QL: Server OK, Parsing response.";					
						
						// Everyting is ok, parse server response
						if (ereg("^([-]?[0-9]+)\r{1}\n{1}(.+)[ ]+([-]?[0-9]+)\r?\n?(.*)$", $server_resp['body'], $regs)) 
						{					
							$status = $regs[1];
							$image_url = $regs[2];
							$image_align = $regs[3];
							$error_msg = $regs[4];
							
							//echo $status.'<br />'.$image_url.'<br />'.$image_align.'<br />'.$error_msg;
							
							if ($status == 0) // Everything is all right!
							{
								// Write formula to the cache if we allowed to
								if($ql_use_cache==1) // 
								{
									if(is_quicklatex_cache_writable($cache_path))
									{
			  							
										//echo "<br/>QL: Writing to cache.";														
										
										// Cache info file
										$handle = fopen($info_full_path, "w");
										fwrite($handle,$image_url."\n");
										fwrite($handle,$image_align."\n");						
										fclose($handle);
										
										// Cache image file
										// Download it from the server
										$image_data = $server->request($image_url);										
										if(!is_wp_error($image_data))
										{
											$handle = fopen($image_full_path, "w");
											fwrite($handle,$image_data['body']);
											fclose($handle);

											$image_url = WP_QUICKLATEX_CACHE_URL.'/'.$image_file;								
										}
									}
								}							
							}
						}
					}
				
			  } //if(!$image_url)
			 

			  if($image_url!=false) // Do we have valid image_url?
			  {
			  	if($status == 0) //No errors
				{
					$out_str = "<img src=\"$image_url\" alt=\"$formula_text\"title=\"Rendered by QuickLaTeX.com\" style=\"vertical-align: ".-$image_align."px; border: none;\"/>";
									
					if($center=='true') $out_str = '<p style="text-align:center;">'.$out_str.'</p>';
									  
				}else{
					$out_str = "<img src=\"$image_url\" alt=\"$error_msg\" title=\"$error_msg\" style=\"vertical-align: ".-$image_align."px; border: none;\"/>";								
				}
			  }
			  			  
			return $out_str ;		
		  }
	}

	// Shortcodes processing are based on wp-includes/shortcodes.php
	// Find tags [latex], [tex], [math] and replace it by formula 
	function quicklatex_parser($content)
	{
		$pattern = '(.?)\[(latex|tex|math)\b(.*?)(?:(\/))?\](?:(.+?)\[\/\2\])?(.?)';
		return preg_replace_callback('/'.$pattern.'/s', 'do_quicklatex_tags', 
			   preg_replace_callback('#\$\$(.*?)\$\$#si','do_quicklatex_dollars', $content));
	}

	// Handle $$..$$ and $$!..$$
	function do_quicklatex_dollars($m)
	{
		$formula_text =	$m[1];
		if (substr($formula_text, 0, 1) == "!") 
		{
			$attr = array('center' => 'true' ); 	  // center formula horizontally		
			$formula_text = substr($formula_text, 1); // skip the first symbol '!'
		}
		
		return quicklatex_kernel($attr,$formula_text);
	}
	
	// Parse tag parameters, formula text and call quicklatex_kernel to get formula 
	function do_quicklatex_tags($m)
	{
	        // allow [[foo]] syntax for escaping a tag
	        if ( $m[1] == '[' && $m[6] == ']' ) 
			{
	            return substr($m[0], 1, -1);
	        }

			// parse tag parameters.
	        $tag = $m[2];
	        $attr = shortcode_parse_atts($m[3]); // additionally strips slashes
			
			// Handle example tag - just output verbatim tag+parameters+formula text
			if(!empty($attr['example']))
				if($attr['example']=='true')
				{
					$m[3]=str_replace(" example=true", "", $m[3]);
					return $m[1].'<code>[latex'.$m[3].']'.$m[5].'[/latex]</code>'.$m[6];
				}
			
	        if ( isset( $m[5] ) ) {
	                // enclosing tag - extra parameter
					// call kernel function
	                return $m[1].quicklatex_kernel($attr,$m[5]).$m[6];
	        }		
	}
	
	add_filter( 'the_content',  'quicklatex_parser'); //Play with the priorities
	add_filter( 'comment_text', 'quicklatex_parser');
	add_filter( 'the_title',    'quicklatex_parser');
	add_filter( 'the_excerpt',  'quicklatex_parser');			
}

// Utilities
// Trye to create and check if cache folder is writable
// Use is_readable() to check readability
function is_quicklatex_cache_writable($path)
{
	// Check if cache directory exists
	if (false==file_exists($path))
	{
		// Try to create if it doesn't
		wp_mkdir_p($path);
	}

	return is_writable($path);
}

// Convert color to valid format. Extract only valid hex symbols, 
// add zeros so length to be of 6 symbols.
function quicklatex_sanitize_color( $color ) 
{
	$color = substr( preg_replace( '/[^0-9a-f]/i', '', $color ), 0, 6 );
	if ( 6 > $l = strlen($color) )
		$color .= str_repeat('0', 6 - $l );
	return $color;
}		

// Taken from examples from the page 
// http://jp2.php.net/manual/en/function.html-entity-decode.php
function quicklatex_unhtmlentities($string)
{
	static $trans_tbl;

	// replace numeric entities
	$string = preg_replace('~&#x([0-9a-f]+);~ei', 'quicklatex_unichr(hexdec("\\1"))', $string);
	$string = preg_replace('~&#([0-9]+);~e', 'quicklatex_unichr("\\1")', $string);

	// replace literal entities
	if (!isset($trans_tbl))
	{
		$trans_tbl = get_html_translation_table(HTML_ENTITIES,ENT_QUOTES);
		$trans_tbl = array_flip($trans_tbl);
	}
	
	return strtr($string, $trans_tbl);
}

// Miguel Perez's function
// http://jp.php.net/manual/en/function.chr.php#77911
function quicklatex_unichr($c) 
{
	if ($c <= 0x7F) {
		return chr($c);
	} else if ($c <= 0x7FF) {
		return chr(0xC0 | $c >> 6) . chr(0x80 | $c & 0x3F);
	} else if ($c <= 0xFFFF) {
		return chr(0xE0 | $c >> 12) . chr(0x80 | $c >> 6 & 0x3F)
									. chr(0x80 | $c & 0x3F);
	} else if ($c <= 0x10FFFF) {
		return chr(0xF0 | $c >> 18) . chr(0x80 | $c >> 12 & 0x3F)
									. chr(0x80 | $c >> 6 & 0x3F)
									. chr(0x80 | $c & 0x3F);
	} else {
		return false;
	}
}

// Do not put any whitespace after this tag!!!
?>