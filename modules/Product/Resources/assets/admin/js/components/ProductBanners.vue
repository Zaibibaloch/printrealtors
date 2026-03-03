<template>
    <div class="box-header">
        <h5>
            Product Banners
        </h5>

        <div class="d-flex">
            <span
                class="toggle-accordion"
                :class="{
                    collapsed: isCollapsedProductBannersAccordion,
                }"
                data-toggle="tooltip"
                data-placement="top"
                :data-original-title="
                    isCollapsedProductBannersAccordion
                        ? trans('product::products.section.expand_all')
                        : trans('product::products.section.collapse_all')
                "
                @click="
                    toggleAccordions({
                        selector: '.product-banners-group .panel-heading',
                        state: isCollapsedProductBannersAccordion,
                        data: form.product_banners,
                    })
                "
            >
                <i class="fa fa-angle-double-up" aria-hidden="true"></i>
            </span>

            <div class="drag-handle">
                <i class="fa fa-ellipsis-h" aria-hidden="true"></i>
                <i class="fa fa-ellipsis-h" aria-hidden="true"></i>
            </div>
        </div>
    </div>

    <div class="box-body">
        <div class="accordion-box-content">
            <draggable
                animation="150"
                class="variations-group product-banners-group"
                force-fallback="true"
                item-key="uid"
                handle=".drag-handle"
                @start="disableContentSelection"
                @end="enableContentSelection"
                @change="reorderProductBanners"
                :list="form.product_banners"
            >
                <template #item="{ element: productBanner, index }">
                    <div
                        :id="`variation-${productBanner.uid}`"
                        class="content-accordion panel-group options-group-wrapper"
                        :class="`variation-${productBanner.uid}`"
                    >
                        <div class="panel panel-default">
                            <div
                                class="panel-heading"
                                @click.stop="toggleAccordion($event, productBanner)"
                            >
                                <h4 class="panel-title">
                                    <div
                                        :aria-expanded="productBanner.is_open"
                                        data-toggle="collapse"
                                        data-transition="false"
                                        :class="{
                                            collapsed: !productBanner.is_open,
                                            'has-error': hasAnyError({
                                                name: 'product_banners',
                                                uid: productBanner.uid,
                                            }),
                                        }"
                                    >
                                        <div class="d-flex align-items-center">
                                            <span class="drag-handle">
                                                <i class="fa">&#xf142;</i>
                                                <i class="fa">&#xf142;</i>
                                            </span>

                                            <span
                                                v-text="
                                                    productBanner.name ||
                                                    'New Product Banner'
                                                "
                                            ></span>
                                        </div>

                                        <span
                                            class="delete-option"
                                            @click.stop="
                                                deleteProductBanner(
                                                    index,
                                                    productBanner.uid
                                                )
                                            "
                                        >
                                            <i class="fa fa-trash"></i>
                                        </span>
                                    </div>
                                </h4>
                            </div>

                            <div
                                class="panel-collapse"
                                :class="{
                                    collapse: !productBanner.is_open,
                                }"
                            >
                                <div class="panel-body">
                                    <div class="new-option">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group row">
                                                    <label
                                                        :for="`product-banners-${productBanner.uid}-name`"
                                                    >
                                                        {{
                                                            trans(
                                                                "product::products.form.variations.name"
                                                            )
                                                        }}
                                                        <span
                                                            v-if="
                                                                productBanner.name ||
                                                                productBanner.type
                                                            "
                                                            class="text-red"
                                                            >*</span
                                                        >
                                                    </label>

                                                    <input
                                                        type="text"
                                                        :name="`product_banners.${productBanner.uid}.name`"
                                                        :id="`product-banners-${productBanner.uid}-name`"
                                                        class="form-control"
                                                        v-model="productBanner.name"
                                                    />

                                                    <span
                                                        class="help-block text-red"
                                                        v-if="
                                                            errors.has(
                                                                `product_banners.${productBanner.uid}.name`
                                                            )
                                                        "
                                                        v-text="
                                                            errors.get(
                                                                `product_banners.${productBanner.uid}.name`
                                                            )
                                                        "
                                                    >
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="col-sm-3">
                                                <div class="form-group row">
                                                    <label
                                                        :for="`product-banners-${productBanner.uid}-type`"
                                                    >
                                                        {{
                                                            trans(
                                                                "product::products.form.variations.type"
                                                            )
                                                        }}
                                                        <span
                                                            v-if="
                                                                productBanner.name ||
                                                                productBanner.type
                                                            "
                                                            class="text-red"
                                                            >*</span
                                                        >
                                                    </label>

                                                    <select
                                                        :name="`product_banners.${productBanner.uid}.type`"
                                                        :id="`product-banners-${productBanner.uid}-type`"
                                                        class="form-control custom-select-black"
                                                        @change="
                                                            changeProductBannerType(
                                                                $event.target
                                                                    .value,
                                                                index,
                                                                productBanner.uid
                                                            )
                                                        "
                                                        v-model="productBanner.type"
                                                    >
                                                        <option value="">
                                                            {{
                                                                trans(
                                                                    "product::products.form.variations.variation_types.please_select"
                                                                )
                                                            }}
                                                        </option>
                                                        <option value="text">
                                                            {{
                                                                trans(
                                                                    "product::products.form.variations.variation_types.text"
                                                                )
                                                            }}
                                                        </option>
                                                        <option value="color">
                                                            {{
                                                                trans(
                                                                    "product::products.form.variations.variation_types.color"
                                                                )
                                                            }}
                                                        </option>
                                                        <option value="image">
                                                            {{
                                                                trans(
                                                                    "product::products.form.variations.variation_types.image"
                                                                )
                                                            }}
                                                        </option>
                                                        <option value="design">
                                                            Design
                                                        </option>
                                                    </select>

                                                    <span
                                                        class="help-block text-red"
                                                        v-if="
                                                            errors.has(
                                                                `product_banners.${productBanner.uid}.type`
                                                            )
                                                        "
                                                        v-text="
                                                            errors.get(
                                                                `product_banners.${productBanner.uid}.type`
                                                            )
                                                        "
                                                    >
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        class="variation-values"
                                        v-if="productBanner.type !== ''"
                                    >
                                        <div class="table-responsive">
                                            <table
                                                class="options table table-bordered table-striped"
                                                :class="
                                                    productBanner.type !== ''
                                                        ? `type-${productBanner.type}`
                                                        : ''
                                                "
                                            >
                                                <thead>
                                                    <tr>
                                                        <th></th>
                                                        <th>
                                                            {{
                                                                trans(
                                                                    "product::products.form.variations.label"
                                                                )
                                                            }}
                                                            <span
                                                                class="text-red"
                                                                >*</span
                                                            >
                                                        </th>
                                                        <th
                                                            v-if="
                                                                productBanner.type ===
                                                                'color'
                                                            "
                                                        >
                                                            {{
                                                                trans(
                                                                    "product::products.form.variations.color"
                                                                )
                                                            }}
                                                            <span
                                                                class="text-red"
                                                                >*</span
                                                            >
                                                        </th>
                                                        <th
                                                            v-else-if="
                                                                productBanner.type ===
                                                                'image'
                                                            "
                                                        >
                                                            {{
                                                                trans(
                                                                    "product::products.form.variations.image"
                                                                )
                                                            }}
                                                            <span
                                                                class="text-red"
                                                                >*</span
                                                            >
                                                        </th>
                                                        <th
                                                            v-else-if="
                                                                productBanner.type ===
                                                                'design'
                                                            "
                                                        >
                                                            Design
                                                            <span
                                                                class="text-red"
                                                                >*</span
                                                            >
                                                        </th>
                                                        <th></th>
                                                    </tr>
                                                </thead>

                                                <tbody
                                                    is="vue:draggable"
                                                    animation="150"
                                                    handle=".drag-handle"
                                                    item-key="uid"
                                                    tag="tbody"
                                                    @change="
                                                        reorderProductBannerValues
                                                    "
                                                    :list="productBanner.values"
                                                >
                                                    <template
                                                        #item="{
                                                            element: value,
                                                            index: valueIndex,
                                                        }"
                                                    >
                                                        <tr class="option-row">
                                                            <td
                                                                class="text-center"
                                                            >
                                                                <span
                                                                    class="drag-handle"
                                                                >
                                                                    <i
                                                                        class="fa"
                                                                        >&#xf142;</i
                                                                    >
                                                                    <i
                                                                        class="fa"
                                                                        >&#xf142;</i
                                                                    >
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <input
                                                                    type="text"
                                                                    :name="`product_banners.${productBanner.uid}.values.${value.uid}.label`"
                                                                    :id="`product-banners-${productBanner.uid}-values-${value.uid}-label`"
                                                                    class="form-control"
                                                                    
                                                                    @keyup.enter="
                                                                        addProductBannerRowOnPressEnter(
                                                                            $event,
                                                                            index,
                                                                            valueIndex
                                                                        )
                                                                    "
                                                                    v-model="
                                                                        value.label
                                                                    "
                                                                />

                                                                <span
                                                                    class="help-block text-red"
                                                                    v-if="
                                                                        errors.has(
                                                                            `product_banners.${productBanner.uid}.values.${value.uid}.label`
                                                                        )
                                                                    "
                                                                    v-text="
                                                                        errors.get(
                                                                            `product_banners.${productBanner.uid}.values.${value.uid}.label`
                                                                        )
                                                                    "
                                                                >
                                                                </span>
                                                            </td>
                                                            <td
                                                                v-if="
                                                                    productBanner.type ===
                                                                    'color'
                                                                "
                                                            >
                                                                <div>
                                                                    <input
                                                                        type="text"
                                                                        :name="`product_banners.${productBanner.uid}.values.${value.uid}.color`"
                                                                        :id="`product-banners-${productBanner.uid}-values-${value.uid}-color`"
                                                                        class="form-control variation-color-picker"
                                                                        v-model="
                                                                            value.color
                                                                        "
                                                                    />
                                                                </div>

                                                                <span
                                                                    class="help-block text-red"
                                                                    v-if="
                                                                        errors.has(
                                                                            `product_banners.${productBanner.uid}.values.${value.uid}.color`
                                                                        )
                                                                    "
                                                                    v-text="
                                                                        errors.get(
                                                                            `product_banners.${productBanner.uid}.values.${value.uid}.color`
                                                                        )
                                                                    "
                                                                >
                                                                </span>
                                                            </td>
                                                            <td
                                                                v-else-if="
                                                                    productBanner.type ===
                                                                    'image'
                                                                "
                                                            >
                                                                <div
                                                                    class="d-flex"
                                                                >
                                                                    <div
                                                                        class="image-holder"
                                                                        @click="
                                                                            chooseProductBannerImage(
                                                                                index,
                                                                                productBanner.uid,
                                                                                valueIndex,
                                                                                value.uid
                                                                            )
                                                                        "
                                                                    >
                                                                        <template
                                                                            v-if="
                                                                                value
                                                                                    .image
                                                                                    .id
                                                                            "
                                                                        >
                                                                            <img
                                                                                :src="
                                                                                    value
                                                                                        .image
                                                                                        .path
                                                                                "
                                                                                alt="product banner image"
                                                                            />
                                                                        </template>

                                                                        <img
                                                                            v-else
                                                                            src="@admin/images/placeholder_image.png"
                                                                            class="placeholder-image"
                                                                            alt="Placeholder Image"
                                                                        />
                                                                    </div>
                                                                </div>

                                                                <span
                                                                    class="help-block text-red"
                                                                    v-if="
                                                                        errors.has(
                                                                            `product_banners.${productBanner.uid}.values.${value.uid}.image`
                                                                        )
                                                                    "
                                                                    v-text="
                                                                        errors.get(
                                                                            `product_banners.${productBanner.uid}.values.${value.uid}.image`
                                                                        )
                                                                    "
                                                                >
                                                                </span>
                                                            </td>
                                                            <td
                                                                v-else-if="
                                                                    productBanner.type ===
                                                                    'design'
                                                                "
                                                            >
                                                                <div
                                                                    class="d-flex"
                                                                >
                                                                    <div
                                                                        class="image-holder"
                                                                        @click="
                                                                            chooseProductBannerDesign(
                                                                                index,
                                                                                productBanner.uid,
                                                                                valueIndex,
                                                                                value.uid
                                                                            )
                                                                        "
                                                                    >
                                                                        <template
                                                                            v-if="
                                                                                value.design &&
                                                                                value.design.id
                                                                            "
                                                                        >
                                                                            <img
                                                                                :src="
                                                                                    value
                                                                                        .design
                                                                                        .path
                                                                                "
                                                                                alt="product banner design"
                                                                            />
                                                                        </template>

                                                                        <img
                                                                            v-else
                                                                            src="@admin/images/placeholder_image.png"
                                                                            class="placeholder-image"
                                                                            alt="Placeholder Image"
                                                                        />
                                                                    </div>
                                                                </div>

                                                                <span
                                                                    class="help-block text-red"
                                                                    v-if="
                                                                        errors.has(
                                                                            `product_banners.${productBanner.uid}.values.${value.uid}.design`
                                                                        )
                                                                    "
                                                                    v-text="
                                                                        errors.get(
                                                                            `product_banners.${productBanner.uid}.values.${value.uid}.design`
                                                                        )
                                                                    "
                                                                >
                                                                </span>
                                                            </td>
                                                            <td
                                                                class="text-center"
                                                            >
                                                                <button
                                                                    type="button"
                                                                    tabindex="-1"
                                                                    class="btn btn-default delete-row"
                                                                    @click="
                                                                        deleteProductBannerRow(
                                                                            index,
                                                                            productBanner.uid,
                                                                            valueIndex,
                                                                            value.uid
                                                                        )
                                                                    "
                                                                >
                                                                    <i
                                                                        class="fa fa-trash"
                                                                    ></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    </template>
                                                </tbody>
                                            </table>
                                        </div>

                                        <button
                                            type="button"
                                            class="btn btn-default"
                                            @click="
                                                addProductBannerRow(
                                                    index,
                                                    productBanner.uid
                                                )
                                            "
                                        >
                                            {{
                                                trans(
                                                    "product::products.variations.add_row"
                                                )
                                            }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </draggable>

            <div class="accordion-box-footer">
                <button
                    type="button"
                    class="btn btn-default"
                    @click="addProductBanner"
                >
                    Add Product Banner
                </button>

                <div class="insert-template">
                    <select
                        class="form-control custom-select-black"
                        v-model="globalProductBannerId"
                    >
                        <option value="">
                            {{
                                trans(
                                    "product::products.form.variations.select_template"
                                )
                            }}
                        </option>

                        <option
                            v-for="globalVariation in globalProductBanners"
                            :key="globalVariation.id"
                            :value="globalVariation.id"
                        >
                            {{ globalVariation.name }}
                        </option>
                    </select>

                    <button
                        type="button"
                        class="btn btn-default"
                        :class="{
                            'btn-loading': addingGlobalProductBanner,
                        }"
                        :disabled="isAddGlobalProductBannerDisabled"
                        @click="addGlobalProductBanner"
                    >
                        {{ trans("product::products.variations.insert") }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, nextTick } from "vue";
import { useForm } from "../composables/useForm";
import { useVariations } from "../composables/useVariations";
import { useProductMethods } from "../composables/useProductMethods";
import { useDraggableSections } from "../composables/useDraggableSections";
import { generateUid } from "@admin/js/functions";
import { toaster } from "@admin/js/Toaster";
import draggable from "vuedraggable";

const globalProductBannerId = ref("");
const addingGlobalProductBanner = ref(false);
const globalProductBanners = ref(FleetCart.data["global-product-banners"] ?? []);

const {
    form,
    errors,
    focusField,
    hasAnyError,
    clearErrors,
    clearValuesError,
    clearValueRowErrors,
} = useForm();
const { initVariationsColorPicker } = useVariations();
const { toggleAccordions, toggleAccordion } = useProductMethods();
const { enableContentSelection, disableContentSelection } =
    useDraggableSections();

const isAddGlobalProductBannerDisabled = computed(
    () => globalProductBannerId.value === "" || addingGlobalProductBanner.value
);

const isCollapsedProductBannersAccordion = computed(() =>
    form.product_banners.every(({ is_open }) => is_open === false)
);

function reorderProductBanners() {}

function reorderProductBannerValues() {}

function addProductBanner({ preventFocus }) {
    const uid = generateUid();

    form.product_banners.push({
        uid,
        type: "",
        is_global: false,
        is_open: true,
        values: [
            {
                uid: generateUid(),
                image: {
                    id: null,
                    path: null,
                },
            },
        ],
    });

    if (!preventFocus) {
        focusField({
            selector: `#product-banners-${uid}-name`,
        });
    }
}

function deleteProductBanner(index, uid) {
    form.product_banners.splice(index, 1);

    clearErrors({ name: "product_banners", uid });
}

function changeProductBannerType(value, index, uid) {
    const productBanner = form.product_banners[index];

    if (value !== "" && productBanner.values.length === 1) {
        focusField({
            selector: `#product-banners-${uid}-values-${productBanner.values[0].uid}-label`,
        });
    }

    if (value === "text") {
        productBanner.values.forEach((value) => {
            errors.clear([
                `product_banners.${uid}.values.${value.uid}.color`,
                `product_banners.${uid}.values.${value.uid}.image`,
                `product_banners.${uid}.values.${value.uid}.design`,
            ]);
        });
    } else if (value === "color") {
        productBanner.values.forEach((value) => {
            errors.clear(`product_banners.${uid}.values.${value.uid}.image`);
            errors.clear(`product_banners.${uid}.values.${value.uid}.design`);
        });

        nextTick(() => {
            initVariationsColorPicker();
        });
    } else if (value === "image") {
        productBanner.values.forEach((value, valueIndex) => {
            if (!value.image) {
                productBanner.values[valueIndex].image = {
                    id: null,
                    path: null,
                };
            }
        });

        productBanner.values.forEach((value) => {
            errors.clear(`product_banners.${uid}.values.${value.uid}.color`);
            errors.clear(`product_banners.${uid}.values.${value.uid}.design`);
        });
    } else if (value === "design") {
        productBanner.values.forEach((value, valueIndex) => {
            if (!value.design) {
                productBanner.values[valueIndex].design = {
                    id: null,
                    path: null,
                };
            }
        });

        productBanner.values.forEach((value) => {
            errors.clear(`product_banners.${uid}.values.${value.uid}.color`);
            errors.clear(`product_banners.${uid}.values.${value.uid}.image`);
        });
    } else {
        clearValuesError(index, uid);
    }
}

function chooseProductBannerImage(
    productBannerIndex,
    productBannerUid,
    valueIndex,
    valueUid
) {
    let picker = new MediaPicker({ type: "image" });

    picker.on("select", ({ id, path }) => {
        form.product_banners[productBannerIndex].values[valueIndex].image = {
            id: +id,
            path,
        };

        errors.clear(`product_banners.${productBannerUid}.values.${valueUid}.image`);
    });
}

function chooseProductBannerDesign(
    productBannerIndex,
    productBannerUid,
    valueIndex,
    valueUid
) {
    let picker = new MediaPicker({ type: null });

    picker.on("select", ({ id, path, size }) => {
        if (size && size > 10485760) {
            toaster("File size must be less than 10MB", {
                type: "default",
            });
            return;
        }

        form.product_banners[productBannerIndex].values[valueIndex].design = {
            id: +id,
            path,
        };

        errors.clear(`product_banners.${productBannerUid}.values.${valueUid}.design`);
    });
}

async function addProductBannerRow(index, productBannerUid) {
    const valueUid = generateUid();
    const productBanner = form.product_banners[index];

    const newValue = {
        uid: valueUid,
        image: {
            id: null,
            path: null,
        },
    };

    if (productBanner.type === "design") {
        newValue.design = {
            id: null,
            path: null,
        };
    }

    form.product_banners[index].values.push(newValue);

    await nextTick(() => {
        initVariationsColorPicker();

        $(`#product-banners-${productBannerUid}-values-${valueUid}-label`).trigger(
            "focus"
        );
    });
}

function addProductBannerRowOnPressEnter(event, productBannerIndex, valueIndex) {
    const productBanner = form.product_banners[productBannerIndex];
    const values = productBanner.values;

    if (event.target.value === "") return;

    if (values.length - 1 === valueIndex) {
        addProductBannerRow(productBannerIndex, productBanner.uid);

        return;
    }

    if (valueIndex < values.length - 1) {
        $(
            `#product-banners-${productBanner.uid}-values-${
                values[valueIndex + 1].uid
            }-label`
        ).trigger("focus");
    }
}

function deleteProductBannerRow(
    productBannerIndex,
    productBannerUid,
    valueIndex,
    valueUid
) {
    const productBanner = form.product_banners[productBannerIndex];

    productBanner.values.splice(valueIndex, 1);

    clearValueRowErrors({
        name: "product_banners",
        uid: productBannerUid,
        valueUid,
    });

    if (productBanner.values.length === 0) {
        addProductBannerRow(productBannerIndex, productBannerUid);
    }

}

function hideColorPicker() {
    $(document).on("click", "#clr-swatches button", (e) => {
        $(e.currentTarget).parents("#clr-picker").removeClass("clr-open");
    });
}

function addGlobalProductBanner() {
    if (globalProductBannerId.value === "") return;

    addingGlobalProductBanner.value = true;

    axios
        .get(`/product-banners/${globalProductBannerId.value}`)
        .then(({ data }) => {
            data.uid = generateUid();
            data.is_open = true;

            data.values.forEach((value) => {
                value.uid = generateUid();

                if (!value.image) {
                    value.image = {
                        id: null,
                        path: null,
                    };
                }
            });

            form.product_banners.push(data);
            nextTick(() => {
                $(`#product-banners-${data.uid}-name`).trigger("focus");
            });
        })
        .catch((error) => {
            toaster(error.response.data.message, {
                type: "error",
            });
        })
        .finally(() => {
            globalProductBannerId.value = "";
            addingGlobalProductBanner.value = false;
        });
}

watch(
    () => form.product_banners,
    (newValue) => {
        if (newValue.length === 0) {
            addProductBanner({ preventFocus: true });
        }
    },
    { deep: true, immediate: true }
);

onMounted(() => {
    initVariationsColorPicker();
    hideColorPicker();
});
</script>
