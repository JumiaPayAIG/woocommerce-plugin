
## Run the plugin locally.

You can use the provide `docker-compose` file to launch an wordpress instance.

```sh
docker-compose up -d
```

After that you must first install the wooCommerce plugin manually:


### Step 1 - Install a storefront theme

Let's install a theme to transform our website in a store. The default theme of WordPress is a blog site and is not what we want.

Dashboard > Appearance > Themes > Add new and search for "storefront".

### Step 2 - Install the woocommerce plugin

Install the woo-commerce plugin similar to the last step:

Dashboard > Plugins > Add new and search for "woocommerce"

Now, your store is completely configured to be used locally.