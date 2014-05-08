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

use FSHL\Lexer\Cache\Html;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Latte;

/**
 * Convert class definition
 *
 * @package
 * @subpackage
 * @author Tomáš Lembacher <tomas.lembacher@seznam.cz>
 * @license
 */
class Convert extends Command
{
	protected function configure()
	{
		$this
				->setName('texy')
				->setDescription('Convert from texy.')
				->addArgument(
						'from',
						InputArgument::REQUIRED,
						'Texy source file.'
				)
				->addArgument(
						'target',
						InputArgument::REQUIRED,
						'Target file.'
				)
				->addOption(
						'template',
						't',
						InputOption::VALUE_REQUIRED,
						'Specifies the Latte template to use. There will be $content abailable in the template.'
				)
				->addOption(
						'force',
						'f',
						InputOption::VALUE_NONE,
						'Forcing script to overwrite any target file.'
				);
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$from = $input->getArgument('from');
		$to = $input->getArgument('target');

		if(file_exists($to) && !$input->getOption('force')) {
			throw new \InvalidArgumentException("File $to already exists. Use --force option to force rewrite it.");
		}

		$fileExtension = strtolower(end(explode('.',$to)));

		$translator = null;
		switch($fileExtension) {
			case 'html' :
			case 'htm' :
				$translator = new HtmlConvertor();
				break;

			case 'md' :
				$translator = new MarkdownConvertor();
				break;

			default:
				throw new \InvalidArgumentException("Destination format $fileExtension is not supported.");
		}


		if(!file_exists($from)) {
			throw new \InvalidArgumentException("File $from doesn't exist.");
		}

		$source = file_get_contents($from);

		$target = $translator->convert($source);

		if($template = $input->getOption('template')) {
			if(!file_exists($template)) {
				throw new \InvalidArgumentException("Template $template doesn't exist.");
			}

			$latte = new Latte\Engine;
			$params['content'] = $target;
			$target = $latte->renderToString($template, $params);
		}

		file_put_contents($to, $target);

		$output->writeln("Texy from $from converted to $to.");
	}
}
 