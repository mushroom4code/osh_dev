<script type="text/javascript">
    $(document).ready(function () {
        let new_site = sessionStorage.getItem('new_site');
        if (new_site !== 'true') {
            $(document).find('header').append('<div class="position-absolute width-fit-content container_header z-index-1000 margin-rel">' +
                '<div class="bg-gray-white text-center p-lg-4 p-md-4 p-3 max-width-400 br-10 new-site-feedback position-relative">' +
                '<p class="mb-0 text_font_13 font-weight-bold">' +
                'Наш новый сайт находится на этапе разработки, мы будем рады вашим отзывам!</p>' +
                ' <span class="position-absolute  close_photo close_span"></span>' +
                '</div>' +
                '</div>');
        }

        $(document).find('.close_span').on('click', function () {
            $(this).closest('.margin-rel').remove();
            sessionStorage.setItem('new_site', 'true');
        })
    });
</script>