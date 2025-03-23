<?php

namespace App;

class EventDispatcher
{
	private ListenerProvider $listenerProvider;

	public function __construct(ListenerProvider $listenerProvider)
	{
		$this->listenerProvider = $listenerProvider;
	}

	public function dispatch(object $event): object
	{
		// if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
		// 	return $event;
		// }

		foreach ($this->listenerProvider->getListenersForEvent($event) as $listener) {
			$listener($event);
			// if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
			// 	break;
			// }
		}

		return $event;
	}
}
