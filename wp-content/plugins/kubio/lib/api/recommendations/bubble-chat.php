<?php

use IlluminateAgnostic\Arr\Support\Arr;
use \Kubio\Flags;
use Kubio\Core\LodashBasic;

add_action(
	'rest_api_init',
	function () {
		$namespace = 'kubio/v1';

		register_rest_route(
			$namespace,
			'/prepare_bubble_chat_plugin',
			array(
				'methods'             => 'GET',
				'callback'            => 'kubio_api_prepare_bubble_chat_plugin',
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},

			)
		);

		register_rest_route(
			$namespace,
			'/get_bubble_chat_widgets',
			array(
				'methods'             => 'GET',
				'callback'            => 'kubio_api_get_bubble_chat_widgets',
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},

			)
		);

		register_rest_route(
			$namespace,
			'/update_bubble_chat_widget_data',
			array(
				'methods'             => 'POST',
				'callback'            => 'kubio_api_update_bubble_chat_widget_data',
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},

			)
		);
	}
);



function kubio_api_prepare_bubble_chat_plugin( WP_REST_Request $request ) {

	//in case of failures only try init once
	$already_setup = Flags::getSetting( 'bubbleChatInstalled', null );
	if(empty($already_setup)) {
		Flags::setSetting( "bubbleChatInstalled", true );
	}

	if(!kubio_is_bubble_chat_plugin_active()) {
		wp_send_json_error(__('Required plugin is missing', 'kubio'), 400);
	}


	if ( ! class_exists( '\FloatingContact\Features\Widgets\FCWidget' )
		|| ! method_exists( '\FloatingContact\Features\Widgets\FCWidget', 'createFromTemplate' )) {
		wp_send_json_error(__('Required class or functions are missing', 'kubio'), 400);
	}

	if ( $already_setup ) {
		wp_send_json_success(kubio_get_bubble_chat_widgets());
	}

	ob_start();


	try {
		$widgets = kubio_get_bubble_chat_widgets();

		//in case the widgets are already created
		if(!empty($widgets)) {

			$errors = ob_get_clean();
			if(!empty($errors)) {
				error_log($errors);
			}

			return $widgets;
		}

		\FloatingContact\Features\Widgets\FCWidget::createFromTemplate([
			'name' => __('Kubio Widget', 'kubio')
		]);

		$result = kubio_get_bubble_chat_widgets();

		$errors = ob_get_clean();
		if(!empty($errors)) {
			error_log($errors);
		}

		wp_send_json_success($result);

	} catch ( Exception $e ) {
		$errors = ob_get_clean();
		if(!empty($errors)) {
			error_log($errors);
		}
		wp_send_json_error($e->getMessage(), 400);
	}
}


function kubio_api_get_bubble_chat_widgets( WP_REST_Request $request ) {
	wp_send_json_success(kubio_get_bubble_chat_widgets());
}
function kubio_api_update_bubble_chat_widget_data( WP_REST_Request $request ) {
	if(!kubio_is_bubble_chat_plugin_active()) {
		wp_send_json_error(__('Required plugin is missing', 'kubio'), 400);
	}
	if ( ! class_exists( '\FloatingContact\Features\Widgets\FCWidget' ) ||
		! method_exists( '\FloatingContact\Features\Widgets\FCWidget', 'findById' ) ||
		! method_exists( '\FloatingContact\Features\Widgets\FCWidget', 'updateParams' )
	) {
		wp_send_json_error(__('Required class or functions are missing', 'kubio'), 400);
	}

	try {
		$id = $request->get_param( 'widgetId' );
		$params = $request->get_param( 'params' );
		$phoneNr = LodashBasic::get($params, 'phone.phoneNr');
		$whatsappNr = LodashBasic::get($params, 'whatsapp.phoneNr');
		$widgetParams = [
			'phone' => [
				'phoneNr' => $phoneNr
			],
			'whatsapp' => [
				'phoneNr' => $whatsappNr
			]
		];
		ob_start();
		try {
			$widget = \FloatingContact\Features\Widgets\FCWidget::findById($id);

			if(!$widget) {
				wp_send_json_error(__('Widget not found'), 404);
			}
			$widget->updateParams($widgetParams);

			wp_send_json_success();
		} catch(\Exception $e) {
			wp_send_json_error($e->getMessage(), 400);
		}



	} catch ( Exception $e ) {
		wp_send_json_error($e->getMessage(), 400);
	}
}
