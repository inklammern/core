<?php

namespace Inkl\Core\Senders;

use Psr\Http\Message\ResponseInterface;

class ResponseSender {

	public function send(ResponseInterface $response) {

		$statusCode = $response->getStatusCode();
		$reasonPhrase = $response->getReasonPhrase();
		$protocolVersion = $response->getProtocolVersion();
		header("HTTP/{$protocolVersion} $statusCode $reasonPhrase");
		foreach ($response->getHeaders() as $name => $values) {
			if (strtolower($name) === 'set-cookie') {
				foreach ($values as $cookie) {
					header(sprintf('Set-Cookie: %s', $cookie), false);
				}
				break;
			}
			header(sprintf('%s: %s', $name, $response->getHeaderLine($name)));
		}
		echo $response->getBody();
	}

}
