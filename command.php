#!/usr/bin/env php
<?php

declare(strict_types=1);

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;
use App\PathHelper;
use League\Csv\Reader;
use League\Csv\Writer;

require __DIR__ . '/vendor/autoload.php';

$app = new SingleCommandApplication();
$app->setName('senki/trait-module-color')
    ->setVersion('0.1.0')
    ->addArgument('input-files', InputArgument::REQUIRED, 'Input File/Folder')
    ->addArgument('output-folder', InputArgument::REQUIRED, 'Output Folder')
    ->setCode(function (InputInterface $input, OutputInterface $output) {
        // Init.
        $chunk         = 100;
        $suffix        = '-markers.vip.csv';
        $path          = new PathHelper();
        /** @var string */
        $inputFilesArg = $input->getArgument('input-files');
        $inputFiles    = $path->isDir($inputFilesArg) ? $path->collectFromDir($inputFilesArg) : [$inputFilesArg];
        /** @var string */
        $outputFolder  = $input->getArgument('output-folder');

        $output->writeln(sprintf('Converting %d file(s)', count($inputFiles)));
        foreach ($inputFiles as $file) {
            $filename         = $path->getFilename($file);
            $inputFile        = $path->makeAbsolute($file);
            $outputFile       = $path->makeAbsolute($outputFolder . DIRECTORY_SEPARATOR . $filename);
            $nameParts        = explode('_', substr($filename, 0, -strlen($suffix)));
            $newColumns = [
                'Trait'        => $nameParts[0],
                'Module color' => $nameParts[1],
            ];

            // CSV Manipulation
            $output->writeln(sprintf('Reading %s', $inputFile));
            $reader = Reader::createFromPath($inputFile, 'r');
            $writer = Writer::createFromPath($outputFile, 'w+');

            $headers = $reader->fetchOne();
            $reader->setHeaderOffset(0);
            $records = $reader->getRecords();
            $headers = array_merge(array_keys($newColumns), $headers);

            $writer->setFlushThreshold($chunk);
            $writer->insertOne($headers);
            $output->writeln(sprintf('Writing %s', $outputFile));
            foreach ($records as $record) {
                $record = array_merge($newColumns, $record);
                $writer->insertOne($record);
            }
            $output->writeln('  Done');
        }
    });

    $app->run();
