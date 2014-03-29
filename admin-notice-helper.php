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

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

if ( ! class_exists( 'Admin_Notice_Helper' ) ) {

	class Admin_Notice_Helper {
		// Declare variables and constants
		private static $instance;
		private $notices, $updatedNotices;

		/**
		 * Constructor
		 */
		private function __construct() {
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
				self::$instance = new Admin_Notice_Helper();
			}

			return self::$instance;
		}

		/**
		 * Initializes variables
		 */
		public function init() {
			$defaultNotices              = array( 'updates' => array(), 'errors' => array() );
			$this->notices               = array_merge( $defaultNotices, get_option( 'anh_notices', array() ) );
			$this->userNoticeCount       = array( 'updates' => count( $this->notices['updates'] ), 'errors' => count( $this->notices['errors'] ) );
			$this->updatedNotices        = false;
		}

		/**
		 * Queues up a message to be displayed to the user
		 *
		 * NOTE: In order to allow HTML in the output, any unsafe variables in $message need to be escaped before they're passed in, instead of escaping here.
		 *
		 * @param string $message The text to show the user
		 * @param string $type    'update' for a success or notification message, or 'error' for an error message
		 */
		public function enqueue( $message, $type = 'update' ) {
			$message = apply_filters( 'anh_enqueue-message', $message );

			if ( ! is_string( $message ) ) {
				return false;
			}

			if ( ! isset( $this->notices[ $type . 's' ] ) ) {
				return false;
			}

			array_push( $this->notices[ $type . 's' ], array(
				'message' => $message,
				'type'    => $type,
			) );

			$this->updatedNotices = true;

			return true;
		}

		/**
		 * Displays updates and errors
		 */
		public function printMessages() {
			foreach ( array( 'updates', 'errors' ) as $type ) {
				if ( $this->notices[ $type ] && $this->userNoticeCount[ $type ] ) {
					$message = '';
					$class   = $type == 'updates' ? 'updated' : 'error';

					require( dirname( __FILE__ ) . '/admin-notice.php' );

					$this->notices[ $type ]         = array();
					$this->updatedNotices           = true;
					$this->userNoticeCount[ $type ] = 0;
				}
			}
		}

		/**
		 * Writes notices to the database
		 */
		public function shutdown() {
			if ( $this->updatedNotices ) {
				update_option( 'anh_notices', $this->notices );
			}
		}
	} // end Admin_Notice_Helper

	Admin_Notice_Helper::getSingleton(); // Create the instance immediately to make sure hook callbacks are registered in time
}
