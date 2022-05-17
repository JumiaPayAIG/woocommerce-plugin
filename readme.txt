=== JumiaPay For Woocommerce - Payment Gateway ===
Contributors: jpaywebshop
Tags: payment request, jumiapay, woocommerce, payment gateway
Requires at least: 5.3
Tested up to: 5.9
Stable tag: 1.5.3
License: Apache-2.0 License
License URI: https://github.com/JumiaPayAIG/woocommerce-plugin/blob/master/LICENSE

JumiaPay payment gateway for WooCommerce that accepts all major payment methods available.

== Description ==

JumiaPay handles the payments so you can focus on your business!
JumiaPay checkout page enables your customers to pay for your products or services online in a safe and secure way. They use state-of-the-art fraud detection technology to keep you safe and handle all aspects of PCI DSS compliance at no extra cost to you.
The checkout solution is live in Egypt and soon in more countries: Kenya, Nigeria, Morocco, Ghana, Uganda, Tunisia and Ivory Coast and accepts:

- Nigeria: Mastercard, Visa and Verve 
- Egypt: Mastercard, Visa, Meeza Cards and Fawry 
- Kenya: Mastercard, Visa, Air-tel and MPESA 
- Marocco: Mastercard, Visa, CMI 
- Uganda: Mastercard and Visa
- Ghana: Mastercard and Visa
- Ivory Coast: Mastercard and Visa
- Tunisia: Mastercard and Visa

What do you need to start using this WooCommerce plugin?
1.  You need to open a JumiaPay Business account ([here](https://business-pay.jumia.com.eg/), if you are in Egypt.). Then, follow JumiaPay instructions on how to activate your business and get your credentials to proceed with the plugin installation
2.  You need to instal and activate the WooCommerce plugin for JumiaPay on your WordPress website

Plugin Features:
-   Easily integrate JumiaPay WooCommerce payment gateway on your website. Our teams will always be there to help!
-   Accept all popular and trusted payment methods available in your countries
-   Fast checkout for your consumers with tokenized payment methods
-   State-of-the-art fraud detection technology to keep you safe
-   PCI DSS compliant environment
-   Instant customer refunds
-   Sandbox environment for testing


Importante Note:
This plugin is meant to be used by businesses in Egypt. Therefore, you will need to set your stores in the country you operate. The local currency needs to be configured in the ‘General’ tab on the WooCommerce settings page.

== Installation ==

= Minimum requirements: =
*   WordPress 5.3 or higher

= Automatic Installation =
-   Login to your WordPress Admin Area
-   Click on ‘Plugins’ > ‘Add New’
-   Search for ‘JumiaPay WooCommerce Payment Gateway’ in the search bar
-   From the search result you will see ‘JumiaPay WooCommerce Payment Gateway’, click on ‘Install Now’ button
-   After the installation is complete, activate the plugin
-   Proceed with the configurations described below


= Manual Installation =
-   Download JumiaPay plugin zip file from ...
-   Login to your WordPress Admin Area
-   Click on ‘Plugins’ > ‘Add New’ > ‘Upload Plugin’
-   Click on ‘Choose File’ to select the plugin zip file from your computer. After the file is chosen, click on the ‘Install Now’ button
-   Once the plugin is successfully installed, click on the ‘Activate Plugin’ button
-   Proceed with the configurations described below

= Configuration =
-   Go to ‘WooCommerce’ > ‘Settings’ and click on ‘General’ from the top tab.
-   On ‘Currency options’ set the Currency with the official currency of the country where you’re selling (eg. ‘Nigerian naira’ for Nigeria and ‘Egyptian pound’ for Egypt) and the Number of decimals as ‘2’
-   Click on the ‘Save Changes’ button
-   Then click on ‘Payments’ from the top tab and click on ‘JumiaPay’ from the available list in order to configure this payment option:
-   Enable/Disable: check the box to enable JumiaPay payment option on checkout
-   Environment: you may select ‘Sandbox’ or ‘Live’. Select ‘Sandbox’ to test payments done through JumiaPay before going live. Once you are ready to accept real payments on your site, select ‘Live’
-   On ‘Live Settings’:
--   Country List: Select the country where you’re operating. This plugin is meant to be used in Nigeria or Egypt
--   Shop API Key: Access your live JumiaPay Business account, click on the ‘Shop Configurations’ module, copy the Shop key and paste it here
--   Merchant API Key: API key that will identify your Business. This key will be provided by JumiaPay team as soon as you create your JumiaPay Business account follow the onboarding steps and your business gets approved
-   On ‘Sandbox Settings’:
--   Country List: Select the country where you’re operating. This plugin is meant to be used in Nigeria or Egypt
--  Shop API Key: Access your sandbox JumiaPay Business account, click on the ‘Shop Configurations’ module, copy the Shop key and paste it here
--  Merchant API Key: API key that will identify your Business. This key will be provided by JumiaPay team as soon as you create your JumiaPay Business account, follow the onboarding steps and your business gets approved
-   Click on the ‘Save Changes’ button
-   On the ‘Settings’ on the left hand side, go to ‘Permalinks’ and on ‘Common Settings’ pick the Post name option. This option is the most commonly used to improve your website visibility for relevant web searches

== Frequently Asked Questions ==

= How is the checkout experience for the customer? =
On your website checkout, the customer will be able to select the option ‘JumiaPay’ to proceed with the order payment. After selecting ‘JumiaPay’ and placing an order, your customer will be redirected to JumiaPay where he can login or create an account. Once login is made, he will see the JumiaPay checkout where he can select the desired payment method, insert his payment details and pay.

Once your customer completes the payment, he will be redirected to your website.

= Is there a sandbox mode for testing? =

Yes. JumiaPay has a sandbox available so you can test the plugin integration and monitor fictitious transactions.

For more information, check out our documentation.

To configure and use the sandbox, please follow the instructions provided on the Installation Guide. You may choose to connect in ‘Live’ or ‘Sandbox’ mode and switch between these modes whenever you want.

= Where can I find documentation on JumiaPay? =

You may find our documentation with detailed steps on how to install and configure the JumiaPay WooCommerce Payment Gateway and on how to monitor and take actions over your received payments.

= Can I refund a customer via JumiaPay? =

Yes. Check our documentation for detailed information.