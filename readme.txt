=== NoorPDP ===
Contributors: noorealam
Tags: woocommerce, product page, page builder, gutenberg, single product
Requires at least: 6.6
Tested up to: 7.0
Requires PHP: 7.4
Requires Plugins: woocommerce
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Design beautiful WooCommerce product pages visually with Gutenberg blocks and premade templates — 100% free, no upsells.

== Description ==

Tired of every product page on your store looking like every other default WooCommerce store? **NoorPDP turns the standard WordPress block editor into a real WooCommerce product page builder** — not a bloated, do-everything page builder with a learning curve of its own, just the editor you already know, pointed at the page that actually sells your product.

Build a Layout visually, assign it to one product, a whole category, or your entire catalog, and watch your product pages finally look like *your* brand instead of a theme default. No page-builder plugin to learn. No theme files to edit. No code.

**Built on WooCommerce, not around it**

* Product Title, Rating, Price, Short Description, Add to Cart, Meta, Gallery, Tabs and Breadcrumbs blocks — each a thin wrapper around WooCommerce's own `woocommerce_template_single_*()` functions, so variations, stock handling and reviews keep working exactly as WooCommerce implements them
* The Product Gallery block renders WooCommerce's own native gallery template, so zoom, lightbox and per-variation gallery swapping work exactly as they would on a theme's default product page — wherever the active theme supports them
* Works with classic *and* block themes — a Layout renders through a template override that keeps every WooCommerce hook firing in its normal position, so nothing else on your site breaks

**Real design flexibility**

* Product Grid and Related Products blocks for latest/related/upsell product listings
* Container, Heading, Button, Accordion and Tabs layout blocks for arranging everything exactly how you want it
* Connect a Paragraph, Heading, Image or Button block directly to a product's custom field — including an ACF field, when ACF is active — using WordPress's own Block Bindings "Connect to custom field" picker
* Shortcodes placed inside a Layout's text content render normally, the same as they would in a theme's regular content
* An editor Template Library with premade full layouts and drop-in sections (FAQ accordion, trust badges, feature table) to start from instead of a blank canvas

**Granular control, zero risk**

* Assign a Layout per product, per product category, or as the site-wide default; unconfigured products keep rendering exactly as vanilla WooCommerce always has
* Deactivate any time — nothing is deleted, your product data is untouched, and pages simply fall back to your theme's default

*"NoorPDP has given me considerably more control over my product pages without requiring me to move to a heavyweight page builder... my product pages now feel much more like my own site, and the builder itself has been straightforward to work with."* — a 5-star review from a real store owner

Every block, every Layout, every premade template above is free — no license, no upsell, no locked feature anywhere in the plugin, and none planned.

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

= Can I show a custom field, or an ACF field, on the product page? =

Yes. Select a Paragraph, Heading, Image or Button block, open its block bindings picker ("Connect to custom field" — the small plug icon in the block toolbar), and choose "NoorPDP: Product Field". It lists the current product's own custom fields, ACF fields included when ACF is active, and keeps that block's content in sync with whichever product the Layout is showing.

== External services ==

This plugin includes an optional "Cloud Templates" source that can fetch additional premade templates from a remote API. It is **disabled by default** and only activates if a site or another plugin explicitly defines the `NOORPDP_CLOUD_URL` constant (or hooks the `noorpdp/cloud_api_url` filter) with a real endpoint URL — out of the box, this plugin makes no external requests at all.

When enabled, the plugin sends a single `GET` request to the configured URL (no personal or site-identifying data included beyond standard HTTP headers) to retrieve a JSON list of templates, and caches the response in a transient for 12 hours.

== Source Code ==

The JavaScript in the `build/` directory is compiled from the human-readable source in `src/`. Full source, including the build tooling, is available at https://github.com/noorealamdev/noorpdp. To regenerate the compiled files:

`npm install && npm run build`

== Changelog ==

= 1.1.0 =
* Added: native WordPress Block Bindings support for product custom fields — connect a core Paragraph, Heading, Image or Button block straight to a product field (ACF fields included, when ACF is active) from the block's own "Connect to custom field" picker.
* Changed: the Product Gallery block now renders WooCommerce's own native gallery template instead of a custom carousel, so zoom, lightbox and per-variation gallery swapping behave exactly as they would on a theme's default product page wherever the active theme supports them.
* Fixed: a shortcode placed inside a Layout's text content (e.g. a Heading or Container block) now renders instead of showing as literal `[shortcode]` text.
* Fixed: every bundled Template Library layout referenced a placeholder image on an external URL that no longer resolves; premade templates now ship with their own bundled images.

= 1.0.0 =
* Initial release: WooCommerce product blocks, Product Layouts, per-product/category/site-wide template assignment, starter layouts and sections.
