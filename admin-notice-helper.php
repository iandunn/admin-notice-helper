<?php

/*
Name:        Admin Notice Helper
URI:         https://github.com/iandunn/admin-notice-helper
Version:     0.2
Author:      Ian Dunn
Author URI:  http://iandunn.name
License:     GPLv2
*/

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


if ( ! class_exists( 'Admin_Notice_Handler' ) ) {

final class Admin_Notice_Handler {
    protected static $_instance;
    protected $notices, $notices_were_updated;
    protected $template;

    /**
     * Constructor
     */
    protected function __construct() {
        # needs to run before other plugin's init callbacks so that they can enqueue messages in their init callbacks
        $this->template = 'bootstrap' ;# Use wp / bootstrap
        $this->addStyles();
        add_action( 'init',          array( $this, 'init' ), 9 );    
        add_action( 'admin_notices', array( $this, 'print_notices' ) );
        add_action( 'shutdown',      array( $this, 'shutdown' ) );
    }

    /**
     * Provides access to a single instances of the class using the singleton pattern
     *
     * @mvc    Controller
     * @author Ian Dunn <ian@iandunn.name>
     * @return object
     */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

    /**
     * Initializes variables
     */
    public function init() {
        $default_notices             = array( 'update' => array(), 'error' => array() );
        $this->notices               = array_merge( $default_notices, get_option( 'anh_notices', array() ) );
        $this->notices_were_updated  = false;
        
    }
    
    
    private function addStyles(){
        if($this->template == 'bootstrap'){
            add_action( 'admin_head', array($this,'add_bootstrap_style') );
        }
        
    }

    
    public function add_bootstrap_style(){

        echo '<style>.close {float: right;font-size: 20px;font-weight: bold;line-height: 18px;color: #000000;text-shadow: 0 1px 0 #ffffff;opacity: 0.2;filter: alpha(opacity=20);}.close:hover {color: #000000;text-decoration: none;opacity: 0.4;filter: alpha(opacity=40);cursor: pointer;}.alert {padding: 8px 35px 8px 14px;margin-bottom: 18px;text-shadow: 0 1px 0 rgba(255, 255, 255, 0.5);background-color: #fcf8e3;border: 1px solid #fbeed5;-webkit-border-radius: 4px;-moz-border-radius: 4px;border-radius: 4px;color: #c09853;}.alert-heading {color: inherit;}.alert .close {position: relative;top: -2px;right: -21px;line-height: 18px;}.alert-update {background-color: #dff0d8;border-color: #d6e9c6;color: #468847;}.alert-danger, .alert-error {background-color: #f2dede;border-color: #eed3d7;color: #b94a48;}.alert-info {background-color: #d9edf7;border-color: #bce8f1;color: #3a87ad;}.alert-block {padding-top: 14px;padding-bottom: 14px;}.alert-block > p, .alert-block > ul {margin-bottom: 0;}.alert-block p + p {margin-top: 5px;} </style>';
    }
    
    /**
     * Queues up a message to be displayed to the user
     *
     * @param string $message The text to show the user
     * @param string $type    'update' for a success or notification message, or 'error' for an error message
     */
    public function enqueue( $message, $type = 'update' ) {
        if(isset($this->notices[ $type ])){
            if ( in_array( $message, array_values( $this->notices[ $type ] ) ) ) {
                return;
            }
        }
        $this->notices[ $type ][]   = (string) apply_filters( 'anh_enqueue_message', $message );
        $this->notices_were_updated = true;
    }
    
    
    public function generate_wp_notice(){
        foreach ( array( 'update', 'error' ) as $type ) {

            if ( count( $this->notices[ $type ] ) ) {
                $class = 'update' == $type ? 'updated' : 'error';

                echo '<div class="anh_message '.$class.'">';
                    foreach ( $this->notices[ $type ] as $notice ) :
                        echo '<p>'.wp_kses($notice, wp_kses_allowed_html('post')).'</p>';
                    endforeach;
                echo '</div>';

                $this->notices[ $type ]      = array();
                $this->notices_were_updated  = true;
            }
        }    
    }
    

    public function generate_bootstrap_notice(){
        foreach ( array('block','update','error','info') as $type ) {
            if(isset($this->notices[$type])){
                if ( count( $this->notices[ $type ] ) ) {
                    echo '<div class="alert alert-'.$type.'">
                        <a class="close" data-dismiss="alert">×</a>';
                        foreach ( $this->notices[ $type ] as $notice ) :
                            echo '<p>'.wp_kses($notice, wp_kses_allowed_html('post')).'</p>';
                        endforeach;
                    echo '</div>';

                    $this->notices[ $type ]      = array();
                    $this->notices_were_updated  = true;
                }
            }

        }    
    }
    /**
     * Displays updates and errors
     */
    public function print_notices() { 
        if($this->template == 'wp'){
            $this->generate_wp_notice();
        } else if($this->template == 'bootstrap'){
            $this->generate_bootstrap_notice();
        }

    }

    /**
     * Writes notices to the database
     */
    public function shutdown() {
        if ( $this->notices_were_updated ) {
            update_option( 'anh_notices', $this->notices );
        }
    }
} 

function wp_admin_notice(){
    return Admin_Notice_Handler::instance();
}


if ( ! function_exists( 'add_notice' ) ) {
    function add_notice( $message, $type = 'update' ) {
        wp_admin_notice()->enqueue( $message, $type );
    }
}
}
