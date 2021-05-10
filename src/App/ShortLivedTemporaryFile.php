<?php

namespace Flyimg\App;

use League\Flysystem\FileNotFoundException;
use League\Flysystem\MountManager;

class ShortLivedTemporaryFile implements FileInterface
{
    /**
     * @var MountManager
     */
    private $mountManager;

    /**
     * @var string
     */
    private $filename;

    /**
     * @var string
     */
    private $localPath;

    /**
     * @var string
     */
    private $path;

    /**
     * @param MountManager $mountManager
     * @param string       $localPath
     * @param string       $path
     */
    public function __construct(MountManager $mountManager, string $localPath, string $path)
    {
        $this->mountManager = $mountManager;
        $this->filename = uniqid('flyimg.');
        $this->localPath = $localPath;
        $this->path = $path;
    }

    public function __clone()
    {
        $this->filename = uniqid('flyimg.');
    }

    public function __destruct()
    {
        try {
            $this->mountManager->delete($this->toFlysystem());
        } catch (FileNotFoundException $exception) {
            // FIXME: Do not ignore silently
        }
    }

    public function toFlysystem(): string
    {
        return $this->localPath . '://' . $this->filename;
    }

    public function __toString()
    {
        return $this->path . '/' . $this->filename;
    }

    public function mimeType(): string
    {
        return $this->mountManager->getMimetype($this->toFlysystem());
    }

    public function read(): string
    {
        return $this->mountManager->read($this->toFlysystem());
    }

    public function readStream()
    {
        return $this->mountManager->readStream($this->toFlysystem());
    }
}
