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
 * Create the artifact to be deployed
 */
class Typo3Artifact extends AbstractTask
{
    public function getName()
    {
        return 'Create TYPO3 artifact';
    }

    public function run()
    {
        $toPath = $this->getConfig()->deployment('from');
        $src    = $this->getConfig()->deployment('src');

        if (is_string($src) && is_dir($src)) {
            $srcPath = $src;
        } else {
            $srcPath = $toPath . '/../..';
        }

        if (!is_dir($toPath)) {
            mkdir($toPath, 0777, true);
        }

        $excludeList = $this->getParameter('excludes');
        $excludeStatement = '';

        if (is_array($excludeList) && !empty($excludeList)) {
            $excludeConfiguration = array_map(function ($element) {
                return '--exclude ' . escapeshellarg($element);
            }, $excludeList);

            $excludeStatement = ' ' . implode(' ', $excludeConfiguration);
        }

        $command = 'rsync -a -l --delete --force ' . escapeshellarg($srcPath) .
            ' ' . escapeshellarg($toPath) . $excludeStatement;

        $this->runCommandLocal($command, $output);

        if (trim($output) !== '') {
            throw new ErrorWithMessageException($output);
        }

        return true;
    }
}
