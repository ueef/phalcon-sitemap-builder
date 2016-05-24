<?php

namespace SitemapBuilder {

    use SitemapBuilder\Exceptions\Exception;
    use SitemapBuilder\Interfaces\FileInterface;
    use Phalcon\Extended\Attachment\File as AttachmentFile;

    abstract class File implements FileInterface
    {

        /**
         * @var string
         */
        protected $prefixDir;


        /**
         * @var string
         */
        protected $storageDir;


        /**
         * @var integer
         */
        protected $index = 0;

        /**
         * @var integer
         */
        protected $size = 0;

        /**
         * @var integer
         */
        protected $temPath;

        /**
         * @var resource
         */
        protected $temStream;

        /**
         * @var integer
         */
        protected $shift = null;



        public function __construct($prefixDir, $storageDir)
        {
            $this->prefixDir = $prefixDir;
            $this->storageDir = $storageDir;
        }


        public function __destruct()
        {
            if (is_resource($this->temStream)) {
                @fclose($this->temStream);
            }

            if (file_exists($this->temPath)) {
                @unlink($this->temPath);
            }
        }


        public function getUrl()
        {
            return $this->prefixDir . '/' . $this->getName();
        }


        public function getPath()
        {
            return $this->storageDir . $this->getUrl();
        }


        public function getName()
        {
            $name = 'sitemap';

            if ($this->index > 0) {
                $name .= '-' . $this->index;
            }

            return $name . '.xml';
        }


        public function getSize()
        {
            return $this->size;
        }


        public function setIndex($index)
        {
            $this->index = $index;
        }


        public function save()
        {
            if (false === copy($this->temPath, $this->getPath())) {
                throw new Exception('не удалось скопировать временный файл');
            }

            if (!unlink($this->temPath)) {
                throw new Exception('не получилось удалить временный файл');
            }
        }


        public function open()
        {
            $this->temPath = tempnam(sys_get_temp_dir(), 'sitemap-');

            if (false === $this->temStream) {
                throw new Exception('не удалось создать временный файл');
            }

            $this->temStream = fopen($this->temPath, 'w');

            if (false === $this->temStream) {
                throw new Exception('не удалось открыть временный файл на запись');
            }

            $this->write(
                '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL
            );
        }


        public function clear()
        {
            if (is_resource($this->temStream)) {
                fclose($this->temStream);
            }

            unlink($this->temPath);
        }


        public function undo()
        {
            if (!ftruncate($this->temStream, $this->shift) || -1 == fseek($this->temStream, $this->shift)) {
                throw new Exception('не удалось отменить последнюю запись');
            }
        }


        public function close()
        {
            if (false === fclose($this->temStream)) {
                throw new Exception('не удалось закрыть временный файл, возможно он не был открыт');
            }
        }


        public function write($string)
        {
            if (!is_resource($this->temStream)) {
                throw new Exception('некуда писать, временный файл не открыт');
            }

            if (false === fwrite($this->temStream, $string)) {
                throw new Exception('не удалось записать строчку во временный файл');
            }

            $this->shift = ftell($this->temStream);
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