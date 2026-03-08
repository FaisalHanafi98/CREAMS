<?php

namespace Tests\Traits;

trait AssertsValidation
{
    /**
     * Assert that a field is required
     *
     * @param string $route
     * @param array $validData
     * @param string $field
     * @param string $method
     * @return void
     */
    protected function assertRequiredField(string $route, array $validData, string $field, string $method = 'post'): void
    {
        $data = $validData;
        unset($data[$field]);

        $response = $this->$method($route, $data);

        $response->assertSessionHasErrors($field);
    }

    /**
     * Assert that a field has a maximum length
     *
     * @param string $route
     * @param array $validData
     * @param string $field
     * @param int $max
     * @param string $method
     * @return void
     */
    protected function assertMaxLength(string $route, array $validData, string $field, int $max, string $method = 'post'): void
    {
        $data = $validData;
        $data[$field] = str_repeat('a', $max + 1);

        $response = $this->$method($route, $data);

        $response->assertSessionHasErrors($field);
    }

    /**
     * Assert that a field has a minimum length
     *
     * @param string $route
     * @param array $validData
     * @param string $field
     * @param int $min
     * @param string $method
     * @return void
     */
    protected function assertMinLength(string $route, array $validData, string $field, int $min, string $method = 'post'): void
    {
        $data = $validData;
        $data[$field] = str_repeat('a', $min - 1);

        $response = $this->$method($route, $data);

        $response->assertSessionHasErrors($field);
    }

    /**
     * Assert that a field must be a valid email
     *
     * @param string $route
     * @param array $validData
     * @param string $field
     * @param string $method
     * @return void
     */
    protected function assertEmailFormat(string $route, array $validData, string $field, string $method = 'post'): void
    {
        $data = $validData;
        $data[$field] = 'not-an-email';

        $response = $this->$method($route, $data);

        $response->assertSessionHasErrors($field);
    }

    /**
     * Assert that a field must be unique
     *
     * @param string $route
     * @param array $validData
     * @param string $field
     * @param string $method
     * @return void
     */
    protected function assertUnique(string $route, array $validData, string $field, string $method = 'post'): void
    {
        // First create a record with this value
        $this->$method($route, $validData);

        // Try to create another with the same value
        $response = $this->$method($route, $validData);

        $response->assertSessionHasErrors($field);
    }

    /**
     * Assert that multiple fields are required
     *
     * @param string $route
     * @param array $validData
     * @param array $fields
     * @param string $method
     * @return void
     */
    protected function assertRequiredFields(string $route, array $validData, array $fields, string $method = 'post'): void
    {
        foreach ($fields as $field) {
            $this->assertRequiredField($route, $validData, $field, $method);
        }
    }

    /**
     * Assert that validation passes with valid data
     *
     * @param string $route
     * @param array $validData
     * @param string $method
     * @return void
     */
    protected function assertValidationPasses(string $route, array $validData, string $method = 'post'): void
    {
        $response = $this->$method($route, $validData);

        $response->assertSessionHasNoErrors();
    }
}
