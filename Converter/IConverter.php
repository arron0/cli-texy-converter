<?php

namespace Arron\Converter;

interface IConverter
{
	public function convert(string $input): string;
}
