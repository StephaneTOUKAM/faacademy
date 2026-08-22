<?php
$section  = 'login';
$priority = 1;
$prefix   = 'login_';

Edumall_Kirki::add_field( 'theme', array(
	'type'     => 'select',
	'settings' => 'login_redirect',
	'label'    => esc_html__( 'Login Redirect', 'edumall' ),
	'section'  => $section,
	'priority' => $priority++,
	'default'  => '',
	'choices'  => [
		''          => esc_html__( 'Current Page', 'edumall' ),
		'home'      => esc_html__( 'Home', 'edumall' ),
		'dashboard' => esc_html__( 'Dashboard', 'edumall' ),
		'custom'    => esc_html__( 'Custom', 'edumall' ),
	],
) );

Edumall_Kirki::add_field( 'theme', array(
	'type'            => 'text',
	'settings'        => 'custom_login_redirect',
	'label'           => esc_html__( 'Custom Url', 'edumall' ),
	'section'         => $section,
	'priority'        => $priority++,
	'default' => '',
	'active_callback' => array(
		array(
			'setting'  => 'login_redirect',
			'operator' => '==',
			'value'    => 'custom',
		),
	),
) );
