<?php namespace Sturgeon\PHPArse;

use DOMDocument;
use DOMXpath;

class Parser
{
    protected $xpath;

    public static function readFromUrl($url)
    {
        $html = file_get_contents($url);
        return new static($html);
    }

    public function __construct($html)
    {
        $this->xpath = $this->loadXPathDocument($html);
        if (! $this->isValidPhpInfoHtml()) {
            throw new \Exception('The HTML does not contain valid phpinfo() output');
        }
    }

    public function parse()
    {
        return new Info($this->locatePhpVersion(), $this->locateGeneralInfo());
    }

    public function locatePhpVersion()
    {
        $node = $this->xpath->query('//body//h1')[0];
        return str_replace('PHP Version ', '', $node->nodeValue);
    }

    public function locateGeneralInfo()
    {
        $rows = $this->xpath->query('//body//table[2]/tr');
        $infoPairs = [];
        foreach ($rows as $row) {
            $label = $this->cleanLabel($row->firstChild->textContent);
            $value = $this->cleanValue($row->lastChild->textContent);
            $infoPairs[$label] = $value;
        }
        return $infoPairs;
    }

    private function cleanLabel($label)
    {
        return trim($label);
    }

    private function cleanValue($label)
    {
        return trim($label);
    }

    private function loadXPathDocument($html)
    {
        $document = new DOMDocument;
        $document->loadHTML($html);
        return new DOMXpath($document);
    }

    private function isValidPhpInfoHtml()
    {
        return $this->xpath->query('//head//title')[0]->nodeValue === 'phpinfo()';
    }
}
