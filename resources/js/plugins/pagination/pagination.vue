<template>
    <div class="row">
        <div class="col-md-12 text-left" v-if="data.data !== undefined">
            <ul
                    v-if="shouldShowPaginate"
                    :class="[
            ,
            useStyle === 'default' ? 'default-style' : '',
            listClass,
            contentAlignClass,
            customAlign
            ]"
            >
                <li
                        v-if="showNextPrev"
                        :class="[
               listItemClass,
               !hasPrevious ? (disableClassIn === 'li' ? disableClass : '') : ''
               ]"
                >
                    <a
                            :class="[
                  linkClass,
                  !hasPrevious ? (disableClassIn === 'a' ? disableClass : '') : ''
                  ]"
                            href="#"
                            @click.prevent="paginateTo(previousPage)"
                    >{{ previousText }}</a
                    >
                </li>
                <li
                        v-for="(_page, index) in pages"
                        :key="index"
                        :class="[
               listItemClass,
               activeClassIn === 'li' ? (isActive(_page) ? activeClass : '') : '',
               isDot(_page) ? disableClass : ''
               ]"
                >
                    <a
                            :class="[
                  linkClass,
                  activeClassIn === 'a' ? (isActive(_page) ? activeClass : '') : ''
                  ]"
                            href="#"
                            @click.prevent="paginateTo(_page)"
                    >
                        {{ _page }}
                    </a>
                </li>
                <li
                        v-if="showNextPrev"
                        :class="[
               listItemClass,
               !hasNext ? (disableClassIn === 'li' ? disableClass : '') : ''
               ]"
                >
                    <a
                            :class="[
                  linkClass,
                  !hasNext ? (disableClassIn === 'a' ? disableClass : '') : ''
                  ]"
                            href="#"
                            @click.prevent="paginateTo(nextPage)"
                    >{{ nextText }}</a
                    >
                </li>
            </ul>
        </div>
    </div>
</template>
<script>
export default {
  name: "pagination",
  props: {
    data: {
      type: [Object, Array],
      required: true
    },
    onEachSide: {
      type: Number,
      default: 3
    },
    showNextPrev: {
      type: Boolean,
      default: true
    },
    dots: {
      type: String,
      default: "..."
    },
    useStyle: {
      type: String,
      default: "default",
      validator: value => {
        return ["default", "bootstrap", "custom"].indexOf(value) !== -1;
      }
    },
    alignment: {
      type: String,
      default: null,
      validator: value => {
        return [null, "left", "center", "right"].indexOf(value) !== -1;
      }
    },
    activeClass: {
      type: String,
      default: "active"
    },
    activeClassIn: {
      type: String,
      default: "li",
      validator: value => {
        return ["li", "a"].indexOf(value) !== -1;
      }
    },
    listClass: {
      type: String,
      default: "pagination"
    },
    listItemClass: {
      type: String,
      default: "page-item"
    },
    contentAlignClass: {
      type: String,
      default: ""
    },
    linkClass: {
      type: String,
      default: "page-link"
    },
    disableClass: {
      type: String,
      default: "disabled"
    },
    disableClassIn: {
      type: String,
      default: "li",
      validator: value => {
        return ["li", "a"].indexOf(value) !== -1;
      }
    },
    previousText: {
      type: String,
      default: "Previous"
    },
    nextText: {
      type: String,
      default: "Next"
    },
    autoHidePaginate: {
      type: Boolean,
      default: true
    }
  },
  computed: {
    shouldShowPaginate() {
      if (!this.data.data) {
        return false;
      }

      return this.totalPage === 1 ? !this.autoHidePaginate :  true;
    },
    isResourceApi() {
      return this.data.meta ? true : false;
    },
    hasPrevious() {
      return this.currentPage > 1;
    },
    previousPage() {
      return this.currentPage - 1;
    },
    currentPage() {
      return this.isResourceApi
        ? this.data.meta.current_page
        : this.data.current_page;
    },
    nextPage() {
      return this.currentPage + 1;
    },
    hasNext() {
      return this.nextPage <= this.totalPage;
    },
    totalPage() {
      return this.isResourceApi
        ? this.data.meta.last_page
        : this.data.last_page;
    },
    pages() {
      if (this.onEachSide <= -1) {
        return this.totalPage;
      }

      let pages = [];

      for (let i = 1; i <= this.totalPage; i++) {
        if (
          i == 1 ||
          (this.currentPage - this.onEachSide <= i &&
            this.currentPage + this.onEachSide >= i) ||
          i == this.currentPage ||
          i == this.totalPage
        ) {
          pages.push(i);
        } else if (
          i == this.currentPage - (this.onEachSide + 1) ||
          i == this.currentPage + (this.onEachSide + 1)
        ) {
          pages.push(this.dots);
        }
      }

      return pages;
    },
    customAlign() {
      let _align = "";

      if (this.alignment == "left") {
        _align = "default-left";
      } else if (this.alignment == "center") {
        _align = "default-center";
      } else if (this.alignment == "right") {
        _align = "default-right";
      }

      return _align;
    }
  },
  methods: {
    isActive(pageNumber) {
      return pageNumber == this.currentPage;
    },
    isDot(content) {
      return content == this.dots ? true : false;
    },
    paginateTo(pageNumber) {
      if (pageNumber < 1 || pageNumber > this.totalPage) {
        return;
      }
      this.$emit("paginateTo", pageNumber);
    }
  }
};
</script>
<style scoped>
    .default-style {
        display: flex;
        padding-left: 0;
        list-style: none;
        border-radius: 0.25rem;
    }
    .default-style.default-center {
        justify-content: center !important;
    }
    .default-style.default-left {
        justify-content: flex-start !important;
    }
    .default-style.default-right {
        justify-content: flex-end !important;
    }
    .default-style li a {
        position: relative;
        display: block;
        padding: 0.5rem 0.75rem;
        margin-left: -1px;
        line-height: 1.25;
        color: #007bff;
        background-color: #fff;
        border: 1px solid #dee2e6;
        text-decoration: none;
    }
    .default-style li.active a {
        z-index: 3;
        color: #fff;
        background-color: #007bff;
        border-color: #007bff;
    }
    .default-style li.disabled a {
        color: #6c757d;
        pointer-events: none;
        cursor: auto;
        background-color: #fff;
        border-color: #dee2e6;
    }

</style>
