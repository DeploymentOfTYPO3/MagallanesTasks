<?php
namespace Task;

/*
 * This file is part of the DeploymentOfTypo3 project
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use Mage\Console;
use Mage\Task\AbstractTask;
use Mage\Task\ErrorWithMessageException;

/**
 * Create the artifact to be deployed
 */
class Typo3Console extends AbstractTask
{
    public function getName()
    {
        return 'Running TYPO3 console';
    }

    public function run()
    {
        if ($this->getParameter('copyEntryPoint')) {
            $command = sprintf('cd %s && cp typo3conf/ext/typo3_console/Scripts/typo3cms .', $this->getConfig()->deployment('document-root'));
            $this->runCommandRemote($command, $output, false);
        }

        $command = $this->getParameter('command');
        if (empty($command)) {
            throw new ErrorWithMessageException('No command given! Use something like "typo3-console: {command: backend:lock}" ');
        }


        $command = sprintf('cd %s && ./typo3cms %s', $this->getConfig()->deployment('document-root'), $command);
        $response = $this->runCommandRemote($command, $output, false);
        Console::output('Result of console call: ' . $response);

        return true;
    }
}
