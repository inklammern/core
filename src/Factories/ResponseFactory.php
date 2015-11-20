<?php

namespace Inkl\Core\Factories;

use Zend\Diactoros\Response;

class ResponseFactory {

	/** @var StreamFactory */
	private $streamFactory;

	/**
	 * ResponseFactory constructor.
	 * @param StreamFactory $streamFactory
	 */
	public function __construct(StreamFactory $streamFactory) {
		$this->streamFactory = $streamFactory;
	}

	public function create($content, $status = 200, array $headers = []) {
		return new Response($this->createStreamFromString($content), $status, $headers);
	}

	protected function createStreamFromString($string) {

		$stream = $this->streamFactory->create('php://memory', 'w');
		$stream->write($string);
		$stream->rewind();

		return $stream;
	}

}
