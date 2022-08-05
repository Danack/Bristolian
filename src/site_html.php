<?php

/**
 * This file holds functions for rendering the site html from components
 */

declare(strict_types = 1);

use Bristolian\SiteHtml\Breadcrumbs;
//use Bristolian\SiteHtml\ContentLink;
//use Bristolian\SiteHtml\CopyrightInfo;
//use Bristolian\SiteHtml\EditInfo;
use Bristolian\SiteHtml\HeaderLink;
use Bristolian\SiteHtml\HeaderLinks;
//use Bristolian\Page;
//use Bristolian\PrevNextLinks;
use SlimAuryn\Response\HtmlResponse;

//function createBreadcrumbPart(string $path, string $description)
//{
//    $li_template = "<li><a href=':attr_link'>:html_description</a></li>";
//    $params = [
//        ':attr_link' => $path,
//        ':html_description' => $description
//    ];
//    return esprintf($li_template, $params);
//}

//function createBreadcrumbHtml(
//    ?\Bristolian\Section $section,
//    Breadcrumbs $breadcrumbs
//): string {
//
//    $prefix = '';
//    $li_parts = [];
//
//    if ($section !== null) {
//        $prefix  = $section->getPrefix();
//        $li_parts[] = createBreadcrumbPart($prefix, $section->getName());
//    }
//
//    foreach ($breadcrumbs->getBreadcrumbs() as $breadcrumb) {
//        $li_parts[] = createBreadcrumbPart(
//            $prefix . $breadcrumb->getPath(),
//            $breadcrumb->getDescription()
//        );
//    }
//
//    if (count($li_parts) === 0) {
//        return "";
//    }
//
//    return "<ul>" . implode("", $li_parts) . "</ul>";
//}

//function createPrevNextHtml(?PrevNextLinks $prevNextLinks): string
//{
//    if ($prevNextLinks === null) {
//        return "";
//    }
//
//    $prevLink = $prevNextLinks->getPrevLink();
//    $nextLink = $prevNextLinks->getNextLink();
//
//    $template = '';
//    $params = [];
//
//    if ($prevLink) {
//        $template .= '<span class="Bristolian_prev"><a href=":attr_prev_link">«&nbsp;:html_prev_description</a></span>';
//        $params[':attr_prev_link'] = $prevLink->getPath();
//        $params[':html_prev_description'] = $prevLink->getDescription();
//    }
//
//    if ($nextLink) {
//        $template .= '<span class="Bristolian_next"><a href=":attr_next_link">:html_next_description&nbsp;»</a></span>';
//        $params[':attr_next_link'] = $nextLink->getPath();
//        $params[':html_next_description'] = $nextLink->getDescription();
//    }
//
//    try {
//        esprintf($template, $params);
//    }
//    catch (Zend\Escaper\Exception\RuntimeException $re) {
//        var_dump($params);
//        exit(0);
//    }
//
//    return "<span class='Bristolian_prev_next_container'>" . esprintf($template, $params) ."</span>";
//}

function createPageHeaderHtml(/*HeaderLinks $headerLinks*/) : string
{
    $headerLinks = new HeaderLinks([
        new HeaderLink('/', 'Home'),
        new HeaderLink('/topics', 'Topics'),
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


//function createStandardHeaderLinks(): HeaderLinks
//{
//    return new HeaderLinks([
//        new HeaderLink("/", "Home"),
//        new HeaderLink("/sections", "Sections"),
//        new HeaderLink("/about", "About"),
//        new HeaderLink("/merch", "Merch"),
//    ]);
//}

//function getUrl($sectionPath, $path)
//{
//    // It's an external url
//    if (strpos($path, 'http') === 0) {
//        return $path;
//    }
//
//    return $sectionPath . $path;
//}

//function createContentLinkHtml(string $sectionPath, ContentLink $contentLink): string
//{
//    if ($contentLink->getPath() === null) {
//        return esprintf(
//            "<div class='Bristolian_content_link_level_:attr_level'>:html_description</div>",
//            [
//                ':html_description' => $contentLink->getDescription(),
//                ':attr_level' => $contentLink->getLevel()
//            ]
//        );
//    }
//
//    return esprintf(
//        "<div class='Bristolian_content_link_level_:attr_level'><a href=':attr_path'>:html_description</a></div>",
//        [
//            ':html_description' => $contentLink->getDescription(),
//            ':attr_path' => $sectionPath . $contentLink->getPath(),
//            ':attr_level' => $contentLink->getLevel()
//        ]
//    );
//}

///**
// * @param string $sectionPath
// * @param ContentLink[] $contentLinks
// * @return string
// */
//function createContentLinksHtml(string $sectionPath, array $contentLinks): string
//{
//    $li_elements = [];
//
//    foreach ($contentLinks as $contentLink) {
//        $li_elements[] = createContentLinkHtml(
//            $sectionPath,
//            $contentLink
//        );
//    }
//
//    return implode("\n", $li_elements);
//}

///**
// * @param array<string, string> $namesWithLinks
// */
//function createEditLinks(array $namesWithLinks): string
//{
//    $html_snippets = [];
//
//    $template = '<a href=":attr_edit_link" target="_blank" rel="noopener noreferrer">:html_description</a>';
//
//    foreach ($namesWithLinks as $name => $link) {
//        $html_snippets[] = esprintf(
//            $template,
//            [':html_description' => $name, ":attr_edit_link" => $link]
//        );
//    }
//
//    return implode($html_snippets);
//}

function createFooterHtml(
//    CopyrightInfo $copyrightInfo,
//    EditInfo $editInfo
): string {
    $html = <<< HTML
<span class="system">
  <a href="/system">System</a>
</span>
HTML;

//    <span class="edit_link">
//  :raw_edit_links
//    </span>

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

//function createPageHtmlResponse(
//    ?\Bristolian\Section $section,
//    Page $page,
//    Breadcrumbs $breadcrumbs
//): HtmlResponse {
//    $html = createPageHtml($section, $page);
//    return new HtmlResponse($html);
//}
//
//function createPageHtmlFromPage(Page $page)
//{
//    return createPageHtml($page->getSection(), $page);
//}



function createPageHtml(
    \Bristolian\AssetLinkEmitter $assetLinkEmitter,
//    Page $page
    string $html,
): string {

//    $headerLinks = createStandardHeaderLinks();
//
//    $prefix = '/';
//    if ($section) {
//        $prefix = $section->getPrefix();
//    }
//
//    $pageTitle = $page->getTitle() ?? "PHP Bristolian";

    $pageTitle = "PHP Bristolian";

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
