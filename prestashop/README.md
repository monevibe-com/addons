# Monevibe Affiliate Tracker for PrestaShop

An open-source PrestaShop module that automatically tracks affiliate partners using unique Click IDs. It seamlessly captures incoming affiliate traffic and sends order conversion data back to the Monevibe postback API.

## Features

- **Automated Tracking:** Detects `?clickid=` in the URL and stores it securely in a long-lived browser cookie.
- **Order Attribution:** Dispatches order creation events directly to Monevibe immediately after validation.
- **Status Syncing:** Automatically triggers real-time webhooks on your backend whenever an order status changes to **Paid** or **Canceled**.
- **Lightweight Design:** Native background execution utilizing PrestaShop's core hook architecture without slowing down your store or checkout process.

---

## Installation

### Method 1: Upload via PrestaShop Admin (Recommended)
1. Go to the [Github](https://github.com/monevibe-com/addons/tree/main/prestashop) page of this repository and download the latest `affiliate_tracker.zip` file.
2. Log into your PrestaShop admin panel.
3. Navigate to **Modules** -> **Module Manager** (or **Modules Catalog**).
4. Click the **Upload a module** button in the top right corner.
5. Drag and drop or select the downloaded `affiliate_tracker.zip` file.
6. Once installed, click **Configure** if you need to double-check status, though it works out of the box!

### Method 2: Manual Installation via SSH/FTP
If you prefer managing files directly on your server:
1. Clone or download this repository.
2. Compress the root `affiliate_tracker` directory into a `.zip` archive (make sure files are inside an `affiliate_tracker/` folder within the zip).
3. Upload and extract the folder to your store's root directory under `/modules/`.
4. Ensure your server permissions are set correctly (e.g., `chmod -R 775` or managed by your web server user like `www-data`).
5. Go to your PrestaShop Admin under **Module Manager**, search for **Monevibe Affiliate Tracker**, and click **Install**.

---

## How It Works & Verification

### 1. Tracking Verification
To test if traffic tracking is functioning properly:
1. Visit your store homepage using a test tracking link:
   `https://yourstore.com/?clickid=TEST_123456789`
2. Open your browser Developer Tools (**F12**) and navigate to the **Application** tab (Chrome) or **Storage** tab (Firefox/Safari).
3. Under **Cookies**, select your store's domain.
4. Look for the `monevibe_click_id` cookie. It should display the value `TEST_123456789` and be configured as a long-lived cookie with `Secure` and `HttpOnly` flags active.

### 2. Conversions & Order Webhooks
Once a valid `clickid` cookie is detected in the browser, the module fires payloads back to the Monevibe production system:
- **Order Placement:** Triggers the `create-order` payload to `https://addons.monevibe.com/api/prestashop/order`.
- **Status Updates:** Triggers the `webhook` payload to `https://addons.monevibe.com/api/prestashop/webhook` upon payment confirmation or order cancellations.

---

## Requirements

- **PrestaShop Compatibility:** 1.7.0.0 – 8.x+
- **PHP Compatibility:** PHP 7.4 up to PHP 8.4+
- **Server Dependencies:** Requires the PHP `cURL` extension enabled on your environment to handle background server-to-server API requests safely.

## License

This project is open-source software licensed under the [MIT License](LICENSE).