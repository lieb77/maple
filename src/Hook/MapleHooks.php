<?php

namespace Drupal\maple\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\media\Entity\Media;
use Drupal\file\Entity\File;
use Drupal\Core\Form\FormStateInterface;

/**
 * Hook implementations for the Maple theme.
 */
class MapleHooks {

   /**
     *
     */
    #[Hook('form_system_theme_settings_alter')]
    public function formSystemThemeSettingsAlter(array &$form, FormStateInterface $form_state): void {

        if ($form_state->getBuildInfo()['args'][0] == 'maple') {
           $form['header_image'] = [
                '#type'  => "details",
                '#title' => "Background Image",
                '#open'  => true,
                'settings' => [
                    '#type' => 'container',
                    'image_path' => [
                        '#type' 		 => 'textfield',
                        '#title' 		 => 'Path to image',
                        '#default_value' => theme_get_setting("image_path"),
                    ],
                    'image_upload' => [
                        '#type' => "file",
                        '#title' => "Upload background image",
                        '#upload_validators' => [
                            'FileExtension' => [
                                'extensions' => "png jpg jpeg",
                            ],
                        ],
                    ],
                ],
            ];
        }
	}
  /**
   * Implements hook_preprocess_page().
   */
  #[Hook('preprocess_page')]
  public function preprocessPage(array &$variables): void {
	$variables['site_bg_url'] = theme_get_setting("image_path");
  }

	/**
	 * Provides a common template suggestion for our navbar menus.
	 */
	#[Hook('theme_suggestions_menu_alter')]
	public function menuSuggestionsAlter(array &$suggestions, array $variables): void {
		$menu_name = $variables['menu_name'] ?? '';
		
		// Define the specific menus we want to style as navbar menus
		$target_menus = ['main', 'menu-bike', 'menu-music'];
		
		if (in_array($menu_name, $target_menus)) {
		  // This allows you to use one template: menu--navbar.html.twig
		  $suggestions[] = 'menu__navbar';
		}
	}
	
}