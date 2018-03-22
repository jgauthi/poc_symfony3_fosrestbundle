<?php
namespace PlatformBundle\Service\CustomParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class JsonParamConverter implements ParamConverterInterface
{
    function supports(ParamConverter $configuration)
    {
        // Si le nom de l'argument du contrôleur n'est pas "json", on n'applique pas le convertisseur
        if('json' !== $configuration->getName())
            return false;

        return true;
    }

    function apply(Request $request, ParamConverter $configuration)
    {
        $json = $request->attributes->get('json');
        $json = json_decode($json, true); // Décodage du JSON

        // On met à jour la nouvelle valeur de l'attribut
        $request->attributes->set('json', $json);
    }
}