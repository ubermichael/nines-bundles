<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UtilBundle\Command;

use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use Twig\Environment;

class FontDownload extends Command {
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Environment
     */
    private $twig;
    protected static $defaultName = 'nines:fonts:download';

    /**
     * {@inheritdoc}
     */
    protected function configure() : void {
        $this
            ->setDescription('Fetch the fonts defined in config/fonts.yaml')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function render($variant, $accepted) {
        return $this->twig->render('@NinesUtil/font/font.scss.twig', [
            'name' => $variant['local'][0],
            'family' => $variant['fontFamily'],
            'locals' => $variant['local'],
            'weight' => $variant['fontWeight'],
            'formats' => $accepted,
            'style' => $variant['fontStyle'],
        ]);
    }

    protected function checkVariant($name, $variant, $styles, $weights) {
        if ( ! in_array($variant['fontStyle'], $styles, true)) {
            $this->logger->info('Skipping ' . $name . ' ' . $variant['fontStyle']);

            return false;
        }
        if ( ! in_array($variant['fontWeight'], $weights, true)) {
            $this->logger->info('Skipping ' . $name . ' ' . $variant['fontWeight']);

            return false;
        }

        return true;
    }

    protected function fetch($name, $variant, $formats, $filenameTemplate) {
        $client = new Client();
        $filenames = [];
        foreach ($formats as $format) {
            $callback = [
                'id' => $variant['local'][1],
                'style' => $variant['fontStyle'],
                'weight' => $variant['fontWeight'],
                'ext' => $format,
            ];

            $filename = preg_replace_callback('/\{(\w+)\}/', function ($matches) use ($callback) {
                return $callback[$matches[1]];
            }, $filenameTemplate);

            if (file_exists($filename)) {
                $this->logger->notice('Skipped downloading ' . $name . ' ' . $variant['fontStyle'] . ' ' . $variant['fontWeight'] . ' ' . $format);
            } else {
                $this->logger->notice('Downloading ' . $name . ' ' . $variant['fontStyle'] . ' ' . $variant['fontWeight'] . ' ' . $format);
                $client->get($variant[$format], [
                    'sink' => $filename,
                ]);
            }
            $filenames[$format] = $filename;
        }

        return $filenames;
    }

    protected function processFont($data, $styles, $weights, $formats, $filenameTemplate) {
        $sass = '';
        if ( ! file_exists(dirname($filenameTemplate))) {
            mkdir(dirname($filenameTemplate), 0755, true);
        }
        foreach ($data['variants'] as $variant) {
            $accepted = [];

            $name = $variant['local'][1];
            if ( ! $this->checkVariant($name, $variant, $styles, $weights)) {
                continue;
            }
            $accepted = array_merge($accepted, $this->fetch($name, $variant, $formats, $filenameTemplate));
            $sass .= $this->render($variant, $accepted);
        }

        return $sass;
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int {
        $config = Yaml::parseFile('config/fonts.yaml');
        $subsets = implode(',', $config['fonts']['subsets']);
        $families = $config['fonts']['families'];
        $filename = $config['fonts']['path'] . '/' . $config['fonts']['filename'];
        $formats = $config['fonts']['formats'];
        $client = new Client();
        $sass = '';
        foreach ($families as $id => $data) {
            $url = "https://google-webfonts-helper.herokuapp.com/api/fonts/{$id}?subsets={$subsets}";
            $styles = $data['styles'];
            $weights = $data['weights'];
            $res = $client->get($url);
            $data = json_decode($res->getBody()->getContents(), true);
            $sass .= $this->processFont($data, $styles, $weights, $formats, $filename);
        }
        $sassFile = $config['fonts']['sass'];
        if ( ! file_exists(dirname($sassFile))) {
            mkdir(dirname($sassFile), 0755, true);
        }

        file_put_contents($config['fonts']['sass'], $sass);

        return 0;
    }

    /**
     * @required
     */
    public function setLogger(LoggerInterface $logger) : void {
        $this->logger = $logger;
    }

    /**
     * @required
     */
    public function setTwigEngine(Environment $twig) : void {
        $this->twig = $twig;
    }
}
