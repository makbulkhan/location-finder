# Location Finder Module

## Overview

The Location Finder module provides a form that allows users to search for locations based on country, city, and postal code. The results are displayed in YAML format and include only those locations that work on weekends and have an even number in their address. The results are loaded via AJAX after the user submits the form.

## Features

- **Location Search**: Users can search for locations by entering a country code, city name, and postal code.
- **Weekend and Address Filtering**: The module filters locations to include only those that operate on weekends and have an even number in their address.
- **AJAX Loading**: The results are displayed below the form without reloading the page, using AJAX.
- **YAML Output**: The results are presented in YAML format.

## Configuration

The module requires configuration for the API settings, which can be done through the settings form provided by the module.

### API Configuration

To configure the API settings:

1. Go to the `Configuration` > `Location Finder` page (`/admin/config/location-finder`).
2. Enter the following details:
   - **API URL**: The URL of the API endpoint that provides location data.
   - **API Key**: The header name of the API key used for authentication.
   - **API Value**: The actual API key value.
3. Save the configuration.

## Form Details

### Location Finder Form

- **Form ID**: `location_finder_form`
- **Fields**:
  - `Country Code`: A two-letter country code (e.g., `DE` for Germany). This field is required.
  - `City`: The name of the city (e.g., `Dresden`). This field is required.
  - `Postal Code`: The postal code (e.g., `01067`). This field is required.

When the user submits the form, the module sends a request to the configured API endpoint with the entered values. The results are then filtered and displayed below the form.

## Help

For more information about how to use the Location Finder module, you can visit the module's help page under `Help` > `Location Finder`.

