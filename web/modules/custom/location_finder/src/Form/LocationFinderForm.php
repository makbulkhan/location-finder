<?php

namespace Drupal\location_finder\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Serialization\Yaml;
use GuzzleHttp\ClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for finding locations.
 */
class LocationFinderForm extends FormBase
{
  /**
   * The HTTP client to fetch the feed data with.
   *
   * @var \GuzzleHttp\ClientInterface
   */
    protected $httpClient;

  /**
   * The configuration for the API settings.
   *
   * @var \Drupal\Core\Config\Config
   */
    protected $config;

  /**
   * Constructor for LocationFinder.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   *   A Guzzle client object.
   */
    public function __construct(ClientInterface $http_client)
    {
        $this->httpClient = $http_client;
        $this->config = $this->config('location_finder.settings');
    }

  /**
   * {@inheritdoc}
   */
    public static function create(ContainerInterface $container)
    {
        return new static(
            $container->get('http_client')
        );
    }

  /**
   * {@inheritdoc}
   */
    public function getFormId()
    {
        return 'location_finder_form';
    }

  /**
   * {@inheritdoc}
   */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
      // Retrieve the default values or previously submitted values.
        $country_code = $form_state->getValue('country_code', '');
        $city = $form_state->getValue('city', '');
        $postal_code = $form_state->getValue('postal_code', '');
        $yaml_output = $form_state->get('yaml_output') ?? '';

        $form['country_code'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Country'),
        '#required' => true,
        '#description' => $this->t('E.g., DE for Germany.'),
        '#default_value' => $country_code,
        ];

        $form['city'] = [
        '#type' => 'textfield',
        '#title' => $this->t('City'),
        '#required' => true,
        '#description' => $this->t('E.g., Dresden as city.'),
        '#default_value' => $city,
        ];

        $form['postal_code'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Postal Code'),
        '#required' => true,
        '#description' => $this->t('E.g., 01067 as postal code.'),
        '#default_value' => $postal_code,
        ];

        $form['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Find Locations'),
        '#ajax' => [
        'callback' => '::ajaxSubmitCallback',
        'wrapper' => 'location-result-wrapper',
        ],
        ];

      // Display the YAML output below the form.
        $form['result'] = [
        '#type' => 'textarea',
        '#title' => $this->t('Locations'),
        '#value' => $yaml_output,
        '#attributes' => ['readonly' => 'readonly'],
        '#prefix' => '<div id="location-result-wrapper">',
        '#suffix' => '</div>',
        ];

        $form['#attributes']['autocomplete'] = "off";

        return $form;
    }

  /**
   * AJAX callback to handle form submission.
   */
    public function ajaxSubmitCallback(array &$form, FormStateInterface $form_state)
    {
      // Return the part of the form that needs to be replaced.
        return $form['result'];
    }

  /**
   * {@inheritdoc}
   */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        if (strlen($form_state->getValue('country_code')) != 2) {
            $form_state->setErrorByName('country_code', $this->t('Please enter a valid country code!'));
        }
        if (strlen($form_state->getValue('postal_code')) < 5 || strlen($form_state->getValue('postal_code')) > 9) {
            $form_state->setErrorByName('postal_code', $this->t('Please enter a valid postal code!'));
        }
        $re = '/^[a-zA-Z\s]+$/';
        if (!preg_match($re, $form_state->getValue('city'))) {
            $form_state->setErrorByName('city', $this->t('Please enter a valid city name!'));
        }
    }

  /**
   * {@inheritdoc}
   */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $city = $form_state->getValue('city');
        $postalCode = $form_state->getValue('postal_code');
        $countryCode = $form_state->getValue('country_code');

      // Get API URL and API key from the configuration.
        $api_url = $this->config->get('api_url');
        $api_key = $this->config->get('api_key');
        $api_value = $this->config->get('api_value');

      // Prepare the API request.
        $response = $this->httpClient->get(
            $api_url,
            [
            'headers' => [
            $api_key => $api_value,
            ],
            'query' => [
            'countryCode' => $countryCode,
            'addressLocality' => $city,
            'postalCode' => $postalCode,
            ],
            ]
        );

        if ($response->getStatusCode() == 200) {
            $locations = json_decode($response->getBody(), true);

          // Process and filter locations.
            $filteredLocations = [];
            foreach ($locations['locations'] as $location) {
                // Check if the location works on weekends.
                $worksOnWeekends = $this->locationWorksOnWeekends($location['openingHours'] ?? []);

                // Check if the location's address has an even number.
                $hasEvenNumberInAddress = $this->addressHasEvenNumber($location['place']['address']['streetAddress']);

                // Include only locations that work on weekends and have an even number in the address.
                if ($worksOnWeekends && $hasEvenNumberInAddress) {
                    $filteredLocations[] = $location;
                }
            }

            if (!empty($filteredLocations)) {
              // Output filtered locations in YAML format.
                $yamlOutput = Yaml::encode($filteredLocations);

              // Store the YAML output in the form state to display it below the form.
                $form_state->set('yaml_output', $yamlOutput);
            } else {
                $form_state->set('yaml_output', $this->t("No locations found that meet the criteria."));
            }
        } else {
            $form_state->set('yaml_output', $this->t("Error fetching data. Please try again later."));
        }

      // Rebuild the form to display the results.
        $form_state->setRebuild(true);
    }

  /**
   * Checks if the location works on weekends.
   *
   * @param array $openingHours
   *   The opening hours of the location.
   *
   * @return bool
   *   TRUE if the location works on weekends, FALSE otherwise.
   */
    protected function locationWorksOnWeekends(array $openingHours)
    {
        $weekends = ['http://schema.org/Saturday', 'http://schema.org/Sunday'];
        foreach ($openingHours as $hours) {
            if (in_array($hours['dayOfWeek'], $weekends)) {
                return true;
            }
        }
        return false;
    }

  /**
   * Checks if the address has an even number.
   *
   * @param string $streetAddress
   *   The street address of the location.
   *
   * @return bool
   *   TRUE if the address has an even number, FALSE otherwise.
   */
    protected function addressHasEvenNumber($streetAddress)
    {
        if (preg_match('/\d+/', $streetAddress, $matches)) {
            $number = (int) $matches[0];
            return $number % 2 === 0;
        }
        return false;
    }
}
