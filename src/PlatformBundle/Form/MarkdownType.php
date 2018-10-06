<?php
namespace PlatformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MarkdownType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array
        (
            'attr'  =>  array('class' => 'markdown'),
        ));
    }

    public function getParent()
    {
        return TextareaType::class;
    }
}