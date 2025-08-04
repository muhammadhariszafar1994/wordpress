<?php
/**
 * @license GPL-2.0-only
 *
 * Modified by learndash on 02-October-2024 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace LearnDash\Certificate_Builder\Mpdf\Http;

use LearnDash\Certificate_Builder\Psr\Http\Message\RequestInterface;

interface ClientInterface
{

	public function sendRequest(RequestInterface $request);

}
