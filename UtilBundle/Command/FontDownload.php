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
     * @param $config
     * @param $variant
     * @param $accepted
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     *
     * @return string
     */
    protected function render($config, $variant, $accepted) {
        return $this->twig->render('@NinesUtil/font/font.css.twig', [
            'family' => $variant['fontFamily'],
            'locals' => $variant['local'] ?? [],
            'weight' => $variant['fontWeight'],
            'formats' => $accepted,
            'style' => $variant['fontStyle'],
            'prefix' => $config['prefix'],
        ]);
    }

    /**
     * Download the font file from Google Fonts and store it.
     *
     * @param $id
     * @param $name
     * @param $variant
     * @param $config
     *
     * @return array
     */
    protected function fetch($id, $name, $variant, $config) {
        $client = new Client();
        $filenames = [];
        $filenameTemplate = $config['filename'];

        foreach ($config['formats'] as $format) {
            $callback = [
                'id' => $name,
                'style' => $variant['fontStyle'],
                'weight' => $variant['fontWeight'],
                'ext' => $format,
            ];

            $filename = preg_replace_callback('/\{(\w+)\}/', function ($matches) use ($callback) {
                return $callback[$matches[1]];
            }, $filenameTemplate);
            $file = $config['path'] . '/' . $filename;

            if (file_exists($file)) {
                $this->logger->notice('Skipped existing ' . $name . ' ' . $variant['fontStyle'] . ' ' . $variant['fontWeight'] . ' ' . $format);
            } else {
                $this->logger->notice('Downloading ' . $name . ' ' . $variant['fontStyle'] . ' ' . $variant['fontWeight'] . ' ' . $format);
                $client->get($variant[$format], [
                    'sink' => $file,
                ]);
            }
            $filenames[$format] = $filename;
        }

        return $filenames;
    }

    /**
     * Compare the font variant $variant returned from the API against the requested fonts.
     *
     * @param $id
     * @param string $name
     * @param array $variant
     * @param array $config
     *
     * @return bool true if the font should be included in the CSS and downloaded.
     */
    protected function checkVariant($id, $name, $variant, $config) {
        $styles = $config['families'][$id]['styles'];
        if ( ! in_array($variant['fontStyle'], $styles, false)) {
            $this->logger->info('Skipping style ' . $name . ' ' . $variant['fontStyle']);

            return false;
        }
        $weights = $config['families'][$id]['weights'];
        if ( ! in_array($variant['fontWeight'], $weights, false)) {
            $this->logger->info('Skipping weight ' . $name . ' ' . $variant['fontWeight']);

            return false;
        }

        return true;
    }

    /**
     * Process one font definition as returned by the API and return the rendered CSS for the font.
     *
     * @param $id
     * @param $data
     * @param $config
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     *
     * @return string
     */
    protected function processFont($id, $data, $config) {
        $css = '';
        foreach ($data['variants'] as $variant) {
            $accepted = [];

            if(isset($variant['local']) && is_array($variant['local'])) {
                $name = $variant['local'][1];
            } else {
                $name = '';
            }
            if ( ! $this->checkVariant($id, $name, $variant, $config)) {
                continue;
            }
            $filenames = $this->fetch($id, $name, $variant, $config);
            $accepted = array_merge($accepted, $filenames);
            $css .= $this->render($config, $variant, $accepted);
        }

        return $css;
    }

    /**
     * Execute the command.
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    protected function execute(InputInterface $input, OutputInterface $output) : int {
        $configData = Yaml::parseFile('config/fonts.yaml');
        $config = $configData['fonts'];

        $client = new Client();
        $css = '';

        $path = $config['path'];
        if ( ! file_exists($path)) {
            mkdir($path, 0755, true);
        }

        $subsets = implode(',', $config['subsets']);

        foreach (array_keys($config['families']) as $id) {
            $url = "https://google-webfonts-helper.herokuapp.com/api/fonts/{$id}?subsets={$subsets}";
            $res = $client->get($url);
            $data = json_decode($res->getBody()->getContents(), true);
            $css .= $this->processFont($id, $data, $config);
        }

        $cssFile = $config['css'];
        if ( ! file_exists(dirname($cssFile))) {
            mkdir(dirname($cssFile), 0755, true);
        }
        file_put_contents($cssFile, $css);

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
