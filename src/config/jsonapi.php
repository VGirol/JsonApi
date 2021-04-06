<?php

return [

    /**
     * Specification version which is implemented by server
     */
    'version' => '1.0',

    /**
     * Media type used for each server response (Content-Type header)
     */
    'media-type' => 'application/vnd.api+json',

    /**
     * If false, allows unsafe characters (like space) in member names
     */
    'disallowUnsafeCharacters' => false,

    /**
     * Returns HTTP response with "Location" header when creating a resource
     */
    'creationAddLocationHeader' => true,

    /**
     * Allows full replacement of a relationship (PATCH request to a resource endpoint)
     */
    'relationshipFullReplacementIsAllowed' => false,

    /**
     * Allows POSTing resource with client-generated ID
     */
    'clientGeneratedIdIsAllowed' => true,

    'sort' => [
        /**
         * Allows fetching requests to be sorted
         */
        'allowed' => true,

        /**
         * Routes that are allowed to sort results (regex patterns)
         */
        'routes' => ['*.index']
    ],

    'filter' => [
        /**
         * Allows fetching requests to be filtered
         */
        'allowed' => true,

        /**
         * Routes that are allowed to filter results (regex patterns)
         */
        'routes' => ['*.index']
    ],

    'include' => [
        /**
         * Allows the inclusion of resources
         */
        'allowed' => true,

        /**
         * Routes that are allowed to include resources (regex patterns)
         */
        'routes' => ['*.index', '*.show', '*.store', '*.update']
    ],

    'fields' => [
        /**
         * Allows fetching requests to select specific columns
         */
        'allowed' => true,

        /**
         * Routes that are allowed to have field query (regex patterns)
         */
        'routes' => ['*.index', '*.show', '*.store', '*.update']
    ],

    'pagination' => [
        /**
         * Allows fetching requests to be paginated
         */
        'allowed' => true,

        /**
         * Routes that are allowed to be paginated (regex patterns)
         */
        'routes' => ['*.index']
    ],

    /**
     * Stops script execution at first error
     */
    'stopAtFirstError' => true,

    /**
     * Length of the stack trace added to an error response
     */
    'errorTraceLength' => 7,

    /**
     * The default model directory
     */
    'modelNamespace' => 'Models',

    /**
     * The number of times a transaction should be reattempted when a deadlock occurs
     */
    'transactionAttempts' => 5
];
