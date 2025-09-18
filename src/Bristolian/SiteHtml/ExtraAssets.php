<?php

namespace Bristolian\SiteHtml;

class ExtraAssets
{
    /**
     * @var string
     */
    private $css_files = [];

    /**
     * @var string
     */
    private $js_files = [];

    public function addCSS(string $css_file)
    {
        $this->css_files[] = $css_file;
    }

    public function addJS(string $js_file)
    {
        $this->js_files[] = $js_file;
    }

    public function getHTML(): string
    {
        $html = "";

        foreach ($this->css_files as $css_file) {
            $html .= sprintf("<link rel='stylesheet' href='%s' />\n", $css_file);
        }

        foreach ($this->js_files as $js_file) {
            $html .= sprintf("<script src='%s'></script>\n", $js_file);
        }

        return $html;
    }
}