<?php

namespace Tests\Core\Processor;

use Core\Processor\ImageProcessor;
use Core\Entity\Image\OutputImage;
use Core\Entity\ImageMetaInfo;
use Tests\Core\BaseTest;

/**
 * Class ImageProcessorTest
 * @package Tests\Core\Processor
 */
class ImageProcessorTest extends BaseTest
{
    const PNG_TEST_SQUARE_IMAGE = __DIR__.'/../../testImages/square-opaque-600.png';
    const PNG_TEST_LANDSCAPE_IMAGE = __DIR__.'/../../testImages/landscape-color-squares-900x600.png';
    const PNG_TEST_PORTRAIT_IMAGE = __DIR__.'/../../testImages/portrait-color-squares-600x900.png';
    const PNG_TEST_SMALL_SQUARE_IMAGE = __DIR__.'/../../testImages/square-opaque-200.png';
    const PNG_TEST_SMALL_LANDSCAPE_IMAGE = __DIR__.'/../../testImages/landscape-color-squares-300x200.png';
    const PNG_TEST_SMALL_PORTRAIT_IMAGE = __DIR__.'/../../testImages/portrait-color-squares-200x300.png';
    const OUTPUT_EXTENSIONS = ['png', 'jpg', 'webp', 'gif'];

    protected $imageProcessor;

    /**
     */
    public function setUp()
    {
        parent::setUp();
        $this->imageProcessor = $this->imageHandler->imageProcessor();
    }

    /**
     * @param string $options
     * @param string $expectedSize
     * @param string $sourceImage
     *
     * @dataProvider shrinkProvider
     */
    public function testShrinkSuccess(string $options, string $expectedSize, string $sourceImage)
    {
        $image = $this->imageHandler->processImage($options, $sourceImage);
        $this->generatedImage[] = $image;
        $this->assertFileExists($image->getPath());
        $imageDimensions = $this->imageInfo($image->getPath())[ImageMetaInfo::IMAGE_PROP_DIMENSIONS];
        $this->assertEquals($expectedSize, $imageDimensions);
    }

    /**
     * @param string $options
     * @param string $expectedSize
     * @param string $sourceImage
     *
     * @dataProvider expandProvider
     */
    public function testExpandSuccess(string $options, string $expectedSize, string $sourceImage)
    {
        $image = $this->imageHandler->processImage($options, $sourceImage);
        $this->generatedImage[] = $image;
        $this->assertFileExists($image->getPath());
        $imageDimensions = $this->imageInfo($image->getPath())['dimensions'];
        $this->assertEquals($expectedSize, $imageDimensions);
    }

    /**
     * Creates tests for resize to smaller sizes
     * @return array list of tests
     * The key is the test name, the items in the array are:
     * [options, expected output size, source image path]
     */
    public function shrinkProvider(): array
    {
        $resizingTests = [
            'Resize to width square' =>
                ['w_300', '300x300', self::PNG_TEST_SQUARE_IMAGE],
            'Resize to width landscape' =>
                ['w_300', '300x200', self::PNG_TEST_LANDSCAPE_IMAGE],
            'Resize to width portrait' =>
                ['w_300', '300x451', self::PNG_TEST_PORTRAIT_IMAGE],

            'Resize to height square' =>
                ['h_300', '300x300', self::PNG_TEST_SQUARE_IMAGE],
            'Resize to height landscape' =>
                ['h_300', '450x300', self::PNG_TEST_LANDSCAPE_IMAGE],
            'Resize to height portrait' =>
                ['h_300', '200x300', self::PNG_TEST_PORTRAIT_IMAGE],

            'Resize to width and height (landscape) square' =>
                ['w_300,h_150', '150x150', self::PNG_TEST_SQUARE_IMAGE],
            'Resize to width and height (landscape) landscape' =>
                ['w_300,h_150', '225x150', self::PNG_TEST_LANDSCAPE_IMAGE],
            'Resize to width and height (landscape) portrait' =>
                ['w_300,h_150', '100x150', self::PNG_TEST_PORTRAIT_IMAGE],

            'Resize to width and height (portrait) square' =>
                ['w_150,h_300', '150x150', self::PNG_TEST_SQUARE_IMAGE],
            'Resize to width and height (portrait) landscape' =>
                ['w_150,h_300', '150x100', self::PNG_TEST_LANDSCAPE_IMAGE],
            'Resize to width and height (portrait) portrait' =>
                ['w_150,h_300', '150x225', self::PNG_TEST_PORTRAIT_IMAGE],

            'Resize and Crop to square a square' =>
                ['w_300,h_300,c_1', '300x300', self::PNG_TEST_SQUARE_IMAGE],
            'Resize and Crop to square a landscape' =>
                ['w_300,h_300,c_1', '300x300', self::PNG_TEST_LANDSCAPE_IMAGE],
            'Resize and Crop to square a portrait' =>
                ['w_300,h_300,c_1', '300x300', self::PNG_TEST_PORTRAIT_IMAGE],

            'Resize and Crop to portrait (wider than portrait) square' =>
                ['w_250,h_300,c_1', '250x300', self::PNG_TEST_SQUARE_IMAGE],
            'Resize and Crop to portrait (wider than portrait) landscape' =>
                ['w_250,h_300,c_1', '250x300', self::PNG_TEST_LANDSCAPE_IMAGE],
            'Resize and Crop to portrait (wider than portrait) portrait' =>
                ['w_250,h_300,c_1', '250x300', self::PNG_TEST_PORTRAIT_IMAGE],

            'Resize and Crop to portrait (narrower than portrait) square' =>
                ['w_150,h_300,c_1', '150x300', self::PNG_TEST_SQUARE_IMAGE],
            'Resize and Crop to portrait (narrower than portrait) landscape' =>
                ['w_150,h_300,c_1', '150x300', self::PNG_TEST_LANDSCAPE_IMAGE],
            'Resize and Crop to portrait (narrower than portrait) portrait' =>
                ['w_150,h_300,c_1', '150x300', self::PNG_TEST_PORTRAIT_IMAGE],

            'Resize and Crop to landscape (taller than landscape) square' =>
                ['w_300,h_250,c_1', '300x250', self::PNG_TEST_SQUARE_IMAGE],
            'Resize and Crop to landscape (taller than landscape) landscape' =>
                ['w_300,h_250,c_1', '300x250', self::PNG_TEST_LANDSCAPE_IMAGE],
            'Resize and Crop to landscape (taller than landscape) portrait' =>
                ['w_300,h_250,c_1', '300x250', self::PNG_TEST_PORTRAIT_IMAGE],

            'Resize and Crop to landscape (shorter than landscape) square' =>
                ['w_300,h_150,c_1', '300x150', self::PNG_TEST_SQUARE_IMAGE],
            'Resize and Crop to landscape (shorter than landscape) landscape' =>
                ['w_300,h_150,c_1', '300x150', self::PNG_TEST_LANDSCAPE_IMAGE],
            'Resize and Crop to landscape (shorter than landscape) portrait' =>
                ['w_300,h_150,c_1', '300x150', self::PNG_TEST_PORTRAIT_IMAGE],
        ];

        return $this->addOutputExtensionsToTests($resizingTests);
    }

    /**
     * defines tests to check images don't expand by default
     * @return array Data provider array
     *
     * The key is the test name, the items in the array are:
     * [options, expected output size, source image path]
     */
    public function expandProvider(): array
    {
        return $this->addOutputExtensionsToTests(
            array_merge(
                [
                    'Expand to width square' =>
                        ['w_400', '200x200', self::PNG_TEST_SMALL_SQUARE_IMAGE],
                    'Expand to width landscape' =>
                        ['w_400', '300x200', self::PNG_TEST_SMALL_LANDSCAPE_IMAGE],
                    'Expand to width portrait' =>
                        ['w_400', '200x300', self::PNG_TEST_SMALL_PORTRAIT_IMAGE],

                    'Expand to height square' =>
                        ['h_400', '200x200', self::PNG_TEST_SMALL_SQUARE_IMAGE],
                    'Expand to height landscape' =>
                        ['h_400', '300x200', self::PNG_TEST_SMALL_LANDSCAPE_IMAGE],
                    'Expand to height portrait' =>
                        ['h_400', '200x300', self::PNG_TEST_SMALL_PORTRAIT_IMAGE],

                    'Expand to width and height (landscape) square' =>
                        ['w_400,h_300', '200x200', self::PNG_TEST_SMALL_SQUARE_IMAGE],
                    'Expand to width and height (landscape) landscape' =>
                        ['w_400,h_300', '300x200', self::PNG_TEST_SMALL_LANDSCAPE_IMAGE],
                    'Expand to width and height (landscape) portrait' =>
                        ['w_400,h_350', '200x300', self::PNG_TEST_SMALL_PORTRAIT_IMAGE],

                    'Expand to width and height (portrait) square' =>
                        ['w_320,h_400', '200x200', self::PNG_TEST_SMALL_SQUARE_IMAGE],
                    'Expand to width and height (portrait) landscape' =>
                        ['w_320,h_400', '300x200', self::PNG_TEST_SMALL_LANDSCAPE_IMAGE],
                    'Expand to width and height (portrait) portrait' =>
                        ['w_320,h_400', '200x300', self::PNG_TEST_SMALL_PORTRAIT_IMAGE],

                    'Expand and Crop to square a square' =>
                        ['w_400,h_400,c_1', '200x200', self::PNG_TEST_SMALL_SQUARE_IMAGE],
                    'Expand and Crop to square a landscape' =>
                        ['w_400,h_400,c_1', '300x200', self::PNG_TEST_SMALL_LANDSCAPE_IMAGE],
                    'Expand and Crop to square a portrait' =>
                        ['w_400,h_400,c_1', '200x300', self::PNG_TEST_SMALL_PORTRAIT_IMAGE],

                    'Expand and Crop to portrait (wider a.r. than portrait) square' =>
                        ['w_310,h_600,c_1', '200x200', self::PNG_TEST_SMALL_SQUARE_IMAGE],
                    'Expand and Crop to portrait (wider a.r. than portrait) landscape' =>
                        ['w_310,h_600,c_1', '300x200', self::PNG_TEST_SMALL_LANDSCAPE_IMAGE],
                    'Expand and Crop to portrait (wider a.r. than portrait) portrait' =>
                        ['w_310,h_600,c_1', '200x300', self::PNG_TEST_SMALL_PORTRAIT_IMAGE],

                    'Expand and Crop to portrait (narrower a.r. than portrait) square' =>
                        ['w_320,h_640,c_1', '200x200', self::PNG_TEST_SMALL_SQUARE_IMAGE],
                    'Expand and Crop to portrait (narrower a.r. than portrait) landscape' =>
                        ['w_320,h_640,c_1', '300x200', self::PNG_TEST_SMALL_LANDSCAPE_IMAGE],
                    'Expand and Crop to portrait (narrower a.r. than portrait) portrait' =>
                        ['w_320,h_400,c_1', '200x300', self::PNG_TEST_SMALL_PORTRAIT_IMAGE],

                    'Expand and Crop to landscape (taller a.r. than landscape) square' =>
                        ['w_380,h_320,c_1', '200x200', self::PNG_TEST_SMALL_SQUARE_IMAGE],
                    'Expand and Crop to landscape (taller a.r. than landscape) landscape' =>
                        ['w_380,h_320,c_1', '300x200', self::PNG_TEST_SMALL_LANDSCAPE_IMAGE],
                    'Expand and Crop to landscape (taller a.r. than landscape) portrait' =>
                        ['w_380,h_320,c_1', '200x300', self::PNG_TEST_SMALL_PORTRAIT_IMAGE],

                    'Expand and Crop to landscape (shorter a.r. than landscape) square' =>
                        ['w_600,h_300,c_1', '200x200', self::PNG_TEST_SMALL_SQUARE_IMAGE],
                    'Expand and Crop to landscape (shorter a.r. than landscape) landscape' =>
                        ['w_600,h_300,c_1', '300x200', self::PNG_TEST_SMALL_LANDSCAPE_IMAGE],
                    'Expand and Crop to landscape (shorter a.r. than landscape) portrait' =>
                        ['w_600,h_300,c_1', '200x300', self::PNG_TEST_SMALL_PORTRAIT_IMAGE],

                ],
                $this->partialCropTestProvider()
            )
        );
    }

    /**
     * Test partial crops without expanding
     * @return array
     */
    protected function partialCropTestProvider(): array
    {
        return [
            'Expand and partial crop to square a landscape' =>
                ['w_250,h_250,c_1', '250x200', self::PNG_TEST_SMALL_LANDSCAPE_IMAGE],
            'Expand and partial crop to square a portrait' =>
                ['w_250,h_250,c_1', '200x250', self::PNG_TEST_SMALL_PORTRAIT_IMAGE],
            'Expand and partial crop to portrait (wider a.r. than portrait) square' =>
                ['w_190,h_220,c_1', '190x200', self::PNG_TEST_SMALL_SQUARE_IMAGE],
            'Expand and partial crop to portrait (wider a.r. than portrait) landscape' =>
                ['w_210,h_300,c_1', '210x200', self::PNG_TEST_SMALL_LANDSCAPE_IMAGE],
            'Expand and partial crop to portrait (wider a.r. than portrait) portrait' =>
                ['w_210,h_290,c_1', '200x290', self::PNG_TEST_SMALL_PORTRAIT_IMAGE],
            'Expand and partial crop to portrait (narrower a.r. than portrait) square' =>
                ['w_190,h_300,c_1', '190x200', self::PNG_TEST_SMALL_SQUARE_IMAGE],
            'Expand and partial crop to portrait (narrower a.r. than portrait) landscape' =>
                ['w_190,h_350,c_1', '190x200', self::PNG_TEST_SMALL_LANDSCAPE_IMAGE],
            'Expand and partial crop to portrait (narrower a.r. than portrait) portrait' =>
                ['w_190,h_350,c_1', '190x300', self::PNG_TEST_SMALL_PORTRAIT_IMAGE],
            'Expand and partial crop to landscape (taller a.r. than landscape) square' =>
                ['w_250,h_190,c_1', '200x190', self::PNG_TEST_SMALL_SQUARE_IMAGE],
            'Expand and partial crop to landscape (taller a.r. than landscape) landscape' =>
                ['w_290,h_210,c_1', '290x200', self::PNG_TEST_SMALL_LANDSCAPE_IMAGE],
            'Expand and partial crop to landscape (taller a.r. than landscape) portrait' =>
                ['w_290,h_210,c_1', '200x210', self::PNG_TEST_SMALL_PORTRAIT_IMAGE],
            'Expand and partial crop to landscape (shorter a.r. than landscape) square' =>
                ['w_320,h_190,c_1', '200x190', self::PNG_TEST_SMALL_SQUARE_IMAGE],
            'Expand and partial crop to landscape (shorter a.r. than landscape) landscape' =>
                ['w_320,h_190,c_1', '300x190', self::PNG_TEST_SMALL_LANDSCAPE_IMAGE],
            'Expand and partial crop to landscape (shorter a.r. than landscape) portrait' =>
                ['w_320,h_190,c_1', '200x190', self::PNG_TEST_SMALL_PORTRAIT_IMAGE],
        ];
    }

    protected function addOutputExtensionsToTests(array $transformationsList): array
    {
        $tests = [];

        foreach ($transformationsList as $key => $test) {
            foreach (self::OUTPUT_EXTENSIONS as $extension) {
                $tests[$key.' with '.$extension] = [$test[0].',o_'.$extension, $test[1], $test[2]];
            }
        }

        return $tests;
    }

    /**
     * Returns an associative array with the info of an image in a given path.
     *
     * @param  string $filePath
     *
     * @return array
     */
    protected function imageInfo($filePath): array
    {
        $imgInfo = new ImageMetaInfo($filePath);

        return $imgInfo->info();
    }
}
