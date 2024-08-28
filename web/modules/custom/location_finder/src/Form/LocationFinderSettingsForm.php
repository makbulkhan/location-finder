<?php

namespace Drupal\location_finder\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configuration form for Location Finder settings.
 */
class LocationFinderSettingsForm extends ConfigFormBase
{
  /**
   * {@inheritdoc}
   */
    protected function getEditableConfigNames()
    {
        return ['location_finder.settings'];
    }

  /**
   * {@inheritdoc}
   */
    public function getFormId()
    {
        return 'location_finder_settings_form';
    }

  /**
   * {@inheritdoc}
   */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $config = $this->config('location_finder.settings');

        $form['api_url'] = [
        '#type' => 'textfield',
        '#title' => $this->t('API URL'),
        '#default_value' => $config->get('api_url'),
        '#required' => true,
        ];

        $form['api_key'] = [
        '#type' => 'textfield',
        '#title' => $this->t('API Key'),
        '#default_value' => $config->get('api_key'),
        '#required' => true,
        ];

        $form['api_value'] = [
        '#type' => 'textfield',
        '#title' => $this->t('API Value'),
        '#default_value' => $config->get('api_value'),
        '#required' => true,
        ];

        return parent::buildForm($form, $form_state);
    }

  /**
   * {@inheritdoc}
   */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $this->configFactory->getEditable('location_finder.settings')
        ->set('api_url', $form_state->getValue('api_url'))
        ->set('api_key', $form_state->getValue('api_key'))
        ->set('api_value', $form_state->getValue('api_value'))
        ->save();

        parent::submitForm($form, $form_state);
    }
}
