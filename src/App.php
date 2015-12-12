<?php

namespace Inkl\Core;

use Aura\Router\Exception;
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
			foreach ($route->attributes as $key => $value)
			{
				$this->serverRequest = $this->serverRequest->withAttribute($key, $value);
			}

			$this->container->set(ServerRequestInterface::class, $this->serverRequest);

			return $this->invokeRouteHandler($route->handler);
		}

		return $this->responseFactory->create('Not Found', 404);
	}


	protected function invokeRouteHandler($handler)
	{
		$responseData = '';

		// invoke by array
		if (is_array($handler) && count($handler) >= 2)
		{
			$inputParams = array_merge($this->serverRequest->getQueryParams(), $this->serverRequest->getAttributes());

			$reflectionMethod = new \ReflectionMethod($handler[0], $handler[1]);
			$reflectionParams = $reflectionMethod->getParameters();

			$methodParams = [];
			foreach ($reflectionParams as $reflectionParam)
			{
				$name = $reflectionParam->getName();

				if (!isset($inputParams[$name]) && !$reflectionParam->isOptional()) {
					throw new \Exception(sprintf('cannot resolve parameter "%s" for %s::%s', $name, $handler[0], $handler[1]));
				}

				$methodParams[] = (isset($inputParams[$name]) ? $inputParams[$name] : $reflectionParam->getDefaultValue());
			}

			$responseData = call_user_func_array([$this->container->get($handler[0]), $handler[1]], $methodParams);

			if (isset($handler[2]) && $handler[2] instanceof \Closure)
			{
				$responseData = $handler[2]($responseData, $this->responseFactory, $this->container);
			}
		}

		// invoke by closure
		if ($handler instanceof \Closure)
		{
			$responseData = $handler($this->container);
		}

		// response
		if ($responseData instanceof ResponseInterface)
		{
			return $responseData;
		}

		return $this->responseFactory->create((string)$responseData);
	}

}
