<?php

/*  
 * Copyright 2014 Ian Dunn (email : ian@iandunn.name)
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as 
 * published by the Free Software Foundation.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if ( $_SERVER['SCRIPT_FILENAME'] == __FILE__ )
	die( 'Access denied.' );

if ( ! class_exists( 'Admin_Notice_Helper' ) ) {

	class Admin_Notice_Helper {
		// Declare variables and constants
		private static $instance;
		private $notices, $updatedNotices, $userNoticeCount, $accessiblePrivateVars, $debugMode;
		const NAME    = 'Admin_Notice_Helper';
		const VERSION = '0.1.3';
		const PREFIX  = 'idan_';

		/**
		 * Constructor
		 *
		 * @mvc    Controller
		 * @author Ian Dunn <ian@iandunn.name>
		 */
		private function __construct() {
			// NOTE: Make sure you update the did_action() parameter in the corresponding callback method when changing the hooks here
			add_action( 'init',          array( $this, 'init' ), 9 );         // needs to run before other plugin's init callbacks so that they can enqueue messages in their init callbacks
			add_action( 'admin_notices', array( $this, 'printMessages' ) );
			add_action( 'shutdown',      array( $this, 'shutdown' ) );
		}

		/**
		 * Provides access to a single instances of the class using the singleton pattern
		 *
		 * @mvc    Controller
		 * @author Ian Dunn <ian@iandunn.name>
		 * @return object
		 */
		public static function getSingleton() {
			if ( ! isset( self::$instance ) ) {
				$class          = __CLASS__;
				self::$instance = new $class;
			}

			return self::$instance;
		}

		/**
		 * Initializes variables
		 *
		 * @mvc    Controller
		 * @author Ian Dunn <ian@iandunn.name>
		 */
		public function init() {
			if ( did_action( 'init' ) !== 1 )
				return;

			$defaultNotices              = array( 'updates' => array(), 'errors' => array() );
			$this->notices               = array_merge( $defaultNotices, get_option( self::PREFIX . 'notices', array() ) );
			$this->userNoticeCount       = array( 'updates' => count( $this->notices['updates'] ), 'errors' => count( $this->notices['errors'] ) ); // @todo - don't you need to check if the messages are 'user' mode or not?
			$this->updatedNotices        = false;
			$this->accessiblePrivateVars = array( 'debugMode' );
			$this->debugMode             = false;
		}

		/**
		 * Public getter for private variables
		 *
		 * @mvc    Model
		 * @author Ian Dunn <ian@iandunn.name>
		 * @param string $variable
		 * @return mixed
		 */
		public function __get( $variable ) {
			if ( in_array( $variable, $this->accessiblePrivateVars ) ) {
				return $this->$variable;
			} else {
				throw new Exception( self::NAME . " error: Variable '" . $variable . "' doesn't exist or isn't accessible." );
			}
		}

		/**
		 * Public setter for private variables
		 *
		 * @mvc    Model
		 * @author Ian Dunn <ian@iandunn.name>
		 * @param string $variable
		 * @param mixed  $value
		 */
		public function __set( $variable, $value ) {
			if ( in_array( $variable, $this->accessiblePrivateVars ) ) {
				$this->$variable = $value;
			} else {
				throw new Exception( self::NAME . " error: Variable '" . $variable . "' doesn't exist or isn't accessible." );
			}
		}

		/**
		 * Queues up a message to be displayed to the user
		 * NOTE: In order to allow HTML in the output, any unsafe variables in $message need to be escaped before they're passed in, instead of escaping here.
		 *
		 * @mvc    Model
		 * @author Ian Dunn <ian@iandunn.name>
		 * @param string $message The text to show the user
		 * @param string $type    'update' for a success or notification message, or 'error' for an error message
		 * @param string $mode    'user' if it's intended for the user, or 'debug' if it's intended for the developer
		 */
		public function enqueue( $message, $type = 'update', $mode = 'user' ) {
			$message = apply_filters( self::PREFIX . 'enqueue-message', $message );

			if ( ! is_string( $message ) ) {
				return false;
			}

			if ( ! isset( $this->notices[$type . 's'] ) ) {
				return false;
			}

			array_push( $this->notices[$type . 's'], array(
				'message' => $message,
				'type'    => $type,
				'mode'    => $mode
			) );

			if ( $mode == 'user' ) {
				$this->userNoticeCount[$type . 's'] ++;
			}

			$this->updatedNotices = true;

			return true;
		}

		/**
		 * Displays updates and errors
		 *
		 * @mvc    Model
		 * @author Ian Dunn <ian@iandunn.name>
		 */
		public function printMessages() {
			if ( did_action( 'admin_notices' ) !== 1 ) {
				return;
			}

			foreach ( array( 'updates', 'errors' ) as $type ) {
				if ( $this->notices[$type] && ( $this->debugMode || $this->userNoticeCount[$type] ) ) {
					$message = '';
					$class   = $type == 'updates' ? 'updated' : 'error';

					require( dirname( __FILE__ ) . '/v-admin-notice.php' );

					$this->notices[$type]         = array();
					$this->updatedNotices         = true;
					$this->userNoticeCount[$type] = 0;
				}
			}
		}

		/**
		 * Writes notices to the database
		 *
		 * @mvc    Controller
		 * @author Ian Dunn <ian@iandunn.name>
		 */
		public function shutdown() {
			if ( did_action( 'shutdown' ) !== 1 )
				return;

			if ( $this->updatedNotices ) {
				update_option( self::PREFIX . 'notices', $this->notices );
			}
		}
	} // end Admin_Notice_Helper

	Admin_Notice_Helper::getSingleton(); // Create the instance immediately to make sure hook callbacks are registered in time
}
