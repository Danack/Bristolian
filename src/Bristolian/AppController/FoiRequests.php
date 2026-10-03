<?php

namespace Bristolian\AppController;

use Bristolian\Parameters\FoiRequestParams;
use Bristolian\Repo\FoiRequestRepo\FoiRequestRepo;
use SlimDispatcher\Response\RedirectResponse;
use VarMap\VarMap;
use function esprintf;

class FoiRequests
{
    public function view(FoiRequestRepo $foiRequestRepo): string
    {
        $content = "<h1>FOI requests</h1>";

        $content .= "<a href='/foi_request/edit'>Edit tags</a>";
        $content .= "<h2>List of FOI requests goes</h2>";
        $foiRequests = $foiRequestRepo->getAllFoiRequests();

        if (count($foiRequests) === 0) {
            return "No FOI requests created on system yet.";
        }

        $content .= '<table>' . "\n"
            . '  <tr>' . "\n"
            . '    <th>' . "\n"
            . '      Text  ' . "\n"
            . '    </th>' . "\n"
            . '    <th>Description</th>' . "\n"
            . '  </tr>';

        $tag_template = '<tr>' . "\n"
            . '    <td><a href=":attr_url">:html_text</a></td>' . "\n"
            . '    <td>:html_description</td>' . "\n"
            . '</tr>';

        foreach ($foiRequests as $foiRequest) {
            $params = [
              ':attr_url' => $foiRequest->getUrl(),
              ':html_text' => $foiRequest->getText(),
              ':html_description' => $foiRequest->getDescription()
            ];

            $content .= esprintf($tag_template, $params);
        }


        $content .= "</table>";


        return $content;
    }

    public function process_add(FoiRequestRepo $foiRequestRepo, VarMap $varMap): RedirectResponse
    {
        $foiRequestParam = FoiRequestParams::createFromVarMap($varMap);

        $tag = $foiRequestRepo->createFoiRequest($foiRequestParam);

        return new RedirectResponse('/foi_requests/edit?message=FOI request added');
    }

    public function edit(FoiRequestRepo $tagRepo): string
    {
        $content = "<h1>FOI Request editing page</h1>";

        $content .= '<h2>Add tag</h2>' . "\n"
            . '<form method="post">' . "\n"
            . '<table>' . "\n"
            . '  <tr>' . "\n"
            . '    <td>FOI Request text</td>' . "\n"
            . '    <td><input type="text" name="text" ></input></td>' . "\n"
            . '  </tr>' . "\n"
            . '  <tr>' . "\n"
            . '    <td>URL</td>' . "\n"
            . '    <td><input type="text" name="url" ></input></td>' . "\n"
            . '  </tr>' . "\n"
            . '  <tr>' . "\n"
            . '    <td>Description</td>' . "\n"
            . '    <td><input type="text" name="description"></input></td>' . "\n"
            . '  </tr>' . "\n"
            . '</table>' . "\n\n"
            . '<input type="submit" value="Add"></input>' . "\n"
            . '</form>' . "\n\n"
            . '<h2>Current FOI requests</h2>';

        return $content;
    }
}
