# Monevibe Affiliate Tracker for WooCommerce

A lightweight WordPress plugin designed for Lifetime affiliate conversion tracking and seamless integration with the **Monevibe** platform.

## How It Works
1. The plugin listens to incoming traffic. If the URL contains the `?clickid=...` parameter, it captures it.
2. It stores the ID in the user's browser cookies for **1 year** and syncs it with the WooCommerce session.
3. Upon order creation, order metadata (`click_id`, total amount, currency) is automatically sent to the Monevibe API.
4. When an order status changes to `completed`, `cancelled`, or `refunded`, the plugin fires a webhook to update affiliate balances on the backend.

---

## Installation (ZIP Upload)

Setting up the module takes less than a minute and requires absolutely no configuration.

### Step 1: Download the Archive
Download the production-ready `affiliate-tracker.zip` file from [Github](https://github.com/monevibe-com/addons/tree/main/wordpress).

### Step 2: Upload to WordPress
1. Log in to your WordPress dashboard.
2. Navigate to **Plugins -> Add New**.
3. Click the **Upload Plugin** button at the top of the page.
4. Choose the downloaded `affiliate-tracker.zip` file and click **Install Now**.

### Step 3: Activation
Once the upload is complete, click the **Activate Plugin** button.

> **That's it!** The plugin runs completely in the background. No additional setup or settings pages are required.

---

## How to Test the Integration

1. Visit your store using a test ID parameter in the URL:  
   `https://your-site.com/?clickid=mv_test_99999`
2. Place a test order through WooCommerce.
3. In the WordPress admin, navigate to **WooCommerce -> Orders** and open your newly created test order.
4. Look under the Billing Address section. You will see a blue **Monevibe Integration** meta box displaying the captured `Click ID`.

---

## Technical Specifications
* **Minimum WordPress Version:** 5.8+
* **Dependencies:** Requires an active installation of **WooCommerce**.
* **Attribution Model:** Lifetime Attribution (cookies are deliberately *not* cleared after the first purchase, enabling the tracking of subsequent recurring orders from the same customer).