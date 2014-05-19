<?php
/**
 * Requires PHP Version 5.3 (min)
 *
 * @package Arron
 * @subpackage Translator
 * @author Tomáš Lembacher <tomas.lembacher@seznam.cz>
 * @license MIT
 */

namespace Arron\Convertor;

use Symfony\Component\Console\Application;

require_once "vendor/autoload.php";

$application = new Application();
$application->add(new Convert());
$application->run();