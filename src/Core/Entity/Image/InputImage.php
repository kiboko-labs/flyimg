<?php

namespace Core\Entity\Image;

use Core\Entity\OptionsBag;
use Core\Entity\ImageMetaInfo;
use Imagine\Image\ImageInterface;
use Imagine\Imagick\Image;

/**
 * @deprecated see \Flyimg\Image\ImageInterface
 */
class InputImage
{
    /**
     * @var ImageInterface
     */
    private $source;

    /**
     * @var OptionsBag
     */
    private $optionsBag;

    /**
     * @var string
     */
    protected $sourceImagePath;

    /**
     * @var string
     */
    protected $sourceImageMimeType;

    /**
     * @var ImageMetaInfo
     */
    protected $sourceImageInfo;

    /**
     * @param OptionsBag $optionsBag
     * @param string     $sourceImageUrl
     */
    public function __construct(OptionsBag $optionsBag, string $sourceImageUrl)
    {
        $this->source = (new \Imagick())->readImage($sourceImageUrl);

        $this->optionsBag = $optionsBag;

        $this->sourceImagePath = sys_get_temp_dir() . 'original-'.
            (md5(
                $optionsBag->get('face-crop-position').
                $sourceImageUrl
            ));
        $this->saveToTemporaryFile();
        $this->sourceImageInfo = new ImageMetaInfo($this->sourceImagePath);
    }

    /**
     * Remove Input Image
     */
    public function removeInputImage()
    {
        if (file_exists($this->sourceImagePath())) {
            unlink($this->sourceImagePath());
        }
    }

    /**
     * @param string $key
     *
     * @return string
     */
    public function extractKey(string $key): string
    {
        $value = '';
        if ($this->optionsBag->has($key)) {
            $value = $this->optionsBag->get($key);
            $this->optionsBag->remove($key);
        }

        return is_null($value) ? '' : $value;
    }

    /**
     * @return OptionsBag
     */
    public function optionsBag(): OptionsBag
    {
        return $this->optionsBag;
    }

    /**
     * @return string
     */
    public function sourceImageUrl(): string
    {
        return $this->source->url();
    }

    /**
     * @return string
     */
    public function sourceImagePath(): string
    {
        return $this->sourceImagePath;
    }

    /**
     * @return ImageInterface
     */
    public function file(): ImageInterface
    {
        return $this->source;
    }

    /**
     * @return string
     */
    public function sourceImageMimeType(): string
    {
        if (isset($this->sourceImageMimeType)) {
            return $this->sourceImageMimeType;
        }

        $this->sourceImageMimeType = $this->sourceImageInfo->mimeType();
        return $this->sourceImageMimeType;
    }

    public function sourceImageInfo()
    {
        return $this->sourceImageInfo;
    }
}
