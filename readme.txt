=== WP QuickLaTeX ===
Contributors: advanpix
Donate link: http://www.holoborodko.com/pavel/?page_id=1422
Tags: latex, math, equations, QuickLaTeX.com
Stable tag: 3.0.0
Requires at least: 3.0.1
Tested up to: 3.0.1

Allows user to embed LaTeX math into posts and comments without compromising rendering quality.

== Description ==

Allows user to insert mathematical formulas in the posts and comments using LaTeX. 
WP-QuickLaTeX has such unique features:

1.  High rendering quality with anti-aliasing.
1.  Correct formula positioning relative to surrounding text. Say “NO” to jumpy equations produced by other plugins!
1.  Meaningful error messages for mistakes in LaTeX code (see screenshot).
1.  Amsmath multiline display math environments support: <code>gather, align, multiline, flalign, alignat,</code> etc.
1.  Precise font properties tuning: <code>size, text and background color</code>. 
1.  Formula images caching on user hosting account. 
1.  Allows to setup custom LaTeX document preamble.
1.  No LaTeX installation is required.

Just wrap LaTeX code with <code>[latex]your LaTeX code here[/latex]</code> tags. 
WP QuickLaTeX will convert it to high-quality image and embed into post properly.
To see plugin in action please visit math-pages on my blog, e.g. [Central Differences](http://www.holoborodko.com/pavel/?page_id=239),
[Cubature formulas for the unit disk](http://www.holoborodko.com/pavel/?page_id=1879), [Smooth noise robust differentiators](http://www.holoborodko.com/pavel/?page_id=245), etc.

== Screenshots ==

1. UI Settings page in WordPress dashboard.
1. Example of error message caused by mistake in LaTeX code. In this case `\sqrt` command was misspelled.

== Installation ==

WP QuickLaTeX is based on free web service [QuickLaTeX.com](http://quicklatex.com/) and doesn't require 
LaTeX to be installed on user's server or hosting account. Just install the plugin and you are good to go.

1. Download WP-QuickLaTeX plug-in.
2. Unzip the plugin file and upload its content to `wp-content/plugins` folder of your blog.
3. Activate WP-QuickLaTeX through the 'Plugins' menu in Wordpress.
4. Create `ql-cache` folder in `wp-content` and make it writable (by `chmod 777` or through File Manager in cPanel).

WP-QuickLaTeX uses `ql-cache` folder to store formula images for decreasing loading time of your pages. 

== Frequently Asked Questions ==

= How do I add LaTeX to my posts? =

Wrap LaTeX code with <code>[latex]your LaTeX code here[/latex]</code> tags.
Check [plugin home page](http://www.holoborodko.com/pavel/?page_id=1422) for more information on tag parameters, examples, tricks & tips.

= How can I send bug reports or request new feature? =

Please use comments on the plugin's web page [WP-QuickLaTeX](http://www.holoborodko.com/pavel/?page_id=1422).
I'll do my best to help you.

== Change Log ==

= 3.0.0 =
* Server [QuickLaTeX.com](http://quicklatex.com/) and plugin [WP-QuickLaTeX](http://www.holoborodko.com/pavel/?page_id=1422) have been completely rewritten. 
* Support of multiline environments.
* Font properties tuning.
* Custom LaTeX document preamble support.
* UI Settings page.

= 2.5.4 =
* Support of the latest Wordpress 3.0.

= 2.5.3 =
* Convert entities from extended HTML symbol table to ASCII for correct compilation by LaTeX.

= 2.5.2 =
* Optimize cURL options to support safe mode.

= 2.5.1 =
* Support of $$!..$$ to center formulas horizontally (*displayed* formulas).

= 2.5 =
* Show detailed error messages from LaTeX compiler.
* Increase speed by formula images caching.
* Support of $$..$$ tags.

= 2.4.1 =
* Increase speed by minor code refactoring.

= 2.4 =
* Increase speed by using cURL if allowed by the server configuration.
* Support of restricted servers with disabled `allow_url_fopen`.

= 2.3 =
* Increase speed by formula properties caching.  