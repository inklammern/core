<?php

namespace Inkl\Core;

use Aura\Router\RouterContainer;
use Interop\Container\ContainerInterface;
use Inkl\Core\Factories\ResponseFactory;
use Inkl\Core\Senders\ResponseSender;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class App
{

	/** @var ContainerInterface */
	private $container;
	/** @var RouterContainer */
	private $routerContainer;
	/** @var ServerRequestInterface */
	private $serverRequest;
	/** @var ResponseFactory */
	private $responseFactory;
	/** @var ResponseSender */
	private $responseSender;

	/**
	 * App constructor.
	 * @param ContainerInterface $container
	 * @param RouterContainer $routerContainer
	 * @param ServerRequestInterface $serverRequest
	 * @param ResponseFactory $responseFactory
	 * @param ResponseSender $responseSender
	 */
	public function __construct(ContainerInterface $container, RouterContainer $routerContainer, ServerRequestInterface $serverRequest, ResponseFactory $responseFactory, ResponseSender $responseSender)
	{
		$this->container = $container;
		$this->routerContainer = $routerContainer;
		$this->serverRequest = $serverRequest;
		$this->responseSender = $responseSender;
		$this->responseFactory = $responseFactory;
	}

	public function run()
	{
		$this->dispatch();
	}


	protected function dispatch()
	{
		$route = $this->matchRoute();

		$response = $this->handleRoute($route);

		$this->responseSender->send($response);
	}


	private function matchRoute()
	{
		return $this->routerContainer->getMatcher()->match($this->serverRequest);
	}


	protected function handleRoute($route)
	{
		if ($route)
		{
			// set attributes
			$serverRequestAttributesProperty = (new \ReflectionClass($this->serverRequest))->getProperty('attributes');
			$serverRequestAttributesProperty->setAccessible(true);
			$serverRequestAttributesProperty->setValue($this->serverRequest, $route->attributes);

			return $this->invokeRouteHandler($route->handler);
		}

		return $this->responseFactory->create('Not Found', 404);
	}


	protected function invokeRouteHandler($handler)
	{
		$response = '';

		// invoke by array
		if (is_array($handler) && count($handler) == 2)
		{
			$response = call_user_func_array([$this->container->get($handler[0]), $handler[1]], []);
		}

		// invoke by closure
		if ($handler instanceof \Closure)
		{
			$response = $handler($this->container);
		}

		// response
		if ($response instanceof ResponseInterface)
		{
			return $response;
		}

		return $this->responseFactory->create((string)$response);
	}

}
