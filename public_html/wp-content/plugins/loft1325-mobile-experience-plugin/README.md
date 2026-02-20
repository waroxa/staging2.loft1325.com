# Loft1325 Mobile Experience Plugin

## Overview

The **Loft1325 Mobile Experience** plugin is a custom WordPress plugin designed to provide a mobile-first, luxury experience for the entire Loft1325 website. It extends the `template-11` design across all mobile pages, ensuring a consistent and elegant user interface for mobile visitors.

## Features

*   **Mobile-First Design**: Implements the `template-11` minimalist, high-contrast aesthetic across all mobile pages.
*   **Consistent Header**: Standardized header with menu toggle, logo, and language selector (FR/EN).
*   **Full-Screen Mobile Menu**: Elegant navigation menu overlay with links to all main sections.
*   **Responsive Templates**: Custom mobile templates for homepage, room details, rooms listing, and checkout flow.
*   **Restaurants Section**: Displays partner restaurants in a 2-column grid layout at the footer of the homepage.
*   **Booking Integration**: Seamless integration with existing booking forms and payment systems.
*   **Language Support**: Built-in support for French and English language toggling.
*   **Performance Optimized**: Lightweight CSS and JavaScript, optimized for mobile devices.

## Installation

1. Download or clone the plugin to your WordPress plugins directory:
   ```bash
   git clone https://github.com/yourusername/loft1325-mobile-experience.git /path/to/wp-content/plugins/loft1325-mobile-experience
   ```

2. Navigate to the WordPress admin dashboard.

3. Go to **Plugins** and find "Loft1325 Mobile Experience" in the list.

4. Click **Activate** to enable the plugin.

## Configuration

### Setting Up Restaurants

To display restaurants on the mobile homepage, add restaurant data to the WordPress options:

```php
$restaurants = array(
    array(
        'name' => 'Restaurant Name',
        'logo' => 'https://example.com/logo.png',
    ),
    // Add more restaurants as needed
);

update_option( 'loft1325_restaurants', $restaurants );
```

Alternatively, you can add a settings page to the WordPress admin to manage restaurants without code.

### Customizing Content

The plugin uses standard WordPress functions to fetch content. To customize the displayed content:

1. Edit the template files in the `templates/` directory.
2. Modify the CSS in `assets/css/mobile-style.css`.
3. Update JavaScript functionality in `assets/js/mobile-script.js`.

## File Structure

```
loft1325-mobile-experience/
├── loft1325-mobile-experience.php    # Main plugin file
├── README.md                         # This file
├── assets/
│   ├── css/
│   │   └── mobile-style.css          # Mobile-specific styles
│   ├── js/
│   │   └── mobile-script.js          # Mobile-specific JavaScript
│   └── images/
│       └── logo.svg                  # Loft1325 logo
└── templates/
    ├── mobile-header.php             # Header component
    ├── mobile-menu.php               # Mobile menu overlay
    ├── mobile-front-page.php         # Homepage template
    ├── mobile-single.php             # Single post/room template
    ├── mobile-page.php               # Generic page template
    └── mobile-generic.php            # Fallback template
```

## Hooks and Filters

The plugin provides several hooks for customization:

### Filters

*   `loft1325_mobile_templates` - Customize the template mapping.
*   `loft1325_mobile_assets` - Customize which assets are enqueued.

### Actions

*   `loft1325_mobile_before_header` - Runs before the mobile header.
*   `loft1325_mobile_after_content` - Runs after the main content.

## Customization Examples

### Adding a Custom Mobile Template

1. Create a new template file in the `templates/` directory, e.g., `mobile-custom-page.php`.
2. Add the template mapping in the main plugin file:
   ```php
   'custom-page.php' => 'templates/mobile-custom-page.php',
   ```

### Modifying Styles

Edit `assets/css/mobile-style.css` to customize colors, fonts, spacing, and layout.

### Adding Custom JavaScript

Add your custom JavaScript to `assets/js/mobile-script.js` or enqueue a separate file in the main plugin file.

## Browser Support

The plugin is designed to work on modern mobile browsers:

*   iOS Safari 12+
*   Chrome Mobile 60+
*   Firefox Mobile 60+
*   Samsung Internet 8+

## Performance Considerations

*   Mobile-specific assets are only loaded for mobile devices.
*   CSS and JavaScript are minified for production.
*   Images are optimized for mobile viewing.
*   Caching is recommended for optimal performance.

## Troubleshooting

### Mobile Templates Not Showing

1. Verify that `wp_is_mobile()` is correctly detecting your device.
2. Check that the template files exist in the `templates/` directory.
3. Clear any caching plugins.

### Styling Issues

1. Ensure that desktop CSS is not conflicting with mobile CSS.
2. Check browser console for JavaScript errors.
3. Verify that all CSS files are properly enqueued.

### Menu Not Opening

1. Check that JavaScript is enabled in the browser.
2. Verify that the menu toggle button has the correct ID (`mobile-menu-toggle`).
3. Check browser console for JavaScript errors.

## Support and Contribution

For support, issues, or contributions, please contact the development team or submit a pull request.

## License

This plugin is licensed under the GPL2 License. See the main plugin file for more details.

## Changelog

### Version 1.0.0
- Initial release
- Mobile-first design implementation
- Header, menu, and navigation components
- Homepage, room details, and checkout templates
- Restaurants section integration
- Language toggle support
