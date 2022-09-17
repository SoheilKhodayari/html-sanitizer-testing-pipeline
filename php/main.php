<?php

/*
	Copyright (C) 2022  Soheil Khodayari, CISPA
	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU Affero General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU Affero General Public License for more details.
	You should have received a copy of the GNU Affero General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.


	Description:
	------------
	The main program that runs the php sanitizor testing pipeline


	Usage:
	------------
	$ php main.py /path/to/input/markups/file /path/to/output/file

*/


// ----------------------------------------------------------------------------------------- // 
//  Preparation of input and output vars
// ----------------------------------------------------------------------------------------- // 

ini_set('memory_limit', '8192M');

// process CLI arguments
$cli_params = array_fill(0, 3, null);
 
for($i = 1; $i < $argc; $i++) {
    $cli_params[$i - 1] = $argv[$i];
}

$input_file_path_name = $cli_params[0];
$output_file_path_name = $cli_params[1];

if(is_null($input_file_path_name)){
	$input_file_path_name = './../markups.txt';
}
if(is_null($output_file_path_name)){
	$output_file_path_name = './outputs/results.json';
}

$lines = file($input_file_path_name, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) or die("Unable to open input file!");



// ----------------------------------------------------------------------------------------- // 
// Sanitizers & dependency imports
// ----------------------------------------------------------------------------------------- // 


require_once 'vendor/autoload.php';
require_once 'vendor/ezyang/htmlpurifier/library/HTMLPurifier.auto.php';

// https://www.math.ucla.edu/sites/all/modules/htmLawed/htmLawed/htmLawed_README.htm#s3.2
require_once 'libs/htmLawed/htmLawed.php';




// ----------------------------------------------------------------------------------------- // 
// ezyang HTML Purifier
// ----------------------------------------------------------------------------------------- // 

$purifyConfig = HTMLPurifier_HTML5Config::createDefault();
$purifier = new HTMLPurifier($purifyConfig);



// ----------------------------------------------------------------------------------------- // 
//	HTML Sanitizer
// ----------------------------------------------------------------------------------------- // 

$htmlSanitizor = HtmlSanitizer\Sanitizer::create(['extensions' => ['basic']]);


// ----------------------------------------------------------------------------------------- // 
//	Typo3 Sanitizer
// ----------------------------------------------------------------------------------------- // 

use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig as SymphonySanitizerConfig;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer as SymphonySanitizer;

// By default, an element not added to the allowed or blocked elements
// will be dropped, including its children
$symphonyConfig = (new SymphonySanitizerConfig())
    // Allow "safe" elements and attributes. All scripts will be removed
    // as well as other dangerous behaviors like CSS injection
    ->allowSafeElements()

    // Allow all static elements and attributes from the W3C Sanitizer API
    // standard. All scripts will be removed but the output may still contain
    // other dangerous behaviors like CSS injection (click-jacking), CSS
    // expressions, ...
    ->allowStaticElements()

    // Allow the "div" element and no attribute can be on it
    ->allowElement('div')

    // Allow the "a" element, and the "title" attribute to be on it
    ->allowElement('a', ['title'])

    // Allow the "span" element, and any attribute from the Sanitizer API is allowed
    // (see https://wicg.github.io/sanitizer-api/#default-configuration)
    ->allowElement('span', '*')

    // Block the "section" element: this element will be removed but
    // its children will be retained
    ->blockElement('section')

    // Drop the "div" element: this element will be removed, including its children
    ->dropElement('div')

    // Allow the attribute "title" on the "div" element
    ->allowAttribute('title', ['div'])

    // Allow the attribute "data-custom-attr" on all currently allowed elements
    ->allowAttribute('data-custom-attr', '*')

    // Drop the "data-custom-attr" attribute from the "div" element:
    // this attribute will be removed
    ->dropAttribute('data-custom-attr', ['div'])

    // Drop the "data-custom-attr" attribute from all elements:
    // this attribute will be removed
    ->dropAttribute('data-custom-attr', '*')

    // Forcefully set the value of all "rel" attributes on "a"
    // elements to "noopener noreferrer"
    ->forceAttribute('a', 'rel', 'noopener noreferrer')

    // Transform all HTTP schemes to HTTPS
    ->forceHttpsUrls()

    // Configure which schemes are allowed in links (others will be dropped)
    ->allowLinkSchemes(['https', 'http', 'mailto'])

    // Configure which hosts are allowed in links (by default all are allowed)
    ->allowLinkHosts(['symfony.com'])

    // Allow relative URL in links (by default they are dropped)
    ->allowRelativeLinks()

    // Configure which schemes are allowed in img/audio/video/iframe (others will be dropped)
    ->allowMediaSchemes(['https', 'http'])

    // Configure which hosts are allowed in img/audio/video/iframe (by default all are allowed)
    ->allowMediaHosts(['symfony.com', 'example.com'])

    // Allow relative URL in img/audio/video/iframe (by default they are dropped)
    ->allowRelativeMedias();



// Sanitize a given string, using the configuration provided and in the
// "body" context (tags only allowed in <head> will be removed)
$symphonySanitizor = new SymphonySanitizer($symphonyConfig);




// ----------------------------------------------------------------------------------------- // 
//  HTMLawed Sanitizer
// ----------------------------------------------------------------------------------------- // 

$htmLawedConfig = array("balance" => 1, "unique_ids" => 0);
$htmLawedSpec= '*=href, nomodule, time, method, ontoggle, allowfullscreen, controls, loop, src, script, onkeyup, ondrag, border, as, input, pattern, rel, rows, sizes, onblur, onchange, meta, async, cols, source, alt, style, oncontextmenu, shape, enterkeyhint, textareaaccesskey, media, novalidate, hreflang, hidden, oncanplaythrough, formnovalidate, download, onerror, muted, imagesrcset, action, rowspan, ondragend, ondragstart, minlength, mayscript, defer, ins, scope, crossorigin, default, oninput, max, srcset, control, size, progress, onsuspend, itemscope, dirname, onplay, srcdoc, name, onratechange, loading, onseeking, select, is, onunload, li, preload, onoffline, onmouseup, open, onended, option, a, target, onmouseout, hspace, translate, oncopy, enctype, lang, onreset, sandbox, label, itemref, formenctype, headers, required, start, accept, onpause, low, charset, meter, object, integrity, param, disabled, ononline, del, map, onhashchange, audio, onmousedown, onselect, span, selected, imagesizes, base, Global Attributes, dir, onpageshow, onwheel, autocomplete, contenteditable, output, data-*, id, kind, name, textarea, onafterprint, nonce, srclang, frameBorder, for, onbeforeunload, poster, iframe, onbeforeprint, details, playsinline, optgroup, colgroup, onkeypress, onpaste, onkeydown, maxlength, draggable, id, oncuechange, wrap, ondurationchange, referrerpolicy, color, menu, ondragenter, oncanplay, itemprop, datetime, ondblclick, reversed, archive, formaction, noResize, onscroll, area, onsubmit, video, vspace, codebase, ondragleave, inputmode, scrolling, link, col, width, multiple, align, data, ismap, ol, onmouseover, placeholder, form, readonly, type, formtarget, autofocus, onplaying, high, onemptied, onmousemove, onseeked, http-equiv, autoplay, embed, onabort, body, onresize, onloadeddata, onvolumechange, oncut, step, ondrop, onwaiting, cite, ontimeupdate, img, marginHeight, itemtype, spellcheck, onsearch, onfocus, allow, oninvalid, onprogress, min, ondragover, marginWidth, longDesc, checked, tabindex, onmousewheel, fieldset, list, formmethod, onload, value, onclick, class, onloadstart, itemid, title, button, td, colspan, accept-charset, content, coords, onstalled, height, accesskey, optimum, th, code, onloadedmetadata, autocapitalize, usemap, widt, track;';


// ----------------------------------------------------------------------------------------- // 
//	Typo3 Sanitizer
// ----------------------------------------------------------------------------------------- // 

use TYPO3\HtmlSanitizer\Behavior;
use TYPO3\HtmlSanitizer\Sanitizer as TYPO3Sanitizer;
use TYPO3\HtmlSanitizer\Visitor\CommonVisitor;

require_once 'vendor/autoload.php';

$commonAttrs = [
    new Behavior\Attr('id'),
    new Behavior\Attr('class'),
    new Behavior\Attr('data-', Behavior\Attr::NAME_PREFIX),
];
$hrefAttr = (new Behavior\Attr('href'))
    ->addValues(new Behavior\RegExpAttrValue('#^https?://#'));

// attention: only `Behavior` implementation uses immutability
// (invoking `withFlags()` or `withTags()` returns new instance)
$behavior = (new Behavior())
    ->withFlags(Behavior::ENCODE_INVALID_TAG)
    ->withTags(
        (new Behavior\Tag('div', Behavior\Tag::ALLOW_CHILDREN))
            ->addAttrs(...$commonAttrs),
        (new Behavior\Tag('a', Behavior\Tag::ALLOW_CHILDREN))
            ->addAttrs($hrefAttr, ...$commonAttrs),
        (new Behavior\Tag('br'))
    );

$visitors = [new CommonVisitor($behavior)];
$typo3Sanitizer = new TYPO3Sanitizer(...$visitors);




// ----------------------------------------------------------------------------------------- // 
//	Testing and Output
// ----------------------------------------------------------------------------------------- // 


$results = array(
	"symphony" => array(),
	"htmlpurifier" => array(),
	"htmlawed" => array(),
	"htmlsanitizor" => array(),
	"typo3" => array()
);

foreach($lines as $idx => $payload){

	$output = $symphonySanitizor->sanitize($payload);
	array_push($results["symphony"], 
        array(
            "input" => $payload,
            "output" => $output
        )
    );

	$output = $purifier->purify($payload);
	array_push($results["htmlpurifier"],
        array(
            "input" => $payload,
            "output" => $output
        )
    );
	
	$output = htmLawed($payload, $htmLawedConfig, $htmLawedSpec);
	array_push($results["htmlawed"],
        array(
            "input" => $payload,
            "output" => $output
        )
    );

	$output = $htmlSanitizor->sanitize($payload);
	array_push($results["htmlsanitizor"],
        array(
            "input" => $payload,
            "output" => $output
        )
    );

	$output = $typo3Sanitizer->sanitize($payload);
	array_push($results["typo3"],
        array(
            "input" => $payload,
            "output" => $output
        )
    );

}

// write to output
file_put_contents($output_file_path_name, json_encode($results, JSON_PRETTY_PRINT));


?>