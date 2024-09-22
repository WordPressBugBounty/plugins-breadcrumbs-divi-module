=== Breadcrumbs Divi Module===
Contributors: themeythemes
Tags: divi, divi breadcrumbs, breadcrumbs
Requires at least: 5.0
Tested up to: 6.6
Requires PHP: 5.6
Stable tag: 1.2.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A simple lightweight plugin that adds a breadcrumbs module in the Divi theme.

== Description ==

The Divi Breadcrumbs plugin enhances your website by adding a customizable Breadcrumbs module to the Divi Builder. Breadcrumbs improve user experience by providing a clear navigational path, helping visitors understand their location within your site and easily backtrack to previous sections.

# Features

* **Visual Builder Supported**: Enjoy seamless integration with Divi’s Visual Builder. You can drag and drop the Breadcrumbs module into your layout.
* **Customizable Home Text**: Define your own text for the home link in the breadcrumbs, offering more flexibility in how you label the starting point.
* **Before Text Option**: Add a custom text that appears before the breadcrumbs, providing additional context to your breadcrumbs.
* **Icon Options**: Choose from Divi’s icon library to use as the separator between breadcrumb links. You can also add a custom icon before the breadcrumbs to further personalize their appearance.
* **Custom Home Link**: If you need to link the home breadcrumb to a custom URL, this option allows you to do so.
* **Color Customization**: Tailor the colors of your breadcrumbs to align with your site’s design:
  * **Link Color**: Set a custom color for breadcrumb links and define a different color for the hover state, ensuring your breadcrumbs.
  * **Separator Color**: Customize the color of the separator icon.
  * **Current Text Color**: Specify a color for the text of the current page, helping users easily identify their current location.

For a live demonstration and to see the Breadcrumbs module in action, visit [Breadcrumbs Demo Page](https://www.learnhowwp.com/divi-breadcrumbs-module/).

The content generated in the Visual Builder is only for preview. Part of the Breadcrumbs in the Visual Builder is dummy data but don't worry you will see the correct breadcrumbs on the front end after you exit the builder.

**More Free Divi Plugins**

* [Divi Contact Form DB](https://wordpress.org/plugins/contact-form-db-divi/)
* [Divi Post Carousel Module](https://wordpress.org/plugins/post-carousel-divi/)
* [Divi Overlay on Images Module](https://wordpress.org/plugins/overlay-image-divi-module/)
* [Divi Menu Cart Module](https://wordpress.org/plugins/menu-cart-divi/)
* [Divi Flip Cards Module](https://wordpress.org/plugins/flip-cards-module-divi/)
* [Divi Breadcrumbs Module](https://wordpress.org/plugins/breadcrumbs-divi-module/)
* [Divi Image Carousel](https://wordpress.org/plugins/image-carousel-divi/)

If you have any questions or feature ideas please create a new thread in Support.

== Installation ==
1. Upload `lwp-divi-breadcrumbs.zip` to the `/wp-content/plugins/` directory
2. Activate the plugin through the `Plugins` page in your `WordPress Dashboard`.

== Frequently Asked Questions ==

= Where can I access the module? =

After you activate the plugin a module should automatically appear in the module list. The name of the module is Breadcrumbs

= Why does the Breadcrumbs content in the Visual Builder look different from the front end? =

The content generated in the Visual Builder is only for preview purposes and includes dummy data. This is to help you visualize how the Breadcrumbs will look in the layout. However, the actual Breadcrumbs that appear on the front end of your site will reflect the correct hierarchy of your pages or categories once you exit the builder.

= What is the purpose of the 'Home Text' setting in the Breadcrumbs module? =

The 'Home Text' setting allows you to customize the text that is displayed for the home link in the breadcrumbs. By default, it is set to 'Home'.

= What does the 'Before Text' setting do? =

The 'Before Text' setting allows you to specify any text that you want to display before the breadcrumbs.

= How can I change the icon used as a separator in the breadcrumbs? =

You can change the separator icon in the breadcrumbs by using the 'Separator Icon' setting. This setting allows you to select an icon from the available options.

= What is the 'Add Before Icon' setting used for? =

The 'Add Before Icon' setting allows you to add an icon before the breadcrumbs. You can choose the icon from the available options.

= How can I use a custom link for the Home item in the breadcrumbs? =

You can use a custom link for the Home item by enabling the 'Use Custom Home Link' setting and then providing the link in the 'Custom Home Link' setting.

= How can I change the color of the links, separator, and current text in the breadcrumbs? =

You can change the color of the links, separator, and current text in the breadcrumbs module by using the 'Link Color', 'Separator Color', and 'Current Text Color' settings respectively. These settings allow you to choose a custom color for each element.

= What happens if I don't see the breadcrumbs on my page? =

If you don't see the breadcrumbs on your page, it could be because there are no parent pages or categories for the current page. Breadcrumbs are generated based on the hierarchy of your pages or categories.

= What if I can't find a specific feature or setting in the plugin? =

This is a free plugin and it comes with a set number of features. If you are unable to find a specific feature or setting, it is likely that it was not included in the initial development of the plugin. In such cases, please feel free to open a feature request on the support page. All the features provided by this plugin are listed in the plugin description.

== Changelog ==

= 1.2.3 =
* Fixed icon is deprecated warning

= 1.2.2 =
* Fixed fatal error with number_format function caused by ?et_blog.
* Fixed label for 'Before Icon'.
* Added option for custom home link.
* Fixed issue to prevent breadcrumb icon display on homepage when there were no bredcrumbs to display.

= 1.2.1 =
* Fixed Font Awesome icons not working.
* Fixed warning related to et_pb_get_font_icon_list being used
* Fixed PHP 8.0 error required parameters after optional parameter

= 1.2 =
* Fixed the missing field position error caused when the parent category was a substring of child category eg Videos > Videos of Something

= 1.1 =
* Added hover controls on Link color
* Moved the styles options to the Design tab
* Added option to add a icon at the start of breadcrumbs
* Added RDFa to Breadcrumbs

= 1.0.1 =
* Fixed the icon size in the Theme Builder
* Wrapped the before text in a span

= 1.0 =
* First Release