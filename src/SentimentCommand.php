<?php

namespace AfolabiAbass\App;

use Symfony\Component\Console\Command\Command;
use Phpml\Exception\FileException;
use Phpml\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SentimentCommand
 * @package AfolabiAbass\App
 */
class SentimentCommand extends Command
{
    protected function configure()
    {
        $this->setName('sentiment:run')
            ->setDescription('Sentiment Analysis on Test Data');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws FileException
     * @throws InvalidArgumentException
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        (new SentimentAnalysis)
            ->process()
            ->load()
            ->prepare()
            ->split()
            ->train()
            ->predict()
            ->getAccuracy();
    }
}
