<?php
/*
	Plugin Name: WP QuickLaTeX
	Plugin URI: http://www.holoborodko.com/pavel/?page_id=1422
	Description: Allows user to insert mathematical formulas in the posts and comments using LaTeX language.				 Usual LaTeX plugins suffer from incorrect formula positioning relative to surrounding text producing "jumpy" equations painful for eyes and decreasing overall readability of the web article. WP QuickLaTeX is the only one plugin which solves this issue. Just wrap LaTeX code with [tex][/tex] or [latex][/latex] or [math][/math] tags. WP QuickLaTeX will convert it to high-quality image and embed into post with proper positioning so that formula and surrounding text will blend together well.
	Version: 2.2.1
	Author: Pavel Holoborodko
	Author URI: http://www.holoborodko.com/pavel/
	Copyright: Pavel Holoborodko
	License: GPL2
*/

class WP_QuickLatex{
	var $options;
	
	function init() {
			//1. ToDo: Load Options if Any

			//2. Add filters wisely (or register shortcode handlers)
			add_filter('the_title', array(&$this, 'ql_convert'));
			add_filter('the_content', array(&$this, 'ql_convert'));
			add_filter('the_excerpt', array(&$this, 'ql_convert'));			
			add_filter('comment_text', array(&$this, 'ql_convert'));
	}

	function ql_convert($content)
	{
		$regex = '#(\[|<)(tex|math|latex)(\]|>)(.*?)(\[|<)/(tex|math|latex)(\]|>)#si';	
		return preg_replace_callback($regex, array(&$this, 'ql_convert_callback'), $content);
	}
	
	function ql_convert_callback($match)
	{
		$formula_text = $match[4];
		
		// Remove any HTML tags added by WordPress
		// in the Visual Editing mode
		$formula_text = strip_tags( $formula_text );
		
		// Latex doesn't understand some fancy symbols 
		// inserted by WordPress as HTML numeric entities
		// Make sure they are not included in the formula text.
		// Add lines as needed using HTML symbol translation references:
		// http://www.htmlcodetutorial.com/characterentities_famsupp_69.html
		// http://www.ascii.cl/htmlcodes.htm
		// http://leftlogic.com/lounge/articles/entity-lookup/
		$formula_text = str_replace("&#8217","&#39",$formula_text); // single quote

		// Decode HTML entities (numeric or literal) to characters, e.g. &amp; to &.
		$formula_text = $this->unhtmlentities($formula_text);
		
		// Build URI to request server
		// Don't forget to acknowledge QuickLaTeX.com on your page
		$latex_server_url = 'http://www.quicklatex.com/latex.f?formula='.rawurlencode($formula_text);		
		$server_resp = file_get_contents($latex_server_url);

		// Parse server's response
		if (ereg("^([-]?[0-9]+)\r{1}\n{1}(.+)[ ]+([-]?[0-9]+)\r?\n?(.*)$", $server_resp, $regs)) {					
			$status = $regs[1];
			$image_url = $regs[2];
			$image_align = $regs[3];
			$error_msg = $regs[4];
		}

		// Insert picture
		if ($status == 0) // Everything is all right!
		{
			return "<img src=\"$image_url\" alt=\"$formula_text\" title=\"$formula_text\" style=\"vertical-align: ".-$image_align."px; border: none;\"/>";				
		}
		else	// Some error occured - show error picture instead of the formula
		{
			return "<img src=\"$image_url\" alt=\"$error_msg\" title=\"$error_msg\" style=\"vertical-align: ".-$image_align."px; border: none;\"/>";			
		}
	}
	
	// Taken from examples from the page 
	// http://jp2.php.net/manual/en/function.html-entity-decode.php
	function unhtmlentities($string)
	{
		static $trans_tbl;

		// replace numeric entities
		$string = preg_replace('~&#x([0-9a-f]+);~ei', '$this->unichr(hexdec("\\1"))', $string);
		$string = preg_replace('~&#([0-9]+);~e', '$this->unichr("\\1")', $string);

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
	function unichr($c) 
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
}

if ( is_admin() ) {
	// Add Settings for Admin 
	$wp_quicklatex = new WP_QuickLatex;	
} else {
	$wp_quicklatex = new WP_QuickLatex;
}

add_action('init', array( &$wp_quicklatex, 'init' ));

