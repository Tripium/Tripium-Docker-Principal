<?php

namespace WilcityOpenTable\Controllers;

use WILCITY_SC\SCHelpers;
use WilcityOpenTable\Helpers\Opentable;

class ShortcodeController
{
    public function __construct()
    {
        add_shortcode('wilcity_single_home_opentable', [$this, 'renderOpentableToSingleHome']);
        add_shortcode('wilcity_sidebar_opentable', [$this, 'renderOpentableToSingleSidebar']);
    }
    
    public function renderOpentableToSingleHome($aAtts)
    {
        global $post;
        $aAtts = shortcode_atts([
          'type'        => 'standard',
          'name'        => '',
          'theme'       => 'tall',
          'width'       => '100%',
          'height'      => '500px',
          'lang'        => get_locale(),
          'iframe_name' => 'wilcity-opentable-single-home',
          'rid'         => ''
        ], $aAtts);
        
        if (empty($aAtts['rid']) || empty($aAtts['name'])) {
            $aOpentable = Opentable::getListingOpenTable($post->ID);
            if (empty($aOpentable)) {
                return '';
            }
            
            $aAtts['rid']  = $aOpentable['id'];
            $aAtts['name'] = $aOpentable['label'];
        }
        wp_enqueue_script('wilcity-opentable');
        
        return $this->renderOpentable($aAtts);
    }
    
    public function renderOpentableToSingleSidebar($aArgs)
    {
        global $post;
        $aGeneralSettings = SCHelpers::decodeAtts($aArgs['atts']);
        
        $aAtts = shortcode_atts([
          'type'        => 'standard',
          'name'        => '',
          'theme'       => 'tall',
          'width'       => '100%',
          'height'      => '500px',
          'iframe_name' => 'wilcity-opentable-single-sidebar',
          'lang'        => get_locale(),
          'rid'         => ''
        ], []);
        
        if (empty($aAtts['rid']) || empty($aAtts['name'])) {
            $aOpentable = Opentable::getListingOpenTable($post->ID);
            if (empty($aOpentable)) {
                return '';
            }
            
            $aAtts['rid']  = $aOpentable['id'];
            $aAtts['name'] = $aOpentable['label'];
        }
        
        ob_start();
        ?>
        <div class="<?php echo esc_attr(apply_filters('wilcity/filter/class-prefix',
          'wilcity-sidebar-item-my-products content-box_module__333d9')); ?>">
            <?php wilcityRenderSidebarHeader($aGeneralSettings['name'], $aGeneralSettings['icon']); ?>
            <div class="content-box_body__3tSRB">
                <?php echo $this->renderOpentable($aAtts);; ?>
            </div>
        </div>
        <?php
        $content = ob_get_contents();
        ob_end_clean();
        
        return $content;
    }
    
    public function renderOpentable($aAtts)
    {
        $aAtts = shortcode_atts([
          'rid'         => '',
          'name'        => '',
          'type'        => 'standard',
          'theme'       => 'tall',
          'iframe'      => true,
          'domain'      => 'com',
          'lang'        => 'en-US',
          'width'       => '100%',
          'height'      => 'auto',
          'newtab'      => false,
          'overlay'     => true,
          'iframe_name' => 'opentable-make-reservation-widget',
          'ot_source'   => 'Restaurant%20website'
        ], $aAtts);
        
        if (empty($aAtts['rid'])) {
            return '';
        }
        
        if (strpos($aAtts['lang'], '_') !== false) {
            $aAtts['lang'] = str_replace('_', '-', $aAtts['lang']);
        } else {
            $aAtts['lang'] = $aAtts['lang'].'-'.strtoupper($aAtts['lang']);
        }
        
        $aiFrameArgs = shortcode_atts(
          [
            'rid'       => '',
            'type'      => 'standard',
            'theme'     => 'tall',
            'iframe'    => true,
            'domain'    => 'com',
            'lang'      => 'en-US',
            'newtab'    => false,
            'overlay'   => true,
            'ot_source' => 'Restaurant%20website'
          ],
          $aAtts
        );
        
        $url = add_query_arg(
          $aiFrameArgs,
          'https://www.opentable.com/widget/reservation/canvas'
        );
        
        ob_start();
        ?>
        <iframe src="<?php echo esc_url($url); ?>"
                width="<?php echo esc_attr($aAtts['width']); ?>"
                height="<?php echo esc_attr($aAtts['height']); ?>"
                frameborder="0"
                scrolling="0"
                id="<?php echo esc_attr($aAtts['iframe_name']); ?>"
                name="<?php echo esc_attr($aAtts['iframe_name']); ?>"
                title="<?php echo esc_attr($aAtts['name']); ?>"></iframe>
        <?php
        $content = ob_get_contents();
        ob_end_clean();
        
        return $content;
    }
}
