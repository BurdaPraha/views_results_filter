<?php

namespace Drupal\views_results_filter\Plugin\views\filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\filter\FilterPluginBase;
use Drupal\views\Views;

/**
 * Views results filter plugin.
 *
 * @ViewsFilter("views_results")
 */
class ViewsResults extends FilterPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['exclude'] = ['default' => TRUE];
    $options['display'] = ['default' => 'default'];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
    $form['exclude'] = [
      '#type' => 'checkbox',
      '#title' => t('Exclude'),
      '#default_value' => $this->options['exclude'],
    ];

    $displays = $this->view->storage->get('display');
    $options = [];
    foreach ($this->view->storage->get('display') as $display) {
      // Exclude current display to avoid recursion.
      if ($display['id'] != $this->view->current_display) {
        $options[$display['id']] = $display['display_title'];
      }
    }

    $form['display'] = [
      '#title' => t('Display'),
      '#type' => 'select',
      '#options' => $options,
      '#default_value' => $this->options['display'],
      '#required' => TRUE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    if ($this->view->current_display != $this->options['display']) {
      $this->ensureMyTable();
      $nids = $this->getNids();
      if (count($nids) > 0) {
        $operator = $this->options['exclude'] ? 'NOT IN' : 'IN';
        $this->query->addWhere($this->options['group'], $this->tableAlias . '.nid', $nids, $operator);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function adminSummary() {
    $nids = $this->getNids();
    sort($nids);
    $args = ['@nids' => implode(',', $nids)];
    //return $this->options['exclude'] ? t('Exclude: @nids', $args) : t('Include: @nids.', $args);
    return $this->options['display'];
  }

  /**
   * Return NIDs to filter.
   */
  protected function getNids() {
    // Reload view to avoid cache issues.
    $view = Views::getView($this->view->id());
    $nids = [];
    $view->setDisplay($this->options['display']);
    $view->preExecute();
    $view->execute();
    foreach ($view->result as $row) {
      $nids[] = $row->nid;
    }
    return $nids;
  }

}
