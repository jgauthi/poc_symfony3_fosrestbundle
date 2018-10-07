<?php

namespace PlatformBundle\Service;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class JsonParamConverter implements ParamConverterInterface
{
    public function supports(ParamConverter $configuration): bool
    {
        // If the name of the controller argument is not "json", do not apply the converter
        if ('json' !== $configuration->getName()) {
            return false;
        }

        return true;
    }

    public function apply(Request $request, ParamConverter $configuration): Request
    {
        $json = $request->attributes->get('json');
        $json = json_decode($json, true);

        // We update the new value of the attribute
        $request->attributes->set('json', $json);
    }
}
