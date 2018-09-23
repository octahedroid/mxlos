<?php

namespace Drupal\venue;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\File\FileSystemInterface;
use GuzzleHttp\ClientInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class MapService.
 */
class MapService implements MapServiceInterface {

  /**
   * Drupal\Core\File\FileSystemInterface definition.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;
  /**
   * GuzzleHttp\ClientInterface definition.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;
  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;
  /**
   * Drupal\Core\Config\ConfigFactoryInterface definition.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new MapService object.
   */
  public function __construct(
    FileSystemInterface $file_system,
    ClientInterface $http_client,
    EntityTypeManagerInterface $entity_type_manager,
    ConfigFactoryInterface $config_factory
  ) {
    $this->fileSystem = $file_system;
    $this->httpClient = $http_client;
    $this->entityTypeManager = $entity_type_manager;
    $this->configFactory = $config_factory;
  }

  public function download($address, $fileName) {
    $config = $this->configFactory
      ->get('venue.googletoken');

    $mapKey = $config->get('token_key');
    $zoom = $config->get('zoom');

    $url = "https://maps.googleapis.com/maps/api/staticmap?center=$address&zoom=$zoom&size=600x300&maptype=roadmap&key=$mapKey";
    $mapDirectory = "public://google/static";
    $filePath = "$mapDirectory/$fileName";
    if (!is_dir($this->fileSystem->realpath($mapDirectory))) {
      $this->fileSystem
        ->mkdir($mapDirectory, NULL, TRUE);
    }
    $fileRealPath = $this->fileSystem
      ->realpath($filePath);
    $this->httpClient
      ->get($url, ['save_to' => $fileRealPath]);

    return $this->createImage($filePath);
  }

  private function createImage($uri) {
    $fileStorage = $this->entityTypeManager
      ->getStorage('file');
    $file = current($fileStorage
      ->loadByProperties(['uri' => $uri]));

    if (!$file) {
      $file = $fileStorage->create([
        'uri' => $uri,
      ]);

      $file->save();
    }

    return $file;
  }
}
