<?php

namespace Flyimg\App;

use Flyimg\App\OptionResolver\OptionResolverInterface;
use Imagine\Image\ImagineInterface;
use League\Flysystem\FileExistsException;
use League\Flysystem\MountManager;

class ImageManipulator
{
    /**
     * @var ImagineInterface
     */
    private $imagine;

    /**
     * @var OptionResolverInterface
     */
    private $optionsResolver;

    /**
     * @var MountManager
     */
    private $mountManager;

    /**
     * @var string
     */
    private $realPath;

    /**
     * @var string
     */
    private $localPrefix;

    /**
     * @var string
     */
    private $storePrefix;

    /**
     * @param ImagineInterface        $imagine
     * @param OptionResolverInterface $optionResolver
     * @param MountManager            $mountManager
     * @param string                  $realPath
     * @param string                  $localPrefix
     * @param string                  $storePrefix
     */
    public function __construct(
        ImagineInterface $imagine,
        OptionResolverInterface $optionResolver,
        MountManager $mountManager,
        string $realPath,
        string $localPrefix = 'local',
        string $storePrefix = 'store'
    ) {
        $this->imagine = $imagine;
        $this->optionsResolver = $optionResolver;
        $this->mountManager = $mountManager;
        $this->realPath = $realPath;
        $this->localPrefix = $localPrefix;
        $this->storePrefix = $storePrefix;
    }

    private function hash(string $filterSpec, string $sourceUrl): string
    {
        return $this->storePrefix . '://' . strtr(base64_encode(
            hash('sha256', $filterSpec, true) . hash('sha512', $sourceUrl, true)
        ), '+/=', '._-');
    }

    /**
     * @param string $filtersSpec
     * @param string $sourceUrl
     *
     * @return ShortLivedTemporaryFile
     *
     * @throws FileExistsException
     */
    public function execute(string $filtersSpec, string $sourceUrl): FileInterface
    {
        $hash = $this->hash($filtersSpec, $sourceUrl);

        if ($this->mountManager->has($hash)) {
            return new FileReference($this->mountManager, $hash);
        }

        $temporaryImage = $this->localCopy($sourceUrl);

        $source = $this->imagine->open((string) $temporaryImage);

        $source = $this->optionsResolver->resolve($filtersSpec)->execute($source);

        $source->save((string) $temporaryImage);

        $this->mountManager->copy($temporaryImage->toFlysystem(), $hash);

        return $temporaryImage;
    }

    /**
     * @param string $source
     *
     * @return ShortLivedTemporaryFile
     *
     * @throws FileExistsException
     */
    private function localCopy(string $source): ShortLivedTemporaryFile
    {
        $destination = new ShortLivedTemporaryFile($this->mountManager, $this->localPrefix, $this->realPath);

        $this->mountManager->copy($source, $destination->toFlysystem());

        return $destination;
    }
}
