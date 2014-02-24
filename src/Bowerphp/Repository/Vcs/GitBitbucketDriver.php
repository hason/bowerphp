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
use Composer\Json\JsonFile;
use Composer\IO\IOInterface;
use Composer\Repository\Vcs\GitBitbucketDriver as BaseGitBitbucketDriver;

/**
 * @author Per Bernhardt <plb@webfactory.de>
 */
class GitBitbucketDriver extends BaseGitBitbucketDriver
{
    /**
     * {@inheritDoc}
     */
    public function getComposerInformation($identifier)
    {
        if (!isset($this->infoCache[$identifier])) {
            $resource = $this->getScheme() . '://bitbucket.org/'.$this->owner.'/'.$this->repository.'/raw/'.$identifier.'/composer.json';
            $composer = $this->getContents($resource);
            if (!$composer) {
                return;
            }

            $composer = JsonFile::parseJson($composer, $resource);

            if (!isset($composer['time'])) {
                $resource = $this->getScheme() . '://api.bitbucket.org/1.0/repositories/'.$this->owner.'/'.$this->repository.'/changesets/'.$identifier;
                $changeset = JsonFile::parseJson($this->getContents($resource), $resource);
                $composer['time'] = $changeset['timestamp'];
            }
            $this->infoCache[$identifier] = $composer;
        }

        return $this->infoCache[$identifier];
    }

    public function getBowerInformation($identifier)
    {
        if (!isset($this->infoCache[$identifier])) {
            $resource = $this->getScheme() . '://bitbucket.org/'.$this->owner.'/'.$this->repository.'/raw/'.$identifier.'/bower.json';
            $bower = $this->getContents($resource);
            if (!$bower) {
                return;
            }

            $bower = JsonFile::parseJson($bower, $resource);

            if (!isset($bower['time'])) {
                $resource = $this->getScheme() . '://api.bitbucket.org/1.0/repositories/'.$this->owner.'/'.$this->repository.'/changesets/'.$identifier;
                $changeset = JsonFile::parseJson($this->getContents($resource), $resource);
                $bower['time'] = $changeset['timestamp'];
            }
            $this->infoCache[$identifier] = $bower;
        }

        return $this->infoCache[$identifier];
    }
}
