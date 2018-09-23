<?php

namespace Flyimg\Http;

use Flyimg\Http\Security\SecurityRuleInterface;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;
use League\Flysystem\NotSupportedException;
use League\Flysystem\UnreadableFileException;
use League\Flysystem\Util\MimeType;

class Adapter implements AdapterInterface
{
    /**
     * @var SecurityRuleInterface
     */
    private $securityRule;

    /**
     * @var bool
     */
    private $isSecure;

    /**
     * @var array
     */
    private $context;

    /**
     * @var bool
     */
    private $supportsHead;

    /**
     * @param SecurityRuleInterface $securityRule
     * @param bool $isSecure
     * @param array $context
     * @param bool $supportsHead
     */
    public function __construct(
        SecurityRuleInterface $securityRule,
        array $context = [],
        bool $isSecure = false,
        bool $supportsHead = true
    ) {
        $this->securityRule = $securityRule;
        $this->isSecure = $isSecure;
        $this->context = $context;
        $this->supportsHead = $supportsHead;

        if ($this->isSecure) {
            $this->context += [
                'ssl' => [
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                    'SNI_enabled' => true,
                    'disable_compression' => true,
                ],
            ];
        }
    }

    public function write($path, $contents, Config $config)
    {
        throw new NotSupportedException(
            'Filesystem is readonly, could not write here.'
        );
    }

    public function writeStream($path, $resource, Config $config)
    {
        throw new NotSupportedException(
            'Filesystem is readonly, could not write here.'
        );
    }

    public function update($path, $contents, Config $config)
    {
        throw new NotSupportedException(
            'Filesystem is readonly, could not write here.'
        );
    }

    public function updateStream($path, $resource, Config $config)
    {
        throw new NotSupportedException(
            'Filesystem is readonly, could not write here.'
        );
    }

    public function rename($path, $newpath)
    {
        throw new NotSupportedException(
            'Filesystem is readonly, could not write here.'
        );
    }

    public function copy($path, $newpath)
    {
        throw new NotSupportedException(
            'Filesystem is readonly, could not write here.'
        );
    }

    public function delete($path)
    {
        throw new NotSupportedException(
            'Filesystem is readonly, could not write here.'
        );
    }

    public function deleteDir($dirname)
    {
        throw new NotSupportedException(
            'Filesystem is readonly, could not write here.'
        );
    }

    public function createDir($dirname, Config $config)
    {
        throw new NotSupportedException(
            'Filesystem is readonly, could not write here.'
        );
    }

    public function setVisibility($path, $visibility)
    {
        throw new NotSupportedException(
            'Filesystem is readonly, could not write here.'
        );
    }

    private function head(string $path)
    {
        $defaults = stream_context_get_options(
            stream_context_get_default()
        );

        $options = $this->context;

        if ($this->supportsHead) {
            $options['http']['method'] = 'HEAD';
        }

        stream_context_set_default($options);

        $headers = get_headers($this->url($path), 1);

        stream_context_set_default($defaults);

        if ($headers === false || strpos($headers[0], ' 200') === false) {
            return false;
        }

        return array_change_key_case($headers);
    }

    private function checkVisibility(string $path): string
    {
        return $this->securityRule->isAllowedSourceUrl($this->url($path)) ?
            AdapterInterface::VISIBILITY_PUBLIC :
            AdapterInterface::VISIBILITY_PRIVATE;
    }

    public function has($path)
    {
        $url = $this->url($path);
        if (!$this->securityRule->isAllowedSourceUrl($url)) {
            throw new UnreadableFileException(
                'This remote file location is denied according to the HTTP security restrictions.'
            );
        }

        return (bool) $this->head($path);
    }

    public function read($path)
    {
        $url = $this->url($path);
        if (!$this->securityRule->isAllowedSourceUrl($url)) {
            throw new UnreadableFileException(
                'This remote file location is denied according to the HTTP security restrictions.'
            );
        }

        $context = stream_context_create($this->context);
        $contents = file_get_contents($url, false, $context);

        if ($contents === false) {
            return false;
        }

        return [
            'path' => $path,
            'contents' => $contents,
        ];
    }

    public function readStream($path)
    {
        $url = $this->url($path);
        if (!$this->securityRule->isAllowedSourceUrl($url)) {
            throw new UnreadableFileException(
                'This remote file location is denied according to the HTTP security restrictions.'
            );
        }

        $context = stream_context_create($this->context);
        $stream = fopen($url, 'rb', false, $context);

        if ($stream === false) {
            return false;
        }

        return [
            'path' => $path,
            'stream' => $stream,
        ];
    }

    public function listContents($directory = '', $recursive = false)
    {
        return [];
    }

    public function getMetadata($path)
    {
        if (false === $headers = $this->head($path)) {
            return false;
        }

        return [
            'type' => 'file'
        ] + $this->refreshMetadata($path, $headers);
    }

    public function getSize($path)
    {
        return $this->getMetadata($path);
    }

    public function getMimetype($path)
    {
        return $this->getMetadata($path);
    }

    public function getTimestamp($path)
    {
        return $this->getMetadata($path);
    }

    public function getVisibility($path)
    {
        return [
            'path' => $path,
            'visibility' => $this->checkVisibility($path),
        ];
    }

    private function url(string $path)
    {
        return ($this->isSecure ? 'https://' : 'http://') . $path;
    }

    /**
     * @param array $headers
     *
     * @return \DateTimeInterface|null
     */
    private function refreshTimestamp(array $headers): \DateTimeInterface
    {
        if (isset($headers['last-modified'])) {
            return \DateTimeImmutable::createFromFormat(
                \DateTimeInterface::RFC7231,
                $headers['last-modified']
            );
        }

        return null;
    }

    /**
     * @param string $path
     * @param array  $headers
     *
     * @return array
     */
    private function refreshMetadata($path, array $headers)
    {
        $metadata = [
            'path' => $path,
            'visibility' => $this->checkVisibility($path),
            'mimetype' => $this->refreshMimeType($path, $headers),
        ];

        if (false !== ($timestamp = $this->refreshTimestamp($headers))) {
            $metadata['timestamp'] = $timestamp;
        }

        if (isset($headers['content-length']) && is_numeric($headers['content-length'])) {
            $metadata['size'] = (int) $headers['content-length'];
        }

        return $metadata;
    }

    /**
     * @param string $path
     * @param array  $headers
     *
     * @return string
     */
    private function refreshMimeType($path, array $headers)
    {
        if (isset($headers['content-type'])) {
            [$mimetype] = explode(';', $headers['content-type'], 2);
            return trim($mimetype);
        }

        return MimeType::detectByFilename(parse_url($path, PHP_URL_PATH));
    }
}
