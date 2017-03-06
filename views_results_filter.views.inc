<?php

/**
 * @file
 * Provide views data for views_results_filter.module.
 */

/**
 * Implements hook_views_data_alter().
 */
function views_results_filter_views_data_alter(array &$data) {
  $data['node_field_data']['views_results'] = [
    'title' => t('Views results'),
    'help' => t('Views results.'),
    'filter' => [
      'id' => 'views_results',
    ],
  ];
}
