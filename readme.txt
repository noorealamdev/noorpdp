=== NoorPDP ===
Contributors: noorealam
Tags: woocommerce, product page, page builder, gutenberg, blocks, product design, product template, product layout, single product, woocommerce blocks
Requires at least: 6.6
Tested up to: 7.0
Requires PHP: 7.4
Requires Plugins: woocommerce
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A free WooCommerce product page builder — design your single product page visually with Gutenberg blocks and premade templates.

== Description ==

**NoorPDP is a WooCommerce product page builder** that lets you redesign the single product page visually, in the standard block editor, without touching a theme file:

* Product Title, Rating, Price, Short Description, Add to Cart, Meta, Gallery, Tabs and Breadcrumbs blocks — each a thin wrapper around WooCommerce's own `woocommerce_template_single_*()` functions, so variations, stock handling and reviews keep working exactly as WooCommerce implements them
* Product Grid and Related Products blocks for latest/related/upsell product listings
* Container, Heading, Button, Accordion and Tabs layout blocks for arranging everything
* Product Layouts — full page arrangements built in the standard block editor, so it works on any theme, not just block themes
* Assign a Layout per product, per product category, or as the site-wide default; unconfigured products keep rendering exactly as vanilla WooCommerce always has
* An editor Template Library with premade full layouts and drop-in sections (FAQ accordion, trust badges, feature table)

Everything above is free — no license, upsell, or locked feature anywhere in the plugin.

== Installation ==

1. Make sure WooCommerce is installed and active.
2. Upload the plugin to `/wp-content/plugins/noorpdp`.
3. Run `npm install && npm run build` inside the plugin directory (development installs only).
4. Activate the plugin through the Plugins screen.
5. Go to Products → Add New Product Layout, build a layout, then assign it to a product from the "Product Page Template" box on the product edit screen.

== Frequently Asked Questions ==

= Do I need to know how to code? =

No. NoorPDP adds blocks to the standard WordPress block editor — you build your product page the same way you'd build any other page, with no PHP or template files to edit.

= Will this work with my theme? =

Yes. A Layout renders through a template override that keeps every WooCommerce hook (sale badges, "recently viewed", structured data, etc.) firing in its normal position, so it works with classic and block themes alike — not just block themes.

= Does NoorPDP replace WooCommerce's own product page logic? =

No. Every block is a thin wrapper around WooCommerce's own rendering functions, so variations, stock handling, reviews and add-to-cart behave exactly as WooCommerce implements them — you're only changing the layout, not the underlying logic.

= What happens to my product pages if I deactivate NoorPDP? =

They fall back to whatever your theme/WooCommerce would normally show. Nothing is deleted, and your Layouts and assignments are still there if you reactivate the plugin later.

= Can I use a different design for one product, a whole category, and everything else? =

Yes. NoorPDP checks a per-product choice first, then a per-category default, then your site-wide default — whichever matches first wins.

= Is there a paid or "Pro" version? =

No. Every block, Layout and template in NoorPDP is free — there's no upsell, license, or locked feature anywhere in the plugin.

= Where do I find the premade templates? =

Open a Product Layout in the block editor and click the "NoorPDP" button in the toolbar to browse the Template Library — full page layouts and drop-in sections like FAQ accordions, trust badges and feature tables.

== External services ==

This plugin includes an optional "Cloud Templates" source that can fetch additional premade templates from a remote API. It is **disabled by default** and only activates if a site or another plugin explicitly defines the `NOORPDP_CLOUD_URL` constant (or hooks the `noorpdp/cloud_api_url` filter) with a real endpoint URL — out of the box, this plugin makes no external requests at all.

When enabled, the plugin sends a single `GET` request to the configured URL (no personal or site-identifying data included beyond standard HTTP headers) to retrieve a JSON list of templates, and caches the response in a transient for 12 hours.

== Source Code ==

The JavaScript in the `build/` directory is compiled from the human-readable source in `src/`. Full source, including the build tooling, is available at https://github.com/noorealamdev/noorpdp. To regenerate the compiled files:

`npm install && npm run build`

== Changelog ==

= 1.0.0 =
* Initial release: WooCommerce product blocks, Product Layouts, per-product/category/site-wide template assignment, starter layouts and sections.
