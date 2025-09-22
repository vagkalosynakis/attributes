<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../TestCase.php';

beforeEach(function () {
    $this->testCase = new TestCase();
    $this->testCase->setUp();
});

it('can list all users', function () {
    $response = $this->testCase->get('/api/users');
    $data = $this->testCase->getResponseData($response);
    
    expect($response->getStatusCode())->toBe(200);
    expect($data)->toHaveKey('success');
    expect($data['success'])->toBeTrue();
    expect($data)->toHaveKey('data');
    expect($data)->toHaveKey('count');
    expect($data['data'])->toBeArray();
});

it('can get a specific user by id', function () {
    $response = $this->testCase->get('/api/users/1');
    $data = $this->testCase->getResponseData($response);
    
    expect($response->getStatusCode())->toBe(200);
    expect($data)->toHaveKey('success');
    expect($data['success'])->toBeTrue();
    expect($data)->toHaveKey('data');
    expect($data['data'])->toHaveKey('id');
    expect($data['data']['id'])->toBe('1');
});

it('returns 404 for non-existent user', function () {
    $response = $this->testCase->get('/api/users/99999');
    $data = $this->testCase->getResponseData($response);
    
    expect($response->getStatusCode())->toBe(404);
    expect($data)->toHaveKey('success');
    expect($data['success'])->toBeFalse();
    expect($data)->toHaveKey('error');
});

it('can create a new user with valid data', function () {
    $uniqueEmail = 'test.user.' . time() . '@example.com';
    $userData = [
        'name' => 'Test User',
        'email' => $uniqueEmail
    ];
    
    $response = $this->testCase->post('/api/users', $userData);
    $data = $this->testCase->getResponseData($response);
    
    expect($response->getStatusCode())->toBe(201);
    expect($data)->toHaveKey('success');
    expect($data['success'])->toBeTrue();
    expect($data)->toHaveKey('data');
    expect($data['data'])->toHaveKey('name');
    expect($data['data']['name'])->toBe('Test User');
    expect($data['data'])->toHaveKey('email');
    expect($data['data']['email'])->toBe($uniqueEmail);
});

it('validates user creation - name too short', function () {
    $userData = [
        'name' => 'A',
        'email' => 'valid@example.com'
    ];
    
    $response = $this->testCase->post('/api/users', $userData);
    $data = $this->testCase->getResponseData($response);
    
    expect($response->getStatusCode())->toBe(400);
    expect($data)->toHaveKey('success');
    expect($data['success'])->toBeFalse();
    expect($data)->toHaveKey('error');
    expect($data['error'])->toBe('Validation failed');
    expect($data)->toHaveKey('validation_errors');
    expect($data['validation_errors'])->toHaveKey('name');
    expect($data['validation_errors']['name'])->toContain('too short');
});

it('validates user creation - invalid email', function () {
    $userData = [
        'name' => 'Valid Name',
        'email' => 'invalid-email'
    ];
    
    $response = $this->testCase->post('/api/users', $userData);
    $data = $this->testCase->getResponseData($response);
    
    expect($response->getStatusCode())->toBe(400);
    expect($data)->toHaveKey('success');
    expect($data['success'])->toBeFalse();
    expect($data)->toHaveKey('validation_errors');
    expect($data['validation_errors'])->toHaveKey('email');
    expect($data['validation_errors']['email'])->toContain('valid email');
});

it('can update a user with valid data', function () {
    $uniqueEmail = 'updated.email.' . time() . '@example.com';
    $updateData = [
        'name' => 'Updated Name',
        'email' => $uniqueEmail
    ];
    
    $response = $this->testCase->put('/api/users/1', $updateData);
    $data = $this->testCase->getResponseData($response);
    
    expect($response->getStatusCode())->toBe(200);
    expect($data)->toHaveKey('success');
    expect($data['success'])->toBeTrue();
    expect($data)->toHaveKey('message');
    expect($data['message'])->toBe('User updated successfully');
});

it('can delete a user', function () {
    // First create a user to delete
    $uniqueEmail = 'delete.test.' . time() . '@example.com';
    $userData = [
        'name' => 'Delete Test User',
        'email' => $uniqueEmail
    ];
    
    $createResponse = $this->testCase->post('/api/users', $userData);
    $createData = $this->testCase->getResponseData($createResponse);
    $userId = $createData['data']['id'];
    
    // Now delete the user
    $response = $this->testCase->delete('/api/users/' . $userId);
    $data = $this->testCase->getResponseData($response);
    
    expect($response->getStatusCode())->toBe(200);
    expect($data)->toHaveKey('success');
    expect($data['success'])->toBeTrue();
    expect($data)->toHaveKey('message');
    expect($data['message'])->toBe('User deleted successfully');
}); 