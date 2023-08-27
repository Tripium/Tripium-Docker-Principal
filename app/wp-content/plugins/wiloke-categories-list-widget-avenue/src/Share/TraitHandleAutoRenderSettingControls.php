<?php

namespace WilokePostCategoriesAvenue\Share;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;
use Elementor\Widget_Base;

trait TraitHandleAutoRenderSettingControls
{
	public array $convertTypeElementor
		= [
			'text'            => Controls_Manager::TEXT,
			'number'          => Controls_Manager::NUMBER,
			'textarea'        => Controls_Manager::TEXTAREA,
			'wysiwyg'         => Controls_Manager::WYSIWYG,
			'code'            => Controls_Manager::CODE,
			'hidden'          => Controls_Manager::HIDDEN,
			'switcher'        => Controls_Manager::SWITCHER,
			'popover_toggle'  => Controls_Manager::POPOVER_TOGGLE,
			'select'          => Controls_Manager::SELECT,
			'select2'         => Controls_Manager::SELECT2,
			'choose'          => Controls_Manager::CHOOSE,
			'color'           => Controls_Manager::COLOR,
			'icons'           => Controls_Manager::ICONS,
			'image'           => Controls_Manager::MEDIA,
			'font'            => Controls_Manager::FONT,
			'dateTime'        => Controls_Manager::DATE_TIME,
			'gallery'         => Controls_Manager::GALLERY,
			'array'           => Controls_Manager::REPEATER,
			'tabs'            => Controls_Manager::TABS,
			'tab'             => Controls_Manager::TAB,
			'section'         => Controls_Manager::TAB_CONTENT,
			'section_style'   => Controls_Manager::TAB_STYLE,
			'media'           => Controls_Manager::MEDIA,
			'url'             => Controls_Manager::URL,
			'slider'          => Controls_Manager::SLIDER,
			'divider'         => Controls_Manager::DIVIDER,
			'wil_post'        => 'wil-post',
			'wil_cat_post'    => 'wil-cat-post',
			'wil_product'     => 'wil-product',
			'wil_cat_product' => 'wil-cat-product',
			'wil_slider'      => 'wil-slider',
			'typography'      => 'typography',
			'text_shadow'     => 'text_shadow',
			'box_shadow'      => 'box_shadow',
			'border'          => 'border',
			'background'      => 'background',
			'image_size'      => 'image_size',
			'css_filter'      => 'css_filter',
		];
	public array $groupControl
		= ['typography', 'text_shadow', 'box_shadow', 'border', 'background',
		   'image_size',
		   'css_filter'];
	public array $aTypography
		= [
			'font_family', 'font_style', 'text_decoration', 'font_size',
			'font_weight', 'text_transform',
			'line_height',
			'letter_spacing', 'word_spacing',
		];

	private function detectFieldType($type)
	{
		$realFieldType = $this->convertTypeElementor[$type] ?? "";

		if (empty($realFieldType)) {
			return null;
		}

		if (strpos($realFieldType, "wil-") !== 0) {
			return $realFieldType;
		}

		// là custom field
		return WILOKE_WILOKEPOSTCATEGORIESAVENUE_NAMESPACE . $realFieldType;
	}

	public function formatFieldGroup()
	{
		$this->convertTypeElementor['typography']
			= Group_Control_Typography::get_type();
		$this->convertTypeElementor['text_shadow']
			= Group_Control_Text_Shadow::get_type();
		$this->convertTypeElementor['box_shadow']
			= Group_Control_Box_Shadow::get_type();
		$this->convertTypeElementor['border'] = Group_Control_Border::get_type();
		$this->convertTypeElementor['background']
			= Group_Control_Background::get_type();
		$this->convertTypeElementor['image_size']
			= Group_Control_Image_Size::get_type();
		$this->convertTypeElementor['css_filter']
			= Group_Control_Css_Filter::get_type();
	}

	public function handleFieldsAddControls($aFields,
	                                        $hadTypeRepeater = false): array
	{
		$aData = [];
		if (empty($aFields)) {
			return [];
		}
		if ($hadTypeRepeater) {
			$oRepeater = new Repeater();
		}
		foreach ($aFields as $aItem) {
			if ($hadTypeRepeater) {
				$aDataItems = $aItem;
				$aDataItems['default'] = $this->convertSwitchToBoolean($aItem['default']
					?? '', 'in');
				if (isset($aItem['condition']) && !empty($aItem['condition'])) {
					foreach ($aItem['condition'] as $key => $values) {
						$aDataItems['condition'][$key]
							= $this->convertSwitchToBoolean($values, 'in');
					}
				}
				//check control group
				if (in_array($aItem['type'], $this->groupControl)) {
					$oRepeater->add_group_control(
						$this->detectFieldType($aItem['type']),
						$aDataItems
					);
				} else {
					$aDataItems = $this->checkFieldFont($aDataItems);
					$aDataItems['type'] = $this->detectFieldType($aDataItems['type']);
					$oRepeater->add_control(
						$aItem['id'],
						$aDataItems
					);
				}

			} else {
				$aRawItem = $aItem;
				$aRawItem['type'] = $this->detectFieldType($aItem['type']);
				$aDataItem['default'] = $this->convertSwitchToBoolean($aItem['default'],
					'in');
				$aDataItem = $this->checkFieldFont($aRawItem);
				if (isset($aItem['condition']) && !empty($aItem['condition'])) {
					foreach ($aItem['condition'] as $key => $values) {
						$aRawItem['condition'][$key]
							= $this->convertSwitchToBoolean($values, 'in');
					}
				}
				$aData[] = $aRawItem;
			}
		}

		return $hadTypeRepeater ? $oRepeater->get_controls() : $aData;
	}

	protected function checkFieldFont($aDataField)
	{
		if (isset($aDataField['selector'])) {
			return $aDataField;
		}
		if (($aDataField['type'] == 'font' && empty($aDataField['selectors']))) {
			$aDataField['selectors'] = [
				'{{WRAPPER}} .title' => 'font-family: {{VALUE}}'
			];
		}
		return $aDataField;
	}

	protected function convertSwitchToBoolean($value, $status = 'out')
	{
		if ($status == 'in') {
			if (is_bool($value)) {
				return $value ? 'yes' : 'no';
			}

		} else {
			if (in_array($value, ['yes', 'no'])) {
				return $value == 'yes';
			}
		}
		return $value;
	}

	public function handle(array $aData, Widget_Base $that)
	{
		$this->formatFieldGroup();

		foreach ($aData as $aSession) {
			if (in_array($aSession['type'], ['section', 'section_style'])) {
				$aSessionField = $aSession;
				unset($aSessionField['fields']);
				$aSessionField['tab'] = $this->detectFieldType($aSession['type']);

				$that->start_controls_section(
					$aSession['id'],
					$aSessionField
				);

				if (!empty($aSession['fields'])) {
					foreach ($aSession['fields'] as $aFields) {
						if ($aFields['type'] == 'tabs') {
							$that->start_controls_tabs(
								$aFields['id']
							);
							if (!empty($aFields['fields'])) {

								foreach ($aFields['fields'] as $aItemFields) {

									if ($aItemFields['type'] == 'tab') {
										$that->start_controls_tab(
											$aItemFields['id'],
											$aItemFields
										);
										if (!empty($aItemFields['fields'])) {

											foreach ($aItemFields['fields'] as $aField) {
												$aDataFieldTab = $aField;
												$aDataFieldTab['default']
													= $this->convertSwitchToBoolean($aField['default'],
													'in');
												if (isset($aField['condition']) &&
													!empty($aField['condition'])) {
													foreach ($aField['condition'] as $key => $values) {
														$aDataFieldTab['condition'][$key]
															= $this->convertSwitchToBoolean($values, 'in');
													}
												}
												$that->add_control(
													$aField['id'],
													$aDataFieldTab
												);
											}
										}
										$that->end_controls_tab();
									} else {
										$aFieldsControl = $aItemFields;
										$aFieldsControl['type']
											= $this->detectFieldType($aItemFields['type']);
										$aFieldsControl['fields']
											= $this->handleFieldsAddControls($aItemFields['fields'] ??
											[],
											$aItemFields['type'] == 'repeater');
										if (isset($aFields['condition']) &&
											!empty($aFields['condition'])) {
											foreach ($aFields['condition'] as $key => $values) {
												$aFieldsControl['condition'][$key]
													= $this->convertSwitchToBoolean($values, 'in');
											}
										}

										$that->add_control(
											$aItemFields['id'],
											$aFieldsControl
										);
									}

								}
							}
							$that->end_controls_tabs();
						} else {
							$aFieldsControl = $aFields;
							$aFieldsControl['type']
								= $this->detectFieldType($aFields['type']);
							if ($aFieldsControl['type'] == 'popover_toggle' &&
								!empty($aFieldsControl['fields'])) {
								$that->add_control(
									$aFieldsControl['id'],
									$aFieldsControl
								);
								$that->start_popover();
								foreach ($aFieldsControl['fields'] as $aFieldsItems) {
									$that->add_control(
										$aFieldsItems['id'],
										$aFieldsItems
									);
								}
								$that->end_popover();
							} else {
								$aFieldsControl['fields']
									= $this->handleFieldsAddControls($aFields['fields'] ?? [],
									$aFieldsControl['type'] == 'repeater');
								$aFieldsControl['default']
									= $this->convertSwitchToBoolean($aFields['default'] ?? '',
									'in');

								if (isset($aFields['condition']) &&
									!empty($aFields['condition'])) {
									foreach ($aFields['condition'] as $key => $values) {
										$aFieldsControl['condition'][$key]
											= $this->convertSwitchToBoolean($values,
											'in');
									}
								}
								if (in_array($aFieldsControl['type'], $this->groupControl)) {
									$that->add_group_control(
										$this->detectFieldType($aFieldsControl['type']),
										$aFieldsControl
									);
								} else {
									if (empty($aFieldsControl['fields'])) {
										unset($aFieldsControl['fields']);
									}
									$aFieldsControl = $this->checkFieldFont($aFieldsControl);

									$that->add_control(
										$aFieldsControl['id'],
										$aFieldsControl
									);
								}
							}
						}
					}
				}
				$that->end_controls_section();
			}
		}
	}

	public function handleParseDataTypography($nameField, $aSettings): string
	{
		$aCssInline = [];
		foreach ($this->aTypography as $key) {
			if (isset($aSettings[$nameField . '_' . $key])) {
				$data = $aSettings[$nameField . '_' . $key];
				if (empty($data)) {
					continue;
				}
				if (is_array($data) && empty($data['size'])) {
					continue;
				}
				$aCssInline[] = is_array($data) ?
					$key . ': ' . $data['size'] . $data['unit'] : $key . ': ' . $data;
			}
		}
		$dateResponse = implode(';', $aCssInline);
		return !empty($dateResponse) ? $dateResponse . ';' : '';
	}

	public function handleParseDataPopoverToggle($aFields, $aSettings): array
	{
		$aData = [];
		foreach ($aFields as $aField) {
			if (is_array($aSettings[$aField['id']])) {
				if ($aField['type'] == 'media') {
					$aData[$aField['name']] = $aSettings[$aField['id']]['url'] ??
						$aField['default']['url'] ?? '';
					continue;
				}
				$aData[$aField['name']] = $aSettings[$aField['id']] ?:
					$aField['default'];
			} else {
				$aData[$aField['name']]
					= $this->convertSwitchToBoolean($aSettings[$aField['id']]) ?:
					$aField['default'];
			}
		}
		return $aData;
	}

	public function parseItems($aSettings): array
	{
		$aItems = [];
		$aConfigs = $this->getDataConfigField();
		foreach ($aConfigs as $aSection) {
			$aDataFields = [];
			if (!empty($aSection['fields'])) {
				if ($aSection['type'] == 'divider') {
					continue;
				}
				foreach ($aSection['fields'] as $aFields) {
					if ($aFields['type'] === 'popover_toggle') {
						$aDataFields[$aFields['name']]
							= $this->handleParseDataPopoverToggle($aFields['fields'],
							$aSettings);
						continue;
					}

					if (isset($aSettings[$aFields['name'] . '_typography'])) {
						$aDataFields[$aFields['name']]
							= $this->handleParseDataTypography($aFields['name'], $aSettings);
						continue;
					}
					if (!isset($aSettings[$aFields['id']])) {
						$aDataFields[$aFields['name']] = '';
						continue;
					}

					if (is_array($aSettings[$aFields['id']])) {
						$aSettingsCustom = $aSettings[$aFields['id']];
						//check data custom post
						if ($aFields['type'] == 'wil_post') {
							$aDataFields[$aFields['name']] = HandleQueryData::initSingleClass()
								->queryDataPost($aSettingsCustom);
							continue;
						}
						//check data custom product
						if ($aFields['type'] == 'wil_product') {
							$aDataFields[$aFields['name']] = HandleQueryData::initSingleClass()
								->queryDataProduct($aSettingsCustom);
							continue;
						}
						//check data custom category post
						if ($aFields['type'] == 'wil_cat_post') {
							$aDataFields[$aFields['name']] = HandleQueryData::initSingleClass()
								->queryDataCatPost($aSettingsCustom);
							continue;
						}
						//check data custom category product
						if ($aFields['type'] == 'wil_cat_product') {
							$aDataFields[$aFields['name']] = HandleQueryData::initSingleClass()
								->queryDataCatProduct($aSettingsCustom);
							continue;
						}
						// data repeater
						$aResult = [];
						//get array field name
						if (!empty($aFields['fields'])) {
							$aNameField = [];
							foreach ($aFields['fields'] as $aItem) {
								if ($aItem['type'] == 'divider') {
									continue;
								}
								$aNameField[$aItem['name']] = [
									'type'    => $aItem['type'],
									'id'      => $aItem['id'],
									'default' => $aItem['default'] ?? []
								];
							}
							$aItemSubArrayData = $aSettings[$aFields['id']];
							foreach ($aItemSubArrayData as $aItemDataFields) {
								$aRawResult = [];
								foreach ($aNameField as $name => $aItemField) {
									if (!isset($aItemDataFields[$aItemField['id']])) {
										if (in_array($aItemField['type'], ['wil_post', 'wil_product', 'wil_cat_post',
										                                   'wil_cat_product'])) {
											$aRawResult[$name] = $this->handleQueryDataCustom($aItemField['type'],
												$aItemField['default']);
											continue;
										}
									}
									if (is_array($aItemDataFields[$aItemField['id']])) {
										$aSubSettings = $aItemDataFields[$aItemField['id']] ?? [];

										if (in_array($aItemField['type'], ['wil_post', 'wil_product', 'wil_cat_post',
										                                   'wil_cat_product'])) {
											$aRawResult[$name] = $this->handleQueryDataCustom($aItemField['type'],
												$aSubSettings);
											continue;
										}

										$aRawResult[$name]
											= $aItemDataFields[$aItemField['id']]['value'] ??
											$aItemDataFields[$aItemField['id']]['url'] ?? "";
									} else {

										$valueFields = ($aItemField['type'] == 'switcher')
											? isset($aItemDataFields[$aItemField['id']]) &&
											!empty($aItemDataFields[$aItemField['id']]) &&
											($aItemDataFields[$aItemField['id']] == 'yes') :
											$aItemDataFields[$aItemField['id']] ?? '';

										$aRawResult[$name] = $valueFields;
									}
								}
								$aResult[] = $aRawResult;
							}
							$aDataFields[$aFields['name']] = $aResult;
						} else {
							$aDataFields[$aFields['name']]
								= $aSettings[$aFields['id']]['value'] ??
								$aSettings[$aFields['id']]['url'] ?? $aSettings[$aFields['id']];
						}
					} else {
						$valueFields = ($aFields['type'] == 'switcher') ?
							isset($aSettings[$aFields['id']]) && !empty
							($aSettings[$aFields['id']]) &&
							($aSettings[$aFields['id']] != 'no')
							: $aSettings[$aFields['id']];
						$aDataFields[$aFields['name']] = $valueFields;
					}

				}
			}
			$aItems[$aSection['name']] = $aDataFields;
		}

		return $aItems;
	}

	public function handleQueryDataCustom($type, $aSubSettings): array
	{
		$aData = [];
		switch ($type) {
			case 'wil_post':
				$aData = HandleQueryData::initSingleClass()
					->queryDataPost($aSubSettings);
				break;
			case 'wil_product':
				$aData = HandleQueryData::initSingleClass()
					->queryDataProduct($aSubSettings);
				break;
			case 'wil_cat_post':
				$aData = HandleQueryData::initSingleClass()
					->handleQueryCatPosts($aSubSettings);
				break;
			case 'wil_cat_product':
				$aData = HandleQueryData::initSingleClass()
					->queryDataCatProduct($aSubSettings);
				break;
		}
		return $aData;
	}
}