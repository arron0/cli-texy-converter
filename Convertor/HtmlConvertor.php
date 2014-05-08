<?php
/**
 * Requires PHP Version 5.3 (min)
 *
 * @package
 * @subpackage
 * @author Tomáš Lembacher <tomas.lembacher@seznam.cz>
 * @license
 */

namespace Arron\Translator;


/**
 * HtmlConvertor class definition
 *
 * @package
 * @subpackage
 * @author Tomáš Lembacher <tomas.lembacher@seznam.cz>
 * @license
 */
class HtmlConvertor implements IConvertor
{

	public function convert($input)
	{
		$convertor = new Texy();
		return $convertor->process($input);
	}
}
 