<?php


/**
 * @file
 * Contains \Drupal\embed_image\Twig\EmbedImageExtension.
 */

namespace Drupal\embed_image\Twig;

use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;


/**
 * Class EmbedViewExtension
 * Print a menu
 * @package Drupal\embed_view\Twig
 */
class EmbedImageExtension extends \Twig_Extension
{
    protected $imageFactory;
    protected $renderer;


    /**
     * EmbedImageExtension constructor.
     * @param \Drupal\embed_image\Twig\ImageFactory $imageFactory
     * @param \Drupal\embed_image\Twig\RendererInterface $renderer
     */
    public function __construct()
    {
        $this->imageFactory = \Drupal::service('image.factory');
        $this->renderer = \Drupal::service('renderer');
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'embed_image';
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('embed_responsive_image', [$this, 'embedRepsonsiveImage'], [
                'is_safe' => ['html'],
            ]),
            new \Twig_SimpleFunction('embed_image', [$this, 'embedImage'], [
                'is_safe' => ['html'],
            ]),
            new \Twig_SimpleFunction('img_style_url', [$this, 'getImageStyleUrl'], [
                'is_safe' => ['html'],
            ]),
        ];
    }


    /**
     * @param $target_id
     * @param string $responsive_image_style_id
     * @return array
     */
    public function embedRepsonsiveImage($target_id, $responsive_image_style_id = '')
    {
        $file = File::load($target_id);

        if ($file) {
            $variables = [
                'responsive_image_style_id' => $responsive_image_style_id,
                'uri' => $file->getFileUri(),
            ];

            $image = $this->imageFactory->get($file->getFileUri());

            if ($image->isValid()) {
                $variables['width'] = $image->getWidth();
                $variables['height'] = $image->getHeight();
            } else {
                $variables['width'] = $variables['height'] = NULL;
            }

            $responsiveImage = [
                '#theme' => 'responsive_image',
                '#width' => $variables['width'],
                '#height' => $variables['height'],
                '#responsive_image_style_id' => $variables['responsive_image_style_id'],
                '#uri' => $variables['uri'],
            ];


            $this->renderer->addCacheableDependency($responsiveImage, $file);

            return [$responsiveImage];
        }

        return [];
    }

    /**
     * @param $target_id
     * @param string $image_style
     * @return array
     */
    public function embedImage($target_id, $image_style = 'thumbnail')
    {
        $file = File::load($target_id);

        if ($file) {
            $variables = array(
                'style_name' => $image_style,
                'uri' => $file->getFileUri(),
            );

            $image = $this->imageFactory->get($file->getFileUri());

            if ($image->isValid()) {
                $variables['width'] = $image->getWidth();
                $variables['height'] = $image->getHeight();
            } else {
                $variables['width'] = $variables['height'] = NULL;
            }

            $imageBuild = [
                '#theme' => 'image_style',
                '#width' => $variables['width'],
                '#height' => $variables['height'],
                '#style_name' => $variables['style_name'],
                '#uri' => $variables['uri'],
            ];

            $this->renderer->addCacheableDependency($imageBuild, $file);

            return [$imageBuild];
        }

        return [];
    }

    /**
     * @param $target_id
     * @param string $image_style
     * @return array
     */
    public function getImageStyleUrl($imageUri, $image_style = 'thumbnail')
    {
        $url = null;

        if (empty($imageUri) === false) {
            $url = ImageStyle::load($image_style)->buildUrl($imageUri);
        }

        return $url;
    }
}