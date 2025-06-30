console.log("this log come from reactions jquery started")
$(function () {
    $(document).on('click', '.react-btn', async function (event) {
        const $btn = $(this);
        const $container = $btn.closest('[data-post-id]');
        const postId = $container.data('post-id');
        const type = $btn.data('type');

        try {
            const data = await $.ajax({
                url: `/post/${postId}/react/${type}`,
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });


            $container.find('.likes-count').text(data.likes);
            $container.find('.dislikes-count').text(data.dislikes);

            $container.find('.react-btn').removeClass('active');
            $btn.addClass('active');

            const $icon = $btn.find('i');
            if (type === 'like') {
                $icon.attr('class', 'bi bi-heart-fill');
            } else {
                $icon.attr('class', 'bi bi-heart-break-fill');
            }

        } catch (err) {
            console.error("AJAX request failed:", err);
        }
    });
});
