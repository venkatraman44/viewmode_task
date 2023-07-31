<?php



namespace Drupal\custom_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a custom block block.
 *
 * @Block(
 *   id = "custom_block_custom_block",
 *   admin_label = @Translation("Custom block"),
 *   category = @Translation("Custom"),
 * )
 */
final class CustomBlockBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity display repository service.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   */
  protected $entityDisplayRepository;

  /**
   * Constructs a new CustomBlockBlock instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   * @param \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entityDisplayRepository
   *   The entity display repository service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, $entityTypeManager, EntityDisplayRepositoryInterface $entityDisplayRepository) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entityTypeManager;
    $this->entityDisplayRepository = $entityDisplayRepository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('entity_display.repository')
    );
  }

  // /**
  //  * {@inheritdoc}
  //  */
  // public function defaultConfiguration() {
  //   return [
  //     'task2' => '',
  //     'view_mode' => 'teaser',
  //   ] + parent::defaultConfiguration();
  // }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state): array {
    $form['task2'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Node title'),
      '#target_type' => 'node',
      '#selection_settings' => [
        'target_bundles' => ['shapes'],
      ],
    ];

    $view_modes = $this->entityDisplayRepository->getViewModes('node');
    $options = [];
    foreach ($view_modes as $view_mode => $info) {
      $options[$view_mode] = $info['label'];
    }

    $form['view_mode'] = [
      '#type' => 'radios',
      '#title' => $this->t('View Mode'),
      '#options' => $options,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state): void {
    $this->configuration['task2'] = $form_state->getValue('task2');
    $this->configuration['view_mode'] = $form_state->getValue('view_mode');
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $node_id = $this->configuration['task2'];
    $node = $this->entityTypeManager->getStorage('node')->load($node_id);
    $build = [];

    if ($node) {
      $view_mode = $this->configuration['view_mode'];
      $view_builder = $this->entityTypeManager->getViewBuilder('node');
      $build = $view_builder->view($node, $view_mode);
    }

    return $build;
  }

}
