<?php

namespace Bristolian\AppController;

use Bristolian\Repo\TagRepo\TagRepo;
use SlimDispatcher\Response\RedirectResponse;
use VarMap\VarMap;
use Bristolian\DataType\TagParam;

use function esprintf;

class Lists
{
    public function index(/*TagRepo $tagRepo*/): string
    {
        $content = "<h1>Lists</h1>";

        $content .= <<< HTML
<p>
  This is a list of lists of things used on this site. It is here to allow easy seeding of data, but eventually it probably will be replaced with a tools that makes it easier to add things from Rooms.
</p>
HTML;

        $content .= <<< TABLE
<table>
  <thead>
    <tr>
      <th>Name</th>
    </tr>
  </thead>
  <tbody>
    <tr><td><a href="/foi_requests">FOI requests</a></td></tr>
    <tr><td><a href="/organisations">Organisations</a></td></tr>
    <tr><td><a href="/tags">Tags</a></td></tr>
    </tr>
  </tbody>
</table>
TABLE;

        return $content;
    }
}
