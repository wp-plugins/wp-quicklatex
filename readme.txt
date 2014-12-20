﻿=== WP QuickLaTeX ===
Contributors: advanpix, cityjin, kirkpatrick 
Donate link: http://www.holoborodko.com/pavel/quicklatex/
Tags: latex, math, TikZ, gnuplot, equations, QuickLaTeX.com, plot, SVG
Stable tag: 3.8.0
Requires at least: 2.8
Tested up to: 4.1

Advanced LaTeX plugin. Easy exchange with offline papers. Allows custom preamble, TikZ and other packages. Zoom-independent visual quality (SVG).

== Description ==

Insert formulas & graphics in the posts and comments using native LaTeX shorthands directly in the text. Inline formulas, displayed equations auto-numbering, labeling and referencing, AMS-LaTeX, <code>TikZ</code>, custom LaTeX preamble. No LaTeX installation required. Easily customizable using UI page. Actively developed and maintained. Visit <a href="http://www.holoborodko.com/pavel/quicklatex/">QuickLaTeX homepage</a> for more info. 

1.  Standard LaTeX expressions can be cut and pasted directly into WordPress posts, pages, and comments; display environments require no enclosures, other expressions require only a surrounding <code>$..$</code> or <code>\[..\]</code>.  No need for enclosing tags <code>[latex] ... [/latex]</code>.
1.  Correct vertical positioning of inline formulas relative to baseline of surrounding text. Say “NO” to jumpy equations produced by other plugins!
1.  SVG vector graphics support, so that formulas are crisp regardless of scaling in browser.
1.  (AMS)LaTeX displayed math environments support: <code>equation, align, gather, multiline, flalign, alignat,</code> etc.
1.  Automatic numbering of displayed equations. Override autonumbering with `\tag{}` LaTeX command.
1.  Equation hyper-referencing by standard LaTeX rules with `\label{}`, `\ref{}`.
1.  Custom LaTeX document preamble, allowing added <code>\usepackage{}</code> and <code>\newcommand{}</code>.
1.  <code>TikZ</code> and <code>pgfplots</code> graphics package support.
1.  Preview formulas in comments before publishing. Additionally [AJAX Comment Preview](http://blogwaffe.com/ajax-comment-preview/) plugin should be installed to enable this feature.
1.  Meaningful error messages for mistakes in LaTeX code.
1.  Precise font properties tuning: <code>size, text and background color</code>. 
1.  Easy style customization using UI or CSS file.
1.  No LaTeX installation is required. 
1.	QuickLaTeX.com automatically provides formula images, which are then cached on user's server.
1.  Administrative settings page for setting global parameters; AJAX-ified.

Just place LaTeX math expressions into your text and enable QuickLaTeX on the page by <code>[latexpage]</code> command.
WP QuickLaTeX will convert them to high-quality images and embed into post. Inline formulas will be properly aligned with the text.
Displayed equations will be auto-numbered by LaTeX rules.
To see plugin in action please visit math-pages on my blog, e.g. [Central Differences](http://www.holoborodko.com/pavel/numerical-methods/numerical-derivative/central-differences/),
[Cubature formulas for the unit disk](http://www.holoborodko.com/pavel/numerical-methods/numerical-integration/cubature-formulas-for-the-unit-disk/), [Smooth noise robust differentiators](http://www.holoborodko.com/pavel/numerical-methods/numerical-derivative/smooth-low-noise-differentiators/), etc.

== Screenshots ==

1. LaTeX - enabled post in WordPress editor.
1. Same post - published.
1. TikZ drawing inclusion (up: source code in the post editor, down: published post).
1. Admin page - Basic settings.
1. Admin page - LaTeX Syntax Sitewide & Custom preamble
1. Admin page - Image format and other system settings.
1. Debug Mode - Error message triggered by misspelled `\sqrt` command.

== Installation ==

WP QuickLaTeX is based on the free web service [QuickLaTeX.com](http://quicklatex.com/) and doesn't require 
LaTeX to be installed on user's server or hosting account. Just install the plugin and you are good to go.

1. Download WP QuickLaTeX plug-in.
2. Unzip the plugin file and upload its content to `wp-content/plugins` folder of your blog.
3. Activate WP-QuickLaTeX through the 'Plugins' menu in Wordpress.
4. Create `ql-cache` folder in `wp-content` and make it writable (by `chmod 777` or through File Manager in cPanel).

WP-QuickLaTeX stores expression images in the folder `wp-content/ql-cache`, greatly boosting performance of your site. 

== Frequently Asked Questions ==

= How do I add LaTeX to my posts? =
There are three possible ways:

* Place <code>[latexpage]</code> somewhere on the page, post, or comment. Place LaTeX expression surrounded by <code>$..$, \[..\]</code> or a display environment <code>\begin(equation}..\end{equation}</code> (or <code>align, gather, multiline, flalign, alignat</code>). 
* Enable 'Use LaTeX Syntax Sitewide'; then it is not necessary to place <code>[latexpage]</code>.
* Wrap formulas with <code>[latex] ... [/latex]</code> (this gives compatibility with previously-written "legacy" pages). 

In any case plugin will do automatic/custom equation numbering based on LaTeX rules.  
Check [plugin home page](http://www.holoborodko.com/pavel/quicklatex/) for more information on features, examples, tips & tricks.

= How can I send bug reports or request new feature? =

Please use comments on the plugin's web page [WP-QuickLaTeX](http://www.holoborodko.com/pavel/quicklatex/).
I'll do my best to help you.

== Change Log ==

= 3.8.0 =
* Added rendering to SVG.
* Fixed minor bug on a server side.

= 3.7.9 =
* Fixed critical bug in parsing. Update is strongly recommended.
* Minor changes in settings page.

= 3.7.8 =
* Diagnostics on HTTP connection errors caused by server/PHP configuration have been added.

= 3.7.7 =
* Server has been updated to include newest packages and changes.
* Settings page has been improved.
* Fixed few minor bugs in plugin.
* Improved rendering of TikZ pictures with overlays.
* Improved support of chemistry-related packages: ChemFig, myChemistry.

= 3.7.6 =
* Fixed sanitization of LaTeX source code to be placed in `alt` attribute. Now QuickLaTeX markup passes HTML validation without errors/warnings.

= 3.7.5 =
* Added compatibility with [AJAX Comment Preview](http://blogwaffe.com/ajax-comment-preview/) plugin to allow formulas preview in comments before publishing. Just update QuickLaTeX to 3.7.5 and install AJAX Comment Preview. You can test this feature on [any post on my site ](http://www.holoborodko.com/pavel/).

= 3.7.4 =
* Use `!` before `[latexpage]` tag to escape it from processing. 
* Disabled `$$ .. $$` processing on non-[latexpage] pages.

= 3.7.3 =
* Equation hyper-referencing with `\label{}`, `\ref{}`.
* Fixed bug with CSS styles for TikZ drawings. 

= 3.7.2 =
* Fixed vertical misalignment caused by CSS collisions with some "bossy" themes. 

= 3.7.1 =
* Support of native LaTeX syntax embedded directly in the posts. Copy-paste exchange with offline LaTeX papers.
* `TikZ` graphics support, including `pgfplots` and `gnuplot` commands.
* Automatic displayed equations numbering facility; `\tag{}` overrides autonumbering.
* Styles are in separate CSS file now for easy customization.
* Redesign of UI admin page. AJAX submission of options
* Improved rendering quality (PNG image output).
* Support of Thesis theme.
* Numerous small fixes and improvements.
* Server software is updated to support TexLive 2010 package.

= 3.0.0 =
* Server [QuickLaTeX.com](http://quicklatex.com/) and plugin [WP-QuickLaTeX](http://www.holoborodko.com/pavel/quicklatex/) have been completely rewritten. 
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

== Upgrade Notice == 

On the first load of the page just after plugin installation QuickLaTeX needs to re-generate cache for the page.
Please allow ample time for this process. 
