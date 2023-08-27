<?php

namespace WilokePostCategoriesAvenue\Controllers;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use Exception;
use Timber\Timber;
use WilokePostCategoriesAvenue\Share\App;
use WilokePostCategoriesAvenue\Share\TraitHandleAutoRenderSettingControls;

class PluginAddon extends Widget_Base
{
  use TraitHandleAutoRenderSettingControls;

  public static $aSettings = [];

  public function get_name()
  {
    return App::get('dataConfig')['name'];
  }

  public function get_title()
  {
    return App::get('dataConfig')['title'];
  }

  public function get_script_depends()
  {
    return App::get('handleJs');
  }

  public function get_icon()
  {
    return App::get('dataConfig')['icon'];
  }

  public function get_style_depends()
  {
    return App::get('handleCss');
  }

  public function get_categories()
  {
    return [App::get('dataConfig')['category']['key'] ?? 'basic'];
  }

  public function get_keywords()
  {
    return App::get('dataConfig')['keywords'];
  }

  protected function register_controls()
  {
    $aConfigs = $this->getDataConfigField();
    $this->handle($aConfigs, $this);
  }

  public function getDataConfigField(): array
  {
    return $this->loadSchema();
  }

  private function loadSchema()
  {
    $aSections = json_decode(file_get_contents(plugin_dir_path(__FILE__) .
      '../Configs/schema.json'), true);
    if (is_array($aSections)) {
      foreach ($aSections as $order => $aSection) {
        if (isset($aSection['type']) && $aSection['type'] == 'section') {
          if (!isset($aSection['default']) || empty($aSection['default'])) {
            $aDefault = $this->parseDefault($aSection);
            if (!empty($aDefault)) {
              $aSections[$order]['default'] = $aDefault;
            }
          }
        }
      }
    }

    return $aSections;
  }

  protected function parseDefault(array $aSection): array
  {
    if (isset($aSection['fields']) && is_array($aSection['fields'])) {
      $aDefault = [];
      foreach ($aSection['fields'] as $aField) {
        if ($aField['type'] == 'array') {
          $aSubChildren = $aField['fields'];
          $aSubChildrenDefault = [];
          foreach ($aSubChildren as $aSubChild) {
            if (isset($aSubChild['default'])) {
              $aSubChildrenDefault[$aSubChild['id']] = $aSubChild['default'];
            }
          }
          if (!empty($aSubChildrenDefault)) {
            $aDefault[$aField['id']][] = $aSubChildrenDefault;
          }
        } else {
          if (isset($aField['default'])) {
            $aDefault[$aField['id']] = $aField['default'];
          }
        }
      }

      return $aDefault;
    }
    return [];
  }


  protected function render()
  {
    try {


      Timber::$locations = WILOKE_WILOKEPOSTCATEGORIESAVENUE_VIEWS_PATH . 'src/Views';
      self::$aSettings = $this->get_settings_for_display();
      $aSchema = $this->loadSchema();

      if (is_array($aSchema)) {
        $aDefault = [];
        foreach ($aSchema as $aSection) {
          if (isset($aSection['default'])) {
            $aDefault = array_merge($aSection['default'], $aDefault);
          }
        }
        self::$aSettings = wp_parse_args(self::$aSettings, $aDefault);
      }

      Timber::render(plugin_dir_path(__FILE__) . "../Views/section.twig", [
        "data" => $this->parseItems(self::$aSettings)
      ]);
    }
    catch (Exception $oException) {
      ?>
      <p class="wil-element-warning wil-element-text-center">
        <?php esc_html_e('Something went error!', 'wiloke-post-categories-avenue'); ?>
        <?php echo esc_html($oException->getMessage()); ?>
      </p>
      <?php
    }
  }
}