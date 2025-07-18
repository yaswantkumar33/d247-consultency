<?php
use Kubio\Flags;


require_once __DIR__ . '/recommendations-trait.php';
require_once __DIR__ . '/contact-forms.php';
require_once __DIR__ . '/newsletters.php';
require_once __DIR__ . '/fluent-booking.php';
require_once __DIR__ . '/bubble-chat.php';
require_once __DIR__ . '/recommendation-page-post-type.php';

function kubio_get_recommendations_settings() {
	return array(
		'contactForm'   => array(
			'pluginIsActive' => kubio_is_recommendation_contact_form_plugin_active(),
			'inited'         => Flags::getSetting( 'contactFormsInstalled', false ),
			'itemsList'      => array(),
		),
		'newsletters'   => array(
			'pluginIsActive' => kubio_is_recommendation_newsletter_plugin_active(),
			'inited'         => Flags::getSetting( 'newslettersInstalled', false ),
			'itemsList'      => array(),
		),
		'fluentBooking' => array(
			'pluginIsActive' => kubio_is_fluent_booking_plugin_active(),
			'inited'         => Flags::getSetting( 'fluentBookingInstalled', false ),
			'itemsList'      => array(),
		),
		'bubbleChat'    => array(
			'pluginIsActive' => kubio_is_bubble_chat_plugin_active(),
			'inited'         => Flags::getSetting( 'bubbleChatInstalled', false ),
			'itemsList'      => array(),
		),
	);
}
add_filter(
	'kubio/kubio-utils-data/extras',
	function ( $utils_data ) {

		$utils_data['recommendations'] = isset( $utils_data['recommendations'] ) ? $utils_data['recommendations'] : array();

		$utils_data['recommendations'] = array_merge(
			$utils_data['recommendations'],
			array(
				'displayButtonActionsOptions' => Flags::getSetting( 'displayButtonActionsOptions', false ),
			)
		);

		return $utils_data;
	}
);

add_action(
	'kubio/after_activation',
	function () {
		Flags::setSetting( 'displayButtonActionsOptions', true );
	}
);


add_action(
	'after_switch_theme',
	function () {
		kubio_import_recommendation_page_post_type_template( false );
	}
);
