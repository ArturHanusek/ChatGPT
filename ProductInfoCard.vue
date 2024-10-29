<template>
    <div>
        <div class="d-flex align-items-center">
            <img src="https://via.placeholder.com/50" alt="Product Image" class="me-2"/>
            <div class="text-primary h5">{{ product ? product['name'] : '&nbsp;' }}</div>
        </div>
        <div>
            sku:
            <font-awesome-icon icon="copy" class="fa-xs btn-link" role="button"
                               @click="copyToClipBoard((product ? product['sku'] : '') )"></font-awesome-icon>
            <strong>&nbsp;<product-sku-button :product_sku="product['sku']"/></strong>
        </div>
        <div v-if="showTags && product">
            <template v-for="tag in product['tags']">
                <a class="badge text-uppercase btn btn-outline-primary" :key="tag.id"
                   @click.prevent="filterByTag(tag)">
                    {{ getTagName(tag) }} </a>
            </template>
        </div>
    </div>
</template>

<script>
import helpers from "../../mixins/helpers";
import url from "../../mixins/url";
import ProductSkuButton from "./ProductSkuButton.vue";

export default {
    components: {ProductSkuButton},
    mixins: [helpers, url],

    name: "ProductInfoCard",

    props: {
        product: null,
        showTags: {
            type: Boolean,
            default: true
        }
    },

    methods: {
        getTagName(tag) {
            return tag.name instanceof Object ? tag.name['en'] : tag.name
        },

        filterByTag(tag) {
            this.removeUrlParameter('search');
            this.setUrlParameterAngGo('filter[product_has_tags]', this.getTagName(tag));
        }
    }
}
</script>