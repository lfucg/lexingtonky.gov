<?php

namespace Drupal\search_api\Plugin\views\argument;

use Drupal\taxonomy\Entity\Term;

/**
 * Defines a contextual filter searching through all indexed taxonomy fields.
 *
 * Note: The plugin annotation below is not misspelled. Due to dependency
 * problems, the plugin is not defined here but in
 * search_api_views_plugins_argument_alter().
 *
 * @ingroup views_argument_handlers
 *
 * ViewsArgument("search_api_all_terms")
 *
 * @see search_api_views_plugins_argument_alter()
 */
class SearchApiAllTerms extends SearchApiTerm {

  /**
   * {@inheritdoc}
   */
  public function query($group_by = FALSE) {
    if (empty($this->value)) {
      $this->fillValue();
      if (empty($this->value)) {
        return;
      }
    }

    $not_negated = empty($this->options['not']);
    if ($not_negated) {
      $operator = '=';
      $conjunction = 'OR';
    }
    else {
      $operator = '<>';
      $conjunction = 'AND';
    }

    $terms = Term::loadMultiple($this->value);
    // If values were given, but weren't valid taxonomy term IDs, we abort the
    // query, as this wouldn't have yielded any results. (Unless the filter is
    // negated, in which case this is of course fine.)
    if (empty($terms)) {
      if ($not_negated) {
        $this->query->abort($this->t('No valid taxonomy term IDs given for "All taxonomy term fields" contextual filter.'));
      }
      return;
    }

    $vocabulary_fields = $this->definition['vocabulary_fields'];
    // Add an empty array for the "all vocabularies" fields, so this is always
    // present (to simplify the code below a bit).
    $vocabulary_fields += ['' => []];
    /** @var \Drupal\Core\Entity\EntityInterface $term */
    foreach ($terms as $term) {
      // Set filters for all term reference fields which don't specify a
      // vocabulary, as well as for all fields specifying the term's vocabulary.
      $vocabulary_id = $term->bundle();
      $term_id = $term->id();
      $term_conditions = $this->query->createConditionGroup($conjunction);
      if (!empty($vocabulary_fields[$vocabulary_id])) {
        foreach ($vocabulary_fields[$vocabulary_id] as $field) {
          $term_conditions->addCondition($field, $term_id, $operator);
        }
      }
      foreach ($vocabulary_fields[''] as $field) {
        $term_conditions->addCondition($field, $term_id, $operator);
      }

      // If any conditions were added to the condition group, add it to the
      // query. Otherwise, unless this filter is negated, we abort the query, as
      // the given taxonomy term doesn't belong to a vocabulary contained in any
      // indexed fields.
      if ($term_conditions->getConditions()) {
        $this->query->addConditionGroup($term_conditions);
      }
      elseif ($not_negated) {
        $variables = [
          '@id' => $term_id,
          '%label' => $term->label(),
          '%vocabulary' => $vocabulary_id,
        ];
        $this->query->abort($this->t('"All taxonomy term fields" contextual filter could not be applied as taxonomy term %label (ID: @id) belongs to vocabulary %vocabulary, not contained in any indexed fields.', $variables));
        return;
      }
    }

  }

}
