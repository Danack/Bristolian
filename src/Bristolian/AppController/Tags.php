<?php

namespace Bristolian\AppController;

use Bristolian\Repo\TagRepo\TagRepo;
use SlimDispatcher\Response\RedirectResponse;
use VarMap\VarMap;
use Bristolian\DataType\TagParam;

use function esprintf;

class Tags
{
    public function view(TagRepo $tagRepo): string
    {
        $content = "<h1>Tags</h1>";

        $content .= "<a href='/tags/edit'>Edit tags</a>";
        $content .= "<h2>List of tags goes</h2>";
        $tags = $tagRepo->getAllTags();

        if (count($tags) === 0) {
            return "No tags created on system yet.";
        }

        $content .= <<< HTML
<table>
  <tr>
    <th>
      Tag  
    </th>
    <th>Description</th>
  </tr>
HTML;

        $tag_template = <<< HTML
<tr>
    <td>:html_text</td>
    <td>:html_description</td>        
</tr>
HTML;

        foreach ($tags as $tag) {
            $params = [
              ':html_text' => $tag->getText(),
              ':html_description' => $tag->getDescription()
            ];

            $content .= esprintf($tag_template, $params);
        }


        $content .= "</table>";


        return $content;
    }

    public function process_add(TagRepo $tagRepo, VarMap $varMap): RedirectResponse
    {
        $tagParam = TagParam::createFromVarMap($varMap);

        $tag = $tagRepo->createTag($tagParam);

        return new RedirectResponse('/tags/edit?message=tag added');
    }

    public function edit(TagRepo $tagRepo): string
    {
        $content = "<h1>Tag editing page</h1>";

        $content .= <<< HTML
<h2>Add tag</h2>
<form method="post">
<table>
  <tr>
    <td>Tag text</td>
    <td><input type="text" name="text" ></input></td>
  </tr>
  <tr>
    <td>Description</td>
    <td><input type="text" name="description"></input></td>
  </tr>
</table>

<input type="submit" value="Add"></input>
</form>

<h2>Current tags</h2>
HTML;

        return $content;
    }
}
