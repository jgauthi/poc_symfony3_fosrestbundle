<?php
namespace PlatformBundle\Service\Beta;

use Symfony\Component\HttpFoundation\Response;

class BetaHTMLAdder
{
    public function addBeta(Response $response, int $remainingDays): Response
    {
        $content = $response->getContent();

        $html = '<div style="position: absolute; top: 0; background: orange; width: 100%; text-align: center; padding: 0.5em;">Beta J-'.(int) $remainingDays.' !</div>';

        // Insert code in the page, at the beginning of <body>
        $content = str_replace
        (
            '<body>',
            '<body> '.$html,
            $content
        );

        // Editing the content in the answer
        $response->setContent($content);

        return $response;
    }
}
