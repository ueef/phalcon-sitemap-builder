<?php

namespace SitemapBuilder {

    use SitemapBuilder\File\Sitemap;
    use SitemapBuilder\Exceptions\Exception;
    use SitemapBuilder\Interfaces\FileInterface;
    use SitemapBuilder\Interfaces\SitemapInterface;

    class Builder
    {
        /**
         * @var FileInterface
         */
        private $files = [];

        /**
         * @var SitemapInterface
         */
        private $current;


        public function __construct()
        {
            $this->next();
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