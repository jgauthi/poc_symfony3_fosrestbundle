<?php

namespace MyRestBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApplicationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('author')
            ->add('content')
            ->add('city')
            ->add('salaryClaim');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'        => 'PlatformBundle\Entity\Application',
            'csrf_protection'   => false,
        ));
    }
}

?>
