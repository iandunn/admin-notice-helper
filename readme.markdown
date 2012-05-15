# ID Admin Notices
Author: [Ian Dunn](http://iandunn.name)  
License: GPL2  
Requires: PHP 5 and WordPress 2.0.11  


## Description
A clean and convenient way for WordPress plugins and themes to display messages and errors to the user within the Administration Panels.

## Usage

Include the class from your plugin's main file or theme's functions.php file. This needs to be done as early as possible so that the class can register hook callbacks with WordPress. For example:

	require( dirname( __FILE__ ) . '/includes/id-admin-notices/id-admin-notices.php' );
	
Call the mEnqueue() method anywhere in your code after WordPress fires the 'init' hook. If you call mEnqueue() before the init hook fires, it won't work. If you want to call mEnqeue() during a callback to the init hook, make sure the callback is registered with a priority of 10 or higher.

	function foo()
	{
		$notices = IDAdminNotices::cGetSingleton();
		
		if( $success )
			$notices->mEnqueue( 'Successful' );
		else
			$notices->mEnqueue( 'Failure', 'error' );
	}
	add_action( 'save_posts', 'foo' );
	
You can also add messages to your code that are only intended to be seen by you during development, but not by users during production:

	function foo()
	{
		$notices = IDAdminNotices::cGetSingleton();
		$notices->debugMode = true;
		
		if( !$success )
			$notices->mEnqueue( 'Detailed error message', 'error', 'debug' );
	}
	add_action( 'save_posts', 'foo' );

## Changelog

### 0.1
* Initial release. Refactored into standalone class from previous versions where the methods were included in whatever class used them.