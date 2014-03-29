# Admin Notice Helper
A clean and convenient way for WordPress plugins and themes to display messages and errors to the user within the Administration Panels.


## Usage

Include the class from your plugin's main file or theme's functions.php file. This needs to be done as early as possible so that the class can register hook callbacks with WordPress. For example:

	require( dirname( __FILE__ ) . '/includes/admin-notice-helper/admin-notice-helper.php' );
	
Call the enqueue() method anywhere in your code after WordPress fires the 'init' hook. If you call enqueue() before the init hook fires, it won't work. If you want to call enqueue() during a callback to the init hook, make sure the callback is registered with a priority of 10 or higher.

	function foo()
	{
		$notices = Admin_Notice_Helper::getSingleton();
		
		if( $success )
			$notices->enqueue( 'Successful' );
		else
			$notices->enqueue( 'Failure', 'error' );
	}
	add_action( 'save_posts', 'foo' );
	
You can also add messages to your code that are only intended to be seen by you during development, but not by users during production:

	function foo()
	{
		$notices = Admin_Notice_Helper::getSingleton();
		$notices->debugMode = true;
		
		if( !$success )
			$notices->mnqueue( 'Detailed error message', 'error', 'debug' );
	}
	add_action( 'save_posts', 'foo' );
