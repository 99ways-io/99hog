# 99hog - WooCommerce PostHog Integration

A lean, open-source WordPress plugin that integrates WooCommerce with PostHog for ecommerce event tracking.

## Description

99hog provides a seamless integration between WooCommerce and PostHog, allowing you to track a comprehensive set of ecommerce events. The plugin is designed to be lightweight and developer-friendly, with a focus on providing a standardized event schema that is easy to work with.

### Key Features

*   **Comprehensive Event Tracking:** Tracks a wide range of ecommerce events, including `view_item`, `add_to_cart`, `purchase`, and more.
*   **Standardized Schema:** Uses a GA4-style schema for all events, ensuring consistency and ease of use.
*   **Developer Friendly:** Includes filters and action hooks to allow for easy customization and extension.
*   **Easy Configuration:** Simple admin interface for configuring your PostHog Project API Key and Host.
*   **Event Toggles:** Enable or disable individual events from the admin dashboard.

## Installation

1.  Upload the `99hog` directory to your `/wp-content/plugins/` directory.
2.  Activate the plugin through the 'Plugins' menu in WordPress.
3.  Navigate to the '99hog' menu in your WordPress admin dashboard to configure the plugin.

## Configuration

To configure the plugin, you will need to provide your PostHog Project API Key and Host.

1.  Navigate to the '99hog' menu in your WordPress admin dashboard.
2.  Enter your PostHog Project API Key in the "PostHog Project API Key" field.
3.  Enter your PostHog Host in the "PostHog Host" field (e.g., `https://app.posthog.com`).
4.  Enable or disable the events you want to track using the checkboxes.
5.  Click "Save Settings".

## Testing

To test the plugin, you can use the PostHog debugger to see the events being sent from your website.

1.  Log in to your PostHog account and navigate to the "Debugger" page.
2.  In a separate browser window, navigate to your WooCommerce store and perform some actions, such as viewing a product, adding a product to the cart, and completing a purchase.
3.  You should see the corresponding events appear in the PostHog debugger.

## Developer Extensibility

The plugin includes the following hooks for developers to extend its functionality:

### Filters

*   `ph_ecom_event_props`: Allows you to modify the properties of an event before it is sent to PostHog.

### Actions

*   `ph_ecom_event_sent`: Triggered after an event has been sent to PostHog.
