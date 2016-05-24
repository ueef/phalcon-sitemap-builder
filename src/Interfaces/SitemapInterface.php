<?php

namespace SitemapBuilder\Interfaces {

    interface SitemapInterface extends FileInterface
    {
        public function getRows();


        public function insert(array $data);


        public function exceeded();
    }
}