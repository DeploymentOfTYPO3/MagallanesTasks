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
 * Clear OP cache
 */
class ClearOpCache extends AbstractTask
{
    public function getName()
    {
        return 'Clear OPcache';
    }

    public function run()
    {
        $url = $this->getFrontendUrl();
        $clearFile = '/opcache-free-' . $this->getConfig()->getReleaseId() . '.php';
        $file = rtrim($this->getConfig()->deployment('document-root'), '/') . $clearFile;

        // Create file
        $code = '<?php opcache_reset();';
        $command = sprintf('echo \'%s\' > %s', $code, $file);
        $this->runCommandRemote($command, $output, false);

        // Call file
        $command = 'curl -k -s ' . escapeshellarg($url . $clearFile);
        $this->runCommandRemote($command, $output, false);

        // Remove file
        $command = sprintf('rm %s', $file);
        $this->runCommandRemote($command, $output, false);

        return true;
    }

    /**
     * @return string
     * @throws ErrorWithMessageException
     */
    protected function getFrontendUrl()
    {
        $url = $this->getParameter('frontend-url');
        if (empty($url)) {
            throw new ErrorWithMessageException('No frontend-url defined!');
        }
        $url = rtrim($url, '/');

        return $url;
    }
}
