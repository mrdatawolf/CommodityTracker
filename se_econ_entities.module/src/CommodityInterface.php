<?php

namespace Drupal\se_econ_entities;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface defining a Commodity entity.
 * @ingroup content_entity_example
 */
interface CommodityInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}

?>