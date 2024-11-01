=== XML feed for Spotter.com.mt ===
Plugin URI: https://spotter.com.mt
Description: XML feed creator for spotter.com.mt
Requires at least: 4.7
Tested up to: 5.7.0
Contributors: spottercommt
Author URI: https://spotter.com.mt
WC tested up to: 5.1.0
Tags: ecommerce, e-commerce,  wordpress ecommerce, xml, feed, spotter
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Requires PHP: 7.1
Stable tag: 1.0.5
Create spotter.com.mt XML feeds

== Description ==

With this plugin you can create XML feeds for spotter.com.mt.

== Frequently Asked Questions ==

= When in Stock Availability =
Dropdown  option "When in Stock Availability"   with options will show for all in Stock products
"Available", "1 to 3 days", "4 to 7 days", "7+ days" as availability

= If Product Attribute: Availability is used =
Dropdown  option "When in Stock Availability" value "Product Attribute: Availability" must be used
(the attribute must have slug "availability")

= If Custom Availability plugin is used =
Dropdown  option "When in Stock Availability" value "Custom Availability" must be used

= If a Product is out of Stock =
Dropdown  option "If a Product is out of Stock"  with options will
"Include as out of Stock or Upon Request" or "Exclude from feed"

= Add mpn/isbn to product =

To add mpn/isbn to the product just fill in the SKU field


= Add color =

To add the color to a product , in order to be printed on the XML feed add an attribute with Slug "color" , Type "Select" and Name of your choice

= Add manufacturer =

To add the manufacturer to a product , in order to be printed on the XML feed add an attribute with Slug "manufacturer" , Type "Select" and Name of your choice

OR

Brands plugins are supported to be shown as manufacturer.


= Add sizes =

To add the size to a product, in order to be printed on the XML feed, add an attribute with Slug "size", Type "Select" and Name of your choice.
Then is created a variable product with this attribute.

If you have stock management enabled on variations, sizes with stock lower or equal to 0 will not be shown on the feed

= Remove item from feed =

If you want to remove items from the feed, you can add a special field in the product edit area "onfeed" with value "no".

= Backorder =
If you have enabled backorder and set to notify, the product will be shown as upon order and not in stock.

If you have selected Yes, the product will be shown as available and in stock.

If you have selected no to backorder, the product will be not available.

= GTIN plugins support =

If you want to add extra gtin tag (ean, barcode, isbn) in your xml, you can enable the "Enable GTIN Feed" option in the admin panel and then, to select the preferred option of the tag and the  GTIN Source Plugin (either the name of the plugin or the name of the field)

= Split Variable products based on color attributes =
If you want to split your products based on color attribute you should check the "Split variable products by color" option

= Custom Product Id =
If you want to have a custom product id (and not the default id) you can create a special field in the product edit area i.e. "custom_product_id" or to choose from other meta fields that are available.
If that field has a value, the product will have this for id or else if it has no value that field, product will have the default id as value in the XML.
In order to disable it, just choose the -default- option.

= Exclude categories from XML =
You can add from which categories you want to exclude products from the XML Feed

= Exclude Products based on minimum price =
You can exclude products with prices that is not above a specified threshold

= Calculate taxes on product's price =
Prices should have included VAT. If you have set up your prices without taxes, choose the "Auto Calculate Price with Tax" in order to auto calculate the price with the tax.

= Product with multiple categories =
When a product has multiple categories, it will search for final categories and build the path of one of them.
If it hasn't any final category and product has only parent categories, it will build the path of one of them.
(In all paths, has been added the "Home", in order spotter.com.mt validator to not throw warning for partial path in case of parent category path)

== Changelog ==
1.0.5 Fixed a bug that caused the Select boxes to break in the admin section
1.0.4 Adding Shipping cost to XML
1.0.3 Fixing number formatting
1.0.2 Adding the ability to not include products based on minimum cost and allowing to set free shipping
1.0.1 Fixing undefined variable

= Version: 1.0.5 =
Fixed a bug that caused the Select boxes to break in the admin section

= Version: 1.0.4 =
Adding Shipping cost to XML

= Version: 1.0.3 =
Fixing number formatting

= Version: 1.0.2 =
Adding the ability to not include products based on minimum cost and allowing to set free shipping

= Version: 1.0.1 =
Bug fixing

= Version: 1.0.0 =
Initial Release



