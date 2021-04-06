<?php

namespace VGirol\JsonApi\Messages;

use VGirol\JsonApiStructure\Messages as JsonApiStructureMessages;

abstract class Messages extends JsonApiStructureMessages
{
    public const ERROR_ACCEPT_HEADER_PARSING =
    'Accept header parsing failed.';
    public const ERROR_CONTENT_TYPE_HEADER_PARSING =
    'Content-Type header parsing failed.';
    public const ERROR_ACCEPT_HEADER_WITHOUT_PARAMETERS =
    'A request that include the JSON:API media type in her Accept header MUST specify the media type %s ' .
    'there at least once without any media type parameters.';
    public const ERROR_CONTENT_TYPE_HEADER_MISSING =
    'Clients MUST send all JSON:API data in request documents with the header "Content-Type: %s" ' .
    'without any media type parameters.';
    public const ERROR_CONTENT_TYPE_HEADER_BAD_MEDIA_TYPE =
    'A request MUST specify the header "Content-Type: %s".';
    public const ERROR_CONTENT_TYPE_HEADER_ALLREADY_SET =
    'The response header "Content-Type" is allready set with bad value : ' .
    'a response MUST specify the header "Content-Type: %s" without any media type parameters.';
    public const ERROR_CONTENT_TYPE_HEADER_WITHOUT_PARAMETERS =
    'A request MUST specify the header "Content-Type: %s" without any media type parameters.';

    public const BAD_ENDPOINT =
    'The requested resource was not found.';

    // public const FORM_REQUEST_ERROR_NO_DATA =
    // 'The request MUST include a single resource object as primary data.';
    // public const FORM_REQUEST_ERROR_MISSING_ID_MEMBER =
    // 'The resource object MUST contain at least a "id" member.';
    // public const FORM_REQUEST_ERROR_MISSING_TYPE_MEMBER =
    // 'The resource object MUST contain at least a "type" member.';

    public const FETCHING_REQUEST_NOT_FOUND =
    'The resource object with id equal to "%s" was not found.';
    public const NON_EXISTENT_RELATIONSHIP =
    'The resource object does not have a relationship called "%s".';

    // const UPDATING_ERROR_MANDATORY_FIELD_IS_MISSING =
    // 'The resource object MUST contain at least "type" and "id" members.';
    public const METHOD_NOT_ALLOWED_FOR_RELATIONSHIP =
    'This request method (%s) is not allowed for to-one relationships.';
    public const UPDATING_REQUEST_RELATED_NOT_FOUND =
    'One of the related resources with type "%s" was not found.';
    public const RELATIONSHIP_MALFORMATTED_NO_DATA =
    'The relationship "%s"" is not properly formatted ("data" member is missing).';
    public const SORTING_BAD_FIELD =
    'Can not sort result using one of these fields : "%s".';
    public const SORTING_IMPOSSIBLE_FOR_TO_ONE_RELATIONSHIP =
    'Sorting is not allowed for to-one relationships.';
    public const RELATIONSHIP_FULL_REPLACEMENT =
    'Full replacement of a to-many relationship is not allowed';
    public const CLIENT_GENERATED_ID_NOT_ALLOWED =
    'Client-generated ID along with a request to create a resource is not allowed';

    public const ERROR_QUERY_PARAMETER_SORT_NOT_ALLOWED_BY_SERVER =
    'This server does not allow to sort responses.';
    public const ERROR_QUERY_PARAMETER_SORT_NOT_ALLOWED_FOR_ROUTE =
    'This server does not allow to sort response for this route.';

    public const ERROR_QUERY_PARAMETER_INCLUDE_NOT_ALLOWED_BY_SERVER =
    'This server does not allow to include resources in responses.';
    public const ERROR_QUERY_PARAMETER_INCLUDE_NOT_ALLOWED_FOR_ROUTE =
    'This server does not allow to include resources in response for this route.';

    public const ERROR_QUERY_PARAMETER_FILTER_NOT_ALLOWED_BY_SERVER =
    'This server does not allow to filter resources in responses.';
    public const ERROR_QUERY_PARAMETER_FILTER_NOT_ALLOWED_FOR_ROUTE =
    'This server does not allow to filter resources in response for this route.';

    public const ERROR_QUERY_PARAMETER_FIELDS_NOT_ALLOWED_BY_SERVER =
    'This server does not allow to select resource\'s fields in responses.';
    public const ERROR_QUERY_PARAMETER_FIELDS_NOT_ALLOWED_FOR_ROUTE =
    'This server does not allow to select resource\'s fields in response for this route.';

    public const ERROR_QUERY_PARAMETER_PAGINATION_NOT_ALLOWED_BY_SERVER =
    'This server does not allow to paginate resources in responses.';
    public const ERROR_QUERY_PARAMETER_PAGINATION_NOT_ALLOWED_FOR_ROUTE =
    'This server does not allow to paginate resources in response for this route.';

    public const ERROR_FETCHING_SINGLE_WITH_NOT_ALLOWED_QUERY_PARAMETERS =
    'Sorting, filtering or pagination are not allowed when fetching single resource.';
}
