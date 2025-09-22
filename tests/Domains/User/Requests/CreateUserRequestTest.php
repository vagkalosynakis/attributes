<?php

declare(strict_types=1);

use App\Domains\User\Requests\CreateUserRequest;
use Symfony\Component\Validator\Validation;

beforeEach(function () {
    $this->validator = Validation::createValidatorBuilder()
        ->enableAnnotationMapping()
        ->getValidator();
});

it('passes validation with valid user data', function () {
    $request = new CreateUserRequest([
        'name' => 'John Doe',
        'email' => 'john@example.com'
    ]);

    $errors = $this->validator->validate($request);
    
    expect(count($errors))->toBe(0);
});

it('fails validation with empty name', function () {
    $request = new CreateUserRequest([
        'name' => '',
        'email' => 'john@example.com'
    ]);

    $errors = $this->validator->validate($request);
    
    expect(count($errors))->toBeGreaterThan(0);
    
    $nameErrors = [];
    foreach ($errors as $error) {
        if ($error->getPropertyPath() === 'name') {
            $nameErrors[] = $error->getMessage();
        }
    }
    
    expect($nameErrors)->not->toBeEmpty();
    expect(implode(' ', $nameErrors))->toContain('not be blank');
});

it('fails validation with invalid email format', function () {
    $request = new CreateUserRequest([
        'name' => 'John Doe',
        'email' => 'invalid-email'
    ]);

    $errors = $this->validator->validate($request);
    
    expect(count($errors))->toBeGreaterThan(0);
    
    $emailErrors = [];
    foreach ($errors as $error) {
        if ($error->getPropertyPath() === 'email') {
            $emailErrors[] = $error->getMessage();
        }
    }
    
    expect($emailErrors)->not->toBeEmpty();
    expect(implode(' ', $emailErrors))->toContain('valid email');
}); 