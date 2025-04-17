# Location Vendors Plugin

## Overview

The **Location Vendors** plugin provides a custom post type for managing vendor cards along with modal pop-ups and location filtering. It is designed to help you display vendors based on predefined locations.

## Features

- **Custom Post Type:**  
  Creates a "Location Vendors" post type for easy management of vendor information (title, featured image, owner name, and description).

- **Vendor Locations Taxonomy:**  
  Implements a hierarchical taxonomy (`vendor_location`) that includes the following predefined terms:

  - Downtown
  - Boardwalk
  - Shoppes at the Asbury
  - 3 Little Birds
  - Shoobie Shack

  This taxonomy is displayed as a checkbox list on the vendor edit screen, allowing you to assign one or more locations to each vendor.

- **Modal Pop-Up:**  
  Each vendor card displays additional details in a modal pop-up when clicked.

- **Shortcode Filtering:**  
  Use the `[location_vendors]` shortcode with an optional `location` attribute to filter and display vendors by a specific location. For example, `[location_vendors location="Downtown"]` will display only vendors tagged as "Downtown".

## Requirements

- WordPress 5.0 or later
- PHP 7.0 or later

## Installation

1. **Upload Plugin Files:**  
   Upload the entire plugin folder to the `/wp-content/plugins/` directory.

2. **Activate the Plugin:**  
   Navigate to the WordPress Dashboard, go to **Plugins**, and activate **Location Vendors**.

3. **Predefined Terms Setup:**  
   Upon activation, the plugin automatically registers the five vendor location terms if they do not already exist.

## Usage

### Adding a Vendor

1. Go to the **Location Vendors** custom post type in your WordPress Dashboard.
2. Click **Add New** to create a new vendor.
3. Enter the vendor details such as the title (vendor name), owner name, and description.
4. In the **Vendor Locations** meta box (automatically provided by WordPress), select one or more locations by checking the appropriate boxes.
5. Publish your vendor post.

### Displaying Vendors on the Frontend

Use the `[location_vendors]` shortcode to display vendor cards on any page or post.

**Shortcode Attributes:**

- `count` (optional): The number of vendor cards to display. Set to `-1` for all vendors.
- `location` (optional): Filter vendors by a specific location (e.g., `Downtown`, `Boardwalk`, etc.).

**Example Usage:**

- To display all vendors:
  [location_vendors]

css
Copy

- To display only vendors from Downtown:
  [location_vendors location="Downtown"]

csharp
Copy

## Customization

- **Styles and Scripts:**  
  The plugin includes basic styles and jQuery scripts for handling the modal pop-up. You can override or extend these by adding custom CSS or JavaScript in your theme or via a child theme.

- **Template Customization:**  
  The output for the vendor cards and modals is defined in the shortcode function. Advanced users can modify the HTML structure and styles as needed.

## Support

If you encounter any issues or have questions regarding the plugin, please feel free to reach out or consult the [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/).

## License
