<?php

namespace Easyconn\PhalconLogger;

use Phalcon\Logger\Formatter\Line as PhalconLoggerFormatter;
use Phalcon\Logger\Item;
use Phalcon\Support\HelperFactory;

class Formatter extends PhalconLoggerFormatter
{
    /**
     * @inheritdoc
     */
    public function format(Item $item): string
    {
        $helper = new HelperFactory();
        return $helper->interpolate($item->getMessage(), $item->getContext() ?: []);
    }
}
