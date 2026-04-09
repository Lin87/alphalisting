<?php
declare(strict_types=1);

if (!defined("ABSPATH")) {
    define("ABSPATH", __DIR__);
}

require_once __DIR__ . '/../src/Extension.php';
require_once __DIR__ . '/../src/Singleton.php';
require_once __DIR__ . '/../src/Strings.php';
require_once __DIR__ . '/../src/Shortcode/Extension.php';
require_once __DIR__ . '/../src/Shortcode/QueryParts/ExcludeTerms.php';

use eslin87\AlphaListing\Shortcode\QueryParts\ExcludeTerms;

$extension = new ExcludeTerms();

$attributes = [
    "taxonomy" => "category",
];

/**
 * Build a posts query through the exclude-terms query part.
 *
 * @param array $query The starting query args.
 * @return array
 */
function alphalisting_build_posts_query_with_exclude_terms(array $query): array {
    global $extension;
    global $attributes;

    return $extension->shortcode_query_for_display_and_attribute(
        $query,
        "posts",
        "exclude-terms",
        "10,11",
        $attributes,
    );
}

$query_without_tax_query = alphalisting_build_posts_query_with_exclude_terms([]);

if (!isset($query_without_tax_query["tax_query"][0])) {
    throw new RuntimeException("Expected a new tax_query clause when tax_query is absent.");
}

if (isset($query_without_tax_query["tax_query"]["relation"])) {
    throw new RuntimeException("Did not expect a relation key when none existed previously.");
}

$existing_numeric_clause = [
    [
        "taxonomy" => "post_tag",
        "field" => "term_id",
        "terms" => [33],
        "operator" => "IN",
    ],
];

$query_with_numeric_tax_query = alphalisting_build_posts_query_with_exclude_terms(
    ["tax_query" => $existing_numeric_clause],
);

if (count($query_with_numeric_tax_query["tax_query"]) !== 2) {
    throw new RuntimeException("Expected numeric tax_query clauses to be preserved and appended.");
}

if ($query_with_numeric_tax_query["tax_query"][0] !== $existing_numeric_clause[0]) {
    throw new RuntimeException("Expected existing numeric clause to remain unchanged.");
}

if (($query_with_numeric_tax_query["tax_query"][1]["operator"] ?? null) !== "NOT IN") {
    throw new RuntimeException("Expected appended clause to use NOT IN operator.");
}

$query_with_relation = alphalisting_build_posts_query_with_exclude_terms(
    [
        "tax_query" => [
            "relation" => "OR",
            [
                "taxonomy" => "category",
                "field" => "term_id",
                "terms" => [99],
                "operator" => "IN",
            ],
        ],
    ],
);

if (($query_with_relation["tax_query"]["relation"] ?? null) !== "OR") {
    throw new RuntimeException("Expected existing tax_query relation to be preserved.");
}

if (!isset($query_with_relation["tax_query"][1])) {
    throw new RuntimeException("Expected appended clause when tax_query contains a relation key.");
}

if (($query_with_relation["tax_query"][1]["operator"] ?? null) !== "NOT IN") {
    throw new RuntimeException("Expected appended clause operator to be NOT IN for relation tax_query.");
}

$query_with_named_clause = alphalisting_build_posts_query_with_exclude_terms(
    [
        "tax_query" => [
            "relation" => "AND",
            "featured_terms" => [
                "taxonomy" => "category",
                "field" => "slug",
                "terms" => ["featured"],
                "operator" => "IN",
            ],
        ],
    ],
);

if (($query_with_named_clause["tax_query"]["relation"] ?? null) !== "AND") {
    throw new RuntimeException("Expected relation to be preserved for named tax_query clauses.");
}

if (!isset($query_with_named_clause["tax_query"]["featured_terms"])) {
    throw new RuntimeException("Expected named tax_query clause to be preserved.");
}

if (($query_with_named_clause["tax_query"][0]["operator"] ?? null) !== "NOT IN") {
    throw new RuntimeException("Expected NOT IN clause to append without removing named clauses.");
}

echo "ExcludeTerms merges tax_query clauses correctly for absent, numeric, relation, and named-clause scenarios.\n";
