<div id="tab-news" class="tab-content">

    <div class="rss-feed"></div>

</div>

<script>
    jQuery(document).ready(function() {
        refreshNews();

        jQuery('#tabs-for-stats').on('tab-change', function(e, data) {
            if (data.selectedTabId == 'tab-news') {
                hideNewContentNotification();
            }
        });
    });

    function showNewContentNotification() {
        jQuery('#tabs-for-stats i.latest-news').closest('li')
            .addClass('fxOpacityPulse')
            .css('color', 'red');
    }

    function hideNewContentNotification() {
        jQuery('#tabs-for-stats i.latest-news').closest('li')
            .removeClass('fxOpacityPulse')
            .css('opacity', 1)
            .css('color', '');
    }

    function refreshNews() {
        ClickerVoltFunctions.ajax('wp_ajax_clickervolt_load_rss', null, {
            data: {
                url: "https://clickervolt.com/blog/feed/"
            },
            success: function(feed) {
                var meta = feed.meta;
                var items = feed.items;

                if (meta.hasNewContent) {
                    showNewContentNotification();
                } else {
                    hideNewContentNotification();
                }

                var $container = jQuery('#tab-news .rss-feed');
                $container.empty();

                for (var i = 0; i < items.length; i++) {

                    var item = items[i];
                    var html = `<div class='feed-item'>
                                    <h3 class='item-title'><a target="_blank" href="${item.url}">${item.title}</a></h3>
                                    <p class='item-description'>${item.description}</p>
                                </div>`;
                    $container.append(html);
                }
            },
        });
    }
</script>