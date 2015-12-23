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
 * Change file and directory permissions using the chmod utility
 *
 * tasks:
 *    post-deploy:
 *       - change-mode:
 *          user: 'rwX'   # This is the default
 *          group: 'rwX'  # Allow write to group
 *          group: 'rX'   # Only read to others, this is the default
 *          directory:    # Directory can be a string or array of strings
 *            typo3_src
 *            typo3conf
 *
 * This is a more lightweight alternative to the
 * filesystem/apply-facls and filesystem/permissions task
 *
 * Can be used in the stages "pre-deploy" and "post-deploy"
 */
class ChangeMode extends AbstractTask
{
    public function getName()
    {
        return 'Running chmod';
    }

    public function run()
    {
        if ($this->getStage() == self::STAGE_DEPLOY || $this->getStage() == self::STAGE_POST_RELEASE) {
            $defaultDir = 'to';
        } else {
            $defaultDir = 'from';
        }

        $directory = $this->getParameter('directory', $this->getConfig()->deployment($defaultDir));

        if (!$directory) {
            throw new ErrorWithMessageException('No target directory for chown given');
        }

        if (is_array($directory)) {
            $directory = implode(' ', array_map(function ($dir) {
                return escapeshellarg($dir);
            }, $directory));
        } else {
            $directory = escapeshellarg($directory);
        }

        $user  = $this->getParameter('user', 'rwX');
        $group = $this->getParameter('user', 'rX');
        $other = $this->getParameter('user', 'rX');

        $command = sprintf(
            'chmod -Rf u=%s,g=%s,o=%s %s',
            $user,
            $group,
            $other,
            $directory
        );

        $response = $this->runCommand($command, $output);
        Console::log('Result of console call: ' . $output);

        return $response;
    }
}
