<?php
/**
 * @license GPL-2.0-only
 *
 * Modified by learndash on 02-October-2024 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace LearnDash\Certificate_Builder\Mpdf\File;

class LocalContentLoader implements \LearnDash\Certificate_Builder\Mpdf\File\LocalContentLoaderInterface
{

	public function load($path)
	{
		return file_get_contents($path);
	}

}
