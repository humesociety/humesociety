<?php

namespace App\Entity\Page;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PageType extends AbstractType
{
    private $sections;
    private $templates;

    public function __construct(ParameterBagInterface $params)
    {
        $this->sections = [];
        foreach ($params->get('sections') as $id => $section) {
            $this->sections[$section] = $id;
        }
        $this->templates = [];
        foreach ($params->get('templates') as $id => $template) {
            $this->templates[$template] = $id;
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('section', ChoiceType::class, ['choices' => $this->sections])
            ->add('slug')
            ->add('title')
            ->add('template', ChoiceType::class, ['choices' => $this->templates])
            ->add('content');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => Page::class]);
    }
}
