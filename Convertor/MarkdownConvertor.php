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

		//return $this->texy2markdown($input);
	}

	/*private function markdown2texy($s) // maybe @TODO someday
	{
		$s = preg_replace('/!\[([^\]]+)\]\(([^)]+?)( +"[^"]+")?\)/g', '[* $2 .($1) *]', $s);  // images
		$s = preg_replace('/\[([^\]]+)\]\(([^)]+?)( +"[^"]+")?\)/g', '"$1":$2', $s);  // links
		$s = preg_replace('/^```([^]*?)^```/gm', '/--$1\\--', $s); // /--
		return $s;
	}*/

	private function texy2markdown($s)
	{
		$s = $this->replaceLinksWithBrackets($s);
		$s = $this->replaceLinkWithoutBrackets($s);
		$s = $this->replaceBullets($s);
		$s = $this->replaceImages($s);
		$s = $this->replaceBocksOfCode($s);
		$s = $this->replaceBlocks($s);
		return $s;
	}

	protected function replaceLinksWithBrackets($input) {
		return preg_replace('/"([^"]+)":\[([^\]]+)\]/', '[$1]($2)', $input); // links with []
	}

	protected function replaceLinkWithoutBrackets($input) {
		return preg_replace('/"([^"]+)":((?!\[)[^\s]+[^:);,.!?\s])/', '[$1]($2)', $input); // links without []
	}

	protected function replaceBullets($input) {
		return preg_replace('/^(\d+)\) /m', '$1. ', $input); // bullets
	}

	protected function replaceImages($input) {
		return preg_replace('/\[\* *(\S+) *(?:\.\(([^)]+)\) *)?\*\]/', '![$2]($1)', $input); // images
	}

	protected function replaceBocksOfCode($input) {
		return preg_replace('/^\/--+code ((?s:.)*?)^\\\\--+/m', '```$1```', $input); // /---code
	}

	protected function replaceBlocks($input) {
		return preg_replace('/^\/--+((?s:.)*?)^\\\\--+/m', '```$1```', $input); // /--
	}
}
 