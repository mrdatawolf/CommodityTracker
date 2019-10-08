<?php

namespace Drupal\se_econ_entities;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface defining a Station entity.
 * @ingroup content_entity_example
 */
interface StationInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}

?>