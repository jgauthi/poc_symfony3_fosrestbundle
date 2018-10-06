<?php

namespace PlatformBundle\Form;

use Symfony\Component\Form\{AbstractType, FormBuilderInterface};

class AdvertEditType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->remove('date')
				->remove('image')
				->add('image', ImageType::class, ['required' => false]);
    }

    public function getParent(): string
    {
        return AdvertType::class;
    }
}
