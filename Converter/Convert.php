<?php

namespace Arron\Converter;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Latte;

class Convert extends Command
{
	protected function configure(): void
	{
		$this
				->setName('texy')
				->setDescription('Convert from Texy.')
				->addArgument(
					'from',
					InputArgument::REQUIRED,
					'Texy source file.'
				)
				->addArgument(
					'to',
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
		$to = $input->getArgument('to');

		if (!is_string($from)) {
			$type = gettype($from);
			throw new \InvalidArgumentException("Parameter 'from' has to be string $type given.");
		}

		if (!is_string($to)) {
			$type = gettype($to);
			throw new \InvalidArgumentException("Parameter 'to' has to be string $type given.");
		}

		if (file_exists($to) && !$input->getOption('force')) {
			throw new \InvalidArgumentException("Target file $to already exists. Use --force option to force rewrite it.");
		}

		$explodedFilename = explode('.', $to);
		$fileExtension = strtolower(end($explodedFilename));

		$translator = null;
		switch ($fileExtension) {
			case 'html':
			case 'htm':
				$translator = new HtmlConverter();
				break;

			case 'md':
				$translator = new MarkdownConverter();
				break;

			default:
				throw new \InvalidArgumentException("Destination format '$fileExtension' is not supported.");
		}


		if (!file_exists($from)) {
			throw new \InvalidArgumentException("Source file $from doesn't exist.");
		}

		$source = file_get_contents($from);

		if ($source) {
			$target = $translator->convert($source);
		} else {
			$target = '';
		}

		$template = $input->getOption('template');
		if (!is_string($template)) {
			$type = gettype($template);
			throw new \InvalidArgumentException("Parameter 'template' has to be string $type given.");
		}

		if ($template) {
			if (!file_exists($template)) {
				throw new \InvalidArgumentException("Template $template doesn't exist.");
			}

			$latte = new Latte\Engine();
			$params['content'] = $target;
			$target = $latte->renderToString($template, $params);
		}

		file_put_contents($to, $target);

		$output->writeln("Texy from $from converted to $to.");
		return self::SUCCESS;
	}
}
