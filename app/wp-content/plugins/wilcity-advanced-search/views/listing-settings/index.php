<div id="wil-listing-settings">
	<wil-tabs wrapper-classes="semantic-tabs ui top attached tabular menu" key-prefix="<?php echo esc_attr($_GET['page']); ?>" default-active="main-search-form">
		<template v-slot:default="{active}">

			<wil-tab tab-key="main-search-form" tab-name="Main Search Form" :active="active">
				<template v-slot:info>
					<div class="ui info message">
						<p>
							Refer to
							<a href="https://documentation.wilcity.com/knowledgebase/setting-up-the-main-search-form/" target="_blank">Documentation</a>
							or Open a topic on <a href="https://wilcityservice.com/support/" target="_blank">wilcityservice.com/support/</a>
						</p>
					</div>
				</template>
				<template v-slot:content>
					<wil-main-search-form has-toggle="yes" />
				</template>
			</wil-tab>

      <wil-tab tab-key="listing-card" tab-name="Listing Card" :active="active" heading="Design Search Form">
        <template v-slot:info>
          <div class="info ui message">
            Adding Custom Field to Listing Card: Please read <a target="_blank" href="https://documentation.wilcity.com/knowledgebase/printing-custom-field-to-listing-card/">Printing Custom Field to Listing Card</a> to know more. <br />
          </div>
        </template>
        <template v-slot:content>
          <wil-listing-card />
        </template>
      </wil-tab>
      <wil-icons-modal :status="iconsModalStatus" :std="selectedIcon"></wil-icons-modal>
		</template>
	</wil-tabs>
</div>
