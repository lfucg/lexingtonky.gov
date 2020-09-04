(function () {
    var $ = jQuery;

    console.log('TEST');

    $('.accordion-title').each(function () {
        $parent = $(this);
        $(this).find('a').click(function () {
            $parent.click();
        });
    })


}());