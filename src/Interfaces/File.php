<?php

namespace SitemapBuilder\Interfaces {

    use SitemapBuilder\Exceptions\Exception;

    interface FileInterface
    {
        public function getSize();


        public function save($path);


        public function open();


        public function close();
        

        public function undo();


        public function write($string);
    }
}