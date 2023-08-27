<?php


namespace WooKit\SmartBar\Controllers;


use WooKit\Shared\AutoPrefix;

class SmartBarRegistration
{
    private string $scheduleKey = 're_update_config';
    public function __construct()
    {
        add_action('cmb2_admin_init', [$this, 'registerBox']);
        add_action('init', [$this, 'registerSmartBar']);
        add_action('init', [$this, 'setScheduleKey']);
        add_action('update_post_meta', [$this, 'eventUpdateConfigAfterUpdatePost'], 10, 4);
        add_action('add_post_metadata', [$this, 'eventUpdateConfigAfterUpdatePost'], 10, 4);
        //add_action(AutoPrefix::namePrefix($this->scheduleKey), [$this, 'reUpdateConfig']);
    }
    public function reUpdateConfig($postID)
    {
        $showOnMode = get_post_meta($postID, AutoPrefix::namePrefix('showOnPageMode'), true);
        $aConfig = get_post_meta($postID, AutoPrefix::namePrefix('config'), true);
        if ($showOnMode == 'all') {
            $aConfig['showOnPage']='all';
        }else{
            $aConfig['showOnPage'] = get_post_meta($postID, AutoPrefix::namePrefix('showOnPage'));
        }
        update_post_meta($postID, AutoPrefix::namePrefix('config'), $aConfig);
    }

    public function setScheduleKey()
    {
        $this->scheduleKey = AutoPrefix::namePrefix($this->scheduleKey);
    }

    public function eventUpdateConfigAfterUpdatePost($metaID, $postID, $metaKey, $metaValue)
    {
        if ($metaKey == AutoPrefix::namePrefix('showOnPage') &&
            (get_post_type($postID) == AutoPrefix::namePrefix('smartbar')
            )) {
            $postID = (string)$postID;
            $this->clearSchedule($postID);
            $this->setScheduleHook($postID);
        }
    }

    private function clearSchedule(string $postID): void
    {
        wp_clear_scheduled_hook($this->scheduleKey, [$postID]);
    }

    private function setScheduleHook(string $postID): void
    {
        settype($postID, 'string');

        wp_schedule_single_event(time() + 20, $this->scheduleKey, [$postID]);
    }
    public function registerBox()
    {
        $aConfig = include plugin_dir_path(__FILE__) . '../Configs/PostMeta.php';

        foreach ($aConfig as $aSection) {
            $aFields = $aSection['fields'];
            unset($aSection['fields']);
            $oCmb = new_cmb2_box($aSection);
            foreach ($aFields as $aField) {
                $aField['id'] = AutoPrefix::namePrefix($aField['id']);
                $oCmb->add_field($aField);
            }
        }
    }

    public function registerSmartBar()
    {
        register_post_type(
            AutoPrefix::namePrefix('smartbar'),
            include plugin_dir_path(__FILE__) . '../Configs/PostType.php'
        );
    }
}
