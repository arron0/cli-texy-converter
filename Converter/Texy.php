<?php

namespace Arron\Converter;

use FSHL\Highlighter;
use FSHL\Lexer;
use FSHL\Output;
use Texy\Helpers;
use Texy\Link;
use Texy\HandlerInvocation;
use Texy\HtmlElement;
use Texy\Modifier;

class Texy extends \Texy\Texy
{
	public function __construct()
	{
		parent::__construct();

		$this->headingModule->top = 1;
		$this->headingModule->generateID = true;

		$this->addHandler('block', array($this, 'blockHandler'));
		$this->addHandler('script', array($this, 'scriptHandler'));
		$this->addHandler('phrase', array($this, 'phraseHandler'));

		$link = new Link('http://www.google.com/search?q=%s');
		$this->linkModule->addReference('google', $link);

		$link = new Link('http://en.wikipedia.org/wiki/Special:Search?search=%s');
		$this->linkModule->addReference('wikipedia', $link);

		$link = new Link('http://php.net/%s');
		$this->linkModule->addReference('php', $link);
	}

	/**
	 * @param HandlerInvocation $invocation handler invocation
	 * @param string $cmd command
	 * @param array $args arguments
	 * @param string $raw arguments in raw format
	 *
	 * @return HtmlElement|string|FALSE
	 */
	public function scriptHandler($invocation, $cmd, $args, $raw)
	{
		return '';
	}

	public function phraseHandler(
		HandlerInvocation $invocation,
		string $phrase,
		string $content,
		Modifier $mod,
		?Link $link = null
	): mixed {
		if (!$link) {
			$el = $invocation->proceed();
			if ($el instanceof HtmlElement && $el->getName() !== 'a' && $el->getAttribute('title') !== null) {
				if (isset($el->attrs['class'])) {
					if (is_array($el->attrs['class'])) {
						$el->attrs['class'][] = 'about';
					} else {
						$el->attrs['class'] = [(string)$el->attrs['class'], 'about'];
					}
				} else {
					$el->attrs['class'] = ['about'];
				}
			}
			return $el;
		}

		return $invocation->proceed();
	}

	public function blockHandler(
		HandlerInvocation $invocation,
		string $blocktype,
		string $s,
		string $param,
		Modifier $mod
	): mixed {
		if ($blocktype !== 'block/code') {
			return $invocation->proceed();
		}

		$lang = ucfirst($param);
		$lexerClassName = 'FSHL\Lexer\\' . $param;
		if (!class_exists($lexerClassName)) {
			return $invocation->proceed();
		}

		$parser = new Highlighter(new Output\Html(), Highlighter::OPTION_TAB_INDENT);
		/** @var Lexer $lexer */
		$lexer = new $lexerClassName();
		$parser->setLexer($lexer);

		$content = Helpers::outdent($s);
		$content = $parser->highlight($content);
		$content = $this->protect($content, \Texy\Texy::CONTENT_BLOCK);

		$elPre = HtmlElement::el('pre');
		$mod->decorate($this, $elPre);
		$elPre->attrs['class'] = strtolower($lang);
		$elPre->create('code', $content);

		return $elPre;
	}
}
