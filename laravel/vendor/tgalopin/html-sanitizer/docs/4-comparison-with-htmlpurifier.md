# Comparison with HTMLPurifier

HTML sanitation is a vast domain and many library exist in PHP to tackle this problem. The most famous one is
probably HTMLPurifier. However, html-sanitizer and HTMLPurifier do not have the same goal, even if they share a
common use-case (XSS filtering).

HTMLPurifier aims at creating safe and valid HTML as close as possible to a given input. It wants to be generic
and it is therefore quite cumbersome to configure for specific needs and constraints. It is well suited to clean
full documents in which you need to keep the full structure and CSS while removing only the unsafe elements.

html-sanitizer is much stricter and does not try to fix the HTML provided. Instead, it builds
new HTML from scratch by extracting only the safe data from the input. It aims to be used in combination with a
WYSIWYG / client-side editor that output valid HTML: if the provided HTML was badly written, it means
someone is trying to do something evil and the sanitizer can simply remove the invalid parts entirely.

An important part of html-sanitizer is predictability: by being able to configure a specific list of
allowed tags and attributes, you can be certain you will only get these tags in the ouput of the sanitizer.
This allows you to prevent not only XSS attacks but also all kinds of attacks related to CSS, as you are
able to design properly each tag you allowed.
