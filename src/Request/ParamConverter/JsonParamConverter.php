<?php
namespace App\Request\ParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class JsonParamConverter implements ParamConverterInterface
{
    /**
     * @param ParamConverter $configuration
     *
     * @return bool
     */
    public function supports(ParamConverter $configuration): bool
    {
        return 'json' === $configuration->getName();
    }

    /**
     * @param Request        $request
     * @param ParamConverter $configuration
     *
     * @return Request
     */
    public function apply(Request $request, ParamConverter $configuration): Request
    {
        $json = $request->attributes->get('json');
        $json = json_decode($json, true);

        // We update the new value of the attribute
        $request->attributes->set('json', $json);
    }
}
