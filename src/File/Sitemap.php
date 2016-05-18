<?php

namespace SitemapBuilder\File {

    use SitemapBuilder\File;
    use SitemapBuilder\Exceptions\Exception;
    use SitemapBuilder\Interfaces\SitemapInterface;

    class Sitemap extends File implements SitemapInterface
    {
        const ROWS_LIMIT = 40000;
        const SIZE_LIMIT = 10485760;

        /**
         * @var integer
         */
        private $rows = 0;


        public function getRows()
        {
            return $this->rows;
        }


        public function insert(array $data)
        {
            $this->write($this->buildXml($data));
            $this->rows++;
        }


        public function exceeded()
        {
            return $this->getRows() > self::ROWS_LIMIT || $this->getSize() > self::SIZE_LIMIT;
        }


        public function open()
        {
            parent::open();

            $this->write(
                '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
            );
        }


        public function close()
        {
            parent::close();

            $this->write(
                '</urlset>'
            );
        }


        private function buildXml(array $data)
        {
            $xml = '';
            foreach ($data as $key => $value) {
                $xml .= $this->buildXmlTag($value, $key);
            }

            return $this->buildXmlTag($xml, 'url');
        }
    }
}