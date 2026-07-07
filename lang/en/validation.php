<?php

return [
    'confirmed' => 'The :attribute confirmation does not match.',
    'email' => 'The :attribute field must be a valid email address.',
    'enum' => 'The selected :attribute is invalid.',
    'exists' => 'The selected :attribute is invalid.',
    'image'    => 'The :attribute field must be an image (JPEG, PNG, GIF, or WebP).',
    'integer'  => 'The :attribute field must be an integer.',
    'max' => [
        'file'    => 'The :attribute field must not be greater than :max kilobytes.',
        'numeric' => 'The :attribute field must not be greater than :max.',
        'string'  => 'The :attribute field must not be greater than :max characters.',
    ],
    'min' => [
        'numeric' => 'The :attribute field must be at least :min.',
        'string'  => 'The :attribute field must be at least :min characters.',
    ],
    'numeric'  => 'The :attribute field must be a number.',
    'required' => 'The :attribute field is required.',
    'string' => 'The :attribute field must be a string.',
    'unique' => 'The :attribute has already been taken.',
    'attributes' => [
        'email' => 'email',
        'firstName' => 'first name',
        'lastName' => 'last name',
        'password' => 'password',
        'phone' => 'phone number',
        'role' => 'role',
        'status' => 'status',
        'partName' => 'part name',
        'partNumber' => 'part number',
        'quantity' => 'quantity',
        'price' => 'price',
        'image' => 'part image',
        'categoryId' => 'category',
        'name' => 'name',
    ],
];
