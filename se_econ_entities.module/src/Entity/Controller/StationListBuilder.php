<?php

namespace Drupal\se_econ_entities\Entity\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Url;

/**
 * Provides a list controller for se_econ_entities_station entity.
 *
 * @ingroup se_econ_entities
 */
class StationListBuilder extends EntityListBuilder {
    
    /**
     * {@inheritdoc}
     *
     * We override ::render() so that we can add our own content above the table.
     * parent::render() is where EntityListBuilder creates the table using our
     * buildHeader() and buildRow() implementations.
     */
    public function render() {
        $build['description'] = [
            '#markup' => $this->t('Content Entity Example implements a Stations model. These stations are fieldable entities. You can manage the fields on the <a href="@adminlink">Stations admin page</a>.', array(
                '@adminlink' => \Drupal::urlGenerator()
                    ->generateFromRoute('se_econ_entities.station_settings'),
            )),
        ];
        
        $build += parent::render();
        return $build;
    }
    
    /**
     * {@inheritdoc}
     *
     * Building the header and content lines for the station list.
     *
     * Calling the parent::buildHeader() adds a column for the possible actions
     * and inserts the 'edit' and 'delete' links as defined for the entity type.
     */
    public function buildHeader() {
        $header['id'] = $this->t('StationID');
        $header['name'] = $this->t('Name');
        $header['first_name'] = $this->t('First Name');
        $header['gender'] = $this->t('Gender');
        return $header + parent::buildHeader();
    }
    
    /**
     * {@inheritdoc}
     */
    public function buildRow(EntityInterface $entity) {
        /* @var $entity \Drupal\se_econ_entities\Entity\Station */
        $row['id'] = $entity->id();
        $row['name'] = $entity->link();
        $row['first_name'] = $entity->first_name->value;
        $row['gender'] = $entity->gender->value;
        return $row + parent::buildRow($entity);
    }
    
}
?>