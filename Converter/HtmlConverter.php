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
 * HtmlConverter class definition
 *
 * @package
 * @subpackage
 * @author Tomáš Lembacher <tomas.lembacher@seznam.cz>
 * @license
 */
class HtmlConverter implements IConverter
{

	public function convert($input)
	{
		$convertor = new \Texy\Texy();
		return $convertor->process($input);
	}
}
 