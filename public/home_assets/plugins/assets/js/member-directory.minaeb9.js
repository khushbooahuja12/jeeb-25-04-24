!function(){"use strict";var e;(e=jQuery)((function(){e(".ppressmd-member-directory-sorting-a-text").on("click",(function(r){r.preventDefault(),e(this).blur().next().toggle()})),e(document).on("click",(function(r){"ppressmd-member-directory-sorting-a-text"!==e(r.target).prop("class")&&e(".ppressmd-member-directory-sorting-a .ppressmd-new-dropdown").hide()}));var r=null;e(".ppressmd-member-directory-filters-a").on("click",(function(s){var p=e(this),i=p.parents(".ppressmd-member-directory-header");r=null===r?i.hasClass("ppmd-filters-expand"):r,s.preventDefault(),r=!r,e("a",p).blur(),r?(e(".ppressmd-member-directory-filters-bar",i).removeClass(".ppressmd-header-row-invisible").find(".ppressmd-search").css("display","grid"),e(".ppress-material-icons.ppress-down",p).hide(),e(".ppress-material-icons.ppress-up",p).css("display","inline")):(e(".ppressmd-member-directory-filters-bar",i).addClass(".ppressmd-header-row-invisible").find(".ppressmd-search").css("display","none"),e(".ppress-material-icons.ppress-down",p).css("display","inline"),e(".ppress-material-icons.ppress-up",p).hide())})),e(".ppmd-select2").select2(),e(".ppmd-date").each((function(){e(this).flatpickr(e(this).data("config"))}))})),e(window).on("load",(function(){var r=e(".ppmd-members-wrap").imagesLoaded((function(){r.masonry({itemSelector:".ppmd-member-wrap",columnWidth:".ppmd-member-wrap",gutter:".ppmd-member-gutter",percentPosition:!0})}))}))}();