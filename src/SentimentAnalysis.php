<?php

namespace AfolabiAbass\App;

use Phpml\Classification\NaiveBayes;
use Phpml\Dataset\CsvDataset;
use Phpml\Dataset\ArrayDataset;
use Phpml\Exception\FileException;
use Phpml\FeatureExtraction\TokenCountVectorizer;
use Phpml\Tokenization\WordTokenizer;
use Phpml\FeatureExtraction\TfIdfTransformer;
use Phpml\CrossValidation\StratifiedRandomSplit;
use Phpml\Metric\Accuracy;

/**
 * Class SentimentAnalysis
 * @package AfolabiAbass\App
 */
class SentimentAnalysis {
    /**
     * @var array
     */
    protected $data = [];
    /**
     * @var NaiveBayes
     */
    protected $classifier;
    /**
     * @var string
     */
    protected $testDataPath = __DIR__  .'/../Tweets.csv';
    /**
     * @var string
     */
    protected $cleanedDataPath = __DIR__  .'/../CleanedTweets.csv';
    /**
     * @var CsvDataset
     */
    protected $loadedDataset;
    /**
     * @var array
     */
    protected $loadedSamples = [];
    /**
     * @var ArrayDataset
     */
    protected $preparedDataset;

    protected $trainingSamples;
    protected $trainingLabels;
    protected $testSamples;
    protected $testLabels;
    protected $predictedLabels;

    /**
     * SentimentAnalysis constructor.
     */
    public function __construct()
    {
        $this->classifier = new NaiveBayes();
    }

    /**
     * @throws FileException
     * @throws \Phpml\Exception\InvalidArgumentException
     */
    public function process()
    {
        $handler = $this->openFile($this->testDataPath, 'rb');

        $rows = [];
        while (($data = fgetcsv($handler, 1000, ',')) !== false) {
            $rows[] = [$data[10], $data[1]];
        }
        fclose($handler);

        $handler = $this->openFile($this->cleanedDataPath, 'wb');
        foreach ($rows as $row) {
            fputcsv($handler, $row);
        }
        fclose($handler);

        return $this;
    }

    /**
     * @return $this
     * @throws FileException
     */
    public function load()
    {
        $dataset = new CsvDataset($this->cleanedDataPath, 1);
        $samples = [];
        foreach ($dataset->getSamples() as $sample) {
            $samples[] = $sample[0];
        }

        $this->loadedDataset = $dataset;
        $this->loadedSamples = $samples;

        return $this;
    }

    /**
     * @return $this
     * @throws \Phpml\Exception\InvalidArgumentException
     */
    public function prepare()
    {
        $vectorizer = new TokenCountVectorizer(new WordTokenizer());
        $vectorizer->fit($this->loadedSamples);
        $vectorizer->transform($this->loadedSamples);

        $tfIdfTransformer = new TfIdfTransformer();
        $tfIdfTransformer->fit($this->loadedSamples);
        $tfIdfTransformer->transform($this->loadedSamples);

        $this->preparedDataset = new ArrayDataset($this->loadedSamples, $this->loadedDataset->getTargets());

        return $this;
    }

    /**
     * @return $this
     * @throws \Phpml\Exception\InvalidArgumentException
     */
    public function split()
    {
        $randomSplit = new StratifiedRandomSplit($this->preparedDataset, 0.1);

        $this->trainingSamples = $randomSplit->getTrainSamples();
        $this->trainingLabels = $randomSplit->getTrainLabels();

        $this->testSamples = $randomSplit->getTestSamples();
        $this->testLabels = $randomSplit->getTestLabels();

        return $this;
    }

    /**
     * @param $filePath
     * @param $mode
     * @return bool|resource
     * @throws FileException
     */
    protected function openFile($filePath, $mode) {
        if (!file_exists($filePath)) {
            throw new FileException('File does not exist');
        }

        if (false === $openFile = fopen($filePath, $mode)) {
            $name = basename($filePath);
            throw new FileException("Cannot open file {$name}");
        }

        return $openFile;
    }

    /**
     * @return $this
     */
    public function train()
    {
        $this->classifier->train($this->trainingSamples, $this->trainingLabels);

        return $this;
    }

    /**
     * @return $this
     */
    public function predict()
    {
        $this->predictedLabels = $this->classifier->predict($this->testSamples);

        return $this;
    }

    public function getAccuracy()
    {
        echo 'Accuracy: '.Accuracy::score($this->testLabels, $this->predictedLabels);
    }


}

