<?php

use Kubio\Core\LodashBasic;

function kubio_is_bubble_chat_plugin_active() {
	return class_exists( '\FloatingContact\Constants' );
}



function kubio_get_bubble_chat_widgets() {
	if(!kubio_is_bubble_chat_plugin_active()) {
		return [];
	}
	if ( ! class_exists( '\FloatingContact\Features\Widgets\FCWidgets' )
		|| ! method_exists( '\FloatingContact\Features\Widgets\FCWidgets', 'getInstance' )
		|| ! method_exists( '\FloatingContact\Features\Widgets\FCWidgets', 'getWidgetList' ) ) {
		return [];
	}
	$instance =  \FloatingContact\Features\Widgets\FCWidgets::getInstance();
	if(empty($instance)) {
		return [];
	}

	$widgets = $instance->getWidgetList();

	$items = array_map(function($item) {
		return [
			'value' => LodashBasic::get($item, 'id', null),
			'label' => LodashBasic::get($item, 'props.name', null)
		];
	}, $widgets);


	//We make sure we have the data we require
	$items = array_filter($items, function($item) {
		return LodashBasic::get($item, 'value') !== null && LodashBasic::get($item, 'label') !== null;
	});

	return $items;
}
