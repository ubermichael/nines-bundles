<?php

namespace Nines\UtilBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TextExtension extends AbstractExtension {

    public function getFilters() {
        return array(
            new TwigFilter('ord', [$this, 'ord']),
            new TwigFilter('chr', [$this, 'chr']),
        );
    }

    public function ord($str) {
        return preg_replace_callback('/[^[:ascii:]]/u', function($matches){
            return "{$matches[0]}(\\u" . dechex(mb_ord($matches[0])) . ")";
        }, $str);
    }

    public function chr($int) {
        return mb_chr($int, 'UTF-8');
    }

}
