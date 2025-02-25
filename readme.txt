=== AlphaListing ===

Contributors: eslin87
Tags: a to z, a-z, archive, listing, widget, index
Requires at least: 5.0
Requires PHP: 8.0
Tested up to: 6.7.1
Stable tag: 4.3.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Provides an A to Z index page and widget. The widget links to the index page at the appropriate letter.

This plugin is based on the original A-Z Listing by Dani Llewellyn, which is no longer maintained. 

== Description ==

Show your posts, pages, and terms alphabetically in a Rolodex-, catalogue-, or directory-style list with the AlphaListing plugin!

The plugin has a short-code for the list, and a widget so you can link to the list from anywhere on your site. If a letter doesn't have any pages then the widget will display the letter unlinked. The list page will omit the display for that letter entirely.

Show posts from any or multiple post types including the in-built posts and pages. Also supported are post-types from plugins like WooCommerce products. Alternatively, show terms like categories or tags.

== Installation ==

This section describes how to install the plugin and get it working.

= Requirements =

1. PHP 8.0 is the minimum version you should be using.
1. The plugin requires `mbstring` turned-on in your PHP installation. Without this feature the plugin might behave oddly or fail.

= Instructions =

1. Upload the alphalisting folder to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Use the `[alphalisting]` short-code or block in the block editor (gutenberg) on the page or post that you want to show the listing.
1. Add the A-Z Site Map widget to a sidebar.

== block ==

Version 4.0.0 introduces new block editor support. You should use the block in most cases, as it helps to guide you through configuring the various options.

== short-code for compatibility and PHP use ==

The plugin supplies a short-code for the full AlphaListing allowing use without modifying your theme's templates.

Basic usage is as follows:

    [alphalisting]

To specify a post-type to display instead of `page` then use, e.g. `post`:

    [alphalisting display="posts" post-type="post"]

To filter the posts by a term from a taxonomy:

    [alphalisting display="posts" post-type="post" taxonomy="category" terms="my-term-slug"]

To display pages that are direct children of the page with ID `24`:

    [alphalisting display="posts" post-type="page" parent-post="24"]

To display pages that are children of any depth below the page with ID `24`:

    [alphalisting display="posts" post-type="page" parent-post="24" get-all-children="yes"]

To show terms from a taxonomy instead of posts and pages, e.g. Terms from the `Categories` taxonomy:

    [alphalisting display="terms" taxonomy="category"]

To show terms from the `Categories` taxonomy that are direct children of the term with ID of `42`:

    [alphalisting display="terms" taxonomy="category" parent-term="42"]

To show terms from the `Categories` taxonomy that are children of any depth in the tree below the term with ID of `42`:

    [alphalisting display="terms" taxonomy="category" parent-term="42" get-all-children="yes"]

To override the alphabet used by the plugin:

    [alphalisting display="posts" alphabet="Aa,Bb,Cc,Dd,Ee,Ff,Gg,Hh,Ii,Jj,Kk,Ll,Mm,Nn,Oo,Pp,Qq,Rr,Ss,Tt,Uu,Vv,Ww,Xx,Yy,Zz"]

To add numbers to the listing:

    [alphalisting display="posts" numbers="after"]

The numbers can also be shown before the alphabet:

    [alphalisting display="posts" numbers="before"]

You can group the numbers into a single collection for all posts beginning with a numeral:

    [alphalisting numbers="after" group-numbers="yes"]

To group the alphabet letters into a range:

    [alphalisting grouping="3"]

** The arguments are all optional **

= Common options =

* `display`: specifies whether to display posts or terms from a taxonomy.
  * Default value: `posts`.
  * May only contain one value.
  * Must be set to either `posts` or `terms`.
  * Any value other than `posts` or `terms` will default to displaying posts.
* `numbers`: appends or prepends numerals to the alphabet.
  * Default value: `unset`.
  * May only contain one value.
  * Must be set to either `before` or `after`.
  * Any value other than `before` or `after` will default to **appending** numerals to the alphabet.
* `grouping`: tells the plugin if and how to group the alphabet.
  * Default value: `unset`.
  * May only contain one value.
  * Must be set to any positive number greater than `1` or the value `numbers`.
  * Any value other than a positive number or the value `numbers` will default to disabling all grouping functionality.
  * When set to a number higher than `1` the listing will group letters together into ranges.
    * For example, if you chose `3` then a Latin alphabet will group together `A`, `B`, and `C` into `A-C`. Likewise for `D-F`, `G-I` and so-on.
    * When using this setting, if numbers are also shown via the `numbers="before"` or `numbers="after"` attribute then they will be shown as a single separate group `0-9`.
  * When set to the value `numbers` it will group numerals into a single group `0-9`.
    * This requires the numbers to be displayed via the `numbers="before"` or `numbers="after"` attributes.
* `group-numbers`: tells the plugin to group all items beginning with a numeral into a single collection.
  * Default value: `false`.
  * May only contain one value.
  * Must be set to `true`, `yes`, `on`, or `1` to group items beginning with a numeral in a single collection. All other values will keep the default behaviour.
* `symbols-first`: Tells the plugin to put the unknown alphabet letter (symbols) group before the main alphabet.
  * Default value: `false`.
  * May only contain one value.
  * Must be set to `true`, `yes`, `on`, or `1` to put the symbols group before the alphabet. All other values will keep the default behaviour.
* `alphabet`: allows you to override the alphabet that the plugin uses.
  * Default value: `unset`.
  * When this attribute is not defined, the plugin will either use the untranslated default, or if [glotpress](https://translate.wordpress.org/projects/wp-plugins/alphalisting) includes a translation for your site's language as set in `Admin -> Settings -> Site Language` it will use that translation.
  * The current untranslated default is: `AÁÀÄÂaáàäâ,Bb,Cc,Dd,EÉÈËÊeéèëê,Ff,Gg,Hh,IÍÌÏÎiíìïî,Jj,Kk,Ll,Mm,Nn,OÓÒÖÔoóòöô,Pp,Qq,Rr,Ssß,Tt,UÚÙÜÛuúùüû,Vv,Ww,Xx,Yy,Zz`.
  * Accepts a single line of letters/symbols, which need to be separated via the comma character `,`.
  * Including more than one letter/symbol in each group will display posts starting with any of those under the same section.
  * The first letter/symbol in each group is used as the group's heading when displayed on your site.

= Posts options =

* `post-type`: sets the listing to show a specific post-type.
  * Default value: `page`.
  * Multiple post-types may be specified by separating with commas (`,`) e.g. `post-type="page,post"`.
  * Must be the `slug` of the post-type(s).
* `parent-post`: sets the parent post that all displayed posts must be descended from.
  * Default value: `unset`.
  * May only contain one value.
  * Must be the `ID` of the parent post.
  * Add `get-all-children="yes"` to also include all descendants of any depth below the parent post.
* `exclude-posts`: remove these posts from the list.
  * Default value: `unset`.
  * Multiple posts may be specified by separating by commas: `,`.
  * Must be the `ID` of the post(s).
* `taxonomy`: sets the taxonomy containing the terms specified in the `terms=""` option.
  * Default value: `unset`.
  * May only contain one value.
  * Must be the `slug` of the taxonomy.
* `terms`: sets the taxonomy terms for filtering posts.
  * Default value: `unset`.
  * The taxonomy must also be specified in `taxonomy`.
  * Multiple terms may be specified by separating with commas: `,`.
  * Must be the `slug` of the term(s).

= Terms options =

* `taxonomy`: sets the taxonomy to display terms from in the listing.
  * Default value: `unset`.
  * Multiple taxonomies may be specified by separating with commas: `,`.
  * Must be the `slug` of the taxonomy.
* `terms`: sets the taxonomy terms to include in the listing.
  * Default value: `unset`.
  * The taxonomy must also be specified in `taxonomy`.
  * Multiple terms may be specified by separating with commas: `,`.
  * Must be the `ID` of the term(s).
  * Cannot be used with `exclude-terms=""`.
* `exclude-terms`: sets the terms to exclude from display.
  * Default value: `unset`.
  * The taxonomy must also be specified in `taxonomy`.
  * Multiple terms may be specified by separating with commas: `,`.
  * Must be the `ID` of the term(s).
  * Cannot be used with `terms=""`.
* `parent-term`: set the parent that all displayed terms must be descended from.
  * Default value: `unset`.
  * May only contain one value.
  * Must be the `slug` of the parent term.
  * Add `get-all-children="yes"` to also include all descendants of any depth below the parent term.
* `get-all-children`: when a parent term is chosen this option is used to show all children of any depth or only direct children.
  * Default value: `false`.
  * May only contain one value.
  * Must be set to `true`, `yes`, `on`, or `1` to include all children of any depth. Any value other will use the default behaviour of only showing direct children.
* `hide-empty-terms`: hide terms that have no posts associated.
  * Default value: `false`.
  * May only contain one value.
  * Must be set to `true`, `yes`, `on`, or `1` to hide the empty terms. Any other value will use the default behaviour of showing all terms.

= Internal-use options for completeness =

** You should not need to touch these, as they are meant for internal use by the plugin only**

* `target`: the default target for a listing that doesn't show any items.
  * Default value: `unset`.
  * May only contain one value.
  * Must be set to a URL which will be used as the target for the letters' hyperlinks.
* `return`: what type of listing to show, either `listing` or `letters`.
  * Default value: `listing`.
  * May only contain one value.
  * Must be set to either `listing` to display the default view, or `letters` to show only the letters without any items (posts or terms).

== PHP (expert) ==

= Most compatible method =

The best way to use the plugin from PHP is to call `do_shortcode( '[alphalisting]' );` and save or `echo` the returned text. The parameter passed to `do_shortcode()` should be a complete AlphaListing shortcode with parameters as above.

== Multiple Column Output ==

Multiple column layout is the default on wide screens. A letter's group of items must contain at least 15 items to create two or more columns. This is to provide a more aesthetically pleasing view when a list is short with only a few items.

== Templates and Theming ==

The plugin allows the site owner, developer, or themer to provide custom templates for the AlphaListing output.

To add a template to your theme, you need a file similar to the `templates/a-z-listing.php` file in the plugin folder. Your copy needs to be placed within your theme at the theme root directory and called `a-z-listing.php` or `a-z-listing-section.php` (where `-section` is an optional top-level page slug for the section-targeting feature).

= The Loop =

The theme system this plugin implements is *very* similar to [the standard WordPress loop](https://codex.wordpress.org/The_Loop), with a few added bits.

Important functions to use in your template are as follows:

* `$a_z_query->the_letters()` prints the full alphabet, and links the letters that have posts to their section within the index page.
* `$a_z_query->have_letters()` returns true or false depending on whether there are any letters left to loop-through. This is part of the Letter Loop.
* `$a_z_query->have_items()` behaves very similarly to Core's `have_posts()` function. It is part of the Item Loop.
* `$a_z_query->the_letter()` similar to Core's `the_post()`, this will set-up the next iteration of the AlphaListing's Letter Loop. This needs to wrap-around the Item Loop.
* `$a_z_query->the_item()` similar to Core's `the_post()`, this will set-up the next iteration of the AlphaListing's Item Loop, the same way the normal WordPress Loop works. This needs to be _within_ the Letter Loop.

When you are within the Item Loop you may utilise all in-built WordPress Core post-related functions such as `the_content()`. Note that titles and permalinks have helper functions to cope with the AlphaListing showing taxonomy terms (see the next section).

I advise that you start with a copy of the default template template when customizing your own version. The supplied templates show the usage of most of the functions this plugin provides.

= Helper functions =

The plugin supports displaying taxonomy terms as though each term were a post. This means that the WordPress functions related to posts such as `the_title()` and `the_permalink()` are unreliable. We have therefore added helper functions which will return or print the correct output for the item.

These helper functions cope with the dual usage of the plugin supporting both `WP_Query`-based (returning `WP_Post` objects) and Taxonomy Terms (returning `WP_Term` objects) listings. These are:

* `$a_z_query->the_title()` - prints the current item's Title
* `$a_z_query->get_the_title()` returns the current item's Title but does not print it directly
* `$a_z_query->the_permalink()` prints the current item's Permalink
* `$a_z_query->get_the_permalink()` returns the current item's Permalink but does not print it directly

== Frequently Asked Questions ==

= Why is the list layout completely broken? =

If you are using a page-builder such as WPBakery or Elementor you need to ensure that you put the short-code into a normal text area. Placing the short-code into a preformatted text area will add `<pre>` tags around the listing output. These extra tags break the layout considerably.

= Why is my list in a single column? =

The list of items under each letter heading needs to have at least 11 items for a second column to be created. Once you hit the magic 11 items, the list will break into two columns with 6 items in the first column and 5 items in the second. When you get to 21 items a third column will be added if there is room on your page; and so-on up to a maximum of 15 columns if there is enough space, though it is unexpected that any web-page be wide enough for more than a few columns to fit. The columns will fill-up evenly once you have more than one column on the page.

= How do I show posts of a different post-type (not pages) or multiple post-types (e.g. posts AND pages)? =

This can be achieved using the short-code or PHP. In these examples the generic phrase `post-type-slug` is used to describe the concept. The default post types provided by WordPress are called "Posts" and "Pages". Their slugs are `post` and `page` respectively. You need to use these names in place of the examples (i.e. `your-post-type-slug`, `type1-slug`, and `type1-slug`).

**short-code method**

*Single post-type*

    [alphalisting post-type="your-post-type-slug"]

*Multiple post-types*

For multiple post-types just separate them with a comma.

    [alphalisting post-type="type1-slug,type2-slug"]

**PHP method**

PHP code needs to be added to your theme files, and cannot be used as post or page content in the way that a short-code can.

*Single post-type*

`
<?php
the_alphalisting( array(
    'post_type' => 'your-post-type-slug'
) );
?>
`

*Multiple post-types*

`
<?php
the_alphalisting( array(
    'post_type' => array( 'type1-slug', 'type2-slug' )
) );
?>
`

The argument to `the_alphalisting()` is an [array](https://secure.php.net/manual/en/language.types.array.php) and takes the same parameters as [WP_Query](https://codex.wordpress.org/Class_Reference/WP_Query)

The code above needs to be within a php block which is denoted by the `<?php` and `?>` pair. Depending on your theme, you might not need the opening and closing php tags shown in the above snippet; if that is the case, you are free to omit them in your code.

= How do I show posts from a specific category only? =

This can be achieved using the short-code or PHP. In these examples the generic phrase `taxonomy` and `term` are used to describe the concept. The default taxonomies provided by WordPress are called "Categories" and "Tags". Their slugs are `category` and `post_tag` respectively. Each Category and Tag are then known as "terms". You need to use the slug for each individual category or tag in place of the example slugs (i.e. `term-slug`, `term1-slug`, and `term1-slug`).

**short-code method**

*Single term*

    [alphalisting taxonomy="taxonomy-slug" terms="term-slug"]

*Multiple terms*

For multiple terms just separate them with a comma.

    [alphalisting taxonomy="taxonomy-slug" terms="term1-slug,term2-slug"]

**PHP method**

PHP code needs to be added to your theme files, and cannot be used as post or page content in the way that a short-code can.

`
<?php
the_alphalisting( array(
    'tax_query' => array(
        'taxonomy' => 'your-taxonomy-slug',
        'field' => 'slug',
        'terms' => array( 'term1-slug', 'term2-slug' )
    )
) );
?>
`

Any number of terms may be added to the `terms` [array](https://secure.php.net/manual/en/language.types.array.php), including one or none.

The argument to `the_alphalisting()` is an [array](https://secure.php.net/manual/en/language.types.array.php) and takes the same parameters as [WP_Query](https://codex.wordpress.org/Class_Reference/WP_Query)

The code above needs to be within a php block which is denoted by the `<?php` and `?>` pair. Depending on your theme, you might not need the opening and closing php tags shown in the above snippet; if that is the case, you are free to omit them in your code.

= How do I show terms from a taxonomy instead of posts? =

This can be achieved using the short-code or PHP. In these examples the generic phrase `taxonomy` and `term` are used to describe the concept. The default taxonomies provided by WordPress are called "Categories" and "Tags". Their slugs are `category` and `post_tag` respectively. You need to use the slug for the taxonomy in place of the example slugs (i.e. `taxonomy-slug`).

**short-code method**

    [alphalisting taxonomy="taxonomy-slug" display="terms"]

The taxonomy parameter takes a single taxonomy's slug, e.g. `category` or `post_tag`.

The `display="terms"` attribute is required to display taxonomy terms instead of posts.

**PHP method**

PHP code needs to be added to your theme files, and cannot be used as post or page content in the way that a short-code can.

`
<?php
the_alphalisting( 'taxonomy-slug' );
?>
`

The argument to `the_alphalisting()` is a [string](https://secure.php.net/manual/en/language.types.string.php) and contains the slug of a single taxonomy, e.g. `category` or `post_tag`.

The code above needs to be within a php block which is denoted by the `<?php` and `?>` pair. Depending on your theme, you might not need the opening and closing php tags shown in the above snippet; if that is the case, you are free to omit them in your code.

= How do I remove section targeting or limit which sections are available? =

In your theme's `functions.php` file add the following code:

`
<?php
add_filter( 'alphalisting-sections', '__return_empty_array' );
?>
`

This filter may also be used, by removing entries which are standard $post variables, to limit which top-level pages are used as section identifiers.

If there is code already in your functions.php then add just the lines between `<?php` and `?>` on the line directly after the very first instance of `<?php`.

= I am not using the short-code so the styles are not working, can I still use the in-built styles without the short-code? =

Yes you can. This needs the following code added to your theme's `functions.php` file. We purposely only display the stylesheet on pages where the short-code is active.

`
<?php
add_action( 'init', 'alphalisting_force_enable_styles', 99 );
?>
`

If there is code already in your theme's `functions.php` file then add just the lines between `<?php` and `?>` on the line directly after the very first instance of `<?php`.

The sidebar widget styling also works in a similar manner, and will also respond to the same code above to forcibly enable it.

You can add code which detects the page which the user is browsing and only enable the override on that page so that network requests are kept to a minimum (this is the same reason we detect usage of the short-code).

`
<?php
add_action( 'init', 'your_override_wrapper_function', 99 );
function your_override_wrapper_function() {
    if ( ! is_page( 'your-alphalisting-page-slug-or-ID' ) ) { // ID is numeric, slug is a string.
        return; // we don't want to run for anything except the page we're interested in.
    }
    alphalisting_force_enable_styles(); // this is the page we want, so run the function to enqueue the styles.
}
?>
`

If there is code already in your theme's `functions.php` file then add just the lines between `<?php` and `?>` on the line directly after the very first instance of `<?php`.

= How do I disable the in-built styling? =

In your theme's functions.php add the following code:

`
<?php
add_filter( 'alphalisting-add-styling', '__return_false' );
?>
`

If there is code already in your functions.php then add just the lines between `<?php` and `?>` on the line directly after the very first instance of `<?php`.

= How do I display the listing as a tabbed panel? =

In your theme's functions.php add the following code:

`
<?php
add_filter( 'alphalisting-tabify', '__return_true' );
?>
`

If there is code already in your functions.php then add just the lines between `<?php` and `?>` on the line directly after the very first instance of `<?php`.

== Screenshots ==

1. An example of the index listing page
2. The Widget is shown here

== Changelog ==

= 4.3.2 =

* Bugfix: Fix the listing order under grouped letters so that items are now correctly sorted alphabetically in ascending order.

= Previous =

This plugin is based on the original A-Z Listing by Dani Llewellyn. The last version released by Dani was 4.3.1. Starting from version 4.3.2, this plugin has diverged from the original A-Z Listing. For the full release history, including Dani's releases, refer to the `changelog.md` file.
