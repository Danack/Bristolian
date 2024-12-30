<?php

namespace Bristolian\AppController;

use Bristolian\DataType\FoiRequestParam;
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

        $content .= <<< HTML
<table>
  <tr>
    <th>
      Text  
    </th>
    <th>Description</th>
  </tr>
HTML;

        $tag_template = <<< HTML
<tr>
    <td><a href=":attr_url">:html_text</a></td>
    <td>:html_description</td>        
</tr>
HTML;

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
        $foiRequestParam = FoiRequestParam::createFromVarMap($varMap);

        $tag = $foiRequestRepo->createFoiRequest($foiRequestParam);

        return new RedirectResponse('/foi_requests/edit?message=FOI request added');
    }

    public function edit(FoiRequestRepo $tagRepo): string
    {
        $content = "<h1>FOI Request editing page</h1>";

        $content .= <<< HTML
<h2>Add tag</h2>
<form method="post">
<table>
  <tr>
    <td>FOI Request text</td>
    <td><input type="text" name="text" ></input></td>
  </tr>
  <tr>
    <td>URL</td>
    <td><input type="text" name="url" ></input></td>
  </tr>
  <tr>
    <td>Description</td>
    <td><input type="text" name="description"></input></td>
  </tr>
</table>

<input type="submit" value="Add"></input>
</form>

<h2>Current FOI requests</h2>
HTML;

        return $content;
    }
}
