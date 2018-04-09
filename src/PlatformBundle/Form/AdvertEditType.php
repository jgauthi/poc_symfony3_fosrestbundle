<?php

namespace PlatformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class AdvertEditType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->remove('date')
				->remove('image')
				->add('image', ImageType::class, ['required' => false]);
    }

    public function getParent()
    {
        return AdvertType::class;
    }
}
