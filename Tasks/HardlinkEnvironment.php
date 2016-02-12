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
class HardlinkEnvironment extends AbstractTask
{
    public function getName()
    {
        return 'Create hard environment files';
    }

    public function run()
    {
        $toPath = $this->getConfig()->deployment('from');
        $environment = $this->getConfig()->getEnvironment();

        if (!is_dir($toPath)) {
            throw new ErrorWithMessageException('TYPO3 artifact does not exist (yet).');
        }

        $fileList = $this->getParameter('files');
        $removeStatement = '';

        if (is_array($fileList) && !empty($fileList)) {
            $fileConfiguration = array_map(function ($element) {
                return escapeshellarg($element);
            }, $fileList);

            $removeStatement = ' ' . implode(' ', $fileConfiguration);
        }


        $command = 'cd ' . $toPath . ';rm ' . $removeStatement;


        $this->runCommandLocal($command, $output);

        if (trim($output) !== '') {
            throw new ErrorWithMessageException($output);
        }

        $symlinkStatement = '';
        if (is_array($fileList) && !empty($fileList)) {
            $symlinks = array();
            foreach($fileList as $element){
                $symlinks[] = 'cp ' . $element . '_' .$environment . ' ' . $element . ';';
            }

            $symlinkStatement = ' ' . implode(' ', $symlinks);
        }

        $command = 'cd ' . $toPath . ';' . $symlinkStatement;

        $this->runCommandLocal($command, $output);

        if (trim($output) !== '') {
            throw new ErrorWithMessageException($output);
        }

        return true;
    }
}