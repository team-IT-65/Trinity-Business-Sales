!(function (e, t) {
    "use strict";
    let i = {
        init: function () {
            let t = {
                widget: i.BackgroundOverlay,
                "xpro-simple-gallery.default": i.SimpleGallery,
                "xpro-simple-portfolio.default": i.SimplePortfolio,
                "xpro-progress-bar.default": i.ProgressBar,
                "xpro-pie-chart.default": i.PieChart,
                "xpro-counter.default": i.Counter,
                "xpro-horizontal-menu.default": i.HorizontalMenu,
                "xpro-team.default": i.Team,
                "xpro-before-after.default": i.BeforeAfter,
                "xpro-content-toggle.default": i.ContentToggle,
                "xpro-news-ticker.default": i.NewsTicker,
                "xpro-table.default": i.Table,
                "xpro-icon-box.default": i.IconBox,
                "xpro-post-grid.default": i.PostGrid,
                "xpro-hot-spot.default": i.HotSpot,
                "xpro-image-scroller.default": i.ImageScroller,
                "xpro-horizontal-timeline.default": i.HorizontalTimeline,
                "xpro-contact-form.default": i.ContactForm,
                "xpro-search.default": i.Search,
                "xpro-course-grid.default": i.CourseGrid,
                "xpro-woo-product-grid.default": i.ProductGrid,
                "xpro-hero-slider.default": i.HeroSlider,
            };
            e.each(t, function (e, t) {
                elementorFrontend.hooks.addAction("frontend/element_ready/" + e, t);
            }),
                e("body").on("click.onWrapperLink", "[data-xpro-element-link]", function () {
                    var t,
                        i,
                        o = e(this),
                        n = o.data("xpro-element-link"),
                        r = o.data("id"),
                        a = document.createElement("a");
                    (a.id = "xpro-addons-wrapper-link-" + r),
                        (a.href = n.url),
                        (a.target = n.is_external ? "_blank" : "_self"),
                        (a.rel = n.nofollow ? "nofollow noreferer" : ""),
                        (a.style.display = "none"),
                        document.body.appendChild(a),
                        (t = document.getElementById(a.id)).click(),
                        (i = setTimeout(function () {
                            document.body.removeChild(t), clearTimeout(i);
                        }));
                }),
                e("[data-xpro-equal-height]").each(function () {
                    var t = e(this),
                        i = e(this).data("xpro-equal-height");
                    if ("widgets" === i) {
                        let o = e(this).find(".elementor-widget > .elementor-widget-container"),
                            n = 0;
                        o.each(function () {
                            n = Math.max(n, e(this).outerHeight());
                        }),
                            o.css({ minHeight: n + "px" });
                    } else {
                        let r = 0;
                        setTimeout(function () {
                            t.find(i).each(function () {
                                r = Math.max(r, e(this).outerHeight());
                            }),
                                t.find(i).css({ minHeight: r + "px" });
                        }, 100);
                    }
                });
        },
        BackgroundOverlay: function e(t) {
            t.hasClass("elementor-element-edit-mode") && t.addClass("xpro-widget-bg-overlay");
        },
        getElementSettings: function (e, t) {
            var o = {},
                n = e.data("model-cid");
            if (elementorFrontend.isEditMode() && n) {
                var r = elementorFrontend.config.elements.data[n],
                    a = r.attributes.widgetType || r.attributes.elType,
                    l = elementorFrontend.config.elements.keys[a];
                l ||
                    ((l = elementorFrontend.config.elements.keys[a] = []),
                    jQuery.each(r.controls, function (e, t) {
                        t.frontend_available && l.push(e);
                    })),
                    jQuery.each(r.getActiveControls(), function (e) {
                        -1 !== l.indexOf(e) && (o[e] = r.attributes[e]);
                    });
            } else o = e.data("settings") || {};
            return i.getItems(o, t);
        },
        getItems: function (e, t) {
            if (t) {
                var i = t.split("."),
                    o = i.splice(0, 1);
                if (!i.length) return e[o];
                if (!e[o]) return;
                return this.getItems(e[o], i.join("."));
            }
            return e;
        },
        SimpleGallery: function (t) {
            let o = i.getElementSettings(t),
                n = t.find(".xpro-elementor-gallery-wrapper"),
                r = t.find(".xpro-elementor-gallery-filter > ul"),
                a = r.attr("data-default-filter");
            setTimeout(function () {
                n.cubeportfolio({
                    filters: r,
                    layoutMode: "grid",
                    defaultFilter: a,
                    animationType: o.filter_animation,
                    gridAdjustment: "responsive",
                    mediaQueries: [
                        { width: elementorFrontend.config.breakpoints.lg, cols: o.item_per_row || 3, options: { gapHorizontal: o.margin.size || 0, gapVertical: o.margin.size || 0 } },
                        { width: elementorFrontend.config.breakpoints.md, cols: o.item_per_row_tablet || 2, options: { gapHorizontal: o.margin_tablet.size || 0, gapVertical: o.margin_tablet.size || 0 } },
                        { width: 0, cols: o.item_per_row_mobile || 1, options: { gapHorizontal: o.margin_mobile.size || 0, gapVertical: o.margin_mobile.size || 0 } },
                    ],
                    caption: o.hover_effect || "zoom",
                    displayType: "sequentially",
                    displayTypeSpeed: 80,
                });
            }, 500),
                "none" !== o.popup &&
                    n.lightGallery({
                        pager: !0,
                        addClass: "xpro-gallery-popup-style-" + o.popup,
                        selector: "[data-xpro-lightbox]",
                        thumbnail: "yes" === o.thumbnail,
                        exThumbImage: "data-src",
                        thumbWidth: 130,
                        thumbMargin: 15,
                        closable: !1,
                        showThumbByDefault: "yes" === o.thumbnail_by_default,
                        thumbContHeight: 150,
                        subHtmlSelectorRelative: !0,
                        hideBarsDelay: 99999999,
                        share: "yes" === o.share,
                        download: "yes" === o.download,
                    });
            let l = t.find(".xpro-filter-dropdown-tablet,.xpro-filter-dropdown-mobile"),
                s = t.find('[data-filter="' + a + '"]'),
                p = l.find("li.cbp-filter-item-active").text();
            l.find(".xpro-select-content").text(p || s.text()),
                l.on("click", function () {
                    e(this).toggleClass("active");
                }),
                l.find(".cbp-l-filters-button > li").on("click", function () {
                    l.find(".xpro-select-content").text(e(this).text());
                });
        },
        SimplePortfolio: function (t) {
            let o = i.getElementSettings(t),
                n = t.find(".xpro-elementor-gallery-wrapper"),
                r = t.find(".xpro-elementor-gallery-filter > ul"),
                a = r.attr("data-default-filter");
            u(),
                setTimeout(function () {
                    n.cubeportfolio({
                        filters: r,
                        layoutMode: "grid",
                        defaultFilter: a,
                        animationType: o.filter_animation,
                        gridAdjustment: "responsive",
                        lightboxGallery: !1,
                        mediaQueries: [
                            { width: elementorFrontend.config.breakpoints.lg, cols: o.item_per_row || 3, options: { gapHorizontal: o.margin.size || 0, gapVertical: o.margin.size || 0 } },
                            { width: elementorFrontend.config.breakpoints.md, cols: o.item_per_row_tablet || 2, options: { gapHorizontal: o.margin_tablet.size || 0, gapVertical: o.margin_tablet.size || 0 } },
                            { width: 0, cols: o.item_per_row_mobile || 1, options: { gapHorizontal: o.margin_mobile.size || 0, gapVertical: o.margin_mobile.size || 0 } },
                        ],
                        caption: o.hover_effect || "zoom",
                        displayType: "sequentially",
                        displayTypeSpeed: 80,
                    });
                }, 500);
            let l = t.find(".xpro-filter-dropdown-tablet,.xpro-filter-dropdown-mobile"),
                s = t.find('[data-filter="' + a + '"]'),
                p = l.find("li.cbp-filter-item-active").text();
            l.find(".xpro-select-content").text(p || s.text()),
                l.on("click", function () {
                    e(this).toggleClass("active");
                }),
                l.find(".cbp-l-filters-button > li").on("click", function () {
                    l.find(".xpro-select-content").text(e(this).text());
                });
            var d = null;
            let c = new TimelineLite();
            function f(i, o) {
                let n = e(i).data("title");
                if ("false" !== (d = e(i).data("src-preview"))) {
                    e(i).siblings().removeClass("xpro-preview-demo-item-open"),
                        e(i).addClass("xpro-preview-demo-item-open"),
                        t.find(".xpro-preview .xpro-preview-prev-demo,.xpro-preview .xpro-preview-next-demo").removeClass("xpro-preview-inactive");
                    e(i).prev("[data-src-preview]").length <= 0 && t.find(".xpro-preview .xpro-preview-prev-demo").addClass("xpro-preview-inactive");
                    e(i).next("[data-src-preview]").length <= 0 && t.find(".xpro-preview .xpro-preview-next-demo").addClass("xpro-preview-inactive"),
                        t.find(".xpro-preview .xpro-preview-header-info").html(""),
                        n && t.find(".xpro-preview .xpro-preview-header-info").append(`<div class="xpro-preview-demo-name">${n}</div>`),
                        t.find(".xpro-preview .xpro-preview-iframe").attr("src", d),
                        e("body").addClass("xpro-preview-active"),
                        t.find(".xpro-preview").addClass("active");
                }
            }
            function u() {
                t.find(".xpro-preview-demo-item").removeClass("xpro-preview-demo-item-open"),
                    t.find(".xpro-preview .xpro-preview-iframe").removeAttr("src"),
                    e("body").removeClass("xpro-preview-active"),
                    t.find(".xpro-preview").removeClass("active");
            }
            function m() {
                "1" === o.popup_animation && c.to(t.find(".xpro-portfolio-loader-style-1 li"), { duration: 0.4, scaleX: 1, transformOrigin: "bottom right" }),
                    "2" === o.popup_animation && c.to(t.find(".xpro-portfolio-loader-style-2 li"), { duration: 0.4, scaleX: 1, transformOrigin: "bottom left" }),
                    setTimeout(function () {
                        "1" === o.popup_animation && c.to(t.find(".xpro-portfolio-loader-style-1 li"), { duration: 0.4, scaleX: 0, transformOrigin: "bottom left" }),
                            "2" === o.popup_animation && c.to(t.find(".xpro-portfolio-loader-style-2 li"), { duration: 0.4, scaleX: 0, transformOrigin: "bottom right" });
                    }, 2500);
            }
            c.seek(0).clear(),
                (c = new TimelineLite()),
                t.find(".xpro-preview-iframe").on("load", function () {
                    e(this).addClass("loaded"), e(this).contents().find("html").attr("id", "xpro-portfolio-html-main");
                }),
                t.on("click", ".xpro-preview-type-popup", function (t) {
                    e(t.target).is(".xpro-preview-demo-import-open") || (m(), f(this, t));
                }),
                t.on("click", ".xpro-preview-type-link", function (t) {
                    let i = "";
                    return "" !== (i = e(this).data("src-preview")) && window.open(i, o.preview_target), !1;
                }),
                t.on("click", ".xpro-preview-type-none", function (e) {
                    return !1;
                }),
                t.on("click", ".xpro-preview-prev-demo", function (e) {
                    var i = t.find(".xpro-preview-demo-item-open").prev("[data-src-preview]");
                    i.length > 0 && (m(), f(i, e)), e.preventDefault();
                }),
                t.on("click", ".xpro-preview-next-demo", function (e) {
                    var i = t.find(".xpro-preview-demo-item-open").next("[data-src-preview]");
                    i.length > 0 && (m(), f(i, e)), e.preventDefault();
                }),
                t.on("click", ".xpro-preview-close", function (e) {
                    e.preventDefault(),
                        e.stopPropagation(),
                        m(),
                        setTimeout(function () {
                            u();
                        }, 2e3);
                });
        },
        ProgressBar: function (t) {
            let o = i.getElementSettings(t),
                n = t.find(".xpro-progress-count"),
                r = t.find(".xpro-progress-track");
            t.find(".xpro-progress-bar").elementorWaypoint(
                function () {
                    "yes" === o.show_count &&
                        n.animate(
                            { Counter: o.value },
                            {
                                duration: 1e3 * o.duration.size || 3e3,
                                easing: "swing",
                                step: function (t) {
                                    e(this).text(Math.ceil(t)),
                                        e(this)
                                            .parents(".xpro-progress-counter")
                                            .find(".xpro-progress-count-less")
                                            .text(100 - Math.ceil(t));
                                },
                            }
                        ),
                        r.animate({ width: o.value + "%" }, 1e3 * o.duration.size || 3e3);
                },
                { offset: "100%" }
            );
        },
        PieChart: function (t) {
            let o = i.getElementSettings(t);
            t.find(".xpro-pie-chart").easyPieChart({
                scaleColor: "transparent",
                lineWidth: o.chart_bar_size.size,
                lineCap: o.layout,
                barColor: function () {
                    var e = this.renderer.getCtx(),
                        t = this.renderer.getCanvas(),
                        i = e.createLinearGradient(0, 0, t.width, 0);
                    return i.addColorStop(0, o.bar_color_1 || "#6ec1e4"), i.addColorStop(1, o.bar_color_2 || "#6ec1e4"), i;
                },
                trackColor: o.track_color || "#f5f5f5",
                size: o.chart_size.size,
                rotate: 0,
                animate: 1e3 * o.duration.size || 2e3,
            }),
                t.find(".xpro-pie-chart").elementorWaypoint(
                    function () {
                        t.find(".xpro-pie-chart-count").animate(
                            { Counter: o.value },
                            {
                                duration: 1e3 * o.duration.size || 2e3,
                                easing: "swing",
                                step: function (t) {
                                    e(this).text(Math.ceil(t) + "%");
                                },
                            }
                        ),
                            t.find(".xpro-pie-chart").data("easyPieChart").update(o.value);
                    },
                    { offset: "100%" }
                );
        },
        Counter: function (t) {
            let o = i.getElementSettings(t);
            "yes" === o.animate_counter &&
                t.find(".xpro-counter-wrapper").elementorWaypoint(
                    function () {
                        t.find(".xpro-counter-item > .value").animate(
                            { Counter: o.value },
                            {
                                duration: 1e3 * o.duration.size || 2e3,
                                easing: "swing",
                                step: function (t) {
                                    e(this).text(Math.ceil(t));
                                },
                            }
                        );
                    },
                    { offset: "100%" }
                );
        },
        HorizontalMenu: function (t) {
            let o = i.getElementSettings(t),
                n = t.find(".xpro-elementor-horizontal-menu-toggler"),
                r = t.find(".xpro-elementor-horizontal-menu-close"),
                a = t.find(".xpro-elementor-horizontal-menu-overlay"),
                l = t.find(".dropdown > a > .xpro-dropdown-menu-toggle");
            if (
                (t.find(".dropdown li").each(function (t) {
                    if (e("ul", this).length) {
                        let i = e("ul:first", this),
                            o = i.offset().left,
                            n = i.width(),
                            r;
                        o + n <= e(window).width() ? e(this).removeClass("xpro-edge") : e(this).addClass("xpro-edge");
                    }
                }),
                "yes" === o.one_page_navigation)
            ) {
                function s(i = !1) {
                    var n = e(document).scrollTop(),
                        r = o.scroll_offset.size || 100;
                    t.find(".xpro-elementor-horizontal-navbar-nav li").each(function () {
                        let o = e(this),
                            a = o.find(".xpro-elementor-nav-link").attr("href"),
                            l = a.indexOf("#");
                        if (-1 !== l) {
                            let s = e(a.substring(l));
                            i && s.length && t.find(".xpro-elementor-horizontal-navbar-nav li:not(:first-child)").removeClass("current_page_item"),
                                s.length && s.position().top - r <= n && s.position().top + s.height() > n && (t.find(".xpro-elementor-horizontal-navbar-nav li").removeClass("current_page_item"), o.addClass("current_page_item"));
                        }
                    });
                }
                e(".xpro-elementor-nav-link").on("click", function (t) {
                    let i = e(this).attr("href"),
                        n = i.indexOf("#");
                    if (-1 !== n) {
                        let r = e(i.substring(n));
                        if (r.length) {
                            var a = this.hash;
                            t.stopPropagation(),
                                e("html, body").animate({ scrollTop: e(r).offset().top - (o.scroll_offset.size || 100) + 20 }, 500, function () {
                                    window.location.hash = a;
                                });
                        }
                    }
                }),
                    s(!0),
                    e(document).on("scroll", function () {
                        s();
                    });
            }
            if ("none" !== o.responsive_show) {
                let p = "tablet" === o.responsive_show ? 1025 : 768;
                n.on("click", function (e) {
                    e.preventDefault(), t.find(".xpro-elementor-horizontal-navbar-wrapper").toggleClass("active"), t.find(".xpro-elementor-horizontal-menu-overlay").toggleClass("active");
                }),
                    r.on("click", function (e) {
                        e.preventDefault(), t.find(".xpro-elementor-horizontal-navbar-wrapper").removeClass("active"), t.find(".xpro-elementor-horizontal-menu-overlay").removeClass("active");
                    }),
                    a.on("click", function (i) {
                        i.preventDefault(), t.find(".xpro-elementor-horizontal-navbar-wrapper").removeClass("active"), e(this).removeClass("active");
                    }),
                    l.on("click", function (t) {
                        e(window).width() < p && (t.preventDefault(), t.stopPropagation(), e(this).parent().toggleClass("active"), e(this).parent().next(".xpro-elementor-dropdown-menu").slideToggle());
                    }),
                    e(window).resize(function () {
                        e(window).width() > p ? t.find(".xpro-elementor-dropdown-menu").show() : t.find(".xpro-elementor-dropdown-menu").hide(),
                            t.find(".xpro-elementor-horizontal-navbar-wrapper").removeClass("active"),
                            t.find(".xpro-elementor-horizontal-menu-overlay").removeClass("active"),
                            t.find(".dropdown > a").removeClass("active");
                    });
            }
        },
        Team: function (t) {
            let o = i.getElementSettings(t);
            if (
                ("3" === o.layout &&
                    t.find(".xpro-team-layout-3").hover(
                        function () {
                            e(this).find(".xpro-team-description").slideDown(200);
                        },
                        function () {
                            e(this).find(".xpro-team-description").slideUp(200);
                        }
                    ),
                "7" === o.layout &&
                    t.find(".xpro-team-layout-7").hover(
                        function () {
                            e(this).find(".xpro-team-description").slideDown(200), e(this).find(".xpro-team-social-list").slideDown(250);
                        },
                        function () {
                            e(this).find(".xpro-team-description").slideUp(200), e(this).find(".xpro-team-social-list").slideUp(250);
                        }
                    ),
                "8" === o.layout &&
                    t.find(".xpro-team-layout-8").hover(
                        function () {
                            e(this).find(".xpro-team-content").slideDown(200);
                        },
                        function () {
                            e(this).find(".xpro-team-content").slideUp(200);
                        }
                    ),
                "9" === o.layout)
            ) {
                let n = t.find(".xpro-team-image > img").height(),
                    r = t.find(".xpro-team-inner-content").height();
                t.find(".xpro-team-inner-content").width(n), t.find(".xpro-team-inner-content").css("left", r + "px");
            }
            "14" === o.layout &&
                t.find(".xpro-team-layout-14").hover(
                    function () {
                        e(this).find(".xpro-team-description").slideDown(200), e(this).find(".xpro-team-social-list").slideDown(250);
                    },
                    function () {
                        e(this).find(".xpro-team-description").slideUp(200), e(this).find(".xpro-team-social-list").slideUp(250);
                    }
                );
        },
        BeforeAfter: function (e) {
            let t = i.getElementSettings(e);
            e.find(".xpro-compare-item").XproCompare({
                default_offset_pct: t.visible_ratio.size / 100 || 0.5,
                orientation: t.orientation,
                is_wiggle: "yes" === t.wiggle,
                wiggle_timeout: t.wiggle ? 1e3 * t.wiggle_timeout.size : 1e3,
                move_on_hover: "yes" === t.mouse_move,
            });
        },
        ContentToggle: function (t) {
            i.getElementSettings(t);
            t.find(".xpro-content-toggle-button").on("click", function (t) {
                t.preventDefault(), e(this).parents(".xpro-content-toggle-button-wrapper").toggleClass("active");
            });
        },
        NewsTicker: function (t) {
            let o = i.getElementSettings(t),
                n = t.find(".xpro-news-ticker"),
                r;
            e(".e-route-panel-editor-content").length && (r = ".elementor-preview-responsive-wrapper"),
                n.owlCarousel({
                    items: 1,
                    smartSpeed: 1e3,
                    loop: !0,
                    nav: !1,
                    dots: !1,
                    mouseDrag: !1,
                    touchDrag: !1,
                    rtl: "rtl" === o.direction,
                    responsiveBaseElement: r,
                    autoplay: "yes" === o.autoplay,
                    autoplayTimeout: "yes" === o.autoplay ? 1e3 * o.autoplay_timeout.size : 3e3,
                    animateOut: "fade" === o.effect && "fadeOut",
                    animateIn: "fade" === o.effect && "fadeIn",
                }),
                t.find(".xpro-news-ticker-next").click(function () {
                    n.trigger("next.owl.carousel", [1e3]),
                        t.find(".xpro-news-ticker-pause").find("i").removeClass("fa-pause"),
                        t.find(".xpro-news-ticker-pause").find("i").addClass("fa-play"),
                        t.find(".xpro-news-ticker-pause").addClass("active"),
                        n.trigger("stop.owl.autoplay");
                }),
                t.find(".xpro-news-ticker-prev").click(function () {
                    n.trigger("prev.owl.carousel", [1e3]),
                        t.find(".xpro-news-ticker-pause").find("i").removeClass("fa-pause"),
                        t.find(".xpro-news-ticker-pause").find("i").addClass("fa-play"),
                        t.find(".xpro-news-ticker-pause").addClass("active"),
                        n.trigger("stop.owl.autoplay");
                }),
                t.find(".xpro-news-ticker-close").click(function () {
                    t.find(".xpro-news-ticker-wrapper").fadeOut("slow");
                });
        },
        Table: function (t) {
            let i = t.find(".xpro-table-head-column-cell");
            t.find(".xpro-table-body-row").each(function (t, o) {
                e(o)
                    .find(".xpro-table-body-row-cell")
                    .each(function (t, o) {
                        e(o).prev().prop("colspan") > 1 && 0 !== e(o).prop("colspan") && (t += e(o).prev().prop("colspan") - 1), e(o).prepend('<div class="xpro-table-head-column-cell">' + i.eq(t).html() + "</div>");
                    });
            });
        },
        IconBox: function (e) {
            let t = i.getElementSettings(e),
                o = e.find(".elementor-widget-container"),
                n = e.find(".xpro-box-icon-item");
            "" !== t.hover_animation &&
                o.hover(
                    function () {
                        n.addClass("animated " + t.icon_hover_animation);
                    },
                    function () {
                        n.removeClass("animated " + t.icon_hover_animation);
                    }
                );
        },
        PostGrid: function (e) {
            let t = i.getElementSettings(e),
                o = e.find(".xpro-post-grid-main");
            e.find(".xpro-post-grid-item"),
                o.cubeportfolio({
                    layoutMode: "grid",
                    gridAdjustment: "responsive",
                    lightboxGallery: !1,
                    mediaQueries: [
                        { width: elementorFrontend.config.breakpoints.lg, cols: t.column_grid || 3, options: { gapHorizontal: t.space_between.size, gapVertical: t.space_between.size } },
                        { width: elementorFrontend.config.breakpoints.md, cols: t.column_grid_tablet || 2, options: { gapHorizontal: t.space_between_tablet.size || 0, gapVertical: t.space_between_tablet.size || 0 } },
                        { width: 0, cols: t.column_grid_mobile || 1, options: { gapHorizontal: t.space_between_mobile.size || 0, gapVertical: t.space_between_mobile.size || 0 } },
                    ],
                    displayType: "default",
                    displayTypeSpeed: 0,
                });
        },
        HotSpot: function (t) {
            i.getElementSettings(t),
                t.find(".xpro-post-grid-main"),
                t.find(".xpro-hotspot-type-click").on("click", function (t) {
                    t.preventDefault(), e(this).find(".xpro-hotspot-tooltip-text").toggleClass("active");
                });
        },
        ImageScroller: function (e) {
            let t = i.getElementSettings(e),
                o = e.find(".xpro-scroll-image-inner"),
                n = e.find(".xpro-image-scroll-img > img"),
                r = 0;
            "mouse-hover" === t.trigger_type &&
                o.imagesLoaded(function () {
                    (r = "vertical" === t.direction_type ? n.height() - o.height() : n.width() - o.width()),
                        "yes" === t.reverse
                            ? (n.css("transform", ("vertical" === t.direction_type ? "translateY" : "translateX") + "( -" + r + "px)"),
                              o.hover(
                                  function () {
                                      n.css("transform", ("vertical" === t.direction_type ? "translateY" : "translateX") + "(0px)");
                                  },
                                  function () {
                                      n.css("transform", ("vertical" === t.direction_type ? "translateY" : "translateX") + "( -" + r + "px)");
                                  }
                              ))
                            : o.hover(
                                  function () {
                                      n.css("transform", ("vertical" === t.direction_type ? "translateY" : "translateX") + "( -" + r + "px)");
                                  },
                                  function () {
                                      n.css("transform", ("vertical" === t.direction_type ? "translateY" : "translateX") + "(0px)");
                                  }
                              );
                });
        },
        HorizontalTimeline: function (t) {
            let o = i.getElementSettings(t),
                n = t.find(".xpro-horizontal-timeline"),
                r,
                a = 767;
            function l() {
                if ("yes" === o.reverse && e(window).width() >= a) {
                    let i = t.find(".xpro-horiz-equal-height > div"),
                        n = 0;
                    i.each(function () {
                        n = Math.max(n, e(this).outerHeight());
                    }),
                        i.parent().css({ minHeight: n + "px" });
                } else {
                    let r = t.find(".xpro-horizontal-timeline-content > div"),
                        l = 0;
                    r.each(function () {
                        l = Math.max(l, e(this).outerHeight());
                    }),
                        r.parent().css({ minHeight: l + "px" });
                    let s = t.find(".xpro-horizontal-timeline-date > div"),
                        p = 0;
                    s.each(function () {
                        p = Math.max(p, e(this).outerHeight());
                    }),
                        s.parent().css({ minHeight: p + "px" });
                }
            }
            e(".e-route-panel-editor-content").length && (r = ".elementor-preview-responsive-wrapper"),
                e(".elementor-editor-active").length && (a = 750),
                n.owlCarousel({
                    loop: "yes" === o.loop,
                    center: "yes" === o.center,
                    nav: "yes" === o.nav,
                    navText: ["", ""],
                    dots: "yes" === o.dots,
                    mouseDrag: "yes" === o.mouse_drag,
                    rtl: "yes" === o.rtl,
                    touchDrag: !0,
                    autoHeight: !1,
                    autoWidth: "yes" === o.custom_width,
                    responsiveBaseElement: r,
                    autoplay: "yes" === o.autoplay,
                    autoplayTimeout: "yes" === o.autoplay ? 1e3 * o.autoplay_timeout.size : 3e3,
                    autoplayHoverPause: !0,
                    smartSpeed: 500,
                    responsive: { 0: { items: o.item_per_row_mobile || 1 }, 768: { items: o.item_per_row_tablet || 1 }, 1024: { items: o.item_per_row || 2 } },
                }),
                setTimeout(function () {
                    l();
                }, 300),
                e(window).on("resize", function () {
                    setTimeout(function () {
                        l();
                    }, 500);
                });
        },
        ContactForm: function (t) {
            let o = i.getElementSettings(t),
                n = t.find(".xpro-contact-form"),
                r = (t.find(".xpro-contact-form-submit-button"), t.find(".xpro-contact-form-submit-button > i")),
                a = !1,
                l = n.attr("action");
            n.on("submit", function (i) {
                i.preventDefault(), r.show();
                let s = n.serializeArray(),
                    p = [],
                    d = !0,
                    c = null,
                    f = "",
                    u = 0;
                if (
                    (o.recaptcha && (a = t.find(".g-recaptcha-response").val()),
                    e.each(s, function (e, i) {
                        "" !== i.value && "g-recaptcha-response" !== i.name && ((p[u] = { name: t.find("label[for=" + i.name.replaceAll("[]", "") + "]").text() || "", value: i.value }), u++);
                    }),
                    p.length ? (f = p.map((e) => Object.entries(e).reduce((e, t, i) => `${e}${i > 0 ? "&&" : ""}${t[0]}=${t[1]}`, ""))) : (d = !1),
                    t.find('.xpro-contact-form-require [required="required"]').each(function (t) {
                        "" === e(this).val() && (d = !1);
                    }),
                    !d)
                ) {
                    (c = '<div class="xpro-alert xpro-alert-danger">' + o.required_field_message + "</div>"), t.find(".xpro-contact-form-message").hide().html(c).slideDown(), r.hide();
                    return;
                }
                if (o.recaptcha && !a) {
                    (c = '<div class="xpro-alert xpro-alert-danger">' + o.captcha_message + "</div>"), t.find(".xpro-contact-form-message").hide().html(c).slideDown(), r.hide();
                    return;
                }
                t.find(".xpro-contact-form-message").slideUp(),
                    e.ajax({ method: "POST", url: l, data: { postData: JSON.stringify(f), formName: o.form_name, formSubject: o.form_subject, captcha: a } }).done(function (e) {
                        e.success
                            ? ((c = '<div class="xpro-alert xpro-alert-success">' + o.success_message + "</div>"), t.find(".xpro-contact-form-message").hide().html(c).slideDown())
                            : ((c = '<div class="xpro-alert xpro-alert-danger">' + o.error_message + "</div>"), t.find(".xpro-contact-form-message").hide().html(c).slideDown()),
                            r.hide(),
                            o.recaptcha && grecaptcha.reset();
                    });
            });
        },
        Search: function (e) {
            let t = i.getElementSettings(e);
            ("4" === t.layout || "5" === t.layout) &&
                (e.find(".xpro-elementor-search-button").on("click", function (t) {
                    t.preventDefault(), t.stopPropagation(), e.find(".xpro-elementor-search-layout-4 .xpro-elementor-search-inner").fadeIn(400), e.find(".xpro-elementor-search-layout-5 .xpro-elementor-search-inner").slideDown(400);
                }),
                e.find(".xpro-elementor-search-button-close").on("click", function (t) {
                    t.preventDefault(), t.stopPropagation(), e.find(".xpro-elementor-search-layout-4 .xpro-elementor-search-inner").fadeOut(400), e.find(".xpro-elementor-search-layout-5 .xpro-elementor-search-inner").slideUp(400);
                }));
        },
        CourseGrid: function (e) {
            let t = i.getElementSettings(e),
                o = e.find(".xpro-post-grid-main");
            e.find(".xpro-post-grid-item"),
                o.cubeportfolio({
                    layoutMode: "grid",
                    gridAdjustment: "responsive",
                    mediaQueries: [
                        { width: elementorFrontend.config.breakpoints.lg, cols: t.column_grid || 3, options: { gapHorizontal: t.space_between.size, gapVertical: t.space_between.size } },
                        { width: elementorFrontend.config.breakpoints.md, cols: t.column_grid_tablet || 2, options: { gapHorizontal: t.space_between_tablet.size || 0, gapVertical: t.space_between_tablet.size || 0 } },
                        { width: 0, cols: t.column_grid_mobile || 1, options: { gapHorizontal: t.space_between_mobile.size || 0, gapVertical: t.space_between_mobile.size || 0 } },
                    ],
                    displayType: "default",
                    displayTypeSpeed: 0,
                });
        },
        ProductGrid: function (t) {
            let o = i.getElementSettings(t),
                n = t.find(".xpro-woo-product-grid-main");
            t.find(".xpro-woo-product-grid-item"),
                t.find(".xpro-hv-qv-btn").click(function (i) {
                    i.preventDefault();
                    let o = e(this).attr("id"),
                        n = { action: "load_quick_view_product_data", nonce: XproElementorAddons.nonce, id: o };
                    e.ajax({
                        url: XproElementorAddons.ajax_url,
                        type: "post",
                        data: n,
                        dataType: "html",
                        cache: !1,
                        beforeSend: function () {
                            t.find(".xpro-qv-loader-wrapper").css("display", "unset"), t.find(".xpro-qv-popup-wrapper").css("display", "none");
                        },
                        complete: function () {
                            t.find(".xpro-qv-loader-wrapper").css("display", "none"), t.find(".xpro-qv-popup-wrapper").css("display", "unset"), t.find(".xpro-qv-popup-overlay").css({ opacity: "1", visibility: "visible" });
                        },
                        success: function (i) {
                            t.find("#xpro_elementor_fetch_qv_data").html(i),
                                e(".xpro-woo-qv-content-sec .variations_form").wc_variation_form().find(".variations select:eq(0)").trigger("change"),
                                e(".xpro-woo-qv-content-sec .variations_form").trigger("wc_variation_form");
                        },
                    });
                }),
                t.on("click", ".xpro-woo-qv-cross", function (e) {
                    e.preventDefault(), t.find(".xpro-qv-popup-wrapper").css("display", "none"), t.find(".xpro-qv-popup-overlay").css({ opacity: "0", visibility: "hidden" });
                }),
                t.on("click", ".xpro-qv-popup-overlay", function (e) {
                    e.preventDefault(), t.find(".xpro-qv-popup-wrapper").css("display", "none"), t.find(".xpro-qv-popup-overlay").css({ opacity: "0", visibility: "hidden" });
                }),
                e(document).keyup(function (e) {
                    27 === e.keyCode && (t.find(".xpro-qv-popup-wrapper").css("display", "none"), t.find(".xpro-qv-popup-overlay").css({ opacity: "0", visibility: "hidden" }));
                }),
                t.on("click", "#xpro_elementor_fetch_qv_data .single_add_to_cart_button:not(.disabled)", function (i) {
                    if ((i.preventDefault(), "" !== e(this).parents("form").attr("action"))) return (window.location.href = e(this).parents("form").attr("action")), !1;
                    let o = e(this).closest("form");
                    if (!o[0].checkValidity()) return o[0].reportValidity(), !1;
                    let n = e(this),
                        r = e(this).val();
                    if ((e('input[name="variation_id"]').val(), e('input[name="quantity"]').val(), t.find(".woocommerce-grouped-product-list-item").length)) {
                        let a = e("input.qty"),
                            l = [];
                        e.each(a, function (t, i) {
                            let o = e(this).attr("name");
                            (o = (o = o.replace("quantity[", "")).replace("]", "")), (o = parseInt(o)), e(this).val() && (l[o] = e(this).val());
                        });
                    }
                    let s = o.serialize();
                    n.is(".single_add_to_cart_button") &&
                        (n.removeClass("added"),
                        n.addClass("loading"),
                        e.ajax({
                            url: XproElementorAddons.ajax_url,
                            type: "POST",
                            data: "action=add_cart_single_product_ajax&product_id=" + r + "&nonce=" + XproElementorAddons.nonce + "&" + s,
                            success: function (t) {
                                e(document.body).trigger("wc_fragment_refresh"), n.removeClass("loading"), n.addClass("added");
                            },
                        }));
                }),
                n.cubeportfolio({
                    layoutMode: "grid",
                    gridAdjustment: "responsive",
                    mediaQueries: [
                        { width: elementorFrontend.config.breakpoints.lg, cols: o.column_grid || 3, options: { gapHorizontal: o.space_between.size, gapVertical: o.space_between.size } },
                        { width: elementorFrontend.config.breakpoints.md, cols: o.column_grid_tablet || 2, options: { gapHorizontal: o.space_between_tablet.size || 0, gapVertical: o.space_between_tablet.size || 0 } },
                        { width: 0, cols: o.column_grid_mobile || 1, options: { gapHorizontal: o.space_between_mobile.size || 0, gapVertical: o.space_between_mobile.size || 0 } },
                    ],
                    displayType: "default",
                    displayTypeSpeed: 0,
                });
        },
        HeroSlider: function (t) {
            i.getElementSettings(t);
            var o = t.find(".xpro-hero-slider").data("xpro-hero-slider-setting");
            let n = new Swiper(t.find(".xpro-hero-slider")[0], {
                parallax: !0,
                speed: 100 * o.slide_speed,
                spaceBetween: 0,
                effect: o.slide_effect,
                fadeEffect: { crossFade: !0 },
                loop: o.loop,
                autoplay: { enabled: o.autoplay, disableOnInteraction: !0, delay: o.autoplay_timeout ? 1e3 * o.autoplay_timeout : 3e3 },
                pagination: { el: t.find(".swiper-pagination")[0], clickable: !0 },
                navigation: { nextEl: t.find(".swiper-button-next")[0], prevEl: t.find(".swiper-button-prev")[0] },
                allowTouchMove: o.mouse_drag,
            });
            n.on("slideChangeTransitionStart", function () {
                var t,
                    i = this;
                e(i.slides[i.activeIndex])
                    .find("[data-animation]")
                    .each(function () {
                        var t = e(this),
                            o = "animated " + t.data("animation");
                        t.addClass(o).one("webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend", function () {
                            t.removeClass(o);
                        }),
                            e(i.slides).removeClass("xpro-animation-init"),
                            e(i.slides[i.activeIndex]).addClass("xpro-animation-init");
                    });
            });
        },
    };
    e(window).on("elementor/frontend/init", i.init);
})(jQuery, window.elementorFrontend);
