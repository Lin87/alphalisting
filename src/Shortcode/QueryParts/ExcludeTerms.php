<?php
/**
 * Exclude Terms Query Part.
 *
 * @package alphalisting
 */

declare(strict_types=1);

namespace eslin87\AlphaListing\Shortcode\QueryParts;

if (!defined("ABSPATH")) {
    exit();
}

use eslin87\AlphaListing\Shortcode\Extension;
use eslin87\AlphaListing\Strings;

/**
 * Exclude Terms Query Part extension
 */
class ExcludeTerms extends Extension {
    /**
     * The attribute for this Query Part.
     *
     * @since 4.0.0
     * @var string
     */
    public $attribute_name = "exclude-terms";

    /**
     * The types of listing this shortcode extension may be used with.
     *
     * @since 4.0.0
     * @var array<string>
     */
    public $display_types = ["posts", "terms"];

    /**
     * Update the query with this extension's additional configuration.
     *
     * @param \AlphaListing\Query $query      The query.
     * @param string             $display    The display/query type.
     * @param string             $key        The name of the attribute.
     * @param mixed              $value      The shortcode attribute value.
     * @param array              $attributes The complete set of shortcode attributes.
     * @return mixed The updated query.
     */
    public function shortcode_query_for_display_and_attribute( $query, string $display, string $key, $value, array $attributes) {

        $exclude_terms = $this->normalize_term_ids($value);

        if (empty($exclude_terms)) {
            return $query;
        }

        if ("terms" === $display) {
            $existing_exclusions = [];

            if (isset($query["exclude"])) {
                $existing_exclusions = $this->normalize_term_ids(
                    $query["exclude"],
                );
            }

            $query["exclude"] = array_values(
                array_unique(array_merge($existing_exclusions, $exclude_terms)),
            );

            return $query;
        }

        $taxonomy = isset($attributes["taxonomy"])
            ? $attributes["taxonomy"]
            : "category";

        $exclude_clause = [
            "taxonomy" => $taxonomy,
            "field" => "term_id",
            "terms" => $exclude_terms,
            "operator" => "NOT IN",
        ];

        $existing_tax_query = isset($query["tax_query"]) && is_array($query["tax_query"])
            ? $query["tax_query"]
            : [];

        $relation = null;
        if (isset($existing_tax_query["relation"])) {
            $existing_relation = strtoupper((string) $existing_tax_query["relation"]);
            if (in_array($existing_relation, ["AND", "OR"], true)) {
                $relation = $existing_relation;
            }
        }

        $tax_query = [];
        foreach ($existing_tax_query as $index => $clause) {
            if (is_int($index) && is_array($clause)) {
                $tax_query[] = $clause;
            }
        }

        $tax_query[] = $exclude_clause;
        if (null !== $relation) {
            $tax_query["relation"] = $relation;
        }

        $query["tax_query"] = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
        return $query;
    }

    /**
     * Convert a value into an array of positive term IDs.
     *
     * @param mixed $value The raw value.
     * @return array<int>
     */
    private function normalize_term_ids($value): array {
        if (is_string($value)) {
            $value = Strings::maybe_mb_split(",", $value);
        } elseif (is_int($value)) {
            $value = [$value];
        } elseif (!is_array($value)) {
            $value = [];
        }

        $value = array_map("intval", $value);
        $value = array_filter($value, function (int $term_id): bool {
            return 0 < $term_id;
        });

        return array_values(array_unique($value));
    }
}
