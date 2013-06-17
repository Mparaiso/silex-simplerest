<?php

namespace Mparaiso\SimpleRest\Controller;

use Bootstrap;
use Silex\WebTestCase;
use Symfony\Component\HttpKernel\HttpKernel;

class ControllerTest extends WebTestCase
{


    /**
     * Creates the application.
     *
     * @return HttpKernel
     */
    public function createApplication()
    {
        return Bootstrap::getApp();
    }

    function provider()
    {
        $now = new \DateTime();
        return array(
            array(
                array("title"         => "snippet1",
                      "description"   => "snippet2",
                      "content"       => "content",
                      "prettyContent" => "prettyContent",
                      "created_at"    => $now->format("r"),
                      "updated_at"    => $now->format("r"),
                      "category_id"   => 1
                )
            )
        );
    }

    /**
     * @dataProvider provider
     */
    function testMethods($snippet)
    {
        // on crée un snippet
        $client = $this->createClient();
        $crawler = $client->request("POST", "/snippet", array(), array(),
            array("HTTP_Content-Type" => "application/json"), json_encode($snippet));
        $content = $client->getResponse()->getContent();
        $json = json_decode($content, TRUE);
        $this->assertEquals(array("status" => "ok", "id" => 1), $json);
        $id = $json["id"];
        // or retrouve la liste des snippets
        $client->request("GET", "/snippet");
        $content = $client->getResponse()->getContent();
        $json = json_decode($content, TRUE);
        $this->assertCount(1, $json);
        $this->assertEquals("snippet1", $json[0]["title"]);
        // on retrouve l'élement mis à jour
        // on met à jour un snippet
        $json["title"] = "new title";
        $client->request("PUT", "/snippet/1", array(), array(), array(), json_encode($json));
        $content = $client->getResponse()->getContent();
        $this->assertTrue($client->getResponse()->isOk());
        $json = json_decode($content, TRUE);
        $this->assertEquals(1, $json['rowsAffected']);
        // on efface un snippet
        $client->request("DELETE","/snippet/1");
        $content= $client->getResponse()->getContent();
        $this->assertTrue($client->getResponse()->isOk());
        $json = json_decode($content,true);
        $this->assertEquals(1,$json["rowsAffected"]);
        // on s'assure que le snippet n'existe plus
        $client->request("GET","/snippet/1");
        $content= $client->getResponse()->getContent();
        $this->assertFalse($client->getResponse()->isOk());
        $this->assertEquals(404,$client->getResponse()->getStatusCode());

    }
}
