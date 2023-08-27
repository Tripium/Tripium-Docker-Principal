<?php

namespace WilcityOpenTable\Controllers;

use WilcityOpenTable\Helpers\Opentable;
use WilokeListingTools\Controllers\AddListingController;
use WilokeListingTools\Controllers\Retrieve\RestRetrieve;
use WilokeListingTools\Controllers\RetrieveController;
use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Framework\Helpers\SetSettings;
use WilokeListingTools\Framework\Helpers\Submission;
use WilokeListingTools\Framework\Routing\Controller;

class SaveOpenTableController extends Controller
{
    public function __construct()
    {
        add_action('wiloke-listing-tools/passed-preview-step', [$this, 'prepareSaveOpentable'], 10, 3);
        add_action('init', [$this, 'saveOpentableBackend'], 1);
        add_filter('wilcity/filter/wiloke-listing-tools/select2-values', [$this, 'handleSelect2OpenTableValue'], 10, 3);
    }
    
    public function handleSelect2OpenTableValue($aValues, $oField, $postID)
    {
        if ($oField->args('_name') === 'wilcity_my_opentable') {
            $aOption = self::getMyOpenTable($postID);
            
            if (!empty($aOption)) {
                $aOption['name'] = $aOption['label'];
                unset($aOption['label']);
                
                return [$aOption];
            }
            
            return [];
        }
        
        return $aValues;
    }
    
    public function getMyOpenTable($listingID)
    {
        $id = GetSettings::getPostMeta($listingID, 'opentable_id');
        if (empty($id)) {
            return false;
        }
        
        $name = GetSettings::getPostMeta($listingID, 'opentable_name');
        
        return [
          'label' => $name,
          'id'    => $id
        ];
    }
    
    public function saveOpentableBackend()
    {
        if (!$this->isAdminEditing() || !$this->checkAdminReferrer() || !$this->isWP53()) {
            return false;
        }
        
        $opentableID = isset($_POST['wilcity_my_opentable']) && !empty($_POST['wilcity_my_opentable']) ?
          $_POST['wilcity_my_opentable'] : '';
        
        if (empty($opentableID)) {
            $this->delete($_GET['post']);
            
            return true;
        }
        
        $aTable = Opentable::searchByRestaurantID($_POST['wilcity_my_opentable']);
        
        if (!empty($aTable)) {
            $this->save($_GET['post'], [
              'id'    => trim($_POST['wilcity_my_opentable']),
              'label' => Opentable::buildRestaurantName($aTable)
            ]);
        } else {
            $this->delete($_GET['post']);
        }
    }
    
    protected function save($listingID, $aData)
    {
        SetSettings::setPostMeta($listingID, 'opentable_id', $aData['id']);
        SetSettings::setPostMeta($listingID, 'opentable_name', $aData['label']);
    }
    
    protected function delete($listingID)
    {
        SetSettings::deletePostMeta($listingID, 'opentable_id');
        SetSettings::deletePostMeta($listingID, 'opentable_name');
    }
    
    /**
     * @param $listingID
     * @param $planID
     * @param $that AddListingController
     *
     * @return bool|mixed
     */
    public function prepareSaveOpentable($listingID, $planID, $that)
    {
        $oRetrieve = new RetrieveController(new RestRetrieve());
        $aData     = $that->getRestData('opentable');
        
        if (empty($aData) || !isset($aData['my_opentable'])) {
            $this->delete($listingID);
            
            return false;
        }
        
        $aData = $aData['my_opentable'];
        if (empty($aData['id'])) {
            $this->delete($listingID);
            
            return $oRetrieve->error(['msg' => esc_html__('The open table id is required', 'wilcity-opentable')]);
        }
        
        if (!Submission::isPlanSupported($planID, 'opentable')) {
            return false;
        }
        
        $this->save($listingID, $aData);
        
        return true;
    }
}
