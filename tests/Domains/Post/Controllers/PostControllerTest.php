<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../TestCase.php';

beforeEach(function () {
    $this->testCase = new TestCase();
    $this->testCase->setUp();
});

it('can list all posts', function () {
    $response = $this->testCase->get('/api/posts');
    $data = $this->testCase->getResponseData($response);
    
    expect($response->getStatusCode())->toBe(200);
    expect($data)->toHaveKey('success');
    expect($data['success'])->toBeTrue();
    expect($data)->toHaveKey('data');
    expect($data)->toHaveKey('count');
    expect($data['data'])->toBeArray();
});

it('can get a specific post by id', function () {
    $response = $this->testCase->get('/api/posts/1');
    $data = $this->testCase->getResponseData($response);
    
    expect($response->getStatusCode())->toBe(200);
    expect($data)->toHaveKey('success');
    expect($data['success'])->toBeTrue();
    expect($data)->toHaveKey('data');
    expect($data['data'])->toHaveKey('id');
    expect($data['data']['id'])->toBe('1');
});

it('handles search posts without title parameter', function () {
    $response = $this->testCase->get('/api/posts/search');
    $data = $this->testCase->getResponseData($response);
    
    expect($response->getStatusCode())->toBe(400);
    expect($data)->toHaveKey('success');
    expect($data['success'])->toBeFalse();
    expect($data)->toHaveKey('error');
    expect($data['error'])->toBe('Title parameter is required');
});

it('can create a new post with valid data', function () {
    $postData = [
        'title' => 'Test Post Title',
        'content' => 'This is a test post content that meets the minimum length requirements for validation.',
        'user_id' => 1
    ];
    
    $response = $this->testCase->post('/api/posts', $postData);
    $data = $this->testCase->getResponseData($response);
    
    expect($response->getStatusCode())->toBe(201);
    expect($data)->toHaveKey('success');
    expect($data['success'])->toBeTrue();
    expect($data)->toHaveKey('data');
    expect($data['data'])->toHaveKey('title');
    expect($data['data']['title'])->toBe('Test Post Title');
    expect($data['data'])->toHaveKey('content');
    expect($data['data'])->toHaveKey('user_id');
});

it('validates post creation - title too short', function () {
    $postData = [
        'title' => 'Hi',
        'content' => 'Valid content that is long enough',
        'user_id' => 1
    ];
    
    $response = $this->testCase->post('/api/posts', $postData);
    $data = $this->testCase->getResponseData($response);
    
    expect($response->getStatusCode())->toBe(400);
    expect($data)->toHaveKey('success');
    expect($data['success'])->toBeFalse();
    expect($data)->toHaveKey('error');
    expect($data['error'])->toBe('Validation failed');
    expect($data)->toHaveKey('validation_errors');
    expect($data['validation_errors'])->toHaveKey('title');
    expect($data['validation_errors']['title'])->toContain('too short');
});

it('validates post creation - content too short', function () {
    $postData = [
        'title' => 'Valid Title',
        'content' => 'Short',
        'user_id' => 1
    ];
    
    $response = $this->testCase->post('/api/posts', $postData);
    $data = $this->testCase->getResponseData($response);
    
    expect($response->getStatusCode())->toBe(400);
    expect($data)->toHaveKey('validation_errors');
    expect($data['validation_errors'])->toHaveKey('content');
    expect($data['validation_errors']['content'])->toContain('too short');
});

it('can update a post with valid data', function () {
    $updateData = [
        'title' => 'Updated Post Title',
        'content' => 'Updated post content that meets validation requirements'
    ];
    
    $response = $this->testCase->put('/api/posts/1', $updateData);
    $data = $this->testCase->getResponseData($response);
    
    expect($response->getStatusCode())->toBe(200);
    expect($data)->toHaveKey('success');
    expect($data['success'])->toBeTrue();
    expect($data)->toHaveKey('message');
    expect($data['message'])->toBe('Post updated successfully');
});

it('can delete a post', function () {
    // First create a post to delete
    $postData = [
        'title' => 'Delete Test Post',
        'content' => 'This post will be deleted in the test to ensure reliable testing.',
        'user_id' => 1
    ];
    
    $createResponse = $this->testCase->post('/api/posts', $postData);
    $createData = $this->testCase->getResponseData($createResponse);
    $postId = $createData['data']['id'];
    
    // Now delete the post
    $response = $this->testCase->delete('/api/posts/' . $postId);
    $data = $this->testCase->getResponseData($response);
    
    expect($response->getStatusCode())->toBe(200);
    expect($data)->toHaveKey('success');
    expect($data['success'])->toBeTrue();
    expect($data)->toHaveKey('message');
    expect($data['message'])->toBe('Post deleted successfully');
}); 