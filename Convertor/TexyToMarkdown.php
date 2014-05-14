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

use TexyBlockModule;
use TexyBlockParser;
use TexyBlockQuoteModule;
use TexyEmoticonModule;
use TexyFigureModule;
use TexyHandlerInvocation;
use TexyHeadingModule;
use TexyHorizLineModule;
use TexyHtml;
use TexyHtmlModule;
use TexyHtmlOutputModule;
use TexyImageModule;
use TexyLink;
use TexyLinkModule;
use TexyListModule;
use TexyLongWordsModule;
use TexyModifier;
use TexyParagraphModule;
use TexyPhraseModule;
use TexyScriptModule;
use TexyTableModule;
use TexyTypographyModule;

/**
 * TexyToMarkdown class definition
 *
 * @package
 * @subpackage
 * @author Tomáš Lembacher <tomas.lembacher@seznam.cz>
 * @license
 */
class TexyToMarkdown extends \Texy
{
	function __construct()
	{
		parent::__construct();

		self::$advertisingNotice = FALSE;

		$this->headingModule->top = 1;

		$this->allowedClasses = self::NONE;
		$this->allowedStyles = self::NONE;

		$this->allowed['block/default'] = TRUE;
		$this->allowed['block/pre'] = TRUE;
		$this->allowed['block/code'] = TRUE;
		$this->allowed['block/html'] = TRUE;
		$this->allowed['block/text'] = TRUE;
		$this->allowed['block/texysource'] = TRUE;
		$this->allowed['block/comment'] = TRUE;
		$this->allowed['block/div'] = TRUE;
		$this->allowed['blocks'] = TRUE;
		$this->allowed['blockquote'] = TRUE;
		$this->allowed['emoticon'] = TRUE;
		$this->allowed['heading/underlined'] = TRUE;
		$this->allowed['heading/surrounded'] = TRUE;
		$this->allowed['html/tag'] = FALSE;
		$this->allowed['html/comment'] = FALSE;
		$this->allowed['horizline'] = FALSE;
		$this->allowed['image'] = FALSE;
		$this->allowed['image/definition'] = FALSE;
		$this->allowed['image/hover'] = FALSE;
		$this->allowed['figure'] = FALSE;
		$this->allowed['link/reference'] = FALSE;
		$this->allowed['link/email'] = FALSE;
		$this->allowed['link/url'] = FALSE;
		$this->allowed['link/definition'] = FALSE;
		$this->allowed['list'] = FALSE;
		$this->allowed['list/definition'] = FALSE;
		$this->allowed['paragraph'] = FALSE;
		$this->allowed['table'] = FALSE;
		$this->allowed['typography'] = FALSE;
		$this->allowed['longwords'] = FALSE;
		$this->allowed['phrase/strong+em'] = FALSE;
		$this->allowed['phrase/strong'] = FALSE;
		$this->allowed['phrase/em'] = FALSE;
		$this->allowed['phrase/em-alt'] = FALSE;
		$this->allowed['phrase/em-alt2'] = FALSE;
		$this->allowed['phrase/ins'] = FALSE;
		$this->allowed['phrase/del'] = FALSE;
		$this->allowed['phrase/sup'] = FALSE;
		$this->allowed['phrase/sup-alt'] = FALSE;
		$this->allowed['phrase/sub'] = FALSE;
		$this->allowed['phrase/sub-alt'] = FALSE;
		$this->allowed['phrase/span'] = FALSE;
		$this->allowed['phrase/span-alt'] = FALSE;
		$this->allowed['phrase/cite'] = FALSE;
		$this->allowed['phrase/quote'] = FALSE;
		$this->allowed['phrase/acronym'] = FALSE;
		$this->allowed['phrase/acronym-alt'] = FALSE;
		$this->allowed['phrase/notexy'] = FALSE;
		$this->allowed['phrase/code'] = FALSE;
		$this->allowed['phrase/quicklink'] = FALSE;
		$this->allowed['phrase/wikilink'] = FALSE;
		$this->allowed['phrase/markdown'] = FALSE;
		$this->allowed['script'] = FALSE;

		$this->addHandler('heading', array($this, 'headingHandler'));
		$this->addHandler('block', array($this, 'blockHandler'));
		$this->addHandler('afterBlockquote', array($this, 'afterBlockquoteHandler'));
		$this->addHandler('emoticon', array($this, 'emoticonHandler'));
	}

	/**
	 * Create array of all used modules ($this->modules).
	 * This array can be changed by overriding this method (by subclasses)
	 */
	protected function loadModules()
	{
		// line parsing
		$this->scriptModule = new TexyScriptModule($this);
		$this->htmlModule = new TexyHtmlModule($this);
		$this->imageModule = new TexyImageModule($this);
		$this->phraseModule = new TexyPhraseModule($this);
		$this->linkModule = new TexyLinkModule($this);
		$this->emoticonModule = new TexyEmoticonModule($this);

		// block parsing
		$this->paragraphModule = new TexyParagraphModule($this);
		$this->blockModule = new TexyBlockModule($this);
		$this->figureModule = new TexyFigureModule($this);
		$this->horizLineModule = new TexyHorizLineModule($this);
		$this->blockQuoteModule = new TexyBlockQuoteModule($this);
		$this->tableModule = new TexyTableModule($this);
		$this->headingModule = new TexyHeadingModule($this);
		$this->listModule = new TexyListModule($this);

		// post process
		//$this->typographyModule = new TexyTypographyModule($this);
		//$this->longWordsModule = new TexyLongWordsModule($this);
		//$this->htmlOutputModule = new TexyHtmlOutputModule($this);
	}

	public function process($text, $singleLine = FALSE)
	{
		$s = parent::process($text, $singleLine);
		return self::unescapeHtml($s);
	}

	/**
	 * @TODO Add support for dynamic leveling
	 *
	 * @param TexyHandlerInvocation $invocation
	 * @param integer $level
	 * @param string $content
	 * @param TexyModifier $mod
	 * @param boolean $isSurrounded
	 *
	 * @return string
	 */
	public function headingHandler(TexyHandlerInvocation $invocation, $level, $content, TexyModifier $mod, $isSurrounded)
	{
		$headingSign = '';
		for ($i = $level; $i >= 0; $i--) {
			$headingSign .= '#';
		}
		return $headingSign . ' ' . $content . "\n\n";
	}

	/**
	 * @param TexyHandlerInvocation $invocation handler invocation
	 * @param string $blocktype block type
	 * @param string $content text to highlight
	 * @param string $lang language
	 * @param TexyModifier $modifier modifier
	 *
	 * @return string
	 */
	public function blockHandler(TexyHandlerInvocation $invocation, $blocktype, $content, $lang, $modifier)
	{
		switch($blocktype) {
			case 'block/html' :
				$lang = 'html';
				break;
		}

		$content = trim($content, "\n");
		return "```$lang\n$content\n```\n\n";
	}

	/**
	 * @param TexyBlockParser $parser
	 * @param TexyHtml $el
	 * @param TexyModifier $mod
	 */
	public function afterBlockquoteHandler(TexyBlockParser $parser, TexyHtml $el, TexyModifier $mod)
	{
		$el->setName(NULL);
		foreach($el->getChildren() as $children) {
			$children->setName(NULL);
		}
		$s = $el->toString($this);
		$el->setText('> ' . $s. "\n\n");
	}

	/**
	 * @param TexyHandlerInvocation $invocation
	 * @param string $emoticon
	 * @param string $raw
	 *
	 * @return string
	 */
	public function emoticonHandler(TexyHandlerInvocation $invocation, $emoticon, $raw)
	{
		return $emoticon;
	}
}
 