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
    private $pageTemplates;

    public function __construct(ParameterBagInterface $params)
    {
        $this->sections = [];
        foreach ($params->get('sections') as $id => $section) {
            $this->sections[$section] = $id;
        }
        $this->pageTemplates = [];
        foreach ($params->get('page_templates') as $id => $template) {
            $this->pageTemplates[$template] = $id;
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('section', ChoiceType::class, ['choices' => $this->sections])
            ->add('slug')
            ->add('title')
            ->add('template', ChoiceType::class, ['choices' => $this->pageTemplates])
            ->add('content');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => Page::class]);
    }
}
