# Super Light Store Hours

Contributors: mwalek  
Tags: store, hours, closing, opening, schedule  
Requires at least: 4.6  
Tested up to: 6.4  
Stable tag: 0.0.1  
Requires PHP: 5.3  
License: GPL v3  
License URI: <https://www.gnu.org/licenses/gpl-3.0.en.html>

Disable your store during fixed hours of the week or whenever you wish to pause orders.

## Description

The Super Light Store Hours WordPress plugin is your comprehensive solution for managing and displaying your store's operating hours effortlessly. Tailored for both online and physical stores, this plugin offers a user-friendly interface to configure your schedule and enhance the customer experience. With seamless WooCommerce integration and powerful API features, it's a versatile tool for store owners.

## Features

1\. Flexible Configuration:

- Set working days for your store to align with your business operation.
- Specify opening and closing times in a 24-hour format.

2\. Override Store Status:

- Enable or disable your store with a simple toggle.
- Useful for special occasions, holidays, or unexpected closures.

3\. Seamless WooCommerce Integration:

- Dynamically adjusts capabilities based on WooCommerce activation
- Intelligently manages "Add to Cart" buttons based on store operating hours.

4\. Informative Store Closure Notices:

- Displays a visually appealing store closure notice during closed hours.
- Customized messages strategically placed to inform customers about the closure.

5\. Dynamic Language Localization:

- Declares text domain and languages directory for multilingual support.

6\. Reusable Store Closure Message:

- Utilizes a reusable and easily modifiable store closure message.

## API Features

### RESTful API for Store Hours

1. **Endpoint:**

- `GET /wp-json/slsh/v1/state`

2\. **Parameters:**

- None

3\. **Response:**

- JSON object containing store condition status.
- Example:

<!-- end of the list -->

    {
      "working_days": ["Monday", "Wednesday", "Friday"],
      "opening_closing_time": "09-18",
      "override_status": "0",
      "status": "1"
    }

4\. **How to Use:**

- Make a GET request to the endpoint to retrieve the store's current operating condition.
- Use the provided information to dynamically adjust your application's behavior based on the store's status.

## How to Use

1. Activate the Super Light Store Hours plugin and the WooCommerce plugin.
2. Navigate to the "Store Hours" section in the WordPress settings.
3. Configure working days, opening and closing times, and the override status.
4. Experience enhanced control over product page elements and customer interactions.
5. Utilize provided code snippets for further customization and integration.
6. Leverage the RESTful API to dynamically retrieve the store's operating hours for external applications or integrations.
7. Ensure a seamless and informative experience for your customers based on your store's operating status.

Enhance your WordPress and WooCommerce store management with the Super Light Store Hours plugin. Take control of your store's schedule, provide a better shopping experience, and seamlessly integrate operating hours into your applications. Download and install the plugin today!
