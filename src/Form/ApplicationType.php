<?php

namespace App\Form;

use Symfony\Component\Form\{
    AbstractType,
    Extension\Core\Type\TextType,
    Extension\Core\Type\IntegerType,
    FormBuilderInterface,
};
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApplicationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextType::class, ['description' => 'Application content'])
            ->add('city', TextType::class, ['description' => 'Application city'])
            ->add('salaryClaim', IntegerType::class, ['description' => 'Application salary']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'data_class'        => 'App\Entity\Application',
            'csrf_protection'   => false,
        ));
    }
}

?>
