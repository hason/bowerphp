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

use Composer\Config;
use Composer\Downloader\TransportException;
use Composer\Json\JsonFile;
use Composer\Repository\Vcs\GithubDriver as ComposerGithubDriver;

/**
 * @author Martin Hasoň <martin.hason@gmail.com>
 */
class GitHubDriver extends ComposerGithubDriver
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
        if ($this->gitDriver) {
            return $this->gitDriver->getBowerInformation($identifier);
        }

        if (preg_match('{[a-f0-9]{40}}i', $identifier) && $res = $this->cache->read($identifier)) {
            $this->infoCache[$identifier] = JsonFile::parseJson($res);
        }

        if (!isset($this->infoCache[$identifier])) {
            $notFoundRetries = 2;
            while ($notFoundRetries) {
                try {
                    $resource = $this->getApiUrl() . '/repos/'.$this->owner.'/'.$this->repository.'/contents/bower.json?ref='.urlencode($identifier);
                    $bower = JsonFile::parseJson($this->getContents($resource));
                    if (empty($bower['content']) || $bower['encoding'] !== 'base64' || !($bower = base64_decode($bower['content']))) {
                        throw new \RuntimeException('Could not retrieve $bower.json from '.$resource);
                    }
                    break;
                } catch (TransportException $e) {
                    if (404 !== $e->getCode()) {
                        throw $e;
                    }

                    // TODO should be removed when possible
                    // retry fetching if github returns a 404 since they happen randomly
                    $notFoundRetries--;
                    $bower = false;
                }
            }

            if ($bower) {
                $bower = JsonFile::parseJson($bower, $resource);

                if (!isset($bower['time'])) {
                    $resource = $this->getApiUrl() . '/repos/'.$this->owner.'/'.$this->repository.'/commits/'.urlencode($identifier);
                    $commit = JsonFile::parseJson($this->getContents($resource), $resource);
                    $bower['time'] = $commit['commit']['committer']['date'];
                }
                if (!isset($bower['support']['source'])) {
                    $label = array_search($identifier, $this->getTags()) ?: array_search($identifier, $this->getBranches()) ?: $identifier;
                    $bower['support']['source'] = sprintf('https://%s/%s/%s/tree/%s', $this->originUrl, $this->owner, $this->repository, $label);
                }
                if (!isset($bower['support']['issues']) && $this->hasIssues) {
                    $bower['support']['issues'] = sprintf('https://%s/%s/%s/issues', $this->originUrl, $this->owner, $this->repository);
                }
            }

            if (preg_match('{[a-f0-9]{40}}i', $identifier)) {
                $this->cache->write($identifier, json_encode($bower));
            }

            $this->infoCache[$identifier] = $bower;
        }

        return $this->infoCache[$identifier];
    }
}
