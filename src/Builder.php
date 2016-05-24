<?php

namespace SitemapBuilder {

    use SitemapBuilder\File\Index;
    use SitemapBuilder\File\Sitemap;
    use SitemapBuilder\Exceptions\Exception;
    use SitemapBuilder\Interfaces\FileInterface;
    use SitemapBuilder\Interfaces\SitemapInterface;

    class Builder
    {
        /**
         * @var string
         */
        private $host;

        /**
         * @var string
         */
        private $prefixDir;

        /**
         * @var string
         */
        private $storageDir;

        /**
         * @var FileInterface[]
         */
        private $files = [];

        /**
         * @var SitemapInterface
         */
        private $current;


        public function __construct($host, $prefixDir, $storageDir)
        {
            $this->host = $host;
            $this->prefixDir = $this->correctPath($prefixDir);
            $this->storageDir = $this->correctPath($storageDir);
        }


        public function getDirPath()
        {
            return $this->storageDir . $this->prefixDir;
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

            $this->current = new Sitemap($this->prefixDir, $this->storageDir);
            $this->current->setIndex(count($this->files));
            $this->current->open();

            $this->files[] = $this->current;
        }


        public function end()
        {
            $this->current->close();
            $this->clearDir();
            $this->release();
        }


        public function abort()
        {
            if ($this->current) {
                $this->current->close();
            }

            foreach ($this->files as $file) {
                $file->clear();
            }
        }


        private function clearDir()
        {
            foreach (array_diff(scandir($this->getDirPath()), ['.', '..']) as $file) {
                $path = $this->getDirPath() . '/' . $file;
                
                if (!unlink($path)) {
                    throw new Exception('не получилось удалить файл ' . $path);
                }
            }
        }


        private function release()
        {
            if (count($this->files) > 1) {
                $index = new Index($this->prefixDir, $this->storageDir);
                $index->open();

                foreach ($this->files as $key => $file) {
                    $file->setIndex($key + 1);
                    $index->insert($this->host . '/' . $file->getUrl());
                }

                $index->close();
                array_unshift($this->files, $index);
            }

            foreach ($this->files as $file) {
                $file->save();
            }
        }


        private function correctPath($path)
        {
            if (null === $path || !strlen($path)) {
                return $path;
            }

            return preg_replace('/^\/{0,}([^\/].{0,})\/{0,}$/U', '/$1', $path);
        }
    }
}