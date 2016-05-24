<?php

namespace SitemapBuilder\Interfaces {

    interface IndexInterface extends FileInterface
    {
        public function insert($url);
    }
}