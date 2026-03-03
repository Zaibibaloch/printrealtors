import draggable from "vuedraggable";
import Errors from "@admin/js/Errors";
import Coloris from "@melloware/coloris";
import { generateUid } from "@admin/js/functions";
import { toaster } from "@admin/js/Toaster";

export default {
    components: {
        draggable,
    },

    data() {
        return {
            formSubmitting: false,
            form: { values: [] },
            errors: new Errors(),
        };
    },

    computed: {
        isEmptyProductBannerType() {
            return this.form.type === "";
        },
    },

    mounted() {
        this.hideColorPicker();
    },

    methods: {
        uid() {
            return generateUid();
        },

        changeProductBannerType(value) {
            const values = this.form.values;

            if (value !== "" && values.length === 1) {
                this.$nextTick(() => {
                    $(`#values-${values[0].uid}-label`).trigger("focus");
                });
            }

            if (value === "text") {
                values.forEach((value) => {
                    this.errors.clear(`values.${value.uid}.color`);
                    this.errors.clear(`values.${value.uid}.image`);
                    this.errors.clear(`values.${value.uid}.design`);
                });
            } else if (value === "color") {
                values.forEach((value) => {
                    this.errors.clear(`values.${value.uid}.image`);
                    this.errors.clear(`values.${value.uid}.design`);
                });

                this.$nextTick(() => {
                    this.initColorPicker();
                });
            } else if (value === "image") {
                values.forEach((value) => {
                    this.errors.clear(`values.${value.uid}.color`);
                    this.errors.clear(`values.${value.uid}.design`);
                });
            } else if (value === "design") {
                values.forEach((value) => {
                    this.errors.clear(`values.${value.uid}.color`);
                    this.errors.clear(`values.${value.uid}.image`);
                });
            } else {
                this.clearValueErrors();
            }
        },

        addRow() {
            const values = this.form.values;
            const uid = this.uid();

            const newValue = {
                uid,
                image: {
                    id: null,
                    path: null,
                },
            };

            if (this.form.type === "design") {
                newValue.design = {
                    id: null,
                    path: null,
                };
            }

            values.push(newValue);

            this.$nextTick(() => {
                $(`#values-${uid}-label`).trigger("focus");

                if (this.form.type === "color") {
                    this.initColorPicker();
                }
            });
        },

        addRowOnPressEnter(event, index) {
            const values = this.form.values;

            if (event.target.value === "") return;

            if (values.length - 1 === index) {
                this.addRow();

                return;
            }

            if (index < values.length - 1) {
                $(`#values-${values[index + 1].uid}-label`).trigger("focus");
            }
        },

        deleteRow(index, uid) {
            const values = this.form.values;

            values.splice(index, 1);

            if (values.length === 0) {
                this.addRow();
            }

            this.clearValueRowErrors(uid);
            this.updateColorThumbnails();
        },

        updateColorThumbnails() {
            if (this.form.type !== "color") return;

            const elements = document.querySelectorAll(".clr-field");

            this.form.values.forEach((value, index) => {
                elements[index].style.color = value.color || "";
            });
        },

        initColorPicker() {
            Coloris.init();

            Coloris({
                el: ".color-picker",
                alpha: false,
                rtl: FleetCart.rtl,
                theme: "large",
                wrap: true,
                format: "hex",
                selectInput: true,
                swatches: [
                    "#D01C1F",
                    "#3AA845",
                    "#118257",
                    "#0A33AE",
                    "#0D46A0",
                    "#000000",
                    "#5F4C3A",
                    "#726E6E",
                    "#F6D100",
                    "#C0E506",
                    "#FF540A",
                    "#C5A996",
                    "#4B80BE",
                    "#A1C3DA",
                    "#C8BFC2",
                    "#A9A270",
                ],
            });
        },

        hideColorPicker() {
            $(document).on("click", "#clr-swatches button", (e) => {
                $(e.currentTarget)
                    .parents("#clr-picker")
                    .removeClass("clr-open");
            });
        },

        chooseImage(index, uid) {
            let picker = new MediaPicker({ type: "image" });

            picker.on("select", ({ id, path }) => {
                this.errors.clear(`values.${uid}.image`);

                this.form.values[index].image = {
                    id: +id,
                    path,
                };
            });
        },

        chooseDesign(index, uid) {
            let picker = new MediaPicker({ type: null });

            picker.on("select", ({ id, path, size }) => {
                // Check file size (10MB = 10485760 bytes)
                if (size && size > 10485760) {
                    toaster("File size must be less than 10MB", {
                        type: "default",
                    });
                    return;
                }

                this.errors.clear(`values.${uid}.design`);

                if (!this.form.values[index].design) {
                    this.form.values[index].design = {};
                }

                this.form.values[index].design = {
                    id: +id,
                    path,
                };
            });
        },

        resetForm() {
            this.setFormDefaultData();
            this.focusInitialField();
        },

        clearValueErrors() {
            Object.keys(this.errors.errors).forEach((key) => {
                if (key.startsWith("values")) {
                    this.errors.clear(key);
                }
            });
        },

        clearValueRowErrors(uid) {
            Object.keys(this.errors.errors).forEach((key) => {
                if (key.startsWith(`values.${uid}`)) {
                    this.errors.clear(key);
                }
            });
        },

        focusFirstErrorField(elements) {
            this.$nextTick(() => {
                const element = [...elements].find(
                    (el) => el.name === Object.keys(this.errors.errors)[0]
                );

                if (element !== undefined) {
                    element.focus();
                }
            });
        },

        transformData(data) {
            const formData = JSON.parse(JSON.stringify(data));
            const PATHS = {
                text: ["id", "uid", "label"],
                color: ["id", "uid", "label", "color"],
                image: ["id", "uid", "label", "image"],
                design: ["id", "uid", "label", "design"],
            };

            if (formData.type === "") {
                formData.values = [];

                return formData;
            }

            formData.values = formData.values.reduce((accumulator, value) => {
                value = _.pick(value, PATHS[formData.type]);

                if (formData.type === "image") {
                    value.image = value.image.id;
                } else if (formData.type === "design") {
                    value.design = value.design ? value.design.id : null;
                }

                return { ...accumulator, [value.uid]: value };
            }, {});

            return formData;
        },
    },
};
