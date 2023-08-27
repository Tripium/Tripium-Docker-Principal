<?php
namespace WilcityTranslation\Controllers;

use Gettext\Loader\PoLoader;
use Gettext\Generator\MoGenerator;

class TranslationController
{
    protected $aLocoTranslations = [];
    protected $aConfigTranslations = [];
    protected $aConfigChildThemeTranslations = [];
    
    public function __construct()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueueScripts']);
        add_action('wp_ajax_wilcity_sync_translation', [$this, 'replaceTranslations']);
//        add_filter('wilcity/filter/wiloke-listing-tools/vee-language', [$this, 'getVeeLocate']);
    }
    
    protected function getConfig()
    {
        return include get_template_directory().'/configs/config.translation.php';
    }
    
    /**
     * @return string
     */
    protected function getChildThemeConfigPath()
    {
        return get_stylesheet_directory().'/configs/';
    }
    
    protected function getChildThemeConfigFile()
    {
        return $this->getChildThemeConfigPath().'config.translation.php';
    }
    
    protected function getChildThemeConfig()
    {
        return include $this->getChildThemeConfigPath().'config.translation.php';
    }
    
    public function translate($eng)
    {
        if (!is_array($eng)) {
            if (isset($this->aLocoTranslations[$eng]) && !empty($this->aLocoTranslations[$eng])) {
                return $this->aLocoTranslations[$eng];
            }
            
            return $eng;
        } else {
            return array_map([$this, 'translate'], $eng);
        }
    }
    
    public function enqueueScripts()
    {
        if (isset($_GET['page']) && $_GET['page'] === 'loco-theme') {
            wp_enqueue_script(
              'wilcity-translation',
              WILCITY_TRANSLATION_JS_URL.'translation.js',
              [],
              WILCITY_TRANSLATION_VERSION,
              true
            );
        }
    }
    
    protected function parseTranslation()
    {
        $this->aLocoTranslations = array_reduce($this->aLocoTranslations, function ($aCarry, $oTranslation) {
            $aCarry[$oTranslation->getOriginal()] = $oTranslation->getTranslation();
            
            return $aCarry;
        }, []);
    }
    
    protected function writeFile($fileName, $content)
    {
        if (!function_exists('WP_Filesystem')) {
            require_once(ABSPATH.'wp-admin/includes/file.php');
        }
        
        WP_Filesystem();
        global $wp_filesystem;
        
        $content = is_array($content) ? json_encode($content) : $content;
        
        return $wp_filesystem->put_contents($this->getChildThemeConfigPath().$fileName, $content);
    }
    
    public function getVeeLocate($language)
    {
        if (!function_exists('WP_Filesystem')) {
            require_once(ABSPATH.'wp-admin/includes/file.php');
        }
        
        WP_Filesystem();
        global $wp_filesystem;
        
        $translationDir = get_template_directory().'/configs/vee-translation/';
        
        if (file_exists($translationDir.$language.'.json')) {
            return [
              'code'        => $language,
              'translation' => json_decode($wp_filesystem->get_contents($translationDir.$language.'.json'), true)
            ];
        }
        
        $aParseTranslation = explode('_', $language);
        if (file_exists($translationDir.$aParseTranslation[0].'.json')) {
            return [
              'code'        => $aParseTranslation[0],
              'translation' => json_decode($wp_filesystem->get_contents($translationDir.$aParseTranslation[0].'.json'), true)
            ];
        }
        
        $translationDir = get_stylesheet_directory().'/configs/vee-translation/';
        
        if (file_exists($translationDir.$aParseTranslation[0].'.json')) {
            return [
              'code'        => $aParseTranslation[0],
              'translation' => json_decode($wp_filesystem->get_contents($translationDir.$aParseTranslation[0].'.json'), true)
            ];
        }
        
        return [];
    }
    
    public function replaceTranslations()
    {
        if (!current_user_can('administrator')) {
            wp_send_json_error(['msg' => 'You do not have permission to access this area']);
        }
        
        $file = WP_CONTENT_DIR.'/'.$_POST['file_info'];
        if (!isset($_POST['file_info']) || !file_exists($file)) {
            wp_send_json_error(['msg' => 'The translation file does not exists']);
        }
        
        if (!function_exists('wilcityChildThemeScripts')) {
            wp_send_json_error(['msg' => 'In order to use this feature, you have to activate Wilcity Child Theme first. Please read <a href="https://documentation.wilcity.com/knowledgebase/how-can-i-setup-wilcity-child-theme/" target="_blank">How Can I setup Wilcity Child Theme?</a> to know how to do it.']);
        }
        
        $loader = new PoLoader();
        
        $oFileInfo               = $loader->loadFile($file);
        $this->aLocoTranslations = $oFileInfo->getTranslations();
        $this->parseTranslation();
        $this->aConfigTranslations = $this->getConfig();
        
        if (is_file($this->getChildThemeConfigFile())) {
            $this->aConfigChildThemeTranslations = $this->getChildThemeConfig();
        }
        
        foreach ($this->aConfigTranslations as $key => $eng) {
            $translated = $this->translate($eng);
            
            if ($translated === $eng) {
                if (isset($this->aConfigChildThemeTranslations[$key]) &&
                    $this->aConfigChildThemeTranslations[$key] !== $eng) {
                    $translated = $this->aConfigChildThemeTranslations[$key];
                }
            }
            
            $this->aConfigTranslations[$key] = $translated;
        }
        
        $status = $this->writeFile('config.translation.json', $this->aConfigTranslations);
        
        if ($status) {
            $this->writeFile('config.translation-'.$oFileInfo->getLanguage().'.json', $this->aConfigTranslations);
            
            wp_send_json_success(['msg' => 'The file has been synced']);
        }
        
        wp_send_json_error(['msg' => 'We could not write file to wilcity-childtheme -> configs folder. Please re-check write permission']);
    }
}
