<?php

declare(strict_types=1);

use App\Domains\Post\Requests\CreatePostRequest;
use Symfony\Component\Validator\Validation;

beforeEach(function () {
    $this->validator = Validation::createValidatorBuilder()
        ->enableAnnotationMapping()
        ->getValidator();
});

it('passes validation with valid post data', function () {
    $request = new CreatePostRequest([
        'title' => 'Valid Post Title',
        'content' => 'This is valid content that meets the minimum length requirements',
        'user_id' => 1
    ]);

    $errors = $this->validator->validate($request);
    
    expect(count($errors))->toBe(0);
});

it('fails validation with short title', function () {
    $request = new CreatePostRequest([
        'title' => 'Hi',
        'content' => 'Valid content here',
        'user_id' => 1
    ]);

    $errors = $this->validator->validate($request);
    
    expect(count($errors))->toBeGreaterThan(0);
    
    $titleErrors = [];
    foreach ($errors as $error) {
        if ($error->getPropertyPath() === 'title') {
            $titleErrors[] = $error->getMessage();
        }
    }
    
    expect(implode(' ', $titleErrors))->toContain('too short');
});

it('fails validation with invalid user_id', function () {
    $request = new CreatePostRequest([
        'title' => 'Valid Title',
        'content' => 'Valid content that is long enough',
        'user_id' => -1
    ]);

    $errors = $this->validator->validate($request);
    
    expect(count($errors))->toBeGreaterThan(0);
    
    $userIdErrors = [];
    foreach ($errors as $error) {
        if ($error->getPropertyPath() === 'user_id') {
            $userIdErrors[] = $error->getMessage();
        }
    }
    
    expect(implode(' ', $userIdErrors))->toContain('positive');
}); 