=== Lending Works ===
Contributors: Lending Works Ltd
Tags: lendingworks, Lending Works
Requires at least: 4.4
Tested up to: 5.2
Requires PHP: 5.6
Stable tag: 1.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Integrates Lending Works\' Retail Finance checkout into your WooCommerce shop - for approved retailers, let your customers apply for finance quickly and easily.

== Description ==
Lending Works matches investors directly with borrowers who want to spread the cost of their purchase. By cutting out the old financial institutions, we help your customers buy more for less. There are no hidden fees for you or customers. Just simple, flexible finance on purchases from £50 to £25,000. Lending Works isn’t like old fashioned financial services companies. We seamlessly connect people that want to invest their money with sensible borrowers who want to get a better rate on their personal loan, so both get a better deal. Rather than focusing exclusively on profits, we focus on being fair, transparent and honest. These values not only make us proud of the way we treat our customers, but also help us to set a new standard in financial services. This extension will add a new payment option, Lending Works Retail Finance, to your checkout. This allows customers to apply directly to Lending Works for finance when making a purchase, and if approved, proceed with the order in minutes. It works by connecting to our API, and launching our application checkout remotely, and synchronising order details between your database and ours. Learn more

#Why our solution is great for retailers

- Boost your sales - Offering finance can increase your sales by 17% and order value by 15%, on average. Source: Forrester
- Customers love us - From start to finish, you and your customers enjoy one seamless experience, online, in-store or over the phone. That’s why we’re rated 9.7 out of 10 on trust pilot.- Rocket science made simple - Our integration takes days, not months, and we support you all the way. See all your sales on one easy-to-use online account, or connect by API.
- Flexible finance - Split small purchases into 3 interest-free payments or Finance from 6 to 60 months on purchases from £250.00

#Account & Pricing

**You must be signed up as an approved retailer to use this extension. Additional fees apply. If you would like to set up an account, please contact the sales department here: https://www.lendingworks.co.uk/retail-finance/contact-sales. For pricing information, please visit our pricing page.**

#Features

- Adds a new payment method, Lending Works Retail Finance, to your checkout
- Stores Lending Works order data against the order in the WordPress database
- Adds the ability to manually mark the order as fulfilled from the administration area
- Adds minimum and maximum purchase values for using Retail Finance
- Allows for use of a development/testing environment

#Security Features

During the finance application, data entered into the checkout is collected and sent as an API request to the secure Lending Works servers, authenticated by an order token generated based on the API key entered by the retailer. You can read more about Lending Works\' security practices here: [https://partner-support.lendingworks.co.uk/hc/en-us/articles/360007116612-Data-security-at-Lending-Works](https://partner-support.lendingworks.co.uk/hc/en-us/articles/360007116612-Data-security-at-Lending-Works)

== Installation ==
1. Download the plugin, extract the archive and upload \"lendingworks\" folder to the \"/wp-content/plugins/\" directory.
1. Activate the plugin through the \"Plugins\" menu in WordPress.
1. In WooCommerce settings, click on the \"payment\" tab, and click the \"manage\" button next to \"Lending Works Retail Finance\" payment method.
1. Check the \"Enable LendingWorks Retail Finance\" option and input your Lending Works API Key.

== Frequently Asked Questions ==


== Screenshots ==
1. Checkout page with Lending Works payment method.
2. Lending Works loan application iFrame.
3. Payment confirmation page
4. Order paid with Lending Works detail page.
5. WooCommerce payments settings with Lending Works plugin.
6. Lending Works payment method settings page.

== Changelog ==
= 1.0.1 =
* Replace hardcoded plugin folder path by constant.
= 1.0.0 =
* Initial release.

