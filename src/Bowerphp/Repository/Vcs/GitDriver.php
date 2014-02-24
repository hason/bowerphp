<?php

/*
 * This file is part of Composer.
 *
 * (c) Nils Adermann <naderman@naderman.de>
 *     Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bowerphp\Repository\Vcs;

use Composer\Repository\Vcs\GitDriver as ComposerGitDriver;
use Composer\Json\JsonFile;
use Composer\Config;

/**
 * @author Martin Hasoň <martin.hason@gmail.com>
 */
class GitDriver extends ComposerGitDriver
{
    /**
     * {@inheritDoc}
     */
    public function getComposerInformation($identifier)
    {
        throw new \BadMethodCallException();
    }

    /**
     * {@inheritDoc}
     */
    public function getBowerInformation($identifier)
    {
        if (preg_match('{[a-f0-9]{40}}i', $identifier) && $res = $this->cache->read($identifier)) {
            $this->infoCache[$identifier] = JsonFile::parseJson($res);
        }

        if (!isset($this->infoCache[$identifier])) {
            $resource = sprintf('%s:bower.json', escapeshellarg($identifier));
            $this->process->execute(sprintf('git show %s', $resource), $bower, $this->repoDir);

            if (!trim($bower)) {
                return;
            }

            $bower = JsonFile::parseJson($bower, $resource);

            if (!isset($bower['time'])) {
                $this->process->execute(sprintf('git log -1 --format=%%at %s', escapeshellarg($identifier)), $output, $this->repoDir);
                $date = new \DateTime('@'.trim($output), new \DateTimeZone('UTC'));
                $bower['time'] = $date->format('Y-m-d H:i:s');
            }

            if (preg_match('{[a-f0-9]{40}}i', $identifier)) {
                $this->cache->write($identifier, json_encode($bower));
            }

            $this->infoCache[$identifier] = $bower;
        }

        return $this->infoCache[$identifier];
    }
}
