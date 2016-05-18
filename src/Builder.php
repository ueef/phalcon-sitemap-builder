<?php

namespace SitemapBuilder {

    use SitemapBuilder\File\Sitemap;
    use SitemapBuilder\Exceptions\Exception;
    use SitemapBuilder\Interfaces\FileInterface;
    use SitemapBuilder\Interfaces\SitemapInterface;

    class Builder
    {
        /**
         * @var string
         */
        private $dir;

        /**
         * @var FileInterface
         */
        private $files = [];

        /**
         * @var SitemapInterface
         */
        private $current;


        public function __construct($dir)
        {
            $this->setDir($dir);
            $this->next();
        }


        public function setDir($dir)
        {
            $this->dir = preg_replace('/\/$/iuU', '', $dir);
        }


        public function insert(array $data)
        {
            $this->current->insert($data);

            if ($this->current->exceeded()) {
                $this->current->undo();
                $this->next();
                $this->current->insert($data);
            }
        }


        public function begin()
        {
            $this->next();
        }


        public function next()
        {
            if ($this->current) {
                $this->current->close();
            }

            $this->current = new Sitemap;
            $this->current->open();

            $files[] = $this->current;
        }


        public function end()
        {
            $this->current->close();
        }
    }
}