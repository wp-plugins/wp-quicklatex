<?php
/*
	Plugin Name: WP QuickLaTeX
	Plugin URI: http://www.holoborodko.com/pavel/?page_id=1422
	Description: Allows user to insert mathematical formulas in the posts and comments using LaTeX.				 Usual LaTeX plugins suffer from incorrect formula positioning relative to surrounding text producing "jumpy" equations painful for eyes and decreasing overall readability of the web article. WP QuickLaTeX is the only one plugin which solves this issue. Just wrap LaTeX code with [tex][/tex] or [latex][/latex] or [math][/math] tags. WP QuickLaTeX will convert it to high-quality image and embed into post with proper positioning so that formula and surrounding text will blend together well.
	Version: 2.5
	Author: Pavel Holoborodko
	Author URI: http://www.holoborodko.com/pavel/
	Copyright: Pavel Holoborodko
	License: GPL2
*/

if ( !defined('ABSPATH') ) exit;

class WP_QuickLatex{
	var $options;
	var $fetch_remote_type; // 0 - none, 1 - cURL, 2 - file_get_contents, 3 - fsockopen
	var $fetch_errstr;
	var $fetch_errno;
	
	function init() {
			//1. ToDo: Load Options if Any

			//2. Detect fetch_remote_type
			$this->fetch_remote_type = 0;
			if(function_exists('curl_init')){ 
				$this->fetch_remote_type = 1;
			}elseif (ini_get('allow_url_fopen') == '1' && function_exists('file_get_contents')){
				$this->fetch_remote_type = 2;
			}elseif(function_exists('fsockopen')){
				$this->fetch_remote_type = 3;			
			}

			//3. Add filters wisely (or register shortcode handlers)
			add_filter('the_content', array(&$this, 'ql_convert'));
			add_filter('comment_text', array(&$this, 'ql_convert'));			
			add_filter('the_title', array(&$this, 'ql_convert'));
			add_filter('the_excerpt', array(&$this, 'ql_convert'));			
	}

	function ql_convert($content)
	{
		return preg_replace_callback('#(\[|<)(tex|math|latex)(\]|>)(.*?)(\[|<)/(tex|math|latex)(\]|>)#si', 
							  array(&$this, 'ql_callback_tags'),
							  preg_replace_callback('#\$\$(.*?)\$\$#si', array(&$this, 'ql_callback_dollars'), $content));
	}

	function ql_callback_tags($match)
	{
		return $this->ql_kernel($match[4]);
	}

	function ql_callback_dollars($match)
	{
		return $this->ql_kernel($match[1]);
	}
	
	function ql_kernel($formula)
	{
		$formula_text = $formula;

		$formula_hash = md5($formula_text);

		// The script will automatically create a folder called backup-db in wp-content 
		// folder if that folder is writable. If it is not created, please create it and CHMOD it to 777.
		$cache_dir = 'wp-content/ql-cache';
		$cache_path = ABSPATH.$cache_dir; 

		$info_file  = 'quicklatex-'.$formula_hash.'.txt';
		$image_file = 'quicklatex-'.$formula_hash.'.gif';		
		$info_full_path  = $cache_path.'/'.$info_file;	
		$image_full_path = $cache_path.'/'.$image_file;	
		
 		if (!is_file($info_full_path))
		{
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
				$server_resp = $this->file_get_contents_flex('www.quicklatex.com','/latex.f?formula='.rawurlencode($formula_text).'?remhost='.get_option('siteurl'));											

				if($server_resp==false){
					return $this->fetch_errstr;
				}

				// Parse server's response
				if (ereg("^([-]?[0-9]+)\r{1}\n{1}(.+)[ ]+([-]?[0-9]+)\r?\n?(.*)$", $server_resp, $regs)) 
				{					
					$status = $regs[1];
					$image_url = $regs[2];
					$image_align = $regs[3];
					$error_msg = $regs[4];
					
					if ($status == 0) // Everything is all right!
					{
						if(file_exists($cache_path) && is_writable($cache_path))
						{
							// Cache info file
							$handle = fopen($info_full_path, "w");
							fwrite($handle,$image_url."\n");
							fwrite($handle,$image_align."\n");						
							fclose($handle);
							
							// Cache image file
							$url = parse_url($image_url);
							$image_data = $this->file_get_contents_flex($url['host'],$url['path']);
							if($image_data != false)
							{
								$handle = fopen($image_full_path, "w");
								fwrite($handle,$image_data);
								fclose($handle);
							}
						}
					}
				}
		}else{
				// Use cached files		
		
				// Read info file
				$handle = fopen($info_full_path, "r");
				$image_url = rtrim(fgets($handle),"\n");
				$image_align = rtrim(fgets($handle),"\n");				
				fclose($handle);

				// Use local image for formula if available
				if(is_file($image_full_path))
				{
					$image_url = get_bloginfo('wpurl').'/'.$cache_dir.'/'.$image_file;				
				}else{
					// Try to cache it otherwise (if only info file was cached previousely before 2.5 version)
					$url = parse_url($image_url);
					$image_data = $this->file_get_contents_flex($url['host'],$url['path']);
					if($image_data != false)
						{
							$handle = fopen($image_full_path, "w");
							fwrite($handle,$image_data);
							fclose($handle);
							
							$image_url = get_bloginfo('wpurl').'/'.$cache_dir.'/'.$image_file;											
						}
				}

				$status = 0;
		}

		// Insert picture
		if ($status == 0) // Everything is all right!
		{
			return "<img src=\"$image_url\" alt=\"$formula_text\" title=\"$formula_text\" style=\"vertical-align: ".-$image_align."px; border: none;\"/>";				
		}
		else	// Some error occured - show error picture instead of the formula
		{
			return "<img src=\"$image_url\" alt=\"$error_msg\" title=\"$error_msg\" style=\"vertical-align: -12px; border: none;\"/>";			
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
	
	//	Returns remote file contents if ok, false otrherwise 
	function file_get_contents_flex($host, $query)
	{
		$url = "http://$host$query";		

		$this->fetch_errstr = '';
		$this->fetch_errno = 0;

		if($this->fetch_remote_type == 1 ){
			//1. cURL
			if($ch = curl_init()){
				
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);		
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_CRLF, 0);
				
				$data = curl_exec($ch);
				
				if($data==false){
					$state = curl_getinfo($ch);		
					$this->fetch_errstr = 'curl error: '.curl_error($ch);
					$this->fetch_errno = $state['http_code'];
				}
				
				curl_close($ch);	
				return $data;		
			}else{
				$this->fetch_errstr = 'curl error: curl_init() failed';
				return false;
			} 		
			
		}elseif($this->fetch_remote_type == 2){
			
			//2. file_get_contents			
			if ($data = file_get_contents($url)){
				return $data;
			}else{
				$this->fetch_errstr = 'file_get_contents failed';
				return false;
			}
			
		}elseif($this->fetch_remote_type == 3){
			//3. fsockopen		
			$fp = fsockopen($host, '80', $this->fetch_errno, $this->fetch_errstr, 10);
			if (!$fp) {
				$this->fetch_errstr  = 'fsockopen error: '.$this->fetch_errstr;
				return false;
			} else {
				$out = "GET $query HTTP/1.0\r\n";
				$out .= "Host: $host\r\n";
				$out .= "User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.9.0.11) Gecko/2009060215 Firefox/3.0.11 GTB5 (.NET CLR 3.5.30729)\r\n";
				$out .= "Accept: */*\r\n";		
				$out .= "Accept-Language: en-us,en;q=0.5\r\n";
				$out .= "Accept-Encoding: gzip,deflate\r\n";
				$out .= "Accept-Charset: windows-1251,utf-8;q=0.7,*;q=0.7\r\n";
				$out .= "Connection: keep-alive\r\n";
				$out .= "Referer: http://$host\r\n";
				$out .= "\r\n";
				
				fputs($fp, $out);

				$buffer = '';

				while(!feof($fp)) $buffer .= fgets($fp, 1024); 			
				
				fclose($fp);
				
				// strip the headers
				$pos  = strpos($buffer, "\r\n\r\n");
				$data = substr($buffer, $pos + 4);
				
				return $data;
		   }				
		}else{
			$this->fetch_errstr  = 'Unknown fetch_remote_type';
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

