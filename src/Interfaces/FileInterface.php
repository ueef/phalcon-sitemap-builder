<?php

namespace SitemapBuilder\Interfaces {

    use SitemapBuilder\Exceptions\Exception;
    
    interface FileInterface
    {
        public function getUrl();


        public function getPath();


        public function getName();


        public function getSize();


        public function setIndex($index);


        public function save();


        public function open();


        public function clear();


        public function close();
        

        public function undo();


        public function write($string);
    }
}