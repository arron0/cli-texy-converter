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

use Texy\Modules\BlockModule;
use Texy\BlockParser;
use Texy\Modules\BlockQuoteModule;
use Texy\Modules\EmoticonModule;
use Texy\Modules\FigureModule;
use Texy\HandlerInvocation;
use Texy\Modules\HeadingModule;
use Texy\Modules\HorizLineModule;
use Texy\HtmlElement;
use Texy\Modules\HtmlModule;
use Texy\Modules\HtmlOutputModule;
use Texy\Image;
use Texy\Modules\ImageModule;
use Texy\Link;
use Texy\Modules\LinkModule;
use Texy\Modules\ListModule;
use Texy\Modules\LongWordsModule;
use Texy\Modifier;
use Texy\Modules\ParagraphModule;
use Texy\Modules\PhraseModule;
use Texy\Modules\ScriptModule;
use Texy\Modules\TableModule;
use Texy\Modules\TypographyModule;

/**
 * TexyToMarkdown class definition
 *
 * @package
 * @subpackage
 * @author Tomáš Lembacher <tomas.lembacher@seznam.cz>
 * @license
 */
class TexyToMarkdown extends \Texy\Texy
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
		$this->scriptModule = new ScriptModule($this);
		$this->htmlModule = new HtmlModule($this);
		$this->imageModule = new ImageModule($this);
		$this->phraseModule = new PhraseModule($this);
		$this->linkModule = new LinkModule($this);
		$this->emoticonModule = new EmoticonModule($this);

		// block parsing
		$this->paragraphModule = new ParagraphModule($this);
		$this->blockModule = new BlockModule($this);
		$this->figureModule = new FigureModule($this);
		$this->horizLineModule = new HorizLineModule($this);
		$this->blockQuoteModule = new BlockQuoteModule($this);
		$this->tableModule = new TableModule($this);
		$this->headingModule = new HeadingModule($this);
		$this->listModule = new ListModule($this);

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
	 * @param HandlerInvocation $invocation
	 * @param integer $level
	 * @param string $content
	 * @param Modifier $mod
	 * @param boolean $isSurrounded
	 *
	 * @return string
	 */
	public function headingHandler(HandlerInvocation $invocation, $level, $content, Modifier $mod, $isSurrounded)
	{
		$headingSign = '';
		for ($i = $level; $i >= 0; $i--) {
			$headingSign .= '#';
		}
		return $headingSign . ' ' . $content . "\n\n";
	}

	/**
	 * @param HandlerInvocation $invocation handler invocation
	 * @param string $blocktype block type
	 * @param string $content text to highlight
	 * @param string $lang language
	 * @param Modifier $modifier modifier
	 *
	 * @return string
	 */
	public function blockHandler(HandlerInvocation $invocation, $blocktype, $content, $lang, $modifier)
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
	 * @param BlockParser $parser
	 * @param HtmlElement $el
	 * @param Modifier $mod
	 */
	public function afterBlockquoteHandler(BlockParser $parser, HtmlElement $el, Modifier $mod)
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
	 * @param HandlerInvocation $invocation
	 * @param string $emoticon
	 * @param string $raw
	 *
	 * @return string
	 */
	public function emoticonHandler(HandlerInvocation $invocation, $emoticon, $raw)
	{
		return $emoticon;
	}

	/**
	 * @param HandlerInvocation $invocation
	 * @param $content
	 * @param Modifier $mod
	 *
	 * @return string
	 */
	public function paragraphHandler(HandlerInvocation $invocation, $content, Modifier $mod = NULL)
	{
		$el = HtmlElement::el();
		$el->parseLine($this, $content);
		$content = $el->getText(); // string
		return $content.  "\n\n";
	}

	/**
	 * @param HandlerInvocation $invocation
	 * @param HtmlElement $el
	 * @param boolean $isStart
	 * @param boolean $forceEmpty
	 *
	 * @return string
	 */
	public function htmlTagHandler(HandlerInvocation $invocation, HtmlElement $el, $isStart, $forceEmpty)
	{
		$result = $isStart ? $el->startTag() : $el->endTag();
		return $result;
	}

	/**
	 * @param HandlerInvocation $invocation
	 * @param string $content
	 *
	 * @return string
	 */
	public function htmlCommentHandler(HandlerInvocation $invocation, $content)
	{
		return '<!-- ' . $content . '-->';
	}

	/**
	 * @param HandlerInvocation $invocation
	 * @param string $type
	 * @param Modifier $mod
	 *
	 * @return string
	 */
	public function horizlineHandler(HandlerInvocation $invocation, $type, Modifier $mod)
	{
		return $type . "\n\n";
	}

	/**
	 * @param HandlerInvocation $invocation
	 * @param Image $image
	 * @param Link $link
	 *
	 * @return string
	 */
	public function imageHandler(HandlerInvocation $invocation, Image $image, Link $link = NULL)
	{
		//  ![Alt text](/path/to/img.jpg "Optional title")
		$altText = empty($image->modifier->title) ? $image->URL : $image->modifier->title;
		return "![$altText]({$image->URL})";
	}

	/**
	 * @param HandlerInvocation $invocation
	 * @param Image $image
	 * @param Link $link
	 * @param string $content
	 * @param Modifier $mod
	 *
	 * @return string
	 */
	public function figureHandler(HandlerInvocation $invocation, Image $image, $link, $content, Modifier $mod)
	{
		$altText = empty($image->modifier->title) ? $image->URL : $image->modifier->title;
		return "![$altText]({$image->URL} \"$content\")";
	}

	/**
	 * @param HandlerInvocation $invocation
	 * @param Link $link
	 * @param string $content
	 *
	 * @return string
	 */
	public function linkReferenceHandler(HandlerInvocation $invocation, Link $link, $content)
	{
		// [id]: http://example.com/  "Optional Title Here"
		$protectedLink = $this->protect($link->URL, self::CONTENT_TEXTUAL);
		$linkText = !empty($content) ? $content : (!empty($link->label) ? $link->label : $protectedLink);
		$markdownLink = "[{$linkText}]($protectedLink)";
		return $markdownLink;
	}

	/**
	 * @param HandlerInvocation $invocation
	 * @param Link $link
	 *
	 * @return string
	 */
	public function linkEmailHandler(HandlerInvocation $invocation, Link $link)
	{
		//raw email somewhere in the text. Leaving unchanged, let the markdown dwal with it
		return $link->raw;
	}

	/**
	 * @param HandlerInvocation $invocation
	 * @param Link $link
	 *
	 * @return string
	 */
	public function linkUrlHandler(HandlerInvocation $invocation, Link $link)
	{
		//raw url somewhere in the text. Leaving unchanged, let the markdown deal with it.
		return $link->raw;
	}

	/**
	 * @param HandlerInvocation $invocation
	 * @param string $phrase Phrase name
	 * @param string $content
	 * @param Modifier $mod
	 * @param Link $link
	 *
	 * @return string
	 */
	public function phraseHandler(HandlerInvocation $invocation, $phrase, $content, Modifier $mod, Link $link = NULL)
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
 