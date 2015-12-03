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

use Mage\Task\AbstractTask;
use Mage\Task\ErrorWithMessageException;

/**
 * Task runs commands remotely to finalize the release
 */
class Typo3Release extends AbstractTask
{
    /**
     * The name of the task
     *
     * @return string
     */
    public function getName()
    {
        return 'TYPO3 migrate and finalize';
    }

    public function run()
    {
        $script = rtrim($this->getConfig()->deployment('to'), '/')
            . '/' . $this->getConfig()->release('directory', 'releases')
            . '/' . $this->getConfig()->getReleaseId()
            . '/release-' . $this->getConfig()->getReleaseId() . '.sh';

        $cmd = '/bin/bash -eu ' . $script;

        $success = $this->runCommandRemote($cmd, $output, false);

        if (!$success) {
            throw new ErrorWithMessageException('Command failed: ' . $cmd . '; Output: ' . $output);
        }

        return true;
    }

}
