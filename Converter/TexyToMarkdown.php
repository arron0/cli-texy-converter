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
use TexyImage;
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
	public $phrasesTranslation = array(
			'phrase/strong+em' => array('_**', "**_"),
			'phrase/strong' => array('**', '**'),
			'phrase/em' => array('_', '_'),
			'phrase/em-alt' => array('*', '*'),
			'phrase/em-alt2' => array('*', '*'),
			'phrase/ins' => array('<ins>', '</ins>'),
			'phrase/del' => array('<del>', '</del>'),
			'phrase/sup' => array('<sup>', '</sup>'),
			'phrase/sup-alt' => array('<sup>', '</sup>'),
			'phrase/sub' => array('<sup>', '</sup>'),
			'phrase/sub-alt' => array('<sup>', '</sup>'),
			'phrase/span' => array('<span>', '</span>'),
			'phrase/span-alt' => array('<span>', '</span>'),
			'phrase/cite' => array('<cite>', '</cite>'),
			'phrase/quote' => array('>>', '<<'), // disabled
			'phrase/acronym' => array('', ''),
			'phrase/acronym-alt' => array('', ''),
			'phrase/notexy' => array('', ''),
			'phrase/code' => array('`', '`'), // disabled
			'phrase/quicklink' => array(),
			'phrase/wikilink' => array(),
			'phrase/markdown' => array(), // disabled
	);

	function __construct()
	{
		parent::__construct();

		$this->mergeLines = FALSE;

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
		$this->allowed['html/tag'] = TRUE;
		$this->allowed['html/comment'] = TRUE;
		$this->allowed['horizline'] = TRUE;
		$this->allowed['image'] = TRUE;
		$this->allowed['image/definition'] = TRUE;
		$this->allowed['image/hover'] = TRUE;
		$this->allowed['figure'] = TRUE;
		$this->allowed['link/reference'] = TRUE;
		$this->allowed['link/email'] = TRUE;
		$this->allowed['link/url'] = TRUE;
		$this->allowed['link/definition'] = TRUE;
		$this->allowed['list'] = FALSE; // unable to modify texy ouput, leaving unchanged
		$this->allowed['list/definition'] = FALSE;// unable to modify texy ouput, leaving unchanged
		$this->allowed['paragraph'] = TRUE;
		$this->allowed['table'] = FALSE; // unable to modify texy ouput, leaving unchanged
		$this->allowed['typography'] = FALSE; // don't want to mess with text, let mardown to deal with it
		$this->allowed['longwords'] = FALSE; // don't want to mess with text, let mardown to deal with it
		$this->allowed['phrase/strong+em'] = TRUE;
		$this->allowed['phrase/strong'] = TRUE;
		$this->allowed['phrase/em'] = TRUE;
		$this->allowed['phrase/em-alt'] = TRUE;
		$this->allowed['phrase/em-alt2'] = TRUE;
		$this->allowed['phrase/ins'] = TRUE;
		$this->allowed['phrase/del'] = TRUE;
		$this->allowed['phrase/sup'] = TRUE;
		$this->allowed['phrase/sup-alt'] = TRUE;
		$this->allowed['phrase/sub'] = TRUE;
		$this->allowed['phrase/sub-alt'] = TRUE;
		$this->allowed['phrase/span'] = TRUE;
		$this->allowed['phrase/span-alt'] = TRUE;
		$this->allowed['phrase/cite'] = TRUE;
		$this->allowed['phrase/quote'] = FALSE; // leave it unchanged
		$this->allowed['phrase/acronym'] = TRUE;
		$this->allowed['phrase/acronym-alt'] = TRUE;
		$this->allowed['phrase/notexy'] = TRUE;
		$this->allowed['phrase/code'] = FALSE; // leave it unchanged
		$this->allowed['phrase/quicklink'] = TRUE;
		$this->allowed['phrase/wikilink'] = TRUE;
		$this->allowed['phrase/markdown'] = FALSE; // leave it unchanged
		$this->allowed['script'] = FALSE; // leave it unchanged. Or shoud I filter it?

		$this->addHandler('heading', array($this, 'headingHandler'));
		$this->addHandler('block', array($this, 'blockHandler'));
		$this->addHandler('afterBlockquote', array($this, 'afterBlockquoteHandler'));
		$this->addHandler('emoticon', array($this, 'emoticonHandler'));
		$this->addHandler('paragraph', array($this, 'paragraphHandler'));
		$this->addHandler('htmlTag', array($this, 'htmlTagHandler'));
		$this->addHandler('htmlComment', array($this, 'htmlCommentHandler'));
		$this->addHandler('horizline', array($this, 'horizlineHandler'));
		$this->addHandler('image', array($this, 'imageHandler'));
		$this->addHandler('figure', array($this, 'figureHandler'));
		$this->addHandler('linkReference', array($this, 'linkReferenceHandler'));
		$this->addHandler('linkEmail', array($this, 'linkEmailHandler'));
		$this->addHandler('linkURL', array($this, 'linkUrlHandler'));
		$this->addHandler('phrase', array($this, 'phraseHandler'));
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
		// don't want even register their event, they are altering output
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
			if($children instanceof TexyHtml) {
				$children->setName(NULL);
			}
		}
		$s = $el->toString($this);
		$el->setText('> ' . $s);
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

	/**
	 * @param TexyHandlerInvocation $invocation
	 * @param $content
	 * @param TexyModifier $mod
	 *
	 * @return string
	 */
	public function paragraphHandler(TexyHandlerInvocation $invocation, $content, TexyModifier $mod = NULL)
	{
		$el = TexyHtml::el();
		$el->parseLine($this, $content);
		$content = $el->getText(); // string
		return $content.  "\n\n";
	}

	/**
	 * @param TexyHandlerInvocation $invocation
	 * @param TexyHtml $el
	 * @param boolean $isStart
	 * @param boolean $forceEmpty
	 *
	 * @return string
	 */
	public function htmlTagHandler(TexyHandlerInvocation $invocation, TexyHtml $el, $isStart, $forceEmpty)
	{
		$result = $isStart ? $el->startTag() : $el->endTag();
		return $result;
	}

	/**
	 * @param TexyHandlerInvocation $invocation
	 * @param string $content
	 *
	 * @return string
	 */
	public function htmlCommentHandler(TexyHandlerInvocation $invocation, $content)
	{
		return '<!-- ' . $content . '-->';
	}

	/**
	 * @param TexyHandlerInvocation $invocation
	 * @param string $type
	 * @param TexyModifier $mod
	 *
	 * @return string
	 */
	public function horizlineHandler(TexyHandlerInvocation $invocation, $type, TexyModifier $mod)
	{
		return $type . "\n\n";
	}

	/**
	 * @param TexyHandlerInvocation $invocation
	 * @param TexyImage $image
	 * @param TexyLink $link
	 *
	 * @return string
	 */
	public function imageHandler(TexyHandlerInvocation $invocation, TexyImage $image, TexyLink $link = NULL)
	{
		//  ![Alt text](/path/to/img.jpg "Optional title")
		$altText = empty($image->modifier->title) ? $image->URL : $image->modifier->title;
		return "![$altText]({$image->URL})";
	}

	/**
	 * @param TexyHandlerInvocation $invocation
	 * @param TexyImage $image
	 * @param TexyLink $link
	 * @param string $content
	 * @param TexyModifier $mod
	 *
	 * @return string
	 */
	public function figureHandler(TexyHandlerInvocation $invocation, TexyImage $image, $link, $content, TexyModifier $mod)
	{
		$altText = empty($image->modifier->title) ? $image->URL : $image->modifier->title;
		return "![$altText]({$image->URL} \"$content\")";
	}

	/**
	 * @param TexyHandlerInvocation $invocation
	 * @param TexyLink $link
	 * @param string $content
	 *
	 * @return string
	 */
	public function linkReferenceHandler(TexyHandlerInvocation $invocation, TexyLink $link, $content)
	{
		// [id]: http://example.com/  "Optional Title Here"
		$protectedLink = $this->protect($link->URL, self::CONTENT_TEXTUAL);
		$linkText = !empty($content) ? $content : (!empty($link->label) ? $link->label : $protectedLink);
		$markdownLink = "[{$linkText}]($protectedLink)";
		return $markdownLink;
	}

	/**
	 * @param TexyHandlerInvocation $invocation
	 * @param TexyLink $link
	 *
	 * @return string
	 */
	public function linkEmailHandler(TexyHandlerInvocation $invocation, TexyLink $link)
	{
		//raw email somewhere in the text. Leaving unchanged, let the markdown dwal with it
		return $link->raw;
	}

	/**
	 * @param TexyHandlerInvocation $invocation
	 * @param TexyLink $link
	 *
	 * @return string
	 */
	public function linkUrlHandler(TexyHandlerInvocation $invocation, TexyLink $link)
	{
		//raw url somewhere in the text. Leaving unchanged, let the markdown deal with it.
		return $link->raw;
	}

	/**
	 * @param TexyHandlerInvocation $invocation
	 * @param string $phrase Phrase name
	 * @param string $content
	 * @param TexyModifier $mod
	 * @param TexyLink $link
	 *
	 * @return string
	 */
	public function phraseHandler(TexyHandlerInvocation $invocation, $phrase, $content, TexyModifier $mod, TexyLink $link = NULL)
	{
		if ($link) {
			return $this->linkReferenceHandler($invocation, $link, $content);
		}

		if($phrase === 'phrase/acronym' || $phrase === 'phrase/acronym-alt') {
			return "$content ({$mod->title})"; // pure translation to pharenses
			//return "*[$content]: {$mod->title}"; // may work with some acronyme module to markdown (not with github)
		}

		if($phrase !== 'phrase/wikilink' && $phrase !== 'phrase/quicklink') {
			return $this->phrasesTranslation[$phrase][0] . $content . $this->phrasesTranslation[$phrase][1];
		}
		return $content;
	}
}
 