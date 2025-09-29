<?php

declare(strict_types=1);

use Ergebnis\License;
use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$license = License\Type\MIT::text(
    __DIR__ . '/LICENSE',
    License\Range::since(
        License\Year::fromString('2025'),
        new DateTimeZone('UTC')
    ),
    License\Holder::fromString('MACO'),
    License\Url::fromString('https://github.com/maco-studios/openwire')
);

$license->save();

$finder = Finder::create()
	->in(
		__DIR__
	);

return (new Config())
    ->setFinder($finder)
    ->setRules([
        'header_comment' => [
            'comment_type' => 'PHPDoc',
            'header' => trim($license->header()),
            'location' => 'after_declare_strict',
            'separate' => 'both',
        ],
    ]);
