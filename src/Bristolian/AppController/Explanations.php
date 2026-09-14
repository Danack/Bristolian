<?php

declare(strict_types = 1);

namespace Bristolian\AppController;

use Bristolian\MarkdownRenderer\MarkdownRenderer;
use Bristolian\Page;

class Explanations
{
    public function triangle_road(MarkdownRenderer $markdownRenderer): string
    {
        $fullPath = __DIR__ . "/../../../notes/complaints/triangle_road.md";

        Page::setQrShareMessage("Please share this with anyone you know who would be affected by this road reopening. Show this QR code to someone, and they can scan it with the camera in their device. Or just copy pasta the URL to your socials.");

        $html = $markdownRenderer->renderFile($fullPath);
        $html .= "<hr/>";
        $html .= share_this_page();

        return $html;
    }

    public function bristol_rovers(MarkdownRenderer $markdownRenderer): string
    {
        $fullPath = __DIR__ . "/../../../notes/complaints/bristol_rovers.md";

        Page::setQrShareMessage("Feel free to share this page. Show this QR code to someone, and they can scan it with the camera in their device. Or just copy pasta the URL to your socials.");

        $html = $markdownRenderer->renderFile($fullPath);
        $html .= "<hr/>";
        $html .= share_this_page();

        return $html;
    }

    public function advice_for_speaking_at_council(MarkdownRenderer $markdownRenderer): string
    {
        $fullPath = __DIR__ . "/../../../notes/explanations/advice_for_speaking_at_council.md";

        Page::setQrShareMessage("Feel free to share this page. Show this QR code to someone, and they can scan it with the camera in their device. Or just copy pasta the URL to your socials.");

        $html = $markdownRenderer->renderFile($fullPath);
        $html .= "<hr/>";
        $html .= share_this_page();

        return $html;
    }

    public function avon_crescent(MarkdownRenderer $markdownRenderer): string
    {
        $fullPath = __DIR__ . "/../../../notes/complaints/avon_crescent_spike_island.md";

        Page::setQrShareMessage("Feel free to share this page. Show this QR code to someone, and they can scan it with the camera in their device. Or just copy pasta the URL to your socials.");

        $html = $markdownRenderer->renderFile($fullPath);
        $html .= "<hr/>";
        $html .= share_this_page();

        return $html;
    }

    public function shenanigans_planning(MarkdownRenderer $markdownRenderer): string
    {
        $fullPath = __DIR__ . "/../../../notes/explanations/shenanigans_at_dcb_committee.md";

        Page::setQrShareMessage("Feel free to share this page. Show this QR code to someone, and they can scan it with the camera in their device. Or just copy pasta the URL to your socials.");

        $html = $markdownRenderer->renderFile($fullPath);
        $html .= "<hr/>";
        $html .= share_this_page();

        return $html;
    }

    public function monitoring_officer_notes(MarkdownRenderer $markdownRenderer): string
    {
        $fullPath = __DIR__ . "/../../../notes/explanations/monitoring_officer_problem.md";

        Page::setQrShareMessage("Feel free to share this page. Show this QR code to someone, and they can scan it with the camera in their device. Or just copy pasta the URL to your socials.");

        $html = $markdownRenderer->renderFile($fullPath);
        $html .= "<hr/>";
        $html .= share_this_page();

        return $html;
    }

    public function development_committee_rules(MarkdownRenderer $markdownRenderer): string
    {
        $fullPath = __DIR__ . "/../../../notes/explanations/development_committee_rules.md";

        Page::setQrShareMessage("Feel free to share this page. Show this QR code to someone, and they can scan it with the camera in their device. Or just copy pasta the URL to your socials.");

        $html = $markdownRenderer->renderFile($fullPath);
        $html .= "<hr/>";
        $html .= share_this_page();

        return $html;
    }
}
