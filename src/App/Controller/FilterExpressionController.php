<?php

namespace Flyimg\App\Controller;

use Flyimg\App\Filesystem;
use Flyimg\App\OptionResolver\FilterResolver;
use Flyimg\Image\FaceDetection\FacedetectShell;
use Imagine\Exception\InvalidArgumentException;
use Imagine\Imagick\Imagine;
use League\Flysystem\FileExistsException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class FilterExpressionController extends Controller
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @param string $filtersSpec
     * @param string $imageSrc
     *
     * @return Response
     */
    public function uploadAction(string $filtersSpec, string $imageSrc = null): Response
    {
        $imagine = new Imagine();
        $resolver = FilterResolver::buildStandard($imagine, new FacedetectShell());

        try {
            $imagePath = $this->filesystem->localCopy($imageSrc);

            $source = $imagine->open($imagePath);
        } catch (FileExistsException $e) {
            return new Response($e->getMessage(), 500);
        } catch (InvalidArgumentException $e) {
            return new Response('', 404);
        }

        $source = $resolver->resolve($filtersSpec)->execute($source);

        return new Response($source->get($source->metadata()['format']), 200, [
            'Content-Type' => 'image/png',
        ]);
    }

    /**
     * @param string $filtersSpec
     * @param string $imageSrc
     *
     * @return Response
     */
    public function pathAction(string $filtersSpec, string $imageSrc = null): Response
    {
        return new Response($imageSrc);
    }
}
