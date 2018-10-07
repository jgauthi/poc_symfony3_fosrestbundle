<?php
namespace MyRestBundle\Form;

use Symfony\Component\Form\{
    AbstractType,
    Extension\Core\Type\TextType,
    Extension\Core\Type\PasswordType,
    FormBuilderInterface,
};
use Symfony\Component\OptionsResolver\OptionsResolver;

class CredentialsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('login', TextType::class, ['description' => 'Login utilisateur']);
        $builder->add('password', PasswordType::class, ['description' => 'Mot de passe utilisateur']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => 'MyRestBundle\Entity\Credentials',
            'csrf_protection' => false
        ]);
    }
}
