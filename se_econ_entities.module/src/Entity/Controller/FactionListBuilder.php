<?php

namespace Drupal\se_econ_entities\Entity\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Url;

/**
 * Provides a list controller for se_econ_entities_saction entity.
 *
 * @ingroup se_econ_entities
 */
class FactionListBuilder extends EntityListBuilder {
    
    /**
     * {@inheritdoc}
     *
     * We override ::render() so that we can add our own content above the table.
     * parent::render() is where EntityListBuilder creates the table using our
     * buildHeader() and buildRow() implementations.
     */
    public function render() {
        $build['description'] = [
            '#markup' => $this->t('Content Entity Example implements a Factions model. These sactions are fieldable entities. You can manage the fields on the <a href="@adminlink">Factions admin page</a>.', array(
                '@adminlink' => \Drupal::urlGenerator()
                    ->generateFromRoute('se_econ_entities.faction_settings'),
            )),
        ];
        
        $build += parent::render();
        return $build;
    }
    
    /**
     * {@inheritdoc}
     *
     * Building the header and content lines for the faction list.
     *
     * Calling the parent::buildHeader() adds a column for the possible actions
     * and inserts the 'edit' and 'delete' links as defined for the entity type.
     */
    public function buildHeader() {
        $header['id'] = $this->t('FactionID');
        $header['name'] = $this->t('Name');
        $header['first_name'] = $this->t('First Name');
        $header['gender'] = $this->t('Gender');
        return $header + parent::buildHeader();
    }
    
    /**
     * {@inheritdoc}
     */
    public function buildRow(EntityInterface $entity) {
        /* @var $entity \Drupal\se_econ_entities\Entity\Faction */
        $row['id'] = $entity->id();
        $row['name'] = $entity->link();
        $row['first_name'] = $entity->first_name->value;
        $row['gender'] = $entity->gender->value;
        return $row + parent::buildRow($entity);
    }
    
}
?>