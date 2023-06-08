<?php

/**
 * Requires PHP Version 5.3 (min)
 *
 * @package
 * @subpackage
 * @author Tomáš Lembacher <tomas.lembacher@seznam.cz>
 * @license
 */

namespace Arron\Converter;

/**
 * MarkdownConverter class definition
 *
 * @package
 * @subpackage
 * @author Tomáš Lembacher <tomas.lembacher@seznam.cz>
 * @license
 */
class MarkdownConverter implements IConverter
{
	public function convert($input)
	{
		$texy = new TexyToMarkdown();
		return $texy->process($input);
	}
}
