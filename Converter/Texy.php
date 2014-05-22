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

use FSHL\Highlighter;
use FSHL\Output;
use TexyLink;
use TexyHandlerInvocation;
use TexyHtml;
use TexyModifier;

/**
 * Texy class definition
 *
 * @package
 * @subpackage
 * @author Tomáš Lembacher <tomas.lembacher@seznam.cz>
 * @license
 */
class Texy extends \Texy
{
	function __construct()
	{
		parent::__construct();

		$this->headingModule->top = 1;
		$this->headingModule->generateID = TRUE;

		$this->addHandler('block', array($this, 'blockHandler'));
		$this->addHandler('script', array($this, 'scriptHandler'));
		$this->addHandler('phrase', array($this, 'phraseHandler'));

		$link = new TexyLink('http://www.google.com/search?q=%s');
		$this->linkModule->addReference('google', $link);

		$link = new TexyLink('http://en.wikipedia.org/wiki/Special:Search?search=%s');
		$this->linkModule->addReference('wikipedia', $link);

		$link = new TexyLink('http://php.net/%s');
		$this->linkModule->addReference('php', $link);
	}

	/**
	 * @param TexyHandlerInvocation $invocation handler invocation
	 * @param string $cmd command
	 * @param array $args arguments
	 * @param string $raw arguments in raw format
	 *
	 * @return TexyHtml|string|FALSE
	 */
	public function scriptHandler($invocation, $cmd, $args, $raw)
	{
		return '';
	}

	/**
	 * @param TexyHandlerInvocation $invocation handler invocation
	 * @param string $phrase
	 * @param string $content
	 * @param TexyModifier $modifier
	 * @param TexyLink $link
	 *
	 * @return TexyHtml|string|FALSE
	 */
	public function phraseHandler($invocation, $phrase, $content, $modifier, $link)
	{
		if (!$link) {
			$el = $invocation->proceed();
			if ($el instanceof TexyHtml && $el->getName() !== 'a' && $el->title !== NULL) {
				$el->class[] = 'about';
			}
			return $el;
		}

		return $invocation->proceed();
	}

	/**
	 * User handler for code block
	 *
	 * @param TexyHandlerInvocation $invocation handler invocation
	 * @param string $blocktype block type
	 * @param string $content text to highlight
	 * @param string $lang language
	 * @param TexyModifier $modifier modifier
	 *
	 * @return TexyHtml
	 */
	function blockHandler($invocation, $blocktype, $content, $lang, $modifier)
	{
		if ($blocktype !== 'block/code') {
			return $invocation->proceed();
		}

		$lang = ucfirst($lang);
		$lexerClassName = 'FSHL\Lexer\\' . $lang;
		if(!class_exists($lexerClassName)) {
			return $invocation->proceed();
		}

		$parser = new Highlighter(new Output\Html(), Highlighter::OPTION_TAB_INDENT);
		$parser->setLexer(new $lexerClassName);

		$content = Texy::outdent($content);
		$content = $parser->highlight($content);
		$content = $this->protect($content, Texy::CONTENT_BLOCK);

		$elPre = TexyHtml::el('pre');
		if ($modifier) {
			$modifier->decorate($this, $elPre);
		}
		$elPre->attrs['class'] = strtolower($lang);

		$elCode = $elPre->create('code', $content);

		return $elPre;
	}
}
 