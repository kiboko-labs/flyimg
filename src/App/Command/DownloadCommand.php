<?php

namespace Flyimg\App\Command;

use Flyimg\App\OptionResolver\FilterResolver;
use Flyimg\FilterExpression\Lexer\Lexer;
use Flyimg\Image\FaceDetection\FacedetectShell;
use Imagine\Imagick\Imagine;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DownloadCommand extends ContainerAwareCommand
{
    /**
     * @var string|null The default command name
     */
    protected static $defaultName = 'flyimg:download';

    protected function configure()
    {
        $this
            ->addArgument('url', InputArgument::IS_ARRAY)
            ->addOption('filter', 'f', InputOption::VALUE_OPTIONAL, 'Apply filters to the specified image.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $imagine = new Imagine();
        $filters = FilterResolver::buildStandard($imagine, new FacedetectShell());

        foreach ($input->getArgument('url') as $imageSrc) {
            $source = $imagine->open($imageSrc);

            $source = $filters->resolve($input->getOption('filter'))->execute($source);

            $source->save($filename = (__DIR__ . '/' . uniqid('flyimg.') . '.png'));

            $output->writeln($filename);
        }
    }
}
