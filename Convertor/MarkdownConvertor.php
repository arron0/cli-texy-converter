<?php
/**
 * Requires PHP Version 5.3 (min)
 *
 * @package
 * @subpackage
 * @author Tomáš Lembacher <tomas.lembacher@seznam.cz>
 * @license
 */

namespace Arron\Convertor;

/**
 * MarkdownConvertor class definition
 *
 * @package
 * @subpackage
 * @author Tomáš Lembacher <tomas.lembacher@seznam.cz>
 * @license
 */
class MarkdownConvertor implements IConvertor
{
	public function convert($input)
	{
		$texy = new TexyToMarkdown();
		return $texy->process($input);
	}
}
 