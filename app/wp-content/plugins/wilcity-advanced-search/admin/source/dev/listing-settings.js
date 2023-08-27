Vue.config.devtools = true;
__webpack_public_path__ = WILOKE_LISTING_TOOLS.PRODUCT_ASSETS_URL;
import Vue from "vue";
import WilTabs from "./new-form-fields/WilTabs.vue";
import WilTab from "./new-form-fields/WilTab.vue";
import WilDesignAddListing from "./components/WilDesignAddListing.vue";
import GlobalCompoments from "./global-components";
import { store } from "./store/store.js";
import { FormHelper } from "./mixins/FormHelper";
import { SectionHelper } from "./mixins/SectionHelper";
import UniqueId from 'vue-unique-id';
// import PortalVue from "portal-vue";
// Vue.use(PortalVue);
Vue.use(GlobalCompoments);
import { mapState } from "vuex";
import arrObjFilter from "./filters/arrObjFilter";
Vue.filter("arrObjFilter", arrObjFilter);
Vue.mixin(FormHelper);
Vue.mixin(SectionHelper);
Vue.use(UniqueId);

if (document.getElementById("wil-listing-settings") !== null) {
    new Vue({
        el: "#wil-listing-settings",
        store: store,
        data() {
            return { errorMsg: "", successMsg: "" };
        },
        components: {
            WilTabs,
            WilTab,
            WilDesignAddListing,
            WilMainSearchForm: () =>
                import(
                    /* webpackChunkName: "wil-main-search-form" */
                    /* wepackPreload: true */
                    "./components/WilMainSearchForm.vue"
                    ),
            WilHeroSearchForm: () =>
                import(
                    /* webpackChunkName: "wil-hero-search-form" */
                    /* wepackPreload: true */
                    "./components/WilHeroSearchForm.vue"
                    ),
            WilListingCard: () =>
                import(
                    /* webpackChunkName: "wil-listing-card" */
                    /* wepackPreload: true */
                    "./components/WilListingCard.vue"
                    ),
            WilReviews: () =>
                import(
                    /* webpackChunkName: "wil-reviews" */
                    /* wepackPreload: true */
                    "./components/WilReviews.vue"
                    ),
            WilSingleHighlightBoxes: () =>
                import(
                    /* webpackChunkName: "wil-single-highlight-boxes" */
                    /* wepackPreload: true */
                    "./components/WilSingleHighlightBoxes.vue"
                    ),
            WilSingleNav: () =>
                import(
                    /* webpackChunkName: "wil-single-nav" */
                    /* wepackPreload: true */
                    "./components/WilSingleNav.vue"
                    ),
            WilSingleSidebar: () =>
                import(
                    /* webpackChunkName: "wil-single-sidebar" */
                    /* wepackPreload: true */
                    "./components/WilSingleSidebar.vue"
                    ),
            WilSchemaMarkup: () =>
                import(
                    /* webpackChunkName: "wil-schema-markup" */
                    /* wepackPreload: true */
                    "./components/WilSchemaMarkup.vue"
                    )
        },
        mounted() {
            // this.$store.dispatch('AddListing/updateUsedSections', this.value);
            // console.log('zzzzz', this.iconModalStatus);
        },
        methods: {},
        computed: {
            ...mapState({
                iconsModalStatus: state => state.IconsModal.status,
                selectedIcon: state => state.IconsModal.selectedIcon
            })
        }
    });
}
