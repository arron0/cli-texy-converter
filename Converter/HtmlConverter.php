<?php

namespace Arron\Converter;

use Texy\Texy;

class HtmlConverter implements IConverter
{
	public function convert(string $input): string
	{
		$convertor = new Texy();
		return $convertor->process($input);
	}
}
