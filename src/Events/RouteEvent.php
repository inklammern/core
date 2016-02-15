<?php

namespace Inkl\Core\Events;

use Aura\Router\Route;
use Symfony\Component\EventDispatcher\Event;

class RouteEvent extends Event
{
	/** @var Route */
	private $route;

	/**
	 * RouteEvent constructor.
	 * @param Route $route
     */
	public function __construct(Route $route)
	{
		$this->route = $route;
	}


	public function setRoute(Route $route)
	{
		$this->route = $route;
	}

	public function getRoute()
	{
		return $this->route;
	}

}
