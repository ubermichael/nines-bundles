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
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Download and save fonts from Google Fonts and generate the CSS for them.
 */
class FontDownload extends Command {
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var string
     */
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

    /**
     * Render the CSS for one font family.
     *
     * @param $variant
     * @param $accepted
     *
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    protected function render($variant, $accepted) {
        $def = $this->twig->render('@NinesUtil/font/font.css.twig', [
            'name' => $variant['local'][0],
            'family' => $variant['fontFamily'],
            'locals' => $variant['local'],
            'weight' => $variant['fontWeight'],
            'formats' => $accepted,
            'style' => $variant['fontStyle'],
        ]);
        return $def;
    }

    /**
     * Compare the font variant $variant returned from the API against the requested fonts.
     *
     * @param string $name
     * @param array $variant
     * @param array $styles
     * @param array $weights
     *
     * @return bool true if the font should be included in the CSS and downloaded.
     */
    protected function checkVariant($name, $variant, $styles, $weights) {
        if ( ! in_array($variant['fontStyle'], $styles, false)) {
            $this->logger->info('Skipping style ' . $name . ' ' . $variant['fontStyle']);

            return false;
        }
        if ( ! in_array($variant['fontWeight'], $weights, false)) {
            $this->logger->info('Skipping weight ' . $name . ' ' . $variant['fontWeight']);

            return false;
        }

        return true;
    }

    /**
     * Download the font file from Google Fonts and store it.
     *
     * @param $name
     * @param $variant
     * @param $formats
     * @param $filenameTemplate
     *
     * @return array
     */
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
                $this->logger->notice('Skipped existing ' . $name . ' ' . $variant['fontStyle'] . ' ' . $variant['fontWeight'] . ' ' . $format);
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

    /**
     * Process one font definition as returned by the API and return the rendered CSS for the font.
     *
     * @param $data
     * @param $styles
     * @param $weights
     * @param $formats
     * @param $filenameTemplate
     *
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
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

    /**
     * Execute the command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    protected function execute(InputInterface $input, OutputInterface $output) : int {
        $config = Yaml::parseFile('config/fonts.yaml');
        $subsets = implode(',', $config['fonts']['subsets']);
        $families = $config['fonts']['families'];
        $filename = $config['fonts']['path'] . '/' . $config['fonts']['filename'];
        $formats = $config['fonts']['formats'];
        $client = new Client();
        $css = '';
        foreach ($families as $id => $data) {
            $url = "https://google-webfonts-helper.herokuapp.com/api/fonts/{$id}?subsets={$subsets}";
            $styles = $data['styles'];
            $weights = $data['weights'];
            $res = $client->get($url);
            $data = json_decode($res->getBody()->getContents(), true);
            $css .= $this->processFont($data, $styles, $weights, $formats, $filename);
        }
        $cssFile = $config['fonts']['css'];
        if ( ! file_exists(dirname($cssFile))) {
            mkdir(dirname($cssFile), 0755, true);
        }

        file_put_contents($cssFile, $css);

        return 0;
    }

    /**
     * @required
     *
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger) : void {
        $this->logger = $logger;
    }

    /**
     * @required
     *
     * @param Environment $twig
     */
    public function setTwigEngine(Environment $twig) : void {
        $this->twig = $twig;
    }
}
