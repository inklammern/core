<?php

namespace Inkl\Core\Factories;

use Zend\Diactoros\Stream;

class StreamFactory {

	public function create($stream, $mode) {
		return new Stream($stream, $mode);
	}

}
