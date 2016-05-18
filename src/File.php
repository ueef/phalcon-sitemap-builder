<?php

namespace SitemapBuilder {

    use SitemapBuilder\Exceptions\Exception;
    use SitemapBuilder\Interfaces\FileInterface;

    class File implements FileInterface
    {
        /**
         * @var integer
         */
        protected $size = 0;

        /**
         * @var integer
         */
        protected $path;

        /**
         * @var resource
         */
        protected $stream;

        /**
         * @var integer
         */
        protected $shift = null;


        public function __destruct()
        {
            if (is_resource($this->stream)) {
                @fclose($this->stream);
            }

            if ($this->path) {
                @unlink($this->path);
            }
        }


        public function getSize()
        {
            return $this->size;
        }


        public function save($path)
        {
            if (false === copy($this->path, $path)) {
                throw new Exception('не удалось скопировать временный файл');
            }

            if (unlink($this->path)) {
                throw new Exception('не получилось удалить временный файл');
            }
        }


        public function open()
        {
            $this->path = tempnam(sys_get_temp_dir(), 'sitemap-');

            if (false === $this->stream) {
                throw new Exception('не удалось создать временный файл');
            }

            $this->stream = fopen($this->path, 'w');

            if (false === $this->stream) {
                throw new Exception('не удалось открыть временный файл на запись');
            }

            $this->write(
                '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL
            );
        }


        public function undo()
        {
            if (!ftruncate($this->stream, $this->shift) || -1 == fseek($this->stream, $this->shift)) {
                throw new Exception('не удалось отменить последнюю запись');
            }
        }


        public function close()
        {
            if (false === fclose($this->stream)) {
                throw new Exception('не удалось закрыть временный файл, возможно он не был открыт');
            }
        }


        public function write($string)
        {
            if (!is_resource($this->stream)) {
                throw new Exception('некуда писать, временный файл не открыт');
            }

            if (false === fwrite($this->stream, $string)) {
                throw new Exception('не удалось записать строчку во временный файл');
            }

            $this->shift = ftell($this->stream);
            $this->size += strlen($string);
        }


        protected function buildXmlTag($value, $tag)
        {
            if ('lastmod' == $tag) {
                $value = date(DATE_ATOM, $value);
            }

            return sprintf('<%s>%s</%s>', $tag, $value, $tag);
        }
    }
}