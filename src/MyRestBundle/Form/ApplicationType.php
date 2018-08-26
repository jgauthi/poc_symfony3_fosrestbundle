<?php

namespace MyRestBundle\Form;

use Doctrine\DBAL\Types\IntegerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApplicationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('author', TextType::class, ['description' => 'Titre de la candidature'])
            ->add('content', TextType::class, ['description' => 'Contenu de la candidature'])
            ->add('city', TextType::class, ['description' => 'Ville de la candidature'])
            ->add('salaryClaim', IntegerType::class, ['description' => 'Salaire de la candidature']);
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
