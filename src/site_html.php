<?php

/**
 * This file holds functions for rendering the site html from components
 */

declare(strict_types = 1);

use Bristolian\SiteHtml\HeaderLink;
use Bristolian\SiteHtml\HeaderLinks;

function createPageHeaderHtml(/*HeaderLinks $headerLinks*/) : string
{
    $headerLinks = new HeaderLinks([
        new HeaderLink('/', 'Home'),
        new HeaderLink('/tools', 'Tools'),
        new HeaderLink('/about', "About")
    ]);


    $template = '<span><a href=":attr_path">:html_description</a></span>';
    $html = "";

    foreach ($headerLinks->getHeaderLinks() as $headerLink) {
        $params = [
            ':html_description' => $headerLink->getDescription(),
            ':attr_path' => $headerLink->getPath(),
        ];
        $html .= esprintf($template, $params);
    }

    return $html;
}


function createFooterHtml(): string
{
    $html = <<< HTML
<span class="system">
  <a href="/system">System</a>
</span>
HTML;

    $params = [
//        ':html_copyright_name' => $copyrightInfo->getName(),
//        ':attr_copyright_link' => $copyrightInfo->getLink(),
//        ':raw_edit_links' => createEditLinks($editInfo->getNamesWithLinks())
    ];

    return esprintf($html, $params);
}

function getPageLayoutHtml(): string
{
    return <<< HTML
<!DOCTYPE html>

<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- todo - meta description -->

  <title>:html_page_title</title>
  <link rel="stylesheet" href=":raw_site_css_link">
 
<!--  <link href="/fonts/Cabin-VariableFont_wdth,wght.ttf" rel="preload" as="font" crossorigin="anonymous">-->
</head>

<body>
  <div class="bristolian_wrapper">
    <div class="bristolian_header">:raw_top_header</div>
    <div class="bristolian_breadcrumbs">:raw_breadcrumbs</div>
    <div class="bristolian_prev_next">:raw_prev_next</div>
    <div class="bristolian_content_links">:raw_nav_content</div>
    <div class="bristolian_content">:raw_content</div>
    <div class="bristolian_footer">:raw_footer</div>
  </div>
</body>

<script src=':raw_site_js_link'></script>
</html>
HTML;
}

function createPageHtml(
    \Bristolian\AssetLinkEmitter $assetLinkEmitter,
    string $html,
): string {

    $pageTitle = "Bristolian";

    $assetSuffix = $assetLinkEmitter->getAssetSuffix();

    $params = [
        ':raw_site_css_link' => '/css/site.css' . $assetSuffix,
        ':raw_site_js_link' => '/js/app.bundle.js' . $assetSuffix,
        ':html_page_title' => $pageTitle,
        ':raw_top_header' => createPageHeaderHtml(/*$headerLinks*/),
        ':raw_breadcrumbs' => '', //createBreadcrumbHtml($section, $page->getBreadcrumbs()),
        ':raw_prev_next' => '', //createPrevNextHtml($page->getPrevNextLinks()),
        ':raw_content' => $html, //$page->getContentHtml(),
        ':raw_nav_content' => '', //createContentLinksHtml($prefix, $page->getContentLinks()),
        ':raw_footer' => createFooterHtml(),
    ];

    return esprintf(getPageLayoutHtml(), $params);
}

//function create404Page(string $path)
//{
//    $output = "This is a 404 page.";
//
//    $output .= sprintf(
//        "So you were trying to reach '%s'",
//        $path
//    );
//
//    return $output;
//}

function share_this_page(): string
{
    $url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $query = http_build_query(['url' => $url]);
    $content = "<h3>Share this page</h3>";
    $content .= "<p>" . \Bristolian\Page::getQrShareMessage() . "</p>";
    $content .= "<img src='/qr/code?$query' alt='qr code' width='256' height='256'></img>";

    return $content;
}
