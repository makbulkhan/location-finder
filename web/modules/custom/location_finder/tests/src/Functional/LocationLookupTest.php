<?php

namespace Drupal\Tests\location_finder\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests the Location Finder form functionality.
 *
 * @group location_finder
 */
class LocationLookupTest extends BrowserTestBase
{
  /**
   * Modules to enable.
   *
   * @var array
   */
    protected static $modules = ['location_finder'];

  /**
   * {@inheritdoc}
   */
    protected $defaultTheme = 'olivero';

  /**
   * Tests that the Location Finder page can be accessed.
   */
    public function testLocationFinderPageExists()
    {
        $this->drupalLogin($this->drupalCreateUser(['access content']));
        $this->drupalGet('/location-finder');
        $this->assertSession()->statusCodeEquals(200);
    }

  /**
   * Tests form submission with valid input.
   */
    public function testFormSubmissionSuccess()
    {
      // Simulate a successful API response.
      // Ensure that the user has the necessary permission.
        $this->drupalLogin($this->drupalCreateUser(['access content']));

      // Navigate to the form page.
        $this->drupalGet('/location-finder');

      // Fill in the form values.
        $edit = [
        'country_code' => 'DE',
        'city' => 'Bonn',
        'postal_code' => '53313',
        ];
        $this->submitForm($edit, 'Find Locations');

      // Check that no error messages are shown.
        $this->assertSession()->pageTextNotContains('Error fetching data. Please try again later.');
        $this->assertSession()->pageTextNotContains('Please try with another city name and postal code for the selected country code!');

      // Optionally, assert that the expected output is displayed.
        $this->assertSession()->pageTextContains('Locations');
    }

  /**
   * Tests form validation errors.
   */
    public function testValidationErrors()
    {
        $this->drupalLogin($this->drupalCreateUser(['access content']));

      // Navigate to the form page.
        $this->drupalGet('/location-finder');

      // Invalid country code
        $edit = [
        'country_code' => 'GER',
        'city' => 'Bonn',
        'postal_code' => '53313',
        ];
        $this->submitForm($edit, 'Find Locations');
        $this->assertSession()->pageTextContains('Please enter a valid country code!');

      // Invalid postal code length
        $edit = [
        'country_code' => 'DE',
        'city' => 'Bonn',
        'postal_code' => '533',
        ];
        $this->submitForm($edit, 'Find Locations');
        $this->assertSession()->pageTextContains('Please enter a valid postal code!');

      // Invalid city name
        $edit = [
        'country_code' => 'DE',
        'city' => 'Bonn123',
        'postal_code' => '53313',
        ];
        $this->submitForm($edit, 'Find Locations');
        $this->assertSession()->pageTextContains('Please enter a valid city name!');
    }

  /**
   * Tests handling of API call failures.
   */
    public function testApiCallFailure()
    {
      // Mock or simulate an API failure.
        $this->drupalLogin($this->drupalCreateUser(['access content']));

      // Navigate to the form page.
        $this->drupalGet('/location-finder');

      // Fill in the form values.
        $edit = [
        'country_code' => 'DE',
        'city' => 'Bonn',
        'postal_code' => '53313',
        ];
        $this->submitForm($edit, 'Find Locations');

      // Check for the error message indicating an API failure.
        $this->assertSession()->pageTextContains('Error fetching data. Please try again later.');
    }

  /**
   * Tests filtered location output.
   */
    public function testFilteredLocationOutput()
    {
      // Simulate a successful API response with specific data.
        $this->drupalLogin($this->drupalCreateUser(['access content']));

      // Navigate to the form page.
        $this->drupalGet('/location-finder');

      // Fill in the form values.
        $edit = [
        'country_code' => 'DE',
        'city' => 'Bonn',
        'postal_code' => '53313',
        ];
        $this->submitForm($edit, 'Find Locations');

      // Assert that specific expected locations are in the YAML output.
        $this->assertSession()->pageTextContains('Expected location in YAML format');

      // Optionally, you can verify the specific content of the YAML output
      // if you have a known set of data you expect to be returned.
    }
}
