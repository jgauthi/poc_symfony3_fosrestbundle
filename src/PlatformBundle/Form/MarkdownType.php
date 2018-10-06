<?php
namespace PlatformBundle\Form;

use Symfony\Component\Form\{
    AbstractType,
    Extension\Core\Type\TextareaType,
};
use Symfony\Component\OptionsResolver\OptionsResolver;

class MarkdownType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array
        (
            'attr'  =>  array('class' => 'markdown'),
        ));
    }

    public function getParent(): string
    {
        return TextareaType::class;
    }
}