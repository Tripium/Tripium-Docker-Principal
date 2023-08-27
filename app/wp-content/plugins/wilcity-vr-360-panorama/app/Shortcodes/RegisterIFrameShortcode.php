<?php


namespace WilcityVR\Shortcodes;


class RegisterIFrameShortcode
{
	public function __construct()
	{
		add_action('wilcity_vr_360_iframe', [$this, 'registerVRShortcode']);
	}

	public function registerVRShortcode($aAtts)
	{
		$aAtts = shortcode_atts(
			[
				'width'  => '100%',
				'height' => 'auto',
				'url'
			],
			$aAtts
		);

		if (empty($aAtts['url'])) {
			return '';
		}

		ob_start();
		?>
        <iframe width="<?php echo esc_attr($aAtts[" width"]) ?>"
                height="<?php echo esc_attr($aAtts["auto"]) ?>"
                src="<?php echo esc_url($aAtts['url']) ?>"
                frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen=""></iframe>';
		<?php
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
	}
}