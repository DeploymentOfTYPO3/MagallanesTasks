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
        return 'TYPO3 create artifact';
    }

    public function run()
    {
        $toPath = $this->getConfig()->deployment('from');
        $srcPath = $toPath . '/../..';

        if (!is_dir($toPath)) {
            mkdir($toPath, 0777, true);
        }

        $excludesList = array_map(function ($element) {
            return '--exclude ' . escapeshellarg($element);
        }, [
            'deployment',
            'fileadmin',
            'uploads',
            'typo3temp',
            'composer.phar',
            'composer.lock',
            '/composer.json',
            'atlassian-ide-plugin.xml',
            'typo3conf/LocalConfiguration.php',
            '.idea',
            '.git*',
            'node_modules',
            '.mage',
            'bower_components',
            'Vagrantfile',
            '.vagrant',
            '*.md',
            '.editorconfig',
            'logging_*',
            'deploy.sh',
            '/bin',
            '/typo3',
            '/index.php',
        ]);

        $command = 'rsync -a -l --delete --force ' . escapeshellarg($srcPath) .
            ' ' . escapeshellarg($toPath) . ' ' . implode(' ', $excludesList);

        $this->runCommandLocal($command, $output);

        if (trim($output) !== '') {
            throw new ErrorWithMessageException($output);
        }

        $file =
            $toPath .
            '/opcache-free-' . $this->getConfig()->getReleaseId() . '.php';

        $code = <<<EOF
<?php
 if (function_exists('opcache_reset')) {
    opcache_reset();
}
@unlink(__FILE__);
EOF;

        $success = !!file_put_contents($file, ltrim($code), LOCK_EX);

        if (!$success) {
            throw new ErrorWithMessageException('Cannot write file ' . $file);
        }

        $file = $toPath . '/release-' . $this->getConfig()->getReleaseId() . '.sh';

        $opcacheFile = 'opcache-free-' . $this->getConfig()->getReleaseId() . '.php';
        $frontendUrl = rtrim($this->getConfig()->deployment('http-frontend'), '/') . '/' . $opcacheFile;
        $documentRoot = $this->getConfig()->deployment('document-root');
        $typo3cmsCmd = './typo3cms %s';

        $deployToDirectory = rtrim($this->getConfig()->deployment('to'), '/')
            . '/' . $this->getConfig()->release('directory', 'releases')
            . '/' . $this->getConfig()->getReleaseId();

        $opcacheFrom = escapeshellarg($deployToDirectory . '/' . $opcacheFile);
        $opcacheTo = escapeshellarg($documentRoot . '/' . $opcacheFile);

        $script = [

            // Change into document root
            'cd ' . escapeshellarg($documentRoot),

            // Migrate schema
            sprintf($typo3cmsCmd, 'database:updateschema "*.add,*.change,*.clear"'),

            // Flush caches
            sprintf($typo3cmsCmd, 'cache:flush'),
            '(find ' . escapeshellarg($documentRoot . '/typo3temp/Cache/') . ' -type f -delete || true)',
            '(chmod -fR 0777 ' . escapeshellarg($documentRoot . '/typo3temp/Cache/') . '  || true)',

            // Purge opcache
            'mv ' . $opcacheFrom . ' ' . $opcacheTo,
            'curl -k -s ' . escapeshellarg($frontendUrl),

            // Delete release helper files
            'rm -f ' . $opcacheTo . ' ' . $opcacheFrom,
            'rm -f ' . escapeshellarg($deployToDirectory . '/release-' . $this->getConfig()->getReleaseId() . '.sh')
        ];

        $success = !!file_put_contents($file, implode("\n\n", $script), LOCK_EX);

        if (!$success) {
            throw new ErrorWithMessageException('Cannot write file ' . $file);
        }

        return true;
    }
}
