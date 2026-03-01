<?php

namespace Drupal\maple\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\media\Entity\Media;
use Drupal\file\Entity\File;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Url;

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
	

	/*
	 * Preprocess media images
	 *
	 * Create simple variables for the location data fields
     * Create a URL for a location map overlay from maps.geoapify.com
     *
     */
	#[Hook('preprocess_media')]
	public function preprocessMedia(&$variables): void {
		if ( $variables['view_mode'] == "picture_place") {
		    $base_url = Url::fromRoute('<front>', [], ['absolute' => TRUE])->toString();

			$id = $variables['media']->id();
			$loc = $variables['media']->get('field_location')->value;
			$lat = $variables['media']->get('field_latitude')->value;
			$lng = $variables['media']->get('field_longitude')->value;
			$tak = $variables['media']->get('field_taken')->value;
			$alt = $variables['media']->get('field_media_image')->alt;
			$uri = $base_url .
			    str_replace('public://', 'sites/default/files/', $variables['media']->field_media_image->entity->getFileUri() );
			$map = "https://maps.geoapify.com/v1/staticmap?" .
			'apiKey=' . 'afb07405abd846fc93dd0767b28f18d3' . '&' .
			'marker=lonlat:' . $lng . ',' . $lat . '&' .
			'width=600&height=400&zoom=8';

			$variables['data'] = [
				'id'  => $id,
				'loc' => $loc,
				'tak' => $tak,
				'alt' => $alt,
				'uri' => $uri,
				'map' => $map,
			];
		}
		else if  ( $variables['view_mode'] == "embedded") {
		    $base_url = Url::fromRoute('<front>', [], ['absolute' => TRUE])->toString();
			$id = $variables['media']->id();
			$alt = $variables['media']->get('field_media_image')->alt;
			$uri = $base_url .
			    str_replace('public://', 'sites/default/files/', $variables['media']->field_media_image->entity->getFileUri() );
	        $variables['data'] = [
				'id'  => $id,
				'alt' => $alt,
				'uri' => $uri,
			];
		}
	}

	/*
	 * Preprocess node
	 *
	 * Preprocess various node types with view modes
     *
     */
	#[Hook('preprocess_node')]
	public function preprocessNode(&$variables): void {

	    //  Content type Bicycle - View mode bike_card
		if ( $variables['node']->type->target_id == "bicycle" && $variables['view_mode'] == "bike_card" ) {

			$mediaId = $variables['node']->field_bicycle_picture[0]->get('target_id')->getValue();
			$media = Media::load($mediaId);
			$uri = str_replace('public://', '/sites/default/files/', $media->field_media_image->entity->getFileUri());

			$variables['bike'] = [
				'id'    => $variables['node']->id(),
				'title' => $variables['node']->title->value,
				'make'  => $variables['node']->field_make->value,
				'model' => $variables['node']->field_model->value,
				'year'  => $variables['node']->field_year->value,
				'miles' => $variables['node']->field_mileage->value,
				'from'  => $variables['node']->field_purchased_from->value,
				'text'  => $variables['node']->body->summary,
				'uri'   => $uri,
			];
		}

		//  Content type Ride - All view modes
		elseif ($variables['node']->type->target_id == "ride"){
			// ($variables['view_mode'] == "bike_ride "full"  "tour_ride" {

			$variables['ride'] = [
				'id'      => $variables['node']->id(),
				'title'   => $variables['node']->title->value,
				'date'    => $variables['node']->field_ridedate->value,
				'miles'   => $variables['node']->field_miles->value,
				'buddies' => $variables['node']->field_buddies->value,
				'bike' => [
					'name' => $variables['content']['field_bike'][0]['#plain_text'],
					'path' => $variables['content']['field_bike'][0]['#entity']->path->alias,
				],
			];

			// For tour rides we don't use the field_photos but rather query for all potots from that day
			if ($variables['view_mode'] == "tour_ride") {

			// Query media for pictures on this date
				$date = $variables['node']->field_ridedate->value;

				$entity_type_manager = \Drupal::entityTypeManager();
				$query = $entity_type_manager->getStorage('media')
					->getQuery()
					->accessCheck(FALSE);

				$condition = $query->andConditionGroup()
					->condition('bundle', 'image')
					->condition('field_taken', $date, "STARTS_WITH")
					->exists('field_tour');

				$query->condition($condition);
				$mids = $query->execute();
            	$variables['ride']['photos'] = $mids;
            }
		}

        //  Content type Tour - View mode tour_preview
        elseif ($variables['node']->type->target_id == "tour" && $variables['view_mode'] == "tour_preview" ) {
            $map = null;
            $mediaId = $variables['node']->field_overview_map->target_id;
            if (isset($mediaId)) {
                $media = Media::load($mediaId);
                $map  = str_replace('public://', '/sites/default/files/', $media->field_media_image->entity->getFileUri());
            }
        	$variables['tour'] = [
				'id'      => $variables['node']->id(),
				'title'   => $variables['node']->title->value,
				'date'    => $variables['node']->field_start_date->value,
                'miles'   => $variables['node']->field_mileage->value,
                'days'    => $variables['node']->field_number_of_days->value,
                'text'    => $variables['node']->field_short_description->value,
                'map'     => $map,
            ];
	   }

        // Content type Blog - All view modes
	    elseif ( $variables['node']->type->target_id == "blog" ) {

			foreach($variables['node']->field_tags as $index => $tag){
				$tags[$index] = $tag->target_id;
			}
			$variables['tags'] = $tags;
		}
	}

	// Not sure where I need this but I might
	// $source_url = $node->toUrl('canonical', ['absolute' => TRUE])->toString();


	/**
	 * Implements hook_views_ajax_data_alter().
	 *
	 * Populates the next_url field for the infinite scroll component
	 *
	 */
	#[Hook('preprocess_views_view')]
    public function preprocessViewsView(&$variables) {
		$view = $variables['view'];
		$pager = $view->pager;

		// Check for the existence of more records
		if ($pager && method_exists($pager, 'hasMoreRecords') && $pager->hasMoreRecords()) {
			$current_page = $view->getCurrentPage();

			// Build the query parameters, preserving existing filters/exposed inputs
			$query = \Drupal::request()->query->all();
			$query['page'] = $current_page + 1;

			$variables['next_url'] = \Drupal\Core\Url::fromRoute('<current>', [], [
			  'query' => $query,
			])->toString();
		}
	}

}
