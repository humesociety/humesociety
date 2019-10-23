<?php

namespace App\Entity\NewsItem;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The news item form type.
 */
class NewsItemType extends AbstractType
{
    /**
     * An associative array of news item IDs and labels.
     *
     * @var array
     */
    private $categories;

    /**
     * Constructor function.
     *
     * @param ParameterBagInterface $params Symfony's parameter bag interface.
     * @return void
     */
    public function __construct(ParameterBagInterface $params)
    {
        $this->categories = [];
        foreach ($params->get('news_categories') as $id => $category) {
            $this->categories[$category] = $id;
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
            ->add('category', ChoiceType::class, ['choices' => $this->categories])
            ->add('title')
            ->add('date', DateType::class, ['widget' => 'single_text'])
            ->add('end', DateType::class, ['widget' => 'single_text'])
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
        $resolver->setDefaults(['data_class' => NewsItem::class]);
    }
}
