# Admin Notice Helper
An easy and convenient way for WordPress plugins and themes to display messages and errors to the user within the Administration Panels.

The main benefit of using this over handling it directly in your plugin is that it supports persistent notices (i.e., they don't get wiped out during redirects), and your code will have a smaller footprint (you simply call a single function, rather than register callbacks in multiple places, etc).


## Usage

Include the class from your plugin's main file or theme's functions.php file. This needs to be done as early as possible so that the class can register hook callbacks with WordPress. For example:

```php
require( __DIR__ . '/includes/admin-notice-helper/admin-notice-helper.php' );
```

Call the `add_notice()` function anywhere in your code after WordPress fires the 'init' hook. If you call it before the init hook fires, it won't work. If you want to call it during a callback to the init hook, make sure the callback is registered with a priority of 10 or higher. (The default priority is 10, so you only need to worry about this is you manually register at a lower priority.)

```php
function my_example() {
	if( $success ) {
		add_notice( 'Successful' );
	} else {
		add_notice( 'Failure', 'error' );
	}
}
add_action( 'save_posts', 'my_example' );
```
