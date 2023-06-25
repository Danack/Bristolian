<?php

namespace Bristolian\AppController;

use Bristolian\Repo\OrganisationRepo\OrganisationRepo;
use SlimDispatcher\Response\RedirectResponse;
use VarMap\VarMap;
use Bristolian\DataType\OrganisationParam;

use function esprintf;

class Organisations
{
    public function index(OrganisationRepo $organisationRepo): string
    {
        $content = "<h1>Organisations</h1>";
        $content .= "<p><a href='/organisations/add'>Add organization</a></p>";
        $organisations = $organisationRepo->getAllOrganisations();
        if (count($organisations) === 0) {
            $content .= "<p>No organizations created on system yet.</p>";

            return $content;
        }

        $content .= "<h2>List of organizations</h2>";
        $content .= <<< HTML
<table>
  <tr>
    <th>
      Organisation  
    </th>
    <th>Description</th>
  </tr>
HTML;

        $organisation_template = <<< HTML
<tr>
    <td>:html_name</td>
    <td>:html_description</td>        
</tr>
HTML;

        foreach ($organisations as $organisation) {
            $params = [
              ':html_text' => $organisation->getName(),
              ':html_description' => $organisation->getDescription()
            ];

            $content .= esprintf($organisation_template, $params);
        }


        $content .= "</table>";


        return $content;
    }

    public function process_add(OrganisationRepo $organisationRepo, VarMap $varMap): RedirectResponse
    {
        $organisationParam = OrganisationParam::createFromVarMap($varMap);

        $organisation = $organisationRepo->createOrganisation($organisationParam);

        return new RedirectResponse('/organizations/edit?message=organization added');
    }



    public function show_add(OrganisationRepo $organizationRepo): string
    {
        $content = "<h1>Add organisation page</h1>";

        $content .= <<< HTML
<h2>Add organisation</h2>
<form method="post">
<table>
  <tr>
    <td>Name</td>
    <td><input type="text" name="text" ></input></td>
  </tr>
  <tr>
    <td>Description</td>
    <td><input type="text" name="description"></input></td>
  </tr>

  <tr>
    <td>Homepage link</td>
    <td><input type="text" name="description"></input></td>
  </tr>
  
  <tr>
    <td>Facebook</td>
    <td><input type="text" name="description"></input></td>
  </tr>
  
  <tr>
    <td>Instagram</td>
    <td><input type="text" name="description"></input></td>
  </tr>
  
  <tr>
    <td>Twitter</td>
    <td><input type="text" name="description"></input></td>
  </tr>
  
  <tr>
    <td>Youtube</td>
    <td><input type="text" name="description"></input></td>
  </tr>
</table>

<input type="submit" value="Add"></input>
</form>
HTML;

        return $content;
    }
}
