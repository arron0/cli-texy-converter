<?php

namespace unitTests;

class TexyToMarkdownUnitTest extends \Arron\TestIt\TestCase
{
	/**
	 * @return object
	 */
	protected function createTestObject()
	{
		return new \Arron\Converter\MarkdownConverter();
	}

	/**
	 * @dataProvider conversionDataProvider
	 */
	public function testConversion($texySourceFile, $markdownExpectedResultFile)
	{
		$texySourceFile = ASSETS_DIR . '/' . $texySourceFile;
		$markdownExpectedResultFile = ASSETS_DIR . '/' . $markdownExpectedResultFile;

		if (!file_exists($texySourceFile)) {
			$this->fail("The source file $texySourceFile does not exist.");
		}

		if (!file_exists($markdownExpectedResultFile)) {
			$this->fail("The expected result file $markdownExpectedResultFile does not exist.");
		}

		$texySource = file_get_contents($texySourceFile);
		$markdownResult = file_get_contents($markdownExpectedResultFile);

		$returnedResult = $this->getTestObject()->convert($texySource);

		$this->assertEquals($markdownResult, $returnedResult);
	}

	public function conversionDataProvider()
	{
		return array(
				array('blockquotes.texy', 'blockquotes.md'),
				array('blocks.texy', 'blocks.md'),
				array('emoticons.texy', 'emoticons.md'),
				array('headings.texy', 'headings.md'),
				array('horizlines.texy', 'horizlines.md'),
				array('html.texy', 'html.md'),
				array('images.texy', 'images.md'),
				array('links.texy', 'links.md'),
				array('lists.texy', 'lists.md'),
				array('paragraphs.texy', 'paragraphs.md'),
				array('phrases.texy', 'phrases.md'),
		);
	}
}
