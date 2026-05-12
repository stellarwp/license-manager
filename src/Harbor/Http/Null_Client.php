<?php declare( strict_types=1 );

namespace LiquidWeb\Harbor\Http;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Nyholm\Psr7\Response;
use LiquidWeb\Harbor\Portal\Error_Code;

final class Null_Client implements ClientInterface {
	/**
	 * @inheritDoc
	 */
	public function sendRequest( RequestInterface $request): ResponseInterface {
		return new Response(
			500,
			[],
			'',
			'1.1',
			Error_Code::API_COMMUNICATIONS_NOT_PERMITTED
		);
	}
}
