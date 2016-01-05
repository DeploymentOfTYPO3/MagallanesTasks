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
 * Ensure the executable flag is set on given file(s)
 *
 * tasks:
 *    post-deploy:
 *       - executable:
 *          files:     # Property files can be a string or an array of strings
 *            - 'typo3_src/typo3/cli_dispatch.phpsh'
 *            - 'typo3conf/ext/typo3_console/Scripts/typo3cms'
 *
 * Can be used in the stages "pre-deploy" and "post-deploy"
 */
class Executable extends AbstractTask
{
    public function getName()
    {
        return 'Running executeable';
    }

    public function run()
    {
        if ($this->getStage() == self::STAGE_DEPLOY || $this->getStage() == self::STAGE_POST_RELEASE) {
            $defaultDir = 'to';
        } else {
            $defaultDir = 'from';
        }

        $files = $this->getParameter('files', $this->getConfig()->deployment($defaultDir));

        if (!$files) {
            throw new ErrorWithMessageException('No target file(s) for executable given');
        }

        if (is_array($files)) {
            $files = implode(' ', array_map(function ($dir) {
                return escapeshellarg($dir);
            }, $files));
        } else {
            $files = escapeshellarg($files);
        }

        $this->runCommand('chmod -f ugo+x ' . $files, $output);

        Console::log('Result of console call: ' . $output);

        if (trim($output) !== '') {
            throw new ErrorWithMessageException($output);
        }

        return true;
    }
}
