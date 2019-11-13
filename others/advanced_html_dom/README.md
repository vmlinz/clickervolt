Update: I tested this library with php 7.0.1 - 7.0.5 as well as PHP 5.3 and up, everything seems to be working.

The goal of this project is to be a DOM-based drop-in replacement for PHP's simple html dom library.

*How To Use* - The same way as simple. If you use file/str_get_html then you don't need to change anything. If you are instantiating with `new simple_html_dom()` then you will need to change that to `new AdvancedHtmlDom()`

*What's Different* - Mostly just formatting (spaces) in the html. This is added by DOM and there's no way around it. Some non-standard selectors have been dropped but many more standard ones have been added. For example: `img[src!=foo]` was removed because it's not a valid selector. Added are things like `a + b` and `a ~ b` or even `a.foo:not(.bar)`

*What's Better*
- 10x-20x Performance increase
- Reduced memory requirement
- Support for many more css selectors

Features
* Supports full set of css pseudo selectors plus many jquery extras: `:not`, `:has`, `:contains`, `:gt`, `:lt`, `:eq`
* Use with css or xpath: `$doc->find('h3 a'), $doc->find('//h3//a')`
* Jquery-style functions replace, wrap, unwrap, before after
* Nodeset math: `$doc->find('a')->minus($doc->find('.skip_me'))`
* Lots of stuff that even BeautifulSoup and Nokogiri can't do: `$doc->search('span:lt(7):not(.foo)')`
* Lots more features that haven't been documented yet.

If you love Advanced HTML Dom please [vote for it](http://stackoverflow.com/questions/3577641/how-do-you-parse-and-process-html-xml-in-php#27539256) here!

# How to install it

Add the following code to your composer.json
```json
{
    "require": {
        "hrmatching/advanced_html_dom": "dev-master"
    },
    "autoload": {
       "classmap": ["vendor/hrmatching/advanced_html_dom/advanced_html_dom.php"]
    }
}
```