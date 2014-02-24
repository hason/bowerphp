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
use Composer\Repository\Vcs\SvnDriver as BaseSvnDriver;
use Composer\Downloader\TransportException;

/**
 * @author Jordi Boggiano <j.boggiano@seld.be>
 * @author Till Klampaeckel <till@php.net>
 */
class SvnDriver extends BaseSvnDriver
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
        $identifier = '/' . trim($identifier, '/') . '/';

        if ($res = $this->cache->read($identifier.'.json')) {
            $this->infoCache[$identifier] = JsonFile::parseJson($res);
        }

        if (!isset($this->infoCache[$identifier])) {
            preg_match('{^(.+?)(@\d+)?/$}', $identifier, $match);
            if (!empty($match[2])) {
                $path = $match[1];
                $rev = $match[2];
            } else {
                $path = $identifier;
                $rev = '';
            }

            try {
                $resource = $path.'bower.json';
                $output = $this->execute('svn cat', $this->baseUrl . $resource . $rev);
                if (!trim($output)) {
                    return;
                }
            } catch (\RuntimeException $e) {
                throw new TransportException($e->getMessage());
            }

            $bower = JsonFile::parseJson($output, $this->baseUrl . $resource . $rev);

            if (!isset($bower['time'])) {
                $output = $this->execute('svn info', $this->baseUrl . $path . $rev);
                foreach ($this->process->splitLines($output) as $line) {
                    if ($line && preg_match('{^Last Changed Date: ([^(]+)}', $line, $match)) {
                        $date = new \DateTime($match[1], new \DateTimeZone('UTC'));
                        $bower['time'] = $date->format('Y-m-d H:i:s');
                        break;
                    }
                }
            }

            $this->cache->write($identifier.'.json', json_encode($bower));
            $this->infoCache[$identifier] = $bower;
        }

        return $this->infoCache[$identifier];
    }
}
