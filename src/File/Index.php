<?php

namespace SitemapBuilder\File {

    use SitemapBuilder\Exceptions\Exception;
    use SitemapBuilder\File;
    use SitemapBuilder\Interfaces\IndexInterface;

    class Index extends File implements IndexInterface
    {
        public function insert($url)
        {
            $this->write($this->buildXml($url));
        }


        public function open()
        {
            parent::open();

            $this->write(
                '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
            );
        }


        public function close()
        {
            $this->write(
                '</sitemapindex>'
            );
            
            parent::close();
        }


        private function buildXml($url)
        {
            return $this->buildXmlTag($this->buildXmlTag($url, 'loc'), 'sitemap');
        }
    }
}