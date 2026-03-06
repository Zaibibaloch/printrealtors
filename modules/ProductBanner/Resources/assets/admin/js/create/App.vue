<template>
    <div class="box-body">
        <form
            class="form"
            @input="errors.clear($event.target.name)"
            @submit.prevent
            ref="form"
        >
            <div
                class="row"
                :class="{ 'has-product_banner-type': !isEmptyProductBannerType }"
            >
                <div class="col-lg-2 col-sm-2">
                    <h5>{{ trans("product_banner::product_banners.group.general") }}</h5>
                </div>

                <div class="col-lg-7 col-sm-10">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">
                                    {{ trans("product_banner::attributes.name") }}
                                    <span class="text-red">*</span>
                                </label>

                                <input
                                    type="text"
                                    name="name"
                                    id="name"
                                    class="form-control"
                                    v-model="form.name"
                                />

                                <span
                                    class="help-block text-red"
                                    v-if="errors.has('name')"
                                    v-text="errors.get('name')"
                                ></span>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="type">
                                    {{ trans("product_banner::attributes.type") }}
                                    <span class="text-red">*</span>
                                </label>

                                <select
                                    name="type"
                                    id="type"
                                    class="form-control custom-select-black"
                                    @change="
                                        changeProductBannerType($event.target.value)
                                    "
                                    v-model="form.type"
                                >
                                    <option value="">
                                        {{
                                            trans(
                                                "product_banner::product_banners.form.product_banner_types.please_select"
                                            )
                                        }}
                                    </option>

                                    <option value="text">
                                        {{
                                            trans(
                                                "product_banner::product_banners.form.product_banner_types.text"
                                            )
                                        }}
                                    </option>

                                    <option value="color">
                                        {{
                                            trans(
                                                "product_banner::product_banners.form.product_banner_types.color"
                                            )
                                        }}
                                    </option>

                                    <option value="image">
                                        {{
                                            trans(
                                                "product_banner::product_banners.form.product_banner_types.image"
                                            )
                                        }}
                                    </option>

                                    <option value="design">
                                        Design
                                    </option>
                                </select>

                                <span
                                    class="help-block text-red"
                                    v-if="errors.has('type')"
                                    v-text="errors.get('type')"
                                ></span>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="placement">
                                    Placement
                                    <span class="text-red">*</span>
                                </label>

                                <select
                                    name="placement"
                                    id="placement"
                                    class="form-control custom-select-black"
                                    v-model="form.placement"
                                >
                                    <option value="before_variations">
                                        Before Variations
                                    </option>
                                    <option value="after_variations">
                                        After Variations
                                    </option>
                                </select>

                                <span
                                    class="help-block text-red"
                                    v-if="errors.has('placement')"
                                    v-text="errors.get('placement')"
                                ></span>
                            </div>
                        </div>

                        <div class="col-sm-12 m-b-10">
                            <div class="form-group">
                                <div class="d-flex flex-wrap align-items-center">
                                    <label
                                        class="control-label d-flex align-items-center"
                                        style="margin-right: 24px"
                                    >
                                        <input
                                            type="checkbox"
                                            name="hide_title"
                                            v-model="form.hide_title"
                                            style="margin-right: 8px"
                                        />
                                        Hide title on storefront
                                    </label>

                                    <label class="control-label d-flex align-items-center">
                                        <input
                                            type="checkbox"
                                            name="hide_value_labels"
                                            v-model="form.hide_value_labels"
                                            @change="toggleAllValueLabels"
                                            style="margin-right: 8px"
                                        />
                                        Hide value labels on storefront
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-cloak class="row" v-if="!isEmptyProductBannerType">
                <div class="col-lg-2 col-sm-2">
                    <h5>{{ trans("product_banner::product_banners.group.values") }}</h5>
                </div>

                <div class="col-lg-7 col-sm-10">
                    <div class="product_banner-values clearfix">
                        <div class="table-responsive">
                            <table
                                class="options table table-bordered table-striped"
                                :class="
                                    form.type !== '' ? `type-${form.type}` : ''
                                "
                            >
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>
                                            {{
                                                trans(
                                                    "product_banner::product_banners.form.label"
                                                )
                                            }}
                                            <span class="text-red">*</span>
                                        </th>
                                        <th v-if="form.type === 'color'">
                                            {{
                                                trans(
                                                    "product_banner::product_banners.form.color"
                                                )
                                            }}
                                            <span class="text-red">*</span>
                                        </th>
                                        <th v-else-if="form.type === 'image'">
                                            {{
                                                trans(
                                                    "product_banner::product_banners.form.image"
                                                )
                                            }}
                                            <span class="text-red">*</span>
                                        </th>
                                        <th v-else-if="form.type === 'design'">
                                            Design
                                            <span class="text-red">*</span>
                                        </th>
                                        <th></th>
                                    </tr>
                                </thead>

                                <tbody
                                    is="vue:draggable"
                                    tag="tbody"
                                    handle=".drag-handle"
                                    item-key="uid"
                                    animation="150"
                                    :list="form.values"
                                    @end="updateColorThumbnails"
                                >
                                    <template #item="{ element, index }">
                                        <tr class="option-row">
                                            <td class="text-center">
                                                <span class="drag-handle">
                                                    <i class="fa">&#xf142;</i>
                                                    <i class="fa">&#xf142;</i>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <span class="input-group-addon">
                                                        <input
                                                            type="checkbox"
                                                            :name="`values.${element.uid}.show_label`"
                                                            :id="`values-${element.uid}-show-label`"
                                                            v-model="element.show_label"
                                                        />
                                                    </span>

                                                    <input
                                                        type="text"
                                                        :name="`values.${element.uid}.label`"
                                                        :id="`values-${element.uid}-label`"
                                                        class="form-control"
                                                        @keyup.enter="
                                                            addRowOnPressEnter(
                                                                $event,
                                                                index
                                                            )
                                                        "
                                                        v-model="element.label"
                                                    />
                                                </div>

                                                <span
                                                    class="help-block text-red"
                                                    v-if="
                                                        errors.has(
                                                            `values.${element.uid}.label`
                                                        )
                                                    "
                                                    v-text="
                                                        errors.get(
                                                            `values.${element.uid}.label`
                                                        )
                                                    "
                                                >
                                                </span>

                                                <input
                                                    type="url"
                                                    :name="`values.${element.uid}.link_url`"
                                                    :id="`values-${element.uid}-link-url`"
                                                    class="form-control m-t-5"
                                                    placeholder="Click link URL (optional)"
                                                    v-model="element.link_url"
                                                />
                                            </td>
                                            <td v-if="form.type === 'color'">
                                                <div>
                                                    <input
                                                        type="text"
                                                        :name="`values.${element.uid}.color`"
                                                        :id="`values-${element.uid}-color`"
                                                        class="form-control color-picker"
                                                        v-model="element.color"
                                                    />
                                                </div>

                                                <span
                                                    class="help-block text-red"
                                                    v-if="
                                                        errors.has(
                                                            `values.${element.uid}.color`
                                                        )
                                                    "
                                                    v-text="
                                                        errors.get(
                                                            `values.${element.uid}.color`
                                                        )
                                                    "
                                                >
                                                </span>
                                            </td>
                                            <td
                                                v-else-if="
                                                    form.type === 'image'
                                                "
                                            >
                                                <div class="d-flex">
                                                    <div
                                                        class="image-holder"
                                                        @click="
                                                            chooseImage(
                                                                index,
                                                                element.uid
                                                            )
                                                        "
                                                    >
                                                        <template
                                                            v-if="
                                                                element.image.id
                                                            "
                                                        >
                                                            <img
                                                                :src="
                                                                    element
                                                                        .image
                                                                        .path
                                                                "
                                                                alt="product_banner image"
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
                                                            `values.${element.uid}.image`
                                                        )
                                                    "
                                                    v-text="
                                                        errors.get(
                                                            `values.${element.uid}.image`
                                                        )
                                                    "
                                                >
                                                </span>
                                            </td>
                                            <td
                                                v-else-if="
                                                    form.type === 'design'
                                                "
                                            >
                                                <div class="d-flex">
                                                    <div
                                                        class="image-holder"
                                                        @click="
                                                            chooseDesign(
                                                                index,
                                                                element.uid
                                                            )
                                                        "
                                                    >
                                                        <template
                                                            v-if="
                                                                element.design && element.design.id
                                                            "
                                                        >
                                                            <img
                                                                :src="
                                                                    element
                                                                        .design
                                                                        .path
                                                                "
                                                                alt="product_banner image"
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
                                                            `values.${element.uid}.design`
                                                        )
                                                    "
                                                    v-text="
                                                        errors.get(
                                                            `values.${element.uid}.design`
                                                        )
                                                    "
                                                >
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <button
                                                    type="button"
                                                    tabindex="-1"
                                                    class="btn btn-default delete-row"
                                                    @click="
                                                        deleteRow(
                                                            index,
                                                            element.uid
                                                        )
                                                    "
                                                >
                                                    <i class="fa fa-trash"></i>
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
                            @click="addRow"
                        >
                            {{ trans("product_banner::product_banners.form.add_row") }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-7 col-lg-offset-2 col-md-12 text-right">
                    <button
                        type="button"
                        class="btn btn-primary"
                        :class="{
                            'btn-loading': formSubmitting,
                        }"
                        :disabled="formSubmitting"
                        @click="submit"
                    >
                        {{ trans("admin::admin.buttons.save") }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</template>

<script>
import { toaster } from "@admin/js/Toaster";
import ProductBannerMixin from "../mixins/ProductBannerMixin";

export default {
    mixins: [ProductBannerMixin],

    created() {
        this.setFormDefaultData();
    },

    mounted() {
        this.focusInitialField();
    },

    methods: {
        toggleAllValueLabels() {
            const shouldUsePerLabelControl = Boolean(this.form.hide_value_labels);

            this.form.values.forEach((value) => {
                // Master OFF => uncheck all; master ON => check all.
                value.show_label = shouldUsePerLabelControl;
            });
        },
        setFormDefaultData() {
            this.form = {
                uid: this.uid(),
                type: "",
                placement: "after_variations",
                hide_title: false,
                hide_value_labels: false,
                values: [
                    {
                        uid: this.uid(),
                        image: {
                            id: null,
                            path: null,
                        },
                    },
                ],
            };
        },

        focusInitialField() {
            this.$nextTick(() => {
                $("#name").trigger("focus");
            });
        },

        submit() {
            this.formSubmitting = true;

            axios
                .post("/product-banners", this.transformData(this.form))
                .then((response) => {
                    const message =
                        response?.data?.message || "Product banner saved successfully.";

                    toaster(message, {
                        type: "success",
                    });

                    this.resetForm();
                    this.errors.reset();
                })
                .catch(({ response }) => {
                    const message =
                        response?.data?.message || "Unable to save product banner.";

                    toaster(message, {
                        type: "default",
                    });

                    this.errors.reset();
                    this.errors.record(response?.data?.errors || {});
                    this.focusFirstErrorField(this.$refs.form.elements);
                })
                .finally(() => {
                    this.formSubmitting = false;
                });
        },
    },
};
</script>
