<?php
/**
 * Plugin Name: Edumall Mobile
 * Plugin URI: https://edumall.thememove.com
 * Description: Connecting with mobile platform.
 * Author: ThemeMove
 * Author URI: https://thememove.com
 * Version: 1.0.0
 * Text Domain: edumall-mobile
 * License: GPLv2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

defined( 'ABSPATH' ) || exit;
define( 'EDUMALL_MOBILE_DIR', get_template_directory() . DS . 'mobile' );
define( 'EDUMALL_MOBILE_INCLUDES_DIR', EDUMALL_MOBILE_DIR . DS . 'includes' );
define( 'EM_ENDPOINT', 'edumall_mobile/v1' );
require_once EDUMALL_MOBILE_INCLUDES_DIR . DS . 'base-plugin.php';

$plugin = edumallmobile\Edumall_Mobile_Base_Plugin::instance();
$plugin->initialize();
