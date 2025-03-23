<?php

namespace App;

class ListenerProvider
{
	/**
	 * @var callable[][] $listeners
	 */
	private array $listeners = [];

	public function addListener(string $eventType, callable $listener): void
	{
		$this->listeners[$eventType][] = $listener;
	}

	/**
	 * @return callable[]
	 */
	public function getListenersForEvent(object $event): iterable
	{
		$eventType = get_class($event);
		return $this->listeners[$eventType] ?? [];
	}
}
