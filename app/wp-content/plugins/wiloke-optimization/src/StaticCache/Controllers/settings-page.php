<div class="wiloke-optimization-wrapper">
    <h2><?php _e('Static Pages Settings', 'wiloke-optimization'); ?></h2>
    <hr>
	<?php
	settings_errors('static_pages');
	$activateTab = isset($_GET['tab']) ? $_GET['tab'] : 'general_settings';
	?>

    <h2 class="nav-tab-wrapper">
        <a href="<?php echo $this->getTabUrl('general_settings'); ?>"
           class="nav-tab <?php echo $activateTab == 'general_settings' ? 'nav-tab-active' : ''; ?>">
            General Settings
        </a>
        <a href="<?php echo $this->getTabUrl('generate_static_files'); ?>"
           class="nav-tab <?php echo $activateTab == 'generate_static_files' ? 'nav-tab-active' : ''; ?>">
            Generate Static Pages
        </a>
    </h2>
    <?php do_action('wiloke-optimization/src/WilcityCache/Controllers/settings-page/'.$activateTab); ?>
</div><!-- /.wrap -->
