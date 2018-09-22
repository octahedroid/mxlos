<?php

namespace Drupal\venue;

use Drupal\Core\Entity\EntityInterface;

/**
 * Interface MapServiceInterface.
 */
interface MapServiceInterface {

  public function download($address, $fileName);

}
