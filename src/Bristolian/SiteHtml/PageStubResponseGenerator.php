<?php

declare(strict_types = 1);

namespace Bristolian\SiteHtml;

use Bristolian\AssetLinkEmitter;
use SlimDispatcher\Response\StubResponse;
use SlimDispatcher\Response\HtmlResponse;

class PageStubResponseGenerator
{
    public function __construct(private AssetLinkEmitter $assetLinkEmitter)
    {
    }

    public function create404Page(string $path): StubResponse
    {
        $output = "<p>This is a 404 page.</p>";

        $output .= sprintf(
            "<p>So you were trying to reach '%s', but we couldn't find it.</p>",
            $path
        );

        $pageHtml = createPageHtml($this->assetLinkEmitter, $output);

        return new HtmlResponse($pageHtml, [], 404);
    }


//    private function createPageHtml(string $html): string
//    {
////    $headerLinks = createStandardHeaderLinks();
////
////    $prefix = '/';
////    if ($section) {
////        $prefix = $section->getPrefix();
////    }
//
////    $pageTitle = $page->getTitle() ?? "PHP Bristolian";
//
//        $pageTitle = "PHP Bristolian";
//
//        $assetSuffix = $this->assetLinkEmitter->getAssetSuffix();
//
//        $params = [
//            ':raw_site_css_link' => '/css/site.css' . $assetSuffix,
//            ':raw_site_js_link' => '/js/app.bundle.js' . $assetSuffix,
//            ':html_page_title' => $pageTitle,
//            ':raw_top_header' => createPageHeaderHtml(/*$headerLinks*/),
//            ':raw_breadcrumbs' => '', //createBreadcrumbHtml($section, $page->getBreadcrumbs()),
//            ':raw_prev_next' => '', //createPrevNextHtml($page->getPrevNextLinks()),
//            ':raw_content' => $html, //$page->getContentHtml(),
//            ':raw_nav_content' => '', //createContentLinksHtml($prefix, $page->getContentLinks()),
//            ':raw_footer' => '', //createFooterHtml($page->getCopyrightInfo(), $page->getEditInfo()),
//        ];
//
//        return esprintf(getPageLayoutHtml(), $params);
//    }
}
