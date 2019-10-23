<?php

namespace App\Entity\Page;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The page form type.
 */
class PageType extends AbstractType
{
    /**
     * An associative array of section IDs and labels.
     *
     * @var array
     */
    private $sections;

    /**
     * An associative array of page template IDs and labels.
     *
     * @var array
     */
    private $pageTemplates;

    /**
     * Constructor function.
     *
     * @param ParameterBagInterface $params Symfony's parameter bag interface.
     * @return void
     */
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

    /**
     * Build the form.
     *
     * @param FormBuilderInterface $builder Symfony's form builder interface.
     * @param array An array of options.
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('section', ChoiceType::class, ['choices' => $this->sections])
            ->add('slug')
            ->add('title')
            ->add('template', ChoiceType::class, ['choices' => $this->pageTemplates])
            ->add('content');
    }

    /**
     * Configure the form's options.
     *
     * @param OptionsResolver $resolver Symfony's options resolver.
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => Page::class]);
    }
}
